<section>
    <header>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
            </svg>
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
    <div class="ap-tab-container border-b border-gray-200 dark:border-gray-700 mb-6 mt-6">
        <nav class="flex space-x-4 overflow-x-auto scrollbar-hide" aria-label="Tabs">
            <button type="button" class="payment-tab text-center px-3 py-2 text-sm font-medium border-b-2 border-[var(--brand-blue)] text-[var(--brand-blue)]" data-tab="stripe">
                {{ __('messages.stripe') }}
            </button>
            <button type="button" class="payment-tab text-center px-3 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600" data-tab="invoiceninja">
                Invoice Ninja
            </button>
            <button type="button" class="payment-tab text-center px-3 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600" data-tab="payment-url">
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
    </div>

    <!-- Tab Content: Invoice Ninja -->
    <div id="payment-tab-invoiceninja" class="payment-tab-content hidden">
        @if (session('invoiceninja_error'))
            <div class="mb-4 flex items-start gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 flex-shrink-0 mt-0.5 text-red-600 dark:text-red-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <div class="min-w-0">
                    <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ __('messages.error_invoiceninja_connection') }}</p>
                    <p class="mt-1 text-sm text-red-700 dark:text-red-300">{{ __('messages.'.session('invoiceninja_reason', 'invoiceninja_error_generic')) }}</p>
                    <p class="mt-2 text-xs font-mono break-words text-red-700 dark:text-red-300" v-pre>{{ session('invoiceninja_error') }}</p>
                    <p class="mt-2 text-xs">
                        <x-link href="{{ marketing_url('/docs/account-settings#invoice-ninja') }}" target="_blank">{{ __('messages.learn_more') }}</x-link>
                    </p>
                </div>
            </div>
        @endif

        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            {{ __('messages.invoiceninja_help') }}
        </p>

        @if ($user->invoiceninja_api_key)
            <div class="mt-4">
                <x-text-input type="text" class="mt-1 block w-full" :value="$user->invoiceninja_company_name" readonly/>
                <div class="text-xs pt-1 flex items-center gap-3">
                    <button type="button" id="invoiceninja-change-btn" class="hover:underline text-gray-600 dark:text-gray-400">{{ __('messages.edit') }}</button>
                    <form method="POST" action="{{ route('invoiceninja.unlink') }}" class="inline" data-confirm="{{ __('messages.are_you_sure') }}">
                        @csrf
                        <button type="submit" class="hover:underline text-gray-600 dark:text-gray-400">{{ __('messages.unlink_account') }}</button>
                    </form>
                </div>
            </div>

            <form method="post" action="{{ route('profile.update_payments') }}" id="invoiceninja-change-form" class="mt-4 hidden">
                @csrf
                @method('patch')

                <div class="pt-4">
                    <x-input-label for="invoiceninja_change_api_key" :value="__('messages.api_token')" />
                    <x-text-input id="invoiceninja_change_api_key" name="invoiceninja_api_key" type="text" class="mt-1 block w-full"
                        value="" autocomplete="off" placeholder="{{ str_repeat('•', 10) }}" :disabled="is_demo_mode()" />
                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-0.5">{{ __('messages.invoiceninja_api_token_help') }}</p>
                </div>

                <div class="pt-4">
                    <x-input-label for="invoiceninja_change_api_url" :value="__('messages.api_url')" />
                    <x-text-input id="invoiceninja_change_api_url" name="invoiceninja_api_url" type="url" class="mt-1 block w-full"
                        :value="old('invoiceninja_api_url', $user->invoiceninja_api_url)" placeholder="https://invoices.example.com" :disabled="is_demo_mode()" />
                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-0.5">{{ __('messages.invoiceninja_api_url_help') }}</p>
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
                </div>
            </form>

            <form method="POST" action="{{ route('profile.update_invoiceninja_mode') }}" class="mt-6"
                x-data="{ type: '{{ $user->invoiceninja_mode ?? 'invoice' }}' }">
                @csrf
                @method('patch')

                <input type="hidden" name="invoiceninja_mode" :value="type">

                <x-input-label :value="__('messages.invoiceninja_mode')" />

                <div class="mt-2 space-y-2">
                    <label class="flex items-start gap-2 cursor-pointer">
                        <input type="radio" value="invoice" x-model="type"
                            class="mt-0.5 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]"
                            {{ is_demo_mode() ? 'disabled' : '' }}>
                        <div>
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.invoiceninja_mode_invoice') }}</span>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-0.5">{{ __('messages.invoiceninja_mode_invoice_desc') }}</p>
                        </div>
                    </label>
                    <label class="flex items-start gap-2 cursor-pointer">
                        <input type="radio" value="payment_link" x-model="type"
                            class="mt-0.5 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]"
                            {{ is_demo_mode() ? 'disabled' : '' }}>
                        <div>
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.invoiceninja_mode_payment_link') }}</span>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-0.5">{{ __('messages.invoiceninja_mode_payment_link_desc') }}</p>
                        </div>
                    </label>
                </div>

                @if(Route::has('marketing.docs.tickets'))
                <p class="mt-2 ms-6 text-xs text-gray-500 dark:text-gray-500">
                    <x-link href="{{ route('marketing.docs.tickets') }}#invoiceninja-modes" target="_blank">{{ __('messages.learn_more') }}</x-link>
                </p>
                @endif

                <div class="flex items-center gap-4 pt-4">
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
        @else
            <p class="text-sm text-gray-600 dark:text-gray-400">
                <x-link href="https://invoiceninja.com/partner-perks/event-schedule-perk/" target="_blank">
                    {{ __('messages.invoiceninja_offer') }}
                </x-link>
            </p>

            <form method="post" action="{{ route('profile.update_payments') }}" enctype="multipart/form-data" class="mt-4" id="invoiceninja-connect-form">
                @csrf
                @method('patch')

                <div class="pt-4">
                    <x-input-label for="invoiceninja_api_key" :value="__('messages.api_token') . ' *'" />
                    <x-text-input id="invoiceninja_api_key" name="invoiceninja_api_key" type="text" class="mt-1 block w-full"
                        :value="old('invoiceninja_api_key', $user->invoiceninja_api_key)" autocomplete="off" required :disabled="is_demo_mode()" />
                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-0.5">{{ __('messages.invoiceninja_api_token_help') }}</p>
                    <x-input-error class="mt-2" :messages="$errors->get('invoiceninja_api_key')" />
                </div>

                <div class="pt-4">
                    <x-input-label for="invoiceninja_api_url" :value="__('messages.api_url')" />
                    <x-text-input id="invoiceninja_api_url" name="invoiceninja_api_url" type="url" class="mt-1 block w-full"
                        :value="old('invoiceninja_api_url', $user->invoiceninja_api_url)" placeholder="https://invoices.example.com" :disabled="is_demo_mode()" />
                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-0.5">{{ __('messages.invoiceninja_api_url_help') }}</p>
                    <x-input-error class="mt-2" :messages="$errors->get('invoiceninja_api_url')" />
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
    </div>

