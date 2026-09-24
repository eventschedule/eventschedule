<?php

namespace App\Utils;

/**
 * The bits of the marketing head that have exactly one right answer, kept in one place so a page
 * cannot quietly get them wrong.
 */
class SeoUtils
{
    /**
     * The flag set every JSON-LD block is encoded with.
     *
     * JSON_HEX_TAG is the load-bearing one. A <script type="application/ld+json"> element is raw
     * text, so a literal "</script>" inside a title, an event name or an FAQ answer closes the
     * element early and lets the rest of the string run as markup - something the {{ }}
     * interpolation these blocks used to use could not do, because it HTML-escaped. JSON_HEX_AMP
     * costs nothing and keeps the same payload safe if it is ever moved into an attribute.
     *
     * The two UNESCAPED flags are why the encoder was reached for in the first place: they keep
     * URLs and non-Latin copy readable in the source.
     */
    public const JSON_LD_FLAGS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP;

    /**
     * Encode a JSON-LD payload, or a single value spliced into a hand-written block.
     *
     * Always echo the result with {!! !!}: Blade's {{ }} HTML-escapes, which turns valid JSON into
     * text no consumer can parse. The escaping that matters is done here.
     */
    public static function jsonLd(mixed $payload, bool $pretty = false): string
    {
        return (string) json_encode($payload, self::JSON_LD_FLAGS | ($pretty ? JSON_PRETTY_PRINT : 0));
    }

    /**
     * The robots directives of a guest page that may be indexed - the same string
     * layouts/marketing.blade.php gives a marketing page. max-image-preview:large is what lets
     * Google show a large image in Discover and in results; without it the default is a thumbnail
     * at most. The two -1s lift the default caps on snippet and video preview length.
     */
    public const ROBOTS_INDEX = 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';

    /** Punctuation that already ends a sentence or a clause, so a boundary after it needs no ". ". */
    private const ENDING_PUNCTUATION = '.!?…:;。！？؟';

    /**
     * Owner HTML (a rendered markdown description) as one line of plain text, for a meta
     * description or a structured-data field.
     *
     * strip_tags() alone is not that. It deletes a boundary without leaving anything in its place,
     * so a line break or the end of a paragraph glued the last word of one line to the first of the
     * next - "Spirit" and "Continue" became "SpiritContinue" - and MarkdownUtils renders every
     * single newline as a <br>. A <br> or the end of a block therefore becomes ". " when the text
     * so far has no ending punctuation (a heading, a list item, a line of a poster), otherwise a
     * space. Table cells are a plain space: a row is one line, not a sentence per cell.
     *
     * Entities are decoded ONCE here, so Blade's {{ }} escapes the result exactly once: the old
     * value went out still encoded and "&amp;" reached the page as "&amp;amp;". The U+00A0 spacer
     * paragraphs MarkdownUtils::appendBlankRun() writes for extra blank lines collapse with the rest
     * of the whitespace.
     */
    public static function plainText(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $html = mb_scrub($html, 'UTF-8');

        // Cells first, before the split below eats the row they sit in.
        $html = preg_replace('~</t[dh]\s*>~i', ' ', $html) ?? $html;

        $segments = preg_split('~<br\s*/?>|</(?:p|div|li|h[1-6]|blockquote|tr|pre)\s*>~i', $html) ?: [$html];

        $text = '';

        foreach ($segments as $segment) {
            $segment = self::cleanText(strip_tags($segment));

            if ($segment === '') {
                continue;
            }

            if ($text === '') {
                $text = $segment;

                continue;
            }

            $text .= (self::endsWithPunctuation($text) ? ' ' : '. ').$segment;
        }

        return $text;
    }

