<x-layouts.app title="{{ $lead->full_name }} — Lead" header="Lead Profile">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-bold text-slate-900">{{ $lead->full_name }}</h2>
                <x-crm.status-badge :status="$lead->status" />
            </div>
            <p class="text-sm text-slate-500">{{ $lead->job_title }} {{ $lead->company_name ? 'at ' . $lead->company_name : '' }}</p>
        </div>

        @can('convert', $lead)
            <button type="button" onclick="document.getElementById('convert-lead-dialog').showModal()" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                Convert to Deal
            </button>
        @endcan
    </div>

    @if ($lead->isConverted())
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-900">
            Converted on {{ $lead->converted_at?->toDayDateTimeString() }}.
            @if ($lead->convertedDeal)
                <a href="{{ route('deals.show', $lead->convertedDeal) }}" class="font-semibold underline ml-2">View Deal: {{ $lead->convertedDeal->title }}</a>
            @endif
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl border border-slate-200 p-5 space-y-3 text-sm">
            <h3 class="font-semibold text-slate-900">Lead Details</h3>
            <p><span class="text-slate-500">Email:</span> {{ $lead->email ?? '—' }}</p>
            <p><span class="text-slate-500">Phone:</span> {{ $lead->phone ?? '—' }}</p>
            <p><span class="text-slate-500">Source:</span> {{ $lead->source?->label() }}</p>
            <p><span class="text-slate-500">Score:</span> {{ $lead->score }}/100</p>
            <p><span class="text-slate-500">Est. Value:</span> {{ $lead->estimated_value ? number_format((float) $lead->estimated_value, 2) : '—' }}</p>

            @can('update', $lead)
                <form method="POST" action="{{ route('leads.update', $lead) }}" class="pt-4 border-t border-slate-200 space-y-2">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="first_name" value="{{ $lead->first_name }}">
                    <input type="hidden" name="email" value="{{ $lead->email }}">
                    <input type="hidden" name="phone" value="{{ $lead->phone }}">
                    <input type="hidden" name="source" value="{{ $lead->source?->value }}">
                    <label class="block text-xs font-medium text-slate-600">Update Status</label>
                    <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                        @foreach (\App\Enums\LeadStatus::cases() as $case)
                            @if ($case !== \App\Enums\LeadStatus::Converted)
                                <option value="{{ $case->value }}" @selected($lead->status === $case)>{{ $case->label() }}</option>
                            @endif
                        @endforeach
                    </select>
                    <input type="text" name="disqualified_reason" value="{{ $lead->disqualified_reason }}" placeholder="Disqualified reason (if applicable)" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                    <button type="submit" class="w-full rounded-lg bg-slate-800 px-3 py-1.5 text-sm font-medium text-white">Save Status</button>
                </form>
            @endcan
        </div>

        <div class="lg:col-span-2 space-y-6">
            <x-crm.timeline-feed :subject="$lead" subjectType="lead" />
        </div>
    </div>

    @can('convert', $lead)
        <dialog id="convert-lead-dialog" class="rounded-2xl border border-slate-200 p-6 max-w-lg w-full m-auto shadow-xl backdrop:bg-slate-900/50">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Convert Lead to Deal</h3>
            <form method="POST" action="{{ route('leads.convert', $lead) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-slate-700">Company Name *</label>
                    <input type="text" name="company_name" value="{{ $lead->company_name ?: $lead->full_name . ' Co.' }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700">Deal Title *</label>
                    <input type="text" name="deal_title" value="{{ ($lead->company_name ?: $lead->full_name) . ' Opportunity' }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                @php $defaultPipeline = $pipelines->first(); @endphp
                <input type="hidden" name="pipeline_id" value="{{ $defaultPipeline?->id }}">
                <div>
                    <label class="block text-xs font-medium text-slate-700">Initial Stage *</label>
                    <select name="pipeline_stage_id" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @foreach ($defaultPipeline?->stages ?? [] as $stage)
                            <option value="{{ $stage->id }}">{{ $stage->name }} ({{ $stage->default_probability }}%)</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-700">Expected Value</label>
                        <input type="number" step="0.01" name="expected_value" value="{{ $lead->estimated_value ?? 0 }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700">Currency</label>
                        <input type="text" name="currency" value="{{ config('crm.currency', 'BDT') }}" maxlength="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-3">
                    <button type="button" onclick="document.getElementById('convert-lead-dialog').close()" class="rounded-lg border border-slate-300 px-4 py-2 text-sm">Cancel</button>
                    <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">Convert</button>
                </div>
            </form>
        </dialog>
    @endcan
</x-layouts.app>
