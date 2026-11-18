<x-layouts.app title="{{ $company->name }} — Company" header="{{ $company->name }}">
    <div class="flex justify-end gap-2">
        @can('update', $company)
            <button type="button" onclick="document.getElementById('edit-company-dialog').showModal()" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                Edit Company
            </button>
        @endcan
        @can('delete', $company)
            <form method="POST" action="{{ route('companies.destroy', $company) }}" onsubmit="return confirm('Delete this company?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-100">
                    Delete Company
                </button>
            </form>
        @endcan
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <x-crm.stat-card label="Open Pipeline" value="{{ config('crm.currency') }} {{ number_format($openPipeline, 2) }}" />
        <x-crm.stat-card label="Won Revenue" value="{{ config('crm.currency') }} {{ number_format($wonRevenue, 2) }}" />
        <x-crm.stat-card label="Contacts" value="{{ $company->contacts->count() }}" />
        <x-crm.stat-card label="Subsidiaries" value="{{ $company->subsidiaries->count() }}" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="space-y-4">
            <div class="bg-white rounded-xl border border-slate-200 p-5 space-y-2 text-sm">
                <h3 class="font-semibold text-slate-900">Account Profile</h3>
                <p><span class="text-slate-500">Industry:</span> {{ $company->industry ?? '—' }}</p>
                <p><span class="text-slate-500">Phone:</span> {{ $company->phone ?? '—' }}</p>
                <p><span class="text-slate-500">Website:</span> {{ $company->website ?? '—' }}</p>
                <p><span class="text-slate-500">Parent:</span> {{ $company->parentCompany?->name ?? '—' }}</p>
                <p><span class="text-slate-500">Location:</span> {{ $company->city ? "{$company->city}, " : '' }}{{ $company->country }}</p>
            </div>

            <x-crm.attachments :subject="$company" subject-type="company" />
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h3 class="text-sm font-semibold text-slate-900 mb-3">Deals</h3>
                <div class="divide-y divide-slate-100 text-sm">
                    @forelse ($company->deals as $deal)
                        <div class="py-2 flex items-center justify-between">
                            <a href="{{ route('deals.show', $deal) }}" class="font-medium text-brand-600 hover:underline">{{ $deal->title }}</a>
                            <div class="flex items-center gap-3">
                                <span>{{ $deal->currency }} {{ number_format((float) $deal->value, 2) }}</span>
                                <x-crm.status-badge :status="$deal->status" />
                            </div>
                        </div>
                    @empty
                        <p class="text-slate-500">No deals yet.</p>
                    @endforelse
                </div>
            </div>

            <x-crm.timeline-feed :subject="$company" subjectType="company" />
        </div>
    </div>

    @can('update', $company)
        <dialog id="edit-company-dialog" class="rounded-2xl border border-slate-200 p-6 max-w-lg w-full m-auto shadow-xl backdrop:bg-slate-900/50">
            <form method="POST" action="{{ route('companies.update', $company) }}" class="space-y-3">
                @csrf
                @method('PUT')
                <h3 class="text-base font-semibold text-slate-900">Edit Company</h3>
                <div>
                    <label class="block text-xs font-medium text-slate-600">Company Name *</label>
                    <input type="text" name="name" value="{{ old('name', $company->name) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Industry</label>
                        <input type="text" name="industry" value="{{ old('industry', $company->industry) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $company->phone) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600">Website</label>
                    <input type="url" name="website" value="{{ old('website', $company->website) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600">City</label>
                        <input type="text" name="city" value="{{ old('city', $company->city) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Country (ISO-2) *</label>
                        <input type="text" name="country" maxlength="2" value="{{ old('country', $company->country) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm uppercase">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600">Parent Company</label>
                    <select name="parent_company_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                        <option value="">None</option>
                        @foreach ($parentOptions as $parent)
                            <option value="{{ $parent->id }}" @selected((int) old('parent_company_id', $company->parent_company_id) === $parent->id)>{{ $parent->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('edit-company-dialog').close()" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">Cancel</button>
                    <button type="submit" class="rounded-lg bg-slate-900 px-4 py-1.5 text-sm font-semibold text-white">Save</button>
                </div>
            </form>
        </dialog>
    @endcan
</x-layouts.app>
