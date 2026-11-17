<?php

declare(strict_types=1);

namespace App\Enums;

enum FollowUpChannel: string
{
    case Call = 'call';
    case Email = 'email';
    case WhatsApp = 'whatsapp';
    case Meeting = 'meeting';

    public function label(): string
    {
        return match ($this) {
            self::Call => 'Call',
            self::Email => 'Email',
            self::WhatsApp => 'WhatsApp',
            self::Meeting => 'Meeting',
        };
    }
}
