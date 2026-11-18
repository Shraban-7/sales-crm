<x-layouts.app title="Notifications — Sales CRM" header="Notifications & Preferences">
    <div class="flex justify-end">
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button type="submit" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium">Mark all as read</button>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 divide-y divide-slate-100">
            @forelse ($notifications as $notification)
                <div class="p-4 flex items-center justify-between gap-4 {{ $notification->read_at ? 'opacity-60' : '' }}">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ $notification->data['title'] ?? 'Alert' }}</p>
                        <p class="text-xs text-slate-600">{{ $notification->data['message'] ?? '' }}</p>
                        @if (! empty($notification->data['action_url']))
                            <a href="{{ $notification->data['action_url'] }}" class="text-xs text-brand-600 hover:underline mt-1 inline-block">Open</a>
                        @endif
                    </div>
                    @if (! $notification->read_at)
                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-xs font-medium text-brand-600 hover:underline shrink-0">Mark read</button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="p-6 text-center text-sm text-slate-500">No notifications.</p>
            @endforelse
        </div>

        <form method="POST" action="{{ route('notifications.preferences') }}" class="bg-white rounded-xl border border-slate-200 p-5 space-y-4 h-fit">
            @csrf
            @method('PUT')
            <h2 class="text-sm font-semibold text-slate-900">Channel Preferences</h2>
            @php
                $categories = [
                    'lead_assigned' => 'Lead Assigned',
                    'task_reminder' => 'Task & Follow-Up Reminders',
                    'task_assigned' => 'Task Assigned',
                ];
            @endphp
            @foreach ($categories as $key => $label)
                <div class="border-t border-slate-100 pt-3 space-y-2">
                    <p class="text-xs font-semibold text-slate-700">{{ $label }}</p>
                    <div class="flex items-center gap-4 text-xs text-slate-600">
                        <label class="flex items-center gap-1.5">
                            <input type="hidden" name="preferences[{{ $key }}][database]" value="0">
                            <input type="checkbox" name="preferences[{{ $key }}][database]" value="1" @checked($user->wantsNotificationChannel($key, 'database'))>
                            <span>In-App</span>
                        </label>
                        <label class="flex items-center gap-1.5">
                            <input type="hidden" name="preferences[{{ $key }}][mail]" value="0">
                            <input type="checkbox" name="preferences[{{ $key }}][mail]" value="1" @checked($user->wantsNotificationChannel($key, 'mail'))>
                            <span>Email</span>
                        </label>
                    </div>
                </div>
            @endforeach
            <button type="submit" class="w-full rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white">Save Preferences</button>
        </form>
    </div>

    {{ $notifications->links() }}
</x-layouts.app>
