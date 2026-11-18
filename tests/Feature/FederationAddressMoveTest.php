<?php

namespace Tests\Feature;

use App\Mail\FederationAddressConfirmed;
use App\Models\Event;
use App\Models\FederatedInstance;
use App\Models\Setting;
use App\Models\User;
use App\Services\FederationAddressService;
use App\Services\FederationService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * An install that changes its APP_URL after it was approved: the network keeps it listed while the
 * new address is confirmed, the install holds its events back instead of having them refused for
 * good, and once the network has the new address everything is sent again with links to it.
 */
class FederationAddressMoveTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const EVENTS_ENDPOINT = 'https://eventschedule.com/api/federation/events';

    private const RECONCILE_ENDPOINT = 'https://eventschedule.com/api/federation/reconcile';

    private const REGISTER_ENDPOINT = 'https://eventschedule.com/api/federation/register';

    // ------------------------------------------------------------------ install side

    private function asSender(): void
    {
        // The suite runs with IS_NEXUS=true; federating is an instance-side feature.
        config(['app.is_nexus' => false]);
        Setting::set('federation_enabled', '1');
    }

    private function service(): FederationService
    {
        return app(FederationService::class);
    }

    private function own(): string
    {
        return $this->service()->ownUrl();
    }

    private function shareableEvent(array $eventAttrs = []): Event
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['federation_enabled' => true]);

        return $this->createEvent($role, array_merge([
            'name' => 'Summer Show',
            'flyer_image_url' => 'flyer.jpg',
            'creator_role_id' => $role->id,
        ], $eventAttrs));
    }

    /** The network as it answers when it has this install at $networkUrl. */
    private function fakeNexus(string $networkUrl, array $events = []): void
    {
        $state = ['status' => 'approved', 'site_url' => $networkUrl, 'address_review' => $networkUrl !== $this->own()];

        Http::fake([
            self::EVENTS_ENDPOINT => Http::response(array_merge(['accepted' => 1, 'skipped' => 0, 'skipped_ids' => []], $events, $state)),
            self::RECONCILE_ENDPOINT => Http::response(['removed' => 0, 'missing' => []] + $state),
            self::REGISTER_ENDPOINT => Http::response(['registered' => true] + $state),
        ]);
    }

    /**
     * The first answer from a network that reports addresses, on an install that never moved,
     * records the address and resends nothing: every install updating to this release would
     * otherwise send its whole catalogue again.
     */
    public function test_the_first_answer_records_the_address_without_resending_anything(): void
    {
        $this->asSender();
        $this->fakeNexus($this->own());
        $event = $this->shareableEvent();

        $this->assertSame(1, $this->service()->push()['pushed']);
        $this->service()->reconcile();

        $this->assertNotNull($event->fresh()->federated_at);
        $this->assertSame($this->own(), Setting::get('federation_sent_from_url'));
        $this->assertFalse($this->service()->addressPending());
    }

    /**
     * While the network checks links against an older address, every event would be refused,
     * and a refused event is not sent again until it is edited. So nothing is sent.
     */
    public function test_events_wait_while_the_network_has_an_older_address(): void
    {
        $this->asSender();
        Setting::set('federation_network_url', 'https://old.test');
        Setting::set('federation_sent_from_url', 'https://old.test');
        $this->fakeNexus('https://old.test');
        $event = $this->shareableEvent();

        $result = $this->service()->push();

        $this->assertTrue($result['held']);
        $this->assertSame(0, $result['pushed']);
        Http::assertNotSent(fn ($request) => $request->url() === self::EVENTS_ENDPOINT);
        $this->assertNull($event->fresh()->federated_skipped_at);
    }

    /**
     * The push that first carries the new address finds out from the answer: what the network
     * refused in it was refused for the address, so it is not marked refused.
     */
    public function test_the_push_that_reports_a_move_marks_nothing_refused(): void
    {
        $this->asSender();
        $event = $this->shareableEvent();
        $this->fakeNexus('https://old.test', [
            'accepted' => 0,
            'skipped' => 1,
            'skipped_ids' => [UrlUtils::encodeId($event->id)],
        ]);

        $result = $this->service()->push();

        $this->assertTrue($result['held']);
        $fresh = $event->fresh();
        $this->assertNull($fresh->federated_skipped_at);
        $this->assertNull($fresh->federated_at);
    }

    /**
     * Once the network has the new address, the listings it holds still link to the old one, and
     * events refused in the meantime are marked refused. Everything goes again.
     */
    public function test_everything_is_sent_again_once_the_network_has_the_new_address(): void
    {
        $this->asSender();
        Setting::set('federation_network_url', 'https://old.test');
        Setting::set('federation_sent_from_url', 'https://old.test');
        $this->fakeNexus($this->own());

        $sent = $this->shareableEvent(['federated_at' => now()->subDay(), 'federated_hash' => 'abc']);
        $refused = $this->shareableEvent(['federated_skipped_at' => now()->subHour()]);

        $this->assertTrue($this->service()->reconcile()['ok']);

        $this->assertNull($sent->fresh()->federated_at);
        $this->assertNull($sent->fresh()->federated_hash);
        $this->assertNull($refused->fresh()->federated_skipped_at);
        $this->assertSame($this->own(), Setting::get('federation_sent_from_url'));
        $this->assertFalse($this->service()->addressPending());
    }

    /**
     * An install already part-way through a move when it updates: its first answer says the network
     * has the old address. Once the move is taken, everything goes again, as for a move made after
     * the update.
     */
    public function test_an_install_that_updated_mid_move_still_sends_everything_again(): void
    {
        $this->asSender();
        $sent = $this->shareableEvent(['federated_at' => now()->subDay()]);

        // Http::fake() keeps the first stub for a URL, so the network's address is read per call.
        $network = 'https://old.test';
        Http::fake(function () use (&$network) {
            return Http::response(['removed' => 0, 'missing' => [], 'status' => 'approved', 'site_url' => $network]);
        });

        $this->service()->reconcile();
        $this->assertSame('https://old.test', Setting::get('federation_sent_from_url'));
        $this->assertNotNull($sent->fresh()->federated_at);

        $network = $this->own();
        $this->service()->reconcile();

        $this->assertNull($sent->fresh()->federated_at);
        $this->assertSame($this->own(), Setting::get('federation_sent_from_url'));
    }

    /**
     * The hourly run does not wait another hour when the reconcile's answer says the network has
     * taken the move (a same-site move is taken on the spot): it pushes again in the same run.
     */
    public function test_the_hourly_run_pushes_as_soon_as_the_move_is_taken(): void
    {
        $this->asSender();
        Setting::set('federation_instance_id', (string) Str::uuid());
        Setting::set('federation_network_url', 'https://old.test');
        Setting::set('federation_sent_from_url', 'https://old.test');
        $this->fakeNexus($this->own());
        $event = $this->shareableEvent();

        $this->artisan('federation:push')->assertSuccessful();

        Http::assertSent(fn ($request) => $request->url() === self::EVENTS_ENDPOINT);
        $this->assertNotNull($event->fresh()->federated_at);
    }

    /** The settings card says why events are waiting, naming both addresses. */
    public function test_the_settings_card_says_the_new_address_is_being_confirmed(): void
    {
        $this->asSender();
        Setting::set('federation_instance_id', (string) Str::uuid());
        Setting::set('federation_status', 'approved');
        Setting::set('federation_network_url', 'https://old.test');

        $admin = $this->createOwner(true);
        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);

        // Raw and escaped once, as Blade prints it: the copy has an apostrophe.
        $this->get(route('admin.settings'))
            ->assertOk()
            ->assertSee(e(__('messages.federation_address_pending', ['new' => $this->own(), 'old' => 'https://old.test'])), false);

        Setting::set('federation_network_url', $this->own());

        $this->get(route('admin.settings'))
            ->assertOk()
            ->assertDontSeeText('https://old.test');
    }

    // ------------------------------------------------------------------ nexus side

    private function claimingInstance(array $attributes = []): FederatedInstance
    {
        return FederatedInstance::create(array_merge([
            'instance_id' => (string) Str::uuid(),
            'site_url' => 'https://93.184.216.34',
            'reported_site_url' => 'https://new.test',
            'flagged_at' => now()->subHour(),
            'contact_email' => 'ops@new.test',
            'secret' => Str::random(40),
            'status' => FederatedInstance::STATUS_APPROVED,
            'approved_at' => now()->subMonth(),
        ], $attributes));
    }

    public function test_accepting_an_address_tells_the_operator(): void
    {
        Mail::fake();
        $instance = $this->claimingInstance();

        $admin = User::factory()->create(['is_admin' => true]);
        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);

        $this->post(route('admin.federation.accept_address', UrlUtils::encodeId($instance->id)))
            ->assertSessionHas('message', __('messages.federation_address_accepted'));

        $this->assertSame('https://new.test', $instance->fresh()->site_url);
        Mail::assertSent(FederationAddressConfirmed::class, fn ($mail) => $mail->hasTo('ops@new.test'));
    }

    /**
     * The old address redirecting to the new one is what a real move looks like, and what a copy
     * of the database restored somewhere else cannot produce: adopted without a human.
     */
    public function test_a_claim_is_adopted_when_the_old_address_redirects_to_it(): void
    {
        Mail::fake();
        Http::fake([
            '93.184.216.34' => Http::response('', 301, ['Location' => 'https://www.new.test/']),
            '93.184.216.34/*' => Http::response('', 301, ['Location' => 'https://www.new.test/']),
        ]);
        $instance = $this->claimingInstance();

        $this->assertSame(1, app(FederationAddressService::class)->verifyByRedirect());

        $fresh = $instance->fresh();
        $this->assertSame('https://new.test', $fresh->site_url);
        $this->assertNull($fresh->flagged_at);
        $this->assertNull($fresh->reported_site_url);
        Mail::assertSent(FederationAddressConfirmed::class);

        // The new site is never fetched: its address is all the check needs.
        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'new.test'));
    }

    /** The old address still answering for itself is a copy, not a move: it stays for a human. */
    public function test_a_claim_stays_when_the_old_address_still_answers(): void
    {
        Mail::fake();
        Http::fake([
            '93.184.216.34' => Http::response('<html>still here</html>', 200),
            '93.184.216.34/*' => Http::response('<html>still here</html>', 200),
        ]);
        $instance = $this->claimingInstance();

        $this->assertSame(0, app(FederationAddressService::class)->verifyByRedirect());

        $fresh = $instance->fresh();
        $this->assertSame('https://93.184.216.34', $fresh->site_url);
        $this->assertNotNull($fresh->flagged_at);
        Mail::assertNothingSent();
    }

    /** A redirect to a third site proves nothing about the claimed one. */
    public function test_a_redirect_somewhere_else_is_not_a_yes(): void
    {
        Http::fake([
            '93.184.216.34' => Http::response('', 302, ['Location' => 'https://parked-domains.test/']),
            '93.184.216.34/*' => Http::response('', 302, ['Location' => 'https://parked-domains.test/']),
        ]);
        $instance = $this->claimingInstance();

        $this->assertSame(0, app(FederationAddressService::class)->verifyByRedirect());
        $this->assertNotNull($instance->fresh()->flagged_at);
    }

    /**
     * Claims are checked in turns: ordered by age, a few whose old address is dead would be the head
     * of the queue for good and no newer move would ever be checked.
     */
    public function test_claims_are_checked_in_turns(): void
    {
        Http::fake([
            '93.184.216.34' => Http::response('', 200),
            '93.184.216.34/*' => Http::response('', 200),
        ]);
        $first = $this->claimingInstance();
        $second = $this->claimingInstance(['reported_site_url' => 'https://other.test']);

        $service = app(FederationAddressService::class);
        $cursor = fn () => (int) Cache::get(FederationAddressService::REDIRECT_CURSOR);

        $service->verifyByRedirect(1);
        $this->assertSame($first->id, $cursor());

        $service->verifyByRedirect(1);
        $this->assertSame($second->id, $cursor());

        // And round again.
        $service->verifyByRedirect(1);
        $this->assertSame($first->id, $cursor());
    }

    /** A check never adopts for an install that was suspended meanwhile. */
    public function test_a_redirect_check_never_adopts_a_suspended_install(): void
    {
        $instance = $this->claimingInstance();
        FederatedInstance::whereKey($instance->id)->update(['status' => FederatedInstance::STATUS_SUSPENDED]);

        // $instance is the copy read before the suspension, as a slow check would hold it.
        $this->assertFalse(app(FederationAddressService::class)->adopt($instance, null, FederationAddressService::HOW_REDIRECT));
        $this->assertSame('https://93.184.216.34', $instance->fresh()->site_url);
    }

    /** Only approved installs are checked: a pending or suspended one is a human's decision. */
    public function test_a_suspended_installs_claim_is_never_checked(): void
    {
        Http::fake();
        $this->claimingInstance(['status' => FederatedInstance::STATUS_SUSPENDED]);

        $this->assertSame(0, app(FederationAddressService::class)->verifyByRedirect());
        Http::assertNothingSent();
    }
}
