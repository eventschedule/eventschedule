{{-- The look of /analytics, on the portal's own tokens (--ap-*, --brand-*), so every palette and
     dark mode follow. Plain CSS and not Tailwind classes: a class that is not in the built
     stylesheet draws nothing until the next build. The page itself is the kit's (page-shell,
     x-page-header, x-page-tabs, x-page-card, page-filters); what is here is what the kit has no
     word for: a figure with its chart, a ranked list, a split bar, a funnel. --}}
<style {!! nonce_attr() !!}>
    [v-cloak] { display: none !important; }

    .an {
      --an-views: var(--brand-blue);
      --an-previous: rgb(var(--ap-ink-4));
      --an-boost: #eb6834;
      --an-newsletter: #6250d6;
      --an-wash: var(--brand-blue-a10);
      --an-wash-hover: color-mix(in srgb, var(--brand-blue) 17%, transparent);
      --an-up: #15803d;
      --an-up-bg: rgb(22 163 74 / 0.1);
      --an-down: #b91c1c;
      --an-down-bg: rgb(220 38 38 / 0.08);
      --an-mobile: var(--brand-blue);
      --an-desktop: #1baf7a;
      --an-tablet: #eda100;
      --an-other: rgb(var(--ap-ink-4));
    }
    .dark .an {
      --an-boost: #d95926;
      --an-newsletter: #9085e9;
      --an-up: #4ade80;
      --an-up-bg: rgb(34 197 94 / 0.14);
      --an-down: #f87171;
      --an-down-bg: rgb(239 68 68 / 0.14);
      --an-desktop: #199e70;
      --an-tablet: #c98500;
    }
    .an > * + * { margin-top: 1rem; }
    .an > .ap-tabs-wrap { margin-top: 1.25rem; margin-bottom: 1rem; }
    .an .an-num { font-variant-numeric: tabular-nums; }

    /* The row of filters: the kit's, with each control a size that reads its longest value. */
    .an-filters { align-items: center; margin-bottom: 0; }
    .an-filter { min-width: 11rem; }
    .an-filter.is-event { min-width: 13rem; max-width: 22rem; }
    .an-filters select { min-width: 100%; }
    @media (max-width: 639.98px) {
      .an-filter, .an-filter.is-event { flex: 1 1 100%; max-width: none; }
    }

    /* The figures and the chart: one card. */
    .an-hero { padding: 1.25rem 1.25rem 0.75rem; }
    @media (min-width: 640px) { .an-hero { padding: 1.5rem 1.75rem 1rem; } }
    .an-hero-head {
      display: flex;
      flex-wrap: wrap;
      align-items: flex-start;
      justify-content: space-between;
      gap: 1rem 2rem;
    }
    .an-figs {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(6.5rem, 1fr));
      gap: 1.25rem 1rem;
      flex: 1 1 32rem;
      min-width: 0;
    }
    .an-fig { min-width: 0; }
    .an-fig.is-lead { grid-column: 1 / -1; }
    @media (min-width: 900px) {
      .an-figs { display: flex; flex-wrap: wrap; gap: 1rem 0; }
      .an-fig { flex: 1 1 0; padding-inline: 1.75rem; border-inline-start: 1px solid var(--ap-hairline); max-width: 20rem; }
      .an-fig.is-lead { flex: 0 1 auto; padding-inline-start: 0; padding-inline-end: 2.5rem; border-inline-start: 0; max-width: none; }
    }
    .an-fig-label {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin: 0;
      font-size: 0.8125rem;
      font-weight: 500;
      line-height: 1.75rem;
      color: rgb(var(--ap-ink-3));
    }
    .an-fig-value {
      margin: 0.25rem 0 0;
      font-size: 1.5rem;
      font-weight: 650;
      line-height: 1.2;
      letter-spacing: -0.01em;
      color: rgb(var(--ap-ink));
      white-space: nowrap;
    }
    .an-fig-value.is-text { font-size: 1.25rem; line-height: 1.45; }
    .an-fig.is-lead .an-fig-value {
      margin-top: 0.375rem;
      font-size: 2.75rem;
      font-weight: 700;
      line-height: 1;
      letter-spacing: -0.025em;
    }
    @media (min-width: 640px) { .an-fig.is-lead .an-fig-value { font-size: 3.25rem; } }
    .an-fig.is-lead .an-fig-value.is-list { font-size: 1.75rem; line-height: 1.25; }
    .an-fig-value.is-list p { margin: 0; }
    .an-fig-sub {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 0.25rem 0.5rem;
      margin: 0.5rem 0 0;
      font-size: 0.8125rem;
      color: rgb(var(--ap-ink-2));
    }
    .an-fig:not(.is-lead) .an-fig-sub { margin-top: 0.25rem; font-size: 0.75rem; }
    @media (max-width: 639.98px) {
      .an-fig:not(.is-lead) .an-fig-value { font-size: 1.125rem; }
      .an-fig-value.is-text { font-size: 1rem; }
    }

    /* The change against the window before: a direction AND a sign AND a colour, never colour alone. */
    .an-delta {
      display: inline-flex;
      align-items: center;
      gap: 0.25rem;
      padding: 0.125rem 0.5rem 0.125rem 0.375rem;
      border-radius: 999px;
      font-size: 0.8125rem;
      font-weight: 650;
      font-variant-numeric: tabular-nums;
      background: var(--ap-tint-2);
      color: rgb(var(--ap-ink-2));
    }
    .an-delta svg { width: 0.875rem; height: 0.875rem; }
    .an-delta.is-up { background: var(--an-up-bg); color: var(--an-up); }
    .an-delta.is-down { background: var(--an-down-bg); color: var(--an-down); }

    /* A switch of two or three: the one that is on is pressed in (the portal's segmented look). */
    .an-seg {
      display: inline-flex;
      flex: none;
      gap: 2px;
      padding: 3px;
      border-radius: 0.75rem;
      background: rgb(var(--ap-surface));
      box-shadow: inset 0 0 0 1px rgb(var(--ap-border));
    }
    .an-seg > a, .an-seg > button {
      border: 0;
      border-radius: 0.5625rem;
      padding: 0.4375rem 0.875rem;
      background: transparent;
      font-size: 0.8125rem;
      font-weight: 600;
      line-height: 1.25rem;
      white-space: nowrap;
      color: rgb(var(--ap-ink-3));
      cursor: pointer;
      transition: all 0.2s;
    }
    .an-seg > a:hover, .an-seg > button:hover { color: rgb(var(--ap-ink)); }
    .an-seg > [aria-current="page"], .an-seg > [aria-selected="true"] {
      background: rgb(var(--ap-bg));
      box-shadow: var(--ap-inset-pressed);
      color: rgb(var(--ap-ink));
    }
    .an-seg > :focus-visible { outline: 2px solid var(--brand-blue); outline-offset: -2px; }
    .an-seg.is-sm > button { padding: 0.25rem 0.625rem; font-size: 0.75rem; }
    .an-seg.is-xs { margin-bottom: 0.75rem; }
    .an-seg.is-xs > button { padding: 0.1875rem 0.5rem; font-size: 0.6875rem; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-weight: 500; }

    /* Over the chart: its key on one side, how it groups on the other. */
    .an-chart-bar {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 0.75rem 1.5rem;
      margin-top: 1.5rem;
      padding-top: 1.25rem;
      border-top: 1px solid var(--ap-hairline);
    }
    .an-chart-bar > .an-seg { margin-inline-start: auto; }
    /* A phone: the switch takes its row and its three words share it ("Ежедневно, Еженедельно,
       Ежемесячно" needed 330px of 288), and a long amount wraps rather than leaving the card. */
    @media (max-width: 639.98px) {
      .an-chart-bar > .an-seg { flex: 1 1 100%; margin-inline-start: 0; }
      .an-chart-bar > .an-seg > a { flex: 1 1 0; min-width: 0; padding-inline: 0.375rem; overflow: hidden; text-overflow: ellipsis; text-align: center; }
      .an-fig-value { white-space: normal; overflow-wrap: anywhere; }
      .an-fig.is-lead .an-fig-value { font-size: clamp(1.875rem, 10vw, 2.75rem); }
    }
    .an-info { display: inline-block; width: 0.9375rem; height: 0.9375rem; color: rgb(var(--ap-ink-4)); cursor: help; }

    /* The chart's key: a short line of each series' colour beside its name, in ink. */
    .an-legend {
      display: flex;
      flex-wrap: wrap;
      gap: 0.25rem 1.25rem;
      margin: 0;
      padding: 0;
      list-style: none;
      font-size: 0.75rem;
      color: rgb(var(--ap-ink-3));
    }
    .an-legend li { display: inline-flex; align-items: center; gap: 0.4375rem; }
    .an-key { width: 0.875rem; height: 2px; border-radius: 2px; background: var(--an-views); }
    .an-key.is-previous { background: var(--an-previous); }
    .an-key.is-boost { background: var(--an-boost); }
    .an-key.is-newsletter { background: var(--an-newsletter); }
    .an-key.is-event { width: 0.625rem; height: 0.625rem; border-radius: 999px; background: rgb(var(--ap-surface)); box-shadow: 0 0 0 2px var(--an-views); }
    .an-key.is-bar { width: 0.625rem; height: 0.625rem; border-radius: 2px; }
    .an-chart { position: relative; height: 15rem; margin-top: 0.75rem; }
    @media (min-width: 640px) { .an-chart { height: 18rem; } }
    .an-chart.is-short { height: 12rem; }
    @media (min-width: 640px) { .an-chart.is-short { height: 14rem; } }
    .an-chart-title {
      margin: 0;
      font-size: 0.8125rem;
      font-weight: 600;
      color: rgb(var(--ap-ink-2));
    }
    /* Check-ins: the arrivals chart and the ticket types share the lower half of the card. */
    .an-hero-split {
      display: grid;
      gap: 1.5rem 2.25rem;
      grid-template-columns: minmax(0, 1fr);
      margin-top: 1.5rem;
      padding-top: 1.25rem;
      border-top: 1px solid var(--ap-hairline);
    }
    @media (min-width: 1280px) {
      .an-hero-split.has-side { grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); }
      .an-hero-split.has-side > .an-side { padding-inline-start: 2.25rem; border-inline-start: 1px solid var(--ap-hairline); }
    }
    .an-side-head { display: flex; justify-content: space-between; gap: 1rem; margin-bottom: 0.625rem; }

    /* The cards under the chart, in a grid whose every row is full: one column on a phone, two
       from a tablet, three from a 1440 window (a 1088px frame). The page marks the last cards of
       a row that would be short (an-w2-full, an-w3-half, an-w3-full). */
    .an-grid { display: grid; gap: 1rem; grid-template-columns: minmax(0, 1fr); align-items: stretch; }
    @media (min-width: 768px) {
      .an-grid { grid-template-columns: repeat(6, minmax(0, 1fr)); }
      .an-grid > * { grid-column: span 3; }
      .an-grid > .an-w2-full { grid-column: span 6; }
    }
    @media (min-width: 1440px) {
      .an-grid > * { grid-column: span 2; }
      .an-grid > .an-w2-full { grid-column: span 2; }
      .an-grid > .an-w3-half { grid-column: span 3; }
      .an-grid > .an-w3-full { grid-column: span 6; }
    }
    .an-card { display: flex; flex-direction: column; min-width: 0; }
    .an-grid > .an-card { margin: 0; }
    .an-card > .page-card-head { min-height: 2.125rem; margin-bottom: 0.75rem; }
    .an-card-unit { font-size: 0.75rem; color: rgb(var(--ap-ink-2)); }
    /* With an event selected: one line above the cards says what "Whole schedule" means, and
       each card that cannot narrow to an event carries the words at its foot. */
    .an-note { display: flex; align-items: flex-start; gap: 0.5rem; margin-bottom: 0; font-size: 0.8125rem; color: rgb(var(--ap-ink-2)); }
    .an-note svg, .an-scope svg { flex: none; width: 1rem; height: 1rem; margin-top: 0.0625rem; color: rgb(var(--ap-ink-3)); }
    .an-foot { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.5rem 1rem; margin-top: auto; padding-top: 0.875rem; }
    .an-scope { display: inline-flex; align-items: center; gap: 0.375rem; border-radius: 999px; padding: 0.1875rem 0.625rem 0.1875rem 0.5rem; font-size: 0.75rem; font-weight: 600; background: var(--ap-tint-2); color: rgb(var(--ap-ink-2)); }
    .an-scope svg { margin: 0; width: 0.875rem; height: 0.875rem; }

    /* A ranked list. The wash behind a row's name is as long as its number against the largest;
       it lives in the name's track, which ends before the figures, and is never shorter than the
       picture plus a little, so a small share still reads as a bar and not as a stray chip. */
    .an-rows { margin: 0 -0.5rem; padding: 0; list-style: none; }
    .an-row-in {
      display: flex;
      align-items: center;
      gap: 0.875rem;
      min-height: 2.375rem;
      padding: 0.1875rem 0.5rem;
      border-radius: 0.5rem;
      font-size: 0.875rem;
      color: rgb(var(--ap-ink));
      text-decoration: none;
    }
    .an-row-track {
      position: relative;
      display: flex;
      flex: 1 1 auto;
      align-items: center;
      gap: 0.625rem;
      min-width: 0;
      min-height: 2rem;
      padding: 0 0.5rem;
    }
    .an-row-track[style]::before {
      content: "";
      position: absolute;
      inset-block: 0;
      inset-inline-start: 0;
      width: max(2.75rem, var(--w));
      border-radius: 0.4375rem;
      background: var(--an-wash);
      transition: background 0.2s;
    }
    .an-row-track > * { position: relative; }
    a.an-row-in:hover .an-row-track::before { background: var(--an-wash-hover); }
    a.an-row-in:hover { background: var(--ap-tint-2); }
    a.an-row-in:focus-visible { outline: 2px solid var(--brand-blue); outline-offset: -2px; }
    .an-row-label {
      flex: 1 1 auto;
      min-width: 0;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
    .an-row-label.is-mono { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 0.8125rem; }
    .an-row-sub { margin-inline-start: 0.5rem; font-size: 0.75rem; color: rgb(var(--ap-ink-3)); }
    .an-row-value { flex: none; font-weight: 600; font-variant-numeric: tabular-nums; color: rgb(var(--ap-ink)); }
    .an-row-share { flex: none; width: 2.5rem; margin-inline-start: -0.375rem; text-align: end; font-size: 0.75rem; font-variant-numeric: tabular-nums; color: rgb(var(--ap-ink-2)); }
    .an-row-thumb { flex: none; width: 1.625rem; height: 1.625rem; margin-inline-start: -0.25rem; border-radius: 0.3125rem; object-fit: cover; box-shadow: 0 0 0 1px var(--ap-hairline); }
    .an-row-icon {
      flex: none;
      display: grid;
      place-items: center;
      width: 1.25rem;
      height: 1.25rem;
      color: rgb(var(--ap-ink-3));
    }
    .an-row-icon svg { width: 1.125rem; height: 1.125rem; }
    .an-row-flag { flex: none; display: grid; place-items: center; width: 1.25rem; height: 1.25rem; }
    .an-row-flag .iti__flag { box-shadow: 0 0 0 1px var(--ap-hairline); border-radius: 2px; }
    .an-row-go { flex: none; width: 0.875rem; height: 0.875rem; color: var(--brand-blue); opacity: 0; transition: opacity 0.2s; }
    a.an-row-in:hover .an-row-go, a.an-row-in:focus-visible .an-row-go { opacity: 1; }
    .an-more {
      align-self: flex-start;
      display: inline-flex;
      align-items: center;
      gap: 0.375rem;
      margin-top: 0.625rem;
      border: 0;
      padding: 0.25rem 0;
      background: none;
      font-size: 0.8125rem;
      font-weight: 600;
      /* The darker blue in light: the brand blue is 3.6:1 as small text on a card. */
      color: var(--brand-blue-dark);
      cursor: pointer;
    }
    .dark .an-more { color: var(--brand-blue); }
    .an-more-count { border-radius: 999px; padding: 0 0.375rem; font-size: 0.6875rem; background: var(--an-wash); }
    .an-more[aria-expanded="true"] .an-more-count { display: none; }
    .an-help { font-size: 0.8125rem; color: rgb(var(--ap-ink-2)); }
    .an-help summary { cursor: pointer; font-weight: 500; width: fit-content; }
    .an-help summary:hover { color: rgb(var(--ap-ink)); }
    .an-help dl { display: grid; gap: 0.5rem; margin: 0.75rem 0 0; }
    .an-help dt { display: inline; font-weight: 600; color: rgb(var(--ap-ink-2)); }
    .an-help dt::after { content: " "; }
    .an-help dd { display: inline; margin: 0; }

    /* Devices: one bar split by share, a 2px gap between parts, and the parts named below. */
    .an-split { display: flex; gap: 2px; height: 0.75rem; margin: 0.25rem 0 1rem; }
    .an-split > span { min-width: 0.375rem; flex-basis: 0; }
    .an-split > span:first-child { border-start-start-radius: 999px; border-end-start-radius: 999px; }
    .an-split > span:last-child { border-start-end-radius: 999px; border-end-end-radius: 999px; }
    .an-split .is-mobile, .an-swatch.is-mobile { background: var(--an-mobile); }
    .an-split .is-desktop, .an-swatch.is-desktop { background: var(--an-desktop); }
    .an-split .is-tablet, .an-swatch.is-tablet { background: var(--an-tablet); }
    .an-split .is-unknown, .an-swatch.is-unknown { background: var(--an-other); }
    .an-device-list { display: grid; gap: 0.125rem; margin: 0; padding: 0; list-style: none; }
    .an-device-list li { display: flex; align-items: center; gap: 0.625rem; min-height: 2.25rem; font-size: 0.875rem; color: rgb(var(--ap-ink)); }
    .an-swatch { flex: none; width: 0.625rem; height: 0.625rem; border-radius: 3px; }
    .an-device-name { flex: 1 1 auto; min-width: 0; }
    .an-device-share { font-weight: 600; font-variant-numeric: tabular-nums; }
    .an-device-count { width: 4rem; text-align: end; font-size: 0.75rem; font-variant-numeric: tabular-nums; color: rgb(var(--ap-ink-2)); }

    /* Two figures at the head of a card (promo codes). */
    .an-minifigs { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; margin-bottom: 1rem; padding: 0.875rem 1rem; border-radius: 0.75rem; background: var(--ap-tint-sunken); box-shadow: inset 0 0 0 1px var(--ap-hairline); }
    .an-minifigs .an-fig-label { line-height: 1.25rem; }
    .an-mini-value { margin: 0.125rem 0 0; font-size: 1.25rem; font-weight: 650; color: rgb(var(--ap-ink)); }
    .an-mini-value span { font-size: 0.875rem; font-weight: 500; color: rgb(var(--ap-ink-3)); }
    .an-code { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 0.8125rem; font-weight: 600; color: rgb(var(--ap-ink)); }
    .an-table th:first-child, .an-table td:first-child { padding-inline-start: 0; }
    .an-table th:last-child, .an-table td:last-child { padding-inline-end: 0; }
    @media (min-width: 640px) {
      .page-card.is-flush .an-table th:first-child, .page-card.is-flush .an-table td:first-child { padding-inline-start: 1.25rem; }
      .page-card.is-flush .an-table th:last-child, .page-card.is-flush .an-table td:last-child { padding-inline-end: 1.25rem; }
    }
    @media (max-width: 639.98px) {
      .an-table th:first-child, .an-table td:first-child, .an-table th:last-child, .an-table td:last-child { padding-inline: 0; }
    }
    .an-subtitle { margin: 1.5rem 0 0.25rem; font-size: 0.8125rem; font-weight: 600; color: rgb(var(--ap-ink-2)); }

    /* A funnel: steps left to right, the rate from the step before on the arrow between. On a
       phone the steps stand two by two. */
    .an-steps { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.625rem; margin: 0; padding: 0; list-style: none; }
    @media (min-width: 900px) { .an-steps { grid-template-columns: repeat(var(--n), minmax(0, 1fr)); gap: 1.75rem; } }
    @media (max-width: 899.98px) { .an-steps > .an-step:last-child:nth-child(odd) { grid-column: 1 / -1; } }
    .an-step { position: relative; border-radius: 0.875rem; padding: 0.875rem 1rem 1rem; background: var(--ap-tint-sunken); box-shadow: inset 0 0 0 1px var(--ap-hairline); min-width: 0; }
    .an-step-arrow { display: none; }
    @media (min-width: 900px) {
      .an-step-arrow { position: absolute; top: 50%; inset-inline-start: -1.5rem; display: grid; place-items: center; width: 1.25rem; height: 1.25rem; transform: translateY(-50%); color: rgb(var(--ap-ink-4)); }
      .an-step-arrow svg { width: 1rem; height: 1rem; }
      [dir="rtl"] .an-step-arrow svg { transform: scaleX(-1); }
    }
    .an-step-label { margin: 0; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; color: rgb(var(--ap-ink-3)); }
    .an-step-value { margin: 0.25rem 0 0; font-size: 1.625rem; font-weight: 700; letter-spacing: -0.01em; color: rgb(var(--ap-ink)); }
    .an-step-rate { display: inline-block; margin: 0.375rem 0 0; border-radius: 999px; padding: 0.0625rem 0.5rem; font-size: 0.75rem; font-weight: 600; background: var(--an-wash); color: rgb(var(--ap-ink-2)); }
    .an-step-note { margin: 0.375rem 0 0; font-size: 0.75rem; color: rgb(var(--ap-ink-3)); }
    .an-roas { display: inline-flex; flex-wrap: wrap; align-items: baseline; gap: 0.375rem; border-radius: 999px; padding: 0.25rem 0.75rem; font-size: 0.8125rem; background: var(--ap-tint-2); color: rgb(var(--ap-ink-2)); }
    .an-roas.is-good { background: var(--an-up-bg); color: var(--an-up); }
    .an-roas.is-warn { background: rgb(245 158 11 / 0.12); color: #b45309; }
    .dark .an-roas.is-warn { color: #fbbf24; }

    /* Check-ins: a meter of arrivals against tickets, and the list of nights beside the types. */
    .an-meter { position: relative; height: 0.5rem; margin-top: 0.875rem; max-width: 16rem; border-radius: 999px; background: var(--an-wash); overflow: hidden; }
    .an-meter > span { position: absolute; inset-block: 0; inset-inline-start: 0; border-radius: 999px; background: var(--brand-blue); }
    .an-meter.is-small { display: inline-block; width: 4.5rem; height: 0.375rem; margin: 0; vertical-align: middle; }
    .an-rate { display: inline-flex; align-items: center; gap: 0.625rem; white-space: nowrap; }
    .an-col-rate { width: 10rem; text-align: end; }
    .an-pair { display: grid; gap: 1rem; grid-template-columns: minmax(0, 1fr); align-items: start; }
    @media (min-width: 1280px) { .an-pair.has-side { grid-template-columns: minmax(0, 1fr) minmax(0, 2fr); } }
    @media (max-width: 639.98px) { .an-col-rate { width: auto; text-align: start; } }

    /* Nothing to show yet: the card the numbers will fill, drawn quiet, with the words and the
       one thing to do over it. A full-width card holding one sentence said less than this. */
    .an-ghost { position: relative; overflow: hidden; }
    .an-ghost-figs { display: flex; flex-wrap: wrap; gap: 1rem 3rem; opacity: 0.55; }
    .an-ghost-figs .an-fig { border: 0; padding: 0; flex: none; }
    .an-ghost-figs .an-fig-value { color: rgb(var(--ap-ink-4)); }
    .an-ghost-plot {
      position: relative;
      height: 11rem;
      margin-top: 1.5rem;
      border-top: 1px solid var(--ap-hairline);
      background: repeating-linear-gradient(to bottom, transparent 0, transparent calc(25% - 1px), var(--ap-hairline) calc(25% - 1px), var(--ap-hairline) 25%);
      opacity: 0.8;
    }
    .an-ghost-plot::after { content: ""; position: absolute; inset-inline: 0; bottom: 0; height: 2px; border-radius: 2px; background: rgb(var(--ap-border-strong)); }
    .an-ghost-say {
      position: absolute;
      inset: 0;
      display: grid;
      place-content: center;
      justify-items: center;
      gap: 0.5rem;
      padding: 1.5rem;
      text-align: center;
      background: radial-gradient(closest-side, rgb(var(--ap-surface)) 55%, rgb(var(--ap-surface) / 0.6) 80%, transparent);
    }
    .an-ghost-say h2 { margin: 0; font-size: 1.0625rem; font-weight: 650; color: rgb(var(--ap-ink)); text-wrap: balance; }
    .an-ghost-say p { max-width: 30rem; margin: 0; font-size: 0.875rem; color: rgb(var(--ap-ink-2)); text-wrap: pretty; }
    .an-ghost-say .an-ghost-act { margin-top: 0.5rem; }
</style>