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

    /** The publisher logo, relative to the site root. See organization(). */
    private const ORGANIZATION_LOGO = '/images/dark_logo.png';

    /**
     * The marketing site's root, with no trailing slash: the base every structured-data @id hangs
     * off, so a node on a docs page, the blog host and the homepage all name the same entity.
     */
    public static function siteUrl(): string
    {
        return rtrim((string) config('app.url'), '/');
    }

    /**
     * The one Organization node: the publisher of every marketing page, doc and blog post.
     *
     * One logo for all of them, dark_logo.png - dark ink on a light ground, the mark the site header
     * shows in light mode, which is what a search result or a feed reader draws it on. The layout
     * used the dark logo while the docs and the blog named light_logo.png as the publisher of the
     * very same @id, so two nodes that are meant to merge disagreed about what the publisher looks
     * like.
     *
     * Compact on purpose, so it can be embedded as a publisher; the layout adds the site-wide facts
     * (sameAs, contact point) to the one top-level copy it emits.
     *
     * @return array<string, mixed>
     */
    public static function organization(): array
    {
        $logo = self::siteUrl().self::ORGANIZATION_LOGO;
        [$width, $height] = self::imageDimensions($logo) ?? [712, 140];

        return [
            '@type' => 'Organization',
            '@id' => self::siteUrl().'/#organization',
            'name' => 'Event Schedule',
            'url' => self::siteUrl(),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => $logo,
                'width' => $width,
                'height' => $height,
            ],
        ];
    }

    /**
     * organization() by reference, for a node that names it again (an article's author). The @id
     * is what merges it with the full node the layout emits; the type and name are for a consumer
     * that does not resolve references.
     *
     * @return array<string, string>
     */
    public static function organizationRef(): array
    {
        return [
            '@type' => 'Organization',
            '@id' => self::siteUrl().'/#organization',
            'name' => 'Event Schedule',
        ];
    }

    /**
     * The canonical URL of a marketing page: the site root plus the request path, whatever host
     * served it, and never a ?lang= variant (the marketing pages are English-only, so every
     * language variant canonicalizes to the clean URL and no hreflang alternates are emitted).
     *
     * The layout's <link rel="canonical"> and <x-seo.webpage>'s @id both come from here, so the
     * WebPage node always names the URL the page says it is.
     */
    public static function canonicalUrl(?string $path = null): string
    {
        $path = trim($path ?? request()->path(), '/');

        return $path === '' ? self::siteUrl() : self::siteUrl().'/'.$path;
    }

    /**
     * The product, as one SoftwareApplication node that the marketing layout emits once per page.
     *
     * Every marketing page used to carry a product node of its own - 92 of them, named "Event
     * Schedule for Bars and Pubs", "Event Schedule - Gift Cards" and so on, with their own offers
     * and feature lists - so to a crawler the site described 92 different applications. Now there
     * is one, with the @id {site}/#software, and each page describes ITSELF with a WebPage that is
     * `about` it (<x-seo.webpage>).
     *
     * Prices come from PlatformPricing and the currency from platform_currency(), the same pair
     * every visible price on the site renders, so the offers cannot disagree with /pricing on a
     * platform that set its own. Tiers follow docs/FEATURES.md.
     *
     * @return array<string, mixed>
     */
    public static function softwareApplication(): array
    {
        $site = self::siteUrl();
        $currency = platform_currency();

        $offer = function (string $name, float $monthly, string $description) use ($site, $currency): array {
            $price = $monthly > 0 ? number_format($monthly, 2, '.', '') : '0';

            $offer = [
                '@type' => 'Offer',
                'name' => $name,
                'price' => $price,
                'priceCurrency' => $currency,
                'description' => $description,
                'url' => $site.'/pricing',
                'availability' => 'https://schema.org/InStock',
            ];

            if ($monthly > 0) {
                $offer['priceSpecification'] = [
                    '@type' => 'UnitPriceSpecification',
                    'price' => $price,
                    'priceCurrency' => $currency,
                    'billingDuration' => 1,
                    'billingIncrement' => 1,
                    'unitCode' => 'MON',
                ];
            }

            return $offer;
        };

        return [
            '@context' => 'https://schema.org',
            '@type' => 'SoftwareApplication',
            '@id' => $site.'/#software',
            'name' => 'Event Schedule',
            'url' => $site,
            'description' => 'Event calendar and booking platform. One calendar that takes the bookings, collects free registrations, sells tickets with zero platform fees through Stripe or PayPal, emails the people who follow you and scans tickets at the door. Free plan, open source and selfhostable.',
            'featureList' => [
                'Event calendar pages with a custom link and a website embed',
                'Free registration and RSVP, unlimited on every plan',
                'Paid ticket sales with zero platform fees through Stripe or PayPal (Pro)',
                'QR ticket scanning at the door on every plan',
                'Full and partial refunds through Stripe and PayPal (Pro)',
                "Email sign-up for when an event's tickets go on sale",
                'Newsletters, and automatic new-event digests for confirmed subscribers',
                'Two-way calendar sync with Google Calendar, Microsoft 365 and CalDAV',
                'A live calendar feed guests can subscribe to',
                'Appointment booking, free with one appointment type',
                'Passes and gift cards (Pro)',
                'Reserved seating with a box office console (Enterprise)',
            ],
            'applicationCategory' => 'BusinessApplication',
            'operatingSystem' => ['Web', 'Android', 'iOS'],
            'screenshot' => $site.'/images/social/home.jpg',
            'publisher' => self::organizationRef(),
            'offers' => [
                $offer('Free', 0, 'Unlimited events and schedules, calendar sync, analytics, unlimited free event registration, and QR ticket scanning at the door, with no platform fee on any plan.'),
                $offer('Pro', PlatformPricing::proMonthly(),
                    'Paid ticket sales with every payment method and refunds, the live check-in dashboard, passes, gift cards, installment payments, API and webhooks. Also available at '.plan_price(PlatformPricing::proYearly()).'/year.'),
                $offer('Enterprise', PlatformPricing::enterpriseMonthly(),
                    'Allocated seating, custom domains, internal and unlisted events, multiple team members, and AI content generation. Also available at '.plan_price(PlatformPricing::enterpriseYearly()).'/year.'),
            ],
        ];
    }

    /**
     * The WebPage node <x-seo.webpage> emits: this page, what it is about (the one product), and
     * whose site it is part of - all by @id, so the page never restates the product.
     *
     * @param  array<int, string>  $mentions  competitors the page is about, as schema.org Brand
     * @return array<string, mixed>
     */
    public static function webPage(
        ?string $name,
        ?string $description = null,
        ?string $audience = null,
        ?string $keywords = null,
        array $mentions = [],
    ): array {
        $site = self::siteUrl();
        $url = self::canonicalUrl();

        $mentions = array_values(array_filter(array_map(
            fn ($brand) => is_string($brand) && trim($brand) !== '' ? ['@type' => 'Brand', 'name' => trim($brand)] : null,
            $mentions
        )));

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            '@id' => $url.'#webpage',
            'url' => $url,
            'name' => $name,
            'description' => $description,
            'inLanguage' => 'en',
            'isPartOf' => ['@id' => $site.'/#website'],
            'about' => ['@id' => $site.'/#software'],
            'publisher' => ['@id' => $site.'/#organization'],
            'audience' => $audience ? ['@type' => 'Audience', 'audienceType' => $audience] : null,
            'keywords' => $keywords,
            'mentions' => $mentions,
        ], fn ($value) => $value !== null && $value !== '' && $value !== []);
    }

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
