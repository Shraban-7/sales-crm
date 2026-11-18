<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\FollowUpStatus;
use App\Enums\TaskStatus;
use App\Models\FollowUp;
use App\Models\Reminder;
use App\Models\Task;
use App\Notifications\CrmAlertNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class DispatchDueRemindersCommand extends Command
{
    protected $signature = 'crm:dispatch-due-reminders';

    protected $description = 'Dispatch due task and follow-up reminders idempotently';

    public function handle(): int
    {
        $dispatched = 0;

        DB::transaction(function () use (&$dispatched): void {
            $dueReminders = Reminder::query()
                ->with(['user', 'remindable'])
                ->whereNull('sent_at')
                ->where('remind_at', '<=', now())
                ->lockForUpdate()
                ->get();

            foreach ($dueReminders as $reminder) {
                $reminder->update(['sent_at' => now()]);

                $user = $reminder->user;
                $remindable = $reminder->remindable;

                if ($user === null || ! $user->is_active || $remindable === null) {
                    continue;
                }

                if ($remindable instanceof Task && in_array($remindable->status, [TaskStatus::Completed, TaskStatus::Cancelled], true)) {
                    continue;
                }

                if ($remindable instanceof FollowUp && $remindable->status !== FollowUpStatus::Scheduled) {
                    continue;
                }

                $title = match (true) {
                    $remindable instanceof Task => "Task Reminder: {$remindable->title}",
                    $remindable instanceof FollowUp => "Follow-Up Reminder: {$remindable->objective}",
                    default => 'CRM Reminder',
                };

                $actionUrl = $remindable instanceof FollowUp
                    ? route('follow-ups.index')
                    : route('tasks.index');

                $user->notify(new CrmAlertNotification(
                    category: 'task_reminder',
                    title: $title,
                    message: 'You have a scheduled CRM reminder due now.',
                    actionUrl: $actionUrl,
                ));

                $dispatched++;
            }
        });

        $this->info("Dispatched {$dispatched} due reminder(s).");

        return self::SUCCESS;
    }
}
