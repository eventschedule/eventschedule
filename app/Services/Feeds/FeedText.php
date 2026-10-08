<?php

namespace App\Services\Feeds;

use App\Utils\ImportAddress;
use App\Utils\MarkdownUtils;

/**
 * A source's HTML as the text an event keeps (Markdown).
 *
 * Pictures are dropped: an event has one flyer, taken on purpose, and a description that pulls
 * images from somebody else's server on every page view is a tracker and a hotlink. Links are
 * made whole against the address the text came from, and one that is not a web link goes.
 */
final class FeedText
{
    public static function details(string $html, string $baseUrl): string
    {
        $html = trim($html);

        if ($html === '') {
            return '';
        }

        $html = preg_replace('#<img\b[^>]*>#i', '', $html) ?? $html;
        $html = preg_replace('#<(script|style|iframe|object|embed|form)\b.*?</\1\s*>#is', '', $html) ?? $html;

        $html = preg_replace_callback('#(<a\b[^>]*?\bhref\s*=\s*)(["\'])(.*?)\2#is', function (array $m) use ($baseUrl) {
            $link = ImportAddress::absolute(html_entity_decode($m[3], ENT_QUOTES | ENT_HTML5, 'UTF-8'), $baseUrl);

            return $m[1].'"'.htmlspecialchars($link, ENT_QUOTES, 'UTF-8').'"';
        }, $html) ?? $html;

        // A link left with nowhere to go comes out of the conversion as its words.
        return trim(MarkdownUtils::convertHtmlToMarkdown($html));
    }
}
