<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Watch Parties | Movie Nights</x-slot>
    <x-slot name="description">Free, open-source watch party scheduling software with registration, ticketing, and email notifications. Works with any streaming platform. Zero platform fees.</x-slot>
    <x-slot name="breadcrumbTitle">For Watch Parties</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Watch Parties",
        "description": "Free, open-source watch party scheduling software. Publish the running order, take free registrations against a per-date cap, and hand every registrant a confirmation page carrying the join link. Zero platform fees.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Watch Party Hosts"
        }
    }
    </script>
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Watch Parties",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Watch Party Scheduling Software",
        "operatingSystem": "Web",
        "description": "Schedule watch parties and screenings: one join link per event, a published running order, free registration with a cap counted per date, and a confirmation page that carries the link.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "One join link per event, for whatever platform you are streaming through",
            "Free registration with a capacity cap counted for each date on its own",
            "A confirmation page carrying the join link, emailed to every registrant",
            "A published running order with times, for doors, feature and discussion",
            "Weekly and monthly screening series as one recurring event with date exceptions",
            "Add to calendar links stamped in UTC, so a viewer's own calendar shows their local time",
            "Newsletters to your followers, to everyone who registered for one screening, or to a sub-schedule",
            "Named ticket types with their own prices, quantities and sales windows for paid screenings",
            "Zero platform fees on ticket sales through your own Stripe account",
            "Two-way Google, Outlook and CalDAV calendar sync",
            "Embeddable calendar for the site you already have",
            "Open source, with a selfhosted option"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "watch party platform, schedule watch parties, virtual watch party, online watch party hosting, group streaming events, watch party ticketing, movie night scheduling, free watch party app",
        "screenshot": "{{ asset('images/social/for-watch-parties.png') }}",
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
        "name": "How to host a watch party with Event Schedule",
        "description": "Three steps to run a screening night, not just to publish a stream link.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Open the doors",
                "text": "Create the event, paste your join link into the one Event URL field, turn on free registration and set the cap for the room you can actually handle."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Post the running order",
                "text": "Add the parts of the night with their times: doors and chat, the introduction, the feature, the discussion afterwards. They publish on the event page with it."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Roll it",
                "text": "Every registrant gets a confirmation email linking to their own page, and the join link is on it. Afterwards, collect photos and comments from the people who were there."
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
           For-watch-parties "The Screening" styles.

           THE CONCEPT: a screening is a NIGHT, not a link. The physical
           object is the projection booth's running order: a sheet with
           the shape of the evening on it, a house count for the door,
           and one socket that the projector plugs into. Every device on
           this page is one of those three things, and each maps onto a
           real primitive:

             the running order -> EventPart (name, start_time, end_time),
                                  free, published on the event page
             the house count   -> rsvp_limit against rsvp_sold, which is
                                  a JSON map KEYED BY DATE, so the cap is
                                  counted per occurrence
             the one socket    -> events.event_url. ONE generic field.
                                  There is no platform integration and no
                                  live viewer count, and the page says so
                                  rather than implying otherwise.

           THE SIGNATURE DEVICE IS PROPORTIONAL, NOT DECORATIVE. The
           running order is drawn with each segment's HEIGHT proportional
           to its minutes, because the whole argument is that the feature
           is not the evening: doors, intro and discussion are 55 minutes
           of it. A bulleted list cannot make that point; a proportional
           strip makes it without a word.

           NO SEATS ANYWHERE. An earlier version of this page drew seat
           chips (A1, B4, C2) on the recurring rows. There is no seat
           map, no assigned seating and no section inventory in this
           product, so a seat grid teaches a model that does not exist.
           The door is a COUNT, and it is drawn as a count.

           COLOUR: the page keeps its inherited red-orange family, but
           narrowed to a single tungsten lamp accent (#9c3d10 light,
           #fdba74 dark) instead of a three-stop red-to-amber gradient.
           A gradient heading is scored stop by stop and a bright amber
           stop on a light ground fails at 2.4, so there is no gradient
           text on this page at all: one solid accent, plus a tapered
           CSS underline for emphasis.

           NEVER text-gray-500 on these grounds. Use .es-scr-muted
           (#4e4842, 7.98 on the light ground) and .es-scr-screen-muted
           inside the lit screen panel.

           FIXED PHYSICAL OBJECTS, pinned identical in both colour modes
           and verified with the verifier's --bands flag:
             .es-scr-booth  - the projection booth. Always dark.
             .es-scr-screen - the lit screen inside the booth. Always
                              bright. Its own ink classes, because
                              gray-400 on white measures 2.43.
           Shared classes carry their own .dark rules in marketing.css,
           so .grid-overlay / .animate-shimmer / .es-claim:focus-within
           are re-pinned inside the booth AFTER the base rules.

           BLADE: no @supports probes in this block. A hex "#" inside a
           parenthesised at-rule condition breaks Blade compilation of
           every later parenthesised directive.
           ============================================================== */

        /* --- Ground and ink --- */
        .es-scr-page { background-color: #f2f1ee; color: #161311; }
        .dark .es-scr-page { background-color: #0d0c0b; color: #f0ede8; }
        .es-scr-ink { color: #161311; }
        .dark .es-scr-ink { color: #f0ede8; }
        .es-scr-muted { color: #4e4842; }
        .dark .es-scr-muted { color: #a49c93; }
        .es-scr-accent { color: #9c3d10; }
        .dark .es-scr-accent { color: #fdba74; }
        /* Always-lit lamp accent, for the fixed-dark booth in both modes. */
        .es-scr-lit { color: #fdba74; }

        .es-scr-soft { background-color: #eceae6; }
        .dark .es-scr-soft { background-color: #121110; }

        /* Ink for text sitting DIRECTLY on the booth, not on a card inside it.
           Pinned, because the booth is pinned. These exist as page CSS rather
           than as text-[#f0ede8] utilities on purpose: an arbitrary Tailwind
           value that no other page already uses is not in the prebuilt bundle,
           so the class renders as nothing and the text falls back to the page
           ink - which is how a 1.02 contrast ratio gets shipped. */
        .es-scr-booth-ink { color: #f0ede8; }
        .es-scr-booth-body { color: #c9c1b7; }
        .es-scr-booth-muted { color: #a49c93; }

        /* Two type sizes the shared bundle does not carry. */
        .es-scr-xxs { font-size: 0.66rem; }
        .es-scr-screen-eyebrow {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.24em;
            text-transform: uppercase;
        }

        /* Dot-nav tooltip surface. */
        .es-scr-tip {
            border: 1px solid rgba(22, 19, 17, 0.14);
            border-radius: 9999px;
            background-color: #ffffff;
            color: #4e4842;
        }
        .dark .es-scr-tip {
            border-color: rgba(240, 237, 232, 0.14);
            background-color: #191715;
            color: #b3aaa0;
        }

        .es-scr-hair { border-color: rgba(22, 19, 17, 0.1); }
        .dark .es-scr-hair { border-color: rgba(240, 237, 232, 0.1); }

        /* --- Typographic system: tabular figures for every time and count,
               wide-tracked micro labels for the booth's own vocabulary. --- */
        .es-scr-fig {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            letter-spacing: 0.01em;
        }
        .es-scr-label {
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.28em;
            text-transform: uppercase;
            color: #4e4842;
        }
        .dark .es-scr-label { color: #a49c93; }

        /* A tapered underline for one word of a heading. Drawn with a
           gradient, not an SVG, so it inherits nothing and draws nothing. */
        .es-scr-ul {
            background-image: linear-gradient(90deg, rgba(156, 61, 16, 0), rgba(156, 61, 16, 0.85) 18%, rgba(156, 61, 16, 0.85) 82%, rgba(156, 61, 16, 0));
            background-repeat: no-repeat;
            background-position: 0 100%;
            background-size: 100% 0.14em;
            padding-bottom: 0.1em;
        }
        .dark .es-scr-ul {
            background-image: linear-gradient(90deg, rgba(253, 186, 116, 0), rgba(253, 186, 116, 0.9) 18%, rgba(253, 186, 116, 0.9) 82%, rgba(253, 186, 116, 0));
        }

        /* --- Cards --- */
        .es-scr-card {
            border: 1px solid rgba(22, 19, 17, 0.12);
            border-radius: 1rem;
            background-color: #ffffff;
        }
        .dark .es-scr-card { border-color: rgba(240, 237, 232, 0.12); background-color: #191715; }

        .es-scr-inset {
            border: 1px solid rgba(22, 19, 17, 0.08);
            border-radius: 0.6rem;
            background-color: #e8e6e2;
        }
        .dark .es-scr-inset { border-color: rgba(240, 237, 232, 0.09); background-color: #221f1c; }

        /* --- The reel mark. Sections are reels, numbered in the booth's
               own tabular mono, with a lamp-coloured sprocket edge. --- */
        .es-scr-reel {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.3rem 0.8rem;
            border-radius: 0.3rem;
            border: 1px solid rgba(22, 19, 17, 0.18);
            background-color: #ffffff;
            color: #161311;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-weight: 800;
            font-size: 0.78rem;
            letter-spacing: 0.1em;
        }
        .dark .es-scr-reel { border-color: rgba(240, 237, 232, 0.2); background-color: #191715; color: #f0ede8; }
        .es-scr-reel::before {
            content: "";
            width: 2px;
            align-self: stretch;
            border-radius: 1px;
            background-color: #9c3d10;
        }
        .dark .es-scr-reel::before { background-color: #fdba74; }

        /* --- THE RUNNING ORDER. Height is proportional to minutes, so the
               feature dominates and the 55 minutes around it are visible.
               Each segment threads in from the top like film through a gate;
               the finished state is the resting state. --- */
        .es-scr-order { display: flex; flex-direction: column; gap: 3px; }
        .es-scr-seg {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0 0.7rem;
            border-radius: 0.35rem;
            overflow: hidden;
            height: calc(var(--min, 20) * 0.85px + 1.15rem);
            background-color: #e8e6e2;
            transform-origin: top;
            transition: transform 0.7s cubic-bezier(0.22, 1, 0.36, 1);
            transition-delay: calc(var(--i, 0) * 0.1s);
        }
        .dark .es-scr-seg { background-color: #221f1c; }
        .es-scr-seg-main { background-color: rgba(156, 61, 16, 0.16); box-shadow: inset 2px 0 0 #9c3d10; }
        .dark .es-scr-seg-main { background-color: rgba(253, 186, 116, 0.14); box-shadow: inset 2px 0 0 #fdba74; }
        html.es-anim [data-reveal]:not(.is-revealed) .es-scr-seg { transform: scaleY(0.05); }
        .es-scr-seg-time { flex: none; width: 4.4rem; font-size: 0.7rem; font-weight: 700; color: #4e4842; }
        .dark .es-scr-seg-time { color: #a49c93; }
        .es-scr-seg-name { flex: 1 1 auto; min-width: 0; font-size: 0.8rem; font-weight: 600; color: #161311; }
        .dark .es-scr-seg-name { color: #f0ede8; }
        .es-scr-seg-len { flex: none; font-size: 0.66rem; font-weight: 700; color: #4e4842; }
        .dark .es-scr-seg-len { color: #a49c93; }

        /* --- THE HOUSE COUNT. A fill meter per date, because rsvp_sold is a
               map keyed by date and the cap is counted per occurrence. --- */
        .es-scr-meter {
            position: relative;
            height: 0.5rem;
            border-radius: 9999px;
            overflow: hidden;
            background-color: rgba(22, 19, 17, 0.11);
        }
        .dark .es-scr-meter { background-color: rgba(240, 237, 232, 0.13); }
        .es-scr-meter-fill {
            position: absolute;
            top: 0;
            bottom: 0;
            inset-inline-start: 0;
            border-radius: 9999px;
            background-color: #9c3d10;
            transform-origin: left;
            transition: transform 0.9s cubic-bezier(0.22, 1, 0.36, 1);
            transition-delay: calc(var(--i, 0) * 0.09s);
        }
        .dark .es-scr-meter-fill { background-color: #fdba74; }
        html.es-anim [data-reveal]:not(.is-revealed) .es-scr-meter-fill { transform: scaleX(0); }
        .es-scr-full { background-color: rgba(156, 61, 16, 0.12); }
        .dark .es-scr-full { background-color: rgba(253, 186, 116, 0.1); }

        /* --- THE BOOTH. Fixed dark in both colour modes: it is one room, and
               a room does not change colour when you flip a theme switch. --- */
        .es-scr-booth {
            background-color: #0a0908;
            background-image: radial-gradient(118% 92% at 50% -8%, #241a10 0%, #120e0a 46%, #070605 100%);
            box-shadow: inset 0 0 100px rgba(0, 0, 0, 0.6), inset 0 1px 0 rgba(253, 186, 116, 0.07);
        }
        .es-scr-booth .es-scr-card { border-color: rgba(240, 237, 232, 0.13); background-color: #171512; }
        .es-scr-booth .es-scr-inset { border-color: rgba(240, 237, 232, 0.09); background-color: #221f1c; }
        .es-scr-booth .es-scr-label { color: #fdba74; }
        .es-scr-booth .es-scr-reel { border-color: rgba(240, 237, 232, 0.2); background-color: #171512; color: #f0ede8; }
        .es-scr-booth .es-scr-reel::before { background-color: #fdba74; }
        /* Shared classes that flip with the colour mode. Re-pinned here, AFTER
           the base rules, or the booth quietly stops being one object. */
        .es-scr-booth .grid-overlay {
            background-image:
                linear-gradient(rgba(240, 237, 232, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(240, 237, 232, 0.05) 1px, transparent 1px);
        }
        .es-scr-booth .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-scr-booth .es-claim:focus-within {
            border-color: rgba(253, 186, 116, 0.75);
            box-shadow: 0 0 0 4px rgba(253, 186, 116, 0.22);
        }

        /* --- THE LAMP. A tungsten bloom and a soft shaft off the top edge of
               the booth. Gradients and blur only, no drawn object. --- */
        .es-scr-lamp { position: absolute; inset: 0; overflow: hidden; pointer-events: none; }
        .es-scr-lamp::before {
            content: "";
            position: absolute;
            top: -16%;
            inset-inline-start: 50%;
            width: 72%;
            height: 74%;
            transform: translateX(-50%);
            background-image: radial-gradient(ellipse 58% 100% at 50% 0%, rgba(253, 186, 116, 0.2), rgba(253, 186, 116, 0) 70%);
            filter: blur(26px);
            animation: es-scr-breathe 9s ease-in-out infinite;
        }
        .es-scr-lamp::after {
            content: "";
            position: absolute;
            top: -4%;
            inset-inline-start: 50%;
            width: 48%;
            height: 90%;
            transform: translateX(-50%);
            clip-path: polygon(46% 0%, 54% 0%, 87% 100%, 13% 100%);
            background-image: linear-gradient(to bottom, rgba(253, 186, 116, 0.14), rgba(253, 186, 116, 0.03) 52%, rgba(253, 186, 116, 0));
            filter: blur(9px);
            animation: es-scr-flicker 7s ease-in-out infinite;
        }
        @keyframes es-scr-breathe { 0%, 100% { opacity: 0.82; } 50% { opacity: 1; } }
        @keyframes es-scr-flicker { 0%, 100% { opacity: 0.9; } 47% { opacity: 1; } 53% { opacity: 0.74; } }

        /* Mode-aware twin of the shaft, for the hero, which is not a fixed band. */
        .es-scr-beam {
            position: absolute;
            top: -6%;
            inset-inline-start: 50%;
            width: 52%;
            height: 78%;
            transform: translateX(-50%);
            clip-path: polygon(46% 0%, 54% 0%, 88% 100%, 12% 100%);
            background-image: linear-gradient(to bottom, rgba(156, 61, 16, 0.1), rgba(156, 61, 16, 0.02) 50%, rgba(156, 61, 16, 0));
            filter: blur(12px);
            animation: es-scr-flicker 7s ease-in-out infinite;
        }
        .dark .es-scr-beam {
            background-image: linear-gradient(to bottom, rgba(253, 186, 116, 0.14), rgba(253, 186, 116, 0.03) 50%, rgba(253, 186, 116, 0));
        }

        /* --- THE LIT SCREEN. A bright panel inside the booth, pinned in both
               modes, with its own ink: gray-400 on white measures 2.43. --- */
        .es-scr-screen {
            border: 1px solid rgba(22, 19, 17, 0.14);
            border-radius: 0.9rem;
            background-color: #f4f3f0;
            box-shadow: 0 0 0 1px rgba(253, 186, 116, 0.16), 0 26px 60px -26px rgba(0, 0, 0, 0.85);
        }
        .es-scr-screen-ink { color: #161311; }
        .es-scr-screen-muted { color: #4e4842; }
        .es-scr-screen-accent { color: #9c3d10; }
        .es-scr-screen-inset {
            border: 1px solid rgba(22, 19, 17, 0.1);
            border-radius: 0.5rem;
            background-color: #e6e4df;
        }
        .es-scr-screen-hair { border-color: rgba(22, 19, 17, 0.12); }

        /* --- Leader frames for the three steps. A leader frame is the same
               piece of film in both modes, so it is pinned too. --- */
        .es-scr-leader {
            position: relative;
            overflow: hidden;
            border-radius: 9999px;
            border: 1.5px solid rgba(253, 186, 116, 0.5);
            background-color: #0a0908;
            background-image: radial-gradient(circle at 50% 44%, #241a10 0%, #0a0908 78%);
            box-shadow: 0 0 0 4px rgba(156, 61, 16, 0.1), inset 0 0 14px rgba(0, 0, 0, 0.7);
        }
        .es-scr-leader::before {
            content: "";
            position: absolute;
            inset: -2px;
            z-index: 1;
            background-image: conic-gradient(from 0deg, rgba(253, 186, 116, 0.6) 0deg, rgba(253, 186, 116, 0.08) 26deg, transparent 72deg, transparent 360deg);
            animation: es-scr-sweep 3.6s linear infinite;
        }
        .es-scr-leader::after {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 2;
            background-image:
                linear-gradient(to right, transparent calc(50% - 0.75px), rgba(240, 237, 232, 0.4) calc(50% - 0.75px), rgba(240, 237, 232, 0.4) calc(50% + 0.75px), transparent calc(50% + 0.75px)),
                linear-gradient(to bottom, transparent calc(50% - 0.75px), rgba(240, 237, 232, 0.4) calc(50% - 0.75px), rgba(240, 237, 232, 0.4) calc(50% + 0.75px), transparent calc(50% + 0.75px));
        }
        .es-scr-leader-num {
            position: relative;
            z-index: 3;
            color: #f0ede8;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.85);
        }
        @keyframes es-scr-sweep { to { transform: rotate(360deg); } }

        /* --- Plan tags --- */
        .es-scr-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.25rem;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            border: 1px solid rgba(156, 61, 16, 0.42);
            color: #9c3d10;
        }
        .dark .es-scr-plan { border-color: rgba(253, 186, 116, 0.42); color: #fdba74; }
        .es-scr-plan-paid { border-color: rgba(22, 19, 17, 0.35); color: #161311; }
        .dark .es-scr-plan-paid { border-color: rgba(240, 237, 232, 0.38); color: #f0ede8; }

        /* --- Chips --- */
        .es-scr-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            border: 1px solid rgba(22, 19, 17, 0.16);
            background-color: rgba(255, 255, 255, 0.72);
            color: #4e4842;
            font-size: 0.74rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .dark .es-scr-chip {
            border-color: rgba(240, 237, 232, 0.16);
            background-color: rgba(240, 237, 232, 0.05);
            color: #b3aaa0;
        }

        /* --- Links and buttons --- */
        .es-scr-link { color: #9c3d10; }
        .es-scr-link:hover { color: #161311; }
        .dark .es-scr-link { color: #fdba74; }
        .dark .es-scr-link:hover { color: #f0ede8; }

        .es-scr-btn {
            background-color: #9c3d10;
            color: #ffffff;
            box-shadow: 0 18px 34px -16px rgba(156, 61, 16, 0.55);
        }
        .es-scr-btn:hover { background-color: #8f380e; box-shadow: 0 22px 42px -16px rgba(156, 61, 16, 0.65); }
        .dark .es-scr-btn { background-color: #fdba74; color: #0d0c0b; }
        .dark .es-scr-btn:hover { background-color: #fecfa4; }
        .es-scr-booth .es-scr-btn { background-color: #fdba74; color: #0d0c0b; }
        .es-scr-booth .es-scr-btn:hover { background-color: #fecfa4; }

        /* --- Hover states on cards that are links, and on FAQ rows --- */
        .es-scr-hover:hover { border-color: rgba(156, 61, 16, 0.45); }
        .dark .es-scr-hover:hover { border-color: rgba(253, 186, 116, 0.45); }
        .es-scr-hover:hover .es-scr-hover-title,
        .es-scr-hover:hover .es-scr-hover-arrow { color: #9c3d10; }
        .dark .es-scr-hover:hover .es-scr-hover-title,
        .dark .es-scr-hover:hover .es-scr-hover-arrow { color: #fdba74; }

        /* --- Shared-system recolors (brand blue by default) --- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(156, 61, 16, 0.12), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(253, 186, 116, 0.1), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(156, 61, 16, 0.65); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(253, 186, 116, 0.65); }
        .es-dot.is-active .es-dot-pip { background: #9c3d10; }
        .dark .es-dot.is-active .es-dot-pip { background: #fdba74; }

        /* --- Focus rings. No border-radius here: setting it would change the
               element's own shape on focus. Outlines already follow it. --- */
        #es-scr-page a:focus-visible,
        #es-scr-page summary:focus-visible,
        #es-scr-page button:focus-visible {
            outline: 2px solid #9c3d10;
            outline-offset: 3px;
        }
        .dark #es-scr-page a:focus-visible,
        .dark #es-scr-page summary:focus-visible,
        .dark #es-scr-page button:focus-visible {
            outline-color: #fdba74;
        }
        .es-scr-booth a:focus-visible,
        .es-scr-booth summary:focus-visible,
        .es-scr-booth button:focus-visible {
            outline-color: #fdba74 !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-scr-lamp::before,
            .es-scr-lamp::after,
            .es-scr-beam,
            .es-scr-leader::before {
                animation: none !important;
            }
            .es-scr-seg,
            .es-scr-meter-fill {
                transform: none !important;
                transition: none !important;
            }
        }
    </style>

    @php
        // One screening night. Minutes drive the proportional height, which is
        // the whole point: the feature is 112 of 167 minutes, and the 55
        // minutes around it are the part a stream link cannot hold.
        $order = [
            ['7:45 PM', 'Doors and chat', 15, false],
            ['8:00 PM', 'Introduction', 10, false],
            ['8:10 PM', 'Feature', 112, true],
            ['10:02 PM', 'Discussion', 30, false],
        ];

        // The same weekly event, four occurrences. rsvp_sold is a JSON map keyed
        // by date, so each Friday carries its own count against the one cap.
        $house = [
            ['Fri Nov 6', 118, 120],
            ['Fri Nov 13', 86, 120],
            ['Fri Nov 20', 41, 120],
            ['Fri Nov 27', 9, 120],
        ];

        $faqs = [
            [
                'q' => 'What streaming platforms work with watch parties?',
                'a' => 'Any of them, because Event Schedule does not integrate with any of them. An online event has one field, Event URL, and it takes whatever link you have: YouTube, Twitch, Discord, a Zoom or Teams room, or your own player. There is no platform picker and nothing to connect, so nothing breaks when you change platforms next month.',
            ],
            [
                'q' => 'How do I know how many people are coming?',
                'a' => 'Turn on free registration and set a cap. Registering takes a name and an email, one registration per email per date, and the remaining count is shown on the form as people take places. On a weekly series the cap is counted for each date on its own, so a full Friday does not close the following one. This is a registration list, not a live viewer count: Event Schedule never watches your stream.',
            ],
            [
                'q' => 'Can I charge for watch party access?',
                'a' => 'Yes, on the Pro plan at $5 a month. Create named ticket types with their own prices, quantities and sales windows, sell through your own Stripe account, and keep everything: Event Schedule takes zero platform fees on ticket sales at every plan level. Stripe charges its standard processing fee (2.9% + $0.30).',
            ],
            [
                'q' => 'Can I schedule recurring watch parties?',
                'a' => 'Yes, and it is one event rather than fifty. Pick the days of the week, add date exceptions for the weeks you are skipping, and end the series on a date or after a set number of screenings. Registration caps and ticket inventory are counted per occurrence. The whole series syncs two ways with Google Calendar, Outlook and CalDAV.',
            ],
            [
                'q' => 'Do my followers get emailed when I add a screening?',
                'a' => 'Not automatically, and no page here will tell you otherwise. Nothing goes out on its own: you write the newsletter and you send it. What you get is the list and the targeting, free, so you can send to everyone who follows the schedule, to everyone who registered for one particular screening, or to one sub-schedule. The free plan covers 10 recipients a month, Pro 100 and Enterprise 1,000.',
            ],
            [
                'q' => 'Is Event Schedule free for hosting watch parties?',
                'a' => 'Yes. Unlimited events and screening series, one join link per event, free registration with per-date caps, the published running order, built-in analytics, the embeddable calendar and two-way calendar sync are all free forever. Ticketing is on the Pro plan at $5 a month, and there are zero platform fees on ticket sales at any level. You can also selfhost Event Schedule on your own server, where every Enterprise feature is included.',
            ],
        ];

        $dotSections = [
            ['top', 'The screening'],
            ['why', 'Not a link'],
            ['order', 'The running order'],
            ['house', 'The house count'],
            ['link', 'The one socket'],
            ['reach', 'The call sheet'],
            ['tickets', 'When you charge'],
            ['rest', 'Everything else'],
            ['who', 'Perfect for'],
            ['faq', 'Questions'],
            ['claim', 'Roll it'],
        ];
    @endphp

    <div id="es-scr-page" class="es-scr-page">

    <!-- ============================================================ -->
    <!-- R1. Hero: a screening is a night                             -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(156, 61, 16, 0.2), rgba(156, 61, 16, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(253, 186, 116, 0.16), rgba(253, 186, 116, 0) 65%);"></div>
            <div class="es-scr-beam"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="h-5 w-5 es-scr-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <span class="es-scr-muted text-sm font-medium tracking-wide">For watch party and screening hosts</span>
                    </div>

                    <h1 class="es-balance es-scr-ink mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">The link takes ten seconds.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">The <span class="es-scr-accent es-scr-ul">night</span> is the work.</span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-scr-muted mb-10 max-w-xl text-lg sm:text-xl">
                        A screening has a start time, a shape, a door that only fits so many, and a list of who said they were coming. Event Schedule holds all four, free, and takes nothing at the door.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="#order" class="glass group inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            See the running order
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="es-scr-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Create your screening schedule
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- The running order, drawn to scale. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-scr-card p-6 sm:p-7">
                        <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                            <h2 class="es-scr-ink text-lg font-bold">Friday Movie Night</h2>
                            <span class="es-scr-muted es-scr-fig text-xs">Fri Nov 13</span>
                        </div>
                        <p class="es-scr-muted mb-5 text-sm">Doors 7:45 PM &middot; out by 10:32 &middot; drawn to scale</p>

                        <div class="es-scr-order" aria-hidden="true">
                            @foreach ($order as $oi => [$oTime, $oName, $oMin, $oMain])
                                <div class="es-scr-seg @if ($oMain) es-scr-seg-main @endif" style="--min: {{ $oMin }}; --i: {{ $oi }};">
                                    <span class="es-scr-seg-time es-scr-fig">{{ $oTime }}</span>
                                    <span class="es-scr-seg-name">{{ $oName }}</span>
                                    <span class="es-scr-seg-len es-scr-fig">{{ $oMin }}m</span>
                                </div>
                            @endforeach
                        </div>

                        <p class="es-scr-muted es-scr-hair mt-5 border-t pt-4 text-xs">
                            The feature is 112 of 167 minutes. The other 55 are doors, introduction and the conversation afterwards, and they are the reason people come back. Publish them and nobody has to ask when the film actually starts.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Screening-type marquee -->
            <div class="es-fade-up es-d-4 mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach (['Premiere Screenings', 'Movie Nights', 'Sports Watch Parties', 'Series Finales', 'Documentary Screenings', 'Gaming Events', 'Reaction Streams', 'Marathon Nights'] as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-scr-chip">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- R2. Not a link (the booth: fixed dark in both modes)         -->
    <!-- ============================================================ -->
    <section id="why" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-scr-booth noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-scr-lamp"></div>
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-scr-reel mb-6" data-reveal aria-hidden="true"><span>R2</span></div>
                    <p class="es-scr-label mb-4" data-reveal style="--reveal-delay: 0.05s;">The unit</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Every watch party tool sells you the stream. <span class="es-scr-lit">None of them run the night.</span>
                    </h2>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    <div class="es-scr-card p-6" data-reveal="panel">
                        <p class="es-scr-label mb-3">The socket</p>
                        <h3 class="mb-2 text-lg font-bold es-scr-booth-ink">
                            <span class="es-scr-fig" data-count-to="1">1</span> link
                        </h3>
                        <p class="text-sm es-scr-booth-muted">An online event has one Event URL field and it takes any link. No platform to connect, so nothing to reconnect when you switch.</p>
                    </div>
                    <div class="es-scr-card p-6" data-reveal="panel">
                        <p class="es-scr-label mb-3">The door</p>
                        <h3 class="mb-2 text-lg font-bold es-scr-booth-ink">
                            <span class="es-scr-fig" data-count-to="120">120</span> places
                        </h3>
                        <p class="text-sm es-scr-booth-muted">Free registration with a cap, counted for each date on its own. A full Friday does not close the following one.</p>
                    </div>
                    <div class="es-scr-card p-6" data-reveal="panel">
                        <p class="es-scr-label mb-3">The take</p>
                        <h3 class="mb-2 text-lg font-bold es-scr-booth-ink">
                            <span class="es-scr-fig">$0</span> platform fees
                        </h3>
                        <p class="text-sm es-scr-booth-muted">When you charge, you charge through your own Stripe account and keep the lot. That is true on every plan, including free.</p>
                    </div>
                </div>

                <p class="mt-10 text-center es-scr-booth-body" data-reveal>
                    The night is the unit. Everything below hangs off it.
                    <a href="#order" class="es-scr-lit inline-flex items-center gap-1 font-semibold transition-all hover:gap-2">
                        Start with its shape
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- R3. The running order                                        -->
    <!-- ============================================================ -->
    <section id="order" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-scr-reel mb-6" data-reveal aria-hidden="true"><span>R3</span></div>
                <p class="es-scr-label mb-4" data-reveal style="--reveal-delay: 0.05s;">The running order</p>
                <h2 class="es-balance es-scr-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    A screening has a <span class="es-scr-accent">shape.</span>
                </h2>
                <p class="es-scr-muted mx-auto mt-5 max-w-2xl text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Add the parts of the night to the event, each with its own start and end time, and they publish on the event page underneath it. Free on every plan.
                </p>
            </div>

            <div class="grid items-start gap-8 lg:grid-cols-2">
                <div class="space-y-4" data-reveal-group="90">
                    <div class="es-scr-card p-7" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="es-scr-ink text-lg font-bold">Doors before the feature</h3>
                            <span class="es-scr-plan">Free</span>
                        </div>
                        <p class="es-scr-muted text-sm leading-relaxed">Fifteen minutes of people arriving is not dead time, it is the reason a watch party is not just watching. Put it on the sheet so people know to turn up for it.</p>
                    </div>
                    <div class="es-scr-card p-7" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="es-scr-ink text-lg font-bold">Discussion after it</h3>
                            <span class="es-scr-plan">Free</span>
                        </div>
                        <p class="es-scr-muted text-sm leading-relaxed">The half hour afterwards is the part regulars come back for. Give it a time and it stops being an accident that some people miss.</p>
                    </div>
                    <div class="es-scr-card p-7" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="es-scr-ink text-lg font-bold">A long sheet, scanned</h3>
                            <span class="es-scr-plan es-scr-plan-paid">Enterprise</span>
                        </div>
                        <p class="es-scr-muted text-sm leading-relaxed">For a marathon or a festival day with a dozen slots, paste or upload the agenda and have the parts read off it instead of typing each one.</p>
                    </div>
                </div>

                <div data-reveal="panel">
                    <div class="es-scr-card p-6 sm:p-8">
                        <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                            <p class="es-scr-label">Marathon night</p>
                            <span class="es-scr-muted es-scr-fig text-xs">Sat Nov 21 &middot; 6h 05m</span>
                        </div>

                        <div class="es-scr-order" aria-hidden="true">
                            @php
                                $marathon = [
                                    ['5:30 PM', 'Doors and snacks', 30, false],
                                    ['6:00 PM', 'Part one', 95, true],
                                    ['7:35 PM', 'Interval', 20, false],
                                    ['7:55 PM', 'Part two', 108, true],
                                    ['9:43 PM', 'Interval', 15, false],
                                    ['9:58 PM', 'Part three', 97, true],
                                ];
                            @endphp
                            @foreach ($marathon as $mi => [$mTime, $mName, $mMin, $mMain])
                                <div class="es-scr-seg @if ($mMain) es-scr-seg-main @endif" style="--min: {{ $mMin }}; --i: {{ $mi }};">
                                    <span class="es-scr-seg-time es-scr-fig">{{ $mTime }}</span>
                                    <span class="es-scr-seg-name">{{ $mName }}</span>
                                    <span class="es-scr-seg-len es-scr-fig">{{ $mMin }}m</span>
                                </div>
                            @endforeach
                        </div>

                        <p class="es-scr-muted es-scr-hair mt-5 border-t pt-4 text-xs">
                            Drawn to scale again, because the intervals are the thing people plan their evening around. Six hours is a commitment, and a sheet that shows where the breaks fall is the difference between a full room at 10 PM and an empty one.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- R4. The house count: a real record, per date                 -->
    <!-- ============================================================ -->
    <section id="house" class="es-scr-soft es-scr-hair scroll-mt-24 border-y py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-scr-reel mb-6" data-reveal aria-hidden="true"><span>R4</span></div>
                <p class="es-scr-label mb-4" data-reveal style="--reveal-delay: 0.05s;">The house count</p>
                <h2 class="es-balance es-scr-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    One event. <span class="es-scr-accent">Four different doors.</span>
                </h2>
                <p class="es-scr-muted mx-auto mt-5 max-w-2xl text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    A weekly movie night is one recurring event, and the cap you set is counted for each Friday on its own. So this is what the door actually looks like.
                </p>
            </div>

            <div class="es-scr-card p-6 sm:p-8" data-reveal="panel">
                <table class="w-full border-collapse text-left">
                    <caption class="sr-only">Friday Movie Night: registrations against a 120 place cap, for each of the next four dates</caption>
                    <thead>
                        <tr class="es-scr-label">
                            <th scope="col" class="pb-3 font-bold">Date</th>
                            <th scope="col" class="pb-3 text-end font-bold">Registered</th>
                            <th scope="col" class="hidden pb-3 text-end font-bold sm:table-cell">Cap</th>
                            <th scope="col" class="pb-3 text-end font-bold">Left</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($house as $hi => [$hDate, $hSold, $hCap])
                            @php
                                $hLeft = $hCap - $hSold;
                                $hPct = round($hSold / $hCap * 100);
                            @endphp
                            <tr class="es-scr-hair border-t">
                                <th scope="row" class="es-scr-ink es-scr-fig py-3 pe-3 align-middle text-sm font-bold">{{ $hDate }}</th>
                                <td class="es-scr-ink es-scr-fig py-3 pe-3 text-end align-middle text-sm">{{ $hSold }}</td>
                                <td class="es-scr-muted es-scr-fig hidden py-3 pe-3 text-end align-middle text-sm sm:table-cell">{{ $hCap }}</td>
                                <td class="es-scr-fig py-3 text-end align-middle text-sm font-bold @if ($hLeft <= 5) es-scr-accent @else es-scr-ink @endif">{{ $hLeft }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="pb-3">
                                    <div class="es-scr-meter" aria-hidden="true">
                                        <div class="es-scr-meter-fill" style="width: {{ $hPct }}%; --i: {{ $hi }};"></div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p class="es-scr-muted mt-4 text-xs">
                    Same event, same 120 place cap, four separate counts. The remaining number is shown on the registration form as places go, and one email address can only take a place once per date.
                </p>
            </div>

            <div class="mt-8 grid gap-4 md:grid-cols-3" data-reveal-group="90">
                <div class="es-scr-card p-6" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-scr-ink text-base font-bold">Name and email</h3>
                        <span class="es-scr-plan">Free</span>
                    </div>
                    <p class="es-scr-muted text-sm">That is the whole form, plus a phone number if you choose to ask for one. No account needed, so nobody bounces off a sign-up wall on their way to your movie night.</p>
                </div>
                <div class="es-scr-card p-6" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-scr-ink text-base font-bold">Ask one more thing</h3>
                        <span class="es-scr-plan es-scr-plan-paid">Pro</span>
                    </div>
                    <p class="es-scr-muted text-sm">Add your own questions to the form, with presets or a pattern for the ones that need checking. "Seen it before?" is a better icebreaker than anything you can improvise once people are in.</p>
                </div>
                <div class="es-scr-card es-scr-full p-6" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-scr-ink text-base font-bold">Not a viewer count</h3>
                    </div>
                    <p class="es-scr-muted text-sm">This is a registration list. Event Schedule never touches your stream and cannot tell you how many people are watching right now. Anything that claims to would have to sit inside the platform.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- R5. The one socket (booth + the lit screen inside it)        -->
    <!-- ============================================================ -->
    <section id="link" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-scr-booth noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-scr-lamp"></div>
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-6xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="es-scr-reel mb-6" data-reveal aria-hidden="true"><span>R5</span></div>
                    <p class="es-scr-label mb-4" data-reveal style="--reveal-delay: 0.05s;">The one socket</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        One field in the booth. <span class="es-scr-lit">One lit page out front.</span>
                    </h2>
                </div>

                <div class="grid items-center gap-10 lg:grid-cols-2">
                    <!-- In the booth: what the host fills in -->
                    <div data-reveal>
                        <p class="es-scr-label mb-4">What you fill in</p>

                        <div class="es-scr-card p-6">
                            <p class="es-scr-label mb-2">Event URL</p>
                            <div class="es-scr-inset flex items-center gap-2 px-3 py-2.5" dir="ltr">
                                <svg aria-hidden="true" class="h-4 w-4 flex-none es-scr-lit" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                                <span class="es-scr-fig es-scr-booth-ink min-w-0 flex-1 truncate text-xs">https://watch.yourdomain.com/friday</span>
                            </div>
                            <p class="mt-4 text-sm es-scr-booth-muted">
                                That is the integration. Whatever is behind that link is between you and the platform, which is why nothing here breaks when you move from one to another.
                            </p>
                        </div>

                        <ul class="mt-6 space-y-3 es-scr-booth-muted" data-reveal-group="70">
                            <li class="flex gap-3" data-reveal>
                                <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none es-scr-lit" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="text-sm">In your public calendar an online event shows its link's host where a venue name would go, so the row reads as online at a glance.</span>
                            </li>
                            <li class="flex gap-3" data-reveal>
                                <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none es-scr-lit" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="text-sm">There is no platform picker and no connected-account list, so there is nothing to re-authorise at 7:40 on a Friday.</span>
                            </li>
                            <li class="flex gap-3" data-reveal>
                                <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none es-scr-lit" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="text-sm">A hybrid night can have a venue as well, for the people who want to watch it in a room together.</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Out front: the lit screen. Pinned identical in both modes. -->
                    <div data-reveal="panel">
                        <p class="es-scr-label mb-4">What they get</p>

                        <div class="es-scr-screen p-6 sm:p-7">
                            <p class="es-scr-screen-muted es-scr-screen-eyebrow mb-1">You are registered</p>
                            <h3 class="es-scr-screen-ink mb-1 text-xl font-black tracking-tight">Friday Movie Night</h3>
                            <p class="es-scr-screen-muted es-scr-fig mb-5 text-sm">Fri Nov 13 &middot; 8:00 PM</p>

                            <div class="es-scr-screen-inset mb-3 flex items-center gap-2 px-3 py-2.5" dir="ltr">
                                <svg aria-hidden="true" class="h-4 w-4 flex-none es-scr-screen-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                <span class="es-scr-screen-accent es-scr-fig truncate text-xs font-bold">watch.yourdomain.com/friday</span>
                            </div>

                            <div class="mb-5 grid grid-cols-3 gap-2" aria-hidden="true">
                                <div class="es-scr-screen-inset es-scr-screen-muted px-2 py-1.5 text-center es-scr-xxs font-bold uppercase tracking-wider">Google</div>
                                <div class="es-scr-screen-inset es-scr-screen-muted px-2 py-1.5 text-center es-scr-xxs font-bold uppercase tracking-wider">Outlook</div>
                                <div class="es-scr-screen-inset es-scr-screen-muted px-2 py-1.5 text-center es-scr-xxs font-bold uppercase tracking-wider">.ics</div>
                            </div>

                            <p class="es-scr-screen-muted es-scr-screen-hair border-t pt-4 text-xs leading-relaxed">
                                This is the page the confirmation email links to, and the join link is on it. The three calendar handoffs are stamped in UTC, so a viewer three timezones away gets the right hour in their own calendar without you doing timezone arithmetic in the description.
                            </p>
                        </div>

                        <p class="mt-5 text-xs leading-relaxed es-scr-booth-muted">
                            To be exact about it: times on your public page are shown in your schedule's timezone. The calendar file is absolute, so that is the one to trust and the one to tell people to use.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- R6. The call sheet: who came, and how you reach them         -->
    <!-- ============================================================ -->
    <section id="reach" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-scr-reel mb-6" data-reveal aria-hidden="true"><span>R6</span></div>
                <p class="es-scr-label mb-4" data-reveal style="--reveal-delay: 0.05s;">The call sheet</p>
                <h2 class="es-balance es-scr-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    You own the <span class="es-scr-accent">list.</span>
                </h2>
                <p class="es-scr-muted mx-auto mt-5 max-w-2xl text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Registration gives you a name and an email for every place taken. Following gives you a standing audience. Both are free, and both are yours to email.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-3" data-reveal-group="100">
                <div class="es-scr-card flex flex-col p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-scr-ink text-lg font-bold">Everyone who registered</h3>
                        <span class="es-scr-plan">Free</span>
                    </div>
                    <p class="es-scr-muted text-sm leading-relaxed">Scope a newsletter to one screening and it resolves to the people who took a place for it. That is the email you send the afternoon before with the viewing notes.</p>
                </div>
                <div class="es-scr-card flex flex-col p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-scr-ink text-lg font-bold">Everyone who follows</h3>
                        <span class="es-scr-plan">Free</span>
                    </div>
                    <p class="es-scr-muted text-sm leading-relaxed">A Follow button on your schedule, and a downloadable QR code you can put on screen at the end of the night so the room joins the list while the credits roll.</p>
                </div>
                <div class="es-scr-card flex flex-col p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-scr-ink text-lg font-bold">One strand of it</h3>
                        <span class="es-scr-plan">Free</span>
                    </div>
                    <p class="es-scr-muted text-sm leading-relaxed">Or a sub-schedule, so the documentary crowd hears about documentaries and the sports crowd does not. Pasted lists and the ticket waitlist work as targets too.</p>
                </div>
            </div>

            <div class="es-scr-card mt-6 p-7 sm:p-8" data-reveal="panel">
                <div class="grid gap-8 md:grid-cols-2 md:items-center">
                    <div>
                        <h3 class="es-scr-ink mb-3 text-xl font-bold">Nothing goes out on its own</h3>
                        <p class="es-scr-muted text-sm leading-relaxed">
                            There is no job that emails your followers when you add a date, and no page here is going to pretend otherwise. You write the email and you send it, which is slower and also the reason your list does not quietly rot. Open and click rates come back afterwards so you can tell whether Friday's note actually landed.
                        </p>
                        <p class="es-scr-muted mt-4 text-sm leading-relaxed">
                            The one thing that does arrive by itself is the confirmation, which every registrant gets the moment they take a place. And notifications run the other way too: when somebody asks you to add their screening to your calendar, you are the one who gets the email.
                        </p>
                    </div>
                    <div class="es-scr-inset p-5">
                        <p class="es-scr-label mb-4">Recipients a month</p>
                        <dl class="space-y-3">
                            <div class="es-scr-hair flex items-baseline justify-between gap-3 border-b pb-3">
                                <dt class="es-scr-muted text-sm font-semibold">Free</dt>
                                <dd class="es-scr-ink es-scr-fig text-lg font-black">10</dd>
                            </div>
                            <div class="es-scr-hair flex items-baseline justify-between gap-3 border-b pb-3">
                                <dt class="es-scr-muted text-sm font-semibold">Pro</dt>
                                <dd class="es-scr-ink es-scr-fig text-lg font-black">100</dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-3">
                                <dt class="es-scr-muted text-sm font-semibold">Enterprise</dt>
                                <dd class="es-scr-ink es-scr-fig text-lg font-black">1,000</dd>
                            </div>
                        </dl>
                        <p class="es-scr-muted mt-4 text-xs">Each recipient counts as one, so a note to sixty people is sixty. Selfhosted installs send through your own mail server and are not counted at all.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- R7. When you charge                                          -->
    <!-- ============================================================ -->
    <section id="tickets" class="es-scr-soft es-scr-hair scroll-mt-24 border-y py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-start gap-12 lg:grid-cols-2">
                <div>
                    <div class="es-scr-reel mb-6" data-reveal aria-hidden="true"><span>R7</span></div>
                    <p class="es-scr-label mb-4" data-reveal style="--reveal-delay: 0.05s;">When you charge</p>
                    <h2 class="es-balance es-scr-ink mb-5 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                        Nothing is taken at the <span class="es-scr-accent">door.</span>
                    </h2>
                    <p class="es-scr-muted mb-6 text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        A paid premiere, a benefit screening, a festival day pass. Ticketing is on the Pro plan at five dollars a month, payments run through your own Stripe account, and Event Schedule takes zero platform fees on every plan.
                    </p>
                    <ul class="es-scr-muted space-y-3" data-reveal-group="70">
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none es-scr-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Named ticket types, each with its own price, quantity and sales window. Inventory is counted per date, like the registration cap.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none es-scr-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>A ticket type can carry its own link, and that link goes out in the buyer's confirmation. A "watch at home" tier and an "in the room" tier can point at different places.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none es-scr-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>A pass valid across a run of screenings, once each, for a festival week or a season of Sunday documentaries.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none es-scr-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>A waitlist that tells people when a sold-out screening frees up, plus promo codes and gift cards if you want them.</span>
                        </li>
                    </ul>
                    <p class="es-scr-muted mt-6 text-sm">
                        To be clear about what this is not: there is no seating chart. These are priced ticket types with quantities, and nobody is choosing a specific seat.
                    </p>
                </div>

                <div data-reveal="panel">
                    <div class="es-scr-card p-6 sm:p-8">
                        <div class="mb-6 flex flex-wrap items-baseline justify-between gap-2">
                            <p class="es-scr-label">Premiere night</p>
                            <span class="es-scr-plan es-scr-plan-paid">Pro</span>
                        </div>

                        <table class="w-full border-collapse text-left">
                            <caption class="sr-only">Ticket types for a premiere screening, with prices and quantities</caption>
                            <thead>
                                <tr class="es-scr-label">
                                    <th scope="col" class="pb-3 font-bold">Ticket type</th>
                                    <th scope="col" class="pb-3 text-end font-bold">Price</th>
                                    <th scope="col" class="pb-3 text-end font-bold">Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ([['Watch at home', '$8', '250'], ['In the room', '$18', '60'], ['Supporter', '$40', '25'], ['Festival pass, 6 nights', '$60', '40']] as [$tName, $tPrice, $tQty])
                                    <tr class="es-scr-hair border-t">
                                        <th scope="row" class="es-scr-ink py-3 pe-3 align-middle text-sm font-semibold">{{ $tName }}</th>
                                        <td class="es-scr-ink es-scr-fig py-3 pe-3 text-end align-middle text-sm">{{ $tPrice }}</td>
                                        <td class="es-scr-muted es-scr-fig py-3 text-end align-middle text-sm">{{ $tQty }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="es-scr-inset mt-6 p-5">
                            <div class="es-scr-hair flex items-baseline justify-between gap-3 border-b pb-3">
                                <span class="es-scr-muted text-sm font-semibold">Event Schedule's cut</span>
                                <span class="es-scr-accent es-scr-fig text-2xl font-black">$0</span>
                            </div>
                            <p class="es-scr-muted mt-3 text-xs leading-relaxed">
                                Stripe charges its own standard processing fee on each payment, the same as it would anywhere. Event Schedule adds nothing on top, on any plan.
                            </p>
                        </div>

                        <p class="es-scr-muted mt-5 text-xs">
                            See all <a href="{{ marketing_url('/features/ticketing') }}" class="es-scr-link underline hover:no-underline">ticketing features</a>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- R8. Everything else (bento)                                  -->
    <!-- ============================================================ -->
    <section id="rest" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-scr-reel mb-6" data-reveal aria-hidden="true"><span>R8</span></div>
                <p class="es-scr-label mb-4" data-reveal style="--reveal-delay: 0.05s;">Everything else</p>
                <h2 class="es-balance es-scr-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    The rest of the <span class="es-scr-accent">booth.</span>
                </h2>
            </div>

            {{-- Bento spans, walked on paper. md (2 cols): b1 spans 2 = row 1;
                 b2 + b3 = row 2; b4 spans 2 = row 3; b5 + b6 = row 4. Every md
                 row sums to 2. lg (3 cols): b1(2) + b2 = 3; b3 + b4(2) = 3;
                 b5 + b6(2) = 3. Every lg row sums to 3, no empty corner. --}}
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="100">

                <!-- b1: analytics -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner es-scr-card relative flex h-full flex-col overflow-hidden p-7 lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-4 flex flex-wrap items-center gap-2">
                                    <p class="es-scr-label">Analytics</p>
                                    <span class="es-scr-plan">Free</span>
                                </div>
                                <h3 class="es-scr-ink mb-4 text-2xl font-black tracking-tight lg:text-3xl">Which post filled the room</h3>
                                <p class="es-scr-muted text-base leading-relaxed">
                                    Views by device, where the traffic came from, campaign tags on the links you post, rough locations and clicks on your social buttons. Built in, on every plan, with no third-party script and nothing to bolt on.
                                </p>
                            </div>
                            <div class="es-scr-inset w-full shrink-0 p-5 lg:w-64" aria-hidden="true">
                                <p class="es-scr-label mb-4">Where they came from</p>
                                @foreach ([['Community chat', 62], ['Newsletter', 48], ['Search', 27], ['Direct', 19]] as $si => [$sName, $sPct])
                                    <div class="mb-3">
                                        <div class="mb-1 flex items-baseline justify-between gap-2">
                                            <span class="es-scr-ink text-xs font-semibold">{{ $sName }}</span>
                                            <span class="es-scr-muted es-scr-fig text-xs">{{ $sPct }}</span>
                                        </div>
                                        <div class="es-scr-meter">
                                            <div class="es-scr-meter-fill" style="width: {{ $sPct }}%; --i: {{ $si }};"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- b2: embed -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner es-scr-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="mb-4 flex flex-wrap items-center gap-2">
                            <p class="es-scr-label">Embed</p>
                            <span class="es-scr-plan">Free</span>
                        </div>
                        <h3 class="es-scr-ink mb-3 text-xl font-bold">On the site you already have</h3>
                        <p class="es-scr-muted mb-6 text-sm leading-relaxed">Drop the calendar into your own page as an iframe and it keeps itself current. The ticket and registration form can be embedded too, on Pro.</p>
                        <div class="es-scr-inset mt-auto p-4" aria-hidden="true">
                            <p class="es-scr-fig es-scr-muted es-scr-xxs leading-relaxed" dir="ltr">&lt;iframe src="yourparty<wbr>.eventschedule.com<wbr>/embed"&gt;</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- b3: sub-schedules -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner es-scr-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="mb-4 flex flex-wrap items-center gap-2">
                            <p class="es-scr-label">Sub-schedules</p>
                            <span class="es-scr-plan">Free</span>
                        </div>
                        <h3 class="es-scr-ink mb-3 text-xl font-bold">Strands on one link</h3>
                        <p class="es-scr-muted mb-6 text-sm leading-relaxed">Keep the documentary series, the sports nights and the game launches apart with their own names and colours, so nobody reads the whole year to find one strand.</p>
                        <p class="es-scr-muted mt-auto text-xs leading-relaxed">A sub-schedule sorts and colours. It does not hide anything: to keep a screening off the public page while you plan it, leave it as a draft.</p>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- b4: calendar sync -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner es-scr-card relative flex h-full flex-col overflow-hidden p-7 lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-4 flex flex-wrap items-center gap-2">
                                    <p class="es-scr-label">Calendar sync</p>
                                    <span class="es-scr-plan">Free</span>
                                </div>
                                <h3 class="es-scr-ink mb-4 text-2xl font-black tracking-tight">Both directions, three calendars</h3>
                                <p class="es-scr-muted text-base leading-relaxed">
                                    Google, Outlook or Microsoft 365, and anything speaking CalDAV. Move a screening in your own calendar and it moves here; you choose what happens locally when something is deleted over there. See <a href="{{ marketing_url('/features/calendar-sync') }}" class="es-scr-link underline hover:no-underline">calendar sync</a>.
                                </p>
                            </div>
                            <div class="es-scr-inset p-5" aria-hidden="true">
                                <div class="es-scr-hair mb-3 flex items-baseline justify-between gap-2 border-b pb-3">
                                    <span class="es-scr-label">Your schedule</span>
                                    <span class="es-scr-accent es-scr-fig text-xs font-bold">two-way</span>
                                </div>
                                @foreach (['Google Calendar', 'Outlook / M365', 'CalDAV'] as $ci => $cName)
                                    <div class="es-scr-fig mb-2 flex items-center gap-2 text-xs">
                                        <svg aria-hidden="true" class="es-sync-dot h-3.5 w-3.5 flex-none es-scr-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="--i: {{ $ci }};">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        <span class="es-scr-ink">{{ $cName }}</span>
                                    </div>
                                @endforeach
                                <p class="es-scr-muted mt-3 es-scr-xxs">Plus a .ics download on every event and every date of a series.</p>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- b5: after the night -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner es-scr-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="mb-4 flex flex-wrap items-center gap-2">
                            <p class="es-scr-label">Afterwards</p>
                            <span class="es-scr-plan">Free</span>
                        </div>
                        <h3 class="es-scr-ink mb-3 text-xl font-bold">Photos and reactions</h3>
                        <p class="es-scr-muted mb-6 text-sm leading-relaxed">People who were there can post photos, clips and comments on the event with just a name and an email, and everything waits in an approval queue before it appears.</p>
                        <p class="es-scr-muted mt-auto text-xs leading-relaxed">Twenty-five photos per schedule on the free plan, no cap on Pro, with a bulk download when you want the lot.</p>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- b6: polls and feedback -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner es-scr-card relative flex h-full flex-col overflow-hidden p-7 lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-4 flex flex-wrap items-center gap-2">
                                    <p class="es-scr-label">Polls and feedback</p>
                                    <span class="es-scr-plan es-scr-plan-paid">Pro</span>
                                </div>
                                <h3 class="es-scr-ink mb-4 text-2xl font-black tracking-tight">Let the room pick the next one</h3>
                                <p class="es-scr-muted text-base leading-relaxed">
                                    Put a poll on the event and let viewers vote, or let them add their own suggestions, which land in a queue for you to approve rather than going straight up. After the night, ask for a rating and a comment. See <a href="{{ marketing_url('/features/polls') }}" class="es-scr-link underline hover:no-underline">polls</a>.
                                </p>
                            </div>
                            <div class="es-scr-inset p-5" aria-hidden="true">
                                <p class="es-scr-label mb-4">What are we watching in December?</p>
                                @foreach ([['The one with the boat', 44], ['Something in black and white', 31], ['Suggested by a viewer', 25]] as $pi => [$pName, $pPct])
                                    <div class="mb-3">
                                        <div class="mb-1 flex items-baseline justify-between gap-2">
                                            <span class="es-scr-ink text-xs font-semibold">{{ $pName }}</span>
                                            <span class="es-scr-muted es-scr-fig text-xs">{{ $pPct }}%</span>
                                        </div>
                                        <div class="es-scr-meter">
                                            <div class="es-scr-meter-fill" style="width: {{ $pPct }}%; --i: {{ $pi }};"></div>
                                        </div>
                                    </div>
                                @endforeach
                                <p class="es-scr-muted mt-3 es-scr-xxs">Viewer suggestions wait for your approval, and you get an email when some are pending.</p>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- R9. Perfect for (shared sub-audience cards)                  -->
    <!-- ============================================================ -->
    <section id="who" class="es-scr-soft es-scr-hair scroll-mt-24 border-y py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-scr-reel mb-6" data-reveal aria-hidden="true"><span>R9</span></div>
                <h2 class="es-balance es-scr-ink mb-4 text-3xl font-black tracking-tight md:text-5xl" data-reveal>
                    Watch party software for <span class="es-scr-accent">every community</span>
                </h2>
                <p class="es-scr-muted text-lg sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Whether it is a film club or a sports viewing party, Event Schedule works for you. Also see Event Schedule for <a href="{{ marketing_url('/for-live-concerts') }}" class="es-scr-link underline hover:no-underline">Live Concerts</a> and <a href="{{ marketing_url('/for-virtual-conferences') }}" class="es-scr-link underline hover:no-underline">Virtual Conferences</a>.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <x-sub-audience-card
                    name="Film Clubs & Cinephiles"
                    description="Classic screenings, director retrospectives and themed movie nights. Put the introduction and the discussion on the running order, where the club actually lives."
                    icon-color="amber"
                    blog-slug="for-film-clubs-cinephiles"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Content Creators & YouTubers"
                    description="Premiere a new video with your audience, host a reaction watch-along, or sell tickets to an exclusive screening. One link, whatever you are streaming through."
                    icon-color="orange"
                    blog-slug="for-content-creators-watch-parties"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Sports Fan Communities"
                    description="Game day watch parties, playoff screenings and draft nights. Free registration with a cap tells you how many are coming before the kickoff you cannot move."
                    icon-color="sky"
                    blog-slug="for-sports-fan-watch-parties"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Gaming Communities"
                    description="Esports watch parties, launch nights and tournament screenings. Set the whole season up as one recurring event with the weeks you are skipping taken out."
                    icon-color="teal"
                    blog-slug="for-gaming-community-watch-parties"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Corporate & Team Building"
                    description="Team movie nights and company screenings for people in different offices. The calendar handoff is stamped in UTC, so everyone's own calendar shows their own hour."
                    icon-color="emerald"
                    blog-slug="for-corporate-team-watch-parties"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Education & Documentary Groups"
                    description="Documentary screenings with discussion, film series and classroom viewings. The discussion is a part of the event with its own time, not an afterthought."
                    icon-color="blue"
                    blog-slug="for-education-documentary-watch-parties"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Three steps, as leader frames                                -->
    <!-- ============================================================ -->
    <section class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance es-scr-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal>
                    How to host a watch party online in <span class="es-scr-accent">three steps</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="120">
                @foreach ([['1', 'Open the doors', 'Create the event, paste your join link into the one Event URL field, turn on free registration and set the cap for the room you can actually handle.'], ['2', 'Post the running order', 'Add the parts of the night with their times: doors and chat, the introduction, the feature, the discussion afterwards. They publish with the event.'], ['3', 'Roll it', 'Every registrant gets a confirmation email linking to their own page, with the join link on it. Afterwards, collect photos and comments from the people who were there.']] as [$stepNum, $stepTitle, $stepBody])
                    <div class="text-center" data-reveal>
                        <div class="es-scr-leader mx-auto mb-5 flex h-14 w-14 items-center justify-center text-xl font-black">
                            <span class="es-scr-leader-num es-scr-fig">{{ $stepNum }}</span>
                        </div>
                        <h3 class="es-scr-ink mb-2 text-lg font-bold">{{ $stepTitle }}</h3>
                        <p class="es-scr-muted text-sm leading-relaxed">{{ $stepBody }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Key features                                                 -->
    <!-- ============================================================ -->
    <section class="es-scr-hair border-t py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-scr-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Online Events" description="One join link per event, for whatever you are streaming through" :url="marketing_url('/features/online-events')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="A weekly movie night as one event, with the skipped weeks taken out" :url="marketing_url('/features/recurring-events')" icon-color="amber">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Analytics" description="Views by device, referrers, campaign tags and locations" :url="marketing_url('/features/analytics')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Write it and send it, to followers or to one screening's registrants" :url="marketing_url('/features/newsletters')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-scr-link inline-flex items-center font-medium hover:underline">
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
    <!-- Related pages                                                -->
    <!-- ============================================================ -->
    <section class="es-scr-hair border-t py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-scr-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4" data-reveal-group="70">
                @foreach ([['/for-live-concerts', 'Live Concerts'], ['/for-bars', 'Bars'], ['/for-online-classes', 'Online Classes'], ['/for-live-qa-sessions', 'Live Q&A Sessions']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" class="es-scr-hover es-scr-card group flex flex-col p-5 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-scr-hover-title es-scr-ink mb-3 text-sm font-semibold transition-colors">For {{ $relName }}</span>
                        <span class="es-scr-hover-arrow es-scr-muted mt-auto inline-flex items-center gap-1 text-xs font-medium transition-colors">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
            <div class="mt-8 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-scr-link inline-flex items-center font-medium hover:underline">
                    See all use cases
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- R10. FAQ                                                     -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="es-scr-soft es-scr-hair scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="es-scr-reel mb-6" data-reveal aria-hidden="true"><span>R10</span></div>
                <h2 class="es-balance es-scr-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-scr-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    What screening hosts ask before they move a series across.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-scr-hover es-scr-card group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-scr-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-scr-accent es-scr-fig flex-none text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-scr-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-scr-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-scr-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Finale: house lights                                         -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-scr-booth noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-scr-lamp"></div>
                    <div class="grid-overlay absolute inset-0 opacity-25"></div>
                </div>
                <div class="relative z-10">
                    <p class="es-scr-label mb-4">Free forever</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        House lights down. <span class="es-scr-lit">Roll it.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg es-scr-booth-muted">
                        The running order, the door and the list are free forever. Ticketing is five dollars a month, and nothing is taken at the door.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-party" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="es-scr-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Get Started Free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="mt-6 text-sm es-scr-booth-muted">No credit card required</p>
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
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full es-scr-tip px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
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
