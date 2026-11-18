<x-layouts.app title="Dashboard — Sales CRM" header="Executive Dashboard">
    @php
        $openPipelineLines = $openPipelineByCurrency
            ->map(fn ($row) => $row->currency.' '.number_format((float) $row->total_value, 2))
            ->all();
        $wonRevenueLines = $wonRevenueByCurrency
            ->map(fn ($row) => $row->currency.' '.number_format((float) $row->total_value, 2))
            ->all();
    @endphp

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-xs font-medium uppercase tracking-wider text-slate-400">Workspace Overview</p>
            <p class="text-sm text-slate-600 mt-0.5">Live pipeline value, closed revenue, and upcoming customer commitments.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('pipelines.kanban') }}" class="crm-btn-secondary">
                <svg class="h-4 w-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h4.5v13.5h-4.5V5.25ZM9.75 5.25h4.5v8.25h-4.5V5.25ZM15.75 5.25h4.5v10.5h-4.5V5.25Z" />
                </svg>
                Pipeline Board
            </a>
            <a href="{{ route('leads.index') }}" class="crm-btn-primary">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                New Lead
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <x-crm.stat-card
            accent="brand"
            label="Open Pipeline"
            :lines="$openPipelineLines ?: [config('crm.currency').' 0.00']"
            :subtext="$openDealsCount.' open deal'.($openDealsCount === 1 ? '' : 's')"
        />
        <x-crm.stat-card
            accent="emerald"
            label="Won Revenue"
            :lines="$wonRevenueLines ?: [config('crm.currency').' 0.00']"
            :subtext="$wonDealsCount.' won deal'.($wonDealsCount === 1 ? '' : 's')"
        />
        <x-crm.stat-card
            accent="indigo"
            label="Active Leads"
            :value="$activeLeadsCount"
            subtext="Not yet converted or disqualified"
        />
        <x-crm.stat-card
            :accent="$overdueTasksCount > 0 ? 'rose' : 'amber'"
            label="Pending Tasks"
            :value="$pendingTasksCount"
            :subtext="$overdueTasksCount > 0 ? $overdueTasksCount.' overdue' : 'Nothing overdue'"
        />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <section class="crm-card p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">Recent Deals</h2>
                        <p class="text-xs text-slate-500">Latest opportunities in motion</p>
                    </div>
                </div>
                <a href="{{ route('deals.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700 hover:underline">View all &rarr;</a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($recentDeals as $deal)
                    <div class="py-3 flex items-center justify-between gap-4 hover:bg-slate-50/70 -mx-2 px-2 rounded-lg transition">
                        <div class="min-w-0">
                            <a href="{{ route('deals.show', $deal) }}" class="text-sm font-semibold text-slate-900 hover:text-brand-600 truncate block">
                                {{ $deal->title }}
                            </a>
                            <p class="text-xs text-slate-500 mt-0.5 truncate">
                                {{ $deal->company?->name ?? 'Independent Deal' }} &middot; {{ $deal->stage?->name ?? 'Unassigned stage' }}
                            </p>
                        </div>
                        <div class="text-right shrink-0 space-y-1">
                            <p class="text-sm font-semibold text-slate-900 tabular-nums">{{ $deal->currency }} {{ number_format((float) $deal->value, 2) }}</p>
                            <x-crm.status-badge :status="$deal->status" />
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center">
                        <p class="text-sm font-medium text-slate-600">No deals found</p>
                        <p class="text-xs text-slate-400 mt-1">Convert a qualified lead or create a deal to populate your pipeline.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="crm-card p-6 space-y-5">
            <div>
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-sm font-semibold text-slate-900">Due Tasks</h2>
                            <p class="text-xs text-slate-500">Prioritized action queue</p>
                        </div>
                    </div>
                    <a href="{{ route('tasks.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700 hover:underline">View all &rarr;</a>
                </div>

                <div class="divide-y divide-slate-100 mt-1">
                    @forelse ($dueTasks as $task)
                        <div class="py-3 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-slate-900 truncate">{{ $task->title }}</p>
                                <p class="text-xs mt-0.5 {{ $task->due_at?->isPast() ? 'text-rose-600 font-medium' : 'text-slate-500' }}">
                                    @if ($task->due_at)
                                        Due {{ $task->due_at->diffForHumans() }}
                                    @else
                                        No due date
                                    @endif
                                    &middot; {{ $task->assignee?->name ?? 'Unassigned' }}
                                </p>
                            </div>
                            <x-crm.status-badge :status="$task->priority" />
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 py-4 text-center">No pending tasks.</p>
                    @endforelse
                </div>
            </div>

            <div class="border-t border-slate-100 pt-4 space-y-2">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-900">Upcoming Follow-Ups</h3>
                    <a href="{{ route('follow-ups.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700 hover:underline">
                        {{ $scheduledFollowUpsCount }} scheduled &rarr;
                    </a>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($upcomingFollowUps as $followUp)
                        @php
                            $subject = $followUp->subject;
                            $subjectName = $subject?->title
                                ?? ($subject ? trim(($subject->first_name ?? '').' '.($subject->last_name ?? '')) : '');
                        @endphp
                        <div class="py-2.5 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-slate-900 truncate">{{ $followUp->objective ?? 'Follow-up' }}</p>
                                <p class="text-xs mt-0.5 {{ $followUp->scheduled_at->isPast() ? 'text-rose-600 font-medium' : 'text-slate-500' }}">
                                    {{ $followUp->scheduled_at->diffForHumans() }}
                                    @if ($subjectName !== '')
                                        &middot; {{ $subjectName }}
                                    @endif
                                </p>
                            </div>
                            <x-crm.status-badge :status="$followUp->channel" />
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 py-4 text-center">No follow-ups scheduled.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-layouts.app>
