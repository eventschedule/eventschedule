<?php

namespace Tests\Feature;

use App\Jobs\SendWebhook;
use App\Jobs\SyncEventToGoogleCalendar;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Models\Webhook;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Cancelling an event takes it off the calendars its schedule is connected to. Saving it afterwards
 * (a typo fixed on the form, an API update that renames it) used to push it back: every save of a
 * published event ends with an "update" to the calendar, and the calendar job creates the entry
 * when it finds no mapping, which is exactly the state the cancellation left.
 */
class CancelledEventStaysOffCalendarsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
        Http::preventStrayRequests();
    }

    /** @return array{0: User, 1: Role, 2: array<string, string>} */
    private function syncingSchedule(): array
    {
        $owner = $this->createOwner();
        $owner->forceFill(['google_token' => 'tok', 'google_refresh_token' => 'ref'])->save();
        $role = $this->createRole($owner, 'venue', ['sync_direction' => 'both']);

        Webhook::create([
            'user_id' => $owner->id,
            'url' => 'https://example.test/hook',
            'secret' => 'shh',
            'event_types' => ['event.updated'],
            'is_active' => true,
        ]);

        $raw = 'testapikey_'.Str::random(24);
        $owner->api_key = substr(hash('sha256', $raw), 0, 8);
        $owner->api_key_hash = Hash::make($raw);
        $owner->save();

        return [$owner, $role, ['X-API-Key' => $raw]];
    }

    private function rename(Event $event, array $key): void
    {
        $this->putJson('/api/events/'.UrlUtils::encodeId($event->id), ['name' => 'Renamed'], $key)
            ->assertOk()
            ->assertJsonPath('data.name', 'Renamed');
    }

    public function test_saving_a_cancelled_event_does_not_push_it_to_a_calendar(): void
    {
        [, $role, $key] = $this->syncingSchedule();
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);
        $event->forceFill(['is_cancelled' => true, 'cancelled_at' => now()])->save();

        Bus::fake();
        $this->rename($event, $key);

        Bus::assertNotDispatchedSync(SyncEventToGoogleCalendar::class);
        // Whoever listens for changes is still told: the event did change.
        Bus::assertDispatched(SendWebhook::class);
        $this->assertTrue($event->fresh()->is_cancelled);
    }

    /** The control: the same save of an event that is on does reach the calendar. */
    public function test_saving_an_event_that_is_on_still_pushes_it(): void
    {
        [, $role, $key] = $this->syncingSchedule();
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);

        Bus::fake();
        $this->rename($event, $key);

        Bus::assertDispatchedSync(SyncEventToGoogleCalendar::class);
    }
}
