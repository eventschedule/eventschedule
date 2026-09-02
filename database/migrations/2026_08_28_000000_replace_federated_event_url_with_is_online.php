<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * federated_events.event_url held the full online joining link, and nothing ever
     * read it.
     *
     * FederatedEvent::isOnline()/isHybrid() were its only readers and had no callers;
     * the public card decides its "Online" label from the absence of a venue instead.
     * So the column was retaining third-party meeting links - events.event_url is
     * routinely a tokenised Zoom or Teams URL, which is why it was widened to 500 in
     * 2026_08_27_000001 - for no purpose, and the federation docs never disclosed that
     * it was sent at all.
     *
     * A boolean keeps everything the app actually used, and lets a hybrid event be
     * labelled correctly, which the venue-absence heuristic never could.
     */
    public function up(): void
    {
        // Each step guarded, because these are three statements and MySQL cannot roll them back as
        // one: Schema\Grammars\Grammar::$transactions is false and MySqlGrammar does not override
        // it, so DDL implicitly commits, and Migrator::runUp() writes the migrations row only after
        // up() RETURNS. An interruption between the add and the drop - a lock wait, a killed
        // connection, or the PHP timeout that AppUpdateService's in-request `migrate --force` can
        // hit - would otherwise leave is_online created, nothing logged, and every retry dying on
        // "1060 Duplicate column name", blocking the other ten migrations with no rollback path.
        if (! Schema::hasColumn('federated_events', 'is_online')) {
            Schema::table('federated_events', function (Blueprint $table) {
                $table->boolean('is_online')->default(false)->after('image_path');
            });
        }

        // Backfill before the drop. Already-synced listings are not re-pushed just
        // because the payload changed shape - push() only re-sends unsynced rows and
        // recurring events - so without this every existing online listing would
        // silently become in-person.
        //
        // Guarded on event_url, not is_online: a retry that skipped this because is_online now
        // exists would leave already-online listings marked in-person, which is the exact data
        // loss the backfill exists to prevent.
        if (Schema::hasColumn('federated_events', 'event_url')) {
            DB::table('federated_events')
                ->whereNotNull('event_url')
                ->where('event_url', '!=', '')
                ->update(['is_online' => true]);

            Schema::table('federated_events', function (Blueprint $table) {
                $table->dropColumn('event_url');
            });
        }
    }

    /**
     * The column comes back empty. The stored links are not recoverable, which is the
     * point of the change rather than a shortcoming of it.
     */
    public function down(): void
    {
        Schema::table('federated_events', function (Blueprint $table) {
            $table->string('event_url', 1024)->nullable()->after('image_path');
            $table->dropColumn('is_online');
        });
    }
};
