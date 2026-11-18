<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $quotation->quote_number }}</title>
    <x-crm.favicons />
    @vite(['resources/css/app.css'])
</head>
<body class="p-10 text-slate-900 bg-white max-w-4xl mx-auto antialiased">
    <div class="flex justify-between items-start border-b border-slate-200 pb-6 mb-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-brand-600">{{ config('app.name') }}</p>
            <h1 class="text-2xl font-bold tracking-tight mt-1">QUOTATION</h1>
            <p class="text-sm text-slate-500 mt-0.5">{{ $quotation->quote_number }} (v{{ $quotation->version }})</p>
        </div>
        <div class="text-right text-sm space-y-0.5">
            <p class="font-semibold text-slate-900">{{ $quotation->company?->name }}</p>
            <p class="text-slate-600">Issue Date: {{ $quotation->issue_date->toDateString() }}</p>
            <p class="text-slate-600">Valid Until: {{ $quotation->valid_until->toDateString() }}</p>
        </div>
    </div>

    <table class="w-full text-sm border-collapse mb-6">
        <thead>
        <tr class="border-b-2 border-slate-300 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
            <th class="py-2.5">Description</th>
            <th class="py-2.5 text-right">Qty</th>
            <th class="py-2.5 text-right">Unit Price</th>
            <th class="py-2.5 text-right">Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($quotation->items as $item)
            <tr class="border-b border-slate-200">
                <td class="py-2.5 font-medium text-slate-900">{{ $item->item_name }}</td>
                <td class="py-2.5 text-right tabular-nums">{{ $item->quantity }}</td>
                <td class="py-2.5 text-right font-mono text-xs tabular-nums">{{ number_format((float) $item->unit_price, 2) }}</td>
                <td class="py-2.5 text-right font-mono text-xs font-semibold tabular-nums">{{ number_format((float) $item->line_total, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="flex justify-end">
        <div class="w-72 space-y-1.5 text-sm">
            <div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span class="font-mono tabular-nums">{{ $quotation->currency }} {{ number_format((float) $quotation->subtotal, 2) }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Discount</span><span class="font-mono tabular-nums">{{ $quotation->currency }} {{ number_format((float) $quotation->discount_total, 2) }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Tax</span><span class="font-mono tabular-nums">{{ $quotation->currency }} {{ number_format((float) $quotation->tax_total, 2) }}</span></div>
            <div class="flex justify-between font-bold text-base border-t border-slate-300 pt-2"><span>Grand Total</span><span class="font-mono tabular-nums">{{ $quotation->currency }} {{ number_format((float) $quotation->grand_total, 2) }}</span></div>
        </div>
    </div>
</body>
</html>
