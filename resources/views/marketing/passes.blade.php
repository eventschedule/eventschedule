<x-marketing-layout>
    <x-slot name="title">Passes &amp; Subscriptions | Class Packs and Memberships</x-slot>
    <x-slot name="description">Sell one pass a guest pays for once and reuses across many events. Class packs, memberships, festival passes and season tickets, all on one QR code.</x-slot>
    <x-slot name="breadcrumbTitle">Passes</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Passes and Subscriptions",
        "description": "Multi-use passes redeemable across events: visit passes, memberships, festival passes and season passes, each on a single QR code with its own visit counter.",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": ["Web", "Android", "iOS"],
        "featureList": [
            "Visit pass with a fixed number of visits",
            "Membership with unlimited visits until it expires",
            "Festival pass good for each covered event once",
            "Season pass covering every date of a recurring event",
            "One QR code for the whole series",
            "Saves to Google Wallet as one pass, not one per date",
            "Cover the whole schedule, a sub-schedule, or hand-picked events",
            "Optional advance booking from the holder's own pass page",
            "Cancellation deadline with a forfeit or block policy",
            "Admit a guest without spending an extra visit",
            "Visit log showing attended, booked and forfeited dates"
        ],
        "offers": {
            "@type": "Offer",
            "price": "{{ $proMonthly }}",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Available on the Pro plan"
        }
    }
    </script>
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
           Passes "The Punch Card" styles.

           CONCEPT: THE CARD IN SOMEBODY'S WALLET. A ticket is spent the
           night it is used; a pass is an object the holder keeps, and
           every visit takes one hole out of it. So the page is drawn as
           the card, and the running count is the whole design - which is
           also the data model: a pass is one QR with one visit counter,
           and every one of the four types is just a different way of
           counting down.

           WHY A PUNCH CARD AND NOT A WRISTBAND OR A MEMBERSHIP CARD. A
           wristband is one festival and cannot count (and
           /for-live-concerts already reaches for one). A blank
           membership card shows nothing. The punch card is the only one
           of the three whose APPEARANCE IS ITS BALANCE, which is exactly
           the thing a holder and an owner both want to know.

           DELIBERATELY NOT: a turnstile (/features/ticketing owns "The
           Turnstile" and the door), a gift envelope
           (/features/gift-cards - stored VALUE, not a count of visits),
           playing cards (/for-magicians owns es-pick-*), a squared
           house plan (/features/allocated-seating), a keyring
           (/why-create-account), a ledger (/invoiceninja) or a payout
           (/stripe).

           NO DECORATIVE LINE DRAWINGS. The only shapes here are the card
           and its holes, and a hole means a spent visit. Nothing is
           drawn that does not carry a number.

           THE CARD IS A PHYSICAL OBJECT, so .es-punch-card is FIXED in
           both colour modes - only the room around it changes. That
           includes the holes: a hole is punched THROUGH THE CARD, so it
           is drawn as card-coloured shadow rather than as the page
           ground showing through, which would make the card itself look
           different in dark mode.

           COLOUR, measured against the grounds this page actually paints
           rather than against pure white:
             light ground #f4f5f7: ink #101318 17.06, muted #4b5563 6.93,
                                   accent #115e59 6.95
             dark ground  #0b0d12: ink #e8eaf0 16.16, muted #98a2b3 7.55,
                                   accent #5eead4 13.14
             card #efe7d6 (fixed): ink #2b2417 12.48, muted #5f5647 5.87,
                                   accent #115e59 6.16
           Teal is the one hue the neighbouring feature pages have not
           spent: /features/ticketing is sky, /features/gift-cards and
           /features/embed-tickets are blue, /features/allocated-seating
           is blue. KNOWN AND ACCEPTED: /caldav also paints #115e59, as
           the dark half of a teal pair. It is in the calendar-sync
           family, neither page appears in the other's related strip,
           and no route reaches one from the other - so the neighbour
           test passes. Do not "fix" it by moving this page's accent
           without re-running the hue audit; the ticketing family has no
           unclaimed hue left to move to. text-gray-500 is never used - #6b7280 measures 4.43
           on this page's light ground. Use .es-punch-muted.
           NOTE teal-700 #0f766e measures only 4.45 ON THE CARD, so the
           accent on card stock is teal-800 #115e59. The lighter one is
           not used anywhere text sits.
           ============================================================== */

        .es-punch-page { background-color: #f4f5f7; color: #101318; }
        .dark .es-punch-page { background-color: #0b0d12; color: #e8eaf0; }

        .es-punch-ink { color: #101318; }
        .dark .es-punch-ink { color: #e8eaf0; }
        .es-punch-muted { color: #4b5563; }
        .dark .es-punch-muted { color: #98a2b3; }
        .es-punch-accent { color: #115e59; }
        .dark .es-punch-accent { color: #5eead4; }

        .es-punch-rule { border-top: 1px solid rgba(16, 19, 24, 0.09); }
        .dark .es-punch-rule { border-top-color: rgba(232, 234, 240, 0.10); }

        .es-punch-tag {
            display: inline-block;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #115e59;
        }
        .dark .es-punch-tag { color: #5eead4; }

        /* Section numerals, set as a punched chip so even the furniture counts. */
        .es-punch-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 9999px;
            border: 1.5px solid rgba(17, 94, 89, 0.35);
            font-size: 0.8125rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            color: #115e59;
        }
        .dark .es-punch-mark { border-color: rgba(94, 234, 212, 0.35); color: #5eead4; }

        .es-punch-panel {
            background-color: #ffffff;
            border: 1px solid rgba(16, 19, 24, 0.10);
            border-radius: 1rem;
        }
        .dark .es-punch-panel {
            background-color: rgba(255, 255, 255, 0.04);
            border-color: rgba(232, 234, 240, 0.10);
        }

        /* ---- THE CARD. Fixed in both modes; see the contract above. ---- */
        .es-punch-card {
            background-color: #efe7d6;
            color: #2b2417;
            border: 1px solid #d8ccb2;
            border-radius: 0.9rem;
            box-shadow: 0 10px 30px rgba(16, 19, 24, 0.16);
        }
        .es-punch-card-ink { color: #2b2417; }
        .es-punch-card-muted { color: #5f5647; }
        .es-punch-card-accent { color: #115e59; }
        .es-punch-card-rule { border-top: 1px solid rgba(43, 36, 23, 0.16); }

        /* The card's own perforated edge - a real one, not an illustration:
           it is where the counterfoil tears off. */
        .es-punch-perf {
            border-top: 2px dashed rgba(43, 36, 23, 0.28);
        }

        /* ---- A HOLE. Open = a visit still on the card. Punched = spent. ----
           STATUS IS SHAPE, NOT ONLY COLOUR: an open hole is a ring, a punched
           one is a filled well with an inner shadow. A mono printer and a
           colour-blind reader both still read the balance. */
        .es-punch-hole {
            position: relative;
            width: 1.75rem;
            height: 1.75rem;
            border-radius: 9999px;
            border: 1.5px dashed rgba(43, 36, 23, 0.42);
            background-color: transparent;
        }
        .es-punch-hole-spent {
            border-style: solid;
            border-color: rgba(43, 36, 23, 0.30);
            background-color: #cdbf9f;
            box-shadow: inset 0 2px 4px rgba(43, 36, 23, 0.45);
        }

        /* The punch lands as the card is revealed. Transition on the ALWAYS-ACTIVE
           rule and gate only the pre-state, so no-JS and reduced-motion rest PUNCHED. */
        .es-punch-hole { transition: background-color 0.45s ease, box-shadow 0.45s ease, border-color 0.45s ease; }
        html.es-anim [data-reveal]:not(.is-revealed) .es-punch-hole-spent {
            background-color: transparent;
            box-shadow: none;
            border-color: rgba(43, 36, 23, 0.42);
        }

        .es-punch-band { background-color: #0d1418; }
        .es-punch-band-tag {
            display: inline-block;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #5eead4;
        }
        .es-punch-band-grad {
            background-image: linear-gradient(90deg, #5eead4, #7dd3fc);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        /* Shared classes that carry their own .dark rules in marketing.css would
           otherwise change inside a band that has none. */
        .es-punch-band .grid-overlay { background-image:
            linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px); }
        .es-punch-band .es-claim:focus-within { border-color: rgba(94, 234, 212, 0.55); }

        /* The shared card focus ring at marketing.css:248 only matches
           a.feature-card|bento-card|persona-card, and this page uses none of them. */
        #es-punch-page a:focus-visible,
        #es-punch-page summary:focus-visible,
        #es-punch-page button:focus-visible {
            outline: 2px solid #115e59;
            outline-offset: 2px;
        }
        .dark #es-punch-page a:focus-visible,
        .dark #es-punch-page summary:focus-visible,
        .dark #es-punch-page button:focus-visible { outline-color: #5eead4; }
    </style>

    <div id="es-punch-page" class="es-punch-page">

        <!-- ============================================================ -->
        <!-- 1. Hero: the card                                            -->
        <!-- ============================================================ -->
        <section id="top" class="relative scroll-mt-24 overflow-hidden py-16 lg:py-24">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="grid items-center gap-12 lg:grid-cols-2">
                    <div>
                        <p class="es-punch-tag mb-4" data-reveal>Passes &amp; subscriptions &middot; Pro</p>
                        <h1 class="es-balance es-punch-ink text-4xl font-black tracking-tight md:text-6xl" data-reveal style="--reveal-delay: 0.05s;">
                            Sold once. <span class="es-punch-accent">Used ten times.</span>
                        </h1>
                        <p class="es-punch-muted mt-6 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                            A ticket gets one person into one event. A pass gets one person into many, on a single QR code, and counts itself down as they go. Class packs, memberships, festival passes and season tickets are all the same object with a different counter.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3" data-reveal style="--reveal-delay: 0.15s;">
                            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#115e59] px-6 py-3 font-semibold text-white transition-colors hover:bg-[#0f4c47]">
                                Start for free
                            </a>
                            <a href="{{ marketing_url('/docs/subscriptions') }}" class="es-punch-ink inline-flex items-center gap-2 rounded-xl border border-gray-300 px-6 py-3 font-semibold transition-colors hover:border-[#115e59] dark:border-white/15">
                                Read the guide
                            </a>
                        </div>
                        <p class="es-punch-muted mt-4 text-sm" data-reveal style="--reveal-delay: 0.2s;">
                            Selling tickets is on every plan. The pass switch is the part that needs Pro.
                        </p>
                    </div>

                    <div class="es-punch-card p-6 sm:p-8" data-reveal="panel" style="--reveal-delay: 0.1s;">
                        <div class="mb-1 flex items-baseline justify-between gap-4">
                            <p class="es-punch-card-ink text-base font-black tracking-tight">10-Class Pass</p>
                            <p class="es-punch-card-muted text-xs font-semibold uppercase tracking-widest">Wheelhouse Yoga</p>
                        </div>
                        <p class="es-punch-card-muted text-xs">Valid 90 days from purchase &middot; admits 1</p>

                        {{-- Ten holes, three punched. Fixed, never random: the page has to render
                             identically on every request for the band-diff verifier to mean anything. --}}
                        <div class="mt-7 grid grid-cols-5 justify-items-center gap-x-3 gap-y-4" role="img" aria-label="A ten-visit punch card with three visits used and seven remaining.">
                            @for ($hole = 1; $hole <= 10; $hole++)
                                <div class="es-punch-hole {{ $hole <= 3 ? 'es-punch-hole-spent' : '' }}"></div>
                            @endfor
                        </div>

                        <div class="es-punch-card-rule mt-7 flex items-end justify-between gap-4 pt-4">
                            <div>
                                <p class="es-punch-card-accent text-2xl font-black leading-none">3 of 10</p>
                                <p class="es-punch-card-muted mt-1 text-xs">visits used</p>
                            </div>
                            <p class="es-punch-card-muted text-xs">One QR code &middot; scan at any covered class</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. Four ways to count                                        -->
        <!-- ============================================================ -->
        <section id="types" class="es-punch-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-punch-mark mb-6" data-reveal aria-hidden="true"><span>01</span></div>
                    <p class="es-punch-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Four types</p>
                    <h2 class="es-balance es-punch-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        One card. <span class="es-punch-accent">Four ways to count it.</span>
                    </h2>
                    <p class="es-punch-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        A pass is an ordinary ticket type with one switch turned on. What you pick next decides how many times it can be used, and nothing else about it changes.
                    </p>
                </div>

                @php
                    // Every row is the product's own wording, from the Subscription type field.
                    $passTypes = [
                        ['Visit pass', 'A set number of visits, which you enter yourself. Each visit is one event-day.', 'Class packs, punch cards, ten-entry bundles', '3 of 10 visits used'],
                        ['Membership', 'Unlimited visits to the covered events until the pass expires. Leave the window blank and it never expires.', 'Monthly and annual memberships', 'Unlimited visits'],
                        ['Festival pass', 'One visit to each covered event. Once an event has been used, that event is spent even if others remain.', 'Multi-day festivals, a conference series', 'One visit per event'],
                        ['Season pass', 'Every occurrence of the recurring event it is sold on, once per date. Offered only when the event repeats.', 'A weekly class or a run of the same show', 'Valid for all dates'],
                    ];
                @endphp
                <div class="grid gap-4 sm:grid-cols-2" data-reveal-group="80">
                    @foreach ($passTypes as [$typeName, $typeBody, $typeFor, $typeCount])
                        <div class="es-punch-panel flex flex-col p-6" data-reveal="panel">
                            <h3 class="es-punch-ink text-lg font-bold">{{ $typeName }}</h3>
                            <p class="es-punch-muted mt-2 text-sm leading-relaxed">{{ $typeBody }}</p>
                            <p class="es-punch-muted mt-4 text-xs uppercase tracking-widest">{{ $typeFor }}</p>
                            <p class="es-punch-accent mt-auto pt-4 text-sm font-semibold">{{ $typeCount }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="es-punch-panel mt-8 p-6" data-reveal="panel">
                    <h3 class="es-punch-ink text-base font-bold">A pass has one stock pool, not one per date</h3>
                    <p class="es-punch-muted mt-2 text-sm leading-relaxed">
                        On an ordinary ticket, the quantity is how many are available on each date. A pass is not tied to a date, so its quantity is a single pool across the whole run: set it to fifty and you sell fifty passes in total, however many events they cover. Leave it blank to sell as many as people will buy.
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. Coverage                                                  -->
        <!-- ============================================================ -->
        <section id="coverage" class="es-punch-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-punch-mark mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                    <p class="es-punch-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Coverage</p>
                    <h2 class="es-balance es-punch-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Decide <span class="es-punch-accent">where it works.</span>
                    </h2>
                    <p class="es-punch-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Coverage always resolves inside the schedule the pass was sold on, so a pass can never be redeemed at another schedule's events.
                    </p>
                </div>

                @php
                    $coverage = [
                        ['All events in this schedule', 'The pass works at every event you run. Events you create later are covered automatically, so a membership does not need editing every time the calendar grows.'],
                        ['All events in a sub-schedule', 'Pick one sub-schedule and the pass works at everything in it. Future events in that sub-schedule are covered automatically too.'],
                        ['Specific events', 'Hand-pick the events from a searchable list. A fixed list, so new events are not added to it - which is the point when the pass is a festival wristband and not a membership.'],
                    ];
                @endphp
                <div class="grid gap-4 md:grid-cols-3" data-reveal-group="90">
                    @foreach ($coverage as [$covName, $covBody])
                        <div class="es-punch-panel flex flex-col p-6" data-reveal="panel">
                            <h3 class="es-punch-ink text-base font-bold">{{ $covName }}</h3>
                            <p class="es-punch-muted mt-2 text-sm leading-relaxed">{{ $covBody }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="es-punch-panel mt-8 p-6" data-reveal="panel">
                    <h3 class="es-punch-ink text-base font-bold">The pass shop</h3>
                    <p class="es-punch-muted mt-2 text-sm leading-relaxed">
                        The tidiest way to sell one is to make a single event just for it - call it Memberships or Class Passes - put the pass on that event, and point its coverage at the classes you actually run. The selling event is a shopfront, not a date in your calendar.
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. Advance booking and the cancellation policy               -->
        <!-- ============================================================ -->
        <section id="booking" class="es-punch-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-punch-mark mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                    <p class="es-punch-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Advance booking</p>
                    <h2 class="es-balance es-punch-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Turn up, <span class="es-punch-accent">or book the date.</span>
                    </h2>
                    <p class="es-punch-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Out of the box a pass is scan-at-the-door. Switch on advance booking and holders reserve specific dates themselves, from the private pass page in their confirmation email.
                    </p>
                </div>

                @php
                    $bookingPoints = [
                        ['One pool of seats', 'Advance bookings and ordinary ticket sales draw on the same capacity, so a full room is a full room. If it seats fifty and thirty holders book ahead, twenty seats are left for everyone else.'],
                        ['Hold some back', 'Cap how many seats holders may take on any one date and the rest stays for walk-ups. Leave it blank for no pass-specific limit.'],
                        ['They book, they cancel', 'The holder sees the upcoming dates with the seats left on each and manages their own. Each booking spends a visit up front, until they run out.'],
                        ['Booked or attended', 'The Subscriptions tab separates a date somebody reserved from one they actually turned up to, and the check-in dashboard shows how many seats are held for tonight.'],
                    ];
                @endphp
                <div class="grid gap-4 sm:grid-cols-2" data-reveal-group="80">
                    @foreach ($bookingPoints as [$bpName, $bpBody])
                        <div class="es-punch-panel flex flex-col p-6" data-reveal="panel">
                            <h3 class="es-punch-ink text-base font-bold">{{ $bpName }}</h3>
                            <p class="es-punch-muted mt-2 text-sm leading-relaxed">{{ $bpBody }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8 grid gap-4 md:grid-cols-2" data-reveal-group="90">
                    <div class="es-punch-panel p-6" data-reveal="panel">
                        <h3 class="es-punch-ink text-base font-bold">A deadline, and what happens after it</h3>
                        <p class="es-punch-muted mt-2 text-sm leading-relaxed">
                            By default a holder can cancel at any time and the visit goes back on the card. With limited seats that invites no-shows, so you can set a deadline - until the event starts, or a fixed number of hours before it - and decide what a late cancellation does. Forfeit still releases the seat to other guests and to the waiting list but keeps the visit spent. Block closes cancellation entirely.
                        </p>
                    </div>
                    <div class="es-punch-panel p-6" data-reveal="panel">
                        <h3 class="es-punch-ink text-base font-bold">Nobody is caught out by it</h3>
                        <p class="es-punch-muted mt-2 text-sm leading-relaxed">
                            The rule shows on the ticket before purchase, beside every booked date on the pass page, and in the booking confirmation email. A late cancellation asks for explicit confirmation before it spends the visit, and any booking can be undone with full credit within fifteen minutes of being made, even past the deadline. When a seat comes free before the event, the waiting list hears about it.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 5. At the door, and the ledger behind it                     -->
        <!-- ============================================================ -->
        <section id="door" class="es-punch-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-punch-mark mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                    <p class="es-punch-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">At the door</p>
                    <h2 class="es-balance es-punch-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        One scan <span class="es-punch-accent">punches the card.</span>
                    </h2>
                    <p class="es-punch-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Because a pass is valid across many events, the scanner asks which one you are checking people into. Set it once for the night and the running count comes back with every scan.
                    </p>
                </div>

                <div class="grid gap-6 lg:grid-cols-2 lg:items-start">
                    <div class="es-punch-card p-6 sm:p-8" data-reveal="panel">
                        <p class="es-punch-card-muted text-xs font-semibold uppercase tracking-widest">Scanning at: Vinyasa, 6:30pm</p>
                        <p class="es-punch-card-ink mt-4 text-2xl font-black leading-tight">Welcome - checked in</p>
                        <p class="es-punch-card-accent mt-1 text-sm font-semibold">4 of 10 visits used</p>
                        <div class="es-punch-perf mt-6 pt-5">
                            <p class="es-punch-card-muted text-xs leading-relaxed">
                                Scanning the same person at the same event again the same day reads "Already checked in today" and spends nothing. Check-in opens 24 hours before the event and closes when it ends.
                            </p>
                        </div>
                    </div>

                    @php
                        $doorPoints = [
                            ['A visit is an event-day, not a scan', 'Double-scanning is harmless. The second read at the same event on the same day is recognised as the same visit, and the counter does not move.'],
                            ['Bring a guest without spending a visit', 'Set how many people the pass admits at each event, holder included. Party size and the visit count are separate, so a ten-visit pass that admits two is ten events for two people, not five.'],
                            // GoogleWalletService::classPayload() leaves a pass's class undated, so the
                            // wallet holds ONE pass for everything it covers. The visit count is not on it.
                            ['One wallet pass, not one per date', 'The pass page and the confirmation email carry an Add to Google Wallet button. It saves as a single Google Wallet pass for every date it is good for, with the same QR code, how many it admits and its valid-until date. The visit count stays on the pass page.'],
                            ['A bad pass is a status, not an alarm', 'Out of visits, expired, wrong event, too early, event finished - each comes back as a plain statement of what is true, with what to do next. Only a problem with the order behind it is an error: unpaid, cancelled, refunded, timed out before it was paid, or held for a payment review.'],
                            ['The visit log', 'The Subscriptions tab lists every paid pass with its holder, its count and its expiry, and opens out into the dates behind it: attended, booked, or forfeited.'],
                        ];
                    @endphp
                    <div class="grid gap-4" data-reveal-group="80">
                        @foreach ($doorPoints as [$dpName, $dpBody])
                            <div class="es-punch-panel p-6" data-reveal="panel">
                                <h3 class="es-punch-ink text-base font-bold">{{ $dpName }}</h3>
                                <p class="es-punch-muted mt-2 text-sm leading-relaxed">{{ $dpBody }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        @include('marketing.partials.pricing-nudge')

        <!-- ============================================================ -->
        <!-- 6. FAQ                                                       -->
        <!-- ============================================================ -->
        @php
            $passFaqs = [
                ['q' => 'Is a pass an auto-renewing subscription?', 'a' => 'No. The buyer pays once and Event Schedule never bills them again. A pass here is a multi-use ticket, not a card kept on file, so when it runs out of visits or reaches its expiry the holder simply buys another. The word subscription is also used for your own Pro or Enterprise plan, which is a different thing entirely and is managed on the Plan tab.'],
                ['q' => 'How many QR codes does a holder get?', 'a' => 'One. A pass is a single redeemable unit - one code with one visit counter - which is why the maximum per order is fixed at one and you never have to set that yourself. Saved to Google Wallet it is still one pass, not one per date; on a selfhosted install that button appears once the operator has set up Google Wallet. Buying passes as gifts means a separate order for each, and a pass cannot share an order with ordinary single-date tickets.'],
                ['q' => 'Does a guest use up one of the visits?', 'a' => 'No. Admissions per event is the number of people who may enter at each event, the holder included, and it is counted separately from the visits. A ten-visit pass that admits two is still ten visits, each of which lets two people in. Extra people do count against the event capacity, so an extra admission is only granted while the date still has a free seat.'],
                ['q' => 'What happens if my schedule drops back to the free plan?', 'a' => 'Passes you already sold keep every setting and the scanner still checks holders in, because taking a sold pass away from the person holding it would be indefensible. What stops is booking dates in advance. The plan is checked when the pass is used, not only when it was sold.'],
                ['q' => 'Can a pass be used at another schedule?', 'a' => 'No. Coverage always resolves inside the schedule the selling event belongs to, so a pass sold by one schedule can never be redeemed at another one\'s events.'],
                ['q' => 'Does it work on an event with allocated seating?', 'a' => 'Yes. Booking ahead gives the holder a real seat, chosen as the best available rather than picked from the map, and it is shown beside the date on their pass page. Cancelling gives that exact seat back. The pool is per price band, and a pass may take a seat in any band.'],
                ['q' => 'Someone forfeited a booking and turned up anyway. What then?', 'a' => 'A forfeited booking never revives. If they scan in, that is a new visit and it is subject to the same limits as any other - on a festival pass, where the one visit for that event is already spent, there is nothing left to spend.'],
                ['q' => 'What do I see about how the passes are being used?', 'a' => 'The Subscriptions tab on the Sales page covers every schedule you own and counts the passes and the visits redeemed across them. Each paid pass is a row with the holder, the type, the count and the expiry, marked Active, Used up or Expired, and it expands into the visit log.'],
                // A partial refund leaves Sale.status paid, so the pass keeps working. A full refund
                // releases its bookings (PassBookingService) and drops it off the paid-only
                // Subscriptions tab (TicketController::getSubscriptionsData).
                ['q' => 'Can I refund a pass?', 'a' => 'Yes, from the Sales page. A pass bought through Stripe or PayPal is refunded through the provider, in full or in part, and a partial refund leaves the pass working. A full refund ends it: the scanner reports it as refunded, any dates it had booked are released, and it drops off the Subscriptions tab with its visit log, so export first if you need the history. For any other payment method, Mark as Refunded records it and you return the money yourself.'],
                ['q' => 'Which plan do I need?', 'a' => 'Passes are on the Pro plan, and on every selfhosted install at no cost. Selling tickets itself is not gated: the free plan sells up to 25 paid tickets a calendar month with no platform fee, and scanning a QR code at the door is free on every plan.'],
            ];
        @endphp
        <section id="faq" class="es-punch-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <h2 class="es-punch-ink mb-10 text-center text-3xl font-black tracking-tight md:text-4xl" data-reveal>Questions</h2>
                <div class="space-y-3" data-reveal-group="60">
                    @foreach ($passFaqs as $faq)
                        <details class="es-punch-panel group p-5" data-reveal="panel">
                            <summary class="es-punch-ink flex cursor-pointer items-center justify-between gap-4 text-base font-semibold">
                                {{ $faq['q'] }}
                                <svg class="h-5 w-5 shrink-0 transition-transform group-open:rotate-45" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                                </svg>
                            </summary>
                            <p class="es-punch-muted mt-3 text-sm leading-relaxed">{{ $faq['a'] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>

        <x-seo.faq-schema :items="$passFaqs" />

        <!-- ============================================================ -->
        <!-- 7. Claim                                                     -->
        <!-- ============================================================ -->
        <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
            <div class="mx-auto max-w-6xl">
                <div class="es-punch-band noise relative overflow-hidden rounded-[2rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-20" data-reveal="panel">
                    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                        <div class="grid-overlay absolute inset-0 opacity-25"></div>
                    </div>

                    <div class="relative z-10">
                        <p class="es-punch-band-tag mb-6">Free to start</p>
                        <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                            Stop selling <span class="es-punch-band-grad">one night at a time</span>.
                        </h2>
                        <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                            One card, paid for once, that brings somebody back nine more times.
                        </p>

                        <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                            <label for="es-claim-input" class="sr-only">Your schedule name</label>
                            <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-lg border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                                <input id="es-claim-input" type="text" placeholder="your-studio" autocomplete="off" spellcheck="false" maxlength="30"
                                    class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                                <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                            </div>
                            <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg bg-white px-8 py-4 text-lg font-semibold text-[#0d1418] transition-colors hover:bg-gray-100">
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
