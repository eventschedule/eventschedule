<?php

namespace App\Services;

use App\Exceptions\BillingCancellationException;
use App\Models\Event;
use App\Models\Role;
use App\Utils\CalendarPrivacy;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Venue pages a calendar import made out of a meeting link.
 *
 * A calendar sync turns a location into a venue (ConvertsLocationToVenue), and until 2026-10 it
 * did that for a Zoom link with the meeting's password in it, a Teams dial-in with its code, or
 * just "Microsoft Teams Meeting": a public venue page, named after the link, its subdomain made
 * from it, geocoded by Google to the middle of the country. It no longer makes them
 * (CalendarPrivacy::venueFromLocation()); this takes down the ones that exist.
 *
 * Only a placeholder nobody runs (no email, no real owner, Role::hasRealOwner()) whose address is
 * no place at all. A venue somebody claimed, or one with a real address beside a link, is left
 * alone. The link goes from every column that held it (name, address, subdomain, the geocode and
 * the audit line) before the schedule is marked deleted, because a deleted schedule keeps its
 * subdomain inside the released one, and Event::venue() still finds a deleted venue an event is
 * attached to, so it is detached from every event first.
 */
class MeetingLinkVenues
{
    /** How far the sweep walks the venue ids in one run. */
    public const WINDOW = 2000;

    private const CURSOR = 'meeting_link_venues.cursor.v1';

    /**
     * Whether this venue is a placeholder a calendar import made out of a meeting, and nothing
     * else. All of it is asked, because the event form and the AI import make venues of the same
     * shape (no email, nobody running it) out of whatever a person typed, and a venue whose
     * "address" is its own website is a real place:
     *
     *  - what ConvertsLocationToVenue writes: the location as both name and address, and the
     *    importing account as a follower of it, nobody as its owner;
     *  - a meeting in that text: a join link (with its password or without), a code, or a
     *    provider's name on its own. A website is not a meeting;
     *  - and only imported events on it: a venue a person also chose for an event of their own
     *    is theirs, whatever it is called.
     */
    public static function isMeetingStub(Role $venue): bool
    {
        if ($venue->type !== 'venue' || $venue->is_deleted || $venue->email || ! $venue->user_id || $venue->hasRealOwner()) {
            return false;
        }

        $address = trim((string) $venue->address1);
        if ($address === '' || trim((string) $venue->name) !== $address) {
            return false;
        }

        $findings = CalendarPrivacy::findings(null, null, $address);
        $meeting = ! empty($findings[CalendarPrivacy::LINK_PASSWORD]) || ! empty($findings[CalendarPrivacy::ACCESS_CODES])
            || ! empty($findings[CalendarPrivacy::MEETING_LINK])
            || CalendarPrivacy::isProviderName($address);
        if (! $meeting || CalendarPrivacy::venueFromLocation($address) !== null) {
            return false;
        }

        $follows = DB::table('role_user')
            ->where('role_id', $venue->id)
            ->where('user_id', $venue->user_id)
            ->where('level', 'follower')
            ->exists();
        if (! $follows) {
            return false;
        }

        // An event made by a person: a source that is not an import, or none at all on an event
        // that is not one an older sync read in (made before import_source was recorded, and
        // still mapped to a calendar entry, as CalendarExposureWatch::candidates() reads it).
        return ! DB::table('event_role')
            ->join('events', 'events.id', '=', 'event_role.event_id')
            ->where('event_role.role_id', $venue->id)
            ->where(fn ($q) => $q
                ->whereNotIn('events.import_source', CalendarExposureWatch::SOURCES)
                ->orWhere(fn ($none) => $none
                    ->whereNull('events.import_source')
                    ->where(fn ($handMade) => $handMade
                        ->where('events.created_at', '>=', CalendarExposureWatch::UNLABELLED_BEFORE)
                        ->orWhere(fn ($unmapped) => $unmapped
                            ->whereNotExists(fn ($m) => $m->selectRaw('1')->from('calendar_syncs')->whereColumn('calendar_syncs.event_id', 'events.id'))
                            ->whereNotExists(fn ($m) => $m->selectRaw('1')->from('microsoft_calendar_syncs')->whereColumn('microsoft_calendar_syncs.event_id', 'events.id'))
                            ->whereNotExists(fn ($m) => $m->selectRaw('1')->from('event_role as caldav_map')->whereColumn('caldav_map.event_id', 'events.id')->whereNotNull('caldav_map.caldav_event_uid'))))))
            ->exists();
    }

