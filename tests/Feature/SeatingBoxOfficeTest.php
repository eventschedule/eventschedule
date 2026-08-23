<?php

namespace Tests\Feature;

use App\Exceptions\BusinessException;
use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SeatingLevel;
use App\Models\SeatingPlan;
use App\Models\SeatingSeat;
use App\Models\SeatingSection;
use App\Repos\EventRepo;
use App\Services\BoxOfficeSeatingService;
use App\Services\SeatHoldService;
use App\Services\SeatingMapService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Phase 7 box office: holding seats back, and moving or releasing ONE seat out of a booking.
 *
 * Sale.status is per sale, so releasing a single seat cannot go through the status machine - a
 * four-seat order refunding one seat is still a paid order.
 */
class SeatingBoxOfficeTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function svc(): BoxOfficeSeatingService
    {
        return app(BoxOfficeSeatingService::class);
    }

    private function maps(): SeatingMapService
    {
        return app(SeatingMapService::class);
    }

    private function makePlan(Role $role): SeatingPlan
    {
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Main House']);
        $level = SeatingLevel::create(['seating_plan_id' => $plan->id, 'name' => 'Ground']);
        $section = SeatingSection::create([
            'seating_plan_id' => $plan->id, 'seating_level_id' => $level->id,
            'name' => 'Stalls', 'band' => 'Stalls', 'kind' => 'seated',
        ]);
        for ($n = 1; $n <= 10; $n++) {
            SeatingSeat::create([
                'seating_plan_id' => $plan->id, 'seating_section_id' => $section->id,
                'row_label' => 'A', 'row_position' => 1, 'seat_label' => (string) $n, 'position' => $n,
            ]);
        }

        return $plan->fresh();
    }

    private function seatedEvent(Role $role, SeatingPlan $plan): Event
    {
        $request = Request::create('/', 'POST', [
            'name' => 'Seated Show',
            'starts_at' => now()->addMonths(6)->setTime(19, 30)->format('Y-m-d H:i:s'),
            'tickets_enabled' => 1, 'payment_method' => 'cash', 'ticket_currency_code' => 'USD',
            'creator_role_id' => $role->id, 'seating_plan_id' => $plan->id,
            'tickets' => [['type' => 'Stalls', 'price' => 40, 'quantity' => 999, 'seating_band' => 'Stalls']],
        ]);
        $request->setUserResolver(fn () => $role->user);
        $event = app(EventRepo::class)->saveEvent($role, $request)->fresh();
        $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);

        return $event->fresh();
    }

    private function seats($map)
    {
        return SeatingSeat::where('event_seating_map_id', $map->id)->orderBy('position')->get();
    }

    /** Buy seats through the real guest path so the sale and its lines are genuine. */
    private function buy(Role $role, Event $event, $seats): Sale
    {
        $this->postJson(route('seating.hold', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'date' => $event->saleEventDateFromStartsAt(),
            'seat_ids' => $seats->pluck('id')->all(),
        ])->assertOk();

        $this->post(route('event.checkout', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => $event->saleEventDateFromStartsAt(),
            'name' => 'Buyer', 'email' => 'buyer@gmail.com',
            'tickets' => [UrlUtils::encodeId($event->tickets->first()->id) => $seats->count()],
        ])->assertRedirect();

        return Sale::latest('id')->firstOrFail();
    }

    public function test_staff_can_hold_seats_back_with_an_internal_note(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seats = $this->seats($map)->take(2);

        $this->assertSame(2, $this->svc()->block($map, $seats->pluck('id')->all(), 'house', 'Reserved for the producer'));

        $seat = $seats->first()->fresh();
        $this->assertSame('held', $seat->status);
        $this->assertSame('house', $seat->hold_kind);
        // No expiry: a staff hold never lapses on its own.
        $this->assertNull($seat->hold_expires_at);
        $this->assertTrue($seat->isBlocked());
    }

    public function test_a_staff_hold_reads_as_simply_taken_to_a_guest(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seat = $this->seats($map)->first();

        $this->svc()->block($map, [$seat->id], 'production', 'Lighting desk');

        $body = $this->getJson(route('seating.state', [
            'subdomain' => $role->subdomain,
            'event_id' => UrlUtils::encodeId($event->id),
            'date' => $event->saleEventDateFromStartsAt(),
        ]))->assertOk()->getContent();

        $this->assertStringNotContainsString('Lighting desk', $body);
        $this->assertStringNotContainsString('production', $body);
    }

    public function test_a_sold_seat_cannot_be_held_back(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seats = $this->seats($map)->take(2);
        $this->buy($role, $event, $seats);

        // Blocking it would hide a real attendee from the door list while their ticket stayed valid.
        $this->expectException(BusinessException::class);
        $this->svc()->block($map, [$seats->first()->id], 'house');
    }

    public function test_unblock_frees_a_staff_hold_but_not_a_guests_cart(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seats = $this->seats($map);

        $this->svc()->block($map, [$seats[0]->id], 'house');
        app(SeatHoldService::class)->acquire($map, [$seats[4]->id], 'guest-token');

        $freed = $this->svc()->unblock($map, [$seats[0]->id, $seats[4]->id]);

        $this->assertSame(1, $freed);
        $this->assertSame('available', $seats[0]->fresh()->status);
        $this->assertSame('held', $seats[4]->fresh()->status, "a guest's cart is not the box office's to cancel");
    }

    public function test_releasing_one_seat_leaves_the_rest_of_the_booking_intact(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seats = $this->seats($map)->take(3);
        $sale = $this->buy($role, $event, $seats);

        $line = $sale->saleTickets()->firstOrFail();
        $this->assertSame(3, (int) $line->quantity);

        $this->svc()->releaseSeat($map, $seats->first()->id);

        $freed = $seats->first()->fresh();
        $this->assertSame('available', $freed->status);
        $this->assertNull($freed->sale_id);

        // The order's status is UNTOUCHED - releasing one seat of several is not a sale-level
        // event, which is the whole reason this cannot go through the status machine.
        $this->assertSame($sale->status, $sale->fresh()->status);
        $this->assertSame(2, (int) $line->fresh()->quantity);
        $this->assertSame(2, SeatingSeat::where('sale_id', $sale->id)->where('status', 'sold')->count());
    }

    public function test_releasing_a_seat_keeps_the_sold_counter_in_step(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seats = $this->seats($map)->take(2);
        $sale = $this->buy($role, $event, $seats);

        $ticket = $event->tickets->first();
        $date = $event->saleEventDateFromStartsAt();
        $this->assertSame(2, $ticket->fresh()->soldCountFor($date));

        $this->svc()->releaseSeat($map, $seats->first()->id);

        // SaleTicket only takes stock on CREATE, so an update would leave this high - and
        // Sale::booted reads it when the rest of the order is later cancelled.
        $this->assertSame(1, $ticket->fresh()->soldCountFor($date));
    }

    public function test_exchanging_a_seat_moves_the_booking_with_it(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seats = $this->seats($map);
        $sale = $this->buy($role, $event, $seats->take(2));

        $from = $seats[0]->fresh();
        $to = $seats[8];
        // Captured before the move - $from is cleared by the exchange.
        $originalLineId = $from->sale_ticket_id;
        $this->assertNotNull($originalLineId, 'fixture sanity: the seat was actually sold');

        $this->svc()->exchange($map, $from->id, $to->id);

        $this->assertSame('available', $from->fresh()->status);
        $this->assertNull($from->fresh()->sale_id);

        $moved = $to->fresh();
        $this->assertSame('sold', $moved->status);
        $this->assertSame($sale->id, $moved->sale_id);
        $this->assertSame($originalLineId, $moved->sale_ticket_id, 'the same line still pays for it');

        // The booking still holds two seats - an exchange moves one, it does not add one.
        $this->assertSame(2, SeatingSeat::where('sale_id', $sale->id)->where('status', 'sold')->count());
    }

    public function test_a_seat_cannot_be_exchanged_onto_a_taken_one(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seats = $this->seats($map);
        $this->buy($role, $event, $seats->take(2));

        $this->svc()->block($map, [$seats[8]->id], 'house');

        $this->expectException(BusinessException::class);
        $this->svc()->exchange($map, $seats[0]->id, $seats[8]->id);
    }

    // ------------------------------------------------ removed sections

    public function test_a_seat_in_a_removed_section_cannot_be_held(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seat = $this->seats($map)->first();

        // Sections are soft-deleted so a sold seat keeps its history. Every READ already filtered
        // on that; the WRITERS did not, so a guest whose picker was open could still take one.
        $map->sections()->update(['is_deleted' => true]);

        $this->expectException(BusinessException::class);
        app(SeatHoldService::class)->acquire($map, [$seat->id], 'tok-a');
    }

    public function test_the_poll_does_not_report_seats_from_a_removed_section(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);

        // Move every seat past the client's cursor, then remove the section.
        SeatingSeat::where('event_seating_map_id', $map->id)->update(['state_version' => 99]);
        $map->sections()->update(['is_deleted' => true]);

        $body = $this->getJson(route('seating.state', [
            'subdomain' => $role->subdomain,
            'event_id' => UrlUtils::encodeId($event->id),
            'date' => $event->saleEventDateFromStartsAt(),
            'since' => 1,
        ]))->assertOk()->json();

        $this->assertSame([], $body['seats'], 'the full payload filters these out, so the poll must too');
    }

    public function test_the_designer_will_not_delete_a_seat_a_guest_is_holding(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $plan = $this->makePlan($role);
        $event = $this->seatedEvent($role, $plan);
        $map = $this->maps()->materialize($event);

        app(SeatHoldService::class)->acquire($map, [$this->seats($map)->first()->id], 'guest-token');

        // The per-date editor posts the same structure the designer does. Deleting a seat somebody
        // is mid-purchase on leaves them to fail the balance check at the payment step.
        $structure = app(\App\Services\SeatingStructureService::class)->toArray($map->fresh());
        $structure['levels'][0]['sections'][0]['seats'] = [];

        $this->expectException(BusinessException::class);
        app(\App\Services\SeatingStructureService::class)->save($map->fresh(), $structure);
    }

    // ------------------------------------------------ the console and the per-date editor

    /**
     * The two payloads must never converge.
     *
     * Staff SHOULD see the hold note and the booker; a guest must not. Asserting both in one test
     * means a change that widens the guest payload cannot pass by only updating its own test.
     */
    public function test_the_staff_payload_shows_what_the_guest_payload_hides(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seats = $this->seats($map);

        $this->svc()->block($map, [$seats[0]->id], 'house', 'Producer hold');
        $this->buy($role, $event, $seats->slice(4, 1));

        $args = ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)];

        $staff = $this->actingAs($owner)->getJson(route('box_office.state', $args))
            ->assertOk()->getContent();

        $this->assertStringContainsString('Producer hold', $staff);
        $this->assertStringContainsString('house', $staff);
        $this->assertStringContainsString('buyer@gmail.com', $staff);

        $guest = $this->getJson(route('seating.state', [
            'subdomain' => $role->subdomain,
            'event_id' => UrlUtils::encodeId($event->id),
            'date' => $event->saleEventDateFromStartsAt(),
        ]))->assertOk()->getContent();

        $this->assertStringNotContainsString('Producer hold', $guest);
        $this->assertStringNotContainsString('buyer@gmail.com', $guest);
        $this->assertStringNotContainsString('hold_note', $guest);
    }

    public function test_staff_can_sell_the_selected_seats_to_a_phone_caller(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seats = $this->seats($map)->take(3);
        $args = ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)];

        $this->actingAs($owner)->postJson(route('box_office.book', $args), [
            'seat_ids' => $seats->pluck('id')->all(),
            'name' => 'Phone Caller', 'email' => 'caller@gmail.com', 'phone' => '555 0100',
            'status' => 'paid',
        ])->assertOk();

        $sale = Sale::latest('id')->firstOrFail();
        $this->assertSame('Phone Caller', $sale->name);
        $this->assertSame('box_office', $sale->payment_method);
        $this->assertSame('paid', $sale->status);
        // Three seats at the list price of 40.
        $this->assertSame(120.0, (float) $sale->payment_amount);
        $this->assertSame($event->saleEventDateFromStartsAt(), $sale->event_date);
        // Under the schedule that sold it - a curated event belongs to several.
        $this->assertSame($role->subdomain, $sale->subdomain);

        $line = $sale->saleTickets()->first();
        $this->assertSame(3, (int) $line->quantity);

        foreach ($seats as $seat) {
            $fresh = $seat->fresh();
            $this->assertSame('sold', $fresh->status);
            $this->assertSame($sale->id, (int) $fresh->sale_id);
            $this->assertSame($line->id, (int) $fresh->sale_ticket_id);
        }

        // The seats it took are the seats it names, everywhere else.
        $this->assertCount(3, $line->seatLabels());
    }

    public function test_a_phone_booking_takes_quantity_stock_exactly_once(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seats = $this->seats($map)->take(2);
        $args = ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)];

        $ticket = $event->tickets->first();
        $date = $event->saleEventDateFromStartsAt();
        $this->assertSame(0, $ticket->fresh()->soldCountFor($date));

        $this->actingAs($owner)->postJson(route('box_office.book', $args), [
            'seat_ids' => $seats->pluck('id')->all(),
            'name' => 'Counter Sale', 'status' => 'paid',
        ])->assertOk();

        $this->assertSame(2, $ticket->fresh()->soldCountFor($date),
            'SaleTicket::created already counts; do not count again');
    }

    public function test_a_comped_booking_is_zero_and_a_blank_amount_is_the_list_price(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seats = $this->seats($map);
        $args = ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)];

        // An explicit zero comps the seats; it must not read as "no amount given".
        $this->actingAs($owner)->postJson(route('box_office.book', $args), [
            'seat_ids' => [$seats[0]->id], 'name' => 'Comped Guest', 'status' => 'paid', 'amount' => 0,
        ])->assertOk();
        $this->assertSame(0.0, (float) Sale::latest('id')->firstOrFail()->payment_amount);

        // Blank falls back to the list price.
        $this->actingAs($owner)->postJson(route('box_office.book', $args), [
            'seat_ids' => [$seats[1]->id], 'name' => 'Paying Guest', 'status' => 'unpaid',
        ])->assertOk();
        $sale = Sale::latest('id')->firstOrFail();
        $this->assertSame(40.0, (float) $sale->payment_amount);
        $this->assertSame('unpaid', $sale->status);
    }

    public function test_a_phone_booking_refuses_a_seat_someone_else_just_took(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seats = $this->seats($map);
        $args = ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)];

        $existing = $this->buy($role, $event, $seats->slice(0, 1));

        $this->actingAs($owner)->postJson(route('box_office.book', $args), [
            'seat_ids' => [$seats[0]->id, $seats[1]->id],
            'name' => 'Too Late', 'status' => 'paid',
        ])->assertStatus(422)->assertJsonStructure(['error', 'state']);

        // Nothing partial: the second seat is untouched and no sale was written.
        $this->assertSame('available', $seats[1]->fresh()->status);
        $this->assertSame($existing->id, Sale::latest('id')->firstOrFail()->id);
    }

    public function test_a_phone_booking_may_sell_out_of_a_staff_hold(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seats = $this->seats($map);
        $args = ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)];

        // Holding a seat back for a caller and then selling it to them is the whole point.
        $this->svc()->block($map, [$seats[0]->id], 'box_office', 'Holding for Dana');

        $this->actingAs($owner)->postJson(route('box_office.book', $args), [
            'seat_ids' => [$seats[0]->id], 'name' => 'Dana', 'status' => 'paid',
        ])->assertOk();

        $seat = $seats[0]->fresh();
        $this->assertSame('sold', $seat->status);
        // The internal note went with the hold; it must not ride along on a live booking.
        $this->assertNull($seat->hold_note);
        $this->assertNull($seat->hold_kind);
    }

    public function test_a_phone_booking_will_not_take_a_cart_hold_from_a_shopper(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seats = $this->seats($map);
        $args = ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)];

        $this->postJson(route('seating.hold', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'date' => $event->saleEventDateFromStartsAt(),
            'seat_ids' => [$seats[0]->id],
        ])->assertOk();

        $this->actingAs($owner)->postJson(route('box_office.book', $args), [
            'seat_ids' => [$seats[0]->id], 'name' => 'Queue Jumper', 'status' => 'paid',
        ])->assertStatus(422);

        $this->assertSame('held', $seats[0]->fresh()->status);
    }

    public function test_a_cancelled_event_cannot_be_sold_seats(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seats = $this->seats($map);
        $args = ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)];

        $event->forceFill(['is_cancelled' => true, 'cancelled_at' => now()])->save();

        // The audience has been told it is off and refunded. A new seat sold against it is not a
        // judgement call staff are making, it is always a mistake.
        $this->actingAs($owner)->postJson(route('box_office.book', $args), [
            'seat_ids' => [$seats[0]->id], 'name' => 'Too Late', 'status' => 'paid',
        ])->assertStatus(422);

        $this->assertSame('available', $seats[0]->fresh()->status);
    }

    public function test_a_past_date_can_still_be_sold_at_the_desk(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $args = ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)];

        // Doors have opened. Recording a walk-up after the fact is ordinary box office work, and
        // this console is the surface where it happens - so this must NOT be blocked.
        // Move the event BEFORE materializing: the map is keyed on the occurrence date, so
        // snapshotting first and moving after leaves the console resolving a different map.
        $event->forceFill(['starts_at' => now()->subHours(3)->format('Y-m-d H:i:s')])->save();
        $event = $event->fresh();

        $map = $this->maps()->materialize($event);
        $seats = $this->seats($map);
        $this->assertNotEmpty($seats, 'fixture sanity: the past occurrence still snapshots');

        $this->actingAs($owner)->postJson(route('box_office.book', $args), [
            'seat_ids' => [$seats[0]->id], 'name' => 'Walk Up', 'status' => 'paid',
        ])->assertOk();

        $this->assertSame('sold', $seats[0]->fresh()->status);
    }

    public function test_a_viewer_cannot_book_seats(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seats = $this->seats($map);
        $args = ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)];

        $viewer = $this->createOwner();

        $this->actingAs($viewer)->postJson(route('box_office.book', $args), [
            'seat_ids' => [$seats[0]->id], 'name' => 'Nope', 'status' => 'paid',
        ])->assertForbidden();

        $this->assertSame('available', $seats[0]->fresh()->status);
    }

    public function test_the_console_mutations_round_trip_over_http(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seats = $this->seats($map);
        $args = ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)];

        $this->actingAs($owner)->postJson(route('box_office.block', $args), [
            'seat_ids' => [$seats[0]->id], 'kind' => 'production', 'note' => 'Desk',
        ])->assertOk()->assertJsonStructure(['version', 'levels', 'counts']);
        $this->assertSame('production', $seats[0]->fresh()->hold_kind);

        $this->actingAs($owner)->postJson(route('box_office.unblock', $args), [
            'seat_ids' => [$seats[0]->id],
        ])->assertOk();
        $this->assertSame('available', $seats[0]->fresh()->status);

        $sale = $this->buy($role, $event, $seats->slice(2, 1));
        $sold = $seats[2];

        $this->actingAs($owner)->postJson(route('box_office.exchange', $args), [
            'from_seat_id' => $sold->id, 'to_seat_id' => $seats[7]->id,
        ])->assertOk();
        $this->assertSame($sale->id, $seats[7]->fresh()->sale_id);

        $this->actingAs($owner)->postJson(route('box_office.release_seat', $args), [
            'seat_id' => $seats[7]->id,
        ])->assertOk();
        $this->assertSame('available', $seats[7]->fresh()->status);
    }

    public function test_a_refused_mutation_comes_back_with_the_current_map(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seats = $this->seats($map);
        $this->buy($role, $event, $seats->take(1));

        $this->actingAs($owner)->postJson(route('box_office.block', [
            'subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id),
        ]), ['seat_ids' => [$seats[0]->id], 'kind' => 'house'])
            ->assertStatus(422)
            // The console repaints from this rather than guessing, so a refusal must carry truth.
            ->assertJsonStructure(['error', 'state' => ['levels', 'counts']]);
    }

    public function test_the_console_is_editor_and_enterprise_only(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $args = ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)];

        $this->actingAs($this->createOwner())->get(route('box_office.show', $args))->assertStatus(403);

        config(['app.hosted' => true]);
        $role->plan_type = 'free';
        $role->plan_expires = now()->subYear()->format('Y-m-d');
        $role->save();

        $this->actingAs($owner)->get(route('box_office.show', $args))->assertStatus(403);
    }

    public function test_editing_one_date_leaves_the_template_alone(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $plan = $this->makePlan($role);
        $event = $this->seatedEvent($role, $plan);
        $map = $this->maps()->materialize($event);
        $args = ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)];

        $structure = app(\App\Services\SeatingStructureService::class)->toArray($map);
        $structure['levels'][0]['sections'][0]['name'] = 'Stalls (this date only)';

        $this->actingAs($owner)->putJson(route('seating.occurrence_save', $args), $structure)->assertOk();

        $this->assertSame('Stalls (this date only)', $map->fresh()->sections()->value('name'));
        $this->assertSame('Stalls', $plan->fresh()->sections()->value('name'), 'the template is untouched');
    }

    public function test_reverting_a_date_drops_its_own_layout(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $args = ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)];

        $this->actingAs($owner)->post(route('seating.occurrence_revert', $args))->assertRedirect();

        $this->assertNull(\App\Models\EventSeatingMap::find($map->id));
    }

    public function test_a_date_with_a_sold_seat_cannot_be_reverted(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $this->buy($role, $event, $this->seats($map)->take(1));

        $this->actingAs($owner)->post(route('seating.occurrence_revert', [
            'subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id),
        ]))->assertRedirect();

        $this->assertNotNull(\App\Models\EventSeatingMap::find($map->id), 'a booked date keeps its map');
    }

    /**
     * The summary is the first thing staff read, and nothing was checking it.
     *
     * It shipped reading all zeros: the counts were accumulated through a by-reference argument
     * inside nested fn() arrow functions, which capture by VALUE, so the writes never reached the
     * outer array. Only visible by looking at the payload.
     */
    public function test_the_summary_counts_are_real(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seats = $this->seats($map);

        $this->svc()->block($map, [$seats[0]->id, $seats[1]->id], 'house');
        $this->buy($role, $event, $seats->slice(5, 3));

        $counts = $this->actingAs($owner)->getJson(route('box_office.state', [
            'subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id),
        ]))->assertOk()->json('counts');

        $this->assertSame(10, $counts['total']);
        $this->assertSame(3, $counts['sold']);
        $this->assertSame(2, $counts['blocked']);
        $this->assertSame(5, $counts['available']);
    }

    public function test_the_console_page_renders(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));

        $html = $this->actingAs($owner)->get(route('box_office.show', [
            'subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id),
        ]))->assertOk()->getContent();

        $this->assertStringContainsString('id="seating-box-office"', $html);
        $this->assertStringContainsString('data-props', $html);
        $this->assertStringNotContainsString('messages.seating_lookup', $html, 'strings resolved');
    }

    // ------------------------------------------------ Phase 8: the printed sheet

    public function test_the_report_lists_every_seat_and_who_has_it(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seats = $this->seats($map);

        $this->svc()->block($map, [$seats[0]->id], 'house', 'Producer hold');
        $this->buy($role, $event, $seats->slice(4, 2));

        $html = $this->actingAs($owner)->get(route('box_office.report', [
            'subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id),
        ]))->assertOk()->getContent();

        // Front of house needs the name against the seat, and the internal note for the block.
        $this->assertStringContainsString('Buyer', $html);
        $this->assertStringContainsString('Producer hold', $html);
        $this->assertStringContainsString($event->translatedName(), $html);

        // The date a person can read, not the raw Y-m-d the column stores. This used to assert the
        // stored string, which passed precisely because the sheet printed "2026-01-01" at a venue.
        $this->assertStringContainsString(
            \Carbon\Carbon::parse($map->event_date)->translatedFormat('l, F j, Y'),
            $html
        );
        $this->assertStringNotContainsString('>'.$map->event_date.'<', $html);

        // A row per seat, plus the header.
        $this->assertSame(11, substr_count($html, '<tr'));
    }

    public function test_the_report_csv_downloads_with_a_row_per_seat(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $this->buy($role, $event, $this->seats($map)->take(1));

        $response = $this->actingAs($owner)->get(route('box_office.report_csv', [
            'subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id),
        ]))->assertOk();

        $csv = $response->streamedContent();
        $lines = array_filter(explode("\n", trim($csv)));

        $this->assertCount(11, $lines, 'a header plus one line per seat');
        $this->assertStringContainsString('buyer@gmail.com', $csv);
    }

    public function test_the_report_encodes_status_by_shape_not_colour_alone(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seats = $this->seats($map);

        $this->svc()->block($map, [$seats[0]->id], 'house');
        $this->buy($role, $event, $seats->slice(4, 1));

        $html = $this->actingAs($owner)->get(route('box_office.report', [
            'subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id),
        ]))->assertOk()->getContent();

        // This sheet is usually printed in black and white, where four shades of grey are four
        // identical circles. Sold carries a cross, blocked a hatch.
        $this->assertStringContainsString('&#10005;', $html);
        $this->assertStringContainsString('url(#rptBlocked)', $html);
    }

    public function test_deleting_an_amount_mismatch_sale_frees_its_seats(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seats = $this->seats($map)->take(2);
        $sale = $this->buy($role, $event, $seats);

        // This status never transitions on delete, so Sale::booted's release never fired and the
        // seats stayed sold against a row that had vanished from every list.
        $sale->forceFill(['status' => 'amount_mismatch'])->saveQuietly();

        $this->actingAs($role->user)->post(route('sales.action', [
            'subdomain' => $role->subdomain, 'sale_id' => UrlUtils::encodeId($sale->id),
        ]), ['action' => 'delete']);

        $this->assertTrue((bool) $sale->fresh()->is_deleted, 'fixture sanity: the delete happened');
        $this->assertSame(0, SeatingSeat::where('sale_id', $sale->id)->count());
        $this->assertSame('available', $seats->first()->fresh()->status);
    }

    /**
     * A two-level plan draws one map per level, not both on top of each other.
     *
     * Every level's first section is seeded at the same origin, because the designer only ever
     * shows one level at a time. The report used to flatten them onto a single canvas, so the
     * designer's own theatre preset - Stalls plus Balcony - printed an 80-seat balcony directly on
     * top of a 216-seat stalls, and the demo data only looked right because a section had been
     * hand-nudged sideways.
     */
    public function test_each_level_gets_its_own_map_on_the_report(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $plan = $this->makePlan($role);

        // A balcony, deliberately at the SAME coordinates as the stalls - which is what the
        // designer produces, since a level is its own space.
        $stalls = SeatingSection::where('seating_plan_id', $plan->id)->firstOrFail();
        $balcony = SeatingLevel::create(['seating_plan_id' => $plan->id, 'name' => 'Balcony', 'position' => 1]);
        $upstairs = SeatingSection::create([
            'seating_plan_id' => $plan->id, 'seating_level_id' => $balcony->id,
            'name' => 'Balcony', 'band' => 'Stalls', 'kind' => 'seated',
            'x' => $stalls->x, 'y' => $stalls->y,
        ]);
        for ($n = 1; $n <= 4; $n++) {
            SeatingSeat::create([
                'seating_plan_id' => $plan->id, 'seating_section_id' => $upstairs->id,
                'row_label' => 'A', 'row_position' => 1, 'seat_label' => (string) $n, 'position' => $n,
            ]);
        }

        $event = $this->seatedEvent($role, $plan->fresh());
        $this->maps()->materialize($event);

        $html = $this->actingAs($owner)->get(route('box_office.report', [
            'subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id),
        ]))->assertOk()->getContent();

        // One <svg> per level, each headed by its name, rather than one canvas holding both.
        $this->assertSame(2, substr_count($html, '<svg viewBox='), 'the levels were flattened onto one map');
        $this->assertStringContainsString('Ground', $html);
        $this->assertStringContainsString('Balcony', $html);

        // ...and every seat still reaches the table below, on both levels.
        $this->assertSame(15, substr_count($html, '<tr'));
    }
}
