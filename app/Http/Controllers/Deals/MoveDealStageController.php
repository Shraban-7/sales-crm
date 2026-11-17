<?php

declare(strict_types=1);

namespace App\Http\Controllers\Deals;

use App\Actions\Deals\MoveDealStageAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Deals\MoveDealStageRequest;
use App\Models\Deal;
use App\Models\PipelineStage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

final class MoveDealStageController extends Controller
{
    public function __invoke(
        MoveDealStageRequest $request,
        Deal $deal,
        MoveDealStageAction $moveAction,
    ): JsonResponse|RedirectResponse {
        $this->authorize('moveStage', $deal);

        $targetStage = PipelineStage::query()->findOrFail((int) $request->validated('pipeline_stage_id'));

        $updatedDeal = $moveAction->handle(
            deal: $deal,
            targetStage: $targetStage,
            actor: $request->user(),
            lostReason: $request->validated('lost_reason'),
            lostCompetitor: $request->validated('lost_competitor'),
            wonNotes: $request->validated('won_notes'),
            notes: $request->validated('notes'),
        );

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'moved',
                'deal' => [
                    'id' => $updatedDeal->id,
                    'status' => $updatedDeal->status->value,
                    'probability' => $updatedDeal->probability,
                    'stage_id' => $updatedDeal->pipeline_stage_id,
                    'stage_name' => $updatedDeal->stage->name,
                ],
            ]);
        }

        return back()->with('status', "Deal moved to {$updatedDeal->stage->name}.");
    }
}
