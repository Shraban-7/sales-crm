<x-layouts.app title="Leads — Sale Fusion CRM" header="Leads Management">
    <div class="crm-card p-4 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" action="{{ route('leads.index') }}" class="flex flex-wrap items-center gap-2.5 flex-1">
            <div class="relative flex-1 min-w-52 max-w-sm">
                <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 6.15 6.15a7.5 7.5 0 0 0 10.5 10.5Z" />
                </svg>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search leads by name, email, or company..." class="crm-input pl-9">
            </div>
            <select name="status" class="crm-select w-auto min-w-36">
                <option value="">All Statuses</option>
                @foreach (\App\Enums\LeadStatus::cases() as $case)
                    <option value="{{ $case->value }}" @selected($status === $case->value)>{{ $case->label() }}</option>
                @endforeach
            </select>
            <select name="source" class="crm-select w-auto min-w-36">
                <option value="">All Sources</option>
                @foreach (\App\Enums\LeadSource::cases() as $case)
                    <option value="{{ $case->value }}" @selected($source === $case->value)>{{ $case->label() }}</option>
                @endforeach
            </select>
            <button type="submit" class="crm-btn-dark">Filter</button>
            @if ($search || $status || $source)
                <a href="{{ route('leads.index') }}" class="crm-btn-secondary">Reset</a>
            @endif
        </form>
        <a href="{{ route('imports.index') }}" class="crm-btn-secondary">
            <svg class="h-4 w-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
            </svg>
            Import / Export
        </a>
    </div>

    @can('create', \App\Models\Lead::class)
        <form method="POST" action="{{ route('leads.store') }}" class="crm-card p-5 space-y-3">
            @csrf
            <div class="flex items-center justify-between">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Quick Add Lead</h2>
                <span class="text-xs text-slate-400">Scored automatically on creation</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <input type="text" name="first_name" placeholder="First Name *" required class="crm-input">
                <input type="text" name="last_name" placeholder="Last Name" class="crm-input">
                <input type="text" name="company_name" placeholder="Company" class="crm-input">
                <input type="email" name="email" placeholder="Email" class="crm-input">
                <input type="text" name="phone" placeholder="Phone" class="crm-input">
                <select name="source" required class="crm-select w-full">
                    @foreach (\App\Enums\LeadSource::cases() as $case)
                        @continue($case === \App\Enums\LeadSource::Import)
                        <option value="{{ $case->value }}">{{ $case->label() }}</option>
                    @endforeach
                </select>
                @if ($owners->isNotEmpty() && (auth()->user()->can('leads.update.team') || auth()->user()->can('leads.update.all')))
                    <select name="owner_id" class="crm-select w-full">
                        <option value="{{ auth()->id() }}">Assign to me</option>
                        @foreach ($owners as $owner)
                            @continue($owner->id === auth()->id())
                            <option value="{{ $owner->id }}">{{ $owner->name }}</option>
                        @endforeach
                    </select>
                @else
                    <div class="hidden lg:block"></div>
                @endif
                <input type="hidden" name="status" value="new">
                <button type="submit" class="crm-btn-primary">Add Lead</button>
            </div>
        </form>
    @endcan

    <div class="crm-table-wrap">
        <table class="crm-table">
            <thead class="crm-thead">
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
                @php
                    $scorePct = max(0, min(100, (int) $lead->score));
                    $scoreColor = $scorePct >= 70 ? 'bg-emerald-500' : ($scorePct >= 40 ? 'bg-amber-500' : 'bg-slate-400');
                @endphp
                <tr class="hover:bg-slate-50/80 transition">
                    <td class="px-4 py-3">
                        <a href="{{ route('leads.show', $lead) }}" class="font-semibold text-slate-900 hover:text-brand-600 transition">
                            {{ $lead->full_name }}
                        </a>
                        @if ($lead->job_title)
                            <p class="text-xs text-slate-400">{{ $lead->job_title }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $lead->company_name ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $lead->email ?? $lead->phone ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $lead->source?->label() }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2 min-w-24">
                            <div class="h-1.5 flex-1 rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full rounded-full {{ $scoreColor }}" style="width: {{ $scorePct }}%"></div>
                            </div>
                            <span class="font-mono text-xs text-slate-600 tabular-nums">{{ $scorePct }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3"><x-crm.status-badge :status="$lead->status" /></td>
                    <td class="px-4 py-3 text-slate-600">{{ $lead->owner?->name }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-10 text-center text-slate-500">No leads found matching your filters.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{ $leads->links() }}
</x-layouts.app>
