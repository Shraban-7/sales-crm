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

        $openPipelineByCurrency = Deal::query()
            ->visibleTo($user)
            ->where('status', DealStatus::Open)
            ->groupBy('currency')
            ->orderBy('currency')
            ->selectRaw('currency, COUNT(*) as deal_count, SUM(value) as total_value')
            ->get();

        $wonRevenueByCurrency = Deal::query()
            ->visibleTo($user)
            ->where('status', DealStatus::Won)
            ->groupBy('currency')
            ->orderBy('currency')
            ->selectRaw('currency, COUNT(*) as deal_count, SUM(value) as total_value')
            ->get();

        $activeLeadsCount = Lead::query()
            ->visibleTo($user)
            ->whereNotIn('status', ['converted', 'disqualified'])
            ->count();

        $pendingTasksQuery = Task::query()
            ->visibleTo($user)
            ->whereIn('status', [TaskStatus::Pending->value, TaskStatus::InProgress->value]);

        $pendingTasksCount = (clone $pendingTasksQuery)->count();
        $overdueTasksCount = (clone $pendingTasksQuery)
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->count();

        $dueTasks = (clone $pendingTasksQuery)
            ->with(['taskable', 'assignee'])
            ->orderBy('due_at')
            ->limit(6)
            ->get();

        $scheduledFollowUpsQuery = FollowUp::query()
            ->visibleTo($user)
            ->where('status', FollowUpStatus::Scheduled->value);

        $scheduledFollowUpsCount = (clone $scheduledFollowUpsQuery)->count();

        $upcomingFollowUps = (clone $scheduledFollowUpsQuery)
            ->with(['subject', 'owner'])
            ->orderBy('scheduled_at')
            ->limit(6)
            ->get();

        $recentDeals = Deal::query()
            ->with(['company', 'stage', 'owner'])
            ->visibleTo($user)
            ->latest('updated_at')
            ->limit(6)
            ->get();

        return view('pages.dashboard', [
            'openPipelineByCurrency' => $openPipelineByCurrency,
            'openDealsCount' => (int) $openPipelineByCurrency->sum('deal_count'),
            'wonRevenueByCurrency' => $wonRevenueByCurrency,
            'wonDealsCount' => (int) $wonRevenueByCurrency->sum('deal_count'),
            'activeLeadsCount' => $activeLeadsCount,
            'pendingTasksCount' => $pendingTasksCount,
            'overdueTasksCount' => $overdueTasksCount,
            'dueTasks' => $dueTasks,
            'scheduledFollowUpsCount' => $scheduledFollowUpsCount,
            'upcomingFollowUps' => $upcomingFollowUps,
            'recentDeals' => $recentDeals,
        ]);
    }
}
