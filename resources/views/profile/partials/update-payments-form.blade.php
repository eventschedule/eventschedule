<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('messages.ticket_payment_methods') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('messages.ticket_payment_methods_help') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.update_payments') }}" enctype="multipart/form-data" class="mt-6">
        @csrf
        @method('patch')
        
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            <a href="https://stripe.com" target="_blank" class="hover:underline text-gray-600 dark:text-gray-400">
                Stripe Connect
            </a>
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
                <x-secondary-button onclick="window.location.href='{{ route('stripe.link') }}'">
                    {{ __('messages.connect_stripe') }}
                </x-secondary-button>
            </div>
        @endif

        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 pt-8">
            <a href="https://invoiceninja.com" target="_blank" class="hover:underline text-gray-600 dark:text-gray-400">
                Invoice Ninja
            </a>
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
                <a href="https://invoiceninja.com/partner-perks/event-schedule" target="_blank" class="hover:underline text-gray-600 dark:text-gray-400">
                    {{ __('messages.invoiceninja_offer') }}
                </a>  
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
                    :value="old('invoiceninja_api_url', $user->invoiceninja_api_url)" placeholder="https://invoicing.co" />
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
