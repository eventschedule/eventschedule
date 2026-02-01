<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_part_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('youtube_url');
            $table->boolean('is_approved')->default(false);
            $table->timestamps();

            $table->index(['event_id', 'is_approved']);
            $table->index(['event_part_id', 'is_approved']);
        });

        Schema::create('event_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_part_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('comment');
            $table->boolean('is_approved')->default(false);
            $table->timestamps();

            $table->index(['event_id', 'is_approved']);
            $table->index(['event_part_id', 'is_approved']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_comments');
        Schema::dropIfExists('event_videos');
    }
};
