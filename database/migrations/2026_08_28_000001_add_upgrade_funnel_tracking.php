<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // The third first-touch onboarding milestone, alongside schedule_form_viewed_at and
            // event_form_viewed_at from 2026_07_07_000001. Stamped once when the checkout page
            // actually renders (SubscriptionController::show), never overwritten.
            //
            // Without this the plan funnel has no denominator: only completed subscriptions were
            // ever recorded, so "reached checkout and did not buy" was unanswerable. At roughly
            // one conversion a month that bottom-of-funnel number can never move detectably, and
            // this is the nearest stage upstream that carries enough volume to read.
            $table->timestamp('subscribe_form_viewed_at')->nullable();
        });

        Schema::table('marketing_daily_stats', function (Blueprint $table) {
            // Views of /pricing specifically. These are a SUBSET of page_views, counted the same
            // way as docs_page_views/docs_visitors from 2026_08_03_000000, so the two must not be
            // added together.
            //
            // Note what this still cannot see: TrackMarketingVisit skips auth()->check(), so a
            // signed-in owner weighing an upgrade is never counted here. That is deliberate for
            // "anonymous prospect" traffic; pricing_views therefore measures acquisition-side
            // interest only, and the signed-in half is what subscribe_form_viewed_at covers.
            $table->unsignedInteger('pricing_views')->default(0)->after('signup_views');
            $table->unsignedInteger('pricing_visitors')->default(0)->after('pricing_views');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('subscribe_form_viewed_at');
        });

        Schema::table('marketing_daily_stats', function (Blueprint $table) {
            $table->dropColumn(['pricing_views', 'pricing_visitors']);
        });
    }
};
