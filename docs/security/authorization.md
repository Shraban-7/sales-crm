# Authorization, RBAC & Data Isolation (IDOR Prevention) — Sales CRM

**Last Updated:** 2026-11-19  
**Implementation Status:** `Implemented (Active Engineering Specification)`  
**Related Docs:** `docs/security/role-permission-matrix.md`, `docs/adr/ADR-003-authentication-and-authorization.md`

## 1. Dual-Layer Authorization Model

To guarantee that users can neither execute unauthorized actions nor access records belonging to other sales reps/teams (Insecure Direct Object Reference — IDOR), **Sales CRM** enforces a two-layer authorization model:

1. **Capability Layer ([Spatie Laravel Permission](https://spatie.be/docs/laravel-permission/v8/installation-laravel)):** Checks whether the user's assigned Role grants the permission slug (e.g., `deals.update.own` vs `deals.update.team`).
2. **Record Ownership & Team Layer (Laravel Policies + Eloquent Query Scopes):** Verifies whether the specific model instance's `owner_id` or `team_id` matches the authenticated user.

## 2. Record Visibility Query Scope (`HasVisibilityScope` Trait)

Every primary CRM model (`Lead`, `Contact`, `Company`, `Deal`, `Quotation`, `Activity`, `Task`, `FollowUp`) must implement `scopeVisibleTo(Builder $query, User $user): Builder`:

```php
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

        // Default: Sales Rep sees only records they own within their team
        return $query
            ->where($this->qualifyColumn('team_id'), $user->team_id)
            ->where($this->qualifyColumn('owner_id'), $user->id);
    }
}
```

## 3. Policy Implementation Pattern (`app/Policies/DealPolicy.php`)

```php
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Deal;
use App\Models\User;

final class DealPolicy
{
    public function view(User $user, Deal $deal): bool
    {
        if ($user->hasAnyRole(['Super Admin', 'Org Admin', 'Read-Only Analyst'])) {
            return true;
        }

        if ($user->hasRole('Sales Manager')) {
            return $deal->team_id === $user->team_id && $user->can('deals.view.team');
        }

        return $deal->team_id === $user->team_id
            && $deal->owner_id === $user->id
            && $user->can('deals.view.own');
    }

    public function update(User $user, Deal $deal): bool
    {
        if ($user->hasAnyRole(['Super Admin', 'Org Admin'])) {
            return true;
        }

        if ($user->hasRole('Sales Manager')) {
            return $deal->team_id === $user->team_id && $user->can('deals.update.team');
        }

        return $deal->team_id === $user->team_id
            && $deal->owner_id === $user->id
            && $user->can('deals.update.own');
    }

    public function delete(User $user, Deal $deal): bool
    {
        if ($user->hasAnyRole(['Super Admin', 'Org Admin'])) {
            return true;
        }

        return $user->hasRole('Sales Manager')
            && $deal->team_id === $user->team_id
            && $user->can('deals.delete');
    }
}
```
