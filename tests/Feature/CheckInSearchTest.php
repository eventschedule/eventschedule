<?php

namespace Tests\Feature;

use App\Models\SaleTicket;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * "Find someone at the door" on /checkin promises "C14, a name, or an email". It read the seat map
 * and nothing else, so at an event with no seating plan, which is most events, every name and
 * every address answered "Nobody matched that on this date" about people who had paid. The seated
 * half is held by SeatingArrivalTest; this is the half that was missing.
 */
class CheckInSearchTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** @return array{0: \App\Models\User, 1: \App\Models\Event, 2: \App\Models\Ticket, 3: string} */
    private function door(): array
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'Harbor Hall']);
        $event = $this->createEvent($role, ['creator_role_id' => $role->id, 'tickets_enabled' => true, 'ticket_currency_code' => 'USD']);
        $ticket = $this->createTicket($event, ['type' => 'General', 'price' => 25]);

        return [$owner, $event, $ticket, route('checkin.search', ['event_id' => UrlUtils::encodeId($event->id)])];
    }

    public function test_a_name_or_an_address_finds_somebody_at_an_event_without_seats(): void
    {
        [$owner, $event, $ticket, $url] = $this->door();
        $role = $event->creatorRole;
        $date = $event->saleEventDateFromStartsAt();

        $ned = $this->createSale($event, $role, ['name' => 'Ned Flanders', 'email' => 'ned@leftorium.com', 'payment_amount' => 50], $ticket, 2);
        $this->createSale($event, $role, ['name' => 'Maude Flanders', 'email' => 'maude@gmail.com', 'payment_amount' => 25], $ticket, 1);
        $this->createSale($event, $role, ['name' => 'Moe Szyslak', 'email' => 'moe@gmail.com', 'payment_amount' => 25], $ticket, 1);

        // One of Ned's two tickets has been scanned.
        $line = SaleTicket::where('sale_id', $ned->id)->firstOrFail();
        $line->forceFill(['seats' => json_encode([1 => now()->getTimestamp(), 2 => null])])->save();

        $byName = $this->actingAs($owner)->getJson($url.'?q=flanders&date='.$date)->assertOk()->json('results');
        $this->assertSame(['Maude Flanders', 'Ned Flanders'], array_column($byName, 'name'));

        $nedRow = $byName[1];
        $this->assertSame('General', $nedRow['ticket_type']);
        $this->assertSame(2, $nedRow['quantity']);
        $this->assertSame(1, $nedRow['arrived_count']);
        $this->assertFalse($nedRow['arrived'], 'one of two is not "checked in"');
        $this->assertNull($nedRow['seat']);

        $this->assertSame([1, 0, false], [$byName[0]['quantity'], $byName[0]['arrived_count'], $byName[0]['arrived']]);

        // By address, when the name on the order is not the one they give.
        $byEmail = $this->actingAs($owner)->getJson($url.'?q=leftorium&date='.$date)->assertOk()->json('results');
        $this->assertSame(['Ned Flanders'], array_column($byEmail, 'name'));

        // Everybody in: that is "checked in".
        $line->forceFill(['seats' => json_encode([1 => now()->getTimestamp(), 2 => now()->getTimestamp()])])->save();
        $all = $this->actingAs($owner)->getJson($url.'?q=ned@&date='.$date)->assertOk()->json('results');
        $this->assertTrue($all[0]['arrived']);
        $this->assertSame(2, $all[0]['arrived_count']);
    }

    /** The list answers "may this person come in tonight", so it is the paid orders of this date. */
    public function test_only_paid_orders_of_that_date_are_found(): void
    {
        [$owner, $event, $ticket, $url] = $this->door();
        $role = $event->creatorRole;
        $date = $event->saleEventDateFromStartsAt();

        $this->createSale($event, $role, ['name' => 'Paid Patty', 'email' => 'patty@gmail.com'], $ticket);
        $this->createSale($event, $role, ['name' => 'Unpaid Patty', 'email' => 'unpaid@gmail.com', 'status' => 'unpaid'], $ticket);
        $this->createSale($event, $role, ['name' => 'Cancelled Patty', 'email' => 'cancelled@gmail.com', 'status' => 'cancelled'], $ticket);
        $this->createSale($event, $role, ['name' => 'Deleted Patty', 'email' => 'deleted@gmail.com', 'is_deleted' => true], $ticket);
        $this->createSale($event, $role, ['name' => 'Tomorrow Patty', 'email' => 'tomorrow@gmail.com', 'event_date' => now()->addDays(30)->format('Y-m-d')], $ticket);

        $found = $this->actingAs($owner)->getJson($url.'?q=patty&date='.$date)->assertOk()->json('results');
        $this->assertSame(['Paid Patty'], array_column($found, 'name'));

        // A wildcard typed at the door is a character, not a pattern that lists the whole night.
        $this->assertSame([], $this->actingAs($owner)->getJson($url.'?q=%25%25&date='.$date)->assertOk()->json('results'));
    }

    public function test_the_search_still_refuses_somebody_elses_event(): void
    {
        [, $event, $ticket, $url] = $this->door();
        $this->createSale($event, $event->creatorRole, ['name' => 'Ned Flanders'], $ticket);

        $this->actingAs($this->createOwner())->getJson($url.'?q=flanders')->assertForbidden();
    }
}
