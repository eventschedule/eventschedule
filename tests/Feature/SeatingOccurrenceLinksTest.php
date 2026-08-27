<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\SeatingLevel;
use App\Models\SeatingPlan;
use App\Models\SeatingSeat;
use App\Models\SeatingSection;
use App\Repos\EventRepo;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Occurrence links on the two box office screens.
 *
 * Every box office route resolves its own occurrence and, given no date, falls back to
 * Event::saleEventDateFromStartsAt() - the series anchor. The console and the report both built
 * their links from ['subdomain', 'hash'] alone, so on a run the console's Report link, the report's
 * back link and the report's CSV export all silently jumped to the first night. Reaching night
 * twelve by hand and exporting it gave you night one's seats.
 */
class SeatingOccurrenceLinksTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function seatedRun(Role $role): Event
    {
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Small House']);
        $level = SeatingLevel::create(['seating_plan_id' => $plan->id, 'name' => 'Ground']);
        $section = SeatingSection::create([
            'seating_plan_id' => $plan->id, 'seating_level_id' => $level->id,
            'name' => 'Stalls', 'band' => 'Stalls', 'kind' => 'seated', 'position' => 0,
        ]);
        for ($n = 1; $n <= 4; $n++) {
            SeatingSeat::create([
                'seating_plan_id' => $plan->id, 'seating_section_id' => $section->id,
                'row_label' => 'A', 'row_position' => 1, 'seat_label' => (string) $n,
                'position' => $n, 'x' => $n * 26,
            ]);
        }

        $request = Request::create('/', 'POST', [
            'name' => 'Nightly Show',
            'starts_at' => now()->addMonth()->setTime(19, 30)->format('Y-m-d H:i:s'),
            'tickets_enabled' => 1, 'payment_method' => 'cash', 'ticket_currency_code' => 'USD',
            'creator_role_id' => $role->id,
            'seating_plan_id' => $plan->id,
            'tickets' => [
                ['type' => 'Stalls', 'price' => 40, 'quantity' => 999, 'seating_band' => 'Stalls'],
            ],
        ]);
        $request->setUserResolver(fn () => $role->user);
        $event = app(EventRepo::class)->saveEvent($role, $request)->fresh();
        $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);

        // Set on the model rather than in the payload: EventRepo reads recurrence off the GLOBAL
        // request() helper, not the Request it is handed, so a direct saveEvent() call cannot post
        // schedule_type. Nightly, so every date of the run is a real occurrence.
        $event->days_of_week = '1111111';
        $event->recurring_frequency = 'daily';
        $event->save();

        return $event->fresh();
    }

    /** A night of the run that is NOT the anchor the links used to fall back to. */
    private function laterNight(Event $event): string
    {
        $anchor = $event->saleEventDateFromStartsAt();
        $later = \Carbon\Carbon::parse($anchor)->addDays(12)->format('Y-m-d');
        $this->assertNotSame($anchor, $later);

        return $later;
    }

    public function test_the_console_links_to_the_report_for_the_night_it_is_showing(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->seatedRun($role);
        $night = $this->laterNight($event);

        $props = $this->consoleProps($owner, $role, $event, $night);

        $this->assertSame(
            route('box_office.report', [
                'subdomain' => $role->subdomain,
                'hash' => UrlUtils::encodeId($event->id),
                'date' => $night,
            ], false),
            $props['reportUrl']
        );
    }

    /** The console ships its config as JSON in a data attribute; assert on that, not on the markup. */
    private function consoleProps($owner, Role $role, Event $event, string $night): array
    {
        $html = $this->actingAs($owner)->get(route('box_office.show', [
            'subdomain' => $role->subdomain,
            'hash' => UrlUtils::encodeId($event->id),
            'date' => $night,
        ]))->assertOk()->getContent();

        preg_match('/data-props="(.*?)"><\/div>/s', $html, $m);
        $props = json_decode(html_entity_decode($m[1] ?? '', ENT_QUOTES), true);
        $this->assertIsArray($props, 'the console props must be readable');

        return $props;
    }

    public function test_the_report_exports_and_links_back_to_the_night_it_is_showing(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->seatedRun($role);
        $night = $this->laterNight($event);

        $args = [
            'subdomain' => $role->subdomain,
            'hash' => UrlUtils::encodeId($event->id),
            'date' => $night,
        ];

        $response = $this->actingAs($owner)->get(route('box_office.report', $args))->assertOk();

        // The CSV is the one that actually loses data: the wrong night downloads as though it were
        // this one, with no hint on the file that it is not.
        $response->assertSee(e(route('box_office.report_csv', $args)), false);
        $response->assertSee(e(route('box_office.show', $args)), false);
    }

    public function test_the_console_opens_on_tonight_rather_than_the_night_the_run_began(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->seatedRun($role);

        // Push the run's start into the past, so the series anchor - what
        // SeatingMapService::resolveDate() falls back to - is a night that has already happened.
        $event->starts_at = now()->subDays(10)->setTime(19, 30)->format('Y-m-d H:i:s');
        $event->save();
        $event = $event->fresh();

        $anchor = $event->saleEventDateFromStartsAt();
        $this->assertLessThan($event->scheduleToday(), $anchor, 'the fixture must have a past anchor');

        $html = $this->actingAs($owner)->get(route('box_office.show', [
            'subdomain' => $role->subdomain,
            'hash' => UrlUtils::encodeId($event->id),
        ]))->assertOk()->getContent();

        preg_match('/data-props="(.*?)"><\/div>/s', $html, $m);
        $props = json_decode(html_entity_decode($m[1] ?? '', ENT_QUOTES), true);

        $this->assertNotSame($anchor, $props['date'], 'the console must not open on a finished night');
        $this->assertSame($event->scheduleToday(), $props['date']);
    }

    public function test_every_admin_screen_offers_the_other_nights_of_the_run(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->seatedRun($role);
        $night = $this->laterNight($event);

        $args = [
            'subdomain' => $role->subdomain,
            'hash' => UrlUtils::encodeId($event->id),
            'date' => $night,
        ];

        foreach (['box_office.show', 'box_office.report', 'seating.occurrence_design'] as $route) {
            $html = $this->actingAs($owner)->get(route($route, $args))->assertOk()->getContent();

            $this->assertStringContainsString('name="date"', $html, $route.' has no date picker');
            $this->assertStringContainsString('value="'.$night.'" selected', $html, $route.' does not mark the night it is showing');
            $this->assertGreaterThan(1, substr_count($html, '<option value="20'), $route.' offers only one night');
        }
    }

    public function test_a_one_time_event_gets_no_date_picker(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->seatedRun($role);

        // Back to a single night: there is nothing to choose between, so the control is noise.
        $event->days_of_week = null;
        $event->recurring_frequency = null;
        $event->save();

        $html = $this->actingAs($owner)->get(route('box_office.show', [
            'subdomain' => $role->subdomain,
            'hash' => UrlUtils::encodeId($event->id),
        ]))->assertOk()->getContent();

        $this->assertStringNotContainsString('name="date"', $html);
    }

    public function test_the_state_url_stays_bare_so_the_poll_can_append_its_own_query(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->seatedRun($role);
        $night = $this->laterNight($event);

        $props = $this->consoleProps($owner, $role, $event, $night);

        // seat-map-store appends "?event_id=..&date=.." to this unconditionally. A query string here
        // makes that a second "?" and the whole console stops loading.
        $this->assertStringNotContainsString('?', $props['stateUrl']);
        $this->assertSame($night, $props['date']);
    }
}
