<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\DealStatus;
use App\Enums\FollowUpChannel;
use App\Enums\FollowUpStatus;
use App\Enums\RoleId;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Company;
use App\Models\Deal;
use App\Models\Event;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Proposal;
use App\Models\ProposalDocument;
use App\Models\SmtpSetting;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class NewFeaturesAndEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $rep;

    private Team $team;

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->team = Team::query()->create(['name' => 'Alpha Team', 'slug' => 'alpha-team']);

        $this->admin = User::query()->create([
            'team_id' => $this->team->id,
            'name' => 'Admin User',
            'email' => 'admin@salescrm.test',
            'password' => bcrypt('Password@12345'),
            'is_active' => true,
        ]);
        $this->admin->assignRole('Org Admin');

        $this->rep = User::query()->create([
            'team_id' => $this->team->id,
            'name' => 'Rep User',
            'email' => 'rep@salescrm.test',
            'password' => bcrypt('Password@12345'),
            'is_active' => true,
        ]);
        $this->rep->assignRole('Sales Rep');

        $this->company = Company::query()->create([
            'team_id' => $this->team->id,
            'owner_id' => $this->rep->id,
            'name' => 'Acme Corp',
        ]);
    }

    public function test_urgent_attention_page_renders_with_stalled_deals(): void
    {
        $pipeline = Pipeline::query()->create(['name' => 'Sales', 'team_id' => $this->team->id]);
        $stage = PipelineStage::query()->create([
            'pipeline_id' => $pipeline->id,
            'name' => 'Demo',
            'color' => '#3b82f6',
            'probability' => 40,
            'rotting_days' => 5,
        ]);

        $deal = Deal::query()->create([
            'team_id' => $this->team->id,
            'owner_id' => $this->rep->id,
            'company_id' => $this->company->id,
            'pipeline_id' => $pipeline->id,
            'pipeline_stage_id' => $stage->id,
            'title' => 'Stalled Enterprise Deal',
            'value' => 50000,
            'currency' => 'USD',
            'status' => DealStatus::Open->value,
            'expected_close_date' => now()->subDays(2),
        ]);

        $response = $this->actingAs($this->rep)->get(route('urgent-attention.index'));
        $response->assertOk();
        $response->assertSee('Stalled Enterprise Deal');
        $response->assertSee('Past expected close date');

        // Create overdue task and overdue follow-up to test full rendering
        Task::query()->create([
            'team_id' => $this->team->id,
            'assigned_to_id' => $this->rep->id,
            'created_by_id' => $this->rep->id,
            'taskable_type' => 'deal',
            'taskable_id' => $deal->id,
            'title' => 'Follow up on redlines urgently',
            'priority' => TaskPriority::Urgent,
            'status' => TaskStatus::Pending,
            'due_at' => now()->subDay(),
        ]);

        FollowUp::query()->create([
            'team_id' => $this->team->id,
            'owner_id' => $this->rep->id,
            'subject_type' => 'deal',
            'subject_id' => $deal->id,
            'channel' => FollowUpChannel::Call,
            'status' => FollowUpStatus::Scheduled,
            'scheduled_at' => now()->subHour(),
            'objective' => 'Check in with CEO',
        ]);

        $responseWithOverdue = $this->actingAs($this->rep)->get(route('urgent-attention.index'));
        $responseWithOverdue->assertOk();
        $responseWithOverdue->assertSee('Follow up on redlines urgently');
        $responseWithOverdue->assertSee('Stalled Enterprise Deal');
        $responseWithOverdue->assertSee('Call with Stalled Enterprise Deal');
    }

    public function test_activities_index_feed_renders_my_and_team_tabs(): void
    {
        $responseMy = $this->actingAs($this->rep)->get(route('activities.index', ['tab' => 'my']));
        $responseMy->assertOk();
        $responseMy->assertSee('My Activities');

        $responseTeam = $this->actingAs($this->admin)->get(route('activities.index', ['tab' => 'team']));
        $responseTeam->assertOk();
        $responseTeam->assertSee('Team Activities');
    }

    public function test_leads_check_duplicates_and_export(): void
    {
        Lead::query()->create([
            'team_id' => $this->team->id,
            'owner_id' => $this->rep->id,
            'first_name' => 'Duplicate',
            'last_name' => 'Test',
            'email' => 'duplicate@example.com',
            'company_name' => 'Acme Corp',
        ]);

        // Duplicate Check JSON
        $jsonResponse = $this->actingAs($this->rep)->getJson(route('leads.check-duplicates', ['email' => 'duplicate@example.com']));
        $jsonResponse->assertOk();
        $jsonResponse->assertJson(['count' => 1]);

        // Export Stream
        $exportResponse = $this->actingAs($this->rep)->get(route('leads.export'));
        $exportResponse->assertOk();
        $this->assertStringContainsString('text/csv', (string) $exportResponse->headers->get('content-type'));
    }

    public function test_bulk_import_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get(route('imports.bulk'));
        $response->assertOk();
        $response->assertSee('Upload Spreadsheet');
    }

    public function test_proposals_crud_and_document_storage(): void
    {
        Storage::fake('private');

        // Create proposal
        $createResponse = $this->actingAs($this->rep)->post(route('proposals.store'), [
            'title' => 'ERP Implementation Proposal',
            'amount' => 75000,
            'currency' => 'USD',
            'valid_until' => now()->addDays(30)->toDateString(),
            'notes' => 'Detailed implementation timeline.',
        ]);
        $createResponse->assertRedirect();

        $proposal = Proposal::query()->where('title', 'ERP Implementation Proposal')->firstOrFail();
        $this->assertStringStartsWith('PR-', $proposal->proposal_number);

        // Upload document
        $file = UploadedFile::fake()->create('contract_terms.pdf', 500, 'application/pdf');
        $uploadResponse = $this->actingAs($this->rep)->post(route('proposals.documents.store', $proposal), [
            'file' => $file,
        ]);
        $uploadResponse->assertRedirect();

        $doc = ProposalDocument::query()->where('proposal_id', $proposal->id)->firstOrFail();
        Storage::disk('private')->assertExists($doc->storage_path);

        // Download document
        $downloadResponse = $this->actingAs($this->rep)->get(route('proposals.documents.download', $doc));
        $downloadResponse->assertOk();
    }

    public function test_targets_and_commissions_management(): void
    {
        $pipeline = Pipeline::query()->create(['name' => 'Sales', 'team_id' => $this->team->id]);
        $stage = PipelineStage::query()->create([
            'pipeline_id' => $pipeline->id,
            'name' => 'Closed Won',
            'color' => '#10b981',
            'probability' => 100,
            'is_won' => true,
        ]);

        $deal = Deal::query()->create([
            'team_id' => $this->team->id,
            'owner_id' => $this->rep->id,
            'company_id' => $this->company->id,
            'pipeline_id' => $pipeline->id,
            'pipeline_stage_id' => $stage->id,
            'title' => 'Big Win Deal',
            'value' => 100000,
            'currency' => 'USD',
            'status' => 'won',
        ]);

        // Target
        $targetResponse = $this->actingAs($this->admin)->post(route('targets.store'), [
            'type' => 'team',
            'team_id' => $this->team->id,
            'target_amount' => 150000,
            'currency' => 'USD',
            'period_month' => now()->month,
            'period_year' => now()->year,
        ]);
        $targetResponse->assertRedirect();
        $this->assertDatabaseHas('targets', ['target_amount' => 150000]);

        // Commission
        $commResponse = $this->actingAs($this->admin)->post(route('targets.commissions.store'), [
            'deal_id' => $deal->id,
            'user_id' => $this->rep->id,
            'deal_value' => 100000,
            'amount' => 10000,
            'status' => 'approved',
        ]);
        $commResponse->assertRedirect();
        $this->assertDatabaseHas('commissions', ['amount' => 10000]);
    }

    public function test_event_lead_capture_and_event_creation(): void
    {
        Storage::fake('private');

        $event = Event::query()->create([
            'team_id' => $this->team->id,
            'created_by_id' => $this->rep->id,
            'name' => 'SaaS Summit 2026',
            'location' => 'Las Vegas, NV',
        ]);

        $cardPhoto = UploadedFile::fake()->image('card.jpg');

        $captureResponse = $this->actingAs($this->rep)->post(route('mobile.capture.store'), [
            'event_id' => $event->id,
            'first_name' => 'Booth',
            'last_name' => 'Visitor',
            'company_name' => 'Tech Corp',
            'email' => 'visitor@techcorp.test',
            'phone' => '+15551234567',
            'card_photo' => $cardPhoto,
            'notes' => 'Interested in enterprise subscription.',
        ]);
        $captureResponse->assertRedirect();

        $lead = Lead::query()->where('email', 'visitor@techcorp.test')->firstOrFail();
        $this->assertSame($event->id, $lead->event_id);
        $this->assertNotNull($lead->card_photo_path);
        Storage::disk('private')->assertExists($lead->card_photo_path);
    }

    public function test_smtp_configuration_and_password_encryption(): void
    {
        $response = $this->actingAs($this->admin)->post(route('settings.smtp.store'), [
            'mail_host' => 'smtp.mailgun.org',
            'mail_port' => 587,
            'mail_username' => 'postmaster@mg.example.com',
            'mail_password' => 'secret-smtp-password-12345',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'crm@example.com',
            'mail_from_name' => 'Sale Fusion CRM',
        ]);
        $response->assertRedirect();

        $setting = SmtpSetting::query()->latest()->firstOrFail();
        $this->assertNotSame('secret-smtp-password-12345', $setting->getRawOriginal('mail_password'));
        $this->assertSame('secret-smtp-password-12345', $setting->decrypted_password);
    }

    public function test_check_proposal_expiry_command_expires_past_proposals(): void
    {
        Proposal::query()->create([
            'team_id' => $this->team->id,
            'owner_id' => $this->rep->id,
            'proposal_number' => 'PR-2025-00001',
            'title' => 'Old Expired Proposal',
            'amount' => 10000,
            'currency' => 'USD',
            'status' => 'sent',
            'valid_until' => now()->subDays(5)->toDateString(),
        ]);

        Artisan::call('crm:check-proposal-expiry');

        $this->assertDatabaseHas('proposals', [
            'proposal_number' => 'PR-2025-00001',
            'status' => 'expired',
        ]);
    }

    public function test_four_enterprise_role_ids_exist_and_authorize_correctly(): void
    {
        $this->assertSame('Head of Sales', RoleId::HeadOfSales->value);
        $this->assertSame('Sales Manager', RoleId::SalesManager->value);
        $this->assertSame('Sales Executive', RoleId::SalesExecutive->value);
        $this->assertSame('Viewer', RoleId::Viewer->value);

        $executiveUser = User::query()->create([
            'team_id' => $this->team->id,
            'name' => 'Executive User',
            'email' => 'exec@salescrm.test',
            'password' => bcrypt('Password@12345'),
            'is_active' => true,
        ]);
        $executiveUser->assignRole('Sales Executive');

        $this->assertTrue($executiveUser->hasRole('Sales Executive'));
        $this->assertTrue($executiveUser->can('leads.view.own'));
    }
}
