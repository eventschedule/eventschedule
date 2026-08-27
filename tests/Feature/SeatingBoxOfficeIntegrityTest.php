<?php

namespace Tests\Feature;

use App\Exceptions\BusinessException;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\SeatingLevel;
use App\Models\SeatingPlan;
use App\Models\SeatingSeat;
use App\Models\SeatingSection;
use App\Repos\EventRepo;
use App\Services\BoxOfficeSeatingService;
use App\Services\SeatingMapService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The box office console answers about seats the caller named, and $seatIds is raw request input.
 *
 * Every MUTATION was already scoped to the map. The two helpers that DESCRIBE a mutation for the
 * audit log were not, so an editor could name any id on the install and have the reply written into
 * a row on their own audit page. These tests pin the scoping, the refusal of ids that are not on
 * the map at all, and the two states that must not outlive a seat's owner.
 */
class SeatingBoxOfficeIntegrityTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function seatedEvent(Role $role, string $buyer): array
    {
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'House']);
        $level = SeatingLevel::create(['seating_plan_id' => $plan->id, 'name' => 'Ground']);
        $section = SeatingSection::create([
            'seating_plan_id' => $plan->id, 'seating_level_id' => $level->id,
            'name' => 'Stalls', 'band' => 'Stalls', 'kind' => 'seated', 'position' => 0,
        ]);
        foreach (range(1, 6) as $n) {
            SeatingSeat::create([
                'seating_plan_id' => $plan->id, 'seating_section_id' => $section->id,
                'row_label' => 'A', 'row_position' => 1, 'seat_label' => (string) $n,
                'position' => $n, 'x' => $n * 26,
            ]);
        }

        $request = Request::create('/', 'POST', [
            'name' => 'Show', 'starts_at' => now()->addMonth()->setTime(19, 30)->format('Y-m-d H:i:s'),
            'tickets_enabled' => 1, 'payment_method' => 'cash', 'ticket_currency_code' => 'USD',
            'creator_role_id' => $role->id, 'seating_plan_id' => $plan->id,
            'tickets' => [['type' => 'Stalls', 'price' => 20, 'quantity' => 999, 'seating_band' => 'Stalls']],
        ]);
        $request->setUserResolver(fn () => $role->user);
        $event = app(EventRepo::class)->saveEvent($role, $request)->fresh();
        $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);
        $event = $event->fresh();

        $map = app(SeatingMapService::class)->materialize($event, $event->saleEventDateFromStartsAt());
        $seats = SeatingSeat::where('event_seating_map_id', $map->id)
            ->orderBy('position')->take(2)->pluck('id')->all();

        $this->actingAs($role->user)->postJson(route('box_office.book', [
            'subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id),
        ]), [
            'seat_ids' => $seats, 'name' => $buyer, 'email' => 'buyer@gmail.com', 'status' => 'paid',
        ])->assertOk();

        return [$event, $map->fresh(), $seats];
    }

    /** Two venues, two shows, no relationship between them. */
    private function twoHouses(): array
    {
        $mine = $this->createRole($this->createOwner(), 'venue');
        $theirs = $this->createRole($this->createOwner(), 'venue');

        return [
            $mine, $this->seatedEvent($mine, 'My Buyer'),
            $theirs, $this->seatedEvent($theirs, 'Zenobia Farnsworth'),
        ];
    }

    public function test_an_audit_row_cannot_describe_seats_from_another_venue(): void
    {
        [$mine, [$myEvent, $myMap], , [, , $theirSeats]] = $this->twoHouses();

        $free = SeatingSeat::where('event_seating_map_id', $myMap->id)
            ->where('status', 'available')->orderBy('position')->take(2)->pluck('id')->all();

        // My own seats, plus two ids from a venue I have nothing to do with.
        $this->actingAs($mine->user)->postJson(route('box_office.block', [
            'subdomain' => $mine->subdomain, 'hash' => UrlUtils::encodeId($myEvent->id),
        ]), ['seat_ids' => array_merge($free, $theirSeats), 'kind' => 'box_office'])->assertOk();

        $row = AuditLog::where('action', 'like', 'sale.seat_%')->latest('id')->first();
        $this->assertNotNull($row, 'blocking my own seats must still be recorded');

        // Their seats are labelled A1 and A2 in their own house, and so are two of mine - so the
        // assertion is on the COUNT of labels, which is what the leak actually inflated.
        $labels = substr_count($row->metadata, 'A');
        $this->assertSame(count($free), $labels, 'the row must describe only the seats on this map');

        // And their seats are untouched, which was always true - the mutation was never the leak.
        $this->assertSame(2, SeatingSeat::whereIn('id', $theirSeats)->where('status', 'sold')->count());
    }

    public function test_the_buyer_lookup_is_scoped_to_the_map(): void
    {
        [, [, $myMap], , [, , $theirSeats]] = $this->twoHouses();

        $controller = app(\App\Http\Controllers\BoxOfficeController::class);
        $method = new \ReflectionMethod($controller, 'buyersOf');
        $method->setAccessible(true);

        $this->assertSame('', $method->invoke($controller, $myMap, $theirSeats),
            'naming another venue\'s seats must not return their customer');
    }

    public function test_releasing_a_seat_that_is_not_on_this_map_is_refused(): void
    {
        [, [, $myMap, $mySeats], , [, , $theirSeats]] = $this->twoHouses();

        // Two ids, so this takes the bulk path rather than delegating to the single-seat one.
        try {
            app(BoxOfficeSeatingService::class)->releaseSeats($myMap, $theirSeats);
            $this->fail('ids that are not on this map must be refused, not reported as released');
        } catch (BusinessException $e) {
            // expected
        }

        $this->assertSame(2, SeatingSeat::whereIn('id', $theirSeats)->where('status', 'sold')->count());

        // One foreign id smuggled in among valid ones is refused whole, and nothing moves.
        try {
            app(BoxOfficeSeatingService::class)->releaseSeats($myMap, array_merge($mySeats, [$theirSeats[0]]));
            $this->fail('a partly foreign selection must be refused');
        } catch (BusinessException $e) {
            // expected
        }

        $this->assertSame(2, SeatingSeat::whereIn('id', $mySeats)->where('status', 'sold')->count());
    }

    public function test_no_audit_row_survives_a_release_that_failed(): void
    {
        [$mine, [$myEvent, $myMap, $mySeats]] = $this->twoHouses();

        $free = SeatingSeat::where('event_seating_map_id', $myMap->id)
            ->where('status', 'available')->first();

        AuditLog::query()->delete();

        // A mixed selection is refused by design, and the refusal must leave no trace claiming
        // otherwise - mutate() is not transactional, so an audit written first would commit.
        $this->actingAs($mine->user)->postJson(route('box_office.release_seat', [
            'subdomain' => $mine->subdomain, 'hash' => UrlUtils::encodeId($myEvent->id),
        ]), ['seat_ids' => array_merge($mySeats, [$free->id])])->assertStatus(422);

        $this->assertSame(0, AuditLog::where('action', 'sale.seat_released')->count(),
            'a refused release must not be recorded as a release');
        $this->assertSame(2, SeatingSeat::whereIn('id', $mySeats)->where('status', 'sold')->count());
    }

    public function test_arrival_does_not_transfer_to_the_next_buyer(): void
    {
        [$mine, [$myEvent, $myMap, $mySeats]] = $this->twoHouses();

        // Through the door.
        SeatingSeat::whereIn('id', $mySeats)->update(['checked_in_at' => now()]);

        $this->actingAs($mine->user)->postJson(route('box_office.release_seat', [
            'subdomain' => $mine->subdomain, 'hash' => UrlUtils::encodeId($myEvent->id),
        ]), ['seat_ids' => $mySeats])->assertOk();

        $this->assertSame(0, SeatingSeat::whereIn('id', $mySeats)->whereNotNull('checked_in_at')->count(),
            'releasing a seat must take its arrival with it');

        // Resold to somebody else, who has not arrived.
        $this->actingAs($mine->user)->postJson(route('box_office.book', [
            'subdomain' => $mine->subdomain, 'hash' => UrlUtils::encodeId($myEvent->id),
        ]), ['seat_ids' => $mySeats, 'name' => 'Second Buyer', 'email' => 'second@gmail.com', 'status' => 'paid'])
            ->assertOk();

        $this->assertSame(0, SeatingSeat::whereIn('id', $mySeats)->whereNotNull('checked_in_at')->count(),
            'the new holder must not inherit the previous one\'s arrival');
    }

    public function test_an_exchange_carries_the_arrival_to_the_new_seat(): void
    {
        [, [, $myMap, $mySeats]] = $this->twoHouses();

        SeatingSeat::whereIn('id', [$mySeats[0]])->update(['checked_in_at' => now()]);

        $to = SeatingSeat::where('event_seating_map_id', $myMap->id)
            ->where('status', 'available')->orderBy('position')->first();

        app(BoxOfficeSeatingService::class)->exchange($myMap, $mySeats[0], $to->id);

        $this->assertNotNull($to->fresh()->checked_in_at,
            'somebody already inside who is moved is still inside');
        $this->assertNull(SeatingSeat::find($mySeats[0])->checked_in_at,
            'the seat they left must not keep the arrival');
    }
}
