<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('two_factor_enabled')->default(false)->after('is_active');
            $table->string('two_factor_code_hash', 255)->nullable()->after('two_factor_enabled');
            $table->timestamp('two_factor_expires_at')->nullable()->after('two_factor_code_hash');
            $table->timestamp('two_factor_last_sent_at')->nullable()->after('two_factor_expires_at');
        });

        Schema::table('quotations', function (Blueprint $table): void {
            $table->foreignId('discount_approved_by_id')
                ->nullable()
                ->after('customer_notes')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('discount_approved_at')->nullable()->after('discount_approved_by_id');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('discount_approved_by_id');
            $table->dropColumn('discount_approved_at');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'two_factor_enabled',
                'two_factor_code_hash',
                'two_factor_expires_at',
                'two_factor_last_sent_at',
            ]);
        });
    }
};
