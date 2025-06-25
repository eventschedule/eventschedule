<x-auth-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if(request()->has('email'))
        <!-- Direct unsubscribe link - no form needed -->
        <div class="text-center">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('messages.unsubscribed') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                You have been successfully unsubscribed from future emails.
            </p>
        </div>
    @else
        <!-- Manual unsubscribe form -->
        <form method="POST" action="{{ route('role.unsubscribe') }}">
            @csrf

            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('messages.email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="off" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-primary-button class="w-full">
                    {{ __('messages.unsubscribe') }}
                </x-primary-button>
            </div>
        </form>
    @endif
</x-auth-layout>
