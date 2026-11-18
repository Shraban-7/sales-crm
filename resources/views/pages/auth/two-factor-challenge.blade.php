<!DOCTYPE html>
<html lang="en" class="h-full antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify Sign-In — Sale Fusion CRM</title>
    <x-crm.favicons />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-gradient-to-br from-slate-950 via-slate-900 to-brand-950 flex items-center justify-center p-6">
<div class="w-full max-w-md bg-white/95 backdrop-blur rounded-2xl border border-white/10 shadow-2xl p-8 space-y-6">
    <div class="flex items-center gap-3">
        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 ring-1 ring-brand-200">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
            </svg>
        </div>
        <div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900">Verify your sign-in</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                6-digit code sent to <strong class="text-slate-700">{{ $email }}</strong>@if ($expiresAt) (expires {{ $expiresAt->format('H:i') }})@endif
            </p>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-3.5 py-2.5 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    @if (session('error'))
        <div class="rounded-xl bg-rose-50 border border-rose-200 px-3.5 py-2.5 text-sm text-rose-700">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl bg-rose-50 border border-rose-200 px-3.5 py-2.5 text-sm text-rose-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('two-factor.store') }}" class="space-y-4">
        @csrf
        <div>
            <label for="code" class="block text-xs font-semibold uppercase tracking-wide text-slate-600 mb-1.5">Verification code</label>
            <input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" required autofocus
                   class="crm-input text-center text-2xl font-mono tracking-[0.4em]">
        </div>
        <button type="submit" class="crm-btn-dark w-full py-2.5">Verify and Sign In</button>
    </form>

    <div class="flex items-center justify-between border-t border-slate-200 pt-4 text-sm">
        <form method="POST" action="{{ route('two-factor.resend') }}">
            @csrf
            <button type="submit" @disabled(! $canResend) class="font-medium text-brand-600 hover:text-brand-700 disabled:text-slate-400">
                Resend code
            </button>
        </form>
        <form method="POST" action="{{ route('two-factor.cancel') }}">
            @csrf
            <button type="submit" class="text-slate-500 hover:text-slate-800">Cancel</button>
        </form>
    </div>
</div>
</body>
</html>
