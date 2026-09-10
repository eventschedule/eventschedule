<x-marketing-layout>
    <x-slot name="title">QR Ticket Check-in | Free at the Door, Live Count on Pro</x-slot>
    <x-slot name="description">Scan ticket QR codes at the door free on every plan. On Pro, watch the room fill live: overall progress, each ticket type and the last ten arrivals.</x-slot>
    <x-slot name="breadcrumbTitle">Check-in</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Check-in Dashboard",
        "description": "Live attendance tracking at the door: QR scanning on every plan, plus a Pro dashboard with an overall progress bar, a per-ticket-type breakdown and a recent-arrivals feed.",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": ["Web", "Android", "iOS"],
        "featureList": [
            "QR code scanning at the door on every plan",
            "Unpaid, cancelled, fully refunded and expired orders refused at the scan",
            "Live overall progress with the percentage checked in",
            "Per-ticket-type breakdown of who has arrived",
            "Recent activity feed of the last ten arrivals with times",
            "Seat shown beside the name on an allocated event",
            "Headcount including guests admitted on a pass",
            "Reserved pass seats still expected at the door",
            "Filter by event and by event date",
            "Counts keyed to the venue's own calendar date",
            "Refreshes every ten seconds while the tab is in front"
        ],
        "offers": {
            "@type": "Offer",
            "price": "{{ $proMonthly }}",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Scanning is free on every plan; the live dashboard is on the Pro plan"
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
           Check-in "The Head Count" styles.

           CONCEPT: THE ONE NUMBER SOMEBODY IS WATCHING. Every other
           reporting surface in the product is READ, once, afterwards.
           This is the only one that is WATCHED - it refreshes itself
           every ten seconds while you stand at the door - and the thing
           being watched is a single figure going up against a known
           total. So the page is that figure, at the size it deserves,
           and everything else on the page is subordinate to it.

           WHY NOT A DOOR, A TURNSTILE OR A CLICKER. All three are
           taken, and all three are about ADMITTING one person.
           /features/ticketing owns "The Turnstile", /for-nightclubs owns
           "The Door" together with the clicker and the rope. The
           dashboard is not the act of admitting anyone; it is the count
           that results, which is why the object here is the SCREEN on
           the desk rather than the doorway in front of it.

           DELIBERATELY NOT a dashboard of panels: /features/analytics
           owns "The Dashboard" and is the after-the-fact read. The
           distinction is load-bearing and the page says it out loud.

           THE SCREEN IS A PHYSICAL OBJECT, so .es-head-screen is FIXED
           dark in both colour modes - a door tablet at eight in the
           evening is dark whatever the reader's theme. Only the room
           around it changes.

           COLOUR: ONE HUE ON THE WHOLE PAGE, and it is the brand's.
           Every other hue in the /features/* family is spent -
           analytics emerald, ticketing sky, newsletters cyan, boost and
           custom-fields orange, gift-cards / embed-tickets /
           allocated-seating blue, passes teal - and violet is banned.
           Rather than force a ninth, the identity here is the NUMBER
           and an achromatic ops surface. State is carried by SHAPE and
           VALUE (filled bar versus outline, solid versus dashed), never
           by a second colour, so it survives a mono screen and a
           colour-blind reader.

           Measured against the grounds this page actually paints:
             light ground #f3f4f6: ink #0f1115 17.17, muted #4b5563 6.87,
                                   accent #1e40af 7.93
             dark ground  #0c0e12: ink #e9eaee 16.07, muted #9aa1ad 7.43,
                                   accent #93c5fd 10.71
             screen #15181d (fixed): ink #e8eaed 14.76, muted #8f97a3 6.04,
                                   accent #93c5fd 9.87
           text-gray-500 is never used - #6b7280 measures 4.5 exactly on
           this ground, with no headroom. Use .es-head-muted.
           ============================================================== */

        .es-head-page { background-color: #f3f4f6; color: #0f1115; }
        .dark .es-head-page { background-color: #0c0e12; color: #e9eaee; }

        .es-head-ink { color: #0f1115; }
        .dark .es-head-ink { color: #e9eaee; }
        .es-head-muted { color: #4b5563; }
        .dark .es-head-muted { color: #9aa1ad; }
        .es-head-accent { color: #1e40af; }
        .dark .es-head-accent { color: #93c5fd; }

        .es-head-rule { border-top: 1px solid rgba(15, 17, 21, 0.09); }
        .dark .es-head-rule { border-top-color: rgba(233, 234, 238, 0.10); }

        .es-head-tag {
            display: inline-block;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #1e40af;
        }
        .dark .es-head-tag { color: #93c5fd; }

        .es-head-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.75rem;
            border: 1.5px solid rgba(30, 64, 175, 0.35);
            font-size: 0.8125rem;
            font-weight: 800;
            color: #1e40af;
        }
        .dark .es-head-mark { border-color: rgba(147, 197, 253, 0.35); color: #93c5fd; }

        .es-head-panel {
            background-color: #ffffff;
            border: 1px solid rgba(15, 17, 21, 0.10);
            border-radius: 1rem;
        }
        .dark .es-head-panel {
            background-color: rgba(255, 255, 255, 0.04);
            border-color: rgba(233, 234, 238, 0.10);
        }

        /* ---- THE SCREEN. Fixed in both modes; see the contract above. ---- */
        .es-head-screen {
            background-color: #15181d;
            color: #e8eaed;
            border: 1px solid rgba(255, 255, 255, 0.09);
            border-radius: 1rem;
            box-shadow: 0 18px 45px rgba(6, 8, 12, 0.45);
        }
        .es-head-screen-ink { color: #e8eaed; }
        .es-head-screen-muted { color: #8f97a3; }
        .es-head-screen-accent { color: #93c5fd; }
        .es-head-screen-rule { border-top: 1px solid rgba(255, 255, 255, 0.09); }

        /* Tabular figures so the count cannot jiggle as it changes - the one
           typographic requirement a number being watched actually has. */
        .es-head-figure {
            font-variant-numeric: tabular-nums;
            font-feature-settings: "tnum" 1;
            letter-spacing: -0.03em;
        }

        /* The fill. Arrived is a solid bar; still expected is the dashed
           remainder of the same track, so the split reads without colour. */
        .es-head-track {
            position: relative;
            height: 0.75rem;
            border-radius: 9999px;
            border: 1px dashed rgba(255, 255, 255, 0.28);
            overflow: hidden;
        }
        .es-head-fill {
            position: absolute;
            inset-inline-start: 0;
            top: 0;
            bottom: 0;
            border-radius: 9999px;
            background-color: #93c5fd;
            transform-origin: left;
        }
        [dir="rtl"] .es-head-fill { transform-origin: right; }
        /* Transition on the ALWAYS-ACTIVE rule; only the pre-state is gated, so
           no-JS and reduced-motion rest FULL rather than empty. */
        .es-head-fill { transition: transform 1s ease; }
        html.es-anim [data-reveal]:not(.is-revealed) .es-head-fill { transform: scaleX(0); }

        .es-head-band { background-color: #0f1318; }
        .es-head-band-tag {
            display: inline-block;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #93c5fd;
        }
        .es-head-band-grad {
            background-image: linear-gradient(90deg, #93c5fd, #7dd3fc);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        /* Shared classes carry their own .dark rules in marketing.css and would
           otherwise change inside a band that has none. */
        .es-head-band .grid-overlay { background-image:
            linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px); }
        .es-head-band .es-claim:focus-within { border-color: rgba(147, 197, 253, 0.55); }

        /* marketing.css:248 only rings a.feature-card|bento-card|persona-card. */
        #es-head-page a:focus-visible,
        #es-head-page summary:focus-visible,
        #es-head-page button:focus-visible {
            outline: 2px solid #1e40af;
            outline-offset: 2px;
        }
        .dark #es-head-page a:focus-visible,
        .dark #es-head-page summary:focus-visible,
        .dark #es-head-page button:focus-visible { outline-color: #93c5fd; }
    </style>

    <div id="es-head-page" class="es-head-page">

        <!-- ============================================================ -->
        <!-- 1. Hero: the screen                                          -->
        <!-- ============================================================ -->
        <section id="top" class="relative scroll-mt-24 overflow-hidden py-16 lg:py-24">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="grid items-center gap-12 lg:grid-cols-2">
                    <div>
                        <p class="es-head-tag mb-4" data-reveal>Check-in &middot; scanning is free, the dashboard is Pro</p>
                        <h1 class="es-balance es-head-ink text-4xl font-black tracking-tight md:text-6xl" data-reveal style="--reveal-delay: 0.05s;">
                            Check-in: how many are <span class="es-head-accent">actually in?</span>
                        </h1>
                        <p class="es-head-muted mt-6 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                            Every other report in Event Schedule is something you read afterwards. This is the one you watch while it happens: a single figure going up against a total you already know, refreshing itself every ten seconds while you stand at the door.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3" data-reveal style="--reveal-delay: 0.15s;">
                            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#1e40af] px-6 py-3 font-semibold text-white transition-colors hover:bg-[#1b3894]">
                                Start for free
                            </a>
                            <a href="{{ marketing_url('/docs/tickets') }}#check-in" class="es-head-ink inline-flex items-center gap-2 rounded-xl border border-gray-300 px-6 py-3 font-semibold transition-colors hover:border-[#1e40af] dark:border-white/15">
                                Read the guide
                            </a>
                        </div>
                        <p class="es-head-muted mt-4 text-sm" data-reveal style="--reveal-delay: 0.2s;">
                            Scanning a QR at the door works on every plan, including Free.
                        </p>
                    </div>

                    <div class="es-head-screen p-6 sm:p-8" data-reveal="panel" style="--reveal-delay: 0.1s;">
                        <div class="flex items-baseline justify-between gap-4">
                            <p class="es-head-screen-muted text-xs font-semibold uppercase tracking-widest">Saturday &middot; doors 19:30</p>
                            <p class="es-head-screen-muted text-xs">updated 4s ago</p>
                        </div>

                        <p class="es-head-figure es-head-screen-ink mt-5 text-6xl font-black leading-none sm:text-7xl">
                            184<span class="es-head-screen-muted text-3xl font-bold"> / 300</span>
                        </p>
                        <p class="es-head-screen-accent mt-2 text-sm font-semibold">61% checked in</p>

                        <div class="es-head-track mt-5" role="img" aria-label="184 of 300 attendees checked in, 61 per cent.">
                            <div class="es-head-fill" style="width: 61%;"></div>
                        </div>

                        @php
                            // Fixed, never random: the page has to render identically on every
                            // request for the band-diff verifier to mean anything.
                            $checkinTypes = [
                                ['General admission', 132, 210],
                                ['Balcony', 41, 60],
                                ['Guest list', 11, 30],
                            ];
                        @endphp
                        <div class="es-head-screen-rule mt-6 space-y-2.5 pt-5">
                            @foreach ($checkinTypes as [$typeName, $typeIn, $typeTotal])
                                <div class="flex items-baseline justify-between gap-4 text-sm">
                                    <span class="es-head-screen-muted">{{ $typeName }}</span>
                                    <span class="es-head-figure es-head-screen-ink font-semibold">{{ $typeIn }} / {{ $typeTotal }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="es-head-screen-rule mt-5 pt-4">
                            <p class="es-head-screen-muted text-xs">Last through the door &middot; 19:52 A. Okonkwo &middot; Balcony B12</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. The free half and the Pro half                            -->
        <!-- ============================================================ -->
        <section id="split" class="es-head-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-head-mark mb-6" data-reveal aria-hidden="true"><span>01</span></div>
                    <p class="es-head-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">What check-in costs</p>
                    <h2 class="es-balance es-head-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        The door is free. <span class="es-head-accent">The view is Pro.</span>
                    </h2>
                    <p class="es-head-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Getting people into the room costs nothing on any plan. What Pro adds is being able to see the room from behind the desk while it fills.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2" data-reveal-group="90">
                    <div class="es-head-panel flex flex-col p-6" data-reveal="panel">
                        <p class="es-head-tag mb-3">Every plan, including Free</p>
                        <h3 class="es-head-ink text-lg font-bold">Scan the ticket</h3>
                        <p class="es-head-muted mt-2 text-sm leading-relaxed">
                            Open Sales on a phone and tap Scan Ticket. It reads the QR on any ticket, any free registration, a wallet pass and a subscription pass alike. Each ticket admits once, a second read warns you rather than refusing anyone, and there is no plan check anywhere in it - including on the 25 paid tickets a month the free plan sells.
                        </p>
                        <p class="es-head-muted mt-4 text-xs uppercase tracking-widest">No cap, no gate, no extra app</p>
                    </div>
                    <div class="es-head-panel flex flex-col p-6" data-reveal="panel">
                        <p class="es-head-tag mb-3">Pro</p>
                        <h3 class="es-head-ink text-lg font-bold">Watch the count</h3>
                        <p class="es-head-muted mt-2 text-sm leading-relaxed">
                            The dashboard is the second screen: overall progress with the percentage, the same split per ticket type, and the last ten arrivals with their times. It is what turns a queue into a decision - hold the doors, open the balcony, start on time.
                        </p>
                        <p class="es-head-muted mt-4 text-xs uppercase tracking-widest">Included on Pro and on every selfhosted install</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. What is on it                                             -->
        <!-- ============================================================ -->
        <section id="dashboard" class="es-head-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-head-mark mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                    <p class="es-head-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">On the check-in dashboard</p>
                    <h2 class="es-balance es-head-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Everything the desk <span class="es-head-accent">needs, and nothing else.</span>
                    </h2>
                    <p class="es-head-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        This is not the analytics page. Nothing here is a trend, a comparison or a chart of last month. Every line answers a question somebody is asking out loud right now.
                    </p>
                </div>

                @php
                    $checkinFeatures = [
                        ['The overall count', 'How many of the people who bought are in, as a figure and as a percentage, on one progress bar.'],
                        ['Per ticket type', 'The same split by type, so a sold-out balcony that has not arrived yet is visible before it becomes a queue.'],
                        ['The last ten in', 'A running feed of who just came through, with the time - and on a seated event, the seat they are heading for.'],
                        ['Guests admitted', 'Where a pass admits more than one person, a second headcount counts the people, not the passes.'],
                        ['Still expected', 'Pass holders who reserved a seat in advance sit in a reserved count until they actually arrive. Only a real redemption moves the main number.'],
                        ['Pick the date', 'Filter by event and by date, which is what a recurring night needs: tonight is its own count, not the run\'s total.'],
                    ];
                @endphp
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">
                    @foreach ($checkinFeatures as [$ciName, $ciBody])
                        <div class="es-head-panel flex flex-col p-6" data-reveal="panel">
                            <h3 class="es-head-ink text-base font-bold">{{ $ciName }}</h3>
                            <p class="es-head-muted mt-2 text-sm leading-relaxed">{{ $ciBody }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8 grid gap-4 md:grid-cols-2" data-reveal-group="90">
                    <div class="es-head-panel p-6" data-reveal="panel">
                        <h3 class="es-head-ink text-base font-bold">It refreshes itself, but not constantly</h3>
                        <p class="es-head-muted mt-2 text-sm leading-relaxed">
                            Every ten seconds while the tab is in front of you, and not at all when it is not. That second half matters more than the first: a door screen that kept polling from inside somebody's pocket would flatten the phone that is also running the scanner.
                        </p>
                    </div>
                    <div class="es-head-panel p-6" data-reveal="panel">
                        <h3 class="es-head-ink text-base font-bold">Tonight means the venue's tonight</h3>
                        <p class="es-head-muted mt-2 text-sm leading-relaxed">
                            The counts are keyed to the venue's own calendar date, not to whoever is looking. An evening show west of UTC reports as one evening rather than splitting itself across two days at the wrong midnight.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        @include('marketing.partials.pricing-nudge')

        <!-- ============================================================ -->
        <!-- 4. FAQ                                                       -->
        <!-- ============================================================ -->
        @php
            $checkinFaqs = [
                ['q' => 'Do I need the Pro plan to scan tickets at the door?', 'a' => 'No. Scanning is free on every plan and the Scan Ticket button is always there. It is gated on permission, not on price: owners, admins and viewers can all scan. The Pro half is the live dashboard - the running count, the per-type breakdown and the arrivals feed.'],
                ['q' => 'What device does it need?', 'a' => 'Whatever you already have. Both the scanner and the dashboard are web pages, so a phone works at the door and a tablet or laptop works on the desk. There is nothing to install and nothing to buy.'],
                ['q' => 'What happens if the same ticket is scanned twice?', 'a' => 'It warns rather than refuses. Each ticket admits once, and a second read tells you it has already been used and when - which is the right answer at a door, where the person in front of you is usually holding a phone that a friend already scanned.'],
                ['q' => 'Which tickets does the scanner turn away?', 'a' => 'An order that is unpaid, cancelled, fully refunded or expired, and a ticket scanned more than a day before its event starts or after it has ended. A partial refund leaves the order paid and its tickets valid, so they still scan. A ticket on an installment plan that has fallen behind is flagged rather than refused: the scanner shows the name and the balance still owed, and whether they come in is your call.'],
                ['q' => 'Does it work for free registrations as well as paid tickets?', 'a' => 'Yes. A free registration gets a QR code in its confirmation email on exactly the same terms as a paid ticket, so an RSVP event checks in the same way a ticketed one does.'],
                ['q' => 'Can somebody work the door without seeing our sales?', 'a' => 'Yes, on Enterprise. A team member set to viewer is read-only and sees no sales at all, but may still scan tickets at the door. An admin runs the schedule day to day and does see the sales and the check-in dashboard.'],
                ['q' => 'How do subscription passes appear on it?', 'a' => 'As people, not as passes. Where a pass admits more than one person the dashboard shows a headcount including guests beside the check-in count, and holders who booked a seat ahead are listed as reserved until they actually turn up.'],
                ['q' => 'Does a wallet pass scan differently?', 'a' => 'No. A pass saved into Google Wallet carries the same QR code as the ticket page, so it scans exactly like any other ticket and it works offline once saved. The scanner checks the order\'s live status either way, so a cancelled or fully refunded order is refused at the door even if the pass is still on the phone.'],
                ['q' => 'Which plan do I need?', 'a' => 'The check-in dashboard is on the Pro plan, and on every selfhosted install at no cost. Scanning at the door, free registrations and selling up to 25 paid tickets a calendar month with no platform fee are all on the Free plan.'],
            ];
        @endphp
        <section id="faq" class="es-head-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <h2 class="es-head-ink mb-10 text-center text-3xl font-black tracking-tight md:text-4xl" data-reveal>Check-in questions</h2>
                <div class="space-y-3" data-reveal-group="60">
                    @foreach ($checkinFaqs as $faq)
                        <details class="es-head-panel group p-5" data-reveal="panel">
                            <summary class="es-head-ink flex cursor-pointer items-center justify-between gap-4 text-base font-semibold">
                                {{ $faq['q'] }}
                                <svg class="h-5 w-5 shrink-0 transition-transform group-open:rotate-45" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                                </svg>
                            </summary>
                            <p class="es-head-muted mt-3 text-sm leading-relaxed">{{ $faq['a'] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>

        <x-seo.faq-schema :items="$checkinFaqs" />

        <!-- ============================================================ -->
        <!-- 5. Claim                                                     -->
        <!-- ============================================================ -->
        <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
            <div class="mx-auto max-w-6xl">
                <div class="es-head-band noise relative overflow-hidden rounded-[2rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-20" data-reveal="panel">
                    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                        <div class="grid-overlay absolute inset-0 opacity-25"></div>
                    </div>

                    <div class="relative z-10">
                        <p class="es-head-band-tag mb-6">Free to start</p>
                        <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                            Stop counting <span class="es-head-band-grad">on paper</span>.
                        </h2>
                        <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                            Sell the tickets, scan them at the door, and know the number without asking anyone.
                        </p>

                        <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                            <label for="es-claim-input" class="sr-only">Your schedule name</label>
                            <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-lg border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                                <input id="es-claim-input" type="text" placeholder="your-venue" autocomplete="off" spellcheck="false" maxlength="30"
                                    class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                                <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                            </div>
                            <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg bg-white px-8 py-4 text-lg font-semibold text-[#0f1318] transition-colors hover:bg-gray-100">
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
