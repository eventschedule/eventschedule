{{-- The builder in the portal's voice.

     The builder is a compiled Vue component (resources/js/components/NewsletterBuilder.vue, built
     by Vite into public/build), so its markup cannot be given the kit's classes from a view. This
     sheet dresses what the component renders instead, from outside: its inner tabs as the kit's
     tab strip, its cards and fields at the kit's radius, its buttons in the one voice (brand blue
     for the button that goes on, bordered for the rest, Save last). Every rule is scoped to
     #newsletter-builder and keyed on a class the component already carries, and none of it touches
     what the builder does. When the component is next edited, give it the kit's classes and drop
     the matching rule here; ApNewsletterPagesTest fails if a class this sheet leans on leaves it.

     Included in the head of the six pages that hold the builder. --}}
<style {!! nonce_attr() !!}>
    /* The editor and its preview keep the portal's 1rem between panels. */
    #newsletter-builder > div {
      gap: 1rem;
    }

    /* Content, Style, Settings: the kit's tab strip (.ap-tabs). The line under the tab you are
       on is the component's own; the weight, the spacing and the quiet colour are the kit's. */
    #newsletter-builder > div > div:first-child > .flex.border-b {
      gap: 1.5rem;
      margin-bottom: 1rem;
      border-color: rgb(var(--ap-border));
    }
    #newsletter-builder > div > div:first-child > .flex.border-b > button {
      padding: 0.75rem 0.125rem;
      font-size: 0.9375rem;
      font-weight: 500;
      white-space: nowrap;
      transition: color 0.2s, border-color 0.2s;
    }
    #newsletter-builder > div > div:first-child > .flex.border-b > button.border-transparent {
      color: rgb(var(--ap-ink-3));
    }
    #newsletter-builder > div > div:first-child > .flex.border-b > button.border-transparent:hover {
      border-color: rgb(var(--ap-border-strong));
      color: rgb(var(--ap-ink));
    }
    #newsletter-builder > div > div:first-child > .flex.border-b > button:focus-visible {
      outline: 2px solid var(--brand-blue);
      outline-offset: -2px;
      border-radius: 0.25rem;
    }

    /* Cards and what is in them, at the kit's corners. */
    #newsletter-builder .ap-card {
      border-radius: 0.75rem;
    }
    #newsletter-builder .ap-card h3 {
      font-size: 0.9375rem;
      color: rgb(var(--ap-ink));
    }
    #newsletter-builder input.rounded-md,
    #newsletter-builder select.rounded-md,
    #newsletter-builder textarea.rounded-md {
      border-radius: 0.5rem;
    }
    #newsletter-builder .palette-item,
    #newsletter-builder .block-item {
      border-radius: 0.625rem;
    }
    #newsletter-builder .palette-item:hover {
      background: var(--ap-tint-1);
    }

    /* The button that goes on is the brand button: Add block, Save, and a dialog's own. */
    #newsletter-builder button[class*="bg-[var(--brand-button-bg)]"],
    #newsletter-builder button.bg-yellow-500[type="submit"] {
      border-radius: 0.5rem;
      background-color: var(--brand-button-bg);
      background-image: linear-gradient(to bottom, var(--brand-button-bg-light), var(--brand-button-bg));
      box-shadow: var(--ap-shadow-btn);
      font-weight: 600;
      color: #fff;
      transition: all 0.2s;
    }
    #newsletter-builder button[class*="bg-[var(--brand-button-bg)]"]:hover,
    #newsletter-builder button.bg-yellow-500[type="submit"]:hover {
      background-image: linear-gradient(to bottom, var(--brand-button-bg), var(--brand-button-bg-hover));
    }

    /* Everything else is bordered: Send a test, Save as template, Cancel, and the two that used
       to be yellow and green, Schedule and Send now. */
    #newsletter-builder button.border-gray-300,
    #newsletter-builder button.bg-yellow-500[type="button"],
    #newsletter-builder button.bg-green-600 {
      border: 1px solid rgb(var(--ap-border-strong));
      border-radius: 0.5rem;
      background: rgb(var(--ap-surface));
      box-shadow: var(--ap-shadow-btn);
      font-weight: 600;
      color: rgb(var(--ap-ink));
      transition: all 0.2s;
    }
    #newsletter-builder button.border-gray-300:hover,
    #newsletter-builder button.bg-yellow-500[type="button"]:hover,
    #newsletter-builder button.bg-green-600:hover {
      background: rgb(var(--ap-surface-hover));
    }
    #newsletter-builder button:focus-visible {
      outline: 2px solid var(--brand-blue);
      outline-offset: 2px;
    }

    /* The row under the editor: what else can be done at the start, then Schedule, Send now and,
       last, Save, at the size of the portal's other page-level buttons. */
    #newsletter-builder > div > div:first-child > .mt-4.flex {
      margin-top: 1rem;
    }
    #newsletter-builder > div > div:first-child > .mt-4.flex button {
      padding: 0.75rem 1rem;
      font-size: 1rem;
      line-height: 1.5rem;
    }
    #newsletter-builder > div > div:first-child > .mt-4.flex button[type="submit"] {
      order: 1;
    }
    /* Where the row breaks in two, the buttons that go on keep to the end. */
    #newsletter-builder > div > div:first-child > .mt-4.flex > div:last-child {
      margin-inline-start: auto;
    }
    @media (max-width: 639.98px) {
      #newsletter-builder > div > div:first-child > .mt-4.flex > div {
        flex: 1 1 100%;
      }
      #newsletter-builder > div > div:first-child > .mt-4.flex button {
        flex: 1 1 auto;
        justify-content: center;
      }
    }

    /* A dialog of the builder (send a test, schedule, save as template). */
    #newsletter-builder .fixed.inset-0 > div {
      border-radius: 0.75rem;
      background: rgb(var(--ap-surface));
    }

    /* The A/B panel the Settings tab prints (partials/_ab-test-panel). */
    #newsletter-builder .news-ab-form {
      margin-top: 1rem;
      border: 1px solid rgb(var(--ap-border));
      border-radius: 0.625rem;
      padding: 1rem;
    }

    /* The template's name, above the builder, on the page that edits a template. */
    .news-template-name-field {
      max-width: 32rem;
      margin-bottom: 1rem;
    }
</style>
