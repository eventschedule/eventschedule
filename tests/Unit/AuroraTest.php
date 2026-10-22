<?php

namespace Tests\Unit;

use App\Utils\Aurora;
use PHPUnit\Framework\TestCase;

/**
 * Aurora's colour arithmetic (App\Utils\Aurora). The Style tab's preview ports it
 * (resources/js/style/aurora.js), so its answers are pinned in tests/fixtures/aurora.json, which
 * the browser test reads too: a change here that the port does not make shows up in both.
 */
class AuroraTest extends TestCase
{
    private const ACCENTS = ['#4e81fa', '#ef4444', '#eab308', '#22c55e', '#111827', '#ffffff', '#888888', '#0b1f4d', '#ec4899', '#14b8a6', '#000000', '#f97316'];

    public function test_the_colours_are_the_ones_the_preview_was_pinned_to(): void
    {
        $cases = json_decode(file_get_contents(__DIR__.'/../fixtures/aurora.json'), true);
        $this->assertGreaterThan(20, count($cases));

        foreach ($cases as $case) {
            $aurora = Aurora::fromParts($case['accent'], $case['palette'], $case['colors'], $case['seed'], true);
            foreach (['light', 'dark'] as $mode) {
                $tokens = $aurora->tokens($mode);
                $this->assertSame($case[$mode]['ground'], Aurora::triplet($tokens['ground']), "{$case['palette']} {$case['accent']} {$mode} ground");
                $this->assertSame($case[$mode]['lights'], array_map(fn ($light) => Aurora::triplet($light), $tokens['lights']), "{$case['palette']} {$case['accent']} {$mode} lights");
            }
        }
    }

    /**
     * What is written straight on the page (a list's day headings, the body's text) reads at
     * 4.5:1 over every light at its strongest, for every palette and every accent, in both modes.
     */
    public function test_the_ink_on_the_page_reads_over_every_light(): void
    {
        $ink = ['light' => [0x33, 0x38, 0x3C], 'dark' => [255, 255, 255]];

        $palettes = array_merge(array_keys(Aurora::PALETTES), ['accent']);
        foreach ($palettes as $palette) {
            foreach (self::ACCENTS as $accent) {
                // Three seeds: every harmony From accent can pick.
                foreach ([0, 1024, 2048] as $seed) {
                    $aurora = Aurora::fromParts($accent, $palette, null, $seed, true);
                    foreach (['light', 'dark'] as $mode) {
                        $t = $aurora->tokens($mode);
                        foreach ($t['lights'] as $i => $light) {
                            $mixed = array_map(fn ($channel, $ground) => $channel * $t['alpha'] + $ground * (1 - $t['alpha']), $light, $t['ground']);
                            $this->assertGreaterThanOrEqual(4.5, Aurora::contrast($mixed, $ink[$mode]), "{$palette} {$accent} {$mode} light {$i}");
                        }
                    }
                }
            }
        }
    }

    public function test_a_seed_is_read_by_bit_arithmetic(): void
    {
        $this->assertSame(['comp' => 0, 'mirror' => 0, 'phase' => 0, 'still' => 0.0, 'harmony' => 0], Aurora::decode(0));
        $this->assertSame(5, Aurora::decode(13)['comp']);
        $this->assertSame(1, Aurora::decode(8)['mirror']);
        $this->assertSame(1, Aurora::decode(1024)['harmony']);
        $this->assertSame(1.0, Aurora::decode(15 << 6)['still']);

        foreach ([0, 1, 4095, 65535] as $seed) {
            $d = Aurora::decode($seed);
            $this->assertLessThan(count(Aurora::COMPOSITIONS), $d['comp']);
            $this->assertLessThan(60, $d['phase']);
            $this->assertLessThan(3, $d['harmony']);
        }
    }

    public function test_a_grey_or_black_accent_gives_grey_lights(): void
    {
        foreach (['#000000', '#888888', '#ffffff'] as $accent) {
            $sources = Aurora::fromParts($accent, 'accent', null, 0, true)->sources;
            foreach ($sources as $source) {
                $this->assertMatchesRegularExpression('/^#([0-9a-f]{2})\1\1$/', $source, "{$accent} gives a grey");
            }
        }
    }

    public function test_a_new_schedule_starts_on_a_palette_and_its_accent(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $start = Aurora::randomStart();
            $this->assertArrayHasKey($start['palette'], Aurora::PALETTES);
            $this->assertSame(Aurora::PALETTES[$start['palette']]['accent'], $start['accent']);
            $this->assertGreaterThanOrEqual(0, $start['seed']);
            $this->assertLessThanOrEqual(65535, $start['seed']);
        }
    }

    /** A stored value cannot reach the stylesheet as anything but numbers. */
    public function test_nothing_stored_escapes_the_stylesheet(): void
    {
        $hostile = '#fff;}</style><script>alert(1)</script>, red, #00ff00';
        $aurora = Aurora::fromParts('#4e81fa;}body{x', 'custom', $hostile, 70000, true);

        $css = $aurora->css('.gp-bg').$aurora->keyframes().implode('', $aurora->lightStyles()).$aurora->staticGradient('dark');
        $this->assertStringNotContainsString('<', $css);
        $this->assertStringNotContainsString('script', $css);
        $this->assertStringNotContainsString('red', $css);
        $this->assertSame(65535, $aurora->seed, 'the seed is held to sixteen bits');
        $this->assertSame('accent', $aurora->palette, 'a custom palette without three colours stands on the accent');

        $this->assertSame(['#00ff00'], Aurora::colorList($hostile), 'the list keeps only whole colours');
        $this->assertFalse(Aurora::isPalette('custom;}'));
        $this->assertNull(Aurora::hex('#12345g'));
        $this->assertSame('#aabbcc', Aurora::hex('#ABC'));
    }

    public function test_the_lights_and_their_drift_follow_the_composition(): void
    {
        $aurora = Aurora::fromParts('#4e81fa', 'lagoon', null, 3, false);

        $this->assertCount(count(Aurora::COMPOSITIONS[3]), $aurora->lightStyles());
        $this->assertSame(['data-aurora-comp' => '3', 'data-aurora-mirror' => '0', 'data-aurora-motion' => 'off'], $aurora->attributes());
        $this->assertSame('on', Aurora::fromParts('#4e81fa', 'lagoon', null, 3, true)->attributes()['data-aurora-motion']);
        $this->assertSame('off', Aurora::fromParts('#4e81fa', 'lagoon', null, 3, true)->attributes(still: true)['data-aurora-motion']);
        $this->assertSame(count(Aurora::COMPOSITIONS[3]), substr_count($aurora->keyframes(), '@keyframes aurora-k'));
        $this->assertStringContainsString(':where(.dark) .gp-bg{', $aurora->css('.gp-bg'));
    }

    public function test_dominant_colours_are_read_from_a_picture(): void
    {
        $image = imagecreatetruecolor(60, 30);
        imagefilledrectangle($image, 0, 0, 39, 29, imagecolorallocate($image, 220, 40, 40));
        imagefilledrectangle($image, 40, 0, 59, 29, imagecolorallocate($image, 30, 60, 200));
        ob_start();
        imagepng($image);
        $bytes = ob_get_clean();
        imagedestroy($image);

        $colors = Aurora::dominantColors($bytes);

        $this->assertCount(3, $colors);
        [$r, $g, $b] = sscanf($colors[0], '#%02x%02x%02x');
        $this->assertGreaterThan($b, $r, 'the red that fills two thirds of it comes first');
        $this->assertNull(Aurora::dominantColors('not an image'));
    }
}
