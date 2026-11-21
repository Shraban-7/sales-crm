<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Pipeline;
use App\Models\PipelineStage;
use Illuminate\Database\Seeder;

class DemoPipelineSeeder extends Seeder
{
    public function run(): void
    {
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

        foreach ($stagesSpec as $stageData) {
            PipelineStage::query()->firstOrCreate(
                ['pipeline_id' => $pipeline->id, 'slug' => $stageData['slug']],
                $stageData
            );
        }
    }
}
