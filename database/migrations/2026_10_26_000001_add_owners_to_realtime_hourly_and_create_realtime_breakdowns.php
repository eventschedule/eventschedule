<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Whose pages a total counts, so a blocked account's are left out of every range afterwards, and
 * hourly totals of the three breakdown cards of /admin/dashboard for its longer ranges
 * (App\Services\RealtimeRollup).
 *
 * The three owner columns name the ACCOUNT A PAGE BELONGS TO, never who viewed it: the owner of
 * the schedule the page is on (role_owner_id), and for an event's page the account that made the
 * event (event_user_id) and the owner of the schedule the event belongs to (event_owner_id), which
 * is BlockedAccounts' own rule for "their schedules and events". 0 is none, not NULL: a NULL in a
 * unique key never equals another, and every run would add a row instead of adding to one. Rows
 * counted before this migration have 0 in all three.
 *
 * realtime_breakdowns: per hour, the page views of each page and each country, and the visits each
 * referring source began, with the same three owners. Counts only, with no visitor: a row never
 * says who viewed a page. A page is keyed as the live page keys it (item; item_hash is its sha1, so
 * a long path fits the unique index), with what its label is made from (surface, role_id,
 * event_id, title, channel). Kept 31 days, the longest range the cards offer.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('realtime_hourly', 'role_owner_id')) {
            Schema::table('realtime_hourly', function (Blueprint $table) {
                $table->unsignedBigInteger('role_owner_id')->default(0);
                $table->unsignedBigInteger('event_user_id')->default(0);
                $table->unsignedBigInteger('event_owner_id')->default(0);
            });

            Schema::table('realtime_hourly', function (Blueprint $table) {
                $table->unique(['hour', 'surface', 'role_owner_id', 'event_user_id', 'event_owner_id'], 'realtime_hourly_owners_unique');
                $table->dropUnique(['hour', 'surface']);
            });
        }

        if (! Schema::hasTable('realtime_breakdowns')) {
            Schema::create('realtime_breakdowns', function (Blueprint $table) {
                $table->id();
                $table->dateTime('hour');
                $table->string('dimension', 8);
                $table->char('item_hash', 40);
                $table->string('item', 500);
                $table->string('surface', 8)->nullable();
                $table->unsignedBigInteger('role_id')->default(0);
                $table->unsignedBigInteger('event_id')->default(0);
                $table->string('title', 255)->nullable();
                $table->string('channel', 32)->nullable();
                $table->unsignedBigInteger('role_owner_id')->default(0);
                $table->unsignedBigInteger('event_user_id')->default(0);
                $table->unsignedBigInteger('event_owner_id')->default(0);
                $table->unsignedInteger('views')->default(0);

                $table->unique(['hour', 'dimension', 'item_hash', 'role_owner_id', 'event_user_id', 'event_owner_id'], 'realtime_breakdowns_unique');
                $table->index(['dimension', 'hour']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('realtime_breakdowns');

        if (Schema::hasColumn('realtime_hourly', 'role_owner_id')) {
            // Hours split by owner fold back into one row each before the old key returns.
            $rows = DB::table('realtime_hourly')
                ->groupBy('hour', 'surface')
                ->get(['hour', 'surface', DB::raw('SUM(page_views) as page_views'), DB::raw('SUM(visits) as visits')]);

            DB::table('realtime_hourly')->delete();

            Schema::table('realtime_hourly', function (Blueprint $table) {
                $table->dropUnique('realtime_hourly_owners_unique');
                $table->dropColumn(['role_owner_id', 'event_user_id', 'event_owner_id']);
                $table->unique(['hour', 'surface']);
            });

            foreach ($rows->chunk(500) as $chunk) {
                DB::table('realtime_hourly')->insert($chunk->map(fn ($row) => (array) $row)->all());
            }
        }
    }
};
