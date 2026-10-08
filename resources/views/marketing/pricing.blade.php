<x-marketing-layout :hp="true">
    <x-slot name="title">{{ __('marketing.pricing_title') }}</x-slot>
    <x-slot name="description">{{ __('marketing.pricing_description') }}</x-slot>
    <x-slot name="breadcrumbTitle">Pricing</x-slot>

    @php
        // $proMonthly / $proYearly / $entMonthly / $entYearly come from the marketing.* view
        // composer, which reads PlatformPricing. Re-deriving them here would shadow the shared
        // values and quietly ignore whatever a super-admin set at /admin/settings.
        // Raw, not number_format'd: plan_price() formats to the platform currency's own
        // precision, which is zero decimals for JPY and friends.
        $proPerMonth = $proYearly / 12;
        $entPerMonth = $entYearly / 12;
        $saveMax = max(($proMonthly * 12) - $proYearly, ($entMonthly * 12) - $entYearly);

        // The compare table under the cards: the rate card's rows, shared with /faq.
        $planRows = \App\Utils\PlanRateCard::rows();

        // Curated feature lists (CLAUDE.md:43). Wording and order are fixed - style
        // them, never edit them.
        $freeFeatures = [
            'Unlimited events and schedules',
            'Mobile-optimized, professional design',
            'Custom schedule URLs',
            'Venue location maps',
            'Google Calendar sync',
            'CalDAV sync',
            'Fan videos & comments on events',
            'Embed calendar on website',
            'Recurring events',
            'Free event registration',
            'Scan tickets at the door',
            'Appointment booking (1 free type)',
            'Built-in analytics',
            'Generate event graphics',
            'Sub-schedules',
            '10 ' . __('messages.newsletters_per_month'),
        ];
        $proFeatures = [
            'Everything in Free',
            'Remove Event Schedule branding',
            'Paid ticket sales & check-in dashboard',
            'Every payment method (Stripe, PayPal, Payfast, Invoice Ninja, link, cash)',
            'Refunds, full or partial',
            'Passes, subscriptions & individual tickets',
            'Unlimited appointment types, paid bookings & advanced scheduling',
            __('messages.feature_boost'),
            'Custom fields',
            'Custom CSS styling',
            'REST API & webhooks',
            'Event polls',
            'Post-event feedback',
            'Embed ticket widget',
            'Promo/discount codes',
            'Sales CSV export',
            '100 ' . __('messages.newsletters_per_month'),
        ];
        $enterpriseFeatures = [
            'Everything in Pro',
            'Allocated (reserved) seating',
            'Multiple team members per account',
            'Private & password-protected events',
            'WhatsApp event creation',
            'Custom domains',
            'Email scheduling',
            'Agenda scanning',
            'AI-powered content generation',
            'Availability management',
            'Priority support',
            '1,000 ' . __('messages.newsletters_per_month'),
        ];

        // Fee calculator defaults, computed server-side so the section is correct and meaningful
        // with JavaScript disabled. Everything comes from App\Utils\TicketFees, like /compare and
        // /for-talent; the "typical platform" is Eventbrite at its published US rates, processing
        // fee included, and the script below recomputes from the same rates (data-rates).
        $feeRates = \App\Utils\TicketFees::rates();
        $calcTickets = \App\Utils\TicketFees::EXAMPLE_TICKETS;
        $calcPrice = \App\Utils\TicketFees::EXAMPLE_PRICE;
        $calcEs = \App\Utils\TicketFees::cost('eventschedule', $calcTickets, $calcPrice, $feeRates);
        $calcEb = \App\Utils\TicketFees::cost('eventbrite', $calcTickets, $calcPrice, $feeRates);
        $calcSave = $calcEb - $calcEs;
        $calcEsBar = $calcEb > 0 ? round(($calcEs / $calcEb) * 100) : 0;

        $faqs = [
            ['q' => 'Is there really a free plan?', 'a' => 'Yes! The free plan includes unlimited events, all core features, one free appointment type with a public booking page, and unlimited free registration with QR check-in at the door. You only need to upgrade if you want to charge for a ticket or a booking, offer more appointment types, remove branding, or access advanced features.'],
            ['q' => 'How does the free trial work?', 'a' => 'When you sign up for Pro or Enterprise, you get a 7-day free trial, once per schedule. Enter your card to start, and you won\'t be charged until the trial ends; cancel before then and nothing is charged. After that, Pro is ' . plan_price($proMonthly) . '/month or ' . plan_price($proYearly) . '/year, and Enterprise is ' . plan_price($entMonthly) . '/month or ' . plan_price($entYearly) . '/year. You can cancel anytime.'],
            ['q' => 'What is the difference between Pro and Enterprise?', 'a' => 'The free plan already takes unlimited free registrations, scans tickets at the door and carries one free appointment type. Pro is what puts a price on a ticket, and brings the rest of the ticketing suite with it: every payment method (Stripe, PayPal, Payfast, Invoice Ninja, a payment link or cash), refunds in full or in part, the live check-in dashboard, passes and subscriptions, individual tickets, promo/discount codes, add-ons, gift cards, installment payments through Stripe, the ticket waitlist and sales CSV export. It also lifts the appointment type limit, puts a price on a booking, unlocks advanced scheduling (date overrides, buffers, minimum notice and approvals) and adds white-label branding, event boosting with ads, custom fields, custom CSS styling, REST API & webhooks, and 100 newsletter emails per month, each recipient counting as one. Enterprise adds allocated (reserved) seating for venue schedules, custom domains, private and password-protected events, up to five team members, WhatsApp event creation, email scheduling, agenda scanning, availability management, 1,000 newsletter emails per month, and priority support.'],
            // The free/paid line is Event::canSellPaidTickets(): the creator schedule's plan decides
            // and zero-price rows are always sellable via canOfferTickets(). This question gets
            // asked before anyone signs up, so it belongs here.
            ['q' => 'What can the free plan do with tickets?', 'a' => 'A great deal, as long as nothing has a price on it. Free registration is unlimited on every plan: publish a ticket tier at no charge and guests reserve a place, get a QR code by email and are scanned in at the door. You see who is coming, how many are left and who actually turned up. Putting a price on a ticket is the Pro line, and the same line applies to an appointment booking. An event that only needs a headcount and a guest list never has to pay us at all, and when you do start charging there is still no platform fee.'],
            // Downgrade: Cashier's cancel() keeps the paid period (SubscriptionController::cancel()),
            // paid rows then fail Event::canSellPaidTickets() while free rows keep rendering, and below
            // Enterprise EventRepo::saveEvent() turns Internal and Unlisted into a Draft on the next save.
            ['q' => 'What happens if I cancel or downgrade?', 'a' => 'You keep the plan until the end of the period you have already paid for, and nothing you created is deleted. Every ticket you have already sold stays valid and still scans at the door, and the sales records stay where they are. What stops is new sales of tickets that carry a price; free registration keeps running, so an event that mixes a free tier with paid ones keeps its buy button for the free one. A seating plan already attached to an event stays attached. Every appointment type you made is kept, and so is every scheduling rule on it: the oldest free one keeps taking bookings, priced ones pause until you upgrade, and your buffers and notice periods stay exactly as you set them. Internal and Unlisted events stay hidden and become Drafts the next time they are saved, rather than going public by accident, and your data stays exportable from Backup and Restore at any time.'],
            ['q' => 'Can I cancel anytime?', 'a' => 'Absolutely. You can cancel your subscription at any time and you\'ll keep access until the end of your billing period.'],
            ['q' => 'Do you take a cut of ticket sales?', 'a' => 'No. There is no platform fee on any plan, including free. Buyers pay your own Stripe or PayPal account directly, so the only deduction is the processor\'s own fee (Stripe\'s is 2.9% + $0.30 per transaction in the US, and the processor sets it, not us). We never hold your money, so there is nothing for us to take a cut of.'],
            // Taking money for a ticket is Event::canSellPaidTickets(), which is Pro - so the
            // gateways and refunds are listed on Pro, where they are reachable. The payments layer
            // itself carries no isPro() (docs/FEATURES.md), which is why a schedule that downgrades
            // can still refund what it already took. The interest list is EventInterestController,
            // ungated, bounded by canSendAudienceMail() and not newsletterLimit().
            ['q' => 'Which payment methods can I use?', 'a' => 'All of them, once you are on Pro and charging for a ticket. Stripe, PayPal, Payfast (South African rand only) and Invoice Ninja connect to your own account under Settings, Payment Methods, and you can also send buyers to a payment link of your own or take cash at the door. You pick the method per event. A free schedule never puts a price on a ticket, so it has nothing for a gateway to settle; what it does have is unlimited free registration. Whichever method you use, we add no platform fee on top.'],
            ['q' => 'Can I refund a ticket?', 'a' => 'Yes, and we charge nothing for it. Refunds sit with paid ticketing on Pro, because that is where the money is taken in the first place. From the Sales page, a Stripe or PayPal sale can be refunded in full or in part (an installment plan in full only), and the money goes back through the provider before the sale is marked refunded. A partial refund leaves the tickets valid; a full refund puts the tickets, and any seats, back on sale. A sale taken any other way, such as cash, a payment link, Invoice Ninja or Payfast, is marked as refunded instead, which records it while you return the money yourself. If you cancel Pro, you can still refund the sales you already took.'],
            ['q' => 'Does the “tell me when tickets go on sale” list cost anything?', 'a' => 'No, it is free on every plan. Switch on the “Notify me” card and a visitor leaves just an email address on the event page and gets one email when tickets go on sale, one if the event is cancelled, a reminder shortly before it starts, and any notice you choose to send if the date or venue changes. It does not use your newsletter allowance, and the event\'s Tickets panel shows you how many people are waiting.'],
        ];
    @endphp

    <x-slot name="structuredData">
    {{-- The plans themselves - Free, Pro and Enterprise, priced from PlatformPricing - are the
         offers on the layout's one product node, SeoUtils::softwareApplication(). --}}
    <x-seo.webpage
        name="Event Schedule pricing"
        :description="__('marketing.pricing_description')" />
    </x-slot>

    <style {!! nonce_attr() !!}>
        .text-gradient-pricing {
            background: linear-gradient(135deg, #059669 0%, #0284c7 50%, #2563eb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-pricing {
            background: linear-gradient(135deg, #34d399 0%, #38bdf8 50%, #60a5fa 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .es-finale-panel .text-gradient-pricing {
            background: linear-gradient(135deg, #34d399 0%, #38bdf8 50%, #60a5fa 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Billing toggle: a single .is-annual class on #pricing-plans drives every
           state (no framework). Segmented control - the active half is a raised pill. */
        .bt-seg { min-height: 2.75rem; color: var(--hp-ink-2); transition: background-color .2s, color .2s, box-shadow .2s; }
        .bt-seg-month { background: var(--hp-ink); color: var(--hp-bg); box-shadow: 0 10px 24px -12px rgba(10, 16, 32, 0.6); }
        #pricing-plans.is-annual .bt-seg-month { background: transparent; color: var(--hp-ink-2); box-shadow: none; }
        #pricing-plans.is-annual .bt-seg-year { background: var(--hp-ink); color: var(--hp-bg); box-shadow: 0 10px 24px -12px rgba(10, 16, 32, 0.6); }

        /* The plan cards keep their own shape and their curated lists; here they take the
           page's surfaces. Pro's band and badge are the flat blue of the buttons, and the
           recommended card stands in the page's light. */
        #pricing-plans .from-blue-600.to-sky-500 { background-image: linear-gradient(100deg, #2b5fe3, #2f6fe9); }
        #pricing-plans .es-bento { border-color: var(--hp-line); background: var(--hp-bg-2); box-shadow: var(--hp-card-shadow); }
        #pricing-plans .es-bento.ring-2 { box-shadow: 0 0 0 2px rgba(47, 102, 234, 0.75), 0 30px 70px -28px rgba(47, 102, 234, 0.55), 0 0 80px -20px rgba(34, 211, 238, 0.45); }
        #pricing-plans .es-ring-glow { display: none; }

        /* Price swapping */
        .bt-price-month, .bt-price-year { transition: opacity .2s ease; }
        .bt-price-year { display: none; }
        #pricing-plans.is-annual .bt-price-month { display: none; }
        #pricing-plans.is-annual .bt-price-year { display: flex; }

        /* Note swapping. Both notes stay stacked in one grid cell and are hidden with
           visibility, not display, so the row is always as tall as the LONGER note and
           the cards cannot change height when the toggle flips. */
        .bt-note-month, .bt-note-year { grid-area: 1 / 1; transition: opacity .2s ease; }
        .bt-note-year { visibility: hidden; opacity: 0; }
        #pricing-plans.is-annual .bt-note-month { visibility: hidden; opacity: 0; }
        #pricing-plans.is-annual .bt-note-year { visibility: visible; opacity: 1; }
        .bt-period-year { display: none; }
        #pricing-plans.is-annual .bt-period-month { display: none; }
        #pricing-plans.is-annual .bt-period-year { display: inline; }

        /* Mobile feature disclosure. Markup ships OPEN, so no-JS and crawlers always
           see every item; the script closes Free/Enterprise below md only. */
        .plan-disc > summary { list-style: none; }
        .plan-disc > summary::-webkit-details-marker { display: none; }
        .plan-disc > summary::marker { content: ''; }
        .plan-disc[open] > summary .plan-disc-chev { transform: rotate(180deg); }

        /* Compare plans: the same rows as the rate card on /faq (App\Utils\PlanRateCard), in
           this page's own dress. Plain CSS, so it needs no class the stylesheet lacks. */
        .pc-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.95rem; }
        .pc-table th,
        .pc-table td { padding: 0.8rem 1rem; vertical-align: top; border-top: 1px solid var(--hp-line); }
        .pc-table thead th {
            border-top: 0;
            font-family: var(--hp-mono);
            font-size: 0.72rem;
            font-weight: 700;
            font-variation-settings: normal;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--hp-ink-3);
            white-space: nowrap;
        }
        .pc-table tbody th { font-weight: 700; font-variation-settings: 'wght' 640; color: var(--hp-ink); }
        .pc-table thead th:first-child,
        .pc-table tbody th { width: 40%; }
        /* A denial or a ceiling stays in neutral ink, so no limit reads as a feature. */
        .pc-yes { font-weight: 700; font-variation-settings: 'wght' 640; color: #047857; }
        .dark .pc-yes { color: #6ee7b7; }
        .pc-no { color: var(--hp-ink-3); }
        /* The Pro column carries the same quiet lift its card does. */
        .pc-table th:nth-child(3),
        .pc-table td:nth-child(3) { background-color: rgba(47, 102, 234, 0.05); }
        .dark .pc-table th:nth-child(3),
        .dark .pc-table td:nth-child(3) { background-color: rgba(125, 165, 255, 0.08); }
        /* On a phone each question is its own block, the three plans side by side under their
           names. The names come from data-label, and the head stays for a screen reader (the
           table roles are said outright in the markup, because a table laid out as blocks
           loses them). */
        @media (max-width: 639.98px) {
            .pc-table { display: block; }
            .pc-table thead {
                position: absolute;
                width: 1px;
                height: 1px;
                overflow: hidden;
                clip: rect(0, 0, 0, 0);
                white-space: nowrap;
            }
            .pc-table tbody { display: block; }
            .pc-table tbody tr {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 0.45rem 0.75rem;
                padding: 0.85rem 0;
                border-top: 1px solid var(--hp-line);
            }
            .pc-table tbody tr:first-child { border-top: 0; padding-top: 0; }
            .pc-table tbody th,
            .pc-table tbody td { display: block; width: auto; padding: 0; border-top: 0; background-color: transparent; }
            .pc-table tbody td:nth-child(3),
            .dark .pc-table tbody td:nth-child(3) { background-color: transparent; }
            .pc-table tbody th { grid-column: 1 / -1; }
            .pc-table tbody td { overflow-wrap: anywhere; font-size: 0.9rem; }
            .pc-table tbody td::before {
                content: attr(data-label);
                display: block;
                margin-bottom: 0.15rem;
                font-size: 0.65rem;
                font-weight: 700;
                letter-spacing: 0.1em;
                text-transform: uppercase;
                color: var(--hp-ink-3);
            }
        }
    </style>

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present,
         so no-JS visitors, crawlers, and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <!-- ============================================================ -->
    <!-- Hero (text only - on a pricing page the cards are the CTA)  -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero hp-hero is-short">
        <div class="hp-hero-sky" aria-hidden="true"></div>

        <div class="hp-hero-copy">
            <h1 class="hp-h1">
                <x-marketing.hero-eyebrow class="es-fade-up es-d-1 hp-eyebrow">
                    <svg aria-hidden="true" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Event Schedule pricing, no hidden fees
                </x-marketing.hero-eyebrow>
                <span class="es-mask"><span class="es-mask-line">Pricing that never</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="hp-ink-grad">takes a cut</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 hp-sub">
                Start free and upgrade when you need more. No surprises, and never a cut of your ticket sales.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Plans                                                       -->
    <!-- ============================================================ -->
    <section id="pricing-plans" class="scroll-mt-24 bg-gray-50 pb-16 pt-8 dark:bg-[#0f0f14] lg:pb-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">

            <!-- Billing toggle (vanilla JS: toggles .is-annual on #pricing-plans) -->
            <div class="mb-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <div class="inline-flex items-center rounded-2xl border border-gray-200 bg-gray-100 p-1 dark:border-white/10 dark:bg-white/[0.06]">
                    <button id="bt-monthly" type="button" aria-pressed="true" class="bt-seg bt-seg-month rounded-xl px-5 py-2 text-sm font-semibold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4E81FA]">Monthly</button>
                    <button id="bt-annual" type="button" aria-pressed="false" class="bt-seg bt-seg-year rounded-xl px-5 py-2 text-sm font-semibold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4E81FA]">Annual</button>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">
                    <svg aria-hidden="true" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    Save up to {{ plan_price($saveMax) }} a year
                </span>
            </div>

            {{-- Vertical reveal only: the horizontal variants translate 44px sideways,
                 which overflows a full-width card at 390px. --}}
            <div class="grid grid-cols-1 gap-8 md:grid-cols-3 md:grid-rows-[auto_1fr_auto] md:gap-y-0" data-reveal-group="90">

                <!-- Free -->
                <div class="es-bento es-tilt-inner relative flex flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-6 shadow-sm transition-shadow hover:shadow-xl dark:border-white/10 dark:bg-white/[0.04] md:row-span-3 md:grid md:grid-rows-[subgrid] lg:p-8" data-tilt="2" data-reveal>
                    <div class="mb-8">
                        <!-- Desktop: banner-height container matching trial banner structure -->
                        <div class="-mx-6 -mt-6 mb-8 hidden px-4 py-3 text-center md:block lg:-mx-8 lg:-mt-8">
                            <div class="text-lg font-bold text-gray-600 dark:text-gray-300">Forever Free</div>
                            <div class="text-sm">&nbsp;</div>
                        </div>
                        <!-- Mobile: pill badge -->
                        <div class="mb-6 inline-flex items-center gap-2 self-start rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-600 dark:bg-white/10 dark:text-gray-300 md:hidden">
                            Forever Free
                        </div>

                        <div>
                            <div class="mb-2 flex items-baseline gap-2">
                                <span class="text-6xl font-black tracking-tight text-gray-900 tabular-nums dark:text-white">{{ plan_price(0) }}</span>
                                <span class="text-gray-500 dark:text-gray-400"><span class="bt-period-month">/month</span><span class="bt-period-year">/year</span></span>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400">Perfect for getting started</p>
                        </div>
                    </div>

                    <details class="plan-disc mb-10" open>
                        <summary class="mb-4 flex cursor-pointer items-center justify-between rounded-xl bg-gray-50 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:bg-white/5 dark:text-gray-200 md:hidden">
                            What's included
                            <svg aria-hidden="true" class="plan-disc-chev h-4 w-4 text-gray-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <ul class="space-y-4">
                            @foreach ($freeFeatures as $feature)
                                <x-marketing.plan-feature accent="emerald">{{ $feature }}</x-marketing.plan-feature>
                            @endforeach
                        </ul>
                    </details>

                    <div class="mt-auto">
                        <a href="{{ app_url('/sign_up') }}" class="block w-full rounded-2xl border-2 border-emerald-300 bg-white px-6 py-4 text-center font-semibold text-emerald-700 transition-all hover:bg-emerald-50 dark:border-emerald-500/40 dark:bg-white/10 dark:text-emerald-300 dark:hover:bg-white/20">
                            Start for free
                        </a>
                        <p class="mt-3 text-center text-xs text-gray-500 dark:text-gray-400">No card. No expiry. Nothing to cancel.</p>
                    </div>
                    <div class="es-glare rounded-3xl"></div>
                </div>

                <!-- Pro (the recommendation) -->
                <div class="es-bento es-tilt-inner relative flex flex-col rounded-3xl bg-white p-6 shadow-lg ring-2 ring-blue-500/60 transition-shadow hover:shadow-2xl dark:bg-white/[0.04] dark:ring-blue-400/50 md:row-span-3 md:grid md:grid-rows-[subgrid] lg:-top-2 lg:p-8" data-tilt="2.5" data-reveal>
                    <span class="absolute -top-3 left-1/2 z-10 -translate-x-1/2 whitespace-nowrap rounded-full bg-gradient-to-r from-blue-600 to-sky-500 px-4 py-1 text-xs font-bold uppercase tracking-wider text-white shadow-lg shadow-blue-500/30">Most popular</span>

                    <div class="mb-8">
                        <div class="-mx-6 -mt-6 mb-8 rounded-t-3xl bg-gradient-to-r from-blue-600 to-sky-500 px-4 py-3 text-center text-white lg:-mx-8 lg:-mt-8">
                            <div class="text-lg font-bold">7-Day Free Trial</div>
                            <div class="text-sm text-blue-50">Try all Pro features risk-free</div>
                        </div>

                        <div>
                            <div class="relative mb-2 h-[68px]">
                                <div class="bt-price-year absolute inset-0 items-baseline gap-2">
                                    <span class="text-6xl font-black tracking-tight text-gray-900 tabular-nums dark:text-white">{{ plan_price($proYearly) }}</span>
                                    <span class="text-gray-500 dark:text-gray-400">/year</span>
                                </div>
                                <div class="bt-price-month absolute inset-0 flex items-baseline gap-2">
                                    <span class="text-6xl font-black tracking-tight text-gray-900 tabular-nums dark:text-white">{{ plan_price($proMonthly) }}</span>
                                    <span class="text-gray-500 dark:text-gray-400">/month</span>
                                </div>
                            </div>
                            <div class="grid">
                                <p class="bt-note-year text-gray-500 dark:text-gray-400">Just {{ plan_price($proPerMonth) }}/month, billed annually after your free trial</p>
                                <p class="bt-note-month text-gray-500 dark:text-gray-400">Billed monthly after your free trial</p>
                            </div>
                        </div>
                    </div>

                    <ul class="mb-10 space-y-4">
                        @foreach ($proFeatures as $feature)
                            <x-marketing.plan-feature accent="blue">{{ $feature }}</x-marketing.plan-feature>
                        @endforeach
                    </ul>

                    <div class="mt-auto">
                        <a href="{{ app_url('/sign_up') }}" class="block w-full rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-6 py-4 text-center font-semibold text-white shadow-lg shadow-blue-500/25 transition-all hover:from-blue-500 hover:to-sky-500 hover:shadow-xl">
                            Start free trial
                        </a>
                        <p class="mt-3 text-center text-xs text-gray-500 dark:text-gray-400">7 days free, then {{ plan_price($proMonthly) }} a month. Cancel any time and the schedule stays live.</p>
                    </div>
                    <div class="es-glare rounded-3xl"></div>
                    <div class="es-ring-glow"></div>
                </div>

                <!-- Enterprise -->
                <div class="es-bento es-tilt-inner relative flex flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-6 shadow-sm transition-shadow hover:shadow-xl dark:border-white/10 dark:bg-white/[0.04] md:row-span-3 md:grid md:grid-rows-[subgrid] lg:p-8" data-tilt="2" data-reveal>
                    <div class="mb-8">
                        <div class="-mx-6 -mt-6 mb-8 rounded-t-3xl bg-gradient-to-r from-amber-700 to-amber-800 px-4 py-3 text-center text-white lg:-mx-8 lg:-mt-8">
                            <div class="text-lg font-bold">7-Day Free Trial</div>
                            <div class="text-sm text-amber-100">Try all Enterprise features risk-free</div>
                        </div>

                        <div>
                            <div class="relative mb-2 h-[68px]">
                                <div class="bt-price-year absolute inset-0 items-baseline gap-2">
                                    <span class="text-6xl font-black tracking-tight text-gray-900 tabular-nums dark:text-white">{{ plan_price($entYearly) }}</span>
                                    <span class="text-gray-500 dark:text-gray-400">/year</span>
                                </div>
                                <div class="bt-price-month absolute inset-0 flex items-baseline gap-2">
                                    <span class="text-6xl font-black tracking-tight text-gray-900 tabular-nums dark:text-white">{{ plan_price($entMonthly) }}</span>
                                    <span class="text-gray-500 dark:text-gray-400">/month</span>
                                </div>
                            </div>
                            <div class="grid">
                                <p class="bt-note-year text-gray-500 dark:text-gray-400">Just {{ plan_price($entPerMonth) }}/month, billed annually after your free trial</p>
                                <p class="bt-note-month text-gray-500 dark:text-gray-400">Billed monthly after your free trial</p>
                            </div>
                        </div>
                    </div>

                    <details class="plan-disc mb-10" open>
                        <summary class="mb-4 flex cursor-pointer items-center justify-between rounded-xl bg-gray-50 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:bg-white/5 dark:text-gray-200 md:hidden">
                            What's included
                            <svg aria-hidden="true" class="plan-disc-chev h-4 w-4 text-gray-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <ul class="space-y-4">
                            @foreach ($enterpriseFeatures as $feature)
                                <x-marketing.plan-feature accent="amber">{{ $feature }}</x-marketing.plan-feature>
                            @endforeach
                        </ul>
                    </details>

                    <div class="mt-auto">
                        <a href="{{ app_url('/sign_up') }}" class="block w-full rounded-2xl bg-gradient-to-r from-amber-700 to-amber-800 px-6 py-4 text-center font-semibold text-white shadow-lg shadow-amber-700/25 transition-all hover:from-amber-600 hover:to-amber-700 hover:shadow-xl">
                            Start free trial
                        </a>
                        <p class="mt-3 text-center text-xs text-gray-500 dark:text-gray-400">7 days free. Or selfhost, where every line above is included at no cost.</p>
                    </div>
                    <div class="es-glare rounded-3xl"></div>
                </div>

            </div>

            {{-- Compare plans. The three cards say what each plan adds; this says, row by row,
                 what each plan has, including the rows that say no. The rows are the rate card's
                 (App\Utils\PlanRateCard), so this page and /faq cannot disagree. It ships open,
                 so a reader without JavaScript and a crawler get every row; the script below
                 closes it on a phone, where the summary is the way in. --}}
            <details id="compare" class="plan-disc mt-10 scroll-mt-24" open>
                <summary class="flex cursor-pointer items-center justify-between rounded-2xl border border-gray-200 bg-white px-6 py-4 font-semibold text-gray-900 dark:border-white/10 dark:bg-white/[0.03] dark:text-white md:hidden">
                    Compare plans, row by row
                    <svg aria-hidden="true" class="plan-disc-chev h-4 w-4 text-gray-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                </summary>
                <div class="mt-4 rounded-3xl border border-gray-200 bg-white p-6 dark:border-white/10 dark:bg-white/[0.03] sm:p-8 md:mt-0" data-reveal>
                    <div class="mb-6 hidden md:block">
                        <h2 class="text-2xl font-black tracking-tight text-gray-900 dark:text-white">Compare plans, row by row</h2>
                        <p class="mt-2 text-gray-500 dark:text-gray-400">{{ ucfirst(\Illuminate\Support\Number::spell(count($planRows))) }} rows, including the ones that say no.</p>
                    </div>
                    <table class="pc-table" role="table">
                        <caption class="sr-only">What each Event Schedule plan includes, with monthly and yearly prices</caption>
                        <thead role="rowgroup">
                            <tr role="row">
                                <th scope="col" role="columnheader">What you get</th>
                                <th scope="col" role="columnheader">Free</th>
                                <th scope="col" role="columnheader">Pro</th>
                                <th scope="col" role="columnheader">Enterprise</th>
                            </tr>
                        </thead>
                        <tbody role="rowgroup">
                            @foreach ($planRows as [$rowLabel, $rowFree, $rowPro, $rowEnt])
                                <tr role="row">
                                    <th scope="row" role="rowheader">{{ $rowLabel }}</th>
                                    <td role="cell" data-label="Free" class="{{ \App\Utils\PlanRateCard::includes($rowFree) ? 'pc-yes' : 'pc-no' }}">{{ $rowFree }}</td>
                                    <td role="cell" data-label="Pro" class="{{ \App\Utils\PlanRateCard::includes($rowPro) ? 'pc-yes' : 'pc-no' }}">{{ $rowPro }}</td>
                                    <td role="cell" data-label="Enterprise" class="{{ \App\Utils\PlanRateCard::includes($rowEnt) ? 'pc-yes' : 'pc-no' }}">{{ $rowEnt }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <p class="mt-5 text-sm text-gray-500 dark:text-gray-400">A selfhosted install resolves to the Enterprise feature set at no cost.</p>
                </div>
            </details>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Fees: do the arithmetic in public                           -->
    <!-- ============================================================ -->
    <section id="fees" class="scroll-mt-24 bg-white py-16 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="hp-head is-center" style="margin-bottom: clamp(2rem, 4vw, 3rem);">
                <span class="hp-kicker" data-reveal>What it costs</span>
                <h2 class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">
                    Zero platform fees. <span class="hp-ink-grad">Here's the math.</span>
                </h2>
                <p class="hp-lead" data-reveal style="--reveal-delay: 0.14s;">
                    Most ticketing platforms take a cut of every ticket. We take none. Move the numbers and see what that means for your event.
                </p>
            </div>

            <div class="rounded-3xl border border-gray-200 bg-gray-50 p-6 shadow-sm dark:border-white/10 dark:bg-white/[0.04] sm:p-10"
                 data-reveal="panel" data-fee-tickets="{{ $calcTickets }}" data-fee-price="{{ $calcPrice }}"
                 data-rates="{{ json_encode(\App\Utils\TicketFees::forScript(['eventschedule', 'eventbrite'], $feeRates)) }}">

                <div class="mb-10 flex flex-col items-center justify-center gap-6 sm:flex-row">
                    <div class="flex items-center gap-3">
                        <label for="pf-tickets" class="whitespace-nowrap text-sm font-medium text-gray-700 dark:text-gray-300">Tickets sold</label>
                        <input id="pf-tickets" type="number" value="{{ $calcTickets }}" min="1" max="100000" class="w-28 rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-transparent focus:ring-2 focus:ring-[var(--brand-blue)] dark:border-white/10 dark:bg-white/5 dark:text-white">
                    </div>
                    <div class="flex items-center gap-3">
                        <label for="pf-price" class="whitespace-nowrap text-sm font-medium text-gray-700 dark:text-gray-300">Ticket price</label>
                        <div class="relative">
                            <span class="absolute top-1/2 -translate-y-1/2 text-sm text-gray-500 dark:text-gray-400 ltr:left-3 rtl:right-3">$</span>
                            <input id="pf-price" type="number" value="{{ $calcPrice }}" min="1" max="10000" class="w-28 rounded-xl border border-gray-200 bg-white py-2.5 text-sm text-gray-900 focus:border-transparent focus:ring-2 focus:ring-[var(--brand-blue)] dark:border-white/10 dark:bg-white/5 dark:text-white ltr:pl-7 ltr:pr-3 rtl:pr-7 rtl:pl-3">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-white/10 dark:bg-white/5">
                        <div class="mb-1 text-sm font-semibold text-gray-900 dark:text-white">Typical ticketing platform</div>
                        <div class="mb-3 text-xs text-gray-500 dark:text-gray-400">{{ $feeRates['eventbrite']['label'] }}</div>
                        <div id="pf-eb" data-fee-total="eventbrite" class="mb-3 text-4xl font-black tracking-tight text-gray-900 tabular-nums dark:text-white">${{ number_format($calcEb, 2) }}</div>
                        <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                            <div class="h-full rounded-full bg-gray-400 dark:bg-gray-500" style="width: 100%"></div>
                        </div>
                    </div>

                    <div class="rounded-2xl border-2 border-emerald-300 bg-emerald-50/60 p-6 dark:border-emerald-500/40 dark:bg-emerald-500/10">
                        <div class="mb-1 text-sm font-semibold text-emerald-800 dark:text-emerald-300">Event Schedule Pro</div>
                        {{-- Deliberately a dollar sign, and NOT plan_price(). This label sits inside
                             the fee calculator, whose totals ($calcEs adds the Pro price to Stripe's
                             USD per-ticket fee, and $calcEb is Eventbrite's published US pricing) are
                             a USD unit. Converting just this line would put "R9/month" directly above
                             "$247.50". Same call as the <x-marketing.fee-calculator> component. --}}
                        <div class="mb-3 text-xs text-emerald-800 dark:text-emerald-400/80">${{ $feeRates['eventschedule']['monthly'] }}/month + Stripe, 0% platform fee</div>
                        <div id="pf-es" data-fee-total="eventschedule" class="mb-3 text-4xl font-black tracking-tight text-emerald-700 tabular-nums dark:text-emerald-300">${{ number_format($calcEs, 2) }}</div>
                        <div class="h-2 overflow-hidden rounded-full bg-emerald-100 dark:bg-emerald-500/20">
                            <div id="pf-bar" class="h-full rounded-full bg-emerald-500" style="width: {{ $calcEsBar }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 text-center">
                    <p class="text-lg text-gray-700 dark:text-gray-300">
                        You keep <span id="pf-save" class="text-2xl font-black text-emerald-600 tabular-nums dark:text-emerald-400">${{ number_format($calcSave, 2) }}</span> more on this one event.
                    </p>
                    <a href="{{ app_url('/sign_up') }}" class="group mt-6 inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-7 py-3.5 font-semibold text-white shadow-lg shadow-blue-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
                        Start your free trial
                        <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                    <p class="mt-5 text-xs text-gray-500 dark:text-gray-400">
                        Stripe processing ({{ $feeRates['stripe']['label'] }}) is included on the Event Schedule side. The typical platform is Eventbrite at its published US rates, with the 2.9% payment processing fee it charges on each order on top of the service fee. Payouts go straight to your own Stripe account; connect PayPal instead and the money lands in your PayPal account the same way, at PayPal's own rate.
                    </p>
                    <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                        Weighing up a particular platform? The <x-link href="{{ marketing_url('/ticket-fee-calculator') }}">ticket fee calculator</x-link> sets several side by side at their published rates.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Selfhost                                                    -->
    <!-- ============================================================ -->
    <section id="selfhost" class="hp-dark on-alt">
        <div class="hp-wrap is-narrow" style="text-align: center;" data-reveal>
            <svg aria-hidden="true" style="width: 3rem; height: 3rem; margin: 0 auto 1.5rem;" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
            </svg>
            <h2 class="hp-h2">
                Or run it yourself. <span class="hp-ink-grad">Free, forever.</span>
            </h2>
            <p class="hp-lead" style="max-width: 40rem; margin: 1.1rem auto 0;">
                Event Schedule is open source. Install it on your own server and every Enterprise feature is included at no cost, with your data staying entirely on your infrastructure.
            </p>
            <div class="hp-actions" style="justify-content: center;">
                <a href="{{ marketing_url('/selfhost') }}" class="hp-btn hp-btn-ghost">Selfhosting guide</a>
                <a href="{{ marketing_url('/open-source') }}" class="hp-more">
                    View the source
                    <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- FAQ                                                         -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />
    <x-marketing.hp-faq id="faq" :items="$faqs" class="hp-alt" lead="Everything you need to know about pricing.">Frequently asked <span class="hp-ink-grad">questions</span></x-marketing.hp-faq>

    <x-marketing.related-pages />

    <!-- ============================================================ -->
    <!-- Finale                                                      -->
    <!-- ============================================================ -->
    <x-marketing.hp-finale lead="Create your free schedule in seconds. Start your free trial today." placeholder="your-schedule" :foot="false">
        Start sharing your events <span class="hp-ink-grad">today</span>
        <x-slot name="after">
            <p class="hp-finale-foot">Know other organizers? <a href="{{ route('marketing.docs.referral_program') }}">Earn free months with our referral program</a>.</p>
        </x-slot>
    </x-marketing.hp-finale>



    <!-- Billing toggle + fee calculator + mobile plan disclosure (vanilla JS) -->
    @include('marketing.partials.ticket-fee-math')
    <script {!! nonce_attr() !!}>
        (function () {
            var wrap = document.getElementById('pricing-plans');
            var monthBtn = document.getElementById('bt-monthly');
            var yearBtn = document.getElementById('bt-annual');
            if (wrap && monthBtn && yearBtn) {
                var setAnnual = function (annual) {
                    wrap.classList.toggle('is-annual', annual);
                    monthBtn.setAttribute('aria-pressed', annual ? 'false' : 'true');
                    yearBtn.setAttribute('aria-pressed', annual ? 'true' : 'false');
                };
                monthBtn.addEventListener('click', function () { setAnnual(false); });
                yearBtn.addEventListener('click', function () { setAnnual(true); });
            }
        })();

        (function () {
            var ticketsEl = document.getElementById('pf-tickets');
            var priceEl = document.getElementById('pf-price');
            var esEl = document.getElementById('pf-es');
            var ebEl = document.getElementById('pf-eb');
            var saveEl = document.getElementById('pf-save');
            var barEl = document.getElementById('pf-bar');
            if (!ticketsEl || !priceEl || !esEl || !ebEl || !saveEl || !barEl || !window.esTicketFeeCost) return;

            // The same rates the server rendered with (App\Utils\TicketFees), through the same
            // formula, so this panel, /compare and /for-talent cannot disagree.
            var panel = ticketsEl.closest('[data-rates]');
            var rates;
            try { rates = JSON.parse(panel.getAttribute('data-rates')); } catch (e) { return; }
            if (!rates || !rates.stripe || !rates.eventschedule || !rates.eventbrite) return;

            function fmt(n) { return '$' + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','); }

            function calc() {
                var tickets = parseFloat(ticketsEl.value) || 0;
                var price = parseFloat(priceEl.value) || 0;
                var esTotal = window.esTicketFeeCost(rates.eventschedule, rates.stripe, tickets, price);
                var ebTotal = window.esTicketFeeCost(rates.eventbrite, rates.stripe, tickets, price);
                var save = ebTotal - esTotal;
                esEl.textContent = fmt(esTotal);
                ebEl.textContent = fmt(ebTotal);
                saveEl.textContent = fmt(Math.max(save, 0));
                barEl.style.width = (ebTotal > 0 ? Math.min(100, (esTotal / ebTotal) * 100) : 0) + '%';
            }
            ticketsEl.addEventListener('input', calc);
            priceEl.addEventListener('input', calc);
            calc();
        })();

        (function () {
            // Progressive enhancement: the lists ship open, so no-JS and crawlers see
            // every item. Below md we collapse Free and Enterprise so Pro is the only
            // expanded card - the mobile stand-in for the desktop lift.
            var discs = document.querySelectorAll('details.plan-disc');
            if (!discs.length || !window.matchMedia) return;
            var mq = window.matchMedia('(max-width: 767px)');
            function sync() {
                discs.forEach(function (d) {
                    if (mq.matches) { d.removeAttribute('open'); } else { d.setAttribute('open', ''); }
                });
            }
            if (mq.addEventListener) { mq.addEventListener('change', sync); }
            sync();
        })();
    </script>

    <!-- Motion engines (the finale brings its own confetti) -->
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
