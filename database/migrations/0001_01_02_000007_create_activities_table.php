<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('subject_type', 100);
            $table->unsignedBigInteger('subject_id');
            $table->string('type', 30);
            $table->string('direction', 20)->nullable();
            $table->string('outcome', 60)->nullable();
            $table->string('summary', 255);
            $table->text('body')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['subject_type', 'subject_id', 'occurred_at'], 'idx_activities_morph_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
