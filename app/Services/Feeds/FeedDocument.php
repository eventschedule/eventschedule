<?php

namespace App\Services\Feeds;

use App\Utils\ImportAddress;

/**
 * A feed of posts, read into one plain shape whatever it was written as: RSS 2.0, Atom or
 * JSON Feed.
 *
 * Such a feed says what was published, not when anything happens: an item has a title, a link,
 * some text and the date it was POSTED. The readers built on this take each item's own page for
 * the event's date, or know the provider's format. This class only reads the list.
 *
 * It is somebody else's document:
 *
 *  - XML is parsed with no network access (LIBXML_NONET) and without substituting entities
 *    (never LIBXML_NOENT), and a document that declares a DOCTYPE is refused outright, before
 *    and after parsing. A DOCTYPE is where entity tricks live (a billion laughs, a local file
 *    read through an external entity) and no feed needs one.
 *  - Titles are text: markup in them is dropped. An item's body is handed on as the HTML it is,
 *    for the caller to turn into what it stores.
 *  - Links are made whole against the address the document was served from, and anything that
 *    is not a web link comes out empty.
 *  - It stops at MAX_ITEMS and says so.
 *
 * No library: RSS and Atom are small, and the app's rule is no new dependencies.
 */
final class FeedDocument
{
    public const RSS = 'rss';

    public const ATOM = 'atom';

    public const JSON = 'json';

    /** How many items of one document are read. */
    public const MAX_ITEMS = 2000;

    private const CONTENT_NS = 'http://purl.org/rss/1.0/modules/content/';

    private const MEDIA_NS = 'http://search.yahoo.com/mrss/';

    private const ATOM_NS = 'http://www.w3.org/2005/Atom';

    private const DC_NS = 'http://purl.org/dc/elements/1.1/';

    /**
     * @param  list<array{id: string, title: string, link: string, html: string, published: ?int, updated: ?int, image: string, categories: list<string>}>  $items
     * @param  ?string  $next  The next page's address, when the feed is in pages.
     * @param  bool  $truncated  It had more items than were read.
     */
    private function __construct(
        public readonly string $format,
        public readonly string $title,
        public readonly array $items,
        public readonly ?string $next,
        public readonly bool $truncated,
    ) {}

    /**
     * @param  string  $baseUrl  The address the document was finally served from.
     * @return ?self Null when the text is none of the three, or is not one this class will read.
     */
    public static function parse(string $body, string $baseUrl): ?self
    {
        $body = ltrim($body, "\xEF\xBB\xBF \t\r\n");

        return match ($body[0] ?? '') {
            '{' => self::json($body, $baseUrl),
            '<' => self::xml($body, $baseUrl),
            default => null,
        };
    }

    private static function json(string $body, string $baseUrl): ?self
    {
        $data = json_decode($body, true, 32);

        if (! is_array($data)
            || ! is_string($data['version'] ?? null)
            || ! str_starts_with($data['version'], 'https://jsonfeed.org/version/')
            || ! is_array($data['items'] ?? null)) {
            return null;
        }

        $items = [];
        $truncated = false;

        foreach ($data['items'] as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            if (count($items) >= self::MAX_ITEMS) {
                $truncated = true;

                break;
            }

            $link = ImportAddress::absolute(self::scalar($entry['url'] ?? ''), $baseUrl);
            $html = self::scalar($entry['content_html'] ?? '');
            if ($html === '') {
                // Text is text: it is escaped on its way to being read as HTML.
                $html = nl2br(htmlspecialchars(self::scalar($entry['content_text'] ?? '') ?: self::scalar($entry['summary'] ?? ''), ENT_QUOTES, 'UTF-8'));
            }

            $item = self::item(
                self::scalar($entry['id'] ?? '') ?: $link,
                self::scalar($entry['title'] ?? ''),
                $link,
                $html,
                self::scalar($entry['date_published'] ?? ''),
                self::scalar($entry['date_modified'] ?? ''),
                ImportAddress::absolute(self::scalar($entry['image'] ?? '') ?: self::scalar($entry['banner_image'] ?? ''), $baseUrl),
                array_map([self::class, 'scalar'], is_array($entry['tags'] ?? null) ? $entry['tags'] : []),
            );

            if ($item) {
                $items[] = $item;
            }
        }

        return new self(
            self::JSON,
            self::plain(self::scalar($data['title'] ?? '')),
            $items,
            ImportAddress::absolute(self::scalar($data['next_url'] ?? ''), $baseUrl) ?: null,
            $truncated,
        );
    }

