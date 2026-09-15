<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * events.starts_at is stored in UTC, and the API reads and writes it in UTC. Between the two,
 * ApiEventController rewrites the value as a wall-clock in the schedule's zone, because
 * EventRepo::saveEvent() interprets whatever it is handed that way and converts it back.
 *
 * That round trip is only an identity when both halves resolve the SAME zone. The controller
 * used to pick its zone through a fallback chain that differed from saveEvent()'s own (which
 * consults the venue first) - so on a schedule with no timezone of its own, a PUT that did not
 * even mention starts_at could move the event by the difference between the venue's zone and
 * the caller's.
 *
 * Fixed by resolving the zone once and passing it to saveEvent() as $timezoneOverride.
 */
class ApiEventStartsAtRoundTripTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function apiKey(User $user): string
    {
        $raw = 'testapikey_'.Str::random(24);
        $user->api_key = substr(hash('sha256', $raw), 0, 8);
        $user->api_key_hash = Hash::make($raw);
        $user->save();

        return $raw;
    }

    private function storedStartsAt(Event $event): string
    {
        return Event::query()->whereKey($event->id)->value('starts_at');
    }

    public function test_an_update_without_starts_at_does_not_move_the_event_when_the_schedule_has_no_timezone(): void
    {
        $owner = $this->createOwner();
        $owner->timezone = 'Asia/Tokyo';
        $owner->save();

        // A talent schedule with no zone of its own, playing a venue in a different zone. The
        // controller falls back to the caller's zone; saveEvent() falls back to the venue's.
        $talent = $this->createRole($owner, 'talent', ['timezone' => null]);
        $venue = $this->createRole($owner, 'venue', [
            'name' => 'Round Trip Hall',
            'address1' => '1 Main St',
            'timezone' => 'America/New_York',
        ]);

        $event = $this->createEvent($talent, ['starts_at' => '2026-10-20 11:00:00']);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);
        $event->forceFill(['creator_role_id' => $talent->id])->save();

        $this->putJson('/api/events/'.UrlUtils::encodeId($event->id), [
            'name' => 'Renamed',
            // Re-sent by a read-modify-write client; resolves onto the same venue.
            'venue_name' => 'Round Trip Hall',
            'venue_address1' => '1 Main St',
        ], ['X-API-Key' => $this->apiKey($owner)])->assertOk();

        $this->assertSame('2026-10-20 11:00:00', $this->storedStartsAt($event),
            'a PUT that never mentioned starts_at moved the event');
    }

    public function test_a_schedule_without_a_timezone_round_trips_the_utc_value_it_was_given(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['timezone' => null]);
        $key = $this->apiKey($owner);

        $event = $this->createEvent($role, ['starts_at' => '2026-10-20 11:00:00']);
        $event->forceFill(['creator_role_id' => $role->id])->save();

        $this->putJson('/api/events/'.UrlUtils::encodeId($event->id), [
            'name' => 'Renamed',
        ], ['X-API-Key' => $key])->assertOk();

        $this->assertSame('2026-10-20 11:00:00', $this->storedStartsAt($event));

        $response = $this->postJson('/api/events/'.$role->subdomain, [
            'name' => 'Created',
            'starts_at' => '2026-11-05 18:30:00',
            'duration' => 2,
        ], ['X-API-Key' => $key])->assertCreated();

        $created = Event::findOrFail(UrlUtils::decodeId($response->json('data.id')));
        $this->assertSame('2026-11-05 18:30:00', $this->storedStartsAt($created),
            'the UTC value the API was given is not the UTC value it stored');
    }

    public function test_the_zone_the_controller_resolves_is_the_zone_the_event_records(): void
    {
        $owner = $this->createOwner();
        $owner->timezone = 'Asia/Tokyo';
        $owner->save();
        $role = $this->createRole($owner, 'venue', ['timezone' => null]);

        $response = $this->postJson('/api/events/'.$role->subdomain, [
            'name' => 'Created',
            'starts_at' => '2026-11-05 18:30:00',
            'duration' => 2,
        ], ['X-API-Key' => $this->apiKey($owner)])->assertCreated();

        $created = Event::findOrFail(UrlUtils::decodeId($response->json('data.id')));
        $this->assertSame('2026-11-05 18:30:00', $this->storedStartsAt($created));
        $this->assertSame('Asia/Tokyo', $created->timezone);
    }
}
