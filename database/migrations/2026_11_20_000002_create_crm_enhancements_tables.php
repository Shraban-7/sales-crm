<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Lead Stages
        Schema::create('lead_stages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->string('name', 80);
            $table->unsignedInteger('position')->default(0);
            $table->string('color', 30)->default('#3b82f6');
            $table->boolean('is_won')->default(false);
            $table->boolean('is_lost')->default(false);
            $table->timestamps();
        });

        // 2. Events (for mobile/trade show lead capture)
        Schema::create('events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->restrictOnDelete();
            $table->foreignId('created_by_id')->constrained('users')->restrictOnDelete();
            $table->string('name', 150);
            $table->string('location', 191)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('budget', 15, 2)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Enhance leads table
        Schema::table('leads', function (Blueprint $table): void {
            $table->foreignId('lead_stage_id')->nullable()->constrained('lead_stages')->nullOnDelete();
            $table->foreignId('event_id')->nullable()->constrained('events')->nullOnDelete();
            $table->string('priority', 20)->default('medium')->after('score');
            $table->date('close_date')->nullable()->after('estimated_value');
            $table->string('card_photo_path', 255)->nullable()->after('website');
        });

        // 4. Proposals & Proposal Documents
        Schema::create('proposals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->restrictOnDelete();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('deal_id')->nullable()->constrained('deals')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->string('proposal_number', 50)->unique();
            $table->string('title', 191);
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->char('currency', 3)->default('USD');
            $table->string('status', 30)->default('draft');
            $table->date('valid_until')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['team_id', 'status', 'valid_until'], 'idx_proposals_team_status_valid');
        });

        Schema::create('proposal_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('proposal_id')->constrained('proposals')->cascadeOnDelete();
            $table->foreignId('uploaded_by_id')->constrained('users')->restrictOnDelete();
            $table->string('original_name', 255);
            $table->string('storage_disk', 30)->default('private');
            $table->string('storage_path', 255);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->timestamps();
        });

        // 5. Targets & Commission
        Schema::create('targets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 20)->default('team'); // company, team, individual
            $table->decimal('target_amount', 15, 2);
            $table->char('currency', 3)->default('USD');
            $table->unsignedTinyInteger('period_month');
            $table->unsignedSmallInteger('period_year');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['period_year', 'period_month', 'type'], 'idx_targets_period_type');
        });

        Schema::create('commissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('deal_id')->constrained('deals')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->decimal('deal_value', 15, 2);
            $table->string('status', 30)->default('pending'); // pending, approved, paid, cancelled
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        // 6. SMTP Settings
        Schema::create('smtp_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('mail_mailer', 20)->default('smtp');
            $table->string('mail_host', 191);
            $table->unsignedSmallInteger('mail_port')->default(587);
            $table->string('mail_username', 191)->nullable();
            $table->text('mail_password')->nullable(); // encrypted
            $table->string('mail_encryption', 20)->nullable()->default('tls');
            $table->string('mail_from_address', 191);
            $table->string('mail_from_name', 150);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smtp_settings');
        Schema::dropIfExists('commissions');
        Schema::dropIfExists('targets');
        Schema::dropIfExists('proposal_documents');
        Schema::dropIfExists('proposals');

        Schema::table('leads', function (Blueprint $table): void {
            $table->dropForeign(['lead_stage_id']);
            $table->dropForeign(['event_id']);
            $table->dropColumn(['lead_stage_id', 'event_id', 'priority', 'close_date', 'card_photo_path']);
        });

        Schema::dropIfExists('events');
        Schema::dropIfExists('lead_stages');
    }
};
