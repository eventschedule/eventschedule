<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The code step on /sign_up, which is where the measured leak is.
 *
 * In the 2026-08-30 growth export, 32 people asked for a sign-up code in August and 20 came back
 * with one. Those two counters are deduped identically by construction, so the 37.5% gap is real
 * rather than an artefact: a third of the people who started never finished.
 *
 * Everything asserted here is behind `config('app.hosted') && ! config('app.is_testing')` - the
 * `$stepped` branch in resources/views/auth/register.blade.php - and phpunit.xml pins
 * APP_TESTING on for the whole suite. So every test below turns it off explicitly and drives
 * app_url('/sign_up') rather than route('sign_up'): with is_testing off, RedirectToAppSubdomain
 * bounces any host that does not start with "app." and the page is never rendered.
 * SignupCodeFunnelTest does the same thing for the same reason.
 */
class SignupCodeStepTest extends TestCase
{
    use RefreshDatabase;

    private function signupPage()
    {
        config([
            'app.hosted' => true,
            'app.is_testing' => false,
            // The Google block is gated on this and the env ships it NULL, so without it the
            // section simply is not on the page and an assertion about hiding it passes for the
            // wrong reason. Found by mutating the view and watching the test stay green.
            'services.google.client_id' => 'test-google-client-id',
        ]);

        return $this->get(app_url('/sign_up'));
    }

    /**
     * autocomplete="off" is the one value that SUPPRESSES the OS one-time-code affordance.
     *
     * iOS Safari and Android Chrome offer an emailed or texted code above the keyboard only for
     * autocomplete="one-time-code", and iOS reads Mail for it, not only SMS. The field shipped as
     * "off" - which is also what x-text-input defaults to, so passing nothing is not enough either.
     */
    public function test_the_code_field_lets_a_phone_autofill_the_code(): void
    {
        $page = $this->signupPage()->assertOk();

        $html = $page->getContent();
        $field = $this->codeFieldMarkup($html);

        $this->assertStringContainsString('autocomplete="one-time-code"', $field);
        $this->assertStringContainsString('inputmode="numeric"', $field);
        $this->assertStringNotContainsString('autocomplete="off"', $field);
    }

    /**
     * maxlength truncates a paste BEFORE the input handler can strip the prose around the code.
     *
     * Pasting "Your code is 123456" out of the email inserted "Your c", which the digits-only
     * handler then reduced to an empty string: the box goes blank after a paste and says nothing.
     * The cap moved into JS (slice(0, 6) on input, plus a paste handler), so the attribute has to
     * be gone for that to be reachable at all.
     */
    public function test_the_code_field_does_not_truncate_a_pasted_line(): void
    {
        $field = $this->codeFieldMarkup($this->signupPage()->assertOk()->getContent());

        $this->assertStringNotContainsString('maxlength', $field);
    }

    /**
     * A wrong code must not come back pre-filled with the wrong code.
     *
     * old('verification_code') repopulated it, so the field the visitor had to correct looked
     * already filled in, while the password field beside it was silently empty (browsers never
     * repopulate type="password").
     */
    public function test_a_rejected_code_is_cleared_rather_than_echoed_back(): void
    {
        config(['app.hosted' => true, 'app.is_testing' => false]);

        $response = $this->from(app_url('/sign_up'))->post(app_url('/sign_up'), [
            'name' => 'Test Person',
            'email' => 'organizer@eventschedule-test.org',
            'password' => 'correct-horse-battery',
            'verification_code' => '111111',
            'terms' => '1',
        ]);

        $response->assertSessionHasErrors('verification_code');

        $html = $this->get(app_url('/sign_up'))->getContent();

        $this->assertStringNotContainsString('value="111111"', $this->codeFieldMarkup($html));
    }

