<x-marketing-layout>
    <x-slot name="title">Nightclub Event Calendars | Entry, Capacity and Tickets</x-slot>
    <x-slot name="description">Run the entry side of your club from one link: capacity, cover, timed ticket tiers and QR check-in at the door. Recurring themed nights, and zero platform fees when you sell.</x-slot>
    <x-slot name="breadcrumbTitle">For Nightclubs</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Nightclubs",
        "description": "Run the entry side of your club from one link: capacity, cover, timed ticket tiers and QR check-in at the door, with zero platform fees on ticket sales.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Nightclubs and Dance Venues"
        }
    }
    </script>
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Nightclubs",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Nightclub Event Management Software",
        "operatingSystem": "Web",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Free registration with an optional capacity limit for each night",
            "Ticket types with their own sales windows, so cover can change at a set time",
            "QR check-in on the door with a real-time check-in dashboard",
            "Per-attendee tickets, each with its own confirmation email and QR code",
            "Automatic waitlist notifications when a sold-out night frees up",
            "Multi-use passes and memberships for regulars",
            "Recurring themed nights with date exceptions",
            "Sub-schedules that keep each night apart on one link",
            "A public submission form so DJs can ask to play",
            "Direct newsletters to the people who follow your schedule",
            "Story-sized event graphics for social",
            "Two-way Google, Outlook and CalDAV calendar sync"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "nightclub event calendar, club night ticketing, door capacity management, QR check-in nightclub, recurring club nights, free nightclub scheduling",
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
        "name": "How to run a nightclub's entry and calendar with Event Schedule",
        "description": "Get your club's nights and door online in three steps.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Add your nights",
                "text": "Set each regular night up once as a recurring event, and use sub-schedules to keep the house night, the hip-hop night and the headline shows apart on the same link."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Set the door",
                "text": "Turn on registration with a capacity limit for free nights, or add ticket types with their own sales windows so cover changes at a set time."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Scan them in",
                "text": "Every ticket carries a QR code. Scan on the door and the check-in dashboard shows who is actually inside against the capacity you set."
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
           For-nightclubs "The Door" styles. The page stands OUTSIDE the
           club, at the entrance - the one part of the room /for-djs does
           not occupy, and the thing a club actually sells. The spine is
           one in, one out.

           The door is a physical object: .es-door-face is FIXED brushed
           steel in both colour modes and only the street around it
           changes (daylight pavement / wet night pavement), so anything
           inside the door deliberately carries no dark: variants.

           This page's accent is a MATERIAL, not a hue. Every colour in
           this neighbourhood is taken - cyan/sky (djs, venues), amber
           (djs, comedy, theaters, breweries), rose/red (theaters,
           magicians, live-concerts), lime-olive (bars) - and violet is a
           banned brand colour. So the identity is brushed steel, and the
           ONE signal colour, EXIT-sign green, is confined to state (the
           exit sign, doors-open, capacity, links, active nav pip) so it
           never reads as the page's hue.

           Contrast is measured, not eyeballed: light grounds need
           #166534 (6.54:1) because #15803d is only 4.60 with no
           headroom; steel and dark grounds take #4ade80 (10.20:1).

           BLADE RULE for this block: never use @supports probes here.
           A "#" hex inside a parenthesized at-rule condition breaks
           Blade compilation of every later parenthesized directive.
           ============================================================== */

        /* --- Steel headings. Stops weighted late: an even ramp washes
               out through the middle. --- */
        .text-gradient-steel {
            background-image: linear-gradient(135deg, #3f4650 0%, #3f4650 35%, #0f1216 88%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            -webkit-text-fill-color: transparent;
        }
        .dark .text-gradient-steel {
            background-image: linear-gradient(135deg, #e5e9ee 0%, #e5e9ee 35%, #9aa4b2 88%);
        }
        /* Always-lit variant for the fixed-dark bands (both colour modes). */
        .text-gradient-steel-lit {
            background-image: linear-gradient(135deg, #e5e9ee 0%, #e5e9ee 35%, #9aa4b2 88%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            -webkit-text-fill-color: transparent;
        }

        /* --- The door: brushed steel, fixed in BOTH colour modes --- */
        .es-door-face {
            position: relative;
            background-color: #16181c;
            background-image:
                repeating-linear-gradient(90deg, rgba(229, 233, 238, 0.035) 0 1px, transparent 1px 3px),
                linear-gradient(168deg, #24282e, #16181c 55%, #101216);
            border-radius: 0.35rem;
            color: #e5e9ee;
            box-shadow:
                inset 0 1px 0 rgba(229, 233, 238, 0.12),
                inset 0 0 60px rgba(0, 0, 0, 0.5),
                0 26px 55px -26px rgba(4, 5, 6, 0.85);
        }
        /* The jamb the door sits in. */
        .es-door-jamb {
            background-image: linear-gradient(168deg, #1c1f24, #101216 60%, #0a0b0d);
            border: 1px solid #2c3138;
            border-radius: 0.55rem;
            box-shadow: 0 30px 60px -28px rgba(4, 5, 6, 0.9);
        }
        /* Kick plate / push plate, used as a card surface on the door. */
        .es-door-plate {
            background-image: linear-gradient(168deg, rgba(229, 233, 238, 0.08), rgba(229, 233, 238, 0.03));
            border: 1px solid rgba(229, 233, 238, 0.14);
            border-radius: 0.4rem;
        }
        .es-door-plate-open {
            border-color: rgba(74, 222, 128, 0.45);
            background-image: linear-gradient(168deg, rgba(74, 222, 128, 0.12), rgba(74, 222, 128, 0.04));
            box-shadow: inset 0 0 24px rgba(74, 222, 128, 0.08);
        }

        /* --- The EXIT sign: the only colour on the page --- */
        .es-door-exit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.3rem 1.1rem;
            border-radius: 0.25rem;
            background: #0b1f13;
            border: 1px solid rgba(74, 222, 128, 0.45);
            color: #4ade80;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.42em;
            text-indent: 0.42em;
            text-shadow: 0 0 10px rgba(74, 222, 128, 0.55);
            box-shadow: 0 0 22px rgba(74, 222, 128, 0.18);
        }

        /* --- The clicker: checked in against capacity --- */
        .es-door-clicker {
            display: inline-flex;
            align-items: baseline;
            gap: 0.4rem;
            padding: 0.5rem 1rem;
            border-radius: 0.35rem;
            background: #0c0e11;
            border: 1px solid rgba(229, 233, 238, 0.16);
            box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.6);
            font-variant-numeric: tabular-nums;
        }
        .es-door-clicker-in { color: #4ade80; font-weight: 800; }
        .es-door-clicker-cap { color: #9aa4b2; }

        /* --- The rope: two stanchions and a slung line, all boxes --- */
        .es-door-post {
            width: 0.5rem;
            height: 2.6rem;
            border-radius: 0.15rem 0.15rem 0 0;
            background-image: linear-gradient(180deg, #b7bec8, #6b7480 45%, #454c55);
            box-shadow: 0 6px 14px -8px rgba(4, 5, 6, 0.9);
        }
        .es-door-rope {
            flex: 1 1 auto;
            height: 1.4rem;
            margin: 0 -0.15rem;
            border-bottom: 3px solid #4a5058;
            border-bottom-left-radius: 60% 100%;
            border-bottom-right-radius: 60% 100%;
        }

        /* --- The street the door opens onto --- */
        .es-door-asphalt {
            background-image:
                repeating-linear-gradient(90deg, rgba(15, 18, 22, 0.05) 0 1px, transparent 1px 78px),
                linear-gradient(180deg, transparent, rgba(15, 18, 22, 0.08));
        }
        .dark .es-door-asphalt {
            background-image:
                repeating-linear-gradient(90deg, rgba(229, 233, 238, 0.04) 0 1px, transparent 1px 78px),
                linear-gradient(180deg, transparent, rgba(74, 222, 128, 0.05));
        }
        /* Light pooling on wet ground under the exit sign at night. */
        .es-door-spill {
            background-image: radial-gradient(55% 100% at 50% 0%, rgba(74, 222, 128, 0.14), transparent 72%);
            filter: blur(7px);
        }

        /* --- Slow sheen travelling across the steel --- */
        .es-door-sheen {
            pointer-events: none;
            background-image: linear-gradient(104deg, transparent 35%, rgba(229, 233, 238, 0.07) 48%, transparent 61%);
            background-size: 260% 100%;
        }
        html.es-anim .es-door-sheen { animation: es-door-sheen 14s ease-in-out infinite; }
        @keyframes es-door-sheen {
            0%, 100% { background-position: 130% 0; }
            50% { background-position: -30% 0; }
        }

        /* --- Engraved rule that scores itself in on reveal. The drawn
               state lives on the ALWAYS-ACTIVE rule; only the undrawn
               pre-state is gated, so no-JS and reduced-motion rest
               scored. --- */
        .es-door-rule { position: relative; }
        .es-door-rule::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            bottom: -0.4rem;
            height: 2px;
            border-radius: 1px;
            background: linear-gradient(90deg, transparent, #166534 12%, #166534 88%, transparent);
            transform-origin: left center;
            transform: scaleX(1);
            transition: transform 0.95s cubic-bezier(0.22, 1, 0.36, 1) 0.2s;
        }
        .dark .es-door-rule::after,
        .es-door-face .es-door-rule::after,
        .es-door-band .es-door-rule::after {
            background: linear-gradient(90deg, transparent, #4ade80 12%, #4ade80 88%, transparent);
        }
        html.es-anim [data-reveal]:not(.is-revealed) .es-door-rule::after { transform: scaleX(0); }

        /* --- The bolt sliding BACK in the finale. Finished state is
               retracted and centred (translateX(0)); the gated pre-state is
               the bolt thrown into the strike plate. Getting these the wrong
               way round both reverses the meaning (it would lock, under a
               heading that says "Unlock it") and leaves no-JS and
               reduced-motion users looking at an off-centre bar. --- */
        .es-door-bolt {
            display: block;
            height: 0.55rem;
            width: 3.4rem;
            border-radius: 0.15rem;
            background-image: linear-gradient(180deg, #c3cad3, #79828e 50%, #4a5058);
            transform: translateX(0);
            transition: transform 1s cubic-bezier(0.22, 1, 0.36, 1) 0.5s;
        }
        html.es-anim [data-reveal]:not(.is-revealed) .es-door-bolt { transform: translateX(2.2rem); }

        /* --- Section numeral: an engraved steel plate --- */
        .es-door-corner {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 0.95rem;
            border-radius: 0.3rem;
            background-image: linear-gradient(168deg, #24282e, #16181c);
            border: 1px solid rgba(229, 233, 238, 0.18);
            color: #e5e9ee;
            font-weight: 900;
            font-variant-numeric: tabular-nums;
            letter-spacing: 0.06em;
            box-shadow: inset 0 1px 0 rgba(229, 233, 238, 0.12), 0 8px 18px -12px rgba(4, 5, 6, 0.9);
        }
        .es-door-corner::before {
            content: "";
            width: 2px;
            align-self: stretch;
            border-radius: 1px;
            background: rgba(74, 222, 128, 0.7);
        }

        /* --- Eyebrow tags --- */
        .es-door-tag {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.28em;
            text-transform: uppercase;
            color: #166534;
        }
        .dark .es-door-tag { color: #4ade80; }
        .es-door-face .es-door-tag,
        .es-door-band .es-door-tag { color: #4ade80; }

        /* --- State chip: the only other place green is allowed --- */
        .es-door-state {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.15rem 0.5rem;
            border-radius: 0.25rem;
            border: 1px solid rgba(22, 101, 52, 0.4);
            color: #166534;
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }
        .dark .es-door-state { border-color: rgba(74, 222, 128, 0.45); color: #4ade80; }
        .es-door-face .es-door-state,
        .es-door-band .es-door-state { border-color: rgba(74, 222, 128, 0.45); color: #4ade80; }

        /* --- Plan tags --- */
        .es-door-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.25rem;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            border: 1px solid rgba(22, 101, 52, 0.4);
            color: #166534;
        }
        .dark .es-door-plan { border-color: rgba(74, 222, 128, 0.45); color: #4ade80; }
        .es-door-face .es-door-plan,
        .es-door-band .es-door-plan { border-color: rgba(74, 222, 128, 0.45); color: #4ade80; }
        .es-door-plan-pro { border-color: rgba(15, 18, 22, 0.4); color: #0f1216; }
        .dark .es-door-plan-pro { border-color: rgba(229, 233, 238, 0.4); color: #e5e9ee; }
        .es-door-face .es-door-plan-pro,
        .es-door-band .es-door-plan-pro { border-color: rgba(229, 233, 238, 0.4); color: #e5e9ee; }

        /* --- Links and buttons: steel, never green-dominant --- */
        .es-door-link { color: #166534; }
        .es-door-link:hover { color: #0f1216; }
        .dark .es-door-link { color: #4ade80; }
        .dark .es-door-link:hover { color: #e5e9ee; }

        .es-door-btn {
            background-image: linear-gradient(to right, #24282e, #0f1216);
            box-shadow: 0 20px 40px -12px rgba(15, 18, 22, 0.5);
        }
        .es-door-btn:hover {
            background-image: linear-gradient(to right, #2f343c, #16181c);
            box-shadow: 0 24px 48px -12px rgba(15, 18, 22, 0.6);
        }

        /* --- FAQ / related-card hover recolor --- */
        .es-door-hover:hover { border-color: rgba(22, 101, 52, 0.4); }
        .dark .es-door-hover:hover { border-color: rgba(74, 222, 128, 0.38); }
        .es-door-hover:hover .es-door-hover-title,
        .es-door-hover:hover .es-door-hover-arrow { color: #166534; }
        .dark .es-door-hover:hover .es-door-hover-title,
        .dark .es-door-hover:hover .es-door-hover-arrow { color: #4ade80; }

        /* --- Full-bleed fixed-dark band: the street after 2am --- */
        .es-door-band {
            background-color: #0a0b0c;
            background-image: radial-gradient(120% 100% at 50% 0%, #16191d 0%, #0e1013 55%, #070809 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.6), inset 0 1px 0 rgba(229, 233, 238, 0.05);
        }

        /* --- Hero chips --- */
        .es-door-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.4rem 0.9rem;
            border-radius: 9999px;
            border: 1px solid rgba(15, 18, 22, 0.2);
            background: rgba(255, 255, 255, 0.6);
            color: #3f4650;
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        .dark .es-door-chip {
            border-color: rgba(229, 233, 238, 0.18);
            background: rgba(229, 233, 238, 0.06);
            color: #cbd2da;
        }

        /* --- Shared-system recolors: the cursor spotlight and dot-nav
               pips are hard-coded brand blue in marketing.css. --- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(63, 70, 80, 0.16), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(74, 222, 128, 0.1), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(63, 70, 80, 0.7); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(229, 233, 238, 0.7); }
        .es-dot.is-active .es-dot-pip { background: linear-gradient(180deg, #3f4650, #166534); }
        .dark .es-dot.is-active .es-dot-pip { background: linear-gradient(180deg, #e5e9ee, #4ade80); }

        /* --- Shared classes that break the fixed-object contract inside the
               bands. .grid-overlay flips its line colour with the colour
               mode (marketing.css:118/125) and .es-claim:focus-within is
               hard-coded brand blue (marketing.css:695), so both are pinned
               here to the band's own always-dark treatment. --- */
        .es-door-band .grid-overlay {
            background-image:
                linear-gradient(rgba(229, 233, 238, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(229, 233, 238, 0.05) 1px, transparent 1px);
        }
        /* .animate-shimmer is also mode-dependent (white 0.3 light / 0.15
           dark, marketing.css:67/72); the band is always dark, so pin it. */
        .es-door-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-door-band .es-claim:focus-within {
            border-color: rgba(74, 222, 128, 0.75);
            box-shadow: 0 0 0 4px rgba(74, 222, 128, 0.22);
        }

        /* --- Focus rings. Steel surfaces are not the shared card
               components, so the ring at marketing.css:248 does not reach
               them. This rule is load-bearing for keyboard users. --- */
        #es-door-page a:focus-visible,
        #es-door-page summary:focus-visible,
        #es-door-page button:focus-visible {
            outline: 2px solid #166534;
            outline-offset: 3px;
        }
        .dark #es-door-page a:focus-visible,
        .dark #es-door-page summary:focus-visible,
        .dark #es-door-page button:focus-visible {
            outline-color: #4ade80;
        }
        /* On the door and the bands the ground never changes. */
        .es-door-face a:focus-visible,
        .es-door-face summary:focus-visible,
        .es-door-face button:focus-visible,
        .es-door-band a:focus-visible,
        .es-door-band summary:focus-visible,
        .es-door-band button:focus-visible {
            outline-color: #4ade80 !important;
        }

        /* --- Reduced motion: every page-local effect resolves to its
               finished state, nothing moves. --- */
        @media (prefers-reduced-motion: reduce) {
            html.es-anim .es-door-sheen { animation: none !important; }
            .es-door-rule::after {
                transform: scaleX(1) !important;
                transition: none !important;
            }
            .es-door-bolt {
                transform: translateX(0) !important;
                transition: none !important;
            }
        }
    </style>

    @php
        $clubWeekend = [
            ['Thu', 'Industry night', 'Free entry before midnight', 'Registration with a capacity limit'],
            ['Fri', 'House residency', 'Weekly, same DJs', 'One recurring event, set once'],
            ['Sat', 'Headline show', 'Ticketed, sells out', 'Timed tiers, then the waitlist'],
        ];

        $faqs = [
            [
                'q' => 'Is Event Schedule free for nightclubs?',
                'a' => 'Yes. Sharing your nights, running recurring residencies, splitting them into sub-schedules, taking free registrations with a capacity limit, and two-way sync with Google, Outlook or CalDAV are all free forever. Ticketing with QR check-in, the check-in dashboard, event graphics and passes are on the Pro plan at $5 a month, and Event Schedule charges zero platform fees on tickets.',
            ],
            [
                'q' => 'Can people sign up for a free night without paying?',
                'a' => 'Yes, on every plan. Turn on registration for the night and set how many places there are. The page shows how many are left and stops taking names once they are gone, so a free night still has a real capacity rather than an open door.',
            ],
            [
                'q' => 'How do I charge less before a certain time?',
                'a' => 'Give the night more than one ticket type and put a sales window on each. A cheap early tier can stop selling at 11pm and a full-price tier take over after it, so cover changes on the clock without anyone editing the page at the door.',
            ],
            [
                'q' => 'Can I sell different ticket types for one night?',
                'a' => 'Yes, on the Pro plan. Create as many ticket types as the night needs, each with its own price and quantity, plus add-ons that attach to a ticket and discounts that kick in when someone buys several at once. Connect Stripe and sell straight from your calendar with zero platform fees.',
            ],
            [
                'q' => 'Can DJs ask to play at my club?',
                'a' => 'Yes. Turn on Accept requests and artists can submit a night from your public page. Submissions land on your Requests tab, where you accept or decline before anything reaches your calendar. On Pro you can add your own questions to that form, so a DJ sends their genre and a link to their mixes with the request.',
            ],
            [
                'q' => 'What happens when a night sells out?',
                'a' => 'Turn on the waitlist for that event and people can join it once tickets are gone. If a ticket is released, the waitlist is notified automatically instead of you working through replies. The waitlist is a Pro feature.',
            ],
        ];

        $dotSections = [
            ['top', 'The door'],
            ['decides', 'What it decides'],
            ['entry', 'The list'],
            ['scan', 'The door itself'],
            ['playing', "Who's playing"],
            ['rest', 'The rest of it'],
            ['weekend', 'The weekend'],
            ['who', 'Perfect for'],
            ['steps', 'Three steps'],
            ['faq', 'Questions'],
            ['claim', 'Open it'],
        ];
    @endphp

    <div id="es-door-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the door                                            -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden bg-[#f4f5f7] py-16 dark:bg-[#0a0b0c]">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(63, 70, 80, 0.22), rgba(63, 70, 80, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(74, 222, 128, 0.12), rgba(74, 222, 128, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="es-door-asphalt absolute inset-x-0 bottom-0 h-1/3"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-[#166534] dark:text-[#4ade80]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">For nightclubs and dance venues</span>
            </div>

            <h1 class="es-balance mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">The night is won at the door.</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-steel">Not in the booth.</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-600 dark:text-gray-400 sm:text-xl">
                Capacity, cover, who is on for tonight, and who actually walked in. Put the entry side of your club on one link, with QR check-in on the door and zero platform fees when you sell.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#entry" class="glass group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See how entry works
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
                <a href="{{ app_url('/sign_up?type=venue') }}" class="es-door-btn group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                    Create your club's calendar
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- The door itself -->
            <div class="es-fade-up es-d-4 relative mx-auto mt-14 max-w-md" data-reveal>
                <div class="es-door-jamb p-3">
                    <div class="es-door-face overflow-hidden px-6 py-8">
                        <div class="es-door-sheen absolute inset-0" aria-hidden="true"></div>

                        <div class="relative text-center">
                            <div class="es-door-exit mb-8">EXIT</div>

                            <div class="es-door-clicker mb-2">
                                <span class="es-door-clicker-in text-3xl">241</span>
                                <span class="es-door-clicker-cap text-lg">/ 300</span>
                            </div>
                            <p class="text-xs text-[#9aa4b2]">Checked in against tonight's capacity</p>

                            <div class="mt-7 flex items-center justify-center gap-2 text-start">
                                <span class="es-door-state">Doors open</span>
                                <span class="text-xs text-[#9aa4b2]">59 places left</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- The rope, out front -->
                <div class="mt-6 flex items-end justify-center gap-0 px-6" aria-hidden="true">
                    <span class="es-door-post"></span>
                    <span class="es-door-rope"></span>
                    <span class="es-door-post"></span>
                    <span class="es-door-rope"></span>
                    <span class="es-door-post"></span>
                </div>
                <div class="es-door-spill absolute inset-x-10 -bottom-2 h-8 opacity-0 dark:opacity-100" aria-hidden="true"></div>
            </div>

            <!-- Club-type marquee -->
            <div class="es-fade-up es-d-4 pointer-events-auto mx-auto mt-12 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach (['House', 'Techno', 'Hip-Hop', 'Latin', 'Disco', 'Drum & Bass', 'Rooftop', 'Warehouse', 'Lounge', 'Residency'] as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-door-chip">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. What the door decides (fixed-dark band)                   -->
    <!-- ============================================================ -->
    <section id="decides" class="relative scroll-mt-24 bg-[#f4f5f7] px-2 py-14 dark:bg-[#0a0b0c] sm:px-4 lg:py-20">
        <div class="es-door-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-door-corner mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                    <p class="es-door-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">What the door decides</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Nobody remembers the set. <span class="text-gradient-steel-lit">They remember the queue.</span>
                    </h2>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    <div class="es-door-plate p-6" data-reveal="panel">
                        <p class="es-door-tag mb-3">Capacity</p>
                        <h3 class="mb-2 text-lg font-bold text-[#e5e9ee]">
                            <span data-count-to="300">300</span> in, and no more
                        </h3>
                        <p class="text-sm text-[#9aa4b2]">A number on a clipboard is a guess. A number the page enforces is a limit.</p>
                    </div>
                    <div class="es-door-plate p-6" data-reveal="panel">
                        <p class="es-door-tag mb-3">Cover</p>
                        <h3 class="mb-2 text-lg font-bold text-[#e5e9ee]">
                            Changes at 11pm
                        </h3>
                        <p class="text-sm text-[#9aa4b2]">Cheap early, full price after. Someone has to remember to switch it, or the page does it on the clock.</p>
                    </div>
                    <div class="es-door-plate es-door-plate-open p-6" data-reveal="panel">
                        <p class="es-door-tag mb-3">The answer</p>
                        <h3 class="mb-2 text-lg font-bold text-[#e5e9ee]">One link runs entry</h3>
                        <p class="text-sm text-[#9aa4b2]">Sign-ups, tiers, and the scan at the door, all reading from the same night.</p>
                    </div>
                </div>

                <p class="mt-10 text-center text-gray-300" data-reveal>
                    The booth is handled. This is the other half of the room.
                    <a href="#entry" class="inline-flex items-center gap-1 font-semibold text-[#4ade80] transition-all hover:gap-2">
                        Start at the list
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The list is a ticket                                      -->
    <!-- ============================================================ -->
    <section id="entry" class="scroll-mt-24 bg-white py-20 dark:bg-[#111315] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-door-corner mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                <p class="es-door-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The list</p>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    The list is just <span class="text-gradient-steel">a ticket that costs nothing.</span>
                </h2>
                <p class="mt-5 text-lg text-gray-600 dark:text-gray-400" data-reveal style="--reveal-delay: 0.15s;">
                    Free entry, reduced cover and a full-price door are the same mechanic with different numbers on it.
                </p>
            </div>

            <div class="mx-auto max-w-3xl" data-reveal="panel">
                <div class="es-door-jamb p-3">
                    <div class="es-door-face overflow-hidden px-6 py-7 sm:px-8">
                        <div class="es-door-sheen absolute inset-0" aria-hidden="true"></div>
                        <div class="relative space-y-5">
                            <div class="es-door-plate p-5">
                                <div class="mb-2 flex flex-wrap items-center gap-2">
                                    <h3 class="text-lg font-bold text-[#e5e9ee]">On the list, free, capped</h3>
                                    <span class="es-door-plan">Free</span>
                                </div>
                                <p class="text-sm leading-relaxed text-[#9aa4b2]">
                                    Turn on registration and set how many places the night has. People claim one from the event page, it shows what is left, and it closes itself when they are gone. That is the guest list, without anyone keeping a separate one.
                                </p>
                            </div>

                            <div class="es-door-plate p-5">
                                <div class="mb-2 flex flex-wrap items-center gap-2">
                                    <h3 class="text-lg font-bold text-[#e5e9ee]">Cover that changes on the clock</h3>
                                    <span class="es-door-plan es-door-plan-pro">Pro</span>
                                </div>
                                <p class="text-sm leading-relaxed text-[#9aa4b2]">
                                    Give a night more than one ticket type and put a sales window on each. The cheap tier stops selling at 11pm, the full-price tier takes over, and nobody has to remember to change anything at the door.
                                </p>
                                <div class="mt-4 space-y-1.5">
                                    @foreach ([['Before 11pm', '$10', 'Closed 23:00', true], ['After 11pm', '$18', 'On sale', false], ['Group of 6+', '$15 each', 'On sale', false]] as [$tierName, $tierPrice, $tierState, $tierClosed])
                                        <div class="flex items-center gap-3 rounded px-3 py-2 text-xs {{ $tierClosed ? 'bg-[rgba(229,233,238,0.04)]' : 'bg-[rgba(74,222,128,0.07)]' }}">
                                            <span class="min-w-0 flex-1 truncate font-semibold text-[#e5e9ee]">{{ $tierName }}</span>
                                            <span class="font-mono text-[#e5e9ee]">{{ $tierPrice }}</span>
                                            <span class="font-mono text-[0.65rem] {{ $tierClosed ? 'text-[#9aa4b2]' : 'text-[#4ade80]' }}">{{ $tierState }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="es-door-plate p-5">
                                <div class="mb-2 flex flex-wrap items-center gap-2">
                                    <h3 class="text-lg font-bold text-[#e5e9ee]">Tables, add-ons and groups</h3>
                                    <span class="es-door-plan es-door-plan-pro">Pro</span>
                                </div>
                                <p class="text-sm leading-relaxed text-[#9aa4b2]">
                                    A table is a ticket type with a price and a quantity. Anything that comes with it is an add-on that attaches to the booking, and a group rate can kick in automatically once someone buys several at once.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. The door itself                                           -->
    <!-- ============================================================ -->
    <section id="scan" class="scroll-mt-24 border-t border-gray-200 bg-[#eef0f3] py-20 dark:border-white/5 dark:bg-[#141618] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div>
                    <div class="es-door-corner mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                    <p class="es-door-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The door itself</p>
                    <h2 class="es-balance mb-5 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                        Know who is inside, <span class="text-gradient-steel">not who bought.</span>
                    </h2>
                    <p class="mb-6 text-lg leading-relaxed text-gray-600 dark:text-gray-400" data-reveal style="--reveal-delay: 0.15s;">
                        Every ticket carries a QR code. Scan on the way in and the check-in dashboard counts against the capacity you set, so the number on the clicker is the number in the room.
                    </p>
                    <ul class="space-y-3 text-gray-600 dark:text-gray-400" data-reveal-group="70">
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none text-[#166534] dark:text-[#4ade80]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Real-time attendance with a per-ticket breakdown, so you can see which tier is actually turning up.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none text-[#166534] dark:text-[#4ade80]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Per-attendee tickets give every guest in a group their own confirmation email and their own QR, so one person is not holding six.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none text-[#166534] dark:text-[#4ade80]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Check-in and the dashboard are Pro. The capacity limit on a free night is not.</span>
                        </li>
                    </ul>
                </div>

                <div class="mx-auto w-full max-w-[19rem]" data-reveal="panel">
                    <!-- The frame stays light on the dark surround: it reads as a lit screen. -->
                    <div class="rounded-[2rem] border-4 border-[#24282e] bg-white p-3 shadow-2xl">
                        <div class="mb-3 flex items-center justify-between px-1">
                            <span class="text-[0.65rem] font-bold uppercase tracking-widest text-gray-600">Check-in</span>
                            <span class="rounded bg-[#166534] px-2 py-0.5 text-[0.6rem] font-bold uppercase tracking-wider text-white">Live</span>
                        </div>
                        <div class="mb-3 rounded-xl bg-gray-50 px-3 py-3 text-center">
                            <p class="font-mono text-3xl font-black text-[#166534]">241</p>
                            <p class="text-[0.65rem] text-gray-600">of 300 capacity</p>
                        </div>
                        <div class="space-y-1.5">
                            @foreach ([['Before 11pm', '128', 'of 140'], ['After 11pm', '96', 'of 140'], ['Table 4', '17', 'of 20']] as [$ciName, $ciIn, $ciOf])
                                <div class="flex items-center gap-2 rounded-lg bg-gray-50 px-2 py-1.5 text-xs text-gray-700">
                                    <span class="min-w-0 flex-1 truncate">{{ $ciName }}</span>
                                    <span class="font-mono font-bold text-gray-900">{{ $ciIn }}</span>
                                    <span class="font-mono text-[0.65rem] text-gray-600">{{ $ciOf }}</span>
                                </div>
                            @endforeach
                        </div>
                        <p class="mt-3 px-1 text-[0.65rem] text-gray-600">Updates as each code is scanned.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Who's playing (fixed-dark band)                           -->
    <!-- ============================================================ -->
    <section id="playing" class="relative scroll-mt-24 bg-[#eef0f3] px-2 py-14 dark:bg-[#141618] sm:px-4 lg:py-20">
        <div class="es-door-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(74, 222, 128, 0.12), rgba(74, 222, 128, 0) 60%); opacity: 0.5;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-6xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="es-door-corner mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                    <p class="es-door-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Who's playing</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Let them come to you, <span class="text-gradient-steel-lit">and keep the calendar clean.</span>
                    </h2>
                </div>

                <div class="grid items-start gap-10 lg:grid-cols-2">
                    <div class="space-y-5" data-reveal-group="80">
                        <div class="es-door-plate p-6" data-reveal>
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <h3 class="text-lg font-bold text-[#e5e9ee]">Accept requests</h3>
                                <span class="es-door-plan">Free</span>
                            </div>
                            <p class="text-sm text-[#9aa4b2]">Switch it on and artists can submit a night from your public page. Everything waits on your Requests tab until you accept or decline it, so nothing reaches your calendar by surprise.</p>
                        </div>
                        <div class="es-door-plate p-6" data-reveal>
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <h3 class="text-lg font-bold text-[#e5e9ee]">Ask what you need up front</h3>
                                <span class="es-door-plan es-door-plan-pro">Pro</span>
                            </div>
                            <p class="text-sm text-[#9aa4b2]">Add your own questions to that form and a DJ sends their genre, set length and a link to their mixes with the request, instead of three replies later.</p>
                        </div>
                        <div class="es-door-plate p-6" data-reveal>
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <h3 class="text-lg font-bold text-[#e5e9ee]">Residencies set themselves</h3>
                                <span class="es-door-plan">Free</span>
                            </div>
                            <p class="text-sm text-[#9aa4b2]">A weekly night is one recurring event with a day-of-week pattern, plus date exceptions for the weeks you are closed or the room is booked out.</p>
                        </div>
                    </div>

                    <div data-reveal="panel">
                        <div class="es-door-jamb p-3">
                            <div class="es-door-face px-6 py-6">
                                <div class="mb-4 flex items-baseline justify-between gap-3 border-b border-[rgba(229,233,238,0.16)] pb-3">
                                    <span class="es-door-tag">Requests</span>
                                    <span class="font-mono text-xs text-[#9aa4b2]">3 waiting</span>
                                </div>
                                <div class="space-y-3">
                                    @foreach ([['Kaya Sol', 'Sat 12 Apr', 'House', '2 hr'], ['NULL/VOID', 'Fri 18 Apr', 'Techno', '90 min'], ['Duo Prisma', 'Sat 26 Apr', 'Latin', 'live set']] as [$reqName, $reqDate, $reqGenre, $reqLen])
                                        <div class="es-door-plate flex items-center gap-3 p-3">
                                            <div class="min-w-0 flex-1">
                                                <p class="truncate text-sm font-bold text-[#e5e9ee]">{{ $reqName }}</p>
                                                <p class="truncate text-xs text-[#9aa4b2]">{{ $reqDate }} &middot; {{ $reqGenre }} &middot; {{ $reqLen }}</p>
                                            </div>
                                            <span class="es-door-state flex-none" aria-hidden="true">Accept</span>
                                        </div>
                                    @endforeach
                                </div>
                                <p class="mt-4 text-xs text-[#9aa4b2]">Declined requests never touch your calendar.</p>
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
    <section id="rest" class="scroll-mt-24 bg-white py-20 dark:bg-[#111315] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-door-corner mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                <p class="es-door-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The rest of it</p>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything behind <span class="text-gradient-steel">the door.</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">
                <!-- 1 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Tell the regulars first</h3>
                                <span class="es-door-plan">Free</span>
                            </div>
                            <p class="mb-4 text-gray-600 dark:text-gray-400">
                                People follow your schedule and you email them directly when a night goes up or a headliner is announced. Nothing sits between the two of you deciding who finds out.
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Worth knowing the numbers before you plan around it: 10 emails a month on Free, 100 on Pro and 1,000 on Enterprise, counted per recipient rather than per send.
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
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">When it sells out</h3>
                                <span class="es-door-plan es-door-plan-pro">Pro</span>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400">
                                Turn on the waitlist and people can join once tickets are gone. If one is released they are notified automatically, instead of you working back through a hundred replies.
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
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Every night in its own lane</h3>
                                <span class="es-door-plan">Free</span>
                            </div>
                            <p class="mb-4 text-gray-600 dark:text-gray-400">
                                Sub-schedules split one link into strands, so somebody who only comes for the techno night is not scrolling past two months of everything else to find it.
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Built-in analytics show page views, devices and where the traffic came from, so you can tell which night the interest is actually landing on.
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
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Passes for the regulars</h3>
                                <span class="es-door-plan es-door-plan-pro">Pro</span>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400">
                                Sell a multi-use pass or a membership that works across a run of nights, with its own usage tracking and cancellation policy.
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
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">The post, without opening a design tool</h3>
                                <span class="es-door-plan es-door-plan-pro">Pro</span>
                            </div>
                            <p class="mb-4 text-gray-600 dark:text-gray-400">
                                Generate a graphic from a night in a story, square, portrait or landscape crop, and post it. It is built from that event, so the date and the room are already right.
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Running it online as well? Mark the night as an online event and paste the link to wherever you are streaming.
                                <a href="{{ marketing_url('/features/online-events') }}" class="es-door-link font-medium hover:underline">How online events work</a>
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
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">On the site you already have</h3>
                                <span class="es-door-plan">Free</span>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400">
                                Embed the calendar on your own site so tonight is wherever people already look you up, and every night syncs two ways with Google, Outlook and CalDAV.
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
    <!-- 7. The club weekend                                          -->
    <!-- ============================================================ -->
    <section id="weekend" class="scroll-mt-24 border-t border-gray-200 bg-[#eef0f3] py-20 dark:border-white/5 dark:bg-[#141618] lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-door-corner mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Three nights, one <span class="text-gradient-steel es-door-rule">door</span>
                </h2>
                <p class="mt-6 text-lg text-gray-600 dark:text-gray-400" data-reveal style="--reveal-delay: 0.1s;">
                    The same page, set up three different ways.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="120">
                @foreach ($clubWeekend as [$wDay, $wName, $wDetail, $wHow])
                    <div class="es-door-jamb p-3" data-reveal="panel">
                        <div class="es-door-face px-5 py-6">
                            <p class="es-door-tag mb-3">{{ $wDay }}</p>
                            <h3 class="mb-1 text-lg font-bold text-[#e5e9ee]">{{ $wName }}</h3>
                            <p class="mb-4 text-sm text-[#9aa4b2]">{{ $wDetail }}</p>
                            <div class="border-t border-[rgba(229,233,238,0.16)] pt-3">
                                <p class="text-xs text-[#9aa4b2]">{{ $wHow }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Perfect for                                               -->
    <!-- ============================================================ -->
    <section id="who" class="scroll-mt-24 bg-white py-20 dark:bg-[#111315] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-door-corner mb-6" data-reveal aria-hidden="true"><span>08</span></div>
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    Perfect for all types of <span class="text-gradient-steel es-door-rule">clubs</span>
                </h2>
                <p class="mt-6 text-lg text-gray-600 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Different crowds, different music, the same door.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <!-- Dance Clubs & EDM Venues -->
                <x-sub-audience-card
                    name="Dance Clubs & EDM Venues"
                    description="House, techno, trance crowds. Big rooms, bigger sound systems, and lineups that matter."
                    icon-color="cyan"
                    blog-slug="for-dance-clubs-edm"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Hip-Hop & Urban Clubs -->
                <x-sub-audience-card
                    name="Hip-Hop & Urban Clubs"
                    description="Hip-hop nights, R&B showcases, urban music events. Build your scene's go-to spot."
                    icon-color="sky"
                    blog-slug="for-hip-hop-clubs"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Latin Clubs -->
                <x-sub-audience-card
                    name="Latin Clubs"
                    description="Salsa, bachata, reggaeton communities. Themed nights that keep dancers coming back."
                    icon-color="orange"
                    blog-slug="for-latin-clubs"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Rooftop Clubs -->
                <x-sub-audience-card
                    name="Rooftop Clubs"
                    description="Sunset sessions, seasonal programming, skyline views. Weather-dependent vibes done right."
                    icon-color="blue"
                    blog-slug="for-rooftop-clubs"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Underground & Warehouse Venues -->
                <x-sub-audience-card
                    name="Underground & Warehouse"
                    description="Intimate sets, warehouse parties, curated crowds. Where the real heads gather."
                    icon-color="slate"
                    blog-slug="for-underground-clubs"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- VIP Lounges -->
                <x-sub-audience-card
                    name="VIP Lounges"
                    description="Table-led nights, upscale nightlife, smaller rooms. Premium experiences with a strict door."
                    icon-color="amber"
                    blog-slug="for-vip-lounges"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Three steps                                               -->
    <!-- ============================================================ -->
    <section id="steps" class="scroll-mt-24 border-t border-gray-200 bg-[#eef0f3] py-20 dark:border-white/5 dark:bg-[#141618] lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-door-corner mb-6" data-reveal aria-hidden="true"><span>09</span></div>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    Three <span class="text-gradient-steel es-door-rule">steps</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="120">
                @foreach ([['01', 'Add your nights', 'Each regular night once as a recurring event, with sub-schedules keeping the house night, the hip-hop night and the headline shows apart.'], ['02', 'Set the door', 'Registration with a capacity limit for free nights, or ticket types with their own sales windows so cover changes on the clock.'], ['03', 'Scan them in', 'Every ticket carries a QR code. Scan on the door and the dashboard counts who is inside against the capacity you set.']] as [$stepNum, $stepTitle, $stepBody])
                    <div class="es-door-jamb p-3" data-reveal="panel">
                        <div class="es-door-face px-5 py-6">
                            <div class="mb-3 font-mono text-2xl font-black text-[#4ade80]">{{ $stepNum }}</div>
                            <h3 class="mb-2 text-lg font-bold text-[#e5e9ee]">{{ $stepTitle }}</h3>
                            <p class="text-sm leading-relaxed text-[#9aa4b2]">{{ $stepBody }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. Key features                                             -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-white py-20 dark:border-white/5 dark:bg-[#111315]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="Ticket types, QR check-in, and zero platform fees" :url="marketing_url('/features/ticketing')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="Set a residency once, with exceptions for the weeks you close" :url="marketing_url('/features/recurring-events')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Sub-schedules" description="Keep every night in its own lane on one link" :url="marketing_url('/features/sub-schedules')" icon-color="teal">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Email the people who follow your schedule" :url="marketing_url('/features/newsletters')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-door-link inline-flex items-center font-medium hover:underline">
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
    <section class="border-t border-gray-200 bg-[#eef0f3] py-16 dark:border-white/5 dark:bg-[#141618]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4" data-reveal-group="70">
                @foreach ([['/for-djs', 'DJs'], ['/for-music-venues', 'Music Venues'], ['/for-bars', 'Bars'], ['/for-venues', 'Venues']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" class="es-door-hover group flex flex-col rounded-2xl border border-gray-200 bg-white p-5 transition-all duration-200 hover:shadow-md dark:border-white/10 dark:bg-white/[0.03]" data-reveal>
                        <span class="es-door-hover-title mb-3 text-sm font-semibold text-gray-900 transition-colors dark:text-white">For {{ $relName }}</span>
                        <span class="es-door-hover-arrow mt-auto inline-flex items-center gap-1 text-xs font-medium text-gray-600 transition-colors dark:text-gray-400">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
            <div class="mt-8 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-door-link inline-flex items-center font-medium hover:underline">
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

    <section id="faq" class="scroll-mt-24 bg-white py-20 dark:bg-[#111315] lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="es-door-corner mb-6" data-reveal aria-hidden="true"><span>10</span></div>
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked <span class="text-gradient-steel es-door-rule">questions</span>
                </h2>
                <p class="mt-6 text-lg text-gray-600 dark:text-gray-400" data-reveal style="--reveal-delay: 0.1s;">
                    Everything club owners ask about the door.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-door-hover group rounded-2xl border border-gray-200 bg-white p-6 transition-all duration-200 dark:border-white/10 dark:bg-white/[0.03]" data-reveal>
                        <summary class="flex cursor-pointer items-start gap-3 font-semibold text-gray-900 dark:text-white">
                            <span class="flex-none font-mono text-sm font-bold text-[#166534] dark:text-[#4ade80]" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-door-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none text-gray-400 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer mt-4 leading-relaxed text-gray-600 ps-9 dark:text-gray-400">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 13. Finale: open it                                          -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#111315] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-door-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>
                <div class="relative z-10">
                    <p class="es-door-tag mb-4">Free forever</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Unlock it. <span class="text-gradient-steel-lit">The queue is already outside.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-400">
                        Set the capacity, set the cover, and put the whole thing on one link before Friday.
                    </p>

                    <!-- The bolt slides back -->
                    <div class="mx-auto mb-10 max-w-sm" aria-hidden="true">
                        <div class="es-door-jamb p-3">
                            <div class="es-door-face overflow-hidden px-5 py-7">
                                <div class="es-door-sheen absolute inset-0" aria-hidden="true"></div>
                                <div class="relative">
                                    <div class="mb-5 flex justify-center">
                                        <span class="es-door-bolt"></span>
                                    </div>
                                    <p class="font-mono text-xl font-bold text-[#4ade80]" id="es-door-signtext">your-club</p>
                                    <p class="mt-1 font-mono text-[0.7rem] text-[#9aa4b2]">.eventschedule.com</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-club" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="es-door-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
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

    <!-- Desktop dot nav: steel pips -->
    <nav class="es-dotnav fixed top-1/2 z-40 hidden -translate-y-1/2 lg:block ltr:right-5 rtl:left-5" aria-label="Page sections">
        <ul class="glass flex flex-col items-center gap-1.5 rounded-full px-2 py-3">
            @foreach ($dotSections as [$sectionId, $sectionLabel])
                <li class="relative">
                    <a href="#{{ $sectionId }}" class="es-dot group block rounded-full" aria-label="{{ $sectionLabel }}">
                        <span class="es-dot-pip block h-2 w-2 rounded-full bg-gray-400/60 dark:bg-white/30"></span>
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-medium text-gray-700 opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3 dark:border-white/10 dark:bg-[#1b1e22] dark:text-gray-300">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <x-marketing.related-pages />

    {{-- Stencil the claimed name onto the door, applying the same slug
         transform as the shared claim-input sanitizer. --}}
    <script {!! nonce_attr() !!}>
        (function () {
            var input = document.getElementById('es-claim-input');
            var sign = document.getElementById('es-door-signtext');
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
