<x-layouts.app title="Companies — Sales CRM" header="Companies">
    <div class="crm-card p-4 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" action="{{ route('companies.index') }}" class="flex flex-wrap items-center gap-2.5 flex-1">
            <div class="relative flex-1 min-w-52 max-w-sm">
                <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 6.15 6.15a7.5 7.5 0 0 0 10.5 10.5Z" />
                </svg>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search companies..." class="crm-input pl-9">
            </div>
            <button type="submit" class="crm-btn-dark">Search</button>
            @if ($search)
                <a href="{{ route('companies.index') }}" class="crm-btn-secondary">Reset</a>
            @endif
        </form>
        <a href="{{ route('imports.index') }}" class="crm-btn-secondary">Import / Export</a>
    </div>

    @can('create', \App\Models\Company::class)
        <form method="POST" action="{{ route('companies.store') }}" class="crm-card p-5 space-y-3">
            @csrf
            <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Add Company Account</h2>
            <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                <input type="text" name="name" placeholder="Company Name *" required class="md:col-span-2 crm-input">
                <input type="text" name="industry" placeholder="Industry" class="crm-input">
                <input type="text" name="city" placeholder="City" class="crm-input">
                <input type="text" name="country" value="{{ config('crm.country', 'BD') }}" maxlength="2" required class="crm-input uppercase">
                <button type="submit" class="crm-btn-primary">Add Company</button>
            </div>
        </form>
    @endcan

    <div class="crm-table-wrap">
        <table class="crm-table">
            <thead class="crm-thead">
            <tr>
                <th class="px-4 py-3">Company</th>
                <th class="px-4 py-3">Industry</th>
                <th class="px-4 py-3">City / Country</th>
                <th class="px-4 py-3">Contacts</th>
                <th class="px-4 py-3">Deals</th>
                <th class="px-4 py-3">Owner</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($companies as $company)
                <tr class="hover:bg-slate-50/80 transition">
                    <td class="px-4 py-3 font-medium">
                        <a href="{{ route('companies.show', $company) }}" class="inline-flex items-center gap-2.5 font-semibold text-slate-900 hover:text-brand-600 transition">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-xs font-bold text-slate-700">
                                {{ strtoupper(mb_substr($company->name, 0, 2)) }}
                            </span>
                            <span>{{ $company->name }}</span>
                        </a>
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $company->industry ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $company->city ? "{$company->city}, " : '' }}{{ $company->country }}</td>
                    <td class="px-4 py-3 tabular-nums text-slate-700">{{ $company->contacts_count }}</td>
                    <td class="px-4 py-3 tabular-nums text-slate-700">{{ $company->deals_count }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $company->owner?->name }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">No companies found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $companies->links() }}
</x-layouts.app>
