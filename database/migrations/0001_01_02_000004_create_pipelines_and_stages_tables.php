<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pipelines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->string('name', 120);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pipeline_stages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pipeline_id')->constrained('pipelines')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('slug', 100);
            $table->unsignedSmallInteger('position');
            $table->decimal('default_probability', 5, 2)->default(0.00);
            $table->unsignedSmallInteger('rotting_days')->default(14);
            $table->boolean('is_won_stage')->default(false);
            $table->boolean('is_lost_stage')->default(false);
            $table->string('color', 20)->default('slate');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['pipeline_id', 'position'], 'unq_pipeline_stage_pos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pipeline_stages');
        Schema::dropIfExists('pipelines');
    }
};
