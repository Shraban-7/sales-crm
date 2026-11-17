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
    <aside class="w-64 shrink-0 bg-slate-900 text-slate-100 flex flex-col justify-between hidden md:flex">
        <div>
            <div class="px-6 py-5 border-b border-slate-800 flex items-center gap-2">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-brand-600 font-bold text-white">S</span>
                <div>
                    <p class="text-sm font-semibold tracking-tight">Sales CRM</p>
                    <p class="text-xs text-slate-400">Revenue Platform</p>
                </div>
            </div>

            <nav class="p-3 space-y-1 text-sm">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white font-medium' : 'text-slate-300 hover:bg-slate-800/60' }}">Dashboard</a>
                @can('viewAny', \App\Models\Lead::class)
                    <a href="{{ route('leads.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('leads.*') ? 'bg-slate-800 text-white font-medium' : 'text-slate-300 hover:bg-slate-800/60' }}">Leads</a>
                @endcan
                @can('viewAny', \App\Models\Company::class)
                    <a href="{{ route('companies.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('companies.*') ? 'bg-slate-800 text-white font-medium' : 'text-slate-300 hover:bg-slate-800/60' }}">Companies</a>
                @endcan
                @can('viewAny', \App\Models\Contact::class)
                    <a href="{{ route('contacts.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('contacts.*') ? 'bg-slate-800 text-white font-medium' : 'text-slate-300 hover:bg-slate-800/60' }}">Contacts</a>
                @endcan
                @can('viewAny', \App\Models\Pipeline::class)
                    <a href="{{ route('pipelines.kanban') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('pipelines.*') ? 'bg-slate-800 text-white font-medium' : 'text-slate-300 hover:bg-slate-800/60' }}">Pipeline Kanban</a>
                @endcan
                @can('viewAny', \App\Models\Deal::class)
                    <a href="{{ route('deals.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('deals.*') ? 'bg-slate-800 text-white font-medium' : 'text-slate-300 hover:bg-slate-800/60' }}">Deals</a>
                @endcan
                @can('viewAny', \App\Models\Task::class)
                    <a href="{{ route('tasks.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('tasks.*') ? 'bg-slate-800 text-white font-medium' : 'text-slate-300 hover:bg-slate-800/60' }}">Tasks</a>
                @endcan
                @can('viewAny', \App\Models\FollowUp::class)
                    <a href="{{ route('follow-ups.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('follow-ups.*') ? 'bg-slate-800 text-white font-medium' : 'text-slate-300 hover:bg-slate-800/60' }}">Follow-Ups</a>
                @endcan
                @can('viewAny', \App\Models\Quotation::class)
                    <a href="{{ route('quotations.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('quotations.*') ? 'bg-slate-800 text-white font-medium' : 'text-slate-300 hover:bg-slate-800/60' }}">Quotations</a>
                @endcan
                @if(auth()->user()->can('reports.view.own') || auth()->user()->can('reports.view.team') || auth()->user()->can('reports.view.all'))
                    <a href="{{ route('reports.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('reports.*') ? 'bg-slate-800 text-white font-medium' : 'text-slate-300 hover:bg-slate-800/60' }}">Reports</a>
                @endif
                @can('viewAny', \App\Models\ImportExportJob::class)
                    <a href="{{ route('imports.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('imports.*') ? 'bg-slate-800 text-white font-medium' : 'text-slate-300 hover:bg-slate-800/60' }}">Import / Export</a>
                @endcan
                @can('viewAny', \App\Models\User::class)
                    <a href="{{ route('settings.users.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('settings.users.*') ? 'bg-slate-800 text-white font-medium' : 'text-slate-300 hover:bg-slate-800/60' }}">Users &amp; Teams</a>
                @endcan
                @can('viewAny', \App\Models\AuditLog::class)
                    <a href="{{ route('settings.audit-logs.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('settings.audit-logs.*') ? 'bg-slate-800 text-white font-medium' : 'text-slate-300 hover:bg-slate-800/60' }}">Audit Logs</a>
                @endcan
            </nav>
        </div>

        <div class="p-4 border-t border-slate-800 text-xs text-slate-400">
            <p class="font-medium text-slate-200">{{ auth()->user()->name }}</p>
            <p>{{ auth()->user()->getRoleNames()->first() ?? 'User' }} &middot; {{ auth()->user()->team?->name ?? 'No Team' }}</p>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0">
        <header class="bg-white border-b border-slate-200 px-6 py-3 flex items-center justify-between">
            <h1 class="text-lg font-semibold text-slate-900">{{ $header ?? 'Sales CRM' }}</h1>

            <div class="flex items-center gap-4">
                <x-crm.notification-bell />
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-slate-600 hover:text-slate-900 font-medium">Sign out</button>
                </form>
            </div>
        </header>

        <main class="flex-1 p-6 max-w-7xl w-full mx-auto space-y-6">
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
