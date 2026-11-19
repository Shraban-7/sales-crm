<?php

declare(strict_types=1);

namespace App\Http\Requests\Webhooks;

use App\Enums\WebhookEvent;
use App\Models\Webhook;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Webhook::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'url' => ['required', 'url:http,https', 'max:500'],
            'team_id' => ['nullable', 'integer', 'exists:teams,id'],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['required', 'string', Rule::enum(WebhookEvent::class)],
            'secret' => ['nullable', 'string', 'min:16', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
