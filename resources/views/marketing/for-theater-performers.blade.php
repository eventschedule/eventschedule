<x-marketing-layout>
    <x-slot name="title">Actor Schedules | A Credits List That Updates Itself</x-slot>
    <x-slot name="description">Set your schedule to List and it becomes your credits: every production dated, past work kept, and new bookings arriving from the companies that cast you. Free forever, zero platform fees.</x-slot>
    <x-slot name="breadcrumbTitle">For Theater Performers</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Theater Performers",
        "description": "A public schedule that doubles as a credits list: past productions stay dated and visible, and companies that cast you can put the dates on your page for you to accept.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Theater Performers"
        }
    }
    </script>
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Theater Performers",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Performer Scheduling Software",
        "operatingSystem": "Web",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "A list layout that shows every production, with past work kept under its own divider",
            "Past productions stay public and dated, or can be hidden with one toggle",
            "Booking requests from companies that want to cast you",
            "Every booking waits for the performer to accept it before it is public",
            "Sub-schedules that keep productions, workshops and auditions apart",
            "Draft events that stay members-only, so auditions are not public",
            "Runs set up once as a recurring event with a closing performance",
            "Named ticket types with their own prices, quantities and sales windows",
            "QR check-in for work you produce yourself",
            "Zero platform fees on ticket sales through your own Stripe account",
            "Direct newsletters to the people who follow you",
            "Two-way Google, Outlook and CalDAV calendar sync",
            "Embeddable calendar for your own website"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "actor schedule, theatre credits list, performer calendar, casting booking requests, actor resume online, theatre performance dates",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
    </x-slot>

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present,
         so no-JS visitors, crawlers, and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <style {!! nonce_attr() !!}>
        /* ==============================================================
           For-theater-performers "The Résumé" styles.

           CONCEPT: an actor's credits list, but the version that updates
           itself. The spine is that a résumé is a PDF which was true in
           March. This is NOT an analogy - the product renders a schedule
           as a list (event_layout = 'list'), the list view has a "Show
           past events" toggle and a "Past Events" divider, and past work
           stays public by default (hide_past_events is the opt-out). So
           the page IS the list.

           THREE DEVICES I FIRST REACHED FOR AND DROPPED, because the
           collision checks are cheaper than a redesign:
             - A STAPLE at the corner. /for-spoken-word already renders
               .es-sheet-clip, a metal fastener holding paper, as a CSS
               box - same object family, and that is one of only two other
               paper pages. Replaced with THE ROW THAT ARRIVES: on reveal a
               new credit slides in at the top of the table, so the motion
               is the argument rather than an ornament.
             - "TYPEWRITER" as the headline treatment. /for-comedians
               defines .es-comic-mono as a "rundown/mono voice" and uses it
               27 times on a board that is itself a typed list. Mono here is
               only the year column and the column heads.
             - DOTTED LEADERS. Nine marketing pages already use them.

           COLOUR: archival sepia. Ribbon red was the obvious pick and was
           wrong - #a11d1d sits at 0deg, the same bucket as
           /for-spoken-word's felt-tip #b91c1c, and that is the nearest
           page by concept (also red on warm paper). Sepia is truer to an
           accumulating record and reads BROWN rather than orange because
           it is desaturated: #5c4033 is 28% saturation against curators'
           rust #9a3412 at 79%, comedians' amber at 92%, circus gold at 94%.
           Measured: #5c4033 8.32 on paper / 8.93 on card, #d6a77a 8.70 /
           8.09 / 8.56 in dark.

           NEVER use text-gray-500 - 4.83 on white but only 4.2-4.5 on a
           tinted ground like this one. Use .es-cred-muted (7.11 / 6.44).
           ============================================================== */

        /* --- Ground and ink --- */
        .es-cred-page { background-color: #f4f1e9; color: #1c1a15; }
        .dark .es-cred-page { background-color: #111110; color: #ece8dd; }
        .es-cred-ink { color: #1c1a15; }
        .dark .es-cred-ink { color: #ece8dd; }
        .es-cred-muted { color: #555044; }
        .dark .es-cred-muted { color: #9d968a; }
        .es-cred-accent { color: #5c4033; }
        .dark .es-cred-accent { color: #d6a77a; }
        /* Always-lit accent for the band, in both colour modes. */
        .es-cred-lit { color: #d6a77a; }

        .es-cred-grad {
            background-image: linear-gradient(100deg, #5c4033, #7a5540);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dark .es-cred-grad,
        .es-cred-band .es-cred-grad {
            background-image: linear-gradient(100deg, #e0b48a, #d6a77a);
        }

        /* --- Surfaces --- */
        .es-cred-card {
            background-color: #fbf9f4;
            border: 1px solid rgba(28, 26, 21, 0.12);
            border-radius: 0.5rem;
        }
        .dark .es-cred-card {
            background-color: #1a1917;
            border-color: rgba(236, 232, 221, 0.13);
        }
        .es-cred-sub {
            background-color: rgba(28, 26, 21, 0.045);
            border: 1px solid rgba(28, 26, 21, 0.07);
            border-radius: 0.35rem;
        }
        .dark .es-cred-sub {
            background-color: rgba(236, 232, 221, 0.05);
            border-color: rgba(236, 232, 221, 0.08);
        }
        .es-cred-hover { transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        .es-cred-hover:hover { border-color: rgba(92, 64, 51, 0.45); box-shadow: 0 10px 28px -18px rgba(28, 26, 21, 0.5); }
        .dark .es-cred-hover:hover { border-color: rgba(214, 167, 122, 0.4); box-shadow: 0 10px 28px -18px rgba(0, 0, 0, 0.8); }

        /* --- The credits table -------------------------------------
           A record, not a chart: hairline rules, no span bars, no leaders.
           Mono is confined to the year column and the column heads. */
        .es-cred-mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, 'Liberation Mono', monospace;
            font-variant-numeric: tabular-nums;
        }
        .es-cred-table { width: 100%; border-collapse: collapse; }
        .es-cred-table caption { text-align: left; }
        .es-cred-table th {
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #555044;
            padding: 0 0 0.5rem;
            border-bottom: 1px solid rgba(28, 26, 21, 0.35);
            text-align: left;
        }
        .dark .es-cred-table th { color: #9d968a; border-bottom-color: rgba(236, 232, 221, 0.35); }
        .es-cred-table td {
            padding: 0.62rem 0;
            border-bottom: 1px solid rgba(28, 26, 21, 0.1);
            vertical-align: baseline;
        }
        .dark .es-cred-table td { border-bottom-color: rgba(236, 232, 221, 0.1); }
        .es-cred-table tr:last-child td { border-bottom: 0; }
        .es-cred-year { text-align: right; white-space: nowrap; }

        /* The one credit playing tonight. A state, marked by weight and a
           filled dot as well as by colour, so it does not rely on hue. */
        .es-cred-live { color: #5c4033; font-weight: 700; }
        .dark .es-cred-live { color: #d6a77a; }
        .es-cred-dot {
            display: inline-block;
            width: 0.4rem;
            height: 0.4rem;
            border-radius: 999px;
            background: #5c4033;
            vertical-align: 0.08rem;
        }
        .dark .es-cred-dot { background: #d6a77a; }

        /* --- The row that arrives -----------------------------------
           The newest credit slides in at the top of the list on reveal.
           The FINISHED state is on the always-active rule, so no-JS and
           reduced-motion visitors see a complete, settled list. */
        .es-cred-new {
            transform: translateY(0);
            opacity: 1;
            transition: transform 0.75s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.75s ease;
            transition-delay: 0.45s;
        }
        html.es-anim [data-reveal]:not(.is-revealed) .es-cred-new {
            transform: translateY(-0.75rem);
            opacity: 0;
        }

        /* --- Eyebrow, numerals, plan tags --- */
        .es-cred-tag {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #5c4033;
        }
        .dark .es-cred-tag { color: #d6a77a; }
        .es-cred-band .es-cred-tag { color: #d6a77a; }

        .es-cred-corner {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 1.9rem;
            border: 1px solid rgba(28, 26, 21, 0.22);
            border-radius: 0.2rem;
            background: rgba(28, 26, 21, 0.035);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.78rem;
            font-weight: 700;
            color: #1c1a15;
        }
        .dark .es-cred-corner { border-color: rgba(236, 232, 221, 0.22); background: rgba(236, 232, 221, 0.05); color: #ece8dd; }
        .es-cred-band .es-cred-corner { border-color: rgba(236, 232, 221, 0.22); background: rgba(236, 232, 221, 0.05); color: #ece8dd; }
        .es-cred-corner::before {
            content: "";
            position: absolute;
            left: 0.4rem;
            top: 0.4rem;
            bottom: 0.4rem;
            width: 2px;
            background: #5c4033;
        }
        .dark .es-cred-corner::before { background: #d6a77a; }
        .es-cred-band .es-cred-corner::before { background: #d6a77a; }

        /* Plan tiers ONLY. Never reuse these for a state badge: a reader who
           sees the same pill mean "Pro" in one place and something else in
           another cannot tell which is which. */
        .es-cred-plan {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid transparent;
            padding: 0.1rem 0.5rem;
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        .es-cred-plan-free { border-color: rgba(28, 26, 21, 0.22); color: #555044; }
        .dark .es-cred-plan-free { border-color: rgba(236, 232, 221, 0.26); color: #9d968a; }
        .es-cred-plan-pro { border-color: rgba(92, 64, 51, 0.5); color: #5c4033; background: rgba(92, 64, 51, 0.08); }
        .dark .es-cred-plan-pro { border-color: rgba(214, 167, 122, 0.42); color: #d6a77a; background: rgba(214, 167, 122, 0.1); }

        /* --- Buttons --- */
        .es-cred-btn {
            background-color: #5c4033;
            color: #ffffff;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .es-cred-btn:hover { background-color: #463026; transform: translateY(-1px); box-shadow: 0 14px 28px -16px rgba(92, 64, 51, 0.9); }
        .es-cred-ghost {
            border: 1px solid rgba(28, 26, 21, 0.22);
            color: #1c1a15;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }
        .es-cred-ghost:hover { border-color: rgba(92, 64, 51, 0.5); background-color: rgba(92, 64, 51, 0.06); }
        .dark .es-cred-ghost { border-color: rgba(236, 232, 221, 0.24); color: #ece8dd; }
        .dark .es-cred-ghost:hover { border-color: rgba(214, 167, 122, 0.45); background-color: rgba(214, 167, 122, 0.08); }

        /* --- The dark band ------------------------------------------
           A resolvable background-color under the gradients: it is what
           paints if they fail and what a contrast audit can read. */
        .es-cred-band {
            background-color: #14130f;
            background-image:
                radial-gradient(ellipse 70% 50% at 50% 0%, rgba(92, 64, 51, 0.3), rgba(92, 64, 51, 0) 70%),
                linear-gradient(180deg, #1c1a15, #14130f);
        }

        /* --- Nothing inside the band may change between colour modes --
           The band has no .dark variant, so any descendant that HAS one
           would render differently on an identical ground. That covers two
           shared classes whose .dark rules live in marketing.css and are
           invisible to a grep of this file. */
        .es-cred-band .grid-overlay {
            background-image:
                linear-gradient(rgba(236, 232, 221, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(236, 232, 221, 0.05) 1px, transparent 1px);
        }
        .es-cred-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-cred-band .es-claim:focus-within {
            border-color: rgba(214, 167, 122, 0.75);
            box-shadow: 0 0 0 4px rgba(214, 167, 122, 0.22);
        }

        /* Shared chrome that is hard-coded brand blue. */
        .es-dot:hover .es-dot-pip { background-color: rgba(92, 64, 51, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(214, 167, 122, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #5c4033; }
        .dark .es-dot.is-active .es-dot-pip { background: #d6a77a; }

        /* Focus rings. Never set border-radius here: an outline already
           follows the element's own radius, and overriding it changes the
           element's shape on focus. */
        #es-cred-page a:focus-visible,
        #es-cred-page summary:focus-visible,
        #es-cred-page button:focus-visible,
        #es-cred-page input:focus-visible {
            outline: 2px solid #5c4033;
            outline-offset: 2px;
        }
        .dark #es-cred-page a:focus-visible,
        .dark #es-cred-page summary:focus-visible,
        .dark #es-cred-page button:focus-visible,
        .dark #es-cred-page input:focus-visible {
            outline-color: #d6a77a;
        }
        .es-cred-band a:focus-visible,
        .es-cred-band summary:focus-visible,
        .es-cred-band button:focus-visible,
        .es-cred-band input:focus-visible {
            outline-color: #d6a77a !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-cred-new { transition: none; transform: translateY(0); opacity: 1; }
            .es-cred-btn:hover { transform: none; }
        }
    </style>

    @php
        // One performer's credits. 'live' marks the production playing
        // tonight; 'new' is the row that arrives on reveal. Years are the
        // only figures the table states, and they are ordered newest first.
        $credits = [
            ['Macbeth',        'Lady Macbeth',   'Bridge Theatre',   '2026', 'live'],
            ['The Seagull',    'Nina',           'Studio Four',      '2025', ''],
            ["A Doll's House", 'Kristine Linde', 'Bridge Theatre',   '2025', ''],
            ['Constellations', 'Marianne',       'Fringe Collective','2024', ''],
            ['Twelfth Night',  'Viola',          'Parkside Players', '2024', ''],
        ];

        $faqs = [
            [
                'q' => 'Is Event Schedule free for theater performers?',
                'a' => 'The parts you use every day are free forever: your public schedule and its list layout, past productions kept and dated, sub-schedules, booking requests from companies that want to cast you, Drafts that keep auditions off the public page, two-way calendar sync, an embeddable calendar and up to 10 newsletter emails a month, counted per recipient rather than per send. Selling tickets to work you produce yourself is on the Pro plan at $5 a month, and Event Schedule charges zero platform fees on sales.',
            ],
            [
                'q' => 'How does my schedule become a credits list?',
                'a' => 'Set the default layout to List. The schedule then reads as a list of productions rather than a month grid, and past work sits under its own Past Events heading instead of disappearing. Every credit keeps its date, its venue and its link, so the page is current the day a run closes without you exporting anything.',
            ],
            [
                'q' => 'What happens when a theater casts me in a production?',
                'a' => 'If the company also uses Event Schedule, they can add you to the production and it arrives on your schedule as a request. Nothing is public until you accept it. What you accept carries their own dates and details rather than a second copy you have to keep in step, and you can decline anything you would rather not list.',
            ],
            [
                'q' => 'Can I keep auditions and rehearsals off my public page?',
                'a' => 'Yes. Saving an event as a Draft keeps it members-only, so an audition or a rehearsal call sits on the same schedule without appearing publicly. Sub-schedules keep productions, workshops and teaching on separate strands of the same link.',
            ],
            [
                'q' => 'Can I sell tickets to my own show?',
                'a' => 'Yes, on the Pro plan. Set up named ticket types with their own prices, quantities and sales windows, check people in at the door by scanning a QR code, and take payment through your own Stripe account. Event Schedule charges no platform fee on top.',
            ],
        ];

        $dotSections = [
            ['top', 'Your credits'],
            ['list', 'The list'],
            ['cast', 'Getting cast'],
            ['produce', 'Producing'],
            ['follow', 'Your audience'],
            ['who', 'Who it is for'],
            ['how', 'How it works'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];
    @endphp

    <div id="es-cred-page" class="es-cred-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the credits sheet                                   -->
    <!-- ============================================================ -->
    {{-- The nav overlays the top of the page, so the hero carries extra top
         padding rather than letting the sheet sit under it. --}}
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden pb-16 pt-28">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 28% 28%, rgba(92, 64, 51, 0.22), rgba(92, 64, 51, 0) 62%); opacity: 0.5;"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 74% 64%, rgba(214, 167, 122, 0.16), rgba(214, 167, 122, 0) 62%); opacity: 0.45;"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:72px_72px] [mask-image:radial-gradient(ellipse_72%_62%_at_50%_38%,black_22%,transparent_74%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-[1fr_1.05fr] lg:gap-16">
                <div>
                    <p class="es-cred-tag es-fade-up es-d-1 mb-5">For actors and theatre makers</p>

                    <h1 class="es-balance mb-7 text-[2.6rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">Your r&eacute;sum&eacute; was</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">true <span class="es-cred-grad">in March</span>.</span></span>
                    </h1>

                    <p class="es-cred-muted es-fade-up es-d-2 mb-9 max-w-xl text-lg sm:text-xl">
                        A schedule set to List is a credits list that keeps itself: every production
                        dated, the closed ones still there, and the next one added by the company
                        that cast you.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="es-cred-btn inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            Start your credits page
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                        <a href="#list" class="es-cred-ghost inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            See how the list works
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                    </div>
                </div>

                <!-- The credits sheet. The top row arrives on reveal. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-cred-card p-6 sm:p-8">
                        <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                            <h2 class="es-cred-ink text-xl font-black tracking-tight">Maya Okonkwo</h2>
                            <span class="es-cred-muted es-cred-mono text-xs">maya.eventschedule.com</span>
                        </div>
                        <p class="es-cred-muted mb-6 text-sm">Stage &middot; London</p>

                        <table class="es-cred-table">
                            <caption class="es-cred-tag mb-3">Credits</caption>
                            <thead>
                                <tr>
                                    <th scope="col" class="es-cred-mono">Production</th>
                                    <th scope="col" class="es-cred-mono hidden sm:table-cell">Role</th>
                                    <th scope="col" class="es-cred-mono">Company</th>
                                    <th scope="col" class="es-cred-mono es-cred-year">Year</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($credits as $i => [$cProd, $cRole, $cCo, $cYear, $cState])
                                    <tr @class(['es-cred-new' => $i === 0])>
                                        <td>
                                            <span @class(['es-cred-ink font-semibold', 'es-cred-live' => $cState === 'live'])>{{ $cProd }}</span>
                                            @if ($cState === 'live')
                                                <span class="es-cred-accent ml-2 whitespace-nowrap text-[0.6rem] font-bold uppercase tracking-widest">
                                                    <span class="es-cred-dot" aria-hidden="true"></span> On now
                                                </span>
                                            @endif
                                            <span class="es-cred-muted block text-xs sm:hidden">{{ $cRole }}</span>
                                        </td>
                                        <td class="es-cred-muted hidden text-sm sm:table-cell">{{ $cRole }}</td>
                                        <td class="es-cred-muted text-sm">{{ $cCo }}</td>
                                        <td class="es-cred-muted es-cred-mono es-cred-year text-sm">{{ $cYear }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <p class="es-cred-muted mt-6 border-t border-[rgba(28,26,21,0.1)] pt-4 text-xs dark:border-[rgba(236,232,221,0.1)]">
                            Nothing here was re-exported. The top line was added by the company that cast her.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The list keeps itself (01)                                -->
    <!-- ============================================================ -->
    <section id="list" class="scroll-mt-24 border-t border-[rgba(28,26,21,0.1)] py-20 dark:border-[rgba(236,232,221,0.1)] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-16">
                <div>
                    <div class="es-cred-corner mb-6" data-reveal aria-hidden="true"><span>01</span></div>
                    <p class="es-cred-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The list</p>
                    <h2 class="es-balance es-cred-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        A closed show is still <span class="es-cred-grad">a credit</span>.
                    </h2>
                    <p class="es-cred-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        Most listings vanish when the run ends. Here the date stays, under its own
                        heading, with the venue and the production still attached - so the page a
                        casting director opens in November is right in November.
                    </p>

                    <ul class="space-y-4" data-reveal-group="90">
                        @foreach ([
                            ['Set the layout to List', 'A schedule can render as a month grid or as a list. The list reads like a credits page rather than a calendar.'],
                            ['Past work has its own heading', 'Closed productions sit below a Past Events divider instead of dropping off the page.'],
                            ['Or hide it, if you would rather', 'One toggle removes past events from the public schedule entirely. The default is to keep them.'],
                        ] as [$t, $d])
                            <li class="flex items-start gap-3" data-reveal>
                                <svg aria-hidden="true" class="es-cred-accent mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span><span class="es-cred-ink font-semibold">{{ $t }}</span> <span class="es-cred-muted">- {{ $d }}</span></span>
                            </li>
                        @endforeach
                    </ul>

                    <p class="mt-7" data-reveal>
                        <span class="es-cred-plan es-cred-plan-free">Free</span>
                        <span class="es-cred-muted ml-2 text-sm">The layout, the past-events divider and the toggle are all on the free plan.</span>
                    </p>
                </div>

                <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                    <div class="es-tilt-inner es-cred-card overflow-hidden p-6 sm:p-7">
                        <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                            <h3 class="es-cred-ink text-lg font-bold">Your public page</h3>
                            <span class="es-cred-muted es-cred-mono text-xs">List layout</span>
                        </div>

                        <div class="space-y-2.5">
                            @foreach ([['Macbeth', 'Bridge Theatre', 'Tonight, 7:30pm']] as [$uName, $uCo, $uWhen])
                                <div class="es-cred-sub p-3.5">
                                    <div class="flex items-baseline justify-between gap-3">
                                        <p class="es-cred-live min-w-0 flex-1 truncate text-sm">{{ $uName }}</p>
                                        <p class="es-cred-accent es-cred-mono shrink-0 text-xs">{{ $uWhen }}</p>
                                    </div>
                                    <p class="es-cred-muted text-xs">{{ $uCo }}</p>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6 flex items-center gap-3">
                            <span class="es-cred-tag shrink-0">Past events</span>
                            <span class="h-px flex-1 bg-[rgba(28,26,21,0.18)] dark:bg-[rgba(236,232,221,0.18)]"></span>
                        </div>

                        <div class="mt-3 space-y-2.5">
                            @foreach ([
                                ['The Seagull', 'Studio Four', 'Mar 2025'],
                                ["A Doll's House", 'Bridge Theatre', 'Oct 2025'],
                                ['Constellations', 'Fringe Collective', 'Jun 2024'],
                            ] as [$pName, $pCo, $pWhen])
                                <div class="es-cred-sub p-3.5 opacity-80">
                                    <div class="flex items-baseline justify-between gap-3">
                                        <p class="es-cred-ink min-w-0 flex-1 truncate text-sm font-semibold">{{ $pName }}</p>
                                        <p class="es-cred-muted es-cred-mono shrink-0 text-xs">{{ $pWhen }}</p>
                                    </div>
                                    <p class="es-cred-muted text-xs">{{ $pCo }}</p>
                                </div>
                            @endforeach
                        </div>

                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Someone else casts you (02)                               -->
    <!-- ============================================================ -->
    <section id="cast" class="scroll-mt-24 border-t border-[rgba(28,26,21,0.1)] py-20 dark:border-[rgba(236,232,221,0.1)] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-cred-corner mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                <p class="es-cred-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Getting cast</p>
                <h2 class="es-balance es-cred-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    You do not put most of this <span class="es-cred-grad">on there yourself</span>.
                </h2>
                <p class="es-cred-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Other people decide your dates. So the companies that cast you can add the
                    production to your schedule, and you decide whether it goes up.
                </p>
            </div>

            {{-- No step numerals here: the section chip already reads 02, and a
                 card numbered 02 underneath it reads as a second sequence. The
                 dark band is the one place on the page that numbers steps. --}}
            <div class="grid gap-4 md:grid-cols-3" data-reveal-group="100">
                @foreach ([
                    ['They request', 'Turn on booking requests and a company can ask to put a production on your schedule, with their own dates, venue and details attached.'],
                    ['Nothing appears until you say yes', 'A booking sits as a request until you accept it. There is no setting that lets it skip you, so nothing goes under your name that you did not agree to.'],
                    ['Accept it, or turn it down', 'Requests collect in one place for you to take or decline. What you accept keeps the company\'s own dates and details, so there is no second copy to keep in step.'],
                ] as [$t, $d])
                    <div class="es-cred-card es-cred-hover p-6" data-reveal>
                        <h3 class="es-cred-ink mb-2 text-lg font-bold">{{ $t }}</h3>
                        <p class="es-cred-muted text-sm">{{ $d }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 text-center" data-reveal>
                <span class="es-cred-plan es-cred-plan-free">Free</span>
                <span class="es-cred-muted ml-2 text-sm">
                    All of it is on the free plan. Nobody needs a seat on your schedule to book you.
                </span>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. When you produce it yourself (03)                         -->
    <!-- ============================================================ -->
    <section id="produce" class="scroll-mt-24 border-t border-[rgba(28,26,21,0.1)] py-20 dark:border-[rgba(236,232,221,0.1)] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-16">
                <div class="order-2 lg:order-1">
                    <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                        <div class="es-tilt-inner es-cred-card overflow-hidden p-6 sm:p-7">
                            <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                                <h3 class="es-cred-ink text-lg font-bold">Solo show, Fringe</h3>
                                <span class="es-cred-plan es-cred-plan-pro">Pro</span>
                            </div>
                            <p class="es-cred-muted mb-5 text-sm">One recurring event, twelve nights, closing on the last.</p>

                            <div class="space-y-2.5">
                                @foreach ([
                                    ['Full price', 'On sale now', '$18', '60'],
                                    ['Concession', 'On sale now', '$12', '40'],
                                    ['Preview', 'First two nights', '$8', '30'],
                                ] as [$tName, $tWindow, $tPrice, $tQty])
                                    <div class="es-cred-sub flex items-baseline gap-3 p-3.5">
                                        <span class="es-cred-ink min-w-0 flex-1 truncate text-sm font-semibold">{{ $tName }}</span>
                                        <span class="es-cred-muted hidden truncate text-xs sm:inline">{{ $tWindow }}</span>
                                        <span class="es-cred-muted es-cred-mono text-xs">{{ $tQty }}</span>
                                        <span class="es-cred-ink es-cred-mono text-sm">{{ $tPrice }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <p class="es-cred-muted mt-5 border-t border-[rgba(28,26,21,0.1)] pt-4 text-xs dark:border-[rgba(236,232,221,0.1)]">
                                Payment goes through your own Stripe account. Event Schedule takes none of it.
                            </p>

                            <div class="es-glare" aria-hidden="true"></div>
                            <div class="es-ring-glow" aria-hidden="true"></div>
                        </div>
                    </div>
                </div>

                <div class="order-1 lg:order-2">
                    <div class="es-cred-corner mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                    <p class="es-cred-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Producing</p>
                    <h2 class="es-balance es-cred-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        And when it is <span class="es-cred-grad">your own show</span>.
                    </h2>
                    <p class="es-cred-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        A run of twelve nights is one recurring event with a closing performance, not
                        twelve entries. Sell it from the same page the credits are on.
                    </p>

                    <div class="space-y-3" data-reveal-group="90">
                        @foreach ([
                            ['Named ticket types', 'Full price, concession, preview - each with its own price, quantity and sales window.', true],
                            ['QR check-in', 'Scan tickets at the door from a phone, with a live view so two people can work the queue.', true],
                            ['Zero platform fees', 'You keep the ticket price minus what Stripe charges to process the card. There is no cut on top.', true],
                        ] as [$t, $d, $isPro])
                            <div class="es-cred-card es-cred-hover p-4" data-reveal>
                                <div class="mb-1 flex items-center gap-2">
                                    <p class="es-cred-ink text-sm font-bold">{{ $t }}</p>
                                    @if ($isPro)<span class="es-cred-plan es-cred-plan-pro">Pro</span>@endif
                                </div>
                                <p class="es-cred-muted text-sm">{{ $d }}</p>
                            </div>
                        @endforeach
                    </div>

                    <p class="es-cred-muted mt-6 text-sm" data-reveal>
                        Setting the run up is free. <x-link href="{{ marketing_url('/for-theaters') }}">How a run is built</x-link>.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. The people who follow you (04)                            -->
    <!-- ============================================================ -->
    <section id="follow" class="scroll-mt-24 border-t border-[rgba(28,26,21,0.1)] py-20 dark:border-[rgba(236,232,221,0.1)] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-cred-corner mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                <p class="es-cred-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Your audience</p>
                <h2 class="es-balance es-cred-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    One link for <span class="es-cred-grad">the bio and the programme</span>.
                </h2>
                <p class="es-cred-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    The same address in your profile, your programme biography and your emails, and it
                    is never the out-of-date one.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">
                <div class="es-bento group relative md:col-span-2" data-tilt="3" data-reveal="panel">
                    <div class="es-tilt-inner es-cred-card h-full overflow-hidden p-6">
                        <div class="mb-2 flex items-center gap-2">
                            <h3 class="es-cred-ink text-lg font-bold">Auditions stay off it</h3>
                            <span class="es-cred-plan es-cred-plan-free">Free</span>
                        </div>
                        <p class="es-cred-muted text-sm">
                            Save an audition or a rehearsal call as a Draft and it stays members-only:
                            on your schedule, off your public page. Sub-schedules keep productions,
                            teaching and workshops on separate strands of the same link.
                        </p>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <div class="es-cred-card es-cred-hover p-6" data-reveal>
                    <div class="mb-2 flex items-center gap-2">
                        <h3 class="es-cred-ink text-lg font-bold">Followers</h3>
                        <span class="es-cred-plan es-cred-plan-free">Free</span>
                    </div>
                    <p class="es-cred-muted text-sm">
                        People follow your schedule and hear about the next production from you,
                        in their inbox, rather than from a feed that decides who sees it.
                    </p>
                </div>

                <div class="es-cred-card es-cred-hover p-6 md:col-span-2" data-reveal>
                    <div class="mb-2 flex items-center gap-2">
                        <h3 class="es-cred-ink text-lg font-bold">Newsletters</h3>
                        <span class="es-cred-plan es-cred-plan-free">Free</span>
                    </div>
                    <p class="es-cred-muted text-sm">
                        Write and send from the same place, with open and click rates afterwards. Ten
                        emails a month on the free plan, a hundred on Pro and a thousand on Enterprise, counted per recipient rather than per send.
                    </p>
                </div>

                <div class="es-cred-card es-cred-hover p-6" data-reveal>
                    <div class="mb-2 flex items-center gap-2">
                        <h3 class="es-cred-ink text-lg font-bold">Calendar sync</h3>
                        <span class="es-cred-plan es-cred-plan-free">Free</span>
                    </div>
                    <p class="es-cred-muted text-sm">
                        Two-way sync with Google, Outlook and CalDAV, so a call moved on your phone
                        moves on the schedule too.
                    </p>
                </div>

                <div class="es-cred-card es-cred-hover p-6" data-reveal>
                    <div class="mb-2 flex items-center gap-2">
                        <h3 class="es-cred-ink text-lg font-bold">Embed and share</h3>
                        <span class="es-cred-plan es-cred-plan-free">Free</span>
                    </div>
                    <p class="es-cred-muted text-sm">
                        Drop the list into your own site in an iframe, and generate a post-sized or
                        story-sized image for any date.
                    </p>
                </div>

                <div class="es-cred-card es-cred-hover p-6 lg:col-span-2" data-reveal>
                    <div class="mb-2 flex items-center gap-2">
                        <h3 class="es-cred-ink text-lg font-bold">Online work</h3>
                        <span class="es-cred-plan es-cred-plan-free">Free</span>
                    </div>
                    <p class="es-cred-muted text-sm">
                        Mark an event as online and add the link people join on, from any platform
                        that gives you a URL. Ticket holders get it with their ticket.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Who it is for (05)                                        -->
    <!-- ============================================================ -->
    <section id="who" class="scroll-mt-24 border-t border-[rgba(28,26,21,0.1)] py-20 dark:border-[rgba(236,232,221,0.1)] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-cred-corner mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                <p class="es-cred-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Who it is for</p>
                <h2 class="es-balance es-cred-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Every kind of <span class="es-cred-grad">credit</span>.
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <x-sub-audience-card
                    name="Musical Theater Performers"
                    description="Runs, concerts and cabaret nights, each keeping its dates and its venue once the run has closed."
                    icon-color="amber"
                    blog-slug="for-musical-theater-performers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l11-2v13M9 19a3 3 0 11-6 0 3 3 0 016 0zm11-2a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Dramatic Actors"
                    description="Straight plays at companies that book you months ahead. Their dates arrive on your page for you to accept."
                    icon-color="orange"
                    blog-slug="for-drama-actors"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-7.5 4.5A8 8 0 0112 4a8 8 0 014.5 12.5M8 20h8" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Community Theater"
                    description="A company season and a volunteer cast, on one link that the whole town can follow and subscribe to."
                    icon-color="emerald"
                    blog-slug="for-community-theater-performers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.36-1.86M17 20H7m10 0v-2c0-.66-.13-1.3-.36-1.86m0 0A5 5 0 007 18v2m5-9a3 3 0 100-6 3 3 0 000 6z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Improv & Sketch"
                    description="A weekly night is one recurring event with the dates you are actually on, not forty separate entries."
                    icon-color="sky"
                    blog-slug="for-improv-sketch-performers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Experimental & Fringe"
                    description="Short runs, site-specific work and shared bills. Publish only the dates that are ready and keep the rest as Drafts."
                    icon-color="teal"
                    blog-slug="for-experimental-fringe-theater"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 18c2.5 0 2.5-4 5-4s2.5 4 5 4 2.5-4 5-4M4 10c2.5 0 2.5-4 5-4s2.5 4 5 4 2.5-4 5-4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Children's & Youth Theater"
                    description="School matinees and family weekends, with free registration and a capacity on each date."
                    icon-color="rose"
                    blog-slug="for-childrens-youth-theater"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. How it works (06, dark band)                              -->
    <!-- ============================================================ -->
    <section id="how" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-cred-band noise relative overflow-hidden rounded-[2rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="es-cred-corner mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                    <p class="es-cred-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">How it works</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Set it up once, then <span class="es-cred-grad">stop maintaining it</span>.
                    </h2>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    @foreach ([
                        ['01', 'Claim the address', 'One link that goes in your profile, your programme biography and every email you send.'],
                        ['02', 'Switch the layout to List', 'The schedule reads as credits, with past productions under their own heading.'],
                        ['03', 'Let the work arrive', 'Companies request, you accept, and the ones you trust post straight to the page.'],
                    ] as [$n, $t, $d])
                        <div class="rounded-lg border border-white/10 bg-white/[0.05] p-7 backdrop-blur-sm" data-reveal="panel">
                            <p class="es-cred-lit es-cred-mono mb-3 text-sm font-bold">{{ $n }}</p>
                            <h3 class="mb-2 text-lg font-bold text-white">{{ $t }}</h3>
                            <p class="text-sm text-gray-400">{{ $d }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Key features                                              -->
    <!-- ============================================================ -->
    <section class="scroll-mt-24 border-t border-[rgba(28,26,21,0.1)] py-20 dark:border-[rgba(236,232,221,0.1)]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-cred-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Sub-schedules" description="Keep productions, teaching and workshops on separate strands of one link" :url="marketing_url('/features/sub-schedules')" icon-color="amber">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="A run set up once, with a closing performance" :url="marketing_url('/features/recurring-events')" icon-color="teal">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="Named ticket types, QR check-in, and zero platform fees" :url="marketing_url('/features/ticketing')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Email the people who follow you, with open and click rates" :url="marketing_url('/features/newsletters')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-cred-accent inline-flex items-center font-medium hover:underline">
                    See all features
                    <svg aria-hidden="true" class="ml-1 h-4 w-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    @include('marketing.partials.pricing-nudge')

    <!-- ============================================================ -->
    <!-- 9. Keep exploring                                            -->
    <!-- ============================================================ -->
    <section class="border-t border-[rgba(28,26,21,0.1)] py-16 dark:border-[rgba(236,232,221,0.1)]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-cred-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([
                    ['/for-theaters', 'Theaters'],
                    ['/for-comedians', 'Comedians'],
                    ['/for-dance-groups', 'Dance Groups'],
                    ['/for-spoken-word', 'Spoken Word Artists'],
                ] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="es-cred-card es-cred-hover group flex items-center justify-between p-5">
                        <div>
                            <div class="es-cred-muted text-sm">Event Schedule for</div>
                            <div class="es-cred-ink text-lg font-semibold">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="es-cred-accent h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-cred-accent inline-flex items-center font-medium hover:underline">
                    See all use cases
                    <svg aria-hidden="true" class="ml-1 h-4 w-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <x-marketing.related-pages />

    <!-- ============================================================ -->
    <!-- 10. FAQ (07)                                                 -->
    <!-- ============================================================ -->
    <section id="faq" class="scroll-mt-24 border-t border-[rgba(28,26,21,0.1)] py-20 dark:border-[rgba(236,232,221,0.1)] lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-cred-corner mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                <p class="es-cred-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Questions</p>
                <h2 class="es-balance es-cred-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Asked <span class="es-cred-grad">in the dressing room</span>.
                </h2>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ($faqs as $faq)
                    <details name="faq" data-reveal class="es-cred-card group/faq overflow-hidden">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-6">
                            <h3 class="es-cred-ink text-lg font-semibold">{{ $faq['q'] }}</h3>
                            <svg aria-hidden="true" class="es-cred-muted h-5 w-5 shrink-0 transition-transform duration-300 group-open/faq:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="es-cred-muted faq-answer px-6 pb-6">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <x-seo.faq-schema :items="$faqs" />

    <!-- ============================================================ -->
    <!-- 11. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-cred-band noise relative overflow-hidden rounded-[2rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-25"></div>
                </div>

                <div class="relative z-10">
                    <p class="es-cred-tag mb-6">Free forever</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                        Start the list <span class="es-cred-grad">that keeps itself</span>.
                    </h2>
                    <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                        Every production you have been in, dated, at one address you never have to
                        send a new version of.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-lg border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-name" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="es-cred-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg px-8 py-4 text-lg font-semibold">
                            <span class="relative z-10 flex items-center gap-2">
                                Start your credits page
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

    <!-- Section dot navigation -->
    <nav class="es-dotnav fixed top-1/2 z-40 hidden -translate-y-1/2 lg:block ltr:right-5 rtl:left-5" aria-label="Page sections">
        <ul class="glass flex flex-col items-center gap-1.5 rounded-full px-2 py-3">
            @foreach ($dotSections as [$sectionId, $sectionLabel])
                <li class="relative">
                    <a href="#{{ $sectionId }}" class="es-dot group block rounded-full" aria-label="{{ $sectionLabel }}">
                        <span class="es-dot-pip block h-2 w-2 rounded-full bg-gray-400/60 dark:bg-white/30"></span>
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-medium text-gray-700 opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3 dark:border-white/10 dark:bg-[#1a1917] dark:text-gray-300">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
