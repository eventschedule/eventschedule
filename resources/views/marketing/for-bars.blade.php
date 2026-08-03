<x-marketing-layout>
    <x-slot name="title">Event Calendars for Bars and Pubs | Fill Every Night</x-slot>
    <x-slot name="description">Put your whole week on one link - quiz nights, live music, karaoke, the match. Recurring dates that skip the holidays, free registration, and zero platform fees on tickets.</x-slot>
    <x-slot name="breadcrumbTitle">For Bars</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Bars and Pubs",
        "description": "Put your bar's whole week on one link. Recurring quiz nights and live music, free registration, and zero platform fees on ticket sales.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Bars, Pubs and Taprooms"
        }
    }
    </script>
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Bars and Pubs",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Bar and Pub Event Management Software",
        "operatingSystem": "Web",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Recurring weekly nights with date exceptions for holidays and closures",
            "Sub-schedules that keep live music, quiz and sports nights apart",
            "A public submission form so performers can ask to play",
            "Direct newsletters to the regulars who follow your schedule",
            "Free registration with an optional capacity limit",
            "Zero-fee ticket sales with QR check-in at the door",
            "A logo wall of the acts that have played your room",
            "Fan photos, video and comments with an approval queue",
            "Two-way Google, Outlook and CalDAV calendar sync",
            "Embeddable calendar for the website you already have",
            "Built-in analytics for page views, devices and traffic sources"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "bar event calendar, pub quiz night schedule, live music calendar for bars, bar event management software, free pub event calendar, recurring bar events",
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
        "name": "How to put a bar's weekly event calendar online with Event Schedule",
        "description": "Get your bar's week online in three steps.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Chalk up the week",
                "text": "Add each regular night once as a recurring event and set the day it lands on. Use sub-schedules to keep live music, quiz nights and sports apart on the same page."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Share one link",
                "text": "Put the link on your door, your bio and your bookings page, or embed the calendar on the website you already have."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Keep the regulars posted",
                "text": "People follow your schedule and you email them directly when something changes or a new night goes up. No algorithm decides who finds out."
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
           For-bars "The Chalkboard" styles. The page is the A-frame
           sandwich board standing on the pavement outside the pub. The
           SLATE is a physical object: it stays dark in BOTH colour modes
           and only the street around it changes (daylight pavement in
           light mode, wet night pavement in dark), so anything inside
           .es-slate-board deliberately carries no dark: variants.

           Deliberately NOT the classroom board of for-workshop-instructors:
           that one is board-green, wall-mounted, chalk-dust and post-its.
           This is true slate on a street, written in a pub-signwriting
           voice with ribbons and flourishes, and it wipes rather than
           dusts.

           Accent is chalk lime-olive because every neighbour owns
           something warmer or cooler: amber/copper (breweries, djs,
           comedians), burgundy (restaurants), blue-cyan (nightclubs,
           venues), VU green-amber-red (music venues), board-green
           (workshop). Two values are required because #3f6212 is what
           survives a light ground (6.50:1) while #bef264 is what reads
           on slate (14.47:1) - lime-600 #65a30d measures 2.84:1 on the
           street and fails outright, so do not "brighten" this.

           Consequence of the fixed slate: accent text on a fixed-dark
           band needs the always-lit "-lit" variant.

           BLADE RULE for this block: never use @supports probes here.
           A "#" hex inside a parenthesized at-rule condition breaks
           Blade compilation of every later parenthesized directive.
           ============================================================== */

        /* --- Accent text. Stops are weighted late: an even two-hue ramp
               spends its middle in a washed midpoint. --- */
        .text-gradient-chalk {
            background-image: linear-gradient(135deg, #3f6212 0%, #3f6212 35%, #1c1917 88%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            -webkit-text-fill-color: transparent;
        }
        .dark .text-gradient-chalk {
            background-image: linear-gradient(135deg, #bef264 0%, #bef264 35%, #f4efe6 88%);
        }
        /* Always-lit variant for the fixed-dark slate bands (both modes). */
        .text-gradient-chalk-lit {
            background-image: linear-gradient(135deg, #bef264 0%, #bef264 35%, #f4efe6 88%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            -webkit-text-fill-color: transparent;
        }

        /* --- The slate: fixed dark in BOTH colour modes --- */
        .es-slate-board {
            position: relative;
            background-color: #14100f;
            background-image:
                radial-gradient(rgba(244, 239, 230, 0.035) 1px, transparent 1.4px),
                radial-gradient(rgba(244, 239, 230, 0.028) 1px, transparent 1.4px),
                linear-gradient(168deg, #1b1614, #14100f 55%, #0f0c0b);
            background-size: 26px 26px, 41px 41px, 100% 100%;
            background-position: 0 0, 13px 19px, 0 0;
            border-radius: 0.5rem;
            color: #f4efe6;
            box-shadow: inset 0 0 70px rgba(0, 0, 0, 0.55), inset 0 1px 0 rgba(244, 239, 230, 0.06);
        }

        /* --- The A-frame: painted timber surround, also fixed --- */
        .es-slate-frame {
            position: relative;
            background-image: linear-gradient(168deg, #6b5540, #52402f 55%, #3f3122);
            border: 1px solid #33271b;
            border-radius: 0.9rem;
            box-shadow: 0 30px 60px -28px rgba(10, 8, 6, 0.8);
        }
        /* Legs are built from boxes, never an outline SVG drawing. */
        .es-slate-leg {
            position: absolute;
            bottom: -1.55rem;
            width: 0.5rem;
            height: 1.75rem;
            background-image: linear-gradient(180deg, #52402f, #3a2d20);
            border-radius: 0 0 0.15rem 0.15rem;
            transform-origin: top center;
        }
        .es-slate-leg-l { left: 14%; transform: rotate(10deg); }
        .es-slate-leg-r { right: 14%; transform: rotate(-10deg); }

        /* --- The street the board stands on --- */
        .es-slate-pavement {
            background-image:
                repeating-linear-gradient(90deg, rgba(28, 25, 23, 0.06) 0 1px, transparent 1px 92px),
                linear-gradient(180deg, transparent, rgba(28, 25, 23, 0.07));
        }
        .dark .es-slate-pavement {
            background-image:
                repeating-linear-gradient(90deg, rgba(244, 239, 230, 0.045) 0 1px, transparent 1px 92px),
                linear-gradient(180deg, transparent, rgba(190, 242, 100, 0.05));
        }
        /* Wet reflection pooling under the board at night. */
        .es-slate-reflection {
            background-image: radial-gradient(60% 100% at 50% 0%, rgba(190, 242, 100, 0.12), transparent 70%);
            filter: blur(6px);
        }

        /* --- Chalk --- */
        .es-slate-chalk {
            color: #f4efe6;
            text-shadow: 0 0 1px rgba(244, 239, 230, 0.5), 0 1px 2px rgba(0, 0, 0, 0.35);
        }
        .es-slate-chalk-dim { color: rgba(244, 239, 230, 0.62); }
        .es-slate-chalk-accent {
            color: #bef264;
            text-shadow: 0 0 7px rgba(190, 242, 100, 0.28);
        }

        /* --- Pub signwriting voice: serif, tightly tracked, with a ribbon --- */
        .es-slate-sign {
            font-family: Georgia, "Palatino Linotype", "Times New Roman", serif;
            letter-spacing: 0.02em;
        }
        .es-slate-ribbon {
            display: inline-block;
            padding: 0.3rem 1.5rem;
            border-top: 1px solid rgba(244, 239, 230, 0.45);
            border-bottom: 1px solid rgba(244, 239, 230, 0.45);
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.3em;
            text-transform: uppercase;
            color: rgba(244, 239, 230, 0.85);
        }
        /* Flourish rule either side of a board heading (abstract strokes). */
        .es-slate-flourish {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.8rem;
        }
        .es-slate-flourish::before,
        .es-slate-flourish::after {
            content: "";
            height: 1px;
            flex: 1;
            max-width: 5.5rem;
            background: linear-gradient(to right, transparent, rgba(244, 239, 230, 0.4));
        }
        .es-slate-flourish::after { background: linear-gradient(to left, transparent, rgba(244, 239, 230, 0.4)); }

        /* --- The wiped smear where last week used to be --- */
        .es-slate-smear {
            pointer-events: none;
            background-image: linear-gradient(96deg, transparent, rgba(244, 239, 230, 0.09) 22%, rgba(244, 239, 230, 0.15) 52%, rgba(244, 239, 230, 0.07) 78%, transparent);
            border-radius: 45%;
            filter: blur(1.5px);
        }

        /* --- A night on the board --- */
        .es-slate-night {
            display: flex;
            align-items: baseline;
            gap: 0.75rem;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(244, 239, 230, 0.12);
        }
        .es-slate-night:last-child { border-bottom: 0; }
        .es-slate-day {
            flex: none;
            width: 2.6rem;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: rgba(244, 239, 230, 0.7);
        }
        .es-slate-leader {
            flex: 1 1 auto;
            min-width: 1rem;
            align-self: flex-end;
            margin-bottom: 0.3rem;
            border-bottom: 1px dotted rgba(244, 239, 230, 0.26);
        }
        /* The empty night the whole page is about. */
        .es-slate-open {
            color: rgba(244, 239, 230, 0.34);
            font-style: italic;
        }

        /* --- Chalk-outlined cell, used for cards sitting on the slate --- */
        .es-slate-cell {
            border: 1px dashed rgba(244, 239, 230, 0.24);
            border-radius: 0.6rem;
            background: rgba(244, 239, 230, 0.03);
        }
        .es-slate-cell-open {
            border-color: rgba(190, 242, 100, 0.5);
            background: rgba(190, 242, 100, 0.07);
            box-shadow: inset 0 0 22px rgba(190, 242, 100, 0.08);
        }

        /* --- Section numeral: a small slate tab with a chalk tick --- */
        .es-slate-corner {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 0.95rem;
            border-radius: 0.4rem;
            background-image: linear-gradient(168deg, #1b1614, #14100f);
            border: 1px solid rgba(244, 239, 230, 0.16);
            color: #bef264;
            font-weight: 900;
            font-variant-numeric: tabular-nums;
            letter-spacing: 0.05em;
            box-shadow: 0 8px 18px -12px rgba(8, 6, 5, 0.85);
        }
        .es-slate-corner::before {
            content: "";
            width: 2px;
            align-self: stretch;
            border-radius: 1px;
            background: rgba(190, 242, 100, 0.65);
        }

        /* --- Chalk underline that draws itself on reveal. The finished
               state lives on the ALWAYS-ACTIVE rule; only the undrawn
               pre-state is gated, so no-JS and reduced-motion rest
               drawn. --- */
        .es-slate-underline { position: relative; }
        .es-slate-underline::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            bottom: -0.4rem;
            height: 3px;
            border-radius: 2px;
            background: linear-gradient(90deg, transparent, #4d7c0f 10%, #4d7c0f 90%, transparent);
            transform-origin: left center;
            transform: scaleX(1);
            transition: transform 0.95s cubic-bezier(0.22, 1, 0.36, 1) 0.2s;
        }
        .dark .es-slate-underline::after,
        .es-slate-board .es-slate-underline::after {
            background: linear-gradient(90deg, transparent, #bef264 10%, #bef264 90%, transparent);
        }
        html.es-anim [data-reveal]:not(.is-revealed) .es-slate-underline::after { transform: scaleX(0); }

        /* --- Eyebrow tags --- */
        .es-slate-tag {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.28em;
            text-transform: uppercase;
            color: #3f6212;
        }
        .dark .es-slate-tag { color: #bef264; }
        .es-slate-board .es-slate-tag,
        .es-slate-band .es-slate-tag { color: #bef264; }

        /* --- Links and buttons --- */
        .es-slate-link { color: #3f6212; }
        .es-slate-link:hover { color: #1c1917; }
        .dark .es-slate-link { color: #bef264; }
        .dark .es-slate-link:hover { color: #f4efe6; }

        .es-slate-btn {
            background-image: linear-gradient(to right, #3f6212, #4d7c0f);
            box-shadow: 0 20px 40px -12px rgba(63, 98, 18, 0.45);
        }
        .es-slate-btn:hover {
            background-image: linear-gradient(to right, #365314, #3f6212);
            box-shadow: 0 24px 48px -12px rgba(63, 98, 18, 0.55);
        }

        /* --- FAQ / related-card hover recolor --- */
        .es-slate-hover:hover { border-color: rgba(63, 98, 18, 0.42); }
        .dark .es-slate-hover:hover { border-color: rgba(190, 242, 100, 0.38); }
        .es-slate-hover:hover .es-slate-hover-title,
        .es-slate-hover:hover .es-slate-hover-arrow { color: #3f6212; }
        .dark .es-slate-hover:hover .es-slate-hover-title,
        .dark .es-slate-hover:hover .es-slate-hover-arrow { color: #bef264; }

        /* --- Full-bleed fixed-dark band: the street after closing --- */
        .es-slate-band {
            background-color: #0c0b0a;
            background-image: radial-gradient(120% 100% at 50% 0%, #1a1614 0%, #100e0c 55%, #080706 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.55), inset 0 1px 0 rgba(244, 239, 230, 0.05);
        }

        /* --- Plan tags --- */
        .es-slate-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.3rem;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            border: 1px solid rgba(63, 98, 18, 0.4);
            color: #3f6212;
        }
        .dark .es-slate-plan { border-color: rgba(190, 242, 100, 0.42); color: #bef264; }
        .es-slate-board .es-slate-plan,
        .es-slate-band .es-slate-plan { border-color: rgba(190, 242, 100, 0.42); color: #bef264; }
        .es-slate-plan-pro { border-color: rgba(28, 25, 23, 0.4); color: #1c1917; }
        .dark .es-slate-plan-pro { border-color: rgba(244, 239, 230, 0.4); color: #f4efe6; }
        .es-slate-board .es-slate-plan-pro,
        .es-slate-band .es-slate-plan-pro { border-color: rgba(244, 239, 230, 0.4); color: #f4efe6; }

        /* --- Hero chips --- */
        .es-slate-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.4rem 0.9rem;
            border-radius: 9999px;
            border: 1px solid rgba(63, 98, 18, 0.25);
            background: rgba(255, 255, 255, 0.6);
            color: #3f6212;
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        .dark .es-slate-chip {
            border-color: rgba(190, 242, 100, 0.25);
            background: rgba(255, 255, 255, 0.05);
            color: #d9f99d;
        }

        /* --- Shared-system recolors: the cursor spotlight and the dot-nav
               pips are hard-coded brand blue in marketing.css. --- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(63, 98, 18, 0.14), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(190, 242, 100, 0.13), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(63, 98, 18, 0.7); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(190, 242, 100, 0.7); }
        .es-dot.is-active .es-dot-pip { background: linear-gradient(180deg, #3f6212, #4d7c0f); }
        .dark .es-dot.is-active .es-dot-pip { background: linear-gradient(180deg, #bef264, #f4efe6); }

        /* --- Shared classes that break the fixed-object contract inside the
               bands. .grid-overlay flips its line colour with the colour
               mode (marketing.css:118/125) and .es-claim:focus-within is
               hard-coded brand blue (marketing.css:695), so both are pinned
               here to the band's own always-dark treatment. --- */
        .es-slate-band .grid-overlay {
            background-image:
                linear-gradient(rgba(244, 239, 230, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(244, 239, 230, 0.05) 1px, transparent 1px);
        }
        /* .animate-shimmer is also mode-dependent (white 0.3 light / 0.15
           dark, marketing.css:67/72); the band is always dark, so pin it. */
        .es-slate-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-slate-band .es-claim:focus-within {
            border-color: rgba(190, 242, 100, 0.75);
            box-shadow: 0 0 0 4px rgba(190, 242, 100, 0.22);
        }

        /* --- Focus rings. Chalk surfaces are not the shared card
               components, so the ring at marketing.css:248 does not reach
               them. This rule is load-bearing for keyboard users. --- */
        #es-slate-page a:focus-visible,
        #es-slate-page summary:focus-visible,
        #es-slate-page button:focus-visible {
            outline: 2px solid #3f6212;
            outline-offset: 3px;
        }
        .dark #es-slate-page a:focus-visible,
        .dark #es-slate-page summary:focus-visible,
        .dark #es-slate-page button:focus-visible {
            outline-color: #bef264;
        }
        /* On the slate the ground never changes, so keep the chalk ring. */
        .es-slate-board a:focus-visible,
        .es-slate-board summary:focus-visible,
        .es-slate-board button:focus-visible,
        .es-slate-band a:focus-visible,
        .es-slate-band summary:focus-visible,
        .es-slate-band button:focus-visible {
            outline-color: #bef264 !important;
        }

        /* --- Reduced motion: every page-local effect resolves to its
               finished state, nothing moves. --- */
        @media (prefers-reduced-motion: reduce) {
            .es-slate-underline::after {
                transform: scaleX(1) !important;
                transition: none !important;
            }
            .es-slate-reflection { filter: none !important; }
        }
    </style>

    @php
        // THU is deliberately blank: the empty night is this page's through-line.
        $barWeek = [
            ['Mon', 'Quiz night', '8pm start', false],
            ['Tue', 'Open mic', 'Sign up from 6', false],
            ['Wed', 'Vinyl night', 'Bring a record', false],
            ['Thu', '', '', true],
            ['Fri', 'The Howl', 'Live, 9pm', 'accent'],
            ['Sat', 'Karaoke', 'Til late', false],
            ['Sun', 'The match', 'Kick off 4pm', false],
        ];

        $faqs = [
            [
                'q' => 'Is Event Schedule free for bars and pubs?',
                'a' => 'Yes. Sharing your calendar, running recurring weekly nights, splitting them into sub-schedules, taking free registrations, and syncing with Google, Outlook or CalDAV are all free forever. Newsletters are free too, at 10 emails a month, counted per recipient rather than per send. Ticketing, event graphics and the higher 100-a-month newsletter limit are on the Pro plan at $'.$proMonthly.' a month.',
            ],
            [
                'q' => 'Can I set up a night that repeats every week?',
                'a' => 'Yes, on every plan. Set the day-of-week pattern once and the night repeats itself, then add date exceptions for the weeks you are closed or the holiday lands on your quiz night. You can still add one-off events like a tap takeover or a big match alongside the regular lineup.',
            ],
            [
                'q' => 'How do I keep live music, quiz nights and sports apart on one page?',
                'a' => 'Sub-schedules split one schedule into strands, so your live music, your quiz night and your sports fixtures each sit in their own section of the same link. Sub-schedules are free on every plan.',
            ],
            [
                'q' => 'Can bands and DJs ask to play at my bar?',
                'a' => 'Yes. Turn on Accept requests in your schedule settings and performers can submit an event from your public page. Submissions land on your Requests tab, where you review each one and accept or decline it before anything appears on your calendar. This is not tied to a paid plan.',
            ],
            [
                'q' => 'How do I tell my regulars what is on this week?',
                'a' => 'People follow your schedule and you email them directly, so nothing decides who sees it except you. The free plan covers 10 newsletter emails a month and Pro raises it to 100, counted per recipient rather than per send, so it is worth knowing the number before you plan around it.',
            ],
            [
                'q' => 'Can I sell tickets to a ticketed night?',
                'a' => 'Yes, on the Pro plan. Connect your Stripe account and sell straight from your calendar with QR check-in at the door. Event Schedule charges zero platform fees, so beyond Stripe processing the money is yours. Free registration with a capacity limit is available on every plan for nights you do not charge for.',
            ],
        ];

        $dotSections = [
            ['top', 'The board'],
            ['thursday', 'The empty night'],
            ['week', 'The week'],
            ['playing', "Who's playing"],
            ['regulars', 'The regulars'],
            ['rest', 'The rest of it'],
            ['rooms', 'Every kind of bar'],
            ['who', 'Perfect for'],
            ['steps', 'Three steps'],
            ['faq', 'Questions'],
            ['claim', 'Wipe it clean'],
        ];
    @endphp

    <div id="es-slate-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the A-frame on the pavement                         -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden bg-[#f7f5f1] py-16 dark:bg-[#0c0b0a]">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(77, 124, 15, 0.22), rgba(77, 124, 15, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(120, 113, 108, 0.18), rgba(120, 113, 108, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="es-slate-pavement absolute inset-x-0 bottom-0 h-1/3"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-[#3f6212] dark:text-[#bef264]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">For bars, pubs, and taprooms</span>
            </div>

            <h1 class="es-balance mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Your week is chalked on a board</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-chalk">nobody sees after closing.</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-600 dark:text-gray-400 sm:text-xl">
                Quiz nights, live music, karaoke, the match. Put the whole week on one link that still works when the shutters are down, with recurring dates that skip the holidays and no platform fees when you sell.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#week" class="glass group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See how the week works
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
                <a href="{{ app_url('/sign_up?type=venue') }}" class="es-slate-btn group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                    Create your bar's calendar
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- The A-frame itself -->
            <div class="es-fade-up es-d-4 relative mx-auto mt-14 max-w-lg text-start" data-reveal>
                <div class="es-slate-frame -rotate-1 p-3 sm:p-4">
                    <span class="es-slate-leg es-slate-leg-l" aria-hidden="true"></span>
                    <span class="es-slate-leg es-slate-leg-r" aria-hidden="true"></span>
                    <div class="es-slate-board px-5 py-6 sm:px-6">
                        <div class="es-slate-smear absolute right-6 top-16 h-10 w-28" aria-hidden="true"></div>

                        <div class="es-slate-flourish relative mb-4">
                            <span class="es-slate-ribbon es-slate-sign">This Week</span>
                        </div>

                        @foreach ($barWeek as [$day, $night, $detail, $state])
                            <div class="es-slate-night">
                                <span class="es-slate-day">{{ $day }}</span>
                                @if ($state === true)
                                    <span class="es-slate-open text-base">nothing yet</span>
                                    <span class="es-slate-leader"></span>
                                    <span class="es-slate-chalk-accent text-xs font-bold uppercase tracking-wider">open</span>
                                @else
                                    <span class="es-slate-sign text-base {{ $state === 'accent' ? 'es-slate-chalk-accent font-bold' : 'es-slate-chalk' }}">{{ $night }}</span>
                                    <span class="es-slate-leader"></span>
                                    <span class="es-slate-chalk-dim text-xs">{{ $detail }}</span>
                                @endif
                            </div>
                        @endforeach

                        <p class="es-slate-chalk-dim mt-4 border-t border-[rgba(244,239,230,0.12)] pt-3 text-xs">
                            Wiped at closing. Rewritten every Monday. Gone from the internet entirely.
                        </p>
                    </div>
                </div>
                <div class="es-slate-reflection absolute inset-x-8 -bottom-6 h-8 opacity-0 dark:opacity-100" aria-hidden="true"></div>
            </div>

            <!-- Bar-type marquee -->
            <div class="es-fade-up es-d-4 pointer-events-auto mx-auto mt-12 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach (['Craft Beer', 'Wine Bar', 'Sports Bar', 'Cocktail Lounge', 'Irish Pub', 'Dive Bar', 'Taproom', 'Speakeasy', 'Beer Garden', 'Music Bar'] as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-slate-chip">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The empty Thursday (fixed-dark band)                      -->
    <!-- ============================================================ -->
    <section id="thursday" class="relative scroll-mt-24 bg-[#f7f5f1] px-2 py-14 dark:bg-[#0c0b0a] sm:px-4 lg:py-20">
        <div class="es-slate-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-slate-corner mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                    <p class="es-slate-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The empty night</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Friday looks after itself. <span class="text-gradient-chalk-lit">Thursday is the one costing you.</span>
                    </h2>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    <div class="es-slate-cell p-6" data-reveal="panel">
                        <p class="es-slate-tag mb-3">The board</p>
                        <h3 class="es-slate-chalk es-slate-sign mb-2 text-lg font-bold">One copy, on the pavement</h3>
                        <p class="es-slate-chalk-dim text-sm">It works on the people already walking past your door. Everyone else would have to guess.</p>
                    </div>
                    <div class="es-slate-cell p-6" data-reveal="panel">
                        <p class="es-slate-tag mb-3">The Thursdays</p>
                        <h3 class="es-slate-chalk es-slate-sign mb-2 text-lg font-bold">
                            <span data-count-to="52">52</span> a year
                        </h3>
                        <p class="es-slate-chalk-dim text-sm">A quiet weeknight is not one bad night. It is the same night, fifty-two times, with the lights and the staff already paid for.</p>
                    </div>
                    <div class="es-slate-cell es-slate-cell-open p-6" data-reveal="panel">
                        <p class="es-slate-tag mb-3">The fix</p>
                        <h3 class="es-slate-chalk es-slate-sign mb-2 text-lg font-bold">Give it a reason</h3>
                        <p class="es-slate-chalk-dim text-sm">A quiz, an open mic, a vinyl night. Set it up once, let it repeat, and tell the people who already like your bar.</p>
                    </div>
                </div>

                <p class="mt-10 text-center text-gray-300" data-reveal>
                    The board is right. It just needs to exist somewhere other than the pavement.
                    <a href="#week" class="inline-flex items-center gap-1 font-semibold text-[#bef264] transition-all hover:gap-2">
                        Chalk up the week
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Chalk it once, it repeats                                 -->
    <!-- ============================================================ -->
    <section id="week" class="scroll-mt-24 bg-white py-20 dark:bg-[#121110] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-slate-corner mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                <p class="es-slate-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Chalk it once</p>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Write the week once. <span class="text-gradient-chalk">It writes itself after that.</span>
                </h2>
                <p class="mt-5 text-lg text-gray-600 dark:text-gray-400" data-reveal style="--reveal-delay: 0.15s;">
                    Three things carry a bar's calendar, and all three are on the free plan.
                </p>
            </div>

            <div class="mx-auto max-w-3xl" data-reveal="panel">
                <div class="es-slate-board px-6 py-7 sm:px-8 sm:py-9">
                    <div class="es-slate-smear absolute right-10 bottom-12 h-12 w-32" aria-hidden="true"></div>

                    <div class="relative">
                        <div class="es-slate-night items-start">
                            <span class="es-slate-day pt-1">01</span>
                            <div class="min-w-0 flex-1">
                                <div class="mb-1 flex flex-wrap items-center gap-2">
                                    <h3 class="es-slate-chalk es-slate-sign text-lg font-bold">The quiz repeats itself</h3>
                                    <span class="es-slate-plan">Free</span>
                                </div>
                                <p class="es-slate-chalk-dim text-sm leading-relaxed">
                                    Set a night once with a day-of-week pattern and it comes back every week on its own. Add date exceptions for the weeks you are shut, or when the bank holiday lands on quiz night.
                                </p>
                            </div>
                        </div>

                        <div class="es-slate-night items-start">
                            <span class="es-slate-day pt-1">02</span>
                            <div class="min-w-0 flex-1">
                                <div class="mb-1 flex flex-wrap items-center gap-2">
                                    <h3 class="es-slate-chalk es-slate-sign text-lg font-bold">Music, quiz and sport stay apart</h3>
                                    <span class="es-slate-plan">Free</span>
                                </div>
                                <p class="es-slate-chalk-dim text-sm leading-relaxed">
                                    Sub-schedules split one link into strands, so somebody who only cares about the live music is not scrolling past six weeks of fixtures to find it.
                                </p>
                            </div>
                        </div>

                        <div class="es-slate-night items-start">
                            <span class="es-slate-day pt-1">03</span>
                            <div class="min-w-0 flex-1">
                                <div class="mb-1 flex flex-wrap items-center gap-2">
                                    <h3 class="es-slate-chalk es-slate-sign text-lg font-bold">Hold a spot without charging for it</h3>
                                    <span class="es-slate-plan">Free</span>
                                </div>
                                <p class="es-slate-chalk-dim text-sm leading-relaxed">
                                    Turn on registration for a night and set how many places there are. Quiz teams claim a table, the page shows how many are left, and it closes itself when they are gone.
                                </p>
                            </div>
                        </div>

                        <p class="es-slate-chalk-dim mt-5 border-t border-[rgba(244,239,230,0.12)] pt-4 text-sm">
                            Free also covers unlimited events, two-way Google, Outlook and CalDAV sync, an embeddable calendar for the site you already have, and an .ics download for anyone who wants the date in their own phone.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Who's playing                                             -->
    <!-- ============================================================ -->
    <section id="playing" class="scroll-mt-24 border-t border-gray-200 bg-[#f3f1ec] py-20 dark:border-white/5 dark:bg-[#171614] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div>
                    <div class="es-slate-corner mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                    <p class="es-slate-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Who's playing</p>
                    <h2 class="es-balance mb-5 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                        Let the bands come to you, <span class="text-gradient-chalk">in one place.</span>
                    </h2>
                    <p class="mb-6 text-lg leading-relaxed text-gray-600 dark:text-gray-400" data-reveal style="--reveal-delay: 0.15s;">
                        Turn on <strong class="font-semibold text-gray-900 dark:text-white">Accept requests</strong> and performers can submit a night straight from your public page instead of finding you across three inboxes and a DM. Nothing lands on your calendar until you say so.
                    </p>
                    <ul class="space-y-3 text-gray-600 dark:text-gray-400" data-reveal-group="70">
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none text-[#3f6212] dark:text-[#bef264]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Submissions collect on your Requests tab, where you accept or decline each one.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none text-[#3f6212] dark:text-[#bef264]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Add your own questions to the form on Pro, so a band tells you the set length and what they need from the PA up front.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none text-[#3f6212] dark:text-[#bef264]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Accepting a request is not a paid feature. It works on the free plan.</span>
                        </li>
                    </ul>
                </div>

                <div data-reveal="panel">
                    <div class="es-slate-board px-6 py-7">
                        <div class="mb-4 flex items-baseline justify-between gap-3 border-b border-[rgba(244,239,230,0.18)] pb-3">
                            <span class="es-slate-tag">Requests</span>
                            <span class="es-slate-chalk-dim font-mono text-xs">3 waiting</span>
                        </div>

                        <div class="space-y-3">
                            @foreach ([['The Howl', 'Fri 14 Mar', '4-piece, 45 min'], ['DJ Marren', 'Sat 22 Mar', 'Vinyl only, 2 hrs'], ['Quiz w/ Nadia', 'Thu 27 Mar', 'Hosted, 2 rounds']] as [$reqName, $reqDate, $reqNote])
                                <div class="es-slate-cell flex items-center gap-3 p-3">
                                    <div class="min-w-0 flex-1">
                                        <p class="es-slate-chalk es-slate-sign truncate text-sm font-bold">{{ $reqName }}</p>
                                        <p class="es-slate-chalk-dim truncate text-xs">{{ $reqDate }} &middot; {{ $reqNote }}</p>
                                    </div>
                                    <span class="es-slate-chalk-accent flex-none text-xs font-bold uppercase tracking-wider">Accept</span>
                                </div>
                            @endforeach
                        </div>

                        <p class="es-slate-chalk-dim mt-4 text-xs">Declined requests never touch your calendar.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. The regulars (fixed-dark band)                            -->
    <!-- ============================================================ -->
    <section id="regulars" class="relative scroll-mt-24 bg-[#f3f1ec] px-2 py-14 dark:bg-[#171614] sm:px-4 lg:py-20">
        <div class="es-slate-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(190, 242, 100, 0.16), rgba(190, 242, 100, 0) 60%); opacity: 0.5;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-6xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="es-slate-corner mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                    <p class="es-slate-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The regulars</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        The people who already like your bar <span class="text-gradient-chalk-lit">should not be the hardest to reach.</span>
                    </h2>
                </div>

                <div class="grid items-start gap-10 lg:grid-cols-2">
                    <div class="space-y-5" data-reveal-group="80">
                        <div class="es-slate-cell p-6" data-reveal>
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <h3 class="es-slate-chalk es-slate-sign text-lg font-bold">They follow you, you email them</h3>
                                <span class="es-slate-plan">Free</span>
                            </div>
                            <p class="es-slate-chalk-dim text-sm">Nothing sits between the two of you deciding which regulars find out the quiz has moved. The free plan covers 10 newsletter emails a month and Pro raises it to 100, counted per recipient rather than per send.</p>
                        </div>
                        <div class="es-slate-cell p-6" data-reveal>
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <h3 class="es-slate-chalk es-slate-sign text-lg font-bold">Paid reach, pointed outward</h3>
                                <span class="es-slate-plan es-slate-plan-pro">Pro</span>
                            </div>
                            <p class="es-slate-chalk-dim text-sm">Boost puts an event in front of people on Facebook and Instagram who have <em>not</em> heard of you yet. That is worth paying for. Paying to reach the regulars you already have is not, which is why the newsletter above is free.</p>
                        </div>
                        <div class="es-slate-cell p-6" data-reveal>
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <h3 class="es-slate-chalk es-slate-sign text-lg font-bold">The wall of who's played</h3>
                                <span class="es-slate-plan">Free</span>
                            </div>
                            <p class="es-slate-chalk-dim text-sm">Your schedule can show a wall of the acts that have played your room, pulled from the events you have both agreed on. It is the framed photos behind the bar, except people can find it from home.</p>
                        </div>
                    </div>

                    <div class="mx-auto w-full max-w-[19rem]" data-reveal="panel">
                        <!-- The frame stays light on the dark band: it reads as a lit screen. -->
                        <div class="rounded-[2rem] border-4 border-[#2a2724] bg-white p-3 shadow-2xl">
                            <div class="mb-3 flex items-center justify-between px-1">
                                <span class="text-[0.65rem] font-bold uppercase tracking-widest text-gray-600">This week at</span>
                                <span class="text-[0.65rem] font-semibold text-gray-600">The Anchor</span>
                            </div>
                            <div class="space-y-1.5">
                                @foreach ([['Mon', 'Quiz night', false], ['Tue', 'Open mic', false], ['Wed', 'Vinyl night', false], ['Thu', 'Jazz trio', true], ['Fri', 'The Howl', false]] as [$mDay, $mName, $mNew])
                                    <div class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-xs @if ($mNew) bg-[#f2f9e3] font-bold text-[#3f6212] @else bg-gray-50 text-gray-700 @endif">
                                        <span class="font-mono text-[0.65rem] text-gray-600">{{ $mDay }}</span>
                                        <span class="min-w-0 flex-1 truncate">{{ $mName }}</span>
                                        @if ($mNew)<span class="flex-none text-[0.6rem] uppercase tracking-wider">New</span>@endif
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3 rounded-lg bg-gray-50 px-2 py-2">
                                <p class="text-[0.65rem] text-gray-600">Sent to everyone following this schedule.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. The rest of it: bento                                     -->
    <!-- ============================================================ -->
    <section id="rest" class="scroll-mt-24 bg-white py-20 dark:bg-[#121110] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-slate-corner mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                <p class="es-slate-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The rest of it</p>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything else <span class="text-gradient-chalk">a room needs.</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">
                <!-- 1 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">When the night is ticketed</h3>
                                <span class="es-slate-plan es-slate-plan-pro">Pro</span>
                            </div>
                            <p class="mb-4 text-gray-600 dark:text-gray-400">
                                Connect Stripe and sell straight from your calendar, with QR check-in on the door. Event Schedule takes zero platform fees, so past Stripe's processing the money is yours.
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Discount codes for the regulars, and a pass that covers a whole season of a night rather than one at a time.
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
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Photos from the night</h3>
                                <span class="es-slate-plan">Free</span>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400">
                                Regulars add photos, video and comments to an event with just a name and an email. Everything waits in an approval queue, so your page stays yours. Free covers 25 photos per schedule.
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
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Know which nights actually land</h3>
                                <span class="es-slate-plan">Free</span>
                            </div>
                            <p class="mb-4 text-gray-600 dark:text-gray-400">
                                Built-in analytics show page views, the devices people are on, and where the traffic came from. Enough to tell whether the quiz post did anything, without installing a thing.
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Pair it with a poll on the event and let the room vote on the theme or which night to move to.
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
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">On the site you already have</h3>
                                <span class="es-slate-plan">Free</span>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400">
                                Embed the calendar on your own site so the week lives where people already look you up, instead of only on a page they have to be told about.
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
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">The poster, without opening a design tool</h3>
                                <span class="es-slate-plan es-slate-plan-pro">Pro</span>
                            </div>
                            <p class="mb-4 text-gray-600 dark:text-gray-400">
                                Generate a shareable graphic from an event and post it, rather than rebuilding the same quiz-night square every week.
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Every night also syncs two ways with Google, Outlook and CalDAV, so what is on the wall and what is in your phone cannot drift apart.
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
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Keep the room private</h3>
                                <span class="es-slate-plan">Free</span>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400">
                                A function or a staff party can sit on the calendar as a draft, visible to you but never published to the public page.
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
    <!-- 7. Every kind of bar                                         -->
    <!-- ============================================================ -->
    <section id="rooms" class="scroll-mt-24 border-t border-gray-200 bg-[#f3f1ec] py-20 dark:border-white/5 dark:bg-[#171614] lg:py-28">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-slate-corner mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Whatever is <span class="text-gradient-chalk es-slate-underline">on the board</span>
                </h2>
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-400" data-reveal style="--reveal-delay: 0.1s;">
                    The same seven nights, whatever kind of room you run.
                </p>
            </div>

            <div data-reveal="panel">
                <div class="es-slate-board px-6 py-6 sm:px-8">
                    @foreach ([['Mon', 'Quiz and trivia', 'Teams book a table in advance'], ['Tue', 'Open mic and jams', 'Sign-ups from the door'], ['Wed', 'Tastings and takeovers', 'Limited places, ticketed'], ['Thu', 'Live music', 'The night you are filling'], ['Fri', 'DJs and late sets', 'Doors and last entry'], ['Sun', 'The match and roasts', 'Kick-off times up front']] as [$rDay, $rName, $rBlurb])
                        <div class="es-slate-night">
                            <span class="es-slate-day">{{ $rDay }}</span>
                            <span class="es-slate-chalk es-slate-sign font-semibold">{{ $rName }}</span>
                            <span class="es-slate-leader"></span>
                            <span class="es-slate-chalk-dim hidden text-xs sm:inline">{{ $rBlurb }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Perfect for                                               -->
    <!-- ============================================================ -->
    <section id="who" class="scroll-mt-24 bg-white py-20 dark:bg-[#121110] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-slate-corner mb-6" data-reveal aria-hidden="true"><span>08</span></div>
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    Perfect for all types of <span class="text-gradient-chalk es-slate-underline">bars</span>
                </h2>
                <p class="text-lg text-gray-600 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    A taproom and a cocktail lounge have different crowds and the same quiet Thursday
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <!-- Craft Beer Bars -->
                <x-sub-audience-card
                    name="Craft Beer Bars"
                    description="Tap takeovers, brewery events, and beer release parties. Build a following of craft beer enthusiasts."
                    icon-color="amber"
                    blog-slug="for-craft-beer-bars"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Wine Bars -->
                <x-sub-audience-card
                    name="Wine Bars"
                    description="Wine tastings, vineyard dinners, and sommelier events. Educate and delight your wine-loving guests."
                    icon-color="rose"
                    blog-slug="for-wine-bars"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Sports Bars -->
                <x-sub-audience-card
                    name="Sports Bars"
                    description="Game day watch parties, trivia nights, and UFC events. Let fans know what's on the big screen."
                    icon-color="green"
                    blog-slug="for-sports-bars"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Cocktail Lounges -->
                <x-sub-audience-card
                    name="Cocktail Lounges"
                    description="Mixology classes, speakeasy nights, and cocktail competitions. Attract the craft cocktail crowd."
                    icon-color="blue"
                    blog-slug="for-cocktail-lounges"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Irish & British Pubs -->
                <x-sub-audience-card
                    name="Irish & British Pubs"
                    description="Pub quizzes, live traditional music, and St. Patrick's Day celebrations. Keep the craic alive."
                    icon-color="emerald"
                    blog-slug="for-irish-british-pubs"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Dive Bars & Neighborhood Bars -->
                <x-sub-audience-card
                    name="Dive Bars & Neighborhood Bars"
                    description="Open mics, karaoke nights, and local band showcases. Your neighborhood's living room."
                    icon-color="slate"
                    blog-slug="for-dive-bars"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Three steps                                               -->
    <!-- ============================================================ -->
    <section id="steps" class="scroll-mt-24 border-t border-gray-200 bg-[#f3f1ec] py-20 dark:border-white/5 dark:bg-[#171614] lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-slate-corner mb-6" data-reveal aria-hidden="true"><span>09</span></div>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    Three <span class="text-gradient-chalk es-slate-underline">steps</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="120">
                @foreach ([['01', 'Chalk up the week', 'Add each regular night once as a recurring event, and use sub-schedules to keep live music, quiz nights and sport apart on the same page.'], ['02', 'Share one link', 'On your door, in your bio, on your bookings page. Or embed the calendar straight into the website you already have.'], ['03', 'Keep the regulars posted', 'People follow your schedule, and you email them when something changes or a new night goes up.']] as [$stepNum, $stepTitle, $stepBody])
                    <div class="es-slate-board p-6" data-reveal="panel">
                        <div class="es-slate-chalk-accent es-slate-sign mb-3 text-2xl font-black">{{ $stepNum }}</div>
                        <h3 class="es-slate-chalk es-slate-sign mb-2 text-lg font-bold">{{ $stepTitle }}</h3>
                        <p class="es-slate-chalk-dim text-sm leading-relaxed">{{ $stepBody }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. Key features                                             -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-white py-20 dark:border-white/5 dark:bg-[#121110]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="Set a weekly night once, with exceptions for the weeks you close" :url="marketing_url('/features/recurring-events')" icon-color="lime">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-lime-600 dark:text-lime-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Sub-schedules" description="Keep live music, quiz nights and sport apart on one link" :url="marketing_url('/features/sub-schedules')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="Sell tickets with QR check-in and zero platform fees" :url="marketing_url('/features/ticketing')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Email the regulars who follow your schedule" :url="marketing_url('/features/newsletters')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-slate-link inline-flex items-center font-medium hover:underline">
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
    <!-- 11. Related pages                                            -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-[#f3f1ec] py-16 dark:border-white/5 dark:bg-[#171614]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4" data-reveal-group="70">
                @foreach ([['/for-music-venues', 'Music Venues'], ['/for-breweries-and-wineries', 'Breweries & Wineries'], ['/for-restaurants', 'Restaurants'], ['/for-nightclubs', 'Nightclubs']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" class="es-slate-hover group flex flex-col rounded-2xl border border-gray-200 bg-white p-5 transition-all duration-200 hover:shadow-md dark:border-white/10 dark:bg-white/[0.03]" data-reveal>
                        <span class="es-slate-hover-title mb-3 text-sm font-semibold text-gray-900 transition-colors dark:text-white">For {{ $relName }}</span>
                        <span class="es-slate-hover-arrow mt-auto inline-flex items-center gap-1 text-xs font-medium text-gray-600 transition-colors dark:text-gray-400">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
            <div class="mt-8 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-slate-link inline-flex items-center font-medium hover:underline">
                    See all use cases
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 12. FAQ                                                      -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="scroll-mt-24 bg-white py-20 dark:bg-[#121110] lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="es-slate-corner mb-6" data-reveal aria-hidden="true"><span>10</span></div>
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked <span class="text-gradient-chalk es-slate-underline">questions</span>
                </h2>
                <p class="text-lg text-gray-600 dark:text-gray-400" data-reveal style="--reveal-delay: 0.1s;">
                    Everything bar and pub owners ask before they put the week online.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-slate-hover group rounded-2xl border border-gray-200 bg-white p-6 transition-all duration-200 dark:border-white/10 dark:bg-white/[0.03]" data-reveal>
                        <summary class="flex cursor-pointer items-start gap-3 font-semibold text-gray-900 dark:text-white">
                            <span class="flex-none font-mono text-sm font-bold text-[#3f6212] dark:text-[#bef264]" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-slate-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none text-gray-400 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer mt-4 leading-relaxed text-gray-600 ps-9 dark:text-gray-400">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 13. Finale: wipe it clean                                    -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#121110] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-slate-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>
                <div class="relative z-10">
                    <p class="es-slate-tag mb-4">Free forever</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Wipe the board. <span class="text-gradient-chalk-lit">Write it somewhere it stays.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-400">
                        Your week, on one link that works at four in the afternoon on a Thursday, when the person deciding where to go is nowhere near your door.
                    </p>

                    <!-- The board, wiped, with your name chalked on -->
                    <div class="mx-auto mb-10 max-w-sm" aria-hidden="true">
                        <div class="es-slate-frame -rotate-1 p-3">
                            <div class="es-slate-board px-5 py-6">
                                <div class="es-slate-smear absolute left-6 top-8 h-12 w-40" aria-hidden="true"></div>
                                <div class="relative text-center">
                                    <span class="es-slate-flourish es-slate-ribbon es-slate-sign mb-4">This Week</span>
                                    <p class="es-slate-chalk-accent es-slate-sign text-2xl font-bold" id="es-slate-signtext">your-bar</p>
                                    <p class="es-slate-chalk-dim mt-1 font-mono text-[0.7rem]">.eventschedule.com</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-bar" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="es-slate-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
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

    <!-- Desktop dot nav: chalk ticks down the margin -->
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

    {{-- Chalk the claimed name onto the wiped board, applying the same slug
         transform as the shared claim-input sanitizer. --}}
    <script {!! nonce_attr() !!}>
        (function () {
            var input = document.getElementById('es-claim-input');
            var sign = document.getElementById('es-slate-signtext');
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
