<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ScheduledJobs;
use Illuminate\Console\Command;

final class CheckProposalExpiry extends Command
{
    protected $signature = 'crm:check-proposal-expiry';

    protected $description = 'Flag proposals and quotations past their validity date as expired';

    public function handle(ScheduledJobs $scheduler): int
    {
        $expiredCount = $scheduler->checkProposalExpiry();
        $this->info("Successfully updated {$expiredCount} expired proposals/quotations.");

        return Command::SUCCESS;
    }
}
