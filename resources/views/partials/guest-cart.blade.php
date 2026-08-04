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
@if (! request()->embed)
<div id="es-cart-app" class="print:hidden">
    <template v-if="legs.length > 0">
        <div class="fixed bottom-4 {{ $role->isRtl() ? 'left-4' : 'right-4' }} z-40">
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
             class="fixed bottom-20 {{ $role->isRtl() ? 'left-4' : 'right-4' }} z-40 w-[min(22rem,calc(100vw-2rem))] rounded-2xl bg-white dark:bg-[#1e1e1e] border border-gray-200 dark:border-[#2d2d30] shadow-2xl p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.your_cart') }}</h2>
                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" aria-label="{{ __('messages.close') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <ul class="space-y-3 max-h-64 overflow-y-auto">
                <li v-for="(leg, index) in legs" :key="leg.event_id + '|' + leg.event_date"
                    class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        {{-- Vue interpolation, not server-rendered: the name comes from localStorage
                             and is bound as text, so it is never compiled as a template. --}}
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">@{{ leg.event_name }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            @{{ leg.event_date }} &middot; @{{ ticketCount(leg) }} {{ __('messages.tickets') }}
                        </div>
                    </div>
                    <button type="button" @click="remove(index)"
                        class="text-xs text-red-600 hover:text-red-800 dark:text-red-400 shrink-0">
                        {{ __('messages.remove') }}
                    </button>
                </li>
            </ul>

            <form method="post" action="{{ route('event.checkout', ['subdomain' => $role->subdomain]) }}" class="mt-5">
                @csrf
                {{-- Signed-out only. A password manager will happily fill a decoy field, so handing
                     one to an authenticated buyer trips the check on a legitimate submission -
                     which is why HoneypotTest asserts a signed-in visitor sees no name="website"
                     anywhere on the page. --}}
                @guest
                    <x-honeypot />
                @endguest
                <input type="hidden" name="cart_checkout" value="1">
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

                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1" for="es-cart-name">{{ __('messages.name') }}</label>
                <input id="es-cart-name" name="name" v-model="name" required
                    class="w-full mb-3 rounded-lg border-gray-300 dark:border-[#2d2d30] dark:bg-[#252526] dark:text-gray-300 text-sm">

                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1" for="es-cart-email">{{ __('messages.email') }}</label>
                <input id="es-cart-email" name="email" type="email" v-model="email" required
                    class="w-full mb-4 rounded-lg border-gray-300 dark:border-[#2d2d30] dark:bg-[#252526] dark:text-gray-300 text-sm">

                <button type="submit"
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

    Vue.createApp({
        data: function () {
            return { legs: [], open: false, name: '', email: '' };
        },
        created: function () {
            this.legs = this.read();

            var self = this;
            window.addEventListener('es-cart-add', function (event) {
                self.add(event.detail);
            });
        },
        methods: {
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
        },
    }).mount('#es-cart-app');
});
</script>
@endif
