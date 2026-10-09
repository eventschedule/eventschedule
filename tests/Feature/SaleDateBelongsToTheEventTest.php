<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The date a ticket or a registration is for is decided by the event, not by the request.
 *
 * Stock, the house limit and the RSVP limit are all kept per date. Checkout and RSVP used to take
 * the date as posted and never ask whether the event happens on it, so every unused date had a
 * full allotment: a sold-out ticket type could be bought again under another date, and the sale
 * did not show in the real night's counts. The attendee import and the box office already asked
 * (Event::isOccurrenceDate() and matchesDate(), the test the event page applies to the date in
 * its own address).
 */
class SaleDateBelongsToTheEventTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function ticketed(): array
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'creator_role_id' => $role->id]);
        $ticket = $this->createTicket($event, ['quantity' => 1, 'price' => 0]);

        return [$role, $event, $ticket];
    }

    private function buy($role, $event, $ticket, ?string $date)
    {
        return $this->post(route('event.checkout', ['subdomain' => $role->subdomain]), array_filter([
            'name' => 'A Fan', 'email' => 'fan'.random_int(1000, 9999).'@gmail.com', 'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => $date, 'tickets' => [UrlUtils::encodeId($ticket->id) => 1],
        ]));
    }

    public function test_a_ticket_is_sold_for_the_events_own_date(): void
    {
        [$role, $event, $ticket] = $this->ticketed();

        $this->buy($role, $event, $ticket, $event->saleEventDateFromStartsAt());

        $this->assertSame(1, Sale::where('event_id', $event->id)->where('event_date', $event->saleEventDateFromStartsAt())->count());
    }

    public function test_a_ticket_is_not_sold_for_a_date_the_event_does_not_happen_on(): void
    {
        [$role, $event, $ticket] = $this->ticketed();
        $other = Carbon::parse($event->saleEventDateFromStartsAt())->addDays(3)->format('Y-m-d');

        $this->buy($role, $event, $ticket, $event->saleEventDateFromStartsAt());
        $this->buy($role, $event, $ticket, $other);

        $this->assertSame(0, Sale::where('event_id', $event->id)->where('event_date', $other)->count(), 'no sale under a date that is not the event\'s');
        $this->assertSame(1, Sale::where('event_id', $event->id)->count(), 'and the one ticket was sold once');
    }

    public function test_a_registration_is_not_taken_for_a_date_the_event_does_not_happen_on(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->createEvent($role, ['rsvp_enabled' => true, 'rsvp_limit' => 1, 'creator_role_id' => $role->id]);
        $real = $event->saleEventDateFromStartsAt();
        $other = Carbon::parse($real)->addDays(3)->format('Y-m-d');
        $rsvp = fn (string $date) => $this->post(route('event.rsvp', ['subdomain' => $role->subdomain]), [
            'name' => 'A Fan', 'email' => 'fan'.random_int(1000, 9999).'@gmail.com', 'event_id' => UrlUtils::encodeId($event->id), 'event_date' => $date,
        ]);

        $rsvp($real);
        $rsvp($other);

        $this->assertSame(1, Sale::where('event_id', $event->id)->where('event_date', $real)->count(), 'control: the real date takes the one place');
        $this->assertSame(0, Sale::where('event_id', $event->id)->where('event_date', $other)->count());
    }
}
