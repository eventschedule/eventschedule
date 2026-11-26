<?php

namespace App\Http\Controllers;

use App\Exceptions\EventCreationLimitException;
use App\Models\Event;
use App\Models\Role;
use App\Models\VendorMap;
use App\Models\VendorMapDraft;
use App\Models\VendorMapOccupant;
use App\Models\VendorMapSpot;
use App\Services\UsageTrackingService;
use App\Services\VendorMaps\VendorMapAi;
use App\Services\VendorMaps\VendorMapClaims;
use App\Services\VendorMaps\VendorMapImages;
use App\Services\VendorMaps\VendorMapKinds;
use App\Services\VendorMaps\VendorMapOccupancy;
use App\Services\VendorMaps\VendorMapPng;
use App\Services\VendorMaps\VendorMapPresenter;
use App\Services\VendorMaps\VendorMapStructure;
use App\Utils\SlugPatternUtils;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * The vendor map, as its schedule's editors see it: setting it up, the designer, the AI draft,
 * and the market day (who is coming, the claims waiting, a move, a cancelled day).
 *
 * Every action is editor-only and scoped to the schedule in the address (mapFor()). The vendor
 * link and the AI are Pro; drawing the map and adding vendors by hand are not.
 */
class VendorMapController extends Controller
{
    /** Create the map (and, from the market path, the market's weekly event). */
    public function setup(Request $request, string $subdomain)
    {
        $role = $this->roleFor($subdomain);

        $data = $request->validate([
            'kind' => ['required', Rule::in(VendorMapKinds::KINDS)],
            'event_id' => ['nullable', 'string'],
            // "When is the market?" when there is no event to choose yet.
            'name' => ['nullable', 'string', 'max:200'],
            'days' => ['nullable', 'array'],
            'days.*' => ['integer', 'between:0,6'],
            'starts' => ['nullable', 'date_format:H:i'],
            'ends' => ['nullable', 'date_format:H:i'],
            'first_date' => ['nullable', 'date_format:Y-m-d'],
            'last_date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        // Everything that can refuse the setup is asked before the map row exists, so a refusal
        // leaves nothing behind.
        $event = ! empty($data['event_id']) ? $this->ownEvent($role, $data['event_id']) : null;
        $makesEvent = ! $event && (! empty($data['days']) || ! empty($data['first_date']));

        // The market's event is made here and not through EventRepo::saveEvent(), so the daily
        // cap that one asks is asked here too.
        if ($makesEvent && ! $role->canCreateEvent($request->user())) {
            throw new EventCreationLimitException;
        }

        // createOrFirst underneath: two setups at once (a double tap) meet on one row instead of
        // the second ending on the unique role_id.
        $map = VendorMap::firstOrCreate(['role_id' => $role->id], ['kind' => $data['kind']]);

        DB::transaction(function () use ($map, $role, $data, $event, $makesEvent) {
            $map = VendorMap::whereKey($map->id)->lockForUpdate()->first();

            // A map that already serves an event keeps it: the setup card is shown only while
            // there is no map, so this is the second of two taps, or a tab opened before the first.
            if ($makesEvent && ! $map->event_id) {
                $event = $this->createMarketEvent($role, $data);
            }

            $map->fill(['kind' => $data['kind'], 'enabled' => true]);
            if ($event) {
                $map->event_id = $event->id;
            }
            $map->save();
        });

        return $request->wantsJson()
            ? response()->json(['ok' => true, 'props' => VendorMapPresenter::adminProps($role->fresh())])
            : redirect()->route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'vendor-map']);
    }

