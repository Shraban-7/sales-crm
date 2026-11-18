<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Sales CRM' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full text-slate-900 antialiased">
<div class="min-h-full flex">
    <aside class="w-64 shrink-0 bg-slate-900 text-slate-100 flex-col justify-between hidden md:flex">
        <div>
            <div class="px-6 py-5 border-b border-slate-800 flex items-center gap-2">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-brand-600 font-bold text-white">S</span>
                <div>
                    <p class="text-sm font-semibold tracking-tight">Sales CRM</p>
                    <p class="text-xs text-slate-400">Revenue Platform</p>
                </div>
            </div>

            <nav class="p-3 space-y-1 text-sm" aria-label="Main navigation">
                <x-layouts.nav-links />
            </nav>
        </div>

        <div class="p-4 border-t border-slate-800 text-xs text-slate-400">
            <p class="font-medium text-slate-200">{{ auth()->user()->name }}</p>
            <p>{{ auth()->user()->getRoleNames()->first() ?? 'User' }} &middot; {{ auth()->user()->team?->name ?? 'No Team' }}</p>
        </div>
    </aside>

    <!-- Mobile navigation drawer -->
    <div data-mobile-nav-drawer class="fixed inset-0 z-50 hidden md:hidden" role="dialog" aria-modal="true" aria-label="Mobile navigation">
        <div data-mobile-nav-close class="fixed inset-0 bg-slate-900/60"></div>
        <aside class="relative z-10 w-72 max-w-[85vw] h-full bg-slate-900 text-slate-100 flex flex-col justify-between overflow-y-auto">
            <div>
                <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-brand-600 font-bold text-white">S</span>
                        <span class="text-sm font-semibold">Sales CRM</span>
                    </div>
                    <button type="button" data-mobile-nav-close aria-label="Close navigation" class="rounded-lg p-1.5 text-slate-300 hover:bg-slate-800">
                        &times;
                    </button>
                </div>

                <nav class="p-3 space-y-1 text-sm" aria-label="Mobile main navigation">
                    <x-layouts.nav-links />
                </nav>
            </div>

            <div class="p-4 border-t border-slate-800 text-xs text-slate-400">
                <p class="font-medium text-slate-200">{{ auth()->user()->name }}</p>
                <p>{{ auth()->user()->getRoleNames()->first() ?? 'User' }} &middot; {{ auth()->user()->team?->name ?? 'No Team' }}</p>
            </div>
        </aside>
    </div>

    <div class="flex-1 flex flex-col min-w-0">
        <header class="bg-white border-b border-slate-200 px-4 sm:px-6 py-3 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <button type="button" data-mobile-nav-open aria-label="Open navigation" aria-expanded="false" class="md:hidden inline-flex items-center justify-center rounded-lg border border-slate-200 p-2 text-slate-700 hover:bg-slate-100">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <h1 class="text-lg font-semibold text-slate-900 truncate">{{ $header ?? 'Sales CRM' }}</h1>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <x-crm.notification-bell />
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-slate-600 hover:text-slate-900 font-medium">Sign out</button>
                </form>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-6 max-w-7xl w-full mx-auto space-y-6">
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

            {{ $slot }}
        </main>
    </div>
</div>
</body>
</html>
