<x-layouts.app title="Companies — Sales CRM" header="Companies">
    <form method="GET" action="{{ route('companies.index') }}" class="flex gap-2">
        <input type="text" name="search" value="{{ $search }}" placeholder="Search companies..." class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
        <button type="submit" class="rounded-lg bg-slate-800 px-3 py-2 text-sm font-medium text-white">Search</button>
    </form>

    @can('create', \App\Models\Company::class)
        <form method="POST" action="{{ route('companies.store') }}" class="bg-white rounded-xl border border-slate-200 p-5 grid grid-cols-1 md:grid-cols-6 gap-3">
            @csrf
            <input type="text" name="name" placeholder="Company Name *" required class="md:col-span-2 rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <input type="text" name="industry" placeholder="Industry" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <input type="text" name="city" placeholder="City" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <input type="text" name="country" value="{{ config('crm.country', 'BD') }}" maxlength="2" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Add Company</button>
        </form>
    @endcan

    <div class="bg-white rounded-xl border border-slate-200 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
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
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 font-medium"><a href="{{ route('companies.show', $company) }}" class="text-brand-600 hover:underline">{{ $company->name }}</a></td>
                    <td class="px-4 py-3 text-slate-600">{{ $company->industry ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $company->city ? "{$company->city}, " : '' }}{{ $company->country }}</td>
                    <td class="px-4 py-3">{{ $company->contacts_count }}</td>
                    <td class="px-4 py-3">{{ $company->deals_count }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $company->owner?->name }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">No companies found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $companies->links() }}
</x-layouts.app>
