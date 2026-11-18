<x-layouts.app title="Pipelines — Sales CRM" header="Sales Pipelines Configuration">
    @can('create', \App\Models\Pipeline::class)
        <form method="POST" action="{{ route('pipelines.store') }}" class="bg-white rounded-xl border border-slate-200 p-5 flex flex-wrap gap-3 items-center">
            @csrf
            <input type="text" name="name" placeholder="New Pipeline Name *" required class="flex-1 min-w-60 rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <select name="team_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Organization-wide</option>
                @foreach ($teams as $team)
                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                @endforeach
            </select>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_default" value="1">
                <span>Default</span>
            </label>
            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Create Pipeline</button>
        </form>
    @endcan

    <div class="space-y-4">
        @foreach ($pipelines as $pipeline)
            <div class="bg-white rounded-xl border border-slate-200 p-5 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">
                            {{ $pipeline->name }}
                            @if ($pipeline->is_default)
                                <span class="ml-2 rounded bg-brand-50 px-2 py-0.5 text-xs text-brand-700">Default</span>
                            @endif
                        </h3>
                        <p class="text-xs text-slate-500">{{ $pipeline->deals_count }} deals &middot; {{ $pipeline->stages->count() }} stages</p>
                    </div>
                    <a href="{{ route('pipelines.kanban.show', $pipeline) }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium hover:bg-slate-50">Open Kanban</a>
                </div>

                <div class="divide-y divide-slate-100 border rounded-lg border-slate-200">
                    @foreach ($pipeline->stages as $index => $stage)
                        <div class="p-3 flex flex-wrap items-center justify-between gap-2 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-slate-400">#{{ $stage->position }}</span>
                                <span class="font-semibold text-slate-900">{{ $stage->name }}</span>
                                <span class="text-slate-500">{{ $stage->default_probability }}% &middot; {{ $stage->rotting_days }}d rot</span>
                                @if ($stage->is_won_stage)
                                    <span class="rounded bg-emerald-50 px-1.5 py-0.5 text-emerald-700">Won</span>
                                @elseif ($stage->is_lost_stage)
                                    <span class="rounded bg-rose-50 px-1.5 py-0.5 text-rose-700">Lost</span>
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
                                            <button type="submit" class="rounded border border-slate-300 px-2 py-1 hover:bg-slate-50">&uarr;</button>
                                        </form>
                                    @endif

                                    @if ($pipeline->stages->count() > 1)
                                        <form method="POST" action="{{ route('pipelines.stages.destroy', [$pipeline, $stage]) }}" class="flex items-center gap-1">
                                            @csrf
                                            @method('DELETE')
                                            @if ($stage->deals_count > 0)
                                                <select name="reassign_to_stage_id" required class="rounded border border-slate-300 px-2 py-1 text-xs">
                                                    @foreach ($pipeline->stages as $target)
                                                        @continue($target->id === $stage->id)
                                                        <option value="{{ $target->id }}">Move to {{ $target->name }}</option>
                                                    @endforeach
                                                </select>
                                            @endif
                                            <button type="submit" class="rounded border border-rose-200 bg-rose-50 px-2 py-1 text-rose-700 hover:bg-rose-100">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            @endcan
                        </div>
                    @endforeach
                </div>

                @can('update', $pipeline)
                    <form method="POST" action="{{ route('pipelines.stages.store', $pipeline) }}" class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-100 text-xs">
                        @csrf
                        <input type="text" name="name" placeholder="Stage name *" required class="rounded-lg border border-slate-300 px-2.5 py-1.5">
                        <input type="number" step="0.01" min="0" max="100" name="default_probability" placeholder="Prob %" value="50" required class="w-24 rounded-lg border border-slate-300 px-2.5 py-1.5">
                        <input type="number" min="1" max="365" name="rotting_days" placeholder="Rot days" value="14" required class="w-24 rounded-lg border border-slate-300 px-2.5 py-1.5">
                        <select name="color" class="rounded-lg border border-slate-300 px-2.5 py-1.5">
                            @foreach (['slate', 'blue', 'cyan', 'amber', 'emerald', 'rose', 'indigo'] as $color)
                                <option value="{{ $color }}">{{ ucfirst($color) }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="rounded-lg bg-slate-900 px-3 py-1.5 font-semibold text-white">Add Stage</button>
                    </form>
                @endcan
            </div>
        @endforeach
    </div>
</x-layouts.app>
