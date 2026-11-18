<x-layouts.app title="Import / Export — Sale Fusion CRM" header="Data Import & Export Center">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @can('createImport', \App\Models\ImportExportJob::class)
            <form method="POST" action="{{ route('imports.store') }}" enctype="multipart/form-data" class="crm-card p-6 space-y-4">
                @csrf
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Bulk CSV Import</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Upload records with configurable duplicate resolution.</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <select name="module" required class="crm-select">
                        <option value="leads">Leads</option>
                        <option value="contacts">Contacts</option>
                        <option value="companies">Companies</option>
                    </select>
                    <select name="duplicate_strategy" required class="crm-select">
                        <option value="skip">Skip Duplicates</option>
                        <option value="update">Update Duplicates</option>
                    </select>
                </div>
                <input type="file" name="file" accept=".csv" required class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
                <button type="submit" class="crm-btn-primary">Upload &amp; Import</button>
            </form>
        @endcan

        @can('createExport', \App\Models\ImportExportJob::class)
            <form method="POST" action="{{ route('exports.store') }}" class="crm-card p-6 space-y-4">
                @csrf
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Scoped CSV Export</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Export authorized records visible to your role and team.</p>
                </div>
                <select name="module" required class="crm-select">
                    <option value="leads">Leads</option>
                    <option value="contacts">Contacts</option>
                    <option value="companies">Companies</option>
                    <option value="deals">Deals</option>
                </select>
                <button type="submit" class="crm-btn-dark">Generate Export</button>
            </form>
        @endcan
    </div>

    <div class="crm-table-wrap">
        <table class="crm-table">
            <thead class="crm-thead">
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
                <tr class="hover:bg-slate-50/80 transition">
                    <td class="px-4 py-3 uppercase text-xs font-semibold text-slate-700">{{ $job->type->value }}</td>
                    <td class="px-4 py-3 font-medium text-slate-900">{{ ucfirst($job->module->value) }}</td>
                    <td class="px-4 py-3"><x-crm.status-badge :status="$job->status" /></td>
                    <td class="px-4 py-3 text-xs text-slate-600 tabular-nums">{{ $job->processed_rows }}/{{ $job->total_rows }} ({{ $job->failed_rows }} failed)</td>
                    <td class="px-4 py-3 text-slate-600">{{ $job->user?->name }}</td>
                    <td class="px-4 py-3">
                        @if ($job->result_file_path)
                            <a href="{{ route('imports.download', $job) }}" class="text-brand-600 hover:underline text-xs font-semibold">Download CSV</a>
                        @else
                            <span class="text-slate-400">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">No import/export jobs yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>
