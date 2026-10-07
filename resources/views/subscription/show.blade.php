<x-app-admin-layout>

    <x-slot name="head">
        <script src="https://js.stripe.com/v3/" {!! nonce_attr() !!}></script>

        {{-- A choice drawn as a card: a real radio inside a label, and the label says which one is
             chosen with the brand's border. The two plans were buttons, and the two terms labels
             whose tick a script moved by hand, in indigo and amber, which are nobody's colours
             here. --}}
        <style {!! nonce_attr() !!}>
            .plan-choice {
              position: relative;
              display: flex;
              flex-direction: column;
              border: 1px solid rgb(var(--ap-border-strong));
              border-radius: 0.75rem;
              padding: 1.25rem;
              background: rgb(var(--ap-surface));
              cursor: pointer;
              transition: border-color 0.2s, box-shadow 0.2s, background-color 0.2s;
            }
            .plan-choice:hover {
              border-color: rgb(var(--ap-ink-4));
            }
            .plan-choice:has(input:checked) {
              border-color: var(--brand-blue);
              box-shadow: 0 0 0 1px var(--brand-blue);
              background: color-mix(in srgb, var(--brand-blue) 6%, rgb(var(--ap-surface)));
            }
            .plan-choice:has(input:focus-visible) {
              outline: 2px solid var(--brand-blue);
              outline-offset: 2px;
            }
            .plan-choice-head {
              display: flex;
              align-items: center;
              justify-content: space-between;
              gap: 0.75rem;
            }
            .plan-choice-name {
              font-size: 1rem;
              font-weight: 600;
              color: rgb(var(--ap-ink));
            }
            .plan-choice-sub {
              margin-top: 0.125rem;
              font-size: 0.8125rem;
              color: rgb(var(--ap-ink-3));
            }
            .plan-choice-mark {
              display: flex;
              flex: none;
              align-items: center;
              justify-content: center;
              width: 1.25rem;
              height: 1.25rem;
              border: 2px solid rgb(var(--ap-border-strong));
              border-radius: 50%;
              color: #fff;
            }
            .plan-choice-mark svg {
              display: none;
              width: 0.75rem;
              height: 0.75rem;
            }
            .plan-choice:has(input:checked) .plan-choice-mark {
              border-color: var(--brand-button-bg);
              background: var(--brand-button-bg);
            }
            .plan-choice:has(input:checked) .plan-choice-mark svg {
              display: block;
            }
            .plan-price {
              margin-top: 0.75rem;
              font-size: 1.875rem;
              font-weight: 700;
              line-height: 1.2;
              font-variant-numeric: tabular-nums;
              color: rgb(var(--ap-ink));
            }
            .plan-price.is-small {
              font-size: 1.5rem;
            }
            .plan-price small {
              font-size: 0.875rem;
              font-weight: 400;
              color: rgb(var(--ap-ink-3));
            }
            .plan-features {
              display: grid;
              gap: 0.5rem;
              margin: 1rem 0 0;
              padding: 0;
              list-style: none;
              font-size: 0.875rem;
              color: rgb(var(--ap-ink-2));
            }
            .plan-features li {
              display: flex;
              align-items: flex-start;
              gap: 0.5rem;
            }
            .plan-features svg {
              flex: none;
              width: 1rem;
              height: 1rem;
              margin-top: 0.125rem;
              color: #16a34a;
            }
            .dark .plan-features svg {
              color: #4ade80;
            }
            .plan-save {
              display: inline-block;
              margin-inline-start: 0.5rem;
              border-radius: 999px;
              padding: 0.0625rem 0.5rem;
              background: rgba(34, 197, 94, 0.14);
              font-size: 0.75rem;
              font-weight: 600;
              color: #15803d;
            }
            .dark .plan-save {
              color: #4ade80;
            }
            /* A class that sets display outranks the hidden attribute. */
            .plan-save[hidden] {
              display: none;
            }
            .page-card-title.plan-heading {
              margin-bottom: 0.75rem;
            }
            .plan-card-box {
              border: 1px solid rgb(var(--ap-border-strong));
              border-radius: 0.5rem;
              padding: 0.875rem 0.75rem;
              background: rgb(var(--ap-surface));
            }
        </style>
    </x-slot>

    @php
        // The yearly saving is derived from the two configured amounts, never written down. It
        // used to be a literal "Save 17%" string in all twelve locales, correct only by
        // coincidence, and it would have gone quietly wrong the next time either amount moved.
        // Each tier gets its own figure because the two ratios are free to diverge.
        $planPrices = [];

        foreach (['pro', 'enterprise'] as $tier) {
            $monthly = \App\Utils\PlatformPricing::amount($tier, 'monthly');
            $yearly = \App\Utils\PlatformPricing::amount($tier, 'yearly');
            $monthlyTotal = $monthly * 12;
            // Clamped at zero. Nothing stops an operator pricing the year at or above twelve
            // months, and "Save -8%" on an upgrade button is worse than showing no saving.
            $percent = $monthlyTotal > 0
                ? max(0, (int) round((($monthlyTotal - $yearly) / $monthlyTotal) * 100))
                : 0;

            $planPrices[$tier] = [
                // Pre-formatted, symbol included. The markup used to concatenate a literal
                // '$' into four bindings; savePercent above is still computed from the
                // raw amounts, so the arithmetic is unaffected.
                'monthly' => plan_price($monthly),
                'yearly' => plan_price($yearly),
                'savePercent' => $percent,
                'saveLabel' => __('messages.save_percent', ['percent' => $percent]),
            ];
        }
    @endphp

    <div class="page-shell page-col is-narrow" id="subscribe-page">
        <x-page-header :title="__('messages.upgrade')" :lead="__('messages.subscribe_page_lead', ['tab' => __('messages.plan')])"
                       :back="route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'plan'])"
                       :back-label="$role->getDisplayName(false)"
                       :image="$role->profile_image_url" />

        {{-- A payment that failed comes back to the top of this page: the answer is here, under
             the title, and not beside the card fields a screen and a half down. --}}
        <x-page-flash :keys="['error' => 'error']" class="mb-4" />

        <div class="page-stack">
            {{-- The trial: nothing is charged today --}}
            @if ($role->isEligibleForTrial())
            <x-page-notice tone="success">
                {{ __('messages.free_trial_badge') }} - {{ __('messages.you_wont_be_charged_until', ['date' => now()->addDays(config('app.trial_days'))->translatedFormat('F j, Y')]) }}
            </x-page-notice>
            @elseif ($role->calculateRemainingTrialDays() > 0)
            <x-page-notice tone="info">
                {{ __('messages.trial_days_remaining_info', ['days' => $role->calculateRemainingTrialDays()]) }}
            </x-page-notice>
            @endif

            {{-- Which plan --}}
            <div class="{{ $enterpriseConfigured ? 'page-grid2' : '' }}" role="radiogroup" aria-label="{{ __('messages.plan') }}">
                <label class="plan-choice">
                    <input type="radio" name="tier_radio" value="pro" class="sr-only" @checked($selectedTier !== 'enterprise')>
                    <span class="plan-choice-head">
                        <span class="plan-choice-name">{{ __('messages.pro_plan') }}</span>
                        <span class="plan-choice-mark" aria-hidden="true"><svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></span>
                    </span>
                    <span class="plan-price"><span data-tier-price="pro">{{ $planPrices['pro']['monthly'] }}</span><small>/<span data-period="monthly">{{ __('messages.month') }}</span><span data-period="yearly" hidden>{{ __('messages.year') }}</span></small></span>
                    <ul class="plan-features">
                        <li><svg fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>{{ __('messages.feature_white_label') }}</li>
                        <li><svg fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>{{ __('messages.feature_ticketing_qr') }}</li>
                        <li><svg fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>{{ __('messages.feature_sell_online_stripe') }}</li>
                        <li><svg fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>{{ __('messages.feature_event_graphics') }}</li>
                        <li><svg fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>{{ __('messages.feature_promo_codes') }}</li>
                        <li><svg fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>{{ __('messages.feature_rest_api_webhooks') }}</li>
                        <li><svg fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>100 {{ __('messages.newsletters_per_month') }}</li>
                    </ul>
                </label>

                @if ($enterpriseConfigured)
                <label class="plan-choice">
                    <input type="radio" name="tier_radio" value="enterprise" class="sr-only" @checked($selectedTier === 'enterprise')>
                    <span class="plan-choice-head">
                        <span class="plan-choice-name">{{ __('messages.enterprise_plan') }}</span>
                        <span class="plan-choice-mark" aria-hidden="true"><svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></span>
                    </span>
                    <span class="plan-price"><span data-tier-price="enterprise">{{ $planPrices['enterprise']['monthly'] }}</span><small>/<span data-period="monthly">{{ __('messages.month') }}</span><span data-period="yearly" hidden>{{ __('messages.year') }}</span></small></span>
                    <ul class="plan-features">
                        <li><svg fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>{{ __('messages.everything_in_pro') }}</li>
                        <li><svg fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>{{ __('messages.feature_team_members') }}</li>
                        <li><svg fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>{{ __('messages.feature_ai_parsing_flyer') }}</li>
                        <li><svg fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>{{ __('messages.feature_custom_domain') }}</li>
                        <li><svg fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>{{ __('messages.feature_private_events') }}</li>
                        <li><svg fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>{{ __('messages.feature_whatsapp_creation') }}</li>
                        <li><svg fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>1,000 {{ __('messages.newsletters_per_month') }}</li>
                        <li><svg fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>{{ __('messages.feature_priority_support') }}</li>
                    </ul>
                </label>
                @endif
            </div>

            <div class="ap-card rounded-xl page-card">
                {{-- Monthly or yearly --}}
                <h2 class="page-card-title plan-heading">{{ __('messages.select_plan') }}</h2>
                <div class="page-grid2" role="radiogroup" aria-label="{{ __('messages.select_plan') }}">
                    <label class="plan-choice plan-option" data-plan="monthly">
                        <input type="radio" name="plan_radio" value="monthly" class="sr-only" checked>
                        <span class="plan-choice-head">
                            <span>
                                <span class="plan-choice-name">{{ __('messages.monthly') }}</span>
                                <span class="plan-choice-sub block">{{ __('messages.billed_monthly') }}</span>
                            </span>
                            <span class="plan-choice-mark" aria-hidden="true"><svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></span>
                        </span>
                        <span class="plan-price is-small"><span data-term-price="monthly">{{ $planPrices[$selectedTier === 'enterprise' ? 'enterprise' : 'pro']['monthly'] }}</span><small>/{{ __('messages.month') }}</small></span>
                    </label>

                    <label class="plan-choice plan-option" data-plan="yearly">
                        <input type="radio" name="plan_radio" value="yearly" class="sr-only">
                        <span class="plan-choice-head">
                            <span>
                                <span class="plan-choice-name">{{ __('messages.yearly') }}</span><span class="plan-save" id="plan-save" @if ($planPrices[$selectedTier === 'enterprise' ? 'enterprise' : 'pro']['savePercent'] <= 0) hidden @endif>{{ $planPrices[$selectedTier === 'enterprise' ? 'enterprise' : 'pro']['saveLabel'] }}</span>
                                <span class="plan-choice-sub block">{{ __('messages.billed_yearly') }}</span>
                            </span>
                            <span class="plan-choice-mark" aria-hidden="true"><svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></span>
                        </span>
                        <span class="plan-price is-small"><span data-term-price="yearly">{{ $planPrices[$selectedTier === 'enterprise' ? 'enterprise' : 'pro']['yearly'] }}</span><small>/{{ __('messages.year') }}</small></span>
                    </label>
                </div>

                {{-- The card --}}
                <form id="payment-form" action="{{ route('subscription.store', ['subdomain' => $role->subdomain]) }}" method="POST" class="mt-6">
                    @csrf
                    {{-- Filled in here as well as by the script, so the choice the page opened
                         with is what is sent even if the script never ran. --}}
                    <input type="hidden" name="plan" id="selected-plan" value="monthly">
                    <input type="hidden" name="tier" id="selected-tier" value="{{ $selectedTier === 'enterprise' ? 'enterprise' : 'pro' }}">
                    <input type="hidden" name="payment_method" id="payment-method">
                    @if ($checkoutSource ?? null)
                        <input type="hidden" name="source" value="{{ $checkoutSource }}">
                    @endif

                    <h2 class="page-card-title plan-heading">{{ __('messages.payment_details') }}</h2>

                    <div class="page-form-fields">
                        <div>
                            <x-input-label for="card-holder-name" :value="__('messages.card_holder_name')" />
                            <x-text-input type="text" id="card-holder-name" class="mt-1 block w-full" autocomplete="cc-name" required />
                        </div>

                        <div>
                            <x-input-label :value="__('messages.card_details')" />
                            <div id="card-element" class="plan-card-box mt-1"></div>
                            <div id="card-errors" class="mt-2 text-sm text-red-600 dark:text-red-400" role="alert"></div>
                        </div>

                    </div>

                    <div class="page-form-actions">
                        <x-secondary-link :href="route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'plan'])" class="js-cancel-btn">
                            {{ __('messages.cancel') }}
                        </x-secondary-link>
                        <x-brand-button type="submit" id="submit-button">
                            <span id="button-text">{{ __('messages.subscribe') }}</span>
                            <span id="button-spinner" class="hidden ms-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                        </x-brand-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script {!! nonce_attr() !!}>
        const stripe = Stripe('{{ config('cashier.key') }}');
        const elements = stripe.elements();

        const style = {
            base: {
                color: document.documentElement.classList.contains('dark') ? '#d1d5db' : '#1f2937',
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

        // The two choices (which plan, and monthly or yearly) and everything that follows them:
        // the prices shown, the saving, and the two fields the form sends. Plain script on the
        // radios themselves; this page was the last one here driven by Alpine.
        const prices = @json($planPrices);
        const selectedPlanInput = document.getElementById('selected-plan');
        const selectedTierInput = document.getElementById('selected-tier');
        const saveBadge = document.getElementById('plan-save');

        function chosen(name, fallback) {
            const radio = document.querySelector('input[name="' + name + '"]:checked');
            return radio ? radio.value : fallback;
        }

        function paintChoice() {
            const tier = chosen('tier_radio', selectedTierInput.value);
            const plan = chosen('plan_radio', 'monthly');

            selectedTierInput.value = tier;
            selectedPlanInput.value = plan;

            document.querySelectorAll('[data-tier-price]').forEach(function(el) {
                el.textContent = prices[el.dataset.tierPrice][plan];
            });
            document.querySelectorAll('[data-term-price]').forEach(function(el) {
                el.textContent = prices[tier][el.dataset.termPrice];
            });
            document.querySelectorAll('[data-period]').forEach(function(el) {
                el.hidden = el.dataset.period !== plan;
            });
            saveBadge.textContent = prices[tier].saveLabel;
            saveBadge.hidden = ! (prices[tier].savePercent > 0);
        }

        document.querySelectorAll('input[name="tier_radio"], input[name="plan_radio"]').forEach(function(radio) {
            radio.addEventListener('change', paintChoice);
        });
        // Back, or a reload, brings the page back with the radios as they were left.
        window.addEventListener('pageshow', paintChoice);
        paintChoice();

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
