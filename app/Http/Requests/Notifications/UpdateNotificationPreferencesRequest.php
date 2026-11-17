<?php

declare(strict_types=1);

namespace App\Http\Requests\Notifications;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateNotificationPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('notifications.manage_preferences.own');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'preferences' => ['required', 'array'],
            'preferences.lead_assigned' => ['required', 'array'],
            'preferences.lead_assigned.database' => ['required', 'boolean'],
            'preferences.lead_assigned.mail' => ['required', 'boolean'],
            'preferences.task_reminder' => ['required', 'array'],
            'preferences.task_reminder.database' => ['required', 'boolean'],
            'preferences.task_reminder.mail' => ['required', 'boolean'],
            'preferences.task_assigned' => ['sometimes', 'array'],
            'preferences.task_assigned.database' => ['sometimes', 'boolean'],
            'preferences.task_assigned.mail' => ['sometimes', 'boolean'],
        ];
    }
}
