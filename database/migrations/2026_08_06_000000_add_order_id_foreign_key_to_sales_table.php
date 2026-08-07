<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Make sales.order_id a real self-reference, so a destroyed anchor cannot strand its order.
     *
     * sales.event_id already cascades on delete, and Event is hard-deleted with no soft-delete and
     * no sales guard - so deleting the event that carried an order's ANCHOR row destroys that row
     * at the database layer, with no model hook. order_id was a bare indexed column, so every
     * surviving leg was left pointing at an id that no longer exists. Nothing then satisfied
     * `order_id === id`, isOrderPrimary() was false everywhere, the order cascade went silently
     * dead, and ReleaseTickets - which resolves `order_id ?: id` and bails when the target is
     * missing - could never release those legs' seats or their gift-card holds again.
     *
     * ON DELETE SET NULL turns that into the one coherent outcome: the survivors become ordinary
     * standalone sales, expire on their own windows, and release their inventory as they always
     * did. The anchor's own self-reference disappears with the row.
     *
     * Orphans are cleared first: the constraint cannot be added while a dangling value exists, and
     * any install that already lost an anchor this way is carrying some.
     */
    public function up(): void
    {
        // MySQL only, and loudly so. Laravel's SQLite grammar cannot add a foreign key to an
        // existing table: compileForeign() returns nothing and the migration reports success, which
        // would leave an install believing it has this protection when it has none. The app is
        // MySQL-only (see CLAUDE.md), so anything else is a misconfiguration worth surfacing.
        if (DB::getDriverName() !== 'mysql') {
            throw new \RuntimeException('sales.order_id foreign key requires MySQL; got '.DB::getDriverName());
        }

        // Clear any pointer to a sale that no longer exists, or the constraint cannot be added.
        // An install that already lost an anchor to an event deletion is carrying some. LEFT JOIN
        // rather than NOT IN: it is index-driven, where NOT IN materialises every sales.id into a
        // temp table and holds the table for the length of that copy.
        DB::statement('UPDATE sales AS s LEFT JOIN sales AS anchor ON anchor.id = s.order_id SET s.order_id = NULL WHERE s.order_id IS NOT NULL AND anchor.id IS NULL');

        Schema::table('sales', function (Blueprint $table) {
            $table->foreign('order_id')->references('id')->on('sales')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
        });
    }
};
