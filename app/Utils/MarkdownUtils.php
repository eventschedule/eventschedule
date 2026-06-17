<?php

namespace App\Utils;

use HTMLPurifier;
use HTMLPurifier_Config;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\MarkdownConverter;

class MarkdownUtils
{
    private const MAX_SPACER_PARAGRAPHS = 10;

    public static function convertToHtml($markdown)
    {
        if (! $markdown) {
            return $markdown;
        }

        // Preserve extra vertical spacing the author typed as multiple blank lines.
        $markdown = self::preserveBlankLines($markdown);

        $environment = new Environment([
            // Pass raw HTML through to HTMLPurifier, which sanitizes it below.
            'html_input' => 'allow',
            'renderer' => [
                'soft_break' => '<br>',
            ],
            'heading_permalink' => [
                // Put the slug id directly on the <h*> element so in-page anchor
                // links like [Intro](#intro) jump to "## Intro".
                'apply_id_to_heading' => true,
                // Do not insert a visible permalink anchor/symbol.
                'insert' => 'none',
                // No id prefix, so the id equals the heading slug exactly.
                'id_prefix' => '',
                'fragment_prefix' => '',
            ],
        ]);
        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new HeadingPermalinkExtension);

        $converter = new MarkdownConverter($environment);
        $html = $converter->convert($markdown)->getContent();

        // HTMLPurifier is the security boundary: it sanitizes the HTML that
        // CommonMark passed through, stripping <script>, event handlers, iframes,
        // and javascript: URIs while keeping safe formatting and <img>.
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.TargetBlank', true);
        $config->set('HTML.Nofollow', true);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        // Keep heading ids so in-page table-of-contents links work.
        $config->set('Attr.EnableID', true);
        // Allow ids that start with a digit (e.g. "2024-recap").
        $config->set('Attr.ID.HTML5', true);
        // Defense-in-depth: stop user-authored ids (including heading slugs) from
        // clobbering app element ids that page JavaScript looks up. Keep this list
        // in sync if those element ids change.
        $config->set('Attr.IDBlacklist', [
            'app', 'ticket-selector', 'event-form-section',
            'desktop-cta-buttons', 'mobile-cta-bar', 'name', 'agenda',
            'calendar-pop-up-menu', 'calendar-card-dropdown',
            'calendar-mobile-sheet', 'calendar-mobile-overlay', 'mobile-calendar-cta',
        ]);

        $purifier = new HTMLPurifier($config);