    /** The map's kind, its event, on or off. */
    public function settings(Request $request, string $subdomain)
    {
        [$role, $map] = $this->mapFor($subdomain);

        $data = $request->validate([
            'kind' => ['sometimes', Rule::in(VendorMapKinds::KINDS)],
            'event_id' => ['sometimes', 'nullable', 'string'],
            'enabled' => ['sometimes', 'boolean'],
        ]);

        if (array_key_exists('event_id', $data)) {
            $map->event_id = $data['event_id'] ? $this->ownEvent($role, $data['event_id'])->id : null;
        }
        if (isset($data['kind'])) {
            $map->kind = $data['kind'];
        }
        if (isset($data['enabled'])) {
            $map->enabled = (bool) $data['enabled'];
        }
        $map->save();

        return response()->json(['ok' => true, 'props' => VendorMapPresenter::adminProps($role->fresh())]);
    }

    /** The designer, a wide page of its own. */
    public function design(string $subdomain)
    {
        [$role, $map] = $this->mapFor($subdomain);

        return view('role.vendor-map-designer', [
            'role' => $role,
            'subdomain' => $role->subdomain,
            'props' => VendorMapPresenter::designerProps($role, $map, request('draft')),
        ]);
    }

    public function structure(string $subdomain)
    {
        [, $map] = $this->mapFor($subdomain);

        return response()->json(VendorMapStructure::toArray($map));
    }

    public function saveStructure(Request $request, string $subdomain)
    {
        [$role, $map] = $this->mapFor($subdomain);

        $result = VendorMapStructure::save($map, $request->all());

        // The traced photo from an AI draft, kept as the designer's background (privately).
        if ($request->filled('background_from_draft')) {
            $this->keepBackground($map, (string) $request->input('background_from_draft'));
        } elseif ($request->boolean('clear_background')) {
            if ($map->background_path) {
                VendorMapImages::deletePublic(VendorMapImages::pictureName($map->background_path));
            }
            VendorMapImages::deletePrivate($map->background_path);
            $map->forceFill(['background_path' => null, 'background_public' => false])->save();
        }

        // Under the shoppers' map too: the owner's choice, and only for a picture there is. The
        // public copy is made when it is switched on and taken down when it is switched off.
        if ($request->has('background_public')) {
            $map->refresh();
            $public = $request->boolean('background_public') && $map->background_path !== null
                && VendorMapImages::publishPicture($map->background_path) !== null;
            if (! $public && $map->background_path) {
                VendorMapImages::deletePublic(VendorMapImages::pictureName($map->background_path));
            }
            $map->forceFill(['background_public' => $public])->save();
        }

        VendorMapPng::refreshShareImage($map->fresh());

        return response()->json(['ok' => true] + $result + ['structure' => VendorMapStructure::toArray($map->fresh())]);
    }

    /** One date of the market: who is on which spot, and the claims waiting. */
    public function day(Request $request, string $subdomain)
    {
        [$role, $map] = $this->mapFor($subdomain);

        $date = (string) $request->query('date', '');
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = VendorMapOccupancy::currentDate($map) ?? now()->toDateString();
        }

