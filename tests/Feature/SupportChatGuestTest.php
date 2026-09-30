<?php

namespace Tests\Feature;

use App\Jobs\SendSupportReplyEmail;
use App\Mail\SupportMessageNotification;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Models\User;
use App\Utils\SupportPresence;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The support chat for signed-out visitors on the marketing site
 * (SupportChatGuestController + resources/js/components/SupportChatWidget.vue).
 */
class SupportChatGuestTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const TOKEN_HEADER = 'X-Support-Chat-Token';

    protected function setUp(): void
    {
        parent::setUp();

        // The stateless rule compares the request host with _base_domain(); see
        // MarketingEdgeCacheTest::test_the_test_client_reaches_the_base_domain.
        $this->pinAppUrl('https://eventschedule.test');
        Cache::flush();
    }

    private function admin(): User
    {
        $admin = $this->createOwner(true);
        $admin->name = 'Hillel Coren';
        $admin->save();

        return $admin;
    }

    private function adminActing(User $admin)
    {
        return $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);
    }

    private function startChat(array $data = [], array $headers = [])
    {
        return $this->withHeaders($headers)->postJson('/support-chat/guest/messages', array_merge([
            'body' => 'Can I sell tickets?',
            'page' => '/pricing',
        ], $data));
    }

    public function test_status_needs_the_admin_both_online_and_present(): void
    {
        $admin = $this->admin();

        $this->getJson('/support-chat/guest/status')
            ->assertOk()
            ->assertJsonPath('available', false)
            ->assertJsonPath('agent.name', 'Hillel')
            ->assertJsonPath('agent.initials', 'HC');

        SupportPresence::goOnline($admin);
        $this->getJson('/support-chat/guest/status')->assertJsonPath('available', true);

        // Laptop closed: no AP tab has checked in for longer than the away window.
        $this->travel(SupportPresence::AWAY_AFTER_MINUTES + 1)->minutes();
        $this->getJson('/support-chat/guest/status')->assertJsonPath('available', false);

        SupportPresence::heartbeat($admin);
        $this->getJson('/support-chat/guest/status')->assertJsonPath('available', true);
    }

    public function test_guest_endpoints_set_no_cookies_and_are_never_shared(): void
    {
        SupportPresence::goOnline($this->admin());

        $send = $this->startChat();
        $token = $send->json('token');
        $this->assertIsString($token);

        $responses = [
            'status' => $this->getJson('/support-chat/guest/status'),
            'send' => $send,
            'messages' => $this->withHeaders([self::TOKEN_HEADER => $token])->getJson('/support-chat/guest/messages?visible=1&page=/pricing'),
            'contact' => $this->withHeaders([self::TOKEN_HEADER => $token])->postJson('/support-chat/guest/contact', ['email' => 'visitor@example.com']),
            'read' => $this->withHeaders([self::TOKEN_HEADER => $token])->postJson('/support-chat/guest/read'),
        ];

        foreach ($responses as $name => $response) {
            $response->assertOk();
            $this->assertSame([], $response->headers->all('set-cookie'), $name.' must not hand a visitor a session cookie.');
            $cacheControl = (string) $response->headers->get('Cache-Control');
            $this->assertStringContainsString('private', $cacheControl, $name);
            $this->assertStringContainsString('no-store', $cacheControl, $name);
            $this->assertStringNotContainsString('public', $cacheControl, $name);
        }
    }

    public function test_the_posts_are_csrf_exempt_and_throttled_in_their_own_buckets(): void
    {
        foreach (['support-chat.guest.send', 'support-chat.guest.contact', 'support-chat.guest.read'] as $name) {
            $route = Route::getRoutes()->getByName($name);
            $this->assertNotNull($route, $name.' is not registered.');

            $middleware = app('router')->gatherRouteMiddleware($route);

            $this->assertNotContains(ValidateCsrfToken::class, $middleware, $name.' is called from a cached page whose token is useless.');
            $this->assertNotEmpty(preg_grep('/ThrottleRequests:\d+,\d+,support_guest_\w+$/', $middleware), $name.' needs a named throttle bucket.');
        }
    }

    public function test_the_first_message_starts_a_visitor_conversation(): void
    {
        SupportPresence::goOnline($this->admin());

        $response = $this->withHeaders(['CF-IPCountry' => 'de'])->startChat();

        $response->assertOk()->assertJsonPath('message.body', 'Can I sell tickets?');
        $token = $response->json('token');

        $conversation = SupportConversation::where('guest_token', $token)->firstOrFail();
        $this->assertNull($conversation->user_id);
        $this->assertSame('/pricing', $conversation->guest_page);
        $this->assertSame('DE', $conversation->guest_country);

        // The same token continues the same thread, and hands no new token back.
        $this->startChat(['body' => 'And take payments?'], [self::TOKEN_HEADER => $token])
            ->assertOk()
            ->assertJsonPath('token', null);

        $this->assertSame(1, SupportConversation::count());
        $this->assertSame(2, $conversation->messages()->count());

        $this->withHeaders([self::TOKEN_HEADER => $token])
            ->getJson('/support-chat/guest/messages')
            ->assertOk()
            ->assertJsonCount(2, 'messages');
    }

    public function test_an_unknown_token_reads_nothing(): void
    {
        SupportPresence::goOnline($this->admin());
        $this->startChat()->assertOk();

        $this->withHeaders([self::TOKEN_HEADER => str_repeat('x', 48)])
            ->getJson('/support-chat/guest/messages')
            ->assertNotFound();

        $this->getJson('/support-chat/guest/messages')->assertNotFound();
    }

    public function test_an_account_holders_conversation_cannot_be_read_as_a_guest(): void
    {
        $user = $this->createOwner();
        SupportConversation::create(['user_id' => $user->id, 'guest_token' => str_repeat('a', 48), 'status' => 'open']);

        $this->withHeaders([self::TOKEN_HEADER => str_repeat('a', 48)])
            ->getJson('/support-chat/guest/messages')
            ->assertNotFound();
    }

    public function test_the_honeypot_rejects_the_message(): void
    {
        SupportPresence::goOnline($this->admin());

        $this->startChat(['website' => 'https://spam.example'])->assertStatus(422);

        $this->assertSame(0, SupportConversation::count());
        $this->assertSame(0, SupportMessage::count());
    }

    public function test_an_email_is_required_while_nobody_is_available(): void
    {
        $this->admin();

        $this->startChat()->assertStatus(422)->assertJsonValidationErrors('email');
        $this->assertSame(0, SupportConversation::count());

        $this->startChat(['email' => 'visitor@example.com'])->assertOk()->assertJsonPath('has_email', true);
        $this->assertSame('visitor@example.com', SupportConversation::first()->guest_email);
    }

    public function test_the_page_is_stored_as_a_path_only(): void
    {
        SupportPresence::goOnline($this->admin());

        $this->startChat(['page' => 'javascript:alert(1)'])->assertOk();
        $this->assertNull(SupportConversation::latest('id')->first()->guest_page);

        $this->startChat(['page' => 'https://evil.example/pricing?x=1'])->assertOk();
        $this->assertSame('/pricing', SupportConversation::latest('id')->first()->guest_page);

        // A protocol-relative URL keeps only its path, never the foreign host.
        $this->startChat(['page' => '//evil.example/x'])->assertOk();
        $this->assertSame('/x', SupportConversation::latest('id')->first()->guest_page);
    }

    public function test_the_admin_is_emailed_only_when_away_and_at_most_once_per_burst(): void
    {
        Mail::fake();
        $admin = $this->admin();

        // Available: the in-AP alert is the channel, not email.
        SupportPresence::goOnline($admin);
        $this->startChat()->assertOk();
        Mail::assertNothingQueued();

        // Away: one email for a burst of messages.
        SupportPresence::goOffline();
        $token = $this->startChat(['email' => 'visitor@example.com'])->json('token');
        $this->startChat(['body' => 'Hello?'], [self::TOKEN_HEADER => $token]);
        $this->startChat(['body' => 'Anyone?'], [self::TOKEN_HEADER => $token]);

        Mail::assertQueued(SupportMessageNotification::class, 1);
        Mail::assertQueued(SupportMessageNotification::class, fn ($mail) => $mail->hasTo($admin->email));
    }

    public function test_the_read_endpoint_clears_the_visitors_unread_replies(): void
    {
        Queue::fake();
        $admin = $this->admin();
        SupportPresence::goOnline($admin);
        $token = $this->startChat()->json('token');
        $conversation = SupportConversation::firstOrFail();

        $this->adminActing($admin)->postJson(route('admin.support.reply', ['id' => UrlUtils::encodeId($conversation->id)]), ['body' => 'Yes!'])->assertOk();

        $this->withHeaders([self::TOKEN_HEADER => $token])->getJson('/support-chat/guest/messages')->assertJsonPath('unread_count', 1);
        $this->withHeaders([self::TOKEN_HEADER => $token])->postJson('/support-chat/guest/read')->assertOk();
        $this->withHeaders([self::TOKEN_HEADER => $token])->getJson('/support-chat/guest/messages')->assertJsonPath('unread_count', 0);
    }

    public function test_seen_and_typing_reach_the_visitor(): void
    {
        $admin = $this->admin();
        SupportPresence::goOnline($admin);
        $token = $this->startChat()->json('token');
        $id = UrlUtils::encodeId(SupportConversation::firstOrFail()->id);

        $this->withHeaders([self::TOKEN_HEADER => $token])->getJson('/support-chat/guest/messages')
            ->assertJsonPath('messages.0.read', false)
            ->assertJsonPath('agent_typing', false);

        $this->adminActing($admin)->postJson(route('admin.support.mark-read', ['id' => $id]))->assertOk();
        $this->adminActing($admin)->postJson(route('admin.support.typing', ['id' => $id]))->assertOk();

        $this->withHeaders([self::TOKEN_HEADER => $token])->getJson('/support-chat/guest/messages')
            ->assertJsonPath('messages.0.read', true)
            ->assertJsonPath('agent_typing', true);

        $this->travel(\App\Http\Controllers\SupportChatController::TYPING_SECONDS + 1)->seconds();

        $this->withHeaders([self::TOKEN_HEADER => $token])->getJson('/support-chat/guest/messages')
            ->assertJsonPath('agent_typing', false);
    }

    public function test_the_admin_inbox_shows_a_visitor_conversation(): void
    {
        $admin = $this->admin();
        SupportPresence::goOnline($admin);
        $this->withHeaders(['CF-IPCountry' => 'FR'])->startChat(['email' => $admin->email, 'name' => 'Marie']);
        $conversation = SupportConversation::firstOrFail();
        $id = UrlUtils::encodeId($conversation->id);

        $this->adminActing($admin)->getJson(route('admin.support.conversations'))
            ->assertOk()
            ->assertJsonPath('conversations.0.is_guest', true)
            ->assertJsonPath('conversations.0.display_name', 'Marie')
            ->assertJsonPath('conversations.0.guest_country', 'FR')
            ->assertJsonPath('conversations.0.online', true);

        // Used to be a fatal: $conversation->user->roles on a conversation with no user.
        $this->adminActing($admin)->getJson(route('admin.support.messages', ['id' => $id]))
            ->assertOk()
            ->assertJsonPath('user.is_guest', true)
            ->assertJsonPath('user.started_on', '/pricing')
            ->assertJsonPath('user.current_page', '/pricing')
            ->assertJsonPath('user.has_account', true)
            ->assertJsonPath('user.roles', [])
            ->assertJsonPath('messages.0.sender_name', 'Marie');
    }

    public function test_a_reply_to_an_absent_visitor_is_emailed_with_a_link_back_into_the_chat(): void
    {
        Mail::fake();
        $admin = $this->admin();
        SupportPresence::goOnline($admin);
        $token = $this->startChat(['email' => 'visitor@example.com'])->json('token');
        $conversation = SupportConversation::firstOrFail();

        // They left: the visible-poll presence ran out.
        Cache::forget($conversation->presenceKey());

        $this->adminActing($admin)->postJson(route('admin.support.reply', ['id' => UrlUtils::encodeId($conversation->id)]), ['body' => 'Yes, you can!'])->assertOk();

        // The queue runs synchronously under test, so the delayed job has already run.
        Mail::assertSent(SupportMessageNotification::class, function ($mail) use ($token) {
            $html = $mail->render();

            return $mail->hasTo('visitor@example.com')
                && $mail->hasReplyTo(config('app.support_email'))
                && str_contains($html, 'Yes, you can!')
                && str_contains($html, '/pricing#support-chat='.$token)
                && str_contains($html, 'Continue the conversation');
        });
    }

    public function test_the_widget_is_on_marketing_pages_for_signed_out_visitors_only(): void
    {
        // Online, so there IS a name that could leak into the shared, edge-cached HTML.
        SupportPresence::goOnline($this->admin());

        $response = $this->get('/pricing');

        $response->assertOk()->assertSee('id="es-support-chat-host"', false);

        $this->assertSame(1, preg_match('#<script type="application/json" id="es-support-chat-config">(.*?)</script>#s', $response->getContent(), $blob));
        $config = json_decode($blob[1], true);
        $this->assertSame('/support-chat/guest/status', $config['statusUrl']);
        $this->assertSame('website', $config['honeypotField']);
        $this->assertTrue($config['greet']);

        // And the page is still edge-cacheable: the widget adds nothing visitor-specific.
        $this->assertStringContainsString('public', $response->headers->get('Cache-Control'));
        $this->assertStringNotContainsString('Hillel', $response->getContent());

        $this->actingAs($this->createOwner())->get('/pricing')
            ->assertOk()
            ->assertDontSee('id="es-support-chat-host"', false);
    }

    /**
     * The layout's `! $errorPage` guard: a marketing.* route that ends in the 404 page renders
     * the marketing layout with a marketing route name, so only that guard keeps the widget off
     * it. No real GET marketing route 404s on its own, hence the probe route.
     */
    public function test_the_widget_is_not_on_the_404_page(): void
    {
        // Deep enough that none of the tenant /{subdomain}/... catch-alls registered before it can
        // take the request first - which is how an earlier, one-segment probe passed vacuously.
        $path = '/__support-404-probe/a/b/c/d/e';
        Route::middleware('web')->get($path, fn () => abort(404))->name('marketing.support_404_probe');
        Route::getRoutes()->refreshNameLookups();
        $this->assertSame(
            'marketing.support_404_probe',
            Route::getRoutes()->match(\Illuminate\Http\Request::create($path))->getName(),
            'The probe must be the route that answers, or this proves nothing.'
        );

        $this->get($path)
            ->assertNotFound()
            ->assertSee('Page Not Found', false)
            ->assertDontSee('id="es-support-chat-host"', false);
    }

    public function test_a_form_encoded_post_is_refused(): void
    {
        SupportPresence::goOnline($this->admin());

        // What a hidden auto-submitting form on another site would send.
        $this->post('/support-chat/guest/messages', ['body' => 'Spam', 'page' => '/'])->assertStatus(415);
        $this->assertSame(0, SupportConversation::count());

        $token = $this->startChat()->json('token');
        $this->withHeaders([self::TOKEN_HEADER => $token])
            ->post('/support-chat/guest/contact', ['email' => 'visitor@example.com'])
            ->assertStatus(415);
    }

    public function test_new_conversations_are_capped_per_ip_but_replies_are_not(): void
    {
        SupportPresence::goOnline($this->admin());

        $token = null;
        for ($i = 0; $i < \App\Http\Controllers\SupportChatGuestController::NEW_CONVERSATIONS_PER_HOUR; $i++) {
            $token = $this->startChat()->assertOk()->json('token');
        }

        $this->startChat()
            ->assertStatus(429)
            ->assertJsonPath('error', 'too_many_conversations');
        $this->assertSame(\App\Http\Controllers\SupportChatGuestController::NEW_CONVERSATIONS_PER_HOUR, SupportConversation::count());

        // Carrying on in an existing conversation is not a new conversation.
        $this->startChat(['body' => 'Follow-up'], [self::TOKEN_HEADER => $token])->assertOk();

        // And the cap lifts after the hour.
        $this->travel(61)->minutes();
        SupportPresence::goOnline(User::where('is_admin', true)->first());
        $this->startChat()->assertOk();
    }

    public function test_the_admin_push_is_limited_while_away(): void
    {
        Queue::fake();
        config(['services.onesignal.app_id' => 'test-app-id', 'services.onesignal.rest_api_key' => 'test-rest-key']);
        $admin = $this->admin();
        $admin->push_settings = ['enabled' => true];
        $admin->save();

        $token = $this->startChat(['email' => 'visitor@example.com'])->json('token');
        $this->startChat(['body' => 'Two'], [self::TOKEN_HEADER => $token]);
        $this->startChat(['body' => 'Three'], [self::TOKEN_HEADER => $token]);

        Queue::assertPushed(\App\Jobs\SendQueuedPush::class, 1);

        $this->travel(\App\Http\Controllers\SupportChatGuestController::PUSH_DEBOUNCE_MINUTES + 1)->minutes();
        $this->startChat(['body' => 'Four'], [self::TOKEN_HEADER => $token]);

        Queue::assertPushed(\App\Jobs\SendQueuedPush::class, 2);
    }

    public function test_the_admin_push_is_limited_while_available_too(): void
    {
        Queue::fake();
        config(['services.onesignal.app_id' => 'test-app-id', 'services.onesignal.rest_api_key' => 'test-rest-key']);
        $admin = $this->admin();
        $admin->push_settings = ['enabled' => true];
        $admin->save();
        SupportPresence::goOnline($admin);

        $token = $this->startChat()->json('token');
        $this->startChat(['body' => 'Two'], [self::TOKEN_HEADER => $token]);
        $this->startChat(['body' => 'Three'], [self::TOKEN_HEADER => $token]);

        Queue::assertPushed(\App\Jobs\SendQueuedPush::class, 1);
    }

    /**
     * Keyed on the Cloudflare-aware IP (PageView::clientIp), not the socket address: behind the
     * proxy every visitor could otherwise share one cap.
     */
    public function test_the_new_conversation_cap_is_per_visitor_ip_behind_cloudflare(): void
    {
        SupportPresence::goOnline($this->admin());
        $cap = \App\Http\Controllers\SupportChatGuestController::NEW_CONVERSATIONS_PER_HOUR;

        for ($i = 0; $i < $cap; $i++) {
            $this->startChat([], ['CF-Connecting-IP' => '203.0.113.10'])->assertOk();
        }
        $this->startChat([], ['CF-Connecting-IP' => '203.0.113.10'])->assertStatus(429);

        // A different visitor behind the same edge is unaffected.
        $this->startChat([], ['CF-Connecting-IP' => '203.0.113.20'])->assertOk();
    }

    public function test_messages_are_stored_as_typed(): void
    {
        SupportPresence::goOnline($this->admin());

        $this->startChat(['body' => 'We run <50 events a month, is Pro worth it?'])->assertOk();

        $this->assertSame('We run <50 events a month, is Pro worth it?', SupportMessage::firstOrFail()->body);
    }

    public function test_an_available_admin_gets_a_delayed_email_check_instead_of_an_email(): void
    {
        Queue::fake();
        Mail::fake();
        SupportPresence::goOnline($this->admin());

        $this->startChat()->assertOk();

        Mail::assertNothingQueued();
        Queue::assertPushed(\App\Jobs\NotifyAdminOfUnreadSupport::class, fn ($job) => $job->delay !== null);
    }

    public function test_leaving_an_email_after_a_reply_queues_the_reply_email(): void
    {
        $admin = $this->admin();
        SupportPresence::goOnline($admin);
        $token = $this->startChat()->json('token');
        $conversation = SupportConversation::firstOrFail();
        SupportMessage::create(['support_conversation_id' => $conversation->id, 'user_id' => $admin->id, 'body' => 'Answer', 'is_from_admin' => true]);

        Queue::fake();
        $this->withHeaders([self::TOKEN_HEADER => $token])
            ->postJson('/support-chat/guest/contact', ['email' => 'visitor@example.com'])
            ->assertOk();

        Queue::assertPushed(SendSupportReplyEmail::class, fn ($job) => $job->conversationId === $conversation->id);
    }
}
