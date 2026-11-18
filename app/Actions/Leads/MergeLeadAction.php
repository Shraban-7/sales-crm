<?php

declare(strict_types=1);

namespace App\Actions\Leads;

use App\Exceptions\DomainRuleException;
use App\Models\Lead;
use App\Models\User;
use App\Services\Audit\AuditLoggerService;
use Illuminate\Support\Facades\DB;

final readonly class MergeLeadAction
{
    public function __construct(
        private AuditLoggerService $auditLogger,
    ) {}

    public function handle(Lead $primary, Lead $duplicate, User $actor): Lead
    {
        if ((int) $primary->id === (int) $duplicate->id) {
            throw new DomainRuleException('A lead cannot be merged into itself.', 422);
        }

        if ((int) $primary->team_id !== (int) $duplicate->team_id) {
            throw new DomainRuleException('Leads from different teams cannot be merged.', 422);
        }

        if ($primary->isConverted() || $duplicate->isConverted()) {
            throw new DomainRuleException('Converted leads cannot be merged.', 422);
        }

        return DB::transaction(function () use ($primary, $duplicate, $actor): Lead {
            $primary->update([
                'last_name' => $primary->last_name ?? $duplicate->last_name,
                'company_name' => $primary->company_name ?? $duplicate->company_name,
                'job_title' => $primary->job_title ?? $duplicate->job_title,
                'email' => $primary->email ?? $duplicate->email,
                'phone' => $primary->phone ?? $duplicate->phone,
                'website' => $primary->website ?? $duplicate->website,
                'estimated_value' => $primary->estimated_value ?? $duplicate->estimated_value,
                'score' => max((int) $primary->score, (int) $duplicate->score),
            ]);

            $duplicate->activities()->update([
                'subject_type' => $primary->getMorphClass(),
                'subject_id' => $primary->id,
            ]);
            $duplicate->tasks()->update([
                'taskable_type' => $primary->getMorphClass(),
                'taskable_id' => $primary->id,
            ]);
            $duplicate->followUps()->update([
                'subject_type' => $primary->getMorphClass(),
                'subject_id' => $primary->id,
            ]);
            $duplicate->attachments()->update([
                'attachable_type' => $primary->getMorphClass(),
                'attachable_id' => $primary->id,
            ]);

            $this->auditLogger->log(
                event: 'merged',
                auditable: $primary,
                actor: $actor,
                oldValues: ['duplicate_lead_id' => $duplicate->id],
                newValues: ['primary_lead_id' => $primary->id],
                teamId: $primary->team_id,
            );

            $duplicate->delete();

            return $primary->fresh();
        });
    }
}
