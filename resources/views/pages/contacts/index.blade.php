<x-layouts.app title="Contacts — Sales CRM" header="Contacts">
    <form method="GET" action="{{ route('contacts.index') }}" class="flex gap-2">
        <input type="text" name="search" value="{{ $search }}" placeholder="Search contacts..." class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
        <button type="submit" class="rounded-lg bg-slate-800 px-3 py-2 text-sm font-medium text-white">Search</button>
    </form>

    @can('create', \App\Models\Contact::class)
        <form method="POST" action="{{ route('contacts.store') }}" class="bg-white rounded-xl border border-slate-200 p-5 grid grid-cols-1 md:grid-cols-6 gap-3">
            @csrf
            <input type="text" name="first_name" placeholder="First Name *" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <input type="text" name="last_name" placeholder="Last Name *" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <input type="email" name="email" placeholder="Email" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <input type="text" name="job_title" placeholder="Job Title" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <select name="company_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">— No Company —</option>
                @foreach ($companies as $company)
                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Add Contact</button>
        </form>
    @endcan

    <div class="bg-white rounded-xl border border-slate-200 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
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
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 font-medium"><a href="{{ route('contacts.show', $contact) }}" class="text-brand-600 hover:underline">{{ $contact->full_name }}</a></td>
                    <td class="px-4 py-3 text-slate-600">{{ $contact->company?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $contact->job_title ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $contact->email ?? '—' }}</td>
                    <td class="px-4 py-3 space-x-1">
                        @if ($contact->is_primary_decision_maker)
                            <span class="rounded bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700">Decision Maker</span>
                        @endif
                        @if ($contact->do_not_contact)
                            <span class="rounded bg-rose-50 px-2 py-0.5 text-xs font-medium text-rose-700">Do Not Contact</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No contacts found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $contacts->links() }}
</x-layouts.app>