        return $purifier->purify($html);
    }

    /**
     * Convert HTML (e.g. from Google Calendar, CalDAV, or Eventbrite) to Markdown.
     *
     * Event descriptions are stored as Markdown; the Event saving hook renders
     * them to *_html via convertToHtml(). Plain-text input is passed through
     * (entity-decoded only) so characters like '<' survive strip_tags().
     */
    public static function convertHtmlToMarkdown(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        // No real HTML tags -> treat as plain text (decode entities, do NOT strip_tags).
        if (! preg_match('/<\/?(a|b|i|u|p|br|ul|ol|li|strong|em|h[1-6]|span|div|font|img|blockquote|pre|code)\b[^>]*>/i', $html)) {
            return trim(html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        $text = $html;

        // Headings
        $text = preg_replace('/<h1[^>]*>(.*?)<\/h1>/si', "# $1\n\n", $text);
        $text = preg_replace('/<h2[^>]*>(.*?)<\/h2>/si', "## $1\n\n", $text);
        $text = preg_replace('/<h3[^>]*>(.*?)<\/h3>/si', "### $1\n\n", $text);
        $text = preg_replace('/<h4[^>]*>(.*?)<\/h4>/si', "#### $1\n\n", $text);

        // Bold / italic
        $text = preg_replace('/<(strong|b)[^>]*>(.*?)<\/(strong|b)>/si', '**$2**', $text);
        $text = preg_replace('/<(em|i)[^>]*>(.*?)<\/(em|i)>/si', '*$2*', $text);

        // Links
        $text = preg_replace('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/si', '[$2]($1)', $text);

        // Lists
        $text = preg_replace('/<li[^>]*>(.*?)<\/li>/si', "- $1\n", $text);
        $text = preg_replace('/<\/?[ou]l[^>]*>/si', "\n", $text);

        // Line breaks / paragraphs
        $text = preg_replace('/<br\s*\/?>/si', "\n", $text);
        $text = preg_replace('/<\/p>/si', "\n\n", $text);
        $text = preg_replace('/<p[^>]*>/si', '', $text);

        // Images
        $text = preg_replace('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/si', '![]($1)', $text);

        // Remaining block-level elements -> line breaks (e.g. <div>, <h5>/<h6>, <blockquote>, <pre>).
        $text = preg_replace('#</(div|h[1-6]|blockquote|pre)>#si', "\n", $text);

        // Strip remaining tags, decode entities, tidy whitespace
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }

    /**
     * Preserve vertical spacing the author typed as multiple blank lines.
     *
     * CommonMark collapses any run of blank lines into one paragraph break, so
     * authors could not separate sections without falling back to &nbsp;. We turn
     * each run of 2+ blank lines (outside fenced code) into that many spacer
     * paragraphs. A single blank line keeps its normal paragraph-break meaning;
     * a single newline becomes a <br> via the soft_break renderer option.
     */
    private static function preserveBlankLines(string $markdown): string
    {
        $lines = preg_split('/\r\n|\r|\n/', $markdown);
        $result = [];
        $inFence = false;
        $fenceMarker = null;
        $blankRun = 0;
        $seenContent = false;

        foreach ($lines as $line) {
            $isFence = (bool) preg_match('/^ {0,3}(`{3,}|~{3,})/', $line, $m);

            if ($isFence) {
                if (! $inFence) {
                    self::appendBlankRun($result, $blankRun, $seenContent);
                    $blankRun = 0;
                    $inFence = true;
                    $fenceMarker = $m[1][0];
                    $result[] = $line;
                    $seenContent = true;
                } elseif ($m[1][0] === $fenceMarker) {
                    $inFence = false;
                    $fenceMarker = null;
                    $result[] = $line;
                } else {
                    $result[] = $line; // different marker inside a fence = code content
                }

                continue;
            }

            if ($inFence) {
                $result[] = $line; // verbatim inside code

                continue;
            }

            if (trim($line) === '') {
                $blankRun++;

                continue;
            }

            self::appendBlankRun($result, $blankRun, $seenContent);
            $blankRun = 0;
            $result[] = $line;
            $seenContent = true;
        }

        // Trailing blank run (end of document): keep verbatim, never expand.
        for ($i = 0; $i < $blankRun; $i++) {
            $result[] = '';
        }

        return implode("\n", $result);
    }

    /**
     * Append a run of blank lines to the output, expanding runs of 2+ blanks
     * that sit between content into spacer paragraphs (one non-breaking space
     * each). Leading and trailing runs are emitted verbatim.
     */
    private static function appendBlankRun(array &$result, int $blankRun, bool $seenContent): void
    {
        if ($blankRun <= 0) {
            return;
        }

        if ($seenContent && $blankRun >= 2) {
            $spacers = min($blankRun - 1, self::MAX_SPACER_PARAGRAPHS);
            $result[] = '';
            for ($i = 0; $i < $spacers; $i++) {
                $result[] = '&nbsp;';
                $result[] = '';
            }

            return;
        }

        for ($i = 0; $i < $blankRun; $i++) {
            $result[] = '';
        }
    }

    /**
     * Sanitize HTML content to prevent XSS attacks.
     * Allows common HTML tags while stripping dangerous content.
     */
    public static function sanitizeHtml(?string $html): string
    {
        if (! $html) {
            return '';
        }

        $config = HTMLPurifier_Config::createDefault();
        // Allow common HTML elements for blog content
        $config->set('HTML.Allowed', 'p,br,strong,b,em,i,u,a[href|target|rel],ul,ol,li,h1,h2,h3,h4,h5,h6,blockquote,pre,code,img[src|alt|width|height],table,thead,tbody,tr,th,td,div,span[style],hr');
        $config->set('HTML.TargetBlank', true);
        // Force rel="noopener" on links
        $config->set('HTML.Nofollow', true);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        // Limit URI schemes
        $config->set('URI.AllowedSchemes', ['http', 'https', 'mailto']);

        $purifier = new HTMLPurifier($config);

        return $purifier->purify($html);
    }
}
