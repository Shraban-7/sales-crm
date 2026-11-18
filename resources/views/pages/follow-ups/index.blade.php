<x-layouts.app title="Follow-Ups — Sales CRM" header="Customer Follow-Ups">
    @can('create', \App\Models\FollowUp::class)
        <form method="POST" action="{{ route('follow-ups.store') }}" class="bg-white rounded-xl border border-slate-200 p-5 grid grid-cols-1 md:grid-cols-5 gap-3">
            @csrf
            <input type="hidden" name="subject_type" value="deal">
            <select name="subject_id" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Select Deal *</option>
                @foreach ($deals as $deal)
                    <option value="{{ $deal->id }}">{{ $deal->title }}</option>
                @endforeach
            </select>
            <select name="channel" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @foreach (\App\Enums\FollowUpChannel::cases() as $channel)
                    <option value="{{ $channel->value }}">{{ $channel->label() }}</option>
                @endforeach
            </select>
            <input type="datetime-local" name="scheduled_at" value="{{ now()->addDay()->format('Y-m-d\TH:i') }}" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <input type="text" name="objective" placeholder="Objective *" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Schedule</button>
        </form>
    @endcan

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @foreach (['Overdue' => $overdue, 'Today' => $today, 'Upcoming' => $upcoming] as $label => $items)
            <div class="bg-white rounded-xl border border-slate-200 p-4 space-y-3">
                <h3 class="text-sm font-semibold text-slate-900">{{ $label }} ({{ $items->count() }})</h3>
                <div class="space-y-3">
                    @forelse ($items as $followUp)
                        <div class="rounded-lg border border-slate-200 p-3 space-y-2 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-slate-900">{{ $followUp->objective }}</span>
                                <span class="text-xs uppercase text-slate-500">{{ $followUp->channel->label() }}</span>
                            </div>
                            <p class="text-xs text-slate-500">{{ $followUp->scheduled_at->toDayDateTimeString() }}</p>
                            @can('update', $followUp)
                                <form method="POST" action="{{ route('follow-ups.complete', $followUp) }}" class="space-y-2">
                                    @csrf
                                    @method('PATCH')
                                    <div class="flex gap-2">
                                        <input type="text" name="outcome_notes" placeholder="Outcome notes *" required class="flex-1 rounded border border-slate-300 px-2 py-1 text-xs">
                                        <button type="submit" class="rounded bg-emerald-600 px-2.5 py-1 text-xs font-medium text-white">Done</button>
                                    </div>
                                    <details class="text-xs text-slate-600">
                                        <summary class="cursor-pointer text-brand-600">+ Schedule next follow-up</summary>
                                        <div class="mt-2 space-y-1.5 border-t border-slate-100 pt-2">
                                            <label class="flex items-center gap-1.5">
                                                <input type="checkbox" name="schedule_next" value="1">
                                                <span>Create next follow-up</span>
                                            </label>
                                            <select name="next_channel" class="w-full rounded border border-slate-300 px-2 py-1">
                                                @foreach (\App\Enums\FollowUpChannel::cases() as $channel)
                                                    <option value="{{ $channel->value }}">{{ $channel->label() }}</option>
                                                @endforeach
                                            </select>
                                            <input type="datetime-local" name="next_scheduled_at" value="{{ now()->addDays(2)->format('Y-m-d\TH:i') }}" class="w-full rounded border border-slate-300 px-2 py-1">
                                            <input type="text" name="next_objective" placeholder="Next objective" class="w-full rounded border border-slate-300 px-2 py-1">
                                        </div>
                                    </details>
                                </form>
                            @endcan
                        </div>
                    @empty
                        <p class="text-xs text-slate-500">None.</p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</x-layouts.app>
