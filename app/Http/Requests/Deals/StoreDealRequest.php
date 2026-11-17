<?php

declare(strict_types=1);

namespace App\Http\Requests\Deals;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreDealRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:191'],
            'company_id' => ['required', 'integer', Rule::exists('companies', 'id')->where('team_id', $this->user()->team_id)],
            'primary_contact_id' => ['nullable', 'integer', Rule::exists('contacts', 'id')->where('team_id', $this->user()->team_id)],
            'pipeline_id' => ['required', 'integer', 'exists:pipelines,id'],
            'pipeline_stage_id' => [
                'required',
                'integer',
                Rule::exists('pipeline_stages', 'id')->where('pipeline_id', $this->input('pipeline_id')),
            ],
            'value' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'currency' => ['required', 'string', 'size:3'],
            'probability' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'expected_close_date' => ['nullable', 'date'],
        ];
    }
}
