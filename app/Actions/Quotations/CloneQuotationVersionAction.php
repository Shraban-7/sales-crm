<?php

declare(strict_types=1);

namespace App\Actions\Quotations;

use App\Enums\QuotationStatus;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class CloneQuotationVersionAction
{
    public function handle(Quotation $quotation, User $actor): Quotation
    {
        return DB::transaction(function () use ($quotation, $actor): Quotation {
            $quotation->loadMissing('items');

            $maxVersion = (int) Quotation::query()
                ->where('deal_id', $quotation->deal_id)
                ->max('version');

            $newVersion = $maxVersion + 1;
            $baseNumber = preg_replace('/-V\d+$/', '', $quotation->quote_number);
            $newNumber = "{$baseNumber}-V{$newVersion}";

            /** @var Quotation $clone */
            $clone = Quotation::query()->create([
                'ulid' => (string) Str::ulid(),
                'team_id' => $quotation->team_id,
                'owner_id' => $actor->id,
                'deal_id' => $quotation->deal_id,
                'company_id' => $quotation->company_id,
                'contact_id' => $quotation->contact_id,
                'quote_number' => $newNumber,
                'version' => $newVersion,
                'status' => QuotationStatus::Draft,
                'currency' => $quotation->currency,
                'subtotal' => $quotation->subtotal,
                'discount_total' => $quotation->discount_total,
                'tax_total' => $quotation->tax_total,
                'grand_total' => $quotation->grand_total,
                'issue_date' => now()->toDateString(),
                'valid_until' => now()->addDays(30)->toDateString(),
                'terms_and_conditions' => $quotation->terms_and_conditions,
                'customer_notes' => $quotation->customer_notes,
            ]);

            $itemsPayload = $quotation->items->map(fn ($item): array => [
                'position' => $item->position,
                'item_name' => $item->item_name,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'unit_price' => $item->unit_price,
                'discount_percent' => $item->discount_percent,
                'tax_percent' => $item->tax_percent,
                'line_total' => $item->line_total,
            ])->all();

            $clone->items()->createMany($itemsPayload);

            return $clone->load('items');
        });
    }
}
