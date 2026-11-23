<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\VendorMap;
use App\Services\VendorMaps\VendorMapImages;
use App\Services\VendorMaps\VendorMapKinds;
use App\Services\VendorMaps\VendorMapOccupancy;
use App\Services\VendorMaps\VendorMapPresenter;
use App\Services\VendorMaps\VendorMapSchematic;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * The visitors' map of a market, fair or expo: /{schedule}/map, what the QR at the entrance
 * opens and what a vendor's "Share my stall" links to (with ?s=14).
 *
 * The whole page is printed by the server, the map and the complete list of who is there, so it
 * is useful on a slow phone and to a screen reader before its script starts; the data the script
 * needs rides in the page (vendor-map/public.blade.php), never in a fetch, because a market's
 * shoppers share one mobile network and a per-visitor request would meet a rate limit.
 *
 * Shown only for a schedule its guest page would show (not deleted, published), with its map
 * switched on. What reaches the page is VendorMapPresenter::publicPayload(): never a phone, an
 * account or a claim that is waiting.
 */
class VendorMapPublicController extends Controller
{
    /**
     * The market's picture under the shoppers' map, when its owner chose to show it there (it is
     * otherwise the private tracing photo of the designer).
     */
    public function picture(string $subdomain)
    {
        $role = Role::subdomain($subdomain)->first();
        if (! $role || $role->is_deleted || ! $role->isClaimed()) {
            abort(404);
        }
        $map = VendorMap::where('role_id', $role->id)->where('enabled', true)->first();
        if (! $map || ! $map->background_public || ! $map->background_path) {
            abort(404);
        }
        // The public copy, from the public store and its CDN (made on the first ask if missing).
        $name = VendorMapImages::publishPicture($map->background_path);
        abort_unless($name !== null, 404);

        return redirect(VendorMapImages::publicUrl($name), 302, ['Cache-Control' => 'public, max-age=300']);
    }

