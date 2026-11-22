<?php

declare(strict_types=1);

namespace App\Actions\Quotations;

use App\Enums\QuotationStatus;
use App\Exceptions\DomainRuleException;
use App\Models\Quotation;
use App\Models\User;
use App\Services\Audit\AuditLoggerService;
use Illuminate\Support\Facades\DB;

final readonly class RejectQuotationAction
{
    public function __construct(
        private AuditLoggerService $auditLogger,
    ) {}

    public function handle(Quotation $quotation, string $reason, ?User $actor = null): Quotation
    {
        $snapshot = Quotation::query()->findOrFail($quotation->id);

        if ($snapshot->isExpired()) {
            $snapshot->update(['status' => QuotationStatus::Expired]);

            throw new DomainRuleException('This quotation has expired and can no longer be rejected.', 409);
        }

        return DB::transaction(function () use ($quotation, $reason, $actor): Quotation {
            /** @var Quotation $locked */
            $locked = Quotation::query()->lockForUpdate()->findOrFail($quotation->id);

            if ($locked->status === QuotationStatus::Expired) {
                throw new DomainRuleException('This quotation has expired and can no longer be rejected.', 409);
            }

            if ($locked->status !== QuotationStatus::Sent) {
                throw new DomainRuleException('Only sent quotations can be rejected.', 409);
            }

            $locked->update([
                'status' => QuotationStatus::Rejected,
                'rejected_at' => now(),
                'rejection_reason' => $reason,
            ]);

            $this->auditLogger->log(
                event: 'rejected',
                auditable: $locked,
                actor: $actor,
                oldValues: ['status' => QuotationStatus::Sent->value],
                newValues: ['status' => QuotationStatus::Rejected->value, 'rejection_reason' => $reason],
                teamId: $locked->team_id,
            );

            return $locked->fresh(['deal', 'items']);
        });
    }
}
