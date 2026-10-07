<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Live Q&A Sessions | Hosting Software</x-slot>
    <x-slot name="description">Schedule live Q&As and office hours for free: registration with a place limit per date, one join link for Zoom or YouTube Live, and zero platform fees.</x-slot>
    <x-slot name="breadcrumbTitle">For Live Q&A Sessions</x-slot>

    <x-slot name="headMeta">
        {{-- The page's own typefaces, from the fonts the app already bundles (never a CDN). --}}
        <link rel="stylesheet" href="{{ font_stylesheet_url('Sora') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Public Sans') }}">
    </x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Live Q&A Sessions"
        description="Schedule live Q&A sessions and office hours for free: registration with a place limit per date, one join link for Zoom, YouTube Live or any platform, and zero platform fees."
        audience="Q&A Session Hosts"
        keywords="live Q&A platform, Q&A session scheduling, interactive Q&A events, paid Q&A sessions, office hours scheduling, AMA scheduling" />
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
           For-live-qa-sessions "Ask Me Anything" styles.

           The page is a thread. What an audience asks arrives from the
           left in question yellow, the answer comes back from the right
           in the host's ink, and whatever the answer needs to show is
           attached under it as cards, the way a chat attaches things.
           A page about Q&A that is itself a Q&A.

           Two things on it can be pressed, both in plain CSS. The poll
           under "Can I ask my question before we start?" is the
           product's own poll: one vote each, and results only once you
           have voted, counted with a CSS counter. The upvote queue under
           "So where do we actually meet?" is NOT the product: it is a
           picture of the session on whatever platform the join link
           opens, framed and labelled as the other side of that link,
           because the copy says plainly that Event Schedule has no
           upvoting question queue.

           Everything is scoped under #qa with its own tokens. The two
           fixed bands (.qa-night, .qa-sun) restate those tokens as
           literals, so they do not move with the colour mode.
           ============================================================== */

        #qa {
            --qa-paper: #fbfaf6;
            --qa-paper-2: #f2f0e8;
            --qa-card: #ffffff;
            --qa-ink: #15161a;
            --qa-ink-2: #41434c;
            --qa-ink-3: #5f616b;
            --qa-line: rgba(21, 22, 26, 0.15);
            --qa-soft: rgba(21, 22, 26, 0.07);
            --qa-yellow: #ffd84d;
            --qa-host: #15161a;
            --qa-on-host: #fbfaf6;
            --qa-on-host-2: #c9cbd3;
            --qa-hot: #ff5a1f;
            --qa-hot-ink: #bd3a0b;
            --qa-mint: #1fb98a;
            --qa-mint-ink: #0a6f51;
            --qa-d: 'Sora', 'Avenir Next', 'Segoe UI', system-ui, sans-serif;
            --qa-t: 'Public Sans', 'Helvetica Neue', Helvetica, Arial, system-ui, sans-serif;
            --qa-m: ui-monospace, 'SF Mono', Menlo, Consolas, monospace;
            position: relative;
            background-color: var(--qa-paper);
            background-image: radial-gradient(var(--qa-soft) 1px, transparent 1.6px);
            background-size: 22px 22px;
            color: var(--qa-ink);
            font-family: var(--qa-t);
            font-size: 1.0625rem;
            line-height: 1.6;
        }
        .dark #qa {
            --qa-paper: #101114;
            --qa-paper-2: #16171c;
            --qa-card: #1c1e24;
            --qa-ink: #f6f4ec;
            --qa-ink-2: #c6c8d0;
            --qa-ink-3: #9da0aa;
            --qa-line: rgba(246, 244, 236, 0.17);
            --qa-soft: rgba(246, 244, 236, 0.08);
            --qa-host: #2b2e37;
            --qa-hot-ink: #ff8a5c;
            --qa-mint-ink: #5fe0b8;
        }
        /* Asking early: the room with the lights down, in both modes. */
        #qa .qa-night {
            --qa-paper: #15161a;
            --qa-paper-2: #1b1d22;
            --qa-card: #1f2127;
            --qa-ink: #fbfaf6;
            --qa-ink-2: #c9cbd3;
            --qa-ink-3: #a0a3ad;
            --qa-line: rgba(251, 250, 246, 0.17);
            --qa-soft: rgba(251, 250, 246, 0.08);
            --qa-host: #2e313b;
            --qa-hot-ink: #ff8a5c;
            --qa-mint-ink: #5fe0b8;
            background-color: #15161a;
            color: #fbfaf6;
        }
        /* Between sessions: one long question-yellow band, in both modes. */
        #qa .qa-sun {
            --qa-paper: #ffd84d;
            --qa-paper-2: #f7cd39;
            --qa-card: #fffdf4;
            --qa-ink: #15161a;
            --qa-ink-2: #3a3524;
            --qa-ink-3: #574f2c;
            --qa-line: rgba(21, 22, 26, 0.2);
            --qa-soft: rgba(21, 22, 26, 0.08);
            --qa-host: #15161a;
            --qa-hot-ink: #8f2a05;
            --qa-mint-ink: #0a5a42;
            background-color: #ffd84d;
            color: #15161a;
        }

        /* The site's bar takes the thread's paper. */
        body > header.sticky {
            background-color: rgba(251, 250, 246, 0.92);
            border-bottom-color: rgba(21, 22, 26, 0.12);
        }
        .dark body > header.sticky {
            background-color: rgba(16, 17, 20, 0.92);
            border-bottom-color: rgba(246, 244, 236, 0.14);
        }

        #qa ::selection { background: #ffd84d; color: #15161a; }
        #qa a:focus-visible,
        #qa summary:focus-visible,
        #qa input:focus-visible {
            outline: 3px solid var(--qa-hot);
            outline-offset: 3px;
        }
        #qa .qa-sun a:focus-visible,
        #qa .qa-sun summary:focus-visible { outline-color: #15161a; }

        .qa-wrap { width: min(100% - 2rem, 68rem); margin-inline: auto; }
        .qa-d { font-family: var(--qa-d); font-weight: 700; letter-spacing: -0.02em; line-height: 1.1; }
        .qa-a {
            color: var(--qa-ink);
            font-weight: 700;
            text-decoration: underline;
            text-decoration-color: var(--qa-hot);
            text-decoration-thickness: 2px;
            text-underline-offset: 0.18em;
            transition: color 0.2s ease;
        }
        .qa-a:hover { color: var(--qa-hot-ink); }
        .qa-bub-a .qa-a { color: var(--qa-on-host); }
        .qa-bub-a .qa-a:hover { color: #ffd84d; }
        .qa-tier {
            display: inline-flex;
            align-items: center;
            padding: 0.16rem 0.6rem 0.14rem;
            border-radius: 999px;
            background: var(--qa-mint);
            color: #15161a;
            font-family: var(--qa-d);
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.09em;
            text-transform: uppercase;
            line-height: 1.5;
        }
        .qa-tier-pro { background: var(--qa-hot); }
        .qa-tag {
            font-family: var(--qa-m);
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--qa-ink-3);
        }
        .qa-icon { width: 1.15rem; height: 1.15rem; flex: none; }

        /* Buttons are pills, the way a chat's own buttons are. */
        .qa-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            padding: 0.95rem 1.5rem;
            border: 2px solid var(--qa-ink);
            border-radius: 999px;
            background: var(--qa-ink);
            color: var(--qa-paper);
            font-family: var(--qa-d);
            font-weight: 700;
            font-size: 1rem;
            line-height: 1.2;
            transition: translate 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease, color 0.2s ease;
        }
        .qa-btn:hover { translate: 0 -2px; box-shadow: 0 0.6rem 1.2rem -0.5rem rgba(21, 22, 26, 0.55); }
        .qa-btn .qa-icon { transition: translate 0.2s ease; }
        .qa-btn:hover .qa-icon { translate: 3px 0; }
        .qa-btn-ghost { background: transparent; color: var(--qa-ink); }
        .qa-btn-ghost:hover { background: var(--qa-yellow); color: #15161a; border-color: #15161a; }
        .qa-btn-ghost:hover .qa-icon { translate: 0 3px; }

        /* ---------------------------------------------------------------
           The thread header: a chat's own bar, and the section nav
           --------------------------------------------------------------- */
        .qa-bar {
            position: sticky;
            top: calc(4rem + 1px);
            z-index: 30;
            background: var(--qa-paper);
            background: color-mix(in srgb, var(--qa-paper) 90%, transparent);
            -webkit-backdrop-filter: blur(12px);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--qa-line);
        }
        .qa-bar-in { display: flex; align-items: center; gap: 0.75rem; min-height: 3rem; }
        .qa-bar-av {
            flex: none;
            display: grid;
            place-items: center;
            width: 2rem;
            aspect-ratio: 1;
            border-radius: 50%;
            background: #ffd84d;
            color: #15161a;
            font-family: var(--qa-d);
            font-weight: 700;
            font-size: 1.05rem;
        }
        .qa-bar-title { min-width: 0; display: grid; line-height: 1.15; }
        .qa-bar-title b { font-family: var(--qa-d); font-size: 0.9rem; white-space: nowrap; }
        .qa-bar-title small { display: flex; align-items: center; gap: 0.35rem; font-size: 0.72rem; color: var(--qa-ink-3); white-space: nowrap; }
        .qa-bar-title small::before { content: ""; width: 0.45rem; aspect-ratio: 1; border-radius: 50%; background: var(--qa-mint); }
        .qa-bar-nav { margin-inline-start: auto; min-width: 0; }
        .qa-bar-nav ol { display: flex; align-items: center; }
        #qa .qa-pip { display: flex; align-items: center; gap: 0.4rem; min-width: 1.5rem; justify-content: center; padding: 0.7rem 0.5rem; color: var(--qa-ink-3); }
        .qa-pip i { display: block; width: 0.5rem; height: 0.5rem; border-radius: 999px; background: var(--qa-ink-3); opacity: 0.45; transition: width 0.3s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.3s ease, background-color 0.3s ease; }
        .qa-pip span { display: none; font-family: var(--qa-d); font-size: 0.75rem; font-weight: 700; white-space: nowrap; color: var(--qa-ink); }
        .qa-pip:hover i { opacity: 1; }
        .qa-pip.is-active i { width: 1.5rem; opacity: 1; background: var(--qa-hot); }
        .qa-pip.is-active span { display: block; }
        /* On a phone the bar names the turn you are on, and nothing else. */
        @media (max-width: 719px) {
            #qa .qa-pip:not(.is-active) { display: none; }
            #qa .qa-pip.is-active { padding-inline: 0.2rem; }
        }
        .qa-bar-prog { display: none; }
        @supports (animation-timeline: scroll()) {
            html.es-anim #qa .qa-bar-prog {
                display: block;
                position: absolute;
                inset: auto 0 -1px 0;
                height: 2px;
                background: var(--qa-hot);
                transform-origin: 0 50%;
                animation: qa-prog linear both;
                animation-timeline: scroll(root);
            }
        }
        @keyframes qa-prog { from { transform: scaleX(0); } to { transform: scaleX(1); } }

        /* ---------------------------------------------------------------
           Hero: the question, very large
           --------------------------------------------------------------- */
        .qa-hero { position: relative; overflow: clip; padding-block: clamp(1.75rem, 6vw, 4.5rem) clamp(2.5rem, 5vw, 3.5rem); scroll-margin-top: 8rem; }
        /* The art: a question, its answer, and somebody typing. A small strip above the
           headline on a phone, the whole right-hand side of the hero on a desktop. */
        .qa-hero-art { position: relative; order: -1; height: 7rem; pointer-events: none; }
        .qa-q { position: absolute; inset: -0.75rem -1.75rem auto auto; width: 9rem; aspect-ratio: 1; }
        .qa-q-bub {
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            background: #ffd84d;
            border-radius: 50% 50% 50% 10%;
            rotate: 7deg;
        }
        .qa-q-bub b { font-family: var(--qa-d); font-weight: 700; font-size: 6.6rem; line-height: 1; color: #15161a; translate: 0 -2%; }
        .qa-bang {
            position: absolute;
            inset: auto 5.9rem 0 auto;
            display: grid;
            place-items: center;
            width: 3.9rem;
            aspect-ratio: 1;
            border-radius: 50% 50% 10% 50%;
            background: var(--qa-host);
            box-shadow: 0 0 0 0.3rem var(--qa-paper);
            rotate: -8deg;
        }
        .qa-bang b { font-family: var(--qa-d); font-weight: 700; font-size: 2.5rem; line-height: 1; color: #ffd84d; }
        .qa-hero-typing {
            position: absolute;
            inset: auto auto 0.35rem 0;
            display: inline-flex;
            gap: 0.3rem;
            padding: 0.8rem 1rem;
            border-radius: 1.4rem 1.4rem 1.4rem 0.3rem;
            background: var(--qa-card);
            border: 1.5px solid var(--qa-ink);
        }
        .qa-hero-typing i { width: 0.5rem; aspect-ratio: 1; border-radius: 50%; background: var(--qa-ink); animation: qa-dot 1.2s steps(4, jump-none) infinite; animation-delay: calc(var(--i) * 0.18s); }
        @media (min-width: 1000px) {
            .qa-hero-art { order: 0; height: auto; align-self: stretch; min-height: 27rem; }
            .qa-q { inset: -3.5rem -9rem auto auto; width: min(39rem, 46vw); }
            .qa-q-bub b { font-size: min(29rem, 34vw); }
            .qa-bang { inset: auto auto 0.5rem -1.5rem; width: 10.5rem; box-shadow: 0 0 0 0.5rem var(--qa-paper); }
            .qa-bang b { font-size: 7rem; }
            .qa-hero-typing { inset: 4.5rem auto auto 0.5rem; gap: 0.35rem; padding: 1rem 1.2rem; }
            .qa-hero-typing i { width: 0.6rem; }
        }
        html.es-anim #qa .qa-q-bub b { animation: qa-pop 0.9s cubic-bezier(0.34, 1.56, 0.64, 1) 0.25s both; }
        html.es-anim #qa .qa-bang { animation: qa-pop 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) 0.75s both; }
        @keyframes qa-pop { from { scale: 0.5; opacity: 0; } to { scale: 1; opacity: 1; } }
        @supports (animation-timeline: view()) {
            html.es-anim #qa .qa-q-bub {
                animation: qa-q-turn linear both;
                animation-timeline: view();
                animation-range: exit 0% exit 100%;
            }
        }
        @keyframes qa-q-turn { from { rotate: 7deg; translate: 0 0; } to { rotate: -20deg; translate: -6% 10%; } }

        .qa-hero-grid { position: relative; display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.5rem; align-items: center; }
        @media (min-width: 1000px) {
            .qa-hero-grid { grid-template-columns: minmax(0, 1.12fr) minmax(0, 0.88fr); gap: 3.5rem; }
        }
        .qa-hero-copy { min-width: 0; container-type: inline-size; }
        .qa-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 1.5rem;
            padding: 0.45rem 0.95rem 0.45rem 0.6rem;
            border-radius: 999px;
            background: var(--qa-card);
            border: 1.5px solid var(--qa-line);
            font-family: var(--qa-t);
            font-size: 0.9rem;
            font-weight: 700;
            line-height: 1.3;
            color: var(--qa-ink-2);
        }
        .qa-eyebrow-dots { display: inline-flex; gap: 0.2rem; padding: 0.35rem 0.5rem; border-radius: 999px; background: #ffd84d; }
        .qa-eyebrow-dots i { width: 0.32rem; aspect-ratio: 1; border-radius: 50%; background: #15161a; animation: qa-dot 1.2s steps(4, jump-none) infinite; animation-delay: calc(var(--i) * 0.18s); }
        @keyframes qa-dot { 0%, 55%, 100% { translate: 0 0; opacity: 0.45; } 25% { translate: 0 -0.22rem; opacity: 1; } }
        .qa-h1 { font-family: var(--qa-d); font-weight: 700; font-size: clamp(2.4rem, 11.4cqi, 4.9rem); line-height: 1.02; letter-spacing: -0.035em; }
        .qa-mark { background: #ffd84d; color: #15161a; padding: 0 0.1em; margin-inline: -0.1em -0.06em; border-radius: 0.18em; -webkit-box-decoration-break: clone; box-decoration-break: clone; }
        .qa-lede { max-width: 35rem; margin-top: 1.6rem; font-size: clamp(1.08rem, 1.5vw, 1.25rem); color: var(--qa-ink-2); }
        .qa-cta { display: flex; flex-wrap: wrap; gap: 0.8rem 0.9rem; margin-top: 1.9rem; }

        /* The session's own card, as a chat shows it when you tap the header. */
        .qa-sheet {
            position: relative;
            background: var(--qa-card);
            border: 1.5px solid var(--qa-ink);
            border-radius: 1.6rem;
            box-shadow: 0 1.8rem 3rem -1.6rem rgba(21, 22, 26, 0.5);
            overflow: hidden;
        }
        .dark .qa-sheet { border-color: var(--qa-line); box-shadow: 0 1.8rem 3rem -1.6rem #000; }
        .qa-sheet-head { display: flex; align-items: center; gap: 0.8rem; padding: 0.95rem 1.2rem; border-bottom: 1.5px solid var(--qa-line); }
        .qa-sheet-av { flex: none; display: grid; place-items: center; width: 2.6rem; aspect-ratio: 1; border-radius: 50%; background: var(--qa-ink); color: var(--qa-paper); font-family: var(--qa-d); font-weight: 700; font-size: 0.85rem; }
        .qa-sheet-title { display: block; font-family: var(--qa-d); font-weight: 700; font-size: 1.1rem; line-height: 1.2; }
        .qa-sheet-meta { display: block; margin-top: 0.15rem; font-family: var(--qa-m); font-size: 0.7rem; letter-spacing: 0.08em; color: var(--qa-ink-3); }
        .qa-slot { display: grid; grid-template-columns: 3.3rem minmax(0, 1fr); gap: 0.75rem; padding: 0.65rem 1.2rem; border-bottom: 1px solid var(--qa-line); }
        .qa-slot-time { font-family: var(--qa-m); font-weight: 700; font-size: 0.9rem; color: var(--qa-hot-ink); padding-top: 0.1rem; }
        .qa-slot-name { display: block; font-weight: 700; line-height: 1.3; }
        .qa-slot-note { display: block; font-size: 0.88rem; color: var(--qa-ink-3); }
        .qa-seats { padding: 0.95rem 1.2rem 0.2rem; }
        .qa-dots { display: grid; grid-template-columns: repeat(20, minmax(0, 1fr)); gap: 0.26rem; }
        .qa-dots i { aspect-ratio: 1; border-radius: 50%; background: var(--qa-ink); }
        .qa-dots i.is-free { background: transparent; box-shadow: inset 0 0 0 1.5px var(--qa-ink-3); }
        html.es-anim #qa .qa-dots-live i:not(.is-free) { animation: qa-seat 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) both; animation-delay: calc(0.6s + var(--i) * 0.03s); }
        @keyframes qa-seat { from { scale: 0; } to { scale: 1; } }
        .qa-stamps { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-top: 0.75rem; }
        .qa-stamp { padding: 0.22rem 0.65rem; border-radius: 999px; background: #ffd84d; color: #15161a; font-size: 0.8rem; font-weight: 700; }
        .qa-stamp + .qa-stamp { background: var(--qa-mint); }
        .qa-sheet-note { padding: 0.85rem 1.2rem 1.1rem; font-size: 0.9rem; color: var(--qa-ink-2); }

        /* Quick replies: the kinds of hour, sliding past. */
        .qa-chips { position: relative; margin-top: clamp(2.25rem, 5vw, 3.5rem); min-width: 0; }
        .qa-chip {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border: 1.5px solid var(--qa-ink);
            border-radius: 999px;
            font-family: var(--qa-d);
            font-size: 0.9rem;
            font-weight: 700;
            white-space: nowrap;
            color: var(--qa-ink);
        }
        .qa-chip:nth-child(3n + 1) { background: #ffd84d; color: #15161a; border-color: #15161a; }

        /* ---------------------------------------------------------------
           Pinned: the only three numbers on this page
           --------------------------------------------------------------- */
        .qa-pinned { background: var(--qa-paper-2); border-block: 1px solid var(--qa-line); padding-block: clamp(2.5rem, 5vw, 4rem); }
        .qa-pinned-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2rem 3rem; align-items: center; }
        @media (min-width: 940px) { .qa-pinned-grid { grid-template-columns: minmax(0, 0.95fr) minmax(0, 1.05fr); } }
        .qa-pin-head { display: flex; align-items: center; gap: 0.6rem; margin-bottom: 1.5rem; }
        .qa-pin-head::before { content: ""; width: 0.6rem; aspect-ratio: 1; background: var(--qa-hot); rotate: 45deg; border-radius: 0.1rem; }
        .qa-pins { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem; }
        .qa-pin { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0.2rem 1.5rem; align-items: center; padding: 1.15rem 1.4rem; border-radius: 1.5rem 1.5rem 0.3rem 1.5rem; background: var(--qa-card); border: 1.5px solid var(--qa-line); }
        @media (min-width: 560px) {
            .qa-pin { grid-template-columns: 12.5rem minmax(0, 1fr); }
            .qa-pin-n { grid-row: span 2; }
        }
        .qa-pin-n { font-family: var(--qa-d); font-weight: 700; font-size: clamp(1.9rem, 3vw, 2.4rem); white-space: nowrap; line-height: 1; letter-spacing: -0.03em; color: var(--qa-ink); }
        .qa-pin-n i { font-style: normal; font-weight: 400; color: var(--qa-ink-3); }
        .qa-pin-n-long { font-size: 1.5rem; }
        .qa-pin strong { display: block; font-weight: 700; line-height: 1.3; }
        .qa-pin small { display: block; font-size: 0.9rem; color: var(--qa-ink-2); }

        /* ---------------------------------------------------------------
           The thread
           --------------------------------------------------------------- */
        .qa-sec { position: relative; padding-block: clamp(3.25rem, 7vw, 5.5rem); scroll-margin-top: 7rem; }
        .qa-alt { background: var(--qa-paper-2); }
        .qa-thread { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.1rem; container-type: inline-size; }
        .qa-time {
            justify-self: center;
            padding: 0.22rem 0.8rem;
            border-radius: 999px;
            background: var(--qa-soft);
            font-family: var(--qa-m);
            font-size: 0.72rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--qa-ink-3);
        }
        .qa-in { display: flex; align-items: flex-end; gap: 1.05rem; max-width: min(100%, 47rem); }
        .qa-av {
            flex: none;
            display: grid;
            place-items: center;
            width: 2.5rem;
            aspect-ratio: 1;
            border-radius: 50%;
            background: var(--qa-ink);
            color: var(--qa-paper);
            font-family: var(--qa-d);
            font-weight: 700;
            font-size: 0.8rem;
        }
        .qa-in-col { min-width: 0; display: grid; gap: 0.3rem; justify-items: start; }
        .qa-who { padding-inline-start: 0.4rem; font-size: 0.78rem; color: var(--qa-ink-3); }
        .qa-bub { position: relative; min-width: 0; padding: 1.05rem 1.35rem; border-radius: 1.6rem; }
        .qa-bub-q { background: #ffd84d; color: #15161a; border-end-start-radius: 0.25rem; }
        .qa-sun .qa-bub-q { background: #15161a; color: #fbfaf6; }
        .qa-bub-a { background: var(--qa-host); color: var(--qa-on-host); border-end-end-radius: 0.25rem; }
        .qa-bub-a p { font-size: 1.1rem; line-height: 1.55; }
        .qa-bub-a p + p { margin-top: 0.7rem; }
        .qa-bub-a .qa-h2 + p { margin-top: 0.7rem; color: var(--qa-on-host-2); }
        .qa-lit { color: #ffd84d; }
        .qa-tail { position: absolute; bottom: 0; width: 1rem; height: 1rem; background: inherit; }
        .qa-bub-q > .qa-tail {
            inset-inline-start: -1rem;
            -webkit-mask: radial-gradient(1rem at 0 0, transparent 97%, #000);
            mask: radial-gradient(1rem at 0 0, transparent 97%, #000);
        }
        .qa-bub-a > .qa-tail {
            inset-inline-end: -1rem;
            -webkit-mask: radial-gradient(1rem at 100% 0, transparent 97%, #000);
            mask: radial-gradient(1rem at 100% 0, transparent 97%, #000);
        }
        [dir="rtl"] #qa .qa-tail { scale: -1 1; }
        .qa-h2 { font-family: var(--qa-d); font-weight: 700; font-size: clamp(1.55rem, 3.4cqi + 0.55rem, 2.75rem); line-height: 1.12; letter-spacing: -0.025em; text-wrap: balance; }
        .qa-bub-q .qa-h2 { max-width: 12.5em; }
        .qa-em { text-decoration: underline; text-decoration-color: var(--qa-hot); text-decoration-thickness: 0.11em; text-underline-offset: 0.13em; }
        .qa-bub-s { padding: 0.8rem 1.15rem; font-family: var(--qa-d); font-weight: 700; font-size: 1.05rem; line-height: 1.3; }

        .qa-reply { position: relative; display: grid; justify-items: end; gap: 0.35rem; margin-inline: auto 1rem; max-width: min(100% - 1rem, 43rem); }
        .qa-seen { display: inline-flex; align-items: center; gap: 0.35rem; padding-inline-end: 0.3rem; font-size: 0.78rem; color: var(--qa-ink-3); }
        .qa-seen::after { content: "\2713\2713"; font-variant-emoji: text; letter-spacing: -0.28em; font-size: 0.82rem; font-weight: 700; color: var(--qa-mint-ink); }
        .qa-typing { display: none; }
        html.es-anim #qa .qa-typing {
            display: inline-flex;
            position: absolute;
            inset-inline-end: 0;
            top: 0;
            z-index: 1;
            gap: 0.3rem;
            padding: 0.95rem 1.1rem;
            border-radius: 1.4rem 1.4rem 0.25rem 1.4rem;
            background: var(--qa-host);
            pointer-events: none;
        }
        html.es-anim #qa .qa-typing.is-revealed { animation: qa-typing 0.95s ease both; }
        @keyframes qa-typing { 0% { opacity: 0; } 12%, 72% { opacity: 1; } 100% { opacity: 0; } }
        .qa-typing i { width: 0.5rem; aspect-ratio: 1; border-radius: 50%; background: var(--qa-on-host); animation: qa-dot 0.9s steps(4, jump-none) infinite; animation-delay: calc(var(--i) * 0.15s); }

        /* A quieter aside from the host's side: no fill, no tail. */
        .qa-aside { margin-inline: auto 1rem; max-width: min(100% - 1rem, 43rem); padding: 0.95rem 1.25rem; border: 1.5px dashed var(--qa-ink-3); border-radius: 1.5rem 1.5rem 0.25rem 1.5rem; color: var(--qa-ink-2); font-size: 0.98rem; }

        /* Attachments. */
        .qa-cards { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(min(100%, 16.5rem), 1fr)); }
        .qa-card {
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
            min-width: 0;
            padding: 1.35rem 1.4rem;
            background: var(--qa-card);
            border: 1.5px solid var(--qa-line);
            border-radius: 1.4rem;
            color: var(--qa-ink);
        }
        .qa-card-head { display: flex; flex-wrap: wrap; align-items: center; gap: 0.45rem 0.6rem; }
        .qa-card h3 { font-family: var(--qa-d); font-weight: 700; font-size: 1.1rem; line-height: 1.25; letter-spacing: -0.01em; }
        .qa-card p { font-size: 0.98rem; color: var(--qa-ink-2); }
        .qa-card p + p { margin-top: 0.2rem; }
        .qa-cards-2 { grid-template-columns: minmax(0, 1fr); align-content: start; }
        @container (min-width: 36rem) {
            .qa-cards-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .qa-cards-2 > .qa-span { grid-column: 1 / -1; }
        }
        .qa-side { display: grid; gap: 1rem; justify-items: end; margin-inline-start: auto; width: min(100%, 44rem); }
        .qa-side > .qa-links { width: 100%; }
        .qa-side > .qa-rel { justify-content: flex-end; }
        .qa-two { display: grid; gap: 1rem; grid-template-columns: minmax(0, 1fr); align-items: start; }
        @media (min-width: 920px) { .qa-two { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 1.25rem; } }

        /* Per-date places: a strip of forty for each Thursday. */
        .qa-weeks { display: grid; gap: 0.55rem; margin-top: 0.3rem; }
        .qa-week { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0.3rem; }
        .qa-week b { font-weight: 700; }
        .qa-week span { display: flex; justify-content: space-between; gap: 0.5rem; font-family: var(--qa-m); font-size: 0.7rem; letter-spacing: 0.06em; text-transform: uppercase; color: var(--qa-ink-3); }
        .qa-week .qa-dots { gap: 0.18rem; }

        /* ---------------------------------------------------------------
           The poll: the product's own, and it works
           --------------------------------------------------------------- */
        .qa-poll { min-width: 0; border: 0; padding: 0; margin: 0; display: grid; gap: 0.55rem; }
        .qa-poll legend { float: left; width: 100%; padding: 0; margin-bottom: 0.3rem; font-family: var(--qa-d); font-weight: 700; font-size: 1.2rem; line-height: 1.3; color: var(--qa-ink); }
        .qa-opt {
            position: relative;
            isolation: isolate;
            overflow: hidden;
            clear: both;
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            align-items: center;
            gap: 0.75rem;
            padding: 0.8rem 1rem;
            border: 1.5px solid var(--qa-line);
            border-radius: 999px;
            cursor: pointer;
            counter-reset: qa-votes var(--c);
            transition: border-color 0.2s ease;
        }
        .qa-opt:hover { border-color: var(--qa-ink-3); }
        #qa .qa-opt-in {
            -webkit-appearance: none;
            appearance: none;
            width: 1.2rem;
            height: 1.2rem;
            margin: 0;
            border: 2px solid var(--qa-ink-3);
            border-radius: 50%;
            background: transparent none;
            box-shadow: none;
            cursor: pointer;
        }
        #qa .qa-opt-in:checked {
            border-color: var(--qa-hot);
            background: radial-gradient(circle, var(--qa-hot) 0 42%, transparent 46%);
            counter-increment: qa-votes 1;
        }
        #qa .qa-opt-in:focus { outline: none; box-shadow: none; }
        #qa .qa-opt:has(.qa-opt-in:focus-visible) { outline: 3px solid var(--qa-hot); outline-offset: 3px; }
        .qa-opt-name { font-weight: 700; line-height: 1.3; }
        .qa-opt-n { visibility: hidden; font-family: var(--qa-m); font-weight: 700; font-size: 0.95rem; color: var(--qa-ink-2); }
        .qa-opt-n::after { content: counter(qa-votes); }
        .qa-opt-bar { position: absolute; inset: 0; z-index: -1; background: var(--qa-soft); transform-origin: 0 50%; transform: scaleX(0); transition: transform 0.7s cubic-bezier(0.22, 1, 0.36, 1); }
        .qa-poll:has(.qa-opt-in:checked) .qa-opt-n { visibility: visible; }
        .qa-poll:has(.qa-opt-in:checked) .qa-opt-bar { transform: scaleX(calc(var(--c) / 25)); }
        #qa .qa-opt:has(.qa-opt-in:checked) { border-color: var(--qa-hot); }
        #qa .qa-opt:has(.qa-opt-in:checked) .qa-opt-bar { background: rgba(255, 90, 31, 0.3); transform: scaleX(calc((var(--c) + 1) / 25)); }
        #qa .qa-opt:has(.qa-opt-in:checked) .qa-opt-n { color: var(--qa-ink); }
        [dir="rtl"] #qa .qa-opt-bar { transform-origin: 100% 50%; }
        .qa-poll-hint { font-size: 0.85rem; color: var(--qa-ink-3); }
        .qa-poll:has(.qa-opt-in:checked) + .qa-poll-hint { visibility: hidden; }
        .qa-suggest { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; padding: 0.7rem 0.75rem 0.7rem 1.1rem; border: 1.5px dashed var(--qa-line); border-radius: 999px; font-size: 0.95rem; color: var(--qa-ink-3); }
        .qa-suggest b { display: grid; place-items: center; width: 1.7rem; aspect-ratio: 1; border-radius: 50%; background: var(--qa-soft); color: var(--qa-ink); font-size: 1.1rem; line-height: 1; }
        .qa-crumbs { display: flex; align-items: center; gap: 0.5rem; font-family: var(--qa-d); font-weight: 700; font-size: 0.95rem; }
        .qa-crumbs span:nth-child(2) { color: var(--qa-ink-3); font-weight: 400; }
        .qa-crumbs span:nth-child(3) { color: var(--qa-hot-ink); }
        .qa-crumbs b { display: grid; place-items: center; min-width: 1.35rem; height: 1.35rem; border-radius: 999px; background: var(--qa-hot); color: #15161a; font-size: 0.75rem; }
        .qa-pending { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.6rem; padding: 0.8rem 0.9rem 0.8rem 1.1rem; border-radius: 1.2rem; background: var(--qa-soft); font-weight: 700; }
        .qa-pending-acts { display: inline-flex; gap: 0.4rem; }
        .qa-mini { padding: 0.25rem 0.7rem; border-radius: 999px; border: 1.5px solid var(--qa-line); font-size: 0.78rem; font-weight: 700; color: var(--qa-ink-2); }
        .qa-mini:first-child { background: var(--qa-mint); border-color: var(--qa-mint); color: #15161a; }

        /* ---------------------------------------------------------------
           The link, and what is on the other side of it
           --------------------------------------------------------------- */
        .qa-unfurl { display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 0.85rem; align-items: center; width: min(100%, 24rem); padding: 0.75rem 1rem 0.75rem 0.75rem; border-radius: 1.2rem; background: var(--qa-card); border: 1.5px solid var(--qa-line); }
        .qa-unfurl i { display: grid; place-items: center; width: 2.8rem; aspect-ratio: 1; border-radius: 0.8rem; background: #ffd84d; color: #15161a; }
        .qa-unfurl b { display: block; font-family: var(--qa-d); font-size: 0.95rem; line-height: 1.25; color: var(--qa-ink); }
        /* The address wraps on a narrow phone, where one line of it does not fit the card. */
        .qa-unfurl span { display: block; font-family: var(--qa-m); font-size: 0.75rem; color: var(--qa-ink-3); overflow-wrap: anywhere; }
        .qa-platforms { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-top: 0.4rem; }
        .qa-platforms span { padding: 0.3rem 0.75rem; border-radius: 999px; border: 1.5px solid var(--qa-line); font-size: 0.82rem; font-weight: 700; color: var(--qa-ink-2); }
        .qa-platforms span:last-child { background: #ffd84d; border-color: #ffd84d; color: #15161a; }

        .qa-room {
            position: relative;
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 1.25rem 2.5rem;
            padding: clamp(1.1rem, 3vw, 1.75rem);
            border: 2px dashed var(--qa-ink-3);
            border-radius: 1.6rem;
            background: repeating-linear-gradient(135deg, transparent 0 0.6rem, var(--qa-soft) 0.6rem 0.68rem);
        }
        @media (min-width: 920px) { .qa-room { grid-template-columns: minmax(0, 0.78fr) minmax(0, 1.22fr); align-items: center; } }
        .qa-room-flag { display: inline-flex; flex-wrap: wrap; gap: 0.4rem; }
        .qa-room-flag span { padding: 0.25rem 0.7rem; border-radius: 999px; background: var(--qa-ink); color: var(--qa-paper); font-family: var(--qa-m); font-size: 0.7rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; }
        .qa-room-flag span + span { background: transparent; color: var(--qa-ink); box-shadow: inset 0 0 0 1.5px var(--qa-ink); }
        .qa-room-cap { margin-top: 0.9rem; font-family: var(--qa-d); font-weight: 700; font-size: clamp(1.25rem, 2.4vw, 1.6rem); line-height: 1.2; letter-spacing: -0.02em; }
        .qa-room-sub { margin-top: 0.6rem; font-size: 0.95rem; color: var(--qa-ink-2); }
        .qa-queue { display: flex; flex-direction: column; gap: 0.5rem; min-width: 0; }
        .qa-qrow {
            order: calc(var(--v) * -2);
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            align-items: center;
            gap: 0.85rem;
            padding: 0.55rem 0.9rem 0.55rem 0.55rem;
            border-radius: 1.1rem;
            background: var(--qa-card);
            border: 1.5px solid var(--qa-line);
            counter-reset: qa-up var(--v);
        }
        .qa-qrow:has(.qa-up:checked) { order: calc(var(--v) * -2 - 3); border-color: var(--qa-hot); animation: qa-bump 0.5s cubic-bezier(0.34, 1.56, 0.64, 1); }
        @keyframes qa-bump { from { scale: 0.96; } to { scale: 1; } }
        .qa-qrow.is-live { order: -999; background: #ffd84d; border-color: #ffd84d; color: #15161a; }
        .qa-qrow.is-done { order: 9; }
        .qa-qrow.is-done .qa-qtext { text-decoration: line-through; text-decoration-color: var(--qa-ink-3); color: var(--qa-ink-2); }
        .qa-vote {
            position: relative;
            display: grid;
            justify-items: center;
            gap: 0.1rem;
            width: 2.9rem;
            padding: 0.4rem 0 0.3rem;
            border-radius: 0.8rem;
            background: var(--qa-soft);
            color: var(--qa-ink-2);
            cursor: pointer;
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        .qa-vote i { width: 0; height: 0; border-inline: 0.42rem solid transparent; border-bottom: 0.55rem solid currentColor; transition: translate 0.2s cubic-bezier(0.34, 1.56, 0.64, 1); }
        .qa-vote b { font-family: var(--qa-m); font-size: 0.85rem; line-height: 1.2; }
        .qa-vote b::after { content: counter(qa-up); }
        label.qa-vote:hover i { translate: 0 -2px; }
        #qa .qa-up { position: absolute; inset: 0; width: 100%; height: 100%; margin: 0; opacity: 0; cursor: pointer; }
        #qa .qa-up:checked { counter-increment: qa-up 1; }
        .qa-vote:has(.qa-up:checked) { background: var(--qa-hot); color: #15161a; }
        .qa-vote:has(.qa-up:focus-visible) { outline: 3px solid var(--qa-hot); outline-offset: 2px; }
        .qa-qrow.is-live .qa-vote { background: #15161a; color: #ffd84d; }
        .qa-qrow.is-done .qa-vote { background: var(--qa-mint); color: #15161a; }
        .qa-qtext { font-weight: 700; line-height: 1.3; }
        .qa-state { font-family: var(--qa-m); font-size: 0.68rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; white-space: nowrap; }
        @media (max-width: 480px) { .qa-state { display: none; } }

        /* ---------------------------------------------------------------
           Every Thursday
           --------------------------------------------------------------- */
        .qa-thursdays { display: flex; flex-wrap: wrap; gap: 0.4rem; padding: 0.95rem 1.2rem 0.3rem; }
        .qa-thu { display: grid; justify-items: center; min-width: 2.9rem; padding: 0.35rem 0.4rem 0.3rem; border-radius: 0.8rem; background: var(--qa-ink); color: var(--qa-paper); line-height: 1.15; }
        .qa-thu small { font-family: var(--qa-m); font-size: 0.62rem; letter-spacing: 0.08em; text-transform: uppercase; opacity: 0.8; }
        .qa-thu b { font-family: var(--qa-d); font-size: 1.05rem; }
        .qa-thu.is-out { background: transparent; color: var(--qa-ink-3); outline: 1.5px dashed var(--qa-ink-3); outline-offset: -1.5px; }

        /* ---------------------------------------------------------------
           Six asks: a table that stays a table
           --------------------------------------------------------------- */
        .qa-table-card { padding: clamp(0.9rem, 2.5vw, 1.6rem); background: var(--qa-card); border: 1.5px solid var(--qa-line); border-radius: 1.6rem; }
        .qa-table { width: 100%; border-collapse: collapse; text-align: start; }
        .qa-table thead th { padding: 0 0.9rem 0.8rem 0; font-family: var(--qa-m); font-size: 0.72rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--qa-ink-3); text-align: start; }
        .qa-table tbody tr { border-top: 1px solid var(--qa-line); }
        .qa-table tbody th,
        .qa-table tbody td { padding: 0.9rem 0.9rem 0.9rem 0; vertical-align: top; text-align: start; font-size: 0.98rem; }
        .qa-table tbody th span { display: inline-block; padding: 0.4rem 0.85rem; border-radius: 1.1rem 1.1rem 1.1rem 0.25rem; background: #ffd84d; color: #15161a; font-family: var(--qa-d); font-weight: 700; font-size: 0.92rem; line-height: 1.3; }
        .qa-table tbody td { color: var(--qa-ink-2); }
        .qa-table tbody td.qa-where { font-family: var(--qa-m); font-size: 0.82rem; color: var(--qa-ink-3); }
        .qa-table-note { margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--qa-line); font-size: 0.9rem; color: var(--qa-ink-2); }
        @media (max-width: 760px) {
            .qa-table thead { display: none; }
            .qa-table,
            .qa-table tbody,
            .qa-table tbody tr,
            .qa-table tbody th,
            .qa-table tbody td { display: block; }
            .qa-table tbody tr { padding-block: 1rem; }
            .qa-table tbody tr:first-child { border-top: 0; padding-top: 0.2rem; }
            .qa-table tbody th,
            .qa-table tbody td { padding: 0; }
            .qa-table tbody td { margin-top: 0.5rem; }
            .qa-table tbody td[data-label]::before { content: attr(data-label); display: block; font-family: var(--qa-m); font-size: 0.66rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--qa-ink-3); }
        }

        /* ---------------------------------------------------------------
           Between sessions (the yellow band)
           --------------------------------------------------------------- */
        .qa-center { display: grid; justify-items: center; gap: 0.9rem; text-align: center; margin-bottom: clamp(2rem, 4vw, 3rem); }
        .qa-center-sub { max-width: 34rem; color: var(--qa-ink-2); font-size: 1.1rem; }
        .qa-big { font-family: var(--qa-d); font-weight: 700; font-size: clamp(2.1rem, 6vw, 4rem); line-height: 1.02; letter-spacing: -0.035em; text-wrap: balance; }
        .qa-rest { display: grid; gap: 1rem; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 720px) { .qa-rest { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1040px) {
            .qa-rest { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .qa-rest .qa-wide { grid-column: span 2; }
        }
        .qa-rest .qa-card { border-color: #15161a; border-radius: 1.6rem 1.6rem 1.6rem 0.3rem; transition: translate 0.25s cubic-bezier(0.34, 1.4, 0.64, 1), box-shadow 0.25s ease; }
        .qa-rest .qa-card:nth-child(even) { border-radius: 1.6rem 1.6rem 0.3rem 1.6rem; }
        .qa-rest .qa-card:hover { translate: 0 -4px; box-shadow: 0 0.9rem 0 -0.5rem #15161a; }
        .qa-rest .qa-card h3 { font-size: 1.25rem; }

        /* ---------------------------------------------------------------
           Who is in the thread
           --------------------------------------------------------------- */
        .qa-people { display: grid; gap: 1rem; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 700px) { .qa-people { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1040px) { .qa-people { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .qa-person { display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 0.3rem 0.95rem; align-content: start; padding: 1.25rem 1.3rem; background: var(--qa-card); border: 1.5px solid var(--qa-line); border-radius: 1.4rem; transition: border-color 0.2s ease, translate 0.25s cubic-bezier(0.34, 1.4, 0.64, 1); }
        .qa-person:hover { border-color: var(--qa-ink); translate: 0 -3px; }
        .qa-person-av { grid-row: span 2; display: grid; place-items: center; width: 3.1rem; aspect-ratio: 1; border-radius: 50%; box-shadow: 0 0 0 1.5px var(--qa-line); background: var(--bg, #ffd84d); color: var(--fg, #15161a); font-family: var(--qa-d); font-weight: 700; font-size: 0.95rem; }
        .qa-person h3 { align-self: end; font-family: var(--qa-d); font-weight: 700; font-size: 1.1rem; line-height: 1.25; }
        .qa-person small { font-size: 0.78rem; color: var(--qa-ink-3); }
        .qa-person p { grid-column: 1 / -1; margin-top: 0.5rem; font-size: 0.98rem; color: var(--qa-ink-2); }
        .qa-person a { grid-column: 1 / -1; justify-self: start; margin-top: 0.4rem; }

        /* ---------------------------------------------------------------
           Three steps: one run of three bubbles
           --------------------------------------------------------------- */
        .qa-run { display: grid; justify-items: end; gap: 0.3rem; margin-inline: auto 1rem; max-width: min(100% - 1rem, 40rem); }
        .qa-run .qa-bub-a { width: 100%; display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 0.2rem 1rem; padding: 1.1rem 1.35rem; border-radius: 1.6rem 0.45rem 0.45rem 1.6rem; }
        .qa-run .qa-bub-a:first-of-type { border-start-end-radius: 1.6rem; }
        .qa-run .qa-bub-a:last-of-type { border-end-end-radius: 0.25rem; }
        .qa-run .qa-bub-a:not(:last-of-type) > .qa-tail { display: none; }
        .qa-run .qa-run-head { display: block; }
        .qa-run-n { grid-row: span 2; min-width: 2.75rem; font-family: var(--qa-d); font-weight: 700; font-size: 2rem; line-height: 1; color: #ffd84d; }
        .qa-run h3 { font-family: var(--qa-d); font-weight: 700; font-size: 1.2rem; line-height: 1.25; }
        .qa-run .qa-bub-a p { font-size: 1rem; color: var(--qa-on-host-2); }

        /* ---------------------------------------------------------------
           Key features and related pages: things you can tap
           --------------------------------------------------------------- */
        .qa-links { display: grid; gap: 0.6rem; }
        .qa-link {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.2rem 1rem 1.4rem;
            border: 1.5px solid var(--qa-line);
            border-radius: 999px;
            background: var(--qa-card);
            transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease, padding 0.25s ease;
        }
        .qa-link strong { display: block; font-family: var(--qa-d); font-weight: 700; line-height: 1.25; }
        .qa-link small { display: block; margin-top: 0.1rem; font-size: 0.92rem; color: var(--qa-ink-2); }
        .qa-link .qa-icon { width: 2.2rem; height: 2.2rem; padding: 0.5rem; border-radius: 50%; background: var(--qa-soft); transition: background-color 0.2s ease, rotate 0.25s ease; }
        .qa-link:hover { background: #ffd84d; border-color: #15161a; color: #15161a; }
        .qa-link:hover small { color: #3a3524; }
        .qa-link:hover .qa-icon { background: #15161a; color: #ffd84d; rotate: -45deg; }
        @media (max-width: 560px) { .qa-link { border-radius: 1.4rem; } }
        .qa-more { display: inline-flex; align-items: center; gap: 0.4rem; margin-top: 0.4rem; font-family: var(--qa-d); font-weight: 700; color: var(--qa-ink); border-bottom: 2px solid var(--qa-hot); padding-bottom: 0.1rem; transition: gap 0.2s ease; }
        .qa-more:hover { gap: 0.7rem; }
        .qa-rel { display: flex; flex-wrap: wrap; gap: 0.6rem; }
        .qa-rel a { display: inline-flex; align-items: center; gap: 0.6rem; padding: 0.75rem 0.8rem 0.75rem 1.3rem; border: 1.5px solid var(--qa-ink); border-radius: 999px; font-family: var(--qa-d); font-weight: 700; color: var(--qa-ink); transition: background-color 0.2s ease, color 0.2s ease, translate 0.2s ease; }
        .qa-rel a small { flex: none; display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.25rem 0.65rem; border-radius: 999px; background: var(--qa-soft); font-family: var(--qa-t); font-size: 0.78rem; font-weight: 700; white-space: nowrap; color: var(--qa-ink-2); }
        .qa-rel a small .qa-icon { width: 0.85rem; height: 0.85rem; }
        .qa-rel a:hover { background: #ffd84d; color: #15161a; border-color: #15161a; translate: 0 -2px; }
        .qa-rel a:hover small { background: #15161a; color: #ffd84d; }

        /* ---------------------------------------------------------------
           The shared plan band and closing strip: their words, this ink
           --------------------------------------------------------------- */
        #qa .qa-plans > section { background: var(--qa-paper); padding-block: clamp(3rem, 6vw, 5rem); }
        #qa .qa-plans h2 { font-family: var(--qa-d); font-weight: 700; letter-spacing: -0.03em; font-size: clamp(1.9rem, 4vw, 2.75rem); line-height: 1.08; color: var(--qa-ink); }
        #qa .qa-plans h2 + p { color: var(--qa-ink-2); font-size: 1.0625rem; }
        #qa .qa-plans .grid > div { background: var(--qa-card); border: 1.5px solid var(--qa-line); border-radius: 1.6rem 1.6rem 1.6rem 0.3rem; color: var(--qa-ink); }
        #qa .qa-plans .grid > div:nth-child(2) { border-color: var(--qa-ink); box-shadow: 0 0.8rem 0 -0.45rem #ffd84d; }
        #qa .qa-plans .grid > div span,
        #qa .qa-plans .grid > div p,
        #qa .qa-plans .grid > div li { color: var(--qa-ink-2); }
        #qa .qa-plans .grid > div .text-3xl { font-family: var(--qa-d); font-weight: 700; letter-spacing: -0.03em; color: var(--qa-ink); }
        #qa .qa-plans .grid > div .uppercase { font-family: var(--qa-m); color: var(--qa-ink); }
        #qa .qa-plans .grid > div .rounded-full { background: #ffd84d; color: #15161a; }
        #qa .qa-plans .grid > div svg { color: var(--qa-mint-ink); }
        #qa .qa-plans a.font-medium { color: var(--qa-ink); font-family: var(--qa-d); font-weight: 700; border-bottom: 2px solid var(--qa-hot); }
        #qa .qa-plans a.rounded-2xl { background: var(--qa-ink); background-image: none; color: var(--qa-paper); border-radius: 999px; box-shadow: none; font-family: var(--qa-d); font-weight: 700; }
        #qa .qa-plans a.rounded-2xl:hover { box-shadow: 0 0.6rem 1.2rem -0.5rem rgba(21, 22, 26, 0.55); }

        #qa .qa-keep > section { background: var(--qa-paper-2); border-top: 1px solid var(--qa-line); }
        #qa .qa-keep h2 { font-family: var(--qa-d); font-weight: 700; letter-spacing: -0.03em; color: var(--qa-ink); }
        #qa .qa-keep p.uppercase { font-family: var(--qa-m); letter-spacing: 0.14em; color: var(--qa-hot-ink); }
        #qa .qa-keep .grid > a { background: var(--qa-card); border: 1.5px solid var(--qa-line); border-radius: 1.4rem 1.4rem 1.4rem 0.3rem; }
        #qa .qa-keep .grid > a:hover { border-color: var(--qa-ink); box-shadow: 0 0.7rem 0 -0.4rem #ffd84d; }
        #qa .qa-keep .grid > a > span:first-child { display: none; }
        #qa .qa-keep .grid > a h3 { font-family: var(--qa-d); color: var(--qa-ink); }
        #qa .qa-keep .grid > a p { color: var(--qa-ink-2); }
        #qa .qa-keep .grid > a > span:last-child,
        #qa .qa-keep a.self-start { color: var(--qa-hot-ink); }

        /* ---------------------------------------------------------------
           Frequently asked: each one a question and its reply
           --------------------------------------------------------------- */
        .qa-faq { display: grid; gap: 0.9rem; }
        .qa-faq details { display: grid; gap: 0.6rem; }
        .qa-faq summary { display: flex; align-items: flex-end; gap: 1.05rem; max-width: min(100%, 44rem); cursor: pointer; border-radius: 1.6rem; }
        .qa-faq summary .qa-bub-q { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; gap: 0.9rem; transition: translate 0.2s ease; }
        .qa-faq summary:hover .qa-bub-q { translate: 3px 0; }
        .qa-faq h3 { font-family: var(--qa-d); font-weight: 700; font-size: 1.12rem; line-height: 1.3; }
        .qa-plus { position: relative; width: 1.5rem; height: 1.5rem; border-radius: 50%; background: #15161a; }
        .qa-plus::before,
        .qa-plus::after { content: ""; position: absolute; inset: calc(50% - 1px) 0.38rem auto 0.38rem; height: 2px; background: #ffd84d; transition: rotate 0.3s cubic-bezier(0.22, 1, 0.36, 1); }
        .qa-plus::after { rotate: 90deg; }
        .qa-faq details[open] .qa-plus::after { rotate: 0deg; }
        .qa-faq .qa-reply { max-width: min(100% - 1rem, 46rem); }
        .qa-faq .qa-bub-a p { font-size: 1.02rem; }

        /* ---------------------------------------------------------------
           The last message: a composer with your name in it
           --------------------------------------------------------------- */
        .qa-end { position: relative; overflow: clip; padding-block: clamp(4rem, 9vw, 7rem); scroll-margin-top: 6rem; }
        .qa-end-q { position: absolute; pointer-events: none; inset-inline-end: clamp(-4rem, 2vw, 4rem); top: 50%; translate: 0 -46%; font-family: var(--qa-d); font-weight: 700; font-size: clamp(16rem, 44vw, 40rem); line-height: 0.8; color: rgba(255, 216, 77, 0.13); rotate: 12deg; }
        @media (max-width: 899px) { .qa-end-q { top: auto; bottom: -3rem; translate: 0 0; color: rgba(255, 216, 77, 0.08); } }
        .qa-end-in { position: relative; }
        .qa-end .qa-big { max-width: 46rem; }
        .qa-end-lede { max-width: 40rem; color: var(--qa-ink-2); font-size: 1.12rem; }
        .qa-composer {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 0.5rem;
            width: min(100%, 44rem);
            padding: 0.5rem;
            border-radius: 1.9rem;
            background: #fbfaf6;
            box-shadow: 0 1.6rem 3rem -1.2rem #000;
        }
        @media (min-width: 680px) { .qa-composer { grid-template-columns: minmax(0, 1fr) auto; border-radius: 999px; } }
        #qa .qa-claim {
            display: flex;
            align-items: center;
            min-width: 0;
            padding: 1rem 0.4rem 1rem 1.3rem;
            border: 0;
            border-radius: 999px;
            background: transparent;
            font-family: var(--qa-m);
            font-weight: 700;
            font-size: clamp(1rem, 3.3vw, 1.1rem);
            color: #15161a;
            transition: box-shadow 0.2s ease;
        }
        /* Never under 16px: iOS zooms the page when a smaller field is focused. The
           narrowest phones give up some of the inset so the placeholder still fits. */
        @media (max-width: 374px) { #qa .qa-claim { padding-inline-start: 0.85rem; } }
        #qa .qa-claim:focus-within { border-color: transparent; box-shadow: inset 0 0 0 2px #15161a; }
        #qa .qa-claim input { flex: 1; min-width: 0; border: 0; background: transparent; padding-inline: 0; text-align: right; font: inherit; color: #15161a; box-shadow: none; outline: none; }
        #qa .qa-claim input::placeholder { color: #6d6f78; }
        .qa-claim span { flex: none; color: #5f616b; user-select: none; }
        .qa-send {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            padding: 0.95rem 1rem 0.95rem 1.5rem;
            border-radius: 999px;
            background: #ffd84d;
            color: #15161a;
            font-family: var(--qa-d);
            font-weight: 700;
            font-size: 1rem;
            white-space: nowrap;
            transition: background-color 0.2s ease, translate 0.2s ease;
        }
        .qa-send i { display: grid; place-items: center; width: 2rem; aspect-ratio: 1; border-radius: 50%; background: #15161a; color: #ffd84d; transition: translate 0.25s cubic-bezier(0.34, 1.56, 0.64, 1); }
        .qa-send:hover { background: #ffe37a; }
        .qa-send:hover i { translate: 0 -3px; }
        #qa .qa-end a:focus-visible { outline-color: #ffd84d; }
        .qa-end-note { font-size: 0.92rem; color: var(--qa-ink-3); }
        .qa-end-col { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.1rem; width: min(100%, 44rem); margin-top: 0.75rem; }
        .qa-draft { margin-inline: auto 0.9rem; max-width: calc(100% - 0.9rem); }
        .qa-draft .qa-bub-a { font-family: var(--qa-m); font-weight: 700; font-size: clamp(0.9rem, 3.2vw, 1.1rem); overflow-wrap: anywhere; }
        .qa-draft b { font-weight: 700; color: #ffd84d; }

        @media (prefers-reduced-motion: reduce) {
            .qa-eyebrow-dots i, .qa-typing i, .qa-hero-typing i { animation: none; }
            .qa-btn, .qa-person, .qa-link, .qa-rel a, .qa-send, .qa-send i, .qa-pip i, .qa-opt-bar, .qa-rest .qa-card, .qa-qrow, .qa-vote i { transition: none; animation: none; }
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
            ['Can I pay for the deep dive?', 'Named ticket types, paid through your Stripe or PayPal', "The session's ticket section, Tickets mode", 'Pro'],
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
                'a' => 'Yes, on the Pro plan at '.plan_price($proMonthly).' a month. Connect your own Stripe or PayPal account and sell named ticket types for a premium AMA or a paid deep dive, each with its own price, quantity and sales window. Pro is what puts a price on a ticket, and it brings the rest of the door tooling with it: the live check-in dashboard, discount codes, add-ons and a waitlist on a sold-out ticket type. Scanning a ticket\'s QR code at the door is free on every plan. Event Schedule charges zero platform fees at every plan level, free included, so past your processor\'s own fee the money is yours. Free sessions do not need any of this: registration with a place limit is free and unlimited.',
            ],
            [
                'q' => 'Is Event Schedule free for hosting Q&A sessions?',
                'a' => 'Yes. Unlimited sessions, registration with a capacity limit, the agenda, recurring office hours, the embeddable calendar, the embeddable registration widget, two-way Google, Outlook and CalDAV sync, built-in analytics and newsletters are all free forever, and there is no ceiling on how many people register. Polls, custom questions on the registration form and charging for a seat are on the Pro plan at '.plan_price($proMonthly).' a month. There are zero platform fees on ticket sales on every plan.',
            ],
            [
                'q' => 'Do my followers get an email when I schedule a new session?',
                'a' => 'Anyone who left you an email address and confirmed it, yes: a newly scheduled session reaches them as a digest, batched and never more often than once every few days, and it does not draw on your newsletter allowance. Somebody who followed you from their own account is a separate list, reached only by a newsletter you write, with 10 emails a month on the free plan, 100 on Pro and 1,000 on Enterprise, counted per recipient rather than per send.',
            ],
            [
                'q' => 'Can I cap how many people join?',
                'a' => 'Yes, and the count is per session date. Set a number of places on the session and every occurrence of a weekly office hour counts its own registrations, so this Thursday filling up does not close next Thursday. The page shows how many places are left, and once a date is full it stops taking registrations for that date. A waitlist for a full registration date is free as well; it is the waitlist on a sold-out paid ticket type that is a Pro feature.',
            ],
            [
                'q' => 'Can people get a reminder without registering?',
                'a' => 'Yes. Switch on the free "Notify me" card and, beside the register button, "Tell me if anything changes" takes an email address and nothing else, no account and no name. That person gets a reminder shortly before the session, a notice if it is cancelled, and any change notice you choose to send, say when the join link moves. Every one of those emails unsubscribes in one click. Each date of a weekly office hour is its own list, it is not a subscription to your schedule, and it is free on every plan. For every date at once, your schedule\'s live calendar feed updates itself and costs no email address at all.',
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

        // The six kinds of host, with the two inks of each one's initials.
        $qaPeople = [
            ['TF', 'Tech Founders', 'Product AMAs, roadmap Q&As, and investor office hours. Let the room vote on what the hour covers before it starts.', 'for-tech-founder-qa', '#ffd84d', '#15161a'],
            ['CC', 'Coaches & Consultants', 'Client office hours, group coaching Q&As, and expert sessions. Cap the places so the hour stays useful for everyone in it.', 'for-coach-consultant-qa', '#1fb98a', '#15161a'],
            ['AT', 'Authors & Thought Leaders', 'Book Q&As, fireside chats, and audience discussions. Connect with readers and followers directly.', 'for-author-thought-leader-qa', '#ff5a1f', '#15161a'],
            ['CM', 'Community Managers', 'Town halls, member Q&As, and community feedback sessions. Collect what people want raised, then approve what goes on the page.', 'for-community-manager-qa', '#15161a', '#ffd84d'],
            ['EP', 'Educators & Professors', 'Student office hours, exam review sessions, and open Q&As. One recurring session with a place limit on every date.', 'for-educator-professor-qa', '#ffd84d', '#15161a'],
            ['HR', 'HR & Internal Teams', 'All-hands Q&As, leadership town halls, and policy discussions. Keep the agenda public and the draft ones private until they are ready.', 'for-hr-internal-team-qa', '#1fb98a', '#15161a'],
        ];

        $qaArrow = '<svg aria-hidden="true" class="qa-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>';
        $qaDown = '<svg aria-hidden="true" class="qa-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>';
        $qaUp = '<svg aria-hidden="true" class="qa-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg>';
        $qaLink = '<svg aria-hidden="true" class="qa-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>';
        $qaDots = '<i style="--i: 0;"></i><i style="--i: 1;"></i><i style="--i: 2;"></i>';
    @endphp

    <div id="qa">

        <!-- The thread header: a chat's own bar, and the page's section nav -->
        <div class="qa-bar">
            <div class="qa-wrap qa-bar-in">
                <span class="qa-bar-av" aria-hidden="true">?</span>
                <span class="qa-bar-title" aria-hidden="true"><b>Ask me anything</b><small>the whole thread</small></span>
                <nav class="es-dotnav qa-bar-nav" aria-label="Page sections">
                    <ol>
                        @foreach ($dotSections as [$sectionId, $sectionLabel])
                            <li><a href="#{{ $sectionId }}" class="es-dot qa-pip" aria-label="{{ $sectionLabel }}"><i></i><span aria-hidden="true">{{ $sectionLabel }}</span></a></li>
                        @endforeach
                    </ol>
                </nav>
            </div>
            <i class="qa-bar-prog" aria-hidden="true"></i>
        </div>

        <!-- ============================================================ -->
        <!-- 1. Hero: the question, very large                            -->
        <!-- ============================================================ -->
        <section id="top" class="qa-hero">
            <div class="qa-wrap">
                <div class="qa-hero-grid">
                    <div class="qa-hero-copy">
                        <h1 class="qa-h1">
                            <x-marketing.hero-eyebrow class="qa-eyebrow es-fade-up es-d-1">
                                <span class="qa-eyebrow-dots" aria-hidden="true">{!! $qaDots !!}</span>
                                For live Q&amp;A sessions and AMAs
                            </x-marketing.hero-eyebrow>
                            <span class="es-mask"><span class="es-mask-line">A live Q&amp;A is a</span></span>
                            <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="qa-mark">conversation</span>, so plan it like one.</span></span>
                        </h1>

                        <p class="qa-lede es-fade-up es-d-2">
                            Registration with a real limit on places, an agenda your audience can read before they arrive, a poll they can add their own option to, and one link to wherever you are hosting. Zero platform fees, on every plan.
                        </p>

                        <div class="qa-cta es-fade-up es-d-3">
                            <a href="{{ app_url('/sign_up?type=talent') }}" class="qa-btn">
                                Create your Q&amp;A schedule
                                {!! $qaArrow !!}
                            </a>
                            <a href="#arrive" class="qa-btn qa-btn-ghost">
                                Read the turns
                                {!! $qaDown !!}
                            </a>
                        </div>
                    </div>

                    <div class="qa-hero-art" aria-hidden="true">
                        <div class="qa-q"><div class="qa-q-bub"><b>?</b></div></div>
                        <div class="qa-bang"><b>!</b></div>
                        <div class="qa-hero-typing">{!! $qaDots !!}</div>
                    </div>
                </div>

                <!-- Session types, as a chat's quick replies -->
                <div class="qa-chips es-fade-up es-d-4">
                    <div class="es-marquee-mask">
                        <div class="es-marquee" data-marquee="1">
                            <div class="es-marquee-track">
                                @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                    @foreach (['AMAs', 'Office Hours', 'Town Halls', 'Expert Panels', 'Fireside Chats', 'Community Q&As', 'Roundtables', 'Ask Me Anything'] as $chip)
                                        <span @if ($chipCopy === 1) aria-hidden="true" @endif class="qa-chip">{{ $chip }}</span>
                                    @endforeach
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. Pinned: the only three numbers on this page               -->
        <!-- ============================================================ -->
        <section class="qa-pinned">
            <div class="qa-wrap qa-pinned-grid">
                <!-- One session's own card: the agenda, and the places. -->
                <div class="qa-sheet" data-reveal="panel">
                    <div class="qa-sheet-head">
                        <span class="qa-sheet-av" aria-hidden="true">OH</span>
                        <div>
                            <span class="qa-sheet-title">Office Hours #14</span>
                            <span class="qa-sheet-meta">THU 18:00 &middot; 55 MIN &middot; ONLINE</span>
                        </div>
                    </div>
                    <div>
                        @foreach ($agenda as [$slotTime, $slotName, $slotNote])
                            <div class="qa-slot">
                                <span class="qa-slot-time">{{ $slotTime }}</span>
                                <span>
                                    <span class="qa-slot-name">{{ $slotName }}</span>
                                    @if ($slotNote)
                                        <span class="qa-slot-note">{{ $slotNote }}</span>
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                    <div class="qa-seats">
                        <div class="qa-dots qa-dots-live" aria-hidden="true">
                            @for ($seat = 0; $seat < 40; $seat++)
                                <i @class(['is-free' => $seat >= 34]) style="--i: {{ $seat }};"></i>
                            @endfor
                        </div>
                        <div class="qa-stamps">
                            <span class="qa-stamp">40 places &middot; 6 left</span>
                            <span class="qa-stamp">Free registration</span>
                        </div>
                    </div>
                    <p class="qa-sheet-note">
                        Agenda segments are free on every plan, and each one can take its own comments. The places counter is per date, so next Thursday starts again at forty.
                    </p>
                </div>

                <div>
                <p class="qa-tag qa-pin-head" data-reveal>The only three numbers on this page</p>
                <div class="qa-pins" data-reveal-group="90">
                    <div class="qa-pin" data-reveal>
                        <div class="qa-pin-n">{{ plan_price(0) }}</div>
                        <strong>platform fees on ticket sales</strong>
                        <small>Every plan, including free. Stripe or PayPal still take their own processing fee.</small>
                    </div>
                    <div class="qa-pin" data-reveal>
                        <div class="qa-pin-n"><span data-count-to="5">5</span></div>
                        <strong>polls per session, 2 to 10 options each</strong>
                        <small>Polls are a Pro feature. Your audience can suggest options if you let them.</small>
                    </div>
                    <div class="qa-pin" data-reveal>
                        <div class="qa-pin-n qa-pin-n-long">10 <i>/</i> 100 <i>/</i> 1,000</div>
                        <strong>newsletter emails a month</strong>
                        <small>Free, Pro, Enterprise. Counted per recipient, not per send.</small>
                    </div>
                </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. Q1: getting in                                            -->
        <!-- ============================================================ -->
        <section id="arrive" class="qa-sec">
            <div class="qa-wrap qa-thread">
                <span class="qa-time" aria-hidden="true">Thu &middot; 18:00</span>

                <div class="qa-in" data-reveal="left">
                    <span class="qa-av" aria-hidden="true">Q1</span>
                    <div class="qa-in-col">
                        <span class="qa-who" aria-hidden="true">From the audience</span>
                        <div class="qa-bub qa-bub-q">
                            <h2 class="qa-h2">How does anybody actually <span class="qa-em">get in?</span></h2>
                            <i class="qa-tail" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>

                <div class="qa-reply">
                    <span class="qa-typing" data-reveal aria-hidden="true">{!! $qaDots !!}</span>
                    <div class="qa-bub qa-bub-a" data-reveal="right" style="--reveal-delay: 0.7s;">
                        <p>Registration. It is free, it is native, and it counts places per session date. A name and an email is all your audience has to type, and no account is required of them.</p>
                        <i class="qa-tail" aria-hidden="true"></i>
                    </div>
                    <span class="qa-seen" aria-hidden="true">Answered</span>
                </div>

                <div class="qa-cards" data-reveal-group="100">
                    <div class="qa-card" data-reveal="panel">
                        <div class="qa-card-head">
                            <h3>A list, not a form you built</h3>
                            <span class="qa-tier">Free</span>
                        </div>
                        <p>Set the session to Registration instead of Tickets and it collects sign-ups itself. Every registration lands in the same place your ticket sales would.</p>
                    </div>
                    <div class="qa-card" data-reveal="panel">
                        <div class="qa-card-head">
                            <h3>A limit that counts per date</h3>
                            <span class="qa-tier">Free</span>
                        </div>
                        <p>Give the session a number of places. A weekly office hour counts each Thursday separately, shows how many are left, and closes registration when that date is full.</p>
                        <div class="qa-weeks" aria-hidden="true">
                            @foreach ([['This Thursday', '6 left', 34], ['Next Thursday', '40 left', 0]] as [$weekName, $weekLeft, $weekTaken])
                                <div class="qa-week">
                                    <span><b>{{ $weekName }}</b><b>{{ $weekLeft }}</b></span>
                                    <div class="qa-dots">
                                        @for ($seat = 0; $seat < 40; $seat++)
                                            <i @class(['is-free' => $seat >= $weekTaken])></i>
                                        @endfor
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="qa-card" data-reveal="panel">
                        <div class="qa-card-head">
                            <h3>The confirmation email is yours to write</h3>
                            <span class="qa-tier">Free</span>
                        </div>
                        <p>Registration notes ride along with every confirmation. Put the joining instructions, the house rules, or an invitation to reply with a question in there once.</p>
                    </div>
                </div>

                <p class="qa-aside" data-reveal>
                    The honest limit: custom questions on that form, the ones asking what somebody does or what they want covered, are a Pro feature. The free list collects a name and an email, and one registration per person per date.
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. Q2: asking early (the room with the lights down)          -->
        <!-- ============================================================ -->
        <section id="ask" class="qa-sec qa-night">
            <div class="qa-wrap qa-thread">
                <span class="qa-time" aria-hidden="true">18:10</span>

                <div class="qa-in" data-reveal="left">
                    <span class="qa-av" aria-hidden="true">Q2</span>
                    <div class="qa-in-col">
                        <span class="qa-who" aria-hidden="true">From the audience</span>
                        <div class="qa-bub qa-bub-q">
                            <h2 class="qa-h2">Can I ask my question <span class="qa-em">before we start?</span></h2>
                            <i class="qa-tail" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>

                <div class="qa-reply">
                    <span class="qa-typing" data-reveal aria-hidden="true">{!! $qaDots !!}</span>
                    <div class="qa-bub qa-bub-a" data-reveal="right" style="--reveal-delay: 0.7s;">
                        <p>Three real surfaces, and it is worth being precise about what each one is. A poll the room can add options to. Comments held for approval, which can hang off a single agenda segment. And the email you already send.</p>
                        <i class="qa-tail" aria-hidden="true"></i>
                    </div>
                    <span class="qa-seen" aria-hidden="true">Answered</span>
                </div>

                <!-- The poll, both sides of it -->
                <div class="qa-two" data-reveal-group="110">
                    <div class="qa-card" data-reveal="panel">
                        <div class="qa-card-head">
                            <p class="qa-tag">What the room sees</p>
                            <span class="qa-tier qa-tier-pro">Pro</span>
                        </div>
                        <fieldset class="qa-poll">
                            <legend>Which should we spend the hour on?</legend>
                            @foreach ([['The pricing change', 24], ['The API rewrite', 18], ['Roadmap for the quarter', 11]] as [$optName, $optCount])
                                <label class="qa-opt" style="--c: {{ $optCount }};">
                                    <input type="radio" name="qa-poll" class="qa-opt-in">
                                    <span class="qa-opt-name">{{ $optName }}</span>
                                    <span class="qa-opt-n" aria-hidden="true"></span>
                                    <span class="qa-opt-bar" aria-hidden="true"></span>
                                </label>
                            @endforeach
                        </fieldset>
                        <p class="qa-poll-hint" aria-hidden="true">A picture of the poll. Press an option to vote.</p>
                        <div class="qa-suggest" aria-hidden="true">
                            <span>Suggest another option</span>
                            <b>+</b>
                        </div>
                        <p>
                            Results appear once you have voted: one vote each, from a signed-in account, counted per date. Close the poll and the results are on the page for everyone. More on <a href="{{ marketing_url('/features/polls') }}" class="qa-a">event polls</a>.
                        </p>
                    </div>

                    <div class="qa-card" data-reveal="panel">
                        <div class="qa-card-head">
                            <p class="qa-tag">What you see</p>
                            <span class="qa-tier qa-tier-pro">Pro</span>
                        </div>
                        <div class="qa-crumbs" aria-hidden="true">
                            <span>Engagement</span>
                            <span>/</span>
                            <span>Polls</span>
                            <b>1</b>
                        </div>
                        <div class="qa-pending" aria-hidden="true">
                            <span>&ldquo;Migrating off the old plan&rdquo;</span>
                            <span class="qa-pending-acts">
                                <span class="qa-mini">Approve</span>
                                <span class="qa-mini">Reject</span>
                            </span>
                        </div>
                        <p>
                            Turn on suggestions and you can also require your approval, so a suggested option waits here until you accept it. Ten options is the ceiling, pending ones included.
                        </p>
                        <p>
                            The Polls tab always carries a count of what is waiting. The email about it is a toggle you switch on, and on the hosted plan that one sends through your own email settings. Either way, the notifications run toward the host.
                        </p>
                    </div>
                </div>

                <div class="qa-cards" data-reveal-group="100">
                    <div class="qa-card" data-reveal="panel">
                        <div class="qa-card-head">
                            <h3>Comments, on the segment they are about</h3>
                            <span class="qa-tier">Free</span>
                        </div>
                        <p>Your audience can leave a comment on the session, or on one agenda segment, with just a name and an email. Nothing shows until you approve it, and the pending count sits on the Fan Content tab; the email about it is a toggle you switch on. A per-schedule toggle can require an account instead. More on <a href="{{ marketing_url('/features/fan-videos') }}" class="qa-a">audience content</a>.</p>
                    </div>
                    <div class="qa-card" data-reveal="panel">
                        <div class="qa-card-head">
                            <h3>Or just ask by email</h3>
                            <span class="qa-tier">Free</span>
                        </div>
                        <p>The registration note goes to everyone who signed up. A newsletter goes to everyone who follows you, and once you are on Pro a poll can be dropped into it with a button that votes on the session page.</p>
                    </div>
                </div>

                <p class="qa-aside" data-reveal>
                    What Event Schedule does not have, so you are not surprised on day one: an upvoting question queue with a moderation console. A poll is a poll. Your question, up to ten options, one vote each, and a small approve or reject on any option the room suggests.
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 5. Q3: the link                                              -->
        <!-- ============================================================ -->
        <section id="link" class="qa-sec">
            <div class="qa-wrap qa-thread">
                <span class="qa-time" aria-hidden="true">18:20</span>

                <div class="qa-in" data-reveal="left">
                    <span class="qa-av" aria-hidden="true">Q3</span>
                    <div class="qa-in-col">
                        <span class="qa-who" aria-hidden="true">From the audience</span>
                        <div class="qa-bub qa-bub-q">
                            <h2 class="qa-h2">So where do we <span class="qa-em">actually meet?</span></h2>
                            <i class="qa-tail" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>

                <div class="qa-reply">
                    <span class="qa-typing" data-reveal aria-hidden="true">{!! $qaDots !!}</span>
                    <div class="qa-bub qa-bub-a" data-reveal="right" style="--reveal-delay: 0.7s;">
                        <p>Wherever you already host. Mark the session online, paste the link, and it appears on the session page and on each attendee's own registration page.</p>
                        <i class="qa-tail" aria-hidden="true"></i>
                    </div>
                    <div class="qa-unfurl" data-reveal="right" style="--reveal-delay: 0.85s;" aria-hidden="true">
                        <i>{!! $qaLink !!}</i>
                        <div>
                            <b>Join the session</b>
                            <span>your-platform.example/office-hours</span>
                        </div>
                    </div>
                    <span class="qa-seen" aria-hidden="true">Answered</span>
                </div>

                <div class="qa-two">
                    <div class="qa-cards qa-cards-2" data-reveal-group="100">
                        <div class="qa-card" data-reveal="panel">
                            <div class="qa-card-head">
                                <h3>One field, any platform</h3>
                                <span class="qa-tier">Free</span>
                            </div>
                            <p>Zoom, Google Meet, Microsoft Teams, YouTube Live, or the thing you switch to next year. Learn more about <a href="{{ marketing_url('/features/online-events') }}" class="qa-a">online event features</a>.</p>
                        </div>
                        <div class="qa-card" data-reveal="panel">
                            <div class="qa-card-head">
                                <h3>In the room and online</h3>
                                <span class="qa-tier">Free</span>
                            </div>
                            <p>In person and online are separate ticks, so a town hall can have a venue on the map and a join link for everyone who cannot be there.</p>
                        </div>
                        <div class="qa-card qa-span" data-reveal="panel">
                            <div class="qa-card-head">
                                <h3>The link lands where they will look for it</h3>
                                <span class="qa-tier">Free</span>
                            </div>
                            <p>Registering gives somebody their own page for that date, and the join link is on it. The confirmation email links straight there, so nobody has to search their inbox for a link you sent in March. With the "Notify me" card switched on, somebody not ready to register can press "Tell me if anything changes" instead: an email address, a reminder before the hour, and the notice you send if the link moves.</p>
                        </div>
                    </div>

                    <div class="qa-card" data-reveal="panel">
                        <p class="qa-tag">Being straight with you</p>
                        <p>
                            This is one link field, not a streaming integration. There is no embedded player, no viewer count, and nothing reads back from the platform you host on. That is also why it has never broken when a platform changed its API: it is a URL, and it is yours.
                        </p>
                        <div class="qa-platforms" aria-hidden="true">
                            @foreach (['Zoom', 'Google Meet', 'Teams', 'YouTube Live', 'Anything with a URL'] as $platform)
                                <span>{{ $platform }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- The other side of the link: a picture of the session itself, which is not ours. -->
                <div class="qa-room" data-reveal role="group" aria-label="A picture of a question queue on the platform you host on. It is not an Event Schedule feature.">
                    <div>
                        <p class="qa-room-flag" aria-hidden="true"><span>The other side of the link</span><span>Not Event Schedule</span></p>
                        <p class="qa-room-cap">Your session, wherever you host it.</p>
                        <p class="qa-room-sub">A picture, not a feature. Press an arrow and the queue sorts itself.</p>
                    </div>
                    <ul class="qa-queue">
                        <li class="qa-qrow is-live" style="--v: 14;">
                            <span class="qa-vote" aria-hidden="true"><i></i><b></b></span>
                            <span class="qa-qtext">What is changing in the pricing?</span>
                            <span class="qa-state">Answering now</span>
                        </li>
                        @foreach ([[9, 'When does the API rewrite ship?'], [8, 'Is the old plan going away?'], [7, 'What is on the roadmap this quarter?']] as [$upVotes, $upText])
                            <li class="qa-qrow" style="--v: {{ $upVotes }};">
                                <label class="qa-vote">
                                    <input type="checkbox" class="qa-up">
                                    <span class="sr-only">Upvote: {{ $upText }}</span>
                                    <i aria-hidden="true"></i><b aria-hidden="true"></b>
                                </label>
                                <span class="qa-qtext">{{ $upText }}</span>
                            </li>
                        @endforeach
                        <li class="qa-qrow is-done" style="--v: 11;">
                            <span class="qa-vote" aria-hidden="true"><i></i><b></b></span>
                            <span class="qa-qtext">Where are last week's notes?</span>
                            <span class="qa-state">Answered</span>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 6. Q4: every Thursday                                        -->
        <!-- ============================================================ -->
        <section id="series" class="qa-sec qa-alt">
            <div class="qa-wrap qa-thread">
                <span class="qa-time" aria-hidden="true">18:30</span>

                <div class="qa-in" data-reveal="left">
                    <span class="qa-av" aria-hidden="true">Q4</span>
                    <div class="qa-in-col">
                        <span class="qa-who" aria-hidden="true">From the audience</span>
                        <div class="qa-bub qa-bub-q">
                            <h2 class="qa-h2">Is this a one-off, or <span class="qa-em">every Thursday?</span></h2>
                            <i class="qa-tail" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>

                <div class="qa-reply">
                    <span class="qa-typing" data-reveal aria-hidden="true">{!! $qaDots !!}</span>
                    <div class="qa-bub qa-bub-a" data-reveal="right" style="--reveal-delay: 0.7s;">
                        <p>Office hours are one recurring session, not fifty copies. Pick the days it runs, take out the dates you are away, and give the run an end so it is not still open in eighteen months.</p>
                        <i class="qa-tail" aria-hidden="true"></i>
                    </div>
                    <span class="qa-seen" aria-hidden="true">Answered</span>
                </div>

                <div class="qa-two">
                    <div data-reveal="panel">
                        <div class="qa-sheet">
                            <div class="qa-sheet-head">
                                <span class="qa-sheet-av" aria-hidden="true">OH</span>
                                <div>
                                    <span class="qa-sheet-title">Office Hours</span>
                                    <span class="qa-sheet-meta">RECURRING</span>
                                </div>
                            </div>
                            <div>
                                @foreach ($series as $seriesIndex => [$seriesLine, $seriesLabel])
                                    <div class="qa-slot">
                                        <span class="qa-slot-time">{{ str_pad($seriesIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                        <span>
                                            <span class="qa-slot-name">{{ $seriesLine }}</span>
                                            <span class="qa-slot-note">{{ $seriesLabel }}</span>
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="qa-thursdays" aria-hidden="true">
                                @foreach ([['Dec', 4, false], ['Dec', 11, false], ['Dec', 18, false], ['Dec', 25, true], ['Jan', 1, true], ['Jan', 8, false], ['Jan', 15, false]] as [$thuMonth, $thuDay, $thuOut])
                                    <span @class(['qa-thu', 'is-out' => $thuOut])><small>{{ $thuMonth }}</small><b>{{ $thuDay }}</b></span>
                                @endforeach
                            </div>
                            <p class="qa-sheet-note">
                                One session, one agenda, one link, and its own count of places on every date it runs. Change the time once and every remaining Thursday follows.
                            </p>
                        </div>
                    </div>

                    <div class="qa-cards qa-cards-2" data-reveal-group="100">
                        <div class="qa-card" data-reveal="panel">
                            <div class="qa-card-head">
                                <h3>The days it runs</h3>
                                <span class="qa-tier">Free</span>
                            </div>
                            <p>A day-of-week pattern with a start time. Read more about <a href="{{ marketing_url('/features/recurring-events') }}" class="qa-a">recurring events</a>.</p>
                        </div>
                        <div class="qa-card" data-reveal="panel">
                            <div class="qa-card-head">
                                <h3>The weeks you skip</h3>
                                <span class="qa-tier">Free</span>
                            </div>
                            <p>Date exceptions take single dates out, or put an extra one in, without rebuilding the series. A skipped date is simply not there for your audience.</p>
                        </div>
                        <div class="qa-card" data-reveal="panel">
                            <div class="qa-card-head">
                                <h3>Two kinds of hour</h3>
                                <span class="qa-tier">Free</span>
                            </div>
                            <p>Sub-schedules keep the weekly office hour and the quarterly AMA apart on one link, each with its own colour, so nobody scrolls past what they came for.</p>
                        </div>
                        <div class="qa-card" data-reveal="panel">
                            <div class="qa-card-head">
                                <h3>In their calendar too</h3>
                                <span class="qa-tier">Free</span>
                            </div>
                            <p>Anyone can download a single date as a calendar file, and your own Google, Outlook or CalDAV calendar syncs both ways.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 7. The record: six asks, and what answers each               -->
        <!-- ============================================================ -->
        <section id="asks" class="qa-sec">
            <div class="qa-wrap qa-thread">
                <span class="qa-time" aria-hidden="true">18:40</span>

                <div class="qa-in" data-reveal="left" aria-hidden="true">
                    <span class="qa-av">?</span>
                    <div class="qa-bub qa-bub-q qa-bub-s">Is there a short version?<i class="qa-tail"></i></div>
                </div>

                <div class="qa-reply">
                    <span class="qa-typing" data-reveal aria-hidden="true">{!! $qaDots !!}</span>
                    <div class="qa-bub qa-bub-a" data-reveal="right" style="--reveal-delay: 0.7s;">
                        <h2 class="qa-h2">Six things an audience asks, and the <span class="qa-em">setting that answers it</span></h2>
                        <p>Every row names where the setting lives and which plan it is on. Nothing on this page is anywhere else.</p>
                        <i class="qa-tail" aria-hidden="true"></i>
                    </div>
                </div>

                <div class="qa-table-card" data-reveal="panel">
                    <table class="qa-table">
                        <caption class="sr-only">What a live Q&A audience asks for, the Event Schedule setting that answers it, where that setting lives, and the plan it is on</caption>
                        <thead>
                            <tr>
                                <th scope="col">They ask</th>
                                <th scope="col">You turn on</th>
                                <th scope="col">Where it lives</th>
                                <th scope="col">Plan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($asks as [$askQ, $askWhat, $askWhere, $askPlan])
                                <tr>
                                    <th scope="row"><span>{{ $askQ }}</span></th>
                                    <td data-label="You turn on">{{ $askWhat }}</td>
                                    <td class="qa-where" data-label="Where it lives">{{ $askWhere }}</td>
                                    <td><span class="qa-tier {{ $askPlan === 'Pro' ? 'qa-tier-pro' : '' }}">{{ $askPlan }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <p class="qa-table-note">
                        Pro is {{ plan_price($proMonthly) }} a month, and on the ticketing row it is what lets a ticket carry a price at all. Free registration has no ceiling on any plan, and zero platform fees on ticket sales applies on every plan, including the free one.
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 8. Everything else: between one hour and the next            -->
        <!-- ============================================================ -->
        <section id="rest" class="qa-sec qa-sun">
            <div class="qa-wrap">
                <div class="qa-center">
                    <p class="qa-tag" data-reveal>Everything else</p>
                    <h2 class="qa-big" data-reveal style="--reveal-delay: 0.05s;">
                        Between one hour and the next.
                    </h2>
                </div>

                <div class="qa-rest" data-reveal-group="110">
                    <!-- 1 -->
                    <div class="qa-card qa-wide" data-reveal="panel">
                        <div class="qa-card-head">
                            <h3>Tell the people who already follow you</h3>
                            <span class="qa-tier">Free</span>
                        </div>
                        <p>Your audience follows the schedule, and you see who they are. When there is something worth saying, you write a newsletter and send it, to everyone or to a segment, and you get open and click rates back.</p>
                        <p>New sessions you schedule reach confirmed email subscribers on their own, as a digest, without touching this. The allowance is for the newsletters you write: 10 emails a month on free, 100 on Pro and 1,000 on Enterprise, counted per recipient rather than per send. Read more about <a href="{{ marketing_url('/features/newsletters') }}" class="qa-a">newsletters</a>.</p>
                    </div>

                    <!-- 2 -->
                    <div class="qa-card" data-reveal="panel">
                        <div class="qa-card-head">
                            <h3>A code people can point a phone at</h3>
                            <span class="qa-tier">Free</span>
                        </div>
                        <p>Every schedule has a QR code that takes somebody to your page and lets them follow you. Put it on the last slide of the session, which is when people actually want it.</p>
                    </div>

                    <!-- 3 -->
                    <div class="qa-card qa-wide" data-reveal="panel">
                        <div class="qa-card-head">
                            <h3>On the site you already have</h3>
                            <span class="qa-tier">Free</span>
                        </div>
                        <p>Embed the calendar so your sessions sit where people look you up, and sync two ways with Google, Outlook and CalDAV so your own week stays honest.</p>
                        <p>Built-in <a href="{{ marketing_url('/features/analytics') }}" class="qa-a">analytics</a> show page views, the devices people are on and where the traffic came from. That is what they measure, and nothing more. Embedding the registration form itself on another site is free too; it is the ticket purchase widget that is a Pro feature.</p>
                    </div>

                    <!-- 4 -->
                    <div class="qa-card" data-reveal="panel">
                        <div class="qa-card-head">
                            <h3>Not announced yet</h3>
                            <span class="qa-tier">Free</span>
                        </div>
                        <p>A session you are still thinking about sits on your calendar as a draft. You can see it, your audience cannot, and publishing is one switch when the date is real.</p>
                    </div>

                    <!-- 5 -->
                    <div class="qa-card qa-wide" data-reveal="panel">
                        <div class="qa-card-head">
                            <h3>When the session is worth paying for</h3>
                            <span class="qa-tier qa-tier-pro">Pro</span>
                        </div>
                        <p>Connect your own Stripe or PayPal account and sell named ticket types for a paid AMA or a small-group deep dive, each with its own price, quantity and sales window. Putting a price on a ticket is the Pro plan.</p>
                        <p>Scanning a ticket's QR code at the door is free on every plan. Pro brings the rest of the door tooling with it: the live check-in dashboard, discount codes for the people you want back, add-ons and a waitlist on a sold-out ticket type. Quantities count per date, the same way places do. Event Schedule takes zero platform fees on every plan, so past your processor's own fee the money is yours, and a refund from the Sales page sends a Stripe or PayPal payment back, in full or in part. See all <a href="{{ marketing_url('/features/ticketing') }}" class="qa-a">ticketing features</a>.</p>
                    </div>

                    <!-- 6 -->
                    <div class="qa-card" data-reveal="panel">
                        <div class="qa-card-head">
                            <h3>Ask how the hour went</h3>
                            <span class="qa-tier qa-tier-pro">Pro</span>
                        </div>
                        <p>Post-event feedback collects a star rating and a comment from the people who were there, which is the quietest way to find out whether the hour was worth theirs. On the hosted plan the request sends through your own email settings.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 9. Perfect for: who is in the thread                         -->
        <!-- ============================================================ -->
        <section id="who" class="qa-sec">
            <div class="qa-wrap qa-thread">
                <span class="qa-time" aria-hidden="true">18:45</span>

                <div class="qa-in" data-reveal="left" aria-hidden="true">
                    <span class="qa-av">?</span>
                    <div class="qa-bub qa-bub-q qa-bub-s">Is this for people like me?<i class="qa-tail"></i></div>
                </div>

                <div class="qa-reply">
                    <span class="qa-typing" data-reveal aria-hidden="true">{!! $qaDots !!}</span>
                    <div class="qa-bub qa-bub-a" data-reveal="right" style="--reveal-delay: 0.7s;">
                        <h2 class="qa-h2">Perfect for every type of <span class="qa-em">live Q&amp;A</span></h2>
                        <p>A product AMA or a Thursday office hour, it is the same hour of turns. Also see Event Schedule for <a href="{{ marketing_url('/for-webinars') }}" class="qa-a">Webinars</a> and <a href="{{ marketing_url('/for-virtual-conferences') }}" class="qa-a">Virtual Conferences</a>.</p>
                        <i class="qa-tail" aria-hidden="true"></i>
                    </div>
                </div>

                <div class="qa-people" data-reveal-group="70">
                    @foreach ($qaPeople as [$personInitials, $personName, $personText, $personSlug, $personBg, $personFg])
                        @php $personPost = get_sub_audience_blog($personSlug); @endphp
                        <article class="qa-person" data-reveal>
                            <span class="qa-person-av" style="--bg: {{ $personBg }}; --fg: {{ $personFg }};" aria-hidden="true">{{ $personInitials }}</span>
                            <h3>{{ $personName }}</h3>
                            <p>{{ $personText }}</p>
                            @if ($personPost)
                                <a href="{{ blog_url('/' . $personPost->slug) }}" class="qa-more" aria-label="Learn more about Event Schedule for {{ $personName }}">Learn more {!! $qaArrow !!}</a>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 10. Three steps: one run of three bubbles                    -->
        <!-- ============================================================ -->
        <section class="qa-sec qa-alt">
            <div class="qa-wrap qa-thread">
                <span class="qa-time" aria-hidden="true">18:50</span>

                <div class="qa-in" data-reveal="left" aria-hidden="true">
                    <span class="qa-av">?</span>
                    <div class="qa-bub qa-bub-q qa-bub-s">Where do I start?<i class="qa-tail"></i></div>
                </div>

                <div class="qa-run" data-reveal-group="140">
                    <div class="qa-bub qa-bub-a qa-run-head" data-reveal="right">
                        <h2 class="qa-h2">Three steps to a full hour</h2>
                    </div>
                    @foreach ($steps as [$stepNum, $stepTitle, $stepBody])
                        <div class="qa-bub qa-bub-a" data-reveal="right">
                            <span class="qa-run-n" aria-hidden="true">{{ $stepNum }}</span>
                            <h3>{{ $stepTitle }}</h3>
                            <p>{{ $stepBody }}</p>
                            <i class="qa-tail" aria-hidden="true"></i>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 11. Key features                                             -->
        <!-- ============================================================ -->
        <section class="qa-sec">
            <div class="qa-wrap qa-thread">
                <div class="qa-in" data-reveal="left" aria-hidden="true">
                    <span class="qa-av">?</span>
                    <div class="qa-bub qa-bub-q qa-bub-s">Anything else worth a look?<i class="qa-tail"></i></div>
                </div>

                <div class="qa-reply">
                    <div class="qa-bub qa-bub-a" data-reveal="right">
                        <h2 class="qa-h2">Key features</h2>
                        <i class="qa-tail" aria-hidden="true"></i>
                    </div>
                </div>

                @php
                    $qaFeatures = [
                        ['Online Events', 'One join link, and it works with any platform', marketing_url('/features/online-events')],
                        ['Recurring Events', 'Weekly office hours, with the dates you skip taken out', marketing_url('/features/recurring-events')],
                        ['Analytics', 'Track page views, devices, and traffic sources', marketing_url('/features/analytics')],
                        ['Newsletters', 'Write to the people who follow you, when you have something to say', marketing_url('/features/newsletters')],
                    ];
                @endphp
                <div class="qa-side">
                    <div class="qa-links" data-reveal-group="70">
                        @foreach ($qaFeatures as [$featureName, $featureText, $featureUrl])
                            <a href="{{ $featureUrl }}" class="qa-link" data-reveal>
                                <span>
                                    <strong>{{ $featureName }}</strong>
                                    <small>{{ $featureText }}</small>
                                </span>
                                {!! $qaArrow !!}
                            </a>
                        @endforeach
                    </div>
                    <a href="{{ marketing_url('/features') }}" class="qa-more" data-reveal>
                        See all features
                        {!! $qaArrow !!}
                    </a>
                </div>
            </div>
        </section>

        <div class="qa-plans">
            @include('marketing.partials.pricing-nudge')
        </div>

        <!-- ============================================================ -->
        <!-- 12. Related pages                                            -->
        <!-- ============================================================ -->
        <section class="qa-sec qa-alt">
            <div class="qa-wrap qa-thread">
                <div class="qa-in" data-reveal="left" aria-hidden="true">
                    <span class="qa-av">?</span>
                    <div class="qa-bub qa-bub-q qa-bub-s">Not quite a Q&amp;A?<i class="qa-tail"></i></div>
                </div>

                <div class="qa-reply">
                    <div class="qa-bub qa-bub-a" data-reveal="right">
                        <h2 class="qa-h2">Related pages</h2>
                        <i class="qa-tail" aria-hidden="true"></i>
                    </div>
                </div>

                <div class="qa-side">
                    <div class="qa-rel" data-reveal-group="70">
                        @foreach ([['/for-webinars', 'Webinars'], ['/for-virtual-conferences', 'Virtual Conferences'], ['/for-online-classes', 'Online Classes'], ['/for-watch-parties', 'Watch Parties']] as [$relHref, $relName])
                            <a href="{{ marketing_url($relHref) }}" data-reveal>
                                For {{ $relName }}
                                <small>Read more {!! $qaArrow !!}</small>
                            </a>
                        @endforeach
                    </div>
                    <a href="{{ marketing_url('/use-cases') }}" class="qa-more" data-reveal>
                        See all use cases
                        {!! $qaArrow !!}
                    </a>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 13. FAQ: each one a question and its reply                   -->
        <!-- ============================================================ -->
        <x-seo.faq-schema :items="$faqs" />

        <section id="faq" class="qa-sec">
            <div class="qa-wrap">
                <div class="qa-center">
                    <p class="qa-tag" data-reveal>Questions</p>
                    <h2 class="qa-big" data-reveal style="--reveal-delay: 0.05s;">
                        Frequently asked questions
                    </h2>
                    <p class="qa-center-sub" data-reveal style="--reveal-delay: 0.1s;">
                        What Q&amp;A hosts ask before they move a series across.
                    </p>
                </div>

                <div class="qa-faq" data-reveal-group="80">
                    @foreach ($faqs as $faqIndex => $faq)
                        <details name="faq" data-reveal>
                            <summary>
                                <span class="qa-av" aria-hidden="true">{{ $faqIndex + 1 }}</span>
                                <h3 class="qa-bub qa-bub-q">{{ $faq['q'] }}<span class="qa-plus" aria-hidden="true"></span><i class="qa-tail" aria-hidden="true"></i></h3>
                            </summary>
                            <div class="qa-reply faq-answer">
                                <div class="qa-bub qa-bub-a">
                                    <p>{{ $faq['a'] }}</p>
                                    <i class="qa-tail" aria-hidden="true"></i>
                                </div>
                            </div>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 14. Finale: the last message, and a composer                 -->
        <!-- ============================================================ -->
        <section id="claim" class="qa-end qa-night">
            <span class="qa-end-q" aria-hidden="true">?</span>
            <div class="qa-wrap qa-thread qa-end-in">
                <span class="qa-time" aria-hidden="true">The last turn</span>

                <h2 class="qa-big" data-reveal>
                    You already have the answers. <span class="qa-lit">Give them somewhere to ask.</span>
                </h2>
                <p class="qa-end-lede" data-reveal style="--reveal-delay: 0.08s;">
                    Publishing your sessions, the agenda and registration with a place limit are free forever, however many people sign up. {{ plan_price($proMonthly) }} a month buys polls and the right to charge for a seat. Nothing is ever taken from the door.
                </p>

                <div class="qa-end-col">
                    <div class="qa-in" data-reveal="left" aria-hidden="true">
                        <span class="qa-av">?</span>
                        <div class="qa-bub qa-bub-q qa-bub-s">What should we call your page?<i class="qa-tail"></i></div>
                    </div>

                    <!-- The reply as it will read once it is sent; it follows what is typed below. -->
                    <div class="qa-reply qa-draft" data-reveal="right" aria-hidden="true">
                        <div class="qa-bub qa-bub-a"><b id="qa-mirror">office-hours</b>.eventschedule.com<i class="qa-tail"></i></div>
                        <span class="qa-seen">Ready to send</span>
                    </div>

                    <label for="es-claim-input" class="sr-only">Your schedule name</label>
                    <div class="qa-composer" id="qa-composer" data-reveal="panel">
                        <div dir="ltr" class="es-claim qa-claim">
                            <input id="es-claim-input" type="text" placeholder="office-hours" autocomplete="off" spellcheck="false" maxlength="30">
                            <span>.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="qa-send">
                            Get Started Free
                            <i aria-hidden="true">{!! $qaUp !!}</i>
                        </a>
                    </div>

                    <p class="qa-end-note" data-reveal>No credit card required</p>
                </div>
            </div>
        </section>

        <div class="qa-keep">
            <x-marketing.related-pages />
        </div>
    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    {{-- The draft bubble follows the name being typed, and a little confetti in the thread's own colours goes up when the composer comes into view. --}}
    <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function () {
            var composer = document.getElementById('qa-composer');
            var typed = document.getElementById('es-claim-input');
            var mirror = document.getElementById('qa-mirror');
            if (typed && mirror) {
                {{-- Runs after the shared script has tidied the value, so the bubble shows the name as it will be. --}}
                typed.addEventListener('input', function () {
                    window.requestAnimationFrame(function () {
                        mirror.textContent = typed.value.replace(/-+$/, '') || typed.placeholder;
                    });
                });
            }
            if (!composer || !('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting || typeof window.confetti !== 'function') {
                        return;
                    }
                    io.disconnect();
                    var inks = ['#ffd84d', '#ff5a1f', '#1fb98a', '#fbfaf6'];
                    {{-- Its own cannon, drawn on the page's thread: the default one starts a blob worker, which the site's CSP refuses and logs. --}}
                    var fire = window.confetti.create(null, { resize: true });
                    [[60, 0.08], [120, 0.92]].forEach(function (shot) {
                        fire({ particleCount: 60, angle: shot[0], spread: 55, startVelocity: 50, origin: { x: shot[1], y: 0.95 }, colors: inks, shapes: ['circle'], disableForReducedMotion: true });
                    });
                });
            }, { threshold: 0.7 });
            io.observe(composer);
        });
    </script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
