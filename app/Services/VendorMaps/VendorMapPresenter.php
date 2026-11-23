<?php

namespace App\Services\VendorMaps;

use App\Models\Role;
use App\Models\VendorMap;
use App\Models\VendorMapOccupant;
use App\Utils\GeminiUtils;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * What each screen of the vendor map is given, in one place, so what reaches the public is
 * decided once: publicPayload() never carries a phone number, a user, a claim that is waiting or
 * a private picture.
 */
class VendorMapPresenter
{
    /** Props for the Vendor map tab (VendorMapAdmin.vue). */
    public static function adminProps(Role $role): array
    {
        $map = VendorMap::where('role_id', $role->id)->first();
        $map?->setRelation('role', $role);
        $sub = $role->subdomain;

        $events = $role->ownEvents()
            ->where('events.is_draft', false)
            ->orderByDesc('events.starts_at')
            ->limit(40)
            ->get(['events.id', 'events.name', 'events.starts_at', 'events.days_of_week', 'events.creator_role_id', 'events.recurring_end_type', 'events.recurring_end_value', 'events.recurring_frequency', 'events.recurring_exclude_dates', 'events.recurring_include_dates', 'events.duration', 'events.is_cancelled'])
            ->filter(fn ($e) => $e->nextOccurrenceFrom() !== null)
            ->map(fn ($e) => ['id' => UrlUtils::encodeId($e->id), 'name' => $e->translatedName(), 'recurring' => (bool) $e->days_of_week])
            ->values()->all();

        $kind = $map?->kind() ?? 'market';

        return [
            'exists' => (bool) $map,
            'enabled' => (bool) $map?->enabled,
            'kind' => $kind,
            'kinds' => VendorMapKinds::kindLabels(),
            'event' => $map?->event_id ? UrlUtils::encodeId($map->event_id) : null,
            'eventName' => $map?->event?->translatedName(),
            'events' => $events,
            'isPro' => $role->isPro(),
            'aiAvailable' => $map ? VendorMapAi::available($map) : (GeminiUtils::canReadSeveralImages() && $role->isPro()),
            'registrationOpen' => config('app.hosted') || public_registration_enabled(),
            'hasSpots' => $map ? $map->spots()->exists() : false,
            'date' => $map ? VendorMapOccupancy::currentDate($map) : null,
            'claimsOpen' => (bool) $map?->claims_open,
            'claimUrl' => $map?->claim_token ? self::claimUrl($role, $map) : null,
            'publicUrl' => $map ? self::publicUrl($role) : null,
            'waiting' => $map ? VendorMapOccupant::where('vendor_map_id', $map->id)->where('status', 'pending')->count() : 0,
            'categories' => VendorMapKinds::categoryLabels($kind),
            'strings' => VendorMapKinds::stringsFor($kind),
            'scheduleName' => $role->translatedName(),
            'ownerFirstName' => self::ownerFirstName($role),
            'rtl' => $role->isRtl(),
            'urls' => [
                'setup' => route('vendor_map.setup', ['subdomain' => $sub], false),
                'settings' => route('vendor_map.settings', ['subdomain' => $sub], false),
                'design' => route('vendor_map.design', ['subdomain' => $sub], false),
                'day' => route('vendor_map.day', ['subdomain' => $sub], false),
                'photos' => route('vendor_map.photos', ['subdomain' => $sub], false),
                'parse' => route('vendor_map.draft.parse', ['subdomain' => $sub, 'draft' => '__DRAFT__'], false),
                'structure' => route('vendor_map.structure', ['subdomain' => $sub], false),
                'draft' => route('vendor_map.draft', ['subdomain' => $sub], false),
                'decide' => route('vendor_map.decide', ['subdomain' => $sub, 'occupant' => '__ID__', 'action' => '__ACTION__'], false),
                'approveAll' => route('vendor_map.approve_all', ['subdomain' => $sub], false),
                'move' => route('vendor_map.move', ['subdomain' => $sub], false),
                'dayVendor' => route('vendor_map.day_vendor', ['subdomain' => $sub], false),
                'cancelDay' => route('vendor_map.cancel_day', ['subdomain' => $sub], false),
                'link' => route('vendor_map.link', ['subdomain' => $sub], false),
                'sign' => route('vendor_map.sign', ['subdomain' => $sub], false),
                'signImage' => route('vendor_map.sign_image', ['subdomain' => $sub], false),
                'pricing' => route('role.view_admin', ['subdomain' => $sub, 'tab' => 'plan'], false),
            ],
        ];
    }

