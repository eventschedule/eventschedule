<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Aurora: a schedule's page background drawn by the browser from the schedule's colours, the
 * fourth value of `roles.background`. Its own settings live in `aurora_settings` (the next
 * migration), because `roles` is at MySQL's row limit.
 *
 * Appending to the END of an ENUM is a metadata-only change (INPLACE, not INSTANT), so it does not
 * rebuild `roles`, and an enum stays one byte, so it cannot touch the row limit either. No
 * ALGORITHM clause: the selfhost guide promises MySQL 5.7 and MariaDB 10.3, and 5.7 rejects
 * INSTANT as a syntax error. The definition is restated in full because MODIFY replaces the whole
 * column definition: NOT NULL with no default, as 2024_12_18_142217 left it, so MySQL's implicit
 * default stays the first value, 'gradient', which the API's create relied on.
 */
return new class extends Migration
{
    public function up(): void
    {
        // A short lock wait: `roles` is the busiest table, and an ALTER waiting behind a long
        // transaction holds every query on it in line behind itself. Safe to run again: the
        // statement only restates the column.
        $previous = DB::selectOne('SELECT @@SESSION.lock_wait_timeout AS value')->value;
        DB::statement('SET SESSION lock_wait_timeout = 10');

        try {
            DB::statement("ALTER TABLE `roles` MODIFY `background` ENUM('gradient', 'solid', 'image', 'aurora') NOT NULL");
        } finally {
            DB::statement('SET SESSION lock_wait_timeout = '.(int) $previous);
        }
    }

    public function down(): void
    {
        // Deliberately NOT reversed, as 2026_08_23_000009_add_box_office_payment_method.php.
        //
        // Narrowing the enum back would first have to rewrite every Aurora schedule to another
        // background, which loses the owner's choice for good, and `migrate:rollback --step=1`
        // runs precisely this migration on its own. A widened enum costs nothing once no code
        // writes 'aurora'.
    }
};
