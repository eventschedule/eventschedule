<?php

namespace Tests\Feature\Characterization;

use App\Jobs\NotifyEventCancelled;
use App\Jobs\SendWebhook;
use App\Jobs\SyncEventToGoogleCalendar;
use App\Models\AuditLog;
use App\Models\BoostCampaign;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Models\Webhook;
use App\Services\AuditService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * What cancelling, restoring, publishing and deleting an event do today, entry point by entry
 * point, pinned before the four are moved into one service.
 *
 * Every case goes through the route (or the model method) a person or a calendar reaches, never
 * through the code being moved, so the same file holds before and after. Beyond the flag itself
 * each one checks the things nothing else pinned: the iCal sequence, the push to a connected
 * calendar, the webhook and its type, the audit row. The fixture's schedule syncs to Google and its
 * owner has one webhook subscribed to everything, so "nothing was sent" means something here.
 *
 * Bookings (an appointment's cancel and delete) are held by AppointmentApprovalTest, installment
 * plans by InstallmentTeardownTest, the attendee notice's gate by EventInterestSendTest, and the
 * rest of a calendar-driven deletion by CalendarInboundDeletionTest.
 */
class EventLifecycleCharacterizationTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // A webhook that slips past the bus fake must not leave the machine.
        Http::preventStrayRequests();
    }

    /** @return array{0: User, 1: Role} */
    private function syncingSchedule(array $attrs = []): array
    {
        $owner = $this->createOwner();
        $owner->forceFill(['google_token' => 'tok', 'google_refresh_token' => 'ref'])->save();

        $role = $this->createRole($owner, 'venue', $attrs + ['sync_direction' => 'both']);

        Webhook::create([
            'user_id' => $owner->id,
            'url' => 'https://example.test/hook',
            'secret' => 'shh',
            'event_types' => ['event.created', 'event.updated', 'event.deleted', 'event.cancelled'],
            'is_active' => true,
        ]);

        return [$owner, $role];
    }

    private function hash(Event $event): string
    {
        return UrlUtils::encodeId($event->id);
    }

    /** The job keeps what it carries to itself, so the assertions read it from inside. */
    private function sent(SendWebhook $job, string $property): mixed
    {
        return (fn () => $this->{$property})->call($job);
    }

    private function assertWebhook(string $type, int $times = 1): void
    {
        Bus::assertDispatchedTimes(SendWebhook::class, $times);
        Bus::assertDispatched(SendWebhook::class, fn (SendWebhook $job) => $this->sent($job, 'eventType') === $type);
    }

    private function audited(string $action, Event $event): int
    {
        return AuditLog::where('action', $action)->where('model_id', $event->id)->count();
    }

    public function test_cancel_marks_the_event_bumps_its_sequence_leaves_calendars_and_tells_webhooks(): void
    {
        [$owner, $role] = $this->syncingSchedule();
        $event = $this->createEvent($role, ['ical_sequence' => 4]);

        Bus::fake();
        $this->actingAs($owner)
            ->post(route('event.cancel', ['subdomain' => $role->subdomain, 'hash' => $this->hash($event)]))
            ->assertRedirect()
            ->assertSessionHas('message', __('messages.event_cancelled_flash'));

        $event->refresh();
        $this->assertTrue($event->is_cancelled);
        $this->assertNotNull($event->cancelled_at);
        $this->assertSame(5, (int) $event->ical_sequence);
        $this->assertNull($event->attendees_notified_at);

        Bus::assertDispatchedSync(SyncEventToGoogleCalendar::class, fn ($job) => true);
        $this->assertWebhook('event.cancelled');
        Bus::assertNotDispatched(NotifyEventCancelled::class);
        $this->assertSame(1, $this->audited(AuditService::EVENT_CANCEL, $event));
    }

    public function test_cancelling_a_draft_sends_no_webhook(): void
    {
        [$owner, $role] = $this->syncingSchedule();
        $event = $this->createEvent($role, ['is_draft' => true]);

        Bus::fake();
        $this->actingAs($owner)
            ->post(route('event.cancel', ['subdomain' => $role->subdomain, 'hash' => $this->hash($event)]))
            ->assertRedirect();

        $this->assertTrue($event->fresh()->is_cancelled);
        Bus::assertNotDispatched(SendWebhook::class);
    }

    public function test_cancelling_twice_does_nothing_the_second_time(): void
    {
        [$owner, $role] = $this->syncingSchedule();
        $event = $this->createEvent($role);
        $url = route('event.cancel', ['subdomain' => $role->subdomain, 'hash' => $this->hash($event)]);

        // Faked from the start: a job sent "after the response" is run again by every later
        // request of the same test, which would read here as a second webhook.
        Bus::fake();
        $this->actingAs($owner)->post($url)->assertRedirect();
        $first = $event->fresh();

        $this->actingAs($owner)->post($url)->assertRedirect()
            ->assertSessionHas('message', __('messages.event_cancelled_flash'));

        $second = $event->fresh();
        $this->assertSame((int) $first->ical_sequence, (int) $second->ical_sequence);
        $this->assertEquals($first->cancelled_at, $second->cancelled_at);
        Bus::assertDispatchedTimes(SendWebhook::class, 1);
        Bus::assertDispatchedSyncTimes(SyncEventToGoogleCalendar::class, 1);
        $this->assertSame(1, $this->audited(AuditService::EVENT_CANCEL, $event));
    }

    public function test_cancel_stops_a_boost_that_is_still_running(): void
    {
        [$owner, $role] = $this->syncingSchedule();
        $event = $this->createEvent($role);
        $campaign = BoostCampaign::create([
            'event_id' => $event->id,
            'role_id' => $role->id,
            'user_id' => $owner->id,
            'name' => 'Running boost',
            'user_budget' => 50,
            'status' => 'active',
            'billing_status' => 'pending',
        ]);

        Bus::fake();
        $this->actingAs($owner)
            ->post(route('event.cancel', ['subdomain' => $role->subdomain, 'hash' => $this->hash($event)]))
            ->assertRedirect();

        $this->assertSame('cancelled', $campaign->fresh()->status);
    }

    /**
     * The notice is opt-in, and it needs somebody to tell. Here that is one person on the
     * interest list: buyers only count on a schedule that mails from its own address
     * (EventInterestSendTest holds that gate).
     */
    public function test_people_are_told_only_when_the_owner_asks(): void
    {
        [$owner, $role] = $this->syncingSchedule(['show_event_interest' => true]);
        $quiet = $this->createEvent($role, ['name' => 'Quiet', 'creator_role_id' => $role->id]);
        $told = $this->createEvent($role, ['name' => 'Told', 'creator_role_id' => $role->id]);

        foreach ([$quiet, $told] as $event) {
            $this->postJson(route('event.interest.join', ['subdomain' => $role->subdomain]), [
                'email' => 'fan@fans.test',
                'event_id' => $this->hash($event),
                'event_date' => $event->getStartDateTime(null, true, $event->scheduleTimezone())->format('Y-m-d'),
            ])->assertOk();
        }

        Bus::fake();
        $this->actingAs($owner)
            ->post(route('event.cancel', ['subdomain' => $role->subdomain, 'hash' => $this->hash($quiet)]))
            ->assertRedirect();
        Bus::assertNotDispatched(NotifyEventCancelled::class);
        $this->assertNull($quiet->fresh()->attendees_notified_at);

        $this->actingAs($owner)
            ->post(route('event.cancel', ['subdomain' => $role->subdomain, 'hash' => $this->hash($told)]), [
                'notify_attendees' => 1,
                'notify_message' => 'The hall is flooded.',
            ])
            ->assertRedirect();
        Bus::assertDispatchedTimes(NotifyEventCancelled::class, 1);
        $this->assertNotNull($told->fresh()->attendees_notified_at);
    }

    public function test_cancelling_leaves_a_paid_sale_paid(): void
    {
        [$owner, $role] = $this->syncingSchedule();
        $event = $this->createEvent($role);
        $sale = $this->createSale($event, $role, ['status' => 'paid'], $this->createTicket($event));

        Bus::fake();
        $this->actingAs($owner)
            ->post(route('event.cancel', ['subdomain' => $role->subdomain, 'hash' => $this->hash($event)]))
            ->assertRedirect();

        $this->assertTrue($event->fresh()->is_cancelled);
        $this->assertSame('paid', $sale->fresh()->status);
    }

    public function test_restore_clears_the_cancellation_bumps_the_sequence_and_says_updated(): void
    {
        [$owner, $role] = $this->syncingSchedule();
        $event = $this->createEvent($role, ['ical_sequence' => 2]);
        $event->forceFill(['is_cancelled' => true, 'cancelled_at' => now()])->save();

        Bus::fake();
        $this->actingAs($owner)
            ->post(route('event.restore', ['subdomain' => $role->subdomain, 'hash' => $this->hash($event)]))
            ->assertRedirect()
            ->assertSessionHas('message', __('messages.event_restored'));

        $event->refresh();
        $this->assertFalse($event->is_cancelled);
        $this->assertNull($event->cancelled_at);
        $this->assertSame(3, (int) $event->ical_sequence);

        Bus::assertDispatchedSync(SyncEventToGoogleCalendar::class);
        $this->assertWebhook('event.updated');
        Bus::assertNotDispatched(NotifyEventCancelled::class);
        $this->assertSame(1, $this->audited(AuditService::EVENT_RESTORE, $event));
    }

    public function test_restoring_an_event_that_is_not_cancelled_does_nothing(): void
    {
        [$owner, $role] = $this->syncingSchedule();
        $event = $this->createEvent($role, ['ical_sequence' => 2]);

        Bus::fake();
        $this->actingAs($owner)
            ->post(route('event.restore', ['subdomain' => $role->subdomain, 'hash' => $this->hash($event)]))
            ->assertRedirect();

        $this->assertSame(2, (int) $event->fresh()->ical_sequence);
        Bus::assertNotDispatched(SendWebhook::class);
        $this->assertSame(0, $this->audited(AuditService::EVENT_RESTORE, $event));
    }

    public function test_publish_makes_a_draft_public_pushes_it_and_says_created(): void
    {
        [$owner, $role] = $this->syncingSchedule();
        $event = $this->createEvent($role, ['is_draft' => true]);

        Bus::fake();
        $this->actingAs($owner)
            ->post(route('event.publish', ['subdomain' => $role->subdomain, 'hash' => $this->hash($event)]))
            ->assertRedirect()
            ->assertSessionHas('message', __('messages.event_published'));

        $event->refresh();
        $this->assertFalse((bool) $event->is_draft);
        $this->assertSame('public', $event->visibilityState());

        Bus::assertDispatchedSync(SyncEventToGoogleCalendar::class);
        $this->assertWebhook('event.created');
        $this->assertSame(1, $this->audited(AuditService::EVENT_PUBLISH, $event));
    }

    public function test_publish_leaves_an_internal_event_and_a_published_one_alone(): void
    {
        [$owner, $role] = $this->syncingSchedule();
        $internal = $this->createEvent($role, ['is_draft' => true, 'is_internal' => true]);
        $live = $this->createEvent($role);

        Bus::fake();
        foreach ([$internal, $live] as $event) {
            $this->actingAs($owner)
                ->post(route('event.publish', ['subdomain' => $role->subdomain, 'hash' => $this->hash($event)]))
                ->assertRedirect();
        }

        $this->assertSame('internal', $internal->fresh()->visibilityState());
        Bus::assertNotDispatched(SendWebhook::class);
        $this->assertSame(0, $this->audited(AuditService::EVENT_PUBLISH, $internal));
        $this->assertSame(0, $this->audited(AuditService::EVENT_PUBLISH, $live));
    }

    /**
     * The one thing the move changed on purpose. Publishing a cancelled draft used to make it
     * public and push it to connected calendars as though it were on.
     */
    public function test_publish_leaves_a_cancelled_draft_alone(): void
    {
        [$owner, $role] = $this->syncingSchedule();
        $event = $this->createEvent($role, ['is_draft' => true]);
        $event->forceFill(['is_cancelled' => true, 'cancelled_at' => now()])->save();

        Bus::fake();
        $this->actingAs($owner)
            ->post(route('event.publish', ['subdomain' => $role->subdomain, 'hash' => $this->hash($event)]))
            ->assertRedirect();

        $this->assertTrue((bool) $event->fresh()->is_draft);
        Bus::assertNotDispatched(SendWebhook::class);
        Bus::assertNotDispatchedSync(SyncEventToGoogleCalendar::class);
        $this->assertSame(0, $this->audited(AuditService::EVENT_PUBLISH, $event));
    }

    public function test_delete_removes_an_event_nobody_bought_and_says_deleted_with_what_it_was(): void
    {
        [$owner, $role] = $this->syncingSchedule();
        $event = $this->createEvent($role, ['name' => 'Going away']);
        $id = $event->id;

        Bus::fake();
        $this->actingAs($owner)
            ->delete(route('event.delete', ['subdomain' => $role->subdomain, 'hash' => $this->hash($event)]))
            ->assertRedirect()
            ->assertSessionHas('message', __('messages.event_deleted'));

        $this->assertNull(Event::find($id));
        Bus::assertDispatched(SendWebhook::class, function (SendWebhook $job) {
            $payload = $this->sent($job, 'payload');

            return $this->sent($job, 'eventType') === 'event.deleted'
                && $payload['event'] === 'event.deleted'
                && $payload['data']->name === 'Going away';
        });
        Bus::assertDispatchedSync(SyncEventToGoogleCalendar::class);
        $this->assertSame(1, AuditLog::where('action', AuditService::EVENT_DELETE)->where('model_id', $id)->count());
    }

    public function test_delete_refuses_an_event_somebody_bought(): void
    {
        [$owner, $role] = $this->syncingSchedule();
        $event = $this->createEvent($role);
        $this->createSale($event, $role, ['status' => 'paid'], $this->createTicket($event));

        Bus::fake();
        $this->actingAs($owner)
            ->delete(route('event.delete', ['subdomain' => $role->subdomain, 'hash' => $this->hash($event)]))
            ->assertRedirect()
            ->assertSessionHas('error', __('messages.cannot_delete_event_with_sales'));

        $this->assertNotNull(Event::find($event->id));
        Bus::assertNotDispatched(SendWebhook::class);
    }

    public function test_deleting_a_draft_sends_no_webhook(): void
    {
        [$owner, $role] = $this->syncingSchedule();
        $event = $this->createEvent($role, ['is_draft' => true]);

        Bus::fake();
        $this->actingAs($owner)
            ->delete(route('event.delete', ['subdomain' => $role->subdomain, 'hash' => $this->hash($event)]))
            ->assertRedirect();

        $this->assertNull(Event::find($event->id));
        Bus::assertNotDispatched(SendWebhook::class);
    }

    public function test_the_api_delete_does_what_the_form_delete_does(): void
    {
        config(['app.hosted' => true]);
        [$owner, $role] = $this->syncingSchedule();
        $event = $this->createEvent($role, ['name' => 'Gone by key']);
        $id = $event->id;

        $raw = 'testapikey_'.Str::random(24);
        $owner->api_key = substr(hash('sha256', $raw), 0, 8);
        $owner->api_key_hash = Hash::make($raw);
        $owner->save();

        Bus::fake();
        $this->deleteJson('/api/events/'.$this->hash($event), [], ['X-API-Key' => $raw])
            ->assertOk()
            ->assertJsonPath('data.message', 'Event deleted successfully');

        $this->assertNull(Event::find($id));
        Bus::assertDispatched(SendWebhook::class, fn (SendWebhook $job) => $this->sent($job, 'eventType') === 'event.deleted'
            && $this->sent($job, 'payload')['data']->name === 'Gone by key');
        $this->assertSame(1, AuditLog::where('action', AuditService::EVENT_DELETE)->where('model_id', $id)->count());
    }

    /**
     * A deletion that arrives from a connected calendar is the quiet one: the flag and nothing
     * else. It is the calendar services that write its audit row, after reading the outcome.
     */
    public function test_a_calendar_driven_cancel_bumps_nothing_and_tells_nobody(): void
    {
        [$owner, $role] = $this->syncingSchedule();
        $event = $this->createEvent($role, ['ical_sequence' => 7]);

        Bus::fake();
        $this->assertSame('cancelled', $event->applyInboundDeletion('cancel'));

        $event->refresh();
        $this->assertTrue($event->is_cancelled);
        $this->assertSame(7, (int) $event->ical_sequence);
        Bus::assertNotDispatched(SendWebhook::class);
        Bus::assertNotDispatchedSync(SyncEventToGoogleCalendar::class);
        $this->assertSame(0, $this->audited(AuditService::EVENT_CANCEL, $event));
    }
}
