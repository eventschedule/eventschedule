<?php

namespace App\Services\VendorMaps;

use App\Mail\VendorMapClaimApproved;
use App\Mail\VendorMapClaimDeclined;
use App\Mail\VendorMapClaimsWaiting;
use App\Models\Role;
use App\Models\User;
use App\Models\VendorMap;
use App\Models\VendorMapOccupant;
use App\Services\NotificationEmailService;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * The vendor map's mail, both ways.
 *
 * To the market's editors (the owner and admins who get request mail, and the schedule's shared
 * notification address): ONE mail naming the vendors who asked for a spot and the vendors who
 * said they are not coming, since the last such mail. To a vendor: the organizer's answer,
 * approved or declined.
 *
 * Modelled on RequestNotifier. A row is stamped on its own (`notified_at`, `vendor_notified_at`)
 * only once a mail that NAMES it has left, so every claim is in one owner mail and no other; a
 * row past what one mail names stays unstamped for the next. One run per map at a time, under a
 * cache lock, because both cron rails call app:send-vendor-map-notices. The owner mail goes at
 * most once a quarter of an hour per map (OWNER_EVERY_MINUTES): a market's first WhatsApp share
 * brings thirty claims in an afternoon, and thirty mails would be worse than none.
 *
 * The vendor's mail waits HOLD_MINUTES after the organizer's answer, so the "Undo" the organizer
 * is shown for a few seconds is a real undo (VendorMapClaims::undo() refuses once the mail left).
 * It goes to the vendor page's CURRENT owners, never to whoever asked (`user_id`): a page handed
 * over since must not keep telling the person who left it. It speaks in the market's language,
 * because the market is what it is about and its organizer chose it.
 *
 * Nothing here sends a phone number: those are for the editors' screens only.
 */
class VendorClaimNotifier
{
    /** Minutes the vendor's mail waits after the organizer's answer, so Undo is real. */
    public const HOLD_MINUTES = 2;

    /** The owner mail goes at most this often per map. */
    public const OWNER_EVERY_MINUTES = 15;

    /** Lines (claims and "not coming" together) one owner mail names. The rest are the next mail's. */
    public const LINES = 25;

    /** A decision older than this is never mailed: a backlog (a mailer down for a week) is not news. */
    public const STALE_DAYS = 7;

    /** Rows one run reads per map. */
    private const BATCH = 500;

    /**
     * Called by the claim page right after a vendor asks. Deliberately sends nothing: the owner
     * mail is batched, and app:send-vendor-map-notices (every five minutes, on both cron rails)
     * picks the claim up within the quarter of an hour the batching allows. It exists so the
     * claim code has one place to say "something changed", should the mail ever go sooner.
     */
    public static function soon(VendorMap $map): void
    {
        // Intentionally empty. See the docblock.
    }

    /**
     * The maps with something to send, either kind.
     *
     * @return Collection<int, int> vendor map ids
     */
    public static function mapsWithWork(): Collection
    {
        return VendorMapOccupant::query()
            ->where(fn ($q) => $q->where(fn ($q) => self::ownerNews($q))->orWhere(fn ($q) => self::vendorNews($q)))
            ->distinct()
            ->pluck('vendor_map_id');
    }

    /**
     * Send what is due for one map. Never throws for a mail server's trouble: a claim or a
     * decision that is saved must stay saved, and the next run tries again.
     *
     * @return array{owners: bool, vendors: int}
     */
    public function run(VendorMap $map): array
    {
        try {
            return Cache::lock('vendor-map-notices:'.$map->id, 600)->block(15, fn () => [
                'owners' => $this->tellOwners($map),
                'vendors' => $this->tellVendors($map),
            ]);
        } catch (LockTimeoutException $e) {
            return ['owners' => false, 'vendors' => 0];
        }
    }

