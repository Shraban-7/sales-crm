<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\Team;
use App\Models\User;
use App\Services\Settings\SystemSettingService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class SystemSettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $rep;

    private Team $team;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->team = Team::query()->create(['name' => 'Main Team', 'slug' => 'main-team']);

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
    }

    public function test_guests_and_unauthorized_users_cannot_access_system_settings(): void
    {
        $this->get(route('settings.system.edit'))->assertRedirect(route('login'));
        $this->put(route('settings.system.update'), [])->assertRedirect(route('login'));

        $this->actingAs($this->rep)->get(route('settings.system.edit'))->assertForbidden();
        $this->actingAs($this->rep)->put(route('settings.system.update'), [])->assertForbidden();
    }

    public function test_admin_can_view_and_update_system_settings_with_logo_and_audit(): void
    {
        Storage::fake('public');

        // View settings page
        $viewResponse = $this->actingAs($this->admin)->get(route('settings.system.edit'));
        $viewResponse->assertOk();
        $viewResponse->assertSee('System &amp; Site Settings', false);
        $viewResponse->assertSee('General &amp; Branding Settings', false);

        // Upload logo and update settings
        $logo = UploadedFile::fake()->image('company_logo.png', 400, 100);

        $updateResponse = $this->actingAs($this->admin)->put(route('settings.system.update'), [
            'company_name' => 'Acme Global CRM',
            'company_tagline' => 'Accelerating Enterprise Sales',
            'company_email' => 'billing@acmeglobal.test',
            'company_phone' => '+1 (555) 999-8888',
            'company_address' => '77 Market St, Suite 200, Austin, TX 78701',
            'company_tax_id' => 'TX-99887766',
            'company_logo' => $logo,
            'default_currency' => 'EUR',
            'currency_position' => 'after',
            'default_country' => 'DE',
            'timezone' => 'Europe/Berlin',
            'date_format' => 'd/m/Y',
            'quotation_number_prefix' => 'QTEU',
            'proposal_number_prefix' => 'PREU',
            'discount_approval_threshold' => 20.00,
            'quote_public_link_days' => 45,
            'deal_rotting_days_default' => 21,
            'lead_round_robin' => '1',
            'demo_mode' => '1',
            'records_per_page' => 50,
        ]);

        $updateResponse->assertRedirect();
        $updateResponse->assertSessionHas('status');

        // Verify values via Model & Service
        $this->assertSame('Acme Global CRM', SystemSetting::get('company_name'));
        $this->assertSame('EUR', SystemSetting::get('default_currency'));
        $this->assertSame(20.0, (float) SystemSetting::get('discount_approval_threshold'));
        $this->assertSame(45, (int) SystemSetting::get('quote_public_link_days'));
        $this->assertTrue((bool) SystemSetting::get('lead_round_robin'));
        $this->assertTrue((bool) SystemSetting::get('demo_mode'));
        $this->assertSame(50, (int) SystemSetting::get('records_per_page'));

        $service = app(SystemSettingService::class);
        $this->assertSame('Acme Global CRM', $service->get('company_name'));
        $this->assertSame('EUR', config('crm.currency'));
        $this->assertTrue((bool) config('crm.demo_mode'));

        // Verify Audit Log
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'system_settings_updated',
            'user_id' => $this->admin->id,
        ]);
    }
}
