<x-marketing-layout>
    <x-slot name="title">Recurring Events | Flexible Scheduling - Event Schedule</x-slot>
    <x-slot name="description">Set events to repeat daily, weekly, biweekly, monthly, or yearly with flexible end conditions, per-occurrence tickets, and automatic sync to Google, Outlook and CalDAV.</x-slot>
    <x-slot name="breadcrumbTitle">Recurring Events</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule Recurring Events",
        "description": "Set events to repeat daily, weekly, biweekly, monthly, or yearly with flexible end conditions, per-occurrence tickets, and automatic sync to Google, Outlook and CalDAV.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Recurring Event Scheduling"
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Recurring Events",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Event Scheduling Software",
        "operatingSystem": "Web",
        "description": "One event record with a day-of-week pattern, an end condition and date exceptions produces every date on your calendar. Ticket inventory, registration capacity and check-in count per date.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Included free"
        },
        "featureList": [
            "Daily recurrence",
            "Weekly recurrence on chosen days of the week",
            "Every N weeks recurrence, from 2 to 52 weeks",
            "Monthly recurrence on the same date",
            "Monthly recurrence on the same weekday",
            "Yearly recurrence",
            "Three end conditions: never, on a date, or after a number of dates",
            "Exclude Dates that take a date out of the pattern",
            "Include Dates that add a date the pattern does not produce",
            "Ticket inventory counted per date",
            "Registration capacity counted per date",
            "Check-in counted per date",
            "A page and an .ics download for every date",
            "Subscribe-able iCal feed with one entry per date for the next 90 days",
            "Two-way Google, Outlook and CalDAV calendar sync"
        ],
        "url": "{{ url()->current() }}",
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
           For recurring-events "The Loop" styles. KEEP THIS NICKNAME.
           /features/calendar-sync was renamed to "The Round Trip"
           specifically to free it up here: a recurrence really is a
           loop, a two-way sync is a round trip. That rename is the
           anti-collision record, so do not rename this page.

           THE SIGNATURE DEVICE IS A CONTINUOUS DAY RAIL, NOT A WEEK
           GRID. `grid-cols-7` month grids already appear on eight
           marketing pages, so another one would be house furniture.
           A rail is also the more truthful drawing: it is PROPORTIONAL,
           so on one 8-week ruler you can see daily fill it, a weekly
           pattern hit twice a week, and a yearly pattern hit once. The
           six frequency options are drawn on the SAME ruler for exactly
           that reason - the comparison is the information.

           COLLISION NOTE, found in review and recorded here so the next
           page does not re-derive it: a flex row of equal day slots with
           a hollow dashed cell for the absent date is NO LONGER unique.
           /for-theaters ships .es-bill-strip / .es-bill-day-dark and
           /for-hotels-and-resorts ships .es-conc-wk-cells, both green,
           both with an empty excepted date. The strip is now shared
           vocabulary. What is still this page's own is the RULER: six
           patterns measured against one identical 8-week window as a
           real HTML table, a mask-faded rail for the end condition that
           never stops, and a proportionally positioned stop bar for the
           two that do. A future page wanting a day strip should assume
           it is borrowing, not inventing, and earn its keep somewhere
           other than the strip itself.

           WHAT THE RAIL IS ALLOWED TO SAY, from the code:
           - a recurring event is ONE `events` row with days_of_week,
             ONE starts_at and ONE duration, so it yields at most one
             occurrence per matching day (Event::matchesDate). A second
             sitting at another time is a SECOND event, never a doubled
             cell.
           - an excluded date is ABSENT. matchesDate() returns false and
             the day simply is not on the calendar, so .es-loop-day-gap
             is hollow. Never draw a strike-through: that would teach a
             cancellation the product does not show.
           - the end conditions are never / on_date / after_events
             (recurring_end_type), which is why .es-loop-stop exists and
             why the "never" rail is the only one that runs off the edge.

           COLOUR: the page KEEPS its existing emerald family, now spent
           as two solid inks rather than a three-stop gradient headline.
           #046b4f on the light ground and #34d399 in the dark. It sits
           clear of the greens already claimed: heritage bottle green
           #14532d (/for-theaters), exit-sign #15803d (/for-nightclubs)
           and deep forest (/for-food-trucks). NO gradient heading text
           anywhere on this page - a bright emerald stop scores about 2
           on a light ground, and that is the single most common AA
           failure in this codebase.

           NEVER use text-gray-500 on this ground: #6b7280 measures only
           4.4 on #f3f6f4. Use .es-loop-muted (7.12).

           The dark bands are pinned physical objects: .es-loop-band
           re-inks .grid-overlay, .animate-shimmer and
           .es-claim:focus-within AFTER the base rules, and every rail
           part that actually appears in a band gets a band variant.
           Deliberately NO .es-aurora inside a band - the shared rule
           flips its opacity with the colour mode and would break the pin.

           INSIDE A BAND, accents and links are .es-loop-lit, never
           .es-loop-accent / .es-loop-link. .es-loop-lit is the
           always-#34d399 ink that needs no mode override, which is why
           there are no band variants of the other two: a band variant
           for content that never enters a band is a rule that paints
           nothing. Same reason there is no band variant of
           .es-loop-stop, .es-loop-scale, .es-loop-ruled or
           .es-loop-plan-pro - those devices all live on the light
           sections. Only add one when the markup actually moves in.

           BLADE RULE for this block: never use @supports probes here. A
           "#" hex inside a parenthesized at-rule condition breaks Blade
           compilation of every later parenthesized directive.
           ============================================================== */

        /* --- Ground and ink --- */
        .es-loop-page { background-color: #f3f6f4; color: #101714; }
        .dark .es-loop-page { background-color: #080f0d; color: #e7eeea; }
        .es-loop-ink { color: #101714; }
        .dark .es-loop-ink { color: #e7eeea; }
        .es-loop-muted { color: #4a5551; }
        .dark .es-loop-muted { color: #9aa8a2; }
        .es-loop-accent { color: #046b4f; }
        .dark .es-loop-accent { color: #34d399; }
        /* Always-lit accent, for the pinned dark bands in both modes. */
        .es-loop-lit { color: #34d399; }

        /* --- The day ruler: a hairline every 7th day, behind a section.
               The calendar as a continuous measure, not a box grid. --- */
        .es-loop-ruled {
            background-image: repeating-linear-gradient(
                to right,
                rgba(16, 23, 20, 0.055) 0px,
                rgba(16, 23, 20, 0.055) 1px,
                transparent 1px,
                transparent 56px);
        }
        .dark .es-loop-ruled {
            background-image: repeating-linear-gradient(
                to right,
                rgba(231, 238, 234, 0.05) 0px,
                rgba(231, 238, 234, 0.05) 1px,
                transparent 1px,
                transparent 56px);
        }

        /* --- The rail. Weeks are groups, so the cycle reads without a
               separate tick element, and every cell is flex: 1 so the
               whole rail stays proportional at any width. --- */
        .es-loop-rail {
            position: relative;
            display: flex;
            gap: 0.4rem;
            align-items: stretch;
        }
        .es-loop-week {
            display: flex;
            flex: 1 1 0;
            min-width: 0;
            gap: 0.1rem;
        }
        .es-loop-day {
            flex: 1 1 0;
            min-width: 0;
            height: 1.9rem;
            border-radius: 0.18rem;
            background: rgba(16, 23, 20, 0.07);
        }
        .dark .es-loop-day { background: rgba(231, 238, 234, 0.08); }
        /* A date the loop produces. */
        .es-loop-day-on { background: #046b4f; }
        .dark .es-loop-day-on { background: #34d399; }
        /* An excluded date: hollow, because the guest sees the day absent. */
        .es-loop-day-gap {
            background: transparent;
            border: 1px dashed rgba(16, 23, 20, 0.3);
        }
        .dark .es-loop-day-gap { border-color: rgba(231, 238, 234, 0.3); }
        /* An included date the pattern would not have produced. */
        .es-loop-day-add {
            background: transparent;
            border: 2px solid #046b4f;
        }
        .dark .es-loop-day-add { border-color: #34d399; }
        /* Smaller rails: the six-pattern comparison, and rails in bands. */
        .es-loop-rail-xs .es-loop-day { height: 1.05rem; border-radius: 0.12rem; }
        .es-loop-rail-sm .es-loop-day { height: 1.4rem; }

        /* A loop with no end runs off the right edge. This is the only
           visual difference between "never" and the other two ends, and
           it is the whole argument of the length section. */
        .es-loop-rail-open {
            mask-image: linear-gradient(to right, black 78%, transparent 100%);
            -webkit-mask-image: linear-gradient(to right, black 78%, transparent 100%);
        }
        /* Where the loop stops: an absolute bar, positioned as a
           percentage, so the stop stays proportional to the ruler. */
        .es-loop-stop {
            position: absolute;
            top: -0.3rem;
            bottom: -0.3rem;
            width: 2px;
            background: #046b4f;
            border-radius: 1px;
        }
        .dark .es-loop-stop { background: #34d399; }
        .es-loop-stop::after {
            content: "";
            position: absolute;
            top: -0.28rem;
            left: -0.22rem;
            width: 0.66rem;
            height: 0.28rem;
            border-radius: 0.1rem;
            background: inherit;
        }

        /* The day-letter scale above a short rail. */
        .es-loop-scale {
            display: flex;
            gap: 0.4rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.56rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            color: #4a5551;
        }
        .dark .es-loop-scale { color: #9aa8a2; }
        .es-loop-scale-week {
            display: flex;
            flex: 1 1 0;
            min-width: 0;
            gap: 0.1rem;
        }
        .es-loop-scale-week span { flex: 1 1 0; min-width: 0; text-align: center; }
        /* The week ruler under a long rail: W1 W2 W3 ... */
        .es-loop-weeks {
            display: flex;
            gap: 0.4rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.58rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #4a5551;
        }
        .dark .es-loop-weeks { color: #9aa8a2; }
        .es-loop-weeks span { flex: 1 1 0; min-width: 0; }

        /* The loop coming round: a slow travelling brighten across the
           dates of one live rail. Cells rest fully painted, so this only
           adds a glow and can be killed outright. */
        @keyframes es-loop-round {
            0%, 72%, 100% { box-shadow: none; }
            8% { box-shadow: 0 0 0 2px rgba(4, 107, 79, 0.22), 0 0 14px rgba(4, 107, 79, 0.4); }
        }
        @keyframes es-loop-round-dark {
            0%, 72%, 100% { box-shadow: none; }
            8% { box-shadow: 0 0 0 2px rgba(52, 211, 153, 0.24), 0 0 14px rgba(52, 211, 153, 0.45); }
        }
        .es-loop-rail-live .es-loop-day-on {
            animation: es-loop-round 9s linear infinite;
            animation-delay: var(--d, 0s);
        }
        .dark .es-loop-rail-live .es-loop-day-on { animation-name: es-loop-round-dark; }
        .es-loop-band .es-loop-rail-live .es-loop-day-on { animation-name: es-loop-round-dark; }

        /* --- Cards --- */
        .es-loop-card {
            border: 1px solid rgba(16, 23, 20, 0.12);
            border-radius: 1rem;
            background: #ffffff;
        }
        .dark .es-loop-card {
            border-color: rgba(231, 238, 234, 0.12);
            background: rgba(231, 238, 234, 0.04);
        }
        .es-loop-band .es-loop-card {
            border-color: rgba(231, 238, 234, 0.14);
            background: rgba(231, 238, 234, 0.05);
        }

        /* --- The pinned dark bands --- */
        .es-loop-band {
            background-color: #0c1512;
            background-image: radial-gradient(120% 100% at 50% 0%, #15211d 0%, #0f1815 55%, #070c0a 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(231, 238, 234, 0.05);
        }
        .es-loop-band .es-loop-ink { color: #e7eeea; }
        .es-loop-band .es-loop-muted { color: #9aa8a2; }
        .es-loop-band .es-loop-day { background: rgba(231, 238, 234, 0.08); }
        .es-loop-band .es-loop-day-on { background: #34d399; }
        .es-loop-band .es-loop-day-gap { background: transparent; border-color: rgba(231, 238, 234, 0.3); }
        .es-loop-band .es-loop-weeks { color: #9aa8a2; }
        /* Shared classes that flip with the colour mode inside a band. */
        .es-loop-band .grid-overlay {
            background-image:
                linear-gradient(rgba(231, 238, 234, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(231, 238, 234, 0.05) 1px, transparent 1px);
        }
        .es-loop-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-loop-band .es-claim:focus-within {
            border-color: rgba(52, 211, 153, 0.75);
            box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.22);
        }

        /* --- Section mark: a ring with one bright quarter, so the
               page's own furniture reads as something coming round. --- */
        .es-loop-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.7rem;
            height: 2.7rem;
            border-radius: 9999px;
            border: 1.5px solid rgba(4, 107, 79, 0.22);
            border-top-color: #046b4f;
            color: #046b4f;
            background: #ffffff;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.8rem;
            font-weight: 800;
            letter-spacing: 0.04em;
        }
        .dark .es-loop-mark {
            border-color: rgba(52, 211, 153, 0.22);
            border-top-color: #34d399;
            color: #34d399;
            background: rgba(231, 238, 234, 0.04);
        }
        .es-loop-band .es-loop-mark {
            border-color: rgba(52, 211, 153, 0.22);
            border-top-color: #34d399;
            color: #34d399;
            background: rgba(231, 238, 234, 0.05);
        }

        /* --- Eyebrow --- */
        .es-loop-tag {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            color: #4a5551;
        }
        .dark .es-loop-tag { color: #9aa8a2; }
        .es-loop-band .es-loop-tag { color: #34d399; }

        /* --- Plan pills --- */
        .es-loop-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.25rem;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            border: 1px solid rgba(4, 107, 79, 0.4);
            color: #046b4f;
        }
        .dark .es-loop-plan { border-color: rgba(52, 211, 153, 0.42); color: #34d399; }
        .es-loop-band .es-loop-plan { border-color: rgba(52, 211, 153, 0.42); color: #34d399; }
        .es-loop-plan-pro { border-color: rgba(16, 23, 20, 0.35); color: #101714; }
        .dark .es-loop-plan-pro { border-color: rgba(231, 238, 234, 0.38); color: #e7eeea; }

        /* --- The turn ledger: what is actually stored per date --- */
        .es-loop-table { width: 100%; border-collapse: collapse; text-align: left; }
        .es-loop-th {
            padding-bottom: 0.6rem;
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #4a5551;
        }
        .dark .es-loop-th { color: #9aa8a2; }
        .es-loop-td {
            padding: 0.62rem 0.6rem 0.62rem 0;
            border-top: 1px solid rgba(16, 23, 20, 0.09);
            vertical-align: middle;
        }
        .dark .es-loop-td { border-top-color: rgba(231, 238, 234, 0.09); }
        .es-loop-num {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.8rem;
        }
        .es-loop-meter {
            height: 0.38rem;
            border-radius: 0.19rem;
            background: rgba(16, 23, 20, 0.09);
            overflow: hidden;
        }
        .dark .es-loop-meter { background: rgba(231, 238, 234, 0.1); }
        .es-loop-meter-fill {
            height: 100%;
            border-radius: 0.19rem;
            background: #046b4f;
        }
        .dark .es-loop-meter-fill { background: #34d399; }
        .es-loop-full {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #046b4f;
        }
        .dark .es-loop-full { color: #34d399; }

        /* --- Rules and dividers --- */
        .es-loop-rule-t { border-top: 1px solid rgba(16, 23, 20, 0.08); }
        .dark .es-loop-rule-t { border-top-color: rgba(231, 238, 234, 0.08); }
        .es-loop-rule-y {
            border-top: 1px solid rgba(16, 23, 20, 0.08);
            border-bottom: 1px solid rgba(16, 23, 20, 0.08);
        }
        .dark .es-loop-rule-y {
            border-top-color: rgba(231, 238, 234, 0.08);
            border-bottom-color: rgba(231, 238, 234, 0.08);
        }
        .es-loop-hr { border-top: 1px solid rgba(16, 23, 20, 0.1); }
        .dark .es-loop-hr { border-top-color: rgba(231, 238, 234, 0.12); }

        /* --- Links, buttons, hovers, chips --- */
        .es-loop-link { color: #046b4f; }
        .es-loop-link:hover { color: #101714; }
        .dark .es-loop-link { color: #34d399; }
        .dark .es-loop-link:hover { color: #e7eeea; }

        .es-loop-btn {
            background-color: #046b4f;
            color: #ffffff;
            box-shadow: 0 18px 36px -14px rgba(4, 107, 79, 0.5);
        }
        .es-loop-btn:hover { background-color: #03553f; box-shadow: 0 22px 44px -14px rgba(4, 107, 79, 0.6); }
        .dark .es-loop-btn { background-color: #34d399; color: #080f0d; }
        .dark .es-loop-btn:hover { background-color: #5fe0b0; }
        .es-loop-band .es-loop-btn { background-color: #34d399; color: #080f0d; }
        .es-loop-band .es-loop-btn:hover { background-color: #5fe0b0; }

        .es-loop-hover:hover { border-color: rgba(4, 107, 79, 0.45); }
        .dark .es-loop-hover:hover { border-color: rgba(52, 211, 153, 0.45); }
        .es-loop-hover:hover .es-loop-hover-title,
        .es-loop-hover:hover .es-loop-hover-arrow { color: #046b4f; }
        .dark .es-loop-hover:hover .es-loop-hover-title,
        .dark .es-loop-hover:hover .es-loop-hover-arrow { color: #34d399; }

        .es-loop-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            border: 1px solid rgba(16, 23, 20, 0.16);
            background: rgba(255, 255, 255, 0.7);
            color: #4a5551;
            font-size: 0.76rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .dark .es-loop-chip {
            border-color: rgba(231, 238, 234, 0.16);
            background: rgba(231, 238, 234, 0.05);
            color: #aebab4;
        }

        /* Dot-nav tooltip, in the page's own inks. */
        .es-loop-tip {
            border: 1px solid rgba(16, 23, 20, 0.12);
            background: #ffffff;
            color: #101714;
        }
        .dark .es-loop-tip {
            border-color: rgba(231, 238, 234, 0.12);
            background: #101714;
            color: #e7eeea;
        }

        /* --- Shared-system recolors (brand blue by default) --- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(4, 107, 79, 0.12), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(52, 211, 153, 0.1), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(4, 107, 79, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(52, 211, 153, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #046b4f; }
        .dark .es-dot.is-active .es-dot-pip { background: #34d399; }

        /* --- Focus rings. No border-radius here: setting it changes the
               element's own shape on focus. Outlines already follow it. --- */
        #es-loop-page a:focus-visible,
        #es-loop-page summary:focus-visible,
        #es-loop-page button:focus-visible,
        #es-loop-page input:focus-visible {
            outline: 2px solid #046b4f;
            outline-offset: 3px;
        }
        .dark #es-loop-page a:focus-visible,
        .dark #es-loop-page summary:focus-visible,
        .dark #es-loop-page button:focus-visible,
        .dark #es-loop-page input:focus-visible {
            outline-color: #34d399;
        }
        .es-loop-band a:focus-visible,
        .es-loop-band summary:focus-visible,
        .es-loop-band button:focus-visible,
        .es-loop-band input:focus-visible {
            outline-color: #34d399 !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-loop-rail-live .es-loop-day-on { animation: none !important; }
        }
    </style>

    @php
        // 2026-03-01 is a Sunday, so day index 0 is a Sunday and the weekday of
        // index $i is simply $i % 7. Every rail on this page is built from this
        // one anchor, so the dates in the copy and the cells on the rails are
        // the same arithmetic rather than two things that have to agree.
        $anchor = \Carbon\Carbon::parse('2026-03-01');

        // Build a rail: $days cells, 'on' where the pattern falls, 'gap' where a
        // date exception removes one, 'add' where an Include Date adds one.
        $rail = function (int $days, array $dows, array $gaps = [], array $adds = []) {
            $cells = [];
            for ($i = 0; $i < $days; $i++) {
                if (in_array($i, $adds, true)) {
                    $cells[] = 'add';
                } elseif (in_array($i, $gaps, true)) {
                    $cells[] = 'gap';
                } elseif (in_array($i % 7, $dows, true)) {
                    $cells[] = 'on';
                } else {
                    $cells[] = 'off';
                }
            }

            return array_chunk($cells, 7);
        };

        // Hero: six weeks of Tuesdays and Thursdays, with the Thursday of week
        // four taken out by an exception, and no end condition.
        $heroRail = $rail(42, [2, 4], [25]);
        // Counted off the rail, not asserted: 6 weeks x 2 days less the one exception.
        $heroDates = collect($heroRail)->flatten()->filter(fn ($c) => $c === 'on')->count();

        // The six frequency options, all measured on the SAME eight-week ruler.
        // The closures take the real date so monthly and yearly are derived from
        // it rather than asserted.
        $patterns = [
            ['Daily', 'Every day', 'daily', fn ($d, $i) => true],
            ['Weekly', 'Tuesdays and Thursdays', 'weekly', fn ($d, $i) => in_array((int) $d->dayOfWeek, [2, 4], true)],
            ['Every N weeks', 'Every third Tuesday', 'every_n_weeks', fn ($d, $i) => (int) $d->dayOfWeek === 2 && intdiv($i, 7) % 3 === 0],
            ['Monthly, same date', 'The 3rd of the month', 'monthly_date', fn ($d, $i) => (int) $d->day === 3],
            ['Monthly, same weekday', 'The second Tuesday', 'monthly_weekday', fn ($d, $i) => (int) $d->dayOfWeek === 2 && (int) ceil($d->day / 7) === 2],
            ['Yearly', 'Once a year, on March 3', 'yearly', fn ($d, $i) => $d->format('m-d') === '03-03'],
        ];

        $patternRows = [];
        foreach ($patterns as [$pName, $pWhen, $pCode, $pFn]) {
            $cells = [];
            $hits = 0;
            for ($i = 0; $i < 56; $i++) {
                $d = $anchor->copy()->addDays($i);
                $on = (bool) $pFn($d, $i);
                if ($on) {
                    $hits++;
                }
                $cells[] = $on ? 'on' : 'off';
            }
            $patternRows[] = [
                'name' => $pName,
                'when' => $pWhen,
                'code' => $pCode,
                'weeks' => array_chunk($cells, 7),
                'hits' => $hits,
            ];
        }

        // The three end conditions, on one five-week ruler of Wednesdays.
        // Wednesday is index 3, so the dates are 4, 11, 18, 25 March and 1 April.
        // The stop percentages are measured on the same 35-day ruler.
        $endRail = $rail(35, [3]);
        $ends = [
            ['never', 'Never', 'It keeps producing dates until you change it.', null],
            ['on_date', 'On a date', 'The last date is 25 March. Nothing after it.', 71.4],
            ['after_events', 'After a number of dates', 'Three dates, then it is done.', 51.4],
        ];

        // Date exceptions, on one three-week ruler of Tuesdays.
        $excludeRail = $rail(21, [2], [9]);
        $includeRail = $rail(21, [2], [], [12]);
        $dayLetters = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];

        // The subscribe feed: FeedController walks now -> now+90 days, so 91 calendar
        // days, and writes a VEVENT for every date matchesDate() accepts. Thirteen weeks
        // of Tuesdays and Thursdays is 26, less the two dates one Exclude Date week takes
        // out. The entry count is COUNTED off that rail rather than written into the copy,
        // because the two drifted apart once already.
        $feedRail = $rail(91, [2, 4], [51, 53]);
        $feedEntries = collect($feedRail)->flatten()->filter(fn ($c) => $c === 'on')->count();

        // The turn ledger. One ticket type, quantity 40, on one recurring event.
        // 26 March is not a row: the date exception took it out.
        $turnQty = 40;
        $turns = [
            ['Thu 5 Mar', 38],
            ['Thu 12 Mar', 40],
            ['Thu 19 Mar', 22],
            ['Thu 2 Apr', 9],
            ['Thu 9 Apr', 4],
            ['Thu 16 Apr', 0],
        ];

        $faqs = [
            [
                'q' => 'What recurrence patterns are available?',
                'a' => 'Six. Daily, weekly on the days of the week you tick, every N weeks on the days you tick (from 2 to 52 weeks), monthly on the same date, monthly on the same weekday, and yearly. The day-of-week checkboxes only appear for the weekly and every-N-weeks patterns; the monthly and yearly patterns take their day from the event\'s own start date, so a monthly event that starts on the second Tuesday keeps landing on the second Tuesday.',
            ],
            [
                'q' => 'Can I change a single date without changing the rest?',
                'a' => 'Not from inside the series, and it is worth knowing exactly where the line falls. A recurring event is one record: the name, the start time, the length, the description and the ticket types belong to the whole loop, so editing the start time moves every date. What is genuinely per date is the part that has to be: ticket inventory, registration capacity, check-in, and each date\'s own page. If one date is really a different event, take it out with an Exclude Date and add it back as its own event.',
            ],
            [
                'q' => 'Can I add or skip specific dates in a recurring series?',
                'a' => 'Yes. Exclude Dates take a date out of the pattern, and Include Dates add a date the pattern would not have produced. Exclude wins if a date is in both lists, and an included date is kept even when it falls outside the pattern or past the end condition, so a one-off extra session does not need the loop rebuilt around it.',
            ],
            [
                'q' => 'What do guests see on a date I removed?',
                'a' => 'Nothing. The date is simply not on the calendar. There is no struck-through row and no cancellation notice, so an excluded date reads as "we are not on that week" rather than as something having gone wrong.',
            ],
            [
                'q' => 'Can two sittings on the same day be one recurring event?',
                'a' => 'No, and that is worth saying plainly. A recurring event carries one start time and one length, so it produces at most one date per day it falls on. A matinee and an evening, or a morning class and a lunchtime class, are two events with two patterns. It is slightly more setup, and it keeps the two start times and their tickets properly apart.',
            ],
            [
                'q' => 'How do recurring events reach other calendars?',
                'a' => 'Two-way sync with Google Calendar, Outlook and Microsoft 365, or any CalDAV server is free on every plan, and it carries the event record itself across. What it does not do is hand the connected calendar a repeat rule, so do not expect the series to appear there as a repeating entry. The place a loop is expanded date by date is your schedule\'s iCal feed: it writes one entry per date for the next 90 days, each linking to that date\'s own page. Every event page also offers a download for the single date somebody is looking at, sharing its identifier with the feed so a calendar app updates the entry it already has.',
            ],
            [
                'q' => 'Can one ticket cover every date of the loop?',
                'a' => 'Yes, on the Pro plan. A season pass can be set to cover every date of the loop, once per date, and it is sold alongside single-date tickets rather than instead of them. Advance booking is off by default, which means the pass is scan-at-the-door until you switch it on; switching it on is also what lets you cap how many seats pass holders may reserve on a given date.',
            ],
        ];

        $dotSections = [
            ['top', 'The loop'],
            ['unit', 'One record'],
            ['shape', 'The shape'],
            ['length', 'The length'],
            ['gaps', 'The gaps'],
            ['turns', 'Every turn'],
            ['out', 'Leaving the building'],
            ['rest', 'Everything else'],
            ['faq', 'Questions'],
            ['claim', 'Start the loop'],
        ];
    @endphp

    <div id="es-loop-page" class="es-loop-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: one loop, drawn on a continuous day rail            -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(4, 107, 79, 0.2), rgba(4, 107, 79, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 72% 42%, rgba(52, 211, 153, 0.14), rgba(52, 211, 153, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1fr_1.05fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="h-5 w-5 text-[#046b4f] dark:text-[#34d399]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span class="es-loop-muted text-sm font-medium tracking-wide">Recurring events</span>
                    </div>

                    <h1 class="es-balance es-loop-ink mb-8 text-[2.5rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">It comes round.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">Type it <span class="es-loop-accent">once.</span></span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-loop-muted mb-10 max-w-xl text-lg sm:text-xl">
                        A weekly quiz, a monthly market, a class on Tuesdays and Thursdays. Give the loop a shape, a length and its gaps, and every date after that draws itself.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="{{ route('marketing.docs.creating_events') }}" class="glass es-loop-ink group inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            Read the Events guide
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                        <a href="{{ app_url('/sign_up') }}" class="es-loop-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Start for free
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>

                    <p class="es-fade-up es-d-4 es-loop-muted mt-6 text-sm">
                        <a href="#shape" class="es-loop-link font-semibold hover:underline">See the six patterns on one ruler</a>
                    </p>
                </div>

                <!-- The rail. Six weeks, proportional, one date taken out. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-loop-card p-6 sm:p-7">
                        <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                            <h2 class="es-loop-ink text-lg font-bold">Quiz Night</h2>
                            <span class="es-loop-muted es-loop-num">Tue + Thu &middot; 19:30</span>
                        </div>
                        <p class="es-loop-muted mb-5 text-sm">Weekly, no end date, one date taken out in week four.</p>

                        <div aria-hidden="true">
                            <div class="es-loop-weeks mb-1">
                                @foreach (range(1, 6) as $wk)
                                    <span>W{{ $wk }}</span>
                                @endforeach
                            </div>
                            @php $heroIndex = 0; @endphp
                            <div class="es-loop-rail es-loop-rail-live es-loop-rail-open">
                                @foreach ($heroRail as $week)
                                    <div class="es-loop-week">
                                        @foreach ($week as $cell)
                                            @php $heroDelay = number_format($heroIndex * 0.16, 2); $heroIndex++; @endphp
                                            @if ($cell === 'on')
                                                <div class="es-loop-day es-loop-day-on" style="--d: {{ $heroDelay }}s;"></div>
                                            @elseif ($cell === 'gap')
                                                <div class="es-loop-day es-loop-day-gap"></div>
                                            @else
                                                <div class="es-loop-day"></div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                            <p class="es-loop-muted mt-2 text-[0.68rem]">
                                Filled cells are dates. The dashed cell is an Exclude Date, so that Thursday is simply not there. The rail runs off the edge because the end condition is "never".
                            </p>
                        </div>

                        <p class="es-loop-muted es-loop-hr mt-5 pt-4 text-xs">
                            <span class="es-loop-ink font-semibold">{{ $heroDates }} dates on the calendar. One record behind them.</span>
                            Change the time once and all {{ $heroDates }} move together.
                        </p>
                    </div>
                </div>
            </div>

            <!-- What people actually put on a loop -->
            <div class="es-fade-up es-d-4 mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach (['Quiz night', 'Yoga class', 'Farmers market', 'Open mic', 'Book club', 'Bingo', 'Chess club', 'Life drawing', 'Story time', 'Monthly meetup'] as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-loop-chip">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. One record, many turns (pinned dark band)                 -->
    <!-- ============================================================ -->
    <section id="unit" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-loop-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-loop-mark mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                    <p class="es-loop-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The unit</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        The unit is the loop, <span class="es-loop-lit">not the date.</span>
                    </h2>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    <div class="es-loop-card p-6" data-reveal="panel">
                        <p class="es-loop-tag mb-3">What you type</p>
                        <h3 class="es-loop-ink mb-2 text-lg font-bold">
                            <span data-count-to="1">1</span> event
                        </h3>
                        <p class="es-loop-muted text-sm">A pattern, an end and any exceptions. Fifty-two Tuesdays entered by hand is fifty-two chances to mistype a time.</p>
                    </div>
                    <div class="es-loop-card p-6" data-reveal="panel">
                        <p class="es-loop-tag mb-3">What comes out</p>
                        <h3 class="es-loop-ink mb-2 text-lg font-bold">
                            <span data-count-to="6">6</span> patterns
                        </h3>
                        <p class="es-loop-muted text-sm">Daily through yearly, with day-of-week ticks on the two weekly patterns and an interval of up to fifty-two weeks.</p>
                    </div>
                    <div class="es-loop-card p-6" data-reveal="panel">
                        <p class="es-loop-tag mb-3">How it stops</p>
                        <h3 class="es-loop-ink mb-2 text-lg font-bold">
                            <span data-count-to="3">3</span> ends
                        </h3>
                        <p class="es-loop-muted text-sm">Never, a last date, or a number of dates. A loop with an end is the difference between a season and a calendar nobody trusts.</p>
                    </div>
                </div>

                <p class="mt-10 text-center text-gray-300" data-reveal>
                    Shape, length, gaps. Three settings, all on the free plan.
                    <a href="#shape" class="es-loop-lit inline-flex items-center gap-1 font-semibold transition-all hover:gap-2">
                        Start with the shape
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The shape: six patterns on one ruler                      -->
    <!-- ============================================================ -->
    <section id="shape" class="es-loop-ruled scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-loop-mark mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                <p class="es-loop-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The shape</p>
                <h2 class="es-balance es-loop-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Six patterns, <span class="es-loop-accent">one ruler.</span>
                </h2>
                <p class="es-loop-muted mx-auto mt-5 max-w-2xl text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    The same eight weeks, measured six ways. A month grid would draw all six the same size. On a ruler you can see what you are choosing.
                </p>
            </div>

            <div class="es-loop-card p-5 sm:p-8" data-reveal="panel">
                <table class="es-loop-table">
                    <caption class="es-loop-muted mb-4 text-start text-xs">
                        One eight-week window, 1 March to 25 April 2026, with the dates each pattern produces inside it.
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col" class="es-loop-th">Frequency</th>
                            <th scope="col" class="es-loop-th hidden sm:table-cell">Set to</th>
                            <th scope="col" class="es-loop-th text-end">Dates</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($patternRows as $row)
                            <tr>
                                <th scope="row" class="es-loop-td es-loop-ink text-sm font-bold">
                                    {{ $row['name'] }}
                                    <span class="es-loop-muted es-loop-num block text-[0.66rem] font-normal">{{ $row['code'] }}</span>
                                </th>
                                <td class="es-loop-td es-loop-muted hidden text-xs sm:table-cell">{{ $row['when'] }}</td>
                                <td class="es-loop-td es-loop-ink es-loop-num text-end font-bold">{{ $row['hits'] }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="pb-4">
                                    <div class="es-loop-rail es-loop-rail-xs" aria-hidden="true">
                                        @foreach ($row['weeks'] as $week)
                                            <div class="es-loop-week">
                                                @foreach ($week as $cell)
                                                    <div class="es-loop-day @if ($cell === 'on') es-loop-day-on @endif"></div>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-8 grid gap-6 md:grid-cols-2" data-reveal-group="90">
                <div class="es-loop-card p-6" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-loop-ink text-lg font-bold">Where the day ticks apply</h3>
                        <span class="es-loop-plan">Free</span>
                    </div>
                    <p class="es-loop-muted text-sm">
                        The seven day-of-week checkboxes appear for weekly and every-N-weeks, because those are the patterns that need them. Monthly and yearly take their day from the event's own start date, so an event that starts on the second Tuesday keeps landing on the second Tuesday.
                    </p>
                </div>
                <div class="es-loop-card p-6" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-loop-ink text-lg font-bold">Every other week, or every fifth</h3>
                        <span class="es-loop-plan">Free</span>
                    </div>
                    <p class="es-loop-muted text-sm">
                        Every N weeks takes an interval from 2 to 52 alongside the day ticks. Alternate Fridays, a market every third Saturday and a quarterly meeting all live here rather than in six separate events.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. The length: where the loop stops                          -->
    <!-- ============================================================ -->
    <section id="length" class="es-loop-rule-y scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-loop-mark mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                <p class="es-loop-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The length</p>
                <h2 class="es-balance es-loop-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Give the loop <span class="es-loop-accent">an end.</span>
                </h2>
                <p class="es-loop-muted mx-auto mt-5 max-w-2xl text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Same five weeks of Wednesdays, three ways of finishing. Two of them stop on their own; one of them is still going next February unless somebody remembers.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-3" data-reveal-group="100">
                @foreach ($ends as [$endCode, $endName, $endBody, $endStop])
                    {{-- flex flex-col so the mt-auto below actually bites: the three bodies are
                         different lengths and the rails have to line up across the row. --}}
                    <div class="es-loop-card flex flex-col p-6" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="es-loop-ink text-lg font-bold">{{ $endName }}</h3>
                            <span class="es-loop-plan">Free</span>
                        </div>
                        <p class="es-loop-muted mb-5 text-sm">{{ $endBody }}</p>

                        <div class="mt-auto" aria-hidden="true">
                            <div class="es-loop-rail es-loop-rail-sm @if ($endStop === null) es-loop-rail-open @endif">
                                @foreach ($endRail as $wi => $week)
                                    <div class="es-loop-week">
                                        @foreach ($week as $ci => $cell)
                                            @php
                                                $flat = $wi * 7 + $ci;
                                                $live = $cell === 'on' && ($endStop === null || ($flat / 35 * 100) < $endStop);
                                            @endphp
                                            <div class="es-loop-day @if ($live) es-loop-day-on @endif"></div>
                                        @endforeach
                                    </div>
                                @endforeach
                                @if ($endStop !== null)
                                    <div class="es-loop-stop" style="left: {{ $endStop }}%;"></div>
                                @endif
                            </div>
                            <p class="es-loop-muted es-loop-num mt-2 text-[0.66rem]">{{ $endCode }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <p class="es-loop-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                "Never" is a perfectly good answer for a weekly night that has no planned last date. It is just worth choosing it on purpose rather than by default.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. The gaps: exceptions in both directions                   -->
    <!-- ============================================================ -->
    <section id="gaps" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-loop-mark mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                <p class="es-loop-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The gaps</p>
                <h2 class="es-balance es-loop-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    A week off is <span class="es-loop-accent">a gap</span>, not a notice.
                </h2>
                <p class="es-loop-muted mx-auto mt-5 max-w-2xl text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Date exceptions edit the loop in both directions, and neither of them leaves a mark on your public calendar.
                </p>
            </div>

            <div class="grid gap-6 lg:grid-cols-2" data-reveal-group="100">
                <div class="es-loop-card flex flex-col p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-loop-ink text-lg font-bold">Exclude Dates</h3>
                        <span class="es-loop-plan">Free</span>
                    </div>
                    <p class="es-loop-muted mb-6 text-sm">
                        Take a date out and the day is simply absent. No struck-through row, no cancellation banner, nothing for a guest to interpret. Closed for the bank holiday reads exactly like a week you were never on.
                    </p>

                    <div class="mt-auto" aria-hidden="true">
                        <div class="es-loop-scale mb-1">
                            @foreach ($excludeRail as $week)
                                <div class="es-loop-scale-week">
                                    @foreach ($dayLetters as $letter)
                                        <span>{{ $letter }}</span>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                        <div class="es-loop-rail">
                            @foreach ($excludeRail as $week)
                                <div class="es-loop-week">
                                    @foreach ($week as $cell)
                                        @if ($cell === 'on')
                                            <div class="es-loop-day es-loop-day-on"></div>
                                        @elseif ($cell === 'gap')
                                            <div class="es-loop-day es-loop-day-gap"></div>
                                        @else
                                            <div class="es-loop-day"></div>
                                        @endif
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                        <p class="es-loop-muted mt-2 text-[0.68rem]">Tuesdays, with the middle one excluded. That is the whole change.</p>
                    </div>
                </div>

                <div class="es-loop-card flex flex-col p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-loop-ink text-lg font-bold">Include Dates</h3>
                        <span class="es-loop-plan">Free</span>
                    </div>
                    <p class="es-loop-muted mb-6 text-sm">
                        Add a date the pattern would never produce. An included date is kept even when it falls outside the pattern or past the end condition, so a one-off extra session does not need the loop rebuilt around it.
                    </p>

                    <div class="mt-auto" aria-hidden="true">
                        <div class="es-loop-scale mb-1">
                            @foreach ($includeRail as $week)
                                <div class="es-loop-scale-week">
                                    @foreach ($dayLetters as $letter)
                                        <span>{{ $letter }}</span>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                        <div class="es-loop-rail">
                            @foreach ($includeRail as $week)
                                <div class="es-loop-week">
                                    @foreach ($week as $cell)
                                        @if ($cell === 'on')
                                            <div class="es-loop-day es-loop-day-on"></div>
                                        @elseif ($cell === 'add')
                                            <div class="es-loop-day es-loop-day-add"></div>
                                        @else
                                            <div class="es-loop-day"></div>
                                        @endif
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                        <p class="es-loop-muted mt-2 text-[0.68rem]">The outlined cell is a Friday the weekly pattern does not include.</p>
                    </div>
                </div>
            </div>

            <p class="es-loop-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                If a date somehow lands in both lists, Exclude wins. That is deliberate: the list that removes a date should always be the one you can trust.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Every turn keeps its own count                            -->
    <!-- ============================================================ -->
    <section id="turns" class="es-loop-ruled es-loop-rule-t scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-loop-mark mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                <p class="es-loop-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Every turn</p>
                <h2 class="es-balance es-loop-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    One record. <span class="es-loop-accent">Separate counts.</span>
                </h2>
                <p class="es-loop-muted mx-auto mt-5 max-w-2xl text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    The loop is one event, but the numbers that matter are counted date by date. Next Thursday selling out does not close the one after it.
                </p>
            </div>

            <div class="grid items-start gap-8 lg:grid-cols-[1.2fr_1fr]">
                <div class="es-loop-card p-5 sm:p-7" data-reveal="panel">
                    <table class="es-loop-table">
                        <caption class="es-loop-muted mb-4 text-start text-xs">
                            One ticket type, quantity 40, on one recurring event. Each date counts down on its own.
                        </caption>
                        <thead>
                            <tr>
                                <th scope="col" class="es-loop-th">Date</th>
                                <th scope="col" class="es-loop-th text-end">Sold</th>
                                <th scope="col" class="es-loop-th text-end">Left</th>
                                <th scope="col" class="es-loop-th hidden w-2/5 sm:table-cell"><span class="sr-only">Share of the allowance sold</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($turns as [$turnDate, $turnSold])
                                @php
                                    $turnLeft = $turnQty - $turnSold;
                                    $turnPct = (int) round($turnSold / $turnQty * 100);
                                @endphp
                                <tr>
                                    <th scope="row" class="es-loop-td es-loop-ink es-loop-num font-bold">{{ $turnDate }}</th>
                                    <td class="es-loop-td es-loop-ink es-loop-num text-end">{{ $turnSold }}</td>
                                    <td class="es-loop-td es-loop-num text-end">
                                        @if ($turnLeft === 0)
                                            <span class="es-loop-full">Full</span>
                                        @else
                                            <span class="es-loop-muted">{{ $turnLeft }}</span>
                                        @endif
                                    </td>
                                    <td class="es-loop-td hidden sm:table-cell">
                                        <div class="es-loop-meter" aria-hidden="true">
                                            <div class="es-loop-meter-fill" style="width: {{ $turnPct }}%;"></div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <p class="es-loop-muted es-loop-hr mt-4 pt-4 text-xs">
                        26 March is not a row on this table. An Exclude Date took it out, so there is no date to sell.
                    </p>
                </div>

                <div class="grid gap-4" data-reveal-group="90">
                    <div class="es-loop-card p-6" data-reveal="panel">
                        <p class="es-loop-tag mb-3">Per date</p>
                        <h3 class="es-loop-ink mb-3 text-lg font-bold">What each turn owns</h3>
                        <ul class="es-loop-muted space-y-2 text-sm">
                            <li class="flex gap-2.5">
                                <svg aria-hidden="true" class="mt-0.5 h-4 w-4 flex-none text-[#046b4f] dark:text-[#34d399]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span>Ticket inventory, counted against the same allowance from zero on every date.</span>
                            </li>
                            <li class="flex gap-2.5">
                                <svg aria-hidden="true" class="mt-0.5 h-4 w-4 flex-none text-[#046b4f] dark:text-[#34d399]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span>Free registration and its capacity limit, with the seats left worked out for that date.</span>
                            </li>
                            <li class="flex gap-2.5">
                                <svg aria-hidden="true" class="mt-0.5 h-4 w-4 flex-none text-[#046b4f] dark:text-[#34d399]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span>Check-in, which opens on the date you are working and counts only that date's arrivals.</span>
                            </li>
                            <li class="flex gap-2.5">
                                <svg aria-hidden="true" class="mt-0.5 h-4 w-4 flex-none text-[#046b4f] dark:text-[#34d399]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span>A page of its own, so you can send somebody the link to one date rather than to the series.</span>
                            </li>
                        </ul>
                    </div>

                    <div class="es-loop-card p-6" data-reveal="panel">
                        <p class="es-loop-tag mb-3">Shared</p>
                        <h3 class="es-loop-ink mb-3 text-lg font-bold">What the loop owns</h3>
                        <p class="es-loop-muted text-sm">
                            The name, the start time, the length, the description, the venue and the ticket types belong to the whole loop. Edit one of those and every date follows, which is the point.
                        </p>
                        <p class="es-loop-muted es-loop-hr mt-4 pt-4 text-xs">
                            Worth being exact about one thing: a ticket type's sales window is a single start and end moment for the type, not a window recalculated for each date. Inventory and capacity count per date. The window does not.
                        </p>
                    </div>
                </div>
            </div>

            <p class="es-loop-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                Registration with a capacity limit is free on every plan, and so is selling: 25 paid tickets a month per schedule, counted across the loop's dates rather than reset by each one. {{ plan_price($proMonthly) }} a month takes that ceiling off and adds check-in and the waitlist, and Event Schedule takes zero platform fees on ticket sales on every plan.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. The loop leaves the building (pinned dark band)           -->
    <!-- ============================================================ -->
    <section id="out" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-loop-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-loop-mark mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                    <p class="es-loop-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Leaving the building</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        One record here. <span class="es-loop-lit">Ninety days out there.</span>
                    </h2>
                    <p class="mx-auto mt-5 max-w-2xl text-lg text-gray-300" data-reveal style="--reveal-delay: 0.15s;">
                        Your schedule publishes a calendar feed anyone can subscribe to, and that is where the loop is unrolled date by date.
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="100">
                    <div class="es-loop-card p-6" data-reveal="panel">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="es-loop-ink text-lg font-bold">The subscribe feed</h3>
                            <span class="es-loop-plan">Free</span>
                        </div>
                        <p class="es-loop-muted text-sm">The iCal feed writes one entry per date for the next 90 days, each one linking to that date's own page. Subscribe once and the dates keep arriving.</p>
                    </div>
                    <div class="es-loop-card p-6" data-reveal="panel">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="es-loop-ink text-lg font-bold">One date at a time</h3>
                            <span class="es-loop-plan">Free</span>
                        </div>
                        <p class="es-loop-muted text-sm">Every event page offers a download for the single date somebody is looking at. It shares its identifier with the feed, so a calendar app updates the entry it already has instead of adding a second one.</p>
                    </div>
                    <div class="es-loop-card p-6" data-reveal="panel">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="es-loop-ink text-lg font-bold">Your own calendar</h3>
                            <span class="es-loop-plan">Free</span>
                        </div>
                        <p class="es-loop-muted text-sm">Two-way sync with Google Calendar, Outlook and Microsoft 365, or any CalDAV server, on every plan. It syncs the event record itself and brings edits back. It does not hand the connected calendar a repeat rule, so the feed above is the one that unrolls the dates.</p>
                    </div>
                </div>

                <div class="es-loop-card mt-6 p-6 sm:p-7" data-reveal="panel">
                    <div class="grid items-center gap-6 lg:grid-cols-[1fr_1.1fr]">
                        <div>
                            <h3 class="es-loop-ink mb-2 text-lg font-bold">Ninety days of one weekly loop</h3>
                            <p class="es-loop-muted text-sm">Thirteen weeks, two dates a week, one week taken out for a holiday. {{ $feedEntries }} entries in the feed, from one thing you typed.</p>
                        </div>
                        <div aria-hidden="true">
                            @php $feedIndex = 0; @endphp
                            <div class="es-loop-weeks mb-1">
                                @foreach (range(1, 13) as $feedWk)
                                    <span>W{{ $feedWk }}</span>
                                @endforeach
                            </div>
                            <div class="es-loop-rail es-loop-rail-xs es-loop-rail-live">
                                @foreach ($feedRail as $week)
                                    <div class="es-loop-week">
                                        @foreach ($week as $cell)
                                            @php $feedDelay = number_format($feedIndex * 0.07, 2); $feedIndex++; @endphp
                                            @if ($cell === 'on')
                                                <div class="es-loop-day es-loop-day-on" style="--d: {{ $feedDelay }}s;"></div>
                                            @elseif ($cell === 'gap')
                                                <div class="es-loop-day es-loop-day-gap"></div>
                                            @else
                                                <div class="es-loop-day"></div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                            <p class="es-loop-muted mt-2 text-[0.66rem]">One entry per filled cell. The two dashed cells are the week that is excluded.</p>
                        </div>
                    </div>
                </div>

                <p class="mt-10 text-center text-gray-300" data-reveal>
                    The feed is the side guests subscribe to. For the calendar you work out of,
                    <a href="{{ marketing_url('/features/calendar-sync') }}" class="es-loop-lit font-semibold hover:underline">connect it directly instead</a>.
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
                <div class="es-loop-mark mb-6" data-reveal aria-hidden="true"><span>08</span></div>
                <p class="es-loop-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Everything else</p>
                <h2 class="es-balance es-loop-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Once the loop is running.
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">
                <!-- 1 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-loop-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-loop-ink text-xl font-bold">One pass for the whole loop</h3>
                                <span class="es-loop-plan es-loop-plan-pro">Pro</span>
                            </div>
                            <p class="es-loop-muted mb-4">Because the loop is one record, a season pass can be tied to it: valid for every date of that loop, once per date. Sold next to single-date tickets rather than instead of them. If you would rather sell a fixed number of visits, a pass can carry a use count instead, so ten classes on one code works too.</p>
                            <p class="es-loop-muted text-sm">Each redemption is stored against the date it happened on, so you can see which weeks the pass holders actually turned up to. Advance booking is a separate switch, off until you turn it on, and it is the one that lets you cap how many seats pass holders may reserve on any one date.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-loop-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-loop-ink text-xl font-bold">When one date fills</h3>
                                <span class="es-loop-plan es-loop-plan-pro">Pro</span>
                            </div>
                            <p class="es-loop-muted">Turn the waitlist on and people join once that date is gone. If a place comes back, they hear about it automatically.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-loop-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-loop-ink text-xl font-bold">Not announced yet</h3>
                                <span class="es-loop-plan">Free</span>
                            </div>
                            <p class="es-loop-muted">A loop you are not ready to publish sits on your own calendar as a draft: visible to you and to anyone signed in on the schedule, and to no guest at all until you say so.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-loop-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-loop-ink text-xl font-bold">Tell the regulars</h3>
                                <span class="es-loop-plan">Free</span>
                            </div>
                            <p class="es-loop-muted mb-4">People follow your schedule, and you write to them when the pattern changes or a new season of the class opens. Open and click rates afterwards tell you whether it landed.</p>
                            <p class="es-loop-muted text-sm">The number worth knowing: 10 emails a month on Free, 100 on Pro and 1,000 on Enterprise, counted per recipient rather than per send. Nothing is sent on your behalf, so a new date never mails anybody without you writing it.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-loop-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-loop-ink text-xl font-bold">On the site you have</h3>
                                <span class="es-loop-plan">Free</span>
                            </div>
                            <p class="es-loop-muted">Embed the calendar on your own site and the loop's dates show up there too, without a second place to keep them right.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-loop-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-loop-ink text-xl font-bold">A loop you can copy</h3>
                                <span class="es-loop-plan">Free</span>
                            </div>
                            <p class="es-loop-muted mb-4">Clone a recurring event and the pattern comes with it, frequency, interval, end condition and both date-exception lists, so next term's class starts from this term's settings rather than from an empty form. Put the beginners' loop and the improvers' loop in different sub-schedules and guests can colour-code and filter between them on the same page.</p>
                            <p class="es-loop-muted text-sm">
                                Running the class online as well? Mark it as an online event and paste the link.
                                <a href="{{ marketing_url('/features/online-events') }}" class="es-loop-link font-medium hover:underline">How online events work</a>
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
    <!-- 9. Three steps                                               -->
    <!-- ============================================================ -->
    <section class="es-loop-rule-t py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance es-loop-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal>
                    Three settings
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="120">
                @foreach ([
                    ['01', 'Pick the shape', 'Choose one of the six frequencies. For weekly and every-N-weeks, tick the days it falls on and set the interval.'],
                    ['02', 'Pick the length', 'Never, a last date, or a number of dates. This is the setting that stops a loop selling tickets into next year.'],
                    ['03', 'Pick the gaps', 'Exclude the weeks you are off and include the one-offs the pattern would miss. Exclude wins if a date is in both.'],
                ] as [$stepNum, $stepTitle, $stepBody])
                    <div class="es-loop-card p-7" data-reveal="panel">
                        <div class="es-loop-accent es-loop-num mb-3 text-2xl font-black">{{ $stepNum }}</div>
                        <h3 class="es-loop-ink mb-2 text-lg font-bold">{{ $stepTitle }}</h3>
                        <p class="es-loop-muted text-sm leading-relaxed">{{ $stepBody }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @include('marketing.partials.pricing-nudge')

    <!-- ============================================================ -->
    <!-- 10. Related features                                         -->
    <!-- ============================================================ -->
    <section class="es-loop-rule-t py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-loop-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card
                        name="Calendar Sync"
                        description="Two-way sync with Google Calendar, Outlook and CalDAV"
                        :url="marketing_url('/features/calendar-sync')"
                        icon-color="blue"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Ticketing"
                        description="Inventory per date, QR check-in, and zero platform fees"
                        :url="marketing_url('/features/ticketing')"
                        icon-color="emerald"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Online Events"
                        description="Mark the loop as online and every date carries the same joining link"
                        :url="marketing_url('/features/online-events')"
                        icon-color="sky"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Custom Fields"
                        description="Ask what the class needs to know at the point of booking"
                        :url="marketing_url('/features/custom-fields')"
                        icon-color="teal"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-loop-link inline-flex items-center font-medium hover:underline">
                    See all features
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 11. Popular with                                             -->
    <!-- ============================================================ -->
    <section class="es-loop-rule-t py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-loop-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Popular with</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3" data-reveal-group="70">
                @foreach ([
                    ['/for-bars', 'Bars', 'The quiz, the pub band and the Sunday roast, each on their own loop.'],
                    ['/for-fitness-and-yoga', 'Fitness and Yoga', 'A weekly timetable where every class counts its own places.'],
                    ['/for-libraries', 'Libraries', 'Story time, the book club and the monthly talk on one calendar.'],
                ] as [$popHref, $popName, $popBlurb])
                    <a href="{{ marketing_url($popHref) }}" class="es-loop-hover es-loop-card group flex flex-col p-6 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-loop-hover-title es-loop-ink mb-2 font-bold transition-colors">For {{ $popName }}</span>
                        <span class="es-loop-muted mb-4 text-sm">{{ $popBlurb }}</span>
                        <span class="es-loop-hover-arrow es-loop-muted mt-auto inline-flex items-center gap-1 text-xs font-semibold uppercase tracking-[0.14em] transition-colors">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
            <div class="mt-8 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-loop-link inline-flex items-center font-medium hover:underline">
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

    <section id="faq" class="es-loop-rule-t scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="es-loop-mark mb-6" data-reveal aria-hidden="true"><span>09</span></div>
                <h2 class="es-balance es-loop-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-loop-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    Everything people ask before they put a regular event on a pattern.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-loop-hover es-loop-card group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-loop-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-loop-accent es-loop-num flex-none font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-loop-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-loop-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-loop-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 13. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-loop-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="absolute inset-x-8 bottom-8 opacity-50" style="mask-image: linear-gradient(to right, transparent, black 14%, black 86%, transparent); -webkit-mask-image: linear-gradient(to right, transparent, black 14%, black 86%, transparent);">
                        @php $finaleIndex = 0; @endphp
                        <div class="es-loop-rail es-loop-rail-xs es-loop-rail-live">
                            @foreach ($rail(56, [2, 4]) as $week)
                                <div class="es-loop-week">
                                    @foreach ($week as $cell)
                                        @php $finaleDelay = number_format($finaleIndex * 0.1, 2); $finaleIndex++; @endphp
                                        @if ($cell === 'on')
                                            <div class="es-loop-day es-loop-day-on" style="--d: {{ $finaleDelay }}s;"></div>
                                        @else
                                            <div class="es-loop-day"></div>
                                        @endif
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="relative z-10">
                    <p class="es-loop-tag mb-4">Free forever</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Set the loop once. <span class="es-loop-lit">Stop retyping Tuesdays.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300">
                        Patterns, end conditions and date exceptions are free forever, and the free plan sells 25 paid tickets a month. {{ plan_price($proMonthly) }} a month takes the ceiling off and opens check-in, and nothing is taken from the door on either.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-400 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-loop-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Get started free
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

    <!-- Desktop dot nav -->
    <nav class="es-dotnav fixed top-1/2 z-40 hidden -translate-y-1/2 lg:block ltr:right-5 rtl:left-5" aria-label="Page sections">
        <ul class="glass flex flex-col items-center gap-1.5 rounded-full px-2 py-3">
            @foreach ($dotSections as [$sectionId, $sectionLabel])
                <li class="relative">
                    <a href="#{{ $sectionId }}" class="es-dot group block rounded-full" aria-label="{{ $sectionLabel }}">
                        <span class="es-dot-pip block h-2 w-2 rounded-full bg-gray-400/60 dark:bg-white/30"></span>
                        <span class="es-loop-tip pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
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
