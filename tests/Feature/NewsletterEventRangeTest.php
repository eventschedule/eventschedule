<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Newsletter;
use App\Models\Role;
use App\Services\NewsletterService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * An Events block with a date range (issue #134): "the rest of the month it is sent in" and its
 * siblings, read in the schedule's timezone, as of the moment the send was claimed, and {month}.
 */
class NewsletterEventRangeTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function role(string $zone = 'Europe/Berlin', array $attrs = []): Role
    {
        return $this->createRole($this->createOwner(), 'venue', $attrs + ['timezone' => $zone]);
    }

    /** A one-off event starting at $local in its schedule's clock. */
    private function eventAt(Role $role, string $name, string $local, array $attrs = []): Event
    {
        $zone = $role->timezone;
        $startsAt = strlen($local) === 10
            ? $local
            : Carbon::createFromFormat('Y-m-d H:i', $local, $zone)->setTimezone('UTC')->format('Y-m-d H:i:s');

        return $this->createEvent($role, $attrs + ['name' => $name, 'starts_at' => $startsAt, 'creator_role_id' => $role->id]);
    }

    private function newsletter(Role $role, string $range = 'this_month', array $attrs = []): Newsletter
    {
        return new Newsletter($attrs + [
            'role_id' => $role->id,
            'user_id' => $role->user_id,
            'subject' => 'Events',
            'status' => 'draft',
            'template' => 'modern',
            'type' => 'schedule',
            'blocks' => [['id' => 'e', 'type' => 'events', 'data' => ['layout' => 'list', 'useAllEvents' => true, 'range' => $range, 'eventIds' => []]]],
        ]);
    }

    /** @return array<int, string> the names the Events block lists, in order */
    private function listed(Newsletter $newsletter): array
    {
        $newsletter->setRelation('role', $newsletter->role ?? Role::find($newsletter->role_id));
        $block = collect(app(NewsletterService::class)->processBlocks($newsletter))->firstWhere('type', 'events');

        return collect($block['data']['resolvedEvents'])->pluck('name')->all();
    }

    private function berlin(string $at): Carbon
    {
        return Carbon::createFromFormat('Y-m-d H:i', $at, 'Europe/Berlin');
    }

    public function test_the_rest_of_the_month_ends_at_midnight_on_the_schedules_clock(): void
    {
        $role = $this->role();
        $this->eventAt($role, 'Mid month', '2026-11-10 20:00');
        // 23:30 in Berlin on the 30th is 22:30 UTC, still November; 00:30 on Dec 1 is 23:30 UTC on
        // Nov 30, which a UTC reading would list.
        $this->eventAt($role, 'Last night', '2026-11-30 23:30');
        $this->eventAt($role, 'Next month', '2026-12-01 00:30');

        $this->travelTo($this->berlin('2026-11-01 09:00'));

        $this->assertSame(['Mid month', 'Last night'], $this->listed($this->newsletter($role)));
    }

    public function test_the_following_month_is_not_crowded_out_by_this_months_events(): void
    {
        $role = $this->role();
        foreach (range(1, 28) as $day) {
            $this->eventAt($role, 'November '.$day, sprintf('2026-11-%02d 20:00', $day + 1));
        }
        $this->eventAt($role, 'December show', '2026-12-05 20:00');

        $this->travelTo($this->berlin('2026-11-01 09:00'));

        $this->assertSame(['December show'], $this->listed($this->newsletter($role, 'next_month')));
    }

    public function test_a_series_that_began_last_month_is_listed_under_its_first_date_in_the_window(): void
    {
        $role = $this->role();
        // Every Tuesday from October on.
        $this->createRecurringEvent($role, [
            'name' => 'Tuesday jam',
            'days_of_week' => '0010000',
            'starts_at' => Carbon::createFromFormat('Y-m-d H:i', '2026-10-06 19:00', 'Europe/Berlin')->setTimezone('UTC')->format('Y-m-d H:i:s'),
            'creator_role_id' => $role->id,
        ]);

        $this->travelTo($this->berlin('2026-11-01 09:00'));
        $newsletter = $this->newsletter($role, 'next_month');
        $newsletter->setRelation('role', $role);
        $block = collect(app(NewsletterService::class)->processBlocks($newsletter))->firstWhere('type', 'events');
        $row = $block['data']['resolvedEvents'][0] ?? null;

        $this->assertNotNull($row);
        $this->assertSame('Tuesday jam', $row['name']);
        // The first Tuesday of December 2026 is the 1st.
        $this->assertSame('1', $row['day']);
        $this->assertSame(1, count($block['data']['resolvedEvents']));
    }

    public function test_a_long_event_running_into_the_month_counts_and_a_finished_one_does_not(): void
    {
        $role = $this->role();
        $this->eventAt($role, 'Weekend festival', '2026-11-29 12:00', ['duration' => 72]);
        $this->eventAt($role, 'Over before December', '2026-11-29 12:00', ['duration' => 30]);

        $this->travelTo($this->berlin('2026-11-01 09:00'));

        $this->assertSame(['Weekend festival'], $this->listed($this->newsletter($role, 'next_month')));
    }

    public function test_the_month_edges_are_the_schedules_west_of_greenwich(): void
    {
        $role = $this->role('America/Los_Angeles');
        // Midnight on Dec 1 in Los Angeles is 08:00 UTC; 23:30 on Nov 30 is 07:30 UTC on Dec 1,
        // which a UTC reading of the month would put in December.
        $this->eventAt($role, 'December first', '2026-12-01 00:00');
        $this->eventAt($role, 'November last', '2026-11-30 23:30');

        $this->travelTo(Carbon::create(2026, 11, 2, 9, 0, 0, 'America/Los_Angeles'));

        $this->assertSame(['December first'], $this->listed($this->newsletter($role, 'next_month')));
    }

    public function test_a_block_saved_before_ranges_lists_every_upcoming_event_as_it_did(): void
    {
        $role = $this->role();
        $this->eventAt($role, 'Soon', '2026-11-10 20:00');
        $this->eventAt($role, 'Next year', '2027-03-10 20:00');

        $this->travelTo($this->berlin('2026-11-01 09:00'));
        $newsletter = $this->newsletter($role);
        $newsletter->blocks = [['id' => 'e', 'type' => 'events', 'data' => ['layout' => 'cards', 'useAllEvents' => true, 'eventIds' => []]]];

        $this->assertSame(['Soon', 'Next year'], $this->listed($newsletter));
    }

    public function test_every_batch_of_one_send_lists_the_events_as_of_its_claim(): void
    {
        $role = $this->role();
        $this->eventAt($role, 'Early November', '2026-11-03 20:00');
        $this->eventAt($role, 'Late November', '2026-11-25 20:00');

        $newsletter = $this->newsletter($role);
        $newsletter->events_as_of = $this->berlin('2026-11-01 09:00');

        // A batch run three weeks later, on recovery, still reads the month from the claim.
        $this->travelTo($this->berlin('2026-11-20 09:00'));

        $this->assertSame(['Early November', 'Late November'], $this->listed($newsletter));
    }

    public function test_a_range_lists_at_most_its_cap(): void
    {
        $role = $this->role();
        foreach (range(1, NewsletterService::RANGE_EVENT_LIMIT + 3) as $i) {
            $this->eventAt($role, 'Show '.$i, Carbon::create(2026, 11, 2, 10, 0, 0, 'Europe/Berlin')->addHours($i * 5)->format('Y-m-d H:i'));
        }

        $this->travelTo($this->berlin('2026-11-01 09:00'));

        $this->assertCount(NewsletterService::RANGE_EVENT_LIMIT, $this->listed($this->newsletter($role)));
    }

    public function test_month_is_filled_in_the_subject_preview_text_and_blocks_in_the_schedules_language(): void
    {
        $role = $this->role('Europe/Paris', ['language_code' => 'fr']);
        $this->travelTo(Carbon::create(2026, 11, 30, 23, 30, 0, 'Europe/Paris'));

        $newsletter = $this->newsletter($role, 'all', [
            'subject' => 'Au programme en {month}',
            'style_settings' => ['previewText' => 'Tout {month}'],
            'blocks' => [
                ['id' => 'h', 'type' => 'heading', 'data' => ['text' => 'Agenda de {month}']],
                ['id' => 't', 'type' => 'text', 'data' => ['content' => 'Voici **{month}**.']],
            ],
        ]);
        $newsletter->setRelation('role', $role);
        $service = app(NewsletterService::class);

        // 23:30 in Paris on Nov 30 is still November there, whatever UTC says.
        $this->assertSame('Au programme en novembre 2026', $service->subjectFor($newsletter));
        $this->assertSame('Tout novembre 2026', $service->previewTextFor($newsletter));

        $blocks = collect($service->processBlocks($newsletter))->keyBy('id');
        $this->assertSame('Agenda de novembre 2026', $blocks['h']['data']['text']);
        $this->assertStringContainsString('novembre 2026', $blocks['t']['data']['contentHtml']);

        $html = $service->renderHtml($newsletter);
        $this->assertStringContainsString('<title>Au programme en novembre 2026</title>', $html);
        $this->assertStringNotContainsString('{month}', $html);
        // The stored subject is untouched: the batch job saves the model it holds.
        $this->assertSame('Au programme en {month}', $newsletter->subject);
    }

    public function test_the_preview_of_a_repeating_newsletter_reads_its_next_send(): void
    {
        $role = $this->role();
        $this->eventAt($role, 'This November', '2026-11-20 20:00');
        $this->eventAt($role, 'Next December', '2026-12-10 20:00');

        $this->travelTo($this->berlin('2026-11-15 12:00'));
        $newsletter = $this->newsletter($role, 'this_month', ['status' => 'scheduled', 'scheduled_at' => $this->berlin('2026-12-01 09:00')->setTimezone('UTC')]);

        $this->assertSame(['Next December'], $this->listed($newsletter));
    }

    public function test_month_names_the_month_the_events_are_in_and_opens_with_a_capital(): void
    {
        $role = $this->role('Europe/Madrid', ['language_code' => 'es']);
        $this->travelTo(Carbon::create(2026, 11, 25, 9, 0, 0, 'Europe/Madrid'));
        $service = app(NewsletterService::class);

        // Sent on the 25th over the whole of December: December is the month it is about.
        $following = $this->newsletter($role, 'next_month', ['subject' => '{month}: lo que viene, y más en {month}']);
        $following->setRelation('role', $role);
        $this->assertSame('Diciembre 2026: lo que viene, y más en diciembre 2026', $service->subjectFor($following));

        $rest = $this->newsletter($role, 'this_month', ['subject' => 'Lo que queda de {month}']);
        $rest->setRelation('role', $role);
        $this->assertSame('Lo que queda de noviembre 2026', $service->subjectFor($rest));
    }

    public function test_the_next_seven_days_are_seven_calendar_days_counting_the_first(): void
    {
        $role = $this->role();
        $this->eventAt($role, 'Day seven', '2026-11-07 20:00');
        $this->eventAt($role, 'Day eight', '2026-11-08 20:00');

        $this->travelTo($this->berlin('2026-11-01 09:00'));

        $this->assertSame(['Day seven'], $this->listed($this->newsletter($role, 'next_7_days')));
    }

    public function test_a_sent_newsletter_keeps_its_subject_with_the_month_filled_in(): void
    {
        \Illuminate\Support\Facades\Queue::fake();
        $role = $this->role();
        $this->travelTo($this->berlin('2026-11-01 09:00'));
        $newsletter = $this->newsletter($role, 'all', ['subject' => 'AStA in {month}', 'blocks' => []]);
        $newsletter->save();

        $this->assertTrue(app(NewsletterService::class)->send($newsletter->fresh()));

        $this->assertSame('AStA in November 2026', $newsletter->fresh()->subject);
    }
}
