{{-- The venue map on a schedule's guest page (resources/js/components/VenueMap.vue): its band,
     its panel, its full-window sheet, and what is drawn inside Leaflet's markers.

     A partial of its own, not part of partials/guest-kit-styles: the kit holds single-class rules
     only, and Leaflet's own rule for images in the marker pane (width: auto; padding: 0) outranks
     a single class, so everything drawn inside a pin is addressed through the map's class here.
     Colours are the kit's tokens (--gk-*) and the schedule's own (--es-accent*); the four below are
     the map's alone. Sizes in rem, so the accessibility widget's text-size steps scale them.

     The sheet (#gp-map-sheet) is teleported to the body: the band sits inside a panel with a
     backdrop-filter and a relative z-10 container, either of which would un-fix it. It is above
     the cookie notice (z-50) and below the lightbox and the follow window. --}}
<style {!! nonce_attr() !!}>
    body { --gk-map-ground: #e8ecef; --gk-map-dot: rgb(17 24 39 / .09); --gk-pin-ring: #ffffff; --gk-map-halo: #f6f7f8; --gk-tile-filter: none; }
    :where(.dark) body { --gk-map-ground: #20252a; --gk-map-dot: rgb(255 255 255 / .08); --gk-pin-ring: #f3f4f6; --gk-map-halo: #171b1f; --gk-tile-filter: invert(1) hue-rotate(180deg) brightness(.92) contrast(.88) saturate(.7); }
    {{-- The column the band stands in. Beside the month it lines up with the calendar inside its
         panel (the wrapper's own padding); in the list view the event cards run the full column,
         so the band does too. The first rule is a saved choice before the list's app has
         started, the second is the list view once it has (it marks #gp-calendar). --}}
    html[data-es-view="list"] [data-map-wrap] { padding-inline: 0; }
    [data-map-wrap]:has(~ #gp-events .calendar-panel-border-transparent) { padding-inline: 0; }
    {{-- The room the band will take, kept from the first paint so the list below does not move
         when the component arrives; given back once it has (venue-map-boot.js). --}}
    .gk-map-host { min-height: 3.6875rem; }
    .gk-map-host.is-ask { min-height: 6.25rem; }
    @media (min-width: 40rem) { .gk-map-host.is-ask { min-height: 4.875rem; } }
    @media (min-width: 64rem) { .gk-map-host.is-ask { min-height: 3.6875rem; } }
    .gk-map-host.is-mounted { min-height: 0; }
    .gk-map { overflow: hidden; }
    .gk-map-band { display: grid; grid-template-columns: auto minmax(0, 1fr) auto auto; grid-template-areas: "icon title stack acts" "sub sub sub sub"; align-items: center; column-gap: .75rem; padding: .625rem .75rem .625rem 1rem; transition: background-color var(--gk-swap); }
    [dir="rtl"] .gk-map-band { padding: .625rem 1rem .625rem .75rem; }
    @media (min-width: 40rem) { .gk-map-band { grid-template-areas: "icon title stack acts" "icon sub stack acts"; } }
    .gk-map-band:not(.is-open):not(.is-ask) { cursor: pointer; }
    .gk-map-band:not(.is-open):not(.is-ask):hover { background: color-mix(in srgb, var(--gk-well) 55%, transparent); }
    .gk-map-icon { grid-area: icon; display: grid; place-items: center; width: 2.25rem; height: 2.25rem; border-radius: 999px; background: var(--es-accent-tint); color: var(--es-accent-readable); }
    .gk-map-icon svg { width: 1.25rem; height: 1.25rem; }
    .gk-map-title { grid-area: title; margin: 0; font-size: 1.0625rem; font-weight: 700; line-height: 1.25; }
    .gk-map-sub { grid-area: sub; display: none; margin: 0; font-size: .84375rem; line-height: 1.35; color: var(--gk-ink-3); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    @media (min-width: 40rem) { .gk-map-sub { display: block; } }
    .gk-map-sub-ask { display: block; margin-top: .5rem; white-space: normal; }
    @media (min-width: 40rem) { .gk-map-sub-ask { margin-top: 0; } }
    .gk-map-plain { padding: 0; border: 0; background: none; font-size: inherit; cursor: pointer; white-space: nowrap; }
    .gk-map-stack { grid-area: stack; display: flex; align-items: center; margin: 0; padding: 0; padding-inline-start: .5rem; list-style: none; }
    .gk-map-stack li { flex: none; width: 2rem; height: 2rem; margin-inline-start: -.5rem; border: 2px solid var(--gk-solid); border-radius: 999px; background: #ffffff; box-shadow: 0 0 0 1px var(--gk-line); overflow: hidden; }
    .gk-map-stack img { display: block; width: 100%; height: 100%; object-fit: cover; }
    .gk-map-stack .gk-map-more { display: grid; place-items: center; width: auto; min-width: 2rem; padding: 0 .4375rem; background: var(--gk-well); font-size: .75rem; font-weight: 700; color: var(--gk-ink-2); font-variant-numeric: tabular-nums; }
    .gk-map-stack .gk-map-narrow { display: none; }
    @media (max-width: 26rem) { .gk-map-stack .gk-map-wide { display: none; } .gk-map-stack .gk-map-narrow { display: grid; } }
    .gk-map-band.is-open .gk-map-stack { visibility: hidden; }
    .gk-map-acts { grid-area: acts; display: flex; align-items: center; gap: .5rem; }
    .gk-map-toggle { flex: none; white-space: nowrap; }
    .gk-map-toggle svg { flex: none; }

    .gk-map-body { display: grid; grid-template-columns: minmax(0, 1fr); grid-template-rows: minmax(0, 1fr) auto; border-top: 1px solid var(--gk-line); background: var(--gk-solid); }
    @media (min-width: 48rem) { .gk-map-body { grid-template-columns: minmax(0, 1fr) 18rem; grid-template-rows: minmax(0, 1fr); height: 30rem; } }
    .gk-map-canvas { display: flex; flex-direction: column; min-height: 16rem; min-width: 0; }
    .gk-map-view { position: relative; flex: 1 1 auto; min-height: 13rem; background-color: var(--gk-map-ground); background-image: radial-gradient(var(--gk-map-dot) 1px, transparent 1.5px); background-size: 1.25rem 1.25rem; }
    .gk-map-leaflet { position: absolute; inset: 0; background: transparent; font: inherit; outline-offset: -2px; }
    .gk-map-leaflet:focus-visible { outline: 2px solid var(--gk-ink); }
    .gk-map-leaflet .leaflet-tile-pane { filter: var(--gk-tile-filter); }
    {{-- Street images overlap by a pixel (VenueMap.vue, addTiles), which is what hides the hairline
         between them at the opening view's in-between zoom. Leaflet's own answer to the same
         hairline is a blend mode that ADDS overlapping pixels: with both, every overlap is a
         white line. One of the two, so the blend mode is put back (Leaflet's rule is
         .leaflet-container img.leaflet-tile, which a shorter selector here would lose to). --}}
    .gk-map-leaflet.leaflet-container img.leaflet-tile { mix-blend-mode: normal; }
    .gk-map-wait { position: absolute; z-index: 900; inset-inline: .75rem; top: 50%; transform: translateY(-50%); margin: 0 auto; max-width: 22rem; padding: .625rem .875rem; border-radius: .75rem; background: var(--gk-solid); color: var(--gk-ink-2); box-shadow: var(--gk-shadow-lift); font-size: .875rem; line-height: 1.4; text-align: center; }
    .gk-map-askcard { position: absolute; z-index: 900; inset-inline: .75rem; bottom: 2.375rem; display: flex; align-items: center; gap: .75rem; max-width: 30rem; margin-inline: auto; padding: .5rem .5rem .5rem .875rem; border-radius: .875rem; background: var(--gk-solid); box-shadow: var(--gk-shadow-lift), 0 0 0 1px var(--gk-line); }
    [dir="rtl"] .gk-map-askcard { padding: .5rem .875rem .5rem .5rem; }
    .gk-map-askcard p { flex: 1 1 auto; min-width: 0; margin: 0; font-size: .8125rem; line-height: 1.35; color: var(--gk-ink-2); }

    .gk-map-side { display: flex; flex-direction: column; min-height: 0; overflow-y: auto; border-top: 1px solid var(--gk-line); background: var(--gk-solid); }
    @media (min-width: 48rem) { .gk-map-side { border-top: 0; border-inline-start: 1px solid var(--gk-line); } }
    .gk-map-sidehead { margin: 0; padding: .625rem 1rem .25rem; background: var(--gk-solid); font-size: .75rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--gk-ink-3); }
    [dir="rtl"] .gk-map-sidehead { letter-spacing: 0; font-size: .8125rem; }
    .gk-map-when { position: sticky; top: 0; z-index: 2; display: flex; gap: .375rem; padding: .625rem .875rem .5rem; background: var(--gk-solid); border-bottom: 1px solid var(--gk-line); }
    .gk-map-whenchip { min-height: 1.875rem; padding: 0 .75rem; cursor: pointer; font-family: inherit; transition: background-color var(--gk-swap); }
    .gk-map-whenchip[aria-pressed="true"] { background: var(--gk-ink); color: var(--gk-solid); }
    .gk-map-whenchip:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 2px; }
    .gk-map-townbtn { display: inline-flex; align-items: center; padding: 0; border: 0; background: none; color: inherit; font: inherit; letter-spacing: inherit; text-transform: inherit; cursor: pointer; }
    .gk-map-townbtn:hover { text-decoration: underline; text-underline-offset: 3px; }
    .gk-map-townbtn:hover { color: var(--gk-ink); }
    .gk-map-townbtn:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 2px; }
    .gk-map-list { margin: 0; padding: 0 .375rem .25rem; list-style: none; }
    .gk-map-list-flush { margin-inline: -.625rem; padding: 0; }
    .gk-map-empty { padding: 1rem; }
    .gk-map-nopinmark { flex: none; margin-inline-start: auto; font-size: .75rem; white-space: nowrap; color: var(--gk-ink-3); }
    .gk-map-legend { display: flex; align-items: center; gap: .4375rem; margin: 0; padding: .5rem 1rem 0; font-size: .78125rem; line-height: 1.3; color: var(--gk-ink-3); }
    .gk-map-legend i { flex: none; width: .625rem; height: .625rem; border-radius: 999px; background: var(--gk-ink-3); }
    .gk-map-townbtn svg { flex: none; margin-inline-start: .3125rem; }
    .gk-map-item.is-hot { background: var(--gk-well); }
    .gk-map-item { display: flex; align-items: center; gap: .625rem; width: 100%; min-height: 3rem; padding: .375rem .625rem; border: 0; border-radius: .625rem; background: transparent; color: var(--gk-ink); text-align: start; cursor: pointer; transition: background-color var(--gk-swap); }
    .gk-map-item:hover { background: var(--gk-well); }
    .gk-map-item:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: -2px; }
    .gk-map-face { flex: none; display: grid; place-items: center; width: 2.25rem; height: 2.25rem; border-radius: 999px; background: var(--es-accent); color: var(--es-accent-text); font-size: .9375rem; font-weight: 700; box-shadow: 0 0 0 1px var(--gk-line); overflow: hidden; }
    .gk-map-face img { display: block; width: 100%; height: 100%; object-fit: cover; }
    .gk-map-face-lg { width: 3rem; height: 3rem; font-size: 1.25rem; }
    .gk-map-itemtext { min-width: 0; display: flex; flex-direction: column; }
    .gk-map-itemtext strong { font-size: .9375rem; font-weight: 650; line-height: 1.25; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .gk-map-itemtext small { font-size: .8125rem; line-height: 1.3; color: var(--gk-ink-3); font-variant-numeric: tabular-nums; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .gk-map-venue { flex: 1 0 auto; display: flex; flex-direction: column; gap: .5rem; padding: .5rem 1rem .75rem; }
    .gk-map-back { align-self: flex-start; margin-inline-start: -.625rem; min-height: 2.25rem; }
    .gk-map-back svg { width: 1rem; height: 1rem; }
    .gk-map-who { display: flex; align-items: center; gap: .75rem; }
    .gk-map-who h3 { margin: 0; font-size: 1.125rem; font-weight: 700; line-height: 1.2; outline: none; }
    .gk-map-addr { display: flex; flex-wrap: wrap; align-items: center; gap: .25rem .5rem; margin: 0; font-size: .875rem; line-height: 1.35; color: var(--gk-ink-3); }
    .gk-map-label-gap { margin-top: .5rem; }
    .gk-map-links { display: flex; flex-wrap: wrap; gap: .25rem 1.125rem; margin: 0 0 .25rem; font-size: .875rem; }
    .gk-map-links a { display: inline-flex; align-items: center; gap: .25rem; }
    .gk-map-label { margin: 0; font-size: .75rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--gk-ink-3); }
    [dir="rtl"] .gk-map-label { letter-spacing: 0; font-size: .8125rem; }
    .gk-map-next { margin: 0; padding: 0; list-style: none; border-top: 1px solid var(--gk-line); }
    .gk-map-next li { border-bottom: 1px solid var(--gk-line); }
    .gk-map-next a { display: block; padding: .5rem 0; color: var(--gk-ink); text-decoration: none; }
    .gk-map-next a:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 1px; }
    .gk-map-next small { display: block; font-size: .78125rem; font-weight: 700; color: var(--gk-ink-2); font-variant-numeric: tabular-nums; }
    .gk-map-next b { display: inline; font-size: .9375rem; font-weight: 650; line-height: 1.3; }
    .gk-map-nopin { margin: 0; font-size: .84375rem; color: var(--gk-ink-3); }
    .gk-map-cta { position: sticky; bottom: 0; flex: none; padding: .625rem 1rem; border-top: 1px solid var(--gk-line); background: var(--gk-solid); }


    .gk-map-sheet { position: fixed; inset: 0; z-index: 60; display: flex; flex-direction: column; background: var(--gk-solid); color: var(--gk-ink); font-family: inherit; }
    .gk-map-sheetbar { flex: none; display: flex; align-items: center; gap: .5rem; min-height: 3.25rem; padding: 0 .375rem 0 1rem; border-bottom: 1px solid var(--gk-line); }
    [dir="rtl"] .gk-map-sheetbar { padding: 0 1rem 0 .375rem; }
    .gk-map-sheetbar h2 { margin: 0; margin-inline-end: auto; font-size: 1.0625rem; font-weight: 700; }
    .gk-map-sheetslot { display: flex; flex-direction: column; flex: 1 1 auto; min-height: 0; }
    .gk-map-sheet .gk-map-body { flex: 1 1 auto; min-height: 0; height: auto; border-top: 0; grid-template-rows: minmax(0, 1fr) minmax(0, 52%); }
    .gk-map-sheet .gk-map-canvas { min-height: 0; }

    .gk-map-sheet .gk-map-view { min-height: 0; }
    @media (min-width: 48rem) { .gk-map-sheet .gk-map-body { grid-template-columns: minmax(0, 1fr) 22rem; grid-template-rows: minmax(0, 1fr); } }

    .gk-pinbox { background: none; border: 0; }
    .gk-cluster { background: none; border: 0; }
    .gk-pinwrap { position: relative; display: block; width: 2.5rem; height: 2.875rem; }
    .gk-pin { position: relative; isolation: isolate; display: grid; place-items: center; width: 2.5rem; height: 2.5rem; border: 2px solid var(--gk-pin-ring); border-radius: 999px; background: var(--es-accent); color: var(--es-accent-text); font-weight: 700; font-size: 1rem; line-height: 1; box-shadow: 0 2px 6px rgb(15 23 42 / .38); transition: transform var(--gk-swap); transform-origin: 50% 118%; }
    .gk-pin::after { content: ""; position: absolute; z-index: -1; left: 50%; bottom: -.375rem; width: .75rem; height: .75rem; margin-left: -.375rem; border-radius: .125rem; background: var(--gk-pin-ring); transform: rotate(45deg); box-shadow: 2px 2px 3px rgb(15 23 42 / .22); }
    .gk-map-leaflet .gk-pin img { display: block; width: 100%; height: 100%; border-radius: 999px; object-fit: cover; }
    .gk-pin.is-on { transform: scale(1.22); border-width: 3px; border-color: var(--es-accent); box-shadow: inset 0 0 0 2px var(--gk-pin-ring), 0 0 0 .4375rem var(--es-accent-tint), 0 3px 10px rgb(15 23 42 / .45); }
    .gk-pin.is-on::after { background: var(--es-accent); }
    .gk-pinwrap-cluster { display: flex; justify-content: center; width: 5rem; height: 2.375rem; }
    .gk-cluster-dot { display: inline-flex; align-items: center; height: 2.375rem; padding: 0 .6875rem 0 .3125rem; border-radius: 999px; background: var(--gk-solid); color: var(--gk-ink); font-weight: 800; font-size: .9375rem; line-height: 1; font-variant-numeric: tabular-nums; box-shadow: 0 2px 6px rgb(15 23 42 / .32), 0 0 0 1px var(--gk-line); }
    .gk-map-leaflet .gk-cluster-dot img { flex: none; width: 1.75rem; height: 1.75rem; border: 2px solid var(--gk-solid); border-radius: 999px; background: #ffffff; object-fit: cover; }
    .gk-map-leaflet .gk-cluster-dot img + img { margin-inline-start: -.6875rem; }
    .gk-cluster-dot i { flex: none; display: grid; place-items: center; width: 1.75rem; height: 1.75rem; border: 2px solid var(--gk-solid); border-radius: 999px; background: var(--es-accent); color: var(--es-accent-text); font-weight: 700; font-size: .75rem; line-height: 1; }
    .gk-cluster-dot img + i, .gk-cluster-dot i + i, .gk-cluster-dot i + img { margin-inline-start: -.6875rem; }
    .gk-cluster-dot b { margin-inline-start: .375rem; }
    .gk-cluster-dot b:first-child { margin-inline-start: .5rem; }
    .gk-map-face img.is-fit, .gk-map-stack img.is-fit, .gk-map-leaflet .gk-pin img.is-fit, .gk-map-leaflet .gk-cluster-dot img.is-fit { object-fit: contain; padding: 8%; background: #ffffff; }
    .gk-pin-label { position: absolute; top: 100%; left: 50%; transform: translateX(-50%); display: flex; flex-direction: column; align-items: center; margin-top: .125rem; max-width: 11rem; white-space: nowrap; font-weight: 650; font-size: .75rem; line-height: 1.25; color: var(--gk-ink); text-shadow: 0 0 2px var(--gk-map-halo), 0 0 2px var(--gk-map-halo), 0 0 3px var(--gk-map-halo), 0 0 5px var(--gk-map-halo); pointer-events: none; }
    .gk-pin-label bdi { max-width: 100%; overflow: hidden; text-overflow: ellipsis; }
    .gk-pin-label.is-after { top: 1.25rem; left: 100%; transform: translateY(-50%); margin-top: 0; margin-left: .375rem; align-items: flex-start; }
    .gk-pin-label.is-before { top: 1.25rem; left: auto; right: 100%; transform: translateY(-50%); margin-top: 0; margin-right: .375rem; align-items: flex-end; }
    .gk-pinwrap-cluster .gk-pin-label.is-after { top: 50%; margin-left: 0; text-align: start; }
    .gk-pinwrap-cluster .gk-pin-label.is-before { top: 50%; margin-right: 0; text-align: end; }
    .gk-pin-label.is-above { top: auto; bottom: 100%; margin-top: 0; margin-bottom: .1875rem; }
    .gk-pin-label small { font-size: .6875rem; font-weight: 500; color: var(--gk-ink-3); }
    .has-streets .gk-pin-label small { display: none; }
    .gk-pin-label.is-on { margin-top: .5625rem; padding: .0625rem .4375rem; border-radius: .375rem; background: var(--gk-solid); font-weight: 800; text-shadow: none; box-shadow: 0 0 0 1px var(--gk-line), var(--gk-shadow); }
    .gk-pin.is-hot { box-shadow: 0 0 0 .25rem var(--es-accent-tint), 0 2px 6px rgb(15 23 42 / .38); border-color: var(--es-accent); }
    .gk-pin-quiet { display: block; width: 1.125rem; height: 1.125rem; border: 2px solid var(--gk-pin-ring); border-radius: 999px; background: var(--gk-ink-3); box-shadow: 0 1px 3px rgb(15 23 42 / .35); }
    .gk-pin-quiet.is-on { background: var(--es-accent); box-shadow: 0 0 0 .3125rem var(--es-accent-tint), 0 1px 3px rgb(15 23 42 / .35); }
    .gk-pin-quiet.is-hot { background: var(--gk-ink-2); }
    .gk-pinwrap-cluster .gk-pin-label { margin-top: .1875rem; width: max-content; max-width: 12rem; white-space: normal; text-align: center; font-weight: 700; color: var(--gk-ink-2); }
    .gk-pinwrap-cluster .gk-pin-label { display: block; }
    .gk-pinwrap-cluster .gk-pin-label bdi { overflow: visible; max-width: none; line-height: 1.5; }
    .has-streets .gk-pin-label { text-shadow: none; }
    .has-streets .gk-pin-label bdi { padding: .0625rem .3125rem; border-radius: .375rem; background: color-mix(in srgb, var(--gk-solid) 90%, transparent); -webkit-box-decoration-break: clone; box-decoration-break: clone; }
    .has-streets .gk-pin-label.is-on bdi { padding: 0; background: none; }
    .gk-map-leaflet .leaflet-bar { border: 0; border-radius: .625rem; overflow: hidden; box-shadow: var(--gk-shadow-lift); }
    .gk-map-leaflet .leaflet-bar a { width: 2.25rem; height: 2.25rem; line-height: 2.25rem; background: var(--gk-solid); color: var(--gk-ink); border-bottom-color: var(--gk-line); }
    .gk-map-leaflet .leaflet-bar a:hover { background: var(--gk-well); color: var(--gk-ink); }
    .gk-map-leaflet .leaflet-control-scale { direction: ltr; }
    .gk-map-leaflet .leaflet-control-scale-line { border-color: var(--gk-ink-3); border-top: 0; background: transparent; color: var(--gk-ink-3); font-weight: 600; font-size: .6875rem; line-height: 1.4; text-shadow: none; }
    .gk-map-leaflet .leaflet-control-attribution { background: color-mix(in srgb, var(--gk-solid) 82%, transparent); color: var(--gk-ink-2); font-weight: 500; font-size: .6875rem; line-height: 1.5; }
    .gk-map-leaflet .leaflet-control-attribution a { color: inherit; }

    @media (prefers-reduced-motion: reduce) {
      .gk-btn, .gk-link, .gk-pin, .gk-map-item, .gk-map-band { transition-duration: 1ms; }
      .gk-btn:active { transform: none; }
    }

    .has-streets .gk-map-leaflet .leaflet-control-scale-line { background: color-mix(in srgb, var(--gk-solid) 82%, transparent); }
</style>
