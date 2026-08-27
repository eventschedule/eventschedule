<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * events.event_url was narrower than the values the app already validates into it.
     *
     * appointment_types.location_url is a varchar(500), validated at max:500, and
     * AppointmentService copies it verbatim onto the event it creates for a booking. A Teams or
     * tokenised Zoom join URL routinely exceeds 255 characters, so every booking of such an
     * appointment type hit MySQL 1406 on a varchar(255). The guest-submission form validated
     * event_url at max:500 into the same column, with the same result.
     *
     * There is no index on the column, so this is a plain widening.
     *
     * Note: the public API keeps its stricter max:255 for event_url. A rule tighter than its
     * column cannot overflow, so it stays as-is rather than widening an API contract as a side
     * effect of this fix.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('event_url', 500)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('event_url')->nullable()->change();
        });
    }
};
