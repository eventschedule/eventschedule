<?php

namespace Tests\Unit;

use App\Utils\SeoUtils;
use PHPUnit\Framework\TestCase;

/**
 * The text helpers the guest portal's meta descriptions and titles are built from.
 *
 * A crawl of production found a quarter of the event descriptions carrying raw newlines, words
 * glued across a line break ("Spirit" + "Continue" became "SpiritContinue") and none of them
 * entity-decoded, so an "&amp;" reached the page as "&amp;amp;". These pin each of those, and the
 * character-safe cutting a Hebrew or Japanese description needs.
 */
class SeoUtilsTextTest extends TestCase
{
    public function test_entities_are_decoded_once_so_blade_escapes_them_once(): void
    {
        $text = SeoUtils::plainText('<p>Tom &amp; Jerry &quot;live&quot; &#8211; &lt;3</p>');

        $this->assertSame('Tom & Jerry "live" – <3', $text);
        // What the {{ }} in the layout then prints: one level of escaping, never two.
        $this->assertSame('Tom &amp; Jerry &quot;live&quot; – &lt;3', e($text));
    }

    public function test_a_line_break_or_block_end_never_glues_two_words(): void
    {
        // MarkdownUtils renders a single newline as <br>.
        $this->assertSame('Holy Spirit. Continue the journey', SeoUtils::plainText('<p>Holy Spirit<br>Continue the journey</p>'));
        $this->assertSame('Holy Spirit. Continue', SeoUtils::plainText('<p>Holy Spirit</p><p>Continue</p>'));
        $this->assertSame('Lineup. Band A. Band B', SeoUtils::plainText('<h2 id="lineup">Lineup</h2><ul><li>Band A</li><li>Band B</li></ul>'));
        $this->assertSame('Quote. After', SeoUtils::plainText('<blockquote>Quote</blockquote><div>After</div>'));
        $this->assertSame('A B. C D', SeoUtils::plainText('<table><tr><td>A</td><td>B</td></tr><tr><td>C</td><td>D</td></tr></table>'));
    }

    public function test_a_boundary_after_ending_punctuation_is_a_plain_space(): void
    {
        $this->assertSame('Welcome! Doors at 7', SeoUtils::plainText('<p>Welcome!<br>Doors at 7</p>'));
        $this->assertSame('Lineup: Band A', SeoUtils::plainText('<p>Lineup:</p><p>Band A</p>'));
        $this->assertSame('ברוכים הבאים? כן', SeoUtils::plainText('<p>ברוכים הבאים?</p><p>כן</p>'));
    }

    public function test_raw_newlines_and_nbsp_spacers_collapse_to_single_spaces(): void
    {
        // A newline inside one paragraph (HTML from an import), and the U+00A0 spacer paragraphs
        // MarkdownUtils writes for extra blank lines.
        $this->assertSame(
            'line one line two. Second',
            SeoUtils::plainText("<p>line one\r\nline   two</p><p>&nbsp;</p><p>&nbsp;</p><p>Second</p>")
        );
        $this->assertSame('A B', SeoUtils::plainText('<p>A&nbsp;&nbsp;B</p>'));
        $this->assertStringNotContainsString("\n", SeoUtils::plainText("<p>a\nb\tc</p>"));
    }

    public function test_hebrew_text_is_joined_and_kept_whole(): void
    {
        $this->assertSame('ערב ג׳אז. בפארק הירקון', SeoUtils::plainText('<p>ערב ג׳אז</p><p>בפארק הירקון</p>'));
    }

    public function test_clean_text_keeps_what_was_typed(): void
    {
        // No strip_tags() on text that was typed rather than rendered.
        $this->assertSame('I <3 jazz', SeoUtils::cleanText('I <3 jazz'));
        $this->assertSame('Rock & Roll', SeoUtils::cleanText('Rock &amp; Roll'));
        // " , " is the separator a name renders as a line break; in a meta tag it is a comma.
        $this->assertSame('Band A, Band B', SeoUtils::cleanText('Band A , Band B'));
        $this->assertSame('a b c', SeoUtils::cleanText("  a \n\t b\u{00A0}\u{00A0}c  "));
        // Zero-width space and BOM go; the zero-width joiner of an emoji sequence stays.
        $this->assertSame('ab', SeoUtils::cleanText("a\u{200B}b\u{FEFF}"));
        $this->assertSame("\u{1F468}\u{200D}\u{1F469}", SeoUtils::cleanText("\u{1F468}\u{200D}\u{1F469}"));
        $this->assertSame('', SeoUtils::cleanText(null));
        $this->assertSame('', SeoUtils::plainText(null));
    }

