<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Links an event to the seating plan TEMPLATE it sells from.
     *
     * On events rather than tickets because a plan describes the room, and every ticket
     * type at that occurrence sells out of the same room - the per-band mapping runs the
     * other way, via seating_sections.ticket_id.
     *
     * A NULL here IS the "not an allocated event" flag; there is deliberately no separate
     * boolean to disagree with it.
     *
     * Deliberately added to Event::$fillable, because EventRepo::buildClonePayload() loops
     * getFillable(): a cloned event then references the same template, while its occurrence
     * maps (keyed by event_id in event_seating_maps) correctly do NOT come along. That is
     * exactly the wanted behaviour, and it only holds while this stays fillable.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Plain indexed column, NO foreign key - the same call
            // 2026_08_11_000001_add_appointment_type_id_to_events_table.php made, for the same
            // reason and with an added one.
            //
            // The reason it made: seating_plans is soft-deleted (is_deleted), so an ON DELETE
            // cascade would essentially never fire.
            //
            // The added one: MySQL only supports ALGORITHM=INPLACE for ADD FOREIGN KEY when
            // foreign_key_checks is off, and Laravel does not turn it off here. On a production
            // events table the constraint therefore forces ALGORITHM=COPY - a full rebuild under
            // LOCK=SHARED, blocking every write to events for minutes. Worse, AppUpdateService
            // runs `migrate --force` from an HTTP request (AppController::update,
            // AdminController::appUpdateRun), so a rebuild that outlives PHP-FPM's timeout kills
            // the worker before the migrations row is written, and the retry then dies on a
            // duplicate column.
            //
            // Without the constraint both statements are online: the ADD COLUMN is INSTANT on
            // MySQL 8.0.29+ and the index build is INPLACE.
            //
            // Nothing is lost. EventRepo::saveEvent() already validates the plan on every write
            // (ownership + Enterprise, reverting to the previous value on failure), and
            // SeatingPlanController::destroy() soft-deletes rather than removing the row, so the
            // dangling-reference case the FK guards against is not reachable through the app.
            $table->unsignedBigInteger('seating_plan_id')->nullable()->after('total_tickets_mode');
            $table->index('seating_plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['seating_plan_id']);
            $table->dropColumn('seating_plan_id');
        });
    }
};
