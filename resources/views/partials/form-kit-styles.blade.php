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
      color: rgb(var(--ap-ink-3));
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
    .event-subrow .event-row-summary.is-warn {
      color: #b45309;
      font-weight: 500;
    }
    .dark .event-subrow .event-row-summary.is-warn {
      color: #fcd34d;
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
    .event-eyebrow {
      margin-bottom: 0.125rem;
      font-size: 0.75rem;
      font-weight: 600;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      color: rgb(var(--ap-ink-3));
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
      gap: 0.75rem;
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
      font-size: 0.8125rem;
      font-weight: 500;
      color: var(--brand-blue);
    }
    .event-url-strip a.event-link:hover {
      text-decoration: underline;
    }
    .event-url-strip button.event-link {
      flex: none;
      padding: 0;
      border: 0;
      background: none;
      font-size: 0.8125rem;
      font-weight: 500;
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
      color: rgb(var(--ap-ink-3));
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
    button.event-tile.is-on,
    form button.event-tile.is-on {
      border-color: var(--brand-blue);
      background: var(--brand-blue-a10);
      box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.08);
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
    .event-tile.is-on .event-tile-title::before {
      content: "\2713";
    }
    .event-tile-help {
      font-size: 0.8125rem;
      color: rgb(var(--ap-ink-3));
    }
    .event-tile-narrow {
      max-width: 14rem;
    }
    button.event-link.event-link-quiet,
    form button.event-link.event-link-quiet {
      color: rgb(var(--ap-ink-3));
    }
    @media (min-width: 640px) {
      .event-tiles {
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
      font-size: 0.8125rem;
      font-weight: 400;
      line-height: 1.25rem;
      color: rgb(var(--ap-ink-2));
    }
    .section-nav-summary.is-empty {
      color: rgb(var(--ap-ink-3));
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
      font-weight: 600;
      color: rgb(var(--ap-ink-2));
    }
    button.event-bar-text:hover,
    form button.event-bar-text:hover {
      color: rgb(var(--ap-ink));
    }
    /* Nothing to save yet on an existing event: Save is still there and still works, it just stops
       asking to be pressed. */
    button.event-bar-save.is-idle,
    form button.event-bar-save.is-idle,
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
      .event-save-bar {
        position: sticky;
        z-index: 30;
        margin: 1.5rem -2rem 0;
        padding: 0.75rem 2rem;
        background: rgb(var(--ap-bg));
        box-shadow: none;
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

    .section-nav-link.validation-error {
      border-inline-start-color: #dc2626 !important;
    }

    @media (prefers-color-scheme: dark) {
      .section-nav-link.validation-error {
        border-inline-start-color: #ef4444 !important;
      }
    }

    .dark .section-nav-link.validation-error {
      border-inline-start-color: #ef4444 !important;
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
      font-size: 1.125rem;
      font-weight: 600;
      color: rgb(var(--ap-ink));
    }

    .form-kit-title > span:first-child {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      min-width: 0;
    }

    .form-kit-title > span:first-child > svg {
      width: 1.5rem;
      height: 1.5rem;
      flex: none;
    }

    .form-kit-lead {
      margin: 0 0 1.25rem;
      font-size: 0.875rem;
      color: rgb(var(--ap-ink-3));
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

</style>
