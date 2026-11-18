<?php

declare(strict_types=1);

namespace App\Services\Reports;

use App\Enums\DealStatus;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

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
            'crm_analytics:v3:%d:%s:%s:%s:%s:%s',
            $user->id,
            $dateFrom->toDateString(),
            $dateTo->toDateString(),
            (string) ($pipelineId ?? 'all'),
            (string) ($teamId ?? 'all'),
            (string) ($ownerId ?? 'all'),
        );

        $ttl = (int) config('crm.reports.cache_ttl_seconds', 300);

        return Cache::remember($cacheKey, $ttl, function () use ($user, $dateFrom, $dateTo, $pipelineId, $teamId, $ownerId): array {
            $rangeStart = $dateFrom->copy()->startOfDay();
            $rangeEnd = $dateTo->copy()->endOfDay();

            $dealBase = Deal::query()
                ->visibleTo($user)
                ->when($pipelineId !== null, fn ($q) => $q->where('deals.pipeline_id', $pipelineId))
                ->when($teamId !== null, fn ($q) => $q->where('deals.team_id', $teamId))
                ->when($ownerId !== null, fn ($q) => $q->where('deals.owner_id', $ownerId));

            // Open pipeline uses expected close date, won/lost use the actual close date.
            $openWindow = function ($query) use ($rangeStart, $rangeEnd): void {
                $query->where('deals.status', DealStatus::Open->value)
                    ->where(function ($sub) use ($rangeStart, $rangeEnd): void {
                        $sub->whereBetween('deals.expected_close_date', [$rangeStart->toDateString(), $rangeEnd->toDateString()])
                            ->orWhereNull('deals.expected_close_date');
                    });
            };

            $closedWindow = function ($query) use ($rangeStart, $rangeEnd): void {
                $query->whereIn('deals.status', [DealStatus::Won->value, DealStatus::Lost->value])
                    ->whereBetween('deals.closed_at', [$rangeStart, $rangeEnd]);
            };

            $currencyTotals = (clone $dealBase)
                ->where(function ($query) use ($openWindow, $closedWindow): void {
                    $query->where(function ($sub) use ($openWindow): void {
                        $openWindow($sub);
                    })->orWhere(function ($sub) use ($closedWindow): void {
                        $closedWindow($sub);
                    });
                })
                ->groupBy('deals.currency')
                ->orderBy('deals.currency')
                ->selectRaw("
                    deals.currency,
                    SUM(CASE WHEN deals.status = 'open' THEN 1 ELSE 0 END) as open_count,
                    SUM(CASE WHEN deals.status = 'open' THEN deals.value ELSE 0 END) as open_value,
                    SUM(CASE WHEN deals.status = 'open' THEN ROUND(deals.value * (deals.probability / 100), 2) ELSE 0 END) as forecast_value,
                    SUM(CASE WHEN deals.status = 'won' THEN 1 ELSE 0 END) as won_count,
                    SUM(CASE WHEN deals.status = 'won' THEN deals.value ELSE 0 END) as won_value,
                    SUM(CASE WHEN deals.status = 'lost' THEN 1 ELSE 0 END) as lost_count,
                    COUNT(*) as total_deals
                ")
                ->toBase()
                ->get();

            $wonCount = (int) $currencyTotals->sum('won_count');
            $lostCount = (int) $currencyTotals->sum('lost_count');
            $openCount = (int) $currencyTotals->sum('open_count');
            $closedTotal = $wonCount + $lostCount;
            $winRate = $closedTotal > 0 ? round(($wonCount / $closedTotal) * 100, 2) : 0.00;

            $stageBreakdown = (clone $dealBase)
                ->where(function ($query) use ($openWindow): void {
                    $openWindow($query);
                })
                ->join('pipeline_stages', 'deals.pipeline_stage_id', '=', 'pipeline_stages.id')
                ->join('pipelines', 'deals.pipeline_id', '=', 'pipelines.id')
                ->groupBy('pipeline_stages.id', 'pipeline_stages.name', 'pipeline_stages.position', 'pipeline_stages.color', 'pipelines.name', 'deals.currency')
                ->orderBy('pipelines.name')
                ->orderBy('pipeline_stages.position')
                ->selectRaw('
                    pipeline_stages.id,
                    pipeline_stages.name,
                    pipeline_stages.color,
                    pipelines.name as pipeline_name,
                    deals.currency,
                    COUNT(deals.id) as deal_count,
                    SUM(deals.value) as total_value,
                    SUM(ROUND(deals.value * (deals.probability / 100), 2)) as stage_weighted_value
                ')
                ->toBase()
                ->get();

            $leadBase = Lead::query()
                ->visibleTo($user)
                ->whereBetween('leads.created_at', [$rangeStart, $rangeEnd])
                ->when($teamId !== null, fn ($q) => $q->where('leads.team_id', $teamId))
                ->when($ownerId !== null, fn ($q) => $q->where('leads.owner_id', $ownerId));

            $leadSources = (clone $leadBase)
                ->groupBy('source')
                ->orderByDesc('total_leads')
                ->selectRaw("
                    source,
                    COUNT(*) as total_leads,
                    SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as converted_leads
                ")
                ->toBase()
                ->get();

            $totalLeads = (int) $leadSources->sum('total_leads');
            $convertedLeads = (int) $leadSources->sum('converted_leads');

            $leaderboard = (clone $dealBase)
                ->join('users', 'deals.owner_id', '=', 'users.id')
                ->where('deals.status', DealStatus::Won->value)
                ->whereBetween('deals.closed_at', [$rangeStart, $rangeEnd])
                ->groupBy('users.id', 'users.name', 'deals.currency')
                ->orderByDesc('won_value')
                ->selectRaw('users.id, users.name, deals.currency, COUNT(deals.id) as won_deals, SUM(deals.value) as won_value')
                ->limit(20)
                ->toBase()
                ->get();

            // Cache plain arrays only. Serialized Eloquent models cannot be
            // reliably restored from the database cache and surface as
            // __PHP_Incomplete_Class instances in the view.
            $currencyRows = $currencyTotals->map(fn ($row): array => [
                'currency' => (string) $row->currency,
                'open_count' => (int) $row->open_count,
                'open_value' => (float) $row->open_value,
                'weighted_value' => (float) $row->forecast_value,
                'won_count' => (int) $row->won_count,
                'won_value' => (float) $row->won_value,
                'lost_count' => (int) $row->lost_count,
                'total_deals' => (int) $row->total_deals,
            ])->all();

            $stageRows = $stageBreakdown->map(fn ($row): array => [
                'id' => (int) $row->id,
                'name' => (string) $row->name,
                'pipeline_name' => (string) $row->pipeline_name,
                'color' => (string) $row->color,
                'currency' => (string) $row->currency,
                'deal_count' => (int) $row->deal_count,
                'total_value' => (float) $row->total_value,
                'weighted_value' => (float) $row->stage_weighted_value,
            ])->all();

            $sourceRows = $leadSources->map(fn ($row): array => [
                'source' => (string) $row->source,
                'total_leads' => (int) $row->total_leads,
                'converted_leads' => (int) $row->converted_leads,
            ])->all();

            $leaderboardRows = $leaderboard->map(fn ($row): array => [
                'id' => (int) $row->id,
                'name' => (string) $row->name,
                'currency' => (string) $row->currency,
                'won_deals' => (int) $row->won_deals,
                'won_value' => (float) $row->won_value,
            ])->all();

            return [
                'kpis' => [
                    'total_deals' => (int) $currencyTotals->sum('total_deals'),
                    'open_count' => $openCount,
                    'won_count' => $wonCount,
                    'lost_count' => $lostCount,
                    'win_rate' => $winRate,
                    'total_leads' => $totalLeads,
                    'converted_leads' => $convertedLeads,
                    'lead_conversion_rate' => $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 2) : 0.00,
                ],
                'currency_totals' => $currencyRows,
                'stage_breakdown' => $stageRows,
                'lead_sources' => $sourceRows,
                'leaderboard' => $leaderboardRows,
            ];
        });
    }
}
