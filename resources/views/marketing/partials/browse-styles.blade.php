<style {!! nonce_attr() !!}>
    /* ==============================================================
       /browse: the wall.

       The posters are the page. Every flyer is shown whole, in its
       own shape, and lit from behind by its own colours; an event
       with no flyer gets a poster set in type. App\Utils\PosterWall
       decides where each row ends so the rows come out flush, and
       this file only has to say that a poster is as wide as its
       shape (flex-grow) over a box of that shape (aspect-ratio).

       Two left edges, and only two: the words (the hero, the
       questions, the closing panel) stand on the site header's own
       column, and the wall runs from one side of the window to the
       other.

       Nothing here is a metaphor a visitor has to decode: the copy
       is plain, and the only colour is the brand blue plus whatever
       the posters bring.

       Plain CSS on purpose. A Tailwind class that is not already in
       the built stylesheet renders as nothing, and none of these
       are. Everything is under one id, light on it and dark on
       .dark, and the closing panel is dark in both.

       No probe of a feature whose condition holds a hex colour in
       this block: it breaks Blade's compile of every later
       directive.
       ============================================================== */

    #bw {
        --bw-bg: #f3f5fa;
        --bw-bg-2: #ffffff;
        --bw-bg-3: #e8ecf5;
        --bw-ink: #0a1020;
        --bw-ink-2: #36405a;
        --bw-ink-3: #55607b;
        --bw-line: rgba(10, 16, 32, 0.1);
        --bw-line-2: rgba(10, 16, 32, 0.2);
        --bw-blue: #2456d6;
        --bw-blue-2: #0b84c9;
        --bw-blue-3: #0a9db5;
        --bw-glow: 0.46;
        --bw-ambient: 0.1;
        --bw-edge: inset 0 0 0 1px rgba(10, 16, 32, 0.1);
        --bw-shadow: 0 1px 2px rgba(10, 16, 32, 0.12), 0 14px 30px -16px rgba(10, 16, 32, 0.42);
        --bw-display: 'Red Hat Display', 'Inter', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        --bw-gap: 0.75rem;
        --bw-row: 1.6rem;
        background-color: var(--bw-bg);
        color: var(--bw-ink);
    }
    .dark #bw {
        --bw-bg: #070a14;
        --bw-bg-2: #0e1424;
        --bw-bg-3: #131a2c;
        --bw-ink: #eef2ff;
        --bw-ink-2: #c5cde2;
        --bw-ink-3: #97a3c0;
        --bw-line: rgba(255, 255, 255, 0.1);
        --bw-line-2: rgba(255, 255, 255, 0.2);
        --bw-blue: #8db0ff;
        --bw-blue-2: #5cc4f5;
        --bw-blue-3: #67e8f9;
        --bw-glow: 0.55;
        --bw-ambient: 0.3;
        --bw-edge: inset 0 0 0 1px rgba(255, 255, 255, 0.08);
        --bw-shadow: 0 18px 40px -18px rgba(0, 0, 0, 0.9);
    }
    @media (min-width: 640px) {
        #bw { --bw-gap: 1.125rem; --bw-row: 2.1rem; }
    }
    @media (min-width: 1024px) {
        #bw { --bw-gap: 1.375rem; --bw-row: 2.6rem; }
    }

    /* The site's header sits on this page's ground rather than on white. */
    body > header.sticky { background-color: rgba(243, 245, 250, 0.82); }
    .dark body > header.sticky { background-color: rgba(7, 10, 20, 0.82); }

    /* The header's own column, so the words here start where its logo does. */
    #bw .bw-col {
        width: 100%;
        max-width: 80rem;
        margin-inline: auto;
        padding-inline: 1rem;
    }
    /* The wall's: the window, less a gutter. */
    #bw .bw-bleed {
        width: 100%;
        max-width: 160rem;
        margin-inline: auto;
        padding-inline: 1rem;
    }
    @media (min-width: 640px) {
        #bw .bw-col, #bw .bw-bleed { padding-inline: 1.5rem; }
    }
    @media (min-width: 1024px) {
        #bw .bw-col { padding-inline: 2rem; }
        #bw .bw-bleed { padding-inline: 2.5rem; }
    }

    #bw .bw-lit {
        background-image: linear-gradient(92deg, var(--bw-blue), var(--bw-blue-2) 60%, var(--bw-blue-3));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    /* A jump lands under the site's sticky header, not behind it. */
    #bw [id] { scroll-margin-top: 5.5rem; }

    #bw a:focus-visible,
    #bw summary:focus-visible,
    #bw button:focus-visible,
    #bw select:focus-visible {
        outline: 2px solid var(--bw-blue);
        outline-offset: 3px;
    }

    /* --- Buttons --- */
    #bw .bw-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        min-height: 3.5rem;
        padding: 0 1.75rem;
        border: 0;
        border-radius: 1rem;
        background-image: linear-gradient(135deg, #2f66ea, #1d4ed8);
        color: #ffffff;
        font-size: 1.0625rem;
        font-weight: 650;
        white-space: nowrap;
        box-shadow: 0 14px 30px -14px rgba(29, 78, 216, 0.7);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    #bw .bw-btn:hover { transform: translateY(-2px); box-shadow: 0 20px 36px -14px rgba(29, 78, 216, 0.8); }
    #bw .bw-btn svg { width: 1.25rem; height: 1.25rem; transition: transform 0.2s ease; }
    #bw .bw-btn:hover svg { transform: translateX(3px); }
    [dir="rtl"] #bw .bw-btn svg { transform: scaleX(-1); }
    #bw .bw-btn-sm { min-height: 2.5rem; padding: 0 1rem; font-size: 0.9rem; border-radius: 0.7rem; }
    #bw .bw-ghost {
        display: inline-flex;
        align-items: center;
        min-height: 3rem;
        padding: 0 1.5rem;
        border: 1px solid var(--bw-line-2);
        border-radius: 1rem;
        background-color: var(--bw-bg-2);
        color: var(--bw-ink);
        font-weight: 650;
        transition: border-color 0.2s ease, transform 0.2s ease;
    }
    #bw .bw-ghost:hover { border-color: var(--bw-blue); transform: translateY(-2px); }

    /* ==============================================================
       1. Hero
       ============================================================== */
    #bw .bw-hero {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        padding: 2.5rem 0 1.5rem;
    }
    @media (min-width: 1024px) { #bw .bw-hero { padding: 4rem 0 2.25rem; } }
    /* Two lamps over the wall. By day they are barely there. */
    #bw .bw-hero-light {
        position: absolute;
        inset: 0;
        z-index: -1;
        pointer-events: none;
        background-image:
            radial-gradient(60rem 26rem at 12% -8rem, rgba(47, 102, 234, 0.16), rgba(47, 102, 234, 0) 70%),
            radial-gradient(48rem 22rem at 88% -6rem, rgba(11, 132, 201, 0.13), rgba(11, 132, 201, 0) 70%);
    }
    .dark #bw .bw-hero-light {
        background-image:
            radial-gradient(60rem 28rem at 12% -8rem, rgba(78, 129, 250, 0.3), rgba(78, 129, 250, 0) 70%),
            radial-gradient(48rem 24rem at 88% -6rem, rgba(34, 211, 238, 0.16), rgba(34, 211, 238, 0) 70%);
    }
    #bw .bw-hero-in {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 1.75rem;
    }
    @media (min-width: 1024px) {
        #bw .bw-hero-in {
            grid-template-columns: minmax(0, 1.6fr) minmax(0, 1fr);
            align-items: end;
            gap: 3.5rem;
        }
    }
    #bw .bw-eyebrow {
        display: inline-flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.6rem;
        margin-bottom: 1.1rem;
        color: var(--bw-ink-2);
        font-family: var(--bw-display);
        font-size: 0.95rem;
        font-weight: 700;
    }
    #bw .bw-eyebrow-sep { width: 1.5rem; height: 1px; background-color: var(--bw-line-2); }
    #bw .bw-eyebrow span:last-child { color: var(--bw-ink-3); font-weight: 500; }
    #bw .bw-pulse {
        position: relative;
        width: 0.55rem;
        height: 0.55rem;
        border-radius: 50%;
        background-color: #16a34a;
    }
    #bw .bw-pulse::after {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: 50%;
        background-color: #16a34a;
        animation: bw-ping 2.2s cubic-bezier(0, 0, 0.2, 1) infinite;
    }
    @keyframes bw-ping {
        0% { transform: scale(1); opacity: 0.7; }
        80%, 100% { transform: scale(2.6); opacity: 0; }
    }
    #bw .bw-hero-say { container-type: inline-size; }
    #bw .bw-h1 {
        font-family: var(--bw-display);
        font-size: 2.45rem;
        font-weight: 800;
        font-variation-settings: 'wght' 830;
        line-height: 0.98;
        letter-spacing: -0.035em;
        color: var(--bw-ink);
        text-wrap: balance;
    }
    #bw .bw-h1 br { display: none; }
    @media (min-width: 640px) {
        /* The longer line is about 12.7 of its own em wide, so this is the largest it can be set
           and still stay on one line of the column. */
        #bw .bw-h1 { font-size: clamp(2.6rem, 7.6cqi, 5rem); white-space: nowrap; text-wrap: nowrap; }
        #bw .bw-h1 br { display: inline; }
    }
    #bw .bw-lede {
        max-width: 34rem;
        margin-top: 1.1rem;
        color: var(--bw-ink-2);
        font-size: 1.125rem;
        line-height: 1.5;
    }

    #bw .bw-search-label {
        display: block;
        margin-bottom: 0.55rem;
        color: var(--bw-ink-2);
        font-size: 0.9rem;
        font-weight: 600;
    }
    #bw .bw-search-row { display: flex; gap: 0.6rem; }
    #bw .bw-search-box {
        position: relative;
        flex: 1 1 auto;
        min-width: 0;
        border: 1px solid var(--bw-line-2);
        border-radius: 1rem;
        background-color: var(--bw-bg-2);
        box-shadow: 0 10px 30px -22px rgba(10, 16, 32, 0.5);
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    #bw .bw-search-box:focus-within {
        border-color: var(--bw-blue);
        box-shadow: 0 0 0 4px rgba(47, 102, 234, 0.2);
    }
    #bw .bw-search-icon {
        position: absolute;
        top: 50%;
        inset-inline-start: 1rem;
        width: 1.25rem;
        height: 1.25rem;
        transform: translateY(-50%);
        color: var(--bw-ink-3);
        pointer-events: none;
    }
    #bw .bw-search-box input {
        width: 100%;
        height: 3.5rem;
        padding: 0 1rem;
        padding-inline-start: 3rem;
        border: 0;
        border-radius: 1rem;
        background-color: transparent;
        color: var(--bw-ink);
        font-size: 1.0625rem;
    }
    #bw .bw-search-box input::placeholder { color: var(--bw-ink-3); opacity: 1; }
    #bw .bw-search-box input:focus { outline: none; box-shadow: none; }
    @media (max-width: 419.98px) {
        #bw .bw-search-row .bw-btn { padding: 0 1.1rem; }
    }

    /* One line. Where it cannot fit, it scrolls sideways rather than leave one chip alone on a
       second row. */
    #bw .bw-jump {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
        overflow-x: auto;
        scrollbar-width: none;
        padding-block: 0.15rem;
    }
    #bw .bw-jump::-webkit-scrollbar { display: none; }
    #bw .bw-jump a { flex: none; }
    #bw .bw-jump a {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        min-height: 2.75rem;
        padding: 0 0.85rem;
        white-space: nowrap;
        border: 1px solid var(--bw-line);
        border-radius: 999px;
        background-color: var(--bw-bg-2);
        color: var(--bw-ink);
        font-size: 0.9rem;
        font-weight: 650;
        transition: border-color 0.2s ease, transform 0.2s ease;
    }
    #bw .bw-jump a:hover { border-color: var(--bw-blue); transform: translateY(-1px); }
    #bw .bw-jump a span { color: var(--bw-ink-3); font-weight: 500; font-variant-numeric: tabular-nums; }

    /* ==============================================================
       2. The wall
       ============================================================== */
    #bw .bw-stage { position: relative; isolation: isolate; padding: 1rem 0 3.5rem; }
    @media (min-width: 1024px) { #bw .bw-stage { padding: 1.5rem 0 5rem; } }

    /* The colours of the poster under the pointer, thrown across the wall behind everything. A small picture enlarged, which is
       already out of focus and costs a fraction of blurring a large one. */
    #bw .bw-ambient {
        position: absolute;
        inset: 0;
        z-index: -1;
        /* clip, not hidden: hidden would make this the box its sticky child scrolls in, and the
           light would stay on the wall's first screen. */
        overflow: clip;
        pointer-events: none;
        -webkit-mask-image: linear-gradient(180deg, rgba(0, 0, 0, 0), #000 8rem, #000 calc(100% - 10rem), rgba(0, 0, 0, 0));
        mask-image: linear-gradient(180deg, rgba(0, 0, 0, 0), #000 8rem, #000 calc(100% - 10rem), rgba(0, 0, 0, 0));
    }
    #bw .bw-ambient-in { position: sticky; top: 0; height: 100vh; }
    #bw .bw-ambient img {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 14vw;
        height: 14vh;
        max-width: none;
        object-fit: cover;
        opacity: 0;
        transform: translate(-50%, -50%) scale(9);
        filter: blur(9px) saturate(1.7);
        transition: opacity 1s ease;
    }
    /* At night the wall takes a flyer's hue, not its lightness: a white flyer would fog it. */
    .dark #bw .bw-ambient img { filter: blur(9px) saturate(2.4) brightness(0.42); }
    #bw .bw-ambient img.is-on { opacity: var(--bw-ambient); }

    #bw .bw-flash {
        display: inline-block;
        margin-bottom: 1.5rem;
        padding: 0.55rem 1rem;
        border: 1px solid var(--bw-line-2);
        border-radius: 999px;
        background-color: var(--bw-bg-2);
        font-size: 0.9rem;
        font-weight: 600;
    }
    #bw .bw-flash-bad { border-color: #f59e0b; background-color: #fffbeb; color: #5a3a04; }
    .dark #bw .bw-flash-bad { border-color: rgba(245, 158, 11, 0.6); background-color: #2a1f08; color: #fde9c0; }

    /* Every poster is as wide as its shape says, over a zero basis, so the posters between two
       breaks always share one row whatever the window measures. */
    #bw .bw-wall {
        display: flex;
        flex-wrap: wrap;
        align-items: stretch;
        column-gap: var(--bw-gap);
    }
    #bw .bw-tile {
        position: relative;
        min-width: 0;
        margin-bottom: var(--bw-row);
    }
    /* Times a hundred: growth factors that sum to less than one share out only that fraction of
       the row, which left a poster alone in its row short of the far edge. */
    #bw .bw-poster { flex: calc(var(--r) * 100) 1 0%; container-type: inline-size; }
    #bw .bw-frame {
        position: relative;
        display: block;
        aspect-ratio: var(--r);
        transition: opacity 0.35s ease;
    }
    /* The posters on screen as the page opens arrive one after another. Plain CSS, so they are
       there whether or not any script runs; and only under the motion gate. */
    @keyframes bw-hang {
        from { opacity: 0; transform: translateY(1.1rem) scale(0.985); }
        to { opacity: 1; transform: none; }
    }
    html.es-anim #bw .bw-in {
        animation: bw-hang 0.7s cubic-bezier(0.2, 0.7, 0.2, 1) both;
        animation-delay: calc(var(--i, 0) * 55ms);
    }
    #bw .bw-brk { display: none; flex: 0 0 100%; height: 0; }
    #bw .bw-pad { display: none; flex: calc(var(--p) * 100) 1 0%; }

    @media (max-width: 639.98px) {
        #bw .bw-brk-m, #bw .bw-pad-m { display: block; }
        #bw .bw-blank { --r: var(--rm); }
        #bw .bw-lone .bw-frame { aspect-ratio: 0.8; }
        #bw .bw-lone .bw-img { object-fit: contain; }
        #bw .bw-lone.bw-type .bw-frame { aspect-ratio: 1.2; }
        #bw .bw-lone.bw-type .bw-face { padding: 6cqi; }
        #bw .bw-lone.bw-type .bw-stamp-num { font-size: 24cqi; }
        #bw .bw-lone.bw-type .bw-stamp-top,
        #bw .bw-lone.bw-type .bw-stamp-mon { font-size: 4.4cqi; }
        #bw .bw-lone.bw-type .bw-face-top img { width: 12cqi; height: 12cqi; }
        #bw .bw-lone.bw-type .bw-face-name { font-size: 9cqi; -webkit-line-clamp: 2; }
    }
    @media (min-width: 640px) {
        #bw .bw-lone:not(.bw-fit) .bw-fill { display: none; }
    }
    @media (min-width: 640px) and (max-width: 1023.98px) {
        #bw .bw-brk-t, #bw .bw-pad-t { display: block; }
        #bw .bw-label.bw-mid-t { margin-inline-start: var(--bw-gap); }
        #bw .bw-blank { --r: var(--rt); }
    }
    @media (min-width: 1024px) and (max-width: 1439.98px) {
        #bw .bw-brk-l, #bw .bw-pad-l { display: block; }
        #bw .bw-label.bw-mid-l { margin-inline-start: var(--bw-gap); }
        #bw .bw-blank { --r: var(--rl); }
    }
    @media (min-width: 1440px) and (max-width: 1919.98px) {
        #bw .bw-brk-d, #bw .bw-pad-d { display: block; }
        #bw .bw-label.bw-mid-d { margin-inline-start: var(--bw-gap); }
        #bw .bw-blank { --r: var(--rd); }
    }
    @media (min-width: 1920px) {
        #bw .bw-brk-x, #bw .bw-pad-x { display: block; }
        #bw .bw-label.bw-mid-x { margin-inline-start: var(--bw-gap); }
        #bw .bw-blank { --r: var(--rx); }
    }

    /* An even grid, for pictures whose shape was never recorded. */
    #bw .bw-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: var(--bw-row) var(--bw-gap);
    }
    #bw .bw-grid .bw-tile { margin-bottom: 0; }
    @media (min-width: 640px) { #bw .bw-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (min-width: 1024px) { #bw .bw-grid { grid-template-columns: repeat(var(--n4, 4), minmax(0, 1fr)); } }
    @media (min-width: 1280px) { #bw .bw-grid { grid-template-columns: repeat(var(--n6, 6), minmax(0, 1fr)); } }
    @media (min-width: 1920px) { #bw .bw-grid { grid-template-columns: repeat(var(--n8, 8), minmax(0, 1fr)); } }

    /* --- A poster --- */
    #bw .bw-clip {
        position: absolute;
        inset: 0;
        overflow: hidden;
        border-radius: 0.65rem;
        background-color: var(--bw-bg-3);
        box-shadow: var(--bw-shadow);
        transition: transform 0.4s cubic-bezier(0.2, 0.7, 0.2, 1), box-shadow 0.4s ease;
    }
    /* A hairline over the picture, so a white flyer still has an edge on a pale wall. */
    #bw .bw-clip::after {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: inherit;
        box-shadow: var(--bw-edge);
        pointer-events: none;
    }
    #bw .bw-img {
        position: relative;
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    #bw .bw-fit .bw-img { object-fit: contain; }
    /* A picture that cannot fill its box sits on its own colours, out of focus. */
    #bw .bw-fill {
        position: absolute;
        inset: -12%;
        width: 124%;
        height: 124%;
        max-width: none;
        object-fit: cover;
        filter: blur(22px) saturate(1.5) brightness(0.86);
    }
    /* The light a poster throws on the wall: itself again, out of focus, a little below. */
    /* Kept under the poster and inside its own width, so two neighbours' lights do not meet in
       the gap between them and mix into a colour neither flyer has. */
    #bw .bw-glow {
        position: absolute;
        inset: 14% 9% 3%;
        width: 82%;
        height: 83%;
        max-width: none;
        object-fit: cover;
        border-radius: 1.4rem;
        opacity: var(--bw-glow);
        filter: blur(18px) saturate(1.7);
        transition: opacity 0.4s ease, transform 0.4s ease;
        pointer-events: none;
    }
    #bw .bw-poster.is-hidden .bw-clip { opacity: 0.55; }

    /* Under the pointer a poster comes forward and the others stand back. */
    @media (hover: hover) {
        #bw .bw-poster:hover .bw-clip { transform: translateY(-9px) scale(1.02); box-shadow: 0 2px 4px rgba(10, 16, 32, 0.14), 0 30px 50px -20px rgba(10, 16, 32, 0.55); }
        #bw .bw-poster:hover .bw-glow { opacity: 0.95; transform: scale(1.1); }
        #bw .bw-poster:hover .bw-name { color: var(--bw-blue); }
        /* The others stand back at night only. On a pale wall a faded poster looks switched off. */
        .dark #bw .bw-wall:has(.bw-poster:hover) .bw-poster:not(:hover) .bw-frame,
        .dark #bw .bw-grid:has(.bw-poster:hover) .bw-poster:not(:hover) .bw-frame { opacity: 0.62; }
    }
    #bw .bw-poster:focus-within .bw-clip { transform: translateY(-9px) scale(1.02); }
    #bw .bw-poster:focus-within .bw-glow { opacity: 1; }

    #bw .bw-link {
        position: absolute;
        inset: 0;
        z-index: 2;
        border-radius: 0.65rem;
    }
    #bw .bw-link:focus-visible { outline-offset: 4px; }

    #bw .bw-live,
    #bw .bw-source,
    #bw .bw-flag {
        position: absolute;
        z-index: 1;
        max-width: calc(100% - 1rem);
        padding: 0.28rem 0.6rem;
        border-radius: 999px;
        background-color: rgba(7, 10, 20, 0.78);
        color: #ffffff;
        font-size: 0.72rem;
        font-weight: 700;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        -webkit-backdrop-filter: blur(6px);
        backdrop-filter: blur(6px);
    }
    #bw .bw-live { top: 0.5rem; inset-inline-start: 0.5rem; display: inline-flex; align-items: center; gap: 0.4rem; }
    #bw .bw-live i {
        flex: none;
        width: 0.45rem;
        height: 0.45rem;
        border-radius: 50%;
        background-color: #4ade80;
        animation: bw-blink 1.6s ease-in-out infinite;
    }
    @keyframes bw-blink { 50% { opacity: 0.35; } }
    #bw .bw-source { bottom: 0.5rem; inset-inline-start: 0.5rem; display: block; font-weight: 600; }
    #bw .bw-flag { top: 0.5rem; inset-inline-start: 0.5rem; background-color: #b45309; }

    /* --- Its caption --- */
    /* Its words stand on the wall, never on its light: the light stops short of the poster's
       foot, and the caption is drawn over whatever of it is left. */
    #bw .bw-cap { position: relative; z-index: 1; padding: 0.8rem 0.1rem 0; }
    #bw .bw-when {
        display: flex;
        flex-wrap: wrap;
        align-items: baseline;
        column-gap: 0.45rem;
        font-family: var(--bw-display);
        font-size: 0.8125rem;
        font-weight: 700;
        line-height: 1.3;
        color: var(--bw-blue);
    }
    #bw .bw-when span { color: var(--bw-ink-3); font-weight: 600; }
    #bw .bw-name {
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
        margin-top: 0.2rem;
        color: var(--bw-ink);
        font-size: 0.9375rem;
        font-weight: 650;
        line-height: 1.25;
        overflow-wrap: anywhere;
        transition: color 0.2s ease;
    }
    #bw .bw-place,
    #bw .bw-who,
    #bw .bw-who a {
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
    #bw .bw-place,
    #bw .bw-who,
    #bw .bw-more-of { font-size: 0.8125rem; line-height: 1.45; }
    #bw .bw-place { margin-top: 0.3rem; color: var(--bw-ink-2); }
    #bw .bw-place span { color: var(--bw-ink-3); }
    #bw .bw-type .bw-place { margin-top: 0; }
    #bw .bw-who { color: var(--bw-ink-3); }
    /* Above the poster's own link, so the schedule's name goes to the schedule. */
    #bw .bw-who a { position: relative; z-index: 3; display: block; }
    #bw .bw-who a:hover { color: var(--bw-blue); text-decoration: underline; text-underline-offset: 3px; }
    #bw .bw-more-of a {
        position: relative;
        z-index: 3;
        display: inline-flex;
        align-items: center;
        min-height: 1.75rem;
        color: var(--bw-blue);
        font-weight: 650;
        white-space: nowrap;
    }
    /* A finger's worth of target around four small words. */
    #bw .bw-more-of a::after { content: ""; position: absolute; inset: -0.5rem -0.4rem; }
    #bw .bw-more-of a:hover { text-decoration: underline; text-underline-offset: 3px; }
    @media (min-width: 1024px) {
        #bw .bw-name { font-size: 1rem; }
        #bw .bw-when { font-size: 0.84rem; }
    }

    /* The platform admin's own control, beside the caption rather than over the flyer. */
    #bw .bw-admin { position: relative; z-index: 3; display: flex; align-items: center; gap: 0.5rem; margin-top: 0.45rem; }
    #bw .bw-admin span { color: #b45309; font-size: 0.75rem; font-weight: 700; }
    .dark #bw .bw-admin span { color: #fbbf24; }
    #bw .bw-admin button {
        padding: 0.3rem 0.75rem;
        border: 1px solid var(--bw-line-2);
        border-radius: 999px;
        background-color: var(--bw-bg-2);
        color: var(--bw-ink);
        font-size: 0.75rem;
        font-weight: 700;
    }
    #bw .bw-admin button:hover { border-color: var(--bw-blue); }
    #bw .bw-admin button.is-restore { border-color: #15803d; background-color: #15803d; color: #ffffff; }

    /* --- A poster set in type, for an event with no flyer: paper by day, lit by night --- */
    #bw .bw-face {
        --paper: #e3eaff;
        --ink: #17367c;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 6cqi;
        padding: 8cqi;
        background-color: var(--paper);
        color: var(--ink);
        font-family: var(--bw-display);
    }
    #bw .bw-face::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image: radial-gradient(130% 80% at 0% 0%, rgba(255, 255, 255, 0.55), rgba(255, 255, 255, 0) 60%);
        pointer-events: none;
    }
    .dark #bw .bw-face::before {
        background-image:
            radial-gradient(130% 80% at 0% 0%, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0) 60%),
            linear-gradient(180deg, rgba(0, 0, 0, 0) 45%, rgba(0, 0, 0, 0.3));
    }
    #bw .bw-face > * { position: relative; }
    /* Its own colour is edge enough. */
    #bw .bw-face::after { content: none; }
    #bw .bw-ink-0 { --paper: #b3d3ff; --ink: #0d2f6e; }
    #bw .bw-ink-1 { --paper: #a9e3da; --ink: #083f42; }
    #bw .bw-ink-2 { --paper: #bfe3b4; --ink: #153d20; }
    #bw .bw-ink-3 { --paper: #f6d877; --ink: #4a3300; }
    #bw .bw-ink-4 { --paper: #a3e2ee; --ink: #06404c; }
    #bw .bw-ink-5 { --paper: #efc9a4; --ink: #55230f; }
    .dark #bw .bw-ink-0 { --paper: #1b3f94; --ink: #f4f7ff; }
    .dark #bw .bw-ink-1 { --paper: #0d5a5f; --ink: #effdfb; }
    .dark #bw .bw-ink-2 { --paper: #1e5a33; --ink: #f1fdf3; }
    .dark #bw .bw-ink-3 { --paper: #8a5c00; --ink: #fff8e6; }
    .dark #bw .bw-ink-4 { --paper: #0d5884; --ink: #effaff; }
    .dark #bw .bw-ink-5 { --paper: #7a1f2e; --ink: #fff1f1; }
    #bw .bw-type .bw-clip { box-shadow: var(--bw-shadow), 0 26px 44px -22px var(--paper); }
    .dark #bw .bw-type .bw-clip { box-shadow: var(--bw-shadow), 0 26px 60px -18px var(--paper); }
    #bw .bw-face-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 4cqi; }
    #bw .bw-stamp { display: flex; flex-direction: column; min-width: 0; line-height: 1; }
    #bw .bw-stamp-top {
        font-size: clamp(0.62rem, 5.6cqi, 1rem);
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }
    #bw .bw-stamp-num {
        margin-top: 2cqi;
        font-size: 36cqi;
        font-weight: 800;
        font-variation-settings: 'wght' 860;
        letter-spacing: -0.06em;
        line-height: 0.84;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }
    #bw .bw-stamp-mon { margin-top: 3cqi; font-size: clamp(0.66rem, 5.8cqi, 1.05rem); font-weight: 700; white-space: nowrap; }
    #bw .bw-stamp-mon i { font-style: normal; opacity: 0.55; }
    #bw .bw-face-top img {
        flex: none;
        width: 17cqi;
        height: 17cqi;
        min-width: 1.75rem;
        min-height: 1.75rem;
        border-radius: 50%;
        object-fit: cover;
        background-color: #ffffff;
        box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.85), 0 4px 10px -4px rgba(10, 16, 32, 0.4);
    }
    #bw .bw-face-name {
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 3;
        overflow: hidden;
        padding-bottom: 0.06em;
        font-weight: 800;
        font-variation-settings: 'wght' 820;
        font-size: 14.5cqi;
        line-height: 0.98;
        letter-spacing: -0.035em;
        overflow-wrap: anywhere;
        text-wrap: balance;
    }
    #bw .bw-face-name.bw-len-1 { font-size: 11cqi; line-height: 1.02; -webkit-line-clamp: 4; }
    #bw .bw-face-name.bw-len-2 { font-size: 8.6cqi; line-height: 1.06; -webkit-line-clamp: 5; letter-spacing: -0.02em; }

    /* --- The word a stretch of time opens with: set up the side of its first poster --- */
    #bw .bw-label { flex: 0 0 auto; align-self: flex-start; }
    #bw .bw-label-in {
        display: flex;
        align-items: baseline;
        gap: 0.85rem;
        writing-mode: vertical-rl;
        /* The word before its count, whichever way the page itself reads. */
        direction: ltr;
        white-space: nowrap;
    }
    #bw .bw-label h3 {
        font-family: var(--bw-display);
        font-size: 2.1rem;
        font-weight: 800;
        font-variation-settings: 'wght' 840;
        line-height: 1;
        letter-spacing: -0.04em;
        color: var(--bw-ink);
    }
    /* The count is in the chips beside the search box; set on its side it is too small to read. */
    #bw .bw-label p { display: none; color: var(--bw-ink-3); font-size: 0.85rem; font-weight: 600; }
    @media (min-width: 1440px) { #bw .bw-label h3 { font-size: 2.6rem; } }
    @media (max-width: 639.98px) {
        /* A phone has no room up the side: the word is a heading across the wall. */
        #bw .bw-label { flex: 0 0 100%; margin-bottom: 0.9rem; }
        #bw .bw-label:not(:first-child) { margin-top: 0.6rem; }
        #bw .bw-label-in { writing-mode: horizontal-tb; }
        #bw .bw-label h3 { font-size: 2.1rem; }
        #bw .bw-label p { display: block; }
    }

    /* --- The poster the wall ends on, which nobody has printed yet --- */
    #bw .bw-blank-face {
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        gap: 2.5cqi;
        padding: 9cqi;
        border: 1.5px dashed rgba(36, 86, 214, 0.55);
        background-color: var(--bw-bg-2);
        background-image: radial-gradient(120% 70% at 100% 0%, rgba(47, 102, 234, 0.16), rgba(47, 102, 234, 0) 62%);
        box-shadow: 0 22px 44px -22px rgba(47, 102, 234, 0.55);
        color: var(--bw-ink);
    }
    .dark #bw .bw-blank-face {
        border-color: rgba(141, 176, 255, 0.6);
        background-image: radial-gradient(120% 70% at 100% 0%, rgba(78, 129, 250, 0.34), rgba(78, 129, 250, 0) 62%);
        box-shadow: 0 26px 60px -20px rgba(78, 129, 250, 0.6);
    }
    #bw .bw-blank-face::after { content: none; }
    #bw .bw-blank-k { color: var(--bw-ink-3); font-size: clamp(0.7rem, 6cqi, 0.9rem); font-weight: 600; }
    #bw .bw-blank-t {
        font-family: var(--bw-display);
        font-size: 15cqi;
        font-weight: 800;
        font-variation-settings: 'wght' 830;
        line-height: 0.96;
        letter-spacing: -0.04em;
        text-wrap: balance;
    }
    #bw .bw-blank-go {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        margin-top: 2cqi;
        color: var(--bw-blue);
        font-size: clamp(0.8rem, 6.4cqi, 1rem);
        font-weight: 700;
    }
    #bw .bw-blank-go svg { width: 1.1em; height: 1.1em; transition: transform 0.2s ease; }
    #bw .bw-blank:hover .bw-blank-go svg { transform: translateX(4px); }
    [dir="rtl"] #bw .bw-blank-go svg,
    [dir="rtl"] #bw .bw-blank:hover .bw-blank-go svg { transform: scaleX(-1); }

    #bw .bw-after {
        margin-top: 0.5rem;
        color: var(--bw-ink-3);
        font-size: 0.95rem;
        text-align: center;
    }
    #bw .bw-after a,
    #bw .bw-none a,
    #bw .bw-faq-say a { color: var(--bw-blue); font-weight: 650; text-decoration: underline; text-underline-offset: 3px; }

    /* Nothing on. */
    #bw .bw-empty {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 2rem;
        align-items: center;
        margin-top: 1rem;
    }
    @media (min-width: 640px) { #bw .bw-empty { grid-template-columns: minmax(0, 1fr) 13rem; gap: 3rem; max-width: 50rem; } }
    #bw .bw-empty-sheet {
        aspect-ratio: 3 / 4;
        width: 100%;
        max-width: 13rem;
        border: 1.5px dashed rgba(36, 86, 214, 0.55);
        border-radius: 0.65rem;
        background-color: var(--bw-bg-2);
        background-image: radial-gradient(120% 70% at 100% 0%, rgba(47, 102, 234, 0.16), rgba(47, 102, 234, 0) 62%);
        box-shadow: 0 22px 44px -22px rgba(47, 102, 234, 0.55);
    }
    .dark #bw .bw-empty-sheet { border-color: rgba(141, 176, 255, 0.6); }
    #bw .bw-empty-sheet { display: flex; flex-direction: column; justify-content: flex-end; gap: 0.3rem; padding: 1.1rem; }
    #bw .bw-empty-sheet span { color: var(--bw-ink-3); font-size: 0.8rem; font-weight: 600; }
    #bw .bw-empty-sheet b { font-family: var(--bw-display); font-size: 1.7rem; font-weight: 800; line-height: 0.98; letter-spacing: -0.035em; }
    #bw .bw-empty h3 {
        font-family: var(--bw-display);
        font-size: clamp(1.8rem, 4vw, 2.6rem);
        font-weight: 800;
        line-height: 1.02;
        letter-spacing: -0.035em;
    }
    #bw .bw-empty p { max-width: 28rem; margin: 0.9rem 0 1.5rem; color: var(--bw-ink-2); font-size: 1.0625rem; }

    /* --- From other sites, and the admin's hidden list --- */
    #bw .bw-network,
    #bw .bw-hidden { border-top: 1px solid var(--bw-line); padding-top: 3rem; }
    #bw .bw-head {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1rem 2rem;
        margin-bottom: 2rem;
    }
    #bw .bw-head h2 {
        font-family: var(--bw-display);
        font-size: clamp(1.8rem, 3.4vw, 2.75rem);
        font-weight: 800;
        font-variation-settings: 'wght' 830;
        line-height: 1;
        letter-spacing: -0.035em;
    }
    #bw .bw-head p { max-width: 36rem; margin-top: 0.6rem; color: var(--bw-ink-2); }
    #bw .bw-head-count { color: var(--bw-ink-3); font-weight: 600; }
    #bw .bw-filters { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; }
    #bw .bw-filters select {
        min-height: 2.75rem;
        padding: 0 2.25rem 0 0.9rem;
        border: 1px solid var(--bw-line-2);
        border-radius: 0.8rem;
        background-color: var(--bw-bg-2);
        color: var(--bw-ink);
        /* Sixteen pixels: an iPhone zooms the page on a smaller field. */
        font-size: 1rem;
        font-weight: 600;
    }
    #bw .bw-more { margin-top: 2rem; text-align: center; }
    #bw .bw-none { color: var(--bw-ink-2); font-size: 1.0625rem; }
    #bw .bw-none a { margin-inline-start: 0.5rem; }

    /* ==============================================================
       3. Questions
       ============================================================== */
    #bw .bw-faq { border-top: 1px solid var(--bw-line); padding: 4rem 0; }
    @media (min-width: 1024px) { #bw .bw-faq { padding: 6rem 0; } }
    #bw .bw-faq-in { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2rem; }
    @media (min-width: 1024px) {
        #bw .bw-faq-in { grid-template-columns: minmax(0, 0.8fr) minmax(0, 1.4fr); gap: 4rem; }
        #bw .bw-faq-say { position: sticky; top: 6rem; align-self: start; }
    }
    #bw .bw-faq-say h2 {
        font-family: var(--bw-display);
        font-size: clamp(2.1rem, 4.4vw, 3.4rem);
        font-weight: 800;
        font-variation-settings: 'wght' 830;
        line-height: 0.98;
        letter-spacing: -0.04em;
    }
    #bw .bw-faq-say p { margin-top: 1.25rem; }
    #bw .bw-faq-list details { border-bottom: 1px solid var(--bw-line); }
    #bw .bw-faq-list details:first-child { border-top: 1px solid var(--bw-line); }
    #bw .bw-faq-list summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.25rem 0.25rem;
        cursor: pointer;
    }
    #bw .bw-faq-list h3 { font-size: 1.125rem; font-weight: 650; line-height: 1.35; transition: color 0.2s ease; }
    #bw .bw-faq-list summary:hover h3 { color: var(--bw-blue); }
    #bw .bw-faq-list svg { flex: none; width: 1.25rem; height: 1.25rem; color: var(--bw-ink-3); transition: transform 0.25s ease; }
    #bw .bw-faq-list details[open] svg { transform: rotate(180deg); }
    #bw .bw-faq-list .faq-answer { max-width: 44rem; padding: 0 0.25rem 1.4rem; color: var(--bw-ink-2); line-height: 1.65; }

    /* "Keep exploring", on this page's ground. */
    #bw .bw-keep > section { border-top-color: var(--bw-line); background-color: transparent; }

    /* ==============================================================
       4. For organizers: the wall at night, in both modes.
       ============================================================== */
    #bw .bw-end { padding: 1rem 0 4rem; }
    @media (min-width: 1024px) { #bw .bw-end { padding: 2rem 0 6rem; } }
    #bw .bw-end-panel {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 2.5rem;
        align-items: center;
        padding: 2.5rem 1.5rem;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 2rem;
        background-color: #070a14;
        background-image:
            radial-gradient(48rem 26rem at 82% 0%, rgba(78, 129, 250, 0.3), rgba(78, 129, 250, 0) 70%),
            radial-gradient(40rem 22rem at 0% 100%, rgba(34, 211, 238, 0.1), rgba(34, 211, 238, 0) 70%),
            linear-gradient(180deg, #0c1222, #070a14);
        color: #eef2ff;
        box-shadow: 0 40px 80px -40px rgba(7, 10, 20, 0.7);
    }
    @media (min-width: 640px) { #bw .bw-end-panel { padding: 3.5rem 3rem; } }
    @media (min-width: 1024px) {
        #bw .bw-end-panel { grid-template-columns: minmax(0, 1.35fr) minmax(0, 0.65fr); gap: 4rem; padding: 5rem 4.5rem; }
    }
    #bw .bw-end-kicker { color: #8db0ff; font-family: var(--bw-display); font-size: 0.95rem; font-weight: 700; }
    #bw .bw-end-say h2 {
        margin-top: 0.9rem;
        font-family: var(--bw-display);
        font-size: clamp(2.1rem, 4.6vw, 3.75rem);
        font-weight: 800;
        font-variation-settings: 'wght' 830;
        line-height: 0.98;
        letter-spacing: -0.04em;
        color: #ffffff;
        text-wrap: balance;
    }
    #bw .bw-end-panel .bw-lit { background-image: linear-gradient(92deg, #8db0ff, #5cc4f5 60%, #67e8f9); }
    #bw .bw-end-lede { max-width: 38rem; margin-top: 1.25rem; color: #c5cde2; font-size: 1.0625rem; line-height: 1.6; }
    #bw .bw-claim-row { display: flex; flex-direction: column; gap: 0.75rem; max-width: 40rem; margin-top: 2rem; }
    @media (min-width: 640px) { #bw .bw-claim-row { flex-direction: row; } }
    #bw .bw-claim {
        display: flex;
        flex: 1 1 auto;
        align-items: center;
        min-width: 0;
        padding: 1rem 1.1rem;
        border: 1px solid rgba(255, 255, 255, 0.16);
        border-radius: 1rem;
        background-color: rgba(255, 255, 255, 0.07);
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    #bw .bw-claim:focus-within { border-color: rgba(141, 176, 255, 0.8); box-shadow: 0 0 0 4px rgba(141, 176, 255, 0.22); }
    #bw .bw-claim input {
        flex: 1 1 auto;
        min-width: 0;
        border: 0;
        background-color: transparent;
        padding-inline: 0;
        color: #ffffff;
        font-family: ui-monospace, 'SF Mono', SFMono-Regular, Menlo, Consolas, monospace;
        font-size: 1rem;
        font-weight: 600;
        text-align: right;
    }
    #bw .bw-claim input::placeholder { color: #97a3c0; opacity: 1; }
    #bw .bw-claim input:focus { outline: none; box-shadow: none; }
    #bw .bw-claim span {
        flex: none;
        color: #97a3c0;
        font-family: ui-monospace, 'SF Mono', SFMono-Regular, Menlo, Consolas, monospace;
        font-size: 0.95rem;
        user-select: none;
    }
    @media (max-width: 639.98px) {
        /* The address under the name, so a name of any length is all there as it is typed. */
        #bw .bw-claim { flex-direction: column; align-items: stretch; gap: 0.15rem; padding-block: 0.8rem; }
        #bw .bw-claim input { margin-block: 0; padding-block: 0; text-align: start; }
        #bw .bw-claim span { font-size: 0.85rem; }
    }
    #bw .bw-btn-lit { background-image: linear-gradient(135deg, #a9c4ff, #7fa6ff); color: #070a14; box-shadow: 0 14px 34px -14px rgba(141, 176, 255, 0.7); }
    #bw .bw-btn-lit:hover { box-shadow: 0 20px 40px -14px rgba(141, 176, 255, 0.85); }
    #bw .bw-end-note { margin-top: 1.25rem; color: #97a3c0; font-size: 0.9rem; }
    #bw .bw-end-note a { color: #a9c4ff; font-weight: 650; }
    #bw .bw-end-note a:hover { text-decoration: underline; text-underline-offset: 3px; }
    #bw .bw-end-panel a:focus-visible { outline-color: #a9c4ff; }

    /* The poster that is not printed yet. It takes the name typed beside it. */
    #bw .bw-end-show { position: relative; justify-self: center; width: min(62%, 15rem); }
    @media (min-width: 1024px) { #bw .bw-end-show { width: min(100%, 17rem); } }
    #bw .bw-end-halo {
        position: absolute;
        inset: 8% -6% -10%;
        border-radius: 2rem;
        background-image: linear-gradient(160deg, #4e81fa, #22d3ee);
        opacity: 0.45;
        filter: blur(34px);
    }
    #bw .bw-end-sheet {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 1rem;
        aspect-ratio: 3 / 4;
        /* In rem: a container's own padding cannot be measured against itself. */
        padding: 1.35rem;
        border: 1.5px dashed rgba(169, 196, 255, 0.7);
        border-radius: 0.8rem;
        background-color: #131c33;
        background-image: radial-gradient(120% 70% at 100% 0%, rgba(78, 129, 250, 0.4), rgba(78, 129, 250, 0) 62%);
        transform: rotate(2.2deg);
        transition: border-color 0.4s ease, background-color 0.4s ease, transform 0.5s cubic-bezier(0.2, 0.7, 0.2, 1);
        container-type: inline-size;
    }
    #bw .bw-end-sheet.is-named {
        border-style: solid;
        border-color: rgba(255, 255, 255, 0.2);
        background-image: linear-gradient(160deg, #2f66ea, #0b84c9 70%, #0a9db5);
        transform: rotate(-1.2deg);
    }
    #bw .bw-end-when { color: #a9c4ff; font-family: var(--bw-display); font-size: 0.85rem; font-weight: 700; }
    #bw .bw-end-sheet.is-named .bw-end-when { color: #ffffff; }
    #bw .bw-end-name {
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 4;
        overflow: hidden;
        padding-bottom: 0.06em;
        font-family: var(--bw-display);
        font-size: 15cqi;
        font-weight: 800;
        font-variation-settings: 'wght' 840;
        line-height: 0.98;
        letter-spacing: -0.04em;
        color: #ffffff;
        overflow-wrap: anywhere;
    }
    #bw .bw-end-sheet.is-named .bw-end-name { text-transform: capitalize; }
    #bw .bw-end-url {
        padding-top: 0.8rem;
        border-top: 1px solid rgba(255, 255, 255, 0.24);
        color: #c5cde2;
        font-family: ui-monospace, 'SF Mono', SFMono-Regular, Menlo, Consolas, monospace;
        font-size: clamp(0.56rem, 4.3cqi, 0.72rem);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    #bw .bw-end-url b { color: #ffffff; font-weight: 700; }

    @media (prefers-reduced-motion: reduce) {
        #bw .bw-pulse::after,
        #bw .bw-live i { animation: none; }
        #bw .bw-clip,
        #bw .bw-glow,
        #bw .bw-frame,
        #bw .bw-btn,
        #bw .bw-ghost,
        #bw .bw-jump a,
        #bw .bw-ambient img,
        #bw .bw-end-sheet { transition: none; }
        #bw .bw-poster:hover .bw-clip,
        #bw .bw-poster:focus-within .bw-clip,
        #bw .bw-poster:hover .bw-glow,
        #bw .bw-btn:hover { transform: none; }
    }
</style>
