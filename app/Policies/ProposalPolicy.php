<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Proposal;
use App\Models\User;
use App\Policies\Concerns\ResolvesTeamOwnership;

final class ProposalPolicy
{
    use ResolvesTeamOwnership;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Proposal $proposal): bool
    {
        if ($user->hasAnyRole(['Super Admin', 'Org Admin', 'Read-Only Analyst', 'Viewer', 'Head of Sales'])) {
            return true;
        }

        if ($user->hasRole('Sales Manager')) {
            return (int) $proposal->team_id === (int) $user->team_id;
        }

        return (int) $proposal->team_id === (int) $user->team_id
            && (int) $proposal->owner_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return ! $user->hasAnyRole(['Read-Only Analyst', 'Viewer']);
    }

    public function update(User $user, Proposal $proposal): bool
    {
        if ($user->hasAnyRole(['Super Admin', 'Org Admin', 'Head of Sales'])) {
            return true;
        }

        if ($user->hasRole('Sales Manager')) {
            return (int) $proposal->team_id === (int) $user->team_id;
        }

        return (int) $proposal->team_id === (int) $user->team_id
            && (int) $proposal->owner_id === (int) $user->id;
    }

    public function delete(User $user, Proposal $proposal): bool
    {
        return $this->update($user, $proposal);
    }
}
