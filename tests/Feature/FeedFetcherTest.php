<?php

namespace Tests\Feature;

use App\Services\Feeds\FeedFetcher;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request as PsrRequest;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * One guarded GET of an address a feed reads.
 *
 * It asks politely (the validators go back, a 304 is an answer, Retry-After is passed on), and
 * nothing it hands back or reports can carry the address: for a calendar's private link or a
 * provider's feed the address is the credential, and a transport's own message ends with it.
 *
 * Addresses are IP literals, so nothing resolves a name, and every one is faked.
 */
class FeedFetcherTest extends TestCase
{
    private const FEED = 'https://93.184.216.34/rss/Zx81TokenThatOpensTheFeed';

    private const SECRET = 'Zx81TokenThatOpensTheFeed';

    /** @var list<\Throwable> */
    private array $reported = [];

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();

        // What would be sent to the error tracker, kept instead.
        $this->app->make(ExceptionHandler::class)->reportable(function (\Throwable $e) {
            $this->reported[] = $e;

            return false;
        });
    }

    private function fetcher(): FeedFetcher
    {
        return new FeedFetcher;
    }

    public function test_a_good_answer_comes_back_with_what_the_next_request_sends(): void
    {
        Http::fake(['93.184.216.34/*' => Http::response('<rss/>', 200, [
            'Content-Type' => 'Application/RSS+XML; charset=UTF-8',
            'ETag' => 'W/"abc123"',
            'Last-Modified' => 'Sat, 09 May 2026 17:30:00 GMT',
        ])]);

        $result = $this->fetcher()->get(self::FEED);

        $this->assertTrue($result->ok());
        $this->assertSame(200, $result->httpStatus);
        $this->assertSame('<rss/>', $result->body);
        $this->assertSame('application/rss+xml; charset=utf-8', $result->contentType);
        $this->assertSame(self::FEED, $result->url);
        $this->assertSame('W/"abc123"', $result->etag);
        $this->assertSame('Sat, 09 May 2026 17:30:00 GMT', $result->lastModified);

        Http::assertSent(fn ($request) => ! $request->hasHeader('If-None-Match') && ! $request->hasHeader('If-Modified-Since'));
    }

    public function test_the_validators_go_back_and_nothing_changed_is_an_answer(): void
    {
        Http::fake(['93.184.216.34/*' => Http::response('', 304)]);

        $result = $this->fetcher()->get(self::FEED, 'W/"abc123"', 'Sat, 09 May 2026 17:30:00 GMT');

        $this->assertTrue($result->unchanged());
        $this->assertFalse($result->ok());
        $this->assertSame(FeedFetcher::NOT_MODIFIED, $result->status);
        Http::assertSent(fn ($request) => $request->header('If-None-Match') === ['W/"abc123"']
            && $request->header('If-Modified-Since') === ['Sat, 09 May 2026 17:30:00 GMT']);
    }

    public function test_a_refusal_carries_its_code_and_how_long_to_stay_away(): void
    {
        Http::fake([
            '93.184.216.34/seconds' => Http::response('slow down', 429, ['Retry-After' => '120']),
            '93.184.216.34/date' => Http::response('down', 503, ['Retry-After' => gmdate('D, d M Y H:i:s', time() + 600).' GMT']),
            '93.184.216.34/week' => Http::response('down', 503, ['Retry-After' => '604800']),
            '93.184.216.34/nonsense' => Http::response('down', 503, ['Retry-After' => 'soon']),
            '93.184.216.34/gone' => Http::response('gone', 410),
        ]);

        $seconds = $this->fetcher()->get('https://93.184.216.34/seconds');
        $this->assertSame([FeedFetcher::HTTP_ERROR, 429, 120], [$seconds->status, $seconds->httpStatus, $seconds->retryAfter]);
        $this->assertSame('', $seconds->body);

        $this->assertEqualsWithDelta(600, $this->fetcher()->get('https://93.184.216.34/date')->retryAfter, 5);
        // A server may ask for a week. It is taken at its word for a day.
        $this->assertSame(86400, $this->fetcher()->get('https://93.184.216.34/week')->retryAfter);
        $this->assertNull($this->fetcher()->get('https://93.184.216.34/nonsense')->retryAfter);

        $gone = $this->fetcher()->get('https://93.184.216.34/gone');
        $this->assertSame([FeedFetcher::HTTP_ERROR, 410, null], [$gone->status, $gone->httpStatus, $gone->retryAfter]);
    }

    public function test_a_body_over_the_limit_is_not_read(): void
    {
        Http::fake([
            '93.184.216.34/big' => Http::response(str_repeat('x', FeedFetcher::MAX_BYTES + 1), 200),
            '93.184.216.34/fits' => Http::response(str_repeat('x', FeedFetcher::MAX_BYTES), 200),
        ]);

        $big = $this->fetcher()->get('https://93.184.216.34/big');
        $this->assertSame(FeedFetcher::TOO_LARGE, $big->status);
        $this->assertSame('', $big->body);
        $this->assertTrue($this->fetcher()->get('https://93.184.216.34/fits')->ok());
    }

    public function test_an_address_this_server_must_not_ask_reads_as_unreachable_and_is_never_asked(): void
    {
        // Faked so that it is the guard, not the absence of a fake, that keeps them unasked.
        Http::fake(['*' => Http::response('<rss/>', 200)]);

        foreach (['http://127.0.0.1/feed', 'http://169.254.169.254/latest/meta-data/', 'http://10.0.0.5/feed', 'ftp://93.184.216.34/feed', 'not an address'] as $address) {
            $this->assertSame(FeedFetcher::UNREACHABLE, $this->fetcher()->get($address)->status, $address);
        }

        Http::assertNothingSent();
        $this->assertSame([], $this->reported);
    }

    /** The other side being down is routine: an answer, and nothing for the error tracker. */
    public function test_a_server_that_cannot_be_reached_is_an_answer_and_not_a_report(): void
    {
        Http::fake(fn () => throw new ConnectionException('cURL error 28: Operation timed out after 15001 milliseconds for '.self::FEED));

        $this->assertSame(FeedFetcher::UNREACHABLE, $this->fetcher()->get(self::FEED)->status);
        $this->assertSame([], $this->reported);
    }

    /**
     * What nobody expected is reported, and the report is built fresh: the class of what went
     * wrong and the host, never the original exception, whose message ends with the address.
     */
    public function test_what_is_reported_names_the_host_and_never_the_address(): void
    {
        Http::fake(fn () => throw new RequestException(
            'cURL error 35: TLS handshake failed for '.self::FEED,
            new PsrRequest('GET', self::FEED),
            null,
            null,
            ['errno' => 35]
        ));

        $result = $this->fetcher()->get(self::FEED);

        $this->assertSame(FeedFetcher::FAILED, $result->status);
        $this->assertCount(1, $this->reported);

        $report = $this->reported[0];
        $this->assertSame('A feed could not be fetched from 93.184.216.34: RequestException (curl 35)', $report->getMessage());
        $this->assertNull($report->getPrevious(), 'the original exception rides along, and its message has the address');
        $this->assertStringNotContainsString(self::SECRET, $report->getMessage().$report->getTraceAsString());
        $this->assertStringNotContainsString(self::SECRET, json_encode($result));
    }

    /** The guard cuts a body off at its own cap, which reaches us as a transport error. */
    public function test_a_body_the_guard_cut_off_is_too_large_and_not_a_report(): void
    {
        Http::fake(fn () => throw new RequestException(
            'cURL error 23: Failure writing output to destination for '.self::FEED,
            new PsrRequest('GET', self::FEED),
            null,
            null,
            ['errno' => CURLE_WRITE_ERROR]
        ));

        $this->assertSame(FeedFetcher::TOO_LARGE, $this->fetcher()->get(self::FEED)->status);
        $this->assertSame([], $this->reported);
    }

    public function test_a_validator_too_long_to_keep_is_not_kept_in_part(): void
    {
        Http::fake(['93.184.216.34/*' => Http::response('<rss/>', 200, ['ETag' => '"'.str_repeat('a', 300).'"', 'Last-Modified' => '  '])]);

        $result = $this->fetcher()->get(self::FEED);

        $this->assertTrue($result->ok());
        $this->assertNull($result->etag);
        $this->assertNull($result->lastModified);
    }
}
