<!DOCTYPE html>
<html lang="en" class="h-full antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Talk to Sales — Sale Fusion CRM</title>
    <x-crm.favicons />
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-full bg-gradient-to-br from-slate-100 via-slate-50 to-brand-50/50 py-12 px-4">
<div class="mx-auto w-full max-w-2xl bg-white rounded-2xl border border-slate-200/80 shadow-lg p-8 space-y-6">
    <div class="flex items-start gap-3.5 border-b border-slate-100 pb-5">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 text-white shadow-sm">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 0 1 .778-.332 48.294 48.294 0 0 0 5.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
            </svg>
        </div>
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Talk to our sales team</h1>
            <p class="text-sm text-slate-500 mt-1">Tell us a little about your project and the right representative will get back to you.</p>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-xl bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-800">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-800">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('leads.capture.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @csrf
        <div class="hidden" aria-hidden="true">
            <label for="company_website">Company website</label>
            <input id="company_website" type="text" name="company_website" tabindex="-1" autocomplete="off">
        </div>

        <div>
            <label for="first_name" class="block text-xs font-semibold uppercase tracking-wide text-slate-600 mb-1.5">First name *</label>
            <input id="first_name" name="first_name" value="{{ old('first_name') }}" required class="crm-input">
        </div>
        <div>
            <label for="last_name" class="block text-xs font-semibold uppercase tracking-wide text-slate-600 mb-1.5">Last name</label>
            <input id="last_name" name="last_name" value="{{ old('last_name') }}" class="crm-input">
        </div>
        <div>
            <label for="email" class="block text-xs font-semibold uppercase tracking-wide text-slate-600 mb-1.5">Work email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" class="crm-input">
        </div>
        <div>
            <label for="phone" class="block text-xs font-semibold uppercase tracking-wide text-slate-600 mb-1.5">Phone</label>
            <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" class="crm-input">
        </div>
        <div>
            <label for="company_name" class="block text-xs font-semibold uppercase tracking-wide text-slate-600 mb-1.5">Company</label>
            <input id="company_name" name="company_name" value="{{ old('company_name') }}" class="crm-input">
        </div>
        <div>
            <label for="job_title" class="block text-xs font-semibold uppercase tracking-wide text-slate-600 mb-1.5">Job title</label>
            <input id="job_title" name="job_title" value="{{ old('job_title') }}" class="crm-input">
        </div>
        <div>
            <label for="website" class="block text-xs font-semibold uppercase tracking-wide text-slate-600 mb-1.5">Company website</label>
            <input id="website" name="website" type="url" value="{{ old('website') }}" placeholder="https://example.com" class="crm-input">
        </div>
        <div>
            <label for="source" class="block text-xs font-semibold uppercase tracking-wide text-slate-600 mb-1.5">How did you hear about us? *</label>
            <select id="source" name="source" required class="crm-select">
                @foreach ($sources as $source)
                    <option value="{{ $source->value }}" @selected(old('source') === $source->value)>{{ $source->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-2">
            <label for="message" class="block text-xs font-semibold uppercase tracking-wide text-slate-600 mb-1.5">What can we help with?</label>
            <textarea id="message" name="message" rows="4" class="crm-input">{{ old('message') }}</textarea>
        </div>
        <label class="md:col-span-2 flex items-start gap-2.5 text-sm text-slate-600 cursor-pointer">
            <input type="checkbox" name="consent" value="1" required class="mt-0.5 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
            <span>I agree to be contacted about my enquiry and have read the privacy notice.</span>
        </label>
        <div class="md:col-span-2 flex justify-end pt-2">
            <button type="submit" class="crm-btn-primary px-6 py-2.5">Send enquiry</button>
        </div>
    </form>
</div>
</body>
</html>
