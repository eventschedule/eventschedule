<x-app-admin-layout>

    <x-slot name="head">
        @include('boost.partials.styles')
    </x-slot>

    @php
        // The form is drawn, and its scripts with it, only while another campaign may be started.
        $boostFormOpen = $activeCampaigns < $maxConcurrent;
        // A new schedule's spending limit is the smallest budget there is, which leaves the slider
        // with one stop. It is not drawn then: a slider that cannot move reads as a broken page.
        $sliderMax = min($maxBudget, 500);
        $sliderFixed = $sliderMax <= $minBudget;
    @endphp

    {{-- The short way to boost an event on Facebook and Instagram: a budget and a button, with the
         ad the event's own details make. The longer way (boost/create-advanced) is a link at the
         foot of the form. --}}
    <div class="page-shell">
        <x-page-header :title="__('messages.boost_event')" :lead="__('messages.boost_create_lead')"
            :back="route('boost.index')" :back-label="__('messages.boost')" />

        <x-page-flash :keys="['error' => 'error']" class="mb-4" />

        @if (! $boostFormOpen)
        <x-page-notice tone="warn">
            {{ __('messages.boost_max_concurrent') }}
            <x-slot name="action"><a href="{{ route('boost.index') }}" class="event-link">{{ __('messages.boost') }}</a></x-slot>
        </x-page-notice>
        @else

        <div class="boost-cols">
        <div class="boost-cols-main">

        {{-- First-time onboarding --}}
        @if ($isFirstTime)
        <x-page-notice tone="info" :title="__('messages.boost_onboarding_title')">
            <p>{{ __('messages.boost_onboarding_body') }}</p>
            <ul class="mt-1 list-disc list-inside">
                <li>{{ __('messages.boost_onboarding_point1') }}</li>
                <li>{{ __('messages.boost_onboarding_point2') }}</li>
                <li>{{ __('messages.boost_onboarding_point3') }}</li>
            </ul>
        </x-page-notice>
        @endif

        {{-- Event summary --}}
        <section class="ap-card rounded-xl page-card">
            <div class="boost-event">
                @if ($event->getImageUrl())
                <img src="{{ $event->getImageUrl() }}" alt="">
                @endif
                <div class="min-w-0">
                    <h2><bdi>{{ $event->translatedName() }}</bdi></h2>
                    @if ($event->starts_at)
                    <p>{{ $event->localStartsAt(true) }}</p>
                    @endif
                    <p><bdi>{{ $event->getVenueDisplayName() }}</bdi></p>
                    <p><bdi>{{ $role->name }}</bdi></p>
                </div>
            </div>
        </section>

        {{-- Warnings --}}
        @if (!empty($defaults['warnings']))
            @foreach ($defaults['warnings'] as $warning)
            <x-page-notice tone="warn">{{ $warning }}</x-page-notice>
            @endforeach
        @endif

        {{-- Boost credit banner --}}
        @if (!empty($boostCredit) && $boostCredit > 0)
        <x-page-notice tone="success">
            {{ __('messages.you_have') }} {{ $currencySymbol }}{{ number_format($boostCredit, 2) }} {{ __('messages.in_boost_credit') }}
        </x-page-notice>
        @endif

        {{-- Boost form --}}
        <form id="boost-form">
            @csrf
            <input type="hidden" name="event_id" value="{{ $event->hashedId() }}">
            <input type="hidden" name="role_id" value="{{ \App\Utils\UrlUtils::encodeId($role->id) }}">
            <input type="hidden" id="payment_intent_id" name="payment_intent_id" value="">
            <input type="hidden" name="budget_type" value="lifetime">

            {{-- Budget slider --}}
            <section class="ap-card rounded-xl page-card">
                <div class="page-card-head">
                    <div class="min-w-0">
                        <h2 class="page-card-title"><label for="budget-slider">{{ __('messages.ad_budget') }}</label></h2>
                        {{-- Spending limit info --}}
                        @if ($isHosted)
                        <p class="page-card-lead">
                            {{ __('messages.boost_limit_info', ['limit' => $currencySymbol . number_format($maxBudget, 0)]) }}
                            {{ __('messages.boost_limit_grows') }}
                        </p>
                        @endif
                    </div>
                </div>
                <div class="boost-budget">
                    <input type="range" id="budget-slider" min="{{ $minBudget }}" max="{{ $sliderMax }}" step="5"
                        value="{{ $defaults['budget'] }}" @if ($sliderFixed) hidden @endif>
                    <span id="budget-display" class="boost-budget-figure" @if ($sliderFixed) style="text-align: start" @endif>{{ $currencySymbol }}{{ number_format($defaults['budget'], 0) }}</span>
                </div>
                <input type="hidden" id="budget-input" name="budget" value="{{ $defaults['budget'] }}">

                <p class="boost-note">
                    {{ __('messages.boost_duration_text', ['days' => $defaults['duration_days'], 'date' => $defaults['scheduled_end']->translatedFormat('M j, Y')]) }}
                </p>

                {{-- Cost breakdown --}}
                <div class="boost-costs">
                    <dl class="page-kv">
                        <div>
                            <dt>{{ __('messages.ad_budget') }}</dt>
                            <dd id="cost-budget">{{ $currencySymbol }}{{ number_format($defaults['budget'], 2) }}</dd>
                        </div>
                        @if ($isHosted)
                        <div>
                            <dt>{{ __('messages.service_fee') }} ({{ intval($markupRate * 100) }}%)</dt>
                            <dd id="cost-fee">{{ $currencySymbol }}{{ number_format($defaults['budget'] * $markupRate, 2) }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('messages.total') }}</dt>
                            <dd id="cost-total">{{ $currencySymbol }}{{ number_format($defaults['budget'] * (1 + $markupRate), 2) }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </section>

            {{-- Credit payment (shown when credit covers full cost) --}}
            <section id="credit-payment-section" class="ap-card rounded-xl page-card is-after-preview hidden">
                <div class="page-card-head"><h2 class="page-card-title">{{ __('messages.payment') }}</h2></div>
                <span class="event-status is-on" id="credit-payment-text">{{ __('messages.will_be_paid_with_boost_credit') }}</span>
            </section>

            {{-- Stripe/testing payment --}}
            @if (!empty($isTesting))
            <section id="stripe-payment-section" class="ap-card rounded-xl page-card is-after-preview">
                <div class="page-card-head"><h2 class="page-card-title">{{ __('messages.payment') }}</h2></div>
                <x-page-notice tone="warn">{{ __('messages.boost_testing_mode') }}</x-page-notice>
                <div id="payment-errors" class="mt-3 text-sm text-red-600 dark:text-red-400 hidden" role="alert"></div>
            </section>
            @elseif (empty($isHosted))
            {{-- A selfhosted installation pays Meta from its own ad account, so there is nothing to
                 pay here. The element stays, without a card around it, because the script shows
                 it and writes a refusal into it: it used to be an empty card headed "Payment". --}}
            <div id="stripe-payment-section" class="is-after-preview">
                <div id="payment-errors" class="text-sm text-red-600 dark:text-red-400 hidden" role="alert"></div>
            </div>
            @else
            <section id="stripe-payment-section" class="ap-card rounded-xl page-card is-after-preview">
                <div class="page-card-head"><h2 class="page-card-title">{{ __('messages.payment') }}</h2></div>
                @if (!empty($pmLastFour))
                <p class="boost-note" style="margin: 0 0 0.75rem">
                    {{ __('messages.saved_card_on_file', ['brand' => ucfirst($pmType ?? 'card'), 'last4' => $pmLastFour]) }}
                </p>
                @endif
                <div id="payment-element" class="mb-4"></div>
                <div id="payment-errors" class="text-sm text-red-600 dark:text-red-400 hidden" role="alert"></div>
            </section>
            @endif

            {{-- Submit: the other way to do this at the start of the row, then Cancel, and the
                 button that spends the money last. --}}
            <div class="page-form-actions is-split is-after-preview" style="margin-top: 0">
                <a href="{{ route('boost.create', ['event_id' => $event->hashedId(), 'role_id' => \App\Utils\UrlUtils::encodeId($role->id), 'advanced' => 1]) }}"
                   class="event-link">
                    {{ __('messages.customize_targeting_creative') }}
                </a>
                <div class="page-actions">
                    <x-secondary-link href="{{ route('boost.index') }}" class="js-cancel-btn">{{ __('messages.cancel') }}</x-secondary-link>
                    <x-brand-button type="submit" id="submit-btn">
                        <span id="submit-text">{{ __('messages.boost_for') }} {{ $currencySymbol }}<span id="submit-amount">{{ $isHosted ? number_format($defaults['budget'] * (1 + $markupRate), 2) : number_format($defaults['budget'], 2) }}</span></span>
                        <span id="submit-spinner" class="hidden ms-2">
                            <svg class="animate-spin h-5 w-5 text-white" viewBox="0 0 24 24" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </span>
                    </x-brand-button>
                </div>
            </div>
        </form>

        </div>

        {{-- The ad as it will look: beside the form from a laptop up, and between the budget and
             the payment on a phone. One copy (it was drawn twice, once for each width). --}}
        <aside class="boost-cols-aside">
            <x-page-card :title="__('messages.ad_preview')">
                @include('boost.partials.ad-preview-mockup', [
                    'headline' => $defaults['headline'],
                    'primaryText' => $defaults['primary_text'],
                    'imageUrl' => $defaults['image_url'],
                    'cta' => $defaults['call_to_action'],
                ])
            </x-page-card>
        </aside>

        </div>
        @endif
    </div>

    {{-- The scripts read the form by element id, so they are sent only with the form: on the page
         that says the limit of running campaigns is reached they threw on the first line. --}}
    @if ($boostFormOpen)
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

                if (!response.ok) throw new Error('Request failed');
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
            if (!response.ok) throw new Error('Request failed');
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

            if (!response.ok) throw new Error('Request failed');
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

                    if (!response.ok) throw new Error('Request failed');
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
    @endif
</x-app-admin-layout>
