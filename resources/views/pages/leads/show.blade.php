<x-layouts.app title="{{ $lead->full_name }} — Lead" header="Lead Profile">
    @php
        $leadPhone = \App\Support\PhoneNumber::normalize($lead->phone);
        $leadPhoneIsUsable = $leadPhone !== null && preg_match('/^\+?\d{7,15}$/', $leadPhone) === 1;
        $leadSmsBody = rawurlencode('Hi '.$lead->first_name.', I wanted to follow up with you.');
    @endphp

    <div class="crm-card p-5 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-brand-700 font-bold text-base ring-1 ring-brand-200/80">
                {{ strtoupper(mb_substr($lead->first_name, 0, 1).mb_substr((string) $lead->last_name, 0, 1)) }}
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <h2 class="text-xl font-bold tracking-tight text-slate-900">{{ $lead->full_name }}</h2>
                    <x-crm.status-badge :status="$lead->status" />
                </div>
                <p class="text-sm text-slate-500 mt-0.5">{{ $lead->job_title }} {{ $lead->company_name ? 'at ' . $lead->company_name : '' }}</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if ($leadPhoneIsUsable)
                <a href="tel:{{ $leadPhone }}" class="crm-btn-secondary py-1.5 text-xs" aria-label="Call {{ $lead->full_name }}">Call</a>
                <a href="sms:{{ $leadPhone }}?body={{ $leadSmsBody }}" class="crm-btn-secondary py-1.5 text-xs" aria-label="Text {{ $lead->full_name }}">SMS</a>
            @elseif ($lead->phone)
                <span class="text-xs text-slate-500" title="Use a phone number with 7 to 15 digits, optionally starting with +.">Call and SMS need a valid phone number</span>
            @endif
            @can('convert', $lead)
                <button type="button" onclick="document.getElementById('convert-lead-dialog').showModal()" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-xs hover:bg-emerald-700 transition">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                    </svg>
                    Convert to Deal
                </button>
            @endcan
        </div>
    </div>

    @if ($lead->isConverted())
        <div class="rounded-2xl bg-emerald-50 border border-emerald-200 px-4 py-3.5 text-sm text-emerald-900 flex flex-wrap items-center justify-between gap-2">
            <span>Converted on {{ $lead->converted_at?->toDayDateTimeString() }}.</span>
            @if ($lead->convertedDeal)
                <a href="{{ route('deals.show', $lead->convertedDeal) }}" class="font-semibold text-emerald-800 underline hover:text-emerald-950">View Deal: {{ $lead->convertedDeal->title }} &rarr;</a>
            @endif
        </div>
    @elseif ($lead->status === \App\Enums\LeadStatus::Disqualified)
        <div class="rounded-2xl bg-amber-50 border border-amber-200 px-4 py-3.5 text-sm text-amber-900">
            This lead is disqualified and cannot be converted. Change the status back to New, Contacted, or Qualified first.
        </div>
    @endif

    @if ($possibleDuplicates !== [])
        <div class="rounded-2xl bg-amber-50 border border-amber-200 p-5 space-y-3">
            <h3 class="text-sm font-semibold text-amber-900">Possible duplicate leads detected</h3>
            <div class="space-y-2">
                @foreach ($possibleDuplicates as $duplicate)
                    <div class="flex flex-wrap items-center justify-between gap-2 rounded-xl bg-white/80 border border-amber-200/80 px-3.5 py-2.5 text-sm">
                        <span>
                            <a href="{{ route('leads.show', $duplicate) }}" class="font-semibold text-amber-950 underline">{{ $duplicate->full_name }}</a>
                            <span class="text-amber-800"> — {{ $duplicate->email ?? $duplicate->phone ?? 'no contact' }} ({{ $duplicate->owner?->name }})</span>
                        </span>
                        @can('update', $lead)
                            <form method="POST" action="{{ route('leads.merge', $lead) }}" onsubmit="return confirm('Merge this duplicate into the current lead? The duplicate will be removed.');">
                                @csrf
                                <input type="hidden" name="duplicate_lead_id" value="{{ $duplicate->id }}">
                                <button type="submit" class="crm-btn-secondary py-1 text-xs border-amber-300 text-amber-900">Merge into this lead</button>
                            </form>
                        @endcan
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="space-y-4">
            <div class="crm-card p-5 space-y-4 text-sm">
                <h3 class="font-semibold text-slate-900 border-b border-slate-100 pb-2.5">Lead Details</h3>
                <dl class="space-y-2.5">
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Email</dt><dd class="font-medium text-slate-900 text-right">{{ $lead->email ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Phone</dt><dd class="font-medium text-slate-900 text-right">{{ $lead->phone ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Source</dt><dd class="font-medium text-slate-900 text-right">{{ $lead->source?->label() }}</dd></div>
                    <div class="flex justify-between items-center gap-3">
                        <dt class="text-slate-500">Score</dt>
                        <dd class="font-mono text-xs font-semibold text-slate-900">{{ $lead->score }}/100</dd>
                    </div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Est. Value</dt><dd class="font-medium text-slate-900 tabular-nums">{{ $lead->estimated_value ? number_format((float) $lead->estimated_value, 2) : '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Owner</dt><dd class="font-medium text-slate-900 text-right">{{ $lead->owner?->name }}</dd></div>
                </dl>

                @can('update', $lead)
                    <details class="pt-4 border-t border-slate-100">
                        <summary class="cursor-pointer text-xs font-semibold text-brand-700 hover:text-brand-800">Edit lead details</summary>
                        <form method="POST" action="{{ route('leads.update', $lead) }}" class="mt-3 space-y-2.5">
                            @csrf
                            @method('PUT')
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">First name *</label>
                                    <input type="text" name="first_name" value="{{ $lead->first_name }}" required class="crm-input py-1.5">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Last name</label>
                                    <input type="text" name="last_name" value="{{ $lead->last_name }}" class="crm-input py-1.5">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Company</label>
                                <input type="text" name="company_name" value="{{ $lead->company_name }}" class="crm-input py-1.5">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Job title</label>
                                <input type="text" name="job_title" value="{{ $lead->job_title }}" class="crm-input py-1.5">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Email</label>
                                <input type="email" name="email" value="{{ $lead->email }}" class="crm-input py-1.5">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Phone</label>
                                <input type="text" name="phone" value="{{ $lead->phone }}" class="crm-input py-1.5">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Website</label>
                                <input type="url" name="website" value="{{ $lead->website }}" class="crm-input py-1.5">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Source *</label>
                                    <select name="source" class="crm-select py-1.5">
                                        @foreach (\App\Enums\LeadSource::cases() as $case)
                                            <option value="{{ $case->value }}" @selected($lead->source === $case)>{{ $case->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Status *</label>
                                    <select name="status" class="crm-select py-1.5">
                                        @foreach (\App\Enums\LeadStatus::cases() as $case)
                                            @continue($case === \App\Enums\LeadStatus::Converted)
                                            <option value="{{ $case->value }}" @selected($lead->status === $case)>{{ $case->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Score</label>
                                    <input type="number" min="0" max="100" name="score" value="{{ $lead->score }}" class="crm-input py-1.5">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Est. value</label>
                                    <input type="number" step="0.01" min="0" name="estimated_value" value="{{ $lead->estimated_value }}" class="crm-input py-1.5">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Disqualified reason</label>
                                <input type="text" name="disqualified_reason" value="{{ $lead->disqualified_reason }}" placeholder="Required when disqualified" class="crm-input py-1.5">
                            </div>
                            <button type="submit" class="crm-btn-dark w-full">Save Lead</button>
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
        <dialog id="convert-lead-dialog" class="rounded-2xl border border-slate-200 p-6 max-w-lg w-full m-auto shadow-2xl backdrop:bg-slate-950/60">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Convert Lead to Deal</h3>
            <form method="POST" action="{{ route('leads.convert', $lead) }}" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Company Name *</label>
                    <input type="text" name="company_name" value="{{ $lead->company_name ?: $lead->full_name . ' Co.' }}" required class="crm-input">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Deal Title *</label>
                    <input type="text" name="deal_title" value="{{ ($lead->company_name ?: $lead->full_name) . ' Opportunity' }}" required class="crm-input">
                </div>
                @php $defaultPipeline = $pipelines->first(); @endphp
                <input type="hidden" name="pipeline_id" value="{{ $defaultPipeline?->id }}">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Initial Stage *</label>
                    <select name="pipeline_stage_id" required class="crm-select">
                        @foreach ($defaultPipeline?->stages ?? [] as $stage)
                            <option value="{{ $stage->id }}">{{ $stage->name }} ({{ $stage->default_probability }}%)</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Expected Value</label>
                        <input type="number" step="0.01" name="expected_value" value="{{ $lead->estimated_value ?? 0 }}" class="crm-input">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Currency</label>
                        <input type="text" name="currency" value="{{ config('crm.currency', 'BDT') }}" maxlength="3" class="crm-input uppercase">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('convert-lead-dialog').close()" class="crm-btn-secondary">Cancel</button>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition">Convert</button>
                </div>
            </form>
        </dialog>
    @endcan
</x-layouts.app>
