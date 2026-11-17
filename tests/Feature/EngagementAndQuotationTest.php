<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ActivityType;
use App\Enums\DealStatus;
use App\Enums\FollowUpChannel;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Enums\QuotationStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Activity;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Quotation;
use App\Models\Reminder;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class EngagementAndQuotationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_logging_call_on_new_lead_transitions_to_contacted_and_system_activity_is_immutable(): void
    {
        $team = Team::query()->create(['name' => 'Alpha', 'slug' => 'alpha']);
        $rep = User::query()->create([
            'team_id' => $team->id,
            'name' => 'Rep',
            'email' => 'rep@salescrm.test',
            'password' => Hash::make('Password@12345'),
            'is_active' => true,
        ]);
        $rep->assignRole('Sales Rep');

        $lead = Lead::query()->create([
            'team_id' => $team->id,
            'owner_id' => $rep->id,
            'first_name' => 'Test',
            'email' => 'test@lead.test',
            'source' => LeadSource::Website,
            'status' => LeadStatus::New,
        ]);

        $this->actingAs($rep)->post(route('activities.store'), [
            'subject_type' => 'lead',
            'subject_id' => $lead->id,
            'type' => 'call',
            'summary' => 'Intro discovery call',
            'occurred_at' => now()->toDateTimeString(),
        ])->assertRedirect();

        $this->assertSame(LeadStatus::Contacted, $lead->fresh()->status);

        $sysActivity = Activity::query()->create([
            'team_id' => $team->id,
            'user_id' => $rep->id,
            'subject_type' => 'lead',
            'subject_id' => $lead->id,
            'type' => ActivityType::System,
            'summary' => 'System log',
            'occurred_at' => now(),
        ]);

        $this->actingAs($rep)
            ->delete(route('activities.destroy', $sysActivity))
            ->assertForbidden();
    }

    public function test_do_not_contact_blocks_follow_up_and_due_reminder_dispatches_once(): void
    {
        $team = Team::query()->create(['name' => 'Alpha', 'slug' => 'alpha']);
        $rep = User::query()->create([
            'team_id' => $team->id,
            'name' => 'Rep',
            'email' => 'rep@salescrm.test',
            'password' => Hash::make('Password@12345'),
            'is_active' => true,
        ]);
        $rep->assignRole('Sales Rep');

        $contact = Contact::query()->create([
            'team_id' => $team->id,
            'owner_id' => $rep->id,
            'first_name' => 'Opt',
            'last_name' => 'Out',
            'do_not_contact' => true,
        ]);

        $this->actingAs($rep)->post(route('follow-ups.store'), [
            'subject_type' => 'contact',
            'subject_id' => $contact->id,
            'channel' => FollowUpChannel::Email->value,
            'scheduled_at' => now()->addDay()->toDateTimeString(),
            'objective' => 'Follow up',
        ])->assertSessionHasErrors('subject_id');

        $task = Task::query()->create([
            'team_id' => $team->id,
            'assigned_to_id' => $rep->id,
            'created_by_id' => $rep->id,
            'taskable_type' => 'contact',
            'taskable_id' => $contact->id,
            'title' => 'Call back',
            'priority' => TaskPriority::High,
            'status' => TaskStatus::Pending,
            'due_at' => now()->addHour(),
        ]);

        $reminder = Reminder::query()->create([
            'user_id' => $rep->id,
            'remindable_type' => 'task',
            'remindable_id' => $task->id,
            'remind_at' => now()->subMinute(),
            'channel' => 'database',
        ]);

        $this->artisan('crm:dispatch-due-reminders')->assertSuccessful();
        $this->assertNotNull($reminder->fresh()->sent_at);
        $this->assertSame(1, $rep->notifications()->count());

        // Second run is idempotent
        $this->artisan('crm:dispatch-due-reminders')->assertSuccessful();
        $this->assertSame(1, $rep->notifications()->count());
    }

    public function test_quotation_calculates_server_totals_and_accept_syncs_deal_value(): void
    {
        $team = Team::query()->create(['name' => 'Alpha', 'slug' => 'alpha']);
        $rep = User::query()->create([
            'team_id' => $team->id,
            'name' => 'Rep',
            'email' => 'rep@salescrm.test',
            'password' => Hash::make('Password@12345'),
            'is_active' => true,
        ]);
        $rep->assignRole('Sales Rep');

        $pipeline = Pipeline::query()->create(['name' => 'Default', 'is_default' => true]);
        $stage = PipelineStage::query()->create(['pipeline_id' => $pipeline->id, 'name' => 'Proposal', 'slug' => 'proposal', 'position' => 1]);
        $company = Company::query()->create(['team_id' => $team->id, 'owner_id' => $rep->id, 'name' => 'Acme', 'country' => 'BD']);
        $deal = Deal::query()->create([
            'team_id' => $team->id,
            'owner_id' => $rep->id,
            'pipeline_id' => $pipeline->id,
            'pipeline_stage_id' => $stage->id,
            'company_id' => $company->id,
            'title' => 'Acme Deal',
            'value' => 1000.00,
            'currency' => 'BDT',
            'status' => DealStatus::Open,
            'stage_changed_at' => now(),
        ]);

        $this->actingAs($rep)->post(route('quotations.store'), [
            'deal_id' => $deal->id,
            'currency' => 'BDT',
            'issue_date' => now()->toDateString(),
            'valid_until' => now()->addDays(15)->toDateString(),
            'grand_total' => 1.00, // Tampered client value must be ignored
            'items' => [
                [
                    'item_name' => 'CRM License',
                    'quantity' => 2,
                    'unit_price' => 1000,
                    'discount_percent' => 10,
                    'tax_percent' => 15,
                ],
            ],
        ])->assertRedirect();

        /** @var Quotation $quote */
        $quote = Quotation::query()->firstOrFail();
        // Gross: 2000, Discount 10%: 200, Taxable: 1800, Tax 15%: 270, Grand: 2070.00
        $this->assertSame('2070.00', (string) $quote->grand_total);

        $this->actingAs($rep)->post(route('quotations.accept', $quote))->assertRedirect();
        $this->assertSame(QuotationStatus::Accepted, $quote->fresh()->status);
        $this->assertSame('2070.00', (string) $deal->fresh()->value);
    }
}
