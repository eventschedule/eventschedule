{{-- The month kit: a schedule's month, the card an event opens beside its day, and the panel
     that lists a whole day. One stylesheet for the four pages that show a month (the guest
     page, the embed, the admin's Schedule tab and the dashboard), included once by each layout.

     Plain CSS on the guest kit's tokens (--gk-*, and the schedule's own --es-accent family),
     so no CSS build; sizes in rem; logical properties, so a right-to-left schedule mirrors.
     It is printed after partials/guest-kit-styles and before an owner's custom CSS.

     How a day is written is decided in role/partials/month-script (the list's own class says
     how many lines a name may take; the script marks what no stylesheet can know: a name that
     was cut, a month set a size smaller). The names here are the script's too: change both. --}}
<style {!! nonce_attr() !!}>
    /* The month's fill (today's number, a bar over several days, the tints) is the page's accent,
       read on body, where the theme's tokens are declared (partials/guest-theme; the bridge at
       the foot of this file for the admin's pages). The calendar's wrapper sets --es-accent
       inline as the owner typed it, and the label for a fill (--es-accent-text) is made for the
       fill the theme chose: read under the wrapper, a black accent on a dark page drew today's
       number black on black. Nothing inside .gk-cal reads --es-accent itself. */
    body { --cal-fill: var(--es-accent, #4E81FA); }
    .gk-cal {
        --cal-line: var(--gk-line);
        --cal-hover: color-mix(in srgb, var(--cal-fill) 12%, var(--gk-well));
        --cal-on: color-mix(in srgb, var(--cal-fill) 30%, var(--gk-well));
        --cal-out: color-mix(in srgb, var(--gk-well) 55%, transparent);
        position: relative;
        container: gkcal / inline-size;
        border: 1px solid var(--cal-line);
        border-radius: .875rem;
        overflow: hidden;
        color: var(--gk-ink);
        font-size: .875rem;
        line-height: 1.3;
    }
    .gk-cal-head { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); border-bottom: 1px solid var(--cal-line); }
    .gk-cal-wd { padding: .5rem .5625rem .4375rem; font-size: .6875rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--gk-ink-3); text-align: start; }
    .gk-cal-wd abbr { text-decoration: none; }
    .gk-cal-wd-now { color: var(--es-accent-readable); }
    /* A week is as tall as its fullest day. A week that is wholly over, in the month that holds
       today, is given less room, so the week it is now is not pushed down the page by the ones
       behind it. (.gk-cal-week-past is that week only: never today's, never a past month's.) */
    .gk-cal-weeks { display: grid; }
    .gk-cal-week { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); min-height: 10rem; }
    .gk-cal-week-past { min-height: 6.5rem; }
    .gk-cal-bare .gk-cal-week { min-height: 5.5rem; }
    .gk-cal-week + .gk-cal-week { border-top: 1px solid var(--cal-line); }

    /* A day: its number, then whatever is on, filling the week's height. */
    .gk-cal-day { position: relative; display: flex; flex-direction: column; min-width: 0; padding: .25rem .25rem .375rem; }
    .gk-cal-day + .gk-cal-day { border-inline-start: 1px solid var(--cal-line); }
    .gk-cal-day-out { background: var(--cal-out); }
    .gk-cal-day-today { background: color-mix(in srgb, var(--es-accent-tint) 62%, transparent); }
    /* In the dark a tint of the accent is mud, and a lighter cell reads as the loudest: other
       months' days go darker than this one's, and today is a rule along its top and its number. */
    :where(.dark) .gk-cal { --cal-out: rgb(0 0 0 / .24); }
    :where(.dark) .gk-cal-day-today { background: color-mix(in srgb, var(--cal-fill) 7%, transparent); box-shadow: inset 0 2px 0 var(--es-accent-readable); }
    .gk-cal-dayhead { display: flex; flex: none; align-items: center; gap: .375rem; min-height: 1.625rem; margin-bottom: .125rem; padding-inline: .0625rem; }
    .gk-cal-num { display: inline-grid; place-items: center; min-width: 1.625rem; height: 1.625rem; padding: 0 .375rem; border: 0; border-radius: 999px; background: none; color: var(--gk-ink-2); font: inherit; font-size: .8125rem; font-weight: 650; font-variant-numeric: tabular-nums; white-space: nowrap; }
    button.gk-cal-num { cursor: pointer; transition: background-color var(--gk-swap, 120ms); }
    button.gk-cal-num:hover { background: var(--cal-hover); }
    .gk-cal-num:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 1px; }
    .gk-cal-day-out .gk-cal-num { color: var(--gk-ink-3); font-weight: 500; }
    .gk-cal-day-past .gk-cal-num { color: var(--gk-ink-3); font-weight: 500; }
    .gk-cal-day-today .gk-cal-num { background: var(--cal-fill); color: var(--es-accent-text); box-shadow: 0 0 0 1px var(--es-accent-edge); font-weight: 800; }
    .gk-cal-word { font-size: .6875rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--es-accent-readable); }
    .gk-cal-add { display: inline-grid; place-items: center; width: 1.5rem; height: 1.5rem; margin-inline-start: auto; border-radius: .5rem; color: var(--gk-ink-3); opacity: 0; transition: opacity var(--gk-swap, 120ms), background-color var(--gk-swap, 120ms); }
    .gk-cal-day:hover .gk-cal-add { opacity: 1; }
    .gk-cal-add:focus-visible { opacity: 1; outline: 2px solid var(--gk-ink); outline-offset: 1px; }
    .gk-cal-add:hover { background: var(--cal-hover); color: var(--gk-ink); }
    .gk-cal-add svg { width: 1rem; height: 1rem; }

    .gk-cal-list { display: flex; flex: 1 1 auto; flex-direction: column; gap: .0625rem; min-width: 0; margin: 0; padding: 0; list-style: none; }
    .gk-cal-list li { min-width: 0; }

    /* An event, written one way at every density: its name, and the time at the end of the first
       line; under it, at most one line for what it costs or the state it is in. */
    .gk-cal-ev { display: block; min-width: 0; padding: .1875rem .3125rem; border-radius: .4375rem; color: var(--gk-ink); text-decoration: none; transition: background-color 80ms; }
    .gk-cal-ev:hover { background: var(--cal-hover); }
    .gk-cal-ev:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: -1px; }
    .gk-cal-ev-on { background: var(--cal-on); }
    .gk-cal-ev-on:hover { background: var(--cal-on); }
    /* The quieter inks of an event (its time, a plain note, a past or a cancelled name) hold
       4.5:1 on the panel and not on a tint. Pointed at, they step to the second ink; while the
       event's card is open, everything on it is the full ink, the one colour that reads on that
       tint for any accent a schedule can have, a state's green or amber included. Measured over
       fourteen accents and modes: 6.4:1 and 4.9:1 at the worst, where the third ink fell to 3.7
       and 2.1. (A bar over several days is not one of these: its words stand on the fill.) */
    .gk-cal-ev, .gk-cal-more { --cal-quiet: var(--gk-ink-3); }
    .gk-cal-ev:hover, .gk-cal-more:hover { --cal-quiet: var(--gk-ink-2); }
    .gk-cal-ev.gk-cal-ev-on, .gk-cal-ev.gk-cal-ev-on:hover { --cal-quiet: var(--gk-ink); --cal-say: var(--gk-ink); }
    .gk-cal-top { display: flex; align-items: baseline; gap: .3125rem; min-width: 0; }
    .gk-cal-dot { flex: none; align-self: flex-start; width: .4375rem; height: .4375rem; margin-top: .45em; border-radius: 999px; }
    .gk-cal-name { flex: 1 1 auto; min-width: 0; overflow: hidden; font-weight: 600; }
    /* The line runs the way the month does; only the name's own letters run the way the name does
       (.gk-cal-nm carries its direction), so it is cut at its own end. */
    /* How many lines a name may take is the day's to say. */
    /* On one line the name is cut with an ellipsis and the time stands after it. */
    .gk-cal-list-1 .gk-cal-name { display: flex; align-items: baseline; gap: .3125rem; font-weight: 500; white-space: nowrap; }
    .gk-cal-list-1 .gk-cal-nm { flex: 1 1 auto; order: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; }
    .gk-cal-list-1 .gk-cal-end { flex: none; order: 2; }
    /* On two lines (three where a day has one or two events) the time comes after the name and
       floats to the end of the line the name finishes on: every line before it has the day's
       whole width. A name too long for that ends in an ellipsis the script puts there, cut so
       the time still fits: none can be had from CSS on a line that shares its end. A word
       breaks only when it is longer than a whole line. */
    .gk-cal-list-wrap .gk-cal-name { display: block; max-height: 2.6em; max-height: 2lh; overflow-wrap: break-word; }
    .gk-cal-list-3 .gk-cal-name { max-height: 3.9em; max-height: 3lh; }
    .gk-cal-list-wrap .gk-cal-end { float: inline-end; margin-inline-start: .375rem; }
    .gk-cal-end { display: inline-flex; align-items: baseline; gap: .25rem; font-weight: 550; }
    .gk-cal-time { flex: none; font-size: .75rem; font-weight: 550; font-variant-numeric: tabular-nums; color: var(--cal-quiet, var(--gk-ink-3)); white-space: nowrap; }
    .gk-cal-flag { flex: none; align-self: center; display: inline-grid; place-items: center; width: .9375rem; height: .9375rem; color: var(--cal-quiet, var(--gk-ink-3)); }
    .gk-cal-flag svg { width: .9375rem; height: .9375rem; }
    .gk-cal-now { display: inline-flex; flex: none; align-items: center; gap: .3125rem; color: var(--gk-ink); white-space: nowrap; }
    .gk-cal-now::before { content: ''; width: .4375rem; height: .4375rem; border-radius: 999px; background: var(--gk-bad); animation: gk-peek-beat 1.6s ease-in-out infinite; }
    /* The line under a name. A state is never cut; a price gives way first. */
    .gk-cal-sub { display: flex; align-items: center; gap: .375rem; min-width: 0; margin-top: .0625rem; font-size: .75rem; line-height: 1.3; }
    .gk-cal-note { min-width: 0; overflow: hidden; font-weight: 600; color: var(--cal-quiet, var(--gk-ink-3)); text-overflow: ellipsis; white-space: nowrap; }
    .gk-cal-note-say { flex: none; overflow: visible; font-weight: 700; color: var(--cal-say, var(--gk-ink-2)); }
    .gk-cal-note-out { color: var(--cal-say, var(--gk-ink-2)); }
    .gk-cal-note-warn { color: var(--cal-say, var(--gk-warn)); }
    .gk-cal-note-ok { color: var(--cal-say, var(--gk-ok)); font-weight: 650; }
    .gk-cal-note + .gk-cal-note:not(.gk-cal-now)::before { content: '\00B7'; margin-inline-end: .375rem; color: var(--cal-quiet, var(--gk-ink-3)); font-weight: 600; }
    /* The time ends the event's last line. Where a line under the name says a state or a price
       (gk-cal-ev-under), that is the last line: the time stands at its end and the name keeps
       its own lines whole. Where a day is narrow it is under every name (see below). */
    .gk-cal-time-under { display: none; }
    .gk-cal-sub-bare { display: none; }
    .gk-cal-ev-under .gk-cal-end .gk-cal-time { display: none; }
    .gk-cal-ev-under .gk-cal-time-under { display: inline; order: 9; margin-inline-start: auto; }
    /* A first word longer than the whole line would break inside. The month is set a size
       smaller first (the script marks it; the whole month, so no day stands out), which nearly
       always keeps the word whole. */
    .gk-cal-tight .gk-cal-nm { font-size: .92em; letter-spacing: -.012em; }

    /* A day with ONE event: its picture, as tall as the week has room for. */
    .gk-cal-ev-feat { display: flex; flex-direction: column; padding: .25rem; }
    /* One height for every picture in a month, set by how wide a day is (8cqw: a day is a seventh
       of the month, 14.3cqw), so a week's pictures line up: six rem at least and nine at most;
       eight to ten for a portrait flyer, which is shown whole. */
    .gk-cal-art { position: relative; display: block; flex: none; overflow: hidden; height: 6rem; height: clamp(6rem, 8cqw, 9rem); margin-bottom: .3125rem; border-radius: .5rem; background: var(--gk-well); }
    .gk-cal-art-tall { height: 8rem; height: clamp(8rem, 10cqw, 10rem); }
    .gk-cal-art-img { position: absolute; inset: 0; display: block; width: 100%; height: 100%; object-fit: cover; object-position: 50% 30%; transition: transform 320ms cubic-bezier(.2, .7, .2, 1); }
    .gk-cal-art-glow { display: none; position: absolute; inset: -25%; width: 150%; height: 150%; max-width: none; object-fit: cover; filter: blur(12px) saturate(1.4) brightness(.8); }
    /* A portrait flyer: the whole of it, on a blur of its own colours. */
    .gk-cal-art-tall .gk-cal-art-glow { display: block; }
    .gk-cal-art-tall .gk-cal-art-img { object-fit: contain; object-position: center; padding: .25rem 0; filter: drop-shadow(0 1px 4px rgb(0 0 0 / .45)); }
    .gk-cal-ev-feat:hover .gk-cal-art-img { transform: scale(1.04); }
    .gk-cal-ev-feat .gk-cal-top { padding-inline: .125rem; }
    .gk-cal-ev-feat .gk-cal-sub { padding-inline: .125rem; }
    .gk-cal-ev-feat .gk-cal-name { font-weight: 700; line-height: 1.25; }
    .gk-cal-ev-bare .gk-cal-name { max-height: 5.2em; max-height: 4lh; font-size: .9375rem; }
    /* With no picture to fill the day, it is as tall as its words and no taller. */
    .gk-cal-list-feat li:has(.gk-cal-ev-bare) { flex: none; }

    /* What is over is quieter by its ink and its weight, never by fading: it still has to be read.
       Its picture is the whole of it still, in grey, at its smallest size. */
    .gk-cal-ev-past .gk-cal-name { color: var(--cal-quiet, var(--gk-ink-3)); font-weight: 500; }
    .gk-cal-ev-past .gk-cal-art { height: 6rem; filter: grayscale(1); opacity: .78; }
    .gk-cal-ev-past .gk-cal-art-tall { height: 8rem; }
    .gk-cal-list-feat li:has(.gk-cal-ev-past) { flex: none; }
    .gk-cal-ev-off .gk-cal-name { color: var(--cal-quiet, var(--gk-ink-3)); text-decoration: line-through; }

    /* "+4 more": the rest of the day, one press away, with the hours it covers. The row is one line
       high and wraps: what does not fit (the hours are last) falls to a second line nobody sees,
       so a time is dropped whole and never cut short. */
    .gk-cal-more { display: flex; flex-wrap: wrap; align-items: baseline; align-content: flex-start; column-gap: .375rem; row-gap: 1rem; width: 100%; height: 1.5rem; overflow: hidden; padding: .25rem .3125rem; border: 0; border-radius: .4375rem; background: none; color: var(--gk-ink-2); font: inherit; font-size: .75rem; line-height: 1.3; text-align: start; cursor: pointer; transition: background-color 80ms; }
    .gk-cal-more:hover { background: var(--cal-hover); color: var(--gk-ink); }
    .gk-cal-more:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: -1px; }
    .gk-cal-more-n { flex: none; font-weight: 700; }
    .gk-cal-more-from { flex: none; color: var(--cal-quiet, var(--gk-ink-3)); font-weight: 500; font-variant-numeric: tabular-nums; white-space: nowrap; }
    .gk-cal-more-say { flex: none; font-weight: 700; color: var(--gk-ink-2); }
    .gk-cal-day-past .gk-cal-more { color: var(--cal-quiet, var(--gk-ink-3)); }
    .gk-cal-day-past .gk-cal-more-n { font-weight: 500; }

    /* A day a team member marked themselves away (the admin's Schedule tab). */
    .gk-cal-day-away { background: var(--gk-warn-bg); }
    .gk-cal-away { display: inline-grid; place-items: center; width: 1.375rem; height: 1.375rem; border-radius: 999px; color: var(--gk-warn); cursor: help; }
    .gk-cal-away:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 1px; }
    .gk-cal-away svg { width: 1.125rem; height: 1.125rem; }
    .gk-dayp-away { flex: none; margin: 0; padding: .4375rem .875rem; border-bottom: 1px solid var(--gk-line); background: var(--gk-warn-bg); color: var(--gk-warn); font-size: .8125rem; }

    /* The Availability tab: the same month with nothing on it but its days, and a day is what
       is pressed (role/partials/calendar draws it, role/show-admin wires it). The mark lies under
       the day's number and says its word from data-label, so no language is in this file. */
    .gk-cal-pick .gk-cal-week { min-height: 5.5rem; }
    .gk-cal-pick .gk-cal-dayhead { position: relative; z-index: 1; }
    .gk-cal-pick .day-element { cursor: pointer; transition: background-color var(--gk-swap, 120ms); }
    .gk-cal-pick .day-element:hover { background: var(--cal-hover); }
    .gk-cal-pick .day-element:focus-visible { z-index: 2; outline: 2px solid var(--gk-ink); outline-offset: -2px; }
    .day-x { position: absolute; inset: 0; display: flex; align-items: flex-end; padding: .4375rem .5625rem; background: var(--gk-bad-bg); pointer-events: none; }
    .day-element:hover .day-x { background: color-mix(in srgb, var(--gk-bad) 14%, var(--gk-solid)); }
    .day-x::after { content: attr(data-label); min-width: 0; overflow: hidden; font-size: .6875rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--gk-bad); text-overflow: ellipsis; white-space: nowrap; }
    @container gkcal (max-width: 40rem) {
        .gk-cal-pick .gk-cal-week { min-height: 3.5rem; }
        .gk-cal-pick .gk-cal-wd { padding-inline: .25rem; text-align: center; }
        .gk-cal-pick .gk-cal-dayhead { justify-content: center; }
        .day-x::after { content: none; }
    }

    /* The month while it loads. */
    .gk-cal-wait-bar { display: block; border-radius: .25rem; background: var(--gk-line); }
    .gk-cal-wait-wd { width: 1.75rem; height: .6875rem; }
    .gk-cal-wait-num { width: 1.25rem; height: 1.125rem; margin: .25rem .1875rem .5rem; }
    .gk-cal-wait-line { height: .75rem; margin: 0 .3125rem .375rem; }
    .gk-cal-wait-short { width: 60%; }

    /* The other dates of the event being looked at. */
    /* In ink, not in the schedule's colour: a red ring reads as an error and a navy one is lost in the dark. */
    .gk-cal-kin { box-shadow: inset 0 0 0 1.5px color-mix(in srgb, var(--gk-ink) 46%, transparent); }
    .gk-cal-span-veiled .gk-cal-name { visibility: hidden; }
    .gk-cal-span-veiled { opacity: .45; }
    /* Today's lead: the picture of what is on now or next, over the day's list. */
    .gk-cal-ev-lead { margin-bottom: .125rem; }

    /* An event that runs over several days is one bar across them, in the schedule's colour. The
       bar lives in the first day of its run in a week and is as wide as the run (--span days);
       the days after it hold a blank of the same height, so everything under it lines up. */
    .gk-cal-lanes { display: grid; flex: none; grid-template-columns: minmax(0, 1fr); gap: .125rem; margin-bottom: .125rem; }
    .gk-cal-lane { height: 1.375rem; }
    .gk-cal-span { position: relative; z-index: 1; display: flex; align-items: baseline; gap: .4375rem; box-sizing: border-box; width: calc(var(--span) * 100% + (var(--span) - 1) * (.5rem + 1px)); height: 1.375rem; padding: 0 .5rem; border-radius: .4375rem; background: var(--cal-fill); box-shadow: 0 0 0 1px var(--es-accent-edge); color: var(--es-accent-text); font-size: .8125rem; line-height: 1.375rem; text-decoration: none; transition: filter 80ms; }
    .gk-cal-span:hover { filter: brightness(.93) saturate(1.1); }
    .gk-cal-span.gk-cal-kin { box-shadow: 0 0 0 1px var(--es-accent-edge); filter: brightness(.93) saturate(1.1); }
    .gk-cal-span.gk-cal-ev-on { background: var(--cal-fill); filter: brightness(.93) saturate(1.1); }
    /* A past bar keeps its own quiet band while its card is open: its words are ink, not the fill's label. */
    .gk-cal-span-past.gk-cal-ev-on { background: color-mix(in srgb, var(--cal-fill) 22%, var(--gk-solid)); }
    .gk-cal-span:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 1px; }
    .gk-cal-span-in { border-start-start-radius: 0; border-end-start-radius: 0; margin-inline-start: -.25rem; width: calc(var(--span) * 100% + (var(--span) - 1) * (.5rem + 1px) + .25rem); }
    .gk-cal-span-out { border-start-end-radius: 0; border-end-end-radius: 0; width: calc(var(--span) * 100% + (var(--span) - 1) * (.5rem + 1px) + .25rem); }
    .gk-cal-span-in.gk-cal-span-out { width: calc(var(--span) * 100% + (var(--span) - 1) * (.5rem + 1px) + .5rem); }
    .gk-cal-span .gk-cal-name { flex: 0 1 auto; font-weight: 700; text-overflow: ellipsis; white-space: nowrap; }
    .gk-cal-span-past { background: color-mix(in srgb, var(--cal-fill) 22%, var(--gk-solid)); color: var(--gk-ink-2); }

    /* Where a day is narrower than about 150px (windows from 768 to 1279, the admin beside its
       sidebar) there is no room for a word and the time on one line. So there:
       - the time is under the name, and the name has every line to itself;
       - the time and the state share that line where they fit and take a line each where they
         do not (no dot between them: one would start a line);
       - a state may wrap as a last resort, and nothing leaves its day;
       - the type is a size smaller, and a word breaks only when it is longer than the whole line;
       - a price is not said (the card says it). A state always is. */
    @container gkcal (max-width: 65.6rem) {
        .gk-cal-list-wrap .gk-cal-end .gk-cal-time { display: none; }
        .gk-cal-list-wrap .gk-cal-time-under { display: inline; order: 0; margin-inline-start: 0; }
        .gk-cal-list-wrap .gk-cal-sub-bare { display: flex; }
        .gk-cal-sub { flex-wrap: wrap; column-gap: .375rem; row-gap: 0; }
        .gk-cal-note + .gk-cal-note:not(.gk-cal-now)::before { content: none; }
        .gk-cal-note-say { flex: 0 1 auto; min-width: 0; white-space: normal; overflow-wrap: anywhere; }
        /* A size smaller, so that a leading mark and a first word of twelve letters still share a line. */
        .gk-cal-ev { overflow: hidden; font-size: .8125rem; }
        .gk-cal-sub { font-size: .71875rem; }
        .gk-cal-time { font-size: .71875rem; }
        .gk-cal-note-price { display: none; }
        .gk-cal-word { display: none; }
        .gk-cal-art-tall { height: 6rem; }
        .gk-cal-ev-past .gk-cal-art-tall { height: 6rem; }
    }


    /* A month with nothing in it says so in a strip above its weeks, over nobody's day, and
       offers the way to what is next. */
    .gk-cal-empty { border-bottom: 1px solid var(--cal-line); background: var(--gk-well); }
    .gk-cal-empty-card { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: .375rem 1rem; padding: .75rem 1rem; }
    .gk-cal-empty-card b { font-size: .9375rem; }
    .gk-cal-empty-card span { color: var(--gk-ink-2); font-size: .875rem; }
    .gk-cal-empty-go { display: flex; flex-wrap: wrap; justify-content: center; gap: .5rem; }
    .gk-cal-empty-go:empty { display: none; }
    .gk-cal-empty-btn { min-height: 2.25rem; }

    /* The month arriving from the side it was asked from. */
    @keyframes gk-cal-in-next { from { opacity: 0; transform: translateX(1rem); } to { opacity: 1; transform: none; } }
    @keyframes gk-cal-in-prev { from { opacity: 0; transform: translateX(-1rem); } to { opacity: 1; transform: none; } }
    .gk-cal-weeks-next { animation: gk-cal-in-next 200ms cubic-bezier(.2, .7, .2, 1); }
    .gk-cal-weeks-prev { animation: gk-cal-in-prev 200ms cubic-bezier(.2, .7, .2, 1); }
    @keyframes gk-cal-pulse { 0% { box-shadow: inset 0 0 0 2px var(--es-accent-readable); } 100% { box-shadow: inset 0 0 0 2px transparent; } }
    .gk-cal-day-pulse { animation: gk-cal-pulse 900ms ease-out; }

    /* The card's own buttons and chips. They are the guest kit's (gk-btn, gk-chip) said again
       under the month's names, because the admin and the dashboard show a month and have no
       guest kit: the month kit stands on the tokens and on nothing else. */
    .gk-peek-btn { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; min-height: 2.75rem; padding: 0 .875rem; border: 1px solid transparent; border-radius: var(--gk-radius-ctl, .625rem); font-size: .875rem; font-weight: 650; line-height: 1.2; text-align: center; text-decoration: none; cursor: pointer; transition: background-color var(--gk-swap, 120ms), box-shadow var(--gk-swap, 120ms); }
    .gk-peek-btn:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 2px; }
    .gk-peek-btn-primary { background: var(--es-accent); color: var(--es-accent-text); border-color: var(--es-accent-edge); box-shadow: 0 1px 2px rgb(15 23 42 / .18); }
    .gk-peek-btn-primary:hover { box-shadow: var(--gk-shadow-lift); }
    .gk-peek-btn-secondary { background: var(--gk-solid); color: var(--gk-ink); border-color: var(--gk-line); }
    .gk-peek-btn-secondary:hover { background: var(--gk-well); }
    .gk-peek-chip { display: inline-flex; align-items: center; gap: .3125rem; min-height: 1.5rem; padding: 0 .5625rem; border: 1px solid transparent; border-radius: 999px; background: var(--gk-well); color: var(--gk-ink-2); font-size: .78125rem; font-weight: 650; line-height: 1.2; white-space: nowrap; }
    .gk-peek-chip-free { background: var(--gk-ok-bg); color: var(--gk-ok); border-color: var(--gk-ok-line); }
    .gk-peek-chip-accent { background: var(--es-accent-tint); color: var(--es-accent-readable); }

    /* ---- The docked preview ---------------------------------------------------------------- */
    .gk-peek { --peek-bg: var(--gk-solid); position: fixed; top: 0; left: 0; z-index: 10000; width: 20rem; max-width: calc(100vw - 1rem); opacity: 0; visibility: hidden; pointer-events: none; transition: opacity 110ms ease, visibility 0s linear 110ms; will-change: transform; }
    /* A raised thing is a step lighter than what it stands on in the dark, where a shadow says little. */
    :where(.dark) .gk-peek { --peek-bg: rgb(46 46 50); --peek-glow: .2; }
    .gk-peek-shown { opacity: 1; visibility: visible; pointer-events: auto; transition: opacity 150ms ease, transform 190ms cubic-bezier(.2, .8, .2, 1), visibility 0s; }
    .gk-peek-first { transition: opacity 150ms ease, visibility 0s; }
    .gk-peek-card { position: relative; display: flex; flex-direction: column; overflow: hidden; border-radius: 1rem; background: var(--peek-bg); color: var(--gk-ink); box-shadow: 0 0 0 1px var(--gk-line), 0 1.5rem 3.25rem -1rem rgb(15 23 42 / .38), 0 1.75rem 3.5rem -1.5rem rgb(var(--es-glow, 78 129 250) / var(--peek-glow, .4)); transform-origin: var(--peek-ox, 0) var(--peek-oy, 2rem); }
    @keyframes gk-peek-arrive { from { transform: scale(.965) translateX(var(--peek-from, -.375rem)); } to { transform: none; } }
    .gk-peek-first .gk-peek-card { animation: gk-peek-arrive 190ms cubic-bezier(.2, .8, .2, 1); }
    .gk-peek-caret { position: absolute; top: 0; z-index: 2; width: .75rem; height: .75rem; background: var(--peek-bg); box-shadow: -1px 1px 0 0 var(--gk-line); transform: translateY(var(--peek-cy, 2rem)) rotate(45deg); transition: transform 140ms cubic-bezier(.2, .8, .2, 1); }
    .gk-peek[data-side="right"] .gk-peek-caret { left: -.375rem; }
    .gk-peek[data-side="left"] .gk-peek-caret { right: -.375rem; box-shadow: 1px -1px 0 0 var(--gk-line); }
    .gk-peek[data-side="over"] .gk-peek-caret { display: none; }
    /* Beside the picture it would be a white corner on a photograph: the lit event says which. */
    .gk-peek-nocaret .gk-peek-caret { display: none; }

    .gk-peek-media { position: relative; display: block; flex: none; overflow: hidden; height: 10rem; background: var(--gk-well); }
    .gk-peek-glow { position: absolute; inset: -18%; width: 136%; height: 136%; max-width: none; object-fit: cover; filter: blur(22px) saturate(1.5); opacity: .95; }
    .gk-peek-img { position: relative; display: block; width: 100%; height: 100%; object-fit: cover; object-position: 50% 30%; }
    .gk-peek-media-tall { height: 13.5rem; }
    .gk-peek-media-tall .gk-peek-img { object-fit: contain; object-position: center; filter: drop-shadow(0 .5rem 1rem rgb(0 0 0 / .35)); padding: .625rem; }
    .gk-peek-state { position: absolute; inset-block-start: .625rem; inset-inline-start: .625rem; z-index: 1; display: flex; flex-wrap: wrap; gap: .375rem; }
    .gk-peek-pill { display: inline-flex; align-items: center; gap: .3125rem; padding: .25rem .5625rem; border-radius: 999px; background: rgb(17 24 39 / .86); color: #fff; font-size: .6875rem; font-weight: 800; letter-spacing: .05em; line-height: 1.2; text-transform: uppercase; -webkit-backdrop-filter: blur(6px); backdrop-filter: blur(6px); }
    .gk-peek-pill-warn { background: #f59e0b; color: #1c1300; }
    .gk-peek-pill-live::before { content: ''; width: .4375rem; height: .4375rem; border-radius: 999px; background: #f87171; animation: gk-peek-beat 1.6s ease-in-out infinite; }
    @keyframes gk-peek-beat { 50% { opacity: .35; } }
    .gk-peek-x { position: absolute; inset-block-start: .5rem; inset-inline-end: .5rem; z-index: 3; display: none; place-items: center; width: 2rem; height: 2rem; border: 0; border-radius: 999px; background: rgb(17 24 39 / .62); color: #fff; cursor: pointer; }
    .gk-peek-x svg { width: 1rem; height: 1rem; }
    .gk-peek-pinned .gk-peek-x { display: grid; }

    /* No picture: the day stands where the picture would. */
    .gk-peek-plain { display: flex; align-items: center; gap: .75rem; padding-block: .875rem 0; padding-inline: 1rem 5.25rem; }
    .gk-peek-pinned .gk-peek-plain { padding-inline-end: 7.75rem; }
    .gk-peek-tile { display: grid; place-items: center; align-content: center; flex: none; width: 3.25rem; height: 3.25rem; border: 1px solid var(--gk-line); border-radius: .75rem; background: var(--peek-bg); box-shadow: var(--gk-shadow); }
    .gk-peek-tile i { font-size: .625rem; font-style: normal; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: var(--es-accent-readable); }
    .gk-peek-tile b { font-size: 1.25rem; font-weight: 800; line-height: 1; }
    .gk-peek-plain-when { min-width: 0; }
    .gk-peek-plain-when .gk-peek-state { position: static; margin-bottom: .3125rem; }

    .gk-peek-body { display: flex; flex: 1 1 auto; flex-direction: column; gap: .4375rem; padding: .8125rem 1rem 1rem; }
    .gk-peek-when { margin: 0; font-size: .8125rem; font-weight: 650; color: var(--gk-ink-2); }
    .gk-peek-clock { white-space: nowrap; }
    .gk-peek-when b { margin-inline-end: .375rem; font-size: .6875rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--es-accent-readable); }
    .gk-peek-title { margin: 0; font-size: 1.125rem; font-weight: 800; line-height: 1.22; overflow-wrap: anywhere; }
    .gk-peek-title a { display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 3; overflow: hidden; color: inherit; text-decoration: none; }
    .gk-peek-title a:hover { text-decoration: underline; text-underline-offset: 3px; text-decoration-thickness: 1.5px; }
    .gk-peek-title a:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 2px; border-radius: .25rem; }
    .gk-peek-lock { display: inline-grid; margin-inline-end: .25rem; vertical-align: -.125rem; }
    .gk-peek-line { display: flex; align-items: center; gap: .4375rem; min-width: 0; margin: 0; font-size: .84375rem; color: var(--gk-ink-2); }
    .gk-peek-line svg { flex: none; width: 1rem; height: 1rem; color: var(--gk-ink-3); }
    .gk-peek-line span { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .gk-peek-line a { min-width: 0; overflow: hidden; color: inherit; text-decoration: underline; text-decoration-color: var(--gk-line); text-overflow: ellipsis; text-underline-offset: 3px; white-space: nowrap; }
    .gk-peek-line a:hover { text-decoration-color: currentColor; }
    .gk-peek-faces { display: inline-flex; flex: none; overflow: visible; }
    .gk-peek-faces img { width: 1.375rem; height: 1.375rem; border-radius: 999px; box-shadow: 0 0 0 2px var(--peek-bg); object-fit: cover; }
    .gk-peek-faces img + img { margin-inline-start: -.4375rem; }
    .gk-peek-desc { display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 3; margin: 0; overflow: hidden; font-size: .8125rem; line-height: 1.45; color: var(--gk-ink-2); }
    /* Its other dates: what the rings in the month are, in words, and one press away. */
    .gk-peek-also { display: flex; flex-wrap: wrap; align-items: center; gap: .25rem .3125rem; margin: 0; font-size: .78125rem; color: var(--gk-ink-3); }
    .gk-peek-also span { margin-inline-end: .125rem; font-weight: 650; color: var(--gk-ink-2); }
    .gk-peek-also button { padding: .0625rem .4375rem; border: 1px solid color-mix(in srgb, var(--gk-ink) 46%, transparent); border-radius: 999px; background: none; color: var(--gk-ink); font: inherit; font-weight: 650; cursor: pointer; }
    .gk-peek-also button:hover { background: var(--gk-well); }
    .gk-peek-also button:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 1px; }
    .gk-peek-also-out { border-color: var(--gk-line); color: var(--gk-ink-3); font-weight: 500; }
    .gk-peek-also i { font-style: normal; }
    .gk-peek-chips { display: flex; flex-wrap: wrap; align-items: center; gap: .375rem; margin-top: .0625rem; }
    .gk-peek-more { flex: none; }
    /* The card is as tall as what it has to say and stands on the week's foot, so its buttons stay
       where they are while the pointer runs down a day. */
    .gk-peek-actions { display: flex; align-items: stretch; gap: .5rem; margin-top: auto; padding-top: .375rem; }
    .gk-peek-actions .gk-peek-btn { min-height: 2.75rem; }
    .gk-peek-actions svg { width: 1.125rem; height: 1.125rem; }
    .gk-peek-go { flex: 1 1 auto; flex-wrap: wrap; column-gap: .375rem; row-gap: 0; padding-block: .25rem; }
    .gk-peek-go-price { font-weight: 600; opacity: .86; }
    .gk-peek-go-price::before { content: '\00B7'; margin-inline-end: .375rem; }
    /* Where the words and the price do not fit on one line (the script sees it) the price goes
       under the words, with no dot to start its line. */
    .gk-peek-go-2 { flex-direction: column; flex-wrap: nowrap; justify-content: center; row-gap: .0625rem; padding-block: .25rem; line-height: 1.15; }
    .gk-peek-go-2 .gk-peek-go-price { font-size: .75rem; }
    .gk-peek-go-2 .gk-peek-go-price::before { content: none; }
    /* Add to calendar and share: two small tools at the picture's corner, out of the button's way. */
    .gk-peek-tools { position: absolute; inset-block-start: .5rem; inset-inline-end: .5rem; z-index: 2; display: flex; gap: .375rem; }
    .gk-peek-pinned .gk-peek-tools { inset-inline-end: 2.875rem; }
    .gk-peek-tool { display: grid; place-items: center; width: 2rem; height: 2rem; padding: 0; border: 1px solid var(--gk-line); border-radius: 999px; background: var(--peek-bg); color: var(--gk-ink-2); cursor: pointer; transition: background-color 80ms; }
    .gk-peek-tool:hover { background: var(--gk-well); color: var(--gk-ink); }
    .gk-peek-tool:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 2px; }
    .gk-peek-tool svg { width: 1.0625rem; height: 1.0625rem; }
    .gk-peek-tools-over .gk-peek-tool { border-color: transparent; background: rgb(17 24 39 / .62); color: #fff; -webkit-backdrop-filter: blur(6px); backdrop-filter: blur(6px); }
    .gk-peek-tools-over .gk-peek-tool:hover { background: rgb(17 24 39 / .85); }
    .gk-peek-tools-over .gk-peek-tool:focus-visible { outline-color: #fff; }
    .gk-peek-menu { position: absolute; inset-inline-end: .5rem; top: 2.875rem; z-index: 4; display: grid; min-width: 12rem; padding: .3125rem; border: 1px solid var(--gk-line); border-radius: .75rem; background: var(--peek-bg); box-shadow: var(--gk-shadow-lift); }
    .gk-peek-menu[hidden] { display: none; }
    .gk-peek-menu a { display: flex; align-items: center; gap: .5rem; padding: .5rem .625rem; border-radius: .5rem; color: var(--gk-ink); font-size: .84375rem; font-weight: 600; text-decoration: none; }
    .gk-peek-menu a:hover { background: var(--gk-well); }
    .gk-peek-menu a:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: -2px; }
    .gk-peek-step { display: none; align-items: center; justify-content: space-between; gap: .5rem; padding: .375rem .5rem; border-top: 1px solid var(--gk-line); font-size: .78125rem; font-weight: 650; color: var(--gk-ink-2); }
    .gk-peek-pinned .gk-peek-step { display: flex; }
    .gk-peek-step button { display: grid; place-items: center; width: 2.25rem; height: 2.25rem; border: 0; border-radius: .5rem; background: none; color: var(--gk-ink-2); cursor: pointer; }
    .gk-peek-step button:hover { background: var(--gk-well); }
    .gk-peek-step button[disabled] { opacity: .35; cursor: default; }
    .gk-peek-step svg { width: 1.125rem; height: 1.125rem; }
    @keyframes gk-peek-swap { from { opacity: .35; } to { opacity: 1; } }
    .gk-peek-swap .gk-peek-card > * { animation: gk-peek-swap 130ms ease-out; }
    /* Beside narrow days it says less, so it covers less. */
    .gk-peek-compact .gk-peek-media { height: 7rem; }
    .gk-peek-compact .gk-peek-media-tall { height: 9rem; }
    .gk-peek-compact .gk-peek-desc { display: none; }
    .gk-peek-compact .gk-peek-title { font-size: 1rem; }
    /* A finger needs more to land on than a pointer does. */
    @media (pointer: coarse) {
        .gk-peek-tool { width: 2.75rem; height: 2.75rem; }
        .gk-peek-x { width: 2.75rem; height: 2.75rem; }
        .gk-peek-pinned .gk-peek-tools { inset-inline-end: 3.625rem; }
        .gk-peek-step button { width: 2.75rem; height: 2.75rem; }
        .gk-peek-also button { padding: .3125rem .625rem; }
    }

    /* ---- The day panel ---------------------------------------------------------------------- */
    .gk-dayp { --peek-bg: var(--gk-solid); position: fixed; top: 0; left: 0; z-index: 9990; display: flex; flex-direction: column; width: 23rem; max-width: calc(100vw - 1rem); max-height: min(33rem, calc(100vh - 1.5rem)); overflow: hidden; border-radius: 1rem; background: var(--peek-bg); color: var(--gk-ink); box-shadow: 0 0 0 1px var(--gk-line), 0 1.5rem 3.25rem -1rem rgb(15 23 42 / .4); opacity: 0; visibility: hidden; transform-origin: var(--dayp-ox, 2rem) var(--dayp-oy, 1rem); }
    :where(.dark) .gk-dayp { --peek-bg: rgb(46 46 50); }
    @keyframes gk-dayp-open { from { opacity: 0; scale: .94; } to { opacity: 1; scale: 1; } }
    .gk-dayp-shown { opacity: 1; visibility: visible; animation: gk-dayp-open 170ms cubic-bezier(.2, .8, .2, 1); }
    .gk-dayp-head { display: flex; align-items: center; gap: .125rem; flex: none; padding: .5rem .375rem .5rem .875rem; border-bottom: 1px solid var(--gk-line); }
    .gk-dayp-head h3 { flex: 1 1 auto; min-width: 0; margin: 0; font-size: .9375rem; font-weight: 800; line-height: 1.25; }
    .gk-dayp-head .gk-cal-word { margin-inline-end: .375rem; }
    .gk-dayp-count { margin-inline-end: .25rem; font-size: .75rem; font-weight: 650; color: var(--gk-ink-3); white-space: nowrap; }
    .gk-dayp-x { display: grid; place-items: center; flex: none; width: 2rem; height: 2.25rem; border: 0; border-radius: .625rem; background: none; color: var(--gk-ink-2); cursor: pointer; }
    .gk-dayp-x:hover { background: var(--gk-well); }
    .gk-dayp-x[disabled] { opacity: .3; cursor: default; }
    .gk-dayp-x:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: -2px; }
    .gk-dayp-x svg { width: 1.125rem; height: 1.125rem; }
    .gk-dayp-rows { flex: 1 1 auto; margin: 0; padding: .25rem .25rem .375rem; overflow-y: auto; list-style: none; overscroll-behavior: contain;
        /* A shade at an edge there is more beyond, which the list's own ends cover when reached. */
        background: linear-gradient(var(--peek-bg) 40%, transparent) top / 100% 1.75rem no-repeat local, linear-gradient(transparent, var(--peek-bg) 60%) bottom / 100% 1.75rem no-repeat local,
            linear-gradient(rgb(0 0 0 / .16), transparent) top / 100% .5rem no-repeat scroll, linear-gradient(transparent, rgb(0 0 0 / .16)) bottom / 100% .5rem no-repeat scroll; }
    .gk-dayp-row { display: grid; grid-template-columns: 3.75rem minmax(0, 1fr) 2.5rem; align-items: center; column-gap: .5rem; min-height: 3.25rem; padding: .375rem .5rem; border-radius: .625rem; color: var(--gk-ink); text-decoration: none; transition: background-color 80ms; }
    .gk-dayp-row:hover { background: var(--gk-well); }
    .gk-dayp-row:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: -2px; }
    .gk-dayp-time { font-size: .78125rem; font-weight: 700; font-variant-numeric: tabular-nums; color: var(--gk-ink-2); white-space: nowrap; }
    .gk-dayp-body { display: grid; gap: .0625rem; min-width: 0; }
    .gk-dayp-name { display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; overflow: hidden; font-size: .875rem; font-weight: 650; line-height: 1.25; overflow-wrap: anywhere; }
    .gk-dayp-name .gk-cal-flag { display: inline-grid; margin-inline-start: .25rem; vertical-align: -.125rem; }
    /* The price or the state is never cut; the place gives way to it. */
    .gk-dayp-sub { display: flex; align-items: baseline; gap: .3125rem; min-width: 0; font-size: .78125rem; color: var(--gk-ink-3); white-space: nowrap; }
    .gk-dayp-sub span { flex: 0 1 auto; min-width: 0; overflow: hidden; text-overflow: ellipsis; }
    .gk-dayp-sub i { flex: none; font-style: normal; }
    .gk-dayp-sub .gk-cal-now { flex: none; }
    .gk-dayp-note { flex: none; font-weight: 650; color: var(--gk-ink-2); }
    .gk-dayp-note.gk-cal-note-warn { color: var(--gk-warn); }
    .gk-dayp-note.gk-cal-note-ok { color: var(--gk-ok); }
    .gk-dayp-img { width: 2.5rem; height: 2.5rem; border-radius: .5rem; object-fit: cover; }
    .gk-dayp-row-past .gk-dayp-name { color: var(--gk-ink-3); font-weight: 500; }
    .gk-dayp-row-past .gk-dayp-img { filter: grayscale(1); opacity: .8; }
    .gk-dayp-row-off .gk-dayp-name { color: var(--gk-ink-3); text-decoration: line-through; }
    .gk-dayp-foot { flex: none; padding: .5rem; border-top: 1px solid var(--gk-line); }
    .gk-dayp-foot .gk-peek-btn { width: 100%; }

    /* An arrow that means "the one before" points the other way in a right-to-left month. */
    [dir="rtl"] > * .gk-flip { transform: scaleX(-1); }
    .gk-dayp-dot { display: inline-block; margin-inline-end: .3125rem; margin-top: 0; vertical-align: middle; }

    @media (prefers-reduced-motion: reduce) {
        .gk-cal-weeks-next, .gk-cal-weeks-prev, .gk-cal-day-pulse, .gk-dayp-shown, .gk-peek-first .gk-peek-card, .gk-peek-swap .gk-peek-card > * { animation: none; }
        .gk-peek-shown { transition: none; }
        .gk-peek-caret { transition: none; }
        .gk-cal-art-img { transition: none; }
        .gk-cal-ev-feat:hover .gk-cal-art-img { transform: none; }
        .gk-peek-pill-live::before, .gk-cal-now::before { animation: none; }
    }

    @if ($monthBridge ?? false)
    /* The admin's pages have no guest kit and no schedule's colours: the month's tokens are
       given the portal's own (the six palettes are --ap-*; the accent is the brand's).
       Two are mixed and not taken as they are, because the month sets small text in them and
       the portal's own do not hold 4.5:1 there: its third ink is 3.7:1 to 4.2:1 on a panel, so
       the month's is halfway to the second (5.1:1 at the least, on every surface of the month
       in all six palettes); and the brand blue as text is 3.2:1 on a light palette, so the
       readable accent is moved 30% toward the palette's ink (5:1 at the least). */
    body {
        --gk-panel: rgb(var(--ap-surface)); --gk-solid: rgb(var(--ap-surface)); --gk-well: rgb(var(--ap-surface-hover));
        --gk-ink: rgb(var(--ap-ink)); --gk-ink-2: rgb(var(--ap-ink-2)); --gk-ink-3: color-mix(in srgb, rgb(var(--ap-ink-2)) 50%, rgb(var(--ap-ink-3)));
        --gk-line: rgb(var(--ap-border-strong));
        --gk-ok: #166534; --gk-ok-bg: #f0fdf4; --gk-ok-line: #86efac;
        --gk-warn: #92400e; --gk-warn-bg: #fffbeb; --gk-warn-line: #fcd34d;
        --gk-bad: #b91c1c; --gk-bad-bg: #fef2f2; --gk-bad-line: #fca5a5;
        --gk-shadow: 0 1px 2px rgb(15 23 42 / .07), 0 1px 1px rgb(15 23 42 / .04);
        --gk-shadow-lift: 0 4px 12px rgb(15 23 42 / .16);
        --gk-radius-ctl: .625rem; --gk-swap: 120ms;
        --es-accent: var(--brand-button-bg); --es-accent-text: #ffffff; --es-accent-edge: transparent;
        --es-accent-readable: color-mix(in srgb, var(--brand-blue) 70%, rgb(var(--ap-ink))); --es-accent-tint: color-mix(in srgb, var(--brand-blue) 10%, rgb(var(--ap-surface)));
        --es-glow: 78 129 250;
    }
    :where(.dark) body {
        --gk-ok: #86efac; --gk-ok-bg: rgb(22 101 52 / .3); --gk-ok-line: rgb(134 239 172 / .4);
        --gk-warn: #fcd34d; --gk-warn-bg: rgb(146 64 14 / .3); --gk-warn-line: rgb(252 211 77 / .4);
        --gk-bad: #fca5a5; --gk-bad-bg: rgb(185 28 28 / .28); --gk-bad-line: rgb(252 165 165 / .4);
        --gk-shadow: 0 1px 2px rgb(0 0 0 / .4);
        --gk-shadow-lift: 0 4px 14px rgb(0 0 0 / .5);
    }
    /* A raised thing takes the palette's own raised surface, not the guest pages' grey. */
    :where(.dark) .gk-peek { --peek-bg: rgb(var(--ap-surface-hover)); }
    :where(.dark) .gk-dayp { --peek-bg: rgb(var(--ap-surface-hover)); }
    :where(.dark) .gk-cal { --cal-out: rgb(var(--ap-bg) / .6); }
    @endif
</style>
