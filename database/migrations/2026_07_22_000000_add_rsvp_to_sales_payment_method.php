<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 'import' belongs here even though this migration predates the sales importer. The
        // filename is dated later than the two migrations that add 'import'
        // (2026_04_16 and 2026_05_14), so on a fresh `migrate` this runs last and would drop
        // 'import' back off the enum - every bulk attendee import then dies on "Data truncated
        // for column 'payment_method'". Production migrated incrementally and ran this first, so
        // it keeps 'import' from the later two; only fresh installs and CI saw the broken enum.
        DB::statement("ALTER TABLE sales MODIFY COLUMN payment_method ENUM('cash', 'stripe', 'invoiceninja', 'payment_url', 'rsvp', 'import') DEFAULT 'cash'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE sales MODIFY COLUMN payment_method ENUM('cash', 'stripe', 'invoiceninja', 'payment_url', 'import') DEFAULT 'cash'");
    }
};
