<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait HasVisibilityScope
{
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasAnyRole(['Super Admin', 'Org Admin', 'Read-Only Analyst'])) {
            return $query;
        }

        if ($user->hasRole('Sales Manager')) {
            return $query->where($this->qualifyColumn('team_id'), $user->team_id);
        }

        $ownerColumn = property_exists($this, 'visibilityOwnerColumn')
            ? $this->visibilityOwnerColumn
            : 'owner_id';

        return $query
            ->where($this->qualifyColumn('team_id'), $user->team_id)
            ->where($this->qualifyColumn($ownerColumn), $user->id);
    }
}
