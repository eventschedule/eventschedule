<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Names the seating band a ticket sells, matching seating_sections.band.
     *
     * The mapping lives on the TICKET rather than on the snapshot sections because pricing is set
     * once on the event, while sections are copied per occurrence: a recurring event with 200
     * dates would otherwise need its bands re-mapped 200 times. materialize() resolves each
     * snapshot section's ticket_id from this at copy time, so a single date can still be given a
     * different mapping afterwards without disturbing the rest.
     *
     * NULL means the ticket is not seat-allocated - general admission, standing, a pass or an
     * add-on - and it keeps the quantity-based path it has always used, untouched.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('seating_band', 100)->nullable()->after('max_per_order');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('seating_band');
        });
    }
};
