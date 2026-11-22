<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\RoleId;
use App\Models\ImportExportJob;
use App\Models\User;

final class ImportExportJobPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('imports.create') || $user->can('exports.create.own') || $user->can('exports.create.all');
    }

    public function view(User $user, ImportExportJob $job): bool
    {
        if ($user->hasAnyRole(RoleId::organizationWriters())) {
            return true;
        }

        return (int) $job->team_id === (int) $user->team_id && (int) $job->user_id === (int) $user->id;
    }

    public function createImport(User $user): bool
    {
        return $user->can('imports.create');
    }

    public function createExport(User $user): bool
    {
        return $user->can('exports.create.own') || $user->can('exports.create.team') || $user->can('exports.create.all');
    }
}
