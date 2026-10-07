<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Where a schedule's list ends, and what it says when it could not be loaded.
 *
 * The list drew the first 200 upcoming dates and stopped, with nothing after the last one: a
 * schedule with weekly events ran out a few weeks ahead, and whatever came later could not be
 * reached by scrolling. Behind it the server sends the nearest 400 events and said nothing when
 * there were more. And a load that failed ended in "No scheduled events", which is a statement
 * about the schedule and was only ever true of the connection.
 */
class GuestListEndsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function listPage(): string
    {
        $role = $this->createRole($this->createOwner());
        $this->createEvent($role, ['creator_role_id' => $role->id]);

        return $this->get('/'.$role->subdomain.'?layout=list')->assertOk()->getContent();
    }

    public function test_the_payload_says_when_the_row_cap_cut_it(): void
    {
        $role = $this->createRole($this->createOwner());
        $first = $this->createEvent($role, [
            'starts_at' => now()->addDays(3)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'creator_role_id' => $role->id,
        ]);
        $url = route('role.calendar_events', ['subdomain' => $role->subdomain]).'?list=1';

        $this->assertFalse($this->getJson($url)->assertOk()->json('truncated'), 'one event is the whole list');

        // 400 more, written straight to the table: the cap is 400 rows, so this is one too many.
        $row = (array) DB::table('events')->where('id', $first->id)->first();
        $pivot = (array) DB::table('event_role')->where('event_id', $first->id)->where('role_id', $role->id)->first();
        unset($row['id'], $pivot['id']);

        $rows = [];
        for ($i = 1; $i <= 400; $i++) {
            $rows[] = ['starts_at' => now()->addDays(4)->setTime(12, 0)->addMinutes($i)->format('Y-m-d H:i:s')] + $row;
        }
        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('events')->insert($chunk);
        }
        $pivots = DB::table('events')->where('id', '>', $first->id)->pluck('id')
            ->map(fn ($id) => ['event_id' => $id] + $pivot)->all();
        foreach (array_chunk($pivots, 100) as $chunk) {
            DB::table('event_role')->insert($chunk);
        }

        $json = $this->getJson($url)->assertOk()->json();

        $this->assertCount(400, $json['events'], 'the cap still bounds the payload');
        $this->assertTrue($json['truncated'], 'and the payload says there was more');
    }

    public function test_the_list_offers_the_rest_instead_of_stopping_at_two_hundred(): void
    {
        $html = $this->listPage();

        $this->assertStringNotContainsString('this.maxEvents || 200', $html, 'a fixed cut with nothing after it');

        // One under each of the three lists the page holds: the phone's agenda in the calendar
        // layout, and the list layout as a laptop and as a phone draw it.
        $this->assertGreaterThanOrEqual(3, substr_count($html, 'data-list-more'));
        $this->assertStringContainsString('@click.stop="showMoreListRows"', $html);

        // And where the server cut the payload, the end of the list says so.
        $this->assertGreaterThanOrEqual(3, substr_count($html, 'data-list-truncated'));
        $this->assertStringContainsString(__('messages.later_events_not_listed'), $html);
        $this->assertStringContainsString('this.listTruncated = !!data.truncated', $html);
    }

    public function test_a_failed_load_is_not_reported_as_an_empty_schedule(): void
    {
        $html = $this->listPage();

        $start = strpos($html, 'data-load-failed');
        $this->assertNotFalse($start, 'the page has a place to say the load failed');
        $notice = substr($html, $start, 1600);
        $this->assertStringContainsString(__('messages.error_loading'), $notice);
        $this->assertStringContainsString(__('messages.try_again'), $notice);
        $this->assertStringContainsString('retryLoad', $notice);

        // fetch() resolves on a 500 or a 404, and response.json() of an error page throws only if
        // it is not JSON: both loaders and "load more past events" have to look at the status.
        $this->assertSame(3, substr_count($html, "throw new Error('HTTP ' + response.status)"));

        // No empty state may be drawn while the load has failed: every one is a statement about
        // the schedule ("No scheduled events", "No events found").
        preg_match_all('/v-(?:else-)?if="(!isLoadingEvents && [^"]*)"/', $html, $matches);
        $this->assertGreaterThanOrEqual(7, count($matches[1]), 'the three lists\' empty states and the month grid\'s');
        foreach ($matches[1] as $condition) {
            $this->assertStringContainsString('!loadFailed', $condition, 'empty state shown on a failed load: '.$condition);
        }
    }
}
