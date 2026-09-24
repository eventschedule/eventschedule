{{--
    The ticket fee calculator: what each platform takes from one event at its published US rates,
    recomputed as the visitor changes the number of tickets or the price.

    Every figure comes from App\Utils\TicketFees. The first paint is TicketFees::cost(), so the
    panel is right with JavaScript off and for crawlers; the script recomputes from the SAME rates,
    handed over as data-rates, through the same formula (marketing/partials/ticket-fee-math.blade.php).
    This used to live inline in /compare, where the script retyped every rate - while its comment
    said it read them from the server - and worked Luma out differently from the first paint.

    A dollar calculator on purpose, our own column included. Every other figure in it is a
    competitor's published US pricing, so rendering ours through plan_price() would put "R9/mo"
    beside a "$247.50" saving and compare two currencies. USD or nothing.

    Props:
      platforms  TicketFees keys to show, ours first; four or eight fill the grid's rows exactly
      tickets    tickets sold, as the panel opens
      price      the ticket price in dollars, likewise
      id         prefix for the inputs' ids, so each label can point at its field
--}}
@props([
    'platforms' => \App\Utils\TicketFees::COMPARE_PLATFORMS,
    'tickets' => 100,
    'price' => 10,
    'id' => 'fc',
])
@php
    $feeRates = \App\Utils\TicketFees::rates();
    $feeCosts = [];
    foreach ($platforms as $feePlatform) {
        $feeCosts[$feePlatform] = \App\Utils\TicketFees::cost($feePlatform, $tickets, $price, $feeRates);
    }
    $feeWorst = $feeCosts ? max($feeCosts) : 0;
    $feeSaving = max(0, $feeWorst - ($feeCosts['eventschedule'] ?? $feeWorst));

    // Ours is the card that states the plan price, so it is the one line built here rather than
    // read off a label: TicketFees has the amount, and this is where it becomes a dollar figure.
    $feeCards = [];
    foreach ($platforms as $feePlatform) {
        $feeCards[] = [
            'key' => $feePlatform,
            'value' => $feeCosts[$feePlatform],
            'best' => $feePlatform === 'eventschedule',
            'note' => $feePlatform === 'eventschedule'
                ? '$'.$feeRates['eventschedule']['monthly'].'/mo + Stripe, 0% platform fee'
                : $feeRates[$feePlatform]['label'],
        ];
    }

    // Full class strings: an interpolated Tailwind class is never generated.
    $feeGrid = (count($platforms) % 3 === 0 && count($platforms) % 4 !== 0) ? 'lg:grid-cols-3' : 'lg:grid-cols-4';

    // The footnote says how each figure was worked out, in the order the cards run.
    $feeOnStripe = array_values(array_filter($platforms, fn ($p) => $feeRates[$p]['stripe'] ?? true));
    $feeStripeNames = array_map(fn ($p) => $feeRates[$p]['name'], $feeOnStripe);
    $feeStripeList = count($feeStripeNames) > 1
        ? implode(', ', array_slice($feeStripeNames, 0, -1)).' and '.end($feeStripeNames)
        : implode('', $feeStripeNames);
    $feeBasis = array_values(array_filter(array_map(
        fn ($p) => $p === 'eventschedule' ? null : ($feeRates[$p]['basis'] ?? null),
        $platforms
    )));
