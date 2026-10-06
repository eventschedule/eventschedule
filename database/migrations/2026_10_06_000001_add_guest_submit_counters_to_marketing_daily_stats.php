<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Three counters for the public "Submit your event" page (event.guest_submit).
     *
     * It is the default request form for every venue and curator schedule, it creates an account
     * for each new submitter, and nothing counted it: not how many people saw the form, how many
     * reached the emailed code, or how many got an event through. Each is one visitor per day,
     * deduped the way signup_views is, so the three read as stages of one funnel.
     */
    public function up(): void
    {
        Schema::table('marketing_daily_stats', function (Blueprint $table) {
            $table->unsignedInteger('guest_submit_views')->default(0);
            $table->unsignedInteger('guest_submit_code_requests')->default(0);
            $table->unsignedInteger('guest_submit_submissions')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('marketing_daily_stats', function (Blueprint $table) {
            $table->dropColumn(['guest_submit_views', 'guest_submit_code_requests', 'guest_submit_submissions']);
        });
    }
};
