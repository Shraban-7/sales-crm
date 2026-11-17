<?php

declare(strict_types=1);

namespace App\Services\Audit;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

final class AuditLoggerService
{
    /**
     * @var list<string>
     */
    private const REDACTED_KEYS = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    public function log(
        string $event,
        Model|string $auditable,
        ?User $actor = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $teamId = null,
        ?Request $request = null,
    ): AuditLog {
        $req = $request ?? (app()->runningInConsole() ? null : request());
        $ipAddress = $req?->ip() ?? '127.0.0.1 (cli)';
        $userAgent = $req?->userAgent();

        $auditableType = $auditable instanceof Model ? $auditable->getMorphClass() : $auditable;
        $auditableId = $auditable instanceof Model ? $auditable->getKey() : null;
        $resolvedTeamId = $teamId ?? ($auditable instanceof Model && isset($auditable->team_id) ? (int) $auditable->team_id : $actor?->team_id);

        return AuditLog::query()->create([
            'team_id' => $resolvedTeamId,
            'user_id' => $actor?->id,
            'event' => $event,
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'old_values' => $this->redact($oldValues),
            'new_values' => $this->redact($newValues),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent !== null ? mb_substr($userAgent, 0, 500) : null,
            'created_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $values
     * @return array<string, mixed>|null
     */
    private function redact(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        foreach (self::REDACTED_KEYS as $key) {
            if (array_key_exists($key, $values)) {
                $values[$key] = '[REDACTED]';
            }
        }

        return $values;
    }
}
