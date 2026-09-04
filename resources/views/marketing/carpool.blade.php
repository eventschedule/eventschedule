<x-marketing-layout>
    <x-slot name="title">Event Carpool Matching - Event Schedule</x-slot>
    <x-slot name="description">Attendees offer and request lifts on the event page itself. The driver approves each rider before any contact details are shared, and reviews follow after.</x-slot>
    <x-slot name="breadcrumbTitle">Carpool</x-slot>

    <x-slot name="structuredData">
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule Carpool Matching",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Ride Sharing Coordination Software",
        "operatingSystem": "Web",
        "description": "Attendees offer and request lifts on the event page itself. The driver approves each rider before any contact details are shared, and reviews follow after.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Free plan available, carpool matching on the Pro plan"
        },
        "featureList": [
            "Ride offers with a city, a direction and a number of spots from 1 to 10",
            "To-event, from-event and round trip directions",
            "Rider requests with an optional message, approved or declined by the driver",
            "Contact details exchanged only once the driver approves a rider",
            "Spots counted per offer, and per occurrence date on a repeating event",
            "Email notification on every request, approval and decline, and to every rider when an offer is pulled",
            "An hourly reminder job for rides inside the next 24 hours",
            "One-time carpool disclaimer before anyone can offer or request a ride",
            "Post-event 1 to 5 star ratings between people who shared the ride",
            "Reports and offer removal from the event's Engagement tab",
            "A My Carpools page listing every ride a person has offered or asked for"
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
           Carpool "Four Seats" styles.

           CONCEPT: the cheapest capacity in live events is already
           parked outside. Someone is driving to the show with three
           empty seats behind them, and someone else is not coming
           because there is no way to get there. The whole feature is
           one small number - total_spots, 1 to 10 - and the page is
           built on that number: a SPOT LEDGER of exactly that many
           cells, filled one at a time as the driver approves people.

           WHY A LEDGER AND NOT A SEATING PLAN: riders never pick a
           seat. They ask for "a spot" and the driver says yes, so the
           cells are a COUNT, never a chooser. Allocated seating does
           exist (Enterprise, for venues with rows in them), but a car
           has none and this page must not imply otherwise.
           /for-restaurants deliberately avoided a row of 24 marks
           because at that length it reads as an inventory chart; four
           cells in a car is the literal data model, and the count is
           always printed as words beside it. The h1 says "three seats
           empty" because the hero offer is four spots with one rider
           approved; if $ride changes, the h1 changes with it.

           THE SPINE IS PRIVACY, not matching. There is no routing, no
           map and no payment splitting in the code, so the argument the
           page makes is the one the code actually makes: a name and a
           meeting point are public to signed-in attendees, and an email
           address appears only after the driver says yes. That is the
           duplex in section 03 - the same offer row twice, once before
           and once after approval.

           ANTI-COLLISION: /features/calendar-sync already owns "The
           Round Trip", so round trip is named as a direction here and
           never made into the page's metaphor. No dashboard, no
           stat tiles, no timeline: the two structural devices are the
           ledger and a real <table> of the request lifecycle.

           COLOUR: the page's existing emerald family, pulled deeper and
           more muted (#0e6349, hue 158) so it does not sit on top of
           /for-theaters' heritage green (#14532d, hue 145) or
           /for-nightclubs' exit-sign green. Measured against the real
           grounds this page paints, never against pure white:
             light  #0f1512 ink 16.94 on ground / 17.9 on card
                    #4b5550 muted 7.09 ground / 7.57 card / 6.85 sub
                    #0e6349 accent 6.64 ground / 7.09 card
                    #12764f second gradient stop 5.16 on ground
             dark   #e9efea ink 16.45 / 15.14; #9aa8a1 muted 7.76 /
                    7.14 / 6.50 sub; #5cdcaa accent 11.22 / 10.32
             plate  #ffffff 10.21 on #124a35, #b9d9cb 6.73 (the plate
                    is mode-invariant: one painted card, no .dark rule)
             band   #7ce6bd 12.45 and #9aa8a1 7.60 on #0d1310
           NEVER text-gray-500 on these grounds - use .es-seat-muted.
           ============================================================== */

        /* --- Ground and ink ----------------------------------------- */
        .es-seat-page { background-color: #f2f6f3; color: #0f1512; }
        .dark .es-seat-page { background-color: #0b100d; color: #e9efea; }
        .es-seat-ink { color: #0f1512; }
        .dark .es-seat-ink { color: #e9efea; }
        .es-seat-muted { color: #4b5550; }
        .dark .es-seat-muted { color: #9aa8a1; }
        .es-seat-accent { color: #0e6349; }
        .dark .es-seat-accent { color: #5cdcaa; }
        /* Always-lit accent, for text that sits on the fixed dark band. */
        .es-seat-lit { color: #7ce6bd; }

        .es-seat-grad {
            background-image: linear-gradient(100deg, #0e6349, #12764f);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dark .es-seat-grad,
        .es-seat-band .es-seat-grad {
            background-image: linear-gradient(100deg, #7ce6bd, #5cdcaa);
        }

        .es-seat-num {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, 'Liberation Mono', monospace;
            font-variant-numeric: tabular-nums;
        }
        .es-seat-tag {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #0e6349;
        }
        .dark .es-seat-tag { color: #5cdcaa; }
        .es-seat-band .es-seat-tag { color: #7ce6bd; }
        .es-seat-micro { font-size: 0.7rem; line-height: 1.5; }

        /* --- Surfaces ----------------------------------------------- */
        .es-seat-card {
            background-color: #fbfdfb;
            border: 1px solid rgba(15, 21, 18, 0.12);
            border-radius: 0.75rem;
        }
        .dark .es-seat-card {
            background-color: #141a16;
            border-color: rgba(233, 239, 234, 0.13);
        }
        .es-seat-sub { background-color: #eef2ef; border-radius: 0.5rem; }
        .dark .es-seat-sub { background-color: #1b231e; }
        .es-seat-hover { transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        /* Section separator and in-card footer rule. Written here rather
           than as arbitrary Tailwind border colours, which are not in the
           built bundle and would paint nothing. */
        .es-seat-hr { border-top: 1px solid rgba(15, 21, 18, 0.1); }
        .dark .es-seat-hr { border-top-color: rgba(233, 239, 234, 0.1); }
        .es-seat-foot { border-top: 1px solid rgba(15, 21, 18, 0.1); }
        .dark .es-seat-foot { border-top-color: rgba(233, 239, 234, 0.12); }
        /* Dot-nav tooltip: its own surface, for the same reason. */
        .es-seat-tip {
            border-radius: 999px;
            border: 1px solid rgba(15, 21, 18, 0.14);
            background-color: #fbfdfb;
            color: #0f1512;
        }
        .dark .es-seat-tip {
            border-color: rgba(233, 239, 234, 0.14);
            background-color: #141a16;
            color: #e9efea;
        }
        .es-seat-hover:hover {
            border-color: rgba(14, 99, 73, 0.45);
            box-shadow: 0 10px 28px -18px rgba(15, 21, 18, 0.5);
        }
        .dark .es-seat-hover:hover {
            border-color: rgba(92, 220, 170, 0.4);
            box-shadow: 0 10px 28px -18px rgba(0, 0, 0, 0.85);
        }

        /* --- The section mark: a small stamped plate with a rule under
               it. Deliberately not the corner-crop other pages use. --- */
        .es-seat-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2.6rem;
            padding: 0.28rem 0.5rem 0.34rem;
            border-radius: 0.3rem;
            border: 1px solid rgba(15, 21, 18, 0.18);
            border-bottom: 3px solid #0e6349;
            background-color: rgba(15, 21, 18, 0.03);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            color: #0f1512;
        }
        .dark .es-seat-mark {
            border-color: rgba(233, 239, 234, 0.2);
            border-bottom-color: #5cdcaa;
            background-color: rgba(233, 239, 234, 0.045);
            color: #e9efea;
        }
        .es-seat-band .es-seat-mark {
            border-color: rgba(233, 239, 234, 0.2);
            border-bottom-color: #7ce6bd;
            background-color: rgba(233, 239, 234, 0.045);
            color: #ffffff;
        }

        /* --- The lane rule: an abstract centreline stroke, used under
               the hero and in the trust band. Drifts to the left, which
               reads as forward travel; killed by reduced motion. The
               finale closes on the ledger instead, not on this. ------ */
        .es-seat-lane {
            height: 3px;
            border-radius: 999px;
            background-color: transparent;
            background-image: repeating-linear-gradient(90deg,
                rgba(14, 99, 73, 0.55) 0 26px, rgba(14, 99, 73, 0) 26px 46px);
            background-size: 46px 3px;
            animation: es-seat-drift 2.6s linear infinite;
        }
        .dark .es-seat-lane {
            background-image: repeating-linear-gradient(90deg,
                rgba(92, 220, 170, 0.5) 0 26px, rgba(92, 220, 170, 0) 26px 46px);
        }
        .es-seat-band .es-seat-lane {
            background-image: repeating-linear-gradient(90deg,
                rgba(124, 230, 189, 0.5) 0 26px, rgba(124, 230, 189, 0) 26px 46px);
        }
        @keyframes es-seat-drift {
            from { background-position: 0 0; }
            to { background-position: -46px 0; }
        }

        /* --- The spot ledger ----------------------------------------
               One cell per spot. Filled = a rider the driver approved,
               open = a spot still available. The cell count and the
               fill count are both read from one PHP array, so the
               drawing and the sentence beside it cannot disagree. --- */
        .es-seat-ledger { display: flex; flex-wrap: wrap; gap: 0.45rem; }
        .es-seat-cell {
            width: 1.7rem;
            height: 1.7rem;
            border-radius: 0.35rem;
            border: 1px solid transparent;
        }
        .es-seat-cell-taken { background-color: #0e6349; border-color: #0e6349; }
        .dark .es-seat-cell-taken { background-color: #5cdcaa; border-color: #5cdcaa; }
        .es-seat-cell-open {
            background-color: transparent;
            border-style: dashed;
            border-color: rgba(15, 21, 18, 0.32);
        }
        .dark .es-seat-cell-open { border-color: rgba(233, 239, 234, 0.34); }
        .es-seat-plate .es-seat-cell-taken { background-color: #ffffff; border-color: #ffffff; }
        .es-seat-plate .es-seat-cell-open { border-color: rgba(255, 255, 255, 0.45); }
        /* Same ledger on the fixed dark band (the finale). Pinned, because
           the .dark rules above would otherwise make it a mode diff. */
        .es-seat-band .es-seat-cell-taken { background-color: #7ce6bd; border-color: #7ce6bd; }
        .es-seat-band .es-seat-cell-open { border-color: rgba(255, 255, 255, 0.4); }

        .es-seat-figure {
            font-size: clamp(2.6rem, 8vw, 4rem);
            font-weight: 900;
            letter-spacing: -0.03em;
            line-height: 1;
            font-variant-numeric: tabular-nums;
        }
        .es-seat-of { font-size: 0.4em; font-weight: 700; letter-spacing: 0.04em; }

        /* --- The plate: the one ride offer the hero shows.
               A fixed physical object: the same painted green card in
               both colour modes, so it has NO .dark variant and nothing
               inside it may carry one. The ledger cells inside are
               pinned to white below for the same reason. ------------- */
        .es-seat-plate {
            background-color: #124a35;
            border-radius: 0.75rem;
            color: #ffffff;
        }
        .es-seat-plate-label { color: #b9d9cb; }
        .es-seat-rule { height: 1px; background-color: rgba(255, 255, 255, 0.22); }
        .es-seat-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.4);
            padding: 0.1rem 0.55rem;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        /* --- The duplex: hidden vs shown ---------------------------
               A redacted bar stands in for a value the viewer is not
               entitled to. Solid ground under the hatch so the label
               contrast is deterministic. --------------------------- */
        .es-seat-redact {
            border-radius: 0.35rem;
            background-color: #e7ebe8;
            background-image: repeating-linear-gradient(135deg,
                rgba(15, 21, 18, 0.1) 0 5px, rgba(15, 21, 18, 0) 5px 11px);
            color: #4b5550;
        }
        .dark .es-seat-redact {
            background-color: #1e2622;
            background-image: repeating-linear-gradient(135deg,
                rgba(233, 239, 234, 0.09) 0 5px, rgba(233, 239, 234, 0) 5px 11px);
            color: #9aa8a1;
        }
        .es-seat-shown {
            border-radius: 0.35rem;
            background-color: #e3f0e9;
            border: 1px solid rgba(14, 99, 73, 0.3);
            color: #0f1512;
        }
        .dark .es-seat-shown {
            background-color: #163025;
            border-color: rgba(92, 220, 170, 0.32);
            color: #e9efea;
        }

        /* --- The request ledger table ------------------------------- */
        /* The record scrolls inside its own box, so the page body never
           scrolls sideways on a phone. */
        .es-seat-scroll { overflow-x: auto; overflow-y: hidden; }
        .es-seat-table {
            width: 100%;
            min-width: 44rem;
            border-collapse: collapse;
            font-size: 0.875rem;
        }
        .es-seat-table th {
            text-align: start;
            padding: 0.7rem 1rem;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #4b5550;
            border-bottom: 1px solid rgba(15, 21, 18, 0.16);
            white-space: nowrap;
        }
        .dark .es-seat-table th { color: #9aa8a1; border-bottom-color: rgba(233, 239, 234, 0.18); }
        .es-seat-table td {
            padding: 0.85rem 1rem;
            vertical-align: top;
            border-top: 1px solid rgba(15, 21, 18, 0.09);
            color: #4b5550;
        }
        .dark .es-seat-table td { border-top-color: rgba(233, 239, 234, 0.09); color: #9aa8a1; }
        /* The state column is a row header, so it needs the body cell
           treatment rather than the column-header treatment. */
        .es-seat-table tbody th {
            padding: 0.85rem 1rem;
            vertical-align: top;
            border-top: 1px solid rgba(15, 21, 18, 0.09);
            border-bottom: 0;
            font-size: 0.875rem;
            letter-spacing: normal;
            text-transform: none;
        }
        .dark .es-seat-table tbody th { border-top-color: rgba(233, 239, 234, 0.09); }
        .es-seat-table tbody tr:nth-child(odd) td,
        .es-seat-table tbody tr:nth-child(odd) th { background-color: #eef2ef; }
        .dark .es-seat-table tbody tr:nth-child(odd) td,
        .dark .es-seat-table tbody tr:nth-child(odd) th { background-color: #1b231e; }
        .es-seat-table th:first-child, .es-seat-table td:first-child { padding-inline-start: 1.25rem; }
        .es-seat-table th:last-child, .es-seat-table td:last-child { padding-inline-end: 1.25rem; }

        .es-seat-st {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-weight: 700;
            font-size: 0.8rem;
            color: #0f1512;
            white-space: nowrap;
        }
        .dark .es-seat-st { color: #e9efea; }
        .es-seat-st::before {
            content: "";
            width: 0.55rem;
            height: 0.55rem;
            border-radius: 0.15rem;
            border: 1px solid rgba(15, 21, 18, 0.4);
            background-color: transparent;
        }
        .dark .es-seat-st::before { border-color: rgba(233, 239, 234, 0.4); }
        .es-seat-st-on::before { background-color: #0e6349; border-color: #0e6349; }
        .dark .es-seat-st-on::before { background-color: #5cdcaa; border-color: #5cdcaa; }

        /* --- Plan tiers only. Never reuse for a state badge. -------- */
        .es-seat-plan {
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
        .es-seat-plan-free { border-color: rgba(15, 21, 18, 0.24); color: #4b5550; }
        .dark .es-seat-plan-free { border-color: rgba(233, 239, 234, 0.28); color: #9aa8a1; }
        .es-seat-plan-pro {
            border-color: rgba(14, 99, 73, 0.5);
            background-color: rgba(14, 99, 73, 0.08);
            color: #0e6349;
        }
        .dark .es-seat-plan-pro {
            border-color: rgba(92, 220, 170, 0.42);
            background-color: rgba(92, 220, 170, 0.1);
            color: #5cdcaa;
        }

        /* --- Buttons ------------------------------------------------ */
        .es-seat-btn {
            background-color: #124a35;
            color: #ffffff;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .es-seat-btn:hover {
            background-color: #0e3b29;
            transform: translateY(-1px);
            box-shadow: 0 14px 28px -16px rgba(14, 99, 73, 0.9);
        }
        .es-seat-ghost {
            border: 1px solid rgba(15, 21, 18, 0.22);
            color: #0f1512;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }
        .es-seat-ghost:hover { border-color: rgba(14, 99, 73, 0.5); background-color: rgba(14, 99, 73, 0.06); }
        .dark .es-seat-ghost { border-color: rgba(233, 239, 234, 0.24); color: #e9efea; }
        .dark .es-seat-ghost:hover { border-color: rgba(92, 220, 170, 0.45); background-color: rgba(92, 220, 170, 0.08); }

        /* --- The fixed dark band -----------------------------------
               Same object in both colour modes, so it has no .dark
               variant and nothing inside it may carry one. The
               background-color resolves under the gradient so text over
               it is always scoreable. ------------------------------- */
        .es-seat-band {
            background-color: #0d1310;
            background-image:
                radial-gradient(ellipse 75% 55% at 50% 0%, rgba(14, 99, 73, 0.4), rgba(14, 99, 73, 0) 70%),
                linear-gradient(180deg, #141b17, #0d1310);
        }
        .es-seat-band .es-seat-muted { color: #9aa8a1; }
        /* Shared classes that flip themselves in dark mode and are
           invisible to a grep of this file. */
        .es-seat-band .grid-overlay {
            background-image:
                linear-gradient(rgba(233, 239, 234, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(233, 239, 234, 0.05) 1px, transparent 1px);
        }
        .es-seat-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-seat-band .es-claim:focus-within {
            border-color: rgba(124, 230, 189, 0.75);
            box-shadow: 0 0 0 4px rgba(124, 230, 189, 0.22);
        }

        /* Shared dot nav is hard-coded brand blue. */
        .es-dot:hover .es-dot-pip { background-color: rgba(14, 99, 73, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(92, 220, 170, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #0e6349; }
        .dark .es-dot.is-active .es-dot-pip { background: #5cdcaa; }

        /* Focus rings. No border-radius here: an outline already
           follows the element's own radius. */
        #es-seat-page a:focus-visible,
        #es-seat-page summary:focus-visible,
        #es-seat-page button:focus-visible,
        #es-seat-page input:focus-visible {
            outline: 2px solid #0e6349;
            outline-offset: 2px;
        }
        .dark #es-seat-page a:focus-visible,
        .dark #es-seat-page summary:focus-visible,
        .dark #es-seat-page button:focus-visible,
        .dark #es-seat-page input:focus-visible {
            outline-color: #5cdcaa;
        }
        .es-seat-band a:focus-visible,
        .es-seat-band input:focus-visible {
            outline-color: #7ce6bd !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-seat-lane { animation: none !important; }
            .es-seat-btn:hover { transform: none; }
        }
    </style>

    @php
        // One ride offer. Every figure on this page comes from here, and the
        // ledger cells are generated from the same two numbers the sentences
        // print, so the drawing and the prose cannot drift apart.
        $ride = [
            'driver'  => 'Dana K.',
            'rating'  => '4.9',
            'reviews' => 12,
            'city'    => 'Northside',
            'dir'     => 'Round trip',
            'departs' => '18:30',
            'meet'    => 'Station car park, west entrance',
            'spots'   => 4,
            // One rider approved, so three seats are still empty: the same
            // number the h1 says out loud. Change one and change the other.
            'taken'   => 1,
            'note'    => 'Small car, one bag each please.',
        ];
        $open = $ride['spots'] - $ride['taken'];

        // The three states one offer passes through, same four spots each time.
        $ledgerStates = [
            ['Just posted', 4, 0, 'Anyone signed in can ask for a spot.'],
            ['Two approved', 4, 2, 'Two spots left, and the count came down on its own.'],
            ['Full', 4, 4, 'The request button is gone. Nobody can ask.'],
        ];

        // The offer form, field by field, from CarpoolOfferRequest.
        $fields = [
            ['City', 'Where the driver is setting off from. Free text, and once a list runs past five offers riders get a box to filter on it.', 'Required'],
            ['Direction', 'To the event, from the event, or a round trip.', 'Required'],
            ['Spots', 'How many riders fit. Any whole number from 1 to 10.', 'Required'],
            ['Departure time', 'A clock time for the pick-up.', 'Optional'],
            ['Meeting point', 'A kerb, a car park, a station entrance.', 'Optional'],
            ['Note', 'Anything else worth saying, up to 500 characters.', 'Optional'],
        ];

        // CarpoolRequest.status, and the CarpoolNotification type each one fires.
        $lifecycle = [
            ['Pending', false, 'A rider asks for a spot and can add a message.', 'No', 'The driver'],
            ['Approved', true, 'The driver says yes.', 'Yes', 'The rider, with the driver\'s contact details'],
            ['Declined', false, 'The driver says no. The rider can ask again while spots remain.', 'No', 'The rider'],
            ['Cancelled', false, 'The rider withdraws, or the driver pulls the whole offer.', 'No', 'The driver, if the rider had been approved. Every rider, if the offer went.'],
        ];

        $faqs = [
            [
                'q' => 'How does carpool matching work?',
                'a' => 'A driver posts a ride offer on the event: the city they are setting off from, the direction (to the event, from the event, or a round trip), how many spots they have from 1 to 10, and optionally a departure time, a meeting point and a note. Other attendees see the list, filter it by direction (and by city once there are more than five offers), and ask for a spot with an optional message. The driver approves or declines each request, and an approved spot comes off the count straight away. There is no route matching, no map and no fare splitting: the offer is the whole record.',
            ],
            [
                'q' => 'Who can offer or request rides?',
                'a' => 'Anyone with an Event Schedule account, once they are signed in. The carpool list is not public: a visitor who is not signed in is asked to log in first. Before their first offer or request each person accepts a one-time carpool notice confirming they are 18 or older and that Event Schedule is not responsible for what happens on the ride.',
            ],
            [
                'q' => 'When does anyone see my email address or phone number?',
                'a' => 'Only once a driver has approved a rider. Until then an offer shows the driver\'s name, star rating, city, direction, departure time, meeting point and note, and nothing else. On approval the rider sees the driver\'s email address, and their phone number if they have added one to their profile, both on the carpool page and in the approval email. The driver sees the same details for riders they have approved. A rider who is declined, or who withdraws, never sees them. No email address is ever shown on the public event page, and follower and ticket-buyer email addresses are not part of carpool at all.',
            ],
            [
                'q' => 'What does carpool matching cost and where is it switched on?',
                'a' => 'It is on the Pro plan. There is one switch, on the schedule rather than on each event: Engagement, then Carpool, then Carpool matching. Turn it on and a Carpool link appears on the event pages for that schedule, with a count beside it once there are rides on offer.',
            ],
            [
                'q' => 'Does it work on a weekly or repeating event?',
                'a' => 'Yes, and each date keeps its own rides. An offer on a repeating event is tied to one occurrence, so a full car on the 8th has nothing to do with the 15th. The Carpool link on the event page points at the date being viewed.',
            ],
            [
                'q' => 'How does carpool handle safety?',
                'a' => 'Four ways. Everyone accepts the carpool notice once before taking part. Once the event has finished, a driver and the riders they approved can rate each other from 1 to 5 stars with an optional comment, and the average shows next to that person\'s name on later rides. Those same people, and only those people, can report each other with a reason. And the schedule owner sees every active offer and every report on the event\'s Engagement tab, and can remove an offer, which cancels its requests and emails the riders. Nothing is emailed to the owner, so the tab is where to look.',
            ],
        ];

        $dotSections = [
            ['top', 'Four seats'],
            ['offer', 'The offer'],
            ['spots', 'The spots'],
            ['yes', 'Yes'],
            ['ledger', 'The ledger'],
            ['trust', 'Trust'],
            ['switch', 'The switch'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];
    @endphp

    <div id="es-seat-page" class="es-seat-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: one offer, four spots                               -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden pb-16 pt-28">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(14, 99, 73, 0.24), rgba(14, 99, 73, 0) 62%); opacity: 0.5;"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 72% 64%, rgba(92, 220, 170, 0.16), rgba(92, 220, 170, 0) 62%); opacity: 0.45;"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:72px_72px] [mask-image:radial-gradient(ellipse_72%_62%_at_50%_38%,black_22%,transparent_74%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                <div>
                    <p class="es-seat-tag es-fade-up es-d-1 mb-5">Carpool matching</p>

                    <h1 class="es-balance mb-7 text-[2.6rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">Somebody is driving</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">with <span class="es-seat-grad">three seats empty</span>.</span></span>
                    </h1>

                    <p class="es-seat-muted es-fade-up es-d-2 mb-9 max-w-xl text-lg sm:text-xl">
                        The hardest part of a small show is not the ticket, it is the forty minutes of
                        road between somebody and your door. The capacity to fix that is already parked
                        outside. Carpool matching puts it on the event page.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ app_url('/sign_up') }}" class="es-seat-btn inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            Get started free
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                        <a href="{{ route('marketing.docs.creating_schedules') }}#engagement-carpool" class="es-seat-ghost inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            Read the Carpool guide
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    </div>

                    <div class="es-seat-lane es-fade-up es-d-4 mt-10 max-w-sm" aria-hidden="true"></div>
                </div>

                <!-- One ride offer as an attendee sees it. The ledger below the
                     figure is generated from the same two numbers. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-seat-plate p-7 sm:p-9">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="es-seat-plate-label es-seat-micro font-bold uppercase tracking-[0.2em]">Ride offer</p>
                                <p class="mt-1.5 text-lg font-bold text-white">{{ $ride['driver'] }}</p>
                                <p class="es-seat-plate-label es-seat-num text-sm">{{ $ride['rating'] }} stars &middot; {{ $ride['reviews'] }} ratings</p>
                            </div>
                            <span class="es-seat-chip">{{ $ride['dir'] }}</span>
                        </div>

                        <p class="es-seat-figure mt-7 text-white">
                            {{ $open }}<span class="es-seat-of es-seat-plate-label"> of {{ $ride['spots'] }}</span>
                        </p>
                        <p class="mt-2 text-sm text-white/80">spots open &middot; {{ $ride['taken'] }} {{ $ride['taken'] === 1 ? 'rider' : 'riders' }} approved</p>

                        <div class="es-seat-ledger mt-4" aria-hidden="true">
                            @for ($i = 0; $i < $ride['spots']; $i++)
                                <span class="es-seat-cell {{ $i < $ride['taken'] ? 'es-seat-cell-taken' : 'es-seat-cell-open' }}"></span>
                            @endfor
                        </div>

                        <div class="es-seat-rule my-6" aria-hidden="true"></div>

                        <dl class="space-y-1.5 text-sm">
                            <div class="flex justify-between gap-4">
                                <dt class="es-seat-plate-label">Setting off from</dt>
                                <dd class="text-white">{{ $ride['city'] }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="es-seat-plate-label">Departs</dt>
                                <dd class="es-seat-num text-white">{{ $ride['departs'] }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="es-seat-plate-label">Meets at</dt>
                                <dd class="text-end text-white">{{ $ride['meet'] }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="es-seat-plate-label">Contact</dt>
                                <dd class="text-end text-white">after the driver says yes</dd>
                            </div>
                        </dl>

                        <p class="es-seat-plate-label mt-6 text-sm italic">&ldquo;{{ $ride['note'] }}&rdquo;</p>
                    </div>

                    <p class="es-seat-muted mt-5 text-xs">
                        This is the whole record. No route matching, no map, no money changing hands.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The offer (01)                                            -->
    <!-- ============================================================ -->
    <section id="offer" class="es-seat-hr scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-start gap-14 lg:grid-cols-2 lg:gap-16">
                <div>
                    <div class="es-seat-mark mb-6" data-reveal aria-hidden="true"><span>01</span></div>
                    <p class="es-seat-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The offer</p>
                    <h2 class="es-balance es-seat-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Six fields, and <span class="es-seat-grad">three of them optional</span>.
                    </h2>
                    <p class="es-seat-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        A driver is standing in a kitchen with a phone. Anything you ask them for beyond
                        a town and a number of seats is a chance to give up halfway. So the form is a
                        city, a direction and a count, and the rest can be left blank.
                    </p>

                    <ul class="space-y-4" data-reveal-group="90">
                        @foreach ([
                            ['To, from, or both', 'A lift home after the last band is a different offer from a lift there, and someone doing both is one offer covering the pair. Riders filter the list by direction.'],
                            ['One live offer per direction', 'You cannot post the same run twice by accident. A second offer that overlaps a direction you already cover is refused.'],
                            ['It stops being offerable in time', 'A to-event ride cannot be posted or requested once the event has started, and a from-event ride cannot once it has ended.'],
                        ] as [$t, $d])
                            <li class="flex items-start gap-3" data-reveal>
                                <svg aria-hidden="true" class="es-seat-accent mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span><span class="es-seat-ink font-semibold">{{ $t }}</span> <span class="es-seat-muted">- {{ $d }}</span></span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                    <div class="es-tilt-inner es-seat-card overflow-hidden p-6 sm:p-7">
                        <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                            <h3 class="es-seat-ink text-lg font-bold">Offer a ride</h3>
                            <span class="es-seat-muted es-seat-num text-xs">6 fields</span>
                        </div>

                        <div class="space-y-2.5">
                            @foreach ($fields as [$fName, $fWhat, $fReq])
                                <div class="es-seat-sub p-3.5">
                                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                                        <p class="es-seat-ink text-sm font-semibold">{{ $fName }}</p>
                                        <p class="es-seat-muted es-seat-num es-seat-micro uppercase tracking-[0.12em]">{{ $fReq }}</p>
                                    </div>
                                    <p class="es-seat-muted mt-0.5 text-sm">{{ $fWhat }}</p>
                                </div>
                            @endforeach
                        </div>

                        <p class="es-seat-muted mt-5 es-seat-foot pt-4 text-xs">
                            On a repeating event the offer is tied to the occurrence being viewed, so it
                            never leaks into next week.
                        </p>

                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The spots (02)                                            -->
    <!-- ============================================================ -->
    <section id="spots" class="es-seat-hr scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-seat-mark mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                <p class="es-seat-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The spots</p>
                <h2 class="es-balance es-seat-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Four seats means <span class="es-seat-grad">four people</span>.
                </h2>
                <p class="es-seat-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    The count is the only number in the feature. Every approval takes one off it, and
                    when it reaches zero the ride stops accepting anyone. Nobody picks a seat, they
                    just get one.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-3" data-reveal-group="100">
                @foreach ($ledgerStates as [$sName, $sTotal, $sTaken, $sLine])
                    <div class="es-seat-card es-seat-hover p-6" data-reveal>
                        <div class="mb-4 flex items-baseline justify-between gap-3">
                            <h3 class="es-seat-ink text-lg font-bold">{{ $sName }}</h3>
                            <span class="es-seat-accent es-seat-num text-sm font-bold">{{ $sTotal - $sTaken }}/{{ $sTotal }}</span>
                        </div>
                        <div class="es-seat-ledger mb-4" aria-hidden="true">
                            @for ($i = 0; $i < $sTotal; $i++)
                                <span class="es-seat-cell {{ $i < $sTaken ? 'es-seat-cell-taken' : 'es-seat-cell-open' }}"></span>
                            @endfor
                        </div>
                        <p class="es-seat-muted text-sm">{{ $sLine }}</p>
                        <p class="es-seat-muted es-seat-micro mt-3">{{ $sTotal - $sTaken }} of {{ $sTotal }} spots open</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 grid gap-4 md:grid-cols-3" data-reveal-group="100">
                @foreach ([
                    ['1 to 10, and editable later', 'A driver can raise or lower the count any time up until the event ends. It will not go below the number of riders already approved, so an approval cannot be undone by editing a number.'],
                    ['Two people cannot take one spot', 'Approving is settled inside a single locked transaction, so the last spot goes to exactly one person even if two requests land together.'],
                    ['Counted per date', 'On a weekly night, a full car on the 8th says nothing about the 15th. Each occurrence has its own offers and its own counts.'],
                ] as [$t, $d])
                    <div class="es-seat-card es-seat-hover p-6" data-reveal>
                        <h3 class="es-seat-ink mb-2 text-lg font-bold">{{ $t }}</h3>
                        <p class="es-seat-muted text-sm">{{ $d }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Yes (03) - the privacy duplex                             -->
    <!-- ============================================================ -->
    <section id="yes" class="es-seat-hr scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-seat-mark mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                <p class="es-seat-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Yes</p>
                <h2 class="es-balance es-seat-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    An email address is <span class="es-seat-grad">a thing you are given</span>.
                </h2>
                <p class="es-seat-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Getting into a stranger's car is a decision, and so is telling a stranger how to
                    reach you. Both belong to the driver, in that order. Here is the same offer twice.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2" data-reveal-group="110">
                <!-- Before yes -->
                <div class="es-seat-card p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-seat-ink text-lg font-bold">Before yes</h3>
                        <span class="es-seat-muted es-seat-num es-seat-micro uppercase tracking-[0.12em]">Any signed-in attendee</span>
                    </div>
                    <dl class="space-y-2.5">
                        @foreach ([
                            ['Name', $ride['driver']],
                            ['Rating', $ride['rating'] . ' stars from ' . $ride['reviews'] . ' ratings'],
                            ['City', $ride['city']],
                            ['Direction', $ride['dir']],
                            ['Departs', $ride['departs']],
                            ['Meets at', $ride['meet']],
                            ['Spots', $open . ' of ' . $ride['spots'] . ' open'],
                        ] as [$vLabel, $vValue])
                            <div class="es-seat-sub flex flex-wrap items-baseline justify-between gap-2 p-3">
                                <dt class="es-seat-muted es-seat-micro uppercase tracking-[0.12em]">{{ $vLabel }}</dt>
                                <dd class="es-seat-ink text-sm font-semibold">{{ $vValue }}</dd>
                            </div>
                        @endforeach
                        <div class="es-seat-redact flex flex-wrap items-baseline justify-between gap-2 p-3">
                            <dt class="es-seat-micro uppercase tracking-[0.12em]">Email</dt>
                            <dd class="text-sm font-semibold">not shown</dd>
                        </div>
                        <div class="es-seat-redact flex flex-wrap items-baseline justify-between gap-2 p-3">
                            <dt class="es-seat-micro uppercase tracking-[0.12em]">Phone</dt>
                            <dd class="text-sm font-semibold">not shown</dd>
                        </div>
                    </dl>
                    <p class="es-seat-muted mt-5 es-seat-foot pt-4 text-xs">
                        A rider who is declined, or who withdraws, stays on this side of the line.
                    </p>
                </div>

                <!-- After yes -->
                <div class="es-seat-card p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-seat-ink text-lg font-bold">After yes</h3>
                        <span class="es-seat-accent es-seat-num es-seat-micro uppercase tracking-[0.12em]">Approved rider only</span>
                    </div>
                    <dl class="space-y-2.5">
                        @foreach ([
                            ['Name', $ride['driver']],
                            ['Rating', $ride['rating'] . ' stars from ' . $ride['reviews'] . ' ratings'],
                            ['City', $ride['city']],
                            ['Direction', $ride['dir']],
                            ['Departs', $ride['departs']],
                            ['Meets at', $ride['meet']],
                            ['Spots', $open . ' of ' . $ride['spots'] . ' open'],
                        ] as [$vLabel, $vValue])
                            <div class="es-seat-sub flex flex-wrap items-baseline justify-between gap-2 p-3">
                                <dt class="es-seat-muted es-seat-micro uppercase tracking-[0.12em]">{{ $vLabel }}</dt>
                                <dd class="es-seat-ink text-sm font-semibold">{{ $vValue }}</dd>
                            </div>
                        @endforeach
                        <div class="es-seat-shown flex flex-wrap items-baseline justify-between gap-2 p-3">
                            <dt class="es-seat-micro uppercase tracking-[0.12em]">Email</dt>
                            <dd class="es-seat-num text-sm font-semibold">dana@example.com</dd>
                        </div>
                        <div class="es-seat-shown flex flex-wrap items-baseline justify-between gap-2 p-3">
                            <dt class="es-seat-micro uppercase tracking-[0.12em]">Phone</dt>
                            <dd class="es-seat-num text-sm font-semibold">if the driver added one</dd>
                        </div>
                    </dl>
                    <p class="es-seat-muted mt-5 es-seat-foot pt-4 text-xs">
                        The same two rows appear in the approval email, so the rider has them without
                        going back to the page.
                    </p>
                </div>
            </div>

            <div class="mt-8 grid gap-4 md:grid-cols-3" data-reveal-group="100">
                @foreach ([
                    ['It runs both ways', 'While a driver is deciding they see the requester\'s name, their rating and their message. The requester\'s email and phone arrive only once the driver has approved them.'],
                    ['A phone number is opt-in', 'Nobody has to give one. Carpool suggests adding one to your profile because it is easier for a pick-up, and it is shown only where the email address already is.'],
                    ['Nothing here is public', 'The carpool list needs a sign-in. On a draft or internal event it needs to be your schedule, and on an unlisted one it needs that or the event password. Follower and ticket-buyer addresses are not part of carpool.'],
                ] as [$t, $d])
                    <div class="es-seat-card es-seat-hover p-6" data-reveal>
                        <h3 class="es-seat-ink mb-2 text-lg font-bold">{{ $t }}</h3>
                        <p class="es-seat-muted text-sm">{{ $d }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. The ledger (04) - the request lifecycle as a record       -->
    <!-- ============================================================ -->
    <section id="ledger" class="es-seat-hr scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-seat-mark mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                <p class="es-seat-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The ledger</p>
                <h2 class="es-balance es-seat-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Every request is in <span class="es-seat-grad">one of four states</span>.
                </h2>
                <p class="es-seat-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Nobody has to guess where they stand, and nobody has to sit on the page refreshing
                    it. Every move writes a state, and the last column says who hears about it.
                </p>
            </div>

            <div class="es-seat-card es-seat-scroll" data-reveal="panel">
                <table class="es-seat-table">
                    <caption class="sr-only">The four states a carpool ride request can be in, how it gets there, whether it holds a spot, and who is emailed.</caption>
                    <thead>
                        <tr>
                            <th scope="col">State</th>
                            <th scope="col">How it gets there</th>
                            <th scope="col">Holds a spot</th>
                            <th scope="col">Email goes to</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lifecycle as [$lName, $lOn, $lHow, $lHolds, $lWho])
                            <tr>
                                <th scope="row" class="whitespace-nowrap">
                                    <span class="es-seat-st {{ $lOn ? 'es-seat-st-on' : '' }}">{{ $lName }}</span>
                                </th>
                                <td>{{ $lHow }}</td>
                                <td class="es-seat-num">{{ $lHolds }}</td>
                                <td>{{ $lWho }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="es-seat-muted es-seat-micro mt-3 text-center" data-reveal>The record scrolls sideways on a narrow screen.</p>

            <div class="mt-8 grid gap-4 md:grid-cols-3" data-reveal-group="100">
                @foreach ([
                    ['A reminder the day before', 'An hourly job looks for approved rides whose event starts within the next 24 hours, or ends within them for a lift home, and emails each rider once. The driver gets one too. If browser notifications are switched on, a push goes with it.'],
                    ['One ask per ride', 'A rider cannot stack requests on the same offer, and cannot ask for a spot on their own. Posting offers, asking for spots, reviewing and reporting are all rate limited.'],
                    ['Both sides get told', 'Pulling an offer cancels every request on it and emails everyone who was on it, pending or approved. Withdrawing after approval tells the driver, so the seat is not held for a ghost.'],
                ] as [$t, $d])
                    <div class="es-seat-card es-seat-hover p-6" data-reveal>
                        <h3 class="es-seat-ink mb-2 text-lg font-bold">{{ $t }}</h3>
                        <p class="es-seat-muted text-sm">{{ $d }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 text-center" data-reveal>
                <span class="es-seat-plan es-seat-plan-pro">Pro</span>
                <span class="es-seat-muted ml-2 text-sm">
                    On eventschedule.com these emails go out through your schedule's own email settings,
                    so set those up before you switch carpool on. A selfhosted install uses whatever
                    mailer it is already configured with.
                </span>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Trust (05, fixed dark band)                               -->
    <!-- ============================================================ -->
    <section id="trust" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-seat-band noise relative overflow-hidden rounded-[2rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="es-seat-mark mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                    <p class="es-seat-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Trust</p>
                    <h2 class="es-balance mb-5 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        You are not <span class="es-seat-grad">vouching for anyone</span>.
                    </h2>
                    <p class="es-seat-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Carpool is between attendees, and the product says so in writing before anyone
                        takes part. What you get is a record of who behaved, and a way to act when
                        somebody did not.
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-2" data-reveal-group="110">
                    @foreach ([
                        ['01', 'A notice, once', 'Before a first offer or a first request, each person ticks a box confirming they are 18 or older and that Event Schedule is not responsible for what happens on a ride. It is accepted once per account, not per event.'],
                        ['02', 'Ratings, after the fact', 'When the event has finished, a driver and the riders they approved can rate each other from 1 to 5 stars with an optional comment. One rating per pair per ride, and the average sits next to that person\'s name on every later ride.'],
                        ['03', 'Reports, from inside the ride', 'A driver can report a rider they approved, and an approved rider can report the driver, with a reason. Nobody else on the page can file one, so a report always comes from inside the car.'],
                        ['04', 'Removal, by you', 'The event\'s Engagement tab lists every active offer with its driver, city, direction and how many spots are taken, and every report filed against it. Remove an offer and its requests are cancelled and its riders emailed. Dismiss a report and it is gone.'],
                    ] as [$n, $t, $d])
                        <div class="rounded-lg border border-white/10 bg-white/[0.05] p-7 backdrop-blur-sm" data-reveal="panel">
                            <p class="es-seat-lit es-seat-num mb-3 text-sm font-bold">{{ $n }}</p>
                            <h3 class="mb-2 text-lg font-bold text-white">{{ $t }}</h3>
                            <p class="es-seat-muted text-sm">{{ $d }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="es-seat-lane mx-auto mt-12 max-w-md" aria-hidden="true"></div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. The switch (06)                                           -->
    <!-- ============================================================ -->
    <section id="switch" class="es-seat-hr scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-16">
                <div class="order-2 lg:order-1">
                    <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                        <div class="es-tilt-inner es-seat-card overflow-hidden p-6 sm:p-7">
                            <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                                <h3 class="es-seat-ink text-lg font-bold">Engagement &rsaquo; Carpool</h3>
                                <span class="es-seat-plan es-seat-plan-pro">Pro</span>
                            </div>
                            <p class="es-seat-muted mb-5 text-sm">One switch on the schedule, not one per event.</p>

                            <div class="es-seat-sub p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="es-seat-ink text-sm font-semibold">Carpool matching</p>
                                        <p class="es-seat-muted mt-0.5 text-sm">Allow attendees to offer and request rides to your events.</p>
                                    </div>
                                    <span class="es-seat-accent es-seat-num es-seat-micro shrink-0 font-bold uppercase tracking-[0.12em]">On</span>
                                </div>
                            </div>

                            <p class="es-seat-muted mb-2 mt-6 es-seat-micro font-bold uppercase tracking-[0.12em]">Then, on the event page</p>
                            <div class="es-seat-sub flex items-center justify-between gap-3 p-3.5">
                                <span class="es-seat-ink text-sm font-semibold">Carpool</span>
                                <span class="es-seat-accent es-seat-num text-sm font-bold">3</span>
                            </div>
                            <p class="es-seat-muted mt-2 text-xs">The number beside it is how many rides are currently on offer.</p>

                            <div class="es-glare" aria-hidden="true"></div>
                            <div class="es-ring-glow" aria-hidden="true"></div>
                        </div>
                    </div>
                </div>

                <div class="order-1 lg:order-2">
                    <div class="es-seat-mark mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                    <p class="es-seat-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The switch</p>
                    <h2 class="es-balance es-seat-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        You turn it on <span class="es-seat-grad">once</span>.
                    </h2>
                    <p class="es-seat-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        Carpool matching lives on the schedule, on the Pro plan. Switch it on and the
                        event pages on that schedule carry a Carpool link. There is nothing to remember
                        when you add the next show, and nothing for you to run afterwards.
                    </p>

                    <div class="space-y-3" data-reveal-group="90">
                        @foreach ([
                            ['The link finds the right date', 'On a repeating event the Carpool link carries the occurrence being viewed, so a rider always lands on the rides for the night they are looking at.'],
                            ['Hidden events stay hidden', 'A draft or internal event\'s carpool page is members-only, and an unlisted one needs membership or the event password. The event name and its riders do not leak out with a guessed link.'],
                            ['My Carpools, for the attendee', 'Once someone has offered or asked for a ride, a My Carpools page in their sidebar collects every one of them across every schedule, with how many requests are waiting.'],
                            ['Nothing to administer', 'Riders and drivers sort themselves out. Nothing is queued for you and no report emails you: if you want to check, the offers and any reports are sitting on that event\'s Engagement tab.'],
                        ] as [$t, $d])
                            <div class="es-seat-card es-seat-hover p-4" data-reveal>
                                <p class="es-seat-ink text-sm font-bold">{{ $t }}</p>
                                <p class="es-seat-muted mt-1 text-sm">{{ $d }}</p>
                            </div>
                        @endforeach
                    </div>

                    <p class="mt-7" data-reveal>
                        <span class="es-seat-plan es-seat-plan-free">Free</span>
                        <span class="es-seat-muted ml-2 text-sm">
                            The schedule, the event pages and the calendar cost nothing. Carpool matching
                            is one of the things Pro adds.
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Key features                                              -->
    <!-- ============================================================ -->
    <section class="es-seat-hr scroll-mt-24 py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-seat-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Polls" description="Ask the room a question and show the answers back" :url="marketing_url('/features/polls')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6m6 6V5M3 19h18" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Email the people who follow the schedule, on every plan" :url="marketing_url('/features/newsletters')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="A weekly night as one event, with a date of its own for each occurrence" :url="marketing_url('/features/recurring-events')" icon-color="teal">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="Sell the seat in the room, with zero platform fees" :url="marketing_url('/features/ticketing')" icon-color="lime">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-lime-600 dark:text-lime-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-seat-accent inline-flex items-center font-medium hover:underline">
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
    <!-- 9. Related pages                                             -->
    <!-- ============================================================ -->
    <section class="es-seat-hr py-16">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-seat-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([
                    ['/features/polls', 'Polls', 'Also on the Engagement tab'],
                    ['/features/fan-videos', 'Fan Videos', 'Also on the Engagement tab'],
                    ['/features/recurring-events', 'Recurring Events', 'Why each date has its own rides'],
                    ['/pricing', 'Pricing', 'What Pro adds'],
                ] as [$relHref, $relName, $relKicker])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="es-seat-card es-seat-hover group flex items-center justify-between gap-4 p-5">
                        <div>
                            <div class="es-seat-muted text-sm">{{ $relKicker }}</div>
                            <div class="es-seat-ink text-lg font-semibold">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="es-seat-accent h-5 w-5 shrink-0 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ route('marketing.docs.creating_schedules') }}#engagement-carpool" class="es-seat-accent inline-flex items-center font-medium hover:underline">
                    Read the Carpool guide
                    <svg aria-hidden="true" class="ml-1 h-4 w-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <x-marketing.related-pages />

    <!-- ============================================================ -->
    <!-- 10. FAQ (07)                                                 -->
    <!-- ============================================================ -->
    <section id="faq" class="es-seat-hr scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-seat-mark mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                <p class="es-seat-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Questions</p>
                <h2 class="es-balance es-seat-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Asked <span class="es-seat-grad">from the kerb</span>.
                </h2>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ($faqs as $faq)
                    <details name="faq" data-reveal class="es-seat-card group/faq overflow-hidden">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-6">
                            <h3 class="es-seat-ink text-lg font-semibold">{{ $faq['q'] }}</h3>
                            <svg aria-hidden="true" class="es-seat-muted h-5 w-5 shrink-0 transition-transform duration-300 group-open/faq:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="es-seat-muted faq-answer px-6 pb-6">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <x-seo.faq-schema :items="$faqs" />

    <!-- ============================================================ -->
    <!-- 11. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-seat-band noise relative overflow-hidden rounded-[2rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-25"></div>
                </div>

                <div class="relative z-10">
                    <p class="es-seat-tag mb-6">Free to start</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                        Fill the seats that are <span class="es-seat-grad">already going</span>.
                    </h2>
                    <p class="es-seat-muted mx-auto mb-6 max-w-xl text-lg sm:text-xl">
                        Claim your schedule, put the shows on it, and let the people coming work out the
                        driving between themselves.
                    </p>

                    {{-- The page closes on the object it opened with: the same four spots,
                         the same one rider approved, the same three seats still empty. --}}
                    <div class="es-seat-ledger mb-3 justify-center" aria-hidden="true">
                        @for ($i = 0; $i < $ride['spots']; $i++)
                            <span class="es-seat-cell {{ $i < $ride['taken'] ? 'es-seat-cell-taken' : 'es-seat-cell-open' }}"></span>
                        @endfor
                    </div>
                    <p class="es-seat-muted es-seat-micro mb-10">{{ $open }} of {{ $ride['spots'] }} spots open</p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-lg border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-seat-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg px-8 py-4 text-lg font-semibold">
                            <span class="relative z-10 flex items-center gap-2">
                                Start for free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="es-seat-muted mt-6 text-sm">No credit card required</p>
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
                        <span class="es-seat-tip pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
