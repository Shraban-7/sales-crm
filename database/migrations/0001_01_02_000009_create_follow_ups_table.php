<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follow_ups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->restrictOnDelete();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->string('subject_type', 100);
            $table->unsignedBigInteger('subject_id');
            $table->string('channel', 30);
            $table->string('status', 30)->default('scheduled');
            $table->timestamp('scheduled_at');
            $table->timestamp('completed_at')->nullable();
            $table->string('objective', 255);
            $table->text('outcome_notes')->nullable();
            $table->foreignId('next_follow_up_id')->nullable()->constrained('follow_ups')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['owner_id', 'status', 'scheduled_at'], 'idx_followups_owner_status_sched');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_ups');
    }
};
