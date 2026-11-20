<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Models\Proposal;
use App\Models\Quotation;
use App\Models\Task;
use App\Models\User;
use App\Services\Reports\AnalyticsService;

final class DashboardService
{
    public function __construct(
        private AnalyticsService $analytics,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getDashboardData(User $user): array
    {
        $dateFrom = now()->startOfMonth();
        $dateTo = now()->endOfMonth();

        $analyticsSummary = $this->analytics->getSummary($user, $dateFrom, $dateTo);

        $tasks = Task::query()
            ->visibleTo($user)
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('due_at')
            ->limit(5)
            ->get();

        $proposals = Proposal::query()
            ->visibleTo($user)
            ->latest()
            ->limit(5)
            ->get();

        $quotations = Quotation::query()
            ->visibleTo($user)
            ->latest()
            ->limit(5)
            ->get();

        return [
            'kpis' => $analyticsSummary['kpis'],
            'currency_totals' => $analyticsSummary['currency_totals'],
            'stage_breakdown' => $analyticsSummary['stage_breakdown'],
            'lead_sources' => $analyticsSummary['lead_sources'],
            'leaderboard' => $analyticsSummary['leaderboard'],
            'recent_tasks' => $tasks,
            'recent_proposals' => $proposals,
            'recent_quotations' => $quotations,
        ];
    }
}