    /** Props for the designer page (VendorMapDesigner.vue). */
    public static function designerProps(Role $role, VendorMap $map, ?string $draft = null): array
    {
        $sub = $role->subdomain;

        return [
            'structure' => VendorMapStructure::toArray($map),
            'kind' => $map->kind(),
            'draft' => $draft,
            'categories' => VendorMapKinds::categoryLabels($map->kind),
            'strings' => VendorMapKinds::stringsFor($map->kind),
            'background' => $map->background_path ? route('vendor_map.private_image', ['subdomain' => $sub, 'kind' => 'background', 'id' => UrlUtils::encodeId($map->id)], false) : null,
            'backgroundPublic' => (bool) $map->background_public,
            'rtl' => $role->isRtl(),
            'urls' => [
                'save' => route('vendor_map.save_structure', ['subdomain' => $sub], false),
                'structure' => route('vendor_map.structure', ['subdomain' => $sub], false),
                'draft' => route('vendor_map.draft', ['subdomain' => $sub], false),
                'back' => route('role.view_admin', ['subdomain' => $sub, 'tab' => 'vendor-map'], false),
            ],
        ];
    }

    /** One date for the schedule's editors: every spot's state, and the claims waiting. */
    public static function dayForEditors(VendorMap $map, string $date): array
    {
        $role = $map->role;
        $state = VendorMapOccupancy::on($map, $date);
        $open = VendorMapOccupancy::isOpenOn($map, $date);

        $spots = [];
        foreach ($state as $spotId => $s) {
            $occ = $s['occupant'];
            $spots[$spotId] = [
                'state' => $s['state'],
                'dated' => $s['dated'],
                'occupant' => $occ ? self::occupantForEditors($role, $occ) : null,
            ];
        }

        $pending = VendorMapOccupancy::pending($map);
        $wanted = $pending->groupBy('spot_id')->map->count();
        $holders = self::holdersFor($map, $pending);

        return [
            'date' => $date,
            'open' => $open,
            'cancelled' => $map->cancelled_days[$date] ?? null,
            'dates' => self::dates($map, $date),
            'spots' => $spots,
            'pending' => $pending->map(fn ($p) => self::occupantForEditors($role, $p) + [
                'spot_id' => $p->spot_id,
                'spot_label' => $p->spot?->label,
                'conflict' => ($wanted[$p->spot_id] ?? 0) > 1 || self::spotIsTakenFor($holders, $p),
                'dates' => $p->isSeason() ? null : [$p->event_date],
                'name_differs' => self::nameDiffers($holders, $p),
            ])->values()->all(),
            'counts' => [
                'present' => collect($state)->where('state', 'present')->count(),
                'absent' => collect($state)->where('state', 'absent')->count(),
                'free' => collect($state)->where('state', 'free')->count(),
                'waiting' => $pending->count(),
            ],
        ];
    }

    /**
     * Waiting claims that can be approved without a decision: nobody else wants the spot, it is
     * not taken, and an "Is this you?" claim's name matches the name on the stall.
     *
     * @return Collection<int, VendorMapOccupant>
     */
    public static function conflictFreeClaims(VendorMap $map): Collection
    {
        $pending = VendorMapOccupancy::pending($map);
        $wanted = $pending->groupBy('spot_id')->map->count();
        $holders = self::holdersFor($map, $pending);

        // An "Is this you?" claim is never approved in bulk, even under the same name: the name
        // on a stall is on the vendor link for anybody to read and type.
        return $pending->filter(fn ($p) => ($wanted[$p->spot_id] ?? 0) === 1
            && ! self::spotIsTakenFor($holders, $p)
            && ! self::claimsANamedStall($holders, $p))->values();
    }

