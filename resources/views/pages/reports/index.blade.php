<x-layouts.app title="Reports — Sales CRM" header="Reports & Executive Analytics">
    <search aria-label="Report filters" class="block crm-card p-5">
        <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap gap-3 items-end">
            <div>
                <label for="filter-date-from" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">From</label>
                <input id="filter-date-from" type="date" name="date_from" value="{{ old('date_from', $dateFrom->toDateString()) }}" class="mt-1 min-h-11 crm-input w-auto">
            </div>
            <div>
                <label for="filter-date-to" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">To</label>
                <input id="filter-date-to" type="date" name="date_to" value="{{ old('date_to', $dateTo->toDateString()) }}" class="mt-1 min-h-11 crm-input w-auto">
            </div>
            <div>
                <label for="filter-pipeline" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Pipeline</label>
                <select id="filter-pipeline" name="pipeline_id" class="mt-1 min-h-11 crm-select w-auto min-w-40">
                    <option value="">All Pipelines</option>
                    @foreach ($pipelines as $pipeline)
                        <option value="{{ $pipeline->id }}" @selected((string) old('pipeline_id', $pipelineId ?? '') === (string) $pipeline->id)>{{ $pipeline->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter-team" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Team</label>
                <select id="filter-team" name="team_id" class="mt-1 min-h-11 crm-select w-auto min-w-36">
                    <option value="">All Teams</option>
                    @foreach ($teams as $team)
                        <option value="{{ $team->id }}" @selected((string) old('team_id', $teamId ?? '') === (string) $team->id)>{{ $team->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter-owner" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Owner</label>
                <select id="filter-owner" name="owner_id" class="mt-1 min-h-11 crm-select w-auto min-w-36">
                    <option value="">All Owners</option>
                    @foreach ($owners as $owner)
                        <option value="{{ $owner->id }}" @selected((string) old('owner_id', $ownerId ?? '') === (string) $owner->id)>{{ $owner->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="min-h-11 crm-btn-dark">Apply Filters</button>
            <a href="{{ route('reports.index') }}" class="min-h-11 crm-btn-secondary">Reset</a>
        </form>
    </search>

    <div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-crm.stat-card accent="brand" label="Open Deals" :value="number_format($summary['kpis']['open_count'])" subtext="Expected close in range, or no date set" />
            <x-crm.stat-card accent="emerald" label="Won Deals" :value="number_format($summary['kpis']['won_count'])" subtext="Closed in range" />
            <x-crm.stat-card accent="indigo" label="Win Rate" :value="number_format($summary['kpis']['win_rate'], 1).'%'" subtext="{{ $summary['kpis']['won_count'] }} won / {{ $summary['kpis']['lost_count'] }} lost" />
            <x-crm.stat-card accent="amber" label="Lead Conversion" :value="number_format($summary['kpis']['lead_conversion_rate'], 1).'%'" subtext="{{ $summary['kpis']['converted_leads'] }} of {{ $summary['kpis']['total_leads'] }} new leads" />
        </div>
        <p class="mt-2 text-xs text-slate-500">Open pipeline uses expected close dates; won and lost figures use the date each deal was closed.</p>
    </div>

    <div class="crm-card overflow-hidden">
        <div class="px-6 pt-5 pb-3 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-900">Revenue by Currency</h3>
            <p class="text-xs text-slate-500 mt-0.5">Amounts are never added across currencies.</p>
        </div>
        <div class="overflow-x-auto" role="region" aria-labelledby="currency-totals-caption" tabindex="0">
            <table class="crm-table">
                <caption id="currency-totals-caption" class="sr-only">Open, weighted, won and lost amounts grouped by deal currency</caption>
                <thead class="crm-thead">
                <tr>
                    <th scope="col" class="px-6 py-3">Currency</th>
                    <th scope="col" class="px-6 py-3 text-right">Open Pipeline</th>
                    <th scope="col" class="px-6 py-3 text-right">Weighted Forecast</th>
                    <th scope="col" class="px-6 py-3 text-right">Won Revenue</th>
                    <th scope="col" class="px-6 py-3 text-right">Won</th>
                    <th scope="col" class="px-6 py-3 text-right">Lost</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse ($summary['currency_totals'] as $row)
                    <tr class="hover:bg-slate-50/70 transition">
                        <th scope="row" class="px-6 py-3.5 font-bold text-slate-900 text-left">{{ $row['currency'] }}</th>
                        <td class="px-6 py-3.5 text-right font-mono text-xs text-slate-700 tabular-nums">{{ number_format($row['open_value'], 2) }}</td>
                        <td class="px-6 py-3.5 text-right font-mono text-xs text-slate-700 tabular-nums">{{ number_format($row['weighted_value'], 2) }}</td>
                        <td class="px-6 py-3.5 text-right font-mono text-xs font-semibold text-emerald-700 tabular-nums">{{ number_format($row['won_value'], 2) }}</td>
                        <td class="px-6 py-3.5 text-right tabular-nums font-medium">{{ $row['won_count'] }}</td>
                        <td class="px-6 py-3.5 text-right tabular-nums text-slate-500">{{ $row['lost_count'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-8 text-center text-slate-500">No deals in this range.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @php
        $maxStageDeals = max(1, (int) collect($summary['stage_breakdown'])->max('deal_count'));
        $maxRepValue = max(1.0, (float) collect($summary['leaderboard'])->max('won_value'));
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="crm-card p-6 space-y-4">
            <h3 class="text-sm font-semibold text-slate-900 border-b border-slate-100 pb-2.5">Open Pipeline by Stage</h3>
            <div class="space-y-3 text-sm">
                @forelse ($summary['stage_breakdown'] as $row)
                    @php
                        $pct = min(100, round(((int) $row['deal_count'] / $maxStageDeals) * 100));
                    @endphp
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-medium text-slate-800">
                                {{ $row['name'] }} <span class="text-slate-400 font-normal">({{ $row['deal_count'] }})</span>
                                @if ($pipelineId === null)
                                    <span class="block text-xs text-slate-400 font-normal">{{ $row['pipeline_name'] }}</span>
                                @endif
                            </span>
                            <span class="font-mono text-xs font-semibold text-slate-900 whitespace-nowrap tabular-nums">{{ $row['currency'] }} {{ number_format($row['total_value'], 2) }}</span>
                        </div>
                        <div class="h-1.5 w-full rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full rounded-full bg-brand-500" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-slate-500 py-4 text-center">No open deals in this range.</p>
                @endforelse
            </div>
        </div>

        <div class="crm-card p-6 space-y-4">
            <h3 class="text-sm font-semibold text-slate-900 border-b border-slate-100 pb-2.5">Rep Won Leaderboard</h3>
            <div class="space-y-3 text-sm">
                @forelse ($summary['leaderboard'] as $rep)
                    @php
                        $repPct = min(100, round(((float) $rep['won_value'] / $maxRepValue) * 100));
                    @endphp
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-medium text-slate-800">{{ $rep['name'] }} <span class="text-slate-400 font-normal">({{ $rep['won_deals'] }} deals)</span></span>
                            <span class="font-mono text-xs font-semibold text-emerald-700 whitespace-nowrap tabular-nums">{{ $rep['currency'] }} {{ number_format($rep['won_value'], 2) }}</span>
                        </div>
                        <div class="h-1.5 w-full rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full rounded-full bg-emerald-500" style="width: {{ $repPct }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-slate-500 py-4 text-center">No won deals in this range.</p>
                @endforelse
            </div>
        </div>

        <div class="crm-card p-6 space-y-4 lg:col-span-2">
            <h3 class="text-sm font-semibold text-slate-900 border-b border-slate-100 pb-2.5">Lead Sources</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                @forelse ($summary['lead_sources'] as $source)
                    @php
                        $convPct = $source['total_leads'] > 0
                            ? round(((float) $source['converted_leads'] / (float) $source['total_leads']) * 100, 1)
                            : 0.0;
                    @endphp
                    <div class="rounded-xl border border-slate-200/80 bg-slate-50/50 p-3.5 space-y-2">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-semibold text-slate-800">{{ \App\Enums\LeadSource::tryFrom($source['source'])?->label() ?? ucfirst(str_replace('_', ' ', $source['source'])) }}</span>
                            <span class="text-xs font-medium text-slate-600 tabular-nums">
                                {{ $source['total_leads'] }} leads &middot; {{ $source['converted_leads'] }} converted ({{ number_format($convPct, 1) }}%)
                            </span>
                        </div>
                        <div class="h-1.5 w-full rounded-full bg-slate-200 overflow-hidden">
                            <div class="h-full rounded-full bg-indigo-500" style="width: {{ min(100, $convPct) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-slate-500 py-4 text-center md:col-span-2">No leads created in this range.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.app>
