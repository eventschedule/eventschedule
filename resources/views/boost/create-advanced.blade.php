<x-app-admin-layout>

    <x-slot name="head">
        @include('boost.partials.styles')
    </x-slot>

    @php
        // The card is asked for where a card is charged: on the hosted service, outside testing.
        // In testing the page used to call Stripe with no key, which threw and took the whole
        // script with it (the interest search, the totals, Launch).
        $useStripe = $isHosted && empty($isTesting);
        $boostField = 'block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]';
        $boostCheck = 'rounded border-gray-300 dark:border-gray-600 text-[var(--brand-blue)] shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]';
    @endphp

    {{-- The long way to boost an event on Facebook and Instagram: four steps down one column, each
         a card, then the button that spends the money. --}}
    <div class="page-shell page-col is-narrow">
        <x-page-header :title="__('messages.advanced_boost')" :lead="$event->translatedName()"
            :back="route('boost.index')" :back-label="__('messages.boost')" />

        <x-page-flash :keys="['error' => 'error']" class="mb-4" />

        <form id="boost-form" class="page-stack">
            @csrf
            <input type="hidden" name="event_id" value="{{ $event->hashedId() }}">
            <input type="hidden" name="role_id" value="{{ \App\Utils\UrlUtils::encodeId($role->id) }}">
            <input type="hidden" id="payment_intent_id" name="payment_intent_id" value="">

            {{-- Step 1: Budget & Duration --}}
            <x-page-card :title="'1. '.__('messages.budget_and_duration')"
                :lead="$isHosted ? __('messages.boost_limit_info', ['limit' => $currencySymbol . number_format($maxBudget, 0)]).' '.__('messages.boost_limit_grows') : null">
                <div class="boost-fields">
                    <div class="boost-fields-2 is-words">
                        <div>
                            <x-input-label for="budget-input" :value="__('messages.ad_budget').' ('.$currencySymbol.')'" />
                            <input type="number" name="budget" id="budget-input" value="{{ $defaults['budget'] }}"
                                min="{{ $minBudget }}" max="{{ $maxBudget }}" step="1" inputmode="numeric"
                                class="mt-1 {{ $boostField }}">
                        </div>
                        <div>
                            <x-input-label for="budget-type" :value="__('messages.budget_type')" />
                            <select name="budget_type" id="budget-type" class="mt-1 {{ $boostField }}">
                                <option value="lifetime" selected>{{ __('messages.lifetime_budget') }}</option>
                                <option value="daily">{{ __('messages.daily_budget') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="boost-fields-2">
                        <div>
                            <x-input-label for="scheduled-start" :value="__('messages.start_date')" />
                            <input type="text" name="scheduled_start" id="scheduled-start" value="{{ $defaults['scheduled_start']->format('Y-m-d') }}"
                                autocomplete="off" class="mt-1 boost-date {{ $boostField }}">
                        </div>
                        <div>
                            <x-input-label for="scheduled-end" :value="__('messages.end_date')" />
                            <input type="text" name="scheduled_end" id="scheduled-end" value="{{ $defaults['scheduled_end']->format('Y-m-d') }}"
                                autocomplete="off" class="mt-1 boost-date {{ $boostField }}">
                        </div>
                    </div>

                    <div>
                        <x-input-label for="objective" :value="__('messages.objective')" />
                        <select name="objective" id="objective" class="mt-1 {{ $boostField }}">
                            <option value="OUTCOME_AWARENESS">{{ __('messages.objective_awareness') }}</option>
                            <option value="OUTCOME_TRAFFIC">{{ __('messages.objective_traffic') }}</option>
                            <option value="OUTCOME_ENGAGEMENT">{{ __('messages.objective_engagement') }}</option>
                        </select>
                    </div>
                </div>
            </x-page-card>

            {{-- Step 2: Targeting --}}
            <x-page-card :title="'2. '.__('messages.targeting')">
                <div class="boost-fields">
                    @if (!empty($geoDescription))
                    <div class="boost-fixed">
                        <strong>{{ __('messages.location') }}</strong>
                        <bdi>{{ $geoDescription }}</bdi>
                    </div>
                    @endif

                    <div class="boost-fields-2">
                        <div>
                            <x-input-label for="age-min" :value="__('messages.age_min')" />
                            <input type="number" id="age-min" value="{{ $defaults['targeting']['age_min'] ?? 18 }}" min="18" max="65"
                                class="mt-1 {{ $boostField }}">
                        </div>
                        <div>
                            <x-input-label for="age-max" :value="__('messages.age_max')" />
                            <input type="number" id="age-max" value="{{ $defaults['targeting']['age_max'] ?? 65 }}" min="18" max="65"
                                class="mt-1 {{ $boostField }}">
                        </div>
                    </div>

                    <div>
                        <x-input-label for="interest-search" :value="__('messages.interests')" />
                        <input type="text" id="interest-search" placeholder="{{ __('messages.search_interests') }}" autocomplete="off"
                            class="mt-1 {{ $boostField }}">
                        <div id="interest-results" class="mt-1 hidden border border-gray-200 dark:border-gray-600 rounded-lg max-h-40 overflow-y-auto"></div>
                        <div id="selected-interests" class="flex flex-wrap gap-2 mt-2"></div>
                    </div>

                    <fieldset>
                        <legend class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.placements') }}</legend>
                        <div class="boost-checks">
                            <label class="boost-check">
                                <input type="checkbox" name="placement_facebook_feed" value="1" checked class="{{ $boostCheck }}">
                                <span>{{ __('messages.placement_facebook_feed') }}</span>
                            </label>
                            <label class="boost-check">
                                <input type="checkbox" name="placement_instagram" value="1" checked class="{{ $boostCheck }}">
                                <span>{{ __('messages.placement_instagram') }}</span>
                            </label>
                        </div>
                    </fieldset>

                    <input type="hidden" name="targeting" id="targeting-json" value="{{ json_encode($defaults['targeting']) }}">
                    <input type="hidden" name="placements" id="placements-json" value="">
                </div>
            </x-page-card>

            {{-- Step 3: Creative --}}
            <x-page-card :title="'3. '.__('messages.creative')">
                <div class="boost-fields">
                    @if ($roleLanguage && $roleLanguage !== 'en')
                    <div class="boost-fixed">
                        <label class="boost-check">
                            <input type="checkbox" name="translate_to_english" id="translate-to-english" value="1" class="{{ $boostCheck }}">
                            <span>{{ __('messages.translate_ad_to_english') }}</span>
                        </label>
                        <p class="boost-note" style="margin-top: 0.25rem">{{ __('messages.translate_ad_to_english_desc') }}</p>
                    </div>
                    @endif

                    <div>
                        <x-input-label for="headline" :value="__('messages.headline').' ('.__('messages.max_chars', ['count' => 40]).')'" />
                        <input type="text" name="headline" id="headline" value="{{ $defaults['headline'] }}" maxlength="40" dir="auto"
                            class="mt-1 {{ $boostField }}">
                    </div>

                    <div>
                        <x-input-label for="primary-text" :value="__('messages.primary_text').' ('.__('messages.max_chars', ['count' => 125]).')'" />
                        <textarea name="primary_text" id="primary-text" maxlength="125" rows="2" dir="auto"
                            class="mt-1 {{ $boostField }}">{{ $defaults['primary_text'] }}</textarea>
                    </div>

                    <div>
                        <x-input-label for="description" :value="__('messages.description').' ('.__('messages.max_chars', ['count' => 30]).')'" />
                        <input type="text" name="description" id="description" value="{{ $defaults['description'] }}" maxlength="30" dir="auto"
                            class="mt-1 {{ $boostField }}">
                    </div>

                    <div>
                        <x-input-label for="call-to-action" :value="__('messages.call_to_action')" />
                        <select name="call_to_action" id="call-to-action" class="mt-1 {{ $boostField }}">
                            <option value="LEARN_MORE" {{ $defaults['call_to_action'] === 'LEARN_MORE' ? 'selected' : '' }}>{{ __('messages.cta_learn_more') }}</option>
                            <option value="GET_TICKETS" {{ $defaults['call_to_action'] === 'GET_TICKETS' ? 'selected' : '' }}>{{ __('messages.cta_get_tickets') }}</option>
                            <option value="SIGN_UP" {{ $defaults['call_to_action'] === 'SIGN_UP' ? 'selected' : '' }}>{{ __('messages.cta_sign_up') }}</option>
                            <option value="BOOK_TRAVEL" {{ $defaults['call_to_action'] === 'BOOK_TRAVEL' ? 'selected' : '' }}>{{ __('messages.cta_book_now') }}</option>
                        </select>
                    </div>
                </div>
            </x-page-card>

            {{-- Step 4: Review & Pay --}}
            <x-page-card :title="'4. '.__('messages.review_and_pay')">
                <div class="boost-costs" style="margin-top: 0">
                    <dl class="page-kv">
                        <div>
                            <dt>{{ __('messages.ad_budget') }}</dt>
                            <dd id="review-budget">{{ $currencySymbol }}{{ number_format($defaults['budget'], 2) }}</dd>
                        </div>
                        @if ($isHosted)
                        <div>
                            <dt>{{ __('messages.service_fee') }} ({{ intval($markupRate * 100) }}%)</dt>
                            <dd id="review-fee">{{ $currencySymbol }}{{ number_format($defaults['budget'] * $markupRate, 2) }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('messages.total') }}</dt>
                            <dd id="review-total">{{ $currencySymbol }}{{ number_format($defaults['budget'] * (1 + $markupRate), 2) }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>

                @if ($useStripe)
                @if (!empty($pmLastFour))
                <p class="boost-note" style="margin: 0.75rem 0">
                    {{ __('messages.saved_card_on_file', ['brand' => ucfirst($pmType ?? 'card'), 'last4' => $pmLastFour]) }}
                </p>
                @endif
                <div id="payment-element" class="mt-4"></div>
                @elseif (!empty($isTesting))
                <x-page-notice tone="warn" class="mt-4">{{ __('messages.boost_testing_mode') }}</x-page-notice>
                @endif
                <div id="payment-errors" class="mt-3 text-sm text-red-600 dark:text-red-400 hidden" role="alert"></div>
            </x-page-card>

            {{-- The other way to do this at the start of the row, then Cancel, and the button that
                 spends the money last. --}}
            <div class="page-form-actions is-split" style="margin-top: 0">
                <a href="{{ route('boost.create', ['event_id' => $event->hashedId(), 'role_id' => \App\Utils\UrlUtils::encodeId($role->id)]) }}"
                   class="event-link">
                    {{ __('messages.use_simple_boost') }}
                </a>
                <div class="page-actions">
                    <x-secondary-link href="{{ route('boost.index') }}" class="js-cancel-btn">{{ __('messages.cancel') }}</x-secondary-link>
                    <x-brand-button type="submit" id="submit-btn">
                        <span id="submit-text">{{ __('messages.launch_boost') }}</span>
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

    {{-- The two dates use the portal's date picker. What is sent is the same Y-m-d the browser's
         own date box sent, and the start is not held to "today or later" here: the day the server
         fills in is the server's today, which a browser a few hours ahead would refuse and blank. --}}
    <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof flatpickr === 'undefined') {
                return;
            }
            var fpLocale = window.flatpickrLocales ? window.flatpickrLocales[window.appLocale] : null;
            var localeConfig = fpLocale ? { locale: fpLocale } : {};
            var pickers = {};
            document.querySelectorAll('#boost-form .boost-date').forEach(function(el) {
                pickers[el.name] = flatpickr(el, Object.assign({
                    altInput: true,
                    altFormat: "M j, Y",
                    dateFormat: "Y-m-d",
                }, localeConfig));
            });
            // Choosing a start moves the earliest end with it: the server refuses an end before
            // the start, and by then the card has been charged.
            if (pickers.scheduled_start && pickers.scheduled_end) {
                pickers.scheduled_start.config.onChange.push(function(dates, value) {
                    if (value) {
                        pickers.scheduled_end.set('minDate', value);
                    }
                });
            }
        });
    </script>

    @if ($useStripe)
    <script src="https://js.stripe.com/v3/" {!! nonce_attr() !!}></script>
    @endif
    <script {!! nonce_attr() !!}>
        const markupRate = {{ $markupRate }};
        const currencySymbol = '{{ $currencySymbol }}';
        const submitBtn = document.getElementById('submit-btn');
        const budgetInput = document.getElementById('budget-input');

        // Translate to English toggle
        @if (isset($roleLanguage) && $roleLanguage && $roleLanguage !== 'en')
        (function() {
            const translateCheckbox = document.getElementById('translate-to-english');
            if (!translateCheckbox) return;

            let originalValues = null;

            translateCheckbox.addEventListener('change', async function() {
                const headlineInput = document.querySelector('input[name="headline"]');
                const primaryTextInput = document.querySelector('textarea[name="primary_text"]');
                const descriptionInput = document.querySelector('input[name="description"]');

                if (this.checked) {
                    // Cache original values
                    originalValues = {
                        headline: headlineInput.value,
                        primary_text: primaryTextInput.value,
                        description: descriptionInput.value,
                    };

                    // Fetch English defaults
                    try {
                        const res = await fetch('{{ route("boost.translate_defaults") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                            },
                            body: JSON.stringify({ event_id: '{{ $event->hashedId() }}' }),
                        });
                        if (!res.ok) throw new Error('Request failed');
                        const data = await res.json();
                        if (data.headline) headlineInput.value = data.headline;
                        if (data.primary_text) primaryTextInput.value = data.primary_text;
                        if (data.description) descriptionInput.value = data.description;
                    } catch (err) {
                        this.checked = false;
                    }
                } else if (originalValues) {
                    // Restore original values
                    headlineInput.value = originalValues.headline;
                    primaryTextInput.value = originalValues.primary_text;
                    descriptionInput.value = originalValues.description;
                }
            });
        })();
        @endif

        @if ($useStripe)
        const stripe = Stripe('{{ $stripeKey }}');
        let elements, paymentElement;
        let clientSecret = null;
        let intentBudget = null;
        @endif

        // Update review costs when budget changes
        budgetInput.addEventListener('input', function() {
            const budget = parseFloat(this.value) || 0;
            document.getElementById('review-budget').textContent = currencySymbol + budget.toFixed(2);
            @if ($isHosted)
            document.getElementById('review-fee').textContent = currencySymbol + (budget * markupRate).toFixed(2);
            document.getElementById('review-total').textContent = currencySymbol + (budget * (1 + markupRate)).toFixed(2);
            @endif
        });

        @if ($useStripe)
        // Re-create payment intent when budget changes (debounced)
        let debounceTimer;
        budgetInput.addEventListener('change', function() {
            clearTimeout(debounceTimer);
            submitBtn.disabled = true;
            debounceTimer = setTimeout(async () => {
                try {
                    await initPayment();
                } catch (err) {
                    document.getElementById('payment-errors').textContent = @json(__("messages.payment_error"));
                    document.getElementById('payment-errors').classList.remove('hidden');
                }
                submitBtn.disabled = false;
            }, 500);
        });
        @endif

        // Interest search
        let searchTimer;
        const interestSearch = document.getElementById('interest-search');
        const interestResults = document.getElementById('interest-results');
        const selectedInterests = document.getElementById('selected-interests');
        let interests = @json($defaults['targeting']['interests'] ?? []);

        const removeLabel = @json(__('messages.remove'));

        function renderSelectedInterests() {
            selectedInterests.innerHTML = interests.map((i, idx) =>
                `<span class="boost-interest">
                    <span data-interest-name="${idx}"></span>
                    <button type="button" data-remove-idx="${idx}" aria-label="${removeLabel}">&times;</button>
                </span>`
            ).join('');
            selectedInterests.querySelectorAll('[data-interest-name]').forEach(el => {
                el.textContent = interests[el.dataset.interestName].name;
            });
            selectedInterests.querySelectorAll('[data-remove-idx]').forEach(el => {
                el.addEventListener('click', () => removeInterest(parseInt(el.dataset.removeIdx)));
            });
        }

        window.removeInterest = function(idx) {
            interests.splice(idx, 1);
            renderSelectedInterests();
        };

        interestSearch.addEventListener('input', function() {
            clearTimeout(searchTimer);
            const q = this.value.trim();
            if (q.length < 2) {
                interestResults.classList.add('hidden');
                return;
            }
            searchTimer = setTimeout(async () => {
                const res = await fetch('{{ route("boost.search_interests") }}?q=' + encodeURIComponent(q));
                if (!res.ok) throw new Error('Request failed');
                const data = await res.json();
                if (data.length > 0) {
                    interestResults.innerHTML = data.map((i, idx) =>
                        `<div class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer text-sm text-gray-900 dark:text-white" data-interest-id="${idx}"></div>`
                    ).join('');
                    interestResults.querySelectorAll('[data-interest-id]').forEach(el => {
                        const i = data[el.dataset.interestId];
                        el.textContent = i.name;
                        el.addEventListener('click', () => addInterest(i.id, i.name));
                    });
                    interestResults.classList.remove('hidden');
                } else {
                    interestResults.classList.add('hidden');
                }
            }, 300);
        });

        window.addInterest = function(id, name) {
            if (!interests.find(i => i.id === id)) {
                interests.push({ id, name });
                renderSelectedInterests();
            }
            interestSearch.value = '';
            interestResults.classList.add('hidden');
        };

        renderSelectedInterests();

        @if ($useStripe)
        // Initialize Stripe Payment Element
        async function initPayment() {
            const budget = parseFloat(budgetInput.value);
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
        @endif

        // Helper to build targeting and placements JSON
        function buildFormData() {
            const targeting = {
                age_min: parseInt(document.getElementById('age-min').value),
                age_max: parseInt(document.getElementById('age-max').value),
            };
            if (interests.length > 0) {
                targeting.interests = interests;
            }
            @if (isset($defaults['targeting']['geo_locations']))
            targeting.geo_locations = @json($defaults['targeting']['geo_locations']);
            @endif
            document.getElementById('targeting-json').value = JSON.stringify(targeting);

            const placements = [];
            if (document.querySelector('[name="placement_facebook_feed"]').checked) placements.push('facebook');
            if (document.querySelector('[name="placement_instagram"]').checked) placements.push('instagram');
            document.getElementById('placements-json').value = JSON.stringify(placements);
        }

        // Form submission
        document.getElementById('boost-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitSpinner = document.getElementById('submit-spinner');
            const paymentErrors = document.getElementById('payment-errors');
            submitBtn.disabled = true;
            submitSpinner.classList.remove('hidden');
            paymentErrors.classList.add('hidden');

            buildFormData();

            @if ($useStripe)
            // Ensure payment intent matches current budget
            const currentBudget = parseFloat(budgetInput.value);
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

            const { error, paymentIntent } = await stripe.confirmPayment({
                elements,
                confirmParams: { return_url: window.location.href },
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
            @else
            // No card to charge here (selfhosted, or testing): submit directly without Stripe
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
            @endif
        });

        @if ($useStripe)
        initPayment().catch(function() {
            document.getElementById('payment-errors').textContent = @json(__("messages.payment_error"));
            document.getElementById('payment-errors').classList.remove('hidden');
        });
        @endif
    </script>
</x-app-admin-layout>
