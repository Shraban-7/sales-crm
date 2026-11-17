<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\ActivityType;
use App\Models\Activity;
use App\Models\User;
use App\Policies\Concerns\ResolvesTeamOwnership;

final class ActivityPolicy
{
    use ResolvesTeamOwnership;

    public function viewAny(User $user): bool
    {
        return $user->can('activities.view.own') || $user->can('activities.view.team') || $user->can('activities.view.all');
    }

    public function view(User $user, Activity $activity): bool
    {
        return $this->canViewScoped($user, $activity, 'activities', 'user_id');
    }

    public function create(User $user): bool
    {
        return $user->can('activities.create');
    }

    public function update(User $user, Activity $activity): bool
    {
        if ($activity->type === ActivityType::System) {
            return false;
        }

        return $this->canUpdateScoped($user, $activity, 'activities', 'user_id');
    }

    public function delete(User $user, Activity $activity): bool
    {
        if ($activity->type === ActivityType::System) {
            return false;
        }

        return $this->canDeleteScoped($user, $activity, 'activities');
    }
}
