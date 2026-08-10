<?php

namespace App\Utils;

use App\Models\DismissedVenueMergeSuggestion;
use App\Models\Role;
use Illuminate\Support\Collection;

/**
 * The one definition of "these venue schedules look like the same place".
 *
 * Imports, calendar sync and hand-entry all invent venues, so the same room routinely ends up
 * as several rows. Three surfaces have to agree on how those are grouped: the duplicate banner
 * on the Following page, the merge tool that fixes them, and the event form's venue picker
 * which collapses them. If the picker hid a venue the merge tool did not offer, the user would
 * be left with one they could neither choose nor clean up.
 *
 * Deliberately NOT the matcher used when resolving an incoming venue name onto an existing
 * record - EventRepo::saveEvent(), ConvertsLocationToVenue and GeminiUtils each do that in SQL,
 * with their own ranking, and folding them in here would be an unrelated change.
 */
class VenueUtils
{
    /**
     * Group venues that share a normalized name + country. Venues with an unusable name are
     * dropped rather than lumped into one meaningless group.
     *
     * @return array<string, array<int, Role>> keyed by the grouping key, insertion-ordered
     */
    public static function groupDuplicates(iterable $venues): array
    {
        $grouped = [];

        foreach ($venues as $venue) {
            $key = self::groupKey($venue);

            if ($key === null) {
                continue;
            }

            $grouped[$key][] = $venue;
        }

        return $grouped;
    }

    /** Same as groupDuplicates() but only the groups with more than one member. */
    public static function duplicateGroups(iterable $venues): array
    {
        return array_values(array_filter(
            self::groupDuplicates($venues),
            fn ($group) => count($group) > 1
        ));
    }

    /**
     * The grouping key for one venue, or null when its name normalizes away to nothing.
     *
     * Keyed on the first comma segment of the name, which is the same bridge the sync-time
     * matcher uses (ConvertsLocationToVenue), and for the same reason: calendar sync names a
     * venue after the WHOLE inbound location string, so the duplicate it creates is
     * "The Anchor, Haifa" beside a real venue called "The Anchor". Keyed on the full name those
     * two never met, which meant the merge tool could not group the very duplicates the sync it
     * ships beside was creating - and two variants of one location string could not group with
     * each other either.
     *
     * The city is deliberately NOT in the key. A sync stub has none (nothing on that path sets
     * one), so including it guaranteed a mismatch against any real venue that had one. Two
     * genuinely different venues that share a name in different cities are kept apart by
     * isSafeToCollapse() instead: they are still offered on the merge page, behind a
     * confirmation, but never collapsed out of the picker on their own.
     */
    public static function groupKey(Role $venue): ?string
    {
        $normName = GeminiUtils::normalizeForMatch(self::leadingSegment($venue->name));

        if ($normName === '') {
            return null;
        }

        $country = $venue->country_code ? strtolower($venue->country_code) : '';

        return $normName.'|'.$country;
    }

    /**
     * The part of a venue name before the first comma, or the whole name when that would leave
     * nothing usable ("Haifa, Israel" style names, and anything starting with a comma).
     */
    private static function leadingSegment(?string $name): string
    {
        $name = (string) $name;
        $first = trim(explode(',', $name)[0]);

        return GeminiUtils::normalizeForMatch($first) !== '' ? $first : $name;
    }

    /**
     * The member of a duplicate group that should survive: a claimed venue over an unclaimed
     * one (it has a real operator behind it), a live one over a soft-deleted one, then the one
     * carrying the most events, then the oldest.
     *
     * Reads $venue->future_event_count only when the caller decorated the group with it - the
     * merge screens do, the event picker does not, so there every venue scores 0 and the tie
     * falls through to lowest id. That only ever decides between venues already tied on the two
     * criteria that matter, so the surfaces still agree on which venue is the real one.
     */
    public static function pickBest(array $group): Role
    {
        return collect($group)->sort(function (Role $a, Role $b) {
            $aClaimed = $a->isClaimed() ? 1 : 0;
            $bClaimed = $b->isClaimed() ? 1 : 0;
            if ($aClaimed !== $bClaimed) {
                return $bClaimed - $aClaimed;
            }

            $aLive = $a->is_deleted ? 0 : 1;
            $bLive = $b->is_deleted ? 0 : 1;
            if ($aLive !== $bLive) {
                return $bLive - $aLive;
            }

            $aCount = (int) ($a->future_event_count ?? 0);
            $bCount = (int) ($b->future_event_count ?? 0);
            if ($aCount !== $bCount) {
                return $bCount - $aCount;
            }

            return $a->id - $b->id;
        })->values()->first();
    }

