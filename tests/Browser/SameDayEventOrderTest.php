<?php

namespace Tests\Browser;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * The order of one day's events on the guest calendar, which is decided in the browser
 * (resources/views/role/partials/calendar.blade.php) and so cannot be reached from PHPUnit.
 *
 * A series' starts_at is its FIRST date, so the list (which compared the full local_starts_at)
 * and the month grid (which kept the server's starts_at order) put a weekly 10pm show that began
 * months ago ahead of that night's 8pm one-off - on nights with a series, and not on others.
 *
 * Every date is the schedule's own (New York): CI runs with APP_TIMEZONE=UTC, and the calendar's
 * "today" is the schedule's, so a UTC date would be a day off for four hours every night.
 */
class SameDayEventOrderTest extends DuskTestCase
{
    use DatabaseTruncation;

    private const TZ = 'America/New_York';

    private Role $role;

    private Carbon $today;

    private Carbon $night;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create(['email_verified_at' => now()]);

        $role = new Role;
        $role->subdomain = 'ordertest';
        $role->user_id = $user->id;
        $role->type = 'venue';
        $role->name = 'Order Test';
        $role->email = 'order@gmail.com';
        $role->timezone = self::TZ;
        $role->email_verified_at = now();
        $role->plan_type = 'enterprise';
        $role->plan_expires = now()->addYear()->format('Y-m-d');
        $role->save();
        $role->users()->attach($user->id, ['level' => 'owner']);
        $this->role = $role->fresh();

        $this->today = Carbon::now(self::TZ)->startOfDay();
        $this->night = $this->today->copy()->addDays(4);

        // The night: two series begun on different dates, and a one-off between them.
        $this->makeEvent('DJ Night', $this->night->copy()->subWeeks(8), '22:00', ['days_of_week' => $this->weekdayOnly($this->night), 'recurring_frequency' => 'weekly']);
        $this->makeEvent('Opener', $this->night->copy()->subWeek(), '19:00', ['days_of_week' => $this->weekdayOnly($this->night), 'recurring_frequency' => 'weekly']);
        $this->makeEvent('Live Music', $this->night, '20:00');

        // Today: a festival under way since yesterday evening leads the day's own 10am event.
        $this->makeEvent('Festival', $this->today->copy()->subDay(), '18:00', ['duration' => 48]);
        $this->makeEvent('Brunch', $this->today, '10:00');
    }

    /** Only $day's weekday set, Sunday first - the Event::matchesFrequency() convention. */
    private function weekdayOnly(Carbon $day): string
    {
        return str_pad(str_repeat('0', $day->dayOfWeek).'1', 7, '0');
    }

    private function makeEvent(string $name, Carbon $day, string $time, array $attrs = []): void
    {
        $event = new Event;
        $event->user_id = $this->role->user_id;
        // Without it the event's zone falls back to the app's (UTC) and 22:00 reads as 18:00.
        $event->creator_role_id = $this->role->id;
        $event->name = $name;
        $event->slug = Str::slug($name).'-'.strtolower(Str::random(4));
        $event->starts_at = Carbon::parse($day->format('Y-m-d').' '.$time, self::TZ)->utc()->format('Y-m-d H:i:s');
        $event->duration = 2;
        foreach ($attrs as $key => $value) {
            $event->{$key} = $value;
        }
        $event->save();

        $event->roles()->attach($this->role->id, ['is_accepted' => true]);
    }

    /** The names the list layout shows under $date, in order, and their clock times. */
    private function listDay(Browser $browser, Carbon $day): array
    {
        return $browser->script(sprintf(
            "return window.calendarVueApp.allMobileOccurrences.filter(e => e.occurrenceDate === '%s').map(e => [e.name, (e.local_starts_at || '').slice(11, 16)]);",
            $day->format('Y-m-d')
        ))[0];
    }

    /**
     * Opens the month grid on $day's month and returns that day's names, from Vue and from the
     * page: the day's own lines in order (a name is read whole from data-full, since a long one
     * is cut on screen and the line holds a time too), the bars of the month (an event over
     * several days is one bar, not a line of each day), and what the day says it does not show.
     */
    private function gridDay(Browser $browser, Carbon $day): array
    {
        $date = $day->format('Y-m-d');

        $browser->visit('/ordertest?layout=calendar&month='.$day->month.'&year='.$day->year)
            ->waitUntil("window.calendarVueApp && window.calendarVueApp.eventsMap && window.calendarVueApp.eventsMap['{$date}'] && !window.calendarVueApp.isLoadingEvents", 15);

        $vue = $browser->script("return window.calendarVueApp.getEventsForDate('{$date}').map(e => e.name);")[0];
        $dom = $browser->script("
            const cell = document.querySelector('[data-month] .gk-cal-day[data-date=\"{$date}\"]');
            if (!cell) return null;
            return {
                lines: Array.from(cell.querySelectorAll('.gk-cal-ev .gk-cal-nm')).map(n => n.dataset.full),
                bars: Array.from(document.querySelectorAll('[data-month] .gk-cal-span')).map(a => a.getAttribute('aria-label')),
                more: (cell.querySelector('.gk-cal-more') || {}).textContent || '',
            };
        ")[0];

        return [$vue, $dom];
    }

    public function test_the_list_orders_each_day_by_start_time(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(1440, 1000)
                ->visit('/ordertest?layout=list')
                ->waitForText('Live Music', 15);

            $this->assertSame(
                [['Opener', '19:00'], ['Live Music', '20:00'], ['DJ Night', '22:00']],
                $this->listDay($browser, $this->night),
                'A series begun weeks ago must not jump ahead of that night\'s earlier events'
            );

            $this->assertSame(
                ['Festival', 'Brunch'],
                array_column($this->listDay($browser, $this->today), 0),
                'A multi-day event already running leads the day, whatever time it began'
            );
        });
    }

    public function test_the_month_grid_orders_each_day_by_start_time(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(1440, 1000);

            [$vue, $dom] = $this->gridDay($browser, $this->night);
            $this->assertSame(['Opener', 'Live Music', 'DJ Night'], $vue);
            $this->assertSame(['Opener', 'Live Music', 'DJ Night'], $dom['lines'], 'The cell renders in the order getEventsForDate() returns');

            [$vue, $dom] = $this->gridDay($browser, $this->today);
            $this->assertSame(['Festival', 'Brunch'], $vue);
            // The festival is a bar across its days, above each day's own events. The brunch is
            // today's one line until it is over (noon in New York), and then stands behind
            // "1 earlier today": what is over today gives its place to what is not.
            $this->assertContains('Festival', $dom['bars']);
            $this->assertTrue(
                $dom['lines'] === ['Brunch'] || ($dom['lines'] === [] && str_contains($dom['more'], '1')),
                'Brunch is today\'s line, or behind "1 earlier today": '.json_encode($dom)
            );
        });
    }
}
