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

    public function test_a_host_with_a_letter_outside_ascii_is_refused(): void
    {
        // A client built to convert international names looks such a host up under another
        // name than the one vetted: PHP's own conversion makes "example.com" of the first of
        // these. The pin on the vetted address is keyed by the name as written, so it would
        // not apply to the name that is really fetched. Refused before anything is resolved.
        $this->assertSame('example.com', idn_to_ascii("\u{24D4}xample.com", IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46));

        foreach ([
            "https://\u{24D4}xample.com/feed.ics",
            "https://exa\u{FF4D}ple.com/",
            "https://m\u{00FC}nchen.example/",
            // A full-width dot between the numbers of an address.
            "http://93.184.216\u{FF0E}34/",
            'http://exam ple.test/',
            // A backslash in the name itself, which curl turns away too ("Bad hostname").
            'http://example.test\\.93.184.216.34/',
        ] as $url) {
            $this->assertNull(UrlUtils::validatedTarget($url), $url);
        }

        // A backslash before an "@" is part of a user name to PHP and to curl alike (checked
        // against curl 8.8): both read the host after it, so the host vetted is the one fetched.
        $this->assertSame('93.184.216.34', UrlUtils::validatedTarget('http://example.test\\@93.184.216.34/')['host']);

        // What a host is really written with is untouched, the ASCII form of such a name too.
        foreach (['https://93.184.216.34/a', 'https://[2606:2800:220:1:248:1893:25c8:1946]/a'] as $url) {
            $this->assertNotNull(UrlUtils::validatedTarget($url), $url);
        }
        $normalise = new \ReflectionMethod(UrlUtils::class, 'normalizeHost');
        foreach (['xn--mnchen-3ya.example' => 'xn--mnchen-3ya.example', 'My_Host.Example.COM' => 'my_host.example.com', '[::1]' => '::1', "m\u{00FC}nchen.example" => ''] as $host => $expected) {
            $this->assertSame($expected, $normalise->invoke(null, $host), $host);
        }
    }

    public function test_an_address_is_judged_as_given_not_as_php_tidies_it(): void
    {
        // parse_url() turns a control character in a host into "_", which a host may contain,
        // and on macOS does the same to some bytes above ASCII: the name vetted and pinned was
        // then a tidy one that is not in the address. And a NUL in the path reached curl,
        // which throws something the caller does not catch.
        foreach ([
            "http://a\tb.example.test/",
            "http://a\x01b.example.test/",
            "http://93.184.216.34\x7f/",
            "http://exa\x85mple.test/",
            "http://exa\x9fmple.test/",
            "http://93.184.216.34/poster\0.jpg",
            "http://93.184.216.34/a\r\nHost: inside",
            'http://user name@93.184.216.34/',
        ] as $url) {
            $this->assertNull(UrlUtils::validatedTarget($url), json_encode($url, JSON_INVALID_UTF8_SUBSTITUTE));
        }

        // A space or a letter outside ASCII after the host is a path's business, as before.
        foreach (['https://93.184.216.34/a b', "https://93.184.216.34/caf\u{00E9}/\u{00E9}v\u{00E9}nements?q=\u{00E9}", 'https://user:secret@93.184.216.34/feed.ics'] as $url) {
            $this->assertSame('93.184.216.34', UrlUtils::validatedTarget($url)['host'] ?? null, $url);
        }
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
            '93.184.216.34/none' => \Illuminate\Support\Facades\Http::response('as it is', 200, ['Content-Encoding' => 'none']),
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
        // Not a registered encoding, but one servers send to say there is none.
        $this->assertSame('as it is', UrlUtils::safeFetch('https://93.184.216.34/none'));

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

    public function test_a_body_is_written_to_a_stream_that_stops_at_the_cap(): void
    {
        // The size cap given to curl is read off the length a server states, and before curl
        // 8.4 off nothing else: a body sent in chunks with no length ran until the timeout.
        // A write that is not taken whole stops the transfer on any curl. (Run against a
        // loopback server, an 8 MB chunked body stopped with curl error 23 at a 1 MB cap.)
        $sink = null;
        \Illuminate\Support\Facades\Http::fake(function ($request, array $options) use (&$sink) {
            $sink = $options['sink'] ?? null;

            return \Illuminate\Support\Facades\Http::response('ok');
        });
        UrlUtils::safeFetch('https://93.184.216.34/page');

        $this->assertInstanceOf(\Psr\Http\Message\StreamInterface::class, $sink);
        $megabyte = str_repeat('x', 1024 * 1024);
        $taken = 0;
        for ($n = 0; $n < 12; $n++) {
            $taken += $sink->write($megabyte);
        }
        $this->assertSame(10 * 1024 * 1024, $taken, 'ten megabytes are taken and not a byte more');
        $this->assertSame(0, $sink->write('x'));

        // The stream itself, at any size: all of a body under the cap, and the cap of one over.
        $small = UrlUtils::cappedSink(10);
        $this->assertSame(4, $small->write('body'));
        $this->assertSame(6, $small->write('and more than fits'));
        $this->assertSame(0, $small->write('x'));
        $this->assertSame('bodyand mo', (string) $small);
    }

    public function test_a_preview_image_is_stopped_at_its_cap_whether_or_not_a_length_is_stated(): void
    {
        $options = (new \ReflectionMethod(UrlUtils::class, 'imageCurlOptions'))
            ->invoke(null, 'https://93.184.216.34/poster.jpg', ['host' => '93.184.216.34', 'port' => 443, 'ip' => '93.184.216.34']);

        // A stated length over the cap is refused up front.
        $this->assertSame(5 * 1024 * 1024, $options[CURLOPT_MAXFILESIZE]);
        // A body that states none is stopped as it arrives: answering non-zero is "stop".
        $this->assertFalse($options[CURLOPT_NOPROGRESS]);
        $progress = $options[CURLOPT_PROGRESSFUNCTION];
        $this->assertSame(0, $progress(null, 0, 5 * 1024 * 1024));
        $this->assertSame(1, $progress(null, 0, 5 * 1024 * 1024 + 1));
        // And it still goes nowhere it was not sent.
        $this->assertFalse($options[CURLOPT_FOLLOWLOCATION]);
        $this->assertSame(CURLPROTO_HTTP | CURLPROTO_HTTPS, $options[CURLOPT_PROTOCOLS]);
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
