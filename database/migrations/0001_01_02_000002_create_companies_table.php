<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->restrictOnDelete();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('parent_company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('name', 191);
            $table->string('registration_number', 80)->nullable();
            $table->string('industry', 100)->nullable();
            $table->string('company_size', 50)->nullable();
            $table->decimal('annual_revenue', 15, 2)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('phone', 30)->nullable();
            $table->text('billing_address')->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('city', 100)->nullable();
            $table->char('country', 2)->default('BD');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['team_id', 'owner_id', 'name'], 'idx_companies_team_owner_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
