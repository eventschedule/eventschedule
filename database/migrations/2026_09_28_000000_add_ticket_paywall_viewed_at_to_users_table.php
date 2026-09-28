<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // First time this person was shown the paid-ticket paywall in the event editor: a
            // priced ticket row on a schedule that cannot sell it. Stamped once, never
            // overwritten, the same shape as subscribe_form_viewed_at.
            //
            // Since 2026-09-21 paid selling is Pro-only, so this is where a free organizer
            // meets the plan. Without it the funnel jumps from saved_paid_ticket straight to
            // reached_checkout, and "saw the paywall and walked away" cannot be told apart from
            // "never got that far".
            $table->timestamp('ticket_paywall_viewed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('ticket_paywall_viewed_at');
        });
    }
};
