<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SeatingLevel;
use App\Models\SeatingPlan;
use App\Models\SeatingSeat;
use App\Models\SeatingSection;
use App\Repos\EventRepo;
use App\Services\SeatingMapService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Arrival state on the seat map.
 *
 * The console drew sold / blocked / held / available and had no "arrived" at all; the printed sheet
 * had no column for it, not even an empty box to tick by hand; and the check-in screen had no search
 * of any kind. "Is C14 here yet" was unanswerable on every surface.
 *
 * Stamped per ticket LINE rather than per admission slot: sale_tickets.seats is a slot map carrying
 * no location, and lining it up with seats positionally is fine for labelling a scan and far too
 * fragile to key state on.
 */
class SeatingArrivalTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /**
     * The scan window is [start - 24h, start + duration] and the fixture pins a wall-clock time of
     * day, so what keeps this green is the -24h bound plus the fixture schedule's timezone: NY is
     * behind UTC, which pushes a 19:30 local start out to 23:30 UTC and leaves the window open at
     * every hour. Nothing in the test states that, and a default timezone EAST of UTC would close
     * it - a Tokyo schedule puts the same show at 10:30 UTC and shuts the window at 12:30.
     *
     * Freezing the clock removes the dependency rather than relying on it holding.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(now()->setTime(12, 0));
    }

    private function seatedEvent(Role $role): Event
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
                'row_label' => 'C', 'row_position' => 1, 'seat_label' => (string) $n,
                'position' => $n, 'x' => $n * 26,
            ]);
        }

        $request = Request::create('/', 'POST', [
            'name' => 'Door Show',
            // Today: scanned() enforces a check-in window around the occurrence, and a show two
            // days out is refused - with a 200 carrying an `error`, which is why the assertion
            // below checks the BODY rather than the status.
            'starts_at' => now()->setTime(19, 30)->format('Y-m-d H:i:s'),
            'tickets_enabled' => 1, 'payment_method' => 'cash', 'ticket_currency_code' => 'USD',
            'creator_role_id' => $role->id, 'seating_plan_id' => $plan->id,
            'tickets' => [['type' => 'Stalls', 'price' => 20, 'quantity' => 999, 'seating_band' => 'Stalls']],
        ]);
        $request->setUserResolver(fn () => $role->user);
        $event = app(EventRepo::class)->saveEvent($role, $request)->fresh();
        $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);

        return $event->fresh();
    }

    private function sellTwo(Role $role, Event $event): array
    {
        $map = app(SeatingMapService::class)->materialize($event, $event->saleEventDateFromStartsAt());
        $seats = SeatingSeat::where('event_seating_map_id', $map->id)
            ->orderBy('position')->take(2)->pluck('id')->all();

        $this->actingAs($role->user)->postJson(route('box_office.book', [
            'subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id),
        ]), [
            'seat_ids' => $seats, 'name' => 'Ned Flanders', 'email' => 'ned@gmail.com', 'status' => 'paid',
        ])->assertOk();

        return [$map->fresh(), $seats];
    }

    public function test_a_scan_marks_the_seats_as_arrived(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role);
        [, $seats] = $this->sellTwo($role, $event);

        $this->assertNull(SeatingSeat::find($seats[0])->checked_in_at);

        $sale = Sale::latest('id')->firstOrFail();
        $response = $this->actingAs($role->user)->post(route('ticket.scanned', [
            'event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret,
        ]))->assertOk();

        // scanned() answers 200 for a REFUSAL too, carrying an `error` - so a bare assertOk() here
        // would pass on a ticket that was never admitted.
        $this->assertNull($response->json('error'), 'the scan must be accepted');

        // The whole LINE is admitted by one scan, so both seats on it are stamped.
        foreach ($seats as $id) {
            $this->assertNotNull(SeatingSeat::find($id)->checked_in_at, 'a scanned seat must show as arrived');
        }
    }

    public function test_the_console_and_the_printed_sheet_both_show_who_is_in(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->seatedEvent($role);
        [, $seats] = $this->sellTwo($role, $event);
        $args = ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)];

        SeatingSeat::whereKey($seats[0])->update(['checked_in_at' => now()]);

        $payload = $this->actingAs($owner)->getJson(route('box_office.state', $args))->assertOk()->json();
        $seatRows = collect($payload['levels'][0]['sections'][0]['seats'])->keyBy('id');

        $this->assertTrue($seatRows[$seats[0]]['arrived']);
        $this->assertFalse($seatRows[$seats[1]]['arrived'], 'a seat nobody has scanned is not arrived');
        // Still sold: arriving does not change what they bought.
        $this->assertSame('sold', $seatRows[$seats[0]]['state']);

        $rows = collect($this->actingAs($owner)
            ->get(route('box_office.report', $args))->assertOk()->viewData('rows'))->keyBy('seat');

        $this->assertTrue($rows['1']['arrived']);
        $this->assertFalse($rows['2']['arrived']);
    }

    public function test_the_door_can_find_somebody_by_seat_or_by_name(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->seatedEvent($role);
        [, $seats] = $this->sellTwo($role, $event);
        SeatingSeat::whereKey($seats[0])->update(['checked_in_at' => now()]);

        $url = route('checkin.search', ['event_id' => UrlUtils::encodeId($event->id)]);
        $date = $event->saleEventDateFromStartsAt();

        // By seat - what the person at the door is holding.
        $bySeat = $this->actingAs($owner)->getJson($url.'?q=C1&date='.$date)->assertOk()->json('results');
        $this->assertCount(1, $bySeat);
        $this->assertSame('Ned Flanders', $bySeat[0]['name']);
        $this->assertTrue($bySeat[0]['arrived']);

        // By name - when the scanner will not read their phone.
        $byName = $this->actingAs($owner)->getJson($url.'?q=flanders&date='.$date)->assertOk()->json('results');
        $this->assertCount(2, $byName, 'both of their seats');
        $this->assertSame([true, false], array_column($byName, 'arrived'));

        // Too short to be a search: this fires on every keystroke at a door with poor signal.
        $this->assertSame([], $this->actingAs($owner)->getJson($url.'?q=f&date='.$date)->assertOk()->json('results'));

        // A date that is not a date falls back to TONIGHT. It does not throw - it binds and matches
        // nothing - so the failure mode was a confident empty list at the door, which reads as
        // "not on the list". stats() has always run its date through Carbon; this did not.
        $this->assertCount(2, $this->actingAs($owner)->getJson($url.'?q=flanders&date[]=x')
            ->assertOk()->json('results'), 'an array date must fall back to tonight, not match nothing');
        $this->assertCount(2, $this->actingAs($owner)->getJson($url.'?q=flanders&date=not-a-date')
            ->assertOk()->json('results'));

        // A term carrying a LIKE metacharacter is matched literally, backslash included.
        $this->assertSame([], $this->actingAs($owner)->getJson($url.'?q=%25%25&date='.$date)->assertOk()->json('results'));
        $this->assertSame([], $this->actingAs($owner)->getJson($url.'?q=%5C%5C&date='.$date)->assertOk()->json('results'));
    }

    public function test_the_door_search_refuses_somebody_elses_event(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role);
        $this->sellTwo($role, $event);

        $this->actingAs($this->createOwner())->getJson(route('checkin.search', [
            'event_id' => UrlUtils::encodeId($event->id),
        ]).'?q=flanders')->assertForbidden();
    }

    /**
     * Restoring a snapshot of a night in progress must not empty the door's record of who is in.
     *
     * checked_in_at was absent from both halves of the backup, on a table whose stated purpose is
     * "who is sitting where" - so a restore mid-run silently un-admitted everybody already inside.
     */
    public function test_a_backup_carries_who_has_already_arrived(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->seatedEvent($role);
        [, $seats] = $this->sellTwo($role, $event);

        SeatingSeat::whereKey($seats[0])->update(['checked_in_at' => now()]);

        $svc = app(\App\Services\BackupService::class);
        $exportJob = \App\Models\BackupJob::create(['user_id' => $owner->id, 'type' => 'export', 'status' => 'processing']);
        $data = $svc->exportSchedules([$role->fresh()], false, $exportJob)['json'];

        $exported = collect($data['schedules'][0]['events'][0]['seating_maps'][0]['seats'] ?? [])
            ->filter(fn ($row) => ! empty($row['checked_in_at']));

        $this->assertCount(1, $exported, 'exactly the seat that was scanned in must carry a stamp');

        $importJob = \App\Models\BackupJob::create(['user_id' => $owner->id, 'type' => 'import', 'status' => 'processing']);
        $svc->importSchedules($data, [0], $owner->id, $importJob);

        $newRole = \App\Models\Role::where('user_id', $owner->id)->where('id', '!=', $role->id)->latest('id')->firstOrFail();
        $newEvent = \App\Models\Event::where('creator_role_id', $newRole->id)->latest('id')->firstOrFail();
        $newMap = \App\Models\EventSeatingMap::where('event_id', $newEvent->id)->firstOrFail();

        $arrived = SeatingSeat::where('event_seating_map_id', $newMap->id)->whereNotNull('checked_in_at')->count();
        $this->assertSame(1, $arrived, 'the arrival must survive the restore');
    }
}
