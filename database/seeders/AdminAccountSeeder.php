<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use App\Support\SecuritySecret;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Main Administrator Account Seeder
 *
 * Provisions the primary superadmin@salescrm.test account with full system access.
 * Password: Set via CRM_ADMIN_PASSWORD in .env (or generated secure secret).
 */
class AdminAccountSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction() && ! filled(config('crm.admin_password'))) {
            $this->command?->error('CRM_ADMIN_PASSWORD must be set in .env before seeding in production. Aborting.');

            return;
        }

        $enterpriseTeam = Team::query()->firstOrCreate(
            ['slug' => 'enterprise-sales'],
            [
                'name' => 'Enterprise Sales',
                'monthly_revenue_target' => 2500000.00,
                'is_active' => true,
            ]
        );

        $rawAdminPassword = SecuritySecret::resolveAdminPassword();
        $adminPassword = Hash::make($rawAdminPassword);

        if (! filled(config('crm.admin_password')) && ! app()->environment('testing')) {
            $this->command?->warn("Notice: CRM_ADMIN_PASSWORD was not set in .env. Generated temporary administrator password: {$rawAdminPassword}");
        }

        $mainAdmin = User::query()->firstOrCreate(
            ['email' => 'superadmin@salescrm.test'],
            [
                'team_id' => $enterpriseTeam->id,
                'name' => 'Super Admin',
                'job_title' => 'System Administrator',
                'password' => $adminPassword,
                'is_active' => true,
            ]
        );
        $mainAdmin->update(['password' => $adminPassword]);
        $mainAdmin->syncRoles(['Super Admin']);
    }
}
