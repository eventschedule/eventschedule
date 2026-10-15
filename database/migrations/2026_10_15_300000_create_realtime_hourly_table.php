<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hourly totals of realtime_hits, for the Trends card of /admin/realtime (App\Services\RealtimeRollup).
 *
 * realtime_hits keeps each page view for about an hour, which is what the privacy policy promises,
 * so anything longer than the live page is read from here: per hour and per part of the site, how
 * many page views and how many visits began. Counts only, with no visitor, page, country or device,
 * so nothing here is a record of anybody.
 *
 * realtime_rollup holds one row: how far realtime_hits has been counted, by engaged_at. A page view
 * counts from the moment it is engaged, which can be minutes after it started, so a cursor on
 * engaged_at counts each one exactly once; hour is still the hour it STARTED in, as on the live
 * page's per-minute chart.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('realtime_hourly')) {
            Schema::create('realtime_hourly', function (Blueprint $table) {
                $table->id();
                $table->dateTime('hour');
                $table->string('surface', 8);
                $table->unsignedInteger('page_views')->default(0);
                $table->unsignedInteger('visits')->default(0);

                $table->unique(['hour', 'surface']);
            });
        }

        if (! Schema::hasTable('realtime_rollup')) {
            Schema::create('realtime_rollup', function (Blueprint $table) {
                $table->unsignedTinyInteger('id')->primary();
                $table->dateTime('through_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('realtime_rollup');
        Schema::dropIfExists('realtime_hourly');
    }
};
