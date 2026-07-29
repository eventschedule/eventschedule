<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The unique key on analytics_promotions_daily is (boost_campaign_id, host_role_id, date), so
 * `date` sits third and cannot be used for a range or equality seek unless host_role_id is also
 * constrained. Two hot queries constrain campaign and date but not host:
 *
 *   - PromotionService::deliveredToday(), which runs on every candidate-cache rebuild - at
 *     least every 300s, and again on every budget exhaustion;
 *   - PromotionAnalyticsService::dailySeries(), which groups by date for the advertiser chart.
 *
 * Both therefore scan every row a campaign has ever written, across all hosts and all retained
 * days, to answer a question about one day.
 *
 * Separate migration rather than an edit to 2026_07_28_000001 because that one may already have
 * run. promotion_locations_daily needs nothing: its (boost_campaign_id, date, country_code)
 * unique already serves the same prefix.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analytics_promotions_daily', function (Blueprint $table) {
            $table->index(['boost_campaign_id', 'date'], 'apd_campaign_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('analytics_promotions_daily', function (Blueprint $table) {
            $table->dropIndex('apd_campaign_date_index');
        });
    }
};
