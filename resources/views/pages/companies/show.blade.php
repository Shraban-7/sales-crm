<x-layouts.app title="{{ $company->name }} — Company" header="{{ $company->name }}">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-50 text-brand-700 font-bold text-sm ring-1 ring-brand-200">
                {{ strtoupper(mb_substr($company->name, 0, 2)) }}
            </div>
            <div>
                <p class="text-sm font-semibold text-slate-900">{{ $company->industry ?? 'Account Profile' }}</p>
                <p class="text-xs text-slate-500">{{ $company->city ? "{$company->city}, " : '' }}{{ $company->country }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @can('update', $company)
                <button type="button" onclick="document.getElementById('edit-company-dialog').showModal()" class="crm-btn-secondary py-1.5 text-xs">
                    Edit Company
                </button>
            @endcan
            @can('delete', $company)
                <form method="POST" action="{{ route('companies.destroy', $company) }}" onsubmit="return confirm('Delete this company?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="crm-btn-danger py-1.5 text-xs">
                        Delete Company
                    </button>
                </form>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-crm.stat-card accent="brand" label="Open Pipeline" value="{{ config('crm.currency') }} {{ number_format($openPipeline, 2) }}" />
        <x-crm.stat-card accent="emerald" label="Won Revenue" value="{{ config('crm.currency') }} {{ number_format($wonRevenue, 2) }}" />
        <x-crm.stat-card accent="indigo" label="Contacts" value="{{ $company->contacts->count() }}" />
        <x-crm.stat-card accent="amber" label="Subsidiaries" value="{{ $company->subsidiaries->count() }}" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="space-y-4">
            <div class="crm-card p-5 space-y-3 text-sm">
                <h3 class="font-semibold text-slate-900 border-b border-slate-100 pb-2.5">Account Profile</h3>
                <dl class="space-y-2.5">
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Industry</dt><dd class="font-medium text-slate-900 text-right">{{ $company->industry ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Phone</dt><dd class="font-medium text-slate-900 text-right">{{ $company->phone ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Website</dt><dd class="font-medium text-slate-900 text-right truncate">{{ $company->website ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Parent</dt><dd class="font-medium text-slate-900 text-right">{{ $company->parentCompany?->name ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Location</dt><dd class="font-medium text-slate-900 text-right">{{ $company->city ? "{$company->city}, " : '' }}{{ $company->country }}</dd></div>
                </dl>
            </div>

            <x-crm.attachments :subject="$company" subject-type="company" />
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="crm-card p-5">
                <h3 class="text-sm font-semibold text-slate-900 mb-3">Deals</h3>
                <div class="divide-y divide-slate-100 text-sm">
                    @forelse ($company->deals as $deal)
                        <div class="py-2.5 flex items-center justify-between gap-3">
                            <a href="{{ route('deals.show', $deal) }}" class="font-semibold text-slate-900 hover:text-brand-600 transition">{{ $deal->title }}</a>
                            <div class="flex items-center gap-3">
                                <span class="font-mono text-xs font-semibold text-slate-700 tabular-nums">{{ $deal->currency }} {{ number_format((float) $deal->value, 2) }}</span>
                                <x-crm.status-badge :status="$deal->status" />
                            </div>
                        </div>
                    @empty
                        <p class="text-slate-500 py-4 text-center">No deals yet.</p>
                    @endforelse
                </div>
            </div>

            <x-crm.timeline-feed :subject="$company" subjectType="company" />
        </div>
    </div>

    @can('update', $company)
        <dialog id="edit-company-dialog" class="rounded-2xl border border-slate-200 p-6 max-w-lg w-full m-auto shadow-2xl backdrop:bg-slate-950/60">
            <form method="POST" action="{{ route('companies.update', $company) }}" class="space-y-3.5">
                @csrf
                @method('PUT')
                <h3 class="text-base font-semibold text-slate-900">Edit Company</h3>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Company Name *</label>
                    <input type="text" name="name" value="{{ old('name', $company->name) }}" required class="crm-input py-1.5">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Industry</label>
                        <input type="text" name="industry" value="{{ old('industry', $company->industry) }}" class="crm-input py-1.5">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $company->phone) }}" class="crm-input py-1.5">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Website</label>
                    <input type="url" name="website" value="{{ old('website', $company->website) }}" class="crm-input py-1.5">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">City</label>
                        <input type="text" name="city" value="{{ old('city', $company->city) }}" class="crm-input py-1.5">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Country (ISO-2) *</label>
                        <input type="text" name="country" maxlength="2" value="{{ old('country', $company->country) }}" required class="crm-input py-1.5 uppercase">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Parent Company</label>
                    <select name="parent_company_id" class="crm-select py-1.5">
                        <option value="">None</option>
                        @foreach ($parentOptions as $parent)
                            <option value="{{ $parent->id }}" @selected((int) old('parent_company_id', $company->parent_company_id) === $parent->id)>{{ $parent->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('edit-company-dialog').close()" class="crm-btn-secondary py-1.5">Cancel</button>
                    <button type="submit" class="crm-btn-dark py-1.5">Save</button>
                </div>
            </form>
        </dialog>
    @endcan
</x-layouts.app>
