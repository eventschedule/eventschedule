<x-auth-layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('messages.reset_password') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('messages.email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', request()->email)" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-4">
            <a class="hover:underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#4E81FA] dark:focus:ring-offset-gray-800" href="{{ route('login') }}">
                {{ __('messages.cancel') }}
            </a>
            <x-primary-button>
                {{ __('messages.email_password_reset_link') }}
            </x-primary-button>
        </div>
    </form>
</x-auth-layout>
