<?php

namespace Tests\Feature;

use App\Exceptions\BusinessException;
use App\Models\Event;
use App\Models\Role;
use App\Models\SeatingLevel;
use App\Models\SeatingPlan;
use App\Models\SeatingSeat;
use App\Models\SeatingSection;
use App\Models\Ticket;
use App\Repos\EventRepo;
use App\Services\BestAvailableService;
use App\Services\SeatHoldService;
use App\Services\SeatingMapService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Cart holds and best-available.
 *
 * The hold is stage one of the two-stage claim: seats are held while the guest chooses, then flip
 * to sold at Sale creation BEFORE Stripe. Expiry is evaluated at read time, so a lapsed hold is
 * sellable the moment it lapses without any sweeper having run.
 */
class SeatingHoldTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function maps(): SeatingMapService
    {
        return app(SeatingMapService::class);
    }

    private function holds(): SeatHoldService
    {
        return app(SeatHoldService::class);
    }

    /**
     * Stalls: rows A and B, 8 seats each, gangway after seat 4. Circle: row A, 4 seats.
     * Section order puts Stalls first, so best-available should reach for it.
     */
    private function makePlan(Role $role): SeatingPlan
    {
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Main House']);
        $level = SeatingLevel::create(['seating_plan_id' => $plan->id, 'name' => 'Ground']);

        $stalls = SeatingSection::create([
            'seating_plan_id' => $plan->id, 'seating_level_id' => $level->id,
            'name' => 'Stalls', 'band' => 'Stalls', 'kind' => 'seated', 'position' => 0,
        ]);
        foreach ([['A', 1], ['B', 2]] as [$row, $rp]) {
            for ($n = 1; $n <= 8; $n++) {
                SeatingSeat::create([
                    'seating_plan_id' => $plan->id, 'seating_section_id' => $stalls->id,
                    'row_label' => $row, 'row_position' => $rp, 'seat_label' => (string) $n,
                    'position' => $n, 'aisle_after' => $n === 4,
                ]);
            }
        }

        $circle = SeatingSection::create([
            'seating_plan_id' => $plan->id, 'seating_level_id' => $level->id,
            'name' => 'Circle', 'band' => 'Circle', 'kind' => 'seated', 'position' => 1,
        ]);
        for ($n = 1; $n <= 4; $n++) {
            SeatingSeat::create([
                'seating_plan_id' => $plan->id, 'seating_section_id' => $circle->id,
                'row_label' => 'A', 'row_position' => 1, 'seat_label' => (string) $n, 'position' => $n,
            ]);
        }

        return $plan->fresh();
    }

    private function seatedEvent(Role $role, SeatingPlan $plan): Event
    {
        $request = Request::create('/', 'POST', [
            'name' => 'Test Event',
            'starts_at' => now()->addMonths(6)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'tickets_enabled' => 1, 'payment_method' => 'stripe', 'ticket_currency_code' => 'USD',
            'creator_role_id' => $role->id,
            'seating_plan_id' => $plan->id,
            'tickets' => [
                ['type' => 'Stalls', 'price' => 40, 'quantity' => 999, 'seating_band' => 'Stalls'],
                ['type' => 'Circle', 'price' => 25, 'quantity' => 999, 'seating_band' => 'Circle'],
            ],
        ]);
        $request->setUserResolver(fn () => $role->user);

        $event = app(EventRepo::class)->saveEvent($role, $request)->fresh();

        // saveEvent leaves the schedule pivot pending, and is_accepted is the universal visibility
        // gate - without this the guest page redirects instead of rendering.
        $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);

        return $event->fresh();
    }

    private function seatsIn($map, string $section, string $row)
    {
        return SeatingSeat::where('event_seating_map_id', $map->id)
            ->where('seating_section_id', $map->sections()->where('name', $section)->value('id'))
            ->where('row_label', $row)->orderBy('position')->get();
    }

    // ---------------------------------------------------------------- holds

    public function test_a_hold_replaces_the_previous_selection(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);

        // This test is about REPLACE-not-add. Moving a selection from seats 1-2 to 2-3 strands
        // seat 1, which the orphan rule (correctly) refuses - so switch it off here rather than
        // contorting the selection and losing what is being tested.
        $map->update(['orphan_rule_enabled' => false]);
        $map = $map->fresh();

        $seats = $this->seatsIn($map, 'Stalls', 'A');

        $this->holds()->acquire($map, [$seats[0]->id, $seats[1]->id], 'tok-a');
        $this->assertSame(2, SeatingSeat::where('hold_token', 'tok-a')->where('status', 'held')->count());

        // The guest deselects one and picks a different one. Additive holds would leak the first.
        $this->holds()->acquire($map, [$seats[1]->id, $seats[2]->id], 'tok-a');

        $this->assertSame('available', $seats[0]->fresh()->status);
        $this->assertSame('held', $seats[1]->fresh()->status);
        $this->assertSame('held', $seats[2]->fresh()->status);
        $this->assertSame(2, SeatingSeat::where('hold_token', 'tok-a')->where('status', 'held')->count());
    }

    public function test_a_second_cart_cannot_take_a_held_seat_and_is_told_which(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seats = $this->seatsIn($map, 'Stalls', 'A');

        $this->holds()->acquire($map, [$seats[0]->id], 'tok-a');

        try {
            $this->holds()->acquire($map, [$seats[0]->id, $seats[1]->id], 'tok-b');
            $this->fail('the second cart should have been refused');
        } catch (BusinessException $e) {
            // Named, so the picker can drop that one seat and keep the rest of the selection.
            $this->assertStringContainsString('Row A', $e->getMessage());
        }

        // And nothing was written for the loser.
        $this->assertSame('tok-a', $seats[0]->fresh()->hold_token);
        $this->assertSame('available', $seats[1]->fresh()->status);
    }

    public function test_a_lapsed_hold_is_takeable_with_no_sweeper_having_run(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seat = $this->seatsIn($map, 'Stalls', 'A')->first();

        $this->holds()->acquire($map, [$seat->id], 'tok-a');
        $seat->fresh()->update(['hold_expires_at' => now()->subMinute()]);

        $this->holds()->acquire($map, [$seat->id], 'tok-b');
        $this->assertSame('tok-b', $seat->fresh()->hold_token);
    }

    public function test_release_frees_everything_the_token_held(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seats = $this->seatsIn($map, 'Stalls', 'A');

        $this->holds()->acquire($map, [$seats[0]->id, $seats[1]->id], 'tok-a');
        $this->holds()->release($map, 'tok-a');

        $this->assertSame(0, SeatingSeat::where('hold_token', 'tok-a')->count());
        $this->assertSame('available', $seats[0]->fresh()->status);
    }

    public function test_extend_will_not_revive_a_lapsed_hold(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seat = $this->seatsIn($map, 'Stalls', 'A')->first();

        $this->holds()->acquire($map, [$seat->id], 'tok-a');
        $this->assertNotNull($this->holds()->extend($map, 'tok-a'));

        $seat->fresh()->update(['hold_expires_at' => now()->subMinute()]);

        // Reviving it would take back a seat somebody else may already have.
        $this->assertNull($this->holds()->extend($map, 'tok-a'));
        $this->assertTrue($seat->fresh()->isAvailable());
    }

    // ---------------------------------------------------------------- best available

    public function test_best_available_picks_a_contiguous_block_in_the_first_section(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $stalls = $event->tickets->firstWhere('type', 'Stalls');

        $picked = app(BestAvailableService::class)->pick($map, $stalls, 3);
        $seats = SeatingSeat::whereIn('id', $picked)->orderBy('position')->get();

        $this->assertCount(3, $picked);
        $this->assertSame(['Stalls', 'Stalls', 'Stalls'], $seats->map(fn ($s) => $s->section->name)->all());
        $this->assertSame(['A', 'A', 'A'], $seats->pluck('row_label')->all(), 'the first row wins');
        // Consecutive, and never straddling the gangway after seat 4.
        $positions = $seats->pluck('position')->all();
        $this->assertSame(range($positions[0], $positions[0] + 2), $positions);
        $this->assertNotContains(4, array_slice($positions, 0, 2), 'a block must not cross the aisle');
    }

    public function test_best_available_will_not_straddle_a_gangway(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $stalls = $event->tickets->firstWhere('type', 'Stalls');

        // Seats 1-6 free in row A (7 and 8 sold), nothing in row B. The gangway is after seat 4.
        //
        // Chosen so the two behaviours DISAGREE: scored purely on distance from the centre of what
        // is free, seats 2-5 is the most central four - and it straddles the gangway. Respecting
        // the aisle rejects it and 3-6 with it, leaving 1-4, which ends AT the gangway rather than
        // crossing it. An earlier version of this test used a layout where both rules happened to
        // land on the same seats, so it passed with the aisle check deleted.
        $a = $this->seatsIn($map, 'Stalls', 'A');
        SeatingSeat::whereIn('id', $a->whereIn('position', [7, 8])->pluck('id'))->update(['status' => 'sold']);
        SeatingSeat::whereIn('id', $this->seatsIn($map, 'Stalls', 'B')->pluck('id'))->update(['status' => 'sold']);

        $picked = app(BestAvailableService::class)->pick($map, $stalls, 4);
        $seats = SeatingSeat::whereIn('id', $picked)->orderBy('position')->get();

        $this->assertCount(4, $picked, 'it still fills the order');
        $this->assertSame([1, 2, 3, 4], $seats->pluck('position')->all(),
            'a party must not be split across a gangway just because the block looks more central');
        $this->assertTrue($a->firstWhere('position', 4)->aisle_after, 'fixture sanity: the gangway is after seat 4');
    }

    public function test_best_available_returns_what_it_can_when_the_band_is_nearly_full(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $circle = $event->tickets->firstWhere('type', 'Circle');

        SeatingSeat::whereIn('id', $this->seatsIn($map, 'Circle', 'A')->slice(0, 3)->pluck('id'))
            ->update(['status' => 'sold']);

        $this->assertCount(1, app(BestAvailableService::class)->pick($map, $circle, 4));
        $this->assertSame([], app(BestAvailableService::class)->pick($map, $circle, 0));
    }

    // ---------------------------------------------------------------- the guest endpoint

    public function test_the_guest_payload_never_leaks_who_holds_a_seat(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seats = $this->seatsIn($map, 'Stalls', 'A');

        $seats[0]->update([
            'status' => 'held', 'hold_kind' => 'house', 'hold_note' => 'Reserved for the producer',
        ]);
        $seats[1]->update(['status' => 'sold']);

        $body = $this->getJson(route('seating.state', [
            'subdomain' => $role->subdomain,
            'event_id' => UrlUtils::encodeId($event->id),
            'date' => $event->saleEventDateFromStartsAt(),
        ]))->assertOk()->getContent();

        $this->assertStringNotContainsString('Reserved for the producer', $body);
        $this->assertStringNotContainsString('hold_note', $body);
        $this->assertStringNotContainsString('hold_kind', $body);
        $this->assertStringNotContainsString('hold_token', $body);

        $states = collect(json_decode($body, true)['levels'][0]['sections'])
            ->firstWhere('name', 'Stalls')['seats'];
        $byId = collect($states)->keyBy('id');
        $this->assertSame('taken', $byId[$seats[0]->id]['state'], 'a house block reads as simply taken');
        $this->assertSame('taken', $byId[$seats[1]->id]['state']);
        $this->assertSame('available', $byId[$seats[2]->id]['state']);
    }

    public function test_holding_through_the_endpoint_marks_the_seats_mine(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seats = $this->seatsIn($map, 'Stalls', 'A');

        $args = ['subdomain' => $role->subdomain];
        $payload = [
            'event_id' => UrlUtils::encodeId($event->id),
            'date' => $event->saleEventDateFromStartsAt(),
            'seat_ids' => [$seats[0]->id, $seats[1]->id],
        ];

        $this->postJson(route('seating.hold', $args), $payload)
            ->assertOk()
            ->assertJsonStructure(['held', 'expires_at', 'version']);

        // Same session, so the token matches and the seats read back as mine.
        $state = $this->getJson(route('seating.state', $args + [
            'event_id' => $payload['event_id'], 'date' => $payload['date'],
        ]))->json();

        $byId = collect(collect($state['levels'][0]['sections'])->firstWhere('name', 'Stalls')['seats'])->keyBy('id');
        $this->assertSame('mine', $byId[$seats[0]->id]['state']);
        $this->assertSame('mine', $byId[$seats[1]->id]['state']);
    }

    /**
     * The guest page has to actually RENDER the picker, not merely serve its endpoints.
     *
     * Every other test here talks to JSON endpoints, so none of them compiles the ticket form -
     * and this exact form already died twice during the build on Blade parse errors that only
     * unrelated tests happened to catch.
     */
    public function test_the_guest_page_renders_the_picker_for_an_allocated_ticket(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));

        $html = $this->get(route('event.view_guest', [
            'subdomain' => $role->subdomain, 'slug' => $event->slug,
        ]))->assertOk()->getContent();

        $this->assertStringContainsString('seating-picker-mount', $html, 'the mount point is missing');
        $this->assertStringContainsString('pickerProps(ticket)', $html);
        $this->assertStringContainsString('seatingPicker:', $html, 'the shared props never reached the client');
        $this->assertStringContainsString('"is_allocated":true', $html);
        $this->assertStringContainsString('es-seats-changed', $html, 'the bridge back to the form is missing');

        // The strings resolved rather than shipping raw keys, and the endpoints are path-relative
        // so they stay same-origin on a custom domain.
        $this->assertStringContainsString(__('messages.seating_choose_own'), $html);
        $this->assertStringNotContainsString('messages.seating_how_many', $html);
        $this->assertStringContainsString('\\/seating\\/hold', $html);
    }

    public function test_an_event_with_no_plan_renders_no_picker(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $request = Request::create('/', 'POST', [
            'name' => 'Plain Event',
            'starts_at' => now()->addMonths(6)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'tickets_enabled' => 1, 'payment_method' => 'stripe', 'ticket_currency_code' => 'USD',
            'creator_role_id' => $role->id,
            'tickets' => [['type' => 'General', 'price' => 10, 'quantity' => 40]],
        ]);
        $request->setUserResolver(fn () => $role->user);
        $event = app(EventRepo::class)->saveEvent($role, $request)->fresh();
        $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);

        $html = $this->get(route('event.view_guest', [
            'subdomain' => $role->subdomain, 'slug' => $event->slug,
        ]))->assertOk()->getContent();

        // The mount element's CLASS is part of the client-side template and is therefore always in
        // the source; what must differ is that no ticket claims to be allocated and the picker gets
        // no props at all.
        $this->assertStringContainsString('"is_allocated":false', $html);
        $this->assertStringNotContainsString('"is_allocated":true', $html);
        $this->assertStringContainsString('seatingPicker: null', $html);
        $this->assertStringNotContainsString('seating_choose_own', $html);
        $this->assertStringNotContainsString(__('messages.seating_choose_own'), $html);
    }

    // ------------------------------------------------ review finding 2

    public function test_state_will_not_materialize_a_map_for_an_event_that_cannot_sell(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));

        // Ticketing switched off, so nobody can buy - and therefore nobody should be able to make
        // this date's snapshot exist. A crawler walking a recurring event's dates would otherwise
        // create a map per date through a plain GET.
        $event->tickets_enabled = false;
        $event->save();

        $this->assertNull($this->maps()->mapFor($event->fresh(), $event->saleEventDateFromStartsAt()));

        $this->getJson(route('seating.state', [
            'subdomain' => $role->subdomain,
            'event_id' => UrlUtils::encodeId($event->id),
            'date' => $event->saleEventDateFromStartsAt(),
        ]))->assertStatus(422);

        $this->assertSame(0, \App\Models\EventSeatingMap::where('event_id', $event->id)->count(),
            'a GET must not create rows');
    }

    public function test_state_still_materializes_for_a_sellable_date(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $date = $event->saleEventDateFromStartsAt();

        $this->assertNull($this->maps()->mapFor($event, $date), 'fixture sanity: nothing yet');

        // The lazy path is still the point - a real buyer opening the map brings it into existence.
        $this->getJson(route('seating.state', [
            'subdomain' => $role->subdomain,
            'event_id' => UrlUtils::encodeId($event->id),
            'date' => $date,
        ]))->assertOk()->assertJsonStructure(['version', 'levels']);

        $this->assertNotNull($this->maps()->mapFor($event->fresh(), $date));
    }

    public function test_the_release_endpoint_is_gone(): void
    {
        // acquire([]) already frees everything the token holds, so a separate unauthenticated
        // write route earned nothing. Holding zero seats is the release.
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('seating.release'));

        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seats = $this->seatsIn($map, 'Stalls', 'A');

        $args = ['subdomain' => $role->subdomain];
        $body = ['event_id' => UrlUtils::encodeId($event->id), 'date' => $event->saleEventDateFromStartsAt()];

        $this->postJson(route('seating.hold', $args), $body + ['seat_ids' => [$seats[0]->id]])->assertOk();
        $this->assertSame('held', $seats[0]->fresh()->status);

        $this->postJson(route('seating.hold', $args), $body + ['seat_ids' => []])->assertOk();
        $this->assertSame('available', $seats[0]->fresh()->status);
    }

    // ------------------------------------------------ review finding 6: the embed

    public function test_the_embed_renders_the_picker(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));

        $html = $this->get(route('event.view_guest', [
            // ?tickets=true is what selects show-guest-ticket-embed; ?embed=true alone renders
            // the ordinary guest page in a frame.
            'subdomain' => $role->subdomain, 'slug' => $event->slug,
            'embed' => 'true', 'tickets' => 'true',
        ]))->assertOk()->getContent();

        $this->assertStringContainsString('seatingPicker:', $html);
        $this->assertStringContainsString('"is_allocated":true', $html);

        // The hold token lives in the session, which in a cross-site iframe depends on
        // SESSION_SAME_SITE. That is NOT a new constraint: event.checkout is already on
        // SecurityHeaders' embeddable list and posts a session CSRF token, so embedded checkout has
        // always had the same dependency. If one works in a deployment, so does the other.
        $this->assertStringContainsString('csrfToken', $html);
    }

    public function test_a_seat_id_from_another_event_is_ignored(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $mine = $this->seatedEvent($role, $plan);
        $other = $this->seatedEvent($role, $plan);

        $mineMap = $this->maps()->materialize($mine);
        $otherMap = $this->maps()->materialize($other);
        $foreign = SeatingSeat::where('event_seating_map_id', $otherMap->id)->first();

        $this->postJson(route('seating.hold', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($mine->id),
            'date' => $mine->saleEventDateFromStartsAt(),
            'seat_ids' => [$foreign->id],
        ])->assertOk()->assertJson(['held' => []]);

        $this->assertSame('available', $foreign->fresh()->status);
    }
}
