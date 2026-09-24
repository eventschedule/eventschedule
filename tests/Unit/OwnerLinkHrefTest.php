<?php

namespace Tests\Unit;

use App\Utils\UrlUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * UrlUtils::safeHref() and UrlUtils::linkHost(): what an owner-typed link may become on a page
 * somebody else opens.
 *
 * safeHref() is every guest-facing href built from a stored link - an event's registration and
 * join links, a schedule's website, social, payment and sponsor links. Most of those are validated
 * as a string at most, so a stored javascript: value ran as script on the page.
 *
 * linkHost() is the only part of an online event's private join link a public page may print. It
 * used to be whatever parse_url() returned, which for free-text join instructions was the whole
 * text, meeting id and passcode included.
 */
class OwnerLinkHrefTest extends TestCase
{
    /** @return array<string, array{0: mixed, 1: ?string}> */
    public static function hrefs(): array
    {
        return [
            // A web link is kept exactly as it was typed.
            'https' => ['https://tickets.example.org/e/42?utm_source=flyer', 'https://tickets.example.org/e/42?utm_source=flyer'],
            'http' => ['http://tickets.example.org/e/42', 'http://tickets.example.org/e/42'],
            'an upper-case scheme' => ['HTTPS://Tickets.Example.org/E', 'HTTPS://Tickets.Example.org/E'],
            'an IP address' => ['https://192.0.2.10/room', 'https://192.0.2.10/room'],
            'surrounding whitespace' => ["  https://tickets.example.org/e/1 \n", 'https://tickets.example.org/e/1'],
            // Without a scheme, on a real domain: https:// in front, instead of a link relative to the page.
            'scheme-less' => ['www.example.com/tickets', 'https://www.example.com/tickets'],
            'scheme-less with a port' => ['example.com:8080/room', 'https://example.com:8080/room'],
            'protocol-relative' => ['//example.com/x', 'https://example.com/x'],
            'a unicode host' => ['münchen.de/karten', 'https://münchen.de/karten'],
            // Anything else is no link at all.
            'javascript' => ['javascript:alert(1)', null],
            'javascript in mixed case' => ['JavaScript:alert(1)', null],
            'javascript behind a space' => [' javascript:alert(1)', null],
            'javascript behind a control character' => ["\x01javascript:alert(1)", null],
            'javascript split by a tab' => ["java\tscript:alert(1)", null],
            'javascript split by a newline' => ["java\nscript:alert(1)", null],
            'a line break inside a web link' => ["https://tickets.example.org/e/1\n<script>", null],
            'javascript with a host' => ['javascript://example.com/%0Aalert(1)', null],
            'javascript with a port' => ['javascript:8080', null],
            'data' => ['data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg==', null],
            'vbscript' => ['vbscript:msgbox(1)', null],
            'mailto' => ['mailto:tickets@example.com', null],
            'tel' => ['tel:+15550100', null],
            'an email address' => ['tickets@example.com', null],
            'free-text join instructions' => ['Zoom 884 1234 pw 998877', null],
            'dotted digits' => ['884.1234.5678', null],
            'a single word' => ['online', null],
            'localhost' => ['localhost:8000', null],
            'a path' => ['/tickets', null],
            'https with no host' => ['https://', null],
            'an unparseable http url' => ['http://javascript:alert(1)', null],
            'empty' => ['', null],
            'null' => [null, null],
            'not a string' => [['https://example.com'], null],
        ];
    }

    #[DataProvider('hrefs')]
    public function test_safe_href(mixed $value, ?string $expected): void
    {
        $this->assertSame($this->withoutIntl($value, $expected), UrlUtils::safeHref($value));
    }

    /** Whatever goes in, what comes out can only ever open a web page. */
    public function test_every_href_is_an_http_link(): void
    {
        foreach (self::hrefs() as $label => [$value]) {
            $href = UrlUtils::safeHref($value);

            if ($href !== null) {
                $this->assertMatchesRegularExpression('~^https?://~i', $href, $label);
            }
        }
    }

    /** @return array<string, array{0: mixed, 1: string}> */
    public static function hosts(): array
    {
        return [
            'a meeting link' => ['https://zoom.us/j/5550001234?pwd=TopSecretJoinCode', 'zoom.us'],
            'scheme-less' => ['zoom.us/j/5550009876?pwd=AnotherSecretCode', 'zoom.us'],
            'with credentials' => ['https://user:pass@meet.example.com/room', 'meet.example.com'],
            'with a port' => ['example.com:8080/room', 'example.com'],
            'upper case, with a trailing dot' => ['https://ZOOM.US./j/1', 'zoom.us'],
            'a unicode host, kept readable' => ['https://münchen.de/x', 'münchen.de'],
            'a unicode host in upper case' => ['https://MÜNCHEN.DE/x', 'münchen.de'],
            'a cyrillic host' => ['пример.рф/путь', 'пример.рф'],
            'punycode, kept as typed' => ['https://xn--mnchen-3ya.de/x', 'xn--mnchen-3ya.de'],
            'free text' => ['Zoom 884 1234 pw 998877', ''],
            'dotted digits' => ['884.1234.5678', ''],
            'a passcode' => ['pw.998877', ''],
            'an IPv4 address' => ['1.2.3.4', ''],
            'an IP address behind a scheme' => ['https://192.0.2.10/room', ''],
            'an IPv6 address' => ['https://[::1]/room', ''],
            'localhost' => ['http://localhost:8000/x', ''],
            'a single word' => ['online', ''],
            'mailto' => ['mailto:host@example.com', ''],
            'an email address' => ['host@example.com', ''],
            'javascript' => ['javascript:alert(1)', ''],
            'tel' => ['tel:+15550100', ''],
            'null' => [null, ''],
        ];
    }

    #[DataProvider('hosts')]
    public function test_link_host(mixed $value, string $expected): void
    {
        $this->assertSame($this->withoutIntl($value, $expected), UrlUtils::linkHost($value));
    }

    /**
     * Without intl there is no idn_to_ascii() to validate a Unicode host with, so it is not a
     * domain: no host, and no link for a scheme-less value. CI and production have intl; this
     * keeps the tables true on a selfhost server without it.
     */
    private function withoutIntl(mixed $value, ?string $expected): ?string
    {
        if ($expected === null || function_exists('idn_to_ascii') || ! is_string($value) || ! preg_match('/[^\x00-\x7F]/', $value)) {
            return $expected;
        }

        return str_starts_with($expected, 'http') ? null : '';
    }
}
