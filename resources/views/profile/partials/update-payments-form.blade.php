<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('messages.payment_methods') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('messages.update_your_payment_methods') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.update_payments') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        @if ($user->stripe_account_id)
            <div>
                @if ($user->stripe_completed_at)
                    <x-input-label for="stripe_account_id" :value="'Stripe'" />
                @else
                    <x-input-label for="stripe_account_id" :value="'Stripe - ' . __('messages.account_id') . ' [' . __('messages.pending') . ']'" />
                @endif
                <x-text-input type="text" class="mt-1 block w-full" :value="$user->stripe_company_name ? $user->stripe_company_name : $user->stripe_account_id" readonly/>
                <div class="text-xs pt-1">
                    <a href="#" onclick="return confirm('{{ __('messages.are_you_sure') }}') ? window.location.href='{{ route('stripe.unlink') }}' : false" class="hover:underline text-gray-600 dark:text-gray-400">{{ __('messages.unlink_account') }}</a>
                </div>
            </div>
        @endif

        @if (! $user->stripe_completed_at)
            <div class="pb-4">
                <x-secondary-button onclick="window.location.href='{{ route('stripe.link') }}'">
                    {{ __('messages.connect_stripe') }}
                </x-secondary-button>
            </div>
        @endif

        @if ($user->invoiceninja_api_key)
            <div>
                <x-input-label :value="'Invoice Ninja'" />
                <x-text-input type="text" class="mt-1 block w-full" :value="$user->invoiceninja_company_name" readonly/>
                <div class="text-xs pt-1">
                    <a href="#" onclick="return confirm('{{ __('messages.are_you_sure') }}') ? window.location.href='{{ route('invoiceninja.unlink') }}' : false" class="hover:underline text-gray-600 dark:text-gray-400">{{ __('messages.unlink_account') }}</a>
                </div>
            </div>        
        @else
            <div>
                <x-input-label for="invoiceninja_api_key" :value="'Invoice Ninja - ' . __('messages.api_key')" />
                <x-text-input id="invoiceninja_api_key" name="invoiceninja_api_key" type="text" class="mt-1 block w-full" 
                    :value="old('invoiceninja_api_key', $user->invoiceninja_api_key)" />
                <x-input-error class="mt-2" :messages="$errors->get('invoiceninja_api_key')" />
                <div class="text-xs pt-1">
                    <a href="https://invoiceninja.com/payments" target="_blank" class="hover:underline text-gray-600 dark:text-gray-400">
                        {{ __('messages.learn_more') }}
                    </a>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <x-primary-button>{{ __('messages.save') }}</x-primary-button>

                @if (session('status') === 'payments-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.saved') }}</p>
                @endif
            </div>
        @endif

    </form>
</section>
