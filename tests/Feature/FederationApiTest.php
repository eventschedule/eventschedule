<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\ApiFederationController;
use App\Models\FederatedEvent;
use App\Models\FederatedInstance;
use App\Services\FederationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The nexus intake: who may publish here, and what gets through.
 */
class FederationApiTest extends TestCase
{
    use RefreshDatabase;

    private const REGISTER = '/api/federation/register';

    private const EVENTS = '/api/federation/events';

    private const RECONCILE = '/api/federation/reconcile';

    private string $secret = 'a-secret-long-enough-to-pass-validation-0123456789';

    protected function makeInstance(array $attributes = []): FederatedInstance
    {
        return FederatedInstance::create(array_merge([
            'instance_id' => (string) Str::uuid(),
            'site_url' => 'https://operator.test',
            'name' => 'Operator',
            'contact_email' => 'ops@operator.test',
            'secret' => $this->secret,
            'status' => FederatedInstance::STATUS_APPROVED,
        ], $attributes));
    }

    /** Sign exactly the way FederationService does: HMAC over the raw JSON body. */
    private function signed(string $endpoint, array $payload, ?string $secret = null)
    {
        $body = json_encode($payload);

        return $this->call(
            'POST',
            $endpoint,
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_'.str_replace('-', '_', strtoupper(FederationService::SIGNATURE_HEADER)) => 'sha256='.hash_hmac('sha256', $body, $secret ?? $this->secret),
            ],
            $body
        );
    }

    private function item(array $overrides = []): array
    {
        return array_merge([
            'external_id' => 'abc123',
            'url' => 'https://operator.test/venue/summer-show',
            'name' => 'Summer Show',
            'short_description' => 'An evening of music',
            'language' => 'en',
            'starts_at' => now()->addWeek()->toIso8601String(),
            'timezone' => 'Europe/Berlin',
            'image_url' => 'https://operator.test/img/flyer.jpg',
            'venue_name' => 'The Hall',
            'city' => 'Berlin',
            'country_code' => 'DE',
        ], $overrides);
    }

    public function test_a_new_instance_registers_as_pending(): void
    {
        $id = (string) Str::uuid();

        $this->postJson(self::REGISTER, [
            'instance_id' => $id,
            'site_url' => 'https://operator.test',
            'name' => 'Operator',
            'contact_email' => 'ops@operator.test',
            'secret' => $this->secret,
        ])->assertCreated()->assertJson(['status' => 'pending']);

        $this->assertDatabaseHas('federated_instances', [
            'instance_id' => $id,
            'status' => 'pending',
        ]);
    }

    /**
     * instance_id travels in the clear on every request, so it identifies rather than
     * authenticates. Re-registering one must prove possession of the current secret,
     * or anyone who has seen a request could inherit an approved instance's standing.
     */
    public function test_re_registering_without_the_current_secret_is_rejected(): void
    {
        $instance = $this->makeInstance();

        $this->postJson(self::REGISTER, [
            'instance_id' => $instance->instance_id,
            'site_url' => 'https://attacker.test',
            'secret' => 'an-attacker-supplied-secret-0123456789abcd',
        ])->assertForbidden();

        $instance->refresh();
        $this->assertSame('https://operator.test', $instance->site_url);
        $this->assertSame($this->secret, $instance->secret);
    }

    public function test_re_registering_with_the_current_secret_rotates_it(): void
    {
        $instance = $this->makeInstance();
        $rotated = 'a-freshly-rotated-secret-0123456789abcdefgh';

        $this->signed(self::REGISTER, [
            'instance_id' => $instance->instance_id,
            'site_url' => 'https://operator.test',
            'secret' => $rotated,
        ])->assertOk();

        $this->assertSame($rotated, $instance->fresh()->secret);
    }

    public function test_unsigned_pushes_are_rejected(): void
    {
        $instance = $this->makeInstance();

        $this->postJson(self::EVENTS, [
            'instance_id' => $instance->instance_id,
            'items' => [$this->item()],
        ])->assertForbidden();

        $this->assertDatabaseCount('federated_events', 0);
    }

    public function test_a_signed_push_is_stored(): void
    {
        $instance = $this->makeInstance();

        $this->signed(self::EVENTS, [
            'instance_id' => $instance->instance_id,
            'items' => [$this->item()],
        ])->assertOk()->assertJson(['accepted' => 1, 'status' => 'approved']);

        $this->assertDatabaseHas('federated_events', [
            'federated_instance_id' => $instance->id,
            'external_id' => 'abc123',
            'name' => 'Summer Show',
            'city' => 'Berlin',
            'country_code' => 'DE',
        ]);
    }

    /**
     * An approved instance may only publish links to its own site, or federation
     * becomes an open backlink farm.
     */
    public function test_links_pointing_off_the_instances_host_are_skipped(): void
    {
        $instance = $this->makeInstance();

        $this->signed(self::EVENTS, [
            'instance_id' => $instance->instance_id,
            'items' => [$this->item(['url' => 'https://somewhere-else.test/spam'])],
        ])->assertOk()->assertJson(['accepted' => 0, 'skipped' => 1]);

        $this->assertDatabaseCount('federated_events', 0);
    }

    public function test_subdomains_of_the_registered_host_are_allowed(): void
    {
        $instance = $this->makeInstance();

        $this->signed(self::EVENTS, [
            'instance_id' => $instance->instance_id,
            'items' => [$this->item(['url' => 'https://tenant.operator.test/show'])],
        ])->assertOk()->assertJson(['accepted' => 1]);
    }

    /**
     * schedule_url reaches an admin's browser as a clickable link on the review
     * screen, so it is held to the same host rule as the backlink.
     *
     * Nulled and not refused: a refusal is permanent, so one bad schedule_url would
     * keep an otherwise perfectly publishable event off the network for good.
     */
    public function test_a_schedule_url_off_the_instances_host_is_dropped_not_refused(): void
    {
        $instance = $this->makeInstance();

        $this->signed(self::EVENTS, [
            'instance_id' => $instance->instance_id,
            'items' => [$this->item(['schedule_url' => 'https://somewhere-else.test/phish'])],
        ])->assertOk()->assertJson(['accepted' => 1, 'skipped' => 0]);

        $this->assertDatabaseHas('federated_events', [
            'external_id' => 'abc123',
            'schedule_url' => null,
        ]);
    }

    public function test_a_schedule_url_on_a_subdomain_of_the_registered_host_survives(): void
    {
        $instance = $this->makeInstance();

        $this->signed(self::EVENTS, [
            'instance_id' => $instance->instance_id,
            'items' => [$this->item(['schedule_url' => 'https://tenant.operator.test/agenda'])],
        ])->assertOk()->assertJson(['accepted' => 1]);

        $this->assertDatabaseHas('federated_events', [
            'external_id' => 'abc123',
            'schedule_url' => 'https://tenant.operator.test/agenda',
        ]);
    }

    /**
     * The online joining link is no longer stored, only whether there is one. An
     * install on an older release still sends event_url, and must keep working.
     */
    public function test_an_older_senders_event_url_sets_the_flag_and_is_not_stored(): void
    {
        $instance = $this->makeInstance();

        $this->signed(self::EVENTS, [
            'instance_id' => $instance->instance_id,
            'items' => [$this->item(['event_url' => 'https://zoom.us/j/8412?pwd=secrettoken'])],
        ])->assertOk()->assertJson(['accepted' => 1]);

        $this->assertDatabaseHas('federated_events', [
            'external_id' => 'abc123',
            'is_online' => true,
        ]);

        // The link itself is gone, not merely unread.
        $this->assertFalse(
            \Illuminate\Support\Facades\Schema::hasColumn('federated_events', 'event_url')
        );
    }

    public function test_a_current_sender_sets_the_flag_directly(): void
    {
        $instance = $this->makeInstance();

        $this->signed(self::EVENTS, [
            'instance_id' => $instance->instance_id,
            'items' => [$this->item(['is_online' => true])],
        ])->assertOk()->assertJson(['accepted' => 1]);

        $this->assertDatabaseHas('federated_events', [
            'external_id' => 'abc123',
            'is_online' => true,
        ]);
    }

    public function test_an_in_person_event_is_not_marked_online(): void
    {
        $instance = $this->makeInstance();

        $this->signed(self::EVENTS, [
            'instance_id' => $instance->instance_id,
            'items' => [$this->item()],
        ])->assertOk()->assertJson(['accepted' => 1]);

        $this->assertDatabaseHas('federated_events', [
            'external_id' => 'abc123',
            'is_online' => false,
        ]);
    }

    public function test_junk_names_are_skipped_like_local_events_are(): void
    {
        $instance = $this->makeInstance();

        $this->signed(self::EVENTS, [
            'instance_id' => $instance->instance_id,
            'items' => [
                $this->item(['external_id' => 'a', 'name' => 'test']),
                $this->item(['external_id' => 'b', 'name' => 'asdf']),
                $this->item(['external_id' => 'c', 'name' => 'Real Gig']),
            ],
        ])->assertOk()->assertJson(['accepted' => 1, 'skipped' => 2]);

        $this->assertDatabaseHas('federated_events', ['external_id' => 'c']);
    }

    /**
     * The nexus operator's country policy applies to federated rows too, now that
     * in-person events carry their own country.
     */
    public function test_the_country_exclusion_applies_to_federated_rows(): void
    {
        config(['app.search_exclude_country' => 'de']);
        $instance = $this->makeInstance();

        $this->signed(self::EVENTS, [
            'instance_id' => $instance->instance_id,
            'items' => [$this->item()],
        ])->assertOk()->assertJson(['accepted' => 0, 'skipped' => 1]);
    }

    public function test_a_re_push_updates_in_place_rather_than_duplicating(): void
    {
        $instance = $this->makeInstance();

        $this->signed(self::EVENTS, [
            'instance_id' => $instance->instance_id,
            'items' => [$this->item()],
        ])->assertOk();

        $this->signed(self::EVENTS, [
            'instance_id' => $instance->instance_id,
            'items' => [$this->item(['name' => 'Summer Show (rescheduled)'])],
        ])->assertOk();

        $this->assertDatabaseCount('federated_events', 1);
        $this->assertSame('Summer Show (rescheduled)', FederatedEvent::first()->name);
    }

    /**
     * The regression blocked_at exists to prevent: an admin removes a listing, the
     * source re-pushes it, and it comes back as though nothing happened.
     */
    public function test_a_blocked_listing_stays_blocked_after_a_re_push(): void
    {
        $instance = $this->makeInstance();

        $this->signed(self::EVENTS, [
            'instance_id' => $instance->instance_id,
            'items' => [$this->item()],
        ])->assertOk();

        FederatedEvent::first()->block();

        $this->signed(self::EVENTS, [
            'instance_id' => $instance->instance_id,
            'items' => [$this->item(['name' => 'Sneaky Retitle'])],
        ])->assertOk();

        $this->assertNotNull(FederatedEvent::first()->blocked_at);
    }

    public function test_events_from_a_pending_instance_are_stored_but_not_listable(): void
    {
        $instance = $this->makeInstance(['status' => FederatedInstance::STATUS_PENDING]);

        $this->signed(self::EVENTS, [
            'instance_id' => $instance->instance_id,
            'items' => [$this->item()],
        ])->assertOk()->assertJson(['accepted' => 1, 'status' => 'pending']);

        $row = FederatedEvent::first();
        $row->image_path = 'federated/flyer.jpg';
        $row->save();

        $this->assertSame(0, FederatedEvent::listable()->count());

        $instance->update(['status' => FederatedInstance::STATUS_APPROVED]);
        $this->assertSame(1, FederatedEvent::listable()->count());
    }

    public function test_the_per_instance_cap_reports_rather_than_truncating(): void
    {
        $instance = $this->makeInstance();

        // Fill to the cap directly; pushing thousands through HTTP would be slow.
        $rows = [];
        for ($i = 0; $i < ApiFederationController::MAX_EVENTS_PER_INSTANCE; $i++) {
            $rows[] = [
                'federated_instance_id' => $instance->id,
                'external_id' => 'seed-'.$i,
                'url' => 'https://operator.test/e/'.$i,
                'name' => 'Seeded '.$i,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        foreach (array_chunk($rows, 1000) as $chunk) {
            FederatedEvent::insert($chunk);
        }

        $this->signed(self::EVENTS, [
            'instance_id' => $instance->instance_id,
            'items' => [$this->item(['external_id' => 'one-too-many'])],
        ])->assertStatus(422)->assertJson(['error' => 'Event limit reached']);
    }

    public function test_reconcile_removes_rows_missing_from_the_manifest(): void
    {
        $instance = $this->makeInstance();

        $this->signed(self::EVENTS, [
            'instance_id' => $instance->instance_id,
            'items' => [
                $this->item(['external_id' => 'keep']),
                $this->item(['external_id' => 'drop']),
            ],
        ])->assertOk();

        $this->signed(self::RECONCILE, [
            'instance_id' => $instance->instance_id,
            'external_ids' => ['keep'],
            'run_token' => (string) Str::uuid(),
            'is_final' => true,
        ])->assertOk()->assertJson(['removed' => 1]);

        $this->assertDatabaseHas('federated_events', ['external_id' => 'keep']);
        $this->assertDatabaseMissing('federated_events', ['external_id' => 'drop']);
    }

    /**
     * A blocked row is a tombstone. Deleting it on reconcile would let the source
     * resurrect a blocked listing simply by re-publishing it.
     */
    public function test_reconcile_keeps_blocked_rows_as_tombstones(): void
    {
        $instance = $this->makeInstance();

        $this->signed(self::EVENTS, [
            'instance_id' => $instance->instance_id,
            'items' => [$this->item(['external_id' => 'blocked'])],
        ])->assertOk();

        FederatedEvent::first()->block();

        $this->signed(self::RECONCILE, [
            'instance_id' => $instance->instance_id,
            'external_ids' => [],
            'run_token' => (string) Str::uuid(),
            'is_final' => true,
        ])->assertOk();

        $this->assertDatabaseHas('federated_events', ['external_id' => 'blocked']);
    }

    /**
     * Closes the deadlock: once a sender stamps its watermark it never re-sends, so
     * the nexus has to say what it is missing.
     */
    public function test_reconcile_reports_ids_the_nexus_does_not_hold(): void
    {
        $instance = $this->makeInstance();

        $this->signed(self::RECONCILE, [
            'instance_id' => $instance->instance_id,
            'external_ids' => ['a', 'b'],
            'known_ids' => ['a', 'b'],
            'run_token' => (string) Str::uuid(),
            'is_final' => true,
        ])->assertOk()->assertJson(['missing' => ['a', 'b']]);
    }

    public function test_a_push_claiming_a_different_site_url_is_flagged_for_review(): void
    {
        $instance = $this->makeInstance();

        $this->signed(self::EVENTS, [
            'instance_id' => $instance->instance_id,
            'site_url' => 'https://a-clone.test',
            'items' => [$this->item()],
        ])->assertOk();

        $fresh = $instance->fresh();
        $this->assertNotNull($fresh->flagged_at);
        // Kept, not discarded: the admin screen has to be able to show WHICH address is
        // being claimed, and "Accept new address" has to have something to adopt.
        $this->assertSame('https://a-clone.test', $fresh->reported_site_url);
    }

    /**
     * The reported address is stored now, in a varchar(255), where it used to be
     * compared and discarded. An unbounded one would fail the insert and 500 a push
     * that is otherwise valid - and site_url is never length-checked on this path.
     */
    public function test_an_over_long_reported_address_does_not_break_the_push(): void
    {
        $instance = $this->makeInstance();
        $long = 'https://'.str_repeat('a', 300).'.test';

        $this->signed(self::EVENTS, [
            'instance_id' => $instance->instance_id,
            'site_url' => $long,
            'items' => [$this->item()],
        ])->assertOk();

        $fresh = $instance->fresh();
        $this->assertNotNull($fresh->flagged_at);
        $this->assertSame(255, mb_strlen($fresh->reported_site_url));

        // And the truncated value is stable, so the same sender does not re-flag hourly.
        $flaggedAt = $fresh->flagged_at;
        $this->signed(self::EVENTS, [
            'instance_id' => $instance->instance_id,
            'site_url' => $long,
            'items' => [$this->item()],
        ])->assertOk();

        $this->assertSame(
            $flaggedAt->toDateTimeString(),
            $instance->fresh()->flagged_at->toDateTimeString()
        );
    }

    /**
     * flagged_at marks when the mismatch STARTED, not when it last pushed.
     *
     * This runs on every push. Re-stamping it would mean an admin who has already
     * reviewed this exact address gets re-alerted within the hour, which is what made
     * the dashboard row impossible to drain.
     */
    public function test_a_second_push_of_the_same_address_does_not_re_raise_the_flag(): void
    {
        $flaggedAt = now()->subDays(2);
        $instance = $this->makeInstance([
            'flagged_at' => $flaggedAt,
            'reported_site_url' => 'https://a-clone.test',
        ]);

        $this->signed(self::EVENTS, [
            'instance_id' => $instance->instance_id,
            'site_url' => 'https://a-clone.test',
            'items' => [$this->item()],
        ])->assertOk();

        $this->assertSame(
            $flaggedAt->toDateTimeString(),
            $instance->fresh()->flagged_at->toDateTimeString()
        );
    }

    /** A different address again is a new claim, so it does re-raise. */
    public function test_a_push_from_a_third_address_raises_the_flag_again(): void
    {
        $instance = $this->makeInstance([
            'flagged_at' => now()->subDays(2),
            'reported_site_url' => 'https://a-clone.test',
        ]);

        $this->signed(self::EVENTS, [
            'instance_id' => $instance->instance_id,
            'site_url' => 'https://another-clone.test',
            'items' => [$this->item()],
        ])->assertOk();

        $fresh = $instance->fresh();
        $this->assertSame('https://another-clone.test', $fresh->reported_site_url);
        $this->assertTrue($fresh->flagged_at->isToday());
    }

    /**
     * The operator fixed their APP_URL: the mismatch is over, so the flag goes with it
     * and the dashboard alert drains without anyone clicking anything.
     */
    public function test_a_matching_push_clears_the_flag_on_an_approved_instance(): void
    {
        $instance = $this->makeInstance([
            'flagged_at' => now()->subDay(),
            'reported_site_url' => 'https://a-clone.test',
        ]);

        $this->signed(self::EVENTS, [
            'instance_id' => $instance->instance_id,
            'site_url' => 'https://operator.test',
            'items' => [$this->item()],
        ])->assertOk();

        $fresh = $instance->fresh();
        $this->assertNull($fresh->flagged_at);
        $this->assertNull($fresh->reported_site_url);
    }

    /**
     * Self-healing is scoped to approved instances whose flag came from the push path.
     * A pending instance's flag is part of its review context: clearing it here would
     * let a remote push its own way out of the moderation queue.
     */
    public function test_a_matching_push_does_not_clear_a_pending_instances_flag(): void
    {
        $instance = $this->makeInstance([
            'status' => FederatedInstance::STATUS_PENDING,
            'flagged_at' => now()->subDay(),
            'reported_site_url' => 'https://a-clone.test',
        ]);

        $this->signed(self::EVENTS, [
            'instance_id' => $instance->instance_id,
            'site_url' => 'https://operator.test',
            'items' => [$this->item()],
        ])->assertOk();

        $this->assertNotNull($instance->fresh()->flagged_at);
    }

    /**
     * A register-path flag survives the matching pushes that follow it.
     *
     * Driven through the real endpoint rather than by hand-setting columns: register()
     * de-approves as it flags, and that de-approval is precisely what protects the flag
     * here. A fixture that set flagged_at on an approved row would be asserting against a
     * state register() cannot produce, and would pin the wrong guard.
     */
    public function test_a_matching_push_does_not_clear_a_register_path_flag(): void
    {
        $instance = $this->makeInstance();

        $this->signed(self::REGISTER, [
            'instance_id' => $instance->instance_id,
            'site_url' => 'https://moved.test',
            'secret' => $this->secret,
        ])->assertOk();

        $instance->refresh();
        $this->assertSame(FederatedInstance::STATUS_PENDING, $instance->status);
        $this->assertNotNull($instance->flagged_at);

        // The moved install now pushes, reporting the address register() just recorded.
        $this->signed(self::EVENTS, [
            'instance_id' => $instance->instance_id,
            'site_url' => 'https://moved.test',
            'items' => [$this->item()],
        ])->assertOk();

        // Still flagged and still pending: the reviewer's signal that it moved survives.
        $this->assertNotNull($instance->fresh()->flagged_at);
    }

    /**
     * Rows flagged before reported_site_url existed carry no reported address, so
     * accepting one is impossible - a matching push is the only thing that can drain
     * them. This is the state the live nexus alert was stuck in.
     */
    public function test_a_matching_push_clears_a_legacy_flag_with_no_reported_address(): void
    {
        $instance = $this->makeInstance([
            'flagged_at' => now()->subDay(),
            'reported_site_url' => null,
        ]);

        $this->signed(self::EVENTS, [
            'instance_id' => $instance->instance_id,
            'site_url' => 'https://operator.test',
            'items' => [$this->item()],
        ])->assertOk();

        $this->assertNull($instance->fresh()->flagged_at);
    }

    public function test_intake_is_absent_off_the_nexus(): void
    {
        config(['app.is_nexus' => false]);
        $instance = $this->makeInstance();

        $this->signed(self::EVENTS, [
            'instance_id' => $instance->instance_id,
            'items' => [$this->item()],
        ])->assertNotFound();
    }
}
