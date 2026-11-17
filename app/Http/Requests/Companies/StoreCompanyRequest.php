<?php

declare(strict_types=1);

namespace App\Http\Requests\Companies;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreCompanyRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:191'],
            'parent_company_id' => ['nullable', 'integer', Rule::exists('companies', 'id')->where('team_id', $this->user()->team_id)],
            'registration_number' => ['nullable', 'string', 'max:80'],
            'industry' => ['nullable', 'string', 'max:100'],
            'company_size' => ['nullable', 'string', 'max:50'],
            'annual_revenue' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'website' => ['nullable', 'url', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'billing_address' => ['nullable', 'string', 'max:1000'],
            'shipping_address' => ['nullable', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['required', 'string', 'size:2'],
        ];
    }
}
