<x-layouts.app title="{{ $quotation->quote_number }} — Quotation" header="{{ $quotation->quote_number }}">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <x-crm.status-badge :status="$quotation->status" />
            <span class="text-sm text-slate-600">Version {{ $quotation->version }} &middot; {{ $quotation->company?->name }}</span>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('quotations.print', $quotation) }}" target="_blank" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium">Print View</a>

            @if ($quotation->status === \App\Enums\QuotationStatus::Sent)
                <a href="{{ URL::temporarySignedRoute('quotations.public.show', now()->addDays((int) config('crm.quotations.public_link_days', 30)), ['quotation' => $quotation->ulid]) }}"
                   target="_blank"
                   class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium">Customer Link</a>
            @endif

            @if ($quotation->isDraft())
                @can('update', $quotation)
                    <button type="button" onclick="document.getElementById('edit-quotation-dialog').showModal()" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium">Edit Draft</button>
                @endcan
                @can('send', $quotation)
                    <form method="POST" action="{{ route('quotations.send', $quotation) }}">
                        @csrf
                        <button type="submit" class="rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white">Send to Customer</button>
                    </form>
                @endcan
                @can('delete', $quotation)
                    <form method="POST" action="{{ route('quotations.destroy', $quotation) }}" onsubmit="return confirm('Delete this draft quotation?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-medium text-rose-700">Delete</button>
                    </form>
                @endcan
            @endif

            @if ($quotation->status === \App\Enums\QuotationStatus::Sent)
                @can('send', $quotation)
                    <form method="POST" action="{{ route('quotations.accept', $quotation) }}">
                        @csrf
                        <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white">Accept & Sync Deal</button>
                    </form>
                    <button type="button" onclick="document.getElementById('reject-quotation-dialog').showModal()" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-medium text-rose-700">Mark Rejected</button>
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

    @if ($quotation->requiresDiscountApproval())
        <div class="rounded-xl border {{ $quotation->discount_approved_at ? 'border-emerald-200 bg-emerald-50 text-emerald-900' : 'border-amber-200 bg-amber-50 text-amber-900' }} p-4 space-y-2 text-sm">
            <p class="font-semibold">
                Discount of {{ number_format($quotation->discountPercent(), 2) }}% exceeds the {{ number_format((float) config('crm.quotations.discount_approval_threshold', 15), 2) }}% approval threshold.
            </p>
            @if ($quotation->discount_approved_at)
                <p>Approved by {{ $quotation->discountApprover?->name }} on {{ $quotation->discount_approved_at->toDayDateTimeString() }}.</p>
            @else
                <p>This quotation cannot be sent until a manager approves the discount.</p>
                @can('approveDiscount', $quotation)
                    <form method="POST" action="{{ route('quotations.approve-discount', $quotation) }}">
                        @csrf
                        <button type="submit" class="rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white">Approve Discount</button>
                    </form>
                @endcan
            @endif
        </div>
    @endif

    @if ($quotation->status === \App\Enums\QuotationStatus::Rejected && $quotation->rejection_reason)
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-900">
            <strong>Customer declined this quotation.</strong> {{ $quotation->rejection_reason }}
        </div>
    @endif

    @if ($quotation->isExpired())
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-900">
            This quotation expired on {{ $quotation->valid_until->toFormattedDateString() }}. Clone it to issue a fresh version.
        </div>
    @endif

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

    @if ($quotation->isDraft())
        @can('update', $quotation)
            <dialog id="edit-quotation-dialog" class="rounded-2xl border border-slate-200 p-6 max-w-3xl w-full m-auto shadow-xl backdrop:bg-slate-900/50">
                <form method="POST" action="{{ route('quotations.update', $quotation) }}" data-module="quotation-calculator" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <h3 class="text-base font-semibold text-slate-900">Edit Draft Quotation</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-600">Contact</label>
                            <select name="contact_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                                <option value="">None</option>
                                @foreach ($contacts as $contact)
                                    <option value="{{ $contact->id }}" @selected($quotation->contact_id === $contact->id)>{{ $contact->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600">Issue Date *</label>
                            <input type="date" name="issue_date" value="{{ $quotation->issue_date->toDateString() }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600">Valid Until *</label>
                            <input type="date" name="valid_until" value="{{ $quotation->valid_until->toDateString() }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                        </div>
                    </div>
                    <input type="hidden" name="currency" value="{{ $quotation->currency }}">
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs font-semibold text-slate-700">Line Items</span>
                            <button type="button" data-add-item class="rounded border border-slate-300 px-2 py-1 text-xs">+ Add Item</button>
                        </div>
                        <table class="min-w-full text-xs">
                            <thead><tr class="text-left text-slate-500 border-b"><th class="py-1">Item</th><th class="py-1 w-16">Qty</th><th class="py-1 w-24">Price</th><th class="py-1 w-16">Disc%</th><th class="py-1 w-16">Tax%</th><th class="py-1 w-20 text-right">Total</th><th></th></tr></thead>
                            <tbody data-items-body class="divide-y divide-slate-100">
                            @foreach ($quotation->items as $item)
                                <tr data-item-row>
                                    <td class="py-1 pr-1"><input type="text" data-field="item_name" value="{{ $item->item_name }}" required class="w-full rounded border border-slate-300 px-2 py-1"></td>
                                    <td class="py-1 pr-1"><input type="number" step="0.01" data-field="quantity" value="{{ $item->quantity }}" required class="w-full rounded border border-slate-300 px-2 py-1"></td>
                                    <td class="py-1 pr-1"><input type="number" step="0.01" data-field="unit_price" value="{{ $item->unit_price }}" required class="w-full rounded border border-slate-300 px-2 py-1"></td>
                                    <td class="py-1 pr-1"><input type="number" step="0.01" data-field="discount_percent" value="{{ $item->discount_percent }}" class="w-full rounded border border-slate-300 px-2 py-1"></td>
                                    <td class="py-1 pr-1"><input type="number" step="0.01" data-field="tax_percent" value="{{ $item->tax_percent }}" class="w-full rounded border border-slate-300 px-2 py-1"></td>
                                    <td class="py-1 text-right font-mono" data-line-total>{{ $item->line_total }}</td>
                                    <td class="py-1 text-right"><button type="button" data-remove-item class="text-rose-600">&times;</button></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <template data-item-template>
                            <tr data-item-row>
                                <td class="py-1 pr-1"><input type="text" data-field="item_name" required class="w-full rounded border border-slate-300 px-2 py-1"></td>
                                <td class="py-1 pr-1"><input type="number" step="0.01" data-field="quantity" value="1" required class="w-full rounded border border-slate-300 px-2 py-1"></td>
                                <td class="py-1 pr-1"><input type="number" step="0.01" data-field="unit_price" value="0" required class="w-full rounded border border-slate-300 px-2 py-1"></td>
                                <td class="py-1 pr-1"><input type="number" step="0.01" data-field="discount_percent" value="0" class="w-full rounded border border-slate-300 px-2 py-1"></td>
                                <td class="py-1 pr-1"><input type="number" step="0.01" data-field="tax_percent" value="0" class="w-full rounded border border-slate-300 px-2 py-1"></td>
                                <td class="py-1 text-right font-mono" data-line-total>0.00</td>
                                <td class="py-1 text-right"><button type="button" data-remove-item class="text-rose-600">&times;</button></td>
                            </tr>
                        </template>
                    </div>
                    <div class="flex justify-end gap-4 text-xs font-mono">
                        <span>Sub: <strong data-subtotal>0.00</strong></span>
                        <span>Disc: <strong data-discount-total>0.00</strong></span>
                        <span>Tax: <strong data-tax-total>0.00</strong></span>
                        <span>Total: <strong data-grand-total>0.00</strong></span>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="document.getElementById('edit-quotation-dialog').close()" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">Cancel</button>
                        <button type="submit" class="rounded-lg bg-slate-900 px-4 py-1.5 text-sm font-semibold text-white">Save Draft</button>
                    </div>
                </form>
            </dialog>
        @endcan
    @endif

    @if ($quotation->status === \App\Enums\QuotationStatus::Sent)
        @can('send', $quotation)
            <dialog id="reject-quotation-dialog" class="rounded-2xl border border-slate-200 p-6 max-w-md w-full m-auto shadow-xl backdrop:bg-slate-900/50">
                <form method="POST" action="{{ route('quotations.reject', $quotation) }}" class="space-y-3">
                    @csrf
                    <h3 class="text-base font-semibold text-slate-900">Mark Quotation Rejected</h3>
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Rejection Reason *</label>
                        <textarea name="rejection_reason" rows="3" required maxlength="255" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="document.getElementById('reject-quotation-dialog').close()" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">Cancel</button>
                        <button type="submit" class="rounded-lg bg-rose-600 px-4 py-1.5 text-sm font-semibold text-white">Mark Rejected</button>
                    </div>
                </form>
            </dialog>
        @endcan
    @endif
</x-layouts.app>
