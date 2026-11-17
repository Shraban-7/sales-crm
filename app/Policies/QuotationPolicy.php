<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Quotation;
use App\Models\User;
use App\Policies\Concerns\ResolvesTeamOwnership;

final class QuotationPolicy
{
    use ResolvesTeamOwnership;

    public function viewAny(User $user): bool
    {
        return $user->can('quotations.view.own') || $user->can('quotations.view.team') || $user->can('quotations.view.all');
    }

    public function view(User $user, Quotation $quotation): bool
    {
        return $this->canViewScoped($user, $quotation, 'quotations');
    }

    public function create(User $user): bool
    {
        return $user->can('quotations.create');
    }

    public function update(User $user, Quotation $quotation): bool
    {
        if (! $quotation->isDraft()) {
            return false;
        }

        if ($user->hasAnyRole(['Super Admin', 'Org Admin'])) {
            return true;
        }

        if ($user->hasRole('Sales Manager')) {
            return (int) $quotation->team_id === (int) $user->team_id;
        }

        return (int) $quotation->team_id === (int) $user->team_id
            && (int) $quotation->owner_id === (int) $user->id
            && $user->can('quotations.update.own');
    }

    public function send(User $user, Quotation $quotation): bool
    {
        if ($user->hasAnyRole(['Super Admin', 'Org Admin'])) {
            return true;
        }

        if ($user->hasRole('Sales Manager')) {
            return (int) $quotation->team_id === (int) $user->team_id && $user->can('quotations.send');
        }

        return (int) $quotation->team_id === (int) $user->team_id
            && (int) $quotation->owner_id === (int) $user->id
            && $user->can('quotations.send');
    }

    public function delete(User $user, Quotation $quotation): bool
    {
        if (! $quotation->isDraft()) {
            return false;
        }

        return $this->canDeleteScoped($user, $quotation, 'quotations');
    }
}
