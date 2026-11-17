<?php

declare(strict_types=1);

namespace App\Http\Requests\FollowUps;

use App\Enums\FollowUpChannel;
use App\Models\Contact;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class StoreFollowUpRequest extends FormRequest
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
            'channel' => ['required', Rule::enum(FollowUpChannel::class)],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'objective' => ['required', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->input('subject_type') === 'contact') {
                $contact = Contact::query()->find((int) $this->input('subject_id'));
                if ($contact !== null && $contact->do_not_contact) {
                    $validator->errors()->add('subject_id', 'Cannot schedule outbound follow-up for a contact marked Do Not Contact.');
                }
            }
        });
    }
}
