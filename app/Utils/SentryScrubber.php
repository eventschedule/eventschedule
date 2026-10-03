<?php

namespace App\Utils;

use Sentry\Breadcrumb;
use Sentry\Event;

/**
 * Keeps URL secrets, the cron secret, bearer tokens, email addresses in links and visitors' IP
 * addresses out of Sentry.
 *
 * Secrets in the current route (every parameter RealtimeTracker::SECRET_PARAM matches: ticket,
 * order, gift card, installment and feedback secrets, password-reset, confirm and unsubscribe
 * tokens, verification hashes) are replaced wherever they appear in the event. `?email=` and
 * `?sig=` are blanked like `?secret=`: sign-up, login and unsubscribe links carry an address there.
 *
 * The guest booking surfaces (view, cancel, pay, ical, reschedule) authenticate on a 32-char secret in
 * the URL PATH, so an error on any of them would otherwise ship a working link to a stranger's booking
 * into the issue tracker. Four separate fields carry it: the request url, the Referer header, the
 * transaction name, and the exception message (Symfony's MethodNotAllowedHttpException quotes the raw
 * path back verbatim).
 *
 * Every field that can carry the secret is walked, not just the obvious ones: the request url,
 * query string, headers, cookies and body; the transaction name; the message; exception values;
 * breadcrumbs (message AND metadata); extra; and tags. A field left out is a field that ships the
 * credential, which is how breadcrumbs defeated this class for as long as they were skipped.
 *
 * Wired as `before_send` in config/sentry.php as a static-method STRING rather than a closure: a closure
 * in a config file breaks `php artisan config:cache`, which docs/SECURITY_CONFIG.md tells selfhosters
 * to run.
 */
class SentryScrubber
{
    /**
     * `appointment/<action>/<event hash>/<32-char secret>`, with or without a trailing segment.
     *
     * Anchored on the two segments before it rather than matching any 32-char run, so an encoded id or
     * a hash elsewhere in the path is left readable for debugging. The leading slash is optional
     * because Symfony's MethodNotAllowedHttpException quotes the path without one ("for route
     * appointment/reschedule/...").
     */
    private const SECRET_PATH = '#(\bappointment/[^/?\s]+/[^/?\s]+/)[a-z0-9]{32}#i';

    /**
     * `?secret=...` / `&secret=...` on the cron endpoints (/translate_data, /release_tickets).
     *
     * APP_CRON_SECRET travels in the query string, and Sentry's request integration always stamps
     * `url` and `query_string` - send_default_pii does not gate those. So without this, ANY
     * exception escaping the chain after the auth check ships a live credential to the issue
     * tracker. Worse for selfhosters: config/sentry.php points REPORT_ERRORS=true installs at an
     * upstream eventschedule.com DSN, so their secret would land in someone else's project.
     *
     * Anchored on ^ as well as [?&]: Sentry stamps `query_string` with the BARE string, no leading
     * question mark, so a pattern requiring a separator silently misses the FIRST parameter - which
     * is where ?secret= normally sits. It would then scrub `url` and leave `query_string` intact,
     * i.e. still ship the credential.
     *
     * Matches the value up to the next separator so a following parameter stays readable.
     */
    private const SECRET_QUERY = '#((?:^|[?&])(?:secret|token|api_key|email|sig)=)[^&\s"\']+#i';

    /**
     * Request headers that carry the visitor's real IP address. Sentry's own sanitiser drops
     * X-Forwarded-For and X-Real-IP while send_default_pii is off, but not the ones Cloudflare
     * adds in front of hosted, which would otherwise put every visitor's address in every report.
     */
    private const IP_HEADERS = ['cf-connecting-ip', 'cf-connecting-ipv6', 'true-client-ip', 'x-forwarded-for', 'x-real-ip'];

    /**
     * The current request's secret route parameters (RealtimeTracker::SECRET_PARAM: ticket and
     * order secrets, reset, confirm and unsubscribe tokens, verification hashes), value to
     * placeholder, for the duration of one beforeSend(). Read from the matched route rather than
     * kept as a list of URL shapes, so a new secret route is covered the day it is added.
     *
     * @var array<string, string>
     */
    private static array $routeSecrets = [];

    /**
     * `Authorization: Bearer ...`, which carries GROWTH_DATA_TOKEN to /api/internal/growth.
     *
     * Sentry already drops the Authorization header while send_default_pii is off (its
     * RequestIntegration default), so this is a backstop for an operator who turns that on: the
     * header value then reaches `headers` here, which scrubDeep() walks.
     */
    private const BEARER = '#(\bBearer\s+)[^\s,"\']+#i';

    public static function beforeSend(Event $event): ?Event
    {
        self::$routeSecrets = self::routeSecrets();

        try {
            return self::scrubEvent($event);
        } finally {
            self::$routeSecrets = [];
        }
    }

