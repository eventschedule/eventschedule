<?php

namespace App\Services\Feeds;

use App\Models\EventFeed;
use App\Models\Role;
use App\Models\User;
use App\Repos\EventRepo;
use App\Utils\GeminiUtils;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Which venue an event from a feed is at.
 *
 * EventRepo::saveEvent() looks a venue up across the whole install by name, and attaches
 * whatever it finds. For a person at a form that is a convenience. For a feed it is a way to
 * file a request on somebody else's venue for every one of a hundred events, with nobody there
 * to have meant it. So a feed's venue is decided here, and handed to the save as an id:
 *
 *  1. A venue schedule's events are at the venue.
 *  2. The venue this feed used for that place before, wherever it has got to since: renamed, or
 *     merged into another (its events were re-pointed, and the place follows them).
 *  3. A venue the schedule's own team runs, or an unclaimed one that is already on the
 *     schedule's events, by name. Never a venue somebody else runs, whatever it is called.
 *  4. A new, unclaimed venue, which the schedule's owner then follows so that it is offered to
 *     them on the event form. Only so many in one run: each is a subdomain and an address to
 *     look up. Past that the event waits for the next run.
 */
class FeedVenueResolver
{
    /** New venues one run may make for one feed. */
    public const PER_RUN = 5;

    private int $made = 0;

    /** @var array<int, Collection<int, Role>> */
    private array $candidates = [];

    public function __construct(private EventRepo $events) {}

    /**
     * @param  array  $row  A reader's row.
     * @param  array<string, int>  $known  The feed's memory of which venue stood for which
     *                                     place, by key. Updated in place; the caller keeps it.
     * @return array{venue: ?Role, key: string, deferred: bool} `deferred`: a venue would have
     *                                                          to be made and this run has
     *                                                          made its share.
     */
    public function resolve(EventFeed $feed, Role $role, array $row, array &$known): array
    {
        if ($role->isVenue()) {
            return ['venue' => $role, 'key' => '', 'deferred' => false];
        }

        $name = trim((string) ($row['venue_name'] ?? ''));
        $address = trim((string) ($row['event_address'] ?? ''));
        $city = trim((string) ($row['event_city'] ?? ''));

        if ($name === '' && $address === '') {
            return ['venue' => null, 'key' => '', 'deferred' => false];
        }

        // By name when the place has one: the same hall is written with and without its street.
        $key = $name !== ''
            ? 'n:'.GeminiUtils::normalizeForMatch($name).'|'.GeminiUtils::normalizeForMatch($city)
            : 'a:'.GeminiUtils::normalizeForMatch($address);

        if (isset($known[$key]) && ($venue = $this->knownVenue($feed, (int) $known[$key]))) {
            $known[$key] = $venue->id;

            return ['venue' => $venue, 'key' => $key, 'deferred' => false];
        }

        $venue = $this->among($role, $name, $address, $city);

        if (! $venue) {
            if ($this->made >= self::PER_RUN) {
                return ['venue' => null, 'key' => $key, 'deferred' => true];
            }

            $venue = $this->make($feed, $role, $row, $name, $address, $city);
        }

        $known[$key] = $venue->id;

        return ['venue' => $venue, 'key' => $key, 'deferred' => false];
    }

    /**
     * The venue the feed used before, or the one its events are at now when that one is gone:
     * a merge re-points the events and deletes the venue, and the place has not moved.
     */
    private function knownVenue(EventFeed $feed, int $id): ?Role
    {
        $venue = Role::where('type', 'venue')->find($id);

        if ($venue && ! $venue->is_deleted) {
            return $venue;
        }

        $eventIds = DB::table('event_feed_items')
            ->where('event_feed_id', $feed->id)
            ->whereNotNull('event_id')
            ->where('imported->venue_id', $id)
            ->limit(50)
            ->pluck('event_id');

        if ($eventIds->isEmpty()) {
            return null;
        }

        return Role::where('type', 'venue')
            ->where('is_deleted', false)
            ->whereIn('id', DB::table('event_role')->whereIn('event_id', $eventIds)->select('role_id'))
            ->orderBy('id')
            ->first();
    }

    private function among(Role $role, string $name, string $address, string $city): ?Role
    {
        $candidates = $this->candidates[$role->id] ??= $this->candidatesFor($role);

        if ($name !== '') {
            $wanted = GeminiUtils::normalizeForMatch($name);
            $wantedCity = GeminiUtils::normalizeForMatch($city);
            $named = $candidates->filter(fn (Role $venue) => $wanted !== '' && GeminiUtils::normalizeForMatch((string) $venue->name) === $wanted);

            // Two halls of one name in two towns are two halls. A candidate that names no town,
            // or a row that names none, matches on the name alone.
            return $named->first(fn (Role $venue) => $wantedCity !== '' && GeminiUtils::normalizeForMatch((string) $venue->city) === $wantedCity)
                ?? $named->first(fn (Role $venue) => $wantedCity === '' || GeminiUtils::normalizeForMatch((string) $venue->city) === '');
        }

        // An address and no name: the venue at that address, and when a building holds several
        // (a hall and a cellar bar), the one that is only an address itself.
        $wanted = GeminiUtils::normalizeForMatch($address);
        $there = $candidates->filter(fn (Role $venue) => $wanted !== '' && GeminiUtils::normalizeForMatch((string) $venue->address1) === $wanted);

        return $there->first(fn (Role $venue) => trim((string) $venue->name) === '') ?? $there->first();
    }

    /**
     * The venues a feed of this schedule may use: the ones its owner and admins run, and
     * unclaimed ones already on events the schedule made. Team venues first.
     *
     * @return Collection<int, Role>
     */
    private function candidatesFor(Role $role): Collection
    {
        $team = DB::table('role_user')->where('role_id', $role->id)->whereIn('level', ['owner', 'admin'])->pluck('user_id');

        $run = Role::where('type', 'venue')
            ->where('is_deleted', false)
            ->whereIn('id', DB::table('role_user')->whereIn('user_id', $team)->whereIn('level', ['owner', 'admin'])->select('role_id'))
            ->orderBy('id')
            ->get();

        $onItsEvents = Role::where('type', 'venue')
            ->where('is_deleted', false)
            ->whereIn('id', DB::table('event_role')
                ->join('events', 'events.id', '=', 'event_role.event_id')
                ->where('events.creator_role_id', $role->id)
                ->select('event_role.role_id'))
            ->orderBy('id')
            ->get()
            ->reject(fn (Role $venue) => $venue->isClaimed());

        return $run->concat($onItsEvents)->unique('id')->values();
    }

    private function make(EventFeed $feed, Role $role, array $row, string $name, string $address, string $city): Role
    {
        $venue = $this->events->makeVenue([
            'name' => $name !== '' ? mb_substr($name, 0, 255) : null,
            'address1' => $address !== '' ? mb_substr($address, 0, 255) : null,
            'city' => $city !== '' ? mb_substr($city, 0, 255) : null,
            'state' => ($row['event_state'] ?? '') ?: null,
            'postal_code' => ($row['event_postal_code'] ?? '') ?: null,
            'country_code' => ($row['event_country_code'] ?? '') ?: null,
        ], $role, $feed->source_timezone);

        $this->made++;
        ($this->candidates[$role->id] ??= collect())->push($venue);

        // As a save does for a venue it makes: the owner follows it, so the event form offers it.
        $owner = $role->user_id ? User::find($role->user_id) : null;
        if ($owner && ! $owner->roles()->where('roles.id', $venue->id)->exists()) {
            $owner->roles()->attach($venue->id, ['level' => 'follower', 'created_at' => now()]);
        }

        return $venue;
    }
}
