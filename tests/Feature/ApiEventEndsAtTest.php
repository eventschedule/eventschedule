<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * `ends_at`: a second way to say how long an event is.
 *
 * An event stores a start and a length. Most systems that feed one store a start and an end, and
 * until this they had to do the subtraction themselves, in UTC, and round it the way we do. The
 * field is derived on the way out and turned into a length on the way in; what is held here is
 * that the two directions agree, and what happens when a body carries both.
 */
class ApiEventEndsAtTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
        Http::preventStrayRequests();
    }

    private function keyFor(User $owner): array
    {
        $raw = 'testapikey_'.Str::random(24);
        $owner->api_key = substr(hash('sha256', $raw), 0, 8);
        $owner->api_key_hash = Hash::make($raw);
        $owner->save();

        return ['X-API-Key' => $raw];
    }

    /** @return array{0: Role, 1: array<string, string>} */
    private function schedule(array $attrs = []): array
    {
        $owner = $this->createOwner();

        return [$this->createRole($owner, 'talent', $attrs), $this->keyFor($owner)];
    }

    /** An event from 18:00 to 20:00 UTC, a week out. */
    private function twoHourEvent(Role $role): Event
    {
        return $this->createEvent($role, [
            'creator_role_id' => $role->id,
            'starts_at' => now()->addWeek()->setTime(18, 0)->format('Y-m-d H:i:s'),
            'duration' => 2,
        ]);
    }

    private function at(Event $event, string $time): string
    {
        return substr($event->starts_at, 0, 10).' '.$time;
    }

    private function url(Event $event): string
    {
        return '/api/events/'.UrlUtils::encodeId($event->id);
    }

    /**
     * 1h20 is 1.333 hours at the precision the column keeps, and 1.333 hours is 19:59:58 on a
     * clock. The end that was sent has to be the end that is read.
     */
    public function test_an_end_time_becomes_the_length_and_reads_back_as_it_was_sent(): void
    {
        [$role, $key] = $this->schedule();
        $day = now()->addWeek()->format('Y-m-d');

        $id = $this->postJson('/api/events/'.$role->subdomain, [
            'name' => 'Eighty minutes',
            'starts_at' => $day.' 18:40:00',
            'ends_at' => $day.' 20:00:00',
        ], $key)
            ->assertCreated()
            ->assertJsonPath('data.duration', 1.333)
            ->assertJsonPath('data.starts_at', $day.' 18:40:00')
            ->assertJsonPath('data.ends_at', $day.' 20:00:00')
            ->json('data.id');

        $this->assertSame(1.333, Event::find(UrlUtils::decodeId($id))->duration);
        $this->getJson('/api/events/'.$id, $key)->assertOk()->assertJsonPath('data.ends_at', $day.' 20:00:00');
    }

    /**
     * 8pm on the 7th to 8pm on the 8th in New York, the night the clocks go forward: a day on the
     * wall and 23 hours of time. The length is the time that passes, so the subtraction is done
     * on the UTC instants, before the start is turned into the schedule's wall-clock for the save.
     */
    public function test_an_event_across_a_clock_change_is_as_long_as_the_time_that_passes(): void
    {
        [$role, $key] = $this->schedule(['timezone' => 'America/New_York']);

        $id = $this->postJson('/api/events/'.$role->subdomain, [
            'name' => 'Through the night the clocks change',
            'starts_at' => '2026-03-08 01:00:00',
            'ends_at' => '2026-03-09 00:00:00',
        ], $key)
            ->assertCreated()
            ->assertJsonPath('data.duration', 23)
            ->assertJsonPath('data.starts_at', '2026-03-08 01:00:00')
            ->assertJsonPath('data.ends_at', '2026-03-09 00:00:00')
            ->json('data.id');

        // And an update that only moves the end, read against the stored start.
        $this->putJson('/api/events/'.$id, ['ends_at' => '2026-03-09 01:00:00'], $key)
            ->assertOk()
            ->assertJsonPath('data.duration', 24)
            ->assertJsonPath('data.starts_at', '2026-03-08 01:00:00');
    }

    public function test_an_event_with_no_length_reads_no_end_and_a_null_end_says_nothing(): void
    {
        [$role, $key] = $this->schedule();
        $open = $this->createEvent($role, ['creator_role_id' => $role->id, 'duration' => 0]);
        $event = $this->twoHourEvent($role);

        $this->getJson($this->url($open), $key)->assertOk()->assertJsonPath('data.ends_at', null);

        // Read as null, sent back as null: not a request to take the length off.
        $this->putJson($this->url($event), ['name' => 'Renamed', 'ends_at' => null], $key)
            ->assertOk()
            ->assertJsonPath('data.duration', 2)
            ->assertJsonPath('data.ends_at', $this->at($event, '20:00:00'));
    }

    /**
     * The object carries both, so a client that reads an event and writes it back sends both.
     * After it changed one, they disagree, and the one it changed is the one that differs from
     * what is stored.
     */
    public function test_when_both_are_sent_the_one_that_changed_wins(): void
    {
        [$role, $key] = $this->schedule();
        $event = $this->twoHourEvent($role);
        $read = $this->getJson($this->url($event), $key)->assertOk()->json('data');
        $this->assertSame($this->at($event, '20:00:00'), $read['ends_at']);

        // Written back untouched.
        $this->putJson($this->url($event), $read, $key)->assertOk()->assertJsonPath('data.duration', 2);

        // The end moved, the length as it was read.
        $this->putJson($this->url($event), ['ends_at' => $this->at($event, '21:30:00')] + $read, $key)
            ->assertOk()
            ->assertJsonPath('data.duration', 3.5)
            ->assertJsonPath('data.ends_at', $this->at($event, '21:30:00'));

        // The length changed, the end as it was read.
        $read = $this->getJson($this->url($event), $key)->json('data');
        $this->putJson($this->url($event), ['duration' => 1] + $read, $key)
            ->assertOk()
            ->assertJsonPath('data.duration', 1)
            ->assertJsonPath('data.ends_at', $this->at($event, '19:00:00'));

        // The start moved and the rest came back as read: the event moves whole.
        $read = $this->getJson($this->url($event), $key)->json('data');
        $this->putJson($this->url($event), ['starts_at' => $this->at($event, '15:00:00')] + $read, $key)
            ->assertOk()
            ->assertJsonPath('data.duration', 1)
            ->assertJsonPath('data.starts_at', $this->at($event, '15:00:00'))
            ->assertJsonPath('data.ends_at', $this->at($event, '16:00:00'));

        // Postponed to the next day the same way. The end that comes back with it is now BEFORE
        // the new start, and it is still only the old end, saying nothing.
        $read = $this->getJson($this->url($event), $key)->json('data');
        $nextDay = \Carbon\Carbon::parse($read['starts_at'], 'UTC')->addDay();
        $this->putJson($this->url($event), ['starts_at' => $nextDay->format('Y-m-d H:i:s')] + $read, $key)
            ->assertOk()
            ->assertJsonPath('data.duration', 1)
            ->assertJsonPath('data.starts_at', $nextDay->format('Y-m-d H:i:s'))
            ->assertJsonPath('data.ends_at', $nextDay->copy()->addHour()->format('Y-m-d H:i:s'));

        // A NEW end that is before the start is still refused.
        $this->putJson($this->url($event), ['ends_at' => $nextDay->copy()->subHours(3)->format('Y-m-d H:i:s')], $key)
            ->assertStatus(422)
            ->assertJsonValidationErrors('ends_at');
    }

    public function test_both_changed_and_saying_different_things_is_refused(): void
    {
        [$role, $key] = $this->schedule();
        $event = $this->twoHourEvent($role);

        $this->putJson($this->url($event), ['name' => 'Would be renamed', 'duration' => 5, 'ends_at' => $this->at($event, '21:00:00')], $key)
            ->assertStatus(422)
            ->assertJsonValidationErrors('ends_at');
        $this->assertSame(2.0, $event->fresh()->duration);
        $this->assertNotSame('Would be renamed', $event->fresh()->name);

        // Both changed and saying the same thing is one fact said twice.
        $this->putJson($this->url($event), ['duration' => 3, 'ends_at' => $this->at($event, '21:00:00')], $key)
            ->assertOk()
            ->assertJsonPath('data.duration', 3);

        // A new event has nothing stored to choose by.
        $body = ['name' => 'New', 'starts_at' => $this->at($event, '10:00:00')];
        $this->postJson('/api/events/'.$role->subdomain, $body + ['duration' => 4, 'ends_at' => $this->at($event, '11:00:00')], $key)
            ->assertStatus(422)
            ->assertJsonValidationErrors('ends_at');
        $this->assertSame(0, Event::where('name', 'New')->count());
        $this->postJson('/api/events/'.$role->subdomain, $body + ['duration' => 1, 'ends_at' => $this->at($event, '11:00:00')], $key)
            ->assertCreated()
            ->assertJsonPath('data.duration', 1);
    }

    public function test_an_end_that_is_not_after_the_start_or_is_too_far_or_is_not_a_time_is_refused(): void
    {
        [$role, $key] = $this->schedule();
        $event = $this->twoHourEvent($role);

        foreach ([
            $this->at($event, '18:00:00'),
            $this->at($event, '17:00:00'),
            now()->addYears(2)->format('Y-m-d H:i:s'),
            '2026-05-09T17:30:00Z',
            'tomorrow',
        ] as $end) {
            $this->putJson($this->url($event), ['ends_at' => $end], $key)
                ->assertStatus(422)
                ->assertJsonValidationErrors('ends_at');
        }

        $this->assertSame(2.0, $event->fresh()->duration);

        // The longest an event can be is still accepted.
        $this->putJson($this->url($event), ['ends_at' => \Carbon\Carbon::parse($event->starts_at, 'UTC')->addHours(8760)->format('Y-m-d H:i:s')], $key)
            ->assertOk()
            ->assertJsonPath('data.duration', 8760);
    }

    public function test_an_end_time_needs_a_start_to_be_measured_from(): void
    {
        [$role, $key] = $this->schedule();

        $this->postJson('/api/events/'.$role->subdomain, ['name' => 'No start', 'ends_at' => now()->addWeek()->format('Y-m-d H:i:s')], $key)
            ->assertStatus(422)
            ->assertJsonValidationErrors('starts_at');
        $this->assertSame(0, Event::count());

        // An event that has not been given a date yet has nothing to measure from either.
        $undated = $this->twoHourEvent($role);
        $undated->forceFill(['starts_at' => null])->save();
        $end = now()->addWeek()->setTime(20, 0)->format('Y-m-d H:i:s');

        $this->putJson($this->url($undated), ['ends_at' => $end], $key)
            ->assertStatus(422)
            ->assertJsonPath('errors.ends_at.0', 'An end time needs a start. Send starts_at as well.');
        $this->assertSame(2.0, $undated->fresh()->duration);

        // Given both, it has.
        $this->putJson($this->url($undated), ['starts_at' => now()->addWeek()->setTime(19, 0)->format('Y-m-d H:i:s'), 'ends_at' => $end], $key)
            ->assertOk()
            ->assertJsonPath('data.duration', 1);
    }
}
