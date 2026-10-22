<?php

namespace App\Http\Controllers;

use App\Services\AdminAlertService;
use App\Services\AdminDashboard;
use App\Services\RealtimeDashboard;
use App\Utils\RealtimeTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\View\View;

/**
 * /admin/dashboard: the live view (what was /admin/realtime until 2026-10) with the slower cards
 * of the old dashboard under it.
 *
 * The page itself reads nothing that takes time. The live part polls admin.realtime.data from the
 * moment it starts, the Revenue card in its corner is fetched once the page is up, and the cards
 * below are three panels (PANELS), each fetched the first time it nears the screen and each
 * building only the cards it shows (AdminDashboard::build()). The old page ran every card's
 * queries before it could send a byte, which is what made it slow to open.
 *
 * Inside the `admin` middleware and its throttle:30,1: the poll is six requests a minute, and a
 * page view adds at most four more (Revenue and the three panels), each made once.
 */
class AdminDashboardController extends Controller
{
    /** What each panel shows, as the AdminDashboard cards it is built from. */
    private const PANEL_CARDS = [
        'recent' => ['schedules', 'recentEvents', 'eventCounts'],
        'growth' => ['signups', 'active', 'events'],
        'platform' => ['federation', 'system'],
    ];

    public const PANELS = ['recent', 'growth', 'platform'];

    public function index(Request $request): View|RedirectResponse
    {
        if (! $request->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        return view('admin.dashboard', [
            // The live part starts empty and fills from its first poll. Only "off" is known
            // without reading the visits, and the page then says how to turn tracking on. The
            // filters in the address ride along, so the first poll asks for the view they name.
            'payload' => ['state' => RealtimeTracker::enabled() ? 'loading' : 'off']
                + RealtimeDashboard::fromRequest($request)->requestState(),
            // Everything across /admin that is waiting on an admin, as one to-do list. The nav's
            // badges read the same counts when the page renders, from AdminAlertService's memo.
            'adminAlerts' => AdminAlertService::items(),
            'sample' => $this->sample($request),
        ]);
    }

    /**
     * The Revenue card in the corner: the totals of what Stripe is billing, formatted here so the
     * page prints them as they come. Hosted only, where there is billing.
     */
    public function revenue(Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless(config('app.hosted'), 404);

        $builder = app(AdminDashboard::class);
        $revenue = $this->sample($request)
            ? (AdminDashboard::sample()['revenue'] ?? null)
            : ($builder->build(['revenue'])['revenue'] ?? null);

        if (! $revenue) {
            return response()->json(['failed' => true]);
        }

        $totals = $revenue['totals'];

        return response()->json([
            'failed' => false,
            'mrr' => plan_price($totals['mrr']),
            'arr' => plan_price($totals['arr']),
            'trial_mrr' => plan_price($totals['trial_mrr']),
            'at_risk_mrr' => plan_price($totals['at_risk_mrr']),
            'billing_count' => (int) $totals['billing_count'],
            'trialing_count' => (int) $totals['trialing_count'],
            'at_risk_count' => (int) $totals['at_risk_count'],
            'past_due_count' => (int) $totals['past_due_count'],
            'cancelling_count' => (int) $totals['cancelling_count'],
            // Not printed: the figure the growth export reports as ARR, for the test that holds
            // the two to one definition.
            'arr_value' => (float) $totals['arr'],
        ])->withHeaders($this->sample($request) ? [] : ['Server-Timing' => $builder->serverTiming()]);
    }

    /**
     * One panel of cards, as HTML for the page to put in its place. Outside the page's Vue mount,
     * because the cards print schedule, event and people names as text.
     */
    public function panel(Request $request, string $panel): Response
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless(in_array($panel, self::PANELS, true), 404);

        $cards = self::PANEL_CARDS[$panel];
        $sample = $this->sample($request);
        $builder = app(AdminDashboard::class);

        if ($sample) {
            $data = AdminDashboard::sample();
            $data['eventCounts'] = Arr::only($data['events'], ['all_time', 'new_30d', 'new_change']);
            $dashboard = Arr::only($data, array_merge($cards, ['firstRun']));
        } else {
            $dashboard = $builder->build($cards);
        }

        $response = response()->view('admin.dashboard.panel', ['panel' => $panel, 'dashboard' => $dashboard]);

        // Not for the invented data of the docs screenshot: nothing was read to make it.
        if (! $sample) {
            $response->header('Server-Timing', $builder->serverTiming());
        }

        return $response;
    }

    /**
     * ?sample=1 renders invented data for the documentation screenshot, which must not publish
     * the developer's real schedules and people. Local and testing only: in production the
     * parameter does nothing.
     */
    private function sample(Request $request): bool
    {
        return $request->boolean('sample') && app()->environment('local', 'testing');
    }
}
