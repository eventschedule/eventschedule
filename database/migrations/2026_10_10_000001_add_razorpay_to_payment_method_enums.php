<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add 'razorpay' to the two payment_method enums.
     *
     * Restates each enum in full, exactly as 2026_09_08_000001 (PayPal) left it, plus 'razorpay' at
     * the end. See that migration for why neither column is NOT NULL and why the lists differ.
     *
     * A later migration that MODIFYs either enum must restate 'razorpay' too, or every Razorpay row
     * is orphaned. RazorpayGatewayTest::test_payment_method_enums_include_razorpay catches that.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `events` MODIFY `payment_method`
            ENUM('cash','stripe','invoiceninja','payment_url','payfast','paypal','razorpay')
            DEFAULT 'cash'");

        DB::statement("ALTER TABLE `sales` MODIFY `payment_method`
            ENUM('cash','stripe','invoiceninja','payment_url','rsvp','import','payfast','box_office','paypal','razorpay')
            NULL DEFAULT 'cash'");
    }

    public function down(): void
    {
        // Not reversed, for the reason the PayPal migration gives: narrowing would first have
        // to rewrite Razorpay rows to 'cash', misreporting money taken online.
    }
};
