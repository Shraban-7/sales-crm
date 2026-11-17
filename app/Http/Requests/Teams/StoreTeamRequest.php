<?php

declare(strict_types=1);

namespace App\Http\Requests\Teams;

use Illuminate\Foundation\Http\FormRequest;

final class StoreTeamRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:120'],
            'leader_id' => ['nullable', 'integer', 'exists:users,id'],
            'monthly_revenue_target' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'is_active' => ['boolean'],
        ];
    }
}
