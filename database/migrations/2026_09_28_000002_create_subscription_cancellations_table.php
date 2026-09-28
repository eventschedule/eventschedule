<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Why subscribers leave. 18 cancelled against 6 active on 2026-08-30, and nothing recorded
        // a reason: the in-app cancel asked "are you sure", and a cancel in the Stripe portal left
        // only subscriptions.ends_at. A table rather than audit JSON so the growth export can group
        // by it, and one row per cancelled SUBSCRIPTION (stripe_subscription_id), whichever of the
        // paths below saw it first, so the count is complete even where no reason can be asked.
        Schema::create('subscription_cancellations', function (Blueprint $table) {
            $table->id();
            // No foreign keys: a schedule-deletion cancel writes this row and then the schedule
            // goes, and the row must outlive it to be counted.
            $table->unsignedBigInteger('role_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('stripe_subscription_id')->nullable()->index();
            // app | portal | payment_failed | schedule_deleted | admin
            $table->string('source', 20);
            // SubscriptionCancellation::REASONS, or null when none was given or none could be.
            $table->string('reason', 40)->nullable();
            $table->text('comment')->nullable();
            $table->string('plan_type', 20)->nullable();
            $table->string('plan_term', 10)->nullable();
            // Set when the cancel was taken back during the grace period: not churn.
            $table->timestamp('resumed_at')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_cancellations');
    }
};
