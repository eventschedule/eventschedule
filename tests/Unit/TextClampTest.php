<?php

namespace Tests\Unit;

use App\Utils\TextUtils;
use PHPUnit\Framework\TestCase;

/**
 * The clamp that stands between AI output and a varchar column.
 *
 * Every case here is a way the naive version (substr) gets it wrong: on bytes it splits a
 * multi-byte character and under-fills the column, and returning '' for a whitespace-only tail
 * writes the value that means "nothing to translate" into a column that still needs translating.
 */
class TextClampTest extends TestCase
{
    public function test_a_value_that_fits_is_returned_untouched(): void
    {
        $this->assertNull(TextUtils::clamp(null, 255));
        $this->assertSame('', TextUtils::clamp('', 255));
        $this->assertSame('Vakantiepark Sandur', TextUtils::clamp('Vakantiepark Sandur', 255));
        $this->assertSame('exactly-ten', TextUtils::clamp('exactly-ten', 11), 'a value at the ceiling is not an overflow');
    }

    public function test_an_overflow_is_cut_to_the_ceiling_and_keeps_its_prefix(): void
    {
        $value = str_repeat('a', 300);

        $clamped = TextUtils::clamp($value, 255);

        $this->assertSame(255, mb_strlen($clamped));
        $this->assertStringStartsWith(mb_substr($value, 0, 255), $clamped);
    }

    /**
     * The reason this is mb_substr. Cutting a Hebrew string on bytes lands mid-character, so the
     * column gets mojibake AND fewer than 255 characters of it.
     */
    public function test_multibyte_text_is_cut_on_characters_not_bytes(): void
    {
        $value = str_repeat('א', 300);

        $clamped = TextUtils::clamp($value, 255);

        $this->assertSame(255, mb_strlen($clamped), 'the column holds 255 characters, not 255 bytes');
        $this->assertSame($value !== '' ? mb_substr($value, 0, 255) : '', $clamped);
        $this->assertTrue(mb_check_encoding($clamped, 'UTF-8'), 'the cut must not split a character');
    }

    public function test_whitespace_at_the_cut_is_trimmed(): void
    {
        $value = str_repeat('a', 250).'     tail';

        $clamped = TextUtils::clamp($value, 255);

        $this->assertSame(str_repeat('a', 250), $clamped);
    }

    /**
     * '' is the translation columns' marker for "source language equals target". A whereNull()
     * cannot see it, so writing one by accident parks the row permanently.
     */
    public function test_a_value_that_trims_away_entirely_becomes_null_not_an_empty_string(): void
    {
        $this->assertNull(TextUtils::clamp(str_repeat(' ', 300), 255));
    }

    /** '' is the only value worth discarding. "0" is falsy in PHP and must survive anyway. */
    public function test_a_falsy_but_real_value_survives(): void
    {
        $this->assertSame('0', TextUtils::clamp('0', 255));
        $this->assertSame('0', TextUtils::clamp('0'.str_repeat(' ', 300), 1));
    }
}
