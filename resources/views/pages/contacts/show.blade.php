<x-layouts.app title="{{ $contact->full_name }} — Contact" header="{{ $contact->full_name }}">
    @php
        $contactPhone = \App\Support\PhoneNumber::normalize($contact->mobile ?: $contact->phone);
        $contactPhoneIsUsable = $contactPhone !== null && preg_match('/^\+?\d{7,15}$/', $contactPhone) === 1;
        $contactSmsBody = rawurlencode('Hi '.$contact->first_name.', I wanted to follow up with you.');
        $whatsappUrl = $contactPhone !== null && preg_match('/^\+[1-9]\d{7,14}$/', $contactPhone) === 1
            ? 'https://wa.me/'.substr($contactPhone, 1).'?text='.$contactSmsBody
            : null;
    @endphp

    <div class="crm-card p-5 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-700 font-bold text-base ring-1 ring-brand-200">
                {{ strtoupper(mb_substr($contact->first_name, 0, 1).mb_substr((string) $contact->last_name, 0, 1)) }}
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-lg font-bold text-slate-900">{{ $contact->full_name }}</h2>
                    @if ($contact->is_primary_decision_maker)
                        <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-200/80">Decision Maker</span>
                    @endif
                </div>
                <p class="text-sm text-slate-500">{{ $contact->job_title ?? 'Contact' }}{{ $contact->company ? ' · '.$contact->company->name : '' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if ($contact->do_not_contact)
                <span class="text-xs text-rose-700" role="status">Call, SMS, and WhatsApp are unavailable: Do Not Contact is enabled.</span>
            @else
                @if ($contactPhoneIsUsable)
                    <a href="tel:{{ $contactPhone }}" class="crm-btn-secondary py-1.5 text-xs" aria-label="Call {{ $contact->full_name }}">Call</a>
                    <a href="sms:{{ $contactPhone }}?body={{ $contactSmsBody }}" class="crm-btn-secondary py-1.5 text-xs" aria-label="Text {{ $contact->full_name }}">SMS</a>
                @endif
                @if ($whatsappUrl)
                    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="crm-btn-secondary py-1.5 text-xs" aria-label="Open WhatsApp chat with {{ $contact->full_name }}">
                        WhatsApp
                    </a>
                @endif
            @endif
            @can('update', $contact)
                <button type="button" onclick="document.getElementById('edit-contact-dialog').showModal()" class="crm-btn-secondary py-1.5 text-xs">
                    Edit Contact
                </button>
            @endcan
            @can('delete', $contact)
                <form method="POST" action="{{ route('contacts.destroy', $contact) }}" onsubmit="return confirm('Delete this contact?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="crm-btn-danger py-1.5 text-xs">
                        Delete Contact
                    </button>
                </form>
            @endcan
        </div>
    </div>

    @if ($contact->do_not_contact)
        <div class="rounded-2xl bg-rose-50 border border-rose-200 px-4 py-3 text-sm font-medium text-rose-800">
            Warning: This contact is marked Do Not Contact. Automated outbound follow-ups are blocked.
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="space-y-4">
            <div class="crm-card p-5 space-y-3 text-sm">
                <h3 class="font-semibold text-slate-900 border-b border-slate-100 pb-2.5">Contact Details</h3>
                <dl class="space-y-2.5">
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Company</dt><dd class="font-medium text-slate-900 text-right">{{ $contact->company?->name ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Title</dt><dd class="font-medium text-slate-900 text-right">{{ $contact->job_title ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Email</dt><dd class="font-medium text-slate-900 text-right">{{ $contact->email ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Phone</dt><dd class="font-medium text-slate-900 text-right">{{ $contact->phone ?? '—' }}</dd></div>
                </dl>
            </div>

            <x-crm.attachments :subject="$contact" subject-type="contact" />
        </div>

        <div class="lg:col-span-2 space-y-6">
            <x-crm.timeline-feed :subject="$contact" subjectType="contact" />
        </div>
    </div>

    @can('update', $contact)
        <dialog id="edit-contact-dialog" class="rounded-2xl border border-slate-200 p-6 max-w-lg w-full m-auto shadow-2xl backdrop:bg-slate-950/60">
            <form method="POST" action="{{ route('contacts.update', $contact) }}" class="space-y-3.5">
                @csrf
                @method('PUT')
                <h3 class="text-base font-semibold text-slate-900">Edit Contact</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">First Name *</label>
                        <input type="text" name="first_name" value="{{ old('first_name', $contact->first_name) }}" required class="crm-input py-1.5">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Last Name *</label>
                        <input type="text" name="last_name" value="{{ old('last_name', $contact->last_name) }}" required class="crm-input py-1.5">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $contact->email) }}" class="crm-input py-1.5">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $contact->phone) }}" class="crm-input py-1.5">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Job Title</label>
                        <input type="text" name="job_title" value="{{ old('job_title', $contact->job_title) }}" class="crm-input py-1.5">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Company</label>
                        <select name="company_id" class="crm-select py-1.5">
                            <option value="">None</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}" @selected((int) old('company_id', $contact->company_id) === $company->id)>{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex items-center gap-4 text-xs text-slate-700 pt-1">
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="hidden" name="is_primary_decision_maker" value="0">
                        <input type="checkbox" name="is_primary_decision_maker" value="1" @checked($contact->is_primary_decision_maker) class="rounded border-slate-300 text-brand-600">
                        <span>Primary Decision Maker</span>
                    </label>
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="hidden" name="do_not_contact" value="0">
                        <input type="checkbox" name="do_not_contact" value="1" @checked($contact->do_not_contact) class="rounded border-slate-300 text-rose-600">
                        <span>Do Not Contact</span>
                    </label>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('edit-contact-dialog').close()" class="crm-btn-secondary py-1.5">Cancel</button>
                    <button type="submit" class="crm-btn-dark py-1.5">Save</button>
                </div>
            </form>
        </dialog>
    @endcan
</x-layouts.app>
