<x-auth-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if (config('services.google.client_id'))
    <div class="w-full mb-6">
        <a href="{{ route('auth.google') }}" class="w-full inline-flex items-center justify-center px-4 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#4E81FA] dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
            <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            {{ __('messages.log_in_with_google') }}
        </a>

        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white dark:bg-gray-900 text-gray-500 dark:text-gray-400">{{ __('messages.or') }}</span>
            </div>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="w-full">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('messages.email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />

            <div class="flex justify-end pt-3">
                <a class="hover:underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#4E81FA] dark:focus:ring-offset-gray-800" href="{{ route('password.request') }}">
                    {{ __('messages.reset_password') }}
                </a>
            </div>

            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('messages.password')" />

            <x-password-input id="password" class="block mt-1 w-full"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4 hidden">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-[#4E81FA] shadow-sm focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] dark:focus:ring-offset-gray-800" name="remember" CHECKED>
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('messages.remember_me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-4">
            @if (config('app.hosted'))
            <a class="hover:underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#4E81FA] dark:focus:ring-offset-gray-800" href="{{ route('sign_up') }}">
                {{ __('messages.create_new_account') }}
            </a>
            @else
            <div></div>
            @endif

            <x-primary-button class="ml-4">
                {{ __('messages.log_in') }}
            </x-primary-button>
        </div>
    </form>
</x-auth-layout>
