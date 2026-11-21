<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Enums\WebhookEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Webhooks\StoreWebhookRequest;
use App\Http\Requests\Webhooks\UpdateWebhookRequest;
use App\Models\Team;
use App\Models\Webhook;
use App\Services\Audit\AuditLoggerService;
use App\Support\SecuritySecret;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class WebhookController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Webhook::class);

        $webhooks = Webhook::query()
            ->visibleTo($request->user())
            ->with(['team', 'creator'])
            ->latest('id')
            ->paginate(20);

        $teams = Team::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $events = WebhookEvent::cases();
        $inboundWebhookConfigured = filled(config('crm.lead_capture.webhook_secret'));
        $inboundWebhookUrl = route('leads.capture.webhook');

        return view('pages.settings.webhooks.index', compact(
            'webhooks',
            'teams',
            'events',
            'inboundWebhookConfigured',
            'inboundWebhookUrl',
        ));
    }

    public function store(StoreWebhookRequest $request, AuditLoggerService $auditLogger): RedirectResponse
    {
        $this->authorize('create', Webhook::class);

        $data = $request->validated();
        $generatedSecret = null;
        $secret = trim((string) ($data['secret'] ?? ''));

        if ($secret === '') {
            $secret = SecuritySecret::generate(24);
            $generatedSecret = $secret;
        }

        $webhook = Webhook::query()->create([
            'team_id' => $data['team_id'] ?? null,
            'created_by_id' => $request->user()->id,
            'name' => $data['name'],
            'url' => $data['url'],
            'events' => array_values(array_unique($data['events'])),
            'secret' => $secret,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $auditLogger->log(
            event: 'created',
            auditable: $webhook,
            actor: $request->user(),
            newValues: $webhook->only(['name', 'url', 'team_id', 'events', 'is_active']),
            teamId: $webhook->team_id,
        );

        $redirect = back()->with('status', "Webhook \"{$webhook->name}\" created.");

        if ($generatedSecret !== null) {
            $redirect->with('generated_webhook_secret', $generatedSecret);
        }

        return $redirect;
    }

    public function update(
        UpdateWebhookRequest $request,
        Webhook $webhook,
        AuditLoggerService $auditLogger,
    ): RedirectResponse {
        $this->authorize('update', $webhook);

        $data = $request->validated();
        $oldValues = $webhook->only(['name', 'url', 'team_id', 'events', 'is_active']);

        $payload = [
            'team_id' => $data['team_id'] ?? null,
            'name' => $data['name'],
            'url' => $data['url'],
            'events' => array_values(array_unique($data['events'])),
            'is_active' => $request->boolean('is_active', true),
        ];

        $newSecret = trim((string) ($data['secret'] ?? ''));
        if ($newSecret !== '') {
            $payload['secret'] = $newSecret;
        }

        $webhook->update($payload);

        $auditLogger->log(
            event: 'updated',
            auditable: $webhook,
            actor: $request->user(),
            oldValues: $oldValues,
            newValues: $webhook->only(['name', 'url', 'team_id', 'events', 'is_active']),
            teamId: $webhook->team_id,
        );

        return back()->with('status', "Webhook \"{$webhook->name}\" updated.");
    }

    public function destroy(Request $request, Webhook $webhook, AuditLoggerService $auditLogger): RedirectResponse
    {
        $this->authorize('delete', $webhook);

        $oldValues = $webhook->only(['name', 'url', 'team_id', 'events', 'is_active']);
        $name = $webhook->name;
        $teamId = $webhook->team_id;

        $webhook->delete();

        $auditLogger->log(
            event: 'deleted',
            auditable: $webhook,
            actor: $request->user(),
            oldValues: $oldValues,
            teamId: $teamId,
        );

        return back()->with('status', "Webhook \"{$name}\" removed.");
    }
}
