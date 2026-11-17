<x-layouts.app title="New Quotation — Sales CRM" header="Create Quotation">
    <form method="POST" action="{{ route('quotations.store') }}" data-module="quotation-calculator" class="bg-white rounded-xl border border-slate-200 p-6 space-y-6">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-slate-700">Deal *</label>
                <select name="deal_id" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @foreach ($deals as $deal)
                        <option value="{{ $deal->id }}" @selected((string) $selectedDealId === (string) $deal->id)>{{ $deal->title }} ({{ $deal->company?->name }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-700">Issue Date *</label>
                <input type="date" name="issue_date" value="{{ now()->toDateString() }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-700">Valid Until *</label>
                <input type="date" name="valid_until" value="{{ now()->addDays(30)->toDateString() }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
        </div>
        <input type="hidden" name="currency" value="{{ config('crm.currency', 'BDT') }}">

        <div>
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold text-slate-900">Line Items</h3>
                <button type="button" data-add-item class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-medium">+ Add Item</button>
            </div>
            <table class="min-w-full text-sm">
                <thead>
                <tr class="text-left text-xs text-slate-500 border-b">
                    <th class="py-2">Item</th>
                    <th class="py-2 w-24">Qty</th>
                    <th class="py-2 w-32">Unit Price</th>
                    <th class="py-2 w-24">Disc %</th>
                    <th class="py-2 w-24">Tax %</th>
                    <th class="py-2 w-28 text-right">Line Total</th>
                    <th class="py-2 w-12"></th>
                </tr>
                </thead>
                <tbody data-items-body class="divide-y divide-slate-100">
                <tr data-item-row>
                    <td class="py-2 pr-2"><input type="text" data-field="item_name" required placeholder="Item name" class="w-full rounded border border-slate-300 px-2 py-1"></td>
                    <td class="py-2 pr-2"><input type="number" step="0.01" data-field="quantity" value="1" required class="w-full rounded border border-slate-300 px-2 py-1"></td>
                    <td class="py-2 pr-2"><input type="number" step="0.01" data-field="unit_price" value="0" required class="w-full rounded border border-slate-300 px-2 py-1"></td>
                    <td class="py-2 pr-2"><input type="number" step="0.01" data-field="discount_percent" value="0" class="w-full rounded border border-slate-300 px-2 py-1"></td>
                    <td class="py-2 pr-2"><input type="number" step="0.01" data-field="tax_percent" value="0" class="w-full rounded border border-slate-300 px-2 py-1"></td>
                    <td class="py-2 text-right font-mono text-xs" data-line-total>0.00</td>
                    <td class="py-2 text-right"><button type="button" data-remove-item class="text-rose-600 text-xs">&times;</button></td>
                </tr>
                </tbody>
            </table>

            <template data-item-template>
                <tr data-item-row>
                    <td class="py-2 pr-2"><input type="text" data-field="item_name" required placeholder="Item name" class="w-full rounded border border-slate-300 px-2 py-1"></td>
                    <td class="py-2 pr-2"><input type="number" step="0.01" data-field="quantity" value="1" required class="w-full rounded border border-slate-300 px-2 py-1"></td>
                    <td class="py-2 pr-2"><input type="number" step="0.01" data-field="unit_price" value="0" required class="w-full rounded border border-slate-300 px-2 py-1"></td>
                    <td class="py-2 pr-2"><input type="number" step="0.01" data-field="discount_percent" value="0" class="w-full rounded border border-slate-300 px-2 py-1"></td>
                    <td class="py-2 pr-2"><input type="number" step="0.01" data-field="tax_percent" value="0" class="w-full rounded border border-slate-300 px-2 py-1"></td>
                    <td class="py-2 text-right font-mono text-xs" data-line-total>0.00</td>
                    <td class="py-2 text-right"><button type="button" data-remove-item class="text-rose-600 text-xs">&times;</button></td>
                </tr>
            </template>
        </div>

        <div class="flex justify-end">
            <div class="w-64 space-y-1 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">Subtotal:</span><span class="font-mono" data-subtotal>0.00</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Discount:</span><span class="font-mono" data-discount-total>0.00</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Tax:</span><span class="font-mono" data-tax-total>0.00</span></div>
                <div class="flex justify-between font-bold text-base border-t pt-1"><span>Grand Total:</span><span class="font-mono" data-grand-total>0.00</span></div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2 text-sm font-semibold text-white">Save Draft Quotation</button>
        </div>
    </form>
</x-layouts.app>
