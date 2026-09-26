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
        $this->assertMatchesRegularExpression('/function changeEmail\(\)[\s\S]*?clearInterval\(resendTimer\)/', $html);
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
        $this->assertStringContainsString('id="open-webmail-link"', $html);
        $this->assertStringContainsString('id="code-resent-note"', $html);
        $this->assertStringContainsString(e(__('messages.code_resent')), $html);
        // The Gmail button searches every folder for our sender, so spam is covered too.
        $this->assertStringContainsString(rawurlencode('in:anywhere from:'.config('mail.from.address')), str_replace('\/', '/', $html));
    }

    /** Somebody who already holds a code can open the code step without asking for another. */
    public function test_a_visitor_with_a_code_can_open_the_code_step_without_a_send(): void
    {
        $html = $this->signupPage()->assertOk()->getContent();

        $this->assertStringContainsString('id="have-code-btn"', $html);
        $this->assertStringContainsString(e(__('messages.already_have_a_code')), $html);
        $this->assertMatchesRegularExpression("/getElementById\('have-code-btn'\)[\s\S]*?showCodeSentState\(typed\)/", $html);
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
