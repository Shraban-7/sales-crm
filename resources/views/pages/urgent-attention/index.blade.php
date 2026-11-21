<x-layouts.app title="Urgent Attention — Sale Fusion CRM" header="Urgent Attention & At-Risk Deals">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <p class="text-sm text-slate-600">
                Action items requiring immediate intervention: stalled deals past stage velocity thresholds, overdue customer follow-ups, and pending tasks.
            </p>
        </div>

        <!-- At-Risk / Stalled Deals -->
        <div class="crm-card overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-amber-50/50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-amber-500/20 text-amber-700">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </span>
                    <h2 class="text-sm font-semibold text-slate-900">Stalled &amp; At-Risk Deals ({{ count($atRiskDeals) }})</h2>
                </div>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($atRiskDeals as $item)
                    @php $deal = $item['deal']; @endphp
                    <div class="p-4 flex flex-wrap items-center justify-between gap-4 hover:bg-slate-50/70 transition">
                        <div class="min-w-0 space-y-1">
                            <div class="flex items-center gap-2.5">
                                <a href="{{ route('deals.show', $deal) }}" class="text-sm font-semibold text-slate-900 hover:text-brand-600 transition">
                                    {{ $deal->title }}
                                </a>
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium" style="background-color: {{ $deal->stage?->color ?? '#3b82f6' }}20; color: {{ $deal->stage?->color ?? '#3b82f6' }};">
                                    {{ $deal->stage?->name }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500">
                                Company: <span class="font-medium text-slate-700">{{ $deal->company?->name ?? 'None' }}</span> &middot;
                                Contact: <span class="font-medium text-slate-700">{{ $deal->primaryContact?->name ?? 'None' }}</span> &middot;
                                Owner: <span class="font-medium text-slate-700">{{ $deal->owner?->name }}</span> &middot;
                                Value: <span class="font-semibold text-slate-900">{{ $deal->currency }} {{ number_format((float) $deal->value, 2) }}</span>
                            </p>
                            <div class="flex flex-wrap gap-1.5 pt-1">
                                @foreach ($item['reasons'] as $reason)
                                    <span class="inline-flex items-center gap-1 rounded bg-rose-50 px-2 py-0.5 text-[11px] font-medium text-rose-700 ring-1 ring-rose-600/20">
                                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        {{ $reason }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('deals.show', $deal) }}" class="crm-btn-secondary text-xs">
                                Open Deal
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-500 text-sm">
                        No deals currently flagged as stalled or at-risk. Great work!
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Overdue Tasks & Follow-ups Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Overdue Follow-ups -->
            <div class="crm-card overflow-hidden">
                <div class="p-4 border-b border-slate-100 bg-rose-50/40 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-rose-600"></span>
                        Overdue Customer Follow-ups ({{ $overdueFollowUps->count() }})
                    </h2>
                    <a href="{{ route('follow-ups.index') }}" class="text-xs text-brand-600 hover:underline">View All</a>
                </div>
                <div class="divide-y divide-slate-100 max-h-96 overflow-y-auto">
                    @forelse ($overdueFollowUps as $followUp)
                        @php
                            $subjectName = match (true) {
                                $followUp->subject instanceof \App\Models\Deal => $followUp->subject->title,
                                $followUp->subject instanceof \App\Models\Lead => $followUp->subject->full_name,
                                default => $followUp->subject?->name ?? 'Customer',
                            };
                            $channelLabel = $followUp->channel instanceof \App\Enums\FollowUpChannel ? $followUp->channel->label() : ucfirst($followUp->channel?->value ?? 'Follow-Up');
                        @endphp
                        <div class="p-3.5 text-xs hover:bg-slate-50">
                            <p class="font-semibold text-slate-900">{{ $channelLabel }} with {{ $subjectName }}</p>
                            <p class="text-slate-500 mt-0.5">Scheduled: {{ $followUp->scheduled_at?->diffForHumans() }} &middot; Rep: {{ $followUp->owner?->name ?? 'Unassigned' }}</p>
                        </div>
                    @empty
                        <div class="p-6 text-center text-slate-400 text-xs">No overdue follow-ups.</div>
                    @endforelse
                </div>
            </div>

            <!-- Overdue Tasks -->
            <div class="crm-card overflow-hidden">
                <div class="p-4 border-b border-slate-100 bg-rose-50/40 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-rose-600"></span>
                        Overdue Action Tasks ({{ $overdueTasks->count() }})
                    </h2>
                    <a href="{{ route('tasks.index', ['filter' => 'overdue']) }}" class="text-xs text-brand-600 hover:underline">View All</a>
                </div>
                <div class="divide-y divide-slate-100 max-h-96 overflow-y-auto">
                    @forelse ($overdueTasks as $task)
                        @php
                            $linkedTitle = match (true) {
                                $task->taskable instanceof \App\Models\Deal => $task->taskable->title,
                                $task->taskable instanceof \App\Models\Lead => $task->taskable->full_name,
                                default => $task->taskable?->name,
                            };
                        @endphp
                        <div class="p-3.5 text-xs hover:bg-slate-50">
                            <p class="font-semibold text-slate-900">{{ $task->title }}</p>
                            <p class="text-slate-500 mt-0.5">
                                Due: <span class="text-rose-600 font-medium">{{ $task->due_at?->diffForHumans() }}</span> &middot;
                                Assigned to: {{ $task->assignee?->name ?? 'Unassigned' }}
                                @if ($linkedTitle)
                                    &middot; Linked to: <span class="font-medium text-slate-700">{{ $linkedTitle }}</span>
                                @endif
                            </p>
                        </div>
                    @empty
                        <div class="p-6 text-center text-slate-400 text-xs">No overdue tasks.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