    /**
     * Everything the visitors' map draws, for one date. Never a phone, a user, a waiting claim
     * or a private picture.
     */
    public static function publicPayload(Role $role, VendorMap $map, ?string $date): array
    {
        $map->loadMissing(['areas', 'landmarks']);
        $spots = $map->spots()->orderBy('id')->get();
        $state = $date ? VendorMapOccupancy::on($map, $date) : [];
        $open = $date && VendorMapOccupancy::isOpenOn($map, $date);
        $kind = $map->kind();

        $absentees = collect($state)->where('state', 'absent')->pluck('occupant')->filter()->values()->all();
        $nextFor = $open ? VendorMapOccupancy::nextPresentDates($map, $absentees, $date) : [];

        $out = [];
        foreach ($spots as $spot) {
            $s = $state[$spot->id] ?? ['state' => 'free', 'occupant' => null];
            $occ = $s['occupant'];
            $row = [
                'id' => $spot->id,
                'label' => $spot->label,
                'area' => $spot->area_id,
                'x' => $spot->x, 'y' => $spot->y, 'w' => $spot->w, 'h' => $spot->h,
                'rotation' => $spot->rotation,
                'shape' => $spot->shape,
                'state' => $open ? $s['state'] : 'free',
            ];

            if ($open && $occ) {
                $row['vendor'] = [
                    'name' => $occ->displayName(),
                    'sells' => $occ->sells,
                    'categories' => array_values(array_filter((array) $occ->categories, fn ($c) => VendorMapKinds::isCategory($kind, $c))),
                    'photo' => VendorMapImages::publicUrl($occ->photo_thumb_path) ?: ($occ->vendor ? ($occ->vendor->profile_image_url ?: null) : null),
                    // A name only is plain text: no link, no picture of a page nobody agreed to.
                    'url' => $occ->vendor && ! $occ->vendor->is_deleted ? $occ->vendor->getGuestUrl() : null,
                ];
                if ($s['state'] === 'absent') {
                    $next = $nextFor[$occ->id] ?? null;
                    $row['vendor']['next'] = $next ? self::shortDate($next) : null;
                }
            }

            $out[] = $row;
        }

        $next = $date ? VendorMapOccupancy::nextDateAfter($map, $date) : VendorMapOccupancy::currentDate($map);

        return [
            'kind' => $kind,
            'name' => $role->translatedName(),
            'width' => (int) $map->width,
            'height' => (int) $map->height,
            'date' => $date,
            'dateLabel' => $date ? self::longDate($date) : null,
            'open' => $open,
            'cancelled' => $date ? ($map->cancelled_days[$date] ?? null) : null,
            'next' => $next,
            'nextLabel' => $next ? self::longDate($next) : null,
            'areas' => $map->areas->map(fn ($a) => ['id' => $a->id, 'name' => $a->name, 'color' => $a->color])->values()->all(),
            'landmarks' => $map->landmarks->map(fn ($l) => ['kind' => $l->kind, 'label' => $l->label, 'x' => $l->x, 'y' => $l->y, 'w' => $l->w, 'h' => $l->h, 'rotation' => $l->rotation])->values()->all(),
            'spots' => $out,
            'here' => $map->here_x !== null ? ['x' => $map->here_x, 'y' => $map->here_y] : null,
            'categories' => VendorMapKinds::categoryLabels($kind),
            'strings' => VendorMapKinds::stringsFor($kind),
        ];
    }

    public static function signData(Role $role, VendorMap $map): array
    {
        return [
            'role' => $role,
            'map' => $map,
            'payload' => self::publicPayload($role, $map, null),
            'mapUrl' => self::publicUrl($role, true),
            'qrUrl' => self::signQrUrl($role, $map),
            'strings' => VendorMapKinds::stringsFor($map->kind),
        ];
    }

    /**
     * The visitors' map address. For a printed QR it is the SUBDOMAIN address, never a custom
     * domain: a sign on a fence outlives a domain that is later changed.
     */
    public static function publicUrl(Role $role, bool $forPrint = false): string
    {
        if ($forPrint && config('app.hosted')) {
            return 'https://'.$role->subdomain.'.'._base_domain().'/map';
        }

        return route('vendor_map.public', ['subdomain' => $role->subdomain]);
    }

    /**
     * What the entrance sign's QR opens: the map, with "You are here" where the organizer put the
     * sign. The printed address under the code stays the short one.
     */
    public static function signQrUrl(Role $role, VendorMap $map): string
    {
        return self::publicUrl($role, true).($map->here_x !== null ? '?here=1' : '');
    }

    /** The vendor link, always on the app's own host: nobody is signed in on a custom domain. */
    public static function claimUrl(Role $role, VendorMap $map): string
    {
        return route('vendor_map.claim', ['subdomain' => $role->subdomain, 'token' => $map->ensureClaimToken()]);
    }

