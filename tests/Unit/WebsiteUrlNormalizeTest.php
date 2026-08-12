<?php

namespace Tests\Unit;

use App\Utils\GeminiUtils;
use App\Utils\UrlUtils;
use PHPUnit\Framework\TestCase;

/**
 * The cleanup that stands between a pasted link and roles.website.
 *
 * Copying a link out of Facebook's UI yields a shim - `l.facebook.com/l.php?u=<real url>&h=...` -
 * that is an order of magnitude longer than the site it stands for. roles.website is a
 * varchar(255) under a strict connection, so one pasted into the venue Website field was a
 * QueryException (MySQL 1406) rather than a truncation, and it 500'd the event create form.
 *
 * Half of these cases are the fix working; the other half are the fix keeping its hands off.
 * A schedule's website is legitimately allowed to be scheme-less, and two existing tests assert a
 * stored URL comes back byte-for-byte, so "rebuild it from parse_url" is not available here.
 */
class WebsiteUrlNormalizeTest extends TestCase
{
    /** The exact value from the production report (EVENTSCHEDULE-PHP-40), at its full 390 characters. */
    private const PRODUCTION_SHIM = 'https://l.facebook.com/l.php?u=https%3A%2F%2Fbeithadoar.com%2F%3Ffbclid%3DIwcGRvZgFleHRuA2FlbQIxMABicmlkETFFOFoyMkQyRlYybjRPcHF4c3J0YwZhcHBfaWQQMjIyMDM5MTc4ODIwMDg5MgABHh7JTu2XqUXfGevjbwBL7ugyy6B9nZ-Jfa2YbCvInHsTJW1Hk9p6heqkDwWe_aem_psmIxEJ2vh47GDYFMY-xKA&h=AUCBIJa8scQQgWmJT6Good4Ee5p7oC8fM5gBv4Two7DpNX4_35JZy7JsKv78SbgmpGMe-wnX2pxshF6ZDXEyL6DafE8p1YBxzZb40vbdOxo1EsacoVxZGujKwdNEgmNmKpEk';

    public function test_the_production_facebook_shim_becomes_the_site_it_stood_for(): void
    {
        $this->assertSame(390, strlen(self::PRODUCTION_SHIM), 'the fixture must stay the value that actually overflowed');

        $normalized = UrlUtils::normalizeWebsiteUrl(self::PRODUCTION_SHIM);

        $this->assertSame('https://beithadoar.com/', $normalized);
        $this->assertLessThanOrEqual(255, mb_strlen($normalized), 'the whole point is that it now fits roles.website');
    }

    public function test_a_google_result_link_is_unwrapped(): void
    {
        $this->assertSame(
            'https://example.org/page',
            UrlUtils::normalizeWebsiteUrl('https://www.google.com/url?q=https%3A%2F%2Fexample.org%2Fpage&sa=D')
        );
    }

    public function test_google_itself_is_an_ordinary_destination(): void
    {
        // Only the /url path is a shim. Dusk types this exact value into the schedule form.
        $this->assertSame('https://google.com', UrlUtils::normalizeWebsiteUrl('https://google.com'));
    }

    public function test_a_double_wrapped_shim_is_followed_and_the_loop_is_bounded(): void
    {
        $inner = 'https://lm.facebook.com/l.php?u='.rawurlencode('https://final.example/x');
        $outer = 'https://l.facebook.com/l.php?u='.rawurlencode($inner);

        $this->assertSame('https://final.example/x', UrlUtils::normalizeWebsiteUrl($outer));

        // Deeper than the hop budget: it must stop and return something storable, never spin.
        $deep = 'https://final.example/x';
        for ($i = 0; $i < 6; $i++) {
            $deep = 'https://l.facebook.com/l.php?u='.rawurlencode($deep);
        }
        $this->assertIsString(UrlUtils::normalizeWebsiteUrl($deep));
    }

    public function test_a_shim_whose_target_is_not_an_http_url_is_left_wrapped(): void
    {
        // roles.website is rendered straight into href="", so promoting one of these out of a
        // ?u= would turn a paste into a clickable script URL.
        foreach ([
            'https://l.facebook.com/l.php?u=javascript%3Aalert(1)&h=A',
            'https://l.facebook.com/l.php?u=data%3Atext%2Fhtml%2Cx&h=A',
            'https://l.facebook.com/l.php?u=%2Frelative&h=A',
            'https://l.facebook.com/l.php?h=A',
        ] as $url) {
            $this->assertSame($url, UrlUtils::normalizeWebsiteUrl($url), "must not unwrap {$url}");
        }
    }

    public function test_tracking_parameters_are_dropped_and_real_ones_kept(): void
    {
        $this->assertSame('https://a.com/p?page=2', UrlUtils::normalizeWebsiteUrl('https://a.com/p?utm_source=x&page=2'));
        $this->assertSame('https://a.com/p', UrlUtils::normalizeWebsiteUrl('https://a.com/p?fbclid=1'));
        $this->assertSame('https://a.com/p', UrlUtils::normalizeWebsiteUrl('https://a.com/p?FBCLID=1'), 'matched case-insensitively');
        $this->assertSame('https://a.com/p', UrlUtils::normalizeWebsiteUrl('https://a.com/p?utm_source=x&gclid=y&igshid=z'));
    }

