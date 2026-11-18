<?php

declare(strict_types=1);

namespace App\Actions\Leads;

use App\Data\Leads\LeadConversionData;
use App\Enums\DealStatus;
use App\Enums\LeadStatus;
use App\Events\LeadConverted;
use App\Exceptions\DomainRuleException;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\DealStageHistory;
use App\Models\Lead;
use App\Models\PipelineStage;
use App\Models\User;
use App\Services\Audit\AuditLoggerService;
use Illuminate\Support\Facades\DB;

final readonly class ConvertLeadToDealAction
{
    public function __construct(
        private AuditLoggerService $auditLogger,
    ) {}

    public function handle(Lead $lead, LeadConversionData $data, User $actor): Deal
    {
        return DB::transaction(function () use ($lead, $data, $actor): Deal {
            /** @var Lead $lockedLead */
            $lockedLead = Lead::query()->lockForUpdate()->findOrFail($lead->id);

            if ($lockedLead->status === LeadStatus::Converted) {
                throw new DomainRuleException('This lead has already been converted.');
            }

            if ($lockedLead->status === LeadStatus::Disqualified) {
                throw new DomainRuleException('Disqualified leads cannot be converted. Re-qualify the lead first.', 422);
            }

            $stage = PipelineStage::query()
                ->where('pipeline_id', $data->pipelineId)
                ->findOrFail($data->pipelineStageId);

            $company = Company::query()
                ->where('team_id', $lockedLead->team_id)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($data->companyName))])
                ->first();

            if ($company === null) {
                $company = Company::query()->create([
                    'team_id' => $lockedLead->team_id,
                    'owner_id' => $lockedLead->owner_id,
                    'name' => trim($data->companyName),
                    'website' => $lockedLead->website,
                    'phone' => $lockedLead->phone,
                    'country' => config('crm.country', 'BD'),
                ]);
            }

            $contact = null;
            if ($lockedLead->email !== null) {
                $contact = Contact::query()
                    ->where('team_id', $lockedLead->team_id)
                    ->whereRaw('LOWER(email) = ?', [mb_strtolower($lockedLead->email)])
                    ->first();
            }

            if ($contact === null) {
                $contact = Contact::query()->create([
                    'team_id' => $lockedLead->team_id,
                    'owner_id' => $lockedLead->owner_id,
                    'company_id' => $company->id,
                    'first_name' => $lockedLead->first_name,
                    'last_name' => $lockedLead->last_name ?? '-',
                    'email' => $lockedLead->email,
                    'phone' => $lockedLead->phone,
                    'job_title' => $lockedLead->job_title,
                    'is_primary_decision_maker' => true,
                    'lead_source' => $lockedLead->source?->value,
                ]);
            } else {
                $contact->update([
                    'company_id' => $contact->company_id ?? $company->id,
                    'phone' => $contact->phone ?? $lockedLead->phone,
                    'job_title' => $contact->job_title ?? $lockedLead->job_title,
                ]);
            }

            $deal = Deal::query()->create([
                'team_id' => $lockedLead->team_id,
                'owner_id' => $lockedLead->owner_id,
                'company_id' => $company->id,
                'primary_contact_id' => $contact->id,
                'pipeline_id' => $data->pipelineId,
                'pipeline_stage_id' => $stage->id,
                'title' => $data->dealTitle,
                'value' => $data->expectedValue,
                'currency' => $data->currency,
                'probability' => $stage->default_probability,
                'status' => DealStatus::Open,
                'expected_close_date' => $data->expectedCloseDate,
                'stage_changed_at' => now(),
                'last_activity_at' => now(),
            ]);

            $deal->contacts()->syncWithoutDetaching([
                $contact->id => ['role' => 'Primary Contact', 'is_primary' => true],
            ]);

            DealStageHistory::query()->create([
                'deal_id' => $deal->id,
                'from_stage_id' => null,
                'to_stage_id' => $stage->id,
                'moved_by_id' => $actor->id,
                'duration_in_previous_stage_seconds' => 0,
                'notes' => "Converted from Lead #{$lockedLead->id}",
                'created_at' => now(),
            ]);

            $lockedLead->update([
                'status' => LeadStatus::Converted,
                'converted_at' => now(),
                'converted_by_id' => $actor->id,
                'converted_company_id' => $company->id,
                'converted_contact_id' => $contact->id,
                'converted_deal_id' => $deal->id,
            ]);

            // Copy/re-link open tasks & follow-ups to the new Deal so context is preserved
            $lockedLead->activities()->update([
                'subject_type' => 'deal',
                'subject_id' => $deal->id,
            ]);
            $lockedLead->tasks()->update([
                'taskable_type' => 'deal',
                'taskable_id' => $deal->id,
            ]);
            $lockedLead->followUps()->update([
                'subject_type' => 'deal',
                'subject_id' => $deal->id,
            ]);

            $this->auditLogger->log(
                event: 'converted',
                auditable: $lockedLead,
                actor: $actor,
                oldValues: ['status' => $lead->status?->value],
                newValues: [
                    'status' => LeadStatus::Converted->value,
                    'converted_company_id' => $company->id,
                    'converted_contact_id' => $contact->id,
                    'converted_deal_id' => $deal->id,
                ],
                teamId: $lockedLead->team_id,
            );

            event(new LeadConverted($lockedLead, $deal, $actor));

            return $deal;
        });
    }
}
