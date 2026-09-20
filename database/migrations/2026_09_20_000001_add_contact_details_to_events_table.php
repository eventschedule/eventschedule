<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The contact details of whoever sent a guest booking request (issue #124).
     *
     * The booking form has always asked for a name and an email and then thrown them away, so the
     * owner received a request with no way to answer it. These hold what the visitor typed.
     *
     * Deliberately NOT added to Event::$fillable: EventRepo::saveEvent() does a blanket
     * fill($request->all()) that an anonymous guest can reach through guestImport(), so a fillable
     * contact block could be forged, and buildClonePayload() would copy a stranger's address onto
     * every clone. EventController::bookingRequest() assigns them directly instead, and
     * BackupService carries them by explicit key.
     *
     * Not to be confused with events.ask_phone / require_phone / country_code_phone, which are the
     * TICKET BUYER's phone settings at checkout.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (! Schema::hasColumn('events', 'contact_name')) {
                $table->string('contact_name')->nullable()->after('is_guest_submission');
            }
            if (! Schema::hasColumn('events', 'contact_email')) {
                $table->string('contact_email')->nullable()->after('contact_name');
            }
            if (! Schema::hasColumn('events', 'contact_phone')) {
                $table->string('contact_phone')->nullable()->after('contact_email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['contact_name', 'contact_email', 'contact_phone']);
        });
    }
};
