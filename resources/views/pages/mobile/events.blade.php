<x-layouts.app title="Event Management — Sale Fusion CRM" header="Event Management">
    <div class="space-y-6 max-w-4xl">
        <!-- Create Event Form -->
        <form method="POST" action="{{ route('mobile.events.store') }}" class="crm-card p-6 space-y-4">
            @csrf
            <h2 class="text-sm font-semibold text-slate-900">Create New Event / Trade Show</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Event Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Gitex Global 2026" class="crm-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Location / Venue</label>
                    <input type="text" name="location" placeholder="e.g. Dubai World Trade Centre" class="crm-input">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Start Date</label>
                    <input type="date" name="start_date" class="crm-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">End Date</label>
                    <input type="date" name="end_date" class="crm-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Budget ($)</label>
                    <input type="number" step="0.01" name="budget" placeholder="0.00" class="crm-input">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Event Description &amp; Objectives</label>
                <textarea name="description" rows="2" placeholder="Booth number, goals, lead target..." class="crm-input"></textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="crm-btn-primary">Create Event</button>
            </div>
        </form>

        <!-- Events List -->
        <div class="crm-card divide-y divide-slate-100 overflow-hidden">
            @forelse ($events as $event)
                <div class="p-4 flex items-center justify-between gap-4 hover:bg-slate-50">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">{{ $event->name }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            📍 {{ $event->location ?? 'No location' }} &middot;
                            📅 {{ $event->start_date?->format('M d, Y') ?? 'TBD' }} &middot;
                            Leads Captured: <strong class="text-slate-800">{{ $event->leads_count }}</strong>
                        </p>
                    </div>
                    <a href="{{ route('mobile.capture', ['event_id' => $event->id]) }}" class="crm-btn-secondary text-xs">
                        Capture Leads
                    </a>
                </div>
            @empty
                <div class="p-8 text-center text-xs text-slate-400">No events created yet.</div>
            @endforelse
        </div>
    </div>
</x-layouts.app>
