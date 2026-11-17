<?php

declare(strict_types=1);

namespace App\Data\Leads;

use Illuminate\Foundation\Http\FormRequest;

final readonly class LeadConversionData
{
    public function __construct(
        public string $companyName,
        public string $dealTitle,
        public int $pipelineId,
        public int $pipelineStageId,
        public float $expectedValue,
        public string $currency,
        public ?string $expectedCloseDate,
    ) {}

    public static function fromRequest(FormRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            companyName: (string) $validated['company_name'],
            dealTitle: (string) $validated['deal_title'],
            pipelineId: (int) $validated['pipeline_id'],
            pipelineStageId: (int) $validated['pipeline_stage_id'],
            expectedValue: (float) ($validated['expected_value'] ?? 0),
            currency: (string) ($validated['currency'] ?? config('crm.currency', 'BDT')),
            expectedCloseDate: $validated['expected_close_date'] ?? null,
        );
    }
}
