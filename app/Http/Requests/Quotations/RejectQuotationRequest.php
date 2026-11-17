<?php

declare(strict_types=1);

namespace App\Http\Requests\Quotations;

use Illuminate\Foundation\Http\FormRequest;

final class RejectQuotationRequest extends FormRequest
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
            'rejection_reason' => ['required', 'string', 'max:255'],
        ];
    }
}
