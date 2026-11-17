<?php

declare(strict_types=1);

namespace App\Actions\Activities;

use App\Enums\ActivityType;
use App\Enums\LeadStatus;
use App\Models\Activity;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final readonly class LogActivityAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(Model $subject, User $actor, array $attributes): Activity
    {
        return DB::transaction(function () use ($subject, $actor, $attributes): Activity {
            $occurredAt = isset($attributes['occurred_at'])
                ? Carbon::parse((string) $attributes['occurred_at'])
                : now();

            $teamId = (int) ($subject->getAttribute('team_id') ?? $actor->team_id);

            /** @var Activity $activity */
            $activity = Activity::query()->create([
                'team_id' => $teamId,
                'user_id' => $actor->id,
                'subject_type' => $subject->getMorphClass(),
                'subject_id' => $subject->getKey(),
                'type' => $attributes['type'],
                'direction' => $attributes['direction'] ?? null,
                'outcome' => $attributes['outcome'] ?? null,
                'summary' => $attributes['summary'],
                'body' => $attributes['body'] ?? null,
                'duration_minutes' => $attributes['duration_minutes'] ?? null,
                'occurred_at' => $occurredAt,
            ]);

            if ($subject instanceof Deal) {
                if ($subject->last_activity_at === null || $occurredAt->greaterThan($subject->last_activity_at)) {
                    $subject->update(['last_activity_at' => $occurredAt]);
                }
            }

            if ($subject instanceof Lead) {
                $type = $activity->type;
                $updates = [];

                if ($subject->last_contacted_at === null || $occurredAt->greaterThan($subject->last_contacted_at)) {
                    $updates['last_contacted_at'] = $occurredAt;
                }

                if (
                    $subject->status === LeadStatus::New
                    && in_array($type, [ActivityType::Call, ActivityType::Email, ActivityType::Meeting], true)
                ) {
                    $updates['status'] = LeadStatus::Contacted;
                }

                if ($updates !== []) {
                    $subject->update($updates);
                }
            }

            return $activity;
        });
    }
}
