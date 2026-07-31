<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The moment a sale actually became paid.
 *
 * `sales.created_at` is not a usable proxy: the status column defaults to 'unpaid', so a cash
 * sale is created unpaid and only becomes paid when the owner marks it paid, possibly in a later
 * month. Monthly allowances have to count at payment time or cash escapes them entirely and any
 * sale spanning a month boundary is counted in neither month.
 *
 * `sales.confirmed_at` could not be reused: it is the appointment-approval idempotency latch
 * (AppointmentService::confirm), not a payment timestamp.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->timestamp('paid_at')->nullable()->after('status');

            // Composite, not a standalone paid_at index. The allowance query filters
            //   event_id IN (<subquery>) AND status = 'paid' AND paid_at >= ?
            // and MySQL picks only one index on `sales`, where the existing event_id foreign-key
            // index would beat a single-column paid_at - leaving paid_at as a row-level filter. This
            // shape serves the semi-join on event_id and then satisfies both the status equality and
            // the date range from the index itself.
            $table->index(['event_id', 'status', 'paid_at'], 'sales_allowance_index');
        });

        // Backfill: every sale already in a paid state is treated as having been paid when it was
        // created. That is exact for the immediate-paid paths (RSVP, free, cash checkout marked at
        // the till) and the closest available approximation for the rest.
        // Chunked, not one statement over the whole table: whereNull('paid_at') matches every row
        // on a column created seconds ago, so a single UPDATE is a full scan holding locks until
        // commit, which stalls concurrent checkouts and produces one enormous binlog event. Matches
        // the chunkById pattern used by the other backfill migrations in this repo.
        DB::table('sales')
            ->where('status', 'paid')
            ->whereNull('paid_at')
            ->select('id')
            ->orderBy('id')
            ->chunkById(500, function ($rows) {
                DB::table('sales')
                    ->whereIn('id', collect($rows)->pluck('id'))
                    ->update(['paid_at' => DB::raw('created_at')]);
            });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('sales_allowance_index');
            $table->dropColumn('paid_at');
        });
    }
};
