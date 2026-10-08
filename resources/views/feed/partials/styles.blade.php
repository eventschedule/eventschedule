{{-- What the Feeds pages add to the page kit (partials/admin-page-styles). Plain CSS on the
     --ap-* tokens, so there is nothing to build. Included by the Feeds tab and by each page of
     feed/, which are the only places these classes are used. --}}
<style {!! nonce_attr() !!}>
    .feed-addr { unicode-bidi: isolate; font-size: 0.8125rem; color: rgb(var(--ap-ink-3)); overflow-wrap: anywhere; }
    .feed-name a { color: inherit; }
    .feed-name a:hover { color: var(--brand-blue); text-decoration: underline; }
    .feed-num { font-variant-numeric: tabular-nums; font-weight: 600; color: rgb(var(--ap-ink)); }
    .feed-block + .feed-block { margin-top: 1.5rem; border-top: 1px solid rgb(var(--ap-border)); padding-top: 1.5rem; }
    .feed-block h3 { margin: 0; font-size: 0.9375rem; font-weight: 600; color: rgb(var(--ap-ink)); }
    .feed-block > h3 + p { margin: 0.125rem 0 0.75rem; font-size: 0.8125rem; color: rgb(var(--ap-ink-3)); }
    .feed-block > p.is-after { margin: 0.625rem 0 0; font-size: 0.8125rem; color: rgb(var(--ap-ink-3)); }
    .feed-tiles-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    @media (max-width: 639.98px) { .feed-tiles-2, .feed-tiles-3 { grid-template-columns: minmax(0, 1fr) !important; } }
    /* A choice of one, made of the kit's tiles: a radio inside a label, so it works with no
       script and is announced as what it is. The tile wears the chosen look by itself. */
    label.event-tile { display: flex; flex-direction: column; align-items: flex-start; gap: 0.125rem; min-width: 0; min-height: 4rem; padding: 0.75rem 0.875rem; border: 1px solid rgb(var(--ap-border-strong)); border-radius: 0.75rem; background: rgb(var(--ap-surface)); text-align: start; cursor: pointer; transition: all 0.2s; }
    label.event-tile:hover { border-color: var(--brand-blue); }
    label.event-tile:has(input:checked) { border-color: var(--brand-blue); background: var(--brand-blue-a10); box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.08); }
    label.event-tile:has(input:checked) .event-tile-title { color: var(--brand-blue); }
    label.event-tile:has(input:checked) .event-tile-title::before { content: "\2713"; }
    label.event-tile:has(input:focus-visible) { outline: 2px solid var(--brand-blue); outline-offset: 2px; }
    label.event-tile input { position: absolute; opacity: 0; width: 1px; height: 1px; }
    .feed-found { display: flex; align-items: flex-start; gap: 0.75rem; min-width: 0; }
    .feed-found-mark { flex: none; display: grid; place-items: center; width: 2.25rem; height: 2.25rem; border-radius: 0.75rem; background: rgba(34, 197, 94, 0.12); color: #15803d; }
    .dark .feed-found-mark { color: #4ade80; }
    .feed-found-mark.is-quiet { background: var(--ap-tint-2); color: rgb(var(--ap-ink-3)); }
    .feed-found h2 { margin: 0; font-size: 1.0625rem; font-weight: 600; color: rgb(var(--ap-ink)); }
    .feed-found p { margin: 0.125rem 0 0; font-size: 0.875rem; color: rgb(var(--ap-ink-3)); }
    .feed-row-check { display: flex; gap: 0.5rem; align-items: stretch; margin-top: 0.25rem; }
    .feed-row-check input { margin-top: 0 !important; }
    .feed-row-check > :last-child { flex: none; }
    @media (max-width: 639.98px) { .feed-row-check { flex-direction: column; } }
    .feed-works { margin: 1rem 0 0; padding: 0; list-style: none; display: grid; gap: 0.375rem; font-size: 0.8125rem; color: rgb(var(--ap-ink-3)); }
    .feed-works b { font-weight: 600; color: rgb(var(--ap-ink-2)); }
    .feed-sample { margin: 0; padding: 0; list-style: none; }
    .feed-sample li { border-top: 1px solid rgb(var(--ap-border)); padding: 0.625rem 1.25rem; }
    .feed-sample li:first-child { border-top: 0; }
    .feed-sample b { display: block; font-size: 0.875rem; font-weight: 500; color: rgb(var(--ap-ink)); }
    .feed-sample span { font-size: 0.8125rem; color: rgb(var(--ap-ink-3)); }
    .feed-sample-label { margin: 0; border-top: 1px solid rgb(var(--ap-border)); border-bottom: 1px solid rgb(var(--ap-border)); padding: 0.5rem 1.25rem; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; color: rgb(var(--ap-ink-3)); }
    .page-stack > .ap-card, .page-stack > section { min-width: 0; }
    .feed-line { display: flex; flex-wrap: wrap; align-items: center; gap: 0.25rem 0.875rem; margin-top: 0.375rem; font-size: 0.875rem; color: rgb(var(--ap-ink-3)); }
    .feed-line .event-status { font-size: 0.875rem; font-weight: 500; }
    .feed-line form { display: inline; }
    .feed-draft { display: flex; align-items: flex-start; gap: 0.75rem; min-width: 0; }
    .feed-date { flex: none; display: grid; place-items: center; width: 2.5rem; height: 2.5rem; border-radius: 0.625rem; background: var(--ap-tint-2); font-size: 0.625rem; font-weight: 600; letter-spacing: 0.04em; line-height: 1; text-transform: uppercase; color: rgb(var(--ap-ink-3)); }
    .feed-date b { display: block; font-size: 0.9375rem; letter-spacing: 0; color: rgb(var(--ap-ink)); }
    .feed-missing { margin-top: 0.125rem; font-size: 0.8125rem; font-weight: 500; color: #b45309; }
    .dark .feed-missing { color: #fcd34d; }
    .page-table .c-tick { width: 2.75rem; padding-inline-end: 0; }
    .feed-says { font-size: 0.875rem; font-weight: 500; color: rgb(var(--ap-ink)); }
    .feed-confirm td { background: var(--ap-tint-2); }
    .feed-confirm-box { max-width: 44rem; padding: 0.25rem 0; }
    .feed-confirm-box h3 { margin: 0; font-size: 0.9375rem; font-weight: 600; color: rgb(var(--ap-ink)); }
    .feed-confirm-box h3 + p { margin: 0.125rem 0 0.75rem; font-size: 0.8125rem; color: rgb(var(--ap-ink-3)); }
    .feed-confirm-box textarea { margin-top: 0.75rem; }
    .feed-confirm-box .page-form-actions { justify-content: flex-start; margin-top: 0.875rem; }
    .feed-radio { display: flex; gap: 0.625rem; align-items: flex-start; padding: 0.375rem 0; font-size: 0.875rem; color: rgb(var(--ap-ink-2)); }
    .feed-radio input { margin-top: 0.1875rem; }
    .feed-radio small { display: block; font-size: 0.8125rem; color: rgb(var(--ap-ink-3)); }
    @media (max-width: 639.98px) {
        .feed-decide .c-actions, .feed-waiting .c-actions { flex: 1 1 100%; margin-inline-start: 0; text-align: start; gap: 0.5rem 1.5rem; }
        .feed-waiting .c-tick { flex: 0 0 auto; width: auto; }
        .feed-waiting .c-main { flex: 1 1 0; }
        .feed-waiting .c-actions > * + *, .feed-decide .c-actions > * + * { margin-inline-start: 0; }
        .feed-reads .c-main { flex: 0 0 auto; }
        .feed-reads td:nth-child(2) { flex: 1 1 100%; }
        .feed-confirm td { flex: 1 1 100%; }
    }
    @media (max-width: 639.98px) {
        .feed-list td:nth-child(2) { flex: 0 0 auto; align-self: flex-start; }
        .feed-list td:nth-child(3) { flex: 1 1 calc(100% - 7rem); }
        .feed-list td:nth-child(4) { flex: 1 1 calc(100% - 8rem); }
        .feed-list td[data-label]::before { content: attr(data-label) ": "; color: rgb(var(--ap-ink-3)); }
        .feed-list td .event-list-sub { display: block; }
    }
</style>
