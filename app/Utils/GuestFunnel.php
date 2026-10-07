<?php

namespace App\Utils;

use App\Models\MarketingDailyStat;
use App\Models\PageView;
use App\Models\Role;
use Illuminate\Http\Request;

/**
 * Seven daily counts of what visitors do on guest pages, across every schedule.
 *
 * A schedule's own analytics count its views and its sales. These count the steps between and add
 * them up across schedules, so a change to the guest pages can be read against a "before":
 *
 *   event_view      opened an event page
 *   list_tap        went from a schedule's list or calendar into an event
 *   form_open       opened the ticket or sign-up form
 *   checkout_start  sent the form, and an order was created (tickets or a sign-up)
 *   checkout_done   was sent on to their ticket or order page at the end of it
 *   follow          followed a schedule, or joined its mailing list
 *   calendar_add    used Add to calendar
 *
 * Each is ONE VISITOR PER DAY (PageView::isFirstDailyVisit, the daily-salted IP and user agent
 * hash the submit page's counters use), so they read as "people who did this today" and a later
 * stage can be divided by an earlier one. They are not event counts: someone who buys twice in a
 * day is one checkout_done.
 *
 * Who is left out, at every stage, so the stages stay comparable:
 *   - a schedule's own team and the installation's admins (signed in, on a host where they are);
 *   - demo schedules (Role::isDemoContent()), whose visitors come from the marketing site;
 *   - embeds. A form embedded in someone else's site is a different page with different traffic;
 *   - bots, by the same filters the page-view counters use.
 *
 * Three stages happen only in the browser and arrive by a beacon (GuestFunnelBeaconController).
 * The beacon cannot know who is signed in, so the PAGE decides: it prints the beacon script only
 * when counts() says this visit counts (partials/guest-funnel).
 */
class GuestFunnel
{
    /**
     * stage => marketing_daily_stats column.
     */
    public const STAGES = [
        'event_view' => 'gp_event_visitors',
        'list_tap' => 'gp_list_taps',
        'form_open' => 'gp_form_opens',
        'checkout_start' => 'gp_checkout_starts',
        'checkout_done' => 'gp_checkouts_done',
        'follow' => 'gp_follows',
        'calendar_add' => 'gp_calendar_adds',
    ];

    /**
     * The stages the browser reports. The others are counted where the server does the thing, and
     * the beacon refuses them: a posted "checkout_done" would be a number anybody could raise.
     */
    public const BEACON_STAGES = ['list_tap', 'form_open', 'calendar_add'];

    /**
     * Whether a visit to this schedule's pages counts at all (see the class note).
     *
     * $role may be a subdomain, for the handlers that have no Role in hand. It is only looked up
     * once the cheaper tests have passed.
     */
    public static function counts(Request $request, Role|string|null $role): bool
    {
        if (! $role || $request->boolean('embed')) {
            return false;
        }

        $subdomain = $role instanceof Role ? $role->subdomain : $role;
        $user = $request->user();

        if ($user && ($user->isAdmin() || $user->isMember($subdomain))) {
            return false;
        }

        $role = $role instanceof Role ? $role : Role::subdomain($subdomain)->first();

        return $role !== null && ! $role->isDemoContent();
    }

    /**
     * Count one stage for this visitor, once today. Never throws: a counter must not cost a page
     * view, a follow or an order that has already been stored.
     */
    public static function count(string $stage, Request $request, Role|string|null $role): void
    {
        try {
            if (isset(self::STAGES[$stage]) && self::counts($request, $role)) {
                // A form post and a fetch() do not send a document's Accept header.
                self::record($stage, $request, $request->isMethod('GET'));
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Count a stage the browser reported. Who may send is decided by the page (see the class note).
     */
    public static function countFromBeacon(string $stage, Request $request): void
    {
        try {
            if (in_array($stage, self::BEACON_STAGES, true)) {
                self::record($stage, $request, false);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private static function record(string $stage, Request $request, bool $expectDocumentAccept): void
    {
        $userAgent = $request->userAgent();

        if (PageView::isBot($userAgent) || PageView::isSuspiciousRequest($request, $expectDocumentAccept)) {
            return;
        }

        $ip = $request->header('CF-Connecting-IP') ?? $request->ip();

        if (PageView::isFirstDailyVisit('gp_'.$stage, $ip, $userAgent)) {
            MarketingDailyStat::record(self::STAGES[$stage]);
        }
    }

    /**
     * The beacon's address, without a host: it is always same-origin, on the apex, a tenant
     * subdomain or a custom domain alike.
     */
    public static function beaconPath(): string
    {
        return parse_url(url('/api/guest-count'), PHP_URL_PATH) ?: '/api/guest-count';
    }
}
