<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
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

    public function test_guest_can_view_modern_landing_page_at_root(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Sale Fusion CRM');
        $response->assertSee('Turn Inbound Leads into', false);
        $response->assertSee('Test Drive Every Role in Seconds', false);
        $response->assertSee('Capture Leads on the Move', false);
        $response->assertSee('Progressive Web App Included', false);
        $response->assertSee('admin@demo.yourproduct.com');
        $response->assertSee('Tariq Rahman');
        $response->assertSee('Nadia Karim');
        $response->assertSee('10-pipelines-kanban.png', false);
        $response->assertSee('28-mobile-dashboard.png', false);
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
