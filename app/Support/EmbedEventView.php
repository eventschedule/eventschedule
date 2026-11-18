<?php

namespace App\Support;

use App\Models\Event;
use App\Models\Role;
use App\Utils\CustomFieldUtils;
use Illuminate\Http\Request;

/**
 * An event shown INSIDE an embedded calendar: on the organizer's own website, in the calendar's
 * frame or in the overlay public/embed.js draws over their page (the Embed dialog's "Show it on
 * your website", `events=onsite` on the calendar's address).
 *
 * The event's own page with `embed=true&view=event`, rendered by RoleController::viewGuest() as a
 * signed-out visitor sees it, with none of the site's chrome, and with every link but Back and the
 * other dates of a series leaving the frame (`<base target="_blank">`): our pages, the maps, a
 * video and an organizer's own site all refuse to be framed, and a cross-site frame has no session,
 * so buying or registering happens on the event's page in a new tab.
 *
 * Only the keys named here ever travel between the calendar and the event: there is no free-form
 * "back" address, which would be a way to send a visitor anywhere from a page of ours.
 */
class EmbedEventView
{
    /** The query value of `events=` that asks for events on the organizer's site. */
    public const ONSITE = 'onsite';

    /** Whether this request asks for the framed event view. */
    public static function requested(?Request $request = null): bool
    {
        $request ??= request();
        $embed = $request->query('embed');

        return ($embed === 'true' || $embed === '1') && $request->query('view') === 'event';
    }

    /**
     * What the frame carries from the calendar to the event and back: the theme it was pinned to,
     * its language, its month, the filters the visitor had, the layout. Each read as the place
     * that uses it reads it, and anything else dropped.
     *
     * @return array<string, string|int>
     */
    public static function carried(?Request $request = null): array
    {
        $request ??= request();
        $params = [];

        $dark = $request->query('dark');
        if ($dark === 'true' || $dark === 'false') {
            $params['dark'] = $dark;
        }

        $lang = $request->query('lang');
        if (is_string($lang) && is_valid_language_code($lang)) {
            $params['lang'] = $lang;
        }

        // requested_event_layout()'s reading, of this request rather than the current one.
        $layout = $request->query('layout');
        $layout = is_string($layout) ? match (strtolower(trim($layout))) {
            'calendar', 'grid', 'month' => 'calendar',
            'list' => 'list',
            default => null,
        } : null;
        if ($layout) {
            $params['layout'] = $layout;
        }

        foreach (['category', 'schedule'] as $key) {
            $value = $request->query($key);
            if (is_string($value) && $value !== '' && preg_match('/^[A-Za-z0-9_-]{1,100}$/', $value)) {
                $params[$key] = $value;
            }
        }

        $params += CustomFieldUtils::filterParams($request->query());

        $month = $request->query('month');
        $year = $request->query('year');
        if (is_scalar($month) && is_scalar($year) && ctype_digit((string) $month) && ctype_digit((string) $year)
            && (int) $month >= 1 && (int) $month <= 12 && (int) $year >= 2000 && (int) $year <= 2100) {
            $params['month'] = (int) $month;
            $params['year'] = (int) $year;
        }

        return $params;
    }

    /**
     * The calendar the framed event came from: the schedule's embed address with what it carried.
     * Built here, never taken from the request, so Back can only ever lead to a calendar of ours.
     */
    public static function calendarUrl(Role $role, ?Request $request = null): string
    {
        $params = ['embed' => 'true', 'events' => self::ONSITE] + self::carried($request);

        return HomeSchedule::is($role)
            ? url('/').'?'.http_build_query($params)
            : route('role.view_guest', ['subdomain' => $role->subdomain] + $params);
    }

    /**
     * Another date of the event, inside the same frame.
     */
    public static function eventUrl(Event $event, string $subdomain, ?string $date = null, ?Request $request = null): string
    {
        return self::withQuery($event->getGuestUrl($subdomain, $date), ['embed' => 'true', 'view' => 'event', 'events' => self::ONSITE] + self::carried($request));
    }

    /**
     * The event's own page, which opens in a new tab: with its language, and the form open where
     * one is asked for ($form: 'tickets' or 'rsvp').
     */
    public static function pageUrl(Event $event, string $subdomain, ?string $date = null, ?string $form = null, ?Request $request = null): string
    {
        $params = [];
        $carried = self::carried($request);
        if (isset($carried['lang'])) {
            $params['lang'] = $carried['lang'];
        }
        if ($form === 'tickets' || $form === 'rsvp') {
            $params[$form] = 'true';
        }
        // A promo code the widget's address was given (?promo=), for the form that applies it.
        $promo = ($request ?? request())->query('promo');
        if ($form === 'tickets' && is_string($promo) && preg_match('/^[A-Za-z0-9_-]{1,50}$/', $promo)) {
            $params['promo'] = $promo;
        }

        // Undated is the series, not its first night: getGuestUrl() handed no date names the
        // series' first date, which need not be an occurrence at all.
        $url = $date ? $event->getGuestUrl($subdomain, $date) : $event->getUndatedGuestUrl($subdomain);

        return self::withQuery($url, $params);
    }

    private static function withQuery(string $url, array $params): string
    {
        if ($url === '' || ! $params) {
            return $url;
        }

        return $url.(str_contains($url, '?') ? '&' : '?').http_build_query($params);
    }
}
