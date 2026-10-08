<?php

namespace Tests\Unit;

use App\Utils\ColorUtils;
use App\Utils\NewsletterTheme;
use Tests\TestCase;

/**
 * NewsletterTheme is where every colour of a newsletter comes from, and the colours are the
 * OWNER's: three free choices. Its promise is that whatever they are, every text reads on every
 * surface it is painted on (WCAG AA, 4.5:1). The awkward pages are the mid-tones, where neither
 * black nor white has much room, and the saturated ones, where a surface derived from the page
 * can want the other pole: before the sweep below, ten pairs fell under the line, as low as 2.92.
 *
 * Built in memory: no database.
 */
class NewsletterThemeTest extends TestCase
{
    private const PAGES = ['#000000', '#111111', '#1a1a2e', '#333333', '#555555', '#6a6a6a', '#757575', '#777777', '#808080', '#8a8a8a', '#999999', '#bbbbbb',
        '#dddddd', '#f5f5f5', '#faf9f6', '#ffffff', '#ff0000', '#00ff00', '#0000ff', '#ffff00', '#00ffff', '#ff00ff', '#7f1d1d', '#fef08a', '#008080', '#e94560', '#4e81fa'];

    private const ACCENTS = ['#000000', '#ffffff', '#777777', '#4e81fa', '#ffd400', '#e94560', '#2d6a4f'];

    private const TEXTS = ['#333333', '#ffffff', '#777777', '#eaeaea'];

    public function test_every_text_reads_on_every_surface_whatever_three_colours_are_chosen(): void
    {
        $below = [];
        $checked = 0;

        foreach (NewsletterTheme::DESIGNS as $design) {
            foreach (self::PAGES as $page) {
                // The page's own colour as accent and as text are the hardest two of all.
                foreach ([...self::ACCENTS, $page] as $accent) {
                    foreach ([...self::TEXTS, $page] as $text) {
                        $nl = NewsletterTheme::make($design, ['backgroundColor' => $page, 'accentColor' => $accent, 'textColor' => $text], false);
                        $checked++;

                        $pairs = [
                            'ink on the sheet' => [$nl->ink, $nl->sheet], 'ink on a panel' => [$nl->ink, $nl->panel], 'ink on the tint' => [$nl->ink, $nl->accentTint],
                            'second ink on the sheet' => [$nl->ink2, $nl->sheet], 'second ink on a panel' => [$nl->ink2, $nl->panel], 'second ink on the tint' => [$nl->ink2, $nl->accentTint],
                            'third ink on the sheet' => [$nl->ink3, $nl->sheet], 'third ink on a panel' => [$nl->ink3, $nl->panel], 'third ink on the tint' => [$nl->ink3, $nl->accentTint],
                            'accent as text on the sheet' => [$nl->accentInk, $nl->sheet], 'accent as text on a panel' => [$nl->accentInk, $nl->panel], 'accent as text on the tint' => [$nl->accentInk, $nl->accentTint],
                            'a button label' => [$nl->onAccent, $nl->accent],
                            'the footer on the ground' => [$nl->footInk, $nl->ground],
                            'the name on the ground' => [$nl->groundInk, $nl->ground],
                            'an icon on its disc' => ['#ffffff', $nl->discFill],
                        ];

                        foreach ($pairs as $what => [$ink, $surface]) {
                            $ratio = ColorUtils::getContrastRatio($ink, $surface);
                            // 4.495: the ratio of two rounded hex colours, compared as it is shown.
                            if ($ratio < 4.495) {
                                $below[] = sprintf('%s, page %s accent %s text %s: %s (%s on %s) is %.2f', $design, $page, $accent, $text, $what, $ink, $surface, $ratio);
                            }
                        }
                    }
                }
            }
        }

        $this->assertSame(5400, $checked);
        $this->assertSame([], array_slice($below, 0, 12), count($below).' pairs under 4.5:1');
    }

