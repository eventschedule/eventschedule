<x-marketing-layout>
    <x-slot name="title">Festival Schedule | Stages, Lineups and Weekend Passes</x-slot>
    <x-slot name="description">Every stage its own link, every set on the lineup, and a weekend pass that scans at each gate on one QR code. Zero platform fees on what fans pay you.</x-slot>
    <x-slot name="breadcrumbTitle">For Festivals</x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Festivals"
        description="A festival schedule where each stage is a sub-schedule with its own link, each act can be credited and offered its date, and a weekend pass covers every day on one QR code."
        audience="Music, Arts, Film and Food Festival Organizers"
        keywords="festival schedule, festival lineup, festival ticketing, weekend pass, stage schedule, festival app alternative" />
    <!-- HowTo Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to publish a festival schedule with Event Schedule",
        "description": "Stages first, then the sets, then the pass that opens every gate.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Add the stages",
                "text": "Create one sub-schedule for each stage, tent or screen. Each gets its own link and colour on the festival calendar."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Put the sets on",
                "text": "Add each set as an event on its stage and name the act. An act on Event Schedule is offered the date; one that is not gets a page crediting the festival that it can claim."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Open the gates",
                "text": "Take free registration with a capacity, or sell day tickets and a weekend pass on Pro, and scan every QR code at the gate from a phone."
            }
        ]
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
           For-festivals "The Clash Sheet" styles.

           CONCEPT: a festival's schedule is two-dimensional. Every other
           audience page draws a calendar as a line of dates; a festival
           is a GRID - stages across, hours down, and the question a fan
           asks is "what is on everywhere at nine?". So the hero is the
           grid itself: three stages as columns, sets as blocks sized by
           their running time. A stage is a sub-schedule, a block is an
           event, and the pass underneath opens all of it.

           COLOUR: tangerine, hue ~20. The neighbours this page links to
           are /for-live-concerts (rose, #9f1239), /for-food-trucks (leaf
           green, #2d6b26) and /for-music-venues (yellow, #a16207), so an
           orange sits between them without matching any. Accent #9a3a0b
           is roughly 6.4:1 on the light ground; #fdae6b about 10:1 on the
           dark ground. NEVER text-gray-500 - use .es-fest-muted.
           ============================================================== */

        /* --- Ground and ink --- */
        .es-fest-page { background-color: #f8f4ef; color: #1b1510; }
        .dark .es-fest-page { background-color: #110d0a; color: #f1ebe5; }
        .es-fest-ink { color: #1b1510; }
        .dark .es-fest-ink { color: #f1ebe5; }
        .es-fest-muted { color: #5a4d42; }
        .dark .es-fest-muted { color: #b3a598; }
        .es-fest-accent { color: #9a3a0b; }
        .dark .es-fest-accent { color: #fdae6b; }
        .es-fest-lit { color: #fdae6b; }

        .es-fest-grad {
            background-image: linear-gradient(100deg, #9a3a0b, #c2410c);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dark .es-fest-grad,
        .es-fest-band .es-fest-grad {
            background-image: linear-gradient(100deg, #fed7aa, #fdae6b);
        }

        /* --- Surfaces --- */
        .es-fest-card {
            background-color: #fdfbf8;
            border: 1px solid rgba(27, 21, 16, 0.12);
            border-radius: 0.75rem;
        }
        .dark .es-fest-card {
            background-color: #1b1612;
            border-color: rgba(241, 235, 229, 0.12);
        }
        .es-fest-sub {
            background-color: rgba(27, 21, 16, 0.045);
            border-radius: 0.4rem;
        }
        .dark .es-fest-sub { background-color: rgba(241, 235, 229, 0.05); }
        .es-fest-hover { transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        .es-fest-hover:hover { border-color: rgba(154, 58, 11, 0.45); box-shadow: 0 10px 28px -18px rgba(27, 21, 16, 0.5); }
        .dark .es-fest-hover:hover { border-color: rgba(253, 174, 107, 0.4); box-shadow: 0 10px 28px -18px rgba(0, 0, 0, 0.8); }

        /* --- The clash sheet ----------------------------------------
           A fixed object: the panel is always dark, in both colour
           modes, so nothing inside it has a .dark variant. Rows are
           half-hours; a set's grid-row span is its running time. */
        .es-fest-panel { background-color: #24170f; color: #ffffff; border-radius: 1rem; }
        .dark .es-fest-panel { background-color: #1e140d; }
        .es-fest-sheet {
            display: grid;
            grid-template-columns: 2.6rem repeat(3, minmax(0, 1fr));
            grid-auto-rows: 1.05rem;
            column-gap: 0.4rem;
        }
        .es-fest-hour {
            font-size: 0.6rem;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.72);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 0.1rem;
        }
        .es-fest-set {
            border-radius: 0.35rem;
            padding: 0.25rem 0.4rem;
            overflow: hidden;
            font-size: 0.68rem;
            line-height: 1.15;
            font-weight: 700;
            color: #1b1510;
        }
        .es-fest-set small { display: block; font-weight: 600; font-size: 0.58rem; opacity: 0.8; }
        .es-fest-s1 { background-color: #fdae6b; }
        .es-fest-s2 { background-color: #fcd34d; }
        .es-fest-s3 { background-color: #fed7aa; }
        .es-fest-stagehead {
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.8);
            padding-bottom: 0.4rem;
        }
        .es-fest-cap {
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.75);
        }
        .es-fest-num {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, 'Liberation Mono', monospace;
            font-variant-numeric: tabular-nums;
        }

        /* --- Eyebrow, chips, plan tags --- */
        .es-fest-tag {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #9a3a0b;
        }
        .dark .es-fest-tag { color: #fdae6b; }
        .es-fest-band .es-fest-tag { color: #fdae6b; }

        /* The chip is a wristband: a numbered strip with rounded ends. */
        .es-fest-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            border-radius: 999px;
            padding: 0.2rem 0.8rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            color: #9a3a0b;
            background: rgba(154, 58, 11, 0.09);
            border: 1px solid rgba(154, 58, 11, 0.3);
        }
        .dark .es-fest-chip { color: #fdae6b; background: rgba(253, 174, 107, 0.08); border-color: rgba(253, 174, 107, 0.3); }
        .es-fest-band .es-fest-chip { color: #fdae6b; background: rgba(253, 174, 107, 0.08); border-color: rgba(253, 174, 107, 0.3); }

        /* Plan tiers ONLY. */
        .es-fest-plan {
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
        .es-fest-plan-free { border-color: rgba(27, 21, 16, 0.22); color: #5a4d42; }
        .dark .es-fest-plan-free { border-color: rgba(241, 235, 229, 0.26); color: #b3a598; }
        .es-fest-plan-pro { border-color: rgba(154, 58, 11, 0.5); color: #9a3a0b; background: rgba(154, 58, 11, 0.08); }
        .dark .es-fest-plan-pro { border-color: rgba(253, 174, 107, 0.42); color: #fdae6b; background: rgba(253, 174, 107, 0.1); }
        .es-fest-plan-ent { border-color: rgba(27, 21, 16, 0.4); color: #1b1510; background: rgba(27, 21, 16, 0.06); }
        .dark .es-fest-plan-ent { border-color: rgba(241, 235, 229, 0.4); color: #f1ebe5; background: rgba(241, 235, 229, 0.08); }

        /* --- Buttons --- */
        .es-fest-btn {
            background-color: #9a3a0b;
            color: #ffffff;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .es-fest-btn:hover { background-color: #7c2d08; transform: translateY(-1px); box-shadow: 0 14px 28px -16px rgba(154, 58, 11, 0.9); }
        .es-fest-ghost {
            border: 1px solid rgba(27, 21, 16, 0.22);
            color: #1b1510;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }
        .es-fest-ghost:hover { border-color: rgba(154, 58, 11, 0.5); background-color: rgba(154, 58, 11, 0.06); }
        .dark .es-fest-ghost { border-color: rgba(241, 235, 229, 0.24); color: #f1ebe5; }
        .dark .es-fest-ghost:hover { border-color: rgba(253, 174, 107, 0.45); background-color: rgba(253, 174, 107, 0.08); }

        /* --- The dark band --- */
        .es-fest-band {
            background-color: #150f0b;
            background-image:
                radial-gradient(ellipse 70% 50% at 50% 0%, rgba(194, 65, 12, 0.4), rgba(194, 65, 12, 0) 70%),
                linear-gradient(180deg, #1b1410, #150f0b);
        }
        .es-fest-band .grid-overlay {
            background-image:
                linear-gradient(rgba(241, 235, 229, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(241, 235, 229, 0.05) 1px, transparent 1px);
        }
        .es-fest-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-fest-band .es-claim:focus-within {
            border-color: rgba(253, 174, 107, 0.75);
            box-shadow: 0 0 0 4px rgba(253, 174, 107, 0.22);
        }

        .es-dot:hover .es-dot-pip { background-color: rgba(154, 58, 11, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(253, 174, 107, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #9a3a0b; }
        .dark .es-dot.is-active .es-dot-pip { background: #fdae6b; }

        #es-fest-page a:focus-visible,
        #es-fest-page summary:focus-visible,
        #es-fest-page button:focus-visible,
        #es-fest-page input:focus-visible {
            outline: 2px solid #9a3a0b;
            outline-offset: 2px;
        }
        .dark #es-fest-page a:focus-visible,
        .dark #es-fest-page summary:focus-visible,
        .dark #es-fest-page button:focus-visible,
        .dark #es-fest-page input:focus-visible {
            outline-color: #fdae6b;
        }
        .es-fest-band a:focus-visible,
        .es-fest-band summary:focus-visible,
        .es-fest-band button:focus-visible,
        .es-fest-band input:focus-visible {
            outline-color: #fdae6b !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-fest-btn:hover { transform: none; }
        }
    </style>

    @php
        // The clash sheet. Rows are half-hours from 14:00; every set is
        // [stage column 1-3, start half-hour index, length in half-hours,
        // act, time label]. The counts below the sheet are derived from
        // this array so the copy cannot drift from the picture.
        $stages = ['Main stage', 'The tent', 'Courtyard'];
        $sheetStart = 14;
        $sheetHalfHours = 18; // 14:00 to 23:00
        $sets = [
            [1, 0, 3, 'The Low Tides', '14:00'],
            [1, 4, 3, 'Harbour Lights', '16:00'],
            [1, 8, 4, 'Mara Quinn', '18:00'],
            [1, 13, 5, 'Northbound', '20:30'],
            [2, 1, 2, 'Open decks', '14:30'],
            [2, 4, 4, 'Salt & Cedar', '16:00'],
            [2, 9, 3, 'Kid Lantern', '18:30'],
            [2, 13, 4, 'Velvet Static', '20:30'],
            [3, 0, 4, 'Street food demo', '14:00'],
            [3, 5, 2, 'Poetry hour', '16:30'],
            [3, 8, 3, 'Folk session', '18:00'],
            [3, 12, 3, 'Silent disco', '20:00'],
        ];
        $setCount = count($sets);
        $stageCount = count($stages);

        $faqs = [
            [
                'q' => 'Can one ticket cover the whole weekend?',
                'a' => 'Yes, as a pass. A weekend pass is sold once and redeemed across every event it covers, so Friday, Saturday and Sunday each scan the same QR code, and the pass keeps count of what has been used. A day ticket is simply a ticket type on one day\'s event. Passes, and putting a price on any ticket, are part of Pro at '.plan_price($proMonthly).' a month, with zero platform fees on what fans pay you.',
            ],
            [
                'q' => 'Can each stage have its own link?',
                'a' => 'Each stage is a sub-schedule, and every sub-schedule has its own link and colour. Share the main festival link and people see every stage together; share the tent\'s link and they see only the tent. Sub-schedules are on the free plan, with no limit on how many stages you add.',
            ],
            [
                'q' => 'What happens when an act is not on Event Schedule?',
                'a' => 'Add them by name anyway. They get a page of their own that lists the dates you gave them, says your festival listed them and that they have not claimed it yet, and stays out of search engines until they do. Add their email address and they can claim it by signing in with it. An act that already has a schedule is offered the date instead, and once they accept it shows on their own page too, which is free promotion for your lineup.',
            ],
            [
                'q' => 'How much does Event Schedule take from ticket sales?',
                'a' => 'Nothing. There is no platform fee on any plan. Once tickets carry a price, which is Pro, fans pay through your own Stripe or PayPal account, an Invoice Ninja invoice, a payment link or cash, and the only fee is your payment provider\'s own processing charge. Free-entry days with registration cost nothing at all.',
            ],
            [
                'q' => 'Can volunteers scan tickets at several gates?',
                'a' => 'Scanning works from any phone browser, so there is no scanner hardware to hire, and scanning a ticket is free on every plan. Each ticket admits once and a second scan warns. To give volunteers their own logins you need more than one team member, which is Enterprise; a viewer can scan tickets without seeing any sales. The live check-in dashboard, with a running count per ticket type, is part of Pro.',
            ],
            [
                'q' => 'Can I run early-bird pricing?',
                'a' => 'Yes. Each ticket type can have its own sales start and end dates, so an early-bird type closes on a date you choose and the standard price takes over. Group rates can be set as volume discounts on the same ticket. Promo codes for partners, add-ons such as parking or camping, and monthly installments on an expensive pass are also Pro features.',
            ],
            [
                'q' => 'Our festival is free to enter. Can we still cap numbers?',
                'a' => 'Yes, and for nothing. Make the event free and give it a capacity. People register instead of paying, each gets a QR code to show at the gate, the count is kept separately for each date, and registration closes when the places are gone. Free registration has no monthly ceiling on any plan.',
            ],
            [
                'q' => 'How do acts and traders apply?',
                'a' => 'Through the festival page. The default form takes a pasted pitch or an uploaded flyer and reads it into a request for you to review; switch to the booking form and every application arrives with a date, a time and a description. Nothing appears publicly until you accept it. On Pro you can add your own questions to either form, such as a pitch size, a power requirement or a link to a set.',
            ],
        ];

        $dotSections = [
            ['top', 'The grid'],
            ['stages', 'Stages'],
            ['acts', 'The lineup'],
            ['passes', 'Passes'],
            ['gate', 'The gate'],
            ['applications', 'Applications'],
            ['fans', 'The crowd'],
            ['who', 'Who it is for'],
            ['how', 'How it works'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];
    @endphp

    <div id="es-fest-page" class="es-fest-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the clash sheet                                     -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden pb-16 pt-28">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 24% 32%, rgba(194, 65, 12, 0.2), rgba(194, 65, 12, 0) 62%); opacity: 0.55;"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 78% 62%, rgba(252, 211, 77, 0.18), rgba(252, 211, 77, 0) 62%); opacity: 0.45;"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:76px_76px] [mask-image:radial-gradient(ellipse_72%_62%_at_50%_38%,black_22%,transparent_74%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                <div>
                    <h1 class="es-balance mb-7 text-[2.6rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <x-marketing.hero-eyebrow class="block es-fest-tag es-fade-up es-d-1 mb-5">Festival schedule and lineup, stage by stage</x-marketing.hero-eyebrow>
                        <span class="es-mask"><span class="es-mask-line">Three stages.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">One <span class="es-fest-grad">pass</span>.</span></span>
                    </h1>

                    <p class="es-fest-muted es-fade-up es-d-2 mb-9 max-w-xl text-lg sm:text-xl">
                        A festival is not a list of dates. It is a grid: stages across, hours down, and
                        a crowd trying to work out what is on everywhere at nine. Put every set on the
                        grid, give every stage its own link, and let one pass open all of it.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ app_url('/sign_up?type=curator') }}" class="es-fest-btn inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            Build the lineup
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                        <a href="#stages" class="es-fest-ghost inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            See how stages work
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                    </div>
                </div>

                <!-- The clash sheet. Stages across, half-hours down. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-fest-panel p-5 sm:p-7">
                        <div class="mb-4 flex flex-wrap items-baseline justify-between gap-2">
                            <div>
                                <p class="es-fest-cap">Saturday</p>
                                <p class="mt-1 text-xl font-bold text-white">Riverside Weekender</p>
                            </div>
                            <p class="es-fest-num text-xs text-white/70">14:00 to 23:00</p>
                        </div>

                        <div class="es-fest-sheet" role="img"
                            aria-label="Saturday at Riverside Weekender: {{ $setCount }} sets across {{ $stageCount }} stages, from 14:00 to 23:00.">
                            <div style="grid-column: 1; grid-row: 1 / span 2;"></div>
                            @foreach ($stages as $i => $stage)
                                <div class="es-fest-stagehead truncate" style="grid-column: {{ $i + 2 }}; grid-row: 1 / span 2;">{{ $stage }}</div>
                            @endforeach
                            @for ($h = 0; $h < $sheetHalfHours; $h += 2)
                                <div class="es-fest-hour es-fest-num" style="grid-column: 1; grid-row: {{ $h + 3 }} / span 2;">{{ $sheetStart + intdiv($h, 2) }}:00</div>
                            @endfor
                            @foreach ($sets as [$col, $start, $len, $act, $time])
                                <div class="es-fest-set es-fest-s{{ $col }}" style="grid-column: {{ $col + 1 }}; grid-row: {{ $start + 3 }} / span {{ $len }};">
                                    {{ $act }}<small class="es-fest-num">{{ $time }}</small>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6 grid grid-cols-3 gap-3 border-t border-white/15 pt-5">
                            <div>
                                <p class="es-fest-num text-2xl font-bold text-white">{{ $stageCount }}</p>
                                <p class="es-fest-cap mt-1">Sub-schedules</p>
                            </div>
                            <div>
                                <p class="es-fest-num text-2xl font-bold text-white">{{ $setCount }}</p>
                                <p class="es-fest-cap mt-1">Sets</p>
                            </div>
                            <div>
                                <p class="es-fest-num text-2xl font-bold text-white">1</p>
                                <p class="es-fest-cap mt-1">Weekend pass</p>
                            </div>
                        </div>
                    </div>

                    <p class="es-fest-muted mt-5 text-sm">
                        Every block is an event. Every column is a link you can hand out.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. Stages (01)                                               -->
    <!-- ============================================================ -->
    <section id="stages" class="scroll-mt-24 border-t border-[rgba(27,21,16,0.1)] py-20 dark:border-[rgba(241,235,229,0.1)] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-16">
                <div>
                    <div class="es-fest-chip mb-6" data-reveal aria-hidden="true"><span>01</span></div>
                    <p class="es-fest-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Stages</p>
                    <h2 class="es-balance es-fest-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Every stage gets <span class="es-fest-grad">its own link</span>.
                    </h2>
                    <p class="es-fest-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        A stage, a tent, a screen or a kids' field is a sub-schedule. The festival page
                        shows all of them together, colour-coded, and each one also stands on its own.
                        The acoustic tent can post its own link, and the family field can print a QR code
                        that opens only the family field.
                    </p>

                    <ul class="space-y-4" data-reveal-group="90">
                        @foreach ([
                            ['One page for the whole weekend', 'Fans who want everything see every stage on one schedule, day by day.'],
                            ['A link per stage', 'Hand each stage manager or programmer the link to their own corner of it.'],
                            ['A set is an event', 'Each set has its own time, description, image and page to share, so a headliner announcement is a link, not a screenshot.'],
                            ['Or a day with a running order', 'Prefer one event per day? Put the sets inside it as agenda parts, each with a start and end time, in the order they play.'],
                        ] as [$t, $d])
                            <li class="flex items-start gap-3" data-reveal>
                                <svg aria-hidden="true" class="es-fest-accent mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span><span class="es-fest-ink font-semibold">{{ $t }}</span> <span class="es-fest-muted">- {{ $d }}</span></span>
                            </li>
                        @endforeach
                    </ul>

                    <p class="mt-7" data-reveal>
                        <span class="es-fest-plan es-fest-plan-free">Free plan</span>
                        <span class="es-fest-muted ml-2 text-sm">Sub-schedules, events and agenda parts typed in by hand are all free. Reading a running order off a photo is Enterprise.</span>
                    </p>
                </div>

                <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                    <div class="es-tilt-inner es-fest-card overflow-hidden p-6 sm:p-7">
                        <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                            <h3 class="es-fest-ink text-lg font-bold">The links you hand out</h3>
                            <span class="es-fest-muted es-fest-num text-xs">{{ $stageCount + 1 }} links</span>
                        </div>

                        <div class="space-y-2.5">
                            @foreach ([
                                ['Whole festival', 'riverside.eventschedule.com'],
                                ['Main stage', '.../main-stage'],
                                ['The tent', '.../the-tent'],
                                ['Courtyard', '.../courtyard'],
                            ] as [$fLabel, $fValue])
                                <div class="es-fest-sub flex items-baseline justify-between gap-3 p-3.5">
                                    <span class="es-fest-muted w-28 shrink-0 text-xs uppercase tracking-wider">{{ $fLabel }}</span>
                                    <span class="es-fest-ink es-fest-num min-w-0 flex-1 truncate text-right text-sm font-semibold">{{ $fValue }}</span>
                                </div>
                            @endforeach
                        </div>

                        <p class="es-fest-muted mt-5 border-t border-[rgba(27,21,16,0.1)] pt-4 text-xs dark:border-[rgba(241,235,229,0.12)]">
                            Move a set from the tent to the main stage and every link that shows it updates at once.
                        </p>

                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The lineup (02)                                           -->
    <!-- ============================================================ -->
    <section id="acts" class="scroll-mt-24 border-t border-[rgba(27,21,16,0.1)] py-20 dark:border-[rgba(241,235,229,0.1)] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-fest-chip mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                <p class="es-fest-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The lineup</p>
                <h2 class="es-balance es-fest-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Your acts announce <span class="es-fest-grad">you back</span>.
                </h2>
                <p class="es-fest-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Name an act on a set and the listing does not stop at your page. It reaches the
                    act's own page too, which is where their fans already look.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-3" data-reveal-group="90">
                @foreach ([
                    ['Already on Event Schedule', 'The act is offered the date for their own schedule. Once they accept, your festival shows on the page their followers watch, with a link back to you.'],
                    ['Not on it yet', 'They get a page with their name on it that lists your dates and credits your festival. It stays out of search engines until they claim it by signing in with the email you entered.'],
                    ['Booked through someone else', 'List a venue or a promoter as a source, and every event they publish is linked onto your schedule automatically, past and upcoming. Useful for a festival spread across other people\'s rooms.'],
                ] as [$t, $d])
                    <div class="es-fest-card es-fest-hover flex flex-col p-6" data-reveal>
                        <h3 class="es-fest-ink text-lg font-bold">{{ $t }}</h3>
                        <p class="es-fest-muted mt-2 text-sm">{{ $d }}</p>
                        <p class="mt-auto pt-5"><span class="es-fest-plan es-fest-plan-free">Free plan</span></p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Passes and tickets (03)                                   -->
    <!-- ============================================================ -->
    <section id="passes" class="scroll-mt-24 border-t border-[rgba(27,21,16,0.1)] py-20 dark:border-[rgba(241,235,229,0.1)] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-fest-chip mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                <p class="es-fest-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Passes and tickets</p>
                <h2 class="es-balance es-fest-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Friday to Sunday on <span class="es-fest-grad">one QR code</span>.
                </h2>
                <p class="es-fest-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Some fans come for a day, some for the whole weekend, and some only for the
                    Saturday headliner. Sell each of those as what it is, and keep all the money
                    apart from your payment provider's own fee.
                </p>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="es-fest-card flex flex-col p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-fest-ink text-lg font-bold">Free-entry days</h3>
                        <span class="es-fest-plan es-fest-plan-free">Free plan</span>
                    </div>
                    <p class="es-fest-muted mb-5 text-sm">For the street party, the family day and the fringe show that passes a hat.</p>
                    <ul class="space-y-3">
                        @foreach ([
                            'Registration with a capacity, so the field does not overfill.',
                            'A QR code for everyone who registers, scanned at the gate like a ticket.',
                            'The count kept for each date on its own, closing when the places run out.',
                            'A waitlist on a full date, offering a returned place to the next person in line.',
                            'Several events in one checkout, so a fan picks up free tickets for three workshops at once.',
                        ] as $point)
                            <li class="flex items-start gap-3">
                                <svg aria-hidden="true" class="es-fest-accent mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-fest-muted text-sm">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="es-fest-card flex flex-col p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-fest-ink text-lg font-bold">Paid days and the weekend pass</h3>
                        <span class="es-fest-plan es-fest-plan-pro">Pro plan</span>
                    </div>
                    <p class="es-fest-muted mb-5 text-sm">When there is a price on the pass.</p>
                    <ul class="space-y-3">
                        @foreach ([
                            'Day tickets as ticket types, and a weekend pass redeemed across every day it covers.',
                            'An early-bird type with its own sales window, and group rates as volume discounts.',
                            'Promo codes for partners, and add-ons like parking or a camping pitch with their own stock.',
                            'Installments on an expensive pass, charged monthly to the buyer\'s card through Stripe.',
                            'A waitlist on sold-out tickets, and paid through your own Stripe or PayPal account, Invoice Ninja, a payment link or cash.',
                        ] as $point)
                            <li class="flex items-start gap-3">
                                <svg aria-hidden="true" class="es-fest-accent mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-fest-muted text-sm">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="mt-8 text-center" data-reveal>
                <span class="es-fest-muted text-sm">
                    Zero platform fees, whichever plan you are on. Fans pay the price you set, and your payment provider's own charge is the only deduction.
                </span>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. The gate (04)                                             -->
    <!-- ============================================================ -->
    <section id="gate" class="scroll-mt-24 border-t border-[rgba(27,21,16,0.1)] py-20 dark:border-[rgba(241,235,229,0.1)] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-16">
                <div class="order-2 lg:order-1">
                    <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                        <div class="es-tilt-inner es-fest-card overflow-hidden p-6 sm:p-7">
                            <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                                <h3 class="es-fest-ink text-lg font-bold">Who can do what at the gate</h3>
                                <span class="es-fest-muted es-fest-num text-xs">by plan</span>
                            </div>

                            <div class="space-y-2.5">
                                @foreach ([
                                    ['Scan a QR code from a phone', 'Free'],
                                    ['Warn on a second scan', 'Free'],
                                    ['Live check-in dashboard', 'Pro'],
                                    ['Sponsor logos on the page', 'Pro'],
                                    ['More than one team login', 'Enterprise'],
                                ] as [$gLabel, $gPlan])
                                    <div class="es-fest-sub flex items-baseline justify-between gap-3 p-3.5">
                                        <span class="es-fest-ink min-w-0 flex-1 truncate text-sm font-semibold">{{ $gLabel }}</span>
                                        <span class="es-fest-muted es-fest-num shrink-0 text-xs">{{ $gPlan }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <p class="es-fest-muted mt-5 border-t border-[rgba(27,21,16,0.1)] pt-4 text-xs dark:border-[rgba(241,235,229,0.12)]">
                                A viewer on the team can scan tickets and cannot see a single sale.
                            </p>

                            <div class="es-glare" aria-hidden="true"></div>
                            <div class="es-ring-glow" aria-hidden="true"></div>
                        </div>
                    </div>
                </div>

                <div class="order-1 lg:order-2">
                    <div class="es-fest-chip mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                    <p class="es-fest-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The gate</p>
                    <h2 class="es-balance es-fest-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        No scanners to hire, <span class="es-fest-grad">no printout</span>.
                    </h2>
                    <p class="es-fest-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        The person on the gate opens the scanner in a phone browser and points it at the
                        code. A ticket admits once, a pass counts down, and anything unpaid, refunded or
                        cancelled is refused with the reason on screen.
                    </p>

                    <div class="space-y-3" data-reveal-group="90">
                        @foreach ([
                            ['Every gate, the same codes', 'Tickets, passes and free registrations all scan the same way, so the family gate and the main gate need no separate lists.'],
                            ['Watch the field fill', 'On Pro, the check-in dashboard keeps a running count per ticket type, so you know the Sunday crowd is bigger than Saturday\'s before the bar does.'],
                            ['Sponsors where people look', 'On Pro, partner logos sit on the festival page in tiers, headline partner first.'],
                        ] as [$t, $d])
                            <div class="es-fest-card es-fest-hover p-4" data-reveal>
                                <p class="es-fest-ink text-sm font-bold">{{ $t }}</p>
                                <p class="es-fest-muted mt-1 text-sm">{{ $d }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Applications (05)                                         -->
    <!-- ============================================================ -->
    <section id="applications" class="scroll-mt-24 border-t border-[rgba(27,21,16,0.1)] py-20 dark:border-[rgba(241,235,229,0.1)] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-fest-chip mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                <p class="es-fest-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Applications</p>
                <h2 class="es-balance es-fest-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Acts and traders apply <span class="es-fest-grad">on the page</span>.
                </h2>
                <p class="es-fest-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Every open call ends up as three hundred emails with attachments. Take them
                    through the festival page instead, where each one arrives as a request you accept
                    or decline, and nothing goes public until you say so.
                </p>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="es-fest-card flex flex-col p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-fest-ink text-lg font-bold">Paste the pitch or drop the poster</h3>
                        <span class="es-fest-plan es-fest-plan-free">Free plan</span>
                    </div>
                    <p class="es-fest-muted mb-5 text-sm">The form your page starts with.</p>
                    <ul class="space-y-3">
                        @foreach ([
                            'An act pastes their pitch or uploads a poster, and it is read into a request for you.',
                            'Up to 50 applications are read a day on the free plan and on Pro, 10 while a schedule is on its trial and 100 on Enterprise.',
                            'Or switch to the booking form, where every application brings a date, a time and a description.',
                        ] as $point)
                            <li class="flex items-start gap-3">
                                <svg aria-hidden="true" class="es-fest-accent mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-fest-muted text-sm">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="es-fest-card flex flex-col p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-fest-ink text-lg font-bold">Ask traders what you need to know</h3>
                        <span class="es-fest-plan es-fest-plan-pro">Pro plan</span>
                    </div>
                    <p class="es-fest-muted mb-5 text-sm">Your own questions, on either form.</p>
                    <ul class="space-y-3">
                        @foreach ([
                            'Custom fields for a pitch size, a power requirement or a hygiene rating.',
                            'A validation pattern on a field, so a link has to look like a link.',
                            'Answers shown beside each request, so you decide without opening an inbox.',
                        ] as $point)
                            <li class="flex items-start gap-3">
                                <svg aria-hidden="true" class="es-fest-accent mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-fest-muted text-sm">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. The crowd (06)                                            -->
    <!-- ============================================================ -->
    <section id="fans" class="scroll-mt-24 border-t border-[rgba(27,21,16,0.1)] py-20 dark:border-[rgba(241,235,229,0.1)] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-fest-chip mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                <p class="es-fest-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The crowd</p>
                <h2 class="es-balance es-fest-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    From the first announcement <span class="es-fest-grad">to the last photo</span>.
                </h2>
                <p class="es-fest-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    A festival is sold months before it happens and remembered for months after. The
                    same page carries it through all of that.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group="90">
                @foreach ([
                    ['Before tickets go on sale', 'Switch on the "Notify me" card and fans leave an email address on an announced event. They hear if the date is cancelled and once shortly before it starts, and, when the tickets carry a price on Pro, the moment they go on sale.', 'Free plan'],
                    ['Every new date, on its own', 'People who confirm their email on your page get a short digest when you add events, at most one every three days, and it does not touch your newsletter allowance.', 'Free plan'],
                    ['The lineup in their calendar', 'Fans subscribe to the festival\'s live calendar feed, so a set moved from 18:00 to 19:30 moves in their own phone too.', 'Free plan'],
                    ['The letters you write', 'A newsletter for the headliner reveal: 10 recipients a month on the free plan, 100 on Pro and 1,000 on Enterprise, counted per person it reaches.', 'All plans'],
                    ['Photos, videos, comments', 'The crowd posts what they saw on each event, held in a queue until you approve it. 25 photos on the free plan, no cap on Pro.', 'Free plan'],
                    ['A lift to the site', 'On Pro, fans can offer and ask for seats in a car to the festival, with the driver approving who comes.', 'Pro plan'],
                ] as [$t, $d, $p])
                    <div class="es-fest-card es-fest-hover flex flex-col p-6" data-reveal>
                        <h3 class="es-fest-ink text-base font-bold">{{ $t }}</h3>
                        <p class="es-fest-muted mt-2 text-sm">{{ $d }}</p>
                        <p class="mt-auto pt-5"><span @class(['es-fest-plan', 'es-fest-plan-pro' => $p === 'Pro plan', 'es-fest-plan-free' => $p !== 'Pro plan'])>{{ $p }}</span></p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Who it is for (07)                                        -->
    <!-- ============================================================ -->
    <section id="who" class="scroll-mt-24 border-t border-[rgba(27,21,16,0.1)] py-20 dark:border-[rgba(241,235,229,0.1)] lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-fest-chip mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                <p class="es-fest-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Who it is for</p>
                <h2 class="es-balance es-fest-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Anything with <span class="es-fest-grad">more than one stage</span>.
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2" data-reveal-group="70">
                <x-sub-audience-card
                    name="Music Festivals"
                    description="Stages as sub-schedules, every act linked to its own page, and a weekend pass that scans at every gate on one QR code."
                    icon-color="amber"
                    blog-slug="for-music-festivals"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19a3 3 0 11-6 0 3 3 0 016 0zm12-3a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Arts & Fringe Festivals"
                    description="Hundreds of short runs across borrowed venues, with each company offered its dates and a capacity counted per performance."
                    icon-color="rose"
                    blog-slug="for-fringe-festivals"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 4h14v6a7 7 0 01-14 0V4zm4 5h.01M15 9h.01M9.5 13a3.5 3.5 0 005 0" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Film Festivals"
                    description="Screenings by venue and strand, Q&As added as parts of the programme, and a festival pass that counts down as it is used."
                    icon-color="sky"
                    blog-slug="for-film-festivals"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16v14H4V5zm4 0v14m8-14v14M4 9h4m8 0h4M4 15h4m8 0h4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Food & Street Festivals"
                    description="Free entry with a timed demo stage, vendor applications through the page and one calendar feed for the whole weekend."
                    icon-color="teal"
                    blog-slug="for-food-street-festivals"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10l2-5h14l2 5M3 10h18M3 10v2a3 3 0 006 0 3 3 0 006 0 3 3 0 006 0v-2M5 14v6h14v-6" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. How it works (08, dark band)                              -->
    <!-- ============================================================ -->
    <section id="how" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-fest-band noise relative overflow-hidden rounded-[2rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="es-fest-chip mb-6" data-reveal aria-hidden="true"><span>08</span></div>
                    <p class="es-fest-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">How it works</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Built in a week, <span class="es-fest-grad">run for a weekend</span>.
                    </h2>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    @foreach ([
                        ['01', 'Add the stages', 'One sub-schedule per stage, tent or screen, each with its own link and colour on the festival page.'],
                        ['02', 'Put the sets on', 'Each set as an event on its stage with the act named, or each day as one event with the running order inside it.'],
                        ['03', 'Open the gates', 'Free registration with a capacity, or day tickets and a weekend pass on Pro, scanned at the gate from any phone.'],
                    ] as [$n, $t, $d])
                        <div class="rounded-lg border border-white/10 bg-white/[0.05] p-7 backdrop-blur-sm" data-reveal="panel">
                            <p class="es-fest-lit es-fest-num mb-3 text-sm font-bold">{{ $n }}</p>
                            <h3 class="mb-2 text-lg font-bold text-white">{{ $t }}</h3>
                            <p class="text-sm text-gray-400">{{ $d }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. Key features                                             -->
    <!-- ============================================================ -->
    <section class="scroll-mt-24 border-t border-[rgba(27,21,16,0.1)] py-20 dark:border-[rgba(241,235,229,0.1)]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-fest-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Sub-schedules" description="One per stage, tent or screen, each with its own link" :url="marketing_url('/features/sub-schedules')" icon-color="orange">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Passes & Subscriptions" description="A weekend pass redeemed across every day on one QR code, on the Pro plan" :url="marketing_url('/features/passes')" icon-color="amber">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="Day tickets, early-bird windows and zero platform fees, with prices on Pro" :url="marketing_url('/features/ticketing')" icon-color="rose">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Check-in Dashboard" description="A running count at every gate while the field fills" :url="marketing_url('/features/check-in')" icon-color="teal">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Fan Videos & Photos" description="The crowd's own pictures on each event, approved by you" :url="marketing_url('/features/fan-videos')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-fest-accent inline-flex items-center font-medium hover:underline">
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
    <!-- 11. Related audience pages                                   -->
    <!-- ============================================================ -->
    <section class="border-t border-[rgba(27,21,16,0.1)] py-16 dark:border-[rgba(241,235,229,0.1)]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-fest-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([
                    ['/for-live-concerts', 'Live Concerts'],
                    ['/for-food-trucks-and-vendors', 'Food Trucks & Vendors'],
                    ['/for-curators', 'Curators'],
                    ['/for-music-venues', 'Music Venues'],
                ] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="es-fest-card es-fest-hover group flex items-center justify-between p-5">
                        <div>
                            <div class="es-fest-muted text-sm">Event Schedule for</div>
                            <div class="es-fest-ink text-lg font-semibold">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="es-fest-accent h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-fest-accent inline-flex items-center font-medium hover:underline">
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
    <!-- 12. FAQ (09)                                                 -->
    <!-- ============================================================ -->
    <section id="faq" class="scroll-mt-24 border-t border-[rgba(27,21,16,0.1)] py-20 dark:border-[rgba(241,235,229,0.1)] lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-fest-chip mb-6" data-reveal aria-hidden="true"><span>09</span></div>
                <p class="es-fest-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Questions</p>
                <h2 class="es-balance es-fest-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Asked at <span class="es-fest-grad">the production meeting</span>.
                </h2>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ($faqs as $faq)
                    <details name="faq" data-reveal class="es-fest-card group/faq overflow-hidden">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-6">
                            <h3 class="es-fest-ink text-lg font-semibold">{{ $faq['q'] }}</h3>
                            <svg aria-hidden="true" class="es-fest-muted h-5 w-5 shrink-0 transition-transform duration-300 group-open/faq:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="es-fest-muted faq-answer px-6 pb-6">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <x-seo.faq-schema :items="$faqs" />

    <!-- ============================================================ -->
    <!-- 13. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-fest-band noise relative overflow-hidden rounded-[2rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-25"></div>
                </div>

                <div class="relative z-10">
                    <p class="es-fest-tag mb-6">Free forever</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                        Put the grid up <span class="es-fest-grad">before the poster</span>.
                    </h2>
                    <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                        Stages, sets, act pages, applications and free-entry registration cost
                        nothing. Putting a price on a day ticket or a weekend pass is the part that needs Pro.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-lg border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-festival" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=curator') }}" class="es-fest-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg px-8 py-4 text-lg font-semibold">
                            <span class="relative z-10 flex items-center gap-2">
                                Build the lineup
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
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-medium text-gray-700 opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3 dark:border-white/10 dark:bg-[#1b1612] dark:text-gray-300">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
