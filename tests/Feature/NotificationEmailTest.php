<?php

namespace Tests\Feature;

use App\Jobs\SendQueuedEmail;
use App\Mail\NewSaleNotification;
use App\Mail\NotificationEmailConfirmation;
use App\Models\Role;
use App\Models\User;
use App\Notifications\NewRequestsNotification;
use App\Services\EmailService;
use App\Services\NotificationEmailService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A schedule's shared notification address (issue #124): a team inbox that gets a copy of the
 * owner notifications. What is pinned here is the contract the feature rests on - nothing reaches
 * the address until it is confirmed from the mailbox itself, the confirmation and the removal both
 * belong to one specific address, and the copy follows the same rules as the editors' mail.
 */
class NotificationEmailTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const SHARED = 'team.inbox@gmail.com';

    protected function setUp(): void
    {
        parent::setUp();

        // Selfhost with a real mailer: the notification send sites skip log/array, and
        // phpunit.xml sets array.
        config(['app.hosted' => false, 'mail.default' => 'smtp']);
    }

    private function requestSchedule(?User $owner = null, array $attrs = []): Role
    {
        return $this->createRole($owner ?? $this->createOwner(), 'venue', array_merge([
            'accept_requests' => true,
            'require_account' => false,
            'require_approval' => true,
            'event_request_form' => 'booking',
        ], $attrs));
    }

    private function withVerifiedAddress(Role $role, string $address = self::SHARED, array $settings = []): Role
    {
        $role->notification_email = $address;
        $role->notification_email_verified_at = now();
        $role->notification_email_settings = $settings ?: null;
        $role->save();

        return $role->fresh();
    }

    private function saveSchedule(User $owner, Role $role, array $fields)
    {
        return $this->actingAs($owner)
            ->put(route('role.update', ['subdomain' => $role->subdomain]), array_merge([
                'name' => $role->name,
                'email' => $role->email,
                'timezone' => $role->timezone,
                'language_code' => 'en',
                'new_subdomain' => $role->subdomain,
            ], $fields));
    }

    private function optOutOfRequests(Role $role, User $user): void
    {
        $role->users()->updateExistingPivot($user->id, ['notification_settings' => json_encode(['new_request' => false])]);
    }

    private function postBookingRequest(Role $role): void
    {
        $this->postJson(route('event.booking_request.store', ['subdomain' => $role->subdomain]), [
            'contact_name' => 'Sam Guest',
            'contact_email' => 'sam.guest@gmail.com',
            'event_name' => 'Late Night Set',
            'date' => now()->addDays(7)->format('Y-m-d'),
            'start_time' => '20:00',
            'description' => 'An evening set.',
            'venue_name' => 'The Cellar',
        ])->assertOk();
    }

    private function assertSharedAddressNotified(string $address = self::SHARED): void
    {
        Notification::assertSentOnDemand(
            NewRequestsNotification::class,
            fn ($notification, $channels, $notifiable) => ($notifiable->routes['mail'] ?? null) === $address
        );
    }

    private function queuedRecipients(): array
    {
        $recipients = [];
        Queue::assertPushed(SendQueuedEmail::class, function (SendQueuedEmail $job) use (&$recipients) {
            $recipients[] = [(new \ReflectionProperty($job, 'recipient'))->getValue($job), (new \ReflectionProperty($job, 'mailable'))->getValue($job)];

            return true;
        });

        return $recipients;
    }

    /** The path and query of a link, which is what the test client needs. */
    private function relative(string $url): string
    {
        $parts = parse_url($url);

        return $parts['path'].(isset($parts['query']) ? '?'.$parts['query'] : '');
    }

    // -- Saving --------------------------------------------------------------------------------

    public function test_saving_an_address_stores_it_unconfirmed_and_sends_the_confirmation(): void
    {
        Mail::fake();
        $owner = $this->createOwner();
        $role = $this->requestSchedule($owner);

        $this->saveSchedule($owner, $role, [
            'notification_email' => self::SHARED,
            'notification_email_new_request' => '1',
            'notification_email_new_sale' => '1',
        ])->assertSessionHasNoErrors()->assertSessionHas('message');

        $role->refresh();
        $this->assertSame(self::SHARED, $role->notification_email);
        $this->assertNull($role->notification_email_verified_at);
        $this->assertTrue($role->notificationEmailSettings()['new_sale']);
        $this->assertFalse($role->notificationEmailSettings()['new_feedback'], 'a toggle not posted keeps its default');

        Mail::assertSent(NotificationEmailConfirmation::class, fn ($mail) => $mail->hasTo(self::SHARED));
    }

    public function test_the_same_address_in_another_case_keeps_its_confirmation(): void
    {
        Mail::fake();
        $owner = $this->createOwner();
        $role = $this->withVerifiedAddress($this->requestSchedule($owner));

        $this->saveSchedule($owner, $role, ['notification_email' => strtoupper(self::SHARED)])->assertSessionHasNoErrors();

        $this->assertNotNull($role->fresh()->notification_email_verified_at);
        Mail::assertNothingSent();
    }

    public function test_changing_the_address_starts_it_over_and_clearing_it_removes_it(): void
    {
        Mail::fake();
        $owner = $this->createOwner();
        $role = $this->withVerifiedAddress($this->requestSchedule($owner));

        $this->saveSchedule($owner, $role, ['notification_email' => 'other.inbox@gmail.com'])->assertSessionHasNoErrors();

        $role->refresh();
        $this->assertSame('other.inbox@gmail.com', $role->notification_email);
        $this->assertNull($role->notification_email_verified_at);
        Mail::assertSent(NotificationEmailConfirmation::class, fn ($mail) => $mail->hasTo('other.inbox@gmail.com'));

        $this->saveSchedule($owner, $role, ['notification_email' => ''])->assertSessionHasNoErrors();

        $role->refresh();
        $this->assertNull($role->notification_email);
        $this->assertNull($role->notification_email_verified_at);
    }

    public function test_a_hand_made_post_cannot_confirm_the_address(): void
    {
        Mail::fake();
        $owner = $this->createOwner();
        $role = $this->requestSchedule($owner);

        $this->saveSchedule($owner, $role, [
            'notification_email' => self::SHARED,
            'notification_email_verified_at' => now()->toDateTimeString(),
        ])->assertSessionHasNoErrors();

        $this->assertNull($role->fresh()->notification_email_verified_at);
    }

    public function test_a_viewer_cannot_set_the_address(): void
    {
        Mail::fake();
        $owner = $this->createOwner();
        $role = $this->requestSchedule($owner);
        $viewer = $this->createOwner();
        $role->users()->attach($viewer->id, ['level' => 'viewer']);

        $this->saveSchedule($viewer, $role, ['notification_email' => self::SHARED]);

        $this->assertNull($role->fresh()->notification_email);
        Mail::assertNothingSent();
    }

    public function test_changing_the_contact_email_leaves_the_shared_address_alone(): void
    {
        Mail::fake();
        $owner = $this->createOwner();
        $role = $this->withVerifiedAddress($this->requestSchedule($owner));

        $this->saveSchedule($owner, $role, ['email' => 'new.contact@gmail.com'])->assertSessionHasNoErrors();

        $role->refresh();
        $this->assertSame(self::SHARED, $role->notification_email);
        $this->assertNotNull($role->notification_email_verified_at);
    }

    public function test_the_confirmation_is_rate_limited(): void
    {
        Mail::fake();
        $owner = $this->createOwner();
        $role = $this->requestSchedule($owner);
        $role->notification_email = self::SHARED;
        $role->save();

        $service = app(NotificationEmailService::class);
        $this->assertSame('sent', $service->sendVerification($role));
        $this->assertSame('sent', $service->sendVerification($role));
        $this->assertSame('sent', $service->sendVerification($role));
        $this->assertSame('rate_limited', $service->sendVerification($role));

        $this->actingAs($owner)
            ->post(route('role.notification_email.resend', ['subdomain' => $role->subdomain]))
            ->assertSessionHas('error', __('messages.notification_email_resend_wait'));

        Mail::assertSent(NotificationEmailConfirmation::class, 3);
    }

    public function test_one_address_cannot_be_mailed_over_and_over_from_many_schedules(): void
    {
        Mail::fake();
        $service = app(NotificationEmailService::class);

        $results = [];
        for ($i = 0; $i < 4; $i++) {
            $role = $this->requestSchedule();
            $role->notification_email = self::SHARED;
            $role->save();
            $results[] = $service->sendVerification($role);
        }

        $this->assertSame(['sent', 'sent', 'sent', 'rate_limited'], $results);
        Mail::assertSent(NotificationEmailConfirmation::class, 3);
    }

    public function test_one_person_cannot_send_confirmations_without_limit(): void
    {
        Mail::fake();
        $owner = $this->createOwner();
        $service = app(NotificationEmailService::class);

        $results = [];
        for ($i = 0; $i < 11; $i++) {
            $role = $this->requestSchedule($owner);
            $role->notification_email = "inbox{$i}@gmail.com";
            $role->save();
            $results[] = $service->sendVerification($role, $owner);
        }

        $this->assertSame(array_merge(array_fill(0, 10, 'sent'), ['rate_limited']), $results);
    }

    /**
     * update() stores the address, then later returns early on a file type the image rule lets
     * through but the upload code refuses. The confirmation must already be on its way by then.
     */
    public function test_a_save_that_ends_early_still_sends_the_confirmation(): void
    {
        Mail::fake();
        $owner = $this->createOwner();
        $role = $this->requestSchedule($owner);

        $this->saveSchedule($owner, $role, [
            'notification_email' => self::SHARED,
            'profile_image' => UploadedFile::fake()->image('square.bmp', 200, 200),
        ])->assertSessionHasErrors('profile_image');

        $this->assertSame(self::SHARED, $role->fresh()->notification_email);
        Mail::assertSent(NotificationEmailConfirmation::class, fn ($mail) => $mail->hasTo(self::SHARED));
    }

    public function test_resend_returns_to_the_notifications_tab(): void
    {
        Mail::fake();
        $owner = $this->createOwner();
        $role = $this->requestSchedule($owner);
        $role->notification_email = self::SHARED;
        $role->save();

        $this->actingAs($owner)
            ->post(route('role.notification_email.resend', ['subdomain' => $role->subdomain]))
            ->assertRedirectContains('settings_tab=notifications')
            ->assertSessionHas('message');
    }

    public function test_no_confirmation_is_attempted_without_a_mailer(): void
    {
        Mail::fake();
        config(['mail.default' => 'log']);
        $owner = $this->createOwner();
        $role = $this->requestSchedule($owner);

        $this->saveSchedule($owner, $role, ['notification_email' => self::SHARED])
            ->assertSessionHas('error', __('messages.notification_email_no_mailer'));

        Mail::assertNothingSent();
    }

    // -- Confirming ----------------------------------------------------------------------------

    public function test_the_confirm_link_shows_a_button_and_only_the_post_confirms(): void
    {
        $role = $this->requestSchedule();
        $role->notification_email = self::SHARED;
        $role->save();

        $url = $this->relative(NotificationEmailService::verifyUrl($role));

        $this->get($url)->assertOk()->assertSee(__('messages.notification_email_confirm_button'));
        $this->assertNull($role->fresh()->notification_email_verified_at, 'a mail scanner opening the link confirms nothing');

        $this->post($url)->assertOk()->assertSee(__('messages.notification_email_confirmed_heading'));
        $this->assertNotNull($role->fresh()->notification_email_verified_at);
    }

    public function test_a_link_for_a_previous_address_cannot_confirm_the_current_one(): void
    {
        $role = $this->requestSchedule();
        $role->notification_email = 'old.inbox@gmail.com';
        $role->save();
        $oldLink = $this->relative(NotificationEmailService::verifyUrl($role));

        $role->setNotificationEmail(self::SHARED);
        $role->save();

        $this->post($oldLink)->assertOk()->assertSee(__('messages.notification_email_link_invalid_heading'));
        $this->assertNull($role->fresh()->notification_email_verified_at);
    }

    public function test_an_expired_or_tampered_link_is_refused(): void
    {
        $role = $this->requestSchedule();
        $role->notification_email = self::SHARED;
        $role->save();
        $url = $this->relative(NotificationEmailService::verifyUrl($role));

        $this->post($url.'x')->assertOk()->assertSee(__('messages.notification_email_link_invalid_heading'));

        $this->travel(NotificationEmailService::VERIFY_TTL_DAYS + 1)->days();
        $this->post($url)->assertOk()->assertSee(__('messages.notification_email_link_invalid_heading'));

        $this->assertNull($role->fresh()->notification_email_verified_at);
    }

    // -- What reaches the address -----------------------------------------------------------

    public function test_an_unconfirmed_address_receives_nothing(): void
    {
        Notification::fake();
        Mail::fake();
        $role = $this->requestSchedule();
        $role->notification_email = self::SHARED;
        $role->save();

        $this->postBookingRequest($role);

        Notification::assertNotSentTo(new AnonymousNotifiable, NewRequestsNotification::class);
        Notification::assertSentOnDemandTimes(NewRequestsNotification::class, 0);
    }

    public function test_a_confirmed_address_gets_the_request_notification(): void
    {
        Notification::fake();
        Mail::fake();
        $owner = $this->createOwner();
        $role = $this->withVerifiedAddress($this->requestSchedule($owner));

        $this->postBookingRequest($role);

        $this->assertSharedAddressNotified();
        Notification::assertSentTo($owner, NewRequestsNotification::class);
    }

    public function test_the_counter_moves_when_only_the_shared_address_was_told(): void
    {
        Notification::fake();
        Mail::fake();
        $owner = $this->createOwner();
        $role = $this->withVerifiedAddress($this->requestSchedule($owner));
        $this->optOutOfRequests($role, $owner);

        $this->postBookingRequest($role);

        $this->assertSharedAddressNotified();
        Notification::assertNotSentTo($owner, NewRequestsNotification::class);
        $this->assertSame(1, (int) $role->fresh()->last_notified_request_count, 'otherwise the noon digest tells the inbox again');
    }

    public function test_the_daily_digest_reaches_the_shared_address(): void
    {
        Notification::fake();
        Mail::fake();
        $owner = $this->createOwner();
        $role = $this->requestSchedule($owner);
        $this->optOutOfRequests($role, $owner);
        $this->postBookingRequest($role);

        // Confirmed only afterwards, so the booking request itself told nobody.
        $role = $this->withVerifiedAddress($role->fresh());
        $role->last_notified_request_count = 0;
        $role->save();

        $this->artisan('app:notify-request-changes')->assertSuccessful();

        $this->assertSharedAddressNotified();
    }

    public function test_a_switched_off_type_is_not_sent(): void
    {
        Notification::fake();
        Mail::fake();
        $role = $this->withVerifiedAddress($this->requestSchedule(), self::SHARED, ['new_request' => false]);

        $this->postBookingRequest($role);

        Notification::assertSentOnDemandTimes(NewRequestsNotification::class, 0);
    }

    public function test_an_address_that_is_also_a_notified_editors_login_is_not_sent_twice(): void
    {
        Notification::fake();
        Mail::fake();
        $owner = $this->createOwner();
        $role = $this->withVerifiedAddress($this->requestSchedule($owner), strtoupper($owner->email));

        $this->postBookingRequest($role);

        Notification::assertSentTo($owner, NewRequestsNotification::class);
        Notification::assertSentOnDemandTimes(NewRequestsNotification::class, 0);
    }

    public function test_an_editor_who_opted_out_does_not_suppress_the_shared_copy(): void
    {
        Notification::fake();
        Mail::fake();
        $owner = $this->createOwner();
        $role = $this->withVerifiedAddress($this->requestSchedule($owner), $owner->email);
        $this->optOutOfRequests($role, $owner);

        $this->postBookingRequest($role);

        Notification::assertNotSentTo($owner, NewRequestsNotification::class);
        $this->assertSharedAddressNotified($owner->email);
    }

    public function test_a_sale_reaches_the_address_only_when_switched_on(): void
    {
        Queue::fake();
        $owner = $this->createOwner();
        $role = $this->withVerifiedAddress($this->requestSchedule($owner), self::SHARED, ['new_sale' => true]);
        $event = $this->createEvent($role);
        $sale = $this->createSale($event, $role, ['email' => 'buyer@gmail.com']);

        app(EmailService::class)->sendNewSaleNotification($sale, $event, $role);

        $shared = array_values(array_filter($this->queuedRecipients(), fn ($r) => $r[0] === self::SHARED));
        $this->assertCount(1, $shared);
        $this->assertInstanceOf(NewSaleNotification::class, $shared[0][1]);

        $headers = $shared[0][1]->headers()->text;
        $this->assertStringContainsString('/ne/u/', $headers['List-Unsubscribe']);
        $this->assertSame('List-Unsubscribe=One-Click', $headers['List-Unsubscribe-Post']);
        $this->assertStringContainsString(__('messages.notification_email_unsubscribe_link'), $shared[0][1]->render());
    }

    public function test_a_sale_is_not_copied_when_its_toggle_is_off(): void
    {
        Queue::fake();
        $role = $this->withVerifiedAddress($this->requestSchedule());
        $event = $this->createEvent($role);
        $sale = $this->createSale($event, $role, ['email' => 'buyer@gmail.com']);

        app(EmailService::class)->sendNewSaleNotification($sale, $event, $role);

        Queue::assertNotPushed(SendQueuedEmail::class);
    }

    public function test_a_sale_follows_the_hosted_email_settings_rule(): void
    {
        Queue::fake();
        config(['app.hosted' => true]);
        $role = $this->withVerifiedAddress($this->requestSchedule(), self::SHARED, ['new_sale' => true]);
        $event = $this->createEvent($role);
        $sale = $this->createSale($event, $role, ['email' => 'buyer@gmail.com']);

        app(EmailService::class)->sendNewSaleNotification($sale, $event, $role);

        Queue::assertNotPushed(SendQueuedEmail::class);
    }

    public function test_the_request_copy_carries_the_address_s_own_unsubscribe(): void
    {
        $role = $this->withVerifiedAddress($this->requestSchedule());

        $message = (new NewRequestsNotification($role, 2))->toMail((new AnonymousNotifiable)->route('mail', self::SHARED));
        $html = (string) $message->render();

        $this->assertStringContainsString('/ne/u/', $html);
        $this->assertStringContainsString(__('messages.notification_email_unsubscribe_link'), $html);
        $this->assertStringNotContainsString(route('role.unsubscribe'), $html);
    }

    // -- Removing ------------------------------------------------------------------------------

    public function test_one_click_unsubscribe_removes_the_address_without_a_session(): void
    {
        $role = $this->withVerifiedAddress($this->requestSchedule());
        $url = $this->relative(NotificationEmailService::unsubscribeUrl($role));

        $this->get($url)->assertOk()->assertSee(__('messages.notification_email_unsubscribe_button'));
        $this->assertSame(self::SHARED, $role->fresh()->notification_email, 'a mail scanner opening the link removes nothing');

        // The body an RFC 8058 one-click POST carries. Tests skip CSRF altogether, so the
        // exemption that lets the mail provider's POST through is pinned separately below.
        $this->call('POST', $url, ['List-Unsubscribe' => 'One-Click'])->assertOk();

        $role->refresh();
        $this->assertNull($role->notification_email);
        $this->assertNull($role->notification_email_verified_at);
    }

    public function test_an_old_address_s_unsubscribe_cannot_remove_the_new_one(): void
    {
        $role = $this->withVerifiedAddress($this->requestSchedule(), 'old.inbox@gmail.com');
        $oldLink = $this->relative(NotificationEmailService::unsubscribeUrl($role));

        $role = $this->withVerifiedAddress($role, self::SHARED);

        $this->call('POST', $oldLink)->assertOk()->assertSee(__('messages.notification_email_unsubscribed_heading'));
        $this->assertSame(self::SHARED, $role->fresh()->notification_email);
    }

    public function test_the_unsubscribe_link_rests_on_its_token_not_a_signature(): void
    {
        $role = $this->withVerifiedAddress($this->requestSchedule());
        $url = $this->relative(NotificationEmailService::unsubscribeUrl($role));

        $this->assertStringNotContainsString('signature=', $url);

        // A mangled query string must not leave the address subscribed behind a page that says
        // it was removed.
        $this->call('POST', $url.'?signature=garbled')->assertOk();

        $this->assertNull($role->fresh()->notification_email);
    }

    public function test_an_unmatched_unsubscribe_link_does_not_name_the_schedule(): void
    {
        $role = $this->withVerifiedAddress($this->requestSchedule(null, ['name' => 'Hidden Cellar Nights']));
        $url = '/ne/u/'.UrlUtils::encodeId($role->id).'/'.str_repeat('0', 40);

        $this->get($url)->assertOk()->assertDontSee('Hidden Cellar Nights');
        $this->call('POST', $url)->assertOk()->assertDontSee('Hidden Cellar Nights');

        $this->assertSame(self::SHARED, $role->fresh()->notification_email);
    }

    /**
     * The one-click POST comes from the mail provider, with no session and no token, so it only
     * works because bootstrap/app.php exempts it. The confirm POST must stay protected.
     */
    public function test_only_the_unsubscribe_post_is_exempt_from_csrf(): void
    {
        $middleware = $this->app->make(ValidateCsrfToken::class);
        $inExceptArray = new \ReflectionMethod($middleware, 'inExceptArray');

        $this->assertTrue($inExceptArray->invoke($middleware, Request::create('/ne/u/abc/def', 'POST')));
        $this->assertFalse($inExceptArray->invoke($middleware, Request::create('/ne/c/abc/def', 'POST')));
    }

    // -- Where the address must not go ---------------------------------------------------------

    public function test_the_address_is_not_in_the_schedule_data_shared_with_other_users(): void
    {
        $role = $this->withVerifiedAddress($this->requestSchedule());

        $data = $role->toData();

        $this->assertArrayNotHasKey('notification_email', $data);
        $this->assertArrayNotHasKey('notification_email_settings', $data);
        $this->assertStringNotContainsString(self::SHARED, json_encode($data));
    }

    public function test_the_settings_page_shows_the_address_and_its_state(): void
    {
        $owner = $this->createOwner();
        $role = $this->requestSchedule($owner);
        $role->notification_email = self::SHARED;
        $role->save();

        $this->actingAs($owner)
            ->get(route('role.edit', ['subdomain' => $role->subdomain]))
            ->assertOk()
            ->assertSee('name="notification_email"', false)
            ->assertSee(__('messages.notification_email_pending'))
            ->assertSee('id="notification-email-resend-form"', false);
    }
}
