<?php

namespace App\Services\VendorMaps;

use App\Models\Role;
use App\Models\User;
use App\Models\VendorMap;
use App\Models\VendorMapOccupant;
use App\Models\VendorMapSpot;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Everything that changes who is on a spot: a vendor asking for one, the organizer's answer, a
 * move, "Not coming", a vendor for one day.
 *
 * Every write runs in a transaction that locks the spot's rows first, and the database holds the
 * last line: one approved vendor per spot per date (vendor_map_occupants_one_per_spot), so two
 * approvals racing for one stall end in one stall-holder and one friendly refusal.
 */
class VendorMapClaims
{
    /**
     * A vendor asks for a spot. Waits for the organizer. Replaces their own earlier request on
     * the same map, so tapping two stalls by mistake leaves one request, not two.
     *
     * @param  array{name: string, sells: ?string, categories: array, phone: ?string, dates: ?array, photo_path: ?string}  $details
     */
    public static function request(VendorMap $map, VendorMapSpot $spot, Role $vendor, User $user, array $details): VendorMapOccupant
    {
        $categories = array_values(array_filter((array) ($details['categories'] ?? []), fn ($c) => VendorMapKinds::isCategory($map->kind, $c)));

        return DB::transaction(function () use ($map, $spot, $vendor, $user, $details, $categories) {
            // One waiting request per vendor here: this one replaces what this page, or this person
            // under another page or name, asked for before. Removed after the new rows exist, so
            // a photo the new request reuses is not taken away with the old one.
            $earlier = VendorMapOccupant::where('vendor_map_id', $map->id)
                ->where('status', 'pending')
                ->where(fn ($q) => $q->where('vendor_role_id', $vendor->id)->orWhere('user_id', $user->id))
                ->pluck('id');

            $dates = self::validDates($map, $details['dates'] ?? null);

            $first = null;
            foreach ($dates ?? [VendorMapOccupant::SEASON] as $date) {
                $row = new VendorMapOccupant([
                    'vendor_map_id' => $map->id,
                    'spot_id' => $spot->id,
                    'event_date' => $date,
                    'kind' => 'present',
                    'vendor_role_id' => $vendor->id,
                    'placeholder_name' => mb_substr(trim((string) ($details['name'] ?? '')), 0, 120) ?: null,
                    'sells' => isset($details['sells']) ? (mb_substr(trim((string) $details['sells']), 0, 200) ?: null) : null,
                    'categories' => $categories ?: null,
                    'phone' => self::normalisePhone($details['phone'] ?? null, $map->role?->country_code),
                    'photo_path' => $details['photo_path'] ?? null,
                    'user_id' => $user->id,
                ]);
                $row->status = 'pending';
                $row->save();
                $first ??= $row;
            }

            VendorMapOccupant::whereIn('id', $earlier)->get()->each->delete(); // one by one: each takes its photo

            return $first;
        });
    }

    /**
     * Approve a waiting request. A name only that the request said "Is this you?" to is replaced
     * in the same transaction (or the unique index would refuse the vendor its own stall).
     *
     * @throws ValidationException when the spot already has an approved vendor for that date
     */
    public static function approve(VendorMapOccupant $claim): void
    {
        try {
            DB::transaction(function () use ($claim) {
                $claim = VendorMapOccupant::whereKey($claim->id)->lockForUpdate()->firstOrFail();

                if ($claim->status !== 'pending') {
                    return;
                }

                $holder = VendorMapOccupant::where('spot_id', $claim->spot_id)
                    ->where('event_date', $claim->event_date)
                    ->where('kind', 'present')
                    ->where('status', 'approved')
                    ->lockForUpdate()
                    ->first();

                if ($holder && $holder->vendor_role_id === null) {
                    // A name only: the vendor is taking their own stall. Keep the AI's or the
                    // organizer's words where the vendor gave none.
                    $claim->sells ??= $holder->sells;
                    $claim->categories ??= $holder->categories;
                    self::standAside($holder, $claim);
                } elseif ($holder && (int) $holder->vendor_role_id !== (int) $claim->vendor_role_id) {
                    throw ValidationException::withMessages(['spot' => __('messages.vm_spot_taken')]);
                } elseif ($holder) {
                    self::standAside($holder, $claim);
                }

                // A vendor holds one spot a season on one map: approving a new stall for the
                // season moves them, it does not give them two.
                if ($claim->event_date === VendorMapOccupant::SEASON && $claim->vendor_role_id) {
                    VendorMapOccupant::where('vendor_map_id', $claim->vendor_map_id)
                        ->where('vendor_role_id', $claim->vendor_role_id)
                        ->where('event_date', VendorMapOccupant::SEASON)
                        ->where('status', 'approved')
                        ->where('id', '!=', $claim->id)
                        ->get()->each(fn ($old) => self::standAside($old, $claim));
                }

                $claim->status = 'approved';
                $claim->decided_at = now();
                $claim->vendor_notified_at = null;
                $claim->save();

                self::publishPhoto($claim);
            });
        } catch (QueryException $e) {
            if (self::isDuplicate($e)) {
                throw ValidationException::withMessages(['spot' => __('messages.vm_spot_taken')]);
            }
            throw $e;
        }
    }

