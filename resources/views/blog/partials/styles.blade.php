{{--
    The blog's own look, on the house style's tokens (marketing/partials/hp-kit). Plain CSS on
    purpose: a Tailwind class that is not already in the built stylesheet renders as nothing, and
    the article's body is a post's stored HTML, which carries no classes at all.

    The reading column is 41rem: about 75 characters of 18px text. Everything inside a post
    (links, numbered steps, tables, wording to copy) is styled here, because the typography
    plugin is not installed and Tailwind's reset leaves all of it bare.
--}}
<style {!! nonce_attr() !!}>
    .blog-wrap { width: min(100% - 2.5rem, 76rem); margin-inline: auto; }

    {{-- The top of a post: where it sits, what it is, when it was written. --}}
    .blog-article { position: relative; isolation: isolate; }
    .blog-article > .hp-hero-sky { inset: 0 0 auto 0; height: 38rem; z-index: -1; }
    .blog-head { padding-block: clamp(1rem, 4vw, 3.25rem) clamp(1.25rem, 2.5vw, 2rem); }
    @media (min-width: 768px) { #hp .blog-title { margin-top: 0.9rem; } }
    .blog-crumbs { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; font-family: var(--hp-mono); font-size: 0.78rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--hp-ink-3); }
    .blog-crumbs a { color: var(--hp-ink-3); min-height: 2.75rem; display: inline-flex; align-items: center; }
    @media (min-width: 768px) { .blog-crumbs a { min-height: 1.75rem; } }
    .blog-crumbs a:hover { color: var(--hp-blue); }
    .blog-top .blog-crumbs a::before { content: "\2190"; margin-inline-end: 0.5rem; }
    .blog-top .blog-crumbs span { display: none; }
    #hp .blog-title { margin-top: 0.4rem; overflow-wrap: anywhere; font-size: clamp(2rem, 3.4vw + 1.1rem, 3.25rem); line-height: 1.06; letter-spacing: -0.035em; font-weight: 700; font-variation-settings: 'wght' 820; color: var(--hp-ink); text-wrap: balance; }
    .blog-dek { margin-top: 1rem; font-size: clamp(1.125rem, 0.6vw + 1rem, 1.4rem); line-height: 1.45; letter-spacing: -0.01em; color: var(--hp-ink); text-wrap: pretty; overflow-wrap: anywhere; }
    .blog-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 0.3rem 0.9rem; margin-top: 1.1rem; font-size: 0.92rem; color: var(--hp-ink-3); }
    .blog-meta span + span::before, .blog-meta time + span::before, .blog-meta span + time::before { content: "\00b7"; margin-inline-end: 0.9rem; }
    .blog-figure { margin-block: 1.75rem; aspect-ratio: 16 / 7; border-radius: 1.25rem; overflow: hidden; border: 1px solid var(--hp-line); background: var(--hp-bg-3); }
    .blog-figure img { display: block; width: 100%; height: 100%; object-fit: cover; }
    @media (min-width: 768px) { .blog-figure { aspect-ratio: 16 / 5; } }

    {{-- The post and, from a laptop up, the rail beside it. --}}
    .blog-body { display: grid; grid-template-columns: minmax(0, 41rem); justify-content: center; gap: 3.5rem; width: min(100% - 2.5rem, 76rem); margin-inline: auto; padding-bottom: clamp(3rem, 6vw, 5rem); }
    .blog-rail { display: none; }
    @media (min-width: 1180px) {
        .blog-body { grid-template-columns: minmax(0, 41rem) 17rem; }
        .blog-rail { display: block; position: sticky; top: 5.5rem; align-self: start; margin-top: clamp(1rem, 4vw, 3.25rem); }
        {{-- A post with many sections: the list scrolls, so the card under it stays on the screen. --}}
        .blog-rail .blog-toc ol { max-height: calc(100vh - 29rem); min-height: 6rem; overflow-y: auto; scrollbar-width: thin; }
        .blog-rail > .blog-plug:first-child { margin-top: 0; }
        .blog-toc-inline, .blog-plug.is-inline { display: none; }
    }
    .blog-toc-title { display: flex; align-items: center; min-height: 1.75rem; font-family: var(--hp-mono); font-size: 0.74rem; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: var(--hp-ink-3); }
    .blog-toc ol { margin-top: 0.6rem; display: grid; gap: 0.35rem; }
    .blog-toc a { display: block; padding: 0.4rem 0; font-size: 0.95rem; line-height: 1.3; color: var(--hp-ink-2); overflow-wrap: anywhere; }
    .blog-toc a:hover { color: var(--hp-blue); }
    .blog-toc-inline { margin-bottom: 1.75rem; border: 1px solid var(--hp-line); border-radius: 1rem; background: var(--hp-bg-2); }
    .blog-toc-inline summary { display: flex; align-items: center; justify-content: space-between; min-height: 3rem; padding: 0 1.1rem; cursor: pointer; list-style: none; font-weight: 700; font-variation-settings: 'wght' 700; color: var(--hp-ink); }
    .blog-toc-inline summary::-webkit-details-marker { display: none; }
    .blog-toc-inline summary::after { content: ""; width: 0.55rem; height: 0.55rem; border-inline-end: 2px solid var(--hp-ink-3); border-bottom: 2px solid var(--hp-ink-3); transform: rotate(45deg) translateY(-2px); transition: transform 0.2s ease; }
    .blog-toc-inline[open] summary::after { transform: rotate(225deg) translateY(-2px); }
    .blog-toc-inline ol { padding: 0 1.1rem 0.9rem; margin: 0; }
    .blog-toc-inline a { padding: 0.6rem 0; }

    {{-- A post's stored HTML. --}}
    .blog-prose { font-size: 1.0625rem; line-height: 1.7; color: var(--hp-ink-2); overflow-wrap: break-word; }
    @media (min-width: 768px) { .blog-prose { font-size: 1.125rem; } }
    .blog-prose > * + * { margin-top: 1.2em; }
    {{-- A post that opens on a heading: its top margin would be an empty band under the header. --}}
    #hp .blog-prose > :first-child { margin-top: 0; }
    .blog-prose + .blog-prose { margin-top: 1.2em; }
    #hp .blog-prose h2 { margin-top: 2.1em; font-size: clamp(1.45rem, 1vw + 1.2rem, 1.85rem); line-height: 1.15; letter-spacing: -0.03em; font-weight: 700; font-variation-settings: 'wght' 800; color: var(--hp-ink); text-wrap: balance; scroll-margin-top: 6rem; }
    #hp .blog-prose h3 { margin-top: 1.7em; font-size: clamp(1.1rem, 0.6vw + 0.98rem, 1.25rem); line-height: 1.25; letter-spacing: -0.02em; font-weight: 700; font-variation-settings: 'wght' 760; color: var(--hp-ink); }
    #hp .blog-prose h4 { margin-top: 1.5em; font-size: 1.1rem; font-weight: 700; font-variation-settings: 'wght' 740; color: var(--hp-ink); }
    .blog-prose h2 + *, .blog-prose h3 + *, .blog-prose h4 + * { margin-top: 0.7em; }
    .blog-prose a { color: var(--hp-blue); text-decoration: underline; text-decoration-thickness: 1px; text-underline-offset: 0.2em; font-variation-settings: 'wght' 600; }
    .blog-prose a:hover { text-decoration-thickness: 2px; }
    #hp .blog-prose strong, #hp .blog-prose b { font-weight: 700; font-variation-settings: 'wght' 700; color: var(--hp-ink); }
    .blog-prose ul { list-style: disc; padding-inline-start: 1.35em; }
    .blog-prose ol { list-style: decimal; padding-inline-start: 1.5em; }
    .blog-prose li + li { margin-top: 0.45em; }
    .blog-prose li::marker { color: var(--hp-ink-3); font-variation-settings: 'wght' 700; }
    .blog-prose li > ul, .blog-prose li > ol { margin-top: 0.45em; }
    .blog-prose blockquote { padding: 1.05rem 1.3rem; border: 1px solid var(--hp-line-2); border-radius: 1rem; background: var(--hp-bg-2); color: var(--hp-ink); }
    .blog-prose blockquote > * + * { margin-top: 0.6em; }
    {{-- A table wider than a phone scrolls inside itself. The two soft edges are drawn with the
       background: a cover that travels with the cells sits over a shade that stays put, so the
       shade shows only on the side where more of the table is waiting. --}}
    .blog-prose table {
        --blog-shade: rgba(10, 16, 32, 0.2);
        display: block; width: max-content; max-width: 100%; overflow-x: auto; border-collapse: collapse; border: 1px solid var(--hp-line); border-radius: 0.9rem; font-size: 0.86em; line-height: 1.45;
        background:
            linear-gradient(to right, var(--hp-bg-2) 40%, transparent) left center / 3rem 100% no-repeat local,
            linear-gradient(to left, var(--hp-bg-2) 40%, transparent) right center / 3rem 100% no-repeat local,
            linear-gradient(to right, var(--blog-shade), transparent) left center / 1rem 100% no-repeat scroll,
            linear-gradient(to left, var(--blog-shade), transparent) right center / 1rem 100% no-repeat scroll,
            var(--hp-bg-2);
    }
    .dark .blog-prose table { --blog-shade: rgba(0, 0, 0, 0.6); }
    @media (min-width: 768px) { .blog-prose table { font-size: 0.9em; } }
    .blog-prose th { padding: 0.6rem 0.75rem; text-align: start; font-weight: 700; font-variation-settings: 'wght' 700; color: var(--hp-ink); border-bottom: 1px solid var(--hp-line-2); white-space: nowrap; }
    .blog-prose td { padding: 0.6rem 0.75rem; vertical-align: top; border-bottom: 1px solid var(--hp-line); min-width: 7rem; }
    @media (min-width: 768px) { .blog-prose th, .blog-prose td { padding: 0.7rem 0.95rem; } }
    .blog-prose tr:last-child td { border-bottom: 0; }
    .blog-prose code { padding: 0.12em 0.4em; border-radius: 0.4rem; background: var(--hp-bg-3); font-family: var(--hp-mono); font-size: 0.9em; color: var(--hp-ink); }
    .blog-prose pre { padding: 1rem 1.2rem; border-radius: 1rem; background: var(--hp-bg-3); overflow-x: auto; }
    .blog-prose pre code { padding: 0; background: none; }
    .blog-prose hr { border: 0; border-top: 1px solid var(--hp-line-2); margin-block: 2.2em; }
    .blog-prose img { max-width: 100%; height: auto; border-radius: 1rem; }

    {{-- The one card beside (or under) a post: plain, a full hairline, no stripe. --}}
    .blog-plug { margin-top: 1.75rem; padding: 1.25rem; border: 1px solid var(--hp-line); border-radius: 1.25rem; background: var(--hp-bg-2); box-shadow: var(--hp-card-shadow); }
    .blog-plug.is-inline { margin-top: 2.5rem; }
    #hp .blog-plug h2 { margin-top: 0.6rem; font-size: 1.15rem; line-height: 1.25; letter-spacing: -0.02em; font-weight: 700; font-variation-settings: 'wght' 780; color: var(--hp-ink); }
    .blog-plug p { margin-top: 0.5rem; font-size: 0.95rem; line-height: 1.5; color: var(--hp-ink-2); }
    .blog-plug .hp-more { margin-top: 0.8rem; min-height: 2.75rem; }

    {{-- Cards: the index, a section, "keep reading". --}}
    .blog-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.5rem; }
    @media (min-width: 700px) { .blog-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (min-width: 1080px) { .blog-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    .blog-card { display: flex; flex-direction: column; overflow: hidden; border: 1px solid var(--hp-line); border-radius: 1.25rem; background: var(--hp-bg-2); box-shadow: var(--hp-card-shadow); color: inherit; transition: transform 0.2s ease, border-color 0.2s ease; }
    .blog-card:hover { transform: translateY(-3px); border-color: var(--hp-line-2); }
    .blog-card:focus-visible { outline: 2px solid var(--hp-blue); outline-offset: 3px; }
    .blog-card-img { aspect-ratio: 2 / 1; background: var(--hp-bg-3); overflow: hidden; }
    .blog-card-img img { display: block; width: 100%; height: 100%; object-fit: cover; }
    .blog-card-body { display: flex; flex: 1; flex-direction: column; gap: 0.6rem; padding: 1.35rem 1.4rem 1.3rem; overflow-wrap: anywhere; }
    .blog-card-kicker { font-family: var(--hp-mono); font-size: 0.72rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--hp-ink-3); }
    #hp .blog-card-title { font-size: 1.25rem; line-height: 1.2; letter-spacing: -0.02em; font-weight: 700; font-variation-settings: 'wght' 780; color: var(--hp-ink); text-wrap: balance; }
    .blog-card:hover .blog-card-title { color: var(--hp-blue); }
    .blog-card-text { display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 5; overflow: hidden; font-size: 0.98rem; line-height: 1.5; color: var(--hp-ink-2); }
    .blog-card-meta { margin-top: auto; padding-top: 0.35rem; font-size: 0.85rem; color: var(--hp-ink-3); }
    {{-- The newest post, set apart above the grid: picture beside the words from a tablet up. --}}
    .blog-card.is-lead { margin-bottom: 1.5rem; }
    .blog-card.is-lead .blog-card-text { display: block; overflow: visible; }
    #hp .blog-card.is-lead .blog-card-title { font-size: clamp(1.45rem, 2.2vw + 0.9rem, 2.3rem); line-height: 1.1; letter-spacing: -0.03em; }
    .blog-card.is-lead .blog-card-body { padding: 1.2rem 1.4rem 1.3rem; }
    @media (min-width: 700px) {
        .blog-card.is-lead { flex-direction: row; align-items: stretch; }
        .blog-card.is-lead .blog-card-img { flex: 0 0 38%; aspect-ratio: 16 / 10; }
        .blog-card.is-lead .blog-card-body { justify-content: center; gap: 0.8rem; padding: clamp(1.5rem, 3vw, 2.5rem); }
        .blog-card.is-lead .blog-card-text { font-size: 1.08rem; }
        .blog-card.is-lead .blog-card-meta { margin-top: 0; }
    }

    {{-- The top of the index. --}}
    .blog-top { position: relative; isolation: isolate; overflow: clip; padding-block: clamp(1.25rem, 3vw, 2.5rem) clamp(1rem, 1.8vw, 1.5rem); }
    .blog-top .hp-hero-sky { z-index: -1; }
    #hp .blog-top h1 { margin-top: 0.6rem; font-size: clamp(2.1rem, 3vw + 1.2rem, 3.4rem); line-height: 1.04; letter-spacing: -0.04em; font-weight: 700; font-variation-settings: 'wght' 830; color: var(--hp-ink); overflow-wrap: anywhere; }
    .blog-top .hp-lead { margin-top: 0.8rem; max-width: 40rem; }
    .blog-top-row { display: flex; flex-direction: column; gap: 1.25rem; }
    .blog-search { position: relative; display: flex; gap: 0.5rem; width: min(100%, 30rem); }
    {{-- Beside the heading only where the heading still gets a line to itself. --}}
    @media (min-width: 1180px) {
        .blog-top-row { flex-direction: row; align-items: end; justify-content: space-between; gap: 2.5rem; }
        .blog-search { flex: 0 0 24rem; }
    }
    #hp .blog-search input { flex: 1; min-width: 0; min-height: 3rem; padding: 0 1rem; border: 1px solid var(--hp-line-2); border-radius: 0.9rem; background: var(--hp-bg-2); color: var(--hp-ink); font-size: 1rem; box-shadow: none; }
    #hp .blog-search input::placeholder { color: var(--hp-ink-3); }
    #hp .blog-search input:focus { outline: 2px solid var(--hp-blue); outline-offset: 1px; border-color: var(--hp-blue); }
    {{-- One row that scrolls sideways on a phone: wrapped, eleven chips took a third of the screen. --}}
    .blog-chips { display: flex; flex-wrap: nowrap; gap: 0.5rem; margin-top: 1.25rem; margin-inline: -1.25rem; padding-inline: 1.25rem; overflow-x: auto; scrollbar-width: none; -webkit-overflow-scrolling: touch; }
    .blog-chips::-webkit-scrollbar { display: none; }
    .blog-chip { flex: none; white-space: nowrap; }
    .blog-chips > .blog-chip:first-child { order: -2; }
    .blog-chip.is-on { order: -1; }
    @media (min-width: 900px) {
        .blog-chips { flex-wrap: wrap; margin-inline: 0; padding-inline: 0; overflow: visible; }
        .blog-chips > .blog-chip:first-child, .blog-chip.is-on { order: 0; }
    }
    .blog-chip { display: inline-flex; align-items: baseline; gap: 0.45rem; min-height: 2.75rem; padding: 0.6rem 1rem; border: 1px solid var(--hp-line-2); border-radius: 999px; background: var(--hp-bg-2); font-size: 0.95rem; font-weight: 700; font-variation-settings: 'wght' 650; color: var(--hp-ink-2); transition: border-color 0.2s ease, color 0.2s ease; }
    .blog-chip b { font-family: var(--hp-mono); font-size: 0.72rem; font-variation-settings: normal; color: var(--hp-ink-3); }
    .blog-chip:hover { border-color: var(--hp-blue); color: var(--hp-ink); }
    .blog-chip.is-on { border-color: var(--hp-ink); background: var(--hp-ink); color: var(--hp-bg); }
    .blog-chip.is-on b { color: inherit; opacity: 0.7; }
    .blog-list { padding-block: 0.75rem 1.25rem; }
    .blog-note { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem 1rem; margin-bottom: 1.25rem; font-size: 0.98rem; color: var(--hp-ink-2); }
    .blog-empty { padding: 3rem 1.5rem; border: 1px dashed var(--hp-line-2); border-radius: 1.25rem; text-align: center; color: var(--hp-ink-2); }
    #hp .blog-empty h2 { font-size: 1.3rem; font-weight: 700; font-variation-settings: 'wght' 780; color: var(--hp-ink); }
    .blog-pages { margin-top: 2.25rem; }
    .blog-pager { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 0.5rem; }
    .blog-pager-pages { display: none; align-items: center; gap: 0.35rem; }
    .blog-pager-count { padding-inline: 0.75rem; font-size: 0.95rem; color: var(--hp-ink-3); }
    @media (min-width: 768px) {
        .blog-pager-pages { display: inline-flex; }
        .blog-pager-count { display: none; }
    }
    .blog-page { display: inline-flex; align-items: center; justify-content: center; min-width: 2.75rem; min-height: 2.75rem; padding: 0 0.9rem; border: 1px solid var(--hp-line-2); border-radius: 999px; background: var(--hp-bg-2); font-size: 0.95rem; font-weight: 700; font-variation-settings: 'wght' 650; color: var(--hp-ink-2); transition: border-color 0.2s ease, color 0.2s ease; }
    a.blog-page:hover { border-color: var(--hp-blue); color: var(--hp-ink); }
    .blog-page.is-on { border-color: var(--hp-ink); background: var(--hp-ink); color: var(--hp-bg); }
    .blog-page.is-off { color: var(--hp-ink-3); opacity: 0.55; }
    .blog-page.is-gap { min-width: 1.5rem; padding: 0; border: 0; background: none; }

    {{-- The audience posts, by the page they belong to. --}}
    .blog-directory { columns: 1; column-gap: 2.5rem; }
    @media (min-width: 700px) { .blog-directory { columns: 2; } }
    @media (min-width: 1080px) { .blog-directory { columns: 3; } }
    @media (min-width: 1360px) { .blog-directory { columns: 4; } }
    .blog-directory section { break-inside: avoid; padding-bottom: 1.75rem; }
    #hp .blog-directory h2 { font-size: 1.1rem; font-weight: 700; font-variation-settings: 'wght' 790; letter-spacing: -0.02em; color: var(--hp-ink); }
    {{-- Two names a row on a phone: 150 links in one column were seven screens, 34px apart. --}}
    .blog-directory ul { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: 1rem; margin-top: 0.35rem; }
    .blog-directory a { display: block; padding-block: 0.72rem; line-height: 1.3; color: var(--hp-ink-2); text-decoration: underline; text-decoration-color: var(--hp-line-2); text-decoration-thickness: 1px; text-underline-offset: 0.22em; overflow-wrap: anywhere; }
    .blog-directory a:hover { color: var(--hp-blue); text-decoration-color: currentColor; }
    @media (min-width: 700px) {
        .blog-directory ul { display: block; }
        .blog-directory a { padding: 0.4rem 0; }
    }

    .blog-related { padding-block: clamp(2.5rem, 5vw, 4rem); border-top: 1px solid var(--hp-line); background: var(--hp-bg-2); }
    .dark .blog-related { background: var(--hp-bg-3); }
    .blog-related-head { display: flex; flex-wrap: wrap; align-items: end; justify-content: space-between; gap: 0.75rem 1.5rem; margin-bottom: 1.5rem; }
    #hp .blog-related h2 { font-size: clamp(1.5rem, 1.2vw + 1.1rem, 2rem); line-height: 1.1; letter-spacing: -0.03em; font-weight: 700; font-variation-settings: 'wght' 810; color: var(--hp-ink); }
    .blog-related .blog-card { background: var(--hp-bg); box-shadow: none; }
    {{-- The band keeps the article's own left edge, and three cards are one row or one column,
       never two and one. --}}
    .blog-related > .blog-wrap { width: min(100% - 2.5rem, 41rem); }
    .blog-related .blog-grid { grid-template-columns: minmax(0, 1fr); }
    @media (min-width: 900px) {
        .blog-related > .blog-wrap { width: min(100% - 2.5rem, 56rem); }
        .blog-related .blog-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    @media (min-width: 1180px) { .blog-related > .blog-wrap { width: min(100% - 2.5rem, 61.5rem); } }
    .dark .blog-related .blog-card { background: var(--hp-bg-2); }

    @media (prefers-reduced-motion: reduce) {
        .blog-card, .blog-chip, .blog-toc-inline summary::after { transition: none; }
        .blog-card:hover { transform: none; }
    }
</style>
