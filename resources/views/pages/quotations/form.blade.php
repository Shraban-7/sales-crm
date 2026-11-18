<x-layouts.app title="New Quotation — Sales CRM" header="Create Quotation">
    <form method="POST" action="{{ route('quotations.store') }}" data-module="quotation-calculator" class="crm-card p-6 space-y-6">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-600 mb-1.5">Deal *</label>
                <select name="deal_id" required class="crm-select">
                    @foreach ($deals as $deal)
                        <option value="{{ $deal->id }}" @selected((string) $selectedDealId === (string) $deal->id)>{{ $deal->title }} ({{ $deal->company?->name }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-600 mb-1.5">Issue Date *</label>
                <input type="date" name="issue_date" value="{{ now()->toDateString() }}" required class="crm-input">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-600 mb-1.5">Valid Until *</label>
                <input type="date" name="valid_until" value="{{ now()->addDays(30)->toDateString() }}" required class="crm-input">
            </div>
        </div>
        <input type="hidden" name="currency" value="{{ config('crm.currency', 'BDT') }}">

        <div>
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-slate-900">Line Items</h3>
                <button type="button" data-add-item class="crm-btn-secondary py-1.5 text-xs">+ Add Item</button>
            </div>
            <div class="overflow-x-auto rounded-xl border border-slate-200 px-3">
                <table class="crm-table">
                    <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                        <th class="py-2.5 pr-2">Item</th>
                        <th class="py-2.5 pr-2 w-24">Qty</th>
                        <th class="py-2.5 pr-2 w-32">Unit Price</th>
                        <th class="py-2.5 pr-2 w-24">Disc %</th>
                        <th class="py-2.5 pr-2 w-24">Tax %</th>
                        <th class="py-2.5 w-28 text-right">Line Total</th>
                        <th class="py-2.5 w-10"></th>
                    </tr>
                    </thead>
                    <tbody data-items-body class="divide-y divide-slate-100">
                    <tr data-item-row>
                        <td class="py-2.5 pr-2"><input type="text" data-field="item_name" required placeholder="Item name" class="crm-input py-1.5"></td>
                        <td class="py-2.5 pr-2"><input type="number" step="0.01" data-field="quantity" value="1" required class="crm-input py-1.5"></td>
                        <td class="py-2.5 pr-2"><input type="number" step="0.01" data-field="unit_price" value="0" required class="crm-input py-1.5"></td>
                        <td class="py-2.5 pr-2"><input type="number" step="0.01" data-field="discount_percent" value="0" class="crm-input py-1.5"></td>
                        <td class="py-2.5 pr-2"><input type="number" step="0.01" data-field="tax_percent" value="0" class="crm-input py-1.5"></td>
                        <td class="py-2.5 text-right font-mono text-xs font-semibold text-slate-800 tabular-nums" data-line-total>0.00</td>
                        <td class="py-2.5 text-right"><button type="button" data-remove-item class="text-rose-600 hover:text-rose-700 text-base leading-none px-1">&times;</button></td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <template data-item-template>
                <tr data-item-row>
                    <td class="py-2.5 pr-2"><input type="text" data-field="item_name" required placeholder="Item name" class="crm-input py-1.5"></td>
                    <td class="py-2.5 pr-2"><input type="number" step="0.01" data-field="quantity" value="1" required class="crm-input py-1.5"></td>
                    <td class="py-2.5 pr-2"><input type="number" step="0.01" data-field="unit_price" value="0" required class="crm-input py-1.5"></td>
                    <td class="py-2.5 pr-2"><input type="number" step="0.01" data-field="discount_percent" value="0" class="crm-input py-1.5"></td>
                    <td class="py-2.5 pr-2"><input type="number" step="0.01" data-field="tax_percent" value="0" class="crm-input py-1.5"></td>
                    <td class="py-2.5 text-right font-mono text-xs font-semibold text-slate-800 tabular-nums" data-line-total>0.00</td>
                    <td class="py-2.5 text-right"><button type="button" data-remove-item class="text-rose-600 hover:text-rose-700 text-base leading-none px-1">&times;</button></td>
                </tr>
            </template>
        </div>

        <div class="flex justify-end border-t border-slate-100 pt-4">
            <div class="w-72 rounded-xl bg-slate-50 p-4 space-y-1.5 text-sm border border-slate-200/80">
                <div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span class="font-mono tabular-nums text-slate-700" data-subtotal>0.00</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Discount</span><span class="font-mono tabular-nums text-slate-700" data-discount-total>0.00</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Tax</span><span class="font-mono tabular-nums text-slate-700" data-tax-total>0.00</span></div>
                <div class="flex justify-between font-bold text-base text-slate-900 border-t border-slate-200 pt-2"><span>Grand Total</span><span class="font-mono tabular-nums" data-grand-total>0.00</span></div>
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('quotations.index') }}" class="crm-btn-secondary">Cancel</a>
            <button type="submit" class="crm-btn-primary">Save Draft Quotation</button>
        </div>
    </form>
</x-layouts.app>
