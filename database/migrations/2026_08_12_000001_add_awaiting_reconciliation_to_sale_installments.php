<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * A state for "we asked Stripe to charge this and never learned the outcome".
     *
     * The usual cause is an ApiConnectionException or a read timeout AFTER Stripe created and
     * confirmed the PaymentIntent: the card was debited and the response was lost. The previous
     * handling put the row back to `scheduled` with a retry in 24 hours, which is precisely when
     * Stripe stops honouring the idempotency key - so every network timeout became a second real
     * charge.
     *
     * Parking it instead means a human reconciles one uncertain payment, rather than the buyer
     * silently paying twice.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE sale_installments MODIFY COLUMN status ENUM('scheduled', 'processing', 'awaiting_customer', 'awaiting_reconciliation', 'paid', 'failed', 'cancelled') NOT NULL DEFAULT 'scheduled'");
    }

    public function down(): void
    {
        DB::statement("UPDATE sale_installments SET status = 'failed' WHERE status = 'awaiting_reconciliation'");
        DB::statement("ALTER TABLE sale_installments MODIFY COLUMN status ENUM('scheduled', 'processing', 'awaiting_customer', 'paid', 'failed', 'cancelled') NOT NULL DEFAULT 'scheduled'");
    }
};
