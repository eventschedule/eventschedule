<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Webinars | Registration & Join Links</x-slot>
    <x-slot name="description">Host webinars with built-in registration, ticketing, direct attendee email, and a join link on any platform. Works with Zoom, Google Meet, or any link.</x-slot>
    <x-slot name="breadcrumbTitle">For Webinars</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Webinars",
        "description": "Publish a webinar on a public schedule, take free registrations or sell tickets with zero platform fees, and hand the join link only to the people who registered. One Event URL field, so any meeting or streaming platform works.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Webinar Hosts"
        }
    }
    </script>
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Webinars",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Webinar Scheduling Software",
        "operatingSystem": "Web",
        "description": "Publish a webinar on a public schedule, take free registrations or sell tickets with zero platform fees, and hand the join link only to the people who registered. One Event URL field, so any meeting or streaming platform works.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Free forever"
        },
        "featureList": [
            "One Event URL field, so any meeting or streaming link works",
            "Public listing shows the platform's domain, never the join link",
            "Free registration with a capacity limit counted per session date",
            "Registrants get their own registration page carrying the join link",
            "A change notice you approve before it emails everyone who registered, free RSVPs included",
            "A running order on the event built from agenda parts",
            "Weekly or monthly recurring series with skipped dates and a fixed end",
            "Zero platform fees on ticket sales through your own Stripe account",
            "Two-way Google, Outlook and CalDAV calendar sync",
            "Direct newsletters to followers with open and click rates",
            "Embeddable calendar and a downloadable QR code for your closing slide"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "webinar hosting, webinar scheduling, webinar registration, paid webinars, recurring webinar series",
        "screenshot": "{{ asset('images/social/for-webinars.jpg') }}",
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
        "name": "How to run a webinar with Event Schedule",
        "description": "Publish the session, take the registrations, and send the join link only to the people who registered.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Paste the link",
                "text": "Create the webinar and paste your meeting or streaming link into the Event URL field. Add a running order if the session has segments."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Open registration",
                "text": "Turn on free registration with a capacity limit, or add ticket types and sell through your own Stripe account with zero platform fees."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Go on air",
                "text": "Everyone who registered gets their own registration page carrying the join link. Swap the link and Event Schedule offers to email them all before it saves."
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
           For-webinars "On Air" styles.

           THE CONCEPT IS A CONTROL ROOM, and the reason it belongs on
           this page is that a webinar genuinely has two outputs.
           PREVIEW is the public listing: the title, the time, and the
           bare domain of the platform. PROGRAM is what a person who
           registered gets: their own registration page, carrying the
           actual join link. Event Schedule is the switcher between the
           two, and both come off ONE `event_url` field - there is no
           platform account connected, so there is nothing to
           reconnect. That is the argument and the metaphor in one
           sentence, so the whole page is built as rack panels: an
           engraved label over every heading, a tally light, a duplex
           PVW/PGM pair, a rundown that is a real table, and a patch
           bay of platform jacks feeding one output.

           COLOUR: the page keeps its inherited teal, spent sparingly
           (one LED per strip, one word per heading) because the
           material here is anodized panel and engraved lettering
           rather than gradient. Measured: #0b6b60 is 5.87 on the
           #f2f6f5 ground and 6.39 on white; #5eead4 is 13.02 on
           #07100f and 12.65 on the rack. The tally red #b91c1c is
           5.94 on the light ground, #f87171 is 6.76 on the rack.

           NEVER text-gray-500 here - it measures 4.83 on pure white
           but only ~4.4 on this tinted ground. Use .es-air-muted
           (#485754, 6.97 on the ground, 7.59 on white).

           BLADE RULE for this block: never use an @supports probe. A
           "#" hex inside a parenthesized at-rule condition breaks
           Blade compilation of every later parenthesized directive.
           ============================================================== */

        /* --- Ground and ink --- */
        .es-air-page { background-color: #f2f6f5; color: #0e1a18; }
        .dark .es-air-page { background-color: #07100f; color: #e7eeec; }
        .es-air-alt { background-color: #e9efee; }
        .dark .es-air-alt { background-color: #0b1514; }
        .es-air-ink { color: #0e1a18; }
        .dark .es-air-ink { color: #e7eeec; }
        .es-air-muted { color: #485754; }
        .dark .es-air-muted { color: #93a6a2; }
        .es-air-accent { color: #0b6b60; }
        .dark .es-air-accent { color: #5eead4; }
        /* Always-lit: for text sitting on a rack panel, which is dark in both modes.
           These three carry no .dark variant on purpose - the rack does not change,
           so neither may its ink. */
        .es-air-lit { color: #5eead4; }
        .es-air-rack-ink { color: #e7eeec; }
        .es-air-rack-lede { color: #d1dbd8; }
        .es-air-rack-note { color: #9aada9; }
        .es-air-red { color: #b91c1c; }
        .dark .es-air-red { color: #f87171; }

        /* --- The panel: every card on this page is a piece of rack kit --- */
        .es-air-card {
            border: 1px solid rgba(14, 26, 24, 0.13);
            border-radius: 0.9rem;
            background: #ffffff;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9);
        }
        .dark .es-air-card {
            border-color: rgba(231, 238, 236, 0.12);
            background: rgba(231, 238, 236, 0.045);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }

        /* --- Engraved label. The typographic signature of the page:
               condensed tabular monospace, wide tracking, no colour. --- */
        .es-air-label {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.66rem;
            font-weight: 700;
            letter-spacing: 0.3em;
            text-transform: uppercase;
            color: #485754;
        }
        .dark .es-air-label { color: #93a6a2; }
        .es-air-num {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            letter-spacing: 0.02em;
        }
        .es-air-rule {
            height: 1px;
            border: 0;
            background: rgba(14, 26, 24, 0.11);
        }
        .dark .es-air-rule { background: rgba(231, 238, 236, 0.12); }

        /* --- Channel strip badge: the section numeral, with its own LED --- */
        .es-air-chan {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.3rem 0.8rem;
            border-radius: 0.3rem;
            border: 1px solid rgba(14, 26, 24, 0.16);
            background: #ffffff;
            color: #0e1a18;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-weight: 800;
            letter-spacing: 0.1em;
            font-size: 0.72rem;
        }
        .dark .es-air-chan {
            border-color: rgba(231, 238, 236, 0.18);
            background: rgba(231, 238, 236, 0.05);
            color: #e7eeec;
        }
        .es-air-chan::before {
            content: "";
            width: 3px;
            align-self: stretch;
            border-radius: 2px;
            background: #0b6b60;
        }
        .dark .es-air-chan::before { background: #5eead4; }

        /* --- The tally light. The only red on the page, and it is on
               the two moments that are actually "live". --- */
        .es-air-tally {
            display: inline-block;
            width: 7px;
            height: 7px;
            flex: none;
            border-radius: 9999px;
            background: #dc2626;
            box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.6);
            animation: es-air-tally 2.6s ease-in-out infinite;
        }
        @keyframes es-air-tally {
            0%, 100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.6); opacity: 1; }
            50% { box-shadow: 0 0 0 5px rgba(220, 38, 38, 0); opacity: 0.55; }
        }
        .es-air-onair {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.15rem 0.5rem;
            border-radius: 0.25rem;
            border: 1px solid rgba(185, 28, 28, 0.35);
            background: rgba(185, 28, 28, 0.08);
        }
        .dark .es-air-onair { border-color: rgba(248, 113, 113, 0.35); background: rgba(248, 113, 113, 0.1); }
        /* Its own rule rather than .es-air-label + .es-air-red, because
           `.dark .es-air-label` would outrank a single-class colour. */
        .es-air-onair-txt {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #b91c1c;
        }
        .dark .es-air-onair-txt { color: #f87171; }
        /* On the two surfaces that are dark in BOTH colour modes (the rack, and
           the unlit half of the duplex) the legend is always the bright red. */
        .es-air-rack .es-air-onair-txt,
        .es-air-screen-off .es-air-onair-txt { color: #f87171; }

        /* --- Mic-check level strip. One peak rides amber, the rest teal. --- */
        .es-air-vu {
            display: inline-flex;
            align-items: flex-end;
            gap: 2px;
            height: 13px;
        }
        .es-air-vu i {
            display: block;
            width: 3px;
            border-radius: 1px;
            background: #0b6b60;
            transform-origin: bottom;
            animation: es-air-vu 1.1s ease-in-out infinite;
        }
        .dark .es-air-vu i { background: #5eead4; }
        .es-air-vu i:nth-child(1) { height: 55%; animation-delay: 0s; }
        .es-air-vu i:nth-child(2) { height: 90%; animation-delay: 0.15s; }
        .es-air-vu i:nth-child(3) { height: 40%; animation-delay: 0.3s; background: #b45309; }
        .dark .es-air-vu i:nth-child(3) { background: #fbbf24; }
        .es-air-vu i:nth-child(4) { height: 72%; animation-delay: 0.45s; }
        .es-air-vu i:nth-child(5) { height: 50%; animation-delay: 0.6s; }
        @keyframes es-air-vu {
            0%, 100% { transform: scaleY(0.4); }
            50% { transform: scaleY(1); }
        }

        /* --- Monitor bezel and the lit screen inside it. The screen is a
               FIXED physical object: a lit panel is lit in both colour
               modes, so it keeps white glass and dark ink either way. --- */
        .es-air-mon {
            border-radius: 0.7rem;
            padding: 0.4rem;
            background: linear-gradient(180deg, #2a3634, #18211f);
            border: 1px solid rgba(231, 238, 236, 0.16);
            box-shadow: 0 10px 24px -12px rgba(0, 0, 0, 0.6), inset 0 1px 0 rgba(255, 255, 255, 0.07);
        }
        .es-air-screen {
            border-radius: 0.45rem;
            background: #ffffff;
            box-shadow: 0 0 22px rgba(94, 234, 212, 0.18);
        }
        .es-air-screen-ink { color: #0e1a18; }
        .es-air-screen-muted { color: #556663; }
        /* The dark half of the duplex: the same bezel with the glass off. */
        .es-air-screen-off {
            border-radius: 0.45rem;
            background: #0d1615;
            box-shadow: inset 0 0 24px rgba(0, 0, 0, 0.6);
        }

        /* --- TAKE: the bar between the two outputs --- */
        .es-air-take {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.15rem 0.55rem;
            border-radius: 0.25rem;
            border: 1px solid rgba(94, 234, 212, 0.35);
            background: #18211f;
            color: #5eead4;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.22em;
            text-transform: uppercase;
        }

        /* --- Patch bay: one jack per platform, an LED in its own brand
               colour, and every one of them terminating in one field. --- */
        .es-air-jack {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.55rem 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(14, 26, 24, 0.12);
            background: rgba(255, 255, 255, 0.72);
            font-size: 0.8rem;
            font-weight: 600;
            color: #0e1a18;
        }
        .dark .es-air-jack {
            border-color: rgba(231, 238, 236, 0.12);
            background: rgba(231, 238, 236, 0.045);
            color: #e7eeec;
        }
        .es-air-led {
            width: 8px;
            height: 8px;
            flex: none;
            border-radius: 9999px;
            background: currentColor;
            box-shadow: 0 0 7px currentColor;
        }
        /* The bus: the jacks do not merely sit above a caption saying they all
           end up in one field, they visibly drop into a rail and the rail runs
           out through a single stub. Abstract strokes only, no illustration. */
        .es-air-bus {
            position: relative;
            height: 1.9rem;
            margin-top: 1.4rem;
            --es-air-wire: rgba(14, 26, 24, 0.2);
        }
        .dark .es-air-bus { --es-air-wire: rgba(231, 238, 236, 0.22); }
        .es-air-bus-drops {
            display: flex;
            justify-content: space-between;
            padding: 0 1.25rem;
        }
        .es-air-bus-drops span {
            display: block;
            width: 1px;
            height: 0.9rem;
            background: var(--es-air-wire);
        }
        .es-air-bus::before {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            top: 0.9rem;
            height: 1px;
            background: var(--es-air-wire);
        }
        .es-air-bus::after {
            content: "";
            position: absolute;
            left: 50%;
            top: 0.9rem;
            bottom: 0;
            width: 1px;
            background: var(--es-air-wire);
        }

        /* --- Rundown: proportional duration bars inside a real table --- */
        .es-air-bar {
            position: relative;
            height: 0.5rem;
            border-radius: 9999px;
            background: rgba(231, 238, 236, 0.1);
            overflow: hidden;
        }
        .es-air-bar-fill {
            display: block;
            height: 100%;
            border-radius: 9999px;
            background: #5eead4;
            transform-origin: left center;
            transition: transform 1.05s cubic-bezier(0.22, 1, 0.36, 1);
            transition-delay: calc(var(--i, 0) * 0.09s + 0.2s);
        }
        html.es-anim [data-reveal]:not(.is-revealed) .es-air-bar-fill { transform: scaleX(0.02); }

        /* --- Series ruler: one slot per week of a recurring series --- */
        .es-air-week {
            display: flex;
            gap: 0.22rem;
            align-items: stretch;
        }
        .es-air-slot {
            flex: 1 1 0;
            min-width: 0;
            height: 2rem;
            border-radius: 0.22rem;
            background: rgba(14, 26, 24, 0.07);
        }
        .dark .es-air-slot { background: rgba(231, 238, 236, 0.08); }
        /* A session. */
        .es-air-slot-on { background: #0b6b60; }
        .dark .es-air-slot-on { background: #5eead4; }
        /* A skipped week: hollow, not merely dimmer, because a date exception
           removes the date rather than greying it out. */
        .es-air-slot-skip {
            background: transparent;
            border: 1px dashed rgba(14, 26, 24, 0.28);
        }
        .dark .es-air-slot-skip { background: transparent; border-color: rgba(231, 238, 236, 0.3); }
        /* The hard out: the recurrence ends itself rather than running on. */
        .es-air-slot-out {
            background: transparent;
            border-radius: 0;
            border-left: 2px solid #b91c1c;
        }
        .dark .es-air-slot-out { background: transparent; border-left-color: #f87171; }

        /* --- Capacity meter --- */
        .es-air-meter {
            height: 0.7rem;
            border-radius: 9999px;
            background: rgba(14, 26, 24, 0.09);
            overflow: hidden;
        }
        .dark .es-air-meter { background: rgba(231, 238, 236, 0.1); }
        .es-air-meter-fill {
            display: block;
            height: 100%;
            border-radius: 9999px;
            background: linear-gradient(90deg, #0b6b60, #12786a);
            transform-origin: left center;
            transition: transform 1.2s cubic-bezier(0.22, 1, 0.36, 1) 0.25s;
        }
        .dark .es-air-meter-fill { background: linear-gradient(90deg, #2dd4bf, #5eead4); }
        html.es-anim [data-reveal]:not(.is-revealed) .es-air-meter-fill { transform: scaleX(0.02); }

        /* --- Plan tags --- */
        .es-air-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.22rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.58rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            border: 1px solid rgba(11, 107, 96, 0.45);
            color: #0b6b60;
        }
        .dark .es-air-plan { border-color: rgba(94, 234, 212, 0.45); color: #5eead4; }
        .es-air-plan-pro { border-color: rgba(14, 26, 24, 0.35); color: #0e1a18; }
        .dark .es-air-plan-pro { border-color: rgba(231, 238, 236, 0.38); color: #e7eeec; }
        .es-air-plan-ent { border-color: rgba(180, 83, 9, 0.5); color: #8a4708; }
        .dark .es-air-plan-ent { border-color: rgba(251, 191, 36, 0.45); color: #fbbf24; }

        /* --- The rack: dark in both colour modes, because a control room
               is. Every shared class that flips with the mode is pinned
               here so the panel renders identically either way. --- */
        .es-air-rack {
            background-color: #0a1413;
            background-image: radial-gradient(125% 100% at 50% 0%, #16211f 0%, #0d1716 55%, #060d0c 100%);
            box-shadow: inset 0 0 100px rgba(0, 0, 0, 0.55), inset 0 1px 0 rgba(231, 238, 236, 0.06);
        }
        .es-air-rack .es-air-card {
            border-color: rgba(231, 238, 236, 0.13);
            background: rgba(231, 238, 236, 0.05);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }
        .es-air-rack .es-air-label { color: #9aada9; }
        .es-air-rack .es-air-chan {
            border-color: rgba(231, 238, 236, 0.18);
            background: rgba(231, 238, 236, 0.05);
            color: #e7eeec;
        }
        .es-air-rack .es-air-chan::before { background: #5eead4; }
        .es-air-rack .es-air-onair { border-color: rgba(248, 113, 113, 0.35); background: rgba(248, 113, 113, 0.1); }
        .es-air-rack .es-air-plan { border-color: rgba(94, 234, 212, 0.45); color: #5eead4; }
        .es-air-rack .es-air-plan-pro { border-color: rgba(231, 238, 236, 0.38); color: #e7eeec; }
        .es-air-rack .es-air-plan-ent { border-color: rgba(251, 191, 36, 0.45); color: #fbbf24; }
        .es-air-rack .es-air-rule { background: rgba(231, 238, 236, 0.12); }
        /* The finale's button lives on the rack, so it is the dark-mode button in
           both colour modes: light teal fill, dark ink. */
        .es-air-rack .es-air-btn {
            background-color: #5eead4;
            color: #07100f;
            box-shadow: 0 18px 36px -14px rgba(94, 234, 212, 0.35);
        }
        .es-air-rack .es-air-btn:hover {
            background-color: #99f6e4;
            box-shadow: 0 22px 44px -14px rgba(94, 234, 212, 0.45);
        }
        .es-air-rack .grid-overlay {
            background-image:
                linear-gradient(rgba(231, 238, 236, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(231, 238, 236, 0.05) 1px, transparent 1px);
        }
        .es-air-rack .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-air-rack .es-claim:focus-within {
            border-color: rgba(94, 234, 212, 0.75);
            box-shadow: 0 0 0 4px rgba(94, 234, 212, 0.22);
        }

        /* --- Links and buttons --- */
        .es-air-link { color: #0b6b60; }
        .es-air-link:hover { color: #0e1a18; }
        .dark .es-air-link { color: #5eead4; }
        .dark .es-air-link:hover { color: #e7eeec; }
        .es-air-btn {
            background-color: #0b6b60;
            box-shadow: 0 18px 36px -14px rgba(11, 107, 96, 0.55);
        }
        .es-air-btn:hover { background-color: #085048; box-shadow: 0 22px 44px -14px rgba(11, 107, 96, 0.65); }
        .dark .es-air-btn { background-color: #5eead4; color: #07100f; }
        .dark .es-air-btn:hover { background-color: #99f6e4; }

        /* --- Hover states on cards that are links, and on the FAQ --- */
        .es-air-hover:hover { border-color: rgba(11, 107, 96, 0.5); }
        .dark .es-air-hover:hover { border-color: rgba(94, 234, 212, 0.45); }
        .es-air-hover:hover .es-air-hover-title,
        .es-air-hover:hover .es-air-hover-arrow { color: #0b6b60; }
        .dark .es-air-hover:hover .es-air-hover-title,
        .dark .es-air-hover:hover .es-air-hover-arrow { color: #5eead4; }

        /* --- Marquee chips --- */
        .es-air-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            white-space: nowrap;
            padding: 0.35rem 0.85rem;
            border-radius: 0.4rem;
            border: 1px solid rgba(14, 26, 24, 0.14);
            background: rgba(255, 255, 255, 0.75);
            color: #485754;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }
        .dark .es-air-chip {
            border-color: rgba(231, 238, 236, 0.14);
            background: rgba(231, 238, 236, 0.05);
            color: #93a6a2;
        }
        /* The chip LED is the page's own lamp rather than a brand colour, so it
           has to brighten with the mode like every other lit thing here. It
           cannot be an inline style or the dark rule could never reach it. */
        .es-air-chip .es-air-led { color: #0b6b60; }
        .dark .es-air-chip .es-air-led { color: #5eead4; }

        /* --- Shared-system recolours (brand blue by default) --- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(11, 107, 96, 0.13), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(94, 234, 212, 0.11), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(11, 107, 96, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(94, 234, 212, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #0b6b60; }
        .dark .es-dot.is-active .es-dot-pip { background: #5eead4; }

        /* --- Focus rings. No border-radius here: setting it would change the
               element's own shape on focus, and outlines already follow it. --- */
        #es-air-page a:focus-visible,
        #es-air-page summary:focus-visible,
        #es-air-page button:focus-visible {
            outline: 2px solid #0b6b60;
            outline-offset: 3px;
        }
        .dark #es-air-page a:focus-visible,
        .dark #es-air-page summary:focus-visible,
        .dark #es-air-page button:focus-visible {
            outline-color: #5eead4;
        }
        .es-air-rack a:focus-visible,
        .es-air-rack summary:focus-visible,
        .es-air-rack button:focus-visible {
            outline-color: #5eead4 !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-air-tally, .es-air-vu i { animation: none !important; }
            .es-air-bar-fill, .es-air-meter-fill { transform: none !important; transition: none !important; }
        }
    </style>

    @php
        // A one-hour product webinar's running order. These are event parts:
        // name plus an optional start and end time, typed in on the Agenda
        // section of the event, ungated. Each bar is a percentage of the SAME
        // track width, so the number is minutes/60 rounded and equal durations
        // must carry equal widths - the two five-minute segments both read 8.
        $rundown = [
            ['14:00', 'Welcome and housekeeping', '5 min', 8],
            ['14:05', 'What shipped this quarter', '15 min', 25],
            ['14:20', 'Live walkthrough', '20 min', 33],
            ['14:40', 'Questions from the room', '15 min', 25],
            ['14:55', 'Where to go next', '5 min', 8],
        ];

        // Twelve labels, one field. The LED is each platform's own brand
        // colour; the label is page ink, so the colour never has to carry
        // any text contrast. Event Schedule holds no account on any of them.
        $jacks = [
            ['Zoom', '#2D8CFF'],
            ['Google Meet', '#00832D'],
            ['Microsoft Teams', '#6264A7'],
            ['Webex', '#00BCEB'],
            ['YouTube Live', '#FF0000'],
            ['Twitch', '#9146FF'],
            ['Instagram Live', '#E1306C'],
            ['Vimeo', '#1AB7EA'],
            ['Whereby', '#39D2C0'],
            ['Jitsi Meet', '#1D76BA'],
            ['StreamYard', '#EE4B4B'],
            ['A page you host yourself', '#0b6b60'],
        ];

        // A weekly series: thirteen Thursdays with the sixth skipped for a
        // holiday, which is the twelve sessions the copy states, plus a hard
        // out so the recurrence ends itself instead of running on.
        $weeks = [];
        foreach (range(1, 14) as $w) {
            if ($w === 14) {
                $weeks[] = 'out';
            } elseif ($w === 6) {
                $weeks[] = 'skip';
            } else {
                $weeks[] = 'on';
            }
        }

        $faqs = [
            [
                'q' => 'What video platforms does Event Schedule work with?',
                'a' => 'Any platform that gives you a meeting or streaming link. Zoom, Google Meet, Microsoft Teams, Webex, YouTube Live, Twitch, Vimeo, something you host yourself. An online event carries one Event URL field, and Event Schedule stores whatever you paste into it. To be plain about what that means: there is no account connected to a platform, nothing signs in on your behalf and no meeting is started for you, so there is also nothing to reconnect and nothing that breaks when a platform changes its API. The one exception runs the other way: if you sync a Microsoft 365 calendar you can ask it to create a Teams meeting for online events, and the join link it returns is written back into the field for you.',
            ],
            [
                'q' => 'Is the join link visible to the public?',
                'a' => 'No. The public event page shows the domain of your link as the location, as plain text rather than something to click, and the downloadable calendar file carries no location for an online event at all. The full link sits on the registration page each attendee is given after they sign up, which is reachable only through the private address in their confirmation. A public listing is not an open door.',
            ],
            [
                'q' => 'Can I charge for webinars?',
                'a' => 'Yes, and you can start on the free plan: 25 paid tickets a month, per schedule. Connect your own Stripe account, add as many named ticket types as the session needs, each with its own price, quantity and sales window, and Event Schedule charges zero platform fees on every plan. Stripe charges its own standard processing fee, approximately 2.9% plus $0.30 a transaction. Scanning a ticket\'s QR code is free on every plan, for the sessions you also run in a room. Pro at '.plan_price($proMonthly).' a month takes the monthly ceiling off and adds the rest of the door tooling: the live check-in dashboard, the sold-out ticket waitlist, promo codes and add-ons. Free registration with a capacity limit is unlimited and never counts against the 25.',
            ],
            [
                'q' => 'Can I schedule a recurring webinar series?',
                'a' => 'Yes, on the free plan. Set the days of the week it runs, add date exceptions for the weeks you are skipping, and give the recurrence an end: either a closing date or a number of sessions, so a series that is meant to be twelve weeks long stops after twelve. Registration capacity is counted per session date, so next week starts empty even though it is the same event.',
            ],
            [
                'q' => 'Do people who registered find out if I move the session?',
                'a' => 'Yes, once you say so. Change the join link or the venue and Event Schedule stops on the way to saving and asks whether to email everyone who registered, with a short note you can write into it; cancelling asks the same way. On a one-off session moving the date or the time asks too, though on a recurring series the prompt covers the link and the venue rather than the weekly time. Free registrations are on the list either way. Followers are different: following your schedule puts somebody on a list you can email, and you write and send that newsletter yourself. Nothing is sent to followers automatically when you add a session.',
            ],
            [
                'q' => 'Is Event Schedule free for hosting webinars?',
                'a' => 'Yes. Unlimited webinars, the running order on each one, recurring series, free registration with a capacity limit, two-way calendar sync, the embeddable calendar and built-in analytics are all free forever, and so is selling your first 25 paid tickets a month and scanning those tickets in. Pro at '.plan_price($proMonthly).' a month removes that ceiling and adds the live check-in dashboard, custom questions on the registration form and the sold-out ticket waitlist, extra team members are on Enterprise, and there are zero platform fees on ticket sales at every plan level. On the hosted service, attendee email goes out through your own SMTP details, which you add once in the integrations tab on any plan.',
            ],
        ];

        $dotSections = [
            ['top', 'On air'],
            ['path', 'The signal path'],
            ['platform', 'The patch bay'],
            ['rundown', 'The rundown'],
            ['series', 'The series'],
            ['register', 'Registration'],
            ['rest', 'Everything else'],
            ['who', 'Perfect for'],
            ['faq', 'Questions'],
            ['claim', 'Go live'],
        ];
    @endphp

    <div id="es-air-page" class="es-air-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the duplex. Public listing vs registered attendee.  -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(11, 107, 96, 0.22), rgba(11, 107, 96, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(94, 234, 212, 0.14), rgba(94, 234, 212, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex flex-wrap items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="h-5 w-5 es-air-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <span class="es-air-muted text-sm font-medium tracking-wide">For webinar hosts and teams</span>
                        <span class="es-air-onair" aria-hidden="true">
                            <span class="es-air-tally"></span>
                            <span class="es-air-onair-txt">On air</span>
                        </span>
                    </div>

                    <h1 class="es-balance es-air-ink mb-8 text-[2.5rem] font-black leading-[1.04] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">Announce the session.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="es-air-accent">Not the link.</span></span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-air-muted mb-6 max-w-xl text-lg sm:text-xl">
                        Your public schedule shows the webinar, the time and the bare domain of wherever it is happening. The join link itself rides on the registration each attendee gets, so publishing a session is not the same as leaving the room open.
                    </p>
                    <p class="es-fade-up es-d-2 es-air-muted mb-10 max-w-xl text-base">
                        Webinar scheduling with built-in registration, paid ticketing at zero platform fees, recurring series and two-way calendar sync, for educators, marketers and internal comms teams.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="#path" class="glass group inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            Follow the signal path
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="es-air-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Create your webinar schedule
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- The duplex: PVW is the public listing, PGM is what a
                     registered attendee sees. Both are drawn from one field. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-air-card p-5 sm:p-6">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <p class="es-air-label">Two outputs, one field</p>
                            <span class="es-air-vu" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></span>
                        </div>

                        <div class="es-air-mon mb-2">
                            <div class="mb-1.5 flex items-center justify-between px-1">
                                <span class="es-air-label" style="color: #9aada9;">PVW &middot; public listing</span>
                                <span class="es-air-num text-[0.6rem] font-bold" style="color: #9aada9;">ANYONE</span>
                            </div>
                            <div class="es-air-screen p-4">
                                <p class="es-air-screen-ink text-base font-bold">Product Deep Dive</p>
                                <p class="es-air-screen-muted es-air-num mt-0.5 text-xs">Thu 14:00 &middot; 60 min</p>
                                <hr class="my-3 border-0" style="height: 1px; background: rgba(14, 26, 24, 0.1);">
                                <div class="flex items-center gap-2">
                                    <svg aria-hidden="true" class="h-4 w-4 flex-none es-air-screen-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-18 0h18M12 3c2 3 2 15 0 18M12 3c-2 3-2 15 0 18" /></svg>
                                    <span class="es-air-screen-ink es-air-num text-sm font-semibold">zoom.us</span>
                                </div>
                                <p class="es-air-screen-muted mt-2 text-[0.65rem]">The domain, as plain text. Nothing to click.</p>
                            </div>
                        </div>

                        <div class="my-3 flex items-center gap-3" aria-hidden="true">
                            <hr class="es-air-rule flex-1">
                            <span class="es-air-take">
                                Take
                                <svg aria-hidden="true" class="h-3 w-3 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </span>
                            <hr class="es-air-rule flex-1">
                        </div>

                        <div class="es-air-mon">
                            <div class="mb-1.5 flex items-center justify-between px-1">
                                <span class="es-air-label" style="color: #5eead4;">PGM &middot; after registering</span>
                                <span class="es-air-num text-[0.6rem] font-bold" style="color: #5eead4;">THEM ONLY</span>
                            </div>
                            <div class="es-air-screen-off p-4">
                                <div class="mb-2 flex items-center gap-2">
                                    <span class="es-air-tally"></span>
                                    <span class="es-air-onair-txt">Registered</span>
                                </div>
                                <p class="es-air-num break-all text-sm font-semibold" style="color: #5eead4;">https://zoom.us/j/8814920733</p>
                                <p class="mt-2 text-[0.65rem]" style="color: #9aada9;">On their own registration page, linked from their confirmation email.</p>
                            </div>
                        </div>

                        <p class="es-air-muted mt-5 border-t pt-4 text-xs" style="border-color: rgba(14, 26, 24, 0.1);">
                            One Event URL field on the event feeds both. Add a venue as well and the session goes out hybrid: the address in public, the link on the registration.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Session-type chips -->
            <div class="es-fade-up es-d-4 mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach (['Product demos', 'Training sessions', 'Workshops', 'Panel discussions', 'All-hands', 'Lectures', 'Q&A sessions', 'Onboarding', 'Office hours', 'Launch briefings'] as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-air-chip">
                                        <span class="es-air-led" aria-hidden="true"></span>
                                        {{ $chip }}
                                    </span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The signal path (rack panel, dark in both modes)          -->
    <!-- ============================================================ -->
    <section id="path" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-air-rack noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 25%, rgba(94, 234, 212, 0.13), rgba(94, 234, 212, 0) 60%); opacity: 0.5;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="es-air-onair pointer-events-none absolute z-10 hidden sm:inline-flex" style="top: 1.25rem; right: 1.25rem;" aria-hidden="true">
                <span class="es-air-tally"></span>
                <span class="es-air-onair-txt">On air</span>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-air-chan mb-6" data-reveal aria-hidden="true"><span>CH 02</span></div>
                    <p class="es-air-label mb-4" data-reveal style="--reveal-delay: 0.05s;">The signal path</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Where the join link actually <span class="es-air-lit">goes.</span>
                    </h2>
                    <p class="es-air-rack-lede mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Three surfaces, in the order a webinar hits them.
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    <div class="es-air-card p-6" data-reveal="panel">
                        <p class="es-air-label mb-3">01 &middot; Public</p>
                        <h3 class="mb-2 text-lg font-bold es-air-rack-ink">The listing anyone can read</h3>
                        <p class="es-air-rack-note text-sm">Title, date, time, description, running order, and the domain of your link shown as plain text. The downloadable calendar file for an online session carries no location at all.</p>
                    </div>
                    <div class="es-air-card p-6" data-reveal="panel">
                        <p class="es-air-label mb-3">02 &middot; Registered</p>
                        <h3 class="mb-2 text-lg font-bold es-air-rack-ink">The page only they have</h3>
                        <p class="es-air-rack-note text-sm">Registering hands each attendee their own page, at a private address, carrying the join link. Their confirmation email links straight back to it, so nobody has to keep a message to find the room.</p>
                    </div>
                    <div class="es-air-card p-6" data-reveal="panel">
                        <p class="es-air-label mb-3">03 &middot; Changed</p>
                        <h3 class="mb-2 text-lg font-bold es-air-rack-ink">When you move it</h3>
                        <p class="es-air-rack-note text-sm">Change the join link or the venue and Event Schedule stops on the way to saving to ask whether to email everyone who registered. Free registrations count, and cancelling asks the same way.</p>
                    </div>
                </div>

                <!-- Rack meters: three numbers that are true, as gauges. -->
                <div class="mt-10 grid gap-6 sm:grid-cols-3" data-reveal-group="90">
                    <div class="es-air-card p-6 text-center" data-reveal>
                        <p class="es-air-num text-4xl font-black es-air-rack-ink">1</p>
                        <p class="es-air-label mt-2">Link field per event</p>
                    </div>
                    <div class="es-air-card p-6 text-center" data-reveal>
                        <p class="es-air-num text-4xl font-black es-air-lit">{{ plan_price(0) }}</p>
                        <p class="es-air-label mt-2">Platform fee on sales</p>
                    </div>
                    <div class="es-air-card p-6 text-center" data-reveal>
                        <p class="es-air-num text-4xl font-black es-air-rack-ink">3</p>
                        <p class="es-air-label mt-2">Calendars synced two ways</p>
                    </div>
                </div>

                <p class="es-air-rack-lede mt-10 text-center" data-reveal>
                    On the hosted service, attendee email leaves through your own SMTP details, added once in the integrations tab on any plan.
                    <a href="#platform" class="es-air-lit inline-flex items-center gap-1 font-semibold transition-all hover:gap-2">
                        Next, the patch bay
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The patch bay: any link, and no integration to break      -->
    <!-- ============================================================ -->
    <section id="platform" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-air-chan mb-6" data-reveal aria-hidden="true"><span>CH 03</span></div>
                <p class="es-air-label mb-4" data-reveal style="--reveal-delay: 0.05s;">The patch bay</p>
                <h2 class="es-balance es-air-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Twelve labels. <span class="es-air-accent">One field.</span>
                </h2>
                <p class="es-air-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Everything below terminates in the same place: the Event URL box on your webinar. Which is another way of saying Event Schedule does not hold an account on any of them.
                </p>
            </div>

            <div class="grid items-start gap-8 lg:grid-cols-[1.15fr_1fr]">
                <div class="es-air-card p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <p class="es-air-label">Inputs</p>
                        <p class="es-air-label">LED = brand</p>
                    </div>
                    <div class="grid gap-2.5 sm:grid-cols-2" data-reveal-group="45">
                        @foreach ($jacks as [$jackName, $jackColor])
                            <div class="es-air-jack" data-reveal>
                                <span class="es-air-led" style="color: {{ $jackColor }};" aria-hidden="true"></span>
                                {{ $jackName }}
                            </div>
                        @endforeach
                    </div>
                    <div class="es-air-bus" aria-hidden="true">
                        <div class="es-air-bus-drops">
                            @for ($drop = 0; $drop < 6; $drop++)
                                <span></span>
                            @endfor
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center justify-center gap-3">
                        <p class="es-air-label">Output</p>
                        <code class="es-air-num es-air-accent rounded px-2 py-1 text-sm font-bold" style="background: rgba(11, 107, 96, 0.1);">event_url</code>
                        <span class="es-air-plan">Free</span>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="es-air-card p-6" data-reveal="panel">
                        <h3 class="es-air-ink mb-2 text-lg font-bold">Nothing to reconnect</h3>
                        <p class="es-air-muted text-sm">No sign-in, no token, no account linked to your meeting provider. Event Schedule stores the string you paste and hands it to the people who registered, which means a platform changing its API cannot break your schedule.</p>
                    </div>
                    <div class="es-air-card p-6" data-reveal="panel">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="es-air-ink text-lg font-bold">One exception, and it works backwards</h3>
                            <span class="es-air-plan">Free</span>
                        </div>
                        <p class="es-air-muted text-sm">Sync a Microsoft 365 calendar and you can ask it to create a Teams meeting for your online sessions. The join link it returns is written back into the Event URL field for you, so you never copy it by hand.</p>
                    </div>
                    <div class="es-air-card p-6" data-reveal="panel">
                        <h3 class="es-air-ink mb-2 text-lg font-bold">Half online, half in a room</h3>
                        <p class="es-air-muted text-sm">Give the session a venue as well as a link and it is published as hybrid, with the address for the people in the room and the link for the people who are not. Learn how <a href="{{ marketing_url('/features/online-events') }}" class="es-air-link font-medium hover:underline">online events</a> are put together.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. The rundown: the running order, as a real table           -->
    <!-- ============================================================ -->
    <section id="rundown" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-air-rack noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 30%, rgba(94, 234, 212, 0.11), rgba(94, 234, 212, 0) 60%); opacity: 0.5;"></div>
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-4xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-air-chan mb-6" data-reveal aria-hidden="true"><span>CH 04</span></div>
                    <p class="es-air-label mb-4" data-reveal style="--reveal-delay: 0.05s;">The rundown</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        An hour is not <span class="es-air-lit">one block.</span>
                    </h2>
                    <p class="es-air-rack-lede mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        A webinar has segments, and people decide whether to come by reading them. Type the running order onto the event and it publishes with it, on the free plan.
                    </p>
                </div>

                <div class="es-air-card p-5 sm:p-7" data-reveal="panel">
                    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-bold es-air-rack-ink">Product Deep Dive</h3>
                            <p class="es-air-num es-air-rack-note text-xs">Thu 14:00 &middot; 60 min &middot; 5 segments</p>
                        </div>
                        <span class="es-air-plan">Free</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-left" style="min-width: 26rem;">
                            <caption class="sr-only">Running order for a one-hour product webinar, with each segment's start time and duration</caption>
                            <thead>
                                <tr>
                                    <th scope="col" class="es-air-label pb-3">In</th>
                                    <th scope="col" class="es-air-label pb-3">Segment</th>
                                    <th scope="col" class="es-air-label pb-3 text-right">Dur</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rundown as $rowIndex => [$rIn, $rName, $rDur, $rShare])
                                    <tr class="border-t" style="border-color: rgba(231, 238, 236, 0.1);">
                                        <td class="es-air-num es-air-rack-note py-3 pe-3 align-top text-sm">{{ $rIn }}</td>
                                        <th scope="row" class="py-3 pe-4 align-top text-sm font-bold es-air-rack-ink">
                                            {{ $rName }}
                                            <span class="es-air-bar mt-2 block max-w-[16rem]" aria-hidden="true">
                                                <span class="es-air-bar-fill" style="--i: {{ $rowIndex }}; width: {{ $rShare }}%;"></span>
                                            </span>
                                        </th>
                                        <td class="es-air-num es-air-rack-note py-3 align-top text-right text-sm">{{ $rDur }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <hr class="es-air-rule mt-5">
                    <p class="es-air-rack-note mt-4 text-xs">Each segment is a named part with an optional start and end time, moved up or down into order. The bar is its share of the hour.</p>
                </div>

                <div class="mt-6 grid gap-6 md:grid-cols-3" data-reveal-group="100">
                    <div class="es-air-card p-6" data-reveal="panel">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-bold es-air-rack-ink">Paste the agenda instead</h3>
                            <span class="es-air-plan es-air-plan-ent">Enterprise</span>
                        </div>
                        <p class="es-air-rack-note text-sm">Hand a written agenda to the scanner and the segments are created for you. Typing them yourself is free, so this buys back the typing and nothing else.</p>
                    </div>
                    <div class="es-air-card p-6" data-reveal="panel">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-bold es-air-rack-ink">Let the room vote</h3>
                            <span class="es-air-plan es-air-plan-pro">Pro</span>
                        </div>
                        <p class="es-air-rack-note text-sm">Add a poll to the session and let people pick which of two walkthroughs the second half should be. One thing to know before you plan around it: casting a vote needs the voter signed in to Event Schedule, so a name and an email at registration is not enough.</p>
                    </div>
                    <div class="es-air-card p-6" data-reveal="panel">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-bold es-air-rack-ink">Ask afterwards</h3>
                            <span class="es-air-plan es-air-plan-pro">Pro</span>
                        </div>
                        <p class="es-air-rack-note text-sm">Collect a star rating and a comment from attendees once the session is over, so the next rundown is written from something better than a hunch.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. The series: recurrence with a hard out                    -->
    <!-- ============================================================ -->
    <section id="series" class="es-air-alt scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-air-chan mb-6" data-reveal aria-hidden="true"><span>CH 05</span></div>
                <p class="es-air-label mb-4" data-reveal style="--reveal-delay: 0.05s;">The series</p>
                <h2 class="es-balance es-air-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Twelve Thursdays, and then it <span class="es-air-accent">stops.</span>
                </h2>
                <p class="es-air-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    A weekly webinar is one recurring event, not twelve entries. Set the days it runs, take out the weeks you are skipping, and give the recurrence an end so it is not still advertising itself in March.
                </p>
            </div>

            <div class="es-air-card p-6 sm:p-8" data-reveal="panel">
                <div class="mb-4 flex flex-wrap items-baseline justify-between gap-2">
                    <h3 class="es-air-ink text-lg font-bold">Onboarding Live</h3>
                    <span class="es-air-muted es-air-num text-xs">Weekly &middot; Thursdays 14:00 &middot; 12 sessions</span>
                </div>

                <div aria-hidden="true">
                    <div class="es-air-week">
                        @foreach ($weeks as $wState)
                            <span class="es-air-slot @if ($wState === 'on') es-air-slot-on @elseif ($wState === 'skip') es-air-slot-skip @else es-air-slot-out @endif"></span>
                        @endforeach
                    </div>
                    <div class="es-air-label mt-2 flex gap-4" style="letter-spacing: 0.18em;">
                        <span>Solid = a session</span>
                        <span>Dashed = date exception</span>
                        <span class="es-air-red">Line = hard out</span>
                    </div>
                </div>

                <hr class="es-air-rule my-6">

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="90">
                    <div data-reveal>
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h4 class="es-air-ink font-bold">The days it runs</h4>
                            <span class="es-air-plan">Free</span>
                        </div>
                        <p class="es-air-muted text-sm">Pick the days of the week and one start time. Thursdays at two is a single event that keeps producing Thursdays.</p>
                    </div>
                    <div data-reveal>
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h4 class="es-air-ink font-bold">The weeks you skip</h4>
                            <span class="es-air-plan">Free</span>
                        </div>
                        <p class="es-air-muted text-sm">Date exceptions take individual dates out, and a removed date is simply absent from the calendar rather than shown crossed through.</p>
                    </div>
                    <div data-reveal>
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h4 class="es-air-ink font-bold">The end</h4>
                            <span class="es-air-plan">Free</span>
                        </div>
                        <p class="es-air-muted text-sm">A closing date, or a number of sessions. This is the setting that makes a series a series instead of a weekly slot nobody remembers to switch off.</p>
                    </div>
                </div>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2" data-reveal-group="90">
                <div class="es-air-card p-6" data-reveal="panel">
                    <div class="mb-2 flex flex-wrap items-center gap-2">
                        <h3 class="es-air-ink text-lg font-bold">A format you run again and again</h3>
                        <span class="es-air-plan es-air-plan-pro">Pro</span>
                    </div>
                    <p class="es-air-muted text-sm">Save a session as a template and start the next one from it. On any plan you can also clone an existing webinar, which covers most of the same ground for the price of nothing.</p>
                </div>
                <div class="es-air-card p-6" data-reveal="panel">
                    <div class="mb-2 flex flex-wrap items-center gap-2">
                        <h3 class="es-air-ink text-lg font-bold">Several strands on one link</h3>
                        <span class="es-air-plan">Free</span>
                    </div>
                    <p class="es-air-muted text-sm">Sub-schedules sort and colour-code your sessions, so a customer-training strand reads separately from the launch briefings. They organise; they do not hide. To keep something unpublished, leave it a draft.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Registration desk                                         -->
    <!-- ============================================================ -->
    <section id="register" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-start gap-12 lg:grid-cols-2">
                <div>
                    <div class="es-air-chan mb-6" data-reveal aria-hidden="true"><span>CH 06</span></div>
                    <p class="es-air-label mb-4" data-reveal style="--reveal-delay: 0.05s;">Registration</p>
                    <h2 class="es-balance es-air-ink mb-5 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                        Counted per session, <span class="es-air-accent">not per series.</span>
                    </h2>
                    <p class="es-air-muted mb-6 text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        Free registration takes a name and an email, holds a place, and caps the room if you want it capped. The count belongs to the date, so a full session this Thursday does not make next Thursday look full.
                    </p>

                    <div class="es-air-card p-6" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-baseline justify-between gap-2">
                            <p class="es-air-label">Thu 14:00 &middot; places taken</p>
                            <p class="es-air-ink es-air-num text-sm font-bold">84 / 120</p>
                        </div>
                        <div class="es-air-meter" aria-hidden="true">
                            <span class="es-air-meter-fill" style="width: 70%;"></span>
                        </div>
                        <div class="es-air-label mt-3 flex justify-between">
                            <span>36 left on this date</span>
                            <span>Next Thu: 0 / 120</span>
                        </div>
                        <hr class="es-air-rule my-6">
                        <p class="es-air-muted text-sm">Each registration becomes its own page carrying the join link, and its confirmation email links back to it.</p>
                    </div>
                </div>

                <div class="space-y-4" data-reveal-group="90">
                    <div class="es-air-card p-6" data-reveal="panel">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="es-air-ink text-lg font-bold">Free registration with a cap</h3>
                            <span class="es-air-plan">Free</span>
                        </div>
                        <p class="es-air-muted text-sm">Turn it on, set the limit, and Event Schedule stops taking names when the date is full. No plan, no card, no platform fee, because nothing changed hands.</p>
                    </div>
                    <div class="es-air-card p-6" data-reveal="panel">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="es-air-ink text-lg font-bold">Charging for the session</h3>
                            <span class="es-air-plan">Free</span>
                            <span class="es-air-plan es-air-plan-pro">Pro</span>
                        </div>
                        <p class="es-air-muted text-sm">Connect your own Stripe account and add named ticket types, each with its own price, quantity and sales window. The first 25 paid tickets a month are on the free plan, and scanning their QR codes is free too if the session also has a room; Pro takes the ceiling off and adds the live check-in dashboard. Event Schedule takes zero platform fees either way, so past Stripe's own processing the money is yours. See <a href="{{ marketing_url('/features/ticketing') }}" class="es-air-link font-medium hover:underline">how ticketing works</a>.</p>
                    </div>
                    <div class="es-air-card p-6" data-reveal="panel">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="es-air-ink text-lg font-bold">Ask what you need to know</h3>
                            <span class="es-air-plan es-air-plan-pro">Pro</span>
                        </div>
                        <p class="es-air-muted text-sm">Custom questions on the form collect their job title, their team or the one thing they want covered, answered at the point of registering rather than chased afterwards.</p>
                    </div>
                    <div class="es-air-card p-6" data-reveal="panel">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="es-air-ink text-lg font-bold">When a session fills up</h3>
                            <span class="es-air-plan">Free</span>
                            <span class="es-air-plan es-air-plan-pro">Pro</span>
                        </div>
                        <p class="es-air-muted text-sm">A free session that hits its cap turns its own form into a waitlist, on every plan; the waitlist behind sold-out paid tickets is the Pro one. Either way, when a place comes back the person who has waited longest is emailed automatically and has 24 hours to take it.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Everything else: bento                                    -->
    <!-- ============================================================ -->
    <section id="rest" class="es-air-alt scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-air-chan mb-6" data-reveal aria-hidden="true"><span>CH 07</span></div>
                <p class="es-air-label mb-4" data-reveal style="--reveal-delay: 0.05s;">Everything else</p>
                <h2 class="es-balance es-air-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    The rest of the webinar rack
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">
                <!-- 1 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-air-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-air-ink text-xl font-bold">Email the people who already turn up</h3>
                                <span class="es-air-plan">Free</span>
                                <span class="es-air-vu" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></span>
                            </div>
                            <p class="es-air-muted mb-4">People follow your schedule, which puts them on a list you can write to: the next series announced, the recording posted, the thing you promised to send. A list can also be cut down to just the people who registered for one particular session, which is usually the list you actually wanted. Open and click rates afterwards tell you whether it landed.</p>
                            <p class="es-air-muted text-sm">The number worth knowing first: 10 emails a month on Free, 100 on Pro, 1,000 on Enterprise, counted per recipient rather than per send. And nothing goes to followers unless you send it, so adding a session is not a broadcast.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-air-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-air-ink text-xl font-bold">On the site you already have</h3>
                                <span class="es-air-plan">Free</span>
                            </div>
                            <p class="es-air-muted">Embed the calendar on your own pages so the series lives where people look you up. The registration form embeds too, on the free plan; the ticket purchase form is on Pro.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-air-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-air-ink text-xl font-bold">In the calendar you live in</h3>
                                <span class="es-air-plan">Free</span>
                            </div>
                            <p class="es-air-muted">Two-way sync with Google, Outlook and CalDAV. Move a session in either place and the other one follows. A recurring series syncs across as one entry; the subscribe feed is what unrolls the individual dates.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-air-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-air-ink text-xl font-bold">The closing slide, and the announcement image</h3>
                                <span class="es-air-plan">Free</span>
                                <span class="es-air-plan es-air-plan-pro">Pro</span>
                            </div>
                            <p class="es-air-muted mb-4">Download a QR code for your schedule and put it on the last slide, so the people already watching can follow the series before they close the tab. That one costs nothing on any plan.</p>
                            <p class="es-air-muted text-sm">On Pro you can also generate one share graphic of your next sessions, up to twenty of them, in a story, square, portrait or landscape crop. It is built from the sessions that carry their own image, so the titles and the times are already correct. Built-in analytics, free on every plan, then show page views, devices and where the traffic came from, which is what they measure and nothing more.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-air-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-air-ink text-xl font-bold">Announce when you are ready</h3>
                                <span class="es-air-plan">Free</span>
                            </div>
                            <p class="es-air-muted">A session you have not announced sits on your calendar as a draft, yours to see and nobody else's, until you publish it. Internal and unlisted sessions, including a password, are on Enterprise.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-air-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-air-ink text-xl font-bold">More than one host, and the wiring underneath</h3>
                                <span class="es-air-plan es-air-plan-ent">Enterprise</span>
                                <span class="es-air-plan es-air-plan-pro">Pro</span>
                            </div>
                            <p class="es-air-muted mb-4">Being straight about this one: Free and Pro are a single team member. Extra people who can create and edit sessions are an Enterprise thing, capped at five, along with your own domain on the schedule.</p>
                            <p class="es-air-muted text-sm">On Pro there is a full REST API for events, schedules and sales, plus webhooks that fire on a sale, an event change or a check-in, if your registrations need to land somewhere else as well.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Perfect for                                               -->
    <!-- ============================================================ -->
    <section id="who" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-air-chan mb-6" data-reveal aria-hidden="true"><span>CH 08</span></div>
                <h2 class="es-balance es-air-ink mb-4 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    Perfect for all types of <span class="es-air-accent">webinars</span>
                </h2>
                <p class="es-air-muted text-lg sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    A product demo and a company all-hands are the same shape: a time, a link, and a list of people who said they were coming. Running a whole multi-day programme? See Event Schedule for <a href="{{ marketing_url('/for-virtual-conferences') }}" class="es-air-link font-medium hover:underline">virtual conferences</a>.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <x-sub-audience-card
                    name="Product Demos"
                    description="Show the product on a standing slot, cap the room, and keep the join link off the public listing."
                    icon-color="cyan"
                    blog-slug="for-product-demos"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Training & Onboarding"
                    description="One recurring event covers the whole intake, with capacity counted separately for every week."
                    icon-color="teal"
                    blog-slug="for-training-onboarding"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Educational Lectures"
                    description="Publish the running order so students can see what each session covers before they sign up."
                    icon-color="sky"
                    blog-slug="for-educational-lectures"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Industry Panels"
                    description="Put the panellists in the running order so people can read who is on before they sign up."
                    icon-color="blue"
                    blog-slug="for-industry-panels"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Company All-Hands"
                    description="Keep the standing slot on a schedule, and hold the ones you have not announced as drafts."
                    icon-color="amber"
                    blog-slug="for-company-all-hands"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Customer Workshops"
                    description="Charge for the hands-on ones through your own Stripe account, and keep every penny past processing."
                    icon-color="emerald"
                    blog-slug="for-customer-workshops"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Three steps: the countdown                                -->
    <!-- ============================================================ -->
    <section class="es-air-alt scroll-mt-24 py-20 lg:py-24">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-2xl text-center">
                <p class="es-air-label mb-4" data-reveal>Countdown</p>
                <h2 class="es-balance es-air-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Three steps to a webinar people show up to
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="120">
                @foreach ([
                    ['03', 'Paste the link', 'Create the session, paste your meeting or streaming link into the Event URL field, and type the running order if it has segments.'],
                    ['02', 'Open registration', 'Free registration with a capacity limit, or named ticket types through your own Stripe account. Either way the platform fee is zero.'],
                    ['01', 'Go on air', 'Everyone who registered has their own page with the join link. Swap the link and you are asked whether to email them all.'],
                ] as [$stepNum, $stepTitle, $stepBody])
                    <div class="es-air-card p-7" data-reveal="panel">
                        <div class="es-air-accent es-air-num mb-3 text-3xl font-black">{{ $stepNum }}</div>
                        <h3 class="es-air-ink mb-2 text-lg font-bold">{{ $stepTitle }}</h3>
                        <p class="es-air-muted text-sm leading-relaxed">{{ $stepBody }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. Key features                                             -->
    <!-- ============================================================ -->
    <section class="scroll-mt-24 py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-air-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Online Events" description="One link field, so any meeting or streaming platform works" :url="marketing_url('/features/online-events')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="A weekly series as one event, with skipped dates and an end" :url="marketing_url('/features/recurring-events')" icon-color="teal">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Analytics" description="Track page views, devices, and traffic sources" :url="marketing_url('/features/analytics')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Write to the people who follow your schedule, with open rates" :url="marketing_url('/features/newsletters')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-air-link inline-flex items-center font-medium hover:underline">
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
    <section class="es-air-alt py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-air-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4" data-reveal-group="70">
                @foreach ([['/for-virtual-conferences', 'Virtual Conferences'], ['/for-online-classes', 'Online Classes'], ['/for-live-qa-sessions', 'Live Q&A Sessions'], ['/for-workshop-instructors', 'Workshop Instructors']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" class="es-air-hover es-air-card group flex flex-col p-5 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-air-hover-title es-air-ink mb-3 text-sm font-semibold transition-colors">For {{ $relName }}</span>
                        <span class="es-air-hover-arrow es-air-muted mt-auto inline-flex items-center gap-1 text-xs font-medium transition-colors">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
            <div class="mt-8 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-air-link inline-flex items-center font-medium hover:underline">
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

    <section id="faq" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="es-air-chan mb-6" data-reveal aria-hidden="true"><span>CH 09</span></div>
                <h2 class="es-balance es-air-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-air-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    What webinar hosts ask before they move a series across.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-air-hover es-air-card group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-air-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-air-accent es-air-num flex-none text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-air-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-air-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-air-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 13. Finale: the tally comes on                               -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-air-rack noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 15%, rgba(94, 234, 212, 0.16), rgba(94, 234, 212, 0) 60%); opacity: 0.5;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>
                <div class="relative z-10">
                    <div class="es-air-onair mb-5" aria-hidden="true">
                        <span class="es-air-tally"></span>
                        <span class="es-air-onair-txt">On air</span>
                    </div>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Publish the session. <span class="es-air-lit">Keep the room.</span>
                    </h2>
                    <p class="es-air-rack-note mx-auto mb-10 max-w-2xl text-lg">
                        Unlimited webinars, the running order, recurring series and free registration are free forever, and so are the first twenty-five paid tickets a month. {{ plan_price($proMonthly) }} a month takes the ceiling off, and nothing is ever taken off the top.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-webinars" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-400 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="es-air-rack-note shrink-0 select-none font-mono text-sm sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="es-air-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Get started free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="es-air-rack-note mt-6 text-sm">No credit card required</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Desktop dot nav -->
    <nav class="es-dotnav fixed top-1/2 z-40 hidden -translate-y-1/2 lg:block ltr:right-5 rtl:left-5" aria-label="Page sections">
        <ul class="glass flex flex-col items-center gap-1.5 rounded-full px-2 py-3">
            @foreach ($dotSections as [$sectionId, $sectionLabel])
                <li class="relative">
                    <a href="#{{ $sectionId }}" class="es-dot group block rounded-full" aria-label="{{ $sectionLabel }}">
                        <span class="es-dot-pip block h-2 w-2 rounded-full bg-gray-400/60 dark:bg-white/30"></span>
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-medium text-gray-700 opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3 dark:border-white/10 dark:bg-gray-900 dark:text-gray-300">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
