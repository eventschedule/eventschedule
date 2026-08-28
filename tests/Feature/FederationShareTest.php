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

    /**
     * An event that will actually qualify: image, accepted role, upcoming, and a
     * schedule that opted in. The opt-in is explicit because the column is now
     * tri-state and a freshly created schedule starts undecided, which does not
     * qualify - the same step a real schedule owner takes.
     */
    private function shareableEvent(array $eventAttrs = [], array $roleAttrs = []): Event
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', array_merge(['federation_enabled' => true], $roleAttrs));

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

    /**
     * The opt-in guarantee, tested at the wire rather than at the flag. Calling the
     * service directly is the point: the command checks isEnabled() before it calls
     * anything, so a test that goes through the command only proves the command is
     * careful, not that the service is.
     */
    public function test_nothing_is_sent_when_federation_is_disabled(): void
    {
        $this->shareableEvent();
        $this->fakeNexus();
        Setting::set('federation_enabled', null);

        $this->assertFalse($this->service()->isEnabled());

        $this->service()->push();
        $this->service()->reconcile();
        $this->service()->register();

        Http::assertNothingSent();
    }

    /**
     * And no identity is minted on the way, either. register() builds its payload from
     * instanceId() and secret(), both of which persist on first read, so the guard has
     * to sit ahead of the payload rather than only in send().
     */
    public function test_a_disabled_install_does_not_mint_an_instance_identity(): void
    {
        $this->shareableEvent();
        $this->fakeNexus();
        Setting::set('federation_enabled', null);

        $this->service()->push();
        $this->service()->register();

        $this->assertNull(Setting::get('federation_instance_id'));
        $this->assertNull(Setting::get('federation_secret'));
    }

    public function test_withdrawing_sends_one_empty_final_manifest(): void
    {
        $this->shareableEvent();

        // Stubbed in one call, not layered on fakeNexus(): Http::fake() merges, so a
        // second stub for an endpoint loses to the one registered first.
        Http::fake([
            self::EVENTS_ENDPOINT => Http::response(['accepted' => 1, 'skipped' => 0, 'status' => 'approved']),
            self::REGISTER_ENDPOINT => Http::response(['status' => 'approved', 'registered' => true]),
            self::RECONCILE_ENDPOINT => Http::response(['removed' => 3, 'missing' => [], 'status' => 'approved']),
        ]);

        // Registered, so there is something on the other side to take down.
        $this->service()->register();
        $this->service()->push();

        // As it happens on the on->off transition: the switch is already off.
        Setting::set('federation_enabled', null);
        $result = $this->service()->withdraw();

        $this->assertTrue($result['ok']);
        $this->assertSame(3, $result['removed']);
        $this->assertNull(Setting::get('federation_withdraw_pending'));

        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), '/api/federation/reconcile')) {
                return false;
            }

            $body = $request->data();

            // Empty AND final: the nexus stamps nothing with the run token, so its
            // sweep takes out everything this instance owns.
            return $body['external_ids'] === []
                && $body['known_ids'] === []
                && $body['is_final'] === true;
        });
    }

    public function test_an_install_that_never_registered_withdraws_without_calling_out(): void
    {
        $this->fakeNexus();
        Setting::set('federation_enabled', null);

        $result = $this->service()->withdraw();

        $this->assertTrue($result['ok']);
        Http::assertNothingSent();
        $this->assertNull(Setting::get('federation_instance_id'));
    }

    /**
     * A withdrawal has no other retry - the hourly run returns early while the switch
     * is off - so a nexus that happened to be down would leave the events published
     * for good.
     */
    public function test_a_failed_withdrawal_is_retried_by_the_next_run(): void
    {
        // A closure rather than two Http::fake() calls: stubs merge, so a later one
        // for the same endpoint would never be reached.
        $attempts = 0;
        Http::fake([
            self::REGISTER_ENDPOINT => Http::response(['status' => 'approved', 'registered' => true]),
            self::RECONCILE_ENDPOINT => function () use (&$attempts) {
                $attempts++;

                return $attempts === 1
                    ? Http::response(['error' => 'nope'], 500)
                    : Http::response(['removed' => 2, 'missing' => [], 'status' => 'approved']);
            },
        ]);

        $this->service()->register();
        Setting::set('federation_enabled', null);

        $this->assertFalse($this->service()->withdraw()['ok']);
        $this->assertSame('1', Setting::get('federation_withdraw_pending'));

        // The command carries it out on a later run even though federation is off,
        // which is the only thing that still happens once the switch is down.
        $this->artisan('federation:push')->assertSuccessful();

        $this->assertNull(Setting::get('federation_withdraw_pending'));
        $this->assertSame(2, $attempts);
    }

    public function test_the_preview_lists_exactly_what_would_be_shared(): void
    {
        $this->shareableEvent(['name' => 'Will Share']);
        $this->shareableEvent(['name' => 'Will Not', 'is_draft' => true]);

        $preview = $this->service()->previewEvents();

        $this->assertCount(1, $preview);
        $this->assertSame('Will Share', $preview->first()->name);
    }

    /**
     * A listing carries the schedule's name and the address of its public page, and
     * whoever reviews the install sees both, so the operator is shown the schedules
     * and not only the event titles.
     */
    public function test_the_preview_names_the_schedules_that_would_be_listed(): void
    {
        $event = $this->shareableEvent();

        $schedules = $this->service()->previewSchedules();

        $this->assertCount(1, $schedules);
        $this->assertSame($event->creatorRole->id, $schedules->first()->id);
    }

    /**
     * The whole reason the preview cannot be a plain
     * Role::where('federation_enabled', true): a schedule can be opted in and still
     * publish nothing, because any co-listed participant opting out vetoes the whole
     * event. Listing it as "will be shared" would be a lie in the one place an
     * operator is trusting this screen.
     */
    public function test_a_schedule_vetoed_on_every_event_is_not_listed_as_shared(): void
    {
        $event = $this->shareableEvent();
        $optedIn = $event->creatorRole;

        $objector = $this->createRole($this->createOwner(), 'talent', ['federation_enabled' => false]);
        $event->roles()->attach($objector->id, ['is_accepted' => true]);

        $this->assertCount(0, $this->service()->previewEvents());
        $this->assertCount(0, $this->service()->previewSchedules());
        $this->assertTrue($optedIn->fresh()->federation_enabled);
    }

    /**
     * The role-side gate is load-bearing: the whereHas onto events is an ANY-match,
     * so without it an unverified co-participant on a qualifying event would be
     * reported as being published when it is not.
     */
    public function test_an_unverified_co_participant_is_not_listed_as_shared(): void
    {
        $event = $this->shareableEvent();

        $unverified = $this->createRole($this->createOwner(), 'talent', ['federation_enabled' => true]);
        $unverified->forceFill(['email_verified_at' => null, 'phone_verified_at' => null])->save();
        $event->roles()->attach($unverified->id, ['is_accepted' => true]);

        $names = $this->service()->previewSchedules()->pluck('id')->all();

        $this->assertContains($event->creatorRole->id, $names);
        $this->assertNotContains($unverified->id, $names);
    }

    /** The joining link is not sent at all now, only whether there is one. */
    public function test_the_payload_carries_an_online_flag_and_not_the_joining_link(): void
    {
        $this->fakeNexus();
        $this->shareableEvent(['event_url' => 'https://zoom.us/j/8412?pwd=secrettoken']);

        $this->service()->push();

        Http::assertSent(function ($request) {
            if ($request->url() !== self::EVENTS_ENDPOINT) {
                return true;
            }

            $item = $request->data()['items'][0];

            return ($item['is_online'] ?? null) === true
                && ! array_key_exists('event_url', $item)
                && ! str_contains(json_encode($item), 'secrettoken');
        });
    }
}
