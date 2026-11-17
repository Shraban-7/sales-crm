<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pipelines;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pipelines\StorePipelineRequest;
use App\Models\Deal;
use App\Models\Pipeline;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class PipelineController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Pipeline::class);

        $pipelines = Pipeline::query()
            ->with('stages')
            ->withCount('deals')
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $teams = Team::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('pages.pipelines.index', compact('pipelines', 'teams'));
    }

    public function store(StorePipelineRequest $request): RedirectResponse
    {
        $this->authorize('create', Pipeline::class);

        $pipeline = DB::transaction(function () use ($request): Pipeline {
            if ($request->boolean('is_default')) {
                Pipeline::query()->update(['is_default' => false]);
            }

            /** @var Pipeline $pipeline */
            $pipeline = Pipeline::query()->create([
                'name' => $request->validated('name'),
                'team_id' => $request->validated('team_id'),
                'is_default' => $request->boolean('is_default'),
                'is_active' => true,
            ]);

            $pipeline->stages()->createMany([
                ['name' => 'Qualification', 'slug' => 'qualification', 'position' => 1, 'default_probability' => 25.00, 'rotting_days' => 14, 'color' => 'blue'],
                ['name' => 'Proposal Sent', 'slug' => 'proposal-sent', 'position' => 2, 'default_probability' => 60.00, 'rotting_days' => 10, 'color' => 'amber'],
                ['name' => 'Closed Won', 'slug' => 'closed-won', 'position' => 3, 'default_probability' => 100.00, 'rotting_days' => 365, 'is_won_stage' => true, 'color' => 'emerald'],
                ['name' => 'Closed Lost', 'slug' => 'closed-lost', 'position' => 4, 'default_probability' => 0.00, 'rotting_days' => 365, 'is_lost_stage' => true, 'color' => 'rose'],
            ]);

            return $pipeline;
        });

        return redirect()->route('pipelines.kanban', $pipeline)->with('status', 'Pipeline created.');
    }

    public function kanban(Request $request, ?Pipeline $pipeline = null): View
    {
        $this->authorize('viewAny', Pipeline::class);

        $pipeline ??= Pipeline::query()->where('is_default', true)->first()
            ?? Pipeline::query()->firstOrFail();

        $pipeline->load('stages');

        $deals = Deal::query()
            ->with(['company', 'owner', 'primaryContact', 'stage'])
            ->visibleTo($request->user())
            ->where('pipeline_id', $pipeline->id)
            ->orderByDesc('updated_at')
            ->get()
            ->groupBy('pipeline_stage_id');

        $pipelines = Pipeline::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('pages.pipelines.kanban', compact('pipeline', 'deals', 'pipelines'));
    }
}
