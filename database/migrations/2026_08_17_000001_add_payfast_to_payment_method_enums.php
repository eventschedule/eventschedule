<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Both payment_method columns are MySQL enums, so a new gateway cannot be selected on an event or
     * recorded on a sale until it is listed here.
     *
     * Every value already in each enum is repeated, because MODIFY COLUMN replaces the whole
     * definition rather than adding to it. Dropping one would make its rows unwritable: the
     * 2026_07_22 migration has a comment about exactly that happening to 'import'.
     *
     * The two lists differ on purpose. 'rsvp' and 'import' describe where a sale row came from and
     * are never a choice on an event, so they belong only on sales.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE events MODIFY COLUMN payment_method ENUM('cash', 'stripe', 'invoiceninja', 'payment_url', 'payfast') DEFAULT 'cash'");
        DB::statement("ALTER TABLE sales MODIFY COLUMN payment_method ENUM('cash', 'stripe', 'invoiceninja', 'payment_url', 'rsvp', 'import', 'payfast') DEFAULT 'cash'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE events MODIFY COLUMN payment_method ENUM('cash', 'stripe', 'invoiceninja', 'payment_url') DEFAULT 'cash'");
        DB::statement("ALTER TABLE sales MODIFY COLUMN payment_method ENUM('cash', 'stripe', 'invoiceninja', 'payment_url', 'rsvp', 'import') DEFAULT 'cash'");
    }
};
