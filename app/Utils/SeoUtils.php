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
