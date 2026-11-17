<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Deal;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class LeadConverted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Lead $lead,
        public readonly Deal $deal,
        public readonly User $actor,
    ) {}
}
