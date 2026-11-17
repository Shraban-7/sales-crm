<?php

declare(strict_types=1);

namespace App\Http\Requests\Leads;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateLeadRequest extends FormRequest
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
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'company_name' => ['nullable', 'string', 'max:191'],
            'job_title' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email:rfc', 'max:191', 'required_without:phone'],
            'phone' => ['nullable', 'string', 'max:30', 'required_without:email'],
            'website' => ['nullable', 'url', 'max:255'],
            'source' => ['required', Rule::enum(LeadSource::class)],
            'status' => ['required', Rule::enum(LeadStatus::class), Rule::notIn([LeadStatus::Converted->value])],
            'score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'estimated_value' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'disqualified_reason' => ['nullable', 'required_if:status,disqualified', 'string', 'max:255'],
        ];
    }
}
