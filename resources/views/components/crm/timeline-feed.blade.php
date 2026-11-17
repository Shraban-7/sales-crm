@props(['subject', 'subjectType'])

<div class="bg-white rounded-xl border border-slate-200 p-5 space-y-4">
    <h3 class="text-sm font-semibold text-slate-900">Activity Timeline</h3>

    @can('create', \App\Models\Activity::class)
        <form method="POST" action="{{ route('activities.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 bg-slate-50 p-3 rounded-lg border border-slate-200">
            @csrf
            <input type="hidden" name="subject_type" value="{{ $subjectType }}">
            <input type="hidden" name="subject_id" value="{{ $subject->id }}">
            <input type="hidden" name="occurred_at" value="{{ now()->toDateTimeString() }}">

            <select name="type" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                <option value="call">Call</option>
                <option value="email">Email</option>
                <option value="meeting">Meeting</option>
                <option value="note">Note</option>
            </select>

            <input type="text" name="summary" placeholder="Summary (e.g., Discovery call)" required class="md:col-span-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">

            <button type="submit" class="rounded-lg bg-brand-600 px-3 py-2 text-sm font-medium text-white hover:bg-brand-700">Log Activity</button>
        </form>
    @endcan

    <div class="space-y-3">
        @forelse ($subject->activities->sortByDesc('occurred_at') as $activity)
            <div class="flex items-start justify-between border-l-2 border-brand-600 pl-3 py-1">
                <div>
                    <div class="flex items-center gap-2 text-xs text-slate-500">
                        <span class="font-semibold uppercase text-slate-700">{{ $activity->type->label() }}</span>
                        <span>&middot;</span>
                        <span>{{ $activity->user?->name ?? 'System' }}</span>
                        <span>&middot;</span>
                        <span>{{ $activity->occurred_at?->diffForHumans() }}</span>
                    </div>
                    <p class="text-sm font-medium text-slate-900 mt-0.5">{{ $activity->summary }}</p>
                    @if ($activity->body)
                        <p class="text-xs text-slate-600 mt-1">{{ $activity->body }}</p>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-500">No activities logged yet.</p>
        @endforelse
    </div>
</div>
