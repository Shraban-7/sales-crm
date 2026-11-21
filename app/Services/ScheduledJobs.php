<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DealStatus;
use App\Enums\FollowUpStatus;
use App\Enums\QuotationStatus;
use App\Enums\TaskStatus;
use App\Models\Deal;
use App\Models\FollowUp;
use App\Models\Proposal;
use App\Models\Quotation;
use App\Models\Task;
use App\Notifications\CrmAlertNotification;

final class ScheduledJobs
{
    public function dispatchDueReminders(): int
    {
        $dueTasks = Task::query()
            ->whereIn('status', [TaskStatus::Pending->value, TaskStatus::InProgress->value])
            ->whereNotNull('due_at')
            ->where('due_at', '<=', now()->addHours(2))
            ->with('assignee')
            ->get();

        $count = 0;
        foreach ($dueTasks as $task) {
            $task->assignee?->notify(new CrmAlertNotification(
                category: 'task_reminder',
                title: 'Task Due Reminder',
                message: "Task '{$task->title}' is due soon: {$task->due_at?->diffForHumans()}.",
                actionUrl: route('tasks.index'),
            ));
            $count++;
        }

        return $count;
    }

    public function escalateOverdueFollowUps(): int
    {
        return FollowUp::query()
            ->where('status', FollowUpStatus::Scheduled->value)
            ->where('scheduled_at', '<', now()->startOfDay())
            ->update(['status' => FollowUpStatus::Missed->value]);
    }

    public function expireQuotations(): int
    {
        return Quotation::query()
            ->where('status', QuotationStatus::Sent->value)
            ->whereNotNull('valid_until')
            ->where('valid_until', '<', now()->toDateString())
            ->update(['status' => QuotationStatus::Expired->value]);
    }

    public function expireProposals(): int
    {
        return Proposal::query()
            ->where('status', 'sent')
            ->whereNotNull('valid_until')
            ->where('valid_until', '<', now()->toDateString())
            ->update(['status' => 'expired']);
    }

    public function checkProposalExpiry(): int
    {
        return $this->expireProposals() + $this->expireQuotations();
    }

    public function scoreDeals(): int
    {
        // Detect rotting deals past their stage threshold
        return Deal::query()
            ->where('status', DealStatus::Open->value)
            ->whereHas('stage', function ($query): void {
                $query->whereNotNull('rotting_days');
            })
            ->count();
    }
}
