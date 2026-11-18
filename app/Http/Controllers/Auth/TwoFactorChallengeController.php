<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\TwoFactorCodeNotification;
use App\Services\Audit\AuditLoggerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class TwoFactorChallengeController extends Controller
{
    private const SESSION_USER = 'two_factor.user_id';

    private const SESSION_REMEMBER = 'two_factor.remember';

    public function create(Request $request): View|RedirectResponse
    {
        $user = $this->pendingUser($request);

        if ($user === null) {
            return redirect()->route('login');
        }

        return view('pages.auth.two-factor-challenge', [
            'email' => $user->email,
            'expiresAt' => $user->two_factor_expires_at,
            'canResend' => $this->canResend($user),
        ]);
    }

    public function store(Request $request, AuditLoggerService $auditLogger): RedirectResponse
    {
        $user = $this->pendingUser($request);

        if ($user === null) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'code' => ['required', 'string', 'digits:6'],
        ]);

        $throttleKey = 'two-factor:'.$user->id.'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'code' => "Too many attempts. Try again in {$seconds} seconds.",
            ]);
        }

        if ($user->two_factor_expires_at === null || $user->two_factor_expires_at->isPast()) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'code' => 'This verification code has expired. Request a new one.',
            ]);
        }

        if ($user->two_factor_code_hash === null || ! Hash::check($data['code'], $user->two_factor_code_hash)) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'code' => 'That verification code is not valid.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        $remember = (bool) $request->session()->pull(self::SESSION_REMEMBER, false);
        $request->session()->forget(self::SESSION_USER);
        $request->session()->regenerate();

        Auth::login($user, $remember);

        $user->update([
            'two_factor_code_hash' => null,
            'two_factor_expires_at' => null,
            'two_factor_last_sent_at' => null,
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        $auditLogger->log(
            event: 'login_succeeded',
            auditable: $user,
            actor: $user,
            newValues: ['two_factor' => true],
            teamId: $user->team_id,
            request: $request,
        );

        return redirect()->intended(route('dashboard'));
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = $this->pendingUser($request);

        if ($user === null) {
            return redirect()->route('login');
        }

        if (! $this->canResend($user)) {
            return back()->with('error', 'Please wait a moment before requesting another code.');
        }

        $this->issueCode($user);

        return back()->with('status', 'A new verification code has been sent to your email.');
    }

    public function cancel(Request $request): RedirectResponse
    {
        $request->session()->forget([self::SESSION_USER, self::SESSION_REMEMBER]);

        return redirect()->route('login')->with('status', 'Sign-in cancelled.');
    }

    public static function rememberPendingUser(Request $request, User $user, bool $remember): void
    {
        $request->session()->put([
            self::SESSION_USER => $user->id,
            self::SESSION_REMEMBER => $remember,
        ]);
    }

    public static function issueCode(User $user): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->update([
            'two_factor_code_hash' => Hash::make($code),
            'two_factor_expires_at' => now()->addMinutes(10),
            'two_factor_last_sent_at' => now(),
        ]);

        $user->notify(new TwoFactorCodeNotification($code));
    }

    private function pendingUser(Request $request): ?User
    {
        $userId = $request->session()->get(self::SESSION_USER);

        if ($userId === null) {
            return null;
        }

        return User::query()
            ->whereKey((int) $userId)
            ->where('is_active', true)
            ->where('two_factor_enabled', true)
            ->first();
    }

    private function canResend(User $user): bool
    {
        return $user->two_factor_last_sent_at === null
            || $user->two_factor_last_sent_at->addSeconds(60)->isPast();
    }
}
