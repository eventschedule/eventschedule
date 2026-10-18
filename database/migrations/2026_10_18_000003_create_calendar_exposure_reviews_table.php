<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * What the operator did about a schedule /admin/private-calendars listed, one row per schedule
 * (App\Http\Controllers\AdminCalendarPrivacyController).
 *
 * reviewed_*: marked reviewed; the schedule is listed again only for a match newer than this.
 * hidden_*: its public flagged events taken off the page (and how many were kept because people
 * hold tickets to them), and the placeholder venues made out of meeting links taken down.
 * paused_*: its calendar sync stopped reading (what it was is kept with the sync's own settings,
 * calendar_sync_settings.{p}_stopped_direction, for Resume).
 * emailed_*: its owner told, and what about; the seven days before another may be sent run from
 * emailed_at.
 */
return new class extends Migration
{
    public function up(): void
    {
        // A short lock wait, and safe to run again after a run that stopped half way: the
        // foreign keys take metadata locks on events, roles and users.
        $previous = DB::selectOne('SELECT @@SESSION.lock_wait_timeout AS value')->value;
        DB::statement('SET SESSION lock_wait_timeout = 10');

        try {
            if (! Schema::hasTable('calendar_exposure_reviews')) {
                Schema::create('calendar_exposure_reviews', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('role_id')->unique()->constrained()->cascadeOnDelete();
                    $table->timestamp('reviewed_at')->nullable();
                    $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->timestamp('hidden_at')->nullable();
                    $table->foreignId('hidden_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->unsignedInteger('hidden_count')->default(0);
                    $table->unsignedInteger('kept_count')->default(0);
                    $table->unsignedInteger('venue_count')->default(0);
                    $table->timestamp('paused_at')->nullable();
                    $table->foreignId('paused_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->timestamp('emailed_at')->nullable();
                    $table->foreignId('emailed_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->json('emailed_kinds')->nullable();
                    $table->timestamps();
                });
            }
        } finally {
            DB::statement('SET SESSION lock_wait_timeout = '.(int) $previous);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_exposure_reviews');
    }
};
