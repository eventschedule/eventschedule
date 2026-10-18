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

    /** Whether this venue is a placeholder an import made out of a meeting, and nothing else. */
    public static function isMeetingStub(Role $venue): bool
    {
        if ($venue->type !== 'venue' || $venue->is_deleted || $venue->email || $venue->hasRealOwner()) {
            return false;
        }

        $address = trim((string) $venue->address1);
        if ($address === '') {
            return false;
        }

        return CalendarPrivacy::venueFromLocation($address) === null;
    }

    /**
     * The meeting placeholders attached to an event as its venue. A calendar sync calls this as it
     * updates an event, so the next read takes the link off an event that is already public.
     */
    public static function detachFrom(Event $event): int
    {
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

        DB::table('event_role')->where('role_id', $venue->id)->delete();

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
