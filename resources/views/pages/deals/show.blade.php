<x-layouts.app title="{{ $deal->title }} — Deal" header="{{ $deal->title }}">
    <div class="crm-card p-4 flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-2">
            @foreach ($deal->pipeline->stages as $stage)
                @if ($stage->id === $deal->pipeline_stage_id)
                    <span class="inline-flex items-center rounded-xl bg-brand-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-xs">{{ $stage->name }}</span>
                @else
                    @can('moveStage', $deal)
                        @if ($stage->is_lost_stage)
                            <button
                                type="button"
                                onclick="document.getElementById('lost-deal-stage-id').value = {{ $stage->id }}; document.getElementById('lost-deal-dialog').showModal()"
                                class="inline-flex items-center rounded-xl border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-100 transition"
                            >
                                {{ $stage->name }}
                            </button>
                        @else
                            <form method="POST" action="{{ route('deals.move-stage', $deal) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="pipeline_stage_id" value="{{ $stage->id }}">
                                <button type="submit" class="inline-flex items-center rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-100 transition">
                                    {{ $stage->name }}
                                </button>
                            </form>
                        @endif
                    @else
                        <span class="inline-flex items-center rounded-xl border border-slate-200 px-3 py-1.5 text-xs text-slate-400">{{ $stage->name }}</span>
                    @endcan
                @endif
            @endforeach
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @can('update', $deal)
                <button type="button" onclick="document.getElementById('edit-deal-dialog').showModal()" class="crm-btn-secondary py-1.5 text-xs">
                    Edit Deal
                </button>
            @endcan
            @can('create', \App\Models\Quotation::class)
                <a href="{{ route('quotations.create', ['deal_id' => $deal->id]) }}" class="crm-btn-dark py-1.5 text-xs">New Quotation</a>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="space-y-4">
            <div class="crm-card p-5 space-y-3 text-sm">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                    <h3 class="font-semibold text-slate-900">Deal Summary</h3>
                    <x-crm.status-badge :status="$deal->status" />
                </div>
                <dl class="space-y-2.5">
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Company</dt>
                        <dd class="font-medium text-right">
                            <a href="{{ route('companies.show', $deal->company) }}" class="text-brand-600 hover:underline">{{ $deal->company?->name }}</a>
                        </dd>
                    </div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Value</dt><dd class="font-mono font-semibold text-slate-900 tabular-nums">{{ $deal->currency }} {{ number_format((float) $deal->value, 2) }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Weighted Value</dt><dd class="font-mono text-slate-700 tabular-nums">{{ $deal->currency }} {{ number_format((float) $deal->weighted_value, 2) }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Probability</dt><dd class="font-medium text-slate-900 tabular-nums">{{ $deal->probability }}%</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Owner</dt><dd class="font-medium text-slate-900 text-right">{{ $deal->owner?->name }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Expected Close</dt><dd class="font-medium text-slate-900 text-right">{{ $deal->expected_close_date?->toFormattedDateString() ?? '—' }}</dd></div>
                    @if ($deal->closed_at)
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Closed</dt><dd class="font-medium text-slate-900 text-right">{{ $deal->closed_at->toFormattedDateString() }}</dd></div>
                    @endif
                </dl>

                @if ($deal->status === \App\Enums\DealStatus::Lost)
                    <div class="rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs text-rose-900 space-y-1">
                        <p class="font-semibold">Lost reason</p>
                        <p>{{ $deal->lost_reason ?? 'Not recorded' }}</p>
                        @if ($deal->lost_competitor)
                            <p><span class="font-medium">Competitor:</span> {{ $deal->lost_competitor }}</p>
                        @endif
                    </div>
                @endif

                @if ($deal->status === \App\Enums\DealStatus::Won && $deal->won_notes)
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-xs text-emerald-900">
                        <p class="font-semibold">Won notes</p>
                        <p>{{ $deal->won_notes }}</p>
                    </div>
                @endif
            </div>

            <x-crm.attachments :subject="$deal" subject-type="deal" />

            <div class="crm-card p-5 space-y-3 text-sm">
                <h3 class="font-semibold text-slate-900 border-b border-slate-100 pb-2">Stage History</h3>
                <div class="space-y-2.5">
                    @foreach ($deal->stageHistories as $history)
                        <div class="text-xs text-slate-600 border-l-2 border-brand-300 pl-3 py-0.5">
                            <span class="font-semibold text-slate-900">{{ $history->fromStage?->name ?? 'New' }} &rarr; {{ $history->toStage?->name }}</span>
                            <p class="text-slate-400 mt-0.5">{{ $history->movedBy?->name }} &middot; {{ $history->created_at?->diffForHumans() }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="crm-card p-5 space-y-3">
                <h3 class="text-sm font-semibold text-slate-900">Quotations</h3>
                @forelse ($deal->quotations as $quote)
                    <div class="flex items-center justify-between text-sm border-b border-slate-100 last:border-b-0 pb-2.5 last:pb-0">
                        <a href="{{ route('quotations.show', $quote) }}" class="font-semibold text-brand-600 hover:underline">{{ $quote->quote_number }} (v{{ $quote->version }})</a>
                        <div class="flex items-center gap-3">
                            <span class="font-mono text-xs font-semibold text-slate-700 tabular-nums">{{ $quote->currency }} {{ number_format((float) $quote->grand_total, 2) }}</span>
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

    <dialog id="lost-deal-dialog" class="rounded-2xl border border-slate-200 p-6 max-w-lg w-full m-auto shadow-2xl backdrop:bg-slate-950/60">
        <form method="POST" action="{{ route('deals.move-stage', $deal) }}" class="space-y-4">
            @csrf
            @method('PATCH')
            <input type="hidden" id="lost-deal-stage-id" name="pipeline_stage_id" value="">

            <div>
                <h2 class="text-lg font-semibold text-slate-900">Close deal as lost</h2>
                <p class="text-sm text-slate-500 mt-1">A lost reason is required so the pipeline report explains why this deal did not close.</p>
            </div>

            <div>
                <label for="lost_reason" class="block text-sm font-medium text-slate-700 mb-1">Lost reason *</label>
                <textarea id="lost_reason" name="lost_reason" rows="3" required maxlength="150"
                          class="crm-input"
                          placeholder="e.g. Budget cut, chose a competitor, no decision">{{ old('lost_reason') }}</textarea>
                @error('lost_reason')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="lost_competitor" class="block text-sm font-medium text-slate-700 mb-1">Competitor (optional)</label>
                <input id="lost_competitor" name="lost_competitor" value="{{ old('lost_competitor') }}" maxlength="150" class="crm-input">
            </div>

            <div>
                <label for="lost_notes" class="block text-sm font-medium text-slate-700 mb-1">Stage notes (optional)</label>
                <textarea id="lost_notes" name="notes" rows="2" maxlength="500" class="crm-input">{{ old('notes') }}</textarea>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('lost-deal-dialog').close()" class="crm-btn-secondary">Cancel</button>
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700 transition">Mark Lost</button>
            </div>
        </form>
    </dialog>

    @can('update', $deal)
        <dialog id="edit-deal-dialog" class="rounded-2xl border border-slate-200 p-6 max-w-xl w-full m-auto shadow-2xl backdrop:bg-slate-950/60">
            <form method="POST" action="{{ route('deals.update', $deal) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Edit deal</h2>
                    <p class="text-sm text-slate-500 mt-1">Update the commercial details. Stage changes still happen from the pipeline bar.</p>
                </div>

                <div>
                    <label for="edit_title" class="block text-sm font-medium text-slate-700 mb-1">Title *</label>
                    <input id="edit_title" name="title" required maxlength="191" value="{{ old('title', $deal->title) }}" class="crm-input">
                    @error('title')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="edit_company_id" class="block text-sm font-medium text-slate-700 mb-1">Company *</label>
                        <select id="edit_company_id" name="company_id" required class="crm-select">
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}" @selected((int) old('company_id', $deal->company_id) === $company->id)>{{ $company->name }}</option>
                            @endforeach
                        </select>
                        @error('company_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="edit_primary_contact_id" class="block text-sm font-medium text-slate-700 mb-1">Primary contact</label>
                        <select id="edit_primary_contact_id" name="primary_contact_id" class="crm-select">
                            <option value="">None</option>
                            @foreach ($contacts as $contact)
                                <option value="{{ $contact->id }}" @selected((int) old('primary_contact_id', $deal->primary_contact_id) === $contact->id)>{{ $contact->full_name }}</option>
                            @endforeach
                        </select>
                        @error('primary_contact_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-2">
                        <label for="edit_value" class="block text-sm font-medium text-slate-700 mb-1">Value *</label>
                        <input id="edit_value" name="value" type="number" step="0.01" min="0" required value="{{ old('value', $deal->value) }}" class="crm-input">
                        @error('value')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="edit_currency" class="block text-sm font-medium text-slate-700 mb-1">Currency *</label>
                        <input id="edit_currency" name="currency" required maxlength="3" value="{{ old('currency', $deal->currency) }}" class="crm-input uppercase">
                        @error('currency')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="edit_probability" class="block text-sm font-medium text-slate-700 mb-1">Probability (%)</label>
                        <input id="edit_probability" name="probability" type="number" step="0.01" min="0" max="100" value="{{ old('probability', $deal->probability) }}" class="crm-input">
                        @error('probability')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="edit_expected_close_date" class="block text-sm font-medium text-slate-700 mb-1">Expected close date</label>
                        <input id="edit_expected_close_date" name="expected_close_date" type="date"
                               value="{{ old('expected_close_date', $deal->expected_close_date?->toDateString()) }}"
                               class="crm-input">
                        @error('expected_close_date')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="edit_won_notes" class="block text-sm font-medium text-slate-700 mb-1">Won notes (optional)</label>
                    <textarea id="edit_won_notes" name="won_notes" rows="2" maxlength="2000" class="crm-input">{{ old('won_notes', $deal->won_notes) }}</textarea>
                    @error('won_notes')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('edit-deal-dialog').close()" class="crm-btn-secondary">Cancel</button>
                    <button type="submit" class="crm-btn-dark">Save Changes</button>
                </div>
            </form>
        </dialog>
    @endcan
</x-layouts.app>
