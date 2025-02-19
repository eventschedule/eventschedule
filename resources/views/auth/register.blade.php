<x-auth-layout>

    <x-slot name="head">
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;            
            document.getElementById('timezone').value = timezone;

            var language = navigator.language || navigator.userLanguage;
            var twoLetterLanguageCode = language.substring(0, 2);
            document.getElementById('language_code').value = twoLetterLanguageCode;
        });
        </script>
    </x-slot>

    <form method="POST" action="{{ route('sign_up') }}">
        @csrf

        <input type="hidden" id="timezone" name="timezone"/>
        <input type="hidden" id="language_code" name="language_code"/>

        @if (true ||! config('app.hosted'))

            <!-- Driver -->
            <div>
                <x-input-label for="driver" :value="__('messages.driver')" />
                <x-text-input id="driver" class="block mt-1 w-full" type="text" name="driver" :value="old('driver')" required
                    autofocus autocomplete="driver" />
                <x-input-error :messages="$errors->get('driver')" class="mt-2" />
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
                autocomplete="username" />
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

        <div class="mt-4">
            <div class="relative flex items-start">
                <div class="flex h-6 items-center">
                    <input id="terms" name="terms" type="checkbox" required
                        class="h-4 w-4 rounded border-gray-300 text-[#4E81FA] focus:ring-[#4E81FA]">
                </div>
                <div class="ml-3 text-sm leading-6">
                    <label for="terms" class="font-medium text-gray-900 dark:text-gray-300">
                        {!! str_replace([':terms', ':privacy'], [
                            '<a href="https://www.eventschedule.com/terms-of-service" target="_blank" class="hover:underline"> ' . __('messages.terms_of_service') . '</a>', 
                            '<a href="https://www.eventschedule.com/privacy" target="_blank" class="hover:underline">' . __('messages.privacy_policy') . '</a>'
                        ], __('messages.i_accept_the_terms_and_privacy')) !!}
                    </label>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between mt-4">
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
