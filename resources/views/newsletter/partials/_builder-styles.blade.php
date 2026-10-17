{{-- The newsletter builder's own look.

     The builder is a compiled Vue component (resources/js/components/NewsletterBuilder.vue, built
     by Vite into public/build). It carries the page kit's own classes where the kit has them (the
     tab strip .ap-tabs, the save bar .event-save-bar, .event-link, .page-tool, .event-chip) and
     nb-* classes for everything that is its own, which this sheet styles on the --ap-* tokens, so
     it follows all six palettes and needs no CSS build. ApNewsletterPagesTest fails if a class this
     sheet styles leaves the component.

     Included in the head of the six pages that hold the builder. --}}
<style {!! nonce_attr() !!}>
    /* The editor beside the mail. Below a laptop the mail opens over the editor instead. */
    #newsletter-builder .nb-grid {
      display: block;
    }
    #newsletter-builder .nb-preview-col {
      display: none;
    }
    @media (min-width: 1024px) {
      #newsletter-builder .nb-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 1.5rem;
      }
      #newsletter-builder .nb-preview-col {
        display: block;
      }
      #newsletter-builder .nb-preview-stick {
        position: sticky;
        top: 5rem;
      }
      #newsletter-builder .nb-tab-preview {
        display: none;
      }
    }

    /* Content, Style, Settings: the kit's tab strip, the tab you are on underlined. */
    #newsletter-builder .nb-tabs {
      margin: 0 0 1rem;
    }
    #newsletter-builder .nb-tabs .ap-tab[aria-selected="true"] {
      box-shadow: inset 0 -2px 0 var(--brand-blue);
      color: var(--brand-blue);
    }

    /* A tab's card and its sections. */
    #newsletter-builder .nb-card {
      border-radius: 0.75rem;
    }
    #newsletter-builder .nb-section {
      padding: 1.25rem;
    }
    #newsletter-builder .nb-section + .nb-section {
      border-top: 1px solid rgb(var(--ap-border));
    }
    #newsletter-builder .nb-title {
      display: block;
      margin: 0 0 0.75rem;
      font-size: 0.9375rem;
      font-weight: 600;
      color: rgb(var(--ap-ink));
    }
    #newsletter-builder .nb-label {
      display: block;
      margin-bottom: 0.375rem;
      font-size: 0.875rem;
      font-weight: 500;
      color: rgb(var(--ap-ink-2));
    }
    #newsletter-builder .nb-input,
    #newsletter-builder input.rounded-md,
    #newsletter-builder select.rounded-md,
    #newsletter-builder textarea.rounded-md {
      border-radius: 0.5rem;
    }
    #newsletter-builder .nb-hint {
      margin: 0.5rem 0 0;
      font-size: 0.8125rem;
      color: rgb(var(--ap-ink-3));
    }
    #newsletter-builder .nb-hint.is-lead {
      margin: 0 0 0.75rem;
      font-size: 0.875rem;
      color: rgb(var(--ap-ink-2));
    }
    #newsletter-builder .nb-hint.is-warn {
      color: #b45309;
    }
    .dark #newsletter-builder .nb-hint.is-warn {
      color: #fbbf24;
    }
    #newsletter-builder .nb-icon {
      width: 1.25rem;
      height: 1.25rem;
      flex: none;
    }
    #newsletter-builder .nb-icon.is-sm {
      width: 1rem;
      height: 1rem;
    }

    /* "To: Everyone who follows Harbour Hall (1,234) · Change", under the subject. */
    #newsletter-builder .nb-audience {
      display: flex;
      flex-wrap: wrap;
      align-items: baseline;
      gap: 0.25rem 0.5rem;
      margin: 0.625rem 0 0;
      font-size: 0.875rem;
      color: rgb(var(--ap-ink-2));
    }
    #newsletter-builder .nb-audience-to {
      font-weight: 600;
      color: rgb(var(--ap-ink-3));
    }
    #newsletter-builder .nb-audience-who {
      font-weight: 500;
      color: rgb(var(--ap-ink));
    }
    #newsletter-builder .nb-audience.is-total {
      margin-top: 1rem;
      padding-top: 0.875rem;
      border-top: 1px dashed rgb(var(--ap-border));
    }

    /* The blocks. */
    #newsletter-builder .nb-section-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 0.75rem;
      margin-bottom: 0.75rem;
    }
    #newsletter-builder .nb-section-head .nb-title {
      margin: 0;
    }
    #newsletter-builder .nb-count {
      display: inline-block;
      min-width: 1.25rem;
      margin-inline-start: 0.25rem;
      padding: 0 0.375rem;
      border-radius: 999px;
      background: var(--ap-tint-2);
      font-size: 0.75rem;
      line-height: 1.25rem;
      text-align: center;
      color: rgb(var(--ap-ink-2));
    }
    #newsletter-builder .nb-add {
      gap: 0.375rem;
    }
    #newsletter-builder .nb-palette {
      margin: 0 0 1rem;
      padding: 0.875rem;
      border: 1px solid rgb(var(--ap-border));
      border-radius: 0.75rem;
      background: rgb(var(--ap-bg));
    }
    #newsletter-builder .nb-palette-where {
      margin: 0 0 0.625rem;
      font-size: 0.8125rem;
      color: rgb(var(--ap-ink-3));
    }
    #newsletter-builder .nb-palette-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(8rem, 1fr));
      gap: 0.5rem;
    }
    #newsletter-builder .nb-palette-item {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem 0.625rem;
      border: 1px solid rgb(var(--ap-border));
      border-radius: 0.625rem;
      background: rgb(var(--ap-surface));
      font-size: 0.8125rem;
      font-weight: 500;
      color: rgb(var(--ap-ink));
      cursor: pointer;
      user-select: none;
      transition: border-color 0.2s, background 0.2s;
    }
    #newsletter-builder .nb-palette-item:hover {
      border-color: var(--brand-blue);
      background: var(--ap-tint-1);
    }
    #newsletter-builder .nb-palette-item:focus-visible,
    #newsletter-builder .nb-block-toggle:focus-visible,
    #newsletter-builder .nb-tool:focus-visible,
    #newsletter-builder .nb-seg-btn:focus-visible,
    #newsletter-builder .nb-design:has(input:focus-visible) {
      outline: 2px solid var(--brand-blue);
      outline-offset: 1px;
    }
    #newsletter-builder .nb-palette-item .nb-icon {
      color: var(--brand-blue);
    }
    #newsletter-builder .nb-canvas {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
      min-height: 4rem;
    }
    #newsletter-builder .nb-block {
      border: 1px solid rgb(var(--ap-border));
      border-radius: 0.625rem;
      background: rgb(var(--ap-surface));
      transition: border-color 0.2s, box-shadow 0.2s;
    }
    #newsletter-builder .nb-block:hover {
      border-color: rgb(var(--ap-border-strong));
    }
    #newsletter-builder .nb-block.is-open {
      border-color: var(--brand-blue);
      box-shadow: 0 0 0 3px rgba(78, 129, 250, 0.15);
    }
    #newsletter-builder .nb-block-head {
      display: flex;
      align-items: center;
      gap: 0.125rem;
      padding: 0.25rem 0.375rem 0.25rem 0.25rem;
    }
    #newsletter-builder .nb-handle {
      display: inline-flex;
      padding: 0.375rem;
      color: rgb(var(--ap-ink-4));
      cursor: grab;
    }
    #newsletter-builder .nb-block-toggle {
      display: flex;
      flex: 1;
      min-width: 0;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem 0.25rem;
      border: 0;
      border-radius: 0.375rem;
      background: none;
      text-align: start;
      color: rgb(var(--ap-ink));
      cursor: pointer;
    }
    #newsletter-builder .nb-block-toggle .nb-icon {
      color: rgb(var(--ap-ink-3));
    }
    #newsletter-builder .nb-block.is-open .nb-block-toggle .nb-icon {
      color: var(--brand-blue);
    }
    #newsletter-builder .nb-block-name {
      flex: none;
      font-size: 0.875rem;
      font-weight: 600;
    }
    #newsletter-builder .nb-block-snippet {
      min-width: 0;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      font-size: 0.875rem;
      color: rgb(var(--ap-ink-3));
    }
    #newsletter-builder .nb-block-snippet.is-empty {
      font-style: italic;
      color: rgb(var(--ap-ink-4));
    }
    #newsletter-builder .nb-block-tools {
      display: flex;
      flex: none;
      gap: 0.125rem;
      transition: opacity 0.2s;
    }
    /* With a pointer, a closed block keeps its line quiet until it is pointed at. */
    @media (hover: hover) and (min-width: 640px) {
      #newsletter-builder .nb-block:not(.is-open):not(:hover):not(:focus-within) .nb-block-tools {
        opacity: 0;
      }
    }
    #newsletter-builder .nb-tool {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 2rem;
      height: 2rem;
      border: 0;
      border-radius: 0.5rem;
      background: none;
      color: rgb(var(--ap-ink-3));
      transition: background 0.2s, color 0.2s;
    }
    #newsletter-builder .nb-tool:hover:not(:disabled) {
      background: rgb(var(--ap-surface-hover));
      color: rgb(var(--ap-ink));
    }
    #newsletter-builder .nb-tool.is-danger:hover:not(:disabled) {
      color: #dc2626;
    }
    #newsletter-builder .nb-tool:disabled {
      opacity: 0.35;
      cursor: default;
    }
    #newsletter-builder .nb-block-body {
      padding: 0.875rem 0.875rem 1rem;
      border-top: 1px solid rgb(var(--ap-border));
    }
    #newsletter-builder .nb-empty {
      padding: 2.5rem 1rem;
      border: 1px dashed rgb(var(--ap-border-strong));
      border-radius: 0.75rem;
      font-size: 0.875rem;
      text-align: center;
      color: rgb(var(--ap-ink-3));
    }

    /* The five designs. */
    #newsletter-builder .nb-designs {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(6.5rem, 1fr));
      gap: 0.75rem;
    }
    #newsletter-builder .nb-design {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
      padding: 0.5rem;
      border: 1px solid rgb(var(--ap-border));
      border-radius: 0.75rem;
      background: rgb(var(--ap-surface));
      cursor: pointer;
      transition: border-color 0.2s, box-shadow 0.2s;
    }
    #newsletter-builder .nb-design:hover {
      border-color: var(--brand-blue);
    }
    #newsletter-builder .nb-design.is-on {
      border-color: var(--brand-blue);
      box-shadow: 0 0 0 1px var(--brand-blue);
      background: var(--ap-tint-1);
    }
    #newsletter-builder .nb-design-thumb {
      display: block;
      aspect-ratio: 3 / 2;
      overflow: hidden;
      border: 1px solid rgb(var(--ap-border));
      border-radius: 0.375rem;
    }
    #newsletter-builder .nb-design-name {
      font-size: 0.8125rem;
      font-weight: 500;
      text-align: center;
      color: rgb(var(--ap-ink));
    }
    #newsletter-builder .nb-note {
      display: flex;
      flex-wrap: wrap;
      align-items: baseline;
      gap: 0.375rem;
      margin: 0.75rem 0 0;
      font-size: 0.875rem;
      color: rgb(var(--ap-ink-2));
    }

    /* Choices: everyone or chosen segments, now or later, which address for a test. */
    #newsletter-builder .nb-choices {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }
    #newsletter-builder .nb-choice {
      display: flex;
      align-items: center;
      gap: 0.625rem;
      font-size: 0.875rem;
      color: rgb(var(--ap-ink));
      cursor: pointer;
    }
    #newsletter-builder .nb-choice input,
    #newsletter-builder .nb-segment input {
      color: var(--brand-blue);
    }
    #newsletter-builder .nb-choice-body {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
      margin-top: 0.625rem;
      margin-inline-start: 1.625rem;
    }
    #newsletter-builder .nb-segment {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.625rem 0.75rem;
      border: 1px solid rgb(var(--ap-border));
      border-radius: 0.625rem;
      cursor: pointer;
      transition: border-color 0.2s, background 0.2s;
    }
    #newsletter-builder .nb-segment:hover {
      border-color: var(--brand-blue);
    }
    #newsletter-builder .nb-segment.is-on {
      border-color: var(--brand-blue);
      background: var(--ap-tint-1);
    }
    #newsletter-builder .nb-segment input {
      border-radius: 0.25rem;
    }
    #newsletter-builder .nb-segment-name {
      flex: 1;
      min-width: 0;
      font-size: 0.875rem;
      font-weight: 500;
      color: rgb(var(--ap-ink));
    }
    #newsletter-builder .nb-segment-type {
      margin-inline-start: 0.375rem;
      font-size: 0.75rem;
      font-weight: 400;
      color: rgb(var(--ap-ink-3));
    }
    #newsletter-builder .nb-segment-count {
      font-size: 0.875rem;
      font-variant-numeric: tabular-nums;
      color: rgb(var(--ap-ink-3));
    }
    #newsletter-builder .nb-manage {
      display: inline-block;
      margin-top: 0.75rem;
      font-size: 0.875rem;
    }

    /* Pairs: the A/B test's settings, and what Review and send is about to do. */
    #newsletter-builder .nb-kv {
      display: grid;
      gap: 0.5rem;
      margin: 0;
    }
    #newsletter-builder .nb-kv > div {
      display: grid;
      grid-template-columns: minmax(6rem, 9rem) minmax(0, 1fr);
      gap: 0.75rem;
      font-size: 0.875rem;
    }
    #newsletter-builder .nb-kv dt {
      color: rgb(var(--ap-ink-3));
    }
    #newsletter-builder .nb-kv dd {
      margin: 0;
      overflow-wrap: anywhere;
      color: rgb(var(--ap-ink));
    }
    #newsletter-builder .nb-num,
    #newsletter-builder .nb-quiet {
      color: rgb(var(--ap-ink-3));
    }
    #newsletter-builder .nb-quiet {
      margin-inline-start: 0.25rem;
    }
    #newsletter-builder .nb-ab-form {
      display: flex;
      flex-direction: column;
      gap: 0.875rem;
      padding: 1rem;
      border: 1px solid rgb(var(--ap-border));
      border-radius: 0.75rem;
    }
    #newsletter-builder .nb-field-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 0.75rem;
    }
    #newsletter-builder .nb-section .nb-kv + .nb-hint,
    #newsletter-builder .nb-section .nb-kv ~ .event-link {
      display: block;
      margin-top: 0.75rem;
    }

    /* The mail. */
    #newsletter-builder .nb-preview {
      overflow: hidden;
      border-radius: 0.75rem;
    }
    #newsletter-builder .nb-preview-head {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.75rem 1rem;
      border-bottom: 1px solid rgb(var(--ap-border));
    }
    #newsletter-builder .nb-preview-head .nb-title {
      flex: 1;
      margin: 0;
    }
    #newsletter-builder .nb-updating {
      font-size: 0.75rem;
      color: rgb(var(--ap-ink-3));
    }
    /* The house segmented control: a sunken group, the chosen side pressed in. */
    #newsletter-builder .nb-seg {
      display: inline-flex;
      padding: 0.125rem;
      border: 1px solid rgb(var(--ap-border));
      border-radius: 0.75rem;
      background: rgb(var(--ap-surface-sunken));
    }
    #newsletter-builder .nb-seg-btn {
      padding: 0.375rem 0.75rem;
      border: 0;
      border-radius: 0.5rem;
      background: none;
      font-size: 0.875rem;
      font-weight: 500;
      color: rgb(var(--ap-ink-3));
      transition: all 0.2s;
    }
    #newsletter-builder .nb-seg-btn:hover {
      color: rgb(var(--ap-ink));
    }
    #newsletter-builder .nb-seg-btn[aria-pressed="true"] {
      background: rgb(var(--ap-surface));
      box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.08);
      color: rgb(var(--ap-ink));
    }
    #newsletter-builder .nb-pane {
      position: relative;
      overflow: hidden;
      min-height: 400px;
    }
    #newsletter-builder .nb-pane.is-phone {
      background: rgb(var(--ap-bg));
    }
    #newsletter-builder .nb-pane-loading,
    #newsletter-builder .nb-dialog-loading {
      display: flex;
      align-items: center;
      justify-content: center;
    }
    #newsletter-builder .nb-pane-loading {
      position: absolute;
      inset: 0;
      z-index: 1;
      background: rgb(var(--ap-surface));
    }
    #newsletter-builder .nb-dialog-loading {
      padding: 2rem 0;
    }
    #newsletter-builder .nb-spinner {
      width: 1.5rem;
      height: 1.5rem;
      color: var(--brand-blue);
    }
    #newsletter-builder .nb-frame {
      display: block;
      border: 0;
      background: #fff;
    }
    /* A phone: the mail over the whole screen, with a way out. */
    #newsletter-builder .nb-preview-col.is-sheet {
      display: block;
      position: fixed;
      inset: 0;
      z-index: 70;
      padding: 0.5rem;
      background: rgb(var(--ap-bg));
    }
    #newsletter-builder .nb-preview-col.is-sheet .nb-preview-stick {
      position: static;
    }
    html.nb-sheet-open {
      overflow: hidden;
    }

    /* The save bar is the kit's (form-kit-styles); what is the builder's own in it. */
    #newsletter-builder .nb-bar-error {
      color: #b91c1c;
    }
    .dark #newsletter-builder .nb-bar-error {
      color: #f87171;
    }

    /* Dialogs: Review and send, Send a test, Save as template. */
    #newsletter-builder .nb-dialog-backdrop {
      position: fixed;
      inset: 0;
      z-index: 80;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1rem;
      background: rgba(0, 0, 0, 0.5);
    }
    #newsletter-builder .nb-dialog {
      width: 100%;
      max-width: 32rem;
      max-height: calc(100dvh - 2rem);
      overflow: auto;
      padding: 1.5rem;
      border-radius: 0.875rem;
      background: rgb(var(--ap-surface));
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
      color: rgb(var(--ap-ink));
    }
    #newsletter-builder .nb-dialog-title {
      margin: 0 0 1rem;
      font-size: 1.125rem;
      font-weight: 600;
      color: rgb(var(--ap-ink));
    }
    #newsletter-builder .nb-dialog .nb-choices {
      margin-top: 1.25rem;
    }
    #newsletter-builder .nb-lead {
      margin: 1rem 0 0;
      font-size: 0.875rem;
      color: rgb(var(--ap-ink-2));
    }
    #newsletter-builder .nb-dialog .nb-dialog-title + .nb-lead {
      margin-top: 0;
    }
    #newsletter-builder .nb-test-first {
      margin: 1.25rem 0 0;
      font-size: 0.875rem;
    }
    #newsletter-builder .nb-actions {
      display: flex;
      flex-wrap: wrap;
      justify-content: flex-end;
      gap: 0.75rem;
      margin-top: 1.25rem;
    }
    #newsletter-builder .nb-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 0.75rem 1rem;
      border: 1px solid rgb(var(--ap-border-strong));
      border-radius: 0.5rem;
      background: rgb(var(--ap-surface));
      font-size: 1rem;
      font-weight: 600;
      color: rgb(var(--ap-ink));
      transition: background 0.2s;
    }
    #newsletter-builder .nb-btn:hover {
      background: rgb(var(--ap-surface-hover));
    }
    /* A warning is a bordered amber panel; what went well, a green one. */
    #newsletter-builder .nb-warn,
    #newsletter-builder .nb-ok {
      display: flex;
      gap: 0.625rem;
      margin-top: 1rem;
      padding: 0.75rem;
      border: 1px solid #fcd34d;
      border-radius: 0.5rem;
      background: #fffbeb;
      font-size: 0.875rem;
      color: #92400e;
    }
    #newsletter-builder .nb-ok {
      border-color: #86efac;
      background: #f0fdf4;
      color: #166534;
    }
    .dark #newsletter-builder .nb-warn {
      border-color: #b45309;
      background: rgba(120, 53, 15, 0.2);
      color: #fde68a;
    }
    .dark #newsletter-builder .nb-ok {
      border-color: #15803d;
      background: rgba(20, 83, 45, 0.25);
      color: #bbf7d0;
    }
    #newsletter-builder .nb-warn p,
    #newsletter-builder .nb-ok p {
      margin: 0;
    }
    #newsletter-builder .nb-warn-icon {
      width: 1.25rem;
      height: 1.25rem;
      flex: none;
      color: #d97706;
    }
    .dark #newsletter-builder .nb-warn-icon {
      color: #fbbf24;
    }
    #newsletter-builder .nb-warn-links {
      display: flex;
      flex-wrap: wrap;
      gap: 0.375rem 1rem;
      margin-top: 0.375rem !important;
    }
    #newsletter-builder .nb-warn.is-flush {
      margin: 0;
      border-width: 0 0 1px;
      border-radius: 0;
    }

    /* The template's name, above the builder, on the page that edits a template. */
    .news-template-name-field {
      max-width: 32rem;
      margin-bottom: 1rem;
    }
</style>
