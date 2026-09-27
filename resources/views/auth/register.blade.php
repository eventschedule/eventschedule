<x-auth-layout>

    <x-slot name="head">
        <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function() {
            var timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;            
            document.getElementById('timezone').value = timezone;

            @if(request()->has('lang') && is_valid_language_code(request('lang')))
                document.getElementById('language_code').value = '{{ request('lang') }}';
            @elseif(session()->has('guest_language'))
                document.getElementById('language_code').value = '{{ session('guest_language') }}';
            @else
                var language = navigator.language || navigator.userLanguage;
                var twoLetterLanguageCode = language.substring(0, 2);
                document.getElementById('language_code').value = twoLetterLanguageCode;
            @endif

            // What SocialAuthController reads for a Google or Facebook sign-up, which never posts
            // this form's hidden fields. Only login.blade.php used to write these, so everybody who
            // chose Google HERE was stored as America/New_York and every schedule they then made
            // copied it. Same shape and lifetime as the login page's copy.
            document.cookie = "browser_timezone=" + timezone + ";path=/;max-age=3600;SameSite=Lax";
            document.cookie = "browser_language=" + document.getElementById('language_code').value + ";path=/;max-age=3600;SameSite=Lax";

            @if (selfhost_needs_setup())
                // Disable register button initially
                document.querySelector('button[type="submit"]').disabled = true;

                @if (!is_writable(base_path('.env')))
                    var testBtn = document.getElementById('test-connection-btn');
                    if (testBtn) testBtn.disabled = true;
                @endif
            @endif
        });

        @if (config('app.hosted'))
        var lockedEmail = null;

        /**
         * Whether a code has gone out for the address on screen.
         *
         * Create Account renders disabled and stays that way until then: without a code there is
         * nothing it can do but earn a "verification code is required" error. showCodeSentState()
         * is the one place that means "a code was sent" - a send, a reload of this tab, the mail's
         * ?step=code link, an error reload of a submit that carried one - and changeEmail() takes
         * it back, because a code for the old address is no use for the new one.
         */
        var codeSent = false;

        function setCreateAccountEnabled(enabled) {
            codeSent = enabled;
            var btn = document.getElementById('create-account-btn');
            if (btn) btn.disabled = !enabled;
        }
        var turnstileWidgetId = null;
        // True while Cloudflare has a challenge on screen; see waitForTurnstileToken().
        var turnstileInteractive = false;

        /**
         * Reveal the fields that only apply once a verification code has been sent.
         *
         * The verification code is required, but only from here on: it is rendered without the
         * attribute because its wrapper starts hidden, and a required control inside a hidden
         * container is one the browser will not focus, so requestSubmit() aborts on constraint
         * validation without submitting or saying anything (issue #124).
         */
        function revealSignupFields() {
            ['name-field', 'password-field', 'verification-code-field', 'submit-section'].forEach(function (id) {
                var el = document.getElementById(id);
                if (!el) return;
                el.style.display = 'block';
                // Re-trigger the entrance each time: removing and re-adding the class alone is
                // coalesced by the browser, the reflow in between is what restarts it.
                el.classList.remove('signup-step-enter');
                void el.offsetWidth;
                el.classList.add('signup-step-enter');
            });

            ['name', 'password', 'verification_code'].forEach(function (id) {
                var el = document.getElementById(id);
                if (el) el.required = true;
            });
        }

        /**
         * The inverse of revealSignupFields(), for going back to step one.
         *
         * Disarming `required` matters as much as hiding: a required control inside a hidden
         * container is one the browser refuses to focus, so constraint validation fails and the
         * submit is abandoned silently. That is issue #124, and it is why revealSignupFields()
         * arms these at the moment they become visible rather than in the markup.
         */
        function hideSignupFields() {
            ['name-field', 'password-field', 'verification-code-field', 'submit-section'].forEach(function (id) {
                var el = document.getElementById(id);
                if (el) el.style.display = 'none';
            });

            ['name', 'password', 'verification_code'].forEach(function (id) {
                var el = document.getElementById(id);
                if (el) el.required = false;
            });
        }

        /**
         * Put the page into the "we have sent you a code" state.
         *
         * Note what this deliberately does NOT do: hide the guest option or the "Already
         * registered?" link. It used to hide those and Google, and because the same branch also
         * runs on a validation error, ONE mistyped digit reloaded into a page whose only remaining
         * action was to retype a code the visitor did not have - no resend, no way to correct the
         * address, and the password field emptied by the browser. Google does fold away now, but
         * only inside the address guard, where "Use a different email" and Resend are on screen and
         * changeEmail() brings it back.
         */
        function showCodeSentState(email) {
            revealSignupFields();
            setCreateAccountEnabled(true);

            var emailInput = document.getElementById('email');
            if (email) {
                lockedEmail = email.toLowerCase();
                emailInput.setAttribute('readonly', 'readonly');
                emailInput.classList.add('bg-gray-100', 'dark:bg-gray-700', 'cursor-not-allowed');

                var address = document.getElementById('code-sent-address');
                if (address) address.textContent = email;

                // Hide Continue - but ONLY here, inside the address guard.
                // setSendButtonIdle() re-enables it on every response and the resend wait only
                // governs Resend, so leaving it on screen gave step two a second send
                // path with no rate-limit feedback. Outside this guard it was worse: an
                // empty-address restore hid it while also not showing the panel, leaving no way to
                // request a code at all.
                var sendCodeBtn = document.getElementById('send-code-btn');
                if (sendCodeBtn) sendCodeBtn.style.display = 'none';

                // Step one's ways in fold away too, so step two reads panel, code, name, password,
                // Create account, with nothing above it asking to start over. Only here, inside the
                // address guard: the panel below is then on screen with "Use a different email"
                // and Resend, and changeEmail() brings both back. Hiding Google used to strand an
                // error reload because step two had no way back at all; it has two now.
                var googleSection = document.getElementById('google-signup-section');
                if (googleSection) googleSection.style.display = 'none';

                // The consent box only when it is already ticked. A ticked box still posts while
                // hidden, so this is not the #124 trap (an UNFILLED required control nobody can
                // see). Unticked - a reload or the mail's ?step=code link, both fresh pages - it
                // stays, and requireTerms() re-shows it in any case before asking for the tick.
                var termsBox = document.getElementById('terms');
                var termsField = document.getElementById('terms-field');
                if (termsField && termsBox && termsBox.checked) termsField.style.display = 'none';

                // The panel states the address and offers "use a different email", so the locked
                // field above it only repeated it. Same guard as the panel: with no address (an
                // error reload) the field must stay, or there is nothing left to type into.
                //
                // Visually hidden, NOT display:none: a password manager works out the username for
                // "Save password?" from the fields around the password, and skips ones that are not
                // rendered - so the new login was saved with no address, or somebody else's.
                // Out of the tab order and the accessibility tree while it is folded away.
                var emailEntry = document.getElementById('email-entry');
                if (emailEntry) emailEntry.classList.add('signup-visually-hidden');
                emailInput.setAttribute('tabindex', '-1');
                emailInput.setAttribute('aria-hidden', 'true');
                emailInput.setAttribute('autocomplete', 'username');

                // A code only goes to an address with no account (the send refuses one that has,
                // with a Log in link of its own), so "Already registered?" cannot apply here.
                // changeEmail() brings it back with step one.
                var alreadyRegistered = document.getElementById('already-registered');
                if (alreadyRegistered) alreadyRegistered.style.display = 'none';

            }

            // Only with an address. The restore path calls this with emailInput.value, which can
            // be empty on an error reload, and the panel then reads "We sent a code to ." with a
            // blank <bdi> where the address should be.
            var panel = document.getElementById('code-sent-panel');
            if (panel && email) panel.style.display = 'block';


            // Name the step, in the page and in the tab strip. This flow REQUIRES leaving the tab
            // to read a mail, and every auth page shipped the same literal <title>Event Schedule</title>,
            // so finding the way back meant recognising one of several identical tabs.
            var heading = document.getElementById('signup-heading');
            var subheading = document.getElementById('signup-subheading');
            if (heading) heading.textContent = @json(__('messages.signup_check_email_heading'));
            // #code-sent-panel takes its place: "We emailed a 6-digit code to x" and the change pill.
            if (subheading) subheading.style.display = 'none';
            document.title = @json(__('messages.signup_check_email_heading')) + ' | Event Schedule';

            // Hidden again on every entry: a send starts the countdown, whose end shows it; the
            // restore paths call showCodeHelp() themselves, since no countdown runs for them.
            var helpNote = document.getElementById('code-help-note');
            if (helpNote) helpNote.style.display = 'none';

            enterStepTwoHistory();
        }

        /**
         * Where to look for a code that has not arrived: the expiry, spam folder and subject line.
         *
         * Shown only once the resend countdown runs out, or at once when the step is restored and
         * no countdown is running. Somebody whose code arrived in five seconds never needs it.
         */
        function showCodeHelp() {
            var helpNote = document.getElementById('code-help-note');
            if (helpNote) helpNote.style.display = 'block';
        }

        /**
         * Make the browser's Back button return to step one rather than leave the page.
         *
         * On a phone, Back is how somebody fixes a mistyped address, and it used to throw the whole
         * form away. One entry per visit to step two: a reload keeps history.state, so pushing again
         * there would take two presses of Back to get out. Guarded because pushState can throw in
         * a sandboxed or file:// context, and the page works the same without it.
         */
        function enterStepTwoHistory() {
            try {
                if (!(history.state && history.state.signupStep === 2)) {
                    history.pushState({ signupStep: 2 }, '');
                }
            } catch (e) {}
        }

        window.addEventListener('popstate', function (e) {
            var inStepTwo = lockedEmail !== null;
            var toStepTwo = e.state && e.state.signupStep === 2;
            if (inStepTwo && !toStepTwo) {
                changeEmail();
            }
        });

        /**
         * Back to step one, with the address editable again.
         *
         * The input listener below rewrites any edit back to lockedEmail, so a typo was
         * unrecoverable without reloading the page. Clearing the lock is the whole fix.
         */
        function changeEmail() {
            var emailInput = document.getElementById('email');
            var panel = document.getElementById('code-sent-panel');
            var codeInput = document.getElementById('verification_code');
            var codeMessage = document.getElementById('code-message');

            lockedEmail = null;
            forgetCodeSent();
            emailInput.removeAttribute('readonly');
            emailInput.classList.remove('bg-gray-100', 'dark:bg-gray-700', 'cursor-not-allowed');
            if (panel) panel.style.display = 'none';
            if (codeInput) codeInput.value = '';
            setCodeBoxesState('is-invalid', false);
            markCodeUnchecked();
            renderCodeSlots();

            var emailEntry = document.getElementById('email-entry');
            if (emailEntry) emailEntry.classList.remove('signup-visually-hidden');
            emailInput.removeAttribute('tabindex');
            emailInput.removeAttribute('aria-hidden');
            emailInput.setAttribute('autocomplete', 'email');
            var alreadyRegistered = document.getElementById('already-registered');
            if (alreadyRegistered) alreadyRegistered.style.display = '';
            if (codeMessage) codeMessage.innerHTML = '';

            // The rest of the inverse. Without it the page sat in a hybrid state: the panel gone,
            // but Name, Password, Terms, Submit and an empty REQUIRED "Verification code" box all
            // still on screen, with nothing left to explain where a code would come from.
            hideSignupFields();
            setCreateAccountEnabled(false);

            // Put the send button back, since showCodeSentState() hid it.
            var sendCodeBtn = document.getElementById('send-code-btn');
            if (sendCodeBtn) sendCodeBtn.style.display = '';

            // And step one's ways in, which showCodeSentState() folded away.
            var googleSection = document.getElementById('google-signup-section');
            if (googleSection) googleSection.style.display = '';
            var termsField = document.getElementById('terms-field');
            if (termsField) termsField.style.display = '';

            // Supersede anything still in flight. Without this the page looked live and was dead:
            // a slow Resend left sendInFlight true, changeEmail() un-hid #send-code-btn - which was
            // only ever HIDDEN, never disabled - and clicking it hit `if (sendInFlight) return;`
            // and did nothing at all. No spinner, no message. The bump also tells that response it
            // has been superseded, so it cannot re-lock the address being abandoned.
            sendGeneration++;
            sendInFlight = false;
            setSendButtonIdle(document.getElementById('send-code-btn'));
            setSendButtonIdle(document.getElementById('resend-code-btn'));

            // The auto-submit latch is page-lifetime, so without this a second address could never
            // auto-submit.
            autoSubmitted = false;

            // And stop the resend wait, which otherwise runs on inside a hidden step and later
            // re-reveals a Resend row behind it.
            if (resendTimer) {
                clearTimeout(resendTimer);
                resendTimer = null;
            }
            var resendRow = document.getElementById('code-resend-row');
            if (resendRow) resendRow.style.display = '';
            // A "Code resent" still fading out belongs to the address being abandoned.
            var resentNote = document.getElementById('code-resent-note');
            if (resentNote) resentNote.style.display = 'none';

            // Back to step one means back to step one's heading, or the page still says to go and
            // read a mail that no longer applies to the address in the box.
            var heading = document.getElementById('signup-heading');
            var subheading = document.getElementById('signup-subheading');
            if (heading) heading.textContent = @json(__('messages.signup_heading'));
            if (subheading) subheading.style.display = '';
            document.title = 'Event Schedule';

            var helpNote = document.getElementById('code-help-note');
            if (helpNote) helpNote.style.display = 'none';

            emailInput.focus();
            emailInput.select();
        }

        /**
         * Keep "Didn't receive the code? Resend code" off screen for a moment after a send.
         *
         * Not decoration: sign_up/send-code allows 5 per hour per address AND, since the named
         * prefix in routes/auth.php, 5 per minute per IP. A visitor who taps resend four times
         * because nothing arrived would spend the whole minute bucket and meet a 429.
         *
         * Hidden rather than counted down: a ticking "Resend in 29s" under a code that has only
         * just been sent is noise, and reads as though the code is late already. The row appears
         * once a resend is possible, with the where-else-to-look note beside it - the moment
         * somebody still waiting actually needs both.
         */
        var resendTimer = null;
        function startResendCountdown(seconds) {
            var row = document.getElementById('code-resend-row');
            if (!row) return;

            row.style.display = 'none';
            if (resendTimer) clearTimeout(resendTimer);

            resendTimer = setTimeout(function () {
                resendTimer = null;
                row.style.display = '';
                showCodeHelp();
            }, seconds * 1000);
        }

        /**
         * Hold a send button down for a cooldown, and show the wait where it can be seen.
         *
         * startResendCountdown() alone is not enough: the resend row is part of step two, which is
         * not on screen until a code has actually been sent. A per-address 429 on the first click
         * of a fresh page therefore ran a wait nobody could see, while the only visible control
         * had just been re-enabled. So disable the button that was actually pressed as well, and
         * hold the resend row back only when step two is up.
         */
        function startSendCooldown(btn, seconds) {
            var panel = document.getElementById('code-sent-panel');

            if (panel && panel.style.display !== 'none') {
                startResendCountdown(seconds);
            }

            if (!btn) return;

            // The mark, not just the disabled flag: .finally() runs after this and calls
            // setSendButtonIdle(), which would hand the button straight back and undo the cooldown.
            btn.dataset.cooldown = '1';
            btn.disabled = true;

            setTimeout(function () {
                delete btn.dataset.cooldown;
                if (!sendInFlight) btn.disabled = false;
            }, seconds * 1000);
        }

        /**
         * Busy state for the send button.
         *
         * sendVerificationCode() posts to an endpoint that sends the mail SYNCHRONOUSLY
         * (notifyNow, because the visitor is waiting for the code) and config/mail.php sets no
         * SMTP timeout, so this can block for seconds. It used to grey the button and change its
         * text, with no spinner and nothing announced - while the selfhost database test further
         * down this same file has had one all along.
         */
        function setSendButtonBusy(btn, label) {
            if (!btn) return;
            // Stash the label rather than hardcoding one: this serves both Continue and
            // "Resend code", and restoring the wrong one silently relabels whichever was pressed.
            if (btn.dataset.idleLabel === undefined) {
                btn.dataset.idleLabel = btn.innerHTML;
            }
            btn.disabled = true;
            btn.setAttribute('aria-busy', 'true');
            btn.innerHTML = '<svg class="inline-block w-4 h-4 me-2 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>'
                + (label || @json(__('messages.sending')));
        }

        function setSendButtonIdle(btn) {
            if (!btn) return;
            // A button serving out a 429 cooldown stays down; only its label comes back.
            if (btn.dataset.cooldown === undefined) {
                btn.disabled = false;
            }
            btn.removeAttribute('aria-busy');
            if (btn.dataset.idleLabel !== undefined) {
                btn.innerHTML = btn.dataset.idleLabel;
            }
        }

        /**
         * Remember, for this tab only, that a code went out and to where.
         *
         * The code step requires leaving the tab to read a mail. A reload, or a phone browser
         * discarding the tab while its owner is in Mail, used to land back on step one with no
         * way to type the code already in hand: the only move was another send, spending one of
         * the five per hour and leaving two codes in the inbox. sessionStorage is per tab and
         * survives exactly those two cases. Every access is guarded - private windows and blocked
         * site data make it throw - and the page works the same without it.
         */
        var CODE_SENT_KEY = 'es_signup_code_sent';
        // The code's own lifetime (signup_verification_code_expiry): past it, restoring the step
        // would only invite a code that is certain to be refused.
        var CODE_SENT_TTL_MS = 10 * 60 * 1000;

        function rememberCodeSent(email) {
            try {
                sessionStorage.setItem(CODE_SENT_KEY, JSON.stringify({ email: email, sentAt: Date.now() }));
            } catch (e) {}
        }

        function forgetCodeSent() {
            try {
                sessionStorage.removeItem(CODE_SENT_KEY);
            } catch (e) {}
        }

        function readCodeSent() {
            try {
                var stored = JSON.parse(sessionStorage.getItem(CODE_SENT_KEY) || 'null');
                if (stored && typeof stored.email === 'string' && stored.email
                    && typeof stored.sentAt === 'number' && Date.now() - stored.sentAt < CODE_SENT_TTL_MS) {
                    return stored;
                }
            } catch (e) {}

            return null;
        }

        /**
         * Say, where it can be seen, that a resend worked.
         *
         * The success used to go only to the screen-reader live region, so on screen the one
         * visible change was the countdown restarting - which reads as nothing having happened,
         * and invites another press.
         */
        var resentNoteTimer = null;
        function showResentNote() {
            var note = document.getElementById('code-resent-note');
            if (!note) return;

            note.style.display = 'block';
            if (resentNoteTimer) clearTimeout(resentNoteTimer);
            resentNoteTimer = setTimeout(function () {
                note.style.display = 'none';
                resentNoteTimer = null;
            }, 4000);
        }

        // Put the code step back on screen when there is a code to type.
        document.addEventListener('DOMContentLoaded', function() {
            var verificationCodeInput = document.getElementById('verification_code');
            var emailInput = document.getElementById('email');
            var hasErrors = @json($errors->any());
            if (!verificationCodeInput || !emailInput) return;

            // A form that came back from a full-page submit (no JavaScript fetch, or an old tab).
            if (verificationCodeInput.value || emailInput.readOnly || hasErrors) {
                showCodeSentState(emailInput.value);
                showCodeHelp();
                // Straight to the field they have to correct, rather than the name field above it.
                verificationCodeInput.focus();
                return;
            }

            // "Continue sign-up" in the code mail: ?email=<base64>&step=code. Only with an
            // address, or the step would open onto a code bound to nobody.
            if (@json(request()->query('step') === 'code') && emailInput.value) {
                showCodeSentState(emailInput.value);
                showCodeHelp();
                verificationCodeInput.focus();
                return;
            }

            // This tab sent a code a few minutes ago, then reloaded or was discarded. Only when
            // the address box agrees (or is empty), so a link for one address never reopens the
            // step for another.
            var stored = readCodeSent();
            if (stored && (!emailInput.value || emailInput.value.toLowerCase() === stored.email.toLowerCase())) {
                emailInput.value = stored.email;
                showCodeSentState(stored.email);
                showCodeHelp();
                verificationCodeInput.focus();
            }
        });

        /**
         * Ask for a code.
         *
         * Takes the button that was pressed, because there are two: the one beside the email field
         * and Resend inside the panel. It used to always grey #send-code-btn, so pressing Resend
         * spun a spinner on a different control and left Resend itself live.
         *
         * sendInFlight is the part that matters. The endpoint mails SYNCHRONOUSLY with no SMTP
         * timeout, so the button stays pressable for however long that takes, and the countdown
         * that is supposed to space these out only starts once a response comes back. Four taps on
         * a slow send meant four codes, and spent both the per-IP minute bucket and the per-address
         * hourly one at once.
         */
        var sendInFlight = false;

        /**
         * Which send the page currently belongs to.
         *
         * changeEmail() bumps this, so a response that arrives after the visitor has gone back to
         * step one knows it has been superseded and stays out of the way. Without it, a slow send
         * that lands after "use a different email" re-locks the ABANDONED address: the .then below
         * closes over the email it was called with, and showCodeSentState() would re-apply
         * readonly, re-open the panel and restart the countdown, while the input listener rewrote
         * every keystroke of the new address back to the old one.
         */
        var sendGeneration = 0;

        function sendVerificationCode(triggerBtn) {
                if (sendInFlight) return;

                var email = document.getElementById('email').value.trim();
                var sendCodeBtn = triggerBtn || document.getElementById('send-code-btn');
                var codeMessage = document.getElementById('code-message');
                var emailInput = document.getElementById('email');

                // Both checks, then one decision, so an unticked box and a bad address are reported
                // together rather than one per press. requireTerms() focuses the box, which sits
                // above the address, so it is the first thing to fix either way.
                var termsOk = requireTerms();

                var emailError = null;
                if (!email) {
                    emailError = @json(__('messages.please_enter_email_address'));
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    emailError = @json(__('messages.invalid_email_address'));
                }

                if (emailError) {
                    showCodeMessageError(emailError);
                    if (termsOk) emailInput.focus();
                }

                if (!termsOk || emailError) return;

                // A likely typo of a big provider stops the first press only, with the fix on
                // screen. A second press sends to what was typed: it is a suggestion, and
                // somebody at a real gmial.com must still be able to sign up.
                if (triggerBtn !== document.getElementById('resend-code-btn') && updateEmailSuggestion() && suggestionShownFor !== email) {
                    suggestionShownFor = email;
                    return;
                }

                var myGeneration = ++sendGeneration;

                // Disable button and show loading
                sendInFlight = true;
                // "Sending code" on Continue, whose own label does not say a mail is going out;
                // Resend already does, so it keeps the shorter default.
                setSendButtonBusy(sendCodeBtn, sendCodeBtn && sendCodeBtn.id === 'send-code-btn' ? @json(__('messages.sending_code')) : undefined);
                codeMessage.innerHTML = '';

                // The widget is interaction-only, so its token arrives on its own a moment after
                // load - or after a challenge - and a quick click can beat it. Wait for it under
                // the busy state instead of posting an empty token into a guaranteed "verification
                // failed". After the deadline, send anyway: the server's own Turnstile error then
                // comes back through the normal failure path below.
                waitForTurnstileToken(8000, function (turnstileToken) {
                    // Superseded by changeEmail() while waiting: that already reset the flag.
                    if (myGeneration !== sendGeneration) return;
                    postVerificationCode(email, turnstileToken, sendCodeBtn, codeMessage, myGeneration);
                });
            }

        function showCodeMessageError(message) {
            var codeMessage = document.getElementById('code-message');
            if (!codeMessage) return;
            codeMessage.innerHTML = '';
            var span = document.createElement('span');
            span.className = 'text-red-600 dark:text-red-400';
            span.textContent = message;
            codeMessage.appendChild(span);
        }

        /**
         * "Did you mean jane@gmail.com?" for a likely typo of a big provider's domain.
         *
         * A code sent to gmial.com is a code nobody receives, and the visitor only finds out after
         * waiting for it. Domains that are real providers in their own right, and a letter away
         * from one of the targets, are listed so they are never "corrected" (mail.com, ymail.com,
         * email.com and gmx.com are each one or two edits from gmail.com). Short domains get a
         * tighter bound, because at five letters two edits reach almost anything.
         */
        var SUGGEST_DOMAINS = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'icloud.com', 'aol.com', 'live.com', 'proton.me', 'protonmail.com', 'googlemail.com'];
        var REAL_DOMAINS = ['mail.com', 'email.com', 'ymail.com', 'gmx.com', 'gmx.net', 'gmx.de', 'mac.com', 'me.com', 'msn.com', 'aim.com', 'pm.me', 'mail.ru', 'web.de', 'yandex.ru', 'rocketmail.com', 'hotmail.co.uk', 'live.co.uk', 'yahoo.co.uk', 'outlook.co.uk', 'hotmail.fr', 'live.fr', 'outlook.fr', 'hotmail.de', 'hotmail.it', 'hotmail.es', 'yahoo.fr', 'yahoo.de', 'yahoo.es', 'yahoo.it'];
        var suggestionShownFor = null;

        function editDistance(a, b) {
            var prev = [], cur, i, j;
            for (j = 0; j <= b.length; j++) prev[j] = j;
            for (i = 1; i <= a.length; i++) {
                cur = [i];
                for (j = 1; j <= b.length; j++) {
                    cur[j] = Math.min(prev[j] + 1, cur[j - 1] + 1, prev[j - 1] + (a[i - 1] === b[j - 1] ? 0 : 1));
                }
                prev = cur;
            }
            return prev[b.length];
        }

        function suggestEmail(email) {
            var at = (email || '').lastIndexOf('@');
            if (at < 1) return null;
            var local = email.slice(0, at);
            var domain = email.slice(at + 1).toLowerCase();
            if (!domain || SUGGEST_DOMAINS.indexOf(domain) !== -1 || REAL_DOMAINS.indexOf(domain) !== -1) return null;

            var best = null, bestDistance = Infinity;
            SUGGEST_DOMAINS.forEach(function (candidate) {
                var d = editDistance(domain, candidate);
                if (d < bestDistance) {
                    bestDistance = d;
                    best = candidate;
                }
            });
            if (best && bestDistance <= (domain.length >= 8 ? 2 : 1)) return local + '@' + best;

            // Any other domain with a fumbled .com.
            var fixed = domain.replace(/\.(con|cmo|cpm|xom|vom|comm|coom)$/, '.com');
            return fixed !== domain ? local + '@' + fixed : null;
        }

        // Shows or clears the suggestion for what is in the box; returns whether one is showing.
        function updateEmailSuggestion() {
            var input = document.getElementById('email');
            var row = document.getElementById('email-suggestion');
            if (!input || !row) return false;

            var suggestion = input.readOnly ? null : suggestEmail(input.value.trim());
            row.innerHTML = '';
            if (!suggestion) {
                row.style.display = 'none';
                return false;
            }

            // Built from the translated sentence around a button, never through innerHTML: the
            // address is typed by the visitor.
            var parts = @json(__('messages.did_you_mean', ['email' => '__EMAIL__'])).split('__EMAIL__');
            var fix = document.createElement('button');
            fix.type = 'button';
            fix.className = 'font-semibold text-[var(--brand-blue)] hover:underline focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] rounded';
            fix.textContent = suggestion;
            fix.setAttribute('dir', 'ltr');
            fix.addEventListener('click', function () {
                input.value = suggestion;
                suggestionShownFor = null;
                updateEmailSuggestion();
                input.focus();
            });
            row.appendChild(document.createTextNode(parts[0] || ''));
            row.appendChild(fix);
            row.appendChild(document.createTextNode(parts[1] || ''));
            row.style.display = 'block';
            return true;
        }

        /**
         * Paint the six code boxes from the one real input lying over them.
         *
         * Each box shows its digit; the next one to fill carries the focus ring (and a caret while
         * empty), or the last box once all six are in. A digit that has just arrived pops in.
         * Called from every place the value changes, including the ones that set it in script -
         * an input event does not fire for those.
         */
        var lastCodeValue = '';
        function renderCodeSlots() {
            var input = document.getElementById('verification_code');
            var boxes = document.getElementById('code-boxes');
            if (!input || !boxes) return;

            var value = input.value;
            var focused = document.activeElement === input;
            var active = Math.min(value.length, 5);

            boxes.querySelectorAll('[data-code-slot]').forEach(function (slot, i) {
                var digit = value.charAt(i);
                slot.querySelector('[data-digit]').textContent = digit;
                slot.classList.toggle('is-filled', digit !== '');
                slot.classList.toggle('is-active', focused && i === active);

                slot.classList.remove('is-new');
                if (digit && i >= lastCodeValue.length) {
                    void slot.offsetWidth;
                    slot.classList.add('is-new');
                }
            });

            lastCodeValue = value;
        }

        function setCodeBoxesState(name, on) {
            var boxes = document.getElementById('code-boxes');
            if (boxes) boxes.classList.toggle(name, on);
        }

        /**
         * Keep the caret after the last digit.
         *
         * Clicking the third box of a four-digit code would otherwise put the caret there, and the
         * next digit would push the rest along - the "my digits shifted" confusion. With the
         * caret pinned to the end, a click types into the next empty box and Backspace always
         * takes the last digit, which is how the best one-time-code fields behave.
         */
        function keepCodeCaretAtEnd() {
            var input = document.getElementById('verification_code');
            if (!input || document.activeElement !== input) return;

            var end = input.value.length;
            try {
                if (input.selectionStart !== end || input.selectionEnd !== end) {
                    input.setSelectionRange(end, end);
                }
            } catch (e) {}
        }

        /**
         * The consent box sits above both ways in and gates both.
         *
         * Google is a plain link and the code request is a fetch, so neither is covered by the
         * form's own constraint validation - this is the check for them. Returns false and says
         * why, at the box, when it is not ticked.
         */
        function requireTerms() {
            var terms = document.getElementById('terms');
            var error = document.getElementById('terms-error');
            if (!terms || terms.checked) return true;

            showTermsField();
            if (error) error.style.display = 'block';
            terms.focus();
            return false;
        }

        // showCodeSentState() folds a ticked box away; anything that asks about it again has to
        // put it back first, or the error and the focus land on something nobody can see.
        function showTermsField() {
            var field = document.getElementById('terms-field');
            if (field) field.style.display = '';
        }

        function readTurnstileToken() {
            var turnstileInput = document.querySelector('input[name="cf-turnstile-response"]');
            return turnstileInput ? turnstileInput.value : '';
        }

        function waitForTurnstileToken(timeoutMs, done) {
            var turnstileEnabled = !!document.getElementById('turnstile-widget');
            var token = readTurnstileToken();
            if (!turnstileEnabled || token) {
                done(token);
                return;
            }

            // The short deadline only covers the invisible pass. While a challenge is on screen a
            // person is solving it, and giving up on them posts an empty token, earns "verification
            // failed" and resets the widget - so they solve it twice. Wait for them, within reason.
            var waited = 0;
            var waitedInteractive = 0;
            var poll = setInterval(function () {
                if (turnstileInteractive) {
                    waitedInteractive += 250;
                } else {
                    waited += 250;
                }
                token = readTurnstileToken();
                if (token || waited >= timeoutMs || waitedInteractive >= 120000) {
                    clearInterval(poll);
                    done(token);
                }
            }, 250);
        }

        function postVerificationCode(email, turnstileToken, sendCodeBtn, codeMessage, myGeneration) {
                // Get honeypot value
                var honeypotValue = '';
                var honeypotInput = document.querySelector('input[name="website"]');
                if (honeypotInput) {
                    honeypotValue = honeypotInput.value;
                }

                fetch('{{ route('sign_up.send_code') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        email: email,
                        'cf-turnstile-response': turnstileToken,
                        website: honeypotValue
                    })
                })
                .then(response => {
                    // Superseded by a changeEmail() while this was in flight: touch nothing. The
                    // reset still happens, in the .finally() below.
                    if (myGeneration !== sendGeneration) return;

                    return response.json().then(data => {
                        // Check if response is successful
                        if (response.ok && data.success) {
                            // The panel carries the address, the expiry and both escape hatches, so
                            // the status line says nothing VISIBLE - but it is the page's only live
                            // region (role="status"), and the panel appears via display:block,
                            // which no screen reader announces. Emptying it meant the one event
                            // this region exists for was silent. sr-only keeps that fixed without
                            // repeating "we sent a code" next to a panel that already says it.
                            codeMessage.innerHTML = '';
                            var announcement = document.createElement('span');
                            announcement.className = 'sr-only';
                            announcement.textContent = data.message || '';
                            codeMessage.appendChild(announcement);
                            showCodeSentState(email);
                            startResendCountdown(30);
                            rememberCodeSent(email);
                            if (sendCodeBtn && sendCodeBtn.id === 'resend-code-btn') {
                                showResentNote();
                            }

                            // Pre-fill a known name (a stub account), but focus the field the
                            // visitor actually has to fill: they have just been sent a code.
                            var nameInput = document.getElementById('name');
                            if (nameInput && data.name) {
                                nameInput.value = data.name;
                            }
                            var codeInput = document.getElementById('verification_code');
                            if (codeInput) {
                                codeInput.focus();
                                codeInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                            // Reset Turnstile widget so user gets a fresh token for form submission
                            if (typeof turnstile !== 'undefined' && turnstileWidgetId !== null) {
                                turnstile.reset(turnstileWidgetId);
                            }
                        } else {
                            // Handle validation errors or other errors
                            var errorMessage = data.message || @json(__('messages.error_sending_code'));

                            // A 429 here is the route's per-IP bucket, whose body is the
                            // framework's untranslated "Too Many Requests" - rendered verbatim in
                            // red under the email field, in every one of the 12 locales. Retry-After
                            // is on the response and was being discarded.
                            // Two different 429s reach here and they mean different things. The
                            // route throttle (5/min/IP) sends Retry-After; the per-address counter
                            // in RegisteredUserController is 5 per HOUR and sends its own message.
                            // Overriding both with "wait a minute" told someone who had exhausted
                            // the hourly limit to try again in sixty seconds - and then again, and
                            // again, for up to an hour. Only override when the header is there.
                            if (response.status === 429) {
                                var retryAfter = parseInt(response.headers.get('Retry-After') || '0', 10);

                                // The header-bearing one is the per-IP route throttle, and only it
                                // knows how long to wait, so only it may say "wait a minute". The
                                // other is the per-address 5-per-HOUR limit, whose own message is
                                // already correct - overriding it told somebody with an hour to
                                // wait to try again in sixty seconds, and again, for an hour.
                                if (retryAfter > 0) {
                                    errorMessage = @json(__('messages.too_many_attempts'));
                                }

                                // But BOTH need a cooldown. Without one the visitor can hammer the
                                // button, and each attempt is a live request that spends the per-IP
                                // bucket on top of the limit they have already hit.
                                startSendCooldown(sendCodeBtn, retryAfter > 0 ? retryAfter : 60);
                            }

                            // Check for Laravel validation errors (422 status)
                            if (data.errors && data.errors.email) {
                                errorMessage = Array.isArray(data.errors.email) ? data.errors.email[0] : data.errors.email;
                            }
                            // Check for Turnstile validation errors
                            if (data.errors && data.errors['cf-turnstile-response']) {
                                errorMessage = Array.isArray(data.errors['cf-turnstile-response']) ? data.errors['cf-turnstile-response'][0] : data.errors['cf-turnstile-response'];
                            }

                            var errorSpan = document.createElement('span');
                            errorSpan.className = 'text-red-600 dark:text-red-400';
                            errorSpan.textContent = errorMessage;
                            codeMessage.innerHTML = '';
                            codeMessage.appendChild(errorSpan);

                            // The address already has an account: the way forward is to log in, so
                            // offer that right there, carrying the address, instead of a red line
                            // with nothing to do next.
                            if (data.reason === 'registered') {
                                var loginUrl = new URL(@json(route('login')), window.location.origin);
                                loginUrl.searchParams.set('email', email);
                                var loginLink = document.createElement('a');
                                loginLink.href = loginUrl.toString();
                                loginLink.className = 'ms-1 font-medium text-[var(--brand-blue)] hover:underline';
                                loginLink.textContent = @json(__('messages.log_in'));
                                codeMessage.appendChild(loginLink);
                            }

                            // Reset Turnstile widget on failure
                            if (typeof turnstile !== 'undefined' && turnstileWidgetId !== null) {
                                turnstile.reset(turnstileWidgetId);
                            }
                        }
                    }).catch(function(jsonError) {
                        // If JSON parsing fails, show generic error
                        var errorSpan = document.createElement('span');
                        errorSpan.className = 'text-red-600 dark:text-red-400';
                        errorSpan.textContent = @json(__('messages.error_sending_code'));
                        codeMessage.innerHTML = '';
                        codeMessage.appendChild(errorSpan);
                        // Reset Turnstile widget on failure
                        if (typeof turnstile !== 'undefined' && turnstileWidgetId !== null) {
                            turnstile.reset(turnstileWidgetId);
                        }
                    });
                })
                .catch(error => {
                    if (myGeneration !== sendGeneration) return;

                    codeMessage.innerHTML = '<span class="text-red-600 dark:text-red-400">' + @json(__('messages.error_sending_code')) + '</span>';
                    // Reset Turnstile widget on failure
                    if (typeof turnstile !== 'undefined' && turnstileWidgetId !== null) {
                        turnstile.reset(turnstileWidgetId);
                    }
                })
                .finally(() => {
                    // Guarded by the SAME counter, and that guard is load-bearing. Without it:
                    // send A, change email, send B - and A's late response clears the flag that now
                    // belongs to B, letting a third send start while B is still out. With it, A
                    // no-ops entirely and B keeps the lock.
                    //
                    // In .finally() rather than at the top of .then() so the flag survives until
                    // the body has been parsed; clearing it at headers-received left a window
                    // where the button was live but the success handler had not run yet.
                    if (myGeneration !== sendGeneration) return;

                    sendInFlight = false;
                    setSendButtonIdle(sendCodeBtn);
                });
            }

        // Attach event listener to send code button
        document.addEventListener('DOMContentLoaded', function() {
            var sendCodeBtn = document.getElementById('send-code-btn');
            if (sendCodeBtn) {
                // Ensure button type is button, not submit
                sendCodeBtn.setAttribute('type', 'button');
                // Remove any form association
                sendCodeBtn.setAttribute('form', '');
                // Add additional click handler as backup
                sendCodeBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    sendVerificationCode(this);
                    return false;
                }, true); // Use capture phase
            }

            // Prevent form submission when send code button is clicked
            var form = document.querySelector('form[action="{{ route('sign_up') }}"]');
            if (form) {
                form.addEventListener('submit', function(e) {
                    var submitter = e.submitter || document.activeElement;
                    if (submitter && (submitter.id === 'send-code-btn' || submitter.closest('#send-code-btn'))) {
                        e.preventDefault();
                        e.stopPropagation();
                        e.stopImmediatePropagation();
                        return false;
                    }
                }, true); // Use capture phase
            }

            // Allow Enter key in email field to send code (only if not locked)
            var emailInput = document.getElementById('email');
            if (emailInput) {
                emailInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter' && !this.readOnly) {
                        e.preventDefault();
                        sendVerificationCode();
                    }
                });
                // Prevent changes to email field if it's locked
                emailInput.addEventListener('input', function(e) {
                    if (lockedEmail && this.value.toLowerCase() !== lockedEmail) {
                        this.value = lockedEmail;
                    }
                });

                // The typo suggestion, once typing pauses. Not on blur: pressing Continue blurs
                // the field first, and a line appearing then moves the button out from under the
                // pointer between mousedown and mouseup, so the click never lands.
                var suggestTimer = null;
                emailInput.addEventListener('input', function () {
                    // A "please enter an address" left over from the last press is about a value
                    // that no longer exists.
                    if (!lockedEmail) {
                        var codeMessage = document.getElementById('code-message');
                        if (codeMessage) codeMessage.innerHTML = '';
                    }
                    if (suggestTimer) clearTimeout(suggestTimer);
                    suggestTimer = setTimeout(updateEmailSuggestion, 600);
                });
            }

            // Format verification code input (numbers only)
            var codeInput = document.getElementById('verification_code');
            if (codeInput) {
                codeInput.addEventListener('input', function(e) {
                    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
                    // A refused code's red boxes are about the old attempt, not this keystroke, and a
                    // green "Verified" is about the code that was there before this edit.
                    setCodeBoxesState('is-invalid', false);
                    var codeError = document.querySelector('[data-signup-error="verification_code"]');
                    if (codeError) codeError.remove();
                    if (this.value !== codeCheckedValue) markCodeUnchecked();
                    // Painted before the submit starts, so the sixth digit is visibly in its box.
                    renderCodeSlots();
                    maybeAutoSubmit(this);
                });

                ['focus', 'blur'].forEach(function (type) {
                    codeInput.addEventListener(type, function () {
                        renderCodeSlots();
                        if (type === 'focus') {
                            // After the click has placed the caret, not before.
                            setTimeout(keepCodeCaretAtEnd, 0);
                        }
                    });
                });
                ['click', 'keyup', 'select'].forEach(function (type) {
                    codeInput.addEventListener(type, keepCodeCaretAtEnd);
                });
                // Moving the caret into the middle of the code would type into the middle of it.
                codeInput.addEventListener('keydown', function (e) {
                    if (['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'].indexOf(e.key) !== -1) {
                        e.preventDefault();
                    }
                });

                renderCodeSlots();

                // Most people paste the whole line out of the email rather than the six digits.
                // Handled here because the field carries no maxlength any more (it truncated the
                // paste before this could run), so without a slice a long paste would stick.
                codeInput.addEventListener('paste', function(e) {
                    var clipboard = (e.clipboardData || window.clipboardData);
                    if (!clipboard) return;

                    var digits = (clipboard.getData('text') || '').replace(/[^0-9]/g, '').slice(0, 6);
                    if (digits.length === 6) {
                        e.preventDefault();
                        this.value = digits;
                        setCodeBoxesState('is-invalid', false);
                        renderCodeSlots();
                        maybeAutoSubmit(this);
                    }
                });
            }

            // "At least 8 characters" ticks green once it is true, and back if it stops being.
            var passwordInput = document.getElementById('password');
            var passwordHelp = document.getElementById('password-help');
            if (passwordInput && passwordHelp) {
                passwordInput.addEventListener('input', function () {
                    var met = this.value.length >= 8;
                    passwordHelp.classList.toggle('text-green-700', met);
                    passwordHelp.classList.toggle('dark:text-green-400', met);
                    passwordHelp.classList.toggle('text-gray-500', !met);
                    passwordHelp.classList.toggle('dark:text-gray-400', !met);
                    var icon = passwordHelp.querySelector('[data-met-icon]');
                    if (icon) icon.style.display = met ? '' : 'none';
                });
            }

            var resendBtn = document.getElementById('resend-code-btn');
            if (resendBtn) {
                resendBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    // Reads the email input, which is locked to the address the first code went to,
                    // so this is a resend rather than a new request. Passing `this` puts the busy
                    // state on the button that was actually pressed.
                    sendVerificationCode(this);
                });
            }

            // Carry the address to /login rather than making somebody who has just typed it, and
            // been told it already has an account, type it a second time. login.blade.php reads
            // request('email') into its own field.
            var alreadyRegisteredLink = document.getElementById('already-registered-link');
            if (alreadyRegisteredLink) {
                alreadyRegisteredLink.addEventListener('click', function() {
                    var typed = document.getElementById('email');
                    if (typed && typed.value && typed.value.indexOf('@') > 0) {
                        var url = new URL(this.href, window.location.origin);
                        url.searchParams.set('email', typed.value);
                        this.href = url.toString();
                    }
                });
            }

            // Selected by what it needs rather than by id: the script never reaches for the Google
            // section itself, which is how it used to get hidden (see SignupCodeStepTest).
            // auxclick too: a middle-click opens the link in a new tab without firing `click`.
            document.querySelectorAll('[data-requires-terms]').forEach(function (section) {
                ['click', 'auxclick'].forEach(function (type) {
                    section.addEventListener(type, function (e) {
                        if (e.target.closest('a') && !requireTerms()) {
                            e.preventDefault();
                        }
                    });
                });
            });

            var termsBox = document.getElementById('terms');
            if (termsBox) {
                termsBox.addEventListener('change', function () {
                    var error = document.getElementById('terms-error');
                    if (error && this.checked) error.style.display = 'none';
                });
            }

            var changeEmailBtn = document.getElementById('change-email-btn');
            if (changeEmailBtn) {
                changeEmailBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    changeEmail();
                });
            }

            // Every submit of the hosted form - the button, Enter, and the sixth-digit
            // auto-submit's requestSubmit() - goes through fetch. See submitSignupForm().
            var signupForm = document.querySelector('form[action="{{ route('sign_up') }}"]');
            if (signupForm) {
                signupForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    submitSignupForm(signupForm);
                });
            }
        });

        /**
         * Submit the form without leaving the page.
         *
         * A full-page POST that failed reloaded the form, and browsers never repopulate
         * type="password": one mistyped digit of the code cost the visitor their password too,
         * on top of the code. With fetch, store() answers a rejection as 422 JSON and it is shown
         * under the field it is about, with everything typed still in place. Success comes back
         * as {redirect}, which the page follows.
         */
        var signupSubmitting = false;
        function submitSignupForm(form) {
            if (signupSubmitting) return;
            // The disabled button already stops a click and Enter's implicit submit; this stops
            // the sixth-digit auto-submit, whose requestSubmit() does not go through the button.
            if (!codeSent) return;
            signupSubmitting = true;

            clearSignupErrors();
            var submitBtn = document.getElementById('create-account-btn');
            setSendButtonBusy(submitBtn, @json(__('messages.create_account')));
            // The sixth digit visibly did something while the request is out.
            setCodeBoxesState('is-busy', true);

            // store() validates Turnstile, and a token is single-use: a successful send spent the
            // previous one and a rejected submit spends this one. Wait for the fresh token the
            // reset produces rather than posting an empty field into a guaranteed failure.
            waitForTurnstileToken(8000, function () {
                var succeeded = false;

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: new FormData(form)
                })
                .then(function (response) {
                    // The route's `guest` middleware answers a visitor who is already signed in
                    // (finished with Google in another tab, say) with a redirect, which fetch
                    // follows to an HTML page. Go where it pointed instead of reporting an error.
                    if (response.redirected) {
                        succeeded = true;
                        forgetCodeSent();
                        window.location.assign(response.url);
                        return;
                    }

                    return response.json().catch(function () { return {}; }).then(function (data) {
                        if (response.ok && data.redirect) {
                            succeeded = true;
                            forgetCodeSent();
                            window.location.assign(data.redirect);
                            return;
                        }

                        if (response.status === 422 && data.errors) {
                            showSignupErrors(data.errors);
                        } else if (response.status === 429) {
                            showGeneralSignupError(@json(__('messages.too_many_attempts')));
                        } else if (response.status === 419) {
                            // An expired session or CSRF token fails every retry the same way, so
                            // say to reload. The code step survives the reload (sessionStorage).
                            showGeneralSignupError(@json(__('messages.signup_page_expired')));
                        } else {
                            showGeneralSignupError(@json(__('messages.something_went_wrong')));
                        }
                    });
                })
                .catch(function () {
                    showGeneralSignupError(@json(__('messages.something_went_wrong')));
                })
                .finally(function () {
                    // Left busy on success: the page is navigating away.
                    if (succeeded) return;

                    signupSubmitting = false;
                    setCodeBoxesState('is-busy', false);
                    setSendButtonIdle(submitBtn);
                    // setSendButtonIdle() re-enables, and "Use a different email" may have been
                    // pressed while this was in flight.
                    if (submitBtn) submitBtn.disabled = !codeSent;
                    if (typeof turnstile !== 'undefined' && turnstileWidgetId !== null) {
                        turnstile.reset(turnstileWidgetId);
                    }
                });
            });
        }

        // Where each field's message goes. The email box is folded away in the code step, so its
        // errors go to the status line under it, which stays on screen in both steps.
        var SIGNUP_ERROR_ANCHORS = {
            verification_code: 'verification-code-field',
            name: 'name-field',
            password: 'password-field'
        };

        function clearSignupErrors() {
            document.querySelectorAll('[data-signup-error]').forEach(function (el) { el.remove(); });

            var general = document.getElementById('signup-form-error');
            if (general) general.style.display = 'none';

            var termsError = document.getElementById('terms-error');
            if (termsError) termsError.style.display = 'none';

            // The summary a full-page submit rendered at the top is about an earlier attempt.
            var serverSummary = document.getElementById('signup-server-errors');
            if (serverSummary) serverSummary.remove();
        }

        function showGeneralSignupError(message) {
            var general = document.getElementById('signup-form-error');
            var text = document.getElementById('signup-form-error-text');
            if (!general || !text) return;

            text.textContent = message;
            general.style.display = 'block';
        }

        function showSignupErrors(errors) {
            var firstField = null;

            Object.keys(errors).forEach(function (field) {
                var message = Array.isArray(errors[field]) ? errors[field][0] : errors[field];

                if (field === 'terms') {
                    var termsError = document.getElementById('terms-error');
                    showTermsField();
                    if (termsError) {
                        termsError.textContent = message;
                        termsError.style.display = 'block';
                    }
                    firstField = firstField || document.getElementById('terms');
                    return;
                }

                if (field === 'email') {
                    var codeMessage = document.getElementById('code-message');
                    if (codeMessage) {
                        codeMessage.innerHTML = '';
                        var span = document.createElement('span');
                        span.className = 'text-red-600 dark:text-red-400';
                        span.textContent = message;
                        codeMessage.appendChild(span);
                    }
                    return;
                }

                var anchor = SIGNUP_ERROR_ANCHORS[field] ? document.getElementById(SIGNUP_ERROR_ANCHORS[field]) : null;
                if (!anchor) {
                    showGeneralSignupError(message);
                    return;
                }

                var p = document.createElement('p');
                p.setAttribute('data-signup-error', field);
                p.setAttribute('role', 'alert');
                p.className = 'mt-2 text-sm text-red-600 dark:text-red-400';
                p.textContent = message;
                (anchor.querySelector('[data-error-slot]') || anchor).appendChild(p);

                firstField = firstField || document.getElementById(field);
            });

            // A refused code is cleared so the next six digits auto-submit again, the same as the
            // first six did. Name and password stay exactly as typed.
            if (errors.verification_code) {
                var codeInput = document.getElementById('verification_code');
                if (codeInput) {
                    codeInput.value = '';
                    firstField = codeInput;
                }
                // Red and a single shake, until the next digit.
                markCodeUnchecked();
                setCodeBoxesState('is-invalid', true);
                renderCodeSlots();
                autoSubmitted = false;
                autoSubmitReported = false;
            }

            if (firstField && typeof firstField.focus === 'function') {
                firstField.focus();
            }
        }

        /**
         * Ask the server whether the six digits are right, without using the code up.
         *
         * Green boxes and "Verified" when they are, then on to name and password exactly as
         * before; red, cleared boxes and the reason when they are not. Anything else - a network
         * failure, a throttle, a server error - lets the flow carry on as it did before this
         * existed: store() checks the code in any case, so the early answer is a courtesy.
         */
        var codeCheckedValue = null;
        var codeCheckInFlight = false;
        function checkCode(codeInput) {
            if (codeCheckInFlight) return;
            codeCheckInFlight = true;

            var value = codeInput.value;
            var carryOn = function () {
                codeCheckedValue = value;
                maybeAutoSubmit(codeInput);
            };

            setCodeBoxesState('is-busy', true);

            fetch(@json(route('sign_up.check_code')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ email: document.getElementById('email').value, verification_code: value })
            })
            .then(function (response) {
                return response.json().catch(function () { return {}; }).then(function (data) {
                    return { ok: response.ok, data: data };
                });
            })
            .then(function (result) {
                codeCheckInFlight = false;
                setCodeBoxesState('is-busy', false);
                // Typed on while the answer was out: it is about digits no longer in the boxes.
                if (codeInput.value !== value) return;

                if (result.ok && result.data.valid === true) {
                    setCodeBoxesState('is-valid', true);
                    setCodeHelpRows(false);
                    var note = document.getElementById('code-verified-note');
                    if (note) note.style.display = 'flex';
                    carryOn();
                } else if (result.ok && result.data.valid === false) {
                    clearSignupErrors();
                    showSignupErrors({ verification_code: [result.data.message || @json(__('messages.code_invalid'))] });
                    // Too many wrong tries: this code is done, so offer a new one at once.
                    if (result.data.expired) {
                        if (resendTimer) {
                            clearTimeout(resendTimer);
                            resendTimer = null;
                        }
                        setCodeHelpRows(true);
                    }
                } else {
                    carryOn();
                }
            })
            .catch(function () {
                codeCheckInFlight = false;
                setCodeBoxesState('is-busy', false);
                if (codeInput.value === value) carryOn();
            });
        }

        function markCodeUnchecked() {
            codeCheckedValue = null;
            setCodeBoxesState('is-valid', false);
            var note = document.getElementById('code-verified-note');
            if (note) note.style.display = 'none';
            // Back to waiting on a code: the resend row returns, unless its wait is still running.
            var row = document.getElementById('code-resend-row');
            if (!resendTimer && row && row.style.display === 'none') setCodeHelpRows(true);
        }

        // The resend row and the where-to-look note, which mean nothing once a code is accepted.
        function setCodeHelpRows(show) {
            var row = document.getElementById('code-resend-row');
            if (row) row.style.display = show ? '' : 'none';
            if (show) {
                showCodeHelp();
            } else {
                var help = document.getElementById('code-help-note');
                if (help) help.style.display = 'none';
            }
        }

        /**
         * Submit once the sixth digit lands, so the code step ends where it started.
         *
         * Gated on the consent box being ticked: an auto-submit that trips `terms => accepted`
         * would report a failure the visitor did not cause. requestSubmit() runs constraint
         * validation, and revealSignupFields() has already armed `required` on everything by the
         * time a code can be entered, so the form either submits or reports which field is missing.
         */
        var autoSubmitted = false;
        var autoSubmitReported = false;
        function maybeAutoSubmit(codeInput) {
            if (codeInput.value.length !== 6) return;

            // Once only. This runs on every `input` event, and the handler slices to 6, so typing a
            // seventh digit leaves the value at 6 and fires again. Each attempt calls
            // requestSubmit(), which on an incomplete form focuses the offending field and pops its
            // bubble - yanking the caret out of the code box, repeatedly, while somebody is typing
            // in it. Worst after a failed submit, where the page reloads with the code restored and
            // the terms box re-checked but the password field emptied by the browser.
            if (autoSubmitted) return;

            // Check the code before anything else, so a wrong one is reported while it is still
            // what the visitor is looking at, not after a name and a password. checkCode() comes
            // back here once the answer is in.
            if (codeCheckedValue !== codeInput.value) {
                checkCode(codeInput);
                return;
            }

            // Unticked is normal only on a page that was restored into the code step (a reload, or
            // the "Continue sign-up" link): the box was ticked before the code was sent, but this is a fresh page. Say so, once, instead of the sixth digit doing
            // nothing at all. Consent stays an explicit tick - it is never set for them.
            var terms = document.getElementById('terms');
            if (terms && !terms.checked) {
                if (!autoSubmitReported) {
                    autoSubmitReported = true;
                    requireTerms();
                }
                return;
            }

            var form = codeInput.form;
            if (!form) return;

            // The code now comes BEFORE Name and Password, so an incomplete form here is the normal
            // case, not a mistake: move on to the first field still to fill, quietly. Not
            // reportValidity(), whose "please fill in this field" bubble would scold somebody for
            // doing things in the order the page laid out. Once only, so a seventh keystroke does
            // not yank the caret out of the code box again.
            if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
                if (!autoSubmitReported) {
                    autoSubmitReported = true;
                    var next = Array.prototype.find.call(form.elements, function (el) {
                        return el !== codeInput && el.willValidate && !el.checkValidity() && el.offsetParent !== null;
                    });
                    if (next) next.focus();
                }
                return;
            }

            autoSubmitted = true;

            // store() validates Turnstile too, and a successful send reset the widget, so the token
            // can be momentarily empty (or behind a challenge) when a fast sixth digit lands.
            waitForTurnstileToken(8000, function () {
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    // Safari below 16 has no requestSubmit(). Without this the sixth digit did
                    // nothing at all, for ever, with no message. checkValidity() above has already
                    // run, so skipping the submit event's validation is not a hole here. Straight
                    // to the fetch path rather than form.submit(), which would bypass the submit
                    // listener and reload the page - emptying the password on a wrong code.
                    submitSignupForm(form);
                }
            });
        }
        @endif

        @if (! config('app.hosted'))

            document.addEventListener('DOMContentLoaded', function() {
                var testConnectionBtn = document.getElementById('test-connection-btn');
                if (testConnectionBtn) {
                    testConnectionBtn.addEventListener('click', function() {
                        testConnection();
                    });
                }
            });

            function testConnection() {
                var host = document.getElementById('database_host').value;
                var port = document.getElementById('database_port').value;
                var database = document.getElementById('database_name').value;
                var username = document.getElementById('database_username').value;
                var password = document.getElementById('database_password').value;

                // Show loading state
                document.getElementById('test-result').innerHTML = '<span class="text-gray-500 dark:text-gray-400"><svg class="inline-block w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> {{ __('messages.testing') }}...</span>';

                fetch('{{ route('app.test_database') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        host: host,
                        port: port, 
                        database: database,
                        username: username,
                        password: password
                    })
                })
                .then(response => {
                    if (!response.ok) throw new Error('Request failed');
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        if (data.has_existing_user) {
                            document.getElementById('test-result').innerHTML = '<span class="text-amber-600 dark:text-amber-400"><svg class="inline-block w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg> {{ __('messages.database_already_initialized') }}</span>';
                            document.querySelector('button[type="submit"]').disabled = true;
                            @if (selfhost_needs_setup())
                            var regFields = document.getElementById('registration-fields');
                            if (regFields) regFields.style.display = 'none';
                            @endif
                        } else {
                            document.getElementById('test-result').innerHTML = '<span class="text-green-600 dark:text-green-400"><svg class="inline-block w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> {{ __('messages.connection_successful') }}!</span>';
                            // Enable register button on successful connection
                            document.querySelector('button[type="submit"]').disabled = false;
                            @if (selfhost_needs_setup())
                            var regFields = document.getElementById('registration-fields');
                            if (regFields) regFields.style.display = 'block';
                            @endif
                        }
                    } else {
                        var testResult = document.getElementById('test-result');
                        testResult.innerHTML = '<span class="text-red-600 dark:text-red-400"><svg class="inline-block w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg> <span id="test-error-text"></span></span>';
                        document.getElementById('test-error-text').textContent = data.error;
                        // Disable register button on failed connection
                        document.querySelector('button[type="submit"]').disabled = true;
                        @if (selfhost_needs_setup())
                        var regFields = document.getElementById('registration-fields');
                        if (regFields) regFields.style.display = 'none';
                        @endif
                    }
                })
                .catch(error => {
                    document.getElementById('test-result').innerHTML = '<span class="text-red-600 dark:text-red-400"><svg class="inline-block w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg> {{ __('messages.error_testing_connection') }}</span>';
                    // Disable register button on error
                    document.querySelector('button[type="submit"]').disabled = true;
                    @if (selfhost_needs_setup())
                    var regFields = document.getElementById('registration-fields');
                    if (regFields) regFields.style.display = 'none';
                    @endif
                });
            }

        @endif


        </script>

        <style {!! nonce_attr() !!}>
            /* Scoped: this used to be `form button`, which also caught the password reveal
               toggle - an absolutely positioned button inside the field - so the eye icon painted
               about 88px in from the edge and its hit area covered the end of the input. */
            #send-code-btn, form button[type="submit"] {
                min-width: 100px;
            }
            button:disabled {
                opacity: 0.5;
                cursor: not-allowed;
            }

            /* Step one's address, folded away in step two but still RENDERED, so a password manager
               can pair it with the new password. See showCodeSentState(). */
            .signup-visually-hidden {
                position: absolute;
                width: 1px;
                height: 1px;
                margin: -1px;
                padding: 0;
                overflow: hidden;
                clip: rect(0, 0, 0, 0);
                white-space: nowrap;
                border: 0;
            }

            /* The six code boxes. Surface and border follow the other inputs through the palette
               classes on each slot; states key off classes renderCodeSlots() sets. */
            .code-slots {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
            }
            .code-slots-gap {
                flex: 0 0 0.25rem;
            }
            .code-slot {
                position: relative;
                display: flex;
                flex: 1 1 0;
                align-items: center;
                justify-content: center;
                min-width: 0;
                height: 3.5rem;
                border-width: 1px;
                border-radius: 0.75rem;
                font-size: 1.5rem;
                font-weight: 600;
                font-variant-numeric: tabular-nums;
                color: rgb(var(--ap-ink));
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
                transition: border-color 150ms, box-shadow 150ms, opacity 150ms;
            }
            .code-slot.is-filled {
                border-color: rgb(var(--ap-ink-4));
            }
            .code-slot.is-active {
                border-color: var(--brand-blue);
                box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand-blue) 25%, transparent);
            }
            .code-slot [data-caret] {
                display: none;
                position: absolute;
                width: 2px;
                height: 1.5rem;
                border-radius: 1px;
                background-color: var(--brand-blue);
                animation: code-caret 1s step-end infinite;
            }
            .code-slot.is-active:not(.is-filled) [data-caret] {
                display: block;
            }
            .code-slot.is-new [data-digit] {
                display: inline-block;
                animation: code-digit-in 120ms ease-out;
            }
            #code-boxes.is-invalid .code-slot {
                border-color: rgb(239 68 68);
            }
            #code-boxes.is-invalid .code-slot.is-active {
                box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.25);
            }
            #code-boxes.is-invalid .code-slots {
                animation: code-shake 240ms ease-in-out;
            }
            #code-boxes.is-valid .code-slot {
                border-color: rgb(22 163 74);
            }
            #code-boxes.is-valid .code-slot.is-active {
                box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.2);
            }
            #code-boxes.is-busy .code-slot {
                opacity: 0.6;
            }
            @keyframes code-caret {
                50% { opacity: 0; }
            }
            @keyframes code-digit-in {
                from { transform: scale(0.8); opacity: 0.4; }
                to { transform: none; opacity: 1; }
            }
            @keyframes code-shake {
                20%, 60% { transform: translateX(-4px); }
                40%, 80% { transform: translateX(4px); }
            }

            /* The real input lies over the boxes, invisible but present: transparent, never
               opacity:0 or hidden, which some browsers skip for autofill and focus. 16px, or iOS
               zooms the page on focus. The autofill rule stops Chrome painting its own background
               over the boxes when a code is filled in from Mail.

               44px wider than the boxes, with that strip clipped away: a password manager that
               ignores the data-*ignore attributes positions its badge from the input's right
               edge, so it lands in the clipped strip beyond the sixth box instead of on it. */
            #code-boxes .code-input {
                position: absolute;
                top: 0;
                bottom: 0;
                left: 0;
                width: calc(100% + 44px);
                clip-path: inset(0 44px 0 0);
                height: 100%;
                margin: 0;
                padding: 0;
                border: 0;
                outline: none;
                background: transparent;
                box-shadow: none;
                color: transparent;
                -webkit-text-fill-color: transparent;
                caret-color: transparent;
                font-size: 16px;
                cursor: text;
            }
            /* The card is overflow:hidden, which still SCROLLS to reveal a focused element: the
               input's extra 44px reaches past the card's padding, so focusing it slid the whole
               card 20px sideways. clip cuts the same corners but cannot be scrolled; a browser
               without it keeps the layout's overflow-hidden. */
            .auth-card {
                overflow: clip;
            }
            #code-boxes .code-input:focus {
                outline: none;
                box-shadow: none;
            }
            #code-boxes .code-input::selection {
                background: transparent;
            }
            #code-boxes .code-input:-webkit-autofill {
                -webkit-text-fill-color: transparent;
                transition: background-color 600000s 0s;
            }
            @media (prefers-reduced-motion: reduce) {
                .code-slot [data-caret] { animation: none; }
                .code-slot.is-new [data-digit],
                #code-boxes.is-invalid .code-slots { animation: none; }
            }

            /* Step two's fields settle in rather than snapping into place. */
            @keyframes signup-step-enter {
                from { opacity: 0; transform: translateY(6px); }
                to { opacity: 1; transform: none; }
            }
            .signup-step-enter {
                animation: signup-step-enter 200ms ease-out both;
            }
            @media (prefers-reduced-motion: reduce) {
                .signup-step-enter { animation: none; }
            }
        </style>
    </x-slot>

    <x-slot name="abovePage">
    </x-slot>

    @php
        // The hosted sign-up is a two-step flow: the visitor gives an email, we mail a code, and
        // only then does the rest of the form appear. $stepped is that "still on step one" state.
        //
        // It gates `required` as well as the hiding, because a required control inside a hidden
        // container is one the browser refuses to focus: constraint validation then fails, the
        // submit is silently abandoned and nothing is reported. That is the defect issue #124 was
        // about on the booking form, and Enter in the email field reaches a submit from step one.
        // revealSignupFields() arms name, password and the code the moment they become visible.
        // The hosted terms box is the exception: it is on screen from the start, so it is always
        // required.
        //
        // Not gated on is_testing any more: local installs run APP_TESTING=true, so the stepped
        // page was only ever seen in production and the one-screen fallback is what got reviewed.
        // store() still skips the code check under is_testing, so locally any six digits pass.
        $stepped = config('app.hosted');
    @endphp

    {{-- The page had no <h1> and nothing saying why to be on it: a logo, then fields. Every promise
         the visitor was reading a moment earlier ("Set up in under 2 minutes", "No credit card
         required", marketing/index.blade.php:533,1832) was dropped at the point of commitment.

         Hosted only. On selfhost this is either the install wizard or an operator adding an account
         to their own server, where "no credit card" describes nothing.

         No price here, deliberately: plan_price() and platform_currency() own those, and a figure
         written into a string is wrong on any install that changed its currency. "No credit card"
         is currency-free, which is why the claim is phrased that way. --}}
    @if (config('app.hosted'))
    <div class="mb-8 text-center">
        <h1 id="signup-heading" class="text-xl font-bold text-gray-900 dark:text-gray-100">
            {{ __('messages.signup_heading') }}
        </h1>
        <p id="signup-subheading" class="mt-1 text-sm text-gray-500 dark:text-gray-400 text-balance">
            {{ __('messages.signup_subheading') }}
        </p>

        {{-- Step two's subheading: where the code went, and a way to fix the address. The
             address gets a line of its own, so a long one never breaks the sentence at the card
             edge, and the fix is a small pill rather than a bare link, so it reads as the one
             secondary action here. Outside the form, which is fine: the button is type="button".
             The spam and expiry help is further down, under the code field, and only once it is
             needed (see showCodeHelp()). --}}
        <div id="code-sent-panel" class="mt-2" style="display: none;">
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.code_sent_to_prefix') }}</p>
            <p class="mt-0.5 text-sm font-semibold text-gray-900 dark:text-gray-100 break-words">
                <bdi dir="ltr" id="code-sent-address"></bdi>
            </p>
            <button type="button" id="change-email-btn" class="mt-3 inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-white/5 hover:bg-gray-200 dark:hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] dark:focus:ring-offset-gray-800 transition-all duration-200">
                <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
                {{ __('messages.use_another_email') }}
            </button>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('sign_up') }}" class="w-full">
        @csrf

        <input type="hidden" id="timezone" name="timezone"/>
        <input type="hidden" id="language_code" name="language_code"/>

        @if ($errors->any() && ! $errors->has('database_host'))
        <div id="signup-server-errors" role="alert" class="mb-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 p-3">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
                <p class="text-sm text-red-700 dark:text-red-300">{{ $errors->first() }}</p>
            </div>
        </div>
        @endif

        @if (in_array(session('signup_role_type'), ['talent', 'venue', 'curator'], true))
        <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-700 rounded-lg">
            <p class="text-sm text-blue-700 dark:text-blue-300 text-center">
                {{ __('messages.setting_up_your_schedule', ['type' => __('messages.' . session('signup_role_type'))]) }}
            </p>
        </div>
        @endif

        @if (selfhost_needs_setup())

            @if (!is_writable(base_path('.env')))
            <div class="mb-4 rounded-md bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-700 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 6a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 6zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700 dark:text-yellow-300">
                            {{ __('messages.env_not_writable') }}
                            <a href="https://eventschedule.com/docs/selfhost/installation#permissions" target="_blank" rel="noopener noreferrer" class="font-medium text-yellow-700 dark:text-yellow-200 underline hover:text-yellow-600 dark:hover:text-yellow-100">{{ __('messages.learn_more') }}<svg class="inline-block w-3 h-3 ml-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg></a>
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Host -->
            <div class="mt-4">
                <x-input-label for="database_host" :value="__('messages.mysql_host')" />
                <x-text-input id="database_host" class="block mt-1 w-full" type="text" name="database_host" :value="old('database_host', config('database.connections.mysql.host'))" required
                    autocomplete="off" />
                <x-input-error :messages="$errors->get('database_host')" class="mt-2" />
            </div>

            <!-- Port -->
            <div class="mt-4">
                <x-input-label for="database_port" :value="__('messages.port')" />
                <x-text-input id="database_port" class="block mt-1 w-full" type="text" name="database_port" :value="old('database_port', config('database.connections.mysql.port'))" required
                    autocomplete="off" />
                <x-input-error :messages="$errors->get('database_port')" class="mt-2" />
            </div>

            <!-- Database -->
            <div class="mt-4">
                <x-input-label for="database_name" :value="__('messages.database')" />
                <x-text-input id="database_name" class="block mt-1 w-full" type="text" name="database_name" :value="old('database_name', config('database.connections.mysql.database'))" required
                    autocomplete="off" />
                <x-input-error :messages="$errors->get('database_name')" class="mt-2" />
            </div>

            <!-- Username -->
            <div class="mt-4">
                <x-input-label for="database_username" :value="__('messages.username')" />
                <x-text-input id="database_username" class="block mt-1 w-full" type="text" name="database_username" :value="old('database_username', config('database.connections.mysql.username'))" required
                    autocomplete="off" />
                <x-input-error :messages="$errors->get('database_username')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-input-label for="database_password" :value="__('messages.password')" />
                <x-password-input id="database_password" class="block mt-1 w-full" name="database_password" :value="old('database_password')"
                    autocomplete="new-password" />
                <x-input-error :messages="$errors->get('database_password')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between mt-4 space-x-4">
                <div id="test-result"></div>
                <x-primary-button type="button" id="test-connection-btn">
                    {{ __('messages.test') }}
                </x-primary-button>
            </div>

        @endif

        @if (selfhost_needs_setup())
        <div id="registration-fields" style="display: none;">
        @endif

        @if(!empty($smsPhone))
            <input type="hidden" name="sms_token" value="{{ old('sms_token', session('sms_token', request('sms_token'))) }}">
            <div class="mt-4 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg">
                <p class="text-sm text-green-700 dark:text-green-300">
                    {{ __('messages.phone_will_be_verified') }}: {{ $smsPhone }}
                </p>
            </div>
        @endif

        @if (config('app.hosted'))
        {{-- One consent box, ABOVE both ways in, so it visibly gates Google and the emailed code
             alike. Always required: it is on screen from the first moment, so the #124 rule
             (never require a control inside a hidden container) does not apply to it. Clicking
             Google or Continue unticked is stopped client-side by requireTerms(); the
             server still validates `terms => accepted` on the email path. Both documents are
             replaceable by the operator, so policy_url() resolves them, never marketing_url(). --}}
        <div id="terms-field" class="mb-5">
            <div class="relative flex items-start">
                <div class="flex h-6 items-center">
                    <input id="terms" name="terms" type="checkbox" value="1" {{ old('terms') ? 'checked' : '' }} required aria-describedby="terms-error"
                        class="h-4 w-4 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)] dark:focus:ring-offset-gray-800">
                </div>
                <div class="ms-3 text-sm leading-6">
                    <label for="terms" class="text-gray-700 dark:text-gray-300">
                        {!! str_replace([':terms', ':privacy'], [
                            '<a href="' . policy_url('terms') . '" target="_blank" class="text-[var(--brand-blue)] hover:underline">' . __('messages.terms_of_service') . '</a>',
                            '<a href="' . policy_url('privacy') . '" target="_blank" class="text-[var(--brand-blue)] hover:underline">' . __('messages.privacy_policy') . '</a>'
                        ], __('messages.i_accept_the_terms_and_privacy')) !!}
                    </label>
                </div>
            </div>
            <p id="terms-error" role="alert" class="mt-1 text-sm text-red-600 dark:text-red-400" style="display: none;">{{ __('messages.terms_must_be_accepted') }}</p>
            <x-input-error :messages="$errors->get('terms')" class="mt-2" />
        </div>
        @endif

        {{-- Hosted puts Google FIRST: about half of all accounts arrive this way, and it is the
             one path with no code to wait for. It folds away in step two once a code is out, and
             "Use a different email" brings it back - see showCodeSentState(). Selfhost keeps it
             below the form (further down). --}}
        @if (config('app.hosted') && (config('services.google.client_id') || facebook_login_enabled()) && public_registration_enabled())
        <div id="google-signup-section" class="w-full" data-requires-terms>
            {{-- "Continue with", not "Sign up with": a returning Google user reaching this page
                 should not be told they are signing up. Google first, Facebook second: Google is
                 the proven path. Both sit inside [data-requires-terms], so the terms check covers
                 them alike. --}}
            <div class="space-y-3">
                @if (config('services.google.client_id'))
                <x-google-button>{{ __('messages.continue_with_google') }}</x-google-button>
                @endif
                @if (facebook_login_enabled())
                <x-facebook-button>{{ __('messages.continue_with_facebook') }}</x-facebook-button>
                @endif
            </div>

            {{-- A flex rule, not a line behind an opaque label: .auth-card is a gradient, so no
                 flat mask colour can match the surface behind it. --}}
            <div class="mt-6 flex items-center gap-4">
                <div class="flex-1 h-px bg-gray-300 dark:bg-gray-600"></div>
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.or') }}</span>
                <div class="flex-1 h-px bg-gray-300 dark:bg-gray-600"></div>
            </div>
        </div>
        @endif

        <!-- Email Address -->
        <div class="mt-4">
            @if (config('app.hosted'))
            {{-- Full width: the send button used to share this row and squeezed the address to
                 half the card. It now sits below the Turnstile widget, where the step ends.
                 #email-entry is visually hidden once a code is sent (still rendered, for password
                 managers - see showCodeSentState()), because #code-sent-panel then states
                 the address and offers "use a different email". Folded, not removed: the
                 read-only input is still what the form posts. --}}
            <div id="email-entry">
                <x-input-label for="email" :value="__('messages.email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', base64_decode(is_string(request()->email) ? request()->email : ''))" required
                    autofocus autocomplete="email" autocapitalize="off" spellcheck="false" placeholder="you@example.com" aria-describedby="signup-code-hint" />
                {{-- Says, before the button is pressed, what pressing it does and why: without it
                     the send button read as an odd alternative to signing up rather than the
                     first half of it. Inside #email-entry, so it folds away with the address. --}}
                <p id="signup-code-hint" class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('messages.signup_code_hint') }}</p>
                {{-- "Did you mean jane@gmail.com?" for a likely typo of a big provider, filled in by
                     updateEmailSuggestion(). A code sent to gmial.com is a code nobody receives. --}}
                <p id="email-suggestion" class="mt-2 text-sm text-gray-700 dark:text-gray-300" style="display: none;"></p>
            </div>
            {{-- role="status" because every success and every failure of this page's key
                 interaction was previously announced to nobody. --}}
            <div id="code-message" class="mt-1 text-sm" role="status" aria-live="polite"></div>

            {{-- First in step two: the code is what the visitor has just come back from their inbox
                 with, so it comes before Name and Password, not after them. --}}
            <div class="mt-4" id="verification-code-field" style="display: none;">
                {{-- Screen-reader only: the line under the heading already says "We emailed a 6-digit code",
                     and the boxes say the rest. It still names the input. --}}
                <x-input-label for="verification_code" :value="__('messages.verification_code')" class="sr-only" />
                {{-- NOT required in the markup: this wrapper renders display:none until a code has
                     actually been sent, and a browser refuses to focus a required control it cannot
                     show - so the form silently refuses to submit and reports nothing, which is the
                     defect issue #124 was about on the booking form. revealSignupFields() arms it at
                     the moment it becomes visible, the same way toggleAccountFields() does there. --}}
                {{-- Six boxes, ONE input. The boxes are drawn (aria-hidden) and the real input lies
                     transparent on top of them, which is how the best OTP fields are built rather
                     than six separate inputs: a phone's "code from Mail" suggestion fills a single
                     one-time-code field (into six it lands in the first box only), a pasted
                     "Your code is 123456" fills all six, and a screen reader meets one labelled
                     field instead of six unlabelled characters. renderCodeSlots() paints the boxes.

                     A raw <input>, not the text-input component: that one defaults autocomplete to
                     "off", the one value that SUPPRESSES the phone's suggestion, and brings field
                     chrome the overlay must not have. Transparent rather than opacity:0 or hidden,
                     which some browsers skip for autofill and focus. No maxlength: the browser
                     truncates a paste BEFORE the input handler can strip the prose around the
                     code. No placeholder: the empty boxes are the placeholder. dir="ltr" because a
                     code reads left to right in Hebrew and Arabic too. The data-*ignore attributes
                     (1Password, LastPass, Bitwarden, Dashlane, Proton Pass) keep password managers
                     from painting their badge over the sixth box; see .code-input for the ones
                     that do not listen. --}}
                <div id="code-boxes" class="relative mt-1" dir="ltr">
                    <div class="code-slots" aria-hidden="true">
                        {{-- $codeSlot, never $slot: loops share the view's scope, and $slot is Blade's
                             name for component content. --}}
                        @for ($codeSlot = 0; $codeSlot < 6; $codeSlot++)
                            <div data-code-slot class="code-slot bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-700"><span data-digit></span><span data-caret></span></div>
                            @if ($codeSlot === 2)
                                <span class="code-slots-gap"></span>
                            @endif
                        @endfor
                    </div>
                    <input id="verification_code" name="verification_code" type="text" class="code-input"
                        value="{{ $errors->has('verification_code') ? '' : old('verification_code') }}"
                        inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}"
                        aria-describedby="code-resend-row" data-1p-ignore data-lpignore="true" data-bwignore data-form-type="other" data-protonpass-ignore>
                </div>
                <x-input-error :messages="$errors->get('verification_code')" class="mt-2" />
                {{-- showSignupErrors() puts a refused code here, straight under the box, rather than
                     at the end of the wrapper below the resend row. --}}
                <div data-error-slot></div>
                {{-- Shown once checkSignupCode() has accepted the code, before name and password. --}}
                <p id="code-verified-note" class="mt-2 flex items-center gap-1 text-sm font-medium text-green-700 dark:text-green-400" style="display: none;">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                    {{ __('messages.verified') }}
                </p>

                {{-- Visible confirmation of a Resend. The status line announces it to screen readers
                     but shows nothing, and the resend row simply going away reads as nothing
                     happening. --}}
                <p id="code-resent-note" class="mt-2 text-sm font-medium text-green-700 dark:text-green-400" style="display: none;">{{ __('messages.code_resent') }}</p>
                {{-- Beside the field the visitor is waiting on, not at the bottom of the card, and
                     only once a resend is possible: startResendCountdown() holds it back for the
                     first 30 seconds instead of ticking "Resend in 29s" under a fresh code. --}}
                <p id="code-resend-row" class="mt-2 text-sm">
                    <span class="text-gray-600 dark:text-gray-400">{{ __('messages.didnt_receive_code') }}</span>
                    <button type="button" id="resend-code-btn" class="text-[var(--brand-blue)] hover:underline focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] rounded">{{ __('messages.resend_code') }}</button>
                </p>
                {{-- Where to look when it is not in the inbox. Hidden until the resend wait is over
                     (showCodeHelp()): somebody whose code arrived in five seconds never needs it,
                     and somebody still waiting after thirty is told where to look at that moment.
                     The spam line is the subscribe panel's, already translated everywhere; the
                     subject line is true of the code mail (signup_verification_code_subject). --}}
                <p id="code-help-note" class="mt-1 text-xs text-gray-500 dark:text-gray-400" style="display: none;">{{ __('messages.signup_verification_code_expiry') }} {{ __('messages.subscribe_done_note') }} {{ __('messages.signup_code_in_subject') }}</p>
            </div>
            @else
            <x-input-label for="email" :value="__('messages.email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', base64_decode(is_string(request()->email) ? request()->email : ''))" required
                autocomplete="email" />
            @endif
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Name -->
        <div class="mt-4" id="name-field" @if($stepped) style="display: none;" @endif>
            <x-input-label for="name" :value="__('messages.full_name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" :required="! $stepped"
                :autofocus="! $stepped" autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4" id="password-field" @if($stepped) style="display: none;" @endif>
            <x-input-label for="password" :value="__('messages.password')" />

            <x-password-input id="password" class="block mt-1 w-full" name="password" :required="! $stepped" minlength="8"
                autocomplete="new-password" aria-describedby="password-help" />

            {{-- The only statement of the rule used to be minlength="8", whose violation message is
                 supplied by the browser in ITS language, not the visitor's - so in eleven of the
                 twelve shipped locales the one piece of guidance on this field arrived in English,
                 and only after a failed submit. Key already exists and is already translated. --}}
            <p id="password-help" class="mt-1 flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                {{-- Ticked by the page once the rule is met, so nobody learns it only from a refusal. --}}
                <svg data-met-icon class="w-3.5 h-3.5 flex-shrink-0" style="display: none;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                <span>{{ __('messages.password_min_chars') }}</span>
            </p>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <x-honeypot />

        <!-- Turnstile widget -->
        @if (\App\Utils\TurnstileUtils::isEnabled())
            <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?onload=onTurnstileLoad" async defer {!! nonce_attr() !!}></script>
            <script {!! nonce_attr() !!}>
                function onTurnstileLoad() {
                    @if (config('app.hosted'))
                    // interaction-only: most visitors pass without seeing anything, so the
                    // widget only takes up room for the traffic Cloudflare wants to challenge.
                    // The token therefore arrives on its own a moment after load, which is why
                    // sendVerificationCode() waits for it rather than posting an empty one.
                    turnstileWidgetId = turnstile.render('#turnstile-widget', {
                        sitekey: '{{ \App\Utils\TurnstileUtils::getSiteKey() }}',
                        size: 'flexible',
                        appearance: 'interaction-only',
                        // The container still holds a hidden iframe when nothing is shown, so
                        // its spacing is added only once a challenge actually appears.
                        'before-interactive-callback': function () {
                            turnstileInteractive = true;
                            document.getElementById('turnstile-widget').classList.add('mt-4');
                        },
                        'after-interactive-callback': function () {
                            turnstileInteractive = false;
                        },
                    });
                    @else
                    turnstile.render('#turnstile-widget', {
                        sitekey: '{{ \App\Utils\TurnstileUtils::getSiteKey() }}',
                        size: 'flexible',
                    });
                    @endif
                }
            </script>
            <div id="turnstile-widget" @if (! config('app.hosted')) class="mt-4" @endif></div>
            <x-input-error :messages="$errors->get('cf-turnstile-response')" class="mt-2" />
        @endif

        @if (config('app.hosted'))
        {{-- Below the Turnstile widget rather than beside the email field, so the address gets
             the full width and the step reads top to bottom: address, check, go. The id is what
             the script block drives (busy state, cooldown, hidden in step two), so keep it. --}}
        <button type="button" id="send-code-btn" class="mt-4 w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-[var(--brand-button-bg-light)] to-[var(--brand-button-bg)] hover:from-[var(--brand-button-bg)] hover:to-[var(--brand-button-bg-hover)] border border-transparent rounded-md font-semibold text-base text-white shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-200">
            {{ __('messages.signup_continue') }}
            <svg class="w-5 h-5 rtl:rotate-180" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
        </button>
        @endif

        @if (! config('app.hosted') && (config('services.google.client_id') || facebook_login_enabled()) && public_registration_enabled())
        <div id="google-signup-section" class="w-full mt-6">
            {{-- A flex rule with the label between the two halves, NOT an absolutely-positioned
                 line behind an opaque label. .auth-card is a gradient (app.css) and this layout
                 opts into the six --ap-* palettes, so no flat mask colour can match the surface
                 behind it: the old `px-2 bg-white dark:bg-gray-800` painted a visible patch.
                 Same fix, and the same reasoning, as event/guest-submit.blade.php:569-571. --}}
            <div class="mb-6 flex items-center gap-4">
                <div class="flex-1 h-px bg-gray-300 dark:bg-gray-600"></div>
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.or') }}</span>
                <div class="flex-1 h-px bg-gray-300 dark:bg-gray-600"></div>
            </div>

            {{-- "Continue with", not "Sign up with": half of all accounts arrive this way and a
                 returning Google user reaching this page should not be told they are signing up.
                 The component carries aria-hidden on the icon and the logical me-2 margin; its
                 docblock asks the seven hand-rolled copies to adopt it when next touched. --}}
            <div class="space-y-3">
                @if (config('services.google.client_id'))
                <x-google-button>{{ __('messages.continue_with_google') }}</x-google-button>
                @endif
                @if (facebook_login_enabled())
                <x-facebook-button>{{ __('messages.continue_with_facebook') }}</x-facebook-button>
                @endif
            </div>
        </div>
        @endif

        @if (! config('app.hosted'))
        <div class="mt-8" id="terms-field">
            <div class="relative flex items-start">
                <div class="flex h-6 items-center">
                    <input id="terms" name="terms" type="checkbox" value="1" {{ old('terms') ? 'checked' : '' }} required
                        class="h-4 w-4 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)] dark:focus:ring-offset-gray-800">
                </div>
                <div class="ms-3 text-sm leading-6">
                    <label for="terms" class="font-medium text-gray-900 dark:text-gray-300">
                        {!! str_replace([':terms'], [
                            '<a href="' . policy_url('terms', '/self-hosting-terms-of-service') . '" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline"> ' . __('messages.terms_of_service') . '</a>',
                        ], __('messages.i_accept_the_terms')) !!}
                    </label>
                </div>
            </div>
            {{-- Consistency with every other field, not a fix for an invisible error: the summary
                 block at the top of this form already renders $errors->first(), so the server-side
                 `accepted` rule was always announced. This just points at the control it is about,
                 and pairs with the old('terms') above so a rejected submit does not silently clear
                 the box the visitor had already ticked. --}}
            <x-input-error :messages="$errors->get('terms')" class="mt-2" />
        </div>
        @endif

        @if (! config('app.hosted'))
        <div class="mt-4">
            <div class="relative flex items-start">
                <div class="flex h-6 items-center">
                    <input id="report_errors" name="report_errors" type="checkbox" value="1"
                        class="h-4 w-4 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)] dark:focus:ring-offset-gray-800">
                </div>
                <div class="ms-3 text-sm leading-6">
                    <label for="report_errors" class="font-medium text-gray-900 dark:text-gray-300">
                        {{ __('messages.report_errors') }}
                    </label>
                </div>
            </div>
        </div>
        @endif
        
        {{-- The margin lives on #submit-section, not on this wrapper: the wrapper is always
             rendered, so in step one its margin was dead space at the bottom of the card. --}}
        <div class="flex items-center justify-end">
            <div id="submit-section" class="w-full {{ config('app.hosted') ? 'mt-6' : 'mt-8 sm:w-auto' }}" @if($stepped) style="display: none;" @endif>
                @if (config('app.hosted'))
                {{-- Same full-width brand CTA as step one's send button, so both steps end in the
                     same place with the same kind of button. Disabled until a code has been sent:
                     see setCreateAccountEnabled(). --}}
                {{-- Anything store() refuses that has no field of its own to sit under (Turnstile, a
                     rate limit, a dropped connection). Filled in by showGeneralSignupError(). --}}
                <div id="signup-form-error" role="alert" class="mb-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 p-3" style="display: none;">
                    <p id="signup-form-error-text" class="text-sm text-red-700 dark:text-red-300"></p>
                </div>
                <button type="submit" id="create-account-btn" disabled class="w-full inline-flex items-center justify-center px-4 py-3 bg-gradient-to-r from-[var(--brand-button-bg-light)] to-[var(--brand-button-bg)] hover:from-[var(--brand-button-bg)] hover:to-[var(--brand-button-bg-hover)] border border-transparent rounded-md font-semibold text-base text-white shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-200">
                    {{ __('messages.create_account') }}
                </button>
                @else
                <x-primary-button class="w-full sm:w-auto justify-center">
                    {{ __('messages.sign_up') }}
                </x-primary-button>
                @endif
            </div>
        </div>

        @if (config('app.hosted'))
        {{-- The whole link used to be the question, so the destination was never stated. Two
             existing keys rather than a new sentence: the question stays plain text and only the
             answer is a link. Also carries the address, so /login can prefill it. --}}
        <div class="mt-5 text-sm text-center text-gray-600 dark:text-gray-400" id="already-registered">
            {{ __('messages.already_registered') }}
            <a id="already-registered-link" class="hover:underline text-[var(--brand-blue)] rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--brand-blue)] dark:focus:ring-offset-gray-800"
                href="{{ route('login') }}">
                {{ __('messages.log_in') }}
            </a>
        </div>
        @endif

        @if (selfhost_needs_setup())
        </div>
        @endif
    </form>

    @if(session('pending_request') && session('pending_request_allow_guest') && config('app.hosted'))
    <div id="guest-option" class="w-full mt-2">
        {{-- Flex rule, not a line behind an opaque label: the old bg-white mask painted a patch
             on the gradient .auth-card. --}}
        <div class="mb-6 flex items-center gap-4">
            <div class="flex-1 h-px bg-gray-300 dark:bg-gray-600"></div>
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.or') }}</span>
            <div class="flex-1 h-px bg-gray-300 dark:bg-gray-600"></div>
        </div>

        <a href="{{ session('pending_request_form') === 'booking' ? route('event.booking_request', ['subdomain' => session('pending_request'), 'lang' => is_valid_language_code(request()->lang) ? request()->lang : null]) : route('event.guest_import', ['subdomain' => session('pending_request'), 'lang' => is_valid_language_code(request()->lang) ? request()->lang : null]) }}"
            class="w-full inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-blue-600 dark:text-blue-300 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-md hover:bg-blue-100 dark:hover:bg-blue-800 hover:text-blue-700 dark:hover:text-blue-200 transition-colors duration-200">
            {{ __('messages.continue_as_guest') }}
        </a>
    </div>
    @endif
    @include('partials.social-login-guard')
</x-auth-layout>
