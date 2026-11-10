<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * When a schedule's ticket holders are reminded of their event (Schedule settings > Tickets >
 * Reminders, app:send-ticket-reminders). A table of its own, not columns on `roles`, because `roles`
 * sits at MySQL's 65,535-byte row limit (see 2026_10_07_000005_create_venue_map_settings_table.php).
 *
 * No row reads as the defaults below (Role::ticketReminderSetting() has the same ones): reminders on,
 * a day before. That default is deliberate and was chosen on 2026-11-10: every schedule, existing ones
 * included, reminds its ticket holders unless its owner switches it off.
 */
return new class extends Migration
{
    public function up(): void
    {
        // A short lock wait (the foreign key takes a metadata lock on `roles`), and safe to run
        // again after a run that stopped half way, as 2026_10_27_000000 is.
        $previous = DB::selectOne('SELECT @@SESSION.lock_wait_timeout AS value')->value;
        DB::statement('SET SESSION lock_wait_timeout = 10');

        try {
            if (Schema::hasTable('ticket_reminder_settings')) {
                return;
            }

            try {
                Schema::create('ticket_reminder_settings', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('role_id')->unique()->constrained()->cascadeOnDelete();
                    $table->boolean('enabled')->default(true);
                    $table->unsignedSmallInteger('hours_before')->default(24);
                    $table->timestamps();
                });
            } catch (Throwable $e) {
                DB::statement('SET SESSION lock_wait_timeout = '.(int) $previous);
                try {
                    Schema::dropIfExists('ticket_reminder_settings');
                } catch (Throwable) {
                }

                throw $e;
            }
        } finally {
            DB::statement('SET SESSION lock_wait_timeout = '.(int) $previous);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_reminder_settings');
    }
};