</section>

<script {!! nonce_attr() !!}>
document.addEventListener('DOMContentLoaded', function() {
    // Alert buttons for demo mode
    document.querySelectorAll('[data-alert]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            alert(this.dataset.alert);
        });
    });

    // Connect Stripe button
    var connectStripeBtn = document.getElementById('connect-stripe-btn');
    if (connectStripeBtn) {
        connectStripeBtn.addEventListener('click', function() {
            window.location.href = this.dataset.href;
        });
    }

    const paymentTabs = document.querySelectorAll('.payment-tab');
    const paymentTabContents = document.querySelectorAll('.payment-tab-content');

    // Restore active tab from localStorage
    const savedPaymentTab = localStorage.getItem('paymentActiveTab');
    if (savedPaymentTab) {
        switchPaymentTab(savedPaymentTab);
    }

    // "Change credentials" reveals the pre-filled form over the connected state.
    const invoiceninjaChangeBtn = document.getElementById('invoiceninja-change-btn');
    const invoiceninjaChangeForm = document.getElementById('invoiceninja-change-form');
    if (invoiceninjaChangeBtn && invoiceninjaChangeForm) {
        invoiceninjaChangeBtn.addEventListener('click', function() {
            invoiceninjaChangeForm.classList.remove('hidden');
            const urlField = document.getElementById('invoiceninja_change_api_url');
            if (urlField) {
                urlField.focus();
            }
        });
    }

    // The connect request is synchronous and can take up to 30s, so show progress.
    ['invoiceninja-connect-form', 'invoiceninja-change-form'].forEach(function(formId) {
        const form = document.getElementById(formId);
        if (!form) {
            return;
        }
        form.addEventListener('submit', function() {
            const button = form.querySelector('button[type="submit"], button:not([type])');
            if (!button || button.disabled) {
                return;
            }
            button.disabled = true;
            button.innerHTML = '<svg class="animate-spin h-4 w-4 me-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">'
                + '<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>'
                + '<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>'
                + '</svg>' + @json(__('messages.connecting'), JSON_UNESCAPED_UNICODE);
        });
    });

    @if (session('invoiceninja_error'))
        // A failed connection redirects back here, so make sure the panel is on screen.
        switchPaymentTab('invoiceninja');
        try { localStorage.setItem('paymentActiveTab', 'invoiceninja'); } catch (e) {}
        if (invoiceninjaChangeForm) {
            invoiceninjaChangeForm.classList.remove('hidden');
        }
    @endif

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
                tab.classList.add('border-[var(--brand-blue)]', 'text-[var(--brand-blue)]');
                tab.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400', 'hover:text-gray-700', 'dark:hover:text-gray-300', 'hover:border-gray-300', 'dark:hover:border-gray-600');
            } else {
                tab.classList.remove('border-[var(--brand-blue)]', 'text-[var(--brand-blue)]');
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
