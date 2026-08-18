<x-marketing-layout>
    <x-slot name="title">Integrations | Calendars, Stripe, Webhooks and the API - Event Schedule</x-slot>
    <x-slot name="description">Twelve real integrations, each with its direction, its trigger and its plan written on the label: Google Calendar, Outlook, CalDAV, Stripe, Invoice Ninja, webhooks, the REST API, web push, the accommodation map, Eventbrite import, Facebook and Instagram ads, and WhatsApp.</x-slot>
    <x-slot name="breadcrumbTitle">Integrations</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Integrations",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "description": "Event Schedule connects directly to the services you already run: two-way calendar sync with Google Calendar, Outlook and CalDAV, payments through your own Stripe account or Invoice Ninja, signed outbound webhooks, a REST API, OneSignal web push, a nearby-accommodation map, Eventbrite import, Facebook and Instagram ad boosting, and event creation over WhatsApp.",
        "featureList": [
            "Google Calendar two-way sync with change notifications",
            "Outlook and Microsoft 365 two-way sync via Microsoft Graph",
            "CalDAV two-way sync with any CalDAV server",
            "Stripe payments on your own connected account with zero platform fees",
            "Invoice Ninja invoicing or payment links",
            "Signed outbound webhooks on fourteen event types",
            "REST API for schedules, sub-schedules, events and sales",
            "OneSignal browser and mobile web push",
            "Nearby accommodation map on public event pages",
            "Eventbrite import for events, ticket types and venues",
            "Facebook and Instagram ad boosting through Meta",
            "Event creation over WhatsApp with AI parsing"
        ],
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}"
        },
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
    </x-slot>

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present,
         so no-JS visitors, crawlers, and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <style {!! nonce_attr() !!}>
        /* ==============================================================
           For-integrations "The Wire" styles. An integration here is not
           a marketplace tile, it is a WIRE: one end terminated in Event
           Schedule, the other end terminated in a service the reader
           already runs. Both ends are visible, and the label on the port
           says the three things a reader actually needs - which way the
           wire runs, what makes data move along it, and which plan it is
           on. The concept and the feature story are the same sentence:
           there is no broker in the middle, which is exactly why the
           money lands in the reader's own Stripe account, the calendar
           events land on the reader's own CalDAV server, and the webhook
           POSTs to the reader's own URL.

           THE SIGNATURE DEVICE IS A PATCH PANEL, NOT A LOGO WALL. A logo
           wall was the first-wave device and it is the wrong one twice
           over: it invites padding the page with services that are not
           integrated, and it says nothing about direction. The panel is
           a fixed physical object - the graphite bezel renders
           IDENTICALLY with .dark on and off, verified with
           --bands=.es-wire-bezel - so .es-wire-track, .es-wire-dir,
           .es-wire-tag, .es-wire-plan and .es-wire-stub carry bezel
           overrides placed AFTER their .dark rules.

           TWO TRAPS THIS PAGE HAS ALREADY PAID FOR, both invisible to the
           desktop verifier and both about the panel's WIDTH on a phone:
           (1) this block is rendered after the prebuilt bundle, so a
           page-local `display:` beats Tailwind's `hidden` on source
           order - any visibility toggle on an .es-wire-* element must be
           page-local too (see .es-wire-track-cell). (2) .es-wire-dir is
           nowrap, so a whole sentence in it sets the panel's min-content
           and widens the hero grid past the viewport (see
           .es-wire-legend). Measure with min-content, not by eye.

           COLOUR: this page keeps its existing accent family, the signal
           green already in the first-wave block (#10b981 / #34d399), on
           a graphite instrument ground. It reads as a live conductor
           rather than a brand hue, which is the point: green means this
           wire is carrying something. Deliberately NOT the bottle green
           of /for-theaters, the forest of /for-food-trucks, the
           exit-sign state green of /for-nightclubs or the teal of
           /for-djs, and deliberately not the shared brand
           blue-sky-cyan gradient, which is chrome and not a page accent.

           NEVER use text-gray-500 on this page's tinted ground - it
           drops to about 4.2. Use .es-wire-muted (7.42 on #f4f6f7).

           BLADE RULE for this block: no @supports() probes with a "#"
           hex inside, which breaks Blade compilation of every later
           parenthesized directive.
           ============================================================== */

        /* --- Layout and ink helpers that would otherwise be Tailwind
               arbitrary values. The marketing bundle is PREBUILT, so any
               arbitrary-value class that is not already compiled into it
               silently does nothing - which is how a page ends up with
               page-coloured ink on an always-dark band. Every design-critical
               colour and size on this page is a real rule here. --- */
        .es-wire-tall { min-height: calc(86svh - 4rem); }
        .es-wire-split { display: grid; }
        @media (min-width: 1024px) {
            .es-wire-split { grid-template-columns: 1.02fr 1fr; }
        }
        .es-wire-rule { border-color: rgba(16, 20, 24, 0.08); }
        .dark .es-wire-rule { border-color: rgba(231, 234, 238, 0.08); }
        .es-wire-tip { background-color: #ffffff; }
        .dark .es-wire-tip { background-color: #151a1e; }
        /* Ink for text sitting on an always-dark band, in BOTH colour modes. */
        .es-wire-onband { color: #e7eaee; }
        .es-wire-onband-muted { color: #9aa3ad; }

        /* --- Ground and ink --- */
        .es-wire-page { background-color: #f4f6f7; color: #101418; }
        .dark .es-wire-page { background-color: #0a0e10; color: #e7eaee; }
        .es-wire-ink { color: #101418; }
        .dark .es-wire-ink { color: #e7eaee; }
        .es-wire-muted { color: #4a5158; }
        .dark .es-wire-muted { color: #9aa3ad; }
        .es-wire-accent { color: #046c50; }
        .dark .es-wire-accent { color: #34d399; }
        /* Always-lit conductor, for the fixed-dark bezel and bands. */
        .es-wire-lit { color: #34d399; }

        /* --- Screen-printed label type --- */
        .es-wire-mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
        }
        .es-wire-tag {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            color: #4a5158;
        }
        .dark .es-wire-tag { color: #9aa3ad; }

        /* --- Cards --- */
        .es-wire-card {
            border: 1px solid rgba(16, 20, 24, 0.12);
            border-radius: 1rem;
            background: #ffffff;
        }
        .dark .es-wire-card {
            border-color: rgba(231, 234, 238, 0.12);
            background: #151a1e;
        }
        /* An unterminated port: hollow on purpose, not merely dimmer. */
        .es-wire-open { border-style: dashed; background: transparent; }
        .dark .es-wire-open { background: transparent; }

        /* --- Fixed-dark band: the wiring cabinet --- */
        .es-wire-band {
            background-color: #0c1114;
            background-image: radial-gradient(120% 100% at 50% 0%, #161d21 0%, #101619 55%, #080b0d 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(231, 234, 238, 0.05);
        }
        .es-wire-band .es-wire-card {
            border-color: rgba(231, 234, 238, 0.14);
            background: rgba(231, 234, 238, 0.05);
        }
        .es-wire-band .es-wire-tag { color: #34d399; }
        /* The direction label is muted-on-light by default, which is invisible
           on an always-dark band in LIGHT mode. Measured 2.36 before this rule. */
        .es-wire-band .es-wire-dir { color: #9aa3ad; }
        /* Shared classes that flip with the colour mode inside a fixed band. */
        .es-wire-band .grid-overlay {
            background-image:
                linear-gradient(rgba(231, 234, 238, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(231, 234, 238, 0.05) 1px, transparent 1px);
        }
        .es-wire-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-wire-band .es-claim:focus-within {
            border-color: rgba(52, 211, 153, 0.75);
            box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.22);
        }

        /* --- The patch panel. A rack face, screen-printed, the same real
               object in light mode and dark mode. --- */
        .es-wire-bezel {
            position: relative;
            border: 1px solid rgba(231, 234, 238, 0.13);
            border-radius: 1.1rem;
            padding: 1.35rem 1.25rem 1.1rem;
            background-color: #12171a;
            background-image:
                linear-gradient(180deg, rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0) 20%),
                radial-gradient(130% 150% at 50% -25%, #1b2226 0%, #0f1417 58%, #0a0e10 100%);
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, 0.06),
                inset 0 0 60px rgba(0, 0, 0, 0.55),
                0 26px 52px -30px rgba(0, 0, 0, 0.65);
        }
        .es-wire-screw {
            position: absolute;
            width: 7px;
            height: 7px;
            border-radius: 9999px;
            background: radial-gradient(circle at 35% 30%, rgba(231, 234, 238, 0.35), rgba(231, 234, 238, 0.06) 70%);
            box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.5);
        }
        .es-wire-bar {
            height: 1px;
            background: rgba(231, 234, 238, 0.1);
        }

        /* One port: number, label, conductor, direction, plan. */
        .es-wire-row {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            align-items: center;
            gap: 0.7rem;
            padding: 0.42rem 0;
        }
        .es-wire-port {
            display: grid;
            place-items: center;
            flex: none;
            width: 1.45rem;
            height: 1.45rem;
            border-radius: 9999px;
            border: 1px solid rgba(52, 211, 153, 0.45);
            color: #34d399;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.56rem;
            font-weight: 800;
            letter-spacing: 0.02em;
        }
        /* The same lug on a page card rather than on the graphite bezel: the
           bright conductor green is 1.5:1 on white, so it has to darken. The
           bezel is not an .es-wire-card, so its lugs are untouched. */
        .es-wire-card .es-wire-port { border-color: rgba(4, 108, 80, 0.45); color: #046c50; }
        .dark .es-wire-card .es-wire-port,
        .es-wire-band .es-wire-card .es-wire-port { border-color: rgba(52, 211, 153, 0.45); color: #34d399; }

        .es-wire-cell-dir { flex: none; width: 3.4rem; text-align: end; }
        .es-wire-cell-plan { flex: none; width: 3rem; display: flex; justify-content: flex-end; }
        .es-wire-name {
            color: #e7eaee;
            font-size: 0.8rem;
            font-weight: 600;
            line-height: 1.2;
        }
        .es-wire-sub {
            display: block;
            color: #9aa3ad;
            font-size: 0.64rem;
            font-weight: 400;
            letter-spacing: 0.02em;
        }

        /* The conductor. A pulse travels the way the data actually travels. */
        .es-wire-track {
            display: block;
            position: relative;
            min-width: 34px;
            height: 2px;
            border-radius: 2px;
            overflow: hidden;
            background: rgba(231, 234, 238, 0.14);
        }
        .es-wire-track::after {
            content: "";
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            width: 34%;
            border-radius: 2px;
            background: linear-gradient(90deg, transparent, #34d399, transparent);
            animation: es-wire-run 2.6s linear infinite;
            animation-delay: var(--wd, 0s);
        }
        /* The panel's own conductor column. It CANNOT be Tailwind's `hidden ...
           sm:block`: this page-local block is rendered after the prebuilt
           bundle, so `.es-wire-track { display: block }` wins on source order
           and `hidden` silently does nothing. The result was a panel row whose
           min-content was wider than a phone, which widened the hero grid and
           clipped the headline. Hide it here instead, after that rule. */
        .es-wire-track-cell { display: none; }
        @media (min-width: 640px) {
            .es-wire-track-cell { display: block; width: 4rem; }
        }

        .es-wire-track-in::after { animation-name: es-wire-back; }
        /* An open conductor: dashed and carrying nothing, for the one port on the
           page that is not wired yet. No pulse, so nothing to gate for motion. */
        .es-wire-track-open {
            background: repeating-linear-gradient(90deg, rgba(231, 234, 238, 0.28) 0 4px, transparent 4px 9px);
        }
        .es-wire-track-open::after { display: none; }
        .es-wire-track-both::after {
            animation-name: es-wire-run;
            animation-duration: 3.6s;
            animation-direction: alternate;
            animation-timing-function: ease-in-out;
        }
        @keyframes es-wire-run {
            from { transform: translateX(-115%); }
            to   { transform: translateX(320%); }
        }
        @keyframes es-wire-back {
            from { transform: translateX(320%); }
            to   { transform: translateX(-115%); }
        }

        /* Direction label. Muted in the register, screen-print grey on the panel. */
        .es-wire-dir {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.66rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            white-space: nowrap;
            color: #4a5158;
        }
        .dark .es-wire-dir { color: #9aa3ad; }
        /* The key printed along the bottom of the panel is a whole sentence in
           the direction typeface, and .es-wire-dir is nowrap because a
           two-letter label must never break. Nowrap on the key made the panel's
           min-content ~438px, which widened the hero grid past a phone and
           clipped the headline, so the key opts back out of it. */
        .es-wire-legend { white-space: normal; }

        /* --- Plan pills. Green outline = free on every plan; neutral ink
               outline (.es-wire-plan-pro) = a paid tier, and the label itself
               says which one, Pro or Ent. --- */
        .es-wire-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.25rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            border: 1px solid rgba(4, 108, 80, 0.42);
            color: #046c50;
        }
        .dark .es-wire-plan { border-color: rgba(52, 211, 153, 0.42); color: #34d399; }
        .es-wire-band .es-wire-plan { border-color: rgba(52, 211, 153, 0.42); color: #34d399; }
        .es-wire-plan-pro { border-color: rgba(16, 20, 24, 0.35); color: #101418; }
        .dark .es-wire-plan-pro { border-color: rgba(231, 234, 238, 0.38); color: #e7eaee; }
        .es-wire-band .es-wire-plan-pro { border-color: rgba(231, 234, 238, 0.38); color: #e7eaee; }

        /* Bezel overrides. These come AFTER the .dark rules on purpose: equal
           specificity, later declaration wins, so the panel is the same
           physical object whichever colour mode the reader is in. */
        .es-wire-bezel .es-wire-tag { color: #34d399; }
        .es-wire-bezel .es-wire-dir { color: #9aa3ad; }
        .es-wire-bezel .es-wire-plan { border-color: rgba(52, 211, 153, 0.42); color: #34d399; }
        .es-wire-bezel .es-wire-plan-pro { border-color: rgba(231, 234, 238, 0.38); color: #e7eaee; }

        /* --- A stub: the honest "no port here" marker, mode-aware --- */
        .es-wire-stub {
            display: grid;
            place-items: center;
            flex: none;
            width: 1.45rem;
            height: 1.45rem;
            border-radius: 0.3rem;
            border: 1px dashed rgba(16, 20, 24, 0.3);
            color: #4a5158;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.66rem;
            font-weight: 800;
        }
        .dark .es-wire-stub { border-color: rgba(231, 234, 238, 0.3); color: #9aa3ad; }
        /* AFTER the .dark rule: a stub sitting on the graphite bezel is the same
           object in both colour modes, so it cannot use the light-mode ink. */
        .es-wire-bezel .es-wire-stub { border-color: rgba(231, 234, 238, 0.3); color: #9aa3ad; }

        /* --- The register: a real record, so it is a real table --- */
        .es-wire-scroll { overflow-x: auto; }
        .es-wire-reg {
            width: 100%;
            min-width: 34rem;
            border-collapse: collapse;
            text-align: left;
        }
        .es-wire-reg th,
        .es-wire-reg td {
            padding: 0.7rem 0.8rem 0.7rem 0;
            vertical-align: top;
            border-top: 1px solid rgba(16, 20, 24, 0.09);
        }
        .dark .es-wire-reg th,
        .dark .es-wire-reg td { border-top-color: rgba(231, 234, 238, 0.09); }
        .es-wire-reg thead th { border-top: 0; padding-top: 0; padding-bottom: 0.45rem; }

        /* --- Heat-shrink label, for event types and endpoint paths --- */
        .es-wire-slug {
            display: inline-flex;
            align-items: center;
            padding: 0.16rem 0.45rem;
            border-radius: 0.25rem;
            border: 1px solid rgba(16, 20, 24, 0.14);
            background: rgba(16, 20, 24, 0.04);
            color: #101418;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.68rem;
            white-space: nowrap;
        }
        .dark .es-wire-slug {
            border-color: rgba(231, 234, 238, 0.16);
            background: rgba(231, 234, 238, 0.05);
            color: #e7eaee;
        }
        .es-wire-band .es-wire-slug {
            border-color: rgba(231, 234, 238, 0.16);
            background: rgba(231, 234, 238, 0.05);
            color: #e7eaee;
        }

        /* --- Spec strip: the numbers stamped on the side of the block.
               Stacks on narrow screens; becomes a key/value pair grid from sm. --- */
        .es-wire-spec { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0.1rem 0.9rem; }
        .es-wire-spec-k {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            padding-top: 0.55rem;
            color: #4a5158;
        }
        .dark .es-wire-spec-k { color: #9aa3ad; }
        .es-wire-band .es-wire-spec-k { color: #34d399; }
        .es-wire-spec-v { font-size: 0.83rem; line-height: 1.5; }
        @media (min-width: 640px) {
            .es-wire-spec { grid-template-columns: 8rem minmax(0, 1fr); row-gap: 0.4rem; }
            .es-wire-spec-k { padding-top: 0.15rem; }
        }

        /* --- Section numeral, stamped on a bezel corner --- */
        .es-wire-corner {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.35rem 0.85rem;
            border-radius: 0.3rem;
            border: 1px solid rgba(16, 20, 24, 0.18);
            background: #ffffff;
            color: #101418;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-weight: 800;
            letter-spacing: 0.04em;
        }
        .dark .es-wire-corner { border-color: rgba(231, 234, 238, 0.2); background: rgba(231, 234, 238, 0.05); color: #e7eaee; }
        .es-wire-band .es-wire-corner { border-color: rgba(231, 234, 238, 0.2); background: rgba(231, 234, 238, 0.05); color: #e7eaee; }
        .es-wire-corner::before {
            content: "";
            width: 2px;
            align-self: stretch;
            border-radius: 1px;
            background: #046c50;
        }
        .dark .es-wire-corner::before { background: #34d399; }
        .es-wire-band .es-wire-corner::before { background: #34d399; }

        /* --- List items: each fact gets its own short strand --- */
        .es-wire-li { position: relative; padding-inline-start: 0.95rem; }
        .es-wire-li::before {
            content: "";
            position: absolute;
            inset-inline-start: 0;
            top: 0.6em;
            width: 0.5rem;
            height: 2px;
            border-radius: 2px;
            background: #046c50;
        }
        .dark .es-wire-li::before { background: #34d399; }

        /* --- Chips --- */
        .es-wire-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            border: 1px solid rgba(16, 20, 24, 0.16);
            background: rgba(255, 255, 255, 0.7);
            color: #4a5158;
            font-size: 0.76rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .dark .es-wire-chip {
            border-color: rgba(231, 234, 238, 0.16);
            background: rgba(231, 234, 238, 0.05);
            color: #b0b8c0;
        }

        /* --- Links and buttons --- */
        .es-wire-link { color: #046c50; }
        .es-wire-link:hover { color: #101418; }
        .dark .es-wire-link { color: #34d399; }
        .dark .es-wire-link:hover { color: #e7eaee; }

        .es-wire-btn {
            background-color: #046c50;
            box-shadow: 0 18px 36px -14px rgba(4, 108, 80, 0.5);
        }
        .es-wire-btn:hover { background-color: #035a42; box-shadow: 0 22px 44px -14px rgba(4, 108, 80, 0.6); }
        /* Dark mode flips to a light conductor, so the label has to go dark.
           .dark .es-wire-btn outranks the text-white utility on its own. */
        .dark .es-wire-btn { background-color: #34d399; color: #0a0e10; }
        .dark .es-wire-btn:hover { background-color: #6ee7b7; }

        /* --- FAQ / related hover --- */
        .es-wire-hover:hover { border-color: rgba(4, 108, 80, 0.45); }
        .dark .es-wire-hover:hover { border-color: rgba(52, 211, 153, 0.45); }
        .es-wire-hover:hover .es-wire-hover-title,
        .es-wire-hover:hover .es-wire-hover-arrow { color: #046c50; }
        .dark .es-wire-hover:hover .es-wire-hover-title,
        .dark .es-wire-hover:hover .es-wire-hover-arrow { color: #34d399; }

        /* --- Shared-system recolors (brand blue by default) --- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(4, 108, 80, 0.12), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(52, 211, 153, 0.1), transparent 60%);
        }
        #es-wire-page .es-glare {
            background: radial-gradient(620px circle at var(--gx, 50%) var(--gy, 50%), rgba(4, 108, 80, 0.09), transparent 45%);
        }
        .dark #es-wire-page .es-glare {
            background: radial-gradient(620px circle at var(--gx, 50%) var(--gy, 50%), rgba(255, 255, 255, 0.08), transparent 45%);
        }
        #es-wire-page .es-ring-glow {
            background: radial-gradient(420px circle at var(--gx, 50%) var(--gy, 50%), rgba(4, 108, 80, 0.6), rgba(52, 211, 153, 0.22) 45%, transparent 70%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(4, 108, 80, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(52, 211, 153, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #046c50; }
        .dark .es-dot.is-active .es-dot-pip { background: #34d399; }

        /* --- Focus rings. No border-radius here: setting it would change the
               element's own shape on focus. Outlines already follow it. --- */
        #es-wire-page a:focus-visible,
        #es-wire-page summary:focus-visible,
        #es-wire-page button:focus-visible {
            outline: 2px solid #046c50;
            outline-offset: 3px;
        }
        .dark #es-wire-page a:focus-visible,
        .dark #es-wire-page summary:focus-visible,
        .dark #es-wire-page button:focus-visible {
            outline-color: #34d399;
        }
        .es-wire-band a:focus-visible,
        .es-wire-band summary:focus-visible,
        .es-wire-band button:focus-visible {
            outline-color: #34d399 !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-wire-track::after {
                animation: none !important;
                left: 33%;
                transform: none;
                opacity: 0.55;
            }
        }
    </style>

    @php
        // The twelve ports. Direction is the first thing a reader needs, so it is
        // screen-printed on the panel and repeated in the register below. It is
        // the direction the DATA runs, not the direction the call is made in:
        // CalDAV and Eventbrite arrive by us asking rather than them telling.
        $ports = [
            ['01', 'Google Calendar',  'two-way sync',        'both', 'Free'],
            ['02', 'Outlook 365',      'Microsoft Graph',     'both', 'Free'],
            ['03', 'CalDAV',           'your own server',     'both', 'Free'],
            ['04', 'Stripe',           'checkout + webhook',  'both', 'Free'],
            ['05', 'Invoice Ninja',    'invoice or pay link', 'both', 'Free'],
            ['06', 'Webhooks',         'your endpoint',       'out',  'Pro'],
            ['07', 'REST API',         'you call us',         'in',   'Pro'],
            ['08', 'Eventbrite',       'import on demand',    'in',   'Pro'],
            ['09', 'Web push',         'OneSignal',           'out',  'Pro'],
            ['10', 'Accommodation',    'Stay22 map',          'out',  'Free'],
            ['11', 'Meta ads',         'boost a campaign',    'out',  'Pro'],
            ['12', 'WhatsApp',         'Twilio inbound',      'in',   'Ent'],
        ];

        // The register. Every row is traceable to code: the calendar services,
        // StripeController / TicketController, WebhookService, routes/api.php,
        // OneSignalService and Stay22Service.
        $register = [
            [
                'Google Calendar', 'both', 'Free',
                'Saving an event writes it to Google straight away. Google notifies us back through a watch channel, and a sweep every fifteen minutes catches anything the channel missed.',
            ],
            [
                'Outlook / Microsoft 365', 'both', 'Free',
                'The same shape over Microsoft Graph, with a change subscription inbound and a delta token so a resync does not re-read the whole calendar. Optional Teams meeting link.',
            ],
            [
                'CalDAV', 'both or one', 'Free',
                'Server URL, username, password. Outbound on save; inbound is polled every fifteen minutes, because CalDAV has no notification standard to subscribe to.',
            ],
            [
                'Stripe', 'out then in', 'Free',
                'Checkout runs on Stripe. On the hosted platform the charge is created on your own connected account, and the result returns as a signed webhook. Selling starts free at twenty-five paid tickets a month per schedule; Pro takes the ceiling off.',
            ],
            [
                'Invoice Ninja', 'out then in', 'Free',
                'Either an invoice per sale, or a payment link the buyer completes on your Invoice Ninja install. A webhook marks the sale paid when it clears.',
            ],
            [
                'Webhooks', 'out', 'Pro',
                'Fourteen event types, POSTed as JSON to your URL with an HMAC-SHA256 signature. Five-second timeout, three tries, and every attempt logged.',
            ],
            [
                'REST API', 'in', 'Pro',
                'Full create, read, update and delete on schedules, sub-schedules, events and sales, plus read endpoints for feedback and fan content. Key in a header.',
            ],
            [
                'Eventbrite import', 'in', 'Pro',
                'Paste an Eventbrite private token, pick from the events it finds, and they arrive with their venues, ticket types and images. You press the button; nothing runs on a timer.',
            ],
            [
                'Web push', 'out', 'Pro',
                'The notifications you already get by email, repeated as browser and mobile push. Per device, opt-in, and only where the site operator has configured it.',
            ],
            [
                'Accommodation map', 'out', 'Free',
                'Lodging near the venue on your public event pages, dated from the occurrence being viewed. Nothing is requested until the visitor consents.',
            ],
            [
                'Meta ads', 'out', 'Pro',
                'Your event becomes a Facebook and Instagram campaign: you choose budget, dates, audience and placements. The ads run on the ad account of whoever operates your Event Schedule site.',
            ],
            [
                'WhatsApp', 'in', 'Ent',
                'Message the site\'s WhatsApp number, or send a photo of a flyer, and the event is created and answered with its link. Runs over the operator\'s Twilio account.',
            ],
        ];

        // Webhook event types, verbatim from Webhook::EVENT_TYPES. The heading below counts them,
        // so adding one there means adding it here too.
        $hookTypes = [
            'sale.created', 'sale.paid', 'sale.refunded', 'sale.cancelled',
            'installment.paid', 'installment.failed',
            'event.created', 'event.updated', 'event.deleted', 'event.cancelled',
            'ticket.scanned', 'ticket.booked', 'ticket.booking_cancelled', 'feedback.submitted',
        ];

        $apiPaths = [
            '/api/schedules', '/api/events', '/api/sales',
            '/api/categories', '/api/feedback', '/api/fan-content',
        ];

        $faqs = [
            [
                'q' => 'Which integrations are free?',
                'a' => 'Two-way calendar sync with Google Calendar, Outlook or Microsoft 365 and any CalDAV server is free on every plan, and so is the nearby-accommodation map. So are the two money ports: selling starts free at twenty-five paid tickets a month per schedule, with zero platform fees on every plan. Pro at '.plan_price($proMonthly).' a month takes that ceiling off and adds webhooks, the REST API, web push, Eventbrite import and ad boosting. Creating events over WhatsApp is the one Enterprise port, at '.plan_price($entMonthly).'. Selfhosted installs resolve to the top tier, so every port is on from the first boot.',
            ],
            [
                'q' => 'Is the calendar sync really two-way?',
                'a' => 'Yes, and you choose the direction per schedule: import only, export only, or both. Outbound happens when you save an event. Inbound arrives through a Google watch channel or a Microsoft Graph change subscription, backed up by a sweep every fifteen minutes. CalDAV inbound is polled only, because CalDAV has no notification standard to subscribe to.',
            ],
            [
                'q' => 'Where does the ticket money actually go?',
                'a' => 'Into your own Stripe account. On the hosted platform the charge is created on your connected account, so payouts land on your Stripe schedule and Event Schedule takes zero platform fees; you pay Stripe its processing fee and nothing else. A selfhosted install uses your own Stripe keys directly, with nothing in between at all.',
            ],
            [
                'q' => 'Can I connect a tool that is not on this page?',
                'a' => 'That is what the terminal block is for. A webhook POSTs to any URL you own on fourteen event types, signed with HMAC-SHA256 so you can verify it came from us, and the REST API lets your own code read and write schedules, events and sales with a key in an X-API-Key header. There is no Zapier or Make app; those two ports are the general-purpose route, and both are on the Pro plan.',
            ],
            [
                'q' => 'Do you integrate with Zoom, YouTube or Twitch?',
                'a' => 'No, and it is worth being plain about it. An online event carries one link field: mark the event as online and paste the URL to wherever you are streaming. The one place that field gets filled in for you is Outlook, where turning on Teams meetings writes the join link into it for events that have no venue.',
            ],
            [
                'q' => 'What happens if I delete an event in my calendar?',
                'a' => 'You decide, per schedule. An event deleted in the calendar you import from can be kept in Event Schedule, marked as cancelled so it is hidden but reversible, or deleted outright. Events that have ticket sales or a running ad boost are hidden instead of deleted, so a stray drag in a calendar app cannot take a sold show with it.',
            ],
        ];

        $dotSections = [
            ['top', 'The panel'],
            ['direction', 'Direction'],
            ['register', 'The register'],
            ['calendars', 'Calendar ports'],
            ['money', 'Money ports'],
            ['block', 'Terminal block'],
            ['edges', 'Operator ports'],
            ['open', 'Not wired'],
            ['faq', 'Questions'],
            ['claim', 'Wire it up'],
        ];
    @endphp

    <div id="es-wire-page" class="es-wire-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the patch panel                                     -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative es-wire-tall flex scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(4, 108, 80, 0.18), rgba(4, 108, 80, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(52, 211, 153, 0.13), rgba(52, 211, 153, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="es-wire-split grid items-center gap-14">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="es-wire-accent h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        <span class="es-wire-muted text-sm font-medium tracking-wide">Twelve ports, both ends visible</span>
                    </div>

                    <h1 class="es-balance es-wire-ink mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">No middle layer.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">Just <span class="es-wire-accent">wires.</span></span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-wire-muted mb-8 max-w-xl text-lg sm:text-xl">
                        Event Schedule talks straight to the services you already run: your calendar or your own CalDAV server, your own Stripe account, your own endpoint. Twelve ports, each labelled with which way it runs, what makes data move, and which plan it is on.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="#register" class="glass group inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            Read the wiring register
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                        <a href="{{ app_url('/sign_up') }}" class="es-wire-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Get started free
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- The panel. A fixed physical object: identical in both colour modes. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-wire-bezel">
                        <span class="es-wire-screw" style="top: 0.55rem; left: 0.55rem;" aria-hidden="true"></span>
                        <span class="es-wire-screw" style="top: 0.55rem; right: 0.55rem;" aria-hidden="true"></span>
                        <span class="es-wire-screw" style="bottom: 0.55rem; left: 0.55rem;" aria-hidden="true"></span>
                        <span class="es-wire-screw" style="bottom: 0.55rem; right: 0.55rem;" aria-hidden="true"></span>

                        <div class="mb-2 flex items-baseline justify-between gap-3 px-1">
                            <p class="es-wire-tag">Integration panel</p>
                            <p class="es-wire-dir">12 ports</p>
                        </div>
                        <div class="es-wire-bar mb-1.5" aria-hidden="true"></div>

                        <div class="px-1">
                            @foreach ($ports as $portIndex => [$num, $portName, $portSub, $portDir, $portPlan])
                                <div class="es-wire-row">
                                    <span class="es-wire-port" aria-hidden="true">{{ $num }}</span>
                                    <span class="min-w-0">
                                        <span class="es-wire-name">{{ $portName }}</span>
                                        <span class="es-wire-sub es-wire-mono">{{ $portSub }}</span>
                                    </span>
                                    <span class="flex items-center gap-2.5">
                                        <span class="es-wire-track @if ($portDir === 'in') es-wire-track-in @endif @if ($portDir === 'both') es-wire-track-both @endif es-wire-track-cell" style="--wd: {{ $portIndex * 0.22 }}s;" aria-hidden="true"></span>
                                        <span class="es-wire-dir es-wire-cell-dir">{{ $portDir }}</span>
                                        <span class="es-wire-cell-plan"><span class="es-wire-plan @if ($portPlan !== 'Free') es-wire-plan-pro @endif">{{ $portPlan }}</span></span>
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        <div class="es-wire-bar mb-2 mt-1.5" aria-hidden="true"></div>
                        <p class="es-wire-dir es-wire-legend px-1">out = data leaves here &middot; in = data arrives here</p>
                    </div>
                </div>
            </div>

            <!-- What is on the other end of the wires -->
            <div class="es-fade-up es-d-4 mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach (['Google Calendar', 'Outlook', 'Microsoft 365', 'Nextcloud', 'Radicale', 'Fastmail', 'Baikal', 'Stripe', 'Invoice Ninja', 'Eventbrite', 'Facebook', 'Instagram', 'WhatsApp', 'Your endpoint'] as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-wire-chip">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. Direction (fixed-dark band)                               -->
    <!-- ============================================================ -->
    <section id="direction" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-wire-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-wire-corner mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                    <p class="es-wire-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Direction</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        A wire has a direction. <span class="es-wire-lit">So does every port here.</span>
                    </h2>
                    <p class="mt-5 text-lg es-wire-onband-muted" data-reveal style="--reveal-delay: 0.15s;">
                        Almost every integration question is really a direction question. So this is the first thing the labels tell you.
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    <div class="es-wire-card p-6" data-reveal="panel">
                        <div class="mb-4 flex items-center gap-3">
                            <span class="es-wire-track block w-full" aria-hidden="true"></span>
                            <span class="es-wire-dir">out</span>
                        </div>
                        <h3 class="mb-2 text-lg font-bold es-wire-onband">Data leaves here</h3>
                        <p class="text-sm es-wire-onband-muted">Saving an event is the trigger, not a timer: that save is what pushes it to the calendars you have connected and POSTs it to your webhook URL. Nothing waits for a nightly batch.</p>
                    </div>
                    <div class="es-wire-card p-6" data-reveal="panel">
                        <div class="mb-4 flex items-center gap-3">
                            <span class="es-wire-track es-wire-track-in block w-full" aria-hidden="true"></span>
                            <span class="es-wire-dir">in</span>
                        </div>
                        <h3 class="mb-2 text-lg font-bold es-wire-onband">Data arrives here</h3>
                        <p class="text-sm es-wire-onband-muted">Google and Microsoft tell us when something changed; Stripe, Invoice Ninja and Twilio report the same way. Where a service cannot tell us, we ask: CalDAV on a timer, Eventbrite when you press import. The REST API is inbound too, with your code doing the calling.</p>
                    </div>
                    <div class="es-wire-card p-6" data-reveal="panel">
                        <div class="mb-4 flex items-center gap-3">
                            <span class="es-wire-track es-wire-track-both block w-full" aria-hidden="true"></span>
                            <span class="es-wire-dir">both</span>
                        </div>
                        <h3 class="mb-2 text-lg font-bold es-wire-onband">Two-way</h3>
                        <p class="text-sm es-wire-onband-muted">Both of those on one port, with the loop guarded at each end so an edit that arrived from a calendar is not immediately pushed back out to it.</p>
                    </div>
                </div>

                <p class="mx-auto mt-10 max-w-2xl text-center es-wire-onband-muted" data-reveal>
                    One honest difference: CalDAV has no notification standard, so its inbound leg is polled every fifteen minutes rather than pushed. Worth knowing before you pick a server.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The register: a real record, so a real table               -->
    <!-- ============================================================ -->
    <section id="register" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-wire-corner mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                <p class="es-wire-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The register</p>
                <h2 class="es-balance es-wire-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Twelve ports, <span class="es-wire-accent">written down.</span>
                </h2>
                <p class="es-wire-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Every one of them is on this page in full: the direction it runs, what makes data move along it, and the plan it is on. No marketplace, no logos we merely admire, and the gaps people hit are spelled out further down rather than left for you to find.
                </p>
            </div>

            <div class="es-wire-card p-5 sm:p-7" data-reveal="panel">
                <div class="es-wire-scroll">
                    <table class="es-wire-reg">
                        <caption class="sr-only">The twelve Event Schedule integration ports, with the direction each one runs, what makes data move along it, and the plan it is on</caption>
                        <thead>
                            <tr class="es-wire-tag">
                                <th scope="col" class="font-bold">Port</th>
                                <th scope="col" class="font-bold">Wire</th>
                                <th scope="col" class="font-bold">What moves it</th>
                                <th scope="col" class="font-bold">Plan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($register as [$regName, $regDir, $regPlan, $regWhat])
                                <tr>
                                    <th scope="row" class="es-wire-ink text-sm font-bold">{{ $regName }}</th>
                                    <td class="es-wire-dir">{{ $regDir }}</td>
                                    <td class="es-wire-muted max-w-md text-sm">{{ $regWhat }}</td>
                                    <td><span class="es-wire-plan @if ($regPlan !== 'Free') es-wire-plan-pro @endif">{{ $regPlan }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="es-wire-muted mt-5 text-xs">
                    Selfhosted installs resolve to the top tier, so every port above is on from the first boot. On the hosted platform, Pro is {{ plan_price($proMonthly) }} a month and Enterprise, which the WhatsApp port needs, is {{ plan_price($entMonthly) }}.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Calendar ports                                            -->
    <!-- ============================================================ -->
    <section id="calendars" class="scroll-mt-24 es-wire-rule border-y py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-wire-corner mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                <p class="es-wire-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Calendar ports</p>
                <h2 class="es-balance es-wire-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Your calendar, or <span class="es-wire-accent">your own server.</span>
                </h2>
                <p class="es-wire-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Three ports, all free on every plan, all two-way, and all with the direction under your control per schedule.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3" data-reveal-group="100">
                <a href="{{ marketing_url('/google-calendar') }}" class="es-bento group relative block" data-reveal="panel" data-tilt="4">
                    <div class="es-tilt-inner es-wire-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10 flex h-full flex-col">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <span class="es-wire-port" aria-hidden="true">01</span>
                                <h3 class="es-wire-ink text-xl font-bold">Google Calendar</h3>
                                <span class="es-wire-plan">Free</span>
                            </div>
                            <p class="es-wire-muted mb-4 text-sm">Connect with Google, choose which calendar, and pick the direction. Outbound happens on save; inbound arrives on a watch channel, so a change made in Google shows up without waiting.</p>
                            <ul class="es-wire-muted space-y-2 text-sm">
                                <li class="es-wire-li">Pick the calendar, not just the account</li>
                                <li class="es-wire-li">Sync one event on its own, or take it back out</li>
                                <li class="es-wire-li">Force a full re-push when a calendar has drifted</li>
                            </ul>
                            <span class="es-wire-link mt-auto inline-flex items-center gap-1 pt-5 text-sm font-semibold transition-all group-hover:gap-2">
                                Google Calendar sync
                                <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </span>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </a>

                <a href="{{ marketing_url('/outlook-calendar') }}" class="es-bento group relative block" data-reveal="panel" data-tilt="4">
                    <div class="es-tilt-inner es-wire-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10 flex h-full flex-col">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <span class="es-wire-port" aria-hidden="true">02</span>
                                <h3 class="es-wire-ink text-xl font-bold">Outlook and Microsoft 365</h3>
                                <span class="es-wire-plan">Free</span>
                            </div>
                            <p class="es-wire-muted mb-4 text-sm">The same shape over Microsoft Graph. A change subscription notifies us inbound, and a delta token means a resync reads what changed rather than the whole calendar again.</p>
                            <ul class="es-wire-muted space-y-2 text-sm">
                                <li class="es-wire-li">Personal, work and school accounts</li>
                                <li class="es-wire-li">Optional Teams meeting on events with no venue</li>
                                <li class="es-wire-li">The Teams join link lands in the event's link field</li>
                            </ul>
                            <span class="es-wire-link mt-auto inline-flex items-center gap-1 pt-5 text-sm font-semibold transition-all group-hover:gap-2">
                                Outlook calendar sync
                                <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </span>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </a>

                <a href="{{ marketing_url('/caldav') }}" class="es-bento group relative block" data-reveal="panel" data-tilt="4">
                    <div class="es-tilt-inner es-wire-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10 flex h-full flex-col">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <span class="es-wire-port" aria-hidden="true">03</span>
                                <h3 class="es-wire-ink text-xl font-bold">CalDAV</h3>
                                <span class="es-wire-plan">Free</span>
                            </div>
                            <p class="es-wire-muted mb-4 text-sm">Nextcloud, Radicale, Baikal, Fastmail or anything else that speaks CalDAV. Give it a server URL and credentials, test the connection, then choose from the calendars it finds.</p>
                            <ul class="es-wire-muted space-y-2 text-sm">
                                <li class="es-wire-li">Calendars discovered for you after the test</li>
                                <li class="es-wire-li">Push, pull, both ways, or off</li>
                                <li class="es-wire-li">No third party in the path at all</li>
                            </ul>
                            <span class="es-wire-link mt-auto inline-flex items-center gap-1 pt-5 text-sm font-semibold transition-all group-hover:gap-2">
                                CalDAV sync
                                <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </span>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </a>
            </div>

            <div class="es-wire-card mx-auto mt-6 max-w-4xl p-7" data-reveal="panel">
                <p class="es-wire-tag mb-4">The setting people ask about first</p>
                <h3 class="es-wire-ink mb-3 text-lg font-bold">What happens when you delete it there</h3>
                <p class="es-wire-muted mb-5 text-sm">
                    For a schedule that imports from a calendar, you choose what an outside deletion means. It is one setting with three answers, and it is the difference between a tidy calendar and a missing show.
                </p>
                <div class="es-wire-spec">
                    <span class="es-wire-spec-k">Keep it</span>
                    <span class="es-wire-spec-v es-wire-muted">The default. The event stays in Event Schedule and your calendar is simply tidier than your schedule.</span>
                    <span class="es-wire-spec-k">Mark cancelled</span>
                    <span class="es-wire-spec-v es-wire-muted">Hidden from the public schedule, still there for you, and reversible.</span>
                    <span class="es-wire-spec-k">Delete it</span>
                    <span class="es-wire-spec-v es-wire-muted">Gone. Except where the event has ticket sales or a running ad boost, in which case it is hidden instead.</span>
                </div>
                <p class="es-wire-muted mt-5 text-sm">
                    <a href="{{ marketing_url('/features/calendar-sync') }}" class="es-wire-link font-semibold hover:underline">How calendar sync works</a>
                    is the longer version, and the
                    <a href="{{ route('marketing.docs.creating_schedules') }}#integrations" class="es-wire-link font-semibold hover:underline">user guide</a>
                    walks through the actual screens.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Money ports                                               -->
    <!-- ============================================================ -->
    <section id="money" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-wire-corner mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                <p class="es-wire-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Money ports</p>
                <h2 class="es-balance es-wire-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    The money never <span class="es-wire-accent">lands here.</span>
                </h2>
                <p class="es-wire-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Both payment ports terminate in an account you own, and both are open on the free plan, which sells twenty-five paid tickets a month per schedule before Pro takes the ceiling off. Event Schedule charges zero platform fees on ticket sales on every plan, which is only possible because it is not in the middle of the transaction.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3" data-reveal-group="100">
                <div class="es-wire-card flex flex-col p-7" data-reveal="panel">
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <span class="es-wire-port" aria-hidden="true">04</span>
                        <h3 class="es-wire-ink text-xl font-bold">Stripe</h3>
                        <span class="es-wire-plan">Free</span>
                    </div>
                    <p class="es-wire-muted mb-5 text-sm">Checkout runs on Stripe's own page. On the hosted platform the charge is created on your connected account, so payouts arrive on your Stripe schedule, and the confirmation returns to us as a signed webhook.</p>
                    <div class="es-wire-spec mb-5">
                        <span class="es-wire-spec-k">Fees</span>
                        <span class="es-wire-spec-v es-wire-muted">Stripe's processing fee. Nothing from us.</span>
                        <span class="es-wire-spec-k">Methods</span>
                        <span class="es-wire-spec-v es-wire-muted">Whatever you have enabled in your own Stripe dashboard.</span>
                        <span class="es-wire-spec-k">Selfhost</span>
                        <span class="es-wire-spec-v es-wire-muted">Your own Stripe keys, used directly.</span>
                    </div>
                    <a href="{{ marketing_url('/stripe') }}" class="es-wire-link mt-auto inline-flex items-center gap-1 text-sm font-semibold hover:underline">
                        Stripe payments
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                </div>

                <div class="es-wire-card flex flex-col p-7" data-reveal="panel">
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <span class="es-wire-port" aria-hidden="true">05</span>
                        <h3 class="es-wire-ink text-xl font-bold">Invoice Ninja</h3>
                        <span class="es-wire-plan">Free</span>
                    </div>
                    <p class="es-wire-muted mb-5 text-sm">Point Event Schedule at your Invoice Ninja install with an API token and URL. Useful when the buyer is an organisation that needs a document rather than a receipt.</p>
                    <div class="es-wire-spec mb-5">
                        <span class="es-wire-spec-k">Invoice mode</span>
                        <span class="es-wire-spec-v es-wire-muted">An invoice is raised per sale and the buyer pays it.</span>
                        <span class="es-wire-spec-k">Pay-link mode</span>
                        <span class="es-wire-spec-v es-wire-muted">The buyer goes to an Invoice Ninja payment link and chooses quantities there.</span>
                        <span class="es-wire-spec-k">Back to us</span>
                        <span class="es-wire-spec-v es-wire-muted">A webhook marks the sale paid when the payment clears.</span>
                    </div>
                    <a href="{{ marketing_url('/invoiceninja') }}" class="es-wire-link mt-auto inline-flex items-center gap-1 text-sm font-semibold hover:underline">
                        Invoice Ninja invoicing
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                </div>

                <div class="es-wire-card es-wire-open flex flex-col p-7" data-reveal="panel">
                    <div class="mb-4 flex flex-wrap items-center gap-3">
                        <span class="es-wire-stub" aria-hidden="true"></span>
                        <h3 class="es-wire-ink text-xl font-bold">Not a wire</h3>
                    </div>
                    <p class="es-wire-muted mb-5 text-sm">Two of the payment options are deliberately not integrations, and it is better that you know which.</p>
                    <div class="es-wire-spec">
                        <span class="es-wire-spec-k">Cash</span>
                        <span class="es-wire-spec-v es-wire-muted">The sale is recorded with your own payment instructions attached. Nothing is called, nothing confirms itself.</span>
                        <span class="es-wire-spec-k">Payment URL</span>
                        <span class="es-wire-spec-v es-wire-muted">Send buyers to any link you already use. It is a signpost, not a connection, so you reconcile it yourself.</span>
                    </div>
                    <p class="es-wire-muted mt-auto pt-5 text-sm">Free registration with a capacity limit needs no payment port at all, and works on every plan.</p>
                </div>
            </div>

            <div class="es-wire-card mx-auto mt-6 max-w-4xl p-7" data-reveal="panel">
                <div class="mb-4 flex flex-wrap items-center gap-2">
                    <span class="es-wire-port" aria-hidden="true">08</span>
                    <h3 class="es-wire-ink text-lg font-bold">If the money currently lands at Eventbrite</h3>
                    <span class="es-wire-dir">in</span>
                    <span class="es-wire-plan es-wire-plan-pro">Pro</span>
                </div>
                <p class="es-wire-muted mb-5 text-sm">
                    There is one import port, and it points at Eventbrite. It runs when you press the button, not on a timer, so it is a move rather than a running sync.
                </p>
                <div class="es-wire-spec">
                    <span class="es-wire-spec-k">Credential</span>
                    <span class="es-wire-spec-v es-wire-muted">An Eventbrite private token you paste in. It is used for that import and never stored.</span>
                    <span class="es-wire-spec-k">What comes</span>
                    <span class="es-wire-spec-v es-wire-muted">The events you tick, with their venues, images and ticket types, prices and quantities.</span>
                    <span class="es-wire-spec-k">What does not</span>
                    <span class="es-wire-spec-v es-wire-muted">Past orders and attendees. Sold tickets stay where they were sold, so run the two in parallel until the last one is scanned.</span>
                </div>
                <p class="es-wire-muted mt-5 text-sm">
                    <a href="{{ marketing_url('/eventbrite-alternative') }}" class="es-wire-link font-semibold hover:underline">Event Schedule compared with Eventbrite</a>
                    goes through the rest of the move.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. The terminal block (fixed-dark band)                      -->
    <!-- ============================================================ -->
    <section id="block" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-wire-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 28% 28%, rgba(52, 211, 153, 0.12), rgba(52, 211, 153, 0) 60%); opacity: 0.5;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-wire-corner mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                    <p class="es-wire-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The terminal block</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Two spare terminals, <span class="es-wire-lit">for wires we did not build.</span>
                    </h2>
                    <p class="mt-5 text-lg es-wire-onband-muted" data-reveal style="--reveal-delay: 0.15s;">
                        There is no Zapier app. Instead there is one wire out and one wire in, both general purpose, both documented, both on the Pro plan.
                    </p>
                </div>

                <div class="grid gap-6 lg:grid-cols-2" data-reveal-group="110">
                    <div class="es-wire-card p-7" data-reveal="panel">
                        <div class="mb-4 flex flex-wrap items-center gap-2">
                            <span class="es-wire-port" aria-hidden="true">06</span>
                            <span class="es-wire-dir">out</span>
                            <h3 class="text-xl font-bold es-wire-onband">Webhooks</h3>
                            <span class="es-wire-plan es-wire-plan-pro">Pro</span>
                        </div>
                        <p class="mb-5 text-sm es-wire-onband-muted">Fourteen event types, POSTed as JSON to a URL you own. Subscribe to all of them or pick the ones you care about.</p>
                        <div class="mb-6 flex flex-wrap gap-1.5" aria-hidden="true">
                            @foreach ($hookTypes as $hookType)
                                <span class="es-wire-slug">{{ $hookType }}</span>
                            @endforeach
                        </div>
                        <div class="es-wire-spec">
                            <span class="es-wire-spec-k">Signature</span>
                            <span class="es-wire-spec-v es-wire-onband-muted">HMAC-SHA256 of the body in an <span class="es-wire-mono">X-Webhook-Signature</span> header, keyed on a secret you can regenerate.</span>
                            <span class="es-wire-spec-k">Retries</span>
                            <span class="es-wire-spec-v es-wire-onband-muted">Five-second timeout, three attempts, backing off thirty seconds then sixty.</span>
                            <span class="es-wire-spec-k">Log</span>
                            <span class="es-wire-spec-v es-wire-onband-muted">Every attempt kept with its status, response and duration, and a test button to fire one on demand.</span>
                            <span class="es-wire-spec-k">Safety</span>
                            <span class="es-wire-spec-v es-wire-onband-muted">Private and reserved addresses are refused and redirects are not followed, so a webhook cannot be aimed inside the server.</span>
                        </div>
                        <a href="{{ route('marketing.docs.developer.webhooks') }}" class="es-wire-lit mt-6 inline-flex items-center gap-1 text-sm font-semibold transition-all hover:gap-2">
                            Webhook reference
                            <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    </div>

                    <div class="es-wire-card p-7" data-reveal="panel">
                        <div class="mb-4 flex flex-wrap items-center gap-2">
                            <span class="es-wire-port" aria-hidden="true">07</span>
                            <span class="es-wire-dir">in</span>
                            <h3 class="text-xl font-bold es-wire-onband">REST API</h3>
                            <span class="es-wire-plan es-wire-plan-pro">Pro</span>
                        </div>
                        <p class="mb-5 text-sm es-wire-onband-muted">Create, read, update and delete schedules, sub-schedules, events and sales from your own code, plus read endpoints for post-event feedback and fan content.</p>
                        <div class="mb-6 flex flex-wrap gap-1.5" aria-hidden="true">
                            @foreach ($apiPaths as $apiPath)
                                <span class="es-wire-slug">{{ $apiPath }}</span>
                            @endforeach
                        </div>
                        <div class="es-wire-spec">
                            <span class="es-wire-spec-k">Auth</span>
                            <span class="es-wire-spec-v es-wire-onband-muted">A key in an <span class="es-wire-mono">X-API-Key</span> header. Only a hash of it is stored, and you can give the key an expiry date.</span>
                            <span class="es-wire-spec-k">Rate</span>
                            <span class="es-wire-spec-v es-wire-onband-muted">Three hundred reads and thirty writes a minute, counted per address.</span>
                            <span class="es-wire-spec-k">Shape</span>
                            <span class="es-wire-spec-v es-wire-onband-muted">JSON in, JSON out, with the same encoded identifiers the app's own URLs use.</span>
                            <span class="es-wire-spec-k">Pairs with</span>
                            <span class="es-wire-spec-v es-wire-onband-muted">The webhook opposite: it tells you something happened, this reads or changes it.</span>
                        </div>
                        <a href="{{ route('marketing.docs.developer.api') }}" class="es-wire-lit mt-6 inline-flex items-center gap-1 text-sm font-semibold transition-all hover:gap-2">
                            API reference
                            <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Operator ports                                            -->
    <!-- ============================================================ -->
    <section id="edges" class="scroll-mt-24 es-wire-rule border-t py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-wire-corner mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                <p class="es-wire-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Operator ports</p>
                <h2 class="es-balance es-wire-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Four ports the site operator <span class="es-wire-accent">holds the key to.</span>
                </h2>
                <p class="es-wire-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    These four need a credential that belongs to whoever runs the Event Schedule site, not to your schedule. If the panel for one of them is not there, that is why.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2" data-reveal-group="100">
                <div class="es-wire-card flex flex-col p-7" data-reveal="panel">
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <span class="es-wire-port" aria-hidden="true">09</span>
                        <span class="es-wire-dir">out</span>
                        <h3 class="es-wire-ink text-xl font-bold">Web push</h3>
                        <span class="es-wire-plan es-wire-plan-pro">Pro</span>
                    </div>
                    <p class="es-wire-muted mb-4 text-sm">The notification emails you already receive, repeated as browser and mobile push through OneSignal. On top of email, not instead of it.</p>
                    <ul class="es-wire-muted mt-auto space-y-2 text-sm">
                        <li class="es-wire-li">Per device: enable it on the phone and the laptop separately</li>
                        <li class="es-wire-li">Opt-in, and off until you turn it on</li>
                        <li class="es-wire-li">On iPhone and iPad, the site has to be on the home screen first</li>
                    </ul>
                </div>

                <div class="es-wire-card flex flex-col p-7" data-reveal="panel">
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <span class="es-wire-port" aria-hidden="true">10</span>
                        <span class="es-wire-dir">out</span>
                        <h3 class="es-wire-ink text-xl font-bold">Accommodation map</h3>
                        <span class="es-wire-plan">Free</span>
                    </div>
                    <p class="es-wire-muted mb-4 text-sm">A Stay22 map of lodging near the venue on your public event pages, with the dates taken from the occurrence being viewed. Free on every plan, off by default.</p>
                    <ul class="es-wire-muted mt-auto space-y-2 text-sm">
                        <li class="es-wire-li">Nothing is requested until the visitor consents or clicks</li>
                        <li class="es-wire-li">Suppressed for past events, embeds and password-gated pages</li>
                        <li class="es-wire-li">Add your own affiliate ID and the commission is yours</li>
                    </ul>
                </div>

                <div class="es-wire-card flex flex-col p-7" data-reveal="panel">
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <span class="es-wire-port" aria-hidden="true">11</span>
                        <span class="es-wire-dir">out</span>
                        <h3 class="es-wire-ink text-xl font-bold">Facebook and Instagram ads</h3>
                        <span class="es-wire-plan es-wire-plan-pro">Pro</span>
                    </div>
                    <p class="es-wire-muted mb-4 text-sm">Boosting turns an event into a Meta campaign. The ads run on the ad account belonging to whoever operates the site, so there is no Meta login for you to connect and no ad manager to learn.</p>
                    <ul class="es-wire-muted space-y-2 text-sm">
                        <li class="es-wire-li">Facebook and Instagram feeds, Stories and Reels</li>
                        <li class="es-wire-li">Budget by the day or for the whole run, with your own audience</li>
                        <li class="es-wire-li">The boost is paid for here rather than to Meta</li>
                    </ul>
                    <p class="es-wire-muted mt-auto pt-5 text-sm">
                        <a href="{{ marketing_url('/features/boost') }}" class="es-wire-link font-semibold hover:underline">Event boosting</a>
                    </p>
                </div>

                <div class="es-wire-card flex flex-col p-7" data-reveal="panel">
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <span class="es-wire-port" aria-hidden="true">12</span>
                        <span class="es-wire-dir">in</span>
                        <h3 class="es-wire-ink text-xl font-bold">WhatsApp</h3>
                        <span class="es-wire-plan es-wire-plan-pro">Ent</span>
                    </div>
                    <p class="es-wire-muted mb-4 text-sm">The one port where a message becomes an event. Send text or a photo of a flyer to the site's WhatsApp number and the event is created on your default schedule, with a reply carrying its link.</p>
                    <ul class="es-wire-muted space-y-2 text-sm">
                        <li class="es-wire-li">Runs over the operator's Twilio account, on the Enterprise plan</li>
                        <li class="es-wire-li">Only a phone number verified on your own account can create anything</li>
                        <li class="es-wire-li">The flyer you sent becomes the event's image</li>
                    </ul>
                    <p class="es-wire-muted mt-auto pt-5 text-sm">
                        <a href="{{ route('marketing.docs.creating_events') }}#whatsapp" class="es-wire-link font-semibold hover:underline">Creating events over WhatsApp</a>
                    </p>
                </div>
            </div>

            <div class="es-wire-card mx-auto mt-6 max-w-4xl p-7" data-reveal="panel">
                <p class="es-wire-tag mb-4">Or hold the keys yourself</p>
                <h3 class="es-wire-ink mb-3 text-lg font-bold">Selfhosting? You are the operator</h3>
                <p class="es-wire-muted mb-4 text-sm">Run your own install and all four of those keys are yours to add, alongside your own Google, Microsoft and Stripe credentials. Every port in the register is on, because a selfhosted install resolves to the top tier. An install that is not eventschedule.com can also share its public events with the eventschedule.com listings, which an admin switches on and each schedule can opt out of.</p>
                <p class="es-wire-muted text-sm">
                    <a href="{{ route('marketing.docs.selfhost.installation') }}" class="es-wire-link font-semibold hover:underline">Selfhost installation guide</a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Unterminated: what is honestly not here                   -->
    <!-- ============================================================ -->
    <section id="open" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-wire-corner mb-6" data-reveal aria-hidden="true"><span>08</span></div>
                <p class="es-wire-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Unterminated</p>
                <h2 class="es-balance es-wire-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Ports that <span class="es-wire-accent">are not there.</span>
                </h2>
                <p class="es-wire-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    You came here to find out whether this fits your stack. That answer is worth as much when it is no, so here are the four gaps people hit.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2" data-reveal-group="90">
                <div class="es-wire-card es-wire-open p-7" data-reveal="panel">
                    <div class="mb-4 flex items-center gap-3">
                        <span class="es-wire-stub" aria-hidden="true"></span>
                        <h3 class="es-wire-ink text-lg font-bold">No streaming-platform integrations</h3>
                    </div>
                    <p class="es-wire-muted text-sm">
                        An online event carries one link field. Mark the event as online and paste the URL to wherever you are streaming, whether that is Zoom, Meet, YouTube or something else entirely. Nothing is created for you and nothing is read back. The single exception is Outlook: turn on Teams meetings and the join link is written into that same field for events with no venue.
                    </p>
                </div>

                <div class="es-wire-card es-wire-open p-7" data-reveal="panel">
                    <div class="mb-4 flex items-center gap-3">
                        <span class="es-wire-stub" aria-hidden="true"></span>
                        <h3 class="es-wire-ink text-lg font-bold">No Zapier or Make app</h3>
                    </div>
                    <p class="es-wire-muted text-sm">
                        There is no listing in either directory. The general-purpose route is the terminal block above: a signed webhook out on fourteen event types, and the REST API in. Both accept a plain HTTP client, so an automation tool that can POST and receive JSON can be wired up without a connector.
                    </p>
                </div>

                <div class="es-wire-card es-wire-open p-7" data-reveal="panel">
                    <div class="mb-4 flex items-center gap-3">
                        <span class="es-wire-stub" aria-hidden="true"></span>
                        <h3 class="es-wire-ink text-lg font-bold">No accounting or CRM sync</h3>
                    </div>
                    <p class="es-wire-muted text-sm">
                        Invoice Ninja is the only invoicing wire, and there is nothing that pushes sales into a ledger or contacts into a CRM. What exists instead is a Pro sales export to CSV, including the answers to any custom questions, plus the sale webhooks if you would rather have the data arrive as it happens.
                    </p>
                </div>

                <div class="es-wire-card es-wire-open p-7" data-reveal="panel">
                    <div class="mb-4 flex items-center gap-3">
                        <span class="es-wire-stub" aria-hidden="true"></span>
                        <h3 class="es-wire-ink text-lg font-bold">No conflict detection</h3>
                    </div>
                    <p class="es-wire-muted text-sm">
                        Two-way sync copies events between calendars. It does not compare them, and nothing on this page will warn you that you have accepted two things at the same hour. If a clash matters, the calendar you already read every morning is still the place you will spot it.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Key features                                              -->
    <!-- ============================================================ -->
    <section class="es-wire-rule border-t py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-wire-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Calendar Sync" description="Two-way sync with Google, Outlook and any CalDAV server" :url="marketing_url('/features/calendar-sync')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="Sell through your own Stripe account with zero platform fees" :url="marketing_url('/features/ticketing')" icon-color="teal">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Online Events" description="One link field, pointed at wherever you are streaming" :url="marketing_url('/features/online-events')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Embed Calendar" description="Put the schedule on the website you already have" :url="marketing_url('/features/embed-calendar')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-wire-link inline-flex items-center font-medium hover:underline">
                    See all features
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    @include('marketing.partials.pricing-nudge')

    <!-- ============================================================ -->
    <!-- 10. Related pages                                            -->
    <!-- ============================================================ -->
    <section class="es-wire-rule border-t py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-wire-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3" data-reveal-group="70">
                @foreach ([['/google-calendar', 'Google Calendar'], ['/outlook-calendar', 'Outlook Calendar'], ['/caldav', 'CalDAV'], ['/stripe', 'Stripe'], ['/invoiceninja', 'Invoice Ninja'], ['/features/calendar-sync', 'Calendar Sync']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" class="es-wire-hover es-wire-card group flex flex-col p-5 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-wire-hover-title es-wire-ink mb-3 text-sm font-semibold transition-colors">{{ $relName }}</span>
                        <span class="es-wire-hover-arrow es-wire-muted mt-auto inline-flex items-center gap-1 text-xs font-medium transition-colors">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 11. FAQ                                                      -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="es-wire-corner mb-6" data-reveal aria-hidden="true"><span>09</span></div>
                <h2 class="es-balance es-wire-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-wire-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    What people ask before they wire anything up.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-wire-hover es-wire-card group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-wire-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-wire-accent es-wire-mono flex-none text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-wire-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-wire-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-wire-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 12. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-wire-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>
                <div class="relative z-10">
                    <p class="es-wire-tag mb-4">Free forever</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Pick a port. <span class="es-wire-lit">Wire it up.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg es-wire-onband-muted">
                        Calendar sync, the accommodation map and your first twenty-five ticket sales a month are free forever. {{ plan_price($proMonthly) }} a month takes the ticket ceiling off and adds webhooks, the API, push and the imports. Nothing is taken from the door on any plan.
                    </p>

                    {{-- The last port on the panel is the reader's own, and it is the only
                         one still dashed. The field below is what terminates it. --}}
                    <div class="es-wire-bezel mx-auto mb-6 max-w-sm text-start" aria-hidden="true">
                        <span class="es-wire-screw" style="top: 0.5rem; left: 0.5rem;"></span>
                        <span class="es-wire-screw" style="top: 0.5rem; right: 0.5rem;"></span>
                        <span class="es-wire-screw" style="bottom: 0.5rem; left: 0.5rem;"></span>
                        <span class="es-wire-screw" style="bottom: 0.5rem; right: 0.5rem;"></span>
                        <div class="mb-1.5 flex items-baseline justify-between gap-3 px-1">
                            <p class="es-wire-tag">Port 13</p>
                            <p class="es-wire-dir">open</p>
                        </div>
                        <div class="es-wire-bar mb-1"></div>
                        <div class="es-wire-row px-1">
                            <span class="es-wire-stub"></span>
                            <span class="min-w-0">
                                <span class="es-wire-name">Your schedule</span>
                                <span class="es-wire-sub es-wire-mono">nothing terminated yet</span>
                            </span>
                            <span class="es-wire-track es-wire-track-open w-16"></span>
                        </div>
                    </div>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm es-wire-onband-muted sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-wire-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Get started free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="mt-6 text-sm es-wire-onband-muted">No credit card required</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Desktop dot nav -->
    <nav class="es-dotnav fixed top-1/2 z-40 hidden -translate-y-1/2 lg:block ltr:right-5 rtl:left-5" aria-label="Page sections">
        <ul class="glass flex flex-col items-center gap-1.5 rounded-full px-2 py-3">
            @foreach ($dotSections as [$sectionId, $sectionLabel])
                <li class="relative">
                    <a href="#{{ $sectionId }}" class="es-dot group block rounded-full" aria-label="{{ $sectionLabel }}">
                        <span class="es-dot-pip block h-2 w-2 rounded-full bg-gray-400/60 dark:bg-white/30"></span>
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-medium text-gray-700 opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3 es-wire-tip dark:border-white/10 dark:text-gray-300">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <x-marketing.related-pages />

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
