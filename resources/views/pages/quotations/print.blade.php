<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $quotation->quote_number }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="p-10 text-slate-900 bg-white max-w-4xl mx-auto">
    <div class="flex justify-between items-start border-b pb-6 mb-6">
        <div>
            <h1 class="text-2xl font-bold">QUOTATION</h1>
            <p class="text-sm text-slate-500">{{ $quotation->quote_number }} (v{{ $quotation->version }})</p>
        </div>
        <div class="text-right text-sm">
            <p class="font-semibold">{{ $quotation->company?->name }}</p>
            <p>Issue Date: {{ $quotation->issue_date->toDateString() }}</p>
            <p>Valid Until: {{ $quotation->valid_until->toDateString() }}</p>
        </div>
    </div>

    <table class="w-full text-sm border-collapse mb-6">
        <thead>
        <tr class="border-b-2 border-slate-300 text-left">
            <th class="py-2">Description</th>
            <th class="py-2 text-right">Qty</th>
            <th class="py-2 text-right">Unit Price</th>
            <th class="py-2 text-right">Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($quotation->items as $item)
            <tr class="border-b border-slate-200">
                <td class="py-2">{{ $item->item_name }}</td>
                <td class="py-2 text-right">{{ $item->quantity }}</td>
                <td class="py-2 text-right">{{ number_format((float) $item->unit_price, 2) }}</td>
                <td class="py-2 text-right">{{ number_format((float) $item->line_total, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="flex justify-end">
        <div class="w-64 space-y-1 text-sm">
            <div class="flex justify-between"><span>Subtotal:</span><span>{{ $quotation->currency }} {{ number_format((float) $quotation->subtotal, 2) }}</span></div>
            <div class="flex justify-between"><span>Discount:</span><span>{{ $quotation->currency }} {{ number_format((float) $quotation->discount_total, 2) }}</span></div>
            <div class="flex justify-between"><span>Tax:</span><span>{{ $quotation->currency }} {{ number_format((float) $quotation->tax_total, 2) }}</span></div>
            <div class="flex justify-between font-bold text-base border-t pt-2"><span>Grand Total:</span><span>{{ $quotation->currency }} {{ number_format((float) $quotation->grand_total, 2) }}</span></div>
        </div>
    </div>
</body>
</html>
