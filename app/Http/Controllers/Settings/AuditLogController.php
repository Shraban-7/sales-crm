<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', AuditLog::class);

        $user = $request->user();
        $event = $request->query('event');
        $userId = $request->query('user_id');

        $logs = AuditLog::query()
            ->with('user')
            ->when(
                ! $user->can('audit_logs.view.all'),
                fn ($q) => $q->where('team_id', $user->team_id)
            )
            ->when(is_string($event) && $event !== '', fn ($q) => $q->where('event', $event))
            ->when(is_string($userId) && $userId !== '', fn ($q) => $q->where('user_id', (int) $userId))
            ->latest('created_at')
            ->paginate(25)
            ->withQueryString();

        $users = User::query()->orderBy('name')->get(['id', 'name']);

        return view('pages.settings.audit-logs.index', compact('logs', 'users', 'event', 'userId'));
    }
}
