<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * How an event got onto a schedule when it was not typed into the event form.
     *
     * import_source says which import made it (the vocabulary is Event::IMPORT_SOURCES): until now
     * an event parsed from a flyer, copied from Eventbrite or pulled from a calendar was
     * indistinguishable from one made by hand, so nothing could say whether importing is what fills
     * a schedule. import_batch groups the events of one sitting on the import page, which is what
     * "N events added" counts and what "Undo this import" removes.
     *
     * Both are stamped once, at creation, by the code that creates the event. Neither is fillable:
     * EventRepo::saveEvent() fills an Event from the whole request, so a fillable column here would
     * be writable from every event form.
     *
     * Deliberately no ->after(): several events columns live in migrations dated later than
     * some of their neighbours, so anchoring to one breaks a fresh migrate.
     *
     * Forward-only, with no backfill. Nothing recorded how an existing event was made, so every
     * earlier row keeps null, which reads as "made by hand, or before this was tracked".
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('import_source', 20)->nullable();
            $table->char('import_batch', 12)->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['import_batch']);
            $table->dropColumn(['import_source', 'import_batch']);
        });
    }
};