    /**
     * A PLAIN-text input - a short description, a name - tidied for a meta tag.
     *
     * No strip_tags(): this text was typed, not rendered, so "I <3 jazz" is content and must
     * survive. Entities are decoded (an import can store "Rock &amp; Roll" in a plain column, which
     * Blade would otherwise print as "&amp;amp;"), invisible characters go, whitespace collapses to
     * single spaces, and " , " - the separator the guest pages render as a line break in a name -
     * reads as the comma it is.
     *
     * Only the characters that are always junk are removed: U+200B zero-width space, U+2060 word
     * joiner, U+FEFF byte-order mark and U+00AD soft hyphen, plus control characters (as spaces).
     * The zero-width joiner and non-joiner stay (emoji sequences, Persian and Indic scripts), and
     * so do the bidi marks that keep mixed Hebrew or Arabic text in order.
     */
    public static function cleanText(?string $text): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        $text = html_entity_decode(mb_scrub($text, 'UTF-8'), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $text = preg_replace('/[\x{200B}\x{2060}\x{FEFF}\x{00AD}]/u', '', $text) ?? $text;
        $text = preg_replace('/[\s\p{Z}\p{Cc}]+/u', ' ', $text) ?? $text;

        return trim(str_replace(' , ', ', ', $text));
    }

    /**
     * $text cut to at most $max characters for a meta description.
     *
     * At the last sentence end that keeps at least 60% of $max, as it stands: a whole sentence
     * needs no ellipsis. Otherwise at the last space past that point, and otherwise - CJK runs have
     * no spaces to cut at - mid-run; both of those end in "…", counted inside $max. Everything is
     * in characters, never bytes, so a Hebrew or Japanese description is not cut mid-character.
     */
    public static function excerpt(string $text, int $max = 155): string
    {
        $text = trim($text);

        if (mb_strlen($text) <= $max) {
            return $text;
        }

        $chars = mb_str_split($text);
        $floor = (int) ceil($max * 0.6);

        // A sentence end: CJK full-width punctuation anywhere, the rest only before a space (so the
        // "." in "3.5" or in a URL is not one).
        for ($i = $max - 1; $i >= $floor - 1; $i--) {
            $char = $chars[$i];

            if (in_array($char, ['。', '！', '？'], true)
                || (in_array($char, ['.', '!', '?', '…', '؟'], true) && preg_match('/^\s$/u', $chars[$i + 1] ?? ''))) {
                return implode('', array_slice($chars, 0, $i + 1));
            }
        }

        // One character is kept back for the ellipsis. $chars[$i] is the first one dropped.
        for ($i = $max - 1; $i >= $floor; $i--) {
            if (preg_match('/^\s$/u', $chars[$i])) {
                // A regex, not rtrim(): rtrim()'s character list is bytes, and "·" or a dash is
                // two or three of them - it would eat the tail of a Hebrew letter sharing one.
                $kept = preg_replace('/[\s,;:\x{00B7}\x{2013}\x{2014}-]+$/u', '', implode('', array_slice($chars, 0, $i))) ?? '';

                if ($kept !== '') {
                    return $kept.'…';
                }
            }
        }

        return rtrim(implode('', array_slice($chars, 0, $max - 1))).'…';
    }

    /**
     * The first of $candidates that fits in $max characters, else the last one.
     *
     * For titles built from richest to barest: order them so the last candidate is the floor you
     * accept even when it is too long.
     *
     * @param  array<int, string>  $candidates
     */
    public static function fitTitle(array $candidates, int $max = 60): string
    {
        foreach ($candidates as $candidate) {
            if (mb_strlen($candidate) <= $max) {
                return $candidate;
            }
        }

        return (string) (end($candidates) ?: '');
    }

