<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\FollowUp;
use App\Models\User;
use App\Policies\Concerns\ResolvesTeamOwnership;

final class FollowUpPolicy
{
    use ResolvesTeamOwnership;

    public function viewAny(User $user): bool
    {
        return $user->can('follow_ups.view.own') || $user->can('follow_ups.view.team');
    }

    public function view(User $user, FollowUp $followUp): bool
    {
        return $this->canViewScoped($user, $followUp, 'follow_ups');
    }

    public function create(User $user): bool
    {
        return $user->can('follow_ups.create');
    }

    public function update(User $user, FollowUp $followUp): bool
    {
        if ($user->hasAnyRole(['Super Admin', 'Org Admin'])) {
            return true;
        }

        if ($user->hasRole('Sales Manager')) {
            return (int) $followUp->team_id === (int) $user->team_id;
        }

        return (int) $followUp->team_id === (int) $user->team_id
            && (int) $followUp->owner_id === (int) $user->id
            && $user->can('follow_ups.update.own');
    }

    public function delete(User $user, FollowUp $followUp): bool
    {
        return $this->canDeleteScoped($user, $followUp, 'follow_ups');
    }
}
