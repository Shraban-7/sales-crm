<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Demo CRM Seeder
 *
 * Modular orchestrator for demo accounts, pipelines, and sample business data:
 * - AdminAccountSeeder: Provisions primary administrator
 * - DemoAccountSeeder: Provisions teams, demo accounts, and assigns roles
 * - DemoPipelineSeeder: Sets up default B2B sales pipeline and stages
 * - DemoBusinessDataSeeder: Seeds companies, contacts, leads, deals, tasks, follow-ups, and quotations
 */
class DemoCrmSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction() && ! filled(config('crm.demo_password'))) {
            $this->command?->error('CRM_DEMO_PASSWORD must be set in .env before seeding in production. Aborting.');

            return;
        }

        $this->call([
            AdminAccountSeeder::class,
            DemoAccountSeeder::class,
            DemoPipelineSeeder::class,
            DemoBusinessDataSeeder::class,
        ]);
    }
}
