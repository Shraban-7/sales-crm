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
            <div class="bg-white rounded-xl border border-slate-200 p-5 space-y-3">
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
                <div class="flex flex-wrap gap-2">
                    @foreach ($pipeline->stages as $stage)
                        <span class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-700">
                            {{ $stage->position }}. {{ $stage->name }} ({{ $stage->default_probability }}%)
                        </span>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</x-layouts.app>
