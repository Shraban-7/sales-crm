<?php

declare(strict_types=1);

namespace App\Http\Requests\Pipelines;

use Illuminate\Foundation\Http\FormRequest;

final class StorePipelineRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:120'],
            'team_id' => ['nullable', 'integer', 'exists:teams,id'],
            'is_default' => ['boolean'],
        ];
    }
}
