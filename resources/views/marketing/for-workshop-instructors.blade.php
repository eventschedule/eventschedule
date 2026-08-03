<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Workshop Instructors | Share Classes</x-slot>
    <x-slot name="description">Fill every workshop seat. Announce classes, sell spots with zero platform fees, and email your students directly. Build multi-session series. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Workshop Instructors</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Workshop Instructors",
        "description": "Set a class up once as a weekly series, cap the bench per session, and sell the spots from one link with zero platform fees.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Workshop Instructors & Educators"
        }
    }
    </script>
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Workshop Instructors",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Workshop and Class Scheduling Software",
        "operatingSystem": "Web",
        "description": "One class, set up once as a weekly series that ends after a set number of sessions, with the seat count kept per session date. Sell spots through your own Stripe account with zero platform fees, or run free registration with a seat limit.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "A class set up once as a weekly series, ending on a date or after a set number of sessions",
            "Single dates skipped or added without rebuilding the series",
            "Free registration with a seat limit counted per session date",
            "Named ticket types with their own prices, quantities and sales windows",
            "Multi-class cards valid across the series, with a cancellation cutoff in hours before each session",
            "A waitlist when a session fills, offering a freed seat to the next person waiting for that date",
            "QR check-in, plus a downloadable QR code for your schedule",
            "Zero platform fees on ticket sales through your own Stripe account",
            "Sub-schedules that keep beginner and advanced strands apart on one link",
            "Direct newsletters to the students who follow your schedule",
            "Two-way Google, Outlook and CalDAV calendar sync, a recurring class syncing as one entry",
            "Embeddable calendar for the studio site you already have"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "workshop scheduling, class registration software, workshop calendar, teaching class management, free workshop scheduling",
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
        "name": "How to put a workshop series online with Event Schedule",
        "description": "Set the class up once, cap the bench, and let students book the session they want.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Set the class up as a series",
                "text": "Create the class once as a recurring event, pick the day it runs, and give it an end: a last date, or a number of sessions."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Cap the bench",
                "text": "Set the seat limit. It is counted per session date, so a full Saturday does not close the next one. Skip the weeks the studio is closed."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Open the sheet",
                "text": "Share one link. Take free registrations, or connect Stripe and sell spots and multi-class cards with zero platform fees."
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
           For-workshop-instructors "The Workshop" styles.

           THE CONCEPT: the bench and the sheet pinned above it. An
           instructor's real unit of work is not a date, it is a bench
           with eight stools, run again next Saturday, with a numbered
           sign-up sheet on the wall and a punch card in the student's
           pocket. Those three objects ARE the product argument: one
           recurring event (days_of_week + recurring_end_type), a seat
           count kept per occurrence date (Event::rsvpRemaining($date),
           Ticket::soldKey($date)), and a pass that spans the series
           (tickets.is_pass).

           SIGNATURE DEVICE: the sheet's ruled lines ARE the seats. Eight
           numbered rules, six written on, two blank, and the count read
           off the bottom. No bar chart, no percentage: a workshop's
           capacity is a small whole number and it should read like one.

           COLOUR: this page keeps the slate-blue end of its original
           chalk family (#285f86 / #2f6ea3) and darkens it to #14506e so
           it clears AA on the warm workshop ground. Measured: #14506e on
           #f4f3f0 is 7.88, on the cream sheet #f7f2e6 is 7.82; #8ccbf0
           on the dark ground #0b1013 is 10.85 and on the wall #101a20 is
           10.01. Muted ink is #4b5560 (6.84) - NOT text-gray-500, which
           only measures 4.2-4.5 on a tinted ground like this one.

           FIXED PHYSICAL OBJECTS, identical with .dark on or off:
           .es-shop-sheet (a sheet of cream paper) and .es-shop-wall (the
           workshop wall). Both carry overrides for the shared classes
           that otherwise flip: .grid-overlay, .animate-shimmer,
           .es-claim:focus-within, and this page's own card/tag/pill.
           ============================================================== */

        /* --- Ground and ink ------------------------------------------ */
        .es-shop-page { background-color: #f4f3f0; color: #141a1e; }
        .dark .es-shop-page { background-color: #0b1013; color: #e6edf2; }
        .es-shop-ink { color: #141a1e; }
        .dark .es-shop-ink { color: #e6edf2; }
        .es-shop-muted { color: #4b5560; }
        .dark .es-shop-muted { color: #9aa8b2; }
        .es-shop-accent { color: #14506e; }
        .dark .es-shop-accent { color: #8ccbf0; }
        /* Always-lit accent, for text on the fixed-dark wall in BOTH modes. */
        .es-shop-lit { color: #8ccbf0; }
        .es-shop-wall-ink { color: #e6edf2; }
        .es-shop-wall-muted { color: #9aa8b2; }

        /* Tabular figures, because everything on this page is a count. */
        .es-shop-fig {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            letter-spacing: 0.01em;
        }

        /* --- Cards --------------------------------------------------- */
        .es-shop-card {
            border: 1px solid rgba(20, 26, 30, 0.12);
            border-radius: 1rem;
            background: #fdfdfc;
        }
        .dark .es-shop-card {
            border-color: rgba(230, 237, 242, 0.12);
            background: rgba(230, 237, 242, 0.045);
        }

        /* --- The wall: fixed dark in both colour modes ---------------- */
        .es-shop-wall {
            background-color: #101a20;
            background-image:
                radial-gradient(120% 100% at 50% 0%, rgba(140, 203, 240, 0.09) 0%, rgba(16, 26, 32, 0) 58%),
                linear-gradient(180deg, #16232a 0%, #101a20 46%, #0a1216 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(230, 237, 242, 0.06);
        }
        /* Pegboard: a punched-panel texture, not a drawing. A dark hole with a
           lit lower lip on the same 26px grid reads as a drilled board. */
        .es-shop-peg {
            background-image:
                radial-gradient(circle at 50% 44%, rgba(0, 0, 0, 0.6) 1.7px, transparent 1.9px),
                radial-gradient(circle at 50% 58%, rgba(230, 237, 242, 0.07) 1.7px, transparent 1.9px);
            background-size: 26px 26px, 26px 26px;
            background-position: 0 0, 0 0;
        }
        /* Shared classes that flip with the colour mode. These MUST come
           after their .dark rules so the later, equal-specificity rule
           wins in both modes and the wall stays one object. */
        .es-shop-wall .es-shop-card {
            border-color: rgba(230, 237, 242, 0.14);
            background: rgba(230, 237, 242, 0.05);
        }
        .es-shop-wall .grid-overlay {
            background-image:
                linear-gradient(rgba(230, 237, 242, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(230, 237, 242, 0.05) 1px, transparent 1px);
        }
        .es-shop-wall .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.16), transparent);
            background-size: 200% 100%;
        }
        .es-shop-wall .es-claim:focus-within {
            border-color: rgba(140, 203, 240, 0.75);
            box-shadow: 0 0 0 4px rgba(140, 203, 240, 0.22);
        }

        /* --- Eyebrow label ------------------------------------------- */
        .es-shop-tag {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            color: #4b5560;
        }
        .dark .es-shop-tag { color: #9aa8b2; }
        .es-shop-wall .es-shop-tag { color: #8ccbf0; }

        /* --- The bench tag: a punched job tag carrying the section no. */
        .es-shop-num {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.3rem 0.85rem 0.3rem 0.6rem;
            border: 1px solid rgba(20, 26, 30, 0.18);
            border-radius: 0.35rem;
            background: #fdfdfc;
            color: #141a1e;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-weight: 800;
            letter-spacing: 0.06em;
        }
        .es-shop-num::before {
            content: "";
            width: 0.6rem;
            height: 0.6rem;
            flex: none;
            border-radius: 9999px;
            border: 1px solid rgba(20, 26, 30, 0.4);
        }
        .dark .es-shop-num {
            border-color: rgba(230, 237, 242, 0.2);
            background: rgba(230, 237, 242, 0.05);
            color: #e6edf2;
        }
        .dark .es-shop-num::before { border-color: rgba(230, 237, 242, 0.45); }
        .es-shop-wall .es-shop-num {
            border-color: rgba(230, 237, 242, 0.2);
            background: rgba(230, 237, 242, 0.05);
            color: #e6edf2;
        }
        .es-shop-wall .es-shop-num::before { border-color: rgba(230, 237, 242, 0.45); }

        /* --- Plan pills ---------------------------------------------- */
        .es-shop-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border: 1px solid rgba(20, 80, 110, 0.45);
            border-radius: 0.25rem;
            color: #14506e;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }
        .dark .es-shop-plan { border-color: rgba(140, 203, 240, 0.45); color: #8ccbf0; }
        .es-shop-plan-pro { border-color: rgba(20, 26, 30, 0.35); color: #141a1e; }
        .dark .es-shop-plan-pro { border-color: rgba(230, 237, 242, 0.38); color: #e6edf2; }
        .es-shop-wall .es-shop-plan { border-color: rgba(140, 203, 240, 0.45); color: #8ccbf0; }
        .es-shop-wall .es-shop-plan-pro { border-color: rgba(230, 237, 242, 0.38); color: #e6edf2; }

        /* --- THE SHEET: a sheet of cream paper. Fixed in both modes. -- */
        .es-shop-sheet {
            position: relative;
            border: 1px solid rgba(20, 26, 30, 0.16);
            border-radius: 0.5rem;
            background-color: #f7f2e6;
            background-image: linear-gradient(180deg, rgba(255, 255, 255, 0.6), rgba(0, 0, 0, 0.02));
            box-shadow: 0 22px 40px -24px rgba(10, 18, 22, 0.55), inset 0 1px 0 rgba(255, 255, 255, 0.6);
            color: #2a2721;
            padding: 1.35rem 1.25rem 1.15rem 1.9rem;
        }
        /* Punched binder holes down the left edge. */
        .es-shop-holes {
            position: absolute;
            top: 1.4rem;
            bottom: 1.4rem;
            left: 0.65rem;
            width: 0.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .es-shop-holes i {
            display: block;
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 9999px;
            background: rgba(20, 26, 30, 0.1);
            box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.25);
        }
        .es-shop-sheet-ink { color: #2a2721; }
        .es-shop-sheet-muted { color: #5a544a; }
        .es-shop-sheet-accent { color: #14506e; }
        .es-shop-sheet-rule { border-color: rgba(20, 26, 30, 0.14); }

        /* A ruled line on the sheet IS a seat. */
        .es-shop-line {
            display: flex;
            align-items: baseline;
            gap: 0.7rem;
            padding: 0.34rem 0 0.2rem;
            border-bottom: 1px solid rgba(20, 80, 110, 0.22);
        }
        .es-shop-line-no {
            flex: none;
            width: 1.15rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.68rem;
            font-weight: 700;
            color: #5a544a;
        }
        .es-shop-line-open .es-shop-line-name { color: #5a544a; letter-spacing: 0.18em; }
        .es-shop-line-name { flex: 1 1 auto; min-width: 0; font-size: 0.86rem; font-weight: 600; }
        .es-shop-line-how {
            flex: none;
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #14506e;
        }
        /* The written lines fill in when the sheet reveals. The transition
           lives on the always-active rule; only the undrawn pre-state is
           gated, so no-JS and reduced-motion users see a full sheet. */
        .es-shop-line-name, .es-shop-line-how {
            transition: opacity 0.5s ease, transform 0.5s ease;
            transition-delay: calc(var(--i, 0) * 0.09s + 0.35s);
        }
        html.es-anim [data-reveal]:not(.is-revealed) .es-shop-line-name,
        html.es-anim [data-reveal]:not(.is-revealed) .es-shop-line-how {
            opacity: 0;
            transform: translateY(4px);
        }
        /* The tally stamped at the foot of the sheet. */
        .es-shop-stamp {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.2rem 0.55rem;
            border-radius: 0.25rem;
            background: #14506e;
            color: #ffffff;
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        /* --- The session plan: one row per session in the series ------ */
        .es-shop-sess {
            display: grid;
            grid-template-columns: 2.4rem 1fr auto;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 0;
            border-top: 1px solid rgba(20, 26, 30, 0.1);
        }
        .dark .es-shop-sess { border-top-color: rgba(230, 237, 242, 0.1); }
        .es-shop-sess-skip { opacity: 0.62; }
        .es-shop-sess-skip .es-shop-sess-no {
            border-style: dashed;
            background: transparent;
        }
        .es-shop-sess-no {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 1.6rem;
            border: 1px solid rgba(20, 80, 110, 0.35);
            border-radius: 0.3rem;
            background: rgba(20, 80, 110, 0.08);
            color: #14506e;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.7rem;
            font-weight: 800;
        }
        .dark .es-shop-sess-no {
            border-color: rgba(140, 203, 240, 0.35);
            background: rgba(140, 203, 240, 0.1);
            color: #8ccbf0;
        }

        /* --- Table (the class card ledger) --------------------------- */
        .es-shop-table { min-width: 34rem; }
        .es-shop-tr { border-top: 1px solid rgba(20, 26, 30, 0.1); }
        .dark .es-shop-tr { border-top-color: rgba(230, 237, 242, 0.1); }

        /* --- Hairlines, wells, fine print, tooltips ------------------
               These carry their own colours rather than Tailwind
               arbitrary values, because an arbitrary utility that is not
               already in the compiled bundle renders as nothing at all. */
        .es-shop-hr { border-color: rgba(20, 26, 30, 0.1); }
        .dark .es-shop-hr { border-color: rgba(230, 237, 242, 0.1); }
        .es-shop-well {
            border: 1px solid rgba(20, 80, 110, 0.22);
            background: rgba(20, 80, 110, 0.06);
        }
        .dark .es-shop-well {
            border-color: rgba(140, 203, 240, 0.22);
            background: rgba(140, 203, 240, 0.07);
        }
        .es-shop-fine { font-size: 0.7rem; line-height: 1.55; }
        .es-shop-tip {
            border: 1px solid rgba(20, 26, 30, 0.12);
            background: #ffffff;
            color: #374151;
        }
        .dark .es-shop-tip {
            border-color: rgba(230, 237, 242, 0.12);
            background: #141a1e;
            color: #d1d5db;
        }

        /* --- Chips (the class-type marquee) -------------------------- */
        .es-shop-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.35rem 0.85rem;
            border: 1px solid rgba(20, 26, 30, 0.16);
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.7);
            color: #4b5560;
            font-size: 0.76rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .dark .es-shop-chip {
            border-color: rgba(230, 237, 242, 0.16);
            background: rgba(230, 237, 242, 0.05);
            color: #9aa8b2;
        }

        /* --- Links, buttons, hovers --------------------------------- */
        .es-shop-link { color: #14506e; }
        .es-shop-link:hover { color: #0f4560; }
        .dark .es-shop-link { color: #8ccbf0; }
        .dark .es-shop-link:hover { color: #e6edf2; }
        .es-shop-btn {
            background-color: #14506e;
            color: #ffffff;
            box-shadow: 0 18px 36px -16px rgba(20, 80, 110, 0.55);
        }
        .es-shop-btn:hover { background-color: #0f4560; }
        .dark .es-shop-btn { background-color: #8ccbf0; color: #0b1013; }
        .dark .es-shop-btn:hover { background-color: #a9daf5; }
        /* Always-lit button, for the wall, where light and dark are the same. */
        .es-shop-btn-lit {
            background-color: #8ccbf0;
            color: #0b1013;
            box-shadow: 0 18px 36px -16px rgba(140, 203, 240, 0.35);
        }
        .es-shop-btn-lit:hover { background-color: #a9daf5; }
        .es-shop-hover:hover { border-color: rgba(20, 80, 110, 0.45); }
        .dark .es-shop-hover:hover { border-color: rgba(140, 203, 240, 0.45); }
        .es-shop-hover:hover .es-shop-hover-title,
        .es-shop-hover:hover .es-shop-hover-arrow { color: #14506e; }
        .dark .es-shop-hover:hover .es-shop-hover-title,
        .dark .es-shop-hover:hover .es-shop-hover-arrow { color: #8ccbf0; }

        /* --- Shared-system recolours (brand blue by default) --------- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(20, 80, 110, 0.14), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(140, 203, 240, 0.12), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(20, 80, 110, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(140, 203, 240, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #14506e; }
        .dark .es-dot.is-active .es-dot-pip { background: #8ccbf0; }

        /* --- Focus rings. No border-radius: it would reshape the
               element on focus, and outlines follow the shape anyway. -- */
        #es-shop-page a:focus-visible,
        #es-shop-page summary:focus-visible,
        #es-shop-page input:focus-visible {
            outline: 2px solid #14506e;
            outline-offset: 3px;
        }
        .dark #es-shop-page a:focus-visible,
        .dark #es-shop-page summary:focus-visible,
        .dark #es-shop-page input:focus-visible {
            outline-color: #8ccbf0;
        }
        .es-shop-wall a:focus-visible,
        .es-shop-wall summary:focus-visible,
        .es-shop-wall input:focus-visible {
            outline-color: #8ccbf0 !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-shop-line-name, .es-shop-line-how {
                opacity: 1 !important;
                transform: none !important;
                transition: none !important;
            }
        }
    </style>

    @php
        // The sheet: eight ruled lines, six written on. The line number IS the
        // seat number, so capacity reads as the small whole number it is.
        $sheet = [
            [1, 'Maya R.', 'Paid'],
            [2, 'Tom A.', 'Paid'],
            [3, 'Priya S.', 'Class card'],
            [4, 'Dan K.', 'Paid'],
            [5, 'Lena M.', 'Class card'],
            [6, 'Ivo P.', 'Paid'],
            [7, null, null],
            [8, null, null],
        ];

        // One class, one recurring event, so every session carries the SAME name
        // and the same start time - the date is what changes. Session 04 keeps
        // its number across the skipped week: an excluded date removes the date,
        // it does not renumber the course.
        $sessions = [
            ['01', 'Sat 7 Feb', '10:00', 'Full', false],
            ['02', 'Sat 14 Feb', '10:00', '2 seats left', false],
            ['03', 'Sat 21 Feb', '10:00', '5 seats left', false],
            ['--', 'Sat 28 Feb', 'Date taken out', 'Skipped', true],
            ['04', 'Sat 7 Mar', '10:00', '8 seats left', false],
            ['05', 'Sat 14 Mar', '10:00', '8 seats left', false],
        ];

        // A ten-class card, four sessions in. Same class every time, because the
        // card spans one recurring event.
        $cardRows = [
            ['1', 'Sat 7 Feb', 'Pottery Fundamentals', 'Used', '9'],
            ['2', 'Sat 14 Feb', 'Pottery Fundamentals', 'Used', '8'],
            ['3', 'Sat 21 Feb', 'Pottery Fundamentals', 'Used', '7'],
            ['4', 'Sat 7 Mar', 'Pottery Fundamentals', 'Booked', '6'],
            ['5', 'Not booked yet', 'Any session left in the term', 'Open', '6'],
        ];

        $faqs = [
            [
                'q' => 'Is Event Schedule free for workshop instructors?',
                'a' => 'Yes. Publishing your classes, running one as a weekly series, capping the seats with free registration, sorting strands into sub-schedules, emailing the students who follow you and syncing two ways with Google, Outlook or CalDAV are all free forever. So is selling, up to 25 paid spots a month, and so is scanning the QR at the door on every plan. Lifting that ceiling, plus multi-class cards, custom questions at checkout and the live check-in screen, is the Pro plan at $'.$proMonthly.' a month, and Event Schedule charges zero platform fees on what you sell.',
            ],
            [
                'q' => 'Can I run different kinds of workshops on one schedule?',
                'a' => 'Yes. Sub-schedules keep cooking, pottery, photography and craft strands apart on one link, each with its own colour, and every class carries its own description, images, capacity and prices. To be straight about what a sub-schedule is: it sorts and colours, it does not hide. A class you have not announced yet is a draft, and drafts are what keep it off the public page until you say so.',
            ],
            [
                'q' => 'How do students find out about a new class?',
                'a' => 'They follow your schedule, and then you email them. Nothing goes out on its own: you write the newsletter and send it, and you get open and click rates afterwards. The allowance counts recipients rather than sends, at 10 a month on Free, 100 on Pro and 1,000 on Enterprise. Alongside that, share your one link, embed the calendar on the studio site you already have, and download your schedule\'s QR code to tape to the bench.',
            ],
            [
                'q' => 'Can I sell spots and cap the class?',
                'a' => 'Yes. Connect your own Stripe account and sell spots with named ticket types, each with its own price, quantity and sales window. The free plan sells 25 paid spots a month and Pro lifts the ceiling; free classes can use registration with a seat limit instead, unlimited on every plan. Either way the count is kept per session date, so a full Saturday does not close the next one, and students see the number of spots left rather than who is on the sheet.',
            ],
            [
                'q' => 'How does a multi-class card work?',
                'a' => 'A card is one purchase valid across the series, on the Pro plan. The holder gets a private link and books the session they want, and you can set how many people the card admits at each session. Give it a cancellation cutoff in hours before a session starts: cancel earlier than that and the seat goes back to the class, cancel later and the card\'s policy decides whether the visit is forfeited or the cancellation is blocked outright. Usage is tracked per session, so you can see which weeks a card holder actually turned up.',
            ],
            [
                'q' => 'What about a class that runs twice on the same day?',
                'a' => 'That is two events. One recurring event carries one start time, so a 10am beginners bench and a 2pm intermediate bench are two series, each with its own seat count and its own tickets. It is a little more setup and it keeps the two benches, and their money, properly apart.',
            ],
        ];

        $dotSections = [
            ['top', 'The sheet'],
            ['unit', 'The unit'],
            ['plan', 'The series'],
            ['sheet', 'Two sides'],
            ['card', 'The class card'],
            ['money', 'Getting paid'],
            ['after', 'After class'],
            ['rest', 'Everything else'],
            ['who', 'Perfect for'],
            ['faq', 'Questions'],
            ['claim', 'Open the sheet'],
        ];
    @endphp

    <div id="es-shop-page" class="es-shop-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the sheet pinned above the bench                    -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(20, 80, 110, 0.22), rgba(20, 80, 110, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(140, 203, 240, 0.2), rgba(140, 203, 240, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="h-5 w-5 es-shop-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.247m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.247" />
                        </svg>
                        <span class="es-shop-muted text-sm font-medium tracking-wide">For workshop and class instructors</span>
                    </div>

                    <h1 class="es-balance es-shop-ink mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">One class. Ten Saturdays.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="es-shop-accent">Eight</span> seats each.</span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-shop-muted mb-10 max-w-xl text-lg sm:text-xl">
                        Set the class up once as a series, cap the bench, and let the sheet fill itself. The seat count is kept per session, so a full Saturday never closes the next one.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="#plan" class="glass group inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            How a class is set up
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="es-shop-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Create your schedule
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- THE SHEET. Ruled lines are seats. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-shop-sheet">
                        <div class="es-shop-holes" aria-hidden="true"><i></i><i></i><i></i></div>
                        <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                            <h2 class="es-shop-sheet-ink text-base font-bold">Wheel Throwing &middot; Beginners</h2>
                            <span class="es-shop-sheet-muted es-shop-fig text-xs">Sat 14 Feb &middot; 10:00</span>
                        </div>
                        <p class="es-shop-sheet-muted mb-4 border-b es-shop-sheet-rule pb-3 text-xs">Bench of eight &middot; session 02 of 10</p>

                        <div>
                            @foreach ($sheet as $i => [$no, $who, $how])
                                <div class="es-shop-line @if (! $who) es-shop-line-open @endif">
                                    <span class="es-shop-line-no">{{ $no }}</span>
                                    <span class="es-shop-line-name es-shop-sheet-ink" style="--i: {{ $i }};">{{ $who ?: 'open' }}</span>
                                    @if ($how)
                                        <span class="es-shop-line-how" style="--i: {{ $i }};">{{ $how }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
                            <span class="es-shop-stamp">6 of 8 taken</span>
                            <span class="es-shop-sheet-accent es-shop-fig text-xs font-bold">2 seats left</span>
                        </div>
                        <p class="es-shop-sheet-muted es-shop-fine mt-3">
                            Your side of the sheet. Students see the number, never the names.
                        </p>
                    </div>
                </div>
            </div>

            <!-- What people teach -->
            <div class="es-fade-up es-d-4 mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach (['Cooking', 'Pottery', 'Photography', 'Woodworking', 'Painting', 'Music lessons', 'Sewing', 'Coding', 'Printmaking', 'Bread'] as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-shop-chip">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The unit is the bench (the wall: fixed dark)               -->
    <!-- ============================================================ -->
    <section id="unit" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-shop-wall noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-shop-peg absolute inset-0"></div>
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-shop-num mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                    <p class="es-shop-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The unit</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight es-shop-wall-ink md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Most calendars think a class is <span class="es-shop-lit">one date.</span>
                    </h2>
                    <p class="es-shop-wall-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        It is a bench, run again next week, with the seats counted separately every time.
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    <div class="es-shop-card p-6" data-reveal="panel">
                        <p class="es-shop-tag mb-3">The term</p>
                        <h3 class="mb-2 text-lg font-bold es-shop-wall-ink">
                            <span class="es-shop-fig" data-count-to="10">10</span> sessions
                        </h3>
                        <p class="text-sm es-shop-wall-muted">One event, a weekly pattern, ending after ten. Typing ten classes in by hand is ten chances to fat-finger a start time.</p>
                    </div>
                    <div class="es-shop-card p-6" data-reveal="panel">
                        <p class="es-shop-tag mb-3">The bench</p>
                        <h3 class="mb-2 text-lg font-bold es-shop-wall-ink">
                            <span class="es-shop-fig" data-count-to="8">8</span> seats, per session
                        </h3>
                        <p class="text-sm es-shop-wall-muted">The cap is held per date, not per class. Fill this Saturday and the next one is still wide open, with its own count.</p>
                    </div>
                    <div class="es-shop-card p-6" data-reveal="panel">
                        <p class="es-shop-tag mb-3">The link</p>
                        <h3 class="mb-2 text-lg font-bold es-shop-wall-ink">1 address</h3>
                        <p class="text-sm es-shop-wall-muted">Students see the whole term at one link, book the week that suits them, and download that date to their own calendar.</p>
                    </div>
                </div>

                <p class="mt-10 text-center es-shop-wall-muted" data-reveal>
                    The bench is the unit. Everything below hangs off it.
                    <a href="#plan" class="es-shop-lit inline-flex items-center gap-1 font-semibold transition-all hover:gap-2">
                        Set one up
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Setting the series                                        -->
    <!-- ============================================================ -->
    <section id="plan" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-shop-num mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                <p class="es-shop-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Setting the series</p>
                <h2 class="es-balance es-shop-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    The day, the weeks off, and <span class="es-shop-accent">the last one.</span>
                </h2>
                <p class="es-shop-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Three settings turn one event into a whole term, and all three are on the free plan.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-3" data-reveal-group="100">
                <div class="es-shop-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-shop-ink text-lg font-bold">The day it runs</h3>
                        <span class="es-shop-plan">Free</span>
                    </div>
                    <p class="es-shop-muted text-sm">Pick the days of the week and the start time, or repeat every second week, monthly, or once a year. Saturdays is one entry, not ten.</p>
                </div>
                <div class="es-shop-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-shop-ink text-lg font-bold">The weeks you are closed</h3>
                        <span class="es-shop-plan">Free</span>
                    </div>
                    <p class="es-shop-muted text-sm">Take single dates out and add one-off dates in. A closed studio or a kiln repair does not mean rebuilding the term.</p>
                </div>
                <div class="es-shop-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-shop-ink text-lg font-bold">The last session</h3>
                        <span class="es-shop-plan">Free</span>
                    </div>
                    <p class="es-shop-muted text-sm">End on a date, or after a set number of sessions. This is the setting that makes a term a term instead of a weekly class that runs forever.</p>
                </div>
            </div>

            <!-- The session plan: numbers survive the skipped week -->
            <div class="mt-10 es-shop-card p-6 sm:p-8" data-reveal="panel">
                <div class="mb-4 flex flex-wrap items-baseline justify-between gap-2">
                    <h3 class="es-shop-ink text-lg font-bold">Pottery Fundamentals</h3>
                    <span class="es-shop-muted es-shop-fig text-xs">One recurring event &middot; Saturdays 10:00 &middot; ends after 10</span>
                </div>
                <div>
                    @foreach ($sessions as [$sNo, $sDate, $sNote, $sSeats, $sSkip])
                        <div class="es-shop-sess @if ($sSkip) es-shop-sess-skip @endif">
                            <span class="es-shop-sess-no">{{ $sNo }}</span>
                            <span class="min-w-0">
                                <span class="es-shop-ink es-shop-fig block truncate text-sm font-semibold">{{ $sDate }}</span>
                                <span class="es-shop-muted block text-xs">{{ $sNote }}</span>
                            </span>
                            <span class="es-shop-muted es-shop-fig text-xs font-bold">{{ $sSeats }}</span>
                        </div>
                    @endforeach
                </div>
                <p class="es-shop-muted es-shop-hr mt-5 border-t pt-4 text-xs">
                    The skipped week does not use up a session. A date you take out comes off the count as well, so a term set to end after ten still teaches ten. Change the start time once and every remaining session follows it.
                </p>
            </div>

            <p class="es-shop-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                A class that runs twice on the same day is two events, because one recurring event carries one start time. Two benches, two seat counts, no argument about which one somebody booked.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Two sides of the same sheet (duplex)                      -->
    <!-- ============================================================ -->
    <section id="sheet" class="scroll-mt-24 es-shop-hr border-y py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-shop-num mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                <p class="es-shop-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Two sides</p>
                <h2 class="es-balance es-shop-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    You see the sheet. They see <span class="es-shop-accent">the count.</span>
                </h2>
                <p class="es-shop-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Names and email addresses belong to you. The public page shows spots remaining and nothing else: no roster, no addresses, not ever.
                </p>
            </div>

            <div class="grid items-start gap-8 lg:grid-cols-2">
                <!-- Your side -->
                <div data-reveal="panel">
                    <p class="es-shop-tag mb-3">Signed in as the instructor</p>
                    <div class="es-shop-sheet">
                        <div class="es-shop-holes" aria-hidden="true"><i></i><i></i><i></i></div>
                        <div class="mb-4 flex flex-wrap items-baseline justify-between gap-2 border-b es-shop-sheet-rule pb-3">
                            <h3 class="es-shop-sheet-ink text-sm font-bold">Sat 14 Feb &middot; 10:00</h3>
                            <span class="es-shop-sheet-muted es-shop-fig text-xs">6 of 8</span>
                        </div>
                        <div>
                            @foreach ($sheet as $i => [$no, $who, $how])
                                <div class="es-shop-line @if (! $who) es-shop-line-open @endif">
                                    <span class="es-shop-line-no">{{ $no }}</span>
                                    <span class="es-shop-line-name es-shop-sheet-ink" style="--i: {{ $i }};">{{ $who ?: 'open' }}</span>
                                    @if ($how)
                                        <span class="es-shop-line-how" style="--i: {{ $i }};">{{ $how }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <p class="es-shop-sheet-muted es-shop-fine mt-4">
                            Every name arrived with an email address, from a sale or from a free registration. Sales export to a CSV on the Pro plan, when you want the list somewhere else.
                        </p>
                    </div>
                </div>

                <!-- Their side -->
                <div data-reveal="panel">
                    <p class="es-shop-tag mb-3">What a student sees</p>
                    <div class="es-shop-card p-6 sm:p-7">
                        <h3 class="es-shop-ink text-lg font-bold">Wheel Throwing &middot; Beginners</h3>
                        <p class="es-shop-muted es-shop-fig mt-1 text-xs">Sat 14 Feb &middot; 10:00 &middot; Clay Lane Studio</p>
                        <p class="es-shop-accent es-shop-fig mt-5 text-2xl font-black">2 spots remaining</p>
                        <div class="es-shop-well mt-4 rounded-xl p-4">
                            <p class="es-shop-ink text-sm font-semibold">Register</p>
                            <p class="es-shop-muted mt-1 text-xs">Name and email, or a card if the class is paid. That is the whole form, unless you have added a question of your own.</p>
                        </div>
                        <ul class="mt-5 space-y-3" data-reveal-group="70">
                            <li class="flex gap-3" data-reveal>
                                <svg aria-hidden="true" class="es-shop-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-shop-muted text-sm">The number comes off the same count you set, so it is right the moment somebody books.</span>
                            </li>
                            <li class="flex gap-3" data-reveal>
                                <svg aria-hidden="true" class="es-shop-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-shop-muted text-sm">When the session is full, the form becomes a waitlist for that date instead of a dead end: free on registration, Pro once the spots are paid tickets.</span>
                            </li>
                            <li class="flex gap-3" data-reveal>
                                <svg aria-hidden="true" class="es-shop-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-shop-muted text-sm">Free registration with a seat limit is unlimited on every plan, and the free plan sells 25 paid spots a month on top of that.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. The class card (a real record: the punch ledger)          -->
    <!-- ============================================================ -->
    <section id="card" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-shop-num mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                <p class="es-shop-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The class card</p>
                <h2 class="es-balance es-shop-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    Ten classes, <span class="es-shop-accent">one purchase.</span>
                </h2>
                <p class="es-shop-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Because the term is one recurring event, a card can span it. The holder gets a private link, books the week that suits them, and the card keeps its own ledger.
                </p>
            </div>

            <div class="es-shop-card p-6 sm:p-8" data-reveal="panel">
                <div class="mb-5 flex flex-wrap items-center gap-2">
                    <h3 class="es-shop-ink text-lg font-bold">Ten-class card &middot; Priya S.</h3>
                    <span class="es-shop-plan es-shop-plan-pro">Pro</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="es-shop-table w-full border-collapse text-left">
                        <caption class="sr-only">A ten-class card, four sessions in: each booking with its date, the class it covers, whether it was used, and the number of classes still on the card</caption>
                        <thead>
                            <tr class="es-shop-tag">
                                <th scope="col" class="pb-3 font-bold">Use</th>
                                <th scope="col" class="pb-3 font-bold">Date</th>
                                <th scope="col" class="pb-3 font-bold">Class</th>
                                <th scope="col" class="pb-3 font-bold">Status</th>
                                <th scope="col" class="pb-3 text-right font-bold">Left</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cardRows as [$cNo, $cDate, $cClass, $cStatus, $cLeft])
                                <tr class="es-shop-tr">
                                    <th scope="row" class="es-shop-ink es-shop-fig py-3 pe-3 align-middle text-sm font-bold">{{ $cNo }}</th>
                                    <td class="es-shop-muted es-shop-fig py-3 pe-3 align-middle text-xs">{{ $cDate }}</td>
                                    <td class="es-shop-ink py-3 pe-3 align-middle text-sm">{{ $cClass }}</td>
                                    <td class="py-3 pe-3 align-middle">
                                        <span class="@if ($cStatus === 'Open') es-shop-muted @else es-shop-accent @endif es-shop-fig text-xs font-bold uppercase tracking-wider">{{ $cStatus }}</span>
                                    </td>
                                    <td class="es-shop-ink es-shop-fig py-3 text-right align-middle text-sm font-bold">{{ $cLeft }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <p class="es-shop-muted mt-4 text-xs">Usage is tracked per session, so you can see which weeks a card holder actually turned up to.</p>
            </div>

            <div class="mt-8 grid gap-6 md:grid-cols-3" data-reveal-group="100">
                <div class="es-shop-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-shop-ink text-lg font-bold">A cutoff you set</h3>
                        <span class="es-shop-plan es-shop-plan-pro">Pro</span>
                    </div>
                    <p class="es-shop-muted text-sm">Give the card a cancellation cutoff in hours before a session starts. Cancel earlier than that and the seat goes back to the bench, and the waitlist for that date hears about it.</p>
                </div>
                <div class="es-shop-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-shop-ink text-lg font-bold">A policy for late notice</h3>
                        <span class="es-shop-plan es-shop-plan-pro">Pro</span>
                    </div>
                    <p class="es-shop-muted text-sm">Past the cutoff, the card decides: forfeit the class, or block the cancellation outright. Either way it is your rule, written down before anybody argues it.</p>
                </div>
                <div class="es-shop-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-shop-ink text-lg font-bold">More than one seat</h3>
                        <span class="es-shop-plan es-shop-plan-pro">Pro</span>
                    </div>
                    <p class="es-shop-muted text-sm">Set how many people a card admits at each session, so a parent-and-child card takes two places off the bench rather than one.</p>
                </div>
            </div>

            <p class="es-shop-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                Cards are sold alongside single spots, not instead of them. Somebody who wants one Saturday can still just buy one Saturday.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Getting paid                                              -->
    <!-- ============================================================ -->
    <section id="money" class="scroll-mt-24 es-shop-hr border-t py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-shop-num mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                <p class="es-shop-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Getting paid</p>
                <h2 class="es-balance es-shop-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    What the bench earns is <span class="es-shop-accent">what you keep.</span>
                </h2>
                <p class="es-shop-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Spots are sold through your own Stripe account. Event Schedule charges zero platform fees on every plan, so past Stripe's own processing the money is yours. The free plan sells 25 paid spots a month; Pro takes the ceiling off.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-3" data-reveal-group="100">
                <div class="es-shop-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-shop-ink text-lg font-bold">Prices, plainly</h3>
                        <span class="es-shop-plan">Free</span>
                    </div>
                    <p class="es-shop-muted text-sm">Named ticket types, each with its own price, quantity and sales window: early bird, concession, materials included. A cheaper rate for booking several at once comes with them. Discount codes are Pro.</p>
                </div>
                <div class="es-shop-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-shop-ink text-lg font-bold">Ask before they arrive</h3>
                        <span class="es-shop-plan es-shop-plan-pro">Pro</span>
                    </div>
                    <p class="es-shop-muted text-sm">Custom questions collect what the class actually needs: apron size, dietary needs, whether they are bringing their own camera or borrowing yours.</p>
                </div>
                <div class="es-shop-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-shop-ink text-lg font-bold">A class as a present</h3>
                        <span class="es-shop-plan es-shop-plan-pro">Pro</span>
                    </div>
                    <p class="es-shop-muted text-sm">Sell a gift card that somebody sends to a recipient by email, with its balance redeemed toward any class on your schedule.</p>
                </div>
            </div>

            <p class="es-shop-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                Teaching for free, or asking for the money in the room? Registration with a seat limit needs none of this and is on the free plan.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. After the class (the wall again)                          -->
    <!-- ============================================================ -->
    <section id="after" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-shop-wall noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-shop-peg absolute inset-0"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-shop-num mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                    <p class="es-shop-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">After class</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight es-shop-wall-ink md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        The bit that fills <span class="es-shop-lit">next term.</span>
                    </h2>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="100">
                    <div class="es-shop-card p-6" data-reveal="panel">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-bold es-shop-wall-ink">The code on the bench</h3>
                            <span class="es-shop-plan">Free</span>
                        </div>
                        <p class="text-sm es-shop-wall-muted">Download your schedule's QR code, print it, and tape it to the bench. People who liked the class scan it on the way out and follow you.</p>
                    </div>
                    <div class="es-shop-card p-6" data-reveal="panel">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-bold es-shop-wall-ink">What they made</h3>
                            <span class="es-shop-plan">Free</span>
                        </div>
                        <p class="text-sm es-shop-wall-muted">Students add photos, video and comments to the class they came to, and nothing appears until you approve it. Free covers 25 photos per schedule.</p>
                    </div>
                    <div class="es-shop-card p-6" data-reveal="panel">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-bold es-shop-wall-ink">Whether it landed</h3>
                            <span class="es-shop-plan es-shop-plan-pro">Pro</span>
                        </div>
                        <p class="text-sm es-shop-wall-muted">Collect star ratings and written comments from the people who attended, so the second run of a class is better than the first.</p>
                    </div>
                </div>

                <p class="mt-10 text-center es-shop-wall-muted" data-reveal>
                    Being straight about following: it is a mailing list, not a notification robot. Nobody is told automatically when you add a class. You write the newsletter and you send it, and the allowance counts recipients: 10 a month on Free, 100 on Pro, 1,000 on Enterprise.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Everything else: bento                                    -->
    <!-- ============================================================ -->
    <section id="rest" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-shop-num mb-6" data-reveal aria-hidden="true"><span>08</span></div>
                <p class="es-shop-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Everything else</p>
                <h2 class="es-balance es-shop-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Between one Saturday and the next.
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">
                <!-- 1 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-shop-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-shop-ink text-xl font-bold">Tell the students you already have</h3>
                                <span class="es-shop-plan">Free</span>
                            </div>
                            <p class="es-shop-muted mb-4">Everyone who found you from the bench, the link or the embed and then followed your schedule is a mailing list you own. Write a newsletter when the next term goes up, and read the open and click rates afterwards.</p>
                            <p class="es-shop-muted text-sm">The number worth knowing: the allowance counts recipients, not sends, so one email to 40 students is 40 of the month's allowance.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-shop-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-shop-ink text-xl font-bold">When a session fills</h3>
                                <span class="es-shop-plan">Free</span>
                            </div>
                            <p class="es-shop-muted">A full session turns its sign-up into a waitlist for that date rather than a dead end. Cancel a booking and the freed seat is offered to the first person waiting on that date, and if they do not take it the offer passes to the next one. Free when the class takes registrations; on paid tickets the waitlist is Pro.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-shop-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-shop-ink text-xl font-bold">Beginners and advanced</h3>
                                <span class="es-shop-plan">Free</span>
                            </div>
                            <p class="es-shop-muted">Sub-schedules keep the strands apart on one link, each with its own colour. They sort and they colour; hiding a class until you announce it is what a draft is for.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-shop-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-shop-ink text-xl font-bold">On the studio site you already have</h3>
                                <span class="es-shop-plan">Free</span>
                            </div>
                            <p class="es-shop-muted mb-4">Embed the calendar on your own site so the term lives where people look you up, and sync two ways with Google, Outlook and CalDAV. A recurring class crosses as a single entry rather than ten, so the term itself stays here where the seats are counted.</p>
                            <p class="es-shop-muted text-sm">Built-in analytics show page views, the devices people are on and where the traffic came from. That is what they measure, and nothing more.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-shop-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-shop-ink text-xl font-bold">Next term, faster</h3>
                                <span class="es-shop-plan">Free</span>
                            </div>
                            <p class="es-shop-muted">Clone a class you have run before and change the dates. Keep it as a draft while you think about it, and publish when the term is settled.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-shop-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-shop-ink text-xl font-bold">The running order of one class</h3>
                                <span class="es-shop-plan">Free</span>
                            </div>
                            <p class="es-shop-muted mb-4">Break a session into its parts with times, so people know the three hours are wedging, centering, pulling and a clean-up rather than a mystery. It shows on the class page as an agenda.</p>
                            <p class="es-shop-muted text-sm">
                                Already written the blurb somewhere else? Paste the text or drop in the flyer and the details are pulled out for you to check, on every plan.
                                <a href="{{ marketing_url('/features/ai') }}" class="es-shop-link font-medium hover:underline">How the import works</a>
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
    <!-- 9. Perfect for                                               -->
    <!-- ============================================================ -->
    <section id="who" class="scroll-mt-24 es-shop-hr border-t py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-shop-num mb-6" data-reveal aria-hidden="true"><span>09</span></div>
                <h2 class="es-balance es-shop-ink mb-4 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    Perfect for all <span class="es-shop-accent">workshop instructors</span>
                </h2>
                <p class="es-shop-muted text-lg sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Six seats or thirty, a bench is a bench.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <x-sub-audience-card
                    name="Cooking Class Instructors"
                    description="From pasta making to pastry arts. Cap the kitchen per session, ask about dietary needs at checkout, and build a following of food lovers."
                    icon-color="sky"
                    blog-slug="for-cooking-class-instructors"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Pottery & Ceramics Teachers"
                    description="Wheel throwing, hand building and glazing. Run the term as one series, hold the wheels to the number you actually have, and sell a ten-class card."
                    icon-color="blue"
                    blog-slug="for-pottery-ceramics-teachers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Photography Workshop Leaders"
                    description="Photo walks, studio sessions and editing evenings. Ask what gear they are bringing at checkout, and take a washed-out date out of the series."
                    icon-color="blue"
                    blog-slug="for-photography-workshop-leaders"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Craft & Maker Instructors"
                    description="Woodworking, metalwork, sewing and beyond. Put the materials list in the class description and hold the bench to the number of vices on it."
                    icon-color="amber"
                    blog-slug="for-craft-maker-instructors"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Art Teachers"
                    description="Painting, drawing and mixed media. Let students post what they made to the class page, all held for your approval first."
                    icon-color="cyan"
                    blog-slug="for-art-teachers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Music Lesson Instructors"
                    description="Group lessons, masterclasses and jam sessions. Run the term as one weekly series and sell a card that covers the whole thing."
                    icon-color="teal"
                    blog-slug="for-music-lesson-instructors"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. Three steps                                              -->
    <!-- ============================================================ -->
    <section class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance es-shop-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal>
                    Three steps
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="120">
                @foreach ([['01', 'Set the class up as a series', 'Create the class once as a recurring event, pick the day it runs, and give it an end: a last date, or a number of sessions.'], ['02', 'Cap the bench', 'Set the seat limit. It is counted per session date, so a full Saturday does not close the next one. Skip the weeks the studio is closed.'], ['03', 'Open the sheet', 'Share one link. Take free registrations, or connect Stripe and sell spots and class cards with zero platform fees.']] as [$stepNum, $stepTitle, $stepBody])
                    <div class="es-shop-card p-7" data-reveal="panel">
                        <div class="es-shop-accent es-shop-fig mb-3 text-2xl font-black">{{ $stepNum }}</div>
                        <h3 class="es-shop-ink mb-2 text-lg font-bold">{{ $stepTitle }}</h3>
                        <p class="es-shop-muted text-sm leading-relaxed">{{ $stepBody }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 11. Key features                                             -->
    <!-- ============================================================ -->
    <section class="es-shop-hr border-t py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-shop-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="One class, a weekly pattern, ending after a set number of sessions" :url="marketing_url('/features/recurring-events')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="Named ticket types, class cards, QR check-in and zero platform fees" :url="marketing_url('/features/ticketing')" icon-color="blue">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Email the students who follow you, with open and click rates" :url="marketing_url('/features/newsletters')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Calendar Sync" description="Two-way sync with Google, Outlook and CalDAV" :url="marketing_url('/features/calendar-sync')" icon-color="teal">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-shop-link inline-flex items-center font-medium hover:underline">
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
    <section class="es-shop-hr border-t py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-shop-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4" data-reveal-group="70">
                @foreach ([['/for-online-classes', 'Online Classes'], ['/for-fitness-and-yoga', 'Fitness & Yoga'], ['/for-community-centers', 'Community Centers'], ['/for-libraries', 'Libraries']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" class="es-shop-hover es-shop-card group flex flex-col p-5 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-shop-hover-title es-shop-ink mb-3 text-sm font-semibold transition-colors">For {{ $relName }}</span>
                        <span class="es-shop-hover-arrow es-shop-muted mt-auto inline-flex items-center gap-1 text-xs font-medium transition-colors">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
            <div class="mt-8 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-shop-link inline-flex items-center font-medium hover:underline">
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
                <div class="es-shop-num mb-6" data-reveal aria-hidden="true"><span>10</span></div>
                <h2 class="es-balance es-shop-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-shop-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    What instructors ask before they move a term across.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-shop-hover es-shop-card group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-shop-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-shop-accent es-shop-fig flex-none text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-shop-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-shop-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-shop-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
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
            <div class="es-shop-wall noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-shop-peg absolute inset-0"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>
                <div class="relative z-10">
                    <p class="es-shop-tag mb-4">Free forever</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight es-shop-wall-ink md:text-5xl">
                        Pin up the sheet. <span class="es-shop-lit">Fill the bench.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg es-shop-wall-muted">
                        Publishing your classes, capping the seats and emailing the students who follow you are free forever, and so are the first 25 paid spots a month. Unlimited sales and class cards are ${{ $proMonthly }} a month, and nothing is taken off the top.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-workshop" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-400 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm es-shop-wall-muted sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="es-shop-btn-lit group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Create your schedule
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="mt-6 text-sm es-shop-wall-muted">No credit card required</p>
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
                        <span class="es-shop-tip pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
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
