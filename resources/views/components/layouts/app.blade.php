<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Sale Fusion CRM') }}</title>
    <x-crm.favicons />
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#2563eb">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full text-slate-900 antialiased">
@php
    $userName = auth()->user()->name;
    $initials = collect(explode(' ', $userName))
        ->filter()
        ->take(2)
        ->map(fn (string $part): string => strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
@endphp

<div class="min-h-full flex">
    <aside class="w-64 shrink-0 bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950 text-slate-100 flex-col justify-between hidden md:flex border-r border-slate-800/80">
        <div>
            <div class="px-5 py-4 border-b border-slate-800/80 flex items-center gap-3">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 font-bold text-white shadow-sm">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                </span>
                <div>
                    <p class="text-sm font-semibold tracking-tight text-white">{{ config('app.name', 'Sale Fusion CRM') }}</p>
                    <p class="text-[11px] text-slate-400">Revenue Workspace</p>
                </div>
            </div>

            <nav class="p-3 overflow-y-auto text-sm" aria-label="Main navigation">
                <x-layouts.nav-links />
            </nav>
        </div>

        <div class="p-4 border-t border-slate-800/80 bg-slate-950/50 flex items-center gap-3">
            <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-600/20 text-xs font-semibold text-brand-100 ring-1 ring-brand-500/40">
                {{ $initials ?: 'U' }}
            </span>
            <div class="min-w-0">
                <p class="text-xs font-semibold text-slate-100 truncate">{{ $userName }}</p>
                <p class="text-[11px] text-slate-400 truncate">{{ auth()->user()->getRoleNames()->first() ?? 'User' }} &middot; {{ auth()->user()->team?->name ?? 'No Team' }}</p>
            </div>
        </div>
    </aside>

    <!-- Mobile navigation drawer -->
    <div data-mobile-nav-drawer class="fixed inset-0 z-50 hidden md:hidden" role="dialog" aria-modal="true" aria-label="Mobile navigation">
        <div data-mobile-nav-close class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs"></div>
        <aside class="relative z-10 w-72 max-w-[85vw] h-full bg-slate-900 text-slate-100 flex flex-col justify-between overflow-y-auto">
            <div>
                <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-brand-600 font-bold text-white">S</span>
                        <span class="text-sm font-semibold">{{ config('app.name', 'Sale Fusion CRM') }}</span>
                    </div>
                    <button type="button" data-mobile-nav-close aria-label="Close navigation" class="rounded-lg p-1.5 text-slate-300 hover:bg-slate-800">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <nav class="p-3 text-sm" aria-label="Mobile main navigation">
                    <x-layouts.nav-links />
                </nav>
            </div>

            <div class="p-4 border-t border-slate-800 flex items-center gap-3">
                <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-600/20 text-xs font-semibold text-brand-100">
                    {{ $initials ?: 'U' }}
                </span>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-slate-200 truncate">{{ $userName }}</p>
                    <p class="text-[11px] text-slate-400 truncate">{{ auth()->user()->getRoleNames()->first() ?? 'User' }} &middot; {{ auth()->user()->team?->name ?? 'No Team' }}</p>
                </div>
            </div>
        </aside>
    </div>

    <div class="flex-1 flex flex-col min-w-0">
        <header class="sticky top-0 z-30 bg-white/90 backdrop-blur-md border-b border-slate-200/80 px-4 sm:px-6 py-3 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <button type="button" data-mobile-nav-open aria-label="Open navigation" aria-expanded="false" class="md:hidden inline-flex items-center justify-center rounded-xl border border-slate-200 p-2 text-slate-700 hover:bg-slate-100">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div class="min-w-0">
                    <h1 class="text-base sm:text-lg font-semibold tracking-tight text-slate-900 truncate">{{ $header ?? config('app.name', 'Sale Fusion CRM') }}</h1>
                </div>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <span class="hidden sm:inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
                    {{ auth()->user()->getRoleNames()->first() ?? 'User' }}
                </span>

                <x-crm.notification-bell />

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Sign out" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200/90 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-2xs transition hover:bg-slate-50 hover:text-slate-900">
                        <svg class="h-4 w-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                        </svg>
                        <span class="hidden sm:inline">Sign out</span>
                    </button>
                </form>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-6 max-w-7xl w-full mx-auto space-y-6">
            @if (session('status'))
                <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800 flex items-center gap-2.5 shadow-2xs">
                    <svg class="h-4 w-4 shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-xl bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-800 flex items-center gap-2.5 shadow-2xs">
                    <svg class="h-4 w-4 shrink-0 text-rose-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-xl bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-800 shadow-2xs">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</div>
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        });
    }
</script>
</body>
</html>
