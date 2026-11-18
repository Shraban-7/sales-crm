<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pipelines;

use App\Actions\Pipelines\ReorderPipelineStagesAction;
use App\Exceptions\DomainRuleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pipelines\ReorderPipelineStagesRequest;
use App\Http\Requests\Pipelines\StorePipelineStageRequest;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class PipelineStageController extends Controller
{
    public function store(StorePipelineStageRequest $request, Pipeline $pipeline): RedirectResponse
    {
        $this->authorize('update', $pipeline);

        $maxPos = (int) $pipeline->stages()->max('position');
        $data = $request->validated();

        $pipeline->stages()->create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']).'-'.($maxPos + 1),
            'position' => $maxPos + 1,
            'default_probability' => $data['default_probability'],
            'rotting_days' => $data['rotting_days'],
            'color' => $data['color'],
            'is_won_stage' => $request->boolean('is_won_stage'),
            'is_lost_stage' => $request->boolean('is_lost_stage'),
        ]);

        return back()->with('status', 'Pipeline stage added.');
    }

    public function reorder(
        ReorderPipelineStagesRequest $request,
        Pipeline $pipeline,
        ReorderPipelineStagesAction $reorderAction,
    ): JsonResponse|RedirectResponse {
        $this->authorize('update', $pipeline);

        /** @var list<int> $stageIds */
        $stageIds = array_map('intval', $request->validated('stage_ids'));
        $reorderAction->handle($pipeline, $stageIds);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'reordered']);
        }

        return back()->with('status', 'Pipeline stages reordered.');
    }

    public function destroy(Request $request, Pipeline $pipeline, PipelineStage $stage): RedirectResponse
    {
        $this->authorize('update', $pipeline);

        if ((int) $stage->pipeline_id !== (int) $pipeline->id) {
            abort(404);
        }

        $hasDeals = $stage->deals()->exists();
        $reassignToId = $request->input('reassign_to_stage_id');

        if ($hasDeals && empty($reassignToId)) {
            throw new DomainRuleException('Stage has active deals. Provide a target reassignment stage.', 409);
        }

        DB::transaction(function () use ($stage, $pipeline, $reassignToId): void {
            if ($reassignToId !== null && $reassignToId !== '') {
                $target = PipelineStage::query()
                    ->where('pipeline_id', $pipeline->id)
                    ->where('id', '!=', $stage->id)
                    ->findOrFail((int) $reassignToId);

                $stage->deals()->update(['pipeline_stage_id' => $target->id]);
            }

            $stage->delete();
        });

        return back()->with('status', 'Pipeline stage deleted.');
    }
}
