{{-- The look the public request pages share (the submit page and the booking form): the page's
     accent, cards, fields and their errors, the time list, pills, rows, the bar that stays on
     screen, the emailed-code step and the sent screen. Rules only: include it inside a <style>.
     It reads $accentColor, $contrastColor, $accentOnLight and $accentOnDark from the page. --}}
    #event-submit-app { visibility: hidden; }
    #event-submit-app.loaded { visibility: visible; }

    {{-- Ticks, links and focus rings are painted in --brand-blue. On a schedule's own page that
         is the schedule's accent, in a shade that can be read on the card. --}}
    #gs-page {
      --es-accent: {{ $accentColor }};
      --es-accent-text: {{ $contrastColor }};
      --brand-blue: {{ $accentOnLight }};
      --brand-blue-a10: color-mix(in srgb, {{ $accentOnLight }} 10%, transparent);
    }
    .dark #gs-page {
      --brand-blue: {{ $accentOnDark }};
      --brand-blue-a10: color-mix(in srgb, {{ $accentOnDark }} 14%, transparent);
    }
    .gs-fill {
      background-color: var(--es-accent);
      color: var(--es-accent-text);
    }
    .gs-h {
      margin-bottom: 1rem;
      font-size: 1.125rem;
      font-weight: 600;
      color: rgb(var(--ap-ink));
    }
    .gs-next {
      display: flex;
      align-items: flex-start;
      gap: 0.5rem;
      margin-top: 0.75rem;
      font-size: 0.9375rem;
      color: rgb(var(--ap-ink-2));
    }
    .gs-next svg {
      flex: none;
      width: 1.25rem;
      height: 1.25rem;
      margin-top: 0.0625rem;
      color: var(--brand-blue);
    }
    .gs-terms {
      margin-top: 1rem;
      border: 1px solid rgb(var(--ap-border));
      border-radius: 0.75rem;
      padding: 0.875rem 1rem;
      background: var(--ap-tint-1);
    }
    .gs-terms h3 {
      margin-bottom: 0.25rem;
      font-size: 0.8125rem;
      font-weight: 600;
      color: rgb(var(--ap-ink));
    }
    .gs-terms div {
      font-size: 0.875rem;
      color: rgb(var(--ap-ink-2));
      overflow-wrap: anywhere;
    }
    .gs-quiet {
      flex: none;
      border: 1px solid rgb(var(--ap-border-strong));
      border-radius: 0.5rem;
      padding: 0.375rem 0.75rem;
      font-size: 0.875rem;
      font-weight: 500;
      color: rgb(var(--ap-ink-2));
      transition: all 0.2s;
    }
    .gs-quiet:hover {
      border-color: var(--brand-blue);
      color: var(--brand-blue);
    }
    /* After a submit the header keeps the schedule and drops the pitch for a form that is gone. */
    #gs-page.gs-done [data-gs-before] { display: none; }

    /* The flyer: one place for the image, which also fills in the form where it can. */
    .gs-flyer {
      position: relative;
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 0.875rem 1rem;
      margin-bottom: 1.5rem;
      border: 1.5px dashed rgb(var(--ap-border-strong));
      border-radius: 0.875rem;
      padding: 1rem;
      background: var(--ap-tint-1);
      transition: border-color 0.2s, background-color 0.2s;
    }
    .gs-flyer.has-image {
      border-style: solid;
    }
    .gs-flyer.is-drag {
      border-color: var(--brand-blue);
      background: var(--brand-blue-a10);
    }
    .gs-flyer-art {
      display: flex;
      align-items: center;
      justify-content: center;
      flex: none;
      width: 4.5rem;
      height: 4.5rem;
      border-radius: 0.75rem;
      background: rgb(var(--ap-surface));
      box-shadow: inset 0 0 0 1px rgb(var(--ap-border));
      color: var(--brand-blue);
      overflow: hidden;
    }
    .gs-flyer-art img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .gs-flyer-art svg {
      width: 1.75rem;
      height: 1.75rem;
    }
    .gs-flyer-text {
      flex: 1;
      min-width: 12rem;
    }
    /* On a phone the empty picture frame is the first thing to go: the words need the width. */
    @media (max-width: 479px) {
      .gs-flyer-art.is-empty { display: none; }
    }
    .gs-flyer-title {
      font-size: 0.9375rem;
      font-weight: 600;
      color: rgb(var(--ap-ink));
    }
    .gs-flyer-help {
      font-size: 0.875rem;
      color: rgb(var(--ap-ink-3));
    }
    .gs-flyer-fine {
      margin-top: 0.125rem;
      font-size: 0.75rem;
      color: rgb(var(--ap-ink-3));
    }
    .gs-flyer-actions {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 0.5rem 1rem;
      margin-top: 0.625rem;
    }
    .gs-flyer-paste {
      flex-basis: 100%;
    }
    .gs-btn {
      border: 1px solid rgb(var(--ap-border-strong));
      border-radius: 0.5rem;
      padding: 0.4375rem 0.875rem;
      background: rgb(var(--ap-surface));
      font-size: 0.875rem;
      font-weight: 600;
      color: rgb(var(--ap-ink));
      transition: all 0.2s;
    }
    .gs-btn:hover:not(:disabled) {
      border-color: var(--brand-blue);
      color: var(--brand-blue);
    }
    .gs-btn:disabled {
      opacity: 0.5;
    }
    /* Before there is a flyer, adding one is the thing this card most wants pressed. */
    .gs-btn.is-lead {
      border-color: transparent;
      background: var(--brand-blue-a10);
      color: var(--brand-blue);
    }
    .gs-flyer-help, .gs-hint, .gs-next span {
      text-wrap: pretty;
    }
    .gs-ok .gs-link {
      margin-inline-start: 0.375rem;
      font-size: 0.875rem;
    }
    /* In person or online decides which fields are asked: a control the size of the others. */
    #gs-page .gs-pills .gs-pill span {
      padding: 0.5625rem 1.125rem;
      font-size: 0.9375rem;
    }
    /* A field the flyer just filled in shows itself for a moment. */
    .gs-filled {
      animation: gs-filled 2.4s ease-out;
    }
    @keyframes gs-filled {
      0%, 35% { background-color: var(--brand-blue-a10); box-shadow: 0 0 0 2px var(--brand-blue-a10); }
      100% { box-shadow: 0 0 0 0 transparent; }
    }
    @media (prefers-reduced-motion: reduce) {
      .gs-filled { animation: none; }
    }
    @media (min-width: 640px) {
      .gs-rows .gs-row-title { min-width: 12.5rem; }
    }
    .gs-ok {
      display: inline-flex;
      align-items: center;
      gap: 0.375rem;
      font-size: 0.9375rem;
      font-weight: 600;
      color: #15803d;
    }
    .dark .gs-ok {
      color: #86efac;
    }
    .gs-ok svg {
      width: 1.125rem;
      height: 1.125rem;
    }
    .gs-spin {
      width: 1.125rem;
      height: 1.125rem;
      animation: gs-spin 0.8s linear infinite;
    }
    @keyframes gs-spin { to { transform: rotate(360deg); } }

    /* A field that is wrong says so, under itself. */
    .gs-err {
      margin-top: 0.3125rem;
      font-size: 0.8125rem;
      font-weight: 500;
      color: #dc2626;
    }
    .dark .gs-err {
      color: #f87171;
    }
    .gs-bad,
    .gs-bad.flatpickr-input {
      border-color: #ef4444 !important;
      box-shadow: 0 0 0 1px #ef4444 !important;
    }
    /* The list under a time box: half hours to pick from; any minute can be typed. */
    .gs-times {
      position: absolute;
      z-index: 40;
      inset-inline: 0;
      max-height: 13rem;
      margin-top: 0.25rem;
      overflow-y: auto;
      border: 1px solid rgb(var(--ap-border-strong));
      border-radius: 0.625rem;
      padding: 0.25rem;
      background: rgb(var(--ap-surface));
      box-shadow: 0 10px 28px rgba(0, 0, 0, 0.16);
    }
    .dark .gs-times {
      background: rgb(var(--ap-surface-hover));
    }
    /* A time reads left to right in every language; in a right-to-left page it still sits at the
       start of its box. */
    [dir="rtl"] #gs-page input[role="combobox"] { text-align: right; }
    [dir="rtl"] #gs-page .gs-times { direction: ltr; text-align: right; }
    .gs-times li {
      border-radius: 0.375rem;
      padding: 0.4375rem 0.625rem;
      font-size: 0.9375rem;
      font-variant-numeric: tabular-nums;
      color: rgb(var(--ap-ink));
      cursor: pointer;
    }
    .gs-times li:hover,
    .gs-times li.is-on {
      background: var(--brand-blue-a10);
      color: var(--brand-blue);
    }
    .gs-hint {
      margin-top: 0.3125rem;
      font-size: 0.8125rem;
      color: rgb(var(--ap-ink-3));
    }
    /* Three in a row on a wide screen. On a phone the first takes the row and the other two
       share the next: date over start and end, city over state and postal code. */
    .gs-grid3 {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 1rem;
    }
    .gs-grid3 > :first-child {
      grid-column: 1 / -1;
    }
    @media (min-width: 640px) {
      .gs-grid3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
      .gs-grid3 > :first-child { grid-column: auto; }
      .gs-grid2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    .gs-grid2 {
      display: grid;
      grid-template-columns: minmax(0, 1fr);
      gap: 1rem;
    }
    {{-- Rows that open in place and say what they hold, pills, and quiet links. The rules are
         this page's own, so it needs no other page's stylesheet. --}}
    .gs-rows {
      border-top: 1px solid rgb(var(--ap-border));
    }
    button.gs-row {
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
    button.gs-row:hover .gs-row-title {
      color: var(--brand-blue);
    }
    .gs-row[aria-expanded="true"] {
      border-bottom-color: transparent;
    }
    .gs-row[aria-expanded="true"] .gs-row-chevron {
      transform: rotate(180deg);
    }
    .gs-row-title {
      flex: none;
      min-width: 7rem;
      font-size: 0.875rem;
      font-weight: 600;
      color: rgb(var(--ap-ink));
    }
    .gs-row-summary {
      flex: 1;
      min-width: 0;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      font-size: 0.875rem;
      color: rgb(var(--ap-ink-2));
    }
    .gs-row-summary.is-empty {
      color: rgb(var(--ap-ink-3));
    }
    .gs-row-chevron {
      width: 1.125rem;
      height: 1.125rem;
      flex: none;
      margin-inline-start: auto;
      color: rgb(var(--ap-ink-3));
      transition: transform 0.2s;
    }
    .gs-row-body {
      padding: 0.75rem 0 0.5rem;
      border-bottom: 1px solid rgb(var(--ap-border));
    }
    @media (max-width: 639px) {
      button.gs-row {
        flex-wrap: wrap;
        row-gap: 0;
        padding: 0.625rem 0;
      }
      .gs-row .gs-row-title {
        min-width: 0;
        flex: 1;
      }
      .gs-row .gs-row-summary {
        order: 3;
        flex: none;
        width: 100%;
        white-space: normal;
      }
    }
    /* A row a schedule made required is always open: it reads as a heading, not a button. */
    button.gs-row.is-fixed {
      cursor: default;
    }
    button.gs-row.is-fixed:hover .gs-row-title {
      color: rgb(var(--ap-ink));
    }
    .gs-pills {
      display: inline-flex;
      gap: 0.375rem;
    }
    .gs-pill span {
      display: inline-flex;
      align-items: center;
      gap: 0.375rem;
      border: 1px solid rgb(var(--ap-border-strong));
      border-radius: 9999px;
      font-weight: 500;
      color: rgb(var(--ap-ink-3));
      cursor: pointer;
      transition: all 0.2s;
    }
    .gs-pill input:checked + span {
      border-color: var(--brand-blue);
      background: var(--brand-blue-a10);
      color: var(--brand-blue);
    }
    .gs-pill input:checked + span::before {
      content: "\2713";
    }
    .gs-pill input:focus-visible + span {
      outline: 2px solid var(--brand-blue);
      outline-offset: 2px;
    }
    button.gs-link {
      min-width: 0;
      min-height: 0;
      padding: 0;
      border: 0;
      background: none;
      font-size: 0.8125rem;
      font-weight: 500;
      color: var(--brand-blue);
    }
    button.gs-link:hover {
      text-decoration: underline;
    }
    button.gs-link.is-quiet {
      color: rgb(var(--ap-ink-3));
    }
    .gs-who {
      display: flex;
      flex-wrap: wrap;
      align-items: baseline;
      gap: 0.25rem 0.5rem;
      font-size: 0.9375rem;
      color: rgb(var(--ap-ink-2));
    }
    .gs-who strong {
      color: rgb(var(--ap-ink));
    }

    /* One bar, always on screen: what Submit will do, or what it is still waiting for. */
    .gs-bar {
      position: sticky;
      bottom: 0;
      z-index: 30;
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
      /* On a phone it runs edge to edge and sits on the bottom of the screen, so nothing shows
         in a gap beneath it. */
      margin: 1rem -1.25rem 0;
      border-top: 1px solid rgb(var(--ap-border-strong));
      padding: 0.75rem 1.25rem max(0.75rem, env(safe-area-inset-bottom));
      background: rgb(var(--ap-surface));
      box-shadow: 0 -6px 20px rgba(0, 0, 0, 0.1);
    }
    /* On the dark palettes the card behind is nearly the bar's own shade: lift it one step. */
    .dark .gs-bar {
      background: rgb(var(--ap-surface-hover));
      box-shadow: 0 -6px 24px rgba(0, 0, 0, 0.5);
    }
    .gs-bar-status {
      min-height: 1.25rem;
      font-size: 0.875rem;
      color: rgb(var(--ap-ink-2));
      text-align: center;
    }
    /* What is left, before anything has been refused: said plainly, not as an error. */
    .gs-bar-status.is-todo {
      color: rgb(var(--ap-ink-3));
    }
    .gs-bar-status.is-bad {
      color: #b91c1c;
      font-weight: 500;
    }
    .dark .gs-bar-status.is-bad {
      color: #fca5a5;
    }
    .gs-bar-status:focus { outline: none; }
    .gs-bar-status.is-ready {
      color: #15803d;
      font-weight: 600;
    }
    .dark .gs-bar-status.is-ready {
      color: #86efac;
    }
    .gs-bar-status button {
      font-weight: 600;
      text-decoration: underline;
      text-underline-offset: 2px;
      color: inherit;
    }
    .gs-bar-actions {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }
    .gs-bar-cancel {
      flex: none;
      padding: 0.75rem 0.75rem;
      font-size: 1rem;
      font-weight: 500;
      color: rgb(var(--ap-ink-3));
    }
    .gs-bar-cancel:hover {
      color: rgb(var(--ap-ink));
    }
    .gs-bar-go {
      flex: 1;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      border-radius: 0.625rem;
      padding: 0.75rem 1rem;
      font-size: 1rem;
      font-weight: 600;
      transition: all 0.2s;
    }
    .gs-bar-go:hover:not(:disabled) {
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.18);
    }
    .gs-bar-go:disabled {
      opacity: 0.55;
      cursor: not-allowed;
    }
    @media (min-width: 640px) {
      .gs-bar {
        bottom: 0.5rem;
        flex-direction: row;
        align-items: center;
        justify-content: flex-end;
        gap: 1rem;
        margin: 1rem 0 0;
        border: 1px solid rgb(var(--ap-border-strong));
        border-radius: 1rem;
        padding: 0.75rem;
        padding-inline-start: 1.25rem;
        box-shadow: 0 8px 28px rgba(0, 0, 0, 0.14);
      }
      .gs-bar-status {
        flex: 1;
        min-width: 0;
        text-align: start;
      }
      .gs-bar-actions { flex: none; }
      .gs-bar-go { flex: none; min-width: 11rem; }
    }

    /* The emailed code: its own step, after Submit, with nothing else to look at. The field is
       the sign-up page's own six boxes (partials/code-boxes-styles), driven here by Vue. */
    @include('partials.code-boxes-styles')
    #gs-page #code-boxes {
      max-width: 21rem;
      margin: 1.5rem auto 0;
    }
    /* The real input is 44px wider than the boxes (see the partial): clip, so focusing it cannot
       slide the card sideways. */
    .gs-code-card {
      overflow: clip;
    }
    .gs-step {
      max-width: 28rem;
      margin-inline: auto;
      text-align: center;
    }
    .gs-step h2 {
      font-size: 1.375rem;
      font-weight: 700;
      color: rgb(var(--ap-ink));
    }
    .gs-code-go {
      width: 100%;
      max-width: 20rem;
      margin: 1.25rem auto 0;
    }
    #gs-page.gs-coding [data-gs-before] { display: none; }
    .gs-step .gs-ticket { margin-top: 1.25rem; }
    /* "Didn't receive the code?" takes its own line; the two ways back share the next. */
    .gs-step-links > span:first-child {
      flex-basis: 100%;
    }
    .gs-step-links {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 0.5rem 1.25rem;
      margin-top: 1rem;
      font-size: 0.875rem;
      color: rgb(var(--ap-ink-3));
    }

    /* Sent. */
    .gs-tick {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 3.75rem;
      height: 3.75rem;
      margin: 0 auto 1rem;
      border-radius: 9999px;
      background: #dcfce7;
      color: #16a34a;
    }
    .dark .gs-tick {
      background: rgba(22, 163, 74, 0.2);
      color: #86efac;
    }
    .gs-tick svg {
      width: 2rem;
      height: 2rem;
      stroke-dasharray: 30;
      stroke-dashoffset: 0;
    }
    .gs-ticket {
      display: flex;
      align-items: center;
      gap: 0.875rem;
      margin: 1.25rem auto 0;
      max-width: 30rem;
      border: 1px solid rgb(var(--ap-border));
      border-radius: 0.875rem;
      padding: 0.75rem;
      background: var(--ap-tint-1);
      text-align: start;
    }
    .gs-ticket img {
      flex: none;
      width: 3.5rem;
      height: 3.5rem;
      border-radius: 0.5rem;
      object-fit: cover;
    }
    .gs-ticket-name {
      font-weight: 600;
      color: rgb(var(--ap-ink));
      overflow-wrap: anywhere;
    }
    .gs-ticket-sub {
      font-size: 0.875rem;
      color: rgb(var(--ap-ink-3));
    }
    .gs-steps {
      display: flex;
      justify-content: center;
      gap: 0.5rem;
      margin: 1.25rem auto 0;
      max-width: 30rem;
      font-size: 0.8125rem;
      color: rgb(var(--ap-ink-3));
    }
    .gs-steps li {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 0.375rem;
      text-align: center;
    }
    .gs-steps li::before {
      content: "";
      width: 100%;
      height: 0.25rem;
      border-radius: 9999px;
      background: rgb(var(--ap-border-strong));
    }
    .gs-steps li.is-done::before {
      background: #22c55e;
    }
    /* The step it is on now: named in bold, its bar in the schedule's colour. Not a measure of
       how far along the review is, which nobody here knows. */
    .gs-steps li.is-now {
      color: rgb(var(--ap-ink));
      font-weight: 600;
    }
    .gs-steps li.is-now::before {
      background: var(--brand-blue);
    }
    .gs-after {
      display: flex;
      flex-direction: column;
      align-items: stretch;
      gap: 0.625rem;
      max-width: 30rem;
      margin-inline: auto;
    }
    .gs-after-btn {
      display: block;
      border-radius: 0.625rem;
      padding: 0.75rem 1rem;
      font-size: 1rem;
      font-weight: 600;
      text-align: center;
      transition: all 0.2s;
    }
    .gs-after-main { order: -1; }
    .gs-after-again { order: 3; padding: 0.5rem; }
    @media (min-width: 640px) {
      .gs-after { flex-direction: row; align-items: center; justify-content: center; max-width: none; }
      .gs-after-main { order: 0; }
      .gs-after-again { order: -1; }
    }
    @media (prefers-reduced-motion: no-preference) {
      .gs-tick { animation: gs-pop 0.45s cubic-bezier(0.2, 0.9, 0.3, 1.4) both; }
      .gs-tick svg { animation: gs-draw 0.5s 0.25s ease-out both; }
      .gs-ticket { animation: gs-rise 0.4s 0.35s ease-out both; }
      .gs-steps { animation: gs-rise 0.4s 0.5s ease-out both; }
      .gs-row-body { animation: gs-rise 0.2s ease-out; }
    }
    @keyframes gs-pop { from { transform: scale(0.4); opacity: 0; } }
    @keyframes gs-draw { from { stroke-dashoffset: 30; } }
    @keyframes gs-rise { from { transform: translateY(6px); opacity: 0; } }
