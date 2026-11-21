<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Attachment;
use App\Models\Company;
use App\Models\Concerns\HasVisibilityScope;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Event;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Proposal;
use App\Models\Quotation;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Models\Webhook;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DatabaseAccessRulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_all_core_business_models_implement_soft_deletes(): void
    {
        $coreModels = [
            Lead::class,
            Contact::class,
            Company::class,
            Deal::class,
            Quotation::class,
            Task::class,
            FollowUp::class,
            Activity::class,
            Attachment::class,
            Event::class,
            Proposal::class,
            Pipeline::class,
            PipelineStage::class,
            Team::class,
            User::class,
            Webhook::class,
        ];

        foreach ($coreModels as $modelClass) {
            $traits = class_uses_recursive($modelClass);
            $this->assertArrayHasKey(
                SoftDeletes::class,
                $traits,
                "Model [{$modelClass}] must use the SoftDeletes trait."
            );
        }
    }

    public function test_all_models_define_explicit_fillable_and_none_use_empty_guarded(): void
    {
        $modelFiles = glob(app_path('Models/*.php'));
        $this->assertNotEmpty($modelFiles, 'Expected to find Eloquent model files in app/Models.');

        foreach ($modelFiles as $file) {
            $className = 'App\\Models\\'.basename($file, '.php');
            if (! class_exists($className) || ! is_subclass_of($className, Model::class)) {
                continue;
            }

            $model = new $className;
            $fillable = $model->getFillable();

            $this->assertNotEmpty(
                $fillable,
                "Model [{$className}] must declare an explicit non-empty \$fillable array."
            );

            // Ensure guarded is not set to empty array (which would allow unintended mass assignment)
            $this->assertNotEquals(
                [],
                $model->getGuarded(),
                "Model [{$className}] must not use protected \$guarded = [] (mass assignment vulnerability)."
            );
        }
    }

    public function test_all_multi_tenant_entities_implement_has_visibility_scope(): void
    {
        $multiTenantModels = [
            Lead::class,
            Contact::class,
            Company::class,
            Deal::class,
            Quotation::class,
            Task::class,
            FollowUp::class,
            Activity::class,
            Event::class,
            Proposal::class,
        ];

        foreach ($multiTenantModels as $modelClass) {
            $traits = class_uses_recursive($modelClass);
            $this->assertArrayHasKey(
                HasVisibilityScope::class,
                $traits,
                "Multi-tenant domain model [{$modelClass}] must use the HasVisibilityScope trait."
            );
        }
    }

    public function test_soft_deleted_records_are_excluded_from_normal_queries(): void
    {
        $team = Team::query()->create(['name' => 'Test Team', 'slug' => 'test-team']);
        $user = User::query()->create([
            'team_id' => $team->id,
            'name' => 'Lead Owner',
            'email' => 'leadowner@example.com',
            'password' => bcrypt('Password@12345'),
            'is_active' => true,
        ]);

        $lead = Lead::query()->create([
            'team_id' => $team->id,
            'owner_id' => $user->id,
            'first_name' => 'Disposable',
            'last_name' => 'Lead',
            'email' => 'disposable@example.com',
            'score' => 10,
        ]);

        $this->assertDatabaseHas('leads', ['id' => $lead->id, 'deleted_at' => null]);
        $this->assertSame(1, Lead::query()->count());

        $lead->delete();

        // Normal query excludes soft-deleted record
        $this->assertSame(0, Lead::query()->count());
        $this->assertNull(Lead::query()->find($lead->id));

        // With trashed includes it
        $this->assertSame(1, Lead::withTrashed()->count());
        $this->assertNotNull(Lead::withTrashed()->find($lead->id));
        $this->assertNotNull(Lead::withTrashed()->find($lead->id)->deleted_at);
    }

    public function test_strict_mode_prevents_lazy_loading(): void
    {
        // Model::shouldBeStrict(! app()->isProduction()) is called in AppServiceProvider
        $this->assertTrue(
            Model::preventsLazyLoading(),
            'Eloquent lazy loading must be strictly prevented in non-production environments to avoid N+1 query performance regressions.'
        );

        $this->assertTrue(
            Model::preventsSilentlyDiscardingAttributes(),
            'Eloquent silently discarding unfillable attributes must be prevented in non-production environments.'
        );
    }
}
