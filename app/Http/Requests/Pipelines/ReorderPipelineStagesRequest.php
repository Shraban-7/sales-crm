<?php

declare(strict_types=1);

namespace App\Http\Requests\Pipelines;

use App\Models\Pipeline;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ReorderPipelineStagesRequest extends FormRequest
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
        /** @var Pipeline $pipeline */
        $pipeline = $this->route('pipeline');

        return [
            'stage_ids' => ['required', 'array', 'min:1'],
            'stage_ids.*' => ['required', 'integer', Rule::exists('pipeline_stages', 'id')->where('pipeline_id', $pipeline->id)],
        ];
    }
}
