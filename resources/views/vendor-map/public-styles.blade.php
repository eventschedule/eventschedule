{{-- The visitors' map page (vendor-map/public): the same classes for the server's markup and for
     PublicMapApp.vue, so nothing moves when the app takes over. On the guest stage's tokens
     (--gk-*, --es-accent*), light and dark; logical properties throughout, so right to left needs
     nothing of its own; rem sizes, so the accessibility widget's text steps scale it.

     A phone: the map (the market as blocks, whole at a phone's width) under the header, then the
     search and the chips, which stay at the top while the list scrolls.
     From 64rem: the list is a panel at the start side, the map stands beside it to the window's
     height. The list comes first in the document either way; the phone lifts the map with order. --}}
<style {!! nonce_attr() !!}>
    .gk-stage.gk-p-vmap { padding: 0 0 2rem; }
    .vm-page { display: flex; flex-direction: column; }
    .vm-head { order: 0; display: flex; flex-direction: column; gap: .375rem; padding: .875rem 1rem .875rem; }
    .vm-mapwrap { order: 1; padding: .25rem 1rem 1rem; background: var(--gk-stage); }
    .vm-side { order: 2; min-width: 0; padding: 0 1rem; }

    .vm-back { display: inline-flex; align-items: center; gap: .375rem; align-self: flex-start; min-height: 2.25rem; font-size: .9375rem; font-weight: 500; color: var(--gk-ink-2); text-decoration: none; }
    .vm-back:hover { color: var(--gk-ink); text-decoration: underline; text-underline-offset: 3px; }
    .vm-back:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 2px; border-radius: .25rem; }
    .vm-title { margin: 0; font-size: clamp(1.625rem, 1.2rem + 2vw, 2.5rem); font-weight: 800; line-height: 1.1; letter-spacing: -.01em; color: var(--gk-ink); overflow-wrap: anywhere; }
    [dir="rtl"] .vm-title { letter-spacing: 0; }
    .vm-closed { margin-top: .5rem; }
    .vm-closed p { margin: 0; }
    .vm-closed-title { font-weight: 700; }

    .vm-sheethead { position: sticky; top: 0; z-index: 5; display: flex; flex-direction: column; gap: .625rem; margin: 0 -1rem; padding: .875rem 1rem .625rem; background: var(--gk-stage); border-bottom: 1px solid var(--gk-line); }
    .vm-search { position: relative; display: flex; align-items: center; }
    .vm-search > svg { position: absolute; inset-inline-start: .9375rem; color: var(--gk-ink-3); pointer-events: none; }
    .vm-search .vm-search-input { box-sizing: border-box; width: 100%; min-height: 3rem; margin: 0; padding: 0 2.875rem; border: 1px solid var(--gk-line); border-radius: 999px; background: var(--gk-solid); color: var(--gk-ink); font: inherit; font-size: 1rem; box-shadow: none; -webkit-appearance: none; appearance: none; }
    .vm-search .vm-search-input::placeholder { color: var(--gk-ink-3); opacity: 1; }
    .vm-search .vm-search-input:focus { border-color: var(--es-accent-readable); outline: none; box-shadow: 0 0 0 1px var(--es-accent-readable); }
    .vm-search .vm-search-input::-webkit-search-cancel-button { -webkit-appearance: none; appearance: none; }
    .vm-search-clear { position: absolute; inset-inline-end: .375rem; display: grid; place-items: center; width: 2.25rem; height: 2.25rem; padding: 0; border: 0; border-radius: 999px; background: transparent; color: var(--gk-ink-2); cursor: pointer; }
    .vm-search-clear:hover { background: var(--gk-well); }
    .vm-search-clear:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 0; }
    .vm-chips { display: flex; gap: .375rem; overflow-x: auto; margin: 0 -1rem; padding: .125rem 1rem; scrollbar-width: none; }
    .vm-chips::-webkit-scrollbar { display: none; }
    .vm-chips .vm-chip { flex: none; min-height: 2.25rem; padding: 0 .875rem; border-color: var(--gk-line); background: var(--gk-solid); color: var(--gk-ink); font-family: inherit; font-size: .875rem; cursor: pointer; transition: background-color var(--gk-swap); }
    .vm-chips .vm-chip svg { flex: none; }
    .vm-chips .vm-chip[aria-pressed="true"] { background: var(--gk-ink); border-color: var(--gk-ink); color: var(--gk-solid); }
    .vm-chips .vm-chip:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 2px; }

    .vm-sr { position: absolute; width: 1px; height: 1px; margin: -1px; padding: 0; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; border: 0; }

    .vm-listtitle { margin: 1.125rem 0 .25rem; font-size: 1.0625rem; font-weight: 750; color: var(--gk-ink); }
    .vm-group + .vm-group { margin-top: .5rem; }
    .vm-group-name { display: flex; align-items: center; gap: .5rem; margin: .875rem 0 .25rem; font-size: .875rem; font-weight: 650; color: var(--gk-ink-2); }
    .vm-dot { flex: none; display: inline-block; width: .625rem; height: .625rem; border-radius: 999px; background: var(--area, var(--gk-ink-3)); box-shadow: 0 0 0 1px color-mix(in srgb, var(--gk-ink) 18%, transparent); }
    .vm-list { margin: 0; padding: 0; list-style: none; }
    .vm-row { box-sizing: border-box; display: flex; align-items: center; gap: .75rem; width: 100%; min-height: 3.75rem; margin: 0; padding: .5rem; border: 0; border-radius: .875rem; background: none; color: var(--gk-ink); font: inherit; text-align: start; cursor: pointer; transition: background-color var(--gk-swap); }
    div.vm-row { cursor: default; }
    .vm-row:hover { background: var(--gk-well); }
    .vm-row.is-on { background: var(--es-accent-tint); }
    .vm-row:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: -2px; }
    .vm-row-num { box-sizing: border-box; flex: none; display: grid; place-items: center; min-width: 2.5rem; height: 2.5rem; padding: 0 .375rem; border: 1px solid color-mix(in srgb, var(--area, #9ca3af) 65%, transparent); border-radius: .625rem; background: color-mix(in srgb, var(--area, #9ca3af) 18%, var(--gk-solid)); color: var(--gk-ink); font-size: .9375rem; font-weight: 750; font-variant-numeric: tabular-nums; }
    .vm-row-pic { box-sizing: border-box; flex: none; width: 2.5rem; height: 2.5rem; border-radius: 999px; object-fit: cover; background: var(--gk-well); }
    .vm-row-initials { display: grid; place-items: center; font-size: .8125rem; font-weight: 700; color: var(--gk-ink-2); }
    .vm-row-text { display: flex; flex: 1 1 auto; flex-direction: column; gap: .0625rem; min-width: 0; }
    .vm-row-name { overflow: hidden; font-size: 1rem; font-weight: 650; line-height: 1.3; text-overflow: ellipsis; white-space: nowrap; }
    .vm-row-link { color: inherit; text-decoration: underline; text-decoration-color: var(--gk-line); text-underline-offset: 3px; }
    .vm-row-sub { overflow: hidden; font-size: .875rem; line-height: 1.35; color: var(--gk-ink-2); text-overflow: ellipsis; white-space: nowrap; }
    .vm-row.is-absent { color: var(--gk-ink-2); }
    .vm-row.is-absent .vm-row-num { border-color: var(--gk-line); background: repeating-linear-gradient(45deg, transparent 0 4px, color-mix(in srgb, var(--gk-ink) 16%, transparent) 4px 5.5px), var(--gk-well); color: var(--gk-ink-2); }
    .vm-row.is-absent .vm-row-pic { opacity: .55; filter: grayscale(1); }
    .vm-row-absent { font-weight: 600; }

    .vm-card { position: relative; overflow: hidden; margin: 1rem 0 .25rem; border: 1px solid var(--gk-card-edge); border-radius: 1.25rem; background: var(--gk-stage-raised); color: var(--gk-ink); scroll-margin-top: 1rem; }
    :where(.dark) .vm-card { border-color: transparent; }
    .vm-card-close { position: absolute; z-index: 1; top: .5rem; inset-inline-end: .5rem; background: var(--gk-solid); box-shadow: 0 0 0 1px var(--gk-line); }
    .vm-card-photo { display: block; width: 100%; height: 12.5rem; object-fit: cover; background: var(--gk-well); }
    .vm-card.is-absent .vm-card-photo { filter: grayscale(1); opacity: .7; }
    .vm-card-body { display: flex; flex-direction: column; gap: .625rem; padding: 1rem 1.125rem 1.125rem; }
    .vm-card-spot { display: flex; flex-wrap: wrap; align-items: center; gap: .375rem; margin: 0; padding-inline-end: 2.75rem; font-size: .875rem; font-weight: 650; color: var(--gk-ink-2); }
    .vm-card-name { margin: 0; font-size: 1.375rem; font-weight: 800; line-height: 1.2; overflow-wrap: anywhere; }
    .vm-card-name:focus { outline: none; }
    .vm-card-absent { display: flex; align-items: center; gap: .5rem; margin: 0; padding: .5rem .75rem; border: 1px solid var(--gk-warn-line); border-radius: .75rem; background: var(--gk-warn-bg); color: var(--gk-warn); font-size: .9375rem; font-weight: 650; }
    .vm-card-cats { display: flex; flex-wrap: wrap; gap: .375rem; margin: 0; padding: 0; list-style: none; }
    .vm-card-cats .gk-chip { min-height: 1.875rem; padding: 0 .75rem; font-size: .8125rem; }
    .vm-card-sells { margin: 0; font-size: 1rem; line-height: 1.5; color: var(--gk-ink-2); }
    .vm-card-link { align-self: flex-start; margin-top: .25rem; }

    .vm-none, .vm-empty { display: flex; flex-direction: column; align-items: center; gap: .75rem; padding: 2.5rem 1rem; text-align: center; color: var(--gk-ink-2); }
    .vm-none p, .vm-empty p { margin: 0; max-width: 22rem; font-size: 1rem; line-height: 1.5; }
    .vm-empty svg { color: var(--gk-ink-3); }

    {{-- The market as blocks (vendor-map/schematic-styles), on the guest stage's colours. --}}
    .vm-mapwrap .vms { --vms-ink: var(--gk-ink); --vms-ink2: var(--gk-ink-2); --vms-bg: var(--gk-stage); --vms-tint: 10%; --vms-edge: 35%;
        --vms-cell: color-mix(in srgb, var(--zc) 22%, var(--gk-stage)); --vms-text-base: var(--gk-ink); --vms-text-mix: 0%;
        --vms-cell-h: clamp(2.25rem, 9.5vw, 2.75rem); --vms-cell-font: 1rem; --vms-gap: .45rem; --vms-zone-gap: .9rem; --vms-pad: 0; }
    .vm-mapwrap .vms-grid { background: none; border: 0; }
    .vm-mapwrap .vms-c { border-color: color-mix(in srgb, var(--zc) 55%, var(--gk-stage)); font-family: inherit; padding: 0; transition: background-color .15s, opacity .15s; }
    .vm-mapwrap button.vms-c { cursor: pointer; }
    .vm-mapwrap button.vms-c:hover { border-color: var(--zc); }
    .vm-mapwrap .vms-c.is-on { background: var(--gk-ink); border-color: var(--gk-ink); color: var(--gk-stage); }
    .vm-mapwrap .vms-c.is-dim { opacity: .22; }
    .vm-mapwrap .vms-c:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 2px; }
    .vm-mapwrap .vms-foot { padding-top: .5rem; }
    .vm-mapwrap .vms-sells { display: none; }
    .vm-mapwrap .vmc { min-height: 0; }

    @media (min-width: 64rem) {
        .gk-stage.gk-p-vmap { padding: 1.25rem clamp(1rem, 3vw, 2.5rem) 3rem; }
        .vm-page { display: grid; grid-template-columns: 24rem minmax(0, 1fr); grid-template-rows: auto 1fr; column-gap: 1.5rem; max-width: 96rem; margin: 0 auto; }
        {{-- The header heads the list's column, so the map stands the window's full height
             from the first view, its zoom buttons in sight. --}}
        .vm-head { grid-column: 1; grid-row: 1; padding: 0 0 1rem; }
        .vm-title { font-size: 2rem; }
        .vm-side { grid-column: 1; grid-row: 2; padding: 0; }
        .vm-mapwrap { grid-column: 2; grid-row: 1 / span 2; align-self: start; position: sticky; top: 1rem; padding: 1.5rem; border: 1px solid var(--gk-line); border-radius: 1.25rem; }
        .vm-mapwrap .vms { --vms-cell-h: 3rem; --vms-cell-font: 1.0625rem; --vms-gap: .55rem; --vms-zone-gap: 1.25rem; }
        .vm-mapwrap .vms-sells { display: inline; }
        .vm-sheethead { top: 0; margin: 0; padding: .25rem 0 .75rem; border-bottom: 0; }
        .vm-chips { margin: 0; padding: .125rem 0; flex-wrap: wrap; overflow: visible; }
        .vm-card { margin-top: .25rem; }
    }

    @media (prefers-reduced-motion: reduce) {
        .vm-row, .vm-chips .vm-chip, .vm-mapwrap .vms-c { transition: none; }
    }

    /* Before the app starts: a big market's phone overview (vendor-map/area-plan), the whole map
       from a laptop up, as PublicMapApp.vue chooses. */
    .vm-ssr-plan { display: none; }
    @media (max-width: 63.9375rem) {
        .vm-mapwrap.is-layered .vm-ssr-plan { display: block; }
        .vm-mapwrap.is-layered .vm-ssr-plan + .vm-ssr-full { display: none; }
    }
</style>
