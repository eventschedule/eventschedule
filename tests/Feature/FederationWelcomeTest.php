<?php

namespace Tests\Feature;

use App\Jobs\SendFederationWelcome;
use App\Mail\FederationInstanceReviewed;
use App\Mail\FederationInstanceWelcome;
use App\Models\FederatedEvent;
use App\Models\FederatedInstance;
use App\Models\User;
use App\Services\FederationService;
use App\Services\FederationWelcomeService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The welcome an operator gets once their install is approved on the nexus: when it goes out,
 * what it says for where the install stands, and the admin controls for installs approved
 * before it existed.
 */
class FederationWelcomeTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private string $secret = 'a-secret-long-enough-to-pass-validation-0123456789';

    private function adminActing(array $attributes = []): User
    {
        $admin = $this->createOwner(true);
        if ($attributes) {
            $admin->forceFill($attributes)->save();
        }

        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);

        return $admin;
    }

    private function makeInstance(array $attributes = []): FederatedInstance
    {
        return FederatedInstance::create(array_merge([
            'instance_id' => (string) Str::uuid(),
            'site_url' => 'https://operator.test',
            'name' => 'Operator',
            'contact_email' => 'ops@operator.test',
            'secret' => $this->secret,
            'app_version' => 'v1.0.100',
            'status' => FederatedInstance::STATUS_PENDING,
        ], $attributes));
    }

    private function makeEvent(FederatedInstance $instance, array $attributes = [], ?string $imagePath = null): FederatedEvent
    {
        $event = FederatedEvent::create(array_merge([
            'federated_instance_id' => $instance->id,
            'external_id' => Str::random(8),
            'url' => 'https://operator.test/show',
            'name' => 'Summer Show',
            'next_occurrence_at' => now()->addWeek(),
            'image_url' => 'https://operator.test/f.jpg',
        ], $attributes));

        // image_path is not fillable on purpose, and live() needs it.
        if ($imagePath) {
            $event->image_path = $imagePath;
            $event->save();
        }

        return $event;
    }

    private function approve(FederatedInstance $instance)
    {
        return $this->post(route('admin.federation.approve', UrlUtils::encodeId($instance->id)));
    }

    private function signedRegister(array $payload)
    {
        $body = json_encode($payload);

        return $this->call('POST', '/api/federation/register', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_'.str_replace('-', '_', strtoupper(FederationService::SIGNATURE_HEADER)) => 'sha256='.hash_hmac('sha256', $body, $this->secret),
        ], $body);
    }

    private function signedCall(string $endpoint, array $payload)
    {
        $body = json_encode($payload);

        return $this->call('POST', $endpoint, [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_'.str_replace('-', '_', strtoupper(FederationService::SIGNATURE_HEADER)) => 'sha256='.hash_hmac('sha256', $body, $this->secret),
        ], $body);
    }

    /** Every href in the rendered mail. */
    private function hrefs(string $html): array
    {
        preg_match_all('/href="([^"]*)"/', $html, $matches);

        return $matches[1];
    }

    // ------------------------------------------------------------------ who gets what

    public function test_the_first_approval_sends_the_welcome_not_the_decision_note(): void
    {
        Mail::fake();
        $this->adminActing();
        $instance = $this->makeInstance();

        $this->approve($instance)->assertRedirect();

        Mail::assertSent(FederationInstanceWelcome::class, fn ($mail) => $mail->hasTo('ops@operator.test'));
        Mail::assertNotSent(FederationInstanceReviewed::class);
        $this->assertNotNull($instance->fresh()->welcomed_at);
    }

    /**
     * Two copies of the same row, both believing nobody was welcomed yet - a double-clicked
     * Approve, or an approval racing a bulk one. Only the conditional update stands between
     * them and a second email, because neither in-memory model knows about the other.
     */
    public function test_two_sends_for_the_same_install_mail_once(): void
    {
        Mail::fake();
        $instance = $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED]);

        $first = FederatedInstance::find($instance->id);
        $second = FederatedInstance::find($instance->id);

        $this->assertTrue(app(FederationWelcomeService::class)->send($first));
        $this->assertFalse(app(FederationWelcomeService::class)->send($second));

        Mail::assertSent(FederationInstanceWelcome::class, 1);
    }

    public function test_bulk_approval_welcomes_each_install(): void
    {
        Mail::fake();
        $this->adminActing();
        $a = $this->makeInstance();
        $b = $this->makeInstance(['contact_email' => 'second@operator.test']);

        $this->post(route('admin.federation.bulk'), [
            'action' => 'approve',
            'hashes' => [UrlUtils::encodeId($a->id), UrlUtils::encodeId($b->id)],
        ])->assertRedirect();

        Mail::assertSent(FederationInstanceWelcome::class, 2);
    }

    /** The welcome is once per install; later decisions get the short note. */
    public function test_suspension_and_reapproval_send_the_decision_note(): void
    {
        Mail::fake();
        $this->adminActing();
        $instance = $this->makeInstance([
            'status' => FederatedInstance::STATUS_APPROVED,
            'welcomed_at' => now()->subMonth(),
        ]);

        $this->post(route('admin.federation.suspend', UrlUtils::encodeId($instance->id)))->assertRedirect();
        Mail::assertSent(FederationInstanceReviewed::class, 1);

        $this->approve($instance->fresh())->assertRedirect();

        Mail::assertSent(FederationInstanceReviewed::class, 2);
        Mail::assertNotSent(FederationInstanceWelcome::class);
    }

    public function test_an_install_without_a_contact_email_is_not_mailed_and_stays_unwelcomed(): void
    {
        Mail::fake();
        $this->adminActing();
        $instance = $this->makeInstance(['contact_email' => null]);

        $this->approve($instance)->assertRedirect();

        Mail::assertNothingSent();
        $this->assertNull($instance->fresh()->welcomed_at);
    }

    /**
     * config('app.locale') is the acting admin's language - SetUserLanguage rewrites it - so a
     * welcome built from it would reach the operator in whatever language the admin reads.
     */
    public function test_the_welcome_is_queued_in_the_operators_language_not_the_admins(): void
    {
        Queue::fake();
        $this->adminActing(['language_code' => 'he']);

        $this->approve($this->makeInstance())->assertRedirect();
        $this->approve($this->makeInstance(['locale' => 'fr', 'contact_email' => 'fr@operator.test']))->assertRedirect();

        $locales = [];
        Queue::assertPushed(SendFederationWelcome::class, function ($job) use (&$locales) {
            $property = new \ReflectionProperty($job, 'locale');
            $property->setAccessible(true);
            $locales[] = $property->getValue($job);

            return true;
        });

        sort($locales);
        $this->assertSame(['en', 'fr'], $locales);
    }

    // ------------------------------------------------------------------ what it says

    public function test_an_install_that_sent_nothing_is_told_how_to_list_schedules(): void
    {
        $instance = $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED]);
        $mail = new FederationInstanceWelcome($instance);
        $html = $mail->render();

        $this->assertStringContainsString(e(__('messages.federation_welcome_empty_title')), $html);
        // The app's own label, so the operator can find it.
        $this->assertStringContainsString(e(__('messages.federation_schedule_choice_undecided')), $html);
        $this->assertStringContainsString(marketing_url('/docs/selfhost/federation').'#per-schedule', $html);
        $mail->assertSeeInText(__('messages.federation_welcome_empty_title'));
        $mail->assertSeeInText('php artisan federation:push');
    }

    public function test_received_listings_are_announced_as_going_live_soon(): void
    {
        $instance = $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED]);
        $this->makeEvent($instance);
        $this->makeEvent($instance);

        $html = (new FederationInstanceWelcome($instance))->render();

        $this->assertStringContainsString(e(trans_choice('messages.federation_welcome_received_title', 2, ['count' => 2])), $html);
        $this->assertStringContainsString('?instance='.UrlUtils::encodeId($instance->id), $html);
    }

    /** A welcome sent long after approval - the backfill case - reports what is live. */
    public function test_live_listings_are_counted_as_live(): void
    {
        $instance = $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED]);
        $this->makeEvent($instance, [], 'federated/a.jpg');
        $this->makeEvent($instance);

        $html = (new FederationInstanceWelcome($instance))->render();

        $this->assertStringContainsString(e(trans_choice('messages.federation_welcome_live_title', 1, ['count' => 1])), $html);
        $this->assertStringNotContainsString(e(trans_choice('messages.federation_welcome_received_title', 2, ['count' => 2])), $html);
    }

    public function test_blocked_and_expired_listings_do_not_count(): void
    {
        $instance = $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED]);
        // block(), not a blocked_at attribute: it is not fillable, so create() would drop it and
        // leave an ordinary listing that the count rightly includes.
        $this->makeEvent($instance, [], 'federated/blocked.jpg')->block();
        $this->makeEvent($instance, ['next_occurrence_at' => now()->subWeek()], 'federated/old.jpg');

        $this->assertSame(1, FederatedEvent::whereNotNull('blocked_at')->count());

        $html = (new FederationInstanceWelcome($instance))->render();

        $this->assertStringContainsString(e(__('messages.federation_welcome_empty_title')), $html);
    }

    /**
     * site_url arrives on an unauthenticated registration. Every link in a mail sent under this
     * site's name must be one this site chose, and the host must not be something a mail client
     * turns into a link either.
     */
    public function test_every_link_points_at_this_site_and_the_host_is_not_linkable(): void
    {
        $instance = $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED]);
        // With the trailing slash: without it, https://eventschedule.test.evil.example would pass.
        $base = rtrim(marketing_url('/'), '/').'/';

        foreach ([false, true] as $withListings) {
            if ($withListings) {
                $this->makeEvent($instance, [], 'federated/a.jpg');
            }

            $mail = new FederationInstanceWelcome($instance);
            $html = $mail->render();
            $data = $mail->welcomeData();
            $text = view('emails.federation_instance_welcome_text', $data)->render();

            $this->assertNotEmpty($this->hrefs($html));
            foreach ($this->hrefs($html) as $href) {
                $this->assertStringStartsWith($base, $href);
            }

            foreach (['html' => $html, 'text' => $text] as $part => $body) {
                $this->assertStringNotContainsString('operator.test', $body, "the {$part} part prints a linkable host");
                $this->assertStringContainsString("operator.\u{200B}test", $body, "the {$part} part lost the host");
            }
        }
    }

    public function test_step_one_matches_what_the_install_can_do(): void
    {
        $old = $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED, 'app_version' => 'v1.0.100']);
        $new = $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED, 'app_version' => FederatedInstance::ONE_CLICK_LISTING_VERSION]);
        $unknown = $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED, 'app_version' => 'dev']);

        // The exact sentences, built the way the view builds them - the section name alone
        // would also match the subject in <title>.
        $sentence = function ($instance, string $key, array $boldKeys) {
            $mail = new FederationInstanceWelcome($instance);
            $data = $mail->welcomeData();

            return (string) $mail->bold($key, $data['labels'] + ['host' => $data['host']], $boldKeys);
        };
        $manual = fn ($instance) => $sentence($instance, 'messages.federation_welcome_step_list_body_manual', ['host', 'setting', 'option']);
        $oneClick = fn ($instance) => $sentence($instance, 'messages.federation_welcome_step_list_body_oneclick', ['host', 'section']);

        // And the steps name the app's real buttons, not words of their own.
        $this->assertStringContainsString(e(__('messages.edit_schedule')), $manual($old));
        $this->assertStringContainsString(e(__('messages.system')), $oneClick($new));

        foreach ([$old, $unknown] as $instance) {
            $html = (new FederationInstanceWelcome($instance))->render();
            $this->assertStringContainsString($manual($instance), $html);
            $this->assertStringNotContainsString($oneClick($instance), $html);
        }

        $newHtml = (new FederationInstanceWelcome($new))->render();
        $this->assertStringContainsString($oneClick($new), $newHtml);
        $this->assertStringNotContainsString($manual($new), $newHtml);
    }

    /** The tip names a version to update to, so it waits until that version is out. */
    public function test_the_update_tip_never_names_an_unreleased_version(): void
    {
        $old = $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED, 'app_version' => 'v1.0.100']);
        $new = $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED, 'app_version' => FederatedInstance::ONE_CLICK_LISTING_VERSION]);
        $tip = e(__('messages.federation_welcome_update_link'));

        config(['self-update.version_installed' => 'v1.0.131']);
        $this->assertStringNotContainsString($tip, (new FederationInstanceWelcome($old))->render());

        config(['self-update.version_installed' => FederatedInstance::ONE_CLICK_LISTING_VERSION]);
        $this->assertStringContainsString($tip, (new FederationInstanceWelcome($old))->render());
        $this->assertStringNotContainsString($tip, (new FederationInstanceWelcome($new))->render());
    }

    public function test_replies_reach_a_person(): void
    {
        $instance = $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED]);

        $this->assertTrue((new FederationInstanceWelcome($instance))->hasReplyTo(config('app.support_email')));
        $this->assertTrue((new FederationInstanceReviewed($instance))->hasReplyTo(config('app.support_email')));
    }

    // ------------------------------------------------------------------ admin controls

    public function test_the_welcome_can_be_sent_to_an_install_approved_before_it_existed(): void
    {
        Mail::fake();
        $this->adminActing();
        $instance = $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED, 'approved_at' => now()->subMonth()]);

        $this->post(route('admin.federation.welcome', UrlUtils::encodeId($instance->id)))
            ->assertRedirect()
            ->assertSessionHas('message', __('messages.federation_welcome_queued'));

        Mail::assertSent(FederationInstanceWelcome::class, 1);
        $this->assertNotNull($instance->fresh()->welcomed_at);
        $this->assertDatabaseHas('audit_logs', ['action' => 'admin.federation_welcome']);
    }

    public function test_a_resend_waits_for_the_cooldown(): void
    {
        Mail::fake();
        $this->adminActing();
        $recent = $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED, 'welcomed_at' => now()->subMinutes(2)]);
        $older = $this->makeInstance([
            'status' => FederatedInstance::STATUS_APPROVED,
            'welcomed_at' => now()->subMinutes(FederationWelcomeService::RESEND_COOLDOWN_MINUTES + 1),
            'contact_email' => 'older@operator.test',
        ]);

        $this->post(route('admin.federation.welcome', UrlUtils::encodeId($recent->id)))
            ->assertSessionHas('error', __('messages.federation_welcome_not_sent'));
        Mail::assertNothingSent();

        $this->post(route('admin.federation.welcome', UrlUtils::encodeId($older->id)))
            ->assertSessionHas('message');
        Mail::assertSent(FederationInstanceWelcome::class, fn ($mail) => $mail->hasTo('older@operator.test'));
    }

    public function test_the_welcome_needs_an_approved_install_with_an_address(): void
    {
        Mail::fake();
        $this->adminActing();
        $pending = $this->makeInstance();
        $noEmail = $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED, 'contact_email' => null]);

        foreach ([$pending, $noEmail] as $instance) {
            $this->post(route('admin.federation.welcome', UrlUtils::encodeId($instance->id)))
                ->assertSessionHas('error', __('messages.federation_welcome_unavailable'));
        }

        Mail::assertNothingSent();
    }

    public function test_the_welcome_controls_need_an_admin_on_the_nexus(): void
    {
        $instance = $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED]);

        $this->actingAs($this->createOwner());
        $this->post(route('admin.federation.welcome', UrlUtils::encodeId($instance->id)))->assertRedirect(route('home'));

        $this->adminActing();
        config(['app.is_nexus' => false]);
        $this->post(route('admin.federation.welcome', UrlUtils::encodeId($instance->id)))->assertNotFound();
        $this->get(route('admin.federation.welcome_preview', UrlUtils::encodeId($instance->id)))->assertNotFound();
    }

    public function test_the_preview_shows_the_mail_in_the_operators_language(): void
    {
        $this->adminActing(['language_code' => 'he']);
        $instance = $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED, 'locale' => 'fr']);

        $this->get(route('admin.federation.welcome_preview', UrlUtils::encodeId($instance->id)))
            ->assertOk()
            ->assertSee(trans('messages.federation_welcome_heading', [], 'fr'));
    }

    public function test_the_bulk_welcome_skips_installs_already_welcomed(): void
    {
        Mail::fake();
        $this->adminActing();
        $welcomed = $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED, 'welcomed_at' => now()->subDay()]);
        $fresh = $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED, 'contact_email' => 'fresh@operator.test']);
        $pending = $this->makeInstance(['contact_email' => 'pending@operator.test']);

        $this->post(route('admin.federation.bulk'), [
            'action' => 'welcome',
            'hashes' => array_map(fn ($i) => UrlUtils::encodeId($i->id), [$welcomed, $fresh, $pending]),
        ])->assertRedirect();

        Mail::assertSent(FederationInstanceWelcome::class, 1);
        Mail::assertSent(FederationInstanceWelcome::class, fn ($mail) => $mail->hasTo('fresh@operator.test'));
    }

    public function test_the_approved_tab_shows_the_welcome_state_and_empty_installs(): void
    {
        $this->adminActing();
        $unwelcomed = $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED, 'name' => 'Needs A Welcome']);

        $welcomeUrl = route('admin.federation.welcome', UrlUtils::encodeId($unwelcomed->id));
        // The row button, told apart from the bulk bar's button of the same wording by its target.
        $rowButton = '/formaction="'.preg_quote($welcomeUrl, '/').'"\s+class="ap-secondary-btn/';

        $before = $this->get(route('admin.federation', ['status' => 'approved']))->assertOk();
        $this->assertMatchesRegularExpression($rowButton, $before->getContent());
        $before->assertSee(__('messages.federation_no_listings_pill'))
            ->assertDontSee(__('messages.federation_resend_welcome'));

        $unwelcomed->forceFill(['welcomed_at' => now()->subHour(), 'welcomed_email' => $unwelcomed->contact_email])->save();
        // Live, not merely received: the pill is about what the network shows.
        $this->makeEvent($unwelcomed, [], 'federated/live.jpg');

        $after = $this->get(route('admin.federation', ['status' => 'approved']))->assertOk();
        $this->assertDoesNotMatchRegularExpression($rowButton, $after->getContent());
        $after->assertSee(__('messages.federation_resend_welcome'))
            ->assertSee($welcomeUrl, false)
            ->assertDontSee(__('messages.federation_no_listings_pill'));
    }

    // ------------------------------------------------------------------ intake

    /**
     * The one welcome that is not an admin action: an approved install that registered without
     * an address, sending one now. The request is signed with the install's own secret.
     */
    public function test_an_approved_install_adding_an_address_is_welcomed_once(): void
    {
        Mail::fake();
        $approved = $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED, 'contact_email' => null]);

        $payload = [
            'instance_id' => $approved->instance_id,
            'site_url' => 'https://operator.test',
            'secret' => $this->secret,
            'contact_email' => 'late@operator.test',
        ];

        $this->signedRegister($payload)->assertOk();
        $this->signedRegister($payload)->assertOk();

        Mail::assertSent(FederationInstanceWelcome::class, 1);
        Mail::assertSent(FederationInstanceWelcome::class, fn ($mail) => $mail->hasTo('late@operator.test'));
    }

    public function test_a_pending_install_adding_an_address_is_not_mailed(): void
    {
        Mail::fake();
        $pending = $this->makeInstance(['contact_email' => null]);

        $this->signedRegister([
            'instance_id' => $pending->instance_id,
            'site_url' => 'https://operator.test',
            'secret' => $this->secret,
            'contact_email' => 'someone@operator.test',
        ])->assertOk();

        Mail::assertNothingSent();
    }

    public function test_registration_keeps_a_supported_language_and_drops_anything_else(): void
    {
        $this->postJson('/api/federation/register', [
            'instance_id' => $french = (string) Str::uuid(),
            'site_url' => 'https://french.test',
            'secret' => $this->secret,
            'locale' => 'fr',
        ])->assertCreated();

        $this->postJson('/api/federation/register', [
            'instance_id' => $klingon = (string) Str::uuid(),
            'site_url' => 'https://klingon.test',
            'secret' => $this->secret,
            'locale' => 'tlh',
        ])->assertCreated();

        $this->postJson('/api/federation/register', [
            'instance_id' => $long = (string) Str::uuid(),
            'site_url' => 'https://long.test',
            'secret' => $this->secret,
            'locale' => str_repeat('x', 40),
        ])->assertCreated();

        $this->assertSame('fr', FederatedInstance::where('instance_id', $french)->value('locale'));
        $this->assertNull(FederatedInstance::where('instance_id', $klingon)->value('locale'));
        $this->assertNull(FederatedInstance::where('instance_id', $long)->value('locale'));

        // Absent on a later call (the hourly reconnect sends none): the stored one stays.
        $instance = FederatedInstance::where('instance_id', $french)->first();
        $this->signedRegister([
            'instance_id' => $french,
            'site_url' => 'https://french.test',
            'secret' => $this->secret,
        ])->assertOk();
        $this->assertSame('fr', $instance->fresh()->locale);
    }

    /** Only this site can encode the id, so every response hands the install its listings URL. */
    public function test_responses_carry_the_listings_url(): void
    {
        $response = $this->postJson('/api/federation/register', [
            'instance_id' => $id = (string) Str::uuid(),
            'site_url' => 'https://new.test',
            'secret' => $this->secret,
        ])->assertCreated();

        $instance = FederatedInstance::where('instance_id', $id)->first();
        $this->assertSame($instance->listingsUrl(), $response->json('listings_url'));
        $this->assertStringContainsString('?instance='.UrlUtils::encodeId($instance->id), $response->json('listings_url'));
    }

    // ------------------------------------------------------------------ review fixes

    /** app_version is registrant text; only a plain version number is ever printed. */
    public function test_a_junk_version_is_never_printed(): void
    {
        config(['self-update.version_installed' => FederatedInstance::ONE_CLICK_LISTING_VERSION]);
        $instance = $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED, 'app_version' => 'verify.example']);

        $mail = new FederationInstanceWelcome($instance);
        $html = $mail->render();
        $text = view('emails.federation_instance_welcome_text', $mail->welcomeData())->render();

        foreach ([$html, $text] as $body) {
            $this->assertStringNotContainsString('verify.example', $body);
            $this->assertStringNotContainsString(__('messages.federation_welcome_update_link'), $body);
        }
        // Still told how to do it by hand.
        $this->assertStringContainsString(e(__('messages.federation_schedule_toggle')), $html);
    }

    /** A welcome queued just before a suspension is built after it, and must not report live listings. */
    public function test_a_suspended_install_is_not_told_its_listings_are_live(): void
    {
        $instance = $this->makeInstance(['status' => FederatedInstance::STATUS_SUSPENDED]);
        $this->makeEvent($instance, [], 'federated/a.jpg');

        $data = (new FederationInstanceWelcome($instance))->welcomeData();

        $this->assertSame(0, $data['liveCount']);
        $this->assertNotSame('live', $data['state']);
    }

    /** A listing whose image could not be fetched can never go live, so it is not promised. */
    public function test_a_listing_whose_image_fetch_failed_is_not_counted(): void
    {
        $instance = $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED]);
        $this->makeEvent($instance, ['image_url' => null]);

        $this->assertSame('empty', (new FederationInstanceWelcome($instance))->welcomeData()['state']);
    }

    /**
     * A send that fails hands the claim back, so the admin screen offers it again - for the
     * first send, and for a resend, which gets its previous values back.
     */
    public function test_a_failed_send_hands_the_claim_back(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP is down'));
        $service = app(FederationWelcomeService::class);

        $fresh = $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED]);
        $this->assertFalse($service->send($fresh));
        $this->assertNull($fresh->fresh()->welcomed_at);
        $this->assertNull($fresh->fresh()->welcomed_email);

        $sentBefore = now()->subHour()->startOfSecond();
        $again = $this->makeInstance([
            'status' => FederatedInstance::STATUS_APPROVED,
            'contact_email' => 'new@operator.test',
            'welcomed_at' => $sentBefore,
            'welcomed_email' => 'old@operator.test',
        ]);
        $this->assertFalse($service->resend($again));
        $this->assertTrue($again->fresh()->welcomed_at->equalTo($sentBefore));
        $this->assertSame('old@operator.test', $again->fresh()->welcomed_email);
    }

    /** The worker-side hook only releases its own claim, never a newer one. */
    public function test_the_failure_hook_releases_only_its_own_claim(): void
    {
        $claimedAt = now()->subMinutes(5)->startOfSecond();
        $instance = $this->makeInstance([
            'status' => FederatedInstance::STATUS_APPROVED,
            'welcomed_at' => $claimedAt,
            'welcomed_email' => 'ops@operator.test',
        ]);

        $newer = now()->startOfSecond();
        $instance->forceFill(['welcomed_at' => $newer])->save();
        (new SendFederationWelcome($instance, $claimedAt->format('Y-m-d H:i:s')))->failed();
        $this->assertTrue($instance->fresh()->welcomed_at->equalTo($newer));

        $instance->forceFill(['welcomed_at' => $claimedAt])->save();
        (new SendFederationWelcome($instance, $claimedAt->format('Y-m-d H:i:s')))->failed();
        $this->assertNull($instance->fresh()->welcomed_at);
        $this->assertNull($instance->fresh()->welcomed_email);
    }

    public function test_two_resends_for_the_same_install_mail_once(): void
    {
        Mail::fake();
        $instance = $this->makeInstance([
            'status' => FederatedInstance::STATUS_APPROVED,
            'welcomed_at' => now()->subHour(),
            'welcomed_email' => 'ops@operator.test',
        ]);

        $first = FederatedInstance::find($instance->id);
        $second = FederatedInstance::find($instance->id);

        $this->assertTrue(app(FederationWelcomeService::class)->resend($first));
        $this->assertFalse(app(FederationWelcomeService::class)->resend($second));

        Mail::assertSent(FederationInstanceWelcome::class, 1);
    }

    /** A mistyped address never gets the steps; the row says when the address has changed since. */
    public function test_a_changed_address_is_shown_but_never_mailed_on_its_own(): void
    {
        Mail::fake();
        $this->adminActing();
        $instance = $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED]);

        app(FederationWelcomeService::class)->send($instance);
        $this->assertSame('ops@operator.test', $instance->fresh()->welcomed_email);

        $this->get(route('admin.federation', ['status' => 'approved']))
            ->assertDontSee(__('messages.federation_welcome_email_changed'));

        $instance->forceFill(['contact_email' => 'fixed@operator.test'])->save();

        $this->get(route('admin.federation', ['status' => 'approved']))
            ->assertSee(__('messages.federation_welcome_email_changed'));
        Mail::assertSent(FederationInstanceWelcome::class, 1);

        // With no address on record there is nothing to compare against.
        $instance->forceFill(['welcomed_email' => null])->save();

        $this->assertFalse(app(FederationWelcomeService::class)->addressChangedSinceWelcome($instance->fresh()));
    }

    /**
     * Suspending junk is how the queue is cleared, and a junk row's address, name and site are
     * the registrant's. Only an install that was welcomed hears about a suspension.
     */
    public function test_suspending_an_unreviewed_registration_sends_nothing(): void
    {
        Mail::fake();
        $this->adminActing();
        $junk = $this->makeInstance();
        $moved = $this->makeInstance(['contact_email' => 'moved@operator.test', 'welcomed_at' => now()->subMonth()]);

        $this->post(route('admin.federation.suspend', UrlUtils::encodeId($junk->id)))->assertRedirect();
        Mail::assertNothingSent();

        $this->post(route('admin.federation.suspend', UrlUtils::encodeId($moved->id)))->assertRedirect();
        Mail::assertSent(FederationInstanceReviewed::class, fn ($mail) => $mail->hasTo('moved@operator.test'));
    }

    public function test_the_decision_note_prints_only_a_link_proof_host(): void
    {
        $instance = $this->makeInstance([
            'status' => FederatedInstance::STATUS_SUSPENDED,
            'name' => 'Claim your prize at prize.example',
        ]);

        $mail = new FederationInstanceReviewed($instance);
        $html = $mail->render();
        $content = $mail->content();
        $text = view($content->text, $content->with)->render();

        foreach (['html' => $html, 'text' => $text] as $part => $body) {
            $this->assertStringNotContainsString('prize', $body, "the {$part} part prints the registrant's name");
            $this->assertStringNotContainsString('operator.test', $body, "the {$part} part prints a linkable host");
            $this->assertStringContainsString("operator.\u{200B}test", $body);
        }

        // Plain text is not HTML: in French the copy has apostrophes, which {{ }} turned into
        // entities at the reader.
        app()->setLocale('fr');
        $frenchText = view($content->text, $content->with)->render();
        app()->setLocale('en');
        $this->assertStringContainsString("'", $frenchText);
        $this->assertStringNotContainsString('&#039;', $frenchText);
    }

    /** An install that updated after registering reports its version on every signed call. */
    public function test_a_signed_sync_updates_the_reported_version(): void
    {
        $instance = $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED, 'app_version' => 'v1.0.120']);

        $reconcile = fn (array $extra) => $this->signedCall('/api/federation/reconcile', array_merge([
            'instance_id' => $instance->instance_id,
            'site_url' => 'https://operator.test',
            'external_ids' => [],
            'run_token' => Str::random(8),
        ], $extra));

        $reconcile(['app_version' => FederatedInstance::ONE_CLICK_LISTING_VERSION])->assertOk();
        $this->assertSame(FederatedInstance::ONE_CLICK_LISTING_VERSION, $instance->fresh()->app_version);
        $this->assertTrue($instance->fresh()->supportsOneClickListing());

        $reconcile(['app_version' => str_repeat('9', 40)])->assertOk();
        $reconcile([])->assertOk();
        $this->assertSame(FederatedInstance::ONE_CLICK_LISTING_VERSION, $instance->fresh()->app_version);
    }

    /**
     * Every legacy install re-registers once after updating, with the same address. That must not
     * mail anyone: the welcome goes to those installs when the admin chooses to send it.
     */
    public function test_an_approved_install_re_registering_with_the_same_address_is_not_mailed(): void
    {
        Mail::fake();
        $legacy = $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED]);

        $this->signedRegister([
            'instance_id' => $legacy->instance_id,
            'site_url' => 'https://operator.test',
            'secret' => $this->secret,
            'contact_email' => 'ops@operator.test',
        ])->assertOk();

        Mail::assertNothingSent();
        $this->assertNull($legacy->fresh()->welcomed_at);
    }

    public function test_the_admin_screen_offers_the_right_controls(): void
    {
        $this->adminActing();
        $pending = $this->makeInstance(['name' => 'Pending One']);
        $welcomedSuspended = $this->makeInstance([
            'status' => FederatedInstance::STATUS_SUSPENDED,
            'contact_email' => 'was@operator.test',
            'welcomed_at' => now()->subMonth(),
            'site_url' => 'https://banned.test',
        ]);
        $sameHost = $this->makeInstance(['site_url' => 'https://banned.test/events', 'name' => 'Came Back']);
        $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED, 'name' => 'Approved One']);

        $pendingPage = $this->get(route('admin.federation', ['status' => 'pending']))->assertOk();
        $content = $pendingPage->getContent();

        // Enter in the form clicks a disabled default button, not a row action.
        $this->assertMatchesRegularExpression('/<form method="POST" action="[^"]*\/admin\/federation\/bulk"[^>]*>\s*<input[^>]*name="_token"[^>]*>\s*<button type="submit" disabled/', $content);
        // Preview on pending rows too; no bulk welcome on this tab.
        $pendingPage->assertSee(route('admin.federation.welcome_preview', UrlUtils::encodeId($pending->id)), false)
            ->assertDontSee(__('messages.federation_bulk_welcome'))
            ->assertSee(__('messages.federation_approve_hint', ['email' => 'ops@operator.test']), false)
            ->assertSee(__('messages.federation_same_host_suspended_warning'));

        // A welcomed install being re-approved gets the short note, so no setup-steps hint.
        $this->get(route('admin.federation', ['status' => 'suspended']))
            ->assertOk()
            ->assertDontSee(__('messages.federation_approve_hint', ['email' => 'was@operator.test']), false);

        $this->get(route('admin.federation', ['status' => 'approved']))
            ->assertSee(__('messages.federation_bulk_welcome'));
    }

    // ------------------------------------------------------------------ translations

    /**
     * Every language defines every key this feature added or reworded, and none of them is the
     * English string. Read from the files: __() falls back to English, so a render-based check
     * passes with a language missing entirely.
     */
    public function test_every_language_defines_its_own_copy(): void
    {
        $en = require resource_path('lang/en/messages.php');

        $prefixes = ['federation_welcome_', 'federation_state_', 'federation_pill_', 'federation_listing_prompt_', 'federation_listed_', 'federation_saved_'];
        $named = [
            'federation_send_welcome', 'federation_bulk_welcome', 'federation_resend_welcome',
            'federation_no_listings_pill', 'federation_approve_hint', 'federation_approved_on',
            'federation_see_listings', 'federation_list_these_title', 'federation_list_these_help',
            'federation_would_share_count', 'federation_needs_verification',
            'federation_preview_empty_tick', 'federation_preview_empty_rules',
            'federation_rereview_warning', 'federation_reapproved_intro',
            'federation_contact_email_note', 'federation_undecided_others_count',
            'federation_error_rejected_reconnecting', 'federation_listing_none_ticked',
            'federation_same_host_suspended_warning', 'federation_approved_next',
        ];

        $keys = array_values(array_filter(array_keys($en), fn ($key) => in_array($key, $named, true)
            || collect($prefixes)->contains(fn ($prefix) => str_starts_with($key, $prefix))));

        // Guard the guard: a prefix typo would quietly check nothing.
        $this->assertGreaterThan(60, count($keys));

        $renamedAway = ['federation_approved_intro', 'federation_error_rejected', 'federation_undecided_count', 'federation_contact_email_help'];

        foreach (array_keys(config('app.supported_languages')) as $lang) {
            $lines = require resource_path("lang/{$lang}/messages.php");

            foreach ($renamedAway as $old) {
                $this->assertArrayNotHasKey($old, $lines, "{$lang} still has the renamed key {$old}");
            }

            foreach ($keys as $key) {
                $this->assertArrayHasKey($key, $lines, "{$lang} is missing {$key}");

                if ($lang !== 'en') {
                    $this->assertNotSame($en[$key], $lines[$key], "{$lang}/{$key} is the English string copied over");
                }

                preg_match_all('/:[a-z_]+/', $en[$key], $expected);
                foreach (array_unique($expected[0]) as $placeholder) {
                    $this->assertStringContainsString($placeholder, $lines[$key], "{$lang}/{$key} lost {$placeholder}");
                }

                $this->assertSame(
                    substr_count($en[$key], '|'),
                    substr_count($lines[$key], '|'),
                    "{$lang}/{$key} does not keep the plural segments"
                );

                $this->assertStringNotContainsString("\u{2014}", $lines[$key], "{$lang}/{$key} uses an em-dash");
            }
        }
    }
}
