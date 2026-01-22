<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('messages.payment_methods') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('messages.ticket_payment_methods_help') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.update_payments') }}" enctype="multipart/form-data" class="mt-6">
        @csrf
        @method('patch')
        
        @if (config('app.hosted'))
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                <x-link href="https://stripe.com" target="_blank">
                    Stripe Connect
                </x-link>
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.stripe_help') }}
            </p>

            @if ($user->stripe_account_id)
                <div class="mt-4">
                    @if (! $user->stripe_completed_at)
                        <x-input-label for="stripe_account_id" :value="__('messages.account_id') . ' [' . __('messages.pending') . ']'" />
                    @endif
                    <x-text-input type="text" class="mt-1 block w-full" :value="$user->stripe_company_name ? $user->stripe_company_name : $user->stripe_account_id" readonly/>
                    <div class="text-xs pt-1">
                        <a href="#" onclick="return confirm('{{ __('messages.are_you_sure') }}') ? window.location.href='{{ route('stripe.unlink') }}' : false" class="hover:underline text-gray-600 dark:text-gray-400">{{ __('messages.unlink_account') }}</a>
                    </div>
                </div>
            @endif

            @if (! $user->stripe_completed_at)
                <div class="mt-4">
                    <x-primary-button type="button" onclick="window.location.href='{{ route('stripe.link') }}'">
                        {{ __('messages.connect_stripe') }}
                    </x-primary-button>
                </div>
            @endif
        @else
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                <x-link href="https://stripe.com" target="_blank">
                    Stripe
                </x-link>
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.stripe_selfhosted_help') }}
            </p>

            <div class="mt-4">
                @if (config('services.stripe_platform.secret'))
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.stripe_configured') }}</span>
                    </div>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-500">
                        {{ __('messages.stripe_configured_help') }}
                    </p>
                @else
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
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

        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mt-8">
            <x-link href="https://invoiceninja.com" target="_blank">
                Invoice Ninja
            </x-link>
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('messages.invoiceninja_help') }}
        </p>
        
        @if ($user->invoiceninja_api_key)
            <div style="margin-top: 16px;">
                <x-text-input type="text" class="mt-1 block w-full" :value="$user->invoiceninja_company_name" readonly/>
                <div class="text-xs pt-1">
                    <a href="#" onclick="return confirm('{{ __('messages.are_you_sure') }}') ? window.location.href='{{ route('invoiceninja.unlink') }}' : false" class="hover:underline text-gray-600 dark:text-gray-400">{{ __('messages.unlink_account') }}</a>
                </div>
            </div>        
        @else

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                <x-link href="https://invoiceninja.com/partner-perks/event-schedule/" target="_blank">
                    {{ __('messages.invoiceninja_offer') }}
                </x-link>  
            </p>

            <div class="pt-4">
                <x-input-label for="invoiceninja_api_key" :value="__('messages.api_token') . ' *'" />
                <x-text-input id="invoiceninja_api_key" name="invoiceninja_api_key" type="text" class="mt-1 block w-full" 
                    :value="old('invoiceninja_api_key', $user->invoiceninja_api_key)" autocomplete="off" required />
                <x-input-error class="mt-2" :messages="$errors->get('invoiceninja_api_key')" />
            </div>

            <div class="pt-4">
                <x-input-label for="invoiceninja_api_url" :value="__('messages.api_url')" />
                <x-text-input id="invoiceninja_api_url" name="invoiceninja_api_url" type="url" class="mt-1 block w-full" 
                    :value="old('invoiceninja_api_url', $user->invoiceninja_api_url)" />
                <x-input-error class="mt-2" :messages="$errors->get('invoiceninja_api_url')" />
            </div>

            <div class="flex items-center gap-4 pt-8">
                <x-primary-button>{{ __('messages.save') }}</x-primary-button>

                @if (session('status') === 'payments-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.saved') }}</p>
                @endif
            </div>
        @endif

    </form>
</section>

<section class="mt-8">

    <form method="post" action="{{ route('profile.update_payments') }}" enctype="multipart/form-data" class="mt-6">
        @csrf
        @method('patch')

        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 pt-4">
            {{ __('messages.payment_url') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('messages.payment_url_help') }}
        </p>


        @if ($user->payment_url)
            <div class="mt-4">
                <x-text-input type="text" class="mt-1 block w-full" :value="$user->payment_url" readonly/>
                <div class="text-xs pt-1">
                    <a href="#" onclick="return confirm('{{ __('messages.are_you_sure') }}') ? window.location.href='{{ route('profile.unlink_payment_url') }}' : false" class="hover:underline text-gray-600 dark:text-gray-400">{{ __('messages.unlink_account') }}</a>
                </div>
            </div>
        @else
            <div class="mt-4">
                <x-text-input id="payment_url" name="payment_url" type="url" class="mt-1 block w-full" 
                    :value="old('payment_url', $user->payment_url)" autocomplete="off" required />
                <x-input-error class="mt-2" :messages="$errors->get('payment_url')" />
            </div>

            <div class="flex items-center gap-4 pt-8">
                <x-primary-button>{{ __('messages.save') }}</x-primary-button>

                @if (session('status') === 'payments-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.saved') }}</p>
                @endif
            </div>
        @endif

    </form>
</section>
