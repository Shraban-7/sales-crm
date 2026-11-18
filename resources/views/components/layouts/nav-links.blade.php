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
<a href="{{ route('settings.security.edit') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('settings.security.*') ? 'bg-slate-800 text-white font-medium' : 'text-slate-300 hover:bg-slate-800/60' }}">Security</a>
