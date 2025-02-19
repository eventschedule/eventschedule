<x-auth-layout>

    <x-slot name="head">
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;            
            document.getElementById('timezone').value = timezone;

            var language = navigator.language || navigator.userLanguage;
            var twoLetterLanguageCode = language.substring(0, 2);
            document.getElementById('language_code').value = twoLetterLanguageCode;

            @if (! config('app.hosted') && ! config('app.url'))
                // Disable register button initially
                document.querySelector('button[type="submit"]').disabled = true;
            @endif
        });

        @if (! config('app.hosted'))

            function testConnection() {
                var host = document.getElementById('database_host').value;
                var port = document.getElementById('database_port').value;
                var database = document.getElementById('database_name').value;
                var username = document.getElementById('database_username').value;
                var password = document.getElementById('database_password').value;

                // Show loading state
                document.getElementById('test-result').innerHTML = '<span class="text-gray-500"><svg class="inline-block w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Testing...</span>';

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
                        document.getElementById('test-result').innerHTML = '<span class="text-green-600"><svg class="inline-block w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> Connection successful!</span>';
                        // Enable register button on successful connection
                        document.querySelector('button[type="submit"]').disabled = false;
                    } else {
                        document.getElementById('test-result').innerHTML = '<span class="text-red-600"><svg class="inline-block w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg> ' + data.error + '</span>';
                        // Disable register button on failed connection
                        document.querySelector('button[type="submit"]').disabled = true;
                    }
                })
                .catch(error => {
                    document.getElementById('test-result').innerHTML = '<span class="text-red-600"><svg class="inline-block w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg> Error testing connection</span>';
                    // Disable register button on error
                    document.querySelector('button[type="submit"]').disabled = true;
                });
            }

        @endif


        </script>

        <style>
            button {
                min-width: 100px;
            }
            button:disabled {
                opacity: 0.5;
                cursor: not-allowed;
            }
        </style>
    </x-slot>

    <form method="POST" action="{{ route('sign_up') }}">
        @csrf

        <input type="hidden" id="timezone" name="timezone"/>
        <input type="hidden" id="language_code" name="language_code"/>

        @if (! config('app.hosted') && ! config('app.url'))

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
                <x-text-input id="database_password" class="block mt-1 w-full" type="password" name="database_password" :value="old('database_password')" required
                    autocomplete="off" />
                <x-input-error :messages="$errors->get('database_password')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between mt-4 space-x-4">
                <div id="test-result"></div>
                <x-primary-button type="button" onclick="testConnection()">
                    {{ __('messages.test') }}
                </x-primary-button>
            </div>

            </div>
            <div class="w-full sm:max-w-sm mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">

        @endif

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('messages.full_name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required
                autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('messages.email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', base64_decode(request()->email))" required
                autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('messages.password')" />

            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required
                autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('messages.confirm_password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Honeypot field -->
        <div class="hidden">
            <input type="text" name="website" autocomplete="off" tabindex="-1">
        </div>

        <div class="mt-6">
            <div class="relative flex items-start">
                <div class="flex h-6 items-center">
                    <input id="terms" name="terms" type="checkbox" required
                        class="h-4 w-4 rounded border-gray-300 text-[#4E81FA] focus:ring-[#4E81FA]">
                </div>
                <div class="ml-3 text-sm leading-6">
                    <label for="terms" class="font-medium text-gray-900 dark:text-gray-300">
                        {!! str_replace([':terms', ':privacy'], [
                            '<a href="https://www.eventschedule.com/' . (config('app.hosted') ? 'terms-of-service' : 'self-hosting-terms-of-service') . '" target="_blank" class="hover:underline"> ' . __('messages.terms_of_service') . '</a>', 
                            '<a href="https://www.eventschedule.com/privacy" target="_blank" class="hover:underline">' . __('messages.privacy_policy') . '</a>'
                        ], __('messages.i_accept_the_terms_and_privacy')) !!}
                    </label>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between mt-6">
            <a class="hover:underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#4E81FA] dark:focus:ring-offset-gray-800"
                href="{{ route('login') }}">
                {{ __('messages.already_registered') }}
            </a>

            <x-primary-button class="ml-4">
                {{ __('messages.register') }}
            </x-primary-button>
        </div>
    </form>
</x-auth-layout>
