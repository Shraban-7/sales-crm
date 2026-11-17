@php
    $unreadCount = auth()->user()?->unreadNotifications()->count() ?? 0;
@endphp

<a href="{{ route('notifications.index') }}" class="relative inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50">
    <span>Alerts</span>
    @if ($unreadCount > 0)
        <span class="inline-flex items-center justify-center rounded-full bg-rose-600 px-1.5 py-0.5 text-[11px] font-semibold text-white">{{ $unreadCount }}</span>
    @endif
</a>
