<?php

declare(strict_types=1);

namespace App\Http\Controllers\Leads;

use App\Actions\Leads\MergeLeadAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Leads\MergeLeadRequest;
use App\Http\Requests\Leads\StoreLeadRequest;
use App\Http\Requests\Leads\UpdateLeadRequest;
use App\Models\Lead;
use App\Models\Pipeline;
use App\Models\User;
use App\Notifications\CrmAlertNotification;
use App\Services\Audit\AuditLoggerService;
use App\Services\Leads\LeadDuplicateService;
use App\Support\CsvSanitizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class LeadController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Lead::class);

        $user = $request->user();
        $status = $request->query('status');
        $source = $request->query('source');
        $search = trim((string) $request->query('search', ''));

        $leads = Lead::query()
            ->with('owner')
            ->visibleTo($user)
            ->when(is_string($status) && $status !== '', fn ($q) => $q->where('status', $status))
            ->when(is_string($source) && $source !== '', fn ($q) => $q->where('source', $source))
            ->when($search !== '', function ($q) use ($search): void {
                $q->where(function ($sub) use ($search): void {
                    $sub->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate((int) config('crm.pagination.per_page', 20))
            ->withQueryString();

        $owners = $user->can('leads.view.team') || $user->can('leads.view.all')
            ? User::query()->where('team_id', $user->team_id)->where('is_active', true)->orderBy('name')->get(['id', 'name'])
            : collect();

        return view('pages.leads.index', compact('leads', 'status', 'source', 'search', 'owners'));
    }

    public function store(
        StoreLeadRequest $request,
        LeadDuplicateService $duplicates,
        AuditLoggerService $auditLogger,
    ): RedirectResponse {
        $this->authorize('create', Lead::class);

        $user = $request->user();
        $data = $request->validated();

        $ownerId = (int) ($data['owner_id'] ?? $user->id);
        if ($ownerId !== $user->id && ! $user->can('leads.update.team') && ! $user->can('leads.update.all')) {
            abort(403, 'You cannot assign leads to another user.');
        }

        $duplicate = $duplicates->find(
            team: $user->team,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
        );

        if ($duplicate !== null) {
            throw ValidationException::withMessages([
                'email' => "A matching lead already exists: {$duplicate->full_name} (#{$duplicate->id}).",
            ]);
        }

        $lead = Lead::query()->create(array_merge($data, [
            'team_id' => $user->team_id,
            'owner_id' => $ownerId,
            'score' => (int) ($data['score'] ?? 0),
        ]));

        $auditLogger->log(
            event: 'created',
            auditable: $lead,
            actor: $user,
            newValues: $lead->only(['first_name', 'last_name', 'email', 'phone', 'source', 'status', 'owner_id']),
            teamId: $lead->team_id,
        );

        if ($ownerId !== $user->id) {
            User::query()->find($ownerId)?->notify(new CrmAlertNotification(
                category: 'lead_assigned',
                title: 'New Lead Assigned',
                message: "{$user->name} assigned you a lead: {$lead->full_name}.",
                actionUrl: route('leads.show', $lead),
            ));
        }

        return redirect()->route('leads.show', $lead)->with('status', 'Lead created.');
    }

    public function show(Lead $lead, LeadDuplicateService $duplicates): View
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
            'attachments.uploader',
        ]);

        $pipelines = Pipeline::query()
            ->with(['stages' => fn ($q) => $q->where('is_won_stage', false)->where('is_lost_stage', false)])
            ->where('is_active', true)
            ->get();

        $possibleDuplicates = $duplicates->allFor($lead);
        $owners = User::query()
            ->where('team_id', $lead->team_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('pages.leads.show', compact('lead', 'pipelines', 'possibleDuplicates', 'owners'));
    }

    public function update(
        UpdateLeadRequest $request,
        Lead $lead,
        LeadDuplicateService $duplicates,
        AuditLoggerService $auditLogger,
    ): RedirectResponse {
        $this->authorize('update', $lead);

        $data = $request->validated();
        $user = $request->user();

        if (isset($data['owner_id']) && (int) $data['owner_id'] !== (int) $lead->owner_id) {
            if (! $user->can('leads.update.team') && ! $user->can('leads.update.all')) {
                abort(403, 'You cannot reassign this lead.');
            }
        }

        $duplicate = $duplicates->find(
            team: $lead->team,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            excludeLeadId: $lead->id,
        );

        if ($duplicate !== null) {
            throw ValidationException::withMessages([
                'email' => "A matching lead already exists: {$duplicate->full_name} (#{$duplicate->id}).",
            ]);
        }

        $oldValues = $lead->only(['first_name', 'last_name', 'email', 'phone', 'source', 'status', 'owner_id']);
        $lead->update($data);

        $auditLogger->log(
            event: 'updated',
            auditable: $lead,
            actor: $user,
            oldValues: $oldValues,
            newValues: $lead->only(['first_name', 'last_name', 'email', 'phone', 'source', 'status', 'owner_id']),
            teamId: $lead->team_id,
        );

        return redirect()->route('leads.show', $lead)->with('status', 'Lead updated.');
    }

    public function merge(
        MergeLeadRequest $request,
        Lead $lead,
        MergeLeadAction $mergeAction,
    ): RedirectResponse {
        $this->authorize('update', $lead);

        $duplicate = Lead::query()->findOrFail((int) $request->validated('duplicate_lead_id'));
        $this->authorize('view', $duplicate);

        $mergeAction->handle($lead, $duplicate, $request->user());

        return redirect()->route('leads.show', $lead)->with('status', 'Duplicate lead merged.');
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        $this->authorize('delete', $lead);

        $lead->delete();

        return redirect()->route('leads.index')->with('status', 'Lead deleted.');
    }

    public function checkDuplicates(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Lead::class);

        $user = $request->user();
        $email = $request->query('email');
        $companyName = $request->query('company_name');
        $phone = $request->query('phone');

        $duplicates = Lead::query()
            ->visibleTo($user)
            ->where(function ($q) use ($email, $companyName, $phone): void {
                if ($email) {
                    $q->orWhere('email', $email);
                }
                if ($companyName) {
                    $q->orWhere('company_name', 'like', "%{$companyName}%");
                }
                if ($phone) {
                    $q->orWhere('phone', $phone);
                }
            })
            ->limit(10)
            ->get(['id', 'first_name', 'last_name', 'company_name', 'email', 'phone', 'status']);

        return response()->json([
            'count' => $duplicates->count(),
            'duplicates' => $duplicates,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', Lead::class);

        $user = $request->user();
        $status = $request->query('status');
        $source = $request->query('source');
        $search = trim((string) $request->query('search', ''));

        $query = Lead::query()
            ->with(['owner', 'team'])
            ->visibleTo($user)
            ->when(is_string($status) && $status !== '', fn ($q) => $q->where('status', $status))
            ->when(is_string($source) && $source !== '', fn ($q) => $q->where('source', $source))
            ->when($search !== '', function ($q) use ($search): void {
                $q->where(function ($sub) use ($search): void {
                    $sub->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="leads-export-'.now()->format('Y-m-d').'.csv"',
        ];

        return response()->stream(function () use ($query): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'First Name', 'Last Name', 'Company', 'Job Title', 'Email', 'Phone', 'Source', 'Status', 'Score', 'Owner', 'Created At']);

            $query->chunk(250, function ($leads) use ($handle): void {
                foreach ($leads as $lead) {
                    fputcsv($handle, [
                        (string) $lead->id,
                        CsvSanitizer::sanitizeCell($lead->first_name),
                        CsvSanitizer::sanitizeCell($lead->last_name),
                        CsvSanitizer::sanitizeCell($lead->company_name),
                        CsvSanitizer::sanitizeCell($lead->job_title),
                        CsvSanitizer::sanitizeCell($lead->email),
                        CsvSanitizer::sanitizeCell($lead->phone),
                        CsvSanitizer::sanitizeCell($lead->source?->value ?? (string) $lead->source),
                        CsvSanitizer::sanitizeCell($lead->status?->value ?? (string) $lead->status),
                        (string) $lead->score,
                        CsvSanitizer::sanitizeCell($lead->owner?->name),
                        $lead->created_at?->toIso8601String() ?? '',
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}