@endphp
<div {{ $attributes->merge(['class' => 'rounded-3xl border border-gray-200 bg-gray-50 p-6 shadow-sm dark:border-white/10 dark:bg-white/[0.04] sm:p-10']) }}
     data-fee-calculator
     data-fee-tickets="{{ $tickets }}"
     data-fee-price="{{ $price }}"
     data-rates="{{ json_encode(\App\Utils\TicketFees::forScript($platforms, $feeRates)) }}">

    <div class="mb-10 flex flex-col items-center justify-center gap-6 sm:flex-row">
        <div class="flex items-center gap-3">
            <label for="{{ $id }}-tickets" class="whitespace-nowrap text-sm font-medium text-gray-700 dark:text-gray-300">Tickets sold</label>
            <input id="{{ $id }}-tickets" data-fee-input="tickets" type="number" value="{{ $tickets }}" min="1" max="100000" class="w-28 rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
        </div>
        <div class="flex items-center gap-3">
            <label for="{{ $id }}-price" class="whitespace-nowrap text-sm font-medium text-gray-700 dark:text-gray-300">Ticket price</label>
            <div class="relative">
                <span class="absolute top-1/2 -translate-y-1/2 text-sm text-gray-500 dark:text-gray-400 ltr:left-3 rtl:right-3">$</span>
                <input id="{{ $id }}-price" data-fee-input="price" type="number" value="{{ $price }}" min="1" max="10000" class="w-28 rounded-xl border border-gray-200 bg-white py-2.5 text-sm text-gray-900 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-white/10 dark:bg-white/5 dark:text-white ltr:pl-7 ltr:pr-3 rtl:pr-7 rtl:pl-3">
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 {{ $feeGrid }}" data-reveal-group="70">
        @foreach ($feeCards as $card)
            <div data-reveal class="relative flex flex-col rounded-2xl border p-5 {{ $card['best']
                ? 'border-blue-300 bg-white ring-2 ring-blue-500/25 dark:border-blue-500/40 dark:bg-white/[0.06] dark:ring-blue-400/20'
                : 'border-gray-200 bg-white dark:border-white/10 dark:bg-white/5' }}">
                @if ($card['best'])
                    <span class="absolute -top-3 rounded-full bg-gradient-to-r from-blue-600 to-sky-500 px-3 py-1 text-[11px] font-bold uppercase tracking-wide text-white shadow-lg shadow-blue-500/30 ltr:right-4 rtl:left-4">Ours</span>
                @endif
                <div class="mb-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $feeRates[$card['key']]['name'] }}</div>
                <div class="mb-3 text-xs text-gray-500 dark:text-gray-400">{{ $card['note'] }}</div>
                <div class="mt-auto">
                    <div data-fee-total="{{ $card['key'] }}" class="text-3xl font-black tabular-nums {{ $card['best'] ? 'es-keep' : 'es-cost' }}">${{ number_format($card['value'], 2) }}</div>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                        <div data-fee-bar="{{ $card['key'] }}" class="es-bar h-full rounded-full {{ $card['best'] ? 'bg-emerald-500' : 'bg-rose-500' }}" style="width: {{ $feeWorst > 0 ? max(2, round(($card['value'] / $feeWorst) * 100)) : 0 }}%;"></div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-10 text-center">
        <p class="text-lg text-gray-700 dark:text-gray-300">
            On this event you keep up to
            <span data-fee-saving class="es-od es-keep mx-1 justify-center align-middle text-4xl font-black tabular-nums sm:text-5xl" data-odometer="${{ number_format($feeSaving, 0) }}">${{ number_format($feeSaving, 0) }}</span>
            more.
        </p>
        <a href="{{ app_url('/sign_up') }}" class="group mt-6 inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-7 py-3.5 font-semibold text-white shadow-lg shadow-blue-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
            Start free
            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
        </a>
        <p class="mx-auto mt-5 max-w-2xl text-xs text-gray-500 dark:text-gray-400">
            Estimates from each platform's published US rates, for an organizer who absorbs the fees, with one ticket per order.
            Stripe processing ({{ $feeRates['stripe']['label'] }}) is included for {{ $feeStripeList }}.
            @foreach ($feeBasis as $basis)
                {{ $basis }}
            @endforeach
            {{ $feeRates['eventschedule']['basis'] }}
        </p>
        {{ $slot }}
    </div>
</div>

@include('marketing.partials.ticket-fee-math')

@once
<style {!! nonce_attr() !!}>
    /* The calculator's money rule, carried with it so it reads the same on any page: what leaves
       your pocket is rose, what you keep is emerald, and the savings odometer's digits with it.
       background-image, not the background shorthand, on the digits: the shorthand resets
       background-clip and turns each one into a solid block. */
    [data-fee-calculator] .es-cost { color: #e11d48; }
    .dark [data-fee-calculator] .es-cost { color: #fb7185; }
    [data-fee-calculator] .es-keep { color: #047857; }
    .dark [data-fee-calculator] .es-keep { color: #6ee7b7; }
    [data-fee-calculator] .es-od-strip span {
        background-image: linear-gradient(135deg, #059669 0%, #10b981 50%, #0d9488 100%);
    }
    .dark [data-fee-calculator] .es-od-strip span {
        background-image: linear-gradient(135deg, #6ee7b7 0%, #34d399 50%, #2dd4bf 100%);
    }
</style>
<script {!! nonce_attr() !!}>
    (function () {
        function money(n) { return '$' + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','); }
        function whole(n) { return '$' + Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','); }

        document.querySelectorAll('[data-fee-calculator]').forEach(function (panel) {
            var ticketsEl = panel.querySelector('[data-fee-input="tickets"]');
            var priceEl = panel.querySelector('[data-fee-input="price"]');
            var rates;
            try { rates = JSON.parse(panel.getAttribute('data-rates')); } catch (e) { return; }
            if (!ticketsEl || !priceEl || !rates || !rates.stripe || !window.esTicketFeeCost) return;

            var platforms = Object.keys(rates).filter(function (key) { return key !== 'stripe'; });
            var saving = panel.querySelector('[data-fee-saving]');

            function calc() {
                var tickets = parseFloat(ticketsEl.value) || 0;
                var price = parseFloat(priceEl.value) || 0;
                var costs = {};
                var worst = 0;

                platforms.forEach(function (key) {
                    costs[key] = window.esTicketFeeCost(rates[key], rates.stripe, tickets, price);
                    worst = Math.max(worst, costs[key]);
                });

                platforms.forEach(function (key) {
                    var total = panel.querySelector('[data-fee-total="' + key + '"]');
                    var bar = panel.querySelector('[data-fee-bar="' + key + '"]');
                    if (total) total.textContent = money(costs[key]);
                    if (bar) bar.style.width = (worst > 0 ? Math.max(2, Math.round((costs[key] / worst) * 100)) : 0) + '%';
                });

                if (saving) {
                    var ours = costs.eventschedule === undefined ? worst : costs.eventschedule;
                    saving.textContent = whole(Math.max(0, worst - ours));
                }
            }

            ticketsEl.addEventListener('input', calc);
            priceEl.addEventListener('input', calc);
        });
    })();
</script>
@endonce
