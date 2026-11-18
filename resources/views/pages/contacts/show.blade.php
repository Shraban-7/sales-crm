<x-layouts.app title="{{ $contact->full_name }} — Contact" header="{{ $contact->full_name }}">
    <div class="flex justify-end gap-2">
        @can('update', $contact)
            <button type="button" onclick="document.getElementById('edit-contact-dialog').showModal()" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                Edit Contact
            </button>
        @endcan
        @can('delete', $contact)
            <form method="POST" action="{{ route('contacts.destroy', $contact) }}" onsubmit="return confirm('Delete this contact?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-100">
                    Delete Contact
                </button>
            </form>
        @endcan
    </div>

    @if ($contact->do_not_contact)
        <div class="rounded-xl bg-rose-50 border border-rose-200 px-4 py-3 text-sm font-medium text-rose-800">
            Warning: This contact is marked Do Not Contact. Automated outbound follow-ups are blocked.
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="space-y-4">
            <div class="bg-white rounded-xl border border-slate-200 p-5 space-y-2 text-sm">
                <h3 class="font-semibold text-slate-900">Contact Details</h3>
                <p><span class="text-slate-500">Company:</span> {{ $contact->company?->name ?? '—' }}</p>
                <p><span class="text-slate-500">Title:</span> {{ $contact->job_title ?? '—' }}</p>
                <p><span class="text-slate-500">Email:</span> {{ $contact->email ?? '—' }}</p>
                <p><span class="text-slate-500">Phone:</span> {{ $contact->phone ?? '—' }}</p>
            </div>

            <x-crm.attachments :subject="$contact" subject-type="contact" />
        </div>

        <div class="lg:col-span-2 space-y-6">
            <x-crm.timeline-feed :subject="$contact" subjectType="contact" />
        </div>
    </div>

    @can('update', $contact)
        <dialog id="edit-contact-dialog" class="rounded-2xl border border-slate-200 p-6 max-w-lg w-full m-auto shadow-xl backdrop:bg-slate-900/50">
            <form method="POST" action="{{ route('contacts.update', $contact) }}" class="space-y-3">
                @csrf
                @method('PUT')
                <h3 class="text-base font-semibold text-slate-900">Edit Contact</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600">First Name *</label>
                        <input type="text" name="first_name" value="{{ old('first_name', $contact->first_name) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Last Name *</label>
                        <input type="text" name="last_name" value="{{ old('last_name', $contact->last_name) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Email</label>
                        <input type="email" name="email" value="{{ old('email', $contact->email) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $contact->phone) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Job Title</label>
                        <input type="text" name="job_title" value="{{ old('job_title', $contact->job_title) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Company</label>
                        <select name="company_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                            <option value="">None</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}" @selected((int) old('company_id', $contact->company_id) === $company->id)>{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex items-center gap-4 text-xs text-slate-700">
                    <label class="flex items-center gap-1.5">
                        <input type="hidden" name="is_primary_decision_maker" value="0">
                        <input type="checkbox" name="is_primary_decision_maker" value="1" @checked($contact->is_primary_decision_maker)>
                        <span>Primary Decision Maker</span>
                    </label>
                    <label class="flex items-center gap-1.5">
                        <input type="hidden" name="do_not_contact" value="0">
                        <input type="checkbox" name="do_not_contact" value="1" @checked($contact->do_not_contact)>
                        <span>Do Not Contact</span>
                    </label>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('edit-contact-dialog').close()" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">Cancel</button>
                    <button type="submit" class="rounded-lg bg-slate-900 px-4 py-1.5 text-sm font-semibold text-white">Save</button>
                </div>
            </form>
        </dialog>
    @endcan
</x-layouts.app>
