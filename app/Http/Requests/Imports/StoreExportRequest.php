<?php

declare(strict_types=1);

namespace App\Http\Requests\Imports;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreExportRequest extends FormRequest
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
            'module' => ['required', Rule::in(['leads', 'contacts', 'companies', 'deals'])],
        ];
    }
}
