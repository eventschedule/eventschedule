<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // A card-free trial of paid ticket selling, started from the event editor's paywall.
            // Deliberately NOT roles.trial_ends_at: that is Cashier's generic trial, which makes
            // isPro() true and so unlocks every Pro extra, and some of those outlive a trial in a
            // BUYER's hands - a pass stops booking and an installment plan stops collecting the
            // day the schedule stops being Pro. This one is read only by canSellPaidTickets().
            //
            // Kept after it ends: a non-null value is what makes the owner ineligible for another.
            // Neither column is fillable.
            $table->timestamp('ticket_trial_ends_at')->nullable();
            // Claim for the ending reminders in SendSubscriptionReminders.
            $table->timestamp('ticket_trial_reminder_sent_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['ticket_trial_ends_at', 'ticket_trial_reminder_sent_at']);
        });
    }
};
