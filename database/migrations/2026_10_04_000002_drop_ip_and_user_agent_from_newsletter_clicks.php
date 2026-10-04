<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Every click on a newsletter link stored the reader's raw IP address and browser, forever,
     * and nothing ever read either column: the stats use the URL and the time. Data kept for no
     * purpose is the kind GDPR Art. 5(1)(c) says not to keep, so what they hold is erased, and the
     * code no longer writes them.
     *
     * Emptied rather than dropped, despite the filename. `migrate --force` runs in the new
     * container's start command while the old containers are still serving, and those still write
     * both columns on every click: dropping them here would turn each newsletter link clicked
     * during the rollover into a 500. Drop them in a later release, once no running code names them.
     *
     * In batches by primary key, so a large table is never locked in one long UPDATE.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('newsletter_clicks', 'ip_address') || ! Schema::hasColumn('newsletter_clicks', 'user_agent')) {
            return;
        }

        do {
            $ids = DB::table('newsletter_clicks')
                ->where(fn ($query) => $query->whereNotNull('ip_address')->orWhereNotNull('user_agent'))
                ->limit(1000)
                ->pluck('id');

            if ($ids->isNotEmpty()) {
                DB::table('newsletter_clicks')->whereIn('id', $ids)->update(['ip_address' => null, 'user_agent' => null]);
            }
        } while ($ids->count() === 1000);
    }

    public function down(): void
    {
        // The values are gone; there is nothing to restore.
    }
};
