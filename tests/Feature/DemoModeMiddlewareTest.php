<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoModeMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $owner;

    protected Team $team;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $team = Team::query()->create([
            'name' => 'Demo Team',
            'slug' => 'demo-team',
            'is_active' => true,
        ]);
        $this->team = $team;

        $this->admin = User::factory()->create([
            'team_id' => $team->id,
            'email' => 'demosuperadmin@salescrm.test',
            'is_active' => true,
        ]);
        $this->admin->assignRole('Super Admin');

        $this->owner = User::factory()->create([
            'team_id' => $team->id,
            'email' => 'owner@verified-crm.test',
            'is_active' => true,
        ]);
        $this->owner->assignRole('Super Admin');

        config(['crm.owner_email' => 'owner@verified-crm.test']);
    }

    public function test_when_demo_mode_is_disabled_mutations_are_permitted(): void
    {
        config(['crm.demo_mode' => false]);

        $response = $this->actingAs($this->admin)
            ->from(route('settings.system.edit'))
            ->put(route('settings.system.update'), [
                'company_name' => 'Updated Normal CRM',
                'company_email' => 'contact@crm.test',
                'company_phone' => '+1 555-0100',
                'company_address' => '123 Business St',
                'default_currency' => 'USD',
                'currency_position' => 'before',
                'default_country' => 'US',
                'timezone' => 'UTC',
                'date_format' => 'Y-m-d',
                'quotation_number_prefix' => 'QT',
                'proposal_number_prefix' => 'PR',
                'discount_approval_threshold' => 15.00,
                'quote_public_link_days' => 30,
                'deal_rotting_days_default' => 14,
                'records_per_page' => 20,
            ]);

        $response->assertRedirect(route('settings.system.edit'));
        $response->assertSessionHas('status');
        $this->assertDatabaseHas('system_settings', [
            'key' => 'company_name',
            'value' => 'Updated Normal CRM',
        ]);
    }

    public function test_when_demo_mode_is_enabled_demo_users_can_explore_via_get_requests(): void
    {
        config(['crm.demo_mode' => true]);

        // Demo user can freely view and explore settings and features
        $response = $this->actingAs($this->admin)->get(route('settings.system.edit'));

        $response->assertOk();
        $response->assertSee('Live Demo Mode');
        $response->assertSee('Protected in Demo Mode');
    }

    public function test_when_demo_mode_is_enabled_demo_users_are_blocked_from_modifying_settings(): void
    {
        config(['crm.demo_mode' => true]);

        // Web form mutation attempt
        $response = $this->actingAs($this->admin)->put(route('settings.system.update'), [
            'company_name' => 'Hacked Company Name',
            'default_currency' => 'EUR',
            'currency_position' => 'after',
            'default_country' => 'FR',
            'timezone' => 'Europe/Paris',
            'date_format' => 'd/m/Y',
            'quotation_number_prefix' => 'HACK',
            'proposal_number_prefix' => 'HACK',
            'discount_approval_threshold' => 50.00,
            'quote_public_link_days' => 1,
            'deal_rotting_days_default' => 1,
            'records_per_page' => 10,
        ]);

        $response->assertSessionHas('warning');
        $this->assertDatabaseMissing('system_settings', [
            'key' => 'company_name',
            'value' => 'Hacked Company Name',
        ]);

        // AJAX / JSON mutation attempt returns 403 Forbidden
        $jsonResponse = $this->actingAs($this->admin)
            ->putJson(route('settings.system.update'), [
                'company_name' => 'Hacked Via API',
            ]);

        $jsonResponse->assertForbidden();
        $jsonResponse->assertJsonFragment([
            'message' => 'Action disabled in live demo mode. In your private installation, you have complete administrative control.',
        ]);
    }

    public function test_when_demo_mode_is_enabled_verified_owner_can_modify_settings(): void
    {
        config(['crm.demo_mode' => true]);

        $response = $this->actingAs($this->owner)
            ->from(route('settings.system.edit'))
            ->put(route('settings.system.update'), [
                'company_name' => 'Verified Owner Official CRM',
                'company_email' => 'owner@verified-crm.test',
                'company_phone' => '+1 555-9999',
                'company_address' => '999 Executive Way',
                'default_currency' => 'GBP',
                'currency_position' => 'before',
                'default_country' => 'GB',
                'timezone' => 'Europe/London',
                'date_format' => 'd/m/Y',
                'quotation_number_prefix' => 'QTGB',
                'proposal_number_prefix' => 'PRGB',
                'discount_approval_threshold' => 10.00,
                'quote_public_link_days' => 45,
                'deal_rotting_days_default' => 21,
                'records_per_page' => 50,
            ]);

        $response->assertRedirect(route('settings.system.edit'));
        $response->assertSessionHas('status');
        $this->assertDatabaseHas('system_settings', [
            'key' => 'company_name',
            'value' => 'Verified Owner Official CRM',
        ]);
    }

    public function test_when_demo_mode_is_enabled_normal_users_do_not_see_demo_banner_and_can_mutate(): void
    {
        config(['crm.demo_mode' => true]);

        $normalUser = User::factory()->create([
            'team_id' => $this->team->id,
            'email' => 'superadmin@salescrm.test',
            'is_active' => true,
        ]);
        $normalUser->assignRole('Super Admin');

        // Normal user visiting dashboard or settings does NOT see demo mode banner
        $this->actingAs($normalUser)->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Live Demo Account')
            ->assertDontSee('Demo Role');

        $this->actingAs($normalUser)->get(route('settings.system.edit'))
            ->assertOk()
            ->assertDontSee('Live Demo Mode &mdash;', false);

        // Normal user can perform mutations even when demo_mode is true
        $response = $this->actingAs($normalUser)
            ->from(route('settings.system.edit'))
            ->put(route('settings.system.update'), [
                'company_name' => 'Normal Admin Production CRM',
                'company_email' => 'admin@salescrm.test',
                'company_phone' => '+1 555-0100',
                'company_address' => '123 Business St',
                'default_currency' => 'USD',
                'currency_position' => 'before',
                'default_country' => 'US',
                'timezone' => 'UTC',
                'date_format' => 'Y-m-d',
                'quotation_number_prefix' => 'QT',
                'proposal_number_prefix' => 'PR',
                'discount_approval_threshold' => 15.00,
                'quote_public_link_days' => 30,
                'deal_rotting_days_default' => 14,
                'records_per_page' => 20,
            ]);

        $response->assertRedirect(route('settings.system.edit'));
        $response->assertSessionHas('status');
        $this->assertDatabaseHas('system_settings', [
            'key' => 'company_name',
            'value' => 'Normal Admin Production CRM',
        ]);
    }
}
