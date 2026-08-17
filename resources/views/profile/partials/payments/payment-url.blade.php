    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
        {{ __('messages.payment_url_help') }}
    </p>

    @if ($user->payment_url)
        <div class="mt-4">
            <x-text-input type="text" class="mt-1 block w-full" :value="$user->payment_url" readonly/>
            <div class="text-xs pt-1">
                <form method="POST" action="{{ route('profile.unlink_payment_url') }}" class="inline" data-confirm="{{ __('messages.are_you_sure') }}">
                    @csrf
                    <button type="submit" class="hover:underline text-gray-600 dark:text-gray-400">{{ __('messages.unlink_account') }}</button>
                </form>
            </div>
        </div>
    @else
        <form method="post" action="{{ route('profile.update_payments') }}" enctype="multipart/form-data" class="mt-4">
            @csrf
            @method('patch')

            <div class="mt-4">
                <x-text-input id="payment_url" name="payment_url" type="url" class="mt-1 block w-full"
                    :value="old('payment_url', $user->payment_url)" autocomplete="off" required :disabled="is_demo_mode()" />
                <x-input-error class="mt-2" :messages="$errors->get('payment_url')" />
            </div>

            <div class="flex items-center gap-4 pt-8">
                @if (is_demo_mode())
                    <button type="button"
                        data-alert="{{ __('messages.saving_disabled_demo_mode') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-400 dark:bg-gray-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest cursor-not-allowed">
                        {{ __('messages.save') }}
                    </button>
                @else
                    <x-primary-button>{{ __('messages.save') }}</x-primary-button>
                @endif

                @if (session('status') === 'payments-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.saved') }}</p>
                @endif
            </div>
        </form>
    @endif
