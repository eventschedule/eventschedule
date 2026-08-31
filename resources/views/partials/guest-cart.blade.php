{{--
    The cart for buying tickets to several events of one schedule in a single checkout.

    Mounted here, after {{ $slot }}, so it sits OUTSIDE both guest Vue mounts (#calendar-app on the
    schedule page and #ticket-selector on an event page). Nesting one Vue app inside another's
    template would have the outer compiler consume this markup.

    The cart lives entirely in the browser. localStorage rather than sessionStorage, so it survives
    the hard navigation between events and a closed tab; the server is told nothing until checkout,
    and then re-resolves and re-prices every leg from the database. Stored prices are for display
    only and are never trusted.
--}}
@php
    // Why the cart renders its own errors. session('error') is already toasted for every guest page
    // (app-guest nests inside layouts/app, which raises a Toastify for it), but VALIDATION errors
    // are not toasted anywhere - so a checkout refused by the request rules came back with nothing
    // on screen at all. Both are shown here, beside the form that produced them and on whichever
    // page the buyer was bounced back to, which the toast alone cannot do.
    // Keys ("<encoded event id>|<date>") of legs checkout refused, so the panel can point at the
    // one that is actually at fault instead of leaving the buyer to bisect their own cart.
    $cartInvalidLegs = session('cart_invalid_legs', []);

    // Only claim an error when this cart produced it. $errors is the shared bag and the
    // single-event checkout form posts to the same route from this same page, so reading it
    // unconditionally popped the cart open and printed that form's errors inside a shopping
    // popover, for a buyer who had never touched the cart. `cart` is the named bag
    // refuseCartLeg() and the request use; session('cart_submitted') covers a validation failure,
    // which Laravel puts in the default bag before any of our code runs.
    $cartOwnsErrors = session('cart_submitted') || $cartInvalidLegs;
    $cartError = $cartOwnsErrors ? session('error') : null;
    $cartFieldErrors = $cartOwnsErrors && $errors->any() ? $errors->all() : [];
    $cartHasError = $cartError || $cartFieldErrors;
@endphp
@if (! request()->embed)
<div id="es-cart-app" class="print:hidden {{ $role->show_accessibility_widget ? 'es-cart-above-a11y' : '' }}">
    <template v-if="legs.length > 0">
        {{-- Bottom-right is a crowded corner. The guest event page pins a full-width mobile CTA bar
             at bottom-0, and the accessibility launcher (when the owner enables it) sits at
             bottom-6 in this same corner with a higher z-index. es-cart-fab lifts this clear of
             both, reusing the --es-a11y-cta-clearance the CTA bar already publishes. --}}
        <div class="es-cart-fab fixed {{ $role->isRtl() ? 'left-4' : 'right-4' }} z-40">
            <button type="button" @click="open = !open"
                class="flex items-center gap-2 rounded-full bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 px-5 py-3 shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-105"
                :aria-expanded="open ? 'true' : 'false'"
                aria-controls="es-cart-panel">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                </svg>
                <span class="font-semibold">@{{ legs.length }}</span>
            </button>
        </div>

        <div v-show="open" id="es-cart-panel"
             class="es-cart-panel fixed {{ $role->isRtl() ? 'left-4' : 'right-4' }} z-40 w-[min(22rem,calc(100vw-2rem))] max-h-[min(80vh,44rem)] overflow-y-auto rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-2xl p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.your_cart') }}</h2>
                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" aria-label="{{ __('messages.close') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- The messages below carry a v-pre. They sit inside the #es-cart-app mount, and the
                 page loads the full Vue build (compiler included), so server-rendered markup here
                 is compiled as a Vue template. $cartError embeds an EVENT NAME - refuseCartLeg()
                 interpolates $event->translatedName() into the message - and Blade's {{ }} escapes
                 < > & " ' but NOT { }, so a name containing a Vue mustache would be compiled and
                 EXECUTED on the schedule's own origin. CSP unsafe-eval is intentionally on and
                 will not stop it. Neither message needs Vue, so both opt out of compilation. --}}
            @if ($cartHasError)
                <div id="es-cart-error" class="mb-4 flex items-start gap-2 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 p-3">
                    <svg class="w-5 h-5 shrink-0 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    <div v-pre class="text-sm text-red-700 dark:text-red-300 space-y-1">
                        @if ($cartError)
                            <p>{{ $cartError }}</p>
                        @endif
                        @foreach ($cartFieldErrors as $cartFieldError)
                            <p>{{ $cartFieldError }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <ul class="space-y-3 max-h-64 overflow-y-auto">
                <li v-for="(leg, index) in legs" :key="leg.event_id + '|' + leg.event_date"
                    class="flex items-start justify-between gap-3 rounded-lg"
                    :class="isInvalid(leg) ? 'bg-red-50 dark:bg-red-900/20 p-2 -m-2' : ''">
                    <div class="min-w-0">
                        {{-- Vue interpolation, not server-rendered: the name comes from localStorage
                             and is bound as text, so it is never compiled as a template. --}}
                        <div class="text-sm font-medium truncate"
                             :class="isInvalid(leg) ? 'text-red-700 dark:text-red-300' : 'text-gray-900 dark:text-gray-100'">@{{ leg.event_name }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            @{{ leg.event_date }} &middot; @{{ ticketCount(leg) }} {{ __('messages.tickets') }}
                            <template v-if="legPrice(leg) !== null">&middot; @{{ formatMoney(legPrice(leg), leg.currency) }}</template>
                        </div>
                        {{-- Allocated seating only. The basket used to say "4 tickets" for a buyer
                             holding Row C 12-15, and said nothing at all about the twelve-minute
                             hold those seats are on - which expires while they carry on browsing,
                             and is then refused at checkout with no way back to the same seats. --}}
                        <div v-if="leg.seat_labels && leg.seat_labels.length"
                             class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            {{-- A real middle dot, not the entity: a mustache sets textContent, so
                                 &middot; reaches the buyer as those eight characters. --}}
                            @{{ leg.seat_labels.join(' · ') }}
                        </div>
                        <div v-if="seatHoldLabel(leg)" class="mt-0.5 text-xs"
                             :class="seatsLapsed(leg) ? 'text-amber-700 dark:text-amber-400 font-medium' : 'text-gray-500 dark:text-gray-400'">
                            @{{ seatHoldLabel(leg) }}
                        </div>
                    </div>
                    <button type="button" @click="remove(index)"
                        class="text-xs shrink-0"
                        :class="isInvalid(leg) ? 'font-semibold text-red-700 dark:text-red-300 hover:text-red-900' : 'text-red-600 hover:text-red-800 dark:text-red-400'">
                        {{ __('messages.remove') }}
                    </button>
                </li>
            </ul>

            {{-- The buyer was previously asked to check out without ever being shown a number. These
                 prices come from localStorage and are for orientation only - checkout re-reads and
                 re-prices every ticket from the database, so nothing here is trusted. --}}
            <div v-if="orderTotal !== null" class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between text-sm font-semibold text-gray-900 dark:text-gray-100">
                    <span>{{ __('messages.total') }}</span>
                    <span>@{{ formatMoney(orderTotal, legs[0] && legs[0].currency) }}</span>
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('messages.cart_total_estimate') }}</p>
            </div>

            <form method="post" action="{{ route('event.checkout', ['subdomain' => $role->subdomain]) }}" class="mt-5"
                  @submit="onSubmit">
                @csrf
                {{-- Signed-out only. A password manager will happily fill a decoy field, so handing
                     one to an authenticated buyer trips the check on a legitimate submission -
                     which is why HoneypotTest asserts a signed-in visitor sees no name="website"
                     anywhere on the page. --}}
                @guest
                    <x-honeypot />
                @endguest
                <template v-for="(leg, index) in legs">
                    <input type="hidden" :name="'legs[' + index + '][event_id]'" :value="leg.event_id">
                    <input type="hidden" :name="'legs[' + index + '][event_date]'" :value="leg.event_date">
                    <input type="hidden" v-if="leg.promo_code" :name="'legs[' + index + '][promo_code]'" :value="leg.promo_code">
                    <template v-for="(qty, ticketId) in leg.tickets">
                        <input type="hidden" :name="'legs[' + index + '][tickets][' + ticketId + ']'" :value="qty">
                    </template>
                    <template v-for="(qty, addonId) in leg.addons">
                        <input type="hidden" :name="'legs[' + index + '][addons][' + addonId + ']'" :value="qty">
                    </template>
                </template>

                {{-- Asked ONCE. A signed-in buyer is already known, and a signed-out one typed
                     their name and email into the ticket form on the way here - addToCart() sends
                     them along with the leg. Asking again in this panel was the same two questions
                     twice on one screen.

                     The fields are still here for the case where we genuinely do not know: a cart
                     restored from a browser that stored legs before this existed, or a leg added
                     while the ticket form's own fields were empty. Hiding them unconditionally
                     would post a blank name, and the refusal would come back with nothing on
                     screen to fix. --}}
                {{-- Audience opt-in. The cart can span several schedules, so the copy says
                     "these schedules" and TicketController::captureAudienceOptIn() captures every
                     distinct one in the basket rather than arbitrarily picking the first leg.

                     Signed-out only, like the honeypot above: a signed-in buyer already has an
                     account to follow with. Unchecked by default, per GDPR Art. 4(11).

                     No schedule name is interpolated here, so no v-pre is needed - unlike the
                     single-event forms, which name the schedule inside a Vue mount. --}}
                @guest
                    <div class="mt-4 flex items-start">
                        <input id="es-cart-audience-opt-in" name="audience_opt_in" type="checkbox" value="1"
                            class="mt-1 h-4 w-4 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)] border-gray-300 dark:border-gray-600 rounded">
                        <label for="es-cart-audience-opt-in" class="ms-3 block text-sm text-gray-700 dark:text-gray-300">
                            {{ __('messages.audience_opt_in_label_cart') }}
                        </label>
                    </div>
                @endguest

                <template v-if="knowsBuyer">
                    <input type="hidden" name="name" :value="name">
                    <input type="hidden" name="email" :value="email">
                </template>
                <template v-else>
                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1" for="es-cart-name">{{ __('messages.name') }}</label>
                    <input id="es-cart-name" name="name" v-model="name" required
                        class="w-full mb-3 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 text-sm">

                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1" for="es-cart-email">{{ __('messages.email') }}</label>
                    <input id="es-cart-email" name="email" type="email" v-model="email" required
                        class="w-full mb-4 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 text-sm">
                </template>

                {{-- Shown when ANY leg asks for a phone and required when ANY leg requires one -
                     the same union TicketCheckoutRequest applies across legs. Without the field the
                     cart could not satisfy a require_phone event at all: checkout failed validation
                     and the redirect back showed nothing, so the button just appeared dead. --}}
                <template v-if="asksPhone">
                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1" for="es-cart-phone">{{ __('messages.phone') }}</label>
                    <input id="es-cart-phone" name="phone" type="tel" v-model="phone" :required="requiresPhone"
                        class="w-full mb-4 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 text-sm">
                </template>

                {{-- The server has applied order-level gift cards since resolveOrderGiftCard(), but
                     the cart had no way to send one, so a cart buyer could not spend a card the
                     single-event form would have taken.

                     Gated on the schedule accepting them at all, the same test the single-event
                     form applies per event - otherwise a schedule that has never issued a card
                     offers a field that can only ever fail. Unlike that form there is no
                     pre-validation round trip here, so a wrong code is refused at checkout: that is
                     why the panel restores old() below, or a typo would also wipe the name, email
                     and phone the buyer had typed. --}}
                @if ($role->giftCardsEnabled())
                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1" for="es-cart-gift-card">{{ __('messages.gift_card_code') }}</label>
                    <input id="es-cart-gift-card" name="gift_card_code" v-model="giftCardCode" maxlength="20"
                        class="w-full mb-4 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 text-sm">
                @endif

                @if (! auth()->check() && config('app.hosted'))
                    {{-- Matches the single-event form, which asks a signed-out hosted buyer to
                         accept the terms before taking their money. --}}
                    <label class="flex items-start gap-2 mb-4 text-xs text-gray-600 dark:text-gray-400">
                        <input type="checkbox" name="terms" value="1" required
                            class="mt-0.5 h-4 w-4 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                        <span>{!! str_replace([':terms', ':privacy'], [
                            '<a href="'.policy_url('terms').'" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">'.__('messages.terms_of_service').'</a>',
                            '<a href="'.policy_url('privacy').'" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">'.__('messages.privacy_policy').'</a>',
                        ], __('messages.i_accept_the_terms_and_privacy')) !!}</span>
                    </label>
                @endif

                @if (\App\Utils\TurnstileUtils::isActiveForRequest())
                    {{-- Rendered explicitly rather than by Cloudflare's auto-scan: this panel lives
                         inside a v-if, so the container does not exist at DOMContentLoaded when the
                         auto-scan runs. Same reason event/tickets.blade.php renders its widget by
                         hand. The token is submitted through a bound hidden input because the
                         widget's own input would be outside Vue's control. --}}
                    <div id="es-cart-turnstile" class="mb-4"></div>
                    <input type="hidden" name="cf-turnstile-response" :value="turnstileToken">
                @endif

                <button type="submit" dusk="cart-checkout"
                    class="w-full rounded-lg bg-[var(--brand-button-bg)] hover:bg-[var(--brand-button-bg-hover)] text-white font-semibold px-4 py-3 transition-all duration-200">
                    {{ strtoupper(__('messages.checkout')) }}
                </button>
            </form>
        </div>
    </template>
