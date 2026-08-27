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

    /**
     * The gap that caused EVENTSCHEDULE-PHP-49: a textarea's maxlength counts a line break as one
     * LF, but a form submits it as CRLF, so a box capped at 500 puts up to 500 + N on the wire.
     */
    public function test_newlines_are_collapsed_to_lf(): void
    {
        $this->assertSame("a\nb", TextUtils::normalizeNewlines("a\r\nb"));
        $this->assertSame("a\nb", TextUtils::normalizeNewlines("a\rb"), 'a lone CR is a line break too');
        $this->assertSame("a\nb\nc", TextUtils::normalizeNewlines("a\r\nb\rc"));
    }

    public function test_normalizing_leaves_everything_else_alone(): void
    {
        $this->assertNull(TextUtils::normalizeNewlines(null));
        $this->assertSame('', TextUtils::normalizeNewlines(''));
        $this->assertSame('no breaks here', TextUtils::normalizeNewlines('no breaks here'));
        $this->assertSame("already\nlf", TextUtils::normalizeNewlines("already\nlf"));
    }

    /** The production shape: exactly at the ceiling in LF, over it in CRLF. */
    public function test_a_crlf_value_at_the_ceiling_fits_once_normalized(): void
    {
        $lf = str_repeat("0123456789\n", 45).str_repeat('x', 50 - 5);
        $this->assertSame(540, mb_strlen($lf), 'fixture sanity');

        $crlf = str_replace("\n", "\r\n", $lf);
        $this->assertSame(540 + 45, mb_strlen($crlf), 'CRLF adds one character per line break');

        $this->assertSame($lf, TextUtils::clamp(TextUtils::normalizeNewlines($crlf), 540));
        $this->assertNotSame($lf, TextUtils::clamp($crlf, 540), 'without normalizing, the tail is cut');
    }
}
