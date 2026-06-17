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
}
