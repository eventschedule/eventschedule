<x-marketing-layout>
    <x-slot name="title">Ticket Waitlist | Sell the Seat That Comes Back</x-slot>
    <x-slot name="description">When a date sells out, guests join a waitlist. A returned seat is offered to one person at a time, with a 24-hour window, so it is never promised to two people.</x-slot>
    <x-slot name="breadcrumbTitle">Waitlist</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Ticket Waitlist",
        "description": "A waitlist that opens automatically when an event date sells out, notifying one person at a time with a 24-hour window so a returned seat can never be oversold.",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "featureList": [
            "A Join Waitlist button appears automatically when a date sells out",
            "Guests join with a name and an email address",
            "A cancelled, refunded or expired sale frees a place",
            "Only one person is notified at a time",
            "The offer holds for 24 hours, then passes on",
            "A pass holder cancelling a booked date frees a place too",
            "Waitlist tab listing every entry, event, date and status",
            "Free on registration events, Pro on ticketed ones"
        ],
        "offers": {
            "@type": "Offer",
            "price": "{{ $proMonthly }}",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Free on registration events; the ticketed waitlist is on the Pro plan"
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
           Waitlist "Now Serving" styles.

           CONCEPT: THE COUNTER THAT SERVES ONE PERSON AT A TIME. A
           waitlist looks like a list and behaves like a QUEUE, and the
           whole engineering decision behind it is that exactly one
           person holds the offer at any moment. Announce a returned seat
           to forty people and thirty-nine of them find it gone; announce
           it to one and nobody is disappointed and nothing is oversold.
           So the page is drawn as a counter with a position showing:
           one name lit, the rest waiting their turn, and a clock on the
           one that is lit.

           WHY NOT A LIST. A list has no head. Drawing this as rows of
           equal weight would picture a broadcast, which is exactly the
           product this is NOT, and would make the page argue against
           its own mechanism.

           DELIBERATELY NOT: /for-spoken-word owns "The Sign-Up Sheet"
           (a clipboard of names, all equal, no queue);
           /for-nightclubs owns "The Door" and the rope;
           /features/check-in owns the ops screen and the live count;
           /features/passes owns the punch card.

           THE BOARD IS A PHYSICAL OBJECT - a counter display is the same
           display whatever the light in the room - so .es-queue-board is
           FIXED in both colour modes.

           COLOUR: green, and the semantics come first: green is the seat
           coming BACK, which is the only good news this feature has to
           deliver. Green-800 is chosen over the emerald
           /features/analytics spends (#047857) and the heritage green
           /for-theaters spends (#14532d); neither page is reachable from
           this one and neither appears in its related strip. Position is
           carried by SHAPE and NUMBER as well as colour - the served
           position is a filled chip with a countdown, the rest are
           hollow - so the queue reads with no colour at all.

           Measured against the grounds this page actually paints:
             light ground #f4f6f4: ink #0f1512 17.02, muted #4b5563 6.96,
                                   accent #166534 6.57
             dark ground  #0a0d0b: ink #e6ebe7 16.18, muted #9aa39d 7.53,
                                   accent #86efac 13.91
             board #121714 (fixed): ink #e6ebe7 15.02, muted #949d97 6.50,
                                   accent #86efac 12.91
           text-gray-500 is never used on the tinted ground. Use
           .es-queue-muted.
           ============================================================== */

        .es-queue-page { background-color: #f4f6f4; color: #0f1512; }
        .dark .es-queue-page { background-color: #0a0d0b; color: #e6ebe7; }

        .es-queue-ink { color: #0f1512; }
        .dark .es-queue-ink { color: #e6ebe7; }
        .es-queue-muted { color: #4b5563; }
        .dark .es-queue-muted { color: #9aa39d; }
        .es-queue-accent { color: #166534; }
        .dark .es-queue-accent { color: #86efac; }

        .es-queue-rule { border-top: 1px solid rgba(15, 21, 18, 0.10); }
        .dark .es-queue-rule { border-top-color: rgba(230, 235, 231, 0.10); }

        .es-queue-tag {
            display: inline-block;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #166534;
        }
        .dark .es-queue-tag { color: #86efac; }

        .es-queue-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.75rem;
            border: 1.5px solid rgba(22, 101, 52, 0.35);
            font-size: 0.8125rem;
            font-weight: 800;
            color: #166534;
        }
        .dark .es-queue-mark { border-color: rgba(134, 239, 172, 0.35); color: #86efac; }

        .es-queue-panel {
            background-color: #ffffff;
            border: 1px solid rgba(15, 21, 18, 0.10);
            border-radius: 1rem;
        }
        .dark .es-queue-panel {
            background-color: rgba(255, 255, 255, 0.04);
            border-color: rgba(230, 235, 231, 0.10);
        }

        /* ---- THE BOARD. Fixed in both modes; see the contract above. ---- */
        .es-queue-board {
            background-color: #121714;
            color: #e6ebe7;
            border: 1px solid rgba(255, 255, 255, 0.09);
            border-radius: 1rem;
            box-shadow: 0 18px 45px rgba(6, 9, 7, 0.45);
        }
        .es-queue-board-ink { color: #e6ebe7; }
        .es-queue-board-muted { color: #949d97; }
        .es-queue-board-accent { color: #86efac; }
        .es-queue-board-rule { border-top: 1px solid rgba(255, 255, 255, 0.09); }

        /* A position in the queue. The one being served is a filled chip; the rest
           are hollow, so the head of the queue reads on a mono screen too. */
        .es-queue-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.9rem;
            height: 1.9rem;
            flex: none;
            border-radius: 0.55rem;
            border: 1.5px solid rgba(230, 235, 231, 0.30);
            font-size: 0.75rem;
            font-weight: 800;
            font-variant-numeric: tabular-nums;
            color: #949d97;
        }
        .es-queue-chip-now {
            background-color: #86efac;
            border-color: #86efac;
            color: #0f1512;
        }

        .es-queue-band { background-color: #0e1411; }
        .es-queue-band-tag {
            display: inline-block;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #86efac;
        }
        .es-queue-band-grad {
            background-image: linear-gradient(90deg, #86efac, #7dd3fc);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        /* Shared classes carry their own .dark rules in marketing.css and would
           otherwise change inside a band that has none. */
        .es-queue-band .grid-overlay { background-image:
            linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px); }
        .es-queue-band .es-claim:focus-within { border-color: rgba(134, 239, 172, 0.55); }

        /* marketing.css:248 only rings a.feature-card|bento-card|persona-card. */
        #es-queue-page a:focus-visible,
        #es-queue-page summary:focus-visible,
        #es-queue-page button:focus-visible {
            outline: 2px solid #166534;
            outline-offset: 2px;
        }
        .dark #es-queue-page a:focus-visible,
        .dark #es-queue-page summary:focus-visible,
        .dark #es-queue-page button:focus-visible { outline-color: #86efac; }
    </style>

    <div id="es-queue-page" class="es-queue-page">

        <!-- ============================================================ -->
        <!-- 1. Hero: the board                                           -->
        <!-- ============================================================ -->
        <section id="top" class="relative scroll-mt-24 overflow-hidden py-16 lg:py-24">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="grid items-center gap-12 lg:grid-cols-2">
                    <div>
                        <p class="es-queue-tag mb-4" data-reveal>Waitlist &middot; free on registration, Pro on tickets</p>
                        <h1 class="es-balance es-queue-ink text-4xl font-black tracking-tight md:text-6xl" data-reveal style="--reveal-delay: 0.05s;">
                            Sold out is not <span class="es-queue-accent">the end of it.</span>
                        </h1>
                        <p class="es-queue-muted mt-6 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                            Seats come back. A card is declined, an order is refunded, a pass holder cancels the night before. When one does, it goes to one person on your waitlist - not to all of them - and they get a day to take it.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3" data-reveal style="--reveal-delay: 0.15s;">
                            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#166534] px-6 py-3 font-semibold text-white transition-colors hover:bg-[#12522a]">
                                Start for free
                            </a>
                            <a href="{{ marketing_url('/docs/tickets') }}" class="es-queue-ink inline-flex items-center gap-2 rounded-xl border border-gray-300 px-6 py-3 font-semibold transition-colors hover:border-[#166534] dark:border-white/15">
                                Read the guide
                            </a>
                        </div>
                        <p class="es-queue-muted mt-4 text-sm" data-reveal style="--reveal-delay: 0.2s;">
                            On a full registration date the waitlist works on every plan, including Free.
                        </p>
                    </div>

                    @php
                        // Fixed, never random: the page has to render identically on every request
                        // for the band-diff verifier to mean anything.
                        $queuePositions = [
                            ['now', '1', 'R. Achebe', 'Offered - 23h left to take it'],
                            ['wait', '2', 'M. Halvorsen', 'Next, if it is not taken'],
                            ['wait', '3', 'T. Osei', 'Waiting'],
                            ['wait', '4', 'J. Lindqvist', 'Waiting'],
                        ];
                    @endphp
                    <div class="es-queue-board p-6 sm:p-8" data-reveal="panel" style="--reveal-delay: 0.1s;">
                        <div class="flex items-baseline justify-between gap-4">
                            <p class="es-queue-board-muted text-xs font-semibold uppercase tracking-widest">Waitlist &middot; Fri 12 Dec</p>
                            <p class="es-queue-board-accent text-xs font-semibold">1 seat returned</p>
                        </div>

                        <ol class="mt-6 space-y-4">
                            @foreach ($queuePositions as [$qState, $qNum, $qName, $qNote])
                                <li class="flex items-start gap-3">
                                    <span class="es-queue-chip {{ $qState === 'now' ? 'es-queue-chip-now' : '' }}" aria-hidden="true">{{ $qNum }}</span>
                                    <span class="min-w-0">
                                        <span class="{{ $qState === 'now' ? 'es-queue-board-ink' : 'es-queue-board-muted' }} block text-sm font-semibold">{{ $qName }}</span>
                                        <span class="{{ $qState === 'now' ? 'es-queue-board-accent' : 'es-queue-board-muted' }} block text-xs">{{ $qNote }}</span>
                                    </span>
                                </li>
                            @endforeach
                        </ol>

                        <div class="es-queue-board-rule mt-6 pt-4">
                            <p class="es-queue-board-muted text-xs leading-relaxed">
                                One offer is live at a time. If it is not taken inside 24 hours it passes to position two, and the seat was never promised to anyone else in the meantime.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. How a seat comes back                                     -->
        <!-- ============================================================ -->
        <section id="returns" class="es-queue-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-queue-mark mb-6" data-reveal aria-hidden="true"><span>01</span></div>
                    <p class="es-queue-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The returns</p>
                    <h2 class="es-balance es-queue-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        More seats come back <span class="es-queue-accent">than you think.</span>
                    </h2>
                    <p class="es-queue-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        A sold-out date is not a fixed thing. Four ordinary events put a place back into the room, and each one wakes the queue on its own.
                    </p>
                </div>

                @php
                    $queueReturns = [
                        ['A sale is cancelled', 'Somebody changes their mind, or you cancel an order yourself. The place goes straight back into the count.'],
                        ['A sale is refunded', 'A refund releases what it was holding, so the seat is available again the moment the money goes back.'],
                        ['An unpaid order expires', 'A checkout begun and abandoned holds its seats only for as long as your release window allows. When it lapses, the seats return.'],
                        ['A pass holder cancels a date', 'Where a pass books dates in advance, cancelling one hands the place back - and that includes a late cancellation that forfeits the visit, because the seat is more useful to somebody else than the credit is to them.'],
                    ];
                @endphp
                <div class="grid gap-4 sm:grid-cols-2" data-reveal-group="80">
                    @foreach ($queueReturns as [$qrName, $qrBody])
                        <div class="es-queue-panel flex flex-col p-6" data-reveal="panel">
                            <h3 class="es-queue-ink text-base font-bold">{{ $qrName }}</h3>
                            <p class="es-queue-muted mt-2 text-sm leading-relaxed">{{ $qrBody }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. One at a time                                             -->
        <!-- ============================================================ -->
        <section id="order" class="es-queue-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-queue-mark mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                    <p class="es-queue-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The rule</p>
                    <h2 class="es-balance es-queue-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        One person, <span class="es-queue-accent">one seat, one day.</span>
                    </h2>
                    <p class="es-queue-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        The alternative - emailing everybody and letting them race - is how a sold-out night gets oversold and how thirty-nine people are told about a seat that has already gone.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-3" data-reveal-group="90">
                    <div class="es-queue-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-queue-ink text-base font-bold">Only the next in line hears</h3>
                        <p class="es-queue-muted mt-2 text-sm leading-relaxed">
                            When a place frees up, one email goes out, to one person, carrying a link that will let them buy it. Nobody else is told the seat exists yet.
                        </p>
                    </div>
                    <div class="es-queue-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-queue-ink text-base font-bold">The offer holds for 24 hours</h3>
                        <p class="es-queue-muted mt-2 text-sm leading-relaxed">
                            Long enough for somebody at work to see it and decide, short enough that the seat is not still sitting there on the night. When the window closes without a purchase, the offer moves on by itself.
                        </p>
                    </div>
                    <div class="es-queue-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-queue-ink text-base font-bold">Then it passes on</h3>
                        <p class="es-queue-muted mt-2 text-sm leading-relaxed">
                            Position two becomes position one and the same thing happens again, until somebody takes it. The queue drains in the order people joined it, without anyone needing to run it.
                        </p>
                    </div>
                </div>

                <div class="es-queue-panel mt-8 p-6" data-reveal="panel">
                    <h3 class="es-queue-ink text-base font-bold">You can see the whole queue</h3>
                    <p class="es-queue-muted mt-2 text-sm leading-relaxed">
                        A Waitlist tab appears on the Sales page as soon as there is one entry, listing each person's name and email with the event, the date and where they are in the line. It is the demand that did not fit, in writing - which is worth reading before you decide whether to add a second night.
                    </p>
                </div>
            </div>
        </section>

        @include('marketing.partials.pricing-nudge')

        <!-- ============================================================ -->
        <!-- 4. FAQ                                                       -->
        <!-- ============================================================ -->
        @php
            $waitlistFaqs = [
                ['q' => 'Do I have to switch the waitlist on?', 'a' => 'No. Once a date has sold out, the Join Waitlist button takes the place of the buy button on that event page by itself. Guests give a name and an email, and that is the whole sign-up.'],
                ['q' => 'Is it per event or per date?', 'a' => 'Per date. A recurring night that is full on Friday and half empty on Saturday offers the waitlist on Friday only, because Friday is the thing that sold out.'],
                ['q' => 'What stops two people buying the same returned seat?', 'a' => 'Only one person is ever notified at a time. The next in line is told only after the current offer is either taken or expires, which is the whole reason the feature works this way rather than emailing everyone at once.'],
                ['q' => 'How long does someone have?', 'a' => 'Twenty-four hours from the email. If they have not bought by then the offer passes to the next person automatically - there is nothing for you to chase or re-send.'],
                ['q' => 'Does a refunded ticket wake the list?', 'a' => 'Yes. A cancelled sale, a refunded sale and an unpaid order that expires all put the place back and offer it on. So does a pass holder cancelling a date they had booked in advance.'],
                ['q' => 'Can I see who is waiting?', 'a' => 'Yes, on the Waitlist tab of the Sales page, which appears as soon as there is one entry. It shows each person with their event, date and status, so you know how much demand you turned away.'],
                ['q' => 'Which plan do I need?', 'a' => 'On a free registration event the waitlist works on every plan, including Free. On a ticketed event it is on the Pro plan, and on every selfhosted install at no cost.'],
            ];
        @endphp
        <section id="faq" class="es-queue-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <h2 class="es-queue-ink mb-10 text-center text-3xl font-black tracking-tight md:text-4xl" data-reveal>Questions</h2>
                <div class="space-y-3" data-reveal-group="60">
                    @foreach ($waitlistFaqs as $faq)
                        <details class="es-queue-panel group p-5" data-reveal="panel">
                            <summary class="es-queue-ink flex cursor-pointer items-center justify-between gap-4 text-base font-semibold">
                                {{ $faq['q'] }}
                                <svg class="h-5 w-5 shrink-0 transition-transform group-open:rotate-45" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                                </svg>
                            </summary>
                            <p class="es-queue-muted mt-3 text-sm leading-relaxed">{{ $faq['a'] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>

        <x-seo.faq-schema :items="$waitlistFaqs" />

        <!-- ============================================================ -->
        <!-- 5. Claim                                                     -->
        <!-- ============================================================ -->
        <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
            <div class="mx-auto max-w-6xl">
                <div class="es-queue-band noise relative overflow-hidden rounded-[2rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-20" data-reveal="panel">
                    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                        <div class="grid-overlay absolute inset-0 opacity-25"></div>
                    </div>

                    <div class="relative z-10">
                        <p class="es-queue-band-tag mb-6">Free to start</p>
                        <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                            Keep the queue <span class="es-queue-band-grad">outside the door</span>.
                        </h2>
                        <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                            Sell the seat that comes back, to the person who asked for it first.
                        </p>

                        <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                            <label for="es-claim-input" class="sr-only">Your schedule name</label>
                            <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-lg border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                                <input id="es-claim-input" type="text" placeholder="your-venue" autocomplete="off" spellcheck="false" maxlength="30"
                                    class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                                <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                            </div>
                            <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg bg-white px-8 py-4 text-lg font-semibold text-[#0e1411] transition-colors hover:bg-gray-100">
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
