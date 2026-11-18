<x-layouts.app title="Notifications — Sale Fusion CRM" header="Notifications & Preferences">
    <div class="flex justify-end">
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button type="submit" class="crm-btn-secondary py-1.5 text-xs">Mark all as read</button>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 crm-card divide-y divide-slate-100 overflow-hidden">
            @forelse ($notifications as $notification)
                <div class="p-4 flex items-start justify-between gap-4 transition {{ $notification->read_at ? 'opacity-65 bg-white' : 'bg-brand-50/30' }}">
                    <div class="flex items-start gap-3">
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $notification->read_at ? 'bg-slate-300' : 'bg-brand-600' }}"></span>
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $notification->data['title'] ?? 'Notification' }}</p>
                            <p class="text-xs text-slate-600 mt-0.5">{{ $notification->data['message'] ?? '' }}</p>
                            <div class="flex items-center gap-3 mt-1.5">
                                <span class="text-[11px] text-slate-400">{{ $notification->created_at?->diffForHumans() }}</span>
                                @if (! empty($notification->data['action_url']))
                                    <a href="{{ $notification->data['action_url'] }}" class="text-xs font-semibold text-brand-600 hover:underline">Open &rarr;</a>
                                @endif
                            </div>
                        </div>
                    </div>
                    @if (! $notification->read_at)
                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-xs font-semibold text-brand-600 hover:underline shrink-0">Mark read</button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="p-10 text-center">
                    <p class="text-sm font-medium text-slate-600">You're all caught up</p>
                    <p class="text-xs text-slate-400 mt-1">New assignment and reminder alerts will appear here.</p>
                </div>
            @endforelse
        </div>

        <form method="POST" action="{{ route('notifications.preferences') }}" class="crm-card p-6 space-y-4 h-fit">
            @csrf
            @method('PUT')
            <div>
                <h2 class="text-sm font-semibold text-slate-900">Channel Preferences</h2>
                <p class="text-xs text-slate-500 mt-0.5">Choose how alerts are delivered.</p>
            </div>
            @php
                $categories = [
                    'lead_assigned' => 'Lead Assigned',
                    'task_reminder' => 'Task & Follow-Up Reminders',
                    'task_assigned' => 'Task Assigned',
                ];
            @endphp
            @foreach ($categories as $key => $label)
                <div class="border-t border-slate-100 pt-3.5 space-y-2">
                    <p class="text-xs font-semibold text-slate-700">{{ $label }}</p>
                    <div class="flex items-center gap-5 text-xs text-slate-600">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="preferences[{{ $key }}][database]" value="0">
                            <input type="checkbox" name="preferences[{{ $key }}][database]" value="1" @checked($user->wantsNotificationChannel($key, 'database')) class="rounded border-slate-300 text-brand-600">
                            <span>In-App</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="preferences[{{ $key }}][mail]" value="0">
                            <input type="checkbox" name="preferences[{{ $key }}][mail]" value="1" @checked($user->wantsNotificationChannel($key, 'mail')) class="rounded border-slate-300 text-brand-600">
                            <span>Email</span>
                        </label>
                    </div>
                </div>
            @endforeach
            <button type="submit" class="crm-btn-dark w-full">Save Preferences</button>
        </form>
    </div>

    {{ $notifications->links() }}
</x-layouts.app>
