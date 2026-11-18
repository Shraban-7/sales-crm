<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Attachment;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class AttachmentPolicy
{
    public function view(User $user, Attachment $attachment): bool
    {
        if ((int) $attachment->team_id !== (int) $user->team_id && ! $user->hasAnyRole(['Super Admin', 'Org Admin'])) {
            return false;
        }

        $attachable = $attachment->attachable;

        return $attachable !== null && Gate::forUser($user)->allows('view', $attachable);
    }

    public function delete(User $user, Attachment $attachment): bool
    {
        if ((int) $attachment->team_id !== (int) $user->team_id && ! $user->hasAnyRole(['Super Admin', 'Org Admin'])) {
            return false;
        }

        if ((int) $attachment->uploaded_by_id === (int) $user->id) {
            return true;
        }

        return $user->hasAnyRole(['Super Admin', 'Org Admin', 'Sales Manager']);
    }
}
