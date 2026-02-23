<x-app-admin-layout>

    <x-slot name="head">
        <script src="https://js.stripe.com/v3/" {!! nonce_attr() !!}></script>
    </x-slot>

    <div class="max-w-4xl mx-auto py-8" x-data="{
        selectedTier: '{{ $selectedTier }}',
        selectedPlan: 'monthly',
        prices: {
            pro: { monthly: '{{ config('services.stripe_platform.price_monthly_amount') }}', yearly: '{{ config('services.stripe_platform.price_yearly_amount') }}' },
            enterprise: { monthly: '{{ config('services.stripe_platform.enterprise_price_monthly_amount') }}', yearly: '{{ config('services.stripe_platform.enterprise_price_yearly_amount') }}' }
        }
    }">
        <h2 class="pb-4 text-xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:truncate sm:text-2xl sm:tracking-tight" x-text="selectedTier === 'enterprise' ? '{{ __('messages.enterprise_plan') }}' : '{{ __('messages.subscribe_to_pro') }}'">
        </h2>

        {{-- Free Trial Badge --}}
        @if ($role->isEligibleForTrial())
        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-green-800 dark:text-green-200 font-medium">
                    {{ __('messages.free_trial_badge') }} - {{ __('messages.you_wont_be_charged_until', ['date' => now()->addDays(config('app.trial_days'))->format('F j, Y')]) }}
                </span>
            </div>
        </div>
        @elseif ($role->calculateRemainingTrialDays() > 0)
        <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-blue-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <span class="text-blue-800 dark:text-blue-200 font-medium">
                    {{ __('messages.trial_days_remaining_info', ['days' => $role->calculateRemainingTrialDays()]) }}
                </span>
            </div>
        </div>
        @endif

        {{-- Tier Selection Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
            {{-- Pro Card --}}
            <button type="button" @click="selectedTier = 'pro'" class="relative rounded-xl border-2 p-6 text-left transition-all cursor-pointer" :class="selectedTier === 'pro' ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-900/20 ring-1 ring-indigo-600' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-600'">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ __('messages.pro_plan') }}</h3>
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center" :class="selectedTier === 'pro' ? 'border-indigo-600 bg-indigo-600' : 'border-gray-300 dark:border-gray-600'">
                        <svg x-show="selectedTier === 'pro'" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
                <div class="mb-4">
                    <span class="text-3xl font-bold text-gray-900 dark:text-gray-100" x-text="'$' + prices.pro[selectedPlan]"></span>
                    <span class="text-sm text-gray-500 dark:text-gray-400" x-text="selectedPlan === 'yearly' ? '/{{ __('messages.year') }}' : '/{{ __('messages.month') }}'"></span>
                </div>
                <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        {{ __('messages.feature_white_label') }}
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Ticketing & QR check-ins
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        {{ __('messages.feature_api_access') }}
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        {{ __('messages.feature_boost') }}
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        100 {{ __('messages.newsletters_per_month') }}
                    </li>
                </ul>
            </button>

            {{-- Enterprise Card --}}
            <button type="button" @click="selectedTier = 'enterprise'" class="relative rounded-xl border-2 p-6 text-left transition-all cursor-pointer" :class="selectedTier === 'enterprise' ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20 ring-1 ring-amber-500' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-600'">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ __('messages.enterprise_plan') }}</h3>
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center" :class="selectedTier === 'enterprise' ? 'border-amber-500 bg-amber-500' : 'border-gray-300 dark:border-gray-600'">
                        <svg x-show="selectedTier === 'enterprise'" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
                <div class="mb-4">
                    <span class="text-3xl font-bold text-gray-900 dark:text-gray-100" x-text="'$' + prices.enterprise[selectedPlan]"></span>
                    <span class="text-sm text-gray-500 dark:text-gray-400" x-text="selectedPlan === 'yearly' ? '/{{ __('messages.year') }}' : '/{{ __('messages.month') }}'"></span>
                </div>
                <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        {{ __('messages.everything_in_pro') }}
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        {{ __('messages.feature_team_members') }}
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        {{ __('messages.feature_ai_text') }}
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        {{ __('messages.feature_email_scheduling') }}
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        {{ __('messages.feature_agenda_scanning') }}
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        {{ __('messages.feature_custom_css_enterprise') }}
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        1,000 {{ __('messages.newsletters_per_month') }}
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        {{ __('messages.feature_priority_support') }}
                    </li>
                </ul>
            </button>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-8 border border-gray-100 dark:border-gray-700">
            {{-- Plan Selection (Monthly/Yearly) --}}
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('messages.select_plan') }}</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="relative flex cursor-pointer rounded-lg border bg-white dark:bg-gray-700 p-4 shadow-sm focus:outline-none plan-option" data-plan="monthly" @click="selectedPlan = 'monthly'">
                        <input type="radio" name="plan_radio" value="monthly" class="sr-only" checked>
                        <span class="flex flex-1">
                            <span class="flex flex-col">
                                <span class="block text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('messages.monthly') }}</span>
                                <span class="mt-1 flex items-center text-sm text-gray-500 dark:text-gray-400">{{ __('messages.billed_monthly') }}</span>
                                <span class="mt-4 text-2xl font-semibold text-gray-900 dark:text-gray-100">$<span x-text="prices[selectedTier]['monthly']"></span><span class="text-sm font-normal text-gray-500 dark:text-gray-400">/{{ __('messages.month') }}</span></span>
                            </span>
                        </span>
                        <svg class="h-5 w-5 text-indigo-600 plan-check hidden" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="pointer-events-none absolute -inset-px rounded-lg border-2 plan-border border-transparent" aria-hidden="true"></span>
                    </label>

                    <label class="relative flex cursor-pointer rounded-lg border bg-white dark:bg-gray-700 p-4 shadow-sm focus:outline-none plan-option" data-plan="yearly" @click="selectedPlan = 'yearly'">
                        <input type="radio" name="plan_radio" value="yearly" class="sr-only">
                        <span class="flex flex-1">
                            <span class="flex flex-col">
                                <span class="block text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ __('messages.yearly') }}
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        {{ __('messages.save_17_percent') }}
                                    </span>
                                </span>
                                <span class="mt-1 flex items-center text-sm text-gray-500 dark:text-gray-400">{{ __('messages.billed_yearly') }}</span>
                                <span class="mt-4 text-2xl font-semibold text-gray-900 dark:text-gray-100">$<span x-text="prices[selectedTier]['yearly']"></span><span class="text-sm font-normal text-gray-500 dark:text-gray-400">/{{ __('messages.year') }}</span></span>
                            </span>
                        </span>
                        <svg class="h-5 w-5 text-indigo-600 plan-check hidden" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="pointer-events-none absolute -inset-px rounded-lg border-2 plan-border border-transparent" aria-hidden="true"></span>
                    </label>
                </div>
            </div>

            {{-- Payment Form --}}
            <form id="payment-form" action="{{ route('subscription.store', ['subdomain' => $role->subdomain]) }}" method="POST">
                @csrf
                <input type="hidden" name="plan" id="selected-plan" :value="selectedPlan">
                <input type="hidden" name="tier" id="selected-tier" :value="selectedTier">
                <input type="hidden" name="payment_method" id="payment-method">

                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('messages.payment_details') }}</h3>

                <div class="mb-6">
                    <label for="card-holder-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('messages.card_holder_name') }}
                    </label>
                    <input type="text" id="card-holder-name" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('messages.card_details') }}
                    </label>
                    <div id="card-element" class="block w-full rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 p-3 shadow-sm"></div>
                    <div id="card-errors" class="mt-2 text-sm text-red-600" role="alert"></div>
                </div>

                @if (session('error'))
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <p class="text-sm text-red-600 dark:text-red-400">{{ session('error') }}</p>
                </div>
                @endif

                <div class="flex items-center justify-between pt-4">
                    <a href="{{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'plan']) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-500">
                        {{ __('messages.cancel') }}
                    </a>
                    <button type="submit" id="submit-button" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span id="button-text">{{ __('messages.subscribe') }}</span>
                        <span id="button-spinner" class="hidden ml-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script {!! nonce_attr() !!}>
        const stripe = Stripe('{{ config('cashier.key') }}');
        const elements = stripe.elements();

        const style = {
            base: {
                color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#1f2937',
                fontFamily: 'ui-sans-serif, system-ui, sans-serif',
                fontSmoothing: 'antialiased',
                fontSize: '16px',
                '::placeholder': {
                    color: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280'
                }
            },
            invalid: {
                color: '#ef4444',
                iconColor: '#ef4444'
            }
        };

        const cardElement = elements.create('card', { style: style });
        cardElement.mount('#card-element');

        cardElement.on('change', function(event) {
            const displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });

        // Plan selection (monthly/yearly)
        const planOptions = document.querySelectorAll('.plan-option');
        const selectedPlanInput = document.getElementById('selected-plan');

        planOptions.forEach(option => {
            option.addEventListener('click', function() {
                planOptions.forEach(opt => {
                    opt.querySelector('input').checked = false;
                    opt.querySelector('.plan-check').classList.add('hidden');
                    opt.querySelector('.plan-border').classList.remove('border-indigo-600');
                });

                this.querySelector('input').checked = true;
                this.querySelector('.plan-check').classList.remove('hidden');
                this.querySelector('.plan-border').classList.add('border-indigo-600');
                // Alpine binding handles the hidden field value
            });
        });

        // Initialize first option as selected
        planOptions[0].querySelector('.plan-check').classList.remove('hidden');
        planOptions[0].querySelector('.plan-border').classList.add('border-indigo-600');

        // Form submission
        const form = document.getElementById('payment-form');
        const submitButton = document.getElementById('submit-button');
        const buttonText = document.getElementById('button-text');
        const buttonSpinner = document.getElementById('button-spinner');
        const cardHolderName = document.getElementById('card-holder-name');

        form.addEventListener('submit', async function(event) {
            event.preventDefault();

            submitButton.disabled = true;
            buttonText.textContent = @json(__('messages.processing'));
            buttonSpinner.classList.remove('hidden');

            const { setupIntent, error } = await stripe.confirmCardSetup(
                '{{ $intent->client_secret }}',
                {
                    payment_method: {
                        card: cardElement,
                        billing_details: {
                            name: cardHolderName.value
                        }
                    }
                }
            );

            if (error) {
                const displayError = document.getElementById('card-errors');
                displayError.textContent = error.message;
                submitButton.disabled = false;
                buttonText.textContent = @json(__('messages.subscribe'));
                buttonSpinner.classList.add('hidden');
            } else {
                document.getElementById('payment-method').value = setupIntent.payment_method;
                form.submit();
            }
        });
    </script>

</x-app-admin-layout>
