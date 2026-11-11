<x-marketing-layout :hp="true">
    <x-slot name="title">Installment Payments | Spread a Ticket Over Months</x-slot>
    <x-slot name="description">Let buyers pay for an expensive ticket monthly, with no interest. They see every date and amount first, and the ticket is theirs from the first payment.</x-slot>
    <x-slot name="breadcrumbTitle">Installments</x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule - Installment Payments"
        description="Split an expensive ticket over monthly payments charged automatically to the buyer's saved card, with no interest and no fee, and the ticket valid from the first payment." />
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
           Installments "The Standing Order" styles.

           CONCEPT: THE DATED SCHEDULE, AGREED IN ADVANCE. The thing that
           makes this feature trustworthy is that nothing about it is a
           surprise: the buyer sees every date and every amount before
           they agree, and then those exact dates and amounts happen. So
           the page is that schedule - a short column of dated rows, the
           first one settled and the rest waiting - and every section
           adds one more thing that is decided up front rather than
           later.

           WHY NOT A CALENDAR OR A PROGRESS BAR. A calendar pictures
           choice, and there is none here: the dates follow from the
           checkout date. A progress bar pictures completion, which is
           the seller's view; the reason to buy is the BUYER's view, and
           what they want is the list.

           DELIBERATELY NOT: /features/check-in owns the live count and
           its progress track; /stripe owns "The Payout";
           /invoiceninja owns "The Ledger"; /features/promo-codes owns
           the receipt and its order lines; /features/passes owns the
           punch card and its count.

           THE STATEMENT IS A PHYSICAL OBJECT, so .es-inst-statement is
           FIXED in both colour modes.

           COLOUR: blue-900, the deep end of the brand family, and the
           reasoning is documented rather than hidden. Three feature
           pages already spend blue at 600-800 (/features/gift-cards
           #1b45c4, /features/allocated-seating #1d4ed8,
           /features/embed-tickets #11429b) and none of them is reachable
           from this page. Everything else in the ticketing family is
           taken - sky, teal, emerald, amber, red, green, achromatic -
           and violet is banned, so the honest choice is a documented
           shade of the house colour rather than an invented ninth hue.
           Identity is carried by the STRUCTURE, a dated column, not by
           the accent.

           Measured against the grounds this page actually paints:
             light ground #f4f5f8: ink #0f1219 17.19, muted #4b5563 6.93,
                                   accent #1e3a8a 9.50
             dark ground  #0b0d12: ink #e7e9f0 16.02, muted #9aa0ae 7.42,
                                   accent #bfdbfe 13.68
             statement #141721 (fixed): ink #e7e9f0 14.74, muted #949aa8 6.34,
                                   accent #bfdbfe 12.59
           text-gray-500 is never used on the tinted ground. Use
           .es-inst-muted.
           ============================================================== */

        .es-inst-page { background-color: #f4f5f8; color: #0f1219; }
        .dark .es-inst-page { background-color: #0b0d12; color: #e7e9f0; }

        .es-inst-ink { color: #0f1219; }
        .dark .es-inst-ink { color: #e7e9f0; }
        .es-inst-muted { color: #4b5563; }
        .dark .es-inst-muted { color: #9aa0ae; }
        .es-inst-accent { color: #1e3a8a; }
        .dark .es-inst-accent { color: #bfdbfe; }

        .es-inst-rule { border-top: 1px solid rgba(15, 18, 25, 0.10); }
        .dark .es-inst-rule { border-top-color: rgba(231, 233, 240, 0.10); }

        .es-inst-tag {
            display: inline-block;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #1e3a8a;
        }
        .dark .es-inst-tag { color: #bfdbfe; }

        .es-inst-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.75rem;
            border: 1.5px solid rgba(30, 58, 138, 0.35);
            font-size: 0.8125rem;
            font-weight: 800;
            color: #1e3a8a;
        }
        .dark .es-inst-mark { border-color: rgba(191, 219, 254, 0.35); color: #bfdbfe; }

        .es-inst-panel {
            background-color: #ffffff;
            border: 1px solid rgba(15, 18, 25, 0.10);
            border-radius: 1rem;
        }
        .dark .es-inst-panel {
            background-color: rgba(255, 255, 255, 0.04);
            border-color: rgba(231, 233, 240, 0.10);
        }

        /* ---- THE STATEMENT. Fixed in both modes; see the contract above. ---- */
        .es-inst-statement {
            background-color: #141721;
            color: #e7e9f0;
            border: 1px solid rgba(255, 255, 255, 0.09);
            border-radius: 1rem;
            box-shadow: 0 18px 45px rgba(7, 9, 14, 0.45);
        }
        .es-inst-statement-ink { color: #e7e9f0; }
        .es-inst-statement-muted { color: #949aa8; }
        .es-inst-statement-accent { color: #bfdbfe; }
        .es-inst-statement-rule { border-top: 1px solid rgba(255, 255, 255, 0.09); }

        /* Tabular figures so a column of money lines up as a column. */
        .es-inst-figure {
            font-variant-numeric: tabular-nums;
            font-feature-settings: "tnum" 1;
        }

        /* A leg of the plan. Paid is a filled square, due is hollow - shape, not
           colour, so the state reads on a mono screen. */
        .es-inst-leg {
            width: 0.6rem;
            height: 0.6rem;
            border-radius: 0.15rem;
            border: 1.5px solid rgba(231, 233, 240, 0.35);
            flex: none;
        }
        .es-inst-leg-paid { background-color: #bfdbfe; border-color: #bfdbfe; }
        /* The same mark on a page panel (the missed-payment timeline), where the
           statement's pale border would vanish on white. */
        .es-inst-panel .es-inst-leg { border-color: rgba(30, 58, 138, 0.45); }
        .dark .es-inst-panel .es-inst-leg { border-color: rgba(191, 219, 254, 0.45); }

        .es-inst-band { background-color: #0d1017; }
        .es-inst-band-tag {
            display: inline-block;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #bfdbfe;
        }
        .es-inst-band-grad {
            background-image: linear-gradient(90deg, #bfdbfe, #7dd3fc);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        /* Shared classes carry their own .dark rules in marketing.css and would
           otherwise change inside a band that has none. */
        .es-inst-band .grid-overlay { background-image:
            linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px); }
        .es-inst-band .es-claim:focus-within { border-color: rgba(191, 219, 254, 0.55); }

        /* marketing.css:248 only rings a.feature-card|bento-card|persona-card. */
        #es-inst-page a:focus-visible,
        #es-inst-page summary:focus-visible,
        #es-inst-page button:focus-visible {
            outline: 2px solid #1e3a8a;
            outline-offset: 2px;
        }
        .dark #es-inst-page a:focus-visible,
        .dark #es-inst-page summary:focus-visible,
        .dark #es-inst-page button:focus-visible { outline-color: #bfdbfe; }
    </style>

    <div id="es-inst-page" class="es-inst-page">

        <!-- ============================================================ -->
        <!-- 1. Hero: the statement                                       -->
        <!-- ============================================================ -->
        <section id="top" class="relative scroll-mt-24 overflow-hidden py-16 lg:py-24">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="grid items-center gap-12 lg:grid-cols-2">
                    <div>
                        <h1 class="es-balance es-inst-ink text-4xl font-black tracking-tight md:text-6xl" data-reveal style="--reveal-delay: 0.05s;">
                            <x-marketing.hero-eyebrow class="block es-inst-tag mb-4">Installment payments &middot; Pro &middot; Stripe</x-marketing.hero-eyebrow>
                            Pay in installments, and the price stops being <span class="es-inst-accent">the reason not to.</span>
                        </h1>
                        <p class="es-inst-muted mt-6 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                            A course, a retreat, a weekend announced eight months out. Let the buyer pay for it monthly: the first payment at checkout, the ticket valid straight away, and the rest charged to the same card on dates they agreed to before they agreed to anything.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3" data-reveal style="--reveal-delay: 0.15s;">
                            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#1e3a8a] px-6 py-3 font-semibold text-white transition-colors hover:bg-[#182f70]">
                                Start for free
                            </a>
                            <a href="{{ marketing_url('/docs/tickets') }}#installments" class="es-inst-ink inline-flex items-center gap-2 rounded-xl border border-gray-300 px-6 py-3 font-semibold transition-colors hover:border-[#1e3a8a] dark:border-white/15">
                                Read the guide
                            </a>
                        </div>
                        <p class="es-inst-muted mt-4 text-sm" data-reveal style="--reveal-delay: 0.2s;">
                            No interest and no fee. The total is the same whichever way they pay.
                        </p>
                    </div>

                    @php
                        // 1,000 over three, which is the docs' own worked example: the odd cent goes
                        // on the first payment. Fixed, never random, for the band-diff verifier.
                        $instLegs = [
                            ['paid', '4 March', '333.34', 'Paid at checkout - ticket issued'],
                            ['due', '4 April', '333.33', 'Charged to the saved card'],
                            ['due', '4 May', '333.33', 'Plan complete, 21 days before the doors'],
                        ];
                    @endphp
                    <div class="es-inst-statement p-6 sm:p-8" data-reveal="panel" style="--reveal-delay: 0.1s;">
                        <div class="flex items-baseline justify-between gap-4">
                            <p class="es-inst-statement-muted text-xs font-semibold uppercase tracking-widest">Payment plan</p>
                            <p class="es-inst-figure es-inst-statement-ink text-sm font-black">1,000.00</p>
                        </div>

                        <ol class="mt-6 space-y-4">
                            @foreach ($instLegs as [$legState, $legDate, $legAmount, $legNote])
                                <li class="flex items-start gap-3">
                                    <span class="es-inst-leg {{ $legState === 'paid' ? 'es-inst-leg-paid' : '' }} mt-1.5" aria-hidden="true"></span>
                                    <span class="min-w-0 flex-1">
                                        <span class="flex items-baseline justify-between gap-4">
                                            <span class="{{ $legState === 'paid' ? 'es-inst-statement-ink' : 'es-inst-statement-muted' }} text-sm font-semibold">{{ $legDate }}</span>
                                            <span class="es-inst-figure {{ $legState === 'paid' ? 'es-inst-statement-ink' : 'es-inst-statement-muted' }} text-sm font-semibold">{{ $legAmount }}</span>
                                        </span>
                                        <span class="{{ $legState === 'paid' ? 'es-inst-statement-accent' : 'es-inst-statement-muted' }} mt-0.5 block text-xs">{{ $legNote }}</span>
                                    </span>
                                </li>
                            @endforeach
                        </ol>

                        <div class="es-inst-statement-rule mt-6 pt-4">
                            <p class="es-inst-statement-muted text-xs leading-relaxed">
                                The buyer sees exactly this before they commit, confirms they authorise the later charges, and is charged only the first one.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. What you decide                                           -->
        <!-- ============================================================ -->
        <section id="setup" class="es-inst-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-inst-mark mb-6" data-reveal aria-hidden="true"><span>01</span></div>
                    <p class="es-inst-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Three settings</p>
                    <h2 class="es-balance es-inst-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Decided once, <span class="es-inst-accent">on the event.</span>
                    </h2>
                    <p class="es-inst-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Turn it on under Tickets, then Payment, and answer three questions. Everything after that follows from the date somebody buys.
                    </p>
                </div>

                @php
                    $instSettings = [
                        ['How many payments', 'The order total split into that many monthly charges, the first at checkout. An amount that will not divide evenly puts the odd cent on the first payment, so a thousand over three is 333.34 and then 333.33 twice.'],
                        ['How much runway', 'How long before the event the last payment has to land, so a failed charge can be chased while it still matters. Fourteen days is a sensible floor, and the editor tells you live whether the number of payments you picked actually finishes in time.'],
                        ['A floor for the option', 'Offer monthly payments only above an order value you set, so it appears on the full course and not on a single tasting. Leave it blank to offer it on everything.'],
                    ];
                @endphp
                <div class="grid gap-4 md:grid-cols-3" data-reveal-group="90">
                    @foreach ($instSettings as [$isName, $isBody])
                        <div class="es-inst-panel flex flex-col p-6" data-reveal="panel">
                            <h3 class="es-inst-ink text-base font-bold">{{ $isName }}</h3>
                            <p class="es-inst-muted mt-2 text-sm leading-relaxed">{{ $isBody }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="es-inst-panel mt-8 p-6" data-reveal="panel">
                    <h3 class="es-inst-ink text-base font-bold">Where it will not be offered, and why</h3>
                    <p class="es-inst-muted mt-2 text-sm leading-relaxed">
                        Each payment has to clear Stripe's own minimum charge, so an order split too many ways simply is not offered the option - and a promo code or a gift card applied at checkout can take an order under that line, or under your own floor, with the same result. It is also not offered on a basket spanning several events, or one containing a pass. The buyer is never shown a plan that cannot be carried out.
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. What the buyer gets                                       -->
        <!-- ============================================================ -->
        <section id="buyer" class="es-inst-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-inst-mark mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                    <p class="es-inst-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">At checkout and after</p>
                    <h2 class="es-balance es-inst-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Nothing about it <span class="es-inst-accent">is a surprise.</span>
                    </h2>
                    <p class="es-inst-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Paying in full is the default, and choosing monthly is a decision the buyer makes with the whole schedule in front of them.
                    </p>
                </div>

                @php
                    $instBuyer = [
                        ['They see the whole plan first', 'Every date and every amount, before committing, with an explicit confirmation that they authorise the later charges. There is no interest and no fee, so the total is identical either way.'],
                        ['The ticket works immediately', 'It is valid from the first payment. Nobody waits until the plan finishes to have something to show at the door.'],
                        ['A reminder before each charge', 'Two days ahead, naming the card and the amount, so the money leaving is expected rather than noticed afterwards.'],
                        ['Their own plan page', 'Linked from every one of those emails: pay early, clear the balance in one go, or change the card. It is the only place the saved card is shown.'],
                    ];
                @endphp
                <div class="grid gap-4 sm:grid-cols-2" data-reveal-group="80">
                    @foreach ($instBuyer as [$ibName, $ibBody])
                        <div class="es-inst-panel flex flex-col p-6" data-reveal="panel">
                            <h3 class="es-inst-ink text-base font-bold">{{ $ibName }}</h3>
                            <p class="es-inst-muted mt-2 text-sm leading-relaxed">{{ $ibBody }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8 grid gap-4 md:grid-cols-2" data-reveal-group="90">
                    <div class="es-inst-panel p-6" data-reveal="panel">
                        <h3 class="es-inst-ink text-base font-bold">If a payment fails</h3>
                        <p class="es-inst-muted mt-2 text-sm leading-relaxed">
                            A declined card is retried, and a plan that still falls into arrears puts the ticket on hold. At the door the scanner shows the name and the balance still owed rather than a flat refusal, and paying, or giving a new card, lifts the hold. The runway setting exists to leave time for all of that: the editor warns you while you are setting it up if the schedule you picked would not finish in time, and the checkout is the backstop, where an order whose last payment would land too late is simply not offered monthly payments.
                        </p>
                    </div>
                    <div class="es-inst-panel p-6" data-reveal="panel">
                        <h3 class="es-inst-ink text-base font-bold">What you see</h3>
                        <p class="es-inst-muted mt-2 text-sm leading-relaxed">
                            An Installments tab on the Sales page with each plan's progress and balance, plus a forecast of what is due and when. Money you have agreed but not yet collected is worth being able to look at.
                        </p>
                    </div>
                </div>
                <div class="mt-10 text-center">
                    <a href="{{ marketing_url('/features') }}" class="es-inst-accent inline-flex items-center font-medium hover:underline">
                        See all features
                        <svg aria-hidden="true" class="ml-1 h-4 w-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. When a payment plan makes sense                           -->
        <!-- ============================================================ -->
        <section id="when" class="es-inst-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-inst-mark mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                    <p class="es-inst-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Should you offer installment payments?</p>
                    <h2 class="es-balance es-inst-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        A payment plan fits a big price <span class="es-inst-accent">sold a long way out.</span>
                    </h2>
                    <p class="es-inst-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Monthly payments help a buyer who wants to come and cannot pay it all this month. They cost you cash up front and a little chasing. That trade is worth making in some cases and not in others.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2" data-reveal-group="90">
                    <div class="es-inst-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-inst-ink text-base font-bold">Offer it when</h3>
                        <ul class="es-inst-muted mt-3 list-disc space-y-2 pl-5 text-sm leading-relaxed">
                            <li>The ticket is a large share of what your audience can spend in a month: a residential course, a retreat, a festival weekend with a camping pass, a training that runs for weeks.</li>
                            <li>You go on sale months before the event, so there are months to spread the payments over.</li>
                            <li>You can wait for the money. The plan pays you month by month, so a venue deposit due now still comes out of what you already hold.</li>
                            <li>Your buyers are people, not companies. A business paying for staff usually wants one invoice, not four card payments.</li>
                        </ul>
                    </div>
                    <div class="es-inst-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-inst-ink text-base font-bold">Leave it off when</h3>
                        <ul class="es-inst-muted mt-3 list-disc space-y-2 pl-5 text-sm leading-relaxed">
                            <li>The ticket is cheap. Splitting it saves the buyer little, and each part must still clear Stripe's minimum charge.</li>
                            <li>Most tickets sell in the last few weeks. A plan has to finish before your runway, so late buyers would never be offered it.</li>
                            <li>You take payment through PayPal, Payfast, Razorpay, Invoice Ninja, a payment link or cash. Installments need Stripe, which can charge a saved card later.</li>
                            <li>Selling out is certain anyway. A plan helps people who would otherwise wait or not come; a show that sells out in a day does not need it.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 5. Setting the schedule                                      -->
        <!-- ============================================================ -->
        <section id="schedule" class="es-inst-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-inst-mark mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                    <p class="es-inst-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">How to set up a payment plan</p>
                    <h2 class="es-balance es-inst-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Count backwards <span class="es-inst-accent">from the doors.</span>
                    </h2>
                    <p class="es-inst-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Payments are a month apart, starting on the day of purchase, and the last one must land before your runway. So every number of payments has a last day it can be offered. After that day the buyer simply sees pay in full; the plan does not shrink to fit.
                    </p>
                </div>

                @php
                    // A retreat on 1 October with a 14-day runway: the last payment must be due by
                    // 17 September. Payments are monthly from the purchase date, so N payments fit
                    // an order placed by 17 September minus (N - 1) months.
                    $instCutoffs = [
                        ['2 payments', 'by 17 August'],
                        ['3 payments', 'by 17 July'],
                        ['4 payments', 'by 17 June'],
                        ['6 payments', 'by 17 April'],
                        ['12 payments', 'by 17 October the year before'],
                    ];
                @endphp
                <div class="grid items-start gap-8 lg:grid-cols-2">
                    <div class="es-inst-statement p-6 sm:p-8" data-reveal="panel">
                        <p class="es-inst-statement-muted text-xs font-semibold uppercase tracking-widest">Retreat on 1 October &middot; runway 14 days</p>
                        <p class="es-inst-statement-ink mt-2 text-sm">Last payment due by 17 September. Offered on orders placed:</p>
                        <div class="mt-5 space-y-3">
                            @foreach ($instCutoffs as [$icCount, $icBy])
                                <div class="flex items-baseline justify-between gap-4">
                                    <span class="es-inst-statement-ink text-sm font-semibold">{{ $icCount }}</span>
                                    <span class="es-inst-figure es-inst-statement-accent text-sm font-semibold">{{ $icBy }}</span>
                                </div>
                            @endforeach
                        </div>
                        <p class="es-inst-statement-rule es-inst-statement-muted mt-6 pt-4 text-xs leading-relaxed">
                            Announce this retreat in March and choose twelve payments, and nobody is ever offered the plan. Choose four, and everyone who books before mid-June can spread it.
                        </p>
                    </div>

                    <div class="space-y-4" data-reveal-group="90">
                        <div class="es-inst-panel p-6" data-reveal="panel">
                            <h3 class="es-inst-ink text-base font-bold">Pick the count for when most people buy</h3>
                            <p class="es-inst-muted mt-2 text-sm leading-relaxed">
                                Not for your earliest buyer. If most of your bookings come in the three months before an event, a count that fits three months is the one that gets used. The options are 2, 3, 4, 5, 6, 8, 10 or 12 payments, and the buyer chooses only between that plan and paying in full.
                            </p>
                        </div>
                        <div class="es-inst-panel p-6" data-reveal="panel">
                            <h3 class="es-inst-ink text-base font-bold">The first payment is the deposit</h3>
                            <p class="es-inst-muted mt-2 text-sm leading-relaxed">
                                There is no separate deposit amount: the first payment is one equal share, taken at checkout. With four payments it is a quarter of the order, with two it is half. Want more up front? Choose fewer payments.
                            </p>
                        </div>
                        <div class="es-inst-panel p-6" data-reveal="panel">
                            <h3 class="es-inst-ink text-base font-bold">Fourteen days of runway, not seven</h3>
                            <p class="es-inst-muted mt-2 text-sm leading-relaxed">
                                A declined card is retried one, four and nine days after the first attempt. At the seven-day minimum, the last retry of a declined final payment can fall after the doors open. At fourteen, every retry is done at least five days before the event.
                            </p>
                        </div>
                    </div>
                </div>

                @php
                    // The final payment of the plan above, declined. Retries follow
                    // SaleInstallment::RETRY_DAYS (1, 3, 5): the 18th, 21st and 26th.
                    $instTimeline = [
                        ['15 Sep', 'Reminder', 'The buyer is emailed two days ahead, with the card and the amount.'],
                        ['17 Sep', 'Declined', 'The buyer is told, and told plainly that the ticket still works. You get an email too.'],
                        ['18 and 21 Sep', 'Retries', 'The card is tried again, and each attempt that fails emails the buyer, who can change the card on their plan page.'],
                        ['24 Sep', 'Final notice', 'A week before the event, everyone with a balance gets a last notice, and you get the list.'],
                        ['26 Sep', 'Last retry', 'If this one fails, the ticket goes on hold until they pay or give a new card.'],
                        ['1 Oct', 'At the door', 'Scanning shows an amber Overdue balance screen with the name and what is owed. Admit anyway is your call.'],
                    ];
                @endphp
                <div class="es-inst-panel mt-8 p-6 sm:p-8" data-reveal="panel">
                    <h3 class="es-inst-ink text-base font-bold">What happens when the last payment fails, day by day</h3>
                    <ol class="mt-5 space-y-4">
                        @foreach ($instTimeline as [$itWhen, $itWhat, $itBody])
                            <li class="flex items-start gap-3">
                                <span class="es-inst-leg mt-1.5" aria-hidden="true"></span>
                                <span class="min-w-0">
                                    <span class="es-inst-ink block text-sm font-semibold"><span class="es-inst-figure">{{ $itWhen }}</span> &middot; {{ $itWhat }}</span>
                                    <span class="es-inst-muted block text-sm leading-relaxed">{{ $itBody }}</span>
                                </span>
                            </li>
                        @endforeach
                    </ol>
                    <p class="es-inst-muted es-inst-rule mt-6 pt-4 text-sm leading-relaxed">
                        If the buyer's bank asks them to approve a payment, they get an email asking them to confirm it instead, and it does not count as a decline. Full details are in <x-link href="{{ marketing_url('/docs/tickets') }}#installments-missed">the guide's section on missed payments</x-link>.
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 6. Wording the offer                                         -->
        <!-- ============================================================ -->
        <section id="wording" class="es-inst-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-inst-mark mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                    <p class="es-inst-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">How to word a payment plan offer</p>
                    <h2 class="es-balance es-inst-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Say the total, the count <span class="es-inst-accent">and the last day.</span>
                    </h2>
                    <p class="es-inst-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Checkout shows every date and amount before the buyer agrees. Your event description is where they decide whether to get that far, so put the plan there in plain words.
                    </p>
                </div>

                <div class="grid items-start gap-8 lg:grid-cols-2">
                    <div class="es-inst-statement p-6 sm:p-8" data-reveal="panel">
                        <p class="es-inst-statement-muted text-xs font-semibold uppercase tracking-widest">Lines to adapt for an event description</p>
                        <ul class="mt-5 space-y-4">
                            <li class="es-inst-statement-ink text-sm leading-relaxed">"Pay in full, or in four monthly payments with no interest and no fee. The first is taken today and your place is confirmed straight away."</li>
                            <li class="es-inst-statement-ink text-sm leading-relaxed">"Monthly payments are open on bookings made by 17 June. After that, the full price is due at booking."</li>
                            <li class="es-inst-statement-ink text-sm leading-relaxed">"If you need to cancel, [your policy]. Payments not yet taken are always cancelled with the booking."</li>
                        </ul>
                        <p class="es-inst-statement-rule es-inst-statement-muted mt-6 pt-4 text-xs leading-relaxed">
                            The date in the second line comes from the table above: your event date, less your runway, less one month for each payment after the first.
                        </p>
                    </div>

                    <div class="space-y-4" data-reveal-group="90">
                        <div class="es-inst-panel p-6" data-reveal="panel">
                            <h3 class="es-inst-ink text-base font-bold">Lead with the total</h3>
                            <p class="es-inst-muted mt-2 text-sm leading-relaxed">
                                "From a quarter a month" without the full price reads like a trick. Give the price, then the plan. A buyer who knows both trusts the monthly figure.
                            </p>
                        </div>
                        <div class="es-inst-panel p-6" data-reveal="panel">
                            <h3 class="es-inst-ink text-base font-bold">Avoid the words of a loan</h3>
                            <p class="es-inst-muted mt-2 text-sm leading-relaxed">
                                Nobody lends anything here: you charge your own buyer's card on agreed dates through your own Stripe account. Words like credit or finance suggest a lender and a credit check that are not there.
                            </p>
                        </div>
                        <div class="es-inst-panel p-6" data-reveal="panel">
                            <h3 class="es-inst-ink text-base font-bold">Decide your drop-out policy first</h3>
                            <p class="es-inst-muted mt-2 text-sm leading-relaxed">
                                Two actions on a sale's menu on the Sales page match the two usual policies. Refund Ticket returns every payment collected so far, as a plan is refunded only in full. Cancel Ticket stops the payments still to come and frees the place, without refunding what was paid. Either way, write the rule down before the first booking.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @include('marketing.partials.pricing-nudge')

        <!-- ============================================================ -->
        <!-- 4. FAQ                                                       -->
        <!-- ============================================================ -->
        @php
            $instFaqs = [
                ['q' => 'Does the buyer pay more for spreading it out?', 'a' => 'No. There is no interest and no fee - the total is exactly the same whether they pay once or monthly. What you are giving up is the cash today, not a margin.'],
                ['q' => 'Do they get the ticket straight away?', 'a' => 'Yes. The ticket is valid from the first payment, which is taken at checkout. Waiting until the plan finished would make the option useless for anything announced far in advance, which is the only kind of event this is for.'],
                ['q' => 'Can I use it with PayPal or another gateway?', 'a' => 'No. Installments need a card that can be charged automatically later, and Stripe is the only method here that can do that. The option appears only on events paid through Stripe. PayPal, Payfast and the other methods still sell the same tickets, paid in one go.'],
                ['q' => 'What happens if a monthly payment fails?', 'a' => 'The card is retried, and if the plan still falls into arrears the ticket goes on hold: at the door the scanner shows the name and the balance still owed rather than a flat refusal, and paying, or giving a new card, lifts the hold. That is what the runway setting is for: it makes you leave enough time before the event to notice and chase it, and the editor warns you if the schedule you picked would not finish in time.'],
                ['q' => 'Why is the option missing on some orders?', 'a' => 'Because the plan could not actually be carried out. Each individual payment has to clear Stripe\'s minimum charge, so a small order split several ways is not offered it - and a promo code or a gift card can take an order under that line, or under the minimum you set. It is also not offered on a basket spanning several events or one containing a pass.'],
                ['q' => 'Can a buyer clear the balance early?', 'a' => 'Yes. Their payment-plan page, linked from every reminder, lets them pay a single installment early, clear the whole balance, or change the card the rest will be charged to.'],
                ['q' => 'How does a refund work on a plan?', 'a' => 'Leg by leg, and in full only, from the Sales page. Every payment that was actually taken goes back through Stripe, each through the account it was charged to - installment plans are the one case that records which account that was, so a seller who has since reconnected Stripe is not refunding from the wrong place. The payments not yet taken are cancelled rather than refunded, so the card is never charged again, and the seats go back on sale.'],
                ['q' => 'Can I take a deposit now and the balance later?', 'a' => 'Not as a separate amount. The first payment is the deposit: one equal share of the order, taken at checkout, so with four payments it is a quarter and with two it is half. For a larger first payment, choose fewer payments.'],
                ['q' => 'Why did the payment plan stop being offered as the event got closer?', 'a' => 'Because the plan would no longer finish in time. Payments are a month apart from the day of purchase, and the last one has to be due before the runway you set. Once a new order could not fit every payment before that line, checkout offers paying in full only. The plan is never shortened to fit, so pick a number of payments that suits when most of your buyers book.'],
                ['q' => 'Can buyers choose how many payments to make?', 'a' => 'No. You choose the number once, on the event: 2, 3, 4, 5, 6, 8, 10 or 12. The buyer chooses between that plan and paying in full, which is selected by default. After that they can pay ahead or clear the balance from their plan page whenever they like.'],
                ['q' => 'Is this the same as buy now, pay later?', 'a' => 'No. With a buy now, pay later service a lender pays you and collects from the buyer. Here there is no lender: you charge your own buyer\'s saved card through your Stripe account on dates they agreed to, you receive each payment as it is collected, and an unpaid balance is yours to chase. There is no credit check, and no interest or fee for the buyer.'],
                ['q' => 'Which plan do I need?', 'a' => 'Installments are on the Pro plan, and on every selfhosted install at no cost. Selling a ticket that carries a price is Pro too, so the two arrive together, and there is no platform fee on any tier.'],
            ];
        @endphp
        <section id="faq" class="es-inst-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <h2 class="es-inst-ink mb-10 text-center text-3xl font-black tracking-tight md:text-4xl" data-reveal>Installment questions</h2>
                <div class="space-y-3" data-reveal-group="60">
                    @foreach ($instFaqs as $faq)
                        <details class="es-inst-panel group p-5" data-reveal="panel">
                            <summary class="es-inst-ink flex cursor-pointer items-center justify-between gap-4 text-base font-semibold">
                                {{ $faq['q'] }}
                                <svg class="h-5 w-5 shrink-0 transition-transform group-open:rotate-45" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                                </svg>
                            </summary>
                            <p class="es-inst-muted mt-3 text-sm leading-relaxed">{{ $faq['a'] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>

        <x-seo.faq-schema :items="$instFaqs" />

        <!-- ============================================================ -->
        <!-- 5. Claim                                                     -->
        <!-- ============================================================ -->
        <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
            <div class="mx-auto max-w-6xl">
                <div class="es-inst-band noise relative overflow-hidden rounded-[2rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-20" data-reveal="panel">
                    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                        <div class="grid-overlay absolute inset-0 opacity-25"></div>
                    </div>

                    <div class="relative z-10">
                        <p class="es-inst-band-tag mb-6">Free to start</p>
                        <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                            Sell the thing <span class="es-inst-band-grad">worth saving up for</span>.
                        </h2>
                        <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                            Three payments, no interest, and a ticket that works from the first one.
                        </p>

                        <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                            <label for="es-claim-input" class="sr-only">Your schedule name</label>
                            <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-lg border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                                <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                    class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                                <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                            </div>
                            <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg bg-white px-8 py-4 text-lg font-semibold text-[#0d1017] transition-colors hover:bg-gray-100">
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
