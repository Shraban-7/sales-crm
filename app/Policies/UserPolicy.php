<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

final class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.view') || $user->can('users.manage');
    }

    public function view(User $user, User $model): bool
    {
        if ($user->can('users.manage')) {
            return true;
        }

        return $user->can('users.view') && (int) $user->team_id === (int) $model->team_id;
    }

    public function create(User $user): bool
    {
        return $user->can('users.manage');
    }

    public function update(User $user, User $model): bool
    {
        if (! $user->can('users.manage')) {
            return false;
        }

        if ($model->hasRole('Super Admin') && ! $user->hasRole('Super Admin')) {
            return false;
        }

        return true;
    }
}