    public static function decline(VendorMapOccupant $claim, ?string $note = null): void
    {
        $claim->forceFill([
            'status' => 'declined',
            'decided_at' => now(),
            'note' => $note !== null ? mb_substr(trim($note), 0, 500) : null,
            'vendor_notified_at' => null,
        ])->save();
    }

    /**
     * Undo an answer given in the last few minutes (the toast's Undo). The vendor's mail waits
     * VendorClaimNotifier::HOLD_MINUTES, so before then an undo is a real undo.
     */
    public static function undo(VendorMapOccupant $row): bool
    {
        if (! in_array($row->status, ['approved', 'declined'], true) || $row->vendor_notified_at || ! $row->decided_at || $row->decided_at->lt(now()->subMinutes(10))) {
            return false;
        }

        DB::transaction(function () use ($row) {
            // Waiting again: the public thumbnail approving made comes down until it is approved again.
            $thumb = $row->photo_thumb_path;
            $row->forceFill(['status' => 'pending', 'decided_at' => null, 'note' => null, 'photo_thumb_path' => null])->save();
            if ($thumb && ! VendorMapOccupant::where('photo_thumb_path', $thumb)->exists()) {
                VendorMapImages::deletePublic($thumb);
            }

            // What the approval stood aside comes back: the name on the stall, or the vendor's
            // previous spot.
            VendorMapOccupant::where('replaced_by', $row->id)->where('status', 'replaced')
                ->get()->each(fn ($old) => $old->forceFill(['status' => 'approved', 'replaced_by' => null])->save());
        });

        return true;
    }

    /**
     * The season's vendor on a spot is not there on these dates. Idempotent per date.
     *
     * @param  string[]  $dates
     */
    public static function markAbsent(VendorMap $map, VendorMapOccupant $season, array $dates, ?User $by = null): int
    {
        $count = 0;
        foreach (self::validDates($map, $dates) ?? [] as $date) {
            VendorMapOccupant::firstOrCreate([
                'vendor_map_id' => $map->id,
                'spot_id' => $season->spot_id,
                'event_date' => $date,
                'kind' => 'absent',
                'vendor_role_id' => $season->vendor_role_id,
                'placeholder_name' => $season->vendor_role_id ? null : $season->placeholder_name,
            ], ['user_id' => $by?->id])->forceFill(['status' => 'approved', 'decided_at' => now()])->save();
            $count++;
        }

        return $count;
    }

    /** "I'm coming after all": take the absent row for that date away. */
    public static function clearAbsent(VendorMap $map, VendorMapOccupant $season, string $date): void
    {
        VendorMapOccupant::where('vendor_map_id', $map->id)
            ->where('spot_id', $season->spot_id)
            ->where('event_date', $date)
            ->where('kind', 'absent')
            ->when($season->vendor_role_id, fn ($q) => $q->where('vendor_role_id', $season->vendor_role_id), fn ($q) => $q->whereNull('vendor_role_id')->where('placeholder_name', $season->placeholder_name))
            ->delete();
    }

