<x-marketing-layout :hp="true">
    <x-slot name="title">Ticket Fee Calculator: What Each Platform Takes Per Ticket</x-slot>
    <x-slot name="description">Work out what Eventbrite, Ticket Tailor, TicketLeap, Luma and others take per ticket, line by line, and the price that covers the fees. 0% platform fee here.</x-slot>
    <x-slot name="breadcrumbTitle">Ticket Fee Calculator</x-slot>

    {{-- Every rate on this page is App\Utils\TicketFees: the calculator's figures, the rate lines on
         the cards below and the worked example in the FAQ. The prose around them explains each fee
         MODEL (who pays by default, what is capped, what processing is on top), from the same
         vendor pages TicketFees cites with the date each was checked. Ticket Tailor's band was
         re-checked on 2026-11-11 from its pricing script; see its comment there.

         Differentiation: /compare is the feature-by-feature hub with a four-platform calculator,
         /pricing explains what Event Schedule costs, each /x-alternative page is one head-to-head.
         This page is the calculator itself, across every platform we have a published rate for,
         and how each one charges. --}}

    @php
        $feeRates = \App\Utils\TicketFees::rates();

        // Competitors' published US prices, written out as a reader would: dollars (whole ones
        // without the cents) and percentages without trailing zeros. Never our own plan price,
        // which goes through plan_price() like everywhere else.
        $usd = fn ($amount) => '$'.(floor($amount) == $amount ? number_format($amount) : number_format($amount, 2));
        $pct = fn ($share) => rtrim(rtrim(number_format($share * 100, 2), '0'), '.').'%';
        $exampleEventbrite = \App\Utils\TicketFees::cost('eventbrite', 1, \App\Utils\TicketFees::EXAMPLE_PRICE, $feeRates);

        // "Eventbrite, Luma, ... and Hi.Events": every platform the calculator sets against ours.
        $rivalNames = array_map(
            fn ($key) => $feeRates[$key]['name'],
            array_values(array_diff(\App\Utils\TicketFees::CALCULATOR_PLATFORMS, ['eventschedule']))
        );
        $rivalList = implode(', ', array_slice($rivalNames, 0, -1)).' and '.end($rivalNames);

        // How each platform charges, in the calculator's order. `rate` is the line a card leads
        // with, straight from TicketFees; `how` is the model around it; `source` is where it comes
        // from; `route` is the head-to-head, where one exists.
        $models = [
            [
                'key' => 'eventschedule',
                'rate' => '0% platform fee on every plan',
                'how' => 'Free registration costs nothing on any plan. A ticket with a price needs Pro, at '.plan_price($proMonthly).' a month, and the buyer pays through your own Stripe or PayPal account, so the only deduction is that processor\'s own fee: '.$feeRates['stripe']['label'].' with Stripe in the US.',
                'source' => 'Our own pricing page',
                'route' => 'marketing.pricing',
                'link' => 'See the plans',
            ],
            [
                'key' => 'eventbrite',
                'rate' => $feeRates['eventbrite']['label'],
                'how' => 'A service fee of '.$pct($feeRates['eventbrite']['percent']).' + '.$usd($feeRates['eventbrite']['fixed']).' on each paid ticket, and a separate '.$pct($feeRates['eventbrite']['processing']).' payment processing fee on each order, with no cap on either. Buyers pay them by default, or you absorb them. Free events carry no fees.',
                'source' => 'eventbrite.com/organizer/pricing',
                'route' => 'marketing.compare_eventbrite',
                'link' => 'Eventbrite alternative',
            ],
            [
                'key' => 'luma',
                'rate' => $feeRates['luma']['label'],
                'how' => 'A '.$pct($feeRates['luma']['plans'][0]['percent']).' platform fee on paid events on the free plan, or none on Luma Plus, billed annually. Stripe\'s processing fee is charged on top of either, so the calculator uses whichever plan is cheaper for your event.',
                'source' => 'luma.com/pricing',
                'route' => 'marketing.compare_luma',
                'link' => 'Luma alternative',
            ],
            [
                'key' => 'ticket-tailor',
                'rate' => $feeRates['ticket-tailor']['label'],
                'how' => 'A flat fee on each paid ticket rather than a share of its price, with Stripe, PayPal or Square charging its own processing on top. It is $0.85 a ticket pay as you go and less with prepaid credits, so the calculator uses the middle of the range.',
                'source' => null,
                'route' => 'marketing.compare_ticket_tailor',
                'link' => 'Ticket Tailor alternative',
            ],
            [
                'key' => 'ticketleap',
                'rate' => $feeRates['ticketleap']['label'],
                'how' => $usd($feeRates['ticketleap']['fixed']).' + '.$pct($feeRates['ticketleap']['percent']).' of the price on each ticket, or a flat '.$usd($feeRates['ticketleap']['low_fixed']).' on a ticket of '.$usd($feeRates['ticketleap']['low_price']).' or less, capped at '.$usd($feeRates['ticketleap']['cap']).' a ticket. On top of that, a '.$pct($feeRates['ticketleap']['processing']).' online transaction fee on each order, with no cap. No subscription.',
                'source' => 'ticketleap.com/info/pricing',
                'route' => 'marketing.compare_ticketleap',
                'link' => 'TicketLeap alternative',
            ],
            [
                'key' => 'universe',
                'rate' => $feeRates['universe']['label'],
                'how' => 'On the US Starter tier, '.$pct($feeRates['universe']['percent']).' + '.$usd($feeRates['universe']['fixed']).' on each ticket, capped at '.$usd($feeRates['universe']['cap']).', plus a '.$pct($feeRates['universe']['processing']).' processing fee through Universe Payments. Connect your own Stripe account instead and the service fee stays, with Stripe\'s rate in place of the processing fee.',
                'source' => 'support.universe.com, payment processing preferences',
                'route' => 'marketing.compare_universe',
                'link' => 'Universe alternative',
            ],
            [
                'key' => 'allevents',
                'rate' => $feeRates['allevents']['label'],
                'how' => 'A booking fee of '.$usd($feeRates['allevents']['fixed']).' on each paid ticket sold online, charged to the buyer unless you absorb it; free tickets and payments taken offline carry none. Outside India the ticket money goes to your own Stripe or PayPal account, which charges its own processing. Events priced in Indian rupees pay 10% + INR 10 a ticket instead, and AllEvents collects that money itself. Its paid plans raise how many upcoming events you can list at once.',
                'source' => 'allevents.in/pages/pricing',
                'route' => 'marketing.compare_allevents',
                'link' => 'AllEvents alternative',
            ],
            [
                'key' => 'hi-events',
                'rate' => $feeRates['hi-events']['label'],
                'how' => 'Hi.Events Cloud charges '.$pct($feeRates['hi-events']['percent']).' + '.$usd($feeRates['hi-events']['fixed']).' on each paid ticket, with no monthly fee, and payment processing is charged on top. The software is open source, so it can be selfhosted too.',
                'source' => 'hi.events/pricing',
                'route' => 'marketing.compare_hi_events',
                'link' => 'Hi.Events alternative',
            ],
        ];

        // ---- The field guide below the calculator. Every figure is TicketFees::costOf() or the
        //      rate fields it reads, per ticket, one ticket per order, fees absorbed. Our own row is
        //      Stripe alone: the Pro subscription is a monthly cost, not a per-ticket one, and the
        //      cards above already say so.
        $stripeRate = $feeRates['stripe'];
        $money = fn (float $amount) => '$'.number_format($amount, 2);
        $perTicket = fn (string $key, float $price) => \App\Utils\TicketFees::costOf(
            $key === 'eventschedule' ? ['name' => 'Event Schedule'] : $feeRates[$key], $stripeRate, 1, $price
        );
        $feeShare = fn (string $key, float $price) => rtrim(rtrim(number_format($perTicket($key, $price) / $price * 100, 1), '0'), '.').'%';

        // One rate as the parts a reader can see on a statement. Luma is its free plan here (the
        // one a single ticket is charged on); Plus is a subscription, which the cards explain.
        $parts = function (string $key, float $price) use ($feeRates, $stripeRate): array {
            $rate = $key === 'eventschedule' ? [] : $feeRates[$key];
            if ($key === 'luma') {
                $rate = array_merge($rate, $rate['plans'][0]);
            }
            $ownsProcessing = ($rate['stripe'] ?? true) === false;

            return [
                'share' => $price * (float) ($rate['percent'] ?? 0),
                'fixed' => (float) ($rate['fixed'] ?? 0),
                'processing' => $ownsProcessing
                    ? $price * (float) ($rate['processing'] ?? 0)
                    : $price * (float) $stripeRate['percent'] + (float) $stripeRate['fixed'],
                'processor' => $ownsProcessing ? 'its own' : 'Stripe',
            ];
        };

        // $20, not the calculator's $25, for the line-by-line table: every percentage in
        // TicketFees comes out at a whole cent on it, so the lines add up to the total as printed.
        $lineExample = 20;
        $priceColumns = [10, 25, 50, 100];
        $keepTargets = [20, 40, 100];

        // The price that leaves $keep after a platform's fees, solved against costOf() itself, so a
        // cap or a flat fee on cheap tickets is respected exactly. Rounded up to the cent.
        $grossUp = function (string $key, float $keep) use ($perTicket): float {
            $lo = $keep;
            $hi = $keep * 2 + 50;
            for ($i = 0; $i < 80; $i++) {
                $mid = ($lo + $hi) / 2;
                if ($mid - $perTicket($key, $mid) >= $keep) {
                    $hi = $mid;
                } else {
                    $lo = $mid;
                }
            }

            return ceil(round($hi * 100, 6)) / 100;
        };

        // The straight-line form of the same thing, for the worked formula: total share a and total
        // fixed amount b on one ticket, from the rates. Event Schedule's is Stripe's alone.
        $ebLine = [
            'a' => (float) $feeRates['eventbrite']['percent'] + (float) $feeRates['eventbrite']['processing'],
            'b' => (float) $feeRates['eventbrite']['fixed'],
        ];
        $stripeLine = ['a' => (float) $stripeRate['percent'], 'b' => (float) $stripeRate['fixed']];
        $exampleKeep = \App\Utils\TicketFees::EXAMPLE_PRICE;

        // Where the per-ticket caps start to bite: the price at which percent * price + fixed reaches
        // the cap. Only TicketLeap and Universe have one.
        $capFrom = fn (string $key) => ((float) $feeRates[$key]['cap'] - (float) $feeRates[$key]['fixed']) / (float) $feeRates[$key]['percent'];

        $anatomy = [
            ['A share of the price', 'A percentage of the ticket\'s face value, kept by the platform. It grows with the price, so it is the part that hurts most on expensive tickets. Eventbrite\'s is '.$pct($feeRates['eventbrite']['percent']).', Luma\'s free plan '.$pct($feeRates['luma']['plans'][0]['percent']).'.'],
            ['A fixed amount per ticket', 'The same amount whatever the ticket costs, so it is the part that hurts most on cheap tickets. In the US, Ticket Tailor and AllEvents charge only this; Eventbrite adds '.$usd($feeRates['eventbrite']['fixed']).' to its percentage.'],
            ['Payment processing', 'What it costs to take the card. Either the platform charges its own (Eventbrite\'s '.$pct($feeRates['eventbrite']['processing']).' per order), or the money settles in your Stripe account and Stripe takes '.$feeRates['stripe']['label'].'. One way or another, every paid ticket pays it.'],
            ['Caps, floors and subscriptions', 'Some platforms cap the per-ticket fee (TicketLeap at '.$usd($feeRates['ticketleap']['cap']).', reached at about '.$usd(round($capFrom('ticketleap'))).' a ticket), charge less on the cheapest tickets, or swap the percentage for a monthly plan, as Luma Plus does.'],
        ];

        $method = [
            ['Platform fee, then processing', 'A platform fee is what the ticketing company keeps. Processing is what it costs to take a card payment. Some platforms run the payment themselves and charge a processing fee of their own, like Eventbrite\'s '.$pct($feeRates['eventbrite']['processing']).' per order; others settle into your own Stripe account, which charges '.$feeRates['stripe']['label'].'. Every total above includes both.'],
            ['Every fee absorbed', 'Most platforms can add their fee to what the buyer pays instead. That changes who pays it, not whether it is paid, and a higher price at checkout costs sales of its own. So each total is what the event costs you if you absorb the fees, which makes the platforms comparable.'],
            ['One ticket per order', 'Card processors charge a fixed amount on every payment, so an order of four tickets pays it once and four orders of one pay it four times. The calculator assumes one ticket per order, the dearest case. If your buyers usually take several, the fixed part falls for every platform that settles through Stripe.'],
            ['US rates, in dollars', 'These are the rates each platform publishes for the United States, and the calculator works in US dollars throughout. Rates differ by country and currency, so check the platform\'s pricing page for yours. The calculator counts our own plan in dollars too, so every figure in it is in one currency.'],
        ];

        $faqs = [
            [
                'q' => 'How do I work out the fees on my ticket sales?',
                'a' => 'Take the platform\'s percentage of the ticket price, add its fixed fee per ticket, and multiply by the number of tickets. Then add the payment processing: the platform\'s own processing fee if it runs the payment, or your processor\'s rate if the money settles into your own account, which is '.$feeRates['stripe']['label'].' with Stripe in the US. Add any monthly subscription the plan needs. The calculator on this page does that for each platform at its published rates.',
            ],
            [
                'q' => 'How much does Eventbrite charge per ticket?',
                'a' => 'In the United States, a service fee of '.$pct($feeRates['eventbrite']['percent']).' + '.$usd($feeRates['eventbrite']['fixed']).' on each paid ticket and a separate '.$pct($feeRates['eventbrite']['processing']).' payment processing fee on each order. Buyers pay them on top of the ticket price by default. If you absorb them instead, as the calculator on this page does, a single '.$usd(\App\Utils\TicketFees::EXAMPLE_PRICE).' ticket costs you '.$usd($exampleEventbrite).' in fees.',
            ],
            [
                'q' => 'Does Event Schedule charge a fee per ticket?',
                'a' => 'No. There is no platform fee on any plan. Putting a price on a ticket needs Pro, at '.plan_price($proMonthly).' a month, and buyers pay through your own Stripe or PayPal account, so the only deduction is that processor\'s own fee. Free registration costs nothing at all.',
            ],
            [
                'q' => 'Who pays the fees, the buyer or the organizer?',
                'a' => 'It depends on the platform and on you. Eventbrite, Universe, AllEvents and Hi.Events add their fee to what the buyer pays by default, and each lets you absorb it instead; TicketLeap lets you choose either way. Passing a fee on moves it to the buyer rather than removing it, so the calculator shows every fee as if you absorbed it.',
            ],
            [
                'q' => 'Why does the calculator assume one ticket per order?',
                'a' => 'Because a card processor charges a fixed amount on every payment, '.$usd($feeRates['stripe']['fixed']).' at Stripe in the US, and one ticket per order is the case where that adds up the most. If your buyers usually take two or three tickets at a time, the fixed part falls for every platform that settles through Stripe.',
            ],
            [
                'q' => 'Are these fees the same outside the United States?',
                'a' => 'No. These are US rates in US dollars. Platforms publish different rates for other countries and currencies: Universe charges different service and processing fees in Canada, the UK and Europe, and Eventbrite publishes its fees country by country. Check the platform\'s pricing page for your country before you compare.',
            ],
            [
                'q' => 'Where do these numbers come from?',
                'a' => 'From each platform\'s own published pricing for the United States, read from its pricing page or help center, and each card above names its source. Ticket Tailor charges a range, depending on whether you prepay, so its card uses the middle of it. Platforms change their prices, so confirm the figure on the platform\'s own page before you decide.',
            ],
            [
                'q' => 'What price should I charge to cover ticket fees?',
                'a' => 'Divide what you want to keep plus every fixed fee per ticket by one minus every percentage fee. With Stripe alone, keeping '.$usd($exampleKeep).' takes ('.$usd($exampleKeep).' + '.$money($stripeLine['b']).') / (1 - '.rtrim(rtrim(number_format($stripeLine['a'], 4), '0'), '.').'), so a price of '.$money($grossUp('eventschedule', $exampleKeep)).'. On Eventbrite, with fees absorbed, the same '.$usd($exampleKeep).' takes '.$money($grossUp('eventbrite', $exampleKeep)).'. Round up to the cent. Adding the percentage to the price falls short, because the fee is charged on the higher price as well.',
            ],
            [
                'q' => 'Should I pass ticket fees on to buyers or absorb them?',
                'a' => 'Absorb them, or build them into the price, when buyers expect a round figure or are sensitive to price, and pass them on when the fee is small next to the ticket and your audience is used to seeing one. Passing a fee on does not remove it; it moves it to the last step of checkout, where some buyers leave. Some places also require the price to be shown with mandatory fees included from the start, so check the rules where you sell.',
            ],
            [
                'q' => 'How much does Ticket Tailor charge per ticket?',
                'a' => 'In the US, a flat fee on each paid ticket rather than a share of its price: $0.85 a ticket pay as you go, less with prepaid credits, which Ticket Tailor headlines as from $0.30 a ticket. Your payment processor charges its own fee on top, '.$feeRates['stripe']['label'].' with Stripe. At the middle of that range, as the calculator uses it, one '.$usd(\App\Utils\TicketFees::EXAMPLE_PRICE).' ticket costs you '.$money($perTicket('ticket-tailor', \App\Utils\TicketFees::EXAMPLE_PRICE)).' in fees.',
            ],
            [
                'q' => 'How much does TicketLeap charge per ticket?',
                'a' => 'In the US, '.$usd($feeRates['ticketleap']['fixed']).' + '.$pct($feeRates['ticketleap']['percent']).' of the price on each ticket, or a flat '.$usd($feeRates['ticketleap']['low_fixed']).' on a ticket of '.$usd($feeRates['ticketleap']['low_price']).' or less, with the per-ticket fee capped at '.$usd($feeRates['ticketleap']['cap']).'. On top of that comes a '.$pct($feeRates['ticketleap']['processing']).' online transaction fee on each order, which has no cap. With every fee absorbed, one '.$usd(\App\Utils\TicketFees::EXAMPLE_PRICE).' ticket costs you '.$money($perTicket('ticketleap', \App\Utils\TicketFees::EXAMPLE_PRICE)).'.',
            ],
        ];
    @endphp

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Ticket Fee Calculator"
        description="A ticket fee calculator comparing what Event Schedule, Eventbrite, Luma, Ticket Tailor, TicketLeap, Universe, AllEvents and Hi.Events take from one event's ticket sales, at their published US rates."
        keywords="ticket fee calculator, ticketing fee calculator, eventbrite fee calculator, ticket platform fees, eventbrite fees, ticketing fees comparison"
        :mentions="['Eventbrite', 'Luma', 'Ticket Tailor', 'TicketLeap', 'Universe', 'AllEvents', 'Hi.Events']" />
    </x-slot>

    {{-- Motion gate: the hidden pre-reveal states only apply when this class is present, so
         no-JS visitors, crawlers and reduced-motion users always see the whole page. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <style {!! nonce_attr() !!}>
        /* The page accent: the site's blue family, spent on the one phrase that matters. */
        .text-gradient-fees {
            background: linear-gradient(135deg, #1d4ed8 0%, #0284c7 55%, #0891b2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-fees,
        .es-finale-panel .text-gradient-fees {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 55%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>

    <!-- ============================================================ -->
    <!-- Hero                                                         -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero relative flex min-h-[calc(46svh-4rem)] items-center overflow-hidden bg-white py-14 dark:bg-[#0a0a0f] noise">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(37, 99, 235, 0.26), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(14, 165, 233, 0.24), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(16, 185, 129, 0.12), rgba(16, 185, 129, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="absolute inset-0 grid-pattern"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <h1 class="es-balance mb-5 text-[2.5rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <x-marketing.hero-eyebrow class="es-fade-up es-d-1 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5 mb-6">
                    <svg aria-hidden="true" class="h-5 w-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V13.5Zm0 2.25h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V18Zm2.498-6.75h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V13.5Zm0 2.25h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V18Zm2.504-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V18Zm2.498-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5ZM8.25 6h7.5v2.25h-7.5V6ZM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.65 4.5 4.757V19.5a2.25 2.25 0 0 0 2.25 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25V4.757c0-1.108-.806-2.057-1.907-2.185A48.507 48.507 0 0 0 12 2.25Z" />
                    </svg>
                    <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Ticket fee calculator</span>
                </x-marketing.hero-eyebrow>
                <span class="es-mask"><span class="es-mask-line">What each platform</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-fees">takes from a ticket</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Enter how many tickets you expect to sell and what they cost, and see what {{ $rivalList }} would take at their published US rates, next to Event Schedule's zero platform fee.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- The calculator                                               -->
    <!-- ============================================================ -->
    <section id="calculator" class="relative scroll-mt-24 bg-white pb-16 pt-8 dark:bg-[#0a0a0f] lg:pb-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <x-marketing.fee-calculator
                :platforms="\App\Utils\TicketFees::CALCULATOR_PLATFORMS"
                :tickets="\App\Utils\TicketFees::EXAMPLE_TICKETS"
                :price="\App\Utils\TicketFees::EXAMPLE_PRICE"
                id="tfc"
                data-reveal="panel" />
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- What a ticket fee is made of                                 -->
    <!-- ============================================================ -->
    <section id="anatomy" class="scroll-mt-24 bg-gray-50 py-16 dark:bg-[#0f0f14] lg:py-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    What a ticket fee <span class="text-gradient-fees">is made of</span>
                </h2>
                <p class="mt-4 text-lg text-gray-500 dark:text-gray-400" data-reveal style="--reveal-delay: 0.08s;">
                    Every platform's fee is built from the same few parts, mixed differently. Once you can see the parts, you can read any pricing page and work out what one ticket will cost you before you sign up.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2" data-reveal-group="70">
                @foreach ($anatomy as [$partTitle, $partBody])
                    <div class="flex flex-col rounded-2xl border border-gray-200 bg-white p-6 dark:border-white/10 dark:bg-white/[0.04]" data-reveal="panel">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $partTitle }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $partBody }}</p>
                    </div>
                @endforeach
            </div>

            {{-- Line by line on one ticket, at $lineExample: see where it is set for why. --}}
            <div class="mt-8 overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]" data-reveal="panel">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[40rem] text-sm">
                        <caption class="px-6 pt-5 text-start text-base font-bold text-gray-900 dark:text-white">One {{ $usd($lineExample) }} ticket, line by line</caption>
                        <thead>
                            <tr class="border-b border-gray-200 text-xs uppercase tracking-wider text-gray-500 dark:border-white/10 dark:text-gray-400">
                                <th scope="col" class="px-6 py-3 text-start font-semibold">Platform</th>
                                <th scope="col" class="px-4 py-3 text-end font-semibold">Share of price</th>
                                <th scope="col" class="px-4 py-3 text-end font-semibold">Fixed fee</th>
                                <th scope="col" class="px-4 py-3 text-end font-semibold">Processing</th>
                                <th scope="col" class="px-4 py-3 text-end font-semibold">Total fees</th>
                                <th scope="col" class="px-6 py-3 text-end font-semibold">You keep</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @foreach (\App\Utils\TicketFees::CALCULATOR_PLATFORMS as $lineKey)
                                @php
                                    $lineParts = $parts($lineKey, $lineExample);
                                    $lineTotal = $perTicket($lineKey, $lineExample);
                                @endphp
                                <tr>
                                    <th scope="row" class="px-6 py-3.5 text-start font-normal">
                                        <span class="block font-semibold text-gray-900 dark:text-white">{{ $feeRates[$lineKey]['name'] }}{{ $lineKey === 'luma' ? ', free plan' : '' }}</span>
                                        <span class="block text-xs text-gray-500 dark:text-gray-400">Processing by {{ $lineParts['processor'] === 'Stripe' ? 'Stripe, on your account' : 'the platform itself' }}</span>
                                    </th>
                                    <td class="px-4 py-3.5 text-end tabular-nums text-gray-700 dark:text-gray-300">{{ $money($lineParts['share']) }}</td>
                                    <td class="px-4 py-3.5 text-end tabular-nums text-gray-700 dark:text-gray-300">{{ $money($lineParts['fixed']) }}</td>
                                    <td class="px-4 py-3.5 text-end tabular-nums text-gray-700 dark:text-gray-300">{{ $money($lineParts['processing']) }}</td>
                                    <td class="px-4 py-3.5 text-end tabular-nums font-semibold text-gray-900 dark:text-white">{{ $money($lineTotal) }}</td>
                                    <td class="px-6 py-3.5 text-end tabular-nums font-semibold {{ $lineKey === 'eventschedule' ? 'text-emerald-700 dark:text-emerald-400' : 'text-gray-900 dark:text-white' }}">{{ $money($lineExample - $lineTotal) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="border-t border-gray-100 px-6 py-4 text-xs leading-relaxed text-gray-500 dark:border-white/5 dark:text-gray-400">
                    Published US rates, one ticket per order, every fee absorbed. Ticket Tailor is shown at the middle of its range. Event Schedule's row is Stripe's fee alone; a priced ticket also needs the Pro plan, a monthly cost rather than a per-ticket one.
                </p>
            </div>

            {{-- The same model at four prices: fixed fees weigh on cheap tickets, shares on dear ones. --}}
            <div class="mt-8 overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]" data-reveal="panel">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[40rem] text-sm">
                        <caption class="px-6 pt-5 text-start text-base font-bold text-gray-900 dark:text-white">Fees on one ticket, at four prices</caption>
                        <thead>
                            <tr class="border-b border-gray-200 text-xs uppercase tracking-wider text-gray-500 dark:border-white/10 dark:text-gray-400">
                                <th scope="col" class="px-6 py-3 text-start font-semibold">Platform</th>
                                @foreach ($priceColumns as $columnPrice)
                                    <th scope="col" class="px-4 py-3 text-end font-semibold">{{ $usd($columnPrice) }} ticket</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @foreach (\App\Utils\TicketFees::CALCULATOR_PLATFORMS as $rowKey)
                                <tr>
                                    <th scope="row" class="px-6 py-3.5 text-start font-semibold text-gray-900 dark:text-white">{{ $feeRates[$rowKey]['name'] }}</th>
                                    @foreach ($priceColumns as $columnPrice)
                                        <td class="px-4 py-3.5 text-end tabular-nums text-gray-800 dark:text-gray-200">
                                            <span class="font-semibold">{{ $money($perTicket($rowKey, $columnPrice)) }}</span>
                                            <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $feeShare($rowKey, $columnPrice) }} of the price</span>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="border-t border-gray-100 px-6 py-4 text-xs leading-relaxed text-gray-500 dark:border-white/5 dark:text-gray-400">
                    Read down a column to compare platforms at your price, and along a row to see how one platform's take changes as the price rises. A platform with a big fixed fee looks expensive on a {{ $usd($priceColumns[0]) }} ticket and cheap on a {{ $usd(end($priceColumns)) }} one; a percentage does the opposite.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Absorb or pass on, and pricing to cover the fees             -->
    <!-- ============================================================ -->
    <section id="pricing-for-fees" class="scroll-mt-24 bg-white py-16 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Absorb the fee, pass it on, <span class="text-gradient-fees">or price it in</span>
                </h2>
                <p class="mt-4 text-lg text-gray-500 dark:text-gray-400" data-reveal style="--reveal-delay: 0.08s;">
                    The fee is paid either way. The only choice is where it shows: in your payout, on the buyer's receipt, or inside a ticket price you set a little higher.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3" data-reveal-group="80">
                <div class="flex flex-col rounded-2xl border border-gray-200 bg-gray-50 p-6 dark:border-white/10 dark:bg-white/[0.04]" data-reveal="panel">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Absorb it</h3>
                    <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">The buyer pays the price on the poster and you take the fees out of your payout. Simple to explain, and right when the ticket price is a round number people already expect. On a {{ $usd(\App\Utils\TicketFees::EXAMPLE_PRICE) }} Eventbrite ticket you would keep {{ $money(\App\Utils\TicketFees::EXAMPLE_PRICE - $exampleEventbrite) }}.</p>
                </div>
                <div class="flex flex-col rounded-2xl border border-gray-200 bg-gray-50 p-6 dark:border-white/10 dark:bg-white/[0.04]" data-reveal="panel">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Pass it on</h3>
                    <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">The platform adds its fee at checkout, so you keep close to the face value and the buyer pays more than the poster said. Where it is allowed, a fee that appears at the last step is noticed, and some buyers leave. Several places now require ticket prices to be shown with mandatory fees included from the start (in the US, the FTC's rule on fees covers live-event tickets), so check the rules where you sell.</p>
                </div>
                <div class="flex flex-col rounded-2xl border border-blue-300 bg-white p-6 ring-2 ring-blue-500/25 dark:border-blue-500/40 dark:bg-white/[0.06]" data-reveal="panel">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Price it in</h3>
                    <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">Work out the price that leaves you the amount you need after fees, and put that on the poster. The buyer sees one honest number, and you keep what you planned. This is the only way to cover the fee on Event Schedule, which adds nothing at checkout: the only fee there is your payment processor's, Stripe's {{ $feeRates['stripe']['label'] }} in the US.</p>
                </div>
            </div>

            <div class="mt-8 grid grid-cols-1 gap-4 lg:grid-cols-5">
                <div class="flex flex-col rounded-2xl border border-gray-200 bg-gray-50 p-6 dark:border-white/10 dark:bg-white/[0.04] lg:col-span-2" data-reveal="panel">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">The formula</h3>
                    <p class="mt-3 font-mono text-base font-semibold text-gray-900 dark:text-white">price = (keep + fixed) &divide; (1 &minus; share)</p>
                    <p class="mt-3 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                        <span class="font-semibold text-gray-900 dark:text-white">keep</span> is what you want from each ticket, <span class="font-semibold text-gray-900 dark:text-white">fixed</span> is every fixed amount per ticket added together, and <span class="font-semibold text-gray-900 dark:text-white">share</span> is every percentage added together, as a decimal.
                    </p>
                    <div class="mt-auto space-y-3 pt-5 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                        <p>
                            <span class="font-semibold text-gray-900 dark:text-white">With Stripe alone:</span>
                            ({{ $usd($exampleKeep) }} + {{ $money($stripeLine['b']) }}) &divide; (1 &minus; {{ rtrim(rtrim(number_format($stripeLine['a'], 4), '0'), '.') }}) = {{ number_format(($exampleKeep + $stripeLine['b']) / (1 - $stripeLine['a']), 3) }}, so price at {{ $money($grossUp('eventschedule', $exampleKeep)) }}.
                        </p>
                        <p>
                            <span class="font-semibold text-gray-900 dark:text-white">On Eventbrite:</span>
                            the service fee and processing make a share of {{ $pct($ebLine['a']) }} and a fixed {{ $money($ebLine['b']) }}, so ({{ $usd($exampleKeep) }} + {{ $money($ebLine['b']) }}) &divide; (1 &minus; {{ rtrim(rtrim(number_format($ebLine['a'], 4), '0'), '.') }}) = {{ number_format(($exampleKeep + $ebLine['b']) / (1 - $ebLine['a']), 3) }}, so price at {{ $money($grossUp('eventbrite', $exampleKeep)) }}.
                        </p>
                        <p>Always round up, never down. A cap, or a flat fee on very cheap tickets, bends the line; the table beside this works those in.</p>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04] lg:col-span-3" data-reveal="panel">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[28rem] text-sm">
                            <caption class="px-6 pt-5 text-start text-base font-bold text-gray-900 dark:text-white">The price to charge to keep a set amount</caption>
                            <thead>
                                <tr class="border-b border-gray-200 text-xs uppercase tracking-wider text-gray-500 dark:border-white/10 dark:text-gray-400">
                                    <th scope="col" class="px-6 py-3 text-start font-semibold">Platform</th>
                                    @foreach ($keepTargets as $keepTarget)
                                        <th scope="col" class="px-4 py-3 text-end font-semibold">To keep {{ $usd($keepTarget) }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                                @foreach (\App\Utils\TicketFees::CALCULATOR_PLATFORMS as $keepKey)
                                    <tr>
                                        <th scope="row" class="px-6 py-3 text-start font-semibold text-gray-900 dark:text-white">{{ $feeRates[$keepKey]['name'] }}</th>
                                        @foreach ($keepTargets as $keepTarget)
                                            <td class="px-4 py-3 text-end tabular-nums text-gray-800 dark:text-gray-200">{{ $money($grossUp($keepKey, $keepTarget)) }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="border-t border-gray-100 px-6 py-4 text-xs leading-relaxed text-gray-500 dark:border-white/5 dark:text-gray-400">
                        Worked with the same rates as the calculator, each figure rounded up to the cent. Luma is its free plan; Event Schedule is Stripe's fee alone.
                    </p>
                </div>
            </div>

            <div class="mx-auto mt-8 max-w-4xl rounded-2xl border border-gray-200 bg-gray-50 p-6 dark:border-white/10 dark:bg-white/[0.04]" data-reveal="panel">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Three mistakes that cost more than the fee</h3>
                <ul class="mt-3 space-y-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                    <li><span class="font-semibold text-gray-900 dark:text-white">Adding the percentage to the price.</span> A {{ $pct($stripeLine['a']) }} fee is not covered by raising the price {{ $pct($stripeLine['a']) }}: the fee is charged on the new, higher price too, which is why the formula divides instead of multiplying.</li>
                    <li><span class="font-semibold text-gray-900 dark:text-white">Comparing percentages and forgetting the fixed part.</span> On a {{ $usd($priceColumns[0]) }} ticket Eventbrite's {{ $usd($feeRates['eventbrite']['fixed']) }} is most of the fee; the percentage is the smaller half.</li>
                    <li><span class="font-semibold text-gray-900 dark:text-white">Leaving out processing.</span> A platform that quotes its own fee and then settles into your Stripe account still costs you Stripe's {{ $feeRates['stripe']['label'] }} on top. Every figure on this page includes it.</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- How each platform charges                                    -->
    <!-- ============================================================ -->
    <section id="models" class="scroll-mt-24 bg-gray-50 py-16 dark:bg-[#0f0f14] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    How each platform <span class="text-gradient-fees">charges</span>
                </h2>
                <p class="mt-4 text-lg text-gray-500 dark:text-gray-400" data-reveal style="--reveal-delay: 0.08s;">
                    The same total can hide very different rules: a cap here, a flat fee there, processing on top or folded in. This is what sits behind each figure.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group="60">
                @foreach ($models as $model)
                    <div class="flex flex-col rounded-2xl border p-6 {{ $model['key'] === 'eventschedule' ? 'border-blue-300 bg-white ring-2 ring-blue-500/25 dark:border-blue-500/40 dark:bg-white/[0.06]' : 'border-gray-200 bg-white dark:border-white/10 dark:bg-white/[0.04]' }}" data-reveal="panel">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $feeRates[$model['key']]['name'] }}</h3>
                        <p class="mt-1 text-sm font-semibold {{ $model['key'] === 'eventschedule' ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }}">{{ $model['rate'] }}</p>
                        <p class="mt-3 text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $model['how'] }}</p>
                        <p class="mt-3 text-sm text-gray-700 dark:text-gray-300">
                            On one {{ $usd(\App\Utils\TicketFees::EXAMPLE_PRICE) }} ticket: <span class="font-semibold tabular-nums text-gray-900 dark:text-white">{{ $money($perTicket($model['key'], \App\Utils\TicketFees::EXAMPLE_PRICE)) }}</span> in fees{{ $model['key'] === 'eventschedule' ? ', all of it Stripe\'s' : '' }}, and you keep <span class="font-semibold tabular-nums text-gray-900 dark:text-white">{{ $money(\App\Utils\TicketFees::EXAMPLE_PRICE - $perTicket($model['key'], \App\Utils\TicketFees::EXAMPLE_PRICE)) }}</span>.
                        </p>
                        {{-- The source and the link sit together at the foot of the card, on a wrapper: the
                             target-size rule in marketing.css gives a standalone inline-flex link its own
                             block margin and padding, which outrank mt-auto and pt-4 on the link itself. --}}
                        <div class="mt-auto pt-4">
                            @if ($model['source'])
                                <p class="text-xs text-gray-500 dark:text-gray-400">Source: {{ $model['source'] }}</p>
                            @endif
                            @if ($model['route'])
                                <p @class(['mt-3' => $model['source']])>
                                    <a href="{{ route($model['route']) }}" class="group inline-flex items-center gap-1 text-sm font-semibold text-blue-700 transition-all hover:gap-2 dark:text-blue-400">
                                        {{ $model['link'] }}
                                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                    </a>
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- What the totals include                                      -->
    <!-- ============================================================ -->
    <section id="method" class="scroll-mt-24 bg-white py-16 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    What the totals <span class="text-gradient-fees">include</span>
                </h2>
                <p class="mt-4 text-lg text-gray-500 dark:text-gray-400" data-reveal style="--reveal-delay: 0.08s;">
                    Four assumptions, the same for every platform, so the comparison is like for like.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2" data-reveal-group="80">
                @foreach ($method as [$methodTitle, $methodBody])
                    <div class="flex flex-col rounded-2xl border border-gray-200 bg-gray-50 p-6 dark:border-white/10 dark:bg-white/[0.04]" data-reveal="panel">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $methodTitle }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $methodBody }}</p>
                    </div>
                @endforeach
            </div>

            <p class="mx-auto mt-10 max-w-3xl text-center text-gray-600 dark:text-gray-400" data-reveal>
                For more than fees, the <x-link href="{{ marketing_url('/compare') }}">feature-by-feature comparison</x-link> sets the platforms side by side, and <x-link href="{{ marketing_url('/features/ticketing') }}">selling tickets on Event Schedule</x-link> explains our side in full.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- FAQ                                                          -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />
    <section id="faq" class="scroll-mt-24 bg-gray-50 py-16 dark:bg-[#0f0f14] lg:py-24">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>
                    Ticket fee questions
                </h2>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faq)
                    <details name="faq" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition-colors hover:border-blue-300 dark:border-white/10 dark:bg-white/[0.04] dark:hover:border-blue-500/40">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $faq['q'] }}</h3>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-400 transition-transform duration-300 group-open/faq:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="faq-answer px-6 pb-6 text-gray-600 dark:text-gray-400">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Finale                                                       -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-blue-500/20 sm:px-12 lg:py-24" data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        No platform fee <span class="text-gradient-fees">on any plan</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                        Only your payment processor's fee comes off a paid ticket. Free registration on every plan, and paid tickets on Pro.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-blue-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
                            <span class="relative z-10 flex items-center gap-2">
                                Get Started Free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>
                    <p class="mt-6 text-sm text-gray-400">No credit card required</p>
                </div>
            </div>
        </div>
    </section>

    <x-marketing.related-pages />

    {{-- Load-bearing, not decoration: marketing.css hides every [data-reveal] element behind
         html.es-anim, so a page that sets that class and never loads the reveal observer renders
         completely blank below the nav. --}}
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
