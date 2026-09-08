<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add 'paypal' to the two payment_method enums.
     *
     * MODIFY replaces the whole column definition, so each statement restates its own enum in full -
     * and the two are NOT the same list. `sales` carries 'rsvp', 'import' and 'box_office' that
     * `events` has no use for, and it is nullable where `events` is not. Copying one line onto the
     * other silently drops those members and orphans every RSVP, imported and box-office row.
     *
     * Appended at the END of each list: that keeps every existing 1-based ordinal where it was, so
     * MySQL performs the change INPLACE as metadata only and the payment_method index survives.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `events` MODIFY `payment_method`
            ENUM('cash','stripe','invoiceninja','payment_url','payfast','paypal')
            DEFAULT 'cash'");

        DB::statement("ALTER TABLE `sales` MODIFY `payment_method`
            ENUM('cash','stripe','invoiceninja','payment_url','rsvp','import','payfast','box_office','paypal')
            NULL DEFAULT 'cash'");
    }

    public function down(): void
    {
        // Deliberately NOT reversed, the same call 2026_08_23_000009_add_box_office_payment_method
        // made: narrowing the enum back would first have to rewrite every PayPal row to something
        // else, and 'cash' is the only candidate - which would report money taken through PayPal as
        // cash at the door. That rewrite is irreversible, and `migrate:rollback --step=1` runs
        // precisely this migration on its own, so the most likely rollback is the most destructive.
        //
        // Leaving a widened enum in place costs nothing: no code writes 'paypal' once the driver is
        // gone from config('payments.gateways').
    }
};
