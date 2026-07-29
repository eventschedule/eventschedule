<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the on-network promotions channel to the existing boost campaign table.
 *
 * "Boost" already owns campaign budgets, Stripe platform-account billing, refunds, the
 * boost_credit wallet, the admin approve/refund queue and - via sales.boost_campaign_id -
 * click-to-ticket conversion attribution. A parallel promotions table would have had to
 * duplicate all of it, so a network campaign is a boost_campaigns row with channel='network'.
 *
 * Every ->after() anchor is a column from 2026_02_20_000001_create_boost_tables.php or
 * earlier, so this still applies cleanly on a fresh migrate.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boost_campaigns', function (Blueprint $table) {
            // 'meta' = Meta Ads (all existing rows, hence the default), 'network' = on-platform.
            $table->string('channel', 20)->default('meta')->after('user_id');

            // Network pricing. Money is integer micros (1e-6 of a currency unit) so that
            // per-impression debits never accumulate decimal rounding error, and so budget
            // exhaustion is a single race-free integer comparison in one UPDATE.
            $table->string('pricing_model', 10)->nullable()->after('budget_type');   // cpm | cpc
            $table->unsignedBigInteger('unit_rate_micros')->nullable()->after('pricing_model');
            $table->unsignedBigInteger('budget_micros')->nullable()->after('unit_rate_micros');
            $table->unsignedBigInteger('spent_micros')->default(0)->after('budget_micros');
            $table->timestamp('exhausted_at')->nullable()->after('spent_micros');

            // Deliberately NOT reusing `targeting`: that column holds Meta's API payload
            // verbatim and is POSTed to Meta, so sharing it would make every reader
            // channel-aware and risk sending network targeting to Facebook.
            $table->json('network_targeting')->nullable()->after('placements');

            // Moderation. NULL for Meta campaigns, which Meta reviews itself.
            $table->string('moderation_status', 20)->nullable()->after('status');
            $table->text('moderation_notes')->nullable()->after('moderation_status');
            $table->unsignedBigInteger('moderated_by')->nullable()->after('moderation_notes');
            $table->timestamp('moderated_at')->nullable()->after('moderated_by');

            $table->foreign('moderated_by')->references('id')->on('users')->nullOnDelete();

            // The serving query is an equality prefix on exactly these three.
            $table->index(['channel', 'status', 'moderation_status'], 'boost_campaigns_network_serving_index');
        });

        Schema::table('roles', function (Blueprint $table) {
            // A free schedule can decline to host other schedules' promotions. Free rather
            // than Pro-gated: forcing a schedule to carry a competitor's ad with no exit is
            // the abuse this feature has to avoid, and opting out only removes its own
            // inventory.
            $table->boolean('promotions_opt_out')->default(false)->after('boost_max_budget');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('promotions_opt_out');
        });

        Schema::table('boost_campaigns', function (Blueprint $table) {
            $table->dropForeign(['moderated_by']);
            $table->dropIndex('boost_campaigns_network_serving_index');
            $table->dropColumn([
                'channel',
                'pricing_model',
                'unit_rate_micros',
                'budget_micros',
                'spent_micros',
                'exhausted_at',
                'network_targeting',
                'moderation_status',
                'moderation_notes',
                'moderated_by',
                'moderated_at',
            ]);
        });
    }
};
