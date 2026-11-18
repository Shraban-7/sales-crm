<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\QuotationStatus;
use App\Models\Quotation;
use Illuminate\Console\Command;

final class ExpireQuotationsCommand extends Command
{
    protected $signature = 'crm:expire-quotations';

    protected $description = 'Mark sent quotations past their validity date as expired';

    public function handle(): int
    {
        $expired = Quotation::query()
            ->where('status', QuotationStatus::Sent->value)
            ->whereDate('valid_until', '<', now()->toDateString())
            ->update(['status' => QuotationStatus::Expired->value]);

        $this->info("Expired {$expired} quotation(s).");

        return self::SUCCESS;
    }
}
