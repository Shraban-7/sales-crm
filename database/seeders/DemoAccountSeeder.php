<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SystemSetting;
use App\Models\Team;
use App\Models\User;
use App\Support\SecuritySecret;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Demo Account Seeder
 *
 * Provisions demo enterprise and SMB sales teams along with demo user accounts:
 * - Super Admin:        demosuperadmin@salescrm.test (and superadmin@salescrm.test via AdminAccountSeeder)
 * - Org Admin:          demoadmin@salescrm.test (and admin@salescrm.test)
 * - Sales Manager:      demomanager@salescrm.test (and manager@salescrm.test)
 * - Sales Executive:    demorep@salescrm.test (and rep@salescrm.test)
 * - SMB Executive:      demosmbrep@salescrm.test (and smbrep@salescrm.test)
 * - Read-Only Analyst:  demoanalyst@salescrm.test (and analyst@salescrm.test)
 */
class DemoAccountSeeder extends Seeder
{
    public function run(): void
    {
        if (! filled(config('crm.demo_password')) && ! app()->environment('testing')) {
            throw new \RuntimeException('Set CRM_DEMO_PASSWORD before seeding. No demo accounts were created.');
        }

        $enterpriseTeam = Team::query()->firstOrCreate(
            ['slug' => 'enterprise-sales'],
            [
                'name' => 'Enterprise Sales',
                'monthly_revenue_target' => 2500000.00,
                'is_active' => true,
            ]
        );

        $smbTeam = Team::query()->firstOrCreate(
            ['slug' => 'smb-inbound'],
            [
                'name' => 'SMB Inbound',
                'monthly_revenue_target' => 1000000.00,
                'is_active' => true,
            ]
        );

        $rawDemoPassword = SecuritySecret::resolveDemoPassword();
        $password = Hash::make($rawDemoPassword);

        $superAdmin = User::query()->firstOrCreate(
            ['email' => 'demosuperadmin@salescrm.test'],
            [
                'team_id' => $enterpriseTeam->id,
                'name' => 'Demo Super Admin',
                'job_title' => 'System Administrator',
                'password' => $password,
                'is_active' => true,
            ]
        );
        $superAdmin->update(['password' => $password]);
        $superAdmin->syncRoles(['Super Admin']);

        $orgAdmin = User::query()->firstOrCreate(
            ['email' => 'demoadmin@salescrm.test'],
            [
                'team_id' => $enterpriseTeam->id,
                'name' => 'Demo Org Admin',
                'job_title' => 'VP of Revenue Operations',
                'password' => $password,
                'is_active' => true,
            ]
        );
        $orgAdmin->syncRoles(['Org Admin']);

        User::query()->firstOrCreate(
            ['email' => 'admin@salescrm.test'],
            [
                'team_id' => $enterpriseTeam->id,
                'name' => 'Org Admin',
                'job_title' => 'VP of Revenue Operations',
                'password' => $password,
                'is_active' => true,
            ]
        )->syncRoles(['Org Admin']);

        $manager = User::query()->firstOrCreate(
            ['email' => 'demomanager@salescrm.test'],
            [
                'team_id' => $enterpriseTeam->id,
                'name' => 'Tariq Rahman',
                'job_title' => 'Enterprise Sales Manager',
                'password' => $password,
                'is_active' => true,
            ]
        );
        $manager->syncRoles(['Sales Manager']);
        $enterpriseTeam->update(['leader_id' => $manager->id]);

        User::query()->firstOrCreate(
            ['email' => 'manager@salescrm.test'],
            [
                'team_id' => $enterpriseTeam->id,
                'name' => 'Tariq Rahman',
                'job_title' => 'Enterprise Sales Manager',
                'password' => $password,
                'is_active' => true,
            ]
        )->syncRoles(['Sales Manager']);

        $rep = User::query()->firstOrCreate(
            ['email' => 'demorep@salescrm.test'],
            [
                'team_id' => $enterpriseTeam->id,
                'name' => 'Nadia Karim',
                'job_title' => 'Account Executive',
                'password' => $password,
                'is_active' => true,
            ]
        );
        $rep->syncRoles(['Sales Rep']);

        User::query()->firstOrCreate(
            ['email' => 'rep@salescrm.test'],
            [
                'team_id' => $enterpriseTeam->id,
                'name' => 'Nadia Karim',
                'job_title' => 'Account Executive',
                'password' => $password,
                'is_active' => true,
            ]
        )->syncRoles(['Sales Rep']);

        $smbRep = User::query()->firstOrCreate(
            ['email' => 'demosmbrep@salescrm.test'],
            [
                'team_id' => $smbTeam->id,
                'name' => 'Imran Hossain',
                'job_title' => 'SMB Account Executive',
                'password' => $password,
                'is_active' => true,
            ]
        );
        $smbRep->syncRoles(['Sales Rep']);

        User::query()->firstOrCreate(
            ['email' => 'smbrep@salescrm.test'],
            [
                'team_id' => $smbTeam->id,
                'name' => 'Imran Hossain',
                'job_title' => 'SMB Account Executive',
                'password' => $password,
                'is_active' => true,
            ]
        )->syncRoles(['Sales Rep']);

        $analyst = User::query()->firstOrCreate(
            ['email' => 'demoanalyst@salescrm.test'],
            [
                'team_id' => $enterpriseTeam->id,
                'name' => 'Farhana Yasmin',
                'job_title' => 'Revenue Analyst',
                'password' => $password,
                'is_active' => true,
            ]
        );
        $analyst->syncRoles(['Read-Only Analyst']);

        User::query()->firstOrCreate(
            ['email' => 'analyst@salescrm.test'],
            [
                'team_id' => $enterpriseTeam->id,
                'name' => 'Farhana Yasmin',
                'job_title' => 'Revenue Analyst',
                'password' => $password,
                'is_active' => true,
            ]
        )->syncRoles(['Read-Only Analyst']);

        SystemSetting::query()->updateOrCreate(
            ['key' => 'demo_mode'],
            [
                'value' => '0',
                'group' => 'general',
                'type' => 'boolean',
                'description' => 'Enable or disable interactive demo mode and login demo credentials showcase',
            ]
        );

        if ($this->command) {
            $this->command->info('Sale Fusion CRM demo accounts provisioned:');
            $this->command->table(
                ['Type', 'Role', 'Email'],
                [
                    ['Main Account', 'Super Admin', 'superadmin@salescrm.test'],
                    ['Demo Account', 'Super Admin', 'demosuperadmin@salescrm.test'],
                    ['Demo Account', 'Org Admin', 'demoadmin@salescrm.test'],
                    ['Demo Account', 'Sales Manager', 'demomanager@salescrm.test'],
                    ['Demo Account', 'Sales Executive', 'demorep@salescrm.test'],
                    ['Demo Account', 'SMB Executive', 'demosmbrep@salescrm.test'],
                    ['Demo Account', 'Read-Only Analyst', 'demoanalyst@salescrm.test'],
                ]
            );
            $this->command->info('Database Setting: [system_settings.demo_mode = 0 (Default: Disabled)]');
        }
    }
}
