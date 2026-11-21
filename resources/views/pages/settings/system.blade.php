<x-layouts.app title="System Settings — Sale Fusion CRM" header="System & Site Settings">
    <div class="max-w-4xl space-y-6">
        @if (config('crm.demo_mode') && auth()->user()?->isDemoUser() && ! auth()->user()?->isOwner())
            <div class="rounded-2xl border border-amber-200 bg-amber-50/90 p-4 shadow-2xs flex items-start gap-3">
                <svg class="h-5 w-5 text-amber-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
                <div class="space-y-1">
                    <p class="text-sm font-semibold text-amber-900">Live Demo Mode &mdash; Read &amp; Explore Mode</p>
                    <p class="text-xs text-amber-800 leading-relaxed">
                        You can inspect all system options, defaults, and field configurations. Saving changes is disabled for demo accounts to preserve this demonstration for other evaluators. Only the verified site owner has write permissions.
                    </p>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('settings.system.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- General & Branding Card -->
            <div class="crm-card p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">General &amp; Branding Settings</h2>
                        <p class="text-xs text-slate-500">Configure your organization identity, logo, and public communication contact information.</p>
                    </div>
                    <span class="inline-flex items-center rounded bg-brand-50 px-2 py-1 text-xs font-semibold text-brand-700">
                        General
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Organization / Site Name *</label>
                        <input type="text" name="company_name" value="{{ old('company_name', $allSettings['company_name']) }}" required class="crm-input">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tagline / Slogan</label>
                        <input type="text" name="company_tagline" value="{{ old('company_tagline', $allSettings['company_tagline']) }}" class="crm-input">
                    </div>
                </div>

                <!-- Logo Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Company Logo</label>
                        <input type="file" name="company_logo" accept="image/png,image/jpeg,image/svg+xml,image/webp" class="crm-input text-xs">
                        <p class="text-[11px] text-slate-400 mt-1">PNG, JPG, SVG, WebP up to 5MB</p>
                    </div>
                    <div class="md:col-span-2 flex items-center gap-4">
                        @if (! empty($allSettings['company_logo_url']))
                            <div class="h-14 w-28 bg-white border border-slate-200 rounded-lg p-2 flex items-center justify-center shadow-xs">
                                <img src="{{ $allSettings['company_logo_url'] }}" alt="Current Logo" class="max-h-full max-w-full object-contain">
                            </div>
                            <span class="text-xs text-slate-500">Current brand logo displayed across invoices and client links.</span>
                        @else
                            <div class="h-14 w-28 bg-slate-100 border border-dashed border-slate-300 rounded-lg flex items-center justify-center text-xs text-slate-400">
                                No Logo
                            </div>
                            <span class="text-xs text-slate-500">Upload a logo to appear on printable quotes, proposals, and customer portals.</span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Support Email *</label>
                        <input type="email" name="company_email" value="{{ old('company_email', $allSettings['company_email']) }}" required class="crm-input">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Main Phone Number</label>
                        <input type="text" name="company_phone" value="{{ old('company_phone', $allSettings['company_phone']) }}" class="crm-input">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tax ID / VAT Registration #</label>
                        <input type="text" name="company_tax_id" value="{{ old('company_tax_id', $allSettings['company_tax_id']) }}" class="crm-input">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Headquarters Physical Address</label>
                    <textarea name="company_address" rows="2" class="crm-input">{{ old('company_address', $allSettings['company_address']) }}</textarea>
                </div>

                <div class="pt-2">
                    <label class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100 cursor-pointer">
                        <input type="checkbox" name="demo_mode" value="1" @checked(old('demo_mode', $allSettings['demo_mode'] ?? false)) class="h-4 w-4 rounded text-brand-600 focus:ring-brand-500">
                        <div>
                            <span class="text-xs font-semibold text-slate-800">Interactive Demo Mode &amp; Demo Credentials Showcase</span>
                            <p class="text-[11px] text-slate-400">When enabled, the root URL (/) displays the marketing landing page, and /login displays demo credentials and quick-fill buttons. When disabled, the login page is clean and root redirects directly to sign in.</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Localization Card -->
            <div class="crm-card p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Localization &amp; Currency Defaults</h2>
                        <p class="text-xs text-slate-500">Set regional parameters, currency formats, and timezone defaults.</p>
                    </div>
                    <span class="inline-flex items-center rounded bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700">
                        Localization
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Default Currency Code *</label>
                        <select name="default_currency" class="crm-select">
                            @foreach (['USD' => 'USD ($)', 'BDT' => 'BDT (৳)', 'EUR' => 'EUR (€)', 'GBP' => 'GBP (£)', 'CAD' => 'CAD ($)', 'AUD' => 'AUD ($)', 'SGD' => 'SGD ($)', 'INR' => 'INR (₹)', 'JPY' => 'JPY (¥)'] as $code => $label)
                                <option value="{{ $code }}" @selected(old('default_currency', $allSettings['default_currency']) === $code)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Currency Symbol Position</label>
                        <select name="currency_position" class="crm-select">
                            <option value="before" @selected(old('currency_position', $allSettings['currency_position']) === 'before')>Before Amount ($100.00)</option>
                            <option value="after" @selected(old('currency_position', $allSettings['currency_position']) === 'after')>After Amount (100.00 $)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Default Country Code (ISO-2) *</label>
                        <input type="text" name="default_country" value="{{ old('default_country', $allSettings['default_country']) }}" required maxlength="2" class="crm-input uppercase">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">System Timezone *</label>
                        <input type="text" name="timezone" value="{{ old('timezone', $allSettings['timezone']) }}" required placeholder="UTC, America/New_York, Asia/Dhaka..." class="crm-input">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Date Display Format *</label>
                        <select name="date_format" class="crm-select">
                            <option value="Y-m-d" @selected(old('date_format', $allSettings['date_format']) === 'Y-m-d')>YYYY-MM-DD ({{ now()->format('Y-m-d') }})</option>
                            <option value="d/m/Y" @selected(old('date_format', $allSettings['date_format']) === 'd/m/Y')>DD/MM/YYYY ({{ now()->format('d/m/Y') }})</option>
                            <option value="m/d/Y" @selected(old('date_format', $allSettings['date_format']) === 'm/d/Y')>MM/DD/YYYY ({{ now()->format('m/d/Y') }})</option>
                            <option value="M d, Y" @selected(old('date_format', $allSettings['date_format']) === 'M d, Y')>Mon DD, YYYY ({{ now()->format('M d, Y') }})</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Sales & Workflow Policies Card -->
            <div class="crm-card p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Sales, Quotations &amp; Pipeline Policies</h2>
                        <p class="text-xs text-slate-500">Configure business rules for discount thresholds, rotting deals, and auto-numbering prefixes.</p>
                    </div>
                    <span class="inline-flex items-center rounded bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700">
                        Sales Policies
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Quotation Prefix *</label>
                        <input type="text" name="quotation_number_prefix" value="{{ old('quotation_number_prefix', $allSettings['quotation_number_prefix']) }}" required maxlength="10" class="crm-input uppercase">
                        <p class="text-[11px] text-slate-400 mt-0.5">Example: QT-2026-00001</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Proposal Prefix *</label>
                        <input type="text" name="proposal_number_prefix" value="{{ old('proposal_number_prefix', $allSettings['proposal_number_prefix']) }}" required maxlength="10" class="crm-input uppercase">
                        <p class="text-[11px] text-slate-400 mt-0.5">Example: PR-2026-00001</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Discount Approval Threshold (%) *</label>
                        <input type="number" step="0.01" min="0" max="100" name="discount_approval_threshold" value="{{ old('discount_approval_threshold', $allSettings['discount_approval_threshold']) }}" required class="crm-input">
                        <p class="text-[11px] text-slate-400 mt-0.5">Quotes above this % require manager approval</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Quote Validity (Days) *</label>
                        <input type="number" min="1" max="365" name="quote_public_link_days" value="{{ old('quote_public_link_days', $allSettings['quote_public_link_days']) }}" required class="crm-input">
                        <p class="text-[11px] text-slate-400 mt-0.5">Days before online customer link expires</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Deal Rotting Alert (Days) *</label>
                        <input type="number" min="1" max="180" name="deal_rotting_days_default" value="{{ old('deal_rotting_days_default', $allSettings['deal_rotting_days_default']) }}" required class="crm-input">
                        <p class="text-[11px] text-slate-400 mt-0.5">Flags stalled deals in Kanban and Urgent Attention</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center pt-2">
                    <label class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100 cursor-pointer">
                        <input type="checkbox" name="lead_round_robin" value="1" @checked(old('lead_round_robin', $allSettings['lead_round_robin'])) class="h-4 w-4 rounded text-brand-600 focus:ring-brand-500">
                        <div>
                            <span class="text-xs font-semibold text-slate-800">Round-Robin Lead Assignment</span>
                            <p class="text-[11px] text-slate-400">Automatically rotates incoming website &amp; webhook leads among active team reps.</p>
                        </div>
                    </label>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Records Displayed Per Page *</label>
                        <select name="records_per_page" class="crm-select">
                            @foreach ([10, 15, 20, 25, 50, 100] as $count)
                                <option value="{{ $count }}" @selected((int) old('records_per_page', $allSettings['records_per_page']) === $count)>{{ $count }} rows per page</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex items-center justify-end gap-3 pt-2">
                @if (config('crm.demo_mode') && auth()->user()?->isDemoUser() && ! auth()->user()?->isOwner())
                    <span class="inline-flex items-center gap-1.5 text-xs text-amber-700 bg-amber-50 border border-amber-200 px-3 py-1.5 rounded-lg">
                        <svg class="h-3.5 w-3.5 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                        Protected in Demo Mode (Only verified site owner can save changes)
                    </span>
                @endif
                <button type="submit" class="crm-btn-primary px-6 py-2.5">
                    Save System Settings
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
