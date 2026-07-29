<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Role;
use App\Models\Setting;
use App\Utils\ColorUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * The Stay22 accommodation map: a per-schedule widget on public event pages showing
 * lodging near the venue, which earns an affiliate commission on bookings.
 *
 * Deliberately NOT part of AdsService. That feature is gated on ADS_ENABLED, serves only
 * free-tier schedules, and pays only the instance operator. This one has its own env gate,
 * applies to paid schedules too, and lets each schedule owner supply their own affiliate
 * ID and keep the commission. Sharing AdsService::setting() would also make the value
 * silently unreadable whenever ADS_ENABLED is false, because that helper prefixes keys
 * with `ads_` and short-circuits on the ads master switch.
 *
 * Static, like AdsService, so it is safe to call from a queued context and cheap to call
 * from a Blade component.
 */
class Stay22Service
{
    /**
     * Characters Stay22 account IDs use, and the injection guard - see sanitizeAid().
     */
    private const AID_PATTERN = '/^[A-Za-z0-9._-]{1,64}$/';

    /**
     * The operator-level master switch.
     *
     * Config only, never the settings table: SecurityHeaders calls this on EVERY request
     * to decide whether to widen frame-src, and that path must not touch the database.
     */
    public static function isEnabled(): bool
    {
        return (bool) config('stay22.enabled');
    }

    /**
     * The instance operator's fallback affiliate ID.
     *
     * Settings table first so an operator can change it without a redeploy, then the env
     * default. Returns null when the master switch is off, so a stored value cannot leak
     * into an install that has opted out.
     */
    public static function operatorAid(): ?string
    {
        if (! self::isEnabled()) {
            return null;
        }

        $stored = Setting::get('stay22_aid');

        return self::sanitizeAid(
            ($stored !== null && $stored !== '') ? $stored : config('stay22.aid')
        );
    }

    /**
     * Whether this schedule keeps its own commission. Drives both the admin-portal
     * warning panel and the custom-domain carve-out in embedFor().
     */
    public static function hasOwnAid(Role $role): bool
    {
        return self::sanitizeAid($role->stay22_aid) !== null;
    }

    /**
     * Who gets paid for bookings made from this schedule's event pages.
     *
     * The owner's own ID always wins; the operator's is only a fallback, and the admin
     * portal discloses exactly that. Null means neither is usable, which is the signal to
     * render nothing at all - Stay22 has no anonymous embed.
     */
    public static function resolveAid(Role $role): ?string
    {
        return self::sanitizeAid($role->stay22_aid) ?? self::operatorAid();
    }

    /**
     * The full embed URL, or null when this request must not show a map.
     *
     * Guards are ordered cheapest-first, mirroring AdsService::isEligible().
     */
    public static function embedFor(
        Role $role,
        ?Event $event,
        ?string $date,
        Request $request,
        ?string $accentColor = null,
        bool $passwordGate = false
    ): ?string {
        if (! self::isEnabled() || ! $role->stay22_enabled || ! $event) {
            return null;
        }

        // $passwordGate cannot currently be true: a password-protected event returns
        // event/password-prompt from RoleController::viewGuest() and never reaches the view
        // that renders this. Belt-and-braces rather than dead code, for the same reason
        // partials/promo-slot.blade.php keeps its copy - adding this component to a gated
        // view later must not quietly start earning commission on a page whose content the
        // visitor has not been allowed to see.
        if ($passwordGate) {
            return null;
        }

        // Embeds render inside someone else's iframe, and ?graphic=1 renders a shareable
        // PNG. Same reasoning as AdsService: an affiliate widget baked into a downloaded
        // image is nonsense, and the graphic renderer is headless so it can never consent.
        if ($request->boolean('embed') || $request->has('graphic')) {
            return null;
        }

        // The demo schedule is a sales surface, matching Role::showAds().
        if (is_demo_role($role)) {
            return null;
        }

        [$lat, $lng] = self::coordinates($event->venue);

        if ($lat === null) {
            return null;
        }

        $dates = self::bookableDates($event, $date);

        if (! $dates) {
            return null;
        }

        $aid = self::resolveAid($role);

        if (! $aid) {
            return null;
        }

        // On a customer's own branded domain we serve the map only when they keep the
        // commission. Falling back to the operator's ID on a white-label domain the
        // customer pays for reads as a bait-and-switch no matter what the settings page
        // said. Unlike the Google Maps embed a few lines above this in the guest view,
        // there is no API key to protect, so the map itself is fine here.
        if ($request->attributes->has('custom_domain_host') && ! self::hasOwnAid($role)) {
            return null;
        }

        // aid MUST come first: Stay22 keys their error detection and reporting off its
        // position. Built by hand rather than folded into the http_build_query() call
        // below so that a later refactor to Arr::query() or a sorted array cannot
        // silently reorder it.
        $url = 'https://www.stay22.com/embed/gm?aid='.rawurlencode($aid);

        $rest = array_filter([
            'lat' => $lat,
            'lng' => $lng,
            'checkin' => $dates['checkin'],
            'checkout' => $dates['checkout'],
            'campaign' => self::campaign($role),
            'maincolor' => self::mainColor($accentColor),
            // mapstyle is only the pre-JS default: Stay22Map.vue overrides it at mount from the
            // theme actually resolved in the browser, which this cannot know.
            'mapstyle' => self::mapStyle($role),
            'currency' => self::currency($event),
            // Deliberately no `supportedlang`. The vendor treats it as the list of languages the
            // widget may offer, so passing a single value locks the widget to it - and the only
            // value available here is the schedule OWNER's language, not the viewer's. Stay22
            // negotiates from the browser by default, which is strictly better.
            'unitsystem' => self::unitSystem($role),
        ], fn ($value) => $value !== null && $value !== '');

        return $url.'&'.http_build_query($rest);
    }

