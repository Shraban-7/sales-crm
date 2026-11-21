<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\Team;
use App\Models\User;
use App\Services\Settings\SystemSettingService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $team = Team::query()->create([
            'name' => 'Demo Team',
            'slug' => 'demo-team',
            'is_active' => true,
        ]);

        $this->user = User::factory()->create([
            'team_id' => $team->id,
            'email' => 'admin@salescrm.test',
            'is_active' => true,
        ]);
        $this->user->assignRole('Super Admin');
    }

    public function test_when_demo_mode_is_active_guest_views_landing_page_at_root(): void
    {
        config(['crm.demo_mode' => true]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Sale Fusion CRM');
        $response->assertSee('Turn Inbound Leads into', false);
        $response->assertSee('Test Drive Every Role in Seconds', false);
        $response->assertSee('Capture Leads on the Move', false);
        $response->assertSee('Progressive Web App Included', false);
        $response->assertSee('demosuperadmin@salescrm.test');
        $response->assertSee('Tariq Rahman');
        $response->assertSee('Nadia Karim');
        $response->assertSee('10-pipelines-kanban.png', false);
        $response->assertSee('28-mobile-dashboard.png', false);
    }

    public function test_when_client_purchases_crm_root_redirects_directly_to_login(): void
    {
        // When client purchases and installs CRM (CRM_DEMO_MODE=false), no marketing landing page is shown
        config(['crm.demo_mode' => false]);

        $response = $this->get(route('home'));

        $response->assertRedirect(route('login'));
    }

    public function test_login_page_demo_credentials_panel_controlled_by_demo_mode(): void
    {
        // 1. Purchased client install: login page has NO demo quick-fill panel or pre-filled credentials
        config(['crm.demo_mode' => false]);
        $clientResponse = $this->get(route('login'));
        $clientResponse->assertOk();
        $clientResponse->assertDontSee('Demo Accounts Quick-Fill');
        $clientResponse->assertDontSee('Demo Mode Active');
        $clientResponse->assertDontSee('value="demosuperadmin@salescrm.test"', false);
        $clientResponse->assertDontSee('&larr; Back to Landing Page', false);

        // 2. Marketplace live demo mode: login page displays 1-click quick-fill panel, demo notice & pre-filled credentials
        config(['crm.demo_mode' => true]);
        $demoResponse = $this->get(route('login'));
        $demoResponse->assertOk();
        $demoResponse->assertSee('Demo Mode Active');
        $demoResponse->assertSee('Seeder Demo Credentials');
        $demoResponse->assertSee('Demo Accounts Quick-Fill');
        $demoResponse->assertSee('demosuperadmin@salescrm.test');
        $demoResponse->assertSee('Super Admin');
        $demoResponse->assertSee('Sales Manager');
        $demoResponse->assertSee('value="demosuperadmin@salescrm.test"', false);
        $demoResponse->assertSee('value="password"', false);
        $demoResponse->assertSee('&larr; Back to Landing Page', false);
    }

    public function test_database_system_settings_demo_mode_dynamically_controls_login_and_landing_page(): void
    {
        // 1. Direct DB setting demo_mode = 0 (client purchase / demo disabled)
        SystemSetting::set('demo_mode', false, 'general', 'boolean');
        app(SystemSettingService::class)->applyToConfig();

        $rootResponse = $this->get(route('home'));
        $rootResponse->assertRedirect(route('login'));

        $loginResponse = $this->get(route('login'));
        $loginResponse->assertOk();
        $loginResponse->assertDontSee('Demo Mode Active');
        $loginResponse->assertDontSee('Demo Accounts Quick-Fill');
        $loginResponse->assertDontSee('demosuperadmin@salescrm.test');
        $loginResponse->assertDontSee('value="password"', false);

        // 2. Direct DB setting demo_mode = 1 (demo enabled)
        SystemSetting::set('demo_mode', true, 'general', 'boolean');
        app(SystemSettingService::class)->applyToConfig();

        $demoRootResponse = $this->get(route('home'));
        $demoRootResponse->assertOk();
        $demoRootResponse->assertSee('Turn Inbound Leads into', false);

        $demoLoginResponse = $this->get(route('login'));
        $demoLoginResponse->assertOk();
        $demoLoginResponse->assertSee('Demo Mode Active');
        $demoLoginResponse->assertSee('Demo Accounts Quick-Fill');
        $demoLoginResponse->assertSee('demosuperadmin@salescrm.test');
        $demoLoginResponse->assertSee('value="password"', false);
    }

    public function test_authenticated_user_visiting_root_is_redirected_to_dashboard(): void
    {
        $response = $this->actingAs($this->user)->get(route('home'));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_authenticated_user_can_access_dashboard_and_welcome_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Recent Deals');

        $welcomeResponse = $this->actingAs($this->user)->get(route('landing'));
        $welcomeResponse->assertOk();
        $welcomeResponse->assertSee('Open Dashboard');
    }

    public function test_guest_is_redirected_from_dashboard_to_login(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }
}
