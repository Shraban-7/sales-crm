<x-layouts.app title="Audit Logs — Sales CRM" header="Immutable Security & Compliance Audit Logs">
    <form method="GET" action="{{ route('settings.audit-logs.index') }}" class="crm-card p-5 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Event</label>
            <input type="text" name="event" value="{{ $event }}" placeholder="e.g. login_succeeded, accepted" class="crm-input w-auto min-w-56">
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Actor</label>
            <select name="user_id" class="crm-select w-auto min-w-44">
                <option value="">All Users</option>
                @foreach ($users as $actor)
                    <option value="{{ $actor->id }}" @selected((string) $userId === (string) $actor->id)>{{ $actor->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="crm-btn-dark">Filter Logs</button>
        @if ($event || $userId)
            <a href="{{ route('settings.audit-logs.index') }}" class="crm-btn-secondary">Reset</a>
        @endif
    </form>

    <div class="crm-table-wrap">
        <table class="crm-table">
            <thead class="crm-thead">
            <tr>
                <th class="px-4 py-3">Timestamp</th>
                <th class="px-4 py-3">Actor</th>
                <th class="px-4 py-3">Event</th>
                <th class="px-4 py-3">Entity</th>
                <th class="px-4 py-3">IP</th>
                <th class="px-4 py-3">Diff</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($logs as $log)
                <tr class="hover:bg-slate-50/80 transition">
                    <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap tabular-nums">{{ $log->created_at?->toDateTimeString() }}</td>
                    <td class="px-4 py-3 font-medium text-slate-900">{{ $log->user?->name ?? 'System' }}</td>
                    <td class="px-4 py-3">
                        <span class="rounded-md bg-slate-100 px-2 py-0.5 font-mono text-xs text-slate-700">{{ $log->event }}</span>
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-600">{{ $log->auditable_type }} #{{ $log->auditable_id }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $log->ip_address }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-slate-600">
                        <details>
                            <summary class="cursor-pointer font-semibold text-brand-600 hover:underline">View diff</summary>
                            <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2 max-w-md">
                                <pre class="bg-slate-50 p-2.5 rounded-lg border border-slate-200 text-[11px] overflow-x-auto">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                                <pre class="bg-slate-50 p-2.5 rounded-lg border border-slate-200 text-[11px] overflow-x-auto">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </details>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">No audit logs recorded yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $logs->links() }}
</x-layouts.app>
