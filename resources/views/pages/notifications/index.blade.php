<x-layouts.app title="Notifications — Sales CRM" header="Notifications &amp; Preferences">
    <div class="flex justify-end">
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button type="submit" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium">Mark all as read</button>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 divide-y divide-slate-100">
        @forelse ($notifications as $notification)
            <div class="p-4 flex items-center justify-between {{ $notification->read_at ? 'opacity-60' : '' }}">
                <div>
                    <p class="text-sm font-semibold text-slate-900">{{ $notification->data['title'] ?? 'Alert' }}</p>
                    <p class="text-xs text-slate-600">{{ $notification->data['message'] ?? '' }}</p>
                </div>
                @if (! $notification->read_at)
                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="text-xs font-medium text-brand-600 hover:underline">Mark read</button>
                    </form>
                @endif
            </div>
        @empty
            <p class="p-6 text-center text-sm text-slate-500">No notifications.</p>
        @endforelse
    </div>

    {{ $notifications->links() }}
</x-layouts.app>
