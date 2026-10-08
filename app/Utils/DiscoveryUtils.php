<?php

namespace App\Utils;

use App\Models\Event;
use Illuminate\Database\Eloquent\Builder;
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
 * There are two steps and both are needed. candidates() decides WHICH rows are looked at: the
 * soonest, but only a few from any one schedule. spread() decides the ORDER they are shown in.
 * Until 2026-10 there was only the second, run over the 100 soonest rows, and one schedule that
 * had synced a work calendar (74 upcoming meetings, every one wearing the same profile photo) was
 * most of those 100. Thirteen schedules reached the walk, eleven of them with one event each, so
 * the other ten places on the homepage wall went back to the two that had more: nine of its 25
 * posters were that one photo. A quota cannot share out a list that one schedule already owns.
 *
 * spread() DEMOTES rather than drops: an event past its schedule's quota moves down the same
 * list instead of leaving it, and a pool that the per-schedule limit leaves short is topped up
 * with the rows it passed over. The count a caller gets back is therefore what it would be with
 * no spreading at all, which matters to /for-talent, which hides its whole section below 4, and
 * to /search, where one schedule may be the only answer. That is the one real difference from
 * GraphicController::applyPerScheduleCap(), which drops, and is why the two are separate: that
 * one also keys on every linked talent and venue minus the schedule the graphic is for, which has
 * no analogue here. Neither should be bent into the other.
 *
 * The homepage and /browse do NOT spread. They show one event for each schedule and stop there
 * (onePerSchedule() and App\Utils\BrowseWall::pick(), on the same scheduleKey()), so a list
 * shorter than its places is their honest state: the homepage wall fills the places left with
 * its demo flyers. Until 2026-10 the homepage took one from each schedule and then went round
 * again, and with sixteen schedules to fill 25 places it showed one of them five times. Both
 * still draw their events through candidates(), which is what stops one schedule owning the
 * pool.
 *
 * The order out of spread() is by turns: every schedule's first events in date order, then every
 * schedule's next ones, and so on, with anything that repeats a card already shown after all of
 * those. It is deliberately NOT re-sorted back into pure date order at the end: a caller renders
 * the front of the collection, so restoring the date order would float the demoted duplicates
 * straight back to the top and undo the whole thing on exactly the corpus that needed it. When
 * there are enough distinct schedules to fill the list in the first turn, nothing is demoted into
 * the visible range and the output is in date order anyway.
 */
class DiscoveryUtils
{
    /**
     * How many events one schedule may contribute to a turn before the rest wait for the next.
     *
     * Two rather than one so an active schedule can still show it runs more than one thing. The
     * homepage and /browse do not take turns at all: one event for each schedule, and no more
     * (onePerSchedule(), BrowseWall::pick()).
     */
    public const MAX_PER_SCHEDULE = 2;

    /**
     * Rows to consider so the quota has somewhere to backfill from.
     *
     * Flat rather than a multiple of the display limit, so the smallest surface (/search, 12)
     * does not get the narrowest pool: it is the one most likely to be asking about a single busy
     * schedule.
     */
    public const CANDIDATE_POOL = 100;

    /**
     * How many of one schedule's events may enter the pool, as a multiple of its quota.
     *
     * More than the quota itself, because a schedule's soonest rows can be each other's
     * duplicates (a translated copy of every day) and it then needs something different to put
     * forward. Few enough that the pool always holds the schedules a full list needs: a pool of
     * 100 at three per schedule is at least 34 schedules for the homepage's 25 places and for the
     * 24 on /browse's wall, which each take one event from a schedule, and at six it is at least
     * 17 for the 12 places /search fills.
     */
    public const POOL_DEPTH = 3;

    /**
     * How many qualifying rows candidates() reads the keys of.
     *
     * Two integers a row, so this bounds a transfer, not the work: every discovery query orders
     * by a CASE expression, which the plain events.starts_at index cannot satisfy, so MySQL
     * filters and sorts the whole qualifying set whatever the LIMIT says.
     */
    public const CANDIDATE_SCAN = 5000;

    /**
     * How many rows a discovery pool holds, given what the surface intends to display.
     */
    public static function poolLimit(int $limit): int
    {
        return max($limit, self::CANDIDATE_POOL);
    }

