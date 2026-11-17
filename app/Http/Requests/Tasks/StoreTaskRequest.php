<?php

declare(strict_types=1);

namespace App\Http\Requests\Tasks;

use App\Enums\TaskPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $assignedToId = (int) $this->input('assigned_to_id', $this->user()->id);

        if ($assignedToId !== $this->user()->id && ! $this->user()->can('tasks.assign.team')) {
            return false;
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'taskable_type' => ['required', 'string', Rule::in(['lead', 'contact', 'company', 'deal'])],
            'taskable_id' => ['required', 'integer'],
            'assigned_to_id' => ['required', 'integer', Rule::exists('users', 'id')->where('team_id', $this->user()->team_id)],
            'title' => ['required', 'string', 'max:191'],
            'description' => ['nullable', 'string', 'max:2000'],
            'priority' => ['required', Rule::enum(TaskPriority::class)],
            'due_at' => ['required', 'date'],
            'remind_at' => ['nullable', 'date', 'before_or_equal:due_at'],
        ];
    }
}
