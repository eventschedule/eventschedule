<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ScheduleActivity;
use App\Services\ScheduleRealtime;
use App\Utils\UrlUtils;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The polls behind a schedule owner's live view of their own guest pages.
 *
 * The view itself is the Realtime tab of /analytics (AnalyticsController::index(),
 * analytics/_realtime). Its traffic refreshes from data() and its Activity rail and door card from
 * activity(); the dashboard's Realtime tile refreshes from summary(). Not /admin/realtime with a filter: that page is the operator's, about the whole
 * install and about people; these answer for the viewer's schedules only and never say who
 * anyone is. All of that is in App\Services\ScheduleRealtime and ScheduleActivity; this class
 * decides who may ask and for which schedule.
 *
 * Where the install does not offer the view (Realtime off, the owner switch off, the shared demo
 * account, or nobody's schedule to show) every route is a 404, the same as a page that does not
 * exist, and neither the Analytics page nor the dashboard carries a way in.
 */
class RealtimeController extends Controller
{
    /** The tab's poll. The same answer the tab was rendered with, as JSON. */
    public function data(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless(ScheduleRealtime::available($user), 404);

        return response()->json(ScheduleActivity::trafficPayload($request, $user, $this->selectedSchedule($request, $user)));
    }

    /**
     * The Activity rail and the door card: what people did on the viewer's schedules in the last
     * day, from the owner's own records and never from the traffic table. Asked for once a minute
     * and when a sale lands, on a route of its own so that a traffic poll never carries it.
     */
    public function activity(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless(ScheduleRealtime::available($user), 404);

        return response()->json(ScheduleActivity::railPayload($request, $user, $this->selectedSchedule($request, $user)));
    }

    /** The dashboard's Realtime tile. */
    public function summary(Request $request): JsonResponse
    {
        $summary = ScheduleRealtime::summary($request->user(), ScheduleRealtime::salt($request));
        abort_if($summary === null, 404);

        // Keyed by encoded id on the way out: a raw schedule id is never printed for a browser.
        $summary['by_schedule'] = collect($summary['by_schedule'])
            ->mapWithKeys(fn ($count, $id) => [UrlUtils::encodeId($id) => $count])->all();

        return response()->json($summary);
    }

    /**
     * The schedule the poll is narrowed to, or null for all of the viewer's.
     *
     * A value that is not one of theirs is refused, not ignored: answering with "all of yours"
     * would tell someone probing ids nothing, but it would also keep a tab polling as though it
     * were still narrowed to a schedule this person has since lost.
     */
    private function selectedSchedule(Request $request, User $user): ?int
    {
        $value = $request->query('schedule');

        if ($value === null || $value === '') {
            return null;
        }

        $id = is_string($value) ? (int) UrlUtils::decodeId($value) : 0;

        abort_unless($id > 0 && $user->manageableRoles()->contains('id', $id), 403);

        return $id;
    }
}
