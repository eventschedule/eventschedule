<x-marketing-layout>
    <x-slot name="title">Availability | Cross Out the Dates You Cannot Work</x-slot>
    <x-slot name="description">On a talent schedule, mark whole dates as unavailable and your team sees who is out on the shared calendar. Private to signed-in members, never published to guests. Enterprise plan.</x-slot>
    <x-slot name="breadcrumbTitle">Availability</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule Availability Management",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Scheduling Software",
        "operatingSystem": "Web",
        "description": "Mark whole dates as unavailable on a talent schedule. Each team member keeps their own dates, and the shared Schedule tab shows who is out on which day. Availability is never shown publicly.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Availability management is an Enterprise plan feature on the hosted platform and is included on selfhosted deployments"
        },
        "featureList": [
            "Month calendar where every date starts available",
            "Click a date to mark it unavailable, click again to clear it",
            "Whole dates only, with no times or reasons stored",
            "One set of dates per team member, editable only by that member",
            "Crossed dates surface on the shared Schedule tab with the names of who is out",
            "Never shown on the public schedule page",
            "Talent schedules on the Enterprise plan"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to mark the dates you cannot be booked",
        "description": "Cross out the days you are gone on a talent schedule so the rest of your team can see them.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Open the Availability tab",
                "text": "On a talent schedule with the Enterprise plan, open the Availability tab. It is a month calendar, and every date on it starts available."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Click the days you are gone",
                "text": "Click a date to mark it unavailable and click it again to clear it. Use the month arrows to mark dates further ahead."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Save",
                "text": "Save writes your dates against your own membership of the schedule and returns you to the month you were on. Your crossed days then appear for your team on the Schedule tab."
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
           Availability "Office Hours" styles.

           CONCEPT: THE CARD BY THE DOOR. Not the brass plate in the
           window that tells the street when you are open - the card on
           the INSIDE of the door, where the only marks are the days you
           are gone, and the only people who read it are the ones with a
           key. That is exactly the shape of this feature:
           role_user.dates_unavailable is a JSON array of Y-m-d strings,
           so a day carries a date and nothing else; the marks are per
           MEMBER (RoleController::availability writes the signed-in
           user's pivot row only); and they surface on the owner-facing
           Schedule tab, never on the guest page. Metaphor and feature
           story are one sentence: you cross out, you do not publish.

           THE SIGNATURE DEVICE IS A CROSSED MONTH, AND IT IS A REAL
           <table>. The product's Availability tab genuinely is a month
           grid you click, so a month is the honest picture rather than
           an invented one, and a calendar is tabular data. What makes
           it this page's and no other page's is the INVERSION: the
           marked cells are the ones with ink in them, the untouched
           cells are the quiet ones, and the card's footer counts the
           days you are gone rather than the days you are free.

           DELIBERATELY NOT A SLOT PICKER, A WEEKLY HOURS TABLE OR A
           TIME AXIS. /features/appointments owns bookable time slots
           and weekly hours; drawing an hour rail here would teach the
           product a thing this tab does not have (there are no times in
           dates_unavailable, and no half days). Sections 08 and 09 say
           so in words rather than leaving the drawing to imply it.

           COLOUR: the page keeps its inherited teal, but spends it as
           INK rather than as a glowing cyan-to-teal gradient. Deep
           #0d5b4e on the light ground (7.34) and #5eead4 in the dark.
           Nearest sibling is /features/team-scheduling ("The Lineup"),
           which also draws card stock in the cyan-teal family: it is
           held apart on purpose. That page's identity is a ruled
           lineup card with two-letter position codes and hollow slots;
           this one's is a hatched month cell, a tracked door plate and
           a monospace date slip. Do not import position codes, hollow
           numbered slots or hairline-ruled rows from it.

           THE AMBER inside the section 04 mock is not a page accent: it
           is the product's own colour for a day where somebody is out
           (calendar.blade.php uses bg-orange-50 / orange-900/30), so
           the mock is faithful rather than fashionable. It appears in
           that one panel and nowhere else.

           NEVER text-gray-500 here: #6b7280 measures 4.42 on this
           page's #f2f6f4 ground. Use .es-hours-muted (7.04 on the
           ground, 7.67 on a white card).

           BLADE RULE for this block: no @supports probes - a "#" hex
           inside a parenthesised at-rule condition breaks Blade
           compilation of every later parenthesised directive.
           ============================================================== */

        /* --- Ground and ink ------------------------------------------ */
        .es-hours-page { background-color: #f2f6f4; color: #101a17; }
        .dark .es-hours-page { background-color: #08120f; color: #e9f3f0; }
        .es-hours-ink { color: #101a17; }
        .dark .es-hours-ink { color: #e9f3f0; }
        .es-hours-muted { color: #495652; }
        .dark .es-hours-muted { color: #97a8a3; }
        .es-hours-accent { color: #0d5b4e; }
        .dark .es-hours-accent { color: #5eead4; }
        /* Always-lit accent, for the bands that stay dark in both modes. */
        .es-hours-lit { color: #5eead4; }
        /* Ink and muted ink for the fixed-dark band, in both colour modes. Real
           rules rather than text-[#e9f3f0] utilities: arbitrary Tailwind values
           are not in the built marketing CSS, so a utility like that renders as
           inherited dark ink on a dark band. Measured 16.43 and 7.48 on #0a1512. */
        .es-hours-band-ink { color: #e9f3f0; }
        .es-hours-band-muted { color: #97a8a3; }
        /* Icon strokes that follow the accent. */
        .es-hours-icon { color: #0d5b4e; }
        .dark .es-hours-icon { color: #5eead4; }

        /* --- Hairline rule ------------------------------------------- */
        .es-hours-rule { border-top: 1px solid rgba(16, 26, 23, 0.12); }
        .dark .es-hours-rule { border-top-color: rgba(233, 243, 240, 0.13); }

        /* --- Cards --------------------------------------------------- */
        .es-hours-card {
            border: 1px solid rgba(16, 26, 23, 0.12);
            border-radius: 1rem;
            background: #ffffff;
        }
        .dark .es-hours-card {
            border-color: rgba(233, 243, 240, 0.12);
            background: rgba(233, 243, 240, 0.045);
        }
        .es-hours-band .es-hours-card {
            border-color: rgba(233, 243, 240, 0.14);
            background: rgba(233, 243, 240, 0.05);
        }

        /* --- The fixed-dark band: the corridor outside the door ------- */
        .es-hours-band {
            background-color: #0a1512;
            background-image: radial-gradient(120% 100% at 50% 0%, #12211c 0%, #0d1815 55%, #060d0b 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(233, 243, 240, 0.05);
        }
        /* Shared classes that flip with the colour mode. Pinned so the band
           renders identically with .dark on and off. */
        .es-hours-band .grid-overlay {
            background-image:
                linear-gradient(rgba(233, 243, 240, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(233, 243, 240, 0.05) 1px, transparent 1px);
        }
        .es-hours-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-hours-band .es-claim:focus-within {
            border-color: rgba(94, 234, 212, 0.75);
            box-shadow: 0 0 0 4px rgba(94, 234, 212, 0.22);
        }

        /* --- The door plate: tracked uppercase eyebrow ---------------- */
        .es-hours-plate {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.3em;
            text-transform: uppercase;
            color: #495652;
        }
        .dark .es-hours-plate { color: #97a8a3; }
        .es-hours-band .es-hours-plate { color: #5eead4; }

        /* --- Section numeral ----------------------------------------- */
        .es-hours-num {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.3rem 0.8rem;
            border-radius: 0.3rem;
            border: 1px solid rgba(16, 26, 23, 0.18);
            background: #ffffff;
            color: #101a17;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-weight: 800;
            letter-spacing: 0.08em;
        }
        .dark .es-hours-num { border-color: rgba(233, 243, 240, 0.2); background: rgba(233, 243, 240, 0.05); color: #e9f3f0; }
        .es-hours-band .es-hours-num { border-color: rgba(233, 243, 240, 0.2); background: rgba(233, 243, 240, 0.05); color: #e9f3f0; }
        .es-hours-num::before {
            content: "";
            width: 2px;
            align-self: stretch;
            border-radius: 1px;
            background: #0d5b4e;
        }
        .dark .es-hours-num::before { background: #5eead4; }
        .es-hours-band .es-hours-num::before { background: #5eead4; }

        /* --- The card by the door: a month as a table ----------------- */
        .es-hours-cal {
            width: 100%;
            border-collapse: separate;
            border-spacing: 3px;
            table-layout: fixed;
        }
        .es-hours-cal th {
            padding-bottom: 0.35rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #495652;
        }
        .dark .es-hours-cal th { color: #97a8a3; }
        .es-hours-day {
            position: relative;
            height: 2.65rem;
            border-radius: 0.35rem;
            vertical-align: top;
            background: rgba(16, 26, 23, 0.04);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.72rem;
            font-weight: 600;
            color: #495652;
            padding: 0.25rem 0 0 0.3rem;
            text-align: left;
        }
        .dark .es-hours-day { background: rgba(233, 243, 240, 0.05); color: #97a8a3; }
        /* An untouched date. Available is the default state of the product,
           so it is also the default state of this cell. */
        .es-hours-day-blank { background: transparent; }
        /* A crossed date: ink in the cell, a hatch, and a stroke through it. */
        .es-hours-day-out {
            background-color: #e7efed;
            background-image: repeating-linear-gradient(135deg, rgba(13, 91, 78, 0.16) 0 2px, rgba(13, 91, 78, 0) 2px 7px);
            color: #0d5b4e;
            box-shadow: inset 0 0 0 1px rgba(13, 91, 78, 0.35);
        }
        .dark .es-hours-day-out {
            background-color: #1d3933;
            background-image: repeating-linear-gradient(135deg, rgba(94, 234, 212, 0.16) 0 2px, rgba(94, 234, 212, 0) 2px 7px);
            color: #5eead4;
            box-shadow: inset 0 0 0 1px rgba(94, 234, 212, 0.4);
        }
        .es-hours-day-out::after {
            content: "";
            position: absolute;
            left: 12%;
            right: 12%;
            top: 50%;
            height: 1px;
            transform: rotate(-24deg);
            transform-origin: center;
            background: rgba(13, 91, 78, 0.55);
        }
        .dark .es-hours-day-out::after { background: rgba(94, 234, 212, 0.6); }
        /* The micro tag the product writes into a marked cell. */
        .es-hours-outtag {
            position: absolute;
            left: 0.3rem;
            bottom: 0.15rem;
            font-size: 0.5rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        /* Ink the marks in when the card reveals. The undrawn pre-state lives
           behind BOTH gates - the es-anim class and a motion preference - so a
           no-JS visitor and a reduced-motion visitor both get the finished card,
           and the rule needs no per-mode variant to undo. */
        .es-hours-day-out { transition: box-shadow 0.5s ease, background-color 0.5s ease; }
        @media (prefers-reduced-motion: no-preference) {
            html.es-anim [data-reveal]:not(.is-revealed) .es-hours-day-out {
                background-color: transparent;
                box-shadow: none;
            }
        }

        /* --- The date slip: what one crossed day actually stores ------ */
        .es-hours-slip {
            border-radius: 0.6rem;
            border: 1px dashed rgba(16, 26, 23, 0.22);
            background: rgba(16, 26, 23, 0.03);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
        }
        .dark .es-hours-slip { border-color: rgba(233, 243, 240, 0.22); background: rgba(233, 243, 240, 0.04); }
        .es-hours-slip-row { display: flex; align-items: baseline; justify-content: space-between; gap: 0.75rem; }

        /* --- A disabled Save, which is how the tab really opens ------- */
        .es-hours-save {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.3rem 0.7rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(16, 26, 23, 0.14);
            background: rgba(16, 26, 23, 0.05);
            color: #495652;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        .dark .es-hours-save { border-color: rgba(233, 243, 240, 0.14); background: rgba(233, 243, 240, 0.06); color: #97a8a3; }

        /* --- The week strip used in the two door-face mocks ----------- */
        .es-hours-week { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 3px; }
        .es-hours-cell {
            border-radius: 0.35rem;
            padding: 0.3rem 0.25rem 0.5rem;
            min-height: 3.1rem;
            background: rgba(233, 243, 240, 0.05);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.66rem;
            font-weight: 600;
            color: #97a8a3;
            text-align: center;
        }
        /* The product's own tint for a day somebody is out. */
        .es-hours-cell-out {
            background-color: #39341a;
            color: #fcd34d;
            box-shadow: inset 0 0 0 1px rgba(252, 211, 77, 0.3);
        }
        .es-hours-tip {
            display: inline-flex;
            flex-direction: column;
            gap: 0.15rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(252, 211, 77, 0.3);
            background: rgba(57, 52, 26, 0.9);
            padding: 0.45rem 0.7rem;
            color: #fcd34d;
            font-size: 0.7rem;
            line-height: 1.35;
        }
        .es-hours-gig {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 0.6rem;
            border-radius: 0.4rem;
            background: rgba(233, 243, 240, 0.05);
            padding: 0.4rem 0.6rem;
            font-size: 0.74rem;
        }

        /* --- The card, one line of it, for the finale ------------------
               The hero opens on the whole month; the finale closes on five
               cells of it. Pinned to the lit palette with no .dark rules,
               because it only ever sits inside the fixed-dark band and the
               --bands diff has to stay at zero. */
        .es-hours-mini { display: inline-flex; gap: 3px; }
        .es-hours-mini span {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.85rem;
            height: 1.85rem;
            border-radius: 0.35rem;
            background: rgba(233, 243, 240, 0.06);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.68rem;
            font-weight: 700;
            color: #97a8a3;
        }
        .es-hours-mini span.is-out {
            background-color: #1d3933;
            background-image: repeating-linear-gradient(135deg, rgba(94, 234, 212, 0.16) 0 2px, rgba(94, 234, 212, 0) 2px 7px);
            color: #5eead4;
            box-shadow: inset 0 0 0 1px rgba(94, 234, 212, 0.4);
        }
        .es-hours-mini span.is-out::after {
            content: "";
            position: absolute;
            left: 14%;
            right: 14%;
            top: 50%;
            height: 1px;
            transform: rotate(-24deg);
            background: rgba(94, 234, 212, 0.6);
        }

        /* --- Plan tags ----------------------------------------------- */
        .es-hours-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.25rem;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            border: 1px solid rgba(13, 91, 78, 0.4);
            color: #0d5b4e;
        }
        .dark .es-hours-plan { border-color: rgba(94, 234, 212, 0.42); color: #5eead4; }
        .es-hours-band .es-hours-plan { border-color: rgba(94, 234, 212, 0.42); color: #5eead4; }
        .es-hours-plan-alt { border-color: rgba(16, 26, 23, 0.35); color: #101a17; }
        .dark .es-hours-plan-alt { border-color: rgba(233, 243, 240, 0.38); color: #e9f3f0; }
        .es-hours-band .es-hours-plan-alt { border-color: rgba(233, 243, 240, 0.38); color: #e9f3f0; }

        /* --- Chips (marquee) ----------------------------------------- */
        .es-hours-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            border: 1px solid rgba(16, 26, 23, 0.16);
            background: rgba(255, 255, 255, 0.7);
            color: #495652;
            font-size: 0.76rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .dark .es-hours-chip {
            border-color: rgba(233, 243, 240, 0.16);
            background: rgba(233, 243, 240, 0.05);
            color: #a8b7b2;
        }

        /* --- The comparison table ------------------------------------ */
        .es-hours-table { width: 100%; border-collapse: collapse; }
        .es-hours-table th, .es-hours-table td {
            padding: 0.85rem 0.75rem;
            text-align: start;
            vertical-align: top;
            border-top: 1px solid rgba(16, 26, 23, 0.1);
        }
        .dark .es-hours-table th, .dark .es-hours-table td { border-top-color: rgba(233, 243, 240, 0.11); }
        .es-hours-table thead th { border-top: 0; }
        /* Keep the row labels on one line at tablet widths. Written here rather
           than as min-w-[9rem]: that arbitrary utility is not in the built CSS. */
        .es-hours-table tbody th { min-width: 8.5rem; }

        /* --- Links and buttons --------------------------------------- */
        .es-hours-link { color: #0d5b4e; }
        .es-hours-link:hover { color: #101a17; }
        .dark .es-hours-link { color: #5eead4; }
        .dark .es-hours-link:hover { color: #e9f3f0; }

        /* The button carries its own text colour: white on the deep teal fill
           (8.00) in light mode, near-black on the bright fill (12.86) in dark. */
        .es-hours-btn {
            background-color: #0d5b4e;
            color: #ffffff;
            box-shadow: 0 18px 36px -14px rgba(13, 91, 78, 0.5);
        }
        .es-hours-btn:hover { background-color: #09453b; box-shadow: 0 22px 44px -14px rgba(13, 91, 78, 0.6); }
        .dark .es-hours-btn { background-color: #5eead4; color: #08120f; }
        .dark .es-hours-btn:hover { background-color: #8af3e2; }
        /* Inside the fixed-dark band the button is pinned to the lit variant in
           both colour modes, so the band really is one physical place and the
           --bands mode diff stays at zero. #08120f on #5eead4 measures 12.86. */
        .es-hours-band .es-hours-btn { background-color: #5eead4; color: #08120f; }
        .es-hours-band .es-hours-btn:hover { background-color: #8af3e2; }

        /* --- Dot-nav tooltip ----------------------------------------- */
        .es-hours-navtip {
            background: #ffffff;
            border-color: rgba(16, 26, 23, 0.14);
            color: #101a17;
        }
        .dark .es-hours-navtip {
            background: #101a17;
            border-color: rgba(233, 243, 240, 0.16);
            color: #e9f3f0;
        }

        /* --- FAQ / related hover ------------------------------------- */
        .es-hours-hover:hover { border-color: rgba(13, 91, 78, 0.45); }
        .dark .es-hours-hover:hover { border-color: rgba(94, 234, 212, 0.45); }
        .es-hours-hover:hover .es-hours-hover-title,
        .es-hours-hover:hover .es-hours-hover-arrow { color: #0d5b4e; }
        .dark .es-hours-hover:hover .es-hours-hover-title,
        .dark .es-hours-hover:hover .es-hours-hover-arrow { color: #5eead4; }

        /* --- Shared-system recolours (brand blue by default) ---------- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(13, 91, 78, 0.12), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(94, 234, 212, 0.1), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(13, 91, 78, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(94, 234, 212, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #0d5b4e; }
        .dark .es-dot.is-active .es-dot-pip { background: #5eead4; }

        /* --- Focus rings. No border-radius here: setting it changes the
               element's own shape on focus. Outlines already follow it. --- */
        #es-hours-page a:focus-visible,
        #es-hours-page summary:focus-visible,
        #es-hours-page button:focus-visible {
            outline: 2px solid #0d5b4e;
            outline-offset: 3px;
        }
        .dark #es-hours-page a:focus-visible,
        .dark #es-hours-page summary:focus-visible,
        .dark #es-hours-page button:focus-visible {
            outline-color: #5eead4;
        }
        .es-hours-band a:focus-visible,
        .es-hours-band summary:focus-visible,
        .es-hours-band button:focus-visible {
            outline-color: #5eead4 !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-hours-day-out { transition: none !important; }
        }
    </style>

    @php
        // The card by the door. Next month, so the page never shows a month that
        // has already gone, built from the schedule's first day of week (Sunday
        // is the product default, roles.first_day_of_week).
        $cardMonth = now()->addMonth()->startOfMonth();
        $cardMonthName = $cardMonth->format('F');
        $cardLead = (int) $cardMonth->dayOfWeek;          // 0 = Sunday
        $cardDays = (int) $cardMonth->daysInMonth;
        // A five-night run away, plus one weekend at somebody else's wedding.
        // Derived from the real weekdays of the month rather than hard-coded, so
        // the run genuinely lands Monday to Friday and the pair genuinely lands
        // on a Saturday and Sunday whichever month this page is served in.
        $cardSecondMonday = null;
        $cardFourthSaturday = null;
        $cardMondays = 0;
        $cardSaturdays = 0;
        for ($cardProbe = 1; $cardProbe <= $cardDays; $cardProbe++) {
            $cardProbeDow = (int) $cardMonth->copy()->day($cardProbe)->dayOfWeek;
            if ($cardProbeDow === 1 && ++$cardMondays === 2) {
                $cardSecondMonday = $cardProbe;
            }
            if ($cardProbeDow === 6 && ++$cardSaturdays === 4) {
                $cardFourthSaturday = $cardProbe;
            }
        }
        $cardOut = range($cardSecondMonday, min($cardSecondMonday + 4, $cardDays));
        $cardOut[] = $cardFourthSaturday;
        if ($cardFourthSaturday + 1 <= $cardDays) {
            $cardOut[] = $cardFourthSaturday + 1;
        }
        $cardCells = array_merge(
            array_fill(0, $cardLead, null),
            range(1, $cardDays)
        );
        while (count($cardCells) % 7 !== 0) {
            $cardCells[] = null;
        }
        $cardWeeks = array_chunk($cardCells, 7);
        $cardDayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

        // What the two faces of the door show for the same week.
        $doorWeek = [
            ['Mon', 16, false],
            ['Tue', 17, false],
            ['Wed', 18, true],
            ['Thu', 19, false],
            ['Fri', 20, false],
            ['Sat', 21, false],
            ['Sun', 22, false],
        ];

        $versus = [
            ['What it records', 'Whole dates you cannot work', 'Bookable time slots guests can take'],
            ['Who marks it', 'Each member, for themselves', 'You, once, as an appointment type'],
            ['Who can read it', 'Signed-in members of the schedule', 'Anyone with your booking page'],
            ['What a guest can do with it', 'Nothing. It is never published', 'Pick an open time and book it'],
            ['Where it lives', 'The Availability tab', 'The Appointments tab, plus a public booking page'],
            ['Plan and schedule type', 'Enterprise, talent schedules', 'Pro, any schedule type'],
        ];

        $faqs = [
            [
                'q' => 'How does availability management work?',
                'a' => 'The Availability tab on a talent schedule is a month calendar. Every date on it starts available. Click a date to mark it unavailable, click it again to clear it, then press Save. Your dates are stored against your own membership of that schedule, and they appear for the rest of your team on the Schedule tab.',
            ],
            [
                'q' => 'Who can set availability?',
                'a' => 'Every member of the schedule sets their own dates, and a member can only edit their own. Viewers can read the calendar without changing it. Availability management is an Enterprise plan feature on eventschedule.com and is included on selfhosted deployments.',
            ],
            [
                'q' => 'Can I set different availability for different days?',
                'a' => 'You can mark any date you like, in any month, but a mark is a whole date rather than a set of hours. There are no half days, no time ranges and no reason field: a crossed day stores the date and nothing else. If you want guests to pick a time inside a day, that is appointment booking rather than availability.',
            ],
            [
                'q' => 'Can guests see the dates I have crossed out?',
                'a' => 'No. Availability is not published anywhere. Your public schedule page, the embedded calendar and the feeds show your events and say nothing about which dates you marked. Only members of the schedule who are signed in can see the marks.',
            ],
            [
                'q' => 'Does marking a date stop somebody booking me on it?',
                'a' => 'No, and this is worth being straight about: a crossed date is a note to your team, not a lock. Nothing is blocked and nothing is checked against it, so a venue or a curator can still add you to an event on a day you marked. They never saw the mark either: the marks are only visible to signed-in members of your own schedule, so the tab is there for the person on your side who answers the email, not for the person sending it.',
            ],
            [
                'q' => 'Why are the dates on my calendar not clickable?',
                'a' => 'The Availability calendar becomes clickable once the schedule email address has been verified, and the Save button appears at the same time. The tab itself is only offered on talent schedules: curator schedules are sent back to the Schedule tab, and a venue schedule is never given the tab in its navigation.',
            ],
            [
                'q' => 'How far ahead can I mark dates?',
                'a' => 'As far as you like. Move between months with the calendar arrows and mark dates in each one. Save returns you to the month you were working in, so a run of months takes a few passes rather than one long form.',
            ],
        ];

        $dotSections = [
            ['top', 'The card'],
            ['open', 'Open by default'],
            ['record', 'One crossed day'],
            ['door', 'Who reads it'],
            ['team', 'One card each'],
            ['gates', 'Two locks'],
            ['note', 'A note, not a lock'],
            ['around', 'Around the card'],
            ['versus', 'Or appointments'],
            ['faq', 'Questions'],
            ['claim', 'Start free'],
        ];
    @endphp

    <div id="es-hours-page" class="es-hours-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the card by the door                                -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(13, 91, 78, 0.2), rgba(13, 91, 78, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(94, 234, 212, 0.12), rgba(94, 234, 212, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="h-5 w-5 es-hours-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="es-hours-muted text-sm font-medium tracking-wide">Availability &middot; talent schedules &middot; Enterprise</span>
                    </div>

                    <h1 class="es-balance es-hours-ink mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">Do not list the days you are free.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="es-hours-accent">Cross out</span> the days you are gone.</span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-hours-muted mb-10 max-w-xl text-lg sm:text-xl">
                        The Availability tab is a month you click. Every date starts open, the ones you cross stay crossed, and whoever on your team answers the booking emails sees them on the shared calendar before they say yes to a Friday you are already 400 miles from.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="#door" class="glass group inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            See who reads it
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="es-hours-btn group inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Create your schedule
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- The card: a month, with the days you are gone crossed out -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-hours-card p-6 sm:p-7">
                        <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                            <p class="es-hours-plate">Availability</p>
                            <span class="es-hours-save" aria-hidden="true">
                                <svg class="h-3.5 w-3.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16l4 4m0 0l4-4m-4 4V8m7 4a7 7 0 10-14 0" /></svg>
                                Save
                            </span>
                        </div>
                        <p class="es-hours-ink mb-4 text-lg font-bold">{{ $cardMonthName }}</p>

                        <table class="es-hours-cal">
                            <caption class="sr-only">{{ $cardMonthName }}, with {{ count($cardOut) }} dates marked unavailable</caption>
                            <thead>
                                <tr>
                                    @foreach ($cardDayNames as $cardDayName)
                                        <th scope="col">{{ substr($cardDayName, 0, 1) }}<span class="sr-only">{{ substr($cardDayName, 1) }}</span></th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cardWeeks as $cardWeek)
                                    <tr>
                                        @foreach ($cardWeek as $cardDay)
                                            @if ($cardDay === null)
                                                <td class="es-hours-day es-hours-day-blank"></td>
                                            @elseif (in_array($cardDay, $cardOut, true))
                                                <td class="es-hours-day es-hours-day-out">
                                                    {{ $cardDay }}
                                                    <span class="es-hours-outtag">Out</span>
                                                </td>
                                            @else
                                                <td class="es-hours-day">{{ $cardDay }}</td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <p class="es-hours-muted mt-4 text-[0.6875rem]">
                            Hatched cells are the dates you marked. Everything else is simply available, because that is what an untouched date means.
                        </p>

                        <p class="es-hours-muted es-hours-rule mt-4 pt-4 text-xs">
                            {{ count($cardOut) }} dates crossed this month, stored against your name on this schedule. Nobody outside the schedule can see this card.
                        </p>
                    </div>
                </div>
            </div>

            <!-- What people actually cross out -->
            <div class="es-fade-up es-d-4 mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach (['On tour', 'Studio week', 'Day job', 'Family', 'Wedding', 'Recovering', 'Residency', 'Out of the country', 'Teaching', 'Off'] as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-hours-chip">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. Open by default                                           -->
    <!-- ============================================================ -->
    <section id="open" class="es-hours-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-hours-num mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                <p class="es-hours-plate mb-4" data-reveal style="--reveal-delay: 0.05s;">The direction</p>
                <h2 class="es-balance es-hours-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    An hours board is the <span class="es-hours-accent">wrong way round</span> for a performer.
                </h2>
                <p class="es-hours-muted mx-auto mt-5 max-w-2xl text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    A shop posts the hours it is open because the rest of the week it is shut. A working performer is the other way round: most dates are possible, and the short list is the one nobody can guess.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                <div class="es-hours-card p-7" data-reveal="panel">
                    <p class="es-hours-plate mb-3">The default</p>
                    <h3 class="es-hours-ink mb-2 text-lg font-bold">Available</h3>
                    <p class="es-hours-muted text-sm leading-relaxed">Nothing has to be entered for a date to be open. There is no weekly pattern to fill in first and no hours to publish, so an empty calendar already says the true thing.</p>
                </div>
                <div class="es-hours-card p-7" data-reveal="panel">
                    <p class="es-hours-plate mb-3">The exception</p>
                    <h3 class="es-hours-ink mb-2 text-lg font-bold">One click</h3>
                    <p class="es-hours-muted text-sm leading-relaxed">A date you cannot work takes one click, and a second click takes the mark back off. The work is proportional to how much of your year is actually spoken for.</p>
                </div>
                <div class="es-hours-card p-7" data-reveal="panel">
                    <p class="es-hours-plate mb-3">The upkeep</p>
                    <h3 class="es-hours-ink mb-2 text-lg font-bold">Nothing to renew</h3>
                    <p class="es-hours-muted text-sm leading-relaxed">Because marks are individual dates rather than a repeating rule, none of it silently keeps applying next year. A month you never touch stays completely open.</p>
                </div>
            </div>

            <p class="es-hours-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                It also means there is nothing to switch off when a plan changes. Take the mark away and the date is open again.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. One crossed day                                           -->
    <!-- ============================================================ -->
    <section id="record" class="es-hours-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div>
                    <div class="es-hours-num mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                    <p class="es-hours-plate mb-4" data-reveal style="--reveal-delay: 0.05s;">The record</p>
                    <h2 class="es-balance es-hours-ink mb-5 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                        A crossed day carries <span class="es-hours-accent">a date.</span> That is all.
                    </h2>
                    <p class="es-hours-muted mb-6 text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        Under the calendar, your availability is a plain list of dates kept against your membership of the schedule. Knowing that is worth more than a longer feature list, because it tells you exactly what the tab can and cannot answer.
                    </p>
                    <ul class="es-hours-muted space-y-3" data-reveal-group="70">
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none es-hours-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>No times. A date is out or it is not, so there is no morning-only and no after-six.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none es-hours-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>No reason. Nobody has to be told whether it is a tour, a wedding or a Tuesday you need back.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none es-hours-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>No repeating rule. Every Sunday of a long season is a click each, which is the honest cost of the simplicity.</span>
                        </li>
                    </ul>
                </div>

                <div data-reveal="panel">
                    <div class="es-hours-card p-6 sm:p-7">
                        <div class="mb-4 flex flex-wrap items-center gap-2">
                            <h3 class="es-hours-ink text-lg font-bold">What Save writes</h3>
                            <span class="es-hours-plan">Enterprise</span>
                        </div>
                        <div class="es-hours-slip p-4" aria-hidden="true">
                            <div class="es-hours-slip-row es-hours-muted mb-2 text-[0.625rem] uppercase tracking-[0.18em]">
                                <span>Field</span>
                                <span>Value</span>
                            </div>
                            @foreach ([['member', 'you'], ['schedule', 'your talent schedule'], ['dates', count($cardOut) . ' of them'], ['times', 'none'], ['reasons', 'none'], ['public', 'no']] as [$slipKey, $slipValue])
                                <div class="es-hours-slip-row es-hours-rule py-1.5 text-xs">
                                    <span class="es-hours-muted">{{ $slipKey }}</span>
                                    <span class="es-hours-ink font-semibold">{{ $slipValue }}</span>
                                </div>
                            @endforeach
                        </div>
                        <p class="es-hours-muted mt-4 text-xs leading-relaxed">
                            Save also remembers which month you were working in and puts you back there, so marking a long stretch is a few short passes rather than one enormous form.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Who reads the card (fixed-dark band, both modes)          -->
    <!-- ============================================================ -->
    <section id="door" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-hours-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-hours-num mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                    <p class="es-hours-plate mb-4" data-reveal style="--reveal-delay: 0.05s;">Two faces of one door</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        The card hangs on the <span class="es-hours-lit">inside.</span>
                    </h2>
                    <p class="mt-5 text-lg text-gray-300" data-reveal style="--reveal-delay: 0.15s;">
                        This is the part first-time users get wrong, so it is worth drawing. The same week, seen from both sides.
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-2" data-reveal-group="120">
                    <!-- Inside: the shared Schedule tab -->
                    <div class="es-hours-card p-6" data-reveal="panel">
                        <div class="mb-4 flex flex-wrap items-center gap-2">
                            <h3 class="es-hours-band-ink text-lg font-bold">Inside: your Schedule tab</h3>
                            <span class="es-hours-plan">Members only</span>
                        </div>
                        <div class="es-hours-week mb-3" aria-hidden="true">
                            @foreach ($doorWeek as [$doorName, $doorDate, $doorOut])
                                <div class="es-hours-cell @if ($doorOut) es-hours-cell-out @endif">
                                    <div class="text-[0.6rem] uppercase tracking-[0.12em]">{{ substr($doorName, 0, 1) }}</div>
                                    <div class="text-[0.8rem]">{{ $doorDate }}</div>
                                    @if ($doorOut)
                                        <div class="mt-1 text-[0.6rem] font-bold uppercase tracking-[0.08em]">2 out</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <div class="mb-3 space-y-1.5" aria-hidden="true">
                            <div class="es-hours-gig">
                                <span class="es-hours-band-ink">Fri 20 &middot; The Blue Note</span>
                                <span class="es-hours-band-muted">21:00</span>
                            </div>
                            <div class="es-hours-gig">
                                <span class="es-hours-band-ink">Sat 21 &middot; Harbour Festival</span>
                                <span class="es-hours-band-muted">18:30</span>
                            </div>
                        </div>
                        <div class="es-hours-tip mb-4" aria-hidden="true">
                            <span class="font-semibold uppercase tracking-[0.12em]">Unavailable</span>
                            <span>Nadia K.</span>
                            <span>Sam R.</span>
                        </div>
                        <p class="es-hours-band-muted text-sm leading-relaxed">
                            Same week, same two gigs, one extra thing: a date somebody has marked is tinted, and the icon on it names who is out. That tint is the product's own colour for the state, and it is the whole reason the marks exist. The person holding the diary can see the day before they answer the email.
                        </p>
                    </div>

                    <!-- Outside: the public schedule page -->
                    <div class="es-hours-card p-6" data-reveal="panel">
                        <div class="mb-4 flex flex-wrap items-center gap-2">
                            <h3 class="es-hours-band-ink text-lg font-bold">Outside: your public page</h3>
                            <span class="es-hours-plan es-hours-plan-alt">Everyone</span>
                        </div>
                        <div class="es-hours-week mb-3" aria-hidden="true">
                            @foreach ($doorWeek as [$doorName, $doorDate, $doorOut])
                                <div class="es-hours-cell">
                                    <div class="text-[0.6rem] uppercase tracking-[0.12em]">{{ substr($doorName, 0, 1) }}</div>
                                    <div class="text-[0.8rem]">{{ $doorDate }}</div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mb-4 space-y-1.5" aria-hidden="true">
                            <div class="es-hours-gig">
                                <span class="es-hours-band-ink">Fri 20 &middot; The Blue Note</span>
                                <span class="es-hours-band-muted">21:00</span>
                            </div>
                            <div class="es-hours-gig">
                                <span class="es-hours-band-ink">Sat 21 &middot; Harbour Festival</span>
                                <span class="es-hours-band-muted">18:30</span>
                            </div>
                        </div>
                        <p class="es-hours-band-muted text-sm leading-relaxed">
                            The same two gigs, and nothing else. No tint, no gap explained, no hint that a mark exists. The public page, the embedded calendar and the feeds all behave this way, because availability is never published. And it is worth knowing exactly who is out here: guests, yes, but also the venue about to pitch you that Wednesday. Nothing on their side of the door shows your marks.
                        </p>
                    </div>
                </div>

                <p class="mt-10 text-center text-gray-300" data-reveal>
                    So the honest summary is: this is an internal diary, not a shop window.
                    <a href="{{ marketing_url('/features/private-events') }}" class="es-hours-lit inline-flex items-center gap-1 font-semibold transition-all hover:gap-2">
                        What else stays private
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. One card each                                             -->
    <!-- ============================================================ -->
    <section id="team" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-hours-num mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                <p class="es-hours-plate mb-4" data-reveal style="--reveal-delay: 0.05s;">One card each</p>
                <h2 class="es-balance es-hours-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Your dates are <span class="es-hours-accent">yours.</span> The month is everyone's.
                </h2>
                <p class="es-hours-muted mx-auto mt-5 max-w-2xl text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    A duo, a band with a sound engineer, a performer plus the person who answers the enquiries: each of them keeps their own card, and the shared month adds them up.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                <div class="es-hours-card flex flex-col p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-hours-ink text-lg font-bold">You edit yours</h3>
                        <span class="es-hours-plan">Enterprise</span>
                    </div>
                    <p class="es-hours-muted text-sm leading-relaxed">The Availability tab always shows the dates of whoever is signed in, and Save only ever writes that person's dates. Nobody can cross a day out on your behalf, and you cannot clear theirs.</p>
                </div>
                <div class="es-hours-card flex flex-col p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-hours-ink text-lg font-bold">The month adds up</h3>
                        <span class="es-hours-plan">Enterprise</span>
                    </div>
                    <p class="es-hours-muted text-sm leading-relaxed">On the Schedule tab, every member's marks land on the same calendar and each affected date names the people who are out, so two absences on one Friday read as two names rather than one vague warning.</p>
                </div>
                <div class="es-hours-card flex flex-col p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-hours-ink text-lg font-bold">Up to five names</h3>
                        <span class="es-hours-plan">Enterprise</span>
                    </div>
                    <p class="es-hours-muted text-sm leading-relaxed">On the Enterprise plan a schedule on eventschedule.com holds up to five team members, and each one is either an admin who edits the calendar or a viewer who only reads it. Viewers see the marks without being able to change anything. A free or Pro schedule has exactly one member, which is you.</p>
                </div>
            </div>

            <p class="es-hours-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                Multiple team members are an Enterprise feature too, which is the real reason this tab sits where it does: a card nobody else can read is not worth much.
                <a href="{{ marketing_url('/features/team-scheduling') }}" class="es-hours-link font-medium hover:underline">How team members work</a>
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Two locks                                                 -->
    <!-- ============================================================ -->
    <section id="gates" class="es-hours-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-hours-num mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                <p class="es-hours-plate mb-4" data-reveal style="--reveal-delay: 0.05s;">Two locks</p>
                <h2 class="es-balance es-hours-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    Both have to <span class="es-hours-accent">turn.</span>
                </h2>
                <p class="es-hours-muted mx-auto mt-5 max-w-2xl text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Most feature pages bury the conditions in a footnote. These two decide whether the tab exists for you at all, so they get a section of their own.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-2" data-reveal-group="110">
                <div class="es-hours-card flex flex-col p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <p class="es-hours-plate">Lock one</p>
                        <span class="es-hours-plan">Required</span>
                    </div>
                    <h3 class="es-hours-ink mb-2 text-xl font-bold">A talent schedule</h3>
                    <p class="es-hours-muted text-sm leading-relaxed">The Availability tab is offered on talent schedules only. Curator schedules are sent back to the Schedule tab if they try the address, and a venue schedule is never given the tab in its navigation: a venue's opening pattern is a different problem, and the product does not pretend this tab solves it.</p>
                    <p class="es-hours-muted mt-auto pt-4 text-sm">
                        <a href="{{ marketing_url('/for-musicians') }}" class="es-hours-link font-medium hover:underline">What a talent schedule is</a>
                    </p>
                </div>
                <div class="es-hours-card flex flex-col p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <p class="es-hours-plate">Lock two</p>
                        <span class="es-hours-plan">Required</span>
                    </div>
                    <h3 class="es-hours-ink mb-2 text-xl font-bold">The Enterprise plan</h3>
                    <p class="es-hours-muted text-sm leading-relaxed">Availability management is an Enterprise feature on eventschedule.com, and every feature is included on a selfhosted deployment. On the free and Pro plans the tab prompts an upgrade rather than saving, so nothing is half enabled.</p>
                    <p class="es-hours-muted mt-auto pt-4 text-sm">
                        <a href="{{ marketing_url('/pricing') }}" class="es-hours-link font-medium hover:underline">See the plans</a>
                    </p>
                </div>
            </div>

            <div class="es-hours-card mx-auto mt-6 max-w-3xl p-6" data-reveal="panel">
                <div class="mb-2 flex flex-wrap items-center gap-2">
                    <p class="es-hours-plate">And one practical thing</p>
                </div>
                <p class="es-hours-muted text-sm leading-relaxed">
                    The calendar becomes clickable once the schedule's email address is verified, and the Save button only appears then too. If the dates are not responding to a click, that is almost always the reason. The verification email goes out when the schedule is created, and a banner at the top of the schedule will send it again.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. A note, not a lock                                        -->
    <!-- ============================================================ -->
    <section id="note" class="es-hours-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-10 max-w-3xl text-center">
                <div class="es-hours-num mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                <p class="es-hours-plate mb-4" data-reveal style="--reveal-delay: 0.05s;">What it will not do</p>
                <h2 class="es-balance es-hours-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    A note to your team, <span class="es-hours-accent">not a lock</span> on the date.
                </h2>
            </div>

            <div class="es-hours-card p-7 sm:p-8" data-reveal="panel">
                <p class="es-hours-muted mb-5 leading-relaxed">
                    A crossed date changes what the shared calendar looks like. It does not change what the software allows. Nothing is validated against your marks, so no event is refused, moved or flagged because of them.
                </p>
                <ul class="es-hours-muted space-y-3 text-sm" data-reveal-group="70">
                    <li class="flex gap-3" data-reveal>
                        <span class="es-hours-accent mt-0.5 flex-none font-mono text-xs font-bold" aria-hidden="true">01</span>
                        <span>A venue or a curator can still add you to an event on a date you crossed. They never saw the mark, so the request turns up on your Requests tab like any other, to accept or decline.</span>
                    </li>
                    <li class="flex gap-3" data-reveal>
                        <span class="es-hours-accent mt-0.5 flex-none font-mono text-xs font-bold" aria-hidden="true">02</span>
                        <span>Nobody is emailed when you mark a date. The marks are read on screen, by the people already looking at the shared month.</span>
                    </li>
                    <li class="flex gap-3" data-reveal>
                        <span class="es-hours-accent mt-0.5 flex-none font-mono text-xs font-bold" aria-hidden="true">03</span>
                        <span>Your own events are unaffected. Marking a date does not hide, move or cancel anything already on the calendar for that day.</span>
                    </li>
                </ul>
                <p class="es-hours-muted es-hours-rule mt-6 pt-5 text-sm leading-relaxed">
                    If what you actually want is for a date to be genuinely untakeable, the thing that does that is appointment booking, where open times are the only times a guest can pick. Section 09 puts the two side by side.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Around the card: bento                                    -->
    <!-- ============================================================ -->
    <section id="around" class="es-hours-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-hours-num mb-6" data-reveal aria-hidden="true"><span>08</span></div>
                <p class="es-hours-plate mb-4" data-reveal style="--reveal-delay: 0.05s;">Around the card</p>
                <h2 class="es-balance es-hours-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    What the same calendar is already doing.
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">
                <!-- 1 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-hours-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-hours-ink text-xl font-bold">The month everyone shares</h3>
                                <span class="es-hours-plan">Enterprise</span>
                            </div>
                            <p class="es-hours-muted mb-4">The Schedule tab is where the marks are actually useful. Your accepted events and every member's crossed dates sit on the same month, so the answer to "can we do the 14th" is on screen instead of somewhere in a group chat.</p>
                            <p class="es-hours-muted text-sm">The calendar starts the week on whichever day your schedule is set to start it, so the grid matches the way your part of the world reads a month.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-hours-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-hours-ink text-xl font-bold">Your own calendar</h3>
                                <span class="es-hours-plan es-hours-plan-alt">Free</span>
                            </div>
                            <p class="es-hours-muted">Each member can sync the schedule into the calendar they personally live in, so the gigs sit next to the day job that made you cross a date out in the first place.</p>
                        </div>
                        <p class="es-hours-muted relative z-10 mt-auto pt-4 text-sm">
                            <a href="{{ marketing_url('/features/calendar-sync') }}" class="es-hours-link font-medium hover:underline">How calendar sync works</a>
                        </p>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-hours-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-hours-ink text-xl font-bold">Months ahead</h3>
                                <span class="es-hours-plan">Enterprise</span>
                            </div>
                            <p class="es-hours-muted">Move with the calendar arrows and mark next spring today. Save brings you back to the month you were in, ready for the next handful of dates.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-hours-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-hours-ink text-xl font-bold">Requests keep arriving</h3>
                                <span class="es-hours-plan es-hours-plan-alt">Free</span>
                            </div>
                            <p class="es-hours-muted mb-4">A curator or a venue putting you on their bill shows up as a request you accept or decline, whatever your card says about that date. The mark is context for your decision, not a filter on your inbox.</p>
                            <p class="es-hours-muted text-sm">Accepting puts the event on your public schedule. Declining leaves it off, and neither answer touches your availability dates.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-hours-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-hours-ink text-xl font-bold">Bookable time instead</h3>
                                <span class="es-hours-plan es-hours-plan-alt">Pro</span>
                            </div>
                            <p class="es-hours-muted">Want a guest to take a slot rather than read a diary? Appointment types publish open times on a booking page and take the booking there.</p>
                        </div>
                        <p class="es-hours-muted relative z-10 mt-auto pt-4 text-sm">
                            <a href="{{ route('marketing.appointments') }}" class="es-hours-link font-medium hover:underline">Appointment booking</a>
                        </p>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-hours-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-hours-ink text-xl font-bold">The part guests do see</h3>
                                <span class="es-hours-plan es-hours-plan-alt">Free</span>
                            </div>
                            <p class="es-hours-muted mb-4">While the card stays inside, your dates that are public work hard: a schedule page of your own, an embeddable calendar for the site you already have, and a follow button that lets you email the people who pressed it.</p>
                            <p class="es-hours-muted text-sm">
                                Newsletters are free at 10 emails a month, counted per recipient, and rise to 100 on Pro and 1,000 on Enterprise.
                                <a href="{{ marketing_url('/features/newsletters') }}" class="es-hours-link font-medium hover:underline">What following does</a>
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
    <!-- 9. Availability or appointments                               -->
    <!-- ============================================================ -->
    <section id="versus" class="es-hours-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-hours-num mb-6" data-reveal aria-hidden="true"><span>09</span></div>
                <p class="es-hours-plate mb-4" data-reveal style="--reveal-delay: 0.05s;">Two tabs, similar names</p>
                <h2 class="es-balance es-hours-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    Availability, or <span class="es-hours-accent">appointments</span>?
                </h2>
                <p class="es-hours-muted mx-auto mt-5 max-w-2xl text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    They sound alike and they solve opposite halves of the problem. One is a diary your team reads. The other is a door guests can walk through.
                </p>
            </div>

            <div class="es-hours-card overflow-x-auto p-4 sm:p-6" data-reveal="panel">
                <table class="es-hours-table">
                    <caption class="sr-only">How the Availability tab differs from appointment booking</caption>
                    <thead>
                        <tr>
                            <th scope="col"><span class="sr-only">What is being compared</span></th>
                            <th scope="col" class="es-hours-ink text-sm font-bold">Availability</th>
                            <th scope="col" class="es-hours-ink text-sm font-bold">Appointments</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($versus as [$vsLabel, $vsAvail, $vsAppt])
                            <tr>
                                <th scope="row" class="es-hours-muted text-xs font-semibold uppercase tracking-[0.12em]">{{ $vsLabel }}</th>
                                <td class="es-hours-ink text-sm">{{ $vsAvail }}</td>
                                <td class="es-hours-muted text-sm">{{ $vsAppt }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="es-hours-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                Plenty of performers end up running both: the diary for the dates the band is away, the booking page for the lessons and the studio hours.
                <a href="{{ route('marketing.appointments') }}" class="es-hours-link font-medium hover:underline">Read about appointments</a>
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. Three steps                                              -->
    <!-- ============================================================ -->
    <section class="es-hours-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance es-hours-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal>Three steps</h2>
                <p class="es-hours-muted mt-4 text-lg" data-reveal style="--reveal-delay: 0.1s;">From nothing marked to a month your team can read.</p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="120">
                @foreach ([['01', 'Open the tab', 'On a talent schedule with the Enterprise plan, the Availability tab sits beside Schedule. It opens on the current month with nothing marked.'], ['02', 'Click the days you are gone', 'One click marks a date, a second click clears it. Use the arrows to reach the months you already know about.'], ['03', 'Save', 'Your dates are stored against your name and appear on the shared Schedule tab. Save puts you back on the month you were working in.']] as [$stepNum, $stepTitle, $stepBody])
                    <div class="es-hours-card p-7" data-reveal="panel">
                        <div class="es-hours-accent mb-3 font-mono text-2xl font-black">{{ $stepNum }}</div>
                        <h3 class="es-hours-ink mb-2 text-lg font-bold">{{ $stepTitle }}</h3>
                        <p class="es-hours-muted text-sm leading-relaxed">{{ $stepBody }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 text-center" data-reveal>
                <a href="{{ route('marketing.docs.managing_schedules') }}#availability" class="es-hours-link inline-flex items-center font-medium hover:underline">
                    Read the Availability guide
                    <svg aria-hidden="true" class="ml-1 h-4 w-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 11. Related features                                         -->
    <!-- ============================================================ -->
    <section class="es-hours-rule py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-hours-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Team Scheduling" description="Add the people who need to read the shared month" :url="marketing_url('/features/team-scheduling')" icon-color="teal">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Appointments" description="Publish bookable times and let guests take one" :url="route('marketing.appointments')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Calendar Sync" description="Two-way sync with Google, Outlook and CalDAV" :url="marketing_url('/features/calendar-sync')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Private Events" description="Internal and unlisted events, for the dates you keep back" :url="marketing_url('/features/private-events')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-hours-link inline-flex items-center font-medium hover:underline">
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
    <!-- 12. Related pages                                            -->
    <!-- ============================================================ -->
    <section class="es-hours-rule py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-hours-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4" data-reveal-group="70">
                @foreach ([['/for-musicians', 'Musicians'], ['/for-djs', 'DJs'], ['/for-comedians', 'Comedians'], ['/for-dance-groups', 'Dance Groups']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" class="es-hours-hover es-hours-card group flex flex-col p-5 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-hours-hover-title es-hours-ink mb-3 text-sm font-semibold transition-colors">For {{ $relName }}</span>
                        <span class="es-hours-hover-arrow es-hours-muted mt-auto inline-flex items-center gap-1 text-xs font-medium transition-colors">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
            <div class="mt-8 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-hours-link inline-flex items-center font-medium hover:underline">
                    See all use cases
                    <svg aria-hidden="true" class="ml-1 h-4 w-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

    <section id="faq" class="es-hours-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="es-hours-num mb-6" data-reveal aria-hidden="true"><span>10</span></div>
                <h2 class="es-balance es-hours-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-hours-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    What performers ask before they start crossing dates out.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-hours-hover es-hours-card group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-hours-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-hours-accent flex-none font-mono text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-hours-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-hours-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-hours-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
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
            <div class="es-hours-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>
                <div class="relative z-10">
                    {{-- One line off the card in the hero: the same five-night run, crossed. --}}
                    <div class="es-hours-mini mb-6" aria-hidden="true">
                        @foreach (range($cardSecondMonday - 1, $cardSecondMonday + 5) as $finaleDay)
                            <span @class(['is-out' => in_array($finaleDay, $cardOut, true)])>{{ $finaleDay }}</span>
                        @endforeach
                    </div>
                    <p class="es-hours-plate mb-4">Free to start</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Put your dates somewhere <span class="es-hours-lit">your team can read them.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300">
                        A schedule, a public page and a calendar are free forever. Availability marking arrives with the Enterprise plan, alongside the extra team members who make it worth keeping.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-name" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-400 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-300 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="es-hours-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Start for free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="mt-6 text-sm text-gray-300">No credit card required</p>
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
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 es-hours-navtip whitespace-nowrap rounded-full border px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
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
