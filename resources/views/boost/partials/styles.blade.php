{{-- What the Boost pages need beyond the portal's page kit (partials/admin-page-styles): a thin
     meter for money spent against a budget, the dialog that picks an event, the two columns of the
     create pages and the frame an ad preview sits in. Written like the kit: plain CSS on the
     portal's tokens and logical properties, so every palette and both directions follow. --}}
<style {!! nonce_attr() !!}>
    /* Spent against budget. In a list's cell it sits under the figure; on a campaign's page it
       takes the card's width. */
    .boost-meter {
      height: 0.375rem;
      overflow: hidden;
      border-radius: 999px;
      background: var(--ap-tint-2);
    }
    .boost-meter > i {
      display: block;
      height: 100%;
      border-radius: 999px;
      background: var(--brand-button-bg);
    }
    .page-table .boost-meter {
      width: 5.5rem;
      margin-top: 0.375rem;
      margin-inline-start: auto;
    }
    .boost-meter-ends {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      gap: 0.25rem 1rem;
      margin-bottom: 0.5rem;
      font-size: 0.875rem;
      font-variant-numeric: tabular-nums;
      color: rgb(var(--ap-ink-2));
    }
    .boost-meter + .page-kv,
    .boost-meter + .boost-facts {
      margin-top: 1.25rem;
    }
    /* A few figures across a card, each under its name. */
    .boost-facts {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(9rem, 1fr));
      gap: 0.875rem 1.25rem;
      margin: 0;
    }
    .boost-facts dt {
      font-size: 0.8125rem;
      color: rgb(var(--ap-ink-3));
    }
    .boost-facts dd {
      margin: 0.125rem 0 0;
      font-size: 0.9375rem;
      font-weight: 600;
      font-variant-numeric: tabular-nums;
      color: rgb(var(--ap-ink));
    }
    .boost-facts dd span {
      font-weight: 400;
      color: rgb(var(--ap-ink-3));
    }
    @media (max-width: 639.98px) {
      .page-table .boost-meter {
        display: none;
      }
      .boost-facts {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
    }

    /* A status is one thing: "Awaiting review" broke over two lines in the list. */
    .page-table .event-status {
      white-space: nowrap;
    }
    /* Room for a schedule's name beside the picker's own arrow and its clear mark: at the width
       the row squeezed it to, "Lisa Simpson Jazz Quartet" ran under both. */
    .boost-schedule-pick {
      flex: 0 1 17rem;
      min-width: 12.5rem;
      max-width: 20rem;
    }
    @media (max-width: 639.98px) {
      .boost-schedule-pick {
        flex: 1 1 100%;
        max-width: none;
      }
      .page-top > .page-actions > #boost-modal-app {
        flex: 1 1 100%;
      }
      #boost-modal-app > div > button,
      #boost-modal-app > button {
        width: 100%;
      }
    }

    /* A status mark at the end of a title row sits on the buttons' line. */
    .page-actions > .event-status {
      margin-inline-end: 0.5rem;
      font-size: 0.875rem;
      font-weight: 500;
    }
    .page-actions > form {
      display: inline-flex;
    }
    /* The red button beside the others in a title row: their height and their padding. */
    .page-shell button.boost-danger {
      padding-inline: 1rem;
      line-height: 1.5rem;
    }

    /* The dialog that asks which event to boost. */
    .boost-dialog {
      width: calc(100% - 2rem);
      max-width: 28rem;
      border: 1px solid rgb(var(--ap-border));
      border-radius: 0.75rem;
      background: rgb(var(--ap-surface));
      box-shadow: var(--ap-shadow-lg, 0 20px 25px -5px rgba(0, 0, 0, 0.2));
    }
    .boost-dialog-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      padding: 1.25rem 1.25rem 0.75rem;
    }
    .boost-dialog-close {
      border-radius: 0.375rem;
      color: rgb(var(--ap-ink-3));
      transition: color 0.2s;
    }
    .boost-dialog-close:hover {
      color: rgb(var(--ap-ink));
    }
    .boost-dialog-close:focus-visible {
      outline: 2px solid var(--brand-blue);
      outline-offset: 2px;
    }
    .boost-dialog-body {
      padding: 0 1.25rem;
    }
    .boost-dialog-foot {
      margin-top: 0;
      padding: 1rem 1.25rem 1.25rem;
    }

    /* The create pages: the form, and beside it (from a laptop up) the ad as it will look. There is
       one preview, not one for each width: on a phone the column and the form are taken out of
       the layout (display: contents), so the preview can take its place among the form's cards,
       after what it shows and before the payment. */
    .boost-cols {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }
    .boost-cols-main,
    .boost-cols-main > form {
      display: contents;
    }
    .boost-cols-aside {
      order: 1;
      min-width: 0;
    }
    .boost-cols .is-after-preview {
      order: 2;
    }
    @media (min-width: 1024px) {
      .boost-cols {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 24rem);
        align-items: start;
      }
      .boost-cols-main,
      .boost-cols-main > form {
        display: grid;
        gap: 1rem;
        min-width: 0;
      }
      .boost-cols-main > *,
      .boost-cols-main > form > * {
        min-width: 0;
      }
      .boost-cols-aside {
        position: sticky;
        top: 1.5rem;
      }
    }
    /* The place a refusal is written into, where there is nothing to pay (a selfhosted
       installation): no box of its own, and no gap for one, until there is something to say. */
    #stripe-payment-section:not(.ap-card):not(.hidden) {
      display: contents;
    }
    .boost-event {
      display: flex;
      align-items: flex-start;
      gap: 1rem;
    }
    .boost-event > img {
      flex: none;
      width: 5rem;
      border-radius: 0.5rem;
      object-fit: contain;
    }
    .boost-event h2 {
      margin: 0;
      font-size: 1rem;
      font-weight: 600;
      color: rgb(var(--ap-ink));
      overflow-wrap: anywhere;
    }
    .boost-event p {
      margin: 0.125rem 0 0;
      font-size: 0.875rem;
      color: rgb(var(--ap-ink-3));
    }
    /* The budget: a slider and the figure it sets. */
    .boost-budget {
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    .boost-budget input[type="range"] {
      flex: 1;
      min-width: 0;
      accent-color: var(--brand-button-bg);
      cursor: pointer;
    }
    .boost-budget-figure {
      min-width: 5rem;
      font-size: 1.5rem;
      font-weight: 700;
      line-height: 1.2;
      font-variant-numeric: tabular-nums;
      text-align: end;
      color: rgb(var(--ap-ink));
    }
    .boost-note {
      margin: 0.5rem 0 0;
      font-size: 0.8125rem;
      color: rgb(var(--ap-ink-3));
    }
    /* What it costs, on a sunken panel: the last line is the one that is charged. */
    .boost-costs {
      margin-top: 1rem;
      border-radius: 0.5rem;
      padding: 0.75rem 0.875rem;
      background: var(--ap-tint-2);
    }
    .boost-costs .page-kv > div:last-child:not(:first-child) dt,
    .boost-costs .page-kv > div:last-child:not(:first-child) dd {
      font-weight: 600;
      color: rgb(var(--ap-ink));
    }
    .boost-costs .page-kv dd {
      font-weight: 500;
      color: rgb(var(--ap-ink-2));
    }
    .boost-fields {
      display: grid;
      gap: 1rem;
    }
    .boost-fields-2 {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 1rem;
    }
    @media (max-width: 479.98px) {
      /* A pair whose values are words, not figures (the budget and its kind), takes a line each
         on a phone: side by side the select read "Lifetime Budg" under its own arrow. */
      .boost-fields-2.is-words {
        grid-template-columns: minmax(0, 1fr);
      }
    }
    @media (max-width: 639.98px) {
      /* The buttons that end a form share the phone's width between them. */
      .page-form-actions > .page-actions {
        flex: 1 1 100%;
      }
      .page-form-actions > .page-actions > * {
        flex: 1 1 auto;
        justify-content: center;
      }
    }
    .boost-fields label.boost-check,
    .boost-check {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.875rem;
      color: rgb(var(--ap-ink-2));
    }
    .boost-checks {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem 1.5rem;
      margin-top: 0.5rem;
    }
    /* A fact the form shows and does not ask for (where the ad will run). */
    .boost-fixed {
      border-radius: 0.5rem;
      padding: 0.625rem 0.75rem;
      background: var(--ap-tint-2);
      font-size: 0.875rem;
      color: rgb(var(--ap-ink-2));
    }
    .boost-fixed strong {
      display: block;
      font-size: 0.8125rem;
      font-weight: 500;
      color: rgb(var(--ap-ink-3));
    }
    .boost-interest {
      display: inline-flex;
      align-items: center;
      gap: 0.25rem;
      border-radius: 999px;
      padding-block: 0.1875rem;
      padding-inline: 0.75rem 0.5rem;
      background: var(--ap-tint-2);
      font-size: 0.8125rem;
      color: rgb(var(--ap-ink-2));
    }
    .boost-interest button {
      border-radius: 999px;
      padding: 0 0.25rem;
      font-size: 1rem;
      line-height: 1;
      color: rgb(var(--ap-ink-3));
    }
    .boost-interest button:hover {
      color: rgb(var(--ap-ink));
    }
    /* Variants of an ad, side by side where there is room. */
    .boost-ads {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(min(100%, 16rem), 1fr));
      gap: 1.5rem;
    }
    .boost-ad-head {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 0.5rem;
      font-size: 0.8125rem;
      color: rgb(var(--ap-ink-3));
    }
    .boost-ad-foot {
      margin-top: 0.5rem;
      text-align: center;
    }
    .boost-chart {
      position: relative;
      height: 16rem;
    }

    /* ------------------------------------------------------------------------------------------
       Boosts that run through a boost partner's own Page (boost/partner-*): a request card with
       the ad and its words side by side, what changed since the last version, the two checks
       (approval and payment) each on a row of its own, and the dialogs that answer them. */
    .bp-request {
      scroll-margin-top: 1.5rem;
    }
    .bp-request:focus {
      outline: none;
    }
    .bp-request.is-focused {
      box-shadow: 0 0 0 2px var(--brand-blue);
    }
    .bp-request-head {
      display: flex;
      flex-wrap: wrap;
      align-items: flex-start;
      justify-content: space-between;
      gap: 0.5rem 1rem;
      padding: 1.25rem 1.25rem 0;
    }
    .bp-request-title {
      margin: 0;
      font-size: 1.0625rem;
      font-weight: 600;
      color: rgb(var(--ap-ink));
      overflow-wrap: anywhere;
    }
    .bp-request-title a {
      color: inherit;
    }
    .bp-request-title a:hover {
      color: var(--brand-blue);
    }
    .bp-request-meta {
      margin: 0.25rem 0 0;
      font-size: 0.875rem;
      color: rgb(var(--ap-ink-2));
      overflow-wrap: anywhere;
    }
    .bp-request-meta.is-quiet {
      color: rgb(var(--ap-ink-3));
    }
    /* The ad as it will look, and beside it every word of it in full: the preview cuts the
       headline and the link to one line. On a phone the words go under the preview. */
    .bp-ad {
      display: grid;
      gap: 1.25rem;
      padding: 1.25rem;
    }
    @media (min-width: 768px) {
      .bp-ad {
        grid-template-columns: minmax(0, 20rem) minmax(0, 1fr);
        align-items: start;
      }
    }
    .bp-fields {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(min(100%, 14rem), 1fr));
      gap: 0.875rem 1.25rem;
      margin: 0;
    }
    .bp-fields > div.is-wide {
      grid-column: 1 / -1;
    }
    .bp-fields dt {
      font-size: 0.75rem;
      font-weight: 600;
      color: rgb(var(--ap-ink-3));
    }
    .bp-fields dd {
      margin: 0.125rem 0 0;
      font-size: 0.9375rem;
      color: rgb(var(--ap-ink));
      overflow-wrap: anywhere;
    }
    .bp-fields dd.is-link {
      font-size: 0.8125rem;
      color: rgb(var(--ap-ink-2));
      word-break: break-all;
    }
    .bp-fields dd.is-empty {
      color: rgb(var(--ap-ink-4));
    }
    /* What changed since the version before: Before and Now side by side, one under the other
       on a phone, with the note that asked for the change. */
    .bp-changes {
      margin: 0 1.25rem 1.25rem;
      border: 1px solid rgb(var(--ap-border));
      border-radius: 0.75rem;
      padding: 1rem;
      background: var(--ap-tint-1);
    }
    .bp-changes-title {
      margin: 0;
      font-size: 0.875rem;
      font-weight: 600;
      color: rgb(var(--ap-ink));
    }
    .bp-diff {
      display: grid;
      gap: 0.75rem;
      margin-top: 0.75rem;
    }
    .bp-diff-row {
      display: grid;
      gap: 0.5rem;
    }
    @media (min-width: 640px) {
      .bp-diff-row {
        grid-template-columns: 8rem minmax(0, 1fr) minmax(0, 1fr);
        align-items: start;
      }
    }
    .bp-diff-label {
      font-size: 0.8125rem;
      font-weight: 600;
      color: rgb(var(--ap-ink-2));
    }
    .bp-diff-cell {
      min-width: 0;
      border: 1px solid rgb(var(--ap-border));
      border-radius: 0.5rem;
      padding: 0.5rem 0.625rem;
      background: rgb(var(--ap-surface));
      font-size: 0.875rem;
      color: rgb(var(--ap-ink));
      overflow-wrap: anywhere;
    }
    .bp-diff-cell.is-now {
      border-color: var(--brand-blue);
    }
    .bp-diff-tag {
      display: block;
      margin-bottom: 0.125rem;
      font-size: 0.6875rem;
      font-weight: 600;
      letter-spacing: 0.04em;
      text-transform: uppercase;
      color: rgb(var(--ap-ink-3));
    }
    .bp-diff-cell img {
      display: block;
      max-width: 9rem;
      border-radius: 0.375rem;
    }
    /* A note somebody wrote, quoted: a tinted panel with a full border, never a stripe down one
       side. */
    .bp-note {
      margin: 0.75rem 0 0;
      border: 1px solid rgb(var(--ap-border));
      border-radius: 0.5rem;
      padding: 0.625rem 0.75rem;
      background: rgb(var(--ap-surface));
    }
    .bp-note figcaption {
      font-size: 0.75rem;
      font-weight: 600;
      color: rgb(var(--ap-ink-3));
    }
    .bp-note blockquote {
      margin: 0.125rem 0 0;
      font-size: 0.875rem;
      color: rgb(var(--ap-ink));
      white-space: pre-line;
      overflow-wrap: anywhere;
    }
    /* The two checks, approval and payment: a row each, what it says, and its buttons at the end
       (the one that goes on last). The buttons take a line of their own on a phone. */
    .bp-checks {
      border-top: 1px solid rgb(var(--ap-border));
    }
    .ap-card > .bp-checks:first-child {
      border-top: 0;
    }
    .bp-check {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 0.5rem 1rem;
      padding: 0.875rem 1.25rem;
    }
    .bp-check + .bp-check {
      border-top: 1px solid rgb(var(--ap-border));
    }
    .bp-check-name {
      flex: none;
      min-width: 6rem;
      font-size: 0.875rem;
      font-weight: 600;
      color: rgb(var(--ap-ink));
    }
    .bp-check-state {
      flex: 1 1 14rem;
      min-width: 0;
      font-size: 0.875rem;
      color: rgb(var(--ap-ink-2));
      overflow-wrap: anywhere;
    }
    .bp-check-state .event-status {
      margin-inline-end: 0.375rem;
    }
    .bp-check-actions {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 0.5rem 0.75rem;
      margin-inline-start: auto;
    }
    .bp-check-actions form {
      display: inline-flex;
    }
    @media (max-width: 639.98px) {
      .bp-check-actions {
        flex: 1 1 100%;
      }
      .bp-check-actions > button,
      .bp-check-actions > a {
        flex: 1 1 auto;
        justify-content: center;
      }
    }
    /* The dialogs that answer a request: approve, ask for changes, record a payment. */
    .bp-dialog {
      width: calc(100% - 2rem);
      max-width: 30rem;
      border: 1px solid rgb(var(--ap-border));
      border-radius: 0.75rem;
      padding: 0;
      background: rgb(var(--ap-surface));
      color: rgb(var(--ap-ink));
      box-shadow: var(--ap-shadow-lg, 0 20px 25px -5px rgba(0, 0, 0, 0.2));
    }
    .bp-dialog::backdrop {
      background: rgba(0, 0, 0, 0.5);
    }
    .bp-dialog form {
      display: grid;
      gap: 1rem;
      padding: 1.25rem;
    }
    .bp-dialog-title {
      margin: 0;
      font-size: 1.0625rem;
      font-weight: 600;
      color: rgb(var(--ap-ink));
    }
    .bp-dialog-lead {
      margin: -0.625rem 0 0;
      font-size: 0.875rem;
      color: rgb(var(--ap-ink-3));
      overflow-wrap: anywhere;
    }
    .bp-dialog-text {
      margin: 0;
      font-size: 0.875rem;
      color: rgb(var(--ap-ink-2));
    }
    .bp-dialog-part {
      display: grid;
      gap: 0.875rem;
      border-radius: 0.5rem;
      padding: 0.875rem;
      background: var(--ap-tint-1);
    }
    .bp-dialog-part[hidden] {
      display: none;
    }
    .bp-dialog .page-form-actions {
      margin-top: 0.25rem;
    }
    /* The three steps a first request goes through, said before it is sent. */
    .bp-steps {
      margin: 0.25rem 0 0;
      padding-inline-start: 1.25rem;
      list-style: decimal;
    }
    .bp-steps li + li {
      margin-top: 0.125rem;
    }
    /* A hint under a field of the edit form: what changing it does to a running ad. */
    .bp-hint {
      margin: 0.25rem 0 0;
      font-size: 0.8125rem;
      color: rgb(var(--ap-ink-3));
    }
    .bp-picture {
      display: flex;
      flex-wrap: wrap;
      align-items: flex-start;
      gap: 1rem;
    }
    .bp-picture > img {
      flex: none;
      width: 6rem;
      border-radius: 0.5rem;
      object-fit: contain;
    }
    .bp-picture > div {
      flex: 1 1 14rem;
      min-width: 0;
    }
    /* The filters of the Campaigns list that are switches, side by side. */
    .bp-filter-checks {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 0.5rem 1rem;
      min-height: 2.25rem;
    }
    /* A partner's first visit: what to do, in order, each done or not. */
    .bp-setup {
      display: grid;
      gap: 0;
      margin: 0;
      padding: 0;
      list-style: none;
    }
    .bp-setup li {
      display: flex;
      align-items: flex-start;
      gap: 0.75rem;
      padding: 0.875rem 1.25rem;
    }
    .bp-setup li + li {
      border-top: 1px solid rgb(var(--ap-border));
    }
    .bp-setup-mark {
      display: inline-flex;
      flex: none;
      align-items: center;
      justify-content: center;
      width: 1.5rem;
      height: 1.5rem;
      border: 1px solid rgb(var(--ap-border-strong));
      border-radius: 999px;
      font-size: 0.75rem;
      font-weight: 600;
      color: rgb(var(--ap-ink-3));
    }
    .bp-setup-text {
      min-width: 0;
      font-size: 0.875rem;
      color: rgb(var(--ap-ink-2));
    }
    .bp-setup-text strong {
      display: block;
      font-weight: 600;
      color: rgb(var(--ap-ink));
    }
    .bp-setup li.is-done .bp-setup-text strong {
      color: rgb(var(--ap-ink-3));
    }
    /* A request's history, oldest first: what happened, who did it, when. */
    .bp-history {
      margin: 0;
      padding: 0;
      list-style: none;
    }
    .bp-history li {
      display: flex;
      flex-wrap: wrap;
      align-items: baseline;
      gap: 0.25rem 0.75rem;
      padding: 0.625rem 1.25rem;
      font-size: 0.875rem;
    }
    .bp-history li + li {
      border-top: 1px solid rgb(var(--ap-border));
    }
    .bp-history-what {
      font-weight: 500;
      color: rgb(var(--ap-ink));
    }
    .bp-history-who {
      color: rgb(var(--ap-ink-3));
    }
    .bp-history time {
      margin-inline-start: auto;
      font-size: 0.8125rem;
      font-variant-numeric: tabular-nums;
      color: rgb(var(--ap-ink-3));
    }
    .bp-text-block {
      white-space: pre-line;
      overflow-wrap: anywhere;
    }
</style>
