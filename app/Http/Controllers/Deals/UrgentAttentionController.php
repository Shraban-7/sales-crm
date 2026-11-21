<?php

declare(strict_types=1);

namespace App\Http\Controllers\Deals;

use App\Enums\DealStatus;
use App\Enums\FollowUpStatus;
use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\FollowUp;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class UrgentAttentionController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        // 1. Stalled & At-risk deals
        $openDeals = Deal::query()
            ->with(['stage', 'company', 'primaryContact', 'owner'])
            ->visibleTo($user)
            ->where('status', DealStatus::Open->value)
            ->get();

        $atRiskDeals = [];
        foreach ($openDeals as $deal) {
            $reasons = [];

            if ($deal->expected_close_date && $deal->expected_close_date->isPast()) {
                $reasons[] = 'Past expected close date ('.($deal->expected_close_date->format('M d, Y')).')';
            }

            $rottingDays = $deal->stage?->rotting_days ?? 14;
            $reference = $deal->last_activity_at ?? $deal->stage_changed_at ?? $deal->updated_at ?? $deal->created_at;
            $daysSinceUpdate = $reference ? (int) $reference->diffInDays(now()) : 0;
            if ($daysSinceUpdate >= $rottingDays) {
                $stageName = $deal->stage?->name ?? 'Pipeline';
                $reasons[] = "Stalled in '{$stageName}' stage for {$daysSinceUpdate} days (threshold: {$rottingDays} days)";
            }

            if (! empty($reasons)) {
                $atRiskDeals[] = [
                    'deal' => $deal,
                    'reasons' => $reasons,
                    'days_stalled' => $daysSinceUpdate,
                ];
            }
        }

        // 2. Overdue follow-ups
        $overdueFollowUps = FollowUp::query()
            ->with(['owner', 'subject'])
            ->visibleTo($user)
            ->where(function ($q): void {
                $q->where('status', FollowUpStatus::Missed->value)
                    ->orWhere(function ($sub): void {
                        $sub->where('status', FollowUpStatus::Scheduled->value)
                            ->where('scheduled_at', '<', now());
                    });
            })
            ->orderBy('scheduled_at')
            ->limit(15)
            ->get();

        // 3. Overdue tasks
        $overdueTasks = Task::query()
            ->with(['assignee', 'taskable'])
            ->visibleTo($user)
            ->whereIn('status', [TaskStatus::Pending->value, TaskStatus::InProgress->value])
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->orderBy('due_at')
            ->limit(15)
            ->get();

        return view('pages.urgent-attention.index', [
            'atRiskDeals' => $atRiskDeals,
            'overdueFollowUps' => $overdueFollowUps,
            'overdueTasks' => $overdueTasks,
        ]);
    }
}
