<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ScheduledJobs;
use Illuminate\Console\Command;

final class ExpireQuotationsCommand extends Command
{
    protected $signature = 'crm:expire-quotations';

    protected $description = 'Mark sent quotations past their validity date as expired';

    public function handle(ScheduledJobs $scheduler): int
    {
        $expired = $scheduler->expireQuotations();

        $this->info("Expired {$expired} quotation(s).");

        return self::SUCCESS;
    }
}
