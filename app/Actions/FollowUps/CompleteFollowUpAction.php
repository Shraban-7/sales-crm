<?php

declare(strict_types=1);

namespace App\Actions\FollowUps;

use App\Actions\Activities\LogActivityAction;
use App\Enums\ActivityType;
use App\Enums\FollowUpStatus;
use App\Exceptions\DomainRuleException;
use App\Models\FollowUp;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class CompleteFollowUpAction
{
    public function __construct(
        private LogActivityAction $logActivityAction,
    ) {}

    /**
     * @param  array<string, mixed>|null  $nextFollowUpData
     */
    public function handle(FollowUp $followUp, User $actor, string $outcomeNotes, ?array $nextFollowUpData = null): FollowUp
    {
        if (trim($outcomeNotes) === '') {
            throw new DomainRuleException('Outcome notes are required when completing a follow-up.', 422);
        }

        return DB::transaction(function () use ($followUp, $actor, $outcomeNotes, $nextFollowUpData): FollowUp {
            $nextFollowUp = null;

            if ($nextFollowUpData !== null) {
                $nextFollowUp = FollowUp::query()->create([
                    'team_id' => $followUp->team_id,
                    'owner_id' => $followUp->owner_id,
                    'subject_type' => $followUp->subject_type,
                    'subject_id' => $followUp->subject_id,
                    'channel' => $nextFollowUpData['channel'],
                    'status' => FollowUpStatus::Scheduled,
                    'scheduled_at' => $nextFollowUpData['scheduled_at'],
                    'objective' => $nextFollowUpData['objective'],
                ]);
            }

            $followUp->update([
                'status' => FollowUpStatus::Completed,
                'completed_at' => now(),
                'outcome_notes' => $outcomeNotes,
                'next_follow_up_id' => $nextFollowUp?->id,
            ]);

            $subject = $followUp->subject;
            if ($subject !== null) {
                $activityType = match ($followUp->channel->value) {
                    'email' => ActivityType::Email->value,
                    'meeting' => ActivityType::Meeting->value,
                    default => ActivityType::Call->value,
                };

                $this->logActivityAction->handle($subject, $actor, [
                    'type' => $activityType,
                    'direction' => 'outbound',
                    'outcome' => 'Follow-up completed',
                    'summary' => "Follow-up: {$followUp->objective}",
                    'body' => $outcomeNotes,
                    'occurred_at' => now(),
                ]);
            }

            return $followUp->fresh(['nextFollowUp']);
        });
    }
}
