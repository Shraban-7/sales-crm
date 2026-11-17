<x-layouts.app title="{{ $quotation->quote_number }} — Quotation" header="{{ $quotation->quote_number }}">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <x-crm.status-badge :status="$quotation->status" />
            <span class="text-sm text-slate-600">Version {{ $quotation->version }} &middot; {{ $quotation->company?->name }}</span>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('quotations.print', $quotation) }}" target="_blank" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium">Print View</a>

            @if ($quotation->isDraft())
                @can('send', $quotation)
                    <form method="POST" action="{{ route('quotations.send', $quotation) }}">
                        @csrf
                        <button type="submit" class="rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white">Mark Sent</button>
                    </form>
                @endcan
            @endif

            @if (in_array($quotation->status, [\App\Enums\QuotationStatus::Draft, \App\Enums\QuotationStatus::Sent], true))
                @can('send', $quotation)
                    <form method="POST" action="{{ route('quotations.accept', $quotation) }}">
                        @csrf
                        <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white">Accept &amp; Sync Deal</button>
                    </form>
                @endcan
            @endif

            @if (in_array($quotation->status, [\App\Enums\QuotationStatus::Rejected, \App\Enums\QuotationStatus::Expired], true))
                @can('create', \App\Models\Quotation::class)
                    <form method="POST" action="{{ route('quotations.clone', $quotation) }}">
                        @csrf
                        <button type="submit" class="rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-semibold text-white">Clone New Version</button>
                    </form>
                @endcan
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-4">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="text-left text-xs font-semibold uppercase text-slate-500">
            <tr>
                <th class="py-2">Item</th>
                <th class="py-2 text-right">Qty</th>
                <th class="py-2 text-right">Unit Price</th>
                <th class="py-2 text-right">Disc %</th>
                <th class="py-2 text-right">Tax %</th>
                <th class="py-2 text-right">Line Total</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @foreach ($quotation->items as $item)
                <tr>
                    <td class="py-2.5 font-medium">{{ $item->item_name }}</td>
                    <td class="py-2.5 text-right">{{ $item->quantity }}</td>
                    <td class="py-2.5 text-right font-mono text-xs">{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td class="py-2.5 text-right">{{ $item->discount_percent }}%</td>
                    <td class="py-2.5 text-right">{{ $item->tax_percent }}%</td>
                    <td class="py-2.5 text-right font-mono text-xs">{{ number_format((float) $item->line_total, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="flex justify-end border-t pt-4">
            <div class="w-64 space-y-1 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">Subtotal:</span><span class="font-mono">{{ $quotation->currency }} {{ number_format((float) $quotation->subtotal, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Discount:</span><span class="font-mono">{{ $quotation->currency }} {{ number_format((float) $quotation->discount_total, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Tax:</span><span class="font-mono">{{ $quotation->currency }} {{ number_format((float) $quotation->tax_total, 2) }}</span></div>
                <div class="flex justify-between font-bold text-base border-t pt-1"><span>Grand Total:</span><span class="font-mono">{{ $quotation->currency }} {{ number_format((float) $quotation->grand_total, 2) }}</span></div>
            </div>
        </div>
    </div>
</x-layouts.app>
