{{-- The Events graphic page: one image and one caption of a schedule's upcoming events.

     One Vue mount on the page kit, with one copy of every field at every width (it used to be
     written twice, once for a phone, with script keeping the copies in step). The post (the image
     and its caption) is the page; the settings are a slim column of rows beside it; the picture
     redraws as a setting changes, and a save bar shows only while something is unsaved.

     What must hold (tests/Feature/GraphicPageTest.php):
     - Nobody's text is printed inside the mount. Settings reach Vue through ONE json directive
       on a bare variable built in the php block below (the directive splits its argument on
       commas), and the schedule's own timezone carries v-pre. No directive is named with its
       at-sign in this comment: Blade pairs a php block's opening with the next closing one
       before it removes comments, and this page did not compile until that was learned.
     - The page posts back what GraphicController::pageSettings() handed it, so a stored value
       the page cannot show must never make a save fail.
     - Only what the plan shows is sent: a locked part keeps what it holds.
     - Each row carries data-row-group and aria-controls, which is how the Help link follows it
       (layouts/navigation, HelpUtils).
     The preview's query string and the save's body are GraphicController's contract, read by
     the scheduled email too: a new setting goes through every place the controller's own
     docblocks list. --}}
<x-app-admin-layout>
    <x-slot name="head">
        <style {!! nonce_attr() !!}>
            /* Two sides from a laptop up: settings, then the post. Below that the post comes first. */
            .gfx {
                display: grid;
                grid-template-columns: minmax(0, 1fr);
                gap: 1rem;
            }
            .gfx-controls { order: 2; min-width: 0; }
            .gfx-stage { order: 1; min-width: 0; }
            @media (min-width: 1280px) {
                .gfx {
                    grid-template-columns: 22.5rem minmax(0, 1fr);
                    align-items: start;
                }
                .gfx-controls { order: 1; }
                /* The card fills what the window has left under the page's head (and above the
                   save bar while that is up): the page's script measures it, because a notice
                   above or the bar below changes it and a guess put the bar over Download. */
                .gfx-stage {
                    order: 2;
                    position: sticky;
                    top: 5.5rem;
                    /* --gfx-top is where the two sides start, measured by the page's script (a
                       notice above moves it). Room for the save bar is kept at all times, so the
                       picture does not change size when the bar comes and goes. */
                    height: calc(100vh - var(--gfx-top, 14rem) - 1.5rem);
                    min-height: 27rem;
                    max-height: 62rem;
                }
                /* While the save bar is up the card ends above it. In a tall window that room is
                   kept at all times, so the picture does not change size as the bar comes and goes. */
                .gfx.has-bar .gfx-stage { height: calc(100vh - var(--gfx-top, 14rem) - 5.75rem); }
            }
            @media (min-width: 1280px) and (min-height: 860px) {
                .gfx-stage { height: calc(100vh - var(--gfx-top, 14rem) - 5.75rem); }
            }
            /* A window too short to hold the card: it is as tall as what it holds and scrolls
               with the page, as on one column. */
            @media (min-width: 1280px) and (max-height: 719.98px) {
                .gfx-stage { position: static; height: auto; min-height: 0; max-height: none; }
                .gfx-stage .gfx-post, .gfx-stage .gfx-pane.is-image, .gfx-stage .gfx-pane.is-caption, .gfx-stage .gfx-canvas { flex: none; }
                .gfx-stage .gfx-canvas > .gfx-shot, .gfx-stage .gfx-canvas > .gfx-scroll { position: static; inset: auto; max-width: 100%; }
                .gfx-stage .gfx-canvas img { max-height: 60vh; }
                .gfx-stage textarea.gfx-text { flex: none; height: calc(8 * 1.4375rem + 1.5rem + 2px); }
            }
            /* The setup guide's ring and the chat keep the far bottom corner; both ride above a
               page's own bar by this much (SetupGuide.vue, --sg-bar). */
            @media (min-width: 1024px) { :root { --sg-bar: 4.75rem; } }

            /* The settings: a card of what everyone sets, then rows that open in place. */
            .gfx-card { padding: 1.25rem; }
            .gfx-card + .gfx-card { margin-top: 1rem; }
            .gfx-label {
                display: block;
                margin: 0 0 0.5rem;
                font-size: 0.8125rem;
                font-weight: 600;
                color: rgb(var(--ap-ink));
            }
            .gfx-field + .gfx-field { margin-top: 1.125rem; }
            .gfx-hint {
                margin: 0.375rem 0 0;
                font-size: 0.8125rem;
                line-height: 1.25rem;
                color: rgb(var(--ap-ink-3));
            }
            .gfx-hint a, .gfx-hint button { color: var(--brand-blue); font-weight: 500; }
            .gfx-hint a:hover, .gfx-hint button:hover { text-decoration: underline; }
            .gfx-error {
                margin: 0.375rem 0 0;
                font-size: 0.8125rem;
                font-weight: 500;
                color: #b91c1c;
            }
            .dark .gfx-error { color: #fca5a5; }

            /* The layout's 1.15rem fields are for a form's column; these sit in a 24rem one. */
            .gfx-controls input[type="text"],
            .gfx-controls select,
            .gfx-controls textarea {
                width: 100%;
                padding: 0.5rem 0.75rem !important;
                border: 1px solid rgb(var(--ap-border-strong));
                border-radius: 0.5rem;
                background-color: rgb(var(--ap-surface));
                font-size: 0.9375rem !important;
                line-height: 1.375rem !important;
                color: rgb(var(--ap-ink));
            }
            .gfx-controls select { padding-inline-end: 2.25rem !important; }
            .gfx-controls textarea.is-code {
                font-family: ui-monospace, SFMono-Regular, Menlo, monospace !important;
                font-size: 0.8125rem !important;
                line-height: 1.375rem !important;
            }
            .gfx-controls input:focus, .gfx-controls select:focus, .gfx-controls textarea:focus {
                border-color: var(--brand-blue);
                outline: none;
                box-shadow: 0 0 0 1px var(--brand-blue);
            }
            .gfx-controls .is-wrong { border-color: #dc2626; }

            /* The three layouts, each with a sketch of what it draws. A single choice is a group of
               real radios (arrow keys move it, a screen reader hears "1 of 3"): the input is out
               of sight and the span after it is the tile that is seen. */
            .gfx-tiles { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.5rem; }
            .gfx-tile, .gfx-shape, .gfx-opt { position: relative; display: block; min-width: 0; cursor: pointer; }
            .gfx-tile > span {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 0.5rem;
                padding: 0.75rem 0.5rem 0.625rem;
                border: 1px solid rgb(var(--ap-border-strong));
                border-radius: 0.75rem;
                background: rgb(var(--ap-surface));
                font-size: 0.875rem;
                font-weight: 600;
                color: rgb(var(--ap-ink-2));
                transition: all 0.2s;
            }
            .gfx-tile:hover > span, .gfx-shape:hover > span, .gfx-opt:hover > span { border-color: var(--brand-blue); }
            .gfx-tile > input:focus-visible + span, .gfx-shape > input:focus-visible + span, .gfx-opt > input:focus-visible + span, button.gfx-day:focus-visible {
                outline: 2px solid var(--brand-blue);
                outline-offset: 2px;
            }
            .gfx-tile > input:checked + span, .gfx-shape > input:checked + span {
                border-color: var(--brand-blue);
                background: var(--brand-blue-a10);
                box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.08);
                color: rgb(var(--ap-ink));
            }
            .gfx-tile svg { width: 3.25rem; height: 2.25rem; }
            .gfx-tile svg rect { fill: rgb(var(--ap-ink-4)); }
            .gfx-tile > input:checked + span svg rect { fill: var(--brand-blue); }
            .gfx-tile svg rect.is-soft { opacity: 0.45; }

            /* The shapes: an outline of each, so a 9:16 looks like one before it is chosen. */
            .gfx-shapes { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 0.375rem; }
            .gfx-shape > span {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: flex-end;
                gap: 0.375rem;
                height: 4.25rem;
                padding: 0.5rem 0.125rem 0.4375rem;
                border: 1px solid rgb(var(--ap-border-strong));
                border-radius: 0.625rem;
                background: rgb(var(--ap-surface));
                overflow: hidden;
                font-size: 0.75rem;
                font-weight: 600;
                color: rgb(var(--ap-ink-2));
                transition: all 0.2s;
            }
            .gfx-shape i {
                display: block;
                flex: none;
                border: 1.5px solid rgb(var(--ap-ink-3));
                border-radius: 2px;
            }
            .gfx-shape > input:checked + span i { border-color: var(--brand-blue); background: var(--brand-blue-a20); }
            .gfx-shape i.is-auto { width: 1.25rem; height: 1.125rem; border-style: dashed; }
            .gfx-shape i.is-square { width: 1.25rem; height: 1.25rem; }
            .gfx-shape i.is-portrait { width: 1.1rem; height: 1.375rem; }
            .gfx-shape i.is-story { width: 0.875rem; height: 1.5625rem; }
            .gfx-shape i.is-landscape { width: 1.625rem; height: 0.875rem; }

            /* A choice of two or three in a row (date strip, how often), and the week's days, which
               are several at once and so are pressed buttons, not radios. */
            .gfx-opts { display: flex; flex-wrap: wrap; gap: 0.375rem; }
            .gfx-opt > span, button.gfx-day {
                display: block;
                min-width: 0;
                border: 1px solid rgb(var(--ap-border-strong));
                border-radius: 9999px;
                padding: 0.3125rem 0.75rem;
                background: rgb(var(--ap-surface));
                font-size: 0.8125rem;
                font-weight: 500;
                color: rgb(var(--ap-ink-2));
                transition: all 0.2s;
            }
            /* Seven in a line where the language shortens its days (Mon, Mo, Lun), and as many lines
               as it takes where it does not: Russian, Estonian and Arabic write them in full here,
               and seven equal columns cut every one of them off. */
            button.gfx-day { flex: 1 0 auto; min-width: 2.25rem; padding-inline: 0.375rem; text-align: center; }
            .gfx-days { display: flex; flex-wrap: wrap; gap: 0.25rem; }
            button.gfx-day:hover { border-color: var(--brand-blue); }
            .gfx-opt > input:checked + span, button.gfx-day[aria-pressed="true"] {
                border-color: var(--brand-blue);
                background: var(--brand-blue-a10);
                box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.08);
                font-weight: 600;
                color: rgb(var(--ap-ink));
            }

            .gfx-controls input.peer:focus-visible + div { outline: 2px solid var(--brand-blue); outline-offset: 2px; }
            .gfx-switch:has(input:disabled) { opacity: 0.5; }
            .gfx-switch:has(input:disabled) label { cursor: not-allowed; }
            /* A switch with its name, and a line under it. */
            .gfx-switch + .gfx-hint { margin-inline-start: 3.5rem; margin-top: 0.125rem; }
            .gfx-inline { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; }
            .gfx-inline .gfx-label { margin: 0; }
            .gfx-inline select { width: auto; min-width: 7.5rem; }

            /* The rows. The kit's rows put the summary beside the name; this column is a phone's
               width on any screen, so the summary goes under the name, in full, as on a phone. */
            .gfx-rows { padding: 0 1.25rem; }
            .gfx-rows .event-subrows { margin-top: 0; border-top: 0; }
            .gfx-rows button.event-subrow { flex-wrap: wrap; row-gap: 0; min-height: 3.5rem; padding: 0.625rem 0; }
            .gfx-rows .event-subrow .event-row-title { flex: 1; min-width: 0; }
            .gfx-rows .event-subrow .event-row-summary { order: 3; flex: none; width: 100%; white-space: normal; font-size: 0.8125rem; }
            .gfx-rows .event-subrow:last-of-type:not([aria-expanded="true"]) { border-bottom-color: transparent; }
            .gfx-rows .event-subrow { scroll-margin-top: 5.5rem; }
            .gfx-rows .event-subrow-body { padding: 0.5rem 0 1.25rem; }
            /* The last row clears the save bar when it is the one open. */
            .gfx.has-bar .gfx-controls { padding-bottom: 1rem; }
            .gfx-rows .event-subrow-body:last-child { border-bottom-color: transparent; }

            /* Variables to press into a field. */
            .gfx-tokens { display: flex; flex-wrap: wrap; gap: 0.25rem; margin-top: 0.5rem; }
            button.gfx-token {
                min-width: 0;
                border: 1px solid rgb(var(--ap-border));
                border-radius: 0.375rem;
                padding: 0.0625rem 0.4375rem;
                background: var(--ap-tint-1);
                font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
                font-size: 0.75rem;
                line-height: 1.25rem;
                color: rgb(var(--ap-ink-2));
            }
            button.gfx-token:hover { border-color: var(--brand-blue); color: var(--brand-blue); }

            /* The header image. */
            .gfx-banner { display: flex; align-items: center; gap: 0.75rem; }
            .gfx-banner img { max-width: 9rem; max-height: 3.5rem; border: 1px solid rgb(var(--ap-border)); border-radius: 0.375rem; }
            .gfx-banner-acts { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; }

            /* A part the plan does not include. */
            .gfx-locked {
                border: 1px dashed rgb(var(--ap-border-strong));
                border-radius: 0.625rem;
                padding: 0.875rem;
            }
            .gfx-locked p { margin: 0 0 0.75rem; font-size: 0.875rem; color: rgb(var(--ap-ink-2)); }
            .gfx-sub { display: flex; align-items: center; gap: 0.5rem; margin: 1.25rem 0 0.5rem; padding-top: 1.125rem; border-top: 1px solid rgb(var(--ap-border)); }
            .gfx-sub .gfx-label { margin: 0; }

            /* The post: the image, what to do with it, and its caption. */
            .gfx-stage { display: flex; flex-direction: column; overflow: hidden; }
            .gfx-post { display: flex; flex-direction: column; flex: 1; min-height: 0; }
            .gfx-pane { display: flex; flex-direction: column; min-width: 0; min-height: 0; }
            .gfx-pane.is-image { flex: 3 1 0; }
            .gfx-pane.is-caption { flex: 2 1 0; border-top: 1px solid rgb(var(--ap-border)); }
            .gfx-pane-head {
                display: flex;
                flex: none;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
                min-height: 3.25rem;
                padding: 0.5rem 1rem;
            }
            .gfx-pane-name { display: flex; align-items: baseline; gap: 0.625rem; min-width: 0; }
            .gfx-pane-name h2 { margin: 0; font-size: 0.9375rem; font-weight: 600; color: rgb(var(--ap-ink)); }
            .gfx-pane-name span { font-size: 0.8125rem; color: rgb(var(--ap-ink-3)); white-space: nowrap; }
            .gfx-pane-tools { display: flex; flex: none; align-items: center; gap: 0.375rem; }
            button.gfx-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 2.25rem;
                height: 2.25rem;
                border-radius: 0.5rem;
                color: rgb(var(--ap-ink-3));
                transition: all 0.2s;
            }
            button.gfx-icon:hover { background: var(--ap-tint-2); color: rgb(var(--ap-ink)); }
            button.gfx-icon:focus-visible { outline: 2px solid var(--brand-blue); outline-offset: 1px; }
            button.gfx-icon[disabled] { opacity: 0.4; cursor: not-allowed; }
            .gfx-icon svg { width: 1.125rem; height: 1.125rem; }
            .gfx-canvas {
                position: relative;
                display: flex;
                flex: 1;
                align-items: center;
                justify-content: center;
                min-height: 14rem;
                margin: 0 1rem;
                padding: 1rem;
                border-radius: 0.75rem;
                background: rgb(var(--ap-surface-sunken));
                box-shadow: var(--ap-inset-track);
                overflow: hidden;
            }
            /* The button is the whole ground (press anywhere to see it full size); the picture
               inside keeps its own shape, so its corners and shadow are the picture's and no
               paler box stands round it. */
            .gfx-canvas > button.gfx-shot {
                position: absolute;
                inset: 1rem;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: zoom-in;
                border-radius: 0.5rem;
            }
            .gfx-canvas > button.gfx-shot:focus-visible { outline: 2px solid var(--brand-blue); outline-offset: 3px; }
            .gfx-canvas img {
                display: block;
                width: auto;
                height: auto;
                max-width: 100%;
                max-height: 100%;
                border-radius: 0.375rem;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.12), 0 8px 24px -6px rgba(0, 0, 0, 0.28);
                transition: opacity 0.2s;
            }
            .gfx-canvas.is-busy img { opacity: 0.45; }
            /* A single line of flyers is many times wider than tall: shown at a height that
               can be read, and scrolled sideways, where fitting it made a 36px ribbon. */
            .gfx-scroll { position: absolute; inset: 1rem; border-radius: 0.5rem; scrollbar-width: thin; }
            .gfx-scroll:focus-visible { outline: 2px solid var(--brand-blue); outline-offset: 3px; }
            .gfx-scroll.is-wide {
                display: flex;
                align-items: center;
                overflow-x: scroll;
                overflow-y: hidden;
                -webkit-mask-image: linear-gradient(to right, #000 90%, transparent);
                mask-image: linear-gradient(to right, #000 90%, transparent);
            }
            .gfx-scroll.is-wide[dir="rtl"] {
                -webkit-mask-image: linear-gradient(to left, #000 90%, transparent);
                mask-image: linear-gradient(to left, #000 90%, transparent);
            }
            .gfx-scroll.is-wide img { max-width: none; height: min(15rem, calc(100% - 1rem)); }
            .gfx-scroll.is-tall { overflow-x: hidden; overflow-y: scroll; text-align: center; }
            .gfx-scroll.is-tall img { display: inline-block; max-height: none; width: min(24rem, 100%); }
            .gfx-long-note { flex: none; margin: 0.5rem 1rem 0; font-size: 0.8125rem; color: rgb(var(--ap-ink-3)); }
            /* The List sketch draws its flyers on the side the schedule's image puts them. */
            .gfx-tiles.is-rtl .gfx-tile:last-child svg { transform: scaleX(-1); }
            .gfx-hint.is-plain, .gfx .event-save-status small { color: rgb(var(--ap-ink-2)); }
            button.gfx-cap-top { display: inline-flex; }
            .gfx-acts.gfx-cap-foot { display: none; }
            /* A new picture is on its way: the old one stays, dimmed, under a line that moves. */
            .gfx-progress {
                position: absolute;
                inset-inline: 0;
                top: 0;
                height: 3px;
                overflow: hidden;
                background: var(--brand-blue-a20);
            }
            .gfx-progress::before {
                content: "";
                position: absolute;
                top: 0;
                bottom: 0;
                width: 35%;
                background: var(--brand-blue);
                animation: gfx-slide 1.1s ease-in-out infinite;
            }
            @keyframes gfx-slide { from { inset-inline-start: -35%; } to { inset-inline-start: 100%; } }
            .gfx-busy-chip {
                position: absolute;
                top: 0.75rem;
                inset-inline-end: 0.75rem;
                border-radius: 9999px;
                padding: 0.1875rem 0.625rem;
                background: rgb(var(--ap-surface));
                box-shadow: var(--ap-shadow-dropdown);
                font-size: 0.75rem;
                font-weight: 600;
                color: rgb(var(--ap-ink-2));
            }
            /* Before the first picture: a sheet the shape of the one that is coming. */
            .gfx-ghost {
                width: min(100%, 22rem);
                max-height: 100%;
                border-radius: 0.5rem;
                background: linear-gradient(100deg, var(--ap-tint-2) 30%, var(--ap-tint-1) 50%, var(--ap-tint-2) 70%);
                background-size: 300% 100%;
                animation: gfx-shimmer 1.4s linear infinite;
            }
            @keyframes gfx-shimmer { from { background-position: 100% 0; } to { background-position: -50% 0; } }
            @media (prefers-reduced-motion: reduce) {
                .gfx-progress::before, .gfx-ghost, .gfx-ai .is-spin { animation: none; }
                .gfx-progress::before { width: 100%; opacity: 0.5; }
            }
            .gfx-acts {
                display: flex;
                flex: none;
                flex-wrap: wrap;
                align-items: center;
                justify-content: flex-end;
                gap: 0.5rem;
                padding: 0.75rem 1rem;
            }
            .gfx-acts .gfx-note { margin-inline-end: auto; font-size: 0.8125rem; color: rgb(var(--ap-ink-3)); }
            .gfx-acts .gfx-note.is-bad { color: #b91c1c; font-weight: 500; }
            .dark .gfx-acts .gfx-note.is-bad { color: #fca5a5; }
            /* The buttons are the portal's own (x-brand-button for the one that goes on, the
               secondary button's classes for the rest). A button that cannot act yet is dimmed and
               says so (aria-disabled), but stays in the Tab order. */
            .gfx-acts svg, .gfx-pane-tools .page-tool svg, .gfx-pane-tools .gfx-when-strip svg { flex: none; width: 1.125rem; height: 1.125rem; }
            .gfx .is-off { opacity: 0.5; cursor: not-allowed; }
            .gfx .is-off:hover { transform: none; box-shadow: none; }
            textarea.gfx-text, div.gfx-text {
                display: block;
                flex: 1;
                min-height: 9rem;
                margin: 0 1rem 1rem;
                border: 1px solid rgb(var(--ap-border));
                border-radius: 0.625rem;
                padding: 0.75rem 0.875rem !important;
                background: rgb(var(--ap-surface));
                box-shadow: none;
                overflow-y: auto;
                resize: none;
                white-space: pre-wrap;
                overflow-wrap: anywhere;
                font-size: 0.875rem !important;
                line-height: 1.4375rem !important;
                color: rgb(var(--ap-ink));
            }
            .gfx-cap-note { margin: -0.5rem 1rem 0.75rem; font-size: 0.8125rem; color: rgb(var(--ap-ink-3)); }
            .gfx-cap-note.is-bad { color: #b91c1c; font-weight: 500; }
            .dark .gfx-cap-note.is-bad { color: #fca5a5; }
            .gfx-text:focus-visible { outline: 2px solid var(--brand-blue); outline-offset: 1px; }
            .gfx-ai {
                display: flex;
                flex: none;
                flex-wrap: wrap;
                align-items: center;
                gap: 0.375rem 0.625rem;
                margin: -0.25rem 1rem 0.75rem;
                font-size: 0.8125rem;
                color: rgb(var(--ap-ink-2));
            }
            .gfx-ai svg { width: 1rem; height: 1rem; color: var(--brand-blue); }
            .gfx-ai .is-spin { animation: gfx-turn 1s linear infinite; }
            @keyframes gfx-turn { to { transform: rotate(360deg); } }
            .gfx-ai button { font-weight: 600; color: var(--brand-blue); }
            .gfx-ai button:hover { text-decoration: underline; }
            .gfx-state { display: flex; flex: 1; flex-direction: column; align-items: center; justify-content: center; padding: 1rem; }
            .gfx-state .page-empty { margin: 0; }

            button.gfx-fold, button.gfx-when-strip, .gfx-thumb { display: none; }
            .gfx-cap-line { display: none; }
            .gfx-thumb { flex: none; align-self: center; width: 2.25rem; height: 2.25rem; border-radius: 0.375rem; object-fit: cover; }
            /* A wide window has room for the caption beside the image. */
            @media (min-width: 1536px) {
                .gfx-post { flex-direction: row; }
                .gfx-pane.is-image { flex: 1 1 0; }
                .gfx-pane.is-caption { flex: 0 0 20.5rem; border-top: 0; border-inline-start: 1px solid rgb(var(--ap-border)); }
                button.gfx-cap-top { display: none; }
                .gfx-acts.gfx-cap-foot { display: flex; }
                .gfx-pane.is-caption textarea.gfx-text { margin-bottom: 0; }
            }
            /* A smaller laptop has room for one of them at a time under the other's name: the
               caption folds to its first line, with Copy text still beside it, and opens when it
               is pressed or when the Caption settings are. */
            @media (min-width: 1280px) and (max-width: 1535.98px) {
                button.gfx-fold { display: inline-flex; }
                .gfx-pane.is-image { flex: 1 1 0; }
                .gfx-pane.is-caption { flex: none; }
                .gfx-post.is-folded .gfx-text { display: none; }
                .gfx-post.is-folded .gfx-cap-line {
                    display: block;
                    flex: 1;
                    min-width: 0;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                    font-size: 0.8125rem;
                    color: rgb(var(--ap-ink-3));
                }
                /* The caption open: the image steps back to a line of its own (a small copy, its
                   size, Download), where half a card each left it a stamp over its three buttons. */
                .gfx-post:not(.is-folded) .gfx-pane.is-image { flex: none; }
                .gfx-post:not(.is-folded) .gfx-pane.is-image > .gfx-canvas,
                .gfx-post:not(.is-folded) .gfx-pane.is-image > .gfx-acts,
                .gfx-post:not(.is-folded) .gfx-pane.is-image > .gfx-state,
                .gfx-post:not(.is-folded) .gfx-when-open { display: none; }
                .gfx-post:not(.is-folded) button.gfx-when-strip { display: inline-flex; }
                .gfx-post:not(.is-folded) .gfx-thumb { display: block; }
                .gfx-post:not(.is-folded) .gfx-pane.is-caption { flex: 1 1 0; }
                .gfx-post:not(.is-folded) .gfx-text { min-height: 5.5rem; }
                .gfx-pane.is-caption .gfx-pane-name { flex: 1; cursor: pointer; }
            }
            /* One column: the post is as tall as what it holds, and the caption opens on request. */
            @media (max-width: 1279.98px) {
                .gfx-post, .gfx-pane.is-image, .gfx-pane.is-caption, .gfx-canvas { flex: none; }
                .gfx-canvas { min-height: 12rem; }
                .gfx-canvas > button.gfx-shot { position: static; inset: auto; max-width: 100%; }
                /* A Story is taller than a phone: held to a little over half of it, so the
                   buttons under the picture are on the first screen. */
                .gfx-canvas img { max-height: 50vh; max-height: 44svh; }
                .gfx-scroll { position: static; inset: auto; max-width: 100%; }
                .gfx-scroll.is-wide img { height: 9rem; }
                .gfx-scroll.is-tall { max-height: 60vh; }
                /* The corner copy stands over the foot of the settings: room for the last row under it. */
                .gfx-controls { padding-bottom: 5.5rem; }
                /* Eight whole lines, never half of one. */
                textarea.gfx-text { flex: none; height: calc(8 * 1.4375rem + 1.5rem + 2px); }
                .gfx-ghost { max-height: 20rem; }
            }
            @media (max-width: 639.98px) {
                .gfx-card { padding: 1rem; }
                .gfx-rows { padding: 0 1rem; }
                /* The one to press takes the row; the other two share the next. */
                .gfx-acts { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
                .gfx-acts .gfx-main { grid-column: 1 / -1; order: -1 !important; }
                .gfx-acts .gfx-note { grid-column: 1 / -1; order: -2; }
                .gfx-acts .gfx-note:empty { display: none; }
                /* Half a phone's width each: the room goes to the words ("Bild kopieren",
                   "Copier l'image"), which otherwise break in two beside a one-line neighbour. */
                .gfx-acts > :not(.gfx-main):not(.gfx-note) { padding-inline: 0.5rem; }
                /* On a phone the bar takes the foot of the screen while something is unsaved:
                   the picture gives way, so the three buttons stay above it. */
                .gfx-canvas img { max-height: 44vh; max-height: 38svh; }
            }
            .gfx-more { display: none; margin: -0.5rem 1rem 0.75rem; font-size: 0.8125rem; font-weight: 600; color: var(--brand-blue); text-align: start; }
            @media (max-width: 1279.98px) { .gfx-more { display: block; } }


            /* On one column the image scrolls away while a setting is changed: a small copy of it
               stays in the corner, and leads back up. */
            button.gfx-peek {
                position: fixed;
                inset-inline-start: 1rem;
                bottom: 1rem;
                z-index: 35;
                display: none;
                width: 4.5rem;
                height: 4.5rem;
                border: 2px solid rgb(var(--ap-surface));
                border-radius: 0.75rem;
                background: rgb(var(--ap-surface-sunken)) center / contain no-repeat;
                box-shadow: var(--ap-shadow-dropdown);
                overflow: hidden;
            }
            .gfx.has-bar button.gfx-peek { bottom: 8.75rem; }
            /* A phone's head has room for the size or for the words of Generate again, not both:
               the button keeps its sign (and its name, for a screen reader). */
            @media (max-width: 639.98px) {
                .gfx-pane-tools .page-tool.gfx-when-open { gap: 0; padding: 0.5rem; font-size: 0; }
                .gfx .event-save-status small { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            }
            @media (max-width: 1279.98px) { button.gfx-peek.is-on { display: block; } }
            button.gfx-peek.is-busy::after {
                content: "";
                position: absolute;
                inset: 0;
                background: rgba(255, 255, 255, 0.55);
            }

            /* The save bar is the kit's; here it is only on the page while there is something to save. */
            .gfx .gfx-bar { order: 3; grid-column: 1 / -1; }
            @media (min-width: 1024px) { .gfx .gfx-bar { margin-top: 0; } }
            @media (max-width: 1023.98px) { .gfx.has-bar { padding-bottom: 7.5rem; } }
            .gfx .event-save-status small { display: block; font-size: 0.8125rem; color: rgb(var(--ap-ink-3)); }
            @media (max-width: 1023.98px) {
                .gfx .event-save-status { text-align: center; }
                .gfx .gfx-bar .event-save-actions .event-bar-save { flex: 1; }
            }
        </style>
    </x-slot>

    @php
        $hosted = (bool) config('app.hosted');
        $canEnglish = $role->canForceEnglish();
        $settings = $pageSettings;
        $defaultTemplate = \App\Utils\EventTextGenerator::getDefaultTemplate();
        // The hour as the schedule writes its own times (the caption says 9:00 PM or 21:00 by the same setting).
        $hours = [];
        for ($i = 0; $i < 24; $i++) {
            $hours[$i] = $role->use_24_hour_time ? sprintf('%02d:00', $i) : date('g:i A', mktime($i, 0, 0, 1, 1, 2026));
        }
        // The direction the schedule's own text reads in: its image, its caption and the fields
        // that hold its words follow it, whatever language the person's interface is in.
        $contentDir = content_dir($role);
        $iconCopy = 'M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75';
        $iconDone = 'M4.5 12.75l6 6 9-13.5';
        $iconShare = 'M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z';
        $iconDownload = 'M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3';
        $secondaryButton = 'ap-secondary-btn inline-flex items-center justify-center gap-2 px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800';
        $subdomain = ['subdomain' => $role->subdomain];
        $days = [__('messages.sun'), __('messages.mon'), __('messages.tue'), __('messages.wed'), __('messages.thu'), __('messages.fri'), __('messages.sat')];
        $boot = [
            'settings' => $settings,
            'defaultTemplate' => $defaultTemplate,
            'headerUrl' => $headerImagePreviewUrl,
            'canEdit' => $canEdit,
            'enterprise' => (bool) $isEnterprise,
            'aiReady' => (bool) $aiReady,
            'hosted' => $hosted,
            'userEmail' => auth()->user()->email,
            'file' => $role->subdomain.'-upcoming-events.png',
            'dir' => content_dir($role),
            'days' => $days,
            'hours' => $hours,
            'urls' => [
                'data' => route('event.generate_graphic_data', $subdomain),
                'save' => route('event.save_graphic_settings', $subdomain),
                'ai' => route('event.graphic_ai_text', $subdomain),
                'test' => route('event.graphic_test_email', $subdomain),
                'header' => route('event.graphic_upload_header_image', $subdomain),
            ],
            // What a layout draws is said under Layout, and where a shape is used under Shape:
            // "best for Instagram Stories" under List sent people to List for a Story.
            'px' => ['square' => '1080 × 1080 px.', 'portrait' => '1080 × 1350 px.', 'story' => '1080 × 1920 px.', 'landscape' => '1200 × 630 px.'],
            'sizes' => [
                'square' => __('messages.graphic_shape_square_help'),
                'portrait' => __('messages.graphic_shape_portrait_help'),
                'story' => __('messages.graphic_shape_story_help'),
                'landscape' => __('messages.graphic_shape_landscape_help'),
            ],
            'tips' => ['grid' => __('messages.graphic_tip_grid'), 'row' => __('messages.graphic_tip_row'), 'list' => __('messages.graphic_tip_list')],
            'groups' => [
                'look' => __('messages.graphic_group_look'),
                'events' => __('messages.events'),
                'flyers' => __('messages.graphic_row_flyers'),
                'header' => __('messages.graphic_row_header'),
                'caption' => __('messages.graphic_row_caption'),
                'email' => __('messages.graphic_row_email'),
            ],
            'words' => [
                'shape_auto' => __('messages.graphic_shape_auto_help'),
                'shape_fitted' => __('messages.graphic_shape_fitted'),
                'per_row' => __('messages.graphic_per_row_help'),
                'per_row_rows' => __('messages.graphic_per_row_rows_auto'),
                'per_row_list' => __('messages.graphic_per_row_list'),
                'locked_email' => __('messages.graphic_sum_email_locked'),
                'next' => __('messages.graphic_sum_next'),
                'each' => __('messages.graphic_sum_per_schedule'),
                'no_recurring' => __('messages.graphic_sum_no_recurring'),
                'flyers_only' => __('messages.graphic_none'),
                'date_overlay' => __('messages.graphic_sum_date_overlay'),
                'date_above' => __('messages.graphic_sum_date_above'),
                'numbered' => __('messages.graphic_sum_numbered'),
                'not_used' => __('messages.graphic_sum_not_used'),
                'none' => __('messages.graphic_none'),
                'banner' => __('messages.graphic_sum_banner'),
                'headline' => __('messages.graphic_sum_headline'),
                'signoff' => __('messages.graphic_sum_signoff'),
                'default' => __('messages.graphic_sum_default'),
                'own' => __('messages.graphic_sum_own'),
                'all_events' => __('messages.graphic_sum_all_events'),
                'english' => __('messages.graphic_sum_english'),
                'ai' => __('messages.graphic_sum_ai'),
                'off' => __('messages.graphic_sum_off'),
                'daily' => __('messages.graphic_sum_daily'),
                'weekly' => __('messages.graphic_sum_weekly'),
                'monthly' => __('messages.graphic_sum_monthly'),
                'error' => __('messages.error_loading_graphic'),
                'failed' => __('messages.error'),
                'recipients' => __('messages.recipient_emails_required'),
                'days' => __('messages.send_days_required'),
                'copy_failed' => __('messages.graphic_copy_failed'),
                'share_failed' => __('messages.share_failed'),
                'test_sent' => __('messages.test_email_sent'),
                'ai_failed' => __('messages.graphic_ai_failed'),
                'per_row_count' => __('messages.graphic_per_row_count'),
                'paused' => __('messages.graphic_sum_email_paused'),
                'no_day' => __('messages.graphic_sum_no_day'),
                'say_updating' => __('messages.graphic_say_updating'),
                'say_updated' => __('messages.graphic_say_updated'),
                'save_failed' => __('messages.graphic_save_failed'),
                'test_failed' => __('messages.graphic_test_failed'),
                'session' => __('messages.graphic_session'),
                'copy_caption_failed' => __('messages.graphic_copy_caption_failed'),
                'alt' => __('messages.events_graphic'),
            ],
        ];
        $tokens = ['{schedule_name}', '{month_name}', '{year}', '{first_event_date}', '{last_event_date}'];
        $captionTokens = ['{event_name}', '{day_name}', '{date_dmy}', '{time}', '{venue}', '{city}', '{price}', '{url}'];
        $stripTokens = ['{date_dmy}', '{time}', '{event_name}', '{venue}'];
        $scheduleUrl = route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']);
        $variablesUrl = marketing_url('/docs/event-graphics#variables');
    @endphp

    <div class="page-shell">
    {{-- Reached from the schedule's own page, so the way back is the schedule, by name. --}}
    <x-page-header :title="__('messages.events_graphic')" :lead="__('messages.graphic_lead')" :image="$role->profile_image_url ?: null"
        :back="$scheduleUrl" :back-label="$role->name" />

    <div class="page-stack">
    @if (! empty($timezoneMismatchCount))
    <x-page-notice tone="warn">
        <span v-pre>{{ str_replace([':count', ':timezone'], [$timezoneMismatchCount, $role->timezone], __('messages.timezone_mismatch_graphic_warning')) }}</span>
        <x-slot name="action"><a href="{{ $scheduleUrl }}" class="event-link">{{ __('messages.review') }}</a></x-slot>
    </x-page-notice>
    @endif

    @unless ($canEdit)
    <x-page-notice tone="info">{{ __('messages.graphic_viewer') }}</x-page-notice>
    @endunless

    {{-- The mount's own element is not part of its template, so the page's root is one level in. --}}
    <div id="graphic-app" v-cloak>
    <div class="gfx" :class="{ 'has-bar': barOn }">

        {{-- The settings. One form at every width. --}}
        <form class="gfx-controls" id="gfx-form" v-on:submit.prevent novalidate>
            <div class="gfx-card ap-card rounded-xl">
                <div class="gfx-field">
                    <span class="gfx-label" id="gfx-layout-label">{{ __('messages.layout') }}</span>
                    <div class="gfx-tiles{{ $contentDir === 'rtl' ? ' is-rtl' : '' }}" role="radiogroup" aria-labelledby="gfx-layout-label" aria-describedby="gfx-layout-tip">
                        <label class="gfx-tile">
                            <input type="radio" class="sr-only" name="layout" value="grid" v-model="s.layout">
                            <span>
                                <svg viewBox="0 0 52 36" aria-hidden="true"><rect x="1" y="1" width="15" height="16" rx="2"/><rect x="18.5" y="1" width="15" height="16" rx="2"/><rect x="36" y="1" width="15" height="16" rx="2"/><rect x="1" y="19" width="15" height="16" rx="2"/><rect x="18.5" y="19" width="15" height="16" rx="2"/><rect x="36" y="19" width="15" height="16" rx="2"/></svg>
                                {{ __('messages.grid_layout') }}
                            </span>
                        </label>
                        <label class="gfx-tile">
                            <input type="radio" class="sr-only" name="layout" value="row" v-model="s.layout">
                            <span>
                                <svg viewBox="0 0 52 36" aria-hidden="true"><rect x="1" y="1" width="20" height="16" rx="2"/><rect x="23" y="1" width="11" height="16" rx="2"/><rect x="36" y="1" width="15" height="16" rx="2"/><rect x="1" y="19" width="12" height="16" rx="2"/><rect x="15" y="19" width="22" height="16" rx="2"/><rect x="39" y="19" width="12" height="16" rx="2"/></svg>
                                {{ __('messages.row_layout') }}
                            </span>
                        </label>
                        <label class="gfx-tile">
                            <input type="radio" class="sr-only" name="layout" value="list" v-model="s.layout">
                            <span>
                                <svg viewBox="0 0 52 36" aria-hidden="true"><rect x="1" y="1" width="13" height="10" rx="2"/><rect class="is-soft" x="17" y="3" width="34" height="6" rx="2"/><rect x="1" y="13" width="13" height="10" rx="2"/><rect class="is-soft" x="17" y="15" width="34" height="6" rx="2"/><rect x="1" y="25" width="13" height="10" rx="2"/><rect class="is-soft" x="17" y="27" width="34" height="6" rx="2"/></svg>
                                {{ __('messages.list_layout') }}
                            </span>
                        </label>
                    </div>
                    <p class="gfx-hint" id="gfx-layout-tip" v-text="tip"></p>
                </div>

                <div class="gfx-field">
                    <div class="gfx-inline">
                        <label class="gfx-label" for="max_per_row" :style="{ opacity: s.layout === 'list' ? 0.5 : 1 }">{{ __('messages.graphic_per_row') }}</label>
                        <select id="max_per_row" v-model="s.max_per_row" :disabled="s.layout === 'list'">
                            <option value="">{{ __('messages.no_limit') }}</option>
                            @for ($i = 1; $i <= 10; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <p class="gfx-hint" id="gfx-per-row-tip" v-text="perRowLine"></p>
                </div>

                <div class="gfx-field">
                    <span class="gfx-label" id="gfx-shape-label">{{ __('messages.graphic_shape') }}</span>
                    <div class="gfx-shapes" role="radiogroup" aria-labelledby="gfx-shape-label" aria-describedby="gfx-shape-tip">
                        @foreach (['auto', 'square', 'portrait', 'story', 'landscape'] as $shape)
                        <label class="gfx-shape">
                            <input type="radio" class="sr-only" name="image_size" value="{{ $shape }}" v-model="s.image_size">
                            <span><i class="is-{{ $shape }}" aria-hidden="true"></i>{{ __('messages.graphic_shape_'.$shape) }}</span>
                        </label>
                        @endforeach
                    </div>
                    {{-- The size is left to right in every language ("1080 × 1350 px", never "px 1350 × 1080"). --}}
                    <p class="gfx-hint" id="gfx-shape-tip"><bdi dir="ltr" v-if="shapeSize" v-text="shapeSize"></bdi> <span v-text="shapeLine"></span></p>
                </div>
            </div>

            <div class="gfx-card gfx-rows ap-card rounded-xl">
                <div class="event-subrows">
                    {{-- Which events. --}}
                    <button type="button" class="event-subrow" :aria-expanded="open === 'events' ? 'true' : 'false'" data-row-group="graphic" data-tab="events" aria-controls="graphic-pane-events" @click="toggle('events')">
                        <span class="event-row-title">{{ __('messages.events') }}</span>
                        <span class="event-row-summary"><bdi v-text="sum.events"></bdi></span>
                        <svg class="event-row-chevron" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div id="graphic-pane-events" class="event-subrow-body" v-show="open === 'events'">
                        <div class="gfx-field">
                            <div class="gfx-inline">
                                <label class="gfx-label" for="event_count">{{ __('messages.graphic_event_count') }}</label>
                                <select id="event_count" v-model="s.event_count">
                                    <option value="">{{ __('messages.graphic_events_all') }}</option>
                                    @for ($i = 1; $i <= 20; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="gfx-field">
                            <div class="gfx-inline">
                                <label class="gfx-label" for="max_per_schedule">{{ __('messages.graphic_per_schedule') }}</label>
                                <select id="max_per_schedule" v-model="s.max_per_schedule">
                                    <option value="">{{ __('messages.graphic_no_limit') }}</option>
                                    @for ($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <p class="gfx-hint">{{ __('messages.graphic_per_schedule_help') }}</p>
                        </div>
                        @if ($hasRecurringEvents)
                        <div class="gfx-field gfx-switch">
                            <x-toggle name="exclude_recurring" :label="__('messages.exclude_recurring_events')" v-model="s.exclude_recurring" />
                        </div>
                        @endif
                    </div>

                    {{-- What is drawn on each flyer: nothing of it applies to the List layout. --}}
                    <button type="button" class="event-subrow" :aria-expanded="open === 'flyers' ? 'true' : 'false'" data-row-group="graphic" data-tab="flyers" aria-controls="graphic-pane-flyers" @click="toggle('flyers')">
                        <span class="event-row-title">{{ __('messages.graphic_row_flyers') }}</span>
                        <span class="event-row-summary"><bdi v-text="sum.flyers"></bdi></span>
                        <svg class="event-row-chevron" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div id="graphic-pane-flyers" class="event-subrow-body" v-show="open === 'flyers'">
                        <p class="gfx-hint" v-if="s.layout === 'list'" style="margin: 0 0 1rem">{{ __('messages.graphic_flyers_list') }}</p>
                        <div class="gfx-field" v-if="s.layout !== 'list'">
                            <span class="gfx-label" id="gfx-date-label">{{ __('messages.graphic_date_strip') }}</span>
                            <div class="gfx-opts" role="radiogroup" aria-labelledby="gfx-date-label">
                                <label class="gfx-opt"><input type="radio" class="sr-only" name="date_position" value="" v-model="s.date_position"><span>{{ __('messages.graphic_none') }}</span></label>
                                <label class="gfx-opt"><input type="radio" class="sr-only" name="date_position" value="overlay" v-model="s.date_position"><span>{{ __('messages.graphic_date_overlay') }}</span></label>
                                <label class="gfx-opt"><input type="radio" class="sr-only" name="date_position" value="above" v-model="s.date_position"><span>{{ __('messages.graphic_date_above') }}</span></label>
                            </div>
                        </div>
                        <div class="gfx-field" v-if="s.date_position && s.layout !== 'list'">
                            <label class="gfx-label" for="overlay_text">{{ __('messages.graphic_strip_text') }}</label>
                            <input type="text" id="overlay_text" v-model="s.overlay_text" maxlength="200" dir="{{ $contentDir }}" placeholder="{{ __('messages.graphic_strip_placeholder') }}">
                            <div class="gfx-tokens">
                                @foreach ($stripTokens as $token)
                                <button type="button" class="gfx-token" dir="ltr" aria-label="{{ __('messages.graphic_insert', ['token' => $token]) }}" @click="insert('overlay_text', '{{ $token }}')">{{ $token }}</button>
                                @endforeach
                            </div>
                            <p class="gfx-hint"><a href="{{ $variablesUrl }}" target="_blank" rel="noopener">{{ __('messages.graphic_all_variables') }}</a></p>
                        </div>
                        <div class="gfx-field gfx-switch">
                            <x-toggle name="number_events" :label="__('messages.graphic_number_flyers')" v-model="s.number_events" />
                        </div>
                        <p class="gfx-hint" v-text="s.layout === 'list' ? @js(__('messages.graphic_number_list_help')) : @js(__('messages.graphic_number_help'))"></p>
                    </div>

                    {{-- Above and below the events. --}}
                    <button type="button" class="event-subrow" :aria-expanded="open === 'header' ? 'true' : 'false'" data-row-group="graphic" data-tab="header" aria-controls="graphic-pane-header" @click="toggle('header')">
                        <span class="event-row-title">{{ __('messages.graphic_row_header') }}</span>
                        <span class="event-row-summary" :class="{ 'is-empty': ! sum.headerSet }"><bdi v-text="sum.header"></bdi></span>
                        <svg class="event-row-chevron" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div id="graphic-pane-header" class="event-subrow-body" v-show="open === 'header'">
                        <div class="gfx-field">
                            <span class="gfx-label">{{ __('messages.graphic_banner') }}</span>
                            <div class="gfx-banner">
                                <img v-if="header.url" :src="header.url" alt="">
                                @if ($canEdit)
                                {{-- An editor's, and left out of a viewer's page altogether (not only hidden by
                                     the script): the upload, like Save and the test, answers a viewer 403. --}}
                                <div class="gfx-banner-acts">
                                    <input type="file" ref="file" class="sr-only" tabindex="-1" aria-label="{{ __('messages.graphic_banner') }}" accept="image/jpeg,image/png,image/gif,image/webp" @change="upload">
                                    <template v-if="! header.asking">
                                        <button type="button" class="page-tool" :disabled="header.busy" @click="$refs.file.click()" v-text="header.busy ? @js(__('messages.uploading')) : (header.url ? @js(__('messages.graphic_replace')) : @js(__('messages.graphic_choose_image')))"></button>
                                        <button v-if="header.url" type="button" class="event-link is-danger" @click="header.asking = true">{{ __('messages.remove') }}</button>
                                    </template>
                                    <template v-else>
                                        <span class="gfx-hint" style="margin: 0">{{ __('messages.graphic_remove_ask') }}</span>
                                        <button type="button" class="event-link" @click="header.asking = false">{{ __('messages.cancel') }}</button>
                                        <button type="button" class="event-link is-danger" @click="removeHeader">{{ __('messages.remove') }}</button>
                                    </template>
                                </div>
                                @endif
                            </div>
                            <p class="gfx-error" v-if="header.error" role="alert" v-text="header.error"></p>
                            <p class="gfx-hint">{{ __('messages.graphic_header_image_help') }} <span v-if="saved.enabled && enterprise">{{ __('messages.graphic_header_image_email') }}</span></p>
                        </div>
                        <div class="gfx-field">
                            <label class="gfx-label" for="header_text">{{ __('messages.graphic_headline') }}</label>
                            <input type="text" id="header_text" v-model="s.header_text" maxlength="200" dir="{{ $contentDir }}" placeholder="{{ __('messages.graphic_header_text_placeholder') }}">
                            <div class="gfx-tokens">
                                @foreach ($tokens as $token)
                                <button type="button" class="gfx-token" dir="ltr" aria-label="{{ __('messages.graphic_insert', ['token' => $token]) }}" @click="insert('header_text', '{{ $token }}')">{{ $token }}</button>
                                @endforeach
                            </div>
                        </div>
                        <div class="gfx-field">
                            <label class="gfx-label" for="footer_text">{{ __('messages.graphic_signoff') }}</label>
                            <textarea id="footer_text" v-model="s.footer_text" rows="2" maxlength="300" dir="{{ $contentDir }}" placeholder="{{ __('messages.graphic_footer_text_placeholder') }}"></textarea>
                            <div class="gfx-tokens">
                                @foreach ($tokens as $token)
                                <button type="button" class="gfx-token" dir="ltr" aria-label="{{ __('messages.graphic_insert', ['token' => $token]) }}" @click="insert('footer_text', '{{ $token }}')">{{ $token }}</button>
                                @endforeach
                            </div>
                            <p class="gfx-hint">{{ __('messages.graphic_signoff_help') }}</p>
                        </div>
                    </div>

                    {{-- The caption. --}}
                    <button type="button" class="event-subrow" :aria-expanded="open === 'caption' ? 'true' : 'false'" data-row-group="graphic" data-tab="caption" aria-controls="graphic-pane-caption" @click="toggle('caption')">
                        <span class="event-row-title">{{ __('messages.graphic_row_caption') }}</span>
                        <span class="event-row-summary"><bdi v-text="sum.caption"></bdi></span>
                        <svg class="event-row-chevron" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div id="graphic-pane-caption" class="event-subrow-body" v-show="open === 'caption'">
                        <div class="gfx-field">
                            <label class="gfx-label" for="text_template">{{ __('messages.graphic_template') }}</label>
                            <textarea id="text_template" class="is-code" v-model="s.text_template" rows="6" maxlength="2000" dir="ltr" spellcheck="false"></textarea>
                            <div class="gfx-tokens">
                                @foreach ($captionTokens as $token)
                                <button type="button" class="gfx-token" dir="ltr" aria-label="{{ __('messages.graphic_insert', ['token' => $token]) }}" @click="insert('text_template', '{{ $token }}')">{{ $token }}</button>
                                @endforeach
                            </div>
                            <p class="gfx-hint">{{ __('messages.graphic_template_help') }}@if ($contentDir === 'rtl') {{ __('messages.graphic_template_rtl') }}@endif</p>
                            <p class="gfx-hint">
                                <a href="{{ $variablesUrl }}" target="_blank" rel="noopener">{{ __('messages.graphic_all_variables') }}</a>
                                <template v-if="s.text_template !== defaultTemplate"> &middot; <button type="button" @click="s.text_template = defaultTemplate">{{ __('messages.graphic_template_reset') }}</button></template>
                            </p>
                        </div>
                        <div class="gfx-field gfx-switch">
                            <x-toggle name="text_show_all" :label="__('messages.graphic_all_events')" v-model="s.text_show_all" v-bind:disabled="s.number_events" />
                        </div>
                        <p class="gfx-hint" v-text="s.number_events ? @js(__('messages.graphic_all_events_numbered')) : @js(__('messages.graphic_all_events_help'))"></p>
                        @if ($canEnglish)
                        <div class="gfx-field gfx-switch">
                            <x-toggle name="force_english" :label="__('messages.graphic_in_english')" v-model="s.force_english" />
                        </div>
                        <p class="gfx-hint">{{ __('messages.graphic_in_english_help') }}</p>
                        @endif
                        <div class="gfx-field">
                            <span class="gfx-label">{{ __('messages.graphic_links') }}</span>
                            <div class="gfx-switch"><x-toggle name="url_include_https" :label="__('messages.graphic_link_https')" v-model="s.url_include_https" /></div>
                            <div class="gfx-switch" style="margin-top: 0.625rem"><x-toggle name="url_include_id" :label="__('messages.graphic_link_id')" v-model="s.url_include_id" /></div>
                            <p class="gfx-hint">{{ __('messages.graphic_link_id_help') }}</p>
                        </div>

                        <div class="gfx-sub">
                            <label class="gfx-label" for="ai_prompt">{{ __('messages.graphic_ai') }}</label>
                            @unless ($isEnterprise)
                            <x-lock-badge tier="enterprise" />
                            @endunless
                        </div>
                        @if ($isEnterprise)
                        <textarea id="ai_prompt" v-model="s.ai_prompt" rows="3" maxlength="2000" dir="auto" placeholder="{{ __('messages.ai_prompt_placeholder') }}"></textarea>
                        <p class="gfx-hint">{{ $aiReady ? __('messages.graphic_ai_help') : __('messages.graphic_ai_no_key') }}</p>
                        @else
                        <div class="gfx-locked">
                            <p>{{ __('messages.graphic_ai_locked') }}</p>
                            <button type="button" class="page-tool" data-modal-open="upgrade-ai-prompt">{{ __('messages.upgrade_to_enterprise') }}</button>
                        </div>
                        @endif
                    </div>

                    {{-- The scheduled email. --}}
                    <button type="button" class="event-subrow" :aria-expanded="open === 'email' ? 'true' : 'false'" data-row-group="graphic" data-tab="email" aria-controls="graphic-pane-email" @click="toggle('email')">
                        <span class="event-row-title">{{ __('messages.graphic_row_email') }}</span>
                        <span class="event-row-summary" :class="{ 'is-empty': ! s.enabled }"><bdi v-text="sum.email"></bdi></span>
                        @unless ($isEnterprise)
                        <x-lock-badge tier="enterprise" class="event-row-lock" />
                        @else
                        <svg class="event-row-chevron" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                        @endunless
                    </button>
                    <div id="graphic-pane-email" class="event-subrow-body" v-show="open === 'email'">
                        @if ($isEnterprise)
                        <fieldset :disabled="! canEdit" style="min-width: 0">
                        <div class="gfx-field gfx-switch">
                            <x-toggle name="enabled" :label="__('messages.graphic_email_toggle')" v-model="s.enabled" v-bind:disabled="! canEdit" />
                        </div>
                        {{-- The only thing the page can say about a send that did not come: when one last did, and the one reason one is skipped. --}}
                        <p class="gfx-hint" v-if="saved.enabled">{{ $lastSent ? __('messages.graphic_email_last', ['when' => $lastSent]) : __('messages.graphic_email_never') }} {{ __('messages.graphic_email_skipped') }}</p>
                        <template v-if="s.enabled">
                            <div class="gfx-field">
                                <span class="gfx-label" id="gfx-often-label">{{ __('messages.graphic_email_how_often') }}</span>
                                <div class="gfx-opts" role="radiogroup" aria-labelledby="gfx-often-label">
                                    <label class="gfx-opt"><input type="radio" class="sr-only" name="frequency" value="daily" v-model="s.frequency"><span>{{ __('messages.daily') }}</span></label>
                                    <label class="gfx-opt"><input type="radio" class="sr-only" name="frequency" value="weekly" v-model="s.frequency"><span>{{ __('messages.weekly') }}</span></label>
                                    <label class="gfx-opt"><input type="radio" class="sr-only" name="frequency" value="monthly" v-model="s.frequency"><span>{{ __('messages.monthly') }}</span></label>
                                </div>
                            </div>
                            <div class="gfx-field" v-if="s.frequency === 'weekly'">
                                <span class="gfx-label" id="gfx-days-label">{{ __('messages.graphic_email_on') }}</span>
                                <div class="gfx-days" role="group" aria-labelledby="gfx-days-label">
                                    <button v-for="(day, index) in days" :key="index" type="button" class="gfx-day" :aria-pressed="s.send_days.indexOf(index) !== -1 ? 'true' : 'false'" @click="day_(index)" v-text="day"></button>
                                </div>
                                <p class="gfx-error" v-if="saveState.fields.send_days" role="alert" v-text="saveState.fields.send_days"></p>
                            </div>
                            <div class="gfx-field" v-if="s.frequency === 'monthly'">
                                <div class="gfx-inline">
                                    <label class="gfx-label" for="send_day">{{ __('messages.graphic_email_day') }}</label>
                                    <select id="send_day" v-model.number="s.send_day">
                                        @for ($i = 1; $i <= 28; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="gfx-field">
                                <div class="gfx-inline">
                                    <label class="gfx-label" for="send_hour">{{ __('messages.time') }} <span style="font-weight: 400; color: rgb(var(--ap-ink-3))" dir="ltr" v-pre>({{ $role->timezone ?: 'UTC' }})</span></label>
                                    <select id="send_hour" v-model.number="s.send_hour">
                                        @foreach ($hours as $i => $hourLabel)
                                        <option value="{{ $i }}">{{ $hourLabel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="gfx-field">
                                <label class="gfx-label" for="recipient_emails">{{ __('messages.graphic_email_to') }}</label>
                                <input type="text" id="recipient_emails" v-model="s.recipient_emails" maxlength="1000" dir="ltr" inputmode="email" autocomplete="off" :class="{ 'is-wrong': saveState.fields.recipient_emails }" :aria-invalid="saveState.fields.recipient_emails ? 'true' : 'false'" aria-describedby="recipient_emails_help">
                                <p class="gfx-error" v-if="saveState.fields.recipient_emails" role="alert" v-text="saveState.fields.recipient_emails"></p>
                                <p class="gfx-hint" id="recipient_emails_help">{{ __('messages.graphic_email_to_help') }}</p>
                            </div>
                            @if ($canEdit)
                            <div class="gfx-field">
                                <button type="button" class="page-tool" :disabled="test.busy" @click="sendTest">
                                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>
                                    <span v-text="test.busy ? @js(__('messages.sending')) : (dirty ? @js(__('messages.graphic_email_test_save')) : @js(__('messages.graphic_email_test')))"></span>
                                </button>
                                <p :class="test.ok ? 'gfx-hint is-plain' : 'gfx-error'" role="status" v-text="test.message || @js(__('messages.graphic_email_test_help'))"></p>
                            </div>
                            @endif
                        </template>
                        </fieldset>
                        @else
                        <div class="gfx-locked">
                            {{-- A plan that lapsed left a schedule stored: said as that, not as something never had. --}}
                            <p v-if="s.enabled" v-text="@js(__('messages.graphic_email_paused')).replace(':when', emailLine)"></p>
                            <p v-else>{{ __('messages.graphic_email_locked') }}</p>
                            <button type="button" class="page-tool" data-modal-open="upgrade-email-scheduling">{{ __('messages.upgrade_to_enterprise') }}</button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

        </form>

        {{-- The post. After the settings in the page's own order, so Tab runs from a setting on to Download and then to Save; on one column it is DRAWN first (order, in the stylesheet). --}}
        <section class="gfx-stage ap-card rounded-xl" aria-label="{{ __('messages.events_graphic') }}">
            {{-- "Nothing coming up" is only true while nothing is being left out: with recurring
                 events switched off, an empty answer may mean every date is one of those. --}}
            <div v-if="img.state === 'empty' && filtered" class="gfx-state">
                <div class="page-empty">
                    <h3>{{ __('messages.graphic_filtered_title') }}</h3>
                    <p>{{ __('messages.graphic_filtered_text') }}</p>
                    <div class="page-actions"><button type="button" class="{{ $secondaryButton }}" @click="s.exclude_recurring = false">{{ __('messages.graphic_filtered_undo') }}</button></div>
                </div>
            </div>
            <div v-else-if="img.state === 'empty'" class="gfx-state">
                <x-page-empty :title="__('messages.graphic_empty_title')" :text="__('messages.graphic_empty_text')"
                    icon="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z">
                    @if ($canEdit)
                    <x-brand-link href="{{ route('event.create', ['subdomain' => $role->subdomain]) }}">{{ __('messages.add_event') }}</x-brand-link>
                    @endif
                </x-page-empty>
            </div>

            <div v-else class="gfx-post" :class="{ 'is-folded': fold }">
                <div class="gfx-pane is-image">
                    <div class="gfx-pane-head">
                        <div class="gfx-pane-name">
                            <img v-if="img.src" class="gfx-thumb" :src="img.src" alt="">
                            <h2>{{ __('messages.image') }}</h2>
                            <span v-if="img.w" dir="ltr" :style="{ opacity: busy ? 0.45 : 1 }" v-text="img.w + ' × ' + img.h + ' px'"></span>
                        </div>
                        <div class="gfx-pane-tools">
                            <x-brand-button size="sm" class="gfx-when-strip" v-bind:class="{ 'is-off': off }" v-bind:aria-disabled="off ? 'true' : 'false'" v-on:click="download">{{ __('messages.download') }}</x-brand-button>
                            <button type="button" class="gfx-icon gfx-when-strip" @click="fold = true" title="{{ __('messages.graphic_show_image') }}" aria-label="{{ __('messages.graphic_show_image') }}">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                            </button>
                            <button type="button" class="page-tool gfx-when-open" :disabled="busy || img.state === 'loading'" @click="again">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                                {{ __('messages.refresh') }}
                            </button>
                            <button type="button" class="gfx-icon gfx-when-open" :disabled="img.state !== 'ready'" @click="enlarge" title="{{ __('messages.graphic_full_size') }}" aria-label="{{ __('messages.graphic_full_size') }}">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" /></svg>
                            </button>
                        </div>
                    </div>

                    <div v-if="img.state === 'noflyers'" class="gfx-state">
                        <x-page-empty :title="__('messages.graphic_noflyers_title')" v-bind:class="{ 'has-caption': cap.state === 'ready' }" :text="__('messages.graphic_noflyers_text')"
                            icon="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z">
                            <x-secondary-link href="{{ $scheduleUrl }}">{{ __('messages.graphic_go_to_events') }}</x-secondary-link>
                        </x-page-empty>
                        {{-- Only said while there is one: with flyers numbered the caption follows the image, and is empty too. --}}
                        <p v-if="cap.state === 'ready'" class="gfx-hint" style="text-align: center; margin-top: -0.5rem">{{ __('messages.graphic_noflyers_caption') }}</p>
                    </div>
                    <div v-else-if="img.state === 'error'" class="gfx-state">
                        <div class="page-empty" role="alert">
                            <h3>{{ __('messages.graphic_error_title') }}</h3>
                            <p v-text="img.message || @js(__('messages.graphic_error_text'))"></p>
                            <div class="page-actions"><button type="button" class="{{ $secondaryButton }}" @click="again">{{ __('messages.try_again') }}</button></div>
                        </div>
                    </div>
                    <div v-else class="gfx-canvas" :class="{ 'is-busy': busy }" ref="canvas">
                        <div v-if="img.state === 'loading'" class="gfx-ghost" :style="ghost"></div>
                        {{-- One line of ten flyers is 5,500 px wide, and twenty events in a list are
                             2,800 px tall: fitted to the card either is a ribbon. It is shown at a size
                             that can be read and scrolls in its own box (not a button: the keyboard
                             scrolls it), from the side the SCHEDULE reads from, with its far edge faded. --}}
                        <div v-else-if="long" class="gfx-scroll" :class="long" dir="{{ content_dir($role) }}" tabindex="0" role="group" aria-label="{{ __('messages.image') }}">
                            <img :src="img.src" alt="{{ __('messages.events_graphic') }}" ref="image">
                        </div>
                        <button v-else type="button" class="gfx-shot" tabindex="-1" @click="enlarge" aria-label="{{ __('messages.graphic_full_size') }}">
                            <img :src="img.src" alt="{{ __('messages.events_graphic') }}" ref="image">
                        </button>
                        <div v-if="busy" class="gfx-progress" aria-hidden="true"></div>
                        <div v-if="busy" class="gfx-busy-chip" aria-hidden="true">{{ __('messages.graphic_updating') }}</div>
                    </div>
                    <p v-if="long && img.state === 'ready'" class="gfx-long-note" v-text="long === 'is-wide' ? @js(__('messages.graphic_long_wide')) : @js(__('messages.graphic_long_tall'))"></p>
                    {{-- Said once for a screen reader, in one place that is always there: that a
                         new picture is on its way, and that it has come, with its size. --}}
                    <p class="sr-only" role="status" aria-live="polite" v-text="spoken"></p>

                    <div class="gfx-acts" v-if="img.state !== 'noflyers' && img.state !== 'error'">
                        <span class="gfx-note" :class="{ 'is-bad': note.bad }" role="status" v-text="note.text"></span>
                        {{-- Off while a picture is on its way, but still reachable by Tab (aria-disabled, not
                             disabled). One of them is the portal's brand button: Download where a mouse is
                             the pointer, Share where a finger is, and it stands last. --}}
                        <button type="button" class="{{ $secondaryButton }}" :class="{ 'is-off': off }" :aria-disabled="off ? 'true' : 'false'" @click="copyImage">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" :d="copied === 'image' ? @js($iconDone) : @js($iconCopy)" /></svg>
                            <span v-text="copied === 'image' ? @js(__('messages.graphic_copied')) : @js(__('messages.graphic_copy_image'))"></span>
                        </button>
                        <template v-if="canShare">
                            <x-brand-button v-if="touch" class="gap-2 gfx-main" style="order: 3" v-bind:class="{ 'is-off': off }" v-bind:aria-disabled="off ? 'true' : 'false'" v-on:click="share">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconShare }}" /></svg>
                                <span v-text="copied === 'share' ? @js(__('messages.graphic_shared')) : (cap.state === 'ready' ? @js(__('messages.graphic_share_both')) : @js(__('messages.share')))"></span>
                            </x-brand-button>
                            <button v-else type="button" class="{{ $secondaryButton }}" :class="{ 'is-off': off }" :aria-disabled="off ? 'true' : 'false'" @click="share">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconShare }}" /></svg>
                                <span v-text="copied === 'share' ? @js(__('messages.graphic_shared')) : @js(__('messages.share'))"></span>
                            </button>
                        </template>
                        <button v-if="canShare && touch" type="button" class="{{ $secondaryButton }}" :class="{ 'is-off': off }" :aria-disabled="off ? 'true' : 'false'" @click="download">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconDownload }}" /></svg>
                            {{ __('messages.download') }}
                        </button>
                        <x-brand-button v-else class="gap-2 gfx-main" v-bind:class="{ 'is-off': off }" v-bind:aria-disabled="off ? 'true' : 'false'" v-on:click="download">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconDownload }}" /></svg>
                            {{ __('messages.download') }}
                        </x-brand-button>
                    </div>
                </div>

                <div class="gfx-pane is-caption">
                    <div class="gfx-pane-head">
                        <div class="gfx-pane-name">
                            <h2>{{ __('messages.graphic_caption') }}</h2>
                            <span class="gfx-cap-line" :dir="s.force_english ? 'ltr' : @js(content_dir($role))" v-text="firstLine"></span>
                        </div>
                        <div class="gfx-pane-tools">
                            <button type="button" class="gfx-icon gfx-fold" @click="fold = ! fold" :aria-expanded="fold ? 'false' : 'true'" aria-controls="graphic-caption" :title="fold ? @js(__('messages.graphic_show_all')) : @js(__('messages.show_less'))" :aria-label="fold ? @js(__('messages.graphic_show_all')) : @js(__('messages.show_less'))">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" :d="fold ? 'M19.5 8.25l-7.5 7.5-7.5-7.5' : 'M4.5 15.75l7.5-7.5 7.5 7.5'" /></svg>
                            </button>
                            <button type="button" class="page-tool gfx-cap-top" :class="{ 'is-off': cap.state !== 'ready' || cap.busy }" :aria-disabled="cap.state !== 'ready' || cap.busy ? 'true' : 'false'" @click="copyText">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" :d="copied === 'text' ? @js($iconDone) : @js($iconCopy)" /></svg>
                                <span v-text="copied === 'text' ? @js(__('messages.graphic_copied')) : @js(__('messages.graphic_copy_caption'))"></span>
                            </button>
                        </div>
                    </div>
                    {{-- Said where it can be seen at every width: a rewrite that no longer matches
                         must not sit folded away beside a live Copy caption. --}}
                    <div v-if="ai.state !== 'off' && cap.state === 'ready'" class="gfx-ai" role="status">
                        <svg v-if="ai.state === 'working'" class="is-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2.5" opacity="0.25"></circle><path d="M21 12a9 9 0 00-9-9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"></path></svg>
                        <svg v-else fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" /></svg>
                        <span v-if="ai.state === 'working'">{{ __('messages.graphic_ai_working') }}</span>
                        <span v-else-if="ai.state === 'done'">{{ __('messages.graphic_ai_done') }}</span>
                        <span v-else-if="ai.state === 'stale'">{{ __('messages.graphic_ai_stale') }}</span>
                        <span v-else-if="ai.state === 'old'">{{ __('messages.graphic_ai_old') }}</span>
                        <span v-else v-text="ai.message"></span>
                        <button v-if="ai.state === 'stale' || ai.state === 'old' || ai.state === 'failed'" type="button" @click="rewrite">{{ __('messages.graphic_ai_rewrite') }}</button>
                    </div>
                    <div v-if="cap.state === 'loading'" class="gfx-text" role="status" aria-label="{{ __('messages.graphic_updating') }}"></div>
                    {{-- A caption that could not be written is said as that, never put where the
                         caption goes: it was one press from being copied and posted. --}}
                    <div v-else-if="cap.state === 'error'" class="gfx-state" role="alert">
                        <div class="page-empty is-compact">
                            <h3>{{ __('messages.graphic_caption_error') }}</h3>
                            <p v-text="cap.message || @js(__('messages.graphic_error_text'))"></p>
                            <div class="page-actions"><button type="button" class="page-tool" @click="loadText">{{ __('messages.try_again') }}</button></div>
                        </div>
                    </div>
                    <p v-else-if="cap.state === 'none'" class="gfx-cap-note" style="margin-top: 0">{{ __('messages.graphic_caption_none') }}</p>
                    <textarea v-else id="graphic-caption" class="gfx-text" readonly :value="cap.text" :style="captionStyle" :dir="s.force_english ? 'ltr' : @js(content_dir($role))" aria-label="{{ __('messages.graphic_caption') }}" ref="caption"></textarea>
                    <button v-if="cap.state === 'ready' && cap.long" type="button" class="gfx-more" @click="unfold" v-text="cap.open ? @js(__('messages.show_less')) : @js(__('messages.graphic_show_all'))"></button>
                    <p v-if="cap.note" class="gfx-cap-note is-bad" role="status" v-text="cap.note"></p>
                    {{-- Beside the image, the caption's one action stands level with Download and is its size: two things to take, a pair. --}}
                    <div class="gfx-acts gfx-cap-foot" v-if="cap.state === 'ready' || cap.state === 'loading'">
                        <button type="button" class="{{ $secondaryButton }}" :class="{ 'is-off': cap.state !== 'ready' || cap.busy }" :aria-disabled="cap.state !== 'ready' || cap.busy ? 'true' : 'false'" @click="copyText">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" :d="copied === 'text' ? @js($iconDone) : @js($iconCopy)" /></svg>
                            <span v-text="copied === 'text' ? @js(__('messages.graphic_copied')) : @js(__('messages.graphic_copy_caption'))"></span>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        {{-- The kit's save bar, drawn by this page's own mount, only while there is something to
             save or a save has just answered. Save is a button and not the form's submit, so Enter
             in a field saves nothing: with the email on, a save changes what is sent. --}}
        @if ($canEdit)
        <div class="event-save-bar gfx-bar" v-if="barOn">
            <div class="event-save-bar-inner">
                <div class="event-save-status" aria-live="polite">
                    <span v-if="asking">{{ __('messages.discard_unsaved_changes') }}</span>
                    <span v-else-if="saveState.error" class="event-save-strong" v-text="saveState.error"></span>
                    <span v-else-if="saveState.done && ! dirty" class="event-save-quiet" v-text="s.enabled && enterprise ? @js(__('messages.graphic_saved_email')) : @js(__('messages.saved'))"></span>
                    <span v-else>
                        <span class="event-save-pair">
                            <span>{{ __('messages.graphic_unsaved') }}:</span>
                            <template v-for="(group, index) in dirtyGroups" :key="group.id">
                                <span v-if="index > 0" aria-hidden="true">&middot;</span>
                                <button type="button" class="event-link" @click="go(group.id)" v-text="group.label"></button>
                            </template>
                        </span>
                    </span>
                    <small v-if="dirty && ! saveState.error && ! asking" v-text="s.enabled && enterprise ? @js(__('messages.graphic_unsaved_help_email')) : @js(__('messages.graphic_unsaved_help'))"></small>
                </div>
                <div class="event-save-actions" v-if="dirty && ! asking">
                    <button type="button" class="event-bar-text" @click="asking = true">{{ __('messages.graphic_discard') }}</button>
                    <x-brand-button type="button" class="event-bar-save" v-on:click="save" v-bind:disabled="saveState.busy">
                        <span v-text="saveState.busy ? @js(__('messages.saving')) : @js(__('messages.save'))"></span>
                    </x-brand-button>
                </div>
                <div class="event-save-actions" v-if="asking">
                    <button type="button" class="event-bar-text" @click="asking = false">{{ __('messages.keep_editing') }}</button>
                    <button type="button" class="event-bar-quiet" @click="reset">{{ __('messages.discard') }}</button>
                </div>
            </div>
        </div>
        @endif


        <button type="button" class="gfx-peek" :class="{ 'is-on': peek && img.state === 'ready', 'is-busy': busy }" :style="{ backgroundImage: img.src ? 'url(' + img.src + ')' : 'none' }" @click="toTop" aria-label="{{ __('messages.graphic_peek') }}"></button>
    </div>
    </div>
    </div>
    </div>

    @if ($hosted && ! $isEnterprise)
    <x-upgrade-modal name="upgrade-ai-prompt" tier="enterprise" :subdomain="$role->subdomain" :learnMoreUrl="marketing_url('/features/event-graphics')">
        {{ __('messages.upgrade_feature_description_ai_prompt') }}
    </x-upgrade-modal>
    <x-upgrade-modal name="upgrade-email-scheduling" tier="enterprise" :subdomain="$role->subdomain" :learnMoreUrl="marketing_url('/features/event-graphics')">
        {{ __('messages.graphic_email_locked') }}
    </x-upgrade-modal>
    @endif

    <script {!! nonce_attr() !!}>window.Vue || document.write('<script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}><\/script>')</script>
    <script {!! nonce_attr() !!}>
    (function () {
        var boot = @json($boot);
        var clone = function (value) { return JSON.parse(JSON.stringify(value)); };
        var token = function () { var meta = document.querySelector('meta[name="csrf-token"]'); return meta ? meta.content : ''; };
        // Every request says it wants JSON, so a refused one answers with its reason and not a redirect.
        var ask = function (url, options) {
            options = options || {};
            options.headers = Object.assign({ 'Accept': 'application/json', 'X-CSRF-TOKEN': token() }, options.headers || {});
            return fetch(url, options).then(function (response) {
                return response.json().catch(function () { return {}; }).then(function (body) {
                    return { ok: response.ok, status: response.status, body: body || {} };
                });
            });
        };
        var reason = function (result, fallback) {
            var body = result.body || {};
            // Signed out, or the page sat open past its session: said in the page's own words.
            if (result.status === 401 || result.status === 419) { return boot.words.session; }
            if (body.errors) {
                for (var key in body.errors) { return [].concat(body.errors[key])[0]; }
            }
            return body.message || body.error || fallback;
        };
        var groups = {
            look: ['layout', 'image_size', 'max_per_row'],
            events: ['event_count', 'max_per_schedule', 'exclude_recurring'],
            flyers: ['date_position', 'overlay_text', 'number_events'],
            header: ['header_text', 'footer_text'],
            caption: ['text_template', 'text_show_all', 'force_english', 'url_include_https', 'url_include_id', 'ai_prompt'],
            email: ['enabled', 'frequency', 'send_days', 'send_day', 'send_hour', 'recipient_emails'],
        };
        var words = boot.words;

        Vue.createApp({
            data: function () {
                return {
                    s: clone(boot.settings),
                    saved: clone(boot.settings),
                    defaultTemplate: boot.defaultTemplate,
                    canEdit: boot.canEdit,
                    enterprise: boot.enterprise,
                    days: boot.days,
                    open: '',
                    img: { state: 'loading', src: '', w: 0, h: 0, message: '' },
                    // state: loading, ready, none (nothing to write), error. Only a ready caption can be copied or shared.
                    cap: { state: 'loading', text: '', message: '', note: '', open: false, long: false, busy: false, height: 0 },
                    ai: { state: 'off', message: '' },
                    busy: false,
                    saveState: { busy: false, done: false, error: '', fields: {} },
                    copied: '',
                    note: { text: '', bad: false },
                    canShare: false,
                    peek: false,
                    fold: true,
                    header: { url: boot.headerUrl || '', busy: false, asking: false, error: '' },
                    test: { busy: false, message: '', ok: true },
                    turn: { image: 0, text: 0, ai: 0 },
                    aiReady: boot.aiReady,
                    filtered: false,
                    asking: false,
                    spoken: '',
                    firstText: true,
                };
            },
            computed: {
                flyerOptions: function () { return this.s.layout !== 'list'; },
                // Nothing of a picture can be taken while it is loading or being replaced.
                off: function () { return this.img.state !== 'ready' || this.busy; },
                // A picture far longer one way than the other is scrolled, not squeezed.
                long: function () {
                    if (! this.img.w || this.img.state !== 'ready') { return ''; }
                    return this.img.w > this.img.h * 3.2 ? 'is-wide' : (this.img.h > this.img.w * 2.6 ? 'is-tall' : '');
                },
                // Text that would be lost: the one kind of unsaved change worth stopping a person for.
                typed: function () {
                    return ['text_template', 'ai_prompt', 'header_text', 'footer_text', 'overlay_text', 'recipient_emails'].some(function (key) {
                        return (key !== 'ai_prompt' && key !== 'recipient_emails' || this.enterprise) && this.s[key] !== this.saved[key];
                    }, this);
                },
                emailLine: function () {
                    var s = this.s;
                    var hour = boot.hours[s.send_hour] || '';
                    if (s.frequency === 'daily') { return words.daily.replace(':time', hour); }
                    if (s.frequency === 'monthly') { return words.monthly.replace(':day', s.send_day).replace(':time', hour); }
                    var names = s.send_days.slice().sort().map(function (day) { return boot.days[day]; });
                    return names.length ? words.weekly.replace(':days', names.join(', ')).replace(':time', hour) : words.no_day;
                },
                shapeSize: function () { return this.s.image_size === 'auto' ? '' : boot.px[this.s.image_size]; },
                // What the picture depends on, and what the caption depends on: a change to one
                // of them asks for that one again, and nothing else does.
                imageKey: function () {
                    var s = this.s;
                    return JSON.stringify([s.layout, s.image_size, this.flyerOptions ? [s.date_position, s.max_per_row, s.date_position ? s.overlay_text : ''] : 0, s.number_events,
                        s.header_text, s.footer_text, s.event_count, s.max_per_schedule, s.exclude_recurring, s.force_english, this.header.url]);
                },
                textKey: function () {
                    var s = this.s;
                    return JSON.stringify([s.text_template, s.event_count, s.max_per_schedule, s.exclude_recurring, s.url_include_https, s.url_include_id,
                        s.text_show_all, s.number_events, s.force_english]);
                },
                dirtyGroups: function () {
                    var out = [];
                    for (var id in groups) {
                        if (id === 'email' && ! this.enterprise) { continue; }
                        var changed = groups[id].some(function (key) {
                            if (key === 'ai_prompt' && ! this.enterprise) { return false; }
                            return JSON.stringify(this.s[key]) !== JSON.stringify(this.saved[key]);
                        }, this);
                        if (changed) { out.push({ id: id, label: boot.groups[id] }); }
                    }
                    return out;
                },
                dirty: function () { return this.dirtyGroups.length > 0; },
                barOn: function () { return this.canEdit && (this.dirty || this.saveState.done || !! this.saveState.error); },
                tip: function () { return boot.tips[this.s.layout]; },
                perRowLine: function () {
                    if (this.s.layout === 'list') { return words.per_row_list; }
                    if (this.s.max_per_row) { return words.per_row_count.replace(':count', this.s.max_per_row); }
                    return this.s.layout === 'row' ? words.per_row_rows : words.per_row;
                },
                captionStyle: function () {
                    var style = { opacity: this.cap.busy ? 0.45 : 1 };
                    if (this.cap.open && this.cap.height) { style.height = this.cap.height + 'px'; }
                    return style;
                },
                firstLine: function () { return this.cap.text.split('\n').filter(function (line) { return line.trim(); }).slice(0, 2).join('  ').replace(/\*/g, ''); },
                touch: function () { return !! (window.matchMedia && window.matchMedia('(pointer: coarse)').matches); },
                shapeLine: function () {
                    return this.s.image_size === 'auto' ? words.shape_auto : boot.sizes[this.s.image_size] + ' ' + words.shape_fitted;
                },
                ghost: function () {
                    var ratios = { auto: '10 / 9', square: '1 / 1', portrait: '4 / 5', story: '9 / 16', landscape: '1200 / 630' };
                    return { aspectRatio: ratios[this.s.image_size] || '1 / 1' };
                },
                sum: function () {
                    var s = this.s;
                    var events = [words.next.replace(':count', s.event_count || '20')];
                    if (s.max_per_schedule) { events.push(words.each.replace(':count', s.max_per_schedule)); }
                    if (s.exclude_recurring) { events.push(words.no_recurring); }
                    var flyers = this.flyerOptions ? [s.date_position === 'overlay' ? words.date_overlay : (s.date_position === 'above' ? words.date_above : words.flyers_only)] : [];
                    if (s.number_events) { flyers.push(flyers.length ? words.numbered : words.numbered.charAt(0).toUpperCase() + words.numbered.slice(1)); }
                    if (! flyers.length) { flyers.push(words.not_used); }
                    var header = [];
                    if (this.header.url) { header.push(words.banner); }
                    if (s.header_text) { header.push(header.length ? words.headline : words.headline.charAt(0).toUpperCase() + words.headline.slice(1)); }
                    if (s.footer_text) { header.push(header.length ? words.signoff : words.signoff.charAt(0).toUpperCase() + words.signoff.slice(1)); }
                    var caption = [s.text_template === this.defaultTemplate ? words.default : words.own];
                    if (s.text_show_all && ! s.number_events) { caption.push(words.all_events); }
                    if (s.force_english) { caption.push(words.english); }
                    if (this.enterprise && s.ai_prompt.trim()) { caption.push(words.ai); }
                    var email = this.enterprise ? (s.enabled ? this.emailLine : words.off) : (s.enabled ? words.paused : words.locked_email);
                    return {
                        events: events.join(' · '),
                        flyers: flyers.join(' · '),
                        header: header.length ? header.join(' · ') : words.none,
                        headerSet: header.length > 0,
                        caption: caption.join(' · '),
                        email: email,
                    };
                },
            },
            watch: {
                imageKey: function () { this.later('image', 450); },
                barOn: function (on, was) {
                    // When the bar goes, the keyboard goes back to where it was working.
                    if (was && ! on) {
                        var self = this;
                        this.$nextTick(function () {
                            var at = document.activeElement;
                            if (self._last && (! at || at === document.body) && document.contains(self._last)) { self._last.focus({ preventScroll: true }); }
                        });
                    }
                },
                textKey: function () { this.later('text', 450); },
                // The instruction changed. What is in the box is either the plain caption (say so, and
                // offer the rewrite) or a rewrite made with the earlier instruction (say that). With
                // the instruction emptied, a rewrite in the box is no longer wanted: back to the plain one.
                's.ai_prompt': function (prompt) {
                    var had = this.ai.state === 'done' || this.ai.state === 'old';
                    this.turn.ai++;
                    if (! this.aiReady || this.cap.state !== 'ready') { this.ai.state = 'off'; return; }
                    if (! prompt.trim()) {
                        this.ai.state = 'off';
                        if (had) { this.later('text', 300); }
                        return;
                    }
                    this.ai.state = had ? 'old' : 'stale';
                },
                's.enabled': function (on) { if (on && ! this.s.recipient_emails.trim()) { this.s.recipient_emails = boot.userEmail; } },
                dirty: function (on) { if (on) { this.saveState.done = false; } },
            },
            methods: {
                query: function (type) {
                    var s = this.s;
                    var q = new URLSearchParams();
                    q.set('layout', s.layout);
                    q.set('type', type);
                    if (s.exclude_recurring) { q.set('exclude_recurring', '1'); }
                    if (this.flyerOptions && s.date_position) { q.set('date_position', s.date_position); q.set('overlay_text', s.overlay_text); }
                    if (this.flyerOptions && s.max_per_row) { q.set('max_per_row', s.max_per_row); }
                    if (s.event_count) { q.set('event_count', s.event_count); }
                    if (s.max_per_schedule) { q.set('max_per_schedule', s.max_per_schedule); }
                    if (s.image_size !== 'auto') { q.set('image_size', s.image_size); }
                    q.set('header_text', s.header_text);
                    q.set('footer_text', s.footer_text);
                    q.set('number_events', s.number_events ? '1' : '0');
                    q.set('force_english', s.force_english ? '1' : '0');
                    if (type === 'text') {
                        q.set('text_template', s.text_template);
                        q.set('ai_prompt', this.enterprise ? s.ai_prompt : '');
                        q.set('url_include_https', s.url_include_https ? '1' : '0');
                        q.set('url_include_id', s.url_include_id ? '1' : '0');
                        q.set('text_show_all', s.text_show_all ? '1' : '0');
                    }
                    return boot.urls.data + '?' + q.toString();
                },
                later: function (what, wait) {
                    var self = this;
                    // Dimmed at once: for the half second before the request leaves, the picture on
                    // screen is already not what the settings say, and must not be taken as if it were.
                    if (what === 'image' && this.img.state === 'ready') { this.busy = true; }
                    if (what === 'text' && this.cap.state === 'ready') { this.cap.busy = true; }
                    clearTimeout(this['_' + what]);
                    this['_' + what] = setTimeout(function () { what === 'image' ? self.loadImage() : self.loadText(); }, wait);
                },
                // The request before this one is dropped: a new picture is asked for on every
                // change, each one is seconds of work, and only the last can be shown.
                fresh: function (what) {
                    if (this['_' + what + 'Ask']) { this['_' + what + 'Ask'].abort(); }
                    this['_' + what + 'Ask'] = 'AbortController' in window ? new AbortController() : null;
                    return this['_' + what + 'Ask'] ? { signal: this['_' + what + 'Ask'].signal } : {};
                },
                loadImage: function () {
                    var self = this;
                    var turn = ++this.turn.image;
                    this.filtered = this.s.exclude_recurring;
                    this.spoken = words.say_updating;
                    if (this.img.src && this.img.state === 'ready') { this.busy = true; } else { this.img.state = 'loading'; }
                    ask(this.query('image'), this.fresh('image')).then(function (result) {
                        if (turn !== self.turn.image) { return; }
                        self.busy = false;
                        var body = result.body;
                        if (! result.ok) {
                            self.img.state = 'error';
                            self.img.message = reason(result, '');
                        } else if (body.error) {
                            self.img.state = body.error_type === 'no_events_found' ? 'empty' : 'noflyers';
                            self.img.src = '';
                            self.img.w = 0;
                        } else {
                            // Kept as a file in memory with a short address: as a string it was
                            // megabytes, written into the page again on every keystroke.
                            var bytes = atob(body.image);
                            var raw = new Uint8Array(bytes.length);
                            for (var i = 0; i < bytes.length; i++) { raw[i] = bytes.charCodeAt(i); }
                            var address = URL.createObjectURL(new Blob([raw], { type: 'image/png' }));
                            var probe = new Image();
                            probe.onload = function () {
                                if (turn !== self.turn.image) { URL.revokeObjectURL(address); return; }
                                var old = self.img.src;
                                self.img.w = probe.naturalWidth;
                                self.img.h = probe.naturalHeight;
                                self.img.src = address;
                                self.img.state = 'ready';
                                self.spoken = words.say_updated.replace(':size', probe.naturalWidth + ' × ' + probe.naturalHeight);
                                if (old) { setTimeout(function () { URL.revokeObjectURL(old); }, 1000); }
                            };
                            probe.onerror = function () {
                                URL.revokeObjectURL(address);
                                if (turn !== self.turn.image) { return; }
                                self.img.state = 'error';
                                self.img.message = '';
                            };
                            probe.src = address;
                        }
                    }).catch(function (error) {
                        if (turn !== self.turn.image || (error && error.name === 'AbortError')) { return; }
                        self.busy = false;
                        self.img.state = 'error';
                        self.img.message = '';
                    });
                },
                loadText: function () {
                    var self = this;
                    var turn = ++this.turn.text;
                    // A rewrite still on its way is for the caption this one replaces.
                    this.turn.ai++;
                    this.cap.note = '';
                    if (this.cap.state === 'ready') { this.cap.busy = true; }
                    ask(this.query('text'), this.fresh('text')).then(function (result) {
                        if (turn !== self.turn.text) { return; }
                        var body = result.body;
                        self.cap.busy = false;
                        self.cap.open = false;
                        if (body.error_type === 'no_events_with_flyers' || body.error_type === 'no_events_found') {
                            // Nothing to write (with flyers numbered the caption follows the image).
                            self.cap.state = 'none';
                            self.cap.text = '';
                            self.ai.state = 'off';
                            return;
                        }
                        if (! result.ok || body.error || typeof body.text !== 'string') {
                            self.cap.state = 'error';
                            self.cap.text = '';
                            self.cap.message = reason(result, '');
                            self.ai.state = 'off';
                            return;
                        }
                        self.cap.text = body.text;
                        self.cap.state = 'ready';
                        self.cap.long = body.text.split('\n').length > 8;
                        if (! (self.aiReady && body.ai_prompt_enabled)) {
                            self.ai.state = 'off';
                        } else if (self.firstText) {
                            self.rewrite();
                        } else {
                            self.ai.state = 'stale';
                        }
                        self.firstText = false;
                    }).catch(function (error) {
                        if (turn !== self.turn.text || (error && error.name === 'AbortError')) { return; }
                        self.cap.busy = false;
                        self.cap.state = 'error';
                        self.cap.text = '';
                        self.cap.message = '';
                    });
                },
                again: function () {
                    this.loadImage();
                    this.loadText();
                },
                // AI rewrites once when the page opens; after that only when asked, because each
                // rewrite counts against the day's allowance. An answer is used only if nothing
                // it was written from has changed while it was on its way.
                rewrite: function () {
                    var self = this;
                    var s = this.s;
                    var mine = ++this.turn.ai;
                    var late = function () { return mine !== self.turn.ai; };
                    var failed = function (message) {
                        if (late()) { return; }
                        self.ai.state = 'failed';
                        self.ai.message = message || words.ai_failed;
                    };
                    this.ai.state = 'working';
                    ask(boot.urls.ai, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ text: this.cap.text, ai_prompt: s.ai_prompt, exclude_recurring: s.exclude_recurring, text_show_all: s.text_show_all,
                            event_count: s.event_count, max_per_schedule: s.max_per_schedule, number_events: s.number_events, force_english: s.force_english }),
                    }).then(function (result) {
                        if (late()) { return; }
                        if (! result.ok || ! result.body.request_id) { failed(reason(result, words.ai_failed)); return; }
                        var tries = 0;
                        var poll = function () {
                            if (late()) { return; }
                            ask(boot.urls.ai + '/' + result.body.request_id).then(function (answer) {
                                if (late()) { return; }
                                tries++;
                                if (answer.body.status === 'completed' && answer.body.text) {
                                    self.cap.text = answer.body.text;
                                    self.cap.long = answer.body.text.split('\n').length > 8;
                                    self.ai.state = 'done';
                                } else if (answer.body.status === 'processing' && tries < 40) {
                                    setTimeout(poll, 3000);
                                } else {
                                    failed();
                                }
                            }).catch(function () { failed(); });
                        };
                        setTimeout(poll, 1500);
                    }).catch(function () { failed(); });
                },
                toggle: function (row) {
                    var closing = this.open === row;
                    this.open = closing ? '' : row;
                    // The post shows what is being worked on: the caption while its settings are
                    // open, and the image again when they close or another row opens.
                    this.fold = this.open !== 'caption';
                    if (this.open) {
                        this.$nextTick(function () {
                            var pane = document.getElementById('graphic-pane-' + row);
                            var head = pane && pane.previousElementSibling;
                            if (head && (pane.getBoundingClientRect().bottom > window.innerHeight - 96 || head.getBoundingClientRect().top < 80)) {
                                head.scrollIntoView({ block: 'start', behavior: 'smooth' });
                            }
                        });
                    }
                },
                // Where the two sides start, for the card's height: a notice above them (the
                // timezone warning, a viewer's line) moves it, and a guess put Download below the fold.
                size: function () {
                    var root = document.querySelector('.gfx');
                    // Read where it stands NOW: once the page has scrolled and the card is held under
                    // the top bar, the room it left above becomes the card's.
                    if (root) { root.style.setProperty('--gfx-top', Math.max(88, Math.round(root.getBoundingClientRect().top)) + 'px'); }
                },
                // To a group named by the bar: its row is opened, brought into view and given the focus.
                go: function (group) {
                    this.open = group === 'look' ? '' : group;
                    if (this.open) { this.fold = this.open !== 'caption'; }
                    this.$nextTick(function () {
                        var pane = document.getElementById('graphic-pane-' + group);
                        var target = group === 'look' ? document.querySelector('.gfx-tile') : (pane && pane.previousElementSibling);
                        if (target) { target.scrollIntoView({ block: 'center', behavior: 'smooth' }); target.focus({ preventScroll: true }); }
                    });
                },
                insert: function (field, text) {
                    var el = document.getElementById(field);
                    var value = this.s[field] || '';
                    var start = el && el.selectionStart != null ? el.selectionStart : value.length;
                    var end = el && el.selectionEnd != null ? el.selectionEnd : value.length;
                    this.s[field] = value.slice(0, start) + text + value.slice(end);
                    this.$nextTick(function () { if (el) { el.focus(); el.setSelectionRange(start + text.length, start + text.length); } });
                },
                day_: function (index) {
                    var at = this.s.send_days.indexOf(index);
                    at === -1 ? this.s.send_days.push(index) : this.s.send_days.splice(at, 1);
                },
                payload: function () {
                    var s = this.s;
                    var out = {
                        layout: s.layout, image_size: s.image_size, max_per_row: s.max_per_row ? parseInt(s.max_per_row, 10) : null,
                        event_count: s.event_count ? parseInt(s.event_count, 10) : null, max_per_schedule: s.max_per_schedule ? parseInt(s.max_per_schedule, 10) : null,
                        exclude_recurring: s.exclude_recurring, date_position: s.date_position || null, overlay_text: s.overlay_text, number_events: s.number_events,
                        header_text: s.header_text, footer_text: s.footer_text, text_template: s.text_template, text_show_all: s.text_show_all,
                        force_english: s.force_english, url_include_https: s.url_include_https, url_include_id: s.url_include_id,
                    };
                    // Only what this plan shows is sent: a part that is locked keeps what it holds.
                    if (this.enterprise) {
                        out.ai_prompt = s.ai_prompt;
                        out.enabled = s.enabled;
                        out.frequency = s.frequency;
                        var week = s.send_days.slice().sort();
                        out.send_days = week;
                        // As the page has always sent it: the month's day, or the week's first day.
                        out.send_day = s.frequency === 'monthly' ? s.send_day : (s.frequency === 'weekly' && week.length ? week[0] : 1);
                        out.send_hour = s.send_hour;
                        out.recipient_emails = s.recipient_emails;
                    }
                    return out;
                },
                save: function () {
                    var self = this;
                    var s = this.s;
                    if (! this.canEdit || this.saveState.busy) { return Promise.resolve(false); }
                    this.asking = false;
                    this.saveState.error = '';
                    this.saveState.fields = {};
                    if (this.enterprise && s.enabled && ! s.recipient_emails.trim()) {
                        this.saveState.fields = { recipient_emails: words.recipients };
                    } else if (this.enterprise && s.enabled && s.frequency === 'weekly' && ! s.send_days.length) {
                        this.saveState.fields = { send_days: words.days };
                    }
                    if (Object.keys(this.saveState.fields).length) {
                        this.saveState.error = this.saveState.fields[Object.keys(this.saveState.fields)[0]];
                        this.go('email');
                        return Promise.resolve(false);
                    }
                    this.saveState.busy = true;
                    // What was sent is what was saved: a change made while the save was on its
                    // way is still unsaved when the answer comes.
                    var sent = clone(this.s);
                    return ask(boot.urls.save, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(this.payload()) }).then(function (result) {
                        self.saveState.busy = false;
                        if (result.ok && result.body.success) {
                            self.saved = sent;
                            self.saveState.done = true;
                            setTimeout(function () { self.saveState.done = false; }, 2600);
                            return true;
                        }
                        self.saveState.error = reason(result, words.save_failed);
                        var errors = result.body.errors || {};
                        for (var key in errors) { self.saveState.fields[key.split('.')[0]] = [].concat(errors[key])[0]; }
                        if (self.saveState.fields.recipient_emails || self.saveState.fields.send_days) { self.go('email'); }
                        return false;
                    }).catch(function () {
                        self.saveState.busy = false;
                        self.saveState.error = words.save_failed;
                        return false;
                    });
                },
                reset: function () {
                    this.s = clone(this.saved);
                    this.asking = false;
                    this.saveState.error = '';
                    this.saveState.fields = {};
                },
                sendTest: function () {
                    var self = this;
                    this.test = { busy: true, message: '', ok: true };
                    var first = this.dirty ? this.save() : Promise.resolve(true);
                    first.then(function (saved) {
                        if (! saved) { self.test = { busy: false, message: self.saveState.error, ok: false }; return; }
                        return ask(boot.urls.test, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: '{}' }).then(function (result) {
                            var ok = result.ok && result.body.success;
                            self.test = { busy: false, ok: !! ok, message: ok ? (result.body.message || words.test_sent) : reason(result, words.test_failed) };
                        });
                    }).catch(function () { self.test = { busy: false, message: words.test_failed, ok: false }; });
                },
                upload: function (event) {
                    var self = this;
                    var file = event.target.files[0];
                    if (! file) { return; }
                    var form = new FormData();
                    form.append('header_image', file);
                    this.header.busy = true;
                    this.header.error = '';
                    ask(boot.urls.header, { method: 'POST', body: form }).then(function (result) {
                        self.header.busy = false;
                        event.target.value = '';
                        if (result.ok && result.body.success) { self.header.url = result.body.url; } else { self.header.error = reason(result, words.save_failed); }
                    }).catch(function () { self.header.busy = false; self.header.error = words.save_failed; });
                },
                removeHeader: function () {
                    var self = this;
                    this.header.asking = false;
                    ask(boot.urls.header, { method: 'DELETE' }).then(function (result) {
                        if (result.ok && result.body.success) { self.header.url = ''; } else { self.header.error = reason(result, words.failed); }
                    }).catch(function () { self.header.error = words.failed; });
                },
                blob: function () {
                    var image = this.$refs.image;
                    return new Promise(function (resolve) {
                        var canvas = document.createElement('canvas');
                        canvas.width = image.naturalWidth;
                        canvas.height = image.naturalHeight;
                        canvas.getContext('2d').drawImage(image, 0, 0);
                        canvas.toBlob(resolve, 'image/png');
                    });
                },
                flash: function (what) {
                    var self = this;
                    this.copied = what;
                    this.note = { text: '', bad: false };
                    setTimeout(function () { if (self.copied === what) { self.copied = ''; } }, 2000);
                },
                fail: function (text) { this.note = { text: text, bad: true }; },
                download: function () {
                    if (this.off) { return; }
                    this.blob().then(function (blob) {
                        var link = document.createElement('a');
                        link.href = URL.createObjectURL(blob);
                        link.download = boot.file;
                        document.body.appendChild(link);
                        link.click();
                        link.remove();
                        setTimeout(function () { URL.revokeObjectURL(link.href); }, 1000);
                    });
                },
                copyImage: function () {
                    var self = this;
                    if (this.off) { return; }
                    try {
                        navigator.clipboard.write([new ClipboardItem({ 'image/png': this.blob() })]).then(function () { self.flash('image'); }, function () { self.fail(words.copy_failed); });
                    } catch (error) {
                        this.fail(words.copy_failed);
                    }
                },
                copyText: function () {
                    var self = this;
                    if (this.cap.state !== 'ready' || this.cap.busy) { return; }
                    var refused = function () {
                        // The words are still there to be taken by hand: selected, and said so beside them.
                        self.cap.note = words.copy_caption_failed;
                        if (self.$refs.caption) { self.$refs.caption.focus(); self.$refs.caption.select(); }
                    };
                    this.cap.note = '';
                    try {
                        navigator.clipboard.writeText(this.cap.text).then(function () { self.flash('text'); }, refused);
                    } catch (error) {
                        refused();
                    }
                },
                enlarge: function () {
                    if (! this.off && window.showLightbox) { window.showLightbox(this.img.src); }
                },
                unfold: function () {
                    this.cap.open = ! this.cap.open;
                    if (this.cap.open && this.$refs.caption) { this.cap.height = this.$refs.caption.scrollHeight + 2; }
                },
                share: function () {
                    var self = this;
                    if (this.off) { return; }
                    this.blob().then(function (blob) {
                        var what = { files: [new File([blob], boot.file, { type: 'image/png' })] };
                        if (self.cap.state === 'ready') { what.text = self.cap.text; }
                        return navigator.share(what);
                    }).then(function () { self.flash('share'); }).catch(function (error) { if (! error || error.name !== 'AbortError') { self.fail(words.share_failed); } });
                },
                toTop: function () {
                    document.querySelector('.gfx-stage').scrollIntoView({ block: 'start', behavior: 'smooth' });
                },
            },
            mounted: function () {
                var self = this;
                try {
                    var probe = new File([new Blob(['x'], { type: 'image/png' })], 'x.png', { type: 'image/png' });
                    this.canShare = !! (navigator.canShare && navigator.canShare({ files: [probe] }));
                } catch (error) {
                    this.canShare = false;
                }
                // The small copy in the corner is only for when the picture itself is out of sight.
                if ('IntersectionObserver' in window) {
                    new IntersectionObserver(function (entries) { self.peek = ! entries[0].isIntersecting; }, { threshold: 0.15 }).observe(document.querySelector('.gfx-stage'));
                }
                // Leaving asks only when typed text would be lost (a caption's wording, an address
                // list). A layout tried for one post and not saved is the page working as meant:
                // the bar itself says a download needs no save.
                window.addEventListener('beforeunload', function (event) {
                    if (self.typed && self.canEdit && ! window._skipUnsavedWarning) { event.preventDefault(); event.returnValue = ''; }
                });
                document.addEventListener('focusin', function (event) {
                    if (event.target.closest && event.target.closest('.gfx-controls')) { self._last = event.target; }
                });
                var waiting = false;
                window.addEventListener('scroll', function () {
                    if (waiting) { return; }
                    waiting = true;
                    requestAnimationFrame(function () { waiting = false; self.size(); });
                }, { passive: true });
                // What the page this one replaced kept in the browser: nothing reads it now.
                try {
                    ['graphic_page_tab', 'graphic_settings_tab', 'graphic_settings_open', 'textarea_height_text_template', 'textarea_height_text_template_mobile',
                        'textarea_height_ai_prompt', 'textarea_height_ai_prompt_mobile'].forEach(function (key) { localStorage.removeItem(key); });
                } catch (error) {}
                // Measured once the page is drawn: while it mounts it is still hidden (v-cloak) and
                // has no place on the screen to measure.
                window.addEventListener('resize', this.size);
                window.addEventListener('load', this.size);
                requestAnimationFrame(this.size);
                this.loadImage();
                this.loadText();
            },
        }).mount('#graphic-app');
    })();
    </script>
</x-app-admin-layout>
