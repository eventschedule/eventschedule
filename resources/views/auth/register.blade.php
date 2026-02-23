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

            @if (! config('app.hosted') && ! config('app.url'))
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

        // Show all fields if form is reloaded with validation errors or if code was already sent
        document.addEventListener('DOMContentLoaded', function() {
            var verificationCodeInput = document.getElementById('verification_code');
            var emailInput = document.getElementById('email');
            var hasErrors = @json($errors->any());
            // If verification code field has a value (from old input), email is readonly, or there are validation errors, show all fields
            if (verificationCodeInput && (verificationCodeInput.value || emailInput.readOnly || hasErrors)) {
                var nameField = document.getElementById('name-field');
                var passwordField = document.getElementById('password-field');
                var verificationCodeField = document.getElementById('verification-code-field');
                var termsField = document.getElementById('terms-field');
                var submitSection = document.getElementById('submit-section');
                if (nameField) nameField.style.display = 'block';
                if (passwordField) passwordField.style.display = 'block';
                if (verificationCodeField) verificationCodeField.style.display = 'block';
                if (termsField) termsField.style.display = 'block';
                if (submitSection) submitSection.style.display = 'block';
                // Hide the Google signup section
                var googleSignupSection = document.getElementById('google-signup-section');
                if (googleSignupSection) googleSignupSection.style.display = 'none';
                // Hide guest option if code was already sent
                var guestOption = document.getElementById('guest-option');
                if (guestOption) guestOption.style.display = 'none';
                // If email is readonly, lock it
                if (emailInput.readOnly) {
                    lockedEmail = emailInput.value.toLowerCase();
                    emailInput.classList.add('bg-gray-100', 'dark:bg-gray-700', 'cursor-not-allowed');
                }
            }
        });

        function sendVerificationCode() {
                var email = document.getElementById('email').value;
                var sendCodeBtn = document.getElementById('send-code-btn');
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
                sendCodeBtn.disabled = true;
                sendCodeBtn.innerHTML = @json(__('messages.sending')) + '...';
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
                    sendCodeBtn.disabled = false;
                    sendCodeBtn.innerHTML = @json(__('messages.send_code'));

                    return response.json().then(data => {
                        // Check if response is successful
                        if (response.ok && data.success) {
                            // Lock the email field after successful code send
                            lockedEmail = email.toLowerCase();
                            emailInput.setAttribute('readonly', 'readonly');
                            emailInput.classList.add('bg-gray-100', 'dark:bg-gray-700', 'cursor-not-allowed');
                            codeMessage.innerHTML = '<span class="text-green-600 dark:text-green-400">' + data.message + '</span>';
                            // Show the rest of the form fields
                            var nameField = document.getElementById('name-field');
                            var passwordField = document.getElementById('password-field');
                            var verificationCodeField = document.getElementById('verification-code-field');
                            var termsField = document.getElementById('terms-field');
                            var submitSection = document.getElementById('submit-section');
                            if (nameField) nameField.style.display = 'block';
                            if (passwordField) passwordField.style.display = 'block';
                            if (verificationCodeField) verificationCodeField.style.display = 'block';
                            if (termsField) termsField.style.display = 'block';
                            if (submitSection) submitSection.style.display = 'block';
                            // Hide the Google signup section
                            var googleSignupSection = document.getElementById('google-signup-section');
                            if (googleSignupSection) googleSignupSection.style.display = 'none';
                            // Hide the guest option
                            var guestOption = document.getElementById('guest-option');
                            if (guestOption) guestOption.style.display = 'none';
                            // Focus on the name field and pre-fill if available
                            var nameInput = document.getElementById('name');
                            if (nameInput) {
                                if (data.name) {
                                    nameInput.value = data.name;
                                }
                                nameInput.focus();
                            }
                            // Reset Turnstile widget so user gets a fresh token for form submission
                            if (typeof turnstile !== 'undefined' && turnstileWidgetId !== null) {
                                turnstile.reset(turnstileWidgetId);
                            }
                        } else {
                            // Handle validation errors or other errors
                            var errorMessage = data.message || @json(__('messages.error_sending_code'));

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
                    sendCodeBtn.disabled = false;
                    sendCodeBtn.innerHTML = @json(__('messages.send_code'));
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
                    sendVerificationCode();
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
                    this.value = this.value.replace(/[^0-9]/g, '');
                });
            }
        });
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
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.has_existing_user) {
                            document.getElementById('test-result').innerHTML = '<span class="text-amber-600 dark:text-amber-400"><svg class="inline-block w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg> {{ __('messages.database_already_initialized') }}</span>';
                            document.querySelector('button[type="submit"]').disabled = true;
                            @if (! config('app.url'))
                            var regFields = document.getElementById('registration-fields');
                            if (regFields) regFields.style.display = 'none';
                            @endif
                        } else {
                            document.getElementById('test-result').innerHTML = '<span class="text-green-600 dark:text-green-400"><svg class="inline-block w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> {{ __('messages.connection_successful') }}!</span>';
                            // Enable register button on successful connection
                            document.querySelector('button[type="submit"]').disabled = false;
                            @if (! config('app.url'))
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
                        @if (! config('app.url'))
                        var regFields = document.getElementById('registration-fields');
                        if (regFields) regFields.style.display = 'none';
                        @endif
                    }
                })
                .catch(error => {
                    document.getElementById('test-result').innerHTML = '<span class="text-red-600 dark:text-red-400"><svg class="inline-block w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg> {{ __('messages.error_testing_connection') }}</span>';
                    // Disable register button on error
                    document.querySelector('button[type="submit"]').disabled = true;
                    @if (! config('app.url'))
                    var regFields = document.getElementById('registration-fields');
                    if (regFields) regFields.style.display = 'none';
                    @endif
                });
            }

        @endif


        </script>

        <style {!! nonce_attr() !!}>
            form button {
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

    <form method="POST" action="{{ route('sign_up') }}" class="w-full">
        @csrf

        <input type="hidden" id="timezone" name="timezone"/>
        <input type="hidden" id="language_code" name="language_code"/>

        @if (! config('app.hosted') && ! config('app.url'))

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

        @if (! config('app.hosted') && ! config('app.url'))
        <div id="registration-fields" style="display: none;">
        @endif

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('messages.email')" />
            @if (config('app.hosted'))
            <div class="flex flex-col sm:flex-row gap-2">
                <x-text-input id="email" class="block mt-1 flex-1 min-w-0 w-full sm:w-auto" type="email" name="email" :value="old('email', base64_decode(request()->email))" required
                    autocomplete="email" />
                <button type="button" id="send-code-btn" class="mt-1 w-full sm:w-auto sm:flex-shrink-0 whitespace-nowrap inline-flex items-center justify-center px-6 py-3 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-sm text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-[#4E81FA] focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    {{ __('messages.send_code') }}
                </button>
            </div>
            <div id="code-message" class="mt-1 text-sm"></div>
            @else
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', base64_decode(request()->email))" required
                autocomplete="email" />
            @endif
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Name -->
        <div class="mt-4" id="name-field" @if(config('app.hosted')) style="display: none;" @endif>
            <x-input-label for="name" :value="__('messages.full_name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required
                autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4" id="password-field" @if(config('app.hosted')) style="display: none;" @endif>
            <x-input-label for="password" :value="__('messages.password')" />

            <x-password-input id="password" class="block mt-1 w-full" name="password" required minlength="8"
                autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Verification Code -->
        @if (config('app.hosted'))
        <div class="mt-4" id="verification-code-field" style="display: none;">
            <x-input-label for="verification_code" :value="__('messages.verification_code')" />
            <x-text-input id="verification_code" class="block mt-1 w-full" type="text" name="verification_code" 
                :value="old('verification_code')" maxlength="6" pattern="[0-9]{6}" required autocomplete="off" />
            <x-input-error :messages="$errors->get('verification_code')" class="mt-2" />
        </div>
        @endif

        <!-- Honeypot field -->
        <div class="hidden">
            <input type="text" name="website" autocomplete="off" tabindex="-1">
        </div>

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

        @if (config('services.google.client_id') && config('app.hosted'))
        <div id="google-signup-section" class="w-full mt-6">
            <div class="relative mb-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400">{{ __('messages.or') }}</span>
                </div>
            </div>

            <a href="{{ route('auth.google') }}" class="w-full inline-flex items-center justify-center px-4 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#4E81FA] dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                {{ __('messages.sign_up_with_google') }}
            </a>
        </div>
        @endif

        @if (config('app.hosted'))
        <div class="mt-6">
            <a class="hover:underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#4E81FA] dark:focus:ring-offset-gray-800"
                href="{{ route('login') }}">
                {{ __('messages.already_registered') }}
            </a>
        </div>
        @endif

        <div class="mt-8" id="terms-field" @if(config('app.hosted')) style="display: none;" @endif>
            <div class="relative flex items-start">
                <div class="flex h-6 items-center">
                    <input id="terms" name="terms" type="checkbox" required
                        class="h-4 w-4 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] dark:focus:ring-offset-gray-800">
                </div>
                <div class="ml-3 text-sm leading-6">
                    <label for="terms" class="font-medium text-gray-900 dark:text-gray-300">
                        @if (config('app.hosted'))
                            {!! str_replace([':terms', ':privacy'], [
                                '<a href="' . marketing_url('/terms-of-service') . '" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline"> ' . __('messages.terms_of_service') . '</a>',
                                '<a href="' . marketing_url('/privacy') . '" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">' . __('messages.privacy_policy') . '</a>'
                            ], __('messages.i_accept_the_terms_and_privacy')) !!}
                        @else
                            {!! str_replace([':terms'], [
                                '<a href="' . marketing_url('/self-hosting-terms-of-service') . '" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline"> ' . __('messages.terms_of_service') . '</a>',
                            ], __('messages.i_accept_the_terms')) !!}
                        @endif
                    </label>
                </div>
            </div>
        </div>

        @if (! config('app.hosted'))
        <div class="mt-4">
            <div class="relative flex items-start">
                <div class="flex h-6 items-center">
                    <input id="report_errors" name="report_errors" type="checkbox" value="1"
                        class="h-4 w-4 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] dark:focus:ring-offset-gray-800">
                </div>
                <div class="ml-3 text-sm leading-6">
                    <label for="report_errors" class="font-medium text-gray-900 dark:text-gray-300">
                        {{ __('messages.report_errors') }}
                    </label>
                </div>
            </div>
        </div>
        @endif
        
        <div class="flex items-center justify-end mt-8">
            <div id="submit-section" @if(config('app.hosted')) style="display: none;" @endif>
                <x-primary-button>
                    {{ __('messages.sign_up') }}
                </x-primary-button>
            </div>
        </div>

        @if (! config('app.hosted') && ! config('app.url'))
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

        <a href="{{ route('event.guest_import', ['subdomain' => session('pending_request'), 'lang' => is_valid_language_code(request()->lang) ? request()->lang : null]) }}"
            class="w-full inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-blue-600 dark:text-blue-300 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-md hover:bg-blue-100 dark:hover:bg-blue-800 hover:text-blue-700 dark:hover:text-blue-200 transition-colors duration-200">
            {{ __('messages.continue_as_guest') }}
        </a>
    </div>
    @endif
</x-auth-layout>
