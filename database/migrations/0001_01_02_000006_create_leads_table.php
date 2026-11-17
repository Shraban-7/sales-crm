<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->restrictOnDelete();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->string('first_name', 100);
            $table->string('last_name', 100)->nullable();
            $table->string('company_name', 191)->nullable();
            $table->string('job_title', 120)->nullable();
            $table->string('email', 191)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('source', 60)->default('website');
            $table->string('status', 40)->default('new');
            $table->unsignedSmallInteger('score')->default(0);
            $table->decimal('estimated_value', 15, 2)->nullable();
            $table->string('disqualified_reason', 255)->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->foreignId('converted_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('converted_company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('converted_contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('converted_deal_id')->nullable()->constrained('deals')->nullOnDelete();
            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['team_id', 'status', 'owner_id', 'created_at'], 'idx_leads_team_status_owner');
            $table->index(['email'], 'idx_leads_email');
            $table->index(['phone'], 'idx_leads_phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
