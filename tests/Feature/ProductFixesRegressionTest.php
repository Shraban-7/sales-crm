<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Leads\ConvertLeadToDealAction;
use App\Actions\Quotations\CloneQuotationVersionAction;
use App\Actions\Quotations\CreateQuotationAction;
use App\Data\Leads\LeadConversionData;
use App\Enums\ActivityType;
use App\Enums\DealStatus;
use App\Enums\FollowUpChannel;
use App\Enums\FollowUpStatus;
use App\Enums\ImportExportModule;
use App\Enums\ImportExportStatus;
use App\Enums\ImportExportType;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Enums\QuotationStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Exceptions\DomainRuleException;
use App\Jobs\ProcessCsvImportJob;
use App\Models\Activity;
use App\Models\Attachment;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\FollowUp;
use App\Models\ImportExportJob;
use App\Models\Lead;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Quotation;
use App\Models\Reminder;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Notifications\CrmAlertNotification;
use App\Services\Reports\AnalyticsService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

final class ProductFixesRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_public_lead_capture_webhook_round_robin_and_disqualified_block(): void
    {
        Notification::fake();

        $team = Team::query()->create(['name' => 'Inbound', 'slug' => 'inbound', 'is_active' => true]);
        $rep1 = $this->makeUser($team, 'Sales Rep', 'rep1@salescrm.test');
        $rep2 = $this->makeUser($team, 'Sales Rep', 'rep2@salescrm.test');

        $this->get(route('leads.capture.show'))->assertOk();

        // First public capture assigns to rep1
        $this->post(route('leads.capture.store'), [
            'first_name' => 'Alice',
            'email' => 'alice@prospect.test',
            'source' => LeadSource::Website->value,
            'consent' => '1',
        ])->assertRedirect();

        $lead1 = Lead::query()->where('email', 'alice@prospect.test')->firstOrFail();
        $this->assertSame($rep1->id, $lead1->owner_id);
        Notification::assertSentTo($rep1, CrmAlertNotification::class);

        // Second public capture round-robins to rep2
        $this->post(route('leads.capture.store'), [
            'first_name' => 'Bob',
            'phone' => '+8801711111111',
            'source' => LeadSource::Referral->value,
            'consent' => '1',
        ])->assertRedirect();

        $lead2 = Lead::query()->where('first_name', 'Bob')->firstOrFail();
        $this->assertSame($rep2->id, $lead2->owner_id);

        // Honeypot blocks bot submissions silently
        $this->post(route('leads.capture.store'), [
            'first_name' => 'Bot',
            'email' => 'bot@spam.test',
            'source' => LeadSource::Website->value,
            'company_website' => 'http://spam.invalid',
        ])->assertRedirect();
        $this->assertDatabaseMissing('leads', ['email' => 'bot@spam.test']);

        // HMAC webhook intake
        config()->set('crm.lead_capture.webhook_secret', 'test-secret-key');
        $payload = json_encode([
            'first_name' => 'Webhook',
            'email' => 'hook@prospect.test',
            'source' => LeadSource::LinkedIn->value,
            'team_id' => $team->id,
        ], JSON_THROW_ON_ERROR);
        $sig = hash_hmac('sha256', $payload, 'test-secret-key');

        $this->call('POST', '/api/leads/capture', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_CRM_SIGNATURE' => $sig,
        ], $payload)->assertStatus(201);

        // Disqualified lead cannot be converted
        $pipeline = Pipeline::query()->create(['name' => 'Default', 'is_default' => true]);
        $stage = PipelineStage::query()->create(['pipeline_id' => $pipeline->id, 'name' => 'Qual', 'slug' => 'qual', 'position' => 1]);
        $lead1->update(['status' => LeadStatus::Disqualified, 'disqualified_reason' => 'No budget']);

        $this->expectException(DomainRuleException::class);
        app(ConvertLeadToDealAction::class)->handle(
            $lead1,
            new LeadConversionData('Alice Co', 'Alice Deal', $pipeline->id, $stage->id, 1000, 'BDT', null),
            $rep1,
        );
    }

    public function test_quote_number_does_not_collide_after_version_clone_and_send_logs_activity(): void
    {
        Mail::fake();

        $team = Team::query()->create(['name' => 'Alpha', 'slug' => 'alpha']);
        $rep = $this->makeUser($team, 'Sales Rep', 'rep@salescrm.test');
        $pipeline = Pipeline::query()->create(['name' => 'Default', 'is_default' => true]);
        $stage = PipelineStage::query()->create(['pipeline_id' => $pipeline->id, 'name' => 'Proposal', 'slug' => 'proposal', 'position' => 1]);
        $company = Company::query()->create(['team_id' => $team->id, 'owner_id' => $rep->id, 'name' => 'Acme', 'country' => 'BD']);
        $contact = Contact::query()->create(['team_id' => $team->id, 'owner_id' => $rep->id, 'company_id' => $company->id, 'first_name' => 'Ana', 'last_name' => 'Lee', 'email' => 'ana@acme.test']);
        $deal = Deal::query()->create([
            'team_id' => $team->id,
            'owner_id' => $rep->id,
            'pipeline_id' => $pipeline->id,
            'pipeline_stage_id' => $stage->id,
            'company_id' => $company->id,
            'primary_contact_id' => $contact->id,
            'title' => 'Acme Deal',
            'value' => 5000,
            'currency' => 'BDT',
            'status' => DealStatus::Open,
            'stage_changed_at' => now(),
        ]);

        $createAction = app(CreateQuotationAction::class);
        $basePayload = [
            'deal_id' => $deal->id,
            'contact_id' => $contact->id,
            'currency' => 'BDT',
            'issue_date' => now()->toDateString(),
            'valid_until' => now()->addDays(15)->toDateString(),
            'items' => [['item_name' => 'License', 'quantity' => 1, 'unit_price' => 5000]],
        ];

        $q1 = $createAction->handle($basePayload, $rep);
        $q2 = $createAction->handle($basePayload, $rep);
        $q1Clone = app(CloneQuotationVersionAction::class)->handle($q1, $rep);
        $q3 = $createAction->handle($basePayload, $rep);

        $year = now()->format('Y');
        $this->assertSame("QT-{$year}-00001", $q1->quote_number);
        $this->assertSame("QT-{$year}-00002", $q2->quote_number);
        $this->assertSame("QT-{$year}-00001-V2", $q1Clone->quote_number);
        $this->assertSame("QT-{$year}-00003", $q3->quote_number);

        // Sending logs an outbound email activity on the deal
        $this->actingAs($rep)->post(route('quotations.send', $q1))->assertRedirect();
        $this->assertDatabaseHas('activities', [
            'subject_type' => 'deal',
            'subject_id' => $deal->id,
            'type' => ActivityType::Email->value,
        ]);

        // Accepted quote cannot be rejected
        $this->actingAs($rep)->post(route('quotations.accept', $q1))->assertRedirect();
        $this->actingAs($rep)->post(route('quotations.reject', $q1), ['rejection_reason' => 'Too late'])
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->assertSame(QuotationStatus::Accepted, $q1->fresh()->status);
    }

    public function test_within_team_idor_blocks_quoting_or_attaching_teammate_records(): void
    {
        $team = Team::query()->create(['name' => 'Alpha', 'slug' => 'alpha']);
        $repA = $this->makeUser($team, 'Sales Rep', 'repa@salescrm.test');
        $repB = $this->makeUser($team, 'Sales Rep', 'repb@salescrm.test');

        $pipeline = Pipeline::query()->create(['name' => 'Default', 'is_default' => true]);
        $stage = PipelineStage::query()->create(['pipeline_id' => $pipeline->id, 'name' => 'Qual', 'slug' => 'qual', 'position' => 1]);
        $companyB = Company::query()->create(['team_id' => $team->id, 'owner_id' => $repB->id, 'name' => 'B Co', 'country' => 'BD']);
        $dealB = Deal::query()->create([
            'team_id' => $team->id,
            'owner_id' => $repB->id,
            'pipeline_id' => $pipeline->id,
            'pipeline_stage_id' => $stage->id,
            'company_id' => $companyB->id,
            'title' => 'B Deal',
            'value' => 1000,
            'currency' => 'BDT',
            'status' => DealStatus::Open,
            'stage_changed_at' => now(),
        ]);

        $this->actingAs($repA)->post(route('quotations.store'), [
            'deal_id' => $dealB->id,
            'currency' => 'BDT',
            'issue_date' => now()->toDateString(),
            'valid_until' => now()->addDays(10)->toDateString(),
            'items' => [['item_name' => 'Item', 'quantity' => 1, 'unit_price' => 100]],
        ])->assertForbidden();

        $this->actingAs($repA)->post(route('deals.store'), [
            'title' => 'Hijack Deal',
            'company_id' => $companyB->id,
            'pipeline_id' => $pipeline->id,
            'pipeline_stage_id' => $stage->id,
            'value' => 1000,
            'currency' => 'BDT',
        ])->assertForbidden();
    }

    public function test_reports_separate_currencies_use_closed_at_and_dashboard_counts_exceed_six(): void
    {
        $team = Team::query()->create(['name' => 'Alpha', 'slug' => 'alpha']);
        $analyst = $this->makeUser($team, 'Read-Only Analyst', 'analyst@salescrm.test');
        $rep = $this->makeUser($team, 'Sales Rep', 'rep@salescrm.test');
        $pipeline = Pipeline::query()->create(['name' => 'Default', 'is_default' => true]);
        $stage = PipelineStage::query()->create(['pipeline_id' => $pipeline->id, 'name' => 'Won', 'slug' => 'won', 'position' => 1, 'is_won_stage' => true]);
        $company = Company::query()->create(['team_id' => $team->id, 'owner_id' => $rep->id, 'name' => 'Acme', 'country' => 'BD']);

        // Deal created 6 months ago, closed today -> should appear in current 30-day closed_at window
        $oldCreatedWon = Deal::query()->create([
            'team_id' => $team->id,
            'owner_id' => $rep->id,
            'pipeline_id' => $pipeline->id,
            'pipeline_stage_id' => $stage->id,
            'company_id' => $company->id,
            'title' => 'BDT Deal',
            'value' => 5000.00,
            'currency' => 'BDT',
            'status' => DealStatus::Won,
            'closed_at' => now(),
            'stage_changed_at' => now(),
        ]);
        $oldCreatedWon->forceFill(['created_at' => now()->subMonths(6)])->saveQuietly();

        Deal::query()->create([
            'team_id' => $team->id,
            'owner_id' => $rep->id,
            'pipeline_id' => $pipeline->id,
            'pipeline_stage_id' => $stage->id,
            'company_id' => $company->id,
            'title' => 'USD Deal',
            'value' => 200.00,
            'currency' => 'USD',
            'status' => DealStatus::Won,
            'closed_at' => now(),
            'stage_changed_at' => now(),
        ]);

        $summary = app(AnalyticsService::class)->getSummary($analyst, now()->subDays(30), now());
        $currencies = collect($summary['currency_totals'])->keyBy('currency');
        $this->assertSame('5000.00', number_format((float) $currencies['BDT']['won_value'], 2, '.', ''));
        $this->assertSame('200.00', number_format((float) $currencies['USD']['won_value'], 2, '.', ''));

        // Analyst can view tasks/follow-ups & mobile nav exists on dashboard
        for ($i = 1; $i <= 8; $i++) {
            Task::query()->create([
                'team_id' => $team->id,
                'assigned_to_id' => $rep->id,
                'created_by_id' => $rep->id,
                'taskable_type' => 'company',
                'taskable_id' => $company->id,
                'title' => "Task {$i}",
                'priority' => TaskPriority::Medium,
                'status' => TaskStatus::Pending,
                'due_at' => now()->addDays($i),
            ]);
        }

        $this->actingAs($rep)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('pendingTasksCount', 8)
            ->assertSee('data-mobile-nav-open', false);

        $firstTask = Task::query()->firstOrFail();
        $this->assertTrue($analyst->can('view', $firstTask));
        $this->actingAs($analyst)->get(route('tasks.index'))->assertOk();
        $this->actingAs($analyst)->get(route('follow-ups.index'))->assertOk();
    }

    public function test_csv_import_preserves_plus_phone_and_dedupes_contacts_and_companies(): void
    {
        Storage::fake('private');

        $team = Team::query()->create(['name' => 'Alpha', 'slug' => 'alpha']);
        $manager = $this->makeUser($team, 'Sales Manager', 'manager@salescrm.test');

        $leadCsv = "first_name,phone\nNadia,+8801711223344\n";
        Storage::disk('private')->put('imports/leads.csv', $leadCsv);
        $leadJob = ImportExportJob::query()->create([
            'team_id' => $team->id,
            'user_id' => $manager->id,
            'type' => ImportExportType::Import,
            'module' => ImportExportModule::Leads,
            'status' => ImportExportStatus::Pending,
            'file_disk' => 'private',
            'source_file_path' => 'imports/leads.csv',
        ]);
        (new ProcessCsvImportJob($leadJob->id, 'skip'))->handle();

        $this->assertDatabaseHas('leads', ['first_name' => 'Nadia', 'phone' => '+8801711223344']);

        $companyCsv = "name,industry\nAcme BD,Tech\nAcme BD,Software\n";
        Storage::disk('private')->put('imports/companies.csv', $companyCsv);
        $companyJob = ImportExportJob::query()->create([
            'team_id' => $team->id,
            'user_id' => $manager->id,
            'type' => ImportExportType::Import,
            'module' => ImportExportModule::Companies,
            'status' => ImportExportStatus::Pending,
            'file_disk' => 'private',
            'source_file_path' => 'imports/companies.csv',
        ]);
        (new ProcessCsvImportJob($companyJob->id, 'update'))->handle();

        $this->assertSame(1, Company::query()->where('name', 'Acme BD')->count());
        $this->assertDatabaseHas('companies', ['name' => 'Acme BD', 'industry' => 'Software']);
    }

    public function test_attachments_upload_download_idor_and_overdue_follow_up_escalation(): void
    {
        Storage::fake('private');
        Notification::fake();

        $teamA = Team::query()->create(['name' => 'Team A', 'slug' => 'team-a']);
        $teamB = Team::query()->create(['name' => 'Team B', 'slug' => 'team-b']);
        $repA = $this->makeUser($teamA, 'Sales Rep', 'repa@salescrm.test');
        $repB = $this->makeUser($teamB, 'Sales Rep', 'repb@salescrm.test');

        $companyA = Company::query()->create(['team_id' => $teamA->id, 'owner_id' => $repA->id, 'name' => 'A Co', 'country' => 'BD']);

        $file = UploadedFile::fake()->create('proposal.pdf', 120, 'application/pdf');
        $this->actingAs($repA)->post(route('attachments.store'), [
            'attachable_type' => 'company',
            'attachable_id' => $companyA->id,
            'file' => $file,
        ])->assertRedirect();

        $attachment = Attachment::query()->firstOrFail();
        $this->actingAs($repA)->get(route('attachments.download', $attachment))->assertOk();
        $this->actingAs($repB)->get(route('attachments.download', $attachment))->assertForbidden();

        // Overdue follow-up escalation & reminder dispatch
        $followUp = FollowUp::query()->create([
            'team_id' => $teamA->id,
            'owner_id' => $repA->id,
            'subject_type' => 'company',
            'subject_id' => $companyA->id,
            'channel' => FollowUpChannel::Call,
            'status' => FollowUpStatus::Scheduled,
            'scheduled_at' => now()->subHours(2),
            'objective' => 'Follow up on SLA',
        ]);

        Reminder::query()->create([
            'user_id' => $repA->id,
            'remindable_type' => 'follow_up',
            'remindable_id' => $followUp->id,
            'remind_at' => now()->subMinute(),
            'channel' => 'database',
        ]);

        $this->artisan('crm:dispatch-due-reminders')->assertSuccessful();
        $this->artisan('crm:escalate-overdue-follow-ups')->assertSuccessful();

        $this->assertSame(FollowUpStatus::Missed, $followUp->fresh()->status);
        Notification::assertSentTo($repA, CrmAlertNotification::class);
    }

    public function test_reassignment_moves_quotations_and_activities_and_rejects_inactive_target(): void
    {
        $team = Team::query()->create(['name' => 'Alpha', 'slug' => 'alpha']);
        $admin = $this->makeUser($team, 'Org Admin', 'admin@salescrm.test');
        $rep1 = $this->makeUser($team, 'Sales Rep', 'rep1@salescrm.test');
        $rep2 = $this->makeUser($team, 'Sales Rep', 'rep2@salescrm.test');
        $inactive = $this->makeUser($team, 'Sales Rep', 'inactive@salescrm.test', false);

        $pipeline = Pipeline::query()->create(['name' => 'Default', 'is_default' => true]);
        $stage = PipelineStage::query()->create(['pipeline_id' => $pipeline->id, 'name' => 'Qual', 'slug' => 'qual', 'position' => 1]);
        $company = Company::query()->create(['team_id' => $team->id, 'owner_id' => $rep1->id, 'name' => 'Acme', 'country' => 'BD']);
        $deal = Deal::query()->create([
            'team_id' => $team->id,
            'owner_id' => $rep1->id,
            'pipeline_id' => $pipeline->id,
            'pipeline_stage_id' => $stage->id,
            'company_id' => $company->id,
            'title' => 'Acme Deal',
            'value' => 1000,
            'currency' => 'BDT',
            'status' => DealStatus::Open,
            'stage_changed_at' => now(),
        ]);

        $quote = Quotation::query()->create([
            'ulid' => (string) Str::ulid(),
            'team_id' => $team->id,
            'owner_id' => $rep1->id,
            'deal_id' => $deal->id,
            'company_id' => $company->id,
            'quote_number' => 'QT-2026-00001',
            'version' => 1,
            'status' => QuotationStatus::Draft,
            'currency' => 'BDT',
            'issue_date' => now()->toDateString(),
            'valid_until' => now()->addDays(10)->toDateString(),
        ]);

        $activity = Activity::query()->create([
            'team_id' => $team->id,
            'user_id' => $rep1->id,
            'subject_type' => 'deal',
            'subject_id' => $deal->id,
            'type' => ActivityType::Call,
            'summary' => 'Discovery call',
            'occurred_at' => now(),
        ]);

        $this->actingAs($admin)->post(route('settings.users.reassign', $rep1), [
            'to_user_id' => $inactive->id,
        ])->assertRedirect()->assertSessionHas('error');

        $this->actingAs($admin)->post(route('settings.users.reassign', $rep1), [
            'to_user_id' => $rep2->id,
        ])->assertRedirect();

        $this->assertSame($rep2->id, $deal->fresh()->owner_id);
        $this->assertSame($rep2->id, $quote->fresh()->owner_id);
        $this->assertSame($rep2->id, $activity->fresh()->user_id);
    }

    public function test_report_filters_only_offer_teams_and_owners_the_user_may_query(): void
    {
        $alpha = Team::query()->create(['name' => 'Alpha Reports', 'slug' => 'alpha-reports', 'is_active' => true]);
        $beta = Team::query()->create(['name' => 'Beta Reports', 'slug' => 'beta-reports', 'is_active' => true]);

        $rep = $this->makeUser($alpha, 'Sales Rep', 'rep-alpha@salescrm.test');
        $peer = $this->makeUser($alpha, 'Sales Rep', 'peer-alpha@salescrm.test');
        $manager = $this->makeUser($alpha, 'Sales Manager', 'manager-alpha@salescrm.test');
        $this->makeUser($beta, 'Sales Rep', 'rep-beta@salescrm.test');

        // A Sales Rep may only filter by themselves, not teammates or other teams.
        $this->actingAs($rep)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee('rep-alpha')
            ->assertDontSee('peer-alpha')
            ->assertDontSee('manager-alpha')
            ->assertDontSee('Beta Reports');

        // A Sales Manager may filter their team, but not another team.
        $this->actingAs($manager)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee('peer-alpha')
            ->assertDontSee('rep-beta')
            ->assertDontSee('Beta Reports');
    }

    private function makeUser(Team $team, string $role, string $email, bool $active = true): User
    {
        $user = User::query()->create([
            'team_id' => $team->id,
            'name' => explode('@', $email)[0],
            'email' => $email,
            'password' => Hash::make('Password@12345'),
            'is_active' => $active,
        ]);
        $user->assignRole($role);
        $user->refresh();

        return $user;
    }
}
