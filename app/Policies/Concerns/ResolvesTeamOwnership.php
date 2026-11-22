<?php

declare(strict_types=1);

namespace App\Policies\Concerns;

use App\Enums\RoleId;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait ResolvesTeamOwnership
{
    protected function canViewScoped(User $user, Model $record, string $module, string $ownerColumn = 'owner_id'): bool
    {
        if ($user->hasAnyRole(RoleId::organizationReaders()) && $user->can("{$module}.view.all")) {
            return true;
        }

        if ($user->hasRole(RoleId::SalesManager->value)) {
            return (int) $record->getAttribute('team_id') === (int) $user->team_id
                && $user->can("{$module}.view.team");
        }

        return (int) $record->getAttribute('team_id') === (int) $user->team_id
            && (int) $record->getAttribute($ownerColumn) === (int) $user->id
            && $user->can("{$module}.view.own");
    }

    protected function canUpdateScoped(User $user, Model $record, string $module, string $ownerColumn = 'owner_id'): bool
    {
        if ($user->hasAnyRole(RoleId::organizationWriters())) {
            return true;
        }

        if ($user->hasRole(RoleId::SalesManager->value)) {
            return (int) $record->getAttribute('team_id') === (int) $user->team_id
                && $user->can("{$module}.update.team");
        }

        return (int) $record->getAttribute('team_id') === (int) $user->team_id
            && (int) $record->getAttribute($ownerColumn) === (int) $user->id
            && $user->can("{$module}.update.own");
    }

    protected function canDeleteScoped(User $user, Model $record, string $module): bool
    {
        if ($user->hasAnyRole(RoleId::organizationWriters())) {
            return true;
        }

        return $user->hasRole(RoleId::SalesManager->value)
            && (int) $record->getAttribute('team_id') === (int) $user->team_id
            && $user->can("{$module}.delete");
    }
}
