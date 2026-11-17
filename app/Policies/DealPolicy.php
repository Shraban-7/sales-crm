<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Deal;
use App\Models\User;
use App\Policies\Concerns\ResolvesTeamOwnership;

final class DealPolicy
{
    use ResolvesTeamOwnership;

    public function viewAny(User $user): bool
    {
        return $user->can('deals.view.own') || $user->can('deals.view.team') || $user->can('deals.view.all');
    }

    public function view(User $user, Deal $deal): bool
    {
        return $this->canViewScoped($user, $deal, 'deals');
    }

    public function create(User $user): bool
    {
        return $user->can('deals.create');
    }

    public function update(User $user, Deal $deal): bool
    {
        return $this->canUpdateScoped($user, $deal, 'deals');
    }

    public function moveStage(User $user, Deal $deal): bool
    {
        if ($user->hasAnyRole(['Super Admin', 'Org Admin'])) {
            return true;
        }

        if ($user->hasRole('Sales Manager')) {
            return (int) $deal->team_id === (int) $user->team_id && $user->can('deals.update.team');
        }

        return (int) $deal->team_id === (int) $user->team_id
            && (int) $deal->owner_id === (int) $user->id
            && $user->can('deals.move_stage.own');
    }

    public function reassign(User $user, Deal $deal): bool
    {
        if ($user->hasAnyRole(['Super Admin', 'Org Admin'])) {
            return true;
        }

        return $user->hasRole('Sales Manager')
            && (int) $deal->team_id === (int) $user->team_id
            && $user->can('deals.reassign');
    }

    public function delete(User $user, Deal $deal): bool
    {
        return $this->canDeleteScoped($user, $deal, 'deals');
    }
}
