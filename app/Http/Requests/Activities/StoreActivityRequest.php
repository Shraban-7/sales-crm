<?php

declare(strict_types=1);

namespace App\Http\Requests\Activities;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreActivityRequest extends FormRequest
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
            'subject_type' => ['required', 'string', Rule::in(['lead', 'contact', 'company', 'deal'])],
            'subject_id' => ['required', 'integer'],
            'type' => ['required', Rule::in(['call', 'email', 'meeting', 'note'])],
            'direction' => ['nullable', Rule::in(['inbound', 'outbound'])],
            'outcome' => ['nullable', 'string', 'max:60'],
            'summary' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:10000'],
            'duration_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'occurred_at' => ['required', 'date'],
        ];
    }
}
