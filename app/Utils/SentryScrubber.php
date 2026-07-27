<?php

namespace App\Utils;

use Sentry\Event;

/**
 * Keeps appointment booking secrets out of Sentry.
 *
 * The guest booking surfaces (view, cancel, pay, ical, reschedule) authenticate on a 32-char secret in
 * the URL PATH, so an error on any of them would otherwise ship a working link to a stranger's booking
 * into the issue tracker. Four separate fields carry it: the request url, the Referer header, the
 * transaction name, and the exception message (Symfony's MethodNotAllowedHttpException quotes the raw
 * path back verbatim).
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

    public static function beforeSend(Event $event): ?Event
    {
        $request = $event->getRequest();

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

    public static function scrub(string $value): string
    {
        return preg_replace(self::SECRET_PATH, '$1[secret]', $value) ?? $value;
    }
}
