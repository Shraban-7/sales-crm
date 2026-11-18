<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\ReportFilterRequest;
use App\Models\Pipeline;
use App\Models\Team;
use App\Models\User;
use App\Services\Reports\AnalyticsService;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

final class ReportController extends Controller
{
    public function index(ReportFilterRequest $request, AnalyticsService $analytics): View
    {
        $validated = $request->validated();
        $dateFrom = isset($validated['date_from'])
            ? Carbon::parse((string) $validated['date_from'])
            : now()->subDays(90);
        $dateTo = isset($validated['date_to'])
            ? Carbon::parse((string) $validated['date_to'])
            : now();

        $pipelineId = isset($validated['pipeline_id']) ? (int) $validated['pipeline_id'] : null;
        $teamId = isset($validated['team_id']) ? (int) $validated['team_id'] : null;
        $ownerId = isset($validated['owner_id']) ? (int) $validated['owner_id'] : null;

        $summary = $analytics->getSummary(
            user: $request->user(),
            dateFrom: $dateFrom,
            dateTo: $dateTo,
            pipelineId: $pipelineId,
            teamId: $teamId,
            ownerId: $ownerId,
        );

        $user = $request->user();
        $canViewAll = $user->can('reports.view.all');
        $canViewTeam = $canViewAll || $user->can('reports.view.team');

        $pipelines = Pipeline::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);

        // Only offer filters the request authorization will accept. Listing
        // every team/owner made reps and managers hit a guaranteed 403.
        $teams = Team::query()
            ->where('is_active', true)
            ->when(! $canViewAll, fn ($q) => $q->whereKey($user->team_id))
            ->orderBy('name')
            ->get(['id', 'name']);

        $owners = User::query()
            ->where('is_active', true)
            ->when(! $canViewAll, fn ($q) => $q->where('team_id', $user->team_id))
            ->when(! $canViewTeam, fn ($q) => $q->whereKey($user->id))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('pages.reports.index', compact(
            'summary',
            'dateFrom',
            'dateTo',
            'pipelineId',
            'teamId',
            'ownerId',
            'pipelines',
            'teams',
            'owners',
        ));
    }
}
