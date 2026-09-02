<x-marketing-layout>
    <x-slot name="title">Library Program Calendars | Recurring Sessions</x-slot>
    <x-slot name="description">Set story time up once as a recurring program, take out the weeks the branch is closed, and give every single date its own place count. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Libraries</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Libraries",
        "description": "Set a library program up once as a recurring event, exclude the dates the branch is closed, and take free registrations with a place limit counted separately for every date.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Libraries"
        }
    }
    </script>
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Libraries",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Library Program Management Software",
        "operatingSystem": "Web",
        "description": "Set a library program up once as a recurring event, take out the dates the branch is closed, and give every date its own place count.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Free forever"
        },
        "featureList": [
            "Recurring programs by day of week, every few weeks, or the same weekday each month",
            "Date exceptions that take out closed days and add extra sessions",
            "A recurrence that ends on a date or after a set number of sessions",
            "Free registration with a place limit counted separately for every date",
            "Sub-schedules that keep children, teen, adult and senior programming apart",
            "A public request form so community groups can ask for the meeting room",
            "Newsletters you write and send to the patrons who follow you",
            "A downloadable QR code that points at your program calendar",
            "Embeddable calendar for the library website you already have",
            "Two-way Google, Outlook and CalDAV calendar sync",
            "Zero platform fees on ticket sales through your own Stripe account"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "library program calendar, library event schedule, story time scheduling, author event management, free library scheduling",
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
        "name": "How to put a library program calendar online with Event Schedule",
        "description": "Catalogue the program once and every date looks after itself.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Catalogue the program",
                "text": "Add the program once as a recurring event: the days it runs, the sub-schedule it belongs to, and an end date or a number of sessions."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Take out the closed days",
                "text": "Add date exceptions for public holidays and staff training days, and add extra dates for one-off sessions."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Open the places",
                "text": "Turn on free registration and set a place limit. The limit is counted separately for every date, so each session has its own list."
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
           For-libraries "The Catalog" styles.

           THE CONCEPT IS THE CATALOGUE CARD AND THE DATE-DUE SLIP, and
           the two together are the product argument. A catalogue card
           describes one work on one card, no matter how many times it
           is borrowed; an event record describes one program on one
           record, no matter how many Tuesdays it runs. The slip pasted
           in the back is the list of dates, one stamped line each, and
           that is exactly what a recurring event plus a per-date place
           count is: ONE record, MANY dated lists.

           So the card carries the fields (call number = sub-schedule,
           main entry = name, collation = the recurrence, tracings =
           the sub-schedules a patron can filter by) and the slip
           carries the occurrences, including the GAP where a date
           exception took a closed Tuesday out. The gap is the feature.

           FIXED PHYSICAL OBJECTS. Manila card stock is manila in a
           lit reading room and in a dark one, so .es-cat-card and
           .es-cat-slip render IDENTICALLY with .dark on and off, as
           does the oak drawer .es-cat-drawer. Nothing inside them may
           carry a dark: utility, and the shared classes that flip
           themselves (.grid-overlay, .animate-shimmer,
           .es-claim:focus-within) are pinned again below. Verified
           with the verifier's --bands flag, expecting 0 diffs.

           COLOUR. Brass and manila: the page keeps the amber family it
           was born with, pushed deeper and browner so it reads as
           book cloth and lamp brass rather than as a highlighter.
           Measured against the page grounds:
             #1d1a14 ink       15.67 on #f7f3ea
             #57503f muted      7.23 on #f7f3ea
             #8a4f0b accent     5.92 on #f7f3ea
             #efe9dc ink       15.71 on #12100b
             #a49a86 muted      6.83 on #12100b
             #f2b53c accent    10.37 on #12100b
             #f4bd52 lit       10.78 on the drawer #181309
             #241d12 card ink  12.14 on manila #e7dbc0
             #5b4b2f card mute  6.14 on manila
             #7a4a08 call no.   5.44 on manila
             #9e2b20 stamp      5.42 on manila
           NEVER reach for text-gray-500 here: 4.83 on pure white is
           only 4.4 on this warm ground.

           BLADE RULE for this block: no @supports probes with a hex
           inside, which breaks compilation of later directives.
           ============================================================== */

        /* --- Ground and ink ------------------------------------------- */
        .es-cat-page { background-color: #f7f3ea; color: #1d1a14; }
        .dark .es-cat-page { background-color: #12100b; color: #efe9dc; }
        .es-cat-ink { color: #1d1a14; }
        .dark .es-cat-ink { color: #efe9dc; }
        .es-cat-muted { color: #57503f; }
        .dark .es-cat-muted { color: #a49a86; }
        .es-cat-accent { color: #8a4f0b; }
        .dark .es-cat-accent { color: #f2b53c; }
        /* Always-lit inks, for text that sits on a fixed-dark drawer in both modes.
           These are page-local on purpose: an arbitrary Tailwind colour
           (text-[#efe9dc]) would need a rebuild to exist at all. */
        .es-cat-lit { color: #f4bd52; }
        .es-cat-band-ink { color: #efe9dc; }
        .es-cat-band-muted { color: #a49a86; }

        /* Section hairline */
        .es-cat-rule { border-color: rgba(29, 26, 20, 0.09); }
        .dark .es-cat-rule { border-color: rgba(239, 233, 220, 0.09); }

        /* Dot-nav tooltip */
        .es-cat-tip {
            border: 1px solid rgba(29, 26, 20, 0.14);
            background-color: #fffdf7;
            color: #1d1a14;
        }
        .dark .es-cat-tip {
            border-color: rgba(239, 233, 220, 0.12);
            background-color: #1d1a14;
            color: #efe9dc;
        }

        /* --- Drawer label: the section mark is a brass card holder ----- */
        .es-cat-label {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.3rem 0.8rem 0.3rem 0.55rem;
            border: 1px solid rgba(29, 26, 20, 0.22);
            border-radius: 2px;
            background-color: #efe7d6;
            color: #6b3d06;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.6);
        }
        .dark .es-cat-label {
            border-color: rgba(239, 233, 220, 0.2);
            background-color: #221b10;
            color: #f2b53c;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }
        .es-cat-label::before {
            content: "";
            width: 3px;
            align-self: stretch;
            border-radius: 1px;
            background-color: #b07a1e;
        }

        /* --- Eyebrow -------------------------------------------------- */
        .es-cat-eyebrow {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            color: #57503f;
        }
        .dark .es-cat-eyebrow { color: #a49a86; }

        /* --- Panels (the page's ordinary card) ------------------------ */
        .es-cat-panel {
            --es-ring-radius: 0.9rem;
            border: 1px solid rgba(29, 26, 20, 0.13);
            border-radius: 0.9rem;
            background-color: #fffdf7;
        }
        .dark .es-cat-panel {
            border-color: rgba(239, 233, 220, 0.12);
            background-color: rgba(239, 233, 220, 0.04);
        }

        /* --- Plan chips ---------------------------------------------- */
        .es-cat-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border: 1px solid rgba(138, 79, 11, 0.45);
            border-radius: 0.2rem;
            color: #8a4f0b;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }
        .dark .es-cat-plan { border-color: rgba(242, 181, 60, 0.45); color: #f2b53c; }
        .es-cat-plan-pro { border-color: rgba(29, 26, 20, 0.35); color: #1d1a14; }
        .dark .es-cat-plan-pro { border-color: rgba(239, 233, 220, 0.35); color: #efe9dc; }
        .es-cat-plan-enterprise { border-color: rgba(87, 80, 63, 0.5); color: #57503f; }
        .dark .es-cat-plan-enterprise { border-color: rgba(164, 154, 134, 0.45); color: #a49a86; }

        /* ==============================================================
           THE CARD. Manila card stock, ruled, punched at the foot for
           the rod that holds the drawer together. Identical in both
           colour modes, because card stock is.
           ============================================================== */
        .es-cat-card {
            position: relative;
            border: 1px solid rgba(60, 44, 18, 0.3);
            border-radius: 2px 2px 4px 4px;
            background-color: #e7dbc0;
            background-image: repeating-linear-gradient(
                to bottom,
                rgba(0, 0, 0, 0) 0,
                rgba(0, 0, 0, 0) 1.55rem,
                rgba(90, 66, 32, 0.15) 1.55rem,
                rgba(90, 66, 32, 0.15) calc(1.55rem + 1px));
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.5), 0 12px 26px -14px rgba(31, 22, 8, 0.55);
            color: #241d12;
        }
        .es-cat-card-ink { color: #241d12; }
        .es-cat-card-muted { color: #5b4b2f; }
        .es-cat-card-rule { border-top: 1px solid rgba(90, 66, 32, 0.28); }
        .es-cat-call {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.68rem;
            font-weight: 700;
            line-height: 1.25;
            letter-spacing: 0.06em;
            color: #7a4a08;
            text-transform: uppercase;
        }
        /* The rod hole at the foot of every card in the drawer. */
        .es-cat-punch {
            width: 0.62rem;
            height: 0.62rem;
            border-radius: 9999px;
            background-color: #cfc09c;
            box-shadow: inset 0 1px 2px rgba(60, 44, 18, 0.65), 0 1px 0 rgba(255, 255, 255, 0.45);
        }
        /* Tracings: the subject headings typed along the foot of a card. */
        .es-cat-tracing {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.62rem;
            letter-spacing: 0.04em;
            color: #5b4b2f;
        }
        /* The schedule's own QR code, printed small on a bookmark. */
        .es-cat-qr { shape-rendering: crispEdges; }
        .es-cat-swatch {
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 1px;
            box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.25);
        }

        /* ==============================================================
           THE DATE-DUE SLIP. One stamped line per occurrence, and a
           blank where a date exception took a Tuesday out.
           ============================================================== */
        .es-cat-slip {
            border: 1px solid rgba(90, 66, 32, 0.4);
            border-radius: 2px;
            background-color: #f3ebd7;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.55), 0 12px 26px -16px rgba(31, 22, 8, 0.5);
            color: #241d12;
        }
        .es-cat-slip-head {
            border-bottom: 1px solid rgba(90, 66, 32, 0.4);
            background-color: #e9dfc4;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.28em;
            text-transform: uppercase;
            color: #5b4b2f;
        }
        .es-cat-slip-row { border-bottom: 1px dotted rgba(90, 66, 32, 0.4); }
        .es-cat-slip-row:last-child { border-bottom: 0; }
        .es-cat-slip-foot {
            border-top: 1px solid rgba(90, 66, 32, 0.4);
            background-color: #e9dfc4;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.28em;
            text-transform: uppercase;
            color: #5b4b2f;
        }
        .es-cat-stamp {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 5.1rem;
            padding: 0.2rem 0.4rem;
            border: 1.5px solid rgba(158, 43, 32, 0.6);
            border-radius: 2px;
            color: #9e2b20;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            transform: rotate(var(--rot, -1.5deg));
        }
        /* A date that is NOT in the run. Deliberately hollow, not dimmer. */
        .es-cat-stamp-void {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 5.1rem;
            padding: 0.2rem 0.4rem;
            border: 1.5px dashed rgba(90, 66, 32, 0.5);
            border-radius: 2px;
            color: #5b4b2f;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        .es-cat-slip-count {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            color: #7a4a08;
        }

        /* ==============================================================
           THE DRAWER. Oak front, brass label holder, one lamp above it.
           Fixed in both colour modes.
           ============================================================== */
        .es-cat-drawer {
            background-color: #181309;
            background-image:
                radial-gradient(120% 90% at 50% -10%, rgba(176, 122, 30, 0.16) 0%, rgba(24, 19, 9, 0) 60%),
                repeating-linear-gradient(96deg,
                    rgba(255, 255, 255, 0.014) 0 3px,
                    rgba(0, 0, 0, 0.05) 3px 7px),
                linear-gradient(175deg, #21190d 0%, #171106 60%, #100c05 100%);
            box-shadow: inset 0 0 100px rgba(0, 0, 0, 0.6), inset 0 1px 0 rgba(239, 233, 220, 0.06);
        }
        /* Pin the shared classes that would otherwise flip inside a fixed object. */
        .es-cat-drawer .grid-overlay {
            background-image:
                linear-gradient(rgba(239, 233, 220, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(239, 233, 220, 0.05) 1px, transparent 1px);
        }
        .es-cat-drawer .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.18), transparent);
            background-size: 200% 100%;
        }
        .es-cat-drawer .es-claim:focus-within {
            border-color: rgba(244, 189, 82, 0.8);
            box-shadow: 0 0 0 4px rgba(244, 189, 82, 0.22);
        }
        .es-cat-drawer .es-cat-panel {
            border-color: rgba(239, 233, 220, 0.13);
            background-color: rgba(239, 233, 220, 0.05);
        }
        .es-cat-drawer .es-cat-eyebrow { color: #f4bd52; }
        .es-cat-drawer .es-cat-label {
            border-color: rgba(239, 233, 220, 0.2);
            background-color: #221b10;
            color: #f4bd52;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }
        .es-cat-drawer .es-cat-plan { border-color: rgba(244, 189, 82, 0.45); color: #f4bd52; }
        /* The lamp over the drawer: static, so it cannot differ by mode. */
        .es-cat-lampglow {
            position: absolute;
            inset: 0;
            background: radial-gradient(70% 46% at 50% 0%, rgba(244, 189, 82, 0.15), rgba(244, 189, 82, 0) 70%);
        }
        /* Brass plate, used for the drawer's own label and the card guide. */
        .es-cat-brass {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(120, 84, 22, 0.8);
            border-radius: 2px;
            background-color: #d3a344;
            background-image: linear-gradient(180deg, #f0d494 0%, #e0b658 45%, #c9963a 100%);
            color: #241d12;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.66rem;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
        }
        .es-cat-brass::after {
            content: "";
            position: absolute;
            top: 0;
            bottom: 0;
            width: 40%;
            background-image: linear-gradient(100deg, rgba(255, 255, 255, 0) 0%, rgba(255, 252, 235, 0.55) 50%, rgba(255, 255, 255, 0) 100%);
            animation: es-cat-sheen 7s ease-in-out infinite;
        }
        @keyframes es-cat-sheen {
            0%, 100% { transform: translateX(-140%); }
            55% { transform: translateX(320%); }
        }

        /* --- The shelf list table ------------------------------------ */
        .es-cat-table { border-collapse: collapse; width: 100%; }
        .es-cat-table th,
        .es-cat-table td {
            border-bottom: 1px solid rgba(29, 26, 20, 0.1);
            padding: 0.7rem 0.6rem;
            text-align: left;
            vertical-align: middle;
        }
        .dark .es-cat-table th,
        .dark .es-cat-table td { border-bottom-color: rgba(239, 233, 220, 0.1); }
        .es-cat-table thead th {
            border-bottom-width: 2px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #57503f;
        }
        .dark .es-cat-table thead th { color: #a49a86; }
        .es-cat-table tbody tr:last-child th,
        .es-cat-table tbody tr:last-child td { border-bottom: 0; }

        /* --- Buttons and links --------------------------------------- */
        .es-cat-btn {
            background-color: #8a4f0b;
            box-shadow: 0 18px 34px -16px rgba(138, 79, 11, 0.6);
        }
        .es-cat-btn:hover { background-color: #6f3f07; box-shadow: 0 22px 42px -16px rgba(138, 79, 11, 0.7); }
        .dark .es-cat-btn { background-color: #f2b53c; color: #1b1408; }
        .dark .es-cat-btn:hover { background-color: #f7c65e; }
        .es-cat-drawer .es-cat-btn { background-color: #f2b53c; color: #1b1408; }
        .es-cat-drawer .es-cat-btn:hover { background-color: #f7c65e; }

        .es-cat-link { color: #8a4f0b; }
        .es-cat-link:hover { color: #1d1a14; }
        .dark .es-cat-link { color: #f2b53c; }
        .dark .es-cat-link:hover { color: #efe9dc; }
        .es-cat-drawer .es-cat-link { color: #f4bd52; }
        .es-cat-drawer .es-cat-link:hover { color: #efe9dc; }

        /* --- Hover states on cards that are links -------------------- */
        .es-cat-hover:hover { border-color: rgba(138, 79, 11, 0.5); }
        .dark .es-cat-hover:hover { border-color: rgba(242, 181, 60, 0.5); }
        .es-cat-hover:hover .es-cat-hover-title,
        .es-cat-hover:hover .es-cat-hover-arrow { color: #8a4f0b; }
        .dark .es-cat-hover:hover .es-cat-hover-title,
        .dark .es-cat-hover:hover .es-cat-hover-arrow { color: #f2b53c; }

        /* --- Chips (hero marquee) ------------------------------------ */
        .es-cat-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.32rem 0.8rem;
            border: 1px solid rgba(29, 26, 20, 0.16);
            border-radius: 9999px;
            background-color: rgba(255, 253, 247, 0.75);
            color: #57503f;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .dark .es-cat-chip {
            border-color: rgba(239, 233, 220, 0.16);
            background-color: rgba(239, 233, 220, 0.05);
            color: #a49a86;
        }

        /* --- Shelf of spines, used as a section rule ------------------ */
        .es-cat-shelf {
            display: flex;
            align-items: flex-end;
            justify-content: center;
            gap: 2px;
            height: 46px;
            padding: 0 1rem;
            border-bottom: 3px solid #6b4a22;
            overflow: hidden;
        }
        .dark .es-cat-shelf { border-bottom-color: #2c1f0e; }
        .es-cat-spine {
            width: 7px;
            border-top-left-radius: 2px;
            border-top-right-radius: 2px;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, 0.18);
        }

        /* --- Step numerals ------------------------------------------- */
        .es-cat-num {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 1.6rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            color: #8a4f0b;
        }
        .dark .es-cat-num { color: #f2b53c; }

        /* --- Shared-system recolours (brand blue by default) --------- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(138, 79, 11, 0.13), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(242, 181, 60, 0.12), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(138, 79, 11, 0.65); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(242, 181, 60, 0.65); }
        .es-dot.is-active .es-dot-pip { background: #8a4f0b; }
        .dark .es-dot.is-active .es-dot-pip { background: #f2b53c; }

        /* --- Focus rings. No border-radius here: setting it would
               reshape the element itself on focus. ------------------- */
        #es-cat-page a:focus-visible,
        #es-cat-page summary:focus-visible,
        #es-cat-page input:focus-visible,
        #es-cat-page button:focus-visible {
            outline: 2px solid #8a4f0b;
            outline-offset: 3px;
        }
        .dark #es-cat-page a:focus-visible,
        .dark #es-cat-page summary:focus-visible,
        .dark #es-cat-page input:focus-visible,
        .dark #es-cat-page button:focus-visible {
            outline-color: #f2b53c;
        }
        .es-cat-drawer a:focus-visible,
        .es-cat-drawer summary:focus-visible,
        .es-cat-drawer input:focus-visible,
        .es-cat-drawer button:focus-visible {
            outline-color: #f4bd52 !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-cat-brass::after { animation: none; opacity: 0.35; }
        }
    </style>

    @php
        // The date-due slip: one line per occurrence of ONE recurring program.
        // [stamp, weekday, places, remaining, kind, rotation]
        $slip = [
            ['Sep 1',  'Tue', '24 places', 'full',    'stamped', '-2deg'],
            ['Sep 8',  'Tue', '24 places', '3 left',  'stamped', '1.5deg'],
            ['Sep 15', 'Tue', 'branch closed', 'no session', 'void', '0deg'],
            ['Sep 22', 'Tue', '24 places', '11 left', 'stamped', '-1deg'],
            ['Sep 29', 'Tue', '24 places', '19 left', 'stamped', '2deg'],
            ['Oct 3',  'Sat', '24 places', '24 left', 'extra',   '-1.5deg'],
            ['Oct 6',  'Tue', '24 places', '24 left', 'stamped', '1deg'],
        ];

        // The shelf list: one week of a branch, as a record.
        // [program, sub-schedule, colour, when, places, sign-up, plan]
        $shelfList = [
            ['Toddler Story Time', 'Children', '#8a4f0b', 'Tuesdays, 10:00', '24', 'Free registration', ''],
            ['Lego Club', 'Children', '#8a4f0b', 'Wednesdays, 16:00', '20', 'Free registration', ''],
            ['Teen Coding Club', 'Teens', '#2f5d50', 'Wednesdays, 16:30', '12', 'Free registration', ''],
            ['Book Club', 'Adults', '#1f3a5f', 'First Thursday, 19:00', '15', 'Free registration', ''],
            ['Tech Help Drop-in', 'Seniors', '#6b4226', 'Fridays, 13:00', 'No limit', 'Just turn up', ''],
            ['Author Reading: Jane Ahmad', 'Adults', '#1f3a5f', 'Sat Oct 3, 19:00', '90', 'Tickets, $6', ''],
            ['Local History Talk', 'Adults', '#1f3a5f', 'Not announced yet', '60', 'Draft', ''],
        ];

        // Book cloth on the shelf rule. No purples: buckram browns, greens and blues.
        $spinePalette = ['#8a4f0b', '#6b4226', '#b07a1e', '#1f3a5f', '#2f5d50', '#4a5058', '#9e2b20', '#7a4a08', '#3c3226', '#c9a45a'];
        $spineHeights = [34, 42, 28, 44, 38, 30, 45, 36, 40, 26, 43, 32];

        $faqs = [
            [
                'q' => 'Is Event Schedule free for libraries?',
                'a' => 'Yes. Publishing your program calendar, setting programs up as recurring, organising them into sub-schedules, taking free registrations with a place limit, embedding the calendar on your library site, syncing two ways with Google, Outlook or CalDAV, and the built-in analytics are all free forever. Newsletters are free too, with 10 emails a month counted per recipient; Pro raises that to 100 and Enterprise to 1,000. Selling tickets for a paid program is free as well, up to 25 paid tickets a month, and Pro at '.plan_price($proMonthly).' a month lifts that ceiling and adds the live check-in count at the door. Event Schedule charges zero platform fees on ticket sales, on every plan.',
            ],
            [
                'q' => 'Can I manage story times, author events, and workshops together?',
                'a' => 'Yes. Sub-schedules organise one calendar into strands, so children\'s story times, teen clubs, adult talks and community meetings each sit on their own strand with their own colour, and a patron can filter the calendar down to just the strand they came for. To be exact about what a sub-schedule is: it organises and colour-codes, and it cannot hide anything. A program you are not ready to publish is a Draft instead.',
            ],
            [
                'q' => 'How do patrons find out about library programs?',
                'a' => 'Four ways, and none of them is an algorithm. Patrons follow your schedule and you email them a newsletter when you have something to say. Your calendar embeds on the library website you already have. Every schedule has a downloadable QR code you can print on a bookmark, a poster or a shelf label. And each date has an iCal download so it lands in the patron\'s own calendar. Being straight about the newsletter: nothing is sent automatically when you add a program, because you decide when to write and what goes in it.',
            ],
            [
                'q' => 'Can patrons register for programs?',
                'a' => 'Yes, on the free plan. Turn on registration and set a place limit, and the limit is counted separately for every date, so this Tuesday filling up does not close next Tuesday. Patrons get a confirmation email with their own link. For a paid program, connect Stripe and sell named ticket types on the free plan too, up to 25 paid tickets a month, with zero platform fees past Stripe\'s own processing. Pro at '.plan_price($proMonthly).' a month takes the 25 off.',
            ],
            [
                'q' => 'What happens on the weeks the branch is closed?',
                'a' => 'You add a date exception and that date comes out of the run, so patrons simply do not see a session that day. Exceptions work the other way too: add a single extra date for a half-term session without setting up a second program. A recurrence can also be told to stop, either on a date or after a set number of sessions, so a school-year program is not still listed in August.',
            ],
            [
                'q' => 'Can community groups ask to use the meeting room?',
                'a' => 'Yes. Turn on event requests and your schedule gets a public form. A submission arrives as a pending request rather than a published event, you approve or decline it, and you can keep a list of schedules whose submissions are approved without review. Requests are free, and so is the approval queue.',
            ],
            [
                'q' => 'Can the whole staff have logins?',
                'a' => 'Not on the free plan, which is one team member. Multiple team members, up to five, are an Enterprise feature at '.plan_price($entMonthly).' a month, along with custom domains. Plenty of branches run the whole calendar from one shared account, so it is worth knowing which you need before you pay for it.',
            ],
        ];

        $dotSections = [
            ['top', 'The catalogue card'],
            ['card', 'One card, one program'],
            ['slip', 'The date-due slip'],
            ['week', 'The shelf list'],
            ['room', 'The meeting room'],
            ['reach', 'Reaching patrons'],
            ['rest', 'Everything else'],
            ['who', 'Every branch'],
            ['steps', 'Three steps'],
            ['faq', 'Questions'],
            ['claim', 'Check it out'],
        ];
    @endphp

    <div id="es-cat-page" class="es-cat-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: one card, and the dates it runs                     -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(176, 122, 30, 0.26), rgba(176, 122, 30, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(138, 79, 11, 0.18), rgba(138, 79, 11, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 mb-8">
                        <span class="es-cat-label">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            For public libraries
                        </span>
                    </div>

                    <h1 class="es-balance es-cat-ink mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">Story time is not one Tuesday.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">It is <span class="es-cat-accent" data-count-to="38">38</span> of them.</span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-cat-muted mb-10 max-w-xl text-lg sm:text-xl">
                        Catalogue the program once: the days it runs, the dates the branch is shut, and how many places there are. Every date then keeps its own list, and the count starts again each week.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row sm:flex-wrap">
                        <a href="#slip" class="glass group inline-flex shrink-0 items-center justify-center gap-2 whitespace-nowrap rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            See the date-due slip
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="es-cat-btn group inline-flex shrink-0 items-center justify-center gap-2 whitespace-nowrap rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Create your calendar
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- The catalogue card. The same object in both colour modes. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-cat-card mx-auto max-w-md px-6 pb-5 pt-6 sm:px-8">
                        <div class="flex items-start justify-between gap-4">
                            <div class="es-cat-call">
                                027.4<br>STO<br>2026
                            </div>
                            <div class="es-cat-card-muted text-right text-[0.6rem] font-semibold uppercase tracking-[0.2em]">
                                Recurring program
                            </div>
                        </div>

                        <h2 class="es-cat-card-ink mt-4 font-serif text-2xl font-bold leading-tight">Toddler Story Time</h2>
                        <p class="es-cat-card-muted mt-1 text-sm">Tuesdays, 10:00 &middot; Children's Room &middot; ages 0 to 3</p>

                        <dl class="es-cat-card-rule mt-4 space-y-1.5 pt-4 text-sm">
                            <div class="flex items-baseline justify-between gap-4">
                                <dt class="es-cat-card-muted">Runs</dt>
                                <dd class="es-cat-card-ink font-medium">Tuesdays, from Sep 1</dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-4">
                                <dt class="es-cat-card-muted">Ends</dt>
                                <dd class="es-cat-card-ink font-medium">after 38 sessions</dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-4">
                                <dt class="es-cat-card-muted">Places</dt>
                                <dd class="es-cat-card-ink font-medium">24, per date</dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-4">
                                <dt class="es-cat-card-muted">Not held</dt>
                                <dd class="es-cat-card-ink font-medium">6 closed days</dd>
                            </div>
                        </dl>

                        <div class="es-cat-card-rule mt-4 pt-3">
                            <div class="es-cat-tracing flex flex-wrap items-center gap-x-4 gap-y-1">
                                <span class="inline-flex items-center gap-1.5"><span class="es-cat-swatch" style="background-color: #8a4f0b;"></span>Children &middot; this card</span>
                                <span class="inline-flex items-center gap-1.5"><span class="es-cat-swatch" style="background-color: #2f5d50;"></span>Teens</span>
                                <span class="inline-flex items-center gap-1.5"><span class="es-cat-swatch" style="background-color: #1f3a5f;"></span>Adults</span>
                            </div>
                        </div>

                        <div class="mt-5 flex justify-center">
                            <span class="es-cat-punch" aria-hidden="true"></span>
                        </div>
                    </div>
                    <p class="es-cat-muted mx-auto mt-4 max-w-md text-center text-xs">
                        One record, filed on one sub-schedule. The others are the strands a patron can filter the calendar down to.
                    </p>
                </div>
            </div>

            <!-- Program-type marquee -->
            <div class="es-fade-up es-d-4 mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach (['Story Time', 'Book Clubs', 'Author Readings', 'Maker Space', 'Film Screenings', 'Local History', 'Tech Help', 'ESL Conversation', 'Summer Reading', 'Class Visits'] as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-cat-chip">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The card is the record                                    -->
    <!-- ============================================================ -->
    <section id="card" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="mb-6" data-reveal><span class="es-cat-label" aria-hidden="true">Drawer 02 &middot; 027.4</span></div>
                <p class="es-cat-eyebrow mb-4" data-reveal style="--reveal-delay: 0.05s;">One card, one program</p>
                <h2 class="es-balance es-cat-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    A catalogue card never got <span class="es-cat-accent">retyped</span> for every loan.
                </h2>
                <p class="es-cat-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Neither should a program. Everything a librarian would type on a card has a field on the record, and every line below is on the free plan.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="90">
                <div class="es-cat-panel p-7" data-reveal="panel">
                    <p class="es-cat-eyebrow mb-3">Call number</p>
                    <h3 class="es-cat-ink mb-2 text-lg font-bold">The sub-schedule</h3>
                    <p class="es-cat-muted text-sm">Children, Teens, Adults, Seniors, Community. Each sub-schedule has a name and a colour, and a patron can filter the calendar down to one of them.</p>
                </div>
                <div class="es-cat-panel p-7" data-reveal="panel">
                    <p class="es-cat-eyebrow mb-3">Main entry</p>
                    <h3 class="es-cat-ink mb-2 text-lg font-bold">The program itself</h3>
                    <p class="es-cat-muted text-sm">Name, description, an image, the branch address and a map drawn from it. Written once and it stands for every session.</p>
                </div>
                <div class="es-cat-panel p-7" data-reveal="panel">
                    <p class="es-cat-eyebrow mb-3">Collation</p>
                    <h3 class="es-cat-ink mb-2 text-lg font-bold">The recurrence</h3>
                    <p class="es-cat-muted text-sm">Weekly on chosen days, every other week, or the same weekday each month. First Thursday book club is one setting, not twelve events.</p>
                </div>
                <div class="es-cat-panel p-7" data-reveal="panel">
                    <p class="es-cat-eyebrow mb-3">Contents note</p>
                    <h3 class="es-cat-ink mb-2 text-lg font-bold">The running order</h3>
                    <p class="es-cat-muted text-sm">A workshop that is really four parts can list them, so patrons see the shape of the afternoon before they commit to it.</p>
                </div>
                <div class="es-cat-panel p-7" data-reveal="panel">
                    <p class="es-cat-eyebrow mb-3">Tracings</p>
                    <h3 class="es-cat-ink mb-2 text-lg font-bold">Where else it appears</h3>
                    <p class="es-cat-muted text-sm">Attach the visiting author's own schedule and the program appears on their calendar as well as yours, the way one card generated a subject card and an author card.</p>
                </div>
                <div class="es-cat-panel p-7" data-reveal="panel">
                    <p class="es-cat-eyebrow mb-3">Not yet catalogued</p>
                    <h3 class="es-cat-ink mb-2 text-lg font-bold">Drafts</h3>
                    <p class="es-cat-muted text-sm">A program you are still arranging sits on the calendar as a Draft, visible to you and never published until you say so.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Shelf rule: book cloth on a shelf                            -->
    <!-- ============================================================ -->
    <div class="pb-4" aria-hidden="true">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="es-cat-shelf">
                @for ($si = 0; $si < 112; $si++)
                    <span class="es-cat-spine" style="height: {{ $spineHeights[$si % count($spineHeights)] }}px; background-color: {{ $spinePalette[$si % count($spinePalette)] }};"></span>
                @endfor
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- 3. The date-due slip (fixed-dark drawer)                     -->
    <!-- ============================================================ -->
    <section id="slip" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-cat-drawer noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-cat-lampglow"></div>
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="mb-6" data-reveal><span class="es-cat-label" aria-hidden="true">Drawer 03 &middot; Date due</span></div>
                    <p class="es-cat-eyebrow mb-4" data-reveal style="--reveal-delay: 0.05s;">The date-due slip</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight es-cat-band-ink md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        One record. <span class="es-cat-lit">A separate list for every date.</span>
                    </h2>
                    <p class="mt-5 text-lg es-cat-band-muted" data-reveal style="--reveal-delay: 0.15s;">
                        A place limit belongs to the program, but it is counted per date. Sep 8 filling up has nothing to do with Sep 22, and neither of them needs a second event.
                    </p>
                </div>

                <div class="grid items-start gap-8 lg:grid-cols-3">
                    <div data-reveal="panel">
                        <div class="es-cat-slip mx-auto max-w-sm">
                            <div class="es-cat-slip-head flex items-center justify-between px-4 py-2.5">
                                <span>Date due</span>
                                <span>Places</span>
                            </div>
                            <ul class="px-4 py-2">
                                @foreach ($slip as $rowIndex => [$stampDate, $stampDay, $stampPlaces, $stampLeft, $stampKind, $stampRot])
                                    <li class="es-cat-slip-row es-ai-field flex items-center justify-between gap-3 py-2.5" style="--i: {{ $rowIndex }};">
                                        <span class="flex min-w-0 items-center gap-2">
                                            @if ($stampKind === 'void')
                                                <span class="es-cat-stamp-void">{{ $stampDate }}</span>
                                            @else
                                                <span class="es-cat-stamp" style="--rot: {{ $stampRot }};">{{ $stampDate }}</span>
                                            @endif
                                            <span class="es-cat-card-muted text-[0.6rem] font-semibold uppercase tracking-[0.14em]">
                                                {{ $stampDay }}@if ($stampKind === 'extra') &middot; added @endif
                                            </span>
                                        </span>
                                        <span class="text-right">
                                            <span class="es-cat-slip-count block">{{ $stampLeft }}</span>
                                            <span class="es-cat-card-muted block text-[0.6rem] uppercase tracking-[0.12em]">{{ $stampPlaces }}</span>
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="es-cat-slip-foot px-4 py-2 text-center">
                                6 of 38 sessions
                            </div>
                        </div>
                        <p class="mx-auto mt-4 max-w-sm text-center text-xs es-cat-band-muted">
                            Sep 15 has no stamp. That Tuesday the branch was closed, so a date exception took it out of the run and patrons never see a session there.
                        </p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 lg:col-span-2" data-reveal-group="100">
                        <div class="es-cat-panel p-6" data-reveal="panel">
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <h3 class="text-lg font-bold es-cat-band-ink">The days it runs</h3>
                                <span class="es-cat-plan">Free</span>
                            </div>
                            <p class="text-sm es-cat-band-muted">Tick the days of the week and set the time. Or every other week for a fortnightly club, or the same weekday each month for a first-Thursday book group.</p>
                        </div>
                        <div class="es-cat-panel p-6" data-reveal="panel">
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <h3 class="text-lg font-bold es-cat-band-ink">The days you are shut</h3>
                                <span class="es-cat-plan">Free</span>
                            </div>
                            <p class="text-sm es-cat-band-muted">Date exceptions take single dates out for a public holiday or a staff training day, and put single dates in for a half-term extra without a second program.</p>
                        </div>
                        <div class="es-cat-panel p-6" data-reveal="panel">
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <h3 class="text-lg font-bold es-cat-band-ink">The end of the run</h3>
                                <span class="es-cat-plan">Free</span>
                            </div>
                            <p class="text-sm es-cat-band-muted">A recurrence can stop on a date or after a set number of sessions. A school-year program that closes after 38 is not still on the calendar in August.</p>
                        </div>
                        <div class="es-cat-panel p-6" data-reveal="panel">
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <h3 class="text-lg font-bold es-cat-band-ink">The places</h3>
                                <span class="es-cat-plan">Free</span>
                            </div>
                            <p class="text-sm es-cat-band-muted">Registration with a limit, counted per date, and the remaining count is shown to the patron as they sign up. Leave the limit off for a drop-in.</p>
                        </div>
                    </div>
                </div>

                <p class="mt-10 text-center es-cat-band-muted" data-reveal>
                    Change the time once and every remaining date follows.
                    <a href="{{ marketing_url('/features/recurring-events') }}" class="es-cat-link inline-flex items-center gap-1 font-semibold transition-all hover:gap-2">
                        How recurring events work
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. The shelf list: a real table                              -->
    <!-- ============================================================ -->
    <section id="week" class="scroll-mt-24 border-y es-cat-rule py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="mb-6" data-reveal><span class="es-cat-label" aria-hidden="true">Drawer 04 &middot; Shelf list</span></div>
                <p class="es-cat-eyebrow mb-4" data-reveal style="--reveal-delay: 0.05s;">The shelf list</p>
                <h2 class="es-balance es-cat-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    A branch week, <span class="es-cat-accent">as a record.</span>
                </h2>
                <p class="es-cat-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Seven programs, four sub-schedules, one link. Six of these rows cost nothing.
                </p>
            </div>

            <div class="es-cat-panel overflow-hidden p-2 sm:p-6" data-reveal="panel">
                <div class="overflow-x-auto">
                    <table class="es-cat-table">
                        <caption class="sr-only">One week of library programs, with the sub-schedule each belongs to, when it runs, how many places it has, and how patrons sign up</caption>
                        <thead>
                            <tr>
                                <th scope="col">Program</th>
                                <th scope="col" class="hidden sm:table-cell">Sub-schedule</th>
                                <th scope="col">When</th>
                                <th scope="col">Places</th>
                                <th scope="col">Sign-up</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($shelfList as [$rowName, $rowStrand, $rowColour, $rowWhen, $rowPlaces, $rowSignup, $rowPlan])
                                <tr>
                                    <th scope="row" class="es-cat-ink text-sm font-bold">
                                        {{ $rowName }}
                                        <span class="es-cat-muted block text-[0.7rem] font-normal sm:hidden">{{ $rowStrand }}</span>
                                    </th>
                                    <td class="es-cat-muted hidden text-sm sm:table-cell">
                                        <span class="inline-flex items-center gap-2">
                                            <span class="es-cat-swatch" style="background-color: {{ $rowColour }};" aria-hidden="true"></span>
                                            {{ $rowStrand }}
                                        </span>
                                    </td>
                                    <td class="es-cat-muted font-mono text-xs">{{ $rowWhen }}</td>
                                    <td class="es-cat-muted font-mono text-xs">{{ $rowPlaces }}</td>
                                    <td class="text-sm">
                                        <span class="inline-flex flex-wrap items-center gap-2">
                                            <span class="es-cat-ink font-medium">{{ $rowSignup }}</span>
                                            @if ($rowPlan === 'Pro')
                                                <span class="es-cat-plan es-cat-plan-pro">Pro</span>
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-8 grid gap-4 md:grid-cols-2" data-reveal-group="90">
                <p class="es-cat-muted text-sm" data-reveal>
                    The Local History Talk is a Draft: on your calendar, not on the public one, until the speaker confirms. Sub-schedules colour-code and organise, so a Draft is how a program hides, not a strand.
                </p>
                <p class="es-cat-muted text-sm" data-reveal>
                    The author reading charges $6, and the free plan still sells it: 25 paid tickets a month, no platform fee. Pro at {{ plan_price($proMonthly) }} a month is what you buy when 90 seats will not fit inside 25. Everything free stays free: the free registration on the other four is not a trial.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. The meeting room: requests                                -->
    <!-- ============================================================ -->
    <section id="room" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div>
                    <div class="mb-6" data-reveal><span class="es-cat-label" aria-hidden="true">Drawer 05 &middot; Requests</span></div>
                    <p class="es-cat-eyebrow mb-4" data-reveal style="--reveal-delay: 0.05s;">The meeting room</p>
                    <h2 class="es-balance es-cat-ink mb-5 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                        Let the knitting group <span class="es-cat-accent">fill in a slip.</span>
                    </h2>
                    <p class="es-cat-muted mb-6 text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        Turn on event requests and your schedule gets a public form. What arrives is a pending request, not a published event, so the community can ask for the room without anyone getting posting rights to your calendar.
                    </p>
                    <ul class="es-cat-muted space-y-3" data-reveal-group="70">
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-cat-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Requests land in an approval queue. You accept, decline, or edit the details first.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-cat-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Community groups that run their own schedule can be listed as pre-approved, so the regulars stop waiting on you.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-cat-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Event Schedule emails you when requests are waiting, so the queue is not something you have to remember to open.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-cat-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Requests and the queue are free. Extra questions on the form are a Pro plan feature.</span>
                        </li>
                    </ul>
                    <p class="es-cat-muted mt-6 text-sm">
                        One thing this is not: room booking. Event Schedule does not hold an inventory of rooms and will not warn you that two groups asked for the same afternoon. The queue is where you catch that, with your own eyes.
                    </p>
                </div>

                <div data-reveal="panel">
                    <div class="es-cat-card mx-auto max-w-sm px-6 pb-5 pt-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="es-cat-call">
                                REQ<br>PEND<br>001
                            </div>
                            <div class="es-cat-card-muted text-right text-[0.6rem] font-semibold uppercase tracking-[0.2em]">
                                Awaiting approval
                            </div>
                        </div>
                        <h3 class="es-cat-card-ink mt-4 font-serif text-xl font-bold leading-tight">Riverside Knitting Circle</h3>
                        <p class="es-cat-card-muted mt-1 text-sm">Thursdays, 18:30 &middot; Meeting Room B</p>
                        <dl class="es-cat-card-rule mt-4 space-y-1.5 pt-4 text-sm">
                            <div class="flex items-baseline justify-between gap-4">
                                <dt class="es-cat-card-muted">Submitted by</dt>
                                <dd class="es-cat-card-ink font-medium">A patron, via your form</dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-4">
                                <dt class="es-cat-card-muted">Public now</dt>
                                <dd class="es-cat-card-ink font-medium">No</dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-4">
                                <dt class="es-cat-card-muted">Needs</dt>
                                <dd class="es-cat-card-ink font-medium">One click from you</dd>
                            </div>
                        </dl>
                        <div class="es-cat-card-rule mt-4 flex items-center justify-between gap-3 pt-3">
                            <span class="es-cat-tracing">Decline</span>
                            <span class="es-cat-brass px-3 py-1.5">Approve</span>
                        </div>
                        <div class="mt-5 flex justify-center">
                            <span class="es-cat-punch" aria-hidden="true"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Reaching patrons (fixed-dark drawer)                      -->
    <!-- ============================================================ -->
    <section id="reach" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-cat-drawer noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-cat-lampglow"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="mb-6" data-reveal><span class="es-cat-label" aria-hidden="true">Drawer 06 &middot; Circulation</span></div>
                    <p class="es-cat-eyebrow mb-4" data-reveal style="--reveal-delay: 0.05s;">Reaching patrons</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight es-cat-band-ink md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        The poster in the foyer only reaches <span class="es-cat-lit">people already inside.</span>
                    </h2>
                    <p class="mt-5 text-lg es-cat-band-muted" data-reveal style="--reveal-delay: 0.15s;">
                        Four ways out of the building, none of them an algorithm deciding who deserves to hear about Tuesday.
                    </p>
                </div>

                <div class="grid items-start gap-8 lg:grid-cols-3">
                    <div class="grid gap-4 sm:grid-cols-2 lg:col-span-2" data-reveal-group="100">
                        <div class="es-cat-panel p-6" data-reveal="panel">
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <h3 class="text-lg font-bold es-cat-band-ink">They follow you</h3>
                                <span class="es-cat-plan">Free</span>
                            </div>
                            <p class="text-sm es-cat-band-muted">A patron follows your schedule and you have their name and email. That list is yours, it is visible only to your side of the calendar, and nobody rents it back to you.</p>
                        </div>
                        <div class="es-cat-panel p-6" data-reveal="panel">
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <h3 class="text-lg font-bold es-cat-band-ink">You write to them</h3>
                                <span class="es-cat-plan">Free</span>
                            </div>
                            <p class="text-sm es-cat-band-muted">Newsletters are yours to compose and send: the autumn program, a cancelled session, a new author date. Nothing goes out on its own, which is the point. 10 emails a month free, 100 on Pro, 1,000 on Enterprise, counted per recipient.</p>
                        </div>
                        <div class="es-cat-panel p-6" data-reveal="panel">
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <h3 class="text-lg font-bold es-cat-band-ink">On the library website</h3>
                                <span class="es-cat-plan">Free</span>
                            </div>
                            <p class="text-sm es-cat-band-muted">Embed the calendar in the site you already have, and sync two ways with Google, Outlook or CalDAV. Worth knowing: a recurring program crosses to those calendars as a single entry, so it is the iCal feed, not the sync, that unrolls every Tuesday.</p>
                        </div>
                        <div class="es-cat-panel p-6" data-reveal="panel">
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <h3 class="text-lg font-bold es-cat-band-ink">Into their own calendar</h3>
                                <span class="es-cat-plan">Free</span>
                            </div>
                            <p class="text-sm es-cat-band-muted">Every date has an iCal download, so a parent puts Tuesday 10:00 in the phone they actually check.</p>
                        </div>
                    </div>

                    <!-- The bookmark: manila stock again, with the schedule's QR code. -->
                    <div data-reveal="panel">
                        <div class="es-cat-card mx-auto w-full max-w-[15rem] px-5 pb-5 pt-6 text-center">
                            <div class="es-cat-call">GUIDE<br>QR</div>
                            <p class="es-cat-card-ink mt-3 font-serif text-base font-bold leading-tight">What's on at the branch</p>
                            <div class="mt-4 flex justify-center">
                                <svg aria-hidden="true" class="es-cat-qr h-28 w-28" viewBox="0 0 29 29" fill="#241d12">
                                    <rect x="0" y="0" width="9" height="9" fill="none" stroke="#241d12" stroke-width="2"></rect>
                                    <rect x="3" y="3" width="3" height="3"></rect>
                                    <rect x="20" y="0" width="9" height="9" fill="none" stroke="#241d12" stroke-width="2"></rect>
                                    <rect x="23" y="3" width="3" height="3"></rect>
                                    <rect x="0" y="20" width="9" height="9" fill="none" stroke="#241d12" stroke-width="2"></rect>
                                    <rect x="3" y="23" width="3" height="3"></rect>
                                    @foreach ([[11,1],[13,1],[11,3],[15,3],[12,5],[14,5],[11,7],[13,7],[1,11],[3,11],[5,11],[7,11],[11,11],[13,11],[16,11],[19,11],[22,11],[25,11],[2,13],[6,13],[12,13],[15,13],[18,13],[21,13],[24,13],[27,13],[1,15],[4,15],[7,15],[11,15],[14,15],[17,15],[20,15],[23,15],[26,15],[3,17],[5,17],[12,17],[16,17],[19,17],[22,17],[25,17],[11,19],[13,19],[15,19],[18,19],[21,19],[24,19],[27,19],[12,21],[14,21],[17,21],[20,21],[23,21],[26,21],[11,23],[15,23],[18,23],[22,23],[25,23],[12,25],[14,25],[16,25],[19,25],[23,25],[27,25],[11,27],[13,27],[17,27],[21,27],[24,27]] as [$qx, $qy])
                                        <rect x="{{ $qx }}" y="{{ $qy }}" width="2" height="2"></rect>
                                    @endforeach
                                </svg>
                            </div>
                            <p class="es-cat-card-muted mt-3 text-[0.7rem] leading-snug">Scan for every program, every date</p>
                            <div class="es-cat-card-rule mt-4 pt-3">
                                <span class="es-cat-tracing">Your branch &middot; free to download</span>
                            </div>
                        </div>
                        <p class="mx-auto mt-4 max-w-[15rem] text-center text-xs es-cat-band-muted">
                            Every schedule has a QR code you can download and print on a bookmark, a shelf label or the back of a receipt. It is on the free plan and it always points at your live calendar.
                        </p>
                    </div>
                </div>

                <p class="mx-auto mt-10 max-w-2xl text-center es-cat-band-muted" data-reveal>
                    <span class="es-cat-plan">Free</span>
                    goes one step further still: embed the sign-up form itself, and a patron registers for Tuesday without ever leaving the library website.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Everything else: bento                                    -->
    <!-- ============================================================ -->
    <section id="rest" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="mb-6" data-reveal><span class="es-cat-label" aria-hidden="true">Drawer 07 &middot; Everything else</span></div>
                <p class="es-cat-eyebrow mb-4" data-reveal style="--reveal-delay: 0.05s;">Everything else</p>
                <h2 class="es-balance es-cat-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    The rest of the drawer.
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">
                <!-- 1 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-cat-panel relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-cat-ink text-xl font-bold">Which programs are actually being looked at</h3>
                                <span class="es-cat-plan">Free</span>
                            </div>
                            <p class="es-cat-muted mb-4">The built-in analytics show page views, the devices patrons are on, and where the traffic came from, per program and over time. Useful when the board asks whether the calendar is doing anything.</p>
                            <p class="es-cat-muted text-sm">Worth saying plainly: that is what they measure. Attendance is what you count at the door, or what registration counted for you.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-cat-panel relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-cat-ink text-xl font-bold">Next year's summer reading</h3>
                                <span class="es-cat-plan">Free</span>
                            </div>
                            <p class="es-cat-muted">Clone last year's program and change the dates. The description, the recurrence with its date exceptions, the venue and the place limit all come with it.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-cat-panel relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-cat-ink text-xl font-bold">Photos from the craft table</h3>
                                <span class="es-cat-plan">Free</span>
                            </div>
                            <p class="es-cat-muted">Patrons add photos, video and comments to a program, and every submission waits in an approval queue. Free covers 25 photos per schedule, which matters when children are in them.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-cat-panel relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-cat-ink text-xl font-bold">When a program costs money</h3>
                                <span class="es-cat-plan">Free</span>
                            </div>
                            <p class="es-cat-muted mb-4">An author evening, a paid workshop, a Friends of the Library fundraiser. Named ticket types with their own prices and quantities, sold through your own Stripe account on the free plan, up to 25 paid tickets a month, and Event Schedule takes zero platform fees: past Stripe's own processing, the money is yours.</p>
                            <p class="es-cat-muted text-sm">Pro at {{ plan_price($proMonthly) }} a month takes the 25 off and adds the desk work: extra questions at checkout for access needs or a child's age, a waitlist once a ticket type sells out, and a live count as patrons check in. Scanning the QR on a ticket is free on every plan; it is the running total that is Pro. Free registration for free programs needs none of it.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-cat-panel relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-cat-ink text-xl font-bold">The poster for the noticeboard</h3>
                                <span class="es-cat-plan es-cat-plan-pro">Pro</span>
                            </div>
                            <p class="es-cat-muted">Generate one graphic from your upcoming programs, up to twenty of them, in a story, square, portrait or landscape crop. Only programs carrying their own flyer image appear, and printing the date on each is a setting, off until you turn it on.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-cat-panel relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-cat-ink text-xl font-bold">Branches, staff logins and your own address</h3>
                                <span class="es-cat-plan es-cat-plan-enterprise">Enterprise</span>
                            </div>
                            <p class="es-cat-muted mb-4">Being honest about the shape of this: the free plan is one team member, so if two people need their own login you are looking at Enterprise, which allows up to five, and which also puts the calendar on your own domain.</p>
                            <p class="es-cat-muted text-sm">
                                A separate schedule per branch is free and unlimited, and plenty of library systems run one shared account per branch instead.
                                <a href="{{ marketing_url('/pricing') }}" class="es-cat-link font-medium hover:underline">Compare the plans</a>
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
    <!-- 8. Perfect for                                               -->
    <!-- ============================================================ -->
    <section id="who" class="scroll-mt-24 border-t es-cat-rule py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="mb-6" data-reveal><span class="es-cat-label" aria-hidden="true">Drawer 08 &middot; Every branch</span></div>
                <h2 class="es-balance es-cat-ink mb-4 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    Perfect for all types of <span class="es-cat-accent">libraries</span>
                </h2>
                <p class="es-cat-muted text-lg sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    From a single branch to a university collection to a van with a route.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <x-sub-audience-card
                    name="Public Libraries"
                    description="Community programs, story times, workshops, and author events. Keep your neighborhood informed and engaged."
                    icon-color="sky"
                    blog-slug="for-public-libraries"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="University Libraries"
                    description="Lectures, research workshops, study sessions, and academic events. Reach students and faculty directly."
                    icon-color="blue"
                    blog-slug="for-university-libraries"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Community Reading Rooms"
                    description="Reading groups, literacy programs, and neighborhood book exchanges. Build a culture of reading."
                    icon-color="amber"
                    blog-slug="for-community-reading-rooms"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Children's Libraries"
                    description="Story times, crafts, summer reading programs, and educational events. Make reading fun for every child."
                    icon-color="orange"
                    blog-slug="for-childrens-libraries"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Archive & Research Centers"
                    description="Exhibitions, lectures, guided tours, and research workshops. Share your collections with the public."
                    icon-color="slate"
                    blog-slug="for-archive-research-centers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Mobile Libraries"
                    description="Bookmobile stops, pop-up reading events, and outreach programs. Bring the library to your community."
                    icon-color="teal"
                    blog-slug="for-mobile-libraries"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Three steps                                               -->
    <!-- ============================================================ -->
    <section id="steps" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="mb-6" data-reveal><span class="es-cat-label" aria-hidden="true">Drawer 09 &middot; Accession</span></div>
                <h2 class="es-balance es-cat-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    Three steps
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="120">
                @foreach ([
                    ['01', 'Catalogue the program', 'Sign up as a venue schedule, add the branch, and enter the program once: the days it runs, the sub-schedule it belongs to, and an end date or a number of sessions.'],
                    ['02', 'Take out the closed days', 'Add date exceptions for public holidays and training days, and add single extra dates for one-off sessions. Set up your sub-schedules for children, teens, adults and seniors.'],
                    ['03', 'Open the places', 'Turn on free registration with a place limit, embed the calendar on the library site, print the QR code, and email the patrons who follow you when there is news.'],
                ] as [$stepNum, $stepTitle, $stepBody])
                    <div class="es-cat-panel p-7" data-reveal="panel">
                        <div class="es-cat-num mb-3">{{ $stepNum }}</div>
                        <h3 class="es-cat-ink mb-2 text-lg font-bold">{{ $stepTitle }}</h3>
                        <p class="es-cat-muted text-sm leading-relaxed">{{ $stepBody }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. Key features                                             -->
    <!-- ============================================================ -->
    <section class="border-t es-cat-rule py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-cat-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="Weekly, fortnightly or monthly programs, with date exceptions and an end" :url="marketing_url('/features/recurring-events')" icon-color="amber">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Sub-schedules" description="Keep children, teen, adult and senior programming on their own strands" :url="marketing_url('/features/sub-schedules')" icon-color="teal">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Embed Calendar" description="Add your program calendar to the library website with one snippet" :url="marketing_url('/features/embed-calendar')" icon-color="blue">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Write to the patrons who follow you, with open and click rates" :url="marketing_url('/features/newsletters')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-cat-link inline-flex items-center font-medium hover:underline">
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
    <!-- 11. Related pages                                            -->
    <!-- ============================================================ -->
    <section class="border-t es-cat-rule py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-cat-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4" data-reveal-group="70">
                @foreach ([['/for-community-centers', 'Community Centers'], ['/for-spoken-word', 'Spoken Word'], ['/for-workshop-instructors', 'Workshop Instructors'], ['/for-theaters', 'Theaters']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" class="es-cat-hover es-cat-panel group flex flex-col p-5 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-cat-hover-title es-cat-ink mb-3 text-sm font-semibold transition-colors">For {{ $relName }}</span>
                        <span class="es-cat-hover-arrow es-cat-muted mt-auto inline-flex items-center gap-1 text-xs font-medium transition-colors">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
            <div class="mt-8 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-cat-link inline-flex items-center font-medium hover:underline">
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

    <section id="faq" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="mb-6" data-reveal><span class="es-cat-label" aria-hidden="true">Drawer 10 &middot; Ask a librarian</span></div>
                <h2 class="es-balance es-cat-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-cat-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    What librarians ask before they move a program calendar across.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-cat-hover es-cat-panel group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-cat-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-cat-accent flex-none font-mono text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-cat-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-cat-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-cat-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
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
            <div class="es-cat-drawer noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-cat-lampglow"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>
                <div class="relative z-10">
                    <div class="mb-6 flex justify-center">
                        <span class="es-cat-brass px-4 py-1.5" aria-hidden="true">Drawer 11 &middot; Circulation desk</span>
                    </div>
                    <p class="es-cat-eyebrow mb-4">Free forever</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight es-cat-band-ink md:text-5xl">
                        Catalogue it once. <span class="es-cat-lit">Check it out all year.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg es-cat-band-muted">
                        Your program calendar, recurring sessions, free registration with a place limit, and a newsletter to the patrons who follow you. All of it free, with no card.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-library" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-400 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm es-cat-band-muted sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="es-cat-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Create your calendar
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="mt-6 text-sm es-cat-band-muted">No credit card required</p>
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
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap es-cat-tip rounded-full px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
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
