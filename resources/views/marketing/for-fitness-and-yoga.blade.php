<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Fitness & Yoga | Class Schedule</x-slot>
    <x-slot name="description">Share your class schedule, sell drop-ins and class passes, and reach students directly with newsletters. No algorithm. Zero platform fees. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Fitness & Yoga</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Fitness & Yoga",
        "description": "Publish a weekly class timetable once as recurring classes, then sell visits off a pass instead of seats at a single night. Zero platform fees.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Fitness & Yoga Instructors"
        }
    }
    </script>
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Fitness & Yoga Instructors",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Fitness Class Scheduling Software",
        "operatingSystem": "Web",
        "description": "Set each class up once as a recurring event with the days it runs and a start time, then sell visit passes, memberships and drop-ins with zero platform fees. Built for yoga teachers, personal trainers and fitness studios.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Free forever"
        },
        "featureList": [
            "Recurring classes with a day-of-week pattern, date exceptions and an end",
            "Visit passes, memberships, festival passes and season passes",
            "A cancellation deadline per pass, with forfeit or block after it",
            "A cap on how many advance seats pass holders may take per date",
            "Free registration with a capacity limit per class date",
            "QR check-in with a real-time check-in dashboard",
            "Zero platform fees on class payments through your own Stripe account",
            "Bookable one-to-one appointment types on a public booking page",
            "Newsletters to the students who follow you, with open and click rates",
            "Two-way Google, Outlook and CalDAV calendar sync",
            "Embeddable timetable for the website you already have"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "fitness class schedule, yoga class calendar, class pass, studio timetable, fitness studio scheduling, free fitness scheduling",
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
        "name": "How to put a studio timetable online with Event Schedule",
        "description": "Set the week once, then sell visits rather than single nights.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Set the week",
                "text": "Create each class once as a recurring event: the days of the week it runs, one start time, a length, and date exceptions for the days you are closed."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Price the visit",
                "text": "Add a drop-in ticket and a pass. Choose how the pass is spent, how long it lasts, what it covers, and how many people it admits per class."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Share one link",
                "text": "Put your schedule link in your bio, embed the timetable on your site, and print the follower QR code for the studio door."
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
           For-fitness-and-yoga "The Flow" styles.

           THE CONCEPT. A flow is a sequence you set once and repeat, and
           what a student buys is not a seat at one night - it is a visit
           off a card. Those two sentences are also the product: a class
           is ONE recurring event (days_of_week + a single starts_at + a
           duration), and a class pass is a ticket with is_pass set, spent
           a visit at a time. So the page has exactly two signature
           objects: the TIMETABLE RAIL (the sequence, drawn on a real
           clock) and the CLASS CARD (the visit, drawn as pressed
           cardstock that is the same object in both colour modes).

           THE RAIL IS A CLOCK, NOT A WEEK GRID. `grid-cols-7` is house
           furniture on this site, and worse, a 7-by-2 grid of cells
           implies a morning and an evening class are the same row of
           data. They are not: one recurring event carries ONE starts_at,
           so the 6:30 AM flow and the 6:45 PM vinyasa are two events.
           The rail therefore gives each class its OWN row, positions its
           bar at its real clock time, draws it as wide as its real
           length, and shows days_of_week as a seven-pip strip. Every
           mark on it is a column the product actually stores.

           COLOUR: jade. This page's existing family was emerald/teal;
           teal is claimed by /for-djs and /for-dance-groups and the amber
           dawn wash collided with six other pages, so both are gone and
           the family is held as a single deep jade with a mint
           counterpart for dark grounds. Distinct from /for-theaters'
           bottle green (#14532d) by being cooler and one step brighter,
           and it is never a gradient: an accent word plus a drawn
           underline stroke instead, which also removes the commonest AA
           failure on this site.

           MEASURED (see report): ink #101816 on #eff3f1 = 16.11; muted
           #4a5b56 = 6.42 on ground, 7.19 on white, 5.89 on #e4eae7;
           jade #0b6b52 = 5.79 on ground, 6.48 on white; white on jade =
           6.48; dark ink #e6efec on #09110f = 16.31; dark muted #9aaca6
           = 7.23 on card, 6.62 on band card; mint #6ee7b7 = 11.29 on
           card, 12.27 on band; cardstock ink #12211c on #e9efea = 14.28,
           cardstock muted #4a5b54 = 6.17, jade on cardstock = 5.56.
           NEVER reach for text-gray-500 here: 4.83 on pure white but
           only ~4.4 on this tinted ground.

           TWO ORDERING RULES THIS BLOCK MUST KEEP, both found by the
           band-mode diff rather than by reading:

           1. EVERY base rule and its `.dark` partner come FIRST, and all
              `.es-flow-band ...` / `.es-flow-stock ...` overrides come
              LAST, band before stock. A `.dark .es-flow-count` declared
              after `.es-flow-band .es-flow-count` has equal specificity
              and wins on order, which silently un-pins a fixed object in
              dark mode only.
           2. No `.es-aurora` inside a fixed band: its opacity flips with
              the colour mode. Use a plain inline radial gradient there.
              `.grid-overlay`, `.animate-shimmer` and
              `.es-claim:focus-within` are pinned below for the same
              reason.

           NO NEW ARBITRARY TAILWIND VALUES in the markup: the production
           CSS bundle is prebuilt, so a class like `dark:bg-[#141d1a]`
           that no page used at build time simply does not exist. Anything
           this page needs that Tailwind has not already generated lives
           here instead (.es-flow-edge, .es-flow-tip, .es-flow-record).

           BLADE RULE for this block: no @supports probes - a "#" hex
           inside a parenthesised at-rule condition breaks Blade
           compilation of every later parenthesised directive.
           ============================================================== */

        /* ==== 1. Ground and ink ====================================== */
        .es-flow-page { background-color: #eff3f1; color: #101816; }
        .dark .es-flow-page { background-color: #09110f; color: #e6efec; }
        .es-flow-ink { color: #101816; }
        .dark .es-flow-ink { color: #e6efec; }
        .es-flow-muted { color: #4a5b56; }
        .dark .es-flow-muted { color: #9aaca6; }
        .es-flow-accent { color: #0b6b52; }
        .dark .es-flow-accent { color: #6ee7b7; }
        /* Always-lit accent, for the fixed-dark bands in both modes. */
        .es-flow-lit { color: #6ee7b7; }

        /* ==== 2. Surfaces and edges ================================== */
        .es-flow-card {
            border: 1px solid rgba(16, 24, 22, 0.12);
            border-radius: 1rem;
            background-color: #ffffff;
        }
        .dark .es-flow-card {
            border-color: rgba(230, 239, 236, 0.12);
            background-color: #141d1a;
        }
        .es-flow-sub {
            border: 1px solid rgba(16, 24, 22, 0.1);
            border-radius: 0.75rem;
            background-color: #e4eae7;
        }
        .dark .es-flow-sub {
            border-color: rgba(230, 239, 236, 0.1);
            background-color: #101917;
        }
        /* Hairline divider inside a card. */
        .es-flow-rule { border-top: 1px solid rgba(16, 24, 22, 0.12); }
        .dark .es-flow-rule { border-top-color: rgba(230, 239, 236, 0.12); }
        /* Section edges. A page-local class rather than an arbitrary
           Tailwind colour, which the prebuilt bundle would not carry. */
        .es-flow-edge-t { border-top: 1px solid rgba(16, 24, 22, 0.08); }
        .es-flow-edge-y {
            border-top: 1px solid rgba(16, 24, 22, 0.08);
            border-bottom: 1px solid rgba(16, 24, 22, 0.08);
        }
        .dark .es-flow-edge-t { border-top-color: rgba(230, 239, 236, 0.08); }
        .dark .es-flow-edge-y {
            border-top-color: rgba(230, 239, 236, 0.08);
            border-bottom-color: rgba(230, 239, 236, 0.08);
        }

        /* ==== 3. Typographic signature =============================== */
        /* Lowercase, wide-tracked eyebrows: studio lettering, and a clean
           break from the uppercase printer's labels the rebuilt event
           pages use. */
        .es-flow-eyebrow {
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.3em;
            text-transform: lowercase;
            color: #4a5b56;
        }
        .dark .es-flow-eyebrow { color: #9aaca6; }

        /* Count marks. Studios count rounds, so the section numeral is a
           breath count in a ring rather than a printer's corner block. */
        .es-flow-count {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.65rem;
            height: 2.65rem;
            border-radius: 9999px;
            border: 1px solid rgba(16, 24, 22, 0.18);
            background-color: #ffffff;
            color: #101816;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.04em;
        }
        .dark .es-flow-count {
            border-color: rgba(230, 239, 236, 0.2);
            background-color: #141d1a;
        }
        .dark .es-flow-count { color: #e6efec; }
        .es-flow-count::after {
            content: "";
            position: absolute;
            left: 50%;
            bottom: -0.55rem;
            width: 1px;
            height: 0.4rem;
            margin-left: -0.5px;
            border-radius: 1px;
            background-color: #0b6b52;
        }
        .dark .es-flow-count::after { background-color: #6ee7b7; }

        /* A hand-drawn underline under the accented word of a heading. */
        .es-flow-mark { position: relative; white-space: nowrap; }
        .es-flow-underline {
            position: absolute;
            left: 0;
            bottom: -0.2em;
            width: 100%;
            height: 0.3em;
            overflow: visible;
            fill: none;
            stroke: currentColor;
            stroke-width: 5;
            stroke-linecap: round;
            opacity: 0.5;
        }

        /* ==== 4. Plan pills ========================================== */
        .es-flow-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.25rem;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            border: 1px solid rgba(11, 107, 82, 0.42);
            color: #0b6b52;
        }
        .dark .es-flow-plan { border-color: rgba(110, 231, 183, 0.45); color: #6ee7b7; }
        .es-flow-plan-pro,
        .es-flow-plan-ent { border-color: rgba(16, 24, 22, 0.35); color: #101816; }
        .dark .es-flow-plan-pro,
        .dark .es-flow-plan-ent { border-color: rgba(230, 239, 236, 0.38); color: #e6efec; }

        /* ==== 5. THE TIMETABLE RAIL ================================== */
        .es-flow-scroll { overflow-x: auto; }
        .es-flow-rail { width: 100%; border-collapse: collapse; min-width: 33rem; }
        .es-flow-rail th, .es-flow-rail td { padding: 0.5rem; vertical-align: middle; }
        .es-flow-rail th:first-child, .es-flow-rail td:first-child { padding-inline-start: 0; }
        .es-flow-rail th:last-child, .es-flow-rail td:last-child { padding-inline-end: 0; width: 46%; }
        .es-flow-rail tbody tr { border-top: 1px solid rgba(16, 24, 22, 0.09); }
        .dark .es-flow-rail tbody tr { border-top-color: rgba(230, 239, 236, 0.09); }
        /* The pass table is a record too, just a plainer one. */
        .es-flow-record { width: 100%; border-collapse: collapse; min-width: 30rem; }

        /* The clock ruler, in the header cell of the track column. */
        .es-flow-ruler {
            display: flex;
            justify-content: space-between;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.58rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            color: #4a5b56;
        }
        .dark .es-flow-ruler { color: #9aaca6; }

        /* The track: 6am on the left, 9pm on the right, hairlines every
           three hours so a bar's position is readable, not decorative. */
        .es-flow-track {
            position: relative;
            height: 1.35rem;
            border-radius: 0.3rem;
            background-color: rgba(16, 24, 22, 0.05);
            background-image: repeating-linear-gradient(to right,
                rgba(16, 24, 22, 0.14) 0 1px, transparent 1px 20%);
        }
        .dark .es-flow-track {
            background-color: rgba(230, 239, 236, 0.05);
            background-image: repeating-linear-gradient(to right,
                rgba(230, 239, 236, 0.16) 0 1px, transparent 1px 20%);
        }
        .es-flow-bar {
            position: absolute;
            top: 0.2rem;
            bottom: 0.2rem;
            min-width: 0.55rem;
            border-radius: 0.25rem;
            background-color: #0b6b52;
        }
        .dark .es-flow-bar { background-color: #6ee7b7; }
        .es-flow-bar-soft { background-color: rgba(11, 107, 82, 0.42); }
        .dark .es-flow-bar-soft { background-color: rgba(110, 231, 183, 0.45); }

        /* days_of_week, as seven pips. */
        .es-flow-days { display: inline-flex; gap: 0.18rem; }
        .es-flow-day {
            width: 0.42rem;
            height: 0.42rem;
            border-radius: 9999px;
            background-color: rgba(16, 24, 22, 0.15);
        }
        .dark .es-flow-day { background-color: rgba(230, 239, 236, 0.16); }
        .es-flow-day-on { background-color: #0b6b52; }
        .dark .es-flow-day-on { background-color: #6ee7b7; }

        /* A single class expanded across one week (hero). */
        .es-flow-strip { display: flex; gap: 0.25rem; }
        .es-flow-cell {
            flex: 1 1 0;
            min-width: 0;
            height: 1.9rem;
            border-radius: 0.25rem;
            background-color: rgba(16, 24, 22, 0.06);
        }
        .dark .es-flow-cell { background-color: rgba(230, 239, 236, 0.07); }
        .es-flow-cell-on { background-color: #0b6b52; }
        .dark .es-flow-cell-on { background-color: #6ee7b7; }
        .es-flow-cell-off { background-color: transparent; border: 1px dashed rgba(16, 24, 22, 0.22); }
        .dark .es-flow-cell-off { border-color: rgba(230, 239, 236, 0.24); }

        /* ==== 6. Buttons, links, chips, tooltip ====================== */
        .es-flow-btn {
            background-color: #0b6b52;
            color: #ffffff;
            box-shadow: 0 16px 34px -14px rgba(10, 95, 74, 0.5);
        }
        .es-flow-btn:hover { background-color: #095845; }
        .dark .es-flow-btn { background-color: #6ee7b7; color: #09110f; }
        .dark .es-flow-btn:hover { background-color: #8bf0c8; }

        .es-flow-link { color: #0b6b52; }
        .es-flow-link:hover { color: #101816; }
        .dark .es-flow-link { color: #6ee7b7; }
        .dark .es-flow-link:hover { color: #e6efec; }

        .es-flow-hover:hover { border-color: rgba(11, 107, 82, 0.45); }
        .dark .es-flow-hover:hover { border-color: rgba(110, 231, 183, 0.45); }
        .es-flow-hover:hover .es-flow-hover-title,
        .es-flow-hover:hover .es-flow-hover-arrow { color: #0b6b52; }
        .dark .es-flow-hover:hover .es-flow-hover-title,
        .dark .es-flow-hover:hover .es-flow-hover-arrow { color: #6ee7b7; }

        .es-flow-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            border: 1px solid rgba(16, 24, 22, 0.16);
            background-color: rgba(255, 255, 255, 0.72);
            color: #4a5b56;
            font-size: 0.74rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: lowercase;
        }
        .dark .es-flow-chip {
            border-color: rgba(230, 239, 236, 0.16);
            background-color: rgba(230, 239, 236, 0.05);
            color: #a8b9b3;
        }

        /* Dot-nav tooltip. Page-local, because its dark ground is not a
           colour Tailwind has generated for this bundle. */
        .es-flow-tip { background-color: #ffffff; color: #3f4a47; }
        .dark .es-flow-tip { background-color: #141d1a; color: #c3cec9; }

        /* ==== 7. Breath: the page's one piece of ambient motion ======= */
        @keyframes es-flow-breathe {
            0%, 100% { transform: scale(0.94); opacity: 0.45; }
            50%      { transform: scale(1.07); opacity: 0.85; }
        }
        .es-flow-breathe {
            border-radius: 9999px;
            border: 1px solid rgba(11, 107, 82, 0.22);
            animation: es-flow-breathe 9s ease-in-out infinite;
        }
        .dark .es-flow-breathe { border-color: rgba(110, 231, 183, 0.2); }

        /* Stretch the shared hero entrance onto a slow, even breath.
           Gated behind html.es-anim, so reduced motion sees none of it. */
        html.es-anim .es-flow-breath .es-mask .es-mask-line {
            animation-duration: 1.7s;
            animation-delay: 0.2s;
            animation-timing-function: cubic-bezier(0.37, 0, 0.34, 1);
        }
        html.es-anim .es-flow-breath .es-mask-2 .es-mask-line { animation-delay: 0.85s; }
        html.es-anim .es-flow-breath .es-fade-up {
            animation-duration: 1.5s;
            animation-timing-function: cubic-bezier(0.37, 0, 0.34, 1);
        }
        html.es-anim .es-flow-breath .es-d-1 { animation-delay: 0.3s; }
        html.es-anim .es-flow-breath .es-d-2 { animation-delay: 1.2s; }
        html.es-anim .es-flow-breath .es-d-3 { animation-delay: 1.85s; }
        html.es-anim .es-flow-breath .es-d-4 { animation-delay: 2.4s; }

        /* ==== 8. Shared-system recolours (brand blue by default) ====== */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(11, 107, 82, 0.12), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(110, 231, 183, 0.1), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(11, 107, 82, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(110, 231, 183, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #0b6b52; }
        .dark .es-dot.is-active .es-dot-pip { background: #6ee7b7; }

        /* ==== 9. FIXED OBJECT A: the band (one dark room, both modes) = */
        /* Everything from here down is an override and must stay last. */
        .es-flow-band {
            background-color: #0b1412;
            background-image: radial-gradient(125% 105% at 50% 0%, #14211d 0%, #0e1816 52%, #070d0b 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(230, 239, 236, 0.05);
        }
        .es-flow-band .es-flow-ink { color: #e6efec; }
        .es-flow-band .es-flow-muted { color: #9aaca6; }
        .es-flow-band .es-flow-accent { color: #6ee7b7; }
        .es-flow-band .es-flow-eyebrow { color: #6ee7b7; }
        .es-flow-band .es-flow-card {
            border-color: rgba(230, 239, 236, 0.14);
            background-color: #1a2523;
        }
        .es-flow-band .es-flow-count {
            border-color: rgba(230, 239, 236, 0.2);
            background-color: #1a2523;
            color: #e6efec;
        }
        .es-flow-band .es-flow-count::after { background-color: #6ee7b7; }
        .es-flow-band .es-flow-plan { border-color: rgba(110, 231, 183, 0.45); color: #6ee7b7; }
        .es-flow-band .es-flow-plan-pro,
        .es-flow-band .es-flow-plan-ent { border-color: rgba(230, 239, 236, 0.38); color: #e6efec; }
        .es-flow-band .es-flow-breathe { border-color: rgba(110, 231, 183, 0.2); }
        .es-flow-band .es-flow-btn { background-color: #6ee7b7; color: #09110f; }
        .es-flow-band .es-flow-btn:hover { background-color: #8bf0c8; }
        /* Shared classes that flip with the colour mode. Pinned here. */
        .es-flow-band .grid-overlay {
            background-image:
                linear-gradient(rgba(230, 239, 236, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(230, 239, 236, 0.05) 1px, transparent 1px);
        }
        .es-flow-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-flow-band .es-claim:focus-within {
            border-color: rgba(110, 231, 183, 0.75);
            box-shadow: 0 0 0 4px rgba(110, 231, 183, 0.22);
        }

        /* ==== 10. FIXED OBJECT B: the class card ===================== */
        /* Pressed cardstock. It sits INSIDE the band, so every rule here
           must come after the band's, and none of it may have a `.dark`
           partner: it is one physical object in both colour modes. */
        .es-flow-stock {
            border-radius: 0.9rem;
            background-color: #e9efea;
            background-image: linear-gradient(160deg, #eef2ee 0%, #e9efea 42%, #dfe7e1 100%);
            box-shadow:
                0 22px 46px -18px rgba(0, 0, 0, 0.65),
                inset 0 1px 0 rgba(255, 255, 255, 0.7),
                inset 0 -1px 0 rgba(16, 24, 22, 0.1);
        }
        .es-flow-stock .es-flow-ink { color: #12211c; }
        .es-flow-stock .es-flow-muted { color: #4a5b54; }
        .es-flow-stock .es-flow-accent { color: #0a5f4a; }
        .es-flow-stock .es-flow-eyebrow { color: #4a5b54; }
        .es-flow-stock .es-flow-rule { border-top-color: rgba(16, 24, 22, 0.14); }
        .es-flow-stock .es-flow-plan { border-color: rgba(10, 95, 74, 0.5); color: #0a5f4a; }
        .es-flow-stock .es-flow-plan-pro { border-color: rgba(16, 24, 22, 0.4); color: #12211c; }
        .es-flow-well {
            border-radius: 0.55rem;
            background-color: #dde5df;
            box-shadow: inset 0 1px 3px rgba(16, 24, 22, 0.16);
        }
        /* Visits: spent ones are stamped through, open ones are a hole
           waiting for a stamp. Fixed inks, so the card never changes. */
        .es-flow-punch {
            width: 1.35rem;
            height: 1.35rem;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .es-flow-punch-used {
            background-color: #0a5f4a;
            background-image: radial-gradient(circle at 50% 34%, #0f8163, #084a3a);
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.45);
        }
        .es-flow-punch-open {
            background-color: #d3ddd6;
            border: 1px dashed rgba(16, 24, 22, 0.3);
            box-shadow: inset 0 1px 2px rgba(16, 24, 22, 0.14);
        }
        .es-flow-tick {
            width: 0.7rem;
            height: 0.7rem;
            fill: none;
            stroke: #eaf6f0;
            stroke-width: 3;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        /* ==== 11. Focus rings ======================================== */
        /* No border-radius here: setting it would change the element's
           own shape on focus. Outlines already follow it. */
        #es-flow-page a:focus-visible,
        #es-flow-page summary:focus-visible,
        #es-flow-page button:focus-visible {
            outline: 2px solid #0b6b52;
            outline-offset: 3px;
        }
        .dark #es-flow-page a:focus-visible,
        .dark #es-flow-page summary:focus-visible,
        .dark #es-flow-page button:focus-visible {
            outline-color: #6ee7b7;
        }
        .es-flow-band a:focus-visible,
        .es-flow-band summary:focus-visible,
        .es-flow-band button:focus-visible {
            outline-color: #6ee7b7 !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-flow-breathe { animation: none !important; }
        }
    </style>

    @php
        // ---- The timetable ------------------------------------------------
        // One row per recurring class, because one recurring event carries one
        // starts_at: the 6:30 AM flow and the 6:45 PM vinyasa are two events.
        // start = minutes past midnight, len = minutes, days = Mon..Sun.
        // The rail runs 6:00 AM to 9:00 PM, i.e. 900 minutes wide.
        $railFrom = 360;
        $railSpan = 900;
        $timetable = [
            ['Sunrise Flow',      '6:30 AM',  390,  60, [1, 1, 1, 1, 1, 0, 0], false],
            ['Reformer Pilates',  '9:00 AM',  540,  50, [0, 1, 0, 1, 0, 0, 0], false],
            ['Lunch Express',     '12:15 PM', 735,  30, [1, 0, 1, 0, 1, 0, 0], false],
            ['Strength Circuit',  '5:30 PM',  1050, 45, [1, 0, 1, 0, 0, 0, 0], false],
            ['Power Vinyasa',     '6:45 PM',  1125, 75, [0, 1, 0, 1, 0, 0, 0], false],
            ['Sound Bath',        '7:30 PM',  1170, 60, [0, 0, 0, 0, 1, 0, 0], true],
            ['Weekend Long Flow', '9:00 AM',  540,  90, [0, 0, 0, 0, 0, 1, 1], false],
        ];
        $dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        // The four pass shapes, straight off Ticket::pass_usage_type.
        $passKinds = [
            ['Visit pass', 'A set number of visits, counted down as they are used', 'The ten-class card', 'pass_max_uses'],
            ['Membership', 'No count at all, valid until the pass expires', 'A monthly unlimited', 'pass_valid_days'],
            ['Festival pass', 'One visit to each class the pass covers', 'A six-week course, one visit per session', 'pass_scope'],
            ['Season pass', 'Every occurrence of one recurring class, once each', 'A term of Tuesday 6:45 vinyasa', 'days_of_week'],
        ];

        $faqs = [
            [
                'q' => 'Is Event Schedule free for fitness and yoga instructors?',
                'a' => 'Yes. Publishing your timetable, setting classes up as recurring events, free registration with a capacity limit per date, two-way Google, Outlook and CalDAV sync, the embeddable timetable, built-in analytics and newsletters to 10 recipients a month are all free forever, and so is selling up to 25 paid drop-ins a month. Scanning a QR code at the door is free on every plan too. Class passes, an unlimited number of paid visits and the live check-in dashboard are on the Pro plan at '.plan_price($proMonthly).' a month, and Event Schedule charges zero platform fees on what you sell, free plan included.',
            ],
            [
                'q' => 'Can I schedule recurring weekly classes?',
                'a' => 'Yes, on the free plan. Pick the days of the week a class runs, give it one start time and a length, and add date exceptions for the days you are closed. One recurring event carries one start time, so a 6:30 AM flow and a 6:45 PM vinyasa are two events on the timetable rather than two entries on one row. That is a little more setup, and it keeps each class its own thing with its own tickets.',
            ],
            [
                'q' => 'How do students find and follow my classes?',
                'a' => 'You get one link for the whole timetable, so it works in a bio, on a flyer, and as a QR code you can print for the studio door. Students who follow your schedule are yours: you can see their name and email, and you write and send the newsletter yourself. To be straight about it, there is no automatic alert when you add a class. Nothing is emailed to your followers unless you send it.',
            ],
            [
                'q' => 'Can I sell class passes and drop-ins?',
                'a' => 'Yes, through your own Stripe account. Single drop-ins sell on the free plan, up to 25 paid tickets a month, and the Pro plan at '.plan_price($proMonthly).' a month takes that ceiling off. Passes are the Pro half: alongside a single drop-in you can sell a visit pass with a set number of visits, a membership that is unlimited until it expires, a festival pass good for each covered class once, or a season pass covering every occurrence of one recurring class. Set how long the pass lasts, whether it covers the whole schedule, one sub-schedule or named classes, and how many people it admits at each class. Event Schedule charges zero platform fees on either plan, so past Stripe\'s own processing the money is yours.',
            ],
            [
                'q' => 'What happens when somebody cancels at the last minute?',
                'a' => 'You set a cancellation deadline on the pass, measured in hours before the class starts. Cancel before it and the visit goes back on the pass. After it, your choice applies: either the booking can still be cancelled but the visit stays spent, which releases the mat without giving a no-show a free credit, or cancelling is not allowed at all. You can also cap how many mats pass holders may reserve in advance per date, so a walk-up can still get in.',
            ],
            [
                'q' => 'Can students book a one-to-one with me?',
                'a' => 'Yes, and one appointment type is free. Appointment types are separate from classes: give one a length, a start-time interval, the hours you are open each week, buffers before and after, how much notice you need and how far ahead people may book, then students pick a slot on your public booking page. Each type says whether it happens in the studio, online or by phone, can require your approval before it is confirmed, and can be free or paid. Pro lifts the one-type limit, so a private session, an assessment and a beginners consultation can sit side by side. Your classes already count as busy time, so nobody books a session on top of one.',
            ],
            [
                'q' => 'Can my other teachers log in?',
                'a' => 'The free plan is one login. Multiple team members, capped at five, are an Enterprise feature at '.plan_price($entMonthly).' a month, as is the availability tab that tracks which team members are around on which days. If you only need the timetable to say who is teaching, put the instructor in the class itself and stay on the free plan.',
            ],
        ];

        $dotSections = [
            ['top', 'The flow'],
            ['unit', 'Not a night'],
            ['week', 'The timetable'],
            ['pass', 'The card'],
            ['kinds', 'Four passes'],
            ['door', 'The deadline'],
            ['onetoone', 'One-to-ones'],
            ['rest', 'Everything else'],
            ['who', 'Perfect for'],
            ['faq', 'Questions'],
            ['claim', 'Start'],
        ];
    @endphp

    <div id="es-flow-page" class="es-flow-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: one class, expanded across a week                   -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero es-flow-breath noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 32%, rgba(11, 107, 82, 0.2), rgba(11, 107, 82, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 42%, rgba(110, 231, 183, 0.16), rgba(110, 231, 183, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <div class="es-flow-breathe absolute right-[10%] top-20 hidden h-40 w-40 md:block"></div>
            <div class="es-flow-breathe absolute bottom-20 left-[10%] hidden h-28 w-28 md:block" style="animation-delay: 4s;"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="es-flow-accent h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="es-flow-muted text-sm font-medium tracking-wide">For fitness studios and yoga teachers</span>
                    </div>

                    <h1 class="es-balance es-flow-ink mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">A studio week is a sequence.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">What you sell is a <span class="es-flow-accent es-flow-mark">visit<svg class="es-flow-underline" viewBox="0 0 200 12" preserveAspectRatio="none" aria-hidden="true"><path d="M3 8c34-6 78-7 116-4 26 2 52 5 78 3" /></svg></span>.</span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-flow-muted mb-10 max-w-xl text-lg sm:text-xl">
                        Set each class up once with the days it runs and one start time, then sell drop-ins, ten-class cards and memberships from a single link. Reach your students directly, with zero platform fees.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="#week" class="glass group inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            See the week
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="es-flow-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Create your schedule
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- One recurring class, expanded across a week. Two strips,
                     because two start times are two events. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-flow-card p-6 sm:p-7">
                        <p class="es-flow-eyebrow mb-5">two events, seven classes</p>

                        <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                            <p class="es-flow-ink text-base font-bold">Sunrise Flow</p>
                            <span class="es-flow-muted font-mono text-xs">6:30 AM &middot; 60 min</span>
                        </div>
                        <div class="es-flow-strip mt-2" aria-hidden="true">
                            @foreach ([1, 1, 1, 1, 1, 0, 0] as $on)
                                <div class="es-flow-cell {{ $on ? 'es-flow-cell-on' : 'es-flow-cell-off' }}"></div>
                            @endforeach
                        </div>
                        <p class="es-flow-muted mt-2 text-[11px]">Monday to Friday. Dashed cells are days it does not run.</p>

                        <div class="es-flow-rule mt-5 pt-5">
                            <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                                <p class="es-flow-ink text-base font-bold">Power Vinyasa</p>
                                <span class="es-flow-muted font-mono text-xs">6:45 PM &middot; 75 min</span>
                            </div>
                            <div class="es-flow-strip mt-2" aria-hidden="true">
                                @foreach ([0, 1, 0, 1, 0, 0, 0] as $on)
                                    <div class="es-flow-cell {{ $on ? 'es-flow-cell-on' : 'es-flow-cell-off' }}"></div>
                                @endforeach
                            </div>
                            <p class="es-flow-muted mt-2 text-[11px]">A different start time is a different class, and a separate event.</p>
                        </div>

                        <p class="es-flow-muted es-flow-rule mt-5 pt-4 text-xs">
                            Two recurring events, seven classes a week, and nothing retyped. Move a start time once and every class in the pattern follows.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Discipline marquee -->
            <div class="es-fade-up es-d-4 mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach (['Vinyasa', 'Pilates', 'HIIT', 'Barre', 'Spin', 'Bootcamp', 'Strength', 'Mobility', 'Sound bath', 'Prenatal', 'CrossFit', 'Meditation'] as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-flow-chip">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. Not a night: the unit is a visit (fixed-dark band)        -->
    <!-- ============================================================ -->
    <section id="unit" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-flow-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
                <div class="es-flow-breathe absolute left-[10%] top-16 h-44 w-44"></div>
                <div class="es-flow-breathe absolute bottom-16 right-[10%] h-28 w-28" style="animation-delay: 4.5s;"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-flow-count mb-8" data-reveal aria-hidden="true"><span>01</span></div>
                    <p class="es-flow-eyebrow mb-4" data-reveal style="--reveal-delay: 0.05s;">the unit</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Nobody buys a <span class="es-flow-lit es-flow-mark">Tuesday<svg class="es-flow-underline" viewBox="0 0 200 12" preserveAspectRatio="none" aria-hidden="true"><path d="M3 8c34-6 78-7 116-4 26 2 52 5 78 3" /></svg></span>.
                    </h2>
                    <p class="es-flow-muted mt-6 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Event tools are built around a night: one date, one ticket, one door. A studio does not work that way. Students buy ten of something and spend them one class at a time, which is a different object with different rules.
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    <div class="es-flow-card p-6" data-reveal="panel">
                        <p class="es-flow-eyebrow mb-3">the class</p>
                        <h3 class="es-flow-ink mb-2 text-lg font-bold">
                            <span data-count-to="1">1</span> recurring event
                        </h3>
                        <p class="es-flow-muted text-sm">The days of the week, one start time, a length, and date exceptions for the days you are closed. Entering a whole term one class at a time is a hundred chances to mistype a time.</p>
                    </div>
                    <div class="es-flow-card p-6" data-reveal="panel">
                        <p class="es-flow-eyebrow mb-3">the visit</p>
                        <h3 class="es-flow-ink mb-2 text-lg font-bold">
                            <span data-count-to="1">1</span> off the card
                        </h3>
                        <p class="es-flow-muted text-sm">A pass is spent a visit at a time, and every use is recorded against the class it was spent on. You can see which sessions your card holders actually turn up to.</p>
                    </div>
                    <div class="es-flow-card p-6" data-reveal="panel">
                        <p class="es-flow-eyebrow mb-3">the money</p>
                        <h3 class="es-flow-ink mb-2 text-lg font-bold">{{ plan_price(0) }} taken</h3>
                        <p class="es-flow-muted text-sm">Payments land in your own Stripe account. Event Schedule takes no cut of a drop-in, a pass or a membership, on any plan.</p>
                    </div>
                </div>

                <p class="es-flow-muted mt-10 text-center" data-reveal>
                    Set the sequence, then price the visit.
                    <a href="#week" class="es-flow-lit inline-flex items-center gap-1 font-semibold transition-all hover:gap-2">
                        Start with the week
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The timetable rail: a real table on a real clock          -->
    <!-- ============================================================ -->
    <section id="week" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-flow-count mb-8" data-reveal aria-hidden="true"><span>02</span></div>
                <p class="es-flow-eyebrow mb-4" data-reveal style="--reveal-delay: 0.05s;">the timetable</p>
                <h2 class="es-balance es-flow-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Seven classes. Seven <span class="es-flow-accent es-flow-mark">events<svg class="es-flow-underline" viewBox="0 0 200 12" preserveAspectRatio="none" aria-hidden="true"><path d="M3 8c34-6 78-7 116-4 26 2 52 5 78 3" /></svg></span>.
                </h2>
                <p class="es-flow-muted mt-6 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Each row below is one recurring event: its own start time, its own length, its own days. That is genuinely how it is stored, which is why the whole week is seven things to keep, not two hundred.
                </p>
            </div>

            <div class="es-flow-card p-5 sm:p-8" data-reveal="panel">
                <div class="es-flow-scroll">
                    <table class="es-flow-rail">
                        <caption class="es-flow-muted mb-4 text-start text-xs">
                            A studio week, drawn on a clock from 6:00 AM to 9:00 PM. Bar position is the start time, bar length is the class length.
                        </caption>
                        <thead>
                            <tr>
                                <th scope="col" class="es-flow-eyebrow text-start">class</th>
                                <th scope="col" class="es-flow-eyebrow text-start">starts</th>
                                <th scope="col" class="es-flow-eyebrow text-start">days</th>
                                <th scope="col">
                                    <div class="es-flow-ruler" aria-hidden="true">
                                        <span>6a</span><span>9a</span><span>12p</span><span>3p</span><span>6p</span><span>9p</span>
                                    </div>
                                    <span class="sr-only">Where in the day the class falls</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($timetable as [$cName, $cTime, $cStart, $cLen, $cDays, $cSoft])
                                @php
                                    $left = round(($cStart - $railFrom) / $railSpan * 100, 2);
                                    $width = round($cLen / $railSpan * 100, 2);
                                    $dayList = collect($cDays)->filter()->keys()->map(fn ($k) => $dayNames[$k])->implode(', ');
                                @endphp
                                <tr>
                                    <th scope="row" class="es-flow-ink text-start text-sm font-bold">{{ $cName }}</th>
                                    <td class="es-flow-muted whitespace-nowrap font-mono text-xs">{{ $cTime }}</td>
                                    <td>
                                        <span class="es-flow-days" aria-hidden="true">
                                            @foreach ($cDays as $on)
                                                <span class="es-flow-day {{ $on ? 'es-flow-day-on' : '' }}"></span>
                                            @endforeach
                                        </span>
                                        <span class="sr-only">{{ $dayList }}</span>
                                    </td>
                                    <td>
                                        <div class="es-flow-track" aria-hidden="true">
                                            <div class="es-flow-bar {{ $cSoft ? 'es-flow-bar-soft' : '' }}" style="left: {{ $left }}%; width: {{ $width }}%;"></div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="es-flow-muted es-flow-rule mt-5 pt-4 text-xs">
                    The pale bar is a class held as a draft while you decide whether to keep it. A draft is how you hide a class: sub-schedules colour and group your timetable, but they cannot hide anything.
                </p>
            </div>

            <div class="mt-8 grid gap-6 md:grid-cols-3" data-reveal-group="100">
                <div class="es-flow-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-flow-ink text-lg font-bold">The days it runs</h3>
                        <span class="es-flow-plan">Free</span>
                    </div>
                    <p class="es-flow-muted text-sm">Tick the days of the week and set the start time. Monday to Friday is five classes a week without entering five classes.</p>
                </div>
                <div class="es-flow-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-flow-ink text-lg font-bold">The days you are closed</h3>
                        <span class="es-flow-plan">Free</span>
                    </div>
                    <p class="es-flow-muted text-sm">Date exceptions take single dates out, so a public holiday or a week away does not need the class rebuilding. They can add a one-off date in, too.</p>
                </div>
                <div class="es-flow-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-flow-ink text-lg font-bold">The end</h3>
                        <span class="es-flow-plan">Free</span>
                    </div>
                    <p class="es-flow-muted text-sm">A finishing date, or a number of sessions. This is what turns a six-week beginners course into a course instead of a Tuesday night that never stops.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. The card: the fixed physical object (fixed-dark band)     -->
    <!-- ============================================================ -->
    <section id="pass" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-flow-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
                <div class="absolute inset-0" style="background: radial-gradient(58% 46% at 62% 34%, rgba(110, 231, 183, 0.1), rgba(110, 231, 183, 0) 70%);"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-6xl">
                <div class="grid items-center gap-12 lg:grid-cols-2">
                    <div>
                        <div class="es-flow-count mb-8" data-reveal aria-hidden="true"><span>03</span></div>
                        <p class="es-flow-eyebrow mb-4" data-reveal style="--reveal-delay: 0.05s;">the card</p>
                        <h2 class="es-balance mb-5 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                            Ten visits, and the <span class="es-flow-lit es-flow-mark">count<svg class="es-flow-underline" viewBox="0 0 200 12" preserveAspectRatio="none" aria-hidden="true"><path d="M3 8c34-6 78-7 116-4 26 2 52 5 78 3" /></svg></span> looks after itself.
                        </h2>
                        <p class="es-flow-muted mb-8 text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                            A pass is a ticket type with a different job. It carries its own allowance, its own expiry, its own reach across your timetable and its own rules at the door, and the tally is kept for you instead of in a shoebox by the till.
                        </p>
                        <ul class="es-flow-muted space-y-4" data-reveal-group="70">
                            <li class="flex gap-3" data-reveal>
                                <svg aria-hidden="true" class="es-flow-lit mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span>Give the pass a life in days from purchase, or leave it blank and it never expires.</span>
                            </li>
                            <li class="flex gap-3" data-reveal>
                                <svg aria-hidden="true" class="es-flow-lit mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span>Set how many people it admits at each class, so a card that brings a friend is two through the door on one code.</span>
                            </li>
                            <li class="flex gap-3" data-reveal>
                                <svg aria-hidden="true" class="es-flow-lit mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span>Let holders reserve a mat for specific dates ahead of time, or keep the pass scan-at-the-door only.</span>
                            </li>
                            <li class="flex gap-3" data-reveal>
                                <svg aria-hidden="true" class="es-flow-lit mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span>Passes are a Pro feature. The timetable they run against, and your first 25 paid drop-ins a month, are not.</span>
                            </li>
                        </ul>
                    </div>

                    <!-- The class card. One object, identical in both modes. -->
                    <div data-reveal="panel">
                        <div class="es-flow-stock p-6 sm:p-7">
                            <div class="mb-5 flex items-start justify-between gap-4">
                                <div>
                                    <p class="es-flow-eyebrow mb-1">riverbend studio</p>
                                    <h3 class="es-flow-ink text-xl font-black tracking-tight">Ten-class card</h3>
                                </div>
                                <span class="es-flow-plan es-flow-plan-pro">Pro</span>
                            </div>

                            <div class="es-flow-well p-4" aria-hidden="true">
                                <div class="grid grid-cols-5 justify-items-center gap-2.5">
                                    @for ($visit = 0; $visit < 10; $visit++)
                                        <span class="es-flow-punch {{ $visit < 3 ? 'es-flow-punch-used' : 'es-flow-punch-open' }}">
                                            @if ($visit < 3)
                                                <svg class="es-flow-tick" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                            @endif
                                        </span>
                                    @endfor
                                </div>
                            </div>
                            <p class="es-flow-muted mt-3 text-center text-xs">
                                <span class="es-flow-accent font-bold">3 spent</span> &middot; 7 visits left
                            </p>

                            <dl class="es-flow-rule mt-5 space-y-2.5 pt-4 text-sm">
                                <div class="flex items-baseline justify-between gap-3">
                                    <dt class="es-flow-muted">Valid for</dt>
                                    <dd class="es-flow-ink font-semibold">90 days from purchase</dd>
                                </div>
                                <div class="flex items-baseline justify-between gap-3">
                                    <dt class="es-flow-muted">Covers</dt>
                                    <dd class="es-flow-ink font-semibold">Every class on the schedule</dd>
                                </div>
                                <div class="flex items-baseline justify-between gap-3">
                                    <dt class="es-flow-muted">Admits</dt>
                                    <dd class="es-flow-ink font-semibold">1 per class, holder only</dd>
                                </div>
                                <div class="flex items-baseline justify-between gap-3">
                                    <dt class="es-flow-muted">Advance mats</dt>
                                    <dd class="es-flow-ink font-semibold">2 per date, all cards</dd>
                                </div>
                            </dl>

                            <p class="es-flow-muted es-flow-rule mt-4 pt-3 text-[11px]">
                                Every card carries a QR code. Scan it at the door from any phone, and the visit comes off the count.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Four passes: a real record, so a real table               -->
    <!-- ============================================================ -->
    <section id="kinds" class="scroll-mt-24 es-flow-edge-y py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-flow-count mb-8" data-reveal aria-hidden="true"><span>04</span></div>
                <p class="es-flow-eyebrow mb-4" data-reveal style="--reveal-delay: 0.05s;">four shapes</p>
                <h2 class="es-balance es-flow-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    Pick how the pass is <span class="es-flow-accent es-flow-mark">spent<svg class="es-flow-underline" viewBox="0 0 200 12" preserveAspectRatio="none" aria-hidden="true"><path d="M3 8c34-6 78-7 116-4 26 2 52 5 78 3" /></svg></span>.
                </h2>
                <p class="es-flow-muted mt-6 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    These are the four options as the app names them, and what each one turns into on a studio noticeboard. All four are on the Pro plan.
                </p>
            </div>

            <div class="es-flow-card p-5 sm:p-8" data-reveal="panel">
                <div class="es-flow-scroll">
                    <table class="es-flow-record text-start">
                        <caption class="sr-only">The four class pass types, how each is spent, and the studio equivalent</caption>
                        <thead>
                            <tr>
                                <th scope="col" class="es-flow-eyebrow pb-3 text-start">pass type</th>
                                <th scope="col" class="es-flow-eyebrow pb-3 text-start">how it is spent</th>
                                <th scope="col" class="es-flow-eyebrow hidden pb-3 text-start sm:table-cell">in a studio</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($passKinds as [$pName, $pSpend, $pStudio, $pField])
                                <tr class="es-flow-edge-t">
                                    <th scope="row" class="es-flow-ink py-4 pe-4 align-top text-sm font-bold">
                                        {{ $pName }}
                                        <span class="es-flow-muted block font-mono text-[10px] font-normal">{{ $pField }}</span>
                                    </th>
                                    <td class="es-flow-muted py-4 pe-4 align-top text-sm">{{ $pSpend }}</td>
                                    <td class="es-flow-muted hidden py-4 align-top text-sm sm:table-cell">{{ $pStudio }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="es-flow-muted es-flow-rule mt-5 pt-4 text-xs">
                    Whatever the shape, you choose its reach: every class on the schedule including ones you add later, every class in one sub-schedule, or a named handful. A season pass is the exception and belongs to a single recurring class.
                </p>
            </div>

            <p class="es-flow-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                Connect Stripe and sell straight from the timetable. Event Schedule charges zero platform fees, so past Stripe's own processing the money is yours.
                <a href="{{ marketing_url('/features/ticketing') }}" class="es-flow-link font-medium hover:underline">How ticketing works</a>
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. The deadline: a duplex, because the rule has two sides    -->
    <!-- ============================================================ -->
    <section id="door" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-flow-count mb-8" data-reveal aria-hidden="true"><span>05</span></div>
                <p class="es-flow-eyebrow mb-4" data-reveal style="--reveal-delay: 0.05s;">the deadline</p>
                <h2 class="es-balance es-flow-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    The mat that goes <span class="es-flow-accent es-flow-mark">empty<svg class="es-flow-underline" viewBox="0 0 200 12" preserveAspectRatio="none" aria-hidden="true"><path d="M3 8c34-6 78-7 116-4 26 2 52 5 78 3" /></svg></span>.
                </h2>
                <p class="es-flow-muted mt-6 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Set a cancellation deadline on the pass, in hours before the class starts. One rule, two outcomes, and you decide which side the late cancel falls on.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-2" data-reveal-group="110">
                <div class="es-flow-card p-7 sm:p-8" data-reveal="panel">
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <p class="es-flow-eyebrow">before the deadline</p>
                        <span class="es-flow-plan es-flow-plan-pro">Pro</span>
                    </div>
                    <h3 class="es-flow-ink mb-3 text-2xl font-bold tracking-tight">The visit goes back on.</h3>
                    <p class="es-flow-muted mb-5">Cancel in time and the credit returns to the pass, the mat is released, and nobody has to email you about it.</p>
                    <div class="es-flow-sub p-4" aria-hidden="true">
                        <div class="flex items-center justify-between gap-3">
                            <span class="es-flow-muted text-xs">Pass balance</span>
                            <span class="es-flow-accent font-mono text-sm font-bold">7 visits left</span>
                        </div>
                    </div>
                </div>

                <div class="es-flow-card p-7 sm:p-8" data-reveal="panel">
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <p class="es-flow-eyebrow">after the deadline</p>
                        <span class="es-flow-plan es-flow-plan-pro">Pro</span>
                    </div>
                    <h3 class="es-flow-ink mb-3 text-2xl font-bold tracking-tight">Your call: spent, or locked.</h3>
                    <p class="es-flow-muted mb-5">Either the booking can still be cancelled but the visit stays spent, which frees the mat without handing a no-show a free credit, or cancelling stops being possible and the booking stands.</p>
                    <div class="es-flow-sub p-4" aria-hidden="true">
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <span class="es-flow-muted text-xs">Cancel late, keep the visit spent</span>
                            <span class="es-flow-ink font-mono text-xs font-bold">forfeit</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="es-flow-muted text-xs">No cancelling after the cut-off</span>
                            <span class="es-flow-ink font-mono text-xs font-bold">block</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="es-flow-card mt-6 p-7 sm:p-8" data-reveal="panel">
                <div class="grid gap-6 md:grid-cols-2 md:items-center">
                    <div>
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="es-flow-ink text-lg font-bold">How many mats fit</h3>
                            <span class="es-flow-plan">Free</span>
                        </div>
                        <p class="es-flow-muted text-sm">For a class you are not charging for, turn on registration and give it a capacity. The remaining places are counted per date, so this Wednesday filling up does not close next Wednesday. Pass holders get their own separate cap on how many of a date's places they may reserve between them.</p>
                    </div>
                    <div class="es-flow-sub p-4" aria-hidden="true">
                        <div class="space-y-2">
                            @foreach ([['Wed 15', 'full', true], ['Wed 22', '4 places left', false], ['Wed 29', '12 places left', false]] as [$dLabel, $dState, $dFull])
                                <div class="flex items-center justify-between gap-3">
                                    <span class="es-flow-muted font-mono text-xs">{{ $dLabel }}</span>
                                    <span class="{{ $dFull ? 'es-flow-muted' : 'es-flow-accent' }} text-xs font-semibold">{{ $dState }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. One-to-ones: a different shape entirely                   -->
    <!-- ============================================================ -->
    <section id="onetoone" class="scroll-mt-24 es-flow-edge-t py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-flow-count mb-8" data-reveal aria-hidden="true"><span>06</span></div>
                <p class="es-flow-eyebrow mb-4" data-reveal style="--reveal-delay: 0.05s;">one-to-ones</p>
                <h2 class="es-balance es-flow-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    A private session is not a <span class="es-flow-accent es-flow-mark">class<svg class="es-flow-underline" viewBox="0 0 200 12" preserveAspectRatio="none" aria-hidden="true"><path d="M3 8c34-6 78-7 116-4 26 2 52 5 78 3" /></svg></span>.
                </h2>
                <p class="es-flow-muted mt-6 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    So it is not built like one. Appointment types are their own thing: you describe when you are free, and students pick a slot on your public booking page.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-3" data-reveal-group="100">
                <div class="es-flow-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-flow-ink text-lg font-bold">The hours you teach</h3>
                        <span class="es-flow-plan">Free</span>
                    </div>
                    <p class="es-flow-muted text-sm">Weekly hours per day, and per-date overrides for the days that differ. A public holiday or a workshop weekend is a change to one date, not to the pattern.</p>
                </div>
                <div class="es-flow-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-flow-ink text-lg font-bold">Room to breathe</h3>
                        <span class="es-flow-plan">Free</span>
                    </div>
                    <p class="es-flow-muted text-sm">A length, the interval slots start on, buffers before and after, the notice you need, and how far ahead people may book. Nobody lands a 6:00 AM assessment at midnight.</p>
                </div>
                <div class="es-flow-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-flow-ink text-lg font-bold">Studio, screen or phone</h3>
                        <span class="es-flow-plan">Free</span>
                    </div>
                    <p class="es-flow-muted text-sm">Each type says where it happens, can ask for your approval before it is confirmed, and can be free or paid. If a time has to move, the booking moves with its payment and its private link rather than being cancelled and rebuilt.</p>
                </div>
            </div>

            <p class="es-flow-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                Your classes count as busy time. The slot list already knows every recurring class on your timetable, so nobody books a one-to-one on top of Thursday's vinyasa. One appointment type is free; Pro is what lets several run side by side.
                <a href="{{ marketing_url('/features/appointments') }}" class="es-flow-link font-medium hover:underline">How appointments work</a>
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Everything else: bento                                    -->
    <!-- ============================================================ -->
    <section id="rest" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-flow-count mb-8" data-reveal aria-hidden="true"><span>07</span></div>
                <p class="es-flow-eyebrow mb-4" data-reveal style="--reveal-delay: 0.05s;">everything else</p>
                <h2 class="es-balance es-flow-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Between the first mat and the last.
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">
                <!-- 1 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-flow-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-flow-ink text-xl font-bold">Write to the people who already come</h3>
                                <span class="es-flow-plan">Free</span>
                            </div>
                            <p class="es-flow-muted mb-4">Students follow your schedule and you email them: the new term, a cover teacher, a workshop with places left. You compose it and you send it, so nothing goes out that you did not write. Open and click rates afterwards tell you whether it landed.</p>
                            <p class="es-flow-muted text-sm">The numbers worth knowing first: 10 emails a month on Free, 100 on Pro and 1,000 on Enterprise, counted per recipient rather than per send.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-flow-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-flow-ink text-xl font-bold">A code for the door</h3>
                                <span class="es-flow-plan">Free</span>
                            </div>
                            <p class="es-flow-muted">Download your schedule's QR code and put it on the studio door, the mat rack, the back of a card. A phone camera is all it takes to follow you, and it costs nothing on any plan.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-flow-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-flow-ink text-xl font-bold">Strands, not silos</h3>
                                <span class="es-flow-plan">Free</span>
                            </div>
                            <p class="es-flow-muted">Sub-schedules group and colour your timetable, so yoga, strength and workshops read apart at a glance on one link. They organise; they do not hide. Hiding a class is a draft.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-flow-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-flow-ink text-xl font-bold">On the site you already have, and the calendar you already use</h3>
                                <span class="es-flow-plan">Free</span>
                            </div>
                            <p class="es-flow-muted mb-4">Embed the timetable on your own site so the week lives where people look you up, and sync two ways with Google, Outlook or CalDAV so your teaching hours and your life are one calendar. Worth knowing: a recurring class syncs across as a single entry, not as a repeating one. To see every class date in your calendar app, subscribe to the schedule's feed instead, which unrolls the next 90 days one date at a time. Any single class date also downloads as an .ics file.</p>
                            <p class="es-flow-muted text-sm">
                                Teaching online as well? Mark the class as an online event and paste the link to wherever you are streaming it.
                                <a href="{{ marketing_url('/features/online-events') }}" class="es-flow-link font-medium hover:underline">How online events work</a>
                            </p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-flow-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-flow-ink text-xl font-bold">The teaching team</h3>
                                <span class="es-flow-plan es-flow-plan-ent">Enterprise</span>
                            </div>
                            <p class="es-flow-muted">The free plan is one login, and there is no way around that. Multiple team members, capped at five, plus the availability tab that tracks who is around, are Enterprise. Naming the instructor on the class itself needs neither.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-flow-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-flow-ink text-xl font-bold">Which classes people actually look at</h3>
                                <span class="es-flow-plan">Free</span>
                            </div>
                            <p class="es-flow-muted mb-4">Built-in analytics rank your classes by views, show the devices people are on and where the traffic came from, and, once you are selling, which classes brought the money in. That is what they measure, and nothing more.</p>
                            <p class="es-flow-muted text-sm">
                                When a class fills, a waitlist can take names and tell them the moment a place frees up. Free on a class you are not charging for, and on a paid ticket it is
                                <span class="es-flow-plan es-flow-plan-pro ms-1">Pro</span>.
                            </p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Perfect for                                               -->
    <!-- ============================================================ -->
    <section id="who" class="scroll-mt-24 es-flow-edge-t py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-flow-count mb-8" data-reveal aria-hidden="true"><span>08</span></div>
                <h2 class="es-balance es-flow-ink mb-4 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    Perfect for all types of <span class="es-flow-accent">fitness professionals</span>
                </h2>
                <p class="es-flow-muted text-lg sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    One mat in a hall or forty in a studio, a week is still a sequence.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <x-sub-audience-card
                    name="Yoga Teachers"
                    description="Flows, workshops and retreats on one link. Sell a ten-class card that covers every class you teach, and let it expire when you say."
                    icon-color="emerald"
                    blog-slug="for-yoga-teachers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Personal Trainers"
                    description="Publish bookable appointment types with your real weekly hours, buffers and notice period, and let clients pick a slot themselves."
                    icon-color="teal"
                    blog-slug="for-personal-trainers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Pilates Instructors"
                    description="Mat and reformer on separate strands of one timetable, with a per-date cap so a six-apparatus studio never oversells a Tuesday."
                    icon-color="green"
                    blog-slug="for-pilates-instructors"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h9" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="CrossFit Coaches"
                    description="A WOD at 6:00 AM and a WOD at 6:00 PM as two recurring classes, plus a membership that is simply unlimited until it expires."
                    icon-color="amber"
                    blog-slug="for-crossfit-coaches"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Group Fitness Instructors"
                    description="Spin here, Zumba there, bootcamp in the park. Each venue is a class of its own, all on one link and one synced calendar."
                    icon-color="cyan"
                    blog-slug="for-group-fitness-instructors"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Meditation Guides"
                    description="Sound baths and mindfulness courses. A festival pass covers each session of a six-week series once, so the whole run is paid for up front."
                    icon-color="sky"
                    blog-slug="for-meditation-guides"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3a9 9 0 000 18 9 9 0 000-18zm0 5a4 4 0 100 8 4 4 0 000-8z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. Three steps                                              -->
    <!-- ============================================================ -->
    <section class="py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance es-flow-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal>
                    Three steps to a full class
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="120">
                @foreach ([
                    ['01', 'Set the week', 'Create each class once as a recurring event: the days it runs, one start time, a length, and date exceptions for the days you are closed.'],
                    ['02', 'Price the visit', 'Add a drop-in and a pass. Choose how the pass is spent, how long it lasts, what it covers, and how many it admits per class.'],
                    ['03', 'Share one link', 'Put it in your bio, embed the timetable on your site, print the QR code for the door, and write to the students who follow.'],
                ] as [$stepNum, $stepTitle, $stepBody])
                    <div class="es-flow-card p-7" data-reveal="panel">
                        <div class="es-flow-accent mb-3 font-mono text-2xl font-black">{{ $stepNum }}</div>
                        <h3 class="es-flow-ink mb-2 text-lg font-bold">{{ $stepTitle }}</h3>
                        <p class="es-flow-muted text-sm leading-relaxed">{{ $stepBody }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 11. Key features                                             -->
    <!-- ============================================================ -->
    <section class="es-flow-edge-t py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-flow-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="One class, the days it runs, and date exceptions" :url="marketing_url('/features/recurring-events')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="Drop-ins, class passes, QR check-in, zero platform fees" :url="marketing_url('/features/ticketing')" icon-color="teal">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Appointments" description="Bookable one-to-ones on your own public booking page" :url="marketing_url('/features/appointments')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Email the students who follow you, with open rates" :url="marketing_url('/features/newsletters')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Calendar Sync" description="Two-way sync with Google, Outlook and CalDAV" :url="marketing_url('/features/calendar-sync')" icon-color="blue">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-flow-link inline-flex items-center font-medium hover:underline">
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
    <section class="es-flow-edge-t py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-flow-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4" data-reveal-group="70">
                @foreach ([['/for-workshop-instructors', 'Workshop Instructors'], ['/for-dance-groups', 'Dance Groups'], ['/for-online-classes', 'Online Classes'], ['/for-community-centers', 'Community Centers']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" class="es-flow-hover es-flow-card group flex flex-col p-5 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-flow-hover-title es-flow-ink mb-3 text-sm font-semibold transition-colors">For {{ $relName }}</span>
                        <span class="es-flow-hover-arrow es-flow-muted mt-auto inline-flex items-center gap-1 text-xs font-medium transition-colors">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
            <div class="mt-8 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-flow-link inline-flex items-center font-medium hover:underline">
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
                <div class="es-flow-count mb-8" data-reveal aria-hidden="true"><span>09</span></div>
                <h2 class="es-balance es-flow-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-flow-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    Everything fitness and yoga instructors ask before they move a timetable across.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-flow-hover es-flow-card group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-flow-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-flow-accent flex-none font-mono text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-flow-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-flow-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-flow-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
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
            <div class="es-flow-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-flow-breathe absolute left-1/2 top-1/2 h-64 w-64 -translate-x-1/2 -translate-y-1/2"></div>
                </div>
                <div class="relative z-10">
                    <p class="es-flow-eyebrow mb-4">free forever</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Your classes. Your students. <span class="es-flow-lit">No middleman.</span>
                    </h2>
                    <p class="es-flow-muted mx-auto mb-10 max-w-2xl text-lg sm:text-xl">
                        Publishing the timetable is free forever, and so is selling your first 25 drop-ins a month. Passes, an unlimited count and the check-in dashboard are {{ plan_price($proMonthly) }} a month, and nothing is taken off the door.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-studio" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-400 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-300 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="es-flow-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Get Started Free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="es-flow-muted mt-6 text-sm">No credit card required</p>
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
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap es-flow-tip rounded-full border border-gray-200 px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3 dark:border-white/10">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
