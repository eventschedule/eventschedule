<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('messages.payment_methods') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('messages.ticket_payment_methods_help') }}
        </p>
    </header>

    @if (is_demo_mode())
    <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded text-yellow-800 dark:text-yellow-200 text-sm">
        {{ __('messages.demo_mode_settings_disabled') }}
    </div>
    @endif

    <!-- Tab Navigation -->
    <div class="border-b border-gray-200 dark:border-gray-700 mb-6 mt-6">
        <nav class="flex space-x-4" aria-label="Tabs">
            <button type="button" class="payment-tab px-3 py-2 text-sm font-medium border-b-2 border-[#4E81FA] text-[#4E81FA]" data-tab="stripe">
                {{ __('messages.stripe') }}
            </button>
            <button type="button" class="payment-tab px-3 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600" data-tab="invoiceninja">
                Invoice Ninja
            </button>
            <button type="button" class="payment-tab px-3 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600" data-tab="payment-url">
                {{ __('messages.payment_url') }}
            </button>
        </nav>
    </div>

    <!-- Tab Content: Stripe -->
    <div id="payment-tab-stripe" class="payment-tab-content">
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
                        <form method="POST" action="{{ route('stripe.unlink') }}" class="inline" onsubmit="return confirm('{{ __('messages.are_you_sure') }}')">
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
                            onclick="alert('{{ __('messages.saving_disabled_demo_mode') }}')"
                            class="inline-flex items-center px-4 py-2 bg-gray-400 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest cursor-not-allowed">
                            {{ __('messages.connect_stripe') }}
                        </button>
                    @else
                        <x-primary-button type="button" onclick="window.location.href='{{ route('stripe.link') }}'">
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
    </div>

    <!-- Tab Content: Invoice Ninja -->
    <div id="payment-tab-invoiceninja" class="payment-tab-content hidden">
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            {{ __('messages.invoiceninja_help') }}
        </p>

        @if ($user->invoiceninja_api_key)
            <div class="mt-4">
                <x-text-input type="text" class="mt-1 block w-full" :value="$user->invoiceninja_company_name" readonly/>
                <div class="text-xs pt-1">
                    <form method="POST" action="{{ route('invoiceninja.unlink') }}" class="inline" onsubmit="return confirm('{{ __('messages.are_you_sure') }}')">
                        @csrf
                        <button type="submit" class="hover:underline text-gray-600 dark:text-gray-400">{{ __('messages.unlink_account') }}</button>
                    </form>
                </div>
            </div>
        @else
            <p class="text-sm text-gray-600 dark:text-gray-400">
                <x-link href="https://invoiceninja.com/partner-perks/event-schedule-perk/" target="_blank">
                    {{ __('messages.invoiceninja_offer') }}
                </x-link>
            </p>

            <form method="post" action="{{ route('profile.update_payments') }}" enctype="multipart/form-data" class="mt-4">
                @csrf
                @method('patch')

                <div class="pt-4">
                    <x-input-label for="invoiceninja_api_key" :value="__('messages.api_token') . ' *'" />
                    <x-text-input id="invoiceninja_api_key" name="invoiceninja_api_key" type="text" class="mt-1 block w-full"
                        :value="old('invoiceninja_api_key', $user->invoiceninja_api_key)" autocomplete="off" required :disabled="is_demo_mode()" />
                    <x-input-error class="mt-2" :messages="$errors->get('invoiceninja_api_key')" />
                </div>

                <div class="pt-4">
                    <x-input-label for="invoiceninja_api_url" :value="__('messages.api_url')" />
                    <x-text-input id="invoiceninja_api_url" name="invoiceninja_api_url" type="url" class="mt-1 block w-full"
                        :value="old('invoiceninja_api_url', $user->invoiceninja_api_url)" :disabled="is_demo_mode()" />
                    <x-input-error class="mt-2" :messages="$errors->get('invoiceninja_api_url')" />
                </div>

                <div class="flex items-center gap-4 pt-8">
                    @if (is_demo_mode())
                        <button type="button"
                            onclick="alert('{{ __('messages.saving_disabled_demo_mode') }}')"
                            class="inline-flex items-center px-4 py-2 bg-gray-400 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest cursor-not-allowed">
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
    </div>

    <!-- Tab Content: Payment URL -->
    <div id="payment-tab-payment-url" class="payment-tab-content hidden">
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            {{ __('messages.payment_url_help') }}
        </p>

        @if ($user->payment_url)
            <div class="mt-4">
                <x-text-input type="text" class="mt-1 block w-full" :value="$user->payment_url" readonly/>
                <div class="text-xs pt-1">
                    <form method="POST" action="{{ route('profile.unlink_payment_url') }}" class="inline" onsubmit="return confirm('{{ __('messages.are_you_sure') }}')">
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
                            onclick="alert('{{ __('messages.saving_disabled_demo_mode') }}')"
                            class="inline-flex items-center px-4 py-2 bg-gray-400 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest cursor-not-allowed">
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
    </div>

</section>

<script {!! nonce_attr() !!}>
document.addEventListener('DOMContentLoaded', function() {
    const paymentTabs = document.querySelectorAll('.payment-tab');
    const paymentTabContents = document.querySelectorAll('.payment-tab-content');

    // Restore active tab from localStorage
    const savedPaymentTab = localStorage.getItem('paymentActiveTab');
    if (savedPaymentTab) {
        switchPaymentTab(savedPaymentTab);
    }

    paymentTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const tabName = this.dataset.tab;
            switchPaymentTab(tabName);
            localStorage.setItem('paymentActiveTab', tabName);
        });
    });

    function switchPaymentTab(tabName) {
        paymentTabs.forEach(tab => {
            if (tab.dataset.tab === tabName) {
                tab.classList.add('border-[#4E81FA]', 'text-[#4E81FA]');
                tab.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400', 'hover:text-gray-700', 'dark:hover:text-gray-300', 'hover:border-gray-300', 'dark:hover:border-gray-600');
            } else {
                tab.classList.remove('border-[#4E81FA]', 'text-[#4E81FA]');
                tab.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400', 'hover:text-gray-700', 'dark:hover:text-gray-300', 'hover:border-gray-300', 'dark:hover:border-gray-600');
            }
        });

        paymentTabContents.forEach(content => {
            const contentId = content.id.replace('payment-tab-', '');
            if (contentId === tabName) {
                content.classList.remove('hidden');
            } else {
                content.classList.add('hidden');
            }
        });
    }
});
</script>
