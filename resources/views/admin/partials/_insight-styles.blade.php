{{-- What the platform admin's reporting pages (users, revenue, analytics, usage, growth,
     referrals, domains) needed beyond the page kit (partials/admin-page-styles): a strip of seven
     figures that stays on one line of a laptop, a bar, a legend, a panel that needs a person.
     Written like the kit, plain CSS on the portal's tokens, and included from each page's head
     slot, which the layout prints after the kit. --}}
@once
<style {!! nonce_attr() !!}>
    /* Seven figures fit one line of a laptop at this floor; the kit's nine rem put the seventh
       on a line of its own. A phone keeps the kit's two columns. */
    @media (min-width: 640px) {
      .page-stats.is-auto.insight-strip {
        grid-template-columns: repeat(auto-fit, minmax(7.25rem, 1fr));
      }
    }
    /* A strip under a chart, inside the same card: the figures the chart draws. */
    .insight-strip-top {
      border-top: 1px solid rgb(var(--ap-border));
    }
    .insight-pad {
      padding: 1.25rem;
    }

    /* When a card's figures are for: "Selected period", "All time". Quiet, at the end of the
       card's head, as the dashboard's cards say "Last 30 days". */
    .insight-when {
      font-size: 0.75rem;
      white-space: nowrap;
      color: rgb(var(--ap-ink-3));
    }

    /* A panel that needs a person: the notice says what is wrong, the list under it says which. */
    .insight-attention {
      display: grid;
      gap: 0.5rem;
      min-width: 0;
      scroll-margin-top: 1rem;
    }
    .insight-attention > * {
      min-width: 0;
    }

    /* A share of a whole, as a bar. One colour: amber and grey are for a bar that means
       something else than the rest (a schedule that sold before, a bucket of nothing). */
    .insight-bars {
      display: grid;
      gap: 0.875rem;
    }
    .insight-bar-head {
      display: flex;
      align-items: baseline;
      justify-content: space-between;
      gap: 1rem;
      margin-bottom: 0.375rem;
      font-size: 0.875rem;
    }
    .insight-bar-name {
      min-width: 0;
      font-weight: 500;
      color: rgb(var(--ap-ink));
    }
    .insight-bar-figure {
      flex: none;
      font-variant-numeric: tabular-nums;
      color: rgb(var(--ap-ink-3));
    }
    .insight-bar-figure b {
      font-weight: 600;
      color: rgb(var(--ap-ink));
    }
    .insight-bar {
      height: 0.5rem;
      overflow: hidden;
      border-radius: 999px;
      background: var(--ap-tint-2);
    }
    .insight-bar > i {
      display: block;
      height: 100%;
      border-radius: inherit;
      background: var(--brand-button-bg);
    }
    .insight-bar > i.is-warn {
      background: #f59e0b;
    }
    .insight-bar > i.is-quiet {
      background: rgb(var(--ap-ink-4));
    }

    /* A chart's legend, as the kit's pairs with the series' own colour beside each name. */
    .insight-dot {
      display: inline-block;
      width: 0.625rem;
      height: 0.625rem;
      margin-inline-end: 0.5rem;
      border-radius: 50%;
    }
    .insight-donut {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 1.25rem 1.5rem;
    }
    .insight-donut > .insight-donut-chart {
      flex: none;
      width: 11rem;
      height: 11rem;
      margin: 0 auto;
    }
    .insight-donut > .page-kv {
      flex: 1 1 12rem;
    }
    .page-kv dd small {
      margin-inline-start: 0.375rem;
      font-size: 0.8125rem;
      font-weight: 400;
      color: rgb(var(--ap-ink-3));
    }
    /* A pair that belongs to the one above it (a reason under "Cancelled"). */
    .page-kv > div.is-sub {
      border-top: 0;
      padding: 0 0 0.375rem;
      padding-inline-start: 1rem;
      font-size: 0.8125rem;
    }
    .page-kv > div.is-sub dt {
      color: rgb(var(--ap-ink-3));
    }
    .page-kv > div.is-sub dd {
      font-weight: 500;
      color: rgb(var(--ap-ink-2));
    }
    .page-kv + .page-kv {
      margin-top: 1rem;
      border-top: 1px solid rgb(var(--ap-border-strong));
      padding-top: 1rem;
    }

    /* A card that fills the row when its neighbour has nothing to show and is left out. */
    .page-grid2 > :only-child {
      grid-column: 1 / -1;
    }
    /* A card whose list is flush but whose first lines are prose. */
    .page-card.is-flush > .insight-pad + .page-scroll,
    .page-card.is-flush > .insight-pad + .page-table {
      border-top: 1px solid rgb(var(--ap-border));
    }
    /* An arrow between two steps points the way the text runs. */
    [dir="rtl"] .insight-arrow {
      display: inline-block;
      transform: scaleX(-1);
    }

    /* A list narrowed by one of a few states: every state in sight, the one in force pressed in.
       The look of the kit's chips (a venue's shortcuts on the event form), as links. */
    .page-filters .event-chips-label {
      align-self: center;
    }
    .page-pill {
      display: inline-flex;
      align-items: center;
      border: 1px solid rgb(var(--ap-border-strong));
      border-radius: 999px;
      padding: 0.25rem 0.75rem;
      background: rgb(var(--ap-surface));
      font-size: 0.8125rem;
      font-weight: 500;
      white-space: nowrap;
      color: rgb(var(--ap-ink-2));
      transition: border-color 0.2s, color 0.2s, background-color 0.2s;
    }
    .page-pill:hover {
      border-color: var(--brand-blue);
      color: var(--brand-blue);
    }
    .page-pill:focus-visible {
      outline: 2px solid var(--brand-blue);
      outline-offset: 2px;
    }
    .page-pill[aria-current="true"] {
      border-color: var(--brand-blue);
      background: var(--ap-tint-2);
      box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.08);
      color: var(--brand-blue);
    }
    .dark .page-pill[aria-current="true"] {
      box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.5);
    }

    /* The layout gives every select 1rem of padding on all sides with !important, which is the
       room its arrow needs: "Last 30 Days" ran under its own chevron. Said again for the selects
       of a page's head and of a row of filters, as the kit does for the one inside a list. */
    .page-head .page-actions select,
    .page-filters select {
      padding-block: 0.5rem !important;
      padding-inline: 0.75rem 2.25rem !important;
      font-size: 0.875rem !important;
      line-height: 1.25rem !important;
    }
    .page-filters input[type="text"] {
      padding: 0.5rem 0.75rem !important;
      font-size: 0.875rem !important;
      line-height: 1.25rem !important;
    }

    /* From a tablet up a long word breaks only when it has to. The kit lets these cells break
       anywhere, which is right in a phone's stacked row; in a table it also tells the browser
       the column may be one letter wide, and a name or an address was given a column narrower
       than itself beside empty ones ("owner@example.te" and "st"). */
    @media (min-width: 640px) {
      .page-table .c-wrap,
      .page-table .c-sub {
        overflow-wrap: break-word;
      }
    }

    /* A chip that opens its cell, or its line, has nothing before it to stand off from. */
    .page-table td > .event-chip:first-child,
    .page-table .c-sub > .event-chip:first-child {
      margin-inline-start: 0;
    }
</style>
@endonce
