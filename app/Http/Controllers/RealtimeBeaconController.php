<?php

namespace App\Http\Controllers;

use App\Models\PageView;
use App\Utils\RealtimeTracker;
use App\Utils\UserAgentUtils;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * POST /api/realtime - the beacon behind /admin/realtime.
 *
 * Lives in routes/api.php because the api group has no session, no cookies and no CSRF: a beacon
 * must never create or slide a session (that would also take a visitor off the marketing edge
 * cache), and it has to answer same-origin on every host, custom domains included.
 *
 * Message types, all JSON, all answered 204 (422 for a malformed body):
 *   pv     a page view. m = 'f' (the visitor accepted cookies) or 'c' (count-only, no identifier).
 *   hb     a heartbeat from a full-mode page; g = 1 once the visitor has engaged.
 *   end    the page was hidden or closed.
 *   revoke the visitor withdrew consent: strip identity from what is still held.
 *
 * The mode is the client's choice and the server enforces it: a count-only page view is stored
 * with no visitor key, user, title, browser or OS whatever its context carries.
 *
 * Whether a schedule's owner may see an identified visitor as a row of their own Realtime page
 * (owner_visible) is the one thing read from a cookie here, and it is the server's reading, not a
 * bit the page sends: RealtimeTracker::consentCoversOrganizers() looks at the visitor's own
 * recorded choice, which the browser sends with this same-origin request. Reading a cookie
 * starts no session, which is what the first paragraph is about.
 */
class RealtimeBeaconController extends Controller
{
    private const MAX_BODY = 4096;

    public function store(Request $request): Response
    {
        if (strlen($request->getContent()) > self::MAX_BODY) {
            return response()->noContent(422);
        }

        $data = json_decode($request->getContent(), true);
        $type = is_array($data) ? ($data['t'] ?? null) : null;
        $key = is_array($data) ? ($data['k'] ?? null) : null;

        if (! in_array($type, ['pv', 'hb', 'end', 'revoke'], true)
            || ! is_string($key) || preg_match('/^[0-9a-f]{32}$/', $key) !== 1) {
            return response()->noContent(422);
        }

        // The beacon URL is host-less, so a real beacon is always same-origin. Anything else is
        // another site making its visitors' browsers post here (CORS allows api/* from anywhere,
        // and the raw body is read whatever its content type). Browsers too old to send the header
        // are let through.
        $fetchSite = $request->header('Sec-Fetch-Site');

        if (! RealtimeTracker::enabled()
            || ($fetchSite !== null && $fetchSite !== 'same-origin')
            || PageView::isBot($request->userAgent())
            || PageView::isSuspiciousRequest($request, false)) {
            return response()->noContent();
        }

        // A page view is identified only when the page says so: one without a mode is count-only,
        // never the other way round. Heartbeats and ends carry no mode and only ever touch a row a
        // full page view already marked consented. Global Privacy Control is treated as "Decline"
        // here too, not only in the browser.
        $mode = $data['m'] ?? ($type === 'pv' ? 'c' : 'f');
        $full = $mode === 'f' && $request->header('Sec-GPC') !== '1';

        RealtimeTracker::write(function () use ($type, $request, $data, $full, $key) {
            // Inside write(): a context nobody signed must never be able to throw out of here.
            $context = RealtimeTracker::verify($data['c'] ?? null);

            match ($type) {
                'pv' => $context ? $this->pageView($request, $data, $context, $full, $key) : null,
                'hb' => $full ? $this->heartbeat($request, $data, $context, $key) : null,
                'end' => $full ? $this->end($key) : null,
                'revoke' => $this->revoke($request, $context, $key),
            };
        });

        if ($type === 'pv') {
            RealtimeTracker::pruneIfDue();
        }

        return response()->noContent();
    }

