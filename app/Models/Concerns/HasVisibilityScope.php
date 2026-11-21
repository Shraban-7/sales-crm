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
            if ($user->team_id === null) {
                return $query;
            }

            return $query->where(function (Builder $sub) use ($user): void {
                $sub->where($this->qualifyColumn('team_id'), $user->team_id)
                    ->orWhereNull($this->qualifyColumn('team_id'));
            });
        }

        $ownerColumn = property_exists($this, 'visibilityOwnerColumn')
            ? $this->visibilityOwnerColumn
            : 'owner_id';

        if ($user->team_id === null) {
            return $query
                ->where($this->qualifyColumn($ownerColumn), $user->id);
        }

        return $query
            ->where(function (Builder $sub) use ($user): void {
                $sub->where($this->qualifyColumn('team_id'), $user->team_id)
                    ->orWhereNull($this->qualifyColumn('team_id'));
            })
            ->where($this->qualifyColumn($ownerColumn), $user->id);
    }
}
