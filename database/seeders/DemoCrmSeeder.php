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
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoCrmSeeder extends Seeder
{
    public function run(): void
    {
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

        $password = Hash::make('Password@12345');

        $superAdmin = User::query()->firstOrCreate(
            ['email' => 'superadmin@salescrm.test'],
            [
                'team_id' => $enterpriseTeam->id,
                'name' => 'Super Admin',
                'job_title' => 'System Administrator',
                'password' => $password,
                'is_active' => true,
            ]
        );
        $superAdmin->syncRoles(['Super Admin']);

        $orgAdmin = User::query()->firstOrCreate(
            ['email' => 'admin@salescrm.test'],
            [
                'team_id' => $enterpriseTeam->id,
                'name' => 'Org Admin',
                'job_title' => 'VP of Revenue Operations',
                'password' => $password,
                'is_active' => true,
            ]
        );
        $orgAdmin->syncRoles(['Org Admin']);

        $manager = User::query()->firstOrCreate(
            ['email' => 'manager@salescrm.test'],
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

        $rep = User::query()->firstOrCreate(
            ['email' => 'rep@salescrm.test'],
            [
                'team_id' => $enterpriseTeam->id,
                'name' => 'Nadia Karim',
                'job_title' => 'Account Executive',
                'password' => $password,
                'is_active' => true,
            ]
        );
        $rep->syncRoles(['Sales Rep']);

        $smbRep = User::query()->firstOrCreate(
            ['email' => 'smbrep@salescrm.test'],
            [
                'team_id' => $smbTeam->id,
                'name' => 'Imran Hossain',
                'job_title' => 'SMB Account Executive',
                'password' => $password,
                'is_active' => true,
            ]
        );
        $smbRep->syncRoles(['Sales Rep']);

        $analyst = User::query()->firstOrCreate(
            ['email' => 'analyst@salescrm.test'],
            [
                'team_id' => $enterpriseTeam->id,
                'name' => 'Farhana Yasmin',
                'job_title' => 'Revenue Analyst',
                'password' => $password,
                'is_active' => true,
            ]
        );
        $analyst->syncRoles(['Read-Only Analyst']);

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

        if (Company::query()->exists()) {
            return;
        }

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
    }
}
