<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Two counters for the public booking request page (event.booking_request).
     *
     * It is the request form of every talent schedule, and of a venue or curator that chose it,
     * and nothing counted it either: not how many people saw the form, nor how many sent a request.
     * One visitor per day each, deduped the way the submit page's counters are
     * (2026_10_06_000001), so the two forms can be read side by side.
     */
    public function up(): void
    {
        Schema::table('marketing_daily_stats', function (Blueprint $table) {
            $table->unsignedInteger('booking_request_views')->default(0);
            $table->unsignedInteger('booking_request_submissions')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('marketing_daily_stats', function (Blueprint $table) {
            $table->dropColumn(['booking_request_views', 'booking_request_submissions']);
        });
    }
};