    /**
     * A social image as og:image wants it: the URL, and its pixel size when that is known.
     *
     * $dimensions is the hook for sizes recorded at upload time (Phase F stores them with the
     * image variants). Until then the only source is imageDimensions(), which reads files this app
     * serves out of public/ - the bundled demo images and a local-disk install's /storage - and
     * says nothing about a CDN URL, so the width and height are simply left out there. A declared
     * size the file does not have is worse than none: scrapers crop to it.
     *
     * @param  array{0: int, 1: int}|null  $dimensions
     * @return array{url: string, width?: int, height?: int}|null
     */
    public static function imageObject(?string $url, ?array $dimensions = null): ?array
    {
        if (! $url) {
            return null;
        }

        $image = ['url' => $url];

        if ($size = $dimensions ?? self::imageDimensions($url)) {
            $image['width'] = (int) $size[0];
            $image['height'] = (int) $size[1];
        }

        return $image;
    }

    /**
     * An imageObject() result as a schema.org ImageObject node, for JSON-LD: the same URL, and the
     * same width and height only when they are known.
     *
     * @param  array{url: string, width?: int, height?: int}|null  $image
     * @return array<string, string|int>|null
     */
    public static function schemaImageObject(?array $image): ?array
    {
        if (! $image || empty($image['url'])) {
            return null;
        }

        return ['@type' => 'ImageObject'] + array_intersect_key($image, array_flip(['url', 'width', 'height']));
    }

    /**
     * Whether $text already ends a sentence or a clause, so whatever follows it needs a space and
     * not a ". ".
     */
    public static function endsWithPunctuation(string $text): bool
    {
        $last = mb_substr(rtrim($text), -1);

        return $last !== '' && mb_strpos(self::ENDING_PUNCTUATION, $last) !== false;
    }

    /**
     * The real pixel size of an image this app serves out of its own public/ directory, or null
     * for anything it cannot read.
     *
     * og:image:width and og:image:height have to describe the actual bytes: a declared size the
     * file does not have gets the image re-cropped by every scraper that trusts the tags. The
     * generated social cards are all 1200x630, but a blog post ships its own 1200x600 twin
     * (BlogPost::socialImageUrl()), so the layout cannot hardcode one pair for both.
     *
     * Only same-app URLs are read: config('app.url') and the host serving the request, which is
     * how the blog subdomain's own images resolve. getimagesize() opens the file, so the result is
     * memoised per resolved path - stable for the life of the process, since the bytes are not
     * rewritten under a running container.
     *
     * @return array{0: int, 1: int}|null
     */
    public static function imageDimensions(?string $url): ?array
    {
        static $memo = [];

        $file = self::localPublicFile($url);

        if ($file === null) {
            return null;
        }

        if (! array_key_exists($file, $memo)) {
            $size = @getimagesize($file);

            $memo[$file] = ($size && $size[0] > 0 && $size[1] > 0)
                ? [(int) $size[0], (int) $size[1]]
                : null;
        }

        return $memo[$file];
    }

    /** The absolute path of the public/ file a URL names, or null if it names anything else. */
    private static function localPublicFile(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $parts = parse_url($url);

        if ($parts === false || empty($parts['path'])) {
            return null;
        }

        $host = $parts['host'] ?? null;

        if ($host !== null && ! in_array(strtolower($host), self::ownHosts(), true)) {
            return null;
        }

        $root = realpath(public_path());
        $file = realpath(public_path(urldecode(ltrim($parts['path'], '/'))));

        // realpath() has already resolved any ../ in the path, so the prefix check is what keeps a
        // crafted URL from reading a file outside the document root.
        if ($root === false || $file === false || ! is_file($file)) {
            return null;
        }

        return str_starts_with($file, $root.DIRECTORY_SEPARATOR) ? $file : null;
    }

    /**
     * The hosts this install answers on: the configured app URL, plus whatever host is serving the
     * current request. The blog is a second subdomain of the same document root, so its images are
     * ours even though they do not sit under config('app.url').
     *
     * @return array<int, string>
     */
    private static function ownHosts(): array
    {
        $hosts = [];

        foreach ([parse_url((string) config('app.url'), PHP_URL_HOST), request()?->getHost()] as $host) {
            if (is_string($host) && $host !== '') {
                $hosts[] = strtolower($host);
            }
        }

        return array_unique($hosts);
    }
}
