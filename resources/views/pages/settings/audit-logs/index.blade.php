<x-layouts.app title="Audit Logs — Sales CRM" header="Immutable Security & Compliance Audit Logs">
    <form method="GET" action="{{ route('settings.audit-logs.index') }}" class="bg-white rounded-xl border border-slate-200 p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-slate-600">Event</label>
            <input type="text" name="event" value="{{ $event }}" placeholder="e.g. login_succeeded, accepted" class="mt-1 rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600">Actor</label>
            <select name="user_id" class="mt-1 rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                <option value="">All Users</option>
                @foreach ($users as $actor)
                    <option value="{{ $actor->id }}" @selected((string) $userId === (string) $actor->id)>{{ $actor->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">Filter</button>
    </form>

    <div class="bg-white rounded-xl border border-slate-200 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
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
                <tr>
                    <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">{{ $log->created_at?->toDateTimeString() }}</td>
                    <td class="px-4 py-3">{{ $log->user?->name ?? 'System' }}</td>
                    <td class="px-4 py-3 font-mono text-xs">{{ $log->event }}</td>
                    <td class="px-4 py-3 text-xs">{{ $log->auditable_type }} #{{ $log->auditable_id }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $log->ip_address }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-slate-600">
                        <details>
                            <summary class="cursor-pointer text-brand-600">View diff</summary>
                            <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2 max-w-md">
                                <pre class="bg-slate-50 p-2 rounded border text-[11px] overflow-x-auto">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                                <pre class="bg-slate-50 p-2 rounded border text-[11px] overflow-x-auto">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </details>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">No audit logs recorded yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $logs->links() }}
</x-layouts.app>
