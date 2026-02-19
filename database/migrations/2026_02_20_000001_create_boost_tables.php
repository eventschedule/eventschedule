<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boost_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Meta IDs
            $table->string('meta_campaign_id')->nullable();
            $table->string('meta_adset_id')->nullable();

            // Config
            $table->string('name');
            $table->string('objective', 50)->default('OUTCOME_AWARENESS');
            $table->string('status', 30)->default('draft');
            $table->string('meta_status', 50)->nullable();
            $table->text('meta_rejection_reason')->nullable();

            // Budget
            $table->decimal('daily_budget', 10, 2)->nullable();
            $table->decimal('lifetime_budget', 10, 2)->nullable();
            $table->string('budget_type', 20)->default('lifetime');
            $table->string('currency_code', 3)->default('USD');
            $table->timestamp('scheduled_start')->nullable();
            $table->timestamp('scheduled_end')->nullable();

            // Targeting
            $table->json('targeting')->nullable();
            $table->json('placements')->nullable();

            // Billing
            $table->decimal('user_budget', 10, 2);
            $table->decimal('markup_rate', 5, 4)->default(0.2000);
            $table->decimal('total_charged', 10, 2)->nullable();
            $table->decimal('actual_spend', 10, 2)->nullable();
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('billing_status', 30)->default('pending');

            // Analytics (cached)
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('reach')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->decimal('ctr', 8, 4)->default(0);
            $table->decimal('cpc', 10, 2)->default(0);
            $table->decimal('cpm', 10, 2)->default(0);
            $table->unsignedInteger('conversions')->default(0);
            $table->json('daily_analytics')->nullable();

            // Timestamps
            $table->timestamp('analytics_synced_at')->nullable();
            $table->timestamp('meta_synced_at')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'status']);
            $table->index(['role_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('meta_campaign_id');
        });

        Schema::create('boost_ads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boost_campaign_id')->constrained()->cascadeOnDelete();

            // Meta IDs
            $table->string('meta_ad_id')->nullable();
            $table->string('meta_creative_id')->nullable();

            // Creative
            $table->string('headline')->nullable();
            $table->text('primary_text')->nullable();
            $table->string('description')->nullable();
            $table->string('call_to_action', 30)->default('LEARN_MORE');
            $table->string('image_url', 2048)->nullable();
            $table->string('image_hash')->nullable();
            $table->string('destination_url', 2048);

            // A/B testing
            $table->char('variant', 1)->nullable();
            $table->boolean('is_winner')->default(false);

            // Status + per-ad analytics
            $table->string('status', 30)->default('pending');
            $table->string('meta_status', 50)->nullable();
            $table->text('meta_rejection_reason')->nullable();
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('reach')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->decimal('spend', 10, 2)->default(0);
            $table->decimal('ctr', 8, 4)->default(0);
            $table->timestamps();

            $table->index(['boost_campaign_id', 'status']);
        });

        Schema::create('boost_billing_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boost_campaign_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20); // charge, refund, adjustment
            $table->decimal('amount', 10, 2);
            $table->decimal('meta_spend', 10, 2)->nullable();
            $table->decimal('markup_amount', 10, 2)->nullable();
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('stripe_refund_id')->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['boost_campaign_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boost_billing_records');
        Schema::dropIfExists('boost_ads');
        Schema::dropIfExists('boost_campaigns');
    }
};
