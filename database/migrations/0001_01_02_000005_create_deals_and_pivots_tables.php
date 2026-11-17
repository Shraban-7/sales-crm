<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->restrictOnDelete();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('pipeline_id')->constrained('pipelines')->restrictOnDelete();
            $table->foreignId('pipeline_stage_id')->constrained('pipeline_stages')->restrictOnDelete();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('primary_contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->string('title', 191);
            $table->decimal('value', 15, 2)->default(0.00);
            $table->char('currency', 3)->default('BDT');
            $table->decimal('probability', 5, 2)->default(10.00);
            $table->string('status', 30)->default('open');
            $table->date('expected_close_date')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->text('won_notes')->nullable();
            $table->string('lost_reason', 150)->nullable();
            $table->string('lost_competitor', 150)->nullable();
            $table->timestamp('stage_changed_at')->useCurrent();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['pipeline_id', 'pipeline_stage_id', 'status', 'team_id'], 'idx_deals_kanban');
            $table->index(['team_id', 'status', 'expected_close_date', 'owner_id'], 'idx_deals_forecast');
        });

        Schema::create('deal_contact', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('deal_id')->constrained('deals')->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained('contacts')->cascadeOnDelete();
            $table->string('role', 80)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['deal_id', 'contact_id'], 'unq_deal_contact');
        });

        Schema::create('deal_stage_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('deal_id')->constrained('deals')->cascadeOnDelete();
            $table->foreignId('from_stage_id')->nullable()->constrained('pipeline_stages')->nullOnDelete();
            $table->foreignId('to_stage_id')->constrained('pipeline_stages')->restrictOnDelete();
            $table->foreignId('moved_by_id')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('duration_in_previous_stage_seconds')->default(0);
            $table->string('notes', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['deal_id', 'created_at'], 'idx_dsh_deal_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_stage_histories');
        Schema::dropIfExists('deal_contact');
        Schema::dropIfExists('deals');
    }
};
