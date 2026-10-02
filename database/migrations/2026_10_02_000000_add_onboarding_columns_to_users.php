<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The schedule an account is in the middle of creating, and when it was last sent an
     * onboarding nudge.
     *
     * pending_schedule_type / pending_schedule_name: the type picked on a for-* page or in the
     * chooser, and the name claimed in the homepage box. Both used to live only in the session,
     * which /login clears, so an onboarding email could only send someone back to the type
     * chooser - and the people it is for are mostly those who had already picked a type and left
     * the form. Cleared once the first schedule is saved.
     *
     * onboarding_nudge_sent_at: onboarding_nudge_stage records WHICH email went out but not when,
     * and SendActivationNudges needs the when to leave a few quiet days after it, so somebody who
     * creates a schedule the day after an onboarding nudge is not emailed again the day after that.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('pending_schedule_type', 16)->nullable();
            $table->string('pending_schedule_name', 255)->nullable();
            $table->timestamp('onboarding_nudge_sent_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['pending_schedule_type', 'pending_schedule_name', 'onboarding_nudge_sent_at']);
        });
    }
};
