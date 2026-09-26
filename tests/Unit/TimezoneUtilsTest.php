<?php

namespace Tests\Unit;

use App\Utils\TimezoneUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * TimezoneUtils::canonicalize(): the listed name for what a browser reports.
 *
 * Chrome reports India as Asia/Calcutta, and Laravel's plain `timezone` rule - the one the schedule
 * form used - rejects it, so those users could not save their first schedule at all.
 */
class TimezoneUtilsTest extends TestCase
{
    public static function aliases(): array
    {
        return [
            'India in Chrome' => ['Asia/Calcutta', 'Asia/Kolkata'],
            'Vietnam' => ['Asia/Saigon', 'Asia/Ho_Chi_Minh'],
            'Nepal' => ['Asia/Katmandu', 'Asia/Kathmandu'],
            'Argentina' => ['America/Buenos_Aires', 'America/Argentina/Buenos_Aires'],
            'Indiana' => ['America/Indianapolis', 'America/Indiana/Indianapolis'],
            'Linux desktops' => ['Etc/UTC', 'UTC'],
            'GMT' => ['GMT', 'UTC'],
            // Not in the explicit map: ICU resolves these, and the result is listed.
            'US link' => ['US/Eastern', 'America/New_York'],
            'Canada link' => ['Canada/Pacific', 'America/Vancouver'],
        ];
    }

    #[DataProvider('aliases')]
    public function test_an_alias_becomes_the_listed_name(string $alias, string $expected): void
    {
        $this->assertNotContains($alias, timezone_identifiers_list(), 'not an alias here, so this proves nothing');

        $this->assertSame($expected, TimezoneUtils::canonicalize($alias));
        $this->assertContains(TimezoneUtils::canonicalize($alias), timezone_identifiers_list());
    }

    /**
     * ICU's canonical IDs are the OLD names: getCanonicalID('Asia/Kolkata') is Asia/Calcutta. The
     * explicit map has to win, and a listed name must never be "canonicalized" away from itself.
     */
    public function test_a_listed_name_is_returned_unchanged(): void
    {
        foreach (['Asia/Kolkata', 'America/New_York', 'Europe/London', 'UTC', 'Asia/Jerusalem'] as $timezone) {
            $this->assertSame($timezone, TimezoneUtils::canonicalize($timezone));
        }

        if (in_array('Europe/Kyiv', timezone_identifiers_list(), true)) {
            $this->assertSame('Europe/Kyiv', TimezoneUtils::canonicalize('Europe/Kiev'));
            $this->assertSame('Europe/Kyiv', TimezoneUtils::canonicalize('Europe/Kyiv'));
        }
    }

    public function test_letter_case_is_normalized(): void
    {
        $this->assertSame('Asia/Kolkata', TimezoneUtils::canonicalize('asia/kolkata'));
        $this->assertSame('Asia/Kolkata', TimezoneUtils::canonicalize('asia/calcutta'));
    }

    /**
     * Every caller falls back to America/New_York on null, so a usable zone with no listed name
     * must come back as itself rather than be replaced.
     */
    public function test_a_usable_zone_with_no_listed_name_passes_through(): void
    {
        $this->assertSame('Etc/GMT-3', TimezoneUtils::canonicalize('Etc/GMT-3'));
        $this->assertSame('EST', TimezoneUtils::canonicalize('EST'));
    }

    /**
     * ICU "canonicalizes" abbreviations to regional zones (PST to America/Los_Angeles), which swaps
     * a fixed offset for one with daylight saving. Only Region/City aliases may be remapped.
     */
    public function test_an_abbreviation_is_not_turned_into_a_regional_zone(): void
    {
        $this->assertSame('PST', TimezoneUtils::canonicalize('PST'));
        $this->assertSame('CEST', TimezoneUtils::canonicalize('CEST'));
        $this->assertSame('+05:30', TimezoneUtils::canonicalize('+05:30'));
        $this->assertSame('America/New_York', TimezoneUtils::canonicalize('US/Eastern'));
    }

    public function test_junk_is_null(): void
    {
        foreach ([null, '', '   ', 'Not/AZone', 'Mars/Olympus_Mons', ['Asia/Kolkata'], 42] as $junk) {
            $this->assertNull(TimezoneUtils::canonicalize($junk), var_export($junk, true));
        }
    }

    /** The browser copy of the map proposes exactly what the server would store. */
    public function test_the_alias_map_only_names_listed_targets(): void
    {
        $map = TimezoneUtils::aliasMap();

        $this->assertSame('Asia/Kolkata', $map['Asia/Calcutta'] ?? null);

        foreach ($map as $alias => $target) {
            $this->assertContains($target, timezone_identifiers_list(), $alias);
            $this->assertSame($target, TimezoneUtils::canonicalize($alias), $alias);
        }
    }
}
