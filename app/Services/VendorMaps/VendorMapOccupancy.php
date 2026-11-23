<?php

namespace App\Services\VendorMaps;

use App\Models\VendorMap;
use App\Models\VendorMapOccupant;
use App\Services\BlockedAccounts;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Who is on which spot on a given date. The ONE reader of vendor_map_occupants.
 *
 * A vendor holds a spot for the season (event_date '*') or for one date. On a date, a spot is:
 *  - taken by a dated, approved, present row (a vendor for that day, or one moved there);
 *  - otherwise by its season vendor, unless that vendor has an approved ABSENT row for the date
 *    ("Not coming this Friday"), in which case the spot is shown greyed under their name;
 *  - otherwise free.
 * A date the map's event does not fall on has nobody: the event decides its days
 * (Event::matchesDate(), in the schedule's timezone), so a date orphaned by a change to the
 * series simply matches nothing.
 *
 * Deleted schedules and blocked accounts' schedules are left out, as everywhere public.
 */
class VendorMapOccupancy
{
    /**
     * @return array<int, array{state: string, occupant: ?VendorMapOccupant, dated: bool}> keyed by spot id;
     *                                                                                     state is present|absent|free
     */
    public static function on(VendorMap $map, string $date): array
    {
        $spotIds = $map->spots()->pluck('id')->all();
        $out = [];
        foreach ($spotIds as $id) {
            $out[$id] = ['state' => 'free', 'occupant' => null, 'dated' => false];
        }

        if (! self::isOpenOn($map, $date)) {
            return $out;
        }

        $rows = self::approvedRows($map, [VendorMapOccupant::SEASON, $date]);

        $season = $rows->where('event_date', VendorMapOccupant::SEASON)->where('kind', 'present')->keyBy('spot_id');
        $datedPresent = $rows->where('event_date', $date)->where('kind', 'present')->keyBy('spot_id');
        $absent = $rows->where('event_date', $date)->where('kind', 'absent');

        foreach ($spotIds as $id) {
            if ($dated = $datedPresent->get($id)) {
                $out[$id] = ['state' => 'present', 'occupant' => $dated, 'dated' => true];

                continue;
            }

            if ($row = $season->get($id)) {
                // The same vendor: by their page when they have one (the season row also keeps the
                // name they typed, the absent row does not), by the name for a name-only row.
                $isAbsent = $absent->contains(fn ($a) => (int) $a->spot_id === (int) $id
                    && ($row->vendor_role_id
                        ? (int) $a->vendor_role_id === (int) $row->vendor_role_id
                        : (! $a->vendor_role_id && (string) $a->placeholder_name === (string) $row->placeholder_name)));

                // A season vendor moved to another spot for this date is absent here too: the
                // move wrote an absent row on this spot and a present row on the other.
                $out[$id] = ['state' => $isAbsent ? 'absent' : 'present', 'occupant' => $row, 'dated' => false];
            }
        }

        return $out;
    }

    /** The event the map serves falls on this date. */
    public static function isOpenOn(VendorMap $map, string $date): bool
    {
        $event = $map->event;

        if (! $event || $event->is_cancelled || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }

        // One day of an event that is not weekly (a three-day expo) called off on its own.
        if (array_key_exists($date, (array) ($map->cancelled_days ?? [])) && ! $event->days_of_week) {
            return false;
        }

        return (bool) $event->matchesDate($date, $event->scheduleTimezone());
    }

    /** Today in the schedule's timezone, or the next date the market is on. Null when none is ahead. */
    public static function currentDate(VendorMap $map): ?string
    {
        $event = $map->event;

        if (! $event) {
            return null;
        }

        return $event->nextOccurrenceFrom(Carbon::now($event->scheduleTimezone()));
    }

    /** The next date after $date the market is on, or null. */
    public static function nextDateAfter(VendorMap $map, string $date): ?string
    {
        $event = $map->event;

        if (! $event) {
            return null;
        }

        return $event->nextOccurrenceFrom(Carbon::parse($date)->addDay());
    }

    /**
     * The next date after $date this season occupant is actually there (not absent), or null.
     * Bounded: twelve market days ahead is plenty to say "next: 28 Nov".
     */
    public static function nextPresentDate(VendorMap $map, VendorMapOccupant $occupant, string $date): ?string
    {
        $cursor = $date;
        $absentDates = VendorMapOccupant::where('vendor_map_id', $map->id)
            ->where('spot_id', $occupant->spot_id)
            ->where('kind', 'absent')
            ->where('status', 'approved')
            ->where('event_date', '>', $date)
            ->when($occupant->vendor_role_id, fn ($q) => $q->where('vendor_role_id', $occupant->vendor_role_id), fn ($q) => $q->whereNull('vendor_role_id'))
            ->pluck('event_date')
            ->all();

        for ($i = 0; $i < 12; $i++) {
            $cursor = self::nextDateAfter($map, $cursor);
            if (! $cursor) {
                return null;
            }
            if (! in_array($cursor, $absentDates, true)) {
                return $cursor;
            }
        }

        return null;
    }

    /**
     * nextPresentDate() for every absent season vendor of a date, with ONE query for their
     * absences (a page with eleven absent vendors asked eleven times).
     *
     * @param  VendorMapOccupant[]  $occupants
     * @return array<int, ?string> keyed by occupant id
     */
    public static function nextPresentDates(VendorMap $map, array $occupants, string $date): array
    {
        if ($occupants === []) {
            return [];
        }

        $absent = VendorMapOccupant::where('vendor_map_id', $map->id)
            ->whereIn('spot_id', array_map(fn ($o) => $o->spot_id, $occupants))
            ->where('kind', 'absent')
            ->where('status', 'approved')
            ->where('event_date', '>', $date)
            ->get(['spot_id', 'vendor_role_id', 'placeholder_name', 'event_date']);

        // The market's next dates once, shared by every vendor.
        $dates = [];
        $cursor = $date;
        for ($i = 0; $i < 12 && $cursor; $i++) {
            $cursor = self::nextDateAfter($map, $cursor);
            if ($cursor) {
                $dates[] = $cursor;
            }
        }

        $out = [];
        foreach ($occupants as $o) {
            $theirs = $absent->filter(fn ($a) => (int) $a->spot_id === (int) $o->spot_id
                && ($o->vendor_role_id ? (int) $a->vendor_role_id === (int) $o->vendor_role_id : $a->vendor_role_id === null))
                ->pluck('event_date')->all();
            $out[$o->id] = collect($dates)->first(fn ($d) => ! in_array($d, $theirs, true));
        }

        return $out;
    }

    /** Claims waiting for the organizer, newest last, with their vendor pages loaded. */
    public static function pending(VendorMap $map): Collection
    {
        return VendorMapOccupant::where('vendor_map_id', $map->id)
            ->where('status', 'pending')
            ->with(['vendor', 'spot:id,label,vendor_map_id,area_id'])
            ->orderBy('created_at')
            ->get()
            ->filter(fn ($row) => ! $row->vendor_role_id || ($row->vendor && ! $row->vendor->is_deleted))
            ->values();
    }

    /**
     * Approved rows for the given event dates, with vendor pages loaded once and the hidden ones
     * (deleted, or owned by a blocked account) taken out.
     */
    private static function approvedRows(VendorMap $map, array $dates): Collection
    {
        $rows = VendorMapOccupant::where('vendor_map_id', $map->id)
            ->where('status', 'approved')
            ->whereIn('event_date', $dates)
            ->whereHas('spot', fn ($q) => $q->whereNull('retired_at'))
            // The whole row: getGuestUrl() asks isClaimed() (verified email or phone) and the custom
            // domain's state, and a narrowed select answered '' for every vendor.
            ->with('vendor')
            ->orderBy('id')
            ->get();

        $hiddenRoles = self::hiddenVendorIds($rows->pluck('vendor_role_id')->filter()->unique()->all());

        return $rows->reject(fn ($row) => $row->vendor_role_id && (! $row->vendor || $row->vendor->is_deleted || in_array($row->vendor_role_id, $hiddenRoles, true)))->values();
    }

    /**
     * @return int[] vendor schedules owned by a blocked account. A block also marks them deleted,
     *               which approvedRows() already reads; this covers a page restored by hand.
     */
    private static function hiddenVendorIds(array $roleIds): array
    {
        if ($roleIds === []) {
            return [];
        }

        $blocked = new BlockedAccounts;

        return $blocked->any() ? array_values(array_intersect($roleIds, $blocked->scheduleIds())) : [];
    }
}
