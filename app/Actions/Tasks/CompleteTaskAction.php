<?php

declare(strict_types=1);

namespace App\Actions\Tasks;

use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Support\Facades\DB;

final readonly class CompleteTaskAction
{
    public function handle(Task $task): Task
    {
        return DB::transaction(function () use ($task): Task {
            $task->update([
                'status' => TaskStatus::Completed,
                'completed_at' => now(),
            ]);

            $task->reminders()
                ->whereNull('sent_at')
                ->delete();

            return $task->fresh();
        });
    }
}
