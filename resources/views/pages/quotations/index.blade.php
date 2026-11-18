<x-layouts.app title="Quotations — Sales CRM" header="Quotations & Proposals">
    @can('create', \App\Models\Quotation::class)
        <div class="flex justify-end">
            <a href="{{ route('quotations.create') }}" class="crm-btn-primary">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                New Quotation
            </a>
        </div>
    @endcan

    <div class="crm-table-wrap">
        <table class="crm-table">
            <thead class="crm-thead">
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
                <tr class="hover:bg-slate-50/80 transition">
                    <td class="px-4 py-3 font-medium">
                        <a href="{{ route('quotations.show', $quotation) }}" class="font-semibold text-brand-600 hover:underline">{{ $quotation->quote_number }} (v{{ $quotation->version }})</a>
                    </td>
                    <td class="px-4 py-3 text-slate-700 font-medium">{{ $quotation->deal?->title }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $quotation->company?->name ?? '—' }}</td>
                    <td class="px-4 py-3 font-mono text-xs font-semibold text-slate-900 tabular-nums">{{ $quotation->currency }} {{ number_format((float) $quotation->grand_total, 2) }}</td>
                    <td class="px-4 py-3"><x-crm.status-badge :status="$quotation->status" /></td>
                    <td class="px-4 py-3 text-slate-500 tabular-nums">{{ $quotation->valid_until->toDateString() }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">No quotations found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $quotations->links() }}
</x-layouts.app>
