<x-layouts.app title="Create Proposal — Sale Fusion CRM" header="Create Proposal">
    <div class="max-w-3xl">
        <form method="POST" action="{{ route('proposals.store') }}" class="crm-card p-6 space-y-4">
            @csrf
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Number: {{ $proposalNumber }}</span>
                <span class="text-xs text-slate-400">Status: Draft</span>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Proposal Title *</label>
                <input type="text" name="title" required placeholder="e.g. Enterprise Cloud ERP Implementation Proposal" class="crm-input">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Linked Deal (Optional)</label>
                    <select name="deal_id" class="crm-select">
                        <option value="">None</option>
                        @foreach ($deals as $deal)
                            <option value="{{ $deal->id }}">{{ $deal->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Company</label>
                    <select name="company_id" class="crm-select">
                        <option value="">None</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Proposal Amount *</label>
                    <input type="number" step="0.01" name="amount" required placeholder="0.00" class="crm-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Currency *</label>
                    <input type="text" name="currency" value="USD" required maxlength="3" class="crm-input uppercase">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Valid Until</label>
                    <input type="date" name="valid_until" value="{{ now()->addDays(30)->format('Y-m-d') }}" class="crm-input">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Scope, Terms &amp; Notes</label>
                <textarea name="notes" rows="4" placeholder="Detail deliverables, milestones, and payment schedule..." class="crm-input"></textarea>
            </div>

            <div class="pt-3 flex items-center justify-end gap-3">
                <a href="{{ route('proposals.index') }}" class="crm-btn-secondary">Cancel</a>
                <button type="submit" class="crm-btn-primary">Create Proposal</button>
            </div>
        </form>
    </div>
</x-layouts.app>
