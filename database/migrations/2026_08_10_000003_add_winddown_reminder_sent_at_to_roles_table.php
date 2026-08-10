<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * When the comped wind-down last reminded this schedule.
 *
 * It used to reuse trial_reminder_sent_at, which the Stripe trial path reads with no time
 * window at all - any value means "already sent", permanently. So a schedule that received a
 * wind-down notice and later started a real Stripe trial never got its "your trial ends
 * tomorrow" email, which is the one that actually prevents an unintended downgrade.
 *
 * The two reminders answer different questions and have different cadences (the wind-down sends
 * at 14, 3 and 1 days), so they get their own columns rather than a shared one with a flag.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->timestamp('winddown_reminder_sent_at')->nullable()->after('trial_reminder_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('winddown_reminder_sent_at');
        });
    }
};
