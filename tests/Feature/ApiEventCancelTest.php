<?php

namespace Tests\Feature;

use App\Jobs\NotifyEventCancelled;
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
 * POST /api/events/{id}/cancel and /restore, and the read side of a cancellation.
 *
 * Until these the API could only delete, and it refuses to delete an event with sales, so the one
 * event an integration most needs to call off was the one it could not touch. The endpoints are
 * thin over EventLifecycleService, which the admin portal uses too; what is held here is the
 * API's own part: who may, what the reply says, the two fields on the event object, the list
 * filter, and that a body cannot do by the back door what the endpoints do properly.
 */
class ApiEventCancelTest extends TestCase
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

    /** @return array{0: User, 1: Role, 2: array<string, string>} */
    private function schedule(array $attrs = []): array
    {
        $owner = $this->createOwner();
        $owner->forceFill(['google_token' => 'tok', 'google_refresh_token' => 'ref'])->save();
        $role = $this->createRole($owner, 'venue', $attrs + ['sync_direction' => 'both']);

        Webhook::create([
            'user_id' => $owner->id,
            'url' => 'https://example.test/hook',
            'secret' => 'shh',
            'event_types' => ['event.updated', 'event.cancelled'],
            'is_active' => true,
        ]);

        return [$owner, $role, $this->keyFor($owner)];
    }

    private function url(Event $event, string $action = ''): string
    {
        return '/api/events/'.UrlUtils::encodeId($event->id).($action ? '/'.$action : '');
    }

    public function test_an_event_with_sales_is_cancelled_and_its_sale_stays_paid(): void
    {
        [, $role, $key] = $this->schedule();
        $event = $this->createEvent($role, ['creator_role_id' => $role->id, 'ical_sequence' => 1]);
        $sale = $this->createSale($event, $role, ['status' => 'paid'], $this->createTicket($event));

        // The thing DELETE will not do.
        $this->deleteJson($this->url($event), [], $key)
            ->assertStatus(422)
            ->assertJsonFragment(['error' => 'This event has sales and cannot be deleted. Cancel it instead with POST /api/events/{id}/cancel, so buyers keep their records and can be notified.']);

        Bus::fake();
        $this->postJson($this->url($event, 'cancel'), [], $key)
            ->assertOk()
            ->assertJsonPath('meta.message', 'Event cancelled successfully')
            ->assertJsonPath('data.is_cancelled', true)
            ->assertJsonPath('data.id', UrlUtils::encodeId($event->id));

        $event->refresh();
        $this->assertTrue($event->is_cancelled);
        $this->assertSame(2, (int) $event->ical_sequence);
        $this->assertSame('paid', $sale->fresh()->status);
        Bus::assertDispatchedSync(SyncEventToGoogleCalendar::class);
        Bus::assertDispatched(SendWebhook::class);
        Bus::assertNotDispatched(NotifyEventCancelled::class);
    }

    public function test_cancelled_at_is_a_time_once_cancelled_and_null_before(): void
    {
        [, $role, $key] = $this->schedule();
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);

        $this->getJson($this->url($event), $key)
            ->assertOk()
            ->assertJsonPath('data.is_cancelled', false)
            ->assertJsonPath('data.cancelled_at', null);

        Bus::fake();
        $cancelled = $this->postJson($this->url($event, 'cancel'), [], $key)->assertOk()->json('data.cancelled_at');

        $this->assertNotNull($cancelled);
        $this->assertSame($event->fresh()->cancelled_at->toIso8601String(), $cancelled);
    }

    public function test_the_people_registered_are_told_only_when_asked_and_a_long_note_is_refused(): void
    {
        [, $role, $key] = $this->schedule(['show_event_interest' => true]);
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);
        $this->postJson(route('event.interest.join', ['subdomain' => $role->subdomain]), [
            'email' => 'fan@fans.test',
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => $event->getStartDateTime(null, true, $event->scheduleTimezone())->format('Y-m-d'),
        ])->assertOk();

        Bus::fake();
        $this->postJson($this->url($event, 'cancel'), ['notify_attendees' => true, 'message' => str_repeat('a', 281)], $key)
            ->assertStatus(422)
            ->assertJsonValidationErrors('message');
        $this->assertFalse($event->fresh()->is_cancelled);

        $this->postJson($this->url($event, 'cancel'), ['notify_attendees' => true, 'message' => 'The hall is flooded.'], $key)
            ->assertOk();
        Bus::assertDispatchedTimes(NotifyEventCancelled::class, 1);
        $this->assertNotNull($event->fresh()->attendees_notified_at);
    }

    public function test_cancelling_twice_answers_the_same_and_does_it_once(): void
    {
        [, $role, $key] = $this->schedule();
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);

        Bus::fake();
        $this->postJson($this->url($event, 'cancel'), [], $key)->assertOk();
        $this->postJson($this->url($event, 'cancel'), [], $key)
            ->assertOk()
            ->assertJsonPath('meta.message', 'Event was already cancelled')
            ->assertJsonPath('data.is_cancelled', true);

        Bus::assertDispatchedTimes(SendWebhook::class, 1);
    }

    public function test_restore_brings_it_back_and_a_live_event_answers_without_change(): void
    {
        [, $role, $key] = $this->schedule();
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);
        $event->forceFill(['is_cancelled' => true, 'cancelled_at' => now()])->save();

        Bus::fake();
        $this->postJson($this->url($event, 'restore'), [], $key)
            ->assertOk()
            ->assertJsonPath('meta.message', 'Event restored successfully')
            ->assertJsonPath('data.is_cancelled', false)
            ->assertJsonPath('data.cancelled_at', null);
        $this->assertFalse($event->fresh()->is_cancelled);

        $this->postJson($this->url($event, 'restore'), [], $key)
            ->assertOk()
            ->assertJsonPath('meta.message', 'Event was not cancelled');
        Bus::assertDispatchedTimes(SendWebhook::class, 1);
    }

    public function test_only_someone_who_may_edit_the_event_can_cancel_or_restore_it(): void
    {
        [, $role] = $this->schedule();
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);
        $stranger = $this->keyFor($this->createOwner());

        $this->postJson($this->url($event, 'cancel'), [], $stranger)->assertStatus(403);
        $this->postJson($this->url($event, 'restore'), [], $stranger)->assertStatus(403);
        $this->postJson('/api/events/'.UrlUtils::encodeId(999999).'/cancel', [], $stranger)->assertStatus(404);
        $this->postJson($this->url($event, 'cancel'))->assertStatus(401);

        $this->assertFalse($event->fresh()->is_cancelled);
    }

    public function test_the_list_can_ask_for_cancelled_events_or_leave_them_out(): void
    {
        [, $role, $key] = $this->schedule();
        $on = $this->createEvent($role, ['creator_role_id' => $role->id, 'name' => 'On']);
        $off = $this->createEvent($role, ['creator_role_id' => $role->id, 'name' => 'Off']);
        $off->forceFill(['is_cancelled' => true, 'cancelled_at' => now()])->save();

        $names = fn (string $query) => collect($this->getJson('/api/events'.$query, $key)->assertOk()->json('data'))->pluck('name')->sort()->values()->all();

        $this->assertSame(['Off', 'On'], $names(''));
        $this->assertSame(['Off'], $names('?is_cancelled=true'));
        $this->assertSame(['Off'], $names('?is_cancelled=1'));
        $this->assertSame(['On'], $names('?is_cancelled=false'));
        $this->assertSame(['On'], $names('?is_cancelled=0'));

        $this->getJson('/api/events?is_cancelled=maybe', $key)->assertStatus(422);
    }

    /**
     * The object carries is_cancelled, so a client that reads an event and writes it back sends
     * it. The stored value passes. A different one is refused rather than dropped: a script that
     * set it would otherwise be told 200 and leave the event on.
     */
    public function test_a_body_cannot_cancel_or_restore(): void
    {
        [, $role, $key] = $this->schedule();
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);

        $this->putJson($this->url($event), ['name' => 'Same state', 'is_cancelled' => false], $key)
            ->assertOk()
            ->assertJsonPath('data.name', 'Same state');

        $this->putJson($this->url($event), ['name' => 'Sneaky', 'is_cancelled' => true], $key)
            ->assertStatus(422)
            ->assertJsonValidationErrors('is_cancelled');
        $this->assertFalse($event->fresh()->is_cancelled);
        $this->assertSame('Same state', $event->fresh()->name);

        $this->postJson('/api/events/'.$role->subdomain, [
            'name' => 'Born cancelled',
            'starts_at' => now()->addWeek()->format('Y-m-d H:i:s'),
            'is_cancelled' => true,
        ], $key)->assertStatus(422)->assertJsonValidationErrors('is_cancelled');
        $this->assertSame(0, Event::where('name', 'Born cancelled')->count());

        // And on a cancelled event the value that is stored passes too.
        $event->forceFill(['is_cancelled' => true, 'cancelled_at' => now()])->save();
        $this->putJson($this->url($event), ['name' => 'Still off', 'is_cancelled' => true], $key)->assertOk();
        $this->putJson($this->url($event), ['is_cancelled' => false], $key)->assertStatus(422);
        $this->assertTrue($event->fresh()->is_cancelled);
    }

    public function test_a_booking_is_cancelled_through_its_sale_and_cannot_be_restored(): void
    {
        [$owner, $role, $key] = $this->schedule();
        $type = $this->createAppointmentType($role);
        $event = $this->createEvent($role, ['creator_role_id' => $role->id, 'appointment_type_id' => $type->id]);
        $sale = $this->createSale($event, $role, ['status' => 'unpaid']);

        $this->postJson($this->url($event, 'cancel'), [], $key)
            ->assertOk()
            ->assertJsonPath('meta.message', 'Appointment cancelled')
            ->assertJsonPath('data.is_cancelled', true);
        $this->assertSame('cancelled', $sale->fresh()->status);

        $this->postJson($this->url($event, 'restore'), [], $key)->assertStatus(422);
        $this->assertTrue($event->fresh()->is_cancelled);
    }
}
