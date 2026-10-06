{{-- The look of a schedule's admin pages (role/show-admin and its ten tabs): one strip of tabs that
     never hides one, one head for every page (what the page is for, then what you can do on it),
     one list that reads on a phone, one empty state. It sits on the form kit
     (partials/form-kit-styles, included just before this), whose text links, status marks and
     address strip these pages use as they are, so the lists here and the forms they lead to speak
     in one voice. Plain CSS on the portal's own tokens, so every palette follows. --}}
<style {!! nonce_attr() !!}>
    /* The tabs. They used to be clipped at the edge of the page with the scrollbar hidden, so on
       a laptop "Plan" was not on the page at all and "Team" read "Tea". The strip still scrolls
       when it must, but it says so: it fades where it runs on, and the tab you are on is brought
       into view. */
    .ap-tabs-wrap {
      position: relative;
      margin: 1.25rem 0 1.5rem;
      border-bottom: 1px solid rgb(var(--ap-border));
    }
    .ap-tabs {
      display: flex;
      gap: 1.5rem;
      overflow-x: auto;
      scrollbar-width: none;
    }
    .ap-tabs::-webkit-scrollbar {
      display: none;
    }
    .ap-tab {
      display: inline-flex;
      flex: none;
      align-items: center;
      gap: 0.4rem;
      padding: 0.75rem 0.125rem;
      font-size: 0.9375rem;
      font-weight: 500;
      white-space: nowrap;
      color: rgb(var(--ap-ink-3));
      transition: color 0.2s, border-color 0.2s;
    }
    .ap-tab:hover {
      box-shadow: inset 0 -2px 0 rgb(var(--ap-border-strong));
      color: rgb(var(--ap-ink));
    }
    .ap-tab[aria-current="page"] {
      box-shadow: inset 0 -2px 0 var(--brand-blue);
      color: var(--brand-blue);
    }
    /* Drawn inside the tab: the strip's own overflow would cut a ring drawn around it. */
    .ap-tab:focus-visible {
      outline: 2px solid var(--brand-blue);
      outline-offset: -2px;
      border-radius: 0.25rem;
    }
    .ap-tabs-select {
      margin: 1.25rem 0;
    }
    .ap-tab-count {
      min-width: 1.25rem;
      border-radius: 999px;
      padding: 0 0.375rem;
      background: var(--ap-tint-2);
      font-size: 0.75rem;
      font-weight: 600;
      line-height: 1.25rem;
      text-align: center;
      color: rgb(var(--ap-ink-2));
    }
    /* Something is waiting for an answer: requests, bookings to confirm. */
    .ap-tab-count.is-waiting {
      background: var(--brand-button-bg);
      color: #fff;
    }
    .ap-tabs-wrap::before,
    .ap-tabs-wrap::after {
      content: "";
      position: absolute;
      top: 0;
      bottom: 1px;
      width: 2.5rem;
      pointer-events: none;
      opacity: 0;
      transition: opacity 0.2s;
    }
    .ap-tabs-wrap::before {
      inset-inline-start: 0;
      background: linear-gradient(to right, rgb(var(--ap-bg)), rgb(var(--ap-bg) / 0));
    }
    .ap-tabs-wrap::after {
      inset-inline-end: 0;
      background: linear-gradient(to left, rgb(var(--ap-bg)), rgb(var(--ap-bg) / 0));
    }
    [dir="rtl"] .ap-tabs-wrap::before {
      background: linear-gradient(to left, rgb(var(--ap-bg)), rgb(var(--ap-bg) / 0));
    }
    [dir="rtl"] .ap-tabs-wrap::after {
      background: linear-gradient(to right, rgb(var(--ap-bg)), rgb(var(--ap-bg) / 0));
    }
    .ap-tabs-wrap.more-before::before,
    .ap-tabs-wrap.more-after::after {
      opacity: 1;
    }

    /* The pages that are lists and cards keep to a column: across a wide screen a member's name
       stood a thousand pixels from their role. The calendars take the width they are given. */
    .page-col {
      max-width: 64rem;
    }

    /* A page's head: one line saying what the page is for, and its actions beside it. */
    .page-head {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 0.75rem 1.5rem;
      margin: 0 0 1.25rem;
    }
    .page-lead {
      flex: 1 1 18rem;
      max-width: 44rem;
      margin: 0;
      font-size: 0.9375rem;
      color: rgb(var(--ap-ink-3));
    }
    .page-actions {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 0.5rem;
    }
    .page-head.is-flush {
      margin-bottom: 0;
    }
    .page-head > .page-stats {
      flex: 1 1 22rem;
      max-width: 42rem;
    }
    .page-subhead {
      display: flex;
      flex-wrap: wrap;
      align-items: baseline;
      gap: 0.25rem 0.75rem;
      margin: 2rem 0 0.75rem;
    }
    .page-subhead h2 {
      margin: 0;
      font-size: 1rem;
      font-weight: 600;
      color: rgb(var(--ap-ink));
    }
    .page-subhead p {
      margin: 0;
      font-size: 0.8125rem;
      color: rgb(var(--ap-ink-3));
    }

    /* A list of people or things, as a table that becomes a stack of rows on a phone: it used to
       scroll sideways there, with the action that mattered (a member's role, Remove) off the edge. */
    .page-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.875rem;
    }
    .page-table th {
      border-bottom: 1px solid rgb(var(--ap-border));
      padding: 0.625rem 1rem;
      font-size: 0.75rem;
      font-weight: 600;
      letter-spacing: 0.04em;
      text-align: start;
      text-transform: uppercase;
      white-space: nowrap;
      color: rgb(var(--ap-ink-3));
    }
    /* A heading the list can be sorted by is a button, so a keyboard reaches it. */
    .page-sort {
      border: 0;
      padding: 0;
      background: none;
      font: inherit;
      letter-spacing: inherit;
      text-transform: inherit;
      color: inherit;
      cursor: pointer;
    }
    .page-sort:hover {
      color: rgb(var(--ap-ink));
    }
    .page-sort:focus-visible {
      outline: 2px solid var(--brand-blue);
      outline-offset: 2px;
      border-radius: 0.125rem;
    }
    .page-table td {
      border-top: 1px solid rgb(var(--ap-border));
      padding: 0.75rem 1rem;
      vertical-align: middle;
      color: rgb(var(--ap-ink-2));
    }
    .page-table tbody tr:first-child td {
      border-top: 0;
    }
    .page-person {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      min-width: 0;
    }
    /* A long address with nowhere to break ran out of a phone's row and was cut by the card. */
    .page-person-text {
      min-width: 0;
      overflow-wrap: anywhere;
    }
    .page-table .c-actions {
      text-align: end;
      white-space: nowrap;
    }
    .page-table .c-actions > * + * {
      margin-inline-start: 0.875rem;
    }
    .page-table .c-actions form {
      display: inline;
    }
    .page-table .c-date {
      white-space: nowrap;
      color: rgb(var(--ap-ink-3));
    }
    .page-table .col-status {
      width: 15rem;
    }
    .page-table .col-date {
      width: 9rem;
    }
    .page-table .col-act {
      width: 6rem;
    }
    /* The layout gives every select 1rem of padding on all sides with !important, which takes
       back the room the chevron needs: a member's role read "Viewe" and "Admi" under its own
       arrow. Said again here, as firmly, for the one select these lists hold. */
    .page-table select {
      min-width: 8rem;
      padding-block: 0.4rem !important;
      padding-inline: 0.75rem 2.25rem !important;
      font-size: 0.875rem !important;
      line-height: 1.25rem !important;
    }
    @media (max-width: 639.98px) {
      .page-table thead {
        position: absolute;
        width: 1px;
        height: 1px;
        overflow: hidden;
        clip-path: inset(50%);
      }
      .page-table,
      .page-table tbody {
        display: block;
      }
      .page-table tr {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.375rem 0.875rem;
        border-top: 1px solid rgb(var(--ap-border));
        padding: 0.75rem 1rem;
      }
      .page-table tbody tr:first-child {
        border-top: 0;
      }
      .page-table td {
        border: 0;
        padding: 0;
      }
      .page-table td:empty {
        display: none;
      }
      .page-table .c-main {
        flex: 1 1 100%;
        min-width: 0;
      }
      .page-table .c-actions {
        margin-inline-start: auto;
      }
      .page-table colgroup {
        display: none;
      }
      /* A row that is a name and a date keeps them on one line. */
      .page-table.is-compact .c-main {
        flex: 1 1 0;
      }
      /* Room for a thumb around the small links. Written with the element, as the kit writes the
         rule this has to outrank (.event-url-strip button.event-link { padding: 0 }): without it
         the padding lost and the negative margin still applied. */
      .page-table button.event-link,
      .page-table a.event-link,
      .request-foot button.event-link,
      .request-foot a.event-link,
      .event-url-strip button.event-link,
      .event-url-strip a.event-link {
        padding: 0.5rem 0.375rem;
        margin: -0.5rem -0.125rem;
      }
      .request-foot .request-answer {
        gap: 1.5rem;
      }
    }

    /* Three figures on one line, at every width: as three tall cards they took a screen and a half
       of a phone before the first name. */
    .page-stats {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
    }
    .page-stat {
      padding: 0.875rem 1.25rem;
    }
    .page-stat + .page-stat {
      border-inline-start: 1px solid rgb(var(--ap-border));
    }
    .page-stat-value {
      font-size: 1.5rem;
      font-weight: 700;
      line-height: 1.2;
      color: rgb(var(--ap-ink));
    }
    .page-stat-label {
      margin-top: 0.125rem;
      font-size: 0.8125rem;
      color: rgb(var(--ap-ink-3));
    }
    @media (max-width: 639.98px) {
      .page-stat {
        padding: 0.75rem 0.75rem;
      }
      .page-stat-value {
        font-size: 1.25rem;
      }
      .page-stat-label {
        font-size: 0.75rem;
      }
    }

    /* Nothing here yet: what would be here, and the one thing to do about it. */
    .page-empty {
      padding: 2.5rem 1.5rem;
      text-align: center;
    }
    .page-empty svg.page-empty-icon {
      width: 2.5rem;
      height: 2.5rem;
      margin: 0 auto 0.75rem;
      color: rgb(var(--ap-ink-4));
    }
    .page-empty h3 {
      margin: 0;
      font-size: 1rem;
      font-weight: 600;
      color: rgb(var(--ap-ink));
    }
    .page-empty p {
      max-width: 30rem;
      margin: 0.25rem auto 0;
      font-size: 0.875rem;
      color: rgb(var(--ap-ink-3));
    }
    .page-empty .page-actions {
      justify-content: center;
      margin-top: 1rem;
    }
    .page-empty-link {
      max-width: 34rem;
      margin: 1.5rem auto 0;
      text-align: start;
    }
    .page-empty-link p {
      max-width: none;
      margin: 0.5rem 0 0;
      font-size: 0.75rem;
    }

    /* A request: who is asking, for what, and when, then the answer. */
    .request-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(min(100%, 24rem), 1fr));
      gap: 1rem;
    }
    .request-card {
      display: flex;
      flex-direction: column;
    }
    .request-body {
      display: flex;
      gap: 1rem;
      padding: 1.25rem;
    }
    .request-body > img {
      flex: none;
      width: 3.5rem;
      height: 3.5rem;
      border-radius: 0.625rem;
      object-fit: cover;
    }
    .request-text {
      min-width: 0;
      flex: 1;
    }
    .request-kind {
      font-size: 0.75rem;
      font-weight: 600;
      letter-spacing: 0.04em;
      text-transform: uppercase;
      color: rgb(var(--ap-ink-3));
    }
    .request-title {
      margin: 0.125rem 0 0;
      font-size: 1.0625rem;
      font-weight: 600;
      color: rgb(var(--ap-ink));
      overflow-wrap: anywhere;
    }
    .request-line {
      margin-top: 0.25rem;
      font-size: 0.875rem;
      color: rgb(var(--ap-ink-2));
      overflow-wrap: anywhere;
    }
    .request-line.is-quiet {
      color: rgb(var(--ap-ink-3));
    }
    .request-line.is-chip-only > .event-chip {
      margin-inline-start: 0;
    }
    .request-note {
      margin-top: 0.625rem;
      border-radius: 0.5rem;
      padding: 0.625rem 0.75rem;
      background: var(--ap-tint-1);
      font-size: 0.8125rem;
      color: rgb(var(--ap-ink-2));
    }
    .request-foot {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 0.5rem 1rem;
      margin-top: auto;
      border-top: 1px solid rgb(var(--ap-border));
      padding: 0.75rem 1.25rem;
    }
    .request-foot .request-answer {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin-inline-start: auto;
    }
    .request-foot form {
      display: inline-flex;
    }

    /* The Schedule tab's actions on a phone: Add event on a line of its own, the rest beneath.
       Three buttons shared one line, and every label broke in two ("Add / Event"). */
    @media (max-width: 767.98px) {
      .calendar-phone-actions {
        flex-wrap: wrap;
      }
      .calendar-phone-actions > a {
        order: -1;
        flex: 1 1 100%;
      }
      .calendar-phone-actions > button {
        flex: 1 1 0;
        width: auto;
        white-space: nowrap;
      }
    }

    /* The booking address on the Appointments tab: a floor on its width, below which it takes a
       line to itself. On a phone it was squeezed to "http:/" beside its own label and buttons. */
    .appt-book-url {
      flex: 1 1 15rem;
    }

    .page-legend {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.8125rem;
      color: rgb(var(--ap-ink-3));
    }
    .page-legend i {
      width: 1rem;
      height: 1rem;
      border: 1px solid rgba(220, 38, 38, 0.45);
      border-radius: 0.25rem;
      background: rgba(239, 68, 68, 0.2);
    }
    .dark .page-legend i {
      border-color: rgba(252, 165, 165, 0.6);
      background: rgba(239, 68, 68, 0.35);
    }
</style>
