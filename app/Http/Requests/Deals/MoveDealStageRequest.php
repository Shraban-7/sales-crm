<?php

declare(strict_types=1);

namespace App\Http\Requests\Deals;

use App\Models\Deal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class MoveDealStageRequest extends FormRequest
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
        /** @var Deal $deal */
        $deal = $this->route('deal');

        return [
            'pipeline_stage_id' => [
                'required',
                'integer',
                Rule::exists('pipeline_stages', 'id')->where('pipeline_id', $deal->pipeline_id),
            ],
            'lost_reason' => ['nullable', 'string', 'max:150'],
            'lost_competitor' => ['nullable', 'string', 'max:150'],
            'won_notes' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
