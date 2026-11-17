<x-layouts.app title="Tasks — Sales CRM" header="Tasks &amp; Reminders">
    <div class="flex gap-2 text-sm">
        <a href="{{ route('tasks.index', ['filter' => 'open']) }}" class="rounded-lg px-3 py-1.5 {{ $filter === 'open' ? 'bg-slate-900 text-white' : 'bg-white border border-slate-200' }}">Open</a>
        <a href="{{ route('tasks.index', ['filter' => 'overdue']) }}" class="rounded-lg px-3 py-1.5 {{ $filter === 'overdue' ? 'bg-rose-600 text-white' : 'bg-white border border-slate-200' }}">Overdue</a>
        <a href="{{ route('tasks.index', ['filter' => 'completed']) }}" class="rounded-lg px-3 py-1.5 {{ $filter === 'completed' ? 'bg-emerald-600 text-white' : 'bg-white border border-slate-200' }}">Completed</a>
    </div>

    @can('create', \App\Models\Task::class)
        <form method="POST" action="{{ route('tasks.store') }}" class="bg-white rounded-xl border border-slate-200 p-5 grid grid-cols-1 md:grid-cols-6 gap-3">
            @csrf
            <input type="hidden" name="taskable_type" value="deal">
            <input type="text" name="title" placeholder="Task Title *" required class="md:col-span-2 rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <select name="taskable_id" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Deal *</option>
                @foreach ($deals as $deal)
                    <option value="{{ $deal->id }}">{{ $deal->title }}</option>
                @endforeach
            </select>
            <select name="priority" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @foreach (\App\Enums\TaskPriority::cases() as $priority)
                    <option value="{{ $priority->value }}">{{ $priority->label() }}</option>
                @endforeach
            </select>
            <input type="datetime-local" name="due_at" value="{{ now()->addDay()->format('Y-m-d\TH:i') }}" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <input type="hidden" name="assigned_to_id" value="{{ auth()->id() }}">
            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Add Task</button>
        </form>
    @endcan

    <div class="bg-white rounded-xl border border-slate-200 divide-y divide-slate-100">
        @forelse ($tasks as $task)
            <div class="p-4 flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-slate-900">{{ $task->title }}</p>
                    <p class="text-xs text-slate-500">Assigned to {{ $task->assignee?->name }} &middot; Due {{ $task->due_at->toDayDateTimeString() }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <x-crm.status-badge :status="$task->priority" />
                    <x-crm.status-badge :status="$task->status" />
                    @if ($task->status !== \App\Enums\TaskStatus::Completed)
                        @can('update', $task)
                            <form method="POST" action="{{ route('tasks.complete', $task) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white">Complete</button>
                            </form>
                        @endcan
                    @endif
                </div>
            </div>
        @empty
            <p class="p-6 text-center text-sm text-slate-500">No tasks found.</p>
        @endforelse
    </div>

    {{ $tasks->links() }}
</x-layouts.app>
