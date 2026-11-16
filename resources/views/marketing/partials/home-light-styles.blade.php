{{-- The light homepage's styles, printed inside the homepage's own head stylesheet (see the
     @include in marketing/index.blade.php) so that the rules which show one middle and hide the
     other are in force before either is parsed. Every note here is a Blade comment. --}}

    {{-- Which middle is on show. Today's is, unless #hp says light: the picker in the hero says so
       for a visitor given the light page, and the server says so once the round has ended on it. --}}
    #hp:not([data-home="light"]) .hp-lite,
    #hp:not([data-home="light"]) .hp-lite-only,
    #hp[data-home="light"] .hp-full,
    #hp[data-home="light"] .hp-full-only,
    #hp[data-home="light"] .hp-stage { display: none; }

    {{-- With the screen gone the hero is one screen: the headline, the box, and room under them
       before the wall fades into the page. --}}
    #hp[data-home="light"] .hp-hero { padding-bottom: clamp(2.5rem, 13vh, 9rem); }
    #hp[data-home="light"] .hp-hero::after { height: 9rem; }
    @media (max-width: 1023px) {
        #hp[data-home="light"] .hp-hero { padding-bottom: 2.5rem; }
        #hp[data-home="light"] .hp-hero::after { height: 3rem; }
    }

    {{-- Who it is for: the line-up, standing still. --}}
    .hp-lt-for {
        padding-block: clamp(1.4rem, 2.6vw, 2rem);
        border-block: 1px solid var(--hp-line);
        background: var(--hp-bg-2);
    }
    .dark .hp-lt-for { background: var(--hp-bg-3); }
    .hp-lt-for ul {
        text-align: center;
        text-wrap: balance;
        font-size: clamp(1rem, 0.5vw + 0.9rem, 1.2rem);
        font-weight: 700;
        letter-spacing: -0.015em;
    }
    .hp-lt-for li:first-child {
        font-family: var(--hp-mono);
        font-size: 0.78rem;
        font-variation-settings: 'wght' 600;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: var(--hp-ink-3);
    }
    .hp-lt-for li { display: inline-block; margin: 0.25rem 0.8rem; }
    .hp-lt-for a { display: inline-flex; align-items: center; min-height: 2rem; color: var(--hp-ink); transition: color 0.2s ease; }
    .hp-lt-for a:hover { color: var(--hp-blue); }
    .hp-lt-for li:last-child a { color: var(--hp-blue); gap: 0.35rem; }
    .hp-lt-for li:last-child svg { width: 1rem; height: 1rem; }

    {{-- The one show (today's poster and its ticket) and the three acts, each in a sentence. --}}
    .hp-lt-acts { padding-block: clamp(4rem, 8vw, 7rem); }
    .hp-lt-stage { display: grid; grid-template-columns: minmax(0, 1fr); gap: clamp(2.75rem, 6vw, 4.5rem); align-items: center; }
    .hp-lt-stage > .hp-show { justify-self: center; }
    @media (min-width: 1100px) {
        .hp-lt-stage { grid-template-columns: minmax(0, 0.92fr) minmax(0, 1.08fr); column-gap: clamp(3rem, 6vw, 6rem); }
        .hp-lt-stage > .hp-show { max-width: none; }
    }
    .hp-lt-three { display: grid; grid-template-columns: minmax(0, 1fr); gap: clamp(2.25rem, 4vw, 3.25rem); }
    .hp-lt-act { display: flex; flex-direction: column; }
    {{-- From a large phone up an act is a row: the verb, and what it means beside it. --}}
    @media (min-width: 600px) {
        .hp-lt-act { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); column-gap: clamp(1.5rem, 3vw, 2.5rem); align-items: start; }
        .hp-lt-act > .hp-lt-ix, .hp-lt-act > .hp-vbars, .hp-lt-act > .hp-lt-verb { grid-column: 1; }
        #hp .hp-lt-act > p:not(.hp-lt-ix) { grid-column: 2; grid-row: 1 / span 3; align-self: end; margin-top: 0; }
        .hp-lt-act > .hp-more { grid-column: 2; }
    }
    .hp-lt-ix {
        font-family: var(--hp-mono);
        font-size: 0.78rem;
        font-weight: 700;
        font-variation-settings: 'wght' 600;
        letter-spacing: 0.14em;
        color: var(--hp-ink-3);
    }
    .hp-lt-act .hp-vbars { margin-top: 0.9rem; }
    .hp-lt-verb {
        margin-top: 1.1rem;
        font-weight: 700;
        font-variation-settings: 'wght' 880;
        font-size: clamp(3rem, 2.5vw + 1.2rem, 3.9rem);
        letter-spacing: -0.05em;
        line-height: 0.95;
    }
    .hp-lt-verb .hp-ink-grad { display: inline-block; padding-block-end: 0.08em; }
    .hp-lt-act > p:not(.hp-lt-ix) {
        margin-top: 1.1rem;
        max-width: 24rem;
        font-size: clamp(1.05rem, 0.4vw + 0.98rem, 1.2rem);
        line-height: 1.55;
        color: var(--hp-ink-2);
        text-wrap: pretty;
    }
    .hp-lt-act .hp-more { margin-top: 0.8rem; align-self: flex-start; justify-self: start; }

    {{-- What it costs: today's three figures and the sentence under them, and nothing else. --}}
    .hp-lt-cost { padding-block: clamp(4rem, 8vw, 7rem) clamp(3.5rem, 6vw, 5rem); }
    .hp-lt-line { display: block; }
    #hp .hp-lt-cost .hp-figs-note { margin-top: 1.75rem; font-size: clamp(1.1rem, 0.4vw + 1rem, 1.25rem); font-weight: 600; color: var(--hp-ink); }
    .hp-lt-links {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.5rem 2rem;
        margin-top: 1.5rem;
    }

    #hp[data-home="light"] #claim { padding-top: clamp(3rem, 5vw, 4.5rem) !important; }

    {{-- The quotes, when there are any, between the figures and the closing panel. --}}
    .hp-lt-quotes { padding-bottom: clamp(1rem, 3vw, 2.5rem); }
    .hp-lt-quotes .hp-quotes { margin-top: 0; }
