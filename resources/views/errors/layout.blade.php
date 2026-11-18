@php
    $tone = trim((string) $__env->yieldContent('tone', 'brand'));
    $badgeTone = match ($tone) {
        'rose' => 'bg-rose-50 text-rose-700 ring-rose-200/80',
        'amber' => 'bg-amber-50 text-amber-800 ring-amber-200/80',
        'indigo' => 'bg-indigo-50 text-indigo-700 ring-indigo-200/80',
        'slate' => 'bg-slate-100 text-slate-700 ring-slate-200/80',
        default => 'bg-brand-50 text-brand-700 ring-brand-200/80',
    };
    $iconTone = match ($tone) {
        'rose' => 'bg-rose-100 text-rose-600 ring-rose-200',
        'amber' => 'bg-amber-100 text-amber-600 ring-amber-200',
        'indigo' => 'bg-indigo-100 text-indigo-600 ring-indigo-200',
        'slate' => 'bg-slate-100 text-slate-600 ring-slate-200',
        default => 'bg-brand-100 text-brand-600 ring-brand-200',
    };
    $codeTone = match ($tone) {
        'rose' => 'text-rose-600/20',
        'amber' => 'text-amber-600/20',
        'indigo' => 'text-indigo-600/20',
        'slate' => 'text-slate-600/20',
        default => 'text-brand-600/20',
    };

    $isSignedIn = rescue(static fn (): bool => auth()->check(), false, report: false);
    $homeUrl = $isSignedIn ? url('/') : url('/login');
    $homeLabel = $isSignedIn ? 'Back to Dashboard' : 'Go to Sign In';
    $hasAssets = file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot'));
@endphp
<!DOCTYPE html>
<html lang="en" class="h-full antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code', 'Error') — @yield('title', 'Something went wrong') | {{ config('app.name', 'Sales CRM') }}</title>
    <x-crm.favicons />
    @if ($hasAssets)
        @vite(['resources/css/app.css'])
    @endif
</head>
<body class="min-h-full relative overflow-x-hidden bg-slate-950 text-slate-900 flex flex-col justify-between p-6 sm:p-10">
    {{-- Ambient background --}}
    <div class="pointer-events-none fixed inset-0 bg-gradient-to-br from-slate-950 via-slate-900 to-brand-950"></div>
    <div class="pointer-events-none fixed -top-32 -left-24 h-96 w-96 rounded-full bg-brand-600/20 blur-3xl"></div>
    <div class="pointer-events-none fixed -bottom-32 -right-24 h-96 w-96 rounded-full bg-indigo-500/20 blur-3xl"></div>
    <div class="pointer-events-none fixed inset-0 opacity-[0.05]"
         style="background-image: linear-gradient(to right, #ffffff 1px, transparent 1px), linear-gradient(to bottom, #ffffff 1px, transparent 1px); background-size: 40px 40px;"></div>

    {{-- Top brand bar --}}
    <header class="relative z-10 mx-auto w-full max-w-4xl flex items-center justify-between">
        <a href="{{ $homeUrl }}" class="inline-flex items-center gap-3 text-white hover:opacity-90 transition">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-indigo-600 shadow-lg ring-1 ring-white/20">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                </svg>
            </span>
            <span>
                <span class="block text-sm font-bold tracking-tight text-white">{{ config('app.name', 'Sales CRM') }}</span>
                <span class="block text-[11px] text-slate-400">Revenue Workspace</span>
            </span>
        </a>

        <a href="{{ url('/capture/lead') }}" class="text-xs font-medium text-slate-300 hover:text-white transition">
            Need help? Contact Sales &rarr;
        </a>
    </header>

    {{-- Centered error card --}}
    <main class="relative z-10 mx-auto w-full max-w-xl my-auto py-10">
        <div class="relative overflow-hidden rounded-3xl bg-white/95 backdrop-blur-md border border-white/20 shadow-2xl p-8 sm:p-10 space-y-6">
            <span aria-hidden="true" class="pointer-events-none select-none absolute -top-4 right-5 text-8xl sm:text-9xl font-black tracking-tighter {{ $codeTone }}">
                @yield('code', 'ERR')
            </span>

            <div class="relative flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl ring-1 {{ $iconTone }}">
                    @yield('icon')
                </div>
                <div class="space-y-1.5 min-w-0">
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wider ring-1 ring-inset {{ $badgeTone }}">
                        HTTP @yield('code', 'Error') &middot; @yield('label', 'Notice')
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">
                        @yield('title', 'Something went wrong')
                    </h1>
                </div>
            </div>

            <p class="relative text-sm sm:text-base text-slate-600 leading-relaxed">
                @yield('message')
            </p>

            @hasSection('tip')
                <div class="rounded-2xl bg-slate-50 border border-slate-200/80 px-4 py-3 text-xs text-slate-600 leading-relaxed">
                    <span class="font-semibold text-slate-800">Helpful tip:</span> @yield('tip')
                </div>
            @endif

            <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-slate-100">
                <a
                    href="{{ $homeUrl }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-brand-600/20 hover:from-brand-700 hover:to-indigo-700 transition"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    <span>{{ $homeLabel }}</span>
                </a>

                @yield('secondary_action')
            </div>
        </div>
    </main>

    <footer class="relative z-10 mx-auto w-full max-w-4xl flex flex-wrap items-center justify-between gap-2 text-xs text-slate-400">
        <span>{{ config('app.name', 'Sales CRM') }} &middot; Protected Workspace</span>
        <span>{{ now()->toFormattedDateString() }}</span>
    </footer>
</body>
</html>
