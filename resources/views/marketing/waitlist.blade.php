<x-marketing-layout :hp="true">
    <x-slot name="title">Ticket Waitlist for Sold-Out Events | One Offer at a Time</x-slot>
    <x-slot name="description">When a date sells out, guests join a waitlist. Each returned seat is offered to one person at a time, a day each. How to set one up and read the queue.</x-slot>
    <x-slot name="breadcrumbTitle">Waitlist</x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule - Ticket Waitlist"
        description="A waitlist that opens automatically when an event date sells out, notifying one person at a time with a 24-hour window so a returned seat can never be oversold." />
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
                        <h1 class="es-balance es-queue-ink text-4xl font-black tracking-tight md:text-6xl" data-reveal style="--reveal-delay: 0.05s;">
                            <x-marketing.hero-eyebrow class="block es-queue-tag mb-4">Ticket waitlist &middot; free on registration, Pro on tickets</x-marketing.hero-eyebrow>
                            Sold out is not <span class="es-queue-accent">the end of it.</span>
                        </h1>
                        <p class="es-queue-muted mt-6 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                            Seats come back. A card is declined, an order is refunded, a pass holder cancels the night before. When one does, the next person on your waitlist is emailed, not all of them, and has a day's head start before the offer passes on.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3" data-reveal style="--reveal-delay: 0.15s;">
                            <a href="{{ app_url('/sign_up?intent=sell') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#166534] px-6 py-3 font-semibold text-white transition-colors hover:bg-[#12522a]">
                                Start for free
                            </a>
                            <a href="{{ marketing_url('/docs/tickets') }}#waitlist" class="es-queue-ink inline-flex items-center gap-2 rounded-xl border border-gray-300 px-6 py-3 font-semibold transition-colors hover:border-[#166534] dark:border-white/15">
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
                                One offer is live at a time. If it is not taken inside 24 hours it passes to position two. Nobody else on the list is emailed in the meantime.
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
                        ['A sale is refunded in full', 'A full refund releases what the order was holding, so the seat is back on sale as soon as the refund goes through. A partial refund leaves the ticket valid, so it frees nothing.'],
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
                    <p class="es-queue-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">How the waitlist works</p>
                    <h2 class="es-balance es-queue-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        One person, <span class="es-queue-accent">one seat, one day.</span>
                    </h2>
                    <p class="es-queue-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        The alternative - emailing everybody and letting them race - is how thirty-nine people are told about a seat that has already gone.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-3" data-reveal-group="90">
                    <div class="es-queue-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-queue-ink text-base font-bold">Only the next in line hears</h3>
                        <p class="es-queue-muted mt-2 text-sm leading-relaxed">
                            When a place frees up, one email goes out, to one person, carrying a link that will let them buy it. Nobody else on the list is emailed about it yet.
                        </p>
                    </div>
                    <div class="es-queue-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-queue-ink text-base font-bold">Each offer runs for 24 hours</h3>
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
                        A Waitlist tab appears on the Sales page as soon as there is one entry, listing each person's name and email with the event, the date, their status and when they joined, which is their place in the line. It is the demand that did not fit, in writing - which is worth reading before you decide whether to add a second night.
                    </p>
                </div>
                <div class="mt-10 text-center">
                    <a href="{{ marketing_url('/features') }}" class="es-queue-accent inline-flex items-center font-medium hover:underline">
                        See all features
                        <svg aria-hidden="true" class="ml-1 h-4 w-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. Before the date sells out                                 -->
        <!-- ============================================================ -->
        <section id="setup" class="es-queue-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-queue-mark mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                    <p class="es-queue-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">How to set up a ticket waitlist</p>
                    <h2 class="es-balance es-queue-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Six things to settle <span class="es-queue-accent">before you sell out.</span>
                    </h2>
                    <p class="es-queue-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        A waitlist only works if the date can really fill up, the seats that come back really come back, and the people in the queue know what they signed up for. Each of those is a setting or a sentence you decide now, not on the night.
                    </p>
                </div>

                @php
                    $queueSetup = [
                        ['Give every ticket type a limit', 'The Join Waitlist button appears only when the date has no seat left: every ticket type used up, or the shared total reached if your types draw on one number. Leave a single type unlimited and the date can never sell out, so nobody can ever join.', [marketing_url('/docs/tickets').'#combined-total', 'Shared totals in the guide']],
                        ['Keep sales open until the night', 'A full date whose ticket sales have ended shows as ended, not as a waitlist, because a seat that came back could not be sold anyway. If you close sales a day early, the queue closes with them. Close sales only when you truly stop taking people.', null],
                        ['Release unpaid orders on a timer', 'An order nobody pays still holds its seats. Turn on Expire unpaid tickets in the Sales rules row and enter a number of hours: a few for card payments, longer when buyers pay an invoice. Every order that lapses puts its seats back and wakes the queue.', [marketing_url('/docs/tickets').'#sales-rules', 'Sales rules in the guide']],
                        ['Cancel the orders you know are empty', 'When a buyer writes to say they cannot come, cancel the order or refund it in full. That frees the seat and the next person in line is emailed. A partial refund leaves the ticket valid, so it frees nothing.', [marketing_url('/docs/tickets').'#refunds', 'Refunds in the guide']],
                        ['Say what joining means', 'Put one line in the event description: joining is not a ticket, people are told in the order they joined, and a returned seat is first come, first served once they are told. A guest who knew that does not write to you angry.', null],
                        ['Check the plan for the kind of event', 'A full free registration date offers the waitlist on every plan, Free included. A sold-out ticketed date needs Pro. On a selfhosted install both are included at no cost.', [marketing_url('/pricing'), 'Compare plans']],
                    ];
                @endphp
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">
                    @foreach ($queueSetup as $qsIndex => [$qsName, $qsBody, $qsLink])
                        <div class="es-queue-panel flex flex-col p-6" data-reveal="panel">
                            <p class="es-queue-tag mb-3">Step {{ $qsIndex + 1 }}</p>
                            <h3 class="es-queue-ink text-base font-bold">{{ $qsName }}</h3>
                            <p class="es-queue-muted mt-2 text-sm leading-relaxed">{{ $qsBody }}</p>
                            @if ($qsLink)
                                <a href="{{ $qsLink[0] }}" class="es-queue-accent mt-auto pt-4 text-sm font-semibold hover:underline">{{ $qsLink[1] }}</a>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="es-queue-board mt-8 p-6 sm:p-8" data-reveal="panel">
                    <p class="es-queue-board-muted text-xs font-semibold uppercase tracking-widest">A line to copy into a sold-out event's description</p>
                    <p class="es-queue-board-ink mt-4 text-base leading-relaxed">
                        "Sold out? Join the waitlist with your name and email. If a seat comes back, we email the next person in line, in the order people joined. The seat is then first come, first served, so buy as soon as you see the email. After about a day, the next person is told."
                    </p>
                    <p class="es-queue-board-muted es-queue-board-rule mt-6 pt-4 text-xs leading-relaxed">
                        Why that wording: the email a guest gets when it is their turn says a place may now be free and asks them to act quickly. It does not reserve the seat, and your description should not promise that it does.
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 5. The clock and late returns                                -->
        <!-- ============================================================ -->
        <section id="clock" class="es-queue-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-queue-mark mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                    <p class="es-queue-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">How long a waitlist offer lasts</p>
                    <h2 class="es-balance es-queue-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        A day per person, <span class="es-queue-accent">so late seats travel less far.</span>
                    </h2>
                    <p class="es-queue-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Each offer runs for 24 hours before it passes on (the check runs every hour, so it can be a little over). That is fair to the person at the front, and it has a cost you can count: the closer to the night a seat comes back, the fewer people in the queue it can reach if nobody answers.
                    </p>
                </div>

                @php
                    // Worst case, nobody answers: offers go out at the return and then about every
                    // 24 to 25 hours until doors. 7 days = 168h -> 7 offers; 72h -> 3; 36h -> 2; 6h -> 1.
                    $queueClock = [
                        ['7', 'A week before', 'Seven people can be offered it in turn before doors, even if the first six let it go.'],
                        ['3', 'Three days before', 'Three people at most. A long queue for a Saturday show barely moves in the last half of the week.'],
                        ['2', 'The night before', 'Thirty-six hours out, the second offer goes out with about half a day to spare.'],
                        ['1', 'The afternoon of the show', 'One person is told. If they miss the email, the seat sits on the event page for whoever finds it.'],
                    ];
                @endphp
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group="80">
                    @foreach ($queueClock as [$qcNum, $qcWhen, $qcBody])
                        <div class="es-queue-panel flex flex-col p-6" data-reveal="panel">
                            <p class="es-queue-accent text-4xl font-black tabular-nums">{{ $qcNum }}</p>
                            <h3 class="es-queue-ink mt-2 text-base font-bold">{{ $qcWhen }}</h3>
                            <p class="es-queue-muted mt-2 text-sm leading-relaxed">{{ $qcBody }}</p>
                        </div>
                    @endforeach
                </div>
                <p class="es-queue-muted mx-auto mt-6 max-w-3xl text-center text-sm">
                    Figures are the most people an unanswered seat can be offered to, one after another, before the doors open.
                </p>

                <div class="mt-8 grid gap-4 md:grid-cols-2" data-reveal-group="90">
                    <div class="es-queue-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-queue-ink text-base font-bold">An offer is a head start, not a hold</h3>
                        <p class="es-queue-muted mt-2 text-sm leading-relaxed">
                            The moment a seat comes back, the date is on sale again, so somebody who happens to open the event page can buy it before the person who was emailed. Checkout counts stock, so the seat is never sold twice. If a passer-by takes it, the queue simply waits: when the emailed person's day runs out, the next in line is told only if the date still has room.
                        </p>
                    </div>
                    <div class="es-queue-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-queue-ink text-base font-bold">In the last day, people beat the queue</h3>
                        <p class="es-queue-muted mt-2 text-sm leading-relaxed">
                            Because the final offers have so little time to travel, the last seats of a sold-out night tend to go to whoever is watching the page. If you would rather they went to the queue, ask buyers to cancel early: a cancellation a week out reaches up to seven people, one on the day reaches one.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 6. Reading the queue                                         -->
        <!-- ============================================================ -->
        <section id="demand" class="es-queue-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-queue-mark mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                    <p class="es-queue-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">What a waitlist tells you</p>
                    <h2 class="es-balance es-queue-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        A long queue is a reason <span class="es-queue-accent">to add a night.</span>
                    </h2>
                    <p class="es-queue-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Most of the people on a waitlist will never be offered a seat, because only a handful come back. The list is more useful as a measure: it counts the demand you turned away, by date, with an email address for each person.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-3" data-reveal-group="90">
                    <div class="es-queue-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-queue-ink text-base font-bold">An entry is a person, not a seat</h3>
                        <p class="es-queue-muted mt-2 text-sm leading-relaxed">
                            Joining asks for a name and an email, never a number of tickets, and one address can be on a date's list once. Somebody who joins usually wants seats for the people they would have come with. If your typical order is two tickets, read forty entries as up to about eighty seats.
                        </p>
                    </div>
                    <div class="es-queue-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-queue-ink text-base font-bold">Size the extra night below the list</h3>
                        <p class="es-queue-muted mt-2 text-sm leading-relaxed">
                            Joining costs nothing and plans change, so not everyone will buy when the new date opens. A second night is safer sized at a fraction of what the queue suggests, in a smaller room or with fewer tickets released, and grown if it sells.
                        </p>
                    </div>
                    <div class="es-queue-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-queue-ink text-base font-bold">Tell the queue first</h3>
                        <p class="es-queue-muted mt-2 text-sm leading-relaxed">
                            Newsletters have a Waitlist segment, on every plan, that can be narrowed to one event. Send the new date to it before you announce it anywhere else. On the hosted plans, unless you send through your own email settings, each person counts as one email against the monthly newsletter allowance.
                        </p>
                        <a href="{{ marketing_url('/docs/newsletters') }}#managing-segments" class="es-queue-accent mt-auto pt-4 text-sm font-semibold hover:underline">Segments in the guide</a>
                    </div>
                </div>

                <div class="es-queue-panel mt-8 p-6 sm:p-8" data-reveal="panel">
                    <h3 class="es-queue-ink text-base font-bold">Keeping the list clean</h3>
                    <ul class="es-queue-muted mt-3 list-disc space-y-2 pl-5 text-sm leading-relaxed">
                        <li>When somebody on a ticketed date's list pays for a ticket to that date with the same email address, their entry is marked Purchased by itself, so the queue does not offer them a second seat.</li>
                        <li>A person who asks to be taken off can be removed with Remove at the end of their row, after a confirmation.</li>
                        <li>A recurring event keeps one queue per date. Adding a new date starts an empty queue for it, which is another reason to write to the old one.</li>
                        <li>Thirty days after a date, its waitlist entries are deleted as personal data. Write to those people before then if you want them for the next run, or point them at <x-link href="{{ marketing_url('/features/recurring-events') }}">the dates you add</x-link> while the list still exists.</li>
                    </ul>
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
                ['q' => 'How is this different from "Tell me when tickets go on sale"?', 'a' => 'That is the interest list, and it does a different job. The waitlist is for a date that has sold out: it queues people for a seat that comes back and offers it to one of them at a time, free on registration events and Pro on ticketed ones. The interest list is for an event whose tickets are not on sale yet, or for someone who only wants to hear if it changes. It takes an email address and nothing else, then sends one email when tickets go on sale, one if the event is cancelled and a reminder before it starts, plus any notice you choose to send if the date or venue changes. It is free on every plan, off until you switch on the "Notify me" card, and it never offers anyone a seat.', 'link' => [marketing_url('/docs/tickets').'#interest-list', 'How the interest list works']],
                ['q' => 'Is it per event or per date?', 'a' => 'Per date. A recurring night that is full on Friday and half empty on Saturday offers the waitlist on Friday only, because Friday is the thing that sold out.'],
                ['q' => 'What stops two people buying the same returned seat?', 'a' => 'Checkout counts stock, so a seat can only ever be sold once. The waitlist adds the courtesy: one person is emailed when a place comes back, and the next in line is told only after that offer has run out and the date still has room, rather than everyone being emailed at once and most of them finding the seat gone.'],
                ['q' => 'How long does someone have?', 'a' => 'Twenty-four hours from the email before the offer passes to the next person, automatically, with nothing for you to chase or re-send. The seat is not locked to them in that time: the date is back on sale, so somebody visiting the event page could buy it first. That is why the email asks them to act quickly.'],
                ['q' => 'Does a refunded ticket wake the list?', 'a' => 'A full refund does. A cancelled sale, a fully refunded sale and an unpaid order that expires all put the place back and offer it on, and so does a pass holder cancelling a date they had booked in advance. A partial refund leaves the ticket valid, so the seat is still taken and nobody is offered it.'],
                ['q' => 'Can I see who is waiting?', 'a' => 'Yes, on the Waitlist tab of the Sales page, which appears as soon as there is one entry. It shows each person with their event, date and status, so you know how much demand you turned away.'],
                ['q' => 'Does joining a waitlist reserve a ticket?', 'a' => 'No. Joining puts a name and an email address in the queue for one date, and nothing is charged or held. When a place comes back, the next person is emailed a link to the event page and buys there like anyone else, at the price the ticket has then.'],
                ['q' => 'Can someone ask for more than one ticket from the waitlist?', 'a' => 'The waitlist does not ask how many. When it is their turn they open the event page and can buy as many as are free, within the limits you set on the event. If one cancelled order returned four seats and they want two, the other two stay on sale on the event page.'],
                ['q' => 'Should I keep a waitlist or add another date?', 'a' => 'Both, usually. The waitlist sells the few seats that come back, which on a typical sold-out night is a handful. When the queue for one date is many times longer than that, the demand is there for a second date: add it, write to the Waitlist segment first, and size it below the length of the list, because not everyone who joined will buy.', 'link' => [marketing_url('/features/recurring-events'), 'Adding dates to a series']],
                ['q' => 'What happens to the waitlist after the event?', 'a' => 'Thirty days after the date, its waitlist entries are deleted, because they are personal data collected for one night. Until then the people still waiting can be seen with Include past events on the Waitlist tab, and written to with a newsletter, so contact them before the thirty days run out if you want them for the next run.'],
                ['q' => 'Which plan do I need?', 'a' => 'On a free registration event the waitlist works on every plan, including Free. On a ticketed event it is on the Pro plan, and on every selfhosted install at no cost.'],
            ];
        @endphp
        <section id="faq" class="es-queue-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <h2 class="es-queue-ink mb-10 text-center text-3xl font-black tracking-tight md:text-4xl" data-reveal>Waitlist questions</h2>
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
                            @if (! empty($faq['link']))
                                <a href="{{ $faq['link'][0] }}" class="es-queue-accent mt-3 inline-block text-sm font-semibold hover:underline">{{ $faq['link'][1] }}</a>
                            @endif
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
                            <a href="{{ app_url('/sign_up?intent=sell') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg bg-white px-8 py-4 text-lg font-semibold text-[#0e1411] transition-colors hover:bg-gray-100">
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
