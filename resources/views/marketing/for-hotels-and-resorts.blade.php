<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Hotels & Resorts | Guest Activities</x-slot>
    <x-slot name="description">Elevate the guest experience. Share your activity calendar, sell tickets to special events, and keep guests engaged. Zero platform fees. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Hotels & Resorts</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Hotels & Resorts",
        "description": "Put the week of guest activities on a page with your property's name on it, print the link on the key-card sleeve, and let guests read the card without asking the desk.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Hotels & Resorts"
        }
    }
    </script>
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Hotels & Resorts",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Hotel and Resort Activity Management Software",
        "operatingSystem": "Web",
        "description": "One page for every guest activity at your property: the standing week entered once as recurring activities, a printable QR code and link, free sign-ups with a capacity, and zero-fee ticketing for the paid experiences.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Standing activities entered once, repeating on chosen days of the week",
            "Date exceptions for the weeks an activity does not run",
            "A printable QR code and a short link for the key-card sleeve",
            "Embeddable calendar for the hotel website you already have",
            "Free sign-ups with a capacity, counted separately for each date",
            "Zero-fee ticketing for paid experiences through your own Stripe account",
            "QR check-in at the door",
            "Promo codes for a resident rate",
            "Sub-schedules with a name, a colour and their own link",
            "Draft activities that stay members-only until you publish them",
            "Booking requests that wait for you to accept them",
            "Two-way Google, Outlook and CalDAV calendar sync",
            "Newsletters to the guests who followed the schedule",
            "Built-in analytics per activity"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "hotel activity calendar, resort event schedule, guest activity management, hotel entertainment calendar, free hotel scheduling",
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
           For-hotels-and-resorts "The Concierge" styles.

           CONCEPT: THE CARD IN THE RACK. Every property already has this
           object - the printed card behind the desk that says what is on
           today. It is the right object because it fails in a way the
           product fixes: it is correct for about a day, the desk that
           reads it aloud goes home at eleven, and the only guests who
           get the answer are the ones who queue for it. So the whole
           page is that card, promoted to a page: same object, always
           current, and what you print is only the link to it.

           The metaphor and the feature story are the same sentence:
             - the card = the schedule's own public page,
             - the rack = recurring activities on chosen days of the week
               plus date exceptions, which is what makes a standing week
               one entry instead of fifty-two,
             - the sleeve = the ungated QR code (RoleController::qrCode)
               plus the iframe embed, which is the whole distribution
               story and costs nothing,
             - the book = sign-ups with a capacity counted PER DATE
               (Event::rsvpRemaining) for the free activities, and
               ticketing for the paid ones.

           The devices that carry it: the hero card and the key-card
           sleeve on one shared stock; the brass rack tab that numbers
           every section; a real <table> of the standing programme with
           the days_of_week string rendered as it is stored; twenty-eight
           equal day slots drawn twice, entered and published, so a
           single date exception reads as one gap in a rhythm; the five
           strands filed as five more cards behind five coloured tabs;
           and the drawer/card duplex, which is the same argument as the
           is_accepted visibility gate.

           WHAT THE PAGE REFUSES TO DRAW. No room grid and no per-space
           capacity: a sub-schedule is fillable on name, slug, colour
           only, so it organises and colour-codes and nothing more. No
           overlap or double-booking warning: no such check exists. No
           automatic guest alerts: the only follower-facing mail is a
           newsletter the owner writes and sends.

           NO ARBITRARY TAILWIND VALUES. The marketing bundle is compiled
           ahead of time, so a class like `lg:grid-cols-[1.05fr_0.95fr]`
           or `dark:bg-[#1a1e23]` that no other page already uses simply
           does not exist in the stylesheet and paints nothing - the hero
           silently loses its second column and the dot-nav tooltip
           silently keeps a white background in dark mode. Every layout,
           colour and size this page needs that is not already in the
           bundle is therefore declared here, in this block, by name.

           COLOUR: the page keeps its slate-and-brass family, restated as
           a MATERIAL pair rather than a gradient - stone ground, brass
           rule, card stock. Distinctiveness comes from the material, the
           serif display face and the record-shaped table, not from a new
           hue. Measured (see the report): ink #191b1e 15.55 on the
           #f4f3f0 ground, muted #4f555c 6.79, brass #6d4c14 7.02; in
           dark #f0ece6 15.55 on #12151a, muted #a8afb6 8.25, brass
           #e8c477 10.97. NEVER text-gray-500 on this ground.

           THE CARD STOCK IS A FIXED PHYSICAL OBJECT: .es-conc-stock and
           everything inside it renders identically with .dark on and
           off, because a printed card does not know what the browser
           theme is. It therefore carries no dark: utilities and no
           shared class that flips.
           ============================================================== */

        /* --- Ground and ink ---------------------------------------- */
        .es-conc-page { background-color: #f4f3f0; color: #191b1e; }
        .dark .es-conc-page { background-color: #12151a; color: #f0ece6; }
        .es-conc-ink { color: #191b1e; }
        .dark .es-conc-ink { color: #f0ece6; }
        .es-conc-muted { color: #4f555c; }
        .dark .es-conc-muted { color: #a8afb6; }
        .es-conc-accent { color: #6d4c14; }
        .dark .es-conc-accent { color: #e8c477; }
        /* Always-lit brass, for the two fixed-dark bands. */
        .es-conc-lit { color: #e8c477; }

        /* --- Page furniture that the compiled bundle does not carry ---
               A seam between sections: one hairline, drawn the same way
               everywhere so the page reads as a stack of filed cards. */
        .es-conc-seam { border-top: 1px solid rgba(25, 27, 30, 0.1); }
        .dark .es-conc-seam { border-top-color: rgba(240, 236, 230, 0.1); }

        /* The hero's two columns: the argument on the left, the object on
           the right, and the object is given slightly less room because a
           card in a rack is a small thing. */
        .es-conc-duo { display: grid; align-items: center; gap: 3rem; }
        @media (min-width: 1024px) {
            .es-conc-duo { grid-template-columns: 1.05fr 0.95fr; gap: 4rem; }
        }

        /* Hero texture: not a square grid but close-set vertical hairlines,
           the edges of cards standing in a rack, with every fourth one
           heavier. Masked to a soft ellipse so it never reaches the text. */
        .es-conc-weave {
            background-image:
                linear-gradient(90deg, rgba(25, 27, 30, 0.085) 1px, transparent 1px),
                linear-gradient(90deg, rgba(25, 27, 30, 0.04) 1px, transparent 1px);
            background-size: 148px 100%, 37px 100%;
            -webkit-mask-image: radial-gradient(ellipse 74% 62% at 50% 38%, black 20%, transparent 74%);
            mask-image: radial-gradient(ellipse 74% 62% at 50% 38%, black 20%, transparent 74%);
        }
        .dark .es-conc-weave {
            background-image:
                linear-gradient(90deg, rgba(240, 236, 230, 0.07) 1px, transparent 1px),
                linear-gradient(90deg, rgba(240, 236, 230, 0.03) 1px, transparent 1px);
        }

        /* The full-bleed bands stop widening on very large screens. */
        @media (min-width: 1536px) {
            .es-conc-wide { max-width: 100rem; margin-left: auto; margin-right: auto; }
        }

        /* Type sizes below Tailwind's smallest step. */
        .es-conc-fine { font-size: 0.68rem; }
        .es-conc-finer { font-size: 0.62rem; }
        .es-conc-cap {
            font-size: 0.58rem;
            font-weight: 700;
            letter-spacing: 0.24em;
            text-transform: uppercase;
        }

        /* --- Typography: a serif display face, which is the register a
               property already prints its own card in. -------------- */
        .es-conc-display {
            font-family: ui-serif, Georgia, 'Iowan Old Style', 'Times New Roman', serif;
            font-weight: 600;
            letter-spacing: -0.012em;
            line-height: 1.08;
        }
        .es-conc-eyebrow {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            color: #4f555c;
        }
        .dark .es-conc-eyebrow { color: #a8afb6; }
        .es-conc-band .es-conc-eyebrow { color: #e8c477; }
        .es-conc-num {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, 'Liberation Mono', monospace;
            font-variant-numeric: tabular-nums;
        }

        /* A brass rule under one word of a heading. Drawn as a flat
           stroke, not an illustration, and it never touches the glyphs. */
        .es-conc-mark { color: #6d4c14; position: relative; white-space: nowrap; }
        .dark .es-conc-mark { color: #e8c477; }
        .es-conc-mark::after {
            content: "";
            position: absolute;
            left: 0.02em;
            right: 0.02em;
            bottom: -0.12em;
            height: 2px;
            border-radius: 2px;
            background: rgba(109, 76, 20, 0.5);
        }
        .dark .es-conc-mark::after { background: rgba(232, 196, 119, 0.45); }
        .es-conc-band .es-conc-mark { color: #e8c477; }
        .es-conc-band .es-conc-mark::after { background: rgba(232, 196, 119, 0.45); }

        /* --- Surfaces ---------------------------------------------- */
        .es-conc-card {
            background-color: #fbfaf8;
            border: 1px solid rgba(25, 27, 30, 0.13);
            border-radius: 0.35rem;
        }
        .dark .es-conc-card {
            background-color: #1a1e23;
            border-color: rgba(240, 236, 230, 0.13);
        }
        /* A strand, drawn as one more card standing in the same rack: the
           colour is on the tab edge, which is where a colour goes on a
           filed card. The hex is a free-form value the owner picks, so it
           is set inline per row and nothing about it is hard-coded here. */
        .es-conc-rackrow {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 0.75rem;
            border: 1px solid rgba(25, 27, 30, 0.13);
            border-left-width: 4px;
            border-radius: 0.15rem 0.35rem 0.35rem 0.15rem;
            background-color: #fbfaf8;
        }
        .dark .es-conc-rackrow {
            border-color: rgba(240, 236, 230, 0.13);
            background-color: #1a1e23;
        }
        .es-conc-hover { transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        .es-conc-hover:hover {
            border-color: rgba(109, 76, 20, 0.45);
            box-shadow: 0 12px 30px -20px rgba(25, 27, 30, 0.55);
        }
        .dark .es-conc-hover:hover {
            border-color: rgba(232, 196, 119, 0.4);
            box-shadow: 0 12px 30px -20px rgba(0, 0, 0, 0.85);
        }
        .es-conc-rule { height: 1px; background: rgba(25, 27, 30, 0.12); }
        .dark .es-conc-rule { background: rgba(240, 236, 230, 0.12); }

        /* --- The rack tab: a section numeral on a brass card tab --- */
        .es-conc-tab {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.3rem 0.8rem 0.3rem 0.55rem;
            border-radius: 0.15rem 0.35rem 0.35rem 0.15rem;
            border: 1px solid rgba(25, 27, 30, 0.16);
            border-left: 3px solid #6d4c14;
            background: #fbfaf8;
            color: #191b1e;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.06em;
        }
        .dark .es-conc-tab {
            border-color: rgba(240, 236, 230, 0.18);
            border-left-color: #e8c477;
            background: #1a1e23;
            color: #f0ece6;
        }
        .es-conc-band .es-conc-tab {
            border-color: rgba(240, 236, 230, 0.18);
            border-left-color: #e8c477;
            background: rgba(240, 236, 230, 0.05);
            color: #f0ece6;
        }

        /* --- Plan pills. Tier only, never a state. ----------------- */
        .es-conc-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.2rem;
            border: 1px solid rgba(25, 27, 30, 0.3);
            color: #4f555c;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }
        .dark .es-conc-plan { border-color: rgba(240, 236, 230, 0.32); color: #a8afb6; }
        .es-conc-band .es-conc-plan { border-color: rgba(240, 236, 230, 0.32); color: #a8afb6; }
        .es-conc-plan-pro { border-color: rgba(109, 76, 20, 0.55); color: #6d4c14; background: rgba(109, 76, 20, 0.07); }
        .dark .es-conc-plan-pro { border-color: rgba(232, 196, 119, 0.45); color: #e8c477; background: rgba(232, 196, 119, 0.1); }
        .es-conc-band .es-conc-plan-pro { border-color: rgba(232, 196, 119, 0.45); color: #e8c477; background: rgba(232, 196, 119, 0.1); }

        /* --- The standing card: a real table of a real record ------ */
        .es-conc-table { width: 100%; border-collapse: collapse; text-align: left; }
        .es-conc-table th, .es-conc-table td { padding: 0.7rem 0.6rem; vertical-align: middle; }
        .es-conc-table thead th {
            padding-top: 0;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #4f555c;
            white-space: nowrap;
        }
        .dark .es-conc-table thead th { color: #a8afb6; }
        .es-conc-table tbody tr { border-top: 1px solid rgba(25, 27, 30, 0.1); }
        .dark .es-conc-table tbody tr { border-top-color: rgba(240, 236, 230, 0.1); }
        .es-conc-table th[scope="row"] { font-weight: 700; color: #191b1e; }
        .dark .es-conc-table th[scope="row"] { color: #f0ece6; }

        /* days_of_week is a seven-character string indexed from Sunday,
           so the strip is drawn Sunday first: that is the stored shape. */
        .es-conc-dow { display: flex; gap: 2px; }
        .es-conc-dow span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.15rem;
            height: 1.15rem;
            border-radius: 0.15rem;
            background: rgba(25, 27, 30, 0.06);
            color: #4f555c;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.6rem;
            font-weight: 700;
        }
        .dark .es-conc-dow span { background: rgba(240, 236, 230, 0.08); color: #a8afb6; }
        .es-conc-dow .es-conc-dow-on { background-color: #6d4c14; color: #ffffff; }
        .dark .es-conc-dow .es-conc-dow-on { background-color: #e8c477; color: #191b1e; }

        /* --- Four weeks, twice. The upper strip is what one recurring
               activity holds; the lower strip is what the page publishes
               after a single date exception. The excepted date is drawn
               EMPTY, not struck through and not annotated, because that
               is literally what a guest gets: the day is not offered.
               Proportional by design - twenty-eight equal slots, so the
               one missing session is visible as a gap in a rhythm. --- */
        .es-conc-weeks { display: flex; gap: 0.55rem; }
        .es-conc-wk { flex: 1 1 0; min-width: 0; }
        .es-conc-wk-cells { display: flex; gap: 2px; }
        .es-conc-wk-cells span {
            flex: 1 1 0;
            min-width: 0;
            height: 1.7rem;
            border-radius: 0.15rem;
            background-color: rgba(25, 27, 30, 0.07);
        }
        .dark .es-conc-wk-cells span { background-color: rgba(240, 236, 230, 0.08); }
        .es-conc-wk-cells .es-conc-wk-on { background-color: #6d4c14; }
        .dark .es-conc-wk-cells .es-conc-wk-on { background-color: #e8c477; }
        .es-conc-wk-label {
            margin-top: 0.35rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.58rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            color: #4f555c;
        }
        .dark .es-conc-wk-label { color: #a8afb6; }

        /* How a guest joins: the three honest answers. */
        .es-conc-join {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            white-space: nowrap;
            padding: 0.15rem 0.5rem;
            border-radius: 999px;
            border: 1px solid rgba(25, 27, 30, 0.18);
            color: #4f555c;
            font-size: 0.68rem;
            font-weight: 700;
        }
        .dark .es-conc-join { border-color: rgba(240, 236, 230, 0.2); color: #a8afb6; }
        .es-conc-join-brass { border-color: rgba(109, 76, 20, 0.5); color: #6d4c14; }
        .dark .es-conc-join-brass { border-color: rgba(232, 196, 119, 0.45); color: #e8c477; }

        /* --- Strand dots. A sub-schedule's colour is a free-form hex
               the owner picks, so these are set inline per row. ----- */
        .es-conc-strand { display: inline-flex; align-items: center; gap: 0.4rem; white-space: nowrap; }
        .es-conc-pip { width: 0.5rem; height: 0.5rem; border-radius: 999px; flex: none; }

        /* --- The card stock. FIXED OBJECT: identical in both modes. */
        .es-conc-stock {
            background-color: #f7f4ec;
            background-image: linear-gradient(160deg, #fbf9f3 0%, #f7f4ec 46%, #f1ecdf 100%);
            border: 1px solid rgba(109, 76, 20, 0.28);
            border-radius: 0.3rem;
            box-shadow: 0 26px 50px -28px rgba(25, 27, 30, 0.6), inset 0 1px 0 rgba(255, 255, 255, 0.7);
            color: #22201b;
        }
        .es-conc-stock-ink { color: #22201b; }
        .es-conc-stock-muted { color: #5d574c; }
        .es-conc-stock-brass { color: #6d4c14; }
        .es-conc-stock-rule { height: 2px; background: linear-gradient(90deg, #6d4c14, rgba(109, 76, 20, 0.15)); }
        .es-conc-stock-hair { height: 1px; background: rgba(34, 32, 27, 0.14); }
        .es-conc-stock-row { border-radius: 0.2rem; background: rgba(34, 32, 27, 0.04); }
        .es-conc-stock-pill {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.05rem 0.4rem;
            border-radius: 999px;
            border: 1px solid rgba(34, 32, 27, 0.22);
            color: #5d574c;
            font-size: 0.58rem;
            font-weight: 700;
            letter-spacing: 0.04em;
        }
        .es-conc-stock-pill-brass { border-color: rgba(109, 76, 20, 0.5); color: #6d4c14; }
        /* Punched hole and the notched corner of a real sleeve. */
        .es-conc-stock-slot {
            width: 1.6rem;
            height: 0.32rem;
            border-radius: 999px;
            background: rgba(34, 32, 27, 0.22);
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.35);
        }
        .es-conc-stock-notch { border-top-right-radius: 1.1rem; }

        /* The module block on the sleeve: a printed code, drawn as
           filled squares from a fixed pattern. Decorative and hidden
           from assistive tech. */
        .es-conc-code { display: grid; grid-template-columns: repeat(9, 1fr); gap: 1px; }
        .es-conc-code i { display: block; aspect-ratio: 1; background: rgba(34, 32, 27, 0.1); border-radius: 1px; }
        .es-conc-code i.es-conc-code-on { background: #22201b; }

        /* --- The book: a place kept, drawn proportionally ---------- */
        .es-conc-meter { height: 0.45rem; border-radius: 999px; background: rgba(25, 27, 30, 0.1); overflow: hidden; }
        .dark .es-conc-meter { background: rgba(240, 236, 230, 0.12); }
        .es-conc-meter-fill { height: 100%; border-radius: 999px; background: #6d4c14; }
        .dark .es-conc-meter-fill { background: #e8c477; }

        /* --- Buttons and links ------------------------------------- */
        .es-conc-btn {
            background-color: #6d4c14;
            color: #ffffff;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .es-conc-btn:hover { background-color: #563b0d; transform: translateY(-1px); box-shadow: 0 16px 32px -18px rgba(109, 76, 20, 0.95); }
        .dark .es-conc-btn { background-color: #e8c477; color: #191b1e; }
        .dark .es-conc-btn:hover { background-color: #f2d492; }
        .es-conc-ghost {
            border: 1px solid rgba(25, 27, 30, 0.22);
            color: #191b1e;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }
        .es-conc-ghost:hover { border-color: rgba(109, 76, 20, 0.55); background-color: rgba(109, 76, 20, 0.06); }
        .dark .es-conc-ghost { border-color: rgba(240, 236, 230, 0.24); color: #f0ece6; }
        .dark .es-conc-ghost:hover { border-color: rgba(232, 196, 119, 0.5); background-color: rgba(232, 196, 119, 0.08); }
        .es-conc-link { color: #6d4c14; }
        .es-conc-link:hover { color: #191b1e; }
        .dark .es-conc-link { color: #e8c477; }
        .dark .es-conc-link:hover { color: #f0ece6; }

        /* --- The fixed-dark bands. A resolvable background-color sits
               under the gradient so text is scored against a real
               surface. ---------------------------------------------- */
        .es-conc-band {
            background-color: #12151a;
            background-image:
                radial-gradient(115% 90% at 50% 0%, rgba(109, 76, 20, 0.3), rgba(109, 76, 20, 0) 62%),
                linear-gradient(180deg, #1a1e23, #12151a 62%, #0e1115);
            border-radius: 1.5rem;
        }
        /* A pip inside a band, lit in both colour modes. */
        .es-conc-bullet {
            width: 0.375rem;
            height: 0.375rem;
            border-radius: 999px;
            flex: none;
            background-color: #e8c477;
        }
        /* Shared classes that flip with the colour mode and would
           otherwise render two different bands. */
        .es-conc-band .grid-overlay {
            background-image:
                linear-gradient(rgba(240, 236, 230, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(240, 236, 230, 0.05) 1px, transparent 1px);
        }
        .es-conc-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-conc-band .es-claim:focus-within {
            border-color: rgba(232, 196, 119, 0.75);
            box-shadow: 0 0 0 4px rgba(232, 196, 119, 0.22);
        }
        /* Same for the card stock, which is the same printed object in
           both modes and must not inherit a flipped shared rule. */
        .es-conc-stock .grid-overlay {
            background-image:
                linear-gradient(rgba(34, 32, 27, 0.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(34, 32, 27, 0.06) 1px, transparent 1px);
        }

        /* --- Shared chrome that is hard-coded brand blue -----------
               The dot-nav tooltip is the one piece of shared chrome with
               no page-local hook, and its dark surface has to be named
               here or it stays white under gray-300 text. Measured:
               #374151 on #ffffff is 10.31, #d1d5db on #1a1e23 is 11.37. */
        .es-conc-tip {
            border: 1px solid #e5e7eb;
            background-color: #ffffff;
            color: #374151;
        }
        .dark .es-conc-tip {
            border-color: rgba(255, 255, 255, 0.1);
            background-color: #1a1e23;
            color: #d1d5db;
        }
        .es-conc-page .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(109, 76, 20, 0.12), transparent 60%);
        }
        .dark .es-conc-page .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(232, 196, 119, 0.1), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(109, 76, 20, 0.65); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(232, 196, 119, 0.65); }
        .es-dot.is-active .es-dot-pip { background: #6d4c14; }
        .dark .es-dot.is-active .es-dot-pip { background: #e8c477; }

        /* --- Focus rings. No border-radius here: an outline already
               follows the element's own shape. ---------------------- */
        #es-conc-page a:focus-visible,
        #es-conc-page summary:focus-visible,
        #es-conc-page button:focus-visible,
        #es-conc-page input:focus-visible {
            outline: 2px solid #6d4c14;
            outline-offset: 3px;
        }
        .dark #es-conc-page a:focus-visible,
        .dark #es-conc-page summary:focus-visible,
        .dark #es-conc-page button:focus-visible,
        .dark #es-conc-page input:focus-visible {
            outline-color: #e8c477;
        }
        .es-conc-band a:focus-visible,
        .es-conc-band summary:focus-visible,
        .es-conc-band button:focus-visible,
        .es-conc-band input:focus-visible {
            outline-color: #e8c477 !important;
        }

        /* --- Motion: one drift on the hero card, gated ------------- */
        @keyframes es-conc-settle {
            0%, 100% { transform: translateY(0) rotate(-0.6deg); }
            50% { transform: translateY(-7px) rotate(-0.2deg); }
        }
        .es-conc-settle { animation: es-conc-settle 9s ease-in-out infinite; }
        @media (prefers-reduced-motion: reduce) {
            .es-conc-settle { animation: none; transform: rotate(-0.6deg); }
            .es-conc-btn:hover { transform: none; }
        }
    </style>

    @php
        // ---------------------------------------------------------------
        // The standing card. One row per activity that runs on a pattern.
        // 'days' is the seven-character days_of_week string the product
        // actually stores, indexed from Sunday, so the strip below is a
        // direct rendering of that column. A once-a-month dinner is NOT
        // expressible as a day-of-week pattern, so it is entered as its
        // own dated activity and its row says so rather than pretending.
        // 'join' is the honest mechanism: nothing, a free sign-up with a
        // capacity, or a ticket.
        // ---------------------------------------------------------------
        $dowLetters = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];
        $dowNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        $strands = [
            'wellness' => ['Wellness', '#2f7d64'],
            'family'   => ['Family',   '#b07d1f'],
            'water'    => ['Water',    '#1f6f93'],
            'music'    => ['Music',    '#8a4a2f'],
            'dining'   => ['Dining',   '#6d4c14'],
        ];

        $programme = [
            ['Sunrise yoga',   'wellness', '0010101', '7:00',  'Place kept', '12 mats',   'free', false],
            ['Kids club',      'family',   '1111111', '10:00', 'Drop in',    'no limit',  'free', false],
            ['Reef walk',      'water',    '0001000', '8:30',  'Place kept', '8 places',  'free', false],
            ['Terrace trio',   'music',    '0000011', '19:30', 'Drop in',    'no limit',  'free', false],
            ['Sunset sail',    'water',    '0000100', '17:45', 'Ticket',     '$60',       'pro',  false],
            ['Cellar dinner',  'dining',   null,      '19:30', 'Ticket',     '$95',       'pro',  true],
        ];

        // Four weeks of sunrise yoga, twice. The upper strip is the single
        // recurring activity: days_of_week '0010101' indexed from Sunday, so
        // it lands on Tuesday, Thursday and Saturday. The lower strip is what
        // the page publishes once ONE date exception is set on the Thursday in
        // week three - slot 18, because 18 % 7 is 4 and index 4 is Thursday.
        // The excepted slot goes back to being an ordinary empty day, which is
        // exactly what the product does: the date is removed, not annotated.
        $yogaPattern = '0010101';
        $exceptSlot = 18;
        $entered = [];
        $published = [];
        foreach (range(0, 27) as $i) {
            $on = $yogaPattern[$i % 7] === '1';
            $entered[] = $on;
            $published[] = ($i === $exceptSlot) ? false : $on;
        }
        $weekLabels = ['Mar 1', 'Mar 8', 'Mar 15', 'Mar 22'];

        // Today's card in the hero. Times are the property's own.
        $today = [
            ['7:00',  'Sunrise yoga',  'wellness', 'Place kept'],
            ['10:00', 'Kids club',     'family',   'Drop in'],
            ['16:00', 'Reef walk',     'water',    'Place kept'],
            ['19:30', 'Terrace trio',  'music',    'Drop in'],
        ];

        // The printed module block on the sleeve. Decorative: a fixed
        // pattern of filled squares with the three corner finders a
        // printed code has, not a scannable code.
        $codeRows = [
            '111010111',
            '100010001',
            '101010101',
            '100000001',
            '110101011',
            '000110100',
            '111010111',
            '100011001',
            '101010101',
        ];

        // The book. Two lines, two mechanisms, and the meters are
        // computed from the same figures the text prints.
        $book = [
            ['Sunrise yoga',  'Thursday',  9,  12, 'Sign-ups', 'free', 'No money changes hands. A capacity, counted for this date only.'],
            ['Cellar dinner', 'Saturday',  22, 30, 'Tickets',  'pro',  '$95 a head, through your own Stripe account. Zero platform fees.'],
        ];

        $faqs = [
            [
                'q' => 'Is Event Schedule free for hotels and resorts?',
                'a' => 'Yes. The activity page and its link, the QR code, standing activities that repeat on chosen days of the week, date exceptions, sub-schedules, free sign-ups with a capacity, the embeddable calendar, two-way Google, Outlook and CalDAV sync and built-in analytics are all free forever. Newsletters are on the free plan too, at 10 emails a month counted per recipient, which Pro raises to 100 and Enterprise to 1,000. Ticketing for the paid experiences is on the Pro plan at $5 a month, and Event Schedule charges zero platform fees on sales.',
            ],
            [
                'q' => 'How do guests find out what is on during their stay?',
                'a' => 'Your schedule has its own address and a QR code you can download and print, so the link can go on the key-card sleeve, the room folder, a sign by the pool or the pre-arrival email. You can also embed the same calendar in the website you already have. Nothing is installed and no account is needed to read it. Guests can follow the schedule, which builds a list you can write a newsletter to; nothing is sent automatically, a newsletter goes out when you send it.',
            ],
            [
                'q' => 'Can I set up the activities that run every week?',
                'a' => 'Yes, on the free plan. An activity can repeat on chosen days of the week at a start time, so sunrise yoga on Tuesday, Thursday and Saturday is one entry rather than three more every week. Date exceptions take individual dates out for the week the court is being resurfaced, and guests simply do not see that day offered. One recurring activity carries one start time, so a morning session and an evening session are two entries.',
            ],
            [
                'q' => 'Can guests reserve a place, and can I sell the paid experiences?',
                'a' => 'Both. A free activity can take sign-ups with a capacity, and the count is kept for each date separately, so a full Tuesday does not close Thursday. Paid experiences use ticketing on the Pro plan: named ticket types with their own prices and quantities, QR check-in at the door, your own Stripe account and no platform fee from us. A promo code can carry a resident rate for the people staying with you.',
            ],
            [
                'q' => 'Can I keep the pool, the spa, the kids club and the conference programme apart?',
                'a' => 'Yes, with sub-schedules, free on every plan. Each one has a name, a colour and its own link, so the spa can point a sign at its own strand of the same calendar. Being straight about what they are: they organise and colour-code, they are not rooms with their own capacity, and nothing is checking whether two activities overlap. A conference day can carry its own agenda inside the event as parts, and anything you are not ready to show stays a Draft, which is members-only until you publish it.',
            ],
            [
                'q' => 'Can more than one person keep the card up to date?',
                'a' => 'The free plan is one team member, and multiple team members are an Enterprise feature capped at five. In between, calendar sync does a lot of the work: the schedule syncs two ways with Google, Outlook or CalDAV, so whoever runs the programme can work in the calendar they already have and the public page follows. Booking requests are free as well, so an act or a planner can ask about a date and it waits for you to accept it before it appears anywhere.',
            ],
        ];

        $dotSections = [
            ['top', 'The card'],
            ['rack', 'The standing week'],
            ['sleeve', 'The sleeve'],
            ['book', 'Keeping a place'],
            ['strands', 'The strands'],
            ['desk', 'Behind the desk'],
            ['who', 'Who it is for'],
            ['how', 'How it works'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];
    @endphp

    <div id="es-conc-page" class="es-conc-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the card in the rack                                -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden pb-16 pt-28">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 26% 30%, rgba(109, 76, 20, 0.2), rgba(109, 76, 20, 0) 62%); opacity: 0.55;"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 74% 58%, rgba(79, 85, 92, 0.2), rgba(79, 85, 92, 0) 62%); opacity: 0.5;"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="es-conc-weave absolute inset-0"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="es-conc-duo">
                <div>
                    <p class="es-conc-eyebrow es-fade-up es-d-1 mb-5">For hotels and resorts</p>

                    <h1 class="es-conc-display es-balance mb-7 text-[2.7rem] sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">The desk closes</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">at eleven.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line es-conc-accent">The card does not.</span></span>
                    </h1>

                    <p class="es-conc-muted es-fade-up es-d-2 mb-9 max-w-xl text-lg sm:text-xl">
                        Guests ask the same four questions all week, and the answer lives with
                        whoever is on the desk plus a printed sheet that went out of date on
                        Tuesday. Put the week on a page with your property's name on it, then
                        print the link on the key-card sleeve and let the card answer at six
                        in the morning.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="es-conc-btn inline-flex items-center justify-center gap-2 rounded px-7 py-4 text-base font-semibold">
                            Put the week on a page
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                        <a href="#rack" class="es-conc-ghost inline-flex items-center justify-center gap-2 rounded px-7 py-4 text-base font-semibold">
                            See the standing card
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                    </div>

                    <p class="es-conc-muted es-fade-up es-d-4 mt-8 max-w-lg text-sm">
                        Free forever for the page, the link, the QR code and the standing week.
                    </p>
                </div>

                <!-- The card. A fixed printed object: it renders the same
                     with the dark theme on or off, because card stock does
                     not know what the browser is set to. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-conc-settle es-conc-stock mx-auto max-w-sm p-6 sm:p-7">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="es-conc-cap es-conc-stock-brass">Today at</p>
                                <p class="es-conc-display es-conc-stock-ink text-2xl">Lantern Bay</p>
                            </div>
                            <div class="es-conc-stock-slot mt-2" aria-hidden="true"></div>
                        </div>

                        <div class="es-conc-stock-rule mt-4" aria-hidden="true"></div>

                        <p class="es-conc-stock-muted es-conc-num es-conc-fine mt-3 uppercase tracking-[0.14em]">Thursday, 12 March</p>

                        <ul class="mt-4 space-y-2">
                            @foreach ($today as [$tTime, $tName, $tStrand, $tJoin])
                                <li class="es-conc-stock-row flex items-center gap-3 p-2.5">
                                    <span class="es-conc-stock-brass es-conc-num w-11 shrink-0 text-xs font-bold">{{ $tTime }}</span>
                                    <span class="min-w-0 flex-1">
                                        <span class="es-conc-stock-ink block truncate text-sm font-semibold">{{ $tName }}</span>
                                        <span class="es-conc-strand es-conc-stock-muted es-conc-finer">
                                            <span class="es-conc-pip" style="background: {{ $strands[$tStrand][1] }};" aria-hidden="true"></span>
                                            {{ $strands[$tStrand][0] }}
                                        </span>
                                    </span>
                                    <span class="es-conc-stock-pill @if ($tJoin === 'Place kept') es-conc-stock-pill-brass @endif">{{ $tJoin }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <div class="es-conc-stock-hair mt-4" aria-hidden="true"></div>

                        <p class="es-conc-stock-muted es-conc-num es-conc-fine mt-3">lanternbay.eventschedule.com</p>
                    </div>

                    <p class="es-conc-muted mx-auto mt-5 max-w-sm text-xs">
                        The same card your desk already keeps. The difference is that this one is a
                        page, so it is right at midnight, and the only thing you ever print is the
                        line at the bottom.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The standing week (01): recurring + exceptions            -->
    <!-- ============================================================ -->
    <section id="rack" class="scroll-mt-24 es-conc-seam py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-conc-tab mb-6" data-reveal aria-hidden="true"><span>01</span></div>
                <p class="es-conc-eyebrow mb-4" data-reveal style="--reveal-delay: 0.05s;">The standing week</p>
                <h2 class="es-conc-display es-balance es-conc-ink text-3xl md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Most of the week is <span class="es-conc-mark">the same week</span>.
                </h2>
                <p class="es-conc-muted mt-6 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Yoga on the lawn Tuesday, Thursday and Saturday at seven. Kids club every
                    morning. The trio on the terrace at the weekend. Enter that rhythm once and
                    the week draws itself for as long as it runs.
                </p>
            </div>

            <div class="es-conc-card p-5 sm:p-7" data-reveal="panel">
                <div class="overflow-x-auto">
                    <table class="es-conc-table">
                        <caption class="sr-only">The standing programme at Lantern Bay: each activity with its strand, the days it repeats, its start time and how a guest joins</caption>
                        <thead>
                            <tr>
                                <th scope="col">Activity</th>
                                <th scope="col" class="hidden sm:table-cell">Strand</th>
                                <th scope="col">Repeats</th>
                                <th scope="col">Time</th>
                                <th scope="col">How to join</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($programme as [$pName, $pStrand, $pDays, $pTime, $pJoin, $pNote, $pTier, $pDated])
                                <tr>
                                    <th scope="row" class="text-sm">{{ $pName }}</th>
                                    <td class="hidden sm:table-cell">
                                        <span class="es-conc-strand es-conc-muted text-xs">
                                            <span class="es-conc-pip" style="background: {{ $strands[$pStrand][1] }};" aria-hidden="true"></span>
                                            {{ $strands[$pStrand][0] }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($pDays)
                                            <span class="sr-only">
                                                @foreach (str_split($pDays) as $dIdx => $dOn)
                                                    @if ($dOn === '1'){{ $dowNames[$dIdx] }}. @endif
                                                @endforeach
                                            </span>
                                            <span class="es-conc-dow" aria-hidden="true">
                                                @foreach (str_split($pDays) as $dIdx => $dOn)
                                                    <span class="@if ($dOn === '1') es-conc-dow-on @endif">{{ $dowLetters[$dIdx] }}</span>
                                                @endforeach
                                            </span>
                                        @else
                                            <span class="es-conc-muted es-conc-num text-xs">one date</span>
                                        @endif
                                    </td>
                                    <td class="es-conc-muted es-conc-num whitespace-nowrap text-xs">{{ $pTime }}</td>
                                    <td>
                                        <span class="es-conc-join @if ($pJoin !== 'Drop in') es-conc-join-brass @endif">{{ $pJoin }}</span>
                                        <span class="es-conc-muted es-conc-num es-conc-finer mt-1 block">
                                            {{ $pNote }}@if ($pTier === 'pro') &middot; Pro @endif
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <p class="es-conc-muted mt-5 text-xs">
                    The lit squares are the days the activity repeats, read from Sunday, which is
                    exactly how the pattern is stored. The cellar dinner has no pattern on purpose:
                    a repeat is by day of the week, so a once-a-month dinner is entered as its own
                    dated activity.
                </p>
            </div>

            <!-- One row of the table above, opened out over four weeks. The
                 lower strip is the same activity after a single date
                 exception, and the excepted Thursday is simply an empty
                 slot: the product removes the date rather than marking it. -->
            <div class="es-conc-card mt-4 p-5 sm:p-7" data-reveal="panel">
                <div class="mb-6 flex flex-wrap items-baseline justify-between gap-x-4 gap-y-2">
                    <h3 class="es-conc-ink text-base font-bold">Sunrise yoga, four weeks of March</h3>
                    <span class="es-conc-muted es-conc-num es-conc-fine">Tuesday &middot; Thursday &middot; Saturday &middot; 7:00</span>
                </div>

                <p class="es-conc-eyebrow mb-2">What you entered</p>
                <div class="es-conc-weeks" aria-hidden="true">
                    @foreach ($weekLabels as $wIdx => $wLabel)
                        <div class="es-conc-wk">
                            <div class="es-conc-wk-cells">
                                @foreach (range(0, 6) as $dOffset)
                                    <span class="@if ($entered[$wIdx * 7 + $dOffset]) es-conc-wk-on @endif"></span>
                                @endforeach
                            </div>
                            <p class="es-conc-wk-label">{{ $wLabel }}</p>
                        </div>
                    @endforeach
                </div>
                <p class="es-conc-muted es-conc-fine mt-2">One activity. Twelve sessions, and nothing to re-enter.</p>

                <div class="es-conc-rule my-6" aria-hidden="true"></div>

                <p class="es-conc-eyebrow mb-2">What the page publishes</p>
                <div class="es-conc-weeks" aria-hidden="true">
                    @foreach ($weekLabels as $wIdx => $wLabel)
                        <div class="es-conc-wk">
                            <div class="es-conc-wk-cells">
                                @foreach (range(0, 6) as $dOffset)
                                    <span class="@if ($published[$wIdx * 7 + $dOffset]) es-conc-wk-on @endif"></span>
                                @endforeach
                            </div>
                            <p class="es-conc-wk-label">{{ $wLabel }}</p>
                        </div>
                    @endforeach
                </div>
                <p class="es-conc-muted es-conc-fine mt-2">
                    One date exception, on Thursday 19 March. Eleven mornings instead of twelve.
                </p>

                <p class="es-conc-muted mt-6 text-xs">
                    <span class="sr-only">Both strips describe the same activity: it runs on Tuesday, Thursday and Saturday for four weeks from 1 March, and a date exception removes Thursday 19 March.</span>
                    Worth being exact about the second strip: the excepted morning is not
                    labelled cancelled and not struck through. The date comes out, so a guest
                    reading that week sees a Wednesday and a Friday and no reason to ask what
                    happened to Thursday.
                </p>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-3" data-reveal-group="100">
                @foreach ([
                    ['One entry, not fifty-two', 'Pick the days and the start time and it keeps appearing. Nothing to re-enter on Sunday night, and nothing that quietly stops because somebody was on holiday.'],
                    ['The weeks it does not run', 'A date exception takes a single date out. Yoga does not run the Thursday the lawn is being cut, and guests are simply not offered that morning rather than being told at the door.'],
                    ['Morning and evening are two entries', 'One repeating activity carries one start time, so a sunrise class and a sunset class are two entries. A little more setup, and no confusion about which one somebody signed up for.'],
                ] as [$rTitle, $rDesc])
                    <div class="es-conc-card es-conc-hover p-6" data-reveal>
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="es-conc-ink text-base font-bold">{{ $rTitle }}</h3>
                            <span class="es-conc-plan">Free</span>
                        </div>
                        <p class="es-conc-muted text-sm">{{ $rDesc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The sleeve (02): the QR, the link, the embed              -->
    <!-- ============================================================ -->
    <section id="sleeve" class="scroll-mt-24 es-conc-seam py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-16">
                <div>
                    <div class="es-conc-tab mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                    <p class="es-conc-eyebrow mb-4" data-reveal style="--reveal-delay: 0.05s;">The sleeve</p>
                    <h2 class="es-conc-display es-balance es-conc-ink mb-6 text-3xl md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Print the link. <span class="es-conc-mark">Never the week</span>.
                    </h2>
                    <p class="es-conc-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        A printed week is wrong the first time something moves, and reprinting it is
                        somebody's Thursday. Print the address instead. It never changes, so the
                        sleeve you had made in March is still correct in November.
                    </p>

                    <ul class="space-y-4" data-reveal-group="90">
                        @foreach ([
                            ['A QR code you can print', 'Free', 'Download your schedule\'s code and put it where guests already look: the key-card sleeve, the room folder, the lift, a sign by the pool, the pre-arrival email. It opens the page in a browser, with nothing to install and no account needed to read it.'],
                            ['The calendar, inside your own site', 'Free', 'Embed the same calendar in the page your website already has, so the "What\'s on" tab stops being a PDF from last season.'],
                            ['A list you can write to', 'Free', 'Guests can follow the schedule, which gives you a list to send a newsletter to. Nothing goes out on its own: a newsletter is something you write and send. The allowance counts recipients, 10 a month free, 100 on Pro and 1,000 on Enterprise.'],
                        ] as [$sTitle, $sPlan, $sDesc])
                            <li class="flex items-start gap-3" data-reveal>
                                <svg aria-hidden="true" class="es-conc-accent mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span>
                                    <span class="es-conc-ink font-semibold">{{ $sTitle }}</span>
                                    <span class="es-conc-plan ms-1.5 align-middle">{{ $sPlan }}</span>
                                    <span class="es-conc-muted block text-sm">{{ $sDesc }}</span>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- The sleeve: the second printed object, same stock. -->
                <div data-reveal="panel">
                    <div class="es-conc-stock es-conc-stock-notch mx-auto max-w-md p-6 sm:p-7">
                        <div class="flex items-start justify-between gap-5">
                            <div class="min-w-0">
                                <p class="es-conc-cap es-conc-stock-brass">Room key</p>
                                <p class="es-conc-display es-conc-stock-ink mt-1 text-xl">What is on this week</p>
                                <p class="es-conc-stock-muted mt-2 text-xs">Point a camera at the code, or type the line below.</p>
                            </div>
                            <div class="w-20 shrink-0 sm:w-24">
                                <div class="es-conc-code" aria-hidden="true">
                                    @foreach ($codeRows as $row)
                                        @foreach (str_split($row) as $cell)
                                            <i class="@if ($cell === '1') es-conc-code-on @endif"></i>
                                        @endforeach
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="es-conc-stock-rule mt-5" aria-hidden="true"></div>

                        <p class="es-conc-stock-ink es-conc-num mt-4 break-all text-sm font-semibold">lanternbay.eventschedule.com</p>
                        <p class="es-conc-stock-muted mt-3 text-xs">
                            One address for the whole property. It is the same page the desk reads
                            from, so nobody is working off two versions of Tuesday.
                        </p>

                        <div class="es-conc-stock-hair mt-5" aria-hidden="true"></div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <span class="es-conc-stock-pill">Key-card sleeve</span>
                            <span class="es-conc-stock-pill">Room folder</span>
                            <span class="es-conc-stock-pill">Pool sign</span>
                            <span class="es-conc-stock-pill">Pre-arrival email</span>
                            <span class="es-conc-stock-pill es-conc-stock-pill-brass">Your own website</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Keeping a place (03): sign-ups free, tickets Pro          -->
    <!-- ============================================================ -->
    <section id="book" class="scroll-mt-24 es-conc-seam py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-conc-tab mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                <p class="es-conc-eyebrow mb-4" data-reveal style="--reveal-delay: 0.05s;">Keeping a place</p>
                <h2 class="es-conc-display es-balance es-conc-ink text-3xl md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Twelve mats means <span class="es-conc-mark">twelve names</span>.
                </h2>
                <p class="es-conc-muted mt-6 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Some things guests just turn up to. Some things have a number: the mats, the
                    boat, the seats in the cellar. Two mechanisms, and the difference is whether
                    money is involved.
                </p>
            </div>

            <div class="grid gap-4 lg:grid-cols-2" data-reveal-group="100">
                @foreach ($book as [$bName, $bDay, $bTaken, $bTotal, $bKind, $bTier, $bNote])
                    <div class="es-conc-card p-6 sm:p-7" data-reveal="panel">
                        <div class="mb-5 flex flex-wrap items-center justify-between gap-2">
                            <h3 class="es-conc-ink text-lg font-bold">{{ $bName }}</h3>
                            @if ($bTier === 'pro')
                                <span class="es-conc-plan es-conc-plan-pro">Pro</span>
                            @else
                                <span class="es-conc-plan">Free</span>
                            @endif
                        </div>

                        <p class="es-conc-display es-conc-ink text-4xl">
                            {{ $bTaken }}<span class="es-conc-muted text-lg font-normal"> of {{ $bTotal }}</span>
                        </p>
                        <p class="es-conc-muted mt-1 text-sm">{{ strtolower($bKind) }} taken for {{ $bDay }} &middot; {{ $bTotal - $bTaken }} left</p>

                        <div class="es-conc-meter mt-4" aria-hidden="true">
                            <div class="es-conc-meter-fill" style="width: {{ (int) round($bTaken / $bTotal * 100) }}%;"></div>
                        </div>

                        <div class="es-conc-rule my-6" aria-hidden="true"></div>
                        <p class="es-conc-muted text-sm">{{ $bNote }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-3" data-reveal-group="90">
                @foreach ([
                    ['Counted for each date', 'Free', 'A full Tuesday does not close Thursday. Every date keeps its own count, which is the only way a standing activity with a limit can work at all.'],
                    ['A ticket for the paid ones', 'Pro', 'Named ticket types with their own prices and quantities, QR check-in at the door, and your own Stripe account. Event Schedule takes nothing from the ticket price.'],
                    ['A rate for people staying with you', 'Pro', 'A promo code carries a resident rate that the desk can hand out. Nothing is verifying who is a guest, so the code is what does it.'],
                ] as [$kTitle, $kPlan, $kDesc])
                    <div class="es-conc-card es-conc-hover p-6" data-reveal>
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="es-conc-ink text-base font-bold">{{ $kTitle }}</h3>
                            @if ($kPlan === 'Pro')
                                <span class="es-conc-plan es-conc-plan-pro">Pro</span>
                            @else
                                <span class="es-conc-plan">Free</span>
                            @endif
                        </div>
                        <p class="es-conc-muted text-sm">{{ $kDesc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. The strands (04): sub-schedules, and what they are not    -->
    <!-- ============================================================ -->
    <section id="strands" class="scroll-mt-24 es-conc-seam py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-start gap-14 lg:grid-cols-2 lg:gap-16">
                <div class="order-2 lg:order-1">
                    <!-- The rack itself: five cards standing behind five
                         coloured tabs. The colour is a free-form hex the
                         owner picks, so it is set inline on the tab edge
                         and nothing about it is baked into the stylesheet. -->
                    <div class="es-conc-card p-6 sm:p-7" data-reveal="panel">
                        <p class="es-conc-eyebrow mb-4">Five strands, one address</p>
                        <div class="space-y-2">
                            @foreach ($strands as $sKey => [$sLabel, $sColor])
                                <div class="es-conc-rackrow" style="border-left-color: {{ $sColor }};">
                                    <span class="es-conc-ink min-w-0 flex-1 truncate text-sm font-semibold">{{ $sLabel }}</span>
                                    <span class="es-conc-muted es-conc-num es-conc-fine truncate">/{{ $sKey }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="es-conc-rule my-6" aria-hidden="true"></div>
                        <p class="es-conc-muted text-xs">
                            Each strand keeps its own link on the same address, so the spa can point
                            a sign at its own list without asking for its own website. The colour is
                            yours to pick, and it is the same colour the strand wears on the card.
                        </p>
                    </div>
                </div>

                <div class="order-1 lg:order-2">
                    <div class="es-conc-tab mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                    <p class="es-conc-eyebrow mb-4" data-reveal style="--reveal-delay: 0.05s;">The strands</p>
                    <h2 class="es-conc-display es-balance es-conc-ink mb-6 text-3xl md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        The spa and the kids club, <span class="es-conc-mark">one card</span>.
                    </h2>
                    <p class="es-conc-muted mb-6 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        Sub-schedules give each part of the programme a name and a colour, so a
                        family arriving on Friday can read the family strand and a couple on a
                        wellness break can read theirs. Free on every plan.
                    </p>

                    <div class="es-conc-card p-5" data-reveal>
                        <p class="es-conc-ink mb-2 text-sm font-bold">What a strand is not</p>
                        <p class="es-conc-muted text-sm">
                            It is a label, not a room. A strand has a name, a colour and a link and
                            nothing else: no capacity of its own, and nothing in it is hidden from
                            anybody. Nothing here checks whether two activities overlap either, so
                            the ballroom is still your call. Anything you are not ready to show yet
                            stays a Draft, which stays members-only until you publish it.
                        </p>
                    </div>

                    <p class="mt-6" data-reveal>
                        <span class="es-conc-plan">Free</span>
                        <span class="es-conc-muted ms-2 text-sm">Strands, Drafts and the address itself cost nothing.</span>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Behind the desk (05): the duplex, fixed-dark band         -->
    <!-- ============================================================ -->
    <section id="desk" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-conc-band noise relative overflow-hidden border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 es-conc-wide">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="es-conc-tab mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                    <p class="es-conc-eyebrow mb-4" data-reveal style="--reveal-delay: 0.05s;">Behind the desk</p>
                    <h2 class="es-conc-display es-balance text-3xl text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        What the desk keeps, <span class="es-conc-mark">and what the card says</span>.
                    </h2>
                    <p class="mt-6 text-lg text-gray-300" data-reveal style="--reveal-delay: 0.15s;">
                        A desk has a drawer as well as a card rack. The two sides are not the same
                        list, and only one of them is public.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2" data-reveal-group="110">
                    <div class="rounded-lg border border-white/10 bg-white/[0.05] p-7" data-reveal="panel">
                        <p class="es-conc-lit es-conc-num mb-4 text-xs font-bold uppercase tracking-[0.18em]">In the drawer</p>
                        <ul class="space-y-3">
                            @foreach ([
                                ['A Draft nobody can see', 'Free', 'The New Year dinner exists, with its price and its date, and stays members-only until you publish it.'],
                                ['An enquiry waiting on you', 'Free', 'Booking requests arrive through the page and wait until you accept one, and the schedule can email you when a new one is sitting there. Nothing appears publicly first.'],
                                ['A date taken out', 'Free', 'The exception for the Wednesday the pool is drained. It removes the date rather than annotating it.'],
                                ['Your own calendar', 'Free', 'Two-way sync with Google, Outlook or CalDAV, so whoever runs the programme works where they already work.'],
                                ['Tonight\'s check-in list', 'Pro', 'For the paid experiences, a scan at the door reads the ticket and marks it used, and the running count is staff-side only.'],
                            ] as [$dTitle, $dPlan, $dDesc])
                                <li class="flex gap-3">
                                    <span class="es-conc-bullet mt-1.5" aria-hidden="true"></span>
                                    <span>
                                        <span class="text-sm font-semibold text-white">{{ $dTitle }}</span>
                                        <span class="es-conc-plan @if ($dPlan === 'Pro') es-conc-plan-pro @endif ms-1.5 align-middle">{{ $dPlan }}</span>
                                        <span class="block text-sm text-gray-400">{{ $dDesc }}</span>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="rounded-lg border border-white/10 bg-white/[0.05] p-7" data-reveal="panel">
                        <p class="es-conc-lit es-conc-num mb-4 text-xs font-bold uppercase tracking-[0.18em]">On the card</p>
                        <ul class="space-y-3">
                            @foreach ([
                                ['Only what you published', 'Free', 'The public page carries exactly what you put on it. Nothing arrives on it because somebody else asked.'],
                                ['Times in the property\'s own zone', 'Free', 'The schedule holds a time zone, so a guest reading the page in another one still sees seven in the morning here.'],
                                ['A day that is simply not offered', 'Free', 'An excepted date does not appear as cancelled. It is not there, which is what a guest actually needs to know.'],
                                ['One tap to their own phone', 'Free', 'Any activity, or a whole repeating one, downloads as a calendar file, so the sunrise class is in their own week.'],
                                ['Nothing they did not ask for', 'Free', 'The card does not say who else signed up, and nobody is emailed because you added something. A newsletter reaches the guests who followed you when you write one and send it.'],
                            ] as [$cTitle, $cPlan, $cDesc])
                                <li class="flex gap-3">
                                    <span class="es-conc-bullet mt-1.5" aria-hidden="true"></span>
                                    <span>
                                        <span class="text-sm font-semibold text-white">{{ $cTitle }}</span>
                                        <span class="es-conc-plan ms-1.5 align-middle">{{ $cPlan }}</span>
                                        <span class="block text-sm text-gray-400">{{ $cDesc }}</span>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <p class="mx-auto mt-8 max-w-2xl text-center text-sm text-gray-400" data-reveal>
                    The counting side is worth a look too: built-in analytics record views per
                    activity, and sales against them, so next season's card is written from what
                    guests actually turned up to. Free on every plan.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Who it is for (06)                                        -->
    <!-- ============================================================ -->
    <section id="who" class="scroll-mt-24 es-conc-seam py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-conc-tab mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                <p class="es-conc-eyebrow mb-4" data-reveal style="--reveal-delay: 0.05s;">Who it is for</p>
                <h2 class="es-conc-display es-balance es-conc-ink text-3xl md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Any property with a <span class="es-conc-mark">week to publish</span>.
                </h2>
                <p class="es-conc-muted mt-6 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    From a twelve-room townhouse to a resort with five strands running at once.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <x-sub-audience-card
                    name="Boutique Hotels"
                    description="A tasting, a supper, a walk with somebody local. A handful of things a month, each one worth a page of its own."
                    icon-color="slate"
                    blog-slug="for-boutique-hotels"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-slate-600 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 21V6a1 1 0 011-1h9a1 1 0 011 1v15M15 11h4a1 1 0 011 1v9M3 21h18M8 9h3m-3 4h3" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Beach Resorts"
                    description="Pool sessions, water sports, sunset yoga, a bonfire on Saturday. A standing week with a limit on the boat."
                    icon-color="amber"
                    blog-slug="for-beach-resorts"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v2m0 12v2m8-8h-2M6 12H4m12.5-5.5l-1.4 1.4M8.9 15.1l-1.4 1.4m9 0l-1.4-1.4M8.9 8.9L7.5 7.5M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Conference Hotels"
                    description="Session times, networking evenings and corporate dinners on one calendar, with each day's agenda listed inside the event."
                    icon-color="sky"
                    blog-slug="for-conference-hotels"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 11h10M4 16h13M4 21h7" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Spa & Wellness Resorts"
                    description="Meditation at six, breath work at eight, a workshop on Sunday. Small numbers, so every place is kept in advance."
                    icon-color="teal"
                    blog-slug="for-spa-resorts"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21c0-6 3-9 8-9-1 6-4 9-8 9zm0 0c0-6-3-9-8-9 1 6 4 9 8 9zm0 0V8" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Mountain Lodges"
                    description="Guided walks, ski lessons, a fire and a talk. The programme changes with the season, and the address does not."
                    icon-color="emerald"
                    blog-slug="for-mountain-lodges"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 20l6-9 3 4.5M12 20l5-8 4 8H3z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Casino Hotels"
                    description="Shows, tournaments, dining nights and late music. A busy card, sold where it needs to be sold."
                    icon-color="orange"
                    blog-slug="for-casino-hotels"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 4h10a1 1 0 011 1v14a1 1 0 01-1 1H7a1 1 0 01-1-1V5a1 1 0 011-1zm5 3l2.5 4L12 15l-2.5-4L12 7z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. How it works (07)                                         -->
    <!-- ============================================================ -->
    <section id="how" class="scroll-mt-24 es-conc-seam py-20 lg:py-24">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-2xl text-center">
                <div class="es-conc-tab mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                <p class="es-conc-eyebrow mb-4" data-reveal style="--reveal-delay: 0.05s;">How it works</p>
                <h2 class="es-conc-display es-balance es-conc-ink text-3xl md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    An afternoon, <span class="es-conc-mark">then it runs</span>.
                </h2>
            </div>

            <div class="grid gap-4 md:grid-cols-3" data-reveal-group="100">
                @foreach ([
                    ['01', 'Take the address', 'Sign up as a venue schedule, put the property\'s name on it, and name the strands: wellness, family, water, dining, whatever you actually run.'],
                    ['02', 'Enter the standing week', 'Add the activities that repeat, with their days and their start times, and set exceptions for the dates they do not run. Give the ones with a number a capacity.'],
                    ['03', 'Print the link', 'Download the QR code for the sleeve and the room folder, embed the calendar in your own site, and put the address in the pre-arrival email.'],
                ] as [$hNum, $hTitle, $hDesc])
                    <div class="es-conc-card p-7" data-reveal="panel">
                        <p class="es-conc-accent es-conc-num mb-3 text-sm font-bold">{{ $hNum }}</p>
                        <h3 class="es-conc-ink mb-2 text-lg font-bold">{{ $hTitle }}</h3>
                        <p class="es-conc-muted text-sm">{{ $hDesc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Key features                                              -->
    <!-- ============================================================ -->
    <section class="es-conc-seam py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-conc-display es-conc-ink mb-8 text-center text-2xl md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="The standing week as one entry, with exceptions for the dates it does not run" :url="marketing_url('/features/recurring-events')" icon-color="amber">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Embed Calendar" description="Put the same calendar inside the hotel website you already have" :url="marketing_url('/features/embed-calendar')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="Sell the paid experiences with QR check-in and zero platform fees" :url="marketing_url('/features/ticketing')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Sub-schedules" description="Give the pool, the spa and the kids club a colour and a link" :url="marketing_url('/features/sub-schedules')" icon-color="teal">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-conc-link inline-flex items-center font-medium hover:underline">
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
    <!-- 10. Related pages                                            -->
    <!-- ============================================================ -->
    <section class="es-conc-seam py-16">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-conc-display es-conc-ink mb-8 text-center text-2xl md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([
                    ['/for-restaurants', 'Restaurants'],
                    ['/for-venues', 'Venues'],
                    ['/for-community-centers', 'Community Centers'],
                    ['/for-bars', 'Bars'],
                ] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="es-conc-card es-conc-hover group flex items-center justify-between p-5">
                        <div>
                            <div class="es-conc-muted text-sm">Event Schedule for</div>
                            <div class="es-conc-ink text-lg font-semibold">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="es-conc-accent h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-conc-link inline-flex items-center font-medium hover:underline">
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
    <section id="faq" class="scroll-mt-24 es-conc-seam py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-conc-tab mb-6" data-reveal aria-hidden="true"><span>08</span></div>
                <p class="es-conc-eyebrow mb-4" data-reveal style="--reveal-delay: 0.05s;">Questions</p>
                <h2 class="es-conc-display es-balance es-conc-ink text-3xl md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Asked <span class="es-conc-mark">across the desk</span>.
                </h2>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ($faqs as $faq)
                    <details name="faq" data-reveal class="es-conc-card es-conc-hover group/faq overflow-hidden">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-6">
                            <h3 class="es-conc-ink text-lg font-semibold">{{ $faq['q'] }}</h3>
                            <svg aria-hidden="true" class="es-conc-muted h-5 w-5 shrink-0 transition-transform duration-300 group-open/faq:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="es-conc-muted faq-answer px-6 pb-6">{{ $faq['a'] }}</p>
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
            <div class="es-conc-band noise relative overflow-hidden border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-25"></div>
                </div>

                <div class="relative z-10">
                    <p class="es-conc-eyebrow mb-6">Free to start</p>
                    <h2 class="es-conc-display es-balance mx-auto mb-6 max-w-3xl text-3xl text-white md:text-5xl">
                        Nobody should have to <span class="es-conc-mark">ask twice</span>.
                    </h2>
                    <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                        The card, the address, the QR code, the standing week and the sign-up sheet
                        are free forever. Selling the paid experiences is five dollars a month, and
                        none of the ticket price comes to us.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-lg border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-property" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-400 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="es-conc-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg px-8 py-4 text-lg font-semibold">
                            <span class="relative z-10 flex items-center gap-2">
                                Put the week on a page
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
                        <span class="es-conc-tip pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
