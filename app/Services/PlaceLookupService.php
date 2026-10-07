<?php

namespace App\Services;

use App\Models\PlaceLookup;
use App\Models\Role;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Every request the venue map makes to its address search, and the only writer of place_lookups.
 *
 * Why a second geocoder exists beside GeocodingService: that one is Google's, and Google's terms
 * allow its coordinates on a Google map only ("Geocoding API results displayed on a map must be
 * shown on a Google Map"). The venue map draws on OpenStreetMap streets, so its pins come from an
 * OpenStreetMap-based search (config services.map.geocoder_url, Nominatim's /search shape) and
 * the two sets of coordinates are never mixed: nothing here reads roles.geo_lat or geo_lon.
 *
 * The service is free, and its policy is the design:
 *
 *   - One request a second at the very most, and four a minute for a script on a timer. So
 *     nothing is ever asked inside a page view or a save. An address is REGISTERED as pending
 *     when a map needs it, and asked later by one runner at a time (run()), which both scheduler
 *     rails and the queued job reach through the same cache lock.
 *   - A client that repeats a query is blocked. So answers are kept by ADDRESS, for as long as
 *     the address is in use, and a miss is an answer too.
 *   - It has no guarantee. A failure in transit (a timeout, a 5xx, a 429 or a 403, a body that is
 *     not its JSON) says nothing about the address: nothing is recorded against it, the run ends,
 *     and nobody asks again for ten minutes. The same three outcomes GeocodingService has.
 *
 * What is sent is the venue's own words and nothing about a person: see addressFor().
 */
class PlaceLookupService
{
    public const PENDING = 'pending';

    public const FOUND = 'found';

    /** A small place with no street to find (a kibbutz, a moshav): pinned at its centre. */
    public const APPROXIMATE = 'approximate';

    public const MISSING = 'missing';

    /** What a run on a timer may ask: the service's limit for a script at regular intervals. */
    public const TIMER_BATCH = 4;

    /** What one queued run may ask when an owner has just switched a map on, a second apart. */
    public const BURST_BATCH = 25;

    public const RETRY_MISS_DAYS = 30;

    private const PAUSE_MINUTES = 10;

    /** Nominatim's place_rank for a street; a house is 30. Below it the answer is a place. */
    private const STREET_RANK = 26;

    /** A place this small is pinned at its centre and said to be approximate. */
    private const SMALL_PLACE_KM = 2.0;

    private const LOCK = 'place_lookups_lock';

    private const PAUSE_KEY = 'place_lookups.paused';

    /** When lookups began failing, for AdminAlertService. Cleared by the next answer. */
    public const FAILING_SINCE_KEY = 'place_lookups.failing_since';

    public static function enabled(): bool
    {
        return map_lookup() !== null;
    }

    /**
     * The address a venue is looked up by, or why it has none.
     *
     * The venue's OWN words: the raw address1, city, state and postal_code. Never the _en copies
     * (machine translations, which Role::fullAddress() swaps in while a translation is showing),
     * never Google's formatted_address, and not fullAddressRaw(), which appends the country code
     * as text. A calendar-sync placeholder stores whatever the calendar's location field held in
     * address1, and that is often a meeting link: a URL is never sent.
     *
     * Not asked at all: a venue with neither street nor town; a street or a bare name with no
     * town or postcode to place it in (it would pin somewhere confident and wrong); a venue whose
     * country is unknown and whose schedule's is too (the whole world is not searched). `why`
     * says which: no_street, no_country, or online for a meeting link with no place beside it.
     *
     * A town alone IS asked: where the place is small, its centre is the pin (APPROXIMATE).
     *
     * @return array{hash: ?string, address: ?string, country: ?string, street: bool, why: ?string}
     */
    public static function addressFor(Role $venue, ?Role $schedule = null): array
    {
        $raw = fn (string $column) => trim((string) ($venue->getAttributes()[$column] ?? ''));

        $street = $raw('address1');
        $link = $street !== '' && preg_match('~^\s*(?:[a-z][a-z0-9+.-]*://|www\.)~i', $street);
        if ($link) {
            $street = '';
        }

        $city = $raw('city');
        $state = $raw('state');
        $postal = $raw('postal_code');

        $none = fn (string $why) => ['hash' => null, 'address' => null, 'country' => null, 'street' => $street !== '', 'why' => $why];

        // A meeting link and nothing else: not a place at all, and VenueMap leaves it out.
        if ($link && $city === '' && $postal === '') {
            return $none('online');
        }

        if ($street === '' && $city === '') {
            return $none('no_street');
        }

        if ($city === '' && $postal === '') {
            return $none('no_street');
        }

        $country = strtolower($raw('country_code') ?: trim((string) ($schedule?->getAttributes()['country_code'] ?? '')));
        if (! preg_match('/^[a-z]{2}$/', $country)) {
            return $none('no_country');
        }

        $address = implode(', ', array_filter([$street, $city, $state, $postal], fn ($part) => $part !== ''));
        $address = mb_substr($address, 0, 500);

        return [
            'hash' => sha1(mb_strtolower($address).'|'.$country),
            'address' => $address,
            'country' => $country,
            'street' => $street !== '',
            'why' => null,
        ];
    }

    /**
     * Note the addresses a map needs, to be asked later. One statement, and a no-op for every
     * address already known, which after a map's first hour is all of them.
     *
     * @param  array<int, array{hash: string, address: string, country: string}>  $addresses
     */
    public static function register(array $addresses): void
    {
        if (! $addresses) {
            return;
        }

        $now = now();

        PlaceLookup::insertOrIgnore(array_map(fn (array $a) => [
            'address_hash' => $a['hash'],
            'address' => $a['address'],
            'country_code' => $a['country'],
            'status' => self::PENDING,
            'created_at' => $now,
            'updated_at' => $now,
        ], array_values($addresses)));
    }

    /** @return Collection<string, PlaceLookup> keyed by address_hash */
    public static function rows(array $hashes): Collection
    {
        $hashes = array_values(array_unique(array_filter($hashes)));

        return $hashes ? PlaceLookup::whereIn('address_hash', $hashes)->get()->keyBy('address_hash') : collect();
    }

    /** A miss old enough to be asked again goes back in the queue, when a map still needs it. */
    public static function requeueIfStale(PlaceLookup $row): bool
    {
        if ($row->status !== self::MISSING || ! $row->looked_up_at || $row->looked_up_at->gt(now()->subDays(self::RETRY_MISS_DAYS))) {
            return false;
        }

        $row->update(['status' => self::PENDING, 'try_after' => null]);

        return true;
    }

    /**
     * Ask about up to $max waiting addresses, oldest first, $gapMicroseconds apart.
     *
     * One runner at a time, whoever it is: the scheduler rail, the HTTP rail and the queued job
     * all come through here, and withoutOverlapping() only ever serialised a rail against itself.
     * The lock's TTL is a backstop for a killed run, sized above the longest one.
     *
     * @param  array<int, string>|null  $onlyHashes  limit the run to these addresses
     * @return array{asked: int, busy: bool, paused: bool, failed: bool}
     */
    public static function run(int $max, int $gapMicroseconds = 1000000, ?array $onlyHashes = null): array
    {
        $result = ['asked' => 0, 'busy' => false, 'paused' => false, 'failed' => false];

        if (! self::enabled() || $max < 1) {
            return $result;
        }

        if (Cache::has(self::PAUSE_KEY)) {
            return ['paused' => true] + $result;
        }

        $lock = Cache::lock(self::LOCK, 300);

        if (! $lock->get()) {
            return ['busy' => true] + $result;
        }

        try {
            $rows = PlaceLookup::query()
                ->where('status', self::PENDING)
                ->where(fn ($q) => $q->whereNull('try_after')->orWhere('try_after', '<=', now()))
                ->when($onlyHashes !== null, fn ($q) => $q->whereIn('address_hash', $onlyHashes ?: ['']))
                ->orderBy('created_at')
                ->orderBy('id')
                ->limit($max)
                ->get();

            foreach ($rows as $index => $row) {
                if ($index > 0 && $gapMicroseconds > 0) {
                    usleep($gapMicroseconds);
                }

                $result['asked']++;

                if (self::ask($row) === null) {
                    $result['failed'] = true;

                    break;
                }
            }
        } finally {
            $lock->release();
        }

        return $result;
    }

    /**
     * One request about one address. Returns the status stored, or null for a failure in transit,
     * which stores nothing about the address.
     */
    public static function ask(PlaceLookup $row): ?string
    {
        $lookup = map_lookup();

        if (! $lookup) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                // The service refuses a stock library User-Agent: this names the app and where it runs.
                'User-Agent' => config('app.name', 'Event Schedule').' venue map (+'.config('app.url').')',
            ])->timeout(8)->get($lookup['url'], array_filter([
                'format' => 'jsonv2',
                'limit' => 1,
                'q' => $row->address,
                'countrycodes' => $row->country_code,
            ]));

            $json = $response->successful() ? $response->json() : null;
        } catch (\Throwable $e) {
            $json = null;
        }

        // Not its JSON list: a timeout, a 5xx, a 429 or 403, an error page. A verdict on the
        // request, never on the address.
        if (! is_array($json) || ($json !== [] && ! array_is_list($json))) {
            $row->update(['attempts' => $row->attempts + 1, 'try_after' => now()->addMinutes(self::PAUSE_MINUTES)]);
            Cache::put(self::PAUSE_KEY, true, now()->addMinutes(self::PAUSE_MINUTES));
            Cache::add(self::FAILING_SINCE_KEY, now()->timestamp, now()->addDays(7));

            return null;
        }

        Cache::forget(self::FAILING_SINCE_KEY);

        [$status, $lat, $lon] = self::read($json[0] ?? null);

        $row->update([
            'status' => $status,
            'lat' => $lat,
            'lon' => $lon,
            'attempts' => $row->attempts + 1,
            'try_after' => null,
            'looked_up_at' => now(),
        ]);

        return $status;
    }

    /**
     * What an answer is worth. A street or better is a pin. A place is a pin only when it is small
     * enough that its centre is near everything in it, and then it is said to be approximate: a
     * town's centre would put a venue on the wrong side of town with a straight face.
     *
     * @return array{0: string, 1: ?float, 2: ?float}
     */
    private static function read(mixed $item): array
    {
        $miss = [self::MISSING, null, null];

        if (! is_array($item) || ! is_numeric($item['lat'] ?? null) || ! is_numeric($item['lon'] ?? null)) {
            return $miss;
        }

        $lat = (float) $item['lat'];
        $lon = (float) $item['lon'];

        if (($lat == 0.0 && $lon == 0.0) || abs($lat) > 90 || abs($lon) > 180) {
            return $miss;
        }

        if ((int) ($item['place_rank'] ?? 0) >= self::STREET_RANK) {
            return [self::FOUND, $lat, $lon];
        }

        // [south, north, west, east], as strings.
        $box = $item['boundingbox'] ?? null;

        if (is_array($box) && count($box) === 4 && count(array_filter($box, 'is_numeric')) === 4) {
            $tall = abs((float) $box[1] - (float) $box[0]) * 111.32;
            $wide = abs((float) $box[3] - (float) $box[2]) * 111.32 * cos(deg2rad($lat));

            if (max($tall, $wide) > 0 && max($tall, $wide) <= self::SMALL_PLACE_KM) {
                return [self::APPROXIMATE, $lat, $lon];
            }
        }

        return $miss;
    }
}
