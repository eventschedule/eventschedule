<?php

namespace App\Services;

use App\Models\Event;
use App\Models\PageView;
use App\Models\PromoterLink;
use App\Models\Role;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * Promoter links (Pro): `?via=CODE` on a schedule's guest pages, and the sales it leads to.
 *
 * The carrier is the session, keyed by schedule, and the last link followed wins (a promoter's
 * link is a deliberate click, unlike the first-touch utm_* attribution beside it). With marketing
 * consent, the same map also rides in the 30-day `es_via` cookie, which CaptureUtmParameters
 * forgets when consent is withdrawn. The ticket form carries the code in a hidden field as well,
 * because an embedded checkout may not keep a session.
 *
 * A sale is stamped only for an event the link's own schedule OWNS (Event::ticketingRole()): a
 * curator that merely lists somebody else's event must not see that event's revenue through a
 * link of its own. And only while that schedule is Pro, the plan the feature belongs to.
 */
class PromoterLinks
{
    public const COOKIE = 'es_via';

    public const MAX_LINKS = 200;

    private const SESSION_KEY = 'promoter_via';

    private const COUNTED_KEY = 'promoter_counted';

    private const COOKIE_MINUTES = 60 * 24 * 30;

    /** Clicks counted per link from one network address an hour: a client that keeps no cookie is a new visit every time. */
    private const CLICKS_PER_ADDRESS_HOUR = 10;

    /** A code as it may be typed after ?via=: letters, digits, dash and underscore. */
    public static function cleanCode(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return preg_match('/^[A-Za-z0-9_-]{1,40}$/', $value) ? $value : null;
    }

    /** A code made from a promoter's name, unused on this schedule. */
    public static function codeFromName(Role $role, string $name, ?int $ignoreId = null): string
    {
        $base = substr(trim(preg_replace('/[^a-z0-9]+/', '-', Str::lower(Str::ascii($name))), '-'), 0, 34);
        if ($base === '') {
            $base = 'promoter';
        }

        $code = $base;
        for ($i = 2; self::codeTaken($role, $code, $ignoreId); $i++) {
            $code = $base.'-'.$i;
        }

        return $code;
    }

    public static function codeTaken(Role $role, string $code, ?int $ignoreId = null): bool
    {
        return PromoterLink::where('role_id', $role->id)
            ->where('code', $code)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists();
    }

    /**
     * Called by the schedule's guest pages. Remembers the link a visitor arrived through and counts
     * the click once per visit. A code that is not this schedule's, or is archived, is ignored.
     */
    public static function capture(Request $request, Role $role): void
    {
        $code = self::cleanCode($request->query('via'));

        if ($code === null || ! $role->isPro() || PageView::isBot($request->userAgent())) {
            return;
        }

        $link = PromoterLink::where('role_id', $role->id)->where('code', $code)->whereNull('archived_at')->first();

        if (! $link) {
            return;
        }

        $map = (array) $request->session()->get(self::SESSION_KEY, []);
        $map[$role->id] = $link->id;
        $request->session()->put(self::SESSION_KEY, $map);

        // Counted once a visit, never for the schedule's own team (an owner checking the link would
        // lock its code), and at most a few times an hour from one address.
        $counted = (array) $request->session()->get(self::COUNTED_KEY, []);
        $isTeam = $request->user()?->isMember($role->subdomain);
        $address = Blocklist::address($request) ?? 'unknown';
        if (! in_array($link->id, $counted, true) && ! $isTeam
            && RateLimiter::attempt('promoter-click:'.$link->id.':'.sha1($address), self::CLICKS_PER_ADDRESS_HOUR, fn () => true, 3600)) {
            $counted[] = $link->id;
            $request->session()->put(self::COUNTED_KEY, $counted);

            // In SQL, so two clicks at once both count; the stamp is written once, by whichever
            // click finds it empty. Not through the model: a click is not an edit, and must not
            // move updated_at.
            PromoterLink::whereKey($link->id)->toBase()->increment('clicks');
            PromoterLink::whereKey($link->id)->whereNull('first_click_at')->toBase()->update(['first_click_at' => now()]);
        }

        if (consent_granted('marketing', $request)) {
            $stored = self::cookieMap($request);
            $stored[$role->id] = $link->id;
            Cookie::queue(Cookie::make(self::COOKIE, json_encode($stored), self::COOKIE_MINUTES, '/', null, true, true, false, 'Lax'));
        }
    }

    /**
     * The code the ticket form carries for this event, or null: from this request's own ?via=,
     * or from what this visit followed earlier to the event's own schedule.
     */
    public static function codeForForm(Request $request, Event $event): ?string
    {
        $link = self::linkFor($request, $event, includeInput: false);

        return $link?->code;
    }

    /**
     * The link a sale of this event is credited to, or null.
     *
     * A code is unique on its own schedule only, so a code typed in an address is read only on the
     * owning schedule's own pages (on a curator's page `?via=sam` is the curator's sam, which
     * capture() already counted), and a posted one only for the event the form was printed for:
     * a cart posts one form for several events.
     */
    public static function linkFor(Request $request, Event $event, bool $includeInput = true): ?PromoterLink
    {
        $owner = $event->ticketingRole();

        if (! $owner || ! $owner->isPro()) {
            return null;
        }

        $candidates = [];

        $postedEvent = $request->input('event_id');
        $postedFor = is_string($postedEvent) ? UrlUtils::decodeId($postedEvent) : null;
        if ($includeInput && (int) $postedFor === (int) $event->id && ($code = self::cleanCode($request->input('via')))) {
            $candidates[] = ['code' => $code];
        }

        $pageSchedule = $request->route('subdomain');
        if (is_string($pageSchedule) && strcasecmp($pageSchedule, (string) $owner->subdomain) === 0 && ($code = self::cleanCode($request->query('via')))) {
            $candidates[] = ['code' => $code];
        }

        if ($id = ((array) $request->session()->get(self::SESSION_KEY, []))[$owner->id] ?? null) {
            $candidates[] = ['id' => (int) $id];
        }

        if ($id = self::cookieMap($request)[$owner->id] ?? null) {
            $candidates[] = ['id' => (int) $id];
        }

        foreach ($candidates as $candidate) {
            $link = PromoterLink::where('role_id', $owner->id)
                ->whereNull('archived_at')
                ->when(isset($candidate['code']), fn ($q) => $q->where('code', $candidate['code']))
                ->when(isset($candidate['id']), fn ($q) => $q->whereKey($candidate['id']))
                ->first();

            if ($link) {
                return $link;
            }
        }

        return null;
    }