    /**
     * The meeting placeholders attached to an event as its venue. A calendar sync calls this as it
     * updates an event, so the next read takes the link off an event that is already public.
     */
    public static function detachFrom(Event $event): int
    {
        // Only an event the sync read in: one made here and pushed out (a two-way sync reads it
        // back) has the venue its owner chose.
        if (! $event->cameFromCalendarSync()) {
            return 0;
        }

        $detached = 0;

        foreach ($event->roles()->where('roles.type', 'venue')->get() as $venue) {
            if (self::isMeetingStub($venue)) {
                $event->roles()->detach($venue->id);
                $detached++;
            }
        }

        if ($detached) {
            $event->unsetRelation('roles');
        }

        return $detached;
    }

    /**
     * Takes one down: detached from every event, every trace of the link scrubbed, then marked
     * deleted. False when it is not one, or Stripe would not let go of it (it never has a plan,
     * but markDeleted() asks).
     */
    public static function takeDown(Role $venue, ?int $actorUserId = null): bool
    {
        if (! self::isMeetingStub($venue)) {
            return false;
        }

        // Which events it was on, so a mistake can be put back: the audit line keeps their ids
        // (and never the text, which is the meeting's password).
        $eventIds = DB::table('event_role')->where('role_id', $venue->id)->pluck('event_id')->all();
        DB::table('event_role')->where('role_id', $venue->id)->delete();
        AuditService::log(AuditService::ADMIN_CALENDAR_PRIVACY_VENUE_REMOVED, $actorUserId, 'App\\Models\\Role', $venue->id, ['event_ids' => $eventIds], null, 'Detached a venue made from a meeting link');

        // Targeted writes, not save(): a save runs the geocode hook on the address being removed.
        $venue->writeOperationalColumns([
            'name' => __('messages.venue'),
            'name_normalized' => null,
            'address1' => null,
            'address1_normalized' => null,
            'subdomain' => 'venue-removed-'.$venue->id,
            'subdomain_before_delete' => null,
            'formatted_address' => null,
            'google_place_id' => null,
            'geo_address' => null,
            'geo_lat' => null,
            'geo_lon' => null,
        ]);

        try {
            app(ScheduleDeletionService::class)->markDeleted($venue, $actorUserId, AuditService::ADMIN_CALENDAR_PRIVACY_VENUE_REMOVED);
        } catch (BillingCancellationException $e) {
            Log::warning('A meeting-link venue could not be marked deleted', ['role_id' => $venue->id]);

            return false;
        }

        return true;
    }

    /**
     * Walks the venue ids from where the last run stopped, a window at a time, and takes down the
     * placeholders in it. The cursor goes back to the start once it passes the last venue, so a
     * placeholder made by an older release, or by a path that still makes one, is found again.
     * Returns how many it took down.
     */
    public static function sweep(): int
    {
        // In the cache, not a Setting: saving one throws away every request's cached settings.
        $from = (int) Cache::get(self::CURSOR, 0);
        $to = $from + self::WINDOW;

        $venues = Role::query()
            ->where('type', 'venue')
            ->where('is_deleted', false)
            ->whereNull('email')
            ->where('id', '>', $from)
            ->where('id', '<=', $to)
            ->whereNotNull('address1')
            // A cheap first cut: something in the address that could be a link, a code or a
            // provider's name. CalendarPrivacy decides.
            ->where(function ($q) {
                foreach (['%://%', '%www.%', '%/%', '%#%', '%zoom%', '%teams%', '%meet%', '%webex%', '%skype%', '%gotomeeting%', '%bluejeans%', '%chime%', '%code%', '%pin%', '%passcode%', '%password%'] as $like) {
                    $q->orWhere('address1', 'like', $like);
                }
            })
            ->get();

        $removed = 0;
        foreach ($venues as $venue) {
            if (self::takeDown($venue)) {
                $removed++;
            }
        }

        $last = (int) Role::max('id');
        Cache::forever(self::CURSOR, $to >= $last ? 0 : $to);

        return $removed;
    }
}
