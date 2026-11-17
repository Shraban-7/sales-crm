<x-layouts.app title="{{ $deal->title }} — Deal" header="{{ $deal->title }}">
    <div class="bg-white rounded-xl border border-slate-200 p-4 flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-2">
            @foreach ($deal->pipeline->stages as $stage)
                @if ($stage->id === $deal->pipeline_stage_id)
                    <span class="rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white">{{ $stage->name }}</span>
                @else
                    @can('moveStage', $deal)
                        <form method="POST" action="{{ route('deals.move-stage', $deal) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="pipeline_stage_id" value="{{ $stage->id }}">
                            @if ($stage->is_lost_stage)
                                <input type="hidden" name="lost_reason" value="Marked lost from deal workspace">
                            @endif
                            <button type="submit" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-100">
                                {{ $stage->name }}
                            </button>
                        </form>
                    @else
                        <span class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs text-slate-400">{{ $stage->name }}</span>
                    @endcan
                @endif
            @endforeach
        </div>

        @can('create', \App\Models\Quotation::class)
            <a href="{{ route('quotations.create', ['deal_id' => $deal->id]) }}" class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white">New Quotation</a>
        @endcan
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="space-y-4">
            <div class="bg-white rounded-xl border border-slate-200 p-5 space-y-2 text-sm">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-slate-900">Deal Summary</h3>
                    <x-crm.status-badge :status="$deal->status" />
                </div>
                <p><span class="text-slate-500">Company:</span> <a href="{{ route('companies.show', $deal->company) }}" class="text-brand-600 hover:underline">{{ $deal->company?->name }}</a></p>
                <p><span class="text-slate-500">Value:</span> {{ $deal->currency }} {{ number_format((float) $deal->value, 2) }}</p>
                <p><span class="text-slate-500">Weighted Value:</span> {{ $deal->currency }} {{ number_format((float) $deal->weighted_value, 2) }}</p>
                <p><span class="text-slate-500">Probability:</span> {{ $deal->probability }}%</p>
                <p><span class="text-slate-500">Owner:</span> {{ $deal->owner?->name }}</p>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-5 space-y-2 text-sm">
                <h3 class="font-semibold text-slate-900">Stage History</h3>
                @foreach ($deal->stageHistories as $history)
                    <div class="text-xs text-slate-600 border-l-2 border-slate-300 pl-2.5 py-1">
                        <span class="font-medium text-slate-900">{{ $history->fromStage?->name ?? 'New' }} &rarr; {{ $history->toStage?->name }}</span>
                        <p class="text-slate-400">{{ $history->movedBy?->name }} &middot; {{ $history->created_at?->diffForHumans() }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-slate-200 p-5 space-y-3">
                <h3 class="text-sm font-semibold text-slate-900">Quotations</h3>
                @forelse ($deal->quotations as $quote)
                    <div class="flex items-center justify-between text-sm border-b border-slate-100 pb-2">
                        <a href="{{ route('quotations.show', $quote) }}" class="font-medium text-brand-600 hover:underline">{{ $quote->quote_number }} (v{{ $quote->version }})</a>
                        <div class="flex items-center gap-3">
                            <span>{{ $quote->currency }} {{ number_format((float) $quote->grand_total, 2) }}</span>
                            <x-crm.status-badge :status="$quote->status" />
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No quotations created for this deal.</p>
                @endforelse
            </div>

            <x-crm.timeline-feed :subject="$deal" subjectType="deal" />
        </div>
    </div>
</x-layouts.app>
