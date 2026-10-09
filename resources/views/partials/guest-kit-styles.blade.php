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

    {{-- The event's own facts (the custom fields its schedule put on the event page), beside one
         tile like the When and Where rows. One fact reads as those rows do: the answer, then
         what it is, which the reversed column gives while the label stays first in the markup
         (.gk-fact-flag is a lone switch or a lone date, which leads with its name).
         Several are a list: on a phone each label over its answer, a full rem between pairs so a
         label sits nearer its own answer than the one above; from 40rem the labels are a column
         of their own, each answer on its label's baseline. That column stops at 9rem and a
         longer label wraps: sized to the longest label, one long question pushed every answer
         away from a short one. A list no taller than the tile is centred on it (the 4rem is the
         tile's own height). An answer keeps its line breaks, and a word with no break in it (an
         address, a code) wraps rather than widening the card. --}}
    .gk-facts { display: grid; gap: .5rem; min-width: 0; margin: 0; }
    .gk-fact { display: flex; flex-direction: column-reverse; min-width: 0; }
    .gk-fact-flag { display: flex; flex-direction: column; min-width: 0; }
    .gk-facts-pairs { gap: 1rem; min-height: 4rem; align-content: center; }
    .gk-fact-pair { display: flex; flex-direction: column; min-width: 0; }
    .gk-fact-text { margin: 0; min-width: 0; overflow-wrap: anywhere; white-space: pre-line; }
    @media (min-width: 40rem) {
        .gk-facts-pairs { grid-template-columns: fit-content(9rem) minmax(0, 1fr); gap: .375rem 1rem; align-items: baseline; }
        .gk-fact-pair { display: contents; }
    }

    {{-- The foot of a form: its actions, kept at the bottom of the screen while the form is longer
         than it. It bleeds to the edges of the panel it sits in (1.25rem of padding on a phone,
         2rem from a tablet up), so it reads as the panel's own foot. --}}
    .gk-buybar { position: sticky; bottom: 0; z-index: 5; margin: 1.25rem -1.25rem -1.5rem; padding: .75rem 1.25rem calc(.75rem + env(safe-area-inset-bottom, 0px)); border-top: 1px solid var(--gk-line); background: var(--gk-solid); }
    @media (min-width: 40rem) { .gk-buybar { margin: 1.5rem -2rem -2rem; padding: 1rem 2rem; border-radius: 0 0 1rem 1rem; } }
    {{-- Floating over the form it is a plain strip with a little shadow; the rounded corners are
         the panel's, and belong to it only at rest (the page sets the attribute). :where() so
         this weighs what every other rule here weighs, one class. --}}
    :where([data-buybar-floating]) .gk-buybar { border-radius: 0; box-shadow: 0 -.5rem 1.125rem -.75rem rgb(0 0 0 / .28); }

    {{-- A list of events as rows, on a phone: when, what, where, and a small picture. --}}
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
    .gk-row-title { font-size: 1.03125rem; font-weight: 700; line-height: 1.25; }
    .gk-row-where { display: -webkit-box; overflow: hidden; -webkit-box-orient: vertical; -webkit-line-clamp: 2; margin: 0; font-size: .875rem; color: var(--gk-ink-3); }
    .gk-row-img { display: block; width: 100%; height: 100%; object-fit: cover; }
    {{-- On a wide phone (40rem to 48rem, where the cards take over) the time has a column of
         its own, so a day reads down its left edge. --}}
    @media (min-width: 40rem) {
        .gk-row { grid-template-columns: 9.5rem minmax(0, 1fr) 4.75rem; grid-template-areas: "t b i"; grid-template-rows: auto; align-items: center; column-gap: 1rem; padding: .875rem 1.25rem; }
        .gk-row-bare { grid-template-columns: 9.5rem minmax(0, 1fr); grid-template-areas: "t b"; }
        .gk-row-time span { display: block; margin-inline-start: 0; }
        .gk-dayhead { padding: .75rem 1.25rem; }
    }

    {{-- The schedule's next event, above its month (role/show-guest). One link: a picture in a
         box of its own shape, then when, what and where. --}}
    .gk-lead { display: grid; overflow: hidden; max-width: 46rem; margin-inline: auto; color: var(--gk-ink); text-decoration: none; transition: box-shadow var(--gk-swap, 120ms) ease; }
    .gk-lead:hover { box-shadow: var(--gk-shadow-lift); }
    .gk-lead:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 2px; }
    .gk-lead[hidden] { display: none; }
    .gk-lead-img { display: block; width: 100%; height: auto; aspect-ratio: 16 / 9; object-fit: cover; }
    .gk-lead-body { display: flex; flex-direction: column; gap: .3125rem; min-width: 0; padding: 1rem; }
    .gk-lead-when { font-size: .875rem; font-weight: 600; color: var(--gk-ink-2); }
    .gk-lead-when b { margin-inline-end: .375rem; font-size: .75rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--es-accent-readable); }
    .gk-lead-title { font-size: 1.375rem; font-weight: 800; line-height: 1.2; overflow-wrap: anywhere; }
    .gk-lead-where { display: flex; flex-wrap: wrap; column-gap: .625rem; font-size: .9375rem; color: var(--gk-ink-3); }
    @media (min-width: 40rem) {
        .gk-lead { grid-template-columns: minmax(0, 5fr) minmax(0, 6fr); align-items: center; }
        .gk-lead-bare { grid-template-columns: minmax(0, 1fr); }
        .gk-lead-img { height: 100%; aspect-ratio: 4 / 3; }
        .gk-lead-body { padding: 1.25rem 1.5rem; }
        .gk-lead-title { font-size: 1.625rem; }
    }

    {{-- The month on a phone (role/partials/calendar): seven columns of days, a count under a
         day that has events. Picking one folds the month to a single line. --}}
    .gk-month { margin-bottom: .875rem; padding: .625rem; }
    .gk-month-title { display: flex; align-items: center; justify-content: space-between; margin-bottom: .375rem; font-size: 1rem; font-weight: 700; color: var(--gk-ink); }
    .gk-month-nav { display: grid; place-items: center; width: 2.75rem; height: 2.75rem; border: 0; border-radius: .625rem; background: none; color: var(--gk-ink-2); cursor: pointer; }
    .gk-month-nav svg { width: 1.5rem; height: 1.5rem; }
    .gk-month-nav:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: -2px; }
    {{-- Put away on a phone, where the month panel has its own (the laptop's month buttons). --}}
    @media (max-width: 47.99rem) { .gk-phone-off { display: none; } }
    .gk-month-head { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); margin-bottom: .25rem; font-size: .6875rem; font-weight: 700; text-align: center; text-transform: uppercase; color: var(--gk-ink-3); }
    .gk-month-grid { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: .125rem; }
    .gk-month-day { display: grid; place-items: center; align-content: center; gap: .0625rem; min-height: 2.75rem; padding: 0; border: 0; border-radius: .625rem; background: none; color: var(--gk-ink-3); font: inherit; }
    .gk-month-day b { font-size: .9375rem; font-weight: 600; line-height: 1.1; }
    .gk-month-day i { font-size: .625rem; font-style: normal; font-weight: 800; line-height: 1; color: var(--es-accent-readable); }
    .gk-month-day:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: -2px; }
    .gk-month-has { background: var(--es-accent-tint); color: var(--gk-ink); cursor: pointer; }
    .gk-month-today { box-shadow: inset 0 0 0 2px var(--es-accent-readable); }
    .gk-month-past { opacity: .55; }
    .gk-month-fold { display: flex; align-items: center; justify-content: space-between; gap: .75rem; width: 100%; min-height: 2.75rem; padding: 0 .375rem; border: 0; background: none; color: var(--gk-ink); font: inherit; font-weight: 700; text-align: start; cursor: pointer; }
    .gk-month-fold span + span { font-size: .8125rem; font-weight: 600; color: var(--gk-ink-3); text-decoration: underline; text-underline-offset: 3px; }
    .gk-month-earlier { display: block; margin: 0 auto .875rem; padding: .375rem .875rem; border: 0; border-radius: 999px; background: var(--gk-solid); color: var(--gk-ink-2); font: inherit; font-size: .875rem; font-weight: 600; cursor: pointer; }

    {{-- The chips above the schedule's list. One row that scrolls sideways on a phone, and
         never wraps into a block that pushes the first event down the page. --}}
    .gk-pills { display: flex; gap: .5rem; overflow-x: auto; max-width: 46rem; margin: 0 auto .75rem; padding: .125rem; scrollbar-width: none; -webkit-overflow-scrolling: touch; }
    .gk-pills::-webkit-scrollbar { display: none; }
    .gk-pills[v-cloak] { display: none; }
    .gk-pill { display: inline-flex; flex: none; align-items: center; min-height: 2.25rem; padding: 0 .875rem; border: 1px solid var(--gk-line); border-radius: 999px; background: var(--gk-solid); color: var(--gk-ink); font: inherit; font-size: .875rem; font-weight: 600; white-space: nowrap; cursor: pointer; transition: background-color var(--gk-swap, 120ms) ease, color var(--gk-swap, 120ms) ease; }
    .gk-pill:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 2px; }
    .gk-pill-on { border-color: var(--es-accent-edge, transparent); background: var(--es-accent); color: var(--es-accent-text); }

    {{-- The schedule's list on a phone, and the days under a phone's month: one column of day
         panels, a row for each event (role/partials/calendar, guest route). --}}
    .gk-list { max-width: 46rem; margin-inline: auto; }
    .gk-days { display: grid; gap: .875rem; }
    {{-- Rounded (the list sits inside the page's own gutters), and clipped, so a
         row's hover tint keeps to the panel's corners. --}}
    .gk-day { overflow: hidden; }
    .gk-dayhead-title { margin: 0; font-size: 1rem; font-weight: 700; line-height: 1.25; color: var(--gk-ink); }
    .gk-month-none { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .75rem; color: var(--gk-ink-2); }
    .gk-row-where a { color: inherit; text-decoration: underline; text-decoration-color: var(--gk-line); text-underline-offset: 2px; }
    .gk-dayhead-word { font-size: .75rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--es-accent-readable); }
    {{-- Where the list turns from what is coming to what has been (role/partials/calendar). The
         label stands on the panel's own frosted ground and each rule is a light line over a dark
         one, so both read on whatever the owner put behind the list: a grey hairline vanished on
         one photograph and scratched across the next, and plain text was dark on a dark page. --}}
    .gk-past { display: flex; align-items: center; gap: 1rem; padding-block: .75rem 0; }
    .gk-past::before { content: ''; flex: 1; height: 2px; background: linear-gradient(rgb(255 255 255 / .62), rgb(255 255 255 / .62)) top / 100% 1px no-repeat, linear-gradient(rgb(0 0 0 / .32), rgb(0 0 0 / .32)) bottom / 100% 1px no-repeat; -webkit-mask-image: linear-gradient(to right, transparent, #000 30%, #000 70%, transparent); mask-image: linear-gradient(to right, transparent, #000 30%, #000 70%, transparent); }
    .gk-past::after { content: ''; flex: 1; height: 2px; background: linear-gradient(rgb(255 255 255 / .62), rgb(255 255 255 / .62)) top / 100% 1px no-repeat, linear-gradient(rgb(0 0 0 / .32), rgb(0 0 0 / .32)) bottom / 100% 1px no-repeat; -webkit-mask-image: linear-gradient(to right, transparent, #000 30%, #000 70%, transparent); mask-image: linear-gradient(to right, transparent, #000 30%, #000 70%, transparent); }
    .gk-past-label { display: inline-flex; align-items: center; gap: .4375rem; padding: .4375rem .875rem; border-radius: 999px; background: var(--gk-panel); color: var(--gk-ink-2); font-size: .75rem; font-weight: 800; letter-spacing: .08em; line-height: 1.2; text-transform: uppercase; white-space: nowrap; -webkit-backdrop-filter: blur(6px); backdrop-filter: blur(6px); box-shadow: var(--gk-shadow); }
    {{-- Spaced letters pull an Arabic word apart. --}}
    :where([dir="rtl"]) .gk-past-label, :where(.rtl) .gk-past-label { letter-spacing: 0; }
    .gk-past-label svg { flex: none; width: .9375rem; height: .9375rem; }
    .gk-row-item { position: relative; }
    {{-- A row of the schedule's list is a card pressed as a whole; its name is the link. --}}
    .gk-row-press { cursor: pointer; }
    .gk-row-title h3 { display: -webkit-box; overflow: hidden; -webkit-box-orient: vertical; -webkit-line-clamp: 3; margin: 0; font: inherit; overflow-wrap: anywhere; }
    .gk-row-title a { color: inherit; text-decoration: none; }
    .gk-row-title a:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 2px; border-radius: .25rem; }
    .gk-row-desc { display: -webkit-box; overflow: hidden; -webkit-box-orient: vertical; -webkit-line-clamp: 2; margin: 0; font-size: .875rem; color: var(--gk-ink-2); }
    .gk-row-media { grid-area: i; align-self: center; overflow: hidden; width: 4.75rem; height: 4.75rem; border-radius: .625rem; }
    .gk-row-media a { display: block; width: 100%; height: 100%; }
    .gk-row-dot { display: inline-block; width: .5rem; height: .5rem; margin-inline-end: .375rem; border-radius: 999px; vertical-align: .0625rem; }
    .gk-row-lock { display: inline-block; width: 1rem; height: 1rem; margin-inline-end: .375rem; vertical-align: -.125rem; color: var(--gk-ink-3); }
    .gk-row-chips { display: flex; flex-wrap: wrap; gap: .375rem; margin-top: .125rem; }
    .gk-row-edit { display: inline-block; margin: -.25rem 1rem .625rem; font-size: .8125rem; color: var(--gk-ink-3); text-decoration: underline; text-underline-offset: 3px; }
    {{-- A day that is over is quieter, and its pictures lose their colour, as they always did. --}}
    :where(.gk-day-past) .gk-row-img { filter: grayscale(1); }
    :where(.gk-day-past) .gk-row-title { color: var(--gk-ink-2); }

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
         more-events): the day panels and rows of the schedule's own phone list (.gk-day, .gk-row),
         under a slim panel that names them and leads to the whole schedule. --}}
    .gk-up { display: grid; gap: .875rem; }
    .gk-up-head { display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between; gap: .25rem .75rem; padding: .75rem 1rem; }
    .gk-up-title { margin: 0; font-size: 1rem; font-weight: 700; line-height: 1.25; color: var(--gk-ink); }
    .gk-up-all { font-size: .875rem; }
    @media (min-width: 40rem) { .gk-up-head { padding: .75rem 1.25rem; } }
    {{-- Its rows stay stacked at every width (the time over the name, the picture beside them),
         where the schedule's own rows give the time a column from 40rem: from 64rem this list is
         in a column 23.75rem wide, and below that it is a short list at the end of the page.
         Declared after that rule, which it undoes. A name is cut at three lines, as a row's
         heading is, and a word too long for the column breaks rather than running under the
         picture. --}}
    .gk-row-stack { grid-template-columns: minmax(0, 1fr) 4.75rem; grid-template-areas: "t i" "b i"; grid-template-rows: auto 1fr; align-items: start; column-gap: .75rem; }
    .gk-row-stack-bare { grid-template-columns: minmax(0, 1fr); grid-template-areas: "t" "b"; }
    .gk-row-name { display: -webkit-box; overflow: hidden; -webkit-box-orient: vertical; -webkit-line-clamp: 3; overflow-wrap: anywhere; }
    {{-- Wherever the page is ONE column (below 64rem, see .gk-event) the list is at its end, and
         stops after five. Cut below 48rem only, a tablet got all twenty there. --}}
    @media (max-width: 63.99rem) { .gk-up-late { display: none; } }
    .gk-event-foot { display: grid; gap: 1rem; margin-top: 1rem; }
    .gk-create { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .75rem; padding: .875rem 1rem; }
    @media (min-width: 64rem) {
        .gk-event { display: grid; grid-template-columns: 23.75rem minmax(0, 1fr); gap: 2.5rem; align-items: start; }
        .gk-event-col { display: flex; flex-direction: column; gap: 1rem; min-width: 0; }
        .gk-event-main { gap: 1.5rem; }
        .gk-event-col > *, .gk-event-flat > * { order: 0; }
        .gk-event-foot { margin-top: 2.5rem; }
    }

    {{-- The schedule's header (role/partials/headers/banner): a card on the owner's page. Its
         picture across the top, or a wash of the schedule's colour where the owner chose one; the
         logo; the name at display size in the owner's own typeface; one main button; a line of
         facts with the socials and the list's tools at its far end; the description folded to
         its first lines. States are told by a class on the card, read through :where() so
         every rule here still weighs one class and an owner's CSS wins a tie. --}}
    .gk-head { --gk-head-pad: 1.25rem; --gk-head-logo: 5.25rem; --gk-head-name: clamp(2rem, 1.15rem + 3.6vw, 3.25rem); position: relative; border-radius: 1.5rem; background: var(--gk-panel); color: var(--gk-ink); font-size: 1rem; line-height: 1.5; text-align: start; -webkit-backdrop-filter: blur(.625rem); backdrop-filter: blur(.625rem); box-shadow: 0 1px 2px rgb(15 23 42 / .08), 0 1.25rem 2.5rem -1.5rem rgb(15 23 42 / .45); }
    @media (min-width: 48rem) { .gk-head { --gk-head-pad: 2.5rem; --gk-head-logo: 6.5rem; } }
    .gk-head-stage { position: absolute; inset: 0 0 auto; height: 11rem; overflow: hidden; border-radius: 1.5rem 1.5rem 0 0; pointer-events: none; }
    {{-- Where the owner chose it over a picture (Header Image: Accent color gradient): one even
         light in the schedule's colour, deepest at the top edge and gone behind the name.
         --es-glow is the accent as a light (three numbers), so this needs no color-mix(), which
         an older browser drops with the whole declaration. --}}
    :where(.gk-head-washed) .gk-head-stage { background: linear-gradient(to bottom, rgb(var(--es-glow) / .44), rgb(var(--es-glow) / .17) 38%, rgb(var(--es-glow) / 0)); }
    {{-- A picture is shown as it is: nothing is written on it, so nothing is laid over it. --}}
    :where(.gk-head-pictured) .gk-head-stage { position: relative; inset: auto; height: clamp(9.5rem, 31vw, 17rem); background: #111111; pointer-events: auto; }
    :where(.gk-head-walled) .gk-head-stage { position: relative; inset: auto; height: auto; overflow: visible; background: none; pointer-events: auto; }
    .gk-head-stage picture { display: block; height: 100%; }
    .gk-head-picture { display: block; width: 100%; height: 100%; object-fit: cover; }

    .gk-head-body { position: relative; padding: 1.75rem var(--gk-head-pad) 1.25rem; }
    :where(.gk-head-pictured) .gk-head-body, :where(.gk-head-walled) .gk-head-body { padding-top: 0; }
    {{-- The two ids owners were given for the header's contents (the header was once drawn
         twice, for a phone and for a laptop) are kept on two wrappers that take no box. --}}
    .gk-head-wrap { display: contents; }
    .gk-head-top { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: .5rem 1rem; min-height: 2.75rem; }
    {{-- Over a picture or the logo wall, the logo stands on its lower edge. --}}
    :where(.gk-head-pictured.gk-head-logoed) .gk-head-top, :where(.gk-head-walled.gk-head-logoed) .gk-head-top { margin-top: calc(var(--gk-head-logo) * -.58); }
    :where(.gk-head-pictured:not(.gk-head-logoed)) .gk-head-top, :where(.gk-head-walled:not(.gk-head-logoed)) .gk-head-top { padding-top: 1rem; }
    .gk-head-logo { flex: none; overflow: hidden; width: var(--gk-head-logo); height: var(--gk-head-logo); border-radius: 22%; background: var(--gk-solid); box-shadow: 0 0 0 .25rem var(--gk-solid), 0 .625rem 1.5rem -.5rem rgb(0 0 0 / .45); }
    :where(.dark) .gk-head-logo { box-shadow: 0 0 0 .25rem var(--gk-solid), 0 0 0 calc(.25rem + 1px) rgb(255 255 255 / .16), 0 .625rem 1.5rem -.5rem rgb(0 0 0 / .6); }
    .gk-head-logo img { display: block; width: 100%; height: 100%; object-fit: cover; }

    {{-- Beside the logo: the one main button, Share, and Manage for a member. Whatever else a
         visitor may do is a second row under the description. --}}
    .gk-head-actions { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: .5rem; min-width: 0; margin-inline-start: auto; }
    :where(.gk-head-pictured.gk-head-logoed) .gk-head-actions, :where(.gk-head-walled.gk-head-logoed) .gk-head-actions { padding-top: calc(var(--gk-head-logo) * .58 + .875rem); }
    .gk-head-more-actions { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; min-width: 0; margin-top: 1rem; }
    .gk-head-ico { flex: none; width: 1.125rem; height: 1.125rem; }
    .gk-head-follow { padding-inline: 1.25rem 1.375rem; font-weight: 700; white-space: nowrap; }
    {{-- Manage is drawn twice, beside the main button for a laptop and in the second row for a
         phone, where three buttons do not fit beside a logo. --}}
    .gk-head-manage-phone { display: none; }
    .gk-head-more-actions-phone { display: none; }
    .gk-head-following { display: inline-flex; align-items: center; gap: .375rem; white-space: nowrap; }
    .gk-head-share { position: relative; }
    .gk-head-said { position: absolute; inset-block-end: calc(100% + .5rem); inset-inline-end: 0; padding: .3125rem .625rem; border-radius: .5rem; background: rgb(17 24 39 / .95); color: #ffffff; font-size: .8125rem; font-weight: 600; white-space: nowrap; pointer-events: none; }
    .gk-head-said:empty { display: none; }

    {{-- font-synthesis: a display face with one weight is drawn as it was cut, where
         font-semibold used to thicken it artificially. A long name steps down (the class is
         the server's, from the name's length), so it never runs to four lines. --}}
    .gk-head-name { margin: .75rem 0 0; font-size: var(--gk-head-name); font-weight: 700; font-synthesis: none; line-height: 1.06; color: var(--gk-ink); text-wrap: balance; overflow-wrap: anywhere; }
    .gk-head-name-m { font-size: calc(var(--gk-head-name) * .86); }
    .gk-head-name-s { font-size: calc(var(--gk-head-name) * .74); }
    .gk-head-tagline { margin: .5rem 0 0; font-size: 1.0625rem; font-weight: 500; color: var(--gk-ink-2); text-wrap: pretty; }

    {{-- Each fact has its own mark, so facts are parted by space alone: a dot between them was
         left hanging at the start of a wrapped line. --}}
    .gk-head-meta { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem 1.5rem; margin-top: .625rem; }
    .gk-head-facts { display: flex; flex-wrap: wrap; align-items: center; gap: .25rem 1.125rem; margin: 0; padding: 0; list-style: none; font-size: .9375rem; font-weight: 500; color: var(--gk-ink-2); }
    .gk-head-fact { display: inline-flex; align-items: center; gap: .375rem; }
    .gk-head-fact svg { flex: none; width: 1rem; height: 1rem; opacity: .7; }
    .gk-head-fact a { color: inherit; text-decoration: underline; text-decoration-color: var(--gk-line); text-underline-offset: 3px; }
    .gk-head-fact a:hover { text-decoration-color: currentColor; }
    .gk-head-fact b { font-weight: 700; color: var(--gk-ink); }
    .gk-head-side { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem .75rem; margin-inline-start: auto; }
    .gk-head-social { display: flex; flex-wrap: wrap; align-items: center; gap: .125rem; }
    .gk-head-social-link { display: grid; place-items: center; width: 2.25rem; height: 2.25rem; border-radius: 50%; color: var(--gk-ink-2); transition: background-color var(--gk-swap), color var(--gk-swap); }
    .gk-head-social-link:hover { background: var(--gk-well); color: var(--gk-ink); }
    .gk-head-social-link:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 1px; }
    .gk-head-social-link svg { width: 1.1875rem; height: 1.1875rem; }

    {{-- The list's own tools: the filter and the view switch. Grey, so Follow is the only
         thing here in the schedule's colour; the badge alone says a filter is on. The laptop's
         and the phone's filter are two buttons (they open different panels), shown by turns. --}}
    .gk-head-tools { display: flex; flex: none; align-items: center; gap: .5rem; }
    .gk-head-tool { position: relative; display: inline-flex; align-items: center; justify-content: center; gap: .4375rem; height: 2.375rem; min-width: 2.375rem; padding: 0 .75rem; border: 1px solid var(--gk-line); border-radius: .75rem; background: var(--gk-solid); color: var(--gk-ink); font: inherit; font-size: .875rem; font-weight: 650; white-space: nowrap; cursor: pointer; transition: background-color var(--gk-swap); }
    .gk-head-tool:hover { background: var(--gk-well); }
    .gk-head-tool:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 2px; }
    .gk-head-tool svg { width: 1rem; height: 1rem; }
    .gk-head-tool-phone { display: none; }
    .gk-head-badge { display: inline-grid; place-items: center; min-width: 1.125rem; height: 1.125rem; padding: 0 .25rem; border-radius: 999px; background: var(--es-accent); color: var(--es-accent-text); font-size: .6875rem; font-weight: 800; line-height: 1; }
    .gk-head-badge[hidden] { display: none; }
    .gk-head-seg { display: inline-flex; padding: .125rem; border: 1px solid var(--gk-line); border-radius: .75rem; background: var(--gk-well); }
    .gk-head-seg-btn { display: grid; place-items: center; width: 2.25rem; height: 2rem; border: 0; border-radius: .5625rem; background: none; color: var(--gk-ink-2); cursor: pointer; transition: background-color var(--gk-swap), color var(--gk-swap); }
    .gk-head-seg-btn svg { width: 1.125rem; height: 1.125rem; }
    .gk-head-seg-btn:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 1px; }
    .gk-head-seg-btn[aria-pressed="true"] { background: var(--gk-solid); color: var(--gk-ink); box-shadow: 0 1px 2px rgb(0 0 0 / .18); }

    {{-- The description. Folded, its first lines are a summary in running text: a heading the
         owner typed is left out of them (it returns with More), where it used to stand larger
         than everything but the name. The fold is in the stylesheet, not added by script, so
         the full text never flashes. --}}
    .gk-head-about { max-width: 46rem; margin-top: .75rem; font-size: .9375rem; line-height: 1.5; color: var(--gk-ink-2); }
    .gk-head-about-text { display: -webkit-box; overflow: hidden; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }
    .gk-head-about-text hr { margin-block: .75rem; border: 0; border-top: 1px solid var(--gk-line); }
    :where(.gk-head-about[data-open]) .gk-head-about-text { display: block; }
    :where(.gk-head-about:not([data-open])) .gk-head-about-text :is(p, ul, ol, blockquote, li) { display: inline; margin: 0; padding: 0; border: 0; font-size: inherit; font-weight: inherit; line-height: inherit; }
    :where(.gk-head-about:not([data-open])) .gk-head-about-text :is(p, li, blockquote)::after { content: ' '; }
    :where(.gk-head-about:not([data-open])) .gk-head-about-text :is(h2, h3, h4, h5, h6, hr, img, table, pre, br) { display: none; }
    :where(.gk-head-about:not([data-open])) .gk-head-about-text strong { font-weight: 600; color: var(--gk-ink); }
    .gk-head-more { display: inline-flex; align-items: center; gap: .25rem; margin-top: .25rem; padding: 0; border: 0; background: none; color: var(--gk-ink); font: inherit; font-size: .875rem; font-weight: 700; cursor: pointer; }
    .gk-head-more[hidden] { display: none; }
    .gk-head-more svg { width: .875rem; height: .875rem; transition: transform var(--gk-open); }
    .gk-head-more[aria-expanded="true"] svg { transform: rotate(180deg); }
    .gk-head-more:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 2px; border-radius: .25rem; }

    @media (max-width: 47.99rem) {
        .gk-head-side { justify-content: space-between; width: 100%; margin-inline-start: 0; }
        {{-- The first icon's own edge, not its 2.25rem box, lines up with the text above it. --}}
        .gk-head-social { margin-inline-start: -.5rem; }
        .gk-head-about-text { -webkit-line-clamp: 3; }
        .gk-head-seg { display: none; }
        .gk-head-tool-desk { display: none; }
        .gk-head-tool-phone { display: inline-flex; }
        :where(.gk-head-more-actions) .gk-btn { flex: 1 1 calc(50% - .25rem); }
        .gk-head-manage-desk { display: none; }
        .gk-head-manage-phone { display: inline-flex; }
        .gk-head-more-actions-phone { display: flex; }
        {{-- A phone's page background is often a picture that ends just under this card: a
             long shadow fell across that edge. --}}
        .gk-head { box-shadow: 0 1px 2px rgb(15 23 42 / .1), 0 .375rem .75rem -.5rem rgb(15 23 42 / .4); }
    }

    {{-- The slim bar that takes over once the header has scrolled away (role/partials/headers/
         bar). It is a child of main, never of the card: the card's backdrop-filter would make
         it the bar's containing block and un-fix it. Under the page's dialogs (z-50). --}}
    .gk-headbar { position: fixed; inset: 0 0 auto; z-index: 30; padding-top: env(safe-area-inset-top, 0px); transform: translateY(-110%); visibility: hidden; transition: transform var(--gk-open) ease, visibility 0s linear var(--gk-open); background: var(--gk-panel); color: var(--gk-ink); -webkit-backdrop-filter: blur(.75rem); backdrop-filter: blur(.75rem); box-shadow: 0 1px 0 var(--gk-line), 0 .75rem 1.5rem -1rem rgb(0 0 0 / .35); }
    .gk-headbar[data-on] { transform: none; visibility: visible; transition: transform var(--gk-open) ease, visibility 0s; }
    .gk-headbar-in { display: flex; align-items: center; gap: .75rem; padding: .5rem 0; }
    .gk-headbar-logo { flex: none; width: 2.25rem; height: 2.25rem; border-radius: 22%; object-fit: cover; }
    .gk-headbar-name { flex: 1 1 auto; min-width: 0; overflow: hidden; font-size: 1.125rem; font-weight: 700; font-synthesis: none; line-height: 1.2; white-space: nowrap; text-overflow: ellipsis; color: var(--gk-ink); }
    {{-- Its button is a second copy of the header's, so it can be smaller than a first one may be. --}}
    :where(.gk-headbar) .gk-btn { min-height: 2.25rem; padding: 0 .75rem; }
    .gk-headbar-top { display: grid; flex: none; place-items: center; width: 2.375rem; height: 2.375rem; border: 1px solid var(--gk-line); border-radius: .625rem; background: var(--gk-solid); color: var(--gk-ink); cursor: pointer; }
    .gk-headbar-top:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 2px; }
    .gk-headbar-top svg { width: 1rem; height: 1rem; }
    {{-- Whatever the page brings to the top of the window (the map when it opens, the list after
         "See all events here", a day's panel, a link to a section) stops under the bar's foot and
         not behind it: the bar is .5rem + 2.375rem + .5rem, and half a rem of air. The map's own
         title row and its Hide map button sat under the bar. Said whether or not the bar is on:
         the map opens while the header is still there, and without this it was that very scroll
         which sent the header away and brought the bar in over the map. Inside :where() and by
         the bar's class, never its id: an owner's own rule for html, or for the bar's promised
         id, has nothing here to beat (GuestKitTest). --}}
    :where(html:has(.gk-headbar)) { scroll-padding-top: calc(3.875rem + env(safe-area-inset-top, 0px)); }

    @media (prefers-reduced-motion: reduce) {
        .gk-btn, .gk-link { transition-duration: 1ms; }
        .gk-btn:active { transform: none; }
        .gk-headbar { transition: none; }
    }

    @media print {
        {{-- .dark as well: a panel that kept its dark:bg- utility would outrank the single class
             and print black text on a dark ground. --}}
        .gk-panel, .dark .gk-panel { background: #ffffff; color: #000000; box-shadow: none; -webkit-backdrop-filter: none; backdrop-filter: none; border: 1px solid #d1d5db; }
        .gk-btn { display: none; }
        .gk-headbar { display: none; }
        .gk-note { background: #ffffff; color: #000000; border-color: #9ca3af; }
    }
</style>
