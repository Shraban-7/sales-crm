<x-layouts.app title="{{ $contact->full_name }} — Contact" header="{{ $contact->full_name }}">
    @if ($contact->do_not_contact)
        <div class="rounded-xl bg-rose-50 border border-rose-200 px-4 py-3 text-sm font-medium text-rose-800">
            Warning: This contact is marked Do Not Contact. Automated outbound follow-ups are blocked.
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl border border-slate-200 p-5 space-y-2 text-sm">
            <h3 class="font-semibold text-slate-900">Contact Details</h3>
            <p><span class="text-slate-500">Company:</span> {{ $contact->company?->name ?? '—' }}</p>
            <p><span class="text-slate-500">Title:</span> {{ $contact->job_title ?? '—' }}</p>
            <p><span class="text-slate-500">Email:</span> {{ $contact->email ?? '—' }}</p>
            <p><span class="text-slate-500">Phone:</span> {{ $contact->phone ?? '—' }}</p>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <x-crm.timeline-feed :subject="$contact" subjectType="contact" />
        </div>
    </div>
</x-layouts.app>
