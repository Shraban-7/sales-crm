<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AuditLog;
use App\Models\User;

final class AuditLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('audit_logs.view.team') || $user->can('audit_logs.view.all');
    }

    public function view(User $user, AuditLog $auditLog): bool
    {
        if ($user->can('audit_logs.view.all')) {
            return true;
        }

        return $user->can('audit_logs.view.team') && (int) $auditLog->team_id === (int) $user->team_id;
    }
}
