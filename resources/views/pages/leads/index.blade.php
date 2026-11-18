<x-layouts.app title="Leads — Sales CRM" header="Leads Management">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <form method="GET" action="{{ route('leads.index') }}" class="flex flex-wrap items-center gap-2">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search leads..." class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
            <select name="status" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                <option value="">All Statuses</option>
                @foreach (\App\Enums\LeadStatus::cases() as $case)
                    <option value="{{ $case->value }}" @selected($status === $case->value)>{{ $case->label() }}</option>
                @endforeach
            </select>
            <select name="source" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                <option value="">All Sources</option>
                @foreach (\App\Enums\LeadSource::cases() as $case)
                    <option value="{{ $case->value }}" @selected($source === $case->value)>{{ $case->label() }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-lg bg-slate-800 px-3 py-2 text-sm font-medium text-white">Filter</button>
        </form>
    </div>

    @can('create', \App\Models\Lead::class)
        <form method="POST" action="{{ route('leads.store') }}" class="bg-white rounded-xl border border-slate-200 p-5 grid grid-cols-1 md:grid-cols-4 lg:grid-cols-8 gap-3">
            @csrf
            <input type="text" name="first_name" placeholder="First Name *" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <input type="text" name="last_name" placeholder="Last Name" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <input type="text" name="company_name" placeholder="Company" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <input type="email" name="email" placeholder="Email" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <input type="text" name="phone" placeholder="Phone" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <select name="source" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @foreach (\App\Enums\LeadSource::cases() as $case)
                    @continue($case === \App\Enums\LeadSource::Import)
                    <option value="{{ $case->value }}">{{ $case->label() }}</option>
                @endforeach
            </select>
            @if ($owners->isNotEmpty() && (auth()->user()->can('leads.update.team') || auth()->user()->can('leads.update.all')))
                <select name="owner_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="{{ auth()->id() }}">Assign to me</option>
                    @foreach ($owners as $owner)
                        @continue($owner->id === auth()->id())
                        <option value="{{ $owner->id }}">{{ $owner->name }}</option>
                    @endforeach
                </select>
            @endif
            <input type="hidden" name="status" value="new">
            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Add Lead</button>
        </form>
    @endcan

    <div class="bg-white rounded-xl border border-slate-200 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
            <tr>
                <th class="px-4 py-3">Name</th>
                <th class="px-4 py-3">Company</th>
                <th class="px-4 py-3">Contact</th>
                <th class="px-4 py-3">Source</th>
                <th class="px-4 py-3">Score</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Owner</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($leads as $lead)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 font-medium">
                        <a href="{{ route('leads.show', $lead) }}" class="text-brand-600 hover:underline">{{ $lead->full_name }}</a>
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $lead->company_name ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $lead->email ?? $lead->phone }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $lead->source?->label() }}</td>
                    <td class="px-4 py-3 font-mono text-xs">{{ $lead->score }}/100</td>
                    <td class="px-4 py-3"><x-crm.status-badge :status="$lead->status" /></td>
                    <td class="px-4 py-3 text-slate-600">{{ $lead->owner?->name }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-6 text-center text-slate-500">No leads found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{ $leads->links() }}
</x-layouts.app>
