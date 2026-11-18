# Feature & HTTP Workflow Testing — Sale Fusion CRM

**Last Updated:** 2026-11-19  
**Implementation Status:** `Implemented (Active Engineering Specification)`  
**Related Docs:** `docs/testing/testing-strategy.md`, `docs/modules/leads.md`, `docs/modules/deals-and-opportunities.md`

## 1. Feature Test Structure (`tests/Feature/`)

Feature tests exercise the full [Laravel 12](https://laravel.com/framework/docs/12.x/configuration) HTTP stack: Routing -> Middleware -> FormRequest -> Policy -> Controller -> Action/Service -> MySQL -> Blade/Redirect response.

## 2. Lead Atomic Conversion Feature Test

```php
<?php

declare(strict_types=1);

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('atomically converts a qualified lead into company, contact, and deal', function (): void {
    $this->seed(\Database\Seeders\RolePermissionSeeder::class);

    $team = Team::factory()->create();
    $rep = User::factory()->for($team)->create();
    $rep->assignRole('Sales Rep');

    $pipeline = Pipeline::factory()->for($team)->create(['is_default' => true]);
    $stage = PipelineStage::factory()->for($pipeline)->create(['position' => 1, 'default_probability' => 25]);

    $lead = Lead::factory()->for($team)->create([
        'owner_id' => $rep->id,
        'first_name' => 'Tanvir',
        'last_name' => 'Rahman',
        'company_name' => 'Apex Textiles Ltd',
        'email' => 'tanvir@apextextiles.bd',
        'status' => LeadStatus::Qualified,
    ]);

    $response = $this->actingAs($rep)->post(route('leads.convert', $lead), [
        'company_name' => 'Apex Textiles Ltd',
        'deal_title' => 'Apex ERP & CRM Rollout',
        'pipeline_id' => $pipeline->id,
        'pipeline_stage_id' => $stage->id,
        'expected_value' => '450000.00',
        'currency' => 'BDT',
        'expected_close_date' => now()->addDays(30)->toDateString(),
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('companies', [
        'team_id' => $team->id,
        'name' => 'Apex Textiles Ltd',
    ]);

    $this->assertDatabaseHas('contacts', [
        'team_id' => $team->id,
        'email' => 'tanvir@apextextiles.bd',
    ]);

    $this->assertDatabaseHas('deals', [
        'team_id' => $team->id,
        'owner_id' => $rep->id,
        'title' => 'Apex ERP & CRM Rollout',
        'value' => '450000.00',
    ]);

    expect($lead->fresh()->status)->toBe(LeadStatus::Converted)
        ->and($lead->fresh()->converted_deal_id)->not->toBeNull();
});
```