        return response()->json(VendorMapPresenter::dayForEditors($map, $date));
    }

    public function uploadPhotos(Request $request, string $subdomain)
    {
        [$role, $map] = $this->mapFor($subdomain);

        if (! VendorMapAi::available($map)) {
            abort(403);
        }

        $request->validate(['photos' => ['required', 'array', 'max:'.(VendorMapAi::MAX_PHOTOS + 1)], 'photos.*' => ['file', 'max:8192'], 'draft' => ['nullable', 'string'],
            'area' => ['nullable', 'integer', 'min:0', 'max:'.(VendorMapAi::MAX_PHOTO_AREAS - 1)], 'area_name' => ['nullable', 'string', 'max:80']]);

        // The photos come a few at a time; a later batch names the draft the first one made,
        // which must be this map's and not yet read.
        $append = null;
        if ($request->filled('draft')) {
            $append = $this->draftFor($map, (string) $request->input('draft'));
            if (($append->status ?? 'uploaded') !== 'uploaded') {
                abort(409);
            }
        }

        $draft = VendorMapAi::upload($map, $request->user(), (array) $request->file('photos'), $append,
            $request->filled('area') ? (int) $request->input('area') : null, $request->input('area_name'));

        return response()->json(['draft' => UrlUtils::encodeId($draft->id), 'images' => $this->draftImageUrls($role, $draft)]);
    }

    public function parseDraft(Request $request, string $subdomain, string $draft)
    {
        [$role, $map] = $this->mapFor($subdomain);
        $draft = $this->draftFor($map, $draft);

        if (! VendorMapAi::available($map)) {
            abort(403);
        }

        // A parse can take a minute; Cloudflare's limit is 100 seconds.
        @set_time_limit(120);

        $result = VendorMapAi::parse($draft, $request->input('notes'));

        if (! $result) {
            return response()->json(['ok' => false, 'message' => __('messages.vm_ai_failed')], 422);
        }

        // Photographed area by area: one area is read per request, and the page asks again.
        if (! empty($result['pending'])) {
            return response()->json(['ok' => true, 'pending' => true, 'busy' => ! empty($result['busy']), 'done' => $result['done'], 'total' => $result['total'], 'next' => $result['next']]);
        }

        return response()->json(['ok' => true, 'result' => $result]);
    }

    /** The newest draft from the last day, so a tab closed during the wait comes back to it. */
    public function latestDraft(string $subdomain)
    {
        [$role, $map] = $this->mapFor($subdomain);

        $draft = $map->drafts()->where('created_at', '>=', now()->subDay())->latest('id')->first();

        if (! $draft) {
            return response()->json(['draft' => null]);
        }

        return response()->json([
            'draft' => UrlUtils::encodeId($draft->id),
            'status' => $draft->status,
            'result' => $draft->result,
            'images' => $this->draftImageUrls($role, $draft),
        ]);
    }

    /** A private picture, to this schedule's editors only. */
    public function privateImage(string $subdomain, string $kind, string $id, ?int $index = null)
    {
        [$role, $map] = $this->mapFor($subdomain);

        $path = match ($kind) {
            'draft' => ($this->draftFor($map, $id)->images[$index ?? 0] ?? null),
            'background' => $map->background_path,
            'claim' => VendorMapOccupant::where('vendor_map_id', $map->id)->findOrFail(UrlUtils::decodeId($id))->photo_path,
            default => abort(404),
        };

        $bytes = VendorMapImages::readPrivate($path);
        abort_if($bytes === null, 404);

        return response($bytes, 200, [
            'Content-Type' => VendorMapImages::mimeFor($path),
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /** approve | decline | undo | absent | present | remove, on one row. */
    public function decide(Request $request, string $subdomain, string $occupant, string $action)
    {
        [$role, $map] = $this->mapFor($subdomain);
        $row = VendorMapOccupant::where('vendor_map_id', $map->id)->findOrFail(UrlUtils::decodeId($occupant));

        match ($action) {
            'approve' => VendorMapClaims::approve($row),
            'decline' => VendorMapClaims::decline($row, $request->input('note')),
            'undo' => VendorMapClaims::undo($row) ?: throw ValidationException::withMessages(['undo' => __('messages.vm_cannot_undo')]),
            'absent' => VendorMapClaims::markAbsent($map, $row, [(string) $request->input('date')], $request->user()),
            'present' => VendorMapClaims::clearAbsent($map, $row, (string) $request->input('date')),
            'remove' => $this->removeOccupant($row),
        };

        return response()->json(['ok' => true] + VendorMapPresenter::dayForEditors($map->fresh(), $this->dateFrom($request, $map)));
    }

    /** "Approve all without conflicts": every waiting claim on a spot nobody else wants. */
    public function approveAll(Request $request, string $subdomain)
    {
        [$role, $map] = $this->mapFor($subdomain);

        $ids = [];
        foreach (VendorMapPresenter::conflictFreeClaims($map) as $claim) {
            try {
                VendorMapClaims::approve($claim);
                $ids[] = UrlUtils::encodeId($claim->id);
            } catch (ValidationException) {
                // Taken meanwhile: it stays waiting, to be answered by hand.
            }
        }

        return response()->json(['ok' => true, 'approved' => $ids] + VendorMapPresenter::dayForEditors($map->fresh(), $this->dateFrom($request, $map)));
    }

    public function move(Request $request, string $subdomain)
    {
        [$role, $map] = $this->mapFor($subdomain);

        $data = $request->validate([
            'occupant' => ['required', 'string'],
            'spot' => ['required', 'integer'],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $row = VendorMapOccupant::where('vendor_map_id', $map->id)->findOrFail(UrlUtils::decodeId($data['occupant']));
        $to = VendorMapSpot::where('vendor_map_id', $map->id)->whereNull('retired_at')->findOrFail($data['spot']);

        if ($row->status === 'pending') {
            // A request not yet answered simply asks for another stall now.
            $row->forceFill(['spot_id' => $to->id])->save();
        } else {
            VendorMapClaims::move($map, $row, $to, $data['date'] ?? null);
        }

        return response()->json(['ok' => true] + VendorMapPresenter::dayForEditors($map->fresh(), $this->dateFrom($request, $map)));
    }

    public function addForDay(Request $request, string $subdomain)
    {
        [$role, $map] = $this->mapFor($subdomain);

        $data = $request->validate([
            'spot' => ['required', 'integer'],
            'date' => ['required', 'date_format:Y-m-d'],
            'name' => ['required', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:40'],
        ]);

        $spot = VendorMapSpot::where('vendor_map_id', $map->id)->whereNull('retired_at')->findOrFail($data['spot']);
        VendorMapClaims::addForDay($map, $spot, $data['date'], $data['name'], $data['category'] ?? null);

        return response()->json(['ok' => true] + VendorMapPresenter::dayForEditors($map->fresh(), $data['date']));
    }

    /** "Cancel this market day": the date leaves the season (an excluded date). */
    public function cancelDay(Request $request, string $subdomain)
    {
        [$role, $map] = $this->mapFor($subdomain);

        $data = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'reason' => ['nullable', Rule::in(['weather', 'holiday', 'other'])],
        ]);

        $event = $map->event;
        abort_unless($event && $request->user()->runsEvent($event), 403);

        if (! VendorMapOccupancy::isOpenOn($map, $data['date'])) {
            throw ValidationException::withMessages(['date' => __('messages.vm_not_open_that_day')]);
        }

        if ($event->days_of_week) {
            $excluded = (array) ($event->recurring_exclude_dates ?? []);
            $excluded[] = $data['date'];
            $event->recurring_exclude_dates = array_values(array_unique($excluded));
            $event->save();
        }
        // An event that is not weekly (one day, or an expo over several) is called off on the map
        // alone (VendorMapOccupancy::isOpenOn reads it): the event's own Cancel, with its mail to
        // ticket buyers, stays the organizer's, and a day called off here can be opened again.

        // Why, for the visitors' "No market today (rain)". Kept on the map: the event's
        // excluded dates are a plain list.
        $cancelled = (array) ($map->cancelled_days ?? []);
        $cancelled[$data['date']] = $data['reason'] ?? 'other';
        ksort($cancelled);
        $map->forceFill(['cancelled_days' => array_slice($cancelled, -60, null, true)])->save();

        return response()->json(['ok' => true] + VendorMapPresenter::dayForEditors($map->fresh(), $data['date']));
    }

    /** A day called off by mistake, or the rain stopped: the market is on that day again. */
    public function reopenDay(Request $request, string $subdomain)
    {
        [$role, $map] = $this->mapFor($subdomain);
        $data = $request->validate(['date' => ['required', 'date_format:Y-m-d']]);

        $event = $map->event;
        abort_unless($event && $request->user()->runsEvent($event), 403);

        if ($event->days_of_week) {
            $event->recurring_exclude_dates = array_values(array_diff((array) ($event->recurring_exclude_dates ?? []), [$data['date']]));
            $event->save();
        }

        $cancelled = (array) ($map->cancelled_days ?? []);
        unset($cancelled[$data['date']]);
        $map->forceFill(['cancelled_days' => $cancelled])->save();

        return response()->json(['ok' => true] + VendorMapPresenter::dayForEditors($map->fresh(), $data['date']));
    }

    /** The vendor link: made, rotated, opened or closed. */
    public function link(Request $request, string $subdomain)
    {
        [$role, $map] = $this->mapFor($subdomain);

        if (! $role->isPro()) {
            abort(403);
        }

        $data = $request->validate([
            'action' => ['required', Rule::in(['make', 'rotate', 'open', 'close'])],
        ]);

        match ($data['action']) {
            'make' => $map->ensureClaimToken(),
            'rotate' => $map->rotateClaimToken(),
            'open' => $map->forceFill(['claims_open' => true])->save(),
            'close' => $map->forceFill(['claims_open' => false])->save(),
        };

        return response()->json(['ok' => true, 'props' => VendorMapPresenter::adminProps($role->fresh())]);
    }

    /** The printable entrance sign. */
    public function sign(string $subdomain)
    {
        [$role, $map] = $this->mapFor($subdomain);

        return view('vendor-map.sign', VendorMapPresenter::signData($role, $map));
    }

    /** The entrance sign as a 300 dpi PNG, for the print shop. */
    public function signImage(string $subdomain)
    {
        [$role, $map] = $this->mapFor($subdomain);

        // Without GD or FreeType (some selfhost PHPs) there is no PNG, and the sign page does not
        // offer one; the printable page itself needs neither.
        $printable = redirect()->route('vendor_map.sign', ['subdomain' => $role->subdomain]);
        if (! VendorMapPng::available()) {
            return $printable;
        }

        try {
            $png = VendorMapPng::sign($role, $map);
        } catch (\Throwable $e) {
            report($e);

            return $printable;
        }

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="'.($role->subdomain ?: 'map').'-sign.png"',
        ]);
    }

    /** @return array{0: Role, 1: VendorMap} */
    private function mapFor(string $subdomain): array
    {
        $role = $this->roleFor($subdomain);
        $map = VendorMap::where('role_id', $role->id)->first();
        abort_unless($map, 404);
        $map->setRelation('role', $role);

        return [$role, $map];
    }

    private function roleFor(string $subdomain): Role
    {
        $user = auth()->user();
        abort_unless($user && $user->isEditor($subdomain), 403);

        $role = Role::subdomain($subdomain)->where('is_deleted', false)->firstOrFail();
        abort_unless($role->isVenue() || $role->isCurator(), 404);

        return $role;
    }

    /** An event the schedule itself runs, never one it merely lists. */
    private function ownEvent(Role $role, string $hash): Event
    {
        $event = $role->ownEvents()->where('events.id', UrlUtils::decodeId($hash))->first();

        if (! $event || ! auth()->user()->runsEvent($event)) {
            throw ValidationException::withMessages(['event_id' => __('messages.vm_event_not_yours')]);
        }

        return $event;
    }

    private function draftFor(VendorMap $map, string $hash): VendorMapDraft
    {
        return VendorMapDraft::where('vendor_map_id', $map->id)->findOrFail(UrlUtils::decodeId($hash));
    }

    private function draftImageUrls(Role $role, VendorMapDraft $draft): array
    {
        $out = [];
        foreach ((array) $draft->images as $i => $path) {
            $out[] = [
                'url' => route('vendor_map.private_image', ['subdomain' => $role->subdomain, 'kind' => 'draft', 'id' => UrlUtils::encodeId($draft->id), 'index' => $i], false),
                'pdf' => str_ends_with($path, '.pdf'),
            ];
        }

        return $out;
    }

    /** Copy a draft's picture to be the designer's background; the draft is pruned in a day. */
    private function keepBackground(VendorMap $map, string $value): void
    {
        [$hash, $index] = array_pad(explode(':', $value, 2), 2, 0);
        // The map is already saved; a draft pruned while the designer was open just means no
        // photo to keep, never a failed save.
        $draft = VendorMapDraft::where('vendor_map_id', $map->id)->find(UrlUtils::decodeId($hash));
        if (! $draft) {
            return;
        }
        $path = $draft->images[(int) $index] ?? null;

        if (! $path || str_ends_with($path, '.pdf')) {
            return;
        }

        $bytes = VendorMapImages::readPrivate($path);
        if ($bytes === null) {
            return;
        }

        $old = $map->background_path;
        // A new picture is not public until its owner says so for this one.
        if ($map->background_path) {
            VendorMapImages::deletePublic(VendorMapImages::pictureName($map->background_path));
        }
        $map->background_public = false;
        $map->forceFill(['background_path' => VendorMapImages::storePrivate($bytes, pathinfo($path, PATHINFO_EXTENSION))])->save();
        VendorMapImages::deletePrivate($old);
    }

    private function removeOccupant(VendorMapOccupant $row): void
    {
        $row->delete(); // its photos go with it (VendorMapOccupant::booted)
    }

    private function dateFrom(Request $request, VendorMap $map): string
    {
        $date = (string) $request->input('view_date', $request->input('date', ''));

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : (VendorMapOccupancy::currentDate($map) ?? now()->toDateString());
    }

    /**
     * The market's weekly (or one-off) event, from "When is the market?". Made directly, the way
     * an import makes one, owned by this schedule and on it as accepted.
     */
    private function createMarketEvent(Role $role, array $data): Event
    {
        $tz = $role->timezone ?: config('app.timezone');
        $days = array_values(array_unique(array_map('intval', (array) ($data['days'] ?? []))));
        $starts = $data['starts'] ?? '08:00';
        $ends = $data['ends'] ?? '14:00';

        $first = isset($data['first_date']) ? Carbon::parse($data['first_date'], $tz) : Carbon::now($tz)->startOfDay();
        if ($days !== []) {
            // The first of the chosen weekdays from that day on.
            while (! in_array($first->dayOfWeek, $days, true)) {
                $first->addDay();
            }
        }

        $startAt = Carbon::parse($first->format('Y-m-d').' '.$starts, $tz);
        $endAt = Carbon::parse($first->format('Y-m-d').' '.$ends, $tz);
        if ($endAt->lte($startAt)) {
            $endAt->addDay();
        }

        $name = trim((string) ($data['name'] ?? '')) ?: $role->translatedName();

        return DB::transaction(function () use ($role, $days, $startAt, $endAt, $name, $data, $tz) {
            $event = new Event;
            $event->user_id = auth()->id();
            $event->creator_role_id = $role->id;
            $event->name = mb_substr($name, 0, 200);
            $event->starts_at = $startAt->copy()->utc()->format('Y-m-d H:i:s');
            $event->duration = round($startAt->diffInMinutes($endAt) / 60, 2);
            $event->slug = SlugPatternUtils::generateSlug(null, $event->name, null, null, $role);

            if ($days !== []) {
                $mask = '0000000';
                foreach ($days as $d) {
                    $mask[$d] = '1';
                }
                $event->days_of_week = $mask;
                $event->recurring_frequency = 'weekly';
                if (! empty($data['last_date'])) {
                    $event->recurring_end_type = 'on_date';
                    $event->recurring_end_value = $data['last_date'];
                }
            } elseif (! empty($data['last_date'])) {
                // A one-off event over several days (an expo): its length covers them all.
                $last = Carbon::parse($data['last_date'].' '.$endAt->format('H:i'), $tz);
                $event->duration = max($event->duration, round($startAt->diffInMinutes($last) / 60, 2));
            }

            $event->save();
            $event->roles()->attach($role->id, ['is_accepted' => true]);

            // Counted toward the daily cap setup() asked, as EventRepo::saveEvent() counts its own.
            UsageTrackingService::track(UsageTrackingService::EVENT_CREATE, $role->id);

            return $event;
        });
    }
}
