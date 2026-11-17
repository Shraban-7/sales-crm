<?php

declare(strict_types=1);

namespace App\Services\Reports;

use App\Enums\DealStatus;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class AnalyticsService
{
    /**
     * @return array<string, mixed>
     */
    public function getSummary(
        User $user,
        Carbon $dateFrom,
        Carbon $dateTo,
        ?int $pipelineId = null,
        ?int $teamId = null,
        ?int $ownerId = null,
    ): array {
        $cacheKey = sprintf(
            'crm_analytics:%d:%s:%s:%s:%s:%s',
            $user->id,
            $dateFrom->toDateString(),
            $dateTo->toDateString(),
            (string) ($pipelineId ?? 'all'),
            (string) ($teamId ?? 'all'),
            (string) ($ownerId ?? 'all'),
        );

        $ttl = (int) config('crm.reports.cache_ttl_seconds', 300);

        return Cache::remember($cacheKey, $ttl, function () use ($user, $dateFrom, $dateTo, $pipelineId, $teamId, $ownerId): array {
            $dealBase = Deal::query()
                ->visibleTo($user)
                ->whereBetween('deals.created_at', [$dateFrom->copy()->startOfDay(), $dateTo->copy()->endOfDay()]);

            if ($pipelineId !== null) {
                $dealBase->where('deals.pipeline_id', $pipelineId);
            }
            if ($teamId !== null) {
                $dealBase->where('deals.team_id', $teamId);
            }
            if ($ownerId !== null) {
                $dealBase->where('deals.owner_id', $ownerId);
            }

            $kpis = (clone $dealBase)->selectRaw("
                COUNT(*) as total_deals,
                SUM(CASE WHEN status = 'open' THEN value ELSE 0 END) as open_pipeline_value,
                SUM(CASE WHEN status = 'open' THEN ROUND(value * (probability / 100), 2) ELSE 0 END) as weighted_forecast_value,
                SUM(CASE WHEN status = 'won' THEN value ELSE 0 END) as won_revenue,
                SUM(CASE WHEN status = 'won' THEN 1 ELSE 0 END) as won_count,
                SUM(CASE WHEN status = 'lost' THEN 1 ELSE 0 END) as lost_count
            ")->first();

            $wonCount = (int) ($kpis->won_count ?? 0);
            $lostCount = (int) ($kpis->lost_count ?? 0);
            $closedTotal = $wonCount + $lostCount;
            $winRate = $closedTotal > 0 ? round(($wonCount / $closedTotal) * 100, 2) : 0.00;

            $stageBreakdown = (clone $dealBase)
                ->join('pipeline_stages', 'deals.pipeline_stage_id', '=', 'pipeline_stages.id')
                ->groupBy('pipeline_stages.id', 'pipeline_stages.name', 'pipeline_stages.position', 'pipeline_stages.color')
                ->orderBy('pipeline_stages.position')
                ->selectRaw('
                    pipeline_stages.id,
                    pipeline_stages.name,
                    pipeline_stages.color,
                    COUNT(deals.id) as deal_count,
                    SUM(deals.value) as total_value,
                    SUM(ROUND(deals.value * (deals.probability / 100), 2)) as weighted_value
                ')
                ->get();

            $leadBase = Lead::query()
                ->visibleTo($user)
                ->whereBetween('leads.created_at', [$dateFrom->copy()->startOfDay(), $dateTo->copy()->endOfDay()]);

            if ($teamId !== null) {
                $leadBase->where('leads.team_id', $teamId);
            }
            if ($ownerId !== null) {
                $leadBase->where('leads.owner_id', $ownerId);
            }

            $leadSources = (clone $leadBase)
                ->groupBy('source')
                ->selectRaw("
                    source,
                    COUNT(*) as total_leads,
                    SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as converted_leads
                ")
                ->get();

            $leaderboard = (clone $dealBase)
                ->join('users', 'deals.owner_id', '=', 'users.id')
                ->where('deals.status', DealStatus::Won->value)
                ->groupBy('users.id', 'users.name')
                ->orderByDesc('won_value')
                ->selectRaw('users.id, users.name, COUNT(deals.id) as won_deals, SUM(deals.value) as won_value')
                ->limit(10)
                ->get();

            return [
                'kpis' => [
                    'total_deals' => (int) ($kpis->total_deals ?? 0),
                    'open_pipeline_value' => (float) ($kpis->open_pipeline_value ?? 0),
                    'weighted_forecast_value' => (float) ($kpis->weighted_forecast_value ?? 0),
                    'won_revenue' => (float) ($kpis->won_revenue ?? 0),
                    'won_count' => $wonCount,
                    'lost_count' => $lostCount,
                    'win_rate' => $winRate,
                ],
                'stage_breakdown' => $stageBreakdown,
                'lead_sources' => $leadSources,
                'leaderboard' => $leaderboard,
            ];
        });
    }
}
