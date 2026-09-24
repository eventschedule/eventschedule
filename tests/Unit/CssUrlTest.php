<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * css_url(): a URL printed inside CSS url("...") - in the guest layout's <style> block, or a
 * style="" attribute - cannot end the url(), the string inside it, or the style around it.
 *
 * Inside <style> an HTML entity is not decoded, but a raw line break ends a CSS string and the rest
 * of the value parses as rules. Inside style="" the browser decodes &#039; before CSS reads it, so
 * an HTML-escaped quote still closes the string. Both are why Blade's escaping is not enough.
 */
class CssUrlTest extends TestCase
{
    /** @return array<string, array{0: ?string, 1: string}> */
    public static function urls(): array
    {
        return [
            // What the app itself writes is left exactly as it is.
            'an asset' => ['https://eventschedule.test/images/backgrounds/Abstract_Sunrise.webp', 'https://eventschedule.test/images/backgrounds/Abstract_Sunrise.webp'],
            'a query string' => ['https://cdn.test/a.png?v=2&w=960', 'https://cdn.test/a.png?v=2&w=960'],
            'an encoded path' => ['https://cdn.test/My%20Image%27s.png', 'https://cdn.test/My%20Image%27s.png'],
            'a fragment and a port' => ['http://cdn.test:8080/a.png#x', 'http://cdn.test:8080/a.png#x'],
            'unicode' => ['https://cdn.test/müller.png', 'https://cdn.test/müller.png'],
            'empty' => ['', ''],
            'null' => [null, ''],
            // Anything that can end the string, the url() or the style around it is encoded.
            'a single quote' => ["x.png');}body{display:none}/*", 'x.png%27%29;}body{display:none}/*'],
            'a double quote' => ['x.png");}body{display:none}/*', 'x.png%22%29;}body{display:none}/*'],
            'an opening parenthesis' => ['x(1).png', 'x%281%29.png'],
            'a backslash' => ['x\\22.png', 'x%5C22.png'],
            'angle brackets' => ['x.png</style><script>', 'x.png%3C/style%3E%3Cscript%3E'],
            'a space' => ['my image.png', 'my%20image.png'],
            'a line feed' => ["x.png\n}body{display:none}", 'x.png%0A}body{display:none}'],
            'a carriage return' => ["x.png\r}", 'x.png%0D}'],
            'a form feed' => ["x.png\f}", 'x.png%0C}'],
            'a tab' => ["x.png\t}", 'x.png%09}'],
            'a null byte' => ["x.png\0", 'x.png%00'],
            'other control characters' => ["x\x01\x1F\x7F", 'x%01%1F%7F'],
        ];
    }

    #[DataProvider('urls')]
    public function test_css_url(?string $url, string $expected): void
    {
        $this->assertSame($expected, css_url($url));
    }
}
