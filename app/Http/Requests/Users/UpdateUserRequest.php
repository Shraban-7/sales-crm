<?php

declare(strict_types=1);

namespace App\Http\Requests\Users;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class UpdateUserRequest extends FormRequest
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
        /** @var User $targetUser */
        $targetUser = $this->route('user');

        $allowedRoles = ['Org Admin', 'Sales Manager', 'Sales Rep', 'Read-Only Analyst'];
        if ($this->user()->hasRole('Super Admin')) {
            $allowedRoles[] = 'Super Admin';
        }

        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email:rfc', 'max:191', Rule::unique('users', 'email')->ignore($targetUser->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'job_title' => ['nullable', 'string', 'max:100'],
            'team_id' => ['required', 'integer', 'exists:teams,id'],
            'role' => ['required', 'string', Rule::in($allowedRoles)],
            'is_active' => ['boolean'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ];
    }
}
