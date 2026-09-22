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
        var turnstileWidgetId = null;

        /**
         * Reveal the fields that only apply once a verification code has been sent.
         *
         * The verification code is required, but only from here on: it is rendered without the
         * attribute because its wrapper starts hidden, and a required control inside a hidden
         * container is one the browser will not focus, so requestSubmit() aborts on constraint
         * validation without submitting or saying anything (issue #124).
         */
        function revealSignupFields() {
            ['name-field', 'password-field', 'verification-code-field', 'terms-field', 'submit-section'].forEach(function (id) {
                var el = document.getElementById(id);
                if (el) el.style.display = 'block';
            });

            ['name', 'password', 'terms', 'verification_code'].forEach(function (id) {
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
            ['name-field', 'password-field', 'verification-code-field', 'terms-field', 'submit-section'].forEach(function (id) {
                var el = document.getElementById(id);
                if (el) el.style.display = 'none';
            });

            ['name', 'password', 'terms', 'verification_code'].forEach(function (id) {
                var el = document.getElementById(id);
                if (el) el.required = false;
            });
        }

        /**
         * Put the page into the "we have sent you a code" state.
         *
         * Note what this deliberately does NOT do: hide the Google button, the guest option or the
         * "Already registered?" link. It used to hide all three, and because the same branch also
         * runs on a validation error, ONE mistyped digit reloaded into a page whose only remaining
         * action was to retype a code the visitor did not have - no resend, no way to correct the
         * address, and the password field emptied by the browser. Those are the escape hatches; the
         * moment somebody is stuck is the moment they have to stay on screen.
         */
        function showCodeSentState(email) {
            revealSignupFields();

            var emailInput = document.getElementById('email');
            if (email) {
                lockedEmail = email.toLowerCase();
                emailInput.setAttribute('readonly', 'readonly');
                emailInput.classList.add('bg-gray-100', 'dark:bg-gray-700', 'cursor-not-allowed');

                var address = document.getElementById('code-sent-address');
                if (address) address.textContent = email;
            }

            // Only with an address. The restore path calls this with emailInput.value, which can
            // be empty on an error reload, and the panel then reads "We sent a code to ." with a
            // blank <bdi> where the address should be.
            var panel = document.getElementById('code-sent-panel');
            if (panel && email) panel.style.display = 'block';

            // Hide the top "Email me a code" button. setSendButtonIdle() re-enables it on every
            // response and startResendCountdown() only governs the panel's Resend, so leaving it on
            // screen gave step two a second send path with no rate-limit feedback at all.
            var sendCodeBtn = document.getElementById('send-code-btn');
            if (sendCodeBtn) sendCodeBtn.style.display = 'none';

            // Name the step, in the page and in the tab strip. This flow REQUIRES leaving the tab
            // to read a mail, and every auth page shipped the same literal <title>Event Schedule</title>,
            // so finding the way back meant recognising one of several identical tabs.
            var heading = document.getElementById('signup-heading');
            var subheading = document.getElementById('signup-subheading');
            if (heading) heading.textContent = @json(__('messages.signup_check_email_heading'));
            if (subheading) subheading.style.display = 'none';
            document.title = @json(__('messages.signup_check_email_heading')) + ' | Event Schedule';
        }

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
            emailInput.removeAttribute('readonly');
            emailInput.classList.remove('bg-gray-100', 'dark:bg-gray-700', 'cursor-not-allowed');
            if (panel) panel.style.display = 'none';
            if (codeInput) codeInput.value = '';
            if (codeMessage) codeMessage.innerHTML = '';

            // The rest of the inverse. Without it the page sat in a hybrid state: the panel gone,
            // but Name, Password, Terms, Submit and an empty REQUIRED "Verification code" box all
            // still on screen, with nothing left to explain where a code would come from.
            hideSignupFields();

            // Put the send button back, since showCodeSentState() hid it.
            var sendCodeBtn = document.getElementById('send-code-btn');
            if (sendCodeBtn) sendCodeBtn.style.display = '';

            // And stop the countdown, which otherwise ticks on inside a hidden panel and
            // eventually re-reveals a Resend button behind it.
            if (resendTimer) {
                clearInterval(resendTimer);
                resendTimer = null;
            }
            var counter = document.getElementById('resend-countdown');
            if (counter) counter.style.display = 'none';
            var resendBtn = document.getElementById('resend-code-btn');
            if (resendBtn) resendBtn.style.display = '';

            // Back to step one means back to step one's heading, or the page still says to go and
            // read a mail that no longer applies to the address in the box.
            var heading = document.getElementById('signup-heading');
            var subheading = document.getElementById('signup-subheading');
            if (heading) heading.textContent = @json(__('messages.signup_heading'));
            if (subheading) subheading.style.display = '';
            document.title = 'Event Schedule';

            emailInput.focus();
            emailInput.select();
        }

        /**
         * Hold the resend button for a moment after a send.
         *
         * Not decoration: sign_up/send-code allows 5 per hour per address AND, since the named
         * prefix in routes/auth.php, 5 per minute per IP. A visitor who taps resend four times
         * because nothing arrived would spend the whole minute bucket and meet a 429.
         */
        var resendTimer = null;
        function startResendCountdown(seconds) {
            var btn = document.getElementById('resend-code-btn');
            var counter = document.getElementById('resend-countdown');
            if (!btn || !counter) return;

            var remaining = seconds;
            btn.style.display = 'none';
            counter.style.display = 'inline';

            if (resendTimer) clearInterval(resendTimer);

            var tick = function () {
                counter.textContent = @json(__('messages.resend_in_label')) + ' ' + remaining + 's';
                if (remaining <= 0) {
                    clearInterval(resendTimer);
                    resendTimer = null;
                    counter.style.display = 'none';
                    // '' not 'inline': a <button>'s UA default is inline-block, and the markup
                    // carries no inline display to begin with.
                    btn.style.display = '';
                }
                remaining--;
            };

            tick();
            resendTimer = setInterval(tick, 1000);
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
        function setSendButtonBusy(btn) {
            if (!btn) return;
            // Stash the label rather than hardcoding one: this serves both "Email me a code" and
            // "Resend code", and restoring the wrong one silently relabels whichever was pressed.
            if (btn.dataset.idleLabel === undefined) {
                btn.dataset.idleLabel = btn.innerHTML;
            }
            btn.disabled = true;
            btn.setAttribute('aria-busy', 'true');
            btn.innerHTML = '<svg class="inline-block w-4 h-4 me-2 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>'
                + @json(__('messages.sending'));
        }

        function setSendButtonIdle(btn) {
            if (!btn) return;
            btn.disabled = false;
            btn.removeAttribute('aria-busy');
            if (btn.dataset.idleLabel !== undefined) {
                btn.innerHTML = btn.dataset.idleLabel;
            }
        }

        // Restore the code state when the form comes back from a failed submit.
        document.addEventListener('DOMContentLoaded', function() {
            var verificationCodeInput = document.getElementById('verification_code');
            var emailInput = document.getElementById('email');
            var hasErrors = @json($errors->any());
            if (verificationCodeInput && (verificationCodeInput.value || emailInput.readOnly || hasErrors)) {
                showCodeSentState(emailInput.value);
                // Straight to the field they have to correct, rather than the name field above it.
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
        function sendVerificationCode(triggerBtn) {
                if (sendInFlight) return;

                var email = document.getElementById('email').value;
                var sendCodeBtn = triggerBtn || document.getElementById('send-code-btn');
                var codeMessage = document.getElementById('code-message');
                var emailInput = document.getElementById('email');

                if (!email) {
                    codeMessage.innerHTML = '<span class="text-red-600 dark:text-red-400">' + @json(__('messages.please_enter_email_address')) + '</span>';
                    return;
                }

                // Validate email format
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    codeMessage.innerHTML = '<span class="text-red-600 dark:text-red-400">' + @json(__('messages.invalid_email_address')) + '</span>';
                    return;
                }

                // Get Turnstile token if available
                var turnstileToken = '';
                var turnstileInput = document.querySelector('input[name="cf-turnstile-response"]');
                if (turnstileInput) {
                    turnstileToken = turnstileInput.value;
                }

                // Get honeypot value
                var honeypotValue = '';
                var honeypotInput = document.querySelector('input[name="website"]');
                if (honeypotInput) {
                    honeypotValue = honeypotInput.value;
                }

                // Disable button and show loading
                sendInFlight = true;
                setSendButtonBusy(sendCodeBtn);
                codeMessage.innerHTML = '';

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
                    // Always re-enable button and restore text
                    sendInFlight = false;
                    setSendButtonIdle(sendCodeBtn);

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
                                if (retryAfter > 0) {
                                    errorMessage = @json(__('messages.too_many_attempts'));
                                    startResendCountdown(retryAfter);
                                }
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
                    codeMessage.innerHTML = '<span class="text-red-600 dark:text-red-400">' + @json(__('messages.error_sending_code')) + '</span>';
                    sendInFlight = false;
                    setSendButtonIdle(sendCodeBtn);
                    // Reset Turnstile widget on failure
                    if (typeof turnstile !== 'undefined' && turnstileWidgetId !== null) {
                        turnstile.reset(turnstileWidgetId);
                    }
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
            }

            // Format verification code input (numbers only)
            var codeInput = document.getElementById('verification_code');
            if (codeInput) {
                codeInput.addEventListener('input', function(e) {
                    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
                    maybeAutoSubmit(this);
                });

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
                        maybeAutoSubmit(this);
                    }
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

            var changeEmailBtn = document.getElementById('change-email-btn');
            if (changeEmailBtn) {
                changeEmailBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    changeEmail();
                });
            }
        });

        /**
         * Submit once the sixth digit lands, so the code step ends where it started.
         *
         * Gated on the consent box being ticked: an auto-submit that trips `terms => accepted`
         * would report a failure the visitor did not cause. requestSubmit() runs constraint
         * validation, and revealSignupFields() has already armed `required` on everything by the
         * time a code can be entered, so the form either submits or reports which field is missing.
         */
        var autoSubmitted = false;
        function maybeAutoSubmit(codeInput) {
            if (codeInput.value.length !== 6) return;

            // Once only. This runs on every `input` event, and the handler slices to 6, so typing a
            // seventh digit leaves the value at 6 and fires again. Each attempt calls
            // requestSubmit(), which on an incomplete form focuses the offending field and pops its
            // bubble - yanking the caret out of the code box, repeatedly, while somebody is typing
            // in it. Worst after a failed submit, where the page reloads with the code restored and
            // the terms box re-checked but the password field emptied by the browser.
            if (autoSubmitted) return;

            var terms = document.getElementById('terms');
            if (terms && !terms.checked) return;

            var form = codeInput.form;
            if (!form) return;

            // Do not auto-submit an incomplete form. Let the visitor finish Name and Password and
            // press the button themselves, rather than being bounced out of the field mid-entry.
            if (typeof form.checkValidity === 'function' && !form.checkValidity()) return;

            autoSubmitted = true;

            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                // Safari below 16 has no requestSubmit(). Without this the sixth digit did nothing
                // at all, for ever, with no message. checkValidity() above has already run, so
                // submit() skipping validation is not a hole here.
                form.submit();
            }
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
        // revealSignupFields() arms all four the moment they become visible.
        $stepped = config('app.hosted') && ! config('app.is_testing');
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
    <div class="mb-6 text-center">
        <h1 id="signup-heading" class="text-xl font-bold text-gray-900 dark:text-gray-100">
            {{ __('messages.signup_heading') }}
        </h1>
        <p id="signup-subheading" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ __('messages.signup_subheading') }}
        </p>
    </div>
    @endif

    <form method="POST" action="{{ route('sign_up') }}" class="w-full">
        @csrf

        <input type="hidden" id="timezone" name="timezone"/>
        <input type="hidden" id="language_code" name="language_code"/>

        @if ($errors->any() && ! $errors->has('database_host'))
        <div role="alert" class="mb-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 p-3">
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

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('messages.email')" />
            @if (config('app.hosted'))
            <div class="flex flex-col sm:flex-row gap-2">
                <x-text-input id="email" class="block mt-1 flex-1 min-w-0 w-full sm:w-auto" type="email" name="email" :value="old('email', base64_decode(is_string(request()->email) ? request()->email : ''))" required
                    autocomplete="email" />
                <button type="button" id="send-code-btn" class="mt-1 w-full sm:w-auto sm:flex-shrink-0 whitespace-nowrap inline-flex items-center justify-center px-6 py-3 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-sm text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    {{ __('messages.email_me_a_code') }}
                </button>
            </div>
            {{-- role="status" because every success and every failure of this page's key
                 interaction was previously announced to nobody. --}}
            <div id="code-message" class="mt-1 text-sm" role="status" aria-live="polite"></div>

            {{-- Where the code went, and the two ways out. Without these a mistyped address, a
                 mail in a spam folder or one missed expiry was a dead end: the field locks itself
                 readonly and silently rewrites keystrokes, and there was no resend.
                 Every key here already exists and is already translated in all 12 locales,
                 because event/guest-submit.blade.php has had this panel all along. --}}
            <div id="code-sent-panel" class="mt-2 rounded-lg bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-700 p-3" style="display: none;">
                <p class="text-sm text-blue-800 dark:text-blue-200">
                    {{ __('messages.code_sent_to_prefix') }}
                    <bdi dir="ltr" class="font-medium" id="code-sent-address"></bdi>.
                    {{ __('messages.code_sent_to_suffix') }}
                </p>
                <p class="mt-1 text-xs text-blue-700 dark:text-blue-300">{{ __('messages.signup_verification_code_expiry') }}</p>
                <p class="mt-2 text-sm">
                    <span class="text-gray-600 dark:text-gray-400">{{ __('messages.didnt_receive_code') }}</span>
                    <span id="resend-countdown" class="text-gray-500 dark:text-gray-500" style="display: none;"></span>
                    <button type="button" id="resend-code-btn" class="text-blue-600 dark:text-blue-300 underline hover:no-underline focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] rounded">{{ __('messages.resend_code') }}</button>
                    <span class="text-gray-300 dark:text-gray-600" aria-hidden="true">&middot;</span>
                    <button type="button" id="change-email-btn" class="text-blue-600 dark:text-blue-300 underline hover:no-underline focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] rounded">{{ __('messages.use_another_email') }}</button>
                </p>
            </div>
            @else
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', base64_decode(is_string(request()->email) ? request()->email : ''))" required
                autocomplete="email" />
            @endif
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Name -->
        <div class="mt-4" id="name-field" @if($stepped) style="display: none;" @endif>
            <x-input-label for="name" :value="__('messages.full_name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" :required="! $stepped"
                autofocus autocomplete="name" />
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
            <p id="password-help" class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('messages.password_min_chars') }}</p>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Verification Code -->
        @if (config('app.hosted'))
        <div class="mt-4" id="verification-code-field" style="display: none;">
            <x-input-label for="verification_code" :value="__('messages.verification_code')" />
            {{-- NOT required in the markup: this wrapper renders display:none until a code has
                 actually been sent, and a browser refuses to focus a required control it cannot
                 show - so the form silently refuses to submit and reports nothing, which is the
                 defect issue #124 was about on the booking form. revealSignupFields() arms it at
                 the moment it becomes visible, the same way toggleAccountFields() does there. --}}
            {{-- autocomplete="one-time-code" is the whole reason a phone offers the emailed code
                 above the keyboard; "off" - which is what x-text-input defaults to, see
                 components/text-input.blade.php - is the one value that SUPPRESSES it, and iOS
                 reads Mail for this, not only SMS. inputmode keeps a digits-only field off QWERTY.
                 No maxlength: the browser truncates a paste BEFORE the input handler can strip the
                 prose around the code, so pasting "Your code is 123456" left the box empty. The
                 paste handler in the script block extracts the digits instead.
                 Matches event/guest-submit.blade.php, which has had this shape all along. --}}
            <x-text-input id="verification_code" class="block mt-1 w-full sm:w-44 text-center text-lg tracking-[0.4em]" type="text" name="verification_code"
                :value="$errors->has('verification_code') ? '' : old('verification_code')"
                inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" placeholder="000000" />
            <x-input-error :messages="$errors->get('verification_code')" class="mt-2" />
        </div>
        @endif

        <x-honeypot />

        <!-- Turnstile widget -->
        @if (\App\Utils\TurnstileUtils::isEnabled())
            <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?onload=onTurnstileLoad" async defer {!! nonce_attr() !!}></script>
            <script {!! nonce_attr() !!}>
                function onTurnstileLoad() {
                    @if (config('app.hosted'))
                    turnstileWidgetId = turnstile.render('#turnstile-widget', {
                        sitekey: '{{ \App\Utils\TurnstileUtils::getSiteKey() }}',
                        size: 'flexible',
                    });
                    @else
                    turnstile.render('#turnstile-widget', {
                        sitekey: '{{ \App\Utils\TurnstileUtils::getSiteKey() }}',
                        size: 'flexible',
                    });
                    @endif
                }
            </script>
            <div id="turnstile-widget" class="mt-4"></div>
            <x-input-error :messages="$errors->get('cf-turnstile-response')" class="mt-2" />
        @endif

        @if (config('services.google.client_id') && public_registration_enabled())
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
            <x-google-button>{{ __('messages.continue_with_google') }}</x-google-button>

            {{-- The Google button collects no checkbox, so the terms are stated beside it and
                 pressing it is the consent. Both documents are replaceable by the operator, so
                 policy_url() resolves them, never marketing_url(). --}}
            @if (config('app.hosted'))
            <p class="mt-3 text-xs text-center text-gray-500 dark:text-gray-400">
                {!! str_replace([':terms', ':privacy'], [
                    '<a href="' . policy_url('terms') . '" target="_blank" class="underline hover:no-underline">' . __('messages.terms_of_service') . '</a>',
                    '<a href="' . policy_url('privacy') . '" target="_blank" class="underline hover:no-underline">' . __('messages.privacy_policy') . '</a>'
                ], __('messages.by_continuing_you_accept')) !!}
            </p>
            @endif
        </div>
        @endif

        @if (config('app.hosted'))
        {{-- The whole link used to be the question, so the destination was never stated. Two
             existing keys rather than a new sentence: the question stays plain text and only the
             answer is a link. Also carries the address, so /login can prefill it. --}}
        <div class="mt-6 text-sm text-gray-600 dark:text-gray-400" id="already-registered">
            {{ __('messages.already_registered') }}
            <a id="already-registered-link" class="underline hover:no-underline text-[var(--brand-blue)] rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--brand-blue)] dark:focus:ring-offset-gray-800"
                href="{{ route('login') }}">
                {{ __('messages.log_in') }}
            </a>
        </div>
        @endif

        <div class="mt-8" id="terms-field" @if($stepped) style="display: none;" @endif>
            <div class="relative flex items-start">
                <div class="flex h-6 items-center">
                    <input id="terms" name="terms" type="checkbox" value="1" {{ old('terms') ? 'checked' : '' }} {{ $stepped ? '' : 'required' }}
                        class="h-4 w-4 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)] dark:focus:ring-offset-gray-800">
                </div>
                <div class="ms-3 text-sm leading-6">
                    <label for="terms" class="font-medium text-gray-900 dark:text-gray-300">
                        @if (config('app.hosted'))
                            {!! str_replace([':terms', ':privacy'], [
                                '<a href="' . policy_url('terms') . '" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline"> ' . __('messages.terms_of_service') . '</a>',
                                '<a href="' . policy_url('privacy') . '" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">' . __('messages.privacy_policy') . '</a>'
                            ], __('messages.i_accept_the_terms_and_privacy')) !!}
                        @else
                            {!! str_replace([':terms'], [
                                '<a href="' . policy_url('terms', '/self-hosting-terms-of-service') . '" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline"> ' . __('messages.terms_of_service') . '</a>',
                            ], __('messages.i_accept_the_terms')) !!}
                        @endif
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
        
        <div class="flex items-center justify-end mt-8">
            <div id="submit-section" class="w-full sm:w-auto" @if($stepped) style="display: none;" @endif>
                <x-primary-button class="w-full sm:w-auto justify-center">
                    {{ __('messages.sign_up') }}
                </x-primary-button>
            </div>
        </div>

        @if (selfhost_needs_setup())
        </div>
        @endif
    </form>

    @if(session('pending_request') && session('pending_request_allow_guest') && config('app.hosted'))
    <div id="guest-option" class="w-full mt-2">
        <div class="relative mb-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400">{{ __('messages.or') }}</span>
            </div>
        </div>

        <a href="{{ session('pending_request_form') === 'booking' ? route('event.booking_request', ['subdomain' => session('pending_request'), 'lang' => is_valid_language_code(request()->lang) ? request()->lang : null]) : route('event.guest_import', ['subdomain' => session('pending_request'), 'lang' => is_valid_language_code(request()->lang) ? request()->lang : null]) }}"
            class="w-full inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-blue-600 dark:text-blue-300 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-md hover:bg-blue-100 dark:hover:bg-blue-800 hover:text-blue-700 dark:hover:text-blue-200 transition-colors duration-200">
            {{ __('messages.continue_as_guest') }}
        </a>
    </div>
    @endif
</x-auth-layout>
