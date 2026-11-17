<x-layouts.app title="Audit Logs — Sales CRM" header="Immutable Security &amp; Compliance Audit Logs">
    <div class="bg-white rounded-xl border border-slate-200 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
            <tr>
                <th class="px-4 py-3">Timestamp</th>
                <th class="px-4 py-3">Actor</th>
                <th class="px-4 py-3">Event</th>
                <th class="px-4 py-3">Entity</th>
                <th class="px-4 py-3">IP</th>
                <th class="px-4 py-3">Changes</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($logs as $log)
                <tr>
                    <td class="px-4 py-3 text-xs text-slate-500">{{ $log->created_at?->toDateTimeString() }}</td>
                    <td class="px-4 py-3">{{ $log->user?->name ?? 'System' }}</td>
                    <td class="px-4 py-3 font-mono text-xs">{{ $log->event }}</td>
                    <td class="px-4 py-3 text-xs">{{ $log->auditable_type }} #{{ $log->auditable_id }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $log->ip_address }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-slate-600 max-w-xs truncate">{{ json_encode($log->new_values) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">No audit logs recorded yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $logs->links() }}
</x-layouts.app>
