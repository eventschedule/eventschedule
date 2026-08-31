<?php

namespace Tests\Feature;

use App\Jobs\SendQueuedEmail;
use App\Mail\SubscriptionConfirmation;
use App\Models\NewsletterUnsubscribe;
use App\Models\Role;
use App\Models\RoleSubscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Account-less audience capture.
 *
 * The point of the feature is that following stops costing a user account: RoleController::follow()
 * bounces a signed-out visitor to sign_up, which is why 139k guest page views produced 764
 * followers. So the load-bearing assertion in most of these is not "a row was written" but "and NO
 * account was created", and not "one email went out" but "to whom".
 */
class RoleSubscriberTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();
        $this->role = $this->createRole($this->createOwner());
    }

    private function joinUrl(): string
    {
        return route('role.audience.join', ['subdomain' => $this->role->subdomain]);
    }

    public function test_a_signed_out_visitor_subscribes_with_only_an_email(): void
    {
        $userCount = User::count();

        $this->post($this->joinUrl(), ['email' => 'fan@fans.test', 'name' => 'A Fan'])
            ->assertRedirect();

        $sub = RoleSubscriber::where('role_id', $this->role->id)->first();
        $this->assertNotNull($sub);
        $this->assertSame('fan@fans.test', $sub->email);

        // The whole point: no account, and no follower pivot.
        $this->assertSame($userCount, User::count(), 'subscribing must not create a user account');
        // The owner's own pivot row is level 'owner' and always present; what must NOT appear is
        // a follower.
        $this->assertSame(0, \DB::table('role_user')
            ->where('role_id', $this->role->id)
            ->where('level', 'follower')
            ->count());
    }

    public function test_a_new_subscriber_is_unconfirmed_and_is_sent_a_confirmation(): void
    {
        Queue::fake();

        $this->post($this->joinUrl(), ['email' => 'fan@fans.test']);

        $sub = RoleSubscriber::first();
        $this->assertNull($sub->confirmed_at, 'a fresh subscriber must not be mailable yet');

        Queue::assertPushed(SendQueuedEmail::class, function ($job) {
            return $this->queuedMailable($job) instanceof SubscriptionConfirmation
                && $this->queuedRecipient($job) === 'fan@fans.test';
        });
    }

    public function test_confirming_makes_the_row_mailable(): void
    {
        $this->post($this->joinUrl(), ['email' => 'fan@fans.test']);
        $sub = RoleSubscriber::first();

        $this->get(route('subscriber.confirm', ['token' => $sub->confirm_token]))->assertOk();

        $this->assertNotNull($sub->fresh()->confirmed_at);
        $this->assertSame(1, RoleSubscriber::confirmed()->count());
    }

    public function test_email_case_and_whitespace_do_not_create_a_second_row(): void
    {
        $this->post($this->joinUrl(), ['email' => 'Fan@Fans.test']);
        $this->post($this->joinUrl(), ['email' => '  fan@fans.test  ']);

        $this->assertSame(1, RoleSubscriber::count());
        $this->assertSame('fan@fans.test', RoleSubscriber::first()->email);
    }

    public function test_a_duplicate_submission_is_idempotent_and_says_nothing_different(): void
    {
        $first = $this->post($this->joinUrl(), ['email' => 'fan@fans.test']);
        $second = $this->post($this->joinUrl(), ['email' => 'fan@fans.test']);

        $this->assertSame(1, RoleSubscriber::count());

        // No membership oracle: an unauthenticated caller must not be able to tell a new address
        // from a known one. This is exactly where WaitlistController::join() leaks.
        $this->assertSame(
            session('message'),
            $second->baseResponse->getSession()->get('message'),
        );
        $first->assertRedirect();
        $second->assertRedirect();
    }

    public function test_a_filled_honeypot_writes_nothing_and_flashes_an_error(): void
    {
        $this->post($this->joinUrl(), [
            'email' => 'fan@fans.test',
            'website' => 'http://spam.example',
        ])->assertSessionHas('subscribe_error');

        $this->assertSame(0, RoleSubscriber::count());
    }

    public function test_an_absent_honeypot_is_allowed(): void
    {
        // Every non-browser caller omits the field entirely; a has() check instead of filled()
        // would break all of them at once.
        $this->post($this->joinUrl(), ['email' => 'fan@fans.test']);

        $this->assertSame(1, RoleSubscriber::count());
    }

    public function test_the_json_path_returns_200_on_a_tripped_honeypot(): void
    {
        // Not an error status: the modal's caller throws a generic failure on !response.ok and
        // only renders data.message on a 200.
        $this->postJson($this->joinUrl(), [
            'email' => 'fan@fans.test',
            'website' => 'http://spam.example',
        ])->assertOk()->assertJson(['success' => false]);

        $this->assertSame(0, RoleSubscriber::count());
    }

    public function test_subscribing_never_clears_an_existing_unsubscribe(): void
    {
        NewsletterUnsubscribe::create([
            'role_id' => $this->role->id,
            'email' => 'fan@fans.test',
            'unsubscribed_at' => now(),
        ]);

        $this->post($this->joinUrl(), ['email' => 'fan@fans.test']);

        // Reversing an explicit "no" from an unauthenticated POST is worse than the accepted
        // single-opt-in risk. Only confirming, which proves mailbox possession, may lift it.
        $this->assertSame(1, NewsletterUnsubscribe::where('email', 'fan@fans.test')->count());
    }

    public function test_confirming_does_lift_a_previous_unsubscribe(): void
    {
        $this->post($this->joinUrl(), ['email' => 'fan@fans.test']);
        NewsletterUnsubscribe::create([
            'role_id' => $this->role->id,
            'email' => 'fan@fans.test',
            'unsubscribed_at' => now(),
        ]);

        $this->get(route('subscriber.confirm', ['token' => RoleSubscriber::first()->confirm_token]));

        $this->assertSame(0, NewsletterUnsubscribe::where('email', 'fan@fans.test')->count());
    }

    public function test_one_click_unsubscribe_writes_the_shared_list(): void
    {
        $this->post($this->joinUrl(), ['email' => 'fan@fans.test']);
        $sub = RoleSubscriber::first();

        $this->post('/sub/u/'.$sub->token)->assertOk();

        $this->assertSame(1, NewsletterUnsubscribe::where('role_id', $this->role->id)
            ->where('email', 'fan@fans.test')->count());
    }

    public function test_the_one_click_path_is_exempt_from_csrf(): void
    {
        // Asserted against the middleware's own matcher, NOT by posting without a token.
        // VerifyCsrfToken::handle() short-circuits on runningUnitTests(), so a feature-test POST
        // succeeds whether or not the exemption exists - that version of this test passed with
        // 'sub/u/*' deleted from bootstrap/app.php, i.e. it pinned nothing.
        $middleware = new \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken(
            app(), app('encrypter')
        );

        $matches = new \ReflectionMethod($middleware, 'inExceptArray');
        $matches->setAccessible(true);

        $request = \Illuminate\Http\Request::create('/sub/u/'.str_repeat('a', 64), 'POST');

        $this->assertTrue(
            $matches->invoke($middleware, $request),
            "sub/u/* must be in bootstrap/app.php's validateCsrfTokens(except:) list - a mail "
            .'client one-click POST carries no session and no token.'
        );
    }

    public function test_unsubscribe_all_covers_every_schedule_the_address_reaches(): void
    {
        $other = $this->createRole($this->createOwner(), 'talent');
        $this->post($this->joinUrl(), ['email' => 'fan@fans.test']);
        $this->post(route('role.audience.join', ['subdomain' => $other->subdomain]), ['email' => 'fan@fans.test']);

        $this->post('/sub/u/'.RoleSubscriber::first()->token, ['all' => 1])->assertOk();

        // Without this a fan following six venues needs six links, and presses Report spam instead.
        $this->assertSame(2, NewsletterUnsubscribe::where('email', 'fan@fans.test')->count());
    }

    public function test_a_single_unsubscribe_does_not_touch_another_schedule(): void
    {
        $other = $this->createRole($this->createOwner(), 'talent');
        $this->post($this->joinUrl(), ['email' => 'fan@fans.test']);
        $this->post(route('role.audience.join', ['subdomain' => $other->subdomain]), ['email' => 'fan@fans.test']);

        $this->post('/sub/u/'.RoleSubscriber::where('role_id', $this->role->id)->first()->token)->assertOk();

        $this->assertSame(1, NewsletterUnsubscribe::where('email', 'fan@fans.test')->count());
        $this->assertSame(0, NewsletterUnsubscribe::where('role_id', $other->id)->count());
    }

    public function test_the_signed_in_follow_path_is_unchanged(): void
    {
        // Regression guard on the consent-modal edit: an account holder still gets a real
        // role_user follower row and still lands on /following.
        $user = $this->createOwner();

        $this->actingAs($user)
            ->get(route('role.follow', ['subdomain' => $this->role->subdomain]))
            ->assertRedirect();

        $this->assertSame(1, \DB::table('role_user')
            ->where('role_id', $this->role->id)
            ->where('user_id', $user->id)
            ->where('level', 'follower')
            ->count());
    }

    public function test_subscriber_emails_never_reach_a_guest_surface(): void
    {
        $this->post($this->joinUrl(), ['email' => 'private@fans.test']);

        $this->get($this->role->getGuestUrl())
            ->assertOk()
            ->assertDontSee('private@fans.test');
    }

    public function test_the_panel_renders_for_a_signed_out_visitor(): void
    {
        // The header Follow button loops the event's claimed PERFORMERS and is gated on
        // isClaimed() + hosted, so on a venue event with no claimed talent this panel is the only
        // capture surface there is.
        $event = $this->createEvent($this->role);

        $this->get($this->guestEventUrl($this->role, $event))
            ->assertOk()
            ->assertSee(route('role.audience.join', ['subdomain' => $this->role->subdomain]), false);
    }

    public function test_the_event_page_panel_does_not_nest_a_second_card(): void
    {
        // The event page includes the panel INSIDE the right column's container, which already
        // carries bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm sm:rounded-2xl p-6 sm:p-8.
        // With no $panelClass the partial fell through to its default and rendered every one of
        // those again: a white card nested inside an identical white card, backdrop-blur stacked
        // on backdrop-blur, and the contents inset a further 24-32px from the rest of the column.
        //
        // Local dev cannot show this - every schedule here belongs to the demo user, so the panel
        // is (correctly) suppressed - which is exactly why it is pinned here instead.
        $event = $this->createEvent($this->role);

        $html = $this->get($this->guestEventUrl($this->role, $event))->assertOk()->getContent();

        preg_match('/id="subscribe-panel" class="([^"]*)"/', $html, $m);
        $this->assertNotEmpty($m, 'the panel did not render on the event page');

        $this->assertStringNotContainsString('backdrop-blur-sm', $m[1],
            'the panel must not repeat the card treatment its container already applies');
        $this->assertStringContainsString('border-t', $m[1],
            'the panel should separate itself with a rule, not a nested card');
    }

    public function test_the_schedule_page_panel_keeps_its_own_card_and_padding(): void
    {
        // The other side of the same change: padding moved INSIDE $panelClass, so a caller that
        // passes one now owns it. If the schedule page's class string ever loses the padding
        // again, the form renders flush against the card edge.
        $html = $this->get($this->role->getGuestUrl())->assertOk()->getContent();

        preg_match('/id="subscribe-panel" class="([^"]*)"/', $html, $m);
        $this->assertNotEmpty($m, 'the panel did not render on the schedule page');

        $this->assertStringContainsString('rounded-2xl', $m[1]);
        $this->assertStringContainsString('p-6', $m[1]);
    }

    public function test_the_panel_is_hidden_from_a_signed_in_visitor(): void
    {
        // It carries a honeypot, and the repo's rule is that an authenticated page must never
        // render one - a password manager could fill it. HoneypotTest pins the same thing from
        // the other side.
        $event = $this->createEvent($this->role);

        $this->actingAs($this->createOwner())
            ->get($this->guestEventUrl($this->role, $event))
            ->assertOk()
            ->assertDontSee(route('role.audience.join', ['subdomain' => $this->role->subdomain]), false);
    }

    public function test_every_language_defines_its_own_subscription_copy(): void
    {
        // Read the language FILES, never the rendered mail. __() silently falls back to English,
        // so a test that renders in each locale and greps for an unresolved key passes with a
        // whole language file deleted.
        $keys = [
            'subscribe_panel_heading',
            'subscribe_panel_body',
            'subscription_confirm_subject',
            'subscription_confirm_button',
            'subscription_unsubscribe_confirm',
            'all_subscribers',
        ];

        $english = require resource_path('lang/en/messages.php');

        foreach (config('app.supported_languages') as $lang => $label) {
            $messages = require resource_path('lang/'.$lang.'/messages.php');

            foreach ($keys as $key) {
                $this->assertArrayHasKey($key, $messages, "{$lang} is missing {$key}");

                if ($lang === 'en') {
                    continue;
                }

                $this->assertNotSame(
                    $english[$key],
                    $messages[$key],
                    "{$lang}.{$key} is still the English string"
                );
            }
        }
    }

    public function test_the_follow_trigger_carries_a_subscribe_url_for_the_modal(): void
    {
        // The modal is a single chokepoint in front of the Follow buttons, and it reads the target
        // from the trigger. Without this attribute the guest branch has nowhere to post and
        // silently falls back to the account route it was meant to replace.
        //
        // Asserted on the SCHEDULE page, not the event page: the event page's only trigger sits
        // inside a loop over the event's claimed performers, so a venue event with no claimed
        // talent renders none at all. That gap is exactly why the subscribe panel exists, and it
        // is covered by test_the_panel_renders_for_a_signed_out_visitor.
        $this->get($this->role->getGuestUrl())
            ->assertOk()
            ->assertSee(route('role.audience.join', ['subdomain' => $this->role->subdomain]), false);
    }

    public function test_the_modal_emits_exactly_one_honeypot_and_no_blade_component(): void
    {
        // A literal component tag inside the modal's JS comments got compiled by Blade into a
        // second, real honeypot - inside a JavaScript string - which broke the page's honeypot
        // accounting and leaked a decoy onto authenticated pages. Blade compiles component tags
        // anywhere in the file, comments included.
        $event = $this->createEvent($this->role);

        $html = $this->actingAs($this->createOwner())
            ->get($this->guestEventUrl($this->role, $event))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('name="website"', $html);
    }

    public function test_an_rsvp_opt_in_captures_the_buyer(): void
    {
        // Guest buyers are the largest uncaptured audience in the product: the existing follower
        // attach only fires inside `! $user && create_account && hosted`.
        $event = $this->createEvent($this->role, ['rsvp_enabled' => true]);

        $this->post(route('event.rsvp', ['subdomain' => $this->role->subdomain]), [
            'name' => 'A Fan',
            'email' => 'fan@fans.test',
            'event_id' => \App\Utils\UrlUtils::encodeId($event->id),
            'event_date' => $event->getStartDateTime()->format('Y-m-d'),
            'audience_opt_in' => '1',
        ]);

        $sub = RoleSubscriber::where('email', 'fan@fans.test')->first();
        $this->assertNotNull($sub, 'a ticked opt-in must capture the buyer');
        $this->assertSame($this->role->id, $sub->role_id);

        // Confirmed on the spot: the same address is simultaneously receiving a transactional
        // receipt, so it is proven by use, and a second "please confirm" mail next to an RSVP
        // confirmation reads as a bug.
        $this->assertNotNull($sub->confirmed_at);
        $this->assertSame('checkout', $sub->source);
    }

    public function test_the_opt_in_prefers_the_owning_schedule_over_the_storefront(): void
    {
        // A curator's storefront listing a venue's event: the opt-in belongs to whoever owns the
        // event, because sales.subdomain is a booking-time snapshot that a rename never rewrites.
        // Without this the previous test passes purely on the fallback branch.
        $curator = $this->createRole($this->createOwner(), 'curator');
        $event = $this->createEvent($this->role, ['rsvp_enabled' => true]);
        $event->forceFill(['creator_role_id' => $this->role->id])->save();
        $event->roles()->syncWithoutDetaching([$curator->id => ['is_accepted' => true]]);

        $this->post(route('event.rsvp', ['subdomain' => $curator->subdomain]), [
            'name' => 'A Fan',
            'email' => 'fan@fans.test',
            'event_id' => \App\Utils\UrlUtils::encodeId($event->id),
            'event_date' => $event->getStartDateTime()->format('Y-m-d'),
            'audience_opt_in' => '1',
        ]);

        $sub = RoleSubscriber::where('email', 'fan@fans.test')->first();
        $this->assertNotNull($sub);
        $this->assertSame($this->role->id, $sub->role_id, 'the owning schedule must win over the storefront');
    }

    public function test_an_rsvp_without_the_opt_in_captures_nobody(): void
    {
        // Unchecked by default is the GDPR Art. 4(11) position, so the absent case is the one
        // that matters most.
        $event = $this->createEvent($this->role, ['rsvp_enabled' => true]);

        $this->post(route('event.rsvp', ['subdomain' => $this->role->subdomain]), [
            'name' => 'A Fan',
            'email' => 'fan@fans.test',
            'event_id' => \App\Utils\UrlUtils::encodeId($event->id),
            'event_date' => $event->getStartDateTime()->format('Y-m-d'),
        ]);

        $this->assertSame(0, RoleSubscriber::count());
    }

    public function test_a_confirmation_link_cannot_resurrect_an_unsubscribe(): void
    {
        // The defect this replaced: confirm() lifted the suppression unconditionally and the link
        // never expired, so subscribe -> confirm -> unsubscribe -> reopening the ORIGINAL
        // confirmation email silently re-subscribed. Because it is a GET, "reopening" includes a
        // corporate mail gateway prefetching links.
        $this->post($this->joinUrl(), ['email' => 'fan@fans.test']);
        $sub = RoleSubscriber::first();
        $liveConfirmUrl = route('subscriber.confirm', ['token' => $sub->confirm_token]);

        $this->get($liveConfirmUrl)->assertOk();
        $this->post('/sub/u/'.$sub->token)->assertOk();

        // Replay the link that is still sitting in their inbox. 410, not 404: the link WAS
        // valid, and the dead end it used to produce was indistinguishable from a broken site.
        // What must not change is that the replay changes nothing.
        $replay = $this->get($liveConfirmUrl);
        $replay->assertStatus(410);
        $replay->assertSee(__('messages.subscription_link_expired_heading'));

        // The page is reached with no row in hand, so it can say nothing about who or what.
        $replay->assertDontSee('fan@fans.test');
        $replay->assertDontSee($this->role->name, false);

        $this->assertSame(1, NewsletterUnsubscribe::where('email', 'fan@fans.test')->count(),
            'a replayed confirmation link must not lift a suppression');
    }

    public function test_unsubscribing_kills_a_confirmation_link_that_was_never_used(): void
    {
        // The other order: they unsubscribe from the confirmation email itself, without ever
        // confirming. The confirm link in that same email must die with it.
        $this->post($this->joinUrl(), ['email' => 'fan@fans.test']);
        $sub = RoleSubscriber::first();
        $liveConfirmUrl = route('subscriber.confirm', ['token' => $sub->confirm_token]);

        $this->post('/sub/u/'.$sub->token)->assertOk();
        $this->get($liveConfirmUrl)->assertStatus(410);

        $this->assertNull($sub->fresh()->confirmed_at);
        $this->assertSame(1, NewsletterUnsubscribe::where('email', 'fan@fans.test')->count());
    }

    public function test_somebody_who_unsubscribed_can_subscribe_again(): void
    {
        // The flip side of never lifting a suppression from the form: there has to be a way back,
        // or an unsubscribe is permanent even for the person who changes their mind.
        $this->post($this->joinUrl(), ['email' => 'fan@fans.test']);
        $sub = RoleSubscriber::first();
        $this->get(route('subscriber.confirm', ['token' => $sub->confirm_token]));
        $this->post('/sub/u/'.$sub->token);

        // Fill the form in again: a fresh confirmation goes out.
        $this->post($this->joinUrl(), ['email' => 'fan@fans.test']);
        $fresh = $sub->fresh();
        $this->assertNotNull($fresh->confirm_token, 'a suppressed address must get a new confirmation');

        $this->get(route('subscriber.confirm', ['token' => $fresh->confirm_token]))->assertOk();
        $this->assertSame(0, NewsletterUnsubscribe::where('email', 'fan@fans.test')->count());
    }

    public function test_a_viewer_cannot_delete_a_subscriber(): void
    {
        // isMember() is ['owner','admin','viewer']. A viewer may read the audience tab and must
        // not be able to destroy it. The stranger test above passes either way.
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $viewer = $this->createOwner();
        $viewer->roles()->attach($role->id, ['level' => 'viewer']);
        $sub = RoleSubscriber::create([
            'role_id' => $role->id, 'email' => 'fan@fans.test',
            'token' => RoleSubscriber::newToken(), 'confirmed_at' => now(),
        ]);

        $this->actingAs($viewer)
            ->delete(route('role.subscribers.remove', ['subdomain' => $role->subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($sub->id)]))
            ->assertStatus(403);

        $this->assertSame(1, RoleSubscriber::count());
    }

    public function test_a_rejected_address_is_visible_and_does_not_pop_the_ticket_modal(): void
    {
        // Guest layouts render no per-field errors, so a ValidationException here would redirect
        // back showing nothing and the form would look dead. Worse, event/show-guest.blade.php
        // keys on $errors->any() to force-open the RSVP / purchase modal, so a bad address would
        // pop the wrong dialog.
        $response = $this->from($this->role->getGuestUrl())
            ->post($this->joinUrl(), ['email' => 'not-an-email']);

        // The rejection has to be VISIBLE: guest layouts toast this key.
        $response->assertSessionHas('subscribe_error');

        // ...and it must not be the key that opens the modal. This is what the test name has
        // always claimed and what it did not previously check: session('error') sits in the same
        // @if as $errors->any() in event/show-guest.blade.php, so flashing it reopened the ticket
        // form and hidePanelsBelow() then hid the subscribe panel itself.
        $response->assertSessionMissing('error');
        $response->assertSessionHasNoErrors();

        // The typed address survives, under a key that cannot cross-fill the ticket/RSVP forms.
        $response->assertSessionHas('subscribe_email', 'not-an-email');

        $this->assertSame(0, RoleSubscriber::count());
    }

    public function test_a_rejected_address_returns_200_on_the_json_path(): void
    {
        $this->postJson($this->joinUrl(), ['email' => 'not-an-email'])
            ->assertOk()
            ->assertJson(['success' => false]);
    }

    /** SendQueuedEmail keeps both properties protected. */
    private function queuedMailable($job)
    {
        $p = new \ReflectionProperty($job, 'mailable');
        $p->setAccessible(true);

        return $p->getValue($job);
    }

    private function queuedRecipient($job)
    {
        $p = new \ReflectionProperty($job, 'recipient');
        $p->setAccessible(true);

        return $p->getValue($job);
    }
}
