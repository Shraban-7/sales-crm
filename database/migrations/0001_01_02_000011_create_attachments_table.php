<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->restrictOnDelete();
            $table->foreignId('uploaded_by_id')->constrained('users')->restrictOnDelete();
            $table->string('attachable_type', 100);
            $table->unsignedBigInteger('attachable_id');
            $table->string('original_name', 255);
            $table->string('storage_disk', 30)->default('private');
            $table->string('storage_path', 255);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->char('sha256_checksum', 64);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['attachable_type', 'attachable_id'], 'idx_attachments_morph');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
