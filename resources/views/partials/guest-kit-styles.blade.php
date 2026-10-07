{{-- The guest kit: the pieces every guest page is built from, as the form kit is for the admin
     forms (partials/form-kit-styles). Plain CSS on tokens, so no CSS build.

     Rules for anything added here:
       - one class per rule and no !important, so an owner's custom CSS (printed after this,
         like everything in this slot it follows the app's own stylesheet) wins a tie. The ids owners are promised (#gp-...) are not styled here at all.
       - sizes in rem, so the accessibility widget's text-size steps scale them.
       - colours from the tokens below. The schedule's own colours are the --es-accent family,
         printed by partials/guest-theme; the surfaces and inks are the --gk- family, which
         follow the page's light or dark mode.
       - logical properties (inline-start, not left), so a right-to-left schedule mirrors.
       - one set of timings (--gk-press, --gk-swap, --gk-open). Under reduced motion a state
         still changes, at once, and nothing moves.

     The classes are gk-, not es-: es-btn, es-panel and es-chip are the EMAIL components' names.
     A page's own pieces (the ticket, the event facts, the list row) are added below the shared
     ones as each page moves onto the kit. --}}
<style {!! nonce_attr() !!}>
    body {
        --gk-panel: rgb(255 255 255 / .95);
        --gk-solid: #ffffff;
        --gk-well: rgb(var(--ap-gray-100));
        --gk-ink: rgb(var(--ap-gray-900));
        --gk-ink-2: rgb(var(--ap-gray-700));
        --gk-ink-3: #5f6875;
        --gk-line: rgb(17 24 39 / .12);
        --gk-ok: #166534; --gk-ok-bg: #f0fdf4; --gk-ok-line: #86efac;
        --gk-warn: #92400e; --gk-warn-bg: #fffbeb; --gk-warn-line: #fcd34d;
        --gk-bad: #b91c1c; --gk-bad-bg: #fef2f2; --gk-bad-line: #fca5a5;
        --gk-shadow: 0 1px 2px rgb(15 23 42 / .07), 0 1px 1px rgb(15 23 42 / .04);
        --gk-shadow-lift: 0 4px 12px rgb(15 23 42 / .16);
        --gk-radius: 1rem;
        --gk-radius-ctl: .625rem;
        --gk-press: 50ms;
        --gk-swap: 120ms;
        --gk-open: 220ms;
    }
    :where(.dark) body {
        --gk-panel: rgb(30 30 30 / .95);
        --gk-solid: rgb(var(--ap-gray-900));
        --gk-well: rgb(var(--ap-gray-700));
        --gk-ink: rgb(var(--ap-gray-100));
        --gk-ink-2: rgb(var(--ap-gray-300));
        --gk-ink-3: rgb(var(--ap-gray-400));
        --gk-line: rgb(255 255 255 / .14);
        --gk-ok: #86efac; --gk-ok-bg: rgb(22 101 52 / .3); --gk-ok-line: rgb(134 239 172 / .4);
        --gk-warn: #fcd34d; --gk-warn-bg: rgb(146 64 14 / .3); --gk-warn-line: rgb(252 211 77 / .4);
        --gk-bad: #fca5a5; --gk-bad-bg: rgb(185 28 28 / .28); --gk-bad-line: rgb(252 165 165 / .4);
        --gk-shadow: 0 1px 2px rgb(0 0 0 / .4);
        --gk-shadow-lift: 0 4px 14px rgb(0 0 0 / .5);
    }

    {{-- A panel: the translucent card every section of a guest page sits in. --}}
    .gk-panel { background: var(--gk-panel); color: var(--gk-ink); border-radius: var(--gk-radius); box-shadow: var(--gk-shadow); -webkit-backdrop-filter: blur(6px); backdrop-filter: blur(6px); }
    {{-- Edge to edge on a phone, where the guest pages' panels have always run to both sides. --}}
    .gk-panel-flush { border-radius: 0; }
    @media (min-width: 40rem) { .gk-panel-flush { border-radius: var(--gk-radius); } }
    .gk-pad { padding: 1.125rem 1rem; }
    @media (min-width: 48rem) { .gk-pad { padding: 1.5rem 1.75rem; } }

    .gk-h { margin: 0 0 .75rem; font-size: 1.0625rem; font-weight: 700; line-height: 1.25; color: var(--gk-ink); }
    .gk-h-lg { font-size: 1.375rem; line-height: 1.2; }
    .gk-hint { margin-top: .5rem; font-size: .84375rem; color: var(--gk-ink-3); }
    .gk-err { margin-top: .375rem; font-size: .84375rem; font-weight: 600; color: var(--gk-bad); }

    {{-- A link in running text. Ink with an underline, never the accent: an accent is not guaranteed to read as text, and --es-accent-readable is for the few places that want it. --}}
    .gk-link { color: var(--gk-ink); font-weight: 600; text-decoration: underline; text-decoration-thickness: 1.5px; text-underline-offset: 3px; text-decoration-color: color-mix(in srgb, currentColor 40%, transparent); transition: text-decoration-color var(--gk-swap); }
    .gk-link:hover { text-decoration-color: currentColor; }

    {{-- The button. One primary per view: the schedule's colour. --}}
    .gk-btn { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; min-height: 2.75rem; padding: 0 1.125rem; border: 1px solid transparent; border-radius: var(--gk-radius-ctl); font-size: .9375rem; font-weight: 650; line-height: 1.2; text-align: center; text-decoration: none; cursor: pointer; transition: background-color var(--gk-swap), box-shadow var(--gk-swap), transform var(--gk-press), opacity var(--gk-swap); }
    .gk-btn:active { transform: scale(.985); }
    .gk-btn:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 2px; }
    .gk-btn-primary { background: var(--es-accent); color: var(--es-accent-text); border-color: var(--es-accent-edge); box-shadow: 0 1px 2px rgb(15 23 42 / .18); }
    .gk-btn-primary:hover { box-shadow: var(--gk-shadow-lift); }
    .gk-btn-secondary { background: var(--gk-solid); color: var(--gk-ink); border-color: var(--gk-line); }
    .gk-btn-secondary:hover { background: var(--gk-well); }
    .gk-btn-quiet { background: transparent; color: var(--gk-ink-2); padding: 0 .625rem; }
    .gk-btn-quiet:hover { background: var(--gk-well); }
    .gk-btn-lg { min-height: 3.25rem; padding: 0 1.5rem; border-radius: .75rem; font-size: 1.0625rem; }
    .gk-btn-sm { padding: 0 .875rem; font-size: .875rem; }
    .gk-btn-icon { width: 2.75rem; padding: 0; }
    .gk-btn-block { display: flex; width: 100%; }
    .gk-btn[disabled], .gk-btn[aria-disabled="true"] { opacity: .45; cursor: default; box-shadow: none; }
    @media (min-width: 64rem) { .gk-btn-sm { min-height: 2.375rem; } }

    {{-- A chip: one fact about an event, read at a glance. --}}
    .gk-chip { display: inline-flex; align-items: center; gap: .3125rem; min-height: 1.5rem; padding: 0 .5625rem; border: 1px solid transparent; border-radius: 999px; background: var(--gk-well); color: var(--gk-ink-2); font-size: .78125rem; font-weight: 650; line-height: 1.2; white-space: nowrap; }
    .gk-chip-free { background: var(--gk-ok-bg); color: var(--gk-ok); border-color: var(--gk-ok-line); }
    .gk-chip-few { background: var(--gk-warn-bg); color: var(--gk-warn); border-color: var(--gk-warn-line); }
    .gk-chip-out { background: var(--gk-bad-bg); color: var(--gk-bad); border-color: var(--gk-bad-line); }
    .gk-chip-accent { background: var(--es-accent-tint); color: var(--es-accent-readable); }

    {{-- A notice: something the visitor is told. A tinted ground and a full hairline, never a stripe down one side. --}}
    .gk-note { display: flex; align-items: flex-start; gap: .625rem; padding: .75rem .875rem; border: 1px solid var(--gk-line); border-radius: .75rem; background: var(--gk-well); color: var(--gk-ink); font-size: .90625rem; line-height: 1.4; }
    .gk-note-icon { flex: none; width: 1.25rem; height: 1.25rem; margin-top: .0625rem; }
    .gk-note-body { flex: 1 1 auto; min-width: 0; }
    .gk-note-ok { background: var(--gk-ok-bg); border-color: var(--gk-ok-line); color: var(--gk-ok); }
    .gk-note-warn { background: var(--gk-warn-bg); border-color: var(--gk-warn-line); color: var(--gk-warn); }
    .gk-note-bad { background: var(--gk-bad-bg); border-color: var(--gk-bad-line); color: var(--gk-bad); }

    {{-- No size or padding here: layouts/app forces both on every text field (the 16px that
         stops a phone zooming in), and a rule that cannot apply should not be written. --}}
    .gk-input { min-height: 2.875rem; border: 1px solid var(--gk-line); border-radius: var(--gk-radius-ctl); background: var(--gk-solid); color: var(--gk-ink); }
    .gk-input::placeholder { color: var(--gk-ink-3); }
    .gk-input:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 1px; }
    .gk-input-bad { border-color: var(--gk-bad); box-shadow: 0 0 0 1px var(--gk-bad); }

    {{-- The other days of a series, under the When row and in line with its text (the tile is
         4rem and the row's gap 1rem). --}}
    .gk-more-dates { margin-top: -.75rem; padding-inline-start: 5rem; }

    {{-- The foot of a form: its actions, kept at the bottom of the screen while the form is longer
         than it. It bleeds to the edges of the panel it sits in (1.25rem of padding on a phone,
         2rem from a tablet up), so it reads as the panel's own foot. --}}
    .gk-buybar { position: sticky; bottom: 0; z-index: 5; margin: 1.25rem -1.25rem -1.5rem; padding: .75rem 1.25rem calc(.75rem + env(safe-area-inset-bottom, 0px)); border-top: 1px solid var(--gk-line); background: var(--gk-solid); }
    @media (min-width: 40rem) { .gk-buybar { margin: 1.5rem -2rem -2rem; padding: 1rem 2rem; border-radius: 0 0 1rem 1rem; } }
    {{-- Floating over the form it is a plain strip with a little shadow; the rounded corners are
         the panel's, and belong to it only at rest (the page sets the attribute). :where() so
         this weighs what every other rule here weighs, one class. --}}
    :where([data-buybar-floating]) .gk-buybar { border-radius: 0; box-shadow: 0 -.5rem 1.125rem -.75rem rgb(0 0 0 / .28); }

    {{-- A list of events as rows: when, what, where, and a small picture. A row is a link. --}}
    .gk-dayhead { display: flex; align-items: baseline; gap: .5rem; padding: .75rem 1rem; border-bottom: 1px solid var(--gk-line); }
    .gk-dayhead h2 { margin: 0; font-size: 1rem; font-weight: 700; line-height: 1.25; color: var(--gk-ink); }
    .gk-dayhead-link { margin-inline-start: auto; font-size: .875rem; }
    .gk-rows { margin: 0; padding: 0; list-style: none; }
    .gk-rows li + li { border-top: 1px solid var(--gk-line); }
    .gk-row { display: grid; grid-template-columns: minmax(0, 1fr) 4.75rem; grid-template-areas: "t i" "b i"; grid-template-rows: auto 1fr; column-gap: .75rem; row-gap: .125rem; padding: .75rem 1rem; color: var(--gk-ink); text-decoration: none; transition: background-color var(--gk-swap); }
    .gk-row-bare { grid-template-columns: minmax(0, 1fr); grid-template-areas: "t" "b"; }
    .gk-row:hover { background: var(--gk-well); }
    .gk-row:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: -2px; }
    .gk-row-time { grid-area: t; font-size: .84375rem; font-weight: 700; color: var(--gk-ink-2); font-variant-numeric: tabular-nums; }
    .gk-row-time span { margin-inline-start: .375rem; font-weight: 500; color: var(--gk-ink-3); }
    .gk-row-body { grid-area: b; display: flex; flex-direction: column; gap: .1875rem; min-width: 0; }
    .gk-row-title { display: -webkit-box; overflow: hidden; -webkit-box-orient: vertical; -webkit-line-clamp: 3; font-size: 1.03125rem; font-weight: 700; line-height: 1.25; }
    .gk-row-where { display: -webkit-box; overflow: hidden; -webkit-box-orient: vertical; -webkit-line-clamp: 2; font-size: .875rem; color: var(--gk-ink-3); }
    .gk-row-img { grid-area: i; align-self: center; width: 4.75rem; height: 4.75rem; border-radius: .625rem; object-fit: cover; }

    {{-- The event page's frame. Two columns from a laptop up, as the page has always been: pictures,
         performers and the venue beside the facts and the story. On a phone the two columns are
         dealt into ONE in the order a visitor needs them (picture, facts, form, about, agenda,
         performers, venue, photos and comments), which is what the order rules are for: a column
         is display:contents there, so its children are the items. Anything in a column without a
         place of its own falls at the end of its column's part of the page. --}}
    .gk-event-page { max-width: 70rem; }
    .gk-event { display: flex; flex-direction: column; gap: 1rem; }
    .gk-event-col { display: contents; }
    .gk-event-side > * { order: 7; }
    .gk-event-main > * { order: 9; }
    .gk-event-flat > * { order: 6; }
    .gk-o1 { order: 1; }
    .gk-o2 { order: 2; }
    .gk-o3 { order: 3; }
    .gk-o4 { order: 4; }
    .gk-o5 { order: 5; }
    .gk-o6 { order: 6; }
    .gk-o8 { order: 8; }
    .gk-o10 { order: 10; }
    {{-- The schedule's other upcoming events, down the event page's left column (event/partials/
         more-events): a date between two lines, a small card for each event of that day. --}}
    .gk-up { padding: 1rem; }
    .gk-up-title { margin: 0 0 .25rem; font-size: .9375rem; font-weight: 600; color: var(--gk-ink); }
    .gk-up-list { display: flex; flex-direction: column; gap: .625rem; margin: 0; padding: 0; list-style: none; }
    .gk-up-day { display: flex; align-items: center; gap: .75rem; margin-top: .625rem; font-size: .875rem; font-weight: 600; color: var(--gk-ink); text-align: center; }
    .gk-up-day::before { content: ""; flex: 1; height: 1px; background: var(--gk-line); }
    .gk-up-day::after { content: ""; flex: 1; height: 1px; background: var(--gk-line); }
    .gk-up-card { display: grid; grid-template-columns: minmax(0, 1fr); overflow: hidden; border: 1px solid var(--gk-line); border-radius: .75rem; background: var(--gk-solid); color: var(--gk-ink); text-decoration: none; box-shadow: var(--gk-shadow); transition: box-shadow var(--gk-swap), transform var(--gk-swap); }
    .gk-up-pictured { grid-template-columns: minmax(0, 1fr) 42%; }
    .gk-up-card:hover { box-shadow: var(--gk-shadow-lift); transform: translateY(-1px); }
    .gk-up-card:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 2px; }
    .gk-up-body { display: flex; flex-direction: column; gap: .25rem; min-width: 0; padding: .75rem .875rem; }
    .gk-up-name { display: -webkit-box; overflow: hidden; -webkit-box-orient: vertical; -webkit-line-clamp: 3; font-size: .9375rem; font-weight: 700; line-height: 1.25; }
    .gk-up-meta { display: flex; align-items: flex-start; gap: .375rem; font-size: .8125rem; line-height: 1.3; color: var(--gk-ink-3); }
    .gk-up-meta svg { flex: none; width: .875rem; height: .875rem; margin-top: .0625rem; }
    .gk-up-img { width: 100%; height: 100%; min-height: 5.5rem; object-fit: cover; }
    .gk-up-all { display: block; margin-top: .875rem; font-size: .875rem; text-align: center; }
    @media (max-width: 47.99rem) { .gk-up-late { display: none; } }
    .gk-event-foot { display: grid; gap: 1rem; margin-top: 1rem; }
    .gk-create { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .75rem; padding: .875rem 1rem; }
    @media (min-width: 64rem) {
        .gk-event { display: grid; grid-template-columns: 23.75rem minmax(0, 1fr); gap: 2.5rem; align-items: start; }
        .gk-event-col { display: flex; flex-direction: column; gap: 1rem; min-width: 0; }
        .gk-event-main { gap: 1.5rem; }
        .gk-event-col > *, .gk-event-flat > * { order: 0; }
        .gk-event-foot { margin-top: 2.5rem; }
    }

    @media (prefers-reduced-motion: reduce) {
        .gk-btn, .gk-link { transition-duration: 1ms; }
        .gk-btn:active { transform: none; }
    }

    @media print {
        {{-- .dark as well: a panel that kept its dark:bg- utility would outrank the single class
             and print black text on a dark ground. --}}
        .gk-panel, .dark .gk-panel { background: #ffffff; color: #000000; box-shadow: none; -webkit-backdrop-filter: none; backdrop-filter: none; border: 1px solid #d1d5db; }
        .gk-btn { display: none; }
        .gk-note { background: #ffffff; color: #000000; border-color: #9ca3af; }
    }
</style>
