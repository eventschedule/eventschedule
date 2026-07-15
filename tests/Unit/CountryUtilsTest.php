<?php

namespace Tests\Unit;

use App\Utils\CountryUtils;
use Tests\TestCase;

/**
 * Covers country-code normalization (Sentry EVENTSCHEDULE-JS-2Y: an alpha-3 code like
 * "isr" reached the alpha-2-only country picker). Extends the Laravel TestCase because
 * CountryUtils reads its data files via database_path().
 */
class CountryUtilsTest extends TestCase
{
    public function test_normalize_country_code(): void
    {
        $cases = [
            // alpha-3 -> alpha-2 (case-insensitive, trimmed)
            ['isr', 'il'],
            ['ISR', 'il'],
            ['Isr', 'il'],
            ['  isr  ', 'il'],
            ['usa', 'us'],
            ['GBR', 'gb'],
            ['deu', 'de'],
            ['xkx', 'xk'],   // Kosovo (user-assigned)
            ['rom', 'ro'],   // historical alias

            // already alpha-2 -> lowercased passthrough
            ['il', 'il'],
            ['IL', 'il'],
            ['us', 'us'],

            // unknown values pass through unchanged (non-destructive), empty -> null
            ['xx', 'xx'],
            ['zzz', 'zzz'],
            ['', null],
            ['   ', null],
            [null, null],
        ];

        foreach ($cases as [$input, $expected]) {
            $this->assertSame(
                $expected,
                CountryUtils::normalizeCountryCode($input),
                'normalizeCountryCode('.var_export($input, true).')'
            );
        }
    }

    public function test_alpha3_map_is_loaded_and_lowercase(): void
    {
        $map = CountryUtils::getAlpha3Map();

        $this->assertNotEmpty($map);
        $this->assertArrayHasKey('isr', $map);
        $this->assertSame('il', $map['isr']);

        // Keys are lowercase alpha-3, values lowercase alpha-2.
        foreach ($map as $alpha3 => $alpha2) {
            $this->assertMatchesRegularExpression('/^[a-z]{3}$/', $alpha3);
            $this->assertMatchesRegularExpression('/^[a-z]{2}$/', $alpha2);
        }
    }
}
