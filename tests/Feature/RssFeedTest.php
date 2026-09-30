<?php

namespace Tests\Feature;

use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The schedule's RSS feed (FeedController::rssFeed): one item per one-off event and the next
 * date of each running series, soonest first, capped at 50.
 *
 * A series' starts_at is its FIRST date, in the past for any series that has been running a
 * while, so ordering the items by it listed every series ahead of every one-off, on any day, and
 * let the series crowd one-offs out of the cap.
 */
class RssFeedTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private Role $role;

    private Carbon $today;

    protected function setUp(): void
    {
        parent::setUp();

        $this->role = $this->createRole($this->createOwner(), 'venue');
        $this->today = Carbon::now('America/New_York')->startOfDay();
    }

    /** A New York wall-clock time as the UTC starts_at the schedule stores. */
    private function at(Carbon $day, string $time): string
    {
        return Carbon::parse($day->format('Y-m-d').' '.$time, 'America/New_York')->utc()->format('Y-m-d H:i:s');
    }

    private function oneOff(string $name, Carbon $day, string $time, array $attrs = []): void
    {
        $this->createEvent($this->role, array_merge(['name' => $name, 'starts_at' => $this->at($day, $time), 'creator_role_id' => $this->role->id], $attrs));
    }

    /** A weekly series on $day's weekday, first held eight weeks before it. */
    private function weekly(string $name, Carbon $day, string $time, array $attrs = []): void
    {
        $this->oneOff($name, $day->copy()->subWeeks(8), $time, array_merge([
            'days_of_week' => str_pad(str_repeat('0', $day->dayOfWeek).'1', 7, '0'),
            'recurring_frequency' => 'weekly',
        ], $attrs));
    }

    /** Each item's fixture name, in feed order. Titles read ":role at :venue" when there is one. */
    private function feedNames(array $names): array
    {
        $response = $this->get(route('feed.rss', ['subdomain' => $this->role->subdomain]));
        $response->assertOk();

        $titles = [];
        foreach (simplexml_load_string($response->getContent())->channel->item as $item) {
            $titles[] = (string) $item->title;
        }

        return array_map(function (string $title) use ($names) {
            foreach ($names as $name) {
                if (str_contains($title, $name)) {
                    return $name;
                }
            }

            return $title;
        }, $titles);
    }

    public function test_items_are_in_start_order_not_series_first(): void
    {
        $night = $this->today->copy()->addDays(3);

        $this->weekly('DJ Night', $night, '22:00');
        $this->oneOff('Live Music', $night, '20:00');
        $this->oneOff('Tomorrow Show', $this->today->copy()->addDay(), '20:00');
        // Under way since yesterday: still listed, and first.
        $this->oneOff('Festival', $this->today->copy()->subDay(), '18:00', ['duration' => 72]);
        // Ended last month: nothing left to list.
        $this->weekly('Old Residency', $night, '21:00', [
            'recurring_end_type' => 'on_date',
            'recurring_end_value' => $this->today->copy()->subMonth()->format('Y-m-d'),
        ]);

        $this->assertSame(
            ['Festival', 'Tomorrow Show', 'Live Music', 'DJ Night'],
            $this->feedNames(['Festival', 'Tomorrow Show', 'Live Music', 'DJ Night', 'Old Residency'])
        );
    }

    /** The cap keeps the 50 soonest, not every series and then whatever one-offs fit. */
    public function test_the_cap_keeps_the_soonest_fifty(): void
    {
        $this->weekly('DJ Night', $this->today->copy()->addDays(3), '22:00');

        $tomorrow = $this->today->copy()->addDay();
        for ($i = 0; $i < 50; $i++) {
            $this->oneOff(sprintf('Slot %02d', $i), $tomorrow, Carbon::createFromTime(8, 0)->addMinutes(10 * $i)->format('H:i'));
        }

        $names = $this->feedNames(['DJ Night', 'Slot']);

        $this->assertCount(50, $names);
        $this->assertNotContains('DJ Night', $names);
    }
}
