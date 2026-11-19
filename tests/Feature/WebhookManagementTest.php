<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\LeadSource;
use App\Enums\WebhookEvent;
use App\Jobs\DeliverWebhookJob;
use App\Models\AuditLog;
use App\Models\Team;
use App\Models\User;
use App\Models\Webhook;
use App\Services\Webhooks\WebhookDispatcherService;
use Database\Seeders\DemoCrmSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class WebhookManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(DemoCrmSeeder::class);
    }

    public function test_guests_cannot_access_webhook_management_routes(): void
    {
        $this->get(route('settings.webhooks.index'))->assertRedirect(route('login'));
        $this->post(route('settings.webhooks.store'), [])->assertRedirect(route('login'));
    }

    public function test_non_admin_roles_are_forbidden_from_managing_webhooks(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@salescrm.test')->firstOrFail();
        $webhook = Webhook::query()->create([
            'team_id' => null,
            'created_by_id' => $superAdmin->id,
            'name' => 'Existing Endpoint',
            'url' => 'https://hooks.example.com/crm',
            'events' => [WebhookEvent::LeadCreated->value],
            'secret' => 'super-secret-signing-key-1234',
            'is_active' => true,
        ]);

        $nonAdmins = User::query()
            ->whereIn('email', ['manager@salescrm.test', 'rep@salescrm.test', 'analyst@salescrm.test'])
            ->get();

        $this->assertCount(3, $nonAdmins);

        foreach ($nonAdmins as $user) {
            $this->actingAs($user)->get(route('settings.webhooks.index'))->assertForbidden();
            $this->actingAs($user)->post(route('settings.webhooks.store'), [
                'name' => 'Blocked Hook',
                'url' => 'https://hooks.example.com/blocked',
                'events' => [WebhookEvent::DealWon->value],
            ])->assertForbidden();
            $this->actingAs($user)->put(route('settings.webhooks.update', $webhook), [
                'name' => 'Tampered Hook',
                'url' => 'https://hooks.example.com/tampered',
                'events' => [WebhookEvent::DealWon->value],
            ])->assertForbidden();
            $this->actingAs($user)->delete(route('settings.webhooks.destroy', $webhook))->assertForbidden();
        }
    }

    public function test_webhook_store_validates_url_and_events(): void
    {
        $admin = User::query()->where('email', 'admin@salescrm.test')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('settings.webhooks.store'), [
                'name' => '',
                'url' => 'not-a-valid-url',
                'events' => ['unknown.event'],
            ])
            ->assertSessionHasErrors(['name', 'url', 'events.0']);
    }

    public function test_org_admin_can_create_update_and_delete_webhooks_with_audit_logs(): void
    {
        $admin = User::query()->where('email', 'admin@salescrm.test')->firstOrFail();
        $team = Team::query()->firstOrFail();

        $this->actingAs($admin)
            ->post(route('settings.webhooks.store'), [
                'name' => 'ERP Deal Sync',
                'url' => 'https://erp.example.com/webhooks/sales-crm',
                'team_id' => $team->id,
                'events' => [WebhookEvent::DealWon->value, WebhookEvent::QuotationSent->value],
                'is_active' => '1',
            ])
            ->assertRedirect()
            ->assertSessionHas('generated_webhook_secret');

        $webhook = Webhook::query()->where('name', 'ERP Deal Sync')->firstOrFail();
        $this->assertSame($team->id, $webhook->team_id);
        $this->assertSame([WebhookEvent::DealWon->value, WebhookEvent::QuotationSent->value], $webhook->events);
        $this->assertNotEmpty($webhook->secret);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'created',
            'auditable_type' => 'webhook',
            'auditable_id' => $webhook->id,
        ]);

        $this->actingAs($admin)
            ->put(route('settings.webhooks.update', $webhook), [
                'name' => 'ERP Deal Sync v2',
                'url' => 'https://erp.example.com/webhooks/v2',
                'team_id' => null,
                'events' => [WebhookEvent::LeadConverted->value],
                'is_active' => '0',
            ])
            ->assertRedirect();

        $webhook->refresh();
        $this->assertSame('ERP Deal Sync v2', $webhook->name);
        $this->assertNull($webhook->team_id);
        $this->assertFalse($webhook->is_active);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'updated',
            'auditable_type' => 'webhook',
            'auditable_id' => $webhook->id,
        ]);

        $this->actingAs($admin)
            ->delete(route('settings.webhooks.destroy', $webhook))
            ->assertRedirect();

        $this->assertSoftDeleted('webhooks', ['id' => $webhook->id]);
        $this->assertSame(1, AuditLog::query()->where('auditable_type', 'webhook')->where('event', 'deleted')->count());
    }

    public function test_outbound_webhook_dispatcher_and_signed_delivery_job_work_end_to_end(): void
    {
        Queue::fake();

        $superAdmin = User::query()->where('email', 'superadmin@salescrm.test')->firstOrFail();
        $teams = Team::query()->orderBy('id')->get();
        $teamA = $teams[0];
        $teamB = $teams[1];

        $matchingWebhook = Webhook::query()->create([
            'team_id' => $teamA->id,
            'created_by_id' => $superAdmin->id,
            'name' => 'Team A Lead Alerts',
            'url' => 'https://hooks.example.com/team-a',
            'events' => [WebhookEvent::LeadCreated->value],
            'secret' => 'test-signing-secret-123456',
            'is_active' => true,
        ]);

        Webhook::query()->create([
            'team_id' => $teamB->id,
            'created_by_id' => $superAdmin->id,
            'name' => 'Team B Only',
            'url' => 'https://hooks.example.com/team-b',
            'events' => [WebhookEvent::LeadCreated->value],
            'secret' => 'test-signing-secret-654321',
            'is_active' => true,
        ]);

        Webhook::query()->create([
            'team_id' => null,
            'created_by_id' => $superAdmin->id,
            'name' => 'Paused Global Hook',
            'url' => 'https://hooks.example.com/paused',
            'events' => [WebhookEvent::LeadCreated->value],
            'secret' => 'test-signing-secret-999999',
            'is_active' => false,
        ]);

        $dispatcher = app(WebhookDispatcherService::class);
        $count = $dispatcher->dispatch(WebhookEvent::LeadCreated, ['lead_id' => 42], $teamA->id);

        $this->assertSame(1, $count);
        Queue::assertPushed(DeliverWebhookJob::class, fn (DeliverWebhookJob $job): bool => $job->webhookId === $matchingWebhook->id && $job->eventName === WebhookEvent::LeadCreated->value);

        Http::fake([
            'https://hooks.example.com/team-a' => Http::response(['ok' => true], 200),
        ]);

        $job = new DeliverWebhookJob($matchingWebhook->id, WebhookEvent::LeadCreated->value, ['lead_id' => 42]);
        $job->handle($dispatcher);

        Http::assertSent(function ($request) use ($dispatcher): bool {
            $expectedSignature = $dispatcher->signPayload($request->body(), 'test-signing-secret-123456');

            return $request->url() === 'https://hooks.example.com/team-a'
                && $request->header('X-CRM-Event')[0] === WebhookEvent::LeadCreated->value
                && hash_equals($expectedSignature, (string) ($request->header('X-CRM-Signature')[0] ?? ''));
        });

        $matchingWebhook->refresh();
        $this->assertSame(200, $matchingWebhook->last_status_code);
        $this->assertNotNull($matchingWebhook->last_triggered_at);
        $this->assertSame(0, $matchingWebhook->failure_count);
    }

    public function test_public_lead_capture_accepts_new_professional_sources_and_rejects_internal_only_import(): void
    {
        $this->post(route('leads.capture.store'), [
            'first_name' => 'Tanvir',
            'last_name' => 'Hossain',
            'company_name' => 'Summit Tech',
            'email' => 'tanvir.summit@example.com',
            'source' => LeadSource::OrganicSearch->value,
            'consent' => '1',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('leads', [
            'email' => 'tanvir.summit@example.com',
            'source' => LeadSource::OrganicSearch->value,
        ]);

        $this->post(route('leads.capture.store'), [
            'first_name' => 'Blocked',
            'email' => 'blocked.import@example.com',
            'source' => LeadSource::Import->value,
            'consent' => '1',
        ])->assertSessionHasErrors(['source']);
    }
}
