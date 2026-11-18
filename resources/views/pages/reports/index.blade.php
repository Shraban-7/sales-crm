<x-layouts.app title="Reports — Sales CRM" header="Reports & Executive Analytics">
    <form method="GET" action="{{ route('reports.index') }}" class="bg-white rounded-xl border border-slate-200 p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-slate-600">From</label>
            <input type="date" name="date_from" value="{{ $dateFrom->toDateString() }}" class="mt-1 rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600">To</label>
            <input type="date" name="date_to" value="{{ $dateTo->toDateString() }}" class="mt-1 rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600">Pipeline</label>
            <select name="pipeline_id" class="mt-1 rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                <option value="">All Pipelines</option>
                @foreach ($pipelines as $pipeline)
                    <option value="{{ $pipeline->id }}" @selected($pipelineId === $pipeline->id)>{{ $pipeline->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600">Team</label>
            <select name="team_id" class="mt-1 rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                <option value="">All Teams</option>
                @foreach ($teams as $team)
                    <option value="{{ $team->id }}" @selected($teamId === $team->id)>{{ $team->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600">Owner</label>
            <select name="owner_id" class="mt-1 rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                <option value="">All Owners</option>
                @foreach ($owners as $owner)
                    <option value="{{ $owner->id }}" @selected($ownerId === $owner->id)>{{ $owner->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">Apply</button>
        <a href="{{ route('reports.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600">Reset</a>
    </form>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-crm.stat-card label="Open Deals" value="{{ number_format($summary['kpis']['open_count']) }}" subtext="Expected to close in range" />
        <x-crm.stat-card label="Won Deals" value="{{ number_format($summary['kpis']['won_count']) }}" subtext="Closed in range" />
        <x-crm.stat-card label="Win Rate" value="{{ number_format($summary['kpis']['win_rate'], 1) }}%" subtext="{{ $summary['kpis']['won_count'] }} won / {{ $summary['kpis']['lost_count'] }} lost" />
        <x-crm.stat-card label="Lead Conversion" value="{{ number_format($summary['kpis']['lead_conversion_rate'], 1) }}%" subtext="{{ $summary['kpis']['converted_leads'] }} of {{ $summary['kpis']['total_leads'] }} new leads" />
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-x-auto">
        <div class="px-5 pt-5">
            <h3 class="text-sm font-semibold text-slate-900">Revenue by Currency</h3>
            <p class="text-xs text-slate-500 mt-1">Amounts are never added across currencies.</p>
        </div>
        <table class="min-w-full divide-y divide-slate-200 text-sm mt-3">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
            <tr>
                <th class="px-5 py-3">Currency</th>
                <th class="px-5 py-3 text-right">Open Pipeline</th>
                <th class="px-5 py-3 text-right">Weighted Forecast</th>
                <th class="px-5 py-3 text-right">Won Revenue</th>
                <th class="px-5 py-3 text-right">Won</th>
                <th class="px-5 py-3 text-right">Lost</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($summary['currency_totals'] as $row)
                <tr>
                    <td class="px-5 py-3 font-semibold">{{ $row->currency }}</td>
                    <td class="px-5 py-3 text-right font-mono text-xs">{{ number_format((float) $row->open_value, 2) }}</td>
                    <td class="px-5 py-3 text-right font-mono text-xs">{{ number_format((float) $row->weighted_value, 2) }}</td>
                    <td class="px-5 py-3 text-right font-mono text-xs">{{ number_format((float) $row->won_value, 2) }}</td>
                    <td class="px-5 py-3 text-right">{{ $row->won_count }}</td>
                    <td class="px-5 py-3 text-right">{{ $row->lost_count }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-5 py-6 text-center text-slate-500">No deals in this range.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl border border-slate-200 p-5 space-y-3">
            <h3 class="text-sm font-semibold text-slate-900">Open Pipeline by Stage</h3>
            <div class="space-y-2 text-sm">
                @forelse ($summary['stage_breakdown'] as $row)
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <span>{{ $row->name }} ({{ $row->deal_count }})</span>
                        <span class="font-mono text-xs">{{ $row->currency }} {{ number_format((float) $row->total_value, 2) }}</span>
                    </div>
                @empty
                    <p class="text-slate-500">No open deals expected to close in this range.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5 space-y-3">
            <h3 class="text-sm font-semibold text-slate-900">Rep Won Leaderboard</h3>
            <div class="space-y-2 text-sm">
                @forelse ($summary['leaderboard'] as $rep)
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <span>{{ $rep->name }} ({{ $rep->won_deals }} deals)</span>
                        <span class="font-mono text-xs">{{ $rep->currency }} {{ number_format((float) $rep->won_value, 2) }}</span>
                    </div>
                @empty
                    <p class="text-slate-500">No won deals in this range.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5 space-y-3 lg:col-span-2">
            <h3 class="text-sm font-semibold text-slate-900">Lead Sources</h3>
            <div class="space-y-2 text-sm">
                @forelse ($summary['lead_sources'] as $source)
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <span>{{ \App\Enums\LeadSource::tryFrom((string) $source->source)?->label() ?? ucfirst(str_replace('_', ' ', (string) $source->source)) }}</span>
                        <span class="text-xs text-slate-500">
                            {{ $source->total_leads }} leads &middot;
                            {{ $source->converted_leads }} converted
                            ({{ $source->total_leads > 0 ? number_format(((float) $source->converted_leads / (float) $source->total_leads) * 100, 1) : '0.0' }}%)
                        </span>
                    </div>
                @empty
                    <p class="text-slate-500">No leads created in this range.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.app>
