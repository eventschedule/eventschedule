<?php

namespace App\Services\Feeds;

use App\Utils\UrlUtils;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;

/**
 * One guarded GET of an address a feed reads: the feed itself, a page of it, or the page of
 * one of its items.
 *
 * Through UrlUtils, like every outbound fetch: the address is one this server may ask, every
 * redirect is checked again and pinned to the address that was checked, and the body is cut off
 * at the guard's cap.
 *
 * What is particular to a reader that comes back every hour:
 *
 *  - It asks politely. The validators the server gave last time go back with the request, and a
 *    304 is an answer ("nothing changed"), not a failure. When the server says how long to stay
 *    away (Retry-After), that is passed on.
 *  - It never puts the address in anything that leaves the process. An address a schedule's
 *    owner gave us is a credential in its path. The outcome is a reason key and a status code;
 *    an exception nobody expected is reported as its class and the HOST, built fresh, without
 *    the original as `previous`: a transport's own message ends "... for https://host/path".
 */
class FeedFetcher
{
    public const OK = 'ok';

    public const NOT_MODIFIED = 'not_modified';

    /** Timed out, refused, unresolvable, or an address the guard will not ask. One answer on purpose: saying which would tell the asker what answers on the inside. */
    public const UNREACHABLE = 'unreachable';

    public const HTTP_ERROR = 'http_error';

    public const TOO_LARGE = 'too_large';

    public const FAILED = 'failed';

    /** A feed or a page bigger than this is not read. */
    public const MAX_BYTES = 3 * 1024 * 1024;

    private const SECONDS = 15;

    /** The longest a server's Retry-After is taken at its word. */
    private const MAX_RETRY_AFTER = 86400;

    public function get(string $url, ?string $etag = null, ?string $lastModified = null, int $seconds = self::SECONDS): FetchResult
    {
        $headers = [
            'User-Agent' => 'EventSchedule/1.0',
            'Accept' => 'text/calendar, application/feed+json, application/atom+xml, application/rss+xml, application/json;q=0.9, application/xml;q=0.9, text/html;q=0.8, */*;q=0.5',
            'Accept-Language' => 'en-US,en;q=0.5',
        ];

        if ($etag !== null && $etag !== '') {
            $headers['If-None-Match'] = $etag;
        }
        if ($lastModified !== null && $lastModified !== '') {
            $headers['If-Modified-Since'] = $lastModified;
        }

        try {
            $followed = UrlUtils::safeHttpGetWithUrl($url, $headers, $seconds, 4);
        } catch (ConnectionException $e) {
            // The other side is down, slow or gone. Routine, and not ours to report.
            return new FetchResult(self::UNREACHABLE);
        } catch (RequestException $e) {
            // Stopped by the guard's size cap: a stated length over it, or a body that ran past.
            if (in_array($e->getHandlerContext()['errno'] ?? null, [CURLE_WRITE_ERROR, CURLE_FILESIZE_EXCEEDED], true)) {
                return new FetchResult(self::TOO_LARGE);
            }

            $this->reportWithoutAddress($e, $url);

            return new FetchResult(self::FAILED);
        } catch (\Throwable $e) {
            $this->reportWithoutAddress($e, $url);

            return new FetchResult(self::FAILED);
        }

        $response = $followed['response'];

        if (! $response instanceof Response) {
            return new FetchResult(self::UNREACHABLE);
        }

        if ($response->status() === 304) {
            return new FetchResult(self::NOT_MODIFIED, 304, url: (string) $followed['url']);
        }

        if (! $response->successful()) {
            return new FetchResult(self::HTTP_ERROR, $response->status(), retryAfter: $this->retryAfter($response));
        }

        $body = $response->body();

        if (strlen($body) > self::MAX_BYTES) {
            return new FetchResult(self::TOO_LARGE, $response->status());
        }

        return new FetchResult(
            self::OK,
            $response->status(),
            $body,
            strtolower((string) $response->header('Content-Type')),
            (string) $followed['url'],
            $this->validator($response->header('ETag')),
            $this->validator($response->header('Last-Modified')),
        );
    }

    /** A validator as it will be sent back, or null: one too long for its column is not kept in part. */
    private function validator(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' || strlen($value) > 255 ? null : $value;
    }

    /** Retry-After as seconds from now: it comes as a number of seconds or as a date. */
    private function retryAfter(Response $response): ?int
    {
        $value = trim((string) $response->header('Retry-After'));

        if ($value === '') {
            return null;
        }

        if (ctype_digit($value)) {
            $seconds = (int) $value;
        } else {
            $at = strtotime($value);
            if ($at === false) {
                return null;
            }
            $seconds = $at - time();
        }

        return max(0, min($seconds, self::MAX_RETRY_AFTER));
    }

    private function reportWithoutAddress(\Throwable $e, string $url): void
    {
        $host = (string) (parse_url($url, PHP_URL_HOST) ?: 'unknown host');
        $errno = $e instanceof RequestException ? ($e->getHandlerContext()['errno'] ?? null) : null;

        report(new \RuntimeException(
            'A feed could not be fetched from '.$host.': '.class_basename($e).($errno !== null ? ' (curl '.$errno.')' : '')
        ));
    }
}
