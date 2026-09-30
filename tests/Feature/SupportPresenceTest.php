<?php

namespace Tests\Feature;

use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Models\User;
use App\Utils\SupportPresence;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The admin's side of support chat presence: the hourly "still available?" check with its grace
 * window (App\Utils\SupportPresence), the AP heartbeat, and the endpoints
 * partials/support-presence drives from every AP page.
 */
class SupportPresenceTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    private function adminActing(User $admin)
    {
        return $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);
    }

    public function test_going_online_asks_again_after_an_hour_and_lapses_after_the_grace_window(): void
    {
        $admin = $this->createOwner(true);
        SupportPresence::goOnline($admin);

        $this->travel(SupportPresence::CONFIRM_EVERY_MINUTES - 1)->minutes();
        SupportPresence::heartbeat($admin);
        $state = SupportPresence::state();
        $this->assertTrue($state['online']);
        $this->assertGreaterThan(0, $state['confirm_in'], 'Nothing is due before the hour is up.');

        $this->travel(2)->minutes();
        $state = SupportPresence::state();
        $this->assertTrue($state['online'], 'Still online during the grace window.');
        $this->assertSame(0, $state['confirm_in'], 'The hour is up, so the prompt is due.');
        $this->assertGreaterThan(0, $state['expires_in']);

        $this->travel(SupportPresence::GRACE_MINUTES)->minutes();
        $this->assertFalse(SupportPresence::isOnline(), 'Unconfirmed, the admin goes offline.');
        $this->assertFalse(SupportPresence::isAvailable());
    }

    public function test_confirming_restarts_the_hour(): void
    {
        $admin = $this->createOwner(true);
        SupportPresence::goOnline($admin);

        $this->travel(65)->minutes();
        $this->assertTrue(SupportPresence::confirm($admin));

        $this->travel(50)->minutes();
        $this->assertTrue(SupportPresence::isOnline(), 'Confirmed at 65 minutes, so still online at 115.');
        $this->assertGreaterThan(0, SupportPresence::state()['confirm_in']);
    }

    public function test_a_confirmation_after_the_lapse_does_not_revive_presence(): void
    {
        $admin = $this->createOwner(true);
        SupportPresence::goOnline($admin);

        $this->travel(SupportPresence::CONFIRM_EVERY_MINUTES + SupportPresence::GRACE_MINUTES + 1)->minutes();

        $this->assertFalse(SupportPresence::confirm($admin));
        $this->assertFalse(SupportPresence::isOnline());
    }

    public function test_the_legacy_key_is_ignored_and_cleared(): void
    {
        Cache::put(SupportPresence::LEGACY_KEY, true, now()->addHours(4));
        $this->assertFalse(SupportPresence::isOnline(), 'The old four-hour flag no longer means online.');

        SupportPresence::goOnline($this->createOwner(true));
        $this->assertFalse(Cache::has(SupportPresence::LEGACY_KEY));
    }

    public function test_the_ping_keeps_the_admin_present_and_reports_the_newest_unread_message(): void
    {
        $admin = $this->createOwner(true);
        $this->actingAs($admin)->postJson(route('support-chat.presence.online'))
            ->assertOk()
            ->assertJsonPath('presence.online', true)
            ->assertJsonPath('presence.available', true);

        $conversation = SupportConversation::create([
            'user_id' => null,
            'guest_token' => str_repeat('b', 48),
            'guest_name' => 'Marie',
            'status' => 'open',
            'last_message_at' => now(),
        ]);
        $message = SupportMessage::create([
            'support_conversation_id' => $conversation->id,
            'body' => 'Is there a free plan?',
            'is_from_admin' => false,
        ]);
        // Four minutes idle, then a ping: still present.
        $this->travel(4)->minutes();
        Cache::put($conversation->presenceKey(), ['page' => '/pricing'], now()->addMinutes(2));
        $this->adminActing($admin)->postJson(route('support-chat.presence.ping'))
            ->assertOk()
            ->assertJsonPath('presence.available', true)
            ->assertJsonPath('presence.is_agent', true)
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('latest_unread.id', $message->id)
            ->assertJsonPath('latest_unread.conversation_id', UrlUtils::encodeId($conversation->id))
            ->assertJsonPath('latest_unread.created_at', $message->created_at->toIso8601String())
            ->assertJsonPath('latest_unread.sender', 'Marie')
            ->assertJsonPath('latest_unread.page', '/pricing')
            ->assertJsonPath('latest_unread.is_guest', true)
            ->assertJsonPath('latest_unread.preview', 'Is there a free plan?');

        $this->travel(4)->minutes();
        $this->assertTrue(SupportPresence::isAvailable(), 'The ping at four minutes restarted the away window.');

        // No tab checks in: away, though still switched on.
        $this->travel(SupportPresence::AWAY_AFTER_MINUTES + 1)->minutes();
        $this->assertTrue(SupportPresence::isOnline());
        $this->assertFalse(SupportPresence::isAvailable());
    }

    /**
     * The presence endpoints skip the admin password re-check so a timer can call them from any
     * AP page. So who wrote, and what, is only handed out while that check is still fresh.
     */
    public function test_the_ping_hides_who_wrote_and_what_without_a_fresh_reauth(): void
    {
        $admin = $this->createOwner(true);
        SupportPresence::goOnline($admin);

        $conversation = SupportConversation::create([
            'user_id' => null,
            'guest_token' => str_repeat('d', 48),
            'guest_email' => 'visitor@example.com',
            'status' => 'open',
            'last_message_at' => now(),
        ]);
        $message = SupportMessage::create(['support_conversation_id' => $conversation->id, 'body' => 'Private question', 'is_from_admin' => false]);

        $response = $this->actingAs($admin)->postJson(route('support-chat.presence.ping'))->assertOk();

        $response->assertJsonPath('latest_unread.id', $message->id)
            ->assertJsonPath('latest_unread.conversation_id', UrlUtils::encodeId($conversation->id));
        $this->assertArrayNotHasKey('sender', $response->json('latest_unread'));
        $this->assertArrayNotHasKey('preview', $response->json('latest_unread'));
        $this->assertStringNotContainsString('visitor@example.com', $response->getContent());
    }

    /**
     * Presence belongs to the admin who switched chat on. Another admin's open tab must not keep
     * them looking available, nor answer their hourly question.
     */
    public function test_another_admins_tab_does_not_keep_the_agent_present(): void
    {
        $agent = $this->createOwner(true);
        $other = $this->createOwner(true);
        SupportPresence::goOnline($agent);

        $this->travel(SupportPresence::AWAY_AFTER_MINUTES - 1)->minutes();
        $this->actingAs($other)->postJson(route('support-chat.presence.ping'))
            ->assertOk()
            ->assertJsonPath('presence.online', true)
            ->assertJsonPath('presence.is_agent', false);

        $this->travel(2)->minutes();
        $this->assertTrue(SupportPresence::isOnline());
        $this->assertFalse(SupportPresence::isAvailable(), "Only the agent's own pings count.");

        $this->assertFalse(SupportPresence::confirm($other));
        $this->assertTrue(SupportPresence::confirm($agent));
    }

    /**
     * The same browser binding EnsureUserIsAdmin enforces: a session confirmed in one browser
     * and replayed from another does not get the sender or preview.
     */
    public function test_the_ping_hides_who_wrote_and_what_from_another_browser(): void
    {
        $admin = $this->createOwner(true);
        SupportPresence::goOnline($admin);
        $conversation = SupportConversation::create(['user_id' => null, 'guest_token' => str_repeat('f', 48), 'guest_name' => 'Marie', 'status' => 'open', 'last_message_at' => now()]);
        SupportMessage::create(['support_conversation_id' => $conversation->id, 'body' => 'Private question', 'is_from_admin' => false]);

        $response = $this->withSession([
            'admin_password_confirmed_at' => now()->timestamp,
            \App\Utils\AdminReauthUtils::USER_AGENT_KEY => 'The browser the password was typed in',
        ])->withHeader('User-Agent', 'Somewhere else entirely')
            ->actingAs($admin)
            ->postJson(route('support-chat.presence.ping'))
            ->assertOk();

        $this->assertArrayNotHasKey('sender', $response->json('latest_unread'));
        $this->assertArrayNotHasKey('preview', $response->json('latest_unread'));
    }

    /**
     * A switch-off must never fail: if the presence lock cannot be had, it goes ahead unlocked
     * rather than erroring (which also covers adminReply, where the reply is already saved).
     */
    public function test_going_offline_still_works_while_the_lock_is_held(): void
    {
        SupportPresence::goOnline($this->createOwner(true));
        $held = Cache::lock(SupportPresence::KEY.'_lock', 10);
        $this->assertTrue($held->get());

        SupportPresence::goOffline();

        $this->assertFalse(SupportPresence::isOnline());
        $held->release();
    }

    public function test_a_ping_while_offline_does_not_make_the_admin_present(): void
    {
        $admin = $this->createOwner(true);

        $this->actingAs($admin)->postJson(route('support-chat.presence.ping'))
            ->assertOk()
            ->assertJsonPath('presence.online', false);

        $this->assertFalse(Cache::has(SupportPresence::HEARTBEAT_KEY.'_'.$admin->id));
    }

    public function test_the_presence_endpoints_are_admin_only(): void
    {
        $owner = $this->createOwner();

        foreach (['ping', 'confirm', 'online', 'offline'] as $action) {
            $this->actingAs($owner)->postJson(route('support-chat.presence.'.$action))->assertForbidden();
        }

        $this->assertFalse(SupportPresence::isOnline());
    }

    public function test_the_presence_endpoints_do_not_need_the_admin_reauth_window(): void
    {
        // No admin_password_confirmed_at in the session: /admin itself would answer 423.
        $admin = $this->createOwner(true);

        $this->actingAs($admin)->postJson(route('support-chat.presence.online'))->assertOk();
        $this->actingAs($admin)->postJson(route('support-chat.presence.offline'))
            ->assertOk()
            ->assertJsonPath('presence.online', false);
    }

    public function test_the_inbox_switch_sets_the_state_it_shows_rather_than_flipping(): void
    {
        $admin = $this->createOwner(true);

        // Twice "on" stays on. The old endpoint flipped, so a stale page switched chat off.
        $this->adminActing($admin)->postJson(route('admin.support.toggle-availability'), ['available' => true])
            ->assertOk()->assertJsonPath('available', true);
        $this->adminActing($admin)->postJson(route('admin.support.toggle-availability'), ['available' => true])
            ->assertOk()->assertJsonPath('available', true);

        $this->adminActing($admin)->postJson(route('admin.support.toggle-availability'), ['available' => false])
            ->assertOk()->assertJsonPath('available', false);

        $this->adminActing($admin)->postJson(route('admin.support.toggle-availability'))
            ->assertStatus(422);
    }

    public function test_replying_counts_as_confirming(): void
    {
        Queue::fake();
        $admin = $this->createOwner(true);
        SupportPresence::goOnline($admin);

        $user = $this->createOwner();
        $conversation = SupportConversation::create(['user_id' => $user->id, 'status' => 'open', 'last_message_at' => now()]);

        $this->travel(65)->minutes();
        $this->assertSame(0, SupportPresence::state()['confirm_in']);

        $this->adminActing($admin)->postJson(route('admin.support.reply', ['id' => UrlUtils::encodeId($conversation->id)]), ['body' => 'Hi!'])->assertOk();

        $this->assertGreaterThan(0, SupportPresence::state()['confirm_in']);
        $this->assertTrue(SupportPresence::isAvailable());
    }

    public function test_the_ap_renders_the_presence_driver_for_admins_and_the_chat_widget_for_everyone_else(): void
    {
        $admin = $this->createOwner(true);
        $this->actingAs($admin)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('id="support-presence-app"', false)
            ->assertSee('data-popover="support"', false)
            ->assertDontSee('id="support-chat-widget"', false);

        $owner = $this->createOwner();
        $this->actingAs($owner)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('id="support-chat-widget"', false)
            ->assertDontSee('id="support-presence-app"', false)
            ->assertDontSee('data-popover="support"', false);
    }
}
