<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify Sign-In — Sales CRM</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-6">
<div class="w-full max-w-md bg-white rounded-2xl border border-slate-200 shadow-sm p-8 space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Verify your sign-in</h1>
        <p class="text-sm text-slate-500 mt-1">
            We emailed a 6-digit code to <strong>{{ $email }}</strong>.
            @if ($expiresAt)
                It expires at {{ $expiresAt->format('H:i') }}.
            @endif
        </p>
    </div>

    @if (session('status'))
        <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    @if (session('error'))
        <div class="rounded-lg bg-rose-50 border border-rose-200 p-3 text-sm text-rose-700">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="rounded-lg bg-rose-50 border border-rose-200 p-3 text-sm text-rose-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('two-factor.store') }}" class="space-y-4">
        @csrf
        <div>
            <label for="code" class="block text-sm font-medium text-slate-700">Verification code</label>
            <input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" required autofocus
                   class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-center text-2xl font-mono tracking-[0.4em]">
        </div>
        <button type="submit" class="w-full rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Verify and Sign In</button>
    </form>

    <div class="flex items-center justify-between border-t border-slate-200 pt-4 text-sm">
        <form method="POST" action="{{ route('two-factor.resend') }}">
            @csrf
            <button type="submit" @disabled(! $canResend) class="font-medium text-brand-600 disabled:text-slate-400">
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
