<x-layouts.app title="{{ $company->name }} — Company" header="{{ $company->name }}">
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <x-crm.stat-card label="Open Pipeline" value="{{ config('crm.currency') }} {{ number_format($openPipeline, 2) }}" />
        <x-crm.stat-card label="Won Revenue" value="{{ config('crm.currency') }} {{ number_format($wonRevenue, 2) }}" />
        <x-crm.stat-card label="Contacts" value="{{ $company->contacts->count() }}" />
        <x-crm.stat-card label="Subsidiaries" value="{{ $company->subsidiaries->count() }}" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl border border-slate-200 p-5 space-y-2 text-sm">
            <h3 class="font-semibold text-slate-900">Account Profile</h3>
            <p><span class="text-slate-500">Industry:</span> {{ $company->industry ?? '—' }}</p>
            <p><span class="text-slate-500">Phone:</span> {{ $company->phone ?? '—' }}</p>
            <p><span class="text-slate-500">Website:</span> {{ $company->website ?? '—' }}</p>
            <p><span class="text-slate-500">Parent:</span> {{ $company->parentCompany?->name ?? '—' }}</p>
            <p><span class="text-slate-500">Location:</span> {{ $company->city ? "{$company->city}, " : '' }}{{ $company->country }}</p>
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
</x-layouts.app>
