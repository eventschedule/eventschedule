<?php

namespace Tests\Feature;

use App\Mail\AppointmentConfirmed;
use App\Mail\AppointmentReminder;
use App\Mail\AppointmentRescheduled;
use App\Models\Event;
use App\Models\Role;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * An online event's join link (events.event_url) is the private way in: the public surfaces show
 * where the event is - the link's domain, or "Online" - and never the link.
 *
 * The web form validates event_url only as a string, so owners store free-text join instructions
 * ("Zoom 884 1234 pw 998877"), and Event::getEventUrlDomain() handed all of that back as the
 * "domain": the meeting id and the passcode printed on the event page, in the calendar JSON, in
 * the noscript list, and in every iCal and RSS title. A ticket holder, who is entitled to the
 * whole link, still reads free text as text, and only a web link is linked for them.
 */
class OnlineJoinLinkTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** Free text, with a meeting id and a passcode nobody else prints. */
    private const FREE_TEXT = 'Zoom meeting 88412345 passcode Ringtail';

    private Role $talent;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.google.backend' => null]);
        $this->travelTo(Carbon::parse('2026-09-24 09:00:00', 'UTC'));

        $this->talent = $this->createRole($this->createOwner(), 'talent', ['name' => 'Stream Talent']);
    }

    private function onlineEvent(string $link, array $attrs = []): Event
    {
        return $this->createEvent($this->talent, array_merge([
            'name' => 'Jazz Night',
            'creator_role_id' => $this->talent->id,
            'event_url' => $link,
        ], $attrs));
    }

    /** The event page's location badge, #gp-event-location, up to the next gp-event- block. */
    private function locationBadge(string $html): string
    {
        $start = strpos($html, 'id="gp-event-location"');
        $this->assertNotFalse($start, 'the event page renders a location badge');
        $end = strpos($html, 'id="gp-event-', $start + 1);

        return substr($html, $start, $end === false ? 3000 : $end - $start);
    }

    private function assertNoFreeText(string $haystack, string $where): void
    {
        $this->assertStringNotContainsString('88412345', $haystack, "{$where}: the meeting id");
        $this->assertStringNotContainsString('Ringtail', $haystack, "{$where}: the passcode");
    }

    public function test_the_event_page_says_online_for_free_text_and_prints_none_of_it(): void
    {
        $event = $this->onlineEvent(self::FREE_TEXT);

        $html = $this->get($this->guestEventUrl($this->talent, $event))->assertOk()->getContent();

        $this->assertNoFreeText($html, 'the event page');
        $this->assertStringContainsString('>Online</span>', $this->locationBadge($html));
    }

    /** Values that parse_url() reads as a host, and that are no domain: "Online", not the digits. */
    public function test_dotted_digits_and_a_passcode_are_not_domains(): void
    {
        foreach (['884.1234.5678' => '1234.5678', 'pw.998877' => '998877', 'https://192.0.2.44/room' => '192.0.2.44'] as $link => $secret) {
            $event = $this->onlineEvent($link);

            $html = $this->get($this->guestEventUrl($this->talent, $event))->assertOk()->getContent();
            $badge = $this->locationBadge($html);

            $this->assertStringContainsString('>Online</span>', $badge, $link);
            $this->assertStringNotContainsString($secret, $badge, $link);
        }
    }

    /** A real meeting link still names its domain, and still no more of it. */
    public function test_a_meeting_link_shows_its_domain_only(): void
    {
        $event = $this->onlineEvent('https://zoom.us/j/5550001234?pwd=TopSecretJoinCode');

        $html = $this->get($this->guestEventUrl($this->talent, $event))->assertOk()->getContent();

        $this->assertStringContainsString('>zoom.us</span>', $this->locationBadge($html));
        $this->assertStringNotContainsString('5550001234', $html);
        $this->assertStringNotContainsString('TopSecretJoinCode', $html);
    }

    /**
     * The badge's text branch is also reached by a venue with no name in the page's language. That
     * is a physical place with an address, and must not be called "Online".
     */
    public function test_a_venue_known_only_by_its_address_is_never_called_online(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner, ['name' => 'Night Guide']);
        $venue = $this->createRole($owner, 'venue', ['name' => '', 'address1' => '12 Harbour Road', 'city' => 'Springfield']);
        $event = $this->createEvent($curator, ['name' => 'Harbour Show', 'creator_role_id' => $curator->id]);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        $badge = $this->locationBadge($this->get($this->guestEventUrl($curator, $event))->assertOk()->getContent());

        $this->assertStringContainsString('12 Harbour Road', $badge);
        $this->assertStringNotContainsString('Online', $badge);
    }

    public function test_the_calendar_json_names_it_online(): void
    {
        $event = $this->onlineEvent(self::FREE_TEXT);

        $payload = $this->getJson(route('role.calendar_events', [
            'subdomain' => $this->talent->subdomain,
            'month' => 10,
            'year' => 2026,
        ]))->assertOk()->json();

        $row = collect($payload['events'])->firstWhere('id', UrlUtils::encodeId($event->id));
        $this->assertNotNull($row, 'the event is in the month payload');
        $this->assertSame('Online', $row['venue_name']);
        $this->assertTrue($row['is_online']);
        $this->assertNoFreeText(json_encode($payload), 'the calendar JSON');
    }

    public function test_the_noscript_list_names_it_online(): void
    {
        $this->onlineEvent(self::FREE_TEXT);

        $html = $this->get('/'.$this->talent->subdomain)->assertOk()->getContent();

        $this->assertSame(1, preg_match('#<noscript v-pre>(.*?)</noscript>#s', $html, $m), 'the schedule page renders its noscript list');
        $this->assertStringContainsString('Jazz Night', $m[1]);
        $this->assertStringContainsString('Online', $m[1]);
        $this->assertNoFreeText($html, 'the schedule page');
    }

    /**
     * getTitle() is "{name} at {where}" in iCal SUMMARY, the RSS title, the single .ics and the
     * add-to-calendar links. With nothing to name it is just the name: it used to print the free
     * text after "at", or leave "Jazz Night at " dangling, and "at Online" reads as a place.
     */
    public function test_feed_titles_carry_no_free_text_and_no_dangling_at(): void
    {
        $free = $this->onlineEvent(self::FREE_TEXT);
        $this->onlineEvent('https://zoom.us/j/5550001234?pwd=TopSecretJoinCode', ['name' => 'Webinar']);

        $this->assertSame('Jazz Night', $free->getTitle());

        $ical = $this->get(route('feed.ical', ['subdomain' => $this->talent->subdomain]))->assertOk()->getContent();
        $this->assertStringContainsString("SUMMARY:Jazz Night\r\n", $ical);
        $this->assertStringContainsString("SUMMARY:Webinar at zoom.us\r\n", $ical);
        $this->assertNoFreeText($ical, 'the iCal feed');
        $this->assertStringNotContainsString('5550001234', $ical);

        $rss = $this->get(route('feed.rss', ['subdomain' => $this->talent->subdomain]))->assertOk()->getContent();
        $this->assertStringContainsString('<title>Jazz Night</title>', $rss);
        $this->assertStringContainsString('<title>Webinar at zoom.us</title>', $rss);
        $this->assertNoFreeText($rss, 'the RSS feed');

        $single = $this->get($free->getAppleCalendarUrl())->assertOk()->getContent();
        $this->assertStringContainsString("SUMMARY:Jazz Night\r\n", $single);
        $this->assertNoFreeText($single, 'the single .ics');
        $this->assertNoFreeText(urldecode($free->getGoogleCalendarUrl()), 'the Google Calendar link');
    }

    /**
     * The ticket holder is entitled to the whole join text, so it is shown - but a free-text value
     * is no link, and it used to be printed into href, where the browser read it as a path.
     */
    public function test_the_ticket_page_links_only_a_web_join_link(): void
    {
        $page = function (Event $event): string {
            $sale = $this->createSale($event, $this->talent);

            return $this->get(route('ticket.view', [
                'event_id' => UrlUtils::encodeId($event->id),
                'secret' => $sale->secret,
            ]))->assertOk()->getContent();
        };

        $html = $page($this->onlineEvent(self::FREE_TEXT));
        $this->assertStringContainsString('<span class="print:text-slate-600">'.self::FREE_TEXT.'</span>', $html, 'the ticket holder still reads the instructions');
        $this->assertStringNotContainsString('href="'.self::FREE_TEXT, $html);

        $html = $page($this->onlineEvent('javascript:alert(document.domain)', ['name' => 'Scripted']));
        $this->assertDoesNotMatchRegularExpression('/href\s*=\s*"\s*javascript:/i', $html);

        $html = $page($this->onlineEvent('https://zoom.us/j/5550001234?pwd=TopSecretJoinCode', ['name' => 'Linked']));
        $this->assertStringContainsString('href="https://zoom.us/j/5550001234?pwd=TopSecretJoinCode"', $html);
    }

    /** The booked guest's manage page, on the same terms as the ticket page. */
    public function test_the_appointment_page_links_only_a_web_join_link(): void
    {
        $page = function (Event $event): string {
            $sale = $this->createSale($event, $this->talent);

            return $this->get(route('appointments.manage', [
                'event_id' => UrlUtils::encodeId($event->id),
                'secret' => $sale->secret,
            ]))->assertOk()->getContent();
        };

        $booking = ['appointment_type_id' => $this->createAppointmentType($this->talent)->id, 'is_private' => true];

        // The add-to-calendar links carry the text as the entry's location, which is theirs to keep.
        $html = $page($this->onlineEvent(self::FREE_TEXT, $booking));
        $this->assertStringContainsString('<dd class="break-all">'.self::FREE_TEXT.'</dd>', $html);
        $this->assertStringNotContainsString('href="'.self::FREE_TEXT, $html);

        $html = $page($this->onlineEvent('https://zoom.us/j/5550001234', ['name' => 'Linked'] + $booking));
        $this->assertStringContainsString('href="https://zoom.us/j/5550001234"', $html);
    }

    /** The three booking emails that print the join link, on the same terms. */
    public function test_the_booking_emails_link_only_a_web_join_link(): void
    {
        $type = $this->createAppointmentType($this->talent);

        foreach ([self::FREE_TEXT => null, 'https://zoom.us/j/5550001234' => 'href="https://zoom.us/j/5550001234"'] as $link => $anchor) {
            $event = $this->onlineEvent($link, ['appointment_type_id' => $type->id, 'is_private' => true]);
            $sale = $this->createSale($event, $this->talent);

            foreach ([
                new AppointmentConfirmed($sale, $event, $this->talent, $type),
                new AppointmentReminder($sale, $event, $this->talent, $type),
                new AppointmentRescheduled($sale, $event, $this->talent, $type, now()->subDay()->format('Y-m-d H:i:s')),
            ] as $mail) {
                $html = $mail->render();

                $this->assertStringContainsString($link, $html, $mail::class.' still gives the guest the join text');
                $this->assertStringNotContainsString('href="'.self::FREE_TEXT, $html, $mail::class);

                if ($anchor) {
                    $this->assertStringContainsString($anchor, $html, $mail::class);
                }
            }
        }
    }
}
