{{--
    The house style: the homepage's design language (2026-10), for every other marketing page
    that asks for it with :hp="true" on the marketing layout. The layout then wraps the page in
    a div with the id "hp" and prints this once, so a page writes no CSS of its own to join in.

    (Component names are written here without their angle brackets on purpose: a test finds
    the pages that use a component by looking for its tag in every view's source.)

    Three layers, all scoped under #hp so nothing leaks into the header, the footer or a page
    that has not opted in:

    1. Tokens. The page's own (--hp-*), and the site's grey ramp re-pointed at them: Tailwind's
       gray-50 to gray-900 resolve through --ap-gray-* (tailwind.config.js), so setting those
       here moves every bg-gray-*, text-gray-* and border-gray-* on the page from neutral grey
       to this palette's cool paper and navy, without touching the markup.
    2. The adapter. The shared marketing components are written in utilities; a few of those
       are literals a variable cannot reach (the dark grounds, the gradient buttons, the page
       gradients), and are restated here.
    3. The kit. The homepage's own pieces (hp-*), for the sections a page writes itself.

    The homepage still carries its own copy of the tokens and the kit inline, because it was
    built first and its stylesheet holds its one-off set pieces too. A rule changed here that
    the homepage also has (anything in "The kit" below) wants changing there as well, until
    the homepage is moved onto this file.

    Every note is a Blade comment, so none of this reaches a visitor.
--}}
<style {!! nonce_attr() !!}>
    {{-- ==============================================================
       1. Tokens
       ============================================================== --}}
    #hp {
        --hp-bg: #f4f6fb;
        --hp-bg-2: #ffffff;
        --hp-bg-3: #e9edf6;
        --hp-ink: #0a1020;
        --hp-ink-2: #36405a;
        --hp-ink-3: #56617c;
        --hp-line: rgba(10, 16, 32, 0.1);
        --hp-line-2: rgba(10, 16, 32, 0.17);
        --hp-blue: #2f66ea;
        --hp-glow: rgba(78, 129, 250, 0.22);
        --hp-grad: linear-gradient(100deg, #2f66ea 0%, #0b8fd8 55%, #0aa5c4 100%);
        --hp-grad-lit: linear-gradient(100deg, #7da5ff 0%, #38bdf8 55%, #67e8f9 100%);
        --hp-card-shadow: 0 1px 2px rgba(10, 16, 32, 0.05), 0 18px 40px -22px rgba(10, 16, 32, 0.28);
        --hp-pop-shadow: 0 2px 4px rgba(10, 16, 32, 0.06), 0 28px 60px -24px rgba(10, 16, 32, 0.4);
        --hp-display: 'Red Hat Display', 'Inter', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        --hp-mono: ui-monospace, 'SF Mono', SFMono-Regular, Menlo, Consolas, 'Liberation Mono', monospace;

        --ap-gray-50: 244 246 251;
        --ap-gray-100: 236 240 248;
        --ap-gray-200: 221 227 239;
        --ap-gray-300: 197 205 226;
        {{-- 400 keeps the old grey's lightness: it is mostly asked for on grounds that are dark in
           both modes (a claim box's ending, a caption on a night band), where a darker one fell
           under 4.5:1. --}}
        --ap-gray-400: 156 163 180;
        --ap-gray-500: 86 97 124;
        --ap-gray-600: 54 64 90;
        --ap-gray-700: 38 47 72;
        --ap-gray-800: 22 29 50;
        --ap-gray-900: 10 16 32;

        position: relative;
        background: var(--hp-bg);
        color: var(--hp-ink);
        font-family: var(--hp-display);
    }
    .dark #hp {
        --hp-bg: #070a14;
        --hp-bg-2: #0e1424;
        --hp-bg-3: #0a0f1c;
        --hp-ink: #eef2ff;
        --hp-ink-2: #c5cde2;
        --hp-ink-3: #97a3c0;
        --hp-line: rgba(255, 255, 255, 0.09);
        --hp-line-2: rgba(255, 255, 255, 0.17);
        --hp-blue: #8db0ff;
        --hp-glow: rgba(78, 129, 250, 0.34);
        --hp-grad: linear-gradient(100deg, #7da5ff 0%, #38bdf8 55%, #5eead4 100%);
        --hp-card-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05), 0 24px 50px -26px rgba(0, 0, 0, 0.9);
        --hp-pop-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.07), 0 30px 70px -24px rgba(0, 0, 0, 0.95);

        {{-- In dark mode the ramp is asked for text (300, 400) and, rarely, for a surface (800, 900). --}}
        --ap-gray-300: 197 205 226;
        --ap-gray-400: 151 163 192;
        --ap-gray-700: 38 48 78;
        --ap-gray-800: 20 27 48;
        --ap-gray-900: 14 20 36;
    }

    {{-- The bar above takes the page's ground, so the hero starts at the top of the window. --}}
    body:has(#hp) > header.sticky {
        background-color: rgba(244, 246, 251, 0.9);
        border-bottom-color: rgba(10, 16, 32, 0.08);
    }
    .dark body:has(#hp) > header.sticky {
        background-color: rgba(7, 10, 20, 0.88);
        border-bottom-color: rgba(255, 255, 255, 0.08);
    }

    #hp ::selection { background: rgba(78, 129, 250, 0.32); }
    #hp a:focus-visible,
    #hp summary:focus-visible,
    #hp button:focus-visible {
        outline: 3px solid var(--hp-blue);
        outline-offset: 3px;
    }
    #hp section[id] { scroll-margin-top: 4.5rem; }

    {{-- ==============================================================
       2. The adapter: shared markup, written in utilities, in this palette
       ============================================================== --}}

    {{-- One family. The bundled file is variable but is declared at 400 and 700 only, so a
       heavier or an in-between weight is asked for by its axis. The axis is inherited, which is
       why the lighter classes restate it: a "font-normal" child of a heavy heading would
       otherwise stay heavy. --}}
    #hp .font-black { font-weight: 700; font-variation-settings: 'wght' 850; }
    #hp .font-extrabold { font-weight: 700; font-variation-settings: 'wght' 800; }
    #hp .font-bold,
    #hp strong,
    #hp b { font-weight: 700; font-variation-settings: 'wght' 720; }
    #hp .font-semibold { font-weight: 700; font-variation-settings: 'wght' 630; }
    #hp .font-medium { font-weight: 400; font-variation-settings: 'wght' 520; }
    #hp .font-normal { font-weight: 400; font-variation-settings: 'wght' 400; }
    #hp .font-mono,
    #hp code,
    #hp pre,
    #hp kbd,
    #hp samp { font-family: var(--hp-mono); font-variation-settings: normal; }
    #hp h1,
    #hp h2 { letter-spacing: -0.04em; text-wrap: balance; }
    #hp h3,
    #hp h4 { letter-spacing: -0.02em; }

    {{-- A section that is dark in both modes by its hex takes the night's navy. --}}
    #hp .bg-\[\#0a0a0f\] { background-color: #050814; }

    {{-- The four dark grounds the pages name by their hex. --}}
    .dark #hp .dark\:bg-\[\#0a0a0f\] { background-color: var(--hp-bg); }
    .dark #hp .dark\:bg-\[\#0f0f14\] { background-color: var(--hp-bg-3); }
    .dark #hp .dark\:bg-\[\#15151c\],
    .dark #hp .dark\:bg-\[\#101016\] { background-color: var(--hp-bg-2); }

    {{-- Each page had a gradient of its own for the lit phrase of a heading. Here it is one
       gradient, deep enough to read as type on paper; on a ground that is dark in both modes
       it is the lit one. --}}
    #hp .text-gradient,
    #hp .text-gradient-features,
    #hp .text-gradient-pricing,
    #hp .text-gradient-usecases,
    #hp .text-gradient-docs { background-image: var(--hp-grad); padding-inline-end: 0.05em; }
    {{-- /selfhost keeps its own emerald (text-gradient-selfhost): on that page green means
       "included, free, yours", and amber "yours to run". Those are its words, not decoration. --}}
    #hp .es-band-dark [class*="text-gradient"]:not(.text-gradient-selfhost),
    #hp .es-finale-panel [class*="text-gradient"]:not(.text-gradient-selfhost),
    #hp .hp-dark [class*="text-gradient"]:not(.text-gradient-selfhost),
    #hp .hp-dark .hp-ink-grad,
    #hp .hp-finale .hp-ink-grad { background-image: var(--hp-grad-lit); }

    {{-- The main button: flat and deep enough for white type at every point along it (the
       blue-to-sky gradient fell to 4.1:1 at its light end). --}}
    #hp a.bg-gradient-to-r.from-blue-600,
    #hp a.bg-gradient-to-r.from-blue-500,
    #hp button.bg-gradient-to-r.from-blue-600 {
        background-image: linear-gradient(100deg, #2b5fe3, #2f6fe9);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.28), 0 14px 34px -12px rgba(47, 102, 234, 0.75), 0 0 0 1px rgba(47, 102, 234, 0.5);
    }
    #hp a.bg-gradient-to-r.from-blue-600:hover,
    #hp button.bg-gradient-to-r.from-blue-600:hover {
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.28), 0 22px 44px -14px rgba(47, 102, 234, 0.9), 0 0 0 1px rgba(47, 102, 234, 0.6), 0 0 44px -6px rgba(34, 211, 238, 0.55);
    }
    #hp .animate-shimmer { display: none; }

    {{-- Frosted pills and second buttons become plain paper with a hairline. On a ground that is
       dark in both modes they stay glass: paper there would be a white pill under white type. --}}
    #hp .glass {
        border: 1px solid var(--hp-line-2);
        background: var(--hp-bg-2);
        box-shadow: var(--hp-card-shadow);
        -webkit-backdrop-filter: none;
        backdrop-filter: none;
    }
    #hp :is(.es-band-dark, .es-finale-panel, .hp-dark, .hp-finale, .bg-\[\#0a0a0f\], .bg-\[\#0f0f14\], .bg-gray-900, .bg-black) .glass {
        border: 1px solid rgba(255, 255, 255, 0.14);
        background: rgba(255, 255, 255, 0.06);
        box-shadow: none;
    }

    {{-- The old ground effects (drifting blobs, rays, film grain) are not this page's light. --}}
    #hp .es-aurora,
    #hp .es-rays { display: none; }
    #hp .noise::before,
    #hp .noise::after { display: none; }

    {{-- The dots down the right edge: the homepage dropped them, and so do the pages that follow it. --}}
    #hp .es-dotnav { display: none !important; }

    {{-- Fixed-dark bands (dark in both modes) take the homepage's night. Inside a run
       (.hp-dark.is-run, which draws the ground once) they are clear. --}}
    #hp .es-band-dark {
        background:
            radial-gradient(52rem 30rem at 72% 0%, rgba(47, 102, 234, 0.34), transparent 70%),
            radial-gradient(34rem 22rem at 10% 100%, rgba(34, 211, 238, 0.14), transparent 70%),
            #050814;
    }
    #hp .hp-dark .es-band-dark { background: transparent; }

    {{-- ---- the feature-chapter component ---- --}}
    #hp .es-chapter { padding-top: clamp(4rem, 9vw, 8rem); padding-bottom: clamp(0.5rem, 2vw, 1.5rem); background: transparent; }
    #hp .es-chapter-label {
        font-family: var(--hp-mono);
        font-size: 0.78rem;
        font-weight: 700;
        font-variation-settings: normal;
        letter-spacing: 0.14em;
    }
    #hp .es-chapter-title { font-size: clamp(2.3rem, 3.8vw + 1rem, 4.6rem); line-height: 0.98; font-variation-settings: 'wght' 850; letter-spacing: -0.045em; }
    #hp .es-chapter-lede { max-width: 38rem; margin-top: 1.1rem; font-size: clamp(1.1rem, 0.5vw + 1rem, 1.3rem); line-height: 1.55; color: var(--hp-ink-2); }
    #hp .es-chapter-num { font-size: clamp(8rem, 21vw, 19rem); font-variation-settings: 'wght' 900; letter-spacing: -0.07em; line-height: 0.8; opacity: 0.1; }
    .dark #hp .es-chapter-num { opacity: 0.16; }
    @media (max-width: 639px) {
        {{-- On a phone the numeral stands over the label, clear of the title it used to run behind. --}}
        #hp .es-chapter-num { top: 0; transform: none; font-size: 6.5rem; }
    }
    #hp .es-band-dark .es-chapter-lede { color: #c5cde2; }

    {{-- ---- the feature-banner component ----
       The words on one side, and what they are about standing on a stage on the other, as the
       features of the homepage's three acts do. The stage is the wide column; the object keeps the
       width it was drawn at and stands in the middle of it. --}}
    #hp .es-banner { background: transparent; }
    #hp .es-banner-row { align-items: stretch; }
    #hp .es-banner-copy { align-self: center; }
    #hp .es-banner-mock {
        position: relative;
        display: grid;
        place-items: center;
        max-width: none;
        padding: clamp(1.25rem, 3.2vw, 3rem);
        border: 1px solid var(--hp-line);
        border-radius: 1.9rem;
        background:
            radial-gradient(34rem 22rem at 78% 0%, var(--hp-glow), transparent 70%),
            var(--hp-bg-2);
        box-shadow: var(--hp-card-shadow);
        overflow: hidden;
    }
    #hp .es-banner-mock::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(var(--hp-line) 1px, transparent 1px),
            linear-gradient(90deg, var(--hp-line) 1px, transparent 1px);
        background-size: calc(100% / 7) 5.5rem;
        -webkit-mask-image: linear-gradient(160deg, #000, transparent 62%);
        mask-image: linear-gradient(160deg, #000, transparent 62%);
        opacity: 0.8;
        pointer-events: none;
    }
    #hp .es-banner-mock > a { position: relative; width: 100%; max-width: 34rem; }
    #hp .es-banner-mock.is-lead > a { max-width: 38rem; }
    #hp .es-banner-mock.es-shot-col > a { max-width: none; }
    #hp .es-banner-mock .es-tilt-inner { box-shadow: var(--hp-pop-shadow); }
    @media (min-width: 1024px) {
        #hp .es-banner-copy { flex: 0 1 29rem; }
        #hp .es-banner-mock { flex: 1 1 0; width: auto; min-height: clamp(21rem, 27vw, 27rem); }
        #hp .es-banner-mock.es-shot-col { flex: 1.25 1 0; }
    }
    #hp .es-banner-heading { letter-spacing: -0.04em; line-height: 1.02; }
    #hp .es-banner-lede { color: var(--hp-ink-2); }
    #hp .es-banner-chip { border-color: var(--hp-line-2); background: transparent; font-size: 0.82rem; color: var(--hp-ink-2); }
    #hp .es-band-dark .es-banner-mock { border-color: rgba(125, 165, 255, 0.2); background: radial-gradient(34rem 22rem at 78% 0%, rgba(78, 129, 250, 0.3), transparent 70%), #0b1124; box-shadow: none; }
    #hp .es-band-dark .es-banner-lede { color: #c5cde2; }
    #hp .es-band-dark .es-banner-chip { border-color: rgba(255, 255, 255, 0.16); color: #c5cde2; }

    {{-- ---- the related-pages strip and the audience-card component ---- --}}
    #hp .es-related-eyebrow {
        font-family: var(--hp-mono);
        font-size: 0.78rem;
        font-variation-settings: normal;
        letter-spacing: 0.14em;
        color: var(--hp-ink-3);
    }
    #hp .es-related-title { font-size: clamp(1.6rem, 1.4vw + 1.05rem, 2.25rem); font-variation-settings: 'wght' 800; }
    #hp .es-related-card,
    #hp .es-aud-card { border-color: var(--hp-line); border-radius: 1.25rem; background: var(--hp-bg-2); box-shadow: none; }
    #hp .es-related-card:hover,
    #hp .es-aud-card:hover { border-color: var(--hp-blue); box-shadow: var(--hp-card-shadow); }

    {{-- ==============================================================
       3. The kit: the homepage's own pieces
       ============================================================== --}}
    .hp-wrap { width: min(100% - 2.5rem, 76rem); margin-inline: auto; }
    .hp-wrap.is-narrow { width: min(100% - 2.5rem, 58rem); }
    .hp-sec { position: relative; padding-block: clamp(4.5rem, 9vw, 8.5rem); }
    .hp-sec.is-tight { padding-block: clamp(2.75rem, 5vw, 4.5rem); }
    .hp-alt { background: var(--hp-bg-2); }
    .dark .hp-alt { background: var(--hp-bg-3); }

    #hp .hp-h1,
    #hp .hp-h2,
    #hp .hp-h3,
    #hp .hp-num {
        font-weight: 700;
        letter-spacing: -0.04em;
        text-wrap: balance;
        color: inherit;
    }
    #hp .hp-h1 { font-variation-settings: 'wght' 840; font-size: clamp(2.45rem, 10.4vw, 4.25rem); line-height: 1.02; }
    #hp .hp-h2 { font-variation-settings: 'wght' 820; font-size: clamp(2rem, 3.1vw + 0.9rem, 3.6rem); line-height: 1.04; }
    #hp .hp-h3 { font-variation-settings: 'wght' 790; font-size: clamp(1.6rem, 1.4vw + 1.05rem, 2.25rem); line-height: 1.08; letter-spacing: -0.035em; }
    @media (min-width: 1024px) {
        #hp .hp-h1 { font-size: min(5.4vw, 5.6rem); line-height: 1; }
    }
    .hp-lead { font-size: clamp(1.1rem, 0.5vw + 1rem, 1.3rem); line-height: 1.55; color: var(--hp-ink-2); text-wrap: pretty; }
    .hp-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        font-family: var(--hp-mono);
        font-size: 0.78rem;
        font-weight: 700;
        font-variation-settings: normal;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: var(--hp-ink-3);
    }
    .hp-kicker::before { content: ""; width: 0.5rem; height: 0.5rem; border-radius: 0.15rem; background: linear-gradient(135deg, #4e81fa, #22d3ee); }
    .hp-ink-grad {
        background: var(--hp-grad);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        padding-inline-end: 0.06em;
    }
    .hp-head { max-width: 46rem; }
    .hp-head.is-center { margin-inline: auto; text-align: center; }
    .hp-head .hp-h2 { margin-top: 1rem; }
    .hp-head .hp-lead { margin-top: 1.1rem; }

    {{-- Buttons and links --}}
    .hp-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.6rem;
        min-height: 3.6rem;
        padding: 0 1.7rem;
        border-radius: 1rem;
        font-size: 1.0625rem;
        font-weight: 700;
        font-variation-settings: 'wght' 700;
        letter-spacing: -0.01em;
        white-space: nowrap;
        transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease, border-color 0.2s ease;
    }
    .hp-btn.is-small { min-height: 3rem; padding-inline: 1.25rem; font-size: 0.98rem; border-radius: 0.85rem; }
    .hp-btn svg { width: 1.15rem; height: 1.15rem; transition: transform 0.2s ease; }
    .hp-btn:hover svg { transform: translateX(3px); }
    .hp-btn.is-down:hover svg { transform: translateY(3px); }
    .hp-btn.is-still:hover svg { transform: none; }
    [dir="rtl"] #hp .hp-btn:not(.is-down) svg,
    [dir="rtl"] #hp .hp-more svg { transform: scaleX(-1); }
    .hp-btn-primary {
        color: #fff;
        background: linear-gradient(100deg, #2b5fe3, #2f6fe9);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.28), 0 14px 34px -12px rgba(47, 102, 234, 0.75), 0 0 0 1px rgba(47, 102, 234, 0.5);
    }
    .hp-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.28), 0 22px 44px -14px rgba(47, 102, 234, 0.9), 0 0 0 1px rgba(47, 102, 234, 0.6), 0 0 44px -6px rgba(34, 211, 238, 0.55);
    }
    .hp-btn-ghost { color: var(--hp-ink); background: var(--hp-bg-2); border: 1px solid var(--hp-line-2); box-shadow: var(--hp-card-shadow); }
    .hp-btn-ghost:hover { transform: translateY(-2px); border-color: var(--hp-blue); }
    .hp-more { display: inline-flex; align-items: center; gap: 0.35rem; min-height: 1.75rem; font-weight: 700; font-variation-settings: 'wght' 700; color: var(--hp-blue); transition: gap 0.2s ease; }
    .hp-more:hover { gap: 0.6rem; }
    .hp-more svg { width: 1rem; height: 1rem; }
    .hp-inline { font-weight: 700; font-variation-settings: 'wght' 700; color: var(--hp-blue); text-decoration: underline; text-decoration-thickness: 1px; text-underline-offset: 0.2em; }
    .hp-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 1rem 1.5rem; margin-top: 2.25rem; }

    {{-- Hero: the headline alone on the page's sky. --}}
    #hp .hp-hero {
        --hp-col: 40rem;
        position: relative;
        isolation: isolate;
        overflow: hidden;
        overflow: clip;
        padding-block: clamp(3.25rem, 9vh, 6.5rem) clamp(3.25rem, 8vh, 6rem);
    }
    @media (min-width: 1024px) {
        #hp .hp-hero { --hp-col: min(64vw, 66rem); }
    }
    {{-- A page whose first screen is its content (plans, a directory) keeps the hero short. --}}
    #hp .hp-hero.is-short { padding-block: clamp(2.75rem, 7vh, 5rem) clamp(1.5rem, 3vh, 2.5rem); }
    .hp-hero-sky {
        position: absolute;
        inset: 0;
        z-index: 0;
        pointer-events: none;
        {{-- The sky thins out before the section ends, so its light is never cut off by a straight edge. --}}
        -webkit-mask-image: linear-gradient(to bottom, #000 55%, transparent 100%);
        mask-image: linear-gradient(to bottom, #000 55%, transparent 100%);
        background:
            radial-gradient(62rem 34rem at 50% -9rem, rgba(78, 129, 250, 0.2), transparent 70%),
            radial-gradient(36rem 26rem at 8% 18rem, rgba(34, 211, 238, 0.14), transparent 70%),
            radial-gradient(36rem 26rem at 92% 22rem, rgba(14, 165, 233, 0.13), transparent 70%);
    }
    .dark .hp-hero-sky {
        background:
            radial-gradient(62rem 34rem at 50% -9rem, rgba(78, 129, 250, 0.34), transparent 70%),
            radial-gradient(36rem 26rem at 8% 18rem, rgba(34, 211, 238, 0.14), transparent 70%),
            radial-gradient(36rem 26rem at 92% 22rem, rgba(14, 165, 233, 0.16), transparent 70%);
    }
    {{-- A week of seven columns behind the headline: the product is a calendar. --}}
    .hp-hero-sky::after {
        content: "";
        position: absolute;
        inset: 0 0 auto 0;
        height: 46rem;
        background-image:
            linear-gradient(var(--hp-line) 1px, transparent 1px),
            linear-gradient(90deg, var(--hp-line) 1px, transparent 1px);
        background-size: calc(var(--hp-col) / 7) 6.5rem;
        background-position: 50% 0;
        -webkit-mask-image: radial-gradient(ellipse 34rem 24rem at 50% 15rem, #000 10%, transparent 72%);
        mask-image: radial-gradient(ellipse 34rem 24rem at 50% 15rem, #000 10%, transparent 72%);
    }
    .hp-hero-copy { position: relative; z-index: 10; width: min(100% - 2.5rem, var(--hp-col)); margin-inline: auto; text-align: center; }
    #hp .es-hero-eyebrow { font-family: var(--hp-display); font-variation-settings: normal; letter-spacing: normal; }
    .hp-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        margin-bottom: clamp(1.4rem, 3vh, 2rem);
        padding: 0.45rem 1rem 0.45rem 0.8rem;
        border: 1px solid var(--hp-line-2);
        border-radius: 999px;
        background: var(--hp-bg-2);
        box-shadow: var(--hp-card-shadow);
        font-size: 0.92rem;
        font-weight: 400;
        line-height: 1.4;
        color: var(--hp-ink-2);
        white-space: normal;
    }
    .hp-live { position: relative; display: inline-flex; flex: none; width: 0.55rem; height: 0.55rem; }
    .hp-live i,
    .hp-live::before { content: ""; position: absolute; inset: 0; border-radius: 999px; background: #16a34a; }
    .hp-live::before { animation: hp-ping 1.8s cubic-bezier(0, 0, 0.2, 1) infinite; opacity: 0.6; }
    @keyframes hp-ping { 75%, 100% { transform: scale(2.6); opacity: 0; } }
    {{-- The mask hides a line below its own foot while it rises; sideways it must cut nothing. --}}
    #hp .hp-h1 .es-mask { padding-bottom: 0.16em; margin-bottom: -0.16em; overflow-x: visible; overflow-y: clip; }
    .hp-sub { max-width: 43rem; margin: clamp(1.1rem, 2.4vh, 1.6rem) auto 0; font-size: clamp(1.075rem, 0.55vw + 0.95rem, 1.32rem); line-height: 1.5; color: var(--hp-ink-2); text-wrap: pretty; }
    .hp-hero-actions { display: flex; flex-direction: column; align-items: stretch; justify-content: center; gap: 0.75rem; max-width: 22rem; margin: clamp(1.6rem, 3.4vh, 2.3rem) auto 0; }
    @media (min-width: 640px) {
        .hp-hero-actions { flex-direction: row; align-items: center; max-width: none; }
    }
    {{-- The way around a long page: its parts by number, under the hero's buttons. --}}
    .hp-toc { display: flex; flex-wrap: wrap; justify-content: center; gap: 0.5rem; margin-top: clamp(1.75rem, 4vh, 2.75rem); }
    .hp-toc a {
        display: inline-flex;
        align-items: baseline;
        gap: 0.5rem;
        min-height: 2.5rem;
        padding: 0.5rem 0.95rem;
        border: 1px solid var(--hp-line-2);
        border-radius: 999px;
        background: var(--hp-bg-2);
        font-size: 0.95rem;
        font-weight: 700;
        font-variation-settings: 'wght' 680;
        line-height: 1.4;
        color: var(--hp-ink-2);
        transition: border-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
    }
    .hp-toc a b { font-family: var(--hp-mono); font-size: 0.72rem; font-variation-settings: normal; letter-spacing: 0.06em; color: var(--hp-ink-3); }
    .hp-toc a:hover { border-color: var(--hp-blue); color: var(--hp-ink); transform: translateY(-1px); }
    .hp-hero-foot { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 0.4rem 1.4rem; margin-top: 1.15rem; font-size: 0.95rem; color: var(--hp-ink-3); }

    {{-- The line-up: names in large type, drifting. --}}
    .hp-lineup { position: relative; overflow: hidden; padding-block: clamp(1.75rem, 3.5vw, 3rem); border-block: 1px solid var(--hp-line); background: var(--hp-bg-2); }
    .dark .hp-lineup { background: var(--hp-bg-3); }
    #hp .hp-lineup .es-marquee-track { gap: 0; padding-right: 0; align-items: center; }
    .hp-lineup .es-marquee + .es-marquee { margin-top: clamp(0.35rem, 1vw, 0.9rem); }
    .hp-act {
        display: inline-flex;
        flex: none;
        align-items: center;
        gap: clamp(1rem, 2.2vw, 2.25rem);
        padding-inline-end: clamp(1rem, 2.2vw, 2.25rem);
        font-size: clamp(1.7rem, 3.3vw, 3.3rem);
        font-weight: 700;
        font-variation-settings: 'wght' 820;
        letter-spacing: -0.04em;
        line-height: 1.15;
        white-space: nowrap;
        color: var(--hp-ink);
        transition: color 0.2s ease;
    }
    .hp-act:hover { color: var(--hp-blue); }
    .hp-act i { flex: none; width: 0.3em; height: 0.3em; border-radius: 999px; }
    [data-marquee="-1"] .hp-act { color: var(--hp-ink-3); font-variation-settings: 'wght' 560; }
    [data-marquee="-1"] .hp-act:hover { color: var(--hp-blue); }

    {{-- Cards, tiles, small parts --}}
    .hp-card { position: relative; border: 1px solid var(--hp-line); border-radius: 1.25rem; background: var(--hp-bg-2); box-shadow: var(--hp-card-shadow); }
    .hp-panel { position: relative; border: 1px solid var(--hp-line); border-radius: 1.9rem; background: radial-gradient(40rem 18rem at 50% 0%, var(--hp-glow), transparent 70%), var(--hp-bg-2); box-shadow: var(--hp-card-shadow); }
    .hp-ico { display: inline-flex; flex: none; align-items: center; justify-content: center; width: 2.75rem; height: 2.75rem; border-radius: 0.85rem; }
    .hp-ico svg { width: 1.45rem; height: 1.45rem; }
    .hp-ok { display: inline-flex; flex: none; align-items: center; justify-content: center; width: 1.35rem; height: 1.35rem; border-radius: 999px; background: rgba(78, 129, 250, 0.14); color: var(--hp-blue); }
    .hp-ok svg { width: 0.8rem; height: 0.8rem; }
    .hp-checks { display: grid; gap: 0.85rem; margin-top: 1.75rem; }
    .hp-checks li { display: flex; align-items: flex-start; gap: 0.75rem; color: var(--hp-ink-2); }
    .hp-checks .hp-ok { margin-top: 0.15rem; }
    .hp-tag { display: inline-flex; align-items: center; min-height: 1.6rem; padding: 0 0.6rem; border: 1px solid var(--hp-line-2); border-radius: 999px; font-family: var(--hp-mono); font-size: 0.68rem; font-weight: 700; font-variation-settings: normal; letter-spacing: 0.08em; text-transform: uppercase; color: var(--hp-ink-3); }

    {{-- Three numerals in one panel. Plain type, not the rolling odometer: its digit columns are
       all as wide as a nought, which sets "100%" as "1 00%" in a face whose one is narrow. --}}
    .hp-figs { display: grid; grid-template-columns: minmax(0, 1fr); border: 1px solid var(--hp-line); border-radius: 1.9rem; background: radial-gradient(40rem 18rem at 50% 0%, var(--hp-glow), transparent 70%), var(--hp-bg-2); box-shadow: var(--hp-card-shadow); }
    .hp-fig { padding: clamp(1.75rem, 3.4vw, 3rem); text-align: center; }
    .hp-fig + .hp-fig { border-top: 1px solid var(--hp-line); }
    @media (min-width: 768px) {
        .hp-figs { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .hp-fig + .hp-fig { border-top: 0; border-inline-start: 1px solid var(--hp-line); }
    }
    #hp .hp-num { display: inline-block; font-variation-settings: 'wght' 880; font-size: clamp(3.75rem, 7.5vw, 6.5rem); line-height: 1; padding-inline: 0.08em; font-variant-numeric: proportional-nums; }
    #hp .hp-num { background: linear-gradient(120deg, #2f66ea 0%, #0b8fd8 55%, #0aa5c4 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
    .dark #hp .hp-num { background-image: linear-gradient(120deg, #7da5ff 0%, #38bdf8 55%, #5eead4 100%); }
    .hp-fig strong { display: block; margin-top: 1rem; font-size: 1.3rem; letter-spacing: -0.02em; }
    .hp-fig p { max-width: 17rem; margin: 0.4rem auto 0; font-size: 0.95rem; color: var(--hp-ink-3); }

    {{-- Tiles for a short row of names with a mark (integrations). --}}
    .hp-plugs { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.75rem; }
    {{-- Five on a phone: three and then two, each row full. --}}
    .hp-plugs.is-five { grid-template-columns: repeat(6, minmax(0, 1fr)); }
    .hp-plugs.is-five > li { grid-column: span 2; }
    .hp-plugs.is-five > li:nth-child(n+4) { grid-column: span 3; }
    @media (min-width: 640px) {
        .hp-plugs { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .hp-plugs.is-five { grid-template-columns: repeat(5, minmax(0, 1fr)); }
        .hp-plugs.is-five > li,
        .hp-plugs.is-five > li:nth-child(n+4) { grid-column: auto; }
    }
    .hp-plug {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.8rem;
        height: 100%;
        min-height: clamp(7rem, 11vw, 9.5rem);
        padding: 1.1rem 0.6rem;
        border: 1px solid var(--hp-line);
        border-radius: 1.25rem;
        background: var(--hp-bg);
        font-size: 0.95rem;
        font-weight: 700;
        font-variation-settings: 'wght' 700;
        line-height: 1.25;
        text-align: center;
        color: var(--hp-ink-2);
        transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease, color 0.2s ease;
    }
    .dark .hp-plug { background: var(--hp-bg-2); }
    .hp-plug:hover { transform: translateY(-3px); border-color: var(--hp-blue); box-shadow: var(--hp-card-shadow); color: var(--hp-ink); }
    .hp-plug-logo { width: clamp(2.25rem, 3.4vw, 3rem); height: clamp(2.25rem, 3.4vw, 3rem); }

    {{-- The bill: names set like the foot of a festival poster. Each name is the link; its
       sentence stands under the bill while the name is pointed at or focused, and under the
       name itself on a touch screen, where nothing can be pointed at. --}}
    .hp-bill {
        position: relative;
        padding: clamp(1.5rem, 4vw, 3.25rem) clamp(1.25rem, 5vw, 4.5rem) clamp(1.75rem, 4vw, 3.25rem);
        border: 1px solid var(--hp-line);
        border-radius: 1.9rem;
        background:
            radial-gradient(46rem 20rem at 50% 0%, var(--hp-glow), transparent 70%),
            var(--hp-bg-2);
        box-shadow: var(--hp-card-shadow);
    }
    .hp-bill-top { display: flex; justify-content: space-between; gap: 1rem; padding-bottom: 0.9rem; border-bottom: 2px solid var(--hp-ink); font-family: var(--hp-mono); font-size: 0.72rem; font-weight: 700; font-variation-settings: normal; letter-spacing: 0.16em; text-transform: uppercase; color: var(--hp-ink-3); }
    #hp .hp-bill .hp-h2 { margin-top: clamp(1.5rem, 3vw, 2.5rem); font-size: clamp(1.2rem, 0.8vw + 1rem, 1.6rem); font-variation-settings: 'wght' 700; letter-spacing: -0.02em; line-height: 1.3; color: var(--hp-ink-2); }
    .hp-bill-list { margin-top: 1.25rem; }
    .hp-bill-list li { border-top: 1px solid var(--hp-line); }
    .hp-bill-list li.hp-bill-break { display: none; }
    .hp-bill-list a { display: block; padding-block: 0.95rem; }
    .hp-bill-name { display: block; font-size: 1.2rem; font-weight: 700; font-variation-settings: 'wght' 860; letter-spacing: -0.03em; line-height: 1.15; text-transform: uppercase; color: var(--hp-ink); transition: color 0.2s ease; }
    .hp-bill-desc { display: block; margin-top: 0.35rem; font-size: 0.95rem; line-height: 1.5; color: var(--hp-ink-2); }
    .hp-bill-list a:hover .hp-bill-name { color: var(--hp-blue); }
    @media (hover: hover) and (min-width: 768px) {
        .hp-bill { text-align: center; }
        .hp-bill-list {
            --b: clamp(1.5rem, 2.6vw, 2.75rem);
            position: relative;
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            justify-content: center;
            gap: 0.1rem 1.6rem;
            padding-bottom: 6.25rem;
        }
        .hp-bill-list li { border-top: 0; }
        .hp-bill-list li.hp-bill-break { display: block; flex-basis: 100%; height: 0.35rem; }
        .hp-bill-list a { padding-block: 0.1rem; }
        .hp-bill-name { font-size: var(--b); line-height: 1.12; white-space: nowrap; }
        .hp-bill-list li:nth-child(-n+3) .hp-bill-name { font-size: calc(var(--b) * 1.35); letter-spacing: -0.045em; }
        .hp-bill-list li:nth-child(n+9) .hp-bill-name { font-size: calc(var(--b) * 0.8); }
        .hp-bill-list li:nth-child(even) .hp-bill-name { color: var(--hp-blue); }
        .hp-bill-list a:hover .hp-bill-name,
        .hp-bill-list a:focus-visible .hp-bill-name { color: var(--hp-ink); text-decoration: underline; text-decoration-thickness: 0.07em; text-underline-offset: 0.12em; }
        .hp-bill-list li:nth-child(odd) a:hover .hp-bill-name,
        .hp-bill-list li:nth-child(odd) a:focus-visible .hp-bill-name { color: var(--hp-blue); }
        .hp-bill-list::after,
        .hp-bill-desc {
            position: absolute;
            inset-inline: 0;
            bottom: 0;
            display: grid;
            place-items: center;
            height: 4.75rem;
            margin: 0;
            border-top: 1px solid var(--hp-line);
        }
        .hp-bill-list::after { content: attr(data-hint); font-family: var(--hp-mono); font-size: 0.72rem; font-weight: 700; font-variation-settings: normal; letter-spacing: 0.16em; text-transform: uppercase; color: var(--hp-ink-3); }
        .hp-bill-desc {
            z-index: 1;
            padding-inline: max(0rem, calc((100% - 46rem) / 2));
            background: var(--hp-bg-2);
            font-size: 1.05rem;
            text-wrap: balance;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s ease, visibility 0s linear 0.2s;
        }
        .hp-bill-list a:hover .hp-bill-desc,
        .hp-bill-list a:focus-visible .hp-bill-desc { opacity: 1; visibility: visible; transition-delay: 0s; }
    }

    {{-- A band that is dark in both modes: the page goes somewhere else for a while. Its
       colours are literal for that reason. The dark stops two pixels short of its top and
       bottom edges: a band rarely starts on a whole pixel, and a browser that draws the dark
       and then the fade over it, each softened at that edge, leaves a grey hairline. --}}
    .hp-dark {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        overflow: clip;
        padding-block: clamp(6rem, 12vw, 10rem);
        background: linear-gradient(#050814, #050814) 0 2px / 100% calc(100% - 4px) no-repeat;
        color: #eef2ff;
    }
    .hp-dark::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -1;
        background:
            linear-gradient(to bottom, var(--hp-bg) 0, var(--hp-bg) 2px, #c3cff0 1.6rem, #5a6fb4 3.4rem, #1b2759 5.4rem, rgba(5, 8, 20, 0) 8rem) top / 100% 8rem no-repeat,
            linear-gradient(to top, var(--hp-bg) 0, var(--hp-bg) 2px, #bfe3f6 1.6rem, #4f9fd0 3.2rem, #16306a 5.2rem, rgba(5, 8, 20, 0) 8rem) bottom / 100% 8rem no-repeat,
            radial-gradient(52rem 30rem at 72% 26%, rgba(47, 102, 234, 0.42), transparent 70%),
            radial-gradient(34rem 22rem at 14% 78%, rgba(34, 211, 238, 0.16), transparent 70%);
    }
    .hp-dark.on-alt::before {
        background:
            linear-gradient(to bottom, var(--hp-bg-2) 0, var(--hp-bg-2) 2px, #c3cff0 1.6rem, #5a6fb4 3.4rem, #1b2759 5.4rem, rgba(5, 8, 20, 0) 8rem) top / 100% 8rem no-repeat,
            linear-gradient(to top, var(--hp-bg-2) 0, var(--hp-bg-2) 2px, #bfe3f6 1.6rem, #4f9fd0 3.2rem, #16306a 5.2rem, rgba(5, 8, 20, 0) 8rem) bottom / 100% 8rem no-repeat,
            radial-gradient(52rem 30rem at 72% 26%, rgba(47, 102, 234, 0.42), transparent 70%),
            radial-gradient(34rem 22rem at 14% 78%, rgba(34, 211, 238, 0.16), transparent 70%);
    }
    .dark .hp-dark { background: linear-gradient(#111d5a, #111d5a) 0 2px / 100% calc(100% - 4px) no-repeat; }
    .dark .hp-dark::before,
    .dark .hp-dark.on-alt::before {
        background:
            linear-gradient(to bottom, var(--hp-bg) 0, var(--hp-bg) 2px, rgba(17, 29, 90, 0) 7rem) top / 100% 7rem no-repeat,
            linear-gradient(to top, var(--hp-bg) 0, var(--hp-bg) 2px, rgba(17, 29, 90, 0) 7rem) bottom / 100% 7rem no-repeat,
            radial-gradient(60rem 34rem at 72% 30%, rgba(78, 129, 250, 0.9), transparent 72%),
            radial-gradient(40rem 26rem at 14% 78%, rgba(34, 211, 238, 0.4), transparent 70%);
    }
    .hp-dark.is-run { padding-block: clamp(4rem, 8vw, 7rem) clamp(5rem, 10vw, 9rem); }
    #hp .hp-dark.is-run .es-chapter { padding-top: clamp(2rem, 4vw, 3.5rem); }
    #hp .hp-dark .hp-kicker { color: #9fb1d6; }
    #hp .hp-dark .hp-lead { color: #c5cde2; }
    #hp .hp-dark .hp-more { color: #a9c3ff; }
    #hp .hp-dark .hp-btn-ghost { color: #eef2ff; background: #101833; border-color: rgba(125, 165, 255, 0.34); box-shadow: none; }

    {{-- Questions: the heading stays beside them from a laptop up. --}}
    .hp-faq-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: clamp(2rem, 4vw, 4rem); }
    @media (min-width: 1024px) {
        .hp-faq-grid { grid-template-columns: minmax(0, 0.8fr) minmax(0, 1.2fr); align-items: start; }
        .hp-faq-grid .hp-head { position: sticky; top: 7rem; }
    }
    .hp-faq { border-bottom: 1px solid var(--hp-line-2); }
    .hp-faq:first-child { border-top: 1px solid var(--hp-line-2); }
    .hp-faq summary { display: flex; cursor: pointer; align-items: center; justify-content: space-between; gap: 1.5rem; padding: 1.35rem 0.25rem; list-style: none; }
    .hp-faq summary::-webkit-details-marker { display: none; }
    #hp .hp-faq h3 { font-size: clamp(1.1rem, 0.5vw + 1rem, 1.3rem); font-weight: 700; font-variation-settings: 'wght' 720; letter-spacing: -0.02em; line-height: 1.3; transition: color 0.2s ease; }
    .hp-faq summary:hover h3 { color: var(--hp-blue); }
    .hp-faq summary i { position: relative; flex: none; width: 2rem; height: 2rem; border: 1px solid var(--hp-line-2); border-radius: 999px; transition: transform 0.3s ease, background-color 0.2s ease, border-color 0.2s ease; }
    .hp-faq summary i::before,
    .hp-faq summary i::after { content: ""; position: absolute; top: 50%; left: 50%; width: 0.7rem; height: 2px; margin: -1px 0 0 -0.35rem; border-radius: 2px; background: currentColor; }
    .hp-faq summary i::after { transform: rotate(90deg); transition: transform 0.3s ease; }
    .hp-faq[open] summary i { background: var(--hp-ink); border-color: var(--hp-ink); color: var(--hp-bg); transform: rotate(180deg); }
    .hp-faq[open] summary i::after { transform: rotate(0deg); }
    .hp-faq .faq-answer { max-width: 44rem; padding: 0 0.25rem 1.5rem; color: var(--hp-ink-2); }
    .hp-faq .faq-answer a { font-weight: 700; font-variation-settings: 'wght' 700; color: var(--hp-blue); text-decoration: underline; text-decoration-thickness: 1px; text-underline-offset: 0.2em; }
    .hp-faq .faq-answer > * + * { margin-top: 0.75rem; }

    {{-- The claim box and the finale that holds it (dark in both modes, literal colours). --}}
    .hp-claimrow { display: flex; flex-direction: column; gap: 0.75rem; max-width: 38rem; margin: clamp(1.6rem, 3.4vh, 2.3rem) auto 0; }
    @media (min-width: 640px) {
        .hp-claimrow { flex-direction: row; }
    }
    #hp .hp-claim {
        display: flex;
        flex: 1 1 0;
        min-width: 0;
        align-items: center;
        padding: 1rem 1.2rem;
        border: 1px solid var(--hp-line-2);
        border-radius: 1rem;
        background: var(--hp-bg-2);
        box-shadow: var(--hp-card-shadow);
        font-family: var(--hp-mono);
        font-size: 1rem;
        font-variation-settings: normal;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    #hp .hp-claim input {
        flex: 1 1 0;
        width: 0;
        min-width: 0;
        border: 0;
        background: transparent;
        padding-block: 0;
        padding-inline: 0;
        box-shadow: none;
        outline: 0;
        text-align: right;
        font-family: inherit;
        font-size: inherit;
        font-weight: 700;
        color: var(--hp-ink);
    }
    #hp .hp-claim:focus-within { border-color: var(--hp-blue); box-shadow: 0 0 0 4px rgba(78, 129, 250, 0.3); }
    #hp .hp-claim input::placeholder { color: var(--hp-ink-3); opacity: 1; font-weight: 400; }
    #hp .hp-claim input:focus { box-shadow: none; outline: 0; border: 0; }
    .hp-claim-suffix { flex: none; color: var(--hp-ink-3); user-select: none; }
    @media (max-width: 479px) {
        {{-- Room for the name: the field keeps its 16px (a smaller one makes a phone zoom in), the
           ending it cannot change gives way. --}}
        #hp .hp-claim { padding-inline: 1rem; }
        .hp-claim-suffix { font-size: 0.84rem; }
    }
    .hp-finale {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        padding: clamp(3.5rem, 8vw, 7rem) clamp(1.25rem, 5vw, 4rem);
        border-radius: 2.5rem;
        background:
            radial-gradient(52rem 30rem at 50% -6rem, rgba(47, 102, 234, 0.62), transparent 70%),
            radial-gradient(30rem 20rem at 8% 100%, rgba(34, 211, 238, 0.2), transparent 70%),
            radial-gradient(30rem 20rem at 92% 100%, rgba(14, 165, 233, 0.2), transparent 70%),
            #050814;
        color: #eef2ff;
        text-align: center;
        box-shadow: 0 40px 100px -40px rgba(47, 102, 234, 0.7);
    }
    .hp-finale::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -1;
        background-image:
            linear-gradient(rgba(255, 255, 255, 0.07) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.07) 1px, transparent 1px);
        background-size: calc(100% / 7) 6rem;
        -webkit-mask-image: radial-gradient(ellipse at 50% 0%, #000, transparent 72%);
        mask-image: radial-gradient(ellipse at 50% 0%, #000, transparent 72%);
    }
    #hp .hp-finale .hp-h2 { max-width: 16em; margin-inline: auto; font-size: clamp(2.3rem, 3.4vw + 0.8rem, 4.2rem); }
    .hp-finale .hp-lead { max-width: 40rem; margin-top: 1.4rem; margin-inline: auto; color: #c5cde2; }
    #hp .hp-finale .hp-claim { border-color: rgba(125, 165, 255, 0.4); background: #101831; box-shadow: none; }
    #hp .hp-finale .hp-claim:focus-within { border-color: #8db0ff; box-shadow: 0 0 0 4px rgba(141, 176, 255, 0.35); }
    #hp .hp-finale .hp-claim input { color: #fff; }
    #hp .hp-finale .hp-claim input::placeholder,
    .hp-finale .hp-claim-suffix { color: #b9c6e6; }
    .hp-finale .hp-btn-primary { background: linear-gradient(100deg, #3a6df0, #1f8fe0); }
    .hp-finale-foot { margin-top: 1.4rem; font-size: 0.92rem; color: #b9c6e6; }
    .hp-finale-foot a { color: #a9c3ff; text-decoration: underline; text-underline-offset: 0.2em; }
    .dark .hp-finale { box-shadow: 0 0 0 1px rgba(125, 165, 255, 0.22), 0 40px 100px -40px rgba(47, 102, 234, 0.7); }

    {{-- Motion off --}}
    @media (prefers-reduced-motion: reduce) {
        #hp .es-marquee-track > [data-loop-copy] { display: none; }
        .hp-live::before { animation: none !important; }
        .hp-btn,
        .hp-btn svg,
        .hp-plug,
        .hp-more { transition: none; }
    }
</style>
