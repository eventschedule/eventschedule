{{-- What the newsletters section needs beyond the portal's page kit (partials/admin-page-styles):
     the schedule picker in the title row, the allowance meter, a template's card and its
     swatch. Included in the head of every page of the section, the schedule owner's and the
     platform admin's. Plain CSS on the portal's tokens, like the kit. --}}
<style {!! nonce_attr() !!}>
    /* A notice keeps its distance on a page that is not a stack; in a stack the gap is the stack's. */
    .news-notice {
      margin-bottom: 1rem;
    }
    .page-stack > .news-notice {
      margin-bottom: 0;
    }
    .news-notice-list {
      margin: 0;
      padding-inline-start: 1.25rem;
      list-style: disc;
    }

    /* The schedule these newsletters belong to, beside the button that writes one. */
    .news-picker {
      width: 15rem;
      max-width: 100%;
    }
    /* The layout pads every field on all sides with !important, which takes back the room the
       picker's arrow needs: a long name ran under it. */
    .news-picker input {
      padding-inline-end: 2.5rem !important;
      text-overflow: ellipsis;
    }
    @media (max-width: 639.98px) {
      .news-picker,
      .news-picker + a {
        flex: 1 1 100%;
        width: auto;
      }
    }

    /* The month's allowance: a figure, a thin bar and what is left, at the end of the line that
       says what the page is for. It was a bar across the whole page. */
    .news-meter {
      flex: 0 1 24rem;
      min-width: min(100%, 16rem);
    }
    .news-meter .text-sm {
      margin-bottom: 0.375rem;
      font-size: 0.8125rem;
      line-height: 1.25rem;
    }

    /* A card's last quiet line keeps off the form above it (the kit spaces it only in a flush card). */
    .page-card:not(.is-flush) > .page-card-foot {
      margin-top: 0.75rem;
    }

    /* The platform admin's pages open under the admin navigation, not under a title row. */
    .news-admin {
      margin-top: 1.25rem;
    }

    /* A section inside a tab that is more than one thing (the list, then the form that adds to it). */
    .news-block + .news-block {
      margin-top: 1rem;
    }

    /* Templates, as cards. */
    .news-templates {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(min(100%, 14rem), 1fr));
      gap: 1rem;
    }
    .news-template {
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }
    .news-template-face {
      display: block;
      border-bottom: 1px solid rgb(var(--ap-border));
      padding: 1rem;
      background: var(--ap-tint-1);
    }
    .news-swatch {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 0.375rem;
      height: 6.5rem;
      max-width: 9rem;
      margin: 0 auto;
      border-radius: 0.375rem;
      box-shadow: 0 1px 2px rgba(0, 0, 0, 0.12), 0 0 0 1px rgba(0, 0, 0, 0.06);
    }
    .dark .news-swatch {
      box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.1);
    }
    .news-swatch i {
      display: block;
    }
    .news-swatch-bar {
      width: 55%;
      height: 0.5rem;
      border-radius: 0.125rem;
    }
    .news-swatch-line {
      width: 65%;
      height: 0.1875rem;
      border-radius: 1px;
      opacity: 0.45;
    }
    .news-swatch-line.is-short {
      width: 45%;
    }
    .news-swatch-button {
      width: 35%;
      height: 0.625rem;
      margin-top: 0.125rem;
      border-radius: 999px;
    }
    .news-swatch-button.is-square {
      border-radius: 0.125rem;
    }
    .news-template-text {
      min-width: 0;
      padding: 0.875rem 1rem 0.75rem;
    }
    .news-template-name {
      margin: 0;
      font-size: 0.9375rem;
      font-weight: 600;
      color: rgb(var(--ap-ink));
      overflow-wrap: anywhere;
    }
    .news-template-meta {
      margin: 0.125rem 0 0;
      font-size: 0.8125rem;
      color: rgb(var(--ap-ink-3));
    }
    .news-template-foot {
      display: flex;
      align-items: center;
      gap: 0.875rem;
      margin-top: auto;
      border-top: 1px solid rgb(var(--ap-border));
      padding: 0.625rem 1rem;
    }
    .news-template-foot form {
      display: inline-flex;
      margin-inline-start: auto;
    }

    /* The same swatch, smaller, where a newsletter is started from a template. */
    .news-picks {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(min(100%, 9rem), 1fr));
      gap: 0.75rem;
    }
    .news-pick {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
      border: 1px solid rgb(var(--ap-border));
      border-radius: 0.625rem;
      padding: 0.625rem;
      transition: border-color 0.2s, background-color 0.2s;
    }
    .news-pick:hover {
      border-color: var(--brand-blue);
      background: var(--ap-tint-1);
    }
    .news-pick:focus-visible {
      outline: 2px solid var(--brand-blue);
      outline-offset: 2px;
    }
    .news-pick .news-swatch {
      width: 100%;
      max-width: none;
      height: 4.5rem;
    }
    .news-pick-name {
      font-size: 0.875rem;
      font-weight: 500;
      color: rgb(var(--ap-ink));
      overflow-wrap: anywhere;
    }
    .news-pick-meta {
      font-size: 0.75rem;
      text-transform: capitalize;
      color: rgb(var(--ap-ink-3));
    }

    /* A form whose fields sit side by side from a tablet up (a name and an address, two dates). */
    .news-inline-form {
      display: flex;
      flex-wrap: wrap;
      align-items: flex-end;
      gap: 0.75rem;
    }
    .news-inline-form > .is-grow {
      flex: 1 1 14rem;
      min-width: 0;
    }
    @media (max-width: 639.98px) {
      .news-inline-form > button {
        flex: 1 1 100%;
      }
    }

    /* A row put away with the hidden attribute stays away on a phone, where the kit makes every
       row a flex box and so outranks the attribute. */
    .page-table tr[hidden] {
      display: none;
    }

    /* A row of the list being changed in place: its two fields and its two buttons. */
    .page-table .news-edit-row td {
      background: var(--ap-tint-1);
    }
    @media (max-width: 639.98px) {
      .page-table .news-edit-row td {
        flex: 1 1 100%;
      }
    }

    /* What a subscriber list resolves to, under a segment's name field. */
    .news-facts {
      display: flex;
      flex-wrap: wrap;
      gap: 0.25rem 2rem;
      margin: 0;
      font-size: 0.875rem;
      color: rgb(var(--ap-ink-3));
    }
    .news-facts dt {
      display: inline;
    }
    .news-facts dt::after {
      content: ":";
    }
    .news-facts dd {
      display: inline;
      margin: 0;
      font-weight: 500;
      color: rgb(var(--ap-ink));
    }

    /* A chart card keeps its canvas from pushing the page wide on a phone. */
    .news-chart {
      position: relative;
      min-width: 0;
    }
    .news-chart canvas {
      max-width: 100%;
    }

    /* The two halves of an A/B test, side by side. The winner is said in words and a tint with a
       full border, never by a border on one side. */
    .news-variant {
      border: 1px solid rgb(var(--ap-border));
      border-radius: 0.625rem;
      padding: 1rem;
    }
    .news-variant.is-winner {
      border-color: rgba(34, 197, 94, 0.55);
      background: rgba(34, 197, 94, 0.08);
    }
    .news-variant-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 0.75rem;
      margin-bottom: 0.25rem;
      font-weight: 600;
      color: rgb(var(--ap-ink));
    }
    .news-variant p {
      margin: 0 0 0.75rem;
      font-size: 0.875rem;
      color: rgb(var(--ap-ink-3));
      overflow-wrap: anywhere;
    }

    /* A long address in the list of links that were clicked breaks rather than widening the card. */
    .page-kv.news-links dt {
      overflow-wrap: anywhere;
    }
</style>
