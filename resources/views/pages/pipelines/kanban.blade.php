<x-layouts.app title="Pipeline Kanban — Sales CRM" header="{{ $pipeline->name }} — Kanban">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            @foreach ($pipelines as $item)
                <a href="{{ route('pipelines.kanban.show', $item) }}" class="rounded-lg px-3 py-1.5 text-sm font-medium {{ $item->id === $pipeline->id ? 'bg-brand-600 text-white' : 'bg-white border border-slate-200 text-slate-700' }}">
                    {{ $item->name }}
                </a>
            @endforeach
        </div>
        <a href="{{ route('pipelines.index') }}" class="text-sm text-brand-600 hover:underline">Configure Pipelines</a>
    </div>

    <div data-module="kanban" class="flex gap-4 overflow-x-auto pb-4">
        @foreach ($pipeline->stages as $stage)
            @php
                $stageDeals = $deals->get($stage->id, collect());
                $stageTotal = $stageDeals->sum(fn ($d) => (float) $d->value);
            @endphp
            <div data-stage-column data-stage-id="{{ $stage->id }}" data-is-lost-stage="{{ $stage->is_lost_stage ? '1' : '0' }}" class="w-72 shrink-0 bg-slate-100 rounded-xl p-3 flex flex-col gap-3 border border-slate-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">{{ $stage->name }}</h3>
                        <p class="text-xs text-slate-500">{{ $stageDeals->count() }} deals &middot; {{ config('crm.currency') }} {{ number_format($stageTotal, 0) }}</p>
                    </div>
                    <span class="text-xs font-mono text-slate-500">{{ (int) $stage->default_probability }}%</span>
                </div>

                <div data-stage-cards class="space-y-2.5 min-h-24">
                    @foreach ($stageDeals as $deal)
                        <div data-deal-card draggable="true" data-move-url="{{ route('deals.move-stage', $deal) }}" class="bg-white rounded-lg border border-slate-200 p-3 shadow-xs cursor-grab active:cursor-grabbing space-y-1.5">
                            <a href="{{ route('deals.show', $deal) }}" class="text-sm font-semibold text-slate-900 hover:text-brand-600 block">{{ $deal->title }}</a>
                            <p class="text-xs text-slate-500">{{ $deal->company?->name }}</p>
                            <div class="flex items-center justify-between pt-1 text-xs">
                                <span class="font-semibold text-slate-800">{{ $deal->currency }} {{ number_format((float) $deal->value, 0) }}</span>
                                @if ($deal->isRotting())
                                    <span class="rounded bg-rose-50 px-1.5 py-0.5 text-[11px] font-medium text-rose-700">Rotting</span>
                                @else
                                    <span class="text-slate-400">{{ $deal->owner?->name }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</x-layouts.app>
