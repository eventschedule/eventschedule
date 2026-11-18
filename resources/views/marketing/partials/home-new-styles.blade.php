{{-- The new homepage's styles (marketing/partials/home-new), printed inside the homepage's own head
     stylesheet so that the rules which show one page and hide the other are in force before either
     is parsed. Drawn on today's page's tokens (--hp-*, which follow the site's dark mode), so the
     two arms are one site; every class of its own is hn-*. Every note here is a Blade comment. --}}

    {{-- Which page is on show. Today's is, unless #hp says new: the picker at the top of #hp says
       so for a visitor given the new page, and the server says so once the round ended on it.
       The dock at the foot of a phone's screen serves both. --}}
    #hp:not([data-home="new"]) > .hn { display: none; }
    #hp[data-home="new"] > :not(.hn):not(.hp-dock):not(script):not(style) { display: none; }

    .hn { --hn-r: 1.25rem; overflow-x: clip; }
    .hn-wrap { width: min(100% - 2rem, 76rem); margin-inline: auto; }
    @media (min-width: 768px) { .hn-wrap { width: min(100% - 4rem, 76rem); } }
    .hn a:not(.hp-btn):not(.hn-card) { color: var(--hp-blue); font-weight: 700; text-decoration: none; }
    .hn a:not(.hp-btn):not(.hn-card):hover { text-decoration: underline; text-underline-offset: 0.2em; }

    {{-- Hero: the promise, the box and three facts, left aligned. --}}
    .hn-hero { position: relative; isolation: isolate; padding: 2.5rem 0 2.25rem; }
    .hn-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -1;
        background:
            radial-gradient(42% 60% at 85% 20%, rgba(34, 211, 238, 0.16), transparent 70%),
            radial-gradient(40% 60% at 5% 0%, rgba(78, 129, 250, 0.18), transparent 70%);
    }
    .hn-hgrid { display: grid; gap: 1.75rem; align-items: end; }
    @media (min-width: 1024px) {
        .hn-hero { padding: 5.5rem 0 4.5rem; }
        .hn-hgrid { grid-template-columns: minmax(0, 1.65fr) minmax(0, 0.6fr); gap: 3.5rem; }
    }
    .hn-eyebrow { font-size: 0.9rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: var(--hp-blue); }
    .hn-h1 {
        margin: 0.875rem 0 0;
        font-size: clamp(2.75rem, 9.4vw, 5.75rem);
        font-weight: 800;
        line-height: 0.98;
        letter-spacing: -0.045em;
        color: var(--hp-ink);
    }
    .hn-h1 > span { display: block; }
    .hn-h1 .hp-ink-grad { padding-block-end: 0.06em; }
    .hn-lede { max-width: 33em; margin: 1.1rem 0 0; font-size: clamp(1.08rem, 2vw, 1.3rem); line-height: 1.5; color: var(--hp-ink-2); }
    .hn-claimwrap { max-width: 38rem; margin-top: 1.5rem; }
    #hp .hn-claimwrap .hp-claimrow { margin: 0; max-width: none; }
    .hn-under { margin: 0.875rem 0 0; font-size: 0.94rem; color: var(--hp-ink-3); }

    .hn-ledger { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); margin: 0; border-top: 1px solid var(--hp-line-2); }
    .hn-ledger dd { margin: 0; padding: 0.875rem 0.5rem 0 0; }
    .hn-ledger b { display: block; font-size: clamp(1.6rem, 3.4vw, 2.75rem); font-weight: 800; line-height: 1; letter-spacing: -0.04em; color: var(--hp-ink); }
    .hn-ledger span { display: block; margin-top: 0.375rem; font-size: 0.85rem; line-height: 1.3; color: var(--hp-ink-2); }
    @media (min-width: 1024px) {
        .hn-ledger { grid-template-columns: minmax(0, 1fr); border-top: 0; }
        .hn-ledger > div { border-top: 1px solid var(--hp-line-2); }
        .hn-ledger dd { padding: 1.4rem 0; }
        .hn-ledger b { font-size: 4rem; }
        .hn-ledger span { font-size: 1rem; }
    }

    {{-- The wall, straight under the hero: two rows drifting in opposite directions, one on a
       phone. Paused under the pointer, and standing still for reduced motion. --}}
    .hn-wall { padding: 0.5rem 0 4rem; overflow: hidden; }
    .hn-wall-cap { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem; margin-bottom: 1.1rem; font-size: 0.94rem; color: var(--hp-ink-2); }
    .hn-wall-cap b { display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 700; color: var(--hp-ink); }
    .hn-wall-cap b i { width: 0.5rem; height: 0.5rem; border-radius: 50%; background: #ef4444; box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.18); }
    .hn-row { display: flex; gap: 1rem; width: max-content; animation: hn-drift var(--t, 110s) linear infinite; }
    .hn-row.is-rev { margin-top: 1.1rem; animation-direction: reverse; }
    .hn-wall:hover .hn-row, .hn-wall:focus-within .hn-row { animation-play-state: paused; }
    @keyframes hn-drift { to { transform: translateX(-50%); } }
    .hn-fig { flex: none; width: var(--w, 9.5rem); margin: 0; }
    .hn-card { display: block; aspect-ratio: 3 / 4; overflow: hidden; border-radius: 0.75rem; background: var(--hp-bg-3); box-shadow: 0 14px 30px -16px rgba(0, 0, 0, 0.5); }
    .hn-card img { width: 100%; height: 100%; object-fit: cover; }
    .hn-fig figcaption { margin-top: 0.5rem; overflow: hidden; font-size: 0.84rem; font-weight: 600; color: var(--hp-ink-2); text-overflow: ellipsis; white-space: nowrap; }
    .hn-mine {
        display: flex;
        flex-direction: column;
        aspect-ratio: 3 / 4;
        padding: 0.875rem;
        border-radius: 0.75rem;
        background: linear-gradient(160deg, #4e81fa, #0ea5e9 60%, #22d3ee);
        box-shadow: 0 0 0 2px var(--hp-bg), 0 0 0 4px #4e81fa, 0 20px 40px -16px rgba(78, 129, 250, 0.7);
        color: #ffffff;
        cursor: text;
    }
    .hn-mine small, .hn-fposter small { display: flex; justify-content: space-between; gap: 0.375rem; font: 700 0.6rem/1 var(--hp-mono); letter-spacing: 0.08em; text-transform: uppercase; white-space: nowrap; }
    .hn-mine strong { margin-top: auto; font-size: calc(1.5rem * var(--fit, 1)); line-height: 0.98; letter-spacing: -0.03em; text-transform: uppercase; overflow-wrap: anywhere; }
    .hn-mine span { margin-top: 0.625rem; font: 500 0.62rem/1.3 var(--hp-mono); overflow-wrap: anywhere; }
    @media (min-width: 768px) { .hn-wall { --w: 12rem; } }
    @media (max-width: 767px) { .hn-row.is-rev { display: none; } }

    {{-- The five promises. --}}
    .hn-ch { padding: 5rem 0; border-top: 1px solid var(--hp-line); }
    @media (min-width: 1024px) { .hn-ch { padding: 7rem 0; } }
    .hn-alt { background: var(--hp-bg-3); }
    .hn-ch-grid { display: grid; gap: 2.25rem; }
    @media (min-width: 1024px) { .hn-ch-grid { grid-template-columns: minmax(0, 0.95fr) minmax(0, 1.05fr); gap: 5rem; align-items: center; } }
    .hn-no { font: 700 0.875rem/1 var(--hp-mono); letter-spacing: 0.08em; text-transform: uppercase; color: var(--hp-blue); }
    .hn-no i { font-style: normal; color: var(--hp-ink-3); }
    .hn-h2 { margin: 0.875rem 0 0; font-size: clamp(2.1rem, 5.6vw, 4rem); font-weight: 800; line-height: 1.02; letter-spacing: -0.04em; color: var(--hp-ink); text-wrap: balance; }
    .hn-sub { max-width: 32em; margin: 1.1rem 0 0; font-size: clamp(1.06rem, 1.8vw, 1.25rem); color: var(--hp-ink-2); }
    .hn-free { margin: 1.75rem 0 0; padding: 0; list-style: none; }
    .hn-free li { display: flex; align-items: baseline; gap: 0.75rem; padding: 0.75rem 0; border-top: 1px solid var(--hp-line); font-size: 1.06rem; color: var(--hp-ink); }
    .hn-free li span { margin-inline-start: auto; font-size: 0.875rem; color: var(--hp-ink-3); white-space: nowrap; }
    .hn-free svg { flex: none; width: 1rem; height: 1rem; color: #15803d; transform: translateY(2px); }
    .dark .hn-free svg { color: #4ade80; }
    .hn-zero-price { text-align: center; }
    .hn-zero-price .hn-sub { margin-inline: auto; max-width: 22em; }
    .hn-big0 { font-size: clamp(7.5rem, 22vw, 16rem); font-weight: 800; line-height: 0.8; letter-spacing: -0.07em; }
    .hn-big0, .hn-pct { background: linear-gradient(90deg, #2f62e0, #0284c7 55%, #0891b2); -webkit-background-clip: text; background-clip: text; color: transparent; }
    .dark .hn-big0 { background-image: linear-gradient(90deg, #6e98ff, #38bdf8 55%, #22d3ee); }

    {{-- 02, dark in both modes: the 0% and the calculator. --}}
    .hn-dark { position: relative; isolation: isolate; overflow: hidden; border-top: 0; background: #04060b; color: #eef2f8; }
    .hn-dark::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -1;
        background:
            radial-gradient(45% 60% at 20% 30%, rgba(78, 129, 250, 0.32), transparent 70%),
            radial-gradient(40% 50% at 90% 80%, rgba(34, 211, 238, 0.18), transparent 70%);
    }
    .hn-dark::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -1;
        background-image: linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
        background-size: 64px 64px;
        -webkit-mask-image: radial-gradient(60% 70% at 30% 40%, #000, transparent 80%);
        mask-image: radial-gradient(60% 70% at 30% 40%, #000, transparent 80%);
    }
    .hn-dark .hn-no { color: #8fb0ff; }
    .hn-dark .hn-no i { color: #8a95ad; }
    .hn-dark .hn-sub { color: #b4bed0; }
    .hn-pct {
        margin: 1.4rem 0 0;
        font-size: clamp(9rem, 30vw, 21rem);
        font-weight: 800;
        line-height: 0.78;
        letter-spacing: -0.07em;
        background-image: linear-gradient(180deg, #ffffff 10%, #86a8ff 55%, #22d3ee);
        filter: drop-shadow(0 0 60px rgba(78, 129, 250, 0.45));
    }
    .hn-pct-cap { margin: 1.1rem 0 0; font-size: clamp(1.25rem, 2.4vw, 1.6rem); font-weight: 700; line-height: 1.25; letter-spacing: -0.02em; color: #ffffff; }
    .hn-calc { padding: 1.6rem; border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 1.5rem; background: rgba(255, 255, 255, 0.04); }
    .hn-calc h3 { margin: 0; font-size: 0.94rem; letter-spacing: 0.08em; text-transform: uppercase; color: #8fb0ff; }
    .hn-sl { margin-top: 1.25rem; }
    .hn-sl label { display: flex; justify-content: space-between; font-size: 0.97rem; color: #b4bed0; }
    .hn-sl label b { font-size: 1.1rem; color: #ffffff; font-variant-numeric: tabular-nums; }
    .hn-sl input[type=range] {
        -webkit-appearance: none;
        appearance: none;
        width: 100%;
        height: 6px;
        margin: 0.875rem 0 0.25rem;
        border-radius: 99px;
        background: linear-gradient(90deg, #4e81fa, #22d3ee) 0 / var(--p, 50%) 100% no-repeat, rgba(255, 255, 255, 0.14);
        outline: 0;
    }
    .hn-sl input[type=range]:focus-visible { outline: 2px solid #8fb0ff; outline-offset: 6px; }
    .hn-sl input[type=range]::-webkit-slider-thumb { -webkit-appearance: none; width: 24px; height: 24px; border-radius: 50%; background: #ffffff; box-shadow: 0 0 0 6px rgba(78, 129, 250, 0.35); cursor: pointer; }
    .hn-sl input[type=range]::-moz-range-thumb { width: 24px; height: 24px; border: 0; border-radius: 50%; background: #ffffff; box-shadow: 0 0 0 6px rgba(78, 129, 250, 0.35); }
    .hn-out { margin-top: 1.4rem; border-top: 1px dashed rgba(255, 255, 255, 0.18); }
    .hn-out div { display: flex; justify-content: space-between; gap: 0.75rem; padding: 0.7rem 0; border-bottom: 1px dashed rgba(255, 255, 255, 0.1); font-size: 1rem; color: #c6cedc; }
    .hn-out b { color: #ffffff; font-variant-numeric: tabular-nums; }
    .hn-out .is-zero b { color: #4ade80; }
    .hn-out .is-keep { border-bottom: 0; padding-top: 1rem; font-size: 1.1rem; font-weight: 700; color: #ffffff; }
    .hn-out .is-keep b { font-size: 2.1rem; letter-spacing: -0.03em; background: linear-gradient(90deg, #86a8ff, #38bdf8 55%, #22d3ee); -webkit-background-clip: text; background-clip: text; color: transparent; }
    .hn-calc > p { margin: 0.75rem 0 0; font-size: 0.82rem; line-height: 1.45; color: #9aa5ba; }
    .hn-calc > p b { color: #c6cedc; }

    {{-- 03, 04, 05. --}}
    .hn-pay { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.625rem; margin: 0; padding: 0; list-style: none; }
    @media (min-width: 640px) { .hn-pay { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    .hn-pay li { display: flex; flex-direction: column; gap: 0.25rem; padding: 1rem; border: 1px solid var(--hp-line); border-radius: 1rem; background: var(--hp-bg-2); box-shadow: var(--hp-card-shadow); font-size: 1.03rem; font-weight: 700; color: var(--hp-ink); }
    .hn-pay small { font-size: 0.85rem; font-weight: 500; color: var(--hp-ink-3); }
    .hn-pay .is-wide { grid-column: 1 / -1; }
    .hn-paths { display: grid; gap: 0.75rem; margin: 0; padding: 0; list-style: none; }
    .hn-paths li { display: grid; grid-template-columns: 3.25rem minmax(0, 1fr); gap: 1rem; align-items: center; padding: 1.1rem; border: 1px solid var(--hp-line); border-radius: 1.1rem; background: var(--hp-bg-2); box-shadow: var(--hp-card-shadow); }
    .hn-paths i { display: grid; place-items: center; width: 3.25rem; height: 3.25rem; border-radius: 0.95rem; background: rgba(78, 129, 250, 0.12); color: var(--hp-blue); }
    .hn-paths i svg { width: 1.6rem; height: 1.6rem; }
    .hn-paths b { display: block; font-size: 1.15rem; letter-spacing: -0.01em; color: var(--hp-ink); }
    .hn-paths span { font-size: 0.97rem; color: var(--hp-ink-2); }
    .hn-keep { margin: 0; border-top: 1px solid var(--hp-line-2); }
    .hn-keep > div { display: grid; gap: 0.25rem; padding: 1.25rem 0; border-bottom: 1px solid var(--hp-line-2); }
    @media (min-width: 640px) { .hn-keep > div { grid-template-columns: 12.5rem minmax(0, 1fr); gap: 1.5rem; } }
    .hn-keep dt { font-size: 1.25rem; font-weight: 800; letter-spacing: -0.02em; color: var(--hp-ink); }
    .hn-keep dd { margin: 0; color: var(--hp-ink-2); }

    {{-- The one link: a complete 3 x 2 grid, and the three steps. --}}
    .hn-bento, .hn-steps { display: grid; gap: 1rem; margin: 2.75rem 0 0; padding: 0; list-style: none; }
    @media (min-width: 700px) { .hn-bento { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (min-width: 1024px) { .hn-bento { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    .hn-card-box { display: flex; flex-direction: column; padding: 1.5rem; border: 1px solid var(--hp-line); border-radius: 1.4rem; background: var(--hp-bg-2); box-shadow: var(--hp-card-shadow); }
    .hn-card-box h3 { margin: 1.25rem 0 0.375rem; font-size: 1.3rem; font-weight: 800; letter-spacing: -0.02em; color: var(--hp-ink); }
    .hn-card-box p { margin: 0; font-size: 1rem; color: var(--hp-ink-2); }
    .hn-ico { display: grid; place-items: center; width: 2.75rem; height: 2.75rem; border-radius: 0.8rem; background: color-mix(in srgb, var(--c) 13%, transparent); color: var(--c); }
    .hn-ico svg { width: 1.45rem; height: 1.45rem; }
    @media (min-width: 700px) { .hn-bento .hn-card-box { min-height: 13.75rem; } .hn-bento .hn-card-box h3 { margin-top: auto; padding-top: 1.75rem; } }
    .hn-steps { counter-reset: hn-step; }
    @media (min-width: 900px) { .hn-steps { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    .hn-steps li::before {
        counter-increment: hn-step;
        content: counter(hn-step);
        font-size: 3.5rem;
        font-weight: 800;
        line-height: 1;
        letter-spacing: -0.04em;
        background: linear-gradient(90deg, #2f62e0, #0284c7 55%, #0891b2);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }
    .dark .hn-steps li::before { background-image: linear-gradient(90deg, #6e98ff, #38bdf8 55%, #22d3ee); }
    .hn-steps h3 { margin-top: 1rem; }
    .hn-steps small { margin-top: auto; padding-top: 1rem; font-size: 0.875rem; color: var(--hp-ink-3); }

    {{-- Questions. --}}
    .hn-faq { display: grid; gap: 2.25rem; }
    @media (min-width: 1024px) { .hn-faq { grid-template-columns: minmax(0, 0.8fr) minmax(0, 1.2fr); gap: 5rem; } }
    .hn-faq details { border-bottom: 1px solid var(--hp-line-2); }
    .hn-faq summary { position: relative; padding: 1.4rem 2.5rem 1.4rem 0; list-style: none; cursor: pointer; font-size: 1.2rem; font-weight: 700; color: var(--hp-ink); }
    .hn-faq summary::-webkit-details-marker { display: none; }
    .hn-faq summary::before, .hn-faq summary::after { content: ""; position: absolute; inset-inline-end: 0.5rem; top: 50%; width: 0.875rem; height: 2px; background: var(--hp-ink-3); transition: transform 0.2s ease; }
    .hn-faq summary::after { transform: rotate(90deg); }
    .hn-faq details[open] summary::after { transform: rotate(0); }
    .hn-faq details p { max-width: 40em; margin: 0 0 1.4rem; font-size: 1.03rem; color: var(--hp-ink-2); }

    {{-- The finale. --}}
    .hn-fin { padding: 5.5rem 0; overflow: hidden; border-top: 1px solid var(--hp-line); background: var(--hp-bg-3); }
    .hn-fgrid { display: grid; gap: 2.75rem; align-items: center; }
    @media (min-width: 1024px) { .hn-fin { padding: 8rem 0; } .hn-fgrid { grid-template-columns: minmax(0, 1.3fr) minmax(0, 0.7fr); gap: 5rem; } }
    .hn-fin-h { font-size: clamp(2.5rem, 7vw, 5.25rem); }
    .hn-fposter {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-self: center;
        width: min(18.75rem, 78vw);
        aspect-ratio: 4 / 5;
        padding: 1.4rem;
        overflow: hidden;
        border-radius: 1.25rem;
        background: linear-gradient(160deg, #4e81fa, #0ea5e9 60%, #22d3ee);
        box-shadow: 0 40px 80px -30px rgba(78, 129, 250, 0.75);
        color: #ffffff;
        transform: rotate(3deg);
        cursor: text;
    }
    .hn-fposter::after { content: ""; position: absolute; inset: 0; background: linear-gradient(115deg, transparent 35%, rgba(255, 255, 255, 0.22) 48%, transparent 62%); pointer-events: none; }
    .hn-fposter small { font-size: 0.75rem; letter-spacing: 0.1em; }
    .hn-fposter strong { margin-top: auto; font-size: calc(2.75rem * var(--fit, 1)); line-height: 0.95; letter-spacing: -0.04em; text-transform: uppercase; overflow-wrap: anywhere; }
    .hn-fposter span { margin-top: 0.875rem; font: 600 0.8rem/1.3 var(--hp-mono); overflow-wrap: anywhere; }
    .hn-fposter em { align-self: flex-start; margin-top: 1rem; padding: 0.5rem 0.875rem; border-radius: 0.625rem; background: #ffffff; color: #0a1222; font-size: 0.875rem; font-style: normal; font-weight: 800; }
    [dir="rtl"] .hn-fposter { transform: rotate(-3deg); }

    @media (prefers-reduced-motion: reduce) {
        .hn-row { animation: none; }
        .hn-wall { overflow-x: auto; }
        .hn-faq summary::before, .hn-faq summary::after { transition: none; }
    }
