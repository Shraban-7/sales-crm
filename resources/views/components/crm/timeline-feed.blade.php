@props(['subject', 'subjectType'])

<div class="crm-card p-5 space-y-4">
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-semibold text-slate-900">Activity Timeline</h3>
        <span class="text-xs text-slate-400">{{ $subject->activities->count() }} entries</span>
    </div>

    @can('create', \App\Models\Activity::class)
        <form method="POST" action="{{ route('activities.store') }}" class="grid grid-cols-1 md:grid-cols-12 gap-2.5 bg-slate-50/90 p-3.5 rounded-xl border border-slate-200/80">
            @csrf
            <input type="hidden" name="subject_type" value="{{ $subjectType }}">
            <input type="hidden" name="subject_id" value="{{ $subject->id }}">
            <input type="hidden" name="occurred_at" value="{{ now()->toDateTimeString() }}">

            <select name="type" class="crm-select md:col-span-3">
                <option value="call">Call</option>
                <option value="email">Email</option>
                <option value="meeting">Meeting</option>
                <option value="note">Note</option>
            </select>

            <input type="text" name="summary" placeholder="Log a call, email, meeting, or note..." required class="crm-input md:col-span-6">

            <button type="submit" class="crm-btn-primary md:col-span-3">Log Activity</button>
        </form>
    @endcan

    <div class="space-y-3">
        @forelse ($subject->activities->sortByDesc('occurred_at') as $activity)
            @php
                $badgeColor = match ($activity->type->value) {
                    'call' => 'bg-blue-50 text-blue-700 border-blue-200',
                    'email' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                    'meeting' => 'bg-amber-50 text-amber-700 border-amber-200',
                    'system' => 'bg-slate-100 text-slate-600 border-slate-200',
                    default => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                };
            @endphp
            <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-3.5 flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                        <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-[11px] font-semibold uppercase {{ $badgeColor }}">
                            {{ $activity->type->label() }}
                        </span>
                        <span class="font-medium text-slate-700">{{ $activity->user?->name ?? 'System' }}</span>
                        <span>&middot;</span>
                        <span>{{ $activity->occurred_at?->diffForHumans() }}</span>
                    </div>
                    <p class="text-sm font-medium text-slate-900">{{ $activity->summary }}</p>
                    @if ($activity->body)
                        <p class="text-xs text-slate-600 whitespace-pre-line">{{ $activity->body }}</p>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-500 py-4 text-center">No activities logged yet.</p>
        @endforelse
    </div>
</div>
