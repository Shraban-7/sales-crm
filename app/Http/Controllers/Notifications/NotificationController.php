<?php

declare(strict_types=1);

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notifications\UpdateNotificationPreferencesRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

final class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $notifications = $user->notifications()->latest()->paginate(20);

        return view('pages.notifications.index', compact('notifications', 'user'));
    }

    public function markRead(Request $request, DatabaseNotification $notification): JsonResponse|RedirectResponse
    {
        if (
            (int) $notification->notifiable_id !== (int) $request->user()->id
            || $notification->notifiable_type !== $request->user()->getMorphClass()
        ) {
            abort(403);
        }

        $notification->markAsRead();

        if ($request->expectsJson()) {
            return response()->json(['status' => 'read']);
        }

        return back()->with('status', 'Notification marked as read.');
    }

    public function markAllRead(Request $request): JsonResponse|RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'all_read']);
        }

        return back()->with('status', 'All notifications marked as read.');
    }

    public function updatePreferences(UpdateNotificationPreferencesRequest $request): RedirectResponse
    {
        $request->user()->update([
            'notification_preferences' => $request->validated('preferences'),
        ]);

        return back()->with('status', 'Notification preferences saved.');
    }
}
