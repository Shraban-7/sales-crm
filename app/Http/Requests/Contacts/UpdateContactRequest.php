<?php

declare(strict_types=1);

namespace App\Http\Requests\Contacts;

use App\Models\Contact;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateContactRequest extends FormRequest
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
        /** @var Contact $contact */
        $contact = $this->route('contact');

        return [
            'company_id' => ['nullable', 'integer', Rule::exists('companies', 'id')->where('team_id', $contact->team_id)],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email:rfc', 'max:191'],
            'phone' => ['nullable', 'string', 'max:30'],
            'mobile' => ['nullable', 'string', 'max:30'],
            'job_title' => ['nullable', 'string', 'max:120'],
            'department' => ['nullable', 'string', 'max:100'],
            'is_primary_decision_maker' => ['boolean'],
            'do_not_contact' => ['boolean'],
            'lead_source' => ['nullable', 'string', 'max:80'],
        ];
    }
}
