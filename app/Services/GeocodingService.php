<?php

namespace App\Services;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Every Google Geocoding API request the app makes, behind a shared cache.
 *
 * Google bills per request, and bills a ZERO_RESULTS answer exactly like a hit. The two callers
 * (Role's saving hook and the Validate button) used to ask directly, so the same address was paid
 * for again on every ask: the hourly demo reset recreates sixteen schedules in three cities and
 * geocoded all sixteen every hour, which on its own used up the monthly free tier.
 *
 * What is remembered, and for how long:
 *
 *   - A resolved address, for 30 days. That is the longest Google's terms let coordinates be
 *     cached (a place ID may be kept indefinitely, a latitude and longitude may not).
 *   - A definitive miss, for the same 30 days. Role's saving hook asks again for a schedule
 *     Google could not place, so this entry is what bounds that to one request a month per
 *     address however often the schedule is saved - and what lets an address Google learns
 *     later resolve by itself.
 *   - A transient failure (timeout, quota, a rejected key), for five minutes: long enough that
 *     a loop saving the same schedule cannot hammer a request that keeps failing, short enough
 *     that an owner who saves again gets a real retry.
 *
 * Where it is remembered matters as much. The caller is usually a model hook, often inside a
 * transaction, and on hosted the shared cache is a database table on the SAME connection. So
 * the answer goes to this process's own array store at once, and to the shared store only
 * after the caller's transaction commits: written inside it, the cache row would stay locked
 * until the commit, a rollback would discard an answer already paid for, and a failed cache
 * write would abort the save it had nothing to do with.
 *
 * The READ cannot be moved the same way - the answer is needed now - so it does happen inside
 * the caller's transaction. On the database store that is a plain SELECT, except when it finds
 * an entry that has expired and not yet been pruned, which it deletes there and then. That
 * leftover is accepted: it needs an expired entry for the very address being saved, inside a
 * transaction, in the hour before app:prune-cache removes it.
 *
 * And the cache is this class's bookkeeping, not the caller's business. If it cannot be read,
 * nothing is asked of Google at all: with nowhere to remember the answer every save would ask
 * again, which is the bill this class exists to stop.
 *
 * Static, so it is safe to call from a model hook and from a queued context.
 */
class GeocodingService
{
    public const OK = 'OK';

    /** No backend key on this install. Never cached, so setting one takes effect at once. */
    public const NOT_CONFIGURED = 'NOT_CONFIGURED';

    /** No usable answer came back at all: a timeout, a 5xx, a body that is not Google's. */
    public const UNAVAILABLE = 'UNAVAILABLE';

    private const ENDPOINT = 'https://maps.googleapis.com/maps/api/geocode/json';

    /** Statuses that are a verdict on the ADDRESS, rather than on the request or the account. */
    private const DEFINITIVE_MISSES = ['ZERO_RESULTS', 'INVALID_REQUEST'];

    private const SETTLED_TTL_DAYS = 30;

    private const TRANSIENT_TTL_MINUTES = 5;

    /**
     * Geocode an address, from the cache when it has been asked before.
     *
     * $fresh is for a person waiting on the answer: only a cached HIT is served, and anything
     * else is asked again. Pressing Validate after fixing a key, waiting out a quota, or on an
     * address Google has since learned must not repeat a stale "no" for the rest of the month.
     *
     * @return array{status: string, lat: float|null, lng: float|null, formatted_address: string|null, place_id: string|null, address_components: array}
     */
    public static function lookup(string $address, bool $fresh = false): array
    {
        $key = config('services.google.backend');

        if (! $key) {
            return self::result(self::NOT_CONFIGURED);
        }

        $cacheKey = self::cacheKey($address);

        try {
            $cached = Cache::store('array')->get($cacheKey) ?? Cache::get($cacheKey);
        } catch (\Throwable $e) {
            // On the database store a failed read is a failed statement on the caller's own
            // connection, and inside a transaction it may be a deadlock that has already rolled
            // that transaction back. Carrying on would hide it, so that one is the caller's.
            if ($e instanceof QueryException && DB::transactionLevel() > 0) {
                throw $e;
            }

            report($e);

            return self::result(self::UNAVAILABLE);
        }

        if (is_array($cached) && (! $fresh || self::isResolved($cached))) {
            return $cached;
        }

        $result = self::request($address, $key);

        // A failure in transit never replaces an answer. Only a $fresh lookup gets here holding
        // one - a "not found" the Validate button was asked to check again - and overwriting its
        // thirty days with the failure's five minutes would discard the entry that bounds the
        // saving hook's re-ask to one request a month.
        $holdsAnAnswer = is_array($cached) && ! self::isTransient($cached);

        if (! (self::isTransient($result) && $holdsAnAnswer)) {
            self::remember($cacheKey, $result);
        }

        return $result;
    }

    /**
     * This process now, the shared store once the caller's transaction has committed - see the
     * class docblock. DB::afterCommit() runs the callback at once when no transaction is open.
     */
    private static function remember(string $cacheKey, array $result): void
    {
        $expires = self::isTransient($result)
            ? now()->addMinutes(self::TRANSIENT_TTL_MINUTES)
            : now()->addDays(self::SETTLED_TTL_DAYS);

        Cache::store('array')->put($cacheKey, $result, $expires);

        DB::afterCommit(function () use ($cacheKey, $result, $expires) {
            try {
                Cache::put($cacheKey, $result, $expires);
            } catch (\Throwable $e) {
                // Outside the transaction, so nothing of the caller's is at stake: the cost of
                // losing this write is one repeated request.
                report($e);
            }
        });
    }

    public static function isResolved(array $result): bool
    {
        return ($result['status'] ?? null) === self::OK;
    }

    /** Google looked and found nothing: the answer will not change until the address does. */
    public static function isDefinitiveMiss(array $result): bool
    {
        return in_array($result['status'] ?? null, self::DEFINITIVE_MISSES, true);
    }

    public static function isTransient(array $result): bool
    {
        return ! self::isResolved($result) && ! self::isDefinitiveMiss($result);
    }

    /**
     * Case and spacing do not change what Google resolves, so they do not change the key either.
     */
    public static function cacheKey(string $address): string
    {
        $normalized = preg_replace('/\s+/u', ' ', $address) ?? $address;

        return 'geocode:v1:'.sha1(mb_strtolower(trim($normalized)));
    }

    private static function request(string $address, string $key): array
    {
        try {
            $response = Http::timeout(10)->get(self::ENDPOINT, [
                'address' => $address,
                'key' => $key,
            ]);
        } catch (\Exception $e) {
            // The message quotes the request URL, and the URL carries the key.
            Log::warning('Geocoding failed: '.preg_replace('/key=[^&\s]+/', 'key=[redacted]', $e->getMessage()));

            return self::result(self::UNAVAILABLE);
        }

        // Read the status off the body whatever the HTTP code: Google answers INVALID_REQUEST
        // with a 400, and that is a verdict on the address like any other.
        $status = $response->json('status');

        if (! is_string($status) || $status === '') {
            return self::result(self::UNAVAILABLE);
        }

        if ($status !== self::OK) {
            return self::result($status);
        }

        $first = $response->json('results.0');
        $lat = $first['geometry']['location']['lat'] ?? null;
        $lng = $first['geometry']['location']['lng'] ?? null;

        if (! is_numeric($lat) || ! is_numeric($lng)) {
            return self::result(self::UNAVAILABLE);
        }

        return [
            'status' => self::OK,
            'lat' => (float) $lat,
            'lng' => (float) $lng,
            'formatted_address' => $first['formatted_address'] ?? null,
            'place_id' => $first['place_id'] ?? null,
            'address_components' => $first['address_components'] ?? [],
        ];
    }

    private static function result(string $status): array
    {
        return [
            'status' => $status,
            'lat' => null,
            'lng' => null,
            'formatted_address' => null,
            'place_id' => null,
            'address_components' => [],
        ];
    }
}
