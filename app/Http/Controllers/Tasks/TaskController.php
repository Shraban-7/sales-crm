<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tasks;

use App\Actions\Tasks\CompleteTaskAction;
use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tasks\StoreTaskRequest;
use App\Models\Deal;
use App\Models\Reminder;
use App\Models\Task;
use App\Models\User;
use App\Notifications\CrmAlertNotification;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Task::class);

        $user = $request->user();
        $filter = (string) $request->query('filter', 'open');

        $tasks = Task::query()
            ->with(['taskable', 'assignee', 'creator'])
            ->visibleTo($user)
            ->when($filter === 'completed', fn ($q) => $q->where('status', TaskStatus::Completed->value))
            ->when($filter === 'overdue', fn ($q) => $q->whereIn('status', [TaskStatus::Pending->value, TaskStatus::InProgress->value])->where('due_at', '<', now()))
            ->when($filter === 'open', fn ($q) => $q->whereIn('status', [TaskStatus::Pending->value, TaskStatus::InProgress->value]))
            ->orderBy('due_at')
            ->paginate((int) config('crm.pagination.per_page', 20))
            ->withQueryString();

        $teamUsers = User::query()
            ->where('team_id', $user->team_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $deals = Deal::query()->visibleTo($user)->orderBy('title')->get(['id', 'title']);

        return view('pages.tasks.index', compact('tasks', 'teamUsers', 'deals', 'filter'));
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $this->authorize('create', Task::class);

        $data = $request->validated();
        $modelClass = Relation::getMorphedModel((string) $data['taskable_type']);
        if ($modelClass === null) {
            abort(422);
        }

        $taskable = $modelClass::query()->findOrFail((int) $data['taskable_id']);
        $this->authorize('view', $taskable);

        $user = $request->user();

        $task = DB::transaction(function () use ($data, $user, $taskable): Task {
            /** @var Task $task */
            $task = Task::query()->create([
                'team_id' => $user->team_id,
                'assigned_to_id' => (int) $data['assigned_to_id'],
                'created_by_id' => $user->id,
                'taskable_type' => $taskable->getMorphClass(),
                'taskable_id' => $taskable->getKey(),
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'priority' => $data['priority'],
                'status' => TaskStatus::Pending,
                'due_at' => $data['due_at'],
            ]);

            if (! empty($data['remind_at'])) {
                Reminder::query()->create([
                    'user_id' => $task->assigned_to_id,
                    'remindable_type' => $task->getMorphClass(),
                    'remindable_id' => $task->id,
                    'remind_at' => $data['remind_at'],
                    'channel' => 'database',
                ]);
            }

            if ($task->assigned_to_id !== $user->id) {
                $assignee = User::query()->find($task->assigned_to_id);
                $assignee?->notify(new CrmAlertNotification(
                    category: 'task_assigned',
                    title: 'New Task Assigned',
                    message: "{$user->name} assigned you a task: {$task->title}",
                    actionUrl: route('tasks.index'),
                ));
            }

            return $task;
        });

        return back()->with('status', "Task '{$task->title}' created.");
    }

    public function complete(Request $request, Task $task, CompleteTaskAction $completeAction): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $task);

        $completed = $completeAction->handle($task);

        if ($request->expectsJson()) {
            return response()->json(['status' => $completed->status->value]);
        }

        return back()->with('status', 'Task marked as completed.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return back()->with('status', 'Task deleted.');
    }
}
