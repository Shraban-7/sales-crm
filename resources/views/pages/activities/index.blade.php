<x-layouts.app title="Activity Feed — Sale Fusion CRM" header="Activities & Communications">
    <div class="space-y-5">
        <!-- Tabs & Filters -->
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="inline-flex items-center gap-1.5 rounded-xl bg-slate-200/70 p-1 text-sm">
                <a href="{{ route('activities.index', ['tab' => 'my', 'type' => $currentType]) }}"
                   class="rounded-lg px-4 py-1.5 font-medium transition {{ $currentTab === 'my' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    My Activities
                </a>
                <a href="{{ route('activities.index', ['tab' => 'team', 'type' => $currentType]) }}"
                   class="rounded-lg px-4 py-1.5 font-medium transition {{ $currentTab === 'team' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Team Activities
                </a>
            </div>

            <!-- Type and Date Filters -->
            <form method="GET" action="{{ route('activities.index') }}" class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="tab" value="{{ $currentTab }}">
                <select name="type" class="crm-select text-xs py-1.5">
                    <option value="">All Types</option>
                    <option value="call" @selected($currentType === 'call')>Calls</option>
                    <option value="meeting" @selected($currentType === 'meeting')>Meetings</option>
                    <option value="email" @selected($currentType === 'email')>Emails</option>
                    <option value="note" @selected($currentType === 'note')>Notes</option>
                </select>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="crm-input text-xs py-1.5" placeholder="From Date">
                <input type="date" name="date_to" value="{{ $dateTo }}" class="crm-input text-xs py-1.5" placeholder="To Date">
                <button type="submit" class="crm-btn-secondary text-xs py-1.5">Filter</button>
            </form>
        </div>

        <!-- Activity Timeline -->
        <div class="crm-card divide-y divide-slate-100 overflow-hidden">
            @forelse ($activities as $activity)
                <div class="p-4 hover:bg-slate-50/70 transition flex items-start gap-3.5">
                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-600 mt-0.5">
                        @if($activity->type === 'call')
                            📞
                        @elseif($activity->type === 'meeting')
                            🤝
                        @elseif($activity->type === 'email')
                            ✉️
                        @else
                            📝
                        @endif
                    </span>
                    <div class="min-w-0 flex-1 space-y-1">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm font-semibold text-slate-900">
                                {{ $activity->summary }}
                            </p>
                            <span class="text-xs text-slate-400 whitespace-nowrap">{{ $activity->occurred_at?->diffForHumans() }}</span>
                        </div>
                        @if ($activity->body)
                            <p class="text-xs text-slate-600 whitespace-pre-line">{{ $activity->body }}</p>
                        @endif
                        <div class="flex items-center gap-3 text-[11px] text-slate-400 pt-1">
                            <span>Logged by <strong class="text-slate-700 font-medium">{{ $activity->user?->name }}</strong></span>
                            @if ($activity->duration_minutes)
                                <span>&middot; {{ $activity->duration_minutes }} mins</span>
                            @endif
                            @if ($activity->outcome)
                                <span>&middot; Outcome: <strong class="text-slate-600">{{ $activity->outcome }}</strong></span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center text-slate-500 text-sm">
                    No activities recorded yet for this filter.
                </div>
            @endforelse
        </div>

        <div>
            {{ $activities->links() }}
        </div>
    </div>
</x-layouts.app>
