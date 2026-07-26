<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Setting;
use App\Services\FederationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The sender half: what this install is willing to share, and what it holds back.
 */
class FederationShareTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const EVENTS_ENDPOINT = 'https://eventschedule.com/api/federation/events';

    private const RECONCILE_ENDPOINT = 'https://eventschedule.com/api/federation/reconcile';

    private const REGISTER_ENDPOINT = 'https://eventschedule.com/api/federation/register';

    protected function setUp(): void
    {
        parent::setUp();

        // The suite runs with IS_NEXUS=true; federating is an instance-side feature.
        config(['app.is_nexus' => false]);
        Setting::set('federation_enabled', '1');
    }

    private function fakeNexus(array $eventsResponse = ['accepted' => 1, 'skipped' => 0, 'status' => 'approved']): void
    {
        Http::fake([
            self::EVENTS_ENDPOINT => Http::response($eventsResponse),
            self::RECONCILE_ENDPOINT => Http::response(['removed' => 0, 'missing' => [], 'status' => 'approved']),
            self::REGISTER_ENDPOINT => Http::response(['status' => 'pending', 'registered' => true]),
        ]);
    }

    private function service(): FederationService
    {
        return app(FederationService::class);
    }

    /** An event that will actually qualify: image, accepted role, upcoming. */
    private function shareableEvent(array $eventAttrs = [], array $roleAttrs = []): Event
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', $roleAttrs);

        return $this->createEvent($role, array_merge([
            'name' => 'Summer Show',
            // A stored filename, not a URL: the accessor makes it absolute.
            'flyer_image_url' => 'flyer.jpg',
            // Without a creator role, scheduleTimezone() falls back to the app
            // timezone and the fixture would not exercise a real event's timezone.
            'creator_role_id' => $role->id,
        ], $eventAttrs));
    }

    public function test_a_public_event_is_pushed_and_watermarked(): void
    {
        $this->fakeNexus();
        $event = $this->shareableEvent();

        $result = $this->service()->push();

        $this->assertSame(1, $result['pushed']);
        $this->assertFalse($result['failed']);
        $this->assertNotNull($event->fresh()->federated_at);

        Http::assertSent(function ($request) {
            if ($request->url() !== self::EVENTS_ENDPOINT) {
                return false;
            }

            // The body is sent raw so it can be signed byte-for-byte, which leaves
            // $request->data() empty.
            $item = json_decode($request->body(), true)['items'][0];

            return $item['name'] === 'Summer Show'
                && str_contains($item['url'], 'http')
                && str_contains($item['image_url'], 'flyer.jpg')
                && $item['timezone'] === 'America/New_York';
        });
    }

    public function test_every_request_is_signed_with_the_instance_secret(): void
    {
        $this->fakeNexus();
        $this->shareableEvent();

        $this->service()->push();

        $secret = Setting::get('federation_secret');
        $this->assertNotEmpty($secret);

        Http::assertSent(function ($request) use ($secret) {
            $expected = 'sha256='.hash_hmac('sha256', $request->body(), $secret);

            return $request->header(FederationService::SIGNATURE_HEADER)[0] === $expected;
        });
    }

    public function test_private_draft_cancelled_and_password_protected_events_are_never_sent(): void
    {
        $this->fakeNexus();

        $this->shareableEvent(['is_private' => true]);
        $this->shareableEvent(['is_draft' => true]);
        $this->shareableEvent(['is_cancelled' => true]);
        $this->shareableEvent(['event_password' => 'secret']);

        $result = $this->service()->push();

        $this->assertSame(0, $result['pushed']);
        Http::assertNotSent(fn ($request) => $request->url() === self::EVENTS_ENDPOINT);
    }

    public function test_a_schedule_that_opted_out_is_not_shared(): void
    {
        $this->fakeNexus();
        $this->shareableEvent([], ['federation_enabled' => false]);

        $this->assertSame(0, $this->service()->push()['pushed']);
    }

    public function test_an_unverified_schedule_is_not_shared(): void
    {
        $this->fakeNexus();
        $this->shareableEvent([], ['email_verified_at' => null]);

        $this->assertSame(0, $this->service()->push()['pushed']);
        $this->assertSame(1, $this->service()->unverifiedScheduleCount());
    }

    public function test_an_event_without_an_image_is_not_shared(): void
    {
        $this->fakeNexus();
        $this->shareableEvent(['flyer_image_url' => null]);

        // Browse only lists cards that show a real picture, so sending it is wasted.
        $this->assertSame(0, $this->service()->push()['pushed']);
    }

    public function test_a_failed_chunk_leaves_the_watermark_unset_so_it_retries(): void
    {
        Http::fake([self::EVENTS_ENDPOINT => Http::response(['error' => 'nope'], 500)]);
        $event = $this->shareableEvent();

        $result = $this->service()->push();

        $this->assertTrue($result['failed']);
        $this->assertNull($event->fresh()->federated_at);
        $this->assertSame('error', Setting::get('federation_last_error'));
    }

    /**
     * Editing something the listing shows must re-queue it; churn that no listing
     * shows must not, or an hourly sync becomes a continuous one.
     */
    public function test_editing_a_displayed_field_requeues_the_event(): void
    {
        $this->fakeNexus();
        $event = $this->shareableEvent();
        $this->service()->push();
        $this->assertNotNull($event->fresh()->federated_at);

        $event->refresh();
        $event->name = 'Renamed Show';
        $event->save();

        $this->assertNull($event->fresh()->federated_at);
    }

    public function test_rsvp_churn_does_not_requeue_the_event(): void
    {
        $this->fakeNexus();
        $event = $this->shareableEvent();
        $this->service()->push();
        $this->assertNotNull($event->fresh()->federated_at);

        $event->refresh();
        $event->rsvp_sold = json_encode(['2026-01-01' => 3]);
        $event->save();

        $this->assertNotNull($event->fresh()->federated_at);
    }

    /**
     * An inbound calendar sync writes through $event->save() rather than
     * EventRepo::saveEvent(), which is why invalidation lives in a model hook.
     */
    public function test_a_direct_save_outside_the_repo_still_requeues(): void
    {
        $this->fakeNexus();
        $event = $this->shareableEvent();
        $this->service()->push();

        $fresh = Event::find($event->id);
        $fresh->starts_at = now()->addDays(21)->setTime(12, 0)->format('Y-m-d H:i:s');
        $fresh->save();

        $this->assertNull($fresh->fresh()->federated_at);
    }

    public function test_renaming_the_schedule_requeues_its_events(): void
    {
        $this->fakeNexus();
        $event = $this->shareableEvent();
        $this->service()->push();
        $this->assertNotNull($event->fresh()->federated_at);

        $role = $event->roles->first();
        $role->name = 'The Renamed Hall';
        $role->save();

        $this->assertNull($event->fresh()->federated_at);
    }

    public function test_changing_the_subdomain_requeues_events_because_the_backlink_moves(): void
    {
        $this->fakeNexus();
        $event = $this->shareableEvent();
        $this->service()->push();

        $role = $event->roles->first();
        $role->subdomain = 'a-new-subdomain';
        $role->save();

        $this->assertNull($event->fresh()->federated_at);
    }

    public function test_reconcile_sends_the_full_manifest_and_requeues_missing_ids(): void
    {
        $event = $this->shareableEvent();
        $external = \App\Utils\UrlUtils::encodeId($event->id);

        // Stubbed once: Http::fake() merges rather than replaces, so a later call
        // would lose to the stub registered first.
        Http::fake([
            self::EVENTS_ENDPOINT => Http::response(['accepted' => 1, 'skipped' => 0, 'status' => 'approved']),
            self::RECONCILE_ENDPOINT => Http::response([
                'removed' => 0,
                'missing' => [$external],
                'status' => 'approved',
            ]),
        ]);

        $this->service()->push();
        $result = $this->service()->reconcile();

        $this->assertTrue($result['ok']);
        $this->assertSame(1, $result['requeued']);
        // The nexus does not hold it, so the watermark clears and the next run re-sends.
        $this->assertNull($event->fresh()->federated_at);
    }

    public function test_nothing_is_sent_when_federation_is_disabled(): void
    {
        $this->fakeNexus();
        Setting::set('federation_enabled', null);

        $this->assertFalse($this->service()->isEnabled());
    }

    public function test_the_preview_lists_exactly_what_would_be_shared(): void
    {
        $this->shareableEvent(['name' => 'Will Share']);
        $this->shareableEvent(['name' => 'Will Not', 'is_draft' => true]);

        $preview = $this->service()->previewEvents();

        $this->assertCount(1, $preview);
        $this->assertSame('Will Share', $preview->first()->name);
    }
}
