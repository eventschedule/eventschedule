<x-marketing-layout>
    <x-slot name="title">Google Calendar Sync & Integration - Event Schedule</x-slot>
    <x-slot name="description">Real-time two-way sync with Google Calendar. OAuth authentication, webhook updates, and multi-calendar support for smooth event management.</x-slot>
    <x-slot name="breadcrumbTitle">Google Calendar</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Google Calendar Sync",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Calendar Synchronization Software",
        "operatingSystem": "Web",
        "description": "Real-time two-way sync with Google Calendar. OAuth authentication, webhook updates, and multi-calendar support for smooth event management.",
        "featureList": [
            "Two-way Google Calendar sync, free on every plan",
            "Google OAuth connection with automatic token refresh",
            "Push notifications from Google, with a fifteen-minute incremental sweep as a backstop",
            "Per-schedule direction: to Google, from Google, both, or off",
            "Choose which Google Calendar each schedule syncs with",
            "Per-schedule policy for events deleted in Google Calendar",
            "Calendar description template for the text of the Google entry",
            "An address typed into Google Calendar becomes a venue on your schedule",
            "Followers and team members can sync a schedule to a Google Calendar of their own",
            "Add to Google Calendar links on public event pages"
        ],
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Included free on every plan"
        },
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
           For-google-calendar "The Invitation" styles.

           THE PAGE IS ONE CALENDAR ENTRY, READ FIELD BY FIELD. A Google
           Calendar event is an invitation: a title, a when, a where, a
           note and a visibility. So the signature object is the entry
           itself, with every field annotated in the margin by where it
           came from in Event Schedule. The metaphor and the feature
           story are then the same sentence: what is on the invitation
           IS what crosses the wire, and the ledger table further down
           is the same object again, in rows.

           DELIBERATELY NOT THE ITINERARY. /features/calendar-sync was
           just rebuilt as "The Round Trip" and owns legs, boarding,
           fares and a two-track rail for sync-in-general across Google,
           Outlook and CalDAV. This page is Google only, so it takes the
           things that are Google-specific and nothing else: the OAuth
           consent, the push notification channel, the per-PERSON
           calendar (Google is the only provider with one - see
           Role::getMembersWithCalendarSync() and
           role_user.google_calendar_id), and the Add to Google Calendar
           link on public event pages.

           THE PER-PERSON CALENDAR IS FREE AND IT IS NOT A TEAM-ONLY
           FEATURE. Do not re-label it Enterprise. The only UI that
           writes role_user.google_calendar_id for a non-owner is "Sync
           to my calendar" on the FOLLOWING list
           (role/following_table.blade.php, and RoleController::
           following() scopes that list to level='follower'), plus the
           member block in role/edit.blade.php for a non-owner editor.
           GoogleCalendarController::memberSync() checks only for a
           google_token and a role_user row: no isPro / isEnterprise
           anywhere. What IS Enterprise is having more than one team
           member at all (RoleController:1210, capped at 5), so the
           section says that in prose instead of pinning an Enterprise
           pill on a free capability.

           COLOUR: Google Calendar's own palette, which is legitimate
           here because the whole page is about that one product and the
           four-colour G is a real brand logo. #1558c0 is the light-mode
           accent (6.20 on this ground, 6.59 on white) and #8ab4f8 is
           the dark-mode one (8.90 on #101215); #1a73e8 appears only as
           the entry's colour rail, never as text, because it measures
           4.24 on the page ground. NO gradient headline text anywhere:
           solid accent words only, so no bright stop is ever scored
           against a light ground. The previous version of this page led
           with the shared brand blue/sky/cyan gradient, which is house
           chrome rather than an identity.

           THE ENTRY IS A FIXED PHYSICAL OBJECT, so it is pinned:
           .es-invite-note and everything inside it render IDENTICALLY
           with `.dark` on and off (verified with the verifier's --bands
           flag). Nothing inside it may use a `dark:` utility or a shared
           class that carries its own `.dark` rule, hence its own ink,
           muted, tag, rule and chip classes rather than the page ones.
           The dark bands are fixed objects too, so .es-invite-band
           re-inks .grid-overlay, .animate-shimmer and
           .es-claim:focus-within after the base rules.

           NEVER use text-gray-500 on this ground - #6b7280 measures
           only 4.4 on #f7f8fa. Use .es-invite-muted (7.65).

           BLADE RULE for this block: never use @supports probes here.
           A "#" hex inside a parenthesized at-rule condition breaks
           Blade compilation of every later parenthesized directive.
           ============================================================== */

        /* --- Ground and ink --- */
        .es-invite-page { background-color: #f7f8fa; color: #202124; }
        .dark .es-invite-page { background-color: #101215; color: #e8eaed; }
        .es-invite-ink { color: #202124; }
        .dark .es-invite-ink { color: #e8eaed; }
        .es-invite-muted { color: #4c5054; }
        .dark .es-invite-muted { color: #9aa0a6; }
        .es-invite-accent { color: #1558c0; }
        .dark .es-invite-accent { color: #8ab4f8; }
        /* Always-bright ink, muted and accent for the fixed-dark bands, in both
           colour modes. These have to be real classes: no build runs during a
           rebuild, so a `es-invite-band-muted` that is not already in
           public/build/assets/marketing-app-*.css does nothing at all and the
           text falls back to the page ink - measured at 1.15 on the band. */
        .es-invite-band-ink { color: #e8eaed; }
        .es-invite-band-muted { color: #a8b0b8; }
        .es-invite-lit { color: #8ab4f8; }

        /* A second surface, one step off the page ground. */
        .es-invite-shade { background-color: #eef1f6; }
        .dark .es-invite-shade { background-color: #191b1f; }

        /* --- Hairlines. Page-local classes, NOT Tailwind arbitrary values:
               no build runs during a rebuild, so a border-[rgba(...)] that is
               not already in the built CSS silently does nothing and every
               section separator vanishes. --- */
        .es-invite-rule-t { border-top: 1px solid rgba(32, 33, 36, 0.12); }
        .dark .es-invite-rule-t { border-top-color: rgba(232, 234, 237, 0.12); }
        .es-invite-rule-y {
            border-top: 1px solid rgba(32, 33, 36, 0.12);
            border-bottom: 1px solid rgba(32, 33, 36, 0.12);
        }
        .dark .es-invite-rule-y {
            border-top-color: rgba(232, 234, 237, 0.12);
            border-bottom-color: rgba(232, 234, 237, 0.12);
        }

        /* --- Cards --- */
        .es-invite-card {
            border: 1px solid rgba(32, 33, 36, 0.12);
            border-radius: 1rem;
            background: #ffffff;
        }
        .dark .es-invite-card {
            border-color: rgba(232, 234, 237, 0.12);
            background: rgba(232, 234, 237, 0.04);
        }
        .es-invite-band .es-invite-card {
            border-color: rgba(232, 234, 237, 0.14);
            background: rgba(232, 234, 237, 0.05);
        }

        /* --- Fixed-dark bands --- */
        .es-invite-band {
            background-color: #0d1420;
            background-image: radial-gradient(120% 100% at 50% 0%, #16213a 0%, #0f1622 55%, #090c11 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(232, 234, 237, 0.05);
        }
        /* Shared classes that flip with the colour mode inside a band. */
        .es-invite-band .grid-overlay {
            background-image:
                linear-gradient(rgba(232, 234, 237, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(232, 234, 237, 0.05) 1px, transparent 1px);
        }
        .es-invite-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-invite-band .es-claim:focus-within {
            border-color: rgba(138, 180, 248, 0.75);
            box-shadow: 0 0 0 4px rgba(138, 180, 248, 0.22);
        }

        /* ==============================================================
           THE INVITATION. A Google Calendar entry, rendered as the
           physical thing it is. Pinned: identical with `.dark` on and
           off, so every token below is literal and none of its children
           may carry a dark: utility.
           ============================================================== */
        .es-invite-note {
            position: relative;
            border: 1px solid #dadce0;
            border-radius: 1rem;
            background: #ffffff;
            box-shadow: 0 24px 48px -28px rgba(15, 23, 42, 0.45);
            overflow: hidden;
            /* Padding lives here rather than on utilities: `ps-7` and `sm:ps-8`
               are not in the built CSS, and the colour rail needs the extra
               inline-start room in every direction of text. */
            padding: 1.5rem;
            padding-inline-start: 1.9rem;
        }
        /* Google Calendar paints a colour rail down the side of an entry. */
        .es-invite-note::before {
            content: "";
            position: absolute;
            inset-inline-start: 0;
            top: 0;
            bottom: 0;
            width: 5px;
            background: #1a73e8;
        }
        .es-invite-note-ink { color: #202124; }
        .es-invite-note-muted { color: #5f6368; }
        .es-invite-note-rule { border-top: 1px solid #dadce0; }
        .es-invite-note-tag {
            font-size: 0.63rem;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #5f6368;
        }
        .es-invite-note-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.2rem 0.55rem;
            border-radius: 9999px;
            background: #e8f0fe;
            color: #174ea6;
            font-size: 0.66rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .es-invite-note-row {
            display: grid;
            grid-template-columns: 4.6rem 1fr;
            gap: 0.35rem 0.9rem;
            align-items: baseline;
        }
        .es-invite-note-key {
            font-size: 0.66rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #5f6368;
        }
        .es-invite-note-val {
            font-size: 0.9rem;
            font-weight: 600;
            color: #202124;
        }
        /* The margin annotation: where this field came from over here. */
        .es-invite-note-src {
            display: block;
            margin-top: 0.15rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.67rem;
            font-weight: 600;
            letter-spacing: 0.01em;
            color: #1558c0;
        }
        .es-invite-note-mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.78rem;
            color: #5f6368;
        }
        /* Three small entries, one per member calendar. */
        .es-invite-note-mini {
            border: 1px solid #dadce0;
            border-radius: 0.75rem;
            background: #ffffff;
            padding: 0.95rem 1rem;
        }
        .es-invite-note-mini-bar {
            height: 3px;
            width: 2.2rem;
            border-radius: 2px;
            background: #1a73e8;
            margin-bottom: 0.6rem;
        }
        /* The finale's invitation: the one that has not been written yet. Same paper,
           dashed edge, ruled lines where the values will go. Literal tokens only,
           because it sits inside a fixed-dark band and is pinned with the rest. */
        /* Width lives in a real rule: max-w-[17rem] is NOT in the built marketing CSS
           and no build runs during a rebuild, so it would silently do nothing. */
        .es-invite-blank-wrap { max-width: 17rem; }
        .es-invite-note-blank { border-style: dashed; }
        .es-invite-blank-line {
            display: block;
            height: 0.5rem;
            border-radius: 2px;
            background: #e8eaed;
        }
        .es-invite-blank-short { width: 58%; }

        /* --- Section stamp: a perforated mark, numbered --- */
        .es-invite-stamp {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.32rem 0.75rem;
            border: 1px dashed rgba(32, 33, 36, 0.3);
            border-radius: 0.35rem;
            background: #ffffff;
            color: #202124;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }
        .dark .es-invite-stamp {
            border-color: rgba(232, 234, 237, 0.3);
            background: rgba(232, 234, 237, 0.05);
            color: #e8eaed;
        }
        .es-invite-band .es-invite-stamp {
            border-color: rgba(232, 234, 237, 0.3);
            background: rgba(232, 234, 237, 0.05);
            color: #e8eaed;
        }
        .es-invite-stamp::before {
            content: "";
            width: 0.42rem;
            height: 0.42rem;
            border-radius: 1px;
            background: #1558c0;
        }
        .dark .es-invite-stamp::before { background: #8ab4f8; }
        .es-invite-band .es-invite-stamp::before { background: #8ab4f8; }

        /* --- Eyebrow --- */
        .es-invite-tag {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            color: #4c5054;
        }
        .dark .es-invite-tag { color: #9aa0a6; }
        .es-invite-band .es-invite-tag { color: #8ab4f8; }

        /* --- Plan pills --- */
        .es-invite-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.25rem;
            border: 1px solid rgba(21, 88, 192, 0.45);
            color: #1558c0;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }
        .dark .es-invite-plan { border-color: rgba(138, 180, 248, 0.45); color: #8ab4f8; }
        .es-invite-band .es-invite-plan { border-color: rgba(138, 180, 248, 0.45); color: #8ab4f8; }

        /* --- The ledger: one row per field on the invitation --- */
        .es-invite-table { width: 100%; border-collapse: collapse; }
        .es-invite-table th,
        .es-invite-table td { padding: 0.7rem 0.7rem; vertical-align: top; text-align: start; }
        .es-invite-table thead th {
            font-size: 0.66rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #4c5054;
            border-bottom: 1px solid rgba(32, 33, 36, 0.14);
            white-space: nowrap;
        }
        .dark .es-invite-table thead th { color: #9aa0a6; border-bottom-color: rgba(232, 234, 237, 0.14); }
        /* Every row of the ledger is a line of the entry, so every field cell carries
           the entry's own colour rail. This is what keeps the table from reading as
           a provider mapping grid: /outlook-calendar already owns a three-column
           "Request field / Sent to Outlook / Read back in" table, and without the
           rail these two would be the same device on sibling pages. #1a73e8 is used
           here as a rail, never as text, per the colour note above. */
        .es-invite-table tbody th {
            position: relative;
            padding-inline-start: 1.15rem;
            font-size: 0.9rem;
            font-weight: 700;
            color: #202124;
            white-space: nowrap;
        }
        .dark .es-invite-table tbody th { color: #e8eaed; }
        .es-invite-table tbody th::before {
            content: "";
            position: absolute;
            inset-inline-start: 0;
            top: 0.75rem;
            bottom: 0.7rem;
            width: 3px;
            border-radius: 2px;
            background: #1a73e8;
        }
        .es-invite-table tbody td { font-size: 0.86rem; color: #4c5054; }
        .dark .es-invite-table tbody td { color: #9aa0a6; }
        .es-invite-table tbody tr + tr th,
        .es-invite-table tbody tr + tr td { border-top: 1px solid rgba(32, 33, 36, 0.1); }
        .dark .es-invite-table tbody tr + tr th,
        .dark .es-invite-table tbody tr + tr td { border-top-color: rgba(232, 234, 237, 0.1); }
        .es-invite-yes {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #1558c0;
        }
        .dark .es-invite-yes { color: #8ab4f8; }
        .es-invite-no {
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #4c5054;
        }
        .dark .es-invite-no { color: #9aa0a6; }

        /* --- The direction card: four settings, one chosen --- */
        .es-invite-dir {
            display: flex;
            align-items: flex-start;
            gap: 0.85rem;
            padding: 0.85rem 1rem;
            border: 1px solid rgba(32, 33, 36, 0.12);
            border-radius: 0.75rem;
            background: #ffffff;
        }
        .dark .es-invite-dir { border-color: rgba(232, 234, 237, 0.12); background: rgba(232, 234, 237, 0.04); }
        .es-invite-dir-on {
            border-color: rgba(21, 88, 192, 0.55);
            box-shadow: inset 0 0 0 1px rgba(21, 88, 192, 0.35);
        }
        .dark .es-invite-dir-on {
            border-color: rgba(138, 180, 248, 0.55);
            box-shadow: inset 0 0 0 1px rgba(138, 180, 248, 0.3);
        }
        .es-invite-pip {
            flex: none;
            margin-top: 0.2rem;
            width: 0.9rem;
            height: 0.9rem;
            border-radius: 9999px;
            border: 2px solid rgba(32, 33, 36, 0.3);
        }
        .dark .es-invite-pip { border-color: rgba(232, 234, 237, 0.32); }
        .es-invite-pip-on { border-color: #1558c0; background: #1558c0; box-shadow: inset 0 0 0 2.5px #ffffff; }
        .dark .es-invite-pip-on { border-color: #8ab4f8; background: #8ab4f8; box-shadow: inset 0 0 0 2.5px #101215; }

        /* --- The channel: Google's push notification arriving --- */
        .es-invite-ping {
            position: relative;
            display: inline-flex;
            width: 0.6rem;
            height: 0.6rem;
            border-radius: 9999px;
            background: #1558c0;
        }
        .dark .es-invite-ping { background: #8ab4f8; }
        .es-invite-ping::after {
            content: "";
            position: absolute;
            inset: -0.35rem;
            border-radius: 9999px;
            border: 1px solid rgba(21, 88, 192, 0.6);
            animation: es-invite-ping 2.6s cubic-bezier(0.22, 1, 0.36, 1) infinite;
        }
        .dark .es-invite-ping::after { border-color: rgba(138, 180, 248, 0.6); }
        @keyframes es-invite-ping {
            0% { transform: scale(0.6); opacity: 0.9; }
            70% { transform: scale(1.6); opacity: 0; }
            100% { transform: scale(1.6); opacity: 0; }
        }

        /* --- Chips --- */
        .es-invite-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            border: 1px solid rgba(32, 33, 36, 0.16);
            background: rgba(255, 255, 255, 0.7);
            color: #4c5054;
            font-size: 0.76rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .dark .es-invite-chip {
            border-color: rgba(232, 234, 237, 0.16);
            background: rgba(232, 234, 237, 0.05);
            color: #a8b0b8;
        }

        /* --- Links and buttons. Inside a fixed-dark band, links use
               .es-invite-lit instead, which is bright in both modes. --- */
        .es-invite-link { color: #1558c0; }
        .es-invite-link:hover { color: #202124; }
        .dark .es-invite-link { color: #8ab4f8; }
        .dark .es-invite-link:hover { color: #e8eaed; }

        .es-invite-btn {
            background-color: #0b57d0;
            box-shadow: 0 18px 36px -14px rgba(11, 87, 208, 0.55);
        }
        .es-invite-btn:hover { background-color: #08409b; box-shadow: 0 22px 44px -14px rgba(11, 87, 208, 0.65); }
        .dark .es-invite-btn { background-color: #8ab4f8; color: #101215; }
        .dark .es-invite-btn:hover { background-color: #a8c7fa; }

        /* Dot-nav tooltip. Its own class because dark:bg-[#191b1f] is not in the
           built CSS, which would leave white-on-white in dark mode. */
        .es-invite-tip {
            border: 1px solid rgba(32, 33, 36, 0.14);
            background: #ffffff;
            color: #202124;
        }
        .dark .es-invite-tip {
            border-color: rgba(232, 234, 237, 0.14);
            background: #191b1f;
            color: #e8eaed;
        }

        /* --- Hover treatment for FAQ rows and related cards --- */
        .es-invite-hover:hover { border-color: rgba(21, 88, 192, 0.45); }
        .dark .es-invite-hover:hover { border-color: rgba(138, 180, 248, 0.45); }
        .es-invite-hover:hover .es-invite-hover-title,
        .es-invite-hover:hover .es-invite-hover-arrow { color: #1558c0; }
        .dark .es-invite-hover:hover .es-invite-hover-title,
        .dark .es-invite-hover:hover .es-invite-hover-arrow { color: #8ab4f8; }

        /* --- Shared-system recolours (brand blue by default) --- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(21, 88, 192, 0.12), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(138, 180, 248, 0.12), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(21, 88, 192, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(138, 180, 248, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #1558c0; }
        .dark .es-dot.is-active .es-dot-pip { background: #8ab4f8; }

        /* --- Focus rings. No border-radius here: setting it changes the
               element's own shape on focus. Outlines already follow it. --- */
        #es-invite-page a:focus-visible,
        #es-invite-page summary:focus-visible,
        #es-invite-page button:focus-visible {
            outline: 2px solid #1558c0;
            outline-offset: 3px;
        }
        .dark #es-invite-page a:focus-visible,
        .dark #es-invite-page summary:focus-visible,
        .dark #es-invite-page button:focus-visible {
            outline-color: #8ab4f8;
        }
        .es-invite-band a:focus-visible,
        .es-invite-band summary:focus-visible,
        .es-invite-band button:focus-visible {
            outline-color: #8ab4f8 !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-invite-ping::after { animation: none !important; }
        }
    </style>

    @php
        // The invitation, field by field. Each row is [key, value, where it comes from].
        $noteRows = [
            ['When', 'Thu, Sep 17 &middot; 8:00 to 10:00 PM', 'start time + duration'],
            ['Where', 'The Blue Note, 131 W 3rd St', 'the venue on the event'],
            ['Details', 'Doors at 7:30. Trio plus guests.', 'description, or your template'],
            ['Zone', 'America/New_York', "the schedule's timezone"],
            ['Shown', 'Public', 'private for an unlisted event'],
        ];

        // The ledger: what the Google entry carries, and whether an edit made in
        // Google comes back. Backed by GoogleCalendarService::createEvent() and
        // updateEventFromGoogle().
        $ledger = [
            ['Title', 'The event name.', true, 'Yes'],
            ['Description', 'The event description, or the calendar template you write for this schedule.', true, 'Yes'],
            ['Start', 'The start date and time, stamped in the schedule\'s timezone.', true, 'Yes'],
            ['End', 'The start plus the event\'s duration. There is no separate end field to keep in step.', true, 'Yes'],
            ['Location', 'The address of the venue on the event.', true, 'Yes'],
            ['Time zone', 'The schedule\'s timezone, so an entry does not drift for anybody reading it elsewhere.', false, 'Set here'],
            ['Visibility', 'Public, or private when the event is unlisted.', false, 'Stays as set here'],
        ];

        $directions = [
            ['to', 'To Google Calendar', 'Events you publish here are written to the calendar you picked. Nothing in that calendar touches your schedule.', false],
            ['from', 'From Google Calendar', 'Entries in that calendar become events on your schedule. Useful when the calendar is already where you book.', false],
            ['both', 'Both directions', 'The one most people pick. Save it in either place and the other one catches up.', true],
            ['off', 'No sync', 'Where a newly connected schedule sits until you choose one of the other three. Connected, but nothing moves.', false],
        ];

        $faqs = [
            [
                'q' => 'Is two-way sync available on the free plan?',
                'a' => 'Yes. Google Calendar sync is available on all plans, including the free plan. You can push events to Google, pull events from Google, or sync both ways at no cost.',
            ],
            [
                'q' => 'How quickly do changes sync?',
                'a' => 'Outbound, the entry is written when you save the event. Inbound, Google sends a push notification to Event Schedule and the change is read within seconds. An incremental sweep every fifteen minutes is the backstop, so a notification that never arrives costs you a quarter of an hour rather than the change itself.',
            ],
            [
                'q' => 'Can I sync multiple schedules to different Google Calendars?',
                'a' => 'Yes. Each schedule picks its own Google Calendar and its own direction, from the one Google account you connected. That lets you keep a band schedule, a venue diary and your own week in separate calendars.',
            ],
            [
                'q' => 'What happens to a recurring event?',
                'a' => 'A recurring event is written to Google Calendar as a single entry at the series start, not as a Google repeat rule. If you want every date of a run to land on a calendar, the schedule\'s iCal feed is the better road: it publishes one entry per date for the next ninety days, and a subscribed calendar re-reads it on its own.',
            ],
            [
                'q' => 'If I delete an entry in Google Calendar, what happens here?',
                'a' => 'Whatever you told it to do. Each schedule has a policy for events deleted in the connected calendar: keep the event here, mark it cancelled so guests stop seeing it, or delete it here as well. It defaults to keeping the event, because that is the choice you can undo.',
            ],
            [
                'q' => 'Does Google Calendar sync work with selfhosted Event Schedule?',
                'a' => 'Yes. A selfhosted install uses its own Google OAuth credentials: create a client in the Google Cloud Console, add your callback URL as an authorized redirect URI, and set the client id, secret and redirect in your environment file. Push notifications also need your install to be reachable over HTTPS. Full setup instructions are in the selfhosted documentation.',
            ],
        ];

        $dotSections = [
            ['top', 'The invitation'],
            ['ledger', 'What it carries'],
            ['reply', 'Saying yes'],
            ['direction', 'Which way'],
            ['moved', 'When it moves'],
            ['list', 'The distribution list'],
            ['guests', 'Your audience'],
            ['rest', 'Everything else'],
            ['faq', 'Questions'],
            ['claim', 'Send the first one'],
        ];
    @endphp

    <div id="es-invite-page" class="es-invite-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the invitation itself                               -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(21, 88, 192, 0.22), rgba(21, 88, 192, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(138, 180, 248, 0.16), rgba(138, 180, 248, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        <span class="es-invite-muted text-sm font-medium tracking-wide">Google Calendar &middot; free on every plan</span>
                    </div>

                    <h1 class="es-balance es-invite-ink mb-8 text-4xl font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">Save the event once.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">The <span class="es-invite-accent">invitation</span> writes itself.</span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-invite-muted mb-10 max-w-xl text-lg sm:text-xl">
                        Pick a calendar and a direction once, and every event you publish keeps an entry in Google Calendar, and keeps it right. Move it in Google and the change comes back. Type an address there and it becomes a venue here.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="{{ app_url('/sign_up') }}" class="es-invite-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Get started free
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                        <a href="#ledger" class="glass group inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            See exactly what crosses
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-y-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                    </div>
                </div>

                <!-- The invitation. A fixed physical object: identical in both modes. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-invite-note">
                        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                            <span class="es-invite-note-tag">Google Calendar</span>
                            <span class="es-invite-note-chip">Written on save</span>
                        </div>

                        {{-- Deliberately a <p>, not a heading: this is mock content and it has no
                             business in the document outline between the h1 and the section h2s. --}}
                        <p class="es-invite-note-ink text-xl font-black tracking-tight">Thursday Night Jazz</p>
                        <span class="es-invite-note-src">the event name</span>
                        <p class="es-invite-note-mono mb-5 mt-2.5">Calendar: Gigs</p>

                        <dl class="es-invite-note-rule space-y-3.5 pt-5">
                            @foreach ($noteRows as [$nKey, $nVal, $nSrc])
                                <div class="es-invite-note-row">
                                    <dt class="es-invite-note-key">{{ $nKey }}</dt>
                                    <dd class="es-invite-note-val">
                                        {!! $nVal !!}
                                        <span class="es-invite-note-src">{{ $nSrc }}</span>
                                    </dd>
                                </div>
                            @endforeach
                        </dl>

                        <p class="es-invite-note-rule es-invite-note-muted mt-5 pt-4 text-xs">
                            One entry per event. The blue notes name where each line came from: you filled those in once, over here. Edit it in either place and the other side catches up.
                        </p>
                    </div>
                </div>
            </div>

            <div class="es-fade-up es-d-4 mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach (['Two-way', 'Push notifications', 'Pick the calendar', 'Per schedule', 'Venue from an address', 'Delete policy', 'Selfhost ready', 'Free plan'] as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-invite-chip">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The ledger: the same object, in rows                      -->
    <!-- ============================================================ -->
    <section id="ledger" class="es-invite-rule-y scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-invite-stamp mb-6" data-reveal aria-hidden="true"><span>01</span></div>
                <p class="es-invite-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The fields</p>
                <h2 class="es-balance es-invite-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    What the invitation <span class="es-invite-accent">carries.</span>
                </h2>
                <p class="es-invite-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Seven fields cross, and five of them cross back. This is the whole list, so there is nothing to discover later.
                </p>
            </div>

            <div class="es-invite-card p-4 sm:p-7" data-reveal="panel">
                <div class="overflow-x-auto">
                    <table class="es-invite-table text-sm">
                        <caption class="sr-only">Each field on a Google Calendar entry, what Event Schedule writes into it, and whether an edit made in Google Calendar comes back</caption>
                        <thead>
                            <tr>
                                <th scope="col">On the entry</th>
                                <th scope="col">Written from</th>
                                <th scope="col">Comes back</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ledger as [$lField, $lFrom, $lBack, $lLabel])
                                <tr>
                                    <th scope="row">{{ $lField }}</th>
                                    <td>{{ $lFrom }}</td>
                                    <td>
                                        @if ($lBack)
                                            <span class="es-invite-yes">
                                                <svg aria-hidden="true" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                                {{ $lLabel }}
                                            </span>
                                        @else
                                            <span class="es-invite-no">{{ $lLabel }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="es-invite-muted mt-4 px-3 text-xs">
                    An address that arrives in the Location field is matched against the venues you already have, and becomes a new venue only when none of them fit.
                </p>
            </div>

            <p class="es-invite-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                Guest lists and attendees are not on the list, in either direction. Who is coming lives in Event Schedule, on registrations and tickets, and is never written into somebody's calendar entry.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Saying yes: the OAuth consent (fixed-dark band)           -->
    <!-- ============================================================ -->
    <section id="reply" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-invite-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-invite-stamp mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                    <p class="es-invite-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The reply</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        You say yes to Google <span class="es-invite-lit">once.</span>
                    </h2>
                    <p class="mt-5 text-lg es-invite-band-muted" data-reveal style="--reveal-delay: 0.15s;">
                        Connecting is Google's own consent screen, in your account settings. No password of yours is stored here, and the connection is yours to end.
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    <div class="es-invite-card p-6" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-bold es-invite-band-ink">What Google is asked for</h3>
                            <span class="es-invite-plan">Free</span>
                        </div>
                        <p class="text-sm es-invite-band-muted">Permission to read your calendars and to manage calendar events, plus your name and email so the connection has an owner. That is the whole request, and Google shows it to you before you agree.</p>
                    </div>
                    <div class="es-invite-card p-6" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-bold es-invite-band-ink">It stays connected</h3>
                            <span class="es-invite-plan">Free</span>
                        </div>
                        <p class="text-sm es-invite-band-muted">The connection is offline, so the access token is refreshed in the background before it lapses. You are not asked to sign in again every few hours, and sync does not quietly stop while you sleep.</p>
                    </div>
                    <div class="es-invite-card p-6" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-bold es-invite-band-ink">One account, every schedule</h3>
                            <span class="es-invite-plan">Free</span>
                        </div>
                        <p class="text-sm es-invite-band-muted">Connect the Google account once. After that each schedule chooses its own calendar and its own direction, and disconnecting is one link in the same settings page.</p>
                    </div>
                </div>

                <p class="mt-10 text-center es-invite-band-muted" data-reveal>
                    Running your own install? Point it at your own Google client and the same flow is yours.
                    <a href="{{ route('marketing.docs.selfhost.google_calendar') }}" class="es-invite-lit inline-flex items-center gap-1 font-semibold transition-all hover:gap-2">
                        Selfhost setup
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Which way the invitations travel                          -->
    <!-- ============================================================ -->
    <section id="direction" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-start gap-12 lg:grid-cols-2">
                <div>
                    <div class="es-invite-stamp mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                    <p class="es-invite-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The direction</p>
                    <h2 class="es-balance es-invite-ink mb-5 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                        Set per schedule, <span class="es-invite-accent">not per account.</span>
                    </h2>
                    <p class="es-invite-muted mb-6 text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        A band schedule and a venue diary want different things from the same Google account. So the direction, and the calendar it points at, both live on the schedule.
                    </p>
                    <ul class="es-invite-muted space-y-3" data-reveal-group="70">
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-invite-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>The calendar list is read from your Google account, so you pick a real calendar by name rather than pasting an id.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-invite-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Leave it unset and the schedule uses your primary calendar, which is what most people want on day one.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-invite-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Change your mind and there is a re-send button on the same tab that writes every published event out again.</span>
                        </li>
                    </ul>
                </div>

                <div class="es-invite-card p-6 sm:p-7" data-reveal="panel">
                    <p class="es-invite-tag mb-4">Sync direction</p>
                    <div class="space-y-3">
                        @foreach ($directions as [$dKey, $dName, $dBody, $dOn])
                            <div class="es-invite-dir @if ($dOn) es-invite-dir-on @endif">
                                <span class="es-invite-pip @if ($dOn) es-invite-pip-on @endif" aria-hidden="true"></span>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="es-invite-ink text-sm font-bold">{{ $dName }}</span>
                                        @if ($dOn)<span class="es-invite-plan">Chosen</span>@endif
                                    </div>
                                    <p class="es-invite-muted mt-1 text-sm">{{ $dBody }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <p class="es-invite-muted mt-5 text-xs">
                        Four settings, one radio group, saved with the rest of the schedule. A new schedule starts on no sync, so nothing leaves until you say which way. Turning inbound sync on is what asks Google to start notifying us.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. When somebody moves it in Google                          -->
    <!-- ============================================================ -->
    <section id="moved" class="es-invite-shade es-invite-rule-y scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-invite-stamp mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                <p class="es-invite-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The channel</p>
                <h2 class="es-balance es-invite-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Somebody drags it to Friday. <span class="es-invite-accent">Now what?</span>
                </h2>
                <p class="es-invite-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Google does not wait to be asked. When inbound sync is on, it holds a notification channel for that calendar and calls us the moment something changes.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-3" data-reveal-group="100">
                <div class="es-invite-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <span class="es-invite-ping" aria-hidden="true"></span>
                        <h3 class="es-invite-ink text-lg font-bold">The notification</h3>
                        <span class="es-invite-plan">Free</span>
                    </div>
                    <p class="es-invite-muted text-sm">Google posts to Event Schedule when the calendar changes, and only the changes since last time are read. Seconds, not a poll on the hour.</p>
                </div>
                <div class="es-invite-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-invite-ink text-lg font-bold">The backstop</h3>
                        <span class="es-invite-plan">Free</span>
                    </div>
                    <p class="es-invite-muted text-sm">A channel can lapse and a single notification can go missing, so an incremental sweep runs every fifteen minutes as well, and the channel is renewed daily.</p>
                </div>
                <div class="es-invite-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-invite-ink text-lg font-bold">If it is deleted there</h3>
                        <span class="es-invite-plan">Free</span>
                    </div>
                    <p class="es-invite-muted text-sm">Your call, per schedule: keep the event here, mark it cancelled so guests stop seeing it, or delete it here too. It defaults to keeping it, which is the choice you can undo.</p>
                </div>
            </div>

            <div class="es-invite-card mx-auto mt-8 max-w-3xl p-6" data-reveal="panel">
                <p class="es-invite-tag mb-3">Being straight about two things</p>
                <p class="es-invite-muted text-sm">
                    A recurring event is written out as a single entry at the series start, not as a Google repeat rule, so a whole run does not appear date by date in Google. And an inbound entry arrives as a plain event, without your recurrence or your
                    <a href="{{ marketing_url('/features/ticketing') }}" class="es-invite-link font-semibold hover:underline">ticket types</a>.
                    If what you want is every date of a run on somebody's calendar, that is the
                    <a href="{{ marketing_url('/features/calendar-sync') }}" class="es-invite-link font-semibold hover:underline">iCal feed</a>, not this.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. The distribution list (fixed-dark band)                   -->
    <!-- ============================================================ -->
    <section id="list" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-invite-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-invite-stamp mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                    <p class="es-invite-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The distribution list</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        The same entry, on <span class="es-invite-lit">everybody's</span> calendar.
                    </h2>
                    <p class="mt-5 text-lg es-invite-band-muted" data-reveal style="--reveal-delay: 0.15s;">
                        This one is Google only, and it costs nothing. Anybody who follows your schedule, and any team member on it, can point it at a Google Calendar of their own, so the entry is written there as well as wherever the schedule itself syncs.
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-3" data-reveal-group="90">
                    @foreach ([['Ada', 'Team member', 'ada@ &middot; Work'], ['Marco', 'Follower', 'marco@ &middot; Personal'], ['Priya', 'Follower', 'priya@ &middot; Primary']] as [$mName, $mRoleLabel, $mCal])
                        <div data-reveal>
                            <div class="es-invite-note-mini">
                                <div class="es-invite-note-mini-bar" aria-hidden="true"></div>
                                <p class="es-invite-note-ink text-sm font-bold">Thursday Night Jazz</p>
                                <p class="es-invite-note-muted mt-0.5 text-xs">Thu, Sep 17 &middot; 8:00 PM</p>
                                <div class="es-invite-note-rule mt-3 pt-3">
                                    <p class="es-invite-note-src">{{ $mName }} &middot; {{ $mRoleLabel }}</p>
                                    <p class="es-invite-note-mono">{!! $mCal !!}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8 grid gap-6 md:grid-cols-2" data-reveal-group="110">
                    <div class="es-invite-card p-7" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-bold es-invite-band-ink">Each person picks their own</h3>
                            <span class="es-invite-plan">Free</span>
                        </div>
                        <p class="text-sm es-invite-band-muted">They connect their own Google account and choose which of their calendars receives your schedule, from their own following list. Nobody sees anybody else's calendar, and turning it back off takes the entries that were written off their calendar again.</p>
                    </div>
                    <div class="es-invite-card p-7" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-bold es-invite-band-ink">It works even when the schedule does not sync</h3>
                            <span class="es-invite-plan">Free</span>
                        </div>
                        <p class="text-sm es-invite-band-muted">These copies are written on each person's own account. If the schedule itself is set to no sync, the people who asked for the entry still get it, and unpublishing the event removes it from their calendars as well.</p>
                    </div>
                </div>

                <p class="mt-10 text-center es-invite-band-muted" data-reveal>
                    Following is free and open to anybody with an account; adding team members beyond yourself is an Enterprise feature, capped at five. Outlook and CalDAV connect at the schedule level only, so this part is a Google thing.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Your audience gets an invitation too                      -->
    <!-- ============================================================ -->
    <section id="guests" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-invite-stamp mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                <p class="es-invite-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Your audience</p>
                <h2 class="es-balance es-invite-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    They can take one <span class="es-invite-accent">too.</span>
                </h2>
            </div>

            <div class="grid gap-6 md:grid-cols-2" data-reveal-group="110">
                <div class="es-invite-card flex flex-col p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-invite-ink text-xl font-bold">Add to Google Calendar</h3>
                        <span class="es-invite-plan">Free</span>
                    </div>
                    <p class="es-invite-muted mb-5">Public event pages carry an Add to Calendar menu, except where the page is already leading with a Register or Buy tickets button. On a ticketed page it sits with the venue details instead. The Google item opens a new Google Calendar entry with the title, the date, the address and the details already filled in.</p>
                    <div class="mb-5 flex flex-wrap gap-2" aria-hidden="true">
                        <span class="es-invite-chip">Google Calendar</span>
                        <span class="es-invite-chip">Apple Calendar</span>
                        <span class="es-invite-chip">Outlook</span>
                    </div>
                    <p class="es-invite-muted mt-auto text-sm">On a recurring event the link is built for the date they are looking at, so a Thursday in October is a Thursday in October and not the first night of the run.</p>
                </div>

                <div class="es-invite-card flex flex-col p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-invite-ink text-xl font-bold">No account, and no strings</h3>
                        <span class="es-invite-plan">Free</span>
                    </div>
                    <p class="es-invite-muted mb-5">Nobody signs up to take a copy, and nothing is tracked back to them. Being straight about it: a copy is a copy. If you move the start time afterwards, their entry does not hear about it.</p>
                    <p class="es-invite-muted mb-5 text-sm">Anybody who wants to stay current wants the schedule's iCal feed instead, which their calendar re-reads on its own.</p>
                    <a href="{{ marketing_url('/features/calendar-sync') }}" class="es-invite-link mt-auto inline-flex items-center gap-1 font-semibold transition-all hover:gap-2">
                        How the feed works
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Everything else: bento                                    -->
    <!-- ============================================================ -->
    <section id="rest" class="es-invite-rule-t scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-invite-stamp mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                <p class="es-invite-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Everything else</p>
                <h2 class="es-balance es-invite-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    The small print, in full.
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">
                <!-- 1 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-invite-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-invite-ink text-xl font-bold">Write the entry the way you read it</h3>
                                <span class="es-invite-plan">Free</span>
                            </div>
                            <p class="es-invite-muted mb-4">By default the Google entry carries the event's own description. Give the schedule a calendar template instead and every entry is written to that shape, from the event's real values.</p>
                            <p class="es-invite-muted font-mono text-sm">{event_name} &middot; {time} to {end_time} &middot; {venue}, {city} &middot; {price} &middot; {url}</p>
                            <p class="es-invite-muted mt-3 text-sm">A line whose values are all empty is dropped rather than left as stray punctuation, so a gig with no venue does not arrive with a dangling separator.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-invite-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-invite-ink text-xl font-bold">Drafts stay home</h3>
                                <span class="es-invite-plan">Free</span>
                            </div>
                            <p class="es-invite-muted">Nothing leaves while an event is a draft. Publish it and the entry appears; unpublish it and the entry is taken away again.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-invite-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-invite-ink text-xl font-bold">Unlisted goes out private</h3>
                                <span class="es-invite-plan">Free</span>
                            </div>
                            <p class="es-invite-muted">An unlisted event is written to Google marked private, so it is not offered up to anybody browsing a shared calendar.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-invite-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-invite-ink text-xl font-bold">Put them all on the right calendar</h3>
                                <span class="es-invite-plan">Free</span>
                            </div>
                            <p class="es-invite-muted mb-4">The schedule owner has a re-send button that walks every published event and puts it on the calendar the schedule points at now. It is the fix for a calendar you switched, or a batch you wrote before you connected.</p>
                            <p class="es-invite-muted text-sm">Events already sitting on that calendar are left alone and the rest are moved across, so pressing it twice costs nothing. It only pushes, never imports, and it needs sync to Google switched on first. An old copy it can no longer reach stays where it is, and it says so before it runs.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-invite-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-invite-ink text-xl font-bold">Bookings are protected</h3>
                                <span class="es-invite-plan">Pro</span>
                            </div>
                            <p class="es-invite-muted">An appointment somebody booked with you is owned here, not by the calendar, so an inbound edit never rewrites the guest's own notes or drags the booking back to its old time.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-invite-card relative flex h-full flex-col overflow-hidden p-7">
                        {{-- flex-1 flex-col so the mt-auto on the link below actually has a
                             flex container to push against. --}}
                        <div class="relative z-10 flex flex-1 flex-col">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-invite-ink text-xl font-bold">Selfhosted, on your own client</h3>
                                <span class="es-invite-plan">Free</span>
                            </div>
                            <p class="es-invite-muted mb-4">Create an OAuth client in the Google Cloud Console, add your own callback as an authorized redirect URI, and put the client id, secret and redirect in your environment file. Nothing is proxied through us. Push notifications need your install reachable over HTTPS; without that, the fifteen-minute sweep still does the job.</p>
                            <a href="{{ route('marketing.docs.selfhost.google_calendar') }}" class="es-invite-link mt-auto inline-flex items-center gap-1 font-semibold transition-all hover:gap-2">
                                Read the selfhost guide
                                <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </a>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('marketing.partials.pricing-nudge')

    <!-- ============================================================ -->
    <!-- 9. Related features                                          -->
    <!-- ============================================================ -->
    <section class="es-invite-rule-t py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-invite-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Calendar Sync" description="The same two-way sync for Outlook, Microsoft 365 and any CalDAV server" :url="marketing_url('/features/calendar-sync')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="A day-of-week pattern with exceptions, so a weekly night is one event" :url="marketing_url('/features/recurring-events')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Embed Calendar" description="Put the same schedule on the website you already have" :url="marketing_url('/features/embed-calendar')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="All Integrations" description="Every tool Event Schedule connects to, in one list" :url="marketing_url('/features/integrations')" icon-color="blue">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-invite-link inline-flex items-center font-medium hover:underline">
                    See all features
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. Popular with                                             -->
    <!-- ============================================================ -->
    <section class="es-invite-rule-t py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-invite-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Popular with</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3" data-reveal-group="70">
                @foreach ([['/for-musicians', 'Musicians', 'Gigs in the calendar you check on the way to the gig.'], ['/for-venues', 'Venues', 'A room diary that agrees with the public listings.'], ['/for-community-centers', 'Community Centers', 'Bookings taken in the calendar the office already lives in.']] as [$relHref, $relName, $relBlurb])
                    <a href="{{ marketing_url($relHref) }}" class="es-invite-hover es-invite-card group flex flex-col p-6 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-invite-hover-title es-invite-ink mb-2 font-bold transition-colors">For {{ $relName }}</span>
                        <span class="es-invite-muted mb-4 text-sm">{{ $relBlurb }}</span>
                        <span class="es-invite-hover-arrow es-invite-muted mt-auto inline-flex items-center gap-1 text-xs font-semibold uppercase tracking-[0.14em] transition-colors">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
            <div class="mt-8 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-invite-link inline-flex items-center font-medium hover:underline">
                    See all use cases
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 11. FAQ                                                      -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="es-invite-rule-t scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="es-invite-stamp mb-6" data-reveal aria-hidden="true"><span>08</span></div>
                <h2 class="es-balance es-invite-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-invite-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    Common questions about Google Calendar sync.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-invite-hover es-invite-card group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-invite-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-invite-accent flex-none font-mono text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-invite-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-invite-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-invite-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 12. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-invite-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    {{-- The last invitation on the page is the one that has not been written yet. --}}
                    <div class="es-invite-blank-wrap mx-auto mb-10" data-reveal>
                        <div class="es-invite-note-mini es-invite-note-blank text-start">
                            <div class="es-invite-note-mini-bar" aria-hidden="true"></div>
                            <p class="es-invite-note-tag">Google Calendar</p>
                            <p class="es-invite-note-ink mt-1 text-sm font-bold">Your first event</p>
                            <div class="es-invite-note-rule mt-3 space-y-2 pt-3" aria-hidden="true">
                                <span class="es-invite-blank-line"></span>
                                <span class="es-invite-blank-line es-invite-blank-short"></span>
                            </div>
                            <p class="es-invite-note-muted mt-3 text-xs">Written the moment you save it.</p>
                        </div>
                    </div>

                    <p class="es-invite-tag mb-4">Free on every plan</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Connect Google. <span class="es-invite-lit">Send the first one.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg es-invite-band-muted">
                        Pick a name, connect the calendar you already keep open, and the next event you save is on it.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-400 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm es-invite-band-muted sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-invite-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Get started free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="mt-6 text-sm es-invite-band-muted">No credit card required</p>
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
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3 es-invite-tip">{{ $sectionLabel }}</span>
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
