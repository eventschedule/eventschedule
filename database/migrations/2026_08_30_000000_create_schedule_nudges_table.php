<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per activation nudge actually delivered to a schedule.
     *
     * A separate table rather than another column on users, because the two sequences are
     * different shapes. SendOnboardingNudges carries a single monotonic
     * users.onboarding_nudge_stage: its stages are linear, per-user, and end once a schedule
     * exists. Activation nudges are per-SCHEDULE and independent of each other - a schedule can
     * go idle without ever having reached the tickets state - so there is no single number that
     * "only ever moves forward", which is the property that makes the double-fired scheduler
     * safe over there.
     *
     * The unique index is the claim. insertOrIgnore() returns the inserted row count, so exactly
     * one runner wins a nudge even with routes/console.php and AppController::translateData both
     * firing, the same guarantee the conditional UPDATE gives SendOnboardingNudges.
     */
    public function up(): void
    {
        Schema::create('schedule_nudges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            // Which nudge, e.g. 'no_ticket_type'. A string rather than an enum so adding one
            // is a code change and not a migration.
            $table->string('nudge_key', 40);
            $table->timestamp('created_at')->nullable();

            // Sent at most once per schedule per nudge, enforced by the database rather than
            // by a read-then-write that two schedulers can interleave.
            $table->unique(['role_id', 'nudge_key']);

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_nudges');
    }
};
