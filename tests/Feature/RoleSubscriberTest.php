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
 * The point of the feature is that CAPTURE stops costing a user account: RoleController::follow()
 * bounces a signed-out visitor to sign_up, which is why 139k guest page views produced 764
 * followers. Confirming now grants the account instead, so the split matters: at SUBMIT the
 * load-bearing assertion is still "and NO account was created", and at CONFIRM it is that a stub
 * plus a follower pivot appear - but only for a claimed schedule, and only where public
 * registration is open.
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

    /**
     * The minimum a real submission carries.
     *
     * The name is REQUIRED (RoleSubscriberController::store()), so a test that only cares about
     * the address still has to send one. Keeping that in one place means the next required field
     * is one edit rather than thirty; `$overrides + $defaults` lets a caller replace either key,
     * because PHP's array union keeps the LEFT operand's value on a collision.
     */
    private function joinPayload(array $overrides = []): array
    {
        return $overrides + ['email' => 'fan@fans.test', 'name' => 'A Fan'];
    }

    public function test_a_subscription_without_a_name_is_rejected(): void
    {
        // The name is what the owner sees on the followers tab, the newsletter stats and the
        // segment editor. While it was optional those surfaces filled up with rows identified by
        // nothing but an address.
        $response = $this->from($this->role->getGuestUrl())
            ->post($this->joinUrl(), ['email' => 'fan@fans.test']);

        $this->assertSame(0, RoleSubscriber::count());

        // Visible, and visible on the panel's own key - guest layouts render no per-field errors,
        // so a ValidationException would redirect back showing nothing. Same contract as a
        // rejected address, and session('error') must stay clear or event/show-guest.blade.php
        // reopens the ticket modal.
        $response->assertSessionHas('subscribe_error', __('messages.subscribe_name_required'));
        $response->assertSessionHas('subscribe_error_for', $this->role->subdomain);
        $response->assertSessionMissing('error');
        $response->assertSessionHasNoErrors();
    }

    public function test_a_name_of_nothing_but_markup_is_rejected(): void
    {
        // strip_tags runs BEFORE the rule, not at write time. Validating first and stripping after
        // would let "<b></b>" satisfy 'required' and then land as an empty string - a name the
        // form insists on and the row does not have.
        foreach (['<b></b>', '<b> </b>', '   '] as $name) {
            $this->post($this->joinUrl(), $this->joinPayload(['name' => $name]))
                ->assertSessionHas('subscribe_error', __('messages.subscribe_name_required'));
        }

        $this->assertSame(0, RoleSubscriber::count());
    }

    public function test_a_missing_name_is_rejected_on_the_json_path_too(): void
    {
        // The follow-consent modal posts this endpoint as JSON from hand-built payload, where the
        // input's `required` attribute does not exist - so the server is the only gate it has.
        // 200, not 422: the caller throws a generic failure on !response.ok and only renders
        // data.message on a 200.
        $this->postJson($this->joinUrl(), ['email' => 'fan@fans.test', 'source' => 'modal'])
            ->assertOk()
            ->assertJson(['success' => false, 'message' => __('messages.subscribe_name_required')]);

        $this->assertSame(0, RoleSubscriber::count());
    }

    public function test_the_stored_name_is_stripped_then_trimmed(): void
    {
        // The ORDER is the point, and '<b> </b>Fan' is the fixture that shows it: TrimStrings has
        // nothing to trim on the way in, strip_tags then EXPOSES a leading space, and only a trim
        // running after the strip removes it. A plain outer-whitespace fixture proves nothing here
        // - the middleware would have handled that one on its own, so the test would pass with or
        // without this code.
        $this->post($this->joinUrl(), $this->joinPayload(['name' => '<b> </b>Fan']));

        $this->assertSame('Fan', RoleSubscriber::first()->name);
    }

    public function test_both_subscribe_surfaces_require_a_name(): void
    {
        // The label lost "(optional)" and the input gained `required`. Checked on the rendered
        // page rather than on the partial, because the modal and the panel are separate copies of
        // the same two fields and only one of them is a real <form>.
        $html = $this->get($this->role->getGuestUrl())->assertOk()->getContent();

        // Asserted against the label's rendered TEXT, not against the old key: that key is gone, so
        // __() would hand back the key name itself and the check would pass for free.
        preg_match('/<label for="subscribe_name_[^"]*"[^>]*>(.*?)<\\/label>/s', $html, $label);
        $this->assertNotEmpty($label, 'the panel name label did not render');
        $this->assertSame(__('messages.subscribe_your_name'), trim($label[1]));

        foreach (['subscribe_name_', 'follow-consent-name'] as $id) {
            preg_match('/<input[^>]*id="'.preg_quote($id, '/').'[^>]*>/', $html, $m);
            $this->assertNotEmpty($m, "the {$id} input did not render");
            $this->assertStringContainsString('required', $m[0],
                "the {$id} input must be marked required");
        }
    }

    public function test_a_signed_out_visitor_subscribes_without_an_account(): void
    {
        $userCount = User::count();

        $this->post($this->joinUrl(), $this->joinPayload())
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

        $this->post($this->joinUrl(), $this->joinPayload());

        $sub = RoleSubscriber::first();
        $this->assertNull($sub->confirmed_at, 'a fresh subscriber must not be mailable yet');

        Queue::assertPushed(SendQueuedEmail::class, function ($job) {
            return $this->queuedMailable($job) instanceof SubscriptionConfirmation
                && $this->queuedRecipient($job) === 'fan@fans.test';
        });
    }

    public function test_confirming_makes_the_row_mailable(): void
    {
        $this->post($this->joinUrl(), $this->joinPayload());
        $sub = RoleSubscriber::first();

        $this->post(route('subscriber.confirm', ['token' => $sub->confirm_token]))
            ->assertRedirect(route('subscriber.confirmed'));

        $this->assertNotNull($sub->fresh()->confirmed_at);
        $this->assertSame(1, RoleSubscriber::confirmed()->count());
    }

    public function test_email_case_and_whitespace_do_not_create_a_second_row(): void
    {
        $this->post($this->joinUrl(), $this->joinPayload(['email' => 'Fan@Fans.test']));
        $this->post($this->joinUrl(), $this->joinPayload(['email' => '  fan@fans.test  ']));

        $this->assertSame(1, RoleSubscriber::count());
        $this->assertSame('fan@fans.test', RoleSubscriber::first()->email);
    }

    public function test_a_duplicate_submission_is_idempotent_and_says_nothing_different(): void
    {
        $first = $this->post($this->joinUrl(), $this->joinPayload());
        $second = $this->post($this->joinUrl(), $this->joinPayload());

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
        $this->post($this->joinUrl(), $this->joinPayload(['website' => 'http://spam.example']))->assertSessionHas('subscribe_error');

        $this->assertSame(0, RoleSubscriber::count());
    }

    public function test_an_absent_honeypot_is_allowed(): void
    {
        // Every non-browser caller omits the field entirely; a has() check instead of filled()
        // would break all of them at once.
        $this->post($this->joinUrl(), $this->joinPayload());

        $this->assertSame(1, RoleSubscriber::count());
    }

    public function test_the_json_path_returns_200_on_a_tripped_honeypot(): void
    {
        // Not an error status: the modal's caller throws a generic failure on !response.ok and
        // only renders data.message on a 200.
        $this->postJson($this->joinUrl(), $this->joinPayload(['website' => 'http://spam.example']))->assertOk()->assertJson(['success' => false]);

        $this->assertSame(0, RoleSubscriber::count());
    }

    public function test_subscribing_never_clears_an_existing_unsubscribe(): void
    {
        NewsletterUnsubscribe::create([
            'role_id' => $this->role->id,
            'email' => 'fan@fans.test',
            'unsubscribed_at' => now(),
        ]);

        $this->post($this->joinUrl(), $this->joinPayload());

        // Reversing an explicit "no" from an unauthenticated POST is worse than the accepted
        // single-opt-in risk. Only confirming, which proves mailbox possession, may lift it.
        $this->assertSame(1, NewsletterUnsubscribe::where('email', 'fan@fans.test')->count());
    }

    public function test_confirming_does_lift_a_previous_unsubscribe(): void
    {
        $this->post($this->joinUrl(), $this->joinPayload());
        NewsletterUnsubscribe::create([
            'role_id' => $this->role->id,
            'email' => 'fan@fans.test',
            'unsubscribed_at' => now(),
        ]);

        $this->post(route('subscriber.confirm', ['token' => RoleSubscriber::first()->confirm_token]));

        $this->assertSame(0, NewsletterUnsubscribe::where('email', 'fan@fans.test')->count());
    }

    public function test_fetching_the_confirm_link_does_not_confirm(): void
    {
        // The regression this guards: confirm() used to be a GET that mutated. Corporate inbound
        // mail security (Defender Safe Links, Proofpoint, Barracuda) fetches every link in an
        // inbound message before the recipient sees it, so that fetch completed the subscription
        // AND deleted the recipient's newsletter_unsubscribes row on their behalf. Anyone can put
        // any address into the public form, so it made an unauthenticated caller able to erase a
        // stranger's newsletter opt-out by proxy.
        $this->post($this->joinUrl(), $this->joinPayload());
        $sub = RoleSubscriber::first();

        NewsletterUnsubscribe::create([
            'role_id' => $this->role->id,
            'email' => 'fan@fans.test',
            'unsubscribed_at' => now(),
        ]);

        // The page renders, and offers the button.
        $page = $this->get(route('subscriber.show_confirm', ['token' => $sub->confirm_token]));
        $page->assertOk();
        $page->assertSee(__('messages.subscription_confirm_button'));

        // Nothing moved.
        $this->assertNull($sub->fresh()->confirmed_at, 'a GET must not confirm');
        $this->assertNotNull($sub->fresh()->confirm_token, 'a GET must not burn the token');
        $this->assertSame(1, NewsletterUnsubscribe::where('email', 'fan@fans.test')->count(),
            'a GET must not lift a suppression');

        // And the token is still live for the person who actually presses the button.
        $this->post(route('subscriber.confirm', ['token' => $sub->confirm_token]))
            ->assertRedirect(route('subscriber.confirmed'));
        $this->assertNotNull($sub->fresh()->confirmed_at);
        $this->assertSame(0, NewsletterUnsubscribe::where('email', 'fan@fans.test')->count());
    }

    public function test_an_array_email_is_rejected_rather_than_fatal(): void
    {
        // respond() cast the submitted address to string for the repopulate flash, so `email[]=a`
        // raised "Array to string conversion" - which HandleExceptions promotes to an
        // ErrorException, i.e. a 500 on a public endpoint. Same class as ArrayLanguageParamTest,
        // and as the is_valid_language_code() signature change in this release.
        $this->post($this->joinUrl(), $this->joinPayload(['email' => ['a']]))->assertRedirect();
        $this->post($this->joinUrl(), $this->joinPayload(['website' => ['x']]))->assertRedirect();

        $this->assertSame(0, RoleSubscriber::count());
    }

    public function test_an_array_name_is_rejected_rather_than_fatal(): void
    {
        // The name's turn at the same trap. store() normalises the name with strip_tags BEFORE
        // validating - which is what stops "<b></b>" being stored as '' - and that put a cast in
        // front of the 'string' rule, where the rule cannot defend it. `name[]=x` therefore reached
        // `(string) []`, "Array to string conversion", an ErrorException, and a 500 on a public
        // endpoint. Same failure as the address above, and it needs its own test because
        // joinPayload() always supplies a well-typed name.
        $this->post($this->joinUrl(), $this->joinPayload(['name' => ['a']]))->assertRedirect();

        // Not only the body: input() unions query->all(), so a query string reaches the same cast
        // even when the posted body is entirely well-formed.
        $this->post($this->joinUrl().'?name[]=x', ['email' => 'fan@fans.test'])->assertRedirect();

        $this->assertSame(0, RoleSubscriber::count());
    }

    public function test_a_rejected_submission_hands_back_the_typed_name(): void
    {
        // The address already came back; the name did not, and `required` turned that from an
        // annoyance into retyping - the browser will not resubmit until the box is filled again,
        // and each attempt costs one of five per minute.
        $response = $this->from($this->role->getGuestUrl())
            ->post($this->joinUrl(), $this->joinPayload([
                'email' => 'not-an-email',
                'name' => 'María González',
            ]));

        $response->assertSessionHas('subscribe_email', 'not-an-email');
        $response->assertSessionHas('subscribe_name', 'María González');

        // And it actually reaches the input, rather than only the session.
        $this->assertStringContainsString('value="María González"',
            $this->get($this->role->getGuestUrl())->getContent());
    }

    public function test_the_rejected_field_is_the_one_marked_invalid(): void
    {
        // aria-invalid/aria-describedby/autofocus used to hang off the email input on ANY error,
        // which was right while an address was the only thing that could fail. With the name
        // required, the common rejection would otherwise focus the already-correct email box and
        // tell a screen reader that the EMAIL is invalid, described by the NAME's error text.
        $this->post($this->joinUrl(), ['email' => 'fan@fans.test'])
            ->assertSessionHas('subscribe_error_field', 'name');

        $html = $this->get($this->role->getGuestUrl())->getContent();

        preg_match('/<input[^>]*id="subscribe_name_[^>]*>/', $html, $name);
        $this->assertNotEmpty($name, 'the name input did not render');
        $this->assertStringContainsString('aria-invalid', $name[0],
            'the name is what failed, so the name input is what must be flagged');

        preg_match('/<input[^>]*id="subscribe_email_[^>]*>/', $html, $email);
        $this->assertNotEmpty($email, 'the email input did not render');
        $this->assertStringNotContainsString('aria-invalid', $email[0],
            'the address was fine, so nothing may announce it as invalid');
    }

    public function test_a_bad_address_still_marks_the_address(): void
    {
        // The other side of the same switch: the original behaviour has to survive it.
        $this->post($this->joinUrl(), $this->joinPayload(['email' => 'not-an-email']))
            ->assertSessionHas('subscribe_error_field', 'email');

        preg_match('/<input[^>]*id="subscribe_email_[^>]*>/',
            $this->get($this->role->getGuestUrl())->getContent(), $email);
        $this->assertStringContainsString('aria-invalid', $email[0]);
    }

    public function test_the_json_path_normalises_the_name_too(): void
    {
        // Request::merge() writes to the JSON bag on a JSON request, so the modal gets the same
        // strip-then-trim the form does. Worth pinning: every other normalisation test drives the
        // form path, and a merge that silently missed the JSON bag would leave the modal storing
        // raw markup with nothing failing.
        $this->postJson($this->joinUrl(), $this->joinPayload(['name' => '<b> </b>Fan', 'source' => 'modal']))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame('Fan', RoleSubscriber::first()->name);
    }

    public function test_a_failing_mailer_does_not_500_the_public_form(): void
    {
        // Selfhost ships QUEUE_CONNECTION=sync, where dispatch() IS the send: SyncQueue runs the
        // job inline and rethrows, SendQueuedEmail has no catch, and RoleMailerService's
        // non-role-mailer branch sends outside its own try. A dead SMTP host therefore rendered a
        // 500 - with the transport exception on the page wherever APP_DEBUG is on - to an
        // anonymous visitor, for a row that had already been committed.
        Queue::shouldReceive('push')->andThrow(new \RuntimeException('smtp is down'));
        Queue::shouldReceive('connection')->andReturnSelf();

        $this->post($this->joinUrl(), $this->joinPayload())->assertRedirect();

        // The row is still written, and the visitor is told the same thing as always.
        $this->assertSame(1, RoleSubscriber::count());
    }

    public function test_one_click_unsubscribe_writes_the_shared_list(): void
    {
        $this->post($this->joinUrl(), $this->joinPayload());
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
        $this->post($this->joinUrl(), $this->joinPayload());
        $this->post(route('role.audience.join', ['subdomain' => $other->subdomain]), $this->joinPayload());

        $this->post('/sub/u/'.RoleSubscriber::first()->token, ['all' => 1])->assertOk();

        // Without this a fan following six venues needs six links, and presses Report spam instead.
        $this->assertSame(2, NewsletterUnsubscribe::where('email', 'fan@fans.test')->count());
    }

    public function test_a_single_unsubscribe_does_not_touch_another_schedule(): void
    {
        $other = $this->createRole($this->createOwner(), 'talent');
        $this->post($this->joinUrl(), $this->joinPayload());
        $this->post(route('role.audience.join', ['subdomain' => $other->subdomain]), $this->joinPayload());

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
        $this->post($this->joinUrl(), $this->joinPayload(['email' => 'private@fans.test']));

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

    public function test_the_event_page_panel_carries_its_own_card(): void
    {
        // This test used to assert the exact opposite, on a premise that was never true: that the
        // include sits inside the right column's card, so a card of its own would nest one inside
        // an identical one. That container opens above the breadcrumb and closes at the mobile
        // calendar sheet, a thousand lines before the include - so what the panel actually got was
        // a border-t rule with negative margins over the SCHEDULE'S BACKGROUND. On a light
        // background image in dark mode that is near-white text on a photo.
        //
        // Every other block in the column (tickets, description, agenda, media, reviews,
        // x-sponsor-grid) carries the guest-portal card, and so must this one.
        //
        // Local dev cannot show this - every schedule here belongs to the demo user, so the panel
        // is (correctly) suppressed - which is exactly why it is pinned here instead.
        $event = $this->createEvent($this->role);

        $html = $this->get($this->guestEventUrl($this->role, $event))->assertOk()->getContent();

        preg_match('/id="subscribe-panel"[^>]*class="([^"]*)"/', $html, $m);
        $this->assertNotEmpty($m, 'the panel did not render on the event page');

        $this->assertStringContainsString('bg-white/95', $m[1],
            'the panel renders over the schedule background and needs an opaque surface of its own');
        $this->assertStringContainsString('backdrop-blur-sm', $m[1],
            'the panel must use the same card treatment as every sibling in the column');
        $this->assertStringContainsString('p-6', $m[1],
            'a card without padding puts the form flush against its own edge');
    }

    public function test_the_schedule_page_panel_keeps_its_own_card_and_padding(): void
    {
        // The other side of the same change: padding moved INSIDE $panelClass, so a caller that
        // passes one now owns it. If the schedule page's class string ever loses the padding
        // again, the form renders flush against the card edge.
        //
        // Asserted as the two axes separately rather than as 'p-6', because the horizontal half is
        // px-6 lg:px-16 now (see the test below) and neither px-6 nor py-6 contains 'p-6'.
        $html = $this->get($this->role->getGuestUrl())->assertOk()->getContent();

        preg_match('/id="subscribe-panel"[^>]*class="([^"]*)"/', $html, $m);
        $this->assertNotEmpty($m, 'the panel did not render on the schedule page');

        $this->assertStringContainsString('rounded-2xl', $m[1]);
        $this->assertStringContainsString('px-6', $m[1]);
        $this->assertStringContainsString('py-6', $m[1]);
    }

    public function test_the_subscribe_panel_indents_its_content_like_the_calendar_panel(): void
    {
        // Card EDGES already line up - that is the width test below. This is the inner inset, and
        // it is a different thing that can regress on its own: every sibling card in the column
        // (the banner header's inner <header>, the carousel, the calendar wrapper, the video grid)
        // steps to lg:px-16, so a panel that stopped at its own p-8 put "Stay up to date" 32px to
        // the left of "September 2026" on every screen at lg or wider.
        //
        // Asserted as an equality against the calendar's own lg step rather than against the
        // literal 'lg:px-16', for the same reason the width test compares against the calendar:
        // the requirement is "the same inset as the calendar", not any particular number.
        //
        // Only the lg step is compared. Below it the calendar is deliberately px-0 md:px-6 - it
        // runs edge to edge once .calendar-panel-border drops its background under 768px - and in
        // LIST view #calendar-panel-wrapper is zeroed outright by the CSS in show-guest.blade.php
        // (padding: 0 !important, transparent background), because a list has no card of its own.
        // That override is CSS-only, so the rendered class attribute still carries lg:px-16 in
        // both views and this assertion holds; in list view the panel's inset is matching the
        // header, carousel and video grid, which keep px-6 lg:px-16 in both views.
        $html = $this->get($this->role->getGuestUrl())->assertOk()->getContent();

        // Grab the whole opening tag first, then read class out of it: [^>]* cannot cross a '>',
        // so the tag match is anchored to one element, and pulling class separately means neither
        // side depends on the attribute ORDER. The calendar wrapper writes class before id and the
        // panel writes it after, which is exactly the trap a single combined pattern falls into.
        preg_match('/<[^>]*id="calendar-panel-wrapper"[^>]*>/', $html, $calendarTag);
        $this->assertNotEmpty($calendarTag, 'the calendar wrapper did not render');
        preg_match('/class="([^"]*)"/', $calendarTag[0], $calendar);
        $this->assertNotEmpty($calendar, 'the calendar wrapper rendered no class attribute');

        preg_match('/<[^>]*id="subscribe-panel"[^>]*>/', $html, $panelTag);
        $this->assertNotEmpty($panelTag, 'the panel did not render on the schedule page');
        preg_match('/class="([^"]*)"/', $panelTag[0], $panel);
        $this->assertNotEmpty($panel, 'the panel rendered no class attribute');

        preg_match('/(?:^|\s)(lg:px-\S+)/', $calendar[1], $calendarStep);
        $this->assertNotEmpty($calendarStep, 'the calendar wrapper lost its lg inset');

        preg_match('/(?:^|\s)(lg:px-\S+)/', $panel[1], $panelStep);
        $this->assertNotEmpty($panelStep, 'the subscribe panel lost its lg inset');

        $this->assertSame($calendarStep[1], $panelStep[1],
            'the subscribe panel must indent its content as far as the calendar panel does');
    }

    public function test_the_subscribe_panel_matches_the_calendar_panel_width(): void
    {
        // The panel sits directly under the calendar, both children of the same container, so a
        // width the calendar does not share reads as a misaligned card: the calendar swings to the
        // full container in calendar view while this stayed hard-capped at 56rem.
        //
        // Asserted as an EQUALITY against the calendar's own rendered max-width rather than against
        // the literal '200rem', because the requirement is "the same width as the calendar", not
        // any particular number.
        $this->role->update(['event_layout' => 'calendar']);

        $html = $this->get($this->role->getGuestUrl())->assertOk()->getContent();

        // [^>]* keeps each match inside its own opening tag.
        preg_match('/id="calendar-panel-wrapper"[^>]*style="max-width:\s*([^"]+)"/', $html, $calendar);
        $this->assertNotEmpty($calendar, 'the calendar wrapper did not render');

        // The wrapper is the element that opens immediately before the panel, so look back through
        // a window for it - the same shape AudienceTemplateInjectionTest uses. Safe against false
        // positives: transition-[max-width] in the class list cannot match style="max-width:, and
        // the calendar wrapper is thousands of characters earlier in the output.
        $panelPos = strpos($html, 'id="subscribe-panel"');
        $this->assertNotFalse($panelPos, 'the panel did not render on the schedule page');
        $wrapper = substr($html, max(0, $panelPos - 400), 400);

        $this->assertStringContainsString('data-view-width', $wrapper,
            'without it the wrapper cannot follow the calendar/list toggle');

        preg_match('/style="max-width:\s*([^"]+)"/', $wrapper, $subscribe);
        $this->assertNotEmpty($subscribe, 'the wrapper rendered no max-width');
        $this->assertSame(trim($calendar[1]), trim($subscribe[1]),
            'in calendar view the subscribe panel must be as wide as the calendar panel');
    }

    public function test_the_subscribe_form_stays_readable_when_the_card_is_wide(): void
    {
        // The other half of that change: the wrapper only gets to track the calendar because the
        // readable cap moved onto the form. Both inputs are flex-1 min-w-0, so on a ~1496px card
        // they would otherwise stretch to roughly 650px each.
        $html = view('partials.subscribe-panel', ['role' => $this->role])->render();

        preg_match('/<form[^>]*class="([^"]*)"/', $html, $m);
        $this->assertNotEmpty($m, 'the form did not render');
        $this->assertStringContainsString('max-w-4xl', $m[1],
            'the form row carries the readable cap now that the wrapper no longer does');
    }

    public function test_the_panel_offers_no_second_path_to_an_account(): void
    {
        // The panel used to end with "Prefer an account? Sign up and follow instead", linking to
        // role.follow. It was a choice between a thing and the same thing: linkAccount() mints an
        // account on confirm anyway.
        //
        // Asserted against the whole page rather than a slice of it, and specifically against THIS
        // schedule's follow URL. The event page can carry Follow triggers, but only inside the loop
        // over the event's claimed performers, so they are other subdomains; the page's own
        // schedule had exactly one thing pointing at role.follow, and it was the panel. The
        // join-route assertion is what stops this passing because the panel failed to render.
        $event = $this->createEvent($this->role);

        $html = $this->get($this->guestEventUrl($this->role, $event))->assertOk()->getContent();

        $this->assertStringContainsString(
            route('role.audience.join', ['subdomain' => $this->role->subdomain]), $html,
            'the panel did not render, so the assertion below would prove nothing');
        $this->assertStringNotContainsString(
            route('role.follow', ['subdomain' => $this->role->subdomain]), $html,
            'the panel must not offer a separate sign-up path');
    }

    public function test_the_panel_says_an_account_is_created(): void
    {
        // The other half: having stopped offering the account, the panel has to say it makes one,
        // or the password form on /sub/done is the first anyone hears of it.
        //
        // Rendered as a partial, not asserted against the page, and this is not fussiness: the
        // follow modal carries the SAME sentence for its own guest form, and its Blade renders on
        // every guest page whether or not the modal is ever opened. An assertSee() on the page was
        // green with this paragraph deleted from the panel.
        $html = view('partials.subscribe-panel', ['role' => $this->role])->render();

        $this->assertStringContainsString(__('messages.subscribe_account_note'), $html);
    }

    public function test_the_panel_promises_no_account_where_none_is_created(): void
    {
        // The note is gated on linkAccount()'s own two conditions, because a promise that outlives
        // the behaviour it describes is worse than silence.
        //
        // Rendered as a partial rather than through a page, because neither gate can be turned off
        // on a real request: RoleController::viewGuest() redirects an unclaimed schedule away
        // before any of this renders, and closing registration means app.hosted = false, which
        // routes/web.php reads at BOOT to decide which half of the route table to register. The
        // partial is the whole of the behaviour under test either way.
        config(['app.hosted' => false, 'app.allow_registration' => false]);
        $this->assertFalse(public_registration_enabled());

        $html = view('partials.subscribe-panel', ['role' => $this->role])->render();

        $this->assertStringContainsString(
            route('role.audience.join', ['subdomain' => $this->role->subdomain]), $html,
            'the panel itself must still render - only the account sentence is conditional');
        $this->assertStringNotContainsString(__('messages.subscribe_account_note'), $html);
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

    // ---------------------------------------------------------------------------------------
    // Confirming now creates an account and makes it follow the schedule.
    // ---------------------------------------------------------------------------------------

    /** Drive the real endpoint, so these pin the path a person actually takes. */
    private function subscribeAndConfirm(string $email, ?Role $role = null, ?string $name = null): void
    {
        $role = $role ?: $this->role;

        // Defaulted, not passed through as null: the endpoint requires a name, and every caller
        // here that leaves it out is testing the CONFIRM half rather than the form.
        $this->post(route('role.audience.join', ['subdomain' => $role->subdomain]), [
            'email' => $email,
            'name' => $name ?: 'A Fan',
        ]);

        $sub = RoleSubscriber::where('role_id', $role->id)->where('email', $email)->firstOrFail();

        $this->post(route('subscriber.confirm', ['token' => $sub->confirm_token]));
    }

    private function followerPivots(Role $role, ?User $user = null): int
    {
        $query = \DB::table('role_user')->where('role_id', $role->id)->where('level', 'follower');

        if ($user) {
            $query->where('user_id', $user->id);
        }

        return $query->count();
    }

    public function test_confirming_creates_a_stub_account_that_follows_the_schedule(): void
    {
        $this->subscribeAndConfirm('fan@fans.test', null, 'A Fan');

        $user = User::where('email', 'fan@fans.test')->first();
        $this->assertNotNull($user, 'confirming should mint an account');
        $this->assertNull($user->password, 'it is a stub - nobody chose a password');
        $this->assertNull($user->email_verified_at,
            'unverified keeps them out of every cohort counter, which all require a verified account');
        $this->assertSame('subscriber', $user->signup_intent,
            'signup_intent keeps them out of the organizer funnel');
        $this->assertTrue($user->isStub());
        $this->assertSame(1, $this->followerPivots($this->role, $user));
    }

    public function test_submitting_alone_still_creates_no_account(): void
    {
        // The other half of test_a_signed_out_visitor_subscribes_with_only_an_email: the account is
        // minted on CONFIRM, never on submit. resolveFollowers() applies no confirmation filter, so
        // a pivot written here would put any address a stranger typed into the owner's next
        // newsletter with no opt-in at all.
        $before = User::count();

        $this->post($this->joinUrl(), $this->joinPayload());

        $this->assertSame($before, User::count());
        $this->assertSame(0, $this->followerPivots($this->role));
    }

    public function test_confirming_leaves_an_existing_account_untouched(): void
    {
        $existing = $this->createOwner();
        $existing->forceFill(['name' => 'Real Name', 'is_subscribed' => true])->save();
        $before = $existing->only(['name', 'password', 'email_verified_at', 'is_subscribed', 'signup_intent']);

        $this->subscribeAndConfirm($existing->email, null, 'Typed Something Else');

        $after = $existing->fresh();
        $this->assertSame($before['name'], $after->name);
        $this->assertSame($before['password'], $after->password);
        $this->assertEquals($before['email_verified_at'], $after->email_verified_at);
        $this->assertEquals($before['is_subscribed'], $after->is_subscribed);
        $this->assertSame($before['signup_intent'], $after->signup_intent);
        $this->assertSame(1, $this->followerPivots($this->role, $after));
    }

    public function test_confirming_does_not_touch_an_owners_own_pivot(): void
    {
        // isConnected() is any role_user row at any level, so the owner keeps level 'owner' rather
        // than being demoted to follower or gaining a second row.
        $owner = $this->role->owner();

        $this->subscribeAndConfirm($owner->email);

        $this->assertSame(0, $this->followerPivots($this->role, $owner));
        $this->assertSame(1, \DB::table('role_user')
            ->where('role_id', $this->role->id)->where('user_id', $owner->id)->count());
    }

    public function test_one_address_on_two_schedules_reuses_one_account(): void
    {
        $other = $this->createRole($this->createOwner());

        $this->subscribeAndConfirm('fan@fans.test');
        $this->subscribeAndConfirm('fan@fans.test', $other);

        $this->assertSame(1, User::where('email', 'fan@fans.test')->count());
        $user = User::where('email', 'fan@fans.test')->first();
        $this->assertSame(1, $this->followerPivots($this->role, $user));
        $this->assertSame(1, $this->followerPivots($other, $user));
    }

    public function test_confirming_on_an_unclaimed_schedule_creates_nothing(): void
    {
        // The security gate, not tidiness. Role::isEditableBy() ends with
        // "! isClaimed() && $user->isFollowing(...)", so following an unclaimed schedule grants
        // EDIT rights on it - and this endpoint is a public form. Without the guard, "type any
        // address and click the emailed link" would be an edit-access path.
        $unclaimed = $this->createRole($this->createOwner(), 'venue', [
            'email_verified_at' => null,
            'phone_verified_at' => null,
        ]);
        $unclaimed->forceFill(['user_id' => null])->save();
        $this->assertFalse($unclaimed->fresh()->isClaimed());

        $this->subscribeAndConfirm('fan@fans.test', $unclaimed->fresh());

        $this->assertNotNull(RoleSubscriber::where('role_id', $unclaimed->id)->first(),
            'the audience row is still kept for whoever claims the schedule');
        $this->assertNull(User::where('email', 'fan@fans.test')->first());
        $this->assertSame(0, $this->followerPivots($unclaimed));
    }

    public function test_nothing_is_created_when_public_registration_is_closed(): void
    {
        // Every other account-creating path in the app honours this gate. Without it a selfhost
        // install with ALLOW_REGISTRATION unset accrues an unclaimable users row per subscriber.
        config(['app.hosted' => false, 'app.allow_registration' => false]);
        $this->assertFalse(public_registration_enabled());

        $this->subscribeAndConfirm('fan@fans.test');

        $this->assertNull(User::where('email', 'fan@fans.test')->first());
        $this->assertSame(0, $this->followerPivots($this->role));
    }

    // ---------------------------------------------------------------------------------------
    // Turning the stub into a real account.
    // ---------------------------------------------------------------------------------------

    public function test_the_confirmed_page_offers_a_password_and_survives_a_refresh(): void
    {
        $this->subscribeAndConfirm('fan@fans.test');

        // Twice: the page reads the session, not a token, so a refresh must not lose the form the
        // way replaying the burned confirm POST used to.
        foreach ([1, 2] as $ignored) {
            $this->get(route('subscriber.confirmed'))
                ->assertOk()
                ->assertSee(__('messages.subscription_account_heading'))
                ->assertSee(route('subscriber.claim_account'), false)
                // The conditional <x-slot name="head"> is the only one in the repo; if it silently
                // stopped emitting, every claimed account would quietly get America/New_York.
                ->assertSee('claim_timezone', false);
        }
    }

    public function test_the_confirmed_page_offers_no_password_to_an_existing_account(): void
    {
        $existing = $this->createOwner();

        $this->subscribeAndConfirm($existing->email);

        $this->get(route('subscriber.confirmed'))
            ->assertOk()
            ->assertDontSee(__('messages.subscription_account_heading'))
            ->assertSee(__('messages.subscription_account_existing_heading'));
    }

    public function test_the_confirmed_page_needs_the_session(): void
    {
        $this->get(route('subscriber.confirmed'))->assertRedirect('/');
    }

    public function test_claiming_sets_a_password_verifies_the_email_and_signs_in(): void
    {
        $this->subscribeAndConfirm('fan@fans.test');

        $this->post(route('subscriber.claim_account'), ['password' => 'sup3rsecret'])
            ->assertRedirect();

        $user = User::where('email', 'fan@fans.test')->first();
        $this->assertNotNull($user->password);
        $this->assertFalse($user->isStub());
        // /following sits behind the `verified` middleware, so this is load-bearing, not cosmetic.
        $this->assertNotNull($user->email_verified_at);
        $this->assertAuthenticatedAs($user);
    }

    public function test_claiming_refuses_a_reset_token_that_did_not_come_from_this_session(): void
    {
        // Without the session check this endpoint is a general stub takeover AND an email
        // verification bypass: PasswordResetLinkController has no stub filter, so a token can be
        // minted for any stub - including a team invite sitting at admin level - and posted here to
        // get email_verified_at stamped and be signed in. NewPasswordController deliberately
        // refuses to do either off a reset token.
        $victim = User::factory()->create(['password' => null, 'email_verified_at' => null]);
        $token = \Illuminate\Support\Facades\Password::createToken($victim);

        $this->post(route('subscriber.claim_account'), [
            'email' => $victim->email,
            'token' => $token,
            'password' => 'sup3rsecret',
        ])->assertRedirect(route('subscriber.confirmed'));

        $victim->refresh();
        $this->assertNull($victim->password);
        $this->assertNull($victim->email_verified_at);
        $this->assertGuest();
    }

    public function test_claiming_is_one_shot(): void
    {
        $this->subscribeAndConfirm('fan@fans.test');

        $this->post(route('subscriber.claim_account'), ['password' => 'sup3rsecret'])->assertRedirect();
        $this->post(route('subscriber.claim_account'), ['password' => 'anotherone1'])
            ->assertRedirect(route('subscriber.confirmed'));
    }

    // ---------------------------------------------------------------------------------------
    // Keeping the two records in step.
    // ---------------------------------------------------------------------------------------

    public function test_removing_a_subscriber_also_removes_the_follower_pivot(): void
    {
        // Deleting the row stopped being the whole job: resolveFollowers() has no confirmation
        // filter and there is no suppression row here, so a surviving pivot meant the owner pressed
        // Delete and carried on mailing them.
        $this->subscribeAndConfirm('fan@fans.test');
        $user = User::where('email', 'fan@fans.test')->firstOrFail();
        $this->assertSame(1, $this->followerPivots($this->role, $user));

        $sub = RoleSubscriber::where('role_id', $this->role->id)->firstOrFail();

        $this->actingAs($this->role->owner())
            ->delete(route('role.subscribers.remove', [
                'subdomain' => $this->role->subdomain,
                'hash' => \App\Utils\UrlUtils::encodeId($sub->id),
            ]))->assertRedirect();

        $this->assertSame(0, RoleSubscriber::where('role_id', $this->role->id)->count());
        $this->assertSame(0, $this->followerPivots($this->role, $user));
    }

    public function test_a_stranger_cannot_get_a_real_follow_deleted(): void
    {
        // Anybody can type a known follower's address into the public panel. That writes an
        // UNCONFIRMED row - confirm(), and therefore linkAccount(), never run - so the pivot on
        // the table is the one the person made themselves by pressing Follow. An owner tidying
        // that pending row away must not destroy it.
        $follower = $this->createOwner();
        $this->followRole($follower, $this->role);

        $this->post($this->joinUrl(), $this->joinPayload(['email' => $follower->email]));

        $sub = RoleSubscriber::where('role_id', $this->role->id)->firstOrFail();
        $this->assertNull($sub->confirmed_at);

        $this->actingAs($this->role->owner())->delete(route('role.subscribers.remove', [
            'subdomain' => $this->role->subdomain,
            'hash' => \App\Utils\UrlUtils::encodeId($sub->id),
        ]));

        $this->assertSame(1, $this->followerPivots($this->role, $follower),
            'a pending row a stranger created must never delete a real follow');
    }

    public function test_removing_a_subscriber_keeps_a_follow_that_predates_it(): void
    {
        // The other order: pressed Follow first, subscribed and confirmed later. linkAccount()
        // skips attach() because isConnected() is already true, so the only pivot is theirs.
        $follower = $this->createOwner();
        $this->followRole($follower, $this->role);
        \DB::table('role_user')->where('user_id', $follower->id)
            ->update(['created_at' => now()->subYear()]);

        $this->subscribeAndConfirm($follower->email);

        $sub = RoleSubscriber::where('role_id', $this->role->id)->firstOrFail();
        $this->actingAs($this->role->owner())->delete(route('role.subscribers.remove', [
            'subdomain' => $this->role->subdomain,
            'hash' => \App\Utils\UrlUtils::encodeId($sub->id),
        ]));

        $this->assertSame(1, $this->followerPivots($this->role, $follower));
    }

    public function test_a_follow_that_predates_a_subscription_stays_an_account_follower(): void
    {
        // Narrowing all_followers has the same mechanism as widening it, which
        // NewsletterSegment::resolveSubscribers() forbids in writing: a newsletter already sitting
        // in status='scheduled' resolves at SEND time. Somebody who pressed Follow in 2025 and
        // confirmed in 2026 is an account follower by any definition and must stay in that segment.
        $follower = $this->createOwner();
        $this->followRole($follower, $this->role);
        \DB::table('role_user')->where('user_id', $follower->id)
            ->update(['created_at' => now()->subYear()]);

        $this->subscribeAndConfirm($follower->email);

        $this->assertSame(1, $this->role->fresh()->accountOnlyFollowers()->count());
    }

    public function test_removing_a_subscriber_never_touches_an_owner_row(): void
    {
        // followers()->detach() would: that relation constrains the RELATED query, not the pivot.
        $owner = $this->role->owner();
        $this->subscribeAndConfirm($owner->email);

        $sub = RoleSubscriber::where('role_id', $this->role->id)->firstOrFail();

        $this->actingAs($owner)->delete(route('role.subscribers.remove', [
            'subdomain' => $this->role->subdomain,
            'hash' => \App\Utils\UrlUtils::encodeId($sub->id),
        ]));

        $this->assertSame(1, \DB::table('role_user')
            ->where('role_id', $this->role->id)->where('user_id', $owner->id)
            ->where('level', 'owner')->count());
    }

    public function test_unsubscribing_everywhere_suppresses_without_deleting_the_account(): void
    {
        // Deliberately NOT a delete. User has no SoftDeletes and the schema has 20 cascading
        // user_id foreign keys, so deleting here would be a hard delete triggered from an
        // unauthenticated link in an email.
        $this->subscribeAndConfirm('fan@fans.test');
        $sub = RoleSubscriber::where('role_id', $this->role->id)->firstOrFail();

        $this->post('/sub/u/'.$sub->token, ['all' => 1])->assertOk();

        $this->assertNotNull(User::where('email', 'fan@fans.test')->first());
        $this->assertSame(1, NewsletterUnsubscribe::where('email', 'fan@fans.test')->count());
    }

    public function test_unsubscribing_everywhere_leaves_the_platform_flag_alone(): void
    {
        // is_subscribed is not a bigger version of the suppression list. Setting it would reach
        // User::sendEmailVerificationNotification(), which refuses to send while it is false, and
        // nothing in the app ever sets it back to true for an existing user.
        $existing = $this->createOwner();
        $this->subscribeAndConfirm($existing->email);
        $sub = RoleSubscriber::where('role_id', $this->role->id)->firstOrFail();

        $this->post('/sub/u/'.$sub->token, ['all' => 1])->assertOk();

        $kept = User::find($existing->id);
        $this->assertNotNull($kept, 'a real account is never deleted by an unsubscribe link');
        $this->assertTrue((bool) $kept->is_subscribed);
    }

    public function test_a_later_subscription_to_another_schedule_still_works(): void
    {
        // The regression this guards: a platform-wide opt-out written here would be cleared by
        // nothing - confirm() only lifts the PER-SCHEDULE suppression - so a deliberate later
        // subscription would confirm, say "you are on the list", and deliver nothing for ever.
        $this->subscribeAndConfirm('fan@fans.test');
        $sub = RoleSubscriber::where('role_id', $this->role->id)->firstOrFail();
        $this->post('/sub/u/'.$sub->token, ['all' => 1])->assertOk();

        $other = $this->createRole($this->createOwner());
        $this->subscribeAndConfirm('fan@fans.test', $other);

        $recipients = app(\App\Services\AudienceResolver::class)
            ->announcementRecipients($other->fresh())
            ->pluck('email');

        $this->assertContains('fan@fans.test', $recipients->all(),
            'the new subscription must actually be mailable');
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
            // The strings added with the account flow. Without them here, the "always translate"
            // rule is unenforced for exactly the newest copy, which is where it fails.
            'subscribe_done_heading',
            'subscribe_done_body',
            'subscribe_account_note',
            'subscription_account_heading',
            'subscription_account_button',
            'subscription_account_skip_note',
            'subscription_account_existing_heading',
            // Sentences only. A one-word key like subscriber_has_account is legitimately identical
            // in several languages ("Account" in Italian and Dutch), so it would fail this rule
            // while being correctly translated - and it proves nothing about whether a whole file
            // was copied from English, which is what this test is for.
            'subscription_account_body',
            'subscribers_help',
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

            // Retired with the "Prefer an account? Sign up and follow instead" link and the
            // matching button in the follow modal. An array-key check on the loaded file, not a
            // grep of the source, so a comment mentioning the key cannot satisfy it - and leaving
            // one behind in some files is what check_translations.php reports as drift.
            foreach (['subscribe_signup_instead', 'follow_consent_signup_button', 'follow_consent_body_guest'] as $retired) {
                $this->assertArrayNotHasKey($retired, $messages,
                    "{$lang} still defines the retired key {$retired}");
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
        //
        // Two details this test was originally missing, either of which made it green with no
        // trigger on the page at all. accept_requests off, because with it on $hasSubmitButton is
        // true and the trigger then requires auth()->user(), so a signed-out visitor gets none.
        // And the ATTRIBUTE, not the bare URL: the subscribe panel prints that same URL in its
        // form action, so the bare string was satisfied by the thing this is not about.
        $role = $this->createRole($this->createOwner(), 'venue', ['accept_requests' => false]);

        $this->get($role->getGuestUrl())
            ->assertOk()
            ->assertSee('data-subscribe-url="'.route('role.audience.join', ['subdomain' => $role->subdomain]).'"', false);
    }

    public function test_the_follow_trigger_tells_the_modal_whether_an_account_follows(): void
    {
        // One modal serves every Follow trigger on the page, and on an event page each performer is
        // a different schedule, so the modal cannot evaluate linkAccount()'s gates itself - the
        // trigger carries the answer. This is the only automated guard on the modal half: it is a
        // Vue app inlined in Blade, and tools/check-vue-bindings.mjs (what npm run build runs) is
        // pointed at resources/js/components/*.vue, so it never sees that file.
        //
        // accept_requests off on purpose. With it on, $hasSubmitButton is true and the trigger's
        // own condition then requires auth()->user(), so a signed-out visitor - the only kind that
        // sees any of this - gets no trigger at all and the assertion would pass on nothing.
        $role = $this->createRole($this->createOwner(), 'venue', ['accept_requests' => false]);

        $this->get($role->getGuestUrl())
            ->assertOk()
            ->assertSee('data-account-note="1"', false);
    }

    public function test_the_confirm_page_says_an_account_is_coming(): void
    {
        // The button on this page POSTs straight into confirm() -> linkAccount(), so it is the last
        // surface before the account exists - later than the email, and unlike the email it renders
        // at click time, so it is the one that can still be right if the schedule was claimed after
        // the mail went out.
        $this->post($this->joinUrl(), $this->joinPayload());
        $token = RoleSubscriber::where('email', 'fan@fans.test')->first()->confirm_token;

        $this->get('/sub/c/'.$token)
            ->assertOk()
            ->assertSee(__('messages.subscription_confirm_button'), false)
            ->assertSee(__('messages.subscribe_account_note'), false);
    }

    public function test_a_demo_schedule_promises_no_account(): void
    {
        // store() abort(404)s a demo schedule, so nothing here can produce an account. The Follow
        // trigger renders anyway - it gates on is_demo_mode(), which is about the signed-in demo
        // USER, not the demo schedule - so without the demo clause in
        // Role::willCreateAccountOnConfirm() the modal offers a signed-out visitor an account on
        // top of a subscribe form that 404s. Confirmed against the live demo schedule before this
        // was written.
        $demoOwner = $this->createOwner();
        $demoOwner->forceFill(['email' => \App\Services\DemoService::DEMO_EMAIL])->save();
        $demo = $this->createRole($demoOwner, 'venue', ['accept_requests' => false]);
        $this->assertTrue(is_demo_role($demo->fresh()));
        $this->assertFalse($demo->fresh()->willCreateAccountOnConfirm());

        $html = $this->get($demo->fresh()->getGuestUrl())->assertOk()->getContent();

        $this->assertStringContainsString('data-follow-trigger', $html,
            'the trigger still renders on a demo schedule - if that ever changes, this test is moot');
        $this->assertStringNotContainsString('data-account-note="1"', $html);
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

    public function test_the_checkout_opt_in_stores_null_rather_than_a_blank_name(): void
    {
        // captureAudienceOptInFor() stripped tags without trimming, so "<b> </b>" landed as "   ".
        // A whitespace-only string is TRUTHY, so it walked straight past the
        // `@if ($subscriber->name)` fallback on show-admin-followers.blade.php and drew an empty
        // cell where "No name" belongs. Null is the honest value, and it is the one the fallback
        // can see. Not rejected here the way the subscribe panel rejects it - a checkout must not
        // fail over the audience checkbox.
        $event = $this->createEvent($this->role, ['rsvp_enabled' => true]);

        $this->post(route('event.rsvp', ['subdomain' => $this->role->subdomain]), [
            'name' => '<b> </b>',
            'email' => 'fan@fans.test',
            'event_id' => \App\Utils\UrlUtils::encodeId($event->id),
            'event_date' => $event->getStartDateTime()->format('Y-m-d'),
            'audience_opt_in' => '1',
        ]);

        $sub = RoleSubscriber::where('email', 'fan@fans.test')->first();
        $this->assertNotNull($sub, 'the opt-in must still capture the buyer');
        $this->assertNull($sub->name, 'a name with nothing in it must be null, not whitespace');
    }

    public function test_the_checkout_opt_in_keeps_a_real_name(): void
    {
        // The other half: normalising must not eat a name that has one.
        $event = $this->createEvent($this->role, ['rsvp_enabled' => true]);

        $this->post(route('event.rsvp', ['subdomain' => $this->role->subdomain]), [
            'name' => '<b> </b>Fan',
            'email' => 'fan@fans.test',
            'event_id' => \App\Utils\UrlUtils::encodeId($event->id),
            'event_date' => $event->getStartDateTime()->format('Y-m-d'),
            'audience_opt_in' => '1',
        ]);

        $this->assertSame('Fan', RoleSubscriber::where('email', 'fan@fans.test')->first()->name);
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
        // confirmation email silently re-subscribed. "Reopening" included a corporate mail gateway
        // prefetching links, which is why confirming is now a POST - see the GET test below.
        $this->post($this->joinUrl(), $this->joinPayload());
        $sub = RoleSubscriber::first();
        $liveConfirmUrl = route('subscriber.confirm', ['token' => $sub->confirm_token]);

        $this->post($liveConfirmUrl)->assertRedirect(route('subscriber.confirmed'));
        $this->post('/sub/u/'.$sub->token)->assertOk();

        // Replay the link that is still sitting in their inbox. 410, not 404: the link WAS
        // valid, and the dead end it used to produce was indistinguishable from a broken site.
        // What must not change is that the replay changes nothing.
        $replay = $this->get($liveConfirmUrl);
        $replay->assertStatus(410);
        $this->post($liveConfirmUrl)->assertStatus(410);
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
        $this->post($this->joinUrl(), $this->joinPayload());
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
        $this->post($this->joinUrl(), $this->joinPayload());
        $sub = RoleSubscriber::first();
        $this->post(route('subscriber.confirm', ['token' => $sub->confirm_token]));
        $this->post('/sub/u/'.$sub->token);

        // Fill the form in again: a fresh confirmation goes out.
        $this->post($this->joinUrl(), $this->joinPayload());
        $fresh = $sub->fresh();
        $this->assertNotNull($fresh->confirm_token, 'a suppressed address must get a new confirmation');

        $this->post(route('subscriber.confirm', ['token' => $fresh->confirm_token]))
            ->assertRedirect(route('subscriber.confirmed'));
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
            ->post($this->joinUrl(), $this->joinPayload(['email' => 'not-an-email']));

        // The rejection has to be VISIBLE. It no longer toasts - the panel renders it inline and
        // respond() redirects to #subscribe-panel, so a toast at the top of the viewport would be a
        // second notification for something already on screen - which is why the scoping key below
        // matters: without it the panel cannot tell whose error it is.
        $response->assertSessionHas('subscribe_error');
        $response->assertSessionHas('subscribe_error_for', $this->role->subdomain);

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
        $this->postJson($this->joinUrl(), $this->joinPayload(['email' => 'not-an-email']))
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
