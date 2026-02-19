<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add missing index on boost_ads.meta_ad_id (queried by webhook handler)
        Schema::table('boost_ads', function (Blueprint $table) {
            $table->index('meta_ad_id');
        });

        // Add index for checkCompletedCampaigns query
        Schema::table('boost_campaigns', function (Blueprint $table) {
            $table->index(['status', 'billing_status']);
        });

        // Change boost_campaigns.event_id from cascadeOnDelete to nullOnDelete
        Schema::table('boost_campaigns', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
            $table->foreignId('event_id')->nullable()->change();
            $table->foreign('event_id')->references('id')->on('events')->nullOnDelete();
        });

        // Change boost_billing_records.boost_campaign_id from cascadeOnDelete to nullOnDelete
        Schema::table('boost_billing_records', function (Blueprint $table) {
            $table->dropForeign(['boost_campaign_id']);
            $table->foreignId('boost_campaign_id')->nullable()->change();
            $table->foreign('boost_campaign_id')->references('id')->on('boost_campaigns')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('boost_billing_records', function (Blueprint $table) {
            $table->dropForeign(['boost_campaign_id']);
            $table->foreignId('boost_campaign_id')->nullable(false)->change();
            $table->foreign('boost_campaign_id')->references('id')->on('boost_campaigns')->cascadeOnDelete();
        });

        Schema::table('boost_campaigns', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
            $table->foreignId('event_id')->nullable(false)->change();
            $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
        });

        Schema::table('boost_campaigns', function (Blueprint $table) {
            $table->dropIndex(['status', 'billing_status']);
        });

        Schema::table('boost_ads', function (Blueprint $table) {
            $table->dropIndex(['meta_ad_id']);
        });
    }
};