    /** The batched mail to the market's editors. Returns whether anybody was told. */
    public function tellOwners(VendorMap $map): bool
    {
        $role = $map->role;

        if (! $role || $role->is_deleted || is_demo_role($role)) {
            return false;
        }

        if (Cache::has($this->ownerThrottleKey($map))) {
            return false;
        }

        $editors = $role->getEditorsWantingNotification('new_request');
        if ($editors->isEmpty() && ! $role->getNotificationEmailWanting('new_request', $editors)) {
            return false;
        }

        // Read once, before anything is sent: a claim that arrives while the mail is on its way
        // was not in it, and must not be stamped as though it had been.
        $rows = VendorMapOccupant::where('vendor_map_id', $map->id)
            ->where(fn ($q) => self::ownerNews($q))
            ->with(['vendor:id,name,name_en,is_deleted', 'spot:id,label'])
            ->orderBy('id')
            ->limit(self::BATCH)
            ->get();

        if ($rows->isEmpty()) {
            return false;
        }

        $claims = $this->lines($rows->where('kind', 'present'));
        $absences = $this->lines($rows->where('kind', 'absent'));

        // The oldest are named, claims first; whatever is past LINES waits for the next mail.
        $namedClaims = $claims->take(self::LINES);
        $namedAbsences = $absences->take(max(0, self::LINES - $namedClaims->count()));
        $following = ($claims->count() - $namedClaims->count()) + ($absences->count() - $namedAbsences->count());

        $told = $namedClaims->concat($namedAbsences)->flatMap(fn ($line) => $line['ids'])->all();
        $strip = fn (Collection $lines) => $lines->map(fn ($line) => collect($line)->except('ids')->all())->values()->all();
        $make = fn () => new VendorMapClaimsWaiting($role, $map->kind(), $strip($namedClaims), $strip($namedAbsences), $following);

        $sent = 0;
        foreach ($editors as $editor) {
            if (! $editor->email) {
                continue;
            }

            $locale = is_valid_language_code($editor->language_code)
                ? $editor->language_code
                : NotificationEmailService::locale($role);

            try {
                Mail::to($editor->email)->locale($locale)->send($make());
                $sent++;
            } catch (\Throwable $e) {
                report($e);
            }
        }

        if (app(NotificationEmailService::class)->sendMailable($role, 'new_request', $make(), $editors)) {
            $sent++;
        }

        // Stamped only once a mail that names them has left.
        if ($sent > 0) {
            foreach (array_chunk($told, 500) as $ids) {
                VendorMapOccupant::whereIn('id', $ids)->whereNull('notified_at')->update(['notified_at' => now()]);
            }
            Cache::put($this->ownerThrottleKey($map), true, now()->addMinutes(self::OWNER_EVERY_MINUTES));
        }

        return $sent > 0;
    }

    /** The organizer's answers, to the vendors. Returns how many requests were told of. */
    public function tellVendors(VendorMap $map): int
    {
        $market = $map->role;

        if (! $market || $market->is_deleted) {
            return 0;
        }

        $rows = VendorMapOccupant::where('vendor_map_id', $map->id)
            ->where(fn ($q) => self::vendorNews($q))
            ->with(['vendor', 'spot:id,label,vendor_map_id'])
            ->orderBy('id')
            ->limit(self::BATCH)
            ->get();

        $told = 0;

        // A vendor who chose dates asked once and got one row per date: one mail answers them all.
        foreach ($this->requests($rows) as $group) {
            /** @var VendorMapOccupant $first */
            $first = $group->first();
            $vendor = $first->vendor;
            $ok = true;

            if ($vendor && ! $vendor->is_deleted && ! is_demo_role($market)) {
                $ok = $this->mailVendor($map, $vendor, $group);
            }

            // A vendor page that is gone, or nobody left to tell, is settled too: there is
            // nobody this row could ever be mailed to.
            if ($ok) {
                VendorMapOccupant::whereIn('id', $group->pluck('id')->all())
                    ->whereNull('vendor_notified_at')
                    ->update(['vendor_notified_at' => now()]);
                // Told: the approval is final, and what it stood aside can go.
                $group->each(fn ($row) => VendorMapClaims::forgetReplaced($row));
                $told++;
            }
        }

        return $told;
    }

    /**
     * One request's answer to its vendor page's owners. False only when every send failed, so
     * the rows stay unstamped and the next run tries again.
     *
     * @param  Collection<int, VendorMapOccupant>  $group
     */
    private function mailVendor(VendorMap $map, Role $vendor, Collection $group): bool
    {
        // Unsubscribed ("Email updates" off) is respected as for the other answers to a request
        // (EventController::mailRequestDecision()); the vendor's own link still shows the answer.
        $owners = $vendor->members()->wherePivot('level', 'owner')->get()
            ->filter(fn (User $user) => filled($user->email) && (bool) $user->is_subscribed)
            ->values();

        if ($owners->isEmpty()) {
            return true;
        }

        $first = $group->first();
        $dates = $group->pluck('event_date')->reject(fn ($d) => $d === VendorMapOccupant::SEASON)->sort()->values()->all();
        $locale = NotificationEmailService::locale($map->role);
        $failed = 0;

        foreach ($owners as $owner) {
            $mail = $first->status === 'approved'
                ? new VendorMapClaimApproved($map, $first, $vendor, $owner, $dates, $this->nextDate($map, $first, $dates))
                : new VendorMapClaimDeclined($map, $first, $vendor, $owner, $dates);

            try {
                Mail::to($owner->email)->locale($locale)->send($mail);
            } catch (\Throwable $e) {
                report($e);
                $failed++;
            }
        }

        return $failed < $owners->count();
    }

