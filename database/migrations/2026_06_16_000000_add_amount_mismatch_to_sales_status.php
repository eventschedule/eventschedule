<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Payment webhooks (Stripe, Invoice Ninja) set sales.status to 'amount_mismatch'
     * when the paid amount differs from the expected amount, and the admin dashboard
     * approves/refunds those sales. The enum was missing that value, so the webhook
     * save failed (or truncated) under MySQL strict mode. Add it.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE sales MODIFY COLUMN status ENUM('unpaid', 'paid', 'cancelled', 'refunded', 'expired', 'amount_mismatch') NOT NULL DEFAULT 'unpaid'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE sales MODIFY COLUMN status ENUM('unpaid', 'paid', 'cancelled', 'refunded', 'expired') NOT NULL DEFAULT 'unpaid'");
    }
};