    /**
     * The events a discovery list is chosen from: the soonest, but only a few per schedule.
     *
     * Hand it the query with its filters and its ORDER BY and no LIMIT. It reads the id and the
     * owning schedule of the qualifying rows in that order, keeps the first few of each schedule
     * until the pool is full, and loads those. A plain `LIMIT 100` is what this replaced: the
     * soonest hundred rows are whoever publishes most, so the schedules with one event next month
     * never reached spread() at all.
     *
     * The schedule here is the one that OWNS the event (creator_role_id, which every way of
     * creating an event sets), not the one spread() counts against, which is the schedule the
     * card credits and takes the loaded roles to work out. The two differ for a curator listing
     * other people's events, and the owner is the right key for this half: it is the one whose
     * volume fills a pool. An old row with no owner is counted against the person who made it.
     *
     * A pool the limit leaves short is topped up with the rows it passed over, soonest first, so
     * a search that only one schedule answers still gets every one of its events.
     */
    public static function candidates(Builder $ordered, int $limit, int $perSchedule = self::MAX_PER_SCHEDULE): EloquentCollection
    {
        $size = self::poolLimit($limit);

        if ($perSchedule < 1) {
            return (clone $ordered)->limit($size)->get();
        }

        $each = $perSchedule * self::POOL_DEPTH;

        $rows = (clone $ordered)
            ->toBase()
            ->limit(self::CANDIDATE_SCAN)
            ->get(['events.id', 'events.creator_role_id', 'events.user_id']);

        $taken = [];
        $picked = [];
        $passedOver = [];

        foreach ($rows as $row) {
            $key = match (true) {
                (bool) $row->creator_role_id => 'r'.$row->creator_role_id,
                (bool) $row->user_id => 'u'.$row->user_id,
                default => 'e'.$row->id,
            };

            if (($taken[$key] ?? 0) < $each) {
                $taken[$key] = ($taken[$key] ?? 0) + 1;
                $picked[] = $row->id;

                if (count($picked) >= $size) {
                    break;
                }
            } elseif (count($passedOver) < $size) {
                $passedOver[] = $row->id;
            }
        }

        $ids = array_slice(array_merge($picked, $passedOver), 0, $size);

        if (! $ids) {
            return $ordered->getModel()->newCollection();
        }

        // The same query again, narrowed to the chosen rows: it carries the eager loads the
        // cards need and its ORDER BY puts the pool back in the order spread() walks.
        return (clone $ordered)->whereIn('events.id', $ids)->get();
    }

    /**
     * Reorder a discovery list so no schedule owns the top of it, then cut it to $limit.
     *
     * Walks in the order given, which is the order the SQL returned, as many times as it takes.
     * On each turn an event is placed while its schedule is under quota for THAT turn and it
     * does not look like something that schedule has already shown; the rest wait for the next
     * turn. So the list opens with up to $perSchedule from every schedule, and what follows is
     * shared out the same way instead of going to whoever has the most.
     *
     * To fill L places in the first turn at the default quota you need ceil(L / 2) distinct
     * schedules in the pool. Below that the later turns backfill and the quota becomes a
     * preference rather than a guarantee, which is the price of never shrinking the list.
     *
     * An event that repeats a card already placed (see duplicateFingerprints()) waits until a
     * turn places nothing, which is when everything still waiting is such a repeat. The record of
     * what has been shown is cleared then and the repeats are shared out by the same turns, so a
     * day published twice appears twice only after every other day has appeared once.
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

        // Worked out once, because a turn can pass over the same event many times.
        $waiting = [];

        foreach ($events as $event) {
            $key = self::scheduleKey($event);
            $waiting[] = [$event, $key, self::duplicateFingerprints($event, $key)];
        }

        $placed = [];
        $seen = [];

        while ($waiting && count($placed) < $limit) {
            $counts = [];
            $held = [];
            $before = count($placed);

            foreach ($waiting as $entry) {
                [$event, $key, $prints] = $entry;

                $overQuota = ($counts[$key] ?? 0) >= $perSchedule;
                $looksDuplicate = false;

                foreach ($prints as $print) {
                    if (isset($seen[$print])) {
                        $looksDuplicate = true;

                        break;
                    }
                }

                if ($overQuota || $looksDuplicate) {
                    $held[] = $entry;

                    continue;
                }

                $counts[$key] = ($counts[$key] ?? 0) + 1;

                foreach ($prints as $print) {
                    $seen[$print] = true;
                }

                $placed[] = $event;
            }

            // Nothing placed: every event still waiting repeats one already shown. Forget what
            // was shown, and the next turn places the first of them at least.
            if (count($placed) === $before) {
                $seen = [];
            }

            $waiting = $held;
        }

        // take(0) rather than collect() so an Eloquent collection stays one. concat() rather
        // than merge(), which on an Eloquent collection would key on the model id.
        return $events->take(0)
            ->concat($placed)
            ->take($limit)
            ->values();
    }

    /**
     * One event for each schedule: the first it has in the order given, cut to $limit.
     *
     * What the homepage shows, on its wall and in its rail. Unlike spread() this DROPS: a
     * schedule's second event is left off the list, not moved down it, so the list is as long
     * as the number of schedules in the pool and never longer. The caller has to be able to
     * stand a short list, which the homepage can (see MarketingController::discoverWallEvents()).
     *
     * No fingerprints here. They exist to tell a schedule's repeats from its other events, and
     * with one event a schedule there is nothing to tell apart.
     */
    public static function onePerSchedule(Collection $events, int $limit): Collection
    {
        // As in spread(): a no-op for the discovery queries, which eager-load roles in the
        // order scheduleKey() reads them.
        if ($events instanceof EloquentCollection) {
            $events->loadMissing('roles');
        }

        $shown = [];
        $placed = [];

        foreach ($events as $event) {
            if (count($placed) >= $limit) {
                break;
            }

            $key = self::scheduleKey($event);

            if (isset($shown[$key])) {
                continue;
            }

            $shown[$key] = true;
            $placed[] = $event;
        }

        // take(0) and concat(), as in spread(): an Eloquent collection stays one, in this order.
        return $events->take(0)->concat($placed)->values();
    }

    /**
     * The schedule an event is counted against, on every discovery surface.
     *
     * getViewableRole() rather than creator_role_id, because it is the schedule whose name the
     * card actually prints, and a visitor counts repeats by the name they read. The fallbacks
     * are unreachable through the discovery queries, which all require an accepted pivot on a
     * listed schedule, but the last one matters anyway: without it a null key would put every
     * schedule-less event in ONE bucket, and all but one or two of them would be demoted or
     * dropped.
     */
    public static function scheduleKey(Event $event): string
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
