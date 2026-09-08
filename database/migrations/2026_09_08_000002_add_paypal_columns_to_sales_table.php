<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Two facts about a PayPal sale that have nowhere else to live.
     *
     * No ->after() anchors, for the reason the payfast users migration spells out.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // PayPal's order id, which is NOT the capture id that ends up in transaction_reference.
            // The buyer's return carries it as ?token=, so the happy path never reads this column -
            // it is here for the paths that have no token: a webhook whose custom_id did not
            // propagate, and a person working out why one sale is stuck.
            $table->string('paypal_order_id')->nullable();

            // Set when PayPal accepts a payment but holds it for review.
            //
            // This exists to stop the app inviting the buyer to pay a SECOND time. The sale is still
            // `unpaid`, and ticket/view.blade.php renders "this ticket is not paid" plus a Complete
            // payment button for any unpaid sale on a resumable rail - and that button starts a
            // fresh checkout against a new Sale. Someone whose payment is merely under review would
            // be charged twice.
            //
            // eChecks are refused at order creation (IMMEDIATE_PAYMENT_REQUIRED), so in practice
            // this is a fraud review that clears in hours, not the three-day case.
            $table->timestamp('paypal_pending_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['paypal_order_id', 'paypal_pending_at']);
        });
    }
};
