<?php

namespace Tests\Unit;

use App\Utils\MarkdownUtils;
use PHPUnit\Framework\TestCase;

class MarkdownUtilsTest extends TestCase
{
    public function test_converts_common_html_to_markdown(): void
    {
        $md = MarkdownUtils::convertHtmlToMarkdown(
            '<p>Join us for <strong>drinks</strong> at <a href="https://example.com">the bar</a>.</p>'
            .'<ul><li>Free entry</li><li>Live music</li></ul>'
        );

        $this->assertStringContainsString('**drinks**', $md);
        $this->assertStringContainsString('[the bar](https://example.com)', $md);
        $this->assertStringContainsString('- Free entry', $md);
        $this->assertStringContainsString('- Live music', $md);
        $this->assertStringNotContainsString('<', $md);
    }

    public function test_preserves_plain_text_with_angle_brackets(): void
    {
        $plain = 'Q3 planning: revenue > target, costs < budget';
        $this->assertSame($plain, MarkdownUtils::convertHtmlToMarkdown($plain));
    }

    public function test_decodes_entities_in_plain_text(): void
    {
        $this->assertSame('Tom & Jerry', MarkdownUtils::convertHtmlToMarkdown('Tom &amp; Jerry'));
    }

    public function test_converts_line_breaks(): void
    {
        $this->assertSame("Line 1\nLine 2", MarkdownUtils::convertHtmlToMarkdown('Line 1<br>Line 2'));
    }

    public function test_converts_block_level_tags_to_line_breaks(): void
    {
        $this->assertSame("A\nB", MarkdownUtils::convertHtmlToMarkdown('<div>A</div><div>B</div>'));
    }

    public function test_handles_empty_and_null(): void
    {
        $this->assertSame('', MarkdownUtils::convertHtmlToMarkdown(''));
        $this->assertSame('', MarkdownUtils::convertHtmlToMarkdown(null));
    }

    public function test_round_trip_renders_formatting(): void
    {
        $md = MarkdownUtils::convertHtmlToMarkdown('<p>Hello <strong>world</strong></p>');
        $this->assertStringContainsString('<strong>world</strong>', MarkdownUtils::convertToHtml($md));
    }

    /**
     * An owner's "# Heading" on a guest page becomes a marked <h2>, so the page keeps its one <h1>
     * and the stylesheet can still give the heading an H1's size.
     */
    public function test_demote_h1_marks_the_demoted_headings(): void
    {
        $html = MarkdownUtils::convertToHtml("# Our Story\n\nText\n\n## Lineup\n\n# Tickets");

        $demoted = MarkdownUtils::demoteH1($html);

        $this->assertStringNotContainsString('<h1', $demoted);
        // The heading id (the in-page anchor) survives: only the tag name changes.
        $this->assertStringContainsString('<h2 data-es-h1 id="our-story">Our Story</h2>', $demoted);
        $this->assertStringContainsString('<h2 data-es-h1 id="tickets">Tickets</h2>', $demoted);
        // An existing <h2> is not marked, so it keeps the H2 look.
        $this->assertStringContainsString('<h2 id="lineup">Lineup</h2>', $demoted);
    }

    /** The blog's mode: the same demotion with no marker, which BlogSeoTest pins exactly. */
    public function test_demote_h1_can_leave_the_marker_off(): void
    {
        $this->assertSame(
            '<h2>Selling Tickets</h2><p>Body.</p><h2 class="x">Second</h2>',
            MarkdownUtils::demoteH1('<H1>Selling Tickets</H1><p>Body.</p><h1 class="x">Second</h1>', false)
        );
    }

    public function test_demote_h1_touches_nothing_else(): void
    {
        $untouched = '<h2>A</h2><h10>B</h10><h1x>C</h1x><p>h1 is a word</p>';

        $this->assertSame($untouched, MarkdownUtils::demoteH1($untouched));
        $this->assertSame('', MarkdownUtils::demoteH1(null));
        $this->assertSame('', MarkdownUtils::demoteH1(''));
    }
}
