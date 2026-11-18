<x-layouts.app title="Security — Sales CRM" header="Security Settings">
    <div class="max-w-2xl space-y-6">
        <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Two-Factor Authentication</h2>
                    <p class="text-sm text-slate-500 mt-1">
                        Require a 6-digit code sent to your email in addition to your password at every sign-in.
                    </p>
                </div>
                <x-crm.status-badge :status="auth()->user()->two_factor_enabled ? 'enabled' : 'disabled'" />
            </div>

            @if (auth()->user()->two_factor_enabled)
                <div class="rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
                    Two-factor authentication is currently protecting your account.
                </div>
            @else
                <div class="rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
                    Your account is protected by a password only. Enabling two-factor authentication is strongly recommended for admin and manager accounts.
                </div>
            @endif

            <form method="POST" action="{{ route('settings.security.update') }}" class="space-y-3 pt-2 border-t border-slate-200">
                @csrf
                @method('PUT')
                <input type="hidden" name="two_factor_enabled" value="{{ auth()->user()->two_factor_enabled ? 0 : 1 }}">
                <div>
                    <label for="current_password" class="block text-xs font-medium text-slate-600">Confirm your current password *</label>
                    <input id="current_password" name="current_password" type="password" required autocomplete="current-password"
                           class="mt-1 w-full max-w-sm rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <button type="submit" class="rounded-lg {{ auth()->user()->two_factor_enabled ? 'bg-rose-600 hover:bg-rose-700' : 'bg-brand-600 hover:bg-brand-700' }} px-4 py-2 text-sm font-semibold text-white">
                    {{ auth()->user()->two_factor_enabled ? 'Disable Two-Factor Authentication' : 'Enable Two-Factor Authentication' }}
                </button>
            </form>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-2 text-sm text-slate-600">
            <h2 class="text-sm font-semibold text-slate-900">Sign-in activity</h2>
            <p><span class="text-slate-500">Last sign-in:</span> {{ auth()->user()->last_login_at?->toDayDateTimeString() ?? 'Never recorded' }}</p>
            <p><span class="text-slate-500">Last IP address:</span> {{ auth()->user()->last_login_ip ?? '—' }}</p>
        </div>
    </div>
</x-layouts.app>
