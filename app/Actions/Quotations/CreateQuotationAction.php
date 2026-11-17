<?php

declare(strict_types=1);

namespace App\Actions\Quotations;

use App\Enums\QuotationStatus;
use App\Models\Deal;
use App\Models\Quotation;
use App\Models\User;
use App\Services\Audit\AuditLoggerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class CreateQuotationAction
{
    public function __construct(
        private CalculateQuotationTotalsAction $calculateTotals,
        private AuditLoggerService $auditLogger,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, User $actor): Quotation
    {
        return DB::transaction(function () use ($data, $actor): Quotation {
            $deal = Deal::query()->findOrFail((int) $data['deal_id']);
            $computed = $this->calculateTotals->handle($data['items']);

            $year = now()->format('Y');
            $prefix = (string) config('crm.quotations.number_prefix', 'QT');
            $latestNumber = Quotation::withTrashed()
                ->where('quote_number', 'like', "{$prefix}-{$year}-%")
                ->lockForUpdate()
                ->orderByDesc('id')
                ->value('quote_number');

            $nextSeq = 1;
            if (is_string($latestNumber) && preg_match('/(\d+)$/', $latestNumber, $matches) === 1) {
                $nextSeq = ((int) $matches[1]) + 1;
            }

            $quoteNumber = sprintf('%s-%s-%05d', $prefix, $year, $nextSeq);

            /** @var Quotation $quotation */
            $quotation = Quotation::query()->create([
                'ulid' => (string) Str::ulid(),
                'team_id' => $deal->team_id,
                'owner_id' => $actor->id,
                'deal_id' => $deal->id,
                'company_id' => $deal->company_id,
                'contact_id' => $data['contact_id'] ?? $deal->primary_contact_id,
                'quote_number' => $quoteNumber,
                'version' => 1,
                'status' => QuotationStatus::Draft,
                'currency' => $data['currency'] ?? $deal->currency,
                'subtotal' => $computed['subtotal'],
                'discount_total' => $computed['discount_total'],
                'tax_total' => $computed['tax_total'],
                'grand_total' => $computed['grand_total'],
                'issue_date' => $data['issue_date'],
                'valid_until' => $data['valid_until'],
                'terms_and_conditions' => $data['terms_and_conditions'] ?? null,
                'customer_notes' => $data['customer_notes'] ?? null,
            ]);

            $quotation->items()->createMany($computed['items']);

            $this->auditLogger->log(
                event: 'created',
                auditable: $quotation,
                actor: $actor,
                newValues: ['quote_number' => $quoteNumber, 'grand_total' => $computed['grand_total']],
                teamId: $quotation->team_id,
            );

            return $quotation->load('items');
        });
    }
}
