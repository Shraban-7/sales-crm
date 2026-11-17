<?php

declare(strict_types=1);

namespace App\Actions\Pipelines;

use App\Models\Pipeline;
use App\Models\PipelineStage;
use Illuminate\Support\Facades\DB;

final readonly class ReorderPipelineStagesAction
{
    /**
     * @param  list<int>  $orderedStageIds
     */
    public function handle(Pipeline $pipeline, array $orderedStageIds): void
    {
        DB::transaction(function () use ($pipeline, $orderedStageIds): void {
            // Pass 1: move to temporary high positions to avoid unique (pipeline_id, position) collisions
            foreach ($orderedStageIds as $index => $stageId) {
                PipelineStage::query()
                    ->where('pipeline_id', $pipeline->id)
                    ->where('id', $stageId)
                    ->update(['position' => 10000 + $index]);
            }

            // Pass 2: assign final 1-based positions
            foreach ($orderedStageIds as $index => $stageId) {
                PipelineStage::query()
                    ->where('pipeline_id', $pipeline->id)
                    ->where('id', $stageId)
                    ->update(['position' => $index + 1]);
            }
        });
    }
}
