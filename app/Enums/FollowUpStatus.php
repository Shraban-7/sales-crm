<?php

declare(strict_types=1);

namespace App\Enums;

enum FollowUpStatus: string
{
    case Scheduled = 'scheduled';
    case Completed = 'completed';
    case Missed = 'missed';
    case Rescheduled = 'rescheduled';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
