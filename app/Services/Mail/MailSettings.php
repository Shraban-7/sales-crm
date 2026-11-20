<?php

declare(strict_types=1);

namespace App\Services\Mail;

use App\Models\SmtpSetting;
use Illuminate\Support\Facades\Config;

final class MailSettings
{
    public function getSettings(): ?SmtpSetting
    {
        return SmtpSetting::query()->where('is_active', true)->latest()->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function saveSettings(array $data): SmtpSetting
    {
        $setting = SmtpSetting::query()->latest()->first() ?? new SmtpSetting;
        $setting->fill([
            'mail_mailer' => $data['mail_mailer'] ?? 'smtp',
            'mail_host' => (string) $data['mail_host'],
            'mail_port' => (int) ($data['mail_port'] ?? 587),
            'mail_username' => $data['mail_username'] ?? null,
            'mail_encryption' => $data['mail_encryption'] ?? 'tls',
            'mail_from_address' => (string) $data['mail_from_address'],
            'mail_from_name' => (string) ($data['mail_from_name'] ?? config('app.name')),
            'is_active' => true,
        ]);

        if (isset($data['mail_password']) && $data['mail_password'] !== '') {
            $setting->mail_password = (string) $data['mail_password'];
        }

        $setting->save();
        $this->applyToMailer();

        return $setting;
    }

    public function applyToMailer(): void
    {
        $setting = $this->getSettings();
        if ($setting === null) {
            return;
        }

        Config::set('mail.mailers.smtp.host', $setting->mail_host);
        Config::set('mail.mailers.smtp.port', $setting->mail_port);
        Config::set('mail.mailers.smtp.encryption', $setting->mail_encryption);
        Config::set('mail.mailers.smtp.username', $setting->mail_username);

        $password = $setting->decrypted_password;
        if ($password !== null) {
            Config::set('mail.mailers.smtp.password', $password);
        }

        Config::set('mail.from.address', $setting->mail_from_address);
        Config::set('mail.from.name', $setting->mail_from_name);
    }
}
