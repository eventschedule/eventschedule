<?php

namespace Tests\Unit;

use App\Utils\CalendarPrivacy;
use PHPUnit\Framework\TestCase;

/**
 * What the calendar imports and the operator's page call private. The entries here are shaped
 * like the ones a standing sync published in October 2026 (a Teams dial-in with its code in the
 * location, a Zoom link with its password, an email thread made into an entry), and like the
 * invitations Zoom, Teams, Meet and Webex write, in more than one language.
 */
class CalendarPrivacyTest extends TestCase
{
    public function test_a_location_with_a_dial_in_code_is_meeting_details_and_no_place(): void
    {
        $location = 'Microsoft Teams Meeting or Conference call #: (555)-010-0199 / Code: 583920174#; OPS-ROOM-2';

        $findings = CalendarPrivacy::findings(null, null, $location);

        $this->assertSame([CalendarPrivacy::ACCESS_CODES], CalendarPrivacy::flags($findings));
        $this->assertSame(CalendarPrivacy::HOLD_MEETING_DETAILS, CalendarPrivacy::holdReason($findings));
        $this->assertSame('location', $findings[CalendarPrivacy::ACCESS_CODES][0]['field']);
        $this->assertNull(CalendarPrivacy::venueFromLocation($location));
    }

    public function test_a_join_link_carrying_its_password_is_found(): void
    {
        $link = 'https://us02web.zoom.us/j/81234567890?pwd=Xq7rTzLmPa2wKdE9vB.1';

        $findings = CalendarPrivacy::findings(null, null, $link);

        $this->assertSame([CalendarPrivacy::LINK_PASSWORD], CalendarPrivacy::flags($findings));
        $this->assertSame($link, $findings[CalendarPrivacy::LINK_PASSWORD][0]['match']);
        $this->assertNull(CalendarPrivacy::venueFromLocation($link));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function invitations(): array
    {
        return [
            'zoom' => ["Join Zoom Meeting\nhttps://us02web.zoom.us/j/81234567890?pwd=abc\n\nMeeting ID: 812 3456 7890\nPasscode: 123456"],
            'zoom one tap, as Markdown escapes it' => ["One tap mobile\n+15550100188,,81234567890\\#,,,,\\*123456\\# US"],
            'teams' => ["Microsoft Teams meeting\n[Click here to join the meeting](https://teams.microsoft.com/l/meetup-join/19%3ameeting_abc)\nMeeting ID: 123 456 789 012\nPasscode: AbC12d"],
            'teams dial-in' => ["Or call in (audio only)\n+1 555-010-0199,,583920174\\# United States\nPhone Conference ID: 541 485 493\\#"],
            'meet' => ["Join with Google Meet: https://meet.google.com/abc-defg-hij\nOr dial: (US) +1 234-567-8901 PIN: 123 456 789#"],
            'webex' => ["Meeting number (access code): 2345 678 9012\nMeeting password: AbCdE12345"],
            'german teams' => ["Besprechungs-ID: 123 456 789 012\nKenncode: Ab12cD"],
            'french zoom' => ["ID de réunion : 812 3456 7890\nCode secret : 123456"],
            'spanish zoom' => ["ID de reunión: 812 3456 7890\nCódigo de acceso: 123456"],
            'hebrew zoom' => ['מזהה פגישה: 812 3456 7890'],
            'teams short link with its passcode' => ['Join: https://teams.microsoft.com/meet/27381234567?p=AbCdEfGh'],
        ];
    }

    /**
     * @dataProvider invitations
     */
    public function test_the_details_of_an_invitation_are_meeting_details(string $description): void
    {
        $findings = CalendarPrivacy::findings('Weekly sync', $description, null);

        $this->assertSame(CalendarPrivacy::HOLD_MEETING_DETAILS, CalendarPrivacy::holdReason($findings), json_encode($findings));
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function threads(): array
    {
        return [
            'reply' => ['Re: Budget', ''],
            'reply in capitals' => ['RE: Budget', ''],
            'forward' => ['Fwd: Budget', ''],
            'outlook forward' => ['FW: Budget', ''],
            'german reply' => ['AW: Budget', ''],
            'german forward' => ['WG: Budget', ''],
            'french forward' => ['TR: Budget', ''],
            'a reply to a reply' => ['RE: RE: Budget', ''],
            'tagged by a mail server' => ['[EXTERNAL] Clinic appointment', ''],
            'outlook headers' => ['Lunch', "**From:** Sam <sam@example.com>\n**Sent:** Monday, October 12\n**To:** Me\n**Subject:** lunch\n\nHi"],
            'quoted reply' => ['Lunch', "Sounds good.\n\nOn Mon, Oct 12, 2026 at 9:14 AM Sam Example <sam@example.com> wrote:\n> lunch?"],
            'original message' => ['Lunch', "Sure.\n\n-----Original Message-----\nlunch?"],
        ];
    }

    /**
     * @dataProvider threads
     */
    public function test_an_email_made_into_an_entry_is_an_email_thread(string $name, string $description): void
    {
        $findings = CalendarPrivacy::findings($name, $description, null);

        $this->assertSame(CalendarPrivacy::HOLD_EMAIL_THREAD, CalendarPrivacy::holdReason($findings), json_encode($findings));
    }

    public function test_the_incidents_thread_is_found_in_its_title(): void
    {
        $findings = CalendarPrivacy::findings(
            'Re: [EXTERNAL] CLINIC SCHEDULING / NEW PATIENTS',
            'Received, thank you. There is an opening on Thursday morning.',
            null
        );

        $this->assertSame([CalendarPrivacy::EMAIL_THREAD], CalendarPrivacy::flags($findings));
        $this->assertSame(['Re:', '[EXTERNAL]'], array_column($findings[CalendarPrivacy::EMAIL_THREAD], 'match'));
    }

    /**
     * @return array<string, array{0: ?string, 1: ?string, 2: ?string}>
     */
    public static function publicEntries(): array
    {
        return [
            'a festival called Re:Union' => ['Re:Union Festival', 'Come along', null],
            'a bar named after a provider' => ['Quiz night', null, 'Zoom Bar, 12 Main St'],
            'pin as a verb' => ['Gala', 'Pin your seat number to the board.', null],
            'a box office number' => ['Call us', 'Box office: +1 555-010-0199', null],
            'a health class' => ['Pediatric CPR class', 'Learn first aid for children', null],
            'the demo bowling league' => ['PIN PALS!', 'Bowling night with the PIN PALS! league', null],
            'a promo code' => ['Sale', 'Promo code: SAVE10 at checkout', null],
            'a dress code' => ['Gala', 'Dress code: black tie', null],
            'a reply in the middle of a title' => ['Ask me anything: Re: the future', null, null],
            'a church address' => ['Speaking on the first Sunday', null, 'Grace Hall, 12 Main Street, Springfield, IL 62701'],
            'an event that lists its own times' => ['Gala', "Date: Saturday\nFrom: 7pm\nTo: 11pm", null],
            'the same in Spanish' => ['Gala', "Fecha: sábado\nDe: 20h\nA: 23h", null],
        ];
    }

    /**
     * @dataProvider publicEntries
     */
    public function test_an_ordinary_public_entry_is_not_held(?string $name, ?string $description, ?string $location): void
    {
        $findings = CalendarPrivacy::findings($name, $description, $location);

        $this->assertSame([], CalendarPrivacy::flags($findings), json_encode($findings));
        $this->assertNull(CalendarPrivacy::holdReason($findings));
    }

    public function test_a_bare_webinar_link_is_noted_and_never_held(): void
    {
        $findings = CalendarPrivacy::findings('Webinar', 'Join at https://us02web.zoom.us/j/81234567890', null);

        $this->assertSame(['https://us02web.zoom.us/j/81234567890'], array_column($findings[CalendarPrivacy::MEETING_LINK], 'match'));
        $this->assertSame([], CalendarPrivacy::flags($findings));
        $this->assertNull(CalendarPrivacy::holdReason($findings));
    }

    public function test_a_location_keeps_the_place_and_loses_the_meeting(): void
    {
        $this->assertSame('ASO-HDQ-Main-5-550E-ConfRm (FAA)', CalendarPrivacy::venueFromLocation('Microsoft Teams Meeting; ASO-HDQ-Main-5-550E-ConfRm (FAA)'));
        $this->assertSame('12 Main St', CalendarPrivacy::venueFromLocation('12 Main St (https://maps.google.com/?q=12)'));
        $this->assertSame('Zoom Bar', CalendarPrivacy::venueFromLocation('Zoom Bar'));
        $this->assertSame('Café – Zürich', CalendarPrivacy::venueFromLocation('Café – Zürich'));
        $this->assertSame(
            'Grace Hall, 12 Main Street, Springfield, IL 62701',
            CalendarPrivacy::venueFromLocation('Grace Hall, 12 Main Street, Springfield, IL 62701')
        );

        foreach (['', 'Zoom', 'Microsoft Teams Meeting', 'Google Meet', 'Webex', 'https://meet.google.com/abc-defg-hij', 'Teams; https://teams.microsoft.com/l/meetup-join/abc'] as $noPlace) {
            $this->assertNull(CalendarPrivacy::venueFromLocation($noPlace), $noPlace);
        }
    }

    public function test_the_escapes_a_markdown_conversion_adds_are_read_through(): void
    {
        $this->assertSame('a#b _c [EXTERNAL]', CalendarPrivacy::normalise('a\\#b \\_c \\[EXTERNAL\\]'));
        $this->assertSame('one two', CalendarPrivacy::normalise("one\u{00A0}two\u{200B}"));

        $this->assertSame(
            CalendarPrivacy::textHash('Re: x', 'Code: 583920174\\#', null),
            CalendarPrivacy::textHash('Re: x', 'Code: 583920174#', null)
        );
        $this->assertNotSame(CalendarPrivacy::textHash('Re: x', 'one', null), CalendarPrivacy::textHash('Re: x', 'two', null));
    }

    public function test_a_highlight_escapes_what_it_marks(): void
    {
        $html = (string) CalendarPrivacy::highlight('<b>Passcode: <script>1</script></b>', 'Passcode: <script>1</script></b>');

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('<mark class="spam-word" dir="ltr">Passcode: &lt;script&gt;', $html);

        $long = str_repeat('word ', 60).'Meeting ID: 812 3456 7890'.str_repeat(' tail', 60);
        $cut = (string) CalendarPrivacy::highlight($long, 'Meeting ID: 812 3456 7890', 60);
        $this->assertStringContainsString('<mark class="spam-word" dir="ltr">Meeting ID: 812 3456 7890</mark>', $cut);
        $this->assertStringStartsWith('…', $cut);
    }

    public function test_a_link_is_found_with_or_without_a_scheme(): void
    {
        foreach (['https://example.com', 'Zoom: https://zoom.us/j/1?pwd=2', 'www.example.com', 'example.com/meeting'] as $link) {
            $this->assertTrue(CalendarPrivacy::hasLink($link), $link);
        }

        foreach (['12 Main St', 'P.F.C. Hall', 'Room 4.2'] as $place) {
            $this->assertFalse(CalendarPrivacy::hasLink($place), $place);
        }
    }
}
