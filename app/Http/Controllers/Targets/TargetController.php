<?php

declare(strict_types=1);

namespace App\Http\Controllers\Targets;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Deal;
use App\Models\Target;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class TargetController extends Controller
{
    public function index(Request $request): View
    {
        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);

        $targets = Target::query()
            ->with(['team', 'user'])
            ->where('period_year', $year)
            ->where('period_month', $month)
            ->get();

        $commissions = Commission::query()
            ->with(['deal', 'user'])
            ->latest()
            ->paginate(15);

        return view('pages.targets.index', [
            'targets' => $targets,
            'commissions' => $commissions,
            'selectedYear' => $year,
            'selectedMonth' => $month,
            'teams' => Team::query()->where('is_active', true)->get(),
            'users' => User::query()->where('is_active', true)->get(),
            'deals' => Deal::query()->where('status', 'won')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'string', 'in:company,team,individual'],
            'team_id' => ['nullable', 'required_if:type,team', 'integer', 'exists:teams,id'],
            'user_id' => ['nullable', 'required_if:type,individual', 'integer', 'exists:users,id'],
            'target_amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'period_month' => ['required', 'integer', 'between:1,12'],
            'period_year' => ['required', 'integer', 'between:2020,2035'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        Target::query()->updateOrCreate(
            [
                'type' => $data['type'],
                'team_id' => $data['team_id'] ?? null,
                'user_id' => $data['user_id'] ?? null,
                'period_year' => (int) $data['period_year'],
                'period_month' => (int) $data['period_month'],
            ],
            [
                'target_amount' => $data['target_amount'],
                'currency' => strtoupper((string) $data['currency']),
                'notes' => $data['notes'] ?? null,
            ]
        );

        return back()->with('status', 'Target saved successfully.');
    }

    public function storeCommission(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'deal_id' => ['required', 'integer', 'exists:deals,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'deal_value' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:pending,approved,paid,cancelled'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        Commission::query()->create([
            'deal_id' => (int) $data['deal_id'],
            'user_id' => (int) $data['user_id'],
            'amount' => $data['amount'],
            'deal_value' => $data['deal_value'],
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
            'paid_at' => $data['status'] === 'paid' ? now() : null,
        ]);

        return back()->with('status', 'Commission recorded successfully.');
    }
}
