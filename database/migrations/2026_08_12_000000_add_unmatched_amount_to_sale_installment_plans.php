<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Money that arrived at Stripe but could not be matched to anything chargeable.
     *
     * Two ways in, both reachable: a payment landing after the plan was cancelled (an organizer
     * refunds or cancels the event while the buyer has a Checkout Session open), and a genuinely
     * second PaymentIntent for a row another payment already settled.
     *
     * Both used to be a `Log::warning` and nothing else, which is the worst possible handling for
     * money the buyer has actually paid: nothing in this app refunds a Connect ticket sale, so the
     * organizer has to act in their own Stripe dashboard - and they can only do that if something
     * tells them it happened.
     */
    public function up(): void
    {
        Schema::table('sale_installment_plans', function (Blueprint $table) {
            $table->decimal('unmatched_amount', 13, 3)->nullable()->after('amount_paid');
        });
    }

    public function down(): void
    {
        Schema::table('sale_installment_plans', function (Blueprint $table) {
            $table->dropColumn('unmatched_amount');
        });
    }
};
