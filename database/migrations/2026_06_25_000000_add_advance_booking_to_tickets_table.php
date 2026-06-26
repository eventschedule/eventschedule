<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // When true, holders of this pass can reserve a seat for a specific
            // occurrence in advance (drawing from the shared per-occurrence pool)
            // instead of only scanning in at the door.
            $table->boolean('pass_allow_booking')->default(false)->after('pass_event_ids');
            // Optional cap on how many seats per occurrence advance bookings may
            // take, protecting walk-up inventory. Null = no pass-specific cap
            // (the shared house capacity is the only ceiling).
            $table->unsignedInteger('pass_seats_per_occurrence')->nullable()->after('pass_allow_booking');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['pass_allow_booking', 'pass_seats_per_occurrence']);
        });
    }
};
