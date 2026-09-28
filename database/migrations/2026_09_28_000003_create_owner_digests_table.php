<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The claim for app:send-owner-digests: one weekly digest per owner per ISO week. The unique
        // index IS the claim - insertOrIgnore() returns the rows it inserted, so of two runs that
        // race (the two cron rails hold different mutexes) exactly one sends. Same pattern as
        // schedule_nudges.
        Schema::create('owner_digests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // ISO week, e.g. 2026-W40.
            $table->string('week', 8);
            // How many schedules the email covered, for the growth export.
            $table->unsignedSmallInteger('schedules')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->unique(['user_id', 'week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_digests');
    }
};
