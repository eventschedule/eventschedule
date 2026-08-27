<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * When the person in this seat came through the door.
 *
 * The console draws sold / blocked / held / available and has no "arrived" at all, and the check-in
 * screen has no search of any kind - so "is C14 here yet" was unanswerable on every surface. The
 * printed sheet has no column for it either, and front of house cannot even tick it by hand.
 *
 * NOT derived from sale_tickets.seats. That column is an admission-slot map (1..n => timestamp) and
 * carries no location; the two line up only positionally, through seatLabels() ordering, which is
 * good enough to LABEL a scan and far too fragile to key arrival state on. A scan checks in a whole
 * ticket line, so every seat bound to that line is stamped directly and the correspondence question
 * never arises.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seating_seats', function (Blueprint $table) {
            $table->timestamp('checked_in_at')->nullable()->after('sale_ticket_id');
        });
    }

    public function down(): void
    {
        Schema::table('seating_seats', function (Blueprint $table) {
            $table->dropColumn('checked_in_at');
        });
    }
};