    /**
     * Move a season vendor to another spot, for one date or for the rest of the season. Onto a
     * spot that has its own season vendor, the two swap.
     */
    public static function move(VendorMap $map, VendorMapOccupant $season, VendorMapSpot $to, ?string $date): void
    {
        if ((int) $to->vendor_map_id !== (int) $map->id || $to->retired_at) {
            throw ValidationException::withMessages(['spot' => __('messages.vm_spot_taken')]);
        }

        // A vendor here for one date only is simply moved, to a spot nobody holds that date.
        if ($season->event_date !== VendorMapOccupant::SEASON) {
            if ((VendorMapOccupancy::on($map, $season->event_date)[$to->id]['state'] ?? 'free') === 'present') {
                throw ValidationException::withMessages(['spot' => __('messages.vm_spot_taken')]);
            }
            $season->forceFill(['spot_id' => $to->id])->save();

            return;
        }

        try {
            DB::transaction(function () use ($map, $season, $to, $date) {
                $other = VendorMapOccupant::where('spot_id', $to->id)
                    ->where('event_date', VendorMapOccupant::SEASON)
                    ->where('kind', 'present')
                    ->where('status', 'approved')
                    ->lockForUpdate()
                    ->first();

                if ($date === null) {
                    $from = $season->spot_id;
                    // A swap: the other vendor steps out of the one-per-spot index while the
                    // mover takes the spot, then comes back on the mover's old one.
                    $other?->forceFill(['status' => 'pending'])->save();
                    $season->forceFill(['spot_id' => $to->id])->save();
                    $other?->forceFill(['spot_id' => $from, 'status' => 'approved'])->save();
                    // Dates they already said they would miss go with them.
                    self::carryAbsences($map, $season, $from, $to->id);
                    if ($other) {
                        self::carryAbsences($map, $other, $to->id, $from);
                    }

                    return;
                }

                // The other vendor is not there that date anyway: nobody to swap with.
                if ($other && (VendorMapOccupancy::on($map, $date)[$to->id]['state'] ?? 'free') !== 'present') {
                    $other = null;
                }

                // One date: absent where they were, present where they go; the other vendor the
                // mirror image, so each shows exactly once that day.
                self::markAbsent($map, $season, [$date]);
                self::placeFor($map, $to, $season, $date);
                if ($other) {
                    self::markAbsent($map, $other, [$date]);
                    self::placeFor($map, VendorMapSpot::findOrFail($season->spot_id), $other, $date);
                }
            });
        } catch (QueryException $e) {
            if (self::isDuplicate($e)) {
                throw ValidationException::withMessages(['spot' => __('messages.vm_spot_taken')]);
            }
            throw $e;
        }
    }

    /**
     * Out of the way of an approval, but kept until the approval can no longer be undone
     * (VendorClaimNotifier clears these once the vendor is told; the prune command after a day).
     */
    private static function standAside(VendorMapOccupant $old, VendorMapOccupant $claim): void
    {
        $old->forceFill(['status' => 'replaced', 'replaced_by' => $claim->id])->save();
    }

    /** Rows stood aside by an approval that is now final. */
    public static function forgetReplaced(VendorMapOccupant $claim): void
    {
        VendorMapOccupant::where('replaced_by', $claim->id)->where('status', 'replaced')->get()->each->delete();
    }

    /** A season vendor's future "not coming" dates follow them to their new spot. */
    private static function carryAbsences(VendorMap $map, VendorMapOccupant $season, int $from, int $to): void
    {
        VendorMapOccupant::where('vendor_map_id', $map->id)
            ->where('spot_id', $from)
            ->where('kind', 'absent')
            ->where('event_date', '!=', VendorMapOccupant::SEASON)
            ->when($season->vendor_role_id,
                fn ($q) => $q->where('vendor_role_id', $season->vendor_role_id),
                fn ($q) => $q->whereNull('vendor_role_id')->where('placeholder_name', $season->placeholder_name))
            ->update(['spot_id' => $to]);
    }

    /** A vendor for one date only, on a free spot: a name and what they sell, no account. */
    public static function addForDay(VendorMap $map, VendorMapSpot $spot, string $date, string $name, ?string $category): VendorMapOccupant
    {
        if (! self::validDates($map, [$date])) {
            throw ValidationException::withMessages(['date' => __('messages.vm_not_open_that_day')]);
        }

        $state = VendorMapOccupancy::on($map, $date)[$spot->id]['state'] ?? 'free';
        if ($state === 'present') {
            throw ValidationException::withMessages(['spot' => __('messages.vm_spot_taken')]);
        }

        $row = new VendorMapOccupant([
            'vendor_map_id' => $map->id,
            'spot_id' => $spot->id,
            'event_date' => $date,
            'kind' => 'present',
            'placeholder_name' => mb_substr(trim($name), 0, 120),
            'categories' => VendorMapKinds::isCategory($map->kind, $category) ? [$category] : null,
        ]);
        $row->status = 'approved';
        $row->decided_at = now();

        try {
            $row->save();
        } catch (QueryException $e) {
            if (self::isDuplicate($e)) {
                throw ValidationException::withMessages(['spot' => __('messages.vm_spot_taken')]);
            }
            throw $e;
        }

        return $row;
    }

