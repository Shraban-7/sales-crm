<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\Users\ReassignUserRecordsAction;
use App\Exceptions\DomainRuleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Models\Team;
use App\Models\User;
use App\Services\Audit\AuditLoggerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

final class UserController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $actor = $request->user();
        $users = User::query()
            ->with(['team', 'roles'])
            ->when(! $actor->can('users.manage'), fn ($q) => $q->where('team_id', $actor->team_id))
            ->orderBy('name')
            ->paginate(20);

        $teams = Team::query()->orderBy('name')->get();

        return view('pages.settings.users.index', compact('users', 'teams'));
    }

    public function store(StoreUserRequest $request, AuditLoggerService $auditLogger): RedirectResponse
    {
        $this->authorize('create', User::class);

        $data = $request->validated();
        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'job_title' => $data['job_title'] ?? null,
            'team_id' => $data['team_id'],
            'password' => Hash::make($data['password']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $user->syncRoles([$data['role']]);

        $auditLogger->log(
            event: 'created',
            auditable: $user,
            actor: $request->user(),
            newValues: $user->only(['name', 'email', 'team_id', 'is_active']) + ['role' => $data['role']],
            teamId: $user->team_id,
        );

        return back()->with('status', "User {$user->name} created.");
    }

    public function update(UpdateUserRequest $request, User $user, AuditLoggerService $auditLogger): RedirectResponse
    {
        $this->authorize('update', $user);

        $data = $request->validated();
        $isActive = $request->boolean('is_active', true);

        if ($user->id === $request->user()->id && ! $isActive) {
            throw new DomainRuleException('You cannot deactivate your own account.', 422);
        }

        $oldValues = $user->only(['name', 'email', 'team_id', 'is_active']) + ['role' => $user->getRoleNames()->first()];

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'job_title' => $data['job_title'] ?? null,
            'team_id' => $data['team_id'],
            'is_active' => $isActive,
        ]);

        $user->syncRoles([$data['role']]);

        $auditLogger->log(
            event: 'updated',
            auditable: $user,
            actor: $request->user(),
            oldValues: $oldValues,
            newValues: $user->only(['name', 'email', 'team_id', 'is_active']) + ['role' => $data['role']],
            teamId: $user->team_id,
        );

        return back()->with('status', "User {$user->name} updated.");
    }

    public function reassign(
        Request $request,
        User $user,
        ReassignUserRecordsAction $reassignAction,
    ): RedirectResponse {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'to_user_id' => ['required', 'integer', 'exists:users,id', 'different:' . $user->id],
        ]);

        $targetUser = User::query()->findOrFail((int) $validated['to_user_id']);
        $reassignAction->handle($user, $targetUser, $request->user());

        return back()->with('status', "Open records reassigned from {$user->name} to {$targetUser->name}.");
    }
}
