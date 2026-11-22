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

    public function handle(
        Quotation $quotation,
        ?User $actor = null,
        ?string $acceptedByName = null,
        ?string $acceptedByEmail = null,
        ?string $acceptedFromIp = null,
    ): Quotation
    {
        $snapshot = Quotation::query()->findOrFail($quotation->id);

        if ($snapshot->isExpired()) {
            $snapshot->update(['status' => QuotationStatus::Expired]);

            throw new DomainRuleException(
                'This quotation expired on '.$snapshot->valid_until?->toFormattedDateString().' and can no longer be accepted.',
                409,
            );
        }

        return DB::transaction(function () use ($quotation, $actor, $acceptedByName, $acceptedByEmail, $acceptedFromIp): Quotation {
            /** @var Quotation $locked */
            $locked = Quotation::query()->lockForUpdate()->findOrFail($quotation->id);

            if ($locked->isExpired()) {
                throw new DomainRuleException('This quotation has expired and can no longer be accepted.', 409);
            }

            if ($locked->status !== QuotationStatus::Sent) {
                throw new DomainRuleException('Only sent quotations can be accepted.', 409);
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
                newValues: array_filter([
                    'status' => QuotationStatus::Accepted->value,
                    'grand_total' => $locked->grand_total,
                    'accepted_by_name' => $acceptedByName,
                    'accepted_by_email' => $acceptedByEmail,
                    'accepted_from_ip' => $acceptedFromIp,
                ], fn ($value): bool => $value !== null),
                teamId: $locked->team_id,
            );

            return $locked->fresh(['deal', 'items']);
        });
    }
}