    public function test_the_parameters_that_survive_a_strip_are_untouched(): void
    {
        // Stripping is a raw-text filter on the query, not a parse_str/http_build_query round
        // trip - that round trip renames a key containing a dot or a space, collapses repeated
        // keys, and rewrites ?a[]=1 with explicit indices. Each case here is one of those.
        foreach ([
            'https://a.com/p?a.b=1&fbclid=x' => 'https://a.com/p?a.b=1',
            'https://a.com/p?c d=2&fbclid=x' => 'https://a.com/p?c d=2',
            'https://a.com/p?a=1&a=2&fbclid=x' => 'https://a.com/p?a=1&a=2',
            'https://a.com/p?a[]=1&a[]=2&fbclid=x' => 'https://a.com/p?a[]=1&a[]=2',
            'https://a.com/p?flag&fbclid=x' => 'https://a.com/p?flag',
            'https://a.com/p?q=a+b&fbclid=x' => 'https://a.com/p?q=a+b',
            'https://a.com/p?q=hello%20world&fbclid=x' => 'https://a.com/p?q=hello%20world',
        ] as $input => $expected) {
            $this->assertSame($expected, UrlUtils::normalizeWebsiteUrl($input));
        }
    }

    public function test_a_url_with_nothing_to_change_is_returned_byte_for_byte(): void
    {
        // HoneypotTest::test_authenticated_schedule_update_still_accepts_a_real_website and
        // EventSaveVenueCharacterizationTest both assertSame on a stored URL, so a rebuild that
        // merely added or dropped a trailing slash - or folded case - would break them.
        foreach ([
            'https://example.org',
            'https://example.org/',
            'HTTPS://Example.ORG/Path',
            'https://keep-or-clear.example',
            'https://a.com/p?page=2',
            'https://a.com:8443/p?page=2',
            'https://user:pw@a.com/p?page=2',
        ] as $url) {
            $this->assertSame($url, UrlUtils::normalizeWebsiteUrl($url), "must not rewrite {$url}");
        }
    }

    public function test_a_value_that_is_not_an_absolute_http_url_passes_through_untouched(): void
    {
        // clean() and detectPlatform() both handle a scheme-less website on purpose, so one is a
        // legitimate stored value and must not be "repaired" into something else.
        foreach ([
            'example.com',
            'www.example.com/venue',
            '//protocol-relative.example',
            'mailto:hi@example.com',
            'tel:+123',
            'not a url at all',
        ] as $value) {
            $this->assertSame($value, UrlUtils::normalizeWebsiteUrl($value), "must not rewrite {$value}");
        }
    }

    public function test_blank_becomes_null(): void
    {
        $this->assertNull(UrlUtils::normalizeWebsiteUrl(null));
        $this->assertNull(UrlUtils::normalizeWebsiteUrl(''));
        $this->assertNull(UrlUtils::normalizeWebsiteUrl('   '));
    }

    public function test_surrounding_whitespace_is_trimmed(): void
    {
        $this->assertSame('https://example.org', UrlUtils::normalizeWebsiteUrl("  https://example.org\n"));
    }

    public function test_a_fragment_survives_and_a_query_inside_one_is_not_touched(): void
    {
        $this->assertSame('https://a.com/p#anchor', UrlUtils::normalizeWebsiteUrl('https://a.com/p?fbclid=1#anchor'));
        // The '?' here belongs to the fragment, so there is no query to clean.
        $this->assertSame('https://a.com/p#f?utm_source=x', UrlUtils::normalizeWebsiteUrl('https://a.com/p#f?utm_source=x'));
    }

    public function test_unwrap_redirect_does_not_strip_tracking_parameters(): void
    {
        // registration_url is a ticket-sales link: utm_* there is the seller's attribution, so
        // GeminiUtils must get the unwrap without the stripping.
        $this->assertSame(
            'https://tickets.example/buy?utm_source=partner',
            UrlUtils::unwrapRedirect('https://tickets.example/buy?utm_source=partner')
        );

        $this->assertSame(
            'https://tickets.example/buy?utm_source=partner',
            UrlUtils::unwrapRedirect('https://l.facebook.com/l.php?u='.rawurlencode('https://tickets.example/buy?utm_source=partner'))
        );
    }

    /**
     * The rule above is only worth anything if parseEvent() actually calls the non-stripping one,
     * and swapping the two would leave every other test green while silently deleting sellers'
     * campaign attribution. parseEvent() reaches the provider through raw curl, so it cannot be
     * driven without a network call - pinned by inspection, the way AiParsedFieldLengthTest pins
     * the clamp loop in the same method.
     */
    public function test_gemini_unwraps_the_registration_url_without_stripping_it(): void
    {
        $method = new \ReflectionMethod(GeminiUtils::class, 'parseEvent');
        $source = implode('', array_slice(
            file($method->getFileName()),
            $method->getStartLine() - 1,
            $method->getEndLine() - $method->getStartLine() + 1
        ));

        // Comments are stripped first: the method carries a comment naming both functions to
        // explain the choice, and matching that would make this assertion pin prose, not code.
        $code = '';
        foreach (token_get_all('<?php '.$source) as $token) {
            if (is_array($token)) {
                if ($token[0] === T_COMMENT || $token[0] === T_DOC_COMMENT) {
                    continue;
                }
                $code .= $token[1];
            } else {
                $code .= $token;
            }
        }

        $this->assertStringContainsString(
            'UrlUtils::unwrapRedirect(',
            $code,
            'parseEvent() must unwrap link shims on registration_url'
        );

        $this->assertStringNotContainsString(
            'normalizeWebsiteUrl',
            $code,
            'normalizeWebsiteUrl() strips utm_*, which on a ticket-sales link is the seller\'s attribution'
        );
    }
}
