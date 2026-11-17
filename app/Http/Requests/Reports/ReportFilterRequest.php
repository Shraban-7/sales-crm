<?php

declare(strict_types=1);

namespace App\Http\Requests\Reports;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

final class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! ($user->can('reports.view.own') || $user->can('reports.view.team') || $user->can('reports.view.all'))) {
            return false;
        }

        if ($user->hasRole('Sales Rep')) {
            if ($this->filled('owner_id') && (int) $this->input('owner_id') !== $user->id) {
                return false;
            }
            if ($this->filled('team_id') && (int) $this->input('team_id') !== (int) $user->team_id) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'pipeline_id' => ['nullable', 'integer', 'exists:pipelines,id'],
            'team_id' => ['nullable', 'integer', 'exists:teams,id'],
            'owner_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->filled('date_from') && $this->filled('date_to')) {
                $from = Carbon::parse((string) $this->input('date_from'));
                $to = Carbon::parse((string) $this->input('date_to'));
                $maxDays = (int) config('crm.reports.max_date_range_days', 366);

                if ($from->diffInDays($to) > $maxDays) {
                    $validator->errors()->add('date_to', "Date range cannot exceed {$maxDays} days.");
                }
            }
        });
    }
}
