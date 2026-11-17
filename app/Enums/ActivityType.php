<?php

declare(strict_types=1);

namespace App\Enums;

enum ActivityType: string
{
    case Call = 'call';
    case Email = 'email';
    case Meeting = 'meeting';
    case Note = 'note';
    case System = 'system';

    public function label(): string
    {
        return match ($this) {
            self::Call => 'Call',
            self::Email => 'Email',
            self::Meeting => 'Meeting',
            self::Note => 'Note',
            self::System => 'System',
        };
    }
}
