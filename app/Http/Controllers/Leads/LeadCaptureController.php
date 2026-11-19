<?php

declare(strict_types=1);

namespace App\Http\Controllers\Leads;

use App\Actions\Activities\LogActivityAction;
use App\Enums\ActivityType;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Enums\WebhookEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Leads\PublicLeadCaptureRequest;
use App\Models\Lead;
use App\Models\Team;
use App\Notifications\CrmAlertNotification;
use App\Services\Leads\LeadAssignmentService;
use App\Services\Leads\LeadDuplicateService;
use App\Services\Webhooks\WebhookDispatcherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class LeadCaptureController extends Controller
{
    public function show(): View
    {
        return view('pages.leads.capture', [
            'sources' => LeadSource::publicOptions(),
        ]);
    }

    public function store(
        PublicLeadCaptureRequest $request,
        LeadDuplicateService $duplicates,
        LeadAssignmentService $assignment,
        LogActivityAction $logActivity,
        WebhookDispatcherService $webhooks,
    ): RedirectResponse {
        if ($request->filled('company_website')) {
            return back()->with('status', 'Thanks — your enquiry has been received.');
        }

        $team = $this->resolveTeam();

        if ($team === null) {
            return back()->with('error', 'Lead capture is not configured yet. Please contact us directly.');
        }

        $data = $request->validated();
        $duplicate = $duplicates->find($team, $data['email'] ?? null, $data['phone'] ?? null);

        if ($duplicate !== null) {
            return back()->with('status', 'Thanks — your enquiry has been received.');
        }

        $owner = $assignment->assignForTeam($team);

        if ($owner === null) {
            return back()->with('error', 'No sales representative is available right now. Please try again later.');
        }

        $lead = Lead::query()->create([
            'team_id' => $team->id,
            'owner_id' => $owner->id,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? null,
            'company_name' => $data['company_name'] ?? null,
            'job_title' => $data['job_title'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'website' => $data['website'] ?? null,
            'source' => $data['source'],
            'status' => LeadStatus::New,
            'score' => 10,
        ]);

        $logActivity->handle($lead, $owner, [
            'type' => ActivityType::System,
            'direction' => 'inbound',
            'summary' => 'Lead captured via public form',
            'body' => $data['message'] ?? null,
            'occurred_at' => now(),
        ]);

        $owner->notify(new CrmAlertNotification(
            category: 'lead_assigned',
            title: 'New Inbound Lead',
            message: "Public capture assigned you lead: {$lead->full_name}.",
            actionUrl: route('leads.show', $lead),
        ));

        $webhooks->dispatch(
            WebhookEvent::LeadCreated,
            [
                'lead_id' => $lead->id,
                'full_name' => $lead->full_name,
                'email' => $lead->email,
                'source' => $lead->source?->value,
                'owner_id' => $lead->owner_id,
            ],
            $lead->team_id,
        );

        return back()->with('status', 'Thanks — your enquiry has been received.');
    }

    private function resolveTeam(): ?Team
    {
        $configuredTeamId = config('crm.lead_capture.default_team_id');

        if ($configuredTeamId !== null && $configuredTeamId !== '') {
            $team = Team::query()->whereKey((int) $configuredTeamId)->where('is_active', true)->first();

            if ($team !== null) {
                return $team;
            }
        }

        return Team::query()->where('is_active', true)->orderBy('id')->first();
    }
}
