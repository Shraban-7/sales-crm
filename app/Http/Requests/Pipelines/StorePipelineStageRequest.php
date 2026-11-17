<?php

declare(strict_types=1);

namespace App\Http\Requests\Pipelines;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class StorePipelineStageRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100'],
            'default_probability' => ['required', 'numeric', 'min:0', 'max:100'],
            'rotting_days' => ['required', 'integer', 'min:1', 'max:365'],
            'color' => ['required', 'string', Rule::in(['slate', 'blue', 'cyan', 'amber', 'emerald', 'rose', 'indigo'])],
            'is_won_stage' => ['boolean'],
            'is_lost_stage' => ['boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->boolean('is_won_stage') && $this->boolean('is_lost_stage')) {
                $validator->errors()->add('is_won_stage', 'A stage cannot be both a Won stage and a Lost stage.');
            }
        });
    }
}
