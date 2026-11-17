<?php

declare(strict_types=1);

namespace App\Actions\Quotations;

final readonly class CalculateQuotationTotalsAction
{
    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array{
     *     items: list<array<string, mixed>>,
     *     subtotal: string,
     *     discount_total: string,
     *     tax_total: string,
     *     grand_total: string
     * }
     */
    public function handle(array $items): array
    {
        $subtotal = 0.0;
        $discountTotal = 0.0;
        $taxTotal = 0.0;
        $computedItems = [];

        foreach (array_values($items) as $index => $item) {
            $qty = round((float) $item['quantity'], 2);
            $unitPrice = round((float) $item['unit_price'], 2);
            $discountPercent = round((float) ($item['discount_percent'] ?? 0), 2);
            $taxPercent = round((float) ($item['tax_percent'] ?? 0), 2);

            $gross = round($qty * $unitPrice, 2);
            $discountAmount = round($gross * ($discountPercent / 100), 2);
            $taxable = round($gross - $discountAmount, 2);
            $taxAmount = round($taxable * ($taxPercent / 100), 2);
            $lineTotal = round($taxable + $taxAmount, 2);

            $subtotal = round($subtotal + $gross, 2);
            $discountTotal = round($discountTotal + $discountAmount, 2);
            $taxTotal = round($taxTotal + $taxAmount, 2);

            $computedItems[] = [
                'position' => $index + 1,
                'item_name' => (string) $item['item_name'],
                'description' => $item['description'] ?? null,
                'quantity' => number_format($qty, 2, '.', ''),
                'unit' => (string) ($item['unit'] ?? 'unit'),
                'unit_price' => number_format($unitPrice, 2, '.', ''),
                'discount_percent' => number_format($discountPercent, 2, '.', ''),
                'tax_percent' => number_format($taxPercent, 2, '.', ''),
                'line_total' => number_format($lineTotal, 2, '.', ''),
            ];
        }

        $grandTotal = round($subtotal - $discountTotal + $taxTotal, 2);

        return [
            'items' => $computedItems,
            'subtotal' => number_format($subtotal, 2, '.', ''),
            'discount_total' => number_format($discountTotal, 2, '.', ''),
            'tax_total' => number_format($taxTotal, 2, '.', ''),
            'grand_total' => number_format($grandTotal, 2, '.', ''),
        ];
    }
}
