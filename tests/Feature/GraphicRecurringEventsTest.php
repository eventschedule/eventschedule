<?php

namespace Tests\Feature;

use App\Http\Controllers\GraphicController;
use App\Mail\GraphicEmail;
use App\Models\Event;
use App\Models\Role;
use App\Services\GraphicEmailService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The Events graphic lists a recurring series that began in the past.
 *
 * Its events used to be one query on `starts_at >= now`, and a series' starts_at is its FIRST
 * date, so a weekly night begun last month was on no image and in no caption. Each series is now
 * listed once, under its next occurrence in the schedule's timezone, and that occurrence's date is
 * the one the caption prints (GraphicController::upcomingGraphicEvents()).
 */
class GraphicRecurringEventsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** A weekly series on one weekday, begun four weeks before its next date, three days out. */
    private function runningSeries(Role $role, array $attrs = []): array
    {
        $next = Carbon::now($role->timezone)->startOfDay()->addDays(3);
        $first = $next->copy()->subWeeks(4)->setTime(19, 0);
        $days = str_repeat('0', 7);
        $days[$next->dayOfWeek] = '1';

        $event = $this->createRecurringEvent($role, array_merge([
            'name' => 'Weekly Quiz',
            'starts_at' => $first->copy()->setTimezone('UTC')->format('Y-m-d H:i:s'),
            'days_of_week' => $days,
            'creator_role_id' => $role->id,
        ], $attrs));

        return [$event, $next];
    }

    private function oneOff(Role $role, string $name, int $daysAhead, array $attrs = []): Event
    {
        return $this->createEvent($role, array_merge([
            'name' => $name,
            'starts_at' => Carbon::now($role->timezone)->startOfDay()->addDays($daysAhead)->setTime(12, 0)->setTimezone('UTC')->format('Y-m-d H:i:s'),
            'creator_role_id' => $role->id,
        ], $attrs));
    }

    private function dataUrl(Role $role, string $query): string
    {
        return route('event.generate_graphic_data', ['subdomain' => $role->subdomain]).'?'.$query;
    }

    public function test_a_series_begun_last_month_is_in_the_caption_under_its_next_date(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        [$series, $next] = $this->runningSeries($role);
        $this->oneOff($role, 'Early Show', 1);
        $this->oneOff($role, 'Late Show', 5);

        $text = $this->actingAs($owner)
            ->get($this->dataUrl($role, 'type=text&text_template='.urlencode('{event_name} {date_full_dmy}')))
            ->assertOk()
            ->json('text');

        $this->assertStringContainsString('Weekly Quiz '.$next->format('d/m/Y'), $text);
        // Once, not once per week, and never under the date it began on.
        $this->assertSame(1, substr_count($text, 'Weekly Quiz'));
        $this->assertStringNotContainsString($next->copy()->subWeeks(4)->format('d/m/Y'), $text);
        // In date order among the one-off events.
        $this->assertLessThan(strpos($text, 'Weekly Quiz'), strpos($text, 'Early Show'));
        $this->assertLessThan(strpos($text, 'Late Show'), strpos($text, 'Weekly Quiz'));
    }

    public function test_exclude_recurring_still_leaves_it_out(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $this->runningSeries($role);
        $this->oneOff($role, 'Early Show', 1);

        $text = $this->actingAs($owner)->get($this->dataUrl($role, 'type=text&exclude_recurring=1'))->assertOk()->json('text');

        $this->assertStringContainsString('Early Show', $text);
        $this->assertStringNotContainsString('Weekly Quiz', $text);
    }

    public function test_a_running_series_with_a_flyer_makes_the_image(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $this->runningSeries($role, ['flyer_image_url' => 'quiz.jpg']);

        $response = $this->actingAs($owner)->get($this->dataUrl($role, 'type=image'))->assertOk();

        // It used to answer "no events found".
        $this->assertNull($response->json('error'));
        $this->assertNotNull($response->json('image'));
    }

    public function test_the_scheduled_email_lists_it_under_its_next_date(): void
    {
        Mail::fake();
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $role->graphic_settings = ['text_template' => '{event_name} {date_full_dmy}'];
        $role->save();
        [$series, $next] = $this->runningSeries($role, ['flyer_image_url' => 'quiz.jpg']);

        // It used to find nothing to send.
        $this->assertTrue(app(GraphicEmailService::class)->sendGraphicEmail($role->fresh(), 'owner@gmail.com'));

        Mail::assertSent(GraphicEmail::class, fn (GraphicEmail $mail) => str_contains(
            (fn () => $this->eventText)->call($mail),
            'Weekly Quiz '.$next->format('d/m/Y')
        ));
    }

    public function test_the_listed_copy_carries_its_occurrence_and_the_event_is_untouched(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        [$series, $next] = $this->runningSeries($role, ['flyer_image_url' => 'quiz.jpg']);

        $listed = GraphicController::upcomingGraphicEvents($role, false, true, 5);

        $this->assertCount(1, $listed);
        $this->assertSame($series->id, $listed->first()->id);
        $this->assertSame($next->format('Y-m-d'), $listed->first()->getStartDateTime(null, true)->format('Y-m-d'));
        $this->assertSame('19:00', $listed->first()->getStartDateTime(null, true)->format('H:i'));
        $this->assertSame($series->starts_at, Event::find($series->id)->starts_at);
    }

    public function test_series_moved_past_today_do_not_crowd_out_an_event_beyond_the_first_rows(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        Carbon::setTestNow(Carbon::now($role->timezone)->startOfDay()->setTime(12, 0)->setTimezone('UTC'));

        try {
            // Four daily classes whose 8am today has begun, so each moves to tomorrow; the
            // evening show today sits below all four in date order, past the rows fetched for
            // a limit of two.
            $start = Carbon::now($role->timezone)->subWeeks(3)->setTime(8, 0)->setTimezone('UTC')->format('Y-m-d H:i:s');
            foreach (range(1, 4) as $n) {
                $this->createRecurringEvent($role, [
                    'name' => 'Class '.$n,
                    'starts_at' => $start,
                    'days_of_week' => '1111111',
                    'creator_role_id' => $role->id,
                ]);
            }
            $show = $this->createEvent($role, [
                'name' => 'Evening Show',
                'starts_at' => Carbon::now($role->timezone)->setTime(18, 0)->setTimezone('UTC')->format('Y-m-d H:i:s'),
                'creator_role_id' => $role->id,
            ]);

            $listed = GraphicController::upcomingGraphicEvents($role, false, false, 2);

            $this->assertCount(2, $listed);
            $this->assertSame($show->id, $listed->first()->id, 'the show tonight comes before classes moved to tomorrow');
        } finally {
            Carbon::setTestNow();
        }
    }
}
