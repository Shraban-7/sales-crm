<?php

declare(strict_types=1);

namespace App\Http\Requests\Leads;

use App\Models\Lead;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class MergeLeadRequest extends FormRequest
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
        /** @var Lead $lead */
        $lead = $this->route('lead');

        return [
            'duplicate_lead_id' => [
                'required',
                'integer',
                Rule::exists('leads', 'id')->where('team_id', $lead->team_id),
                'different:'.$lead->id,
            ],
        ];
    }
}
