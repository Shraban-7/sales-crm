<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\DealStatus;
use App\Models\Company;
use App\Models\Deal;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class DealAndPipelineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_moving_deal_stage_logs_history_and_requires_lost_reason(): void
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
        $stage1 = PipelineStage::query()->create([
            'pipeline_id' => $pipeline->id,
            'name' => 'Qualification',
            'slug' => 'qualification',
            'position' => 1,
            'default_probability' => 25.00,
        ]);
        $stageLost = PipelineStage::query()->create([
            'pipeline_id' => $pipeline->id,
            'name' => 'Closed Lost',
            'slug' => 'closed-lost',
            'position' => 2,
            'default_probability' => 0.00,
            'is_lost_stage' => true,
        ]);

        $company = Company::query()->create([
            'team_id' => $team->id,
            'owner_id' => $rep->id,
            'name' => 'Acme',
            'country' => 'BD',
        ]);

        $deal = Deal::query()->create([
            'team_id' => $team->id,
            'owner_id' => $rep->id,
            'pipeline_id' => $pipeline->id,
            'pipeline_stage_id' => $stage1->id,
            'company_id' => $company->id,
            'title' => 'Acme Deal',
            'value' => 100000.00,
            'currency' => 'BDT',
            'probability' => 25.00,
            'status' => DealStatus::Open,
            'stage_changed_at' => now()->subHours(2),
        ]);

        // Missing lost_reason returns 422
        $this->actingAs($rep)
            ->patchJson(route('deals.move-stage', $deal), [
                'pipeline_stage_id' => $stageLost->id,
            ])
            ->assertStatus(422);

        // With lost_reason succeeds
        $this->actingAs($rep)
            ->patchJson(route('deals.move-stage', $deal), [
                'pipeline_stage_id' => $stageLost->id,
                'lost_reason' => 'Budget frozen',
            ])
            ->assertOk();

        $deal->refresh();
        $this->assertSame(DealStatus::Lost, $deal->status);
        $this->assertSame('0.00', (string) $deal->probability);
        $this->assertDatabaseHas('deal_stage_histories', [
            'deal_id' => $deal->id,
            'from_stage_id' => $stage1->id,
            'to_stage_id' => $stageLost->id,
        ]);
    }

    public function test_org_admin_can_reorder_pipeline_stages_atomically(): void
    {
        $team = Team::query()->create(['name' => 'Alpha', 'slug' => 'alpha']);
        $admin = User::query()->create([
            'team_id' => $team->id,
            'name' => 'Org Admin',
            'email' => 'admin@salescrm.test',
            'password' => Hash::make('Password@12345'),
            'is_active' => true,
        ]);
        $admin->assignRole('Org Admin');

        $pipeline = Pipeline::query()->create(['name' => 'Enterprise']);
        $s1 = PipelineStage::query()->create(['pipeline_id' => $pipeline->id, 'name' => 'S1', 'slug' => 's1', 'position' => 1]);
        $s2 = PipelineStage::query()->create(['pipeline_id' => $pipeline->id, 'name' => 'S2', 'slug' => 's2', 'position' => 2]);

        $this->actingAs($admin)
            ->postJson(route('pipelines.stages.reorder', $pipeline), [
                'stage_ids' => [$s2->id, $s1->id],
            ])
            ->assertOk();

        $this->assertSame(1, $s2->fresh()->position);
        $this->assertSame(2, $s1->fresh()->position);
    }
}
