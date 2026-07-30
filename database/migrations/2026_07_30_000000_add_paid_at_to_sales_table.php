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
            $table->index(['paid_at']);
        });

        // Backfill: every sale already in a paid state is treated as having been paid when it was
        // created. That is exact for the immediate-paid paths (RSVP, free, cash checkout marked at
        // the till) and the closest available approximation for the rest.
        DB::table('sales')
            ->where('status', 'paid')
            ->whereNull('paid_at')
            ->update(['paid_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['paid_at']);
            $table->dropColumn('paid_at');
        });
    }
};
