<x-app-admin-layout>
    <div class="max-w-5xl mx-auto">
        <div class="flex justify-between items-center gap-6 mb-6">
            @if (is_rtl())
                <a href="{{ route('event.edit_admin', $event->hashedId()) }}"
                   class="inline-flex items-center justify-center rounded-md bg-gray-200 dark:bg-gray-700 px-5 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                    {{ __('messages.cancel') }}
                </a>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('messages.boost_event') }}</h1>
            @else
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('messages.boost_event') }}</h1>
                <a href="{{ route('event.edit_admin', $event->hashedId()) }}"
                   class="inline-flex items-center justify-center rounded-md bg-gray-200 dark:bg-gray-700 px-5 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                    {{ __('messages.cancel') }}
                </a>
            @endif
        </div>

        @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-md text-red-700 dark:text-red-300">
            {{ session('error') }}
        </div>
        @endif

        @if ($activeCampaigns >= $maxConcurrent)
        <div class="mb-4 p-4 bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-md text-yellow-700 dark:text-yellow-300">
            {{ __('messages.boost_max_concurrent') }}
        </div>
        @else

        {{-- First-time onboarding --}}
        @if ($isFirstTime)
        <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg">
            <h3 class="font-semibold text-blue-800 dark:text-blue-200 mb-2">{{ __('messages.boost_onboarding_title') }}</h3>
            <p class="text-sm text-blue-700 dark:text-blue-300 mb-2">{{ __('messages.boost_onboarding_body') }}</p>
            <ul class="text-sm text-blue-700 dark:text-blue-300 list-disc list-inside space-y-1">
                <li>{{ __('messages.boost_onboarding_point1') }}</li>
                <li>{{ __('messages.boost_onboarding_point2') }}</li>
                <li>{{ __('messages.boost_onboarding_point3') }}</li>
            </ul>
        </div>
        @endif

        <div class="lg:grid lg:grid-cols-2 lg:gap-8">
        <div>

        {{-- Event summary --}}
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 mb-6">
            <div class="flex items-start gap-4">
                @if ($event->getImageUrl())
                <img src="{{ $event->getImageUrl() }}" alt="{{ $event->name }}" class="w-24 h-24 rounded-lg object-cover flex-shrink-0">
                @endif
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $event->translatedName() }}</h2>
                    @if ($event->starts_at)
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $event->localStartsAt(true) }}</p>
                    @endif
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $event->getVenueDisplayName() }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $role->name }}</p>
                </div>
            </div>
        </div>

        {{-- Warnings --}}
        @if (!empty($defaults['warnings']))
        <div class="mb-6 space-y-2">
            @foreach ($defaults['warnings'] as $warning)
            <div class="p-3 bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-md text-sm text-yellow-700 dark:text-yellow-300">
                {{ $warning }}
            </div>
            @endforeach
        </div>
        @endif

        {{-- Boost credit banner --}}
        @if (!empty($boostCredit) && $boostCredit > 0)
        <div class="mb-0 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg">
            <p class="text-sm text-green-700 dark:text-green-300 font-medium">
                {{ __('messages.you_have') }} {{ $currencySymbol }}{{ number_format($boostCredit, 2) }} {{ __('messages.in_boost_credit') }}
            </p>
        </div>
        @endif

        {{-- Spending limit info --}}
        @if ($isHosted)
        <div class="mb-0 p-4 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                {{ __('messages.boost_limit_info', ['limit' => $currencySymbol . number_format($maxBudget, 0)]) }}
                {{ __('messages.boost_limit_grows') }}
            </p>
        </div>
        @endif

        {{-- Boost form --}}
        <form id="boost-form" class="space-y-6">
            @csrf
            <input type="hidden" name="event_id" value="{{ $event->hashedId() }}">
            <input type="hidden" name="role_id" value="{{ \App\Utils\UrlUtils::encodeId($role->id) }}">
            <input type="hidden" id="payment_intent_id" name="payment_intent_id" value="">
            <input type="hidden" name="budget_type" value="lifetime">

            {{-- Budget slider --}}
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.ad_budget') }}</label>
                <div class="flex items-center gap-4 mb-2">
                    <input type="range" id="budget-slider" min="{{ $minBudget }}" max="{{ min($maxBudget, 500) }}" step="5"
                        value="{{ $defaults['budget'] }}"
                        class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer">
                    <span id="budget-display" class="text-2xl font-bold text-gray-900 dark:text-white min-w-[80px] {{ is_rtl() ? 'text-left' : 'text-right' }}">{{ $currencySymbol }}{{ number_format($defaults['budget'], 0) }}</span>
                </div>
                <input type="hidden" id="budget-input" name="budget" value="{{ $defaults['budget'] }}">

                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    {{ __('messages.boost_duration_text', ['days' => $defaults['duration_days'], 'date' => $defaults['scheduled_end']->format('M j, Y')]) }}
                </p>

                {{-- Cost breakdown --}}
                <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg text-sm">
                    <div class="flex justify-between text-gray-600 dark:text-gray-300">
                        <span>{{ __('messages.ad_budget') }}</span>
                        <span id="cost-budget">{{ $currencySymbol }}{{ number_format($defaults['budget'], 2) }}</span>
                    </div>
                    @if ($isHosted)
                    <div class="flex justify-between text-gray-600 dark:text-gray-300 mt-1">
                        <span>{{ __('messages.service_fee') }} ({{ intval($markupRate * 100) }}%)</span>
                        <span id="cost-fee">{{ $currencySymbol }}{{ number_format($defaults['budget'] * $markupRate, 2) }}</span>
                    </div>
                    <div class="flex justify-between font-semibold text-gray-900 dark:text-white mt-2 pt-2 border-t border-gray-200 dark:border-gray-600">
                        <span>{{ __('messages.total') }}</span>
                        <span id="cost-total">{{ $currencySymbol }}{{ number_format($defaults['budget'] * (1 + $markupRate), 2) }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Ad preview (mobile only; desktop version in right column) --}}
            <div class="lg:hidden">
                <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('messages.ad_preview') }}</h3>
                    @include('boost.partials.ad-preview-mockup', [
                        'headline' => $defaults['headline'],
                        'primaryText' => $defaults['primary_text'],
                        'imageUrl' => $defaults['image_url'],
                        'cta' => $defaults['call_to_action'],
                    ])
                </div>
            </div>

            {{-- Credit payment (shown when credit covers full cost) --}}
            <div id="credit-payment-section" class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 hidden">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('messages.payment') }}</h3>
                <div class="flex items-center gap-2 text-green-700 dark:text-green-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm font-medium" id="credit-payment-text">{{ __('messages.will_be_paid_with_boost_credit') }}</span>
                </div>
            </div>

            {{-- Stripe/testing payment --}}
            @if (!empty($isTesting) || empty($isHosted))
            <div id="stripe-payment-section" class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('messages.payment') }}</h3>
                @if (!empty($isTesting))
                <p class="text-sm text-yellow-600 dark:text-yellow-400">Testing mode: payment will be skipped.</p>
                @endif
                <div id="payment-errors" class="text-sm text-red-600 dark:text-red-400 hidden"></div>
            </div>
            @else
            <div id="stripe-payment-section" class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('messages.payment') }}</h3>
                @if (!empty($pmLastFour))
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                    {{ __('messages.saved_card_on_file', ['brand' => ucfirst($pmType ?? 'card'), 'last4' => $pmLastFour]) }}
                </p>
                @endif
                <div id="payment-element" class="mb-4"></div>
                <div id="payment-errors" class="text-sm text-red-600 dark:text-red-400 hidden"></div>
            </div>
            @endif

            {{-- Submit --}}
            <div class="flex items-center justify-between">
                <a href="{{ route('boost.create', ['event_id' => $event->hashedId(), 'role_id' => \App\Utils\UrlUtils::encodeId($role->id), 'advanced' => 1]) }}"
                   class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                    {{ __('messages.customize_targeting_creative') }}
                </a>
                <button type="submit" id="submit-btn"
                    class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <span id="submit-text">{{ __('messages.boost_for') }} {{ $currencySymbol }}<span id="submit-amount">{{ $isHosted ? number_format($defaults['budget'] * (1 + $markupRate), 2) : number_format($defaults['budget'], 2) }}</span></span>
                    <span id="submit-spinner" class="hidden ml-2">
                        <svg class="animate-spin h-5 w-5 text-white" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </span>
                </button>
            </div>
        </form>

        </div>

        {{-- Right column: ad preview (desktop only, sticky) --}}
        <div class="hidden lg:block">
            <div class="lg:sticky lg:top-6">
                <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('messages.ad_preview') }}</h3>
                    @include('boost.partials.ad-preview-mockup', [
                        'headline' => $defaults['headline'],
                        'primaryText' => $defaults['primary_text'],
                        'imageUrl' => $defaults['image_url'],
                        'cta' => $defaults['call_to_action'],
                    ])
                </div>
            </div>
        </div>

        </div>
        @endif
    </div>

    <script {!! nonce_attr() !!}>
        // Budget slider (in its own script block so it works even if Stripe fails)
        const markupRate = {{ $markupRate }};
        const currencySymbol = '{{ $currencySymbol }}';
        const boostCredit = {{ $boostCredit ?? 0 }};

        const slider = document.getElementById('budget-slider');
        const budgetDisplay = document.getElementById('budget-display');
        const budgetInput = document.getElementById('budget-input');
        const costBudget = document.getElementById('cost-budget');
        const costFee = document.getElementById('cost-fee');
        const costTotal = document.getElementById('cost-total');
        const submitAmount = document.getElementById('submit-amount');
        const submitBtn = document.getElementById('submit-btn');
        const creditPaymentSection = document.getElementById('credit-payment-section');
        const stripePaymentSection = document.getElementById('stripe-payment-section');

        let usingCredit = false;

        function updateCosts() {
            const budget = parseFloat(slider.value);
            const fee = budget * markupRate;
            const total = budget + fee;

            budgetDisplay.textContent = currencySymbol + budget.toFixed(0);
            budgetInput.value = budget;
            costBudget.textContent = currencySymbol + budget.toFixed(2);
            @if ($isHosted)
            costFee.textContent = currencySymbol + fee.toFixed(2);
            costTotal.textContent = currencySymbol + total.toFixed(2);
            submitAmount.textContent = total.toFixed(2);
            @else
            submitAmount.textContent = budget.toFixed(2);
            @endif

            // Toggle credit vs Stripe payment
            const totalCost = @if ($isHosted) total @else budget @endif;
            if (boostCredit >= totalCost && boostCredit > 0) {
                usingCredit = true;
                creditPaymentSection.classList.remove('hidden');
                stripePaymentSection.classList.add('hidden');
            } else {
                usingCredit = false;
                creditPaymentSection.classList.add('hidden');
                stripePaymentSection.classList.remove('hidden');
            }
        }

        slider.addEventListener('input', updateCosts);
        // Run on load to set initial state
        updateCosts();
    </script>

    @if (!empty($isTesting) || empty($isHosted))
    <script {!! nonce_attr() !!}>
        // Direct submission without Stripe (selfhosted or testing mode)
        document.getElementById('boost-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitSpinner = document.getElementById('submit-spinner');
            const paymentErrors = document.getElementById('payment-errors');

            submitBtn.disabled = true;
            submitSpinner.classList.remove('hidden');
            paymentErrors.classList.add('hidden');

            const formData = new FormData(document.getElementById('boost-form'));

            try {
                const response = await fetch('{{ route("boost.store") }}', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData,
                });

                const data = await response.json();

                if (data.redirect) {
                    window.location.href = data.redirect;
                } else if (data.error) {
                    paymentErrors.textContent = data.error;
                    paymentErrors.classList.remove('hidden');
                    submitBtn.disabled = false;
                    submitSpinner.classList.add('hidden');
                } else {
                    window.location.href = '{{ route("boost.index") }}';
                }
            } catch (err) {
                paymentErrors.textContent = @json(__("messages.boost_store_error"));
                paymentErrors.classList.remove('hidden');
                submitBtn.disabled = false;
                submitSpinner.classList.add('hidden');
            }
        });
    </script>
    @else
    <script src="https://js.stripe.com/v3/" {!! nonce_attr() !!}></script>
    <script {!! nonce_attr() !!}>
        const stripe = Stripe('{{ $stripeKey }}');
        let elements, paymentElement;
        let clientSecret = null;
        let intentBudget = null;
        let pendingPayment = false;

        // Helper: submit form directly (for credit or non-Stripe)
        async function submitFormDirectly() {
            const formData = new FormData(document.getElementById('boost-form'));
            const response = await fetch('{{ route("boost.store") }}', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData,
            });
            return await response.json();
        }

        // Initialize Stripe Payment Element
        async function initPayment() {
            if (usingCredit) {
                // Skip Stripe init when using credit
                intentBudget = parseFloat(slider.value);
                return;
            }

            const budget = parseFloat(slider.value);
            const previousPaymentIntentId = document.getElementById('payment_intent_id').value || null;
            const response = await fetch('{{ route("boost.payment_intent") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                },
                body: JSON.stringify({
                    event_id: '{{ $event->hashedId() }}',
                    role_id: '{{ \App\Utils\UrlUtils::encodeId($role->id) }}',
                    budget: budget,
                    previous_payment_intent_id: previousPaymentIntentId,
                }),
            });

            const data = await response.json();
            if (data.error) {
                document.getElementById('payment-errors').textContent = data.error;
                document.getElementById('payment-errors').classList.remove('hidden');
                return;
            }

            // Server says credit covers it
            if (data.use_credit) {
                usingCredit = true;
                intentBudget = budget;
                creditPaymentSection.classList.remove('hidden');
                stripePaymentSection.classList.add('hidden');
                return;
            }

            clientSecret = data.client_secret;
            intentBudget = budget;
            document.getElementById('payment_intent_id').value = data.payment_intent_id;

            if (paymentElement) {
                paymentElement.destroy();
                paymentElement = null;
            }

            const isDarkMode = document.documentElement.classList.contains('dark');
            const appearance = {
                theme: isDarkMode ? 'night' : 'stripe',
            };
            const elementsOptions = { clientSecret, appearance };
            if (data.customer_session_client_secret) {
                elementsOptions.customerSessionClientSecret = data.customer_session_client_secret;
            }
            elements = stripe.elements(elementsOptions);
            paymentElement = elements.create('payment', {
                paymentMethodOrder: ['card'],
            });
            paymentElement.mount('#payment-element');
        }

        // Re-create payment intent when budget changes (debounced)
        let debounceTimer;
        slider.addEventListener('change', function() {
            clearTimeout(debounceTimer);
            submitBtn.disabled = true;
            debounceTimer = setTimeout(async () => {
                pendingPayment = true;
                try {
                    await initPayment();
                } catch (err) {
                    document.getElementById('payment-errors').textContent = @json(__("messages.payment_error"));
                    document.getElementById('payment-errors').classList.remove('hidden');
                }
                pendingPayment = false;
                submitBtn.disabled = false;
            }, 500);
        });

        // Form submission
        document.getElementById('boost-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitText = document.getElementById('submit-text');
            const submitSpinner = document.getElementById('submit-spinner');
            const paymentErrors = document.getElementById('payment-errors');

            submitBtn.disabled = true;
            submitSpinner.classList.remove('hidden');
            paymentErrors.classList.add('hidden');

            // Credit payment path - submit directly without Stripe
            if (usingCredit) {
                try {
                    const data = await submitFormDirectly();
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else if (data.error) {
                        paymentErrors.textContent = data.error;
                        paymentErrors.classList.remove('hidden');
                        submitBtn.disabled = false;
                        submitSpinner.classList.add('hidden');
                    } else {
                        window.location.href = '{{ route("boost.index") }}';
                    }
                } catch (err) {
                    paymentErrors.textContent = @json(__("messages.boost_store_error"));
                    paymentErrors.classList.remove('hidden');
                    submitBtn.disabled = false;
                    submitSpinner.classList.add('hidden');
                }
                return;
            }

            // Ensure payment intent matches current budget
            const currentBudget = parseFloat(slider.value);
            if (!clientSecret || intentBudget !== currentBudget) {
                try {
                    await initPayment();
                } catch (err) {
                    paymentErrors.textContent = @json(__("messages.payment_error"));
                    paymentErrors.classList.remove('hidden');
                    submitBtn.disabled = false;
                    submitSpinner.classList.add('hidden');
                    return;
                }
            }

            // Check again in case initPayment switched to credit
            if (usingCredit) {
                try {
                    const data = await submitFormDirectly();
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else if (data.error) {
                        paymentErrors.textContent = data.error;
                        paymentErrors.classList.remove('hidden');
                        submitBtn.disabled = false;
                        submitSpinner.classList.add('hidden');
                    } else {
                        window.location.href = '{{ route("boost.index") }}';
                    }
                } catch (err) {
                    paymentErrors.textContent = @json(__("messages.boost_store_error"));
                    paymentErrors.classList.remove('hidden');
                    submitBtn.disabled = false;
                    submitSpinner.classList.add('hidden');
                }
                return;
            }

            const { error, paymentIntent } = await stripe.confirmPayment({
                elements,
                confirmParams: {
                    return_url: window.location.href,
                },
                redirect: 'if_required',
            });

            if (error) {
                paymentErrors.textContent = error.message;
                paymentErrors.classList.remove('hidden');
                submitBtn.disabled = false;
                submitSpinner.classList.add('hidden');
                return;
            }

            if (paymentIntent && paymentIntent.status === 'succeeded') {
                // Submit the form with the payment intent ID
                const formData = new FormData(document.getElementById('boost-form'));
                formData.set('payment_intent_id', paymentIntent.id);

                try {
                    const response = await fetch('{{ route("boost.store") }}', {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: formData,
                    });

                    const data = await response.json();

                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else if (data.error) {
                        paymentErrors.textContent = data.error;
                        paymentErrors.classList.remove('hidden');
                        submitBtn.disabled = false;
                        submitSpinner.classList.add('hidden');
                    } else if (data.message) {
                        paymentErrors.textContent = data.message;
                        paymentErrors.classList.remove('hidden');
                        submitBtn.disabled = false;
                        submitSpinner.classList.add('hidden');
                    } else {
                        window.location.href = '{{ route("boost.index") }}';
                    }
                } catch (err) {
                    paymentErrors.textContent = @json(__("messages.boost_store_error")) + ' (ref: ' + paymentIntent.id + ')';
                    paymentErrors.classList.remove('hidden');
                    submitBtn.disabled = false;
                    submitSpinner.classList.add('hidden');
                }
            }
        });

        // Init on load
        initPayment().catch(function() {
            document.getElementById('payment-errors').textContent = @json(__("messages.payment_error"));
            document.getElementById('payment-errors').classList.remove('hidden');
        });
    </script>
    @endif
</x-app-admin-layout>