    public static function ownerFirstName(Role $role): string
    {
        $name = trim((string) $role->user?->name);
        $first = $name !== '' ? preg_split('/\s+/u', $name)[0] : '';

        return $first !== '' ? $first : (string) $role->translatedName();
    }

    public static function shortDate(string $date): string
    {
        return Carbon::parse($date)->translatedFormat('j M');
    }

    public static function longDate(string $date): string
    {
        return Carbon::parse($date)->translatedFormat('l j F');
    }

    private static function occupantForEditors(Role $role, VendorMapOccupant $o): array
    {
        return [
            'id' => UrlUtils::encodeId($o->id),
            'name' => $o->displayName(),
            'typed_name' => $o->placeholder_name,
            'placeholder' => $o->isPlaceholder(),
            'season' => $o->isSeason(),
            'sells' => $o->sells,
            'categories' => (array) $o->categories,
            'phone' => $o->phone,
            'photo' => $o->photo_path
                ? route('vendor_map.private_image', ['subdomain' => $role->subdomain, 'kind' => 'claim', 'id' => UrlUtils::encodeId($o->id)], false)
                : (VendorMapImages::publicUrl($o->photo_thumb_path) ?: ($o->vendor?->profile_image_url ?: null)),
            'url' => $o->vendor ? $o->vendor->getGuestUrl() : null,
            'status' => $o->status,
            'decided_recently' => $o->decided_at && $o->decided_at->gt(now()->subMinutes(10)) && ! $o->vendor_notified_at,
        ];
    }

    /** The next eight dates from about now, and the date on screen, for the date switcher. */
    private static function dates(VendorMap $map, string $date): array
    {
        $event = $map->event;
        if (! $event) {
            return [];
        }

        $out = [];
        $cursor = Carbon::now($event->scheduleTimezone())->subDays(7)->format('Y-m-d');
        for ($i = 0; $i < 10; $i++) {
            $cursor = $event->nextOccurrenceFrom($cursor);
            if (! $cursor) {
                break;
            }
            $out[] = ['date' => $cursor, 'label' => self::shortDate($cursor)];
            $cursor = Carbon::parse($cursor)->addDay()->format('Y-m-d');
        }

        if (! collect($out)->contains('date', $date)) {
            array_unshift($out, ['date' => $date, 'label' => self::shortDate($date)]);
        }

        return $out;
    }

    /**
     * The approved vendors on the spots claims ask for, read once (a list of forty claims asked
     * for each holder separately was eighty queries).
     */
    private static function holdersFor(VendorMap $map, Collection $pending): Collection
    {
        if ($pending->isEmpty()) {
            return collect();
        }

        return VendorMapOccupant::where('vendor_map_id', $map->id)
            ->whereIn('spot_id', $pending->pluck('spot_id')->unique()->all())
            ->whereIn('event_date', $pending->pluck('event_date')->push(VendorMapOccupant::SEASON)->unique()->all())
            ->where('kind', 'present')
            ->where('status', 'approved')
            ->get(['id', 'spot_id', 'event_date', 'vendor_role_id', 'placeholder_name']);
    }

    private static function spotIsTakenFor(Collection $holders, VendorMapOccupant $claim): bool
    {
        return $holders->contains(fn ($h) => (int) $h->spot_id === (int) $claim->spot_id
            && $h->event_date === $claim->event_date
            && $h->vendor_role_id !== null
            && (int) $h->vendor_role_id !== (int) $claim->vendor_role_id);
    }

    /** A claim on a stall the organizer put a name on ("Is this you?"). */
    private static function claimsANamedStall(Collection $holders, VendorMapOccupant $claim): bool
    {
        return $holders->contains(fn ($h) => (int) $h->spot_id === (int) $claim->spot_id
            && $h->event_date === VendorMapOccupant::SEASON
            && $h->vendor_role_id === null);
    }

    /** An "Is this you?" claim whose typed name is not the name on the stall. */
    private static function nameDiffers(Collection $holders, VendorMapOccupant $claim): bool
    {
        $holder = $holders->first(fn ($h) => (int) $h->spot_id === (int) $claim->spot_id
            && $h->event_date === VendorMapOccupant::SEASON
            && $h->vendor_role_id === null);

        if (! $holder) {
            return false;
        }

        $norm = fn ($s) => mb_strtolower(preg_replace('/[^\p{L}\p{N}]+/u', '', (string) $s));

        return $norm($holder->placeholder_name) !== $norm($claim->placeholder_name ?: $claim->vendor?->name);
    }
}
