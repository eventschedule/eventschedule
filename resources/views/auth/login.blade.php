<x-auth-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="w-full">
        @csrf
        <x-honeypot />

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('messages.email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', request('email'))" required autofocus autocomplete="username" />

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
                <input id="remember_me" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-[var(--brand-blue)] shadow-sm focus:ring-[var(--brand-blue)] dark:focus:ring-offset-gray-800" name="remember" CHECKED>
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('messages.remember_me') }}</span>
            </label>
        </div>

        <x-turnstile />

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('messages.log_in') }}
            </x-primary-button>
        </div>
    </form>

    @if (config('services.google.client_id'))
    <div class="w-full mt-6 mb-4">
        {{-- Flex rule with the label between the halves, not a line behind an opaque label:
             .auth-card is a gradient, so no flat colour can mask it. The two auth pages disagreed
             about which flat colour to use (gray-900 here, gray-800 on sign-up) and neither was
             right. Same fix as event/guest-submit.blade.php:569-571. --}}
        <div class="mb-8 flex items-center gap-4">
            <div class="flex-1 h-px bg-gray-300 dark:bg-gray-600"></div>
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.or') }}</span>
            <div class="flex-1 h-px bg-gray-300 dark:bg-gray-600"></div>
        </div>

        <x-google-button>{{ __('messages.log_in_with_google') }}</x-google-button>

        <div class="flex items-center {{ public_registration_enabled() ? 'justify-between' : 'justify-end' }} mt-8">
            @if (public_registration_enabled())
            <a class="hover:underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--brand-blue)] dark:focus:ring-offset-gray-800" href="{{ route('sign_up') }}">
                {{ __('messages.create_new_account') }}
            </a>
            @endif
            <a class="hover:underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--brand-blue)] dark:focus:ring-offset-gray-800" href="{{ route('password.request') }}">
                {{ __('messages.reset_password') }}
            </a>
        </div>
    </div>
    @else
        <div class="flex items-center {{ public_registration_enabled() ? 'justify-between' : 'justify-end' }} mt-8">
            @if (public_registration_enabled())
            <a class="hover:underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--brand-blue)] dark:focus:ring-offset-gray-800" href="{{ route('sign_up') }}">
                {{ __('messages.create_new_account') }}
            </a>
            @endif
            <a class="hover:underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--brand-blue)] dark:focus:ring-offset-gray-800" href="{{ route('password.request') }}">
                {{ __('messages.reset_password') }}
            </a>
        </div>
    @endif
    <script {!! nonce_attr() !!}>
        document.cookie = "browser_timezone=" + Intl.DateTimeFormat().resolvedOptions().timeZone + ";path=/;max-age=3600;SameSite=Lax";
        document.cookie = "browser_language=" + (navigator.language || "en").substring(0, 2) + ";path=/;max-age=3600;SameSite=Lax";
    </script>
</x-auth-layout>
