<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table): void {
            $table->id();
            $table->char('ulid', 26)->unique();
            $table->foreignId('team_id')->constrained('teams')->restrictOnDelete();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('deal_id')->constrained('deals')->restrictOnDelete();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->string('quote_number', 50)->unique();
            $table->unsignedSmallInteger('version')->default(1);
            $table->string('status', 30)->default('draft');
            $table->char('currency', 3)->default('BDT');
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('discount_total', 15, 2)->default(0.00);
            $table->decimal('tax_total', 15, 2)->default(0.00);
            $table->decimal('grand_total', 15, 2)->default(0.00);
            $table->date('issue_date');
            $table->date('valid_until');
            $table->text('terms_and_conditions')->nullable();
            $table->text('customer_notes')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->string('rejection_reason', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['deal_id', 'version'], 'idx_quotes_deal_version');
        });

        Schema::create('quotation_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->unsignedSmallInteger('position')->default(1);
            $table->string('item_name', 191);
            $table->text('description')->nullable();
            $table->decimal('quantity', 10, 2)->default(1.00);
            $table->string('unit', 30)->default('unit');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount_percent', 5, 2)->default(0.00);
            $table->decimal('tax_percent', 5, 2)->default(0.00);
            $table->decimal('line_total', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');
    }
};
