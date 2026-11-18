<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\FollowUpStatus;
use App\Models\FollowUp;
use App\Notifications\CrmAlertNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class EscalateOverdueFollowUpsCommand extends Command
{
    protected $signature = 'crm:escalate-overdue-follow-ups';

    protected $description = 'Mark overdue scheduled follow-ups as missed and alert owners and team leaders';

    public function handle(): int
    {
        $escalated = 0;

        DB::transaction(function () use (&$escalated): void {
            $overdue = FollowUp::query()
                ->with(['owner', 'team.leader'])
                ->where('status', FollowUpStatus::Scheduled->value)
                ->where('scheduled_at', '<', now()->subHour())
                ->lockForUpdate()
                ->get();

            foreach ($overdue as $followUp) {
                $followUp->update(['status' => FollowUpStatus::Missed]);

                $owner = $followUp->owner;
                if ($owner !== null && $owner->is_active) {
                    $owner->notify(new CrmAlertNotification(
                        category: 'task_reminder',
                        title: "Missed Follow-Up: {$followUp->objective}",
                        message: 'A scheduled customer follow-up is overdue and was marked as missed.',
                        actionUrl: route('follow-ups.index'),
                    ));
                }

                $leader = $followUp->team?->leader;
                if ($leader !== null && $leader->is_active && $leader->id !== $owner?->id) {
                    $leader->notify(new CrmAlertNotification(
                        category: 'task_reminder',
                        title: "Overdue Team Follow-Up: {$followUp->objective}",
                        message: "{$owner?->name} missed a scheduled follow-up.",
                        actionUrl: route('follow-ups.index'),
                    ));
                }

                $escalated++;
            }
        });

        $this->info("Escalated {$escalated} overdue follow-up(s).");

        return self::SUCCESS;
    }
}
