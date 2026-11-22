<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\LeadStatus;
use App\Enums\RoleId;
use App\Models\Lead;
use App\Models\User;
use App\Policies\Concerns\ResolvesTeamOwnership;

final class LeadPolicy
{
    use ResolvesTeamOwnership;

    public function viewAny(User $user): bool
    {
        return $user->can('leads.view.own') || $user->can('leads.view.team') || $user->can('leads.view.all');
    }

    public function view(User $user, Lead $lead): bool
    {
        return $this->canViewScoped($user, $lead, 'leads');
    }

    public function create(User $user): bool
    {
        return $user->can('leads.create');
    }

    public function update(User $user, Lead $lead): bool
    {
        if ($lead->isConverted()) {
            return false;
        }

        return $this->canUpdateScoped($user, $lead, 'leads');
    }

    public function convert(User $user, Lead $lead): bool
    {
        if ($lead->isConverted() || $lead->status === LeadStatus::Disqualified) {
            return false;
        }

        if ($user->hasAnyRole(RoleId::organizationWriters())) {
            return true;
        }

        if ($user->hasRole(RoleId::SalesManager->value)) {
            return (int) $lead->team_id === (int) $user->team_id && $user->can('leads.convert');
        }

        return (int) $lead->team_id === (int) $user->team_id
            && (int) $lead->owner_id === (int) $user->id
            && $user->can('leads.convert');
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $this->canDeleteScoped($user, $lead, 'leads');
    }
}
