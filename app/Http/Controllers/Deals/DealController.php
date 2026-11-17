<?php

declare(strict_types=1);

namespace App\Http\Controllers\Deals;

use App\Enums\DealStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Deals\StoreDealRequest;
use App\Http\Requests\Deals\UpdateDealRequest;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\DealStageHistory;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Services\Audit\AuditLoggerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class DealController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Deal::class);

        $status = $request->query('status');
        $search = trim((string) $request->query('search', ''));

        $deals = Deal::query()
            ->with(['company', 'stage', 'owner', 'primaryContact'])
            ->visibleTo($request->user())
            ->when(is_string($status) && $status !== '', fn ($q) => $q->where('status', $status))
            ->when($search !== '', fn ($q) => $q->where('title', 'like', "%{$search}%"))
            ->latest()
            ->paginate((int) config('crm.pagination.per_page', 20))
            ->withQueryString();

        $companies = Company::query()->visibleTo($request->user())->orderBy('name')->get(['id', 'name']);
        $contacts = Contact::query()->visibleTo($request->user())->orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        $pipelines = Pipeline::query()->with('stages')->where('is_active', true)->get();

        return view('pages.deals.index', compact('deals', 'companies', 'contacts', 'pipelines', 'status', 'search'));
    }

    public function store(StoreDealRequest $request): RedirectResponse
    {
        $this->authorize('create', Deal::class);

        $user = $request->user();
        $data = $request->validated();
        $stage = PipelineStage::query()->findOrFail((int) $data['pipeline_stage_id']);

        $deal = DB::transaction(function () use ($data, $user, $stage): Deal {
            $status = DealStatus::Open;
            $probability = $data['probability'] ?? $stage->default_probability;
            $closedAt = null;

            if ($stage->is_won_stage) {
                $status = DealStatus::Won;
                $probability = 100.00;
                $closedAt = now();
            } elseif ($stage->is_lost_stage) {
                $status = DealStatus::Lost;
                $probability = 0.00;
                $closedAt = now();
            }

            /** @var Deal $deal */
            $deal = Deal::query()->create([
                'team_id' => $user->team_id,
                'owner_id' => $user->id,
                'company_id' => $data['company_id'],
                'primary_contact_id' => $data['primary_contact_id'] ?? null,
                'pipeline_id' => $data['pipeline_id'],
                'pipeline_stage_id' => $stage->id,
                'title' => $data['title'],
                'value' => $data['value'],
                'currency' => $data['currency'],
                'probability' => $probability,
                'status' => $status,
                'expected_close_date' => $data['expected_close_date'] ?? null,
                'closed_at' => $closedAt,
                'stage_changed_at' => now(),
                'last_activity_at' => now(),
            ]);

            if (! empty($data['primary_contact_id'])) {
                $deal->contacts()->syncWithoutDetaching([
                    (int) $data['primary_contact_id'] => ['role' => 'Primary Contact', 'is_primary' => true],
                ]);
            }

            DealStageHistory::query()->create([
                'deal_id' => $deal->id,
                'from_stage_id' => null,
                'to_stage_id' => $stage->id,
                'moved_by_id' => $user->id,
                'duration_in_previous_stage_seconds' => 0,
                'notes' => 'Deal created',
                'created_at' => now(),
            ]);

            return $deal;
        });

        return redirect()->route('deals.show', $deal)->with('status', 'Deal created.');
    }

    public function show(Request $request, Deal $deal): View
    {
        $this->authorize('view', $deal);

        $deal->load([
            'company',
            'owner',
            'pipeline.stages',
            'stage',
            'primaryContact',
            'contacts',
            'stageHistories.fromStage',
            'stageHistories.toStage',
            'stageHistories.movedBy',
            'quotations.items',
            'activities.user',
            'tasks.assignee',
            'followUps.owner',
        ]);

        $companies = Company::query()->visibleTo($request->user())->orderBy('name')->get(['id', 'name']);
        $contacts = Contact::query()->visibleTo($request->user())->orderBy('first_name')->get(['id', 'first_name', 'last_name']);

        return view('pages.deals.show', compact('deal', 'companies', 'contacts'));
    }

    public function update(UpdateDealRequest $request, Deal $deal, AuditLoggerService $auditLogger): RedirectResponse
    {
        $this->authorize('update', $deal);

        $oldValues = $deal->only(['title', 'value', 'currency', 'probability', 'expected_close_date']);
        $deal->update($request->validated());

        $auditLogger->log(
            event: 'updated',
            auditable: $deal,
            actor: $request->user(),
            oldValues: $oldValues,
            newValues: $deal->only(['title', 'value', 'currency', 'probability', 'expected_close_date']),
            teamId: $deal->team_id,
        );

        return redirect()->route('deals.show', $deal)->with('status', 'Deal updated.');
    }

    public function destroy(Deal $deal): RedirectResponse
    {
        $this->authorize('delete', $deal);

        $deal->delete();

        return redirect()->route('deals.index')->with('status', 'Deal deleted.');
    }
}
