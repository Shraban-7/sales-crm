<?php

declare(strict_types=1);

namespace App\Http\Controllers\Leads;

use App\Http\Controllers\Controller;
use App\Http\Requests\Leads\StoreLeadRequest;
use App\Http\Requests\Leads\UpdateLeadRequest;
use App\Models\Lead;
use App\Models\Pipeline;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class LeadController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Lead::class);

        $status = $request->query('status');
        $search = trim((string) $request->query('search', ''));

        $leads = Lead::query()
            ->with('owner')
            ->visibleTo($request->user())
            ->when(is_string($status) && $status !== '', fn ($q) => $q->where('status', $status))
            ->when($search !== '', function ($q) use ($search): void {
                $q->where(function ($sub) use ($search): void {
                    $sub->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate((int) config('crm.pagination.per_page', 20))
            ->withQueryString();

        return view('pages.leads.index', compact('leads', 'status', 'search'));
    }

    public function store(StoreLeadRequest $request): RedirectResponse
    {
        $this->authorize('create', Lead::class);

        $user = $request->user();
        $lead = Lead::query()->create(array_merge($request->validated(), [
            'team_id' => $user->team_id,
            'owner_id' => $user->id,
            'score' => (int) ($request->validated('score') ?? 0),
        ]));

        return redirect()->route('leads.show', $lead)->with('status', 'Lead created.');
    }

    public function show(Lead $lead): View
    {
        $this->authorize('view', $lead);

        $lead->load([
            'owner',
            'convertedCompany',
            'convertedContact',
            'convertedDeal',
            'activities.user',
            'tasks.assignee',
            'followUps.owner',
        ]);

        $pipelines = Pipeline::query()
            ->with(['stages' => fn ($q) => $q->where('is_won_stage', false)->where('is_lost_stage', false)])
            ->where('is_active', true)
            ->get();

        return view('pages.leads.show', compact('lead', 'pipelines'));
    }

    public function update(UpdateLeadRequest $request, Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);

        $lead->update($request->validated());

        return redirect()->route('leads.show', $lead)->with('status', 'Lead updated.');
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        $this->authorize('delete', $lead);

        $lead->delete();

        return redirect()->route('leads.index')->with('status', 'Lead deleted.');
    }
}
