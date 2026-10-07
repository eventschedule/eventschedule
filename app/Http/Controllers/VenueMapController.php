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

        return response()->json(VenueMap::status($role) + ['on' => VenueMap::enabledFor($role)]);
    }
}
