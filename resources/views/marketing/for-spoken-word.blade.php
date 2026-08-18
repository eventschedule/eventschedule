<x-marketing-layout>
    <x-slot name="title">Open Mic and Reading Schedules for Poets | Event Schedule</x-slot>
    <x-slot name="description">Run open mic sign-ups, reading series, and workshops from one link. Free registration with a capacity limit, recurring dates that skip the holidays, and zero platform fees on tickets.</x-slot>
    <x-slot name="breadcrumbTitle">For Spoken Word</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Spoken Word",
        "description": "Run open mic sign-ups, reading series, and workshops from one link. Free registration with a capacity limit, recurring dates, and zero platform fees on tickets.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Poets, Storytellers, and Open Mic Hosts"
        }
    }
    </script>
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Poets and Spoken Word",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Open Mic and Poetry Reading Scheduling Software",
        "operatingSystem": "Web",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Free forever"
        },
        "featureList": [
            "Free registration with an optional capacity limit for open mic slots",
            "Recurring weekly and monthly mics with date exceptions for holidays",
            "Sub-schedules that keep the mic, the reading series, and workshops apart",
            "A public submission form so performers can put themselves forward",
            "Custom questions on the registration form for what is being read",
            "Zero-fee ticket sales with QR check-in for featured readings",
            "Direct newsletters to the people who follow your schedule",
            "Fan photos, videos, and comments from the night with an approval queue",
            "Two-way Google, Outlook, and CalDAV calendar sync",
            "Auto-generated flyers and social graphics for each night",
            "Embeddable calendar for a venue, bookstore, or personal site",
            "Online and hybrid readings for people who cannot make the room"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "open mic schedule, poetry reading calendar, open mic sign up sheet, spoken word event management, poetry slam scheduling, storytelling event calendar, free open mic software",
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
        "name": "How to run an open mic sign-up list with Event Schedule",
        "description": "Put your open mic list online in three steps.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Start the list",
                "text": "Add the mic as a recurring event, turn on registration, and set how many spots there are. Skip a date when the room is closed for a holiday."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Share one link",
                "text": "Put the link in your bio, on the venue's site, and on the back of your chapbook, or embed the calendar on a page you already have."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Let them sign up",
                "text": "Poets take a spot themselves. You can see the list from anywhere, and the page stops taking names once every spot is gone."
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
           For-spoken-word "The Sign-Up Sheet" styles. The page is the
           clipboard by the door at an open mic. The SHEET is a physical
           object: it stays warm ivory in BOTH colour modes and only the
           room around it changes (a bright cafe wall in light mode, the
           back of the room in dark), so anything inside .es-sheet-paper
           deliberately carries no dark: variants. Two inks do all the
           work: the performers' ballpoint blue and the host's red
           felt-tip. Slot numbers 01..12 run down the page as the
           section numerals, each sitting in the sheet's red margin.

           Consequence of the fixed paper: any accent text placed on a
           fixed-dark band needs the always-lit "-lit" variant, or a
           light-mode visitor gets dark ink on a dark ground.

           BLADE RULE for this block: never use @supports probes here.
           A "#" hex inside a parenthesized at-rule condition breaks
           Blade compilation of every later parenthesized directive.
           ============================================================== */

        /* --- Accent text: ballpoint ink into felt-tip red --- */
        /* The stops are weighted late on purpose: a plain blue-to-red ramp
           spends its middle in a desaturated mauve, which both looks washed
           out and drifts toward a hue the brand rules rule out. Holding the
           ink flat to 35% keeps it reading as ballpoint that lands on the
           host's felt-tip. */
        .text-gradient-signup {
            background-image: linear-gradient(135deg, #1c3d6e 0%, #1c3d6e 35%, #b91c1c 88%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            -webkit-text-fill-color: transparent;
        }
        .dark .text-gradient-signup {
            background-image: linear-gradient(135deg, #93c5fd 0%, #93c5fd 35%, #f87171 88%);
        }
        /* Always-lit variant for the fixed-dark bands (both colour modes). */
        .text-gradient-signup-lit {
            background-image: linear-gradient(135deg, #93c5fd 0%, #93c5fd 35%, #f87171 88%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            -webkit-text-fill-color: transparent;
        }

        /* --- Eyebrow tags --- */
        .es-sheet-tag {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.28em;
            text-transform: uppercase;
            color: #1c3d6e;
        }
        .dark .es-sheet-tag,
        .es-sheet-band .es-sheet-tag { color: #93c5fd; }
        /* Inside the sheet the room never changes, so pin the ink. */
        .es-sheet-paper .es-sheet-tag { color: #1c3d6e; }

        /* --- Links and buttons --- */
        .es-sheet-link { color: #1c3d6e; }
        .es-sheet-link:hover { color: #b91c1c; }
        .dark .es-sheet-link { color: #93c5fd; }
        .dark .es-sheet-link:hover { color: #f87171; }

        .es-sheet-btn {
            background-image: linear-gradient(to right, #1c3d6e, #2f5d9e);
            box-shadow: 0 20px 40px -12px rgba(28, 61, 110, 0.45);
        }
        .es-sheet-btn:hover {
            background-image: linear-gradient(to right, #16305a, #1c3d6e);
            box-shadow: 0 24px 48px -12px rgba(28, 61, 110, 0.55);
        }

        /* --- FAQ / related-card hover recolor --- */
        .es-sheet-hover:hover { border-color: rgba(28, 61, 110, 0.4); }
        .dark .es-sheet-hover:hover { border-color: rgba(147, 197, 253, 0.35); }
        .es-sheet-hover:hover .es-sheet-hover-title,
        .es-sheet-hover:hover .es-sheet-hover-arrow { color: #1c3d6e; }
        .dark .es-sheet-hover:hover .es-sheet-hover-title,
        .dark .es-sheet-hover:hover .es-sheet-hover-arrow { color: #93c5fd; }

        /* --- The back of the room: fixed-dark band, identical in both modes --- */
        .es-sheet-band {
            background-color: #0f0d0b;
            background-image: radial-gradient(120% 100% at 50% 0%, #1e1912 0%, #13100c 55%, #0a0807 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }

        /* --- The sheet: warm ivory in BOTH modes --- */
        .es-sheet-paper {
            position: relative;
            background-image: linear-gradient(170deg, #fdfaf1, #f3ecdb);
            border: 1px solid #ddd2b8;
            border-radius: 0.75rem;
            color: #3f3a33;
            box-shadow: 0 18px 40px -22px rgba(20, 24, 33, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.75);
        }
        /* The legal-pad red margin rule, plus room for it. */
        .es-sheet-margin { padding-inline-start: 3rem; }
        .es-sheet-margin::before {
            content: "";
            position: absolute;
            top: 0.5rem;
            bottom: 0.5rem;
            inset-inline-start: 2.1rem;
            width: 1px;
            background: rgba(185, 28, 28, 0.4);
            pointer-events: none;
        }
        @media (min-width: 640px) {
            .es-sheet-margin { padding-inline-start: 3.75rem; }
            .es-sheet-margin::before { inset-inline-start: 2.75rem; }
        }

        /* --- The clipboard behind the sheet: hardboard, fixed in both modes --- */
        .es-sheet-board {
            background-image: linear-gradient(165deg, #8a7554, #6d5a3e 55%, #57472f);
            border: 1px solid #4a3c28;
            border-radius: 1.1rem;
            box-shadow: 0 30px 60px -28px rgba(12, 10, 8, 0.75);
        }
        .es-sheet-clip {
            width: 6.5rem;
            height: 1.5rem;
            border-radius: 0.4rem 0.4rem 0.55rem 0.55rem;
            background-image: linear-gradient(180deg, #e6e3dc, #a9a49a 60%, #7d786e);
            border: 1px solid #6f6a60;
            box-shadow: 0 4px 10px -3px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.6);
        }

        /* --- One ruled line on the sheet --- */
        .es-sheet-row {
            display: flex;
            align-items: baseline;
            gap: 0.6rem;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(28, 61, 110, 0.16);
        }
        .es-sheet-row:last-child { border-bottom: 0; }
        /* Dotted leader that eats the space between a name and its time. */
        .es-sheet-leader {
            flex: 1 1 auto;
            min-width: 1rem;
            align-self: flex-end;
            margin-bottom: 0.28rem;
            border-bottom: 1px dotted rgba(63, 58, 51, 0.42);
        }
        .es-sheet-num {
            flex: none;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            color: #1c3d6e;
            letter-spacing: 0.02em;
        }

        /* --- Handwriting. System stacks only: marketing pages load no
               webfont, so this degrades to the generic cursive face and,
               failing that, to the inherited stack. Never used for
               anything the layout has to measure. --- */
        .es-sheet-hand {
            font-family: "Bradley Hand", "Segoe Script", "Ink Free", "Chalkboard SE", "Comic Sans MS", cursive;
            color: #1c3d6e;
            letter-spacing: 0.01em;
        }
        .es-sheet-hand-red { color: #b91c1c; }

        /* --- The caret waiting on the next empty line --- */
        .es-sheet-caret {
            display: inline-block;
            width: 0.5rem;
            height: 1.05em;
            vertical-align: -0.16em;
            background: #1c3d6e;
        }
        html.es-anim .es-sheet-caret { animation: es-sheet-blink 1.15s step-end infinite; }
        @keyframes es-sheet-blink { 0%, 100% { opacity: 1; } 50% { opacity: 0; } }

        /* --- The host's marks in red felt-tip --- */
        .es-sheet-stamp {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.15rem 0.55rem;
            border: 2px solid rgba(185, 28, 28, 0.75);
            border-radius: 0.3rem;
            color: #b91c1c;
            font-size: 0.65rem;
            font-weight: 800;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            transform: rotate(-6deg);
        }
        /* The circled feature. The stroke length lives on an always-active
           rule so the ring is fully drawn without JS or with motion off;
           only the undrawn pre-state is gated. */
        .es-sheet-ring { stroke-dasharray: 340; stroke-dashoffset: 0; transition: stroke-dashoffset 1.2s ease 0.4s; }
        html.es-anim [data-reveal]:not(.is-revealed) .es-sheet-ring { stroke-dashoffset: 340; }
        /* The crossed-off no-show. */
        .es-sheet-strike { position: relative; }
        .es-sheet-strike::after {
            content: "";
            position: absolute;
            left: -0.15rem;
            right: -0.15rem;
            top: 52%;
            height: 2px;
            background: rgba(185, 28, 28, 0.8);
            transform-origin: left center;
            transform: scaleX(1);
            transition: transform 0.7s cubic-bezier(0.22, 1, 0.36, 1) 0.5s;
        }
        html.es-anim [data-reveal]:not(.is-revealed) .es-sheet-strike::after { transform: scaleX(0); }

        /* --- Section slot numeral: a torn tab off the sheet's margin --- */
        .es-sheet-corner {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            padding: 0.4rem 0.85rem 0.4rem 0.6rem;
            border-radius: 0.5rem;
            background-image: linear-gradient(170deg, #fdfaf1, #f3ecdb);
            border: 1px solid #ddd2b8;
            box-shadow: 0 8px 18px -12px rgba(20, 24, 33, 0.55);
            font-weight: 900;
            font-variant-numeric: tabular-nums;
            color: #1c3d6e;
            letter-spacing: 0.04em;
        }
        .es-sheet-corner::before {
            content: "";
            width: 2px;
            align-self: stretch;
            border-radius: 1px;
            background: rgba(185, 28, 28, 0.6);
        }

        /* --- Torn paper scraps drifting behind the hero --- */
        .es-sheet-scrap {
            position: absolute;
            border-radius: 0.2rem;
            background-image: linear-gradient(170deg, rgba(253, 250, 241, 0.9), rgba(243, 236, 219, 0.75));
            border: 1px solid rgba(221, 210, 184, 0.8);
            box-shadow: 0 10px 22px -14px rgba(20, 24, 33, 0.55);
            opacity: 0.55;
        }
        .dark .es-sheet-scrap { opacity: 0.14; }
        html.es-anim .es-sheet-scrap {
            animation: es-sheet-drift 15s ease-in-out infinite alternate;
            animation-delay: var(--d, 0s);
        }
        @keyframes es-sheet-drift {
            from { transform: translate3d(0, 0, 0) rotate(var(--r, -6deg)); }
            to   { transform: translate3d(0, -18px, 0) rotate(calc(var(--r, -6deg) + 5deg)); }
        }

        /* --- Chips in the hero marquee --- */
        .es-sheet-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            white-space: nowrap;
            padding: 0.4rem 0.9rem;
            border-radius: 9999px;
            border: 1px solid rgba(28, 61, 110, 0.22);
            background: rgba(253, 250, 241, 0.75);
            color: #1c3d6e;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        .dark .es-sheet-chip {
            border-color: rgba(147, 197, 253, 0.22);
            background: rgba(255, 255, 255, 0.05);
            color: #bfd7ff;
        }

        /* --- Plan tag: which tier a line on the sheet needs --- */
        .es-sheet-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.3rem;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            border: 1px solid rgba(28, 61, 110, 0.3);
            color: #1c3d6e;
        }
        .es-sheet-plan-pro {
            border-color: rgba(185, 28, 28, 0.45);
            color: #b91c1c;
        }

        /* --- Shared-system recolors. The cursor spotlight and the dot-nav
               pips are hard-coded brand blue in marketing.css. --- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(28, 61, 110, 0.14), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(147, 197, 253, 0.14), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(28, 61, 110, 0.7); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(147, 197, 253, 0.7); }
        .es-dot.is-active .es-dot-pip { background: linear-gradient(180deg, #1c3d6e, #b91c1c); }
        .dark .es-dot.is-active .es-dot-pip { background: linear-gradient(180deg, #93c5fd, #f87171); }

        /* --- Shared classes that break the fixed-object contract inside the
               bands. .grid-overlay flips its line colour with the colour
               mode (marketing.css:118/125) and .es-claim:focus-within is
               hard-coded brand blue (marketing.css:695), so both are pinned
               here to the band's own always-dark treatment. --- */
        .es-sheet-band .grid-overlay {
            background-image:
                linear-gradient(rgba(244, 239, 230, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(244, 239, 230, 0.05) 1px, transparent 1px);
        }
        /* .animate-shimmer is also mode-dependent (white 0.3 light / 0.15
           dark, marketing.css:67/72); the band is always dark, so pin it. */
        .es-sheet-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-sheet-band .es-claim:focus-within {
            border-color: rgba(147, 197, 253, 0.75);
            box-shadow: 0 0 0 4px rgba(147, 197, 253, 0.22);
        }

        /* --- Focus rings. Most of the page is paper rather than the shared
               card components, so the ring at marketing.css:248 does not
               reach it. This rule is load-bearing for keyboard users. --- */
        #es-sheet-page a:focus-visible,
        #es-sheet-page summary:focus-visible,
        #es-sheet-page button:focus-visible {
            outline: 2px solid #1c3d6e;
            outline-offset: 3px;
        }
        .dark #es-sheet-page a:focus-visible,
        .dark #es-sheet-page summary:focus-visible,
        .dark #es-sheet-page button:focus-visible {
            outline-color: #93c5fd;
        }
        /* Inside the sheet the ground never changes, so keep the ink ring. */
        .dark .es-sheet-paper a:focus-visible,
        .dark .es-sheet-paper summary:focus-visible,
        .dark .es-sheet-paper button:focus-visible {
            outline-color: #1c3d6e;
        }

        /* --- The signature on the last line of the finale sheet --- */
        .es-sheet-signline {
            border-bottom: 2px solid rgba(28, 61, 110, 0.45);
            min-height: 3.1rem;
        }

        /* --- Reduced motion: every page-local effect resolves to its
               finished state, nothing moves. --- */
        @media (prefers-reduced-motion: reduce) {
            html.es-anim .es-sheet-caret,
            html.es-anim .es-sheet-scrap {
                animation: none !important;
            }
            .es-sheet-ring {
                stroke-dashoffset: 0 !important;
                transition: none !important;
            }
            .es-sheet-strike::after {
                transform: scaleX(1) !important;
                transition: none !important;
            }
            .es-sheet-scrap { transform: none !important; }
        }
    </style>

    @php
        $faqs = [
            [
                'q' => 'Is Event Schedule free for open mics and readings?',
                'a' => 'Yes. Sharing your schedule, running recurring nights, taking free registrations with a capacity limit, and syncing with Google, Outlook, or CalDAV are all free forever. Ticketing, event graphics, and custom questions on the sign-up form are on the Pro plan at '.plan_price($proMonthly).' a month, and Event Schedule charges zero platform fees on tickets.',
            ],
            [
                'q' => 'Can poets sign up for a slot themselves?',
                'a' => 'Yes. Turn on registration for the night and set how many spots there are. Performers claim a spot from the event page, the page shows how many are left, and it stops taking names once they are gone. Registration is free on every plan.',
            ],
            [
                'q' => "Can I ask performers what they're reading?",
                'a' => 'Yes, on the Pro plan. Custom fields let you add your own questions to the sign-up form, so a poet answers "what are you reading", "how long is it", or a content note when they take a spot. Text answers can be checked against a pattern with a hint you write. You can also let people submit a whole event for your schedule, and their answers land on your Requests tab.',
            ],
            [
                'q' => 'How do I run a mic that happens every second Tuesday?',
                'a' => 'Set it up once as a recurring event with a day-of-week pattern, then add date exceptions for the weeks the room is closed or the holiday lands on your night. Recurring events and exceptions are free on every plan.',
            ],
            [
                'q' => 'Can I keep the open mic, the feature series, and workshops on one page?',
                'a' => 'Yes. Sub-schedules split one schedule into strands, so the weekly mic, the featured reading series, and your workshops each sit in their own section of the same link. Sub-schedules are free on every plan.',
            ],
            [
                'q' => 'Can I sell tickets to a featured reading?',
                'a' => 'Yes, on the Pro plan. Connect your Stripe account and sell tickets straight from your schedule with QR check-in at the door. Event Schedule takes zero platform fees, so you only pay Stripe processing. You can also sell a pass that covers a whole season of the series.',
            ],
        ];

        $dotSections = [
            ['top', 'The list'],
            ['tonight', 'Tonight'],
            ['slots', 'The slots'],
            ['whos-up', "Who's up"],
            ['order', 'Running order'],
            ['rest', 'The rest of it'],
            ['rooms', 'The rooms'],
            ['who', 'Perfect for'],
            ['steps', 'Three steps'],
            ['faq', 'Questions'],
            ['claim', 'Line 01'],
        ];
    @endphp

    <div id="es-sheet-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the clipboard by the door (slot 01)                 -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden bg-[#faf8f4] py-16 dark:bg-[#0d0c0b]">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(28, 61, 110, 0.24), rgba(28, 61, 110, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(185, 28, 28, 0.18), rgba(185, 28, 28, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <div class="es-sheet-scrap left-[6%] top-[20%] h-16 w-12" style="--d: 0s; --r: -8deg;"></div>
            <div class="es-sheet-scrap left-[86%] top-[16%] h-20 w-14" style="--d: 2.6s; --r: 7deg;"></div>
            <div class="es-sheet-scrap left-[89%] top-[64%] h-14 w-11" style="--d: 5.2s; --r: -4deg;"></div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-[#1c3d6e] dark:text-[#93c5fd]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">For poets, storytellers, and open mic hosts</span>
            </div>

            <h1 class="es-balance mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Every open mic has a list.</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-signup">Yours should outlive the night.</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-600 dark:text-gray-400 sm:text-xl">
                Sign-ups, features, workshops, and book launches on one link. Free registration with a capacity limit, recurring dates that skip the holidays, and no platform fees when you sell.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#slots" class="glass group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See how the list works
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
                <a href="{{ app_url('/sign_up?type=talent') }}" class="es-sheet-btn group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                    Start your list
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- The clipboard itself -->
            <div class="es-fade-up es-d-4 mx-auto mt-14 max-w-lg text-start" data-reveal>
                <div class="es-sheet-board relative -rotate-1 px-3 pb-3 pt-7 sm:px-4 sm:pb-4">
                    <div class="es-sheet-clip absolute -top-3 left-1/2 -translate-x-1/2" aria-hidden="true"></div>
                    <div class="es-sheet-paper es-sheet-margin px-4 py-4 sm:px-5 sm:py-5">
                        <div class="mb-1 flex items-baseline justify-between gap-3 border-b-2 border-[rgba(28,61,110,0.3)] pb-2">
                            <span class="es-sheet-tag">Tuesday open mic</span>
                            <span class="font-mono text-[0.7rem] font-semibold text-[#1c3d6e]">12 SLOTS</span>
                        </div>

                        <div class="es-sheet-row">
                            <span class="es-sheet-num">01</span>
                            <span class="es-sheet-hand text-lg">mara g.</span>
                            <span class="es-sheet-leader"></span>
                            <span class="font-mono text-xs text-[#6b6459]">3 min</span>
                        </div>
                        <div class="es-sheet-row">
                            <span class="es-sheet-num">02</span>
                            <span class="es-sheet-hand text-lg">dez</span>
                            <span class="es-sheet-leader"></span>
                            <span class="font-mono text-xs text-[#6b6459]">3 min</span>
                        </div>
                        <div class="es-sheet-row">
                            <span class="es-sheet-num">03</span>
                            <span class="relative inline-flex items-center">
                                <span class="es-sheet-hand es-sheet-hand-red text-lg">feature</span>
                                <svg class="pointer-events-none absolute -inset-x-3 -inset-y-2 h-[calc(100%+1rem)] w-[calc(100%+1.5rem)]" viewBox="0 0 120 44" preserveAspectRatio="none" fill="none" aria-hidden="true">
                                    <ellipse class="es-sheet-ring" cx="60" cy="22" rx="55" ry="18" stroke="rgba(185,28,28,0.75)" stroke-width="2" stroke-linecap="round" />
                                </svg>
                            </span>
                            <span class="es-sheet-leader"></span>
                            <span class="font-mono text-xs text-[#6b6459]">15 min</span>
                        </div>
                        <div class="es-sheet-row">
                            <span class="es-sheet-num">04</span>
                            <span class="es-sheet-hand text-lg es-sheet-strike">jonah</span>
                            <span class="es-sheet-leader"></span>
                            <span class="font-mono text-xs text-[#6b6459]">no show</span>
                        </div>
                        <div class="es-sheet-row">
                            <span class="es-sheet-num">05</span>
                            <span class="es-sheet-caret" aria-hidden="true"></span>
                            <span class="es-sheet-leader"></span>
                        </div>
                        <div class="es-sheet-row">
                            <span class="es-sheet-num">06</span>
                            <span class="es-sheet-leader"></span>
                        </div>

                        <p class="mt-3 flex items-center justify-between gap-3 pt-1 text-xs">
                            <span class="text-[#6b6459]">Anyone with the link can take a spot.</span>
                            <span class="font-mono font-bold text-[#b91c1c]">8 left</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Night-type marquee -->
            <div class="es-fade-up es-d-4 pointer-events-auto mx-auto mt-8 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach (['Open Mic', 'Slam', 'Featured Reading', 'Storytelling', 'Workshop', 'Book Launch', 'Lit Fest', 'Chapbook Release', 'Salon', 'Author Q&A'] as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-sheet-chip">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. Tonight: the list that only exists in the room (slot 02)  -->
    <!-- ============================================================ -->
    <section id="tonight" class="relative scroll-mt-24 bg-[#faf8f4] px-2 py-14 dark:bg-[#0d0c0b] sm:px-4 lg:py-20">
        <div class="es-sheet-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-sheet-corner mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                    <p class="es-sheet-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Tonight, and then never again</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        The list exists in one room, on one sheet, <span class="text-gradient-signup-lit">for about four hours.</span>
                    </h2>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    <div class="es-sheet-paper -rotate-2 p-6" data-reveal="panel">
                        <p class="es-sheet-tag mb-3">The clipboard</p>
                        <h3 class="mb-2 text-lg font-bold">One copy, by the door</h3>
                        <p class="text-sm text-[#6b6459]">Whoever arrives first writes first. Everyone else texts you to ask if there is still room.</p>
                    </div>
                    <div class="es-sheet-paper p-6" data-reveal="panel">
                        <p class="es-sheet-tag mb-3">The group chat</p>
                        <h3 class="mb-2 text-lg font-bold">
                            <span data-count-to="212">212</span> unread
                        </h3>
                        <p class="text-sm text-[#6b6459]">The date is in there somewhere, above four photos of a dog and a poll about a bar.</p>
                    </div>
                    <div class="es-sheet-paper rotate-2 p-6" data-reveal="panel">
                        <p class="es-sheet-tag mb-3">The DMs</p>
                        <h3 class="mb-2 text-lg font-bold">
                            &ldquo;Can I get on?&rdquo; &times;<span data-count-to="9">9</span>
                        </h3>
                        <p class="text-sm text-[#6b6459]">Nine separate conversations, three platforms, and one of them is asking about a night that already happened.</p>
                    </div>
                </div>

                <p class="mt-10 text-center text-gray-300" data-reveal>
                    A schedule holds the same list, and it is still there on Wednesday.
                    <a href="#slots" class="inline-flex items-center gap-1 font-semibold text-[#93c5fd] transition-all hover:gap-2">
                        See the slots
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The slots: one link is the list (slot 03)                 -->
    <!-- ============================================================ -->
    <section id="slots" class="scroll-mt-24 bg-white py-20 dark:bg-[#111010] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-sheet-corner mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                <p class="es-sheet-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">One link is the list</p>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Put the sheet somewhere <span class="text-gradient-signup">the room cannot lose it.</span>
                </h2>
                <p class="mt-5 text-lg text-gray-600 dark:text-gray-400" data-reveal style="--reveal-delay: 0.15s;">
                    Three things do almost all the work, and all three are on the free plan.
                </p>
            </div>

            <div class="mx-auto max-w-3xl" data-reveal="panel">
                <div class="es-sheet-paper es-sheet-margin px-5 py-6 sm:px-7 sm:py-8">
                    <div class="es-sheet-row items-start">
                        <span class="es-sheet-num pt-1">01</span>
                        <div class="min-w-0 flex-1">
                            <div class="mb-1 flex flex-wrap items-center gap-2">
                                <h3 class="text-lg font-bold">Registration, with a cap</h3>
                                <span class="es-sheet-plan">Free</span>
                            </div>
                            <p class="text-sm leading-relaxed text-[#6b6459]">
                                Turn on registration for the night and say how many spots there are. The page shows how many are left, and once they are gone it stops taking names instead of quietly overbooking you.
                            </p>
                        </div>
                    </div>

                    <div class="es-sheet-row items-start">
                        <span class="es-sheet-num pt-1">02</span>
                        <div class="min-w-0 flex-1">
                            <div class="mb-1 flex flex-wrap items-center gap-2">
                                <h3 class="text-lg font-bold">Every second Tuesday, minus the holiday</h3>
                                <span class="es-sheet-plan">Free</span>
                            </div>
                            <p class="text-sm leading-relaxed text-[#6b6459]">
                                Set the pattern once as a recurring event, then add exceptions for the weeks the room is closed. You are not rebuilding the same night twenty-six times a year.
                            </p>
                        </div>
                    </div>

                    <div class="es-sheet-row items-start">
                        <span class="es-sheet-num pt-1">03</span>
                        <div class="min-w-0 flex-1">
                            <div class="mb-1 flex flex-wrap items-center gap-2">
                                <h3 class="text-lg font-bold">The mic, the series, the workshop</h3>
                                <span class="es-sheet-plan">Free</span>
                            </div>
                            <p class="text-sm leading-relaxed text-[#6b6459]">
                                Sub-schedules split one link into strands, so the weekly mic, the featured reading series, and your workshops each get their own section without needing their own page.
                            </p>
                        </div>
                    </div>

                    <p class="mt-5 border-t border-[rgba(28,61,110,0.16)] pt-4 text-sm text-[#6b6459]">
                        Free also covers unlimited events, two-way Google, Outlook, and CalDAV sync, an embeddable calendar, online and hybrid readings, and an .ics download for anyone who wants the date in their own calendar.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Who's up: submissions and the questions you ask (slot 04) -->
    <!-- ============================================================ -->
    <section id="whos-up" class="scroll-mt-24 border-t border-gray-200 bg-[#f6f2ea] py-20 dark:border-white/5 dark:bg-[#141211] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div>
                    <div class="es-sheet-corner mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                    <p class="es-sheet-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Who's up</p>
                    <h2 class="es-balance mb-5 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                        Stop asking &ldquo;what are you reading&rdquo; <span class="text-gradient-signup">at the door.</span>
                    </h2>
                    <p class="mb-6 text-lg leading-relaxed text-gray-600 dark:text-gray-400" data-reveal style="--reveal-delay: 0.15s;">
                        You can let people put an event forward for your schedule at all, on any plan, and their answers land on your Requests tab. On Pro you add your own questions to the sign-up form, so the things you would otherwise shout across a loud room arrive written down.
                    </p>
                    <ul class="space-y-3 text-gray-600 dark:text-gray-400" data-reveal-group="70">
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none text-[#1c3d6e] dark:text-[#93c5fd]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Ask what they are reading, how long it runs, and whether it needs a content note.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none text-[#1c3d6e] dark:text-[#93c5fd]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Text answers can be checked against a pattern, with a hint you write, in the browser and again on the server.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none text-[#1c3d6e] dark:text-[#93c5fd]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Export the answers with your sales when you need a running order on paper after all.</span>
                        </li>
                    </ul>
                </div>

                <div data-reveal="panel">
                    <div class="es-sheet-paper es-sheet-margin px-5 py-6 sm:px-7 sm:py-7">
                        <div class="mb-4 flex items-baseline justify-between gap-3 border-b-2 border-[rgba(28,61,110,0.3)] pb-2">
                            <span class="es-sheet-tag">Take a spot</span>
                            <span class="es-sheet-plan es-sheet-plan-pro">Pro</span>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <p class="mb-1 text-[0.7rem] font-bold uppercase tracking-widest text-[#6b6459]">Your name</p>
                                <p class="es-sheet-hand border-b border-[rgba(28,61,110,0.25)] pb-1 text-xl">mara g.</p>
                            </div>
                            <div>
                                <p class="mb-1 text-[0.7rem] font-bold uppercase tracking-widest text-[#6b6459]">What are you reading?</p>
                                <p class="es-sheet-hand border-b border-[rgba(28,61,110,0.25)] pb-1 text-xl">two new ones + the bridge poem</p>
                            </div>
                            <div class="flex gap-4">
                                <div class="flex-1">
                                    <p class="mb-1 text-[0.7rem] font-bold uppercase tracking-widest text-[#6b6459]">How long?</p>
                                    <p class="es-sheet-hand border-b border-[rgba(28,61,110,0.25)] pb-1 text-xl">3 min</p>
                                </div>
                                <div class="flex-1">
                                    <p class="mb-1 text-[0.7rem] font-bold uppercase tracking-widest text-[#6b6459]">Content note</p>
                                    <p class="es-sheet-hand border-b border-[rgba(28,61,110,0.25)] pb-1 text-xl">grief</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 flex items-center justify-between gap-3 border-t border-[rgba(28,61,110,0.16)] pt-4">
                            <span class="font-mono text-xs text-[#6b6459]">Spot 05 of 12</span>
                            <span class="es-sheet-stamp">Saved</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. The running order: the same list, lit (slot 05)           -->
    <!-- ============================================================ -->
    <section id="order" class="relative scroll-mt-24 bg-[#f6f2ea] px-2 py-14 dark:bg-[#141211] sm:px-4 lg:py-20">
        <div class="es-sheet-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(28, 61, 110, 0.3), rgba(28, 61, 110, 0) 60%); opacity: 0.55;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 60%, rgba(185, 28, 28, 0.22), rgba(185, 28, 28, 0) 60%); opacity: 0.5;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-6xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="es-sheet-corner mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                    <p class="es-sheet-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Doors</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        The same list, <span class="text-gradient-signup-lit">in your hand at the back of the room.</span>
                    </h2>
                </div>

                <div class="grid items-center gap-12 lg:grid-cols-2">
                    <div class="mx-auto w-full max-w-[17rem]" data-reveal="panel">
                        <!-- The frame stays light on the dark band: it reads as a lit screen. -->
                        <div class="rounded-[2rem] border-4 border-[#2a2724] bg-white p-3 shadow-2xl">
                            <div class="mb-3 flex items-center justify-between px-1">
                                <span class="text-[0.65rem] font-bold uppercase tracking-widest text-gray-600">Tue 8:00 PM</span>
                                <span class="rounded-full bg-[#b91c1c] px-2 py-0.5 text-[0.6rem] font-bold uppercase tracking-wider text-white">Full</span>
                            </div>
                            <div class="space-y-1.5">
                                @foreach ([['01', 'Mara G.', '3 min', false], ['02', 'Dez', '3 min', false], ['03', 'Feature: A. Oyelaran', '15 min', true], ['04', 'Jonah', '3 min', false], ['05', 'Priya', '3 min', false], ['06', 'Tomas', '3 min', false]] as [$ordNum, $ordName, $ordLen, $ordFeature])
                                    <div class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-xs @if ($ordFeature) bg-[#fdeaea] font-bold text-[#b91c1c] @else bg-gray-50 text-gray-700 @endif">
                                        <span class="font-mono text-[0.65rem] text-gray-600">{{ $ordNum }}</span>
                                        <span class="min-w-0 flex-1 truncate">{{ $ordName }}</span>
                                        <span class="font-mono text-[0.65rem] text-gray-600">{{ $ordLen }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <p class="mt-3 px-1 text-[0.65rem] text-gray-600">Updates as people sign up.</p>
                        </div>
                    </div>

                    <div class="space-y-5" data-reveal-group="80">
                        <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-6 backdrop-blur-sm" data-reveal>
                            <h3 class="mb-2 text-lg font-semibold text-white">The night promotes itself</h3>
                            <p class="text-sm text-gray-400">Generate a flyer from the event and post it, instead of rebuilding the same graphic in a design tool every second Tuesday. <span class="es-sheet-plan es-sheet-plan-pro ms-1 align-middle" style="border-color: rgba(248,113,113,0.5); color: #fca5a5;">Pro</span></p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-6 backdrop-blur-sm" data-reveal>
                            <h3 class="mb-2 text-lg font-semibold text-white">It is in their calendar, not just yours</h3>
                            <p class="text-sm text-gray-400">Two-way sync with Google, Outlook, and CalDAV, and an .ics download on every event and every recurring date.</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-6 backdrop-blur-sm" data-reveal>
                            <h3 class="mb-2 text-lg font-semibold text-white">It lives on the venue's site too</h3>
                            <p class="text-sm text-gray-400">Embed the calendar on the bookstore's page or your own site, so the list is wherever people already look.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. The rest of it: bento (slot 06)                           -->
    <!-- ============================================================ -->
    <section id="rest" class="scroll-mt-24 bg-white py-20 dark:bg-[#111010] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-sheet-corner mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                <p class="es-sheet-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The rest of it</p>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything a scene needs <span class="text-gradient-signup">that is not the writing.</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">
                <!-- 1 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">The scene's mailing list</h3>
                                <span class="es-sheet-plan dark:border-[rgba(147,197,253,0.4)] dark:text-[#93c5fd]">Free</span>
                            </div>
                            <p class="mb-4 text-gray-600 dark:text-gray-400">
                                People follow your schedule and you email them directly when the next night is up. No algorithm deciding which regulars find out.
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Free covers 10 newsletter emails a month and Pro raises it to 100, counted per recipient. For a room of regulars that is real, and it is worth knowing the number before you plan around it.
                            </p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Clips from the night</h3>
                                <span class="es-sheet-plan dark:border-[rgba(147,197,253,0.4)] dark:text-[#93c5fd]">Free</span>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400">
                                Attendees add photos, video, and comments to the event with just a name and an email. Everything waits in an approval queue, so the page stays yours. Free covers 25 photos per schedule.
                            </p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">When the feature is ticketed</h3>
                                <span class="es-sheet-plan es-sheet-plan-pro dark:border-[rgba(248,113,113,0.5)] dark:text-[#fca5a5]">Pro</span>
                            </div>
                            <p class="mb-4 text-gray-600 dark:text-gray-400">
                                Connect Stripe and sell straight from the schedule, with QR check-in at the door. Event Schedule takes zero platform fees, so what is left after Stripe's processing is yours.
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Discount codes for the regulars, and a pass that covers a whole season of the series rather than one night at a time.
                            </p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Let the room decide</h3>
                                <span class="es-sheet-plan es-sheet-plan-pro dark:border-[rgba(248,113,113,0.5)] dark:text-[#fca5a5]">Pro</span>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400">
                                Put a poll on the event and let people vote on the theme, the next feature, or which night of the month the mic should move to.
                            </p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Read to the people who could not come</h3>
                                <span class="es-sheet-plan dark:border-[rgba(147,197,253,0.4)] dark:text-[#93c5fd]">Free</span>
                            </div>
                            <p class="mb-4 text-gray-600 dark:text-gray-400">
                                Mark a night as an online event and the link sits on the same schedule as the in-person ones. Hybrid workshops and virtual salons do not need a second home.
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Analytics on the free plan show which nights people actually opened, which is a better read on the scene than a like count.
                            </p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Ask afterwards</h3>
                                <span class="es-sheet-plan es-sheet-plan-pro dark:border-[rgba(248,113,113,0.5)] dark:text-[#fca5a5]">Pro</span>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400">
                                Collect star ratings and written comments from people who were there, so the next night is planned on something better than the vibe at the bar.
                            </p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. The rooms: where poetry happens (slot 07)                 -->
    <!-- ============================================================ -->
    <section id="rooms" class="scroll-mt-24 border-t border-gray-200 bg-[#f6f2ea] py-20 dark:border-white/5 dark:bg-[#141211] lg:py-28">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-sheet-corner mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Where the list <span class="text-gradient-signup">goes up</span>
                </h2>
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-400" data-reveal style="--reveal-delay: 0.1s;">
                    One schedule covers all of them, whether you host the night or just read at it.
                </p>
            </div>

            <div data-reveal="panel">
                <div class="es-sheet-paper es-sheet-margin px-5 py-5 sm:px-7 sm:py-6">
                    @foreach ([['01', 'Coffee shops', 'Weeknight mics with a sign-up by the register'], ['02', 'Bookstores', 'Launches, signings, and author Q&As'], ['03', 'Bars and lounges', 'Late slams and monthly showcases'], ['04', 'Universities', 'Student series, visiting readers, workshops'], ['05', 'Lit festivals', 'Multi-day programmes across several rooms'], ['06', 'Online', 'Virtual salons and hybrid workshops']] as [$roomNum, $roomName, $roomBlurb])
                        <div class="es-sheet-row">
                            <span class="es-sheet-num">{{ $roomNum }}</span>
                            <span class="font-semibold">{{ $roomName }}</span>
                            <span class="es-sheet-leader"></span>
                            <span class="hidden text-xs text-[#6b6459] sm:inline">{{ $roomBlurb }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Perfect for (slot 08)                                     -->
    <!-- ============================================================ -->
    <section id="who" class="scroll-mt-24 bg-white py-20 dark:bg-[#111010] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-sheet-corner mb-6" data-reveal aria-hidden="true"><span>08</span></div>
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    Built for how poets <span class="text-gradient-signup">actually work</span>
                </h2>
                <p class="text-lg text-gray-600 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Whether you're on the slam circuit, running the room, or launching a collection
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <!-- Slam Poets -->
                <x-sub-audience-card
                    name="Slam Poets"
                    description="Competition circuit, team slams, regional bouts. Track your season and let fans follow where you're bouting next."
                    icon-color="rose"
                    blog-slug="for-slam-poets"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Spoken Word Artists -->
                <x-sub-audience-card
                    name="Spoken Word Artists"
                    description="Performance poetry with music, movement, multimedia. Share your theatrical shows and collaborations."
                    icon-color="amber"
                    blog-slug="for-spoken-word-artists"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Page Poets -->
                <x-sub-audience-card
                    name="Page Poets"
                    description="Book launches, literary readings, publication events. Promote your collections alongside appearances."
                    icon-color="sky"
                    blog-slug="for-page-poets"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Open Mic Hosts -->
                <x-sub-audience-card
                    name="Open Mic Hosts"
                    description="Running your own series? Set the night up once as a recurring event and let poets take their own spots."
                    icon-color="orange"
                    blog-slug="for-poetry-open-mic-hosts"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Literary Curators -->
                <x-sub-audience-card
                    name="Literary Curators"
                    description="Organizing reading series, festivals, salon events. Aggregate your programming in one place and embed it anywhere."
                    icon-color="cyan"
                    blog-slug="for-literary-curators"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Storytellers -->
                <x-sub-audience-card
                    name="Storytellers"
                    description="Oral storytelling, narrative performance, and story slams. Share your upcoming shows and captivate new audiences."
                    icon-color="emerald"
                    blog-slug="for-storytellers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Three steps (slot 09)                                     -->
    <!-- ============================================================ -->
    <section id="steps" class="scroll-mt-24 border-t border-gray-200 bg-[#f6f2ea] py-20 dark:border-white/5 dark:bg-[#141211] lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-sheet-corner mb-6" data-reveal aria-hidden="true"><span>09</span></div>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    Three <span class="text-gradient-signup">steps</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="120">
                @foreach ([['1', 'Start the list', 'Add the mic as a recurring event, turn on registration, and set how many spots there are. Skip the weeks the room is closed.'], ['2', 'Share one link', 'Your bio, the venue\'s site, the back of your chapbook. Or embed the calendar on a page you already have.'], ['3', 'Let them sign up', 'Poets take a spot themselves. You see the list from anywhere, and it closes itself when the spots are gone.']] as [$stepNum, $stepTitle, $stepBody])
                    <div class="es-sheet-paper es-sheet-margin p-6" data-reveal="panel">
                        <div class="es-sheet-num mb-3 text-2xl">0{{ $stepNum }}</div>
                        <h3 class="mb-2 text-lg font-bold">{{ $stepTitle }}</h3>
                        <p class="text-sm leading-relaxed text-[#6b6459]">{{ $stepBody }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. Key features (slot 10)                                   -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-white py-20 dark:border-white/5 dark:bg-[#111010]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="Set the night once, with exceptions for the weeks you skip" :url="marketing_url('/features/recurring-events')" icon-color="blue">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Custom Fields" description="Ask what they're reading right on the sign-up form" :url="marketing_url('/features/custom-fields')" icon-color="rose">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="Sell tickets with QR check-in and zero platform fees" :url="marketing_url('/features/ticketing')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Send event updates directly to followers' inboxes" :url="marketing_url('/features/newsletters')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-sheet-link inline-flex items-center font-medium hover:underline">
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
    <!-- 11. Related pages (slot 11)                                  -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-[#f6f2ea] py-16 dark:border-white/5 dark:bg-[#141211]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4" data-reveal-group="70">
                @foreach ([['/for-comedians', 'Comedians'], ['/for-musicians', 'Musicians'], ['/for-theater-performers', 'Theater Performers'], ['/for-libraries', 'Libraries']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" class="es-sheet-hover group flex flex-col rounded-2xl border border-gray-200 bg-white p-5 transition-all duration-200 hover:shadow-md dark:border-white/10 dark:bg-white/[0.03]" data-reveal>
                        <span class="es-sheet-hover-title mb-3 text-sm font-semibold text-gray-900 transition-colors dark:text-white">For {{ $relName }}</span>
                        <span class="es-sheet-hover-arrow mt-auto inline-flex items-center gap-1 text-xs font-medium text-gray-600 transition-colors dark:text-gray-400">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
            <div class="mt-8 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-sheet-link inline-flex items-center font-medium hover:underline">
                    See all use cases
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 12. FAQ (slot 12)                                            -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="scroll-mt-24 bg-white py-20 dark:bg-[#111010] lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="es-sheet-corner mb-6" data-reveal aria-hidden="true"><span>10</span></div>
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked <span class="text-gradient-signup">questions</span>
                </h2>
                <p class="text-lg text-gray-600 dark:text-gray-400" data-reveal style="--reveal-delay: 0.1s;">
                    Everything poets and hosts ask before they start the list.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-sheet-hover group rounded-2xl border border-gray-200 bg-white p-6 transition-all duration-200 dark:border-white/10 dark:bg-white/[0.03]" data-reveal>
                        <summary class="flex cursor-pointer items-start gap-3 font-semibold text-gray-900 dark:text-white">
                            <span class="es-sheet-num flex-none dark:text-[#93c5fd]" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-sheet-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none text-gray-400 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer mt-4 leading-relaxed text-gray-600 ps-9 dark:text-gray-400">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 13. Finale: line 01 is open (slot 01, again)                 -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#111010] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-sheet-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>
                <div class="relative z-10">
                    <p class="es-sheet-tag mb-4">Free forever</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Line 01 <span class="text-gradient-signup-lit">is open.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-400">
                        Start the list tonight. Nobody has to DM you to get on it, and it is still there in the morning.
                    </p>

                    <!-- The last line of the sheet: the name writes itself in -->
                    <div class="mx-auto mb-10 max-w-sm" aria-hidden="true">
                        <div class="es-sheet-paper es-sheet-margin -rotate-1 px-5 py-5">
                            <div class="es-sheet-signline flex items-baseline gap-3">
                                <span class="es-sheet-num">01</span>
                                <span class="es-sheet-hand text-2xl" id="es-sheet-signtext">your-name</span>
                            </div>
                            <p class="mt-2 font-mono text-[0.7rem] text-[#6b6459]">.eventschedule.com</p>
                        </div>
                    </div>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-name" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="es-sheet-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Start your list
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

    <!-- Desktop dot nav: the slot numbers running down the margin -->
    <nav class="es-dotnav fixed top-1/2 z-40 hidden -translate-y-1/2 lg:block ltr:right-5 rtl:left-5" aria-label="Page sections">
        <ul class="glass flex flex-col items-center gap-1.5 rounded-full px-2 py-3">
            @foreach ($dotSections as [$sectionId, $sectionLabel])
                <li class="relative">
                    <a href="#{{ $sectionId }}" class="es-dot group block rounded-full" aria-label="{{ $sectionLabel }}">
                        <span class="es-dot-pip block h-2 w-2 rounded-full bg-gray-400/60 dark:bg-white/30"></span>
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-medium text-gray-700 opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3 dark:border-white/10 dark:bg-[#1b1917] dark:text-gray-300">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <x-marketing.related-pages />

    {{-- Write the claimed name onto the last line of the sheet, applying the
         same slug transform as the shared claim-input sanitizer. --}}
    <script {!! nonce_attr() !!}>
        (function () {
            var input = document.getElementById('es-claim-input');
            var sign = document.getElementById('es-sheet-signtext');
            if (!input || !sign) { return; }
            var fallback = sign.textContent;
            input.addEventListener('input', function () {
                var slug = input.value.toLowerCase()
                    .replace(/['’]/g, '')
                    .replace(/[^a-z0-9-]+/g, '-')
                    .replace(/-{2,}/g, '-')
                    .replace(/^-+/, '')
                    .slice(0, 30);
                sign.textContent = slug || fallback;
            });
        })();
    </script>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
