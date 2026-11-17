<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;
use App\Policies\Concerns\ResolvesTeamOwnership;

final class ContactPolicy
{
    use ResolvesTeamOwnership;

    public function viewAny(User $user): bool
    {
        return $user->can('contacts.view.own') || $user->can('contacts.view.team') || $user->can('contacts.view.all');
    }

    public function view(User $user, Contact $contact): bool
    {
        return $this->canViewScoped($user, $contact, 'contacts');
    }

    public function create(User $user): bool
    {
        return $user->can('contacts.create');
    }

    public function update(User $user, Contact $contact): bool
    {
        return $this->canUpdateScoped($user, $contact, 'contacts');
    }

    public function delete(User $user, Contact $contact): bool
    {
        return $this->canDeleteScoped($user, $contact, 'contacts');
    }
}
