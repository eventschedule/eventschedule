<?php

namespace Tests\Unit;

use App\Utils\UrlUtils;
use Tests\TestCase;

/**
 * UrlUtils::validatedTarget() decides whether the server may fetch an address somebody else
 * supplied (a pasted link, a page's preview image, a webhook). It answers with the host and the
 * addresses the request is then pinned to, or null.
 *
 * Every case is an IP literal or a name on the block list, so nothing here resolves a name.
 */
class UrlFetchGuardTest extends TestCase
{
    public function test_a_host_with_an_encoded_character_is_refused(): void
    {
        // The check used to cut a host at its percent sign, as it must for an IPv6 zone id, and
        // so vetted "1.1.1.1" for an address a decoding client reads as a different name.
        foreach ([
            'http://1.1.1.1%2eexample.test/x.jpg',
            'http://local%68ost:9/',
            'http://example.com%2f@127.0.0.1/',
            'https://93.184.216.34%00.example.test/',
        ] as $url) {
            $this->assertNull(UrlUtils::validatedTarget($url), $url);
        }

        // A percent sign anywhere but the host is ordinary.
        $target = UrlUtils::validatedTarget('https://93.184.216.34/a%20b?q=50%25');
        $this->assertNotNull($target);
        $this->assertSame('93.184.216.34', $target['host']);
    }

    public function test_addresses_inside_the_network_are_refused(): void
    {
        foreach ([
            'http://127.0.0.1/', 'http://10.0.0.5/', 'http://192.168.1.1/', 'http://172.16.0.1/',
            'http://169.254.169.254/latest/meta-data/', 'http://[::1]/', 'http://[::ffff:127.0.0.1]/',
            'http://[fe80::1%25eth0]/', 'http://0.0.0.0/', 'http://localhost/', 'http://metadata.google.internal/',
        ] as $url) {
            $this->assertNull(UrlUtils::validatedTarget($url), $url);
        }
    }

    public function test_only_web_addresses_are_taken(): void
    {
        foreach (['file:///etc/passwd', 'gopher://93.184.216.34/', 'ftp://93.184.216.34/', 'javascript:alert(1)', '//93.184.216.34/', 'not a url', ''] as $url) {
            $this->assertNull(UrlUtils::validatedTarget($url), $url);
        }

        $this->assertNotNull(UrlUtils::validatedTarget('http://93.184.216.34/'));
        $this->assertNotNull(UrlUtils::validatedTarget('HTTPS://93.184.216.34:8443/path'));
    }

    public function test_a_compressed_body_is_inflated_only_as_far_as_the_cap(): void
    {
        // The wire cap counts compressed bytes. A few kilobytes of gzip can be hundreds of
        // megabytes, and the client used to inflate all of it before anything looked at its size.
        $bomb = gzencode(str_repeat("\0", 12 * 1024 * 1024), 9);
        $this->assertLessThan(64 * 1024, strlen($bomb));

        \Illuminate\Support\Facades\Http::preventStrayRequests();
        \Illuminate\Support\Facades\Http::fake([
            '93.184.216.34/bomb' => \Illuminate\Support\Facades\Http::response($bomb, 200, ['Content-Encoding' => 'gzip']),
            '93.184.216.34/gzip' => \Illuminate\Support\Facades\Http::response(gzencode('<html>fine</html>'), 200, ['Content-Encoding' => 'gzip', 'Content-Type' => 'text/html']),
            '93.184.216.34/deflate' => \Illuminate\Support\Facades\Http::response(gzcompress('deflated'), 200, ['Content-Encoding' => 'deflate']),
            '93.184.216.34/lies' => \Illuminate\Support\Facades\Http::response('not gzip at all', 200, ['Content-Encoding' => 'gzip']),
            '93.184.216.34/brotli' => \Illuminate\Support\Facades\Http::response('xx', 200, ['Content-Encoding' => 'br']),
            '93.184.216.34/plain' => \Illuminate\Support\Facades\Http::response('plain', 200, ['Content-Type' => 'text/plain']),
        ]);

        $before = memory_get_peak_usage();
        $this->assertNull(UrlUtils::safeHttpGet('https://93.184.216.34/bomb'));
        $this->assertNull(UrlUtils::safeFetch('https://93.184.216.34/bomb'));

        // A server that compresses without being asked is still read, and reads as plain.
        $response = UrlUtils::safeHttpGet('https://93.184.216.34/gzip');
        $this->assertSame('<html>fine</html>', $response->body());
        $this->assertSame('text/html', $response->header('Content-Type'));
        $this->assertSame('', $response->header('Content-Encoding'));
        $this->assertSame('deflated', UrlUtils::safeFetch('https://93.184.216.34/deflate'));
        $this->assertSame('plain', UrlUtils::safeFetch('https://93.184.216.34/plain'));

        // What is not what it says, or is something nobody asked for, is not fetched.
        $this->assertNull(UrlUtils::safeHttpGet('https://93.184.216.34/lies'));
        $this->assertNull(UrlUtils::safeHttpGet('https://93.184.216.34/brotli'));

        // And no compression is asked for in the first place.
        \Illuminate\Support\Facades\Http::assertSent(fn ($request) => $request->header('Accept-Encoding') === ['identity']);
    }

    public function test_the_client_is_told_not_to_inflate_what_it_fetches(): void
    {
        // The bound in the test above is ours. It means nothing if the HTTP client has already
        // inflated the body on the way in, which is what it does unless told not to.
        $seen = [];
        \Illuminate\Support\Facades\Http::fake(function ($request, array $options) use (&$seen) {
            $seen[] = $options;

            return \Illuminate\Support\Facades\Http::response('ok');
        });

        $this->assertSame('ok', UrlUtils::safeFetch('https://93.184.216.34/page'));

        $this->assertCount(1, $seen);
        $this->assertFalse($seen[0]['decode_content']);
        $this->assertFalse($seen[0]['allow_redirects']);
    }

    public function test_redirects_share_one_time_budget(): void
    {
        // The timeout used to apply to each hop: a server that redirects slowly five times held
        // a request for five timeouts. Each hop now gets what is left.
        $timeouts = [];
        \Illuminate\Support\Facades\Http::fake(function ($request, array $options) use (&$timeouts) {
            $timeouts[] = $options['timeout'];

            if (count($timeouts) === 1) {
                usleep(1200000);

                return \Illuminate\Support\Facades\Http::response('', 302, ['Location' => 'https://93.184.216.34/next']);
            }

            return \Illuminate\Support\Facades\Http::response('there');
        });

        $response = UrlUtils::safeHttpGet('https://93.184.216.34/start', [], 3);

        $this->assertSame('there', $response->body());
        $this->assertSame(3, $timeouts[0]);
        $this->assertLessThanOrEqual(2, $timeouts[1]);
    }
}
