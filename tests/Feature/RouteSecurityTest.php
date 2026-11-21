<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Attachment;
use App\Models\Event;
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

    public function test_settings_read_routes_require_admin_role(): void
    {
        $team = Team::query()->create(['name' => 'Sales Team', 'slug' => 'sales-team']);
        $rep = User::query()->create([
            'team_id' => $team->id,
            'name' => 'Regular Rep',
            'email' => 'regularrep@example.com',
            'password' => bcrypt('Password@12345'),
            'is_active' => true,
        ]);
        $rep->assignRole('Sales Rep');

        $manager = User::query()->create([
            'team_id' => $team->id,
            'name' => 'Team Manager',
            'email' => 'manager@example.com',
            'password' => bcrypt('Password@12345'),
            'is_active' => true,
        ]);
        $manager->assignRole('Sales Manager');

        $analyst = User::query()->create([
            'team_id' => $team->id,
            'name' => 'Data Analyst',
            'email' => 'analyst@example.com',
            'password' => bcrypt('Password@12345'),
            'is_active' => true,
        ]);
        $analyst->assignRole('Read-Only Analyst');

        $adminGetRoutes = [
            'settings.users.index',
            'settings.stages.index',
            'settings.smtp.index',
            'settings.system.edit',
            'settings.webhooks.index',
            'settings.audit-logs.index',
        ];

        foreach ([$rep, $manager, $analyst] as $unauthorizedUser) {
            foreach ($adminGetRoutes as $routeName) {
                $this->actingAs($unauthorizedUser)
                    ->get(route($routeName))
                    ->assertForbidden();
            }
        }
    }

    public function test_api_webhook_capture_route_has_signature_middleware(): void
    {
        $routes = RouteFacade::getRoutes();
        $webhookRoute = $routes->getByName('leads.capture.webhook');

        $this->assertNotNull($webhookRoute, 'Route [leads.capture.webhook] must exist.');

        $middleware = $webhookRoute->gatherMiddleware();
        $this->assertContains(
            'App\\Http\\Middleware\\VerifyWebhookSignature',
            $middleware,
            'Route [POST /api/leads/capture] must enforce VerifyWebhookSignature middleware.'
        );
        $this->assertContains(
            'throttle:lead-webhook',
            $middleware,
            'Route [POST /api/leads/capture] must enforce named rate limiting middleware.'
        );
    }

    public function test_attachment_download_and_delete_enforce_cross_team_idor_protection(): void
    {
        $team1 = Team::query()->create(['name' => 'Team Alpha', 'slug' => 'team-alpha']);
        $team2 = Team::query()->create(['name' => 'Team Beta', 'slug' => 'team-beta']);

        $user1 = User::query()->create([
            'team_id' => $team1->id,
            'name' => 'Rep Alpha',
            'email' => 'rep_alpha@example.com',
            'password' => bcrypt('Password@12345'),
            'is_active' => true,
        ]);
        $user1->assignRole('Sales Rep');

        $user2 = User::query()->create([
            'team_id' => $team2->id,
            'name' => 'Rep Beta',
            'email' => 'rep_beta@example.com',
            'password' => bcrypt('Password@12345'),
            'is_active' => true,
        ]);
        $user2->assignRole('Sales Rep');

        $lead = Lead::query()->create([
            'team_id' => $team1->id,
            'owner_id' => $user1->id,
            'first_name' => 'Confidential',
            'last_name' => 'Lead',
            'score' => 50,
        ]);

        $attachment = Attachment::query()->create([
            'team_id' => $team1->id,
            'uploaded_by_id' => $user1->id,
            'attachable_type' => $lead->getMorphClass(),
            'attachable_id' => $lead->id,
            'original_name' => 'secret_contract.pdf',
            'storage_disk' => 'private',
            'storage_path' => 'attachments/secret_contract.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 1024,
            'sha256_checksum' => hash('sha256', 'dummy'),
        ]);

        // User 2 from Team 2 cannot download attachment from Team 1
        $this->actingAs($user2)
            ->get(route('attachments.download', $attachment))
            ->assertForbidden();

        // User 2 from Team 2 cannot delete attachment from Team 1
        $this->actingAs($user2)
            ->delete(route('attachments.destroy', $attachment))
            ->assertForbidden();
    }

    public function test_mobile_capture_rejects_cross_team_event_idor(): void
    {
        $team1 = Team::query()->create(['name' => 'Team Alpha', 'slug' => 'team-alpha']);
        $team2 = Team::query()->create(['name' => 'Team Beta', 'slug' => 'team-beta']);

        $user2 = User::query()->create([
            'team_id' => $team2->id,
            'name' => 'Rep Beta',
            'email' => 'rep_beta2@example.com',
            'password' => bcrypt('Password@12345'),
            'is_active' => true,
        ]);
        $user2->assignRole('Sales Rep');

        $eventTeam1 = Event::query()->create([
            'team_id' => $team1->id,
            'created_by_id' => 1,
            'name' => 'Team 1 Exclusive Trade Show',
        ]);

        // Rep from Team 2 cannot capture a lead into Team 1's event
        $this->actingAs($user2)
            ->post(route('mobile.capture.store'), [
                'first_name' => 'Hijacked',
                'last_name' => 'Lead',
                'event_id' => $eventTeam1->id,
            ])
            ->assertForbidden();
    }

    public function test_owner_check_fails_closed_on_default_email_and_requires_super_admin(): void
    {
        config(['crm.owner_email' => 'owner@yourdomain.com']);

        $regularUser = User::query()->create([
            'name' => 'Default Owner',
            'email' => 'owner@yourdomain.com',
            'password' => bcrypt('Password@12345'),
            'is_active' => true,
        ]);
        $regularUser->assignRole('Sales Rep');

        // Shipped placeholder email must fail closed
        $this->assertFalse($regularUser->isOwner());

        config(['crm.owner_email' => 'realowner@company.com']);
        $realUser = User::query()->create([
            'name' => 'Real Owner',
            'email' => 'realowner@company.com',
            'password' => bcrypt('Password@12345'),
            'is_active' => true,
        ]);
        $realUser->assignRole('Sales Rep');

        // Email matches but lacking Super Admin role must fail closed
        $this->assertFalse($realUser->isOwner());

        // Email matches AND has Super Admin role succeeds
        $realUser->assignRole('Super Admin');
        $this->assertTrue($realUser->isOwner());
    }

    public function test_public_quotation_routes_enforce_signed_middleware(): void
    {
        $routes = RouteFacade::getRoutes();
        $signedRoutes = [
            'quotations.public.show',
            'quotations.public.accept',
            'quotations.public.reject',
        ];

        foreach ($signedRoutes as $routeName) {
            $route = $routes->getByName($routeName);
            $this->assertNotNull($route, "Route [{$routeName}] must exist.");

            $middleware = $route->gatherMiddleware();
            $this->assertContains(
                'signed',
                $middleware,
                "Route [{$routeName}] must enforce cryptographic signed URL middleware."
            );
        }
    }

    public function test_read_only_analyst_and_viewer_are_blocked_from_mutating_crm_routes(): void
    {
        $team = Team::query()->create(['name' => 'Analytics Team', 'slug' => 'analytics-team']);

        $analyst = User::query()->create([
            'team_id' => $team->id,
            'name' => 'Data Analyst',
            'email' => 'analyst.guard@example.com',
            'password' => bcrypt('Password@12345'),
            'is_active' => true,
        ]);
        $analyst->assignRole('Read-Only Analyst');

        $viewer = User::query()->create([
            'team_id' => $team->id,
            'name' => 'CRM Viewer',
            'email' => 'viewer.guard@example.com',
            'password' => bcrypt('Password@12345'),
            'is_active' => true,
        ]);
        $viewer->assignRole('Viewer');

        foreach ([$analyst, $viewer] as $readOnlyUser) {
            // Blocked from creating leads at the route guard level
            $this->actingAs($readOnlyUser)
                ->post(route('leads.store'), [
                    'first_name' => 'Blocked',
                    'last_name' => 'Lead',
                ])
                ->assertForbidden();

            // Blocked from creating deals
            $this->actingAs($readOnlyUser)
                ->post(route('deals.store'), [
                    'title' => 'Blocked Deal',
                ])
                ->assertForbidden();

            // Blocked from creating quotations
            $this->actingAs($readOnlyUser)
                ->post(route('quotations.store'), [
                    'title' => 'Blocked Quote',
                ])
                ->assertForbidden();
        }
    }

    public function test_sales_rep_is_blocked_from_manager_and_admin_guards(): void
    {
        $team = Team::query()->create(['name' => 'Rep Team', 'slug' => 'rep-team']);

        $rep = User::query()->create([
            'team_id' => $team->id,
            'name' => 'Sales Representative',
            'email' => 'rep.guard@example.com',
            'password' => bcrypt('Password@12345'),
            'is_active' => true,
        ]);
        $rep->assignRole('Sales Rep');

        // Blocked by manager guard
        $this->actingAs($rep)
            ->get(route('imports.bulk'))
            ->assertForbidden();

        // Blocked by admin guard
        $this->actingAs($rep)
            ->get(route('settings.users.index'))
            ->assertForbidden();

        $this->actingAs($rep)
            ->post(route('pipelines.store'), ['name' => 'Hacked Pipeline'])
            ->assertForbidden();
    }

    public function test_sales_manager_is_permitted_on_manager_routes_but_blocked_from_admin_settings(): void
    {
        $team = Team::query()->create(['name' => 'Manager Team', 'slug' => 'manager-team']);

        $manager = User::query()->create([
            'team_id' => $team->id,
            'name' => 'Sales Manager User',
            'email' => 'manager.guard@example.com',
            'password' => bcrypt('Password@12345'),
            'is_active' => true,
        ]);
        $manager->assignRole('Sales Manager');

        // Permitted on manager imports
        $this->actingAs($manager)
            ->get(route('imports.bulk'))
            ->assertOk();

        // Blocked from admin settings
        $this->actingAs($manager)
            ->get(route('settings.users.index'))
            ->assertForbidden();

        $this->actingAs($manager)
            ->get(route('settings.smtp.index'))
            ->assertForbidden();
    }
}
