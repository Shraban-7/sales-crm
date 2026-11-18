<?php

declare(strict_types=1);

namespace App\Http\Requests\Leads;

use App\Enums\LeadSource;
use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class PublicLeadCaptureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => $this->filled('email') ? mb_strtolower(trim((string) $this->input('email'))) : null,
            'phone' => PhoneNumber::normalize($this->input('phone')),
        ]);
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
            'source' => ['required', Rule::in([
                LeadSource::Website->value,
                LeadSource::Referral->value,
                LeadSource::ColdCall->value,
                LeadSource::LinkedIn->value,
                LeadSource::Event->value,
            ])],
            'message' => ['nullable', 'string', 'max:2000'],
            'consent' => ['accepted'],
            'company_website' => ['nullable', 'string', 'max:255'],
        ];
    }
}
