<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // How many people this pass admits at each event (including the
            // holder). 1 (or null) = holder only; 2 lets the holder bring one
            // guest, so the QR may be scanned twice per event. Party size is
            // independent of the visit allowance: each event still counts as a
            // single use against pass_max_uses.
            $table->unsignedInteger('pass_admits_per_event')->nullable()->after('pass_seats_per_occurrence');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('pass_admits_per_event');
        });
    }
};
