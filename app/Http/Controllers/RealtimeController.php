<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ScheduleRealtime;
use App\Utils\UrlUtils;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * /realtime: a schedule owner's live view of their own guest pages.
 *
 * Not /admin/realtime with a filter. That page is the operator's, about the whole install and about
 * people; this one answers for the viewer's schedules only and never says who anyone is. All of
 * that is in App\Services\ScheduleRealtime; this class decides who may ask and for which schedule.
 *
 * Where the install does not offer the page (Realtime off, the owner switch off, the shared demo
 * account, or nobody's schedule to show) every route here is a 404, the same as a page that does
 * not exist, and the sidebar and the dashboard carry no way in.
 */
class RealtimeController extends Controller
{
    private const SALT_KEY = 'realtime_owner_salt';

    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless(ScheduleRealtime::available($user), 404);

        $schedules = ScheduleRealtime::scheduleOptions($user);
        $selected = $this->selectedSchedule($request, $user);

        return view('realtime.index', [
            'payload' => $this->service($request, $user)->payload($selected),
            'schedules' => $schedules,
            'selected' => $selected ? UrlUtils::encodeId($selected) : null,
        ]);
    }

    /** The poll. Same answer as the page, as JSON. */
    public function data(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless(ScheduleRealtime::available($user), 404);

        return response()->json($this->service($request, $user)->payload($this->selectedSchedule($request, $user)));
    }

    /** The dashboard's Realtime tile. */
    public function summary(Request $request): JsonResponse
    {
        $summary = ScheduleRealtime::summary($request->user(), $this->salt($request));
        abort_if($summary === null, 404);

        // Keyed by encoded id on the way out: a raw schedule id is never printed for a browser.
        $summary['by_schedule'] = collect($summary['by_schedule'])
            ->mapWithKeys(fn ($count, $id) => [UrlUtils::encodeId($id) => $count])->all();

        return response()->json($summary);
    }

    private function service(Request $request, User $user): ScheduleRealtime
    {
        return new ScheduleRealtime($user, $user->manageableRoles()->pluck('id'), $this->salt($request));
    }

    /**
     * The schedule the page is narrowed to, or null for all of the viewer's.
     *
     * A value that is not one of theirs is refused, not ignored: answering with "all of yours"
     * would tell someone probing ids nothing, but it would also hide a broken link from its owner.
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

    /**
     * A row's handle is a hash salted with this, so it means nothing outside the session that is
     * looking: not to another owner, and not to the same owner tomorrow or on another device.
     */
    private function salt(Request $request): string
    {
        $session = $request->session();

        if (! is_string($session->get(self::SALT_KEY)) || $session->get(self::SALT_KEY) === '') {
            $session->put(self::SALT_KEY, bin2hex(random_bytes(16)));
        }

        return $session->get(self::SALT_KEY);
    }
}