    /** "Stop coming": the vendor gives up their spot for the rest of the season. */
    public static function stop(VendorMap $map, VendorMapOccupant $season): void
    {
        VendorMapOccupant::where('vendor_map_id', $map->id)
            ->where('vendor_role_id', $season->vendor_role_id)
            ->whereIn('status', ['approved', 'pending'])
            ->where(fn ($q) => $q->where('event_date', VendorMapOccupant::SEASON)->orWhere('event_date', '>=', now()->toDateString()))
            ->get()->each->delete();
    }

    /** The approved season row that is this vendor's on this map, if any. */
    public static function seasonOf(VendorMap $map, Role $vendor): ?VendorMapOccupant
    {
        return VendorMapOccupant::where('vendor_map_id', $map->id)
            ->where('vendor_role_id', $vendor->id)
            ->where('event_date', VendorMapOccupant::SEASON)
            ->where('kind', 'present')
            ->where('status', 'approved')
            ->first();
    }

    private static function placeFor(VendorMap $map, VendorMapSpot $spot, VendorMapOccupant $from, string $date): void
    {
        VendorMapOccupant::where('spot_id', $spot->id)->where('event_date', $date)->where('kind', 'absent')
            ->when($from->vendor_role_id, fn ($q) => $q->where('vendor_role_id', $from->vendor_role_id))
            ->delete();

        $row = new VendorMapOccupant([
            'vendor_map_id' => $map->id,
            'spot_id' => $spot->id,
            'event_date' => $date,
            'kind' => 'present',
            'vendor_role_id' => $from->vendor_role_id,
            'placeholder_name' => $from->placeholder_name,
            'sells' => $from->sells,
            'categories' => $from->categories,
            'photo_thumb_path' => $from->photo_thumb_path,
        ]);
        $row->status = 'approved';
        $row->decided_at = now();
        $row->vendor_notified_at = now();
        $row->save();
    }

    /**
     * The dates among $dates that the map's event falls on (the event decides its days). Null
     * means "every date", which is what an empty or missing list asks for.
     *
     * @return string[]|null
     */
    public static function validDates(VendorMap $map, $dates): ?array
    {
        if (! is_array($dates) || $dates === []) {
            return null;
        }

        $valid = [];
        foreach (array_slice(array_unique($dates), 0, 60) as $date) {
            if (is_string($date) && VendorMapOccupancy::isOpenOn($map, $date)) {
                $valid[] = $date;
            }
        }

        return $valid ?: null;
    }

    /**
     * International form (+972...) from what a vendor typed, using the schedule's country for a
     * local number. Kept only if it looks like a phone number.
     */
    public static function normalisePhone(?string $phone, ?string $country): ?string
    {
        $digits = preg_replace('/[^\d+]/', '', (string) $phone);

        if ($digits === '' || strlen(ltrim($digits, '+')) < 7 || strlen($digits) > 20) {
            return null;
        }

        if (str_starts_with($digits, '+')) {
            return $digits;
        }

        if (str_starts_with($digits, '00')) {
            return '+'.substr($digits, 2);
        }

        $codes = ['IL' => '972', 'US' => '1', 'CA' => '1', 'GB' => '44', 'DE' => '49', 'FR' => '33', 'ES' => '34', 'IT' => '39', 'NL' => '31', 'PT' => '351', 'BR' => '55', 'RO' => '40', 'RU' => '7', 'EE' => '372', 'AU' => '61', 'IE' => '353'];
        $code = $codes[strtoupper((string) $country)] ?? null;

        return $code ? '+'.$code.ltrim($digits, '0') : $digits;
    }

    /** A claim's photo becomes public (a small thumbnail) only when the organizer approves it. */
    private static function publishPhoto(VendorMapOccupant $row): void
    {
        if (! $row->photo_path || $row->photo_thumb_path) {
            return;
        }

        $thumb = VendorMapImages::publishThumbnail($row->photo_path);
        if ($thumb) {
            $row->forceFill(['photo_thumb_path' => $thumb])->save();
        }
    }

    private static function isDuplicate(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }
}
