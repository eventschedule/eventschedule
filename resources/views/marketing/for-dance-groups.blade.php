<x-marketing-layout>
    <x-slot name="title">Dance Schedules | Classes, Rehearsals, Shows and Class Cards</x-slot>
    <x-slot name="description">Run weekly classes as recurring events with per-class capacity, and sell 10-visit cards, unlimited memberships and show tickets with zero platform fees.</x-slot>
    <x-slot name="breadcrumbTitle">For Dance Groups</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Dance Groups",
        "description": "One schedule for the class, the rehearsal and the show. Weekly classes run as recurring events with per-class capacity, rehearsal calls stay members-only, and passes cover a set number of visits.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Dance Groups"
        }
    }
    </script>
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Dance Groups",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Dance Studio Scheduling Software",
        "operatingSystem": "Web",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Free forever"
        },
        "featureList": [
            "Weekly classes as recurring events with day-of-week patterns and date exceptions",
            "Per-class capacity with free registration and a remaining-spots count",
            "Draft events that stay members-only, so rehearsal calls are not public",
            "Visit passes for a set number of classes, such as a 10-visit card",
            "Memberships with unlimited visits until the pass expires",
            "Season passes valid for every occurrence of a recurring event",
            "Per-pass cancellation deadline and late-cancel policy",
            "Named ticket types with their own prices, quantities and sales windows",
            "QR check-in for shows and classes",
            "Zero platform fees on ticket sales through your own Stripe account",
            "Sub-schedules that keep classes, rehearsals and performances apart",
            "Direct newsletters to the people who follow your schedule",
            "Two-way Google, Outlook and CalDAV calendar sync",
            "Embeddable calendar for the website you already have"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "dance studio schedule, dance class calendar, class card, dance company rehearsal schedule, recital ticketing, dance recurring classes",
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
           For-dance-groups "The Barre" styles.

           NAME: not "The Studio Wall" - /for-visual-artists already uses
           that nickname for a painter's wall (es-brush-*). "The Barre"
           matches this page's own es-barre-* prefix anyway.

           CONCEPT: the wall every dancer faces - mirror, barre, floor.
           The editorial spine is that the SAME WALL sees three different
           things (the class, the rehearsal, the show) belonging to three
           different audiences, which is exactly what sub-schedules plus
           visibility states plus one public link are for.

           WHY NOT "MOTION": the outgoing page was nicknamed "In Motion"
           and drew drifting curves via .es-flow - but the neighbouring
           /for-fitness-and-yoga is literally "The Flow" and owns that
           idea. Anything flowing, trailing or curving is off limits here.

           THE BARRE is built from CSS BOXES (a rounded bar plus bracket
           blocks), never an outline SVG - CLAUDE.md bans decorative line
           drawings of objects. Same construction as /for-nightclubs' door
           and /for-bars' A-frame legs.

           REFLECTIONS are CSS gradients only. Never duplicate text into a
           flipped copy: it would be read twice by assistive tech and
           indexed twice.

           COLOUR: mirror glass. After ten rebuilds no hue is unclaimed, so
           the accent comes from the MATERIAL rather than the wheel - real
           mirror glass is green-tinted from its iron content, which is why
           a mirror tunnel looks green. Same move as /for-nightclubs'
           brushed steel. Teal is unclaimed as a primary by any rebuilt
           page. Measured on this page's grounds:
             #115e59  6.97 studio / 6.51 panel   <- accent TEXT
             #0f766e  5.47 under white           <- fills, borders, CTA only
                      (only 4.70 on the light panel, so never body text)
             #2dd4bf  9.88 band / 9.24 panel     <- dark-mode accent text
           NEVER use text-gray-500: it measures 4.83 on white but only
           4.2-4.5 on a tinted ground like this one. Use .es-barre-muted
           (7.35 studio / 6.87 panel light, 7.12 / 6.66 dark).

           Dark mode is the same wall at night - an ordinary dark mode, NOT
           a single lit object floating in blackness. /for-theater-performers
           is "The Ghost Light" and owns that.
           ============================================================== */

        /* --- Ground and ink --- */
        .es-barre-page { background-color: #f3f6f5; color: #0f1a18; }
        .dark .es-barre-page { background-color: #0b1211; color: #e6edeb; }
        .es-barre-ink { color: #0f1a18; }
        .dark .es-barre-ink { color: #e6edeb; }
        .es-barre-muted { color: #48534f; }
        .dark .es-barre-muted { color: #93a5a0; }
        .es-barre-accent { color: #115e59; }
        .dark .es-barre-accent { color: #2dd4bf; }
        /* Always-lit accent for the dark band, in both colour modes. */
        .es-barre-lit { color: #2dd4bf; }

        .es-barre-grad {
            background-image: linear-gradient(100deg, #115e59, #0f766e);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dark .es-barre-grad,
        .es-barre-band .es-barre-grad {
            background-image: linear-gradient(100deg, #5eead4, #2dd4bf);
        }

        /* --- The barre: a rounded rail on two bracket blocks --------
           CSS boxes, not an SVG drawing. The rail draws from its left
           bracket on reveal; the FINISHED state lives on the always-active
           rule so no-JS and reduced-motion visitors see a complete barre. */
        .es-barre-rail {
            display: flex;
            align-items: center;
            gap: 0;
        }
        .es-barre-bracket {
            width: 0.6rem;
            height: 1.5rem;
            flex: 0 0 auto;
            border-radius: 0.2rem;
            background: linear-gradient(180deg, #c3cfcb, #94a6a1 55%, #74857f);
        }
        .dark .es-barre-bracket {
            background: linear-gradient(180deg, #3d4d49, #2a3733 55%, #1c2521);
        }
        .es-barre-bar {
            flex: 1 1 auto;
            height: 0.5rem;
            border-radius: 999px;
            background: linear-gradient(180deg, #cbd6d2, #8fa19c 42%, #63756f);
            box-shadow: 0 1px 2px rgba(15, 26, 24, 0.18);
            transform-origin: left center;
            transform: scaleX(1);
            transition: transform 1.1s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .dark .es-barre-bar {
            background: linear-gradient(180deg, #536660, #35443f 42%, #212c28);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }
        html.es-anim [data-reveal]:not(.is-revealed) .es-barre-bar { transform: scaleX(0); }

        /* --- The floor reflection under a panel ---------------------
           A gradient only. Nothing here is readable, so nothing here is
           duplicated content. */
        .es-barre-reflect { position: relative; }
        .es-barre-reflect::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 8%;
            right: 8%;
            height: 2.4rem;
            background: radial-gradient(ellipse 62% 100% at 50% 0%, rgba(17, 94, 89, 0.16), rgba(17, 94, 89, 0) 72%);
            pointer-events: none;
        }
        .dark .es-barre-reflect::after {
            background: radial-gradient(ellipse 62% 100% at 50% 0%, rgba(45, 212, 191, 0.15), rgba(45, 212, 191, 0) 72%);
        }

        /* --- Surfaces --- */
        .es-barre-card {
            background-color: #ffffff;
            border: 1px solid rgba(15, 26, 24, 0.1);
            border-radius: 1rem;
        }
        .dark .es-barre-card {
            background-color: #141d1b;
            border-color: rgba(230, 237, 235, 0.12);
        }
        .es-barre-sub {
            background-color: #e9efed;
            border: 1px solid rgba(15, 26, 24, 0.07);
            border-radius: 0.65rem;
        }
        .dark .es-barre-sub {
            background-color: #0f1917;
            border-color: rgba(230, 237, 235, 0.09);
        }
        .es-barre-hover { transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        .es-barre-hover:hover { border-color: rgba(15, 118, 110, 0.45); box-shadow: 0 10px 30px -18px rgba(15, 26, 24, 0.45); }
        .dark .es-barre-hover:hover { border-color: rgba(45, 212, 191, 0.4); box-shadow: 0 10px 30px -18px rgba(0, 0, 0, 0.8); }

        /* --- The mirror duplex ---------------------------------------
           One section only. Left is the wall the company sees, right is
           the wall the audience sees. The seam is the mirror's edge.
           (The wrapper needs no rule of its own: .es-barre-reflect already
           makes it the positioning context.) */
        .es-barre-seam {
            background: linear-gradient(180deg, rgba(15, 26, 24, 0), rgba(15, 26, 24, 0.16) 18%, rgba(15, 26, 24, 0.16) 82%, rgba(15, 26, 24, 0));
        }
        .dark .es-barre-seam {
            background: linear-gradient(180deg, rgba(230, 237, 235, 0), rgba(230, 237, 235, 0.18) 18%, rgba(230, 237, 235, 0.18) 82%, rgba(230, 237, 235, 0));
        }
        /* A single soft specular sweep, the way a lit mirror catches the
           room. Static: no animation to kill.

           The card is WHITE in light mode, so an additive white sweep is
           invisible - white over white is white. The light-mode sweep is
           therefore SUBTRACTIVE: two faint glass-tinted bands with the card
           left untouched between them, which is how a highlight reads on a
           pale surface. Dark mode can stay additive. Both stops are weak
           enough that text over them keeps its AA margin. */
        .es-barre-glass { position: relative; overflow: hidden; }
        .es-barre-glass::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(118deg,
                rgba(17, 94, 89, 0) 28%,
                rgba(17, 94, 89, 0.05) 41%,
                rgba(17, 94, 89, 0) 50%,
                rgba(17, 94, 89, 0.05) 59%,
                rgba(17, 94, 89, 0) 72%);
            pointer-events: none;
        }
        .dark .es-barre-glass::before {
            background: linear-gradient(118deg,
                rgba(255, 255, 255, 0) 34%,
                rgba(255, 255, 255, 0.055) 47%,
                rgba(255, 255, 255, 0) 60%);
        }

        /* --- Eyebrow, numerals, plan tags --- */
        .es-barre-tag {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #115e59;
        }
        .dark .es-barre-tag { color: #2dd4bf; }
        .es-barre-band .es-barre-tag { color: #2dd4bf; }

        .es-barre-corner {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.6rem;
            height: 2rem;
            border: 1px solid rgba(15, 26, 24, 0.18);
            border-radius: 0.35rem;
            background: rgba(15, 26, 24, 0.03);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.8rem;
            font-weight: 700;
            color: #0f1a18;
        }
        .dark .es-barre-corner { border-color: rgba(230, 237, 235, 0.2); background: rgba(230, 237, 235, 0.05); color: #e6edeb; }
        .es-barre-band .es-barre-corner { border-color: rgba(230, 237, 235, 0.2); background: rgba(230, 237, 235, 0.05); color: #e6edeb; }
        .es-barre-corner::before {
            content: "";
            position: absolute;
            left: 0.42rem;
            top: 0.42rem;
            bottom: 0.42rem;
            width: 2px;
            border-radius: 1px;
            background: #115e59;
        }
        .dark .es-barre-corner::before { background: #2dd4bf; }
        .es-barre-band .es-barre-corner::before { background: #2dd4bf; }

        .es-barre-plan {
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
        .es-barre-plan-free { border-color: rgba(15, 26, 24, 0.2); color: #48534f; }
        .dark .es-barre-plan-free { border-color: rgba(230, 237, 235, 0.24); color: #93a5a0; }
        .es-barre-plan-pro { border-color: rgba(15, 118, 110, 0.45); color: #115e59; background: rgba(15, 118, 110, 0.08); }
        .dark .es-barre-plan-pro { border-color: rgba(45, 212, 191, 0.4); color: #2dd4bf; background: rgba(45, 212, 191, 0.1); }
        .es-barre-plan-ent { border-color: rgba(15, 26, 24, 0.28); color: #0f1a18; background: rgba(15, 26, 24, 0.06); }
        .dark .es-barre-plan-ent { border-color: rgba(230, 237, 235, 0.3); color: #e6edeb; background: rgba(230, 237, 235, 0.08); }

        /* --- Visibility states are NOT plan tiers -------------------
           These must not borrow the plan palette. A teal pill means Pro
           everywhere else on the page, so styling "Public" teal reads as
           "Public needs Pro" - the exact opposite of this section's point,
           which is that Draft is free. States are distinguished by SHAPE:
           published is solid, a draft is hollow with a dashed edge, the
           way an unpublished thing is drawn everywhere else in the app. */
        .es-barre-state {
            display: inline-flex;
            align-items: center;
            border-radius: 0.3rem;
            border: 1px solid transparent;
            padding: 0.1rem 0.45rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .es-barre-state-public {
            border-color: rgba(15, 26, 24, 0.16);
            background: rgba(15, 26, 24, 0.075);
            color: #2f3a36;
        }
        .dark .es-barre-state-public {
            border-color: rgba(230, 237, 235, 0.2);
            background: rgba(230, 237, 235, 0.1);
            color: #cbd6d2;
        }
        .es-barre-state-draft {
            border-style: dashed;
            border-color: rgba(15, 26, 24, 0.32);
            background: transparent;
            color: #48534f;
        }
        .dark .es-barre-state-draft {
            border-color: rgba(230, 237, 235, 0.34);
            color: #93a5a0;
        }

        /* --- Buttons --- */
        .es-barre-btn {
            background-color: #0f766e;
            color: #ffffff;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .es-barre-btn:hover { background-color: #115e59; transform: translateY(-1px); box-shadow: 0 14px 30px -16px rgba(15, 118, 110, 0.9); }
        .es-barre-ghost {
            border: 1px solid rgba(15, 26, 24, 0.2);
            color: #0f1a18;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }
        .es-barre-ghost:hover { border-color: rgba(15, 118, 110, 0.5); background-color: rgba(15, 118, 110, 0.06); }
        .dark .es-barre-ghost { border-color: rgba(230, 237, 235, 0.22); color: #e6edeb; }
        .dark .es-barre-ghost:hover { border-color: rgba(45, 212, 191, 0.45); background-color: rgba(45, 212, 191, 0.08); }

        /* --- The dark band --- */
        .es-barre-band {
            /* A resolvable colour under the gradients: it is what paints if the
               gradients fail, and it is what a contrast audit can actually read. */
            background-color: #0d1614;
            background-image:
                radial-gradient(ellipse 70% 50% at 50% 0%, rgba(15, 118, 110, 0.16), rgba(15, 118, 110, 0) 70%),
                linear-gradient(180deg, #0f1a18, #0d1614);
        }

        /* --- The band does not change between colour modes, so nothing
               inside it may either ----------------------------------
           .es-barre-band has no .dark variant. Anything within it that DOES
           have one renders differently on an identical ground. That covers
           two shared classes which carry their own .dark rules in
           marketing.css and are invisible to a grep of this file's markup
           (.grid-overlay flips its lines black->white, .animate-shimmer
           flips white 0.3->0.15), AND the page's own barre, whose dark
           gradient would drop from silver to gunmetal here. Pin all three. */
        .es-barre-band .grid-overlay {
            background-image:
                linear-gradient(rgba(230, 237, 235, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(230, 237, 235, 0.05) 1px, transparent 1px);
        }
        .es-barre-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-barre-band .es-barre-bar {
            background: linear-gradient(180deg, #cbd6d2, #8fa19c 42%, #63756f);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.45);
        }
        .es-barre-band .es-barre-bracket {
            background: linear-gradient(180deg, #c3cfcb, #94a6a1 55%, #74857f);
        }

        /* --- Shared-chrome recolour --- */
        .es-barre-band .es-claim:focus-within {
            border-color: rgba(45, 212, 191, 0.75);
            box-shadow: 0 0 0 4px rgba(45, 212, 191, 0.22);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(15, 118, 110, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(45, 212, 191, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #115e59; }
        .dark .es-dot.is-active .es-dot-pip { background: #2dd4bf; }

        /* Focus rings. Never set border-radius here: an outline already
           follows the element's own radius, and overriding it changes the
           element's shape on focus. */
        #es-barre-page a:focus-visible,
        #es-barre-page summary:focus-visible,
        #es-barre-page button:focus-visible,
        #es-barre-page input:focus-visible {
            outline: 2px solid #0f766e;
            outline-offset: 2px;
        }
        .dark #es-barre-page a:focus-visible,
        .dark #es-barre-page summary:focus-visible,
        .dark #es-barre-page button:focus-visible,
        .dark #es-barre-page input:focus-visible {
            outline-color: #2dd4bf;
        }
        .es-barre-band a:focus-visible,
        .es-barre-band summary:focus-visible,
        .es-barre-band button:focus-visible,
        .es-barre-band input:focus-visible {
            outline-color: #2dd4bf !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-barre-bar { transition: none; transform: scaleX(1); }
            .es-barre-btn:hover { transform: none; }
        }
    </style>

    @php
        // The studio's week. One recurring event per class: a day-of-week
        // pattern, a curtain time, and a capacity that applies to each
        // occurrence. "Spots left" is capacity minus signed-up, and every
        // number below is asserted rather than eyeballed.
        $classes = [
            ['Ballet I',              'Tue &amp; Thu',  '6:00pm', 18, 15],
            ['Contemporary Technique','Wed',            '7:00pm', 20, 12],
            ['Hip-Hop Foundations',   'Sat',            '2:00pm', 24, 24],
        ];

        // 24 and 31 Dec 2026 are both Thursdays, so they fall on the
        // Ballet I pattern and are genuine date exceptions for it.
        $closures = ['Thu 24 Dec', 'Thu 31 Dec'];

        $faqs = [
            [
                'q' => 'Is Event Schedule free for dance groups?',
                'a' => 'The parts you use every week are free forever: weekly classes as recurring events, date exceptions for the weeks you are closed, free registration with a capacity per class, sub-schedules, two-way calendar sync, an embeddable calendar and up to 10 newsletter emails a month, counted per recipient rather than per send. Selling anything - class cards, memberships and show tickets - is on the Pro plan at '.plan_price($proMonthly).' a month, and Event Schedule charges zero platform fees on sales.',
            ],
            [
                'q' => 'How do I set up a weekly class?',
                'a' => 'Create the class once as a recurring event, choose the days of the week it runs and the time, then add date exceptions for the weeks the studio is closed. Set a capacity and the class shows how many spots are left, counted separately for each date rather than across the whole term.',
            ],
            [
                'q' => 'Can I sell a 10-class card instead of single classes?',
                'a' => 'Yes. A visit pass covers a set number of visits across the classes you attach it to, so a 10-visit card is one purchase that the dancer uses ten times. A membership gives unlimited visits until it expires, and a season pass covers every occurrence of one recurring event. Usage is tracked per visit, and each pass can carry its own cancellation deadline and late-cancel policy.',
            ],
            [
                'q' => 'Can I keep rehearsal calls off the public page?',
                'a' => 'Yes. Saving an event as a Draft keeps it members-only, so a rehearsal call sits on the same schedule as the class it belongs to without appearing on your public page. The Enterprise plan adds two more states: Internal, which is never public, and Unlisted, which is hidden from the schedule but reachable by direct link with an optional password.',
            ],
            [
                'q' => 'Can my choreographers and company manager edit the schedule?',
                'a' => 'A schedule includes one team member on the free plan. The Enterprise plan raises that to multiple team members and adds availability tracking, so you can record who is available before you set a rehearsal call.',
            ],
        ];

        $dotSections = [
            ['top', 'The wall'],
            ['week', 'The week'],
            ['rehearsal', 'The rehearsal'],
            ['card', 'The card'],
            ['show', 'The show'],
            ['public', 'In public'],
            ['who', 'Who it is for'],
            ['how', 'How it works'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];
    @endphp

    <div id="es-barre-page" class="es-barre-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the wall                                            -->
    <!-- ============================================================ -->
    {{-- The nav overlays the top of the page, so the hero carries extra top
         padding: the barre's brackets are the first thing in the right column
         and would otherwise be clipped by it. --}}
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden pb-16 pt-28">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 26%, rgba(15, 118, 110, 0.22), rgba(15, 118, 110, 0) 62%); opacity: 0.55;"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 72% 62%, rgba(45, 212, 191, 0.16), rgba(45, 212, 191, 0) 62%); opacity: 0.5;"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:72px_72px] [mask-image:radial-gradient(ellipse_72%_62%_at_50%_38%,black_22%,transparent_74%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-[1.05fr_1fr] lg:gap-16">
                <div>
                    <p class="es-barre-tag es-fade-up es-d-1 mb-5">For dance companies, studios and crews</p>

                    <h1 class="es-balance mb-7 text-[2.6rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">Three things happen</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">at <span class="es-barre-grad">the same wall</span>.</span></span>
                    </h1>

                    <p class="es-barre-muted es-fade-up es-d-2 mb-9 max-w-xl text-lg sm:text-xl">
                        The class, the rehearsal, the show. Three different audiences, one schedule -
                        and only the parts you choose are public.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="es-barre-btn inline-flex items-center justify-center gap-2 rounded-xl px-7 py-4 text-base font-semibold">
                            Create your schedule
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                        <a href="#week" class="es-barre-ghost inline-flex items-center justify-center gap-2 rounded-xl px-7 py-4 text-base font-semibold">
                            See how the week works
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                    </div>
                </div>

                <!-- The barre, with the week's three strands hanging from it. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-barre-rail mb-6 mt-2" aria-hidden="true">
                        <span class="es-barre-bracket"></span>
                        <span class="es-barre-bar"></span>
                        <span class="es-barre-bracket"></span>
                    </div>

                    <div class="es-barre-reflect grid gap-3">
                        @foreach ([
                            ['Class', 'Ballet I', 'Tue &amp; Thu &middot; 6:00pm', '3 spots left', false],
                            ['Rehearsal', 'Spring Gala, act two', 'Sat &middot; 10:00am', 'Draft &middot; members only', true],
                            ['Show', 'Spring Gala', 'Sat 30 May &middot; 7:30pm', 'Tickets from $22', false],
                        ] as [$strand, $name, $when, $note, $isDraft])
                            <div class="es-barre-card es-barre-hover p-4 sm:p-5">
                                <div class="mb-1.5 flex items-center justify-between gap-3">
                                    <span class="es-barre-tag">{{ $strand }}</span>
                                    <span class="es-barre-muted font-mono text-xs">{!! $when !!}</span>
                                </div>
                                <p class="es-barre-ink text-base font-bold">{!! $name !!}</p>
                                <p class="@if ($isDraft) es-barre-muted @else es-barre-accent @endif mt-1 text-sm font-semibold">{!! $note !!}</p>
                            </div>
                        @endforeach
                    </div>

                    <p class="es-barre-muted mt-10 text-xs">
                        One schedule. The class takes sign-ups, the rehearsal never reaches the public page,
                        and the show sells tickets.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The week (01)                                             -->
    <!-- ============================================================ -->
    <section id="week" class="scroll-mt-24 border-t border-[rgba(15,26,24,0.08)] py-20 dark:border-[rgba(230,237,235,0.08)] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-16">
                <div>
                    <div class="es-barre-corner mb-6" data-reveal aria-hidden="true"><span>01</span></div>
                    <p class="es-barre-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The week</p>
                    <h2 class="es-balance es-barre-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        A weekly class is <span class="es-barre-grad">one event</span>, not forty.
                    </h2>
                    <p class="es-barre-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        Set the class up once as a recurring event: the days it runs, the time it starts, and
                        date exceptions for the weeks the studio is closed. Change the time in September and
                        every Tuesday after it follows.
                    </p>

                    <ul class="space-y-4" data-reveal-group="90">
                        @foreach ([
                            ['Day-of-week patterns', 'Tuesday and Thursday, or just Saturdays. The pattern is the event.'],
                            ['Date exceptions', 'Take individual dates out for a closure, or add a one-off extra date in.'],
                            ['Capacity per class', 'A limit applies to each date on its own, so a full Tuesday does not close the Thursday.'],
                        ] as [$t, $d])
                            <li class="flex items-start gap-3" data-reveal>
                                <svg aria-hidden="true" class="es-barre-accent mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span><span class="es-barre-ink font-semibold">{{ $t }}</span> <span class="es-barre-muted">- {{ $d }}</span></span>
                            </li>
                        @endforeach
                    </ul>

                    <p class="mt-7" data-reveal>
                        <span class="es-barre-plan es-barre-plan-free">Free</span>
                        <span class="es-barre-muted ml-2 text-sm">Recurring events, date exceptions and registration are all on the free plan.</span>
                    </p>
                </div>

                <div class="es-bento group relative" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner es-barre-card overflow-hidden p-6 sm:p-7">
                        <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                            <h3 class="es-barre-ink text-lg font-bold">This week</h3>
                            <span class="es-barre-muted font-mono text-xs">3 recurring events</span>
                        </div>

                        <div class="space-y-2.5">
                            @foreach ($classes as [$cName, $cDays, $cTime, $cCap, $cTaken])
                                @php $left = $cCap - $cTaken; @endphp
                                <div class="es-barre-sub flex items-center justify-between gap-3 p-3.5">
                                    <div class="min-w-0">
                                        <p class="es-barre-ink truncate text-sm font-semibold">{{ $cName }}</p>
                                        <p class="es-barre-muted font-mono text-xs">{!! $cDays !!} &middot; {{ $cTime }}</p>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        @if ($left > 0)
                                            <p class="es-barre-accent text-sm font-bold">{{ $left }} spots left</p>
                                        @else
                                            <p class="es-barre-muted text-sm font-bold">Full</p>
                                        @endif
                                        <p class="es-barre-muted font-mono text-[0.65rem]">{{ $cTaken }} of {{ $cCap }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-5 border-t border-[rgba(15,26,24,0.1)] pt-4 dark:border-[rgba(230,237,235,0.12)]">
                            <p class="es-barre-tag mb-2">Date exceptions</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($closures as $closed)
                                    <span class="es-barre-sub es-barre-muted px-2.5 py-1 font-mono text-xs">{{ $closed }} &middot; closed</span>
                                @endforeach
                            </div>
                            <p class="es-barre-muted mt-3 text-xs">
                                Both fall on a Thursday, so Ballet I skips them and the Tuesday runs as normal.
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
    <!-- 3. The rehearsal - the mirror duplex (02)                    -->
    <!-- ============================================================ -->
    <section id="rehearsal" class="scroll-mt-24 border-t border-[rgba(15,26,24,0.08)] py-20 dark:border-[rgba(230,237,235,0.08)] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-barre-corner mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                <p class="es-barre-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The rehearsal</p>
                <h2 class="es-balance es-barre-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    The wall the company sees.<br class="hidden sm:block">
                    The wall <span class="es-barre-grad">the audience sees</span>.
                </h2>
                <p class="es-barre-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    A rehearsal call belongs on the same schedule as the show it is for. It just does not
                    belong on your public page.
                </p>
            </div>

            <div class="es-barre-reflect" data-reveal="panel">
                <div class="es-barre-card es-barre-glass">
                    <div class="relative z-10 grid lg:grid-cols-[1fr_1px_1fr]">
                        <!-- Company side -->
                        <div class="p-6 sm:p-8">
                            <div class="mb-5 flex items-center justify-between gap-3">
                                <p class="es-barre-tag">Signed in</p>
                                <span class="es-barre-plan es-barre-plan-free">Free</span>
                            </div>
                            <div class="space-y-2.5">
                                @foreach ([
                                    ['Ballet I', 'Tue & Thu 6:00pm', 'Public'],
                                    ['Spring Gala, act two', 'Sat 10:00am', 'Draft'],
                                    ['Spacing call, main stage', 'Fri 4:00pm', 'Draft'],
                                    ['Spring Gala', 'Sat 30 May 7:30pm', 'Public'],
                                ] as [$eName, $eWhen, $eState])
                                    <div class="es-barre-sub flex items-center justify-between gap-3 p-3">
                                        <div class="min-w-0">
                                            <p class="es-barre-ink truncate text-sm font-semibold">{!! $eName !!}</p>
                                            <p class="es-barre-muted font-mono text-xs">{{ $eWhen }}</p>
                                        </div>
                                        <span class="es-barre-state shrink-0 @if ($eState === 'Draft') es-barre-state-draft @else es-barre-state-public @endif">{{ $eState }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <p class="es-barre-muted mt-4 text-xs">Everything the company needs, in one place.</p>
                        </div>

                        <!-- The mirror's edge -->
                        <div class="es-barre-seam mx-6 my-0 h-px lg:mx-0 lg:my-8 lg:h-auto lg:w-px" aria-hidden="true"></div>

                        <!-- Audience side -->
                        <div class="p-6 sm:p-8">
                            <div class="mb-5 flex items-center justify-between gap-3">
                                <p class="es-barre-tag">Your public page</p>
                                <span class="es-barre-muted font-mono text-xs">yourstudio.eventschedule.com</span>
                            </div>
                            <div class="space-y-2.5">
                                @foreach ([
                                    ['Ballet I', 'Tue & Thu 6:00pm'],
                                    ['Spring Gala', 'Sat 30 May 7:30pm'],
                                ] as [$pName, $pWhen])
                                    <div class="es-barre-sub flex items-center justify-between gap-3 p-3">
                                        <div class="min-w-0">
                                            <p class="es-barre-ink truncate text-sm font-semibold">{!! $pName !!}</p>
                                            <p class="es-barre-muted font-mono text-xs">{{ $pWhen }}</p>
                                        </div>
                                        <svg aria-hidden="true" class="es-barre-accent h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    </div>
                                @endforeach
                            </div>
                            <p class="es-barre-muted mt-4 text-xs">The two rehearsal calls are simply not here.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-16 grid gap-4 md:grid-cols-2" data-reveal-group="100">
                <div class="es-barre-card p-6" data-reveal>
                    <div class="mb-3 flex items-center gap-2">
                        <h3 class="es-barre-ink text-lg font-bold">Two more states</h3>
                        <span class="es-barre-plan es-barre-plan-ent">Enterprise</span>
                    </div>
                    <p class="es-barre-muted text-sm">
                        Internal events are never public at all, and Unlisted events are hidden from the
                        schedule but still reachable by direct link, with an optional password - useful for a
                        preview you want the board to see and nobody else.
                    </p>
                </div>
                <div class="es-barre-card p-6" data-reveal>
                    <div class="mb-3 flex items-center gap-2">
                        <h3 class="es-barre-ink text-lg font-bold">Who can edit</h3>
                        <span class="es-barre-plan es-barre-plan-ent">Enterprise</span>
                    </div>
                    <p class="es-barre-muted text-sm">
                        A schedule includes one team member on the free plan. Enterprise raises that to
                        multiple team members and adds availability tracking, so you can see who is free
                        before you call the rehearsal.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. The card (03)                                             -->
    <!-- ============================================================ -->
    <section id="card" class="scroll-mt-24 border-t border-[rgba(15,26,24,0.08)] py-20 dark:border-[rgba(230,237,235,0.08)] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-16">
                <div class="order-2 lg:order-1">
                    <div class="es-bento group relative" data-tilt="4" data-reveal="panel">
                        <div class="es-tilt-inner es-barre-card overflow-hidden p-6 sm:p-7">
                            <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                                <h3 class="es-barre-ink text-lg font-bold">What people buy</h3>
                                <span class="es-barre-plan es-barre-plan-pro">Pro</span>
                            </div>

                            <div class="space-y-2.5">
                                @foreach ([
                                    ['Ten-class card', 'Visit pass', '10 visits, used one at a time', '$180'],
                                    ['Unlimited month', 'Membership', 'Every class until it expires', '$140'],
                                    ['Spring Gala season pass', 'Season pass', 'Every performance of the run, once each', '$95'],
                                    ['Drop-in', 'Single ticket', 'One class', '$22'],
                                ] as [$pName, $pKind, $pScope, $pPrice])
                                    <div class="es-barre-sub p-3.5">
                                        <div class="flex items-baseline justify-between gap-3">
                                            <p class="es-barre-ink min-w-0 flex-1 truncate text-sm font-semibold">{{ $pName }}</p>
                                            <p class="es-barre-ink shrink-0 font-mono text-sm">{{ $pPrice }}</p>
                                        </div>
                                        <div class="mt-1 flex flex-wrap items-baseline gap-x-2">
                                            <span class="es-barre-accent text-xs font-semibold">{{ $pKind }}</span>
                                            <span class="es-barre-muted text-xs">{{ $pScope }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <p class="es-barre-muted mt-5 border-t border-[rgba(15,26,24,0.1)] pt-4 text-xs dark:border-[rgba(230,237,235,0.12)]">
                                Usage is tracked per visit, so you can see how much of a card is left without
                                anyone keeping a paper tally.
                            </p>

                            <div class="es-glare" aria-hidden="true"></div>
                            <div class="es-ring-glow" aria-hidden="true"></div>
                        </div>
                    </div>
                </div>

                <div class="order-1 lg:order-2">
                    <div class="es-barre-corner mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                    <p class="es-barre-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The card</p>
                    <h2 class="es-balance es-barre-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Nobody buys <span class="es-barre-grad">one ballet class</span>.
                    </h2>
                    <p class="es-barre-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        They buy ten of them, or a month of them. A pass is one purchase that covers many
                        visits, so the dancer books in without paying again and you are not reconciling a
                        punch card at the desk.
                    </p>

                    <div class="space-y-3" data-reveal-group="90">
                        @foreach ([
                            ['A set number of visits', 'A visit pass covers a fixed count across the classes you attach it to - a ten-visit card is one purchase used ten times.'],
                            ['Unlimited until it expires', 'A membership covers every covered class until the expiry date, with no per-visit counting at all.'],
                            ['Every date of one class', 'A season pass covers each occurrence of a single recurring event, once per occurrence.'],
                            ['Cancellation, decided in advance', 'Each pass can carry its own cancellation deadline and a late-cancel policy - forfeit the visit, or block the cancellation.'],
                        ] as [$t, $d])
                            <div class="es-barre-card es-barre-hover p-4" data-reveal>
                                <p class="es-barre-ink text-sm font-bold">{{ $t }}</p>
                                <p class="es-barre-muted mt-1 text-sm">{{ $d }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. The show (04)                                             -->
    <!-- ============================================================ -->
    <section id="show" class="scroll-mt-24 border-t border-[rgba(15,26,24,0.08)] py-20 dark:border-[rgba(230,237,235,0.08)] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-barre-corner mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                <p class="es-barre-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The show</p>
                <h2 class="es-balance es-barre-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    The night the whole year <span class="es-barre-grad">points at</span>.
                </h2>
                <p class="es-barre-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Named ticket types, each with its own price, quantity and sales window - so the family
                    rate closes when you want it to and the door price does not open early.
                </p>
            </div>

            <div class="grid gap-4 lg:grid-cols-[1.15fr_1fr]">
                <div class="es-barre-card es-barre-reflect p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-barre-ink text-lg font-bold">Spring Gala</h3>
                        <span class="es-barre-muted font-mono text-xs">Sat 30 May &middot; 7:30pm</span>
                    </div>
                    <p class="es-barre-muted mb-5 text-sm">Four ticket types on one event.</p>

                    <div class="space-y-2.5">
                        @foreach ([
                            ['Adult', 'On sale now', '$28', '120'],
                            ['Student &amp; senior', 'On sale now', '$18', '80'],
                            ['Under 12', 'On sale now', '$12', '60'],
                            ['Family of four', 'Closes 7 days before', '$72', '40'],
                        ] as [$tName, $tWindow, $tPrice, $tQty])
                            <div class="es-barre-sub flex items-baseline gap-3 p-3.5">
                                <span class="es-barre-ink min-w-0 flex-1 truncate text-sm font-semibold">{!! $tName !!}</span>
                                <span class="es-barre-muted hidden truncate text-xs sm:inline">{{ $tWindow }}</span>
                                <span class="es-barre-muted font-mono text-xs">{{ $tQty }}</span>
                                <span class="es-barre-ink font-mono text-sm">{{ $tPrice }}</span>
                            </div>
                        @endforeach
                    </div>

                    <p class="es-barre-muted mt-5 border-t border-[rgba(15,26,24,0.1)] pt-4 text-xs dark:border-[rgba(230,237,235,0.12)]">
                        Payment goes through your own Stripe account. Event Schedule takes no cut of it.
                    </p>
                </div>

                <div class="grid gap-4" data-reveal-group="100">
                    @foreach ([
                        ['Zero platform fees', 'You keep the whole ticket price minus what Stripe charges to process the card. There is no per-ticket cut on top.', 'pro'],
                        ['Live check-in view', 'Scanning tickets from a phone is free on every plan. Pro adds the running count and the per-ticket breakdown, so two people can work the queue and both see the same total.', 'pro'],
                        ['Questions at checkout', 'Ask for the dancer\'s name, the class they are in, or a photo consent - collected with the sale instead of chased afterwards.', 'pro'],
                    ] as [$t, $d, $tier])
                        <div class="es-barre-card es-barre-hover p-6" data-reveal>
                            <div class="mb-2 flex items-center gap-2">
                                <h3 class="es-barre-ink text-base font-bold">{{ $t }}</h3>
                                <span class="es-barre-plan es-barre-plan-pro">Pro</span>
                            </div>
                            <p class="es-barre-muted text-sm">{{ $d }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. The wall in public (05)                                   -->
    <!-- ============================================================ -->
    <section id="public" class="scroll-mt-24 border-t border-[rgba(15,26,24,0.08)] py-20 dark:border-[rgba(230,237,235,0.08)] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-barre-corner mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                <p class="es-barre-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">In public</p>
                <h2 class="es-balance es-barre-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    One link, on <span class="es-barre-grad">everything you print</span>.
                </h2>
                <p class="es-barre-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    The studio window, the programme, the bio on your profile. It is the same address all
                    year and it is never out of date.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">
                <div class="es-bento group relative md:col-span-2" data-tilt="3" data-reveal="panel">
                    <div class="es-tilt-inner es-barre-card h-full overflow-hidden p-6">
                        <div class="mb-2 flex items-center gap-2">
                            <h3 class="es-barre-ink text-lg font-bold">Embed it on the site you already have</h3>
                            <span class="es-barre-plan es-barre-plan-free">Free</span>
                        </div>
                        <p class="es-barre-muted text-sm">
                            Drop the calendar into your existing website in an iframe. It keeps itself current,
                            so the term timetable on your homepage stops being a screenshot somebody has to
                            remember to replace.
                        </p>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <div class="es-barre-card es-barre-hover p-6" data-reveal>
                    <div class="mb-2 flex items-center gap-2">
                        <h3 class="es-barre-ink text-lg font-bold">Followers</h3>
                        <span class="es-barre-plan es-barre-plan-free">Free</span>
                    </div>
                    <p class="es-barre-muted text-sm">
                        People follow your schedule and hear about a new date from you, in their inbox,
                        rather than from a feed that decides who sees it.
                    </p>
                </div>

                <div class="es-barre-card es-barre-hover p-6" data-reveal>
                    <div class="mb-2 flex items-center gap-2">
                        <h3 class="es-barre-ink text-lg font-bold">Newsletters</h3>
                        <span class="es-barre-plan es-barre-plan-free">Free</span>
                    </div>
                    <p class="es-barre-muted text-sm">
                        Write and send from the same place, with open and click rates afterwards. Ten emails a
                        month on the free plan, a hundred on Pro and a thousand on Enterprise, counted per recipient rather than per send.
                    </p>
                </div>

                <div class="es-barre-card es-barre-hover p-6 md:col-span-2" data-reveal>
                    <div class="mb-2 flex items-center gap-2">
                        <h3 class="es-barre-ink text-lg font-bold">Calendar sync</h3>
                        <span class="es-barre-plan es-barre-plan-free">Free</span>
                    </div>
                    <p class="es-barre-muted text-sm">
                        Two-way sync with Google, Outlook and CalDAV, so a rehearsal moved on your phone moves
                        on the schedule too.
                    </p>
                </div>

                <div class="es-barre-card es-barre-hover p-6" data-reveal>
                    <div class="mb-2 flex items-center gap-2">
                        <h3 class="es-barre-ink text-lg font-bold">Share graphics</h3>
                        <span class="es-barre-plan es-barre-plan-free">Free</span>
                    </div>
                    <p class="es-barre-muted text-sm">
                        Every event can generate a post-sized and a story-sized image with your own branding
                        on it, ready to download.
                    </p>
                </div>

                <div class="es-barre-card es-barre-hover p-6 lg:col-span-2" data-reveal>
                    <div class="mb-2 flex items-center gap-2">
                        <h3 class="es-barre-ink text-lg font-bold">Online classes</h3>
                        <span class="es-barre-plan es-barre-plan-free">Free</span>
                    </div>
                    <p class="es-barre-muted text-sm">
                        Mark an event as online and add the link people join on - any platform that gives you
                        a URL. Ticket holders get it with their ticket.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Who it is for (06)                                        -->
    <!-- ============================================================ -->
    <section id="who" class="scroll-mt-24 border-t border-[rgba(15,26,24,0.08)] py-20 dark:border-[rgba(230,237,235,0.08)] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-barre-corner mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                <p class="es-barre-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Who it is for</p>
                <h2 class="es-balance es-barre-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Every kind of room with <span class="es-barre-grad">a mirror in it</span>.
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <x-sub-audience-card
                    name="Ballet Companies"
                    description="A repertory season, a Nutcracker run and a studio showcase, each set up once and sold from the same link."
                    icon-color="teal"
                    blog-slug="for-ballet-companies"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v9m0 0a3 3 0 103 3M12 12a3 3 0 11-3 3m0 0v3m6-3v3" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Hip-Hop Crews"
                    description="Battles, showcases and cyphers. Post the date, take sign-ups with a capacity, sell at the door with QR check-in."
                    icon-color="amber"
                    blog-slug="for-hip-hop-crews"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l11-2v13M9 19a3 3 0 11-6 0 3 3 0 016 0zm11-2a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Ballroom & Latin Studios"
                    description="Weekly technique, a social every month and a showcase in the spring - three sub-schedules, one public page."
                    icon-color="rose"
                    blog-slug="for-ballroom-latin-studios"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 21V9m8 12V9M8 9a3 3 0 116 0m-6 0h8M6 4h4m4 0h4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Contemporary & Modern"
                    description="Residencies, site-specific work and shared bills. Keep the making private and publish only the dates that are ready."
                    icon-color="emerald"
                    blog-slug="for-contemporary-modern-dance"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 18c2.5 0 2.5-4 5-4s2.5 4 5 4 2.5-4 5-4M4 10c2.5 0 2.5-4 5-4s2.5 4 5 4 2.5-4 5-4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Folk & Cultural Ensembles"
                    description="Festival appearances, heritage nights and community performances, in a calendar people can subscribe to."
                    icon-color="sky"
                    blog-slug="for-folk-cultural-dance"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18zm0 0c2.5-2.5 3.5-5.5 3.5-9S14.5 5.5 12 3m0 18c-2.5-2.5-3.5-5.5-3.5-9S9.5 5.5 12 3M3.5 9h17M3.5 15h17" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Dance Schools & Academies"
                    description="A full timetable of graded classes with a capacity on each, and a recital at the end of it that sells its own tickets."
                    icon-color="slate"
                    blog-slug="for-dance-schools-academies"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-slate-600 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. How it works (07, dark band)                              -->
    <!-- ============================================================ -->
    <section id="how" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-barre-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="es-barre-corner mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                    <p class="es-barre-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">How it works</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        A term takes <span class="es-barre-grad">an afternoon</span> to set up.
                    </h2>
                </div>

                <div class="es-barre-rail mb-12" data-reveal aria-hidden="true">
                    <span class="es-barre-bracket"></span>
                    <span class="es-barre-bar"></span>
                    <span class="es-barre-bracket"></span>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    @foreach ([
                        ['01', 'Put the timetable up', 'One recurring event per class, with the days it runs and the weeks you are closed. Sub-schedules keep classes, rehearsals and shows on their own strands.'],
                        ['02', 'Decide what is public', 'Classes and shows go out. Rehearsal calls stay as Drafts, on the same schedule, members-only.'],
                        ['03', 'Sell the card, not the class', 'A ten-visit card, an unlimited month, or a ticket to the gala - all from the one link you already share.'],
                    ] as [$n, $t, $d])
                        <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7 backdrop-blur-sm" data-reveal="panel">
                            <p class="es-barre-lit mb-3 font-mono text-sm font-bold">{{ $n }}</p>
                            <h3 class="mb-2 text-lg font-bold text-white">{{ $t }}</h3>
                            <p class="text-sm text-gray-400">{{ $d }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Key features                                              -->
    <!-- ============================================================ -->
    <section class="scroll-mt-24 border-t border-[rgba(15,26,24,0.08)] py-20 dark:border-[rgba(230,237,235,0.08)]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-barre-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="A weekly class set up once, with date exceptions for the weeks you are closed" :url="marketing_url('/features/recurring-events')" icon-color="teal">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="Class cards, memberships and show tickets with zero platform fees" :url="marketing_url('/features/ticketing')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Sub-schedules" description="Keep classes, rehearsals and performances on their own strands" :url="marketing_url('/features/sub-schedules')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Email the people who follow your studio, with open and click rates" :url="marketing_url('/features/newsletters')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-barre-accent inline-flex items-center font-medium hover:underline">
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
    <!-- 10. Keep exploring                                           -->
    <!-- ============================================================ -->
    <section class="border-t border-[rgba(15,26,24,0.08)] py-16 dark:border-[rgba(230,237,235,0.08)]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-barre-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([
                    ['/for-theaters', 'Theaters'],
                    ['/for-theater-performers', 'Theater Performers'],
                    ['/for-fitness-and-yoga', 'Fitness &amp; Yoga'],
                    ['/for-workshop-instructors', 'Workshop Instructors'],
                ] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="es-barre-card es-barre-hover group flex items-center justify-between p-5">
                        <div>
                            <div class="es-barre-muted text-sm">Event Schedule for</div>
                            <div class="es-barre-ink text-lg font-semibold">{!! $relName !!}</div>
                        </div>
                        <svg aria-hidden="true" class="es-barre-accent h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-barre-accent inline-flex items-center font-medium hover:underline">
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
    <!-- 11. FAQ (08)                                                 -->
    <!-- ============================================================ -->
    <section id="faq" class="scroll-mt-24 border-t border-[rgba(15,26,24,0.08)] py-20 dark:border-[rgba(230,237,235,0.08)] lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-barre-corner mb-6" data-reveal aria-hidden="true"><span>08</span></div>
                <p class="es-barre-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Questions</p>
                <h2 class="es-balance es-barre-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Asked in <span class="es-barre-grad">the studio office</span>.
                </h2>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ($faqs as $faq)
                    <details name="faq" data-reveal class="es-barre-card group/faq overflow-hidden">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-6">
                            <h3 class="es-barre-ink text-lg font-semibold">{{ $faq['q'] }}</h3>
                            <svg aria-hidden="true" class="es-barre-muted h-5 w-5 shrink-0 transition-transform duration-300 group-open/faq:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="es-barre-muted faq-answer px-6 pb-6">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <x-seo.faq-schema :items="$faqs" />

    <!-- ============================================================ -->
    <!-- 12. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-barre-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-25"></div>
                </div>

                <div class="relative z-10">
                    <div class="es-barre-rail mx-auto mb-10 max-w-md" aria-hidden="true">
                        <span class="es-barre-bracket"></span>
                        <span class="es-barre-bar"></span>
                        <span class="es-barre-bracket"></span>
                    </div>

                    <p class="es-barre-tag mb-6">Free forever</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                        Put the timetable <span class="es-barre-grad">where people look</span>.
                    </h2>
                    <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                        Classes, rehearsal calls and the gala, on one schedule with one address.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-studio" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="es-barre-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold">
                            <span class="relative z-10 flex items-center gap-2">
                                Create your schedule
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
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-medium text-gray-700 opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3 dark:border-white/10 dark:bg-[#141d1b] dark:text-gray-300">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
