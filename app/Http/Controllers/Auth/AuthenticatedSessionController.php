<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\Audit\AuditLoggerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('pages.auth.login');
    }

    public function store(LoginRequest $request, AuditLoggerService $auditLogger): RedirectResponse
    {
        $throttleKey = Str::lower((string) $request->input('email')).'|'.$request->ip();
        $maxAttempts = (int) config('crm.auth.max_login_attempts', 5);
        $decaySeconds = (int) config('crm.auth.login_decay_seconds', 60);

        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', ['seconds' => $seconds, 'minutes' => ceil($seconds / 60)]),
            ]);
        }

        $credentials = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'is_active' => true,
        ];

        if (! Auth::validate($credentials)) {
            RateLimiter::hit($throttleKey, $decaySeconds);

            $auditLogger->log(
                event: 'login_failed',
                auditable: 'user',
                actor: null,
                newValues: ['email' => $request->input('email')],
                request: $request,
            );

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        /** @var User|null $user */
        $user = User::query()
            ->where('email', $request->input('email'))
            ->where('is_active', true)
            ->first();

        if ($user === null) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($throttleKey);

        if ($user->two_factor_enabled) {
            TwoFactorChallengeController::rememberPendingUser($request, $user, $request->boolean('remember'));
            TwoFactorChallengeController::issueCode($user);

            $auditLogger->log(
                event: 'two_factor_challenge_sent',
                auditable: $user,
                actor: $user,
                teamId: $user->team_id,
                request: $request,
            );

            return redirect()->route('two-factor.challenge');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        $auditLogger->log(
            event: 'login_succeeded',
            auditable: $user,
            actor: $user,
            teamId: $user->team_id,
            request: $request,
        );

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
