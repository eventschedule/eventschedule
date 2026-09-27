<?php

namespace Tests\Feature;

use App\Models\MarketingDailyStat;
use App\Models\User;
use App\Notifications\SignupVerificationCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
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
     * action was to retype a code the visitor did not have. "Already registered?" now folds away
     * in step two again, because a code only ever goes to an address with no account - but only
     * inside the address guard, where "Use a different email" is on screen, and changeEmail()
     * brings it back. Asserted against the SCRIPT because the hiding is client-side.
     */
    public function test_the_page_no_longer_hides_the_way_out_once_a_code_is_sent(): void
    {
        $html = $this->signupPage()->assertOk()->getContent();

        $this->assertStringContainsString('id="already-registered"', $html);
        $this->assertSame(2, substr_count($html, "getElementById('already-registered')"),
            'already-registered is reached from somewhere other than the step-two fold and changeEmail()');
        $this->assertMatchesRegularExpression(
            "/function showCodeSentState\\(email\\) \\{[\\s\\S]*?if \\(email\\) \\{[^}]*?getElementById\\('already-registered'\\)/",
            $html,
            'already-registered is hidden outside the address guard, so an error reload with no address is a dead end again'
        );
        $this->assertMatchesRegularExpression("/function changeEmail\\(\\)[\\s\\S]*?alreadyRegistered\\.style\\.display = ''/", $html);
    }

    /**
     * Google folds away in step two, but only where step two has a way back.
     *
     * It used to be forbidden to hide it at all, because step two had no resend and no way to fix
     * the address. It has both now, in the code panel, so Google is hidden only inside the same
     * address guard that shows that panel - never on an error reload with no address, where
     * nothing else would lead back - and changeEmail() puts it back.
     */
    public function test_google_is_hidden_in_step_two_only_alongside_the_way_back(): void
    {
        $html = $this->signupPage()->assertOk()->getContent();

        $this->assertStringContainsString('id="google-signup-section"', $html);
        $this->assertSame(2, substr_count($html, "getElementById('google-signup-section')"),
            'Google is reached from somewhere other than the step-two fold and changeEmail()');
        $this->assertMatchesRegularExpression(
            "/function showCodeSentState\\(email\\) \\{[\\s\\S]*?if \\(email\\) \\{[^}]*?getElementById\\('google-signup-section'\\)/",
            $html,
            'Google is hidden outside the address guard, so an error reload with no address loses every way in'
        );
        $this->assertMatchesRegularExpression(
            "/function changeEmail\\(\\)[\\s\\S]*?googleSection\\.style\\.display = ''/",
            $html
        );
    }

    /**
     * A ticked consent box folds away in step two; an unticked one never does.
     *
     * A ticked box still posts while hidden, so folding it is not the #124 trap. An unticked one
     * (a reload, the mail's ?step=code link) has to stay, and anything that asks about it again
     * puts it back first, or the error and the focus land on something nobody can see.
     */
    public function test_the_terms_box_only_folds_away_once_ticked(): void
    {
        $html = $this->signupPage()->assertOk()->getContent();

        $this->assertStringContainsString("if (termsField && termsBox && termsBox.checked) termsField.style.display = 'none';", $html);
        $this->assertMatchesRegularExpression('/function requireTerms\\(\\) \\{[^}]*?showTermsField\\(\\);[^}]*?terms\\.focus\\(\\)/', $html);
        $this->assertMatchesRegularExpression("/if \\(field === 'terms'\\) \\{\\s*var termsError[^;]*;\\s*showTermsField\\(\\);/", $html);
        $this->assertMatchesRegularExpression("/function changeEmail\\(\\)[\\s\\S]*?termsField\\.style\\.display = ''/", $html);
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

    /**
     * An array in the email query parameter must not 500 an unauthenticated page.
     *
     * Both pages read a query param into a text field: /login for the "already have an account"
     * handoff, /sign_up for the address in a claim invitation. `?email[]=x` makes request('email')
     * return an ARRAY, which reaches ComponentAttributeBag::__toString()'s trim() on one page and
     * base64_decode() on the other - a TypeError either way, on routes anybody can hit.
     */
    public function test_an_array_in_the_email_parameter_does_not_error(): void
    {
        config(['app.hosted' => true, 'app.is_testing' => false]);

        $this->get(app_url('/login').'?email[]=x')->assertOk();
        $this->get(app_url('/sign_up').'?email[]=x')->assertOk();
    }

    /**
     * The sixth digit submits once, and only when the rest of the form is filled in.
     *
     * maybeAutoSubmit() runs on every `input` event and the handler slices to 6, so a seventh
     * keystroke leaves the value at 6 and re-fires. Each call reached requestSubmit(), which on an
     * incomplete form focuses the offending field and pops its bubble - taking the caret out of
     * the code box, repeatedly, while somebody is typing in it.
     */
    public function test_auto_submit_is_guarded(): void
    {
        $html = $this->signupPage()->assertOk()->getContent();

        $this->assertStringContainsString('if (autoSubmitted) return;', $html,
            'auto-submit has no once-only latch, so it re-fires on every keystroke past the sixth');
        $this->assertStringContainsString('!form.checkValidity()', $html,
            'auto-submit does not check the form is complete first');
        // The fallback for browsers without requestSubmit() goes through the fetch path too.
        // form.submit() skips the submit listener, so it would reload the page on a wrong code
        // and empty the password - the very thing the fetch submit exists to prevent.
        $this->assertStringContainsString('submitSignupForm(form);', $html,
            'no fallback for browsers without requestSubmit(), where the sixth digit does nothing at all');
        $this->assertStringNotContainsString('form.submit();', $html);
    }

    /**
     * Asking for a code twice at once sends two codes.
     *
     * The endpoint mails synchronously with no SMTP timeout, so the button stays live for however
     * long that takes, and the countdown meant to space these out only starts once a response
     * comes back.
     */
    public function test_the_send_path_cannot_overlap_itself(): void
    {
        $html = $this->signupPage()->assertOk()->getContent();

        $this->assertStringContainsString('if (sendInFlight) return;', $html);
        // The pressed button gets the busy state, not always the top one.
        $this->assertStringContainsString('sendVerificationCode(this)', $html);
    }

    /** Going back to step one has to undo step one's reveal, or required hidden fields remain. */
    public function test_changing_the_email_restores_step_one(): void
    {
        $html = $this->signupPage()->assertOk()->getContent();

        $this->assertStringContainsString('function hideSignupFields()', $html,
            'revealSignupFields() has no inverse, so "use a different email" leaves a required but hidden code field');
        $this->assertMatchesRegularExpression('/function changeEmail\(\)[\s\S]*?hideSignupFields\(\)/', $html);
        $this->assertMatchesRegularExpression('/function changeEmail\(\)[\s\S]*?clearTimeout\(resendTimer\)/', $html);
    }

    /** A browser that looks like a browser, so the counters' bot filters let it through. */
    private function browserHeaders(string $ip = '203.0.113.40'): array
    {
        return [
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/120 Safari/537.36',
            'HTTP_ACCEPT_LANGUAGE' => 'en-GB,en;q=0.9',
            'HTTP_CF_CONNECTING_IP' => $ip,
        ];
    }

    private function signupPayload(string $code, string $email = 'organizer@eventschedule-test.org'): array
    {
        return [
            'name' => 'Test Person',
            'email' => $email,
            'password' => 'correct-horse-battery',
            'verification_code' => $code,
            'terms' => '1',
        ];
    }

    private function stat(string $column): int
    {
        return (int) (MarketingDailyStat::where('date', now()->toDateString())->value($column) ?? 0);
    }

    /**
     * The hosted form submits with fetch, so a refused code has to come back as data.
     *
     * A full-page POST reloaded the form, and browsers never repopulate type="password": one
     * mistyped digit cost the visitor their password as well as the code. As a 422 with the
     * field named, the page shows it under the code box and nothing typed is lost.
     */
    public function test_a_wrong_code_is_answered_as_json_for_the_page_to_show_in_place(): void
    {
        config(['app.hosted' => true, 'app.is_testing' => false]);
        Cache::put('signup_code_email_654321', 'organizer@eventschedule-test.org', now()->addMinutes(10));

        $this->postJson(app_url('/sign_up'), $this->signupPayload('123456'), $this->browserHeaders())
            ->assertStatus(422)
            ->assertJsonValidationErrors('verification_code');

        $this->assertDatabaseMissing('users', ['email' => 'organizer@eventschedule-test.org']);
    }

    /**
     * A wrong guess must not burn the right code.
     *
     * The page keeps the visitor on the code step after a 422 so they can simply retype, which
     * is only true if the code in their inbox still works. Cache::pull() takes the SUBMITTED
     * code, so this pins that a mistype never touches the real one.
     */
    public function test_a_correct_code_after_a_wrong_one_signs_up_and_answers_with_the_redirect(): void
    {
        config(['app.hosted' => true, 'app.is_testing' => false]);
        Cache::put('signup_code_email_654321', 'organizer@eventschedule-test.org', now()->addMinutes(10));

        $this->postJson(app_url('/sign_up'), $this->signupPayload('123456'), $this->browserHeaders())
            ->assertStatus(422);

        $response = $this->postJson(app_url('/sign_up'), $this->signupPayload('654321'), $this->browserHeaders())
            ->assertOk()
            ->assertJsonStructure(['redirect']);

        $this->assertNotEmpty($response->json('redirect'));
        $this->assertDatabaseHas('users', ['email' => 'organizer@eventschedule-test.org']);
        $this->assertAuthenticated();
    }

    /** A full-page POST - no JavaScript, or a tab from before this change - still redirects. */
    public function test_a_plain_form_post_still_redirects(): void
    {
        config(['app.hosted' => true, 'app.is_testing' => false]);
        Cache::put('signup_code_email_654321', 'organizer@eventschedule-test.org', now()->addMinutes(10));

        $this->post(app_url('/sign_up'), $this->signupPayload('654321'), $this->browserHeaders())
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'organizer@eventschedule-test.org']);
    }

    /**
     * signup_code_invalid separates "came back and got it wrong" from "never came back".
     *
     * Deduped per visitor per day like the other two, so a visitor who mistypes three times is
     * one person with a problem, not three.
     */
    public function test_a_refused_code_is_counted_once_per_visitor(): void
    {
        config(['app.hosted' => true, 'app.is_testing' => false]);

        $this->postJson(app_url('/sign_up'), $this->signupPayload('111111'), $this->browserHeaders())->assertStatus(422);
        $this->assertSame(1, $this->stat('signup_code_invalid'));

        $this->postJson(app_url('/sign_up'), $this->signupPayload('222222'), $this->browserHeaders())->assertStatus(422);
        $this->assertSame(1, $this->stat('signup_code_invalid'), 'the same visitor again the same day is not a second person');

        $this->postJson(app_url('/sign_up'), $this->signupPayload('333333'), $this->browserHeaders('203.0.113.41'))->assertStatus(422);
        $this->assertSame(2, $this->stat('signup_code_invalid'));
    }

    /** And only a refused CODE counts: a missing name is not the code wall. */
    public function test_a_rejection_before_the_code_check_is_not_counted(): void
    {
        config(['app.hosted' => true, 'app.is_testing' => false]);

        $payload = $this->signupPayload('111111');
        unset($payload['name']);

        $this->postJson(app_url('/sign_up'), $payload, $this->browserHeaders())
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');

        $this->assertSame(0, $this->stat('signup_code_invalid'));
    }

    /** Every submit of the hosted form goes through fetch, asking for JSON. */
    public function test_the_hosted_form_submits_without_leaving_the_page(): void
    {
        $html = $this->signupPage()->assertOk()->getContent();

        $this->assertStringContainsString('function submitSignupForm(form)', $html);
        $this->assertMatchesRegularExpression("/addEventListener\('submit', function \(e\) \{\s*e\.preventDefault\(\);\s*submitSignupForm\(signupForm\);/", $html,
            'the form submit is not routed through submitSignupForm()');
        $this->assertMatchesRegularExpression("/function submitSignupForm[\s\S]*?'Accept': 'application\/json'/", $html);
        // A refused code resets the once-only latch, or the retyped code never auto-submits.
        $this->assertMatchesRegularExpression('/if \(errors\.verification_code\) \{[\s\S]*?autoSubmitted = false;/', $html);
    }

    /** The code panel says where else to look, and a resend is visible. */
    public function test_the_code_panel_helps_find_the_mail(): void
    {
        $html = $this->signupPage()->assertOk()->getContent();

        $this->assertStringContainsString(__('messages.subscribe_done_note'), $html);
        $this->assertStringContainsString(e(__('messages.signup_code_in_subject')), $html);
        $this->assertStringContainsString('id="code-resent-note"', $html);
        $this->assertStringContainsString(e(__('messages.code_resent')), $html);
        // "Open Gmail" was taken out of step two's header on request; keep it out.
        $this->assertStringNotContainsString('open-webmail-link', $html);
        $this->assertStringNotContainsString('function webmailFor', $html);
    }

    /**
     * Six boxes drawn over ONE real input, not six inputs.
     *
     * A phone's "code from Mail" suggestion fills a single one-time-code field; spread over six it
     * lands in the first box only. The boxes are aria-hidden paint; the input stays a real,
     * visible-to-the-browser field (never hidden or opacity:0, which some browsers skip for
     * autofill), with no placeholder, reading left to right even in RTL locales.
     */
    public function test_the_code_is_six_boxes_over_one_input(): void
    {
        $html = $this->signupPage()->assertOk()->getContent();

        $this->assertSame(1, preg_match_all('/<input[^>]*name="verification_code"/', $html));
        $this->assertMatchesRegularExpression('/<div id="code-boxes"[^>]*dir="ltr"/', $html);
        $this->assertMatchesRegularExpression('/<div class="code-slots" aria-hidden="true">/', $html);
        $this->assertSame(6, substr_count($html, 'data-code-slot class='));

        $field = $this->codeFieldMarkup($html);
        $this->assertStringNotContainsString('placeholder', $field);
        $this->assertStringNotContainsString('type="hidden"', $field);
        $this->assertStringNotContainsString('opacity-0', $field);

        // Painted from every place the value changes, including those an input event never sees.
        // Bounded by [^}] so it cannot borrow the same call from the paste handler below it.
        $this->assertMatchesRegularExpression("/codeInput\.addEventListener\('input', function\(e\) \{[^}]*?renderCodeSlots\(\);\s*maybeAutoSubmit\(this\);\s*\}\);/", $html);
        $this->assertMatchesRegularExpression("/function changeEmail\(\)[\s\S]*?codeInput\.value = '';[\s\S]*?renderCodeSlots\(\);/", $html);
        $this->assertMatchesRegularExpression("/codeInput\.value = '';[\s\S]*?setCodeBoxesState\('is-invalid', true\);\s*renderCodeSlots\(\);/", $html);
    }

    /**
     * A password manager's badge must not sit on the sixth box.
     *
     * The opt-out attributes cover the managers that honour them; the extra 44px, clipped away,
     * moves any other badge (placed from the input's right edge) off the boxes.
     */
    public function test_password_manager_badges_stay_off_the_boxes(): void
    {
        $html = $this->signupPage()->assertOk()->getContent();
        $field = $this->codeFieldMarkup($html);

        foreach (['data-1p-ignore', 'data-lpignore="true"', 'data-bwignore', 'data-form-type="other"', 'data-protonpass-ignore'] as $attribute) {
            $this->assertStringContainsString($attribute, $field);
        }
        $this->assertMatchesRegularExpression('/#code-boxes \.code-input \{[^}]*width: calc\(100% \+ 44px\);\s*clip-path: inset\(0 44px 0 0\);/', $html);
    }

    /**
     * Step one says what Continue does, and there is no numbered step bar.
     *
     * Step one used to read as a one-screen form with a stray "Already have a code?" beside it.
     * Nobody arriving fresh holds a code: a reload is restored from sessionStorage and the mail's
     * own link opens the step with ?step=code, so that link only muddied the page and is gone.
     * The page's own "1 Enter email, 2 Create account" bar went too: the onboarding bar on the
     * very next page numbers "Create account" as step 1, and the two disagreed one page apart.
     */
    public function test_step_one_explains_itself_without_a_step_bar(): void
    {
        $html = $this->signupPage()->assertOk()->getContent();

        $this->assertStringContainsString('id="signup-code-hint"', $html);
        $this->assertStringNotContainsString('have-code', $html);
        $this->assertStringNotContainsString('signup-steps', $html);
        $this->assertStringNotContainsString('setSignupStep', $html);
    }

    /**
     * Create Account is disabled until a code has gone out.
     *
     * Before then it can only earn a "verification code is required" error. It is switched on by
     * showCodeSentState() - every path that means a code was sent - and off again by
     * changeEmail(), and the fetch submit refuses on its own too, because the sixth-digit
     * auto-submit's requestSubmit() never goes through the button.
     */
    public function test_create_account_waits_for_a_code(): void
    {
        $html = $this->signupPage()->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<button type="submit" id="create-account-btn" disabled/', $html);
        $this->assertMatchesRegularExpression('/function showCodeSentState\\(email\\) \\{\\s*revealSignupFields\\(\\);\\s*setCreateAccountEnabled\\(true\\);/', $html);
        $this->assertMatchesRegularExpression('/function changeEmail\\(\\)[\\s\\S]*?setCreateAccountEnabled\\(false\\);/', $html);
        $this->assertMatchesRegularExpression('/function submitSignupForm\\(form\\) \\{\\s*if \\(signupSubmitting\\) return;[\\s\\S]*?if \\(!codeSent\\) return;/', $html);
    }

    /**
     * Local installs get the same two steps as production.
     *
     * APP_TESTING=true is what local dev runs, and the stepped page used to be gated on it being
     * OFF - so the one-screen fallback was the only version anybody reviewed locally. store()
     * still skips the code check under is_testing; only the page changed.
     */
    public function test_the_page_is_stepped_with_is_testing_on(): void
    {
        config(['app.hosted' => true, 'app.is_testing' => true]);

        $html = $this->get(app_url('/sign_up'))->assertOk()->getContent();

        $this->assertStringContainsString('id="code-boxes"', $html);
        $this->assertMatchesRegularExpression('/id="name-field"\s+style="display: none;"/', $html);
        $this->assertMatchesRegularExpression('/id="submit-section"[^>]*style="display: none;"/', $html);
        $this->assertDoesNotMatchRegularExpression('/<input[^>]*id="name"[^>]*required/', $html,
            'a required control inside a hidden container silently blocks the submit (#124)');
    }

    /** Step one ends in a plain Continue, and step two reads top to bottom in the order it is done. */
    public function test_step_one_continues_and_step_two_is_in_order(): void
    {
        $html = $this->signupPage()->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/id="send-code-btn"[^>]*>\s*'.preg_quote(e(__('messages.signup_continue')), '/').'/', $html);

        // Where the code went sits under the heading, then the fields.
        $order = ['id="code-sent-panel"', 'id="verification_code"', 'id="code-verified-note"', 'id="resend-code-btn"', 'id="code-help-note"', 'id="name"', 'id="password"', 'id="create-account-btn"'];
        $positions = array_map(fn ($needle) => strpos($html, $needle), $order);
        foreach ($positions as $i => $position) {
            $this->assertNotFalse($position, $order[$i].' is not on the page');
        }
        $sorted = $positions;
        sort($sorted);
        $this->assertSame($sorted, $positions, 'step two is out of order');
    }

    /** Spam and expiry help waits until the resend countdown runs out, or a restored step. */
    public function test_the_code_help_appears_only_once_it_is_needed(): void
    {
        $html = $this->signupPage()->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/id="code-help-note"[^>]*style="display: none;"/', $html);
        $this->assertMatchesRegularExpression("/resendTimer = setTimeout\\(function \\(\\) \\{[^}]*?row\\.style\\.display = '';\\s*showCodeHelp\\(\\);/", $html,
            'the resend row and the help are never revealed when the wait ends');

        // No ticking "Resend in 29s" under a code that has only just been sent.
        $this->assertStringNotContainsString('resend-countdown', $html);
        $this->assertMatchesRegularExpression("/function startResendCountdown\\(seconds\\) \\{[\\s\\S]*?row\\.style\\.display = 'none';/", $html);
        $this->assertMatchesRegularExpression('/var stored = readCodeSent\(\);[\s\S]*?showCodeSentState\(stored\.email\);\s*showCodeHelp\(\);/', $html);
    }

    /** Back in step two returns to step one instead of leaving the page. */
    public function test_back_returns_to_step_one(): void
    {
        $html = $this->signupPage()->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/function showCodeSentState\(email\) \{[\s\S]*?enterStepTwoHistory\(\);\s*\}/', $html);
        // One entry per visit: a reload keeps history.state, and a second push would need two Backs.
        $this->assertMatchesRegularExpression('/try \{\s*if \(!\(history\.state && history\.state\.signupStep === 2\)\) \{\s*history\.pushState/', $html);
        $this->assertMatchesRegularExpression("/addEventListener\('popstate'[\s\S]*?if \(inStepTwo && !toStepTwo\) \{\s*changeEmail\(\);/", $html);
    }

    /** An unticked box and a bad address are reported by the same press, not one per press. */
    public function test_continue_reports_terms_and_address_together(): void
    {
        $html = $this->signupPage()->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/var termsOk = requireTerms\(\);[\s\S]*?if \(emailError\) \{[\s\S]*?if \(!termsOk \|\| emailError\) return;/', $html);
    }

    /** The typo suggestion stops the first press only, never a resend, and is built without innerHTML. */
    public function test_a_likely_typo_stops_the_first_press_only(): void
    {
        $html = $this->signupPage()->assertOk()->getContent();

        $this->assertStringContainsString('id="email-suggestion"', $html);
        $this->assertStringContainsString("updateEmailSuggestion() && suggestionShownFor !== email) {\n                    suggestionShownFor = email;\n                    return;", $html);
        $this->assertStringContainsString("triggerBtn !== document.getElementById('resend-code-btn')", $html);
        // Providers a letter away from gmail.com must never be "corrected" to it.
        foreach (['mail.com', 'email.com', 'ymail.com', 'gmx.com'] as $real) {
            $this->assertStringContainsString("'".$real."'", $html);
        }
    }

    /** An address that already has an account is told so with a way to log in, not a dead end. */
    public function test_a_registered_address_is_offered_the_login(): void
    {
        config(['app.hosted' => true]);
        Notification::fake();

        User::factory()->create(['email' => 'taken@eventschedule-test.org']);

        $this->postJson(route('sign_up.send_code'), ['email' => 'taken@eventschedule-test.org'])
            ->assertStatus(422)
            ->assertJson(['reason' => 'registered']);

        $html = $this->signupPage()->assertOk()->getContent();
        $this->assertMatchesRegularExpression("/if \(data\.reason === 'registered'\) \{[\s\S]*?searchParams\.set\('email', email\)/", $html);
    }

    /** A stub account (followed a schedule, never set a password) still gets its code. */
    public function test_a_stub_account_still_gets_a_code(): void
    {
        config(['app.hosted' => true]);
        Notification::fake();

        $stub = User::factory()->create(['email' => 'stub@eventschedule-test.org']);
        $stub->forceFill(['password' => null, 'google_id' => null, 'google_oauth_id' => null, 'facebook_id' => null])->save();
        $this->assertTrue($stub->fresh()->isStub());

        $this->postJson(route('sign_up.send_code'), ['email' => 'stub@eventschedule-test.org'])
            ->assertOk()
            ->assertJsonMissing(['reason' => 'registered']);
    }

    /**
     * The code is checked as soon as it is complete - and the check does not use it up.
     *
     * The page used to learn a code was wrong only after a name and a password as well. A check
     * that PULLED the code would leave store() nothing to accept, so the right code, checked, must
     * still sign the visitor up.
     */
    public function test_a_checked_code_still_signs_up(): void
    {
        config(['app.hosted' => true, 'app.is_testing' => false]);
        Cache::put('signup_code_email_654321', 'organizer@eventschedule-test.org', now()->addMinutes(10));

        $this->postJson(app_url('/sign_up/check-code'), ['email' => 'organizer@eventschedule-test.org', 'verification_code' => '123456'], $this->browserHeaders())
            ->assertOk()->assertJson(['valid' => false]);
        $this->postJson(app_url('/sign_up/check-code'), ['email' => 'Organizer@eventschedule-test.org', 'verification_code' => '654321'], $this->browserHeaders())
            ->assertOk()->assertJson(['valid' => true]);

        $this->postJson(app_url('/sign_up'), $this->signupPayload('654321'), $this->browserHeaders())
            ->assertOk()
            ->assertJsonStructure(['redirect']);
        $this->assertDatabaseHas('users', ['email' => 'organizer@eventschedule-test.org']);
    }

    /** Somebody else's code is not "valid" for this address. */
    public function test_a_code_for_another_address_is_not_valid(): void
    {
        config(['app.hosted' => true, 'app.is_testing' => false]);
        Cache::put('signup_code_email_654321', 'someone-else@eventschedule-test.org', now()->addMinutes(10));

        $this->postJson(app_url('/sign_up/check-code'), ['email' => 'organizer@eventschedule-test.org', 'verification_code' => '654321'], $this->browserHeaders())
            ->assertOk()->assertJson(['valid' => false]);
    }

    /**
     * Five wrong checks and even the right code is refused until a new one is sent.
     *
     * Otherwise the check would be a free oracle for guessing codes, bounded only by the per-IP
     * throttle. Sending a new code clears the count (and is itself five an hour per address).
     */
    public function test_wrong_checks_lock_the_code_until_a_new_one_is_sent(): void
    {
        config(['app.hosted' => true, 'app.is_testing' => false]);
        Notification::fake();
        $email = 'organizer@eventschedule-test.org';
        Cache::put('signup_code_email_654321', $email, now()->addMinutes(10));

        foreach (['111111', '222222', '333333', '444444', '555555'] as $wrong) {
            $this->postJson(app_url('/sign_up/check-code'), ['email' => $email, 'verification_code' => $wrong], $this->browserHeaders())
                ->assertJson(['valid' => false]);
        }

        $this->postJson(app_url('/sign_up/check-code'), ['email' => $email, 'verification_code' => '654321'], $this->browserHeaders())
            ->assertOk()->assertJson(['valid' => false, 'expired' => true]);

        $this->postJson(route('sign_up.send_code'), ['email' => $email])->assertOk();

        $this->postJson(app_url('/sign_up/check-code'), ['email' => $email, 'verification_code' => '654321'], $this->browserHeaders())
            ->assertOk()->assertJson(['valid' => true]);
    }

    /** The page checks the code before any submit, and a network failure falls back to store(). */
    public function test_the_page_checks_the_code_before_submitting(): void
    {
        $html = $this->signupPage()->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/function maybeAutoSubmit\(codeInput\) \{[\s\S]*?if \(codeCheckedValue !== codeInput\.value\) \{\s*checkCode\(codeInput\);\s*return;\s*\}[\s\S]*?requestSubmit/', $html);
        $this->assertStringContainsString(json_encode(route('sign_up.check_code'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), str_replace('\/', '/', $html));
        $this->assertStringContainsString('id="code-verified-note"', $html);
        // A refusal's message goes with the next keystroke, or it sits under green boxes later.
        $this->assertMatchesRegularExpression("/codeInput\\.addEventListener\\('input', function\\(e\\) \\{[^}]*?querySelector\\('\\[data-signup-error=\"verification_code\"\\]'\\);\\s*if \\(codeError\\) codeError\\.remove\\(\\);/", $html);
        // Any answer that is not a clear yes or no carries on, as before this existed.
        $this->assertMatchesRegularExpression('/\} else \{\s*carryOn\(\);\s*\}\s*\}\)\s*\.catch\(function \(\) \{[^}]*?carryOn\(\);/', $html);
    }

    /**
     * Step two keeps the address RENDERED, so a password manager can pair it with the password.
     *
     * display:none took it out of what "Save password?" looks at, and the new login was saved
     * with no address.
     */
    public function test_step_two_keeps_the_address_visible_to_password_managers(): void
    {
        $html = $this->signupPage()->assertOk()->getContent();

        $this->assertMatchesRegularExpression("/emailEntry\\.classList\\.add\\('signup-visually-hidden'\\)/", $html);
        $this->assertStringNotContainsString("emailEntry.style.display = 'none'", $html);
        $this->assertMatchesRegularExpression("/emailInput\\.setAttribute\\('autocomplete', 'username'\\)/", $html);
        $this->assertMatchesRegularExpression("/function changeEmail\\(\\)[\\s\\S]*?emailEntry\\.classList\\.remove\\('signup-visually-hidden'\\)/", $html);
    }

    /** The password rule ticks green once met, and Continue says a code is being sent. */
    public function test_small_step_two_details(): void
    {
        $html = $this->signupPage()->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<p id="password-help"[^>]*>[\s\S]*?data-met-icon/', $html);
        $this->assertMatchesRegularExpression('/var met = this\\.value\\.length >= 8;/', $html);
        $this->assertStringContainsString(json_encode(__('messages.sending_code')), $html);
    }

    /** A reloaded or discarded tab comes back to the code step it was on. */
    public function test_the_code_step_survives_a_reload(): void
    {
        $html = $this->signupPage()->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/if \(response\.ok && data\.success\) \{[\s\S]*?rememberCodeSent\(email\);/', $html,
            'a successful send is not remembered for this tab');
        $this->assertMatchesRegularExpression('/var stored = readCodeSent\(\);[\s\S]*?showCodeSentState\(stored\.email\)/', $html);
        // Every sessionStorage access is guarded: it throws in private windows.
        $this->assertSame(
            substr_count($html, 'sessionStorage.'),
            preg_match_all('/try \{\s*(?:var stored = JSON\.parse\()?sessionStorage\./', $html)
        );
    }

    /** "Continue sign-up" in the mail opens the code step, with the address filled in. */
    public function test_the_step_parameter_opens_the_code_step(): void
    {
        config([
            'app.hosted' => true,
            'app.is_testing' => false,
        ]);

        $email = 'organizer@eventschedule-test.org';

        $with = $this->get(app_url('/sign_up').'?'.http_build_query(['email' => base64_encode($email), 'step' => 'code']))->assertOk()->getContent();
        $this->assertStringContainsString('if (true && emailInput.value)', $with);
        $this->assertStringContainsString('value="'.$email.'"', $with);

        $without = $this->get(app_url('/sign_up'))->assertOk()->getContent();
        $this->assertStringContainsString('if (false && emailInput.value)', $without);
    }

    /**
     * The code mail links back to the code step - for sign-up only, and never with the code in it.
     *
     * Rendered rather than inspected: the button is only worth anything if it is in the HTML the
     * visitor opens. The guest-add flow mounts the same send method and has no page to return
     * to, so its mail must carry no button.
     */
    public function test_the_code_mail_links_back_to_the_code_step(): void
    {
        config(['app.hosted' => true]);
        Notification::fake();

        $email = 'organizer@eventschedule-test.org';
        $this->postJson(route('sign_up.send_code'), ['email' => $email])->assertOk();

        Notification::assertSentOnDemand(SignupVerificationCode::class, function ($notification, $channels, $notifiable) use ($email) {
            $mail = $notification->toMail($notifiable);
            $html = $mail->render();
            $code = $mail->envelope()->subject;
            preg_match('/\d{6}/', $code, $m);

            $this->assertStringContainsString(e(__('messages.continue_signup')), $html);
            $this->assertStringContainsString('step=code', $html);
            $this->assertStringContainsString(urlencode(base64_encode($email)), $html);
            $this->assertStringNotContainsString('verification_code='.$m[0], $html);
            $this->assertStringNotContainsString('code='.$m[0], $html);

            return true;
        });
    }

    /**
     * The text part is plain text, so an HTML-escaped URL arrives as `&amp;step=code` - a parameter
     * named `amp;step` - and the link opens step one instead of the code step.
     */
    public function test_the_text_mail_carries_the_continue_link_unescaped(): void
    {
        $url = 'https://app.eventschedule.test/sign_up?email=b3JnYW5pemVy&step=code';

        $text = view('emails.signup_verification_code_text', [
            'code' => '123456',
            'continueUrl' => $url,
        ])->render();

        $this->assertStringContainsString($url, $text);
        $this->assertStringNotContainsString('&amp;', $text);
    }

    public function test_the_guest_code_mail_has_no_sign_up_link(): void
    {
        config(['app.hosted' => true]);
        Notification::fake();

        $this->postJson(route('event.guest_send_code', ['subdomain' => 'a-venue']), ['email' => 'guest@eventschedule-test.org'])
            ->assertOk();

        Notification::assertSentOnDemand(SignupVerificationCode::class, function ($notification, $channels, $notifiable) {
            $html = $notification->toMail($notifiable)->render();

            $this->assertStringNotContainsString('step=code', $html);
            $this->assertStringNotContainsString(e(__('messages.continue_signup')), $html);

            return true;
        });
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
