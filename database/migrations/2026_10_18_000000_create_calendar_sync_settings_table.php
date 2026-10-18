<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * How a schedule's standing calendar syncs (Google, Outlook, CalDAV) treat what they read in.
 *
 * Until 2026-10 every event a sync read from a calendar went straight onto the schedule's public
 * page, and one schedule published somebody's whole work calendar that way: meeting passcodes,
 * dial-in codes, an email thread about a child's doctor. From now on a sync's events arrive as
 * drafts, and publish as they arrive only when the owner says so for THAT calendar
 * ({p}_publishes, bound by {p}_publish_binding to the owner and the calendar it was said for,
 * App\Services\CalendarArrival::publishes()). No row reads as "drafts".
 *
 * The syncs reading a calendar today keep publishing: the backfill below writes their consent,
 * with no binding yet, and the first sync after this release binds it to the calendar it reads.
 *
 * A table of its own because `roles` is at MySQL's 65,535-byte row limit (errno 1118).
 *
 * Per provider (google, microsoft, caldav):
 *  - publishes / publish_binding / publish_confirmed_at: the owner's consent, the owner and
 *    calendar it is for, and when the owner said it themselves (null for the backfill, which is
 *    why a carried-over sync on a main calendar is asked about once).
 *  - reread_at / reread_cursor: the one full read every sync makes so the privacy filters reach
 *    events read in before them, and where a read too big for one run resumes.
 *  - last_pull / first_pull: what the last read and the very first read did, in counts.
 *  - stopped_reason / stopped_at / stopped_direction: reading was turned off for the owner (by
 *    the operator, or by a change of owner), when, and what it was before, for Resume.
 *
 * taken_off_notified_at: when the owner was mailed that a sync took public events off the page.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_sync_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->unique()->constrained()->cascadeOnDelete();

            foreach (['google', 'microsoft', 'caldav'] as $p) {
                $table->boolean("{$p}_publishes")->default(false);
                $table->char("{$p}_publish_binding", 40)->nullable();
                $table->timestamp("{$p}_publish_confirmed_at")->nullable();
                $table->timestamp("{$p}_reread_at")->nullable();
                $table->text("{$p}_reread_cursor")->nullable();
                $table->json("{$p}_last_pull")->nullable();
                $table->json("{$p}_first_pull")->nullable();
                $table->string("{$p}_stopped_reason", 20)->nullable();
                $table->timestamp("{$p}_stopped_at")->nullable();
                $table->string("{$p}_stopped_direction", 10)->nullable();
            }

            // When the owner was told that a sync took events off their public page. Once.
            $table->timestamp('taken_off_notified_at')->nullable();

            $table->timestamps();
        });

        $now = now();
        DB::table('roles')
            ->select(['id', 'sync_direction', 'microsoft_sync_direction', 'caldav_sync_direction'])
            ->where(function ($q) {
                $q->whereIn('sync_direction', ['from', 'both'])
                    ->orWhereIn('microsoft_sync_direction', ['from', 'both'])
                    ->orWhereIn('caldav_sync_direction', ['from', 'both']);
            })
            ->orderBy('id')
            ->chunk(500, function ($roles) use ($now) {
                $rows = [];
                foreach ($roles as $role) {
                    $rows[] = [
                        'role_id' => $role->id,
                        'google_publishes' => in_array($role->sync_direction, ['from', 'both'], true),
                        'microsoft_publishes' => in_array($role->microsoft_sync_direction, ['from', 'both'], true),
                        'caldav_publishes' => in_array($role->caldav_sync_direction, ['from', 'both'], true),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                DB::table('calendar_sync_settings')->insertOrIgnore($rows);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_sync_settings');
    }
};