    /** The next date the vendor stands there: the first of their dates ahead, or the season's next. */
    private function nextDate(VendorMap $map, VendorMapOccupant $row, array $dates): ?string
    {
        try {
            $current = VendorMapOccupancy::currentDate($map);

            if ($dates !== []) {
                $today = $current ?? now()->toDateString();

                return collect($dates)->first(fn ($d) => $d >= $today);
            }

            if (! $current) {
                return null;
            }

            $absent = VendorMapOccupant::where('vendor_map_id', $map->id)
                ->where('spot_id', $row->spot_id)
                ->where('event_date', $current)
                ->where('kind', 'absent')
                ->where('status', 'approved')
                ->where('vendor_role_id', $row->vendor_role_id)
                ->exists();

            return $absent ? VendorMapOccupancy::nextPresentDate($map, $row, $current) : $current;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    /**
     * Rows made together are one request: a vendor who chose three dates, or one "not coming"
     * for two Fridays, is one line. Grouped by who, which spot and what was decided. Not by the
     * second the rows were made in: a request saved across a second's edge became two mails.
     * Only rows not yet told are grouped, and a vendor has one waiting request per map at a time
     * (VendorMapClaims::request() replaces the last), so this never joins two requests.
     *
     * @param  Collection<int, VendorMapOccupant>  $rows
     * @return Collection<int, Collection<int, VendorMapOccupant>>
     */
    private function requests(Collection $rows): Collection
    {
        return $rows->groupBy(fn (VendorMapOccupant $row) => implode('|', [
            $row->vendor_role_id ?: 'row'.$row->id,
            $row->spot_id,
            $row->kind,
            $row->status,
        ]))->values();
    }

    /**
     * Plain lines for the owner mail, worded in the view (each reader's language): who, which
     * spot, what they sell, which dates. No phone number.
     *
     * @return Collection<int, array{ids: int[], name: string, spot: string, sells: ?string, categories: string[], dates: string[]}>
     */
    private function lines(Collection $rows): Collection
    {
        return $this->requests($rows)->map(function (Collection $group) {
            $row = $group->first();
            $typed = trim((string) $row->placeholder_name);

            return [
                'ids' => $group->pluck('id')->all(),
                'name' => $typed !== '' ? $typed : $row->displayName(),
                'spot' => (string) $row->spot?->label,
                'sells' => $row->kind === 'present' ? $row->sells : null,
                'categories' => $row->kind === 'present' ? array_values((array) $row->categories) : [],
                'dates' => $group->pluck('event_date')->reject(fn ($d) => $d === VendorMapOccupant::SEASON)->sort()->values()->all(),
            ];
        })->values();
    }

    /**
     * What the editors are told of: a claim waiting for them, and a "not coming" a VENDOR set
     * for a date ahead. A mark the organizer made themselves (they are an owner or admin of the
     * market) is not news to them.
     */
    private static function ownerNews(Builder $q): Builder
    {
        $today = now()->subDay()->toDateString();

        return $q->whereNull('notified_at')
            ->where(fn ($q) => $q
                ->where(fn ($q) => $q->where('kind', 'present')->where('status', 'pending')
                    ->where(fn ($q) => $q->whereNull('vendor_role_id')->orWhereHas('vendor', fn ($q) => $q->where('is_deleted', false))))
                ->orWhere(fn ($q) => $q->where('kind', 'absent')->where('status', 'approved')
                    ->whereNotNull('user_id')
                    ->where('event_date', '>=', $today)
                    ->whereNotExists(fn ($q) => $q->select(DB::raw(1))
                        ->from('role_user')
                        ->join('vendor_maps', 'vendor_maps.role_id', '=', 'role_user.role_id')
                        ->whereColumn('vendor_maps.id', 'vendor_map_occupants.vendor_map_id')
                        ->whereColumn('role_user.user_id', 'vendor_map_occupants.user_id')
                        ->whereIn('role_user.level', ['owner', 'admin']))));
    }

    /**
     * What a vendor is told of: the organizer's answer to a request they made (a signed-in
     * vendor asked: `user_id`; a name the organizer typed or a lineup put on the map is not a
     * request), held HOLD_MINUTES for Undo, and not older than STALE_DAYS.
     */
    private static function vendorNews(Builder $q): Builder
    {
        return $q->where('kind', 'present')
            ->whereIn('status', ['approved', 'declined'])
            ->whereNull('vendor_notified_at')
            ->whereNotNull('vendor_role_id')
            ->whereNotNull('user_id')
            ->whereNotNull('decided_at')
            ->where('decided_at', '<=', now()->subMinutes(self::HOLD_MINUTES))
            ->where('decided_at', '>=', now()->subDays(self::STALE_DAYS));
    }

    private function ownerThrottleKey(VendorMap $map): string
    {
        return 'vendor-map-owner-mailed:'.$map->id;
    }
}
