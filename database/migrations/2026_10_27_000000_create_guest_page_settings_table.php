<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * What a schedule's guest page shows (Style > Events), one row per schedule whose owner has saved
 * the form since it existed. A table of its own, and not columns on `roles`, because `roles` is full:
 * it sits at MySQL's 65,535-byte row limit, and an ALTER adding one more column fails with errno
 * 1118 (see 2026_10_07_000005_create_venue_map_settings_table.php). The next switch about what the
 * guest page shows goes here too.
 *
 * No row reads as every default below (Role::guestPageSetting() has the same defaults).
 *
 * show_next_event: the card naming the next event above the month (show-guest.blade.php).
 */
return new class extends Migration
{
    public function up(): void
    {
        // A short lock wait (the foreign key takes a metadata lock on `roles`), and safe to run
        // again after a run that stopped half way, as 2026_10_22_000001 is.
        $previous = DB::selectOne('SELECT @@SESSION.lock_wait_timeout AS value')->value;
        DB::statement('SET SESSION lock_wait_timeout = 10');

        try {
            if (Schema::hasTable('guest_page_settings')) {
                return;
            }

            try {
                Schema::create('guest_page_settings', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('role_id')->unique()->constrained()->cascadeOnDelete();
                    $table->boolean('show_next_event')->default(true);
                    $table->timestamps();
                });
            } catch (\Throwable $e) {
                // The keys are statements of their own after CREATE TABLE: one that failed (a lock
                // wait) would leave a table the next run passes by. It is new, so it goes.
                DB::statement('SET SESSION lock_wait_timeout = '.(int) $previous);
                try {
                    Schema::dropIfExists('guest_page_settings');
                } catch (\Throwable) {
                }

                throw $e;
            }
        } finally {
            DB::statement('SET SESSION lock_wait_timeout = '.(int) $previous);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_page_settings');
    }
};
