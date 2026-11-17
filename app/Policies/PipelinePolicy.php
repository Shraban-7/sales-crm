<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Pipeline;
use App\Models\User;

final class PipelinePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('pipelines.view');
    }

    public function view(User $user, Pipeline $pipeline): bool
    {
        return $user->can('pipelines.view');
    }

    public function create(User $user): bool
    {
        return $user->can('pipelines.manage');
    }

    public function update(User $user, Pipeline $pipeline): bool
    {
        return $user->can('pipelines.manage');
    }

    public function delete(User $user, Pipeline $pipeline): bool
    {
        return $user->can('pipelines.manage');
    }
}
