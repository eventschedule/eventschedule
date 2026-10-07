<?php

namespace Tests\Unit;

use App\Utils\ColorUtils;
use App\Utils\GuestTheme;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The colours a schedule's guest pages take from its one accent (App\Utils\GuestTheme).
 */
class GuestThemeTest extends TestCase
{
    /** Accents an owner might really pick, the awkward ones included. */
    public static function accents(): array
    {
        return [
            'the default blue' => ['#4E81FA'],
            'the demo yellow' => ['#FFD90F'],
            'a lime' => ['#a3e635'],
            'a red' => ['#dc2626'],
            'a pale pink' => ['#fbcfe8'],
            'a cream' => ['#fffbe6'],
            'a near-black navy' => ['#0b1220'],
            'a deep green' => ['#064e3b'],
            'black' => ['#000000'],
            'white' => ['#ffffff'],
            'a mid grey' => ['#888888'],
            'a slate grey' => ['#64748b'],
            'three digits' => ['#f0c'],
            'not a colour' => ['javascript:alert(1)'],
            'nothing' => [null],
        ];
    }

    #[DataProvider('accents')]
    public function test_the_accent_as_text_reads_on_every_panel(?string $accent): void
    {
        $theme = GuestTheme::fromAccent($accent);

        foreach (GuestTheme::LIGHT_PANELS as $panel) {
            $this->assertGreaterThanOrEqual(4.5, ColorUtils::getContrastRatio($theme->readable, $panel), "{$theme->readable} on {$panel}");
        }
        foreach (GuestTheme::DARK_PANELS as $panel) {
            $this->assertGreaterThanOrEqual(4.5, ColorUtils::getContrastRatio($theme->readableDark, $panel), "{$theme->readableDark} on {$panel}");
        }
    }

    #[DataProvider('accents')]
    public function test_the_button_stands_out_from_a_dark_panel_and_its_label_from_the_button(?string $accent): void
    {
        $theme = GuestTheme::fromAccent($accent);

        // On the dark panel a fill either stands out or has a hairline round it.
        $this->assertTrue(
            ColorUtils::getContrastRatio($theme->fillDark, GuestTheme::DARK_PANELS[0]) >= 1.6 || $theme->edgeDark !== 'transparent',
            "{$theme->fillDark} on the dark panel"
        );
        // On white, a fill with no edge of its own gets one.
        $this->assertTrue(
            ColorUtils::getContrastRatio($theme->fill, '#ffffff') >= 1.25 || $theme->edge !== 'transparent',
            "{$theme->fill} on white"
        );

        // The label is the black or white the pages have always put on an accent, and it reads
        // at least as a large bold label must (3:1).
        foreach ([[$theme->fill, $theme->onFill], [$theme->fillDark, $theme->onFillDark]] as [$fill, $label]) {
            $this->assertSame(accent_contrast_color($fill), $label);
            $this->assertGreaterThanOrEqual(3.0, ColorUtils::getContrastRatio($fill, $label), "{$label} on {$fill}");
        }
    }

    public function test_a_colour_keeps_its_own_fill(): void
    {
        foreach (['#4e81fa', '#ffd90f', '#dc2626', '#a3e635'] as $accent) {
            $theme = GuestTheme::fromAccent($accent);

            $this->assertFalse($theme->neutral);
            $this->assertSame($accent, $theme->fill, 'an owner\'s colour is the button, as it has always been');
            $this->assertSame($accent, $theme->fillDark);
        }
    }

    public function test_a_grey_becomes_ink_because_a_grey_button_looks_disabled(): void
    {
        $grey = GuestTheme::fromAccent('#888888');

        $this->assertTrue($grey->neutral);
        $this->assertSame(GuestTheme::INK, $grey->fill);
        $this->assertSame('#ffffff', $grey->onFill);
        $this->assertSame(GuestTheme::PAPER, $grey->fillDark);
        $this->assertSame('#000000', $grey->onFillDark);

        // Black is already ink on a light panel, and turns over on a dark one.
        $black = GuestTheme::fromAccent('#000000');
        $this->assertSame('#000000', $black->fill);
        $this->assertSame(GuestTheme::PAPER, $black->fillDark);

        // White is the other way round.
        $white = GuestTheme::fromAccent('#ffffff');
        $this->assertSame(GuestTheme::INK, $white->fill);
        $this->assertSame('#ffffff', $white->fillDark);
    }

    public function test_a_colour_the_dark_panel_swallows_is_lifted_along_its_own_hue(): void
    {
        $navy = GuestTheme::fromAccent('#0b1220');

        $this->assertSame('#0b1220', $navy->fill, 'on white it is a fine button');
        $this->assertNotSame('#0b1220', $navy->fillDark);
        $this->assertGreaterThanOrEqual(3.0, ColorUtils::getContrastRatio($navy->fillDark, GuestTheme::DARK_PANELS[0]));

        // Still a blue: the hue is the one the owner chose.
        $this->assertEqualsWithDelta(ColorUtils::toHsl('#0b1220')[0], ColorUtils::toHsl($navy->fillDark)[0], 0.02);
    }

    public function test_a_readable_yellow_is_still_a_yellow(): void
    {
        $theme = GuestTheme::fromAccent('#FFD90F');

        $this->assertNotSame('#ffd90f', $theme->readable, 'yellow text on white cannot be read');
        $this->assertEqualsWithDelta(ColorUtils::toHsl('#ffd90f')[0], ColorUtils::toHsl($theme->readable)[0], 0.02, 'the same hue, darker');
        $this->assertSame('#ffd90f', $theme->readableDark, 'and on a dark panel it reads as it is');
    }

    #[DataProvider('accents')]
    public function test_only_colours_this_class_made_reach_the_stylesheet(?string $accent): void
    {
        $css = GuestTheme::fromAccent($accent)->css();

        // A hex, the word transparent, or three integers: nothing an owner typed is printed.
        $this->assertMatchesRegularExpression(
            '/^body \{ (--es-[a-z-]+: (#[0-9a-f]{6}|transparent|\d{1,3} \d{1,3} \d{1,3}); ?)+ \}\n\.dark body \{ (--es-[a-z-]+: (#[0-9a-f]{6}|transparent); ?)+ \}$/',
            $css
        );
        $this->assertStringContainsString('--es-accent: ', $css);
        $this->assertStringContainsString('--es-accent-text: ', $css);
        $this->assertStringContainsString('--es-accent-readable: ', $css);
        $this->assertStringContainsString('--es-accent-tint: ', $css);
    }

    public function test_the_glow_is_a_light_even_for_a_dark_or_grey_accent(): void
    {
        foreach (['#0b1220', '#064e3b', '#000000', '#888888', '#ffd90f'] as $accent) {
            [$r, $g, $b] = array_map('intval', explode(' ', GuestTheme::fromAccent($accent)->glow));
            $this->assertGreaterThanOrEqual(3.0, ColorUtils::getContrastRatio(ColorUtils::fromRgb([$r, $g, $b]), '#0b0f19'), $accent.' as a light on a dark ticket');
        }
    }
}
