{{-- The ticket: a dark, glowing object in the colour of the schedule that sold it. Used by
     ticket/view only, so it is not part of partials/guest-kit-styles, which every guest page loads.

     What it keeps from the ticket it replaces: the dark ground, the soft light behind it, Manrope,
     the coloured icon tiles and the notches of a tear-off stub. What changed: the light was one
     violet for every schedule and is the schedule's own colour now (--es-glow, from
     App\Utils\GuestTheme), nothing loops, and the code is the largest thing on it.

     Colours go through the --tk- tokens so the print sheet can turn the whole ticket black on
     white by redefining six of them. Sizes are in rem and properties are logical, as in the kit.
     The door view is NOT inside .gk-ticket: that element has a filter, which would make it the
     containing block of a fixed child and un-fix it. --}}
<style {!! nonce_attr() !!}>
    .gk-tkpage {
        --tk-ink: #ffffff;
        --tk-ink-2: #c3c6d4;
        --tk-ink-3: #9a9eb2;
        --tk-line: rgb(255 255 255 / .12);
        --tk-well: rgb(255 255 255 / .06);
        --tk-card: #15151e;
        --tk-card-top: #1d1d29;
        --tk-ground: #09090f;
        position: relative;
        z-index: 0;
        min-height: 100vh;
        padding: 1rem;
        font-family: 'Manrope', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: .9375rem;
        line-height: 1.45;
        color: var(--tk-ink-2);
        background: var(--tk-ground);
    }
    @media (min-width: 40rem) { .gk-tkpage { padding: 1.5rem; } }
    {{-- The light. A fixed layer of its own, not background-attachment: fixed, which phones ignore. --}}
    .gk-tkpage::before {
        content: '';
        position: fixed;
        inset: 0;
        z-index: -1;
        pointer-events: none;
        background:
            radial-gradient(75% 46% at 10% 0%, rgb(var(--es-glow) / .34), transparent 72%),
            radial-gradient(65% 42% at 100% 100%, rgb(var(--es-glow) / .18), transparent 72%);
    }
    .gk-tkpage :focus-visible { outline: 2px solid #ffffff; outline-offset: 2px; border-radius: .375rem; }
    .gk-tk-wrap { display: grid; gap: .875rem; justify-items: start; width: 100%; max-width: 26rem; margin: 0 auto; }
    .gk-tk-wrap > * { min-width: 0; max-width: 100%; }

    {{-- The way back to the event. --}}
    .gk-tk-back { display: inline-flex; align-items: center; gap: .5rem; min-height: 2.75rem; padding: 0 .875rem 0 .5rem; border-radius: 999px; background: rgb(255 255 255 / .09); color: #eceef4; font-size: .875rem; font-weight: 600; text-decoration: none; -webkit-backdrop-filter: blur(8px); backdrop-filter: blur(8px); transition: background-color 120ms; }
    .gk-tk-back:hover { background: rgb(255 255 255 / .15); }
    .gk-tk-back svg { flex: none; width: 1.125rem; height: 1.125rem; }
    .gk-tk-back span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    [dir="rtl"] .gk-tk-back svg { transform: scaleX(-1); }

    .gk-ticket { width: 100%; filter: drop-shadow(0 1.625rem 3.125rem rgb(var(--es-glow) / .2)); }
    .gk-ticket-a, .gk-ticket-b { background: var(--tk-card); }
    .gk-ticket-a {
        padding-bottom: 1.25rem;
        overflow: hidden;
        border-radius: 1.375rem 1.375rem 0 0;
        background: linear-gradient(180deg, var(--tk-card-top), var(--tk-card));
        -webkit-mask: radial-gradient(circle 11px at 0 100%, transparent 10.5px, #000 11px), radial-gradient(circle 11px at 100% 100%, transparent 10.5px, #000 11px);
        -webkit-mask-composite: source-in;
        mask: radial-gradient(circle 11px at 0 100%, transparent 10.5px, #000 11px), radial-gradient(circle 11px at 100% 100%, transparent 10.5px, #000 11px);
        mask-composite: intersect;
    }
    .gk-ticket-b {
        position: relative;
        border-radius: 0 0 1.375rem 1.375rem;
        -webkit-mask: radial-gradient(circle 11px at 0 0, transparent 10.5px, #000 11px), radial-gradient(circle 11px at 100% 0, transparent 10.5px, #000 11px);
        -webkit-mask-composite: source-in;
        mask: radial-gradient(circle 11px at 0 0, transparent 10.5px, #000 11px), radial-gradient(circle 11px at 100% 0, transparent 10.5px, #000 11px);
        mask-composite: intersect;
    }
    {{-- The tear. --}}
    .gk-ticket-b::before { content: ''; position: absolute; inset: 0 1.25rem auto 1.25rem; border-top: 2px dashed rgb(255 255 255 / .18); }

    .gk-tk-top { position: relative; display: grid; grid-template-columns: minmax(0, 1fr); gap: .875rem; align-items: center; padding: 1.5rem 1.25rem 1.25rem; overflow: hidden; background: linear-gradient(180deg, rgb(var(--es-glow) / .34), rgb(var(--es-glow) / .06)); border-bottom: 1px solid var(--tk-line); }
    .gk-tk-top-logo { grid-template-columns: 3rem minmax(0, 1fr); }
    .gk-tk-top img { width: 3rem; height: 3rem; border-radius: .75rem; object-fit: cover; box-shadow: 0 0 0 1px rgb(255 255 255 / .14); }
    .gk-tk-title { margin: 0; font-size: 1.375rem; font-weight: 800; line-height: 1.15; letter-spacing: -.01em; color: var(--es-glow-ink); overflow-wrap: anywhere; }
    .gk-tk-sub { display: block; margin-top: .125rem; font-size: .90625rem; color: #b3b6c7; overflow-wrap: anywhere; }
    .gk-tk-sub a { color: inherit; text-decoration: underline; text-underline-offset: 3px; }

    {{-- What the ticket's state is, said before the code. --}}
    .gk-tk-note { display: grid; gap: .625rem; padding: 1rem 1.25rem 0; }
    .gk-tk-msg { display: flex; align-items: flex-start; gap: .625rem; padding: .75rem .875rem; border: 1px solid var(--tk-line); border-radius: .75rem; background: var(--tk-well); color: var(--tk-ink); font-size: .90625rem; line-height: 1.4; }
    .gk-tk-msg svg { flex: none; width: 1.25rem; height: 1.25rem; margin-top: .0625rem; }
    .gk-tk-msg strong { font-weight: 800; }
    .gk-tk-msg-warn { border-color: rgb(252 211 77 / .5); background: rgb(146 64 14 / .34); color: #fde68a; }
    .gk-tk-msg-bad { border-color: rgb(252 165 165 / .5); background: rgb(185 28 28 / .32); color: #fecaca; }
    .gk-tk-msg-ok { border-color: rgb(134 239 172 / .4); background: rgb(22 101 52 / .32); color: #bbf7d0; }

    .gk-tk-hero { padding: .375rem 0 .125rem; text-align: center; }
    .gk-tk-check { display: inline-grid; place-items: center; width: 3.25rem; height: 3.25rem; border-radius: 999px; background: rgb(var(--es-glow) / .22); color: var(--es-glow-ink); box-shadow: 0 0 0 6px rgb(var(--es-glow) / .09); }
    .gk-tk-check svg { width: 1.75rem; height: 1.75rem; }
    .gk-tk-hero h2 { margin: .625rem 0 0; font-size: 1.75rem; font-weight: 800; letter-spacing: -.02em; line-height: 1.15; color: var(--tk-ink); }

    {{-- The code. White, because that is what a scanner reads. --}}
    .gk-tk-qr { display: block; position: relative; width: 14rem; max-width: calc(100% - 2.5rem); aspect-ratio: 1; margin: 1.25rem auto .75rem; padding: .875rem; border: 0; border-radius: 1.25rem; background: #ffffff; cursor: zoom-in; box-shadow: 0 0 0 6px rgb(var(--es-glow) / .16), 0 1rem 2.875rem rgb(var(--es-glow) / .3); }
    .gk-tk-qr img { display: block; width: 100%; height: 100%; image-rendering: pixelated; }
    .gk-tk-qr-void { cursor: default; box-shadow: none; }
    .gk-tk-qr-void img { opacity: .14; }
    .gk-tk-stamp { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-10deg); padding: .375rem .875rem; border: 3px solid #b3261e; border-radius: .5rem; background: #ffffff; color: #b3261e; font-size: 1.1875rem; font-weight: 800; letter-spacing: .04em; line-height: 1.2; text-transform: uppercase; white-space: nowrap; }
    .gk-tk-stamp-hold { border-color: #b45309; color: #b45309; }
    .gk-tk-qr-note { margin: 0; padding: 0 1.25rem; text-align: center; font-size: 1rem; font-weight: 800; color: var(--tk-ink); }
    .gk-tk-qr-note span { display: block; margin-top: .25rem; font-size: .84375rem; font-weight: 500; color: #a2a6b8; }

    .gk-tk-btn { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; min-height: 2.75rem; padding: 0 1rem; border: 1px solid rgb(255 255 255 / .13); border-radius: .625rem; background: rgb(255 255 255 / .07); color: #ffffff; font: inherit; font-size: .9375rem; font-weight: 650; line-height: 1.2; text-align: center; text-decoration: none; cursor: pointer; transition: background-color 120ms, transform 50ms; }
    .gk-tk-btn:hover { background: rgb(255 255 255 / .12); }
    .gk-tk-btn:active { transform: scale(.985); }
    .gk-tk-btn svg { flex: none; width: 1.125rem; height: 1.125rem; }
    .gk-tk-btn-fill { border-color: var(--es-accent-edge, transparent); background: var(--es-accent); color: var(--es-accent-text); }
    .gk-tk-btn-fill:hover { background: var(--es-accent); filter: brightness(1.06); }
    .gk-tk-btn-block { display: flex; width: 100%; }
    .gk-tk-btn-danger { border-color: rgb(252 165 165 / .4); color: #fecaca; }
    .gk-tk-enlarge { display: flex; width: max-content; max-width: calc(100% - 2.5rem); margin: .875rem auto 0; }

    {{-- The organizer's own words about the day, directly under the code. --}}
    .gk-tk-info { margin: 1.125rem 1.25rem 0; padding: .875rem 1rem; border: 1px solid var(--tk-line); border-radius: .875rem; background: var(--tk-well); }
    .gk-tk-label { margin: 0 0 .375rem; font-size: .75rem; font-weight: 700; letter-spacing: .09em; text-transform: uppercase; color: var(--tk-ink-3); }
    .gk-tk-info .custom-content { font-size: .875rem; line-height: 1.55; color: var(--tk-ink-2); }
    .gk-tk-info .custom-content a { color: var(--tk-ink); }

    {{-- Three things to do next. --}}
    .gk-tk-tiles { display: grid; grid-template-columns: repeat(auto-fit, minmax(0, 1fr)); gap: .5rem; padding: 1.375rem 1.25rem 0; }
    .gk-tk-tile { display: grid; justify-items: center; align-content: start; gap: .375rem; min-height: 4.25rem; padding: .625rem .25rem; border: 1px solid var(--tk-line); border-radius: .875rem; background: var(--tk-well); color: var(--tk-ink); font: inherit; font-size: .8125rem; font-weight: 700; line-height: 1.2; text-align: center; text-decoration: none; cursor: pointer; transition: background-color 120ms; }
    .gk-tk-tile:hover { background: rgb(255 255 255 / .11); }
    .gk-tk-tile svg { width: 1.375rem; height: 1.375rem; color: var(--es-glow-ink); }
    .gk-tk-menu { display: grid; gap: .375rem; margin: .5rem 1.25rem 0; padding: .5rem; border: 1px solid var(--tk-line); border-radius: .875rem; background: var(--tk-well); }
    .gk-tk-menu a { display: flex; align-items: center; min-height: 2.75rem; padding: 0 .75rem; border-radius: .625rem; color: var(--tk-ink); font-weight: 600; text-decoration: none; }
    .gk-tk-menu a:hover { background: rgb(255 255 255 / .09); }

    .gk-tk-facts { display: grid; gap: 1.125rem; margin: 0; padding: 1.5rem 1.25rem .5rem; }
    .gk-tk-fact { display: grid; grid-template-columns: 2.75rem minmax(0, 1fr); gap: .875rem; align-items: center; }
    .gk-tk-ico { display: grid; place-items: center; width: 2.75rem; height: 2.75rem; border-radius: .875rem; }
    .gk-tk-ico svg { width: 1.375rem; height: 1.375rem; }
    .gk-tk-ico-a { background: rgb(var(--es-glow) / .2); color: var(--es-glow-ink); }
    .gk-tk-ico-b { background: rgb(244 114 182 / .16); color: #f9a8d4; }
    .gk-tk-ico-c { background: rgb(52 211 153 / .16); color: #6ee7b7; }
    .gk-tk-ico-d { background: rgb(251 191 36 / .16); color: #fcd34d; }
    .gk-tk-fact dt { font-size: .75rem; font-weight: 700; letter-spacing: .09em; text-transform: uppercase; color: var(--tk-ink-3); }
    .gk-tk-fact dd { margin: .125rem 0 0; font-size: 1.03125rem; font-weight: 700; line-height: 1.35; color: var(--tk-ink); overflow-wrap: anywhere; }
    .gk-tk-fact dd small { display: block; font-size: .90625rem; font-weight: 500; color: var(--tk-ink-2); }
    .gk-tk-fact dd a { color: inherit; text-decoration: underline; text-underline-offset: 3px; }
    .gk-tk-pill { display: inline-block; margin-inline-start: .375rem; padding: .0625rem .5rem; border-radius: 999px; background: rgb(var(--es-glow) / .2); color: var(--es-glow-ink); font-size: .75rem; font-weight: 700; vertical-align: middle; }

    {{-- A section of the lower half: a pass, add-ons, answers, a payment plan. --}}
    .gk-tk-sec { margin: 0 1.25rem; padding: 1rem 0; border-top: 1px solid var(--tk-line); }
    .gk-tk-rows { display: grid; gap: .5rem; margin: 0; padding: 0; list-style: none; font-size: .90625rem; color: var(--tk-ink); }
    .gk-tk-row { display: flex; align-items: baseline; justify-content: space-between; gap: .75rem; }
    .gk-tk-row > :first-child { color: var(--tk-ink-2); }
    .gk-tk-row > :last-child { font-weight: 700; text-align: end; }
    .gk-tk-good { color: #6ee7b7; }
    .gk-tk-quiet { font-size: .8125rem; color: var(--tk-ink-3); }
    .gk-tk-book { display: flex; align-items: center; justify-content: space-between; gap: .5rem; padding: .5rem .75rem; border-radius: .625rem; background: var(--tk-well); font-size: .84375rem; color: var(--tk-ink); }
    .gk-tk-book small { display: block; font-size: .75rem; color: var(--tk-ink-3); }
    .gk-tk-book button { min-height: 2.25rem; padding: 0 .75rem; border: 0; border-radius: .5rem; background: transparent; color: #fca5a5; font: inherit; font-size: .8125rem; font-weight: 700; cursor: pointer; }
    .gk-tk-book .gk-tk-book-go { background: var(--es-accent); color: var(--es-accent-text); }
    .gk-tk-book .gk-tk-book-warn { color: #fcd34d; }

    .gk-tk-actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(9rem, 1fr)); gap: .5rem; padding: .875rem 1.25rem 1.125rem; }
    .gk-tk-confirm { display: grid; gap: .625rem; margin: 0 1.25rem 1.125rem; padding: .875rem 1rem; border: 1px solid rgb(252 165 165 / .4); border-radius: .875rem; background: rgb(185 28 28 / .2); color: var(--tk-ink); }
    .gk-tk-confirm p { margin: 0; font-weight: 700; }
    .gk-tk-confirm div { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; }
    .gk-tk-foot { display: grid; margin: 0 1.25rem; padding: .375rem 0 .625rem; border-top: 1px solid rgb(255 255 255 / .09); font-size: .90625rem; }
    .gk-tk-foot a, .gk-tk-foot span { display: inline-flex; align-items: center; justify-self: start; min-height: 2.75rem; color: var(--tk-ink-2); font-weight: 500; overflow-wrap: anywhere; }
    .gk-tk-foot a { text-decoration: underline; text-underline-offset: 3px; text-decoration-color: rgb(255 255 255 / .35); }

    {{-- The ticket's sibling pages (an order of several events, a payment plan): the same ground
         and the same card, without the tear. --}}
    .gk-tk-card { width: 100%; overflow: hidden; border-radius: 1.375rem; background: linear-gradient(180deg, var(--tk-card-top), var(--tk-card)); filter: drop-shadow(0 1.625rem 3.125rem rgb(var(--es-glow) / .2)); }
    .gk-tk-body { display: grid; gap: .875rem; padding: 1.125rem 1.25rem 1.25rem; }
    .gk-tk-body > p { margin: 0; }
    .gk-tk-legs { display: grid; gap: .625rem; margin: 0; padding: 0; list-style: none; }
    .gk-tk-leg { border: 1px solid var(--tk-line); border-radius: .875rem; background: var(--tk-well); }
    .gk-tk-leg-off { opacity: .6; }
    .gk-tk-leg-main { display: flex; align-items: center; justify-content: space-between; gap: .75rem; min-height: 4rem; padding: .75rem 1rem; color: var(--tk-ink); text-decoration: none; }
    a.gk-tk-leg-main:hover { background: rgb(255 255 255 / .05); }
    .gk-tk-leg-main b { display: block; font-size: 1rem; font-weight: 700; line-height: 1.3; overflow-wrap: anywhere; }
    .gk-tk-leg-main small { display: block; margin-top: .125rem; font-size: .84375rem; color: var(--tk-ink-2); }
    .gk-tk-leg-go { display: inline-flex; align-items: center; gap: .25rem; flex: none; font-size: .875rem; font-weight: 700; color: var(--es-glow-ink); white-space: nowrap; }
    .gk-tk-leg-go svg { width: 1rem; height: 1rem; }
    [dir="rtl"] .gk-tk-leg-go svg { transform: scaleX(-1); }
    .gk-tk-leg-extra { padding: 0 1rem 1rem; }

    {{-- A class that sets display outranks the hidden attribute, so the blocks a button opens
         say it themselves. --}}
    .gk-tk-menu[hidden], .gk-tk-confirm[hidden], .gk-tk-hero[hidden] { display: none; }

    {{-- The payment plan panel is its own partial and asks for this class by name. --}}
    .gk-tkpage .glass { background: var(--tk-well); border: 1px solid var(--tk-line); }

    {{-- The door: a white screen with the code as large as the screen allows. --}}
    .gk-door { position: fixed; inset: 0; z-index: 60; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .875rem; padding: 4.5rem 1.25rem 1.5rem; overflow-y: auto; background: #ffffff; color: #111318; text-align: center; font-family: 'Manrope', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
    .gk-door[hidden] { display: none; }
    .gk-door :focus-visible { outline: 2px solid #111318; outline-offset: 2px; }
    .gk-door-close { position: absolute; top: .875rem; inset-inline-end: .875rem; min-width: 5.5rem; height: 3.5rem; border: 1px solid #d5d8de; border-radius: .875rem; background: #ffffff; color: #111318; font: inherit; font-size: 1.0625rem; font-weight: 800; cursor: pointer; }
    .gk-door-code { width: min(100%, 60vh, 27.5rem); aspect-ratio: 1; }
    .gk-door-code img { display: block; width: 100%; height: 100%; image-rendering: pixelated; }
    .gk-door-who { margin: 0; font-size: 1.25rem; line-height: 1.35; color: #111318; }
    .gk-door-who b { display: block; font-size: 1.5rem; font-weight: 800; overflow-wrap: anywhere; }
    .gk-door-hint { margin: 0; font-size: .9375rem; color: #555b66; }
    .gk-door-save { display: inline-flex; align-items: center; min-height: 3rem; padding: 0 1.125rem; border: 1px solid #d5d8de; border-radius: .75rem; background: #f3f4f6; color: #111318; font-size: .96875rem; font-weight: 700; text-decoration: none; }

    {{-- Arriving, once: the ticket rises, a light crosses its top, the check draws itself. --}}
    @media (prefers-reduced-motion: no-preference) {
        .gk-ticket-fresh { animation: gk-tk-rise .5s cubic-bezier(.2, .8, .2, 1) both; }
        .gk-ticket-fresh .gk-tk-top::after { content: ''; position: absolute; inset: 0; background: linear-gradient(105deg, transparent 36%, rgb(255 255 255 / .3) 50%, transparent 64%); transform: translateX(-120%); animation: gk-tk-sheen 1s ease-out .4s 1 both; }
        .gk-ticket-fresh .gk-tk-check path { stroke-dasharray: 24; stroke-dashoffset: 24; animation: gk-tk-draw .32s ease-out .55s 1 forwards; }
        .gk-door:not([hidden]) { animation: gk-tk-door .16s ease-out both; }
    }
    @keyframes gk-tk-rise { from { opacity: 0; transform: translateY(1rem) scale(.985); } }
    @keyframes gk-tk-sheen { to { transform: translateX(120%); } }
    @keyframes gk-tk-draw { to { stroke-dashoffset: 0; } }
    @keyframes gk-tk-door { from { opacity: 0; } }

    {{-- On paper: black on white, no light, and nothing that is only a button. --}}
    @media print {
        body { background: #ffffff; }
        .gk-tkpage { --tk-ink: #000000; --tk-ink-2: #1f2937; --tk-ink-3: #4b5563; --tk-line: #cbd5e1; --tk-well: #ffffff; --tk-card: #ffffff; --tk-card-top: #ffffff; --tk-ground: #ffffff; min-height: 0; padding: 0; }
        .gk-tkpage::before { display: none; }
        .gk-ticket { filter: none; animation: none; border: 1px solid #94a3b8; border-radius: 1rem; }
        .gk-tk-card { filter: none; background: #ffffff; border: 1px solid #94a3b8; border-radius: 1rem; }
        .gk-ticket-a, .gk-ticket-b { background: #ffffff; -webkit-mask: none; mask: none; border-radius: 0; }
        .gk-ticket-b::before { border-top-color: #94a3b8; }
        .gk-tk-top { background: #ffffff; }
        .gk-tk-title, .gk-tk-sub, .gk-tk-qr-note span { color: #000000; }
        .gk-tk-qr { box-shadow: none; border: 1px solid #94a3b8; }
        .gk-tk-ico { background: #ffffff; color: #000000; border: 1px solid #cbd5e1; }
        .gk-tk-msg, .gk-tk-pill { background: #ffffff; color: #000000; border: 1px solid #94a3b8; }
        .gk-tk-good { color: #000000; }
        .gk-tk-back, .gk-tk-btn, .gk-tk-tiles, .gk-tk-menu, .gk-tk-actions, .gk-tk-confirm, .gk-tk-book button, .gk-door, .gk-tk-noprint { display: none; }
    }
</style>
