<x-auth-layout>
    @if (session('warning'))
        <div class="mb-4 text-sm text-yellow-600 dark:text-yellow-400">
            {{ session('warning') }}
        </div>
    @endif

    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('messages.admin_confirm_password') }}
    </div>

    <form method="POST" action="{{ route('admin.password.confirm') }}">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('messages.password')" />

            <x-password-input id="password" class="block mt-1 w-full"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end mt-4">
            <x-primary-button>
                {{ __('messages.confirm') }}
            </x-primary-button>
        </div>
    </form>
</x-auth-layout>
