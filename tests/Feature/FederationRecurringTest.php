<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Setting;
use App\Services\FederationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Recurring events are resolved into concrete dates HERE, using the app's own
 * Event::matchesDate(), rather than shipping recurrence rules for the nexus to
 * re-derive. These tests pin that behaviour, and the traffic budget that depends on it.
 */
class FederationRecurringTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const EVENTS_ENDPOINT = 'https://eventschedule.com/api/federation/events';

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.is_nexus' => false]);
        Setting::set('federation_enabled', '1');
        Http::fake([self::EVENTS_ENDPOINT => Http::response(['accepted' => 1, 'skipped' => 0, 'status' => 'approved'])]);
    }

    private function service(): FederationService
    {
        return app(FederationService::class);
    }

    private function makeEvent(array $attrs = []): Event
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        return $this->createEvent($role, array_merge([
            'name' => 'Weekly Session',
            'flyer_image_url' => 'flyer.jpg',
            'creator_role_id' => $role->id,
        ], $attrs));
    }

    private function sentItems(): array
    {
        $items = [];

        Http::assertSent(function ($request) use (&$items) {
            if ($request->url() === self::EVENTS_ENDPOINT) {
                $items = array_merge($items, json_decode($request->body(), true)['items']);
            }

            return true;
        });

        return $items;
    }

    public function test_a_recurring_event_sends_several_resolved_dates_not_a_rule(): void
    {
        $this->makeEvent([
            'days_of_week' => '1111111',
            'starts_at' => Carbon::now()->subDay()->setTime(19, 0)->format('Y-m-d H:i:s'),
        ]);

        $this->service()->push();
        $item = $this->sentItems()[0];

        $this->assertCount(FederationService::OCCURRENCES_AHEAD, $item['occurrences']);
        // Recurrence rules are deliberately never sent.
        $this->assertArrayNotHasKey('days_of_week', $item);
        $this->assertArrayNotHasKey('recurring_frequency', $item);
    }

    /**
     * The traffic budget: a weekly event must cost one push a week, not one an hour.
     */
    public function test_an_unchanged_recurring_event_is_not_pushed_again(): void
    {
        $this->makeEvent([
            'days_of_week' => '1111111',
            'starts_at' => Carbon::now()->subDay()->setTime(19, 0)->format('Y-m-d H:i:s'),
        ]);

        $this->assertSame(1, $this->service()->push()['pushed']);
        $this->assertSame(0, $this->service()->push()['pushed']);
        $this->assertSame(0, $this->service()->push()['pushed']);
    }

    public function test_a_changed_recurrence_is_pushed_again(): void
    {
        $event = $this->makeEvent([
            'days_of_week' => '1111111',
            'starts_at' => Carbon::now()->subDay()->setTime(19, 0)->format('Y-m-d H:i:s'),
        ]);

        $this->assertSame(1, $this->service()->push()['pushed']);

        // Narrow it to a single weekday: the resolved dates move, so the hash changes.
        $event->refresh();
        $event->days_of_week = '0000010';
        $event->save();

        $this->assertSame(1, $this->service()->push()['pushed']);
    }

    public function test_a_one_off_event_stays_watermarked_and_is_not_resent(): void
    {
        $event = $this->makeEvent(['starts_at' => Carbon::now()->addWeek()->setTime(12, 0)->format('Y-m-d H:i:s')]);

        $this->assertSame(1, $this->service()->push()['pushed']);
        $this->assertNotNull($event->fresh()->federated_at);
        $this->assertSame(0, $this->service()->push()['pushed']);
    }

    /**
     * duration is a float in hours and there is no stored end column, so addHours()
     * would truncate and a 90-minute event would end after 60.
     */
    public function test_ends_at_is_derived_in_minutes_not_truncated_hours(): void
    {
        $this->makeEvent([
            'starts_at' => Carbon::now()->addWeek()->setTime(12, 0)->format('Y-m-d H:i:s'),
            'duration' => 1.5,
        ]);

        $this->service()->push();
        $item = $this->sentItems()[0];

        $minutes = Carbon::parse($item['starts_at'])->diffInMinutes(Carbon::parse($item['ends_at']));
        $this->assertSame(90, (int) $minutes);
    }

    public function test_excluded_dates_are_honoured_when_resolving_occurrences(): void
    {
        $start = Carbon::now()->subDay()->setTime(19, 0);
        $event = $this->makeEvent([
            'days_of_week' => '1111111',
            'starts_at' => $start->format('Y-m-d H:i:s'),
        ]);

        $tomorrow = Carbon::now($event->scheduleTimezone())->addDay()->format('Y-m-d');
        $event->refresh();
        $event->recurring_exclude_dates = [$tomorrow];
        $event->save();

        $occurrences = $this->service()->resolveOccurrences($event->fresh());

        foreach ($occurrences as $occurrence) {
            $local = Carbon::parse($occurrence)->setTimezone($event->scheduleTimezone())->format('Y-m-d');
            $this->assertNotSame($tomorrow, $local, 'An excluded date was federated.');
        }
    }
}
