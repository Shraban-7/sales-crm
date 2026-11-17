<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\DealStatus;
use App\Enums\FollowUpStatus;
use App\Enums\TaskStatus;
use App\Models\Deal;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $openDealsQuery = Deal::query()->visibleTo($user)->where('status', DealStatus::Open);
        $openPipelineValue = (float) (clone $openDealsQuery)->sum('value');
        $openDealsCount = (clone $openDealsQuery)->count();
        $wonRevenue = (float) Deal::query()->visibleTo($user)->where('status', DealStatus::Won)->sum('value');
        $activeLeadsCount = Lead::query()->visibleTo($user)->whereNotIn('status', ['converted', 'disqualified'])->count();

        $dueTasks = Task::query()
            ->with(['taskable', 'assignee'])
            ->visibleTo($user)
            ->whereIn('status', [TaskStatus::Pending->value, TaskStatus::InProgress->value])
            ->orderBy('due_at')
            ->limit(6)
            ->get();

        $upcomingFollowUps = FollowUp::query()
            ->with(['subject', 'owner'])
            ->visibleTo($user)
            ->where('status', FollowUpStatus::Scheduled->value)
            ->orderBy('scheduled_at')
            ->limit(6)
            ->get();

        $recentDeals = Deal::query()
            ->with(['company', 'stage', 'owner'])
            ->visibleTo($user)
            ->latest('updated_at')
            ->limit(6)
            ->get();

        return view('pages.dashboard', compact(
            'openPipelineValue',
            'openDealsCount',
            'wonRevenue',
            'activeLeadsCount',
            'dueTasks',
            'upcomingFollowUps',
            'recentDeals',
        ));
    }
}
