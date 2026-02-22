<x-app-admin-layout>
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('boost.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">&larr; {{ __('messages.back_to_boost') }}</a>
        </div>

        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">{{ __('messages.boost_event') }}</h1>

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
                    <div class="flex justify-between text-gray-600 dark:text-gray-300 mt-1">
                        <span>{{ __('messages.service_fee') }} ({{ intval($markupRate * 100) }}%)</span>
                        <span id="cost-fee">{{ $currencySymbol }}{{ number_format($defaults['budget'] * $markupRate, 2) }}</span>
                    </div>
                    <div class="flex justify-between font-semibold text-gray-900 dark:text-white mt-2 pt-2 border-t border-gray-200 dark:border-gray-600">
                        <span>{{ __('messages.total') }}</span>
                        <span id="cost-total">{{ $currencySymbol }}{{ number_format($defaults['budget'] * (1 + $markupRate), 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Ad preview --}}
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('messages.ad_preview') }}</h3>
                @include('boost.partials.ad-preview-mockup', [
                    'headline' => $defaults['headline'],
                    'primaryText' => $defaults['primary_text'],
                    'imageUrl' => $defaults['image_url'],
                    'cta' => $defaults['call_to_action'],
                ])
            </div>

            {{-- Payment --}}
            @if (!empty($isTesting))
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('messages.payment') }}</h3>
                <p class="text-sm text-yellow-600 dark:text-yellow-400">Testing mode: payment will be skipped.</p>
                <div id="payment-errors" class="text-sm text-red-600 dark:text-red-400 hidden"></div>
            </div>
            @else
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('messages.payment') }}</h3>
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
                    <span id="submit-text">{{ __('messages.boost_for') }} {{ $currencySymbol }}<span id="submit-amount">{{ number_format($defaults['budget'] * (1 + $markupRate), 2) }}</span></span>
                    <span id="submit-spinner" class="hidden ml-2">
                        <svg class="animate-spin h-5 w-5 text-white" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </span>
                </button>
            </div>
        </form>
        @endif
    </div>

    <script {!! nonce_attr() !!}>
        // Budget slider (in its own script block so it works even if Stripe fails)
        const markupRate = {{ $markupRate }};
        const currencySymbol = '{{ $currencySymbol }}';

        const slider = document.getElementById('budget-slider');
        const budgetDisplay = document.getElementById('budget-display');
        const budgetInput = document.getElementById('budget-input');
        const costBudget = document.getElementById('cost-budget');
        const costFee = document.getElementById('cost-fee');
        const costTotal = document.getElementById('cost-total');
        const submitAmount = document.getElementById('submit-amount');
        const submitBtn = document.getElementById('submit-btn');

        function updateCosts() {
            const budget = parseFloat(slider.value);
            const fee = budget * markupRate;
            const total = budget + fee;

            budgetDisplay.textContent = currencySymbol + budget.toFixed(0);
            budgetInput.value = budget;
            costBudget.textContent = currencySymbol + budget.toFixed(2);
            costFee.textContent = currencySymbol + fee.toFixed(2);
            costTotal.textContent = currencySymbol + total.toFixed(2);
            submitAmount.textContent = total.toFixed(2);
        }

        slider.addEventListener('input', updateCosts);
    </script>

    @if (!empty($isTesting))
    <script {!! nonce_attr() !!}>
        // Testing mode: submit directly without Stripe
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
                paymentErrors.textContent = '{{ __("messages.boost_store_error") }}';
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

        // Initialize Stripe Payment Element
        async function initPayment() {
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

            clientSecret = data.client_secret;
            intentBudget = budget;
            document.getElementById('payment_intent_id').value = data.payment_intent_id;

            if (paymentElement) {
                paymentElement.destroy();
                paymentElement = null;
            }

            elements = stripe.elements({ clientSecret });
            paymentElement = elements.create('payment');
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
                    document.getElementById('payment-errors').textContent = '{{ __("messages.payment_error") }}';
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

            // Ensure payment intent matches current budget
            const currentBudget = parseFloat(slider.value);
            if (!clientSecret || intentBudget !== currentBudget) {
                try {
                    await initPayment();
                } catch (err) {
                    paymentErrors.textContent = '{{ __("messages.payment_error") }}';
                    paymentErrors.classList.remove('hidden');
                    submitBtn.disabled = false;
                    submitSpinner.classList.add('hidden');
                    return;
                }
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
                    paymentErrors.textContent = '{{ __("messages.boost_store_error") }}' + ' (ref: ' + paymentIntent.id + ')';
                    paymentErrors.classList.remove('hidden');
                    submitBtn.disabled = false;
                    submitSpinner.classList.add('hidden');
                }
            }
        });

        // Init on load
        initPayment().catch(function() {
            document.getElementById('payment-errors').textContent = '{{ __("messages.payment_error") }}';
            document.getElementById('payment-errors').classList.remove('hidden');
        });
    </script>
    @endif
</x-app-admin-layout>
