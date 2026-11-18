<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Enums\DealStatus;
use App\Enums\FollowUpStatus;
use App\Enums\LeadStatus;
use App\Enums\TaskStatus;
use App\Exceptions\DomainRuleException;
use App\Models\Activity;
use App\Models\Attachment;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\Quotation;
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
        if ((int) $fromUser->id === (int) $toUser->id) {
            throw new DomainRuleException('Choose a different user to receive the records.', 422);
        }

        if (! $toUser->is_active) {
            throw new DomainRuleException('Cannot reassign records to an inactive user.', 422);
        }

        $isCrossTeam = (int) $fromUser->team_id !== (int) $toUser->team_id;

        if ($isCrossTeam && ! $actor->can('users.manage')) {
            throw new DomainRuleException(
                'Records can only be reassigned across teams by an organization administrator.',
                422,
            );
        }

        DB::transaction(function () use ($fromUser, $toUser, $actor): void {
            $counts = [];

            $counts['leads'] = Lead::query()
                ->where('owner_id', $fromUser->id)
                ->whereNotIn('status', [LeadStatus::Converted->value, LeadStatus::Disqualified->value])
                ->update(['owner_id' => $toUser->id, 'team_id' => $toUser->team_id]);

            $counts['deals'] = Deal::query()
                ->where('owner_id', $fromUser->id)
                ->where('status', DealStatus::Open->value)
                ->update(['owner_id' => $toUser->id, 'team_id' => $toUser->team_id]);

            $counts['contacts'] = Contact::query()
                ->where('owner_id', $fromUser->id)
                ->update(['owner_id' => $toUser->id, 'team_id' => $toUser->team_id]);

            $counts['companies'] = Company::query()
                ->where('owner_id', $fromUser->id)
                ->update(['owner_id' => $toUser->id, 'team_id' => $toUser->team_id]);

            $counts['quotations'] = Quotation::query()
                ->where('owner_id', $fromUser->id)
                ->update(['owner_id' => $toUser->id, 'team_id' => $toUser->team_id]);

            $counts['activities'] = Activity::query()
                ->where('user_id', $fromUser->id)
                ->update(['user_id' => $toUser->id, 'team_id' => $toUser->team_id]);

            $counts['attachments'] = Attachment::query()
                ->where('uploaded_by_id', $fromUser->id)
                ->update(['uploaded_by_id' => $toUser->id, 'team_id' => $toUser->team_id]);

            $counts['tasks'] = Task::query()
                ->where('assigned_to_id', $fromUser->id)
                ->whereIn('status', [TaskStatus::Pending->value, TaskStatus::InProgress->value])
                ->update(['assigned_to_id' => $toUser->id, 'team_id' => $toUser->team_id]);

            $counts['follow_ups'] = FollowUp::query()
                ->where('owner_id', $fromUser->id)
                ->where('status', FollowUpStatus::Scheduled->value)
                ->update(['owner_id' => $toUser->id, 'team_id' => $toUser->team_id]);

            $this->auditLogger->log(
                event: 'records_reassigned',
                auditable: $fromUser,
                actor: $actor,
                oldValues: ['owner_id' => $fromUser->id, 'team_id' => $fromUser->team_id],
                newValues: [
                    'owner_id' => $toUser->id,
                    'team_id' => $toUser->team_id,
                    'cross_team' => (int) $fromUser->team_id !== (int) $toUser->team_id,
                    'counts' => array_filter($counts, fn (int $count): bool => $count > 0),
                ],
                teamId: $toUser->team_id,
            );
        });
    }
}
