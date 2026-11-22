{{-- The stage: the one surface a guest page is laid on, over the schedule's own background.

     The guest pages used to stack a translucent white panel per section on the background, so a
     page read as a pile of cards. Now each page is ONE sheet (.gk-stage), in the stage colours
     App\Utils\GuestTheme works out from the schedule's accent (a paper in light mode, a deep ink
     in dark, both faintly in the accent's hue, every ink checked at 4.5:1 on them), and what was
     a panel is a flat section of it (.gk-sec) divided by a hairline. Big type carries the page:
     a date line in the accent, a large title, the facts in one row, one large button. Pictures
     stand beside the text (.gk-poster) and the schedules on an event are cards raised on the
     sheet (.gk-card). The background shows around the sheet from a tablet up, and above it on a
     phone, where the page's own bar sits on it.

     Printed after partials/guest-kit-styles, whose tokens it re-points inside the sheet (every
     kit piece on the stage takes the stage's inks with no change of its own), and before the
     owner's custom CSS, which still wins a tie. The kit's own rules hold here: one class per
     rule where it can be, no !important, rem sizes, logical properties, the kit's timings.
     Never a #gp- id: those are the owners'. --}}
<style {!! nonce_attr() !!}>
    .gk-stage {
        --gk-ink: var(--gk-stage-ink);
        --gk-ink-2: var(--gk-stage-ink-2);
        --gk-ink-3: var(--gk-stage-ink-3);
        --gk-solid: var(--gk-stage-raised);
        --gk-panel: var(--gk-stage-raised);
        --gk-well: var(--gk-stage-well);
        --gk-line: color-mix(in srgb, var(--gk-stage-ink) 14%, transparent);
        --gk-card-edge: color-mix(in srgb, var(--gk-stage-ink) 10%, transparent);
        --gk-shadow: none;
        position: relative;
        isolation: isolate;
        box-sizing: border-box;
        width: 100%;
        margin-inline: auto;
        {{-- Edge to edge, as the mock: the page IS the sheet. Its content keeps to a 70rem column. --}}
        padding: 1.25rem max(clamp(1rem, 4vw, 3.5rem), calc((100% - 70rem) / 2)) 4.5rem;
        background: var(--gk-stage);
        color: var(--gk-ink);
    }
    {{-- A sheet capped narrower than the window (a form, a booking, the carpool page) is centred
         by its margins and keeps a plain gutter: the padding above centres a full-width sheet's
         column, and its 100% is the PARENT's width, so on a 1886px window it left a 64rem sheet
         258px of room (the embed sets its own, in role/show-guest-embed). The gift card pages set
         theirs in their own views. --}}
    .gk-stage:is(.gk-pg-sm, .gk-pg-md, .gk-p-appt, .gk-p-carpool, .gk-p-closed, .gk-p-manage) { padding-inline: clamp(1rem, 4vw, 3.5rem); }
    {{-- The accent as a soft light over the top of the sheet, where the date line and the title
         are. Behind everything on the sheet, never over it. --}}
    .gk-stage::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -1;
        border-radius: inherit;
        pointer-events: none;
        background: radial-gradient(75rem 38rem at 15% -10%, rgb(var(--es-glow) / .16), transparent 70%);
    }
    :where(.dark) .gk-stage::before { background: radial-gradient(75rem 38rem at 15% -10%, rgb(var(--es-glow) / .28), transparent 70%); }
    [dir="rtl"] .gk-stage::before { background: radial-gradient(75rem 38rem at 85% -10%, rgb(var(--es-glow) / .16), transparent 70%); }
    :where(.dark) [dir="rtl"] .gk-stage::before { background: radial-gradient(75rem 38rem at 85% -10%, rgb(var(--es-glow) / .28), transparent 70%); }

    {{-- A section of the sheet, where a panel used to be: no ground, no shadow, no corners of its
         own. Each after the first in its column is set off by a hairline. --}}
    .gk-sec { background: none; box-shadow: none; -webkit-backdrop-filter: none; backdrop-filter: none; border-radius: 0; padding: 0; }
    .gk-sec-rule { border-top: 1px solid var(--gk-line); padding-top: 2rem; }
    {{-- A section's name: small and quiet, over what it names. --}}
    .gk-label { margin: 0 0 .875rem; font-size: .8125rem; font-weight: 500; letter-spacing: 0; text-transform: none; color: var(--gk-ink-3); }

    {{-- A card raised on the sheet: a schedule on the event, an event in a list. A hairline in
         light mode, where white on paper needs an edge; a shade of its own in dark. --}}
    .gk-card { overflow: clip; border: 1px solid var(--gk-card-edge); border-radius: 1.25rem; background: var(--gk-stage-raised); color: var(--gk-ink); }
    :where(.dark) .gk-card { border-color: transparent; }

    {{-- A picture standing beside the text: the flyer. Its own corners and a deep shadow, no frame. --}}
    .gk-poster { overflow: hidden; border-radius: 1.5rem; box-shadow: 0 1.875rem 3.75rem -1.25rem rgb(0 0 0 / .5); }
    .gk-poster img { display: block; width: 100%; height: auto; }

    {{-- The page's top line, on the background above the sheet: the way back, the language. A
         glass pill, so it reads on any background a schedule chose. --}}
    .gk-topbar { display: flex; align-items: center; justify-content: space-between; gap: .75rem; width: 100%; max-width: 76rem; margin: 0 auto; padding: .875rem 1rem 0; box-sizing: border-box; }
    @media (min-width: 40rem) { .gk-topbar { padding-inline: 0; } }
    .gk-glass { display: inline-flex; align-items: center; gap: .5rem; min-height: 2.5rem; padding: 0 1rem; border-radius: 999px; background: rgb(15 18 25 / .55); color: #f8fafc; font-size: .9375rem; font-weight: 500; text-decoration: none; -webkit-backdrop-filter: blur(10px); backdrop-filter: blur(10px); transition: background-color var(--gk-swap); }
    .gk-glass:hover { background: rgb(15 18 25 / .72); }
    .gk-glass:focus-visible { outline: 2px solid #f8fafc; outline-offset: 2px; }

    {{-- Type. The title carries the page. --}}
    .gk-dateline { display: flex; flex-wrap: wrap; align-items: center; gap: .25rem .5rem; margin: 0; font-size: .9375rem; font-weight: 600; color: var(--es-accent-readable); }
    .gk-dateline-dot { opacity: .5; }
    .gk-title { margin: 0; font-size: clamp(2.25rem, 1.4rem + 3.4vw, 4.25rem); font-weight: 800; line-height: 1.02; letter-spacing: -.02em; color: var(--gk-ink); text-wrap: balance; overflow-wrap: anywhere; }
    .gk-subtitle { margin: 0; font-size: clamp(1.25rem, 1rem + 1vw, 1.75rem); line-height: 1.25; color: var(--gk-ink-2); text-wrap: pretty; }
    .gk-crumbs { display: flex; flex-wrap: wrap; align-items: center; gap: .25rem 1rem; font-size: .9375rem; color: var(--gk-ink-3); }
    .gk-crumbs a { color: inherit; text-decoration: none; }
    .gk-crumbs a:hover { color: var(--gk-ink); text-decoration: underline; text-underline-offset: 3px; }

    {{-- The facts in one row: when, where, what it costs. Each a small label over its value, the
         row between two hairlines. The label is the cell's data-label, drawn here. --}}
    .gk-factrow { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 11rem), 1fr)); column-gap: 1.5rem; border-block: 1px solid var(--gk-line); }
    .gk-fact-cell { display: flex; flex-direction: column; gap: .375rem; min-width: 0; padding-block: 1.25rem; }
    .gk-fact-cell::before { content: attr(data-label); font-size: .8125rem; font-weight: 500; color: var(--gk-ink-3); }
    .gk-fact-cell-wide { grid-column: 1 / -1; order: 9; padding-top: 0; }
    .gk-stage .gk-more-dates { margin-top: 0; padding-inline-start: 0; }
    .gk-fact-cell-wide::before { content: none; }
    .gk-tile { display: none; }
    .gk-fact-main { font-size: 1.1875rem; font-weight: 650; line-height: 1.3; color: var(--gk-ink); }
    .gk-fact-sub { font-size: .9375rem; line-height: 1.45; color: var(--gk-ink-2); }

    {{-- The buttons under the facts. One large one in the schedule's colour; the rest bordered,
         as tall, and named in words. --}}
    .gk-cta { flex-wrap: wrap; align-items: center; gap: .75rem; width: 100%; }
    .gk-cta-main { display: inline-flex; flex: none; align-items: center; justify-content: center; gap: .5rem; box-sizing: border-box; max-width: 21.25rem; min-height: 3.625rem; padding: 0 1.75rem; border-radius: .875rem; font-size: 1.1875rem; font-weight: 700; line-height: 1.2; box-shadow: none; transition: filter var(--gk-swap), transform var(--gk-press); }
    {{-- It grows only in a row of buttons: in a column a flex basis is a height. --}}
    .gk-cta .gk-cta-main { flex: 1 1 13.75rem; }
    .gk-cta-main:hover { transform: none; filter: brightness(1.08); box-shadow: none; }
    .gk-cta-main:active { transform: scale(.985); }
    .gk-cta-alt { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; min-height: 3.625rem; padding: 0 1.375rem; border: 1px solid color-mix(in srgb, var(--gk-ink) 22%, transparent); border-radius: .875rem; background: transparent; color: var(--gk-ink); font-size: 1rem; font-weight: 550; box-shadow: none; transition: background-color var(--gk-swap); }
    .gk-cta-alt:hover { background: var(--gk-well); box-shadow: none; transform: none; }
    .gk-cta-icon { width: auto; height: 3.625rem; min-width: 3.625rem; padding: 0 1.125rem; gap: .5rem; border-radius: .875rem; border-color: color-mix(in srgb, var(--gk-ink) 22%, transparent); color: var(--gk-ink); font-size: 1rem; font-weight: 550; }
    .gk-cta-icon:hover { background: var(--gk-well); }
    .gk-cta-word { display: none; }
    @media (min-width: 30rem) { .gk-cta-word { display: inline; } }

    {{-- The event page's two columns: the words first, the pictures and the schedules beside
         them at the end. --}}
    @media (min-width: 64rem) {
        .gk-stage .gk-event { grid-template-columns: minmax(0, 1fr) 23.75rem; column-gap: clamp(2rem, 4vw, 3.5rem); }
        .gk-stage .gk-event-main { grid-column: 1; grid-row: 2; gap: 2.25rem; }
        .gk-stage .gk-event-side { grid-column: 2; grid-row: 2; gap: 1.25rem; }
        .gk-event > .gk-topline { grid-row: 1; margin-bottom: 1rem; }
    }
    .gk-details { display: flex; flex-direction: column; gap: 1.75rem; }
    .gk-ev-head { display: flex; flex-direction: column; gap: .875rem; }

    {{-- Running text: the story under the facts. --}}
    .gk-story { max-width: 62ch; font-size: 1.0625rem; line-height: 1.7; color: var(--gk-ink-2); }
    @media (min-width: 48rem) { .gk-story { font-size: 1.125rem; } }
    {{-- Under the event's two columns: the mailing list, then the schedule's other events. --}}
    .gk-event-after { display: grid; gap: 3.5rem; margin-top: 3.5rem; }
    .gk-band { padding: clamp(1.25rem, 3vw, 2rem); border-radius: 1.5rem; }

    {{-- A list of events on the sheet (the event page's "Events", the schedule's own list): a
         big heading with the way to everything at its end, each day as a line in the schedule's
         colour, and the day's events as cards side by side, the picture at the card's start. --}}
    .gk-stage .gk-up { gap: 1.5rem; }
    .gk-stage .gk-up-head { padding: 0; background: none; box-shadow: none; -webkit-backdrop-filter: none; backdrop-filter: none; border-radius: 0; }
    .gk-stage .gk-up-title { font-size: clamp(1.75rem, 1.3rem + 1.6vw, 2.5rem); font-weight: 800; letter-spacing: -.01em; }
    .gk-stage .gk-up-all { font-size: 1rem; font-weight: 600; text-decoration: none; color: var(--es-accent-readable); }
    .gk-stage .gk-up-all:hover { text-decoration: underline; text-underline-offset: 3px; }
    .gk-stage .gk-dayhead { width: auto; margin: 0 0 .875rem; padding: 0; background: none; box-shadow: none; -webkit-backdrop-filter: none; backdrop-filter: none; border-radius: 0; }
    .gk-stage .gk-dayhead-title { font-size: 1.0625rem; font-weight: 650; }
    .gk-stage .gk-dayhead-word { font-size: 1.0625rem; font-weight: 650; letter-spacing: 0; text-transform: none; }
    .gk-stage :where(.gk-up) .gk-day { padding-inline: 0; }
    .gk-stage .gk-up .gk-rows { grid-template-columns: repeat(auto-fill, minmax(min(100%, 26rem), 1fr)); gap: 1rem; }
    .gk-stage .gk-row-item { border: 1px solid var(--gk-card-edge); border-radius: 1.25rem; background: var(--gk-stage-raised); box-shadow: none; transition: background-color var(--gk-swap); }
    :where(.dark) .gk-stage .gk-row-item { border-color: transparent; }
    .gk-stage .gk-row-item:hover { background: var(--gk-stage-well); }
    .gk-stage .gk-row-stack { grid-template-columns: 7rem minmax(0, 1fr); grid-template-areas: "i t" "i b"; column-gap: 1rem; padding-block: 1rem; padding-inline: 0 1rem; }
    .gk-stage .gk-row-stack-bare { grid-template-columns: minmax(0, 1fr); grid-template-areas: "t" "b"; padding-inline: 1.125rem; }
    .gk-stage .gk-row-stack .gk-row-media { min-height: 8.25rem; margin-block: -1rem; }
    .gk-stage .gk-row-time { font-size: .9375rem; font-weight: 650; color: var(--es-accent-readable); }
    .gk-stage .gk-row-title { font-size: 1.1875rem; line-height: 1.25; }
    {{-- A schedule's picture on its card when it has no cover of its own: small, beside the
         words, never a crop of what may be a logo. --}}
    .gk-card-avatar { display: block; width: 3.5rem; height: 3.5rem; margin-bottom: .875rem; border-radius: .875rem; object-fit: cover; }
    .gk-avatar-frame { background: var(--gk-stage-raised); }
    {{-- A card's own button (the venue's Add to calendar): the card's width, quiet. --}}
    .gk-card-btn { display: flex; width: 100%; align-items: center; justify-content: center; min-height: 3rem; border: 1px solid color-mix(in srgb, var(--gk-ink) 22%, transparent); border-radius: .75rem; background: transparent; color: var(--gk-ink); box-shadow: none; transition: background-color var(--gk-swap); }
    .gk-card-btn:hover { background: var(--gk-stage-well); }
    {{-- The schedule's own list (role/partials/guest-row, at every width on a guest page). On a
         phone a card is the picture across its top, then the words; from a tablet up each day is
         a column of its own at the start, its cards beside it with the picture at their start. --}}
    .gk-row-more { display: none; }
    .gk-stage .gk-list { max-width: none; }
    .gk-stage .gk-days { gap: 2rem; }
    .gk-stage .gk-days .gk-dayhead { display: flex; flex-wrap: wrap; align-items: baseline; gap: .5rem; margin: 0 0 .875rem; }
    .gk-stage .gk-days .gk-row { padding-inline: 1.125rem; }
    {{-- On a phone a poster stays at the card's side, in its own tall shape (the owner's call,
         2026-11-19: show posters are rectangles and fit there better than across the top). --}}
    .gk-stage .gk-days .gk-row:not(.gk-row-bare) { padding-inline-end: 0; }
    .gk-stage .gk-days .gk-row-title { font-size: 1.3125rem; line-height: 1.2; }
    .gk-stage .gk-days .gk-row-more { display: inline-flex; gap: .375rem; align-self: flex-end; margin-top: .5rem; font-size: .9375rem; font-weight: 650; color: var(--es-accent-readable); }
    @media (min-width: 48rem) {
        .gk-stage .gk-days .gk-day { display: grid; grid-template-columns: 8.5rem minmax(0, 1fr); column-gap: 2rem; align-items: start; }
        .gk-stage .gk-days .gk-dayhead { position: sticky; top: 1.25rem; flex-direction: column; align-items: flex-start; gap: .25rem; margin: 0; }
        .gk-stage .gk-days .gk-dayhead-title { font-size: 1.25rem; font-weight: 750; line-height: 1.2; }
        .gk-stage .gk-days .gk-row:not(.gk-row-bare) { grid-template-columns: 12.5rem minmax(0, 1fr); grid-template-areas: "i t" "i b"; grid-template-rows: auto 1fr; column-gap: 1.5rem; padding-block: 1.25rem; padding-inline: 0 1.5rem; }
        .gk-stage .gk-days .gk-row:not(.gk-row-bare) .gk-row-media { min-height: 11rem; max-height: none; margin: -1.25rem 0; }
        .gk-stage .gk-days .gk-row-bare { padding: 1.25rem 1.5rem; }
        .gk-stage .gk-days .gk-row-title { font-size: 1.5rem; }
    }

    {{-- The schedule's header on the sheet: no panel of its own. --}}
    .gk-stage .gk-head { background: none; box-shadow: none; -webkit-backdrop-filter: none; backdrop-filter: none; }
    {{-- The schedule's next event, above its month: a card on the sheet. --}}
    .gk-stage .gk-lead { border: 1px solid var(--gk-card-edge); border-radius: 1.25rem; background: var(--gk-stage-raised); box-shadow: none; -webkit-backdrop-filter: none; backdrop-filter: none; }
    :where(.dark) .gk-stage .gk-lead { border-color: transparent; }
    {{-- The category pills start where the list does. --}}
    .gk-stage .gk-pills { max-width: none; }
    {{-- A form's own name at the top of its card (Register, Tickets). --}}
    .gk-band-title { margin: 0; font-size: clamp(1.5rem, 1.2rem + 1vw, 2rem); font-weight: 800; line-height: 1.15; letter-spacing: -.01em; color: var(--gk-ink); }
    {{-- The fan buttons (Add photo, video, comment): quiet, bordered, as tall as a finger. The
         schedule's colour comes on hover (their script), not as a border at rest. --}}
    .gk-stage .accent-hover-btn { min-height: 2.875rem; padding: 0 1.125rem; border-color: color-mix(in srgb, var(--gk-ink) 22%, transparent); border-radius: .75rem; color: var(--gk-ink); }
    {{-- The page's top line: the way back at its start. --}}
    .gk-topline { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 2.25rem; }
    .gk-topline .gk-crumbs { font-size: .9375rem; }
    .gk-event > .gk-topline { order: -1; grid-column: 1 / -1; margin-bottom: .25rem; }
    {{-- The bill (event page): each act a row of the section, its picture small, no card. --}}
    .gk-lineup { display: flex; flex-direction: column; gap: 1.5rem; }
    .gk-lineup > .gk-label { margin-bottom: -.5rem; }
    .gk-lineup .gk-card { overflow: visible; border: 0; border-radius: 0; background: none; }
    .gk-lineup .gk-card + .gk-card { padding-top: 1.5rem; border-top: 1px solid var(--gk-line); }
    .gk-lineup .gk-card > img:first-child { display: none; }
    .gk-lineup .gk-card > .p-5, .gk-lineup .gk-card > .px-5, .gk-lineup .gk-card.p-5 { padding: 0; }
    .gk-lineup .gk-card > .pt-4, .gk-lineup .gk-card > .px-5 > .pt-4 { display: none; }
    .gk-lineup .gk-avatar-frame { width: 3.5rem; height: 3.5rem; margin: 0 0 .75rem; padding: 0; background: none; }
    .gk-lineup .gk-card > h2 { display: none; }
    .gk-lineup .gk-card .text-lg { font-size: 1.25rem; }
    {{-- A link said in words on a card (the venue's website, Facebook, Instagram). --}}
    .gk-chip-link { display: inline-flex; align-items: center; gap: .4375rem; min-height: 2.75rem; padding: 0 .875rem; border-radius: .625rem; background: var(--gk-stage-well); color: var(--gk-ink); font-size: .875rem; font-weight: 600; text-decoration: none; transition: background-color var(--gk-swap); }
    .gk-chip-link:hover { background: color-mix(in srgb, var(--gk-stage-well) 80%, var(--gk-ink)); }
    {{-- The page containers that set a width of their own before the redesign (the event page's
         70rem) are the full-width sheet now; the stage's padding keeps the content to its column. --}}
    .gk-stage.gk-event-page, .gk-stage.gk-sched { max-width: none; }
    {{-- The venue card's head: its picture small beside its name and address. --}}
    .gk-venue-row { display: flex; align-items: center; gap: .875rem; min-width: 0; }
    .gk-venue-logo { flex: none; width: 3rem; height: 3rem; border-radius: .625rem; object-fit: cover; }
    .gk-venue-id { display: flex; flex-direction: column; gap: .125rem; min-width: 0; }
    .gk-venue-addr { font-size: .875rem; color: var(--gk-ink-3); }
    .gk-venue-id .text-base { font-size: 1.0625rem; }
    {{-- The schedule page, as the list mock: one 60rem column for the header and the list. --}}
    .gk-stage.gk-sched { padding-inline: max(clamp(1rem, 4vw, 3.5rem), calc((100% - 60rem) / 2)); }
    {{-- The list and the month take the column's width (their old inline caps were for a page
         with a background showing beside them). --}}
    .gk-stage.gk-sched [data-view-width] { max-width: none !important; }

    {{-- The schedule's header on the sheet: the cover rounded all round; then the logo small
         beside the name, the way to follow and share at the end of that row; then what it is,
         the facts and the links as chips. --}}
    .gk-stage .gk-head { --gk-head-pad: 0; --gk-head-logo: 4.75rem; --gk-head-name: clamp(2.25rem, 1.5rem + 3vw, 3.75rem); border-radius: 0; }
    .gk-stage .gk-head-stage { border-radius: 1.5rem; }
    .gk-stage :where(.gk-head-pictured) .gk-head-stage { height: clamp(10rem, 24vw, 15rem); }
    .gk-stage .gk-head-body { padding: 1.75rem 0 0; }
    .gk-stage .gk-head-wrap { display: grid; grid-template-columns: auto minmax(0, 1fr) auto; grid-template-areas: "logo name actions" "logo tag actions" "meta meta meta"; column-gap: 1.25rem; align-items: center; }
    {{-- Anything in the header that has no place named below (the second row of buttons, Gift
         cards, Submit event) takes a whole row of its own. --}}
    .gk-stage .gk-head-wrap > * { grid-column: 1 / -1; }
    .gk-stage .gk-head:not(.gk-head-logoed) .gk-head-wrap { grid-template-columns: 0 minmax(0, 1fr) auto; column-gap: 0; }
    .gk-stage .gk-head:not(.gk-head-logoed) .gk-head-actions { margin-inline-start: 1rem; }
    .gk-stage .gk-head-top { display: contents; }
    .gk-stage .gk-head-logo { grid-area: logo; align-self: center; border-radius: 1rem; box-shadow: none; }
    .gk-stage :where(.gk-head-pictured.gk-head-logoed) .gk-head-top, .gk-stage :where(.gk-head-walled.gk-head-logoed) .gk-head-top { margin-top: 0; }
    .gk-stage .gk-head-actions, .gk-stage :where(.gk-head-pictured.gk-head-logoed) .gk-head-actions, .gk-stage :where(.gk-head-walled.gk-head-logoed) .gk-head-actions { grid-area: actions; align-self: center; padding-top: 0; }
    .gk-stage .gk-head-name { grid-area: name; margin: 0; font-weight: 800; letter-spacing: -.02em; }
    .gk-stage .gk-head-tagline { grid-area: tag; margin: .25rem 0 0; color: var(--gk-ink-2); }
    .gk-stage .gk-head-meta { grid-area: meta; margin-top: 1.5rem; }
    .gk-stage .gk-head-social-link { width: 2.75rem; height: 2.75rem; border-radius: .625rem; background: var(--gk-stage-well); color: var(--gk-ink); }
    .gk-stage .gk-head-about { margin-top: 1.25rem; }
    @media (max-width: 39.99rem) {
        .gk-stage .gk-head-wrap { grid-template-columns: auto minmax(0, 1fr); grid-template-areas: "logo actions" "name name" "tag tag" "meta meta"; row-gap: .75rem; }
        .gk-stage .gk-head-actions { justify-content: flex-end; }
    }

    {{-- The day beside its events, from a tablet up: the weekday, the date large, the month. The
         heading keeps the whole date for a reader; the large parts are what is seen. --}}
    .gk-dayhead-big { display: none; }
    @media (min-width: 48rem) {
        .gk-stage .gk-days .gk-dayhead-title { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
        .gk-stage .gk-days .gk-dayhead-big { display: flex; flex-direction: column; gap: .125rem; }
        .gk-dh-week { font-size: .9375rem; font-weight: 500; color: var(--gk-ink-2); }
        .gk-dh-num { font-size: 2.75rem; font-weight: 800; line-height: 1; letter-spacing: -.02em; color: var(--gk-ink); }
        .gk-dh-month { font-size: .9375rem; font-weight: 500; color: var(--gk-ink-2); }
        .gk-stage .gk-days .gk-dayhead-word { font-size: .9375rem; }
    }
    {{-- The list mock's finish: a line between days, a larger name, "Details" as a quiet button. --}}
    .gk-stage .gk-days .gk-row-more { align-items: center; min-height: 2.5rem; padding: 0 1rem; border-radius: .625rem; background: var(--gk-stage-well); color: var(--gk-ink); }
    @media (min-width: 48rem) {
        .gk-stage .gk-days { gap: 0; }
        .gk-stage .gk-days .gk-day { padding-block: 2rem; border-top: 1px solid var(--gk-line); }
        .gk-stage .gk-days .gk-row-title { font-size: 1.75rem; font-weight: 800; letter-spacing: -.01em; }
        .gk-stage .gk-days .gk-rows { gap: 1.25rem; }
    }
    {{-- A long name steps down, so the head is never five lines of the largest type. --}}
    .gk-title-m { font-size: clamp(2rem, 1.3rem + 2.6vw, 3.25rem); }
    .gk-title-s { font-size: clamp(1.75rem, 1.25rem + 1.8vw, 2.5rem); line-height: 1.1; }

    {{-- With nothing in the picture column (no flyer, no stand-in, no venue worth a card), the
         event is one column in the middle, not words beside an empty half. --}}
    @media (min-width: 64rem) {
        .gk-stage .gk-event:not(:has(.gk-event-side > :not(script))) { grid-template-columns: minmax(0, 48rem); justify-content: center; }
        .gk-stage .gk-event:not(:has(.gk-event-side > :not(script))) .gk-event-side { display: none; }
    }
    {{-- Round 3, from the owner's second mocks and the UX review (schedule page). --}}

    {{-- The schedule's rows, as the second list mock: from a tablet up the time is a column of
         its own, the picture smaller, the way in said quietly at the row's end. --}}
    @media (min-width: 48rem) {
        .gk-stage .gk-days .gk-row:not(.gk-row-bare) { grid-template-columns: 5rem 9rem minmax(0, 1fr) auto; grid-template-areas: "t i b m"; grid-template-rows: auto; column-gap: 1.25rem; align-items: center; padding: 1rem 1.25rem; }
        .gk-stage .gk-days .gk-row-bare { display: grid; grid-template-columns: 5rem minmax(0, 1fr) auto; grid-template-areas: "t b m"; column-gap: 1.25rem; align-items: center; padding: 1rem 1.25rem; }
        .gk-stage .gk-days .gk-row:not(.gk-row-bare) .gk-row-media { min-height: 0; height: 6rem; margin: 0; border-radius: .75rem; overflow: hidden; }
        .gk-stage .gk-days .gk-row-time { grid-area: t; align-self: center; font-size: 1.0625rem; color: var(--gk-ink); }
        .gk-stage .gk-days .gk-row-body { grid-area: b; }
        .gk-stage .gk-days .gk-row-more { min-height: 2.25rem; margin-top: .25rem; padding: 0; background: none; color: var(--es-accent-readable); }
        .gk-stage .gk-days .gk-row-title { font-size: 1.375rem; }
    }

    {{-- The schedule's name over the foot of its cover, on a shade that keeps it readable over
         any photograph. Without a cover it is on the page, as before. --}}
    .gk-stage :where(.gk-head-pictured) .gk-head-stage { position: relative; }
    @media (min-width: 40rem) {
    }


    {{-- Who is on, on a row of the list: up to three small faces and the names. --}}
    .gk-row-who { display: flex; align-items: center; gap: .5rem; min-width: 0; margin: .125rem 0 0; font-size: .875rem; color: var(--gk-ink-2); }
    .gk-row-who-faces { display: inline-flex; flex: none; }
    .gk-row-who-faces > * { width: 1.5rem; height: 1.5rem; border: 2px solid var(--gk-stage-raised); border-radius: 999px; object-fit: cover; }
    .gk-row-who-faces > * + * { margin-inline-start: -.5rem; }
    .gk-row-who-faces i { display: inline-grid; place-items: center; background: var(--gk-stage-well); color: var(--gk-ink-2); font-size: .6875rem; font-style: normal; font-weight: 700; }
    .gk-row-who-names { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .gk-row-who-names a { color: inherit; text-decoration: none; }
    .gk-row-who-names a:hover { text-decoration: underline; text-underline-offset: 2px; }
    {{-- The day's count, quiet, beside its name on a phone and under its date from a tablet up. --}}
    .gk-dh-count { font-size: .8125rem; color: var(--gk-ink-3); }

    {{-- Sponsors on the sheet (components/sponsor-grid). Not a block of colour across the page
         but a quiet section of it: the name at the start, then each logo on a small chip of
         its own that hugs it, all one height, in the owner's panel colour (a soft white when
         none is set; none at all when it is "transparent"). A light chip melts a logo's own
         white box into it (multiply), and the script sizes each logo by its shape, so wide
         wordmarks, badges and circles read as one even row. What does not fit one row drifts
         slowly as one row, or, with reduced motion, shows two rows and "Show more". Gold
         leads, in a still row of its own and larger. --}}
    .es-sp { --sp-chip: #eaeef1; --sp-chip-ink: #1d2328; --sp-h: 4rem; --sp-gap: .75rem; display: flex; flex-direction: column; gap: .875rem; box-sizing: border-box; width: 100%; margin-inline: auto; color: var(--gk-ink); }
    .es-sp-head { display: flex; align-items: center; justify-content: space-between; gap: 1rem; min-height: 2.75rem; }
    .es-sp-title { margin: 0; font-size: .9375rem; font-weight: 600; line-height: 1.3; text-align: start; color: var(--gk-ink-2); }
    .es-sp-toggle { flex: none; min-height: 2.75rem; padding: 0; border: 0; background: none; color: var(--es-accent-readable); font: inherit; font-size: .9375rem; font-weight: 600; cursor: pointer; }
    .es-sp-toggle:hover { text-decoration: underline; text-underline-offset: 3px; }
    .es-sp-toggle:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 3px; border-radius: .25rem; }
    .es-sp-toggle[hidden] { display: none; }
    {{-- Room above and below for a chip's lift on hover, which a drifting row would clip. --}}
    .es-sp-view { margin-block: -.5rem; padding-block: .5rem; }
    .es-sp-list { display: flex; flex-wrap: wrap; align-items: center; gap: var(--sp-gap); margin: 0; padding: 0; list-style: none; }
    .es-sp-item { display: flex; flex: none; }
    .es-sp-view-lead .es-sp-item { --sp-h: 5.25rem; }
    .es-sp-link { display: flex; flex-direction: column; align-items: center; gap: .5rem; color: inherit; text-decoration: none; border-radius: 1rem; }
    .es-sp-link:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 3px; }
    .es-sp-chip { display: flex; align-items: center; justify-content: center; box-sizing: border-box; isolation: isolate; height: var(--sp-h); min-width: var(--sp-h); padding: .5rem .875rem; border-radius: .875rem; background: var(--sp-chip); color: var(--sp-chip-ink); box-shadow: inset 0 0 0 1px var(--gk-card-edge, transparent); transition: transform var(--gk-swap, .2s ease), box-shadow var(--gk-swap, .2s ease); }
    a.es-sp-link:hover .es-sp-chip { transform: translateY(-2px); box-shadow: 0 .625rem 1.25rem -.625rem rgb(0 0 0 / .5), inset 0 0 0 1px var(--gk-card-edge, transparent); }
    .es-sp-logo { display: block; width: auto; height: calc((var(--sp-h) - 1rem) * var(--sp-s, .75)); max-width: 10rem; object-fit: contain; border-radius: .375rem; mix-blend-mode: multiply; }
    .es-sp-word { overflow: hidden; max-width: 12rem; font-size: 1rem; font-weight: 700; line-height: 1.2; white-space: nowrap; text-overflow: ellipsis; color: var(--sp-chip-ink); }
    .es-sp-name { overflow: hidden; max-width: 8.5rem; font-size: .8125rem; line-height: 1.3; white-space: nowrap; text-overflow: ellipsis; color: var(--gk-ink-3); }
    .es-sp-sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
    {{-- A dark chip the owner chose: the logo as it is, no blending into it. --}}
    .es-sp-dark .es-sp-logo { mix-blend-mode: normal; }
    {{-- "Transparent": the logos on the page itself, as the owner's logos were made for it. --}}
    .es-sp-bare .es-sp-chip { padding-inline: 0; background: none; box-shadow: none; }
    .es-sp-bare a.es-sp-link:hover .es-sp-chip { box-shadow: none; }
    .es-sp-bare .es-sp-logo { mix-blend-mode: normal; }
    .es-sp-bare .es-sp-word { color: var(--gk-ink); }
    {{-- One row drifting: the list twice side by side, moved by one list's width, faded at both
         ends, still under the pointer. --}}
    .es-sp-run .es-sp-view { overflow: hidden; -webkit-mask-image: linear-gradient(to right, transparent, #000 2.5rem, #000 calc(100% - 2.5rem), transparent); mask-image: linear-gradient(to right, transparent, #000 2.5rem, #000 calc(100% - 2.5rem), transparent); }
    .es-sp-run .es-sp-track { display: flex; width: max-content; animation: es-sp-drift var(--sp-dur, 60s) linear infinite; }
    .es-sp-run .es-sp-list { flex-wrap: nowrap; padding-inline-end: var(--sp-gap); }
    .es-sp-run .es-sp-view:hover .es-sp-track { animation-play-state: paused; }
    [dir="rtl"] .es-sp-run .es-sp-track { animation-name: es-sp-drift-rtl; }
    @keyframes es-sp-drift { to { transform: translateX(-50%); } }
    @keyframes es-sp-drift-rtl { to { transform: translateX(50%); } }
    {{-- Two rows, the rest folded away until "Show more". --}}
    .es-sp-clamped .es-sp-view { overflow: hidden; max-height: calc(var(--sp-clamp, 8rem) + 1rem - var(--sp-gap)); }
    @media (max-width: 39.99rem) {
        .es-sp { --sp-h: 3.5rem; --sp-gap: .625rem; }
        .es-sp-view-lead .es-sp-item { --sp-h: 4.5rem; }
        .es-sp-chip { padding-inline: .75rem; border-radius: .75rem; }
    }

    {{-- ===== Round 5: the UX review of the built pages ===== --}}

    {{-- An act named and nothing more, under Talent: the name in the page's direction. --}}
    .gk-bare-act { font-size: 1.125rem; font-weight: 700; }
    .gk-bare-act a, .gk-bare-act span { font-size: inherit; color: var(--gk-ink); }

    {{-- The facts row: a phone fits three cells across before a lone one wraps. --}}
    @media (max-width: 39.99rem) { .gk-factrow { grid-template-columns: repeat(auto-fit, minmax(min(100%, 8rem), 1fr)); column-gap: 1rem; } }
    .gk-stage .gk-more-dates { padding-bottom: 1.25rem; }

    {{-- The event page's "Events": a row with no picture keeps the picture's place, as a quiet
         tile, so every row's words start at the same edge. --}}
    .gk-stage .gk-up .gk-row-stack-bare { grid-template-columns: 7rem minmax(0, 1fr); grid-template-areas: "i t" "i b"; padding-inline: 0 1rem; }
    .gk-stage .gk-up .gk-row-stack-bare::before { content: ""; grid-area: i; align-self: stretch; margin-block: -1rem; background: linear-gradient(140deg, color-mix(in srgb, var(--es-accent) 28%, var(--gk-stage-raised)), var(--gk-stage-well)); }

    {{-- The schedule's rows: the time on the name's first line; the way in as a small chip at
         the row's end, centred; a fuller poster. --}}
    @media (min-width: 48rem) {
        .gk-stage .gk-days .gk-row-time { align-self: start; padding-top: .3rem; }
        .gk-stage .gk-days .gk-row, .gk-stage .gk-days .gk-row-bare { position: relative; align-items: start; }
        .gk-stage .gk-days .gk-row-body { padding-inline-end: 7.5rem; }
        .gk-stage .gk-days .gk-row-more { position: absolute; inset-inline-end: 1.25rem; top: 50%; transform: translateY(-50%); min-height: 2.25rem; margin: 0; padding: 0 .875rem; border: 1px solid color-mix(in srgb, var(--es-accent-readable) 55%, transparent); border-radius: .625rem; background: none; color: var(--es-accent-readable); font-size: .875rem; }
        .gk-stage .gk-days .gk-row:not(.gk-row-bare) { grid-template-columns: 5rem 10rem minmax(0, 1fr); grid-template-areas: "t i b"; padding-block: 1.125rem; }
        .gk-stage .gk-days .gk-row:not(.gk-row-bare) .gk-row-media { align-self: stretch; height: auto; min-height: 7.5rem; margin-block: -1.125rem; border-radius: 0; }
    }

    {{-- The schedule header: one edge for everything under the cover, and room under it. --}}
    @media (min-width: 40rem) {
        .gk-stage :where(.gk-head-pictured) .gk-head-body { padding-inline: 0; }
    }
    .gk-stage .gk-head-social { margin-inline: 0; }

    {{-- Links in the schedule's colour (as text), not a fixed blue. --}}
    .gk-stage :is(.text-blue-600, .dark\:text-blue-400, .text-blue-500) { color: var(--es-accent-readable); }
    .gk-stage .custom-content a, .gk-stage .gk-story a { color: var(--es-accent-readable); }

    {{-- The venue map's band on the sheet: a card of the page's own, not the old near-black
         panel, in the column the sponsors and the list share. --}}
    .gk-stage .gk-map { overflow: clip; border: 1px solid var(--gk-card-edge); border-radius: 1.25rem; background: var(--gk-stage-raised); -webkit-backdrop-filter: none; backdrop-filter: none; box-shadow: none; }
    :where(.dark) .gk-stage .gk-map { border-color: transparent; }
    .gk-stage [data-map-wrap] { padding-inline: 0; }
    .gk-stage .gk-map-band:not(.is-open):not(.is-ask):hover { background: var(--gk-stage-well); }
    .gk-stage .gk-map-stack li { border-color: var(--gk-stage-raised); }

    {{-- ===== Round 6: the design scoring ===== --}}

    {{-- An event with no picture in the event page's "Events": a quiet tile with a calendar mark,
         never a coloured block that reads as a picture that failed to load. --}}
    .gk-stage .gk-up .gk-row-stack-bare::before { background: var(--gk-stage-well) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3.5' y='5' width='17' height='15' rx='2.5'/%3E%3Cpath d='M3.5 10h17M8 3v4M16 3v4'/%3E%3C/svg%3E") center / 1.75rem no-repeat; opacity: .9; }

    {{-- Labels that read: the facts' names and the sections' names one step brighter and larger. --}}
    .gk-fact-cell::before { font-size: .875rem; color: var(--gk-ink-2); }
    .gk-label { font-size: .875rem; color: var(--gk-ink-2); }

    {{-- The facts on a phone: a list of rows, the name at the start and the value beside it, so a
         long place name or time range never wraps into a narrow column. --}}
    @media (max-width: 39.99rem) {
        .gk-factrow { grid-template-columns: minmax(0, 1fr); }
        .gk-factrow > .gk-fact-cell:not(.gk-fact-cell-wide) { display: grid; grid-template-columns: 6.5rem minmax(0, 1fr); column-gap: .75rem; row-gap: .125rem; align-items: start; padding-block: .875rem; }
        .gk-factrow > .gk-fact-cell:not(.gk-fact-cell-wide)::before { grid-row: 1 / span 6; padding-top: .1875rem; }
        .gk-factrow > .gk-fact-cell:not(.gk-fact-cell-wide) > * { grid-column: 2; }
        .gk-factrow > .gk-fact-cell + .gk-fact-cell:not(.gk-fact-cell-wide) { border-top: 1px solid var(--gk-line); }
    }

    {{-- The schedule's rows on a phone: tighter, so a screen holds as many days as before. --}}
    @media (max-width: 47.99rem) {
        .gk-stage .gk-days .gk-row:not(.gk-row-bare) { grid-template-columns: minmax(0, 1fr) min(7.5rem, 32%); padding-block: .75rem; }
        .gk-stage .gk-days .gk-row:not(.gk-row-bare) .gk-row-media { margin-block: -.75rem; }
        .gk-stage .gk-days .gk-row-title { font-size: 1.125rem; }
        .gk-stage .gk-days .gk-row-more { min-height: 2.25rem; margin-top: .375rem; padding: 0 .75rem; font-size: .875rem; }
        .gk-stage .gk-days .gk-row-who { display: none; }
        .gk-stage .gk-days .gk-day { padding-block: 1.25rem; }
    }

    {{-- The schedule's name sits under its cover in the page's ink, in both styles: over the
         cover, a name that wraps hung across the cover's edge, or spilled white text onto a light
         page. --}}

    {{-- A link shown as its icon alone on a card (the venue's social links): round, as the
         schedule header's own are, the name in its tooltip and for a reader. --}}
    .gk-icon-link { display: inline-grid; place-items: center; width: 2.75rem; height: 2.75rem; border-radius: 999px; background: var(--gk-stage-well); color: var(--gk-ink-2); transition: background-color var(--gk-swap), color var(--gk-swap), transform var(--gk-press); }
    .gk-icon-link:hover { color: var(--gk-ink); background: color-mix(in srgb, var(--gk-stage-well) 75%, var(--gk-ink)); }
    .gk-icon-link:active { transform: scale(.96); }
    .gk-icon-link:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 2px; }

    {{-- ===== Round 7: the visual check of the light and dark pages ===== --}}
    .gk-stage .gk-head-tagline { margin-top: .5rem; }
    .gk-stage .gk-head-wrap { column-gap: 1.5rem; }
    {{-- A phone has no button row under the facts, so the facts' own bottom rule met the next
         section's top rule: one is enough. --}}
    @media (max-width: 39.99rem) { .gk-factrow { border-bottom: 0; } }
    {{-- A link that wraps keeps its chevron on its last line. --}}
    .gk-fact-cell a.group { display: inline; }
    {{-- The phone's buy bar: the page's own type and ground. --}}
    .gk-phonebar { background: var(--gk-stage-raised); border-color: color-mix(in srgb, var(--gk-stage-ink) 14%, transparent); color: var(--gk-stage-ink); font-family: inherit; }
    {{-- The row of things a visitor can do (Gift cards, Submit event): compact, the size of their
         words, side by side at the start, at every width; never a full-width bar. --}}
    .gk-stage .gk-head-more-actions { justify-content: flex-start; }
    .gk-stage .gk-head-more-actions .gk-btn { flex: none; min-height: 2.5rem; padding: 0 .875rem; gap: .4375rem; font-size: .875rem; }
    .gk-stage .gk-head-more-actions .gk-btn .gk-head-ico { width: 1rem; height: 1rem; }

    {{-- ===== The schedule header, profile designs 2a (from a tablet up) and 1a (a phone) =====
         2a: the logo beside the name and its facts, the visitor's actions on the same row
         (Follow, Gift cards, Submit event, Share), the description with the links beside it, then
         one bar for the list: its heading, the filter and the List / Calendar switch, in words.
         1a (a phone): identity first (the logo beside the name and its facts), one action row
         with Share in words, the links and Gift cards on one line, the list's heading and filter
         on a rule at the foot. The schedule's name, tagline and description keep their
         order for a reader at every width. --}}
    .gk-head-tools-title, .gk-head-social-word, .gk-head-seg-word, .gk-head-share-word { display: none; }
    .gk-stage .gk-head-wrap .gk-head-wrap { row-gap: 0; }
    .gk-stage .gk-head-meta, .gk-stage .gk-head-side { display: contents; }
    .gk-stage .gk-head-name { grid-area: name; }
    .gk-stage .gk-head-facts { grid-area: facts; display: flex; flex-wrap: wrap; gap: .25rem 1rem; margin: 0; }
    .gk-stage .gk-head-tagline { grid-area: tag; }
    .gk-stage .gk-head-about { grid-area: about; }
    .gk-stage .gk-head-social { grid-area: social; display: flex; flex-wrap: wrap; gap: .5rem; }
    .gk-stage .gk-head-more-actions { grid-area: more; }
    .gk-stage .gk-head-tools { grid-area: tools; display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: .625rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--gk-line); }
    .gk-stage .gk-head-tools-title { flex: 1 1 auto; margin: 0; font-size: 1.375rem; font-weight: 800; letter-spacing: -.01em; color: var(--gk-ink); text-align: start; }
    {{-- Where sponsors or the venue map stand between the header and the list, the list's bar
         is drawn after them (role/show-guest, $toolsBelow), right above the events; the header
         then ends on its own content. --}}
    .gk-stage .gk-tools-band { padding-inline: 0; }
    .gk-stage .gk-tools-band .gk-head-tools { margin-top: .5rem; }
    .gk-stage .gk-tools-band .gk-head-tools-title { display: block; }
    @media (min-width: 40rem) {
        .gk-stage .gk-head { --gk-head-logo: 5rem; --gk-head-name: clamp(2.25rem, 1.75rem + 1.5vw, 2.875rem); }
        .gk-stage .gk-head-wrap .gk-head-wrap { grid-template-columns: auto minmax(0, 1fr) auto; grid-template-areas: "logo name actions" "logo facts actions" "tag tag tag" "about about social" "tools tools tools"; column-gap: 1.25rem; align-items: center; }
        .gk-stage .gk-head:not(.gk-head-logoed) .gk-head-wrap .gk-head-wrap { grid-template-columns: 0 minmax(0, 1fr) auto; column-gap: 0; }
        .gk-stage .gk-head-logo { border-radius: 1.25rem; }
        .gk-stage .gk-head-name { align-self: end; }
        .gk-stage .gk-head-facts { align-self: start; margin-top: .375rem; }
        .gk-stage .gk-head-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: .625rem; margin-inline-start: 1rem; }
        .gk-stage .gk-head-acts-rest { display: contents; }
        .gk-stage .gk-head-actions .gk-btn { min-height: 3rem; border-radius: .875rem; }
        .gk-stage .gk-head-actions .gk-btn-primary:not(.gk-btn-icon) { padding-inline: 2rem; }
        .gk-stage .gk-head-actions .gk-head-share { width: 3rem; height: 3rem; }
        .gk-stage .gk-head-more-actions { display: none; }
        .gk-stage .gk-head-tagline { margin-top: 1rem; }
        .gk-stage .gk-head-about { align-self: start; max-width: 42rem; margin-top: 1.5rem; }
        .gk-stage .gk-head-social { align-self: start; justify-content: flex-end; margin-top: 1.5rem; margin-inline-start: 2rem; }
        .gk-stage .gk-head-tools-title { display: block; }
        .gk-stage .gk-head-seg-btn { width: auto; padding-inline: .75rem; }
        .gk-stage .gk-head-seg-btn svg { display: none; }
        .gk-stage .gk-head-seg-word { display: inline; font-size: .875rem; font-weight: 600; }
        .gk-stage .gk-head-seg-btn[aria-pressed="true"] { color: var(--gk-ink); }
    }
    @media (max-width: 39.99rem) {
        .gk-stage .gk-head-wrap .gk-head-wrap { grid-template-columns: 4rem minmax(0, 1fr); grid-template-areas: "logo name" "logo facts" "tag tag" "actions actions" "about about" "social social" "tools tools"; column-gap: .875rem; align-items: center; }
        .gk-stage .gk-head:not(.gk-head-logoed) .gk-head-wrap .gk-head-wrap { grid-template-columns: 0 minmax(0, 1fr); column-gap: 0; }
        .gk-stage .gk-head-logo { width: 4rem; height: 4rem; border-radius: 1rem; }
        .gk-stage .gk-head-name { align-self: end; font-size: 1.875rem; line-height: 1.05; }
        .gk-stage .gk-head-facts { align-self: start; margin-top: .25rem; gap: .125rem .875rem; font-size: .875rem; }
        .gk-stage .gk-head-tagline { margin-top: .875rem; }
        .gk-stage .gk-head-actions { display: flex; gap: .625rem; margin: 1.25rem 0 0; }
        .gk-stage .gk-head-acts-rest { display: none; }
        .gk-stage .gk-head-actions > .gk-btn:not(.gk-btn-icon), .gk-stage .gk-head-actions > a.gk-btn:not(.gk-btn-icon) { flex: 1 1 auto; min-height: 3rem; border-radius: .875rem; font-size: 1rem; }
        {{-- Share in words beside the full-width main button (his ask: wider, with a label). --}}
        .gk-stage .gk-head-actions > .gk-head-share { width: auto; min-width: 3rem; height: 3rem; gap: .5rem; padding-inline: 1.375rem; border-radius: .875rem; font-size: 1rem; }
        .gk-stage .gk-head-actions > .gk-head-share .gk-head-share-word { display: inline; }
        .gk-stage .gk-head-about { margin-top: 1.25rem; }
        .gk-stage .gk-head-social { margin-top: 1rem; }
        .gk-stage .gk-head-social-link { width: 2.75rem; height: 2.75rem; border-radius: .75rem; }
        {{-- Gift cards and Submit event share the links' line, at its end. --}}
        .gk-stage .gk-head:has(.gk-head-social) .gk-head-more-actions { grid-area: social; justify-self: end; margin-top: 1rem; }
        {{-- With no links on the line, they still keep to its end edge. --}}
        .gk-stage .gk-head:not(:has(.gk-head-social)) .gk-head-more-actions { grid-area: social; justify-self: end; margin-top: 1rem; }
        .gk-stage .gk-head-tools { margin-top: 1.25rem; padding-top: 1rem; }
        .gk-stage .gk-head-tools-title { display: block; font-size: 1.25rem; }
        .gk-stage .gk-head-tool, .gk-stage .gk-head-seg { min-height: 2.75rem; }
    }
    {{-- One or two coming events (role/partials/calendar, .gk-few, his ask 2026-11-22): a list
         built for dozens left a lone event a small flyer at the side of an empty card. Each event
         is a large card instead: its picture whole and as big as the column allows (contained,
         with the same picture blurred behind it to fill the box, because a flyer's words run to
         its edges), its day said on the card, and the way in as a button at its foot. One event
         sets text and picture side by side across the page; two stand side by side as posters,
         one shape for both, so their feet line up. Three or more are the list. A phone stacks them, picture on top.
         The button is outlined in the readable accent, as the list's own: inside the list
         --es-accent is the owner's colour as typed, which --es-accent-text was not made for. --}}
    .gk-few { display: grid; gap: 1.25rem; margin: 0; padding: 0; list-style: none; }
    .gk-few .gk-row-item { overflow: hidden; border-radius: 1.5rem; }
    .gk-few .gk-row, .gk-few .gk-row:not(.gk-row-bare) { display: flex; flex-direction: column; height: 100%; padding: 0; }
    .gk-few .gk-row-media { order: -1; position: relative; overflow: hidden; width: 100%; min-height: 0; max-height: 36rem; margin: 0; aspect-ratio: var(--gk-ratio, .8); background: var(--gk-well); }
    .gk-few .gk-row-media a { position: relative; display: block; width: 100%; height: 100%; }
    .gk-few .gk-row-img-fill { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; opacity: .7; filter: blur(1.5rem) saturate(1.15); transform: scale(1.2); }
    .gk-few .gk-row-img { position: relative; width: 100%; height: 100%; object-fit: contain; }
    .gk-few .gk-row-media-day { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .125rem; aspect-ratio: 2 / 1; background: linear-gradient(140deg, color-mix(in srgb, var(--es-accent) 22%, var(--gk-solid)), var(--gk-well)); color: var(--gk-ink); font-size: 1.0625rem; font-weight: 650; }
    .gk-few .gk-row-media-day b { font-size: 4rem; font-weight: 800; line-height: 1; }
    .gk-few .gk-row-day { display: flex; flex-wrap: wrap; align-items: baseline; gap: .125rem .5rem; padding: 1.25rem 1.25rem 0; font-size: .9375rem; font-weight: 700; color: var(--es-accent-readable); }
    .gk-few .gk-row-day b { font-size: .8125rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
    .gk-few .gk-row-time { padding: .125rem 1.25rem 0; font-size: .9375rem; font-weight: 600; color: var(--gk-ink-2); }
    .gk-few .gk-row-body { flex: 1 1 auto; gap: .375rem; padding: .5rem 1.25rem 1.25rem; }
    .gk-few .gk-row-title { font-size: 1.5rem; font-weight: 800; line-height: 1.2; overflow-wrap: anywhere; }
    .gk-few .gk-row-desc { -webkit-line-clamp: 3; font-size: .9375rem; }
    .gk-few .gk-row-where { font-size: .9375rem; }
    .gk-few .gk-row-body > :nth-last-child(2) { margin-bottom: .875rem; }
    .gk-few .gk-row-more { display: inline-flex; align-items: center; justify-content: center; align-self: flex-start; min-height: 2.75rem; margin-top: auto; padding: 0 1.25rem; border: 1px solid color-mix(in srgb, var(--es-accent-readable) 55%, transparent); border-radius: .75rem; color: var(--es-accent-readable); font-size: .9375rem; font-weight: 700; }
    {{-- The card stays as it is under the pointer (a poster turning grey reads as disabled);
         its button answers, as an outlined button does everywhere on these pages. --}}
    .gk-stage .gk-few .gk-row-item:hover, .gk-few .gk-row:hover { background: var(--gk-solid); }
    .gk-few .gk-row-edit { max-width: calc(100% - 2.5rem); inset-inline-start: 1.25rem; }
    :where(.gk-few .gk-row-editable) .gk-row-body { padding-bottom: 2.75rem; }
    @media (min-width: 48rem) {
        .gk-few-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.5rem; }
        .gk-few-2 .gk-row-media { max-height: none; aspect-ratio: var(--gk-box, .8); }
        .gk-few-2 .gk-row-title { font-size: 1.75rem; }
        .gk-few-1 .gk-row, .gk-few-1 .gk-row:not(.gk-row-bare) { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, min(27rem, 45%)); grid-template-areas: ". i" "d i" "t i" "b i" ". i"; grid-template-rows: 1fr auto auto auto 1fr; }
        .gk-few-1 .gk-row-media { grid-area: i; align-self: stretch; height: auto; max-height: none; }
        .gk-few-1 .gk-row-media-day { aspect-ratio: 4 / 5; }
        .gk-few-1 .gk-row-media-day b { font-size: 6rem; }
        .gk-few-1 .gk-row-day { grid-area: d; padding: 2.5rem 2.5rem 0; font-size: 1.25rem; }
        .gk-few-1 .gk-row-time { grid-area: t; padding: .25rem 2.5rem 0; font-size: 1.125rem; }
        .gk-few-1 .gk-row-body { grid-area: b; gap: .625rem; padding: 1rem 2.5rem 2.5rem; }
        .gk-few-1 .gk-row-title { font-size: 2.75rem; line-height: 1.1; letter-spacing: -.01em; }
        .gk-few-1 .gk-row-desc { -webkit-line-clamp: 6; font-size: 1.0625rem; }
        .gk-few-1 .gk-row-where, .gk-few-1 .gk-row-who { font-size: 1rem; }
        .gk-few-1 .gk-row-more { margin-top: .75rem; min-height: 3.25rem; padding: 0 1.75rem; border-radius: .875rem; font-size: 1.0625rem; }
        .gk-few-1 .gk-row-edit { inset-inline-start: 2.5rem; bottom: 1.25rem; max-width: calc(55% - 5rem); }
        :where(.gk-few-1 .gk-row-editable) .gk-row-body { padding-bottom: 4rem; }
    }
    {{-- One hover per kind of button (his ask 2026-11-22), the one the event page's own buttons
         already had: a filled button in the accent brightens a little (Follow lifted a shadow you
         could not see on the dark sheet, Keep me posted brightened, the event page's main button
         grew), and an outlined or quiet one
         lights up behind its words (the List / Calendar switch had nothing at all, and Add photo, video and comment filled
         with the accent and grew). A link in the
         foot pill darkens and underlines its words, never its mark. --}}
    .gk-fill-btn { transition: filter var(--gk-swap, 120ms), transform var(--gk-swap, 120ms); }
    .gk-btn-primary:hover, .gk-sub-btn:hover, .gk-fill-btn:hover { filter: brightness(1.08); box-shadow: 0 1px 2px rgb(15 23 42 / .18); }
    .gk-btn-primary:active, .gk-sub-btn:active, .gk-fill-btn:active, .gk-btn-secondary:active, .gk-head-tool:active, .gk-cta-alt:active, .gk-cta-icon:active, .accent-hover-btn:active { transform: scale(.985); }
    {{-- The well alone moved a dark sheet's button by some seven shades, which nobody sees, so
         an outlined button's hover mixes in a tenth of the ink and firms its edge. --}}
    .gk-btn-secondary:hover, .gk-head-tool:hover, .gk-cta-alt:hover, .gk-cta-icon:hover, .accent-hover-btn:hover, .gk-card-btn:hover, .gk-few .gk-row-item:hover .gk-row-more { background: color-mix(in srgb, var(--gk-ink) 10%, var(--gk-solid)); border-color: color-mix(in srgb, var(--gk-ink) 30%, transparent); }
    .gk-few .gk-row-item:hover .gk-row-more { border-color: var(--es-accent-readable); }
    .gk-head-seg-btn:not([aria-pressed="true"]):hover { background: color-mix(in srgb, var(--gk-ink) 10%, transparent); color: var(--gk-ink); }
    .es-foot-link { display: inline-flex; align-items: center; gap: .375rem; transition: color var(--gk-swap, 120ms); }
    .es-foot-link:hover { color: #111827; }
    :where(.dark) .es-foot-link:hover { color: #ffffff; }
    .es-foot-link:hover .es-foot-word { text-decoration: underline; text-underline-offset: 3px; }
    {{-- The privacy link's mark, in colour beside the credit's: the same 1rem tile, a shield. --}}
    .es-foot-mark { display: inline-grid; flex: none; place-items: center; width: 1rem; height: 1rem; border-radius: 5px; background: linear-gradient(135deg, #059669, #34d399); color: #ffffff; }
    .es-foot-mark svg { width: .6875rem; height: .6875rem; }
</style>
