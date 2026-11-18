<x-layouts.app title="Tasks — Sales CRM" header="Tasks & Reminders">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="inline-flex items-center gap-1.5 rounded-xl bg-slate-200/70 p-1 text-sm">
            <a href="{{ route('tasks.index', ['filter' => 'open']) }}"
               class="rounded-lg px-3.5 py-1.5 font-medium transition {{ $filter === 'open' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Open
            </a>
            <a href="{{ route('tasks.index', ['filter' => 'overdue']) }}"
               class="rounded-lg px-3.5 py-1.5 font-medium transition {{ $filter === 'overdue' ? 'bg-rose-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Overdue
            </a>
            <a href="{{ route('tasks.index', ['filter' => 'completed']) }}"
               class="rounded-lg px-3.5 py-1.5 font-medium transition {{ $filter === 'completed' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Completed
            </a>
        </div>
    </div>

    @can('create', \App\Models\Task::class)
        <form method="POST" action="{{ route('tasks.store') }}" class="crm-card p-5 space-y-3">
            @csrf
            <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Create Task</h2>
            <input type="hidden" name="taskable_type" value="deal">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                <input type="text" name="title" placeholder="Task Title *" required class="md:col-span-2 crm-input">
                <select name="taskable_id" required class="crm-select">
                    <option value="">Deal *</option>
                    @foreach ($deals as $deal)
                        <option value="{{ $deal->id }}">{{ $deal->title }}</option>
                    @endforeach
                </select>
                <select name="priority" class="crm-select">
                    @foreach (\App\Enums\TaskPriority::cases() as $priority)
                        <option value="{{ $priority->value }}">{{ $priority->label() }}</option>
                    @endforeach
                </select>
                <input type="datetime-local" name="due_at" value="{{ now()->addDay()->format('Y-m-d\TH:i') }}" required class="crm-input">
                <input type="hidden" name="assigned_to_id" value="{{ auth()->id() }}">
                <button type="submit" class="crm-btn-primary">Add Task</button>
            </div>
        </form>
    @endcan

    <div class="crm-card divide-y divide-slate-100 overflow-hidden">
        @forelse ($tasks as $task)
            <div class="p-4 flex flex-wrap items-center justify-between gap-4 hover:bg-slate-50/70 transition">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-900">{{ $task->title }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Assigned to <span class="font-medium text-slate-700">{{ $task->assignee?->name }}</span>
                        &middot;
                        <span class="{{ $task->due_at->isPast() && $task->status !== \App\Enums\TaskStatus::Completed ? 'text-rose-600 font-medium' : '' }}">
                            Due {{ $task->due_at->toDayDateTimeString() }}
                        </span>
                    </p>
                </div>
                <div class="flex items-center gap-2.5">
                    <x-crm.status-badge :status="$task->priority" />
                    <x-crm.status-badge :status="$task->status" />
                    @if ($task->status !== \App\Enums\TaskStatus::Completed)
                        @can('update', $task)
                            <form method="POST" action="{{ route('tasks.complete', $task) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="inline-flex items-center gap-1 rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700 transition">
                                    Complete
                                </button>
                            </form>
                        @endcan
                    @endif
                </div>
            </div>
        @empty
            <div class="p-10 text-center">
                <p class="text-sm font-medium text-slate-600">No tasks found</p>
                <p class="text-xs text-slate-400 mt-1">Switch filters above or create a new task linked to a deal.</p>
            </div>
        @endforelse
    </div>

    {{ $tasks->links() }}
</x-layouts.app>
