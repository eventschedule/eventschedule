<?php

namespace App\Utils;

use App\Models\Role;
use App\Models\Setting;
use App\Services\DemoService;
use App\Services\GeoIpService;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\DetectsConcurrencyErrors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Collection side of /admin/realtime: whether it is on, the signed context every page renders
 * into its beacon, and the helpers the beacon endpoint uses to turn a request into a row.
 *
 * Consent decides how much is stored, not whether the page view is counted. A visitor who clicked
 * "Allow" on the cookie banner is stored with a daily-rotating visitor key and, when signed in,
 * their account; anyone else is stored with no identifier at all (see the migration). The client
 * picks the mode, and the server enforces it: a count-only beacon never gets a key or a user here,
 * whatever its context says.
 */
class RealtimeTracker
{
    use DetectsConcurrencyErrors;

    /** Rows are deleted this long after their last activity. The privacy policy says "about an hour". */
    public const RETENTION_MINUTES = 60;

    public const SURFACES = ['wp', 'gp', 'ap', 'auth', 'embed'];

    /** Heartbeats slow down once this many page views are "now", to protect the one web container. */
    public const BUSY_THRESHOLD = 300;

    /** A context older than this is refused. Edge-cached pages are at most ten minutes old. */
    private const CONTEXT_MAX_AGE = 86400;

    /**
     * Route parameters whose values never reach the table (ticket secrets, reset tokens, ...).
     * SentryScrubber and Google Analytics (via redactedPath()) apply the same rule.
     */
    public const SECRET_PARAM = '/token|secret|hash|code|signature|key/i';

    private const PAID_MEDIUMS = ['cpc', 'ppc', 'paid', 'paidsocial', 'paid_social', 'display', 'cpm', 'banner'];

    private const EMAIL_MEDIUMS = ['email', 'e-mail', 'newsletter'];

    private const AI_HOSTS = [
        'chatgpt.com', 'chat.openai.com', 'perplexity.ai', 'claude.ai', 'gemini.google.com',
        'copilot.microsoft.com', 'chat.deepseek.com', 'grok.com', 'meta.ai', 'chat.mistral.ai', 'you.com',
    ];

    private const EMAIL_HOSTS = [
        'mail.google.com', 'outlook.live.com', 'outlook.office.com', 'outlook.office365.com',
        'mail.yahoo.com', 'com.google.android.gm',
    ];

    private const SEARCH_HOSTS = [
        'google.*', 'bing.com', 'duckduckgo.com', 'search.yahoo.com', 'yandex.*', 'baidu.com', 'ecosia.org',
        'search.brave.com', 'startpage.com', 'kagi.com', 'com.google.android.googlequicksearchbox',
    ];

    private const SOCIAL_HOSTS = [
        'facebook.com', 'instagram.com', 't.co', 'x.com', 'twitter.com', 'linkedin.com', 'lnkd.in',
        'reddit.com', 'tiktok.com', 'youtube.com', 'pinterest.*', 'threads.net', 'bsky.app',
        'web.whatsapp.com', 'discord.com',
    ];

    /**
     * Google products that are not Google Search, which SEARCH_HOSTS' "google.*" would otherwise
     * claim: an event link clicked in a Calendar invite or a shared Doc is not a search visit.
     */
    private const GOOGLE_PRODUCT_HOSTS = [
        'calendar.google.com', 'docs.google.com', 'drive.google.com', 'sites.google.com',
        'groups.google.com', 'meet.google.com', 'photos.google.com', 'keep.google.com', 'chat.google.com',
    ];

    /** Hosts a visitor passes through mid-visit (sign-in, payment); they never start a new source. */
    private const HANDOFF_HOSTS = [
        'accounts.google.com', 'appleid.apple.com', 'checkout.stripe.com', 'connect.stripe.com',
        'paypal.com', 'payfast.co.za', 'invoicing.co', 'invoiceninja.com',
    ];

