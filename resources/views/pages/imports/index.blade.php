<x-layouts.app title="Import / Export — Sales CRM" header="Data Import & Export Center">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @can('createImport', \App\Models\ImportExportJob::class)
            <form method="POST" action="{{ route('imports.store') }}" enctype="multipart/form-data" class="bg-white rounded-xl border border-slate-200 p-5 space-y-3">
                @csrf
                <h3 class="text-sm font-semibold text-slate-900">Bulk CSV Import</h3>
                <div class="grid grid-cols-2 gap-3">
                    <select name="module" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="leads">Leads</option>
                        <option value="contacts">Contacts</option>
                        <option value="companies">Companies</option>
                    </select>
                    <select name="duplicate_strategy" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="skip">Skip Duplicates</option>
                        <option value="update">Update Duplicates</option>
                    </select>
                </div>
                <input type="file" name="file" accept=".csv" required class="w-full text-sm">
                <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Upload &amp; Import</button>
            </form>
        @endcan

        @can('createExport', \App\Models\ImportExportJob::class)
            <form method="POST" action="{{ route('exports.store') }}" class="bg-white rounded-xl border border-slate-200 p-5 space-y-3">
                @csrf
                <h3 class="text-sm font-semibold text-slate-900">Scoped CSV Export</h3>
                <select name="module" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="leads">Leads</option>
                    <option value="contacts">Contacts</option>
                    <option value="companies">Companies</option>
                    <option value="deals">Deals</option>
                </select>
                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Generate Export</button>
            </form>
        @endcan
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
            <tr>
                <th class="px-4 py-3">Type</th>
                <th class="px-4 py-3">Module</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Rows</th>
                <th class="px-4 py-3">User</th>
                <th class="px-4 py-3">File</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($jobs as $job)
                <tr>
                    <td class="px-4 py-3 uppercase text-xs font-semibold">{{ $job->type->value }}</td>
                    <td class="px-4 py-3">{{ $job->module->value }}</td>
                    <td class="px-4 py-3"><x-crm.status-badge :status="$job->status" /></td>
                    <td class="px-4 py-3 text-xs">{{ $job->processed_rows }}/{{ $job->total_rows }} ({{ $job->failed_rows }} failed)</td>
                    <td class="px-4 py-3 text-slate-600">{{ $job->user?->name }}</td>
                    <td class="px-4 py-3">
                        @if ($job->result_file_path)
                            <a href="{{ route('imports.download', $job) }}" class="text-brand-600 hover:underline text-xs font-medium">Download</a>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">No import/export jobs yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>
