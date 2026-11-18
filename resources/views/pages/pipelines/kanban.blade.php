<x-layouts.app title="Pipeline Kanban — Sale Fusion CRM" header="{{ $pipeline->name }} — Kanban">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2">
            @foreach ($pipelines as $item)
                <a href="{{ route('pipelines.kanban.show', $item) }}"
                   class="rounded-xl px-3.5 py-2 text-sm font-medium transition {{ $item->id === $pipeline->id ? 'bg-brand-600 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50' }}">
                    {{ $item->name }}
                </a>
            @endforeach
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('deals.index') }}" class="crm-btn-secondary py-1.5 text-xs">List View</a>
            <a href="{{ route('pipelines.index') }}" class="crm-btn-secondary py-1.5 text-xs">Configure Pipelines</a>
        </div>
    </div>

    <div data-module="kanban" class="flex gap-4 overflow-x-auto pb-4">
        @foreach ($pipeline->stages as $stage)
            @php
                $stageDeals = $deals->get($stage->id, collect());
                $stageTotal = $stageDeals->sum(fn ($d) => (float) $d->value);
                $borderAccent = match ($stage->color) {
                    'blue' => 'border-t-blue-500',
                    'cyan' => 'border-t-cyan-500',
                    'amber' => 'border-t-amber-500',
                    'emerald' => 'border-t-emerald-500',
                    'rose' => 'border-t-rose-500',
                    'indigo' => 'border-t-indigo-500',
                    default => 'border-t-slate-400',
                };
                $dotAccent = match ($stage->color) {
                    'blue' => 'bg-blue-500',
                    'cyan' => 'bg-cyan-500',
                    'amber' => 'bg-amber-500',
                    'emerald' => 'bg-emerald-500',
                    'rose' => 'bg-rose-500',
                    'indigo' => 'bg-indigo-500',
                    default => 'bg-slate-400',
                };
            @endphp
            <div data-stage-column data-stage-id="{{ $stage->id }}" data-is-lost-stage="{{ $stage->is_lost_stage ? '1' : '0' }}"
                 class="w-72 shrink-0 bg-slate-100/80 rounded-2xl border border-slate-200/80 border-t-4 {{ $borderAccent }} p-3.5 flex flex-col gap-3 transition">
                <div class="flex items-start justify-between gap-2 px-0.5">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full {{ $dotAccent }}"></span>
                            <h3 class="text-sm font-semibold text-slate-900">{{ $stage->name }}</h3>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5 tabular-nums">{{ $stageDeals->count() }} deals &middot; {{ config('crm.currency') }} {{ number_format($stageTotal, 0) }}</p>
                    </div>
                    <span class="rounded-full bg-white px-2 py-0.5 text-[11px] font-mono font-semibold text-slate-600 ring-1 ring-slate-200">{{ (int) $stage->default_probability }}%</span>
                </div>

                <div data-stage-cards class="space-y-2.5 min-h-28">
                    @foreach ($stageDeals as $deal)
                        <div data-deal-card draggable="true" data-move-url="{{ route('deals.move-stage', $deal) }}"
                             class="bg-white rounded-xl border border-slate-200/90 p-3.5 shadow-xs hover:shadow-md hover:border-brand-300 cursor-grab active:cursor-grabbing space-y-2 transition">
                            <a href="{{ route('deals.show', $deal) }}" class="text-sm font-semibold text-slate-900 hover:text-brand-600 block leading-snug">
                                {{ $deal->title }}
                            </a>
                            <p class="text-xs text-slate-500 truncate">{{ $deal->company?->name ?? 'Independent Deal' }}</p>
                            <div class="flex items-center justify-between pt-1.5 border-t border-slate-100 text-xs">
                                <span class="font-semibold text-slate-900 tabular-nums">{{ $deal->currency }} {{ number_format((float) $deal->value, 0) }}</span>
                                @if ($deal->isRotting())
                                    <span class="rounded-full bg-rose-50 px-2 py-0.5 text-[11px] font-medium text-rose-700 ring-1 ring-inset ring-rose-200/80">Rotting</span>
                                @else
                                    <span class="text-slate-400 truncate max-w-28">{{ $deal->owner?->name }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</x-layouts.app>