    private static function scrubEvent(Event $event): Event
    {
        $request = $event->getRequest();

        if (isset($request['headers']) && is_array($request['headers'])) {
            $request['headers'] = array_filter(
                $request['headers'],
                fn ($name) => ! in_array(strtolower((string) $name), self::IP_HEADERS, true),
                ARRAY_FILTER_USE_KEY
            );
        }

        // Walked recursively, because these are NOT all flat strings and guessing wrong makes the whole
        // scrub a silent no-op. RequestIntegration builds `headers` from PSR-7 getHeaders(), which
        // returns array<string, string[]> - an earlier is_string() check at this level therefore never
        // matched and Referer, the widest-blast-radius leak of the four, went out untouched. `cookies` is
        // string=>string and `data` is the decoded body, which can nest arbitrarily.
        foreach (['url', 'query_string', 'headers', 'cookies', 'data'] as $key) {
            if (isset($request[$key])) {
                $request[$key] = self::scrubDeep($request[$key]);
            }
        }

        $event->setRequest($request);

        if ($transaction = $event->getTransaction()) {
            $event->setTransaction(self::scrub($transaction));
        }

        if ($message = $event->getMessage()) {
            $formatted = $event->getMessageFormatted();
            $event->setMessage(
                self::scrub($message),
                $event->getMessageParams(),
                $formatted === null ? null : self::scrub($formatted)
            );
        }

        foreach ($event->getExceptions() as $exception) {
            $exception->setValue(self::scrub($exception->getValue()));
        }

        // Breadcrumbs, which are attached to the event BEFORE before_send runs and were the widest
        // remaining hole. config/sentry.php enables `logs` and `http_client_requests` by default,
        // so every \Log:: line and every outbound request URL rides along with the event - and
        // AppController::translateData() logs 'Scheduled command X failed: ...' for every command
        // on the rail whose secret this class exists to protect. Scrubbing url, message and
        // exception value while leaving these untouched shipped the credential anyway.
        //
        // Rebuilt rather than mutated: Breadcrumb is immutable, and withMessage() is typed string
        // while getMessage() is nullable, so the null case has to be skipped rather than coerced.
        $event->setBreadcrumb(array_map(static function (Breadcrumb $crumb): Breadcrumb {
            $message = $crumb->getMessage();

            if ($message !== null) {
                $crumb = $crumb->withMessage(self::scrub($message));
            }

            // Metadata, not just the message: an http_client_requests breadcrumb carries the URL
            // there, which is where an appointment secret actually travels.
            foreach ($crumb->getMetadata() as $key => $value) {
                $scrubbed = self::scrubDeep($value);

                if ($scrubbed !== $value) {
                    $crumb = $crumb->withMetadata($key, $scrubbed);
                }
            }

            return $crumb;
        }, $event->getBreadcrumbs()));

        // extra and tags are free text the app sets itself, and cost nothing to walk.
        $event->setExtra(self::scrubDeep($event->getExtra()));
        $event->setTags(self::scrubDeep($event->getTags()));

        return $event;
    }

    /**
     * Scrub a string, or every string nested anywhere inside an array. Non-strings are returned as-is,
     * so an int header value stays an int rather than being stringified.
     *
     * @param  mixed  $value
     * @return mixed
     */
    private static function scrubDeep($value)
    {
        if (is_string($value)) {
            return self::scrub($value);
        }

        if (is_array($value)) {
            return array_map([self::class, 'scrubDeep'], $value);
        }

        return $value;
    }

    /**
     * @return array<string, string>
     */
    private static function routeSecrets(): array
    {
        try {
            $route = app()->bound('request') ? request()->route() : null;
        } catch (\Throwable) {
            return [];
        }

        $secrets = [];

        foreach ($route?->parameters() ?? [] as $name => $value) {
            // Eight characters at least, so a short value cannot blank out ordinary text that
            // happens to contain it.
            if (is_scalar($value) && strlen((string) $value) >= 8 && preg_match(RealtimeTracker::SECRET_PARAM, (string) $name) === 1) {
                $secrets[(string) $value] = '['.$name.']';
            }
        }

        return $secrets;
    }

    public static function scrub(string $value): string
    {
        if (self::$routeSecrets) {
            $value = strtr($value, self::$routeSecrets);
        }

        // Email addresses anywhere: a mail transport quoting the recipient back in an exception
        // message, a log breadcrumb, a URL. The domain is kept for debugging.
        $value = mask_emails($value);

        $value = preg_replace(self::SECRET_PATH, '$1[secret]', $value) ?? $value;
        $value = preg_replace(self::BEARER, '$1[secret]', $value) ?? $value;

        return preg_replace(self::SECRET_QUERY, '$1[secret]', $value) ?? $value;
    }
}
