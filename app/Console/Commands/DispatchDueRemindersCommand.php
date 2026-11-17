<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\TaskStatus;
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

                if ($remindable instanceof Task && $remindable->status === TaskStatus::Completed) {
                    continue;
                }

                $title = $remindable instanceof Task
                    ? "Task Reminder: {$remindable->title}"
                    : 'CRM Reminder';

                $user->notify(new CrmAlertNotification(
                    category: 'task_reminder',
                    title: $title,
                    message: 'You have a scheduled CRM reminder due now.',
                    actionUrl: route('tasks.index'),
                ));

                $dispatched++;
            }
        });

        $this->info("Dispatched {$dispatched} due reminder(s).");

        return self::SUCCESS;
    }
}
