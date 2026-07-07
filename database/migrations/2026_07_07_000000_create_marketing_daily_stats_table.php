<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('marketing_daily_stats', function (Blueprint $table) {
            $table->id();
            // One row per calendar day (UTC). Unique so the atomic
            // "INSERT ... ON DUPLICATE KEY UPDATE" counter never creates duplicates.
            $table->date('date')->unique();
            // Onboarding funnel top-of-funnel counters for the marketing (WP) site.
            $table->unsignedInteger('visitors')->default(0);      // unique visitors per day
            $table->unsignedInteger('page_views')->default(0);    // raw marketing page views
            $table->unsignedInteger('signup_views')->default(0);  // views of the /sign_up page
            // No timestamps: the atomic upsert counter mirrors AnalyticsDaily
            // (models set $timestamps = false); the `date` column is the meaningful date.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_daily_stats');
    }
};
