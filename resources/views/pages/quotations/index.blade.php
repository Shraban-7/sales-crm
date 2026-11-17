<x-layouts.app title="Quotations — Sales CRM" header="Quotations &amp; Proposals">
    @can('create', \App\Models\Quotation::class)
        <div class="flex justify-end">
            <a href="{{ route('quotations.create') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white">New Quotation</a>
        </div>
    @endcan

    <div class="bg-white rounded-xl border border-slate-200 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
            <tr>
                <th class="px-4 py-3">Quote #</th>
                <th class="px-4 py-3">Deal</th>
                <th class="px-4 py-3">Company</th>
                <th class="px-4 py-3">Grand Total</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Valid Until</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($quotations as $quotation)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 font-medium"><a href="{{ route('quotations.show', $quotation) }}" class="text-brand-600 hover:underline">{{ $quotation->quote_number }} (v{{ $quotation->version }})</a></td>
                    <td class="px-4 py-3 text-slate-700">{{ $quotation->deal?->title }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $quotation->company?->name }}</td>
                    <td class="px-4 py-3 font-mono text-xs">{{ $quotation->currency }} {{ number_format((float) $quotation->grand_total, 2) }}</td>
                    <td class="px-4 py-3"><x-crm.status-badge :status="$quotation->status" /></td>
                    <td class="px-4 py-3 text-slate-500">{{ $quotation->valid_until->toDateString() }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">No quotations found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $quotations->links() }}
</x-layouts.app>
