<x-layouts.app title="{{ $deal->title }} — Deal" header="{{ $deal->title }}">
    <div class="bg-white rounded-xl border border-slate-200 p-4 flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-2">
            @foreach ($deal->pipeline->stages as $stage)
                @if ($stage->id === $deal->pipeline_stage_id)
                    <span class="rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white">{{ $stage->name }}</span>
                @else
                    @can('moveStage', $deal)
                        @if ($stage->is_lost_stage)
                            <button
                                type="button"
                                onclick="document.getElementById('lost-deal-stage-id').value = {{ $stage->id }}; document.getElementById('lost-deal-dialog').showModal()"
                                class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-100"
                            >
                                {{ $stage->name }}
                            </button>
                        @else
                            <form method="POST" action="{{ route('deals.move-stage', $deal) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="pipeline_stage_id" value="{{ $stage->id }}">
                                <button type="submit" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-100">
                                    {{ $stage->name }}
                                </button>
                            </form>
                        @endif
                    @else
                        <span class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs text-slate-400">{{ $stage->name }}</span>
                    @endcan
                @endif
            @endforeach
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @can('update', $deal)
                <button type="button" onclick="document.getElementById('edit-deal-dialog').showModal()" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                    Edit Deal
                </button>
            @endcan
            @can('create', \App\Models\Quotation::class)
                <a href="{{ route('quotations.create', ['deal_id' => $deal->id]) }}" class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white">New Quotation</a>
            @endcan
        </div>
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
                <p><span class="text-slate-500">Expected Close:</span> {{ $deal->expected_close_date?->toFormattedDateString() ?? '—' }}</p>
                @if ($deal->closed_at)
                    <p><span class="text-slate-500">Closed:</span> {{ $deal->closed_at->toFormattedDateString() }}</p>
                @endif

                @if ($deal->status === \App\Enums\DealStatus::Lost)
                    <div class="rounded-lg border border-rose-200 bg-rose-50 p-3 text-xs text-rose-900 space-y-1">
                        <p class="font-semibold">Lost reason</p>
                        <p>{{ $deal->lost_reason ?? 'Not recorded' }}</p>
                        @if ($deal->lost_competitor)
                            <p><span class="font-medium">Competitor:</span> {{ $deal->lost_competitor }}</p>
                        @endif
                    </div>
                @endif

                @if ($deal->status === \App\Enums\DealStatus::Won && $deal->won_notes)
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-xs text-emerald-900">
                        <p class="font-semibold">Won notes</p>
                        <p>{{ $deal->won_notes }}</p>
                    </div>
                @endif
            </div>

            <x-crm.attachments :subject="$deal" subject-type="deal" />

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

    <dialog id="lost-deal-dialog" class="rounded-2xl border border-slate-200 p-6 max-w-lg w-full m-auto shadow-xl backdrop:bg-slate-900/50">
        <form method="POST" action="{{ route('deals.move-stage', $deal) }}" class="space-y-4">
            @csrf
            @method('PATCH')
            <input type="hidden" id="lost-deal-stage-id" name="pipeline_stage_id" value="">

            <div>
                <h2 class="text-lg font-semibold text-slate-900">Close deal as lost</h2>
                <p class="text-sm text-slate-500 mt-1">A lost reason is required so the pipeline report explains why this deal did not close.</p>
            </div>

            <div>
                <label for="lost_reason" class="block text-sm font-medium text-slate-700">Lost reason *</label>
                <textarea id="lost_reason" name="lost_reason" rows="3" required maxlength="150"
                          class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                          placeholder="e.g. Budget cut, chose a competitor, no decision">{{ old('lost_reason') }}</textarea>
                @error('lost_reason')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="lost_competitor" class="block text-sm font-medium text-slate-700">Competitor (optional)</label>
                <input id="lost_competitor" name="lost_competitor" value="{{ old('lost_competitor') }}" maxlength="150"
                       class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label for="lost_notes" class="block text-sm font-medium text-slate-700">Stage notes (optional)</label>
                <textarea id="lost_notes" name="notes" rows="2" maxlength="500"
                          class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('notes') }}</textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('lost-deal-dialog').close()" class="rounded-lg border border-slate-300 px-4 py-2 text-sm">Cancel</button>
                <button type="submit" class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">Mark Lost</button>
            </div>
        </form>
    </dialog>

    @can('update', $deal)
        <dialog id="edit-deal-dialog" class="rounded-2xl border border-slate-200 p-6 max-w-xl w-full m-auto shadow-xl backdrop:bg-slate-900/50">
            <form method="POST" action="{{ route('deals.update', $deal) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Edit deal</h2>
                    <p class="text-sm text-slate-500 mt-1">Update the commercial details. Stage changes still happen from the pipeline bar.</p>
                </div>

                <div>
                    <label for="edit_title" class="block text-sm font-medium text-slate-700">Title *</label>
                    <input id="edit_title" name="title" required maxlength="191" value="{{ old('title', $deal->title) }}"
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @error('title')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="edit_company_id" class="block text-sm font-medium text-slate-700">Company *</label>
                        <select id="edit_company_id" name="company_id" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}" @selected((int) old('company_id', $deal->company_id) === $company->id)>{{ $company->name }}</option>
                            @endforeach
                        </select>
                        @error('company_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="edit_primary_contact_id" class="block text-sm font-medium text-slate-700">Primary contact</label>
                        <select id="edit_primary_contact_id" name="primary_contact_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
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
                        <label for="edit_value" class="block text-sm font-medium text-slate-700">Value *</label>
                        <input id="edit_value" name="value" type="number" step="0.01" min="0" required value="{{ old('value', $deal->value) }}"
                               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('value')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="edit_currency" class="block text-sm font-medium text-slate-700">Currency *</label>
                        <input id="edit_currency" name="currency" required maxlength="3" value="{{ old('currency', $deal->currency) }}"
                               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm uppercase">
                        @error('currency')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="edit_probability" class="block text-sm font-medium text-slate-700">Probability (%)</label>
                        <input id="edit_probability" name="probability" type="number" step="0.01" min="0" max="100" value="{{ old('probability', $deal->probability) }}"
                               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('probability')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="edit_expected_close_date" class="block text-sm font-medium text-slate-700">Expected close date</label>
                        <input id="edit_expected_close_date" name="expected_close_date" type="date"
                               value="{{ old('expected_close_date', $deal->expected_close_date?->toDateString()) }}"
                               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('expected_close_date')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="edit_won_notes" class="block text-sm font-medium text-slate-700">Won notes (optional)</label>
                    <textarea id="edit_won_notes" name="won_notes" rows="2" maxlength="2000"
                              class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('won_notes', $deal->won_notes) }}</textarea>
                    @error('won_notes')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('edit-deal-dialog').close()" class="rounded-lg border border-slate-300 px-4 py-2 text-sm">Cancel</button>
                    <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Save Changes</button>
                </div>
            </form>
        </dialog>
    @endcan
</x-layouts.app>
