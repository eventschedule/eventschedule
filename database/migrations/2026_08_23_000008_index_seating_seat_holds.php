<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lets the lapsed-hold sweep find its rows instead of scanning the table.
     *
     * `SeatHoldService::sweepExpiredHolds()` filters on status + hold_kind + hold_expires_at, and
     * none of the indexes created with the table leads with status. Without this one the hourly
     * ReleaseTickets run would be a full-table UPDATE, and under InnoDB's REPEATABLE READ a
     * scanning UPDATE takes next-key locks on every row it examines - locking the whole
     * seating_seats table for the duration and blocking every concurrent acquire() and
     * claimForSale(). That would turn an explicitly OPTIONAL housekeeping sweep (expiry is
     * evaluated at read time, so the sweep changes nothing a client can observe) into checkout
     * lock-wait timeouts on a busy on-sale.
     */
    public function up(): void
    {
        Schema::table('seating_seats', function (Blueprint $table) {
            $table->index(['status', 'hold_kind', 'hold_expires_at'], 'seating_seats_sweep_idx');
        });
    }

    public function down(): void
    {
        Schema::table('seating_seats', function (Blueprint $table) {
            $table->dropIndex('seating_seats_sweep_idx');
        });
    }
};
