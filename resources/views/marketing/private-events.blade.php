<x-marketing-layout>
    <x-slot name="title">Private Events | Internal and Unlisted Events - Event Schedule</x-slot>
    <x-slot name="description">Keep events members-only with Internal visibility, or hide them from your public schedule as Unlisted with an optional password. Control who sees what, per event.</x-slot>
    <x-slot name="breadcrumbTitle">Private Events</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Private Events",
        "description": "Keep events members-only with Internal visibility, or hide them from your public schedule as Unlisted with an optional password. Control who sees what, per event.",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Event Privacy Controls",
        "operatingSystem": ["Web", "Android", "iOS"],
        "featureList": [
            "Four visibility states per event: Public, Draft, Internal and Unlisted",
            "Internal events visible only to signed-in schedule members",
            "Unlisted events reachable by direct link only",
            "Optional password on an unlisted event",
            "Hidden events excluded from the public iCal and RSS feeds, the sitemap and discovery search",
            "Hidden events excluded from newsletter round-ups, generated graphics and promotions",
            "A schedule-wide default visibility for new events",
            "Draft and Internal events never pushed to a connected calendar; Unlisted events synced as private",
            "Losing Enterprise keeps hidden events hidden rather than publishing them"
        ],
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Free plan available. Internal and Unlisted visibility are included on the Enterprise plan."
        },
        "url": "{{ url()->current() }}",
        "keywords": "private events, unlisted events, internal events, password protected event, members only events, event visibility",
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
           For private-events "The Vault" styles. A vault is not one door.
           It is a corridor of them, and each one lets a different set of
           people through: the lobby, the back office, the staff door, and
           the numbered box you hand a key to. That is exactly the product
           story here, because visibility is FOUR states on the event
           (Public, Draft, Internal, Unlisted) and not a single on/off
           switch, so the page is built around a register of doors rather
           than a padlock.

           THE SIGNATURE MARK IS A MILLED BRASS DOOR PLATE, not a padlock
           icon and not a row of password dots. The previous version of
           this page used blinking password dots in three full-bleed
           layers; dots say "secret" but say nothing about WHICH door, and
           the whole argument of this page is which door. The plate is a
           real physical object, so it renders IDENTICALLY with .dark on
           and off (verified with --bands=.es-vault-plate). Never hang a
           dark: utility on it.

           ANTI-COLLISION, and this is binding. /for-libraries owns the
           ANIMATED brass label (.es-cat-brass: a solid brass gradient with
           a light bar travelling across it on a 7s loop), and
           /for-hotels-and-resorts owns the brass-edged card tab used as a
           section numeral. So this plate must not be a third animated
           brass sheen. It is a plate SCREWED TO A DOOR: two screw heads
           milled into the ends, a vertical brushed grain, and NO motion at
           all. Do not reintroduce a sheen keyframe here, and do not add
           a left-edge accent border, which is the hotels tab.

           COLOUR: brass. The page's existing hue family was amber/gold
           and it stays; what changes is the metal. Brass is pulled down
           and greened (#7a5a12 ink, #b1893a plate, #e5c26a lit) away from
           the flat amber-500 the other amber pages use, and the grounds
           are warm stone (#f5f2ec) and burnt oak (#0d0c0a) rather than
           white and near-black.

           NEVER use text-gray-500 on these tinted grounds - it drops to
           roughly 4.2. Use .es-vault-muted (8.20 light, 7.19 dark).

           BLADE RULE for this block: no @supports probes with a "#" hex
           in the condition, it breaks Blade compilation of every later
           parenthesized directive.
           ============================================================== */

        /* --- Ground and ink --- */
        .es-vault-page { background-color: #f5f2ec; color: #171410; }
        .dark .es-vault-page { background-color: #0d0c0a; color: #ece7dd; }
        .es-vault-ink { color: #171410; }
        .dark .es-vault-ink { color: #ece7dd; }
        .es-vault-muted { color: #4d4740; }
        .dark .es-vault-muted { color: #a49c8e; }
        .es-vault-accent { color: #7a5a12; }
        .dark .es-vault-accent { color: #e5c26a; }
        /* Always-lit ink, for the bands that stay dark in both colour modes.
           These must be real CSS: a Tailwind arbitrary value that is not already
           in the built stylesheet renders as nothing at all. */
        .es-vault-lit { color: #e5c26a; }
        .es-vault-band-ink { color: #ece7dd; }
        .es-vault-band-muted { color: #a49c8e; }
        .es-vault-band-soft { color: #b3ab9d; }

        /* --- Cards --- */
        .es-vault-card {
            border: 1px solid rgba(23, 20, 16, 0.12);
            border-radius: 1rem;
            background: #ffffff;
        }
        .dark .es-vault-card { border-color: rgba(236, 231, 221, 0.12); background: #171614; }
        .es-vault-band .es-vault-card { border-color: rgba(236, 231, 221, 0.14); background: #16130d; }

        /* --- Fixed-dark band: the inside of the vault, dark in both modes --- */
        .es-vault-band {
            background-color: #0b0a08;
            background-image: radial-gradient(120% 100% at 50% 0%, #1a1610 0%, #100e0a 58%, #070605 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.55), inset 0 1px 0 rgba(236, 231, 221, 0.05);
        }
        /* Shared classes carry their own .dark rules in marketing.css, so pin them here. */
        .es-vault-band .grid-overlay {
            background-image:
                linear-gradient(rgba(236, 231, 221, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(236, 231, 221, 0.05) 1px, transparent 1px);
        }
        .es-vault-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-vault-band .es-claim:focus-within {
            border-color: rgba(229, 194, 106, 0.75);
            box-shadow: 0 0 0 4px rgba(229, 194, 106, 0.22);
        }
        .es-vault-band .es-aurora { opacity: 0.5; }

        /* --- The door plate: the page's one physical object --- */
        .es-vault-plate {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            position: relative;
            overflow: hidden;
            flex: none;
            padding: 0.4rem 1.25rem;
            border-radius: 0.25rem;
            border: 1px solid rgba(36, 28, 7, 0.45);
            background-color: #b1893a;
            /* Vertical brushed grain over the brass, so the metal is milled rather
               than polished. Static: the travelling sheen belongs to /for-libraries. */
            background-image:
                repeating-linear-gradient(90deg, rgba(255, 252, 240, 0.05) 0 1px, rgba(36, 28, 7, 0.028) 1px 4px),
                linear-gradient(140deg, #dcbd72 0%, #c4a054 38%, #b1893a 64%, #cdab5f 100%);
            box-shadow:
                inset 0 1px 0 rgba(255, 248, 225, 0.55),
                inset 0 -1px 0 rgba(36, 28, 7, 0.35),
                0 1px 2px rgba(23, 20, 16, 0.25);
            color: #241c07;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            font-variant-numeric: tabular-nums;
        }
        .es-vault-plate > span { position: relative; z-index: 1; }
        /* The two screw heads that hold the plate to the door: a countersunk dot with
           a lit upper edge at each end. Fixed rgba over the brass, so the plate stays
           byte-identical between colour modes. */
        .es-vault-plate::after {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background-repeat: no-repeat;
            background-image:
                radial-gradient(circle at 0.6rem calc(50% - 1.1px), rgba(255, 250, 230, 0.6) 0 0.9px, rgba(36, 28, 7, 0) 1px),
                radial-gradient(circle at 0.6rem 50%, rgba(36, 28, 7, 0.42) 0 2.1px, rgba(36, 28, 7, 0) 2.3px),
                radial-gradient(circle at calc(100% - 0.6rem) calc(50% - 1.1px), rgba(255, 250, 230, 0.6) 0 0.9px, rgba(36, 28, 7, 0) 1px),
                radial-gradient(circle at calc(100% - 0.6rem) 50%, rgba(36, 28, 7, 0.42) 0 2.1px, rgba(36, 28, 7, 0) 2.3px);
        }
        .es-vault-plate-lg { padding: 0.5rem 1.4rem; font-size: 0.78rem; }
        /* In the corridor and the register the plates are a wall, so they are milled
           to one width instead of shrinking to their label. */
        .es-vault-door .es-vault-plate,
        .es-vault-reg .es-vault-plate { min-width: 7.25rem; justify-content: center; }

        /* --- Engraved eyebrow --- */
        .es-vault-tag {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            color: #4d4740;
        }
        .dark .es-vault-tag { color: #a49c8e; }
        .es-vault-band .es-vault-tag { color: #e5c26a; }

        /* --- Plan tags --- */
        .es-vault-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.25rem;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            border: 1px solid rgba(122, 90, 18, 0.45);
            color: #7a5a12;
        }
        .dark .es-vault-plan { border-color: rgba(229, 194, 106, 0.45); color: #e5c26a; }
        .es-vault-band .es-vault-plan { border-color: rgba(229, 194, 106, 0.45); color: #e5c26a; }
        .es-vault-plan-free { border-color: rgba(23, 20, 16, 0.35); color: #4d4740; }
        .dark .es-vault-plan-free { border-color: rgba(236, 231, 221, 0.35); color: #a49c8e; }
        .es-vault-band .es-vault-plan-free { border-color: rgba(236, 231, 221, 0.35); color: #a49c8e; }

        /* --- The corridor: one door per row --- */
        .es-vault-door {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.7rem 0;
            border-top: 1px solid rgba(23, 20, 16, 0.09);
        }
        .dark .es-vault-door { border-color: rgba(236, 231, 221, 0.09); }
        .es-vault-door:first-child { border-top: 0; }

        /* --- The register: which surface each door opens onto --- */
        .es-vault-reg-wrap { overflow-x: auto; }
        .es-vault-reg { width: 100%; min-width: 46rem; border-collapse: collapse; }
        .es-vault-reg th,
        .es-vault-reg td { padding: 0.7rem 0.7rem; text-align: start; vertical-align: middle; }
        .es-vault-reg thead th {
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #4d4740;
        }
        .dark .es-vault-reg thead th { color: #a49c8e; }
        .es-vault-reg tbody tr { border-top: 1px solid rgba(23, 20, 16, 0.1); }
        .dark .es-vault-reg tbody tr { border-color: rgba(236, 231, 221, 0.1); }
        .es-vault-reg tbody td { font-size: 0.82rem; color: #4d4740; }
        .dark .es-vault-reg tbody td { color: #a49c8e; }

        .es-vault-pip {
            display: inline-block;
            width: 0.55rem;
            height: 0.55rem;
            border-radius: 9999px;
            margin-inline-end: 0.45rem;
        }
        .es-vault-pip-open { background: #7a5a12; }
        .dark .es-vault-pip-open { background: #e5c26a; }
        .es-vault-pip-shut { background: transparent; border: 1px dashed rgba(23, 20, 16, 0.5); }
        .dark .es-vault-pip-shut { border-color: rgba(236, 231, 221, 0.5); }
        .es-vault-pip-key { background: transparent; border: 2px solid #7a5a12; }
        .dark .es-vault-pip-key { border-color: #e5c26a; }

        /* --- The visibility chooser, as it sits on the event form --- */
        .es-vault-opt {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.85rem 1rem;
            border: 1px solid rgba(236, 231, 221, 0.14);
            border-radius: 0.75rem;
            background: #16130d;
        }
        .es-vault-opt.is-on { border-color: rgba(229, 194, 106, 0.55); background: #1d1810; }
        .es-vault-opt-dot {
            flex: none;
            margin-top: 0.15rem;
            width: 1rem;
            height: 1rem;
            border-radius: 9999px;
            border: 2px solid rgba(236, 231, 221, 0.35);
        }
        .es-vault-opt.is-on .es-vault-opt-dot { border-color: #e5c26a; }
        .es-vault-opt.is-on .es-vault-opt-dot::after {
            content: "";
            display: block;
            width: 0.4rem;
            height: 0.4rem;
            margin: 0.15rem;
            border-radius: 9999px;
            background: #e5c26a;
        }

        /* --- The key field: the password prompt, recreated --- */
        .es-vault-key {
            border: 1px solid rgba(23, 20, 16, 0.18);
            border-radius: 0.5rem;
            background: #f5f2ec;
            color: #4d4740;
            letter-spacing: 0.4em;
        }
        .dark .es-vault-key { border-color: rgba(236, 231, 221, 0.18); background: #0f0e0c; color: #a49c8e; }

        /* --- The redaction: what the server never sends --- */
        .es-vault-redact {
            display: inline-block;
            height: 0.75rem;
            border-radius: 0.15rem;
            background-color: #2b2513;
            background-image: linear-gradient(90deg, #241c07, #3b3115);
        }
        .dark .es-vault-redact { background-color: #5a4d24; background-image: linear-gradient(90deg, #4a3f1e, #665728); }

        /* --- Chips --- */
        .es-vault-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            border: 1px solid rgba(23, 20, 16, 0.16);
            background: rgba(255, 255, 255, 0.7);
            color: #4d4740;
            font-size: 0.76rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .dark .es-vault-chip {
            border-color: rgba(236, 231, 221, 0.16);
            background: rgba(236, 231, 221, 0.05);
            color: #b3ab9d;
        }

        /* --- Links and buttons --- */
        .es-vault-link { color: #7a5a12; }
        .es-vault-link:hover { color: #171410; }
        .dark .es-vault-link { color: #e5c26a; }
        .dark .es-vault-link:hover { color: #ece7dd; }

        .es-vault-btn {
            background-color: #6b4f0d;
            color: #ffffff;
            box-shadow: 0 18px 36px -14px rgba(107, 79, 13, 0.5);
        }
        .es-vault-btn:hover { background-color: #543e08; box-shadow: 0 22px 44px -14px rgba(107, 79, 13, 0.6); }
        .dark .es-vault-btn { background-color: #e5c26a; color: #171410; }
        .dark .es-vault-btn:hover { background-color: #f0d288; }
        /* Inside an always-dark band the button is brass in BOTH modes, or the
           band stops being one physical object. */
        .es-vault-band .es-vault-btn {
            background-color: #e5c26a;
            color: #171410;
            box-shadow: 0 18px 36px -14px rgba(229, 194, 106, 0.35);
        }
        .es-vault-band .es-vault-btn:hover { background-color: #f0d288; }

        .es-vault-ghost {
            border: 1px solid rgba(23, 20, 16, 0.18);
            background: rgba(255, 255, 255, 0.65);
            color: #171410;
        }
        .es-vault-ghost:hover { border-color: rgba(122, 90, 18, 0.5); }
        .dark .es-vault-ghost { border-color: rgba(236, 231, 221, 0.18); background: rgba(236, 231, 221, 0.05); color: #ece7dd; }
        .dark .es-vault-ghost:hover { border-color: rgba(229, 194, 106, 0.5); }

        /* --- Hover states on cards that are links or details --- */
        .es-vault-hover:hover { border-color: rgba(122, 90, 18, 0.45); }
        .dark .es-vault-hover:hover { border-color: rgba(229, 194, 106, 0.45); }
        .es-vault-hover:hover .es-vault-hover-title,
        .es-vault-hover:hover .es-vault-hover-arrow { color: #7a5a12; }
        .dark .es-vault-hover:hover .es-vault-hover-title,
        .dark .es-vault-hover:hover .es-vault-hover-arrow { color: #e5c26a; }

        /* --- Shared-system recolours (brand blue by default) --- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(122, 90, 18, 0.13), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(229, 194, 106, 0.1), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(122, 90, 18, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(229, 194, 106, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #7a5a12; }
        .dark .es-dot.is-active .es-dot-pip { background: #e5c26a; }
        .es-vault-tip {
            border: 1px solid rgba(23, 20, 16, 0.12);
            background: #ffffff;
            color: #4d4740;
        }
        .dark .es-vault-tip { border-color: rgba(236, 231, 221, 0.12); background: #171614; color: #a49c8e; }

        /* --- Focus rings. No border-radius here: it would reshape the element. --- */
        #es-vault-page a:focus-visible,
        #es-vault-page summary:focus-visible,
        #es-vault-page button:focus-visible {
            outline: 2px solid #7a5a12;
            outline-offset: 3px;
        }
        .dark #es-vault-page a:focus-visible,
        .dark #es-vault-page summary:focus-visible,
        .dark #es-vault-page button:focus-visible {
            outline-color: #e5c26a;
        }
        .es-vault-band a:focus-visible,
        .es-vault-band summary:focus-visible,
        .es-vault-band button:focus-visible {
            outline-color: #e5c26a !important;
        }

        /* The plate carries no animation of its own (see ANTI-COLLISION above), and the
           shared primitives this page borrows - es-fade-up, es-marquee, animate-shimmer,
           es-aurora, [data-reveal] - are already gated inside marketing.css. Kept as a
           kill-switch so a future addition here cannot ship ungated. */
        @media (prefers-reduced-motion: reduce) {
            .es-vault-plate::after { animation: none !important; }
        }
    </style>

    @php
        // The four doors, in the order the event form lists them.
        $doors = [
            ['Public', 'Anyone', 'Listed on your schedule and everywhere it feeds.', false],
            ['Draft', 'Members', 'Hidden until you publish it. Not a permanent state.', false],
            ['Internal', 'Members', 'Hidden permanently. There is no publish button.', true],
            ['Unlisted', 'Link holders', 'Off the schedule, open to anyone holding the link.', true],
        ];

        // The register. Columns are the surfaces an event can reach; every cell is one
        // query filter in the codebase, not a guess.
        $register = [
            [
                'state' => 'Public',
                'plan' => 'Free',
                'cells' => [
                    ['open', 'Listed'],
                    ['open', 'Opens'],
                    ['open', 'Included'],
                    ['open', 'Listed'],
                    ['open', 'Open to everyone'],
                ],
            ],
            [
                'state' => 'Draft',
                'plan' => 'Free',
                'cells' => [
                    ['shut', 'Hidden'],
                    ['shut', 'Members only'],
                    ['shut', 'Excluded'],
                    ['shut', 'Excluded'],
                    ['shut', 'Members only'],
                ],
            ],
            [
                'state' => 'Internal',
                'plan' => 'Enterprise',
                'cells' => [
                    ['shut', 'Hidden'],
                    ['shut', 'Members only'],
                    ['shut', 'Excluded'],
                    ['shut', 'Excluded'],
                    ['shut', 'Members only'],
                ],
            ],
            [
                'state' => 'Unlisted',
                'plan' => 'Enterprise',
                'cells' => [
                    ['shut', 'Hidden'],
                    ['open', 'Opens with the link'],
                    ['shut', 'Excluded'],
                    ['shut', 'Excluded'],
                    ['key', 'Password required'],
                ],
            ],
        ];

        // "Your public schedule page" is deliberate: a signed-in member or admin sees all
        // four states on the schedule page, badged. Every cell below is the guest's view.
        $registerHeads = ['Visibility', 'Your public schedule page', 'Direct link', 'iCal, RSS, sitemap', 'Discovery search', 'Tickets and RSVP'];

        // The wording the event form itself shows under each option (messages.visibility_*_desc).
        $options = [
            ['Public', 'Listed publicly. Anyone can find and view it.', 'Free', false],
            ['Draft', 'Members only. Publish when you are ready.', 'Free', false],
            ['Internal', 'Members only. Never shown publicly.', 'Enterprise', false],
            ['Unlisted', 'Not listed. Anyone with the link can view, with an optional password.', 'Enterprise', true],
        ];

        // What the server empties out of the listing payload for a password-protected
        // event. One row per key that is actually set to null or [] before the array is
        // serialised - no more, because a list of plausible-looking fields is a guess.
        $redacted = [
            ['The event image', 12],
            ['Venue name', 8],
            ['Venue images', 13],
            ['Talent on the bill', 11],
            ['The agenda', 7],
            ['Fan videos', 9],
            ['Recent comments', 10],
        ];

        $keepers = ['The title', 'The date'];

        $audiences = [
            ['Members and subscribers', 'Previews, classes and sessions that belong to the people who pay for them. Internal keeps them off every public surface with no password to circulate.', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
            ['Invitation-only nights', 'A launch or a donor reception you hand out rather than announce. Send the link, send the password, and the event never touches your public calendar.', 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
            ['Company and staff events', 'All-hands, offsites and the staff party. They belong on the calendar your team reads and nowhere else, which is exactly what Internal is for.', 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
            ['Holds and rehearsals', 'Dates you are holding while a contract is still moving. Draft is free on every plan, so a hold sits on your calendar and nobody else\'s.', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ];

        $faqs = [
            [
                'q' => 'How do I make an event private?',
                'a' => 'Every event has a Visibility setting in its Details section, with four options. Public lists it. Draft keeps it to members until you publish it. Internal keeps it to members permanently. Unlisted keeps it off your schedule while the direct link still opens, with an optional password. Public and Draft are on every plan; Internal and Unlisted are Enterprise.',
            ],
            [
                'q' => 'Can I mix public and private events on the same schedule?',
                'a' => 'Yes. Visibility is stored on the event, not on the schedule, so Public, Draft, Internal and Unlisted events can sit side by side on one calendar. Public events carry on appearing normally while the hidden ones stay out of every public surface.',
            ],
            [
                'q' => 'Where exactly does a hidden event disappear from?',
                'a' => 'Your public schedule page and its calendar, the public iCal and RSS feeds, the XML sitemap, the discovery search on eventschedule.com, the upcoming-events block in a newsletter, and the generated event graphics. A hidden event also cannot be put behind an on-network promotion. The one door left open is the direct link, and only for Unlisted.',
            ],
            [
                'q' => 'Does a private event still sync to my own calendar?',
                'a' => 'Draft and Internal events are never pushed to a connected calendar at all. An Unlisted event is pushed, and it is written as private: private visibility on Google, private sensitivity on Outlook, and CLASS:PRIVATE on CalDAV. The public iCal and RSS feeds your visitors subscribe to leave all three out.',
            ],
            [
                'q' => 'Can I sell tickets to an unlisted event?',
                'a' => 'Yes, and this is the one thing worth knowing before you set it up. Ticket checkout and registration on an Unlisted event are accepted from signed-in members of the schedule, or from a guest who has entered the event password. If you want an unlisted event to sell to the people you sent the link to, give it a password.',
            ],
            [
                'q' => 'Can a sub-schedule be private?',
                'a' => 'No, and it is worth being clear about it. Sub-schedules organise and colour-code events on one link; they carry a name, a slug and a colour, and no visibility flag at all. Hiding is always a setting on the event itself.',
            ],
            [
                'q' => 'What happens to my hidden events if my plan lapses?',
                'a' => 'They stay hidden. Internal and Unlisted are Enterprise states, so on the next save of a schedule that is no longer Enterprise an Internal or an Unlisted event becomes a Draft. Nothing you hid is ever flipped public by a downgrade, and a restored backup keeps the same invariant, so an Internal event cannot come back published. The one setting worth re-checking is your schedule default: for a schedule that is no longer Enterprise, a default of Internal falls back to Draft, while a default of Unlisted falls back to Public for new events.',
            ],
            [
                'q' => 'Which plan includes private events?',
                'a' => 'Internal and Unlisted visibility are on the Enterprise plan. Public and Draft are on every plan, including Free, and a schedule-wide default visibility can be set on any of them. A selfhosted installation has every feature, so Internal and Unlisted are available there too.',
            ],
        ];

        $dotSections = [
            ['top', 'Four doors'],
            ['states', 'One setting'],
            ['register', 'The register'],
            ['unlisted', 'The unlisted link'],
            ['internal', 'Internal or Draft'],
            ['default', 'The house default'],
            ['rest', 'Every other surface'],
            ['who', 'Who uses it'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];
    @endphp

    <div id="es-vault-page" class="es-vault-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the corridor of doors                               -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(122, 90, 18, 0.22), rgba(122, 90, 18, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(229, 194, 106, 0.14), rgba(229, 194, 106, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 es-vault-ghost mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="es-vault-accent h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                        <span class="es-vault-muted text-sm font-medium tracking-wide">Private events</span>
                    </div>

                    <h1 class="es-balance es-vault-ink mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">A vault is not one door.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">It is <span class="es-vault-accent">four</span> of them.</span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-vault-muted mb-10 max-w-xl text-lg sm:text-xl">
                        Visibility is a setting on the event, not on the schedule. Public, Draft, Internal, or Unlisted with a password, and the rest of your calendar carries on exactly as it did.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="{{ app_url('/sign_up') }}" class="es-vault-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Get started free
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                        <a href="{{ route('marketing.docs.creating_events') }}#privacy" class="es-vault-ghost group inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            Read the Privacy guide
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- The corridor: one plate per door, and who it lets through -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-vault-card p-6 sm:p-7">
                        <div class="mb-4 flex flex-wrap items-baseline justify-between gap-2">
                            <h2 class="es-vault-ink text-lg font-bold">One event. Four ways to leave it.</h2>
                            <span class="es-vault-muted font-mono text-xs">Set per event</span>
                        </div>

                        @foreach ($doors as [$doorName, $doorWho, $doorNote, $doorEnt])
                            <div class="es-vault-door">
                                <span class="es-vault-plate"><span>{{ $doorName }}</span></span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="es-vault-ink text-sm font-bold">{{ $doorWho }}</span>
                                        @if ($doorEnt)
                                            <span class="es-vault-plan">Enterprise</span>
                                        @else
                                            <span class="es-vault-plan es-vault-plan-free">Free</span>
                                        @endif
                                    </div>
                                    <p class="es-vault-muted text-xs">{{ $doorNote }}</p>
                                </div>
                            </div>
                        @endforeach

                        <p class="es-vault-muted mt-5 border-t pt-4 text-xs" style="border-color: rgba(122, 90, 18, 0.2);">
                            Members are the people signed in to your schedule. Everyone else meets the door you chose.
                        </p>
                    </div>
                </div>
            </div>

            <!-- What people actually keep behind a door -->
            <div class="es-fade-up es-d-4 mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach (['Board meetings', 'Member previews', 'Donor receptions', 'Staff parties', 'Rehearsals', 'Product launches', 'Team offsites', 'Private dinners', 'Holds', 'Dress runs'] as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-vault-chip">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. One setting on the event (fixed-dark band)                -->
    <!-- ============================================================ -->
    <section id="states" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-vault-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 28% 28%, rgba(229, 194, 106, 0.13), rgba(229, 194, 106, 0) 60%);"></div>
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <span class="es-vault-plate es-vault-plate-lg mb-6" data-reveal aria-hidden="true"><span>02</span></span>
                    <p class="es-vault-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The setting</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        It is one field on the event, <span class="es-vault-lit">not a plan you move to.</span>
                    </h2>
                    <p class="mt-5 text-lg es-vault-band-soft" data-reveal style="--reveal-delay: 0.15s;">
                        Open the event, open Details, choose a door. Nothing else about the event changes.
                    </p>
                </div>

                <div class="grid items-start gap-8 lg:grid-cols-[1.1fr_1fr]">
                    <div class="space-y-3" data-reveal-group="90">
                        @foreach ($options as [$optName, $optDesc, $optPlan, $optOn])
                            <div class="es-vault-opt @if ($optOn) is-on @endif" data-reveal>
                                <span class="es-vault-opt-dot" aria-hidden="true"></span>
                                <div class="min-w-0">
                                    <div class="mb-1 flex flex-wrap items-center gap-2">
                                        <span class="font-bold es-vault-band-ink">{{ $optName }}</span>
                                        @if ($optPlan === 'Enterprise')
                                            <span class="es-vault-plan">Enterprise</span>
                                        @else
                                            <span class="es-vault-plan es-vault-plan-free">Free</span>
                                        @endif
                                    </div>
                                    <p class="text-sm es-vault-band-muted">{{ $optDesc }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="es-vault-card p-6" data-reveal="panel">
                        <p class="es-vault-tag mb-4">What follows the choice</p>
                        <ul class="space-y-4 text-sm es-vault-band-muted">
                            <li class="flex gap-3">
                                <svg aria-hidden="true" class="es-vault-lit mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span>The password field appears only for Unlisted, because a password is only meaningful on a link anyone can hold.</span>
                            </li>
                            <li class="flex gap-3">
                                <svg aria-hidden="true" class="es-vault-lit mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span>Move an event off Unlisted and the stored password is cleared, so a stale one can never keep a published event locked.</span>
                            </li>
                            <li class="flex gap-3">
                                <svg aria-hidden="true" class="es-vault-lit mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span>Switching something hidden back to Public warns you first: saving will make this event visible to everyone.</span>
                            </li>
                            <li class="flex gap-3">
                                <svg aria-hidden="true" class="es-vault-lit mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span>The quick Publish action is offered for a Draft and never for an Internal event, in the interface or on the route behind it.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The register: which door opens onto which surface         -->
    <!-- ============================================================ -->
    <section id="register" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <span class="es-vault-plate es-vault-plate-lg mb-6" data-reveal aria-hidden="true"><span>03</span></span>
                <p class="es-vault-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The register</p>
                <h2 class="es-balance es-vault-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Every door, and <span class="es-vault-accent">what it opens onto.</span>
                </h2>
                <p class="es-vault-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    This is the whole feature on one page. Each column is a real surface, and each cell is the filter that surface is built from.
                </p>
            </div>

            <div class="es-vault-card p-5 sm:p-7" data-reveal="panel">
                <div class="es-vault-reg-wrap">
                    <table class="es-vault-reg">
                        <caption class="sr-only">Where an event appears for each of the four visibility states</caption>
                        <thead>
                            <tr>
                                @foreach ($registerHeads as $head)
                                    <th scope="col">{{ $head }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($register as $row)
                                <tr>
                                    <th scope="row" class="whitespace-nowrap">
                                        <span class="es-vault-plate"><span>{{ $row['state'] }}</span></span>
                                        <span class="es-vault-plan @if ($row['plan'] === 'Free') es-vault-plan-free @endif ms-2">{{ $row['plan'] }}</span>
                                    </th>
                                    @foreach ($row['cells'] as [$cellKind, $cellText])
                                        <td>
                                            <span class="es-vault-pip @if ($cellKind === 'open') es-vault-pip-open @elseif ($cellKind === 'key') es-vault-pip-key @else es-vault-pip-shut @endif" aria-hidden="true"></span>{{ $cellText }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mx-auto mt-8 grid max-w-5xl gap-4 md:grid-cols-3" data-reveal-group="90">
                <div class="es-vault-card p-5" data-reveal="panel">
                    <p class="es-vault-tag mb-2">Members only</p>
                    <p class="es-vault-muted text-sm">Every cell above is what a guest gets. Signed in, you and your members see all four states on the schedule page, badged Draft or Internal. A Draft or Internal event will not open on a direct link either.</p>
                </div>
                <div class="es-vault-card p-5" data-reveal="panel">
                    <p class="es-vault-tag mb-2">Password required</p>
                    <p class="es-vault-muted text-sm">Checkout and registration on an Unlisted event are taken from members, or from a guest who has entered the password. Selling to an invite list means setting one.</p>
                </div>
                <div class="es-vault-card p-5" data-reveal="panel">
                    <p class="es-vault-tag mb-2">A separate gate</p>
                    <p class="es-vault-muted text-sm">Whatever the visibility, an event only appears on a schedule that has accepted it. Acceptance is its own gate and applies to all four states.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. The unlisted link and its password                        -->
    <!-- ============================================================ -->
    <section id="unlisted" class="scroll-mt-24 border-y py-20 lg:py-28" style="border-color: rgba(122, 90, 18, 0.16);">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <span class="es-vault-plate es-vault-plate-lg mb-6" data-reveal aria-hidden="true"><span>04</span></span>
                <p class="es-vault-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The numbered box</p>
                <h2 class="es-balance es-vault-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Hand out a link. <span class="es-vault-accent">Or a link and a key.</span>
                </h2>
                <p class="es-vault-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    An Unlisted event is off your schedule, out of the feeds and out of search, and its own URL still works. Add a password and the URL alone stops being enough.
                </p>
            </div>

            <div class="grid items-start gap-8 lg:grid-cols-2">
                <!-- The prompt, as a guest meets it -->
                <div data-reveal="panel">
                    <div class="es-vault-card p-6 sm:p-8">
                        <p class="es-vault-tag mb-5">What a link holder sees first</p>
                        <div class="mx-auto max-w-sm rounded-xl border p-6 text-center" style="border-color: rgba(122, 90, 18, 0.22);" aria-hidden="true">
                            <svg aria-hidden="true" class="es-vault-muted mx-auto mb-4 h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                            <p class="es-vault-ink mb-1 text-lg font-bold">Founders Dinner</p>
                            <p class="es-vault-muted mb-6 text-sm">This event requires a password to view.</p>
                            <div class="es-vault-key mb-3 px-4 py-2.5 text-center font-mono text-sm">&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;</div>
                            <div class="es-vault-btn rounded-lg px-4 py-2.5 text-sm font-semibold">Submit</div>
                        </div>
                        <p class="es-vault-muted mt-6 text-sm">
                            The title, and nothing else. No description, no venue, no line-up, no price. Unlock it once and the browser remembers for that visitor, on that device, for the rest of the session.
                        </p>
                    </div>
                </div>

                <!-- The redaction: what never leaves the server -->
                <div data-reveal="panel">
                    <div class="es-vault-card p-6 sm:p-8">
                        <p class="es-vault-tag mb-2">What is not in the page</p>
                        <h3 class="es-vault-ink mb-4 text-xl font-bold">The gate is not a curtain.</h3>
                        <p class="es-vault-muted mb-6 text-sm">
                            A locked event does not render its own page with the details hidden; the request is answered by the prompt above instead. And where a locked event does appear in a listing, the payload is built with these keys emptied before it is serialised, rather than shipped to the browser and covered over.
                        </p>

                        <ul class="space-y-2.5">
                            @foreach ($redacted as [$fieldName, $barWidth])
                                <li class="flex items-center gap-3">
                                    <span class="es-vault-muted w-44 flex-none text-xs">{{ $fieldName }}</span>
                                    <span class="es-vault-redact" style="width: {{ $barWidth * 8 }}px;" aria-hidden="true"></span>
                                    <span class="es-vault-muted font-mono text-[0.6rem] uppercase tracking-widest">null</span>
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-6 border-t pt-5" style="border-color: rgba(122, 90, 18, 0.2);">
                            <p class="es-vault-tag mb-3">What survives</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($keepers as $keeper)
                                    <span class="es-vault-chip">{{ $keeper }}</span>
                                @endforeach
                            </div>
                            <p class="es-vault-muted mt-4 text-sm">Of the fan content only the counts survive, never a photo, a video or a line of a comment.</p>
                            <p class="es-vault-muted mt-3 text-sm">The locked page is also marked noindex and nofollow, and its social preview tags carry the password notice in place of the event: no name, no description, and the generic Event Schedule card rather than your image. Pasting the link into a chat gives nothing away.</p>
                            <p class="es-vault-muted mt-3 text-sm">And if your schedule is on the free plan and shows ads, none are ever rendered in front of a password prompt.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Internal or Draft                                         -->
    <!-- ============================================================ -->
    <section id="internal" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <span class="es-vault-plate es-vault-plate-lg mb-6" data-reveal aria-hidden="true"><span>05</span></span>
                <p class="es-vault-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The staff door</p>
                <h2 class="es-balance es-vault-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    Draft means not yet. <span class="es-vault-accent">Internal means not ever.</span>
                </h2>
                <p class="es-vault-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    They hide an event the same way. What differs is what happens next, and the product takes that difference seriously.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-2" data-reveal-group="110">
                <div class="es-vault-card flex flex-col p-7" data-reveal="panel">
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <span class="es-vault-plate"><span>Draft</span></span>
                        <span class="es-vault-plan es-vault-plan-free">Free</span>
                    </div>
                    <h3 class="es-vault-ink mb-3 text-xl font-bold">A published event that has not been published yet</h3>
                    <p class="es-vault-muted mb-4 text-sm">
                        Build the event, hold the date, get the copy right. Your members see it on the calendar with a Draft badge, and one Publish action turns it into a normal public event and starts its calendar sync.
                    </p>
                    <p class="es-vault-muted mt-auto text-sm">Free on every plan, on any schedule type.</p>
                </div>

                <div class="es-vault-card flex flex-col p-7" data-reveal="panel">
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <span class="es-vault-plate"><span>Internal</span></span>
                        <span class="es-vault-plan">Enterprise</span>
                    </div>
                    <h3 class="es-vault-ink mb-3 text-xl font-bold">An event that was never for the public</h3>
                    <p class="es-vault-muted mb-4 text-sm">
                        The board meeting, the staff party, the rehearsal. Internal carries its own badge on your calendar, is never offered a Publish action, and refuses one if a request is crafted by hand. There is no password to circulate because there is no link to circulate.
                    </p>
                    <p class="es-vault-muted mt-auto text-sm">Restoring a backup re-applies the rule, so an Internal event cannot come back published.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. The house default (fixed-dark band)                       -->
    <!-- ============================================================ -->
    <section id="default" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-vault-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 72% 60%, rgba(229, 194, 106, 0.12), rgba(229, 194, 106, 0) 60%);"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <span class="es-vault-plate es-vault-plate-lg mb-6" data-reveal aria-hidden="true"><span>06</span></span>
                    <p class="es-vault-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The house default</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Decide once <span class="es-vault-lit">which door is the default.</span>
                    </h2>
                    <p class="mt-5 text-lg es-vault-band-soft" data-reveal style="--reveal-delay: 0.15s;">
                        Your schedule's Advanced settings hold the state every new event starts in. Set it to Draft and nothing goes out by accident.
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    <div class="es-vault-card p-6" data-reveal="panel">
                        <p class="es-vault-tag mb-3">On the schedule</p>
                        <h3 class="mb-2 text-lg font-bold es-vault-band-ink">A starting state</h3>
                        <p class="text-sm es-vault-band-muted">Public, Draft, Internal or Unlisted. New events open on it, and you can change any individual event afterwards.</p>
                    </div>
                    <div class="es-vault-card p-6" data-reveal="panel">
                        <p class="es-vault-tag mb-3">On submissions</p>
                        <h3 class="mb-2 text-lg font-bold es-vault-band-ink">Guests cannot choose</h3>
                        <p class="text-sm es-vault-band-muted">The public request form cannot set a visibility of its own. Anything sent in lands on your default, whatever the form was made to say.</p>
                    </div>
                    <div class="es-vault-card p-6" data-reveal="panel">
                        <p class="es-vault-tag mb-3">On the API</p>
                        <h3 class="mb-2 text-lg font-bold es-vault-band-ink">The same default</h3>
                        <p class="text-sm es-vault-band-muted">An API call that names no visibility inherits the schedule default, so a drafts-by-default schedule never publishes something silently.</p>
                    </div>
                </div>

                <div class="es-vault-card mt-6 p-6 sm:p-8" data-reveal="panel">
                    <div class="flex flex-col gap-6 sm:flex-row sm:items-center">
                        <span class="es-vault-plate es-vault-plate-lg self-start"><span>On downgrade</span></span>
                        <div class="min-w-0 flex-1">
                            <h3 class="mb-2 text-lg font-bold es-vault-band-ink">A lapse never publishes what you hid</h3>
                            <p class="text-sm es-vault-band-muted">
                                Internal and Unlisted are Enterprise states. If the plan lapses, the next save of an Internal or Unlisted event turns it into a Draft, so it stays hidden. The default is the one thing to check: on a schedule that is no longer Enterprise a default of Internal falls back to Draft, but a default of Unlisted falls back to Public. Leave Draft as the default and a lapse cannot change what a new event starts as.
                            </p>
                        </div>
                    </div>
                </div>

                <p class="mt-10 text-center es-vault-band-soft" data-reveal>
                    Selfhosting? A selfhosted installation has every feature, so all four doors are open to you.
                    <a href="{{ marketing_url('/selfhost') }}" class="es-vault-lit inline-flex items-center gap-1 font-semibold transition-all hover:gap-2">
                        Selfhost Event Schedule
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Every other surface: bento                                -->
    <!-- ============================================================ -->
    <section id="rest" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <span class="es-vault-plate es-vault-plate-lg mb-6" data-reveal aria-hidden="true"><span>07</span></span>
                <p class="es-vault-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Every other surface</p>
                <h2 class="es-balance es-vault-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    A door is only shut if <span class="es-vault-accent">every wall holds.</span>
                </h2>
                <p class="es-vault-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Hiding an event is not much use if it leaks out of a feed, a round-up or a shared image. Here is the rest of the wall.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">
                <!-- 1 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-vault-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-vault-ink text-xl font-bold">Feeds, sitemap and discovery</h3>
                                <span class="es-vault-plan es-vault-plan-free">Free</span>
                            </div>
                            <p class="es-vault-muted mb-4">The public iCal feed, the RSS feed, the XML sitemap and the search on eventschedule.com are all built from one filter: not a draft, not unlisted, not cancelled, and accepted by the schedule. The feeds and the sitemap turn away anything still holding a password as well.</p>
                            <p class="es-vault-muted text-sm">Four separate surfaces, one rule. That is deliberate: a privacy setting that each surface interprets for itself is a privacy setting that eventually gets one of them wrong.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-vault-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-vault-ink text-xl font-bold">Newsletters</h3>
                                <span class="es-vault-plan es-vault-plan-free">Free</span>
                            </div>
                            <p class="es-vault-muted">The upcoming-events block a newsletter builds for you skips drafts, unlisted, cancelled and password-protected events, so a round-up cannot carry one out to your whole list.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-vault-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-vault-ink text-xl font-bold">Shareable graphics</h3>
                                <span class="es-vault-plan">Pro</span>
                            </div>
                            <p class="es-vault-muted">Generated event graphics are drawn from the same public set, so a hidden event is not in one and cannot end up in an image you post. The scheduled graphic email, an Enterprise extra, is built from that same query.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-vault-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-vault-ink text-xl font-bold">Promotions and ads</h3>
                            </div>
                            <p class="es-vault-muted mb-4">A draft or unlisted event cannot be put behind an on-network promotion. The purchase form refuses it, the submit behind the form checks again before the card is charged, and the serving pool re-checks every time it picks a promotion, so an event you hide after a campaign starts stops being shown. On the other side of the same wall, a free schedule's ads are never rendered in front of a password prompt.</p>
                            <p class="es-vault-muted text-sm">
                                A refusal at the door and a filter at serve time, which together are the difference between an event that is hard to find and an event that is not there.
                            </p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-vault-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-vault-ink text-xl font-bold">Backups</h3>
                                <span class="es-vault-plan es-vault-plan-free">Free</span>
                            </div>
                            <p class="es-vault-muted">Export your schedule and import it again and the visibility rule is re-applied on the way in, so a hand-edited or legacy file cannot restore an Internal event as a public one.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-vault-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-vault-ink text-xl font-bold">Sync to your own calendar</h3>
                                <span class="es-vault-plan es-vault-plan-free">Free</span>
                            </div>
                            <p class="es-vault-muted mb-4">Draft and Internal events are never pushed to a connected calendar. An Unlisted event is pushed, and it is written as private: private visibility on Google, private sensitivity on Outlook, and CLASS:PRIVATE on CalDAV.</p>
                            <p class="es-vault-muted text-sm">
                                That is the distinction that matters. Your own calendar is not a public surface, so an unlisted event belongs on it, marked for what it is.
                                <a href="{{ marketing_url('/features/calendar-sync') }}" class="es-vault-link font-medium hover:underline">How calendar sync works</a>
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
    <!-- 8. Who uses it                                               -->
    <!-- ============================================================ -->
    <section id="who" class="scroll-mt-24 border-t py-20 lg:py-28" style="border-color: rgba(122, 90, 18, 0.16);">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <span class="es-vault-plate es-vault-plate-lg mb-6" data-reveal aria-hidden="true"><span>08</span></span>
                <h2 class="es-balance es-vault-ink mb-4 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    What people keep <span class="es-vault-accent">behind the door</span>
                </h2>
                <p class="es-vault-muted text-lg sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    The same calendar, with a private half.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group="80">
                @foreach ($audiences as [$audName, $audBody, $audIcon])
                    <div class="es-vault-card flex flex-col p-6" data-reveal="panel">
                        <span class="es-vault-accent mb-5 inline-flex h-12 w-12 items-center justify-center rounded-xl" style="border: 1px solid rgba(122, 90, 18, 0.28);">
                            <svg aria-hidden="true" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $audIcon }}" />
                            </svg>
                        </span>
                        <h3 class="es-vault-ink mb-2 text-lg font-bold">{{ $audName }}</h3>
                        <p class="es-vault-muted text-sm">{{ $audBody }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Related features                                          -->
    <!-- ============================================================ -->
    <section class="border-t py-20" style="border-color: rgba(122, 90, 18, 0.16);">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-vault-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card
                        name="Ticketing and QR Check-ins"
                        description="Sell tickets and scan QR codes for fast check-ins"
                        :url="marketing_url('/features/ticketing')"
                        icon-color="blue"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Online Events"
                        description="Host virtual events with any streaming platform"
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
                        description="Collect additional info from ticket buyers"
                        :url="marketing_url('/features/custom-fields')"
                        icon-color="amber"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-vault-link inline-flex items-center font-medium hover:underline">
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
    <!-- 10. FAQ                                                      -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="scroll-mt-24 border-t py-20 lg:py-28" style="border-color: rgba(122, 90, 18, 0.16);">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <span class="es-vault-plate es-vault-plate-lg mb-6" data-reveal aria-hidden="true"><span>09</span></span>
                <h2 class="es-balance es-vault-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-vault-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    Everything worth knowing before you hide something.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-vault-hover es-vault-card group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-vault-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-vault-accent flex-none font-mono text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-vault-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-vault-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-vault-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 11. Related pages                                            -->
    <!-- ============================================================ -->
    <section class="border-t py-16" style="border-color: rgba(122, 90, 18, 0.16);">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-vault-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4" data-reveal-group="70">
                @foreach ([['/features/sub-schedules', 'Sub-schedules'], ['/features/calendar-sync', 'Calendar Sync'], ['/features/embed-calendar', 'Embed Calendar'], ['/features/newsletters', 'Newsletters']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" class="es-vault-hover es-vault-card group flex flex-col p-5 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-vault-hover-title es-vault-ink mb-3 text-sm font-semibold transition-colors">{{ $relName }}</span>
                        <span class="es-vault-hover-arrow es-vault-muted mt-auto inline-flex items-center gap-1 text-xs font-medium transition-colors">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 12. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-vault-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 18%, rgba(229, 194, 106, 0.16), rgba(229, 194, 106, 0) 60%);"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    <span class="es-vault-plate es-vault-plate-lg mb-6" aria-hidden="true"><span>Free to start</span></span>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Some events are <span class="es-vault-lit">nobody else's business.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg es-vault-band-soft">
                        Publishing your calendar and holding dates as drafts are free forever. Internal and Unlisted visibility come with Enterprise, and a selfhosted installation has all four.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-vault-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Get started free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="mt-6 text-sm es-vault-band-muted">No credit card required</p>
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
                        <span class="es-vault-tip pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
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
