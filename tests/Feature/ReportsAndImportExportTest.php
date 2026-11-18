<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ImportExportModule;
use App\Enums\ImportExportStatus;
use App\Enums\ImportExportType;
use App\Jobs\GenerateExportFileJob;
use App\Models\ImportExportJob;
use App\Models\Lead;
use App\Models\Team;
use App\Models\User;
use App\Services\Audit\AuditLoggerService;
use App\Support\CsvSanitizer;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ReportsAndImportExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_sales_rep_cannot_query_another_reps_report_metrics(): void
    {
        $team = Team::query()->create(['name' => 'Alpha', 'slug' => 'alpha']);
        $rep1 = User::query()->create([
            'team_id' => $team->id,
            'name' => 'Rep 1',
            'email' => 'rep1@salescrm.test',
            'password' => Hash::make('Password@12345'),
            'is_active' => true,
        ]);
        $rep1->assignRole('Sales Rep');

        $rep2 = User::query()->create([
            'team_id' => $team->id,
            'name' => 'Rep 2',
            'email' => 'rep2@salescrm.test',
            'password' => Hash::make('Password@12345'),
            'is_active' => true,
        ]);
        $rep2->assignRole('Sales Rep');

        $this->actingAs($rep1)
            ->get(route('reports.index', ['owner_id' => $rep2->id]))
            ->assertForbidden();

        $this->actingAs($rep1)
            ->get(route('reports.index'))
            ->assertOk();
    }

    public function test_csv_sanitizer_neutralizes_formula_injection_and_export_logs_audit(): void
    {
        Storage::fake('private');

        $this->assertSame("'=CMD|'/C calc'!A0", CsvSanitizer::sanitizeCell("=CMD|'/C calc'!A0"));

        $team = Team::query()->create(['name' => 'Alpha', 'slug' => 'alpha']);
        $rep = User::query()->create([
            'team_id' => $team->id,
            'name' => 'Rep',
            'email' => 'rep@salescrm.test',
            'password' => Hash::make('Password@12345'),
            'is_active' => true,
        ]);
        $rep->assignRole('Sales Rep');

        Lead::query()->create([
            'team_id' => $team->id,
            'owner_id' => $rep->id,
            'first_name' => '=SUM(A1:A2)',
            'email' => 'lead@example.com',
        ]);

        $job = ImportExportJob::query()->create([
            'team_id' => $team->id,
            'user_id' => $rep->id,
            'type' => ImportExportType::Export,
            'module' => ImportExportModule::Leads,
            'status' => ImportExportStatus::Pending,
            'file_disk' => 'private',
        ]);

        (new GenerateExportFileJob($job->id))->handle(app(AuditLoggerService::class));

        $job->refresh();
        $this->assertSame(ImportExportStatus::Completed, $job->status);
        $content = Storage::disk('private')->get((string) $job->result_file_path);
        $this->assertStringContainsString("'=SUM(A1:A2)", $content);
        $this->assertDatabaseHas('audit_logs', ['event' => 'exported', 'user_id' => $rep->id]);
    }
}