    /**
     * The escape hatches have to survive the error path.
     *
     * The reveal branch hid the Google button, the guest option and "Already registered?", and it
     * also runs on $errors->any() - so one mistyped digit produced a page whose only remaining
     * action was to retype a code the visitor did not have. Asserted against the SCRIPT because
     * the hiding was client-side: the elements were always in the DOM.
     */
    public function test_the_page_no_longer_hides_the_way_out_once_a_code_is_sent(): void
    {
        $html = $this->signupPage()->assertOk()->getContent();

        foreach (['google-signup-section', 'already-registered'] as $id) {
            $this->assertStringContainsString('id="'.$id.'"', $html, $id.' is not on the page at all');

            // The script must not reach for these at all any more. Asserting "no getElementById"
            // rather than "no style.display = none" on purpose: the hiding went through a local
            // variable, so a pattern anchored on the id could not see the assignment two
            // statements later, and an earlier version of this test passed with the bug put back.
            $this->assertStringNotContainsString(
                "getElementById('".$id."')",
                $html,
                $id.' is reachable from the script again; if it is being hidden once a code is sent, one mistyped digit is a dead end'
            );
        }
    }

    /** Resend, change-email and the address echo are the three things a stuck visitor needs. */
    public function test_the_code_panel_offers_a_resend_and_a_way_to_fix_the_address(): void
    {
        $html = $this->signupPage()->assertOk()->getContent();

        $this->assertStringContainsString('id="resend-code-btn"', $html);
        $this->assertStringContainsString('id="change-email-btn"', $html);
        $this->assertStringContainsString('id="code-sent-address"', $html);

        // Not new strings: guest-submit has carried these keys, translated, all along.
        $this->assertStringContainsString(__('messages.resend_code'), $html);
        $this->assertStringContainsString(__('messages.use_another_email'), $html);
        $this->assertStringContainsString(__('messages.signup_verification_code_expiry'), $html);
    }