    private static function xml(string $body, string $baseUrl): ?self
    {
        if (self::declaresDoctype($body)) {
            return null;
        }

        $before = libxml_use_internal_errors(true);

        try {
            $dom = new \DOMDocument;
            $loaded = $dom->loadXML($body, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_COMPACT);
        } catch (\Throwable $e) {
            $loaded = false;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($before);
        }

        // The check above reads bytes, and a document in another encoding (UTF-16) hides its
        // DOCTYPE from it. What the parser found is the answer that counts.
        if (! $loaded || $dom->doctype !== null || ! $dom->documentElement) {
            return null;
        }

        $root = $dom->documentElement;

        return match (strtolower((string) $root->localName)) {
            'rss' => self::rss($root, $baseUrl),
            'feed' => self::atom($root, $baseUrl),
            default => null,
        };
    }

    /**
     * Whether the document declares a DOCTYPE, which is only legal before its first element.
     * Read only there: the same letters inside an item (a page quoted in its body) are text.
     */
    private static function declaresDoctype(string $body): bool
    {
        $offset = 0;
        $length = strlen($body);

        while ($offset < $length) {
            $offset += strspn($body, " \t\r\n", $offset);

            foreach (['<?' => '?>', '<!--' => '-->'] as $open => $close) {
                if (substr_compare($body, $open, $offset, strlen($open)) === 0) {
                    $end = strpos($body, $close, $offset);
                    // Never closed: not a document this reads.
                    if ($end === false) {
                        return true;
                    }
                    $offset = $end + strlen($close);

                    continue 2;
                }
            }

            return strncasecmp(substr($body, $offset, 9), '<!DOCTYPE', 9) === 0;
        }

        return false;
    }

    private static function rss(\DOMElement $root, string $baseUrl): ?self
    {
        $channel = self::first($root, 'channel');

        if (! $channel) {
            return null;
        }

        $items = [];
        $truncated = false;
        $title = '';
        $next = null;

        foreach ($channel->childNodes as $node) {
            if (! $node instanceof \DOMElement) {
                continue;
            }

            if ($node->localName === 'title' && $node->namespaceURI === null) {
                $title = self::plain($node->textContent);
            } elseif ($node->localName === 'link' && $node->namespaceURI === self::ATOM_NS && strtolower($node->getAttribute('rel')) === 'next') {
                $next = ImportAddress::absolute($node->getAttribute('href'), $baseUrl) ?: null;
            } elseif ($node->localName === 'item' && $node->namespaceURI === null) {
                if (count($items) >= self::MAX_ITEMS) {
                    $truncated = true;

                    break;
                }

                if ($item = self::rssItem($node, $baseUrl)) {
                    $items[] = $item;
                }
            }
        }

        return new self(self::RSS, $title, $items, $next, $truncated);
    }

    private static function rssItem(\DOMElement $entry, string $baseUrl): ?array
    {
        $guid = $title = $link = $description = $content = $published = $image = '';
        $categories = [];

        foreach ($entry->childNodes as $node) {
            if (! $node instanceof \DOMElement) {
                continue;
            }

            $text = trim($node->textContent);

            match (true) {
                $node->namespaceURI === null && $node->localName === 'guid' => $guid = $text,
                $node->namespaceURI === null && $node->localName === 'title' => $title = $text,
                $node->namespaceURI === null && $node->localName === 'link' => $link = ImportAddress::absolute($text, $baseUrl),
                $node->namespaceURI === null && $node->localName === 'description' => $description = $text,
                $node->namespaceURI === self::CONTENT_NS && $node->localName === 'encoded' => $content = $text,
                $node->namespaceURI === null && $node->localName === 'pubDate' => $published = $text,
                $node->namespaceURI === self::DC_NS && $node->localName === 'date' => $published = $published ?: $text,
                $node->namespaceURI === null && $node->localName === 'category' => $categories[] = $text,
                $node->namespaceURI === null && $node->localName === 'enclosure' => $image = $image ?: self::pictureAt($node, 'url', $baseUrl),
                $node->namespaceURI === self::MEDIA_NS && in_array($node->localName, ['content', 'thumbnail'], true) => $image = $image ?: self::pictureAt($node, 'url', $baseUrl, $node->localName === 'thumbnail'),
                default => null,
            };
        }

        return self::item($guid ?: $link, $title, $link, $content ?: $description, $published, '', $image, $categories);
    }

    private static function atom(\DOMElement $root, string $baseUrl): ?self
    {
        $items = [];
        $truncated = false;
        $title = '';
        $next = null;

        foreach ($root->childNodes as $node) {
            if (! $node instanceof \DOMElement || ! self::isAtom($node)) {
                continue;
            }

            if ($node->localName === 'title') {
                $title = self::plain($node->textContent);
            } elseif ($node->localName === 'link' && strtolower($node->getAttribute('rel')) === 'next') {
                $next = ImportAddress::absolute($node->getAttribute('href'), $baseUrl) ?: null;
            } elseif ($node->localName === 'entry') {
                if (count($items) >= self::MAX_ITEMS) {
                    $truncated = true;

                    break;
                }

                if ($item = self::atomEntry($node, $baseUrl)) {
                    $items[] = $item;
                }
            }
        }

        return new self(self::ATOM, $title, $items, $next, $truncated);
    }

