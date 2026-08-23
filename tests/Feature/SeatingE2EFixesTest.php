<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventSeatingMap;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SeatingLevel;
use App\Models\SeatingPlan;
use App\Models\SeatingSeat;
use App\Models\SeatingSection;
use App\Models\Ticket;
use App\Repos\EventRepo;
use App\Services\SeatingMapService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The eight blockers the end-to-end review found across phases 1-5.
 *
 * Every one of these was invisible to the 82 seating tests that were already passing, because the
 * per-phase reviews only ever examined the phase in front of them - so nothing was checking the
 * sale paths that never go through TicketController::checkout().
 */
class SeatingE2EFixesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function maps(): SeatingMapService
    {
        return app(SeatingMapService::class);
    }

    /** Stalls: one row of 8, plus a Circle of 4 so band separation is testable. */
    private function makePlan(Role $role): SeatingPlan
    {
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Main House']);
        $level = SeatingLevel::create(['seating_plan_id' => $plan->id, 'name' => 'Ground']);

        foreach ([['Stalls', 8, 0], ['Circle', 4, 1]] as [$name, $count, $pos]) {
            $section = SeatingSection::create([
                'seating_plan_id' => $plan->id, 'seating_level_id' => $level->id,
                'name' => $name, 'band' => $name, 'kind' => 'seated', 'position' => $pos,
            ]);
            for ($n = 1; $n <= $count; $n++) {
                SeatingSeat::create([
                    'seating_plan_id' => $plan->id, 'seating_section_id' => $section->id,
                    'row_label' => 'A', 'row_position' => 1, 'seat_label' => (string) $n,
                    'position' => $n, 'x' => $n * 26,
                ]);
            }
        }

        return $plan->fresh();
    }

    private function seatedEvent(Role $role, SeatingPlan $plan, array $attrs = []): Event
    {
        $request = Request::create('/', 'POST', array_merge([
            'name' => 'Seated Show',
            'starts_at' => now()->addMonths(6)->setTime(19, 30)->format('Y-m-d H:i:s'),
            'tickets_enabled' => 1, 'payment_method' => 'cash', 'ticket_currency_code' => 'USD',
            'creator_role_id' => $role->id,
            'seating_plan_id' => $plan->id,
            'tickets' => [
                ['type' => 'Stalls', 'price' => 40, 'quantity' => 999, 'seating_band' => 'Stalls'],
                ['type' => 'Circle', 'price' => 25, 'quantity' => 999, 'seating_band' => 'Circle'],
            ],
        ], $attrs));
        $request->setUserResolver(fn () => $role->user);
        $event = app(EventRepo::class)->saveEvent($role, $request)->fresh();
        $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);

        return $event->fresh();
    }

    private function seats(EventSeatingMap $map, string $section)
    {
        return SeatingSeat::where('event_seating_map_id', $map->id)
            ->where('seating_section_id', $map->sections()->where('name', $section)->value('id'))
            ->orderBy('position')->get();
    }

    // ------------------------------------------------------------ B1

    public function test_payment_link_mode_refuses_an_allocated_event_instead_of_freeing_the_seats(): void
    {
        $owner = $this->createOwner();
        $owner->invoiceninja_mode = 'payment_link';
        $owner->save();

        $role = $this->createRole($owner, 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role), ['payment_method' => 'invoiceninja']);
        $map = $this->maps()->materialize($event);
        $seat = $this->seats($map, 'Stalls')->first();

        $this->postJson(route('seating.hold', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'date' => $event->saleEventDateFromStartsAt(),
            'seat_ids' => [$seat->id],
        ])->assertOk();

        $this->post(route('event.checkout', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => $event->saleEventDateFromStartsAt(),
            'name' => 'Buyer', 'email' => 'buyer@gmail.com',
            'tickets' => [UrlUtils::encodeId($event->tickets->firstWhere('type', 'Stalls')->id) => 1],
        ])->assertRedirect();

        $this->assertSame(0, Sale::count(), 'the sale is refused, not written');
        // The old behaviour RELEASED the seat here and then took payment via the webhook.
        $this->assertSame('held', $seat->fresh()->status, 'the buyer keeps their hold');
    }

    // ------------------------------------------------------------ B2

    public function test_an_imported_attendee_is_given_a_real_seat(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $stalls = $event->tickets->firstWhere('type', 'Stalls');

        $this->actingAs($owner)->post(route('sales.import_store', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => $event->saleEventDateFromStartsAt(),
            'ticket_id' => UrlUtils::encodeId($stalls->id),
            'default_status' => 'paid',
            'entries' => [
                ['name' => 'Comp Guest', 'email' => 'comp@gmail.com', 'quantity' => 2],
            ],
        ]);

        $sale = Sale::first();
        $this->assertNotNull($sale, 'fixture sanity: the import created a sale');

        // Without the auto-assign the sale exists with no seats and the picker keeps offering them.
        $this->assertSame(2, SeatingSeat::where('sale_id', $sale->id)->where('status', 'sold')->count());
    }

    // ------------------------------------------------------------ B3

    public function test_individual_tickets_seats_every_attendee_on_their_own_sale(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $plan = $this->makePlan($role);
        $event = $this->seatedEvent($role, $plan, ['individual_tickets' => 1]);
        $map = $this->maps()->materialize($event);
        $stalls = $event->tickets->firstWhere('type', 'Stalls');
        $picked = $this->seats($map, 'Stalls')->take(2);

        $this->postJson(route('seating.hold', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'date' => $event->saleEventDateFromStartsAt(),
            'seat_ids' => $picked->pluck('id')->all(),
        ])->assertOk();

        $this->post(route('event.checkout', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => $event->saleEventDateFromStartsAt(),
            'name' => 'Primary', 'email' => 'primary@gmail.com',
            'tickets' => [UrlUtils::encodeId($stalls->id) => 2],
            'guests' => [
                ['name' => 'Primary', 'email' => 'primary@gmail.com'],
                ['name' => 'Second', 'email' => 'second@gmail.com'],
            ],
        ])->assertRedirect();

        // Previously: same band => balance check threw and this was unbuyable at all.
        $this->assertGreaterThan(0, Sale::count(), 'the purchase completes');
        $this->assertSame(2, SeatingSeat::whereNotNull('sale_id')->where('status', 'sold')->count());

        // Each attendee's seat belongs to their OWN sale, so their own ticket page shows it.
        $saleIds = SeatingSeat::whereNotNull('sale_id')->pluck('sale_id')->unique();
        $this->assertCount(2, $saleIds, 'one seat per attendee sale, not both on the primary');
    }

    // ------------------------------------------------------------ B4

    public function test_renaming_a_band_on_the_template_does_not_unallocate_a_live_occurrence(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $event = $this->seatedEvent($role, $plan);
        $date = $event->saleEventDateFromStartsAt();
        $this->maps()->materialize($event, $date);

        $stalls = $event->tickets->firstWhere('type', 'Stalls');
        $stalls->setRelation('event', $event);
        $this->assertTrue($stalls->isAllocated($date), 'fixture sanity');

        // The organizer renames the band on the TEMPLATE while the date is on sale. The snapshot
        // keeps the old name by design - reading the template here made the ticket look unallocated,
        // which skipped the checkout balance check entirely and sold seats that were never claimed.
        $plan->sections()->where('band', 'Stalls')->update(['band' => 'Orchestra']);

        $event = $event->fresh();
        $stalls = $event->tickets->firstWhere('type', 'Stalls');
        $stalls->setRelation('event', $event);

        $this->assertTrue($stalls->isAllocated($date), 'the occurrence is still seat-allocated');
    }

    public function test_a_lapsed_schedule_keeps_its_bands(): void
    {
        config(['app.hosted' => true]);
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $plan = $this->makePlan($role);
        $event = $this->seatedEvent($role, $plan);

        $this->assertSame('Stalls', $event->tickets->firstWhere('type', 'Stalls')->seating_band);

        $role->plan_type = 'free';
        $role->plan_expires = now()->subYear()->format('Y-m-d');
        $role->save();
        $role = $role->fresh();

        // An unrelated save. The band select no longer renders, so nothing is posted for it.
        $request = Request::create('/', 'POST', [
            'name' => 'Renamed Show',
            'starts_at' => $event->starts_at,
            'tickets_enabled' => 1, 'payment_method' => 'cash', 'ticket_currency_code' => 'USD',
            'seating_plan_id' => $plan->id,
            'tickets' => [
                ['id' => $event->tickets->firstWhere('type', 'Stalls')->id, 'type' => 'Stalls', 'price' => 40, 'quantity' => 8],
            ],
        ]);
        $request->setUserResolver(fn () => $role->user);
        app(EventRepo::class)->saveEvent($role, $request, $event);

        $this->assertSame('Stalls', Ticket::where('event_id', $event->id)->where('type', 'Stalls')->value('seating_band'),
            'a lapse must not wipe seating that is already sold against');
    }

    // ------------------------------------------------------------ B5

    public function test_a_bogus_date_creates_no_seat_map(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));

        foreach (['aaaa', '2026-13-45', now()->addYears(3)->format('Y-m-d')] as $bogus) {
            $this->getJson(route('seating.state', [
                'subdomain' => $role->subdomain,
                'event_id' => UrlUtils::encodeId($event->id),
                'date' => $bogus,
            ]))->assertStatus(404);
        }

        $this->assertSame(0, EventSeatingMap::where('event_id', $event->id)->count(),
            'an anonymous GET must not be able to write thousands of rows per request');

        // The real occurrence still works.
        $this->getJson(route('seating.state', [
            'subdomain' => $role->subdomain,
            'event_id' => UrlUtils::encodeId($event->id),
            'date' => $event->saleEventDateFromStartsAt(),
        ]))->assertOk();
    }

    // ------------------------------------------------------------ B6

    public function test_an_unlisted_events_seat_map_is_not_readable(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $event->is_private = true;
        $event->save();

        $args = [
            'subdomain' => $role->subdomain,
            'event_id' => UrlUtils::encodeId($event->id),
            'date' => $event->saleEventDateFromStartsAt(),
        ];

        // Checkout already refused this; the seat endpoints served the whole room.
        $this->getJson(route('seating.state', $args))->assertStatus(404);
        $this->postJson(route('seating.hold', ['subdomain' => $role->subdomain]),
            ['event_id' => $args['event_id'], 'date' => $args['date'], 'seat_ids' => []])->assertStatus(404);
    }

    // ------------------------------------------------------------ B7

    /**
     * NOTE on what this actually pins.
     *
     * The reported bug was that ApiEventController rebuilds the ticket array without
     * `seating_band`, so EventRepo wrote null. Adding the key to that map fixes it - but the B4
     * fix ("a band that was not posted is preserved, not cleared") closes the same hole from the
     * other side, and A/B'ing proves it: removing the key from the API map no longer changes the
     * outcome. So this test pins the OUTCOME, and the API-map entry is deliberate redundancy
     * rather than the load-bearing fix. Both stay: the explicit key is what keeps an API caller
     * who DOES send tickets from losing bands.
     */
    public function test_an_api_update_that_omits_tickets_preserves_the_band(): void
    {
        $owner = $this->createOwner();
        $raw = Str::random(32);
        $owner->api_key = substr(hash('sha256', $raw), 0, 8);
        $owner->api_key_hash = Hash::make($raw);
        $owner->save();

        $role = $this->createRole($owner, 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));

        $this->putJson('/api/events/'.UrlUtils::encodeId($event->id), [
            'name' => 'Renamed By API',
        ], ['X-API-Key' => $raw])->assertOk();

        // Prove the request actually went through - otherwise the band "surviving" says nothing.
        $this->assertSame('Renamed By API', $event->fresh()->name);

        $this->assertSame('Stalls',
            Ticket::where('event_id', $event->id)->where('type', 'Stalls')->value('seating_band'),
            'a rename must not silently turn every allocated ticket into general admission');
    }

    // ------------------------------------------------------------ B8

    public function test_the_explicit_hold_path_respects_the_per_order_cap(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $event = $this->seatedEvent($role, $plan);
        $map = $this->maps()->materialize($event);

        Ticket::where('event_id', $event->id)->where('type', 'Stalls')->update(['max_per_order' => 2]);

        $all = $this->seats($map, 'Stalls');
        $this->postJson(route('seating.hold', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'date' => $event->saleEventDateFromStartsAt(),
            'seat_ids' => $all->pluck('id')->all(),
        ])->assertOk();

        // Eight posted, two allowed. Previously the explicit path had no cap at all.
        $this->assertSame(2, SeatingSeat::where('event_seating_map_id', $map->id)
            ->where('status', 'held')->count());
    }
}
