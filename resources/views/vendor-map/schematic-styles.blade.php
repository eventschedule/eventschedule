{{-- The schematic map's look (vendor-map/schematic, SchematicMap.vue). Its colours are variables:
     the entrance sign sets the light print set, the visitors' page the guest stage's tokens.
     A zone is tinted by its area's colour (--zc) through color-mix, so any colour an organizer
     picks reads on both. --}}
<style {!! nonce_attr() !!}>
    .vms { --vms-ink: #0f172a; --vms-ink2: #475569; --vms-cell: #fff; --vms-tint: 16%; --vms-edge: 45%; --vms-text-mix: 62%; --vms-text-base: #0f172a;
        --vms-gap: .5rem; --vms-cell-h: 2.25rem; --vms-cell-font: .9375rem;
        display: flex; flex-direction: column; gap: var(--vms-zone-gap, 1rem); color: var(--vms-ink); }
    .vms-cols { display: grid; gap: var(--vms-zone-gap, 1rem); align-items: start; }
    .vms-col { display: flex; flex-direction: column; gap: var(--vms-zone-gap, 1rem); min-width: 0; }
    .vms-zone { min-width: 0; }
    .vms-zh { display: flex; flex-wrap: wrap; align-items: baseline; column-gap: .5rem; row-gap: .125rem; margin-bottom: .4rem; }
    .vms-zh b { font-size: var(--vms-head-font, .8125rem); font-weight: 800; color: var(--vms-ink); }
    .vms-zh span { font-size: var(--vms-meta-font, .75rem); font-weight: 600; color: var(--vms-ink2); }
    .vms-range { direction: ltr; unicode-bidi: isolate; }
    .vms-range + .vms-sells::before { content: '· '; }
    .vms-grid { display: grid; gap: var(--vms-gap); padding: var(--vms-pad, .55rem); border-radius: var(--vms-radius, .7rem);
        background: color-mix(in srgb, var(--zc) var(--vms-tint), var(--vms-bg, #fff));
        border: 1px solid color-mix(in srgb, var(--zc) var(--vms-edge), var(--vms-bg, #fff)); }
    .vms-c { box-sizing: border-box; display: grid; place-items: center; min-width: 0; height: var(--vms-cell-h);
        border-radius: var(--vms-cell-radius, .4rem); border: 1.5px solid var(--zc); background: var(--vms-cell);
        color: color-mix(in srgb, var(--zc) var(--vms-text-mix), var(--vms-text-base)); font-size: var(--vms-cell-font); font-weight: 800;
        font-variant-numeric: tabular-nums; line-height: 1; direction: ltr; }
    .vms-c.is-gap { visibility: hidden; }
    .vms-c.is-free { border-style: dashed; opacity: .55; }
    .vms-c.is-absent { background: repeating-linear-gradient(45deg, transparent 0 4px, color-mix(in srgb, var(--vms-ink) 14%, transparent) 4px 5.5px), var(--vms-cell); opacity: .6; }
    .vms-foot { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .5rem .75rem; padding-top: .25rem; }
    .vms-here { display: flex; align-items: center; gap: .4rem; margin: 0; font-size: var(--vms-meta-font, .75rem); color: var(--vms-ink2); }
    .vms-here i { width: .75rem; height: .75rem; border-radius: 999px; background: #dc2626; box-shadow: 0 0 0 3px color-mix(in srgb, #dc2626 25%, transparent); }
    .vms-here b { color: #dc2626; font-weight: 800; }
    .vms-amen { display: flex; flex-wrap: wrap; gap: .4rem; margin: 0; padding: 0; list-style: none; }
    .vms-amen li { padding: .2rem .55rem; border: 1px solid color-mix(in srgb, var(--vms-ink) 22%, transparent); border-radius: .4rem; font-size: var(--vms-meta-font, .75rem); font-weight: 600; color: var(--vms-ink2); }

    /* A phone, a big market: the areas first (AreaMap.vue), then one area close up. On the page's
       --gk-* tokens; the vendor link maps its own onto them. */
    .vmo-hint { margin: 0 0 .625rem; font-size: .875rem; color: var(--gk-ink-2); }
    .vmo-board .vms-cols, .vmo-board { gap: .625rem; }
    .vmo-card { display: flex; flex-direction: column; gap: .5rem; width: 100%; margin-bottom: .625rem; padding: .75rem .8125rem; border-radius: 1rem; border: 1.5px solid color-mix(in srgb, var(--zc) 45%, var(--gk-stage)); background: color-mix(in srgb, var(--zc) 12%, var(--gk-stage)); color: var(--gk-ink); font: inherit; text-align: start; cursor: pointer; transition: transform .12s, opacity .15s, border-color .15s; }
    .vmo-card:active { transform: scale(.98); }
    .vmo-card:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 2px; }
    .vmo-card.is-dim { opacity: .4; }
    .vmo-card.is-marked { border-color: var(--gk-ink); }
    .vmo-card-head { display: flex; align-items: baseline; flex-wrap: wrap; gap: .25rem .5rem; }
    .vmo-card-head b { font-size: 1rem; font-weight: 700; }
    .vmo-card-head .vms-range { font-size: .8125rem; font-weight: 600; color: var(--gk-ink-2); direction: ltr; unicode-bidi: isolate; }
    .vmo-dots { display: grid; gap: .1875rem; align-self: flex-start; }
    .vmo-dots i { width: .5rem; height: .5rem; border-radius: .125rem; border: 1px solid color-mix(in srgb, var(--zc) 60%, var(--gk-stage)); }
    .vmo-dots i.is-present { background: var(--zc); border-color: var(--zc); }
    .vmo-dots i.is-absent { opacity: .45; }
    .vmo-dots i.is-gap { border: 0; }
    .vmo-dots i.is-hit { background: var(--gk-ink); border-color: var(--gk-ink); }
    .vmo-dots i.is-on { background: var(--gk-ink); border-color: var(--gk-ink); box-shadow: 0 0 0 2px var(--gk-stage), 0 0 0 3.5px var(--gk-ink); }
    .vmo-card-foot { display: flex; align-items: center; gap: .5rem; font-size: .8125rem; color: var(--gk-ink-2); }
    .vmo-card-foot .vmo-go { margin-inline-start: auto; color: var(--gk-ink-2); }
    .vmo-badge { display: inline-flex; align-items: center; justify-content: center; min-width: 1.25rem; height: 1.25rem; padding: 0 .3rem; border-radius: 999px; background: var(--gk-ink); color: var(--gk-stage); font-size: .75rem; font-weight: 700; }
    .vmo-bar { display: flex; align-items: center; justify-content: space-between; gap: .75rem; margin-bottom: .5rem; }
    .vmo-back { display: inline-flex; align-items: center; gap: .25rem; min-height: 2.5rem; padding: 0; border: 0; background: none; color: var(--gk-ink); font: inherit; font-weight: 600; cursor: pointer; }
    .vmo-zoom { display: inline-flex; border: 1px solid var(--gk-line); border-radius: 999px; overflow: hidden; }
    .vmo-zoom button { width: 2.75rem; height: 2.5rem; border: 0; background: var(--gk-solid); color: var(--gk-ink); font-size: 1.25rem; cursor: pointer; }
    .vmo-zoom button + button { border-inline-start: 1px solid var(--gk-line); }
    .vmo-zoom button:disabled { color: var(--gk-ink-3); cursor: default; }
    .vmo-tabs { display: flex; gap: .4rem; overflow-x: auto; margin: 0 -1rem .625rem; padding: 0 1rem .125rem; scrollbar-width: none; }
    .vmo-tabs::-webkit-scrollbar { display: none; }
    .vmo-tab { flex: 0 0 auto; display: inline-flex; align-items: center; gap: .4rem; min-height: 2.25rem; padding: 0 .8rem; border-radius: 999px; border: 1px solid var(--gk-line); background: var(--gk-solid); color: var(--gk-ink); font: inherit; font-size: .875rem; font-weight: 600; cursor: pointer; white-space: nowrap; }
    .vmo-tab i { width: .5rem; height: .5rem; border-radius: 999px; background: var(--zc); }
    .vmo-tab.is-on { background: var(--gk-ink); border-color: var(--gk-ink); color: var(--gk-stage); }
    .vmo-tab.is-on .vmo-badge { background: var(--gk-stage); color: var(--gk-ink); }
    .vmo-view { max-height: 60vh; overflow: auto; overscroll-behavior: contain; border-radius: 1rem; border: 1px solid var(--gk-line); background: var(--gk-stage); -webkit-overflow-scrolling: touch; }
    .vmo-inner { padding: .75rem; box-sizing: border-box; }
    /* Doubled class: wins over the page's own map sizes (.vm-mapwrap .vms, .vmc-schematic .vms). */
    .vmo-inner.vmo-inner .vms { --vms-cell-h: calc(3.25rem * var(--z, 1)); --vms-cell-font: calc(1.125rem * var(--z, 1)); --vms-gap: calc(.5rem * var(--z, 1)); }
    .vmo-inner.vmo-inner .vms-sells { display: inline; }
    .vmo-view { touch-action: pan-x pan-y; }
    /* The plan of areas: each area a tile where it is, the landmarks quieter around them. */
    .vmo-plan { position: relative; width: 100%; margin: 0 0 .5rem; border-radius: 1rem; background: color-mix(in srgb, var(--gk-ink) 4%, var(--gk-stage)); }
    .vmo-tile { position: absolute; margin: 0; padding: .4rem .5rem; gap: .25rem; overflow: hidden; container-type: size; border-radius: .75rem; justify-content: space-between; }
    .vmo-tile .vmo-card-head b { font-size: .875rem; line-height: 1.15; }
    .vmo-tile .vmo-card-head .vms-range { font-size: .75rem; }
    .vmo-tile .vmo-card-foot { font-size: .75rem; }
    .vmo-tile .vmo-dots { gap: .125rem; }
    .vmo-tile .vmo-dots i { width: .375rem; height: .375rem; }
    @container (max-height: 7.5rem) { .vmo-tile .vmo-dots { display: none; } }
    @container (max-width: 7rem) { .vmo-tile .vmo-dots { display: none; } }
    @container (max-width: 6.5rem) { .vmo-tile .vmo-card-head b { font-size: .8125rem; } .vmo-tile .vmo-card-foot { font-size: .6875rem; } }
    .vmo-tile.is-tall { align-items: center; justify-content: center; padding: .375rem .25rem; }
    .vmo-tile.is-tall .vmo-card-head { writing-mode: vertical-rl; flex-wrap: nowrap; white-space: nowrap; }
    .vmo-tile.is-tall .vmo-dots, .vmo-tile.is-tall .vmo-card-foot { display: none; }
    @container (max-height: 4.25rem) { .vmo-tile .vmo-card-head .vms-range { display: none; } }
    @container (max-width: 6rem) { .vmo-tile .vmo-card-foot .vmo-go { display: none; } }
    .vmo-place { position: absolute; display: flex; align-items: center; justify-content: center; padding: .125rem; border-radius: .5rem; border: 1px dashed color-mix(in srgb, var(--gk-ink) 30%, var(--gk-stage)); color: var(--gk-ink-2); font-size: .6875rem; font-weight: 600; text-align: center; line-height: 1.1; overflow: hidden; }
    .vmo-place.is-parking { background: color-mix(in srgb, var(--gk-ink) 6%, var(--gk-stage)); }
    .vmo-here { position: absolute; width: .875rem; height: .875rem; margin: -.4375rem 0 0 -.4375rem; border-radius: 999px; background: #dc2626; box-shadow: 0 0 0 3px color-mix(in srgb, #dc2626 30%, transparent); }
    .vmo-here-legend { margin: 0 0 .25rem; }
    .vmo-plan { overflow: hidden; }
    .vmo-picture { position: absolute; object-fit: fill; max-width: none; pointer-events: none; }
    .vmo-plan.has-picture .vmo-tile { background: color-mix(in srgb, var(--zc) 28%, color-mix(in srgb, var(--gk-stage) 86%, transparent)); border-width: 2px; border-color: var(--zc); box-shadow: 0 2px 10px rgba(0, 0, 0, .25); -webkit-backdrop-filter: blur(2px); backdrop-filter: blur(2px); }
    .vmo-plan.has-picture .vmo-place { background: color-mix(in srgb, var(--gk-stage) 88%, transparent); border-style: solid; }
    @media (prefers-reduced-motion: reduce) { .vmo-card { transition: none; } }
</style>
