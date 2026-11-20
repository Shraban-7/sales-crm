<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Tests\TestCase;

final class RouteSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_all_mutating_web_routes_require_authentication_or_are_explicitly_public(): void
    {
        $allowedPublicMutations = [
            'login.store',
            'two-factor.store',
            'two-factor.resend',
            'two-factor.cancel',
            'leads.capture.store',
            'quotations.public.accept',
            'quotations.public.reject',
            'leads.capture.webhook',
            'storage.local.upload',
        ];

        $mutatingMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];
        $routes = RouteFacade::getRoutes();

        $unprotectedRoutes = [];

        /** @var Route $route */
        foreach ($routes as $route) {
            $methods = array_intersect($route->methods(), $mutatingMethods);
            if (empty($methods)) {
                continue;
            }

            $name = $route->getName();
            if ($name !== null && in_array($name, $allowedPublicMutations, true)) {
                continue;
            }

            $middleware = $route->gatherMiddleware();
            $hasAuth = in_array('auth', $middleware, true);

            if (! $hasAuth) {
                $unprotectedRoutes[] = sprintf(
                    '[%s] %s (%s)',
                    implode('|', $methods),
                    $route->uri(),
                    $name ?? 'unnamed'
                );
            }
        }

        $this->assertEmpty(
            $unprotectedRoutes,
            'Found mutating routes lacking auth middleware: '.implode(', ', $unprotectedRoutes)
        );
    }

    public function test_settings_mutating_routes_require_specific_permission_middleware(): void
    {
        $routes = RouteFacade::getRoutes();
        $checkedRoutes = 0;

        /** @var Route $route */
        foreach ($routes as $route) {
            $uri = $route->uri();
            if (! str_starts_with($uri, 'settings/')) {
                continue;
            }

            $methods = array_intersect($route->methods(), ['POST', 'PUT', 'PATCH', 'DELETE']);
            if (empty($methods)) {
                continue;
            }

            $middleware = $route->gatherMiddleware();

            if (str_starts_with($uri, 'settings/users')) {
                $this->assertContains(
                    'can:users.manage',
                    $middleware,
                    "Route {$uri} must have can:users.manage middleware."
                );
                $checkedRoutes++;
            } elseif (str_starts_with($uri, 'settings/teams')) {
                $this->assertContains(
                    'can:teams.manage',
                    $middleware,
                    "Route {$uri} must have can:teams.manage middleware."
                );
                $checkedRoutes++;
            } elseif (str_starts_with($uri, 'settings/webhooks')) {
                $this->assertContains(
                    'can:webhooks.manage',
                    $middleware,
                    "Route {$uri} must have can:webhooks.manage middleware."
                );
                $checkedRoutes++;
            } elseif (str_starts_with($uri, 'settings/stages')) {
                $this->assertContains(
                    'can:pipelines.manage',
                    $middleware,
                    "Route {$uri} must have can:pipelines.manage middleware."
                );
                $checkedRoutes++;
            } elseif (str_starts_with($uri, 'settings/smtp')) {
                $this->assertContains(
                    'can:settings.manage',
                    $middleware,
                    "Route {$uri} must have can:settings.manage middleware."
                );
                $checkedRoutes++;
            } elseif (str_starts_with($uri, 'settings/system')) {
                $this->assertContains(
                    'can:settings.manage',
                    $middleware,
                    "Route {$uri} must have can:settings.manage middleware."
                );
                $checkedRoutes++;
            }
        }

        $this->assertGreaterThan(0, $checkedRoutes, 'Expected to check at least one settings mutating route.');
    }

    public function test_non_admin_cannot_access_settings_write_routes(): void
    {
        $team = Team::query()->create(['name' => 'Team Alpha', 'slug' => 'team-alpha']);
        $rep = User::query()->create([
            'team_id' => $team->id,
            'name' => 'Sales Rep',
            'email' => 'rep@example.com',
            'password' => bcrypt('Password@12345'),
            'is_active' => true,
        ]);
        $rep->assignRole('Sales Rep');

        // Cannot create users
        $this->actingAs($rep)
            ->post(route('settings.users.store'), [
                'name' => 'Hacker User',
                'email' => 'hacker@example.com',
                'password' => 'Password@12345',
                'password_confirmation' => 'Password@12345',
                'team_id' => $team->id,
                'role' => 'Sales Rep',
            ])
            ->assertForbidden();

        // Cannot create teams
        $this->actingAs($rep)
            ->post(route('settings.teams.store'), [
                'name' => 'Unauthorized Team',
            ])
            ->assertForbidden();

        // Cannot create webhooks
        $this->actingAs($rep)
            ->post(route('settings.webhooks.store'), [
                'name' => 'Unauthorized Hook',
                'url' => 'https://example.com/webhook',
                'events' => ['lead.created'],
            ])
            ->assertForbidden();
    }

    public function test_has_visibility_scope_isolates_records_between_teams(): void
    {
        $team1 = Team::query()->create(['name' => 'Team 1', 'slug' => 'team-1']);
        $team2 = Team::query()->create(['name' => 'Team 2', 'slug' => 'team-2']);

        $user1 = User::query()->create([
            'team_id' => $team1->id,
            'name' => 'Rep 1',
            'email' => 'rep1@example.com',
            'password' => bcrypt('Password@12345'),
            'is_active' => true,
        ]);
        $user1->assignRole('Sales Rep');

        $user2 = User::query()->create([
            'team_id' => $team2->id,
            'name' => 'Rep 2',
            'email' => 'rep2@example.com',
            'password' => bcrypt('Password@12345'),
            'is_active' => true,
        ]);
        $user2->assignRole('Sales Rep');

        $lead1 = Lead::query()->create([
            'team_id' => $team1->id,
            'owner_id' => $user1->id,
            'first_name' => 'Secret',
            'last_name' => 'Lead Team 1',
            'email' => 'secret1@example.com',
            'score' => 50,
        ]);

        $lead2 = Lead::query()->create([
            'team_id' => $team2->id,
            'owner_id' => $user2->id,
            'first_name' => 'Secret',
            'last_name' => 'Lead Team 2',
            'email' => 'secret2@example.com',
            'score' => 50,
        ]);

        // User 1 only sees lead 1
        $user1Leads = Lead::query()->visibleTo($user1)->pluck('id')->all();
        $this->assertContains($lead1->id, $user1Leads);
        $this->assertNotContains($lead2->id, $user1Leads);

        // User 2 only sees lead 2
        $user2Leads = Lead::query()->visibleTo($user2)->pluck('id')->all();
        $this->assertContains($lead2->id, $user2Leads);
        $this->assertNotContains($lead1->id, $user2Leads);
    }
}
