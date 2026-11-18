<?php

namespace Tests\Feature;

use App\Jobs\ProcessScheduledNewsletters;
use App\Jobs\SendNewsletterBatch;
use App\Jobs\SendQueuedEmail;
use App\Mail\EventInterestNotification;
use App\Mail\NewsletterRepeatSkippedMail;
use App\Models\EventInterest;
use App\Models\LegalDocument;
use App\Models\Newsletter;
use App\Models\NewsletterAbTest;
use App\Models\NewsletterRecipient;
use App\Models\NewsletterSegment;
use App\Models\NewsletterSegmentUser;
use App\Models\Role;
use App\Models\RoleSubscriber;
use App\Models\Setting;
use App\Models\TicketWaitlist;
use App\Models\User;
use App\Services\AccountDeletionService;
use App\Services\AdminAlertService;
use App\Services\AudienceMailGuard;
use App\Services\AudienceResolver;
use App\Services\BackupService;
use App\Services\EmailService;
use App\Services\EventChangeNotifier;
use App\Services\NewsletterService;
use App\Services\SpamWatch;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Stripe\PaymentMethod;
use Stripe\StripeClient;
use Stripe\Subscription;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * AudienceMailGuard, after a new account on a free trial imported a list and sent a phishing
 * newsletter through the platform's own sender (2026-11-18):
 *
 *  A. imported and hand-added addresses are mailed only once the owner has paid;
 *  B. a newer sender's newsletter to more than a handful, or any with a watched word, is held for
 *     review, and no runner sends a held one;
 *  C. email sending can be paused on an account (a watched word, a card used on another account,
 *     an operator), with a request to have it back;
 *  D. a trial sends the free plan's newsletter allowance until the first payment.
 *
 * Every rule stands down under app.is_testing, as canSendAudienceMail() does, so each test turns
 * it off: without that they would pass whatever the guard did.
 */
class NewsletterAntiAbuseTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();

        config([
            'app.hosted' => true,
            'app.is_testing' => false,
            'auth.admin_requires_two_factor' => false,
            'queue.default' => 'database',
            'services.stripe_platform.price_monthly' => 'price_pro_m',
            'services.stripe_platform.price_yearly' => 'price_pro_y',
            'services.stripe_platform.enterprise_price_monthly' => 'price_ent_m',
            'services.stripe_platform.enterprise_price_yearly' => 'price_ent_y',
        ]);

        // The owner and every schedule in this file are free of watched words.
        Setting::set(SpamWatch::SETTING, 'bitcoin, heroconf');
        Cache::flush();

        Queue::fake();
        Mail::fake();
    }

    /**
     * Every request goes to the app's own host. With app.is_testing off, as the guard needs it,
     * RedirectToAppSubdomain sends any other host there first, and the redirect is all a test
     * would see.
     */
    private bool $onAppHost = true;

    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        if (! $this->onAppHost) {
            return parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);
        }

        $path = (string) parse_url($uri, PHP_URL_PATH);
        $query = parse_url($uri, PHP_URL_QUERY);

        return parent::call($method, app_url($path.($query ? '?'.$query : '')), $parameters, $cookies, $files, $server, $content);
    }

    // ── fixtures ─────────────────────────────────────────────────────────

    private function owner(array $attrs = []): User
    {
        $user = $this->createOwner();
        $user->forceFill($attrs + ['name' => 'Dana Organizer'])->save();

        return $user->fresh();
    }

    /** A schedule whose plan is a legacy one: Enterprise features, nothing paid. */
    private function schedule(User $owner, array $attrs = []): Role
    {
        return $this->createRole($owner, 'venue', $attrs + ['name' => 'Open Stage']);
    }

    /** $paid: Stripe reported an invoice above zero paid (by default, an active one past its trial). */
    private function subscribe(Role $role, string $status = 'active', ?Carbon $trialEnds = null, string $price = 'price_pro_m', ?bool $paid = null): void
    {
        $paid ??= $status === 'active' && (! $trialEnds || $trialEnds->isPast());

        DB::table('subscriptions')->insert([
            'last_paid_at' => $paid ? now() : null,
            'role_id' => $role->id,
            'type' => 'default',
            'stripe_id' => 'sub_'.Str::random(12),
            'stripe_status' => $status,
            'stripe_price' => $price,
            'quantity' => 1,
            'trial_ends_at' => $trialEnds,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $role->unsetRelation('subscriptions');
    }

    /** A free schedule that has never had a plan, so its checkout starts a trial. */
    private function trialEligible(User $owner): Role
    {
        $role = $this->createFreeRole($owner);
        $role->forceFill(['plan_expires' => null])->save();

        return $role->fresh();
    }

    /** A schedule on a Stripe trial: the plan's features, nothing paid. */
    private function trialing(User $owner, string $price = 'price_pro_m'): Role
    {
        $role = $this->schedule($owner, ['plan_type' => $price === 'price_ent_m' ? 'enterprise' : 'pro', 'plan_expires' => null]);
        $this->subscribe($role, 'trialing', now()->addDays(7), $price);

        return $role->fresh();
    }

    /** @return list<string> */
    private function subscribers(Role $role, int $count, string $prefix = 'fan'): array
    {
        $emails = [];
        for ($i = 1; $i <= $count; $i++) {
            $email = $prefix.$i.'-'.Str::lower(Str::random(5)).'@mailbox.org';
            RoleSubscriber::create([
                'role_id' => $role->id,
                'email' => $email,
                'name' => 'Fan '.$i,
                'token' => Str::random(40),
                'confirm_token' => Str::random(40),
                'confirmed_at' => now(),
            ]);
            $emails[] = $email;
        }

        return $emails;
    }

    private function importedSegment(Role $role, array $emails): NewsletterSegment
    {
        $segment = NewsletterSegment::create(['role_id' => $role->id, 'name' => 'Imported list', 'type' => 'manual']);
        foreach ($emails as $email) {
            NewsletterSegmentUser::create(['newsletter_segment_id' => $segment->id, 'email' => $email, 'name' => null, 'created_at' => now()]);
        }

        return $segment;
    }

    private function subscriberSegment(Role $role): NewsletterSegment
    {
        return NewsletterSegment::create(['role_id' => $role->id, 'name' => 'Subscribers', 'type' => 'all_subscribers']);
    }

    private function newsletter(Role $role, array $attrs = []): Newsletter
    {
        $newsletter = new Newsletter;
        $newsletter->forceFill($attrs + [
            'role_id' => $role->id,
            'user_id' => $role->user_id,
            'subject' => 'Our autumn season',
            'blocks' => [['type' => 'text', 'data' => ['content' => 'See you there.']]],
            'template' => 'modern',
            'status' => 'draft',
            'type' => 'schedule',
        ])->save();

        return $newsletter->fresh();
    }

    /** @return list<string> */
    private function recipientsOf(Newsletter $newsletter): array
    {
        return NewsletterRecipient::where('newsletter_id', $newsletter->id)->orderBy('email')->pluck('email')->all();
    }

    private function asAdmin(): User
    {
        $admin = $this->createOwner(true);
        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);

        return $admin;
    }

    private function sendUrl(Role $role, Newsletter $newsletter): string
    {
        return route('newsletter.send', ['hash' => UrlUtils::encodeId($newsletter->id), 'role_id' => UrlUtils::encodeId($role->id)]);
    }

    // ── A. imported and hand-added addresses ─────────────────────────────

    public function test_imported_addresses_wait_for_the_first_payment_on_a_trial(): void
    {
        $owner = $this->owner();
        $role = $this->trialing($owner);
        $fans = $this->subscribers($role, 2);
        // One imported address that also subscribed itself is opted in, and stays.
        $imported = $this->importedSegment($role, ['lead1@mailbox.org', 'lead2@mailbox.org', $fans[0]]);
        $newsletter = $this->newsletter($role, ['segment_ids' => [$imported->id, $this->subscriberSegment($role)->id]]);

        $this->assertTrue(app(NewsletterService::class)->send($newsletter));

        $sent = $this->recipientsOf($newsletter);
        $this->assertContains($fans[0], $sent);
        $this->assertContains($fans[1], $sent);
        $this->assertNotContains('lead1@mailbox.org', $sent);
        $this->assertNotContains('lead2@mailbox.org', $sent);
    }

    public function test_a_newsletter_only_to_imported_addresses_is_refused_for_a_free_owner(): void
    {
        $owner = $this->owner();
        $role = $this->createFreeRole($owner);
        $imported = $this->importedSegment($role, ['lead1@mailbox.org', 'lead2@mailbox.org']);
        $newsletter = $this->newsletter($role, ['segment_ids' => [$imported->id]]);

        $this->actingAs($owner)
            ->post($this->sendUrl($role, $newsletter))
            ->assertSessionHas('error', __('messages.newsletter_imported_unpaid'));

        $this->assertSame('draft', $newsletter->fresh()->status);
        $this->assertSame([], $this->recipientsOf($newsletter));

        // The service refuses it on its own as well, whatever the controller does.
        $this->assertSame(['imported_unpaid', 0], app(NewsletterService::class)->send($newsletter->fresh()));
    }

    public function test_a_paid_subscription_mails_imported_addresses(): void
    {
        $owner = $this->owner();
        $role = $this->schedule($owner, ['plan_type' => 'pro', 'plan_expires' => null]);
        $this->subscribe($role, 'active');
        $imported = $this->importedSegment($role, ['lead1@mailbox.org', 'lead2@mailbox.org']);
        $newsletter = $this->newsletter($role, ['segment_ids' => [$imported->id]]);

        $this->assertTrue(app(NewsletterService::class)->send($newsletter));
        $this->assertSame(['lead1@mailbox.org', 'lead2@mailbox.org'], array_values(array_intersect($this->recipientsOf($newsletter), ['lead1@mailbox.org', 'lead2@mailbox.org'])));
    }

    public function test_a_payment_on_another_schedule_of_the_owner_counts_and_a_trial_does_not(): void
    {
        $owner = $this->owner();
        $role = $this->trialing($owner);
        $this->assertFalse($role->mayMailUnsolicitedAudience());

        $other = $this->schedule($owner, ['plan_type' => 'pro', 'plan_expires' => null]);
        // past_due alone is no payment (a trial whose card failed at its end).
        $this->subscribe($other, 'past_due', null, 'price_pro_m', false);
        $this->assertFalse($role->fresh()->mayMailUnsolicitedAudience());

        DB::table('subscriptions')->where('role_id', $other->id)->update(['last_paid_at' => now()]);

        $this->assertTrue($role->fresh()->mayMailUnsolicitedAudience());
    }

    public function test_an_operator_granted_plan_counts_as_paid(): void
    {
        $owner = $this->owner();
        $role = $this->schedule($owner, ['plan_source' => 'admin']);

        $this->assertTrue($role->mayMailUnsolicitedAudience());
        // The same plan with no operator behind it (the legacy free year) does not.
        $this->assertFalse($this->schedule($this->owner())->mayMailUnsolicitedAudience());
    }

    public function test_own_email_settings_and_selfhost_mail_imported_addresses(): void
    {
        $owner = $this->owner();
        $role = $this->trialing($owner);
        $imported = $this->importedSegment($role, ['lead1@mailbox.org']);

        $role->email_settings = ['host' => 'smtp.mailbox.org', 'port' => 587, 'username' => 'me', 'password' => 'secret', 'encryption' => 'tls'];
        $role->save();
        $newsletter = $this->newsletter($role->fresh(), ['segment_ids' => [$imported->id]]);
        $this->assertTrue(app(NewsletterService::class)->send($newsletter));
        $this->assertContains('lead1@mailbox.org', $this->recipientsOf($newsletter));

        config(['app.hosted' => false]);
        $plain = $this->schedule($this->owner());
        $selfhost = $this->newsletter($plain, ['segment_ids' => [$this->importedSegment($plain, ['lead2@mailbox.org'])->id]]);
        $this->assertTrue(app(NewsletterService::class)->send($selfhost));
        $this->assertContains('lead2@mailbox.org', $this->recipientsOf($selfhost));
    }

    public function test_the_scheduled_runner_refuses_imported_addresses(): void
    {
        $role = $this->trialing($this->owner());
        $imported = $this->importedSegment($role, ['lead1@mailbox.org']);
        $newsletter = $this->newsletter($role, ['segment_ids' => [$imported->id], 'status' => 'scheduled', 'scheduled_at' => now()->subMinute()]);

        (new ProcessScheduledNewsletters)();

        $this->assertSame('draft', $newsletter->fresh()->status);
        $this->assertSame([], $this->recipientsOf($newsletter));
    }

    public function test_a_repeat_run_to_imported_addresses_is_skipped_and_said(): void
    {
        $role = $this->trialing($this->owner());
        $imported = $this->importedSegment($role, ['lead1@mailbox.org']);
        $due = now()->subMinute()->startOfMinute();
        $parent = $this->newsletter($role, [
            'segment_ids' => [$imported->id],
            'status' => 'scheduled',
            'scheduled_at' => $due,
            'repeat' => ['every' => 'month', 'at' => $due->copy()->setTimezone($role->timezone)->format('Y-m-d H:i')],
        ]);

        $this->assertSame('skipped', app(NewsletterService::class)->runRepeat($parent));
        $this->assertSame(0, Newsletter::where('repeat_parent_id', $parent->id)->count());
        Mail::assertSent(NewsletterRepeatSkippedMail::class, fn ($mail) => $mail->reason === 'imported_unpaid');
    }

    public function test_an_ab_test_to_imported_addresses_is_refused(): void
    {
        $owner = $this->owner();
        $role = $this->trialing($owner);
        $imported = $this->importedSegment($role, ['lead1@mailbox.org', 'lead2@mailbox.org']);
        $test = NewsletterAbTest::create(['role_id' => $role->id, 'name' => 'Subject test', 'test_field' => 'subject', 'sample_percentage' => 50, 'winner_criteria' => 'open_rate', 'winner_wait_hours' => 4, 'status' => 'pending']);
        $a = $this->newsletter($role, ['segment_ids' => [$imported->id], 'ab_test_id' => $test->id, 'ab_variant' => 'A']);
        $this->newsletter($role, ['segment_ids' => [$imported->id], 'ab_test_id' => $test->id, 'ab_variant' => 'B', 'subject' => 'Other subject']);

        $this->actingAs($owner)
            ->post(route('newsletter.ab_send', ['hash' => UrlUtils::encodeId($a->id), 'role_id' => UrlUtils::encodeId($role->id)]))
            ->assertSessionHas('error', __('messages.newsletter_imported_unpaid'));

        $this->assertSame('pending', $test->fresh()->status);
        $this->assertSame(0, NewsletterRecipient::whereIn('newsletter_id', $test->newsletters()->pluck('id'))->count());
    }

    public function test_the_send_step_says_what_is_left_out_and_what_waits(): void
    {
        $owner = $this->owner();
        $role = $this->schedule($owner);
        $this->subscribers($role, 11);
        $imported = $this->importedSegment($role, ['lead1@mailbox.org', 'lead2@mailbox.org']);
        $subscribers = $this->subscriberSegment($role);
        $newsletter = $this->newsletter($role, ['segment_ids' => [$imported->id, $subscribers->id]]);

        $audience = $this->actingAs($owner)->getJson(route('newsletter.audience', [
            'role_id' => UrlUtils::encodeId($role->id),
            'segment_ids' => [$imported->id, $subscribers->id],
            'newsletter' => UrlUtils::encodeId($newsletter->id),
        ]))->assertOk()->json();

        $this->assertNull($audience['refusal']);
        $this->assertContains(trans_choice('messages.newsletter_imported_left_out', 2, ['count' => 2]), $audience['notices']);
        $this->assertContains(__('messages.newsletter_will_be_reviewed'), $audience['notices']);

        // Imported addresses alone: refused before anything is pressed.
        $only = $this->actingAs($owner)->getJson(route('newsletter.audience', [
            'role_id' => UrlUtils::encodeId($role->id),
            'segment_ids' => [$imported->id],
        ]))->assertOk()->json();
        $this->assertSame(__('messages.newsletter_imported_unpaid'), $only['refusal']['message']);
    }

    public function test_the_import_page_says_it_up_front(): void
    {
        $owner = $this->owner();
        $role = $this->trialing($owner);

        $this->actingAs($owner)
            ->get(route('newsletter.import', ['role_id' => UrlUtils::encodeId($role->id)]))
            ->assertOk()
            ->assertSee(__('messages.newsletter_imports_wait_for_payment'));
    }

    // ── B. review ────────────────────────────────────────────────────────

    public function test_a_new_accounts_first_newsletter_to_more_than_ten_is_held(): void
    {
        $owner = $this->owner();
        $role = $this->schedule($owner);
        $this->subscribers($role, 11);
        $newsletter = $this->newsletter($role);

        $this->actingAs($owner)
            ->post($this->sendUrl($role, $newsletter))
            ->assertRedirect(route('newsletter.index', ['role_id' => UrlUtils::encodeId($role->id)]))
            ->assertSessionHas('status', __('messages.newsletter_held_for_review'));

        $this->assertSame('review', $newsletter->fresh()->status);
        $this->assertNotNull($newsletter->fresh()->held_at);
        $this->assertSame([], $this->recipientsOf($newsletter));

        // Said on the list and on the newsletter.
        $this->actingAs($owner)->get(route('newsletter.index', ['role_id' => UrlUtils::encodeId($role->id)]))
            ->assertOk()->assertSee(__('messages.newsletter_in_review_title'))->assertSee(__('messages.newsletter_status_review'));
        $this->actingAs($owner)->get(route('newsletter.edit', ['hash' => UrlUtils::encodeId($newsletter->id), 'role_id' => UrlUtils::encodeId($role->id)]))
            ->assertOk()->assertSee(__('messages.newsletter_in_review_title'));
    }

    public function test_a_held_scheduled_newsletter_is_never_sent_by_the_runner(): void
    {
        $role = $this->schedule($this->owner());
        $this->subscribers($role, 11);
        $newsletter = $this->newsletter($role, ['status' => 'scheduled', 'scheduled_at' => now()->subMinute()]);

        (new ProcessScheduledNewsletters)();
        $this->assertSame('review', $newsletter->fresh()->status);

        // Every later tick, and the stuck-send recovery, leave it where it is.
        $this->travel(2)->hours();
        (new ProcessScheduledNewsletters)();
        $this->assertSame('review', $newsletter->fresh()->status);
        $this->assertSame([], $this->recipientsOf($newsletter));

        // And send() never claims it.
        $this->assertFalse(app(NewsletterService::class)->send($newsletter->fresh()));
    }

    public function test_a_repeat_copy_waits_for_review_and_the_series_moves_on(): void
    {
        $role = $this->schedule($this->owner());
        $this->subscribers($role, 11);
        $due = now()->subMinute()->startOfMinute();
        $parent = $this->newsletter($role, [
            'status' => 'scheduled',
            'scheduled_at' => $due,
            'repeat' => ['every' => 'month', 'at' => $due->copy()->setTimezone($role->timezone)->format('Y-m-d H:i')],
        ]);

        $this->assertSame('held', app(NewsletterService::class)->runRepeat($parent));
        $copy = Newsletter::where('repeat_parent_id', $parent->id)->first();
        $this->assertSame('review', $copy->status);
        Mail::assertNotSent(NewsletterRepeatSkippedMail::class);
    }

    public function test_an_operator_approves_and_the_owner_is_trusted(): void
    {
        $owner = $this->owner();
        $role = $this->schedule($owner);
        $this->subscribers($role, 11);
        $newsletter = $this->newsletter($role);
        app(NewsletterService::class)->send($newsletter);
        $this->assertSame('review', $newsletter->fresh()->status);

        $this->asAdmin();
        $this->get(route('admin.mail_review'))->assertOk()->assertSee('Our autumn season')->assertSee($owner->email);
        $this->post(route('admin.mail_review.approve', ['hash' => UrlUtils::encodeId($newsletter->id)]))
            ->assertRedirect(route('admin.mail_review'))
            ->assertSessionHas('success', __('messages.mail_review_approved'));

        $this->assertSame('sending', $newsletter->fresh()->status);
        $this->assertCount(12, $this->recipientsOf($newsletter));
        $this->assertNotNull($owner->fresh()->newsletter_trusted_at);

        // The next one is not held.
        $next = $this->newsletter($role->fresh());
        $this->assertTrue(app(NewsletterService::class)->send($next));
    }

    public function test_an_operator_sends_it_back_with_a_note(): void
    {
        $owner = $this->owner();
        $role = $this->schedule($owner);
        $this->subscribers($role, 11);
        $newsletter = $this->newsletter($role);
        app(NewsletterService::class)->send($newsletter);

        $this->asAdmin();
        $this->post(route('admin.mail_review.reject', ['hash' => UrlUtils::encodeId($newsletter->id)]), ['note' => 'Please name your real venue.'])
            ->assertSessionHas('success');

        $fresh = $newsletter->fresh();
        $this->assertSame('draft', $fresh->status);
        $this->assertSame('Please name your real venue.', $fresh->review_note);
        $this->assertNull($owner->fresh()->newsletter_trusted_at);
        $this->assertSame([], $this->recipientsOf($newsletter));

        $this->actingAs($owner)->get(route('newsletter.edit', ['hash' => UrlUtils::encodeId($newsletter->id), 'role_id' => UrlUtils::encodeId($role->id)]))
            ->assertOk()->assertSee('Please name your real venue.');
    }

    public function test_a_trusted_owner_is_not_held(): void
    {
        // Older than thirty days and paid.
        $owner = $this->owner();
        $owner->forceFill(['created_at' => now()->subDays(40)])->save();
        $role = $this->schedule($owner, ['plan_source' => 'admin']);
        $this->subscribers($role, 11);
        $this->assertTrue(app(NewsletterService::class)->send($this->newsletter($role)));

        // Approved before.
        $other = $this->owner(['newsletter_trusted_at' => now()]);
        $otherRole = $this->schedule($other);
        $this->subscribers($otherRole, 11);
        $this->assertTrue(app(NewsletterService::class)->send($this->newsletter($otherRole)));
    }

    public function test_a_small_newsletter_is_not_held(): void
    {
        $role = $this->schedule($this->owner());
        $this->subscribers($role, 5);

        $this->assertTrue(app(NewsletterService::class)->send($this->newsletter($role)));
    }

    public function test_a_watched_word_holds_even_a_trusted_owners_newsletter_and_the_page_names_it(): void
    {
        $owner = $this->owner(['newsletter_trusted_at' => now()]);
        $role = $this->schedule($owner);
        $this->subscribers($role, 2);
        // In the preview text, accented, and in a block: each is found.
        $bySubject = $this->newsletter($role, ['subject' => 'Claim your BÍTCOIN refund']);
        $byBlock = $this->newsletter($role, ['blocks' => [['type' => 'button', 'data' => ['text' => 'Tickets for HeroConf', 'url' => 'https://mailbox.org']]]]);
        $byPreview = $this->newsletter($role, ['style_settings' => ['previewText' => 'heroconf 2026 is here']]);

        foreach ([$bySubject, $byBlock, $byPreview] as $newsletter) {
            $this->assertSame(['review', 3], app(NewsletterService::class)->send($newsletter));
        }

        $this->asAdmin();
        $this->get(route('admin.mail_review'))
            ->assertOk()
            ->assertSee(__('messages.mail_review_word', ['word' => 'bitcoin']))
            ->assertSee(__('messages.mail_review_word', ['word' => 'heroconf']));

        // Approved, it goes, once: a clone needs its own review.
        $this->post(route('admin.mail_review.approve', ['hash' => UrlUtils::encodeId($bySubject->id)]));
        $this->assertSame('sending', $bySubject->fresh()->status);
        $clone = $bySubject->fresh()->replicate(['send_token', 'sent_count', 'open_count', 'click_count', 'sent_at']);
        $clone->forceFill(['status' => 'draft'])->save();
        $this->assertNull($clone->approved_at);
        $this->assertSame(['review', 3], app(NewsletterService::class)->send($clone->fresh()));
    }

    public function test_the_admin_page_and_alert_rows_render(): void
    {
        $role = $this->schedule($this->owner());
        $this->subscribers($role, 11);
        app(NewsletterService::class)->send($this->newsletter($role));
        $paused = $this->owner();
        AudienceMailGuard::pause($paused, AudienceMailGuard::REASON_ADMIN, $this->createOwner(true));
        AudienceMailGuard::requestAccess($paused->fresh());

        AdminAlertService::flush();
        $types = AdminAlertService::items()->pluck('count', 'type');
        $this->assertSame(1, $types['newsletters_review'] ?? null);
        $this->assertSame(1, $types['mail_access_requests'] ?? null);

        $this->asAdmin();
        $this->get(route('admin.mail_review'))
            ->assertOk()
            ->assertSee(__('messages.mail_review_held_title'))
            ->assertSee(__('messages.mail_paused_title'))
            ->assertSee($paused->email)
            ->assertSee(__('messages.mail_review'));
    }

    public function test_non_admins_cannot_reach_the_review_page_or_its_actions(): void
    {
        $owner = $this->owner();
        $role = $this->schedule($owner);
        $this->subscribers($role, 11);
        $newsletter = $this->newsletter($role);
        app(NewsletterService::class)->send($newsletter);
        $hash = UrlUtils::encodeId($newsletter->id);
        $ownerHash = UrlUtils::encodeId($owner->id);

        $this->actingAs($owner);
        $this->assertNotSame(200, $this->get(route('admin.mail_review'))->status());
        $this->post(route('admin.mail_review.approve', ['hash' => $hash]));
        $this->post(route('admin.mail_review.enable', ['hash' => $ownerHash]));
        $this->assertSame('review', $newsletter->fresh()->status);
        $this->assertNull($owner->fresh()->newsletter_trusted_at);
        $this->assertNull($owner->fresh()->mail_cleared_at);

        // The actions answer POST only.
        $this->asAdmin();
        $this->get(route('admin.mail_review.approve', ['hash' => $hash]))->assertStatus(405);
    }

    // ── C. paused email ──────────────────────────────────────────────────

    private function fakeStripeCard(string $fingerprint): void
    {
        $paymentMethods = new class($fingerprint)
        {
            public function __construct(public string $fingerprint) {}

            public function retrieve($id, $params = null, $opts = null)
            {
                return PaymentMethod::constructFrom([
                    'id' => $id,
                    'type' => 'card',
                    'card' => ['brand' => 'visa', 'fingerprint' => $this->fingerprint, 'checks' => ['cvc_check' => 'pass']],
                ]);
            }
        };

        $client = new class($paymentMethods)
        {
            public function __construct(public object $paymentMethods) {}
        };

        $this->app->bind(StripeClient::class, fn () => $client);
    }

    public function test_a_card_already_used_on_another_account_pauses_email(): void
    {
        config(['app.trial_days' => 7, 'cashier.key' => 'pk_test_fixture', 'cashier.secret' => 'sk_test_fixture']);
        $this->fakeStripeCard('fp_same_card');

        // Schedules with no plan behind them, so checkout starts a trial (TrialSecurityCodeTest).
        $first = $this->owner();
        $firstRole = $this->trialEligible($first);
        $this->actingAs($first)->post('/'.$firstRole->subdomain.'/subscribe', ['payment_method' => 'pm_1', 'plan' => 'monthly', 'tier' => 'pro']);

        $second = $this->owner();
        $secondRole = $this->trialEligible($second);
        $this->actingAs($second)->post('/'.$secondRole->subdomain.'/subscribe', ['payment_method' => 'pm_2', 'plan' => 'monthly', 'tier' => 'pro']);

        $this->assertSame(2, DB::table('payment_fingerprints')->where('fingerprint', 'fp_same_card')->count());
        $this->assertNull($first->fresh()->mail_disabled_at);
        $this->assertNotNull($second->fresh()->mail_disabled_at);
        $this->assertSame(AudienceMailGuard::REASON_SHARED_CARD, $second->fresh()->mail_disabled_reason);

        // One card used twice on one account is not another account.
        $this->fakeStripeCard('fp_own_card');
        $third = $this->owner();
        $thirdRole = $this->trialEligible($third);
        $this->actingAs($third)->post('/'.$thirdRole->subdomain.'/subscribe', ['payment_method' => 'pm_3', 'plan' => 'monthly', 'tier' => 'pro']);
        $this->actingAs($third)->post('/'.$thirdRole->subdomain.'/subscribe', ['payment_method' => 'pm_4', 'plan' => 'monthly', 'tier' => 'pro']);
        $this->assertNull($third->fresh()->mail_disabled_at);
    }

    public function test_a_watched_word_pauses_email_live_including_a_word_added_later(): void
    {
        $owner = $this->owner();
        $role = $this->schedule($owner, ['name' => 'PPC Hero Tickets']);
        $this->subscribers($role, 2);
        $newsletter = $this->newsletter($role);

        // Not watched yet: it goes.
        $this->assertTrue(app(NewsletterService::class)->send($this->newsletter($role)));

        // The operator adds the word; the very next send asks the account again, past the cache.
        Setting::set(SpamWatch::SETTING, 'bitcoin, ppc hero');
        $this->assertSame(['mail_paused', 0], app(NewsletterService::class)->send($newsletter));
        $this->assertSame('draft', $newsletter->fresh()->status);
        $this->assertSame(AudienceMailGuard::REASON_SPAM_WATCH, $owner->fresh()->mail_disabled_reason);
        $this->assertTrue(AudienceMailGuard::mailPaused($role->fresh(), $owner->fresh()));

        // Not spam on /admin/blocked lifts it.
        SpamWatch::clear($owner->fresh(), $this->createOwner(true));
        $this->assertNull($owner->fresh()->mail_disabled_at);
    }

    public function test_a_paused_account_cannot_send_on_any_path(): void
    {
        $owner = $this->owner();
        $role = $this->schedule($owner);
        $this->subscribers($role, 2);
        AudienceMailGuard::pause($owner, AudienceMailGuard::REASON_ADMIN, $this->createOwner(true));
        $params = ['role_id' => UrlUtils::encodeId($role->id)];

        // Send now, schedule, a test send, an A/B test.
        $newsletter = $this->newsletter($role);
        $this->actingAs($owner)->post($this->sendUrl($role, $newsletter))->assertSessionHas('error', __('messages.mail_paused_send'));
        $this->actingAs($owner)->post(route('newsletter.schedule', ['hash' => UrlUtils::encodeId($newsletter->id)] + $params), ['scheduled_at' => now()->addDay()->format('Y-m-d H:i')])
            ->assertSessionHas('error', __('messages.mail_paused_send'));
        $this->actingAs($owner)->postJson(route('newsletter.test_send', ['hash' => UrlUtils::encodeId($newsletter->id)] + $params), ['to' => 'me'])->assertStatus(422);
        $test = NewsletterAbTest::create(['role_id' => $role->id, 'name' => 'T', 'test_field' => 'subject', 'sample_percentage' => 50, 'winner_criteria' => 'open_rate', 'winner_wait_hours' => 4, 'status' => 'pending']);
        $a = $this->newsletter($role, ['ab_test_id' => $test->id, 'ab_variant' => 'A']);
        $this->newsletter($role, ['ab_test_id' => $test->id, 'ab_variant' => 'B']);
        $this->actingAs($owner)->post(route('newsletter.ab_send', ['hash' => UrlUtils::encodeId($a->id)] + $params))->assertSessionHas('error', __('messages.mail_paused_send'));

        // A newsletter scheduled before the pause, at its time.
        $scheduled = $this->newsletter($role, ['status' => 'scheduled', 'scheduled_at' => now()->subMinute()]);
        (new ProcessScheduledNewsletters)();
        $this->assertSame('draft', $scheduled->fresh()->status);

        // Automatic announcements ask the pause too; the gate in front of an event's interest
        // list (canSendAudienceMail) does not, because a pause never stops mail people asked for.
        $this->assertTrue(AudienceMailGuard::mailPaused($role->fresh(), $owner->fresh()));
        $this->assertTrue($role->fresh()->canSendAudienceMail(1));

        $this->assertSame(0, NewsletterRecipient::count());
    }

    public function test_the_owner_sees_the_pause_and_can_ask_once_a_day(): void
    {
        $owner = $this->owner();
        $role = $this->schedule($owner);
        AudienceMailGuard::pause($owner, AudienceMailGuard::REASON_SHARED_CARD);
        $index = route('newsletter.index', ['role_id' => UrlUtils::encodeId($role->id)]);

        $this->actingAs($owner)->get($index)
            ->assertOk()
            ->assertSee(__('messages.mail_paused_owner_title'))
            ->assertSee(__('messages.mail_access_request'));

        $this->actingAs($owner)->from($index)->post(route('newsletter.request_mail_access', ['role_id' => UrlUtils::encodeId($role->id)]))
            ->assertSessionHas('status', __('messages.mail_access_requested'));
        $this->assertNotNull($owner->fresh()->mail_access_requested_at);
        $this->assertSame(1, AudienceMailGuard::openRequests()->count());

        $this->actingAs($owner)->from($index)->post(route('newsletter.request_mail_access', ['role_id' => UrlUtils::encodeId($role->id)]))
            ->assertSessionHas('error', __('messages.mail_access_requested_recently'));

        $this->actingAs($owner)->get($index)->assertSee(__('messages.mail_paused_owner_asked'));
    }

    public function test_an_operator_enables_email_or_keeps_it_paused(): void
    {
        $owner = $this->owner();
        $role = $this->schedule($owner);
        $this->subscribers($role, 2);
        AudienceMailGuard::pause($owner, AudienceMailGuard::REASON_SHARED_CARD);
        AudienceMailGuard::requestAccess($owner->fresh());

        $this->asAdmin();
        $this->post(route('admin.mail_review.keep_paused', ['hash' => UrlUtils::encodeId($owner->id)]))->assertSessionHas('success');
        $this->assertNotNull($owner->fresh()->mail_disabled_at);
        $this->assertSame(0, AudienceMailGuard::openRequests()->count());

        $this->post(route('admin.mail_review.enable', ['hash' => UrlUtils::encodeId($owner->id)]))->assertSessionHas('success');
        $fresh = $owner->fresh();
        $this->assertNull($fresh->mail_disabled_at);
        $this->assertNotNull($fresh->mail_cleared_at);
        // Letting mail back on does not trust the account's newsletters: that is an approval.
        $this->assertNull($fresh->newsletter_trusted_at);

        $this->assertTrue(app(NewsletterService::class)->send($this->newsletter($role->fresh())));
    }

    public function test_an_operator_pauses_from_the_account_page(): void
    {
        $owner = $this->owner();
        $this->schedule($owner);

        $this->asAdmin();
        $this->get(route('admin.blocked.account', ['hash' => UrlUtils::encodeId($owner->id)]))
            ->assertOk()
            ->assertSee(__('messages.mail_sending_title'))
            ->assertSee(__('messages.mail_pause_account'));
        $this->post(route('admin.mail_review.pause', ['hash' => UrlUtils::encodeId($owner->id)]))->assertSessionHas('success');

        $this->assertSame(AudienceMailGuard::REASON_ADMIN, $owner->fresh()->mail_disabled_reason);
    }

    public function test_a_pause_leaves_transactional_mail_selfhost_and_own_smtp_alone(): void
    {
        $owner = $this->owner();
        $role = $this->schedule($owner);
        $this->subscribers($role, 2);
        AudienceMailGuard::pause($owner, AudienceMailGuard::REASON_ADMIN, $this->createOwner(true));

        // Tickets and confirmations go through the transactional gate, which the pause is not part of.
        $this->assertTrue(EmailService::canSendScheduleMail($role->fresh(), true));

        // The schedule's own mail server.
        $role->email_settings = ['host' => 'smtp.mailbox.org', 'port' => 587, 'username' => 'me', 'password' => 'secret', 'encryption' => 'tls'];
        $role->save();
        $this->assertTrue(app(NewsletterService::class)->send($this->newsletter($role->fresh())));

        // Selfhost.
        config(['app.hosted' => false]);
        $plain = $this->schedule($owner);
        $this->assertTrue(app(NewsletterService::class)->send($this->newsletter($plain)));
    }

    public function test_deleting_the_account_deletes_its_card_fingerprints(): void
    {
        // The privacy policy says the fingerprint goes with the account.
        $owner = $this->owner();
        $other = $this->owner();
        foreach ([$owner, $other] as $user) {
            DB::table('payment_fingerprints')->insert(['user_id' => $user->id, 'role_id' => null, 'fingerprint' => 'fp_shared', 'created_at' => now()]);
        }

        app(AccountDeletionService::class)->prepare($owner);

        $this->assertSame(0, DB::table('payment_fingerprints')->where('user_id', $owner->id)->count());
        $this->assertSame(1, DB::table('payment_fingerprints')->where('user_id', $other->id)->count());
    }

    public function test_the_embed_dialog_says_email_is_paused_not_that_a_verification_is_needed(): void
    {
        $owner = $this->owner();
        $role = $this->schedule($owner);
        AudienceMailGuard::pause($owner, AudienceMailGuard::REASON_SHARED_CARD);

        $this->actingAs($owner)->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']))
            ->assertOk()
            ->assertSee(__('messages.mail_paused_owner_title'))
            ->assertDontSee(__('messages.embed_subscribe_verification_title', ['limit' => (int) config('usage.audience_mail_unverified_max_recipients', 50)]));
    }

    // ── D. a trial sends the free plan's allowance ───────────────────────

    public function test_pricing_says_what_a_trial_sends(): void
    {
        $this->onAppHost = false;
        config(['app.is_testing' => true]);
        LegalDocument::create(['type' => LegalDocument::COOKIES, 'content' => 'We use cookies.']);

        $html = $this->get('/pricing')->assertOk()->getContent();

        // In the small print and in the trial's FAQ answer.
        $this->assertGreaterThanOrEqual(2, substr_count(html_entity_decode($html, ENT_QUOTES), 'During the free trial, newsletters can go to up to 10 people a month. End the trial early to send to more.'));
    }

    private function fakeStripeSubscriptions(array $answer): object
    {
        $subscriptions = new class($answer)
        {
            public array $updates = [];

            public function __construct(public array $answer) {}

            public function update($id, $params = null, $opts = null)
            {
                $this->updates[] = [$id, $params];

                return Subscription::constructFrom(['id' => $id] + $this->answer);
            }
        };

        $client = new class($subscriptions)
        {
            public function __construct(public object $subscriptions) {}
        };

        $this->app->bind(StripeClient::class, fn () => $client);

        return $subscriptions;
    }

    public function test_a_trial_gets_the_free_newsletter_allowance(): void
    {
        $this->assertSame(Role::FREE_NEWSLETTER_LIMIT, $this->trialing($this->owner())->newsletterLimit());
        $this->assertSame(Role::FREE_NEWSLETTER_LIMIT, $this->trialing($this->owner(), 'price_ent_m')->newsletterLimit());

        $paid = $this->schedule($this->owner(), ['plan_type' => 'pro', 'plan_expires' => null]);
        $this->subscribe($paid, 'active');
        $this->assertSame(100, $paid->fresh()->newsletterLimit());
    }

    public function test_ending_the_trial_lifts_the_allowance(): void
    {
        $owner = $this->owner();
        $role = $this->trialing($owner, 'price_ent_m');
        $stripe = $this->fakeStripeSubscriptions(['status' => 'active']);

        $this->actingAs($owner)->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'plan']))
            ->assertOk()
            ->assertSee(route('subscription.end_trial', ['subdomain' => $role->subdomain]));

        $this->actingAs($owner)->post(route('subscription.end_trial', ['subdomain' => $role->subdomain]))
            ->assertSessionHas('message', __('messages.subscription_trial_ended'));

        $this->assertSame('now', $stripe->updates[0][1]['trial_end']);
        $this->assertSame('pending_if_incomplete', $stripe->updates[0][1]['payment_behavior']);
        $this->assertSame(1000, $role->fresh()->newsletterLimit());
    }

    public function test_only_the_owner_ends_the_trial(): void
    {
        $owner = $this->owner();
        $role = $this->trialing($owner);
        $member = $this->createOwner();
        $this->followRole($member, $role, 'admin');
        $stripe = $this->fakeStripeSubscriptions(['status' => 'active']);

        $this->actingAs($member)->post(route('subscription.end_trial', ['subdomain' => $role->subdomain]))
            ->assertSessionHas('error', __('messages.not_authorized'));

        $this->assertSame([], $stripe->updates);
        $this->assertSame(Role::FREE_NEWSLETTER_LIMIT, $role->fresh()->newsletterLimit());
    }

    public function test_a_failed_charge_keeps_the_trial_and_says_so(): void
    {
        $owner = $this->owner();
        $role = $this->trialing($owner);
        $this->fakeStripeSubscriptions([
            'status' => 'trialing',
            'pending_update' => ['trial_end' => now()->timestamp],
            'latest_invoice' => ['id' => 'in_1', 'payment_intent' => ['id' => 'pi_1', 'status' => 'requires_payment_method']],
        ]);

        $this->actingAs($owner)->post(route('subscription.end_trial', ['subdomain' => $role->subdomain]))
            ->assertSessionHas('error', __('messages.subscription_end_trial_declined'));

        $this->assertTrue($role->fresh()->onSubscriptionTrial());
        $this->assertSame(Role::FREE_NEWSLETTER_LIMIT, $role->fresh()->newsletterLimit());
    }

    public function test_a_charge_that_needs_the_cardholder_goes_to_the_payment_page(): void
    {
        $owner = $this->owner();
        $role = $this->trialing($owner);
        $this->fakeStripeSubscriptions([
            'status' => 'trialing',
            'pending_update' => ['trial_end' => now()->timestamp],
            'latest_invoice' => ['id' => 'in_1', 'payment_intent' => ['id' => 'pi_3ds', 'status' => 'requires_action']],
        ]);

        $response = $this->actingAs($owner)->post(route('subscription.end_trial', ['subdomain' => $role->subdomain]));

        $this->assertStringContainsString('pi_3ds', (string) $response->headers->get('Location'));
    }
    // ── the security review's findings ───────────────────────────────────

    public function test_trust_is_not_read_from_newsletters_a_restore_could_write(): void
    {
        // A backup carries status 'sent' and any sent_count: that is no track record.
        $owner = $this->owner();
        $role = $this->schedule($owner);
        $this->newsletter($role, ['status' => 'sent', 'sent_count' => 500, 'sent_at' => now()->subDays(3)]);
        $this->subscribers($role, 11);

        $this->assertFalse(AudienceMailGuard::isTrusted($owner->fresh()));
        $this->assertSame('review', app(NewsletterService::class)->send($this->newsletter($role))[0]);

        // The backfill trusts only a send from before the abuse began, or an owner who has paid,
        // and never a blocked account.
        $earlier = $this->owner();
        $this->newsletter($this->schedule($earlier), ['status' => 'sent', 'sent_count' => 40, 'sent_at' => '2026-10-15 10:00:00']);
        $blocked = $this->owner();
        $blocked->forceFill(['blocked_at' => now()])->save();
        $this->newsletter($this->schedule($blocked), ['status' => 'sent', 'sent_count' => 40, 'sent_at' => '2026-10-15 10:00:00']);
        $payer = $this->owner();
        $payerRole = $this->schedule($payer, ['plan_type' => 'pro', 'plan_expires' => null]);
        $this->subscribe($payerRole, 'active');
        $this->newsletter($payerRole, ['status' => 'sent', 'sent_count' => 40, 'sent_at' => now()->subDays(2)]);

        $migration = require database_path('migrations/2026_11_18_100000_add_newsletter_review_and_mail_pause.php');
        $migration->up();

        $this->assertNull($owner->fresh()->newsletter_trusted_at, 'a recent send with no payment');
        $this->assertNotNull($earlier->fresh()->newsletter_trusted_at, 'a send from before November');
        $this->assertNull($blocked->fresh()->newsletter_trusted_at, 'a blocked account');
        $this->assertNotNull($payer->fresh()->newsletter_trusted_at, 'an owner who has paid');
    }

    public function test_sales_the_owner_entered_or_that_are_not_paid_are_not_buyers_who_opted_in(): void
    {
        $owner = $this->owner();
        $role = $this->trialing($owner);
        $event = $this->createEvent($role);
        $paid = $this->createSale($event, $role, ['email' => 'buyer@mailbox.org', 'status' => 'paid', 'payment_method' => 'stripe']);
        $this->createSale($event, $role, ['email' => 'imported@mailbox.org', 'status' => 'paid', 'payment_method' => 'import']);
        $this->createSale($event, $role, ['email' => 'boxoffice@mailbox.org', 'status' => 'paid', 'payment_method' => 'box_office']);
        $api = $this->createSale($event, $role, ['email' => 'api@mailbox.org', 'status' => 'paid', 'payment_method' => 'stripe']);
        $api->forceFill(['origin' => 'api'])->save();
        $restored = $this->createSale($event, $role, ['email' => 'restored@mailbox.org', 'status' => 'paid', 'payment_method' => 'stripe']);
        $restored->forceFill(['origin' => 'restore'])->save();
        $this->createSale($event, $role, ['email' => 'abandoned@mailbox.org', 'status' => 'unpaid', 'payment_method' => 'stripe']);

        $buyers = NewsletterSegment::create(['role_id' => $role->id, 'name' => 'Buyers', 'type' => 'ticket_buyers']);
        $newsletter = $this->newsletter($role, ['segment_ids' => [$buyers->id]]);
        $this->assertTrue(app(NewsletterService::class)->send($newsletter));

        $sent = $this->recipientsOf($newsletter);
        $this->assertContains('buyer@mailbox.org', $sent);
        foreach (['imported', 'boxoffice', 'api', 'restored', 'abandoned'] as $who) {
            $this->assertNotContains($who.'@mailbox.org', $sent, $who);
        }

        // Nor do they make an imported address "opted in".
        $optedIn = NewsletterSegment::optedInAmong($role, ['buyer@mailbox.org', 'imported@mailbox.org', 'restored@mailbox.org', 'abandoned@mailbox.org']);
        $this->assertSame(['buyer@mailbox.org'], array_keys($optedIn));
        $this->assertSame('paid', $paid->status);
    }

    public function test_restored_subscribers_wait_for_the_first_payment_and_miss_the_announcements(): void
    {
        $owner = $this->owner();
        $role = $this->trialing($owner);
        $fans = $this->subscribers($role, 1);
        $restored = $this->subscribers($role, 1, 'restored');
        RoleSubscriber::where('email', $restored[0])->update(['origin' => 'restore']);

        $newsletter = $this->newsletter($role);
        $this->assertTrue(app(NewsletterService::class)->send($newsletter));
        $this->assertContains($fans[0], $this->recipientsOf($newsletter));
        $this->assertNotContains($restored[0], $this->recipientsOf($newsletter));

        $announced = app(AudienceResolver::class)->announcementRecipients($role)->pluck('email')->all();
        $this->assertContains($fans[0], $announced);
        $this->assertNotContains($restored[0], $announced);

        // Paid, they are the schedule's again.
        $this->subscribe($role, 'active');
        DB::table('subscriptions')->where('role_id', $role->id)->update(['last_paid_at' => now()]);
        $this->assertContains($restored[0], app(AudienceResolver::class)->announcementRecipients($role->fresh())->pluck('email')->all());
    }

    public function test_a_restore_marks_what_it_brings_in_and_takes_a_held_newsletter_back_as_a_draft(): void
    {
        $owner = $this->owner();
        $role = $this->createFreeRole($owner);
        $event = $this->createEvent($role);
        $backup = app(BackupService::class);
        $flag = new \ReflectionProperty($backup, 'restoreUnsolicited');
        $flag->setValue($backup, true);
        $call = function (string $method, array $args) use ($backup) {
            $reflection = new \ReflectionMethod($backup, $method);

            return $reflection->invokeArgs($backup, $args);
        };

        $idMap = ['promo_codes' => [], 'gift_cards' => [], 'promoter_links' => [], 'ab_tests' => [], 'segments' => [], 'events' => []];
        $sale = $call('importSale', [['name' => 'Buyer', 'email' => 'buyer@mailbox.org', 'status' => 'paid', 'event_date' => '2026-12-01', 'payment_method' => 'stripe', 'payment_amount' => 20, 'discount_amount' => 0], $event, $role, $owner->id, &$idMap]);
        $this->assertSame('restore', $sale->fresh()->origin);

        $call('importWaitlist', [['email' => 'wait@mailbox.org', 'status' => 'waiting', 'event_date' => '2026-12-01'], $event, $role]);
        $this->assertSame('expired', TicketWaitlist::where('email', 'wait@mailbox.org')->value('status'));

        $call('importInterest', [['email' => 'interest@mailbox.org', 'confirmed_at' => now()->toDateTimeString()], $event]);
        $this->assertNull(EventInterest::where('email', 'interest@mailbox.org')->value('confirmed_at'));

        $newsletter = $call('importNewsletter', [['subject' => 'Held one', 'status' => 'review', 'type' => 'admin', 'sent_count' => 900], $role, $owner->id, &$idMap]);
        $this->assertSame('draft', $newsletter->fresh()->status);
        $this->assertSame('schedule', $newsletter->fresh()->type);
    }

    public function test_cancelling_a_trial_does_not_keep_its_allowance(): void
    {
        $role = $this->trialing($this->owner(), 'price_ent_m');
        DB::table('subscriptions')->where('role_id', $role->id)->update(['ends_at' => now()->addDays(7)]);
        $role = $role->fresh();

        $this->assertFalse($role->hasActiveEnterpriseSubscription());
        $this->assertSame(Role::FREE_NEWSLETTER_LIMIT, $role->newsletterLimit());
        $this->assertFalse($role->canEndTrial());
    }

    public function test_review_adds_up_what_an_untrusted_owner_sent_in_thirty_days(): void
    {
        $role = $this->schedule($this->owner());
        $this->subscribers($role, 5);

        // Six with the owner: under the line, and it goes.
        $this->assertTrue(app(NewsletterService::class)->send($this->newsletter($role)));
        // Six more makes twelve in thirty days: held.
        $this->assertSame('review', app(NewsletterService::class)->send($this->newsletter($role))[0]);
    }

    public function test_a_paid_invoice_is_what_counts_as_paying(): void
    {
        config(['cashier.webhook.secret' => null]);
        $owner = $this->owner();
        $role = $this->schedule($owner, ['plan_type' => 'pro', 'plan_expires' => null]);
        $this->subscribe($role, 'active', null, 'price_pro_m', false);
        $this->assertFalse(AudienceMailGuard::ownerHasPaid($owner));

        $stripeId = DB::table('subscriptions')->where('role_id', $role->id)->value('stripe_id');
        $this->postJson(route('stripe.subscription_webhook'), ['type' => 'invoice.payment_succeeded', 'data' => ['object' => [
            'customer' => 'cus_none', 'subscription' => $stripeId, 'amount_paid' => 0,
        ]]])->assertOk();
        $this->assertFalse(AudienceMailGuard::ownerHasPaid($owner), 'a zero invoice is no payment');

        $this->postJson(route('stripe.subscription_webhook'), ['type' => 'invoice.payment_succeeded', 'data' => ['object' => [
            'customer' => 'cus_none', 'subscription' => $stripeId, 'amount_paid' => 1500,
        ]]])->assertOk();
        $this->assertTrue(AudienceMailGuard::ownerHasPaid($owner));
    }

    public function test_ending_a_trial_into_past_due_is_not_a_success(): void
    {
        $owner = $this->owner();
        $role = $this->trialing($owner);
        $this->fakeStripeSubscriptions(['status' => 'past_due', 'latest_invoice' => ['id' => 'in_1', 'amount_paid' => 0, 'payment_intent' => ['id' => 'pi_1', 'status' => 'requires_payment_method']]]);

        $this->actingAs($owner)->post(route('subscription.end_trial', ['subdomain' => $role->subdomain]))
            ->assertSessionHas('error', __('messages.subscription_end_trial_declined'));
        $this->assertNull(DB::table('subscriptions')->where('role_id', $role->id)->value('last_paid_at'));
    }

    public function test_ending_a_trial_twice_at_once_asks_stripe_once(): void
    {
        $owner = $this->owner();
        $role = $this->trialing($owner);
        $stripe = $this->fakeStripeSubscriptions(['status' => 'active']);
        $lock = Cache::lock('end-trial:'.DB::table('subscriptions')->where('role_id', $role->id)->value('stripe_id'), 30);
        $lock->get();

        $this->actingAs($owner)->post(route('subscription.end_trial', ['subdomain' => $role->subdomain]))->assertSessionHas('error');
        $this->assertSame([], $stripe->updates);
        $lock->release();
    }

    public function test_a_pause_stops_a_send_in_progress(): void
    {
        $owner = $this->owner();
        $role = $this->schedule($owner);
        $this->subscribers($role, 3);
        $newsletter = $this->newsletter($role);
        $this->assertTrue(app(NewsletterService::class)->send($newsletter));
        $ids = NewsletterRecipient::where('newsletter_id', $newsletter->id)->pluck('id')->all();

        // Paused by a column write elsewhere: the next batch reads it and stops.
        DB::table('users')->where('id', $owner->id)->update(['mail_disabled_at' => now(), 'mail_disabled_reason' => 'admin']);
        (new SendNewsletterBatch($newsletter->id, $ids))->handle(app(NewsletterService::class));
        $this->assertSame('cancelled', $newsletter->fresh()->status);
        $this->assertSame(0, NewsletterRecipient::where('newsletter_id', $newsletter->id)->where('status', 'sent')->count());
        Mail::assertNothingSent();

        // And pause() itself stops what the account is sending.
        $other = $this->owner();
        $otherRole = $this->schedule($other);
        $this->subscribers($otherRole, 2);
        $sending = $this->newsletter($otherRole);
        app(NewsletterService::class)->send($sending);
        AudienceMailGuard::pause($other, AudienceMailGuard::REASON_ADMIN, $this->createOwner(true));
        $this->assertSame('cancelled', $sending->fresh()->status);
    }

    public function test_a_card_from_a_deleted_account_is_still_recognised(): void
    {
        $gone = $this->owner();
        AudienceMailGuard::recordCardFingerprint($gone, null, 'fp_reused');
        AudienceMailGuard::forgetFingerprints($gone);

        $row = DB::table('payment_fingerprints')->first();
        $this->assertNull($row->user_id);
        $this->assertNotSame('fp_reused', $row->fingerprint);
        $this->assertNotNull($row->hashed_at);

        $next = $this->owner();
        AudienceMailGuard::recordCardFingerprint($next, null, 'fp_reused');
        $this->assertSame(AudienceMailGuard::REASON_SHARED_CARD, $next->fresh()->mail_disabled_reason);

        // Twelve months on, the hash is pruned.
        $this->travel(13)->months();
        $this->artisan('app:prune-personal-data');
        $this->assertSame(0, DB::table('payment_fingerprints')->whereNull('user_id')->count());
    }

    public function test_the_upgrade_to_enterprise_fingerprints_its_card(): void
    {
        config(['cashier.key' => 'pk_test_fixture', 'cashier.secret' => 'sk_test_fixture']);
        $this->fakeStripeCard('fp_upgrade');
        DB::table('payment_fingerprints')->insert(['user_id' => $this->owner()->id, 'role_id' => null, 'fingerprint' => 'fp_upgrade', 'created_at' => now()]);

        $owner = $this->owner();
        $role = $this->schedule($owner, ['plan_type' => 'pro', 'plan_expires' => null]);
        $this->subscribe($role, 'active', null, 'price_pro_m', false);

        $this->actingAs($owner)->post('/'.$role->subdomain.'/subscribe', ['payment_method' => 'pm_up', 'plan' => 'monthly', 'tier' => 'enterprise']);

        // The upgrade charges the card: flagged for an operator, never paused.
        $this->assertSame(1, DB::table('payment_fingerprints')->where('user_id', $owner->id)->count());
        $this->assertNull($owner->fresh()->mail_disabled_at);
        $this->assertSame(AudienceMailGuard::REASON_SHARED_CARD, $owner->fresh()->mail_flag_reason);
    }

    public function test_a_paying_owner_is_flagged_never_paused_or_held(): void
    {
        $owner = $this->owner();
        $role = $this->schedule($owner, ['name' => 'Bitcoin Meetup', 'plan_source' => 'admin']);
        $this->subscribers($role, 2);

        // A watched word in the schedule's name: flagged, not paused.
        $this->assertFalse(AudienceMailGuard::mailPaused($role, $owner));
        $this->assertNull($owner->fresh()->mail_disabled_at);
        $this->assertSame(AudienceMailGuard::REASON_SPAM_WATCH, $owner->fresh()->mail_flag_reason);

        // A watched word in the newsletter: sent, not held.
        $this->assertTrue(app(NewsletterService::class)->send($this->newsletter($role, ['subject' => 'Bitcoin night'])));

        // A shared card: flagged too.
        DB::table('payment_fingerprints')->insert(['user_id' => $this->owner()->id, 'role_id' => null, 'fingerprint' => 'fp_paying', 'created_at' => now()]);
        AudienceMailGuard::recordCardFingerprint($owner->fresh(), $role, 'fp_paying');
        $this->assertNull($owner->fresh()->mail_disabled_at);

        AdminAlertService::flush();
        $this->assertSame(1, AdminAlertService::items()->firstWhere('type', 'mail_flagged')['count'] ?? null);
        $this->asAdmin();
        $this->get(route('admin.mail_review'))->assertOk()->assertSee(__('messages.mail_flagged_title'))->assertSee($owner->email);
        $this->post(route('admin.mail_review.dismiss_flag', ['hash' => UrlUtils::encodeId($owner->id)]))->assertSessionHas('success');
        $this->assertNull($owner->fresh()->mail_flagged_at);
    }

    public function test_watched_words_are_read_in_what_the_owner_wrote_never_in_links(): void
    {
        $owner = $this->owner();
        $role = $this->schedule($owner);
        $this->subscribers($role, 2);
        $newsletter = $this->newsletter($role, ['blocks' => [
            ['type' => 'social_links', 'data' => ['links' => [['platform' => 'paypal', 'url' => 'https://paypal.me/openstage']]]],
            ['type' => 'button', 'data' => ['text' => 'Pay at the door', 'url' => 'https://www.paypal.com/donate']],
            ['type' => 'text', 'data' => ['content' => 'Donate [here](https://paypal.me/openstage) or at https://bitcoin.org/x, or write to bitcoin@mailbox.org']],
        ]]);
        Setting::set(SpamWatch::SETTING, 'bitcoin, paypal');

        $this->assertNull(AudienceMailGuard::watchedWordIn($newsletter));
        $this->assertTrue(app(NewsletterService::class)->send($newsletter));
    }

    public function test_a_held_schedule_newsletter_of_any_type_is_in_the_admin_queue(): void
    {
        // A schedule's row is held whatever its type says (a restore before the hotfix wrote 'admin').
        $role = $this->schedule($this->owner());
        $held = $this->newsletter($role, ['status' => 'review', 'type' => 'admin', 'held_at' => now(), 'subject' => 'Typeless held']);

        AdminAlertService::flush();
        $this->assertSame(1, AdminAlertService::items()->firstWhere('type', 'newsletters_review')['count'] ?? null);
        $this->asAdmin();
        $this->get(route('admin.mail_review'))->assertOk()->assertSee('Typeless held');
        $this->assertSame('review', $held->fresh()->status);
    }
    // ── the second review's findings ─────────────────────────────────────

    public function test_deleting_a_sent_newsletter_gives_back_neither_the_allowance_nor_the_review_count(): void
    {
        $owner = $this->owner();
        $role = $this->schedule($owner);
        $this->subscribers($role, 5);
        $first = $this->newsletter($role);
        $this->assertTrue(app(NewsletterService::class)->send($first));
        $this->assertSame(6, $role->fresh()->newslettersSentThisMonth());

        // It went out.
        NewsletterRecipient::where('newsletter_id', $first->id)->update(['status' => 'sent', 'sent_at' => now()]);
        $first->forceFill(['status' => 'sent', 'sent_at' => now(), 'sent_count' => 6])->save();

        // Delete it (the owner's Delete marks it cancelled; its recipient rows stay).
        $this->actingAs($owner)->delete(route('newsletter.delete', ['hash' => UrlUtils::encodeId($first->id), 'role_id' => UrlUtils::encodeId($role->id)]));
        $this->assertSame('cancelled', $first->fresh()->status);
        $this->assertSame(6, NewsletterRecipient::where('newsletter_id', $first->id)->count());

        $this->assertSame(6, $role->fresh()->newslettersSentThisMonth());
        $this->assertSame(6, AudienceMailGuard::recentRecipients($owner->fresh()));
        $this->assertSame('review', app(NewsletterService::class)->send($this->newsletter($role->fresh()))[0]);
    }

    public function test_a_shared_card_charged_today_flags_and_a_payment_lifts_a_trial_pause(): void
    {
        config(['app.trial_days' => 7, 'cashier.key' => 'pk_test_fixture', 'cashier.secret' => 'sk_test_fixture', 'cashier.webhook.secret' => null]);
        $this->fakeStripeCard('fp_paid_card');
        DB::table('payment_fingerprints')->insert(['user_id' => $this->owner()->id, 'role_id' => null, 'fingerprint' => 'fp_paid_card', 'created_at' => now()]);

        // No trial left on this login, so checkout charges today.
        $payer = $this->owner(['trial_used_at' => now()->subYear()]);
        $role = $this->createFreeRole($payer);
        $this->assertNull($role->checkoutTrialEnd());
        $this->actingAs($payer)->post('/'.$role->subdomain.'/subscribe', ['payment_method' => 'pm_pay', 'plan' => 'monthly', 'tier' => 'pro']);

        $this->assertNull($payer->fresh()->mail_disabled_at);
        $this->assertSame(AudienceMailGuard::REASON_SHARED_CARD, $payer->fresh()->mail_flag_reason);

        // A trial's shared-card pause becomes a flag once a paid invoice arrives.
        $trialist = $this->owner();
        $trialRole = $this->trialing($trialist);
        AudienceMailGuard::pause($trialist, AudienceMailGuard::REASON_SHARED_CARD);
        $this->assertNotNull($trialist->fresh()->mail_disabled_at);
        $stripeId = DB::table('subscriptions')->where('role_id', $trialRole->id)->value('stripe_id');
        $this->postJson(route('stripe.subscription_webhook'), ['type' => 'invoice.payment_succeeded', 'data' => ['object' => [
            'customer' => 'cus_none', 'subscription' => $stripeId, 'amount_paid' => 1500,
        ]]])->assertOk();

        $this->assertNull($trialist->fresh()->mail_disabled_at);
        $this->assertSame(AudienceMailGuard::REASON_SHARED_CARD, $trialist->fresh()->mail_flag_reason);
    }

    public function test_checkout_stamps_a_payment_from_stripes_own_answer(): void
    {
        $owner = $this->owner();
        $role = $this->schedule($owner, ['plan_type' => 'pro', 'plan_expires' => null]);
        $this->subscribe($role, 'active', null, 'price_pro_m', false);
        AudienceMailGuard::pause($owner, AudienceMailGuard::REASON_SHARED_CARD);

        $subscriptions = new class
        {
            public function retrieve($id, $params = null, $opts = null)
            {
                return Subscription::constructFrom(['id' => $id, 'status' => 'active', 'latest_invoice' => ['id' => 'in_1', 'amount_paid' => 2900]]);
            }
        };
        $this->app->bind(StripeClient::class, fn () => new class($subscriptions)
        {
            public function __construct(public object $subscriptions) {}
        });

        AudienceMailGuard::recordPaymentFromStripe($role->fresh()->subscription('default'));

        $this->assertNotNull(DB::table('subscriptions')->where('role_id', $role->id)->value('last_paid_at'));
        $this->assertTrue(AudienceMailGuard::ownerHasPaid($owner->fresh()));
        $this->assertNull($owner->fresh()->mail_disabled_at);
    }

    public function test_buyer_segments_still_hold_every_sale_but_a_cancelled_one_for_a_paying_owner(): void
    {
        $owner = $this->owner();
        $role = $this->schedule($owner, ['plan_source' => 'admin']);
        $event = $this->createEvent($role);
        $this->createSale($event, $role, ['email' => 'unpaid@mailbox.org', 'status' => 'unpaid']);
        $this->createSale($event, $role, ['email' => 'imported@mailbox.org', 'status' => 'paid', 'payment_method' => 'import']);
        $this->createSale($event, $role, ['email' => 'cancelled@mailbox.org', 'status' => 'cancelled']);
        $this->createSale($event, $role, ['email' => 'refunded@mailbox.org', 'status' => 'refunded']);

        $buyers = NewsletterSegment::create(['role_id' => $role->id, 'name' => 'Buyers', 'type' => 'ticket_buyers']);
        $emails = $buyers->resolveRecipients()->pluck('email')->all();

        $this->assertContains('unpaid@mailbox.org', $emails);
        $this->assertContains('imported@mailbox.org', $emails);
        $this->assertNotContains('cancelled@mailbox.org', $emails);
        $this->assertNotContains('refunded@mailbox.org', $emails);
    }

    public function test_a_checkout_tick_box_counts_only_with_a_paid_sale_behind_it(): void
    {
        $owner = $this->owner();
        $role = $this->trialing($owner);
        $event = $this->createEvent($role);
        foreach (['ticked@mailbox.org', 'bought@mailbox.org'] as $email) {
            RoleSubscriber::create(['role_id' => $role->id, 'email' => $email, 'source' => 'checkout', 'token' => Str::random(40), 'confirmed_at' => now()]);
        }
        // One finished paying; the other ticked the box and never paid.
        $this->createSale($event, $role, ['email' => 'bought@mailbox.org', 'status' => 'paid']);
        $this->createSale($event, $role, ['email' => 'ticked@mailbox.org', 'status' => 'unpaid']);

        $newsletter = $this->newsletter($role);
        $this->assertTrue(app(NewsletterService::class)->send($newsletter));

        $this->assertContains('bought@mailbox.org', $this->recipientsOf($newsletter));
        $this->assertNotContains('ticked@mailbox.org', $this->recipientsOf($newsletter));
    }

    public function test_a_pause_leaves_an_interest_lists_cancellation_notice_alone_and_it_carries_no_owner_note(): void
    {
        $owner = $this->owner();
        $role = $this->schedule($owner);
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);
        EventInterest::create(['event_id' => $event->id, 'event_date' => '', 'email' => 'fan@mailbox.org', 'token' => Str::random(40), 'confirmed_at' => now(), 'source' => 'event_page']);
        AudienceMailGuard::pause($owner, AudienceMailGuard::REASON_ADMIN, $this->createOwner(true));

        EventChangeNotifier::notifyCancellation($event->fresh(), $role, 'Pay your refund at evil.example now');

        $sent = Queue::pushed(SendQueuedEmail::class)->map(fn ($job) => (new \ReflectionProperty($job, 'mailable'))->getValue($job))
            ->filter(fn ($mailable) => $mailable instanceof EventInterestNotification);
        $this->assertCount(1, $sent, 'the interest list still hears about the cancellation');
        $this->assertStringNotContainsString('evil.example', $sent->first()->render());
    }

    public function test_the_operators_preview_loads_nothing_from_elsewhere(): void
    {
        $role = $this->schedule($this->owner());
        $newsletter = $this->newsletter($role);

        $this->asAdmin();
        $response = $this->get(route('admin.mail_review.preview', ['hash' => UrlUtils::encodeId($newsletter->id)]))->assertOk();

        $this->assertSame("sandbox; default-src 'none'; img-src 'self' data:; style-src 'unsafe-inline'", $response->headers->get('Content-Security-Policy'));
    }

    public function test_a_held_newsletter_keeps_the_mix_it_was_held_with(): void
    {
        $role = $this->schedule($this->owner());
        $this->subscribers($role, 11);
        $newsletter = $this->newsletter($role);
        app(NewsletterService::class)->send($newsletter);

        $this->assertEquals(['opted_in' => 12, 'imported' => 0, 'sends_to' => 12], $newsletter->fresh()->review_mix);

        // The review page reads it, and does not work it out again.
        $newsletter->forceFill(['review_mix' => ['opted_in' => 3, 'imported' => 9, 'sends_to' => 3]])->save();
        $this->asAdmin();
        $this->get(route('admin.mail_review'))->assertOk()
            ->assertSee(__('messages.mail_review_mix', ['opted' => 3, 'imported' => 9]));
    }

    public function test_an_approval_survives_a_save_that_changes_nothing_it_was_given_for(): void
    {
        $owner = $this->owner();
        $role = $this->schedule($owner);
        $newsletter = $this->newsletter($role, ['approved_at' => now(), 'style_settings' => ['previewText' => 'Hello']]);
        $url = route('newsletter.update', ['hash' => UrlUtils::encodeId($newsletter->id), 'role_id' => UrlUtils::encodeId($role->id)]);
        $post = fn (array $over = []) => $this->actingAs($owner)->put($url, $over + [
            'subject' => $newsletter->subject,
            'blocks' => json_encode($newsletter->blocks),
            'template' => 'bold',
            'style_settings' => ['previewText' => 'Hello'],
        ]);

        // A first save stores the blocks as the builder writes them; the approval comes after.
        $post();
        $newsletter->refresh()->forceFill(['approved_at' => now()])->save();
        $post = fn (array $over = []) => $this->actingAs($owner)->put($url, $over + [
            'subject' => $newsletter->subject,
            'blocks' => json_encode($newsletter->blocks),
            'template' => 'classic',
            'style_settings' => ['previewText' => 'Hello'],
        ]);

        // Another design: still the newsletter that was approved.
        $post();
        $this->assertNotNull($newsletter->fresh()->approved_at);

        // Another subject: not.
        $post(['subject' => 'Something else']);
        $this->assertNull($newsletter->fresh()->approved_at);
    }

    public function test_a_page_view_never_records_a_pause(): void
    {
        $owner = $this->owner();
        $role = $this->schedule($owner, ['name' => 'Bitcoin Help Desk']);
        $sending = $this->newsletter($role, ['status' => 'sending']);
        $params = ['role_id' => UrlUtils::encodeId($role->id)];

        $this->actingAs($owner)->get(route('newsletter.index', $params))->assertOk()->assertSee(__('messages.mail_paused_owner_title'));
        $this->actingAs($owner)->getJson(route('newsletter.audience', $params))->assertOk()
            ->assertJsonPath('refusal.message', __('messages.mail_paused_send'));

        $this->assertNull($owner->fresh()->mail_disabled_at);
        $this->assertSame('sending', $sending->fresh()->status);

        // A send attempt records it.
        app(NewsletterService::class)->send($this->newsletter($role));
        $this->assertSame(AudienceMailGuard::REASON_SPAM_WATCH, $owner->fresh()->mail_disabled_reason);
    }
}