    public static function enabled(): bool
    {
        try {
            $default = config('app.is_nexus') ? '1' : '0';

            return (string) Setting::get('realtime_enabled', $default) === '1';
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Seconds between heartbeats for a page rendered now. Never throws: this runs inside every
     * layout render, and a selfhost install that skipped `migrate` must still serve pages.
     */
    public static function heartbeatSeconds(): int
    {
        try {
            return (int) Cache::remember('realtime_hb_seconds', 60, function () {
                $busy = DB::table('realtime_hits')
                    ->where('consented', true)
                    ->whereNull('ended_at')
                    ->where('last_seen_at', '>=', self::ts(self::now()->subSeconds(150)))
                    ->count();

                return $busy > self::BUSY_THRESHOLD ? 120 : 60;
            });
        } catch (Throwable) {
            return 60;
        }
    }

    /**
     * Whether this request's page should carry the beacon at all.
     */
    public static function shouldRender(Request $request): bool
    {
        if ($request->is('admin', 'admin/*') || $request->graphic || ! self::enabled()) {
            return false;
        }

        try {
            return ! selfhost_needs_setup();
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * The signed context a page hands its beacon, or null when the page must not carry one.
     *
     * Identical for every anonymous visitor to a given page, so an edge-cached marketing page stays
     * shareable: `u` is only ever filled for an authenticated request, which is never cached.
     */
    public static function context(Request $request, string $surface, mixed $role = null, mixed $event = null): ?array
    {
        if (! in_array($surface, self::SURFACES, true) || ! self::shouldRender($request)) {
            return null;
        }

        $user = $request->user();
        $isDemo = $user && DemoService::isDemoUser($user);

        $path = self::path($request);

        $context = [
            's' => $surface,
            'p' => $path,
            // The route template, stored instead of the path for a visitor who has not accepted
            // cookies on pages whose real path names them (an app page's schedule subdomain, a
            // ticket order's id). See RealtimeBeaconController::buildRow().
            'pt' => self::template($request, $path),
            // Blade component model props arrive as EMPTY models rather than null when a view leaves
            // them out, so "exists" is the test, not instanceof alone.
            'r' => $role instanceof Role && $role->exists ? (string) UrlUtils::encodeId($role->id) : '',
            'e' => $event instanceof \App\Models\Event && $event->exists ? (string) UrlUtils::encodeId($event->id) : '',
            'u' => $user && ! $isDemo ? (string) UrlUtils::encodeId($user->id) : '',
            'a' => $user && $user->isAdmin() ? 1 : 0,
            'd' => $isDemo ? 1 : 0,
            'em' => $request->embed ? 1 : 0,
            'hb' => self::heartbeatSeconds(),
            't' => self::now()->timestamp,
        ];

        $context['sig'] = self::sign($context);

        return $context;
    }

    /** Context fields that must arrive as strings, and as integers (JSON numbers decode to int). */
    private const SIGNED_STRINGS = ['s', 'p', 'pt', 'r', 'e', 'u'];

    private const SIGNED_INTS = ['a', 'd', 'em', 'hb', 't'];

    /**
     * HMAC over the typed fields, JSON-encoded so no value can be re-split into another field (a
     * "|" in a path could, with the old implode), and prefixed so the key signs nothing else alike.
     */
    public static function sign(array $context): string
    {
        $fields = [];
        foreach (self::SIGNED_STRINGS as $field) {
            $fields[$field] = (string) ($context[$field] ?? '');
        }
        foreach (self::SIGNED_INTS as $field) {
            $fields[$field] = (int) ($context[$field] ?? 0);
        }

        $payload = json_encode($fields, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

        return hash_hmac('sha256', 'realtime-ctx|'.$payload, (string) config('app.key'));
    }

    /**
     * Check a context the browser sent back, returning it with ids decoded, or null.
     *
     * @return array{surface: string, path: string, role_id: ?int, event_id: ?int, user_id: ?int, is_admin: bool, is_demo: bool, is_embed: bool, hb: int}|null
     */
    public static function verify(mixed $context): ?array
    {
        if (! is_array($context) || ! is_string($context['sig'] ?? null)) {
            return null;
        }

        // Every field typed BEFORE signing: sign() casts, and casting an array a client sent
        // ("hb": []) throws, which used to 500 the endpoint and report to Sentry per request.
        foreach (self::SIGNED_STRINGS as $field) {
            if (! is_string($context[$field] ?? null)) {
                return null;
            }
        }
        foreach (self::SIGNED_INTS as $field) {
            if (! is_int($context[$field] ?? null)) {
                return null;
            }
        }

        if (! hash_equals(self::sign($context), $context['sig'])) {
            return null;
        }

        $age = self::now()->timestamp - $context['t'];
        if ($age > self::CONTEXT_MAX_AGE || $age < -300) {
            return null;
        }

        if (! in_array($context['s'], self::SURFACES, true)) {
            return null;
        }

        $decode = fn (string $value) => $value === '' ? null : ((int) UrlUtils::decodeId($value) ?: null);

        return [
            'surface' => $context['s'],
            'path' => mb_substr($context['p'], 0, 255),
            'template' => mb_substr($context['pt'], 0, 255),
            'role_id' => $decode($context['r']),
            'event_id' => $decode($context['e']),
            'user_id' => $decode($context['u']),
            'is_admin' => $context['a'] === 1,
            'is_demo' => $context['d'] === 1,
            'is_embed' => $context['em'] === 1,
            'hb' => max(30, min(600, $context['hb'])),
        ];
    }

    /**
     * The page's path as stored: the request path with any secret route parameter replaced by its
     * name, and never the query string.
     */
    public static function path(Request $request): string
    {
        // mb_scrub: a path like /%FF decodes to invalid UTF-8, which made the json directive emit
        // nothing and left the beacon script as `var ctx = ;`, a parse error no try/catch catches.
        $path = trim(mb_scrub($request->decodedPath(), 'UTF-8'), '/');
        $secrets = [];

        foreach ($request->route()?->parameters() ?? [] as $name => $value) {
            if (is_scalar($value) && (string) $value !== '' && preg_match(self::SECRET_PARAM, $name) === 1) {
                $secrets[(string) $value] = '{'.$name.'}';
            }
        }

        if ($secrets && $path !== '') {
            $path = implode('/', array_map(fn ($segment) => $secrets[$segment] ?? $segment, explode('/', $path)));
        }

        return mb_substr('/'.$path, 0, 255);
    }

    /**
     * path(), but only when the route carries a secret parameter, and null otherwise. Google
     * Analytics sends the page's location, so a ticket, reset or unsubscribe page must report
     * "/ticket/view/{event_id}/{secret}" rather than the link that opens it; every other page,
     * including an edge-cached marketing page, renders null and the browser uses its own path.
     */
    public static function redactedPath(Request $request): ?string
    {
        foreach ($request->route()?->parameters() ?? [] as $name => $value) {
            if (is_scalar($value) && (string) $value !== '' && preg_match(self::SECRET_PARAM, $name) === 1) {
                return self::path($request);
            }
        }

        return null;
    }

    /**
     * The route's template ("/{subdomain}/{tab}", "/ticket/order/{order_id}/{secret}"), or the path
     * when there is no route.
     */
    public static function template(Request $request, string $path): string
    {
        $uri = $request->route()?->uri();

        if (! is_string($uri) || $uri === '') {
            return $path;
        }

        return mb_substr('/'.ltrim(mb_scrub($uri, 'UTF-8'), '/'), 0, 255);
    }

    /**
     * The beacon's URL, without a host. An absolute URL inside @json() is slash-escaped, so
     * ResolveCustomDomain's body rewrite would miss it and a custom-domain page would post
     * cross-origin, which connect-src 'self' blocks. parse_url keeps a sub-path install's prefix.
     */
    public static function beaconUrl(): string
    {
        return parse_url(url('/api/realtime'), PHP_URL_PATH) ?: '/api/realtime';
    }

    /**
     * Only hosted installs sit behind Cloudflare; anywhere else these headers are whatever the
     * client chose to send.
     */
    public static function clientIp(Request $request): string
    {
        if (config('app.hosted') && $request->header('CF-Connecting-IP')) {
            return (string) $request->header('CF-Connecting-IP');
        }

        return (string) $request->ip();
    }

    public static function country(Request $request): ?string
    {
        if (config('app.hosted')) {
            $header = strtoupper((string) $request->header('CF-IPCountry'));
            if (preg_match('/^[A-Z]{2}$/', $header) === 1 && ! in_array($header, ['XX', 'T1'], true)) {
                return $header;
            }
        }

        try {
            $code = app(GeoIpService::class)->lookup(self::clientIp($request));
        } catch (Throwable) {
            return null;
        }

        return is_string($code) && preg_match('/^[A-Za-z]{2}$/', $code) === 1 ? strtoupper($code) : null;
    }

    /**
     * A daily-rotating, one-way key for one browser on one network. Deliberately not
     * PageView::getIpHash(), so these rows cannot be joined to the cache-only dedup keys.
     * Accept-Language is mixed in because phones on one venue wifi otherwise share a key.
     */
    public static function visitorKey(Request $request, ?CarbonInterface $day = null): ?string
    {
        $ip = self::clientIp($request);
        if ($ip === '') {
            return null;
        }

        $day = ($day ?? self::now())->copy()->utc()->format('Y-m-d');
        $material = 'rt|'.self::networkPart($ip).'|'.$request->userAgent().'|'.$request->header('Accept-Language');

        return substr(hash_hmac('sha256', $material, config('app.key').'|'.$day), 0, 16);
    }

    /**
     * Today's key, plus yesterday's in the first half hour of a UTC day so a visit that crosses
     * midnight stays one visitor.
     *
     * @return list<string>
     */
    public static function visitorKeys(Request $request): array
    {
        $now = self::now();
        $keys = [self::visitorKey($request, $now)];

        if ($now->hour === 0 && $now->minute < 30) {
            $keys[] = self::visitorKey($request, $now->subDay());
        }

        return array_values(array_filter($keys));
    }

    /**
     * IPv6 privacy addresses rotate within a /64, which would otherwise mint a new visitor every
     * few hours on the same device.
     */
    private static function networkPart(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $packed = @inet_pton($ip);
            if ($packed !== false) {
                return (string) inet_ntop(substr($packed, 0, 8).str_repeat("\0", 8));
            }
        }

        return $ip;
    }

    /**
     * Classify where a page view came from.
     *
     * Returns ['inherit' => true] when the view says nothing new about the source (an internal,
     * hand-off or empty referrer, a reload, Back), else the channel, the source name and campaign.
     *
     * Not AnalyticsReferrersDaily::categorizeReferrer(): its unanchored patterns send chatgpt.com
     * and microsoft.com to Social (`t\.co`) and gemini.google.com to Search.
     *
     * @return array{inherit: bool, channel?: string, name?: ?string, campaign?: ?string}
     */
    public static function classifySource(?string $referrerHost, array $utm, string $navigation, string $surface, Request $request): array
    {
        $source = strtolower(self::clean($utm['source'] ?? null, 100) ?? '');
        $medium = strtolower(self::clean($utm['medium'] ?? null, 100) ?? '');
        $campaign = self::clean($utm['campaign'] ?? null, 100);
        $host = self::normalizeHost($referrerHost);

        if ($source !== '' || $medium !== '' || $campaign !== null) {
            $sourceHost = self::normalizeHost($source);

            $channel = match (true) {
                $source === 'boost' || in_array($medium, self::PAID_MEDIUMS, true) => 'paid',
                $source === 'newsletter' || in_array($medium, self::EMAIL_MEDIUMS, true) => 'email',
                self::hostIn($sourceHost, self::AI_HOSTS) => 'ai',
                $medium === 'social' || self::hostIn($sourceHost, self::SOCIAL_HOSTS) => 'social',
                default => 'campaign',
            };

            return ['inherit' => false, 'channel' => $channel, 'name' => $source !== '' ? $source : $host, 'campaign' => $campaign];
        }

        if ($host === null || in_array($navigation, ['reload', 'back_forward'], true)
            || self::isInternalHost($host, $request) || self::hostIn($host, self::HANDOFF_HOSTS)) {
            return ['inherit' => true];
        }

        // A signed-in app page is never reached from a social post; facebook.com there is the
        // OAuth dialog handing the visitor back.
        if ($surface === 'ap' && self::hostIn($host, ['facebook.com'])) {
            return ['inherit' => true];
        }

        return ['inherit' => false, 'channel' => self::hostChannel($host) ?? 'other', 'name' => $host, 'campaign' => null];
    }

    /**
     * The channel a referring host belongs to - ai, email, search or social - or null when it is
     * none of the known ones. One table for every surface that classifies referrers (this tracker
     * and the growth payload), so the same visit cannot be "social" on /admin/realtime and
     * something else in an export.
     */
    public static function hostChannel(?string $host): ?string
    {
        $host = self::normalizeHost($host);

        return match (true) {
            $host === null => null,
            self::hostIn($host, self::AI_HOSTS) => 'ai',
            self::hostIn($host, self::EMAIL_HOSTS) => 'email',
            self::hostIn($host, self::GOOGLE_PRODUCT_HOSTS) => null,
            self::hostIn($host, self::SEARCH_HOSTS) => 'search',
            self::hostIn($host, self::SOCIAL_HOSTS) => 'social',
            default => null,
        };
    }

    public static function isInternalHost(string $host, Request $request): bool
    {
        $base = strtolower(_base_domain());
        $appHost = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));

        if (self::hostIn($host, array_filter([$base, $appHost])) || $host === strtolower($request->getHost())) {
            return true;
        }

        $original = $request->attributes->get('custom_domain_host');
        if (is_string($original) && strtolower($original) === $host) {
            return true;
        }

        if (! config('app.hosted')) {
            return false;
        }

        // One cached map of every active custom domain, never a key per host: the host comes from
        // the client, so per-host keys were an unbounded set of cache entries (which the file store
        // never clears) and a query each.
        try {
            $hosts = Cache::remember('realtime_custom_hosts', 600, fn () => Role::whereNotNull('custom_domain_host')
                ->where('custom_domain_status', 'active')
                ->where('is_deleted', false)
                ->pluck('custom_domain_host')
                ->map(fn ($value) => strtolower((string) $value))
                ->flip()
                ->all());
        } catch (Throwable) {
            return false;
        }

        return isset($hosts[$host]);
    }

    public static function normalizeHost(?string $host): ?string
    {
        $host = strtolower(trim((string) $host));
        $host = preg_replace('/^www\./', '', $host);

        return $host !== '' && preg_match('/^[a-z0-9.-]{1,253}$/', $host) === 1 ? $host : null;
    }

    /**
     * Suffix match, so a subdomain counts but a look-alike does not: "l.facebook.com" is
     * facebook.com, "notfacebook.com" is not. "google.*" also matches country domains.
     */
    private static function hostIn(?string $host, array $domains): bool
    {
        if ($host === null) {
            return false;
        }

        foreach ($domains as $domain) {
            if (str_ends_with($domain, '.*')) {
                $stem = preg_quote(substr($domain, 0, -2), '/');
                if (preg_match('/(^|\.)'.$stem.'\.[a-z]{2,3}(\.[a-z]{2})?$/', $host) === 1) {
                    return true;
                }

                continue;
            }

            if ($host === $domain || str_ends_with($host, '.'.$domain)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Client-supplied text, made safe to store and display: no control or bidi-override
     * characters, trimmed and capped. Null when nothing is left.
     */
    public static function clean(mixed $value, int $max): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = preg_replace('/[\x{0000}-\x{001F}\x{007F}-\x{009F}\x{200E}\x{200F}\x{202A}-\x{202E}\x{2066}-\x{2069}]/u', '', (string) $value);
        $value = trim((string) $value);

        return $value === '' ? null : mb_substr($value, 0, $max);
    }

    /**
     * Delete rows past retention, in primary-key batches so a large backlog never takes one long
     * range lock on the table every beacon writes to.
     */
    public static function prune(?int $minutes = null, ?int $maxBatches = null): int
    {
        $cutoff = self::ts(self::now()->subMinutes($minutes ?? self::RETENTION_MINUTES));
        $deleted = 0;
        $batches = 0;

        do {
            $ids = DB::table('realtime_hits')->where('last_seen_at', '<', $cutoff)->orderBy('id')->limit(5000)->pluck('id');

            if ($ids->isNotEmpty()) {
                $deleted += DB::table('realtime_hits')->whereIn('id', $ids->all())->delete();
            }

            $batches++;
        } while ($ids->count() === 5000 && ($maxBatches === null || $batches < $maxBatches));

        return $deleted;
    }

    /**
     * Whether the table exists yet: a selfhost install can run the new code before `migrate`.
     */
    public static function tableExists(): bool
    {
        try {
            return Schema::hasTable('realtime_hits');
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * A throttled prune for the request path: a backstop for a stalled or missing cron, which holds
     * retention only while beacons or admin polls keep arriving (a quiet night leaves rows past the
     * hour until the next one; realtime_prune_stalled flags anything past two). At most two batches:
     * after a long cron outage the catch-up belongs to realtime:prune, not to whichever visitor's
     * beacon happens to come first.
     */
    public static function pruneIfDue(): void
    {
        try {
            if (Cache::add('realtime_prune', 1, 300) && self::tableExists()) {
                self::prune(maxBatches: 2);
            }
        } catch (Throwable $e) {
            self::reportOnce($e);
        }
    }

    /**
     * Run a beacon write. A beacon must never break anything or flood Sentry: concurrency errors are
     * retried, and anything else is reported at most once an hour.
     */
    public static function write(callable $callback): mixed
    {
        $detector = new self;

        for ($attempt = 1; ; $attempt++) {
            try {
                return $callback();
            } catch (Throwable $e) {
                if ($attempt < 3 && DB::transactionLevel() === 0 && $detector->causedByConcurrencyError($e)) {
                    usleep(random_int(20, 60) * 1000 * $attempt);

                    continue;
                }

                self::reportOnce($e);

                return null;
            }
        }
    }

    private static function reportOnce(Throwable $e): void
    {
        try {
            if (Cache::add('realtime_error_reported', 1, 3600)) {
                report($e);
            }
        } catch (Throwable) {
            // Nothing left to do: the beacon is best-effort.
        }
    }

    /**
     * Through now(), so a test's travel() moves it too.
     */
    public static function now(): CarbonImmutable
    {
        return now()->utc()->toImmutable();
    }

    /**
     * A UTC timestamp string for a binding. Every realtime timestamp goes through here: no
     * connection timezone is configured, so SQL NOW() and a PHP Carbon in APP_TIMEZONE could
     * disagree by hours on a selfhost install.
     */
    public static function ts(CarbonInterface $time): string
    {
        return $time->copy()->utc()->format('Y-m-d H:i:s');
    }
}
