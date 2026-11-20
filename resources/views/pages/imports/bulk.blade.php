<x-layouts.app title="Bulk Import — Sale Fusion CRM" header="Bulk Import from Excel / CSV">
    <div class="max-w-3xl space-y-6">
        <div class="crm-card p-6 space-y-4">
            <div>
                <h2 class="text-base font-semibold text-slate-900">Upload Spreadsheet (CSV or Excel)</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Bulk-add hundreds of Leads, Contacts, or Companies in seconds. Background processing handles duplicate detection automatically.
                </p>
            </div>

            <form method="POST" action="{{ route('imports.bulk.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Target CRM Module *</label>
                    <select name="module" required class="crm-select">
                        <option value="leads">Leads</option>
                        <option value="contacts">Contacts</option>
                        <option value="companies">Companies</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Duplicate Strategy *</label>
                    <select name="duplicate_strategy" required class="crm-select">
                        <option value="skip">Skip duplicates (keep existing records)</option>
                        <option value="update">Update existing records with new values</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Spreadsheet File (.csv) *</label>
                    <input type="file" name="file" accept=".csv,text/csv,text/plain" required class="crm-input">
                </div>

                <div class="pt-2">
                    <button type="submit" class="crm-btn-primary">Queue Bulk Import</button>
                </div>
            </form>
        </div>

        <!-- Format Specification Card -->
        <div class="crm-card p-6 space-y-3 bg-slate-50">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-700">Expected Column Headers</h3>
            <div class="space-y-2 text-xs text-slate-600">
                <p><strong>Leads:</strong> <code class="bg-white px-2 py-0.5 rounded border border-slate-200">first_name, last_name, company_name, email, phone, job_title, source, estimated_value</code></p>
                <p><strong>Contacts:</strong> <code class="bg-white px-2 py-0.5 rounded border border-slate-200">first_name, last_name, company_name, email, phone, job_title</code></p>
                <p><strong>Companies:</strong> <code class="bg-white px-2 py-0.5 rounded border border-slate-200">name, industry, website, phone, city, country</code></p>
            </div>
        </div>
    </div>
</x-layouts.app>
