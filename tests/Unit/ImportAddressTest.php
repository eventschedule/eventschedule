<?php

namespace Tests\Unit;

use App\Utils\ImportAddress;
use PHPUnit\Framework\TestCase;

/**
 * The questions asked of an address before and after it is fetched. The link import's own
 * tests hold what they mean to the person on the import page (LinkImportParseTest); these hold
 * the answers themselves, which a feed reads too.
 */
class ImportAddressTest extends TestCase
{
    public function test_an_address_is_spelled_the_way_it_will_be_fetched(): void
    {
        $this->assertSame('https://example.org/feed.ics', ImportAddress::normalise('  webcal://example.org/feed.ics '));
        $this->assertSame('https://example.org/feed.ics', ImportAddress::normalise('webcals://example.org/feed.ics'));
        $this->assertSame('http://example.org/a?b=c#d', ImportAddress::normalise('http://example.org/a?b=c#d'));

        foreach (['', 'example.org/feed.ics', 'ftp://example.org/feed.ics', 'https://localhost', 'javascript:alert(1)', 'https://example.org/'.str_repeat('a', 2048)] as $notOne) {
            $this->assertNull(ImportAddress::normalise($notOne), $notOne);
        }
    }

    public function test_a_site_behind_a_sign_in_wall_is_named(): void
    {
        $this->assertSame('Facebook', ImportAddress::signInWall('https://www.facebook.com/events/123'));
        $this->assertSame('Facebook', ImportAddress::signInWall('https://fb.me/e/abc'));
        $this->assertSame('Instagram', ImportAddress::signInWall('https://instagram.com/p/abc'));
        $this->assertNull(ImportAddress::signInWall('https://notfacebook.com/events'));
        $this->assertNull(ImportAddress::signInWall('https://example.org/facebook.com'));
    }

    public function test_a_calendars_share_link_stands_for_its_feed(): void
    {
        $feed = 'https://calendar.google.com/calendar/ical/band%40gmail.com/public/basic.ics';

        $this->assertSame([$feed, 'google'], ImportAddress::knownFeedFor('https://calendar.google.com/calendar/embed?src=band%40gmail.com&ctz=Europe/Vienna'));
        $this->assertSame([$feed, 'google'], ImportAddress::knownFeedFor('https://calendar.google.com/calendar/u/0?cid='.rtrim(strtr(base64_encode('band@gmail.com'), '+/', '-_'), '=')));
        // Already a feed, a private one included: left as it is.
        $private = 'https://calendar.google.com/calendar/ical/band%40gmail.com/private-0123abcd/basic.ics';
        $this->assertSame([$private, null], ImportAddress::knownFeedFor($private));

        $this->assertSame(
            ['https://outlook.office365.com/owa/calendar/abc/def/calendar.ics', 'outlook'],
            ImportAddress::knownFeedFor('https://outlook.office365.com/owa/calendar/abc/def/calendar.html')
        );
        $this->assertSame(['https://example.org/calendar.html', null], ImportAddress::knownFeedFor('https://example.org/calendar.html'));
    }

    public function test_what_came_back_is_judged_by_what_it_is(): void
    {
        $calendar = "\xEF\xBB\xBF\r\nBEGIN:VCALENDAR\r\nVERSION:2.0\r\nEND:VCALENDAR";

        $this->assertTrue(ImportAddress::isCalendar('Text/Calendar; charset=utf-8', ''));
        $this->assertTrue(ImportAddress::isCalendar('application/octet-stream', $calendar));
        $this->assertFalse(ImportAddress::isCalendar('text/html', '<html><body>BEGIN:VCALENDAR</body></html>'));

        $this->assertTrue(ImportAddress::isHtml('TEXT/HTML', ''));
        $this->assertTrue(ImportAddress::isHtml('', "\n<!DOCTYPE html><html>"));
        $this->assertFalse(ImportAddress::isHtml('application/rss+xml', '<?xml version="1.0"?><rss></rss>'));
        $this->assertFalse(ImportAddress::isHtml('text/calendar', $calendar));
    }
}
