<x-layouts.app title="Follow-Ups — Sales CRM" header="Customer Follow-Ups">
    @can('create', \App\Models\FollowUp::class)
        <form method="POST" action="{{ route('follow-ups.store') }}" class="crm-card p-5 space-y-3">
            @csrf
            <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Schedule Follow-Up</h2>
            <input type="hidden" name="subject_type" value="deal">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                <select name="subject_id" required class="crm-select">
                    <option value="">Select Deal *</option>
                    @foreach ($deals as $deal)
                        <option value="{{ $deal->id }}">{{ $deal->title }}</option>
                    @endforeach
                </select>
                <select name="channel" required class="crm-select">
                    @foreach (\App\Enums\FollowUpChannel::cases() as $channel)
                        <option value="{{ $channel->value }}">{{ $channel->label() }}</option>
                    @endforeach
                </select>
                <input type="datetime-local" name="scheduled_at" value="{{ now()->addDay()->format('Y-m-d\TH:i') }}" required class="crm-input">
                <input type="text" name="objective" placeholder="Objective *" required class="crm-input">
                <button type="submit" class="crm-btn-primary">Schedule</button>
            </div>
        </form>
    @endcan

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @foreach (['Overdue' => $overdue, 'Today' => $today, 'Upcoming' => $upcoming] as $label => $items)
            @php
                $headerAccent = match ($label) {
                    'Overdue' => 'border-t-rose-500 text-rose-700 bg-rose-50',
                    'Today' => 'border-t-amber-500 text-amber-700 bg-amber-50',
                    default => 'border-t-brand-500 text-brand-700 bg-brand-50',
                };
                $borderTop = match ($label) {
                    'Overdue' => 'border-t-4 border-t-rose-500',
                    'Today' => 'border-t-4 border-t-amber-500',
                    default => 'border-t-4 border-t-brand-500',
                };
            @endphp
            <section class="crm-card {{ $borderTop }} p-5 space-y-3.5">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900">{{ $label }}</h3>
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold tabular-nums {{ $headerAccent }}">{{ $items->count() }}</span>
                </div>
                <div class="space-y-3">
                    @forelse ($items as $followUp)
                        <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-3.5 space-y-2.5 text-sm">
                            <div class="flex items-start justify-between gap-2">
                                <span class="font-semibold text-slate-900 leading-snug">{{ $followUp->objective }}</span>
                                <x-crm.status-badge :status="$followUp->channel" />
                            </div>
                            <p class="text-xs text-slate-500">{{ $followUp->scheduled_at->toDayDateTimeString() }}</p>
                            @can('update', $followUp)
                                <form method="POST" action="{{ route('follow-ups.complete', $followUp) }}" class="space-y-2 pt-1">
                                    @csrf
                                    @method('PATCH')
                                    <div class="flex gap-2">
                                        <input type="text" name="outcome_notes" placeholder="Outcome notes *" required class="crm-input py-1.5 text-xs flex-1">
                                        <button type="submit" class="inline-flex items-center rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700 transition">Done</button>
                                    </div>
                                    <details class="text-xs text-slate-600">
                                        <summary class="cursor-pointer font-medium text-brand-600 hover:text-brand-700">+ Schedule next follow-up</summary>
                                        <div class="mt-2 space-y-2 border-t border-slate-200 pt-2">
                                            <label class="flex items-center gap-1.5 cursor-pointer">
                                                <input type="checkbox" name="schedule_next" value="1" class="rounded border-slate-300 text-brand-600">
                                                <span>Create next follow-up</span>
                                            </label>
                                            <select name="next_channel" class="crm-select py-1 text-xs">
                                                @foreach (\App\Enums\FollowUpChannel::cases() as $channel)
                                                    <option value="{{ $channel->value }}">{{ $channel->label() }}</option>
                                                @endforeach
                                            </select>
                                            <input type="datetime-local" name="next_scheduled_at" value="{{ now()->addDays(2)->format('Y-m-d\TH:i') }}" class="crm-input py-1 text-xs">
                                            <input type="text" name="next_objective" placeholder="Next objective" class="crm-input py-1 text-xs">
                                        </div>
                                    </details>
                                </form>
                            @endcan
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-4 text-center">No follow-ups in this bucket.</p>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>
</x-layouts.app>
