{{--
    Buy an on-network promotion.

    Modelled on boost/create.blade.php rather than forked from it: that form is Meta-shaped
    (interest search, Meta placements, objectives, reach estimation) and none of that applies
    here. There is deliberately no "advanced" network mode.

    The two things this page does that the Meta form does not: it previews the ACTUAL card
    the visitor will see, using the same component the guest page renders, and it shows a
    delivery estimate before the advertiser commits money to a prepaid campaign.
--}}
<x-app-admin-layout>
    <x-slot name="head">
        <script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}></script>
        @include('boost.partials.styles')
        <style {!! nonce_attr() !!}>
            [v-cloak] { display: none !important; }
        </style>
    </x-slot>

    {{-- Payment. Only rendered when a card is actually required: settlePayment() takes
         the wallet branch first, and on selfhost/testing it settles for free. --}}
    {{-- $stripeKey matters as much as the mode: an operator can switch promotions on
         without ever configuring STRIPE_PLATFORM_KEY, and rendering this block then
         calls Stripe(null), which throws and takes the whole payment script with it.
         The buyer would submit anyway, settlePayment() would find no intent and no
         credit, and the campaign would be marked failed with nothing explaining why.
         With no key the wallet is the only way to pay, which is what this now shows. --}}
    @php $needsCard = $isHosted && ! $isTesting && ! empty($stripeKey); @endphp

    {{-- Everything from here to the end of the form is one Vue mount, so a heading or a line of
         help that is a text node carries v-pre: Vue compiles a text node it finds, and these are
         translated strings an operator can override. The title row guards its own. --}}
    <div class="page-shell" id="promo-form-app">
        <x-page-header :title="__('messages.promotion_create_title')" :lead="__('messages.promotion_create_intro')"
            :back="route('boost.index')" :back-label="__('messages.boost')" />

        <div class="boost-cols">
        <div class="boost-cols-main">
        <form method="POST" action="{{ route('promotions.store') }}">
            @csrf
            <input type="hidden" name="event_id" value="{{ \App\Utils\UrlUtils::encodeId($event->id) }}">
            <input type="hidden" name="role_id" value="{{ \App\Utils\UrlUtils::encodeId($role->id) }}">

            <section class="ap-card rounded-xl page-card">
                <div class="page-card-head"><h2 class="page-card-title" v-pre>@lang('messages.creative')</h2></div>
                <div class="boost-fields">
                    <div>
                        <x-input-label for="headline" :value="__('messages.promotion_headline')" />
                        {{-- v-model drives the live preview below; the server still owns the value. --}}
                        <x-text-input id="headline" name="headline" type="text" maxlength="80"
                            class="mt-1 block w-full" v-model="headline" />
                        <x-input-error class="mt-2" :messages="$errors->get('headline')" />
                    </div>

                    <div>
                        <x-input-label for="primary_text" :value="__('messages.promotion_body')" />
                        <x-text-input id="primary_text" name="primary_text" type="text" maxlength="180"
                            class="mt-1 block w-full" v-model="body" />
                        <x-input-error class="mt-2" :messages="$errors->get('primary_text')" />
                    </div>
                </div>
            </section>

            <section class="ap-card rounded-xl page-card">
                <div class="page-card-head"><h2 class="page-card-title" v-pre>@lang('messages.budget_and_duration')</h2></div>
                <div class="boost-fields">
                    <fieldset>
                        <legend class="block font-medium text-sm text-gray-700 dark:text-gray-300" v-pre>@lang('messages.promotion_pricing_model')</legend>
                        <div class="boost-checks">
                            <label class="boost-check">
                                <input type="radio" name="pricing_model" value="cpm" v-model="pricingModel"
                                    class="text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                                <span v-pre>@lang('messages.promotion_pricing_cpm', ['rate' => $currencySymbol.number_format($cpm, 2)])</span>
                            </label>
                            <label class="boost-check">
                                <input type="radio" name="pricing_model" value="cpc" v-model="pricingModel"
                                    class="text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                                <span v-pre>@lang('messages.promotion_pricing_cpc', ['rate' => $currencySymbol.number_format($cpc, 2)])</span>
                            </label>
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('pricing_model')" />
                    </fieldset>

                    <div>
                        {{-- The symbol goes on the label rather than as an inline prefix inside the
                             input: a positioned prefix has to flip side in RTL, and the label reads
                             correctly in both directions without any of that. --}}
                        <x-input-label for="budget" :value="__('messages.promotion_budget').' ('.$currencySymbol.')'" />
                        <x-text-input id="budget" name="budget" type="number" step="0.01"
                            min="{{ $minBudget }}" max="{{ $maxBudget }}"
                            class="mt-1 block w-full" v-model="budget" />
                        {{-- Buying a prepaid campaign with no idea whether it delivers 500 or
                             500,000 impressions is a bad experience. estimateText is what the
                             budget buys at the configured rate; inventoryNote is the separate,
                             more important question of whether this instance actually has the
                             traffic to deliver it. The two are deliberately not merged - the
                             first is exact, the second is an observation about recent traffic. --}}
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400" v-cloak>
                            @{{ estimateText }}
                        </p>
                        <p class="mt-1 text-xs" v-cloak v-if="inventoryNote"
                           :class="hasInventory ? 'text-gray-500 dark:text-gray-400' : 'text-amber-700 dark:text-amber-400'">
                            @{{ inventoryNote }}
                        </p>
                        <x-input-error class="mt-2" :messages="$errors->get('budget')" />
                    </div>

                    <div class="boost-fields-2">
                        <div>
                            <x-input-label for="scheduled_start" :value="__('messages.start_date')" />
                            <x-text-input id="scheduled_start" name="scheduled_start" type="text"
                                class="mt-1 block w-full flatpickr-date" :value="old('scheduled_start')" />
                        </div>
                        <div>
                            <x-input-label for="scheduled_end" :value="__('messages.end_date')" />
                            <x-text-input id="scheduled_end" name="scheduled_end" type="text"
                                class="mt-1 block w-full flatpickr-date" :value="old('scheduled_end')" />
                            <x-input-error class="mt-2" :messages="$errors->get('scheduled_end')" />
                        </div>
                    </div>
                </div>
            </section>

            <section class="ap-card rounded-xl page-card">
                <div class="page-card-head"><h2 class="page-card-title" v-pre>@lang('messages.targeting')</h2></div>
                <div class="boost-fields">
                    <fieldset>
                        <legend class="block font-medium text-sm text-gray-700 dark:text-gray-300" v-pre>@lang('messages.promotion_target_schedule_types')</legend>
                        <p class="event-hint" style="margin-bottom: 0" v-pre>@lang('messages.promotion_target_optional')</p>
                        <div class="boost-checks">
                            @foreach (['talent', 'venue', 'curator'] as $type)
                            <label class="boost-check">
                                <input type="checkbox" name="schedule_types[]" value="{{ $type }}"
                                    class="rounded text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                                <span v-pre>{{ __('messages.'.$type) }}</span>
                            </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <div>
                        <x-input-label :value="__('messages.promotion_target_countries')" />
                        <p class="event-hint" style="margin-bottom: 0" v-pre>@lang('messages.promotion_target_countries_optional')</p>

                        {{-- The selection posts through these hidden inputs rather than through the
                             visible checkboxes. The list below is filtered by the search box, and a
                             v-for that drops a filtered-out node would drop that country from the
                             POST along with it - so typing a search term would silently discard
                             everything already chosen. --}}
                        <input v-for="code in selectedCountries" :key="code"
                               type="hidden" name="visitor_countries[]" :value="code">

                        <div class="mt-2 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                            <div class="border-b border-gray-200 dark:border-gray-700 p-2">
                                <x-text-input type="search" v-model="countrySearch" class="block w-full text-sm"
                                    :placeholder="__('messages.search')" :aria-label="__('messages.search')" autocomplete="off" />
                            </div>
                            <div class="max-h-48 overflow-y-auto p-2" v-cloak>
                                <label v-for="c in filteredCountries" :key="c.code"
                                       class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200">
                                    <input type="checkbox" :value="c.code" v-model="selectedCountries"
                                           class="rounded text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                                    <span>@{{ c.name }}</span>
                                </label>
                                <p v-if="! filteredCountries.length" class="px-2 py-1.5 text-sm text-gray-500 dark:text-gray-400">
                                    @lang('messages.no_results_found')
                                </p>
                            </div>
                        </div>

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" v-cloak v-if="selectedCountries.length">
                            @{{ countrySummary }}
                        </p>
                        <x-input-error class="mt-2" :messages="$errors->get('visitor_countries.*')" />
                    </div>
                </div>
            </section>

            @if ($needsCard)
            <section class="ap-card rounded-xl page-card is-after-preview" id="promo-payment-card" v-cloak v-show="needsPayment">
                <div class="page-card-head"><h2 class="page-card-title" v-pre>@lang('messages.payment')</h2></div>

                @if ($boostCredit > 0)
                <p class="boost-note" style="margin: 0 0 0.75rem" v-pre>
                    {{ __('messages.promotion_credit_available', ['amount' => $currencySymbol.number_format($boostCredit, 2)]) }}
                </p>
                @endif

                {{-- Same affordance the Meta form gives: a returning buyer should be told a card
                     is already on file rather than wondering whether they are re-entering it. --}}
                @if (! empty($pmLastFour))
                <p class="boost-note" style="margin: 0 0 0.75rem" v-pre>
                    {{ __('messages.saved_card_on_file', ['brand' => ucfirst($pmType ?? 'card'), 'last4' => $pmLastFour]) }}
                </p>
                @endif

                <div id="promo-payment-element" class="min-h-[3rem]"></div>

                <p id="promo-payment-error" class="mt-2 hidden text-sm text-red-600 dark:text-red-400" role="alert"></p>
            </section>
            @endif

            <div class="is-after-preview">
                <x-page-notice tone="warn"><span v-pre>@lang('messages.promotion_awaiting_review_help')</span></x-page-notice>

                {{-- Set by the payment step below before the form is submitted natively. --}}
                <input type="hidden" name="payment_intent_id" id="promo-payment-intent-id" value="">

                <div class="page-form-actions" style="margin-top: 1rem">
                    <x-secondary-link href="{{ route('boost.index') }}"><span v-pre>@lang('messages.cancel')</span></x-secondary-link>
                    <x-brand-button type="submit" id="promo-submit"><span v-pre>@lang('messages.promotion_submit')</span></x-brand-button>
                </div>
            </div>
        </form>
        </div>

        {{-- Live preview using the very component the guest page renders, so what the
             advertiser approves is exactly what ships. Beside the form from a laptop up, and
             between the fields and the payment on a phone. --}}
        <aside class="boost-cols-aside">
            <section class="ap-card rounded-xl page-card">
                <div class="page-card-head"><h2 class="page-card-title" v-pre>@lang('messages.promotion_preview')</h2></div>
                <div class="rounded-xl bg-gray-50 dark:bg-gray-800 p-4">
                    <div class="overflow-hidden rounded-2xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-black/5 dark:ring-white/10">
                        <div class="flex items-stretch gap-4 p-4">
                            @if ($event->flyer_image_url)
                            <img src="{{ $event->flyer_image_url }}" alt="" width="96" height="96"
                                 class="h-24 w-24 flex-none rounded-xl object-cover bg-gray-100 dark:bg-gray-700">
                            @endif
                            <div class="flex min-w-0 flex-col">
                                <span class="mb-1.5 inline-flex self-start items-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium uppercase tracking-wide text-gray-500 dark:bg-gray-700 dark:text-gray-400" v-pre>
                                    @lang('messages.promoted')
                                </span>
                                {{-- eventName comes from data() rather than being inlined here.
                                     Inlining @json() into a Vue expression is not an XSS risk
                                     (the flags hex-escape quotes) but braces survive, and an
                                     event name containing }} truncates the interpolation into a
                                     syntax error that kills the entire mount. Event names can
                                     come from guest submissions, so that is cross-user. --}}
                                <span class="line-clamp-2 text-base font-semibold text-gray-900 dark:text-gray-100" v-cloak>
                                    @{{ headline || eventName }}
                                </span>
                                <span class="mt-0.5 line-clamp-2 text-sm text-gray-500 dark:text-gray-400" v-cloak>@{{ body }}</span>
                                {{-- v-pre via the x-user-text component: this is a server-rendered text node
                                     inside a Vue mount, and the app loads Vue's full build with
                                     the in-DOM compiler. Blade escapes <>&"' but not { or }, so
                                     a schedule name containing a mustache would be compiled and
                                     executed. --}}
                                <x-user-text class="mt-auto pt-2 text-xs text-gray-400 dark:text-gray-500">{{ $role->name }}</x-user-text>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </aside>
        </div>
    </div>

    {{-- The two dates use the portal's date picker. They carried its class and nothing ever
         started it, so they were bare text boxes, and the rule below that keeps the end on or
         after the start read a picker that was not there. store() enforces that rule too, but
         the card is confirmed before the form submits, so a server-side rejection there means
         the buyer has already been charged. --}}
    <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function () {
            const start = document.getElementById('scheduled_start');
            const end = document.getElementById('scheduled_end');

            if (! start || ! end || typeof flatpickr === 'undefined') {
                return;
            }

            const fpLocale = window.flatpickrLocales ? window.flatpickrLocales[window.appLocale] : null;
            const options = Object.assign({
                altInput: true,
                altFormat: "M j, Y",
                dateFormat: "Y-m-d",
                minDate: "today",
            }, fpLocale ? { locale: fpLocale } : {});

            const endPicker = flatpickr(end, options);
            flatpickr(start, Object.assign({}, options, {
                onChange: function (dates, value) {
                    endPicker.set('minDate', value || 'today');
                },
            }));
            if (start.value) {
                endPicker.set('minDate', start.value);
            }
        });
    </script>

    <script {!! nonce_attr() !!}>
        const { createApp } = Vue;

        createApp({
            data() {
                return {
                    headline: '',
                    body: '',
                    // Safe here: a <script> block is never compiled as a Vue template, and the
                    // JSON encoding escapes anything that could terminate the string literal.
                    // (Do not write the directive name in a comment - Blade compiles it there too.)
                    eventName: @json($event->translatedName()),
                    budget: {{ $minBudget }},
                    pricingModel: 'cpm',
                    cpm: {{ $cpm }},
                    cpc: {{ $cpc }},
                    // Average daily impressions actually available across eligible free
                    // schedules, from the analytics rollups - not a projection.
                    dailyInventory: {{ $inventory }},
                    // Sorted by name, not by the code the source map is keyed on - otherwise the
                    // list opens on "Ascension Island, Andorra, United Arab Emirates" (AC, AD, AE),
                    // which reads as random to anyone scanning for a country name.
                    countries: Object.entries(@json($countries))
                        .map(([code, name]) => ({ code, name }))
                        .sort((a, b) => a.name.localeCompare(b.name)),
                    selectedCountries: @json(array_values((array) old('visitor_countries', []))),
                    countrySearch: '',
                };
            },
            computed: {
                filteredCountries() {
                    const q = this.countrySearch.trim().toLowerCase();

                    if (! q) {
                        return this.countries;
                    }

                    return this.countries.filter(c =>
                        c.name.toLowerCase().includes(q) || c.code.toLowerCase().includes(q));
                },

                countrySummary() {
                    return @json(__('messages.promotion_countries_selected'))
                        .replace(':count', this.selectedCountries.length.toLocaleString());
                },

                hasInventory() {
                    return this.dailyInventory > 0;
                },

                // What the budget buys is exact arithmetic; whether it can actually be
                // delivered depends on how much traffic the free tier is getting. Quoting the
                // first without the second is how an advertiser ends up paying for 500,000
                // views on an instance that serves 200 a day.
                inventoryNote() {
                    if (! this.hasInventory) {
                        return @json(__('messages.promotion_inventory_none'));
                    }

                    const note = @json(__('messages.promotion_inventory_daily'))
                        .replace(':count', this.dailyInventory.toLocaleString());

                    const budget = parseFloat(this.budget) || 0;

                    // Only CPM converts to impressions without guessing a click-through rate,
                    // so the "how long" half is deliberately CPM-only rather than invented.
                    if (this.pricingModel !== 'cpm' || budget <= 0 || this.cpm <= 0) {
                        return note;
                    }

                    const impressions = Math.floor((budget / this.cpm) * 1000);
                    const days = Math.ceil(impressions / this.dailyInventory);

                    return note + ' ' + @json(__('messages.promotion_inventory_days'))
                        .replace(':days', days.toLocaleString());
                },

                estimateText() {
                    const budget = parseFloat(this.budget) || 0;

                    if (budget <= 0) {
                        return @json(__('messages.promotion_estimate_none'));
                    }

                    if (this.pricingModel === 'cpm') {
                        const impressions = this.cpm > 0 ? Math.floor((budget / this.cpm) * 1000) : 0;
                        return @json(__('messages.promotion_estimate_impressions')).replace(':count', impressions.toLocaleString());
                    }

                    const clicks = this.cpc > 0 ? Math.floor(budget / this.cpc) : 0;
                    return @json(__('messages.promotion_estimate_clicks')).replace(':count', clicks.toLocaleString());
                },

                // A card is only needed for the part the wallet cannot cover. settlePayment()
                // takes the credit branch whole-or-nothing, so any shortfall means Stripe.
                needsPayment() {
                    return {{ $needsCard ? 'true' : 'false' }}
                        && (parseFloat(this.budget) || 0) > {{ $boostCredit }};
                },
            },
        }).mount('#promo-form-app');
    </script>

    @if ($needsCard)
    <script src="https://js.stripe.com/v3/" {!! nonce_attr() !!}></script>
    <script {!! nonce_attr() !!}>
        /**
         * Card payment for a network promotion.
         *
         * The form posts natively (store() answers with a redirect, not JSON), so the job here is
         * only to obtain a succeeded PaymentIntent and drop its id into the hidden field before
         * letting the submit through. store() re-verifies the intent's amount and metadata
         * server-side, so nothing here is trusted.
         */
        (function () {
            const stripe = Stripe(@json($stripeKey));
            const form = document.querySelector('form[action="{{ route('promotions.store') }}"]');
            const submitBtn = document.getElementById('promo-submit');
            const errorBox = document.getElementById('promo-payment-error');
            const intentField = document.getElementById('promo-payment-intent-id');
            const budgetInput = document.getElementById('budget');
            const credit = {{ $boostCredit }};

            let elements = null;
            let clientSecret = null;
            let intentForBudget = null;
            let confirmed = false;

            function showError(message) {
                errorBox.textContent = message;
                errorBox.classList.remove('hidden');
                submitBtn.disabled = false;
            }

            function currentBudget() {
                return parseFloat(budgetInput.value) || 0;
            }

            function needsCard() {
                return currentBudget() > credit;
            }

            // Elements needs a client secret up front, and the secret is tied to an amount - so
            // the intent is recreated whenever the budget changes rather than once on load.
            async function prepareIntent() {
                const budget = currentBudget();

                if (! needsCard() || intentForBudget === budget) {
                    return;
                }

                const body = new FormData();
                body.append('event_id', form.querySelector('[name=event_id]').value);
                body.append('role_id', form.querySelector('[name=role_id]').value);
                body.append('budget', budget);
                body.append('_token', form.querySelector('[name=_token]').value);

                const response = await fetch(@json(route('promotions.payment_intent')), {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: body,
                });

                const data = await response.json();

                if (! response.ok || ! data.client_secret) {
                    throw new Error(data.error || @json(__('messages.boost_payment_failed')));
                }

                clientSecret = data.client_secret;
                intentForBudget = budget;
                confirmed = false;

                elements = stripe.elements({ clientSecret: clientSecret });
                document.getElementById('promo-payment-element').innerHTML = '';
                elements.create('payment').mount('#promo-payment-element');
            }

            budgetInput.addEventListener('change', function () {
                prepareIntent().catch(function (e) { showError(e.message); });
            });

            prepareIntent().catch(function (e) { showError(e.message); });

            form.addEventListener('submit', async function (event) {
                // Wallet covers it, or already paid: let the native submit through - but still
                // disable the button first. Returning here without disabling meant a
                // double-click on a credit-funded purchase fired two native POSTs, both with
                // an empty payment_intent_id, so the UNIQUE column could not dedupe them
                // (MySQL allows unlimited NULLs) and the wallet was debited twice.
                if (! needsCard() || confirmed) {
                    submitBtn.disabled = true;

                    return;
                }

                event.preventDefault();
                submitBtn.disabled = true;
                errorBox.classList.add('hidden');

                try {
                    if (! clientSecret) {
                        await prepareIntent();
                    }

                    const result = await stripe.confirmPayment({
                        elements: elements,
                        confirmParams: { return_url: window.location.href },
                        redirect: 'if_required',
                    });

                    if (result.error) {
                        showError(result.error.message);
                        return;
                    }

                    if (! result.paymentIntent || result.paymentIntent.status !== 'succeeded') {
                        showError(@json(__('messages.boost_payment_failed')));
                        return;
                    }

                    intentField.value = result.paymentIntent.id;
                    confirmed = true;
                    form.submit();
                } catch (e) {
                    showError(e.message || @json(__('messages.boost_payment_failed')));
                }
            });
        })();
    </script>
    @else
    {{-- No card step here (selfhost, testing, or the wallet always covers it), so the block
         above does not render - but the double-submit it guards against still applies: two
         native POSTs with no payment_intent_id are not deduped by the UNIQUE column, and
         settlePayment() would debit the wallet twice. --}}
    <script {!! nonce_attr() !!}>
        (function () {
            const form = document.querySelector('form[action="{{ route('promotions.store') }}"]');
            const submitBtn = document.getElementById('promo-submit');

            if (! form || ! submitBtn) {
                return;
            }

            form.addEventListener('submit', function () {
                submitBtn.disabled = true;
            });
        })();
    </script>
    @endif
</x-app-admin-layout>