    /**
     * Collapse duplicate groups down to one venue each, but only where doing so is safe.
     *
     * Hiding a venue from a picker is close to irreversible for the user: /search-roles matches
     * on exact email or phone only, so a nameless-but-for-its-name stub cannot be searched back
     * up. So a group only collapses when at most one member is claimed and every loser is an
     * empty shell - no email, no phone, and no address that differs from the survivor's. Two
     * real venues that happen to share a name and city (a second room, a franchise branch) both
     * stay on the list.
     *
     * $dismissedHashes are the groups the user already told us are NOT duplicates, via "Not
     * duplicates" on the merge page. Those must stay expanded: the merge page has stopped
     * offering them, so collapsing here would hide a venue the user explicitly kept and give
     * them no way to get it back.
     *
     * The same goes for a group whose survivor the user cannot merge into - somebody else's
     * claimed venue that they merely follow. The merge page skips those (no legal target), so
     * collapsing here would again hide a row with no way to get it back.
     *
     * @param  array<int, string>  $dismissedHashes  DismissedVenueMergeSuggestion::hashForVenueIds() values
     * @return array{0: Collection<int, Role>, 1: int} the kept venues and how many groups collapsed
     */
    public static function collapseDuplicates(iterable $venues, array $dismissedHashes = []): array
    {
        // Materialise: $venues is walked twice below, and an `iterable` may be a one-shot Generator.
        $venues = collect($venues);

        $kept = collect();
        $collapsed = 0;

        foreach (self::groupDuplicates($venues) as $group) {
            if (count($group) < 2 || self::isDismissed($group, $dismissedHashes) || ! self::isSafeToCollapse($group)) {
                $kept = $kept->concat($group);

                continue;
            }

            $kept->push(self::pickBest($group));
            $collapsed++;
        }

        // Venues whose name normalizes to nothing never made it into a group.
        foreach ($venues as $venue) {
            if (self::groupKey($venue) === null) {
                $kept->push($venue);
            }
        }

        return [$kept->values(), $collapsed];
    }

    /** @param  array<int, string>  $dismissedHashes */
    private static function isDismissed(array $group, array $dismissedHashes): bool
    {
        if (empty($dismissedHashes)) {
            return false;
        }

        $hash = DismissedVenueMergeSuggestion::hashForVenueIds(array_map(fn (Role $v) => $v->id, $group));

        return in_array($hash, $dismissedHashes, true);
    }

    private static function isSafeToCollapse(array $group): bool
    {
        $claimed = collect($group)->filter(fn (Role $v) => $v->isClaimed())->count();

        if ($claimed > 1) {
            return false;
        }

        $survivor = self::pickBest($group);

        // Mirrors Role::isEditableBy, and mirrors the merge page's own $editable predicate:
        // owner/admin always, follower only on an UNCLAIMED role. If the user cannot merge into
        // the survivor the merge page skips the whole group, so collapsing here would hide a row
        // that no surface offers a way back to.
        //
        // Deliberately not scoped to isClaimed(). It used to be, which left a hole for an
        // UNCLAIMED survivor the user is only a `viewer` on: viewer is neither owner/admin nor
        // follower, so the merge page dropped the group while the picker collapsed it anyway.
        $level = $survivor->pivot?->level;

        $canMergeInto = in_array($level, ['owner', 'admin'], true)
            || (! $survivor->isClaimed() && $level === 'follower');

        if (! $canMergeInto) {
            return false;
        }

        $survivorAddress = GeminiUtils::normalizeForMatch($survivor->address1);
        $survivorCity = GeminiUtils::normalizeForMatch($survivor->city);

        foreach ($group as $venue) {
            if ($venue->id === $survivor->id) {
                continue;
            }

            if ($venue->email || $venue->phone) {
                return false;
            }

            // The city left the grouping key so that a sync stub (which never has one) can group
            // with the real venue at all. It earns its keep here instead: two venues that BOTH
            // name a city, and name different ones, are a franchise or a second room rather than
            // a duplicate. They stay on the merge page, where the user confirms; they are never
            // silently collapsed out of the picker.
            $city = GeminiUtils::normalizeForMatch($venue->city);

            if ($city !== '' && $survivorCity !== '' && $city !== $survivorCity) {
                return false;
            }

            $address = GeminiUtils::normalizeForMatch($venue->address1);

            // Any address the survivor does not also carry is information that would vanish from
            // the list, since the option label falls back to address1 when a venue has no name.
            if ($address !== '' && $address !== $survivorAddress) {
                return false;
            }
        }

        return true;
    }
}
