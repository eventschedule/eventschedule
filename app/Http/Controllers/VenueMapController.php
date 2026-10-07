<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\ResolvesGuestLanguage;
use App\Models\Role;
use App\Services\VenueMap;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The venue map's two data endpoints: what a visitor's open map is sent, and what the schedule's
 * owner is told about how its venues are placed. Everything they answer comes from VenueMap.
 */
class VenueMapController extends Controller
{
    use ResolvesGuestLanguage;

    /**
     * The venues on a schedule's map, for the guest page's component.
     *
     * One answer for everything that is not a map: an unknown, deleted or unclaimed schedule, a
     * venue schedule, a map that is switched off or has not had its first pass. The same shape
     * RoleController::calendarEvents() gives a schedule it will not show.
     */
    public function show(Request $request, $subdomain): JsonResponse
    {
        $role = Role::subdomain($subdomain)->with('groups')->first();

        if (! $role || $role->is_deleted || ! $role->isClaimed() || ! VenueMap::enabledFor($role) || ! VenueMap::ready($role)) {
            return response()->json(['venues' => []]);
        }

        $lang = $this->resolveGuestDisplayLanguage($request, $role);

        // The sub-schedule the page is on, by the slug its address carries.
        $slug = is_string($request->query('schedule')) ? $request->query('schedule') : '';
        $group = $slug !== '' ? $role->groups->first(fn ($group) => $group->slug === $slug) : null;

        return response()->json(['venues' => VenueMap::payload($role, $group, $lang)]);
    }

    /** How the schedule's venues are placed, for the people who may edit the schedule. */
    public function status(Request $request, $subdomain): JsonResponse
    {
        if (! auth()->user()?->isEditor($subdomain)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! VenueMap::offeredTo($role)) {
            return response()->json(['error' => __('messages.not_authorized')], 404);
        }

        return response()->json(VenueMap::status($role, auth()->user()) + ['on' => VenueMap::enabledFor($role)]);
    }

    /**
     * What the owner decides about one venue on this schedule's map: take it off (hidden), put it
     * back, or say where its pin goes (lat and lon together). Saved at once, apart from the
     * schedule form's own Save: a mark is not a field of the schedule.
     *
     * Only for a venue that is on this schedule's map today. The id in the address is encoded.
     */
    public function mark(Request $request, $subdomain, $venue): JsonResponse
    {
        [$role, $venueId] = $this->markTarget($subdomain, $venue);

        // No `sometimes` on the pair: with it, half a position (a lat and no lon) passed, because
        // the missing half was never looked at, and the request was answered 200 having done nothing.
        $data = $request->validate([
            'hidden' => ['sometimes', 'boolean'],
            'lat' => ['required_with:lon', 'numeric', 'between:-90,90'],
            'lon' => ['required_with:lat', 'numeric', 'between:-180,180'],
        ]);

        // Nothing asked for is not a success either.
        abort_if(! array_key_exists('hidden', $data) && ! array_key_exists('lat', $data), 422);

        $mark = \App\Models\VenueMapMark::firstOrNew(['role_id' => $role->id, 'venue_id' => $venueId]);

        if (array_key_exists('hidden', $data)) {
            $mark->hidden = (bool) $data['hidden'];
        }

        if (array_key_exists('lat', $data) && array_key_exists('lon', $data)) {
            // Null Island is what a form sends when it has nothing: never a place a venue is.
            abort_if((float) $data['lat'] == 0.0 && (float) $data['lon'] == 0.0, 422);

            $mark->lat = round((float) $data['lat'], 7);
            $mark->lon = round((float) $data['lon'], 7);
        }

        $this->keepOrDrop($mark);

        return $this->markAnswer($role, $venueId);
    }

    /** Back to the position the address search found: the pin placed by hand is forgotten. */
    public function unmark(Request $request, $subdomain, $venue): JsonResponse
    {
        [$role, $venueId] = $this->markTarget($subdomain, $venue);

        $mark = \App\Models\VenueMapMark::where('role_id', $role->id)->where('venue_id', $venueId)->first();

        if ($mark) {
            $mark->lat = null;
            $mark->lon = null;
            $this->keepOrDrop($mark);
        }

        return $this->markAnswer($role, $venueId);
    }

    /** A mark that says nothing (not hidden, no position) is no mark: the row goes. */
    private function keepOrDrop(\App\Models\VenueMapMark $mark): void
    {
        if (! $mark->hidden && $mark->lat === null) {
            $mark->exists ? $mark->delete() : null;

            return;
        }

        $mark->save();
    }

    /** @return array{0: Role, 1: int} */
    private function markTarget($subdomain, $venue): array
    {
        abort_unless((bool) auth()->user()?->isEditor($subdomain), 403);

        $role = Role::subdomain($subdomain)->firstOrFail();
        abort_unless(VenueMap::offeredTo($role), 404);

        $venueId = (int) \App\Utils\UrlUtils::decodeId($venue);

        // A venue of THIS schedule's events, as the map itself decides it: nobody can mark a
        // venue the map would not hold, and so nobody can probe for one either.
        abort_unless(VenueMap::venues($role, null, false)->contains(fn (array $v) => $v['venue']->id === $venueId), 404);

        return [$role, $venueId];
    }

    private function markAnswer(Role $role, int $venueId): JsonResponse
    {
        $status = VenueMap::status($role, auth()->user());
        $encoded = \App\Utils\UrlUtils::encodeId($venueId);

        // A pin placed by hand can be what a map was waiting for.
        VenueMap::refreshReady($role);

        return response()->json([
            'venue' => collect($status['venues'])->firstWhere('id', $encoded),
            'placed' => $status['placed'],
            'total' => $status['total'],
        ]);
    }
}
