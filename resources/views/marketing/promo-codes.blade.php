<x-marketing-layout :hp="true">
    <x-slot name="title">Ticket Promo Codes and Add-ons | Discounts With a Deadline</x-slot>
    <x-slot name="description">Discount codes that expire, cap their own use and cover chosen ticket types, plus add-ons with their own stock. A code never comes off an add-on.</x-slot>
    <x-slot name="breadcrumbTitle">Promo Codes</x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule - Promo Codes and Add-ons"
        description="Percentage or fixed-amount discount codes with usage caps, expiry dates and per-ticket-type targeting, plus optional add-ons sold alongside a ticket with their own stock." />
    </x-slot>

    {{-- Motion gate: the hidden pre-reveal states below only apply when this class is present, so
         no-JS visitors, crawlers and reduced-motion users always see the whole page. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <style {!! nonce_attr() !!}>
        /* ==============================================================
           Promo codes "The Order Line" styles.

           CONCEPT: THE RECEIPT, READ TOP TO BOTTOM. A discount is not a
           number, it is a LINE on an order, and the only questions
           anybody actually has about one are what it comes off and in
           what order. So the page is the order itself, with its lines
           in the sequence the checkout really computes them: the
           tickets, then a volume discount, then the code, and the add-on
           standing outside the discount entirely. Drawing it as a
           receipt makes the rule visible instead of stated.

           WHY PROMO CODES AND ADD-ONS SHARE A PAGE. They are the same
           decision from two directions - one takes money off the order
           line and the other puts money on it - and neither is large
           enough alone to be worth a page a reader has to find. Their
           interaction is also the single most misunderstood thing about
           either: a code never discounts an add-on.

           DELIBERATELY NOT: /invoiceninja owns "The Ledger" and is
           bookkeeping after the fact; /stripe owns "The Payout";
           /features/gift-cards owns "The Gift Envelope" and is a
           PREPAID BALANCE, not a discount - the page says so out loud
           because buyers conflate them constantly.

           COLOUR: red, because red is the ink a price is struck through
           with, and because the ticketing family has nothing else left -
           /features/ticketing sky, /features/gift-cards,
           /features/embed-tickets and /features/allocated-seating blue,
           /features/passes teal, /features/check-in achromatic, /paypal
           amber, /features/analytics emerald. Red is used ONLY on the
           discount line and on headings, never as an error state, and
           the discount is additionally marked by SHAPE (a minus and an
           indent) so it does not depend on colour at all.

           Measured against the grounds this page actually paints:
             light ground #f7f4f3: ink #141110 17.17, muted #4b5563 6.91,
                                   accent #991b1b 7.59
             dark ground  #0d0b0b: ink #e9e4e3 15.58, muted #a29a99 7.12,
                                   accent #fca5a5 10.34
             receipt #161211 (fixed): ink #e9e4e3 14.77, muted #9a918f 6.04,
                                   accent #fca5a5 9.80
           text-gray-500 is never used on the tinted ground. Use
           .es-line-muted.
           ============================================================== */

        .es-line-page { background-color: #f7f4f3; color: #141110; }
        .dark .es-line-page { background-color: #0d0b0b; color: #e9e4e3; }

        .es-line-ink { color: #141110; }
        .dark .es-line-ink { color: #e9e4e3; }
        .es-line-muted { color: #4b5563; }
        .dark .es-line-muted { color: #a29a99; }
        .es-line-accent { color: #991b1b; }
        .dark .es-line-accent { color: #fca5a5; }

        .es-line-rule { border-top: 1px solid rgba(20, 17, 16, 0.10); }
        .dark .es-line-rule { border-top-color: rgba(233, 228, 227, 0.10); }

        .es-line-tag {
            display: inline-block;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #991b1b;
        }
        .dark .es-line-tag { color: #fca5a5; }

        .es-line-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.75rem;
            border: 1.5px solid rgba(153, 27, 27, 0.35);
            font-size: 0.8125rem;
            font-weight: 800;
            color: #991b1b;
        }
        .dark .es-line-mark { border-color: rgba(252, 165, 165, 0.35); color: #fca5a5; }

        .es-line-panel {
            background-color: #ffffff;
            border: 1px solid rgba(20, 17, 16, 0.10);
            border-radius: 1rem;
        }
        .dark .es-line-panel {
            background-color: rgba(255, 255, 255, 0.04);
            border-color: rgba(233, 228, 227, 0.10);
        }

        /* ---- THE RECEIPT. A printed slip is the same slip whatever the
           light in the room, so it is FIXED in both colour modes. ---- */
        .es-line-receipt {
            background-color: #161211;
            color: #e9e4e3;
            border: 1px solid rgba(255, 255, 255, 0.09);
            border-radius: 1rem;
            box-shadow: 0 18px 45px rgba(8, 6, 6, 0.45);
        }
        .es-line-receipt-ink { color: #e9e4e3; }
        .es-line-receipt-muted { color: #9a918f; }
        .es-line-receipt-accent { color: #fca5a5; }
        .es-line-receipt-rule { border-top: 1px dashed rgba(255, 255, 255, 0.18); }

        /* Tabular figures so a column of money lines up as a column. */
        .es-line-figure {
            font-variant-numeric: tabular-nums;
            font-feature-settings: "tnum" 1;
        }

        .es-line-band { background-color: #14100f; }
        .es-line-band-tag {
            display: inline-block;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #fca5a5;
        }
        .es-line-band-grad {
            background-image: linear-gradient(90deg, #fca5a5, #fdba74);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        /* Shared classes carry their own .dark rules in marketing.css and would
           otherwise change inside a band that has none. */
        .es-line-band .grid-overlay { background-image:
            linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px); }
        .es-line-band .es-claim:focus-within { border-color: rgba(252, 165, 165, 0.55); }

        /* marketing.css:248 only rings a.feature-card|bento-card|persona-card. */
        #es-line-page a:focus-visible,
        #es-line-page summary:focus-visible,
        #es-line-page button:focus-visible {
            outline: 2px solid #991b1b;
            outline-offset: 2px;
        }
        .dark #es-line-page a:focus-visible,
        .dark #es-line-page summary:focus-visible,
        .dark #es-line-page button:focus-visible { outline-color: #fca5a5; }
    </style>

    <div id="es-line-page" class="es-line-page">

        <!-- ============================================================ -->
        <!-- 1. Hero: the receipt                                         -->
        <!-- ============================================================ -->
        <section id="top" class="relative scroll-mt-24 overflow-hidden py-16 lg:py-24">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="grid items-center gap-12 lg:grid-cols-2">
                    <div>
                        <h1 class="es-balance es-line-ink text-4xl font-black tracking-tight md:text-6xl" data-reveal style="--reveal-delay: 0.05s;">
                            <x-marketing.hero-eyebrow class="block es-line-tag mb-4">Ticket promo codes &amp; add-ons &middot; Pro</x-marketing.hero-eyebrow>
                            Promo codes and add-ons: money off, <span class="es-line-accent">and money on.</span>
                        </h1>
                        <p class="es-line-muted mt-6 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                            A discount code takes something off the order. An add-on puts something on it. They are the same decision from two directions, they meet on the same receipt, and the order they are applied in is fixed so nothing is ever counted twice.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3" data-reveal style="--reveal-delay: 0.15s;">
                            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#991b1b] px-6 py-3 font-semibold text-white transition-colors hover:bg-[#7f1717]">
                                Start for free
                            </a>
                            <a href="{{ marketing_url('/docs/tickets') }}#promo-codes" class="es-line-ink inline-flex items-center gap-2 rounded-xl border border-gray-300 px-6 py-3 font-semibold transition-colors hover:border-[#991b1b] dark:border-white/15">
                                Read the guide
                            </a>
                        </div>
                        <p class="es-line-muted mt-4 text-sm" data-reveal style="--reveal-delay: 0.2s;">
                            Both are on the Pro plan, along with any ticket that has a price. No platform fee, ever.
                        </p>
                    </div>

                    @php
                        // The real order of operations at checkout. Fixed, never random: the page
                        // has to render identically on every request for the band-diff verifier.
                        $orderLines = [
                            ['plain', '2 x General admission', '80.00', 'The tickets'],
                            ['minus', 'Volume discount', '-8.00', 'Worked out first'],
                            ['minus', 'EARLYBIRD, 20%', '-14.40', 'Applied to what is left'],
                            ['plus', 'Parking add-on', '+12.00', 'Never discounted'],
                        ];
                    @endphp
                    <div class="es-line-receipt p-6 sm:p-8" data-reveal="panel" style="--reveal-delay: 0.1s;">
                        <div class="flex items-baseline justify-between gap-4">
                            <p class="es-line-receipt-muted text-xs font-semibold uppercase tracking-widest">Order</p>
                            <p class="es-line-receipt-muted text-xs">Checkout</p>
                        </div>

                        <div class="mt-5 space-y-3">
                            @foreach ($orderLines as [$lineKind, $lineLabel, $lineAmount, $lineNote])
                                <div class="{{ $lineKind === 'minus' ? 'ps-4' : '' }}">
                                    <div class="flex items-baseline justify-between gap-4">
                                        <span class="{{ $lineKind === 'minus' ? 'es-line-receipt-accent' : 'es-line-receipt-ink' }} text-sm font-semibold">{{ $lineLabel }}</span>
                                        <span class="es-line-figure {{ $lineKind === 'minus' ? 'es-line-receipt-accent' : 'es-line-receipt-ink' }} text-sm font-semibold">{{ $lineAmount }}</span>
                                    </div>
                                    <p class="es-line-receipt-muted mt-0.5 text-xs">{{ $lineNote }}</p>
                                </div>
                            @endforeach
                        </div>

                        <div class="es-line-receipt-rule mt-6 flex items-baseline justify-between gap-4 pt-4">
                            <span class="es-line-receipt-ink text-base font-black">Total</span>
                            <span class="es-line-figure es-line-receipt-ink text-base font-black">69.60</span>
                        </div>
                        <p class="es-line-receipt-muted mt-3 text-xs leading-relaxed">
                            The indent is the rule: a discount only ever applies to the ticket lines above it, and never to the add-on below.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. What a code can be told to do                             -->
        <!-- ============================================================ -->
        <section id="codes" class="es-line-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-line-mark mb-6" data-reveal aria-hidden="true"><span>01</span></div>
                    <p class="es-line-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The promo code</p>
                    <h2 class="es-balance es-line-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        A discount with <span class="es-line-accent">an end in sight.</span>
                    </h2>
                    <p class="es-line-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        An early-bird price that never ends is just your price. Every code carries the limits that make it an offer rather than a discount you forgot to switch off.
                    </p>
                </div>

                @php
                    $codeSettings = [
                        ['Percentage or a flat amount', 'Twenty per cent off, or ten off. A percentage is capped at 100 and a fixed amount can never take more than the eligible subtotal, so neither can turn an order negative.'],
                        ['A limit on how many', 'Cap the number of uses and the code stops working when it runs out, with the count so far shown beside it. Leave it blank if the limit is the date instead.'],
                        ['An expiry date and time', 'Not a date, a moment. An early-bird that ends at midnight on Friday ends at midnight on Friday.'],
                        ['An off switch', 'Deactivate a code without deleting it, so the orders that used it keep their history and the code can come back next season.'],
                        ['Only certain tickets', 'Point a code at every ticket type, or tick the ones it covers - which is how you discount the standing floor and not the balcony.'],
                        ['A link that fills it in', 'Every code has a copy-link button that produces a URL with the code already applied at checkout, so a newsletter or a story link does not ask anyone to type it.'],
                    ];
                @endphp
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">
                    @foreach ($codeSettings as [$csName, $csBody])
                        <div class="es-line-panel flex flex-col p-6" data-reveal="panel">
                            <h3 class="es-line-ink text-base font-bold">{{ $csName }}</h3>
                            <p class="es-line-muted mt-2 text-sm leading-relaxed">{{ $csBody }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. Add-ons                                                   -->
        <!-- ============================================================ -->
        <section id="addons" class="es-line-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-line-mark mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                    <p class="es-line-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Ticket add-ons</p>
                    <h2 class="es-balance es-line-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        The things people <span class="es-line-accent">would have bought anyway.</span>
                    </h2>
                    <p class="es-line-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Parking, a programme, a meal, a shirt. An add-on is an optional item on the same order as the ticket, with its own stock and its own limit per order - so the thing you have forty of stops selling after forty.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2" data-reveal-group="90">
                    <div class="es-line-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-line-ink text-base font-bold">Its own stock, its own ceiling</h3>
                        <p class="es-line-muted mt-2 text-sm leading-relaxed">
                            An add-on is counted separately from the ticket it rides along with, so a sold-out car park does not stop anyone buying a seat. A per-order maximum stops one buyer taking every parking space for a night.
                        </p>
                    </div>
                    <div class="es-line-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-line-ink text-base font-bold">Outside the discount</h3>
                        <p class="es-line-muted mt-2 text-sm leading-relaxed">
                            A promo code never comes off an add-on. Twenty per cent off the ticket is twenty per cent off the ticket, and the parking is still the price of the parking - which is what you want when the add-on is something you had to buy in.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. What it is not                                            -->
        <!-- ============================================================ -->
        <section id="not" class="es-line-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-line-mark mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                    <p class="es-line-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Not the same thing</p>
                    <h2 class="es-balance es-line-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        A code is not <span class="es-line-accent">a gift card.</span>
                    </h2>
                    <p class="es-line-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Buyers type both into the same box, so it is worth being clear about which is which. They can use one of each on the same order.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2" data-reveal-group="90">
                    <div class="es-line-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-line-ink text-base font-bold">A promo code changes the price</h3>
                        <p class="es-line-muted mt-2 text-sm leading-relaxed">
                            It is a rule you wrote: this much off, this many times, until this date. Nobody paid for it, and it has no balance to run down.
                        </p>
                    </div>
                    <div class="es-line-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-line-ink text-base font-bold">A gift card spends a balance</h3>
                        <p class="es-line-muted mt-2 text-sm leading-relaxed">
                            Somebody already paid you for it, and it pays for the order rather than discounting it. It is a separate feature with its own page, and the money behaves completely differently in your books.
                        </p>
                        <a href="{{ marketing_url('/features/gift-cards') }}" class="es-line-accent mt-auto pt-4 text-sm font-semibold hover:underline">
                            Gift cards
                        </a>
                    </div>
                </div>
                <div class="mt-10 text-center">
                    <a href="{{ marketing_url('/features') }}" class="es-line-accent inline-flex items-center font-medium hover:underline">
                        See all features
                        <svg aria-hidden="true" class="ml-1 h-4 w-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 5. The playbook                                              -->
        <!-- ============================================================ -->
        <section id="playbook" class="es-line-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-line-mark mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                    <p class="es-line-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">A ticket promo code playbook</p>
                    <h2 class="es-balance es-line-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Six kinds of code, <span class="es-line-accent">and how to set each one.</span>
                    </h2>
                    <p class="es-line-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Most organizers run the same handful of offers. Each one has a job, and the settings follow from the job. The field names below are the ones in the event's Tickets tab.
                    </p>
                </div>

                @php
                    $codePlays = [
                        ['Early bird', 'Rewards the people who commit first, and tells you early how a show is selling while there is still time to act on it.', ['Discount Type: Percentage', 'Discount Value: 15 to 20', 'Expires At: the moment you announce', 'Max Uses: blank, or a cap in orders'], 'Want everyone to see the lower price without typing a code? Make it a ticket type with its own sales end date instead. Sales windows work on every plan.'],
                        ['Group booking', 'Not a code at all. A volume discount on the ticket type applies itself once an order reaches the minimum quantity you set, so nobody has to ask and a single buyer cannot use it.', ['Volume discount on the ticket type', 'Minimum quantity: 4, say', 'Type: Percentage or Fixed amount', 'Works on every plan'], 'A promo code still stacks on top: the volume discount comes off first and the code is worked out on what is left.'],
                        ['Partner or affiliate', 'One code per partner, so every order on the Sales page says which partner brought it: a choir, a bookshop, a club that shares your audience.', ['One code each: CHOIR10, BOOKSHOP10', 'Discount Type: Percentage', 'Expires At: the end of the campaign', 'Max Uses: blank'], 'Paying the partner a commission instead of giving buyers money off? A promoter link (Pro) counts their clicks, tickets and revenue with no discount at all.'],
                        ['Comps and guest list', 'Press, the band\'s guests, a radio giveaway of ten pairs. A full discount on one ticket type, capped at the number of orders you are giving away.', ['Discount Value: 100 (Percentage)', 'Applies To: one ticket type', 'Max Uses: the number of comp orders', 'Expires At: the day before the show'], 'An order that comes to zero is confirmed with no payment step (except through an Invoice Ninja payment link). For guests you can name, importing them on the Sales page (Pro) skips the code, so there is nothing to forward.'],
                        ['Last-minute', 'For the seats still empty in the last few days. Short, narrow and sent privately, so the people who paid full price weeks ago do not learn to wait next time.', ['Expires At: 48 hours after sending', 'Applies To: only the type with seats left', 'Shared as the link that fills it in', 'Sent to followers, not posted'], 'Send it in a newsletter with the code\'s own link, so a reader taps once and the code is already filled in when they choose their tickets.'],
                        ['Thank-you for regulars', 'For the people who came last time. It turns a past buyer into a returning one, and the code tells you how many came back.', ['One code for your mailing list', 'Discount Type: Percentage or Fixed amount', 'Expires At: two weeks after sending', 'Max Uses: blank'], 'Ticket presets can carry promo codes, so a code you give regulars at every show is in place on each new event without retyping it.'],
                    ];
                @endphp
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">
                    @foreach ($codePlays as [$cpName, $cpWhy, $cpSettings, $cpNote])
                        <div class="es-line-panel flex flex-col p-6" data-reveal="panel">
                            <h3 class="es-line-ink text-base font-bold">{{ $cpName }}</h3>
                            <p class="es-line-muted mt-2 text-sm leading-relaxed">{{ $cpWhy }}</p>
                            <ul class="es-line-rule es-line-ink mt-4 space-y-1 pt-3 text-xs font-semibold">
                                @foreach ($cpSettings as $cpSetting)
                                    <li>{{ $cpSetting }}</li>
                                @endforeach
                            </ul>
                            <p class="es-line-muted mt-auto pt-4 text-xs leading-relaxed">{{ $cpNote }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="es-line-panel mt-8 p-6 sm:p-8" data-reveal="panel">
                    <h3 class="es-line-ink text-base font-bold">Where add-ons fit</h3>
                    <p class="es-line-muted mt-2 text-sm leading-relaxed">
                        Because a code never comes off an add-on, you can be generous on the door price and keep your margin on the things you paid for: the meal, the parking, the shirt. It also means a comp code is free entry, not a free everything. A guest who adds parking to a 100% code pays for the parking, and checkout asks for that amount alone. The same is true of a donation at checkout: no code ever reduces it.
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 6. Limits that stop a code leaking                           -->
        <!-- ============================================================ -->
        <section id="limits" class="es-line-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-line-mark mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                    <p class="es-line-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Promo code limits and deadlines</p>
                    <h2 class="es-balance es-line-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Codes get shared. <span class="es-line-accent">Set them up for it.</span>
                    </h2>
                    <p class="es-line-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        A code sent to forty people reaches four hundred the moment one of them posts it. You cannot stop that, so set every code so that the worst case is a cost you already agreed to.
                    </p>
                </div>

                @php
                    $codeLimits = [
                        ['Max Uses counts orders, not tickets', 'Each order that uses a code counts once, however many tickets it holds. A comp code with Max Uses at 10 is ten orders, which can be far more than ten seats. Add a Max per order limit on the ticket type as well, or point the code at a ticket type whose own Quantity is the ceiling you mean.'],
                        ['A fixed amount comes off the order once', 'A fixed code takes its amount off the order\'s eligible tickets, once. Ten off an order of four tickets is ten off, not forty. If you mean so much off each ticket, use a percentage, or a volume discount on the ticket type.'],
                        ['Give every code an end', 'Without Expires At, a code works until its uses run out or you switch it off, and a code posted on a deals site does not go away by itself. Set a moment, not just a day, and say it in the message that sends the code.'],
                        ['Each event keeps its own count', 'A code belongs to the event it is on. Add the same code to five events with Max Uses at 50 and it can be used up to 250 times across them. Set the cap for each event as if it were the only one.'],
                        ['One code per order', 'A buyer enters one promo code for an event, so two codes never stack. A code does combine with a volume discount and with a gift card, and the order of those three is fixed, so the same money is never discounted twice.'],
                        ['Capitals do not make a code private', 'Codes are matched whatever the capitals, and FREE, VIP or 100OFF are the first things people try. Use a word only your audience would know, up to 50 characters, and keep a 100% code on a short leash with a low Max Uses.'],
                    ];
                @endphp
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">
                    @foreach ($codeLimits as $clIndex => [$clName, $clBody])
                        <div class="es-line-panel flex flex-col p-6" data-reveal="panel">
                            <p class="es-line-tag mb-3">Rule {{ $clIndex + 1 }}</p>
                            <h3 class="es-line-ink text-base font-bold">{{ $clName }}</h3>
                            <p class="es-line-muted mt-2 text-sm leading-relaxed">{{ $clBody }}</p>
                        </div>
                    @endforeach
                </div>
                <p class="es-line-muted mx-auto mt-8 max-w-3xl text-center text-sm">
                    Max per order and volume discounts are set on each ticket type: <x-link href="{{ marketing_url('/docs/tickets') }}#max-per-order">Max per order</x-link> and <x-link href="{{ marketing_url('/docs/tickets') }}#volume-discount">volume discounts</x-link> in the guide.
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 7. Measuring which code worked                               -->
        <!-- ============================================================ -->
        <section id="measure" class="es-line-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-line-mark mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                    <p class="es-line-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Did the discount pay for itself?</p>
                    <h2 class="es-balance es-line-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Count what a code brought <span class="es-line-accent">against what it gave away.</span>
                    </h2>
                    <p class="es-line-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Every discounted ticket is money off a ticket somebody might have bought anyway. A code earned its keep only if it brought buyers who would not have come, or brought them early enough to change what you did next.
                    </p>
                </div>

                <div class="grid items-start gap-8 lg:grid-cols-2">
                    <div class="space-y-4" data-reveal-group="90">
                        <div class="es-line-panel p-6" data-reveal="panel">
                            <h3 class="es-line-ink text-base font-bold">Where the numbers are</h3>
                            <p class="es-line-muted mt-2 text-sm leading-relaxed">
                                Times Used sits beside each code's Active switch, counted in orders. On the Sales page every order that used a code carries it, with what it took off. The sales export (Pro) has Promo Code, Discount and Promoter columns, so a spreadsheet can total each code's orders and its cost.
                            </p>
                        </div>
                        <div class="es-line-panel p-6" data-reveal="panel">
                            <h3 class="es-line-ink text-base font-bold">One code per channel</h3>
                            <p class="es-line-muted mt-2 text-sm leading-relaxed">
                                The same 15% as NEWS15 in the newsletter, POSTER15 on the posters and RADIO15 on air tells you which channel sold tickets, which no single code can. Keep the discount the same across them, or you are measuring the size of the discount instead of the channel.
                            </p>
                        </div>
                        <div class="es-line-panel p-6" data-reveal="panel">
                            <h3 class="es-line-ink text-base font-bold">Look at when the orders came</h3>
                            <p class="es-line-muted mt-2 text-sm leading-relaxed">
                                If most early-bird orders land in the last two days before the deadline, the deadline did the work and a smaller discount would likely have done the same. If they arrive evenly from the first day, the price was the draw.
                            </p>
                        </div>
                    </div>

                    @php
                        // Break-even: at discount d, revenue per ticket falls to (1 - d), so the same
                        // takings need 1 / (1 - d) times the buyers. 10% -> 11%, 20% -> 25%,
                        // 25% -> 33%, 30% -> 43%, 50% -> 100% more.
                        $breakEven = [
                            ['10% off', '11% more'],
                            ['20% off', '25% more'],
                            ['25% off', '33% more'],
                            ['30% off', '43% more'],
                            ['50% off', '100% more'],
                        ];
                    @endphp
                    <div class="es-line-receipt p-6 sm:p-8" data-reveal="panel">
                        <div class="flex items-baseline justify-between gap-4">
                            <p class="es-line-receipt-muted text-xs font-semibold uppercase tracking-widest">Discount</p>
                            <p class="es-line-receipt-muted text-xs font-semibold uppercase tracking-widest">Buyers needed to break even</p>
                        </div>
                        <div class="mt-5 space-y-3">
                            @foreach ($breakEven as [$beOff, $beMore])
                                <div class="flex items-baseline justify-between gap-4">
                                    <span class="es-line-figure es-line-receipt-accent text-sm font-semibold">{{ $beOff }}</span>
                                    <span class="es-line-figure es-line-receipt-ink text-sm font-semibold">{{ $beMore }}</span>
                                </div>
                            @endforeach
                        </div>
                        <p class="es-line-receipt-rule es-line-receipt-muted mt-6 pt-4 text-xs leading-relaxed">
                            To take the same money at 20% off, you need a quarter more buyers than you would have had at full price. Half price needs twice as many. Worth it when the room would otherwise be empty, rarely when it would have sold anyway.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        @include('marketing.partials.pricing-nudge')

        <!-- ============================================================ -->
        <!-- 5. FAQ                                                       -->
        <!-- ============================================================ -->
        @php
            $promoFaqs = [
                ['q' => 'Can a code apply to only one ticket type?', 'a' => 'Yes. Point it at all tickets, or tick the specific types it covers. That is how you run a discount on the standing floor while the seated tickets stay at full price.'],
                ['q' => 'Can one code cover all my events?', 'a' => 'Not on its own. A code belongs to the event it was made on and keeps its own count, limit and expiry there, so to run one offer across several events you add the same code to each of them. In the multi-event cart each event\'s code discounts that event\'s tickets and nothing else in the basket.'],
                ['q' => 'Does a promo code discount add-ons too?', 'a' => 'No, never. A code applies to the eligible ticket lines only, so the parking, the programme and the meal stay at their own price. That is deliberate: an add-on is usually something you had to buy in, and discounting it costs you real money rather than margin.'],
                ['q' => 'What happens if a volume discount and a promo code both apply?', 'a' => 'A volume discount is the price break you can set on a ticket type for people buying several at once, so it is decided by the size of the order rather than by anything the buyer types. The two are applied in a fixed order - the volume discount first, then the code against what is left - so the same money is never discounted twice. A percentage code is capped at 100% and a fixed code can never take more than the eligible subtotal.'],
                ['q' => 'How do I get the code to people?', 'a' => 'Every code has a copy-link button that produces a URL with the code already applied at checkout. Put that in a newsletter or a story and nobody has to type anything or remember the spelling.'],
                ['q' => 'Can I stop a code without losing the history?', 'a' => 'Yes. Switch it inactive rather than deleting it. The orders that used it keep their record, the usage count is still there, and you can switch it back on next season.'],
                ['q' => 'Can a buyer use a promo code and a gift card together?', 'a' => 'Yes, on the same order. They do different jobs: the code changes the price, and the gift card spends a balance somebody already paid you for.'],
                ['q' => 'Is there anything different about Invoice Ninja?', 'a' => 'In payment-link mode, yes: one promo code per event, applying to all ticket types. If you need several codes or per-ticket targeting on Invoice Ninja, use invoice mode instead.'],
                ['q' => 'Does Max Uses count tickets or orders?', 'a' => 'Orders. Each order that uses the code counts once, whether it holds one ticket or six. To limit the number of discounted tickets as well, set Max per order on the ticket type, or point the code at a ticket type whose own quantity is the limit you want.'],
                ['q' => 'Is a fixed-amount code taken off each ticket or the whole order?', 'a' => 'The whole order, once. A fixed code takes its amount off the tickets it applies to, together, and never more than their total. For a discount that grows with the number of tickets, use a percentage code, or a volume discount on the ticket type.'],
                ['q' => 'Can I schedule a promo code to start later?', 'a' => 'A code has an end (Expires At) but no start. Add it with its Active switch off, save the event, and switch it on when the offer opens. Until then the code is refused at checkout like any code that does not exist.'],
                ['q' => 'How do I give out free comp tickets?', 'a' => 'Two ways. A 100% code pointed at one ticket type, with Max Uses set to the number of comp orders: an order that comes to zero is confirmed straight away, with no payment step (except through an Invoice Ninja payment link), and any add-on the guest picks is still charged. Or, for guests you can name, import them from the Sales page (Pro) with Send Email on, which issues their tickets without a code anyone could forward.'],
                ['q' => 'Which plan do I need?', 'a' => 'Promo codes and add-ons are both on the Pro plan, and on every selfhosted install at no cost. Anything with a price on it needs Pro anyway, so a discount code always arrives with the rest of the ticketing suite, and no plan takes a platform fee: only your payment processor\'s fee comes off a paid ticket.'],
            ];
        @endphp
        <section id="faq" class="es-line-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <h2 class="es-line-ink mb-10 text-center text-3xl font-black tracking-tight md:text-4xl" data-reveal>Promo code questions</h2>
                <div class="space-y-3" data-reveal-group="60">
                    @foreach ($promoFaqs as $faq)
                        <details class="es-line-panel group p-5" data-reveal="panel">
                            <summary class="es-line-ink flex cursor-pointer items-center justify-between gap-4 text-base font-semibold">
                                {{ $faq['q'] }}
                                <svg class="h-5 w-5 shrink-0 transition-transform group-open:rotate-45" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                                </svg>
                            </summary>
                            <p class="es-line-muted mt-3 text-sm leading-relaxed">{{ $faq['a'] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>

        <x-seo.faq-schema :items="$promoFaqs" />

        <!-- ============================================================ -->
        <!-- 6. Claim                                                     -->
        <!-- ============================================================ -->
        <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
            <div class="mx-auto max-w-6xl">
                <div class="es-line-band noise relative overflow-hidden rounded-[2rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-20" data-reveal="panel">
                    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                        <div class="grid-overlay absolute inset-0 opacity-25"></div>
                    </div>

                    <div class="relative z-10">
                        <p class="es-line-band-tag mb-6">Free to start</p>
                        <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                            Give them a reason <span class="es-line-band-grad">to book early</span>.
                        </h2>
                        <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                            A code that ends on Friday, and a car park that sells itself.
                        </p>

                        <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                            <label for="es-claim-input" class="sr-only">Your schedule name</label>
                            <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-lg border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                                <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                    class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                                <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                            </div>
                            <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg bg-white px-8 py-4 text-lg font-semibold text-[#14100f] transition-colors hover:bg-gray-100">
                                Get Started Free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </a>
                        </div>
                        <p class="mt-6 text-sm text-gray-400">No credit card required</p>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <x-marketing.related-pages />

    {{-- Load-bearing, not decoration: marketing.css hides every [data-reveal] element behind
         html.es-anim, so a page that sets that class and never loads the reveal observer renders
         completely blank below the nav. --}}
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
