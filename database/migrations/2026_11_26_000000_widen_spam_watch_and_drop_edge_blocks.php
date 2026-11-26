<?php

use App\Models\Setting;
use App\Services\SpamWatch;
use App\Utils\EdgeAddress;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Two things the review of the nexus's blocked accounts (2026-11) found.
 *
 * The support and billing scams named themselves with words the watch list did not hold
 * ("customer support", "geek squad", "auto-renewal"). An install that never saved its list reads
 * the new SpamWatch::DEFAULT_WORDS already; one that saved its own (the nexus added the
 * conference phishing's names) is given the new words beside its own. A list saved empty means
 * "watch nothing" and is left so.
 *
 * Every IP entry was a Cloudflare edge, because the visitor's address was read from a header that
 * on hosted is the edge's: each one refused everyone that edge serves and never the person
 * blocked. Blocklist ignores them now; this takes them off the list.
 */
return new class extends Migration
{
    private const ADDED = [
        'customer support',
        'geek squad',
        'auto-renewal',
        'renewal notice',
        'billing invoice',
        'norton',
        'mcafee',
        'secure document',
        'docusign',
    ];

    public function up(): void
    {
        if (Schema::hasTable('settings')) {
            $stored = Setting::get(SpamWatch::SETTING);

            if ($stored !== null && trim((string) $stored) !== '') {
                $words = SpamWatch::parse((string) $stored);
                $missing = array_values(array_diff(self::ADDED, $words));
                $merged = array_slice(array_merge($words, $missing), 0, SpamWatch::MAX_WORDS);

                if ($missing !== [] && $merged !== $words) {
                    Setting::set(SpamWatch::SETTING, implode(', ', $merged));
                }
            }
        }

        if (Schema::hasTable('blocklist_entries')) {
            $edge = DB::table('blocklist_entries')
                ->where('type', 'ip')
                ->get(['id', 'match_key'])
                ->filter(fn ($row) => EdgeAddress::rangeTouchesCloudflare((string) $row->match_key))
                ->pluck('id');

            if ($edge->isNotEmpty()) {
                DB::table('blocklist_entries')->whereIn('id', $edge)->delete();
            }
        }
    }

    public function down(): void
    {
        // The edge entries refused strangers, and the words are the operator's to take off.
    }
};
