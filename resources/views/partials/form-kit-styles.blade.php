{{-- The form vocabulary the event form set, in one place for the three tabbed forms (the event
     form, the schedule form, the settings page): a tab's name with what it holds under it, rows
     that open in place, tiles and pills for a choice, one line per list item, and one save bar.
     The class names keep their event- prefix because the event form's markup, tests and browser
     journeys are written against them. Colours come from the --ap-* tokens, so all six palettes
     follow. A page includes this BEFORE its own <style>, so its own rules win a tie. --}}
<style {!! nonce_attr() !!}>
    [v-cloak] { display: none !important; }
    [v-cloak] { display: none !important; }

    /* The Event tab is several cards, so its section drops the one-card look the others share
       (resources/css/app.css, .section-content - also used by the schedule and profile forms, so
       it is overridden here, never edited there). */
    .section-content.section-plain {
      padding: 0;
      background: none;
      box-shadow: none;
      border-radius: 0;
    }
    .section-content.section-plain::before {
      content: none;
    }
    /* The event form sizes every button in it for a text button; these are links in a button's clothes. */
    button.event-link,
    form button.event-link {
      min-width: 0;
      min-height: 0;
      padding: 0;
      border: 0;
      background: none;
      font-size: 0.8125rem;
      font-weight: 500;
      color: var(--brand-blue);
    }
    button.event-link:hover,
    form button.event-link:hover {
      text-decoration: underline;
    }
    .event-pills {
      display: inline-flex;
      gap: 0.375rem;
    }
    .event-pill span {
      display: inline-flex;
      align-items: center;
      gap: 0.375rem;
      border: 1px solid rgb(var(--ap-border-strong));
      border-radius: 9999px;
      padding: 0.25rem 0.75rem;
      font-size: 0.8125rem;
      font-weight: 500;
      color: rgb(var(--ap-ink-3));
      cursor: pointer;
      transition: all 0.2s;
    }
    .event-pill input:checked + span {
      border-color: var(--brand-blue);
      background: var(--brand-blue-a10);
      color: var(--brand-blue);
    }
    .event-pill input:checked + span::before {
      content: "\2713";
    }
    .event-pill input:focus-visible + span {
      outline: 2px solid var(--brand-blue);
      outline-offset: 2px;
    }
    .event-pill input:disabled + span {
      cursor: not-allowed;
      opacity: 0.6;
    }
    .event-picked {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 0.75rem;
      border: 1px solid rgb(var(--ap-border));
      border-radius: 0.5rem;
      padding: 0.6875rem 0.875rem;
      background: rgb(var(--ap-surface));
    }
    button.event-row,
    form button.event-row {
      display: flex;
      align-items: center;
      gap: 0.875rem;
      width: 100%;
      min-height: 3.25rem;
      padding: 0.5rem 1.25rem;
      border: 0;
      border-radius: 0.75rem;
      background: none;
      text-align: start;
      transition: background-color 0.2s;
    }
    button.event-row:hover,
    form button.event-row:hover {
      background: var(--ap-tint-1);
    }
    .event-row-icon {
      width: 1.25rem;
      height: 1.25rem;
      flex: none;
      color: rgb(var(--ap-ink-3));
    }
    .event-row-title {
      flex: none;
      font-size: 0.875rem;
      font-weight: 600;
      color: rgb(var(--ap-ink));
    }
    .event-row-summary {
      flex: 1;
      min-width: 0;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      font-size: 0.875rem;
      color: rgb(var(--ap-ink-3));
    }
    .event-row-chevron {
      width: 1.125rem;
      height: 1.125rem;
      flex: none;
      margin-inline-start: auto;
      color: rgb(var(--ap-ink-3));
      transition: transform 0.2s;
    }
    .event-row[aria-expanded="true"] .event-row-chevron {
      transform: rotate(180deg);
    }
    .event-row-body {
      padding: 0.5rem 1.5rem 1.5rem;
    }
    .event-fieldset {
      min-width: 0;
    }
    .event-tickets-wide {
      max-width: 48rem;
    }
    /* A tab: its title row, its lists, its small parts. */
    .event-tab-title {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      margin-bottom: 1.25rem;
    }
    .event-tab-aside {
      font-size: 0.8125rem;
      color: rgb(var(--ap-ink-3));
    }
    .event-empty {
      margin: 0 0 0.75rem;
      font-size: 0.875rem;
      color: rgb(var(--ap-ink-3));
    }
    .event-list {
      border-top: 1px solid rgb(var(--ap-border));
    }
    .event-list-row {
      display: flex;
      align-items: flex-start;
      gap: 0.75rem;
      padding: 0.75rem 0;
      border-bottom: 1px solid rgb(var(--ap-border));
    }
    .event-list-row.is-centered {
      align-items: center;
    }
    .event-avatar {
      display: flex;
      flex: none;
      align-items: center;
      justify-content: center;
      width: 2.25rem;
      height: 2.25rem;
      border-radius: 0.625rem;
      background: var(--ap-tint-2);
      font-size: 0.875rem;
      font-weight: 600;
      color: rgb(var(--ap-ink-2));
    }
    .event-list-name {
      font-size: 0.9375rem;
      font-weight: 600;
      color: rgb(var(--ap-ink));
    }
    .event-list-sub {
      font-size: 0.8125rem;
      color: rgb(var(--ap-ink-3));
    }
    .event-chip {
      display: inline-block;
      margin-inline-start: 0.5rem;
      border-radius: 999px;
      padding: 0.0625rem 0.5rem;
      background: var(--ap-tint-2);
      font-size: 0.6875rem;
      font-weight: 600;
      color: rgb(var(--ap-ink-3));
      vertical-align: middle;
    }
    .event-list-actions {
      display: flex;
      flex: none;
      align-items: center;
      gap: 0.875rem;
    }
    .event-list-row.is-centered .event-list-actions {
      padding-top: 0;
    }
    button.event-link.is-danger,
    form button.event-link.is-danger {
      color: #dc2626;
    }
    .dark button.event-link.is-danger,
    .dark form button.event-link.is-danger {
      color: #f87171;
    }
    .event-add-box {
      margin-top: 0.875rem;
      border: 1px solid rgb(var(--ap-border));
      border-radius: 0.75rem;
      padding: 1rem;
      background: var(--ap-tint-1);
    }
    .event-grid2 {
      display: grid;
      grid-template-columns: minmax(0, 1fr);
      gap: 0.75rem;
    }
    @media (min-width: 640px) {
      .event-grid2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
    }
    button.event-icon-btn,
    form button.event-icon-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 0;
      min-height: 0;
      width: 1.75rem;
      height: 1.75rem;
      border: 0;
      border-radius: 0.375rem;
      padding: 0;
      background: none;
      color: rgb(var(--ap-ink-3));
    }
    button.event-icon-btn:hover,
    form button.event-icon-btn:hover {
      background: var(--ap-tint-2);
      color: rgb(var(--ap-ink));
    }
    button.event-icon-btn:disabled,
    form button.event-icon-btn:disabled {
      opacity: 0.3;
    }
    .event-drag {
      display: inline-flex;
      cursor: grab;
      color: rgb(var(--ap-ink-4));
    }
    button.event-icon-btn.is-remove:hover,
    form button.event-icon-btn.is-remove:hover {
      color: #dc2626;
    }
    /* A row's summary goes under its title on a phone, in full, where it was cut off beside it. */
    @media (max-width: 639px) {
      button.event-subrow,
      form button.event-subrow {
        flex-wrap: wrap;
        row-gap: 0;
        padding: 0.625rem 0;
      }
      .event-subrow .event-row-title {
        min-width: 0;
        flex: 1;
      }
      .event-subrow .event-row-summary {
        order: 3;
        flex: none;
        width: 100%;
        white-space: normal;
      }
    }
    .event-seg {
      box-shadow: inset 0 0 0 1px rgb(var(--ap-border));
    }
    .event-check-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(13rem, 1fr));
      gap: 0.625rem 1rem;
    }
    .event-hint {
      margin: 0.25rem 0 0.75rem;
      font-size: 0.8125rem;
      line-height: 1.45;
      color: rgb(var(--ap-ink-2));
    }
    .event-status {
      display: inline-flex;
      align-items: center;
      gap: 0.375rem;
      font-size: 0.8125rem;
      color: rgb(var(--ap-ink-3));
    }
    .event-status::before {
      content: "";
      width: 0.5rem;
      height: 0.5rem;
      border-radius: 50%;
      background: rgb(var(--ap-ink-4));
    }
    .event-status.is-on {
      color: #15803d;
    }
    .dark .event-status.is-on {
      color: #4ade80;
    }
    .event-status.is-on::before {
      background: #22c55e;
    }
    .event-setting {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 0.375rem 1rem;
      padding: 0.375rem 0;
    }
    .event-setting-label {
      font-size: 0.875rem;
      font-weight: 500;
      color: rgb(var(--ap-ink));
    }
    .event-slug-field {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    .event-slug-prefix {
      flex: none;
      max-width: 60%;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      font-size: 0.875rem;
      color: rgb(var(--ap-ink-3));
    }
    .event-group-label {
      margin: 0 0 0.5rem;
      font-size: 0.75rem;
      font-weight: 600;
      letter-spacing: 0.04em;
      text-transform: uppercase;
      color: rgb(var(--ap-ink-3));
    }
    .event-badge-count {
      display: inline-flex;
      flex: none;
      align-items: center;
      justify-content: center;
      min-width: 1.25rem;
      height: 1.25rem;
      border-radius: 999px;
      padding: 0 0.375rem;
      background: #ef4444;
      font-size: 0.6875rem;
      font-weight: 700;
      color: #fff;
    }
    /* Rows that open in place. */
    .event-subrows {
      margin-top: 1.25rem;
      border-top: 1px solid rgb(var(--ap-border));
    }
    button.event-subrow,
    form button.event-subrow {
      display: flex;
      align-items: center;
      gap: 0.875rem;
      width: 100%;
      min-height: 3rem;
      padding: 0.5rem 0;
      border: 0;
      border-bottom: 1px solid rgb(var(--ap-border));
      background: none;
      text-align: start;
    }
    button.event-subrow:hover .event-row-title,
    form button.event-subrow:hover .event-row-title {
      color: var(--brand-blue);
    }
    .event-subrow[aria-expanded="true"] {
      border-bottom-color: transparent;
    }
    .event-subrow[aria-expanded="true"] .event-row-chevron {
      transform: rotate(180deg);
    }
    .event-subrow .event-row-title {
      min-width: 7rem;
    }
    .event-subrow .event-row-summary.is-warn,
    .event-row .event-row-summary.is-warn {
      color: #b45309;
      font-weight: 500;
    }
    .dark .event-subrow .event-row-summary.is-warn,
    .dark .event-row .event-row-summary.is-warn {
      color: #fcd34d;
    }
    /* What a row has chosen, as a small swatch before its words (form-row's chip). */
    .event-row-chip {
      display: inline-block;
      vertical-align: -0.25rem;
      width: 1.75rem;
      height: 1.125rem;
      margin-inline-end: 0.5rem;
      border-radius: 0.3125rem;
      background-size: cover;
      background-position: center;
      box-shadow: inset 0 0 0 1px rgb(var(--ap-border-strong));
    }
    .event-subrow-body {
      padding: 0.75rem 0 0.5rem;
      border-bottom: 1px solid rgb(var(--ap-border));
    }
    /* On a phone the title row is shared with Actions: the link keeps its path and lets the host go. */
    @media (max-width: 639px) {
      .event-url-strip {
        flex-wrap: wrap;
        row-gap: 0.125rem;
      }
      .event-url-host {
        display: none;
      }
    }
    /* What kind of page this is, over its title, in the brand colour (the 2026-11 redesign). */
    .event-eyebrow {
      margin-bottom: 0.375rem;
      font-size: 0.9375rem;
      font-weight: 500;
      color: var(--brand-blue);
    }
    .section-nav-text {
      text-align: start;
    }
    .section-nav-summary.is-wrap {
      white-space: normal;
    }
    .event-save-status .event-save-quiet {
      color: rgb(var(--ap-ink-3));
    }

    /* Shortcuts to a saved venue. */
    .event-chips {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 0.375rem;
      margin-bottom: 0.75rem;
    }
    .event-chips-label {
      margin-inline-end: 0.25rem;
      font-size: 0.8125rem;
      color: rgb(var(--ap-ink-3));
    }
    button.event-chip,
    form button.event-chip {
      max-width: 100%;
      min-width: 0;
      min-height: 0;
      overflow: hidden;
      border: 1px solid rgb(var(--ap-border-strong));
      border-radius: 9999px;
      padding: 0.25rem 0.75rem;
      background: rgb(var(--ap-surface));
      font-size: 0.8125rem;
      font-weight: 500;
      text-overflow: ellipsis;
      white-space: nowrap;
      color: rgb(var(--ap-ink));
      transition: all 0.2s;
    }
    button.event-chip:hover,
    form button.event-chip:hover {
      border-color: var(--brand-blue);
      color: var(--brand-blue);
    }

    /* The public link under the page title. The host gives way before the path does, so the part
       that names the event is the part that survives a narrow screen. */
    .event-url-strip {
      display: flex;
      align-items: baseline;
      gap: 0.875rem;
      min-width: 0;
      margin-top: 0.25rem;
      font-size: 0.875rem;
    }
    .event-url-text {
      display: flex;
      min-width: 0;
      color: rgb(var(--ap-ink-2));
      unicode-bidi: isolate;
    }
    .event-url-host,
    .event-url-path {
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
    .event-url-host {
      flex: 0 1000 auto;
      min-width: 2.5rem;
    }
    .event-url-path {
      flex: 0 1 auto;
      min-width: 0;
    }
    .event-url-strip a.event-link {
      flex: none;
      font-size: 0.875rem;
      font-weight: 600;
      color: var(--brand-blue);
    }
    /* View opens the public page in a new tab, and says so with an arrow (the 2026-11 redesign).
       Drawn here so every page's strip has it; a link that prints its own arrow is left alone.
       The empty alternative keeps a screen reader from reading the arrow out. */
    .event-url-strip a.event-link[target="_blank"]:not(:has([aria-hidden]))::after {
      content: "\2197";
      content: "\2197" / "";
      margin-inline-start: 0.25em;
    }
    .event-url-strip a.event-link:hover {
      text-decoration: underline;
    }
    .event-url-strip button.event-link {
      flex: none;
      padding: 0;
      border: 0;
      background: none;
      font-size: 0.875rem;
      font-weight: 600;
      color: var(--brand-blue);
    }
    .event-url-strip button.event-link:hover {
      text-decoration: underline;
    }

    /* The saved-visibility badge beside the page title. */
    button.event-badge {
      cursor: pointer;
    }
    .event-badge {
      flex: none;
      border: 1px solid rgb(var(--ap-border));
      border-radius: 9999px;
      padding: 0 0.625rem;
      background: rgb(var(--ap-bg));
      font-size: 0.75rem;
      font-weight: 600;
      line-height: 1.375rem;
      color: rgb(var(--ap-ink-2));
    }
    .event-badge.is-public {
      border-color: #86efac;
      background: #f0fdf4;
      color: #166534;
    }
    .event-badge.is-draft {
      border-color: #fcd34d;
      background: #fffbeb;
      color: #92400e;
    }
    .dark .event-badge.is-public {
      border-color: #15803d;
      background: rgba(20, 83, 45, 0.25);
      color: #bbf7d0;
    }
    .dark .event-badge.is-draft {
      border-color: #b45309;
      background: rgba(120, 53, 15, 0.25);
      color: #fde68a;
    }
    /* A row's one-line summary. */
    .event-row-summary.is-empty {
      color: rgb(var(--ap-ink-2));
    }
    .event-row-summary:not(.is-empty) {
      color: rgb(var(--ap-ink-2));
    }
    .event-row-link {
      flex: none;
      margin-inline-end: 1.25rem;
      font-size: 0.8125rem;
      font-weight: 500;
      color: var(--brand-blue);
    }
    .event-row-link:hover {
      text-decoration: underline;
    }

    /* The ticket choice. */
    .event-tiles {
      display: grid;
      grid-template-columns: minmax(0, 1fr);
      gap: 0.625rem;
    }

    /* The Tickets tab, as the 2026-11 redesign draws it. Four tiles ("No tickets" is one of them),
       two across on a phone and four from a laptop. */
    .event-tiles.event-tiles-4 {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    @media (min-width: 1024px) {
      .event-tiles.event-tiles-4 {
        grid-template-columns: repeat(4, minmax(0, 1fr));
      }
    }
    /* Each ticket type is a card of its own, titled, its name first. */
    .event-ticket-card {
      margin-top: 1rem;
      padding: 1rem;
      border: 1px solid rgb(var(--ap-border));
      border-radius: 0.75rem;
      background: rgb(var(--ap-surface));
    }
    .event-ticket-card-title {
      margin: 0 0 0.75rem;
      font-size: 0.9375rem;
      font-weight: 600;
      color: rgb(var(--ap-ink));
    }
    @media (min-width: 640px) {
      /* Zip 16: the name has a line of its own, then price and quantity side by side. */
      .event-ticket-card .event-ticket-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
      .event-ticket-card .event-ticket-grid > .event-ticket-name {
        grid-column: 1 / -1;
      }
      .event-ticket-card .event-ticket-grid > :not(.event-ticket-name):not(.event-ticket-chips) {
        order: 1;
      }
      .event-ticket-card .event-ticket-grid > .event-ticket-chips {
        order: 2;
        grid-column: 1 / -1;
      }
    }
    .event-ticket-card .event-ticket-name {
      order: 0;
    }
    /* What a ticket type can add, as small pills; Remove stays a quiet red word at the end. */
    .event-ticket-chips > button:not(.ms-auto) {
      margin-top: 0;
      padding: 0.3125rem 0.75rem;
      border: 1px solid rgb(var(--ap-border-strong));
      border-radius: 999px;
      font-size: 0.8125rem;
      font-weight: 600;
      line-height: 1.25rem;
      color: rgb(var(--ap-ink));
      background: rgb(var(--ap-surface));
      transition: border-color 0.2s ease, color 0.2s ease;
    }
    .event-ticket-chips > button:not(.ms-auto):hover {
      border-color: var(--brand-blue);
      color: var(--brand-blue);
    }
    /* The 2026-11 redesign of the Tickets tab: a type folds to one line (its name, its price and
       quantity, and Edit), the open one says Close there; the tab's name carries how many are
       sold; its links are blue words; a warning is an amber band with its action at the end. */
    button.event-ticket-head,
    form button.event-ticket-head {
      display: flex;
      align-items: baseline;
      gap: 0.75rem;
      width: 100%;
      margin: 0;
      padding: 0;
      border: 0;
      background: none;
      text-align: start;
      cursor: pointer;
    }
    .event-ticket-card:not(.is-folded) > button.event-ticket-head {
      margin-bottom: 1rem;
    }
    .event-ticket-head-name {
      min-width: 0;
      font-size: 1rem;
      font-weight: 700;
      color: rgb(var(--ap-ink));
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
    .event-ticket-head-meta {
      flex: none;
      font-size: 0.9375rem;
      color: rgb(var(--ap-ink-2));
    }
    .event-ticket-head-act {
      flex: none;
      margin-inline-start: auto;
      font-size: 0.875rem;
      font-weight: 600;
      color: var(--brand-blue);
    }
    .event-ticket-card.is-folded {
      margin-top: 0.625rem;
      padding-block: 0.875rem;
    }
    .event-tickets-sold {
      font-size: 0.875rem;
      font-weight: 400;
      color: rgb(var(--ap-ink-2));
    }
    .event-tickets-sold strong {
      font-weight: 700;
      color: rgb(var(--ap-ink));
    }
    .event-head-link {
      font-size: 0.875rem;
      font-weight: 600;
      color: var(--brand-blue);
    }
    .event-head-link:hover {
      text-decoration: underline;
    }
    .event-warn-band {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 0.25rem 1rem;
      margin-top: 1rem;
      padding: 0.875rem 1rem;
      border-radius: 1rem;
      background: rgb(254 243 199 / 0.8);
      font-size: 0.9375rem;
      color: rgb(var(--ap-ink));
    }
    .dark .event-warn-band {
      background: rgb(120 53 15 / 0.3);
    }
    .event-warn-band-act,
    form button.event-warn-band-act {
      padding: 0;
      border: 0;
      background: none;
      font-size: 0.875rem;
      font-weight: 600;
      color: #92400e;
    }
    .dark .event-warn-band-act {
      color: #fcd34d;
    }
    .event-ticket-types-head {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      gap: 0.25rem 1rem;
      margin: 1.5rem 0 0.25rem;
      font-size: 0.6875rem;
      font-weight: 600;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      color: rgb(var(--ap-ink-3));
    }
    .event-ticket-capacity {
      font-size: 0.8125rem;
      font-weight: 500;
      letter-spacing: 0;
      text-transform: none;
      color: rgb(var(--ap-ink-2));
    }
    .event-ticket-types-head + .event-ticket-card {
      margin-top: 0.5rem;
    }
    #section-tickets .event-subrows {
      padding: 0 1rem;
      border: 1px solid rgb(var(--ap-border));
      border-radius: 0.875rem;
    }

    button.event-ticket-add,
    form button.event-ticket-add {
      width: 100%;
      padding: 0.75rem 1rem;
      border: 1.5px dashed rgb(var(--ap-border-strong));
      border-radius: 0.75rem;
      background: none;
      font-size: 0.9375rem;
      font-weight: 600;
      color: var(--brand-blue);
      transition: border-color 0.2s ease, background-color 0.2s ease;
    }
    button.event-ticket-add:hover,
    form button.event-ticket-add:hover {
      border-color: var(--brand-blue);
      background: var(--brand-blue-a10);
    }
    /* The plan's rule for paid tickets, once, as a tinted note. */
    .event-pro-note {
      margin-top: 1rem;
      padding: 0.875rem 1rem;
      border-radius: 0.75rem;
      background: var(--brand-blue-a10);
      font-size: 0.875rem;
      line-height: 1.45;
      color: rgb(var(--ap-ink-2));
    }
    .event-pro-note strong {
      font-weight: 600;
      color: rgb(var(--ap-ink));
    }
    .event-pro-note-link {
      display: inline-block;
      margin-top: 0.375rem;
      font-weight: 600;
      color: var(--brand-blue);
    }
    .event-pro-note-link:hover {
      text-decoration: underline;
    }
    button.event-tile,
    form button.event-tile {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 0.125rem;
      min-width: 0;
      min-height: 4rem;
      padding: 0.75rem 0.875rem;
      border: 1px solid rgb(var(--ap-border-strong));
      border-radius: 0.75rem;
      background: rgb(var(--ap-surface));
      text-align: start;
      transition: all 0.2s;
    }
    button.event-tile:hover,
    form button.event-tile:hover {
      border-color: var(--brand-blue);
    }
    /* The chosen tile: a 2px blue edge on the card's own white (the 2026-11 design), where it
       was a blue tint with a 1px edge. The second pixel is an inset ring, so nothing moves. */
    button.event-tile.is-on,
    form button.event-tile.is-on {
      border-color: var(--brand-blue);
      background: rgb(var(--ap-surface));
      box-shadow: inset 0 0 0 1px var(--brand-blue);
    }
    .event-tile-title {
      display: flex;
      align-items: center;
      gap: 0.375rem;
      font-size: 0.9375rem;
      font-weight: 600;
      color: rgb(var(--ap-ink));
    }
    .event-tile.is-on .event-tile-title {
      color: var(--brand-blue);
    }
    /* The chosen tile is said by its blue border and title (the 2026-11 redesign); the tick
       stays for a screen reader's sake in aria-pressed, not as a mark. */
    .event-tile-help {
      font-size: 0.8125rem;
      color: rgb(var(--ap-ink-3));
    }
    .event-tile-narrow {
      max-width: 14rem;
    }

    /* Ticket presets (issue #130). On the event form: the owner's chips above the tiles, lit for
       the preset the tab still matches and resting (dashed, not pressable) once an event has
       tickets; a bound team member's preset tiles, frozen the same way, with what the chosen one
       holds under them. On the schedule form: the list and its editor (TicketPresetsEditor.vue). */
    button.event-chip.is-on,
    form button.event-chip.is-on,
    form button.event-chip.is-on:disabled,
    button.event-chip.is-on:disabled:hover,
    form button.event-chip.is-on:disabled:hover {
      border-color: var(--brand-blue);
      background: var(--brand-blue-a10);
      color: var(--brand-blue);
    }
    button.event-chip.is-on::before {
      content: "\2713\00a0";
    }
    button.event-chip:disabled,
    form button.event-chip:disabled {
      border-color: rgb(var(--ap-border));
      border-style: dashed;
      background: transparent;
      color: rgb(var(--ap-ink-3));
      cursor: not-allowed;
    }
    button.event-chip:disabled:hover,
    form button.event-chip:disabled:hover {
      border-color: rgb(var(--ap-border));
      color: rgb(var(--ap-ink-3));
    }
    /* A preset tile that cannot be pressed (its payment cannot be taken), frozen or not. */
    button.event-tile:disabled,
    form button.event-tile:disabled,
    .event-tiles.is-frozen button.event-tile,
    form .event-tiles.is-frozen button.event-tile {
      border-color: rgb(var(--ap-border));
      background: var(--ap-tint-1);
      cursor: not-allowed;
    }
    .event-tiles.is-frozen button.event-tile.is-on,
    form .event-tiles.is-frozen button.event-tile.is-on {
      border-color: var(--brand-blue);
      background: var(--brand-blue-a10);
    }
    .preset-note {
      display: flex;
      align-items: flex-start;
      gap: 0.5rem;
      font-size: 0.8125rem;
      color: rgb(var(--ap-ink-3));
    }
    .preset-picked {
      margin-top: 1rem;
      padding: 0.875rem 1rem;
      border: 1px solid rgb(var(--ap-border));
      border-radius: 0.75rem;
    }
    .preset-picked dl {
      display: grid;
      grid-template-columns: max-content minmax(0, 1fr);
      gap: 0.375rem 1.25rem;
      margin: 0;
      font-size: 0.875rem;
    }
    .preset-picked dt {
      color: rgb(var(--ap-ink-3));
    }
    .preset-picked dd {
      margin: 0;
      min-width: 0;
      overflow-wrap: anywhere;
      color: rgb(var(--ap-ink));
    }
    .preset-row-line {
      display: flex;
      flex-wrap: wrap;
      align-items: baseline;
      gap: 0.5rem;
    }
    .event-list-row.preset-row {
      flex-wrap: wrap;
    }
    .preset-row > .preset-editor {
      flex-basis: 100%;
    }
    .preset-editor {
      margin-top: 0.75rem;
      padding: 1rem;
      border: 1px solid rgb(var(--ap-border));
      border-radius: 0.75rem;
      background: var(--ap-tint-1);
    }
    .preset-types {
      display: grid;
      grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1.4fr) auto;
      align-items: center;
      gap: 0.5rem 0.75rem;
    }
    .preset-types .h {
      font-size: 0.8125rem;
      font-weight: 500;
      color: rgb(var(--ap-ink-2));
    }
    .preset-type {
      display: contents;
    }
    .preset-type .lbl {
      display: none;
    }
    /* A phone: each ticket type its own small box with its own labels, so a price plainly belongs
       to its type. */
    @media (max-width: 639px) {
      .preset-types {
        display: flex;
        flex-direction: column;
        gap: 0.625rem;
      }
      .preset-types > .h {
        display: none;
      }
      .preset-type {
        position: relative;
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 0.5rem 0.75rem;
        padding: 0.75rem;
        border: 1px solid rgb(var(--ap-border));
        border-radius: 0.625rem;
      }
      .preset-type .lbl {
        display: block;
        margin-bottom: 0.125rem;
        font-size: 0.75rem;
        font-weight: 500;
        color: rgb(var(--ap-ink-2));
      }
      .preset-type .cell-type {
        grid-column: 1 / 3;
        grid-row: 1;
        padding-inline-end: 2rem;
      }
      .preset-type .event-icon-btn {
        position: absolute;
        top: 0.5rem;
        inset-inline-end: 0.5rem;
      }
    }
    .preset-switch {
      margin-top: 1.5rem;
      margin-bottom: 1rem;
    }
    .preset-switch.is-off {
      opacity: 0.6;
    }
    button.event-link.event-link-quiet,
    form button.event-link.event-link-quiet {
      color: rgb(var(--ap-ink-3));
    }
    @media (min-width: 640px) {
      .event-tiles:not(.event-tiles-4) {
        grid-template-columns: repeat(3, minmax(0, 1fr));
      }
    }

    /* A tab's name with what it holds underneath it. */
    .section-nav-text {
      display: flex;
      flex: 1;
      flex-direction: column;
      min-width: 0;
    }
    .section-nav-summary {
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      font-size: 0.75rem;
      font-weight: 400;
      line-height: 1.4;
      color: rgb(var(--ap-ink-2));
    }
    .section-nav-summary.is-empty {
      color: rgb(var(--ap-ink-3));
    }
    /* Something to act on (email failing, a setup not finished): the rows' amber, on the tab. */
    .section-nav-summary.is-warn {
      color: #b45309;
      font-weight: 500;
    }
    .dark .section-nav-summary.is-warn {
      color: #fcd34d;
    }
    .section-nav-summary:has(bdi:empty) {
      display: none;
    }
    .section-nav-dot {
      width: 0.5rem;
      height: 0.5rem;
      flex: none;
      border-radius: 9999px;
      background: var(--brand-blue);
    }
    .mobile-section-header > span:first-child {
      flex: 1;
      min-width: 0;
      text-align: start;
    }

    /* The save bar. */
    .event-save-spacer {
      height: 7.5rem;
    }
    /* What is scrolled into view (a field reached with Tab, a row opened from a link, an error
       brought forward) stops above the save bar, never under it (WCAG 2.4.11, focus not
       obscured). The bar is about 5.5rem with its gap; the rest is room to see the field's edge. */
    html:has(.event-save-bar) {
      scroll-padding-bottom: 7rem;
    }
    .event-save-bar {
      position: fixed;
      bottom: 0;
      inset-inline: 0;
      z-index: 40;
      padding: 0.75rem 1.25rem max(0.75rem, env(safe-area-inset-bottom));
      border-top: 1px solid rgb(var(--ap-border));
      background: rgb(var(--ap-surface));
      box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.06);
    }
    .event-save-bar-inner {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }
    .event-save-status {
      min-height: 1.25rem;
      font-size: 0.875rem;
      font-weight: 500;
      color: rgb(var(--ap-ink-2));
    }
    .event-save-status > span {
      display: flex;
      flex-wrap: wrap;
      align-items: baseline;
      justify-content: center;
      gap: 0.125rem 0.375rem;
    }
    .event-save-status .event-save-pair {
      display: inline-flex;
      align-items: baseline;
      gap: 0.375rem;
    }
    .event-save-status .event-save-strong {
      font-weight: 600;
      color: rgb(var(--ap-ink));
    }
    .event-save-status button.event-link,
    form .event-save-status button.event-link {
      font-size: 0.875rem;
      font-weight: 600;
    }
    .event-save-actions {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }
    .event-save-actions .event-bar-save,
    .event-save-actions .event-bar-publish,
    .event-save-actions .event-bar-quiet {
      flex: 1;
    }
    button.event-bar-text,
    form button.event-bar-text {
      min-width: 0;
      padding: 0.75rem 0.5rem;
      border: 0;
      background: none;
      font-size: 0.9375rem;
      font-weight: 500;
      color: rgb(var(--ap-ink-2));
    }
    button.event-bar-text:hover,
    form button.event-bar-text:hover {
      color: rgb(var(--ap-ink));
    }
    /* Nothing to save yet on an existing event: Save is still there and still works, it just stops
       asking to be pressed. */
    button.event-bar-save.is-idle,
    form button.event-bar-save.is-idle {
      opacity: 0.45;
      box-shadow: none;
    }
    button.event-bar-quiet,
    form button.event-bar-quiet {
      border: 1px solid rgb(var(--ap-border-strong));
      background: rgb(var(--ap-surface));
      background-image: none;
      color: rgb(var(--ap-ink));
      box-shadow: none;
    }
    button.event-bar-quiet,
    form button.event-bar-quiet {
      padding: 0.75rem 1rem;
      border-radius: 0.5rem;
      font-size: 1rem;
      font-weight: 600;
    }
    @media (min-width: 1024px) {
      .event-save-spacer {
        height: 0;
      }
      /* A card that floats at the foot of the form (the 2026-11 redesign), not a strip across
         the room. */
      .event-save-bar {
        position: sticky;
        bottom: 0.75rem;
        z-index: 30;
        margin: 1rem 0 0;
        padding: 0.75rem 1rem;
        border: 1px solid rgb(var(--ap-border));
        border-radius: 1rem;
        background: rgb(var(--ap-surface));
        box-shadow: 0 1px 2px rgba(20, 22, 26, 0.06), 0 8px 24px rgba(20, 22, 26, 0.12);
      }
      /* The schedule form and the settings page put their bar in the cards' column, whose
         space-y-0 would sit it flush against the card. */
      #form-save-bar.event-save-bar {
        margin-top: 1rem;
      }
      .event-save-bar-inner {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
      }
      .event-save-status > span {
        justify-content: flex-start;
      }
      .event-save-actions {
        flex: none;
      }
      .event-save-actions .event-bar-save,
      .event-save-actions .event-bar-publish,
      .event-save-actions .event-bar-quiet {
        flex: none;
        min-width: 7.5rem;
      }
    }
    /* A ticket type's description: two lines to start, growing with what is typed (where the
       browser can), up to about eight. */
    .event-ticket-desc textarea {
      min-height: 4.25rem;
      max-height: 12rem;
      field-sizing: content;
    }
    /* A tab holding a field that was refused says so in red. The side menu has no coloured line
       any more (the 2026-11 review: a white tile for the open section and nothing else), so the
       old red edge drew nothing; the name turns red instead, open or not. */
    .section-nav-link.validation-error,
    .section-nav-link.validation-error.nav-active {
      color: #b42318;
    }
    .dark .section-nav-link.validation-error,
    .dark .section-nav-link.validation-error.nav-active {
      color: #f87171;
    }

    /* Mobile accordion styles */
    .mobile-section-header.active .accordion-chevron {
      transform: rotate(180deg);
    }
    .mobile-section-header.active {
      color: var(--brand-blue);
      border-color: var(--brand-blue);
    }
    .mobile-section-header.validation-error {
      border-color: #dc2626 !important;
    }
    @media (prefers-color-scheme: dark) {
      .mobile-section-header.validation-error {
        border-color: #ef4444 !important;
      }
    }
    .dark .mobile-section-header.validation-error {
      border-color: #ef4444 !important;
    }

    /* A section's column: 48rem, where these pages each had their own width. */
    .form-kit-col {
      max-width: 48rem;
    }

    /* A section's heading: its name on one side, one quiet action or note on the other. */
    .form-kit-title {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      margin-bottom: 0.25rem;
      font-size: 1.5rem;
      font-weight: 700;
      letter-spacing: -0.02em;
      color: rgb(var(--ap-ink));
    }

    .form-kit-title > span:first-child {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      min-width: 0;
    }

    /* The 2026-11 redesign names a section in words alone: the tab list beside it has no icons
       either. The same for the headings the schedule form writes by hand. */
    .form-kit-title > span:first-child > svg,
    .section-content .section-heading-name > svg {
      display: none;
    }
    .section-content h2:has(> .section-heading-name) {
      font-size: 1.5rem;
      font-weight: 700;
      letter-spacing: -0.02em;
    }

    /* A row says what pressing it does, in words, where it had a chevron: Add when nothing is set,
       Change when something is, Hide when it is open. The words are the page's own translations
       (--row-add, --row-change and --row-hide, set by layouts/app-admin). */
    .event-subrow .event-row-chevron,
    button.event-row .event-row-chevron {
      display: none;
    }
    .event-subrow::after,
    button.event-row::after {
      content: var(--row-change, "Change");
      flex: none;
      margin-inline-start: auto;
      padding-inline-start: 0.75rem;
      font-size: 0.8125rem;
      font-weight: 600;
      color: var(--brand-blue);
    }
    /* Add only where the line is blank: a line that says a state ("Off", "None") is something to
       change, as the 2026-11 redesign words it. */
    .event-subrow:has(.event-row-summary bdi:empty)::after,
    button.event-row:has(.event-row-summary bdi:empty)::after {
      content: var(--row-add, "Add");
    }
    /* A row whose word is its own ("Set up" on a payment method not yet connected). */
    .event-subrow[data-row-action][data-row-action]::after {
      content: attr(data-row-action);
    }
    .event-subrow[aria-expanded="true"][aria-expanded]::after,
    button.event-row[aria-expanded="true"][aria-expanded]::after {
      content: var(--row-hide, "Hide");
    }

    /* Switches at the end of their line, the name and what it does before them (the 2026-11
       redesign's preferences). */
    .toggles-end > div > .flex.items-center.gap-3 {
      flex-direction: row-reverse;
      justify-content: space-between;
    }
    .toggles-end > div > .flex.items-center.gap-3 > label:last-child {
      font-weight: 600;
      color: rgb(var(--ap-ink));
    }
    .toggles-end > div > p.ms-14 {
      margin-inline-start: 0;
      margin-inline-end: 3.5rem;
      margin-top: 0.125rem;
    }

    /* Settings > Payment methods with nothing connected (the 2026-11 redesign): Stripe on its own,
       with its button, and the other ways listed under it. */
    .payment-hero {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 0.75rem 1rem;
      margin: 0.5rem 0 1.75rem;
      padding: 1.25rem;
      border-radius: 0.875rem;
      /* A pale wash of the brand blue over the card (#f3f6ff on white), so it follows the palette
         and reads as a blue tint in dark mode too. */
      background: color-mix(in srgb, var(--brand-blue) 6%, rgb(var(--ap-surface)));
    }
    .payment-hero-title {
      font-size: 1.125rem;
      font-weight: 600;
      letter-spacing: -0.01em;
      color: rgb(var(--ap-ink));
    }
    .payment-hero-help {
      margin-top: 0.25rem;
      font-size: 0.875rem;
      line-height: 1.45;
      color: rgb(var(--ap-ink-2));
    }
    .payment-other-label {
      margin-bottom: 0.625rem;
      font-size: 0.75rem;
      font-weight: 600;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      color: rgb(var(--ap-ink-3));
    }
    .event-subrow.is-hero-twin:not([aria-expanded="true"]) {
      display: none;
    }
    .settings-title-pill {
      display: inline-flex;
      align-items: center;
      margin-inline-start: 0.5rem;
      padding: 0.125rem 0.5rem;
      border-radius: 999px;
      background: rgb(var(--ap-surface-sunken));
      font-size: 0.75rem;
      font-weight: 600;
      letter-spacing: 0;
      color: rgb(var(--ap-ink-2));
    }

    .form-kit-lead {
      max-width: 40rem;
      margin: 0 0 1.25rem;
      font-size: 0.9375rem;
      line-height: 1.5;
      color: rgb(var(--ap-ink-2));
    }

    .form-kit-title + :not(.form-kit-lead) {
      margin-top: 1.25rem;
    }

    /* A row that is closed has its pane out of the page, whatever the pane's own classes say. */
    .event-subrow-body[hidden] {
      display: none !important;
    }

    .event-subrow .event-row-title {
      white-space: nowrap;
    }

    /* A row the plan does not include: its lock sits where the chevron would. */
    .event-subrow .event-row-lock {
      flex: none;
      margin-inline-start: auto;
    }

    /* The rows of one list share their columns, so every line starts on one edge: each used to
       start where its own title ended. The names take the width of the longest (up to 13rem, a
       longer one wraps), then the line, then a count, a lock and the chevron, each in a column
       of its own so a lock sits in one place down the list. The gaps are margins on what is there,
       so a column no row uses takes no room. A phone keeps the line under the name (above); the
       event graphics page does that at every width, in a column a phone wide. */
    @media (min-width: 640px) {
      @supports (grid-template-columns: subgrid) {
        .event-subrows:not(.gfx-rows .event-subrows) {
          display: grid;
          /* fit-content() cannot sit inside minmax(): the title's own 7rem minimum is the floor. */
          grid-template-columns: fit-content(13rem) minmax(0, 1fr) auto auto auto;
          column-gap: 0;
        }
        .event-subrows:not(.gfx-rows .event-subrows) > * {
          grid-column: 1 / -1;
        }
        .event-subrows:not(.gfx-rows .event-subrows) > button.event-subrow {
          display: grid;
          grid-template-columns: subgrid;
          column-gap: 0;
        }
        .event-subrows:not(.gfx-rows .event-subrows) > button.event-subrow > * {
          grid-row: 1;
        }
        .event-subrows:not(.gfx-rows .event-subrows) > button.event-subrow > .event-row-title {
          grid-column: 1;
          white-space: normal;
        }
        .event-subrows:not(.gfx-rows .event-subrows) > button.event-subrow > .event-row-summary {
          grid-column: 2;
          margin-inline-start: 0.875rem;
          /* A line wraps rather than being cut: beside the Style tab's preview the column is
             narrow, and "Gradient · Custom" came out as "Gradien...". */
          white-space: normal;
        }
        .event-subrows:not(.gfx-rows .event-subrows) > button.event-subrow > .event-badge-count {
          grid-column: 3;
        }
        .event-subrows:not(.gfx-rows .event-subrows) > button.event-subrow > .event-row-lock {
          grid-column: 4;
        }
        .event-subrows:not(.gfx-rows .event-subrows) > button.event-subrow > .event-row-chevron,
        .event-subrows:not(.gfx-rows .event-subrows) > button.event-subrow > .event-row-lock:last-child {
          grid-column: 5;
        }
        .event-subrows:not(.gfx-rows .event-subrows) > button.event-subrow > .event-badge-count,
        .event-subrows:not(.gfx-rows .event-subrows) > button.event-subrow > .event-row-lock,
        .event-subrows:not(.gfx-rows .event-subrows) > button.event-subrow > .event-row-chevron {
          justify-self: end;
          margin-inline-start: 0.75rem;
        }
      }
    }

    /* A stack of fields. As wide as its column, so a field ends where the rows around it end. */
    .form-kit-fields {
      min-width: 0;
    }

    /* hidden means hidden, whatever display class the element also carries: a pane that is
       closed, a form behind its link, a warning with nothing to say yet. */
    .section-content [hidden] {
      display: none !important;
    }

    /* A link dressed as the text action it sits beside. */
    a.event-link {
      font-size: 0.8125rem;
      font-weight: 500;
      color: var(--brand-blue);
    }

    a.event-link:hover {
      text-decoration: underline;
    }

    a.event-link.is-danger {
      color: #dc2626;
    }

    .dark a.event-link.is-danger {
      color: #f87171;
    }

    /* A form that is one text action sits on the line of the actions beside it. */
    .event-list-actions > form {
      display: flex;
      margin: 0;
    }

    .event-status.is-warn {
      color: #b45309;
    }

    .dark .event-status.is-warn {
      color: #fcd34d;
    }

    .event-status.is-warn::before {
      background: #f59e0b;
    }

    @media (max-width: 639px) {
      /* A heading and the link beside it take a line each when they cannot share one. */
      .form-kit-title {
        flex-wrap: wrap;
        row-gap: 0.25rem;
      }

      .event-picked {
        flex-wrap: wrap;
      }
    }

    /* "Saved", said once beside the button that saved, then gone. No script. */
    .form-kit-saved {
      animation: form-kit-saved 0.4s ease 2.5s forwards;
    }

    @keyframes form-kit-saved {
      to { opacity: 0; visibility: hidden; }
    }

    @media (prefers-reduced-motion: reduce) {
      .form-kit-saved { animation-duration: 0.01s; }
    }

    /* One voice for buttons inside these forms: the brand button is sentence case, so the app's
       older capitals-and-letterspacing buttons (secondary, danger) follow it here. A dialog
       opened from a form is outside its sections and keeps the component's own look. */
    .section-content button.uppercase,
    .section-content a.uppercase {
      font-size: 0.875rem;
      letter-spacing: normal;
      text-transform: none;
    }

    /* On a phone each tab is an accordion header that already carries the tab's name (and its
       summary), directly above the tab's own heading. The heading's name gives way there; what
       sits beside it (a generator button, a docs link) stays. A page marks the name with
       .section-heading-name, or uses .form-kit-title, whose first span is the name. */
    @media (max-width: 1023px) {
      .section-content .section-heading-name,
      .section-content .form-kit-title > span:first-child {
        display: none;
      }

      .section-content .form-kit-title:not(:has(> :nth-child(2))) {
        display: none;
      }

      /* A heading that held only the name leaves no gap behind where it was. */
      .section-content h2:has(> .section-heading-name:only-child) {
        display: none;
      }
    }


    /* An AI action beside a section's heading (the 2026-11 redesign): a pill in words, no icon. */
    button.form-kit-ai,
    form button.form-kit-ai {
      display: inline-flex;
      flex: none;
      align-items: center;
      min-height: 2.25rem;
      padding: 0 0.875rem;
      border: 1px solid rgb(var(--ap-border-strong));
      border-radius: 999px;
      background: rgb(var(--ap-surface));
      font-size: 0.8125rem;
      font-weight: 600;
      letter-spacing: normal;
      color: rgb(var(--ap-ink));
      white-space: nowrap;
      transition: border-color 0.2s, color 0.2s;
    }
    button.form-kit-ai:hover,
    form button.form-kit-ai:hover {
      border-color: var(--brand-blue);
      color: var(--brand-blue);
    }
    button.form-kit-ai.is-locked,
    form button.form-kit-ai.is-locked {
      color: rgb(var(--ap-ink-3));
    }

    /* A button that picks a file (the 2026-11 redesign): a plain bordered key, 40px tall. */
    button.form-kit-file,
    form button.form-kit-file {
      display: inline-flex;
      align-items: center;
      min-height: 2.5rem;
      padding: 0 0.875rem;
      border: 1px solid rgb(var(--ap-border-strong));
      border-radius: 0.625rem;
      background: rgb(var(--ap-surface));
      font-size: 0.875rem;
      font-weight: 600;
      color: rgb(var(--ap-ink));
      transition: border-color 0.2s, color 0.2s;
    }
    button.form-kit-file:hover,
    form button.form-kit-file:hover {
      border-color: var(--brand-blue);
      color: var(--brand-blue);
    }

    /* The quiet line under a field (13px in the second ink, the 2026-11 redesign): the forms write
       it with Tailwind's text-xs and gray-500 in many places, which read smaller and paler. */
    .section-content p.text-xs.text-gray-500 {
      font-size: 0.8125rem;
      line-height: 1.45;
      color: rgb(var(--ap-ink-2));
    }

    /* Switches at the redesign's size, 38 by 22 (the component draws 44 by 24). */
    .section-content label.relative.w-11.h-6,
    .section-content label.relative.w-11.h-6 > div.w-11.h-6 {
      width: 2.375rem;
      height: 1.375rem;
    }
    .section-content label.relative.w-11.h-6 > div.absolute {
      top: 0.125rem;
      width: 1.125rem;
      height: 1.125rem;
    }
    .section-content label.relative.w-11.h-6 > input:checked ~ div.absolute {
      --tw-translate-x: 1rem;
    }
    [dir="rtl"] .section-content label.relative.w-11.h-6 > input:checked ~ div.absolute {
      --tw-translate-x: -1rem;
    }
    /* A switch's description under its name: 13px, and short of the switch's edge. */
    .toggles-end > div > p.ms-14 {
      max-width: 85%;
      font-size: 0.8125rem;
      line-height: 1.5;
    }
    .toggles-end > div > .flex.items-center.gap-3 > label:last-child {
      font-size: 0.9375rem;
    }

    /* The 2026-11 redesign, everywhere a row opens in place: the rows stand in a box of their own
       (a hairline between them), as the design draws them. The Stripe row that the payments hero
       stands for stays hidden; the grid rule for rows outranked the first attempt. */
    .section-content .event-subrows:not(.gfx-rows .event-subrows) {
      padding: 0;
      border: 1px solid rgb(var(--ap-border));
      border-radius: 0.875rem;
    }
    /* The rows keep their words off the box's edge with their own padding, so the hairline
       between two rows runs from one side of the box to the other (it stopped 16px short). */
    .section-content .event-subrows:not(.gfx-rows .event-subrows) > button.event-subrow {
      padding-inline: 0.875rem;
    }
    .section-content .event-subrows:not(.gfx-rows .event-subrows) > .event-subrow-body {
      padding-inline: 0.875rem;
    }
    /* The last thing in the box draws no line of its own: the box's border is that line. "Last"
       is the last row on show, and its pane when it is open: a row hidden at the end of the list
       (Accessibility, while the widget is shown) left the one above it with a doubled edge. */
    .section-content .event-subrows:not(.gfx-rows .event-subrows) > button.event-subrow:not(:has(~ button.event-subrow:not([hidden]))):not([aria-expanded="true"]),
    .section-content .event-subrows:not(.gfx-rows .event-subrows) > .event-subrow-body:not(:has(~ button.event-subrow:not([hidden]))) {
      border-bottom-color: transparent;
    }
    .event-subrows:not(.gfx-rows .event-subrows) > button.event-subrow.is-hero-twin:not([aria-expanded="true"]) {
      display: none;
    }
    /* The 2026-11 redesign: a set of pills reads as the segmented control (a grey track, the
       chosen ones white). They stay checkboxes, so an event can be in person and online at once.
       Rows carry no icon before their name. */
    .event-pills {
      gap: 0.25rem;
      padding: 0.25rem;
      border-radius: 0.875rem;
      background: rgb(var(--ap-surface-active));
    }
    .event-pill span {
      border-color: transparent;
      border-radius: 0.625rem;
      padding: 0.375rem 0.875rem;
      font-size: 0.875rem;
      font-weight: 600;
      color: rgb(var(--ap-ink-2));
    }
    .event-pill input:checked + span {
      border-color: transparent;
      background: rgb(var(--ap-surface));
      color: rgb(var(--ap-ink));
      box-shadow: 0 1px 2px rgba(20, 22, 26, 0.1);
    }
    .event-pill input:checked + span::before {
      content: none;
    }
    .event-row-icon {
      display: none;
    }
</style>
