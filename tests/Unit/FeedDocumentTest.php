<?php

namespace Tests\Unit;

use App\Services\Feeds\FeedDocument;
use PHPUnit\Framework\TestCase;

/**
 * A feed of posts read into one shape whatever it was written as, and read as somebody else's
 * document: nothing in it can reach the network or a file, markup in a title is text, and a link
 * that is not a web link is no link.
 */
class FeedDocumentTest extends TestCase
{
    private const BASE = 'https://news.example.org/feeds/events.xml';

    private function rss(string $items, string $channel = ''): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:media="http://search.yahoo.com/mrss/" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/">'
            .'<channel><title>Town &amp; Country</title><link>https://news.example.org</link>'.$channel.$items.'</channel></rss>';
    }

    public function test_an_rss_feed_is_read_item_by_item(): void
    {
        $document = FeedDocument::parse($this->rss(
            '<item><guid isPermaLink="false">post-4686933</guid><title>Spring &lt;b&gt;concert&lt;/b&gt;</title>'
                .'<link>/posts/4686933</link><description>Short text</description>'
                .'<content:encoded><![CDATA[<p>Doors at <b>seven</b>.</p>]]></content:encoded>'
                .'<pubDate>Sat, 09 May 2026 17:30:00 +0000</pubDate><category>Music</category><category>Music</category><category> Outdoors </category>'
                .'<enclosure url="/img/poster.jpg" type="image/jpeg" length="1"/></item>'
            .'<item><title>No guid</title><link>https://news.example.org/posts/2</link><dc:date>2026-05-10T10:00:00Z</dc:date>'
                .'<enclosure url="https://news.example.org/audio.mp3" type="audio/mpeg"/><media:thumbnail url="https://cdn.example.org/t.png"/></item>'
            .'<item><title>Nothing to know it by</title></item>',
            '<atom:link rel="next" href="?page=2"/><atom:link rel="self" href="https://news.example.org/feeds/events.xml"/>'
        ), self::BASE);

        $this->assertSame(FeedDocument::RSS, $document->format);
        $this->assertSame('Town & Country', $document->title);
        $this->assertSame('https://news.example.org/feeds/?page=2', $document->next);
        $this->assertFalse($document->truncated);
        $this->assertCount(2, $document->items);

        $this->assertSame([
            'id' => 'post-4686933',
            'title' => 'Spring concert',
            'link' => 'https://news.example.org/posts/4686933',
            'html' => '<p>Doors at <b>seven</b>.</p>',
            'published' => gmmktime(17, 30, 0, 5, 9, 2026),
            'updated' => null,
            'image' => 'https://news.example.org/img/poster.jpg',
            'categories' => ['Music', 'Outdoors'],
        ], $document->items[0]);

        // No guid: the link is what it is known by. An audio enclosure is not a picture.
        $this->assertSame('https://news.example.org/posts/2', $document->items[1]['id']);
        $this->assertSame(gmmktime(10, 0, 0, 5, 10, 2026), $document->items[1]['published']);
        $this->assertSame('https://cdn.example.org/t.png', $document->items[1]['image']);
        $this->assertSame('', $document->items[1]['html']);
    }

    public function test_an_atom_feed_is_read_entry_by_entry(): void
    {
        $document = FeedDocument::parse(
            '<?xml version="1.0" encoding="utf-8"?><feed xmlns="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/">'
            .'<title type="html">What&apos;s &lt;i&gt;on&lt;/i&gt;</title><link rel="next" href="https://news.example.org/atom?page=2"/>'
            .'<entry><id>urn:uuid:1225c695</id><title>Open  mic</title><dc:title xmlns:dc="http://purl.org/dc/elements/1.1/">Not the title</dc:title><link href="/open-mic"/><link rel="enclosure" type="image/png" href="/mic.png"/>'
                .'<link rel="enclosure" type="audio/mpeg" href="/mic.mp3"/>'
                .'<summary>Short</summary><content type="xhtml"><div xmlns="http://www.w3.org/1999/xhtml"><p>Bring a <em>song</em>.</p></div></content>'
                .'<published>2026-05-09T17:30:00Z</published><updated>2026-05-10T08:00:00+02:00</updated><category term="Music"/>'
                .'<media:content url="https://cdn.example.org/not-the-text.jpg" medium="image"/></entry>'
            .'<entry><id>tag:example.org,2026:2</id><title type="text">Plain &lt;text&gt;</title><link rel="alternate" href="https://news.example.org/2"/>'
                .'<summary type="text">Line one &amp; two &lt;b&gt;</summary><updated>2026-05-11T00:00:00Z</updated>'
                .'<media:thumbnail url="//cdn.example.org/t2.png"/></entry>'
            .'</feed>',
            self::BASE
        );

        $this->assertSame(FeedDocument::ATOM, $document->format);
        $this->assertSame("What's on", $document->title);
        $this->assertSame('https://news.example.org/atom?page=2', $document->next);

        [$first, $second] = $document->items;
        $this->assertSame('urn:uuid:1225c695', $first['id']);
        $this->assertSame('Open mic', $first['title']);
        $this->assertSame('https://news.example.org/open-mic', $first['link']);
        $this->assertSame('https://news.example.org/mic.png', $first['image']);
        $this->assertStringContainsString('<p>Bring a <em>song</em>.</p>', $first['html']);
        $this->assertSame(gmmktime(17, 30, 0, 5, 9, 2026), $first['published']);
        $this->assertSame(gmmktime(6, 0, 0, 5, 10, 2026), $first['updated']);
        $this->assertSame(['Music'], $first['categories']);

        // A title loses anything shaped like markup whatever type it claims: feeds escape real
        // markup into titles far more often than they mean an angle bracket.
        $this->assertSame('Plain', $second['title']);
        // A body that is text reaches the caller escaped, so it cannot turn into markup there.
        $this->assertSame('Line one &amp; two &lt;b&gt;', $second['html']);
        // Another vocabulary's element gives the picture, and nothing else.
        $this->assertSame('https://cdn.example.org/t2.png', $second['image']);
        // No published date: the last change is the nearest thing to one.
        $this->assertSame(gmmktime(0, 0, 0, 5, 11, 2026), $second['published']);
    }

    public function test_a_json_feed_is_read_item_by_item(): void
    {
        $document = FeedDocument::parse(json_encode([
            'version' => 'https://jsonfeed.org/version/1.1',
            'title' => 'Town <em>hall</em>',
            'next_url' => '/feed.json?page=2',
            'items' => [
                ['id' => 4686933, 'url' => '/posts/4686933', 'title' => 'By number', 'content_html' => '<p>Hello</p>',
                    'date_published' => '2026-05-09T17:30:00Z', 'date_modified' => '2026-05-09T18:00:00Z', 'image' => '/a.jpg', 'tags' => ['Music', 7, ['no']]],
                ['url' => 'https://news.example.org/posts/2', 'title' => 'By link', 'content_text' => "Line <one>\nLine two", 'banner_image' => 'https://cdn.example.org/b.png'],
                ['title' => 'Nothing to know it by'],
                'not an item',
            ],
        ]), self::BASE);

        $this->assertSame(FeedDocument::JSON, $document->format);
        $this->assertSame('Town hall', $document->title);
        $this->assertSame('https://news.example.org/feed.json?page=2', $document->next);
        $this->assertCount(2, $document->items);

        $this->assertSame('4686933', $document->items[0]['id']);
        $this->assertSame('https://news.example.org/posts/4686933', $document->items[0]['link']);
        $this->assertSame('https://news.example.org/a.jpg', $document->items[0]['image']);
        $this->assertSame(['Music', '7'], $document->items[0]['categories']);
        $this->assertSame(gmmktime(18, 0, 0, 5, 9, 2026), $document->items[0]['updated']);

        $this->assertSame('https://news.example.org/posts/2', $document->items[1]['id']);
        $this->assertSame("Line &lt;one&gt;<br />\nLine two", $document->items[1]['html']);
        $this->assertSame('https://cdn.example.org/b.png', $document->items[1]['image']);
    }

    /**
     * A DOCTYPE is where entity tricks live, and no feed needs one. Refused before parsing when
     * it can be seen, and after when it could not (a document in another encoding).
     */
    public function test_a_document_that_declares_a_doctype_is_not_read(): void
    {
        $item = '<item><guid>1</guid><title>&lol;</title><link>https://news.example.org/1</link></item>';
        $body = '<rss version="2.0"><channel><title>T</title>'.$item.'</channel></rss>';

        $laughs = '<?xml version="1.0"?><!-- a comment first --><!DOCTYPE rss [<!ENTITY lol "ha"><!ENTITY lol2 "&lol;&lol;&lol;&lol;&lol;&lol;&lol;&lol;">]>'.$body;
        $this->assertNull(FeedDocument::parse($laughs, self::BASE));

        $file = '<?xml version="1.0"?><!DOCTYPE rss [<!ENTITY lol SYSTEM "file:///etc/passwd">]>'.$body;
        $this->assertNull(FeedDocument::parse($file, self::BASE));

        // In another encoding the bytes do not spell DOCTYPE, so the first check sees none. The
        // parser reads it all the same, and what the parser found is what counts. (The same
        // document without its DOCTYPE is read, which is what makes this a test of that.)
        $utf16 = fn (string $doctype) => mb_convert_encoding('<?xml version="1.0" encoding="UTF-16"?>'.$doctype.str_replace('&lol;', 'ha', $body), 'UTF-16LE', 'UTF-8');
        $this->assertSame('ha', FeedDocument::parse($utf16(''), self::BASE)->items[0]['title']);
        $this->assertNull(FeedDocument::parse($utf16('<!DOCTYPE rss [<!ENTITY lol "ha">]>'), self::BASE));

        // The same letters inside an item are text, and the feed is read.
        $quoted = FeedDocument::parse($this->rss('<item><guid>1</guid><title>A page</title><description><![CDATA[<!DOCTYPE html><p>Quoted</p>]]></description></item>'), self::BASE);
        $this->assertSame('<!DOCTYPE html><p>Quoted</p>', $quoted->items[0]['html']);
    }

    public function test_a_link_that_is_not_a_web_link_is_no_link(): void
    {
        $document = FeedDocument::parse($this->rss(
            '<item><guid>1</guid><title>Script</title><link>javascript:alert(1)</link><enclosure url="data:image/png;base64,AAAA" type="image/png"/></item>'
            .'<item><guid>2</guid><title>Mail</title><link>mailto:someone@example.org</link></item>'
        ), self::BASE);

        $this->assertSame(['', ''], array_column($document->items, 'link'));
        $this->assertSame('', $document->items[0]['image']);
        $this->assertSame(['1', '2'], array_column($document->items, 'id'));
    }

    public function test_what_is_not_a_feed_is_not_read_as_an_empty_one(): void
    {
        foreach ([
            '',
            'BEGIN:VCALENDAR',
            '<html><body><h1>Events</h1></body></html>',
            '<rss version="2.0"></rss>',
            '<rss version="2.0"><channel><item><title>Never closed',
            '{"items": []}',
            '{"version": "https://example.org/feed", "items": []}',
            '{"version": "https://jsonfeed.org/version/1.1", "items": "none"}',
            '[1, 2, 3]',
        ] as $body) {
            $this->assertNull(FeedDocument::parse($body, self::BASE), $body);
        }

        // An empty feed is a feed.
        $this->assertSame([], FeedDocument::parse($this->rss(''), self::BASE)->items);
        $this->assertSame([], FeedDocument::parse("\xEF\xBB\xBF\n".'{"version": "https://jsonfeed.org/version/1", "items": []}', self::BASE)->items);
    }

    public function test_it_stops_at_its_limit_and_says_so(): void
    {
        $items = '';
        for ($i = 1; $i <= FeedDocument::MAX_ITEMS + 5; $i++) {
            $items .= '<item><guid>'.$i.'</guid><title>'.$i.'</title></item>';
        }

        $document = FeedDocument::parse($this->rss($items), self::BASE);

        $this->assertCount(FeedDocument::MAX_ITEMS, $document->items);
        $this->assertTrue($document->truncated);
        $this->assertSame((string) FeedDocument::MAX_ITEMS, $document->items[FeedDocument::MAX_ITEMS - 1]['id']);
    }
}