    /** The status line announced nothing to a screen reader. */
    public function test_the_code_status_line_is_announced(): void
    {
        $html = $this->signupPage()->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '/id="code-message"[^>]*aria-live="polite"/',
            $html
        );
    }

    /**
     * The code belongs in the subject, because that is what a phone shows on the lock screen.
     *
     * It used to be three paragraphs into the body, so every visitor had to leave the browser,
     * open the mail app, open the message, and come back - on the step that loses 37.5%.
     */
    public function test_the_email_subject_carries_the_code(): void
    {
        $notification = new \App\Notifications\SignupVerificationCode('123456');
        $notifiable = (new \Illuminate\Notifications\AnonymousNotifiable)->route('mail', 'someone@eventschedule-test.org');

        $subject = $notification->toMail($notifiable)->envelope()->subject;

        $this->assertStringContainsString('123456', $subject);
        $this->assertStringNotContainsString(':code', $subject, 'the placeholder was not interpolated');
    }

    /**
     * Read the language files directly rather than rendering.
     *
     * __() falls back to English, so a test that renders the mail per locale and greps for an
     * unresolved key passes with a whole language file deleted. Comparing the stored value against
     * English is the only assertion that can see a missing translation.
     */
    public function test_both_subject_and_heading_are_translated_in_every_locale(): void
    {
        $english = require base_path('resources/lang/en/messages.php');

        foreach (array_keys(config('app.supported_languages')) as $locale) {
            $strings = require base_path('resources/lang/'.$locale.'/messages.php');

            foreach (['signup_verification_code_subject', 'signup_verification_code_heading'] as $key) {
                $this->assertArrayHasKey($key, $strings, $key.' is missing from '.$locale);

                if ($locale !== 'en') {
                    $this->assertNotSame($english[$key], $strings[$key],
                        $locale.'.'.$key.' is still the English string');
                }
            }

            // The subject is the only one of the two that interpolates.
            $this->assertStringContainsString(':code', $strings['signup_verification_code_subject'],
                $locale.' lost the :code placeholder, so its subject will not carry the code');
            $this->assertStringNotContainsString(':code', $strings['signup_verification_code_heading'],
                $locale.' heading interpolates a code it is not given, so it renders a literal :code');
        }
    }

    /**
     * The page had no <h1> and nothing saying why to be on it.
     *
     * 468 sign-up views produced 64 accounts in August. The complete text of the page was: Email,
     * SEND CODE, Full Name, Password, Verification Code, the consent line, SIGN UP, or, Sign up
     * with Google, Already registered? - while the page the visitor had just left promised setup
     * in under two minutes and no credit card.
     */
    public function test_the_page_states_what_it_is_offering(): void
    {
        $html = $this->signupPage()->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<h1[^>]*id="signup-heading"/', $html);
        $this->assertStringContainsString(__('messages.signup_heading'), $html);
        $this->assertStringContainsString(__('messages.signup_subheading'), $html);
    }

    /** On selfhost this is an install wizard or an operator adding an account to their own server. */
    public function test_the_marketing_heading_is_hosted_only(): void
    {
        config(['app.hosted' => false, 'app.is_testing' => false]);

        $html = $this->get(app_url('/sign_up'))->getContent();

        $this->assertStringNotContainsString('id="signup-heading"', $html);
    }

    /**
     * Half of all accounts arrive through Google, and a returning Google user reaching this page
     * should not be told they are signing up.
     */
    public function test_the_google_button_says_continue_and_hides_its_icon_from_screen_readers(): void
    {
        $html = $this->signupPage()->assertOk()->getContent();

        $this->assertStringContainsString(__('messages.continue_with_google'), $html);
        $this->assertStringNotContainsString(__('messages.sign_up_with_google'), $html);

        // The shared component carries aria-hidden and the logical me-2 margin; the hand-rolled
        // copy this replaced had neither, and ar and he are shipped locales.
        $this->assertMatchesRegularExpression('/<svg class="w-5 h-5 me-2"[^>]*aria-hidden="true"/', $html);
    }

    /**
     * .auth-card is a gradient, so a flat colour behind the "or" label cannot match it.
     *
     * The two auth pages disagreed about WHICH flat colour to use, which is the tell.
     */
    public function test_the_or_divider_does_not_mask_the_card_gradient(): void
    {
        foreach (['/sign_up', '/login'] as $path) {
            config(['app.hosted' => true, 'app.is_testing' => false, 'services.google.client_id' => 'x']);
            $html = $this->get(app_url($path))->getContent();

            $this->assertStringNotContainsString('px-2 bg-white dark:bg-gray-800', $html, $path);
            $this->assertStringNotContainsString('px-2 bg-white dark:bg-gray-900', $html, $path);
        }
    }

    /** Somebody who has just typed their address should not have to type it again on /login. */
    public function test_login_prefills_an_address_handed_to_it(): void
    {
        config(['app.hosted' => true, 'app.is_testing' => false]);

        $html = $this->get(app_url('/login').'?email=someone%40eventschedule-test.org')->getContent();

        $this->assertMatchesRegularExpression(
            '/<input[^>]*id="email"[^>]*value="someone@eventschedule-test\.org"/',
            $html
        );
    }

    /**
     * min-width was applied to `form button`, which includes the password reveal toggle - an
     * absolutely positioned button inside the field.
     */
    public function test_the_min_width_rule_does_not_catch_the_password_toggle(): void
    {
        $html = $this->signupPage()->assertOk()->getContent();

        $this->assertStringNotContainsString('form button {', $html);
        $this->assertStringContainsString('#send-code-btn, form button[type="submit"] {', $html);
    }

    /** Isolates the verification_code input so an attribute elsewhere cannot satisfy a check. */
    private function codeFieldMarkup(string $html): string
    {
        $this->assertMatchesRegularExpression('/<input[^>]*id="verification_code"[^>]*>/', $html,
            'the verification code field is not on the page; the stepped branch probably did not render');

        preg_match('/<input[^>]*id="verification_code"[^>]*>/', $html, $matches);

        return $matches[0];
    }
}
