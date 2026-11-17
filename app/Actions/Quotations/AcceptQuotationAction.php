<?php

declare(strict_types=1);

namespace App\Actions\Quotations;

use App\Enums\QuotationStatus;
use App\Exceptions\DomainRuleException;
use App\Models\Quotation;
use App\Models\User;
use App\Services\Audit\AuditLoggerService;
use Illuminate\Support\Facades\DB;

final readonly class AcceptQuotationAction
{
    public function __construct(
        private AuditLoggerService $auditLogger,
    ) {}

    public function handle(Quotation $quotation, User $actor): Quotation
    {
        return DB::transaction(function () use ($quotation, $actor): Quotation {
            /** @var Quotation $locked */
            $locked = Quotation::query()->lockForUpdate()->findOrFail($quotation->id);

            if (! in_array($locked->status, [QuotationStatus::Draft, QuotationStatus::Sent], true)) {
                throw new DomainRuleException('Only draft or sent quotations can be accepted.');
            }

            $oldStatus = $locked->status->value;

            $locked->update([
                'status' => QuotationStatus::Accepted,
                'accepted_at' => now(),
            ]);

            $locked->deal()->update([
                'value' => $locked->grand_total,
                'currency' => $locked->currency,
            ]);

            $this->auditLogger->log(
                event: 'accepted',
                auditable: $locked,
                actor: $actor,
                oldValues: ['status' => $oldStatus],
                newValues: ['status' => QuotationStatus::Accepted->value, 'grand_total' => $locked->grand_total],
                teamId: $locked->team_id,
            );

            return $locked->fresh(['deal', 'items']);
        });
    }
}
