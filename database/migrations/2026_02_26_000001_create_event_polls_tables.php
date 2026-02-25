<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_polls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('question', 500);
            $table->json('options');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['event_id', 'is_active']);
        });

        Schema::create('event_poll_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_poll_id')->constrained('event_polls')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('option_index');
            $table->timestamps();

            $table->unique(['event_poll_id', 'user_id']);
            $table->index(['event_poll_id', 'option_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_poll_votes');
        Schema::dropIfExists('event_polls');
    }
};
