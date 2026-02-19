<x-app-admin-layout>
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('boost.create', ['event_id' => $event->hashedId(), 'role_id' => \App\Utils\UrlUtils::encodeId($role->id)]) }}"
               class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">&larr; {{ __('messages.back_to_quick_boost') }}</a>
        </div>

        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ __('messages.advanced_boost') }}</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">{{ $event->translatedName() }}</p>

        @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-md text-red-700 dark:text-red-300">
            {{ session('error') }}
        </div>
        @endif

        <form id="boost-form" class="space-y-6">
            @csrf
            <input type="hidden" name="event_id" value="{{ $event->hashedId() }}">
            <input type="hidden" name="role_id" value="{{ \App\Utils\UrlUtils::encodeId($role->id) }}">
            <input type="hidden" id="payment_intent_id" name="payment_intent_id" value="">

            {{-- Step 1: Budget & Duration --}}
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">1. {{ __('messages.budget_and_duration') }}</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('messages.ad_budget') }}</label>
                        <div class="flex items-center gap-3">
                            <span class="text-gray-500 dark:text-gray-400">{{ $currencySymbol }}</span>
                            <input type="number" name="budget" id="budget-input" value="{{ $defaults['budget'] }}"
                                min="{{ $minBudget }}" max="{{ $maxBudget }}" step="1"
                                class="block w-32 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('messages.budget_type') }}</label>
                        <select name="budget_type" class="block w-48 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="lifetime" selected>{{ __('messages.lifetime_budget') }}</option>
                            <option value="daily">{{ __('messages.daily_budget') }}</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('messages.start_date') }}</label>
                            <input type="date" name="scheduled_start" value="{{ $defaults['scheduled_start']->format('Y-m-d') }}"
                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('messages.end_date') }}</label>
                            <input type="date" name="scheduled_end" value="{{ $defaults['scheduled_end']->format('Y-m-d') }}"
                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('messages.objective') }}</label>
                        <select name="objective" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="OUTCOME_AWARENESS">{{ __('messages.objective_awareness') }}</option>
                            <option value="OUTCOME_TRAFFIC">{{ __('messages.objective_traffic') }}</option>
                            <option value="OUTCOME_ENGAGEMENT">{{ __('messages.objective_engagement') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Step 2: Targeting --}}
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">2. {{ __('messages.targeting') }}</h2>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('messages.age_min') }}</label>
                            <input type="number" id="age-min" value="{{ $defaults['targeting']['age_min'] ?? 18 }}" min="18" max="65"
                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('messages.age_max') }}</label>
                            <input type="number" id="age-max" value="{{ $defaults['targeting']['age_max'] ?? 65 }}" min="18" max="65"
                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('messages.interests') }}</label>
                        <input type="text" id="interest-search" placeholder="{{ __('messages.search_interests') }}"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <div id="interest-results" class="mt-1 hidden border border-gray-200 dark:border-gray-600 rounded-md max-h-40 overflow-y-auto"></div>
                        <div id="selected-interests" class="flex flex-wrap gap-2 mt-2"></div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.placements') }}</label>
                        <div class="space-y-2">
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="placement_facebook_feed" value="1" checked
                                    class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.placement_facebook_feed') }}</span>
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="placement_instagram" value="1" checked
                                    class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.placement_instagram') }}</span>
                            </label>
                        </div>
                    </div>

                    <input type="hidden" name="targeting" id="targeting-json" value="{{ json_encode($defaults['targeting']) }}">
                    <input type="hidden" name="placements" id="placements-json" value="">
                </div>
            </div>

            {{-- Step 3: Creative --}}
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">3. {{ __('messages.creative') }}</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('messages.headline') }} ({{ __('messages.max_chars', ['count' => 40]) }})</label>
                        <input type="text" name="headline" value="{{ $defaults['headline'] }}" maxlength="40"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('messages.primary_text') }} ({{ __('messages.max_chars', ['count' => 125]) }})</label>
                        <textarea name="primary_text" maxlength="125" rows="2"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $defaults['primary_text'] }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('messages.description') }} ({{ __('messages.max_chars', ['count' => 30]) }})</label>
                        <input type="text" name="description" value="{{ $defaults['description'] }}" maxlength="30"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('messages.call_to_action') }}</label>
                        <select name="call_to_action" class="block w-48 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="LEARN_MORE" {{ $defaults['call_to_action'] === 'LEARN_MORE' ? 'selected' : '' }}>{{ __('messages.cta_learn_more') }}</option>
                            <option value="GET_TICKETS" {{ $defaults['call_to_action'] === 'GET_TICKETS' ? 'selected' : '' }}>{{ __('messages.cta_get_tickets') }}</option>
                            <option value="SIGN_UP" {{ $defaults['call_to_action'] === 'SIGN_UP' ? 'selected' : '' }}>{{ __('messages.cta_sign_up') }}</option>
                            <option value="BOOK_TRAVEL" {{ $defaults['call_to_action'] === 'BOOK_TRAVEL' ? 'selected' : '' }}>{{ __('messages.cta_book_now') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Step 4: Review & Pay --}}
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">4. {{ __('messages.review_and_pay') }}</h2>

                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg text-sm mb-4">
                    <div class="flex justify-between text-gray-600 dark:text-gray-300">
                        <span>{{ __('messages.ad_budget') }}</span>
                        <span id="review-budget">{{ $currencySymbol }}{{ number_format($defaults['budget'], 2) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-300 mt-1">
                        <span>{{ __('messages.service_fee') }} ({{ intval($markupRate * 100) }}%)</span>
                        <span id="review-fee">{{ $currencySymbol }}{{ number_format($defaults['budget'] * $markupRate, 2) }}</span>
                    </div>
                    <div class="flex justify-between font-semibold text-gray-900 dark:text-white mt-2 pt-2 border-t border-gray-200 dark:border-gray-600">
                        <span>{{ __('messages.total') }}</span>
                        <span id="review-total">{{ $currencySymbol }}{{ number_format($defaults['budget'] * (1 + $markupRate), 2) }}</span>
                    </div>
                </div>

                <div id="payment-element" class="mb-4"></div>
                <div id="payment-errors" class="text-sm text-red-600 dark:text-red-400 hidden"></div>
            </div>

            <div class="flex justify-end">
                <button type="submit" id="submit-btn"
                    class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <span id="submit-text">{{ __('messages.launch_boost') }}</span>
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

    <script src="https://js.stripe.com/v3/" {!! nonce_attr() !!}></script>
    <script {!! nonce_attr() !!}>
        const stripe = Stripe('{{ $stripeKey }}');
        const markupRate = {{ $markupRate }};
        const currencySymbol = '{{ $currencySymbol }}';
        let elements, paymentElement;
        let clientSecret = null;
        let intentBudget = null;
        const submitBtn = document.getElementById('submit-btn');

        // Update review costs when budget changes
        const budgetInput = document.getElementById('budget-input');
        budgetInput.addEventListener('input', function() {
            const budget = parseFloat(this.value) || 0;
            document.getElementById('review-budget').textContent = currencySymbol + budget.toFixed(2);
            document.getElementById('review-fee').textContent = currencySymbol + (budget * markupRate).toFixed(2);
            document.getElementById('review-total').textContent = currencySymbol + (budget * (1 + markupRate)).toFixed(2);
        });

        // Re-create payment intent when budget changes (debounced)
        let debounceTimer;
        budgetInput.addEventListener('change', function() {
            clearTimeout(debounceTimer);
            submitBtn.disabled = true;
            debounceTimer = setTimeout(async () => {
                try {
                    await initPayment();
                } catch (err) {
                    document.getElementById('payment-errors').textContent = '{{ __("messages.payment_error") }}';
                    document.getElementById('payment-errors').classList.remove('hidden');
                }
                submitBtn.disabled = false;
            }, 500);
        });

        // Interest search
        let searchTimer;
        const interestSearch = document.getElementById('interest-search');
        const interestResults = document.getElementById('interest-results');
        const selectedInterests = document.getElementById('selected-interests');
        let interests = @json($defaults['targeting']['interests'] ?? []);

        function renderSelectedInterests() {
            selectedInterests.innerHTML = interests.map((i, idx) =>
                `<span class="inline-flex items-center gap-1 rounded-full bg-blue-100 dark:bg-blue-900/30 px-3 py-1 text-sm text-blue-700 dark:text-blue-300">
                    <span data-interest-name="${idx}"></span>
                    <button type="button" data-remove-idx="${idx}" class="text-blue-500 hover:text-blue-700">&times;</button>
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

        // Initialize Stripe Payment Element
        async function initPayment() {
            const budget = parseFloat(budgetInput.value);
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

        // Form submission
        document.getElementById('boost-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitSpinner = document.getElementById('submit-spinner');
            const paymentErrors = document.getElementById('payment-errors');
            submitBtn.disabled = true;
            submitSpinner.classList.remove('hidden');
            paymentErrors.classList.add('hidden');

            // Build targeting JSON
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

            // Build placements
            const placements = [];
            if (document.querySelector('[name="placement_facebook_feed"]').checked) placements.push('facebook');
            if (document.querySelector('[name="placement_instagram"]').checked) placements.push('instagram');
            document.getElementById('placements-json').value = JSON.stringify(placements);

            // Ensure payment intent matches current budget
            const currentBudget = parseFloat(budgetInput.value);
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

        initPayment().catch(function() {
            document.getElementById('payment-errors').textContent = '{{ __("messages.payment_error") }}';
            document.getElementById('payment-errors').classList.remove('hidden');
        });
    </script>
</x-app-admin-layout>
