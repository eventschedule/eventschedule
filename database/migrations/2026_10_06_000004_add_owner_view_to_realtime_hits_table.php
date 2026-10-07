<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * What a schedule's own Realtime tab (/analytics, App\Services\ScheduleRealtime) needs from a
     * row that /admin/realtime never did.
     *
     *  - is_team: the page view was made by a signed-in member of the schedule the page belongs to.
     *    It comes from the signed context (RealtimeTracker::context()), not from consent, so a team
     *    member who declined cookies is still left out of the owner's numbers. Analytics leaves the
     *    same people out of its daily counts.
     *  - owner_visible: the visitor accepted cookies on a notice that said a schedule's organizer
     *    sees visits to its pages. The choice itself records that (the "org" token of the
     *    cookie_consent cookie; RealtimeTracker::consentCoversOrganizers()). A choice made under
     *    the earlier wording covered "we", the platform, so such a visitor is counted for the
     *    owner and never listed.
     *  - (role_id, last_seen_at): the owner's query is bounded by schedule first. Without it the
     *    poll scans the whole half hour of the install for every organizer.
     *
     * Columns and an index only. An earlier draft also stored the moment the notice changed and
     * compared choices with it; nothing reads that any more.
     *
     * Each step checks first, so a run interrupted half way (MySQL commits every DDL statement on
     * its own) finishes on the next attempt instead of failing on a column that already exists.
     */
    public function up(): void
    {
        if (! Schema::hasTable('realtime_hits')) {
            return;
        }

        if (! Schema::hasColumn('realtime_hits', 'is_team')) {
            Schema::table('realtime_hits', function (Blueprint $table) {
                $table->boolean('is_team')->default(false);
            });
        }

        if (! Schema::hasColumn('realtime_hits', 'owner_visible')) {
            Schema::table('realtime_hits', function (Blueprint $table) {
                $table->boolean('owner_visible')->default(false);
            });
        }

        if (! $this->hasIndex('realtime_hits_role_id_last_seen_at_index')) {
            Schema::table('realtime_hits', function (Blueprint $table) {
                $table->index(['role_id', 'last_seen_at']);
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('realtime_hits')) {
            return;
        }

        if ($this->hasIndex('realtime_hits_role_id_last_seen_at_index')) {
            Schema::table('realtime_hits', function (Blueprint $table) {
                $table->dropIndex('realtime_hits_role_id_last_seen_at_index');
            });
        }

        foreach (['owner_visible', 'is_team'] as $column) {
            if (Schema::hasColumn('realtime_hits', $column)) {
                Schema::table('realtime_hits', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }

    private function hasIndex(string $name): bool
    {
        return collect(DB::select('SHOW INDEX FROM realtime_hits'))->contains(fn ($row) => $row->Key_name === $name);
    }
};
