<?php

declare(strict_types=1);

namespace App\Actions\Deals;

use App\Enums\DealStatus;
use App\Enums\FollowUpStatus;
use App\Enums\WebhookEvent;
use App\Exceptions\DomainRuleException;
use App\Models\Deal;
use App\Models\DealStageHistory;
use App\Models\PipelineStage;
use App\Models\User;
use App\Services\Audit\AuditLoggerService;
use App\Services\Webhooks\WebhookDispatcherService;
use Illuminate\Support\Facades\DB;

final readonly class MoveDealStageAction
{
    public function __construct(
        private AuditLoggerService $auditLogger,
        private WebhookDispatcherService $webhooks,
    ) {}

    public function handle(
        Deal $deal,
        PipelineStage $targetStage,
        User $actor,
        ?string $lostReason = null,
        ?string $lostCompetitor = null,
        ?string $wonNotes = null,
        ?string $notes = null,
    ): Deal {
        return DB::transaction(function () use ($deal, $targetStage, $actor, $lostReason, $lostCompetitor, $wonNotes, $notes): Deal {
            /** @var Deal $lockedDeal */
            $lockedDeal = Deal::query()->lockForUpdate()->findOrFail($deal->id);

            if ((int) $targetStage->pipeline_id !== (int) $lockedDeal->pipeline_id) {
                throw new DomainRuleException('Target stage does not belong to the deal pipeline.', 422);
            }

            if ($targetStage->is_lost_stage && trim((string) $lostReason) === '') {
                throw new DomainRuleException('A lost reason is required when moving a deal to a Closed Lost stage.', 422);
            }

            $previousStageId = $lockedDeal->pipeline_stage_id;
            $previousStatus = $lockedDeal->status;
            $durationSeconds = $lockedDeal->stage_changed_at !== null
                ? (int) max(0, $lockedDeal->stage_changed_at->diffInSeconds(now()))
                : 0;

            $status = DealStatus::Open;
            $probability = $targetStage->default_probability;
            $closedAt = null;

            if ($targetStage->is_won_stage) {
                $status = DealStatus::Won;
                $probability = 100.00;
                $closedAt = now();
            } elseif ($targetStage->is_lost_stage) {
                $status = DealStatus::Lost;
                $probability = 0.00;
                $closedAt = now();
            }

            $lockedDeal->update([
                'pipeline_stage_id' => $targetStage->id,
                'probability' => $probability,
                'status' => $status,
                'closed_at' => $closedAt,
                'won_notes' => $targetStage->is_won_stage ? ($wonNotes ?? $lockedDeal->won_notes) : $lockedDeal->won_notes,
                'lost_reason' => $targetStage->is_lost_stage ? $lostReason : null,
                'lost_competitor' => $targetStage->is_lost_stage ? $lostCompetitor : null,
                'stage_changed_at' => now(),
                'last_activity_at' => now(),
            ]);

            if ($targetStage->is_lost_stage) {
                $lockedDeal->followUps()
                    ->where('status', FollowUpStatus::Scheduled)
                    ->update(['status' => FollowUpStatus::Cancelled]);
            }

            DealStageHistory::query()->create([
                'deal_id' => $lockedDeal->id,
                'from_stage_id' => $previousStageId,
                'to_stage_id' => $targetStage->id,
                'moved_by_id' => $actor->id,
                'duration_in_previous_stage_seconds' => $durationSeconds,
                'notes' => $notes,
                'created_at' => now(),
            ]);

            $this->auditLogger->log(
                event: 'stage_moved',
                auditable: $lockedDeal,
                actor: $actor,
                oldValues: [
                    'pipeline_stage_id' => $previousStageId,
                    'status' => $previousStatus?->value,
                ],
                newValues: [
                    'pipeline_stage_id' => $targetStage->id,
                    'status' => $status->value,
                ],
                teamId: $lockedDeal->team_id,
            );

            $freshDeal = $lockedDeal->fresh(['stage', 'pipeline', 'company']);

            DB::afterCommit(function () use ($freshDeal, $previousStageId, $targetStage, $status): void {
                $payload = [
                    'deal_id' => $freshDeal->id,
                    'title' => $freshDeal->title,
                    'from_stage_id' => $previousStageId,
                    'to_stage_id' => $targetStage->id,
                    'stage_name' => $targetStage->name,
                    'status' => $status->value,
                    'value' => (float) $freshDeal->value,
                    'currency' => $freshDeal->currency,
                ];

                $this->webhooks->dispatch(WebhookEvent::DealStageMoved, $payload, $freshDeal->team_id);

                if ($status === DealStatus::Won) {
                    $this->webhooks->dispatch(WebhookEvent::DealWon, $payload, $freshDeal->team_id);
                } elseif ($status === DealStatus::Lost) {
                    $this->webhooks->dispatch(WebhookEvent::DealLost, $payload, $freshDeal->team_id);
                }
            });

            return $freshDeal;
        });
    }
}
