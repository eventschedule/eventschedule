<?php

namespace Tests\Unit;

use App\Exceptions\InvoiceNinjaException;
use App\Utils\InvoiceNinja;
use Tests\TestCase;

/**
 * Covers Invoice Ninja API base-URL normalization (GitHub issue #110).
 *
 * The old implementation used rtrim($url, 'api/v1'), whose second argument is a character
 * list rather than a suffix, so any host ending in a, p, i, v or 1 was silently truncated:
 * "https://books.example.ai" became "https://books.example." and every request failed.
 *
 * Extends the Laravel TestCase because the rejection path resolves a translation string.
 */
class InvoiceNinjaUrlTest extends TestCase
{
    public static function apiUrlProvider(): array
    {
        return [
            // No URL configured falls back to the Invoice Ninja SaaS endpoint.
            'empty' => ['', 'https://invoicing.co/api/v1/'],
            'whitespace only' => ['   ', 'https://invoicing.co/api/v1/'],
            'null' => [null, 'https://invoicing.co/api/v1/'],

            // Plain hosts.
            'plain host' => ['https://acct.example.com', 'https://acct.example.com/api/v1/'],
            'trailing slash' => ['https://acct.example.com/', 'https://acct.example.com/api/v1/'],
            'surrounding whitespace' => ['  https://acct.example.com  ', 'https://acct.example.com/api/v1/'],

            // A pasted full API endpoint has its suffix stripped exactly once.
            'api suffix' => ['https://acct.example.com/api/v1', 'https://acct.example.com/api/v1/'],
            'api suffix with slash' => ['https://acct.example.com/api/v1/', 'https://acct.example.com/api/v1/'],
            'uppercase host and path' => ['https://EXAMPLE.com/API/V1', 'https://example.com/api/v1/'],

            // Regression cases for issue #110: hosts ending in a character that the old
            // rtrim() character list would have eaten.
            'dot ca' => ['https://billing.example.ca', 'https://billing.example.ca/api/v1/'],
            'dot app' => ['https://pay.example.app', 'https://pay.example.app/api/v1/'],
            'dot ai' => ['https://books.example.ai', 'https://books.example.ai/api/v1/'],
            'dot tv' => ['https://in.example.tv', 'https://in.example.tv/api/v1/'],
            'dot li' => ['https://x.example.li', 'https://x.example.li/api/v1/'],

            // Selfhosted shapes.
            'lan address with port' => ['http://192.168.1.50:8000', 'http://192.168.1.50:8000/api/v1/'],
            'docker service name' => ['http://invoiceninja', 'http://invoiceninja/api/v1/'],
            'sub-path install' => ['https://example.com/ninja', 'https://example.com/ninja/api/v1/'],
            'sub-path install with api suffix' => ['https://example.com/ninja/api/v1', 'https://example.com/ninja/api/v1/'],

            // A bare host gets an https scheme; "host:port" must not read as a scheme.
            'scheme-less host' => ['invoicing.example.com', 'https://invoicing.example.com/api/v1/'],
            'scheme-less host with port' => ['invoicing.example.com:8080', 'https://invoicing.example.com:8080/api/v1/'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('apiUrlProvider')]
    public function test_normalize_api_url($input, string $expected): void
    {
        $this->assertSame($expected, InvoiceNinja::normalizeApiUrl($input));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('apiUrlProvider')]
    public function test_normalize_api_url_is_idempotent($input, string $expected): void
    {
        $once = InvoiceNinja::normalizeApiUrl($input);

        $this->assertSame($once, InvoiceNinja::normalizeApiUrl($once));
    }

    public static function rejectedUrlProvider(): array
    {
        return [
            'file' => ['file:///etc/passwd'],
            'gopher' => ['gopher://example.com'],
            'dict' => ['dict://example.com'],
            'javascript' => ['javascript:alert(1)'],
            'ftp' => ['ftp://example.com'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('rejectedUrlProvider')]
    public function test_normalize_api_url_rejects_non_http_schemes(string $input): void
    {
        $this->expectException(InvoiceNinjaException::class);

        InvoiceNinja::normalizeApiUrl($input);
    }
}
