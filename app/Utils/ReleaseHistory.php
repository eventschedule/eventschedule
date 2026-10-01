<?php

namespace App\Utils;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * When each app version first ran on this install - the deploy dates the growth payload needs to
 * line a change in a metric up with the release that caused it.
 *
 * Nothing else records them. Deploys are manual, so a git tag only says a version EXISTED by some
 * date, not when it went live, and "this release moved signups" could not be dated closer than a
 * week or two. touch() runs on every scheduler heartbeat (both rails); the first tick after a
 * deploy appends the new version with that time.
 *
 * Stored in one Setting row as a JSON list, newest last. A cache key short-circuits every tick in
 * between, because Setting::set() busts the whole settings map and doing that once a minute would
 * cost far more than the stamp is worth. If the cache is lost, the next tick reads the row, sees the
 * version already recorded, and only re-primes the key.
 */
class ReleaseHistory
{
    private const SETTING = 'release_history';

    private const CACHE_KEY = 'release_history.current';

    /** Kept bounded: a year of weekly releases with room to spare. */
    private const MAX_ENTRIES = 100;

    public static function touch(): void
    {
        $version = (string) config('self-update.version_installed');
        if ($version === '' || Cache::get(self::CACHE_KEY) === $version) {
            return;
        }

        $history = self::all();
        if (($history[count($history) - 1]['version'] ?? null) !== $version) {
            $history[] = ['version' => $version, 'first_seen_at' => now()->toIso8601String()];
            Setting::set(self::SETTING, json_encode(array_slice($history, -self::MAX_ENTRIES)));
        }

        Cache::forever(self::CACHE_KEY, $version);
    }

    /** @return list<array{version: string, first_seen_at: string}> oldest first */
    public static function all(): array
    {
        $decoded = json_decode((string) Setting::get(self::SETTING, '[]'), true);

        return is_array($decoded) ? array_values($decoded) : [];
    }
}
