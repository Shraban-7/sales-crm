<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Webhook;

final class WebhookPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('webhooks.manage');
    }

    public function view(User $user, Webhook $webhook): bool
    {
        return $user->can('webhooks.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('webhooks.manage');
    }

    public function update(User $user, Webhook $webhook): bool
    {
        return $user->can('webhooks.manage');
    }

    public function delete(User $user, Webhook $webhook): bool
    {
        return $user->can('webhooks.manage');
    }
}
