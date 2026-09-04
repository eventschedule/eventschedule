<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Live Q&A Sessions | Hosting Software</x-slot>
    <x-slot name="description">Free live Q&A scheduling software with registration, ticketing, and email notifications. Works with Zoom, YouTube Live, and any platform. Zero platform fees.</x-slot>
    <x-slot name="breadcrumbTitle">For Live Q&A Sessions</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Live Q&A Sessions",
        "description": "Free live Q&A scheduling software with registration, ticketing, and email notifications. Works with Zoom, YouTube Live, and any platform. Zero platform fees.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Q&A Session Hosts"
        }
    }
    </script>
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Live Q&A Sessions",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Live Q&A Session Scheduling Software",
        "operatingSystem": "Web",
        "description": "Schedule live Q&A sessions, AMAs and office hours with free registration and a capacity limit per date, an agenda your audience can read, polls your audience can add options to, and one join link for whatever platform you host on.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Free forever"
        },
        "featureList": [
            "Free registration with a capacity limit counted per session date",
            "Confirmation email carrying your own registration notes",
            "Agenda segments with their own start and end times",
            "Polls on a session, with options your audience can suggest (Pro plan)",
            "Comments on a session or on a single agenda segment, held for approval",
            "One join link for Zoom, Google Meet, Microsoft Teams or YouTube Live",
            "Recurring office hours with date exceptions and an end",
            "Zero platform fees on ticket sales through your own Stripe account",
            "Two-way Google, Outlook and CalDAV calendar sync",
            "Embeddable calendar for the website you already have",
            "Newsletters you write and send yourself",
            "Open source, with a selfhosted option"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "live Q&A platform, Q&A session scheduling, interactive Q&A events, paid Q&A sessions, office hours scheduling, AMA scheduling",
        "screenshot": "{{ asset('images/social/for-live-qa-sessions.jpg') }}",
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
        "name": "How to host a live Q&A session with Event Schedule",
        "description": "Three steps to schedule a live Q&A session, open registration, and collect what your audience wants to ask.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Set the hour",
                "text": "Create the session with its date and time, mark it online and paste your join link, and add agenda segments if the hour has a shape."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Open registration",
                "text": "Switch the session to Registration, set how many places there are, and write the note that goes out with every confirmation email."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Collect the questions",
                "text": "Add a poll your audience can suggest options for, or let them comment on the session page, then host the hour wherever you already host it."
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
           For-live-qa-sessions "The Conversation" styles.

           THE CONCEPT IS A TURN, NOT A BUBBLE. A live Q&A is an hour of
           alternating turns: somebody asks, somebody answers. So the
           page is built as a transcript. Every section is one turn -
           a hanging monospace Q label, the audience's question as the
           heading, and the answer underneath naming the actual setting
           that answers it. A thread rule runs down the label column so
           the turns read as one continuous conversation.

           THE OBJECT IS A PRINTED RUN SHEET (.es-conv-sheet): the
           agenda of one session, timecodes in the left margin. Event
           parts really do carry a name, a start time and an end time,
           so the sheet is the product's own data structure on paper.
           It is the same physical sheet in both colour modes and
           therefore carries NO dark: utilities and no shared classes -
           verify with --bands=.es-conv-sheet, expect 0 diffs.

           THE MOTIF IS A TURN-TAKING WAVEFORM (.es-conv-wave): bars
           above the line are the audience, bars below are the host,
           alternating. Abstract strokes, never an illustration.

           COLOUR: the page's existing warm hue family, pushed off gold
           and onto burnt orange so it does not read as one more amber
           page. Measured on this page's own grounds:
             #b03a06 on #faf7f4 = 5.70   accent, light
             #fdba74 on #12100e = 11.26  accent, dark
             #4f4a45 on #faf7f4 = 8.21   muted, light  (NEVER gray-500)
             #a49b93 on #12100e = 6.95   muted, dark
             #ffffff on #b03a06 = 6.08   button ink
             #5a534c on #fffdfa = 7.45   muted, on the paper sheet
           ============================================================== */

        /* --- Ground and ink --- */
        .es-conv-page { background-color: #faf7f4; color: #171412; }
        .dark .es-conv-page { background-color: #12100e; color: #f1ece7; }
        .es-conv-ink { color: #171412; }
        .dark .es-conv-ink { color: #f1ece7; }
        .es-conv-muted { color: #4f4a45; }
        .dark .es-conv-muted { color: #a49b93; }
        .es-conv-accent { color: #b03a06; }
        .dark .es-conv-accent { color: #fdba74; }
        /* Always-lit accent, for the bands that stay dark in both modes. */
        .es-conv-lit { color: #fdba74; }
        .es-conv-alt { background-color: #f4efe9; }
        .dark .es-conv-alt { background-color: #171310; }
        .es-conv-hr { border-color: rgba(23, 20, 18, 0.1); }
        .dark .es-conv-hr { border-color: rgba(241, 236, 231, 0.1); }

        /* --- The turn: hanging speaker label plus a thread rule --- */
        .es-conv-turn { display: grid; grid-template-columns: 1fr; gap: 1rem; }
        @media (min-width: 640px) {
            .es-conv-turn { grid-template-columns: 4.5rem 1fr; gap: 1.75rem; }
        }
        .es-conv-rail { display: flex; flex-direction: row; align-items: center; gap: 0.6rem; }
        @media (min-width: 640px) {
            .es-conv-rail { flex-direction: column; align-items: stretch; align-self: stretch; gap: 0.6rem; }
        }
        .es-conv-label {
            display: inline-flex;
            align-items: baseline;
            gap: 0.25rem;
            flex: none;
            align-self: flex-start;
            padding: 0.28rem 0.5rem 0.32rem;
            border: 1px solid rgba(23, 20, 18, 0.18);
            border-radius: 0.35rem;
            background-color: #ffffff;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.9rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            font-variant-numeric: tabular-nums;
            color: #171412;
        }
        .dark .es-conv-label { border-color: rgba(241, 236, 231, 0.2); background-color: rgba(241, 236, 231, 0.05); color: #f1ece7; }
        .es-conv-label-q { border-color: rgba(176, 58, 6, 0.42); color: #b03a06; }
        .dark .es-conv-label-q { border-color: rgba(253, 186, 116, 0.42); color: #fdba74; }
        .es-conv-label-n { font-size: 0.7rem; font-weight: 700; opacity: 0.72; }
        .es-conv-thread { display: none; }
        @media (min-width: 640px) {
            .es-conv-thread {
                display: block;
                width: 1px;
                flex: 1 1 auto;
                min-height: 2.5rem;
                margin-left: 1.05rem;
                background-image: linear-gradient(to bottom, rgba(176, 58, 6, 0.42), rgba(176, 58, 6, 0.06));
            }
            .dark .es-conv-thread { background-image: linear-gradient(to bottom, rgba(253, 186, 116, 0.42), rgba(253, 186, 116, 0.06)); }
        }
        .es-conv-band .es-conv-thread { background-image: linear-gradient(to bottom, rgba(253, 186, 116, 0.45), rgba(253, 186, 116, 0.06)); }
        /* A band stays the same dark room in both colour modes, so the speaker
           labels inside it must be pinned too or they flip with .dark. */
        .es-conv-band .es-conv-label { border-color: rgba(241, 236, 231, 0.2); background-color: rgba(241, 236, 231, 0.05); color: #f1ece7; }
        .es-conv-band .es-conv-label-q { border-color: rgba(253, 186, 116, 0.42); color: #fdba74; }

        /* --- Eyebrow, chips, plan pills --- */
        .es-conv-tag {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            color: #4f4a45;
        }
        .dark .es-conv-tag { color: #a49b93; }
        .es-conv-band .es-conv-tag { color: #fdba74; }
        .es-conv-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            border: 1px solid rgba(23, 20, 18, 0.16);
            background-color: rgba(255, 255, 255, 0.75);
            color: #4f4a45;
            font-size: 0.76rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .dark .es-conv-chip { border-color: rgba(241, 236, 231, 0.16); background-color: rgba(241, 236, 231, 0.05); color: #b6ada4; }
        .es-conv-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border: 1px solid rgba(176, 58, 6, 0.42);
            border-radius: 0.25rem;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #b03a06;
        }
        .dark .es-conv-plan { border-color: rgba(253, 186, 116, 0.45); color: #fdba74; }
        .es-conv-band .es-conv-plan { border-color: rgba(253, 186, 116, 0.45); color: #fdba74; }
        .es-conv-plan-pro { border-color: rgba(23, 20, 18, 0.35); color: #171412; }
        .dark .es-conv-plan-pro { border-color: rgba(241, 236, 231, 0.38); color: #f1ece7; }
        .es-conv-band .es-conv-plan-pro { border-color: rgba(241, 236, 231, 0.38); color: #f1ece7; }

        /* --- Cards --- */
        .es-conv-card {
            border: 1px solid rgba(23, 20, 18, 0.12);
            border-radius: 1rem;
            background-color: #ffffff;
        }
        .dark .es-conv-card { border-color: rgba(241, 236, 231, 0.12); background-color: rgba(241, 236, 231, 0.04); }
        .es-conv-band .es-conv-card { border-color: rgba(241, 236, 231, 0.14); background-color: rgba(241, 236, 231, 0.05); }

        /* --- Fixed-dark band: the same room in both colour modes --- */
        .es-conv-band {
            background-color: #171310;
            background-image: radial-gradient(120% 100% at 50% 0%, #241c16 0%, #181310 55%, #0d0b09 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(241, 236, 231, 0.05);
        }
        .es-conv-band .es-conv-ink { color: #f1ece7; }
        .es-conv-band .es-conv-muted { color: #a49b93; }
        /* Body copy and fine print ON a fixed-dark band, in both modes. These are
           page-local rather than es-conv-onband utilities on purpose: an arbitrary
           Tailwind value that no built page already uses is not in the compiled
           bundle, so it silently paints nothing and the text falls back to the ink. */
        .es-conv-onband { color: #d1d5db; }
        .es-conv-ondim { color: #a49b93; }
        /* Shared classes carry their own .dark rules, so pin them inside the band. */
        .es-conv-band .grid-overlay {
            background-image:
                linear-gradient(rgba(241, 236, 231, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(241, 236, 231, 0.05) 1px, transparent 1px);
        }
        .es-conv-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-conv-band .es-claim:focus-within {
            border-color: rgba(253, 186, 116, 0.75);
            box-shadow: 0 0 0 4px rgba(253, 186, 116, 0.22);
        }

        /* --- The printed run sheet. A physical object: no dark rules,
               no shared classes, identical with .dark on and off. --- */
        .es-conv-sheet {
            position: relative;
            overflow: hidden;
            padding: 1.35rem 1.4rem 1.3rem;
            border: 1px solid rgba(23, 20, 18, 0.14);
            border-radius: 0.85rem;
            background-color: #fffdfa;
            color: #171412;
            box-shadow: 0 24px 48px -28px rgba(23, 20, 18, 0.5);
        }
        .es-conv-sheet::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background-image: repeating-linear-gradient(
                to bottom,
                transparent 0,
                transparent 1.74rem,
                rgba(23, 20, 18, 0.06) 1.74rem,
                rgba(23, 20, 18, 0.06) 1.75rem);
        }
        .es-conv-sheet::after {
            content: "";
            position: absolute;
            top: 0;
            bottom: 0;
            left: 4.55rem;
            width: 1px;
            background-color: rgba(176, 58, 6, 0.28);
        }
        .es-conv-sheet-body { position: relative; z-index: 1; }
        .es-conv-sheet-title { font-size: 1.05rem; font-weight: 800; letter-spacing: -0.01em; color: #171412; }
        .es-conv-sheet-meta {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.68rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            color: #5a534c;
        }
        .es-conv-slot { display: grid; grid-template-columns: 3.1rem 1fr; gap: 0.85rem; padding: 0.3rem 0 0.32rem; }
        .es-conv-time {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.72rem;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            color: #b03a06;
            padding-top: 0.1rem;
        }
        .es-conv-slot-name { font-size: 0.86rem; font-weight: 600; color: #171412; }
        .es-conv-slot-note { font-size: 0.72rem; color: #5a534c; }
        .es-conv-sheet-note {
            font-size: 0.7rem;
            line-height: 1.45;
            color: #5a534c;
            border-top: 1px solid rgba(23, 20, 18, 0.1);
            padding-top: 0.7rem;
            margin-top: 0.7rem;
        }
        .es-conv-sheet-stamp {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.12rem 0.45rem;
            border: 1px solid rgba(176, 58, 6, 0.4);
            border-radius: 0.25rem;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #b03a06;
        }

        /* --- Turn-taking waveform. Above the line, the audience.
               Below it, the host. Abstract strokes only. --- */
        .es-conv-wave { display: flex; flex-direction: column; gap: 0; }
        .es-conv-wave-mask {
            -webkit-mask-image: linear-gradient(to right, transparent, black 20%, black 80%, transparent);
            mask-image: linear-gradient(to right, transparent, black 20%, black 80%, transparent);
        }
        /* space-between plus flexible bars, so the turns stretch the full width of
           whatever they sit in instead of clustering in the middle. */
        .es-conv-wave-row { display: flex; align-items: flex-end; justify-content: space-between; gap: 0; height: 2.1rem; }
        .es-conv-wave-a { align-items: flex-start; }
        .es-conv-wave-rule { height: 1px; background-image: linear-gradient(to right, transparent, rgba(176, 58, 6, 0.35), transparent); }
        .dark .es-conv-wave-rule { background-image: linear-gradient(to right, transparent, rgba(253, 186, 116, 0.35), transparent); }
        .es-conv-band .es-conv-wave-rule { background-image: linear-gradient(to right, transparent, rgba(253, 186, 116, 0.35), transparent); }
        .es-conv-wave-bar {
            flex: 1 1 0;
            min-width: 2px;
            max-width: 3px;
            height: var(--wb, 40%);
            border-radius: 2px;
            background-color: rgba(176, 58, 6, 0.55);
            transform-origin: bottom;
            animation: es-conv-breathe var(--wd, 3.2s) ease-in-out infinite;
            animation-delay: var(--wdelay, 0s);
        }
        .dark .es-conv-wave-bar { background-color: rgba(253, 186, 116, 0.5); }
        .es-conv-band .es-conv-wave-bar { background-color: rgba(253, 186, 116, 0.5); }
        .es-conv-wave-a .es-conv-wave-bar {
            transform-origin: top;
            background-color: rgba(23, 20, 18, 0.35);
        }
        .dark .es-conv-wave-a .es-conv-wave-bar { background-color: rgba(241, 236, 231, 0.28); }
        .es-conv-band .es-conv-wave-a .es-conv-wave-bar { background-color: rgba(241, 236, 231, 0.28); }
        @keyframes es-conv-breathe {
            0%, 100% { transform: scaleY(0.55); opacity: 0.55; }
            50% { transform: scaleY(1); opacity: 1; }
        }

        /* --- Hand-drawn underline on the accent word --- */
        .es-conv-mark { position: relative; white-space: nowrap; }
        .es-conv-mark-line {
            position: absolute;
            left: 0;
            right: 0;
            bottom: -0.18em;
            width: 100%;
            height: 0.28em;
            overflow: visible;
            color: #b03a06;
        }
        .dark .es-conv-mark-line { color: #fdba74; }
        .es-conv-mark-line path { stroke-dasharray: 240; stroke-dashoffset: 0; }
        html.es-anim .es-conv-mark-line path { animation: es-conv-draw 1.1s cubic-bezier(0.22, 1, 0.36, 1) 0.55s backwards; }
        @keyframes es-conv-draw { from { stroke-dashoffset: 240; } to { stroke-dashoffset: 0; } }

        /* --- The poll: options, a fill bar, a suggestion row --- */
        .es-conv-poll-q { font-size: 0.95rem; font-weight: 700; }
        .es-conv-opt { padding: 0.3rem 0; }
        .es-conv-opt-head { display: flex; align-items: baseline; justify-content: space-between; gap: 0.75rem; font-size: 0.78rem; }
        .es-conv-track { height: 0.4rem; border-radius: 9999px; background-color: rgba(241, 236, 231, 0.12); overflow: hidden; margin-top: 0.28rem; }
        .es-conv-fill {
            height: 100%;
            border-radius: 9999px;
            background-color: rgba(241, 236, 231, 0.32);
            transform-origin: left;
            transition: transform 1.1s cubic-bezier(0.22, 1, 0.36, 1);
            transition-delay: var(--fd, 0.2s);
        }
        .es-conv-fill-mine { background-color: #fdba74; }
        html.es-anim [data-reveal]:not(.is-revealed) .es-conv-fill { transform: scaleX(0.015); }
        .es-conv-count {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.72rem;
            font-weight: 700;
        }
        .es-conv-suggest {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.6rem;
            margin-top: 0.7rem;
            padding: 0.45rem 0.6rem;
            border: 1px dashed rgba(253, 186, 116, 0.45);
            border-radius: 0.5rem;
            font-size: 0.74rem;
            color: #a49b93;
        }
        .es-conv-suggest-btn {
            flex: none;
            padding: 0.05rem 0.42rem;
            border: 1px solid rgba(253, 186, 116, 0.5);
            border-radius: 0.3rem;
            font-size: 0.8rem;
            font-weight: 800;
            line-height: 1.35;
            color: #fdba74;
        }
        .es-conv-pending {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.6rem;
            padding: 0.45rem 0.6rem;
            border: 1px solid rgba(253, 186, 116, 0.4);
            border-radius: 0.5rem;
            background-color: rgba(253, 186, 116, 0.09);
            font-size: 0.78rem;
        }
        .es-conv-mini {
            flex: none;
            padding: 0.08rem 0.45rem;
            border-radius: 0.3rem;
            border: 1px solid rgba(241, 236, 231, 0.24);
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #f1ece7;
        }
        .es-conv-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.15rem;
            height: 1.15rem;
            padding: 0 0.28rem;
            border-radius: 9999px;
            background-color: #fdba74;
            color: #171412;
            font-size: 0.65rem;
            font-weight: 800;
            font-variant-numeric: tabular-nums;
        }

        /* --- Links, buttons, hover states --- */
        .es-conv-link { color: #b03a06; }
        .es-conv-link:hover { color: #171412; }
        .dark .es-conv-link { color: #fdba74; }
        .dark .es-conv-link:hover { color: #f1ece7; }
        .es-conv-band .es-conv-link { color: #fdba74; }
        .es-conv-band .es-conv-link:hover { color: #f1ece7; }
        .es-conv-btn {
            background-color: #b03a06;
            box-shadow: 0 18px 36px -14px rgba(176, 58, 6, 0.5);
        }
        .es-conv-btn:hover { background-color: #8f2f05; box-shadow: 0 22px 44px -14px rgba(176, 58, 6, 0.6); }
        /* The dark-mode fill is light, so the button's own ink has to flip with it.
           Kept here rather than as a dark:text-[...] utility for the reason above. */
        .dark .es-conv-btn { background-color: #fdba74; color: #171412; }
        .dark .es-conv-btn:hover { background-color: #fecfa0; }
        .es-conv-tip { background-color: #ffffff; border-color: rgba(23, 20, 18, 0.14); color: #4f4a45; }
        .dark .es-conv-tip { background-color: #171310; border-color: rgba(241, 236, 231, 0.14); color: #b6ada4; }
        /* Stat-strip separators: horizontal when the strip stacks, vertical once it
           is a row. border-y-0 is not in the compiled bundle, so this is page-local. */
        .es-conv-statmid {
            padding: 1.5rem 1rem;
            border-top: 1px solid rgba(23, 20, 18, 0.1);
            border-bottom: 1px solid rgba(23, 20, 18, 0.1);
        }
        .dark .es-conv-statmid { border-top-color: rgba(241, 236, 231, 0.1); border-bottom-color: rgba(241, 236, 231, 0.1); }
        @media (min-width: 768px) {
            .es-conv-statmid {
                padding: 0 1rem;
                border-top: 0;
                border-bottom: 0;
                border-left: 1px solid rgba(23, 20, 18, 0.1);
                border-right: 1px solid rgba(23, 20, 18, 0.1);
            }
            .dark .es-conv-statmid { border-left-color: rgba(241, 236, 231, 0.1); border-right-color: rgba(241, 236, 231, 0.1); }
        }
        .es-conv-hover:hover { border-color: rgba(176, 58, 6, 0.45); }
        .dark .es-conv-hover:hover { border-color: rgba(253, 186, 116, 0.45); }
        .es-conv-hover:hover .es-conv-hover-title,
        .es-conv-hover:hover .es-conv-hover-arrow { color: #b03a06; }
        .dark .es-conv-hover:hover .es-conv-hover-title,
        .dark .es-conv-hover:hover .es-conv-hover-arrow { color: #fdba74; }

        /* --- Shared-system recolours (brand blue by default) --- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(176, 58, 6, 0.12), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(253, 186, 116, 0.1), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(176, 58, 6, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(253, 186, 116, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #b03a06; }
        .dark .es-dot.is-active .es-dot-pip { background: #fdba74; }

        /* --- Focus rings. No border-radius here: it would reshape the
               element itself on focus. Outlines already follow it. --- */
        #es-conv-page a:focus-visible,
        #es-conv-page summary:focus-visible,
        #es-conv-page input:focus-visible,
        #es-conv-page button:focus-visible {
            outline: 2px solid #b03a06;
            outline-offset: 3px;
        }
        .dark #es-conv-page a:focus-visible,
        .dark #es-conv-page summary:focus-visible,
        .dark #es-conv-page input:focus-visible,
        .dark #es-conv-page button:focus-visible {
            outline-color: #fdba74;
        }
        .es-conv-band a:focus-visible,
        .es-conv-band summary:focus-visible,
        .es-conv-band input:focus-visible,
        .es-conv-band button:focus-visible {
            outline-color: #fdba74 !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-conv-wave-bar { animation: none !important; transform: none !important; opacity: 0.7; }
            html.es-anim .es-conv-mark-line path { animation: none !important; }
            .es-conv-fill { transition: none !important; transform: none !important; }
        }
    </style>

    @php
        // One session's agenda. Event parts carry a name, a start time and an
        // end time, so this sheet is the product's own structure on paper.
        $agenda = [
            ['18:00', 'Welcome and what shipped this week', 'Five minutes, no slides'],
            ['18:10', 'Open questions from the floor', 'The part everyone came for'],
            ['18:40', 'The poll: what we dig into next month', 'Options the room suggested'],
            ['18:50', 'Wrap and where to find the notes', ''],
        ];

        // Repeats, and the dates it skips. Recurring events are a day-of-week
        // pattern with date exceptions and an end.
        $series = [
            ['Every Thursday', 'The pattern'],
            ['Skips Dec 25 and Jan 1', 'Date exceptions'],
            ['Ends after 20 sessions', 'The end'],
        ];

        // Six things an audience asks, and the setting that answers each.
        $asks = [
            ['Can I come?', 'Registration, with a limit on places', "The session's ticket section, Registration mode", 'Free'],
            ['Where is the link?', 'One join link on the online session', "The session's location fields", 'Free'],
            ['Can you ask this one for me?', 'A poll your audience can suggest options for', 'Engagement, then Polls, on the session', 'Pro'],
            ['Can I say it in advance?', 'Comments, held until you approve them', 'The session page, or one agenda segment', 'Free'],
            ['Tell me when the next one is', 'Follow, then a newsletter you write', 'Followers, then Newsletters', 'Free'],
            ['Can I pay for the deep dive?', 'Named ticket types through your Stripe', "The session's ticket section, Tickets mode", 'Free'],
        ];

        $steps = [
            ['01', 'Set the hour', 'Date, time, and the join link. Add agenda segments if the hour has a shape, each with its own start and end time.'],
            ['02', 'Open registration', 'Switch the session to Registration, set how many places there are, and write the note that rides along with every confirmation email.'],
            ['03', 'Collect the questions', 'A poll the room can add options to, or comments on the page. Then host the hour wherever you already host it.'],
        ];

        $faqs = [
            [
                'q' => 'Can I collect audience questions before the session?',
                'a' => 'Yes, three ways, and it is worth knowing exactly what each one is. A poll on the session lets people vote between your options and, if you allow it, suggest their own, which you approve before anyone sees them; polls are on the Pro plan. Comments are free: your audience can leave one on the session, or on a single agenda segment, and nothing appears until you approve it. And the note you attach to registration goes out with every confirmation email, so you can simply ask people to reply with what they want covered.',
            ],
            [
                'q' => 'What streaming platforms work with Event Schedule?',
                'a' => 'Any platform that gives you a meeting or streaming link. Zoom, Google Meet, Microsoft Teams, YouTube Live, or whatever you move to next year. Mark the session as online and paste the link: it shows on the session page and on each attendee\'s own registration page. To be straight with you, this is one link field rather than a streaming integration, and there is no embedded player.',
            ],
            [
                'q' => 'Can I charge for live Q&A sessions?',
                'a' => 'Yes, and you do not have to pay us before you start. Connect your own Stripe account and sell named ticket types for a premium AMA or a paid deep dive, each with its own price, quantity and sales window. The free plan sells up to 25 paid tickets a month per schedule, and scanning a ticket\'s QR code at the door is free on every plan; Pro, at '.plan_price($proMonthly).' a month, removes that ceiling and adds the rest of the door tooling, including the live check-in dashboard, discount codes, add-ons and a waitlist on a sold-out ticket type. Event Schedule charges zero platform fees at every plan level, free included, so past Stripe\'s own processing fee the money is yours. Free sessions do not need any of this: registration with a place limit is free.',
            ],
            [
                'q' => 'Is Event Schedule free for hosting Q&A sessions?',
                'a' => 'Yes. Unlimited sessions, registration with a capacity limit, the agenda, recurring office hours, the embeddable calendar, the embeddable registration widget, two-way Google, Outlook and CalDAV sync, built-in analytics and newsletters are all free forever. Selling is free to start too, at 25 paid tickets a month. Polls, custom questions on the registration form and unlimited ticket sales are on the Pro plan at '.plan_price($proMonthly).' a month. There are zero platform fees on ticket sales on every plan.',
            ],
            [
                'q' => 'Do my followers get an email when I schedule a new session?',
                'a' => 'Anyone who left you an email address and confirmed it, yes: a newly scheduled session reaches them as a digest, batched and never more often than once every few days, and it does not draw on your newsletter allowance. Somebody who followed you from their own account is a separate list, reached only by a newsletter you write, with 10 recipients a month on the free plan, 100 on Pro and 1,000 on Enterprise, counted per recipient rather than per send.',
            ],
            [
                'q' => 'Can I cap how many people join?',
                'a' => 'Yes, and the count is per session date. Set a number of places on the session and every occurrence of a weekly office hour counts its own registrations, so this Thursday filling up does not close next Thursday. The page shows how many places are left, and once a date is full it stops taking registrations for that date. A waitlist for a full registration date is free as well; it is the waitlist on a sold-out paid ticket type that is a Pro feature.',
            ],
        ];

        $dotSections = [
            ['top', 'The hour'],
            ['arrive', 'Getting in'],
            ['ask', 'Asking early'],
            ['link', 'The link'],
            ['series', 'Every Thursday'],
            ['asks', 'Six asks'],
            ['rest', 'Everything else'],
            ['who', 'Perfect for'],
            ['faq', 'Questions'],
            ['claim', 'Open the hour'],
        ];

        // The waveform: alternating turns. Above the line the audience, below
        // it the host, and a long answer follows a short question.
        $waveQ = [];
        $waveA = [];
        foreach (range(0, 71) as $i) {
            $waveQ[] = $i % 2 === 0 ? 34 + (($i * 17) % 62) : 8;
            $waveA[] = $i % 2 === 1 ? 30 + (($i * 23) % 66) : 7;
        }
    @endphp

    <div id="es-conv-page" class="es-conv-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: one hour, and the sheet that runs it                -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(176, 58, 6, 0.2), rgba(176, 58, 6, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(253, 186, 116, 0.16), rgba(253, 186, 116, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <div class="es-conv-wave absolute bottom-6 left-0 right-0 opacity-60 es-conv-wave-mask">
                <div class="es-conv-wave-row">
                    @foreach ($waveQ as $wi => $wh)
                        <span class="es-conv-wave-bar" style="--wb: {{ $wh }}%; --wd: {{ 2.8 + ($wi % 5) * 0.3 }}s; --wdelay: {{ ($wi % 9) * 0.14 }}s;"></span>
                    @endforeach
                </div>
                <div class="es-conv-wave-rule"></div>
                <div class="es-conv-wave-row es-conv-wave-a">
                    @foreach ($waveA as $wi => $wh)
                        <span class="es-conv-wave-bar" style="--wb: {{ $wh }}%; --wd: {{ 3.1 + ($wi % 4) * 0.28 }}s; --wdelay: {{ ($wi % 7) * 0.16 }}s;"></span>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="es-conv-accent h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <span class="es-conv-muted text-sm font-medium tracking-wide">For AMAs, office hours and live Q&amp;A</span>
                    </div>

                    <h1 class="es-balance es-conv-ink mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">A live Q&amp;A is a</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="es-conv-accent es-conv-mark">conversation<svg class="es-conv-mark-line" viewBox="0 0 240 12" fill="none" preserveAspectRatio="none" aria-hidden="true"><path d="M3 8.4C48 3.2 118 2.4 237 6.2" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" /></svg></span>, so plan it like one.</span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-conv-muted mb-10 max-w-xl text-lg sm:text-xl">
                        Registration with a real limit on places, an agenda your audience can read before they arrive, a poll they can add their own option to, and one link to wherever you are hosting. Zero platform fees, on every plan.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="#arrive" class="glass group inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            Read the turns
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="es-conv-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Create your Q&amp;A schedule
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- The run sheet: one session's agenda, on paper. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-conv-sheet">
                        <div class="es-conv-sheet-body">
                            <div class="mb-3 flex flex-wrap items-baseline justify-between gap-2">
                                <span class="es-conv-sheet-title">Office Hours #14</span>
                                <span class="es-conv-sheet-meta">THU 18:00 &middot; 55 MIN &middot; ONLINE</span>
                            </div>
                            <div>
                                @foreach ($agenda as [$slotTime, $slotName, $slotNote])
                                    <div class="es-conv-slot">
                                        <span class="es-conv-time">{{ $slotTime }}</span>
                                        <span>
                                            <span class="es-conv-slot-name">{{ $slotName }}</span>
                                            @if ($slotNote)
                                                <span class="es-conv-slot-note block">{{ $slotNote }}</span>
                                            @endif
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <span class="es-conv-sheet-stamp">40 places &middot; 6 left</span>
                                <span class="es-conv-sheet-stamp">Free registration</span>
                            </div>
                            <p class="es-conv-sheet-note">
                                Agenda segments are free on every plan, and each one can take its own comments. The places counter is per date, so next Thursday starts again at forty.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Session-type marquee -->
            <div class="es-fade-up es-d-4 mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach (['AMAs', 'Office Hours', 'Town Halls', 'Expert Panels', 'Fireside Chats', 'Community Q&As', 'Roundtables', 'Ask Me Anything'] as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-conv-chip">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The only three numbers on this page                       -->
    <!-- ============================================================ -->
    <section class="es-conv-alt border-y py-14 es-conv-hr">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <p class="es-conv-tag mb-8 text-center" data-reveal>The only three numbers on this page</p>
            <div class="grid gap-6 text-center md:grid-cols-3" data-reveal-group="90">
                <div data-reveal class="px-4">
                    <div class="es-conv-accent mb-2 text-4xl font-black">{{ plan_price(0) }}</div>
                    <div class="es-conv-ink text-sm font-semibold">platform fees on ticket sales</div>
                    <div class="es-conv-muted mt-1 text-xs">Every plan, including free. Stripe still charges its own processing fee.</div>
                </div>
                <div data-reveal class="es-conv-statmid">
                    <div class="es-conv-accent mb-2 text-4xl font-black"><span data-count-to="5">5</span></div>
                    <div class="es-conv-ink text-sm font-semibold">polls per session, 2 to 10 options each</div>
                    <div class="es-conv-muted mt-1 text-xs">Polls are a Pro feature. Your audience can suggest options if you let them.</div>
                </div>
                <div data-reveal class="px-4">
                    <div class="es-conv-accent mb-2 text-4xl font-black">10 <span class="es-conv-muted text-2xl">/</span> 100 <span class="es-conv-muted text-2xl">/</span> 1,000</div>
                    <div class="es-conv-ink text-sm font-semibold">newsletter recipients a month</div>
                    <div class="es-conv-muted mt-1 text-xs">Free, Pro, Enterprise. Counted per recipient, not per send.</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Turn 01: getting in                                       -->
    <!-- ============================================================ -->
    <section id="arrive" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="es-conv-turn">
                <div class="es-conv-rail" data-reveal>
                    <span class="es-conv-label es-conv-label-q">Q<span class="es-conv-label-n">01</span></span>
                    <span class="es-conv-thread" aria-hidden="true"></span>
                </div>
                <div>
                    <h2 class="es-balance es-conv-ink mb-5 text-3xl font-black tracking-tight md:text-4xl" data-reveal>
                        How does anybody actually <span class="es-conv-accent">get in?</span>
                    </h2>
                    <p class="es-conv-muted mb-10 max-w-2xl text-lg" data-reveal style="--reveal-delay: 0.1s;">
                        Registration. It is free, it is native, and it counts places per session date. A name and an email is all your audience has to type, and no account is required of them.
                    </p>

                    <div class="grid gap-6 md:grid-cols-3" data-reveal-group="100">
                        <div class="es-conv-card p-7" data-reveal="panel">
                            <div class="mb-3 flex flex-wrap items-center gap-2">
                                <h3 class="es-conv-ink text-lg font-bold">A list, not a form you built</h3>
                                <span class="es-conv-plan">Free</span>
                            </div>
                            <p class="es-conv-muted text-sm">Set the session to Registration instead of Tickets and it collects sign-ups itself. Every registration lands in the same place your ticket sales would.</p>
                        </div>
                        <div class="es-conv-card p-7" data-reveal="panel">
                            <div class="mb-3 flex flex-wrap items-center gap-2">
                                <h3 class="es-conv-ink text-lg font-bold">A limit that counts per date</h3>
                                <span class="es-conv-plan">Free</span>
                            </div>
                            <p class="es-conv-muted text-sm">Give the session a number of places. A weekly office hour counts each Thursday separately, shows how many are left, and closes registration when that date is full.</p>
                        </div>
                        <div class="es-conv-card p-7" data-reveal="panel">
                            <div class="mb-3 flex flex-wrap items-center gap-2">
                                <h3 class="es-conv-ink text-lg font-bold">The confirmation email is yours to write</h3>
                                <span class="es-conv-plan">Free</span>
                            </div>
                            <p class="es-conv-muted text-sm">Registration notes ride along with every confirmation. Put the joining instructions, the house rules, or an invitation to reply with a question in there once.</p>
                        </div>
                    </div>

                    <p class="es-conv-muted mt-8 max-w-2xl text-sm" data-reveal>
                        The honest limit: custom questions on that form, the ones asking what somebody does or what they want covered, are a Pro feature. The free list collects a name and an email, and one registration per person per date.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Turn 02: asking early (fixed-dark band)                   -->
    <!-- ============================================================ -->
    <section id="ask" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-conv-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 20%, rgba(253, 186, 116, 0.12), rgba(253, 186, 116, 0) 60%); opacity: 0.5;"></div>
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
                <div class="es-conv-wave absolute left-0 right-0 top-8 opacity-40 es-conv-wave-mask">
                    <div class="es-conv-wave-row">
                        @foreach ($waveQ as $wi => $wh)
                            <span class="es-conv-wave-bar" style="--wb: {{ $wh }}%; --wd: {{ 3.4 + ($wi % 5) * 0.26 }}s; --wdelay: {{ ($wi % 8) * 0.15 }}s;"></span>
                        @endforeach
                    </div>
                    <div class="es-conv-wave-rule"></div>
                    <div class="es-conv-wave-row es-conv-wave-a">
                        @foreach ($waveA as $wi => $wh)
                            <span class="es-conv-wave-bar" style="--wb: {{ $wh }}%; --wd: {{ 3.6 + ($wi % 4) * 0.24 }}s; --wdelay: {{ ($wi % 6) * 0.18 }}s;"></span>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-6xl">
                <div class="es-conv-turn">
                    <div class="es-conv-rail" data-reveal>
                        <span class="es-conv-label es-conv-label-q">Q<span class="es-conv-label-n">02</span></span>
                        <span class="es-conv-thread" aria-hidden="true"></span>
                    </div>
                    <div>
                        <h2 class="es-balance mb-5 text-3xl font-black tracking-tight text-white md:text-4xl" data-reveal>
                            Can I ask my question <span class="es-conv-lit">before we start?</span>
                        </h2>
                        <p class="mb-10 max-w-2xl text-lg es-conv-onband" data-reveal style="--reveal-delay: 0.1s;">
                            Three real surfaces, and it is worth being precise about what each one is. A poll the room can add options to. Comments held for approval, which can hang off a single agenda segment. And the email you already send.
                        </p>

                        <!-- The poll, both sides of it -->
                        <div class="grid gap-6 lg:grid-cols-2" data-reveal-group="110">
                            <div class="es-conv-card p-6 sm:p-7" data-reveal="panel">
                                <div class="mb-4 flex flex-wrap items-center gap-2">
                                    <p class="es-conv-tag">What the room sees</p>
                                    <span class="es-conv-plan es-conv-plan-pro">Pro</span>
                                </div>
                                <p class="es-conv-poll-q es-conv-ink mb-3">Which should we spend the hour on?</p>
                                <div aria-hidden="true">
                                    @foreach ([['The pricing change', 24, true, 0.15], ['The API rewrite', 18, false, 0.28], ['Roadmap for the quarter', 11, false, 0.41]] as [$optName, $optCount, $optMine, $optDelay])
                                        <div class="es-conv-opt">
                                            <div class="es-conv-opt-head">
                                                <span class="es-conv-ink">{{ $optName }}</span>
                                                <span class="es-conv-count {{ $optMine ? 'es-conv-lit' : 'es-conv-muted' }}">{{ $optCount }}</span>
                                            </div>
                                            <div class="es-conv-track">
                                                <div class="es-conv-fill {{ $optMine ? 'es-conv-fill-mine' : '' }}" style="width: {{ round($optCount / 24 * 100) }}%; --fd: {{ $optDelay }}s;"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                    <div class="es-conv-suggest">
                                        <span>Suggest another option</span>
                                        <span class="es-conv-suggest-btn">+</span>
                                    </div>
                                </div>
                                <p class="es-conv-muted mt-4 text-xs">
                                    Results appear once you have voted: one vote each, from a signed-in account, counted per date. Close the poll and the results are on the page for everyone. More on <a href="{{ marketing_url('/features/polls') }}" class="es-conv-link font-medium hover:underline">event polls</a>.
                                </p>
                            </div>

                            <div class="es-conv-card p-6 sm:p-7" data-reveal="panel">
                                <div class="mb-4 flex flex-wrap items-center gap-2">
                                    <p class="es-conv-tag">What you see</p>
                                    <span class="es-conv-plan es-conv-plan-pro">Pro</span>
                                </div>
                                <div class="mb-4 flex items-center gap-2" aria-hidden="true">
                                    <span class="es-conv-ink text-sm font-semibold">Engagement</span>
                                    <span class="es-conv-muted text-sm">/</span>
                                    <span class="es-conv-lit text-sm font-semibold">Polls</span>
                                    <span class="es-conv-badge">1</span>
                                </div>
                                <div class="es-conv-pending" aria-hidden="true">
                                    <span class="es-conv-ink">&ldquo;Migrating off the old plan&rdquo;</span>
                                    <span class="flex flex-none items-center gap-1.5">
                                        <span class="es-conv-mini">Approve</span>
                                        <span class="es-conv-mini">Reject</span>
                                    </span>
                                </div>
                                <p class="es-conv-muted mt-4 text-sm">
                                    Turn on suggestions and you can also require your approval, so a suggested option waits here until you accept it. Ten options is the ceiling, pending ones included.
                                </p>
                                <p class="es-conv-muted mt-3 text-sm">
                                    The Polls tab always carries a count of what is waiting. The email about it is a toggle you switch on, and on the hosted plan that one sends through your own email settings. Either way, the notifications run toward the host.
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 grid gap-6 md:grid-cols-2" data-reveal-group="100">
                            <div class="es-conv-card p-7" data-reveal="panel">
                                <div class="mb-3 flex flex-wrap items-center gap-2">
                                    <h3 class="es-conv-ink text-lg font-bold">Comments, on the segment they are about</h3>
                                    <span class="es-conv-plan">Free</span>
                                </div>
                                <p class="es-conv-muted text-sm">Your audience can leave a comment on the session, or on one agenda segment, with just a name and an email. Nothing shows until you approve it, and the pending count sits on the Fan Content tab; the email about it is a toggle you switch on. A per-schedule toggle can require an account instead. More on <a href="{{ marketing_url('/features/fan-videos') }}" class="es-conv-link font-medium hover:underline">audience content</a>.</p>
                            </div>
                            <div class="es-conv-card p-7" data-reveal="panel">
                                <div class="mb-3 flex flex-wrap items-center gap-2">
                                    <h3 class="es-conv-ink text-lg font-bold">Or just ask by email</h3>
                                    <span class="es-conv-plan">Free</span>
                                </div>
                                <p class="es-conv-muted text-sm">The registration note goes to everyone who signed up. A newsletter goes to everyone who follows you, and once you are on Pro a poll can be dropped into it with a button that votes on the session page.</p>
                            </div>
                        </div>

                        <p class="mt-10 max-w-3xl es-conv-onband" data-reveal>
                            What Event Schedule does not have, so you are not surprised on day one: an upvoting question queue with a moderation console. A poll is a poll. Your question, up to ten options, one vote each, and a small approve or reject on any option the room suggests.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Turn 03: the link                                         -->
    <!-- ============================================================ -->
    <section id="link" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="es-conv-turn">
                <div class="es-conv-rail" data-reveal>
                    <span class="es-conv-label es-conv-label-q">Q<span class="es-conv-label-n">03</span></span>
                    <span class="es-conv-thread" aria-hidden="true"></span>
                </div>
                <div>
                    <h2 class="es-balance es-conv-ink mb-5 text-3xl font-black tracking-tight md:text-4xl" data-reveal>
                        So where do we <span class="es-conv-accent">actually meet?</span>
                    </h2>
                    <p class="es-conv-muted mb-10 max-w-2xl text-lg" data-reveal style="--reveal-delay: 0.1s;">
                        Wherever you already host. Mark the session online, paste the link, and it appears on the session page and on each attendee's own registration page.
                    </p>

                    <div class="grid items-start gap-10 lg:grid-cols-2">
                        <div class="grid gap-6 sm:grid-cols-2" data-reveal-group="100">
                            <div class="es-conv-card p-7" data-reveal="panel">
                                <div class="mb-3 flex flex-wrap items-center gap-2">
                                    <h3 class="es-conv-ink text-lg font-bold">One field, any platform</h3>
                                    <span class="es-conv-plan">Free</span>
                                </div>
                                <p class="es-conv-muted text-sm">Zoom, Google Meet, Microsoft Teams, YouTube Live, or the thing you switch to next year. Learn more about <a href="{{ marketing_url('/features/online-events') }}" class="es-conv-link font-medium hover:underline">online event features</a>.</p>
                            </div>
                            <div class="es-conv-card p-7" data-reveal="panel">
                                <div class="mb-3 flex flex-wrap items-center gap-2">
                                    <h3 class="es-conv-ink text-lg font-bold">In the room and online</h3>
                                    <span class="es-conv-plan">Free</span>
                                </div>
                                <p class="es-conv-muted text-sm">In person and online are separate ticks, so a town hall can have a venue on the map and a join link for everyone who cannot be there.</p>
                            </div>
                            <div class="es-conv-card p-7 sm:col-span-2" data-reveal="panel">
                                <div class="mb-3 flex flex-wrap items-center gap-2">
                                    <h3 class="es-conv-ink text-lg font-bold">The link lands where they will look for it</h3>
                                    <span class="es-conv-plan">Free</span>
                                </div>
                                <p class="es-conv-muted text-sm">Registering gives somebody their own page for that date, and the join link is on it. The confirmation email links straight there, so nobody has to search their inbox for a link you sent in March.</p>
                            </div>
                        </div>

                        <div data-reveal="panel">
                            <div class="es-conv-card p-6">
                                <p class="es-conv-tag mb-4">Being straight with you</p>
                                <p class="es-conv-muted text-sm leading-relaxed">
                                    This is one link field, not a streaming integration. There is no embedded player, no viewer count, and nothing reads back from the platform you host on. That is also why it has never broken when a platform changed its API: it is a URL, and it is yours.
                                </p>
                                <div class="mt-5 flex flex-wrap gap-2" aria-hidden="true">
                                    @foreach (['Zoom', 'Google Meet', 'Teams', 'YouTube Live', 'Anything with a URL'] as $platform)
                                        <span class="es-conv-chip">{{ $platform }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Turn 04: every Thursday                                   -->
    <!-- ============================================================ -->
    <section id="series" class="es-conv-alt scroll-mt-24 border-y py-20 es-conv-hr lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="es-conv-turn">
                <div class="es-conv-rail" data-reveal>
                    <span class="es-conv-label es-conv-label-q">Q<span class="es-conv-label-n">04</span></span>
                    <span class="es-conv-thread" aria-hidden="true"></span>
                </div>
                <div>
                    <h2 class="es-balance es-conv-ink mb-5 text-3xl font-black tracking-tight md:text-4xl" data-reveal>
                        Is this a one-off, or <span class="es-conv-accent">every Thursday?</span>
                    </h2>
                    <p class="es-conv-muted mb-10 max-w-2xl text-lg" data-reveal style="--reveal-delay: 0.1s;">
                        Office hours are one recurring session, not fifty copies. Pick the days it runs, take out the dates you are away, and give the run an end so it is not still open in eighteen months.
                    </p>

                    <div class="grid items-start gap-10 lg:grid-cols-2">
                        <div data-reveal="panel">
                            <div class="es-conv-sheet">
                                <div class="es-conv-sheet-body">
                                    <div class="mb-3 flex flex-wrap items-baseline justify-between gap-2">
                                        <span class="es-conv-sheet-title">Office Hours</span>
                                        <span class="es-conv-sheet-meta">RECURRING</span>
                                    </div>
                                    <div>
                                        @foreach ($series as $seriesIndex => [$seriesLine, $seriesLabel])
                                            <div class="es-conv-slot">
                                                <span class="es-conv-time">{{ str_pad($seriesIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                                <span>
                                                    <span class="es-conv-slot-name">{{ $seriesLine }}</span>
                                                    <span class="es-conv-slot-note block">{{ $seriesLabel }}</span>
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                    <p class="es-conv-sheet-note">
                                        One session, one agenda, one link, and its own count of places on every date it runs. Change the time once and every remaining Thursday follows.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2" data-reveal-group="100">
                            <div class="es-conv-card p-7" data-reveal="panel">
                                <div class="mb-3 flex flex-wrap items-center gap-2">
                                    <h3 class="es-conv-ink text-lg font-bold">The days it runs</h3>
                                    <span class="es-conv-plan">Free</span>
                                </div>
                                <p class="es-conv-muted text-sm">A day-of-week pattern with a start time. Read more about <a href="{{ marketing_url('/features/recurring-events') }}" class="es-conv-link font-medium hover:underline">recurring events</a>.</p>
                            </div>
                            <div class="es-conv-card p-7" data-reveal="panel">
                                <div class="mb-3 flex flex-wrap items-center gap-2">
                                    <h3 class="es-conv-ink text-lg font-bold">The weeks you skip</h3>
                                    <span class="es-conv-plan">Free</span>
                                </div>
                                <p class="es-conv-muted text-sm">Date exceptions take single dates out, or put an extra one in, without rebuilding the series. A skipped date is simply not there for your audience.</p>
                            </div>
                            <div class="es-conv-card p-7" data-reveal="panel">
                                <div class="mb-3 flex flex-wrap items-center gap-2">
                                    <h3 class="es-conv-ink text-lg font-bold">Two kinds of hour</h3>
                                    <span class="es-conv-plan">Free</span>
                                </div>
                                <p class="es-conv-muted text-sm">Sub-schedules keep the weekly office hour and the quarterly AMA apart on one link, each with its own colour, so nobody scrolls past what they came for.</p>
                            </div>
                            <div class="es-conv-card p-7" data-reveal="panel">
                                <div class="mb-3 flex flex-wrap items-center gap-2">
                                    <h3 class="es-conv-ink text-lg font-bold">In their calendar too</h3>
                                    <span class="es-conv-plan">Free</span>
                                </div>
                                <p class="es-conv-muted text-sm">Anyone can download a single date as a calendar file, and your own Google, Outlook or CalDAV calendar syncs both ways.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. The record: six asks, and what answers each               -->
    <!-- ============================================================ -->
    <section id="asks" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="es-conv-turn">
                <div class="es-conv-rail" data-reveal>
                    <span class="es-conv-label">A<span class="es-conv-label-n">ALL</span></span>
                    <span class="es-conv-thread" aria-hidden="true"></span>
                </div>
                <div class="min-w-0">
                    <h2 class="es-balance es-conv-ink mb-5 text-3xl font-black tracking-tight md:text-4xl" data-reveal>
                        Six things an audience asks, and the <span class="es-conv-accent">setting that answers it</span>
                    </h2>
                    <p class="es-conv-muted mb-10 max-w-2xl text-lg" data-reveal style="--reveal-delay: 0.1s;">
                        Every row names where the setting lives and which plan it is on. Nothing on this page is anywhere else.
                    </p>

                    <div class="es-conv-card p-5 sm:p-7" data-reveal="panel">
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse text-left">
                                <caption class="sr-only">What a live Q&A audience asks for, the Event Schedule setting that answers it, where that setting lives, and the plan it is on</caption>
                                <thead>
                                    <tr class="es-conv-tag">
                                        <th scope="col" class="pb-3 pe-4 font-bold">They ask</th>
                                        <th scope="col" class="pb-3 pe-4 font-bold">You turn on</th>
                                        <th scope="col" class="hidden pb-3 pe-4 font-bold md:table-cell">Where it lives</th>
                                        <th scope="col" class="pb-3 font-bold">Plan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($asks as [$askQ, $askWhat, $askWhere, $askPlan])
                                        <tr class="border-t es-conv-hr">
                                            <th scope="row" class="es-conv-ink py-4 pe-4 align-top text-sm font-bold">{{ $askQ }}</th>
                                            <td class="es-conv-muted py-4 pe-4 align-top text-sm">
                                                {{ $askWhat }}
                                                <span class="es-conv-muted mt-1 block text-xs md:hidden">{{ $askWhere }}</span>
                                            </td>
                                            <td class="es-conv-muted hidden py-4 pe-4 align-top font-mono text-xs md:table-cell">{{ $askWhere }}</td>
                                            <td class="py-4 align-top">
                                                <span class="es-conv-plan {{ $askPlan === 'Pro' ? 'es-conv-plan-pro' : '' }}">{{ $askPlan }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p class="es-conv-muted mt-5 text-xs">
                            Pro is {{ plan_price($proMonthly) }} a month, and on the ticketing row it is what lifts the free plan's ceiling of 25 paid tickets a month. Zero platform fees on ticket sales applies on every plan, including the free one.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Everything else: bento                                    -->
    <!-- ============================================================ -->
    <section id="rest" class="es-conv-alt scroll-mt-24 border-y py-20 es-conv-hr lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <p class="es-conv-tag mb-4" data-reveal>Everything else</p>
                <h2 class="es-balance es-conv-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    Between one hour and the next.
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">
                <!-- 1 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-conv-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-conv-ink text-xl font-bold">Tell the people who already follow you</h3>
                                <span class="es-conv-plan">Free</span>
                            </div>
                            <p class="es-conv-muted mb-4">Your audience follows the schedule, and you see who they are. When there is something worth saying, you write a newsletter and send it, to everyone or to a segment, and you get open and click rates back.</p>
                            <p class="es-conv-muted text-sm">Nothing goes out on its own. The allowance is 10 recipients a month on free, 100 on Pro and 1,000 on Enterprise, counted per recipient rather than per send. Read more about <a href="{{ marketing_url('/features/newsletters') }}" class="es-conv-link font-medium hover:underline">newsletters</a>.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-conv-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-conv-ink text-xl font-bold">A code people can point a phone at</h3>
                                <span class="es-conv-plan">Free</span>
                            </div>
                            <p class="es-conv-muted">Every schedule has a QR code that takes somebody to your page and lets them follow you. Put it on the last slide of the session, which is when people actually want it.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-conv-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-conv-ink text-xl font-bold">On the site you already have</h3>
                                <span class="es-conv-plan">Free</span>
                            </div>
                            <p class="es-conv-muted mb-4">Embed the calendar so your sessions sit where people look you up, and sync two ways with Google, Outlook and CalDAV so your own week stays honest.</p>
                            <p class="es-conv-muted text-sm">Built-in <a href="{{ marketing_url('/features/analytics') }}" class="es-conv-link font-medium hover:underline">analytics</a> show page views, the devices people are on and where the traffic came from. That is what they measure, and nothing more. Embedding the registration form itself on another site is free too; it is the ticket purchase widget that is a Pro feature.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-conv-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-conv-ink text-xl font-bold">Not announced yet</h3>
                                <span class="es-conv-plan">Free</span>
                            </div>
                            <p class="es-conv-muted">A session you are still thinking about sits on your calendar as a draft. You can see it, your audience cannot, and publishing is one switch when the date is real.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-conv-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-conv-ink text-xl font-bold">When the session is worth paying for</h3>
                                <span class="es-conv-plan">Free</span>
                            </div>
                            <p class="es-conv-muted mb-4">Connect your own Stripe and sell named ticket types for a paid AMA or a small-group deep dive, each with its own price, quantity and sales window. Selling starts on the free plan, at 25 paid tickets a month.</p>
                            <p class="es-conv-muted text-sm">Scanning a ticket's QR code at the door is free too. Pro takes that ceiling off and adds the rest of the door tooling: the live check-in dashboard, discount codes for the people you want back, add-ons and a waitlist on a sold-out ticket type. Quantities count per date, the same way places do. Event Schedule takes zero platform fees on every plan, so past Stripe's own processing the money is yours. See all <a href="{{ marketing_url('/features/ticketing') }}" class="es-conv-link font-medium hover:underline">ticketing features</a>.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-conv-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-conv-ink text-xl font-bold">Ask how the hour went</h3>
                                <span class="es-conv-plan es-conv-plan-pro">Pro</span>
                            </div>
                            <p class="es-conv-muted">Post-event feedback collects a star rating and a comment from the people who were there, which is the quietest way to find out whether the hour was worth theirs. On the hosted plan the request sends through your own email settings.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Perfect for                                               -->
    <!-- ============================================================ -->
    <section id="who" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance es-conv-ink mb-4 text-3xl font-black tracking-tight md:text-5xl" data-reveal>
                    Perfect for every type of <span class="es-conv-accent">live Q&amp;A</span>
                </h2>
                <p class="es-conv-muted text-lg sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    A product AMA or a Thursday office hour, it is the same hour of turns. Also see Event Schedule for <a href="{{ marketing_url('/for-webinars') }}" class="es-conv-link font-medium hover:underline">Webinars</a> and <a href="{{ marketing_url('/for-virtual-conferences') }}" class="es-conv-link font-medium hover:underline">Virtual Conferences</a>.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <!-- Tech Founders -->
                <x-sub-audience-card
                    name="Tech Founders"
                    description="Product AMAs, roadmap Q&As, and investor office hours. Let the room vote on what the hour covers before it starts."
                    icon-color="cyan"
                    blog-slug="for-tech-founder-qa"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Coaches & Consultants -->
                <x-sub-audience-card
                    name="Coaches & Consultants"
                    description="Client office hours, group coaching Q&As, and expert sessions. Cap the places so the hour stays useful for everyone in it."
                    icon-color="teal"
                    blog-slug="for-coach-consultant-qa"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Authors & Thought Leaders -->
                <x-sub-audience-card
                    name="Authors & Thought Leaders"
                    description="Book Q&As, fireside chats, and audience discussions. Connect with readers and followers directly."
                    icon-color="sky"
                    blog-slug="for-author-thought-leader-qa"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Community Managers -->
                <x-sub-audience-card
                    name="Community Managers"
                    description="Town halls, member Q&As, and community feedback sessions. Collect what people want raised, then approve what goes on the page."
                    icon-color="blue"
                    blog-slug="for-community-manager-qa"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Educators & Professors -->
                <x-sub-audience-card
                    name="Educators & Professors"
                    description="Student office hours, exam review sessions, and open Q&As. One recurring session with a place limit on every date."
                    icon-color="amber"
                    blog-slug="for-educator-professor-qa"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- HR & Internal Teams -->
                <x-sub-audience-card
                    name="HR & Internal Teams"
                    description="All-hands Q&As, leadership town halls, and policy discussions. Keep the agenda public and the draft ones private until they are ready."
                    icon-color="emerald"
                    blog-slug="for-hr-internal-team-qa"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. Three steps                                              -->
    <!-- ============================================================ -->
    <section class="es-conv-alt border-y py-20 es-conv-hr lg:py-24">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-2xl text-center">
                <h2 class="es-balance es-conv-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal>
                    Three steps to a full hour
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="120">
                @foreach ($steps as [$stepNum, $stepTitle, $stepBody])
                    <div class="es-conv-card p-7" data-reveal="panel">
                        <div class="es-conv-accent mb-3 font-mono text-2xl font-black">{{ $stepNum }}</div>
                        <h3 class="es-conv-ink mb-2 text-lg font-bold">{{ $stepTitle }}</h3>
                        <p class="es-conv-muted text-sm leading-relaxed">{{ $stepBody }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 11. Key features                                             -->
    <!-- ============================================================ -->
    <section class="py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-conv-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Online Events" description="One join link, and it works with any platform" :url="marketing_url('/features/online-events')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="Weekly office hours, with the dates you skip taken out" :url="marketing_url('/features/recurring-events')" icon-color="amber">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Analytics" description="Track page views, devices, and traffic sources" :url="marketing_url('/features/analytics')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Write to the people who follow you, when you have something to say" :url="marketing_url('/features/newsletters')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-conv-link inline-flex items-center font-medium hover:underline">
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
    <section class="border-t py-16 es-conv-hr">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-conv-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4" data-reveal-group="70">
                @foreach ([['/for-webinars', 'Webinars'], ['/for-virtual-conferences', 'Virtual Conferences'], ['/for-online-classes', 'Online Classes'], ['/for-watch-parties', 'Watch Parties']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" class="es-conv-hover es-conv-card group flex flex-col p-5 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-conv-hover-title es-conv-ink mb-3 text-sm font-semibold transition-colors">For {{ $relName }}</span>
                        <span class="es-conv-hover-arrow es-conv-muted mt-auto inline-flex items-center gap-1 text-xs font-medium transition-colors">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
            <div class="mt-8 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-conv-link inline-flex items-center font-medium hover:underline">
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

    <section id="faq" class="es-conv-alt scroll-mt-24 border-t py-20 es-conv-hr lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <p class="es-conv-tag mb-4" data-reveal>Questions</p>
                <h2 class="es-balance es-conv-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-conv-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    What Q&amp;A hosts ask before they move a series across.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-conv-hover es-conv-card group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-conv-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-conv-accent flex-none font-mono text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-conv-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-conv-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-conv-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 14. Finale: the last turn                                    -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-conv-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-conv-wave absolute bottom-7 left-0 right-0 opacity-40 es-conv-wave-mask">
                        <div class="es-conv-wave-row">
                            @foreach ($waveQ as $wi => $wh)
                                <span class="es-conv-wave-bar" style="--wb: {{ $wh }}%; --wd: {{ 3.2 + ($wi % 5) * 0.22 }}s; --wdelay: {{ ($wi % 9) * 0.13 }}s;"></span>
                            @endforeach
                        </div>
                        <div class="es-conv-wave-rule"></div>
                        <div class="es-conv-wave-row es-conv-wave-a">
                            @foreach ($waveA as $wi => $wh)
                                <span class="es-conv-wave-bar" style="--wb: {{ $wh }}%; --wd: {{ 3.5 + ($wi % 4) * 0.2 }}s; --wdelay: {{ ($wi % 7) * 0.15 }}s;"></span>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="relative z-10">
                    <span class="es-conv-label mb-6 inline-flex">A<span class="es-conv-label-n">LAST</span></span>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        You already have the answers. <span class="es-conv-lit">Give them somewhere to ask.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg es-conv-onband">
                        Publishing your sessions, the agenda and registration with a place limit are free forever, and so are your first 25 paid tickets a month. {{ plan_price($proMonthly) }} a month buys polls and no ceiling on what you sell. Nothing is ever taken from the door.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="office-hours" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm es-conv-ondim sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="es-conv-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Get Started Free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="mt-6 text-sm es-conv-ondim">No credit card required</p>
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
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full es-conv-tip border px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