    public function show(Request $request, string $subdomain)
    {
        $role = Role::subdomain($subdomain)->first();

        // The guest page's own rule (RoleController::viewGuest): a deleted schedule, and one
        // nobody has published, answer as an address that matches nothing.
        if (! $role || $role->is_deleted || ! $role->isClaimed()) {
            abort(404);
        }

        $map = VendorMap::where('role_id', $role->id)->where('enabled', true)->first();

        // No market map here: the address is what it was before maps existed, an event slugged
        // "map" (its short link keeps working).
        if (! $map) {
            return app(RoleController::class)->viewGuest($request, $subdomain, 'map');
        }

        $map->setRelation('role', $role);
        $this->useScheduleLanguage($request, $role);

        $event = $map->event;
        if ($event && ($event->is_draft || $event->isInternal() || $event->isPasswordProtected() || $event->is_private)) {
            // A season that is not public (a draft, members only, behind a password, private)
            // has no public dates; the map itself is still the map.
            $map->setRelation('event', null);
            $event = null;
        }
        if ($event && (int) $event->creator_role_id === (int) $role->id) {
            $event->setRelation('creatorRole', $role);
        }

        $timezone = $event ? $event->scheduleTimezone() : ($role->timezone ?: config('app.timezone'));
        $today = Carbon::now($timezone)->format('Y-m-d');
        $date = self::dateFor($map, $request->query('date'), $today);

        $payload = VendorMapPresenter::publicPayload($role, $map, $date);
        $status = $payload['open'] ? 'open' : ($payload['next'] ? 'closed' : 'none');
        // No market on the date: who is coming next time is still worth showing.
        $shown = $status === 'closed' ? VendorMapPresenter::publicPayload($role, $map, $payload['next']) : $payload;

        $kind = $map->kind();
        $rows = array_values(array_filter($shown['spots'], fn ($s) => ! empty($s['vendor']) && in_array($s['state'], ['present', 'absent'], true)));
        $presentCount = count(array_filter($rows, fn ($s) => $s['state'] === 'present'));
        $shownIsToday = $shown['date'] === $today;

        $selected = self::selectedSpot($shown['spots'], $request->query('s'));
        $showHere = $request->boolean('here') && $shown['here'];

        $t = self::strings($kind, $shownIsToday, $shown['date'] ? VendorMapPresenter::longDate($shown['date']) : null);

        $closed = null;
        if ($status !== 'open') {
            $closed = [
                'title' => self::closedTitle($kind, $payload, $today),
                'next' => $payload['nextLabel'] ? __('messages.vm_public_next', ['date' => $payload['nextLabel']]) : null,
            ];
        }

        $listTitle = match (true) {
            $status === 'closed' => __('messages.vm_public_coming_on', ['date' => $shown['dateLabel'], 'count' => number_format($presentCount)]),
            $shownIsToday => __('messages.vm_public_here_today', ['count' => number_format($presentCount)]),
            default => __('messages.vm_public_here_on', ['date' => (string) $shown['dateLabel'], 'count' => number_format($presentCount)]),
        };

        $results = [];
        for ($n = 0; $n <= count($rows); $n++) {
            $results[] = trans_choice('messages.vm_public_results', $n, ['count' => number_format($n)]);
        }

        $boot = [
            'map' => $shown,
            'status' => $status,
            'closed' => $closed,
            'dateText' => $shown['date'] ? ($shownIsToday ? __('messages.vm_public_today_date', ['date' => $shown['dateLabel']]) : $shown['dateLabel']) : null,
            'listTitle' => $listTitle,
            'emptyText' => $status === 'none' ? __('messages.vm_public_no_dates') : __('messages.vm_public_empty'),
            'selected' => $selected,
            'showHere' => (bool) $showHere,
            // On the market's own website (?embed=1): no way back to a page the frame is not on.
            'embed' => in_array($request->input('embed'), ['1', 'true'], true),
            'rtl' => is_rtl(),
            'back' => ['url' => $role->getGuestUrl(true) ?: $role->getGuestUrl(), 'label' => $role->translatedName()],
            't' => $t,
            'results' => $results,
            // The market as blocks of equal stalls per area (VendorMapSchematic), which the page
            // draws instead of the plan itself.
            'schematic' => VendorMapSchematic::build($map, $shown),
        ];

        $name = $role->translatedName();
        $share = VendorMapImages::publicUrl($map->share_image_path ?? null);
        $roleImage = $share ? null : $role->shareImage();

        return view('vendor-map.public', [
            'role' => $role,
            'map' => $map,
            'boot' => $boot,
            'groups' => self::groups($shown, $rows),
            'fonts' => array_values(array_filter([$role->font_family])),
            'pageMeta' => [
                'title' => __('messages.vm_public_title', ['name' => $name]),
                'description' => __('messages.vm_public_description', ['name' => $name]),
                'canonical' => VendorMapPresenter::publicUrl($role),
                'image' => $share ?: ($roleImage['url'] ?? null),
                'image_width' => $share ? 1200 : ($roleImage['width'] ?? null),
                'image_height' => $share ? 630 : ($roleImage['height'] ?? null),
            ],
        ]);
    }

    /**
     * The date the page shows: ?date= when it is a real date, else a market day called off
     * between today and the next one (so a rained-out Friday says so, instead of quietly showing
     * the Friday after), else today or the next date the market is on.
     */
    public static function dateFor(VendorMap $map, $asked, string $today): ?string
    {
        if (is_string($asked) && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $asked, $m) && checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
            return $asked;
        }

        $current = VendorMapOccupancy::currentDate($map);

        $calledOff = collect(array_keys((array) ($map->cancelled_days ?? [])))
            ->filter(fn ($d) => is_string($d) && $d >= $today && ($current === null || $d < $current))
            ->sort()
            ->first();