    public function test_excerpt_leaves_a_short_text_alone(): void
    {
        $this->assertSame('Short and sweet.', SeoUtils::excerpt('Short and sweet.'));
        $this->assertSame(str_repeat('a', 155), SeoUtils::excerpt(str_repeat('a', 155)));
    }

    public function test_excerpt_cuts_at_a_sentence_end_that_keeps_most_of_the_text(): void
    {
        $sentence = 'The quick brown fox jumps over the lazy dog. ';
        $excerpt = SeoUtils::excerpt(str_repeat($sentence, 6));

        // Three sentences (134 characters) fit; a whole sentence needs no ellipsis.
        $this->assertSame(rtrim(str_repeat($sentence, 3)), $excerpt);
        $this->assertLessThanOrEqual(155, mb_strlen($excerpt));
    }

    public function test_excerpt_falls_back_to_a_word_boundary_with_an_ellipsis_inside_the_limit(): void
    {
        $excerpt = SeoUtils::excerpt(str_repeat('word ', 60));

        $this->assertStringEndsWith('word…', $excerpt);
        $this->assertSame(155, mb_strlen($excerpt), 'the ellipsis counts toward the limit');

        // A sentence end too early in the text (under 60% of the limit) is not taken.
        $early = 'Hi. '.str_repeat('word ', 60);
        $this->assertStringEndsWith('…', SeoUtils::excerpt($early));
        $this->assertGreaterThan(93, mb_strlen(SeoUtils::excerpt($early)));

        // Trailing separators are not left dangling before the ellipsis.
        $dangling = str_repeat('x', 120).' · '.str_repeat('y', 60);
        $this->assertSame(str_repeat('x', 120).'…', SeoUtils::excerpt($dangling));
    }

    public function test_excerpt_cuts_hebrew_on_a_word_boundary_and_never_mid_character(): void
    {
        $excerpt = SeoUtils::excerpt(str_repeat('ערב ג׳אז בפארק ', 20));

        $this->assertTrue(mb_check_encoding($excerpt, 'UTF-8'));
        $this->assertLessThanOrEqual(155, mb_strlen($excerpt));
        $this->assertMatchesRegularExpression('/\p{Hebrew}…$/u', $excerpt);
    }

    public function test_excerpt_hard_cuts_cjk_with_no_spaces_and_honours_its_full_stop(): void
    {
        $run = SeoUtils::excerpt(str_repeat('東京で開催されるジャズイベント', 15));
        $this->assertSame(155, mb_strlen($run));
        $this->assertStringEndsWith('…', $run);
        $this->assertTrue(mb_check_encoding($run, 'UTF-8'));

        // 。 ends a sentence with no space after it.
        $sentences = SeoUtils::excerpt(str_repeat('東京で開催されるジャズイベントです。', 12));
        $this->assertStringEndsWith('です。', $sentences);
        $this->assertLessThanOrEqual(155, mb_strlen($sentences));
    }

    public function test_fit_title_takes_the_first_candidate_that_fits_else_the_last(): void
    {
        $this->assertSame('fits', SeoUtils::fitTitle([str_repeat('x', 61), 'fits', 'also fits'], 60));
        $this->assertSame('floor that is too long', SeoUtils::fitTitle([str_repeat('x', 61), 'floor that is too long'], 10));
        // Characters, not bytes: 60 Hebrew letters are 120 bytes and still fit.
        $hebrew = str_repeat('א', 60);
        $this->assertSame($hebrew, SeoUtils::fitTitle([$hebrew, 'b'], 60));
        $this->assertSame('', SeoUtils::fitTitle([]));
    }
}
