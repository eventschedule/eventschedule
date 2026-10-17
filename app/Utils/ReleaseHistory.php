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
 *
 * The version alone could not tell deploys apart: it moves only at an "Update version" commit, so
 * every deploy in between shipped under the same number and never showed here. Where the host says
 * which commit it built (APP_COMMIT, see commit()), a new commit under the same version is a new
 * entry too. A container that does not know its commit never records one: the worker and the web
 * container run the same deploy, and one of them lacking the variable must not record the deploy
 * over and over. At a version change it records the version with no commit.
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
        if ($version === '') {
            return;
        }

        $commit = self::commit();
        $seen = Cache::get(self::CACHE_KEY);
        if ($seen === self::marker($version, $commit)
            || ($commit === null && is_string($seen) && str_starts_with($seen, $version.'|'))) {
            return;
        }

        // Read from the table, not Setting::get()'s cached map: this is a read-modify-write, and a
        // map cached in another container's store (CACHE_STORE=file with a worker beside the web
        // container) would be stale - appending to it would drop whatever that container wrote.
        $history = self::decode(Setting::query()->where('key', self::SETTING)->value('value'));
        $last = $history[count($history) - 1] ?? [];

        // While a deploy rolls out, the old container and the new one both tick for a few minutes,
        // and each would read the other's entry as the last one and append its own again. A commit
        // already recorded under this version is not a new deploy. The cost: a rollback to a commit
        // recorded earlier under the same version is not recorded either.
        $recorded = $commit !== null && collect(array_slice($history, -10))
            ->contains(fn ($entry) => ($entry['version'] ?? null) === $version && ($entry['commit'] ?? null) === $commit);

        if (($last['version'] ?? null) !== $version || ($commit !== null && ($last['commit'] ?? null) !== $commit && ! $recorded)) {
            $history[] = ['version' => $version, 'commit' => $commit, 'first_seen_at' => now()->toIso8601String()];
            Setting::set(self::SETTING, json_encode(array_slice($history, -self::MAX_ENTRIES)));
        }

        Cache::forever(self::CACHE_KEY, self::marker($version, $history[count($history) - 1]['commit'] ?? null));
    }

    /**
     * The commit this container was built from, as 12 lowercase hex characters, or null when the
     * host does not say. APP_COMMIT is an env var, so anything that is not a hash is refused rather
     * than carried into the growth payload.
     */
    public static function commit(): ?string
    {
        $value = strtolower(trim((string) config('app.commit')));

        return preg_match('/^[0-9a-f]{7,40}\z/', $value) ? substr($value, 0, 12) : null;
    }

    private static function marker(string $version, ?string $commit): string
    {
        return $commit === null ? $version : $version.'|'.$commit;
    }

    /**
     * From the table, not Setting::get()'s cached map, for the reason touch() reads it there: the
     * worker writes this row and the web container serves the payload, and a map cached in the web
     * container's own store would never show the release. One indexed read per build.
     *
     * Entries recorded before APP_COMMIT existed have no `commit` key.
     *
     * @return list<array{version: string, commit?: ?string, first_seen_at: string}> oldest first
     */
    public static function all(): array
    {
        return self::decode(Setting::query()->where('key', self::SETTING)->value('value'));
    }

    private static function decode(?string $json): array
    {
        $decoded = json_decode((string) ($json ?? '[]'), true);

        return is_array($decoded) ? array_values($decoded) : [];
    }
}