        return $calledOff ?? $current;
    }

    /**
     * ?s= names the stall: its number as painted ("14", what "Share my stall" sends), or an
     * encoded spot id. Null when it names none.
     */
    public static function selectedSpot(array $spots, $asked): ?int
    {
        if (! is_string($asked) || trim($asked) === '') {
            return null;
        }

        $asked = trim($asked);
        foreach ($spots as $s) {
            if (mb_strtolower((string) $s['label']) === mb_strtolower($asked)) {
                return (int) $s['id'];
            }
        }

        $id = UrlUtils::decodeId($asked);
        foreach ($spots as $s) {
            if ($id && (int) $s['id'] === (int) $id) {
                return (int) $s['id'];
            }
        }

        return null;
    }

    /** The stalls with somebody on them, under their areas in the map's order, by number. */
    public static function groups(array $payload, array $rows): array
    {
        usort($rows, fn ($a, $b) => strnatcasecmp((string) $a['label'], (string) $b['label']));

        $groups = [];
        foreach ($payload['areas'] as $area) {
            $groups[$area['id']] = ['key' => 'a'.$area['id'], 'name' => $area['name'], 'color' => $area['color'], 'rows' => []];
        }
        $rest = ['key' => 'rest', 'name' => __('messages.vm_public_other_area'), 'color' => null, 'rows' => []];

        foreach ($rows as $row) {
            if ($row['area'] !== null && isset($groups[$row['area']])) {
                $groups[$row['area']]['rows'][] = $row;
            } else {
                $rest['rows'][] = $row;
            }
        }

        $groups['rest'] = $rest;

        return array_values(array_filter($groups, fn ($g) => $g['rows'] !== []));
    }

    private static function closedTitle(string $kind, array $payload, string $today): string
    {
        $date = $payload['date'];
        $what = ! $date || $date === $today
            ? VendorMapKinds::say($kind, 'none_today')
            : __('messages.vm_public_closed_on', ['date' => (string) $payload['dateLabel']]);

        $reason = $payload['cancelled'] ?? null;
        if (in_array($reason, ['weather', 'holiday'], true)) {
            return __('messages.vm_public_with_reason', ['what' => $what, 'why' => __('messages.vm_public_reason_'.$reason)]);
        }

        return $what;
    }

    /** Every word the page's script says, translated here. */
    public static function strings(string $kind, bool $today, ?string $dateLabel): array
    {
        $say = VendorMapKinds::stringsFor($kind);

        return [
            'spot' => VendorMapKinds::say($kind, 'spot', ['number' => ':number']),
            'vendors' => $say['people'],
            'search' => __('messages.vm_public_search'),
            'searchLabel' => __('messages.vm_public_search_label'),
            'categories' => __('messages.vm_public_categories'),
            'all' => __('messages.vm_public_all'),
            'clear' => __('messages.vm_public_clear'),
            'clearSearch' => __('messages.vm_public_clear_search'),
            'noResults' => __('messages.vm_public_no_results'),
            'close' => __('messages.close'),
            'visit' => __('messages.vm_public_visit'),
            'map' => __('messages.vm_public_map_label'),
            'resize' => __('messages.vm_public_resize'),
            'otherArea' => __('messages.vm_public_other_area'),
            'here' => __('messages.vm_public_here'),
            'zoomIn' => __('messages.venue_map_zoom_in'),
            'zoomOut' => __('messages.venue_map_zoom_out'),
            'fit' => __('messages.vm_public_fit'),
            'allAreas' => __('messages.vm_public_all_areas'),
            // "5 here today" only on the day itself; another day's map says who is coming.
            'areaHere' => $today ? __('messages.vm_public_area_here') : __('messages.vm_public_area_coming'),
            'tapArea' => __('messages.vm_public_tap_area'),
            'absent' => $today ? __('messages.vm_public_absent_today') : __('messages.vm_public_absent_on'),
            'absentNext' => $today ? __('messages.vm_public_absent_today_next', ['date' => ':date']) : __('messages.vm_public_absent_on_next', ['date' => ':date']),
            'landmarks' => [
                'entrance' => __('messages.vm_landmark_entrance'),
                'restroom' => __('messages.vm_landmark_restroom'),
                'parking' => __('messages.vm_landmark_parking'),
                'stage' => __('messages.vm_landmark_stage'),
            ],
        ];
    }

    /** The language the schedule's guest page would be in (RoleController::viewGuest), without its redirects. */
    private function useScheduleLanguage(Request $request, Role $role): void
    {
        $lang = is_string($request->lang) ? $request->lang : null;

        if ($lang && is_valid_language_code($lang)) {
            app()->setLocale($lang);
            if ($lang === $role->translation_language_code && $lang !== $role->language_code) {
                session()->put('translate', true);
            } else {
                session()->forget('translate');
            }
        } elseif (session()->has('translate') && $role->translation_language_code && is_valid_language_code($role->translation_language_code)) {
            app()->setLocale($role->translation_language_code);
        } elseif (is_valid_language_code($role->language_code)) {
            app()->setLocale($role->language_code);
        }
    }
}
