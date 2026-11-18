@php
    $company = $quotation->company;
    $contact = $quotation->contact;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $quotation->quote_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #0f172a; }
        h1 { font-size: 22px; margin: 0; letter-spacing: 2px; }
        .muted { color: #64748b; }
        .header { border-bottom: 2px solid #0f172a; padding-bottom: 14px; margin-bottom: 20px; }
        .header td { vertical-align: top; }
        .right { text-align: right; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        table.items th { text-align: left; border-bottom: 1px solid #cbd5e1; padding: 6px 4px; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #475569; }
        table.items td { border-bottom: 1px solid #e2e8f0; padding: 7px 4px; }
        table.items td.num, table.items th.num { text-align: right; }
        table.totals { width: 45%; margin-left: 55%; border-collapse: collapse; }
        table.totals td { padding: 5px 4px; }
        table.totals tr.grand td { border-top: 2px solid #0f172a; font-weight: bold; font-size: 14px; padding-top: 8px; }
        .notes { margin-top: 24px; border-top: 1px solid #e2e8f0; padding-top: 12px; }
        .notes h3 { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 4px; color: #475569; }
        .footer { position: fixed; bottom: -8px; left: 0; right: 0; text-align: center; font-size: 9px; color: #94a3b8; }
    </style>
</head>
<body>
<table class="header" width="100%">
    <tr>
        <td width="55%">
            <h1>QUOTATION</h1>
            <p class="muted">{{ $quotation->quote_number }} &middot; Version {{ $quotation->version }}</p>
            <p>
                <strong>{{ $company?->name }}</strong><br>
                @if ($company?->billing_address){{ $company->billing_address }}<br>@endif
                @if ($company?->city){{ $company->city }}@if ($company->country), {{ $company->country }}@endif<br>@endif
                @if ($company?->phone){{ $company->phone }}<br>@endif
                @if ($company?->website){{ $company->website }}@endif
            </p>
        </td>
        <td width="45%" class="right">
            <p>
                <strong>Issue date:</strong> {{ $quotation->issue_date->toDateString() }}<br>
                <strong>Valid until:</strong> {{ $quotation->valid_until->toDateString() }}<br>
                <strong>Currency:</strong> {{ $quotation->currency }}
            </p>
            @if ($contact)
                <p class="muted">
                    Attn: {{ $contact->full_name }}<br>
                    @if ($contact->email){{ $contact->email }}<br>@endif
                    @if ($contact->phone){{ $contact->phone }}@endif
                </p>
            @endif
        </td>
    </tr>
</table>

<table class="items">
    <thead>
    <tr>
        <th>Description</th>
        <th class="num">Qty</th>
        <th class="num">Unit price</th>
        <th class="num">Disc %</th>
        <th class="num">Tax %</th>
        <th class="num">Total</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($quotation->items as $item)
        <tr>
            <td>
                <strong>{{ $item->item_name }}</strong>
                @if ($item->description)<br><span class="muted">{{ $item->description }}</span>@endif
            </td>
            <td class="num">{{ rtrim(rtrim(number_format((float) $item->quantity, 2, '.', ''), '0'), '.') }} {{ $item->unit }}</td>
            <td class="num">{{ number_format((float) $item->unit_price, 2) }}</td>
            <td class="num">{{ number_format((float) $item->discount_percent, 2) }}</td>
            <td class="num">{{ number_format((float) $item->tax_percent, 2) }}</td>
            <td class="num">{{ number_format((float) $item->line_total, 2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<table class="totals">
    <tr>
        <td>Subtotal</td>
        <td class="right">{{ $quotation->currency }} {{ number_format((float) $quotation->subtotal, 2) }}</td>
    </tr>
    <tr>
        <td>Discount</td>
        <td class="right">- {{ $quotation->currency }} {{ number_format((float) $quotation->discount_total, 2) }}</td>
    </tr>
    <tr>
        <td>Tax</td>
        <td class="right">{{ $quotation->currency }} {{ number_format((float) $quotation->tax_total, 2) }}</td>
    </tr>
    <tr class="grand">
        <td>Grand total</td>
        <td class="right">{{ $quotation->currency }} {{ number_format((float) $quotation->grand_total, 2) }}</td>
    </tr>
</table>

@if ($quotation->terms_and_conditions)
    <div class="notes">
        <h3>Terms &amp; Conditions</h3>
        <div>{!! nl2br(e($quotation->terms_and_conditions)) !!}</div>
    </div>
@endif

@if ($quotation->customer_notes)
    <div class="notes">
        <h3>Notes</h3>
        <div>{!! nl2br(e($quotation->customer_notes)) !!}</div>
    </div>
@endif

<div class="footer">
    Prepared by {{ $quotation->owner?->name }} &middot; {{ $quotation->quote_number }} &middot; Generated {{ now()->toDayDateTimeString() }}
</div>
</body>
</html>
