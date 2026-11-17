<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->restrictOnDelete();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 191)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('mobile', 30)->nullable();
            $table->string('job_title', 120)->nullable();
            $table->string('department', 100)->nullable();
            $table->boolean('is_primary_decision_maker')->default(false);
            $table->boolean('do_not_contact')->default(false);
            $table->string('lead_source', 80)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['team_id', 'company_id', 'last_name'], 'idx_contacts_team_company');
            $table->index(['email'], 'idx_contacts_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
