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
 * Password: Set via CRM_ADMIN_PASSWORD in .env before seeding.
 */
class AdminAccountSeeder extends Seeder
{
    public function run(): void
    {
        if (! filled(config('crm.admin_password')) && ! app()->environment('testing')) {
            throw new \RuntimeException('Set CRM_ADMIN_PASSWORD before seeding. No administrator account was created.');
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
