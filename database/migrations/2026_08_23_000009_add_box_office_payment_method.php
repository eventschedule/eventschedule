<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * A seat sold at the counter or over the phone is its own channel.
 *
 * Reusing 'cash' would report a comped house seat as money taken, and 'import' would claim a
 * spreadsheet the operator never uploaded. Appending to the END of an ENUM is a metadata-only
 * change in MySQL (INPLACE, not INSTANT), so it does not rebuild the sales table and does not
 * rewrite the index on payment_method - an enum is stored as its 1-based ordinal, and appending
 * leaves every existing ordinal untouched. The definition is restated in full because MODIFY
 * replaces the whole column definition; anything omitted would revert to the table default.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `sales` MODIFY `payment_method`
            ENUM('cash','stripe','invoiceninja','payment_url','rsvp','import','payfast','box_office')
            NULL DEFAULT 'cash'");
    }

    public function down(): void
    {
        // Deliberately NOT reversed.
        //
        // Narrowing the enum back would first have to rewrite every box office sale to something
        // else, and 'cash' is the only candidate - which reinstates exactly the bug this migration
        // exists to prevent: a comped house seat reported as money taken. That rewrite is
        // irreversible, and `migrate:rollback --step=1` runs precisely this migration on its own,
        // so the most likely rollback is the most destructive one.
        //
        // Same call 2026_08_10_000002_backfill_onboarding_nudge_stage_for_existing_users.php and
        // 2026_08_07_000002_backfill_empty_group_slugs.php made: leaving a widened enum in place
        // costs nothing, because no code writes 'box_office' once the feature is gone.
    }
};