    /**
     * The nights a traveller needs for one occurrence, as [checkin, checkout] calendar
     * dates (Y-m-d) in the schedule's timezone. Null when the event has no start.
     */
    public static function stayDates(Event $event, ?string $date = null): ?array
    {
        if (! $event->starts_at) {
            return null;
        }

        $tz = $event->scheduleTimezone();

        // Passing $tz explicitly is load-bearing twice over. With no override,
        // getStartDateTime() resolves against the AUTHENTICATED VIEWER's timezone, so a
        // signed-in owner in Berlin would be handed different dates from an anonymous
        // visitor at the identical URL; and with no viewer and no creatorRole it falls
        // back to hardcoded 'UTC', which slides an evening event west of UTC onto the
        // wrong calendar day. The second argument enables the conversion at all.
        $start = $event->getStartDateTime($date, true, $tz);

        if (! $start) {
            return null;
        }

        $startDay = $start->copy()->startOfDay();
        $endDay = $start->copy()->addMinutes($event->durationInMinutes())->startOfDay();

        // NIGHTS, not days: the count of calendar boundaries the event crosses, floored at
        // one. An evening gig ends the same day it started, so it crosses none, but the
        // attendee still sleeps the night OF the event - hence the floor, and hence checkout
        // is never the same date as check-in. A Friday 18:00 event running 54 hours ends at
        // Monday 00:00, crossing three boundaries, so the guest sleeps Friday, Saturday and
        // Sunday and checks out on the Monday.
        //
        // This subsumes is_multi_day, which is why "not restricted to multi-day events"
        // needs no special case. getEndDateTime() is deliberately not used: it silently
        // defaults to +2 hours when duration is null, which would land checkout on the
        // check-in date and ask Stay22 for a zero-night stay.
        $nights = max(1, (int) $startDay->diffInDays($endDay));

        $nights = min($nights, max(1, (int) config('stay22.max_nights', 30)));

        return [
            'checkin' => $startDay->format('Y-m-d'),
            'checkout' => $startDay->copy()->addDays($nights)->format('Y-m-d'),
        ];
    }

