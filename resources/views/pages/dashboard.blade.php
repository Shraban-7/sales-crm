<x-layouts.app title="Dashboard — Sales CRM" header="Executive Dashboard">
    @php
        $openPipelineLines = $openPipelineByCurrency
            ->map(fn ($row) => $row->currency.' '.number_format((float) $row->total_value, 2))
            ->all();
        $wonRevenueLines = $wonRevenueByCurrency
            ->map(fn ($row) => $row->currency.' '.number_format((float) $row->total_value, 2))
            ->all();
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-crm.stat-card
            label="Open Pipeline"
            :lines="$openPipelineLines ?: [config('crm.currency').' 0.00']"
            :subtext="$openDealsCount.' open deal'.($openDealsCount === 1 ? '' : 's')"
        />
        <x-crm.stat-card
            label="Won Revenue"
            :lines="$wonRevenueLines ?: [config('crm.currency').' 0.00']"
            :subtext="$wonDealsCount.' won deal'.($wonDealsCount === 1 ? '' : 's')"
        />
        <x-crm.stat-card label="Active Leads" :value="$activeLeadsCount" subtext="Not yet converted or disqualified" />
        <x-crm.stat-card
            label="Pending Tasks"
            :value="$pendingTasksCount"
            :subtext="$overdueTasksCount > 0 ? $overdueTasksCount.' overdue' : 'Nothing overdue'"
        />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl border border-slate-200 p-5 space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-900">Recent Deals</h2>
                <a href="{{ route('deals.index') }}" class="text-xs font-medium text-brand-600 hover:underline">View all</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($recentDeals as $deal)
                    <div class="py-2.5 flex items-center justify-between">
                        <div>
                            <a href="{{ route('deals.show', $deal) }}" class="text-sm font-medium text-slate-900 hover:text-brand-600">{{ $deal->title }}</a>
                            <p class="text-xs text-slate-500">{{ $deal->company?->name }} &middot; {{ $deal->stage?->name }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-slate-900">{{ $deal->currency }} {{ number_format((float) $deal->value, 2) }}</p>
                            <x-crm.status-badge :status="$deal->status" />
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 py-3">No deals found.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5 space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-900">Due Tasks</h2>
                <a href="{{ route('tasks.index') }}" class="text-xs font-medium text-brand-600 hover:underline">View all</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($dueTasks as $task)
                    <div class="py-2.5 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium text-slate-900">{{ $task->title }}</p>
                            <p class="text-xs {{ $task->due_at?->isPast() ? 'text-rose-600 font-medium' : 'text-slate-500' }}">
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
                    <p class="text-sm text-slate-500 py-3">No pending tasks.</p>
                @endforelse
            </div>

            <div class="border-t border-slate-200 pt-3 space-y-2">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-900">Upcoming Follow-Ups</h3>
                    <a href="{{ route('follow-ups.index') }}" class="text-xs font-medium text-brand-600 hover:underline">
                        {{ $scheduledFollowUpsCount }} scheduled
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
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ $followUp->objective ?? 'Follow-up' }}</p>
                                <p class="text-xs {{ $followUp->scheduled_at->isPast() ? 'text-rose-600 font-medium' : 'text-slate-500' }}">
                                    {{ $followUp->scheduled_at->diffForHumans() }}
                                    @if ($subjectName !== '')
                                        &middot; {{ $subjectName }}
                                    @endif
                                </p>
                            </div>
                            <x-crm.status-badge :status="$followUp->channel" />
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 py-3">No follow-ups scheduled.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
