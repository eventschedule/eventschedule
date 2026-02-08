<x-auth-layout>
    <div id="totp-section">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
            {{ __('messages.two_factor_challenge') }}
        </h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
            {{ __('messages.two_factor_challenge_description') }}
        </p>

        <form method="POST" action="{{ route('two-factor.challenge') }}">
            @csrf

            <div>
                <x-input-label for="code" :value="__('messages.two_factor_code')" />
                <x-text-input id="code" class="block mt-1 w-full" type="text" name="code" autofocus autocomplete="one-time-code" inputmode="numeric" pattern="[0-9]*" maxlength="6" />
                <x-input-error :messages="$errors->get('code')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between mt-4">
                <button type="button" id="show-recovery" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:underline">
                    {{ __('messages.two_factor_use_recovery_code') }}
                </button>
                <x-primary-button>
                    {{ __('messages.verify') }}
                </x-primary-button>
            </div>
        </form>
    </div>

    <div id="recovery-section" class="hidden">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
            {{ __('messages.two_factor_recovery') }}
        </h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
            {{ __('messages.two_factor_recovery_description') }}
        </p>

        <form method="POST" action="{{ route('two-factor.challenge') }}">
            @csrf

            <div>
                <x-input-label for="recovery_code" :value="__('messages.two_factor_recovery_code')" />
                <x-text-input id="recovery_code" class="block mt-1 w-full" type="text" name="recovery_code" autocomplete="off" />
                <x-input-error :messages="$errors->get('recovery_code')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between mt-4">
                <button type="button" id="show-totp" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:underline">
                    {{ __('messages.two_factor_use_authenticator') }}
                </button>
                <x-primary-button>
                    {{ __('messages.verify') }}
                </x-primary-button>
            </div>
        </form>
    </div>

    <script {!! nonce_attr() !!}>
        document.getElementById('show-recovery').addEventListener('click', function() {
            document.getElementById('totp-section').classList.add('hidden');
            document.getElementById('recovery-section').classList.remove('hidden');
            document.getElementById('recovery_code').focus();
        });
        document.getElementById('show-totp').addEventListener('click', function() {
            document.getElementById('recovery-section').classList.add('hidden');
            document.getElementById('totp-section').classList.remove('hidden');
            document.getElementById('code').focus();
        });
    </script>
</x-auth-layout>
