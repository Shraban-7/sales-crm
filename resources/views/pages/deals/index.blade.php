<x-layouts.app title="Deals — Sales CRM" header="Deals & Opportunities">
    <div class="crm-card p-4 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" action="{{ route('deals.index') }}" class="flex flex-wrap items-center gap-2.5 flex-1">
            <div class="relative flex-1 min-w-52 max-w-sm">
                <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 6.15 6.15a7.5 7.5 0 0 0 10.5 10.5Z" />
                </svg>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search deals..." class="crm-input pl-9">
            </div>
            <select name="status" class="crm-select w-auto min-w-36">
                <option value="">All Statuses</option>
                @foreach (\App\Enums\DealStatus::cases() as $case)
                    <option value="{{ $case->value }}" @selected($status === $case->value)>{{ $case->label() }}</option>
                @endforeach
            </select>
            <button type="submit" class="crm-btn-dark">Filter</button>
            @if ($search || $status)
                <a href="{{ route('deals.index') }}" class="crm-btn-secondary">Reset</a>
            @endif
        </form>
        <a href="{{ route('pipelines.kanban') }}" class="crm-btn-secondary">
            <svg class="h-4 w-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h4.5v13.5h-4.5V5.25ZM9.75 5.25h4.5v8.25h-4.5V5.25ZM15.75 5.25h4.5v10.5h-4.5V5.25Z" />
            </svg>
            Kanban View
        </a>
    </div>

    @can('create', \App\Models\Deal::class)
        @php $firstPipeline = $pipelines->first(); @endphp
        <form method="POST" action="{{ route('deals.store') }}" class="crm-card p-5 space-y-3">
            @csrf
            <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Quick Create Deal</h2>
            <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                <input type="text" name="title" placeholder="Deal Title *" required class="md:col-span-2 crm-input">
                <select name="company_id" required class="crm-select">
                    <option value="">Company *</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="pipeline_id" value="{{ $firstPipeline?->id }}">
                <select name="pipeline_stage_id" required class="crm-select">
                    @foreach ($firstPipeline?->stages ?? [] as $stage)
                        <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                    @endforeach
                </select>
                <input type="number" step="0.01" name="value" placeholder="Value *" required class="crm-input">
                <input type="hidden" name="currency" value="{{ config('crm.currency', 'BDT') }}">
                <button type="submit" class="crm-btn-primary">Create Deal</button>
            </div>
        </form>
    @endcan

    <div class="crm-table-wrap">
        <table class="crm-table">
            <thead class="crm-thead">
            <tr>
                <th class="px-4 py-3">Deal</th>
                <th class="px-4 py-3">Company</th>
                <th class="px-4 py-3">Stage</th>
                <th class="px-4 py-3">Value</th>
                <th class="px-4 py-3">Prob.</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Owner</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($deals as $deal)
                <tr class="hover:bg-slate-50/80 transition">
                    <td class="px-4 py-3 font-medium">
                        <a href="{{ route('deals.show', $deal) }}" class="font-semibold text-slate-900 hover:text-brand-600 transition">{{ $deal->title }}</a>
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $deal->company?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-700 font-medium">{{ $deal->stage?->name }}</td>
                    <td class="px-4 py-3 font-mono text-xs font-semibold text-slate-900 tabular-nums">{{ $deal->currency }} {{ number_format((float) $deal->value, 2) }}</td>
                    <td class="px-4 py-3 text-xs text-slate-600 tabular-nums">{{ (int) $deal->probability }}%</td>
                    <td class="px-4 py-3"><x-crm.status-badge :status="$deal->status" /></td>
                    <td class="px-4 py-3 text-slate-600">{{ $deal->owner?->name }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-10 text-center text-slate-500">No deals found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $deals->links() }}
</x-layouts.app>