    public function test_a_rule_drawn_in_the_accent_can_be_seen_when_the_accent_is_the_page(): void
    {
        foreach (['#111111', '#ffffff', '#1a1a2e'] as $colour) {
            $nl = NewsletterTheme::make('bold', ['backgroundColor' => $colour, 'accentColor' => $colour, 'textColor' => '#888888'], false);

            $this->assertGreaterThanOrEqual(4.495, ColorUtils::getContrastRatio($nl->accentMark, $nl->sheet), $colour);
        }

        // An accent that already stands out is drawn as the buttons are.
        $bold = NewsletterTheme::make('bold', ['backgroundColor' => '#1a1a2e', 'accentColor' => '#e94560'], false);
        $this->assertSame($bold->accent, $bold->accentMark);
    }

    public function test_a_long_text_block_is_styled_and_never_answered_with_nothing(): void
    {
        $nl = NewsletterTheme::make('modern', [], false);

        // prose() used to finish with patterns that walked to the end of the text a character at a
        // time. At this length preg_replace() gave up and answered null, the return type made
        // that an Error, and the send job catches \Exception: the whole newsletter failed.
        $list = '<p>Hello</p><ul>'.str_repeat('<li>item</li>', 5000).'</ul>';
        $styled = $nl->prose($list);
        $this->assertSame(5000, substr_count($styled, '<li style="margin: 0 0 6px;">'));
        $this->assertStringContainsString('<ul style="margin: 0 0 16px; padding-left: 22px;">', $styled);

        $this->assertStringContainsString('word '.'</p>', $nl->prose('<p>Hello</p><p>'.str_repeat('word ', 80000).'</p>'));
    }

    public function test_prose_gives_each_bare_tag_the_pages_own_look(): void
    {
        $nl = NewsletterTheme::make('modern', [], false);

        // A paragraph indented four spaces is a code block, which does not wrap by itself and laid
        // the whole mail out as wide as its longest line.
        $this->assertStringContainsString('white-space: pre-wrap; word-break: break-word;', $nl->prose("<pre><code>a long line\n</code></pre>"));
        // "?style=" in an address is not a style of the link's own.
        $this->assertStringContainsString('lifestyle=1" style="color: '.$nl->accentInk.';', $nl->prose('<p><a href="https://shop.example.com/?style=vintage&amp;lifestyle=1">shop</a></p>'));
        // Bold inside a link keeps the link's colour; outside it takes the ink.
        $this->assertStringContainsString('<a href="https://x.example.com" style="color: '.$nl->accentInk.'; text-decoration: underline;">see <strong>Tickets</strong></a> and <strong style="color: '.$nl->ink.';">doors</strong>', $nl->prose('<p><a href="https://x.example.com">see <strong>Tickets</strong></a> and <strong>doors</strong></p>'));
        // What the owner styled, and what is not ours, is left as it came.
        $this->assertSame('<p style="color: red;">kept</p><abbr title="x">y</abbr><br><br />', $nl->prose('<p style="color: red;">kept</p><abbr title="x">y</abbr><br><br />'));
        $this->assertStringContainsString('<hr style="border: 0;', $nl->prose('<hr />'));
        $this->assertStringEndsWith('" />', $nl->prose('<hr />'));
        $this->assertStringContainsString('<h5 style="', $nl->prose('<h5>Small</h5>'));

        // Every element keeps its gap, the last one too: the Text block's cell allows for it.
        $this->assertSame(2, substr_count($nl->prose('<p>One</p><p>Two</p>'), 'margin: 0 0 '.$nl->para.'px;'));
        $this->assertSame($nl->gap - 16, $nl->gap - $nl->para);

        // Hebrew and Arabic have no italic.
        $rtl = NewsletterTheme::make('modern', [], true);
        $this->assertStringContainsString('<em style="font-style: normal;">', $rtl->prose('<p><em>emphasis</em></p>'));
        $this->assertStringContainsString('<em>emphasis</em>', $nl->prose('<p><em>emphasis</em></p>'));
        $this->assertStringContainsString('padding-right: 22px;', $rtl->prose('<ul><li>a</li></ul>'));
    }

    public function test_a_separator_can_be_broken_after_and_a_time_stays_left_to_right(): void
    {
        $nl = NewsletterTheme::make('minimal', [], false);

        // Glued with no-break spaces on both sides, a line of several facts was one unbreakable
        // run, wider than a phone.
        $this->assertStringEndsWith('</span> ', $nl->dot());
        $this->assertStringContainsString('&middot;&nbsp; <span dir="ltr"', $nl->when('zaterdag', '20:00'));
    }
}
