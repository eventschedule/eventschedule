<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\User;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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

    // ---- POST /api/sales ----------------------------------------------------------------------

    private function apiKey(User $user): array
    {
        $raw = 'testapikey_'.Str::random(24);
        $user->api_key = substr(hash('sha256', $raw), 0, 8);
        $user->api_key_hash = Hash::make($raw);
        $user->save();

        return ['X-API-Key' => $raw];
    }

    private function buyOverTheApi(array $key, $event, $ticket, string $date)
    {
        return $this->postJson('/api/sales', [
            'event_id' => UrlUtils::encodeId($event->id), 'name' => 'API Buyer', 'email' => 'buyer'.random_int(1000, 9999).'@gmail.com',
            'event_date' => $date, 'tickets' => [UrlUtils::encodeId($ticket->id) => 1],
        ], $key);
    }

    public function test_the_api_does_not_sell_a_ticket_for_a_date_the_event_does_not_happen_on(): void
    {
        config(['app.hosted' => true]);
        [$role, $event, $ticket] = $this->ticketed();
        $key = $this->apiKey(User::find($role->user_id));
        $real = $event->saleEventDateFromStartsAt();
        $other = Carbon::parse($real)->addDays(3)->format('Y-m-d');

        $this->buyOverTheApi($key, $event, $ticket, $real)->assertSuccessful();
        $this->buyOverTheApi($key, $event, $ticket, $other)
            ->assertStatus(422)
            ->assertJsonPath('error', 'The event does not take place on this event_date');

        $this->assertSame(0, Sale::where('event_id', $event->id)->where('event_date', $other)->count(), 'the sold-out ticket is not sold again under another date');
        $this->assertSame(1, Sale::where('event_id', $event->id)->count());
    }

    public function test_the_api_does_not_sell_a_recurring_event_on_a_day_that_is_not_an_occurrence(): void
    {
        config(['app.hosted' => true]);
        $role = $this->createRole($this->createOwner(), 'venue');
        $starts = Carbon::now()->addDays(7)->setTime(16, 0);
        $days = str_repeat('0', 7);
        $days[$starts->copy()->setTimezone($role->timezone)->dayOfWeek] = '1';
        $event = $this->createRecurringEvent($role, ['tickets_enabled' => true, 'creator_role_id' => $role->id, 'starts_at' => $starts->format('Y-m-d H:i:s'), 'days_of_week' => $days]);
        $ticket = $this->createTicket($event, ['quantity' => 5, 'price' => 0]);
        $key = $this->apiKey(User::find($role->user_id));
        $real = $starts->copy()->setTimezone($role->timezone)->addWeek()->format('Y-m-d');
        $notOne = Carbon::parse($real)->addDay()->format('Y-m-d');

        $this->buyOverTheApi($key, $event, $ticket, $real)->assertSuccessful();
        $this->buyOverTheApi($key, $event, $ticket, $notOne)->assertStatus(422);

        $this->assertSame(0, Sale::where('event_id', $event->id)->where('event_date', $notOne)->count());
    }
}
