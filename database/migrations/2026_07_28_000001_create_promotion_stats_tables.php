<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * First-party impression / click rollups for network promotions.
 *
 * There is no raw event log anywhere in this codebase by design - page_views was created
 * and dropped in the same migration series, and every analytics_*_daily table is written
 * synchronously with INSERT ... ON DUPLICATE KEY UPDATE. These follow that convention, one
 * table per dimension rather than one wide table, so cardinality stays bounded.
 *
 * host_role_id is part of the daily key rather than living in its own table: placement
 * distribution is then just a GROUP BY, and no third table is needed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_promotions_daily', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boost_campaign_id')->constrained()->cascadeOnDelete();

            // NOT NULL on purpose: MySQL treats NULLs as distinct in a unique index, so a
            // nullable column here would silently defeat the ON DUPLICATE KEY upsert and
            // insert a fresh row on every single impression.
            $table->foreignId('host_role_id')->constrained('roles')->cascadeOnDelete();

            $table->date('date');
            $table->unsignedBigInteger('impressions')->default(0);
            // Distinct visitors that day, from the daily-salted IP+UA hash. Named for what
            // it actually measures - true "reach" is not computable without a raw log.
            $table->unsignedBigInteger('unique_visitors')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);

            $table->unique(['boost_campaign_id', 'host_role_id', 'date'], 'apd_campaign_host_date_unique');
            $table->index(['host_role_id', 'date']);
            $table->index('date');   // retention pruning
        });

        Schema::create('promotion_locations_daily', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boost_campaign_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            // Country only: GeoIpService reads the DB-IP Lite *Country* database, which has
            // no subdivision records at all.
            $table->char('country_code', 2);
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);

            $table->unique(['boost_campaign_id', 'date', 'country_code'], 'pld_campaign_date_country_unique');
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_locations_daily');
        Schema::dropIfExists('analytics_promotions_daily');
    }
};
