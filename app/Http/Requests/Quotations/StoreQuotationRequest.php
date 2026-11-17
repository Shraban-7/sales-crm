<?php

declare(strict_types=1);

namespace App\Http\Requests\Quotations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreQuotationRequest extends FormRequest
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
            'deal_id' => ['required', 'integer', Rule::exists('deals', 'id')->where('team_id', $this->user()->team_id)],
            'contact_id' => ['nullable', 'integer', Rule::exists('contacts', 'id')->where('team_id', $this->user()->team_id)],
            'currency' => ['required', 'string', 'size:3'],
            'issue_date' => ['required', 'date'],
            'valid_until' => ['required', 'date', 'after_or_equal:issue_date'],
            'terms_and_conditions' => ['nullable', 'string', 'max:5000'],
            'customer_notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_name' => ['required', 'string', 'max:191'],
            'items.*.description' => ['nullable', 'string', 'max:1000'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0', 'max:999999.99'],
            'items.*.unit' => ['nullable', 'string', 'max:30'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
