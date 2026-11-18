<?php

declare(strict_types=1);

namespace App\Http\Requests\Leads;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ConvertLeadRequest extends FormRequest
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
            'company_name' => ['required', 'string', 'max:191'],
            'deal_title' => ['required', 'string', 'max:191'],
            'pipeline_id' => [
                'required',
                'integer',
                Rule::exists('pipelines', 'id')->where(function ($query): void {
                    $query->whereNull('team_id')->orWhere('team_id', $this->user()->team_id);
                }),
            ],
            'pipeline_stage_id' => [
                'required',
                'integer',
                Rule::exists('pipeline_stages', 'id')->where('pipeline_id', $this->input('pipeline_id')),
            ],
            'expected_value' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'currency' => ['nullable', 'string', 'size:3'],
            'expected_close_date' => ['nullable', 'date'],
        ];
    }
}
