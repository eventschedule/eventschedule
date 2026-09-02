<x-marketing-layout>
    <x-slot name="title">Embed Tickets on Any Website - Event Schedule</x-slot>
    <x-slot name="description">Put the ticket checkout on your own website with one iframe tag. Ticket types, custom questions, promo codes and the total, all inside the frame.</x-slot>
    <x-slot name="breadcrumbTitle">Embed Tickets</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule Embed Tickets",
        "description": "Embed a ticket purchase or RSVP form on any website with one iframe tag. Supports every payment method, dark mode, and 12 languages.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Embeddable Ticket Widget"
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Embed Tickets",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Website Integration Software",
        "operatingSystem": "Web",
        "description": "Put the whole ticket checkout on your own website with one iframe tag: ticket types, buyer details, custom questions, promo codes, gift cards and payment.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Included with the Pro plan"
        },
        "featureList": [
            "One iframe tag, no script and no dependency",
            "Every ticket type, add-on and pass on sale for that date",
            "Buyer details, per-attendee details, and your own custom questions",
            "Promo codes, with a code pre-fillable from the embed URL",
            "Stripe, Invoice Ninja, custom payment URL, and cash or at the door",
            "Zero platform fees on ticket sales",
            "RSVP and registration mode for events that take no payment",
            "Light or dark, following the visitor's own system setting",
            "12 interface languages, including right-to-left layout",
            "Served noindex, and never carrying ads"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to embed a ticket form on your own website",
        "description": "Turn on tickets or registration, copy the iframe tag out of the event, and paste it into your page.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Put something on sale",
                "text": "Open the event in the admin portal, go to the Tickets section, and switch on tickets or registration. Add at least one ticket type."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Copy the tag",
                "text": "Click Embed Tickets, or Embed Registration on an RSVP event. The panel gives you the embed URL, the ready-made iframe tag, and a live preview of the widget."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Paste it into your page",
                "text": "Drop the tag into your website's HTML wherever the form belongs. Any block that accepts raw HTML will take it. Nothing needs installing."
            }
        ]
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
           Embed-tickets "The Widget" styles.

           THE CONCEPT. The nickname was already right and is kept: the
           thing being sold here is a WIDGET in the engineering sense -
           one manufactured part, with a documented interface, that you
           bolt onto a machine you already own. So the page is that
           part's DATASHEET: the part drawn to size with dimension
           lines, an exploded view with numbered callouts naming every
           real layer, a pin-out table of the URL parameters, the two
           finishes it ships in, and its operating conditions. The
           metaphor and the product argument are one sentence: a part
           has a fixed published interface, so you fit it once and it
           keeps working.

           DEVICES THIS PAGE MUST NOT BUILD. /features/embed-calendar
           was just rebuilt as "The Paste" and owns the transplant
           reading: a slip of copied text, a marching-ants selection
           border, and a host document with a rectangle reserved in the
           middle of it. None of that appears here, because this page is
           not about the journey of a snippet - it is about the part
           itself, seen from the inside. /for-ai-agents owns "The
           Console" (request/response ledger, block cursor), /selfhost
           owns "The Terminal" (window chrome), /open-source owns "The
           Commit Log" (spine, unified diff). So: no terminal, no diff,
           no fake browser bar.

           THE SIGNATURE SHAPE is a DIMENSION LINE - a hairline with end
           ticks and a monospace label - which is how a datasheet says
           "this is exactly how big the part is". It measures the widget
           in the hero and rules under every section heading. It is an
           abstract stroke, not an outline illustration of an object.

           FIXED PHYSICAL OBJECTS. The widget genuinely renders light or
           dark from the visitor's own system setting, so the hero
           instance FOLLOWS the colour mode on purpose. The two
           instances in section 03 are the opposite: they are the two
           finishes shown side by side, so .es-widg-lit and
           .es-widg-drk are pinned and must render identically with
           .dark on and off, as must .es-widg-band. Verify with
           --bands=.es-widg-band,.es-widg-lit,.es-widg-drk (expect 0
           diffs). Every mock colour is driven by --w-* custom
           properties set on the container, which is what makes pinning
           an instance a two-line job instead of thirty.

           COLOUR. The hue family stays what this page already had -
           blue - because this page is the product's own component and
           blue is the product's own colour. It is spent differently
           from every blue neighbour: NOT the shared brand ramp
           #4E81FA -> #0EA5E9 -> #22D3EE (that is site chrome, never a
           page accent), NOT gradient heading text at all, and NOT "The
           Paste"'s selection highlight #c9dcff / #1c4b96. Here the
           accent is a single flat SIGNAL blue used for rules, callout
           numbers and the pay button - the ink a technical drawing is
           printed in. Deep and desaturated where "The Paste" is bright
           and cool.

           MEASURED (probe, not guessed):
             #14181d on ground #f1f2f4 . . 15.91
             #4c545e on ground #f1f2f4 . .  6.85   (muted; NEVER
                                                    text-gray-500,
                                                    which is 4.2-4.4 on
                                                    a tinted ground)
             #11429b on ground #f1f2f4 . .  8.24
             #11429b on card #ffffff . . .  9.22
             #ffffff on accent #11429b . .  9.22
             #e8eaee on dark ground #0c0e11 16.05
             #9aa3ae on dark ground #0c0e11  7.57
             #8fb6f5 on dark ground #0c0e11  9.37
             #9aa3ae on band #0e1116 . . .   7.41
             #8fb6f5 on band #0e1116 . . .   9.17
             #5b6470 on pinned-light #ffffff 6.00
             #a5adb8 on pinned-dark #252526  6.76

           NO ARBITRARY-VALUE TAILWIND for anything design-critical: the
           build is not run during this campaign, so a class that is not
           already in the built marketing CSS silently does nothing.
           Every colour and material below is a real rule.
           ============================================================== */

        /* --- Ground and ink ------------------------------------------- */
        .es-widg-page { background-color: #f1f2f4; color: #14181d; }
        .dark .es-widg-page { background-color: #0c0e11; color: #e8eaee; }
        .es-widg-ink { color: #14181d; }
        .dark .es-widg-ink { color: #e8eaee; }
        .es-widg-muted { color: #4c545e; }
        .dark .es-widg-muted { color: #9aa3ae; }
        .es-widg-accent { color: #11429b; }
        .dark .es-widg-accent { color: #8fb6f5; }
        .es-widg-mono {
            font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace;
            font-variant-numeric: tabular-nums;
        }

        /* --- THE SIGNATURE: a dimension line. Hairline, end ticks, a
               monospace label above it. It measures the part in the
               hero and rules under every section heading. ------------ */
        .es-widg-measure {
            position: relative;
            height: 1px;
            background-color: rgba(20, 24, 29, 0.3);
        }
        .dark .es-widg-measure { background-color: rgba(232, 234, 238, 0.32); }
        .es-widg-band .es-widg-measure { background-color: rgba(232, 234, 238, 0.32); }
        .es-widg-measure::before,
        .es-widg-measure::after {
            content: "";
            position: absolute;
            top: -4px;
            width: 1px;
            height: 9px;
            background-color: inherit;
        }
        .es-widg-measure::before { left: 0; }
        .es-widg-measure::after { right: 0; }
        .es-widg-measure-label {
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #11429b;
        }
        .dark .es-widg-measure-label { color: #8fb6f5; }
        /* The fixed-dark band never flips, so the label needs the dark ink there in
           BOTH modes - otherwise the light value (#11429b, 1.7:1 on #0e1116) paints
           on a dark ground and the band stops rendering identically. Same shape as
           the .es-widg-band .es-widg-eyebrow pin above. */
        .es-widg-band .es-widg-measure-label { color: #8fb6f5; }

        /* Vertical twin, for the height of the part. */
        .es-widg-measure-v {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 1px;
            background-color: rgba(20, 24, 29, 0.3);
        }
        .dark .es-widg-measure-v { background-color: rgba(232, 234, 238, 0.32); }
        .es-widg-measure-v::before,
        .es-widg-measure-v::after {
            content: "";
            position: absolute;
            left: -4px;
            height: 1px;
            width: 9px;
            background-color: inherit;
        }
        .es-widg-measure-v::before { top: 0; }
        .es-widg-measure-v::after { bottom: 0; }
        .es-widg-measure-vlabel {
            position: absolute;
            top: 50%;
            left: 0.5rem;
            transform: translateY(-50%);
            writing-mode: vertical-rl;
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #11429b;
            white-space: nowrap;
        }
        .dark .es-widg-measure-vlabel { color: #8fb6f5; }

        /* --- Datasheet furniture -------------------------------------- */
        .es-widg-eyebrow {
            font-size: 0.66rem;
            font-weight: 700;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: #11429b;
        }
        .dark .es-widg-eyebrow { color: #8fb6f5; }
        .es-widg-band .es-widg-eyebrow { color: #8fb6f5; }

        /* Callout number: a squared badge, the way a drawing numbers a part. */
        .es-widg-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: none;
            width: 2rem;
            height: 2rem;
            border: 1px solid rgba(17, 66, 155, 0.4);
            border-radius: 0.3rem;
            background-color: rgba(17, 66, 155, 0.07);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.72rem;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            color: #11429b;
        }
        .dark .es-widg-num {
            border-color: rgba(143, 182, 245, 0.4);
            background-color: rgba(143, 182, 245, 0.1);
            color: #8fb6f5;
        }

        /* Leader line from a callout number to the layer it names. */
        .es-widg-lead {
            display: block;
            flex: none;
            width: 0.9rem;
            height: 1px;
            background-color: rgba(17, 66, 155, 0.45);
            transform-origin: left center;
            transition: transform 0.8s cubic-bezier(0.22, 1, 0.36, 1);
            transition-delay: var(--ld, 0.15s);
        }
        .dark .es-widg-lead { background-color: rgba(143, 182, 245, 0.45); }
        @media (min-width: 640px) { .es-widg-lead { width: 1.6rem; } }
        html.es-anim [data-reveal]:not(.is-revealed) .es-widg-lead { transform: scaleX(0); }

        /* Section separator. A real rule rather than
           `dark:border-[rgba(232,234,238,0.1)]`, which is absent from the built
           marketing CSS and left every separator painting the LIGHT value on a
           dark ground, where it is invisible. */
        .es-widg-rule-t { border-top: 1px solid rgba(20, 24, 29, 0.1); }
        .dark .es-widg-rule-t { border-top-color: rgba(232, 234, 238, 0.12); }

        /* --- Surfaces -------------------------------------------------- */
        .es-widg-card {
            background-color: #ffffff;
            border: 1px solid rgba(20, 24, 29, 0.12);
            border-radius: 0.85rem;
        }
        .dark .es-widg-card {
            background-color: #15181d;
            border-color: rgba(232, 234, 238, 0.13);
        }
        .es-widg-hover { transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        .es-widg-hover:hover {
            border-color: rgba(17, 66, 155, 0.45);
            box-shadow: 0 12px 30px -20px rgba(9, 20, 45, 0.6);
        }
        .dark .es-widg-hover:hover {
            border-color: rgba(143, 182, 245, 0.4);
            box-shadow: 0 12px 30px -20px rgba(0, 0, 0, 0.85);
        }

        /* --- THE PART ------------------------------------------------
           Every colour inside the widget mock reads a --w-* custom
           property off the container, so an instance can be PINNED to
           one finish by overriding four variables instead of rewriting
           every child rule. The hero instance follows the colour mode
           (that is the real behaviour); .es-widg-lit and .es-widg-drk
           do not, because section 03 shows both finishes at once. */
        .es-widg-part {
            --w-bg: #ffffff;
            --w-ink: #14181d;
            --w-muted: #5b6470;
            --w-line: rgba(20, 24, 29, 0.13);
            --w-sub: rgba(20, 24, 29, 0.05);
            background-color: var(--w-bg);
            border: 1px solid var(--w-line);
            border-radius: 0.75rem;
            overflow: hidden;
            color: var(--w-ink);
        }
        .dark .es-widg-part {
            --w-bg: #252526;
            --w-ink: #e8eaee;
            --w-muted: #a5adb8;
            --w-line: rgba(232, 234, 238, 0.14);
            --w-sub: rgba(232, 234, 238, 0.07);
        }
        .es-widg-part.es-widg-lit,
        .dark .es-widg-part.es-widg-lit {
            --w-bg: #ffffff;
            --w-ink: #14181d;
            --w-muted: #5b6470;
            --w-line: rgba(20, 24, 29, 0.13);
            --w-sub: rgba(20, 24, 29, 0.05);
        }
        .es-widg-part.es-widg-drk,
        .dark .es-widg-part.es-widg-drk {
            --w-bg: #252526;
            --w-ink: #e8eaee;
            --w-muted: #a5adb8;
            --w-line: rgba(232, 234, 238, 0.14);
            --w-sub: rgba(232, 234, 238, 0.07);
        }

        .es-widg-bar { padding: 0.85rem 1.1rem; }
        .es-widg-bar-sub { opacity: 0.85; }

        .es-widg-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 0;
            border-bottom: 1px solid var(--w-line);
        }
        .es-widg-row:last-child { border-bottom: 0; }
        .es-widg-row-name { color: var(--w-ink); }
        .es-widg-row-note { color: var(--w-muted); }
        .es-widg-qty {
            flex: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2rem;
            padding: 0.15rem 0.4rem;
            border: 1px solid var(--w-line);
            border-radius: 0.3rem;
            background-color: var(--w-sub);
            color: var(--w-ink);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.72rem;
            font-variant-numeric: tabular-nums;
        }
        .es-widg-input {
            border: 1px solid var(--w-line);
            border-radius: 0.4rem;
            background-color: var(--w-sub);
            padding: 0.45rem 0.6rem;
            color: var(--w-muted);
        }
        .es-widg-input-label {
            display: block;
            color: var(--w-muted);
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }
        .es-widg-total {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 1rem;
            border-top: 1px solid var(--w-line);
            padding-top: 0.7rem;
            color: var(--w-ink);
        }
        .es-widg-pay {
            display: block;
            width: 100%;
            border-radius: 0.45rem;
            padding: 0.6rem 1rem;
            text-align: center;
            font-weight: 700;
            font-size: 0.85rem;
        }
        .es-widg-foot {
            border-top: 1px solid var(--w-line);
            padding: 0.5rem 1.1rem;
            text-align: end;
            color: var(--w-muted);
            font-size: 0.65rem;
        }

        /* Exploded view: one stratum of the part, lifted off the stack. */
        .es-widg-slab {
            background-color: var(--w-bg, #ffffff);
            border: 1px solid var(--w-line);
            border-radius: 0.5rem;
            overflow: hidden;
        }

        /* --- Pin-out table -------------------------------------------- */
        .es-widg-table { width: 100%; border-collapse: collapse; text-align: start; }
        .es-widg-table th {
            text-align: start;
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #4c545e;
            padding: 0 0.9rem 0.6rem 0;
            border-bottom: 1px solid rgba(20, 24, 29, 0.16);
            white-space: nowrap;
        }
        .dark .es-widg-table th { color: #9aa3ae; border-bottom-color: rgba(232, 234, 238, 0.18); }
        .es-widg-table td {
            padding: 0.7rem 0.9rem 0.7rem 0;
            border-bottom: 1px solid rgba(20, 24, 29, 0.08);
            vertical-align: top;
            color: #4c545e;
            font-size: 0.9rem;
        }
        .dark .es-widg-table td { color: #9aa3ae; border-bottom-color: rgba(232, 234, 238, 0.08); }
        .es-widg-table tr:last-child td { border-bottom: 0; }

        .es-widg-key {
            display: inline-block;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.78rem;
            font-weight: 600;
            border-radius: 0.3rem;
            padding: 0.1rem 0.35rem;
            background-color: rgba(17, 66, 155, 0.09);
            color: #11429b;
            white-space: nowrap;
        }
        .dark .es-widg-key { background-color: rgba(143, 182, 245, 0.14); color: #8fb6f5; }

        /* --- Plan pills ------------------------------------------------ */
        .es-widg-plan {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid transparent;
            padding: 0.1rem 0.5rem;
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        .es-widg-plan-free { border-color: rgba(20, 24, 29, 0.24); color: #4c545e; }
        .dark .es-widg-plan-free { border-color: rgba(232, 234, 238, 0.26); color: #9aa3ae; }
        .es-widg-plan-pro {
            border-color: rgba(17, 66, 155, 0.5);
            background-color: rgba(17, 66, 155, 0.08);
            color: #11429b;
        }
        .dark .es-widg-plan-pro {
            border-color: rgba(143, 182, 245, 0.45);
            background-color: rgba(143, 182, 245, 0.11);
            color: #8fb6f5;
        }

        /* --- Finish swatches ------------------------------------------ */
        .es-widg-swatch {
            border-radius: 0.45rem;
            padding: 0.55rem 0.7rem;
            font-size: 0.75rem;
            font-weight: 700;
            line-height: 1.2;
        }

        /* --- Buttons --------------------------------------------------- */
        .es-widg-btn {
            background-color: #11429b;
            color: #ffffff;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .es-widg-btn:hover {
            background-color: #0d3479;
            transform: translateY(-1px);
            box-shadow: 0 14px 28px -16px rgba(17, 66, 155, 0.95);
        }
        .es-widg-ghost {
            border: 1px solid rgba(20, 24, 29, 0.22);
            color: #14181d;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }
        .es-widg-ghost:hover { border-color: rgba(17, 66, 155, 0.5); background-color: rgba(17, 66, 155, 0.06); }
        .dark .es-widg-ghost { border-color: rgba(232, 234, 238, 0.24); color: #e8eaee; }
        .dark .es-widg-ghost:hover { border-color: rgba(143, 182, 245, 0.45); background-color: rgba(143, 182, 245, 0.09); }

        /* --- The fixed-dark band -------------------------------------
           A resolvable background-color under the gradient: it is what
           paints if the gradient fails and what a contrast audit reads. */
        .es-widg-band {
            background-color: #0e1116;
            background-image:
                radial-gradient(ellipse 75% 55% at 50% 0%, rgba(17, 66, 155, 0.42), rgba(17, 66, 155, 0) 70%),
                linear-gradient(180deg, #13171e, #0e1116);
        }
        .es-widg-bright { color: #f2f4f7; }
        .es-widg-dim { color: #9aa3ae; }
        .es-widg-glow { color: #8fb6f5; }

        /* Nothing inside the band may change between colour modes. The band
           has no .dark variant, so a descendant that HAS one would render
           differently on an identical ground. Three shared classes carry
           their own .dark rules in marketing.css and are invisible to a
           grep of this file, so they are pinned here, AFTER the base rules. */
        .es-widg-band .grid-overlay {
            background-image:
                linear-gradient(rgba(232, 234, 238, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(232, 234, 238, 0.05) 1px, transparent 1px);
        }
        .es-widg-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-widg-band .es-claim:focus-within {
            border-color: rgba(143, 182, 245, 0.75);
            box-shadow: 0 0 0 4px rgba(143, 182, 245, 0.22);
        }

        /* --- The one animation: a slow hum behind the hero part, so the
               drawing reads as powered rather than printed. ----------- */
        @keyframes es-widg-hum {
            0%, 100% { opacity: 0.42; transform: scale(1); }
            50% { opacity: 0.72; transform: scale(1.06); }
        }
        .es-widg-hum {
            position: absolute;
            border-radius: 999px;
            filter: blur(70px);
            background: radial-gradient(circle at 50% 50%, rgba(17, 66, 155, 0.5), rgba(17, 66, 155, 0) 68%);
            animation: es-widg-hum 9s ease-in-out infinite;
        }
        .dark .es-widg-hum {
            background: radial-gradient(circle at 50% 50%, rgba(143, 182, 245, 0.34), rgba(143, 182, 245, 0) 68%);
        }

        /* Dot-nav tooltip. Deliberately a real rule rather than
           `dark:bg-[#15181d]`: an arbitrary-value class that is not already in
           the built marketing CSS silently does nothing, which left nine
           tooltips as light-grey ink on a white pill in dark mode (1.47:1).
           Measured: #4c545e on #ffffff 7.67, #d1d5db on #15181d 12.07. */
        .es-widg-tip {
            background-color: #ffffff;
            border: 1px solid rgba(20, 24, 29, 0.14);
            color: #4c545e;
        }
        .dark .es-widg-tip {
            background-color: #15181d;
            border-color: rgba(232, 234, 238, 0.14);
            color: #d1d5db;
        }

        /* Shared chrome that is hard-coded brand blue. */
        .es-dot:hover .es-dot-pip { background-color: rgba(17, 66, 155, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(143, 182, 245, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #11429b; }
        .dark .es-dot.is-active .es-dot-pip { background: #8fb6f5; }

        /* Focus rings. Never set border-radius here: an outline already
           follows the element's own radius. */
        #es-widg-page a:focus-visible,
        #es-widg-page summary:focus-visible,
        #es-widg-page button:focus-visible,
        #es-widg-page input:focus-visible {
            outline: 2px solid #11429b;
            outline-offset: 2px;
        }
        .dark #es-widg-page a:focus-visible,
        .dark #es-widg-page summary:focus-visible,
        .dark #es-widg-page button:focus-visible,
        .dark #es-widg-page input:focus-visible {
            outline-color: #8fb6f5;
        }
        .es-widg-band a:focus-visible,
        .es-widg-band summary:focus-visible,
        .es-widg-band button:focus-visible,
        .es-widg-band input:focus-visible {
            outline-color: #8fb6f5 !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-widg-hum { animation: none !important; }
            .es-widg-lead { transform: none !important; transition: none !important; }
            .es-widg-btn:hover { transform: none; }
        }
    </style>

    @php
        // The demo part. One set of figures drives the hero mock, the exploded
        // view and the two finishes, so no two renders of the widget can
        // disagree with each other.
        $demo = [
            'event'   => 'Riverside Sessions',
            'when'    => 'Saturday, August 15',
            'time'    => '8:00 PM',
            'accent'  => '#11429b',
            'rows'    => [
                ['General admission', '$18', '2'],
                ['Supporter', '$35', '0'],
            ],
            'total'   => '$36',
        ];

        // The seven layers of the widget, in the order they render. Each one is
        // a real part of resources/views/event/show-guest-ticket-embed.blade.php
        // or of the purchase view it includes.
        $layers = [
            ['01', 'The header bar', 'Your schedule\'s accent colour, the event name, and the date and start time of the occurrence being sold. The name is a link out to the full event page, and it opens in the parent window rather than inside the frame.'],
            ['02', 'The ticket rows', 'Every ticket type on sale for that date, each with its own price and its own remaining count for that date. A multi-use pass is simply another type, add-ons appear once a ticket is chosen, and a type that has run out reads sold out. When every type has gone, the frame turns into a waitlist form instead of a dead end.'],
            ['03', 'Buyer details', 'A name and an email address. Switch on individual tickets and each attendee gets their own row, their own confirmation email and their own QR code.'],
            ['04', 'Your own questions', 'Custom fields you attached to the event or to a single ticket type, answered here at checkout instead of in a follow-up email thread.'],
            ['05', 'Codes', 'A promo code box, once the event has a live code, and a gift card box on schedules that sell them. A code can also arrive pre-filled from the embed URL, so a link in a newsletter carries its own discount.'],
            ['06', 'Total and pay', 'The running total, any discount applied, and the button that starts checkout on whichever payment method the event uses.'],
            ['07', 'The foot', 'A "Powered by Event Schedule" line, and the one layer most readers of this page will never meet: the widget needs Pro on the hosted platform, and a Pro schedule does not carry branding. A selfhosted install behaves like a paid plan throughout, so it does not carry it either. In practice the line shows on an operator who runs their own free tier, and nowhere else.'],
        ];

        // The published interface. Every row is in the docs and in the code that
        // reads it; nothing here is aspirational.
        $params = [
            ['tickets=true', 'Mode', 'Renders the ticket purchase form. This is what the Embed Tickets link hands you.'],
            ['rsvp=true', 'Mode', 'Renders the registration form instead, for an event that takes RSVPs rather than payments.'],
            ['embed=true', 'Required', 'Strips the schedule header, footer and banner, and is the flag that permits the page to be framed at all.'],
            ['dark=true', 'Optional', 'Forces the dark finish. Left off, the widget follows the visitor\'s own system setting.'],
            ['promo=CODE', 'Optional', 'Pre-fills the promo code box, so one link can carry its own discount.'],
            ['lang=xx', 'Optional', 'Sets the interface language. Twelve are supported, Arabic and Hebrew among them, and those lay the form out right to left.'],
        ];

        // Accent finishes. The ink is picked by the same rule the product uses:
        // ColorUtils::getContrastColor() returns black above a relative
        // luminance of 0.25 and white below it, so the label on the swatch is
        // exactly the label the real widget would draw.
        $luminance = function (string $hex): float {
            $h = ltrim($hex, '#');
            $out = 0.0;
            foreach ([[0, 0.2126], [2, 0.7152], [4, 0.0722]] as [$offset, $weight]) {
                $c = hexdec(substr($h, $offset, 2)) / 255;
                $c = $c <= 0.03928 ? $c / 12.92 : pow(($c + 0.055) / 1.055, 2.4);
                $out += $weight * $c;
            }
            return $out;
        };
        // Two accents below the threshold and two above it, so the row actually
        // DEMONSTRATES the rule rather than asserting it: the ink flips halfway
        // along, and each swatch prints which side of the line it landed on.
        $swatches = [];
        foreach (['#11429b', '#9f1239', '#f59e0b', '#34d399'] as $hex) {
            $isDarkInk = $luminance($hex) > 0.25;
            $swatches[] = [$hex, $isDarkInk ? '#000000' : '#ffffff', $isDarkInk ? 'black ink' : 'white ink'];
        }

        $conditions = [
            ['Plan', 'Pro or Enterprise', 'The ticket widget is a paid feature. The calendar embed, which puts your dates on a page rather than a checkout, is free on every plan.'],
            ['Event', 'Saved, and not unlisted', 'The link appears on an event that exists and is publicly reachable. An unlisted event is link-only and can carry a password, so it gets no widget.'],
            ['Mode', 'Tickets or registration on', 'One of the two switches has to be on. The link appears the moment it is, so add at least one ticket type before you paste the tag anywhere.'],
            ['Host page', 'Any HTML', 'One iframe tag. No script, no package, no build step, and nothing that needs updating when your events change.'],
        ];

        $faqs = [
            [
                'q' => 'How do I get the embed code?',
                'a' => 'Open the event in the admin portal and go to the Tickets section. Switch on tickets or registration, then click the Embed Tickets link next to the heading (it reads Embed Registration on an RSVP event). The panel gives you the embed URL, a ready-made iframe tag, and a live preview of the widget. Copy the tag into your website\'s HTML wherever the form belongs.',
            ],
            [
                'q' => 'Which payment methods work inside the widget?',
                'a' => 'All of them: Stripe, Invoice Ninja, a custom payment URL, and cash or at the door. Which window the checkout finishes in is decided by the event\'s payment method rather than by the amount. On cash it completes inside the frame. Stripe, Invoice Ninja and custom payment URL open in the parent window instead, because payment portals frequently refuse to be framed, and the buyer lands back on your page afterwards.',
            ],
            [
                'q' => 'Can I use it for free events and registrations?',
                'a' => 'Yes. If the event takes registrations rather than payments, the tag carries rsvp=true instead of tickets=true and the widget renders the registration form. RSVP with a capacity per date is free on every plan, though the widget that embeds it is on Pro.',
            ],
            [
                'q' => 'Does it follow dark mode?',
                'a' => 'Yes. The widget reads the visitor\'s own system setting, so it lands light on a light site and dark on a dark one. Add dark=true to the embed URL to force the dark finish whatever they have set.',
            ],
            [
                'q' => 'What does it cost, and what do you take from a sale?',
                'a' => 'The ticket widget is on the Pro plan. Event Schedule charges no platform fee on ticket sales at all: card processing runs through your own Stripe account, so what it charges is between you and Stripe. On a selfhosted install the widget is included at no extra cost.',
            ],
            [
                'q' => 'Will the framed page compete with my own page in search?',
                'a' => 'No. Every embed URL is served noindex, nofollow, so the framed page is never the one a search engine lists. There are no ads inside an embed either, on any plan, and framing is only permitted on the embed routes themselves.',
            ],
            [
                'q' => 'My event repeats every week. Which date does the tag sell?',
                'a' => 'The tag you copy points at the next occurrence that matches the event\'s days, worked out at the moment you copy it. Copy a fresh tag when you want the page to sell a later date.',
            ],
        ];

        $dotSections = [
            ['top', 'The part'],
            ['anatomy', 'The anatomy'],
            ['pinout', 'The pin-out'],
            ['finish', 'Finishes'],
            ['money', 'Payment'],
            ['limits', 'Conditions'],
            ['fit', 'Fitting it'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];
    @endphp

    <div id="es-widg-page" class="es-widg-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the part, drawn to size                             -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden pb-16 pt-28">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-widg-hum" style="width: 620px; height: 620px; right: -120px; top: 4%;"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_72%_62%_at_50%_38%,black_22%,transparent_74%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                <div>
                    <p class="es-widg-eyebrow es-fade-up es-d-1 mb-5">Embed tickets &middot; Pro plan</p>

                    <h1 class="es-balance mb-7 text-[2.6rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">The whole checkout,</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">as <span class="es-widg-accent">one part</span>.</span></span>
                    </h1>

                    <p class="es-widg-muted es-fade-up es-d-2 mb-9 max-w-xl text-lg sm:text-xl">
                        Not a link that takes people somewhere else. One iframe tag, and the ticket
                        types, the buyer details, your own questions, the promo code and the running
                        total all happen on the page you already own.
                    </p>

                    <div class="es-fade-up es-d-3 mb-8 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ app_url('/sign_up') }}" class="es-widg-btn inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            Start for free
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                        <a href="#anatomy" class="es-widg-ghost inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            See what is inside it
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                    </div>

                    <div class="es-fade-up es-d-4 max-w-xl">
                        <p class="es-widg-measure-label mb-1.5">The whole integration</p>
                        <div class="es-widg-measure mb-3"></div>
                        <p class="es-widg-mono es-widg-muted break-all text-xs leading-relaxed">
                            &lt;iframe src="https://your-schedule.eventschedule.com/riverside-sessions?tickets=true&#38;embed=true" width="100%" height="700" frameborder="0" style="border: none;"&gt;&lt;/iframe&gt;
                        </p>
                    </div>
                </div>

                <!-- The part, drawn to size. The dimension lines carry the two
                     numbers the copied tag actually contains. -->
                <div class="es-fade-up es-d-4 relative" data-reveal>
                    <div class="mx-auto max-w-md">
                        <p class="es-widg-measure-label mb-1.5 text-center">width: 100%</p>
                        <div class="es-widg-measure mb-4"></div>

                        <div class="relative">
                            <div class="es-widg-part" style="box-shadow: 0 30px 60px -35px rgba(9, 20, 45, 0.55);">
                                <div class="es-widg-bar" style="background-color: {{ $demo['accent'] }}; color: #ffffff;">
                                    <p class="text-base font-bold leading-tight">{{ $demo['event'] }}</p>
                                    <p class="es-widg-bar-sub mt-0.5 text-xs">{{ $demo['when'] }} &middot; {{ $demo['time'] }}</p>
                                </div>

                                <div class="px-5 py-4">
                                    @foreach ($demo['rows'] as [$rowName, $rowPrice, $rowQty])
                                        <div class="es-widg-row">
                                            <div class="min-w-0 flex-1">
                                                <p class="es-widg-row-name truncate text-sm font-semibold">{{ $rowName }}</p>
                                                <p class="es-widg-row-note es-widg-mono text-xs">{{ $rowPrice }}</p>
                                            </div>
                                            <span class="es-widg-qty" aria-hidden="true">{{ $rowQty }}</span>
                                        </div>
                                    @endforeach

                                    <div class="mt-4 grid grid-cols-2 gap-2.5" aria-hidden="true">
                                        <div>
                                            <span class="es-widg-input-label mb-1">Name</span>
                                            <div class="es-widg-input text-xs">Alex Moreau</div>
                                        </div>
                                        <div>
                                            <span class="es-widg-input-label mb-1">Email</span>
                                            <div class="es-widg-input truncate text-xs">alex@example.com</div>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <span class="es-widg-input-label mb-1">Promo code</span>
                                        <div class="es-widg-input es-widg-mono text-xs">RIVER10</div>
                                    </div>

                                    <div class="es-widg-total mt-4">
                                        <span class="text-sm font-semibold">Total</span>
                                        <span class="es-widg-mono text-lg font-bold">{{ $demo['total'] }}</span>
                                    </div>

                                    <div class="es-widg-pay mt-4" style="background-color: {{ $demo['accent'] }}; color: #ffffff;">Pay {{ $demo['total'] }}</div>
                                </div>
                            </div>

                            <div class="es-widg-measure-v hidden sm:block" style="right: -1.5rem;" aria-hidden="true"></div>
                            <span class="es-widg-measure-vlabel hidden sm:block" style="left: auto; right: -1.35rem;" aria-hidden="true">height: 700</span>
                        </div>

                        <p class="es-widg-muted mt-5 text-center text-xs">
                            One tag, and two numbers in it. Choosing, answering, discounting and
                            totalling all happen right there, in the space you gave it.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The anatomy (01)                                          -->
    <!-- ============================================================ -->
    <section id="anatomy" class="scroll-mt-24 es-widg-rule-t py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <p class="es-widg-eyebrow mb-4" data-reveal>01 &middot; The anatomy</p>
                <h2 class="es-balance es-widg-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    Seven layers, and all of them are real.
                </h2>
                <div class="mx-auto mb-5 max-w-[10rem]" data-reveal style="--reveal-delay: 0.08s;">
                    <div class="es-widg-measure"></div>
                </div>
                <p class="es-widg-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    The embed is not a cut-down version of the checkout that lives on your schedule.
                    It is the same purchase form, in a smaller frame, with the navigation taken off.
                </p>
            </div>

            <ol class="space-y-4">
                @foreach ($layers as $li => [$num, $title, $body])
                    <li class="grid items-center gap-5 md:grid-cols-2 md:gap-8" data-reveal>
                        <div class="flex items-center">
                            <span class="es-widg-num" aria-hidden="true">{{ $num }}</span>
                            <span class="es-widg-lead" aria-hidden="true" style="--ld: {{ 0.15 + $li * 0.05 }}s;"></span>
                            <div class="es-widg-part es-widg-slab min-w-0 flex-1">
                                @if ($num === '01')
                                    <div class="es-widg-bar" style="background-color: {{ $demo['accent'] }}; color: #ffffff;">
                                        <p class="text-sm font-bold leading-tight">{{ $demo['event'] }}</p>
                                        <p class="es-widg-bar-sub mt-0.5 text-[0.7rem]">{{ $demo['when'] }} &middot; {{ $demo['time'] }}</p>
                                    </div>
                                @elseif ($num === '02')
                                    <div class="px-4 py-2">
                                        @foreach ($demo['rows'] as [$rowName, $rowPrice, $rowQty])
                                            <div class="es-widg-row">
                                                <div class="min-w-0 flex-1">
                                                    <p class="es-widg-row-name truncate text-xs font-semibold">{{ $rowName }}</p>
                                                    <p class="es-widg-row-note es-widg-mono text-[0.7rem]">{{ $rowPrice }}</p>
                                                </div>
                                                <span class="es-widg-qty" aria-hidden="true">{{ $rowQty }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif ($num === '03')
                                    <div class="grid grid-cols-2 gap-2.5 px-4 py-3.5" aria-hidden="true">
                                        <div>
                                            <span class="es-widg-input-label mb-1">Name</span>
                                            <div class="es-widg-input text-[0.7rem]">Alex Moreau</div>
                                        </div>
                                        <div>
                                            <span class="es-widg-input-label mb-1">Email</span>
                                            <div class="es-widg-input truncate text-[0.7rem]">alex@example.com</div>
                                        </div>
                                    </div>
                                @elseif ($num === '04')
                                    <div class="px-4 py-3.5" aria-hidden="true">
                                        <span class="es-widg-input-label mb-1">Which workshop are you joining?</span>
                                        <div class="es-widg-input text-[0.7rem]">Songwriting</div>
                                    </div>
                                @elseif ($num === '05')
                                    <div class="flex items-end gap-2 px-4 py-3.5" aria-hidden="true">
                                        <div class="min-w-0 flex-1">
                                            <span class="es-widg-input-label mb-1">Promo code</span>
                                            <div class="es-widg-input es-widg-mono text-[0.7rem]">RIVER10</div>
                                        </div>
                                        <span class="es-widg-qty">Apply</span>
                                    </div>
                                @elseif ($num === '06')
                                    <div class="px-4 py-3.5" aria-hidden="true">
                                        <div class="es-widg-total" style="border-top: 0; padding-top: 0;">
                                            <span class="text-xs font-semibold">Total</span>
                                            <span class="es-widg-mono text-sm font-bold">{{ $demo['total'] }}</span>
                                        </div>
                                        <div class="es-widg-pay mt-2.5" style="background-color: {{ $demo['accent'] }}; color: #ffffff;">Pay {{ $demo['total'] }}</div>
                                    </div>
                                @else
                                    <div class="es-widg-foot" style="border-top: 0;">Powered by Event Schedule</div>
                                @endif
                            </div>
                        </div>

                        <div class="min-w-0">
                            <h3 class="es-widg-ink mb-1.5 text-lg font-bold">{{ $title }}</h3>
                            <p class="es-widg-muted text-sm leading-relaxed">{{ $body }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>

            <div class="mt-10 text-center" data-reveal>
                <span class="es-widg-plan es-widg-plan-pro">Pro</span>
                <span class="es-widg-muted ml-2 text-sm">
                    The ticket widget is on the Pro plan, and Event Schedule takes no platform fee on what it sells.
                </span>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The pin-out (02)                                          -->
    <!-- ============================================================ -->
    <section id="pinout" class="scroll-mt-24 es-widg-rule-t py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-start gap-14 lg:grid-cols-2 lg:gap-16">
                <div>
                    <p class="es-widg-eyebrow mb-4" data-reveal>02 &middot; The pin-out</p>
                    <h2 class="es-balance es-widg-ink mb-5 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                        A published interface, so you can wire it yourself.
                    </h2>
                    <div class="mb-6 max-w-[10rem]" data-reveal style="--reveal-delay: 0.08s;">
                        <div class="es-widg-measure"></div>
                    </div>
                    <p class="es-widg-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.1s;">
                        The copy button hands you a tag that already works. Everything past that is a
                        query string you can edit by hand, which is what makes one widget serve a
                        Spanish page, a dark page and a discounted link without needing three of them.
                    </p>

                    <ul class="space-y-4" data-reveal-group="90">
                        @foreach ([
                            ['Nothing to install', 'No script tag, no npm package, no build step. A block that accepts raw HTML is the entire requirement.'],
                            ['It cannot drift', 'Change a price, add a ticket type, sell out a date, and the frame on your site is already correct. The tag never changes again.'],
                            ['It works on selfhosted too', 'The same tag with your own install\'s address in it. Nothing about the widget calls out to us.'],
                        ] as [$t, $d])
                            <li class="flex items-start gap-3" data-reveal>
                                <svg aria-hidden="true" class="es-widg-accent mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span><span class="es-widg-ink font-semibold">{{ $t }}</span> <span class="es-widg-muted">- {{ $d }}</span></span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                    <div class="es-tilt-inner es-widg-card overflow-hidden p-6 sm:p-7">
                        <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                            <h3 class="es-widg-ink text-lg font-bold">URL parameters</h3>
                            <span class="es-widg-muted es-widg-mono text-xs">6 rows: one mode, one flag, three you can add</span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="es-widg-table">
                                <caption class="sr-only">Query-string parameters accepted by the ticket embed URL</caption>
                                <thead>
                                    <tr>
                                        <th scope="col">Parameter</th>
                                        <th scope="col">Kind</th>
                                        <th scope="col">What it does</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($params as [$key, $kind, $what])
                                        <tr>
                                            <td><span class="es-widg-key">{{ $key }}</span></td>
                                            <td class="es-widg-mono text-xs">{{ $kind }}</td>
                                            <td>{{ $what }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <p class="es-widg-muted es-widg-rule-t mt-5 pt-4 text-xs">
                            The height in the tag is a starting figure, not a ceiling. Set it to whatever
                            suits the page, and the frame fills the width it is given.
                        </p>

                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Two finishes, and your colour (03)                        -->
    <!-- ============================================================ -->
    <section id="finish" class="scroll-mt-24 es-widg-rule-t py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <p class="es-widg-eyebrow mb-4" data-reveal>03 &middot; Finishes</p>
                <h2 class="es-balance es-widg-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    It ships in two, and takes your colour.
                </h2>
                <div class="mx-auto mb-5 max-w-[10rem]" data-reveal style="--reveal-delay: 0.08s;">
                    <div class="es-widg-measure"></div>
                </div>
                <p class="es-widg-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    Both are shown here at once, on purpose. In the wild the widget picks one from the
                    visitor's own system setting, so it belongs on a dark site and a light site
                    without you configuring anything.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-2" data-reveal-group="100">
                <div data-reveal="panel">
                    <div class="mb-3 flex items-baseline justify-between gap-3">
                        <h3 class="es-widg-ink text-base font-bold">Light finish</h3>
                        <span class="es-widg-muted es-widg-mono text-xs">default</span>
                    </div>
                    <div class="es-widg-part es-widg-lit">
                        <div class="es-widg-bar" style="background-color: {{ $demo['accent'] }}; color: #ffffff;">
                            <p class="text-sm font-bold leading-tight">{{ $demo['event'] }}</p>
                            <p class="es-widg-bar-sub mt-0.5 text-[0.7rem]">{{ $demo['when'] }} &middot; {{ $demo['time'] }}</p>
                        </div>
                        <div class="px-4 py-3">
                            @foreach ($demo['rows'] as [$rowName, $rowPrice, $rowQty])
                                <div class="es-widg-row">
                                    <div class="min-w-0 flex-1">
                                        <p class="es-widg-row-name truncate text-xs font-semibold">{{ $rowName }}</p>
                                        <p class="es-widg-row-note es-widg-mono text-[0.7rem]">{{ $rowPrice }}</p>
                                    </div>
                                    <span class="es-widg-qty" aria-hidden="true">{{ $rowQty }}</span>
                                </div>
                            @endforeach
                            <div class="es-widg-total mt-3">
                                <span class="text-xs font-semibold">Total</span>
                                <span class="es-widg-mono text-sm font-bold">{{ $demo['total'] }}</span>
                            </div>
                            <div class="es-widg-pay mt-3" style="background-color: {{ $demo['accent'] }}; color: #ffffff;">Pay {{ $demo['total'] }}</div>
                        </div>
                    </div>
                    <p class="es-widg-muted mt-3 text-sm">What a visitor whose system is set light sees, whichever mode you are reading this page in.</p>
                </div>

                <div data-reveal="panel">
                    <div class="mb-3 flex items-baseline justify-between gap-3">
                        <h3 class="es-widg-ink text-base font-bold">Dark finish</h3>
                        <span class="es-widg-muted es-widg-mono text-xs">dark=true</span>
                    </div>
                    <div class="es-widg-part es-widg-drk">
                        <div class="es-widg-bar" style="background-color: {{ $demo['accent'] }}; color: #ffffff;">
                            <p class="text-sm font-bold leading-tight">{{ $demo['event'] }}</p>
                            <p class="es-widg-bar-sub mt-0.5 text-[0.7rem]">{{ $demo['when'] }} &middot; {{ $demo['time'] }}</p>
                        </div>
                        <div class="px-4 py-3">
                            @foreach ($demo['rows'] as [$rowName, $rowPrice, $rowQty])
                                <div class="es-widg-row">
                                    <div class="min-w-0 flex-1">
                                        <p class="es-widg-row-name truncate text-xs font-semibold">{{ $rowName }}</p>
                                        <p class="es-widg-row-note es-widg-mono text-[0.7rem]">{{ $rowPrice }}</p>
                                    </div>
                                    <span class="es-widg-qty" aria-hidden="true">{{ $rowQty }}</span>
                                </div>
                            @endforeach
                            <div class="es-widg-total mt-3">
                                <span class="text-xs font-semibold">Total</span>
                                <span class="es-widg-mono text-sm font-bold">{{ $demo['total'] }}</span>
                            </div>
                            <div class="es-widg-pay mt-3" style="background-color: {{ $demo['accent'] }}; color: #ffffff;">Pay {{ $demo['total'] }}</div>
                        </div>
                    </div>
                    <p class="es-widg-muted mt-3 text-sm">The same part with the dark finish, forced by the URL or picked up from the visitor's own setting.</p>
                </div>
            </div>

            <div class="mt-10 grid gap-4 md:grid-cols-3" data-reveal-group="90">
                <div class="es-widg-card p-6 md:col-span-2" data-reveal>
                    <h3 class="es-widg-ink mb-2 text-base font-bold">The bar takes your accent colour</h3>
                    <p class="es-widg-muted mb-5 text-sm leading-relaxed">
                        The header bar is painted with the accent colour set on your schedule, and the
                        text on it is chosen for you by brightness: white on a dark bar, black on a
                        light one. These four bars are drawn by that same rule, and you can watch it
                        flip halfway along the row.
                    </p>
                    <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-4">
                        @foreach ($swatches as [$swBg, $swInk, $swInkName])
                            <div class="es-widg-swatch" style="background-color: {{ $swBg }}; color: {{ $swInk }};">
                                {{ $demo['event'] }}
                                <span class="es-widg-mono mt-1 block text-[0.6rem] font-normal">{{ $swBg }} &middot; {{ $swInkName }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="es-widg-card flex flex-col p-6" data-reveal>
                    <h3 class="es-widg-ink mb-2 text-base font-bold">Twelve languages</h3>
                    <p class="es-widg-muted mb-4 text-sm leading-relaxed">
                        Set <span class="es-widg-key">lang=xx</span> and the widget speaks it. Arabic
                        and Hebrew lay the whole form out right to left, including the ticket rows and
                        the total.
                    </p>
                    <div class="mt-auto flex flex-wrap gap-1.5" aria-hidden="true">
                        @foreach (array_keys(config('app.supported_languages')) as $langCode)
                            <span class="es-widg-key">{{ $langCode }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Where the payment goes (04)                               -->
    <!-- ============================================================ -->
    <section id="money" class="scroll-mt-24 es-widg-rule-t py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <p class="es-widg-eyebrow mb-4" data-reveal>04 &middot; Payment</p>
                <h2 class="es-balance es-widg-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    None of the ticket price comes to us.
                </h2>
                <div class="mx-auto mb-5 max-w-[10rem]" data-reveal style="--reveal-delay: 0.08s;">
                    <div class="es-widg-measure"></div>
                </div>
                <p class="es-widg-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    Event Schedule charges no platform fee on ticket sales. Card processing runs
                    through your own Stripe account, so what it costs is between you and Stripe.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4" data-reveal-group="100">
                @foreach ([
                    ['Stripe', 'Opens in the parent window', 'Your own connected account. The buyer leaves the frame for the payment page and comes back to your site when it is done.'],
                    ['Invoice Ninja', 'Opens in the parent window', 'For anyone already invoicing through Invoice Ninja, including the payment-link mode.'],
                    ['Custom payment URL', 'Opens in the parent window', 'Point the event at any payment page you already run, and it is used instead.'],
                    ['Cash or at the door', 'Finishes in the frame', 'No processor involved. The order is recorded, confirmed and emailed without the buyer leaving your page, and a free ticket on a cash event finishes here too.'],
                ] as [$payName, $payWhere, $payNote])
                    <div class="es-widg-card es-widg-hover flex flex-col p-6" data-reveal>
                        <h3 class="es-widg-ink mb-1 text-base font-bold">{{ $payName }}</h3>
                        <p class="es-widg-accent es-widg-mono mb-3 text-xs">{{ $payWhere }}</p>
                        <p class="es-widg-muted text-sm leading-relaxed">{{ $payNote }}</p>
                    </div>
                @endforeach
            </div>

            <p class="es-widg-muted mx-auto mt-8 max-w-3xl text-center text-sm" data-reveal>
                Payment portals frequently refuse to be loaded inside an iframe, so the three that use
                one break out to the parent window rather than failing quietly in a box on your page.
                It is decided by the event's payment method, not by the amount, and it is the one
                thing the widget deliberately does outside the frame.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Operating conditions (05, fixed dark band)                -->
    <!-- ============================================================ -->
    <section id="limits" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-widg-band noise relative overflow-hidden rounded-[2rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <p class="es-widg-eyebrow mb-4" data-reveal>05 &middot; Operating conditions</p>
                    <h2 class="es-balance es-widg-bright mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                        What the part needs to run.
                    </h2>
                    <div class="mx-auto mb-5 max-w-[10rem]" data-reveal style="--reveal-delay: 0.08s;">
                        <div class="es-widg-measure"></div>
                    </div>
                    <p class="es-widg-dim text-lg" data-reveal style="--reveal-delay: 0.1s;">
                        Short list, and it is the whole list. If the Embed Tickets link is not showing
                        on your event, one of these four is the reason.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2" data-reveal-group="100">
                    @foreach ($conditions as [$cLabel, $cValue, $cNote])
                        <div class="rounded-lg border border-white/10 bg-white/[0.05] p-6 backdrop-blur-sm" data-reveal="panel">
                            <p class="es-widg-eyebrow mb-2">{{ $cLabel }}</p>
                            <h3 class="es-widg-bright mb-2 text-lg font-bold">{{ $cValue }}</h3>
                            <p class="es-widg-dim text-sm leading-relaxed">{{ $cNote }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-10 grid gap-4 md:grid-cols-3" data-reveal-group="90">
                    @foreach ([
                        ['Served noindex', 'The framed page carries a noindex, nofollow tag, so your own page is the one search engines read. The embed never competes with it.'],
                        ['Never carries ads', 'Free schedules can show ads on their public pages. Embeds are excluded on every plan, so nothing appears on your site that you did not put there.'],
                        ['Framing is opt-in', 'Only the embed routes may be framed at all. Everything else on the platform refuses, which is why the flag in the URL is not optional.'],
                    ] as [$sTitle, $sNote])
                        <div class="rounded-lg border border-white/10 bg-white/[0.04] p-6" data-reveal>
                            <div class="mb-3 flex items-center gap-2">
                                <svg aria-hidden="true" class="es-widg-glow h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <h3 class="es-widg-bright text-base font-bold">{{ $sTitle }}</h3>
                            </div>
                            <p class="es-widg-dim text-sm leading-relaxed">{{ $sNote }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Fitting it (06)                                           -->
    <!-- ============================================================ -->
    <section id="fit" class="scroll-mt-24 es-widg-rule-t py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <p class="es-widg-eyebrow mb-4" data-reveal>06 &middot; Fitting it</p>
                <h2 class="es-balance es-widg-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    Three steps, once.
                </h2>
                <div class="mx-auto max-w-[10rem]" data-reveal style="--reveal-delay: 0.08s;">
                    <div class="es-widg-measure"></div>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                @foreach ([
                    ['01', 'Put something on sale', 'Open the event, go to the Tickets section, and switch on tickets or registration. Add at least one ticket type so there is something to buy.'],
                    ['02', 'Copy the tag', 'Click Embed Tickets next to the heading. The panel shows the embed URL, the iframe tag and a live preview of the widget as it will look on your page.'],
                    ['03', 'Paste it in', 'Drop the tag into your website\'s HTML where the form belongs. Any block that takes raw HTML will accept it, and you never touch it again.'],
                ] as [$n, $t, $d])
                    <div class="es-widg-card es-widg-hover flex flex-col p-7" data-reveal="panel">
                        <span class="es-widg-num mb-4" aria-hidden="true">{{ $n }}</span>
                        <h3 class="es-widg-ink mb-2 text-lg font-bold">{{ $t }}</h3>
                        <p class="es-widg-muted text-sm leading-relaxed">{{ $d }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 flex flex-wrap items-center justify-center gap-x-8 gap-y-3 text-center" data-reveal>
                <a href="{{ route('marketing.docs.tickets') }}#embed-widget" class="es-widg-accent inline-flex items-center font-medium hover:underline">
                    Read the ticketing guide
                    <svg aria-hidden="true" class="ml-1 h-4 w-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ route('marketing.docs.sharing') }}" class="es-widg-accent inline-flex items-center font-medium hover:underline">
                    Read the sharing guide
                    <svg aria-hidden="true" class="ml-1 h-4 w-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Plans and neighbours                                      -->
    <!-- ============================================================ -->
    <section class="scroll-mt-24 es-widg-rule-t py-20">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-widg-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Which plan, and which embed</h2>

            <div class="grid gap-4 md:grid-cols-3" data-reveal-group="90">
                <div class="es-widg-card flex flex-col p-7" data-reveal="panel">
                    <div class="mb-4 flex items-center gap-2">
                        <span class="es-widg-plan es-widg-plan-pro">Pro</span>
                        <span class="es-widg-muted es-widg-mono text-[0.65rem] uppercase tracking-widest">this page</span>
                    </div>
                    <h3 class="es-widg-ink mb-2 text-lg font-bold">The ticket widget</h3>
                    <p class="es-widg-muted text-sm leading-relaxed">The purchase or registration form itself, on your page. Comes with the rest of ticketing: promo codes, custom fields, waitlist and QR check-in.</p>
                </div>

                <div class="es-widg-card flex flex-col p-7" data-reveal="panel">
                    <div class="mb-4 flex items-center gap-2">
                        <span class="es-widg-plan es-widg-plan-free">Free</span>
                        <span class="es-widg-muted es-widg-mono text-[0.65rem] uppercase tracking-widest">the other one</span>
                    </div>
                    <h3 class="es-widg-ink mb-2 text-lg font-bold">The calendar embed</h3>
                    <p class="es-widg-muted mb-4 text-sm leading-relaxed">A different tag for a different job: your whole schedule on the page, linking out to each event. Free on every plan.</p>
                    <div class="mt-auto">
                        <x-link href="{{ marketing_url('/features/embed-calendar') }}">Embed calendar</x-link>
                    </div>
                </div>

                <div class="es-widg-card flex flex-col p-7" data-reveal="panel">
                    <div class="mb-4 flex items-center gap-2">
                        <span class="es-widg-plan es-widg-plan-free">Free</span>
                        <span class="es-widg-muted es-widg-mono text-[0.65rem] uppercase tracking-widest">selfhosted</span>
                    </div>
                    <h3 class="es-widg-ink mb-2 text-lg font-bold">On your own server</h3>
                    <p class="es-widg-muted mb-4 text-sm leading-relaxed">The same widget with your install's own address in the tag, included at no extra cost.</p>
                    <div class="mt-auto">
                        <x-link href="{{ marketing_url('/selfhost') }}">Selfhosting</x-link>
                    </div>
                </div>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-2 lg:grid-cols-4" data-reveal-group="90">
                <div class="es-widg-card p-6" data-reveal>
                    <h3 class="es-widg-ink mb-1 text-base font-bold">Popular with</h3>
                    <p class="es-widg-muted text-sm leading-relaxed">People who already have a website and want the money to arrive through it.</p>
                </div>
                @foreach ([
                    ['/for-venues', 'Venues', 'The room already has a site. This puts the door money on it.'],
                    ['/for-libraries', 'Libraries', 'Free registrations for a talk or a workshop, taken on the branch page.'],
                    ['/for-community-centers', 'Community Centers', 'Classes and clubs paid for on the same page they are listed on.'],
                ] as [$popHref, $popName, $popBlurb])
                    <a href="{{ marketing_url($popHref) }}" class="es-widg-card es-widg-hover group flex flex-col p-6" data-reveal>
                        <span class="es-widg-ink mb-2 text-base font-bold">For {{ $popName }}</span>
                        <span class="es-widg-muted mb-4 text-sm leading-relaxed">{{ $popBlurb }}</span>
                        <span class="es-widg-accent mt-auto inline-flex items-center gap-1 text-xs font-semibold uppercase tracking-widest">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    @include('marketing.partials.pricing-nudge')

    <!-- ============================================================ -->
    <!-- 9. Related features                                          -->
    <!-- ============================================================ -->
    <section class="es-widg-rule-t py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-widg-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card
                        name="Ticketing"
                        description="Ticket types, promo codes, QR check-in, and zero platform fees"
                        :url="marketing_url('/features/ticketing')"
                        icon-color="blue"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Embed Calendar"
                        description="Put your whole schedule on any website with a single iframe"
                        :url="marketing_url('/features/embed-calendar')"
                        icon-color="teal"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Custom Fields"
                        description="Ask ticket buyers your own questions, answered at checkout"
                        :url="marketing_url('/features/custom-fields')"
                        icon-color="amber"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Gift Cards"
                        description="Balance-tracked gift cards, redeemable in the widget alongside a promo code"
                        :url="marketing_url('/features/gift-cards')"
                        icon-color="green"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-widg-accent inline-flex items-center font-medium hover:underline">
                    See all features
                    <svg aria-hidden="true" class="ml-1 h-4 w-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <x-marketing.related-pages />

    <!-- ============================================================ -->
    <!-- 10. FAQ (07)                                                 -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="scroll-mt-24 es-widg-rule-t py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <p class="es-widg-eyebrow mb-4" data-reveal>07 &middot; Questions</p>
                <h2 class="es-balance es-widg-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    Asked before it goes on the site.
                </h2>
                <div class="mx-auto max-w-[10rem]" data-reveal style="--reveal-delay: 0.08s;">
                    <div class="es-widg-measure"></div>
                </div>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" data-reveal class="es-widg-card es-widg-hover group/faq overflow-hidden">
                        <summary class="flex cursor-pointer items-start gap-3 p-6">
                            <span class="es-widg-accent es-widg-mono flex-none text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <h3 class="es-widg-ink flex-1 text-lg font-semibold">{{ $faq['q'] }}</h3>
                            <svg aria-hidden="true" class="es-widg-muted mt-1 h-5 w-5 flex-none transition-transform duration-300 group-open/faq:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="es-widg-muted faq-answer px-6 pb-6 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 11. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-widg-band noise relative overflow-hidden rounded-[2rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-25"></div>
                </div>

                <div class="relative z-10">
                    <p class="es-widg-eyebrow mb-6">Free to start</p>
                    <h2 class="es-balance es-widg-bright mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight md:text-5xl">
                        Fit it once, and sell from your own page.
                    </h2>

                    {{-- The page opened by measuring the part. It closes by handing you back
                         the same two figures, which are the only two the tag contains. --}}
                    <div class="mx-auto mb-8 max-w-[17rem]">
                        <p class="es-widg-measure-label mb-1.5">width: 100% &middot; height: 700</p>
                        <div class="es-widg-measure"></div>
                    </div>

                    <p class="es-widg-dim mx-auto mb-10 max-w-2xl text-lg sm:text-xl">
                        Claim a schedule, put an event on sale, and copy one tag into the site you
                        already have. Nothing to install, and no cut of the ticket price.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-lg border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-widg-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg px-8 py-4 text-lg font-semibold">
                            <span class="relative z-10 flex items-center gap-2">
                                Get started free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="es-widg-dim mt-6 text-sm">No credit card required</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Section dot navigation -->
    <nav class="es-dotnav fixed top-1/2 z-40 hidden -translate-y-1/2 lg:block ltr:right-5 rtl:left-5" aria-label="Page sections">
        <ul class="glass flex flex-col items-center gap-1.5 rounded-full px-2 py-3">
            @foreach ($dotSections as [$sectionId, $sectionLabel])
                <li class="relative">
                    <a href="#{{ $sectionId }}" class="es-dot group block rounded-full" aria-label="{{ $sectionLabel }}">
                        <span class="es-dot-pip block h-2 w-2 rounded-full bg-gray-400/60 dark:bg-white/30"></span>
                        <span class="es-widg-tip pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