    /** @return array<int, int> schedule id => link id, from the consented cookie. */
    private static function cookieMap(Request $request): array
    {
        // Read only while the visitor still allows it: CaptureUtmParameters expires the cookie on
        // the response, which is one request too late for a checkout that arrives first.
        if (! consent_granted('marketing', $request)) {
            return [];
        }

        $raw = $request->cookie(self::COOKIE);
        $decoded = is_string($raw) ? json_decode($raw, true) : null;

        if (! is_array($decoded)) {
            return [];
        }

        $map = [];
        foreach ($decoded as $roleId => $linkId) {
            if (is_numeric($roleId) && is_numeric($linkId)) {
                $map[(int) $roleId] = (int) $linkId;
            }
        }

        return array_slice($map, -20, null, true);
    }

    /**
     * What each of a schedule's links has brought in: clicks are on the link; tickets, revenue (by
     * currency, donations left out, refunds taken off) and the commission are read from the paid
     * sales stamped with it.
     *
     * @return array<int, array{tickets: int, orders: int, revenue: array<string, float>, commission: array<string, float>}>
     */
    public static function stats(Role $role, $links): array
    {
        $ids = collect($links)->pluck('id')->all();
        $out = [];
        foreach ($links as $link) {
            $out[$link->id] = ['tickets' => 0, 'orders' => 0, 'revenue' => [], 'commission' => []];
        }

        if (! $ids) {
            return $out;
        }

        $paid = DB::table('sales')
            ->join('events', 'sales.event_id', '=', 'events.id')
            ->whereIn('sales.promoter_link_id', $ids)
            ->where('sales.status', 'paid')
            ->where('sales.is_deleted', false);

        // What buyers paid, donations left out, then the refunds taken off once: a refund is
        // recorded on the purchase's first row and may cover every seat of it, so it is taken off
        // the link's total, never off that one row (which would leave the other seats counted).
        $money = (clone $paid)
            ->groupBy('sales.promoter_link_id', 'events.ticket_currency_code')
            ->get([
                'sales.promoter_link_id AS link_id',
                'events.ticket_currency_code AS currency_code',
                // Floored per row: a gift card can pay the donation too, which leaves a row whose payment is
                // less than its donation, and that row must not take another sale's money off the total.
                DB::raw('SUM(GREATEST(sales.payment_amount - COALESCE(sales.donation_amount, 0), 0)) AS revenue'),
                DB::raw('COUNT(DISTINCT COALESCE(sales.order_id, sales.group_id, sales.id)) AS orders'),
            ]);

        $refunds = (clone $paid)
            ->join('sale_refunds', 'sale_refunds.sale_id', '=', 'sales.id')
            ->where('sale_refunds.status', 'succeeded')
            ->groupBy('sales.promoter_link_id', 'events.ticket_currency_code')
            ->get([
                'sales.promoter_link_id AS link_id',
                'events.ticket_currency_code AS currency_code',
                DB::raw('SUM(sale_refunds.amount) AS refunded'),
            ])
            ->keyBy(fn ($row) => $row->link_id.'|'.($row->currency_code ?: 'USD'));

        foreach ($money as $row) {
            $currency = $row->currency_code ?: 'USD';
            $net = (float) $row->revenue - (float) ($refunds[$row->link_id.'|'.$currency]->refunded ?? 0);
            $out[$row->link_id]['orders'] += (int) $row->orders;
            if ($net > 0) {
                $out[$row->link_id]['revenue'][$currency] = round($net, 2);
            }
        }

        // Admissions: ticket lines without add-ons, and each registration of a free event (an
        // RSVP row is one guest and has no ticket lines).
        $tickets = (clone $paid)
            ->join('sale_tickets', 'sale_tickets.sale_id', '=', 'sales.id')
            ->join('tickets', 'tickets.id', '=', 'sale_tickets.ticket_id')
            ->where('tickets.is_addon', false)
            ->groupBy('sales.promoter_link_id')
            ->get(['sales.promoter_link_id AS link_id', DB::raw('SUM(sale_tickets.quantity) AS tickets')]);

        foreach ($tickets as $row) {
            $out[$row->link_id]['tickets'] += (int) $row->tickets;
        }

        $registrations = (clone $paid)
            ->where('sales.payment_method', 'rsvp')
            ->groupBy('sales.promoter_link_id')
            ->get(['sales.promoter_link_id AS link_id', DB::raw('COUNT(*) AS guests')]);

        foreach ($registrations as $row) {
            $out[$row->link_id]['tickets'] += (int) $row->guests;
        }

        foreach ($links as $link) {
            if ($link->commission_percent) {
                foreach ($out[$link->id]['revenue'] as $currency => $amount) {
                    $out[$link->id]['commission'][$currency] = round($amount * $link->commission_percent / 100, 2);
                }
            }
        }

        return $out;
    }
}
