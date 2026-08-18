<x-marketing-layout>
    <x-slot name="title">Event Feedback & Reviews - Event Schedule</x-slot>
    <x-slot name="description">Post-event feedback for ticket holders: a day after the event ends, everyone who booked gets a card with a one-to-five rating and an optional comment. Yours alone by default, or published on the event page. A Pro feature.</x-slot>
    <x-slot name="breadcrumbTitle">Event Feedback</x-slot>

    <x-slot name="structuredData">
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule Post-Event Feedback",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Feedback Collection Software",
        "operatingSystem": "Web",
        "description": "After an event ends, everyone who held a booking for that date is emailed a private feedback link: a required rating from one to five and an optional comment of up to 2,000 characters. One card per booking. Read them on the Feedback tab, export to CSV, or publish them on the event page.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Free to sign up; post-event feedback is included on the Pro plan"
        },
        "featureList": [
            "A required rating from 1 to 5 and an optional comment up to 2,000 characters",
            "Requests emailed to everyone who held a booking, paid tickets and free RSVP registrations alike",
            "One card per booking, enforced in the database",
            "A delay of 1, 2, 6, 12, 24 or 48 hours after each occurrence ends",
            "No request goes out more than 30 days after the event ended",
            "A per-event override of the schedule-level setting",
            "A form branded with your schedule's logo, colour and font",
            "Feedback kept private by default, or published on the event page as attendee reviews",
            "Pending, sent, responded and response-rate counters on the Feedback tab",
            "Resend, send-now and cancel-all controls for pending requests",
            "CSV export, a feedback.submitted webhook, and a REST API endpoint"
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
           For-feedback "The Comment Card" styles. A comment card is the
           slip left on a table for anyone to fill in, and that is exactly
           why the box on the wall tells you nothing. This product's card
           is different in three ways that ARE the feature: it is addressed
           to the person whose name is on the booking, it is not printed
           until the event has finished, and each one can be returned once.

           THE SIGNATURE DEVICE IS A CARD, NOT A FIELD OF STARS. The
           first-wave page ran twinkling star glyphs across the hero and
           the finale, plus five-star rows in four bento cells. Two
           problems. (1) It taught an open review widget sitting on the
           event page, which does not exist: the form lives behind a
           per-booking secret link that only arrives by email
           (FeedbackController::show takes an event id AND a sale secret).
           (2) Stars are house furniture - the AP, the guest page and the
           docs all draw them, so they carry no page identity. The card
           does: printed stock, a boxed one-to-five scale, ruled lines,
           and a tear-off footer with the terms on it.

           COLOUR: the page keeps its existing amber/orange family, taken
           down to a single burnt printer's ink (#8f4a12 light, #f8b26a
           dark) on card stock. It is deliberately NOT the shared brand
           blue to sky to cyan chrome gradient, and not a three-stop
           gradient on headings either - a gradient's bright stop is the
           most common AA failure in this codebase. One flat ink, and the
           distinctiveness comes from the stock, the boxed scale and the
           typewriter labels.

           NEVER use text-gray-500 here. It measures 4.83 on pure white
           but only about 4.4 on this page's warm ground. Use
           .es-comment-muted (8.71 light, 7.51 dark).

           FIXED PHYSICAL OBJECT: .es-comment-stock is a printed card. It
           must render IDENTICALLY with .dark on and off, so nothing
           scoped under it carries a .dark rule - the stock's slots,
           lines, rules, rows, stamp and perforation are defined once and
           once only. Verified with --bands=.es-comment-stock (0 diffs).

           The card appears three times, and each one is a different face
           of the same object rather than a repeat: filled in and stamped
           RETURNED in the hero, turned over and stamped TERMS for the
           small-print section (which is why that copy says "printed on
           the back" - it literally is), and blank and stamped BLANK in
           the finale. If you replace one of them, keep the other two:
           a physical object that shows up once is just an illustration.
           ============================================================== */

        /* --- Ground and ink ------------------------------------------- */
        .es-comment-page { background-color: #faf7f0; color: #1a1613; }
        .dark .es-comment-page { background-color: #0f0d0b; color: #ece7de; }
        .es-comment-ink { color: #1a1613; }
        .dark .es-comment-ink { color: #ece7de; }
        .es-comment-muted { color: #4c463e; }
        .dark .es-comment-muted { color: #a8a096; }
        .es-comment-accent { color: #8f4a12; }
        .dark .es-comment-accent { color: #f8b26a; }
        /* Always-lit accent, for the bands that are dark in both modes. */
        .es-comment-lit { color: #f8b26a; }

        /* Hairline separators. Tailwind cannot generate an arbitrary rgba()
           border class that is not already in the built marketing CSS, so
           this is a real rule rather than a bracket utility. */
        .es-comment-rule { border-color: rgba(26, 22, 19, 0.1); }
        .dark .es-comment-rule { border-color: rgba(236, 231, 222, 0.1); }

        /* --- Panels --------------------------------------------------- */
        .es-comment-card {
            border: 1px solid rgba(26, 22, 19, 0.13);
            border-radius: 1rem;
            background: #ffffff;
        }
        .dark .es-comment-card {
            border-color: rgba(236, 231, 222, 0.12);
            background: rgba(236, 231, 222, 0.045);
        }

        /* --- Fixed-dark band ------------------------------------------ */
        .es-comment-band {
            background-color: #1a1510;
            background-image: radial-gradient(120% 100% at 50% 0%, #221b15 0%, #1a1510 55%, #0d0a08 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(236, 231, 222, 0.05);
        }
        .es-comment-band-ink { color: #ece7de; }
        .es-comment-band-muted { color: #a89e92; }
        .es-comment-band .es-comment-card {
            border-color: rgba(236, 231, 222, 0.14);
            background: rgba(236, 231, 222, 0.05);
        }
        /* Shared and page classes that would otherwise flip with the colour
           mode inside a band that is dark in BOTH modes. */
        .es-comment-band .grid-overlay {
            background-image:
                linear-gradient(rgba(236, 231, 222, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(236, 231, 222, 0.05) 1px, transparent 1px);
        }
        .es-comment-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-comment-band .es-claim:focus-within {
            border-color: rgba(248, 178, 106, 0.75);
            box-shadow: 0 0 0 4px rgba(248, 178, 106, 0.22);
        }

        /* --- The card: printed stock, identical in both colour modes --- */
        .es-comment-stock {
            position: relative;
            border-radius: 0.3rem;
            background-color: #efe6d4;
            background-image: linear-gradient(180deg, #f5eedf 0%, #e8ddc7 100%);
            border: 1px solid #cfc3a6;
            box-shadow: 0 24px 48px -24px rgba(26, 22, 19, 0.5);
            color: #1c1913;
        }
        .es-comment-stock-ink { color: #1c1913; }
        .es-comment-stock-muted { color: #5c5344; }
        .es-comment-stock-accent { color: #8f4a12; }
        .es-comment-stock-label {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.28em;
            text-transform: uppercase;
            color: #5c5344;
        }
        .es-comment-stock-rule { border-top: 1px solid rgba(28, 25, 19, 0.2); }
        /* The tear-off strip along the bottom, where the terms are printed. */
        .es-comment-perf { border-top: 1px dashed rgba(28, 25, 19, 0.38); }
        /* Ruled rows on the REVERSE of the card, where the small print is set
           two columns wide. Same hairline as the front, and like everything
           else scoped to the stock it has no .dark counterpart. */
        .es-comment-stock-row { border-bottom: 1px solid rgba(28, 25, 19, 0.16); }

        /* The one-to-five scale, printed as boxes rather than stars: the
           stored value is a whole number from 1 to 5, and boxes keep the
           card's material honest. The live form draws five stars. */
        .es-comment-scale { display: flex; gap: 0.35rem; }
        .es-comment-slot {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border: 1.5px solid rgba(28, 25, 19, 0.4);
            border-radius: 0.2rem;
            background: #fffdf6;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.8rem;
            font-weight: 800;
            color: #5c5344;
        }
        .es-comment-slot-marked {
            border-color: #8f4a12;
            background: #8f4a12;
            color: #ffffff;
        }

        /* The writing area: ruled hairlines, not a facsimile of handwriting. */
        .es-comment-write { display: flex; flex-direction: column; gap: 0.55rem; }
        .es-comment-line {
            height: 1px;
            border-radius: 1px;
            background: rgba(28, 25, 19, 0.24);
        }
        .es-comment-line-short { width: 62%; }
        /* What the attendee actually wrote, set apart from the printing. */
        .es-comment-hand {
            font-family: ui-serif, Georgia, 'Iowan Old Style', 'Times New Roman', serif;
            font-style: italic;
            color: #1c1913;
            line-height: 1.5;
        }
        .es-comment-stamp {
            display: inline-flex;
            align-items: center;
            padding: 0.24rem 0.6rem;
            border: 2px solid rgba(143, 74, 18, 0.6);
            border-radius: 0.18rem;
            color: #8f4a12;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.55rem;
            font-weight: 800;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            transform: rotate(-4deg);
        }

        /* --- The delay control: the six stops the dropdown offers ------ */
        .es-comment-stops { display: flex; gap: 0.3rem; }
        .es-comment-stop {
            flex: 1 1 0;
            min-width: 0;
            padding: 0.5rem 0.2rem;
            border: 1px solid rgba(26, 22, 19, 0.18);
            border-radius: 0.4rem;
            text-align: center;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.78rem;
            font-weight: 700;
            color: #4c463e;
        }
        .dark .es-comment-stop { border-color: rgba(236, 231, 222, 0.18); color: #a8a096; }
        .es-comment-stop-on {
            border-color: #8f4a12;
            background: #8f4a12;
            color: #ffffff;
        }
        .dark .es-comment-stop-on { border-color: #a85614; background: #a85614; color: #ffffff; }

        /* --- The thirty-day window: one cell per day ------------------- */
        /* 30 window days plus the 2 cut-off cells is 32, which divides evenly
           into 16 columns on a phone and sits on one row from sm up. */
        .es-comment-window {
            display: grid;
            grid-template-columns: repeat(16, minmax(0, 1fr));
            gap: 3px;
        }
        @media (min-width: 640px) {
            .es-comment-window { grid-template-columns: repeat(32, minmax(0, 1fr)); }
        }
        .es-comment-day {
            height: 1.6rem;
            border-radius: 0.15rem;
            background: rgba(143, 74, 18, 0.22);
        }
        .dark .es-comment-day { background: rgba(248, 178, 106, 0.22); }
        .es-comment-day-sent { background: #8f4a12; }
        .dark .es-comment-day-sent { background: #f8b26a; }
        .es-comment-day-off {
            background: transparent;
            border: 1px dashed rgba(26, 22, 19, 0.22);
        }
        .dark .es-comment-day-off { border-color: rgba(236, 231, 222, 0.22); }

        /* --- A rating inside a record row ----------------------------- */
        .es-comment-rate { display: inline-flex; gap: 2px; }
        .es-comment-pip {
            display: block;
            width: 0.55rem;
            height: 0.55rem;
            border-radius: 0.1rem;
            background: rgba(26, 22, 19, 0.16);
        }
        .dark .es-comment-pip { background: rgba(236, 231, 222, 0.16); }
        .es-comment-pip-on { background: #8f4a12; }
        .dark .es-comment-pip-on { background: #f8b26a; }

        /* --- The response-rate meter ---------------------------------- */
        .es-comment-meter {
            position: relative;
            height: 0.45rem;
            border-radius: 9999px;
            background: rgba(26, 22, 19, 0.1);
            overflow: hidden;
        }
        .dark .es-comment-meter { background: rgba(236, 231, 222, 0.12); }
        .es-comment-meter-fill {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            width: var(--w, 50%);
            border-radius: 9999px;
            background: #8f4a12;
            transform-origin: left center;
        }
        .dark .es-comment-meter-fill { background: #f8b26a; }
        html.es-anim .es-comment-meter-fill {
            animation: es-comment-grow 0.9s cubic-bezier(0.22, 1, 0.36, 1) both;
        }
        @keyframes es-comment-grow {
            from { transform: scaleX(0); }
            to { transform: scaleX(1); }
        }

        /* --- Eyebrow, numeral, plan tag, chip ------------------------- */
        .es-comment-tag {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            color: #4c463e;
        }
        .dark .es-comment-tag { color: #a8a096; }
        .es-comment-band .es-comment-tag { color: #f8b26a; }

        /* A filing tab: flat along the bottom, where it meets the section. */
        .es-comment-num {
            display: inline-flex;
            align-items: center;
            padding: 0.3rem 0.9rem;
            border: 1px solid rgba(26, 22, 19, 0.18);
            border-bottom: 2px solid #8f4a12;
            border-radius: 0.4rem 0.4rem 0 0;
            background: #ffffff;
            color: #1a1613;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-weight: 800;
            letter-spacing: 0.12em;
        }
        .dark .es-comment-num {
            border-color: rgba(236, 231, 222, 0.2);
            border-bottom-color: #f8b26a;
            background: rgba(236, 231, 222, 0.05);
            color: #ece7de;
        }
        .es-comment-band .es-comment-num {
            border-color: rgba(236, 231, 222, 0.2);
            border-bottom-color: #f8b26a;
            background: rgba(236, 231, 222, 0.05);
            color: #ece7de;
        }

        .es-comment-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.25rem;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            border: 1px solid rgba(143, 74, 18, 0.45);
            color: #8f4a12;
        }
        .dark .es-comment-plan { border-color: rgba(248, 178, 106, 0.45); color: #f8b26a; }
        .es-comment-band .es-comment-plan { border-color: rgba(248, 178, 106, 0.45); color: #f8b26a; }
        .es-comment-plan-free {
            border-color: rgba(26, 22, 19, 0.35);
            color: #1a1613;
        }
        .dark .es-comment-plan-free { border-color: rgba(236, 231, 222, 0.38); color: #ece7de; }

        .es-comment-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.35rem 0.9rem;
            border-radius: 9999px;
            border: 1px solid rgba(26, 22, 19, 0.16);
            background: rgba(255, 255, 255, 0.7);
            color: #4c463e;
            font-size: 0.78rem;
            font-weight: 600;
        }
        .dark .es-comment-chip {
            border-color: rgba(236, 231, 222, 0.16);
            background: rgba(236, 231, 222, 0.05);
            color: #b7b0a6;
        }

        /* --- Links, buttons, hovers ----------------------------------- */
        .es-comment-link { color: #8f4a12; }
        .es-comment-link:hover { color: #1a1613; }
        .dark .es-comment-link { color: #f8b26a; }
        .dark .es-comment-link:hover { color: #ece7de; }

        .es-comment-btn {
            background-color: #8f4a12;
            box-shadow: 0 18px 36px -14px rgba(143, 74, 18, 0.5);
        }
        .es-comment-btn:hover { background-color: #7d4010; box-shadow: 0 22px 44px -14px rgba(143, 74, 18, 0.62); }
        .dark .es-comment-btn { background-color: #a85614; }
        .dark .es-comment-btn:hover { background-color: #b35d16; }

        .es-comment-hover:hover { border-color: rgba(143, 74, 18, 0.45); }
        .dark .es-comment-hover:hover { border-color: rgba(248, 178, 106, 0.45); }
        .es-comment-hover:hover .es-comment-hover-title,
        .es-comment-hover:hover .es-comment-hover-arrow { color: #8f4a12; }
        .dark .es-comment-hover:hover .es-comment-hover-title,
        .dark .es-comment-hover:hover .es-comment-hover-arrow { color: #f8b26a; }

        .es-comment-tip {
            border-color: rgba(26, 22, 19, 0.14);
            background: #ffffff;
            color: #1a1613;
        }
        .dark .es-comment-tip {
            border-color: rgba(236, 231, 222, 0.12);
            background: #1b1713;
            color: #ece7de;
        }

        /* --- Shared-system recolours (brand blue by default) ---------- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(143, 74, 18, 0.12), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(248, 178, 106, 0.1), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(143, 74, 18, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(248, 178, 106, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #8f4a12; }
        .dark .es-dot.is-active .es-dot-pip { background: #f8b26a; }

        /* --- Focus rings. No border-radius here: setting it changes the
               element's own shape on focus. Outlines already follow it. -- */
        #es-comment-page a:focus-visible,
        #es-comment-page summary:focus-visible,
        #es-comment-page button:focus-visible {
            outline: 2px solid #8f4a12;
            outline-offset: 3px;
        }
        .dark #es-comment-page a:focus-visible,
        .dark #es-comment-page summary:focus-visible,
        .dark #es-comment-page button:focus-visible {
            outline-color: #f8b26a;
        }
        .es-comment-band a:focus-visible,
        .es-comment-band summary:focus-visible,
        .es-comment-band button:focus-visible {
            outline-color: #f8b26a !important;
        }

        @media (prefers-reduced-motion: reduce) {
            html.es-anim .es-comment-meter-fill { animation: none !important; transform: none !important; }
        }
    </style>

    @php
        // One card, carried through the page. The scale is a whole number from
        // 1 to 5 because that is exactly what the column stores.
        $cardEvent = 'Thursday Jazz Session';
        $cardDate = 'Thu 18 Jun, 8:00 PM';
        $cardRating = 4;
        $cardComment = 'Great night. Sound was spot on. Could the doors open a little earlier next time?';

        // The six delays the schedule setting offers, in hours. 24 is the default.
        $delayStops = [1, 2, 6, 12, 24, 48];
        $defaultDelay = 24;

        // The thirty days during which a request will still go out. Day 1 is the
        // event's last day; with the default delay the card is posted on day 2.
        $windowDays = 30;
        $postedOnDay = 2;

        // The Feedback tab, as a record. Comments are short because the table
        // truncates them; the full text is on the card and in the export.
        $returned = [
            ['Dana R.',   'Thursday Jazz Session', '18 Jun', 5, 'The 8pm start worked much better.',        '19 Jun'],
            ['Marcus O.', 'Thursday Jazz Session', '18 Jun', 4, 'Sound was spot on. Doors a bit earlier?',  '19 Jun'],
            ['Priya S.',  'Saturday Matinee',      '20 Jun', 5, '',                                        '21 Jun'],
            ['Tom B.',    'Saturday Matinee',      '20 Jun', 3, 'Could not hear the announcements at the back.', '21 Jun'],
            ['Lena K.',   'Thursday Jazz Session', '25 Jun', 4, 'Bar queue moved fast this week.',          '26 Jun'],
            ['Owen H.',   'Open Studio',           '27 Jun', 5, 'Loved the walkthrough at the start.',      '28 Jun'],
        ];

        // The same cards as the event page publishes them: first name only, a
        // relative age, and the comment. No surname, no email, no link back.
        $publicReviews = [
            ['Dana',   5, '2 days ago',  'The 8pm start worked much better.'],
            ['Marcus', 4, '2 days ago',  'Sound was spot on. Doors a bit earlier?'],
            ['Tom',    3, '1 week ago',  'Could not hear the announcements at the back.'],
        ];

        // The four counters on the Feedback tab. Responded divided by everyone
        // who was either emailed or has already answered is the response rate,
        // which is how the tab works it out: 63 of 104 is 61 per cent.
        $counters = [
            ['Pending',       '12', 'queued, not sent yet'],
            ['Sent',          '41', 'awaiting a reply'],
            ['Responded',     '63', 'average 4.4 out of 5'],
            ['Response rate', '61%', '63 of 104 asked'],
        ];

        // Facts, not adjectives: every chip here is a number or a switch.
        $specChips = [
            'Rating 1 to 5',
            'Comment up to 2,000 characters',
            'One card per booking',
            '1 to 48 hours after the end',
            'Yours alone by default',
            'Per-event override',
            'CSV export',
            'feedback.submitted webhook',
            'Requests stop after 30 days',
        ];

        $spec = [
            ['Rating', 'Required, a whole number from 1 to 5'],
            ['Comment', 'Optional, up to 2,000 characters'],
            ['Cards per booking', 'One, enforced by the database'],
            ['Editing a card', 'Not possible once it is submitted'],
            ['Who is asked', 'Everyone who holds a paid booking, RSVP registrations included'],
            ['Request delay', '1, 2, 6, 12, 24 or 48 hours after that occurrence ends'],
            ['Default delay', '24 hours'],
            ['Queue', 'Checked hourly, so the times shown are approximate'],
            ['Requests stop', '30 days after the occurrence ended'],
            ['Draft events', 'The card will not open while an event is in draft'],
            ['Passes and subscriptions', 'Skipped, because they are not tied to one date'],
            ['Test and blank addresses', 'Skipped'],
            ['Public display', 'Off by default; an average plus the latest 20 when on'],
            ['Per event', 'Schedule default, on, or off'],
            ['Getting it out', 'CSV export, feedback.submitted webhook, REST API'],
            ['Plan', 'Pro, included on Enterprise and in selfhosted installations'],
        ];

        $faqs = [
            [
                'q' => 'How does post-event feedback work?',
                'a' => 'Once an event has finished, everyone who held a booking for that date is emailed a private link. It opens a card branded with your schedule: a rating from one to five, which is required, and a comment of up to 2,000 characters, which is not. They submit it, and you read it on the Feedback tab of your Sales page. Nothing is asked while the event is still on.',
            ],
            [
                'q' => 'Who can leave feedback?',
                'a' => 'Only somebody who holds a booking. The form sits behind a link tied to that one booking, and the link only travels in the request email, so it is not something a passer-by on your event page can fill in. Paid tickets and free RSVP registrations both count, because a registration is recorded as a booking with a zero total. Followers are not asked: following a schedule is not attending an event.',
            ],
            [
                'q' => 'When does the request go out?',
                'a' => 'After that occurrence has ended, plus a delay you choose from 1, 2, 6, 12, 24 or 48 hours. Twenty-four is the default. The end of the occurrence is worked out in the schedule\'s own timezone, so an evening event does not get asked about a day early. The queue is checked hourly, which makes the times approximate rather than exact, and no request goes out for an event that ended more than thirty days ago.',
            ],
            [
                'q' => 'Is feedback shown publicly?',
                'a' => 'Not unless you switch it on. "Show feedback publicly" starts off, and with it off every card is yours alone. Turn it on and the event page grows an Attendee Reviews block: an average, a count, and the twenty most recent cards, each showing the attendee\'s first name only. Attendees are told before they write, because the form carries a notice saying their words may appear on the event page.',
            ],
            [
                'q' => 'Can I moderate or delete a card?',
                'a' => 'No, and it is better to know that up front. There is no per-card approval queue and no way to remove one card from the public list: publishing is a single switch on the schedule, so it is all of them or none of them. That is exactly why it starts off. Fan photos, videos and comments are the feature that does have an approval queue, if per-item moderation is what you need.',
            ],
            [
                'q' => 'How many cards does one booking get?',
                'a' => 'One. Somebody who bought six tickets in a single booking is asked once, not six times, and the database holds a unique constraint on the booking so a second card cannot be created. Open the link again after submitting and you get a thank-you page instead of a blank card. Passes and subscriptions are skipped, because they are not tied to a single date.',
            ],
            [
                'q' => 'Do I need the Pro plan?',
                'a' => 'Yes. Post-event feedback is a Pro feature at '.plan_price($proMonthly).' a month, included on Enterprise, and selfhosted installations have it too. On the hosted platform it also needs your schedule\'s own email settings, because the request is sent from your address rather than ours. Once those are saved the toggle unlocks, and there is a button to send yourself a test card before you turn it loose on real attendees.',
            ],
            [
                'q' => 'Can I get the feedback out of Event Schedule?',
                'a' => 'Three ways. Export the lot to CSV from the Feedback tab. Subscribe a webhook to feedback.submitted and get the event, the date, the attendee\'s name and email, the rating and the comment posted to your endpoint as each card lands. Or read GET /api/feedback with an API key, filtering by event, subdomain, date, minimum rating and a date range. All three are on the same Pro plan.',
            ],
        ];

        $dotSections = [
            ['top', 'The card'],
            ['who', 'Who gets one'],
            ['when', 'When it goes out'],
            ['request', 'What lands'],
            ['public', 'Yours, or pinned up'],
            ['drawer', 'The returned cards'],
            ['alerts', 'When one comes back'],
            ['spec', 'The small print'],
            ['faq', 'Questions'],
            ['claim', 'Ask them'],
        ];
    @endphp

    <div id="es-comment-page" class="es-comment-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the returned card                                   -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 28% 32%, rgba(143, 74, 18, 0.2), rgba(143, 74, 18, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 72% 44%, rgba(248, 178, 106, 0.14), rgba(248, 178, 106, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="es-comment-accent h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3.75h5.25M21 12a8.955 8.955 0 01-1.05 4.22L21 21l-4.86-1.02A9 9 0 1121 12z" />
                        </svg>
                        <span class="es-comment-muted text-sm font-medium tracking-wide">Post-event feedback</span>
                        <span class="es-comment-plan">Pro</span>
                    </div>

                    <h1 class="es-balance es-comment-ink mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">A comment card</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">per <span class="es-comment-accent">ticket.</span></span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-comment-muted mb-10 max-w-xl text-lg sm:text-xl">
                        A day after the event ends, everyone who booked gets a card: a rating from one to five, and a comment if they have one. Nobody else can fill it in, and nobody can fill it in twice.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="{{ app_url('/sign_up') }}" class="es-comment-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Get started free
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                        <a href="{{ route('marketing.docs.tickets') }}#feedback" class="glass group inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            Read the Feedback guide
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    </div>
                </div>

                <!-- The card. A fixed physical object: identical in light and dark. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-comment-stock p-6 sm:p-7">
                        <div class="mb-4 flex items-start justify-between gap-3">
                            <span class="es-comment-stock-label">Comment card</span>
                            <span class="es-comment-stamp">Returned</span>
                        </div>
                        <div class="es-comment-stock-rule mb-4" aria-hidden="true"></div>

                        <p class="es-comment-stock-ink text-lg font-bold leading-snug">{{ $cardEvent }}</p>
                        <p class="es-comment-stock-muted mb-5 font-mono text-xs">{{ $cardDate }}</p>

                        <div aria-hidden="true">
                            <p class="es-comment-stock-label mb-2">How was your experience?</p>
                            <div class="es-comment-scale mb-5">
                                @foreach (range(1, 5) as $slot)
                                    <span class="es-comment-slot @if ($slot <= $cardRating) es-comment-slot-marked @endif">{{ $slot }}</span>
                                @endforeach
                            </div>

                            <p class="es-comment-stock-label mb-2">Anything else?</p>
                            <p class="es-comment-hand text-sm">{{ $cardComment }}</p>
                            <div class="es-comment-write mt-3">
                                <span class="es-comment-line"></span>
                                <span class="es-comment-line es-comment-line-short"></span>
                            </div>
                        </div>

                        <div class="es-comment-perf mt-6 pt-4">
                            <p class="es-comment-stock-muted text-[0.7rem] leading-relaxed">
                                One card per booking. Addressed to the name on it, posted after the event, and returnable once.
                                <span class="es-comment-stock-accent font-semibold">Not left out on a table.</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- The specification, as a marquee of numbers -->
            <div class="es-fade-up es-d-4 mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach ($specChips as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-comment-chip">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. Who gets a card (fixed-dark band)                         -->
    <!-- ============================================================ -->
    <section id="who" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-comment-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 25%, rgba(143, 74, 18, 0.3), rgba(143, 74, 18, 0) 60%); opacity: 0.6;"></div>
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-comment-num mb-6" data-reveal aria-hidden="true"><span>01</span></div>
                    <p class="es-comment-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Who gets one</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Not the room. <span class="es-comment-lit">The booking.</span>
                    </h2>
                    <p class="es-comment-band-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        A card is addressed to a booking, which is why the answers are worth reading. There is no open review box on the event page for people who were never there.
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    <div class="es-comment-card p-6" data-reveal="panel">
                        <p class="es-comment-tag mb-3">The address</p>
                        <h3 class="es-comment-band-ink mb-2 text-lg font-bold">The name on it</h3>
                        <p class="es-comment-band-muted text-sm">The request goes to the email address on a confirmed booking, and the link it carries belongs to that booking alone. Followers are not asked, and nor is anyone who simply opened the page.</p>
                    </div>
                    <div class="es-comment-card p-6" data-reveal="panel">
                        <p class="es-comment-tag mb-3">Free counts</p>
                        <h3 class="es-comment-band-ink mb-2 text-lg font-bold">RSVPs included</h3>
                        <p class="es-comment-band-muted text-sm">A registration is recorded as a booking with a zero total, so a free event that takes RSVPs collects cards exactly the way a paid one does. You do not have to sell a ticket to ask the question.</p>
                    </div>
                    <div class="es-comment-card p-6" data-reveal="panel">
                        <p class="es-comment-tag mb-3">Per booking</p>
                        <h3 class="es-comment-band-ink mb-2 text-lg font-bold">One card, not six</h3>
                        <p class="es-comment-band-muted text-sm">Somebody who booked six seats is asked once. Passes and subscriptions are skipped, since they are not tied to a single date, and so are test and blank addresses.</p>
                    </div>
                </div>

                <p class="es-comment-band-muted mt-10 text-center" data-reveal>
                    Post-event feedback is on the Pro plan.
                    <a href="{{ marketing_url('/pricing') }}" class="es-comment-lit inline-flex items-center gap-1 font-semibold transition-all hover:gap-2">
                        See what that costs
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. When the card goes out                                    -->
    <!-- ============================================================ -->
    <section id="when" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-comment-num mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                <p class="es-comment-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">When it goes out</p>
                <h2 class="es-balance es-comment-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Nothing is asked <span class="es-comment-accent">while the event is still on.</span>
                </h2>
                <p class="es-comment-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    The card is printed off the end of the occurrence, not off its start, and the end is worked out in your schedule's own timezone. Before that moment the link simply does not open.
                </p>
            </div>

            <div class="es-comment-card p-6 sm:p-8" data-reveal="panel">
                <div class="mb-3 flex flex-wrap items-baseline justify-between gap-2">
                    <h3 class="es-comment-ink text-lg font-bold">How long to wait</h3>
                    <span class="es-comment-muted font-mono text-xs">six choices, 24 hours by default</span>
                </div>
                <div class="es-comment-stops mb-2" aria-hidden="true">
                    @foreach ($delayStops as $stop)
                        <span class="es-comment-stop @if ($stop === $defaultDelay) es-comment-stop-on @endif">{{ $stop }}h</span>
                    @endforeach
                </div>
                <p class="es-comment-muted text-xs">
                    Hours after the occurrence ends. The setting is a plain dropdown with these six values on it, so an afternoon workshop can ask straight away while a late show waits until people are awake.
                </p>

                <div class="es-comment-rule mt-7 border-t pt-7">
                    <div class="mb-3 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-comment-ink text-lg font-bold">How long it keeps asking</h3>
                        <span class="es-comment-muted font-mono text-xs">30 days, then it stops</span>
                    </div>
                    <div class="es-comment-window mb-2" aria-hidden="true">
                        @foreach (range(1, $windowDays) as $day)
                            <span class="es-comment-day @if ($day === $postedOnDay) es-comment-day-sent @endif"></span>
                        @endforeach
                        <span class="es-comment-day es-comment-day-off"></span>
                        <span class="es-comment-day es-comment-day-off"></span>
                    </div>
                    <p class="es-comment-muted text-xs">
                        Day one is the day the event ended. The solid cell is the request going out on the default delay. Dashed cells are past the thirty-day cut-off, where no new request is created: an event you forgot to enable feedback on last spring will not suddenly email three hundred people. A card already sent stays open, so a late reply still counts.
                    </p>
                </div>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-3" data-reveal-group="90">
                <div class="es-comment-card p-6" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-comment-ink text-base font-bold">Checked hourly</h3>
                        <span class="es-comment-plan">Pro</span>
                    </div>
                    <p class="es-comment-muted text-sm">The queue runs once an hour, which is why the Feedback tab labels its send times as approximate. If you want them gone now, there is a send-now button.</p>
                </div>
                <div class="es-comment-card p-6" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-comment-ink text-base font-bold">Draft shuts the card</h3>
                        <span class="es-comment-plan es-comment-plan-free">Free</span>
                    </div>
                    <p class="es-comment-muted text-sm">While an event sits in draft its card will not open, even for somebody holding the link, and nothing typed into it can be submitted. It is the card that checks and not the queue, so drafting an event after tickets sold is not a way to recall a request already on its way. Draft is on every plan.</p>
                </div>
                <div class="es-comment-card p-6" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-comment-ink text-base font-bold">Per event, if you want</h3>
                        <span class="es-comment-plan">Pro</span>
                    </div>
                    <p class="es-comment-muted text-sm">
                        Each event can follow the schedule default, force feedback on, or force it off. Handy for the one night you would rather not ask about.
                        <a href="{{ route('marketing.docs.creating_events') }}#feedback" class="es-comment-link font-medium hover:underline">The per-event override</a>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. What lands, and what comes back                           -->
    <!-- ============================================================ -->
    <section id="request" class="es-comment-rule scroll-mt-24 border-y py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-comment-num mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                <p class="es-comment-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">What lands</p>
                <h2 class="es-balance es-comment-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    One email. <span class="es-comment-accent">Two questions.</span>
                </h2>
                <p class="es-comment-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    The request is short on purpose, and so is the card behind it. A rating you cannot skip, a comment you can, and a submit button that stays dead until the rating is marked.
                </p>
            </div>

            <div class="grid gap-4 lg:grid-cols-2" data-reveal-group="110">
                <!-- The email -->
                <div class="es-comment-card flex h-full flex-col p-6 sm:p-8" data-reveal="panel">
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <p class="es-comment-tag">The request</p>
                        <span class="es-comment-muted font-mono text-xs">sent from your address</span>
                    </div>

                    <div class="es-comment-rule mb-4 border-b pb-4" aria-hidden="true">
                        <p class="es-comment-muted font-mono text-xs">Subject</p>
                        <p class="es-comment-ink text-base font-bold">How was your experience?</p>
                    </div>

                    <div aria-hidden="true">
                        <p class="es-comment-muted mb-3 text-sm">Hello Dana,</p>
                        <p class="es-comment-muted mb-4 text-sm">We would love to hear your thoughts on the event you attended.</p>
                        <div class="es-comment-rule mb-4 rounded-lg border p-4">
                            <p class="es-comment-ink text-sm font-bold">{{ $cardEvent }}</p>
                            <p class="es-comment-muted mt-1 font-mono text-xs">{{ $cardDate }}</p>
                        </div>
                        <span class="es-comment-btn inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold text-white">Submit Feedback</span>
                    </div>

                    <p class="es-comment-muted mt-auto pt-6 text-sm">
                        If that event also collects photos, video or comments from attendees, the same email carries a second link inviting them to add theirs. And if they have allowed web push on your schedule, a notification goes out alongside it.
                    </p>
                </div>

                <!-- The form -->
                <div class="es-comment-card flex h-full flex-col p-6 sm:p-8" data-reveal="panel">
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <p class="es-comment-tag">The card</p>
                        <span class="es-comment-muted font-mono text-xs">your logo, colour and font</span>
                    </div>

                    <h3 class="es-comment-ink mb-1 text-xl font-bold">How was your experience?</h3>
                    <p class="es-comment-muted mb-5 text-sm">Five stars on the real form, one to five in the database. The card at the top of this page is our drawing of the same value.</p>

                    <div aria-hidden="true">
                        <p class="es-comment-muted mb-2 text-xs font-semibold uppercase tracking-wide">Rating <span class="es-comment-accent">*</span></p>
                        <div class="mb-5 flex gap-1.5">
                            @foreach (range(1, 5) as $star)
                                <svg role="img" aria-label="star" class="h-7 w-7 @if ($star <= $cardRating) es-comment-accent @else es-comment-muted @endif" viewBox="0 0 24 24" fill="{{ $star <= $cardRating ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                                </svg>
                            @endforeach
                        </div>

                        <p class="es-comment-muted mb-2 text-xs font-semibold uppercase tracking-wide">Comment <span class="font-normal normal-case">(optional)</span></p>
                        <div class="es-comment-rule mb-1 rounded-lg border p-3">
                            <p class="es-comment-muted text-sm">Share your thoughts about the event...</p>
                            <div class="es-comment-write mt-3">
                                <span class="es-comment-line"></span>
                                <span class="es-comment-line es-comment-line-short"></span>
                            </div>
                        </div>
                        <p class="es-comment-muted mb-5 text-right font-mono text-xs">0 / 2000</p>
                    </div>

                    <p class="es-comment-muted mt-auto text-sm">
                        Submit stays disabled until a rating is marked, so a card cannot come back empty. Open the link again afterwards and you get a thank-you page rather than a second blank card.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Yours alone, or pinned up                                 -->
    <!-- ============================================================ -->
    <section id="public" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-comment-num mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                <p class="es-comment-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Yours, or pinned up</p>
                <h2 class="es-balance es-comment-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Read them yourself, <span class="es-comment-accent">or pin them up.</span>
                </h2>
                <p class="es-comment-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    One switch, off by default. Leave it off and the cards are private working notes. Turn it on and they become the reviews that help the next person decide whether to come.
                </p>
            </div>

            <div class="grid gap-4 lg:grid-cols-2" data-reveal-group="110">
                <!-- Off -->
                <div class="es-comment-card flex h-full flex-col p-6 sm:p-8" data-reveal="panel">
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <p class="es-comment-tag">Show publicly: off</p>
                        <span class="es-comment-plan es-comment-plan-free">Default</span>
                    </div>
                    <h3 class="es-comment-ink mb-3 text-xl font-bold">Nobody else reads them</h3>
                    <p class="es-comment-muted mb-5 text-sm">Cards land on the Feedback tab, in the CSV export, on the webhook and in the API. The event page shows nothing at all, so a rough night stays between you and the person who had it.</p>
                    <ul class="es-comment-muted space-y-3 text-sm">
                        <li class="flex gap-3">
                            <svg aria-hidden="true" class="es-comment-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>You see the attendee's full name and email, because you already do on the booking.</span>
                        </li>
                        <li class="flex gap-3">
                            <svg aria-hidden="true" class="es-comment-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>An average across every card you hold, and a response rate, sit above the table.</span>
                        </li>
                    </ul>
                    <p class="es-comment-muted mt-auto pt-6 text-sm">
                        This is where most schedules stay, and it is the honest default: feedback you can act on does not have to be feedback everyone can read.
                    </p>
                </div>

                <!-- On -->
                <div class="es-comment-card flex h-full flex-col p-6 sm:p-8" data-reveal="panel">
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <p class="es-comment-tag">Show publicly: on</p>
                        <span class="es-comment-plan">Pro</span>
                    </div>
                    <h3 class="es-comment-ink mb-3 text-xl font-bold">Attendee reviews on the event page</h3>

                    <div class="es-comment-rule mb-5 rounded-xl border p-5" aria-hidden="true">
                        <p class="es-comment-ink mb-3 text-sm font-bold">Attendee Reviews</p>
                        <div class="mb-4 flex items-center gap-2">
                            <span class="es-comment-rate">
                                @foreach (range(1, 5) as $avgPip)
                                    <span class="es-comment-pip @if ($avgPip <= 4) es-comment-pip-on @endif"></span>
                                @endforeach
                            </span>
                            <span class="es-comment-ink font-mono text-xs font-bold">4.4</span>
                            <span class="es-comment-muted font-mono text-xs">63 reviews</span>
                        </div>
                        @foreach ($publicReviews as [$pubName, $pubRating, $pubAge, $pubComment])
                            <div class="@if (! $loop->first) es-comment-rule mt-3 border-t pt-3 @endif">
                                <div class="mb-1 flex flex-wrap items-center gap-2">
                                    <span class="es-comment-rate">
                                        @foreach (range(1, 5) as $pubPip)
                                            <span class="es-comment-pip @if ($pubPip <= $pubRating) es-comment-pip-on @endif"></span>
                                        @endforeach
                                    </span>
                                    <span class="es-comment-ink text-xs font-semibold">{{ $pubName }}</span>
                                    <span class="es-comment-muted text-xs">{{ $pubAge }}</span>
                                </div>
                                <p class="es-comment-muted text-xs">{{ $pubComment }}</p>
                            </div>
                        @endforeach
                    </div>

                    <p class="es-comment-muted mb-4 text-sm">An average, a count, and the twenty most recent cards, worked out for that event and, on a recurring one, for that date. Each card shows the attendee's first name and nothing more, and cancelled or deleted bookings drop out of the list.</p>
                    <p class="es-comment-muted mt-auto text-sm">
                        Attendees are told before they write: the card carries a notice saying their words may appear on the event page and their first name will be shown.
                    </p>
                </div>
            </div>

            <div class="es-comment-card mx-auto mt-6 max-w-3xl p-6" data-reveal="panel">
                <div class="mb-2 flex flex-wrap items-center gap-2">
                    <h3 class="es-comment-ink text-base font-bold">Being straight about moderation</h3>
                </div>
                <p class="es-comment-muted text-sm">
                    There is no per-card approval queue for feedback and no way to hide one card while publishing the rest. Publishing is a single switch on the schedule, which is precisely why it starts off. If per-item moderation is what you need, fan photos, videos and comments do have an approval queue.
                    <a href="{{ marketing_url('/features/fan-videos') }}" class="es-comment-link font-medium hover:underline">How fan content is moderated</a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. The returned cards: a real record                         -->
    <!-- ============================================================ -->
    <section id="drawer" class="es-comment-rule scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-comment-num mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                <p class="es-comment-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The returned cards</p>
                <h2 class="es-balance es-comment-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    Every card in <span class="es-comment-accent">one drawer.</span>
                </h2>
                <p class="es-comment-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    The Feedback tab of your Sales page counts what is queued, what has gone out, what has come back and what share of the asking worked.
                </p>
            </div>

            <div class="mb-4 grid grid-cols-2 gap-4 lg:grid-cols-4" data-reveal-group="80">
                @foreach ($counters as $ci => [$cLabel, $cValue, $cNote])
                    <div class="es-comment-card p-5" data-reveal="panel">
                        <p class="es-comment-tag mb-3">{{ $cLabel }}</p>
                        <p class="es-comment-ink text-3xl font-black tabular-nums">{{ $cValue }}</p>
                        @if ($ci === 3)
                            <div class="es-comment-meter mt-3" aria-hidden="true">
                                <span class="es-comment-meter-fill" style="--w: 61%;"></span>
                            </div>
                        @endif
                        <p class="es-comment-muted mt-2 text-xs">{{ $cNote }}</p>
                    </div>
                @endforeach
            </div>

            <div class="es-comment-card overflow-x-auto p-6 sm:p-8" data-reveal="panel">
                <table class="w-full border-collapse text-left">
                    <caption class="sr-only">Returned feedback cards, with the attendee, event, date, rating, comment and the day it was submitted</caption>
                    <thead>
                        <tr class="es-comment-tag">
                            <th scope="col" class="pb-3 pe-3 font-bold">Attendee</th>
                            <th scope="col" class="hidden pb-3 pe-3 font-bold sm:table-cell">Event</th>
                            <th scope="col" class="pb-3 pe-3 font-bold">Date</th>
                            <th scope="col" class="pb-3 pe-3 font-bold">Rating</th>
                            <th scope="col" class="hidden pb-3 pe-3 font-bold md:table-cell">Comment</th>
                            <th scope="col" class="pb-3 font-bold">Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($returned as [$rName, $rEvent, $rDate, $rRating, $rComment, $rSubmitted])
                            <tr class="es-comment-rule border-t">
                                <th scope="row" class="es-comment-ink py-3 pe-3 align-middle text-sm font-bold">{{ $rName }}</th>
                                <td class="es-comment-muted hidden py-3 pe-3 align-middle text-sm sm:table-cell">{{ $rEvent }}</td>
                                <td class="es-comment-muted py-3 pe-3 align-middle font-mono text-xs">{{ $rDate }}</td>
                                <td class="py-3 pe-3 align-middle">
                                    <span class="es-comment-rate" role="img" aria-label="{{ $rRating }} out of 5">
                                        @foreach (range(1, 5) as $pip)
                                            <span class="es-comment-pip @if ($pip <= $rRating) es-comment-pip-on @endif"></span>
                                        @endforeach
                                    </span>
                                </td>
                                <td class="es-comment-muted hidden py-3 pe-3 align-middle text-sm md:table-cell">{{ $rComment !== '' ? $rComment : 'No comment left' }}</td>
                                <td class="es-comment-muted py-3 align-middle font-mono text-xs">{{ $rSubmitted }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p class="es-comment-muted mt-4 text-xs">
                    Sortable by attendee, event, date, rating and submission date. Comments are shortened in the table and printed in full in the export. A card with no comment is still a card: the rating is the required half.
                </p>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-3" data-reveal-group="90">
                <div class="es-comment-card p-6" data-reveal="panel">
                    <h3 class="es-comment-ink mb-2 text-base font-bold">Resend one</h3>
                    <p class="es-comment-muted text-sm">A request that landed in a spam folder can be sent again from the row it sits on, for that one attendee.</p>
                </div>
                <div class="es-comment-card p-6" data-reveal="panel">
                    <h3 class="es-comment-ink mb-2 text-base font-bold">Send the ready ones now</h3>
                    <p class="es-comment-muted text-sm">Everything past its delay but still waiting on the hourly queue can be pushed out immediately from the actions menu.</p>
                </div>
                <div class="es-comment-card p-6" data-reveal="panel">
                    <h3 class="es-comment-ink mb-2 text-base font-bold">Or cancel the lot</h3>
                    <p class="es-comment-muted text-sm">Changed your mind about a whole batch? Cancel every pending request in one go before any of it leaves.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. When a card comes back (fixed-dark band)                  -->
    <!-- ============================================================ -->
    <section id="alerts" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-comment-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 70% 25%, rgba(248, 178, 106, 0.16), rgba(248, 178, 106, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-comment-num mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                    <p class="es-comment-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">When one comes back</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        A card should not sit there <span class="es-comment-lit">waiting to be noticed.</span>
                    </h2>
                </div>

                <div class="grid gap-6 md:grid-cols-2" data-reveal-group="100">
                    <div class="es-comment-card p-6" data-reveal="panel">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="es-comment-band-ink text-lg font-bold">A push, straight away</h3>
                            <span class="es-comment-plan">Pro</span>
                        </div>
                        <p class="es-comment-band-muted text-sm">If web push is switched on for your schedule and you have allowed notifications on a device, a new card arrives as a notification the moment it is submitted. That channel does not wait on email being configured.</p>
                    </div>
                    <div class="es-comment-card p-6" data-reveal="panel">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="es-comment-band-ink text-lg font-bold">An email, if you asked for one</h3>
                            <span class="es-comment-plan">Pro</span>
                        </div>
                        <p class="es-comment-band-muted text-sm">Switch on the New feedback notification and you get the event, the attendee, the rating and the comment by email. On the hosted platform this waits until the schedule has its own email settings, the same requirement as the request. Enterprise schedules can have several admins subscribed.</p>
                    </div>
                    <div class="es-comment-card p-6" data-reveal="panel">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="es-comment-band-ink text-lg font-bold">A webhook, into your own stack</h3>
                            <span class="es-comment-plan">Pro</span>
                        </div>
                        <p class="es-comment-band-muted text-sm">Subscribe to feedback.submitted and every card is posted to your endpoint as it lands, carrying the event, the date, the attendee's name and email, the rating and the comment.</p>
                    </div>
                    <div class="es-comment-card p-6" data-reveal="panel">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="es-comment-band-ink text-lg font-bold">Or read it back on demand</h3>
                            <span class="es-comment-plan">Pro</span>
                        </div>
                        <p class="es-comment-band-muted text-sm">GET /api/feedback returns the cards for schedules your API key administers, filtered by event, subdomain, date, a minimum rating or a date range. Handy for putting the ratings on a site of your own.</p>
                    </div>
                </div>

                <p class="es-comment-band-muted mt-10 text-center" data-reveal>
                    Before any of it goes near a real attendee, send yourself a test card from the schedule's Feedback settings.
                    <a href="{{ route('marketing.docs.creating_schedules') }}#engagement-feedback" class="es-comment-lit inline-flex items-center gap-1 font-semibold transition-all hover:gap-2">
                        Where the settings live
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. The small print                                           -->
    <!-- ============================================================ -->
    <section id="spec" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-comment-num mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                <p class="es-comment-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The small print</p>
                <h2 class="es-balance es-comment-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    The whole card, <span class="es-comment-accent">terms and all.</span>
                </h2>
                <p class="es-comment-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Printed on the back, where the small print belongs. Nothing here should surprise you after you have asked three hundred people how it went.
                </p>
            </div>

            {{-- The same fixed physical object as the hero and the finale, turned
                 over: the small print really is printed on the back of the card. --}}
            <div class="es-comment-stock p-6 sm:p-8" data-reveal="panel">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <span class="es-comment-stock-label">Comment card / reverse</span>
                    <span class="es-comment-stamp">Terms</span>
                </div>
                <div class="es-comment-stock-rule mb-3" aria-hidden="true"></div>
                {{-- Printed-form layout: a wide-tracked field label with the value
                     set under it. A right-aligned two-column list cannot hold these
                     values without one column running into the next. --}}
                <dl class="grid gap-x-10 sm:grid-cols-2">
                    @foreach ($spec as [$specName, $specValue])
                        <div class="es-comment-stock-row py-3">
                            <dt class="es-comment-stock-label mb-1.5">{{ $specName }}</dt>
                            <dd class="es-comment-stock-ink text-sm font-semibold leading-snug">{{ $specValue }}</dd>
                        </div>
                    @endforeach
                </dl>
                <div class="es-comment-perf mt-6 pt-4">
                    <p class="es-comment-stock-muted text-xs leading-relaxed">
                        On the hosted platform, feedback emails need the schedule's own email settings saved first, because the request is sent from your address rather than ours. Selfhosted installations have the Pro feature set, so feedback is available there as soon as a real mailer is configured.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Related features                                          -->
    <!-- ============================================================ -->
    <section class="py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-comment-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="The bookings that a feedback card is addressed to" :url="marketing_url('/features/ticketing')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Event Polls" description="The other question, asked before the event instead of after" :url="marketing_url('/features/polls')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Fan Videos & Comments" description="Attendee photos, video and comments, with an approval queue" :url="marketing_url('/features/fan-videos')" icon-color="rose">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Write back to the people who told you how it went" :url="marketing_url('/features/newsletters')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Analytics" description="What the numbers measure, next to what people said" :url="marketing_url('/features/analytics')" icon-color="amber">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-comment-link inline-flex items-center font-medium hover:underline">
                    See all features
                    <svg aria-hidden="true" class="ml-1 h-4 w-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. Keep reading                                             -->
    <!-- ============================================================ -->
    <section class="es-comment-rule border-t py-16 lg:py-20">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-comment-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Keep reading</h2>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3" data-reveal-group="70">
                @php
                    $keepReading = [
                        [route('marketing.docs.tickets') . '#feedback', 'The Feedback guide', 'Enabling it, reading it, exporting it.'],
                        [route('marketing.docs.creating_schedules') . '#engagement-feedback', 'Schedule settings', 'The toggle, the delay and the public switch.'],
                        [marketing_url('/features/ticketing'), 'Ticketing', 'Where a booking, and its card, come from.'],
                        [marketing_url('/features/polls'), 'Event polls', 'Ask before the event instead of after it.'],
                        [route('marketing.docs.developer.api') . '#list-feedback', 'GET /api/feedback', 'Reading the cards back into your own stack.'],
                        [marketing_url('/pricing'), 'Pricing', 'What the Pro plan costs and what else is in it.'],
                    ];
                @endphp
                @foreach ($keepReading as [$krHref, $krName, $krBlurb])
                    <a href="{{ $krHref }}" class="es-comment-card es-comment-hover group flex flex-col p-5 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-comment-hover-title es-comment-ink mb-2 text-sm font-bold transition-colors">{{ $krName }}</span>
                        <span class="es-comment-muted mb-3 text-xs leading-relaxed">{{ $krBlurb }}</span>
                        <span class="es-comment-hover-arrow es-comment-muted mt-auto inline-flex items-center gap-1 text-xs font-semibold transition-colors">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    @include('marketing.partials.pricing-nudge')

    <!-- ============================================================ -->
    <!-- 11. FAQ                                                      -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="es-comment-num mb-6" data-reveal aria-hidden="true"><span>08</span></div>
                <h2 class="es-balance es-comment-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-comment-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    Everything people ask before they start asking their attendees.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-comment-card es-comment-hover group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-comment-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-comment-accent flex-none font-mono text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-comment-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-comment-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-comment-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 12. Finale: the blank card                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-comment-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(143, 74, 18, 0.34), rgba(143, 74, 18, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    <!-- The last card on the page is the blank one: same stock,
                         same boxed scale, nothing filled in yet. -->
                    <div class="mx-auto mb-10 max-w-sm" aria-hidden="true">
                        <div class="es-comment-stock p-5 text-start">
                            <div class="mb-3 flex items-start justify-between gap-3">
                                <span class="es-comment-stock-label">Comment card</span>
                                <span class="es-comment-stamp">Blank</span>
                            </div>
                            <div class="es-comment-stock-rule mb-4"></div>
                            <div class="mb-4 flex items-center gap-3">
                                <span class="es-comment-stock-label flex-none">Event</span>
                                <span class="es-comment-line flex-1"></span>
                            </div>
                            <div class="es-comment-scale mb-4">
                                @foreach (range(1, 5) as $blankSlot)
                                    <span class="es-comment-slot">{{ $blankSlot }}</span>
                                @endforeach
                            </div>
                            <div class="es-comment-write">
                                <span class="es-comment-line"></span>
                                <span class="es-comment-line"></span>
                                <span class="es-comment-line es-comment-line-short"></span>
                            </div>
                            <div class="es-comment-perf mt-5 pt-4">
                                <p class="es-comment-stock-muted text-[0.7rem] leading-relaxed">Your event, your logo, your colour. The words are theirs.</p>
                            </div>
                        </div>
                    </div>

                    <p class="es-comment-tag mb-4">Free to start</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Stop guessing how it went. <span class="es-comment-lit">Ask the room.</span>
                    </h2>
                    <p class="es-comment-band-muted mx-auto mb-10 max-w-2xl text-lg">
                        Claim your schedule and publish your events for free, forever. Post-event feedback is on the Pro plan at {{ plan_price($proMonthly) }} a month, with zero platform fees on anything you sell.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-comment-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Start for free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="es-comment-band-muted mt-6 text-sm">No credit card required</p>
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
                        <span class="es-comment-tip pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
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
