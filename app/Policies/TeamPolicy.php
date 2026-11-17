<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

final class TeamPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('teams.manage') || $user->can('users.view');
    }

    public function create(User $user): bool
    {
        return $user->can('teams.manage');
    }

    public function update(User $user, Team $team): bool
    {
        return $user->can('teams.manage');
    }
}
