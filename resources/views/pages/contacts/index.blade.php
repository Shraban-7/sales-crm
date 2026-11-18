<x-layouts.app title="Contacts — Sales CRM" header="Contacts">
    <div class="crm-card p-4 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" action="{{ route('contacts.index') }}" class="flex flex-wrap items-center gap-2.5 flex-1">
            <div class="relative flex-1 min-w-52 max-w-sm">
                <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 6.15 6.15a7.5 7.5 0 0 0 10.5 10.5Z" />
                </svg>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search contacts..." class="crm-input pl-9">
            </div>
            <button type="submit" class="crm-btn-dark">Search</button>
            @if ($search)
                <a href="{{ route('contacts.index') }}" class="crm-btn-secondary">Reset</a>
            @endif
        </form>
        <a href="{{ route('imports.index') }}" class="crm-btn-secondary">Import / Export</a>
    </div>

    @can('create', \App\Models\Contact::class)
        <form method="POST" action="{{ route('contacts.store') }}" class="crm-card p-5 space-y-3">
            @csrf
            <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Add Contact</h2>
            <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                <input type="text" name="first_name" placeholder="First Name *" required class="crm-input">
                <input type="text" name="last_name" placeholder="Last Name *" required class="crm-input">
                <input type="email" name="email" placeholder="Email" class="crm-input">
                <input type="text" name="job_title" placeholder="Job Title" class="crm-input">
                <select name="company_id" class="crm-select">
                    <option value="">— No Company —</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="crm-btn-primary">Add Contact</button>
            </div>
        </form>
    @endcan

    <div class="crm-table-wrap">
        <table class="crm-table">
            <thead class="crm-thead">
            <tr>
                <th class="px-4 py-3">Name</th>
                <th class="px-4 py-3">Company</th>
                <th class="px-4 py-3">Job Title</th>
                <th class="px-4 py-3">Email</th>
                <th class="px-4 py-3">Flags</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($contacts as $contact)
                <tr class="hover:bg-slate-50/80 transition">
                    <td class="px-4 py-3 font-medium">
                        <a href="{{ route('contacts.show', $contact) }}" class="inline-flex items-center gap-2.5 font-semibold text-slate-900 hover:text-brand-600 transition">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-brand-50 text-xs font-bold text-brand-700">
                                {{ strtoupper(mb_substr($contact->first_name, 0, 1).mb_substr((string) $contact->last_name, 0, 1)) }}
                            </span>
                            <span>{{ $contact->full_name }}</span>
                        </a>
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $contact->company?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $contact->job_title ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $contact->email ?? '—' }}</td>
                    <td class="px-4 py-3 space-x-1">
                        @if ($contact->is_primary_decision_maker)
                            <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-200/80">Decision Maker</span>
                        @endif
                        @if ($contact->do_not_contact)
                            <span class="inline-flex items-center rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-medium text-rose-700 ring-1 ring-inset ring-rose-200/80">Do Not Contact</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">No contacts found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $contacts->links() }}
</x-layouts.app>
