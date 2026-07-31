<x-marketing-layout>
    <x-slot name="title">{{ __('messages.why_create_account_title') }} | Event Schedule</x-slot>
    <x-slot name="description">{{ __('messages.why_create_account_description') }}</x-slot>
    <x-slot name="breadcrumbTitle">{{ __('messages.why_create_account_title') }}</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "WebPage",
        "name": "Why Create an Account - Event Schedule",
        "description": "Most of Event Schedule works without signing in. An account is what puts your name on the record: follow schedules, keep every ticket in one place, own the events you submit, and publish a schedule of your own. Free, no card.",
        "url": "{{ url()->current() }}",
        "isPartOf": {
            "@type": "WebSite",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "about": {
            "@type": "Thing",
            "name": "Event Schedule free account"
        },
        "mainEntity": {
            "@type": "ItemList",
            "name": "What an Event Schedule account unlocks",
            "itemListElement": [
                { "@type": "ListItem", "position": 1, "name": "Follow a schedule, which has no signed-out equivalent" },
                { "@type": "ListItem", "position": 2, "name": "Every ticket and registration you bought, on one page" },
                { "@type": "ListItem", "position": 3, "name": "Events you submit are saved on a schedule of your own" },
                { "@type": "ListItem", "position": 4, "name": "An email when a schedule accepts or declines your event" },
                { "@type": "ListItem", "position": 5, "name": "A schedule of your own with its own address" },
                { "@type": "ListItem", "position": 6, "name": "A team seat on somebody else's schedule" },
                { "@type": "ListItem", "position": 7, "name": "Two-factor sign-in, backup and restore, and account deletion" }
            ]
        }
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free account, no credit card required"
        },
        "featureList": [
            "Follow schedules and see them all on one Following page",
            "Every ticket and free registration you bought, upcoming and past",
            "Submitted events saved on your own schedule and linked to the schedule you sent them to",
            "Email when a schedule accepts or declines your submission",
            "Edit your own events after they are submitted",
            "A Talent, Venue or Curator schedule with its own address",
            "Two-way Google, Outlook and CalDAV calendar sync on the free plan",
            "Built-in analytics, embeddable calendar and sub-schedules on the free plan",
            "Newsletters to the people who follow you",
            "Two-factor sign-in, backup and restore, and one-screen account deletion"
        ],
        "url": "{{ url()->current() }}"
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
           Why-create-account "The Keyring" styles. An account is not a
           paywall and not a feature bundle: it is one ring that carries
           a different key for every door, and the page is literally a
           list of unlocks, so a set of distinct engraved key tags is the
           form the content already has.

           THE ARGUMENT AND THE METAPHOR ARE THE SAME SENTENCE. Most
           doors here need no key at all (checkout writes
           sales.user_id = null, a guest submission is saved on the
           SCHEDULE's own record). What the ring adds is ownership, so
           the page runs two materials against each other:
             - .es-key-blank  bare pale plate  = a door already open
             - .es-key-tag    engraved blue    = a key the account cuts
           Both are FIXED PHYSICAL OBJECTS: identical with .dark on and
           off, verified with --bands. Nothing inside a tag may use a
           `dark:` utility or a shared class that flips.

           COLOUR: the page keeps its inherited blue family, but NOT as
           the shared blue-to-sky-to-cyan chrome gradient - that belongs
           to the site chrome, so a page-level gradient heading would
           read as furniture. Instead one solid deep blue (#1749c4 light,
           #7db8f5 dark) plus the anodised plate (#1e3a8a). The plate is
           anodised-flat, deliberately NOT brushed: brushed steel as a
           material is /for-nightclubs' and this must not read as metal
           tooling.

           NEVER text-gray-500 here: #6b7280 measures only ~4.3 on this
           page's tinted #f4f6fa ground. Use .es-key-muted (6.96).

           BLADE RULE: no @supports probes in this block - a "#" hex
           inside a parenthesised at-rule condition breaks Blade
           compilation of every later parenthesised directive.
           ============================================================== */

        /* --- Ground and ink ------------------------------------------ */
        .es-key-page { background-color: #f4f6fa; color: #101722; }
        .dark .es-key-page { background-color: #0a0e15; color: #e7ecf3; }
        .es-key-ink { color: #101722; }
        .dark .es-key-ink { color: #e7ecf3; }
        .es-key-muted { color: #4a5568; }
        .dark .es-key-muted { color: #97a3b4; }
        .es-key-accent { color: #1749c4; }
        .dark .es-key-accent { color: #7db8f5; }
        /* Always-lit accent, for the bands that stay dark in both modes. */
        .es-key-lit { color: #8fc7fb; }
        .es-key-dim { color: #aab6c6; }

        /* --- Cards --------------------------------------------------- */
        .es-key-card {
            border: 1px solid rgba(16, 23, 34, 0.12);
            border-radius: 1rem;
            background-color: #ffffff;
        }
        .dark .es-key-card {
            border-color: rgba(231, 236, 243, 0.12);
            background-color: rgba(231, 236, 243, 0.045);
        }
        .es-key-band .es-key-card {
            border-color: rgba(231, 236, 243, 0.13);
            background-color: rgba(231, 236, 243, 0.05);
        }
        .es-key-inset {
            border-radius: 0.75rem;
            background-color: #e9edf5;
        }
        .dark .es-key-inset { background-color: rgba(231, 236, 243, 0.07); }

        /* Hairline rules. These are page-local on purpose: an arbitrary
           `border-[rgba(...)]` utility that is not already in the built
           marketing CSS silently paints nothing, and no build may run. */
        .es-key-rule { border-color: rgba(16, 23, 34, 0.1); }
        .dark .es-key-rule { border-color: rgba(231, 236, 243, 0.11); }

        /* Dot-nav tooltip, so it does not borrow another page's tint. */
        .es-key-tip {
            border: 1px solid rgba(16, 23, 34, 0.12);
            background-color: #ffffff;
            color: #2b3648;
        }
        .dark .es-key-tip {
            border-color: rgba(231, 236, 243, 0.12);
            background-color: #141a24;
            color: #d2d9e3;
        }

        /* --- Fixed-dark band ---------------------------------------- */
        .es-key-band {
            background-color: #0c1220;
            background-image: radial-gradient(125% 105% at 50% 0%, #16203a 0%, #0e1526 55%, #070b13 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(231, 236, 243, 0.05);
        }
        /* Shared classes that flip with the colour mode inside a band. */
        .es-key-band .grid-overlay {
            background-image:
                linear-gradient(rgba(231, 236, 243, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(231, 236, 243, 0.05) 1px, transparent 1px);
        }
        .es-key-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-key-band .es-claim:focus-within {
            border-color: rgba(143, 199, 251, 0.78);
            box-shadow: 0 0 0 4px rgba(143, 199, 251, 0.22);
        }

        /* --- THE KEY TAG. A fixed physical object: anodised blue plate,
               punched hole, engraved lettering. Identical in both colour
               modes, so every value here is mode-independent. --------- */
        .es-key-tag {
            position: relative;
            display: inline-flex;
            flex-direction: column;
            justify-content: center;
            gap: 0.1rem;
            padding: 0.5rem 0.85rem 0.5rem 1.75rem;
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 0.45rem;
            background-color: #1e3a8a;
            background-image: linear-gradient(180deg, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0) 44%, rgba(0, 0, 0, 0.2) 100%);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 7px 16px -10px rgba(4, 10, 26, 0.85);
            color: #ffffff;
        }
        /* The punched hole. Geometry, not an illustration. */
        .es-key-tag::before {
            content: "";
            position: absolute;
            inset-inline-start: 0.6rem;
            top: 50%;
            margin-top: -0.23rem;
            width: 0.46rem;
            height: 0.46rem;
            border-radius: 9999px;
            background-color: #0a1024;
            box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.3), inset 0 1px 1px rgba(0, 0, 0, 0.6);
        }
        .es-key-tag-code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #bcd3f2;
        }
        .es-key-tag-name {
            font-size: 0.84rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            line-height: 1.2;
            color: #ffffff;
            text-shadow: 0 1px 0 rgba(0, 0, 0, 0.4);
        }

        /* A BLANK plate: no engraving, because the door needs no key.
           Also fixed in both modes. */
        .es-key-blank {
            border-color: rgba(23, 32, 48, 0.24);
            background-color: #cfd7e4;
            background-image: linear-gradient(180deg, rgba(255, 255, 255, 0.6) 0%, rgba(255, 255, 255, 0) 46%, rgba(23, 32, 48, 0.15) 100%);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.65), 0 7px 16px -10px rgba(4, 10, 26, 0.5);
            color: #1b2434;
        }
        .es-key-blank::before {
            background-color: #a7b3c6;
            box-shadow: 0 0 0 1px rgba(23, 32, 48, 0.3), inset 0 1px 1px rgba(23, 32, 48, 0.35);
        }
        .es-key-blank .es-key-tag-code { color: #41506a; }
        .es-key-blank .es-key-tag-name { color: #1b2434; text-shadow: 0 1px 0 rgba(255, 255, 255, 0.6); }

        /* Section mark: the same tag, engraved with a number. */
        .es-key-mark {
            flex-direction: row;
            align-items: center;
            padding: 0.3rem 0.75rem 0.3rem 1.6rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.76rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-shadow: 0 1px 0 rgba(0, 0, 0, 0.4);
        }
        /* A blank mark is engraved into a pale plate, so its relief runs the
           other way. Compound selector, so it beats .es-key-mark above. */
        .es-key-blank.es-key-mark { text-shadow: 0 1px 0 rgba(255, 255, 255, 0.6); }

        /* --- THE RING. An ellipse of nickel with the tags hung off it.
               Abstract geometry, identical in both modes so the object
               stays the same object. --------------------------------- */
        .es-key-ring { position: relative; padding-inline-start: 2.9rem; }
        .es-key-ring::before {
            content: "";
            position: absolute;
            inset-inline-start: 0.1rem;
            top: 0.9rem;
            bottom: 0.9rem;
            width: 3.1rem;
            border: 2px solid #93a1b5;
            border-radius: 9999px;
            background-image: linear-gradient(105deg, rgba(255, 255, 255, 0.32) 0%, rgba(255, 255, 255, 0) 46%, rgba(20, 28, 44, 0.18) 100%);
        }
        .es-key-hang {
            position: relative;
            display: flex;
            transform-origin: left center;
            animation: es-key-swing 7s ease-in-out infinite;
        }
        .es-key-hang + .es-key-hang { margin-top: 0.5rem; }
        .es-key-hang:nth-child(2) { animation-delay: -1.4s; margin-inline-start: 1rem; }
        .es-key-hang:nth-child(3) { animation-delay: -2.8s; }
        .es-key-hang:nth-child(4) { animation-delay: -4.2s; margin-inline-start: 1rem; }
        .es-key-hang:nth-child(5) { animation-delay: -5.6s; }
        @keyframes es-key-swing {
            0%, 100% { transform: rotate(-0.55deg); }
            50% { transform: rotate(0.65deg); }
        }

        /* --- Eyebrow / labels --------------------------------------- */
        .es-key-tagline {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            color: #4a5568;
        }
        .dark .es-key-tagline { color: #97a3b4; }
        .es-key-band .es-key-tagline { color: #8fc7fb; }

        /* --- Plan pills --------------------------------------------- */
        .es-key-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border: 1px solid rgba(23, 73, 196, 0.42);
            border-radius: 0.25rem;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #1749c4;
        }
        .dark .es-key-plan { border-color: rgba(125, 184, 245, 0.45); color: #7db8f5; }
        /* No plan pill ever renders inside a fixed-dark band on this page: the
           bands carry the "no key needed" and finale arguments, neither of which
           is plan-tiered. A .es-key-band override here would paint nothing. */
        .es-key-plan-pro { border-color: rgba(16, 23, 34, 0.35); color: #101722; }
        .dark .es-key-plan-pro { border-color: rgba(231, 236, 243, 0.38); color: #e7ecf3; }

        /* --- The duplex ledger: one action, two records -------------- */
        .es-key-table { border-collapse: collapse; width: 100%; }
        .es-key-table th,
        .es-key-table td {
            border-top: 1px solid rgba(16, 23, 34, 0.1);
            vertical-align: top;
        }
        .dark .es-key-table th,
        .dark .es-key-table td { border-top-color: rgba(231, 236, 243, 0.1); }
        .es-key-table thead th { border-top: 0; }
        /* The signed-in column is tinted, so the duplex reads as two
           records rather than as a feature checklist. */
        .es-key-col-in { background-color: rgba(23, 73, 196, 0.05); }
        .dark .es-key-col-in { background-color: rgba(125, 184, 245, 0.07); }
        .es-key-col-rule { border-inline-start: 1px solid rgba(23, 73, 196, 0.28); }
        .dark .es-key-col-rule { border-inline-start-color: rgba(125, 184, 245, 0.32); }

        /* --- Chips -------------------------------------------------- */
        .es-key-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.3rem 0.75rem;
            border: 1px solid rgba(16, 23, 34, 0.14);
            border-radius: 9999px;
            background-color: rgba(255, 255, 255, 0.7);
            color: #41506a;
            font-size: 0.74rem;
            font-weight: 600;
            letter-spacing: 0.05em;
        }
        .dark .es-key-chip {
            border-color: rgba(231, 236, 243, 0.16);
            background-color: rgba(231, 236, 243, 0.05);
            color: #aab6c6;
        }

        /* --- Links and buttons -------------------------------------- */
        .es-key-link { color: #1749c4; }
        .es-key-link:hover { color: #101722; }
        .dark .es-key-link { color: #7db8f5; }
        .dark .es-key-link:hover { color: #e7ecf3; }
        .es-key-band .es-key-link { color: #8fc7fb; }
        .es-key-band .es-key-link:hover { color: #ffffff; }

        .es-key-btn {
            background-color: #1749c4;
            color: #ffffff;
            box-shadow: 0 18px 36px -14px rgba(23, 73, 196, 0.55);
        }
        .es-key-btn:hover {
            background-color: #12379a;
            box-shadow: 0 22px 44px -14px rgba(23, 73, 196, 0.65);
        }
        .dark .es-key-btn { background-color: #7db8f5; color: #08111f; }
        .dark .es-key-btn:hover { background-color: #a3cefa; color: #08111f; }
        .es-key-band .es-key-btn { background-color: #7db8f5; color: #08111f; }
        .es-key-band .es-key-btn:hover { background-color: #a3cefa; }

        .es-key-ghost {
            border: 1px solid rgba(16, 23, 34, 0.16);
            background-color: rgba(255, 255, 255, 0.72);
            color: #101722;
        }
        .es-key-ghost:hover { border-color: rgba(23, 73, 196, 0.5); }
        .dark .es-key-ghost {
            border-color: rgba(231, 236, 243, 0.16);
            background-color: rgba(231, 236, 243, 0.05);
            color: #e7ecf3;
        }
        .dark .es-key-ghost:hover { border-color: rgba(125, 184, 245, 0.5); }

        /* --- Hover treatment shared by FAQ and related cards -------- */
        .es-key-hover:hover { border-color: rgba(23, 73, 196, 0.45); }
        .dark .es-key-hover:hover { border-color: rgba(125, 184, 245, 0.45); }
        .es-key-hover:hover .es-key-hover-title,
        .es-key-hover:hover .es-key-hover-arrow { color: #1749c4; }
        .dark .es-key-hover:hover .es-key-hover-title,
        .dark .es-key-hover:hover .es-key-hover-arrow { color: #7db8f5; }

        /* --- Shared-system recolors (brand blue chrome by default) --- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(23, 73, 196, 0.13), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(125, 184, 245, 0.13), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(23, 73, 196, 0.62); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(125, 184, 245, 0.62); }
        .es-dot.is-active .es-dot-pip { background: #1749c4; }
        .dark .es-dot.is-active .es-dot-pip { background: #7db8f5; }

        /* --- Focus rings. No border-radius here: setting it changes the
               element's own shape on focus. Outlines already follow it. */
        #es-key-page a:focus-visible,
        #es-key-page summary:focus-visible,
        #es-key-page button:focus-visible,
        #es-key-page input:focus-visible {
            outline: 2px solid #1749c4;
            outline-offset: 3px;
        }
        .dark #es-key-page a:focus-visible,
        .dark #es-key-page summary:focus-visible,
        .dark #es-key-page button:focus-visible,
        .dark #es-key-page input:focus-visible {
            outline-color: #7db8f5;
        }
        .es-key-band a:focus-visible,
        .es-key-band summary:focus-visible,
        .es-key-band button:focus-visible,
        .es-key-band input:focus-visible {
            outline-color: #8fc7fb !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-key-hang { animation: none !important; transform: none !important; }
        }
    </style>

    @php
        // The five tags on the hero ring. Each is a real door: the code is the
        // surface it opens, the name is what the account does there.
        $ringTags = [
            ['Follow', 'Follow a schedule'],
            ['Tickets', 'Every ticket you bought'],
            ['Submit', 'Events you sent in'],
            ['Publish', 'A schedule of your own'],
            ['Team', 'A seat on somebody else\'s'],
        ];

        // Doors that are already open: no account, no key.
        $openDoors = [
            [
                'Read anything public',
                'Every public schedule page, event page and calendar is open to anyone. Any event can be added to your own calendar as an .ics download without signing in to anything.',
            ],
            [
                'Buy a ticket',
                'Checkout asks for a name and an email. The confirmation and the QR code arrive by email, and the order is stored with no owner on it. Creating an account at checkout is an optional tick box, not a step.',
            ],
            [
                'Register for a free event',
                'Free registration works the same way, capacity limits included. A name and an email address, and the schedule sees the sign-up straight away.',
            ],
            [
                'Add photos and comments',
                'Photos, video and comments on an event take a name and an email, and they go through the schedule\'s approval queue before anyone else sees them. A schedule can choose to require an account here instead, and some do.',
            ],
        ];

        // The duplex ledger. Same action, two records.
        $ledger = [
            [
                'Buy a ticket',
                'Confirmed by email. The order is stored with no owner, so it lives in that email and nowhere else.',
                'The same order, listed on your Tickets page with everything else you have bought, upcoming and past.',
            ],
            [
                'Register for a free event',
                'Registered, confirmed by email, and counted against the capacity for that date.',
                'Registered, and it joins the same list as your tickets.',
            ],
            [
                'Submit an event',
                'Saved on the schedule you sent it to. It is their record from that moment on.',
                'Saved on a schedule of your own and linked to theirs, so it shows on both pages once they accept it.',
            ],
            [
                'Hear the verdict',
                'Nothing comes back to you. There is no address to write to.',
                'An email when the schedule accepts it, and an email when it declines it.',
            ],
            [
                'Fix a detail later',
                'Ask the schedule to change it for you.',
                'Open the event and change it yourself. It is your record.',
            ],
            [
                'Vote in a poll',
                'Not available. A vote is a record too, so the poll asks you to sign in before it counts one.',
                'Counted once, against your account. Polls are a Pro feature for the schedule running them.',
            ],
            [
                'Follow a schedule',
                'Not available. There is no guest version of following: the record IS the link between you and them.',
                'On your ring, listed on your Following page, and one click to unfollow.',
            ],
        ];

        // Free-plan tools that come with a schedule of your own.
        $freeTools = [
            'Google, Outlook and CalDAV sync',
            'Built-in analytics',
            'Embeddable calendar',
            'Sub-schedules',
            'Recurring events',
            'Free registration and capacity',
            'iCal downloads',
            'Event cloning',
            'Backup and restore',
        ];

        $steps = [
            ['01', 'Create the account', 'An email address and a password, or continue with Google. A verification code confirms the address, and no card is asked for at any point.'],
            ['02', 'Decide later what you are', 'You are not forced into a schedule. If you do want one, pick Talent, Venue or Curator and it gets its own address. You can run more than one on the same login.'],
            ['03', 'Start using the ring', 'Follow the schedules you care about, submit to the ones you play, and publish your own when you are ready.'],
        ];

        $faqs = [
            [
                'q' => 'Do I need an account to buy a ticket?',
                'a' => 'No. Checkout asks for a name and an email, and the ticket and its QR code arrive by email. What signing in first changes is where the order lives afterwards: it appears on your Tickets page along with everything else you have bought, upcoming and past, rather than only in that one email.',
            ],
            [
                'q' => 'Does an account cost anything?',
                'a' => 'No. The account is free and asks for no card. What costs money is a schedule you run, once you want more than the free plan: the free plan sells up to 25 paid tickets a month and scans the QR code on every one of them at the door, Pro at $5 a month makes ticket sales unlimited and adds the live check-in dashboard, custom fields and a waitlist, and Enterprise at $15 a month adds multiple team members and custom domains. Event Schedule charges zero platform fees on ticket sales on every plan, the free one included, so past the payment processor the money is yours.',
            ],
            [
                'q' => 'What actually changes when I submit an event signed in?',
                'a' => 'Whose record it is. Signed out, the event is saved on the schedule you sent it to, so they own it and they are the only ones who can edit it. Signed in, it is saved on a schedule of your own and linked to theirs, so it appears on both pages once they accept, you can edit it yourself, and you get an email whether they accept it or decline it.',
            ],
            [
                'q' => 'Will following a schedule fill my inbox?',
                'a' => 'No, because nothing about it is automatic. There is no notification that fires when a schedule adds an event. Following puts the schedule on your Following page and lets that schedule include you when somebody there writes a newsletter, and those are capped at 10 recipients a month on the free plan, 100 on Pro and 1,000 on Enterprise.',
            ],
            [
                'q' => 'Can I have an account without creating a schedule?',
                'a' => 'Yes. Following schedules and keeping your tickets in one place need nothing else. A schedule is what you add when you want a page of your own to publish from, and you can add it months later.',
            ],
            [
                'q' => 'Can one account run more than one schedule?',
                'a' => 'Yes, and they share one login. A schedule somebody else runs can also add you to theirs as an admin, who can edit it, or as a viewer, who can only look, and that lands on the same account. Adding more than one team member is an Enterprise feature for the schedule doing the inviting, capped at five.',
            ],
            [
                'q' => 'How do I get rid of it?',
                'a' => 'Your settings page has a delete option that removes the account outright, on one screen rather than through a support request. Before that, backup and restore lets you export a schedule with or without its images, so you can take the data with you.',
            ],
        ];

        $dotSections = [
            ['top', 'The ring'],
            ['open', 'Already open'],
            ['ledger', 'Two records'],
            ['keys', 'The keys'],
            ['not', 'What it is not'],
            ['cut', 'Cut the key'],
            ['faq', 'Questions'],
            ['claim', 'Claim a name'],
        ];
    @endphp

    <div id="es-key-page" class="es-key-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the ring                                            -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(23, 73, 196, 0.24), rgba(23, 73, 196, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(125, 184, 245, 0.16), rgba(125, 184, 245, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="es-key-accent h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <span class="es-key-muted text-sm font-medium tracking-wide">{{ __('messages.why_create_account_hero_badge') }} &middot; no card</span>
                    </div>

                    <h1 class="es-balance es-key-ink mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">Most of this is already</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="es-key-accent">unlocked.</span></span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-key-muted mb-10 max-w-xl text-lg sm:text-xl">
                        You can read a schedule, buy a ticket, register for a free night and often submit an event without signing in to anything. What an account adds is ownership: your name on the record, the record in one place, and the right to change it later.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="#ledger" class="es-key-ghost group inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            See both records
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                        <a href="{{ app_url('/sign_up') }}" class="es-key-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            {{ __('messages.why_create_account_cta') }}
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- The ring itself: one loop, five engraved tags. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-key-card p-6 sm:p-7">
                        <p class="es-key-tagline mb-1">One login</p>
                        <h2 class="es-key-ink mb-5 text-lg font-bold">A different key for each door</h2>

                        {{-- The ring geometry is decorative (a ::before ellipse, never announced),
                             but the five tag NAMES are the hero's only content list, so they stay
                             in the accessibility tree. Only the engraved key numbers are hidden. --}}
                        <div class="es-key-ring">
                            @foreach ($ringTags as $ringIndex => [$tagCode, $tagName])
                                <div class="es-key-hang">
                                    <div class="es-key-tag">
                                        <span class="es-key-tag-code" aria-hidden="true">{{ str_pad($ringIndex + 1, 2, '0', STR_PAD_LEFT) }} &middot; {{ $tagCode }}</span>
                                        <span class="es-key-tag-name">{{ $tagName }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <p class="es-key-muted mt-6 es-key-rule border-t pt-4 text-xs">
                            Five keys, and not one of them costs anything. Everything on this ring is on the free plan.
                        </p>
                    </div>
                </div>
            </div>

            <!-- What the ring reaches -->
            <div class="es-fade-up es-d-4 mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach (['Following', 'Tickets', 'Submissions', 'Your own schedule', 'Sub-schedules', 'Calendar sync', 'Analytics', 'Embed', 'Newsletters', 'Team seat', 'Backups', 'Two-factor'] as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-key-chip">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. Doors that are already open (fixed-dark band)             -->
    <!-- ============================================================ -->
    <section id="open" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-key-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <span class="es-key-tag es-key-blank es-key-mark mb-6" data-reveal aria-hidden="true">02</span>
                    <p class="es-key-tagline mb-4" data-reveal style="--reveal-delay: 0.05s;">No key needed</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        An account is <span class="es-key-lit">not a turnstile.</span>
                    </h2>
                    <p class="es-key-dim mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        These plates are blank on purpose. Four things people routinely assume sit behind a sign-up are not gated at all, and pretending otherwise would be a poor argument for making one.
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-2" data-reveal-group="100">
                    @foreach ($openDoors as [$doorTitle, $doorBody])
                        <div class="es-key-card p-7" data-reveal="panel">
                            <div class="mb-4 flex flex-wrap items-center gap-3">
                                <span class="es-key-tag es-key-blank" aria-hidden="true">
                                    <span class="es-key-tag-code">Open</span>
                                    <span class="es-key-tag-name">No account</span>
                                </span>
                                <h3 class="es-key-lit text-lg font-bold">{{ $doorTitle }}</h3>
                            </div>
                            <p class="es-key-dim text-sm leading-relaxed">{{ $doorBody }}</p>
                        </div>
                    @endforeach
                </div>

                <p class="es-key-dim mx-auto mt-10 max-w-2xl text-center" data-reveal>
                    Submitting an event belongs on that list too, because most schedules will take one from anybody. It is the interesting case: the door opens either way, and what changes is whose record the event becomes.
                    <a href="#ledger" class="es-key-link inline-flex items-center gap-1 font-semibold transition-all hover:gap-2">
                        See both records
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The duplex ledger: one action, two records               -->
    <!-- ============================================================ -->
    <section id="ledger" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <span class="es-key-tag es-key-mark mb-6" data-reveal aria-hidden="true">03</span>
                <p class="es-key-tagline mb-4" data-reveal style="--reveal-delay: 0.05s;">The same action, twice</p>
                <h2 class="es-balance es-key-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    One action. <span class="es-key-accent">Two records.</span>
                </h2>
                <p class="es-key-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Almost nothing here is about permission. It is about whose record the thing becomes, and that is the whole argument for a key.
                </p>
            </div>

            <div class="es-key-card overflow-x-auto p-4 sm:p-6" data-reveal="panel">
                <table class="es-key-table text-left">
                    <caption class="sr-only">The same seven actions performed signed out and signed in, and the record each one leaves</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="es-key-tagline pb-3 pe-3 font-bold">Action</th>
                            <th scope="col" class="es-key-tagline pb-3 pe-3 font-bold">Signed out</th>
                            <th scope="col" class="es-key-tagline es-key-col-in es-key-col-rule px-3 pb-3 font-bold">Signed in</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ledger as [$actName, $actOut, $actIn])
                            <tr>
                                <th scope="row" class="es-key-ink py-4 pe-3 text-sm font-bold">{{ $actName }}</th>
                                <td class="es-key-muted py-4 pe-3 text-sm leading-relaxed">{{ $actOut }}</td>
                                <td class="es-key-ink es-key-col-in es-key-col-rule px-3 py-4 text-sm leading-relaxed">{{ $actIn }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="es-key-muted mx-auto mt-6 max-w-3xl text-center text-sm" data-reveal>
                Selling tickets starts on the free plan, at 25 paid tickets a month for the schedule doing the selling. Buying one, with or without an account, is free, and Event Schedule takes no cut of the sale either way.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. The keys on the ring: bento                               -->
    <!-- ============================================================ -->
    <section id="keys" class="es-key-rule scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <span class="es-key-tag es-key-mark mb-6" data-reveal aria-hidden="true">04</span>
                <p class="es-key-tagline mb-4" data-reveal style="--reveal-delay: 0.05s;">On the ring</p>
                <h2 class="es-balance es-key-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Six keys, <span class="es-key-accent">one login.</span>
                </h2>
                <p class="es-key-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Each one is a real surface with a plan next to it, so you can see exactly where the free plan stops.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">
                <!-- 1 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-key-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10 flex h-full flex-col">
                            <div class="mb-4 flex flex-wrap items-center gap-3">
                                <span class="es-key-tag" aria-hidden="true">
                                    <span class="es-key-tag-code">04 &middot; Publish</span>
                                    <span class="es-key-tag-name">A page of your own</span>
                                </span>
                                <span class="es-key-plan">Free</span>
                            </div>
                            <h3 class="es-key-ink mb-3 text-xl font-bold">A schedule with its own address</h3>
                            <p class="es-key-muted mb-5">Pick Talent, Venue or Curator, give it a name, and it lives at that name on eventschedule.com. Add a profile image, a header and an accent colour and it stops looking like anybody else's. Everything below comes with it on the free plan, with no trial attached and no card taken.</p>
                            <div class="mt-auto flex flex-wrap gap-2">
                                @foreach ($freeTools as $tool)
                                    <span class="es-key-chip">{{ $tool }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-key-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-3">
                                <span class="es-key-tag" aria-hidden="true">
                                    <span class="es-key-tag-code">01 &middot; Follow</span>
                                    <span class="es-key-tag-name">Follow a schedule</span>
                                </span>
                                <span class="es-key-plan">Free</span>
                            </div>
                            <h3 class="es-key-ink mb-3 text-xl font-bold">There is no guest version of this</h3>
                            <p class="es-key-muted mb-4">Following collects the schedules you care about on one page you can sort, search and prune in bulk. It also lets them write to you: 10 recipients a month on the free plan, 100 on Pro, 1,000 on Enterprise.</p>
                            <p class="es-key-muted text-sm">Worth saying plainly: nothing is automatic. A newsletter is written and sent by a person, so following is permission, not an alert feed.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-key-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-3">
                                <span class="es-key-tag" aria-hidden="true">
                                    <span class="es-key-tag-code">02 &middot; Tickets</span>
                                    <span class="es-key-tag-name">Every ticket you bought</span>
                                </span>
                                <span class="es-key-plan">Free</span>
                            </div>
                            <h3 class="es-key-ink mb-3 text-xl font-bold">Not in a search of your inbox</h3>
                            <p class="es-key-muted">Buy or register while signed in and the order joins one list, upcoming and past, with the event it belongs to. Buy signed out and it exists only in the confirmation email.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-key-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-3">
                                <span class="es-key-tag" aria-hidden="true">
                                    <span class="es-key-tag-code">03 &middot; Submit</span>
                                    <span class="es-key-tag-name">Events you sent in</span>
                                </span>
                                <span class="es-key-plan">Free</span>
                            </div>
                            <h3 class="es-key-ink mb-3 text-xl font-bold">Your submission, and the reply</h3>
                            <p class="es-key-muted mb-4">Submit while signed in and the event is saved on your own schedule and linked to the one you sent it to. Its own page carries a pending-review banner until they decide, you get an email whether they accept it or decline it, and you can correct a time or swap an image without asking anybody.</p>
                            <p class="es-key-muted text-sm">Signed out, the event becomes the receiving schedule's record. That is not a punishment, it is just who the row belongs to, and it is why the reply has nowhere to go.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-key-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-3">
                                <span class="es-key-tag" aria-hidden="true">
                                    <span class="es-key-tag-code">05 &middot; Team</span>
                                    <span class="es-key-tag-name">Somebody else's schedule</span>
                                </span>
                                <span class="es-key-plan es-key-plan-pro">Enterprise</span>
                            </div>
                            <h3 class="es-key-ink mb-3 text-xl font-bold">A key you were handed</h3>
                            <p class="es-key-muted">A schedule can add you as an admin, who can edit it, or as a viewer, who can only look, and it turns up on the same ring as everything else. Adding more than one team member is Enterprise for the schedule inviting you, capped at five.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-key-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-3">
                                <span class="es-key-tag" aria-hidden="true">
                                    <span class="es-key-tag-code">06 &middot; Yours</span>
                                    <span class="es-key-tag-name">Locking and leaving</span>
                                </span>
                                <span class="es-key-plan">Free</span>
                            </div>
                            <h3 class="es-key-ink mb-3 text-xl font-bold">The ring is yours to unpick</h3>
                            <p class="es-key-muted mb-4">Turn on two-factor from your settings so the ring needs a second factor to open. Export a schedule, with or without its images, and import it again. Event Schedule is open source, so a copy you run on your own server reads the same export.</p>
                            <p class="es-key-muted text-sm">
                                And when you are done, deleting the account is one screen in settings rather than a support request.
                                <a href="{{ marketing_url('/selfhost') }}" class="es-key-link font-medium hover:underline">Selfhost it instead</a>
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
    <!-- 5. Two things a key is not                                   -->
    <!-- ============================================================ -->
    <section id="not" class="es-key-rule scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <span class="es-key-tag es-key-blank es-key-mark mb-6" data-reveal aria-hidden="true">05</span>
                <p class="es-key-tagline mb-4" data-reveal style="--reveal-delay: 0.05s;">Being straight about it</p>
                <h2 class="es-balance es-key-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    Two things a key <span class="es-key-accent">is not.</span>
                </h2>
            </div>

            <div class="grid gap-6 md:grid-cols-2" data-reveal-group="110">
                {{-- flex flex-col + mt-auto on the inset so the two footnote panels sit on the
                     same baseline even though the paragraphs above them differ in length. --}}
                <div class="es-key-card flex flex-col p-7" data-reveal="panel">
                    <h3 class="es-key-ink mb-3 text-lg font-bold">It is not a paywall</h3>
                    <p class="es-key-muted mb-4 text-sm leading-relaxed">Every key on this page is on the free plan, and the account asks for no card at sign-up or afterwards. What the paid plans buy is capability for a schedule you run: the free plan already sells up to 25 paid tickets a month and scans them at the door, Pro at $5 a month takes that ceiling off and adds the live check-in dashboard, custom fields and a waitlist, and Enterprise at $15 a month adds multiple team members, custom domains and AI agenda scanning.</p>
                    <div class="es-key-inset mt-auto p-4">
                        <p class="es-key-muted text-xs leading-relaxed">Newsletters, two-way calendar sync, analytics, the embeddable calendar and free registration with a capacity limit are all on the free plan. That is unusual enough to be worth stating outright rather than implying.</p>
                    </div>
                </div>

                <div class="es-key-card flex flex-col p-7" data-reveal="panel">
                    <h3 class="es-key-ink mb-3 text-lg font-bold">It is not a notification subscription</h3>
                    <p class="es-key-muted mb-4 text-sm leading-relaxed">Following a schedule does not sign you up for automatic alerts, because no such alert exists. Nothing emails followers when a schedule adds an event.</p>
                    <div class="es-key-inset mt-auto p-4">
                        <p class="es-key-muted text-xs leading-relaxed">What following does is put the schedule where you can find it again, and let somebody there include you the next time they write to their audience. The traffic that is automatic runs the other way: a schedule is emailed when a submission lands in its queue.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Cutting the key                                           -->
    <!-- ============================================================ -->
    <section id="cut" class="es-key-rule scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <span class="es-key-tag es-key-mark mb-6" data-reveal aria-hidden="true">06</span>
                <h2 class="es-balance es-key-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    {{ __('messages.why_create_account_how_title') }}
                </h2>
                <p class="es-key-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    Three steps, and only the first one is required.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="120">
                @foreach ($steps as [$stepNum, $stepTitle, $stepBody])
                    <div class="es-key-card p-7" data-reveal="panel">
                        <div class="es-key-accent mb-3 font-mono text-2xl font-black">{{ $stepNum }}</div>
                        <h3 class="es-key-ink mb-2 text-lg font-bold">{{ $stepTitle }}</h3>
                        <p class="es-key-muted text-sm leading-relaxed">{{ $stepBody }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Key features                                              -->
    <!-- ============================================================ -->
    <section class="es-key-rule border-t py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-key-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>What the free plan carries</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Email the people who follow you, with open and click rates" :url="marketing_url('/features/newsletters')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Calendar Sync" description="Two-way sync with Google, Outlook and CalDAV" :url="marketing_url('/features/calendar-sync')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Analytics" description="Page views, devices and traffic sources for your schedule" :url="marketing_url('/features/analytics')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Embed Calendar" description="Put your schedule on the website you already have" :url="marketing_url('/features/embed-calendar')" icon-color="blue">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-key-link inline-flex items-center font-medium hover:underline">
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
    <!-- 8. Related pages                                             -->
    <!-- ============================================================ -->
    <section class="es-key-rule border-t py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-key-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4" data-reveal-group="70">
                @foreach ([['/pricing', 'Pricing', 'What the plans cost'], ['/features', 'Features', 'Everything, in one list'], ['/examples', 'Examples', 'Real schedules to look at'], ['/faq', 'FAQ', 'The rest of the questions']] as [$relHref, $relName, $relBlurb])
                    <a href="{{ marketing_url($relHref) }}" class="es-key-hover es-key-card group flex flex-col p-5 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-key-hover-title es-key-ink mb-2 text-sm font-semibold transition-colors">{{ $relName }}</span>
                        <span class="es-key-muted mb-3 text-xs leading-relaxed">{{ $relBlurb }}</span>
                        <span class="es-key-hover-arrow es-key-muted mt-auto inline-flex items-center gap-1 text-xs font-medium transition-colors">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
            <div class="mt-8 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-key-link inline-flex items-center font-medium hover:underline">
                    See all use cases
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. FAQ                                                       -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="es-key-rule scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <span class="es-key-tag es-key-mark mb-6" data-reveal aria-hidden="true">07</span>
                <h2 class="es-balance es-key-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-key-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    What people ask before they hand over an email address.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-key-hover es-key-card group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-key-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-key-accent flex-none font-mono text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-key-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-key-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-key-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. Finale: cut the key                                      -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-key-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    {{-- The concept's payoff, stated as the two materials the whole page has
                         been arguing with: the blank plate anybody already carries, and the
                         one engraving turns it into. What gets engraved is the name the field
                         below asks for. Both plates are mode-independent, like every other
                         plate on the page, so this reads the same in a dark room. --}}
                    <div class="mb-9 flex flex-wrap items-center justify-center gap-4" aria-hidden="true">
                        <span class="es-key-tag es-key-blank">
                            <span class="es-key-tag-code">Blank</span>
                            <span class="es-key-tag-name">Anyone</span>
                        </span>
                        <svg class="es-key-lit h-6 w-6 shrink-0 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                        <span class="es-key-tag">
                            <span class="es-key-tag-code">Cut</span>
                            <span class="es-key-tag-name" dir="ltr">your-name</span>
                        </span>
                    </div>
                    <p class="es-key-tagline mb-4">Free forever</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Cut the key. <span class="es-key-lit">Keep the record.</span>
                    </h2>
                    <p class="es-key-dim mx-auto mb-10 max-w-2xl text-lg sm:text-xl">
                        Claim a name now and leave the rest until you need it. Nothing on this page costs anything, and nothing here asks for a card.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-name" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-400 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-key-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                {{ __('messages.why_create_account_cta') }}
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="es-key-dim mt-6 text-sm">{{ __('messages.why_create_account_no_cc') }}</p>
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
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 es-key-tip whitespace-nowrap rounded-full px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <x-marketing.related-pages />

    <!-- Local confetti (no CDN) + motion engines -->
    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
