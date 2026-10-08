<?php

namespace Tests\Unit;

use App\Utils\SentryScrubber;
use Sentry\Breadcrumb;
use Sentry\Event as SentryEvent;
use Sentry\ExceptionDataBag;
use Tests\TestCase;

class SentryScrubberTest extends TestCase
{
    private const SECRET = 'a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6';

    private const URL = 'https://x.test/appointment/view/Qk9/a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6';

    public function test_it_scrubs_the_secret_from_a_booking_url(): void
    {
        $this->assertSame(
            'https://x.test/appointment/view/Qk9/[secret]',
            SentryScrubber::scrub(self::URL)
        );
    }

    /** The reschedule slots endpoint puts the secret mid-path, not last. */
    public function test_it_scrubs_a_secret_that_is_not_the_final_segment(): void
    {
        $this->assertSame(
            '/appointment/reschedule/Qk9/[secret]/slots',
            SentryScrubber::scrub('/appointment/reschedule/Qk9/'.self::SECRET.'/slots')
        );
    }

    /**
     * GROWTH_DATA_TOKEN travels as a bearer token. Sentry drops Authorization while
     * send_default_pii is off; this is the backstop for an install that turns it on, where the
     * header arrives PSR-7 shaped (an array of strings).
     */
    public function test_it_scrubs_a_bearer_token_from_an_authorization_header(): void
    {
        $event = SentryEvent::createEvent();
        $event->setRequest(['url' => 'https://x.test/api/internal/growth', 'headers' => [
            'Authorization' => ['Bearer '.self::SECRET.self::SECRET],
            'Host' => ['x.test'],
        ]]);

        $request = SentryScrubber::beforeSend($event)->getRequest();

        $this->assertSame(['Bearer [secret]'], $request['headers']['Authorization']);
        $this->assertStringNotContainsString(self::SECRET, json_encode($request));
    }

    public function test_it_leaves_other_paths_readable(): void
    {
        foreach (['/appointment/checkout/success/Qk9', '/venue/events/Qk9', '/'.self::SECRET] as $path) {
            $this->assertSame($path, SentryScrubber::scrub($path), $path.' should be left alone');
        }
    }

    /**
     * Built from the shape Sentry actually produces, which is the whole point of this test.
     *
     * RequestIntegration fills `headers` from PSR-7 getHeaders() - array<string, string[]>, NOT
     * string=>string - and `cookies` from getCookieParams(). An earlier version of this test used a bare
     * `['Referer' => $url]` string, which made a top-level is_string() check look correct while the real
     * Referer went out unscrubbed. Assert against the whole encoded request rather than field by field,
     * so a sink added later cannot slip through unnoticed.
     */
    public function test_it_scrubs_every_shape_sentry_actually_sends(): void
    {
        $event = SentryEvent::createEvent();
        $event->setRequest([
            'url' => self::URL,
            'method' => 'GET',
            'query_string' => '',
            // Arrays of strings, per PSR-7.
            'headers' => ['Referer' => [self::URL], 'Host' => ['x.test'], 'X-Count' => [3]],
            // Written before CaptureUtmParameters learned to skip /appointment/*, so a live cookie can
            // still hold the path for 30 days.
            'cookies' => ['utm_landing_page' => 'appointment/view/Qk9/'.self::SECRET],
            // Decoded request body, arbitrarily nested.
            'data' => ['nested' => ['from' => self::URL]],
        ]);
        $event->setTransaction(self::URL);
        $event->setMessage(self::URL, [], self::URL);
        $event->setExceptions([new ExceptionDataBag(new \RuntimeException(
            'The GET method is not supported for route appointment/reschedule/Qk9/'.self::SECRET
        ))]);

        SentryScrubber::beforeSend($event);

        $this->assertStringNotContainsString(
            self::SECRET,
            json_encode($event->getRequest()),
            'the secret must be absent from EVERY field of the request, not just the url'
        );
        $this->assertStringNotContainsString(self::SECRET, (string) $event->getTransaction());
        $this->assertStringNotContainsString(self::SECRET, (string) $event->getMessage());
        $this->assertStringNotContainsString(self::SECRET, (string) $event->getMessageFormatted());
        $this->assertStringNotContainsString(self::SECRET, $event->getExceptions()[0]->getValue());

        // Types and keys survive: a non-string header value must not be stringified.
        $this->assertSame(3, $event->getRequest()['headers']['X-Count'][0]);
        $this->assertSame(['x.test'], $event->getRequest()['headers']['Host']);
        $this->assertSame('GET', $event->getRequest()['method']);
    }

