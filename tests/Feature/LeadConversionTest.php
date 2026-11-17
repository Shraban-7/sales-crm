<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class LeadConversionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_sales_rep_can_create_lead_and_disqualify_requires_reason(): void
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

        $this->actingAs($rep)
            ->post(route('leads.store'), [
                'first_name' => 'John',
                'source' => LeadSource::Website->value,
                'status' => LeadStatus::Disqualified->value,
                'email' => 'john@example.com',
            ])
            ->assertSessionHasErrors('disqualified_reason');

        $this->actingAs($rep)
            ->post(route('leads.store'), [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'company_name' => 'Acme Corp',
                'email' => 'jane@acme.test',
                'source' => LeadSource::Website->value,
                'status' => LeadStatus::Qualified->value,
                'score' => 80,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('leads', [
            'email' => 'jane@acme.test',
            'owner_id' => $rep->id,
            'team_id' => $team->id,
        ]);
    }

    public function test_atomic_lead_conversion_creates_company_contact_deal_and_blocks_reconversion(): void
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
        $stage = PipelineStage::query()->create([
            'pipeline_id' => $pipeline->id,
            'name' => 'Qualification',
            'slug' => 'qualification',
            'position' => 1,
            'default_probability' => 25.00,
        ]);

        $lead = Lead::query()->create([
            'team_id' => $team->id,
            'owner_id' => $rep->id,
            'first_name' => 'Kamrul',
            'last_name' => 'Hasan',
            'company_name' => 'Apex Textiles',
            'email' => 'kamrul@apex.test',
            'source' => LeadSource::Referral,
            'status' => LeadStatus::Qualified,
        ]);

        $this->actingAs($rep)
            ->post(route('leads.convert', $lead), [
                'company_name' => 'Apex Textiles',
                'deal_title' => 'Apex CRM Deal',
                'pipeline_id' => $pipeline->id,
                'pipeline_stage_id' => $stage->id,
                'expected_value' => 125000,
                'currency' => 'BDT',
            ])
            ->assertRedirect();

        $lead->refresh();
        $this->assertSame(LeadStatus::Converted, $lead->status);
        $this->assertNotNull($lead->converted_company_id);
        $this->assertNotNull($lead->converted_contact_id);
        $this->assertNotNull($lead->converted_deal_id);

        // Attempting second conversion is rejected
        $this->actingAs($rep)
            ->post(route('leads.convert', $lead), [
                'company_name' => 'Apex Textiles',
                'deal_title' => 'Second Deal',
                'pipeline_id' => $pipeline->id,
                'pipeline_stage_id' => $stage->id,
            ])
            ->assertForbidden();
    }

    public function test_sales_rep_cannot_view_or_convert_lead_from_another_team_idor(): void
    {
        $teamA = Team::query()->create(['name' => 'Team A', 'slug' => 'team-a']);
        $teamB = Team::query()->create(['name' => 'Team B', 'slug' => 'team-b']);

        $repA = User::query()->create([
            'team_id' => $teamA->id,
            'name' => 'Rep A',
            'email' => 'repa@salescrm.test',
            'password' => Hash::make('Password@12345'),
            'is_active' => true,
        ]);
        $repA->assignRole('Sales Rep');

        $repB = User::query()->create([
            'team_id' => $teamB->id,
            'name' => 'Rep B',
            'email' => 'repb@salescrm.test',
            'password' => Hash::make('Password@12345'),
            'is_active' => true,
        ]);
        $repB->assignRole('Sales Rep');

        $leadB = Lead::query()->create([
            'team_id' => $teamB->id,
            'owner_id' => $repB->id,
            'first_name' => 'Secret',
            'email' => 'secret@teamb.test',
            'source' => LeadSource::Website,
            'status' => LeadStatus::New,
        ]);

        $this->actingAs($repA)
            ->get(route('leads.show', $leadB))
            ->assertForbidden();
    }
}
