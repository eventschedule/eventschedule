<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Which onboarding nudge this account has already been sent.
     *
     * More than half of all accounts have never created a schedule, and activation is
     * first-session-or-never: 73.5% of first schedules happen within ten minutes of signup and
     * 91% within the hour. Nothing existed to reach the rest - there was no welcome or
     * onboarding email of any kind - so they simply left.
     *
     * A stage counter rather than a timestamp per email: the command walks the stages in order
     * and only ever moves forward, so a send can never be duplicated even if the scheduler
     * double-fires or a run is retried.
     *
     * 0 = none sent, 1 = the ~1 hour nudge, 2 = ~24 hours, 3 = ~72 hours (final).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('onboarding_nudge_stage')->default(0)->after('email_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('onboarding_nudge_stage');
        });
    }
};
