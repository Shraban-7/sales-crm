<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Company;
use App\Models\User;
use App\Policies\Concerns\ResolvesTeamOwnership;

final class CompanyPolicy
{
    use ResolvesTeamOwnership;

    public function viewAny(User $user): bool
    {
        return $user->can('companies.view.own') || $user->can('companies.view.team') || $user->can('companies.view.all');
    }

    public function view(User $user, Company $company): bool
    {
        return $this->canViewScoped($user, $company, 'companies');
    }

    public function create(User $user): bool
    {
        return $user->can('companies.create');
    }

    public function update(User $user, Company $company): bool
    {
        return $this->canUpdateScoped($user, $company, 'companies');
    }

    public function delete(User $user, Company $company): bool
    {
        return $this->canDeleteScoped($user, $company, 'companies');
    }
}
