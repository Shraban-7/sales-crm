<?php

declare(strict_types=1);

namespace App\Http\Requests\FollowUps;

use App\Enums\FollowUpChannel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CompleteFollowUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'outcome_notes' => ['required', 'string', 'max:2000'],
            'schedule_next' => ['boolean'],
            'next_channel' => ['nullable', 'required_if:schedule_next,1,true', Rule::enum(FollowUpChannel::class)],
            'next_scheduled_at' => ['nullable', 'required_if:schedule_next,1,true', 'date', 'after:now'],
            'next_objective' => ['nullable', 'required_if:schedule_next,1,true', 'string', 'max:255'],
        ];
    }
}
