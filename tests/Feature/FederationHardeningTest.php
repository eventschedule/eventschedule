<?php

namespace Tests\Feature;

use App\Console\Commands\FederationMaintenance;
use App\Http\Controllers\Api\ApiFederationController;
use App\Models\Event;
use App\Models\FederatedEvent;
use App\Models\FederatedInstance;
use App\Models\Setting;
use App\Services\FederationService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Regressions for the pre-ship review of federation.
 *
 * Each test here pins a specific way the feature was found to fail: a batch that
 * could never drain, a public page that 500'd on array input, an opt-out that did
 * not opt anything out, images that outlived their rows, and a signed request whose
 * most destructive flag was not actually covered by the signature.
 */
class FederationHardeningTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const EVENTS = '/api/federation/events';

    private const RECONCILE = '/api/federation/reconcile';

    private const REGISTER = '/api/federation/register';

    private string $secret = 'a-secret-long-enough-to-pass-validation-0123456789';

    private function makeInstance(array $attributes = []): FederatedInstance
    {
        return FederatedInstance::create(array_merge([
            'instance_id' => (string) Str::uuid(),
            'site_url' => 'https://operator.test',
            'name' => 'Operator',
            'secret' => $this->secret,
            'status' => FederatedInstance::STATUS_APPROVED,
        ], $attributes));
    }

    /** Sign exactly the way FederationService does: HMAC over the raw JSON body. */
    private function signed(string $endpoint, array $payload, array $query = [])
    {
        $body = json_encode($payload);
        $uri = $endpoint.($query ? '?'.http_build_query($query) : '');

        return $this->call('POST', $uri, [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_'.str_replace('-', '_', strtoupper(FederationService::SIGNATURE_HEADER)) => 'sha256='.hash_hmac('sha256', $body, $this->secret),
        ], $body);
    }

    private function item(array $overrides = []): array
    {
        return array_merge([
            'external_id' => 'abc123',
            'url' => 'https://operator.test/venue/summer-show',
            'name' => 'Summer Show',
            'starts_at' => now()->addWeek()->toIso8601String(),
            'timezone' => 'Europe/Berlin',
            'image_url' => 'https://operator.test/img/flyer.jpg',
        ], $overrides);
    }

    private function makeListing(FederatedInstance $instance, array $attributes = []): FederatedEvent
    {
        $event = FederatedEvent::create(array_merge([
            'federated_instance_id' => $instance->id,
            'external_id' => Str::random(8),
            'url' => 'https://operator.test/venue/show',
            'name' => 'Federated Show',
            'timezone' => 'Europe/Berlin',
            'next_occurrence_at' => now()->addWeek(),
        ], $attributes));

        // image_path is not fillable by a push; only the local fetch sets it.
        if (array_key_exists('image_path', $attributes)) {
            $event->image_path = $attributes['image_path'];
            $event->save();
        }

        return $event;
    }

    /** Switch this install from the nexus (suite default) to a federating sender. */
    private function asSender(): void
    {
        config(['app.is_nexus' => false]);
        Setting::set('federation_enabled', '1');
    }

    private function service(): FederationService
    {
        return app(FederationService::class);
    }

    /**
     * Where a listing's image actually lives on the configured disk.
     *
     * Mirrors FederatedEvent::deleteStoredImage(), which prefixes 'public/' on the
     * local driver. Seeding at the raw path instead would make these tests pass for
     * the wrong reason: the delete would miss and the assertion would still hold.
     */
    private function storageKey(string $path): string
    {
        return config('filesystems.default') == 'local' ? 'public/'.$path : $path;
    }

    /**
     * Point the app at an object-storage-shaped disk: not local/public, and serving
     * from its own host.
     *
     * This is the configuration the bug needed. Event::getFlyerImageUrlAttribute()
     * special-cases do_spaces-while-hosted and local/public, then falls through to
     * returning the raw stored value - so on a disk like this one an uploaded flyer was
     * advertised to the nexus as a bare filename. A literal 's3' default cannot stand in
     * here, because the driver needs credentials to even resolve.
     */
    private function useObjectStorageDisk(): void
    {
        config([
            'filesystems.default' => 'object_test',
            'filesystems.disks.object_test' => [
                'driver' => 'local',
                'root' => storage_path('framework/testing/object_test'),
                'url' => 'https://cdn.example.test',
                'visibility' => 'public',
                'throw' => false,
            ],
        ]);
    }

    // ---------------------------------------------------------------- public page

    /**
     * filled() is true for an array, so ?country[]=US used to reach strtoupper(array)
     * - a TypeError, i.e. an unauthenticated 500 on a crawlable page.
     */
    public function test_browse_survives_array_valued_filters(): void
    {
        $this->makeListing($this->makeInstance(), ['image_path' => 'federated/flyer.jpg']);

        $this->get('/browse?country[]=US')->assertOk();
        $this->get('/browse?lang[]=en')->assertOk();
        $this->get('/browse?country[]=US&lang[]=en')->assertOk();
    }

    /** The scalar path must still actually filter, or the fix above would be a no-op. */
    public function test_browse_scalar_country_filter_still_narrows(): void
    {
        $instance = $this->makeInstance();
        $this->makeListing($instance, ['name' => 'Berlin Show', 'country_code' => 'DE', 'image_path' => 'a.jpg']);
        $this->makeListing($instance, ['name' => 'Austin Show', 'country_code' => 'US', 'image_path' => 'b.jpg']);

        $this->get('/browse?country=DE')
            ->assertOk()
            ->assertSee('Berlin Show')
            ->assertDontSee('Austin Show');
    }

    /**
     * The ItemList is echoed raw with JSON_UNESCAPED_SLASHES, so without JSON_HEX_TAG a
     * closing script tag in a local event name terminates the element and the rest of
     * the name runs as markup.
     */
    public function test_an_event_name_cannot_break_out_of_the_structured_data(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $this->createEvent($role, [
            'name' => '</script><script>alert(1)</script>',
            'flyer_image_url' => 'flyer.jpg',
            'creator_role_id' => $role->id,
        ]);

        $html = $this->get('/browse')->assertOk()->getContent();

        preg_match_all('#<script[^>]*type="application/ld\+json"[^>]*>(.*?)</script>#s', $html, $matches);

        $itemList = null;
        foreach ($matches[1] as $block) {
            if (str_contains($block, 'ItemList')) {
                $itemList = $block;
            }
        }

        $this->assertNotNull($itemList, 'The ItemList structured data was not rendered.');

        // JSON_HEX_TAG escapes every angle bracket, so no literal < or > is left in the
        // block at all - nothing in it can close the element or open a new one.
        $this->assertStringNotContainsString('<', $itemList);
        $this->assertStringNotContainsString('>', $itemList);

        // And it is escaping, not mangling: consumers decode back to the exact name.
        $decoded = json_decode(trim($itemList), true);
        $this->assertIsArray($decoded, 'The structured data is no longer valid JSON.');
        $this->assertSame('</script><script>alert(1)</script>', $decoded['itemListElement'][0]['name']);
    }

    // ------------------------------------------------------------------- intake

    /**
     * One malformed row must not sink the batch. It used to: the request 422'd, the
     * sender stamped nothing, and it rebuilt the identical chunk every hour forever.
     */
    public function test_one_invalid_item_does_not_reject_the_whole_batch(): void
    {
        $instance = $this->makeInstance();

        $response = $this->signed(self::EVENTS, [
            'instance_id' => $instance->instance_id,
            'items' => [
                $this->item(['external_id' => 'good-1', 'url' => 'https://operator.test/e/1']),
                // A bare filename, which is what an S3-backed sender used to emit.
                $this->item(['external_id' => 'bad-image', 'url' => 'https://operator.test/e/2', 'image_url' => 'flyer.jpg']),
                $this->item(['external_id' => 'good-2', 'url' => 'https://operator.test/e/3']),
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('accepted', 2)
            ->assertJsonPath('skipped', 1)
            ->assertJsonPath('skipped_ids', ['bad-image']);

        $this->assertSame(2, FederatedEvent::where('federated_instance_id', $instance->id)->count());
        $this->assertSame(0, FederatedEvent::where('external_id', 'bad-image')->count());
    }

    /** Non-http schemes reach an href on the public page, so they are refused. */
    public function test_non_http_schemes_are_refused(): void
    {
        $instance = $this->makeInstance();

        $response = $this->signed(self::EVENTS, [
            'instance_id' => $instance->instance_id,
            'items' => [
                $this->item(['external_id' => 'ftp', 'url' => 'ftp://operator.test/e/1']),
                $this->item(['external_id' => 'viewsource', 'url' => 'view-source://operator.test/e/2']),
            ],
        ]);

        $response->assertOk()->assertJsonPath('accepted', 0);
        $this->assertSame(0, FederatedEvent::where('federated_instance_id', $instance->id)->count());
    }

    /**
     * is_final drives a full-catalogue sweep, and the HMAC covers only the body - so
     * reading it from the query string put the most destructive flag in the request
     * outside the signature.
     */
    public function test_is_final_cannot_be_supplied_via_the_query_string(): void
    {
        $instance = $this->makeInstance();
        $keep = $this->makeListing($instance, ['external_id' => 'keep-me']);

        // Signed body deliberately omits is_final; the URL tries to smuggle it in.
        $this->signed(self::RECONCILE, [
            'instance_id' => $instance->instance_id,
            'external_ids' => [],
            'run_token' => 'run-1',
        ], ['is_final' => '1'])->assertOk()->assertJsonPath('removed', 0);

        $this->assertNotNull($keep->fresh(), 'A query-string is_final swept the catalogue.');
    }

    /** The signed-body form must still work, or the guard above would break reconcile. */
    public function test_is_final_in_the_signed_body_still_sweeps(): void
    {
        $instance = $this->makeInstance();
        $stale = $this->makeListing($instance, ['external_id' => 'stale']);

        $this->signed(self::RECONCILE, [
            'instance_id' => $instance->instance_id,
            'external_ids' => [],
            'run_token' => 'run-1',
            'is_final' => true,
        ])->assertOk()->assertJsonPath('removed', 1);

        $this->assertNull($stale->fresh());
    }

    /**
     * isAcceptable() authorises backlinks against whatever site_url currently says, so
     * an approved instance quietly re-pointing it is a way to publish links anywhere.
     */
    public function test_changing_the_site_url_host_sends_an_instance_back_for_review(): void
    {
        $instance = $this->makeInstance(['approved_at' => now()]);

        $this->signed(self::REGISTER, [
            'instance_id' => $instance->instance_id,
            'site_url' => 'https://spam.example',
            'secret' => $this->secret,
        ])->assertOk()->assertJsonPath('status', FederatedInstance::STATUS_PENDING);

        $instance->refresh();
        $this->assertSame(FederatedInstance::STATUS_PENDING, $instance->status);
        $this->assertNotNull($instance->flagged_at);
        $this->assertNull($instance->approved_at);
    }

    /** Rotating a secret without moving host is routine and must not disturb approval. */
    public function test_rotating_a_secret_on_the_same_host_keeps_approval(): void
    {
        $instance = $this->makeInstance(['approved_at' => now()]);

        $this->signed(self::REGISTER, [
            'instance_id' => $instance->instance_id,
            'site_url' => 'https://operator.test',
            'secret' => $this->secret,
        ])->assertOk()->assertJsonPath('status', FederatedInstance::STATUS_APPROVED);

        $this->assertSame(FederatedInstance::STATUS_APPROVED, $instance->fresh()->status);
        $this->assertNull($instance->fresh()->flagged_at);
    }

    // ------------------------------------------------------------------ storage

    /** Reconcile mass-deletes, which fires no model events - images used to survive. */
    public function test_the_reconcile_sweep_removes_stored_images(): void
    {
        Storage::fake();
        Storage::put($this->storageKey('federated/gone.jpg'), 'bytes');

        $instance = $this->makeInstance();
        $this->makeListing($instance, ['external_id' => 'stale', 'image_path' => 'federated/gone.jpg']);

        $this->signed(self::RECONCILE, [
            'instance_id' => $instance->instance_id,
            'external_ids' => [],
            'run_token' => 'run-1',
            'is_final' => true,
        ])->assertOk();

        Storage::assertMissing($this->storageKey('federated/gone.jpg'));
    }

    /** Deleting an instance drops its rows via the FK cascade, bypassing PHP entirely. */
    public function test_deleting_an_instance_removes_its_stored_images(): void
    {
        Storage::fake();
        Storage::put($this->storageKey('federated/orphan.jpg'), 'bytes');

        $instance = $this->makeInstance();
        $this->makeListing($instance, ['image_path' => 'federated/orphan.jpg']);

        $admin = $this->createOwner(true);
        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);

        $this->post(route('admin.federation.delete', UrlUtils::encodeId($instance->id)))
            ->assertRedirect();

        $this->assertNull($instance->fresh());
        Storage::assertMissing($this->storageKey('federated/orphan.jpg'));
    }

    /** A single-model delete goes through the model hook. */
    public function test_deleting_one_listing_removes_its_stored_image(): void
    {
        Storage::fake();
        Storage::put($this->storageKey('federated/single.jpg'), 'bytes');

        $listing = $this->makeListing($this->makeInstance(), ['image_path' => 'federated/single.jpg']);
        $listing->delete();

        Storage::assertMissing($this->storageKey('federated/single.jpg'));
    }

    /**
     * A listing is not renderable before approval, so fetching its image early only
     * spends storage that pruneStaleInstances() can never reclaim once the instance
     * has pushed anything.
     */
    public function test_images_are_only_fetched_for_approved_instances(): void
    {
        $pending = $this->makeInstance(['status' => FederatedInstance::STATUS_PENDING]);
        $suspended = $this->makeInstance([
            'instance_id' => (string) Str::uuid(),
            'site_url' => 'https://other.test',
            'status' => FederatedInstance::STATUS_SUSPENDED,
        ]);

        $this->makeListing($pending, ['image_url' => 'https://operator.test/a.jpg']);
        $this->makeListing($suspended, ['image_url' => 'https://other.test/b.jpg']);

        $fetched = app(FederationMaintenance::class)->fetchImages();

        $this->assertSame(0, $fetched);
        $this->assertSame(0, FederatedEvent::whereNotNull('image_path')->count());
    }

    // ------------------------------------------------------------------- sender

    /**
     * getImageUrl() falls through to the raw stored value on any disk that is not
     * local/public, so an S3-backed selfhost advertised a bare filename - which the
     * nexus rejects as not-a-URL.
     */
    public function test_a_stored_filename_is_sent_as_an_absolute_url(): void
    {
        $this->asSender();
        $this->useObjectStorageDisk();

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role, [
            'name' => 'Summer Show',
            'flyer_image_url' => 'flyer.jpg',
            'creator_role_id' => $role->id,
        ]);

        $payload = $this->service()->buildPayload($event->fresh());

        $this->assertNotNull($payload, 'The event was skipped instead of resolved.');
        $this->assertMatchesRegularExpression('#^https?://#', $payload['image_url']);
        $this->assertStringNotContainsString('"flyer.jpg"', json_encode($payload['image_url']));
    }

    /** And whatever it sends must satisfy the rule the nexus applies to it. */
    public function test_the_sent_image_url_passes_the_intake_rule(): void
    {
        $this->asSender();
        $this->useObjectStorageDisk();

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role, [
            'name' => 'Summer Show',
            'flyer_image_url' => 'flyer.jpg',
            'creator_role_id' => $role->id,
        ]);

        $payload = $this->service()->buildPayload($event->fresh());

        $validator = validator(
            ['image_url' => $payload['image_url']],
            ['image_url' => ApiFederationController::itemRules()['image_url']]
        );

        $this->assertTrue($validator->passes(), 'The nexus would reject this install\'s image_url.');
    }

    /**
     * The eligibility query is an ANY-match over roles, so one willing schedule used to
     * drag its co-hosts onto the network - including a venue that had opted out, whose
     * name and full address then travelled with the listing.
     */
    public function test_a_co_listed_opted_out_schedule_suppresses_the_event(): void
    {
        $this->asSender();

        $owner = $this->createOwner();
        // The talent opted in, so the event qualifies on its own and the assertion
        // below can only be about the venue's veto.
        $talent = $this->createRole($owner, 'talent', ['federation_enabled' => true]);
        $venue = $this->createRole($owner, 'venue', ['federation_enabled' => false]);

        $event = $this->createEvent($talent, [
            'name' => 'Co-listed Show',
            'flyer_image_url' => 'flyer.jpg',
            'creator_role_id' => $talent->id,
        ]);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        $this->assertSame(
            0,
            $this->service()->federatableQuery()->whereKey($event->id)->count(),
            'An opted-out participant did not suppress the listing.'
        );
    }

    /** With nobody opted out, the same shape must still federate. */
    public function test_a_co_listed_event_still_federates_when_nobody_opted_out(): void
    {
        $this->asSender();

        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent', ['federation_enabled' => true]);
        $venue = $this->createRole($owner, 'venue', ['federation_enabled' => true]);

        $event = $this->createEvent($talent, [
            'name' => 'Co-listed Show',
            'flyer_image_url' => 'flyer.jpg',
            'creator_role_id' => $talent->id,
        ]);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        $this->assertSame(1, $this->service()->federatableQuery()->whereKey($event->id)->count());
    }

    /**
     * Attribution used to come from getViewableRole(), which returns the first *claimed*
     * role and knows nothing about federation - so it could name a schedule the
     * eligibility query never approved.
     */
    public function test_attribution_never_names_a_non_qualifying_schedule(): void
    {
        $this->asSender();

        $owner = $this->createOwner();
        $unlisted = $this->createRole($owner, 'talent', [
            'name' => 'Hidden Talent',
            'is_unlisted' => true,
            'federation_enabled' => true,
        ]);
        $venue = $this->createRole($owner, 'venue', [
            'name' => 'Public Hall',
            'federation_enabled' => true,
        ]);

        $event = $this->createEvent($unlisted, [
            'name' => 'Attributed Show',
            'flyer_image_url' => 'flyer.jpg',
            'creator_role_id' => $venue->id,
        ]);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        $payload = $this->service()->buildPayload($event->fresh());

        $this->assertNotNull($payload);
        $this->assertSame('Public Hall', $payload['schedule_name']);
        $this->assertNotSame('Hidden Talent', $payload['schedule_name']);
    }

    /**
     * Most venues are never claimed, so tightening attribution must not take the
     * location off the card with it. The venue's consent is settled upstream - an
     * opted-out participant drops the whole event - so it does not have to qualify
     * in its own right the way the credited schedule does.
     */
    public function test_an_unclaimed_venue_still_contributes_its_location(): void
    {
        $this->asSender();

        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent', ['name' => 'The Band']);
        $venue = $this->createRole($owner, 'venue', [
            'name' => 'Unclaimed Hall',
            'city' => 'Berlin',
            'email_verified_at' => null,
            'phone_verified_at' => null,
        ]);

        $event = $this->createEvent($talent, [
            'name' => 'Gig',
            'flyer_image_url' => 'flyer.jpg',
            'creator_role_id' => $talent->id,
        ]);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        $payload = $this->service()->buildPayload($event->fresh());

        $this->assertNotNull($payload);
        $this->assertSame('Unclaimed Hall', $payload['venue_name']);
        $this->assertSame('Berlin', $payload['city']);
    }

    /** Nothing is sent for an event whose image cannot be resolved to a real URL. */
    public function test_an_unresolvable_image_skips_the_event_rather_than_sending_junk(): void
    {
        $this->asSender();

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role, [
            'name' => 'No Image Show',
            'flyer_image_url' => null,
            'creator_role_id' => $role->id,
        ]);

        $this->assertNull($this->service()->buildPayload($event->fresh()));
    }

    /** A batch envelope rejection is reported distinctly, not folded into 'error'. */
    public function test_a_batch_rejection_is_classified_as_validation(): void
    {
        $this->asSender();

        Http::fake([
            'https://eventschedule.com/api/federation/events' => Http::response(
                ['error' => 'Validation failed', 'errors' => []], 422
            ),
            'https://eventschedule.com/api/federation/reconcile' => Http::response(
                ['removed' => 0, 'missing' => [], 'status' => 'approved']
            ),
        ]);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['federation_enabled' => true]);
        $this->createEvent($role, [
            'name' => 'Summer Show',
            'flyer_image_url' => 'flyer.jpg',
            'creator_role_id' => $role->id,
        ]);

        $this->service()->push();

        $this->assertSame('validation', Setting::get('federation_last_error'));
        $this->assertNotSame(
            'messages.federation_error_validation',
            __('messages.federation_error_validation'),
            'The validation error state has no translation.'
        );
    }
}