</div>

<script {!! nonce_attr() !!}>
window.addEventListener('DOMContentLoaded', function () {
    if (typeof Vue === 'undefined') {
        return;
    }

    var storageKey = 'es_cart_' + @json($role->subdomain);
    var buyerKey = storageKey + '_buyer';
    var invalidLegs = @json(array_values((array) $cartInvalidLegs));

    Vue.createApp({
        data: function () {
            return {
                legs: [],
                // Ticks the seat-hold countdown above. One interval for the whole panel.
                now: Date.now(),
                clock: null,
                // Opened when the last checkout was refused, so the message below is on screen
                // rather than hidden behind the cart button.
                open: @json((bool) $cartHasError),
                // Restored from old() so a refused checkout - a wrong gift-card code, an
                // unavailable leg, a failed challenge - does not also throw away everything the
                // buyer typed. withInput() was already being sent; nothing was reading it.
                name: @json(old('name', auth()->check() ? auth()->user()->name : '')),
                email: @json(old('email', auth()->check() ? auth()->user()->email : '')),
                phone: @json(old('phone', auth()->check() ? auth()->user()->phone : '')),
                giftCardCode: @json(old('gift_card_code', '')),
                turnstileToken: '',
                turnstileWidgetId: null,
            };
        },
        computed: {
            // Enough to check out without asking again. Both, because the server requires both.
            knowsBuyer: function () {
                return !! (String(this.name || '').trim() && String(this.email || '').trim());
            },
            // Union across legs, matching TicketCheckoutRequest::rules().
            //
            // A leg stored before these flags existed counts as "asks": the server unions
            // require_phone from the live event regardless, so hiding the field for such a leg
            // produced "The phone field is required" with no field anywhere on screen and no way
            // out but deleting and re-adding it. Showing an optional field costs nothing.
            asksPhone: function () {
                return this.legs.some(function (leg) {
                    return leg.ask_phone || ! ('ask_phone' in leg);
                });
            },
            requiresPhone: function () {
                return this.legs.some(function (leg) { return leg.require_phone; });
            },
            // null rather than a figure that would mislead: when a leg predates the stored prices,
            // and when the legs are not all in one currency. Adding a EUR leg to a USD one and
            // printing the sum in whichever currency came first is worse than printing nothing -
            // checkout refuses a mixed-currency cart anyway, but only after the buyer has been
            // shown a number.
            orderTotal: function () {
                var total = 0;
                var currency = null;

                for (var i = 0; i < this.legs.length; i++) {
                    var price = this.legPrice(this.legs[i]);
                    if (price === null) {
                        return null;
                    }

                    if (currency === null) {
                        currency = this.legs[i].currency;
                    } else if (this.legs[i].currency !== currency) {
                        return null;
                    }

                    total += price;
                }

                return total;
            },
        },
        created: function () {
            this.legs = this.read();
            this.restoreBuyer();

            var self = this;
            window.addEventListener('es-cart-add', function (event) {
                var detail = Object.assign({}, event.detail || {});

                // buyer is order-level and must not ride along as leg data - the legs are keyed by
                // event and date, and one buyer covers the whole order.
                if (detail.buyer) {
                    self.rememberBuyer(detail.buyer);
                    delete detail.buyer;
                }

                self.add(detail);
            });
        },
        mounted: function () {
            if (this.legs.length) {
                this.$nextTick(this.renderTurnstile);
            }

            // Only while something in the basket is actually on a clock, so an ordinary cart pays
            // nothing for this.
            var self = this;
            this.clock = setInterval(function () {
                if (self.legs.some(function (leg) { return !! leg.seats_expire_at; })) {
                    self.now = Date.now();
                }
            }, 1000);
        },
        beforeUnmount: function () {
            if (this.clock) {
                clearInterval(this.clock);
            }
        },
        watch: {
            // The panel (and so the widget container) only exists once there is something in the
            // cart, so the first leg added is the earliest point this can render - and emptying the
            // cart destroys that container with it. Without the teardown the widget id outlived its
            // iframe, so refilling the cart hit the "already rendered" guard, left an empty
            // container, and submitted a blank token. Since ValidTurnstile became implicit that is
            // a hard rejection with no widget on screen to solve, recoverable only by reloading.
            'legs.length': function (count) {
                if (count > 0) {
                    this.$nextTick(this.renderTurnstile);

                    return;
                }

                if (this.turnstileWidgetId !== null) {
                    if (typeof turnstile !== 'undefined') {
                        try { turnstile.remove(this.turnstileWidgetId); } catch (e) {}
                    }

                    this.turnstileWidgetId = null;
                    this.turnstileToken = '';
                }
            },
        },
        methods: {
            /**
             * How long this leg's seats are still held, or that they have gone.
             *
             * Display only. The hold lives in the session and the checkout claims from there, so
             * nothing here is trusted - this exists because a buyer who leaves the event page had
             * no way at all to know the clock was running.
             */
            seatHoldLabel: function (leg) {
                if (! leg.seats_expire_at) {
                    return '';
                }

                var left = Math.floor((leg.seats_expire_at - this.now) / 1000);

                if (left <= 0) {
                    return @json(__('messages.seating_hold_lapsed'));
                }

                var m = Math.floor(left / 60);
                var sec = left % 60;

                return @json(__('messages.seating_holding_for')) + ' ' + m + ':' + String(sec).padStart(2, '0');
            },
            seatsLapsed: function (leg) {
                return !! leg.seats_expire_at && leg.seats_expire_at <= this.now;
            },
            read: function () {
                try {
                    var raw = localStorage.getItem(storageKey);
                    var parsed = raw ? JSON.parse(raw) : [];

                    return Array.isArray(parsed) ? parsed : [];
                } catch (e) {
                    return [];
                }
            },
            persist: function () {
                try {
                    localStorage.setItem(storageKey, JSON.stringify(this.legs));
                } catch (e) {}
            },
            /**
             * Remember who is buying, so the panel does not have to ask.
             *
             * Beside the cart rather than in it, because a buyer is order-level while a leg is per
             * event and date. Only ever filled in from what the buyer has already typed on this
             * site, and only overwritten by a later non-empty value - adding a second leg from a
             * form they left blank must not wipe the details the first one carried.
             */
            rememberBuyer: function (buyer) {
                ['name', 'email', 'phone'].forEach(function (field) {
                    var value = String((buyer || {})[field] || '').trim();
                    if (value) this[field] = value;
                }, this);

                try {
                    localStorage.setItem(buyerKey, JSON.stringify({
                        name: this.name, email: this.email, phone: this.phone,
                    }));
                } catch (e) {}
            },
            restoreBuyer: function () {
                // old() and the signed-in account both beat storage: one is what they just typed,
                // the other is who they actually are.
                if (this.knowsBuyer) return;

                try {
                    var saved = JSON.parse(localStorage.getItem(buyerKey) || '{}');
                    this.name = this.name || saved.name || '';
                    this.email = this.email || saved.email || '';
                    this.phone = this.phone || saved.phone || '';
                } catch (e) {}
            },
            /**
             * Render the Turnstile widget once the panel exists.
             *
             * Cloudflare's auto-scan runs at DOMContentLoaded, when this panel is still behind a
             * v-if and its container does not exist - hence explicit render, the same thing
             * event/tickets.blade.php does for the single-event form.
             *
             * The whole body is compiled away when Turnstile is not active for this request. Not
             * just dead code: the Cloudflare URL below would otherwise appear in the source of
             * every guest page, including the appointment reschedule page, which deliberately
             * forces Turnstile off and asserts the host never appears there.
             */
            renderTurnstile: function () {
                @if (\App\Utils\TurnstileUtils::isActiveForRequest())
                var container = document.getElementById('es-cart-turnstile');
                if (! container || this.turnstileWidgetId !== null) {
                    return;
                }

                if (typeof turnstile === 'undefined') {
                    // Injected rather than emitted as a tag, so the event page - whose head
                    // already loads it - does not pull it down twice. CSP allows the host
                    // (script-src lists challenges.cloudflare.com, no strict-dynamic).
                    if (! document.getElementById('es-turnstile-api')) {
                        var api = document.createElement('script');
                        api.id = 'es-turnstile-api';
                        api.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';
                        api.async = true;
                        api.defer = true;
                        document.head.appendChild(api);
                    }

                    setTimeout(this.renderTurnstile, 100);

                    return;
                }

                var self = this;
                this.turnstileWidgetId = turnstile.render('#es-cart-turnstile', {
                    sitekey: @json(\App\Utils\TurnstileUtils::getSiteKey()),
                    size: 'flexible',
                    retry: 'auto',
                    'refresh-expired': 'auto',
                    callback: function (token) { self.turnstileToken = token; },
                    'error-callback': function () {
                        self.turnstileToken = '';

                        return true;
                    },
                });
                @endif
            },
            /**
             * Hold the submit until the challenge has produced a token.
             *
             * The single-event form does the same (validateForm in event/tickets.blade.php). Without
             * it an impatient click posts an empty cf-turnstile-response, which is now a hard
             * rejection rather than a silent pass - so the buyer would be bounced for doing nothing
             * wrong. Only guards the token: everything else is the server's job.
             */
            onSubmit: function (event) {
                @if (\App\Utils\TurnstileUtils::isActiveForRequest())
                if (! this.turnstileToken) {
                    event.preventDefault();
                    window.alert(@json(__('messages.turnstile_verification_failed')));
                }
                @endif
            },
            add: function (leg) {
                // One entry per event AND date: two dates of a recurring event are separate legs,
                // which is exactly how the server keys them.
                var index = this.legs.findIndex(function (existing) {
                    return existing.event_id === leg.event_id && existing.event_date === leg.event_date;
                });

                if (index === -1) {
                    this.legs.push(leg);
                } else {
                    this.legs.splice(index, 1, leg);
                }

                this.persist();
                this.open = true;
            },
            remove: function (index) {
                this.legs.splice(index, 1);
                this.persist();
            },
            ticketCount: function (leg) {
                return Object.values(leg.tickets || {}).reduce(function (total, qty) {
                    return total + Number(qty);
                }, 0);
            },
            // Sum of the unit prices the event page recorded alongside the quantities. null when
            // this leg carries none, which is how a leg stored before prices existed reads.
            legPrice: function (leg) {
                if (! leg.prices) {
                    return null;
                }

                var total = 0;
                var ids = Object.keys(leg.tickets || {});

                for (var i = 0; i < ids.length; i++) {
                    var unit = leg.prices[ids[i]];
                    if (unit === undefined || unit === null) {
                        return null;
                    }
                    total += Number(unit) * Number(leg.tickets[ids[i]]);
                }

                return total;
            },
            formatMoney: function (amount, currency) {
                try {
                    return new Intl.NumberFormat(undefined, {
                        style: 'currency',
                        currency: currency || 'USD',
                    }).format(amount);
                } catch (e) {
                    return (currency ? currency + ' ' : '') + Number(amount).toFixed(2);
                }
            },
            isInvalid: function (leg) {
                return invalidLegs.indexOf(leg.event_id + '|' + (leg.event_date || '')) !== -1;
            },
        },
    }).mount('#es-cart-app');
});
</script>
@endif
