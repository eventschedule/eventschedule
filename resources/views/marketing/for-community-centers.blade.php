<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Community Centers | Manage Programs</x-slot>
    <x-slot name="description">Keep your community connected. Manage programs, classes, hall-hire requests, and events. Email members directly - no algorithm. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Community Centers</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Community Centers",
        "description": "Keep your community connected. Manage programs, classes, hall-hire requests, and events. Email members directly. Free forever.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Community Centers & Recreation Facilities"
        }
    }
    </script>
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Community Centers",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Community Center Event Management Software",
        "operatingSystem": "Web",
        "description": "Every program the center runs, on one calendar with its own link. Embed it, sync it, print its QR code, and email the people who follow it. Free forever.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Free forever"
        },
        "featureList": [
            "Recurring weekly programs with date exceptions for the weeks they skip",
            "Sub-schedules that group and color-code the strands, each with its own link",
            "Free RSVP sign-up with an optional capacity, counted afresh for every date",
            "Hall-hire requests that stay pending until you accept them",
            "Public program calendar with an embeddable iframe",
            "Two-way Google, Outlook and CalDAV calendar sync, plus a subscribable iCal feed that unrolls every date",
            "A downloadable QR code that opens your calendar",
            "Direct newsletters to the people who follow the center",
            "Ticketed classes through your own Stripe account with zero platform fees, 25 paid tickets a month on the free plan",
            "QR ticket scanning at the door on every plan",
            "Member photos and comments on events, held in an approval queue (25 photos on the free plan)",
            "Online events with the link people join on"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "community center calendar, recreation program schedule, facility booking software, community events, free community center scheduling",
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
           For-community-centers "The Gathering Place" styles.

           CONCEPT: the noticeboard in the lobby. A community center does
           not have a scheduling problem - the week already runs like
           clockwork. It has a REACH problem: the only copy of the
           timetable is a laminated sheet pinned to cork by the front
           door, and cork only reaches whoever walks past it. So the page
           keeps the board and multiplies the doors out of it: a link, an
           iframe on the town website, a phone's own calendar, an inbox, a
           printed QR code, and member photos coming back the other way.
           Metaphor and feature story are the same sentence.

           FOUR DEVICES CARRY IT:
             1. THE BOARD - kraft cork in a stained frame holding pinned
                index cards. A fixed physical object: it has no .dark
                variant and no dark: utility anywhere inside it, so it
                renders identically with .dark on and off. Verified with
                --bands=.es-gather-board,.es-gather-band (0 diffs).
             2. THE WEEK AS A REAL TABLE - seven day columns, one row per
                program, marks on the days it runs. The table IS the
                argument for recurring events (day-of-week patterns) and
                sub-schedules (the strand dot), because that is exactly
                the shape of the data.
             3. SIX DOORS - the always-dark band. Same board, six ways out
                of it, five of them free.
             4. THE PINNED NUMBER TAB - section marks are index-card
                corners with a pushpin, so the board's material runs the
                whole length of the page, including inside the dark band.
             5. THE TRAY AND THE PIN - a hall-hire request is a loose slip
                in a tray until you accept it, and accepting it is what
                pins it to the board. The pin IS is_accepted, so the mock
                and the permission model are the same picture. (Earlier
                drafts used a rotated rubber ACCEPTED stamp; for-venues
                already owns that stamp on the same kind of mock, so it
                went, and the pin does the job with the concept's own
                vocabulary.)

           CLAIM DISCIPLINE. Deleted from the first-wave page: multi-room
           scheduling and "avoid scheduling conflicts" (no rooms exist and
           there is no conflict detection anywhere in the codebase);
           "members receive email notifications for new programs" (no such
           job, mailable or command - NewsletterEmail is composed and sent
           by the owner); "Sending to 2,341 members" (a fabricated follower
           count); "livestreamed events" (an online event is one generic
           event_url field). Corrected: newsletters are FREE at 10 emails a
           month counted per recipient, not Pro. Sub-schedules are named as
           grouping only, since Group is fillable on name, name_en, slug
           and color and therefore has no visibility flag. Also removed on
           review: "room bookings" in the meta description and the Service
           schema, and room names ("main hall", "gym", "room A") plus
           headcounts ("40 people") used as slip data in the hero and
           hall-hire mocks - an Event has no room, space or headcount
           field, so nothing here may render one. And the 25-photo free-tier
           cap on fan photos is now stated wherever the photos claim is.

           SECOND-WAVE CORRECTIONS. Selling tickets is NOT Pro: the free plan
           sells 25 paid tickets a month per schedule (Role::ticketSaleLimit,
           config/usage.php), with zero platform fees on every plan, so the
           "paid class" panel, its closing line and two FAQ answers said the
           wrong tier. Pro removes the ceiling and adds the extras that ARE
           gated (passes and promo codes are scrubbed in EventRepo::saveEvent,
           the waitlist and the sales CSV export and custom fields each carry
           their own isPro check). Door-scanning is free on every plan
           (TicketController::scan has no plan check); only the live check-in
           dashboard is Pro, so the schema featureList no longer says
           "check-in". And no RRULE is emitted anywhere in app/: Google,
           Outlook and CalDAV each receive one instance built from
           getStartDateTime(), so a weekly program syncs as ONE entry. Only the
           iCal feed unrolls the dates (FeedController::icalFeed walks 90 days
           and writes a VEVENT per match), which the sync copy now says.

           COLOUR. Kept the page's existing teal family with its warm
           terracotta second, re-inked to measure. Distinctiveness comes
           from material (cork, index-card stock, a pushpin, a paper tray)
           and from typography (serif card stock against sans chrome,
           tabular mono in the timetable), not from a new hue.

           MEASURED (ratio : ground)
             ground #f4f5f2   card #fcfcfa   sub #eff0ec
             ink    #111917  16.33 : ground
             muted  #4b5553   7.05 : ground   7.51 : card   6.74 : sub
             accent #0b5b52   7.29 : ground   7.77 : card
             warm   #95350f   6.87 : ground   7.32 : card
             grad   #0b5b52 -> #0a4f63   7.29 / 8.30 : ground
             white on #0b5b52 button 7.98, hover #08463f 10.72

             dark ground #0c1211   card #161d1c   sub #1b2322
             ink    #e8edec  15.99 : ground
             muted  #a0acaa   8.08 : ground   7.32 : card   6.85 : sub
             accent #5fdcc9  11.32 : ground  10.25 : card
             warm   #f0a374   9.18 : ground   8.31 : card
             grad   #8de6d2 -> #5fdcc9  12.95 / 11.32 : ground

             band #0f1615 (identical in both modes)
               white 17.6, gray-300 12.44, gray-400 7.22, #a0acaa 7.83,
               lit #5fdcc9 10.97, warm #f0a374 8.90, card stock 16.83

             board (identical in both modes)
               cork #c8a878, frame #7a4f28
               card stock #faf5e8: ink #2b2925 13.34, muted #5d574d 6.57,
               day label #8f3411 7.23
           ============================================================== */

        /* --- Ground and ink ---------------------------------------- */
        .es-gather-page { background-color: #f4f5f2; color: #111917; }
        .dark .es-gather-page { background-color: #0c1211; color: #e8edec; }
        .es-gather-ink { color: #111917; }
        .dark .es-gather-ink { color: #e8edec; }
        .es-gather-muted { color: #4b5553; }
        .dark .es-gather-muted { color: #a0acaa; }
        .es-gather-accent { color: #0b5b52; }
        .dark .es-gather-accent { color: #5fdcc9; }
        .es-gather-warm { color: #95350f; }
        .dark .es-gather-warm { color: #f0a374; }
        /* Always-lit accent, for text that sits on the fixed dark band. */
        .es-gather-lit { color: #5fdcc9; }

        .es-gather-grad {
            background-image: linear-gradient(102deg, #0b5b52, #0a4f63);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dark .es-gather-grad { background-image: linear-gradient(102deg, #8de6d2, #5fdcc9); }

        .es-gather-num {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, 'Liberation Mono', monospace;
            font-variant-numeric: tabular-nums;
        }
        .es-gather-rule { height: 1px; background: rgba(17, 25, 23, 0.1); }
        .dark .es-gather-rule { background: rgba(232, 237, 236, 0.12); }

        /* --- Eyebrow ------------------------------------------------ */
        .es-gather-tag {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #0b5b52;
        }
        .dark .es-gather-tag { color: #5fdcc9; }

        /* --- Surfaces ----------------------------------------------- */
        .es-gather-card {
            background-color: #fcfcfa;
            border: 1px solid rgba(17, 25, 23, 0.12);
            border-radius: 0.75rem;
        }
        .dark .es-gather-card { background-color: #161d1c; border-color: rgba(232, 237, 236, 0.13); }
        .es-gather-sub { background-color: #eff0ec; border-radius: 0.5rem; }
        .dark .es-gather-sub { background-color: #1b2322; }
        .es-gather-hover { transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        .es-gather-hover:hover { border-color: rgba(11, 91, 82, 0.45); box-shadow: 0 10px 26px -18px rgba(17, 25, 23, 0.5); }
        .dark .es-gather-hover:hover { border-color: rgba(95, 220, 201, 0.4); box-shadow: 0 10px 26px -18px rgba(0, 0, 0, 0.85); }

        /* Honesty callout: what the product does NOT do. */
        .es-gather-flag {
            border-left: 3px solid #95350f;
            background-color: rgba(149, 53, 15, 0.06);
            border-radius: 0 0.5rem 0.5rem 0;
        }
        .dark .es-gather-flag { border-left-color: #f0a374; background-color: rgba(240, 163, 116, 0.09); }

        /* --- Buttons ------------------------------------------------ */
        .es-gather-btn {
            background-color: #0b5b52;
            color: #ffffff;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .es-gather-btn:hover {
            background-color: #08463f;
            transform: translateY(-1px);
            box-shadow: 0 14px 28px -16px rgba(11, 91, 82, 0.9);
        }
        .es-gather-ghost {
            border: 1px solid rgba(17, 25, 23, 0.22);
            color: #111917;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }
        .es-gather-ghost:hover { border-color: rgba(11, 91, 82, 0.5); background-color: rgba(11, 91, 82, 0.06); }
        .dark .es-gather-ghost { border-color: rgba(232, 237, 236, 0.24); color: #e8edec; }
        .dark .es-gather-ghost:hover { border-color: rgba(95, 220, 201, 0.45); background-color: rgba(95, 220, 201, 0.08); }

        /* --- Plan tiers. Never reuse these for a state badge. ------- */
        .es-gather-plan {
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
        .es-gather-plan-free { border-color: rgba(17, 25, 23, 0.24); color: #4b5553; }
        .dark .es-gather-plan-free { border-color: rgba(232, 237, 236, 0.26); color: #a0acaa; }
        .es-gather-plan-pro { border-color: rgba(11, 91, 82, 0.5); color: #0b5b52; background-color: rgba(11, 91, 82, 0.08); }
        .dark .es-gather-plan-pro { border-color: rgba(95, 220, 201, 0.42); color: #5fdcc9; background-color: rgba(95, 220, 201, 0.1); }

        /* ==============================================================
           THE BOARD. A real object: cork in a stained frame. It has no
           .dark rule and carries no dark: utility, so it paints the same
           in both colour modes. Everything pinned to it is the same.
           ============================================================== */
        .es-gather-board {
            background-color: #c8a878;
            background-image:
                radial-gradient(circle at 16% 24%, rgba(96, 58, 24, 0.28) 0, rgba(96, 58, 24, 0.28) 1.5px, transparent 2px),
                radial-gradient(circle at 56% 11%, rgba(96, 58, 24, 0.17) 0, rgba(96, 58, 24, 0.17) 1px, transparent 2px),
                radial-gradient(circle at 83% 54%, rgba(96, 58, 24, 0.23) 0, rgba(96, 58, 24, 0.23) 1.5px, transparent 2px),
                radial-gradient(circle at 34% 77%, rgba(96, 58, 24, 0.15) 0, rgba(96, 58, 24, 0.15) 1px, transparent 2px),
                radial-gradient(circle at 69% 89%, rgba(96, 58, 24, 0.21) 0, rgba(96, 58, 24, 0.21) 1.5px, transparent 2px);
            background-size: 88px 88px, 68px 68px, 108px 108px, 78px 78px, 98px 98px;
            border: 8px solid #7a4f28;
            border-radius: 0.5rem;
            box-shadow:
                inset 0 2px 14px rgba(58, 32, 11, 0.45),
                0 18px 40px -22px rgba(24, 14, 5, 0.85);
        }
        /* A single-card strip of the same board, for the finale. */
        .es-gather-strip { border-width: 6px; }

        /* Pinned index card. Serif stock against the page's sans chrome. */
        .es-gather-slip {
            background-color: #faf5e8;
            border-radius: 2px;
            box-shadow: 0 6px 14px rgba(40, 22, 6, 0.42);
            font-family: ui-serif, Georgia, 'Times New Roman', serif;
        }
        .es-gather-slip-day {
            display: block;
            color: #8f3411;
            font-family: ui-sans-serif, system-ui, sans-serif;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }
        .es-gather-slip-title { color: #2b2925; font-weight: 700; }
        .es-gather-slip-sub {
            color: #5d574d;
            font-family: ui-sans-serif, system-ui, sans-serif;
            font-size: 0.7rem;
        }
        .es-gather-slip-url {
            color: #2b2925;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.72rem;
            font-weight: 700;
            word-break: break-all;
        }
        /* Faint ruled lines, so the stock reads as an index card. */
        .es-gather-ruled {
            background-image: repeating-linear-gradient(180deg, transparent 0 15px, rgba(43, 41, 37, 0.09) 15px 16px);
        }

        /* Pushpin. Two shades so the board is not monotonous. */
        .es-gather-pin {
            height: 13px;
            width: 13px;
            border-radius: 999px;
            background-image: radial-gradient(circle at 34% 28%, #f2d9c6, #8f3411 72%);
            box-shadow: 0 2px 3px rgba(30, 16, 4, 0.55);
        }
        .es-gather-pin-teal { background-image: radial-gradient(circle at 34% 28%, #cdf3ea, #0b5b52 72%); }

        /* The tray a request sits in before anyone has pinned it up. The
           accepted request goes onto a strip of the board instead, so the
           pin is doing the work a rubber ACCEPTED stamp would have done
           (and for-venues already owns that stamp).

           NOTE: the tray is deliberately NOT part of the fixed object. It
           is a recessed region of the panel, in the same family as
           .es-gather-sub, so it flips with the colour mode like every other
           surface. Only the board, the card stock, the pin and the numbered
           corner are pinned across modes: verify with
           --bands=.es-gather-board,.es-gather-corner,.es-gather-slip and
           do not add this class to that list. */
        .es-gather-tray {
            background-color: #eff0ec;
            border: 1px dashed rgba(17, 25, 23, 0.22);
            border-radius: 0.5rem;
        }
        .dark .es-gather-tray { background-color: #1b2322; border-color: rgba(232, 237, 236, 0.22); }

        /* Section mark: an index-card corner with a pin through it. Fixed
           stock in both modes, so it also works on the dark band. */
        .es-gather-corner {
            position: relative;
            display: inline-flex;
            align-items: flex-end;
            justify-content: center;
            width: 2.6rem;
            height: 2.6rem;
            padding-bottom: 0.3rem;
            background-color: #faf5e8;
            border-radius: 2px;
            box-shadow: 0 5px 12px rgba(40, 22, 6, 0.4);
            color: #2b2925;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.8rem;
            font-weight: 700;
            transform: rotate(-2.5deg);
        }
        .es-gather-corner::after {
            content: "";
            position: absolute;
            left: 50%;
            top: 0.34rem;
            height: 9px;
            width: 9px;
            margin-left: -4.5px;
            border-radius: 999px;
            background-image: radial-gradient(circle at 34% 28%, #cdf3ea, #0b5b52 72%);
        }

        /* ==============================================================
           THE WEEK. A real table: seven day columns, one row per program.
           ============================================================== */
        .es-gather-th {
            color: #0b5b52;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }
        .dark .es-gather-th { color: #5fdcc9; }
        .es-gather-mark {
            display: block;
            height: 1.15rem;
            width: 1.15rem;
            margin: 0 auto;
            border-radius: 4px;
            background-color: var(--strand, #0b5b52);
        }
        .dark .es-gather-mark { background-color: var(--strand-dark, #5fdcc9); }
        .es-gather-mark-off {
            display: block;
            height: 1.15rem;
            width: 1.15rem;
            margin: 0 auto;
            border-radius: 4px;
            background-color: rgba(17, 25, 23, 0.07);
        }
        .dark .es-gather-mark-off { background-color: rgba(232, 237, 236, 0.07); }
        .es-gather-dot {
            display: inline-block;
            height: 0.45rem;
            width: 0.45rem;
            border-radius: 999px;
            background-color: var(--strand, #0b5b52);
        }
        .dark .es-gather-dot { background-color: var(--strand-dark, #5fdcc9); }
        /* The places bar reuses the table's mark colours, but it must be free
           to shrink: .es-gather-mark carries a fixed 1.15rem width, and a flex
           item's default min-width:auto floors at that, so sixteen of them
           overflowed the panel at 390px. Reset the box here rather than
           loosening the mark itself, which the table depends on. */
        .es-gather-bar { display: flex; gap: 3px; }
        .es-gather-bar > span {
            flex: 1 1 0;
            width: auto;
            min-width: 0;
            height: 0.5rem;
            margin: 0;
            border-radius: 2px;
        }
        .es-gather-tr { border-top: 1px solid rgba(17, 25, 23, 0.09); }
        .dark .es-gather-tr { border-top-color: rgba(232, 237, 236, 0.09); }

        /* ==============================================================
           THE DOORS. Always-dark band, identical in both colour modes.
           A resolvable background-color sits under the gradient so text
           over it is scored against something real.
           ============================================================== */
        .es-gather-band {
            background-color: #0f1615;
            background-image:
                radial-gradient(ellipse 70% 55% at 50% 0%, rgba(11, 91, 82, 0.5), rgba(11, 91, 82, 0) 72%),
                linear-gradient(180deg, #14201e, #0f1615);
        }
        .es-gather-door {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.75rem;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }
        .es-gather-door:hover { border-color: rgba(95, 220, 201, 0.4); background-color: rgba(255, 255, 255, 0.075); }

        /* Nothing inside the band may change between colour modes. These
           three shared classes carry their own .dark rules in
           marketing.css and are invisible to a grep of this file. Keep
           them AFTER the .dark rules above so they win in both modes. */
        .es-gather-band .grid-overlay {
            background-image:
                linear-gradient(rgba(232, 237, 236, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(232, 237, 236, 0.05) 1px, transparent 1px);
        }
        .es-gather-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-gather-band .es-claim:focus-within {
            border-color: rgba(95, 220, 201, 0.75);
            box-shadow: 0 0 0 4px rgba(95, 220, 201, 0.22);
        }
        .es-gather-band .es-gather-tag { color: #5fdcc9; }
        .es-gather-band .es-gather-grad { background-image: linear-gradient(102deg, #8de6d2, #5fdcc9); }
        .es-gather-band .es-gather-plan-free { border-color: rgba(232, 237, 236, 0.26); color: #a0acaa; }

        /* Shared dot navigation is hard-coded brand blue. */
        .es-dot:hover .es-dot-pip { background-color: rgba(11, 91, 82, 0.65); }
        .es-dot.is-active .es-dot-pip { background: #0b5b52; }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(95, 220, 201, 0.65); }
        .dark .es-dot.is-active .es-dot-pip { background: #5fdcc9; }

        /* Focus rings. Never set a radius here: an outline follows the
           element's own radius already. */
        #es-gather-page a:focus-visible,
        #es-gather-page summary:focus-visible,
        #es-gather-page button:focus-visible,
        #es-gather-page input:focus-visible {
            outline: 2px solid #0b5b52;
            outline-offset: 2px;
        }
        .dark #es-gather-page a:focus-visible,
        .dark #es-gather-page summary:focus-visible,
        .dark #es-gather-page button:focus-visible,
        .dark #es-gather-page input:focus-visible {
            outline-color: #5fdcc9;
        }
        .es-gather-band a:focus-visible,
        .es-gather-band summary:focus-visible,
        .es-gather-band button:focus-visible,
        .es-gather-band input:focus-visible {
            outline-color: #5fdcc9 !important;
        }

        /* Section separators, the timetable's scroll floor and the dot-nav
           tooltip live here rather than as arbitrary Tailwind values, so the
           page paints correctly without depending on a CSS rebuild. */
        .es-gather-edge { border-top: 1px solid rgba(17, 25, 23, 0.1); }
        .dark .es-gather-edge { border-top-color: rgba(232, 237, 236, 0.11); }
        .es-gather-table { min-width: 34rem; }
        .es-gather-tip {
            background-color: #ffffff;
            border: 1px solid rgba(17, 25, 23, 0.13);
            color: #111917;
        }
        .dark .es-gather-tip {
            background-color: #161d1c;
            border-color: rgba(232, 237, 236, 0.14);
            color: #e8edec;
        }

        /* The newest card on the board lifts a little, as paper on cork
           does. Gated below. */
        .es-gather-sway { animation: es-gather-sway 9s ease-in-out infinite; transform-origin: 50% 6px; }
        @keyframes es-gather-sway {
            0%, 100% { transform: rotate(-2deg); }
            50% { transform: rotate(1.2deg); }
        }

        @media (prefers-reduced-motion: reduce) {
            .es-gather-sway { animation: none !important; transform: rotate(-2deg); }
            .es-gather-btn:hover { transform: none; }
        }
    </style>

    @php
        // ---------------------------------------------------------------
        // The week as it hangs on the board. Each program is ONE event
        // with the days it repeats on (day-of-week recurrence), and a
        // strand is a sub-schedule, which colour-codes and groups only.
        // The dot colours are inline custom properties rather than
        // interpolated Tailwind classes, which the JIT cannot generate.
        // ---------------------------------------------------------------
        // Four strands need four hues a person can actually tell apart at
        // 1.15rem, otherwise the colour-coding argument does not land. The
        // first draft had Youth #0a4f63 next to Sport #2b4a63, which read as
        // one navy in both modes. These are teal / blue / terracotta / moss,
        // spread around the wheel and steering clear of the forbidden
        // purple-violet-indigo-fuchsia-pink band. They are decorative marks
        // with an sr-only "Runs Monday" behind them, so no text sits on them.
        $strands = [
            'Wellbeing' => ['#0b5b52', '#5fdcc9'],
            'Youth'     => ['#14548c', '#86c5f0'],
            'Learning'  => ['#95350f', '#f0a374'],
            'Sport'     => ['#4a5a12', '#c2d47a'],
        ];

        $dayNames = ['Mon' => 'Monday', 'Tue' => 'Tuesday', 'Wed' => 'Wednesday', 'Thu' => 'Thursday', 'Fri' => 'Friday', 'Sat' => 'Saturday', 'Sun' => 'Sunday'];

        // [program, strand, days it runs, sign-up]
        $week = [
            ['Senior fitness',   'Wellbeing', ['Mon', 'Wed', 'Fri'], 'RSVP, 24 places'],
            ['Youth basketball', 'Youth',     ['Tue', 'Thu'],        'RSVP, 30 places'],
            ['Toddler group',    'Wellbeing', ['Wed'],               'RSVP, 16 places'],
            ['Pottery class',    'Learning',  ['Wed'],               'Ticketed, $45'],
            ['Community lunch',  'Wellbeing', ['Thu'],               'Just turn up'],
            ['Film night',       'Learning',  ['Fri'],               'RSVP, 60 places'],
            ['Open gym',         'Sport',     ['Sat', 'Sun'],        'Just turn up'],
        ];

        // The cards actually pinned to the board in the hero. The second
        // line is the time and the sign-up, which are the fields an event
        // really has: there is no room or space field anywhere in the app,
        // so nothing on a card here pretends there is one.
        $pinned = [
            ['Mon', 'Senior fitness', '9:30am, 24 places', -3.2],
            ['Tue', 'Youth basketball', '6:00pm, 30 places', 2.4],
            ['Wed', 'Toddler group', '10:00am, 16 places', -1.6],
            ['Thu', 'Community lunch', '12:30pm, just turn up', 2.8],
            ['Sat', 'Open gym', '8:00am, just turn up', -2.2],
        ];

        // The six ways out of the one board. Every row is free.
        $doors = [
            [
                'Its own link',
                'The center gets a page at its own address, with every program and every date on it, readable on a phone. That link is the thing you print, text and put in the newsletter.',
                'M13.828 10.172a4 4 0 015.656 5.656l-3 3a4 4 0 01-5.656 0M10.172 13.828a4 4 0 01-5.656-5.656l3-3a4 4 0 015.656 0',
            ],
            [
                'Embedded on the town website',
                'One snippet drops the same calendar into the site the city already runs. Nobody is keeping two lists in step, because there is only one list.',
                'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4',
            ],
            [
                'On the calendar they already check',
                'Two-way sync with Google, Outlook and CalDAV puts an event on the calendar you already keep, one entry each, so a weekly program lands once. The iCal feed anyone can subscribe to is the one that unrolls every date.',
                'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
            ],
            [
                'In their inbox, when you say so',
                'People follow the center and you write to them. You compose it and you send it, so nothing goes out behind your back. Ten emails a month free, counted per recipient, 100 on Pro and 1,000 on Enterprise.',
                'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
            ],
            [
                'Printed on the sheet itself',
                'Your schedule has a QR code you can download and print. The laminated sheet on the actual board becomes a way into the online one, which is the whole trick.',
                'M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z',
            ],
            [
                'And back onto the board',
                'Members can add photos and comments to a program, and every submission waits in an approval queue before anyone sees it. Twenty-five photos on the free plan, and Pro lifts the cap. The board fills itself back up.',
                'M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z',
            ],
        ];

        $faqs = [
            [
                'q' => 'Is Event Schedule free for community centers?',
                'a' => 'Yes, and most of what a center needs is on the free plan: the public program calendar and its own link, recurring programs with date exceptions, sub-schedules, free RSVP sign-up with an optional capacity, the embeddable calendar, two-way Google, Outlook and CalDAV sync, iCal downloads, the downloadable QR code, built-in analytics, member photos and comments with an approval queue (25 photos on the free plan), and 10 newsletter emails a month. Newsletter allowances count each recipient as one email, so ten emails means ten people; Pro raises it to 100 a month and Enterprise to 1,000. Even selling a paid class is free, up to 25 paid tickets a month per schedule, and Event Schedule charges zero platform fees on the sale whatever plan you are on. Pro at '.plan_price($proMonthly).' a month lifts that ceiling.',
            ],
            [
                'q' => 'Can I organize classes, meetings, and events by category?',
                'a' => 'Yes. Sub-schedules group and color-code the strands, so wellbeing, youth, learning and sport read apart at a glance, and each strand has its own shareable link, which means you can send a family a link that shows only the youth programs. Being straight about one thing: a sub-schedule has no visibility setting of its own and cannot hide anything. To keep a program off the public calendar until you are ready, save it as a Draft.',
            ],
            [
                'q' => 'How do community members stay informed about programs?',
                'a' => 'Through as many doors as you care to open, and all of them are yours to trigger. People follow the center and you write to them when there is something to say - you compose the newsletter and press send, nothing is emailed automatically. The calendar embeds into the website you already have. It syncs both ways with Google, Outlook and CalDAV, one entry per event, and the schedule also publishes an iCal feed that unrolls every date of a weekly program for anyone who subscribes to it. And your schedule has a QR code you can download and print for the board in the lobby.',
            ],
            [
                'q' => 'Can we handle event registration and payments?',
                'a' => 'Yes. Free sign-up with an optional capacity is on the free plan, and the capacity is counted per date, so a full Monday session does not stop the following Monday filling up. For a paid class, connect your own Stripe account: the money goes to you, Event Schedule takes no cut, and every ticket carries a QR code you can scan at the door on any plan. The free plan sells 25 paid tickets a month per schedule, and free sign-ups are never counted against that. Pro lifts the ceiling and adds the extras: asking your own questions at checkout, and selling one pass that covers a whole term of a class.',
            ],
            [
                'q' => 'Can outside groups request the hall?',
                'a' => 'Yes. Turn on requests and a group can ask for a date through your page, either with a short booking form or by pasting a listing for us to read. Keep approval on and nothing appears publicly until you accept it, and you are emailed when requests are waiting. It is a request inbox rather than a room-booking system: there is no per-room availability and nothing warns you that two bookings overlap, so you are still the person who checks.',
            ],
        ];

        $dotSections = [
            ['top', 'The board'],
            ['week', 'The week'],
            ['doors', 'The doors'],
            ['hall', 'Hall hire'],
            ['signup', 'Sign-ups'],
            ['who', 'Who it is for'],
            ['how', 'How it works'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];
    @endphp

    <div id="es-gather-page" class="es-gather-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the board in the lobby                              -->
    <!-- ============================================================ -->
    {{-- The nav overlays the top of the page, so the hero carries extra
         top padding rather than letting the board sit under it. --}}
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden pb-16 pt-28">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 28% 28%, rgba(11, 91, 82, 0.26), rgba(11, 91, 82, 0) 62%); opacity: 0.55;"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 74% 60%, rgba(95, 220, 201, 0.18), rgba(95, 220, 201, 0) 62%); opacity: 0.5;"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 55% 82%, rgba(149, 53, 15, 0.12), rgba(149, 53, 15, 0) 60%); opacity: 0.5;"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                <div>
                    <p class="es-gather-tag es-fade-up es-d-1 mb-5">For community centers and recreation facilities</p>

                    <h1 class="es-balance mb-7 text-[2.6rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">The board reaches</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">whoever <span class="es-gather-grad">walks past it</span>.</span></span>
                    </h1>

                    <p class="es-gather-muted es-fade-up es-d-2 mb-9 max-w-xl text-lg sm:text-xl">
                        Your week already runs like clockwork. It is the timetable that never leaves the
                        lobby. Put the same board on a link, in an inbox and on a phone, and the people
                        who could not get through the door still see what you run.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col gap-3 sm:flex-row">
                        <a href="#doors" class="es-gather-ghost inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            See the six doors
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="es-gather-btn inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            Put your board online
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    </div>
                </div>

                <!-- The board. Fixed object: identical with .dark on and off. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-gather-board p-5 sm:p-6">
                        <div class="grid grid-cols-2 gap-3" aria-hidden="true">
                            @foreach ($pinned as $pi => [$pDay, $pName, $pWhere, $pRot])
                                <div class="es-gather-slip es-gather-ruled relative px-3 pb-3 pt-5" style="transform: rotate({{ $pRot }}deg);">
                                    <span class="es-gather-pin {{ $pi % 2 === 0 ? 'es-gather-pin-teal' : '' }} absolute left-1/2 top-1.5 -translate-x-1/2"></span>
                                    <span class="es-gather-slip-day mb-1">{{ $pDay }}</span>
                                    <span class="es-gather-slip-title block text-sm leading-tight">{{ $pName }}</span>
                                    <span class="es-gather-slip-sub mt-0.5 block">{{ $pWhere }}</span>
                                </div>
                            @endforeach

                            <div class="es-gather-slip es-gather-sway relative flex flex-col justify-center px-3 pb-3 pt-5">
                                <span class="es-gather-pin absolute left-1/2 top-1.5 -translate-x-1/2"></span>
                                <span class="es-gather-slip-day mb-1">And the newest card</span>
                                <span class="es-gather-slip-url block">riverside.eventschedule.com</span>
                            </div>
                        </div>
                    </div>

                    <p class="es-gather-muted mt-5 text-xs">
                        Six cards, one link. The board does not change. What changes is how many ways there are to read it.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The week (01) - a real table                              -->
    <!-- ============================================================ -->
    <section id="week" class="scroll-mt-24 es-gather-edge py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-gather-corner mb-6" data-reveal aria-hidden="true"><span>01</span></div>
                <p class="es-gather-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The week</p>
                <h2 class="es-balance es-gather-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Set it once. The week <span class="es-gather-grad">draws itself</span>.
                </h2>
                <p class="es-gather-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Every program that repeats is one entry with the days it runs on, not fifty-two
                    copies of the same class. Add the weeks it skips as date exceptions and those
                    dates come off the calendar, so nobody walks up to a hall that is shut.
                </p>
            </div>

            <div class="es-gather-card p-5 sm:p-7" data-reveal="panel">
                <div class="overflow-x-auto">
                    <table class="es-gather-table w-full border-collapse text-left">
                        <caption class="sr-only">A community center week: each recurring program, the days it runs, and how people sign up</caption>
                        <thead>
                            <tr>
                                <th scope="col" class="es-gather-th pb-3 pe-3">Program</th>
                                @foreach ($dayNames as $dShort => $dLong)
                                    <th scope="col" class="es-gather-th pb-3 text-center">{{ $dShort }}</th>
                                @endforeach
                                <th scope="col" class="es-gather-th hidden pb-3 ps-4 sm:table-cell">Sign-up</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($week as [$wName, $wStrand, $wDays, $wSignup])
                                <tr class="es-gather-tr">
                                    <th scope="row" class="py-3 pe-3 align-middle">
                                        <span class="es-gather-ink block text-sm font-bold">{{ $wName }}</span>
                                        <span class="es-gather-muted mt-0.5 flex items-center gap-1.5 text-[0.65rem] font-normal">
                                            <span class="es-gather-dot" style="--strand: {{ $strands[$wStrand][0] }}; --strand-dark: {{ $strands[$wStrand][1] }};" aria-hidden="true"></span>
                                            {{ $wStrand }}
                                        </span>
                                    </th>
                                    @foreach ($dayNames as $dShort => $dLong)
                                        <td class="px-1 py-3 align-middle">
                                            @if (in_array($dShort, $wDays))
                                                <span class="sr-only">Runs {{ $dLong }}</span>
                                                <span class="es-gather-mark" style="--strand: {{ $strands[$wStrand][0] }}; --strand-dark: {{ $strands[$wStrand][1] }};" aria-hidden="true"></span>
                                            @else
                                                <span class="es-gather-mark-off" aria-hidden="true"></span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="es-gather-muted es-gather-num hidden py-3 ps-4 align-middle text-xs sm:table-cell">{{ $wSignup }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="es-gather-rule my-5" aria-hidden="true"></div>

                <div class="grid gap-4 sm:grid-cols-3">
                    @foreach ([
                        ['One entry, every week', 'Pick the days and it repeats on them. Editing the class edits every date of it.'],
                        ['The weeks it does not run', 'A date exception takes a single date out. Guests do not see a crossed-out line, they see the day simply absent.'],
                        ['A strand is a sub-schedule', 'It groups and color-codes, and it has a link of its own that shows only those programs.'],
                    ] as [$nTitle, $nBody])
                        <div class="es-gather-sub p-4">
                            <p class="es-gather-ink text-sm font-bold">{{ $nTitle }}</p>
                            <p class="es-gather-muted mt-1 text-xs">{{ $nBody }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="es-gather-flag mt-8 p-5" data-reveal>
                <p class="es-gather-warm text-sm font-bold uppercase tracking-wider">Being straight with you</p>
                <p class="es-gather-muted mt-1 text-sm">
                    A sub-schedule cannot hide anything. It has a name, a slug and a color, and that is
                    all, so it is not a permission or a room. When you want a program off the public
                    calendar until you are ready, that is what Draft is for.
                </p>
            </div>

            <p class="mt-8 text-center" data-reveal>
                <span class="es-gather-plan es-gather-plan-free">Free</span>
                <span class="es-gather-muted ms-2 text-sm">Recurring programs, date exceptions and sub-schedules cost nothing.</span>
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The doors (02) - fixed dark band                          -->
    <!-- ============================================================ -->
    <section id="doors" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-gather-band noise relative overflow-hidden rounded-[2rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-6xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="es-gather-corner mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                    <p class="es-gather-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The doors</p>
                    <h2 class="es-balance mb-5 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        One board, <span class="es-gather-grad">six doors out</span>.
                    </h2>
                    <p class="text-lg text-gray-300" data-reveal style="--reveal-delay: 0.15s;">
                        The cork stays where it is. Everything below is the same week, leaving the
                        building by a different route, and every one of them is on the free plan.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="90">
                    @foreach ($doors as $dIndex => [$dTitle, $dBody, $dIcon])
                        <div class="es-gather-door flex h-full flex-col p-6" data-reveal="panel">
                            <div class="mb-4 flex items-center gap-3">
                                <span class="es-gather-lit es-gather-num text-xs font-bold">{{ str_pad($dIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                <svg aria-hidden="true" class="es-gather-lit h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $dIcon }}" /></svg>
                            </div>
                            <h3 class="mb-2 text-lg font-bold text-white">{{ $dTitle }}</h3>
                            <p class="text-sm text-gray-400">{{ $dBody }}</p>
                            <p class="mt-auto pt-4">
                                <span class="es-gather-plan es-gather-plan-free">Free</span>
                            </p>
                        </div>
                    @endforeach
                </div>

                <p class="mx-auto mt-10 max-w-2xl text-center text-sm text-gray-400" data-reveal>
                    Worth saying plainly, because software often implies otherwise: followers are never
                    emailed automatically. A newsletter goes out when you write one and send it.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Hall hire (03)                                            -->
    <!-- ============================================================ -->
    <section id="hall" class="scroll-mt-24 es-gather-edge py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-16">
                <div class="order-2 lg:order-1">
                    <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                        <div class="es-tilt-inner es-gather-card overflow-hidden p-6 sm:p-7">
                            <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                                <h3 class="es-gather-ink text-lg font-bold">Requests waiting</h3>
                                <span class="es-gather-plan es-gather-plan-free">Free</span>
                            </div>

                            <div aria-hidden="true">
                                <p class="es-gather-tag mb-2">Pinned up</p>
                                <div class="es-gather-board es-gather-strip p-3">
                                    <div class="es-gather-slip es-gather-ruled relative px-4 pb-4 pt-6">
                                        <span class="es-gather-pin es-gather-pin-teal absolute left-1/2 top-1.5 -translate-x-1/2"></span>
                                        <span class="es-gather-slip-day mb-1">Accepted</span>
                                        <span class="es-gather-slip-title block text-sm">Lincoln PTA quiz night</span>
                                        <span class="es-gather-slip-sub mt-1 block">Tue 15 Oct, 7:00pm to 9:00pm</span>
                                    </div>
                                </div>

                                <p class="es-gather-tag mb-2 mt-6">Still in the tray</p>
                                <div class="es-gather-tray space-y-2 p-3">
                                    <div class="es-gather-slip p-4">
                                        <span class="es-gather-slip-day mb-1">Pending</span>
                                        <span class="es-gather-slip-title block text-sm">Scout Troop 42 meeting</span>
                                        <span class="es-gather-slip-sub mt-1 block">Wed 23 Oct, 6:30pm to 8:00pm</span>
                                    </div>
                                    <div class="es-gather-slip p-4">
                                        <span class="es-gather-slip-day mb-1">Pending</span>
                                        <span class="es-gather-slip-title block text-sm">Allotment Society AGM</span>
                                        <span class="es-gather-slip-sub mt-1 block">Sat 26 Oct, 10:00am to noon</span>
                                    </div>
                                </div>
                            </div>

                            <p class="es-gather-muted es-gather-edge mt-5 pt-4 text-xs">
                                The pin is the public calendar. Accepting a request is what moves a slip out
                                of the tray and onto the board, and nothing else does.
                            </p>

                            <div class="es-glare" aria-hidden="true"></div>
                            <div class="es-ring-glow" aria-hidden="true"></div>
                        </div>
                    </div>
                </div>

                <div class="order-1 lg:order-2">
                    <div class="es-gather-corner mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                    <p class="es-gather-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Hall hire</p>
                    <h2 class="es-balance es-gather-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        The slip waits for <span class="es-gather-grad">a pushpin</span>.
                    </h2>
                    <p class="es-gather-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        Turn on requests and an outside group asks for a date through your page instead
                        of leaving a note at reception that somebody has to type up. Nothing appears
                        publicly until you accept it.
                    </p>

                    <ul class="space-y-4" data-reveal-group="80">
                        @foreach ([
                            ['Nothing posts without you', 'Keep approval on and every request sits pending. The public calendar only ever shows what you agreed to.'],
                            ['A short form or a pasted listing', 'Ask for the details on a booking form, or let people paste what they already wrote and have it read for you.'],
                            ['Your conditions on the form', 'The terms a hirer has to agree to go on the request itself, so they are answered before the conversation starts.'],
                            ['Groups you already trust', 'Name the schedules whose requests you want posted without review, and leave everyone else pending.'],
                            ['You are told, not the followers', 'A scheduled check emails you when requests are waiting. Nobody on your follower list hears about it.'],
                        ] as [$hTitle, $hBody])
                            <li class="flex items-start gap-3" data-reveal>
                                <svg aria-hidden="true" class="es-gather-accent mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span><span class="es-gather-ink font-semibold">{{ $hTitle }}</span> <span class="es-gather-muted">- {{ $hBody }}</span></span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="es-gather-flag mt-8 p-5" data-reveal>
                        <p class="es-gather-warm text-sm font-bold uppercase tracking-wider">What this is not</p>
                        <p class="es-gather-muted mt-1 text-sm">
                            It is a request inbox, not a room-booking system. There is no per-room
                            availability grid, and nothing will warn you that two bookings overlap. You
                            are still the person who checks, which is worth knowing before you rely on it.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Sign-ups (04)                                             -->
    <!-- ============================================================ -->
    <section id="signup" class="scroll-mt-24 es-gather-edge py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-gather-corner mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                <p class="es-gather-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Sign-ups</p>
                <h2 class="es-balance es-gather-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Sixteen places, <span class="es-gather-grad">counted per date</span>.
                </h2>
                <p class="es-gather-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    A toddler group that fits sixteen fits sixteen. You set that number once on the
                    program, and it is counted separately for every date it runs, so a full Wednesday
                    leaves the following Wednesday alone.
                </p>
            </div>

            <div class="grid gap-6 lg:grid-cols-2" data-reveal-group="110">
                <div class="es-gather-card es-gather-hover flex h-full flex-col p-7" data-reveal="panel">
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <h3 class="es-gather-ink text-xl font-bold">The free class</h3>
                        <span class="es-gather-plan es-gather-plan-free">Free</span>
                    </div>
                    <p class="es-gather-muted mb-6">
                        Turn on sign-up, give the program a capacity, and people put their name down
                        from your page. No card, no checkout, no plan.
                    </p>

                    <div class="es-gather-sub mb-6 p-5" aria-hidden="true">
                        <p class="es-gather-tag mb-3">Toddler group, Wednesday</p>
                        <div class="flex items-baseline gap-2">
                            <span class="es-gather-ink es-gather-num text-4xl font-black leading-none">11</span>
                            <span class="es-gather-muted es-gather-num text-sm font-bold">of 16 places taken</span>
                        </div>
                        <div class="es-gather-bar mt-4">
                            @for ($s = 0; $s < 16; $s++)
                                <span class="{{ $s < 11 ? 'es-gather-mark' : 'es-gather-mark-off' }}"></span>
                            @endfor
                        </div>
                        <p class="es-gather-muted mt-3 text-xs">Next Wednesday starts again at nought of sixteen.</p>
                    </div>

                    <ul class="mt-auto space-y-2">
                        @foreach ([
                            'The capacity is optional. Leave it off and sign-up just stays open.',
                            'A full date stops taking names by itself.',
                            'Attendees can download the date to their own calendar.',
                        ] as $fItem)
                            <li class="es-gather-muted flex items-start gap-2 text-sm">
                                <svg aria-hidden="true" class="es-gather-accent mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                {{ $fItem }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="es-gather-card es-gather-hover flex h-full flex-col p-7" data-reveal="panel">
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <h3 class="es-gather-ink text-xl font-bold">The paid class</h3>
                        <span class="es-gather-plan es-gather-plan-free">Free to 25 a month</span>
                    </div>
                    <p class="es-gather-muted mb-6">
                        Pottery costs money to run, so it costs money to join. Connect your own Stripe
                        account and the money lands in it. Event Schedule charges zero platform fees, so
                        past what Stripe takes for processing, the fee is yours. The free plan sells 25
                        paid tickets a month per schedule, and Pro lifts the ceiling.
                    </p>

                    <div class="es-gather-sub mb-6 p-5" aria-hidden="true">
                        <p class="es-gather-tag mb-3">Pottery class, Wednesday</p>
                        <div class="space-y-2">
                            @foreach ([['Single session', '$45'], ['Term pass, one purchase', '$180'], ['Concession', '$28']] as [$tName, $tPrice])
                                <div class="flex items-baseline gap-3 text-sm">
                                    <span class="es-gather-ink min-w-0 flex-1 truncate font-semibold">{{ $tName }}</span>
                                    <span class="es-gather-ink es-gather-num">{{ $tPrice }}</span>
                                </div>
                            @endforeach
                        </div>
                        <p class="es-gather-muted mt-3 text-xs">A pass is one purchase valid across every date of the class, once each. Passes are a Pro option.</p>
                    </div>

                    <ul class="mt-auto space-y-2">
                        @foreach ([
                            'Every ticket carries a QR code you scan at the door, on any plan.',
                            'Passes, discount codes and a waitlist when a class fills are Pro.',
                            'So are your own questions at checkout and a CSV of the whole list.',
                        ] as $pItem)
                            <li class="es-gather-muted flex items-start gap-2 text-sm">
                                <svg aria-hidden="true" class="es-gather-accent mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                {{ $pItem }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <p class="es-gather-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                <span class="es-gather-plan es-gather-plan-pro">Pro</span>
                <span class="ms-2">At {{ plan_price($proMonthly) }} a month Pro lifts the 25-a-month ceiling on paid tickets and adds
                passes, discount codes, the waitlist and the sales CSV. Free sign-up with a capacity is
                never counted against that number, and for a lot of centers that is the whole requirement.</span>
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Who it is for (05)                                        -->
    <!-- ============================================================ -->
    <section id="who" class="scroll-mt-24 es-gather-edge py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-gather-corner mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                <p class="es-gather-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Who it is for</p>
                <h2 class="es-balance es-gather-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Anywhere with a board <span class="es-gather-grad">by the door</span>.
                </h2>
                <p class="es-gather-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    From recreation facilities to neighborhood gathering spaces, every kind of community center runs on a board like this one.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <x-sub-audience-card
                    name="Recreation Centers"
                    description="Sports leagues, fitness classes, camps, and recreational programs. Keep your community active and engaged."
                    icon-color="teal"
                    blog-slug="for-recreation-centers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0zM7.5 4.5c1.5 2 2 4.5 2 7.5s-.5 5.5-2 7.5m9-15c-1.5 2-2 4.5-2 7.5s.5 5.5 2 7.5" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Senior Centers"
                    description="Programs, social events, wellness activities, and meals. Keep seniors connected and supported."
                    icon-color="cyan"
                    blog-slug="for-senior-centers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-cyan-600 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21c-4.5-2.7-7.5-6-7.5-9.5A4.5 4.5 0 0112 8a4.5 4.5 0 017.5 3.5C19.5 15 16.5 18.3 12 21z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Youth Centers"
                    description="After-school programs, summer camps, teen activities. Give young people a safe place to grow."
                    icon-color="emerald"
                    blog-slug="for-youth-centers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0v7m-5-4.5V19a5 5 0 0010 0v-2.5" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Cultural Centers"
                    description="Heritage events, language classes, cultural celebrations. Preserve and share traditions with your community."
                    icon-color="amber"
                    blog-slug="for-cultural-centers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 20h16M6 20V9m4 11V9m4 11V9m4 11V9M3 9l9-5 9 5z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Neighborhood Centers"
                    description="Local meetings, block parties, civic events, and community gatherings. Strengthen local bonds."
                    icon-color="orange"
                    blog-slug="for-neighborhood-centers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-8 9 8M6 10v10h12V10" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Faith-Based Centers"
                    description="Congregation events, community outreach, classes, and fellowship gatherings. Bring people together."
                    icon-color="sky"
                    blog-slug="for-faith-based-centers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 21V8a3 3 0 016 0v13M4 21h16M7 12h10" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. How it works (06)                                         -->
    <!-- ============================================================ -->
    <section id="how" class="scroll-mt-24 es-gather-edge py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-gather-corner mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                <p class="es-gather-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">How it works</p>
                <h2 class="es-balance es-gather-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Put it up, pin it, <span class="es-gather-grad">open the doors</span>.
                </h2>
            </div>

            <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                @foreach ([
                    ['01', 'Put the board up', 'Sign up as a venue schedule and name the center. You have a public page and a link before you have added anything to it.'],
                    ['02', 'Pin the week', 'Add each program once with the days it repeats on, group the strands into sub-schedules, and knock out the weeks it skips.'],
                    ['03', 'Open the doors', 'Embed the calendar in the site you already have, sync it, print the QR code for the lobby, and write to the people who follow.'],
                ] as [$sNum, $sTitle, $sBody])
                    <div class="es-gather-card es-gather-hover p-7" data-reveal="panel">
                        <p class="es-gather-accent es-gather-num mb-3 text-sm font-bold">{{ $sNum }}</p>
                        <h3 class="es-gather-ink mb-2 text-lg font-bold">{{ $sTitle }}</h3>
                        <p class="es-gather-muted text-sm">{{ $sBody }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Key features                                              -->
    <!-- ============================================================ -->
    <section class="es-gather-edge py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-gather-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="One entry with the days it repeats on, and exceptions for the weeks it skips" :url="marketing_url('/features/recurring-events')" icon-color="teal">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Sub-schedules" description="Group and color-code the strands, each with its own shareable link" :url="marketing_url('/features/sub-schedules')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Embed Calendar" description="Add your program calendar to the website you already have" :url="marketing_url('/features/embed-calendar')" icon-color="blue">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Online Events" description="One link people join on, for a virtual town hall or a class from home" :url="marketing_url('/features/online-events')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Event Graphics" description="Generate a shareable image for a program to post or print (Pro)" :url="marketing_url('/features/event-graphics')" icon-color="amber">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Write to the people who follow the center, ten emails a month free" :url="marketing_url('/features/newsletters')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-gather-accent inline-flex items-center font-medium hover:underline">
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
    <section class="es-gather-edge py-16">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-gather-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([['/for-libraries', 'Libraries'], ['/for-theaters', 'Theaters'], ['/for-workshop-instructors', 'Workshop Instructors'], ['/for-fitness-and-yoga', 'Fitness & Yoga']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="es-gather-card es-gather-hover group flex items-center justify-between p-5">
                        <div>
                            <div class="es-gather-muted text-sm">Event Schedule for</div>
                            <div class="es-gather-ink text-lg font-semibold">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="es-gather-accent h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-gather-accent inline-flex items-center font-medium hover:underline">
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
    <!-- 10. FAQ (07)                                                 -->
    <!-- ============================================================ -->
    <section id="faq" class="scroll-mt-24 es-gather-edge py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-gather-corner mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                <p class="es-gather-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Questions</p>
                <h2 class="es-balance es-gather-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Asked at <span class="es-gather-grad">the front desk</span>.
                </h2>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ($faqs as $faq)
                    <details name="faq" data-reveal class="es-gather-card group/faq overflow-hidden">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-6">
                            <h3 class="es-gather-ink text-lg font-semibold">{{ $faq['q'] }}</h3>
                            <svg aria-hidden="true" class="es-gather-muted h-5 w-5 shrink-0 transition-transform duration-300 group-open/faq:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="es-gather-muted faq-answer px-6 pb-6">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <x-seo.faq-schema :items="$faqs" />

    <!-- ============================================================ -->
    <!-- 11. Finale: the newest card on the board                     -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-gather-band noise relative overflow-hidden rounded-[2rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-25"></div>
                </div>

                <div class="relative z-10">
                    <div class="es-gather-board es-gather-strip mx-auto mb-9 w-full max-w-xs p-4" data-reveal>
                        <div class="es-gather-slip es-gather-ruled es-gather-sway relative px-3 pb-3 pt-5 text-left" aria-hidden="true">
                            <span class="es-gather-pin absolute left-1/2 top-1.5 -translate-x-1/2"></span>
                            <span class="es-gather-slip-day mb-1">Pin this one up</span>
                            <span class="es-gather-slip-url block">your-center.eventschedule.com</span>
                        </div>
                    </div>

                    <p class="es-gather-tag mb-6">Free to start</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                        Put the board where <span class="es-gather-grad">people can find it</span>.
                    </h2>
                    <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                        Everything the center already runs, on a link you can print, embed, sync and
                        email. Free forever, and no cut of anything you sell.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-lg border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-center" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-400 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="es-gather-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg px-8 py-4 text-lg font-semibold">
                            <span class="relative z-10 flex items-center gap-2">
                                Get Started Free
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
                        <span class="es-gather-tip pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
