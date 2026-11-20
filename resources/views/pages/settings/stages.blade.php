<x-layouts.app title="Pipeline Stages — Sale Fusion CRM" header="Pipeline Stage Management">
    <div class="space-y-6 max-w-4xl">
        @foreach ($pipelines as $pipeline)
            <div class="crm-card p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">{{ $pipeline->name }}</h2>
                        <p class="text-xs text-slate-500">Stages define velocity and win probabilities across deal lifecycles.</p>
                    </div>
                </div>

                <!-- Existing Stages -->
                <div class="divide-y divide-slate-100">
                    @foreach ($pipeline->stages as $stage)
                        <div class="py-3 flex items-center justify-between text-xs hover:bg-slate-50 px-2 rounded">
                            <div class="flex items-center gap-3">
                                <span class="h-3 w-3 rounded-full" style="background-color: {{ $stage->color }};"></span>
                                <span class="font-bold text-slate-900">{{ $stage->name }}</span>
                                <span class="text-slate-400">Position: {{ $stage->position }}</span>
                                @if($stage->is_won) <span class="bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded text-[10px] font-bold">WON</span> @endif
                                @if($stage->is_lost) <span class="bg-rose-100 text-rose-800 px-1.5 py-0.5 rounded text-[10px] font-bold">LOST</span> @endif
                            </div>
                            <div class="flex items-center gap-4 text-slate-500">
                                <span>Win Prob: <strong class="text-slate-800">{{ $stage->probability }}%</strong></span>
                                <span>Rotting: <strong class="text-slate-800">{{ $stage->rotting_days ?? 'None' }}d</strong></span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Add Stage Form -->
                <form method="POST" action="{{ route('settings.stages.store') }}" class="grid grid-cols-1 md:grid-cols-6 gap-3 bg-slate-50 p-4 rounded-xl border border-slate-100 pt-4">
                    @csrf
                    <input type="hidden" name="pipeline_id" value="{{ $pipeline->id }}">

                    <div class="md:col-span-2">
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Stage Name *</label>
                        <input type="text" name="name" required placeholder="Stage Name" class="crm-input text-xs">
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Color *</label>
                        <input type="color" name="color" value="#3b82f6" class="h-9 w-full rounded border border-slate-200">
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Win Prob % *</label>
                        <input type="number" name="probability" value="50" min="0" max="100" required class="crm-input text-xs">
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Rotting Days</label>
                        <input type="number" name="rotting_days" placeholder="14" min="1" class="crm-input text-xs">
                    </div>

                    <div class="flex items-end">
                        <button type="submit" class="crm-btn-primary w-full text-xs">Add Stage</button>
                    </div>
                </form>
            </div>
        @endforeach
    </div>
</x-layouts.app>