    /**
     * Transactions carry request.url too and dispatch to a different callback, so the same method has to
     * survive one: a transaction has no exceptions and no message, and those loops must simply not fire.
     */
    public function test_it_survives_a_transaction_event(): void
    {
        $transaction = SentryEvent::createTransaction();
        $transaction->setRequest(['url' => self::URL]);

        $returned = SentryScrubber::beforeSend($transaction);

        $this->assertNotNull($returned, 'returning null would DROP the event');
        $this->assertStringNotContainsString(self::SECRET, $returned->getRequest()['url']);
    }

    /**
     * APP_CRON_SECRET travels in the query string of /translate_data and /release_tickets, and
     * Sentry's request integration always stamps url and query_string regardless of
     * send_default_pii. So any exception escaping the cron chain after its auth check would ship a
     * live credential - and for a selfhoster with REPORT_ERRORS=true, ship it to the UPSTREAM
     * eventschedule.com project rather than their own.
     *
     * The bare form is the one that matters: Sentry writes query_string WITHOUT a leading question
     * mark, so a pattern anchored only on [?&] scrubs the url and leaves query_string intact.
     */
    public function test_it_scrubs_a_cron_secret_from_a_query_string(): void
    {
        $this->assertSame('secret=[secret]&x=1', SentryScrubber::scrub('secret=s3cr3t-live&x=1'));
        $this->assertSame('?secret=[secret]&x=1', SentryScrubber::scrub('?secret=s3cr3t-live&x=1'));
        $this->assertSame('x=1&secret=[secret]', SentryScrubber::scrub('x=1&secret=s3cr3t-live'));
        $this->assertSame('token=[secret]', SentryScrubber::scrub('token=s3cr3t-live'));
        $this->assertSame(
            'https://e.com/translate_data?secret=[secret]',
            SentryScrubber::scrub('https://e.com/translate_data?secret=s3cr3t-live')
        );
    }

    /** The anchor must not turn any parameter ending in "secret" into a false positive. */
    public function test_it_leaves_unrelated_parameters_readable(): void
    {
        $this->assertSame('notasecret=keepme', SentryScrubber::scrub('notasecret=keepme'));
        $this->assertSame('x=1&page=2', SentryScrubber::scrub('x=1&page=2'));
    }

    /** The wiring is what makes the scrub run; a correct helper nobody calls protects nothing. */
    public function test_it_is_registered_for_both_event_types(): void
    {
        foreach (['sentry.before_send', 'sentry.before_send_transaction'] as $key) {
            $callback = config($key);
            $this->assertSame([SentryScrubber::class, 'beforeSend'], $callback, $key.' must be wired');
            $this->assertIsCallable($callback, 'Sentry validates these with is_callable()');
        }
    }

    /**
     * Breadcrumbs are attached to the event before before_send runs, and config/sentry.php enables
     * log breadcrumbs by default - so AppController::translateData()'s
     * "Scheduled command X failed: ..." lines ride along with every exception on the very rail
     * whose secret this class protects. Scrubbing url and query_string while leaving these alone
     * shipped the credential anyway.
     */
    public function test_it_scrubs_a_secret_from_a_log_breadcrumb(): void
    {
        $event = SentryEvent::createEvent();
        $event->setBreadcrumb([
            new Breadcrumb(
                Breadcrumb::LEVEL_ERROR,
                Breadcrumb::TYPE_DEFAULT,
                'log',
                'Scheduled command failed for /translate_data?secret=s3cr3t-live&x=1'
            ),
        ]);

        $message = SentryScrubber::beforeSend($event)->getBreadcrumbs()[0]->getMessage();

        $this->assertStringNotContainsString('s3cr3t-live', $message);
        $this->assertStringContainsString('secret=[secret]', $message);
        $this->assertStringContainsString('x=1', $message, 'the rest of the line stays readable');
    }

    /**
     * An http_client_requests breadcrumb carries the URL in metadata, not in the message. On our
     * own host the path stays, with the secret out of it: our route paths are what make a report
     * readable.
     */
    public function test_it_scrubs_a_booking_secret_from_breadcrumb_metadata(): void
    {
        $url = rtrim(config('app.url'), '/').'/appointment/view/abc123/'.str_repeat('a', 32);

        $event = SentryEvent::createEvent();
        $event->setBreadcrumb([
            (new Breadcrumb(Breadcrumb::LEVEL_INFO, Breadcrumb::TYPE_HTTP, 'http'))
                ->withMetadata('url', $url),
        ]);

        $scrubbed = SentryScrubber::beforeSend($event)->getBreadcrumbs()[0]->getMetadata()['url'];

        $this->assertStringNotContainsString(str_repeat('a', 32), $scrubbed);
        $this->assertStringContainsString('/appointment/view/abc123/[secret]', $scrubbed);
    }

