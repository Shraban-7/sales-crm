<?php

declare(strict_types=1);

namespace App\Http\Requests\Users;

use App\Enums\RoleId;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class StoreUserRequest extends FormRequest
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
        $allowedRoles = ['Org Admin', 'Sales Manager', 'Sales Rep', 'Read-Only Analyst'];
        if ($this->user()->hasRole(RoleId::SuperAdmin->value)) {
            $allowedRoles[] = 'Super Admin';
        }

        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email:rfc', 'max:191', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'job_title' => ['nullable', 'string', 'max:100'],
            'team_id' => ['required', 'integer', 'exists:teams,id'],
            'role' => ['required', 'string', Rule::in($allowedRoles)],
            'password' => ['required', 'confirmed', Password::defaults()],
            'is_active' => ['boolean'],
        ];
    }
}
