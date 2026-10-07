<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Things the event page offered on a laptop and not on a phone.
 *
 * The laptop buttons live in #gp-event-cta, which is `hidden sm:inline-flex`: a phone never draws
 * it, and gets the bar at the bottom of the screen instead. Two things were only ever in the
 * laptop row. The carpool link, so a phone had no way to the ride board at all. And Add to
 * calendar for an event whose ticket sales had ended or not started, where the phone bar showed a
 * line of grey text and nothing to press. A third case was worse than nothing: the phone's
 * calendar button and the sheet it opens were gated by two different conditions, so an event with
 * a registration link whose sign-up had closed got a button that opened nothing.
 */
class GuestEventPhoneActionsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function eventOn(Role $role, array $attrs = []): Event
    {
        return $this->createEvent($role, $attrs + [
            'starts_at' => now()->addDays(7)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'creator_role_id' => $role->id,
        ]);
    }

    private function dom(Event $event, Role $role): \DOMXPath
    {
        $html = $this->get($event->fresh()->getGuestUrl($role->subdomain))->assertOk()->getContent();
        $doc = new \DOMDocument;
        @$doc->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);

        return new \DOMXPath($doc);
    }

    /** Class names on the element and every ancestor, nearest first. */
    private function classesAbove(\DOMNode $node): array
    {
        $classes = [];
        for ($n = $node; $n instanceof \DOMElement; $n = $n->parentNode) {
            $classes[] = $n->getAttribute('class');
        }

        return $classes;
    }

    public function test_the_carpool_link_is_not_inside_anything_a_phone_hides(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['carpool_enabled' => true]);
        $event = $this->eventOn($role);

        $xpath = $this->dom($event, $role);
        $links = $xpath->query('//*[@id="gp-event-carpool"]');

        $this->assertSame(1, $links->length, 'one carpool link, so an owner\'s CSS rule for #gp-event-carpool reaches it');

        foreach ($this->classesAbove($links->item(0)) as $class) {
            $this->assertDoesNotMatchRegularExpression('/(^|\s)hidden(\s|$)/', $class, 'an ancestor of the carpool link is hidden below the sm breakpoint: '.$class);
            $this->assertStringNotContainsString('gp-cta-wide', $class, 'the carpool link sits in the laptop-only part of the row');
        }
    }

    public function test_the_laptop_row_stays_out_of_a_phones_way_when_there_is_no_carpool(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->eventOn($role);

        $xpath = $this->dom($event, $role);
        $row = $xpath->query('//*[@id="gp-event-cta"]')->item(0);

        $this->assertNotNull($row);
        $this->assertMatchesRegularExpression('/(^|\s)hidden(\s|$)/', $row->getAttribute('class'), 'an empty row would add a gap to the details card on a phone');
    }

    /** @return array<string, array{0: array, 1: ?array, 2: bool}> event attributes, ticket attributes, calendar expected */
    public static function states(): array
    {
        return [
            'nothing to sell' => [[], null, true],
            'ticket sales ended' => [['tickets_enabled' => true], ['price' => 10, 'sales_end_at' => '-1 day'], true],
            'ticket sales not started' => [['tickets_enabled' => true], ['price' => 10, 'sales_start_at' => '+2 days'], true],
            'not started, rows shown' => [['tickets_enabled' => true, 'show_unavailable_tickets' => true], ['price' => 10, 'sales_start_at' => '+2 days'], true],
            'registration link, sign-up closed' => [['registration_url' => 'https://tickets.example.org/e/42', 'tickets_enabled' => true], ['price' => 10, 'sales_end_at' => '-1 day'], true],
            'tickets on sale' => [['tickets_enabled' => true], ['price' => 10], false],
            'sign-up open' => [['rsvp_enabled' => true], null, false],
            'registration link' => [['registration_url' => 'https://tickets.example.org/e/42'], null, false],
        ];
    }

    /** @dataProvider states */
    public function test_the_phone_calendar_button_and_its_sheet_come_together(array $eventAttrs, ?array $ticketAttrs, bool $expected): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->eventOn($role, $eventAttrs);

        if ($ticketAttrs) {
            foreach (['sales_end_at', 'sales_start_at'] as $key) {
                if (isset($ticketAttrs[$key])) {
                    $ticketAttrs[$key] = now()->modify($ticketAttrs[$key]);
                }
            }
            $this->createTicket($event, $ticketAttrs);
        }

        $xpath = $this->dom($event, $role);
        $button = $xpath->query('//*[@id="mobile-calendar-cta"]')->length;
        $sheet = $xpath->query('//*[@id="calendar-mobile-sheet"]')->length;

        $this->assertSame($button, $sheet, 'the button and the sheet it opens are on the page together or not at all');
        $this->assertSame($expected ? 1 : 0, $button, $expected ? 'a phone can add this event to a calendar' : 'the bar keeps its one main action');

        if ($button) {
            $bar = $xpath->query('//*[@id="gp-mobile-cta"]//*[@id="mobile-calendar-cta"]')->length;
            $this->assertSame(1, $bar, 'the button is in the phone bar');
        }
    }
}
