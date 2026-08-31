<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per "no thanks" a user gave a suggestion on the dashboard Next steps panel.
     *
     * Keyed per (user, schedule, step) rather than by a flag on users, because the panel is
     * per-schedule: silencing "add a ticket type" on a venue that does not sell tickets must
     * not also silence "add your first event" on a schedule created next month. It is per
     * USER because several editors share a schedule and one of them saying no does not decide
     * for the others.
     *
     * The unique index is also what makes a double submit a no-op, whether it arrives through
     * firstOrCreate on the single-row form or insertOrIgnore from "Dismiss all".
     */
    public function up(): void
    {
        if (! Schema::hasTable('dismissed_next_steps')) {
            Schema::create('dismissed_next_steps', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('role_id')->constrained()->cascadeOnDelete();
                // Which suggestion, e.g. 'next_step_tickets'. A string rather than an enum so
                // adding a step is a code change and not a migration, and 40 to match
                // schedule_nudges.nudge_key - the longest current value is 18 characters.
                $table->string('step_type', 40);
                $table->timestamps();

                // One index serves both readers: user_id leftmost for the dashboard's
                // "where user_id = ? and role_id in (...)", and the full triple for the
                // correlated subquery SendActivationNudges runs per candidate schedule.
                $table->unique(['user_id', 'role_id', 'step_type'], 'dns_user_role_step_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dismissed_next_steps');
    }
};
