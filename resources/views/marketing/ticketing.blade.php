<x-marketing-layout>
    <x-slot name="title">{{ __('marketing.ticketing_title') }}</x-slot>
    <x-slot name="description">{{ __('marketing.ticketing_description') }}</x-slot>
    <x-slot name="breadcrumbTitle">Ticketing</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule Ticketing",
        "description": "Sell tickets from your own event page with named ticket types, promo codes, add-ons and passes, then scan the QR code at the door. Zero platform fees on ticket sales.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Ticketing"
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule Ticketing",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Event Ticketing Software",
        "operatingSystem": "Web",
        "description": "Sell tickets from your own event page and check them in at the door with a phone. Ticket types with their own price, quantity and sales window, promo codes, add-ons, passes, waitlist and QR check-in. Zero platform fees on ticket sales.",
        "offers": {
            "@type": "Offer",
            "price": "5",
            "priceCurrency": "USD",
            "description": "Ticketing is included in the Pro plan at $5 per month, with a 7 day free trial. Zero platform fees on ticket sales."
        },
        "featureList": [
            "Zero platform fees on ticket sales",
            "Ticket types with their own price, quantity and sales window",
            "Ticket inventory counted per occurrence date on recurring events",
            "Combined inventory across every ticket type",
            "Volume discounts and a maximum per order",
            "Add-ons for parking, merchandise and meal packages",
            "Promo codes with percentage or fixed discounts, usage limits and per-ticket targeting",
            "Passes and season subscriptions valid across many events",
            "Custom questions collected at checkout",
            "Individual tickets, so each guest gets their own confirmation and QR code",
            "QR code on every ticket, scanned from a phone at the door",
            "One admission per ticket, with a warning on a second scan",
            "Live check-in dashboard with a per-ticket-type breakdown",
            "Ticket waitlist that notifies one person at a time",
            "Sale notification emails",
            "Sales CSV export including custom field answers",
            "Free registration and RSVP with optional capacity limits"
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
        "name": "How to sell tickets with Event Schedule",
        "description": "Three steps from a published event to a scanned ticket at the door.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Connect a payment method",
                "text": "Connect your own Stripe account, or Invoice Ninja, or a payment URL, or take cash at the door. Money from card sales settles into your account, not ours."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Add your ticket types",
                "text": "Give each ticket type a name, a price, a quantity and, if you want one, a sales start and end date. Add promo codes, add-ons and custom questions on their own tabs."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Scan at the door",
                "text": "Every ticket goes out with a QR code. Open Sales on your phone, tap Scan Tickets, and each ticket admits once. The check-in dashboard keeps the count."
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
           For ticketing "The Turnstile" styles. A turnstile is the only
           machine in the building that touches BOTH halves of a ticket:
           it will not turn until the ticket is valid, it turns once per
           ticket, and it keeps a count. That is the whole argument of
           this page - Event Schedule is that turnstile with an empty
           coin slot. So the page is built as sale side / turn / door
           side, and the mid-page moment is a gate log of the real
           verdicts the scanner returns.

           WHY NOT A DRAWING. CLAUDE.md forbids decorative outline
           illustrations, so there is no turnstile rotor, no arm and no
           gate anywhere in here. The concept is carried by MATERIAL and
           TYPOGRAPHY instead: perforated ticket stock, a machined
           counter plate, and monospace tabular readings.

           WHY NOT "THE DOOR". /for-nightclubs already owns the doorway
           and stands OUTSIDE the club in brushed steel. This page is
           deliberately the mechanism just inside it, and it never uses
           steel as its material.

           COLOUR: the page keeps its inherited sky/blue family, spent
           sparingly, because the campaign's hue wheel is spent and the
           brief pins each page to its existing hue. Distinctiveness
           comes from structure and material, not from a new hue.

           MEASURED (never guessed):
             light ground #f2f5f8: ink #0e1520 16.74, muted #4a5462 7.02,
               accent #075985 6.91
             dark ground  #080d13: ink #e8eef5 16.69, muted #93a4b8 7.65,
               accent #7dd3fc 11.69
             plate #161d25:  ink #e6edf4 14.38, muted #94a3b4 6.60,
               lit #7dd3fc 10.19, admit #4ade80 9.75, refuse #fca5a5 8.95
             band  #0c1219:  muted #93a4b8 7.38, lit #7dd3fc 11.28
             stub  #f5f2ea:  ink #1a1712 15.97, muted #5b5344 6.79,
               accent #075985 6.76
           NEVER text-gray-500 on this page: 4.83 on pure white but only
           ~4.2 on this tinted ground.

           FIXED PHYSICAL OBJECTS, identical with .dark on and off:
             .es-turn-plate  the machined counter plate
             .es-turn-stub   the ticket stock and its counterfoil
             .es-turn-band   the gate hall
           Nothing inside those carries a dark: variant, and the shared
           classes that flip themselves (grid-overlay, animate-shimmer,
           es-claim:focus-within) are overridden per band below.

           BLADE RULE for this block: never use @supports probes here. A
           "#" hex inside a parenthesized at-rule condition breaks Blade
           compilation of every later parenthesized directive.
           ============================================================== */

        /* --- Ground and ink --- */
        .es-turn-page { background-color: #f2f5f8; color: #0e1520; }
        .dark .es-turn-page { background-color: #080d13; color: #e8eef5; }
        .es-turn-ink { color: #0e1520; }
        .dark .es-turn-ink { color: #e8eef5; }
        .es-turn-muted { color: #4a5462; }
        .dark .es-turn-muted { color: #93a4b8; }
        .es-turn-accent { color: #075985; }
        .dark .es-turn-accent { color: #7dd3fc; }
        /* Always-lit accent, for the fixed-dark surfaces in both modes. */
        .es-turn-lit { color: #7dd3fc; }

        /* --- The perforation. The page's signature rule: every band is
               separated by a tear line, because a ticket is a thing that
               comes apart into a sold half and an admitted half. --- */
        .es-turn-perf {
            height: 3px;
            border: 0;
            background-image: radial-gradient(circle at center, rgba(14, 21, 32, 0.34) 0, rgba(14, 21, 32, 0.34) 1px, rgba(14, 21, 32, 0) 1.5px);
            background-size: 10px 3px;
            background-repeat: repeat-x;
            background-position: center;
        }
        .dark .es-turn-perf {
            background-image: radial-gradient(circle at center, rgba(232, 238, 245, 0.3) 0, rgba(232, 238, 245, 0.3) 1px, rgba(232, 238, 245, 0) 1.5px);
        }
        /* Vertical tear, between a ticket and its counterfoil. Fixed in
           both colour modes: it lives only on .es-turn-stub. */
        .es-turn-perf-v {
            width: 3px;
            flex: none;
            align-self: stretch;
            background-image: radial-gradient(circle at center, rgba(26, 23, 18, 0.4) 0, rgba(26, 23, 18, 0.4) 1px, rgba(26, 23, 18, 0) 1.5px);
            background-size: 3px 10px;
            background-repeat: repeat-y;
            background-position: center;
        }

        /* --- The counter plate: machined metal, FIXED in both modes --- */
        .es-turn-plate {
            border-radius: 1.1rem;
            border: 1px solid rgba(232, 238, 245, 0.13);
            background-color: #161d25;
            background-image:
                repeating-linear-gradient(90deg, rgba(255, 255, 255, 0.03) 0 1px, rgba(255, 255, 255, 0) 1px 4px),
                radial-gradient(130% 95% at 50% 0%, #1e2732 0%, #161d25 55%, #0c1218 100%);
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, 0.07),
                inset 0 -22px 44px rgba(0, 0, 0, 0.45),
                0 24px 50px -26px rgba(3, 10, 18, 0.6);
        }
        .es-turn-plate-ink { color: #e6edf4; }
        .es-turn-plate-muted { color: #94a3b4; }
        /* A milled seam across the plate, where a real counter housing splits. */
        .es-turn-seam {
            height: 1px;
            background-image: linear-gradient(90deg, rgba(232, 238, 245, 0) 0%, rgba(232, 238, 245, 0.2) 18%, rgba(232, 238, 245, 0.2) 82%, rgba(232, 238, 245, 0) 100%);
            box-shadow: 0 1px 0 rgba(0, 0, 0, 0.5);
        }

        /* --- Registers: a label on the left, a tabular reading on the right --- */
        .es-turn-reg {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 1rem;
        }
        .es-turn-read {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            letter-spacing: 0.04em;
            font-weight: 700;
        }
        .es-turn-count {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-weight: 800;
            letter-spacing: 0.05em;
            line-height: 1;
            font-size: clamp(2.7rem, 9vw, 4rem);
            color: #e6edf4;
        }
        /* The shared odometer paints each digit with the brand gradient. On a
           machined plate a counter drum is solid ink, so override it. The
           strip spans are built at runtime by marketing-home.js. */
        .es-turn-plate .es-od-strip span {
            background-image: none;
            -webkit-text-fill-color: #e6edf4;
            color: #e6edf4;
        }

        /* --- The duplex: sale side, the turn, door side --- */
        .es-turn-duplex { display: grid; gap: 1.5rem; }
        @media (min-width: 1024px) {
            .es-turn-duplex { grid-template-columns: 1fr auto 1fr; gap: 2rem; align-items: stretch; }
        }
        /* The detent rail. A turnstile advances one notch at a time, so the
           rail is notched rather than continuous. Abstract stroke, not a
           drawing of anything. */
        .es-turn-rail {
            position: relative;
            flex: 1 1 0;
            width: auto;
            height: 3px;
            min-width: 2.5rem;
            border-radius: 2px;
            background-image: repeating-linear-gradient(90deg, rgba(7, 89, 133, 0.85) 0 11px, rgba(7, 89, 133, 0.16) 11px 18px);
        }
        .dark .es-turn-rail {
            background-image: repeating-linear-gradient(90deg, rgba(125, 211, 252, 0.85) 0 11px, rgba(125, 211, 252, 0.18) 11px 18px);
        }
        @media (min-width: 1024px) {
            .es-turn-rail {
                width: 3px;
                height: auto;
                min-width: 0;
                min-height: 3rem;
                background-image: repeating-linear-gradient(180deg, rgba(7, 89, 133, 0.85) 0 11px, rgba(7, 89, 133, 0.16) 11px 18px);
            }
            .dark .es-turn-rail {
                background-image: repeating-linear-gradient(180deg, rgba(125, 211, 252, 0.85) 0 11px, rgba(125, 211, 252, 0.18) 11px 18px);
            }
        }
        /* One notch of travel at a time: steps() is the whole point of the
           device. Only shown on the vertical rail, where there is travel to
           see; on the stacked layout the rail is 3px tall. */
        .es-turn-tick { display: none; }
        @media (min-width: 1024px) {
            .es-turn-tick {
                display: block;
                position: absolute;
                left: -3px;
                top: 0;
                width: 9px;
                height: 9px;
                border-radius: 9999px;
                background-color: #075985;
                box-shadow: 0 0 10px rgba(7, 89, 133, 0.65);
            }
            .dark .es-turn-tick { background-color: #7dd3fc; box-shadow: 0 0 10px rgba(125, 211, 252, 0.6); }
            html.es-anim .es-turn-tick { animation: es-turn-step 5.4s steps(6, end) infinite; }
        }
        @keyframes es-turn-step {
            from { top: 0; }
            to { top: calc(100% - 9px); }
        }

        /* The hub chip that sits on the rail: one ticket, one turn. */
        .es-turn-hub {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.3rem 0.7rem;
            border-radius: 9999px;
            border: 1px solid rgba(7, 89, 133, 0.35);
            background-color: #f2f5f8;
            color: #075985;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            white-space: nowrap;
        }
        .dark .es-turn-hub {
            border-color: rgba(125, 211, 252, 0.35);
            background-color: #080d13;
            color: #7dd3fc;
        }

        /* --- Cards --- */
        .es-turn-card {
            border: 1px solid rgba(14, 21, 32, 0.12);
            border-radius: 1rem;
            background-color: #ffffff;
        }
        .dark .es-turn-card {
            border-color: rgba(232, 238, 245, 0.12);
            background-color: rgba(232, 238, 245, 0.04);
        }
        .es-turn-band .es-turn-card {
            border-color: rgba(232, 238, 245, 0.14);
            background-color: rgba(232, 238, 245, 0.05);
        }
        .es-turn-hover:hover { border-color: rgba(7, 89, 133, 0.45); }
        .dark .es-turn-hover:hover { border-color: rgba(125, 211, 252, 0.45); }
        .es-turn-hover:hover .es-turn-hover-title,
        .es-turn-hover:hover .es-turn-hover-arrow { color: #075985; }
        .dark .es-turn-hover:hover .es-turn-hover-title,
        .dark .es-turn-hover:hover .es-turn-hover-arrow { color: #7dd3fc; }

        /* --- The ticket stub: real ticket stock, FIXED in both modes --- */
        .es-turn-stub {
            display: flex;
            align-items: stretch;
            border-radius: 0.75rem;
            overflow: hidden;
            background-color: #f5f2ea;
            background-image: repeating-linear-gradient(0deg, rgba(26, 23, 18, 0.028) 0 1px, rgba(26, 23, 18, 0) 1px 3px);
            box-shadow: 0 22px 44px -22px rgba(3, 10, 18, 0.6), inset 0 0 0 1px rgba(26, 23, 18, 0.1);
        }
        .es-turn-stub-ink { color: #1a1712; }
        .es-turn-stub-muted { color: #5b5344; }
        .es-turn-stub-accent { color: #075985; }
        /* A QR has to be dark on light to scan, so this panel is fixed too. */
        .es-turn-qr {
            width: 5.5rem;
            height: 5.5rem;
            border-radius: 0.4rem;
            background-color: #ffffff;
            padding: 0.3rem;
            box-shadow: inset 0 0 0 1px rgba(26, 23, 18, 0.12);
        }
        .es-turn-qr svg { display: block; width: 100%; height: 100%; }

        /* --- The gate log: the verdicts the scanner actually returns --- */
        .es-turn-log { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; }
        .es-turn-row {
            display: grid;
            grid-template-columns: 4.9rem 1fr;
            align-items: baseline;
            gap: 0.75rem;
            padding: 0.55rem 0;
            border-top: 1px solid rgba(232, 238, 245, 0.09);
        }
        .es-turn-verdict {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.1rem 0.4rem;
            border-radius: 0.25rem;
            border: 1px solid currentColor;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.12em;
        }
        .es-turn-verdict-pass { color: #4ade80; }
        .es-turn-verdict-warn { color: #fcd34d; }
        .es-turn-verdict-stop { color: #fca5a5; }

        /* --- Fixed-dark band: the gate hall --- */
        .es-turn-band {
            background-color: #0c1219;
            background-image: radial-gradient(125% 100% at 50% 0%, #16202b 0%, #0e151d 55%, #070c11 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(232, 238, 245, 0.05);
        }
        /* Shared classes that flip with the colour mode. Pinned per band so
           the hall renders identically with .dark on and off. */
        .es-turn-band .grid-overlay {
            background-image:
                linear-gradient(rgba(232, 238, 245, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(232, 238, 245, 0.05) 1px, transparent 1px);
        }
        .es-turn-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-turn-band .es-claim:focus-within {
            border-color: rgba(125, 211, 252, 0.75);
            box-shadow: 0 0 0 4px rgba(125, 211, 252, 0.22);
        }
        /* Ink for the band. No dark: variants: the hall is the same hall in
           both colour modes, so these are single fixed values. */
        .es-turn-band-ink { color: #e8eef5; }
        .es-turn-band-muted { color: #93a4b8; }

        /* --- Eyebrow labels --- */
        .es-turn-tag {
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            color: #4a5462;
        }
        .dark .es-turn-tag { color: #93a4b8; }
        .es-turn-band .es-turn-tag { color: #7dd3fc; }
        .es-turn-plate .es-turn-tag { color: #94a3b4; }

        /* --- Section mark: a gate number on a machined tab --- */
        .es-turn-mark {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.35rem 0.8rem;
            border-radius: 0.3rem;
            border: 1px solid rgba(14, 21, 32, 0.18);
            background-color: #ffffff;
            color: #0e1520;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.08em;
        }
        .dark .es-turn-mark { border-color: rgba(232, 238, 245, 0.2); background-color: rgba(232, 238, 245, 0.05); color: #e8eef5; }
        .es-turn-band .es-turn-mark { border-color: rgba(232, 238, 245, 0.2); background-color: rgba(232, 238, 245, 0.05); color: #e8eef5; }
        .es-turn-mark::before {
            content: "";
            width: 2px;
            align-self: stretch;
            border-radius: 1px;
            background-color: #075985;
        }
        .dark .es-turn-mark::before { background-color: #7dd3fc; }
        .es-turn-band .es-turn-mark::before { background-color: #7dd3fc; }

        /* --- Plan tags --- */
        .es-turn-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.25rem;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            border: 1px solid rgba(7, 89, 133, 0.4);
            color: #075985;
        }
        .dark .es-turn-plan { border-color: rgba(125, 211, 252, 0.42); color: #7dd3fc; }
        .es-turn-plan-pro { border-color: rgba(14, 21, 32, 0.35); color: #0e1520; }
        .dark .es-turn-plan-pro { border-color: rgba(232, 238, 245, 0.38); color: #e8eef5; }

        /* --- The ticket-type record. A real table: this content IS a record. --- */
        .es-turn-table { width: 100%; border-collapse: collapse; text-align: left; }
        .es-turn-table th,
        .es-turn-table td { padding: 0.7rem 0.6rem; vertical-align: middle; }
        .es-turn-table thead th {
            font-size: 0.64rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #4a5462;
            border-bottom: 1px solid rgba(14, 21, 32, 0.14);
        }
        .dark .es-turn-table thead th { color: #93a4b8; border-bottom-color: rgba(232, 238, 245, 0.14); }
        .es-turn-table tbody tr + tr th,
        .es-turn-table tbody tr + tr td { border-top: 1px solid rgba(14, 21, 32, 0.08); }
        .dark .es-turn-table tbody tr + tr th,
        .dark .es-turn-table tbody tr + tr td { border-top-color: rgba(232, 238, 245, 0.08); }
        .es-turn-num {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        /* --- Chips --- */
        .es-turn-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            border: 1px solid rgba(14, 21, 32, 0.16);
            background-color: rgba(255, 255, 255, 0.7);
            color: #4a5462;
            font-size: 0.76rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .dark .es-turn-chip {
            border-color: rgba(232, 238, 245, 0.16);
            background-color: rgba(232, 238, 245, 0.05);
            color: #a9b6c4;
        }

        /* --- Links and buttons --- */
        .es-turn-link { color: #075985; }
        .es-turn-link:hover { color: #0e1520; }
        .dark .es-turn-link { color: #7dd3fc; }
        .dark .es-turn-link:hover { color: #e8eef5; }
        .es-turn-btn {
            background-color: #075985;
            box-shadow: 0 18px 36px -14px rgba(7, 89, 133, 0.5);
        }
        .es-turn-btn:hover { background-color: #044866; box-shadow: 0 22px 44px -14px rgba(7, 89, 133, 0.6); }
        /* In dark mode the fill goes bright, so the label has to go dark. This
           lives here rather than as dark:text-[#08131c], because that arbitrary
           utility is not in the built CSS and would silently do nothing. */
        .dark .es-turn-btn { background-color: #7dd3fc; color: #08131c; }
        .dark .es-turn-btn:hover { background-color: #a5e2fd; }

        /* --- Dot-nav tooltip --- */
        .es-turn-tip {
            border-radius: 9999px;
            border-color: rgba(14, 21, 32, 0.12);
            background-color: #ffffff;
            color: #0e1520;
        }
        .dark .es-turn-tip {
            border-color: rgba(232, 238, 245, 0.12);
            background-color: #161d25;
            color: #cbd5e1;
        }

        /* --- Shared-system recolours (brand blue by default) --- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(7, 89, 133, 0.12), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(125, 211, 252, 0.1), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(7, 89, 133, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(125, 211, 252, 0.6); }
        .es-dot.is-active .es-dot-pip { background-color: #075985; }
        .dark .es-dot.is-active .es-dot-pip { background-color: #7dd3fc; }

        /* --- Focus rings. No border-radius here: setting it would change the
               element's own shape on focus. Outlines already follow it. --- */
        #es-turn-page a:focus-visible,
        #es-turn-page summary:focus-visible,
        #es-turn-page button:focus-visible {
            outline: 2px solid #075985;
            outline-offset: 3px;
        }
        .dark #es-turn-page a:focus-visible,
        .dark #es-turn-page summary:focus-visible,
        .dark #es-turn-page button:focus-visible {
            outline-color: #7dd3fc;
        }
        .es-turn-band a:focus-visible,
        .es-turn-band summary:focus-visible,
        .es-turn-band button:focus-visible {
            outline-color: #7dd3fc !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-turn-tick { animation: none !important; top: 42% !important; }
        }
    </style>

    @php
        // One event's ticket types, as the product actually stores them:
        // Ticket.type / price / quantity / sales_start_at / sales_end_at, with
        // the remaining count read per occurrence date via Ticket::soldKey($date).
        // Sales windows are ABSOLUTE datetimes, so they are written as dates
        // here, never as "two hours before doors".
        $ticketTypes = [
            ['Early Bird',      '$18',  '60',        'Sep 1 to Oct 30',   'Sold out',  'Volume discount at 4'],
            ['General',         '$25',  '220',       'Open',              '143 left',  ''],
            ['Student',         '$12',  '40',        'Open',              '31 left',   'Max 2 per order'],
            ['Table of six',    '$132', '12',        'Open',              '4 left',    'One ticket, priced for a group'],
            ['Parking',         '$8',   '80',        'Open',              '52 left',   'Add-on, not a ticket'],
            ['Season pass',     '$120', '50',        'Nov 1 to Nov 30',   '26 left',   'Pass: one pool, not per date'],
        ];

        // The states TicketController::scanned() can return for a ticket, in the
        // order the code checks them, and with the tone the scanner actually
        // shows. Nothing invented: each string mirrors a real messages.* key on
        // that path. WARN is deliberately not REFUSE - a second scan of a used
        // ticket comes back as an orange warning over the holder's details
        // (messages.warning_ticket_used), and no second admission is recorded.
        $gateLog = [
            ['pass', 'ADMIT',  'Sarah M. - General - first scan'],
            ['warn', 'WARN',   'This ticket has already been used'],
            ['stop', 'REFUSE', 'Not authorized to scan this ticket'],
            ['stop', 'REFUSE', 'Check-in opens 24 hours before the start'],
            ['stop', 'REFUSE', 'The check-in period has ended'],
            ['stop', 'REFUSE', 'This ticket is not paid'],
            ['stop', 'REFUSE', 'This ticket is cancelled'],
            ['stop', 'REFUSE', 'This ticket is refunded'],
        ];

        $saleSide = [
            ['Ticket types', 'Each one has its own name, price, quantity and optional sales start and end date.'],
            ['Free ticket types', 'A price of zero is a valid ticket. It still gets a QR code and it still counts against the quantity.'],
            ['Promo codes', 'Percentage or fixed amount, with usage limits, an expiry date and per-ticket targeting.'],
            ['Volume discount', 'A minimum quantity unlocks a rate on that ticket line. A group of four gets it; one buyer does not.'],
            ['A cap per order', 'Set a maximum per order on any ticket type, so one buyer cannot take the whole allocation in one go.'],
            ['Add-ons', 'Parking, merchandise, a meal package. Priced separately and never discounted by a promo code.'],
            ['Custom questions', 'Up to ten per order, plus up to ten more on any one ticket type when each guest has to answer.'],
            ['Passes and subscriptions', 'One purchase a guest reuses across many events, with its own usage rules.'],
        ];

        $doorSide = [
            ['A QR code per ticket', 'It is on the buyer\'s ticket page, which checkout lands them on, and it goes out with the confirmation email.'],
            ['Your own email sender', 'Connect one once under Schedule Settings, Integrations, Email. Until you do, the sale and the ticket page work but no confirmation email leaves.'],
            ['Individual tickets', 'Turn it on and every guest on the order gets their own email and their own code.'],
            ['Scan from a phone', 'Sales, then Scan Tickets, then point the camera. No hardware to buy or rent.'],
            ['One admission per ticket', 'A second scan comes back as an orange warning over the holder details, and no second admission is recorded.'],
            ['It refuses for a reason', 'Unpaid, cancelled, refunded, too early, too late: each one names itself.'],
            ['A live count', 'The check-in dashboard shows progress, a per-ticket-type breakdown and the last ten scans.'],
            ['The record afterwards', 'Every sale carries its check-in status into the CSV export.'],
        ];

        $afterSale = [
            ['Sale notification emails', 'An email each time a ticket sells, with the buyer, the ticket type, the amount, the status and any code used.', 'Pro'],
            ['Sales CSV export', 'Buyer, ticket type, amount, promo code, payment method, check-in status and every custom answer.', 'Pro'],
            ['Ticket waitlist', 'When a date sells out, guests can join. One person is notified at a time, with a 24 hour link to buy.', 'Pro'],
            ['Bulk attendee import', 'Paste rows or upload a CSV for people who paid out of band. Up to 5,000 in one go.', 'Pro'],
            ['Gift cards', 'Sell a balance somebody sends to a recipient by email, redeemable against tickets on your schedule.', 'Pro'],
            ['Post-event feedback', 'Star ratings and comments collected from ticket buyers and registrants after the event ends.', 'Pro'],
            ['Webhooks', 'POST notifications for sales, cancellations and check-ins, into whatever you already run.', 'Pro'],
            ['Embeddable ticket form', 'Put the purchase or RSVP form on the website you already have, in one iframe.', 'Pro'],
        ];

        $steps = [
            ['01', 'Connect a payment method', 'Your own Stripe account, Invoice Ninja, a payment URL, or cash at the door. Card money settles into your account, not ours.'],
            ['02', 'Add your ticket types', 'A name, a price, a quantity, and a sales window if you want one. Promo codes, add-ons and custom questions have their own tabs.'],
            ['03', 'Scan at the door', 'Open Sales on your phone and tap Scan Tickets. Each ticket admits once, and the dashboard keeps the count.'],
        ];

        $faqs = [
            [
                'q' => 'What are the fees for selling tickets?',
                'a' => 'Event Schedule charges zero platform fees on ticket sales. Card payments run through your own connected Stripe account, so Stripe charges you its standard processing rate directly and the rest of the ticket price is yours. Nothing is deducted by us, on any plan, at any volume.',
            ],
            [
                'q' => 'Do I need a paid plan to sell tickets?',
                'a' => 'Ticketing and QR check-in are on the Pro plan, which is $5 a month with a 7 day free trial. Free registration is not: on every plan, including Free, you can turn on RSVP registration for a free event, set a capacity limit, and still get a confirmation email with a QR code for check-in.',
            ],
            [
                'q' => 'How does QR code check-in work?',
                'a' => 'Every ticket carries its own QR code, shown on the buyer\'s ticket page and sent with the confirmation email once you have connected an email sender under Schedule Settings, Integrations, Email. At the door you open Sales on your phone, tap Scan Tickets and point the camera. Each ticket admits once: scan it again and you get a warning instead of an entry. The check-in dashboard refreshes every 10 seconds with the running count and a per-ticket-type breakdown.',
            ],
            [
                'q' => 'Is there a seat map or assigned seating?',
                'a' => 'No, and it is worth being straight about it. Event Schedule sells named ticket types with their own prices and quantities, not numbered seats. A "Table of six" ticket type prices a table and limits how many exist, but the buyer is not picking a specific seat on a chart.',
            ],
            [
                'q' => 'What payment methods are supported?',
                'a' => 'Stripe for credit cards, Apple Pay and Google Pay; Invoice Ninja for invoice or payment-link billing; a custom payment URL to send buyers to any system you already use; or cash, where you add payment instructions to the confirmation email and mark the sale paid yourself.',
            ],
            [
                'q' => 'How are refunds handled?',
                'a' => 'You cancel or refund the sale from the Sales list, and the money movement happens in your Stripe dashboard, where you keep full control of your own refund policy. Cancelling a sale returns its tickets to the pool for that date, which is also what can trigger the next waitlist notification.',
            ],
            [
                'q' => 'Can I offer promo codes or discounts?',
                'a' => 'Yes. Promo codes take a percentage or a fixed amount off, and each one can have a maximum number of uses, an expiry date, an active toggle, and a target of either all ticket types or specific ones. Every code also generates a shareable link that pre-fills it at checkout. Separately, a volume discount can reward buying several of one ticket type at once.',
            ],
            [
                'q' => 'What happens when tickets sell out?',
                'a' => 'A Join Waitlist button appears on the event page for that date. Guests leave a name and email, and when tickets come back into the pool because a sale was cancelled, refunded or expired, the next person in line is emailed a 24 hour link to buy. Only one person is notified at a time, so a single returned ticket is not blasted at the whole list; if that link expires unused, the next person is emailed instead. The waitlist is a Pro feature.',
            ],
            [
                'q' => 'How does inventory work on a recurring event?',
                'a' => 'Per occurrence date. A ticket type with 220 available has 220 for each date the event runs, so a sold-out Saturday does not close the Sunday. A pass is the deliberate exception: one QR code covers the whole series, so a pass draws from a single pool rather than a fresh one per date.',
            ],
            [
                'q' => 'Can I get notified when tickets sell, and export the data?',
                'a' => 'Both. Turn on sale notification emails under Schedule, Settings, Notifications to get an email whenever a ticket sells, with the buyer, the ticket type, the amount, the payment status and any code applied. And the Sales list exports to CSV with buyer details, amounts, promo codes, payment method, check-in status and every custom field answer, ready for a spreadsheet.',
            ],
        ];

        $dotSections = [
            ['top', 'The gate'],
            ['halves', 'Both halves'],
            ['types', 'Ticket types'],
            ['turn', 'The turn'],
            ['fee', 'Zero at the gate'],
            ['free', 'Before you upgrade'],
            ['after', 'After the sale'],
            ['faq', 'Questions'],
            ['claim', 'Start selling'],
        ];
    @endphp

    <div id="es-turn-page" class="es-turn-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the gate and its counter                            -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(80svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 26% 68%, rgba(7, 89, 133, 0.22), rgba(7, 89, 133, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 74% 30%, rgba(2, 132, 199, 0.18), rgba(2, 132, 199, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="es-turn-accent h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        <span class="es-turn-muted text-sm font-medium tracking-wide">Ticketing, with zero platform fees</span>
                    </div>

                    <h1 class="es-balance es-turn-ink mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">A ticket is only half of it.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">The other half is <span class="es-turn-accent">the turn.</span></span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-turn-muted mb-10 max-w-xl text-lg sm:text-xl">
                        Event Schedule sells the ticket, mails the QR code, refuses it at the door if it is not valid, admits it once, and keeps the count. What it takes out of the ticket price: nothing.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="{{ app_url('/sign_up') }}" class="es-turn-btn group inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Start selling tickets
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                        <a href="{{ route('marketing.docs.tickets') }}" class="glass group inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            Read the Ticketing guide
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    </div>
                </div>

                <!-- The counter plate. A turnstile's one organ is its counter. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-turn-plate p-6 sm:p-7">
                        <div class="mb-5 flex items-baseline justify-between gap-3">
                            <p class="es-turn-tag">Gate 01</p>
                            <p class="es-turn-plate-muted es-turn-read text-xs">Sat 14 Nov</p>
                        </div>

                        <p class="es-turn-plate-muted mb-2 text-xs font-semibold uppercase tracking-widest">Admitted</p>
                        <div class="es-od es-turn-count mb-6" data-odometer="142">142</div>

                        <div class="es-turn-seam mb-5" aria-hidden="true"></div>

                        <dl class="space-y-3 text-sm">
                            <div class="es-turn-reg">
                                <dt class="es-turn-plate-muted">Tickets sold</dt>
                                <dd class="es-turn-plate-ink es-turn-read">168</dd>
                            </div>
                            <div class="es-turn-reg">
                                <dt class="es-turn-plate-muted">Refused at the door</dt>
                                <dd class="es-turn-plate-ink es-turn-read">3</dd>
                            </div>
                            <div class="es-turn-reg">
                                <dt class="es-turn-plate-muted">Platform fee taken</dt>
                                <dd class="es-turn-lit es-turn-read">0.00</dd>
                            </div>
                        </dl>

                        <p class="es-turn-plate-muted mt-5 border-t border-white/[0.08] pt-4 text-xs leading-relaxed">
                            One number goes up, one stays at zero. The check-in dashboard refreshes every 10 seconds while the doors are open.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Ticket types you can actually create. -->
            <div class="es-fade-up es-d-4 mx-auto mt-14 max-w-4xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach (['General Admission', 'Early Bird', 'VIP', 'Student', 'Table', 'Free Ticket', 'Season Pass', 'Membership', 'Parking Add-on', 'Free RSVP'] as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-turn-chip">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <hr class="es-turn-perf mx-auto max-w-7xl" aria-hidden="true">

    <!-- ============================================================ -->
    <!-- 2. Both halves: the duplex either side of the turn           -->
    <!-- ============================================================ -->
    <section id="halves" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-turn-mark mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                <p class="es-turn-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Both halves</p>
                <h2 class="es-balance es-turn-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Most tools stop at the <span class="es-turn-accent">checkout page.</span>
                </h2>
                <p class="es-turn-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    A ticket comes apart into two halves: the one you sold and the one you admitted. Both live in the same place here, on the same event, counted against the same quantity.
                </p>
            </div>

            <div class="es-turn-duplex">
                <div class="es-turn-card p-6 sm:p-7" data-reveal="panel">
                    <p class="es-turn-tag mb-5">The sale side</p>
                    <dl class="space-y-4" data-reveal-group="60">
                        @foreach ($saleSide as [$sTitle, $sBody])
                            <div data-reveal>
                                <dt class="es-turn-ink text-sm font-bold">{{ $sTitle }}</dt>
                                <dd class="es-turn-muted mt-1 text-sm leading-relaxed">{{ $sBody }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                <div class="flex flex-row items-center justify-center gap-4 lg:flex-col" aria-hidden="true">
                    <div class="es-turn-rail">
                        <span class="es-turn-tick"></span>
                    </div>
                    <span class="es-turn-hub">1 ticket &middot; 1 turn</span>
                    <div class="es-turn-rail">
                        <span class="es-turn-tick"></span>
                    </div>
                </div>

                <div class="es-turn-card p-6 sm:p-7" data-reveal="panel">
                    <p class="es-turn-tag mb-5">The door side</p>
                    <dl class="space-y-4" data-reveal-group="60">
                        @foreach ($doorSide as [$dTitle, $dBody])
                            <div data-reveal>
                                <dt class="es-turn-ink text-sm font-bold">{{ $dTitle }}</dt>
                                <dd class="es-turn-muted mt-1 text-sm leading-relaxed">{{ $dBody }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </div>

            <p class="es-turn-muted mx-auto mt-10 max-w-2xl text-center text-sm" data-reveal>
                Nothing is exported between the two sides and nothing is re-keyed. The ticket that was bought is the ticket that gets scanned.
            </p>
        </div>
    </section>

    <hr class="es-turn-perf mx-auto max-w-7xl" aria-hidden="true">

    <!-- ============================================================ -->
    <!-- 3. Ticket types: a real record, in a real table              -->
    <!-- ============================================================ -->
    <section id="types" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-turn-mark mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                <p class="es-turn-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Ticket types</p>
                <h2 class="es-balance es-turn-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Name your prices. <span class="es-turn-accent">Not your seats.</span>
                </h2>
                <p class="es-turn-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Create as many ticket types as the event needs. Each one carries its own price, its own quantity, and optionally its own sales start and end date.
                </p>
            </div>

            <div class="es-turn-card p-4 sm:p-7" data-reveal="panel">
                <div class="overflow-x-auto">
                    <table class="es-turn-table">
                        <caption class="es-turn-muted mb-4 text-left text-xs">
                            One event's ticket types, with what is left for the occurrence on Sat 14 Nov.
                        </caption>
                        <thead>
                            <tr>
                                <th scope="col">Ticket type</th>
                                <th scope="col">Price</th>
                                <th scope="col" class="hidden sm:table-cell">Quantity</th>
                                <th scope="col" class="hidden md:table-cell">Sales window</th>
                                <th scope="col">Sat 14 Nov</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ticketTypes as [$tName, $tPrice, $tQty, $tWindow, $tLeft, $tNote])
                                <tr>
                                    <th scope="row" class="es-turn-ink text-sm font-bold">
                                        {{ $tName }}
                                        @if ($tNote)
                                            <span class="es-turn-muted block text-[0.65rem] font-normal">{{ $tNote }}</span>
                                        @endif
                                    </th>
                                    <td class="es-turn-ink es-turn-num text-sm font-semibold">{{ $tPrice }}</td>
                                    <td class="es-turn-muted es-turn-num hidden text-xs sm:table-cell">{{ $tQty }}</td>
                                    <td class="es-turn-muted es-turn-num hidden text-xs md:table-cell">{{ $tWindow }}</td>
                                    <td class="es-turn-muted es-turn-num text-xs">{{ $tLeft }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-8 grid gap-4 md:grid-cols-3" data-reveal-group="90">
                <div class="es-turn-card p-6" data-reveal="panel">
                    <h3 class="es-turn-ink mb-2 text-base font-bold">A window is a date, not a countdown</h3>
                    <p class="es-turn-muted text-sm leading-relaxed">A sales start and end are stored as single moments in time, so an early bird ends on the date you set. Separately, a per-event option keeps selling until the event ends rather than stopping at the start time.</p>
                </div>
                <div class="es-turn-card p-6" data-reveal="panel">
                    <h3 class="es-turn-ink mb-2 text-base font-bold">Inventory is per date</h3>
                    <p class="es-turn-muted text-sm leading-relaxed">On a recurring event, each occurrence gets its own count, so a sold-out Saturday does not close the Sunday. A combined limit can also cap the total across every ticket type at once.</p>
                </div>
                <div class="es-turn-card p-6" data-reveal="panel">
                    <h3 class="es-turn-ink mb-2 text-base font-bold">There is no seat map</h3>
                    <p class="es-turn-muted text-sm leading-relaxed">Worth being straight about. A "Table of six" type prices a table and limits how many exist, but nobody is choosing seat H14 off a chart. If you need a real seating chart, this is not it.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. The turn: the ticket, and the verdict (fixed-dark band)    -->
    <!-- ============================================================ -->
    <section id="turn" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-turn-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-6xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-turn-mark mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                    <p class="es-turn-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The turn</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        It turns <span class="es-turn-lit">once.</span>
                    </h2>
                    <p class="mt-5 text-lg es-turn-band-muted" data-reveal style="--reveal-delay: 0.15s;">
                        Point a phone camera at the code and what comes back is a decision, not a guess. Admitted on a first scan; warned if that code has already been through; refused with the reason named.
                    </p>
                </div>

                <div class="grid items-start gap-8 lg:grid-cols-[1.05fr_1fr]">
                    <!-- The ticket and its counterfoil: real stock, fixed in both modes. -->
                    <div data-reveal="panel">
                        <div class="es-turn-stub">
                            <div class="min-w-0 flex-1 p-5 sm:p-6">
                                <p class="es-turn-stub-muted text-[0.6rem] font-extrabold uppercase tracking-[0.2em]">Admit one</p>
                                <h3 class="es-turn-stub-ink mt-2 text-xl font-black leading-tight">Jazz Night</h3>
                                <p class="es-turn-stub-muted mt-1 text-sm">Sat 14 Nov &middot; The Blue Room</p>
                                <div class="mt-4 space-y-1.5 text-sm">
                                    <div class="es-turn-reg">
                                        <span class="es-turn-stub-muted">Ticket type</span>
                                        <span class="es-turn-stub-ink es-turn-read">General</span>
                                    </div>
                                    <div class="es-turn-reg">
                                        <span class="es-turn-stub-muted">Holder</span>
                                        <span class="es-turn-stub-ink es-turn-read">Sarah M.</span>
                                    </div>
                                    <div class="es-turn-reg">
                                        <span class="es-turn-stub-muted">Paid</span>
                                        <span class="es-turn-stub-accent es-turn-read">$25.00</span>
                                    </div>
                                </div>
                                <p class="es-turn-stub-muted mt-4 text-xs leading-relaxed">
                                    Ticket notes ride along in the confirmation email and on this page: directions, parking, what to bring.
                                </p>
                            </div>
                            <div class="es-turn-perf-v" aria-hidden="true"></div>
                            <div class="flex flex-none flex-col items-center justify-center gap-3 p-5">
                                <div class="es-turn-qr" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 29 29" aria-hidden="true"><path fill="#1f2937" d="M0 0h7v7H0zm2 2v3h3V2zm8 0h1v1h1v1h-1v1h-1V3h-1V2h1zm4 0h1v4h-1V4h-1V3h1V2zm4 0h3v1h-2v1h-1V2zm5 0h7v7h-7zm2 2v3h3V4zM2 10h1v1h1v1H2v-1H1v-1h1zm4 0h1v1H5v1H4v-1h1v-1h1zm3 0h1v3h1v1h-1v-1H9v-1h1v-1H9v-1zm5 0h1v2h1v-2h1v3h-1v1h-1v-1h-1v-1h-1v-1h1v-1zm5 0h1v1h-1v1h-1v-1h1v-1zm3 0h1v2h1v-1h1v3h-1v-1h-1v2h-1v-3h-1v-1h1v-1zM0 14h1v1h1v-1h2v1h-1v1h1v2H3v-2H2v-1H0v-1zm4 0h1v1H4v-1zm9 0h1v1h-1v-1zm8 0h2v1h-2v-1zm0 2v1h1v1h1v1h-1v1h1v1h-2v-2h-1v-1h1v-1h-1v-1h1zm4 0h1v1h-1v-1zM0 18h1v1H0v-1zm2 0h2v1h1v2H4v-1H3v1H2v-2h1v-1H2v-1zm5 0h3v1h1v2h-1v1h-1v-2H8v1H7v-1H6v-1h1v-1zm6 0h2v1h1v-1h1v2h-2v1h-1v-2h-1v-1zm-5 2h1v1H8v-1zM0 22h7v7H0zm2 2v3h3v-3zm9-2h1v1h-1v-1zm2 0h1v1h1v2h-2v-1h-1v-1h1v-1zm3 0h3v1h-2v2h2v1h2v2h-1v1h-2v-1h-1v1h-2v-2h1v-2h-1v-2h1v-1zm7 0h1v1h1v1h-1v3h1v-2h1v3h1v-1h1v2h-2v1h-1v-1h-1v-1h-1v1h-2v-1h1v-2h1v-1h-1v-2h1v-1zm-9 2h1v1h-1v-1zm-2 2h1v1h-1v-1zm7 0h1v1h-1v-1zm-5 2h1v1h-1v-1zm2 0h2v1h-2v-1z"/></svg>
                                </div>
                                <p class="es-turn-stub-muted es-turn-read text-[0.58rem]">ES-8F42-1176</p>
                            </div>
                        </div>

                        <p class="mt-5 text-sm leading-relaxed es-turn-band-muted">
                            Turn on individual tickets and every guest on one order gets a stub of their own: their own confirmation email, their own code, and their own line in the check-in count.
                        </p>
                    </div>

                    <!-- The gate log: the verdicts the scanner really returns. -->
                    <div data-reveal="panel">
                        <div class="es-turn-plate p-5 sm:p-6">
                            <div class="mb-4 flex items-baseline justify-between gap-3">
                                <p class="es-turn-tag">Gate log</p>
                                <p class="es-turn-plate-muted es-turn-read text-xs">doors 19:30</p>
                            </div>
                            <div class="es-turn-log text-xs">
                                @foreach ($gateLog as [$gKind, $gVerdict, $gReason])
                                    <div class="es-turn-row">
                                        <span class="es-turn-verdict @if ($gKind === 'pass') es-turn-verdict-pass @elseif ($gKind === 'warn') es-turn-verdict-warn @else es-turn-verdict-stop @endif">{{ $gVerdict }}</span>
                                        <span class="es-turn-plate-ink min-w-0">{{ $gReason }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <p class="es-turn-plate-muted mt-5 border-t border-white/[0.08] pt-4 text-xs leading-relaxed">
                                The window closes when the event ends, worked out from its start plus its duration. Scanning happens inside the admin panel, so anybody signed in to the schedule can work the door; adding team members of their own is an Enterprise feature.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-10 grid gap-4 md:grid-cols-3" data-reveal-group="90">
                    <div class="es-turn-card p-6" data-reveal="panel">
                        <h3 class="mb-2 text-base font-bold es-turn-band-ink">Progress, live</h3>
                        <p class="text-sm leading-relaxed es-turn-band-muted">A percentage bar for the whole event, a breakdown per ticket type, and the last ten check-ins with names and times.</p>
                    </div>
                    <div class="es-turn-card p-6" data-reveal="panel">
                        <h3 class="mb-2 text-base font-bold es-turn-band-ink">Filtered by date</h3>
                        <p class="text-sm leading-relaxed es-turn-band-muted">Pick the event and the occurrence date, so a recurring event's Saturday count never mixes with its Sunday one.</p>
                    </div>
                    <div class="es-turn-card p-6" data-reveal="panel">
                        <h3 class="mb-2 text-base font-bold es-turn-band-ink">No hardware</h3>
                        <p class="text-sm leading-relaxed es-turn-band-muted">Whatever phone is in your pocket. The dashboard works on the same phone, or on a tablet propped at the desk.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <hr class="es-turn-perf mx-auto max-w-7xl" aria-hidden="true">

    <!-- ============================================================ -->
    <!-- 5. Zero at the gate                                          -->
    <!-- ============================================================ -->
    <section id="fee" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-turn-mark mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                <p class="es-turn-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The fee</p>
                <h2 class="es-balance es-turn-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Nothing is taken <span class="es-turn-accent">at the gate.</span>
                </h2>
                <p class="es-turn-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Card sales run through your own connected Stripe account. Stripe charges you its standard processing rate directly, and the platform fee line is the one that stays at zero.
                </p>
            </div>

            <div class="grid items-start gap-8 lg:grid-cols-[1.05fr_1fr]">
                <div class="es-turn-card p-6 sm:p-8" data-reveal="panel">
                    <p class="es-turn-tag mb-5">One $25 ticket</p>
                    <dl class="space-y-3 text-sm">
                        <div class="es-turn-reg">
                            <dt class="es-turn-muted">Ticket price</dt>
                            <dd class="es-turn-ink es-turn-read">25.00</dd>
                        </div>
                        <div class="es-turn-reg">
                            <dt class="es-turn-muted">Event Schedule platform fee</dt>
                            <dd class="es-turn-accent es-turn-read text-base">0.00</dd>
                        </div>
                        <div class="es-turn-reg">
                            <dt class="es-turn-muted">Payment processing</dt>
                            <dd class="es-turn-muted es-turn-read text-xs">Stripe's own rate, billed by Stripe</dd>
                        </div>
                    </dl>
                    <div class="my-5" aria-hidden="true">
                        <hr class="es-turn-perf">
                    </div>
                    <p class="es-turn-ink text-sm font-semibold">The rest lands in your Stripe account.</p>
                    <p class="es-turn-muted mt-2 text-sm leading-relaxed">
                        Not in ours, and not on a payout schedule we control. Take cash at the door instead and there is nothing to process at all: add payment instructions to the confirmation email and mark the sale paid when you are handed the money.
                    </p>
                    <p class="es-turn-muted mt-4 text-xs leading-relaxed">
                        We are not going to put a competitor's fee table next to this. Check whatever you use today against your own last event and draw your own conclusion.
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2" data-reveal-group="80">
                    <a href="{{ marketing_url('/stripe') }}" data-reveal class="es-turn-card es-turn-hover group flex flex-col p-6 transition-all duration-200 hover:shadow-md">
                        <svg aria-hidden="true" class="es-turn-accent mb-4 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                        <h3 class="es-turn-hover-title es-turn-ink mb-2 text-base font-bold transition-colors">Stripe</h3>
                        <p class="es-turn-muted mb-4 text-sm leading-relaxed">Cards, Apple Pay and Google Pay, into your own connected account.</p>
                        <span class="es-turn-hover-arrow es-turn-muted mt-auto inline-flex items-center gap-1 text-xs font-semibold transition-colors">
                            Learn more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>

                    <a href="{{ marketing_url('/invoiceninja') }}" data-reveal class="es-turn-card es-turn-hover group flex flex-col p-6 transition-all duration-200 hover:shadow-md">
                        <svg aria-hidden="true" class="es-turn-accent mb-4 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        <h3 class="es-turn-hover-title es-turn-ink mb-2 text-base font-bold transition-colors">Invoice Ninja</h3>
                        <p class="es-turn-muted mb-4 text-sm leading-relaxed">Invoice or payment-link billing, for the buyers who need a real invoice.</p>
                        <span class="es-turn-hover-arrow es-turn-muted mt-auto inline-flex items-center gap-1 text-xs font-semibold transition-colors">
                            Learn more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>

                    <div data-reveal class="es-turn-card flex flex-col p-6">
                        <svg aria-hidden="true" class="es-turn-accent mb-4 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                        <h3 class="es-turn-ink mb-2 text-base font-bold">Payment URL</h3>
                        <p class="es-turn-muted text-sm leading-relaxed">Send buyers to whatever you already run, and keep the ticket record here.</p>
                    </div>

                    <div data-reveal class="es-turn-card flex flex-col p-6">
                        <svg aria-hidden="true" class="es-turn-accent mb-4 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <h3 class="es-turn-ink mb-2 text-base font-bold">Cash at the door</h3>
                        <p class="es-turn-muted text-sm leading-relaxed">Hold the ticket unpaid, then mark it paid. Unpaid holds can auto-release after a set number of hours so the seats go back on sale.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <hr class="es-turn-perf mx-auto max-w-7xl" aria-hidden="true">

    <!-- ============================================================ -->
    <!-- 6. Before you upgrade: registration is free                  -->
    <!-- ============================================================ -->
    <section id="free" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-turn-mark mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                <p class="es-turn-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Before you upgrade</p>
                <h2 class="es-balance es-turn-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    You may not need <span class="es-turn-accent">any of this.</span>
                </h2>
                <p class="es-turn-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    If the event is free and you only want a headcount, the free plan already does it, QR code and all. Read this before you pay us anything.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2" data-reveal-group="100">
                <div class="es-turn-card flex flex-col p-7" data-reveal="panel">
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <h3 class="es-turn-ink text-lg font-black">Registration</h3>
                        <span class="es-turn-plan">Free</span>
                    </div>
                    <p class="es-turn-muted mb-5 text-sm leading-relaxed">A name and an email. No payment method to connect, no ticket types to configure.</p>
                    <ul class="es-turn-muted space-y-2.5 text-sm">
                        @foreach (['A Register button on your event page', 'An optional RSVP limit, counted per occurrence date', 'A confirmation email with a QR code for check-in', 'Registration notes in that email: directions, parking, what to bring', 'Guests can cancel their own registration from the email link', 'Every registration listed on the Sales tab'] as $freeItem)
                            <li class="flex gap-2.5">
                                <svg aria-hidden="true" class="es-turn-accent mt-0.5 h-4 w-4 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span>{{ $freeItem }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="es-turn-muted mt-auto pt-5 text-xs leading-relaxed">Free on every plan. Newsletters are free too, at 10 emails a month, so you can email the people who came.</p>
                </div>

                <div class="es-turn-card flex flex-col p-7" data-reveal="panel">
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <h3 class="es-turn-ink text-lg font-black">Ticketing</h3>
                        <span class="es-turn-plan es-turn-plan-pro">Pro</span>
                    </div>
                    <p class="es-turn-muted mb-5 text-sm leading-relaxed">Everything on this page. $5 a month with a 7 day free trial, and still zero platform fees.</p>
                    <ul class="es-turn-muted space-y-2.5 text-sm">
                        @foreach (['Named ticket types with prices, quantities and sales windows', 'Promo codes, volume discounts, add-ons and gift cards', 'Passes and season subscriptions across many events', 'Custom questions at checkout, and individual tickets per guest', 'The check-in dashboard and the sold-out waitlist', 'The ticket form embedded on the website you already have'] as $proItem)
                            <li class="flex gap-2.5">
                                <svg aria-hidden="true" class="es-turn-accent mt-0.5 h-4 w-4 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span>{{ $proItem }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="es-turn-muted mt-auto pt-5 text-xs leading-relaxed">Registration and ticketing are mutually exclusive on one event. Need free and paid side by side? Add a $0 ticket type next to the paid ones.</p>
                </div>
            </div>
        </div>
    </section>

    <hr class="es-turn-perf mx-auto max-w-7xl" aria-hidden="true">

    <!-- ============================================================ -->
    <!-- 7. After the sale                                            -->
    <!-- ============================================================ -->
    <section id="after" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-turn-mark mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                <p class="es-turn-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">After the sale</p>
                <h2 class="es-balance es-turn-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    The part that happens <span class="es-turn-accent">on Monday.</span>
                </h2>
                <p class="es-turn-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    The count at the gate is only useful if it survives the night. These are the eight things the Sales list does once the doors are shut.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4" data-reveal-group="70">
                @foreach ($afterSale as [$aTitle, $aBody, $aPlan])
                    <div class="es-turn-card flex flex-col p-6" data-reveal="panel">
                        <span class="es-turn-plan es-turn-plan-pro mb-3 self-start">{{ $aPlan }}</span>
                        <h3 class="es-turn-ink mb-2 text-base font-bold">{{ $aTitle }}</h3>
                        <p class="es-turn-muted text-sm leading-relaxed">{{ $aBody }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <hr class="es-turn-perf mx-auto max-w-7xl" aria-hidden="true">

    <!-- ============================================================ -->
    <!-- 8. Three steps                                               -->
    <!-- ============================================================ -->
    <section class="scroll-mt-24 py-20 lg:py-24">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance es-turn-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal>
                    Three steps
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="120">
                @foreach ($steps as [$stepNum, $stepTitle, $stepBody])
                    <div class="es-turn-card p-7" data-reveal="panel">
                        <div class="es-turn-accent es-turn-num mb-3 text-2xl font-black">{{ $stepNum }}</div>
                        <h3 class="es-turn-ink mb-2 text-lg font-bold">{{ $stepTitle }}</h3>
                        <p class="es-turn-muted text-sm leading-relaxed">{{ $stepBody }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @include('marketing.partials.pricing-nudge')

    <!-- ============================================================ -->
    <!-- 9. Related features                                          -->
    <!-- ============================================================ -->
    <section class="py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-turn-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Stripe Payments" description="Accept credit cards, Apple Pay, and Google Pay with zero platform fees" :url="marketing_url('/stripe')" icon-color="blue">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Custom Fields" description="Collect additional info from ticket buyers with custom form fields" :url="marketing_url('/features/custom-fields')" icon-color="amber">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Send branded newsletters to followers and ticket buyers" :url="marketing_url('/features/newsletters')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Embed Calendar" description="Embed your full event calendar on any website" :url="marketing_url('/features/embed-calendar')" icon-color="blue">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Embed Tickets" description="Embed a ticket purchase or RSVP form on any website with one line of code" :url="marketing_url('/features/embed-tickets')" icon-color="blue">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-turn-link inline-flex items-center font-medium hover:underline">
                    See all features
                    <svg aria-hidden="true" class="ml-1 h-4 w-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <hr class="es-turn-perf mx-auto max-w-7xl" aria-hidden="true">

    <!-- ============================================================ -->
    <!-- 10. Keep reading                                             -->
    <!-- ============================================================ -->
    <section class="py-16 lg:py-20">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-turn-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Keep reading</h2>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3" data-reveal-group="70">
                @php
                    $keepReading = [
                        [route('marketing.docs.tickets'), 'Ticketing guide', 'Every tab, field and setting, written out.'],
                        [route('marketing.docs.subscriptions'), 'Passes and subscriptions', 'One purchase, reused across many events.'],
                        [marketing_url('/features/ai'), 'AI-powered import', 'Paste text or drop an image and get an event.'],
                        [marketing_url('/for-musicians'), 'For musicians', 'Selling to a room you booked yourself.'],
                        [marketing_url('/for-venues'), 'For venues', 'A door count for somebody else\'s show.'],
                        [marketing_url('/for-comedy-clubs'), 'For comedy clubs', 'Several shows a night, each with its own count.'],
                    ];
                @endphp
                @foreach ($keepReading as [$krHref, $krName, $krBlurb])
                    <a href="{{ $krHref }}" class="es-turn-card es-turn-hover group flex flex-col p-5 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-turn-hover-title es-turn-ink mb-2 text-sm font-bold transition-colors">{{ $krName }}</span>
                        <span class="es-turn-muted mb-3 text-xs leading-relaxed">{{ $krBlurb }}</span>
                        <span class="es-turn-hover-arrow es-turn-muted mt-auto inline-flex items-center gap-1 text-xs font-semibold transition-colors">
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
                <div class="es-turn-mark mb-6" data-reveal aria-hidden="true"><span>08</span></div>
                <h2 class="es-balance es-turn-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-turn-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    What people ask before they put their door money through somebody else's software.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-turn-card es-turn-hover group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-turn-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-turn-accent es-turn-num flex-none text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-turn-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-turn-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-turn-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
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
            <div class="es-turn-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>
                <div class="relative z-10">
                    <p class="es-turn-tag mb-4">Zero platform fees</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Sell the ticket. <span class="es-turn-lit">Keep the ticket.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg es-turn-band-muted">
                        Publishing your events and taking free registrations is free forever. Ticketing, QR check-in and the waitlist are $5 a month, and nothing is taken at the gate.
                    </p>

                    {{-- The last register. The hero's counter climbed to 142; this one never
                         moves, and it is the one the page has been arguing about all along. --}}
                    <div class="es-turn-plate mx-auto mb-10 max-w-sm p-6 text-left">
                        <p class="es-turn-plate-muted mb-2 text-xs font-semibold uppercase tracking-widest">Platform fee taken</p>
                        <div class="es-turn-count">0.00</div>
                        <div class="es-turn-seam my-5" aria-hidden="true"></div>
                        <p class="es-turn-plate-muted text-xs leading-relaxed">
                            The other register on the same machine. It counted 142 admissions tonight and charged for none of them.
                        </p>
                    </div>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-turn-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Get Started Free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="mt-6 text-sm es-turn-band-muted">No credit card required</p>
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
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full es-turn-tip border px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
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
