<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Exceptions\ImmutableRecordException;
use App\Models\AuditLog;
use App\Models\Team;
use App\Models\User;
use App\Notifications\TwoFactorCodeNotification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class AuthAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_guest_is_redirected_to_login_from_protected_routes(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('leads.index'))->assertRedirect(route('login'));
        $this->get(route('deals.index'))->assertRedirect(route('login'));
    }

    public function test_active_user_can_login_and_audit_log_is_created(): void
    {
        $team = Team::query()->create(['name' => 'Alpha', 'slug' => 'alpha']);
        $user = User::query()->create([
            'team_id' => $team->id,
            'name' => 'Test User',
            'email' => 'test@salescrm.test',
            'password' => Hash::make('Password@12345'),
            'is_active' => true,
        ]);
        $user->assignRole('Sales Rep');
        $user->refresh();

        $response = $this->post(route('login.store'), [
            'email' => 'test@salescrm.test',
            'password' => 'Password@12345',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'event' => 'login_succeeded',
        ]);
    }

    public function test_deactivated_user_is_immediately_logged_out_with_403(): void
    {
        $team = Team::query()->create(['name' => 'Alpha', 'slug' => 'alpha']);
        $user = User::query()->create([
            'team_id' => $team->id,
            'name' => 'Inactive User',
            'email' => 'inactive@salescrm.test',
            'password' => Hash::make('Password@12345'),
            'is_active' => false,
        ]);
        $user->assignRole('Sales Rep');
        $user->refresh();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertForbidden();

        $this->assertGuest();
    }

    public function test_audit_log_is_immutable_and_password_is_redacted(): void
    {
        $team = Team::query()->create(['name' => 'Alpha', 'slug' => 'alpha']);
        $admin = User::query()->create([
            'team_id' => $team->id,
            'name' => 'Org Admin',
            'email' => 'orgadmin@salescrm.test',
            'password' => Hash::make('Password@12345'),
            'is_active' => true,
        ]);
        $admin->assignRole('Org Admin');

        $rep = User::query()->create([
            'team_id' => $team->id,
            'name' => 'Rep',
            'email' => 'rep@salescrm.test',
            'password' => Hash::make('Password@12345'),
            'is_active' => true,
        ]);
        $rep->assignRole('Sales Rep');

        $this->actingAs($rep)
            ->get(route('settings.audit-logs.index'))
            ->assertForbidden();

        $log = AuditLog::query()->create([
            'team_id' => $team->id,
            'user_id' => $admin->id,
            'event' => 'updated',
            'auditable_type' => 'user',
            'auditable_id' => $rep->id,
            'new_values' => ['password' => '[REDACTED]'],
            'created_at' => now(),
        ]);

        $this->expectException(ImmutableRecordException::class);
        $log->delete();
    }

    public function test_two_factor_login_requires_emailed_code_before_authenticating(): void
    {
        $team = Team::query()->create(['name' => 'Alpha', 'slug' => 'alpha']);
        $user = User::query()->create([
            'team_id' => $team->id,
            'name' => 'Secure User',
            'email' => 'secure@salescrm.test',
            'password' => Hash::make('Password@12345'),
            'is_active' => true,
            'two_factor_enabled' => true,
        ]);
        $user->assignRole('Sales Rep');

        Notification::fake();

        $this->post(route('login.store'), [
            'email' => 'secure@salescrm.test',
            'password' => 'Password@12345',
        ])->assertRedirect(route('two-factor.challenge'));

        $this->assertGuest();

        $code = null;
        Notification::assertSentTo(
            $user,
            TwoFactorCodeNotification::class,
            function (TwoFactorCodeNotification $notification) use (&$code): bool {
                $code = $notification->code;

                return true;
            },
        );

        $this->assertNotNull($code);
        $this->assertNotSame('', (string) $user->fresh()->two_factor_code_hash);

        $this->post(route('two-factor.store'), ['code' => $code])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertNull($user->fresh()->two_factor_code_hash);
    }

    public function test_two_factor_rejects_invalid_code(): void
    {
        $team = Team::query()->create(['name' => 'Alpha', 'slug' => 'alpha']);
        $user = User::query()->create([
            'team_id' => $team->id,
            'name' => 'Secure User',
            'email' => 'secure@salescrm.test',
            'password' => Hash::make('Password@12345'),
            'is_active' => true,
            'two_factor_enabled' => true,
        ]);
        $user->assignRole('Sales Rep');

        Notification::fake();

        $this->post(route('login.store'), [
            'email' => 'secure@salescrm.test',
            'password' => 'Password@12345',
        ])->assertRedirect(route('two-factor.challenge'));

        $this->from(route('two-factor.challenge'))
            ->post(route('two-factor.store'), ['code' => '000000'])
            ->assertRedirect(route('two-factor.challenge'))
            ->assertSessionHasErrors('code');

        $this->assertGuest();
    }

    public function test_user_can_enable_two_factor_from_security_settings(): void
    {
        $team = Team::query()->create(['name' => 'Alpha', 'slug' => 'alpha']);
        $user = User::query()->create([
            'team_id' => $team->id,
            'name' => 'Rep',
            'email' => 'rep@salescrm.test',
            'password' => Hash::make('Password@12345'),
            'is_active' => true,
        ]);
        $user->assignRole('Sales Rep');
        $user->refresh();

        $this->actingAs($user)->put(route('settings.security.update'), [
            'two_factor_enabled' => 1,
            'current_password' => 'wrong-password',
        ])->assertSessionHasErrors('current_password');

        $this->assertFalse($user->fresh()->two_factor_enabled);

        $this->actingAs($user)->put(route('settings.security.update'), [
            'two_factor_enabled' => 1,
            'current_password' => 'Password@12345',
        ])->assertRedirect();

        $this->assertTrue($user->fresh()->two_factor_enabled);
    }
}
