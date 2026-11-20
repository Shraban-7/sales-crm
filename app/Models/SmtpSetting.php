<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SmtpSetting extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'mail_mailer',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        'mail_from_address',
        'mail_from_name',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'mail_port' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function setMailPasswordAttribute(?string $value): void
    {
        $this->attributes['mail_password'] = ($value !== null && $value !== '')
            ? Crypt::encryptString($value)
            : null;
    }

    public function getDecryptedPasswordAttribute(): ?string
    {
        $raw = $this->attributes['mail_password'] ?? null;
        if ($raw === null || $raw === '') {
            return null;
        }

        try {
            return Crypt::decryptString($raw);
        } catch (\Throwable) {
            return null;
        }
    }
}
