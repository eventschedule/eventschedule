<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Seven counters for what visitors do on guest pages, across every schedule.
     *
     * Each schedule's own analytics count its views and its sales. Nothing counted the steps
     * between, or added them up across schedules: how many people who opened an event page opened
     * its form, pressed Checkout, and got to the end. These are the "before" picture for the guest
     * page redesign, and the numbers it is read against afterwards.
     *
     * One visitor per day each, deduped the way the submit page's counters are
     * (2026_10_06_000001). App\Utils\GuestFunnel says what each one counts and who is left out.
     */
    public function up(): void
    {
        Schema::table('marketing_daily_stats', function (Blueprint $table) {
            $table->unsignedInteger('gp_event_visitors')->default(0);
            $table->unsignedInteger('gp_list_taps')->default(0);
            $table->unsignedInteger('gp_form_opens')->default(0);
            $table->unsignedInteger('gp_checkout_starts')->default(0);
            $table->unsignedInteger('gp_checkouts_done')->default(0);
            $table->unsignedInteger('gp_follows')->default(0);
            $table->unsignedInteger('gp_calendar_adds')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('marketing_daily_stats', function (Blueprint $table) {
            $table->dropColumn([
                'gp_event_visitors', 'gp_list_taps', 'gp_form_opens', 'gp_checkout_starts',
                'gp_checkouts_done', 'gp_follows', 'gp_calendar_adds',
            ]);
        });
    }
};
