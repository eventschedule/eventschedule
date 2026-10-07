{{-- The guest kit: the pieces every guest page is built from, as the form kit is for the admin
     forms (partials/form-kit-styles). Plain CSS on tokens, so no CSS build.

     Rules for anything added here:
       - one class per rule and no !important, so an owner's custom CSS (printed after this,
         like everything in this slot it follows the app's own stylesheet) wins a tie. The ids owners are promised (#gp-...) are not styled here at all.
       - sizes in rem, so the accessibility widget's text-size steps scale them.
       - colours from the tokens below. The schedule's own colours are the --es-accent family,
         printed by partials/guest-theme; the surfaces and inks are the --gk- family, which
         follow the page's light or dark mode.
       - logical properties (inline-start, not left), so a right-to-left schedule mirrors.
       - one set of timings (--gk-press, --gk-swap, --gk-open). Under reduced motion a state
         still changes, at once, and nothing moves.

     The classes are gk-, not es-: es-btn, es-panel and es-chip are the EMAIL components' names.
     A page's own pieces (the ticket, the event facts, the list row) are added below the shared
     ones as each page moves onto the kit. --}}
<style {!! nonce_attr() !!}>
    body {
        --gk-panel: rgb(255 255 255 / .95);
        --gk-solid: #ffffff;
        --gk-well: rgb(var(--ap-gray-100));
        --gk-ink: rgb(var(--ap-gray-900));
        --gk-ink-2: rgb(var(--ap-gray-700));
        --gk-ink-3: #5f6875;
        --gk-line: rgb(17 24 39 / .12);
        --gk-ok: #166534; --gk-ok-bg: #f0fdf4; --gk-ok-line: #86efac;
        --gk-warn: #92400e; --gk-warn-bg: #fffbeb; --gk-warn-line: #fcd34d;
        --gk-bad: #b91c1c; --gk-bad-bg: #fef2f2; --gk-bad-line: #fca5a5;
        --gk-shadow: 0 1px 2px rgb(15 23 42 / .07), 0 1px 1px rgb(15 23 42 / .04);
        --gk-shadow-lift: 0 4px 12px rgb(15 23 42 / .16);
        --gk-radius: 1rem;
        --gk-radius-ctl: .625rem;
        --gk-press: 50ms;
        --gk-swap: 120ms;
        --gk-open: 220ms;
    }
    :where(.dark) body {
        --gk-panel: rgb(30 30 30 / .95);
        --gk-solid: rgb(var(--ap-gray-900));
        --gk-well: rgb(var(--ap-gray-700));
        --gk-ink: rgb(var(--ap-gray-100));
        --gk-ink-2: rgb(var(--ap-gray-300));
        --gk-ink-3: rgb(var(--ap-gray-400));
        --gk-line: rgb(255 255 255 / .14);
        --gk-ok: #86efac; --gk-ok-bg: rgb(22 101 52 / .3); --gk-ok-line: rgb(134 239 172 / .4);
        --gk-warn: #fcd34d; --gk-warn-bg: rgb(146 64 14 / .3); --gk-warn-line: rgb(252 211 77 / .4);
        --gk-bad: #fca5a5; --gk-bad-bg: rgb(185 28 28 / .28); --gk-bad-line: rgb(252 165 165 / .4);
        --gk-shadow: 0 1px 2px rgb(0 0 0 / .4);
        --gk-shadow-lift: 0 4px 14px rgb(0 0 0 / .5);
    }

    {{-- A panel: the translucent card every section of a guest page sits in. --}}
    .gk-panel { background: var(--gk-panel); color: var(--gk-ink); border-radius: var(--gk-radius); box-shadow: var(--gk-shadow); -webkit-backdrop-filter: blur(6px); backdrop-filter: blur(6px); }
    .gk-pad { padding: 1.125rem 1rem; }
    @media (min-width: 48rem) { .gk-pad { padding: 1.5rem 1.75rem; } }

    .gk-h { margin: 0 0 .75rem; font-size: 1.0625rem; font-weight: 700; line-height: 1.25; color: var(--gk-ink); }
    .gk-h-lg { font-size: 1.375rem; line-height: 1.2; }
    .gk-hint { margin-top: .5rem; font-size: .84375rem; color: var(--gk-ink-3); }
    .gk-err { margin-top: .375rem; font-size: .84375rem; font-weight: 600; color: var(--gk-bad); }

    {{-- A link in running text. Ink with an underline, never the accent: an accent is not guaranteed to read as text, and --es-accent-readable is for the few places that want it. --}}
    .gk-link { color: var(--gk-ink); font-weight: 600; text-decoration: underline; text-decoration-thickness: 1.5px; text-underline-offset: 3px; text-decoration-color: color-mix(in srgb, currentColor 40%, transparent); transition: text-decoration-color var(--gk-swap); }
    .gk-link:hover { text-decoration-color: currentColor; }

    {{-- The button. One primary per view: the schedule's colour. --}}
    .gk-btn { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; min-height: 2.75rem; padding: 0 1.125rem; border: 1px solid transparent; border-radius: var(--gk-radius-ctl); font-size: .9375rem; font-weight: 650; line-height: 1.2; text-align: center; text-decoration: none; cursor: pointer; transition: background-color var(--gk-swap), box-shadow var(--gk-swap), transform var(--gk-press), opacity var(--gk-swap); }
    .gk-btn:active { transform: scale(.985); }
    .gk-btn:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 2px; }
    .gk-btn-primary { background: var(--es-accent); color: var(--es-accent-text); border-color: var(--es-accent-edge); box-shadow: 0 1px 2px rgb(15 23 42 / .18); }
    .gk-btn-primary:hover { box-shadow: var(--gk-shadow-lift); }
    .gk-btn-secondary { background: var(--gk-solid); color: var(--gk-ink); border-color: var(--gk-line); }
    .gk-btn-secondary:hover { background: var(--gk-well); }
    .gk-btn-quiet { background: transparent; color: var(--gk-ink-2); padding: 0 .625rem; }
    .gk-btn-quiet:hover { background: var(--gk-well); }
    .gk-btn-lg { min-height: 3.25rem; padding: 0 1.5rem; border-radius: .75rem; font-size: 1.0625rem; }
    .gk-btn-sm { padding: 0 .875rem; font-size: .875rem; }
    .gk-btn-icon { width: 2.75rem; padding: 0; }
    .gk-btn-block { display: flex; width: 100%; }
    .gk-btn[disabled], .gk-btn[aria-disabled="true"] { opacity: .45; cursor: default; box-shadow: none; }
    @media (min-width: 64rem) { .gk-btn-sm { min-height: 2.375rem; } }

    {{-- A chip: one fact about an event, read at a glance. --}}
    .gk-chip { display: inline-flex; align-items: center; gap: .3125rem; min-height: 1.5rem; padding: 0 .5625rem; border: 1px solid transparent; border-radius: 999px; background: var(--gk-well); color: var(--gk-ink-2); font-size: .78125rem; font-weight: 650; line-height: 1.2; white-space: nowrap; }
    .gk-chip-free { background: var(--gk-ok-bg); color: var(--gk-ok); border-color: var(--gk-ok-line); }
    .gk-chip-few { background: var(--gk-warn-bg); color: var(--gk-warn); border-color: var(--gk-warn-line); }
    .gk-chip-out { background: var(--gk-bad-bg); color: var(--gk-bad); border-color: var(--gk-bad-line); }
    .gk-chip-accent { background: var(--es-accent-tint); color: var(--es-accent-readable); }

    {{-- A notice: something the visitor is told. A tinted ground and a full hairline, never a stripe down one side. --}}
    .gk-note { display: flex; align-items: flex-start; gap: .625rem; padding: .75rem .875rem; border: 1px solid var(--gk-line); border-radius: .75rem; background: var(--gk-well); color: var(--gk-ink); font-size: .90625rem; line-height: 1.4; }
    .gk-note-icon { flex: none; width: 1.25rem; height: 1.25rem; margin-top: .0625rem; }
    .gk-note-body { flex: 1 1 auto; min-width: 0; }
    .gk-note-ok { background: var(--gk-ok-bg); border-color: var(--gk-ok-line); color: var(--gk-ok); }
    .gk-note-warn { background: var(--gk-warn-bg); border-color: var(--gk-warn-line); color: var(--gk-warn); }
    .gk-note-bad { background: var(--gk-bad-bg); border-color: var(--gk-bad-line); color: var(--gk-bad); }

    {{-- No size or padding here: layouts/app forces both on every text field (the 16px that
         stops a phone zooming in), and a rule that cannot apply should not be written. --}}
    .gk-input { min-height: 2.875rem; border: 1px solid var(--gk-line); border-radius: var(--gk-radius-ctl); background: var(--gk-solid); color: var(--gk-ink); }
    .gk-input::placeholder { color: var(--gk-ink-3); }
    .gk-input:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 1px; }
    .gk-input-bad { border-color: var(--gk-bad); box-shadow: 0 0 0 1px var(--gk-bad); }

    @media (prefers-reduced-motion: reduce) {
        .gk-btn, .gk-link { transition-duration: 1ms; }
        .gk-btn:active { transform: none; }
    }

    @media print {
        {{-- .dark as well: a panel that kept its dark:bg- utility would outrank the single class
             and print black text on a dark ground. --}}
        .gk-panel, .dark .gk-panel { background: #ffffff; color: #000000; box-shadow: none; -webkit-backdrop-filter: none; backdrop-filter: none; border: 1px solid #d1d5db; }
        .gk-btn { display: none; }
        .gk-note { background: #ffffff; color: #000000; border-color: #9ca3af; }
    }
</style>
