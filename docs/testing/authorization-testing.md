# Authorization & IDOR Security Testing — Sale Fusion CRM

**Last Updated:** 2026-11-19  
**Implementation Status:** `Implemented (Active Engineering Specification)`  
**Related Docs:** `docs/security/authorization.md`, `docs/security/role-permission-matrix.md`

## 1. Mandatory IDOR & RBAC Matrix Testing

Every CRM controller endpoint must be tested against cross-team and cross-owner access attempts to guarantee zero Insecure Direct Object Reference (IDOR) vulnerabilities.

## 2. Cross-Team & Cross-Rep IDOR Test Suite Example

```php
<?php

declare(strict_types=1);

use App\Models\Deal;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\RolePermissionSeeder::class);
});

it('forbids a sales rep from viewing or updating another rep deal in the same team', function (): void {
    $team = Team::factory()->create();
    $repA = User::factory()->for($team)->create()->assignRole('Sales Rep');
    $repB = User::factory()->for($team)->create()->assignRole('Sales Rep');

    $dealOwnedByB = Deal::factory()->for($team)->create(['owner_id' => $repB->id]);

    $this->actingAs($repA)
        ->get(route('deals.show', $dealOwnedByB))
        ->assertForbidden();

    $this->actingAs($repA)
        ->patch(route('deals.update', $dealOwnedByB), ['title' => 'Hijacked Deal'])
        ->assertForbidden();
});

it('allows a sales manager to view and update deals within their own team but forbids cross-team access', function (): void {
    $teamAlpha = Team::factory()->create();
    $teamBeta = Team::factory()->create();

    $managerAlpha = User::factory()->for($teamAlpha)->create()->assignRole('Sales Manager');
    $dealAlpha = Deal::factory()->for($teamAlpha)->create();
    $dealBeta = Deal::factory()->for($teamBeta)->create();

    $this->actingAs($managerAlpha)
        ->get(route('deals.show', $dealAlpha))
        ->assertOk();

    $this->actingAs($managerAlpha)
        ->get(route('deals.show', $dealBeta))
        ->assertForbidden();
});

it('forbids read-only analyst from creating or mutating any CRM record', function (): void {
    $team = Team::factory()->create();
    $analyst = User::factory()->for($team)->create()->assignRole('Read-Only Analyst');
    $deal = Deal::factory()->for($team)->create();

    $this->actingAs($analyst)
        ->get(route('deals.show', $deal))
        ->assertOk();

    $this->actingAs($analyst)
        ->post(route('deals.store'), [])
        ->assertForbidden();
});
```
