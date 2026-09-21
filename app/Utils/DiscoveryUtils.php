<?php

namespace App\Utils;

use App\Models\Event;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

/**
 * Spreading the platform discovery lists across schedules.
 *
 * Every marketing discovery surface (the homepage poster wall and its rail, /browse, /search and
 * the /for-talent proof rail) orders by date and takes the first N rows, so a schedule that
 * publishes a cluster of same-day events owns the top of the list. The homepage shipped four
 * consecutive cards from one festival, showing the same flyer four times, because that schedule
 * had published a per-day event twice, once per language.
 *
 * This DEMOTES rather than drops: an event past its schedule's quota moves to the tail of the
 * same list instead of leaving it. The count a caller gets back is therefore what it is today or
 * better, which matters because three consumers shrink badly - the poster wall pads itself with
 * demo flyers below 25 real events, the rail drops its pinned scroll animation below 4, and
 * /for-talent hides its whole section below 4. That is the one real difference from
 * GraphicController::applyPerScheduleCap(), which drops, and is why the two are separate: that
 * one also keys on every linked talent and venue minus the schedule the graphic is for, which has
 * no analogue here. Neither should be bent into the other.
 *
 * The order out is "the kept events in date order, then the demoted ones in date order". It is
 * deliberately NOT re-sorted back into pure date order at the end: the homepage rail renders the
 * first 12 of the collection, so restoring the date order would float the demoted duplicates
 * straight back to the top and undo the whole thing on exactly the corpus that needed it. When
 * there are enough distinct schedules to fill the head, nothing is demoted into the visible range
 * and the output is in date order anyway.
 */
class DiscoveryUtils
{
    /**
     * How many events one schedule may contribute before the rest are demoted.
     *
     * Two rather than one so an active schedule can still show it runs more than one thing.
     * Do not lower this without re-running the homepage tests in tests/Feature/ImageVariantsTest.php:
     * several of them create two events on a single schedule and assume both render.
     */
    public const MAX_PER_SCHEDULE = 2;

    /**
     * Rows to consider so the quota has somewhere to backfill from.
     *
     * Flat rather than a multiple of the display limit because the extra rows are close to free:
     * all four queries order by a CASE expression, which the plain events.starts_at index cannot
     * satisfy, so MySQL already filters and filesorts the whole qualifying set before applying
     * any LIMIT. Widening it grows model hydration and the roles eager load, not the scan. The
     * flat number also stops the smallest surface (/search, 12) from getting the narrowest pool,
     * which is the one most likely to be asking about a single busy schedule.
     */
    public const CANDIDATE_POOL = 100;

    /**
     * The LIMIT a discovery query should ask for, given what it intends to display.
     */
    public static function poolLimit(int $limit): int
    {
        return max($limit, self::CANDIDATE_POOL);
    }

    /**
     * Reorder a discovery list so no schedule owns the top of it, then cut it to $limit.
     *
     * Walks in the order given, which is the order the SQL returned. An event joins the head
     * while its schedule is under quota and it does not look like something that schedule has
     * already shown; everything else goes to the tail. Head, then tail, then take.
     *
     * To fill L head slots at the default quota you need ceil(L / 2) distinct schedules in the
     * pool. Below that the tail backfills and the quota becomes a preference rather than a
     * guarantee, which is the price of never shrinking the list.
     */
    public static function spread(Collection $events, int $limit, int $perSchedule = self::MAX_PER_SCHEDULE): Collection
    {
        if ($perSchedule < 1 || $events->count() < 2) {
            return $events->take($limit)->values();
        }

        // Normally a no-op, since every discovery query eager-loads roles. It stops the walk
        // below from silently going N+1 if a caller ever forgets - though note it loads them
        // UNORDERED, where publicUpcomingEventsQuery() orders by event_role.id so the quota key
        // and the card label agree. A caller that leans on this fallback can therefore get a
        // different key than the marketing surfaces do.
        if ($events instanceof EloquentCollection) {
            $events->loadMissing('roles');
        }

        $counts = [];
        $seen = [];
        $head = [];
        $tail = [];

        foreach ($events as $event) {
            $key = self::scheduleKey($event);
            $prints = self::duplicateFingerprints($event, $key);

            $overQuota = ($counts[$key] ?? 0) >= $perSchedule;
            $looksDuplicate = false;

            foreach ($prints as $print) {
                if (isset($seen[$print])) {
                    $looksDuplicate = true;

                    break;
                }
            }

            if ($overQuota || $looksDuplicate) {
                $tail[] = $event;

                continue;
            }

            $counts[$key] = ($counts[$key] ?? 0) + 1;

            foreach ($prints as $print) {
                $seen[$print] = true;
            }

            $head[] = $event;
        }

        // take(0) rather than collect() so an Eloquent collection stays one. concat() rather
        // than merge(), which on an Eloquent collection would key on the model id.
        return $events->take(0)
            ->concat($head)
            ->concat($tail)
            ->take($limit)
            ->values();
    }

    /**
     * The schedule an event spends its quota against.
     *
     * getViewableRole() rather than creator_role_id, because it is the schedule whose name the
     * card actually prints, and a visitor counts repeats by the name they read. The fallbacks
     * are unreachable through the discovery queries, which all require an accepted pivot on a
     * listed schedule, but the last one matters anyway: without it a null key would put every
     * schedule-less event in ONE bucket and demote all but two of them.
     */
    private static function scheduleKey(Event $event): string
    {
        $role = $event->getViewableRole();

        if ($role) {
            return 'r'.$role->id;
        }

        if ($event->creator_role_id) {
            return 'r'.$event->creator_role_id;
        }

        return 'e'.$event->id;
    }

    /**
     * The ways one schedule's event can be the same thing listed twice.
     *
     * Both are scoped to the schedule, so two different schedules never take each other's slot.
     *
     * The image is the event's OWN flyer, not the resolved card image. getImageUrl() falls back to
     * a talent or venue schedule's profile photo, and the wall, /browse and /for-talent all admit
     * events on the strength of that photo - so fingerprinting the resolved image would give every
     * schedule without per-event flyers a quota of one rather than $perSchedule, silently and only
     * for them. Two cards wearing a venue's profile photo still carry their own name and date; two
     * cards wearing the same flyer are the same poster twice.
     *
     * The bare URL, not the 480 derivative the cards render: image_variants is per-event JSON, so
     * two events sharing one flyer can resolve to flyer_x_w480.webp and flyer_x.png, two strings
     * for one picture. The accessor returns '' rather than null when unset, so this tests
     * truthiness.
     *
     * The start time is here because the image alone does not catch the case that prompted this.
     * EventRepo's clone path copies a flyer to a FRESH random filename, and so does a second
     * upload of the same file, so "the same event published twice" (a translated duplicate, a
     * re-import) routinely carries two different image URLs and one identical starts_at. Exact
     * equality, not the calendar day: a venue running three different acts on one night keeps
     * all three.
     */
    private static function duplicateFingerprints(Event $event, string $key): array
    {
        $prints = [];

        if ($event->flyer_image_url) {
            $prints[] = $key.'|image|'.$event->flyer_image_url;
        }

        if ($event->starts_at) {
            $prints[] = $key.'|at|'.$event->starts_at;
        }

        return $prints;
    }
}
