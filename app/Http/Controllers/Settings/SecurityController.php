<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Audit\AuditLoggerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class SecurityController extends Controller
{
    public function edit(): View
    {
        return view('pages.settings.security');
    }

    public function update(Request $request, AuditLoggerService $auditLogger): RedirectResponse
    {
        $data = $request->validate([
            'two_factor_enabled' => ['required', 'boolean'],
            'current_password' => ['required', 'current_password'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $enabled = (bool) $data['two_factor_enabled'];

        if ($enabled === $user->two_factor_enabled) {
            throw ValidationException::withMessages([
                'two_factor_enabled' => 'Two-factor authentication is already '.($enabled ? 'enabled' : 'disabled').'.',
            ]);
        }

        $user->update([
            'two_factor_enabled' => $enabled,
            'two_factor_code_hash' => null,
            'two_factor_expires_at' => null,
            'two_factor_last_sent_at' => null,
        ]);

        $auditLogger->log(
            event: $enabled ? 'two_factor_enabled' : 'two_factor_disabled',
            auditable: $user,
            actor: $user,
            newValues: ['two_factor_enabled' => $enabled],
            teamId: $user->team_id,
        );

        return back()->with(
            'status',
            $enabled
                ? 'Two-factor authentication enabled. You will receive an email code at every sign-in.'
                : 'Two-factor authentication disabled.',
        );
    }
}
