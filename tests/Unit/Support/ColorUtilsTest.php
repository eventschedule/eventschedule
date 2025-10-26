<?php

namespace Tests\Unit\Support;

use App\Support\ColorUtils;
use PHPUnit\Framework\TestCase;

class ColorUtilsTest extends TestCase
{
    public function testNormalizeHexColorReturnsNullForInvalidInputs(): void
    {
        $this->assertNull(ColorUtils::normalizeHexColor(null));
        $this->assertNull(ColorUtils::normalizeHexColor(''));
        $this->assertNull(ColorUtils::normalizeHexColor('not-a-color'));
        $this->assertNull(ColorUtils::normalizeHexColor('#12345'));
        $this->assertNull(ColorUtils::normalizeHexColor('#1234567'));
    }

    public function testNormalizeHexColorExpandsAndUppercasesValues(): void
    {
        $this->assertSame('#AABBCC', ColorUtils::normalizeHexColor('abc'));
        $this->assertSame('#AABBCC', ColorUtils::normalizeHexColor('#aabbcc'));
        $this->assertSame('#112233', ColorUtils::normalizeHexColor(' #123 '));
    }

    public function testHexToRgbConversions(): void
    {
        $this->assertSame([18, 52, 86], ColorUtils::hexToRgb('#123456'));
        $this->assertSame('18, 52, 86', ColorUtils::hexToRgbString('#123456'));
        $this->assertNull(ColorUtils::hexToRgb('invalid'));
        $this->assertNull(ColorUtils::hexToRgbString(null));
    }

    public function testRelativeLuminanceAndContrastRatioCalculations(): void
    {
        $this->assertSame(0.0, ColorUtils::relativeLuminance('#000000'));
        $this->assertSame(1.0, ColorUtils::relativeLuminance('#FFFFFF'));

        $midTone = ColorUtils::relativeLuminance('#123456');
        $this->assertIsFloat($midTone);
        $this->assertEqualsWithDelta(0.0325646688, $midTone, 0.0000000001);

        $this->assertEqualsWithDelta(21.0, ColorUtils::contrastRatio('#000000', '#FFFFFF'), 0.0000001);
        $this->assertNull(ColorUtils::contrastRatio('#000000', 'oops'));
    }

    public function testMixClampsWeightAndReturnsNullForInvalidColors(): void
    {
        $this->assertSame('#000000', ColorUtils::mix('#000000', '#FFFFFF', 1.5));
        $this->assertSame('#FFFFFF', ColorUtils::mix('#000000', '#FFFFFF', -1.0));
        $this->assertSame('#BFBFBF', ColorUtils::mix('#000000', '#FFFFFF', 0.25));
        $this->assertNull(ColorUtils::mix('first', '#FFFFFF'));
    }

    public function testBestContrastingColorSkipsInvalidCandidates(): void
    {
        $best = ColorUtils::bestContrastingColor('#000000', ['#ffffff', 'invalid', 123, '#ff0000']);

        $this->assertIsArray($best);
        $this->assertSame('#FFFFFF', $best['color']);
        $this->assertEqualsWithDelta(21.0, $best['ratio'], 0.0000001);

        $this->assertNull(ColorUtils::bestContrastingColor('invalid', ['#FFFFFF']));
    }
}
