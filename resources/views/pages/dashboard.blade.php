<x-layouts.app title="Dashboard — Sales CRM" header="Executive Dashboard">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-crm.stat-card label="Open Pipeline" value="{{ config('crm.currency') }} {{ number_format($openPipelineValue, 2) }}" subtext="{{ $openDealsCount }} open deals" />
        <x-crm.stat-card label="Won Revenue" value="{{ config('crm.currency') }} {{ number_format($wonRevenue, 2) }}" />
        <x-crm.stat-card label="Active Leads" value="{{ $activeLeadsCount }}" />
        <x-crm.stat-card label="Pending Tasks" value="{{ $dueTasks->count() }}" />
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
                <h2 class="text-sm font-semibold text-slate-900">Due Tasks &amp; Follow-Ups</h2>
                <a href="{{ route('tasks.index') }}" class="text-xs font-medium text-brand-600 hover:underline">Tasks</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($dueTasks as $task)
                    <div class="py-2.5 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-900">{{ $task->title }}</p>
                            <p class="text-xs text-slate-500">Due {{ $task->due_at->diffForHumans() }} &middot; {{ $task->assignee?->name }}</p>
                        </div>
                        <x-crm.status-badge :status="$task->priority" />
                    </div>
                @empty
                    <p class="text-sm text-slate-500 py-3">No pending tasks.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.app>
