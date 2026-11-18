<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Talk to Sales — Sales CRM</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-full py-10 px-4">
<div class="mx-auto w-full max-w-2xl bg-white rounded-2xl border border-slate-200 shadow-sm p-8 space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Talk to our sales team</h1>
        <p class="text-sm text-slate-500 mt-1">Tell us a little about your project and the right representative will get back to you.</p>
    </div>

    @if (session('status'))
        <div class="rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-lg bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-800">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-lg bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-800">
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
            <label for="first_name" class="block text-sm font-medium text-slate-700">First name *</label>
            <input id="first_name" name="first_name" value="{{ old('first_name') }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label for="last_name" class="block text-sm font-medium text-slate-700">Last name</label>
            <input id="last_name" name="last_name" value="{{ old('last_name') }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label for="email" class="block text-sm font-medium text-slate-700">Work email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label for="phone" class="block text-sm font-medium text-slate-700">Phone</label>
            <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label for="company_name" class="block text-sm font-medium text-slate-700">Company</label>
            <input id="company_name" name="company_name" value="{{ old('company_name') }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label for="job_title" class="block text-sm font-medium text-slate-700">Job title</label>
            <input id="job_title" name="job_title" value="{{ old('job_title') }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label for="website" class="block text-sm font-medium text-slate-700">Company website</label>
            <input id="website" name="website" type="url" value="{{ old('website') }}" placeholder="https://example.com" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label for="source" class="block text-sm font-medium text-slate-700">How did you hear about us? *</label>
            <select id="source" name="source" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @foreach ($sources as $source)
                    <option value="{{ $source->value }}" @selected(old('source') === $source->value)>{{ $source->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-2">
            <label for="message" class="block text-sm font-medium text-slate-700">What can we help with?</label>
            <textarea id="message" name="message" rows="4" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('message') }}</textarea>
        </div>
        <label class="md:col-span-2 flex items-start gap-2 text-sm text-slate-600">
            <input type="checkbox" name="consent" value="1" required class="mt-0.5 rounded border-slate-300">
            <span>I agree to be contacted about my enquiry and have read the privacy notice.</span>
        </label>
        <div class="md:col-span-2 flex justify-end">
            <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Send enquiry</button>
        </div>
    </form>
</div>
</body>
</html>
