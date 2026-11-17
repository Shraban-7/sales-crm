<x-layouts.app title="Deals — Sales CRM" header="Deals &amp; Opportunities">
    <form method="GET" action="{{ route('deals.index') }}" class="flex flex-wrap gap-2">
        <input type="text" name="search" value="{{ $search }}" placeholder="Search deals..." class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
        <select name="status" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
            <option value="">All Statuses</option>
            @foreach (\App\Enums\DealStatus::cases() as $case)
                <option value="{{ $case->value }}" @selected($status === $case->value)>{{ $case->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-lg bg-slate-800 px-3 py-2 text-sm font-medium text-white">Filter</button>
    </form>

    @can('create', \App\Models\Deal::class)
        @php $firstPipeline = $pipelines->first(); @endphp
        <form method="POST" action="{{ route('deals.store') }}" class="bg-white rounded-xl border border-slate-200 p-5 grid grid-cols-1 md:grid-cols-6 gap-3">
            @csrf
            <input type="text" name="title" placeholder="Deal Title *" required class="md:col-span-2 rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <select name="company_id" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Company *</option>
                @foreach ($companies as $company)
                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                @endforeach
            </select>
            <input type="hidden" name="pipeline_id" value="{{ $firstPipeline?->id }}">
            <select name="pipeline_stage_id" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @foreach ($firstPipeline?->stages ?? [] as $stage)
                    <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                @endforeach
            </select>
            <input type="number" step="0.01" name="value" placeholder="Value *" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <input type="hidden" name="currency" value="{{ config('crm.currency', 'BDT') }}">
            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Create Deal</button>
        </form>
    @endcan

    <div class="bg-white rounded-xl border border-slate-200 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
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
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 font-medium"><a href="{{ route('deals.show', $deal) }}" class="text-brand-600 hover:underline">{{ $deal->title }}</a></td>
                    <td class="px-4 py-3 text-slate-600">{{ $deal->company?->name }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $deal->stage?->name }}</td>
                    <td class="px-4 py-3 font-mono text-xs">{{ $deal->currency }} {{ number_format((float) $deal->value, 2) }}</td>
                    <td class="px-4 py-3 text-xs">{{ (int) $deal->probability }}%</td>
                    <td class="px-4 py-3"><x-crm.status-badge :status="$deal->status" /></td>
                    <td class="px-4 py-3 text-slate-600">{{ $deal->owner?->name }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-6 text-center text-slate-500">No deals found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $deals->links() }}
</x-layouts.app>
