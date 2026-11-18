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
    @elseif ($lead->status === \App\Enums\LeadStatus::Disqualified)
        <div class="rounded-xl bg-amber-50 border border-amber-200 p-4 text-sm text-amber-900">
            This lead is disqualified and cannot be converted. Change the status back to New, Contacted, or Qualified first.
        </div>
    @endif

    @if ($possibleDuplicates !== [])
        <div class="rounded-xl bg-amber-50 border border-amber-200 p-4 space-y-3">
            <h3 class="text-sm font-semibold text-amber-900">Possible duplicate leads</h3>
            <div class="space-y-2">
                @foreach ($possibleDuplicates as $duplicate)
                    <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
                        <span>
                            <a href="{{ route('leads.show', $duplicate) }}" class="font-medium text-amber-900 underline">{{ $duplicate->full_name }}</a>
                            <span class="text-amber-800">— {{ $duplicate->email ?? $duplicate->phone ?? 'no contact' }} ({{ $duplicate->owner?->name }})</span>
                        </span>
                        @can('update', $lead)
                            <form method="POST" action="{{ route('leads.merge', $lead) }}" onsubmit="return confirm('Merge this duplicate into the current lead? The duplicate will be removed.');">
                                @csrf
                                <input type="hidden" name="duplicate_lead_id" value="{{ $duplicate->id }}">
                                <button type="submit" class="rounded-lg border border-amber-300 bg-white px-3 py-1 text-xs font-medium text-amber-900">Merge into this lead</button>
                            </form>
                        @endcan
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="space-y-4">
            <div class="bg-white rounded-xl border border-slate-200 p-5 space-y-3 text-sm">
                <h3 class="font-semibold text-slate-900">Lead Details</h3>
                <p><span class="text-slate-500">Email:</span> {{ $lead->email ?? '—' }}</p>
                <p><span class="text-slate-500">Phone:</span> {{ $lead->phone ?? '—' }}</p>
                <p><span class="text-slate-500">Source:</span> {{ $lead->source?->label() }}</p>
                <p><span class="text-slate-500">Score:</span> {{ $lead->score }}/100</p>
                <p><span class="text-slate-500">Est. Value:</span> {{ $lead->estimated_value ? number_format((float) $lead->estimated_value, 2) : '—' }}</p>
                <p><span class="text-slate-500">Owner:</span> {{ $lead->owner?->name }}</p>

                @can('update', $lead)
                    <details class="pt-4 border-t border-slate-200">
                        <summary class="cursor-pointer text-xs font-semibold text-brand-700">Edit lead details</summary>
                        <form method="POST" action="{{ route('leads.update', $lead) }}" class="mt-3 space-y-2">
                            @csrf
                            @method('PUT')
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-medium text-slate-600">First name *</label>
                                    <input type="text" name="first_name" value="{{ $lead->first_name }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600">Last name</label>
                                    <input type="text" name="last_name" value="{{ $lead->last_name }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600">Company</label>
                                <input type="text" name="company_name" value="{{ $lead->company_name }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600">Job title</label>
                                <input type="text" name="job_title" value="{{ $lead->job_title }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600">Email</label>
                                <input type="email" name="email" value="{{ $lead->email }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600">Phone</label>
                                <input type="text" name="phone" value="{{ $lead->phone }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600">Website</label>
                                <input type="url" name="website" value="{{ $lead->website }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-medium text-slate-600">Source *</label>
                                    <select name="source" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                                        @foreach (\App\Enums\LeadSource::cases() as $case)
                                            <option value="{{ $case->value }}" @selected($lead->source === $case)>{{ $case->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600">Status *</label>
                                    <select name="status" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                                        @foreach (\App\Enums\LeadStatus::cases() as $case)
                                            @continue($case === \App\Enums\LeadStatus::Converted)
                                            <option value="{{ $case->value }}" @selected($lead->status === $case)>{{ $case->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-medium text-slate-600">Score</label>
                                    <input type="number" min="0" max="100" name="score" value="{{ $lead->score }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600">Est. value</label>
                                    <input type="number" step="0.01" min="0" name="estimated_value" value="{{ $lead->estimated_value }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600">Disqualified reason</label>
                                <input type="text" name="disqualified_reason" value="{{ $lead->disqualified_reason }}" placeholder="Required when disqualified" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                            </div>
                            <button type="submit" class="w-full rounded-lg bg-slate-800 px-3 py-1.5 text-sm font-medium text-white">Save Lead</button>
                        </form>
                    </details>
                @endcan
            </div>

            <x-crm.attachments :subject="$lead" subject-type="lead" />
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
