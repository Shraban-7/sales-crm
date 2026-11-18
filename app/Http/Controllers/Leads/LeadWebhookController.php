<?php

declare(strict_types=1);

namespace App\Http\Controllers\Leads;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Team;
use App\Notifications\CrmAlertNotification;
use App\Services\Leads\LeadAssignmentService;
use App\Services\Leads\LeadDuplicateService;
use App\Support\PhoneNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class LeadWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        LeadDuplicateService $duplicates,
        LeadAssignmentService $assignment,
    ): JsonResponse {
        $secret = (string) config('crm.lead_capture.webhook_secret', '');

        if ($secret === '') {
            abort(503, 'Lead webhook is not configured.');
        }

        $signature = (string) $request->header('X-CRM-Signature', '');
        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        if ($signature === '' || ! hash_equals($expected, $signature)) {
            abort(401, 'Invalid signature.');
        }

        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'company_name' => ['nullable', 'string', 'max:191'],
            'job_title' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email:rfc', 'max:191', 'required_without:phone'],
            'phone' => ['nullable', 'string', 'max:30', 'required_without:email'],
            'website' => ['nullable', 'url', 'max:255'],
            'source' => ['nullable', Rule::enum(LeadSource::class)],
            'message' => ['nullable', 'string', 'max:2000'],
            'team_id' => ['nullable', 'integer', 'exists:teams,id'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $team = $this->resolveTeam(isset($data['team_id']) ? (int) $data['team_id'] : null);

        if ($team === null) {
            return response()->json(['message' => 'No active team is available for lead capture.'], 503);
        }

        $email = isset($data['email']) ? mb_strtolower(trim((string) $data['email'])) : null;
        $phone = PhoneNumber::normalize($data['phone'] ?? null);
        $duplicate = $duplicates->find($team, $email, $phone);

        if ($duplicate !== null) {
            return response()->json(['status' => 'duplicate', 'lead_id' => $duplicate->id], 200);
        }

        $owner = $assignment->assignForTeam($team);

        if ($owner === null) {
            return response()->json(['message' => 'No active sales representative is available.'], 503);
        }

        $lead = Lead::query()->create([
            'team_id' => $team->id,
            'owner_id' => $owner->id,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? null,
            'company_name' => $data['company_name'] ?? null,
            'job_title' => $data['job_title'] ?? null,
            'email' => $email,
            'phone' => $phone,
            'website' => $data['website'] ?? null,
            'source' => $data['source'] ?? LeadSource::Website->value,
            'status' => LeadStatus::New,
            'score' => 10,
        ]);

        $owner->notify(new CrmAlertNotification(
            category: 'lead_assigned',
            title: 'New Webhook Lead',
            message: "Webhook intake assigned you lead: {$lead->full_name}.",
            actionUrl: route('leads.show', $lead),
        ));

        return response()->json(['status' => 'created', 'lead_id' => $lead->id], 201);
    }

    private function resolveTeam(?int $teamId): ?Team
    {
        if ($teamId !== null) {
            return Team::query()->whereKey($teamId)->where('is_active', true)->first();
        }

        $configured = config('crm.lead_capture.default_team_id');

        if ($configured !== null && $configured !== '') {
            $team = Team::query()->whereKey((int) $configured)->where('is_active', true)->first();

            if ($team !== null) {
                return $team;
            }
        }

        return Team::query()->where('is_active', true)->orderBy('id')->first();
    }
}