    private function outbound(string $url, string $query = '', string $fragment = ''): Breadcrumb
    {
        // The three keys Sentry's HTTP client integration records for a request.
        return (new Breadcrumb(Breadcrumb::LEVEL_INFO, Breadcrumb::TYPE_HTTP, 'http'))
            ->withMetadata('url', $url)
            ->withMetadata('http.query', $query)
            ->withMetadata('http.fragment', $fragment)
            ->withMetadata('http.request.method', 'GET');
    }

    private function scrubbedCrumb(Breadcrumb $crumb): array
    {
        $event = SentryEvent::createEvent();
        $event->setBreadcrumb([$crumb]);

        return SentryScrubber::beforeSend($event)->getBreadcrumbs()[0]->getMetadata();
    }

    /**
     * An address a schedule's owner gave us to read is a credential in the path, in a shape no
     * pattern can know. A request to somebody else's server keeps the host and loses the rest.
     */
    public function test_a_request_to_somebody_elses_server_keeps_its_host_and_nothing_else(): void
    {
        foreach ([
            'https://calendar.google.com/calendar/ical/someone%40gmail.com/private-0123456789abcdef/basic.ics' => 'https://calendar.google.com/[path]',
            'https://app.jolioo.com/rss/Zx81TokenThatOpensTheFeed' => 'https://app.jolioo.com/[path]',
            'http://feeds.example.org:8443/a/b' => 'http://feeds.example.org:8443/[path]',
        ] as $url => $expected) {
            $metadata = $this->scrubbedCrumb($this->outbound($url, 'key=Zx81&page=2', 'frag'));

            $this->assertSame($expected, $metadata['url']);
            $this->assertSame('[query]', $metadata['http.query']);
            $this->assertSame('[fragment]', $metadata['http.fragment']);
            $this->assertSame('GET', $metadata['http.request.method'], 'what is not an address stays');
        }

        // Nothing to hide in an address that is only a host, and nothing invented for it.
        $bare = $this->scrubbedCrumb($this->outbound('https://example.org/'));
        $this->assertSame('https://example.org/', $bare['url']);
        $this->assertSame('', $bare['http.query']);
    }

    /** Our own host, and a schedule's subdomain of it. A lookalike that only ends in our letters is not ours. */
    public function test_a_request_to_our_own_host_keeps_its_path(): void
    {
        $host = parse_url(config('app.url'), PHP_URL_HOST);

        foreach (['https://'.$host.'/api/internal/growth', 'https://venue.'.$host.'/events/jazz-night'] as $url) {
            $metadata = $this->scrubbedCrumb($this->outbound($url, 'page=2'));

            $this->assertSame($url, $metadata['url']);
            $this->assertSame('page=2', $metadata['http.query']);
        }

        $this->assertSame('https://not'.$host.'/[path]', $this->scrubbedCrumb($this->outbound('https://not'.$host.'/rss/token'))['url']);
    }

    /** Only a request's own breadcrumb is cut down: a log line is text, and is scrubbed as text. */
    public function test_a_breadcrumb_that_is_not_a_request_is_left_to_the_patterns(): void
    {
        $crumb = (new Breadcrumb(Breadcrumb::LEVEL_INFO, Breadcrumb::TYPE_DEFAULT, 'log', 'Read https://example.org/a/b?secret=s3cr3t-live'))
            ->withMetadata('url', 'https://example.org/a/b');

        $event = SentryEvent::createEvent();
        $event->setBreadcrumb([$crumb]);
        $scrubbed = SentryScrubber::beforeSend($event)->getBreadcrumbs()[0];

        $this->assertSame('https://example.org/a/b', $scrubbed->getMetadata()['url']);
        $this->assertSame('Read https://example.org/a/b?secret=[secret]', $scrubbed->getMessage());
    }

    /** A breadcrumb with no message at all must not blow up: withMessage() is typed string. */
    public function test_a_breadcrumb_without_a_message_survives(): void
    {
        $event = SentryEvent::createEvent();
        $event->setBreadcrumb([
            new Breadcrumb(Breadcrumb::LEVEL_INFO, Breadcrumb::TYPE_DEFAULT, 'cache'),
        ]);

        $this->assertNull(SentryScrubber::beforeSend($event)->getBreadcrumbs()[0]->getMessage());
    }

    public function test_it_scrubs_extra_and_tags(): void
    {
        $event = SentryEvent::createEvent();
        $event->setExtra(['url' => '/translate_data?secret=s3cr3t-live']);
        $event->setTags(['endpoint' => 'release_tickets?secret=s3cr3t-live']);

        $scrubbed = SentryScrubber::beforeSend($event);

        $this->assertStringNotContainsString('s3cr3t-live', $scrubbed->getExtra()['url']);
        $this->assertStringNotContainsString('s3cr3t-live', $scrubbed->getTags()['endpoint']);
    }
}
