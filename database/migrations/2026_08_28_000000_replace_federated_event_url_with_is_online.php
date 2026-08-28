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
        Schema::table('federated_events', function (Blueprint $table) {
            $table->boolean('is_online')->default(false)->after('image_path');
        });

        // Backfill before the drop. Already-synced listings are not re-pushed just
        // because the payload changed shape - push() only re-sends unsynced rows and
        // recurring events - so without this every existing online listing would
        // silently become in-person.
        DB::table('federated_events')
            ->whereNotNull('event_url')
            ->where('event_url', '!=', '')
            ->update(['is_online' => true]);

        Schema::table('federated_events', function (Blueprint $table) {
            $table->dropColumn('event_url');
        });
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
