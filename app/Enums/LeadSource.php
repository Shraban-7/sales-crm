<?php

declare(strict_types=1);

namespace App\Enums;

enum LeadSource: string
{
    case Website = 'website';
    case Referral = 'referral';
    case ColdCall = 'cold_call';
    case LinkedIn = 'linkedin';
    case Event = 'event';
    case Import = 'import';

    public function label(): string
    {
        return match ($this) {
            self::Website => 'Website',
            self::Referral => 'Referral',
            self::ColdCall => 'Cold Call',
            self::LinkedIn => 'LinkedIn',
            self::Event => 'Event',
            self::Import => 'Import',
        };
    }
}
