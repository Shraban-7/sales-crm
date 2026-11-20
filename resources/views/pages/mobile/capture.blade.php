<x-layouts.app title="Event Lead Capture — Sale Fusion CRM" header="Event & Trade Show Lead Capture">
    <div class="max-w-2xl space-y-6">
        <div class="crm-card p-6 space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Quick Lead &amp; Business Card Capture</h2>
                    <p class="text-xs text-slate-500">Capture leads on-site at trade shows. Works with camera card uploads and offline sync.</p>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span> PWA Ready
                </span>
            </div>

            <form method="POST" action="{{ route('mobile.capture.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Trade Show / Conference Event</label>
                    <div class="flex gap-2">
                        <select name="event_id" class="crm-select flex-1">
                            <option value="">Generic On-Site Lead</option>
                            @foreach ($events as $event)
                                <option value="{{ $event->id }}">{{ $event->name }} ({{ $event->location }})</option>
                            @endforeach
                        </select>
                        <a href="{{ route('mobile.events.index') }}" class="crm-btn-secondary text-xs">+ Events</a>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">First Name *</label>
                        <input type="text" name="first_name" required placeholder="First Name" class="crm-input">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Last Name</label>
                        <input type="text" name="last_name" placeholder="Last Name" class="crm-input">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Company Name</label>
                        <input type="text" name="company_name" placeholder="Company Name" class="crm-input">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Job Title / Designation</label>
                        <input type="text" name="job_title" placeholder="Job Title" class="crm-input">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address</label>
                        <input type="email" name="email" placeholder="contact@example.com" class="crm-input">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Phone Number</label>
                        <input type="text" name="phone" placeholder="+1234567890" class="crm-input">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Photograph Business Card (Camera / Upload)</label>
                    <input type="file" name="card_photo" accept="image/*" capture="environment" class="crm-input">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Urgency Priority</label>
                    <select name="priority" class="crm-select">
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent Follow-Up</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Conversation Notes</label>
                    <textarea name="notes" rows="3" placeholder="Key interest, pain points, requested demo date..." class="crm-input"></textarea>
                </div>

                <div class="pt-2">
                    <button type="submit" class="crm-btn-primary w-full">Save Event Lead</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
