<?php

declare(strict_types=1);

namespace App\Http\Requests\Companies;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class UpdateCompanyRequest extends FormRequest
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
        /** @var Company $company */
        $company = $this->route('company');

        return [
            'name' => ['required', 'string', 'max:191'],
            'parent_company_id' => [
                'nullable',
                'integer',
                Rule::notIn([$company->id]),
                Rule::exists('companies', 'id')->where('team_id', $company->team_id),
            ],
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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $parentId = $this->input('parent_company_id');
            if ($parentId === null || $parentId === '') {
                return;
            }

            /** @var Company $company */
            $company = $this->route('company');
            $currentId = (int) $parentId;
            $depth = 0;

            while ($currentId > 0 && $depth < 6) {
                if ($currentId === $company->id) {
                    $validator->errors()->add('parent_company_id', 'Circular parent company hierarchy is not allowed.');
                    return;
                }

                $parent = Company::query()->select(['id', 'parent_company_id'])->find($currentId);
                $currentId = (int) ($parent?->parent_company_id ?? 0);
                $depth++;
            }
        });
    }
}
