<x-marketing-layout>
    <x-slot name="title">Music Venue Calendars | Set Times, Tickets, and the Door</x-slot>
    <x-slot name="description">Put the whole show day on one link: set times for every band on the bill, tickets with zero platform fees and QR check-in, and one page bands and fans both read.</x-slot>
    <x-slot name="breadcrumbTitle">For Music Venues</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Music Venues",
        "description": "Publish the whole show day on one link: set times for every band on the bill, tickets with zero platform fees, and QR check-in on the door.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Music Venues"
        }
    }
    </script>
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Music Venues",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Music Venue Management Software",
        "operatingSystem": "Web",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Set times for every act on a bill, published on the public event page",
            "Photos, video and comments attached to the act that played, not just the show",
            "Sub-schedules that keep each room's listings apart on one link",
            "A public submission form so bands can ask to play",
            "Ticket types with their own sales windows, add-ons and group rates",
            "QR check-in on the door with a real-time check-in dashboard",
            "Zero platform fees on ticket sales, with payouts through your own Stripe account",
            "Recurring residencies with date exceptions",
            "Direct newsletters with open and click rates",
            "Two-way Google, Outlook and CalDAV calendar sync",
            "Embeddable calendar for the website you already have"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "music venue calendar, set times, concert listings, venue ticketing, QR check-in, band booking requests, live music schedule",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
    <!-- HowTo Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to publish a music venue's show day with Event Schedule",
        "description": "Get the whole show day, not just the doors time, onto one link.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Add the show",
                "text": "Create the event once, and use sub-schedules to keep the main room and the back room apart on the same link."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Add the running order",
                "text": "Give the show its parts. Each act gets a name and a start time, and they appear in order on the public event page."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Open the door",
                "text": "Add ticket types with their own sales windows, then scan QR codes on the night and watch the check-in dashboard against your capacity."
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
           For-music-venues "The Running Order" styles. The page is a
           show day on a time axis: load-in, soundcheck, doors, support,
           headline, curfew.

           THE AXIS IS PROPORTIONAL, NOT A LIST. The vertical distance
           between two stops is computed from the minutes between them
           (--min x --es-run-scale), so a three-hour dead stretch is
           visibly long and the 20:00 -> 21:15 turnaround is visibly
           tight. The shape of the day reads before any word does.
           Deliberately NO dotted leaders: /for-comedians owns the
           "numbered rows + leader dots + right-hand time" component and
           three sibling pages already use leader rows, so a fourth
           would make this the most derivative page in the campaign.

           COLOUR IS A HIGHLIGHTER. An audit of all 31 for-* pages found
           the hue wheel effectively consumed; the only unclaimed
           non-banned space is 60deg yellow. Dark yellows turn amber or
           olive (owned by bars, breweries, comedy-clubs, theaters), so
           yellow is NEVER text on a light ground - it is a marker swipe
           behind dark ink (11.59:1) and bright text only on dark
           grounds (12.70:1). Used sparingly, one mark per section, or
           it stops reading as marking.

           This page deliberately uses NO fixed-in-both-modes object:
           the previous four rebuilds all did, and a fifth would read as
           sameness. The whole page is free to change with the mode.

           BLADE RULE for this block: never use @supports probes here.
           A "#" hex inside a parenthesized at-rule condition breaks
           Blade compilation of every later parenthesized directive.
           ============================================================== */

        /* --- Ground and ink --- */
        .es-run-page { background-color: #f7f6f3; color: #17181b; }
        .dark .es-run-page { background-color: #0c0d0f; color: #eceae6; }
        .es-run-ink { color: #17181b; }
        .dark .es-run-ink { color: #eceae6; }
        .es-run-muted { color: #4b5158; }
        .dark .es-run-muted { color: #9aa1a9; }

        /* --- The highlighter: a marker swipe behind dark ink. Never
               used as text colour on a light ground. --- */
        .es-run-mark {
            background-image: linear-gradient(180deg, rgba(250, 204, 21, 0) 52%, rgba(250, 204, 21, 0.92) 52%);
            -webkit-box-decoration-break: clone;
            box-decoration-break: clone;
            padding: 0 0.12em 0.04em;
            color: #17181b;
        }
        /* On dark grounds the swipe would fight the ground, so the mark
           becomes a solid block with the ink flipped back to near-black. */
        .dark .es-run-mark {
            background-image: none;
            background-color: #facc15;
            color: #17181b;
            border-radius: 0.15rem;
        }
        /* Bright yellow AS TEXT is allowed only on dark grounds. */
        .es-run-lit { color: #fde047; }

        /* --- The time axis --- */
        .es-run-axis {
            --es-run-scale: 0.055rem;      /* rem per minute */
            position: relative;
            padding-inline-start: 4.25rem;
        }
        .es-run-axis::before {
            content: "";
            position: absolute;
            inset-block: 0.55rem;
            inset-inline-start: 3.15rem;
            width: 2px;
            border-radius: 1px;
            background: linear-gradient(180deg, rgba(23, 24, 27, 0.08), rgba(23, 24, 27, 0.22) 22%, rgba(23, 24, 27, 0.22) 78%, rgba(23, 24, 27, 0.08));
        }
        .dark .es-run-axis::before {
            background: linear-gradient(180deg, rgba(236, 234, 230, 0.1), rgba(236, 234, 230, 0.26) 22%, rgba(236, 234, 230, 0.26) 78%, rgba(236, 234, 230, 0.1));
        }
        @media (max-width: 640px) {
            .es-run-axis { --es-run-scale: 0.038rem; padding-inline-start: 3.5rem; }
            .es-run-axis::before { inset-inline-start: 2.6rem; }
        }

        /* A stop on the axis. */
        .es-run-stop { position: relative; }
        .es-run-stop::before {
            content: "";
            position: absolute;
            top: 0.62rem;
            inset-inline-start: -1.2rem;
            width: 0.6rem;
            height: 0.6rem;
            border-radius: 9999px;
            background: #f7f6f3;
            border: 2px solid rgba(23, 24, 27, 0.35);
        }
        .dark .es-run-stop::before {
            background: #0c0d0f;
            border-color: rgba(236, 234, 230, 0.4);
        }
        /* The marked stop: filled node, so the highlight is not the only cue. */
        .es-run-stop-now::before {
            background: #facc15;
            border-color: #a16207;
        }
        .dark .es-run-stop-now::before { border-color: #facc15; }

        /* The proportional gap. Height comes from the minutes to the next
           stop; the clamp stops a long dead stretch from becoming screens
           of empty page on a phone. */
        .es-run-gap {
            height: clamp(1.75rem, calc(var(--min, 60) * var(--es-run-scale)), 11rem);
        }

        /* The time column, hung in the axis gutter. */
        .es-run-time {
            position: absolute;
            inset-inline-start: -4.25rem;
            top: 0.35rem;
            width: 3rem;
            text-align: end;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, 'Liberation Mono', monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: -0.01em;
            color: #4b5158;
        }
        .dark .es-run-time { color: #9aa1a9; }
        @media (max-width: 640px) {
            .es-run-time { inset-inline-start: -3.5rem; width: 2.5rem; font-size: 0.75rem; }
        }

        /* --- Eyebrow / labels --- */
        .es-run-tag {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            color: #4b5158;
        }
        .dark .es-run-tag { color: #9aa1a9; }
        .es-run-band .es-run-tag { color: #fde047; }

        /* State label, so "now" is never signalled by colour alone. */
        .es-run-state {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.08rem 0.4rem;
            border-radius: 0.2rem;
            background: #17181b;
            color: #facc15;
            font-size: 0.58rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }

        /* --- Plan tags --- */
        .es-run-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.25rem;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            border: 1px solid rgba(23, 24, 27, 0.32);
            color: #17181b;
        }
        .dark .es-run-plan { border-color: rgba(236, 234, 230, 0.34); color: #eceae6; }
        .es-run-band .es-run-plan { border-color: rgba(236, 234, 230, 0.34); color: #eceae6; }
        .es-run-plan-pro { border-color: #a16207; color: #6b4c05; }
        .dark .es-run-plan-pro { border-color: #facc15; color: #fde047; }
        .es-run-band .es-run-plan-pro { border-color: #facc15; color: #fde047; }

        /* --- Cards --- */
        .es-run-card {
            border: 1px solid rgba(23, 24, 27, 0.12);
            border-radius: 1rem;
            background: #ffffff;
        }
        .dark .es-run-card {
            border-color: rgba(236, 234, 230, 0.12);
            background: rgba(236, 234, 230, 0.04);
        }
        .es-run-band .es-run-card {
            border-color: rgba(236, 234, 230, 0.14);
            background: rgba(236, 234, 230, 0.05);
        }

        /* --- Fixed-dark band --- */
        .es-run-band {
            background-color: #0a0b0c;
            background-image: radial-gradient(120% 100% at 50% 0%, #17181b 0%, #101113 55%, #08090a 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.55), inset 0 1px 0 rgba(236, 234, 230, 0.05);
        }
        /* Shared classes that flip with the colour mode inside a band. */
        .es-run-band .grid-overlay {
            background-image:
                linear-gradient(rgba(236, 234, 230, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(236, 234, 230, 0.05) 1px, transparent 1px);
        }
        .es-run-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-run-band .es-claim:focus-within {
            border-color: rgba(250, 204, 21, 0.75);
            box-shadow: 0 0 0 4px rgba(250, 204, 21, 0.22);
        }

        /* --- Section numeral: a call-time chip --- */
        .es-run-corner {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.35rem 0.8rem;
            border-radius: 0.3rem;
            border: 1px solid rgba(23, 24, 27, 0.2);
            background: #ffffff;
            color: #17181b;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-weight: 800;
            letter-spacing: 0.04em;
        }
        .dark .es-run-corner { border-color: rgba(236, 234, 230, 0.2); background: rgba(236, 234, 230, 0.05); color: #eceae6; }
        .es-run-band .es-run-corner { border-color: rgba(236, 234, 230, 0.2); background: rgba(236, 234, 230, 0.05); color: #eceae6; }
        .es-run-corner::before {
            content: "";
            width: 2px;
            align-self: stretch;
            border-radius: 1px;
            background: #facc15;
        }

        /* --- Links and buttons --- */
        .es-run-link { color: #6b4c05; }
        .es-run-link:hover { color: #17181b; }
        .dark .es-run-link { color: #fde047; }
        .dark .es-run-link:hover { color: #eceae6; }

        .es-run-btn {
            background-color: #17181b;
            box-shadow: 0 18px 36px -14px rgba(23, 24, 27, 0.5);
        }
        .es-run-btn:hover { background-color: #000000; box-shadow: 0 22px 44px -14px rgba(23, 24, 27, 0.6); }
        .dark .es-run-btn { background-color: #facc15; }
        .dark .es-run-btn:hover { background-color: #fde047; }

        /* --- FAQ / related hover --- */
        .es-run-hover:hover { border-color: rgba(161, 98, 7, 0.5); }
        .dark .es-run-hover:hover { border-color: rgba(250, 204, 21, 0.45); }
        .es-run-hover:hover .es-run-hover-title,
        .es-run-hover:hover .es-run-hover-arrow { color: #6b4c05; }
        .dark .es-run-hover:hover .es-run-hover-title,
        .dark .es-run-hover:hover .es-run-hover-arrow { color: #fde047; }

        /* --- Chips --- */
        .es-run-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            border: 1px solid rgba(23, 24, 27, 0.16);
            background: rgba(255, 255, 255, 0.7);
            color: #4b5158;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.74rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .dark .es-run-chip {
            border-color: rgba(236, 234, 230, 0.16);
            background: rgba(236, 234, 230, 0.05);
            color: #b9bfc6;
        }

        /* --- Shared-system recolors (brand blue by default) --- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(161, 98, 7, 0.12), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(250, 204, 21, 0.1), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(23, 24, 27, 0.55); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(236, 234, 230, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #a16207; }
        .dark .es-dot.is-active .es-dot-pip { background: #facc15; }

        /* --- Focus rings. No border-radius here: setting it changes the
               element's own shape on focus. Outlines already follow it. --- */
        #es-run-page a:focus-visible,
        #es-run-page summary:focus-visible,
        #es-run-page button:focus-visible {
            outline: 2px solid #6b4c05;
            outline-offset: 3px;
        }
        .dark #es-run-page a:focus-visible,
        .dark #es-run-page summary:focus-visible,
        .dark #es-run-page button:focus-visible {
            outline-color: #facc15;
        }
        .es-run-band a:focus-visible,
        .es-run-band summary:focus-visible,
        .es-run-band button:focus-visible {
            outline-color: #facc15 !important;
        }
    </style>

    @php
        // The show day. --min on each stop is the real number of minutes to the
        // NEXT stop, which is what makes the axis proportional rather than a list.
        $showDay = [
            ['14:00', 'Load-in',    'The truck is outside and nobody has the code.', 180, false],
            ['17:00', 'Soundcheck', 'Three acts, one PA, and a support band running late.', 120, false],
            ['19:00', 'Doors',      'The only time most calendars ever show.',        60,  true],
            ['20:00', 'Oda',        'Opener. 30 minutes.',                            75,  false],
            ['21:15', 'The Fell',   'Headline. 70 minutes and a curfew to beat.',     105, false],
            ['23:00', 'Curfew',     'Lights up, load-out, do it again Thursday.',     0,   false],
        ];

        $bill = [
            ['20:00', 'Oda', 'Opener', '12'],
            ['21:15', 'The Fell', 'Headline', '47'],
            ['22:40', 'DJ set', 'Late', '5'],
        ];

        $faqs = [
            [
                'q' => 'Is Event Schedule free for music venues?',
                'a' => 'Yes. Publishing your listings, adding set times for every act on a bill, running recurring residencies, splitting rooms into sub-schedules, accepting booking requests, and two-way sync with Google, Outlook or CalDAV are all free forever. Ticketing with QR check-in, the check-in dashboard, event graphics and passes are on the Pro plan at $5 a month.',
            ],
            [
                'q' => 'Can I publish set times for each band on the bill?',
                'a' => 'Yes, on every plan. Give a show its parts, and each act gets a name, an optional description and a start and end time. They appear in order on the public event page, so the bill answers the question instead of you answering it fourteen times on the day.',
            ],
            [
                'q' => 'Can photos and video be attached to the band that played?',
                'a' => 'Yes. When a show has parts, fan photos, video and comments attach to the part rather than to the whole night, so a three-band bill ends up with three galleries instead of one pile. Everything waits in an approval queue before it appears.',
            ],
            [
                'q' => 'Can I run more than one room from the same page?',
                'a' => 'Yes, on every plan. Sub-schedules keep the main room and the back room apart on one link, so somebody looking for the small-room show is not scrolling through two months of everything else.',
            ],
            [
                'q' => 'Can bands ask to play at my venue?',
                'a' => 'Yes. Turn on Accept requests and artists can submit a show from your public page. Submissions collect on your Requests tab, where you accept or decline before anything reaches your calendar. On Pro you can add your own questions to that form, so the details you always end up chasing arrive with the request.',
            ],
            [
                'q' => 'What do you charge on ticket sales?',
                'a' => 'Nothing. Event Schedule takes zero platform fees. You connect your own Stripe account, the money lands there, and the only deduction is Stripe\'s own processing. There is no per-ticket cut and no booking fee added on top of your price.',
            ],
        ];

        $dotSections = [
            ['top', '14:00 Load-in'],
            ['cost', 'The gap'],
            ['order', '19:00 Running order'],
            ['bill', '20:00 The bill'],
            ['rooms', 'The rooms'],
            ['playing', 'Who plays'],
            ['door', 'The door'],
            ['rest', 'Everything else'],
            ['who', 'Perfect for'],
            ['faq', 'Questions'],
            ['claim', 'Load in'],
        ];
    @endphp

    <div id="es-run-page" class="es-run-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the show day on the axis                            -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(250, 204, 21, 0.16), rgba(250, 204, 21, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(23, 24, 27, 0.1), rgba(23, 24, 27, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="h-5 w-5 text-[#a16207] dark:text-[#facc15]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="es-run-muted text-sm font-medium tracking-wide">For music venues and live rooms</span>
                    </div>

                    <h1 class="es-balance es-run-ink mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">Every show is nine hours long.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">Your calendar shows <span class="es-run-mark">one</span> of them.</span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-run-muted mb-10 max-w-xl text-lg sm:text-xl">
                        Put the whole show day on one link: set times for every act on the bill, tickets with zero platform fees, and a door that knows who is actually inside.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="#order" class="glass group inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            See the running order
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="es-run-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] dark:text-[#17181b]">
                            Create your venue calendar
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- The axis. Gaps are computed from real minutes. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-run-card p-6 sm:p-8">
                        <p class="es-run-tag mb-6">Thursday &middot; main room</p>
                        <div class="es-run-axis">
                            @foreach ($showDay as [$time, $name, $note, $gap, $isNow])
                                <div class="es-run-stop @if ($isNow) es-run-stop-now @endif">
                                    <span class="es-run-time">{{ $time }}</span>
                                    <div class="flex flex-wrap items-baseline gap-2">
                                        <span class="es-run-ink font-bold @if ($isNow) es-run-mark @endif">{{ $name }}</span>
                                        @if ($isNow)<span class="es-run-state">Published</span>@endif
                                    </div>
                                    <p class="es-run-muted mt-0.5 text-sm">{{ $note }}</p>
                                </div>
                                @if ($gap > 0)
                                    <div class="es-run-gap" style="--min: {{ $gap }};" aria-hidden="true"></div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Venue-type marquee -->
            <div class="es-fade-up es-d-4 mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach (['Listening Room', 'Small Club', 'Mid-Size', 'Concert Hall', 'Jazz Club', 'Amphitheater', 'Multi-Room', 'House Concert', 'Folk Club', 'Warehouse'] as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-run-chip">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The gap (fixed-dark band)                                 -->
    <!-- ============================================================ -->
    <section id="cost" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-run-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-run-corner mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                    <p class="es-run-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The gap</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        You run nine hours. You publish <span class="es-run-lit">one number.</span>
                    </h2>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    <div class="es-run-card p-6" data-reveal="panel">
                        <p class="es-run-tag mb-3">The day</p>
                        <h3 class="mb-2 text-lg font-bold text-[#eceae6]">
                            <span data-count-to="9">9</span> hours
                        </h3>
                        <p class="text-sm text-[#9aa1a9]">Load-in at two, curfew at eleven. That is the job, and it is the same shape every show night.</p>
                    </div>
                    <div class="es-run-card p-6" data-reveal="panel">
                        <p class="es-run-tag mb-3">The listing</p>
                        <h3 class="mb-2 text-lg font-bold text-[#eceae6]">
                            <span data-count-to="1">1</span> time
                        </h3>
                        <p class="text-sm text-[#9aa1a9]">Doors. Everything else lives in a group chat, a pinned message, and your head.</p>
                    </div>
                    <div class="es-run-card p-6" data-reveal="panel">
                        <p class="es-run-tag mb-3">The consequence</p>
                        <h3 class="mb-2 text-lg font-bold text-[#eceae6]">
                            <span data-count-to="3">3</span> bands asking
                        </h3>
                        <p class="text-sm text-[#9aa1a9]">Plus a tour manager, the engineer, and everyone who bought a ticket for the opener.</p>
                    </div>
                </div>

                <p class="mt-10 text-center text-gray-300" data-reveal>
                    The running order already exists. It just is not anywhere the public can read it.
                    <a href="#order" class="es-run-lit inline-flex items-center gap-1 font-semibold transition-all hover:gap-2">
                        Put it on the page
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The running order                                         -->
    <!-- ============================================================ -->
    <section id="order" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-run-corner mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                <p class="es-run-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The running order</p>
                <h2 class="es-balance es-run-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Give the show its <span class="es-run-mark">parts.</span>
                </h2>
                <p class="es-run-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    A show is not one time. Add each act with its own start time and they publish in order on the event page, on every plan.
                </p>
            </div>

            <div class="grid items-start gap-10 lg:grid-cols-2">
                <div class="space-y-5" data-reveal-group="80">
                    <div class="es-run-card p-6" data-reveal>
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="es-run-ink text-lg font-bold">Each act, its own time</h3>
                            <span class="es-run-plan">Free</span>
                        </div>
                        <p class="es-run-muted text-sm">Name, start, end, and a description if the act needs one. Put them in order once and the public page shows the bill exactly as you set it.</p>
                    </div>
                    <div class="es-run-card p-6" data-reveal>
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="es-run-ink text-lg font-bold">It answers the question for you</h3>
                            <span class="es-run-plan">Free</span>
                        </div>
                        <p class="es-run-muted text-sm">"What time is the headline on" stops being a message you reply to and starts being a line on the page you already sent them.</p>
                    </div>
                    <div class="es-run-card p-6" data-reveal>
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="es-run-ink text-lg font-bold">One link for the band too</h3>
                            <span class="es-run-plan">Free</span>
                        </div>
                        <p class="es-run-muted text-sm">Add a performer to the show and it can surface on their schedule as well, so their followers find your room through them.</p>
                    </div>
                </div>

                <div data-reveal="panel">
                    <div class="es-run-card p-6 sm:p-8">
                        <p class="es-run-tag mb-6">Public event page</p>
                        <div class="es-run-axis">
                            @foreach ([['19:00', 'Doors', 60], ['20:00', 'Oda', 75], ['21:15', 'The Fell', 0]] as [$oTime, $oName, $oGap])
                                <div class="es-run-stop">
                                    <span class="es-run-time">{{ $oTime }}</span>
                                    <span class="es-run-ink font-bold">{{ $oName }}</span>
                                </div>
                                @if ($oGap > 0)
                                    <div class="es-run-gap" style="--min: {{ $oGap }};" aria-hidden="true"></div>
                                @endif
                            @endforeach
                        </div>
                        <p class="es-run-muted mt-6 border-t border-[rgba(23,24,27,0.1)] pt-4 text-xs dark:border-[rgba(236,234,230,0.12)]">
                            Times render publicly whenever an act has one. Leave them off and the bill still lists in order.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. The bill: per-act galleries                               -->
    <!-- ============================================================ -->
    <section id="bill" class="scroll-mt-24 border-y border-[rgba(23,24,27,0.08)] py-20 dark:border-[rgba(236,234,230,0.08)] lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-run-corner mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                <p class="es-run-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The bill</p>
                <h2 class="es-balance es-run-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    Three bands played. <span class="es-run-mark">Three galleries.</span>
                </h2>
                <p class="es-run-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Once a show has parts, photos, video and comments attach to the act rather than to the night, so the opener's set is not buried under the headline's.
                </p>
            </div>

            <div class="grid gap-5 md:grid-cols-3" data-reveal-group="90">
                @foreach ($bill as [$bTime, $bName, $bRole, $bCount])
                    <div class="es-run-card p-6" data-reveal="panel">
                        <div class="mb-3 flex items-baseline justify-between gap-3">
                            <span class="font-mono text-sm font-bold text-[#4b5158] dark:text-[#9aa1a9]">{{ $bTime }}</span>
                            <span class="es-run-tag">{{ $bRole }}</span>
                        </div>
                        <h3 class="es-run-ink mb-4 text-xl font-bold">{{ $bName }}</h3>
                        <div class="mb-3 grid grid-cols-4 gap-1.5" aria-hidden="true">
                            @for ($t = 0; $t < 4; $t++)
                                <div class="aspect-square rounded bg-[rgba(23,24,27,0.07)] dark:bg-[rgba(236,234,230,0.08)]"></div>
                            @endfor
                        </div>
                        <p class="es-run-muted text-sm">{{ $bCount }} photos from this set</p>
                    </div>
                @endforeach
            </div>

            <p class="es-run-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                Attendees add them with just a name and an email, and everything waits in an approval queue before it appears. Free covers 25 photos per schedule.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. The rooms                                                 -->
    <!-- ============================================================ -->
    <section id="rooms" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div>
                    <div class="es-run-corner mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                    <p class="es-run-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The rooms</p>
                    <h2 class="es-balance es-run-ink mb-5 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                        Main room and back room, <span class="es-run-mark">one link.</span>
                    </h2>
                    <p class="es-run-muted mb-6 text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        Sub-schedules split one schedule into strands, so somebody who only cares about the 80-cap room is not scrolling past two months of main-room shows to find it. Free on every plan.
                    </p>
                    <ul class="es-run-muted space-y-3" data-reveal-group="70">
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none text-[#a16207] dark:text-[#facc15]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>A residency is one recurring event with a day-of-week pattern, plus exceptions for the weeks you are dark.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none text-[#a16207] dark:text-[#facc15]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Embed the calendar on the site you already have, so the listings live where people look you up.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none text-[#a16207] dark:text-[#facc15]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Two-way sync with Google, Outlook and CalDAV keeps what is on the wall and what is in your phone from drifting apart.</span>
                        </li>
                    </ul>
                </div>

                <div data-reveal="panel">
                    <div class="es-run-card overflow-hidden">
                        @foreach ([['Main room', '450 cap', 'The Fell', 'Thu 12'], ['Back room', '80 cap', 'Oda solo', 'Fri 13'], ['Main room', '450 cap', 'Residency', 'Every Tue']] as $ri => [$rRoom, $rCap, $rShow, $rWhen])
                            <div class="flex items-center gap-3 px-5 py-4 @if ($ri > 0) border-t border-[rgba(23,24,27,0.08)] dark:border-[rgba(236,234,230,0.08)] @endif">
                                <div class="min-w-0 flex-1">
                                    <p class="es-run-ink truncate text-sm font-bold">{{ $rShow }}</p>
                                    <p class="es-run-muted truncate text-xs">{{ $rRoom }} &middot; {{ $rCap }}</p>
                                </div>
                                <span class="font-mono text-xs text-[#4b5158] dark:text-[#9aa1a9]">{{ $rWhen }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Who plays (fixed-dark band)                               -->
    <!-- ============================================================ -->
    <section id="playing" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-run-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(250, 204, 21, 0.12), rgba(250, 204, 21, 0) 60%); opacity: 0.5;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-6xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="es-run-corner mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                    <p class="es-run-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Who plays</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Let bands come to you, <span class="es-run-lit">with the details attached.</span>
                    </h2>
                </div>

                <div class="grid items-start gap-10 lg:grid-cols-2">
                    <div class="space-y-5" data-reveal-group="80">
                        <div class="es-run-card p-6" data-reveal>
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <h3 class="text-lg font-bold text-[#eceae6]">Accept requests</h3>
                                <span class="es-run-plan">Free</span>
                            </div>
                            <p class="text-sm text-[#9aa1a9]">Switch it on and artists submit a show from your public page. Everything waits on your Requests tab until you accept or decline it.</p>
                        </div>
                        <div class="es-run-card p-6" data-reveal>
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <h3 class="text-lg font-bold text-[#eceae6]">Ask for what you always chase</h3>
                                <span class="es-run-plan es-run-plan-pro">Pro</span>
                            </div>
                            <p class="text-sm text-[#9aa1a9]">Add your own questions to that form so set length, party size and a link arrive with the request instead of four emails later.</p>
                        </div>
                        <div class="es-run-card p-6" data-reveal>
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <h3 class="text-lg font-bold text-[#eceae6]">Nothing lands unannounced</h3>
                                <span class="es-run-plan">Free</span>
                            </div>
                            <p class="text-sm text-[#9aa1a9]">A declined request never touches your calendar, and an accepted one arrives as a real event you can add the running order to.</p>
                        </div>
                    </div>

                    <div data-reveal="panel">
                        <div class="es-run-card p-6">
                            <div class="mb-4 flex items-baseline justify-between gap-3 border-b border-[rgba(236,234,230,0.14)] pb-3">
                                <span class="es-run-tag">Requests</span>
                                <span class="font-mono text-xs text-[#9aa1a9]">3 waiting</span>
                            </div>
                            <div class="space-y-3">
                                @foreach ([['Kestrel Hall', 'Fri 2 May', '4-piece', '45 min'], ['Oda', 'Sat 10 May', 'Solo', '30 min'], ['The Fell', 'Thu 22 May', '5-piece', '70 min']] as [$qName, $qDate, $qSize, $qLen])
                                    <div class="flex items-center gap-3 rounded-lg bg-[rgba(236,234,230,0.05)] p-3">
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-bold text-[#eceae6]">{{ $qName }}</p>
                                            <p class="truncate text-xs text-[#9aa1a9]">{{ $qDate }} &middot; {{ $qSize }} &middot; {{ $qLen }}</p>
                                        </div>
                                        <span class="es-run-state flex-none" aria-hidden="true">Accept</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. The door                                                  -->
    <!-- ============================================================ -->
    <section id="door" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-run-corner mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                <p class="es-run-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The door</p>
                <h2 class="es-balance es-run-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    You keep <span class="es-run-mark">all of it.</span>
                </h2>
                <p class="es-run-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Event Schedule charges zero platform fees on ticket sales. You connect your own Stripe account, the money lands in it, and the only deduction is Stripe's own processing.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-3" data-reveal-group="100">
                <div class="es-run-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-run-ink text-lg font-bold">Tiers that move on the clock</h3>
                        <span class="es-run-plan es-run-plan-pro">Pro</span>
                    </div>
                    <p class="es-run-muted text-sm">Give a show more than one ticket type, each with its own sales window, plus add-ons and a rate that kicks in when someone buys several at once.</p>
                </div>
                <div class="es-run-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-run-ink text-lg font-bold">A QR on every ticket</h3>
                        <span class="es-run-plan es-run-plan-pro">Pro</span>
                    </div>
                    <p class="es-run-muted text-sm">Scan on the way in and the check-in dashboard counts who is actually inside, with a per-ticket breakdown so you can see which tier turned up.</p>
                </div>
                <div class="es-run-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-run-ink text-lg font-bold">Everyone gets their own</h3>
                        <span class="es-run-plan es-run-plan-pro">Pro</span>
                    </div>
                    <p class="es-run-muted text-sm">Per-attendee tickets give each guest in a group their own confirmation and their own code, so one person is not stood at the door holding six.</p>
                </div>
            </div>

            <p class="es-run-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                For a free show, registration with a capacity limit works on every plan, so the room still has a real number attached to it.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Everything else: bento                                    -->
    <!-- ============================================================ -->
    <section id="rest" class="scroll-mt-24 border-t border-[rgba(23,24,27,0.08)] py-20 dark:border-[rgba(236,234,230,0.08)] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-run-corner mb-6" data-reveal aria-hidden="true"><span>08</span></div>
                <p class="es-run-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Everything else</p>
                <h2 class="es-balance es-run-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    The rest of the week.
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">
                <!-- 1 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-run-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-run-ink text-xl font-bold">Tell them yourself</h3>
                                <span class="es-run-plan">Free</span>
                            </div>
                            <p class="es-run-muted mb-4">People follow your schedule and you email them when a show goes on sale. You can see open and click rates afterwards, so you know whether the announcement actually landed.</p>
                            <p class="es-run-muted text-sm">The numbers worth knowing before you plan around it: 10 emails a month on Free, 100 on Pro and 1,000 on Enterprise, counted per recipient rather than per send.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-run-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-run-ink text-xl font-bold">When it sells out</h3>
                                <span class="es-run-plan es-run-plan-pro">Pro</span>
                            </div>
                            <p class="es-run-muted">Turn on the waitlist and people join once tickets are gone. If one is released they are notified automatically instead of you working back through replies.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-run-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-run-ink text-xl font-bold">Know what is working</h3>
                                <span class="es-run-plan">Free</span>
                            </div>
                            <p class="es-run-muted mb-4">Built-in analytics show page views, the devices people are on, and where the traffic came from. Enough to tell whether the announcement did anything, without installing a thing.</p>
                            <p class="es-run-muted text-sm">Add a poll to a show and let the room vote on the support slot or which night a residency should move to.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-run-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-run-ink text-xl font-bold">Passes for the regulars</h3>
                                <span class="es-run-plan es-run-plan-pro">Pro</span>
                            </div>
                            <p class="es-run-muted">Sell a multi-use pass or a membership that runs across a season of shows, with its own usage tracking and cancellation policy.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-run-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-run-ink text-xl font-bold">The announcement post</h3>
                                <span class="es-run-plan es-run-plan-pro">Pro</span>
                            </div>
                            <p class="es-run-muted mb-4">Generate a graphic from a show in a story, square, portrait or landscape crop. It is built from the event, so the date, the room and the bill are already right.</p>
                            <p class="es-run-muted text-sm">
                                Running it online as well? Mark the show as an online event and paste the link to wherever you are streaming.
                                <a href="{{ marketing_url('/features/online-events') }}" class="es-run-link font-medium hover:underline">How online events work</a>
                            </p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-run-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-run-ink text-xl font-bold">Hold it back</h3>
                                <span class="es-run-plan">Free</span>
                            </div>
                            <p class="es-run-muted">A show you have not announced yet can sit on the calendar as a draft, visible to you and never published to the public page.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Perfect for                                               -->
    <!-- ============================================================ -->
    <section id="who" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-run-corner mb-6" data-reveal aria-hidden="true"><span>09</span></div>
                <h2 class="es-balance es-run-ink mb-4 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    Built for every kind of <span class="es-run-mark">music room</span>
                </h2>
                <p class="es-run-muted text-lg sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Eighty capacity or two thousand, the show day is the same shape.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <x-sub-audience-card
                    name="Concert Halls"
                    description="Seated performances, classical programmes and acoustic shows. Publish the interval and the running time, and sell a season pass."
                    icon-color="blue"
                    blog-slug="for-concert-halls"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Live Music Bars & Clubs"
                    description="Standing-room venues with regular programming. Build a local following for weekly shows."
                    icon-color="sky"
                    blog-slug="for-small-music-clubs"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Jazz Clubs"
                    description="Intimate sets, residencies and guest headliners. Two sets a night, each with its own start time on the page."
                    icon-color="cyan"
                    blog-slug="for-mid-size-music-venues"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Folk & Acoustic Venues"
                    description="Singer-songwriter nights, open mics and listening rooms. Create a space for acoustic performances."
                    icon-color="amber"
                    blog-slug="for-house-concerts"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Rock & Indie Venues"
                    description="Touring bands, local acts and multi-band bills. Every act on the bill gets its own set time and its own photos."
                    icon-color="rose"
                    blog-slug="for-multi-purpose-venues"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Outdoor Amphitheaters"
                    description="Seasonal programming and festival-style bills. Set the season up once as recurring dates and skip the weeks you are closed."
                    icon-color="emerald"
                    blog-slug="for-outdoor-amphitheaters"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. Three steps                                              -->
    <!-- ============================================================ -->
    <section class="scroll-mt-24 border-t border-[rgba(23,24,27,0.08)] py-20 dark:border-[rgba(236,234,230,0.08)] lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance es-run-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal>
                    Three steps
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="120">
                @foreach ([['01', 'Add the show', 'Create the event once, and use sub-schedules to keep the main room and the back room apart on the same link.'], ['02', 'Add the running order', 'Give the show its parts. Each act gets a name and a start time, and they publish in order on the event page.'], ['03', 'Open the door', 'Ticket types with their own sales windows, then scan QR codes on the night and watch the dashboard against your capacity.']] as [$stepNum, $stepTitle, $stepBody])
                    <div class="es-run-card p-7" data-reveal="panel">
                        <div class="mb-3 font-mono text-2xl font-black text-[#a16207] dark:text-[#facc15]">{{ $stepNum }}</div>
                        <h3 class="es-run-ink mb-2 text-lg font-bold">{{ $stepTitle }}</h3>
                        <p class="es-run-muted text-sm leading-relaxed">{{ $stepBody }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 11. Key features                                             -->
    <!-- ============================================================ -->
    <section class="border-t border-[rgba(23,24,27,0.08)] py-20 dark:border-[rgba(236,234,230,0.08)]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-run-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="Ticket types, QR check-in, and zero platform fees" :url="marketing_url('/features/ticketing')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Sub-schedules" description="Keep each room's listings apart on one link" :url="marketing_url('/features/sub-schedules')" icon-color="teal">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="Set a residency once, and skip the weeks you are dark" :url="marketing_url('/features/recurring-events')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Email the people who follow your venue, with open rates" :url="marketing_url('/features/newsletters')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-run-link inline-flex items-center font-medium hover:underline">
                    See all features
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    @include('marketing.partials.pricing-nudge')

    <!-- ============================================================ -->
    <!-- 12. Related pages                                            -->
    <!-- ============================================================ -->
    <section class="border-t border-[rgba(23,24,27,0.08)] py-16 dark:border-[rgba(236,234,230,0.08)]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-run-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4" data-reveal-group="70">
                @foreach ([['/for-venues', 'Venues'], ['/for-nightclubs', 'Nightclubs'], ['/for-bars', 'Bars'], ['/for-musicians', 'Musicians']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" class="es-run-hover es-run-card group flex flex-col p-5 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-run-hover-title es-run-ink mb-3 text-sm font-semibold transition-colors">For {{ $relName }}</span>
                        <span class="es-run-hover-arrow es-run-muted mt-auto inline-flex items-center gap-1 text-xs font-medium transition-colors">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
            <div class="mt-8 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-run-link inline-flex items-center font-medium hover:underline">
                    See all use cases
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 13. FAQ                                                      -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="es-run-corner mb-6" data-reveal aria-hidden="true"><span>10</span></div>
                <h2 class="es-balance es-run-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-run-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    What venue bookers ask before they move a calendar.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-run-hover es-run-card group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-run-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="flex-none font-mono text-sm font-bold text-[#a16207] dark:text-[#facc15]" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-run-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none text-[#4b5158] transition-transform group-open:rotate-180 dark:text-[#9aa1a9]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-run-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 14. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-run-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>
                <div class="relative z-10">
                    <p class="es-run-tag mb-4">Free forever</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Load-in is at two. <span class="es-run-lit">The page can be ready by one.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-400">
                        Put the whole day on one link, keep every penny of the door, and stop answering the same question fourteen times before soundcheck.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-venue" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="es-run-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] dark:text-[#17181b]">
                            <span class="relative z-10 flex items-center gap-2">
                                Create your calendar
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

    <!-- Desktop dot nav: pips labelled with call times -->
    <nav class="es-dotnav fixed top-1/2 z-40 hidden -translate-y-1/2 lg:block ltr:right-5 rtl:left-5" aria-label="Page sections">
        <ul class="glass flex flex-col items-center gap-1.5 rounded-full px-2 py-3">
            @foreach ($dotSections as [$sectionId, $sectionLabel])
                <li class="relative">
                    <a href="#{{ $sectionId }}" class="es-dot group block rounded-full" aria-label="{{ $sectionLabel }}">
                        <span class="es-dot-pip block h-2 w-2 rounded-full bg-gray-400/60 dark:bg-white/30"></span>
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border border-gray-200 bg-white px-3 py-1 font-mono text-xs font-medium text-gray-700 opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3 dark:border-white/10 dark:bg-[#17181b] dark:text-gray-300">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <x-marketing.related-pages />

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
