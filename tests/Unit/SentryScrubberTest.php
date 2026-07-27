<?php

namespace Tests\Unit;

use App\Utils\SentryScrubber;
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

    /** The wiring is what makes the scrub run; a correct helper nobody calls protects nothing. */
    public function test_it_is_registered_for_both_event_types(): void
    {
        foreach (['sentry.before_send', 'sentry.before_send_transaction'] as $key) {
            $callback = config($key);
            $this->assertSame([SentryScrubber::class, 'beforeSend'], $callback, $key.' must be wired');
            $this->assertIsCallable($callback, 'Sentry validates these with is_callable()');
        }
    }
}
