<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The guest-submit page forces a stranger to pick a password, and used to say nothing about why.
 *
 * Both pieces of copy that answer it have a history of silently disappearing: the value strip was
 * rendered on guest-import.blade.php and was dropped when this page was split out of it (0820e2550),
 * leaving messages.guest_submit_value_strip translated in all 12 languages and referenced by nothing
 * for two months. Nothing failed. This renders the page and asserts the strings actually arrive.
 *
 * The assertions deliberately use hardcoded English literals rather than __(). A missing key makes
 * __() return the key name, the view renders that same key name, and an assertion written with __()
 * passes on completely unwired copy.
 */
class GuestSubmitAccountCopyTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** The only shape that renders this page: request-accepting, require-account, no booking form. */
    private function curator()
    {
        return $this->createCurator($this->createOwner(), [
            'accept_requests' => true,
            'require_account' => true,
        ]);
    }

    public function test_it_explains_the_account_and_links_the_marketing_page(): void
    {
        $curator = $this->curator();

        $response = $this->get(route('event.guest_submit', ['subdomain' => $curator->subdomain]));
        $response->assertOk();

        // assertSee escapes the needle, so the apostrophe in "you'll" matches the &#039; Blade emits.
        // assertStringContainsString against raw content would silently fail on it.
        $response->assertSee('At least 8 characters. This schedule requires an account');
        $response->assertSee("you'll log in with this password to edit your event later.");

        // The value strip, including its <strong> - proof it escaped neither Blade's escaping nor
        // the Vue template compiler.
        $response->assertSee('Free to submit', false);
        $response->assertSee('<strong>edit and track</strong>', false);

        // Host-agnostic: marketing_url() returns url($path) under APP_TESTING, and APP_URL differs
        // between this checkout (eventschedule.test) and CI (empty, so localhost).
        $this->assertStringContainsString('/why-create-account', $response->getContent());
    }

    /**
     * The strip promises "you'll get your own page", which is false for someone who already has one,
     * so it is @guest-gated. (your_details_help further down the page had the same problem and is
     * now gated the same way, on !isAuthed && registrationEnabled - see the closed-registration
     * test below, which asserts that gate.)
     */
    public function test_the_value_strip_is_hidden_from_signed_in_visitors(): void
    {
        $curator = $this->curator();

        $response = $this->actingAs($this->createOwner())
            ->get(route('event.guest_submit', ['subdomain' => $curator->subdomain]));

        $response->assertOk();
        $response->assertDontSee('Free to submit', false);
    }

    /** MarketingController::whyCreateAccount() honours ?lang, so the guest's language rides along. */
    public function test_a_valid_lang_is_forwarded_to_the_marketing_page(): void
    {
        $curator = $this->curator();

        $this->get(route('event.guest_submit', ['subdomain' => $curator->subdomain]).'?lang=es')
            ->assertOk()
            ->assertSee('why-create-account?lang=es', false);
    }

    /**
     * ?lang[]=en hands is_valid_language_code() an array.
     *
     * Be honest about the coverage: THIS page is defended twice over - the widened helper, and an
     * is_string() narrowing at four sites in its render path (showGuestSubmit,
     * guest-submit.blade.php, layouts/app-guest.blade.php, SetUserLanguage). Remove either one
     * alone and this still passes, so it pins the outcome rather than either mechanism. What pins
     * the helper widening is ArrayLanguageParamTest, whose cases hit un-narrowed call sites.
     */
    public function test_an_array_lang_query_param_does_not_break_the_page(): void
    {
        $curator = $this->curator();

        $response = $this->get(route('event.guest_submit', ['subdomain' => $curator->subdomain]).'?lang[]=en');

        $response->assertOk();
        $this->assertStringNotContainsString('why-create-account?lang=', $response->getContent());
    }

    /**
     * A selfhost without ALLOW_REGISTRATION rejects account creation in createAccountWithCode(),
     * but only once the form is submitted - so a visitor used to fill in the whole event form plus
     * name, password and terms before being told. The panel now opens in login mode and says so.
     */
    public function test_a_selfhost_with_registration_closed_says_so_before_the_form_is_filled(): void
    {
        config(['app.hosted' => false, 'app.allow_registration' => false]);

        $curator = $this->curator();

        $response = $this->get(route('event.guest_submit', ['subdomain' => $curator->subdomain]));
        $response->assertOk();

        // Note what does NOT pin anything here: the notice text itself renders into the HTML on
        // every install, because the v-if/v-else around it is resolved by Vue in the browser, not
        // by Blade. Only the seeded state below differs by install.
        $response->assertSee('Account creation is disabled on this server.');
        $this->assertStringContainsString('registrationEnabled: false', $response->getContent());
        $this->assertStringContainsString('accountMode: "login"', $response->getContent());
        $this->assertStringNotContainsString('accountMode: "register"', $response->getContent());

        // "Create your free account..." would contradict the notice. Hidden by Vue rather than by
        // Blade, so the gate is what can be asserted server-side.
        $this->assertStringContainsString(
            '<p v-if="!isAuthed && registrationEnabled"', $response->getContent()
        );

        // With registration closed EVERY email lands in login mode, so a returning user must still
        // be told they already have an account rather than that creation is disabled. Keyed on the
        // visitor (emailExists/emailStub), not on the install.
        $this->assertStringContainsString(
            '<span v-if="emailExists && !emailStub">', $response->getContent()
        );
        $this->assertStringContainsString(
            '<span v-else-if="!registrationEnabled">', $response->getContent()
        );
    }

    /** The same page on a normal install keeps the register branch. */
    public function test_registration_stays_open_when_the_install_allows_it(): void
    {
        config(['app.hosted' => false, 'app.allow_registration' => true]);

        $response = $this->get(route('event.guest_submit', ['subdomain' => $this->curator()->subdomain]));

        $response->assertOk();
        $this->assertStringContainsString('registrationEnabled: true', $response->getContent());
        $this->assertStringContainsString('accountMode: "register"', $response->getContent());
    }

    /**
     * Google sign-up. /sign_up has offered it for a long time and the docs call it quicker, but
     * this page - the one where an account is not optional - only ever offered a password.
     */
    public function test_it_offers_google_sign_up_when_the_install_has_it_configured(): void
    {
        // public_registration_enabled() is the other half of the button's gate; pin it rather than
        // inheriting it, or this passes on hosted and fails on a selfhost checkout.
        config(['services.google.client_id' => 'test-client-id', 'app.hosted' => true]);

        // One curator, held: curator() mints a fresh random subdomain on every call.
        $curator = $this->curator();

        $response = $this->get(route('event.guest_submit', ['subdomain' => $curator->subdomain]));

        $response->assertOk();
        $response->assertSee(route('event.guest_submit.google', ['subdomain' => $curator->subdomain]), false);
        $response->assertSee('Sign up with Google');
    }

    /** Nothing to offer when the install has no Google credentials. */
    public function test_google_sign_up_is_hidden_without_credentials(): void
    {
        config(['services.google.client_id' => null]);

        $this->get(route('event.guest_submit', ['subdomain' => $this->curator()->subdomain]))
            ->assertOk()
            ->assertDontSee('Sign up with Google');
    }

    /** ...and it inherits Part B: a closed install cannot create the account either way. */
    public function test_google_sign_up_is_hidden_when_registration_is_closed(): void
    {
        config([
            'services.google.client_id' => 'test-client-id',
            'app.hosted' => false,
            'app.allow_registration' => false,
        ]);

        $this->get(route('event.guest_submit', ['subdomain' => $this->curator()->subdomain]))
            ->assertOk()
            ->assertDontSee('Sign up with Google');
    }

    /**
     * The Google round trip has to come back HERE. SocialAuthController ends every account path
     * with redirect()->intended(); without a stored URL the pending_request chain wins and lands
     * them in the admin event editor, abandoning the curator's form and orphaning the localStorage
     * draft, which is keyed to this page's subdomain.
     */
    public function test_the_google_hop_stores_this_page_as_the_post_login_destination(): void
    {
        // The hop now mirrors the button's gate, so its preconditions have to be pinned.
        config(['services.google.client_id' => 'test-client-id', 'app.hosted' => true]);

        $curator = $this->curator();

        // Asserted as a literal path rather than by re-running the controller's own
        // app_url(route('auth.google', [], false)) - mirroring the implementation would let it be
        // rewritten with this still green. The app_url() wrapper itself is pinned separately, in
        // test_the_google_hop_targets_the_app_subdomain_on_hosted.
        $response = $this->get(route('event.guest_submit.google', ['subdomain' => $curator->subdomain]));

        $response->assertRedirect();
        $this->assertStringEndsWith('/auth/google', $response->headers->get('Location'));

        $this->assertSame(
            route('event.guest_submit', ['subdomain' => $curator->subdomain]),
            session('url.intended')
        );
    }

    /** A guest reading the schedule's language should come back to it in that language. */
    public function test_the_google_hop_carries_a_valid_lang_into_the_destination(): void
    {
        // The hop now mirrors the button's gate, so its preconditions have to be pinned.
        config(['services.google.client_id' => 'test-client-id', 'app.hosted' => true]);

        $curator = $this->curator();

        $this->get(route('event.guest_submit.google', ['subdomain' => $curator->subdomain]).'?lang=es');

        $this->assertSame(
            route('event.guest_submit', ['subdomain' => $curator->subdomain, 'lang' => 'es']),
            session('url.intended')
        );
    }

    /** An array ?lang must not reach the stored URL either. */
    public function test_the_google_hop_drops_an_array_lang(): void
    {
        // The hop now mirrors the button's gate, so its preconditions have to be pinned.
        config(['services.google.client_id' => 'test-client-id', 'app.hosted' => true]);

        $curator = $this->curator();

        $this->get(route('event.guest_submit.google', ['subdomain' => $curator->subdomain]).'?lang[]=en');

        $this->assertSame(
            route('event.guest_submit', ['subdomain' => $curator->subdomain]),
            session('url.intended')
        );
    }

    /** The hop must not point anywhere useless. */
    public function test_the_google_hop_404s_for_a_schedule_not_accepting_requests(): void
    {
        // Credentials present, so the 404 can only come from the accept-requests gate.
        config(['services.google.client_id' => 'test-client-id', 'app.hosted' => true]);

        $curator = $this->createCurator($this->createOwner(), [
            'accept_requests' => false,
            'require_account' => true,
        ]);

        $this->get(route('event.guest_submit.google', ['subdomain' => $curator->subdomain]))
            ->assertNotFound();

        $this->assertNull(session('url.intended'));
    }

    /**
     * Merely VIEWING the page must store nothing. url.intended is not flash data and is consumed
     * by nine call sites across seven controllers, so writing it on every page view hijacked the
     * next login - which silently skipped the pending_transfer handover offer and the
     * pending_fan_content submission, both of which reach /login through a plain link and need
     * HomeController to run.
     */
    public function test_viewing_the_page_stores_no_post_login_destination(): void
    {
        $curator = $this->curator();

        $this->get(route('event.guest_submit', ['subdomain' => $curator->subdomain]))->assertOk();
        $this->assertNull(session('url.intended'));

        $this->actingAs($this->createOwner())
            ->get(route('event.guest_submit', ['subdomain' => $curator->subdomain]))
            ->assertOk();
        $this->assertNull(session('url.intended'));
    }

    /**
     * Reachable from a stale tab: a guest opens the page, signs in elsewhere, comes back and
     * clicks the button that is still in the old HTML. The hop must not write url.intended for
     * them - auth.google's guest middleware never consumes it, so it would survive to misdirect
     * the email-verification redirect, the one consumer that fires without an intervening logout
     * and does not set its own.
     */
    public function test_the_google_hop_writes_nothing_for_a_signed_in_visitor(): void
    {
        config(['services.google.client_id' => 'test-client-id', 'app.hosted' => true]);

        $curator = $this->curator();

        $this->actingAs($this->createOwner())
            ->get(route('event.guest_submit.google', ['subdomain' => $curator->subdomain]))
            ->assertRedirect(route('event.guest_submit', ['subdomain' => $curator->subdomain]));

        $this->assertNull(session('url.intended'));
    }

    /**
     * The hop must gate on what the BUTTON gates on. GOOGLE_CLIENT_ID is null by default, and
     * forwarding to /auth/google without it hands Socialite no client id - so there is no
     * destination worth remembering, and an ungated write lets any unauthenticated GET plant
     * url.intended for any schedule.
     */
    public function test_the_google_hop_404s_without_google_credentials(): void
    {
        config(['services.google.client_id' => null]);

        $this->get(route('event.guest_submit.google', ['subdomain' => $this->curator()->subdomain]))
            ->assertNotFound();

        $this->assertNull(session('url.intended'));
    }

    /** Same for an install that cannot create the account at the far end anyway. */
    public function test_the_google_hop_404s_when_registration_is_closed(): void
    {
        config([
            'services.google.client_id' => 'test-client-id',
            'app.hosted' => false,
            'app.allow_registration' => false,
        ]);

        $this->get(route('event.guest_submit.google', ['subdomain' => $this->curator()->subdomain]))
            ->assertNotFound();

        $this->assertNull(session('url.intended'));
    }

    /**
     * A custom-domain session is scoped to that origin (ResolveCustomDomain nulls session.domain),
     * so url.intended written there is invisible to the callback on app. - the same reason
     * showGuestSubmit() bounces custom-domain hits to the canonical host.
     */
    public function test_the_google_hop_bounces_a_custom_domain_to_the_request_router(): void
    {
        config(['app.hosted' => true, 'services.google.client_id' => 'test-client-id']);

        $curator = $this->createCurator($this->createOwner(), [
            'accept_requests' => true,
            'require_account' => true,
            'custom_domain' => 'https://hop-direct.test',
            'custom_domain_host' => 'hop-direct.test',
            'custom_domain_mode' => 'direct',
            'custom_domain_status' => 'active',
        ]);

        // Asserted on the path, not the host: like showGuestSubmit(), the bounce is generated
        // against the current request, so it stays on the custom domain and role.request is what
        // performs the canonical hop from there.
        $response = $this->get('https://hop-direct.test/'.$curator->subdomain.'/guest-submit/google');

        $response->assertRedirect();
        $this->assertStringEndsWith(
            '/'.$curator->subdomain.'/request',
            parse_url($response->headers->get('Location'), PHP_URL_PATH)
        );

        $this->assertNull(session('url.intended'));
    }

    /**
     * The app_url() wrapper on the redirect target, which no other test can see: app_url()
     * short-circuits to url() whenever APP_TESTING is set (app/helpers.php), so a bare
     * route('auth.google') is indistinguishable from the wrapped one under the normal test env.
     *
     * auth.google sits behind the app_subdomain middleware, so on hosted a tenant-host URL only
     * reaches it through RedirectToAppSubdomain's extra 302. Targeting app. directly skips that.
     * Routes were registered at boot, so unsetting is_testing here changes URL generation only.
     */
    public function test_the_google_hop_targets_the_app_subdomain_on_hosted(): void
    {
        config([
            'services.google.client_id' => 'test-client-id',
            'app.hosted' => true,
            'app.is_testing' => false,
        ]);

        $response = $this->get(route('event.guest_submit.google', ['subdomain' => $this->curator()->subdomain]));

        $response->assertRedirect();
        $this->assertSame(
            'app.'._base_domain(),
            parse_url($response->headers->get('Location'), PHP_URL_HOST)
        );
    }
}
