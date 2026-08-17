    @if (config('app.hosted'))
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            {{ __('messages.stripe_help') }}
        </p>

        @if ($user->stripe_account_id)
            <div class="mt-4">
                @if (! $user->stripe_completed_at)
                    <x-input-label for="stripe_account_id" :value="__('messages.account_id') . ' [' . __('messages.pending') . ']'" />
                @endif
                <x-text-input type="text" class="mt-1 block w-full" :value="$user->stripe_company_name ? $user->stripe_company_name : $user->stripe_account_id" readonly/>
                <div class="text-xs pt-1">
                    <form method="POST" action="{{ route('stripe.unlink') }}" class="inline" data-confirm="{{ __('messages.are_you_sure') }}">
                        @csrf
                        <button type="submit" class="hover:underline text-gray-600 dark:text-gray-400">{{ __('messages.unlink_account') }}</button>
                    </form>
                </div>
            </div>
        @endif

        @if (! $user->stripe_completed_at)
            <div class="mt-4">
                @if (is_demo_mode())
                    <button type="button"
                        data-alert="{{ __('messages.saving_disabled_demo_mode') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-400 dark:bg-gray-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest cursor-not-allowed">
                        {{ __('messages.connect_stripe') }}
                    </button>
                @else
                    <x-primary-button type="button" id="connect-stripe-btn" data-href="{{ route('stripe.link') }}">
                        {{ __('messages.connect_stripe') }}
                    </x-primary-button>
                @endif
            </div>
        @endif
    @else
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            {{ __('messages.stripe_selfhosted_help') }}
        </p>

        <div class="mt-4">
            @if (config('services.stripe_platform.secret'))
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-500 me-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.stripe_configured') }}</span>
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-500">
                    {{ __('messages.stripe_configured_help') }}
                </p>
            @else
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-400 me-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.stripe_not_configured') }}</span>
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-500">
                    {{ __('messages.stripe_not_configured_help') }}
                </p>
            @endif
        </div>
    @endif
