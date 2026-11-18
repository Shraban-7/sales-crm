<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quotation {{ $quotation->quote_number }} — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-full py-10 px-4">
<div class="mx-auto w-full max-w-3xl space-y-6">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-200 pb-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ config('app.name') }}</p>
                <h1 class="text-2xl font-bold text-slate-900">Quotation {{ $quotation->quote_number }}</h1>
                <p class="text-sm text-slate-500">Version {{ $quotation->version }} &middot; Issued {{ $quotation->issue_date->toFormattedDateString() }}</p>
            </div>
            <div class="text-right text-sm text-slate-600">
                <p class="font-semibold text-slate-900">{{ $quotation->company?->name }}</p>
                <p>{{ $quotation->contact?->full_name }}</p>
                <p>Valid until {{ $quotation->valid_until->toFormattedDateString() }}</p>
                <div class="mt-2"><x-crm.status-badge :status="$quotation->status" /></div>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-800">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="text-left text-xs font-semibold uppercase text-slate-500">
            <tr>
                <th class="py-2">Item</th>
                <th class="py-2 text-right">Qty</th>
                <th class="py-2 text-right">Unit Price</th>
                <th class="py-2 text-right">Total</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @foreach ($quotation->items as $item)
                <tr>
                    <td class="py-2.5">
                        <p class="font-medium text-slate-900">{{ $item->item_name }}</p>
                        @if ($item->description)<p class="text-xs text-slate-500">{{ $item->description }}</p>@endif
                    </td>
                    <td class="py-2.5 text-right">{{ $item->quantity }} {{ $item->unit }}</td>
                    <td class="py-2.5 text-right font-mono text-xs">{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td class="py-2.5 text-right font-mono text-xs">{{ number_format((float) $item->line_total, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="flex justify-end border-t border-slate-200 pt-4">
            <div class="w-64 space-y-1 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">Subtotal:</span><span class="font-mono">{{ $quotation->currency }} {{ number_format((float) $quotation->subtotal, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Discount:</span><span class="font-mono">{{ $quotation->currency }} {{ number_format((float) $quotation->discount_total, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Tax:</span><span class="font-mono">{{ $quotation->currency }} {{ number_format((float) $quotation->tax_total, 2) }}</span></div>
                <div class="flex justify-between font-bold text-base border-t pt-1"><span>Grand Total:</span><span class="font-mono">{{ $quotation->currency }} {{ number_format((float) $quotation->grand_total, 2) }}</span></div>
            </div>
        </div>

        @if ($quotation->terms_and_conditions)
            <div class="border-t border-slate-200 pt-4 text-sm text-slate-600">
                <h2 class="font-semibold text-slate-900 mb-1">Terms &amp; Conditions</h2>
                <p class="whitespace-pre-line">{{ $quotation->terms_and_conditions }}</p>
            </div>
        @endif
    </div>

    @if ($quotation->canBeAccepted())
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <form method="POST" action="{{ $acceptUrl }}" class="bg-white rounded-2xl border border-emerald-200 shadow-sm p-6 space-y-3">
                @csrf
                <h2 class="text-sm font-semibold text-slate-900">Accept this quotation</h2>
                <div>
                    <label for="acceptance_name" class="block text-xs font-medium text-slate-600">Your name *</label>
                    <input id="acceptance_name" name="acceptance_name" required value="{{ old('acceptance_name') }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="acceptance_email" class="block text-xs font-medium text-slate-600">Email</label>
                    <input id="acceptance_email" name="acceptance_email" type="email" value="{{ old('acceptance_email') }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <button type="submit" class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Accept Quotation</button>
            </form>

            <form method="POST" action="{{ $rejectUrl }}" class="bg-white rounded-2xl border border-rose-200 shadow-sm p-6 space-y-3">
                @csrf
                <h2 class="text-sm font-semibold text-slate-900">Decline this quotation</h2>
                <div>
                    <label for="rejection_reason" class="block text-xs font-medium text-slate-600">Reason *</label>
                    <textarea id="rejection_reason" name="rejection_reason" rows="3" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('rejection_reason') }}</textarea>
                </div>
                <button type="submit" class="w-full rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">Decline Quotation</button>
            </form>
        </div>
    @elseif ($quotation->status === \App\Enums\QuotationStatus::Accepted)
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-6 text-sm text-emerald-900">
            This quotation was accepted on {{ $quotation->accepted_at?->toDayDateTimeString() }}. Thank you for your business.
        </div>
    @elseif ($quotation->isExpired())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 p-6 text-sm text-rose-900">
            This quotation expired on {{ $quotation->valid_until->toFormattedDateString() }}. Please contact {{ $quotation->owner?->name ?? 'our sales team' }} for an updated quote.
        </div>
    @elseif ($quotation->status === \App\Enums\QuotationStatus::Rejected)
        <div class="rounded-2xl border border-slate-200 bg-white p-6 text-sm text-slate-600">
            This quotation was declined. Please contact {{ $quotation->owner?->name ?? 'our sales team' }} if you would like to discuss alternatives.
        </div>
    @endif

    <p class="text-center text-xs text-slate-400">
        Questions? Contact {{ $quotation->owner?->name }} &middot; {{ $quotation->owner?->email }}
    </p>
</div>
</body>
</html>
