<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ActivityDirection;
use App\Enums\ActivityType;
use App\Enums\DealStatus;
use App\Enums\FollowUpChannel;
use App\Enums\FollowUpStatus;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Enums\QuotationStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Activity;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\DealStageHistory;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\SystemSetting;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Demo CRM Seeder
 *
 * Seeded Demo Accounts (Prefix: demo):
 * - Super Admin:        demosuperadmin@salescrm.test (and superadmin@salescrm.test)
 * - Org Admin:          demoadmin@salescrm.test (and admin@salescrm.test)
 * - Sales Manager:      demomanager@salescrm.test (and manager@salescrm.test)
 * - Sales Executive:    demorep@salescrm.test (and rep@salescrm.test)
 * - SMB Executive:      demosmbrep@salescrm.test (and smbrep@salescrm.test)
 * - Read-Only Analyst:  demoanalyst@salescrm.test (and analyst@salescrm.test)
 *
 * Password: Value of CRM_DEMO_PASSWORD in .env (defaults to 'password')
 */
class DemoCrmSeeder extends Seeder
{
    public function run(): void
    {
        // Block seeding with the default weak password in production.
        if (app()->isProduction() && ! filled(config('crm.demo_password'))) {
            $this->command?->error('CRM_DEMO_PASSWORD must be set in .env before seeding in production. Aborting.');

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

        $smbTeam = Team::query()->firstOrCreate(
            ['slug' => 'smb-inbound'],
            [
                'name' => 'SMB Inbound',
                'monthly_revenue_target' => 1000000.00,
                'is_active' => true,
            ]
        );

        // Local/demo default only; override via CRM_DEMO_PASSWORD in .env before seeding (always 'password' in testing).
        $rawDemoPassword = app()->environment('testing')
            ? 'password'
            : (filled(config('crm.demo_password')) ? (string) config('crm.demo_password') : 'password');
        $password = Hash::make($rawDemoPassword);

        // Main administrator password; override via CRM_ADMIN_PASSWORD in .env before seeding (always 'password' in testing).
        $rawAdminPassword = app()->environment('testing')
            ? 'password'
            : (filled(config('crm.admin_password')) ? (string) config('crm.admin_password') : Str::password(16));
        $adminPassword = Hash::make($rawAdminPassword);

        if (! filled(config('crm.admin_password')) && ! app()->environment('testing')) {
            $this->command?->warn("Notice: CRM_ADMIN_PASSWORD was not set in .env. Generated temporary administrator password: {$rawAdminPassword}");
        }

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

        // Main Administrator Account
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

        // Legacy / internal test alias
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

        // Legacy / internal test alias
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

        // Legacy / internal test alias
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

        // Legacy / internal test alias
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

        // Legacy / internal test alias
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

        $pipeline = Pipeline::query()->firstOrCreate(
            ['name' => 'B2B Enterprise Pipeline'],
            [
                'team_id' => null,
                'is_default' => true,
                'is_active' => true,
            ]
        );

        $stagesSpec = [
            ['name' => 'Prospecting', 'slug' => 'prospecting', 'position' => 1, 'default_probability' => 10.00, 'rotting_days' => 14, 'is_won_stage' => false, 'is_lost_stage' => false, 'color' => 'slate'],
            ['name' => 'Qualification', 'slug' => 'qualification', 'position' => 2, 'default_probability' => 25.00, 'rotting_days' => 14, 'is_won_stage' => false, 'is_lost_stage' => false, 'color' => 'blue'],
            ['name' => 'Solution Demo', 'slug' => 'solution-demo', 'position' => 3, 'default_probability' => 50.00, 'rotting_days' => 10, 'is_won_stage' => false, 'is_lost_stage' => false, 'color' => 'cyan'],
            ['name' => 'Proposal / Quote Sent', 'slug' => 'proposal-sent', 'position' => 4, 'default_probability' => 75.00, 'rotting_days' => 10, 'is_won_stage' => false, 'is_lost_stage' => false, 'color' => 'amber'],
            ['name' => 'Negotiation', 'slug' => 'negotiation', 'position' => 5, 'default_probability' => 90.00, 'rotting_days' => 7, 'is_won_stage' => false, 'is_lost_stage' => false, 'color' => 'indigo'],
            ['name' => 'Closed Won', 'slug' => 'closed-won', 'position' => 6, 'default_probability' => 100.00, 'rotting_days' => 365, 'is_won_stage' => true, 'is_lost_stage' => false, 'color' => 'emerald'],
            ['name' => 'Closed Lost', 'slug' => 'closed-lost', 'position' => 7, 'default_probability' => 0.00, 'rotting_days' => 365, 'is_won_stage' => false, 'is_lost_stage' => true, 'color' => 'rose'],
        ];

        $stages = [];
        foreach ($stagesSpec as $stageData) {
            $stages[$stageData['slug']] = PipelineStage::query()->firstOrCreate(
                ['pipeline_id' => $pipeline->id, 'slug' => $stageData['slug']],
                $stageData
            );
        }

        $printDemoCredentials = function () use ($rawDemoPassword, $rawAdminPassword): void {
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
                $this->command->info('Sale Fusion CRM accounts ready:');
                $this->command->table(
                    ['Type', 'Role', 'Email', 'Password'],
                    [
                        ['Main Account', 'Super Admin', 'superadmin@salescrm.test', $rawAdminPassword],
                        ['Demo Account', 'Super Admin', 'demosuperadmin@salescrm.test', $rawDemoPassword],
                        ['Demo Account', 'Org Admin', 'demoadmin@salescrm.test', $rawDemoPassword],
                        ['Demo Account', 'Sales Manager', 'demomanager@salescrm.test', $rawDemoPassword],
                        ['Demo Account', 'Sales Executive', 'demorep@salescrm.test', $rawDemoPassword],
                        ['Demo Account', 'SMB Executive', 'demosmbrep@salescrm.test', $rawDemoPassword],
                        ['Demo Account', 'Read-Only Analyst', 'demoanalyst@salescrm.test', $rawDemoPassword],
                    ]
                );
                $this->command->info('Database Setting: [system_settings.demo_mode = 0 (Default: Disabled)]');
                $this->command->comment('-> To enable demo credentials and showcase on login page directly from DB, run:');
                $this->command->line("   UPDATE system_settings SET value = '1' WHERE `key` = 'demo_mode';");
            }
        };

        if (Company::query()->exists()) {
            $printDemoCredentials();

            return;
        }

        // Companies
        $company1 = Company::query()->create([
            'team_id' => $enterpriseTeam->id,
            'owner_id' => $rep->id,
            'name' => 'Bengal Tech Holdings Ltd.',
            'industry' => 'Software & Cloud',
            'company_size' => '200-500',
            'annual_revenue' => 150000000.00,
            'website' => 'https://bengaltech.example.com',
            'phone' => '+8801711000101',
            'city' => 'Dhaka',
            'country' => 'BD',
        ]);

        $company2 = Company::query()->create([
            'team_id' => $enterpriseTeam->id,
            'owner_id' => $manager->id,
            'name' => 'Padma Logistics Group',
            'industry' => 'Supply Chain & Logistics',
            'company_size' => '500+',
            'annual_revenue' => 420000000.00,
            'website' => 'https://padmalogistics.example.com',
            'phone' => '+8801711000102',
            'city' => 'Chattogram',
            'country' => 'BD',
        ]);

        $company3 = Company::query()->create([
            'team_id' => $enterpriseTeam->id,
            'owner_id' => $rep->id,
            'name' => 'Meghna Health Systems',
            'industry' => 'Healthcare & Diagnostics',
            'company_size' => '100-200',
            'annual_revenue' => 88000000.00,
            'website' => 'https://meghnahealth.example.com',
            'phone' => '+8801711000103',
            'city' => 'Dhaka',
            'country' => 'BD',
        ]);

        $company4 = Company::query()->create([
            'team_id' => $smbTeam->id,
            'owner_id' => $smbRep->id,
            'name' => 'Sylhet Tea Exports Co.',
            'industry' => 'Agriculture & Exports',
            'company_size' => '50-100',
            'annual_revenue' => 42000000.00,
            'website' => 'https://sylhettea.example.com',
            'phone' => '+8801711000104',
            'city' => 'Sylhet',
            'country' => 'BD',
        ]);

        $company5 = Company::query()->create([
            'team_id' => $enterpriseTeam->id,
            'owner_id' => $manager->id,
            'name' => 'Crescent Retail Networks',
            'industry' => 'Retail & E-Commerce',
            'company_size' => '200-500',
            'annual_revenue' => 210000000.00,
            'website' => 'https://crescentretail.example.com',
            'phone' => '+8801711000105',
            'city' => 'Dhaka',
            'country' => 'BD',
        ]);

        // Contacts
        $contact1 = Contact::query()->create([
            'team_id' => $enterpriseTeam->id,
            'owner_id' => $rep->id,
            'company_id' => $company1->id,
            'first_name' => 'Arif',
            'last_name' => 'Mahmud',
            'email' => 'arif.mahmud@bengaltech.example.com',
            'phone' => '+8801711000201',
            'job_title' => 'CTO',
            'department' => 'Engineering',
            'is_primary_decision_maker' => true,
            'lead_source' => LeadSource::Referral->value,
        ]);

        $contact2 = Contact::query()->create([
            'team_id' => $enterpriseTeam->id,
            'owner_id' => $manager->id,
            'company_id' => $company2->id,
            'first_name' => 'Sadia',
            'last_name' => 'Afrin',
            'email' => 'sadia.afrin@padmalogistics.example.com',
            'phone' => '+8801711000202',
            'job_title' => 'Head of Procurement',
            'department' => 'Operations',
            'is_primary_decision_maker' => true,
            'lead_source' => LeadSource::LinkedIn->value,
        ]);

        $contact3 = Contact::query()->create([
            'team_id' => $enterpriseTeam->id,
            'owner_id' => $rep->id,
            'company_id' => $company3->id,
            'first_name' => 'Dr. Tanvir',
            'last_name' => 'Ahmed',
            'email' => 'tanvir.ahmed@meghnahealth.example.com',
            'phone' => '+8801711000203',
            'job_title' => 'Director of Digital Health',
            'department' => 'IT & Operations',
            'is_primary_decision_maker' => true,
            'lead_source' => LeadSource::OrganicSearch->value,
        ]);

        $contact4 = Contact::query()->create([
            'team_id' => $smbTeam->id,
            'owner_id' => $smbRep->id,
            'company_id' => $company4->id,
            'first_name' => 'Rezaul',
            'last_name' => 'Karim',
            'email' => 'rezaul@sylhettea.example.com',
            'phone' => '+8801711000204',
            'job_title' => 'Managing Partner',
            'department' => 'Executive',
            'is_primary_decision_maker' => true,
            'lead_source' => LeadSource::Partner->value,
        ]);

        $contact5 = Contact::query()->create([
            'team_id' => $enterpriseTeam->id,
            'owner_id' => $manager->id,
            'company_id' => $company5->id,
            'first_name' => 'Nusrat',
            'last_name' => 'Jahan',
            'email' => 'nusrat.jahan@crescentretail.example.com',
            'phone' => '+8801711000205',
            'job_title' => 'VP of Commercial Operations',
            'department' => 'Sales',
            'is_primary_decision_maker' => true,
            'lead_source' => LeadSource::TradeShow->value,
        ]);

        // Leads across diverse sources & statuses
        Lead::query()->create([
            'team_id' => $enterpriseTeam->id,
            'owner_id' => $rep->id,
            'first_name' => 'Kamrul',
            'last_name' => 'Hasan',
            'company_name' => 'Apex Textiles BD',
            'job_title' => 'IT Director',
            'email' => 'kamrul@apextextiles.example.com',
            'phone' => '+8801811000301',
            'source' => LeadSource::Website,
            'status' => LeadStatus::Qualified,
            'score' => 82,
            'estimated_value' => 450000.00,
        ]);

        Lead::query()->create([
            'team_id' => $enterpriseTeam->id,
            'owner_id' => $rep->id,
            'first_name' => 'Mehnaz',
            'last_name' => 'Chowdhury',
            'company_name' => 'Delta FinServe',
            'job_title' => 'Operations Lead',
            'email' => 'mehnaz@deltafinserve.example.com',
            'phone' => '+8801811000302',
            'source' => LeadSource::LinkedIn,
            'status' => LeadStatus::New,
            'score' => 55,
            'estimated_value' => 280000.00,
        ]);

        Lead::query()->create([
            'team_id' => $enterpriseTeam->id,
            'owner_id' => $manager->id,
            'first_name' => 'Shafiq',
            'last_name' => 'Islam',
            'company_name' => 'Orion Energy Solutions',
            'job_title' => 'Commercial Director',
            'email' => 'shafiq@orionenergy.example.com',
            'phone' => '+8801811000303',
            'source' => LeadSource::OrganicSearch,
            'status' => LeadStatus::Contacted,
            'score' => 68,
            'estimated_value' => 720000.00,
        ]);

        Lead::query()->create([
            'team_id' => $smbTeam->id,
            'owner_id' => $smbRep->id,
            'first_name' => 'Rasheda',
            'last_name' => 'Begum',
            'company_name' => 'Dhaka Print Studio',
            'job_title' => 'Founder',
            'email' => 'rasheda@dhakaprint.example.com',
            'phone' => '+8801811000304',
            'source' => LeadSource::WhatsApp,
            'status' => LeadStatus::Qualified,
            'score' => 74,
            'estimated_value' => 185000.00,
        ]);

        Lead::query()->create([
            'team_id' => $enterpriseTeam->id,
            'owner_id' => $rep->id,
            'first_name' => 'Faisal',
            'last_name' => 'Kabir',
            'company_name' => 'Northstar Maritime',
            'job_title' => 'Head of IT',
            'email' => 'faisal@northstarmaritime.example.com',
            'phone' => '+8801811000305',
            'source' => LeadSource::Partner,
            'status' => LeadStatus::New,
            'score' => 61,
            'estimated_value' => 540000.00,
        ]);

        // Deals spanning every Kanban stage so the board and reports look populated immediately
        $deal1 = Deal::query()->create([
            'team_id' => $enterpriseTeam->id,
            'owner_id' => $rep->id,
            'pipeline_id' => $pipeline->id,
            'pipeline_stage_id' => $stages['proposal-sent']->id,
            'company_id' => $company1->id,
            'primary_contact_id' => $contact1->id,
            'title' => 'Bengal Tech Enterprise CRM Rollout',
            'value' => 650000.00,
            'currency' => 'BDT',
            'probability' => 75.00,
            'status' => DealStatus::Open,
            'expected_close_date' => now()->addDays(20)->toDateString(),
            'stage_changed_at' => now()->subDays(3),
            'last_activity_at' => now()->subDay(),
        ]);
        $deal1->contacts()->attach($contact1->id, ['role' => 'Economic Buyer', 'is_primary' => true]);

        DealStageHistory::query()->create([
            'deal_id' => $deal1->id,
            'from_stage_id' => null,
            'to_stage_id' => $stages['proposal-sent']->id,
            'moved_by_id' => $rep->id,
            'duration_in_previous_stage_seconds' => 0,
            'notes' => 'Initial deal creation',
            'created_at' => now()->subDays(3),
        ]);

        $deal2 = Deal::query()->create([
            'team_id' => $enterpriseTeam->id,
            'owner_id' => $manager->id,
            'pipeline_id' => $pipeline->id,
            'pipeline_stage_id' => $stages['closed-won']->id,
            'company_id' => $company2->id,
            'primary_contact_id' => $contact2->id,
            'title' => 'Padma Logistics Fleet Portal',
            'value' => 920000.00,
            'currency' => 'BDT',
            'probability' => 100.00,
            'status' => DealStatus::Won,
            'expected_close_date' => now()->subDays(2)->toDateString(),
            'closed_at' => now()->subDays(2),
            'won_notes' => 'Signed annual enterprise agreement.',
            'stage_changed_at' => now()->subDays(2),
            'last_activity_at' => now()->subDays(2),
        ]);
        $deal2->contacts()->attach($contact2->id, ['role' => 'Procurement Lead', 'is_primary' => true]);

        $deal3 = Deal::query()->create([
            'team_id' => $enterpriseTeam->id,
            'owner_id' => $rep->id,
            'pipeline_id' => $pipeline->id,
            'pipeline_stage_id' => $stages['prospecting']->id,
            'company_id' => $company3->id,
            'primary_contact_id' => $contact3->id,
            'title' => 'Meghna Health Multi-Branch Patient CRM',
            'value' => 480000.00,
            'currency' => 'BDT',
            'probability' => 10.00,
            'status' => DealStatus::Open,
            'expected_close_date' => now()->addDays(45)->toDateString(),
            'stage_changed_at' => now()->subDays(2),
            'last_activity_at' => now()->subDays(2),
        ]);
        $deal3->contacts()->attach($contact3->id, ['role' => 'Technical Evaluator', 'is_primary' => true]);

        $deal4 = Deal::query()->create([
            'team_id' => $smbTeam->id,
            'owner_id' => $smbRep->id,
            'pipeline_id' => $pipeline->id,
            'pipeline_stage_id' => $stages['qualification']->id,
            'company_id' => $company4->id,
            'primary_contact_id' => $contact4->id,
            'title' => 'Sylhet Tea Export Buyer Desk',
            'value' => 240000.00,
            'currency' => 'BDT',
            'probability' => 25.00,
            'status' => DealStatus::Open,
            'expected_close_date' => now()->addDays(30)->toDateString(),
            'stage_changed_at' => now()->subDays(4),
            'last_activity_at' => now()->subDay(),
        ]);
        $deal4->contacts()->attach($contact4->id, ['role' => 'Decision Maker', 'is_primary' => true]);

        $deal5 = Deal::query()->create([
            'team_id' => $enterpriseTeam->id,
            'owner_id' => $manager->id,
            'pipeline_id' => $pipeline->id,
            'pipeline_stage_id' => $stages['solution-demo']->id,
            'company_id' => $company5->id,
            'primary_contact_id' => $contact5->id,
            'title' => 'Crescent Retail Omnichannel Sales Hub',
            'value' => 810000.00,
            'currency' => 'BDT',
            'probability' => 50.00,
            'status' => DealStatus::Open,
            'expected_close_date' => now()->addDays(25)->toDateString(),
            'stage_changed_at' => now()->subDays(5),
            'last_activity_at' => now()->subDays(1),
        ]);
        $deal5->contacts()->attach($contact5->id, ['role' => 'Executive Sponsor', 'is_primary' => true]);

        $deal6 = Deal::query()->create([
            'team_id' => $enterpriseTeam->id,
            'owner_id' => $rep->id,
            'pipeline_id' => $pipeline->id,
            'pipeline_stage_id' => $stages['negotiation']->id,
            'company_id' => $company3->id,
            'primary_contact_id' => $contact3->id,
            'title' => 'Meghna Diagnostics B2B Referral Portal',
            'value' => 390000.00,
            'currency' => 'BDT',
            'probability' => 90.00,
            'status' => DealStatus::Open,
            'expected_close_date' => now()->addDays(10)->toDateString(),
            'stage_changed_at' => now()->subDays(1),
            'last_activity_at' => now()->subHours(6),
        ]);
        $deal6->contacts()->attach($contact3->id, ['role' => 'Project Owner', 'is_primary' => true]);

        $deal7 = Deal::query()->create([
            'team_id' => $smbTeam->id,
            'owner_id' => $smbRep->id,
            'pipeline_id' => $pipeline->id,
            'pipeline_stage_id' => $stages['closed-lost']->id,
            'company_id' => $company4->id,
            'primary_contact_id' => $contact4->id,
            'title' => 'Sylhet Tea Legacy ERP Connector',
            'value' => 160000.00,
            'currency' => 'BDT',
            'probability' => 0.00,
            'status' => DealStatus::Lost,
            'expected_close_date' => now()->subDays(5)->toDateString(),
            'closed_at' => now()->subDays(5),
            'lost_reason' => 'Deferred until Q3 ERP upgrade',
            'lost_competitor' => 'In-house spreadsheet workflow',
            'stage_changed_at' => now()->subDays(5),
            'last_activity_at' => now()->subDays(5),
        ]);
        $deal7->contacts()->attach($contact4->id, ['role' => 'Decision Maker', 'is_primary' => true]);

        // Activities
        Activity::query()->create([
            'team_id' => $enterpriseTeam->id,
            'user_id' => $rep->id,
            'subject_type' => 'deal',
            'subject_id' => $deal1->id,
            'type' => ActivityType::Call,
            'direction' => ActivityDirection::Outbound,
            'outcome' => 'Connected',
            'summary' => 'Reviewed security & deployment checklist with CTO',
            'duration_minutes' => 25,
            'occurred_at' => now()->subDay(),
        ]);

        Activity::query()->create([
            'team_id' => $enterpriseTeam->id,
            'user_id' => $manager->id,
            'subject_type' => 'deal',
            'subject_id' => $deal5->id,
            'type' => ActivityType::Meeting,
            'direction' => ActivityDirection::Outbound,
            'outcome' => 'Demo Scheduled',
            'summary' => 'Walked Crescent Retail ops team through Kanban & quotation approvals',
            'duration_minutes' => 45,
            'occurred_at' => now()->subDays(1),
        ]);

        // Tasks
        Task::query()->create([
            'team_id' => $enterpriseTeam->id,
            'assigned_to_id' => $rep->id,
            'created_by_id' => $manager->id,
            'taskable_type' => 'deal',
            'taskable_id' => $deal1->id,
            'title' => 'Send revised SLA addendum to Bengal Tech',
            'priority' => TaskPriority::High,
            'status' => TaskStatus::Pending,
            'due_at' => now()->addDay(),
        ]);

        Task::query()->create([
            'team_id' => $enterpriseTeam->id,
            'assigned_to_id' => $rep->id,
            'created_by_id' => $rep->id,
            'taskable_type' => 'deal',
            'taskable_id' => $deal6->id,
            'title' => 'Finalize legal redlines with Meghna Health',
            'priority' => TaskPriority::Urgent,
            'status' => TaskStatus::Pending,
            'due_at' => now()->addDays(2),
        ]);

        Task::query()->create([
            'team_id' => $smbTeam->id,
            'assigned_to_id' => $smbRep->id,
            'created_by_id' => $smbRep->id,
            'taskable_type' => 'deal',
            'taskable_id' => $deal4->id,
            'title' => 'Prepare discovery questionnaire for Sylhet Tea Exports',
            'priority' => TaskPriority::Medium,
            'status' => TaskStatus::Pending,
            'due_at' => now()->addDays(3),
        ]);

        // Follow-ups
        FollowUp::query()->create([
            'team_id' => $enterpriseTeam->id,
            'owner_id' => $rep->id,
            'subject_type' => 'deal',
            'subject_id' => $deal1->id,
            'channel' => FollowUpChannel::Call,
            'status' => FollowUpStatus::Scheduled,
            'scheduled_at' => now()->addDays(2),
            'objective' => 'Confirm quotation sign-off timeline',
        ]);

        FollowUp::query()->create([
            'team_id' => $enterpriseTeam->id,
            'owner_id' => $manager->id,
            'subject_type' => 'deal',
            'subject_id' => $deal5->id,
            'channel' => FollowUpChannel::Meeting,
            'status' => FollowUpStatus::Scheduled,
            'scheduled_at' => now()->addDays(3),
            'objective' => 'Review technical architecture with Crescent Retail IT',
        ]);

        // Quotations
        $quotation = Quotation::query()->create([
            'ulid' => (string) Str::ulid(),
            'team_id' => $enterpriseTeam->id,
            'owner_id' => $rep->id,
            'deal_id' => $deal1->id,
            'company_id' => $company1->id,
            'contact_id' => $contact1->id,
            'quote_number' => sprintf('QT-%s-00001', now()->format('Y')),
            'version' => 1,
            'status' => QuotationStatus::Sent,
            'currency' => 'BDT',
            'subtotal' => 650000.00,
            'discount_total' => 0.00,
            'tax_total' => 0.00,
            'grand_total' => 650000.00,
            'issue_date' => now()->toDateString(),
            'valid_until' => now()->addDays(30)->toDateString(),
            'sent_at' => now()->subDay(),
        ]);

        QuotationItem::query()->create([
            'quotation_id' => $quotation->id,
            'position' => 1,
            'item_name' => 'Enterprise CRM Annual License (50 Seats)',
            'quantity' => 1,
            'unit' => 'year',
            'unit_price' => 500000.00,
            'discount_percent' => 0.00,
            'tax_percent' => 0.00,
            'line_total' => 500000.00,
        ]);

        QuotationItem::query()->create([
            'quotation_id' => $quotation->id,
            'position' => 2,
            'item_name' => 'Onboarding & Data Migration Package',
            'quantity' => 1,
            'unit' => 'package',
            'unit_price' => 150000.00,
            'discount_percent' => 0.00,
            'tax_percent' => 0.00,
            'line_total' => 150000.00,
        ]);

        $acceptedQuotation = Quotation::query()->create([
            'ulid' => (string) Str::ulid(),
            'team_id' => $enterpriseTeam->id,
            'owner_id' => $manager->id,
            'deal_id' => $deal2->id,
            'company_id' => $company2->id,
            'contact_id' => $contact2->id,
            'quote_number' => sprintf('QT-%s-00002', now()->format('Y')),
            'version' => 1,
            'status' => QuotationStatus::Accepted,
            'currency' => 'BDT',
            'subtotal' => 920000.00,
            'discount_total' => 0.00,
            'tax_total' => 0.00,
            'grand_total' => 920000.00,
            'issue_date' => now()->subDays(10)->toDateString(),
            'valid_until' => now()->addDays(20)->toDateString(),
            'sent_at' => now()->subDays(8),
            'accepted_at' => now()->subDays(2),
        ]);

        QuotationItem::query()->create([
            'quotation_id' => $acceptedQuotation->id,
            'position' => 1,
            'item_name' => 'Fleet Portal Enterprise Subscription (100 Seats)',
            'quantity' => 1,
            'unit' => 'year',
            'unit_price' => 920000.00,
            'discount_percent' => 0.00,
            'tax_percent' => 0.00,
            'line_total' => 920000.00,
        ]);

        $printDemoCredentials();
    }
}
