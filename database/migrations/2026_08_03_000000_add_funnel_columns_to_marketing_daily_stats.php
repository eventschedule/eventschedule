<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Two blind spots in the onboarding funnel.
     *
     * 1. `visitors` counts every marketing.* route, so a selfhoster reading /docs/installation
     *    lands in the same total as someone on /pricing. That makes the headline
     *    visitor -> signup rate uninterpretable: you cannot tell a conversion problem from a
     *    traffic-mix one. The docs_* columns are the docs/selfhost SUBSET of the existing
     *    totals, so buyer-intent traffic is (visitors - docs_visitors).
     *
     * 2. Nothing was counted between "viewed /sign_up" and "verified account", and hosted
     *    signup requires a 6-digit emailed code typed back into the form. That wall sits
     *    exactly in the steepest step of the funnel and its cost was invisible.
     *
     * All four are plain counters on the existing one-row-per-day shape, so the unique key on
     * `date` and the atomic INSERT ... ON DUPLICATE KEY UPDATE in the model are unchanged.
     */
    public function up(): void
    {
        Schema::table('marketing_daily_stats', function (Blueprint $table) {
            // Anchored to signup_views, which ships in the table's own create migration.
            $table->unsignedInteger('docs_page_views')->default(0)->after('signup_views');
            $table->unsignedInteger('docs_visitors')->default(0)->after('docs_page_views');
            $table->unsignedInteger('signup_code_requests')->default(0)->after('docs_visitors');
            $table->unsignedInteger('signup_code_verified')->default(0)->after('signup_code_requests');
        });
    }

    public function down(): void
    {
        Schema::table('marketing_daily_stats', function (Blueprint $table) {
            $table->dropColumn([
                'docs_page_views',
                'docs_visitors',
                'signup_code_requests',
                'signup_code_verified',
            ]);
        });
    }
};