    /**
     * The stay dates actually offered to this visitor, or null when there is nothing left to
     * book.
     *
     * stayDates() describes the event's own span, which is the right thing to derive but the
     * wrong thing to send once the event has started. A visitor arriving on day three of a
     * week-long festival still wants a room for tonight, so check-in is clamped forward to
     * today rather than the map being suppressed - suppressing it would hide the feature from
     * exactly the multi-day events it exists for.
     *
     * The event counts as over only when there is no night left: checkout today or earlier.
     * An evening gig last night has a checkout of this morning, so it correctly drops out.
     */
    private static function bookableDates(Event $event, ?string $date): ?array
    {
        $dates = self::stayDates($event, $date);

        if (! $dates) {
            return null;
        }

        // Venue-local rather than now(), for the same reason the dates themselves are.
        $tz = $event->scheduleTimezone();
        $today = Carbon::now($tz)->startOfDay();

        $checkin = Carbon::parse($dates['checkin'], $tz);
        $checkout = Carbon::parse($dates['checkout'], $tz);

        if ($checkout->lessThanOrEqualTo($today)) {
            return null;
        }

        if ($checkin->lessThan($today)) {
            $checkin = $today->copy();
        }

        return [
            'checkin' => $checkin->format('Y-m-d'),
            'checkout' => $checkout->format('Y-m-d'),
        ];
    }

    /**
     * Defence in depth for the affiliate ID.
     *
     * The ID is interpolated as the FIRST query parameter, so a value containing '&' or
     * '#' could append arbitrary Stay22 parameters or truncate the query entirely. Form
     * validation is the primary control; this exists because the operator Setting and any
     * row written before that validation existed both bypass it. Rejecting (render
     * nothing) beats encoding (render a silently wrong map).
     */
    private static function sanitizeAid($value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return ($value !== '' && preg_match(self::AID_PATTERN, $value)) ? $value : null;
    }

    /**
     * The venue's coordinates as URL-safe strings, or [null, null].
     *
     * roles.geo_lat and geo_lon are varchar and frequently unset: geocoding only runs when
     * BACKEND_GOOGLE_KEY is configured, and a venue can be created from just a name. '0'
     * is a legitimate coordinate, so these need real numeric checks rather than
     * truthiness, and number_format keeps a locale-influenced float cast from emitting a
     * comma as the decimal separator.
     */
    private static function coordinates(?Role $venue): array
    {
        if (! $venue) {
            return [null, null];
        }

        $lat = trim((string) $venue->geo_lat);
        $lng = trim((string) $venue->geo_lon);

        if ($lat === '' || $lng === '' || ! is_numeric($lat) || ! is_numeric($lng)) {
            return [null, null];
        }

        if (abs((float) $lat) > 90 || abs((float) $lng) > 180) {
            return [null, null];
        }

        return [
            number_format((float) $lat, 6, '.', ''),
            number_format((float) $lng, 6, '.', ''),
        ];
    }

    /**
     * Segments Stay22's own reporting per schedule, which is how an operator running the
     * fallback ID can later tell an owner what their pages earned. No PII.
     */
    private static function campaign(Role $role): ?string
    {
        $campaign = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $role->subdomain);

        return $campaign === '' ? null : substr($campaign, 0, 40);
    }

    /**
     * Stay22 wants a bare six-digit hex. Rejecting anything else matters because the value
     * lands in a query string.
     */
    private static function mainColor(?string $accent): ?string
    {
        $color = ltrim((string) ($accent ?? ''), '#');

        return preg_match('/^[0-9a-fA-F]{6}$/', $color) ? $color : null;
    }

    /**
     * Match the map chrome to the schedule's own palette.
     *
     * The guest portal is owner-themed, not driven by the visitor's OS preference, so this
     * reads the schedule's background rather than prefers-color-scheme. Reuses the same
     * 0.25 luminance threshold ColorUtils::getContrastColor() uses.
     */
    private static function mapStyle(Role $role): ?string
    {
        $background = $role->background_color;

        if (! is_string($background) || ! preg_match('/^#?[0-9a-fA-F]{6}$/', trim($background))) {
            return null;
        }

        return ColorUtils::getLuminance(trim($background)) > 0.25 ? 'light' : 'dark';
    }

    /**
     * Show accommodation prices in the same currency as the ticket.
     */
    private static function currency(Event $event): ?string
    {
        $currency = strtoupper(trim((string) $event->ticket_currency_code));

        return preg_match('/^[A-Z]{3}$/', $currency) ? $currency : null;
    }

    /**
     * Only the US, Liberia and Myanmar are meaningfully imperial; everywhere else expects
     * metric, so metric is the default rather than the exception.
     */
    private static function unitSystem(Role $role): ?string
    {
        $country = strtoupper(trim((string) $role->country_code));

        if ($country === '') {
            return null;
        }

        return in_array($country, ['US', 'LR', 'MM'], true) ? 'imperial' : 'metric';
    }
}
