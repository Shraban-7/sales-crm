<x-layouts.app title="SMTP Configuration — Sale Fusion CRM" header="SMTP Mail Configuration">
    <div class="max-w-3xl space-y-6">
        <!-- Settings Form -->
        <form method="POST" action="{{ route('settings.smtp.store') }}" class="crm-card p-6 space-y-4">
            @csrf
            <div>
                <h2 class="text-base font-semibold text-slate-900">Custom SMTP Server Settings</h2>
                <p class="text-xs text-slate-500">Configure outbound email delivery. Passwords are encrypted at rest with AES-256-GCM.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">SMTP Host *</label>
                    <input type="text" name="mail_host" value="{{ old('mail_host', $settings?->mail_host) }}" required placeholder="smtp.mailgun.org" class="crm-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">SMTP Port *</label>
                    <input type="number" name="mail_port" value="{{ old('mail_port', $settings?->mail_port ?? 587) }}" required class="crm-input">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">SMTP Username</label>
                    <input type="text" name="mail_username" value="{{ old('mail_username', $settings?->mail_username) }}" class="crm-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">SMTP Password</label>
                    <input type="password" name="mail_password" placeholder="{{ $settings?->mail_password ? '•••••••• (Encrypted)' : 'Enter password' }}" class="crm-input">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Encryption</label>
                    <select name="mail_encryption" class="crm-select">
                        <option value="tls" @selected(($settings?->mail_encryption ?? 'tls') === 'tls')>TLS</option>
                        <option value="ssl" @selected(($settings?->mail_encryption) === 'ssl')>SSL</option>
                        <option value="none" @selected(($settings?->mail_encryption) === 'none')>None</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">From Email Address *</label>
                    <input type="email" name="mail_from_address" value="{{ old('mail_from_address', $settings?->mail_from_address ?? config('mail.from.address')) }}" required class="crm-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">From Sender Name *</label>
                    <input type="text" name="mail_from_name" value="{{ old('mail_from_name', $settings?->mail_from_name ?? config('mail.from.name')) }}" required class="crm-input">
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit" class="crm-btn-primary">Save SMTP Settings</button>
            </div>
        </form>

        <!-- Test Email Card -->
        <form method="POST" action="{{ route('settings.smtp.test') }}" class="crm-card p-6 space-y-4 bg-slate-50">
            @csrf
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Send Test Email</h3>
                <p class="text-xs text-slate-500">Verify your credentials and mail server connectivity.</p>
            </div>

            <div class="flex items-center gap-3">
                <input type="email" name="test_email" required value="{{ auth()->user()->email }}" placeholder="recipient@example.com" class="crm-input flex-1">
                <button type="submit" class="crm-btn-secondary whitespace-nowrap">Send Test Email</button>
            </div>
        </form>
    </div>
</x-layouts.app>
