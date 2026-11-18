<x-layouts.app title="Pipelines — Sales CRM" header="Sales Pipelines Configuration">
    @can('create', \App\Models\Pipeline::class)
        <form method="POST" action="{{ route('pipelines.store') }}" class="crm-card p-5 space-y-3">
            @csrf
            <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Create New Sales Pipeline</h2>
            <div class="flex flex-wrap gap-3 items-center">
                <input type="text" name="name" placeholder="New Pipeline Name *" required class="crm-input flex-1 min-w-60">
                <select name="team_id" class="crm-select w-auto min-w-44">
                    <option value="">Organization-wide</option>
                    @foreach ($teams as $team)
                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                    @endforeach
                </select>
                <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                    <input type="checkbox" name="is_default" value="1" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    <span>Set as default</span>
                </label>
                <button type="submit" class="crm-btn-primary">Create Pipeline</button>
            </div>
        </form>
    @endcan

    <div class="space-y-5">
        @foreach ($pipelines as $pipeline)
            <section class="crm-card p-6 space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h3 class="text-base font-bold text-slate-900">{{ $pipeline->name }}</h3>
                            @if ($pipeline->is_default)
                                <span class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-medium text-brand-700 ring-1 ring-inset ring-brand-200/80">Default</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $pipeline->deals_count }} deals &middot; {{ $pipeline->stages->count() }} stages</p>
                    </div>
                    <a href="{{ route('pipelines.kanban.show', $pipeline) }}" class="crm-btn-secondary py-1.5 text-xs">
                        <svg class="h-3.5 w-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h4.5v13.5h-4.5V5.25ZM9.75 5.25h4.5v8.25h-4.5V5.25ZM15.75 5.25h4.5v10.5h-4.5V5.25Z" />
                        </svg>
                        Open Kanban
                    </a>
                </div>

                <div class="divide-y divide-slate-100 rounded-xl border border-slate-200 overflow-hidden">
                    @foreach ($pipeline->stages as $index => $stage)
                        @php
                            $dotClass = match ($stage->color) {
                                'blue' => 'bg-blue-500',
                                'cyan' => 'bg-cyan-500',
                                'amber' => 'bg-amber-500',
                                'emerald' => 'bg-emerald-500',
                                'rose' => 'bg-rose-500',
                                'indigo' => 'bg-indigo-500',
                                default => 'bg-slate-400',
                            };
                        @endphp
                        <div class="px-4 py-3 flex flex-wrap items-center justify-between gap-2 text-xs hover:bg-slate-50/70 transition">
                            <div class="flex flex-wrap items-center gap-2.5">
                                <span class="h-2.5 w-2.5 rounded-full {{ $dotClass }}"></span>
                                <span class="font-mono text-slate-400">#{{ $stage->position }}</span>
                                <span class="font-semibold text-slate-900">{{ $stage->name }}</span>
                                <span class="text-slate-500">{{ $stage->default_probability }}% &middot; {{ $stage->rotting_days }}d rot</span>
                                @if ($stage->is_won_stage)
                                    <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-700 ring-1 ring-inset ring-emerald-200/80">Won</span>
                                @elseif ($stage->is_lost_stage)
                                    <span class="rounded-full bg-rose-50 px-2 py-0.5 text-[11px] font-medium text-rose-700 ring-1 ring-inset ring-rose-200/80">Lost</span>
                                @endif
                                <span class="text-slate-400">({{ $stage->deals_count }} deals)</span>
                            </div>

                            @can('update', $pipeline)
                                <div class="flex items-center gap-2">
                                    @if ($index > 0)
                                        @php
                                            $ids = $pipeline->stages->pluck('id')->all();
                                            $prev = $ids[$index - 1];
                                            $ids[$index - 1] = $ids[$index];
                                            $ids[$index] = $prev;
                                        @endphp
                                        <form method="POST" action="{{ route('pipelines.stages.reorder', $pipeline) }}">
                                            @csrf
                                            @foreach ($ids as $id)
                                                <input type="hidden" name="stage_ids[]" value="{{ $id }}">
                                            @endforeach
                                            <button type="submit" title="Move stage up" class="crm-btn-secondary px-2 py-1 text-xs">&uarr;</button>
                                        </form>
                                    @endif

                                    @if ($pipeline->stages->count() > 1)
                                        <form method="POST" action="{{ route('pipelines.stages.destroy', [$pipeline, $stage]) }}" class="flex items-center gap-1.5">
                                            @csrf
                                            @method('DELETE')
                                            @if ($stage->deals_count > 0)
                                                <select name="reassign_to_stage_id" required class="crm-select w-auto py-1 text-xs">
                                                    @foreach ($pipeline->stages as $target)
                                                        @continue($target->id === $stage->id)
                                                        <option value="{{ $target->id }}">Move to {{ $target->name }}</option>
                                                    @endforeach
                                                </select>
                                            @endif
                                            <button type="submit" class="crm-btn-danger px-2.5 py-1 text-xs">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            @endcan
                        </div>
                    @endforeach
                </div>

                @can('update', $pipeline)
                    <form method="POST" action="{{ route('pipelines.stages.store', $pipeline) }}" class="flex flex-wrap items-center gap-2.5 pt-2 border-t border-slate-100 text-xs">
                        @csrf
                        <input type="text" name="name" placeholder="Stage name *" required class="crm-input w-auto min-w-44 py-1.5 text-xs">
                        <input type="number" step="0.01" min="0" max="100" name="default_probability" placeholder="Prob %" value="50" required class="crm-input w-24 py-1.5 text-xs">
                        <input type="number" min="1" max="365" name="rotting_days" placeholder="Rot days" value="14" required class="crm-input w-24 py-1.5 text-xs">
                        <select name="color" class="crm-select w-auto py-1.5 text-xs">
                            @foreach (['slate', 'blue', 'cyan', 'amber', 'emerald', 'rose', 'indigo'] as $color)
                                <option value="{{ $color }}">{{ ucfirst($color) }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="crm-btn-dark py-1.5 text-xs">Add Stage</button>
                    </form>
                @endcan
            </section>
        @endforeach
    </div>
</x-layouts.app>