    private static function atomEntry(\DOMElement $entry, string $baseUrl): ?array
    {
        $id = $title = $link = $summary = $content = $published = $updated = $image = '';
        $categories = [];

        foreach ($entry->childNodes as $node) {
            if (! $node instanceof \DOMElement) {
                continue;
            }

            // Other vocabularies reuse Atom's words (media:content, dc:title). Only a picture
            // is taken from them.
            if ($node->namespaceURI === self::MEDIA_NS) {
                if (in_array($node->localName, ['content', 'thumbnail'], true) && $image === '') {
                    $image = self::pictureAt($node, 'url', $baseUrl, $node->localName === 'thumbnail');
                }

                continue;
            }

            if (! self::isAtom($node)) {
                continue;
            }

            switch ($node->localName) {
                case 'id':
                    $id = trim($node->textContent);
                    break;
                case 'title':
                    $title = trim($node->textContent);
                    break;
                case 'link':
                    $rel = strtolower($node->getAttribute('rel')) ?: 'alternate';
                    if ($rel === 'alternate' && $link === '') {
                        $link = ImportAddress::absolute($node->getAttribute('href'), $baseUrl);
                    } elseif ($rel === 'enclosure' && $image === '') {
                        $image = self::pictureAt($node, 'href', $baseUrl);
                    }
                    break;
                case 'summary':
                    $summary = self::atomText($node);
                    break;
                case 'content':
                    $content = self::atomText($node);
                    break;
                case 'published':
                    $published = trim($node->textContent);
                    break;
                case 'updated':
                    $updated = trim($node->textContent);
                    break;
                case 'category':
                    $categories[] = $node->getAttribute('term') ?: $node->getAttribute('label');
                    break;
            }
        }

        return self::item($id ?: $link, $title, $link, $content ?: $summary, $published ?: $updated, $updated, $image, $categories);
    }

    /** An Atom text construct as HTML: text is escaped, xhtml is the markup inside its wrapper. */
    private static function atomText(\DOMElement $node): string
    {
        $type = strtolower($node->getAttribute('type')) ?: 'text';

        if ($type === 'xhtml') {
            $html = '';
            foreach ($node->childNodes as $child) {
                $html .= $node->ownerDocument->saveXML($child);
            }

            return trim($html);
        }

        $text = trim($node->textContent);

        return $type === 'html' ? $text : nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8'));
    }

    /** The address in an attribute, when what it points at is (or is given as) a picture. */
    private static function pictureAt(\DOMElement $node, string $attribute, string $baseUrl, bool $always = false): string
    {
        $type = strtolower($node->getAttribute('type'));
        $medium = strtolower($node->getAttribute('medium'));

        if (! $always && ! str_starts_with($type, 'image/') && $medium !== 'image') {
            return '';
        }

        return ImportAddress::absolute($node->getAttribute($attribute), $baseUrl);
    }

    /**
     * One item in the shape every format comes out as, or null when it has nothing to be known
     * by: no id and no link.
     *
     * @param  list<string>  $categories
     */
    private static function item(string $id, string $title, string $link, string $html, string $published, string $updated, string $image, array $categories): ?array
    {
        $id = trim($id);

        if ($id === '') {
            return null;
        }

        return [
            'id' => $id,
            'title' => self::plain($title),
            'link' => $link,
            'html' => trim($html),
            'published' => self::moment($published),
            'updated' => self::moment($updated),
            'image' => $image,
            'categories' => array_values(array_unique(array_filter(array_map([self::class, 'plain'], $categories), fn ($name) => $name !== ''))),
        ];
    }

    /** Atom's own element. A feed written without the namespace is read as if it had it. */
    private static function isAtom(\DOMElement $node): bool
    {
        return $node->namespaceURI === null || $node->namespaceURI === self::ATOM_NS;
    }

    private static function first(\DOMElement $parent, string $name): ?\DOMElement
    {
        foreach ($parent->childNodes as $node) {
            if ($node instanceof \DOMElement && $node->localName === $name) {
                return $node;
            }
        }

        return null;
    }

    private static function scalar(mixed $value): string
    {
        return is_scalar($value) ? trim((string) $value) : '';
    }

    /** Text with no markup in it, on one line. A title that says <b> means the letters, not bold. */
    private static function plain(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');
    }

    private static function moment(string $value): ?int
    {
        if ($value === '') {
            return null;
        }

        $at = strtotime($value);

        return $at === false ? null : $at;
    }
}
