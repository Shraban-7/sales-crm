<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->restrictOnDelete();
            $table->foreignId('assigned_to_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('created_by_id')->constrained('users')->restrictOnDelete();
            $table->string('taskable_type', 100);
            $table->unsignedBigInteger('taskable_id');
            $table->string('title', 191);
            $table->text('description')->nullable();
            $table->string('priority', 20)->default('medium');
            $table->string('status', 30)->default('pending');
            $table->timestamp('due_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['assigned_to_id', 'status', 'due_at'], 'idx_tasks_assignee_status_due');
            $table->index(['taskable_type', 'taskable_id'], 'idx_tasks_morph');
        });

        Schema::create('reminders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('remindable_type', 100);
            $table->unsignedBigInteger('remindable_id');
            $table->timestamp('remind_at');
            $table->string('channel', 30)->default('database');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['sent_at', 'remind_at'], 'idx_reminders_due_unsent');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminders');
        Schema::dropIfExists('tasks');
    }
};
