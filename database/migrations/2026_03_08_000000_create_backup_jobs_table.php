<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type', 10);
            $table->string('status', 20)->default('pending');
            $table->json('role_ids')->nullable();
            $table->string('file_path', 500)->nullable();
            $table->timestamp('file_expires_at')->nullable();
            $table->json('progress')->nullable();
            $table->json('report')->nullable();
            $table->text('error_message')->nullable();
            $table->boolean('include_images')->default(false);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_jobs');
    }
};
