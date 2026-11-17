<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Enums\DealStatus;
use App\Enums\FollowUpStatus;
use App\Enums\LeadStatus;
use App\Enums\TaskStatus;
use App\Models\Deal;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\Task;
use App\Models\User;
use App\Services\Audit\AuditLoggerService;
use Illuminate\Support\Facades\DB;

final readonly class ReassignUserRecordsAction
{
    public function __construct(
        private AuditLoggerService $auditLogger,
    ) {}

    public function handle(User $fromUser, User $toUser, User $actor): void
    {
        DB::transaction(function () use ($fromUser, $toUser, $actor): void {
            Lead::query()
                ->where('owner_id', $fromUser->id)
                ->whereNotIn('status', [LeadStatus::Converted->value, LeadStatus::Disqualified->value])
                ->update(['owner_id' => $toUser->id, 'team_id' => $toUser->team_id]);

            Deal::query()
                ->where('owner_id', $fromUser->id)
                ->where('status', DealStatus::Open->value)
                ->update(['owner_id' => $toUser->id, 'team_id' => $toUser->team_id]);

            Task::query()
                ->where('assigned_to_id', $fromUser->id)
                ->whereIn('status', [TaskStatus::Pending->value, TaskStatus::InProgress->value])
                ->update(['assigned_to_id' => $toUser->id, 'team_id' => $toUser->team_id]);

            FollowUp::query()
                ->where('owner_id', $fromUser->id)
                ->where('status', FollowUpStatus::Scheduled->value)
                ->update(['owner_id' => $toUser->id, 'team_id' => $toUser->team_id]);

            $this->auditLogger->log(
                event: 'records_reassigned',
                auditable: $fromUser,
                actor: $actor,
                oldValues: ['owner_id' => $fromUser->id],
                newValues: ['owner_id' => $toUser->id],
                teamId: $toUser->team_id,
            );
        });
    }
}
