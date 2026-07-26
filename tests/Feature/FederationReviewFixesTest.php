<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\FederatedEvent;
use App\Models\FederatedInstance;
use App\Models\FederationClicksDaily;
use App\Models\Setting;
use App\Services\FederationService;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Regressions for issues found reviewing the federation diff: they only appear at
 * scale or on a non-local storage disk, so the original suite missed all of them.
 */
class FederationReviewFixesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const EVENTS_ENDPOINT = 'https://eventschedule.com/api/federation/events';

    /** This install's own address. Pinned rather than read off config - see the backlink test. */
    private const INSTALL_ROOT = 'https://install.test';

    private function service(): FederationService
    {
        return app(FederationService::class);
    }

    private function enableSending(): void
    {
        config(['app.is_nexus' => false]);
        Setting::set('federation_enabled', '1');
        Http::fake([self::EVENTS_ENDPOINT => Http::response(['accepted' => 1, 'skipped' => 0, 'status' => 'approved'])]);
    }

    private function shareable(array $attrs = [], array $roleAttrs = []): Event
    {
        $owner = $this->createOwner();
        // Explicit: a freshly created schedule is undecided, which does not qualify.
        $role = $this->createRole($owner, 'venue', array_merge(['federation_enabled' => true], $roleAttrs));

        return $this->createEvent($role, array_merge([
            'name' => 'A Show',
            'flyer_image_url' => 'flyer.jpg',
            'creator_role_id' => $role->id,
        ], $attrs));
    }

    /**
     * Recurring events are re-checked every run. Sharing one budget with unsynced rows
     * meant a backlog of recurring events could fill the window forever and a newly
     * created event would never be published at all.
     */
    public function test_recurring_events_do_not_starve_a_new_one_off_event(): void
    {
        $this->enableSending();

        // Two already-synced recurring events with LOW ids, then a new one-off event
        // with a higher id, pushed with a budget of 1. Under the original single shared
        // budget that one slot went to the lowest-id recurring event and the new event
        // was never reached - the bug this guards.
        for ($i = 0; $i < 2; $i++) {
            $recurring = $this->shareable([
                'name' => 'Weekly '.$i,
                'days_of_week' => '1111111',
                'starts_at' => Carbon::now()->subDay()->setTime(19, 0)->format('Y-m-d H:i:s'),
            ]);
            $recurring->forceFill(['federated_at' => now(), 'federated_hash' => 'stale-hash'])->saveQuietly();
        }

        $fresh = $this->shareable(['name' => 'Brand New One Off']);
        $this->assertNull($fresh->fresh()->federated_at);

        $this->service()->push(1);

        $this->assertNotNull(
            $fresh->fresh()->federated_at,
            'A newly created event was starved out of the push budget by recurring events.'
        );
    }

    /**
     * The occurrence hash lives on the event row. It used to be a single JSON blob in
     * settings, which was re-decoded per event and never pruned.
     */
    public function test_the_occurrence_hash_is_stored_on_the_event_row(): void
    {
        $this->enableSending();
        $event = $this->shareable([
            'days_of_week' => '1111111',
            'starts_at' => Carbon::now()->subDay()->setTime(19, 0)->format('Y-m-d H:i:s'),
        ]);

        $this->service()->push();

        $this->assertNotEmpty($event->fresh()->federated_hash);
        $this->assertNull(Setting::get('federation_occurrence_hashes'), 'The settings blob should be gone.');

        // Unchanged dates: no second push.
        $this->assertSame(0, $this->service()->push()['pushed']);
    }

    /**
     * The card hardcoded '/storage/...', which 404s on the object-storage disk the
     * hosted site uses - the only deployment where federated listings render.
     */
    public function test_the_listing_image_url_follows_the_configured_disk(): void
    {
        $instance = FederatedInstance::create([
            'instance_id' => (string) Str::uuid(),
            'site_url' => 'https://operator.test',
            'secret' => str_repeat('a', 40),
            'status' => FederatedInstance::STATUS_APPROVED,
        ]);

        $listing = FederatedEvent::create([
            'federated_instance_id' => $instance->id,
            'external_id' => 'img-1',
            'url' => 'https://operator.test/e/1',
            'name' => 'Imaged Listing',
            'next_occurrence_at' => now()->addWeek(),
        ]);
        $listing->image_path = 'federated_abc.jpg';
        $listing->save();

        config(['filesystems.default' => 'local']);
        $this->assertStringContainsString('/storage/federated_abc.jpg', $listing->imageUrl());

        config(['app.hosted' => true, 'filesystems.default' => 'do_spaces']);
        $this->assertStringContainsString(
            'digitaloceanspaces.com/federated_abc.jpg',
            $listing->fresh()->imageUrl(),
            'Federated images would 404 on the hosted site.'
        );
    }

    /** Counts should not be inflatable by posting hashes for hidden listings. */
    public function test_clicks_are_not_counted_for_a_blocked_listing(): void
    {
        $instance = FederatedInstance::create([
            'instance_id' => (string) Str::uuid(),
            'site_url' => 'https://operator.test',
            'secret' => str_repeat('a', 40),
            'status' => FederatedInstance::STATUS_APPROVED,
        ]);

        $listing = FederatedEvent::create([
            'federated_instance_id' => $instance->id,
            'external_id' => 'click-1',
            'url' => 'https://operator.test/e/1',
            'name' => 'Clickable',
            'next_occurrence_at' => now()->addWeek(),
        ]);
        $listing->image_path = 'federated_abc.jpg';
        $listing->save();

        $hash = UrlUtils::encodeId($listing->id);

        $this->post('/browse/federated/'.$hash.'/click')->assertNoContent();
        $this->assertSame(1, FederationClicksDaily::totalForInstance($instance->id));

        $listing->block();

        $this->post('/browse/federated/'.$hash.'/click')->assertNoContent();
        $this->assertSame(1, FederationClicksDaily::totalForInstance($instance->id));
    }

    /**
     * A federated instance can push any string it likes. setTimezone() throws on an
     * unknown zone, and the card renders on a public page, so an unvalidated value
     * would 500 /browse for every visitor rather than breaking one card.
     *
     * The bad row is refused per-item rather than 422-ing the batch: a whole-request
     * rejection stamps nothing on the sender, so it would rebuild and re-send the same
     * chunk every hour forever. What matters is that the row is never stored, and that
     * the sender is told which id was refused so it stops retrying it.
     */
    public function test_an_invalid_timezone_is_rejected_at_the_intake(): void
    {
        $instance = FederatedInstance::create([
            'instance_id' => (string) Str::uuid(),
            'site_url' => 'https://operator.test',
            'secret' => str_repeat('a', 40),
            'status' => FederatedInstance::STATUS_APPROVED,
        ]);

        $payload = [
            'instance_id' => $instance->instance_id,
            'items' => [[
                'external_id' => 'bad-tz',
                'url' => 'https://operator.test/e/1',
                'name' => 'Bad Timezone Show',
                'starts_at' => now()->addWeek()->toIso8601String(),
                'timezone' => 'Not/AZone',
            ]],
        ];
        $body = json_encode($payload);

        $this->call('POST', '/api/federation/events', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_FEDERATION_SIGNATURE' => 'sha256='.hash_hmac('sha256', $body, str_repeat('a', 40)),
        ], $body)
            ->assertOk()
            ->assertJsonPath('accepted', 0)
            ->assertJsonPath('skipped', 1)
            ->assertJsonPath('skipped_ids', ['bad-tz']);

        $this->assertSame(0, FederatedEvent::where('external_id', 'bad-tz')->count());
    }

    /** Rows stored before that rule existed must not be able to break the page either. */
    public function test_browse_survives_a_listing_that_already_holds_a_bad_timezone(): void
    {
        $instance = FederatedInstance::create([
            'instance_id' => (string) Str::uuid(),
            'site_url' => 'https://operator.test',
            'secret' => str_repeat('a', 40),
            'status' => FederatedInstance::STATUS_APPROVED,
        ]);

        $listing = FederatedEvent::create([
            'federated_instance_id' => $instance->id,
            'external_id' => 'legacy-bad-tz',
            'url' => 'https://operator.test/e/1',
            'name' => 'Legacy Bad Timezone',
            'next_occurrence_at' => now()->addWeek(),
        ]);
        // Bypass the model to simulate a row written before validation existed.
        \DB::table('federated_events')->where('id', $listing->id)->update([
            'timezone' => 'Not/AZone',
            'image_path' => 'federated_abc.jpg',
        ]);

        $this->get('/browse')->assertOk()->assertSee('Legacy Bad Timezone');
        $this->assertSame('UTC', $listing->fresh()->safeTimezone());
    }

    /**
     * The fetcher used to look only at rows with no image at all, so a source that
     * replaced its flyer kept showing the original here forever.
     */
    public function test_a_replaced_flyer_is_re_fetched(): void
    {
        $instance = FederatedInstance::create([
            'instance_id' => (string) Str::uuid(),
            'site_url' => 'https://operator.test',
            'secret' => str_repeat('a', 40),
            'status' => FederatedInstance::STATUS_APPROVED,
        ]);

        $listing = FederatedEvent::create([
            'federated_instance_id' => $instance->id,
            'external_id' => 'img-refresh',
            'url' => 'https://operator.test/e/1',
            'name' => 'Refreshing Show',
            'next_occurrence_at' => now()->addWeek(),
            'image_url' => 'https://operator.test/old.jpg',
        ]);
        $listing->image_path = 'federated_old.jpg';
        $listing->image_fetched_url = 'https://operator.test/old.jpg';
        $listing->save();

        $command = new \App\Console\Commands\FederationMaintenance;
        $pick = fn () => \App\Models\FederatedEvent::whereNotNull('image_url')
            ->whereNull('blocked_at')
            ->where(function ($q) {
                $q->whereNull('image_path')
                    ->orWhereNull('image_fetched_url')
                    ->orWhereColumn('image_url', '!=', 'image_fetched_url');
            })->pluck('external_id')->all();

        // Nothing to do while the advertised URL still matches the stored copy.
        $this->assertSame([], $pick());

        // The source advertises a different flyer.
        $listing->update(['image_url' => 'https://operator.test/new.jpg']);

        $this->assertSame(['img-refresh'], $pick(), 'A replaced flyer was never re-fetched.');
    }

    /**
     * Every event has a distinct occurrence set and so a distinct hash. Hashing all of
     * them turned a batch into one UPDATE per row; only recurring events read it.
     */
    public function test_one_off_events_do_not_store_an_occurrence_hash(): void
    {
        $this->enableSending();

        $oneOff = $this->shareable(['name' => 'One Off']);
        $recurring = $this->shareable([
            'name' => 'Weekly',
            'days_of_week' => '1111111',
            'starts_at' => Carbon::now()->subDay()->setTime(19, 0)->format('Y-m-d H:i:s'),
        ]);

        $this->service()->push();

        $this->assertNotNull($oneOff->fresh()->federated_at);
        $this->assertNull($oneOff->fresh()->federated_hash, 'A one-off event stored a hash nothing reads.');

        $this->assertNotNull($recurring->fresh()->federated_hash);
    }

    /**
     * Registration is unauthenticated by design, so without a ceiling the review queue
     * can be flooded faster than a human can clear it.
     */
    public function test_registration_is_refused_once_the_review_queue_is_full(): void
    {
        $rows = [];
        for ($i = 0; $i < \App\Http\Controllers\Api\ApiFederationController::MAX_PENDING_INSTANCES; $i++) {
            $rows[] = [
                'instance_id' => (string) Str::uuid(),
                'site_url' => 'https://flood-'.$i.'.test',
                'secret' => encrypt(str_repeat('a', 40)),
                'status' => FederatedInstance::STATUS_PENDING,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        foreach (array_chunk($rows, 250) as $chunk) {
            FederatedInstance::insert($chunk);
        }

        $this->postJson('/api/federation/register', [
            'instance_id' => (string) Str::uuid(),
            'site_url' => 'https://one-too-many.test',
            'secret' => str_repeat('b', 40),
        ])->assertStatus(429);
    }

    /** A registration that never pushed anything must not hold a queue slot for months. */
    public function test_a_registration_that_never_pushed_is_pruned_quickly(): void
    {
        $abandoned = FederatedInstance::create([
            'instance_id' => (string) Str::uuid(),
            'site_url' => 'https://abandoned.test',
            'secret' => str_repeat('a', 40),
            'status' => FederatedInstance::STATUS_PENDING,
        ]);
        $abandoned->forceFill(['created_at' => now()->subDays(30)])->saveQuietly();

        // Same age, but it actually sent something - keep it for review.
        $active = FederatedInstance::create([
            'instance_id' => (string) Str::uuid(),
            'site_url' => 'https://active.test',
            'secret' => str_repeat('a', 40),
            'status' => FederatedInstance::STATUS_PENDING,
        ]);
        $active->forceFill(['created_at' => now()->subDays(30), 'last_seen_at' => now()])->saveQuietly();
        FederatedEvent::create([
            'federated_instance_id' => $active->id,
            'external_id' => 'e1',
            'url' => 'https://active.test/e/1',
            'name' => 'A Real Listing',
            'next_occurrence_at' => now()->addWeek(),
        ]);

        (new \App\Console\Commands\FederationMaintenance)->pruneStaleInstances();

        $this->assertDatabaseMissing('federated_instances', ['id' => $abandoned->id]);
        $this->assertDatabaseHas('federated_instances', ['id' => $active->id]);
    }

    /**
     * The re-push loop. push() used to stamp federated_at for events it could not build
     * or that the nexus refused; reconcile then asked about every stamped id, the nexus
     * answered "missing", the watermark was cleared, and the same event was retried
     * every hour for the life of the install - crowding out real ones via the per-run
     * budget.
     */
    public function test_an_event_the_network_refuses_is_not_retried_forever(): void
    {
        config(['app.is_nexus' => false]);
        Setting::set('federation_enabled', '1');

        $event = $this->shareable(['name' => 'Refused By The Network']);
        $external = \App\Utils\UrlUtils::encodeId($event->id);

        // Stubbed once: Http::fake() merges rather than replaces, so a second call for
        // the same URL would lose to the first.
        Http::fake([
            self::EVENTS_ENDPOINT => Http::response([
                'accepted' => 0,
                'skipped' => 1,
                'skipped_ids' => [$external],
                'status' => 'approved',
            ]),
        ]);

        $this->service()->push();

        $fresh = $event->fresh();
        $this->assertNotNull($fresh->federated_skipped_at, 'A refused event was not marked as skipped.');
        $this->assertNull($fresh->federated_at, 'A refused event must not be recorded as delivered.');

        // The whole point: it does not come back next run.
        $this->assertSame(0, $this->service()->push()['pushed'], 'A refused event was retried.');
    }

    /** An event with nothing to show is skipped rather than recorded as delivered. */
    public function test_an_event_with_no_image_is_marked_skipped_not_delivered(): void
    {
        $this->enableSending();
        $event = $this->shareable(['flyer_image_url' => null]);

        $this->service()->push();

        $fresh = $event->fresh();
        $this->assertNotNull($fresh->federated_skipped_at);
        $this->assertNull($fresh->federated_at);
    }

    /** Fixing the cause makes it retry. */
    public function test_editing_a_skipped_event_lets_it_try_again(): void
    {
        $this->enableSending();
        $event = $this->shareable(['flyer_image_url' => null]);
        $this->service()->push();
        $this->assertNotNull($event->fresh()->federated_skipped_at);

        $fresh = $event->fresh();
        $fresh->flyer_image_url = 'flyer.jpg';
        $fresh->save();

        $this->assertNull($fresh->fresh()->federated_skipped_at, 'Adding a flyer did not clear the skip marker.');
        $this->assertSame(1, $this->service()->push()['pushed']);
    }

    /**
     * A schedule on a direct custom domain would have its backlink refused by the nexus
     * forever, because the nexus only accepts links on the host the instance registered.
     */
    public function test_the_backlink_is_on_this_installs_own_host(): void
    {
        // The payload's links come from route(), which resolves against the request
        // Laravel synthesizes from APP_URL - and CI copies .env.example, which ships
        // APP_URL empty. So pin the root instead of reading the expected host back off
        // config, which would be null there. app.url is pinned to match so the payload's
        // site_url agrees with the links.
        URL::forceRootUrl(self::INSTALL_ROOT);
        config(['app.url' => self::INSTALL_ROOT]);

        $this->enableSending();
        $this->shareable([], [
            'custom_domain' => 'https://tickets.customer.test',
            'custom_domain_mode' => 'direct',
            'custom_domain_status' => 'active',
        ]);

        $this->service()->push();

        Http::assertSent(function ($request) {
            if ($request->url() !== self::EVENTS_ENDPOINT) {
                return false;
            }
            $body = json_decode($request->body(), true);
            $item = $body['items'][0];

            // Both links have to be on the host this install registered as its site_url,
            // never the schedule's own domain, or the nexus refuses them forever.
            return parse_url($item['url'], PHP_URL_HOST) === 'install.test'
                && parse_url($item['schedule_url'], PHP_URL_HOST) === 'install.test'
                && $body['site_url'] === self::INSTALL_ROOT;
        });
    }

    public function test_the_public_empty_state_uses_visitor_facing_wording(): void
    {
        $this->get('/browse?country=ZZ')
            ->assertOk()
            ->assertSee(__('messages.federation_browse_no_results'))
            ->assertDontSee(__('messages.federation_preview_empty'));
    }
}