    private function pageView(Request $request, array $data, array $context, bool $full, string $key): void
    {
        $row = $this->buildRow($request, $data, $context, $full, $key, continuation: false);

        if ($row === null) {
            return;
        }

        $columns = array_keys($row);
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));

        // An engagement heartbeat can overtake its page view and create the row first, and a visitor
        // who clicks Allow mid-page upgrades their count-only row with a full page view under the
        // same key. Either way the row exists already: fill in what this message knows and keep
        // what it does not. Only a full page view replaces the path (the upgrade swaps the template
        // for the real one); a count-only view after Allow-then-Decline keeps whatever is there,
        // and its engagement still counts.
        DB::statement(
            'INSERT INTO realtime_hits ('.implode(', ', $columns).') VALUES ('.$placeholders.')
             ON DUPLICATE KEY UPDATE
                consented = GREATEST(consented, VALUES(consented)),
                owner_visible = GREATEST(owner_visible, VALUES(owner_visible)),
                is_team = GREATEST(is_team, VALUES(is_team)),
                path = CASE WHEN VALUES(consented) = 1 THEN VALUES(path) ELSE path END,
                path_template = COALESCE(VALUES(path_template), path_template),
                engaged_at = COALESCE(engaged_at, VALUES(engaged_at)),
                visitor_key = COALESCE(VALUES(visitor_key), visitor_key),
                user_id = COALESCE(VALUES(user_id), user_id),
                title = COALESCE(VALUES(title), title),
                browser = COALESCE(VALUES(browser), browser),
                os = COALESCE(VALUES(os), os),
                source_channel = COALESCE(VALUES(source_channel), source_channel),
                source_name = COALESCE(VALUES(source_name), source_name),
                utm_campaign = COALESCE(VALUES(utm_campaign), utm_campaign),
                is_entrance = VALUES(is_entrance),
                started_at = LEAST(started_at, VALUES(started_at))',
            array_values($row)
        );
    }

    private function heartbeat(Request $request, array $data, ?array $context, string $key): void
    {
        $now = RealtimeTracker::ts(RealtimeTracker::now());
        $engaged = ! empty($data['g']) ? 1 : 0;

        $changed = DB::update(
            'UPDATE realtime_hits
                SET last_seen_at = ?, ended_at = NULL,
                    engaged_at = CASE WHEN ? = 1 THEN COALESCE(engaged_at, ?) ELSE engaged_at END
              WHERE hit_key = ? AND consented = 1',
            [$now, $engaged, $now, $key]
        );

        // DB::update() counts rows CHANGED, not matched (no MYSQL_ATTR_FOUND_ROWS), so a second
        // heartbeat in the same second returns 0 too. Only a row that is really gone (pruned after
        // an hour hidden, or overtaken by this very heartbeat) is recreated, and only from a valid
        // context, so the GeoIP and source work stays off the normal heartbeat.
        if ($changed > 0 || $context === null || DB::table('realtime_hits')->where('hit_key', $key)->exists()) {
            return;
        }

        $row = $this->buildRow($request, $data, $context, full: true, key: $key, continuation: true);

        if ($row !== null) {
            DB::table('realtime_hits')->insertOrIgnore($row);
        }
    }

    /**
     * Never recreates: closing a two-hour-old tab must not mint a visitor out of nothing.
     */
    private function end(string $key): void
    {
        $now = RealtimeTracker::ts(RealtimeTracker::now());

        DB::update(
            'UPDATE realtime_hits SET last_seen_at = ?, ended_at = ? WHERE hit_key = ? AND consented = 1',
            [$now, $now, $key]
        );
    }

    /**
     * Consent withdrawn: every row this browser (today's and yesterday's key) or this account still
     * has becomes a count-only row, as if the visitor had declined from the start: no identifier,
     * the route template instead of a path that names them, and no heartbeat-extended lifetime.
     */
    private function revoke(Request $request, ?array $context, string $key): void
    {
        // Yesterday's key always, not only in visitorKeys()' first half hour: a visit that crossed
        // midnight keeps yesterday's key on rows that live until about 01:30.
        $keys = array_values(array_unique(array_filter([
            ...RealtimeTracker::visitorKeys($request),
            RealtimeTracker::visitorKey($request, RealtimeTracker::now()->subDay()),
        ])));
        $userId = $context['user_id'] ?? null;

        // A declined row's last activity is its start. Clamped to the retention cutoff so a page
        // open for hours is pruned on the next run rather than looking two hours stale to
        // AdminAlertService's realtime_prune_stalled check. A formatted server timestamp, not input.
        $cutoff = RealtimeTracker::ts(RealtimeTracker::now()->subMinutes(RealtimeTracker::RETENTION_MINUTES));

        DB::table('realtime_hits')
            ->where(function ($query) use ($keys, $userId, $key) {
                // The page's own row by its key, whatever this browser's visitor key is now: a new
                // network since the page loaded must not leave the page in front of them identified.
                $query->where('hit_key', $key);
                if ($keys) {
                    $query->orWhereIn('visitor_key', $keys);
                }
                if ($userId) {
                    $query->orWhere('user_id', $userId);
                }
            })
            // MySQL assigns left to right: `path` must read path_template before it is cleared.
            ->update([
                'path' => DB::raw('COALESCE(path_template, path)'),
                'path_template' => null,
                'last_seen_at' => DB::raw("GREATEST(started_at, '{$cutoff}')"),
                'visitor_key' => null,
                'user_id' => null,
                'title' => null,
                'browser' => null,
                'os' => null,
                'consented' => false,
                'owner_visible' => false,
                'source_channel' => DB::raw('CASE WHEN is_entrance = 1 THEN source_channel ELSE NULL END'),
                'source_name' => DB::raw('CASE WHEN is_entrance = 1 THEN source_name ELSE NULL END'),
                'utm_campaign' => DB::raw('CASE WHEN is_entrance = 1 THEN utm_campaign ELSE NULL END'),
            ]);
    }

    /**
     * The full row for a page view, or null when it must not be stored.
     */
    private function buildRow(Request $request, array $data, array $context, bool $full, string $key, bool $continuation): ?array
    {
        $now = RealtimeTracker::now();
        $ts = RealtimeTracker::ts($now);
        $referrerHost = RealtimeTracker::normalizeHost(RealtimeTracker::clean($data['r'] ?? null, 253));
        $framed = ! empty($data['fr']) || $context['is_embed'];
        $surface = $framed ? 'embed' : $context['surface'];

        // The /embed-calendar marketing demo and the app's embed preview frame real schedule pages
        // on our own hosts; counting them would invent a visitor per preview.
        if ($surface === 'embed' && ($referrerHost === null || RealtimeTracker::isInternalHost($referrerHost, $request))) {
            return null;
        }

        // An embed is never a person on our site, so it is stored the count-only way regardless.
        $identified = $full && $surface !== 'embed';
        $navigation = in_array($data['n'] ?? null, ['reload', 'back_forward'], true) ? $data['n'] : 'navigate';
        $utm = is_array($data['u'] ?? null) ? $data['u'] : [];
        $userAgent = $request->userAgent();

        $source = $continuation
            ? ['inherit' => true]
            : RealtimeTracker::classifySource($referrerHost, $utm, $navigation, $context['surface'], $request);

        $row = [
            'hit_key' => $key,
            'visitor_key' => null,
            'consented' => $identified ? 1 : 0,
            'user_id' => null,
            'is_admin' => $context['is_admin'] ? 1 : 0,
            'is_demo' => $context['is_demo'] ? 1 : 0,
            // Known from the signed-in session at render, so it holds for a team member who
            // declined cookies too. The owner's Realtime page reads neither of these two for
            // anything but leaving a row out or in (App\Services\ScheduleRealtime).
            'is_team' => ! empty($context['is_team']) ? 1 : 0,
            'owner_visible' => $identified && RealtimeTracker::consentCoversOrganizers($request) ? 1 : 0,
            'surface' => $surface,
            'path' => $context['path'],
            'path_template' => null,
            'title' => null,
            'role_id' => $context['role_id'],
            'event_id' => $context['event_id'],
            'source_channel' => null,
            'source_name' => null,
            'utm_campaign' => null,
            'is_entrance' => 0,
            'country' => RealtimeTracker::country($request),
            'device' => PageView::detectDeviceType($userAgent),
            'browser' => null,
            'os' => null,
            'hb' => $context['hb'],
            'started_at' => $ts,
            'last_seen_at' => $ts,
            // A count-only page view is sent at the moment of engagement (it has no heartbeats to
            // engage later), and a continuation exists only because a heartbeat arrived.
            'engaged_at' => $identified && ! $continuation ? null : $ts,
            'ended_at' => null,
        ];

        // A path that names the visitor stays out of an anonymous row: an app page's path is their
        // own schedule's subdomain, and a ticket order, gift card or installment page carries its
        // record's id. Those rows keep the route template instead, and an identified row keeps it
        // beside the real path for revoke() to swap in. Marketing and schedule pages are public, so
        // their real path says nothing about who viewed them.
        $pathNamesVisitor = in_array($context['surface'], ['ap', 'auth'], true)
            || ($context['surface'] === 'gp' && ! $context['role_id']);

        if (! $identified) {
            if ($pathNamesVisitor) {
                $row['path'] = $context['template'];
            }

            // Without an identifier there is no visit to inherit from, so the source is kept only
            // when this view is the start of one: an external referrer or utm, or no referrer at all
            // on a fresh navigation (typed or bookmarked).
            if (! $source['inherit']) {
                $row = array_merge($row, $this->sourceColumns($source), ['is_entrance' => 1]);
            } elseif ($referrerHost === null && $navigation === 'navigate' && ! $continuation) {
                $row = array_merge($row, ['source_channel' => 'direct', 'is_entrance' => 1]);
            }

            return $row;
        }

        $keys = RealtimeTracker::visitorKeys($request);
        if (! $keys) {
            return null;
        }

        // The signed context stays valid for a day, so a tab left open across an account deletion
        // keeps heartbeating as that user. Its rows are an ordinary consented visitor's from then on.
        $userId = $context['user_id'] && DB::table('users')->where('id', $context['user_id'])->exists()
            ? $context['user_id']
            : null;

        $since = RealtimeTracker::ts($now->subMinutes(30));
        // Never this page view's own row: an engagement heartbeat that overtook it may have created
        // it already, and finding it made the page view a non-entrance.
        $previous = DB::table('realtime_hits')
            ->whereIn('visitor_key', $keys)
            ->where('hit_key', '!=', $key)
            ->where('last_seen_at', '>=', $since)
            ->orderByDesc('last_seen_at')
            ->first(['visitor_key', 'source_channel', 'source_name', 'utm_campaign']);

        if (! $previous && $userId) {
            $previous = DB::table('realtime_hits')
                ->where('user_id', $userId)
                ->where('hit_key', '!=', $key)
                ->where('last_seen_at', '>=', $since)
                ->orderByDesc('last_seen_at')
                ->first(['visitor_key', 'source_channel', 'source_name', 'utm_campaign']);
        }

        // Keep yesterday's key when the visit started before midnight, but never adopt another
        // device's key found through the account.
        $visitorKey = $previous && in_array($previous->visitor_key, $keys, true) ? $previous->visitor_key : $keys[0];

        if (! $source['inherit']) {
            $sourceColumns = $this->sourceColumns($source);
        } elseif ($previous && $previous->source_channel) {
            $sourceColumns = [
                'source_channel' => $previous->source_channel,
                'source_name' => $previous->source_name,
                'utm_campaign' => $previous->utm_campaign,
            ];
        } elseif ($continuation) {
            // A page reopened after its row was pruned: the visit's source is no longer known.
            $sourceColumns = [];
        } else {
            $sourceColumns = ['source_channel' => 'direct', 'source_name' => null, 'utm_campaign' => null];
        }

        return array_merge($row, $sourceColumns, [
            'visitor_key' => $visitorKey,
            'user_id' => $userId,
            'path_template' => $pathNamesVisitor ? $context['template'] : null,
            'title' => $this->title($data['ti'] ?? null, $context['surface']),
            'browser' => UserAgentUtils::browser($userAgent),
            'os' => UserAgentUtils::os($userAgent),
            'is_entrance' => $previous || $continuation ? 0 : 1,
        ]);
    }

    private function sourceColumns(array $source): array
    {
        return [
            'source_channel' => $source['channel'],
            'source_name' => RealtimeTracker::clean($source['name'] ?? null, 100),
            'utm_campaign' => RealtimeTracker::clean($source['campaign'] ?? null, 100),
        ];
    }

    /**
     * Marketing and app pages keep their own title, minus the " | Event Schedule" suffix. Schedule
     * pages resolve their names from ids at read time, and sign-in pages all share one fixed
     * title, so neither stores one.
     */
    private function title(mixed $title, string $surface): ?string
    {
        if (! in_array($surface, ['wp', 'ap'], true)) {
            return null;
        }

        $title = RealtimeTracker::clean($title, 300);
        if ($title === null) {
            return null;
        }

        $suffix = preg_quote((string) config('app.name', 'Event Schedule'), '/');
        $title = preg_replace('/\s*[|\-]\s*'.$suffix.'\s*$/u', '', $title);

        return RealtimeTracker::clean($title, 150);
    }
}
