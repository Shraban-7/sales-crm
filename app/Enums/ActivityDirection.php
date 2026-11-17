<?php

declare(strict_types=1);

namespace App\Enums;

enum ActivityDirection: string
{
    case Inbound = 'inbound';
    case Outbound = 'outbound';
}
