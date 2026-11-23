{{-- The look of the vendor's pages (the link, its closed page, the "Not coming" pages): one
     column for a phone, light and dark, read right to left in Hebrew and Arabic. Plain CSS on
     tokens, so nothing here waits for a Tailwind build. Colours checked at 4.5:1 or more for text
     on both grounds: ink #1f2321 on #f6f5f1 and #ffffff; #e9ebe8 on #151816 and #1f2421; the
     button's white on #1d5fd1 (6.0:1) and #2a63cc (5.4:1). --}}
<style {!! nonce_attr() !!}>
    .vmc {
        --vmc-bg: #f6f5f1;
        --vmc-card: #ffffff;
        --vmc-ink: #1f2321;
        --vmc-ink-2: #4b524e;
        --vmc-ink-3: #5f6662;
        --vmc-line: #e2e1db;
        --vmc-line-strong: #c9c8c0;
        --vmc-accent: #1d5fd1;
        --vmc-accent-hover: #174ea9;
        --vmc-accent-text: #1a56bf;
        --vmc-on-accent: #ffffff;
        --vmc-soft: #eaf0fc;
        --vmc-ok-bg: #f0fdf4;
        --vmc-ok-line: #86efac;
        --vmc-ok-ink: #166534;
        --vmc-warn-bg: #fffbeb;
        --vmc-warn-line: #fcd34d;
        --vmc-warn-ink: #92400e;
        --vmc-err-bg: #fef2f2;
        --vmc-err-line: #fca5a5;
        --vmc-err-ink: #991b1b;
        --vmc-shadow: 0 1px 2px rgba(0, 0, 0, 0.06), 0 4px 16px rgba(0, 0, 0, 0.05);
        min-height: 100vh;
        min-height: 100dvh;
        background: var(--vmc-bg);
        color: var(--vmc-ink);
        font-size: 17px;
        line-height: 1.45;
    }
    .dark .vmc {
        --vmc-bg: #151816;
        --vmc-card: #1f2421;
        --vmc-ink: #e9ebe8;
        --vmc-ink-2: #c0c6c2;
        --vmc-ink-3: #a3aaa6;
        --vmc-line: #2f3532;
        --vmc-line-strong: #46504b;
        --vmc-accent: #2a63cc;
        --vmc-accent-hover: #3470de;
        --vmc-accent-text: #8fb4ff;
        --vmc-on-accent: #ffffff;
        --vmc-soft: #1d2a40;
        --vmc-ok-bg: rgba(22, 101, 52, 0.22);
        --vmc-ok-line: #2f7a4b;
        --vmc-ok-ink: #a7f3c0;
        --vmc-warn-bg: rgba(146, 64, 14, 0.22);
        --vmc-warn-line: #a16207;
        --vmc-warn-ink: #fcd9a0;
        --vmc-err-bg: rgba(153, 27, 27, 0.25);
        --vmc-err-line: #b04646;
        --vmc-err-ink: #fecaca;
        --vmc-shadow: 0 1px 2px rgba(0, 0, 0, 0.4);
    }
    .vmc-wrap { max-width: 34rem; margin: 0 auto; padding: 20px 16px 48px; }
    .vmc-kicker { color: var(--vmc-ink-2); font-size: 15px; margin: 0 0 4px; }
    .vmc-title { font-size: 26px; line-height: 1.2; font-weight: 700; margin: 0 0 8px; letter-spacing: -0.01em; }
    .vmc-lead { color: var(--vmc-ink-2); margin: 0 0 20px; }
    .vmc-card { background: var(--vmc-card); border: 1px solid var(--vmc-line); border-radius: 16px; padding: 18px; box-shadow: var(--vmc-shadow); }
    .vmc-stack > * + * { margin-top: 12px; }
    .vmc-btn {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        width: 100%; min-height: 52px; padding: 12px 18px; border-radius: 12px;
        font-size: 17px; font-weight: 600; text-align: center; text-decoration: none;
        border: 1px solid var(--vmc-line-strong); background: var(--vmc-card); color: var(--vmc-ink);
        cursor: pointer; transition: background-color 0.2s, border-color 0.2s, box-shadow 0.2s;
    }
    .vmc-btn:hover { border-color: var(--vmc-ink-3); }
    .vmc-btn:focus-visible, .vmc-field:focus-visible, .vmc-check:focus-within { outline: 3px solid var(--vmc-accent-text); outline-offset: 2px; }
    .vmc-btn[disabled] { opacity: 0.55; cursor: default; }
    .vmc-btn-primary { background: var(--vmc-accent); border-color: var(--vmc-accent); color: var(--vmc-on-accent); }
    .vmc-btn-primary:hover { background: var(--vmc-accent-hover); border-color: var(--vmc-accent-hover); }
    .vmc-btn-quiet { border-color: transparent; background: transparent; color: var(--vmc-accent-text); min-height: 44px; }
    .vmc-btn-quiet:hover { border-color: transparent; text-decoration: underline; }
    .vmc-btn-danger { color: var(--vmc-err-ink); }
    .vmc-row { display: flex; gap: 12px; }
    .vmc-row > * { flex: 1 1 0; }
    .vmc-note { border-radius: 12px; padding: 12px 14px; border: 1px solid var(--vmc-line); background: var(--vmc-soft); color: var(--vmc-ink); }
    .vmc-note-ok { background: var(--vmc-ok-bg); border-color: var(--vmc-ok-line); color: var(--vmc-ok-ink); }
    .vmc-note-warn { background: var(--vmc-warn-bg); border-color: var(--vmc-warn-line); color: var(--vmc-warn-ink); }
    .vmc-note-err { background: var(--vmc-err-bg); border-color: var(--vmc-err-line); color: var(--vmc-err-ink); }
    .vmc-label { display: block; font-weight: 600; margin: 0 0 6px; }
    .vmc-hint { color: var(--vmc-ink-3); font-size: 15px; margin: 4px 0 0; }
    .vmc-field {
        display: block; width: 100%; min-height: 52px; padding: 12px 14px !important; border-radius: 12px;
        border: 1px solid var(--vmc-line-strong); background: var(--vmc-card); color: var(--vmc-ink);
        font-size: 17px !important;
    }
    .vmc-check { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border: 1px solid var(--vmc-line); border-radius: 12px; background: var(--vmc-card); }
    .vmc-check input { width: 22px; height: 22px; accent-color: var(--vmc-accent); }
    .vmc-check-on { border-color: var(--vmc-accent-text); }
    .vmc-muted { color: var(--vmc-ink-3); }
    .vmc-big { font-size: 34px; font-weight: 800; line-height: 1.1; }
    .vmc-center { text-align: center; }
    .vmc-link { color: var(--vmc-accent-text); text-decoration: underline; }
    .vmc-toolbar { display: flex; gap: 8px; margin-bottom: 12px; }
    .vmc-search { flex: 1 1 auto; }
    .vmc-toggle { width: auto; flex: 0 0 auto; }
    .vmc-list { list-style: none; margin: 0; padding: 0; background: var(--vmc-card); border: 1px solid var(--vmc-line); border-radius: 16px; overflow: hidden; }
    .vmc-list li + li { border-top: 1px solid var(--vmc-line); }
    .vmc-list-row { display: flex; align-items: center; gap: 14px; width: 100%; min-height: 60px; padding: 10px 16px; background: none; border: 0; color: inherit; text-align: start; font-size: 17px; cursor: pointer; }
    .vmc-list-row:hover { background: var(--vmc-soft); }
    .vmc-list-row:focus-visible { outline: 3px solid var(--vmc-accent-text); outline-offset: -3px; }
    .vmc-list-row[disabled] { cursor: default; background: none; }
    .vmc-list-row[disabled] .vmc-num, .vmc-list-row[disabled] .vmc-list-name { color: var(--vmc-ink-3); }
    .vmc-num { flex: 0 0 auto; min-width: 48px; padding: 6px 8px; border-radius: 10px; background: var(--vmc-bg); border: 1px solid var(--vmc-line); font-weight: 800; text-align: center; direction: ltr; unicode-bidi: isolate; }
    .vmc-list-text { display: flex; flex-direction: column; min-width: 0; }
    .vmc-list-name { font-weight: 600; overflow-wrap: anywhere; }
    .vmc-list-sub { color: var(--vmc-ink-3); font-size: 15px; }
    .vmc-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .vmc-chip { display: inline-flex; align-items: center; gap: 8px; min-height: 44px; padding: 8px 14px; border-radius: 999px; border: 1px solid var(--vmc-line-strong); background: var(--vmc-card); color: var(--vmc-ink); font-size: 16px; cursor: pointer; transition: background-color 0.2s, border-color 0.2s; }
    .vmc-chip.is-on { background: var(--vmc-soft); border-color: var(--vmc-accent-text); box-shadow: inset 0 0 0 1px var(--vmc-accent-text); font-weight: 600; }
    .vmc-chip:focus-visible { outline: 3px solid var(--vmc-accent-text); outline-offset: 2px; }
    .vmc-chip-icon { width: 20px; height: 20px; flex: 0 0 auto; }
    .vmc-photo { display: flex; gap: 12px; align-items: flex-start; }
    .vmc-photo-preview { position: relative; flex: 0 0 96px; width: 96px; height: 96px; border-radius: 12px; overflow: hidden; border: 1px solid var(--vmc-line); }
    .vmc-photo-preview img { width: 100%; height: 100%; object-fit: cover; }
    .vmc-ring { position: absolute; inset: 22px; width: 52px; height: 52px; transform: rotate(-90deg); }
    .vmc-ring circle { fill: none; stroke-width: 4; }
    .vmc-ring-track { stroke: rgba(255, 255, 255, 0.6); }
    .vmc-ring-bar { stroke: var(--vmc-accent); stroke-linecap: round; transition: stroke-dasharray 0.2s; }
    .vmc-switch { display: flex; align-items: center; justify-content: space-between; gap: 12px; min-height: 52px; padding: 10px 14px; border: 1px solid var(--vmc-line); border-radius: 12px; background: var(--vmc-card); font-weight: 600; cursor: pointer; position: relative; }
    .vmc-switch input { position: absolute; opacity: 0; width: 1px; height: 1px; }
    .vmc-switch-track { flex: 0 0 auto; width: 48px; height: 28px; border-radius: 999px; background: var(--vmc-line-strong); position: relative; transition: background-color 0.2s; }
    .vmc-switch-knob { position: absolute; top: 3px; inset-inline-start: 3px; width: 22px; height: 22px; border-radius: 999px; background: #fff; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.3); transition: inset-inline-start 0.2s; }
    .vmc-switch input:checked + .vmc-switch-track { background: var(--vmc-accent); }
    .vmc-switch input:checked + .vmc-switch-track .vmc-switch-knob { inset-inline-start: 23px; }
    .vmc-switch input:focus-visible + .vmc-switch-track { outline: 3px solid var(--vmc-accent-text); outline-offset: 2px; }
    .vmc-or { display: flex; align-items: center; gap: 12px; color: var(--vmc-ink-3); margin: 4px 0; }
    .vmc-or::before, .vmc-or::after { content: ''; flex: 1; height: 1px; background: var(--vmc-line); }
    .vmc-google { background: var(--vmc-card); color: var(--vmc-ink); border-color: var(--vmc-line-strong); }
    .vmc-google:hover { background: var(--vmc-soft); border-color: var(--vmc-ink-3); }
    .vmc-code { font-size: 26px !important; letter-spacing: 0.3em; text-align: center; }
    .vmc-dates { list-style: none; margin: 0; padding: 0; }
    .vmc-dates li { display: flex; align-items: center; justify-content: space-between; gap: 8px; min-height: 48px; }
    .vmc-dates li + li { border-top: 1px solid var(--vmc-line); }
    .vmc-inline { width: auto; min-height: 40px; padding: 6px 8px; font-size: 15px; }
    /* The market as blocks (vendor-map/schematic), for a vendor choosing where they stand. */
    .vmc-schematic { margin-bottom: 16px; padding: 12px 10px; background: var(--vmc-card); border: 1px solid var(--vmc-line); border-radius: 16px; }
    .vmc-schematic .vms { --vms-ink: var(--vmc-ink); --vms-ink2: var(--vmc-ink-3); --vms-bg: var(--vmc-card); --vms-tint: 9%; --vms-edge: 30%;
        --vms-cell: var(--vmc-card); --vms-text-base: var(--vmc-ink); --vms-text-mix: 45%; --vms-cell-h: clamp(2.4rem, 10vw, 2.9rem); --vms-cell-font: .95rem;
        --vms-gap: .35rem; --vms-zone-gap: .9rem; --vms-pad: .35rem; }
    .vmc-schematic .vms-sells { display: none; }
    @media (min-width: 40rem) { .vmc-schematic .vms-sells { display: inline; } .vmc-schematic .vms { --vms-cell-font: 1.0625rem; --vms-gap: .45rem; --vms-pad: .45rem; } }
    .vmc-schematic .vms-c { font-family: inherit; padding: 0; transition: transform .12s, background-color .12s; }
    .vmc-schematic button.vms-c { cursor: pointer; }
    .vmc-schematic button.vms-c:active { transform: scale(.94); }
    .vmc-schematic .vms-c:focus-visible { outline: 3px solid var(--vmc-accent); outline-offset: 2px; }
    .vmc-schematic .vms-c.is-taken { background: color-mix(in srgb, var(--vmc-ink) 10%, var(--vmc-card)); border-color: color-mix(in srgb, var(--vmc-ink) 22%, var(--vmc-card)); color: color-mix(in srgb, var(--vmc-ink) 45%, var(--vmc-card)); }
    .vmc-schematic .vms-c.is-waiting { background: repeating-linear-gradient(45deg, transparent 0 4px, color-mix(in srgb, var(--vmc-ink) 14%, transparent) 4px 5.5px), var(--vmc-card); border-style: dashed; opacity: .55; }
    .vmc-schematic .vms-c.is-on { background: var(--vmc-accent); border-color: var(--vmc-accent); color: #fff; transform: scale(1.08); box-shadow: 0 0 0 4px color-mix(in srgb, var(--vmc-accent) 28%, transparent); }
    .vmc-schematic.is-confirm .vms-c:not(.is-on) { opacity: .45; }
    .vmc-confirm-name { margin: 0 0 2px; font-size: 22px; font-weight: 700; color: var(--vmc-ink); }
    .vmc-legend { display: inline-flex; align-items: center; gap: 6px; font-size: 14px; color: var(--vmc-ink-3); }
    .vmc-legend i { display: inline-block; width: 14px; height: 14px; border-radius: 4px; border: 1.5px solid color-mix(in srgb, var(--vmc-ink) 35%, var(--vmc-card)); background: var(--vmc-card); }
    .vmc-legend i.is-taken { margin-inline-start: 8px; background: color-mix(in srgb, var(--vmc-ink) 12%, var(--vmc-card)); border-color: transparent; }
    @media (prefers-reduced-motion: reduce) { .vmc-schematic .vms-c { transition: none; } .vmc-schematic .vms-c.is-on { transform: none; } }
    /* The three steps of joining: where you are, a progress bar, the step's title. */
    .vmc-steps { margin-bottom: 18px; }
    .vmc-steps-top { display: flex; justify-content: space-between; align-items: center; gap: 12px; min-height: 32px; font-size: 14px; color: var(--vmc-ink-3); }
    .vmc-steps-where { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .vmc-step-of { font-weight: 600; white-space: nowrap; }
    .vmc-back { display: inline-flex; align-items: center; gap: 4px; min-height: 40px; min-width: 0; padding: 0; border: 0; background: none; color: var(--vmc-accent-text); font: inherit; font-weight: 600; cursor: pointer; }
    .vmc-back > span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .vmc-chev { width: 18px; height: 18px; flex: 0 0 auto; }
    [dir="rtl"] .vmc-chev { transform: scaleX(-1); }
    .vmc-progress { display: grid; grid-auto-flow: column; grid-auto-columns: 1fr; gap: 4px; margin: 6px 0 14px; }
    .vmc-progress i { height: 4px; border-radius: 2px; background: var(--vmc-line); }
    .vmc-progress i.is-on { background: var(--vmc-accent); }
    .vmc-steps .vmc-title { font-size: 28px; font-weight: 800; letter-spacing: -0.02em; }
    .vmc-steps .vmc-lead { margin-bottom: 4px; }
    .vmc-stack.vmc-form > * + * { margin-top: 22px; }
    .vmc-label-row { display: flex; justify-content: space-between; align-items: baseline; gap: 12px; }
    .vmc-label-row .vmc-hint { margin: 0; font-size: 14px; }
    .vmc-optional { font-weight: 500; color: var(--vmc-ink-3); }
    .vmc-tiles { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .vmc-tile { min-height: 48px; display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 8px 14px; border-radius: 12px; border: 1.5px solid var(--vmc-line-strong); background: var(--vmc-card); color: var(--vmc-ink); font: inherit; font-size: 15px; font-weight: 600; text-align: start; cursor: pointer; transition: background-color .15s, border-color .15s, opacity .15s; }
    .vmc-tile.is-on { background: var(--vmc-soft); border-color: var(--vmc-accent); color: var(--vmc-accent-text); }
    .vmc-tile.is-full { opacity: .5; cursor: default; }
    .vmc-tile:focus-visible { outline: 3px solid var(--vmc-accent-text); outline-offset: 2px; }
    .vmc-tile-check { width: 18px; height: 18px; flex: 0 0 auto; }
    .vmc-photo-drop { display: flex; align-items: center; justify-content: center; gap: 10px; min-height: 76px; border: 1.5px dashed var(--vmc-line-strong); border-radius: 12px; color: var(--vmc-accent-text); font-weight: 600; cursor: pointer; }
    .vmc-photo-drop svg { width: 22px; height: 22px; }
    .vmc-photo-drop:focus-within { outline: 3px solid var(--vmc-accent-text); outline-offset: 2px; }
    /* The forward button stays in reach at the foot of the screen. */
    .vmc-foot { position: sticky; bottom: 0; margin: 8px -16px -48px; padding: 12px 16px calc(16px + env(safe-area-inset-bottom)); background: var(--vmc-bg); border-top: 1px solid var(--vmc-line); z-index: 2; }
    .vmc-foot .vmc-btn { width: 100%; }
    .vmc-status { background: var(--vmc-card); border: 1.5px solid var(--vmc-line); border-radius: 16px; padding: 18px; margin-bottom: 18px; }
    .vmc-status-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; padding-bottom: 14px; margin-bottom: 14px; border-bottom: 1px solid var(--vmc-line); }
    .vmc-status-spot { margin: 0; font-size: 30px; font-weight: 800; letter-spacing: -0.02em; line-height: 1.05; }
    .vmc-status-name { margin: 4px 0 0; color: var(--vmc-ink-2); }
    .vmc-pill { flex: 0 0 auto; font-size: 13px; font-weight: 700; padding: 5px 10px; border-radius: 999px; background: var(--vmc-warn-bg); color: var(--vmc-warn-ink); border: 1px solid var(--vmc-warn-line); }
    .vmc-track { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 10px; font-size: 15px; }
    .vmc-track li { display: flex; align-items: center; gap: 10px; color: var(--vmc-ink-3); }
    .vmc-track i { width: 18px; height: 18px; flex: 0 0 auto; box-sizing: border-box; border-radius: 50%; border: 1.5px solid var(--vmc-line-strong); background: var(--vmc-card); }
    .vmc-track li.is-done { color: var(--vmc-ink); }
    .vmc-track li.is-done i { border: 0; background: var(--vmc-accent) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3E%3Cpath d='M5 10.5l3 3 7-7' fill='none' stroke='white' stroke-width='2.4' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E") center / 14px no-repeat; }
    .vmc-track li.is-now { color: var(--vmc-ink); font-weight: 700; }
    .vmc-track li.is-now i { border: 5px solid var(--vmc-accent); }
    .vmc-linkback { margin-bottom: 8px; }
    .vmc-copyrow { display: flex; align-items: center; gap: 8px; background: var(--vmc-card); border: 1.5px solid var(--vmc-line-strong); border-radius: 12px; padding: 4px; padding-inline-start: 14px; }
    .vmc-copyrow-url { flex: 1; min-width: 0; font-size: 15px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; text-align: left; }
    .vmc-copyrow-btn { min-height: 44px; min-width: 76px; padding: 0 14px; border: 0; border-radius: 9px; background: var(--vmc-soft); color: var(--vmc-accent-text); font: inherit; font-size: 15px; font-weight: 700; cursor: pointer; }
    .vmc-copyrow-btn:focus-visible { outline: 3px solid var(--vmc-accent-text); outline-offset: 2px; }
    .vmc-btn-primary[disabled] { opacity: 1; background: var(--vmc-line); border-color: var(--vmc-line); color: var(--vmc-ink-3); }
    /* The area map (AreaMap.vue) on the vendor link's own colours. */
    .vmc-schematic { --gk-stage: var(--vmc-card); --gk-solid: var(--vmc-card); --gk-ink: var(--vmc-ink); --gk-ink-2: var(--vmc-ink-2); --gk-ink-3: var(--vmc-ink-3); --gk-line: var(--vmc-line); }
    .vmc-schematic .vmo-view { max-height: 55vh; border: 0; }
    .vmc-schematic .vmo-dots i.is-taken { background: color-mix(in srgb, var(--vmc-ink) 28%, var(--vmc-card)); border-color: transparent; }
    .vmc-schematic .vmo-dots i.is-waiting { opacity: .45; border-style: dashed; }
    .vmc-schematic .vmo-dots i.is-on { background: var(--vmc-accent); border-color: var(--vmc-accent); box-shadow: none; }
    .vmc-schematic .vmo-card.is-marked { border-color: var(--vmc-accent); }
</style>
