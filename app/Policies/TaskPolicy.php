<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use App\Policies\Concerns\ResolvesTeamOwnership;

final class TaskPolicy
{
    use ResolvesTeamOwnership;

    public function viewAny(User $user): bool
    {
        return $user->can('tasks.view.own') || $user->can('tasks.view.team');
    }

    public function view(User $user, Task $task): bool
    {
        return $this->canViewScoped($user, $task, 'tasks', 'assigned_to_id');
    }

    public function create(User $user): bool
    {
        return $user->can('tasks.create');
    }

    public function update(User $user, Task $task): bool
    {
        if ($user->hasAnyRole(['Super Admin', 'Org Admin'])) {
            return true;
        }

        if ($user->hasRole('Sales Manager')) {
            return (int) $task->team_id === (int) $user->team_id;
        }

        return (int) $task->team_id === (int) $user->team_id
            && ((int) $task->assigned_to_id === (int) $user->id || (int) $task->created_by_id === (int) $user->id)
            && $user->can('tasks.update.own');
    }

    public function delete(User $user, Task $task): bool
    {
        return $this->canDeleteScoped($user, $task, 'tasks');
    }
}
