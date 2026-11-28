{{-- The look of the admin portal's pages. It began as the look of a schedule's own pages
     (role/show-admin and its ten tabs): one strip of tabs that never hides one, one head for
     every page (what the page is for, then what you can do on it), one list that reads on a
     phone, one empty state. The second half, from "Every other page of the portal", is what the
     rest of the portal needed to open the same way: a title row, a card, a row of filters, a
     strip of figures of any length, a small form. It sits on the form kit
     (partials/form-kit-styles, included just before this by layouts/app-admin), whose text links,
     status marks and address strip these pages use as they are, so the lists here and the forms
     they lead to speak in one voice. Plain CSS on the portal's own tokens, so every palette
     follows and nothing here waits on a CSS build. --}}
<style {!! nonce_attr() !!}>
    /* The tabs. They used to be clipped at the edge of the page with the scrollbar hidden, so on
       a laptop "Plan" was not on the page at all and "Team" read "Tea". The strip still scrolls
       when it must, but it says so: it fades where it runs on, and the tab you are on is brought
       into view. */
    /* 2026-11 redesign (zip 17): plain words at 13px/600 on the page's own ground, no track, the
       tab you are on a white pill with its words in the deep brand blue (--brand-blue-dark,
       #1a3fb0, a lighter blue in dark mode); a tab pointed at takes the pill in plain ink. */
    .ap-tabs-wrap {
      position: relative;
      max-width: 100%;
      width: fit-content;
      margin: 1.25rem 0 1.5rem;
    }
    .ap-tabs {
      display: flex;
      gap: 0.125rem;
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
      gap: 0.375rem;
      min-height: 2.5rem;
      padding: 0 0.625rem;
      border-radius: 0.625rem;
      font-size: 0.8125rem;
      font-weight: 600;
      white-space: nowrap;
      color: rgb(var(--ap-ink-2));
      transition: color 0.2s, background-color 0.2s;
    }
    .ap-tab:hover {
      background: rgb(var(--ap-surface));
      color: rgb(var(--ap-ink));
    }
    .ap-tab[aria-current="page"] {
      background: rgb(var(--ap-surface));
      color: var(--brand-blue-dark);
    }
    /* Drawn inside the tab: the strip's own overflow would cut a ring drawn around it. */
    .ap-tab:focus-visible {
      outline: 2px solid var(--brand-blue);
      outline-offset: -2px;
    }
    .ap-tabs-select {
      margin: 1.25rem 0;
    }
    /* A count is a quiet figure beside the name ("Gift cards 1"), not a pill. */
    .ap-tab-count {
      font-weight: 500;
      color: rgb(var(--ap-ink-3));
    }
    /* Something is waiting for an answer (requests, bookings to confirm): then it is a pill. */
    .ap-tab-count.is-waiting {
      min-width: 1.25rem;
      border-radius: 999px;
      padding: 0 0.375rem;
      background: var(--brand-button-bg);
      font-size: 0.75rem;
      font-weight: 600;
      line-height: 1.25rem;
      text-align: center;
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

    /* One column for every page of the portal (layouts/app-admin puts it on <main> and on the
       line under it): the header, the tabs and what the page shows share both edges, at any
       width. It is the 2026-11 redesign's column, 1040px with the layout's gutters (2rem from a
       laptop up, so 976px of content beside the 18rem rail), in the middle of the room beside
       the sidebar. It replaced an 80rem frame, which on a laptop spread every card, list and
       calendar some 15% wider than the designs and left a short page looking sparse. */
    .ap-frame {
      width: 100%;
      max-width: 65rem;
      margin-inline: auto;
    }
    /* The line under the page (the designs' "Questions or suggestions? Email ..."). */
    .ap-foot-line {
      font-size: 0.8125rem;
      color: rgb(var(--ap-ink-2));
    }
    /* Where the support chat's launcher is drawn (partials/support-chat-widget: a 56px button
       fixed 24px above the window's foot, at the end of the line), the line keeps clear of it at
       the bottom of the scroll, so the button never sits on "Questions or suggestions?". */
    body:has(#support-chat-widget) .ap-foot-line {
      padding-bottom: 6.5rem;
    }
    /* The bar above the page is the page's own ground, with no rule or shadow under it, and runs
       the room: the account name and Upgrade stand at the room's end, as in the designs, not over
       the column's. */
    .ap-frame-bar.header-gradient {
      background: rgb(var(--ap-bg));
      border-bottom-color: transparent;
      box-shadow: none;
    }
    /* A bar fixed to the foot of the page's room, not of the window: it starts where the sidebar
       ends, so what it holds is centred on the page. Fixed to the window, a bar's buttons stood
       9rem to the left of the column they belong to, half under the sidebar's side of it. */
    .ap-foot-bar {
      position: fixed;
      bottom: 0;
      inset-inline: 0;
    }
    @media (min-width: 1024px) {
      .ap-foot-bar {
        inset-inline-start: 18rem;
      }
    }
    /* A canvas (the seating designer, the box office) takes the window: a room plan held to the
       column is only a smaller room plan. The page asks for it with the layout's `wide`
       attribute (x-app-admin-layout wide; no angle brackets here, a tag in a comment of this
       partial is compiled). */
    .ap-frame.is-wide {
      max-width: none;
    }
    /* A bar that reaches past the page's gutters to the edges of the column (a sticky toolbar
       with negative margins the size of the gutter) keeps reaching, because that is what covers
       a card, shadow and all, as it scrolls under the bar. Its LINE is another matter: from 83rem
       (the 1040px column beside the 18rem sidebar) the column stops growing, and a line that runs
       a gutter past the page on each side and still falls short of the window is neither the
       page's nor the room's. So from there the bar's own border goes clear and the line is drawn
       at the content's width. The selector is doubled to outrank a `dark:border-*` utility on the
       same element. */
    @media (min-width: 83rem) {
      .ap-frame .ap-frame-bleed {
        border-color: transparent;
      }
      .ap-frame-bleed::after {
        content: "";
        position: absolute;
        inset-inline: 2rem;
        bottom: -1px;
        border-bottom: 1px solid rgb(var(--ap-border));
        pointer-events: none;
      }
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
      color: rgb(var(--ap-ink-2));
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
       scroll sideways there, with the action that mattered (a member's role, Remove) off the edge.
       From a tablet up it reads as the design-language review's rows, not a grid: no band behind
       the headings (they stay, small capitals labels, because a list sorts by them and a screen
       reader names a cell by them), no rules between columns, a hairline between rows, the first
       cell a name in full ink over one grey line (.c-sub, or .page-person's), a figure at the end
       of its cell (.c-num) and the row's action last (.c-actions). */
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
      letter-spacing: 0.06em;
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
    /* The arrow that says which way the list runs. */
    .page-sort svg {
      display: inline-block;
      width: 0.75rem;
      height: 0.75rem;
      margin-inline-start: 0.125rem;
      vertical-align: -1px;
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
    /* No header band and no column rules: the portal's table look (.ap-card thead th, a sunken
       tint, and any border a page's markup gives a cell's side) is undone for the kit's lists. */
    .ap-card .page-table thead th,
    .page-table thead th {
      background: none;
      border-inline: 0;
    }
    .page-table td {
      border-inline: 0;
    }
    .page-table tbody td:first-child {
      color: rgb(var(--ap-ink));
    }
    .page-table tbody td:first-child > .c-strong:first-child,
    .page-table tbody td:first-child .page-person-text > :first-child {
      font-weight: 600;
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
    .page-table input[type="text"],
    .page-table input[type="email"],
    .page-table input[type="number"],
    .page-table textarea {
      padding: 0.4rem 0.625rem !important;
      font-size: 0.875rem !important;
      line-height: 1.25rem !important;
    }
    .page-table select {
      min-width: 8rem;
      padding-block: 0.4rem !important;
      padding-inline: 0.75rem 2.25rem !important;
      font-size: 0.875rem !important;
      line-height: 1.25rem !important;
    }
    @media (max-width: 639.98px) {
      /* Out of sight, but still the list's headings to a screen reader: a column that cannot be
         sorted (a subscriber's Status and Date) has no other name on a phone. Only the head's
         controls are put away altogether, because a sort button clipped to a pixel was still a
         stop in the tab order, three to six invisible ones a list; the bar above the list does
         their work (partials/admin-page-script), and names the column on the cell itself. */
      .page-table thead {
        position: absolute;
        width: 1px;
        height: 1px;
        overflow: hidden;
        clip-path: inset(50%);
      }
      .page-table thead button,
      .page-table thead input,
      .page-table thead select,
      .page-table thead a {
        visibility: hidden;
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
      /* A row put away (the hidden attribute, or the class) stays away: the display above
         outranks both. Any list with rows that open needs this. */
      .page-table tr[hidden],
      .page-table tr.hidden {
        display: none;
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
      .request-foot button.event-link,
      .request-foot a.event-link,
      .event-url-strip button.event-link,
      .event-url-strip a.event-link {
        padding: 0.5rem 0.375rem;
        margin: -0.5rem -0.125rem;
      }
      /* In a list the room is taken back in full, so a title that is a link starts on the same
         line as the text under it, and the links at a row's end are spaced by the row, not by
         what is left of their margins (two links sat 12px apart and a button in a form 26px). */
      .page-table button.event-link,
      .page-table a.event-link {
        padding: 0.5rem 0.375rem;
        margin: -0.5rem -0.375rem;
      }
      .page-table .c-actions {
        display: inline-flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
        gap: 0.5rem 0.875rem;
      }
      .page-table .c-actions > * + * {
        margin-inline-start: 0;
      }
      .page-table .c-actions form {
        display: inline-flex;
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
    /* Inside a card that already says what it holds: a line, not a poster. */
    .page-empty.is-compact {
      padding: 1.5rem 1.25rem;
    }
    .page-empty.is-compact h3 {
      font-size: 0.875rem;
      font-weight: 500;
      color: rgb(var(--ap-ink-2));
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
    /* The key to the Availability grid: the mark's own colours (partials/month-kit-styles, .day-x). */
    .page-legend i {
      width: 1rem;
      height: 1rem;
      border: 1px solid var(--gk-bad-line, rgba(220, 38, 38, 0.45));
      border-radius: 0.25rem;
      background: var(--gk-bad-bg, rgba(239, 68, 68, 0.2));
    }

    /* ------------------------------------------------------------------------------------------
       Every other page of the portal.

       A page opens with its title row (x-page-header): where you came from, what this page is,
       one line on what it is for, and what you can do here at the end of the row. Pages used to
       open nine different ways, half of them with no name at all and a "Back" button where the
       page's own action belongs.
       ------------------------------------------------------------------------------------------ */
    /* The header of the design-language review: a large title at the start, one grey line
       under it (16px in the muted ink, #4b5263 on Mist), an optional blue eyebrow over it, and
       the actions at the end of the same row, 44px tall (the page's one filled button and quiet
       words beside it). */
    .page-top {
      display: flex;
      flex-wrap: wrap;
      align-items: flex-end;
      justify-content: space-between;
      gap: 0.875rem 1.5rem;
      margin: 0 0 1.5rem;
    }
    .page-top-text {
      flex: 1 1 16rem;
      min-width: 0;
    }
    /* A page's name, as the 2026-11 redesign sets it: large, bold and tight. */
    .page-title {
      margin: 0;
      font-size: clamp(1.75rem, 4vw, 2.375rem);
      font-weight: 700;
      line-height: 1.1;
      letter-spacing: -0.03em;
      color: rgb(var(--ap-ink));
      overflow-wrap: anywhere;
    }
    .page-top .page-lead {
      max-width: 44rem;
      margin-top: 0.625rem;
      font-size: 1rem;
      line-height: 1.5;
      color: rgb(var(--ap-ink-2));
    }
    .page-top .event-eyebrow {
      margin-bottom: 0.5rem;
    }
    /* The header's actions are 44px: the filled one keeps its blue, the rest are words. */
    .page-top > .page-actions :is(.ap-btn, .ap-secondary-btn, .ap-text-action),
    .ap-btn.ap-head-btn {
      min-height: 2.75rem;
      padding-block: 0.5625rem;
    }
    /* The way back: the name of the page this one hangs from, never the word "Back". */
    .page-back {
      display: inline-flex;
      align-items: center;
      gap: 0.25rem;
      max-width: 100%;
      margin-bottom: 0.25rem;
      font-size: 0.875rem;
      font-weight: 500;
      color: rgb(var(--ap-ink-3));
      transition: color 0.2s;
    }
    .page-back:hover {
      color: var(--brand-blue);
    }
    .page-back:focus-visible {
      outline: 2px solid var(--brand-blue);
      outline-offset: 2px;
      border-radius: 0.25rem;
    }
    .page-back svg {
      flex: none;
      width: 1rem;
      height: 1rem;
    }
    [dir="rtl"] .page-back svg {
      transform: scaleX(-1);
    }
    .page-back span {
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
    /* A schedule's picture beside the title of a page that is about one schedule. */
    .page-top-figure {
      flex: none;
      width: 3rem;
      height: 3rem;
      border-radius: 0.75rem;
      object-fit: cover;
    }
    .page-top-text.has-figure {
      display: flex;
      align-items: center;
      gap: 0.875rem;
    }
    .page-top-text.has-figure > div {
      min-width: 0;
    }
    @media (max-width: 639.98px) {
      .page-title {
        font-size: 1.75rem;
        line-height: 1.15;
      }
      /* The actions take the row under the title (or under the lead), and a lone button takes
         the width. */
      .page-top > .page-actions,
      .page-head > .page-actions {
        flex: 1 1 100%;
      }
      /* A key to the page's colours is not a button: it stays where it was, at the start. */
      .page-top > .page-actions > :only-child:not(.page-legend),
      .page-head > .page-actions > :only-child:not(.page-legend) {
        flex: 1 1 auto;
        justify-content: center;
      }
    }

    /* The Plan tab's headline (the 2026-11 redesign): the plan large, the date on the far side. */
    .plan-hero {
      display: flex;
      flex-wrap: wrap;
      align-items: flex-end;
      justify-content: space-between;
      gap: 0.75rem 2rem;
    }
    /* What the plan says beyond its name and date (a status, a trial, a cancel), under a rule only
       when there is any: on a plain paid plan the block is empty and the usage's own rule follows. */
    .plan-details:has(> *) {
      margin-top: 1.25rem;
      padding-top: 1.25rem;
      border-top: 1px solid rgb(var(--ap-border));
    }
    .plan-hero-kicker {
      font-size: 0.6875rem;
      font-weight: 600;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      color: rgb(var(--ap-ink-3));
    }
    .plan-hero-name {
      margin-top: 0.125rem;
      font-size: 2.125rem;
      font-weight: 700;
      line-height: 1.1;
      letter-spacing: -0.02em;
      color: rgb(var(--ap-ink));
    }
    .plan-hero-term {
      margin-inline-start: 0.375rem;
      font-size: 0.9375rem;
      font-weight: 500;
      letter-spacing: 0;
      color: rgb(var(--ap-ink-2));
    }
    .plan-hero-side {
      text-align: end;
    }
    .plan-hero-date {
      margin-top: 0.125rem;
      font-size: 1.25rem;
      font-weight: 700;
      color: rgb(var(--ap-ink));
    }
    .plan-usage {
      display: grid;
      gap: 1.5rem;
      margin-top: 1.25rem;
      padding-top: 1.25rem;
      border-top: 1px solid rgb(var(--ap-border));
    }
    @media (min-width: 768px) {
      .plan-usage {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
    }
    .plan-usage h5 {
      font-size: 0.6875rem;
      font-weight: 600;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      color: rgb(var(--ap-ink-3));
    }

    /* Your tickets (the 2026-11 redesign): the next one on its own, then the rest by day. */
    .tickets-next {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 1rem 1.5rem;
      padding: 1.25rem 1.5rem;
      box-shadow: 0 1px 2px rgba(20, 22, 26, 0.06), 0 8px 24px rgba(20, 22, 26, 0.08);
    }
    .tickets-next-kicker {
      font-size: 0.875rem;
      font-weight: 600;
      color: var(--brand-blue);
    }
    .tickets-next-name {
      margin-top: 0.125rem;
      font-size: 1.625rem;
      font-weight: 700;
      line-height: 1.2;
      letter-spacing: -0.015em;
      color: rgb(var(--ap-ink));
    }
    .tickets-next-meta,
    .tickets-row-meta {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 0.25rem 0.625rem;
      margin-top: 0.25rem;
      font-size: 0.875rem;
      color: rgb(var(--ap-ink-2));
    }
    .tickets-label {
      margin: 1.75rem 0 0.625rem;
      font-size: 0.6875rem;
      font-weight: 600;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      color: rgb(var(--ap-ink-3));
    }
    .tickets-day {
      display: grid;
      gap: 0 1rem;
      padding: 0.75rem 1.25rem;
    }
    .tickets-day + .tickets-day {
      border-top: 1px solid rgb(var(--ap-border));
    }
    @media (min-width: 640px) {
      .tickets-day {
        grid-template-columns: 7.5rem minmax(0, 1fr);
      }
    }
    .tickets-day-label {
      padding-top: 0.625rem;
      font-size: 0.875rem;
      font-weight: 600;
      color: rgb(var(--ap-ink));
    }
    .tickets-row {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 0.5rem 0;
    }
    .tickets-row-time {
      flex: none;
      width: 4.5rem;
      font-size: 0.875rem;
      font-weight: 600;
      color: var(--brand-blue);
    }
    .tickets-row-name {
      display: block;
      font-size: 0.9375rem;
      font-weight: 600;
      color: rgb(var(--ap-ink));
    }
    .tickets-row-name:hover {
      text-decoration: underline;
    }
    .tickets-row-meta {
      margin-top: 0;
      font-size: 0.8125rem;
    }

    /* A page's quieter actions as words beside its one filled button (the 2026-11 redesign):
       "Actions", "Edit schedule", "Customize". */
    .ap-text-action {
      display: inline-flex;
      align-items: center;
      gap: 0.375rem;
      min-height: 2.75rem;
      padding: 0.5rem 0.625rem;
      border-radius: 0.625rem;
      font-size: 0.875rem;
      font-weight: 600;
      white-space: nowrap;
      color: rgb(var(--ap-ink));
      transition: background-color 0.2s;
    }
    .ap-text-action:hover {
      background: rgb(var(--ap-surface-hover));
    }
    .ap-text-action:focus-visible {
      outline: none;
      box-shadow: 0 0 0 3px var(--ap-focus-ring);
    }
    .ap-text-action svg {
      width: 1.125rem;
      height: 1.125rem;
      margin: 0;
      color: rgb(var(--ap-ink-3));
    }

    /* A tab that is a button on the page (it shows a pane, it does not go anywhere) says it is the
       one showing with aria-selected, where a link says aria-current. */
    button.ap-tab {
      border: 0;
      background: none;
      cursor: pointer;
    }
    .ap-tab[aria-selected="true"] {
      background: rgb(var(--ap-surface));
      color: var(--brand-blue-dark);
    }
    /* A strip inside a card (the attendee import's Form entry / Upload CSV): the white pill would
       vanish on the card, so there the tab you are on is the card's own step deeper. */
    .ap-card .ap-tab[aria-selected="true"],
    .ap-card .ap-tab[aria-current="page"],
    .ap-card .ap-tab:hover {
      background: rgb(var(--ap-surface-active));
    }
    /* The second row of a section with groups (the platform admin's): the pages of the group you
       are in, all of them in sight. They used to sit in a menu behind the tab. */
    .ap-subtabs {
      display: flex;
      flex-wrap: wrap;
      gap: 0.25rem;
      margin: -0.75rem 0 1.5rem;
    }
    .ap-subtab {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      border-radius: 0.5rem;
      padding: 0.375rem 0.75rem;
      font-size: 0.875rem;
      font-weight: 500;
      white-space: nowrap;
      color: rgb(var(--ap-ink-3));
      transition: background-color 0.2s, color 0.2s;
    }
    .ap-subtab:hover {
      background: var(--ap-tint-1);
      color: rgb(var(--ap-ink));
    }
    .ap-subtab[aria-current="page"] {
      background: var(--ap-tint-2);
      box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.08);
      color: rgb(var(--ap-ink));
    }
    .dark .ap-subtab[aria-current="page"] {
      box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.5);
    }
    .ap-subtab:focus-visible {
      outline: 2px solid var(--brand-blue);
      outline-offset: 1px;
    }

    /* A page built on this kit wraps what it shows in .page-shell (with .page-col is-narrow
       beside it when the page is one form). Inside it the portal's older capitals-and-letterspacing
       buttons (secondary, danger, primary) speak in the brand button's sentence case, as they do
       inside the three forms. */
    .participant-directory {
      color: rgb(var(--ap-ink));
    }
    .participant-directory label {
      color: rgb(var(--ap-ink-2));
    }
    .participant-directory input:not([type="hidden"]),
    .participant-directory textarea {
      color: rgb(var(--ap-ink));
      background-color: rgb(var(--ap-surface));
      border-color: rgb(var(--ap-border-strong));
    }
    .participant-directory .participant-social-item,
    .participant-directory .participant-social-row {
      display: flex;
      align-items: center;
      gap: 0.625rem;
      padding-block: 0.5rem;
      min-width: 0;
    }
    .participant-directory .participant-social-name,
    .participant-directory .participant-social-row > span {
      flex: 0 0 7.25rem;
      color: rgb(var(--ap-ink-2));
      font-size: 0.8125rem;
      font-weight: 600;
    }
    .participant-directory .participant-social-row input {
      min-width: 0;
      height: 2.75rem;
      padding: 0.5rem 0.75rem !important;
      font-size: 0.875rem !important;
    }
    .participant-directory .participant-social-url {
      flex: 1 1 0;
      min-width: 0;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
    .participant-directory .participant-social-delete {
      flex: 0 0 auto;
      margin: 0;
    }
    .participant-directory .participant-social-trash {
      display: inline-flex;
      width: 2.75rem;
      height: 2.75rem;
      align-items: center;
      justify-content: center;
      border: 1px solid rgb(var(--ap-border-strong));
      border-radius: 0.75rem;
      color: rgb(var(--ap-ink-3));
      transition: color 200ms, border-color 200ms, background-color 200ms;
    }
    .participant-directory .participant-social-trash:hover {
      color: rgb(var(--ap-danger, 185 28 28));
      border-color: rgb(var(--ap-border-strong));
      background-color: rgb(var(--ap-surface-hover));
    }
    .participant-directory .participant-social-trash svg {
      width: 1.125rem;
      height: 1.125rem;
    }
    .participant-directory .participant-social-row select {
      color: rgb(var(--ap-ink));
      background-color: rgb(var(--ap-surface));
      border-color: rgb(var(--ap-border-strong));
      flex: 0 0 8.5rem;
      width: 8.5rem;
      height: 2.75rem;
      padding: 0.5rem 2rem 0.5rem 0.75rem !important;
      font-size: 0.8125rem !important;
    }
    .participant-directory .participant-social-row select option {
      color: rgb(var(--ap-ink));
      background-color: rgb(var(--ap-surface));
    }
    html[data-theme="espresso"] .participant-directory .participant-social-row select,
    html[data-theme="midnight"] .participant-directory .participant-social-row select,
    html[data-theme="carbon"] .participant-directory .participant-social-row select {
      color-scheme: dark;
    }
    @media (max-width: 639.98px) {
      .participant-directory .participant-social-item,
      .participant-directory .participant-social-row { gap: 0.375rem; }
      .participant-directory .participant-social-name,
      .participant-directory .participant-social-row > span,
      .participant-directory .participant-social-row select {
        flex-basis: 6.25rem;
        width: 6.25rem;
      }
      .participant-directory .participant-social-row input { flex: 1 1 0; }
    }
    .participant-directory input::file-selector-button {
      color: rgb(var(--ap-ink));
      background-color: rgb(var(--ap-surface-hover));
      border: 1px solid rgb(var(--ap-border-strong));
      border-radius: 0.5rem;
      padding: 0.5rem 0.75rem;
    }
    .page-shell button.uppercase,
    .page-shell a.uppercase {
      font-size: 0.875rem;
      letter-spacing: normal;
      text-transform: none;
    }

    /* Put away is put away. Several of the kit's pieces set their own display (a tool button, a
       chip, a strip of figures), which outranks the hidden attribute. */
    .page-shell [hidden] {
      display: none !important;
    }

    /* One rhythm between the blocks of a page. */
    .page-stack {
      display: grid;
      gap: 1rem;
      min-width: 0;
    }
    .page-stack > * {
      min-width: 0;
    }
    .page-grid2 {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 1rem;
    }
    @media (max-width: 1023.98px) {
      .page-grid2 {
        grid-template-columns: minmax(0, 1fr);
      }
    }
    /* A card that fills the row when its neighbour has nothing to show and is left out. */
    .page-grid2 > :only-child {
      grid-column: 1 / -1;
    }
    /* A page that is one form (add a member, hand a schedule over, scan a ticket): one column of
       one width, in the middle of the frame, and its header with it, so the page reads as one
       task. A form in the platform admin is not one of these: it stands under the admin
       navigation, which runs the frame, and a narrower column under that had two left edges. */
    .page-col.is-narrow {
      max-width: 48rem;
      margin-inline: auto;
    }
    /* What a refused form got wrong is listed by the layout, above the page and at the frame's
       width. Over a page that is one centred column it takes the column's width and place: left
       at the frame's, the notice began 16rem to the left of the form it is about. Found by a
       class of its own and not by its role: this stylesheet is printed into every page, and the
       tests count the alerts on a page by that attribute's text. */
    .ap-form-errors:has(~ .page-col.is-narrow) {
      max-width: 48rem;
      margin-inline: auto;
    }

    /* A card (with ap-card rounded-xl): what it holds, one quiet line about it, then the thing. */
    .page-card {
      padding: 1.25rem;
    }
    .page-card-head {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 0.5rem 1rem;
      margin-bottom: 1rem;
    }
    /* A card's name as a small capitals label (the 2026-11 redesign: "Recent check-ins",
       "Sources", "Coming up"): 12px/600, +0.06em, in the label ink (#6a7183 on Mist), no icon,
       and a count said in the label ("Coming up · 56"). */
    .page-card-title {
      margin: 0;
      font-size: 0.75rem;
      font-weight: 600;
      line-height: 1.25rem;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      color: rgb(var(--ap-ink-3));
    }
    .page-card-lead {
      max-width: 44rem;
      margin: 0.125rem 0 0;
      font-size: 0.8125rem;
      color: rgb(var(--ap-ink-3));
    }
    /* A card that is a list keeps the list flush with its edges, and its head gets the padding. */
    .page-card.is-flush {
      overflow: hidden;
      padding: 0;
    }
    .page-card.is-flush > .page-card-head {
      margin: 0;
      border-bottom: 1px solid rgb(var(--ap-border));
      padding: 1rem 1.25rem;
    }
    .page-card.is-flush > .page-card-foot {
      border-top: 1px solid rgb(var(--ap-border));
      padding: 0.75rem 1.25rem;
    }
    .page-card-foot {
      font-size: 0.8125rem;
      color: rgb(var(--ap-ink-3));
    }
    .page-card:not(.is-flush) > .page-card-foot {
      margin-top: 1rem;
    }
    /* A short strip of tabs that a phone keeps as a strip (x-page-tabs with `strip`): three tabs
       fit across one, and under the platform admin's own dropdown a second dropdown reading the
       same word says nothing. */
    .ap-tabs-wrap.is-always {
      display: block;
    }

    /* The row above a list: what narrows it, then what acts on all of it at the end. */
    .page-filters {
      display: flex;
      flex-wrap: wrap;
      align-items: flex-end;
      gap: 0.625rem;
      margin: 0 0 1rem;
    }
    .page-filters > .is-grow {
      flex: 1 1 14rem;
      max-width: 24rem;
      min-width: 0;
    }
    .page-filters > .is-end {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 0.5rem;
      margin-inline-start: auto;
    }
    .page-filter {
      display: flex;
      flex-direction: column;
      gap: 0.25rem;
      min-width: 0;
    }
    .page-filter > span,
    .page-filter > label {
      font-size: 0.75rem;
      font-weight: 600;
      color: rgb(var(--ap-ink-3));
    }
    .page-filters input[type="text"],
    .page-filters input[type="search"],
    .page-filters input[type="number"],
    .page-filters select {
      max-width: 100%;
    }
    /* From and To: one pair, on one line at every width. On a phone they were split across
       two rows, with To alone on the second. */
    .page-filter-pair {
      display: flex;
      align-items: flex-end;
      gap: 0.625rem;
      min-width: 0;
    }
    @media (max-width: 639.98px) {
      .page-filters > .page-filter-pair {
        flex: 1 1 100%;
      }
      .page-filter-pair > .page-filter {
        flex: 1 1 0;
      }
      .page-filter-pair input {
        width: 100% !important;
      }
    }
    /* The layout gives every field of the portal 1.15rem type and three quarters of a rem of
       padding on all sides, with !important. In a row of filters that made six of them break
       into two rows, and on any select it takes back the room the arrow needs, so "Last 30 Days"
       ran under its own chevron. Said again here, as firmly, for the rows this kit draws. */
    .page-filters input[type="text"],
    .page-filters input[type="search"],
    .page-filters input[type="number"],
    .page-filters input[type="email"],
    .page-filters select {
      padding-block: 0.6875rem !important;
      padding-inline: 0.75rem !important;
      font-size: 0.875rem !important;
      line-height: 1.25rem !important;
    }
    .page-filters select,
    .page-actions select,
    .page-card-head select {
      padding-inline-end: 2.25rem !important;
    }
    /* The select beside a page's lead (the period an admin page reports on) is the size of a
       filter. Beside a page's TITLE it keeps the layout's size, where it stands among buttons. */
    .page-head .page-actions select,
    .page-card-head select {
      padding-block: 0.6875rem !important;
      padding-inline-start: 0.75rem !important;
      font-size: 0.875rem !important;
      line-height: 1.25rem !important;
    }
    /* The buttons at the end of the row are the height of its fields: 44px, the least the
       review allows anything pressed (the fields above are 44px too). */
    .page-filters > .is-end > a,
    .page-filters > .is-end > button {
      min-height: 2.75rem;
      padding: 0.5rem 0.875rem;
      font-size: 0.875rem;
      line-height: 1.25rem;
    }
    @media (max-width: 639.98px) {
      .page-filters > .is-grow {
        flex-basis: 100%;
        max-width: none;
      }
      .page-filters > .is-end {
        flex: 1 1 100%;
      }
    }

    /* A strip of figures of any length. The lines between them are drawn from each figure's own
       top and start edge and clipped at the strip's, so they hold wherever the row breaks. */
    .page-stats.is-auto {
      overflow: hidden;
      grid-template-columns: repeat(auto-fit, minmax(9rem, 1fr));
    }
    .page-stats.is-auto > .page-stat {
      border: 0;
      box-shadow: -1px 0 0 rgb(var(--ap-border)), 0 -1px 0 rgb(var(--ap-border));
    }
    [dir="rtl"] .page-stats.is-auto > .page-stat {
      box-shadow: 1px 0 0 rgb(var(--ap-border)), 0 -1px 0 rgb(var(--ap-border));
    }
    .page-stat-value {
      font-variant-numeric: tabular-nums;
    }
    /* A figure that leads to the list behind it. */
    a.page-stat {
      transition: background-color 0.2s;
    }
    a.page-stat:hover {
      background: var(--ap-tint-1);
    }
    a.page-stat:focus-visible {
      outline: 2px solid var(--brand-blue);
      outline-offset: -2px;
    }
    .page-stat-value.is-good {
      color: #15803d;
    }
    .page-stat-value.is-warn {
      color: #b45309;
    }
    .page-stat-value.is-bad {
      color: #b91c1c;
    }
    .dark .page-stat-value.is-good {
      color: #4ade80;
    }
    .dark .page-stat-value.is-warn {
      color: #fbbf24;
    }
    .dark .page-stat-value.is-bad {
      color: #f87171;
    }
    .page-stat-sub {
      margin-top: 0.125rem;
      font-size: 0.75rem;
      color: rgb(var(--ap-ink-3));
    }
    @media (max-width: 639.98px) {
      .page-stats.is-auto {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
      /* The odd one out takes the row, where it left half of it empty. */
      .page-stats.is-auto > .page-stat:last-child:nth-child(odd) {
        grid-column: 1 / -1;
      }
    }

    /* Two more tones for the kit's status mark (a dot and a word): something failed or was
       called off, and something that is only worth noting. */
    .event-status.is-bad {
      color: #b91c1c;
    }
    .dark .event-status.is-bad {
      color: #f87171;
    }
    .event-status.is-bad::before {
      background: #ef4444;
    }
    .event-status.is-info {
      color: var(--brand-blue);
    }
    .event-status.is-info::before {
      background: var(--brand-blue);
    }

    /* More of the list. */
    .page-table .c-num {
      text-align: end;
      font-variant-numeric: tabular-nums;
      white-space: nowrap;
    }
    .page-table .c-quiet {
      color: rgb(var(--ap-ink-3));
    }
    .page-table .c-strong {
      font-weight: 600;
      color: rgb(var(--ap-ink));
    }
    .page-table .c-sub {
      display: block;
      margin-top: 0.125rem;
      font-size: 0.8125rem;
      font-weight: 400;
      color: rgb(var(--ap-ink-3));
      overflow-wrap: anywhere;
    }
    .page-table .c-wrap {
      overflow-wrap: anywhere;
    }
    .page-table .event-status {
      white-space: nowrap;
    }
    /* From a tablet up the list is a real table, and "anywhere" lets a column shrink below its own
       words: an address read "owner@example.te" over "st". There a long word breaks only when
       it has to. A person's name and address do so only in a list that can be scrolled sideways
       (.page-scroll): a word that will not break is the least width its column can have, and in a
       card that cuts what does not fit (a schedule's Team and Followers) one long address pushed
       Remove out of the card on a small laptop. */
    @media (min-width: 640px) {
      .page-table .c-wrap,
      .page-table .c-sub,
      .page-scroll .page-table .page-person-text {
        overflow-wrap: break-word;
      }
    }
    /* A long code (a payment reference) keeps to one line in the table and gives its room to
       the columns people read; its title carries the whole of it, and a phone's row shows it all. */
    @media (min-width: 640px) {
      .page-table .c-clip {
        display: inline-block;
        max-width: 11rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align: bottom;
      }
    }
    /* A chip that opens its cell, or its line, has nothing before it to stand off from. */
    .page-table td > .event-chip:first-child,
    .page-table .c-sub > .event-chip:first-child {
      margin-inline-start: 0;
    }
    /* Filters that are links (All, Pending, Expired): the one in force is pressed in. */
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
      transition: border-color 0.2s, color 0.2s;
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
    .page-table .c-mono {
      font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
      font-size: 0.8125rem;
    }
    /* A row that leads somewhere says so under the pointer. */
    .page-table.is-hover tbody tr:hover td {
      background: var(--ap-tint-1);
    }
    /* A list with more columns than a laptop has room for scrolls inside its card (from a tablet
       up: on a phone it is a stack of rows like every other list here). */
    .page-scroll {
      overflow-x: auto;
    }
    @media (min-width: 640px) {
      .page-table.is-wide {
        min-width: 52rem;
      }
    }
    @media (max-width: 639.98px) {
      /* A figure that would be a bare number in the stack says what it counts. */
      .page-table td[data-label]:not(:empty)::before {
        content: attr(data-label) ": ";
        font-weight: 400;
        color: rgb(var(--ap-ink-3));
      }
      .page-table .c-num {
        text-align: start;
      }
    }
    .page-pager {
      margin-top: 1rem;
    }
    /* A notice runs the width it is given; its words keep a line a person can read, and its
       action stays at the far end. */
    .page-notice-text {
      max-width: 110ch;
    }
    .page-notice-action {
      margin-inline-start: auto;
    }
    /* What the hidden headings of a phone's stacked list could do, above the list
       (partials/admin-page-script builds it): sort it, turn the order round, tick every row. */
    .page-table-phone {
      display: none;
    }
    @media (max-width: 639.98px) {
      .page-table-phone {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.5rem;
        border-bottom: 1px solid rgb(var(--ap-border));
        padding: 0.5rem 1rem;
      }
      .page-table-phone > .event-link {
        margin-inline-end: auto;
      }
      .page-table-phone select {
        min-width: 0;
        max-width: 100%;
        padding-block: 0.4rem !important;
        padding-inline: 0.75rem 2.25rem !important;
        font-size: 0.875rem !important;
        line-height: 1.25rem !important;
      }
      .page-table-phone .page-tool {
        padding: 0.4375rem;
      }
    }

    /* Pairs: a name and its figure, down a card. */
    .page-kv {
      margin: 0;
    }
    .page-kv > div {
      display: flex;
      align-items: baseline;
      justify-content: space-between;
      gap: 1rem;
      border-top: 1px solid rgb(var(--ap-border));
      padding: 0.5rem 0;
      font-size: 0.875rem;
    }
    .page-kv > div:first-child {
      border-top: 0;
      padding-top: 0;
    }
    .page-kv > div:last-child {
      padding-bottom: 0;
    }
    .page-kv dt {
      min-width: 0;
      color: rgb(var(--ap-ink-2));
    }
    .page-kv dd {
      margin: 0;
      font-weight: 600;
      font-variant-numeric: tabular-nums;
      white-space: nowrap;
      color: rgb(var(--ap-ink));
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

    /* A small form on a page of its own: its fields, then Cancel and the button that goes on, the
       one that goes on at the end. */
    .page-form-fields {
      display: grid;
      gap: 1.25rem;
    }
    /* A form runs the width of its card. The column is the measure now (1040px with its
       gutters): the 48rem caps on fields, pairs and button rows, and the cards that set their
       title beside their body on a wide window, were there to keep an 80rem frame from looking
       stretched, and in the narrower column they only left two right edges in one card. */
    /* In a filter row that is sent with a button, the search takes the room that is left, so
       the button stands beside its last field and not at the far end of the frame. A row that
       filters as you type (Following, Sales) has no button to strand and keeps its small box. */
    form.page-filters > .is-grow {
      max-width: none;
    }
    .page-form-actions {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: flex-end;
      gap: 0.75rem;
      margin-top: 1.5rem;
    }
    .page-form-actions.is-split {
      justify-content: space-between;
    }
    /* In a row of actions a red or a bordered button stands beside a brand one, and takes its
       type and padding so the two are one height: the older buttons are set in 14px capitals,
       which made "Send transfer request" 46px beside a 50px Cancel. */
    .page-shell .page-form-actions > button.uppercase,
    .page-shell .page-form-actions > a.uppercase {
      padding-inline: 1rem;
      font-size: 1rem;
      line-height: 1.5rem;
    }
    @media (max-width: 639.98px) {
      .page-form-actions > * {
        flex: 1 1 auto;
        justify-content: center;
      }
    }

    /* A small bordered button for a list's own tools (Export, Refresh, View ticket, Check-in),
       as the 2026-11 redesign draws them: 13px/600 in plain ink, the field border, 44px tall
       (the review's least height for anything pressed),
       and the brand blue on its border and words when pointed at. Buttons that do the page's
       main thing are x-brand-button. */
    .page-tool {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.375rem;
      min-height: 2.75rem;
      border: 1px solid rgb(var(--ap-border-strong));
      border-radius: 0.625rem;
      padding: 0 0.75rem;
      background: rgb(var(--ap-surface));
      font-size: 0.8125rem;
      font-weight: 600;
      line-height: 1.25rem;
      white-space: nowrap;
      color: rgb(var(--ap-ink));
      cursor: pointer;
      transition: background-color 0.2s, color 0.2s, border-color 0.2s;
    }
    .page-tool:hover {
      border-color: var(--brand-blue);
      color: var(--brand-blue);
    }
    .page-tool:focus-visible {
      outline: none;
      border-color: var(--brand-blue);
      box-shadow: 0 0 0 3px var(--ap-focus-ring);
    }
    .page-tool svg {
      flex: none;
      width: 1rem;
      height: 1rem;
    }
    .page-tool[disabled] {
      opacity: 0.5;
      cursor: not-allowed;
    }
    /* The small button that destroys (Clear log, Flush, Suspend): red, and apart from the rest. */
    .page-tool.is-danger {
      border-color: rgba(220, 38, 38, 0.4);
      color: #b91c1c;
    }
    .page-tool.is-danger:hover {
      border-color: #dc2626;
      background: rgba(220, 38, 38, 0.08);
      color: #b91c1c;
    }
    .dark .page-tool.is-danger,
    .dark .page-tool.is-danger:hover {
      border-color: rgba(248, 113, 113, 0.5);
      color: #f87171;
    }
    .page-card .page-form-actions {
      margin-top: 1.25rem;
    }
    /* The arrow inside a name on the overview pages that opens its guest page
       (components/public-page-icon): it turns brand blue as the name is underlined. The arrow that
       stands on its own (components/public-page-link) does the same with its own hover. */
    .public-page-name:hover .public-page-icon,
    .public-page-name:focus-visible .public-page-icon {
      color: var(--brand-blue);
    }
    /* A watched word inside the text it was found in (SpamWatch::highlight(), on /admin/blocked
       and an account's page), in amber, the colour of the alert that leads there. No padding:
       inside an address it would read as a space. */
    .spam-word {
      background: rgb(254 243 199);
      color: rgb(120 53 15);
      border-radius: 0.125rem;
    }
    .dark .spam-word {
      background: rgb(120 53 15 / 0.55);
      color: rgb(253 230 138);
    }

    /* The 2026-11 redesign: a row's own tools in a list are quiet words, not boxed buttons; a stat
       tile's figure starts where its label does. */
    .page-table td .page-tool,
    .page-table td button.page-tool {
      border-color: transparent;
      background: none;
      box-shadow: none;
      font-weight: 600;
    }
    .page-table td .page-tool:hover {
      background: rgb(var(--ap-surface-hover));
    }
    .admin-stat-figure .items-center {
      align-items: flex-start;
    }
    .admin-stat-figure .text-center {
      text-align: start;
    }
    .admin-stat-figure .justify-center {
      justify-content: flex-start;
    }
    /* A card's own heading reads as the design's small capitals label, wherever a card names
       itself with the old bold line. Not a dialog's title or a notice's (role dialog, status,
       alert; written unquoted so the page's markup holds no stray role attribute text): those are sentences ("Where is The Cellar?", "Your venue map is being prepared"),
       and a sentence in capitals reads as shouting. */
    .ap-card:not([role=dialog], [role=alertdialog], [role=status], [role=alert]) :is(h2, h3).text-base.font-semibold:not(:where([role=dialog], [role=alertdialog], [role=status], [role=alert]) *) {
      font-size: 0.75rem;
      line-height: 1.25rem;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      color: rgb(var(--ap-ink-3));
    }
    /* Decoration: no icon chips (the design-language review). A card names itself in words, so
       the tinted icon tile beside a card's heading (.dashboard-icon, the stat panels' old chip)
       and an icon opening the heading itself are put away wherever a card or a page of the kit
       still draws one. The icons are aria-hidden, so nothing is lost to a screen reader. */
    .ap-card .dashboard-icon,
    .page-shell .dashboard-icon,
    .ap-card :is(h2, h3).text-base.font-semibold > svg:first-child,
    .page-card-title > svg {
      display: none;
    }
    /* A page's title stands alone (the 2026-11 redesign): no schedule picture beside it. */
    .page-top-figure {
      display: none;
    }
    .page-top-text.has-figure {
      display: block;
    }
    /* The sidebar, as the 2026-11 design-language review draws it: the cool rail, 18rem (288px)
       wide (layouts/app-admin, lg:w-72), rows 40px with 15px/600 names, 10px corners and 28px
       icons, a schedule's letter in a 22px tile, the group names as small capitals labels
       (11px/600, +0.08em) in the rail's second ink, the open row a flat step lighter, the footer
       a 52px band of its own. Sizes only: the colours are the palette's --ap-rail* tokens (the
       default Mist palette carries the review's #14161a and #262a33; the others keep their own
       hue). It replaced round 1's warm #12110d rail with 48px rows and 18px names. */
    .sidebar-gradient {
      background: rgb(var(--ap-rail)) !important;
    }
    .sidebar-gradient > div:first-child {
      padding-inline: 1.25rem;
    }
    /* The logo stays put while the list under it scrolls (the rail is scrolled to the schedule in
       use on load, which used to take the logo off the top). */
    .sidebar-gradient > div:first-child > a:first-child {
      position: sticky;
      top: 0;
      z-index: 2;
      background: rgb(var(--ap-rail));
    }
    .sidebar-gradient > div:first-child > a:first-child > div {
      height: auto;
      padding: 1rem 0 0.625rem;
    }
    .sidebar-gradient > div:first-child > a:first-child img {
      height: 2.25rem;
    }
    .sidebar-gradient nav {
      margin-top: 0.25rem;
    }
    .sidebar-gradient nav > ul {
      row-gap: 0.125rem;
    }
    /* The list after the schedules (Following, Tickets, Sales ...) stands a little apart. */
    .sidebar-gradient nav > ul > li:last-child:not(:first-child) {
      margin-top: 0.75rem;
    }
    .sidebar-gradient nav ul ul {
      margin-inline: -0.5rem;
    }
    .sidebar-gradient nav ul ul > li + li {
      margin-top: 0.125rem !important;
    }
    .sidebar-gradient .dark-nav-hover {
      min-height: 2.5rem;
      padding: 0 0.5rem;
      gap: 0.625rem;
      border-radius: 0.625rem;
      font-size: 0.9375rem;
      font-weight: 600;
      line-height: 1.25rem;
      color: rgb(var(--ap-rail-ink));
    }
    .sidebar-gradient .dark-nav-hover > a,
    .sidebar-gradient .dark-nav-hover.flex {
      column-gap: 0.625rem;
    }
    .sidebar-gradient .dark-nav-hover svg.h-8 {
      width: 1.75rem;
      height: 1.75rem;
      margin: 0;
    }
    /* The row's own tools (edit, open the guest page), shown when it is pointed at: 32px each,
       inside the 40px row. */
    .sidebar-gradient .dark-nav-hover a.p-2 {
      padding: 0.375rem;
    }
    .sidebar-gradient .dark-nav-hover:hover,
    .sidebar-gradient .dark-nav-active {
      background: rgb(var(--ap-rail-active)) !important;
      color: #fff;
    }
    .sidebar-gradient .schedule-badge {
      width: 1.375rem;
      height: 1.375rem;
      margin: 0.1875rem;
      border: 0;
      border-radius: 0.375rem;
      background: rgb(var(--ap-rail-hover));
      font-size: 0.625rem;
      font-weight: 600;
      color: rgb(var(--ap-rail-ink));
    }
    .sidebar-gradient .dark-nav-hover:hover .schedule-badge,
    .sidebar-gradient .dark-nav-active .schedule-badge {
      background: color-mix(in srgb, rgb(var(--ap-rail-active)) 80%, #fff);
    }
    .sidebar-gradient nav > ul > li > .text-sm.font-semibold {
      padding: 1rem 0 0.25rem;
      font-size: 0.6875rem;
      line-height: 1rem;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: rgb(var(--ap-rail-ink-2));
    }
    .sidebar-gradient nav > ul > li > ul.mt-2 {
      margin-top: 0;
    }
    .sidebar-gradient .sidebar-footer-bar {
      align-items: center;
      justify-content: space-around;
      min-height: 3.25rem;
      padding: 0.25rem 0.5rem;
      background: color-mix(in srgb, rgb(var(--ap-rail-hover)) 70%, rgb(var(--ap-rail)));
    }
    /* Help, Contact, About and the theme: 44px to press, the least the review allows. */
    .sidebar-gradient .sidebar-footer-bar .theme-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 2.75rem;
    }

    {{-- Builder D (2026-11-24): the hero card lift. The one card a page is about (an appointment
         type, the Plan card, the Next up ticket, the check-in count, the Analytics hero, the
         dashboard's setup guide)
         stands a little higher than the cards around it: a 20px corner and a soft blue-tinted
         shadow, from the designs. Put .ap-hero beside .ap-card (or on <x-page-card class="ap-hero">).
         Dark mode keeps the shape and trades the blue glow for a deeper plain shadow, which is what
         reads as lift on a dark surface. Kept to tokens so every palette follows. --}}
    :root {
      --ap-hero-radius: 1.25rem;
      --ap-hero-shadow: 0 2px 4px rgba(20, 22, 26, 0.05), 0 12px 32px rgba(36, 86, 230, 0.10);
    }
    .dark {
      --ap-hero-shadow: 0 2px 4px rgba(0, 0, 0, 0.30), 0 12px 32px rgba(0, 0, 0, 0.38), 0 0 0 1px rgba(120, 150, 255, 0.06);
    }
    .ap-card.ap-hero,
    .ap-card.rounded-xl.ap-hero,
    .ap-card.rounded-2xl.ap-hero,
    .ap-hero {
      border-radius: var(--ap-hero-radius);
      box-shadow: var(--ap-hero-shadow);
    }
    /* The dashboard's setup guide card (SetupGuide.vue's .sg-section) is the design's other hero. */
    .sg-section.sg-surface {
      border-radius: var(--ap-hero-radius);
      box-shadow: var(--ap-hero-shadow);
    }
</style>
