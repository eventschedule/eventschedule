<x-marketing-layout>
    <x-slot name="title">Sell Gift Cards for Your Events - Event Schedule</x-slot>
    <x-slot name="description">Sell balance-tracked gift cards your customers buy for someone else and redeem toward tickets for any event on your schedule. Set denominations, deliver by email, and track every card.</x-slot>
    <x-slot name="breadcrumbTitle">Gift Cards</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Gift Cards",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Event Gift Card Software",
        "operatingSystem": "Web",
        "description": "Sell balance-tracked gift cards your customers buy for someone else and redeem toward tickets for any event on your schedule. Set denominations, deliver by email, and track every card.",
        "featureList": [
            "Up to twelve denominations you choose, in one currency",
            "Emailed to the recipient with the buyer's personal message",
            "A twelve character code redeemed toward tickets at checkout",
            "A running balance that carries over between orders",
            "Applied after the volume discount and the promo code",
            "A cancelled order returns the redeemed amount to the card",
            "Every card tracked with its balance, status and redemptions",
            "Mark paid, resend, cancel or refund from the Gift cards tab",
            "Optional validity period, counted from the day payment clears",
            "Stripe, Invoice Ninja, a payment link, or cash"
        ],
        "offers": {
            "@type": "Offer",
            "price": "{{ $proMonthly }}",
            "priceCurrency": "USD",
            "description": "Selling gift cards is included in the Pro plan at ${{ $proMonthly }} per month"
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
           Gift-cards "The Gift Envelope" styles.

           CONCEPT: a gift card is the only thing on this platform with
           TWO customers. One person pays and never comes; another
           person turns up holding a code. The object that carries that
           handoff is an envelope: an addressed paper pocket, a written
           message, and a printed card tucked inside that keeps its own
           running balance. Hero = the card in the envelope. Middle =
           the handoff, buyer on one side, recipient on the other, a
           perforated seam between them. Then the back of the card: a
           real register table with a Balance column, because the
           product's actual mechanism is a decrementing balance
           (gift_cards.remaining_amount), not a one-shot coupon. Finale
           = seal it and post it.

           MATERIAL, NOT A NEW HUE. The palette rule for this rebuild is
           to keep the page's existing accent family, which was
           #2563eb -> #0ea5e9 -> #06b6d4, i.e. the shared chrome
           gradient. Cyan and sky are claimed (for-djs, for-venues,
           for-dance-groups) and the wheel is spent, so the accent stays
           BLUE and the distinctiveness comes from three other places:
             - MATERIAL: security-print card stock. The card face is a
               deep navy printed object with a fine engraved cross-hatch
               (two repeating-linear-gradients, i.e. a texture, not a
               line drawing) and a foil-ink amount. Envelope paper is a
               cool stock, one shade off the ground.
             - STRUCTURE: a duplex handoff and a real <table> register
               with a running balance, which is the feature argument.
             - TYPOGRAPHY: tabular monospace for every figure and every
               code, so amounts and the 12 character code line up like
               print on a card.

           FIXED PHYSICAL OBJECTS, pinned to render identically with
           `.dark` on and off (verified with the verifier's --bands):
             .es-gift-card  - a printed card is the same card under any
                              lighting; nothing inside it has a dark
                              variant, and its only animation moves
                              `transform` (never opacity, which the band
                              diff samples).
             .es-gift-band  - the checkout screen and the finale.
           Both carry overrides for the shared classes that secretly
           flip in dark mode (.grid-overlay, .animate-shimmer,
           .es-claim:focus-within), and neither contains .es-aurora,
           .es-glare or .es-ring-glow, all of which change between
           modes.

           MEASURED (never text-gray-500: it is 4.83 on white but only
           ~4.4 on this tinted ground; use .es-gift-muted at 7.13):
             light  ink #12161f 16.47 | muted #4b5261 7.13 / 7.59 panel
                    accent #1b45c4 7.09 / 7.54 panel / 6.54 tint
                    heading stops #123a9e 8.99 and #1b45c4 7.09
                    white on the #1b45c4 button 7.79
             dark   ink #e9ecf5 16.33 | muted #a3abbd 8.38 / 7.56 panel
                    accent #9cbcff 10.15 / 9.16 panel
                    heading stops #b9cfff 12.34 and #8fb6ff 9.46
             card   white 13.81 | #b9c9ea 8.29 | foil #dbe9ff 11.25
                    and #a8cbff 8.32, all on the #132a5e stock
             band   white 18.65 | #a3abbd 8.10 | #9cbcff 9.81, and on
                    the #182342 inner panel 16.17 / 6.71 / 8.14
           ============================================================== */

        /* --- Ground and ink ---------------------------------------- */
        .es-gift-page { background-color: #f3f4f8; color: #12161f; }
        .dark .es-gift-page { background-color: #0c0e14; color: #e9ecf5; }
        .es-gift-ink { color: #12161f; }
        .dark .es-gift-ink { color: #e9ecf5; }
        .es-gift-muted { color: #4b5261; }
        .dark .es-gift-muted { color: #a3abbd; }
        .es-gift-accent { color: #1b45c4; }
        .dark .es-gift-accent { color: #9cbcff; }
        /* Always-lit accent, for the fixed band in both colour modes. */
        .es-gift-lit { color: #9cbcff; }

        .es-gift-grad {
            background-image: linear-gradient(104deg, #123a9e, #1b45c4);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dark .es-gift-grad,
        .es-gift-band .es-gift-grad {
            background-image: linear-gradient(104deg, #b9cfff, #8fb6ff);
        }

        /* --- Surfaces --------------------------------------------- */
        .es-gift-panel {
            background-color: #fbfbfe;
            border: 1px solid rgba(18, 22, 31, 0.11);
            border-radius: 0.75rem;
        }
        .dark .es-gift-panel {
            background-color: #161a23;
            border-color: rgba(233, 236, 245, 0.13);
        }
        .es-gift-sub {
            background-color: #eaecf4;
            border-radius: 0.5rem;
        }
        .dark .es-gift-sub { background-color: #1e2330; }
        .es-gift-tint {
            background-color: #e6ebf9;
            border: 1px solid rgba(27, 69, 196, 0.22);
            border-radius: 0.75rem;
        }
        .dark .es-gift-tint {
            background-color: #182342;
            border-color: rgba(156, 188, 255, 0.26);
        }
        /* Section separators and list dividers. These are page-local rules
           rather than arbitrary-value Tailwind borders on purpose: this
           campaign never runs a build, so a
           `border-[rgba(18,22,31,0.1)]` that is not already in
           public/build/assets/marketing-app-*.css paints nothing at all. */
        .es-gift-rule-t { border-top: 1px solid rgba(18, 22, 31, 0.1); }
        .dark .es-gift-rule-t { border-top-color: rgba(233, 236, 245, 0.1); }
        .es-gift-split > * + * { border-top: 1px solid rgba(18, 22, 31, 0.1); }
        .dark .es-gift-split > * + * { border-top-color: rgba(233, 236, 245, 0.1); }
        .es-gift-micro { font-size: 0.62rem; }

        /* Dot-nav tooltip: the shared markup reaches for a hex background,
           so the surface is defined here instead. */
        .es-gift-tip {
            background-color: #ffffff;
            border: 1px solid rgba(18, 22, 31, 0.14);
            color: #12161f;
        }
        .dark .es-gift-tip {
            background-color: #161a23;
            border-color: rgba(233, 236, 245, 0.14);
            color: #e9ecf5;
        }

        .es-gift-hover { transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        .es-gift-hover:hover {
            border-color: rgba(27, 69, 196, 0.45);
            box-shadow: 0 12px 30px -20px rgba(18, 22, 31, 0.55);
        }
        .dark .es-gift-hover:hover {
            border-color: rgba(156, 188, 255, 0.42);
            box-shadow: 0 12px 30px -20px rgba(0, 0, 0, 0.85);
        }

        /* --- Eyebrow, figures, stub tag --------------------------- */
        .es-gift-tag {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #1b45c4;
        }
        .dark .es-gift-tag { color: #9cbcff; }
        .es-gift-band .es-gift-tag { color: #9cbcff; }

        .es-gift-num {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, 'Liberation Mono', monospace;
            font-variant-numeric: tabular-nums;
        }

        /* A perforated stub, the corner you tear off a gift voucher.
           The perforation is a dot texture, not a drawing. */
        .es-gift-stub {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.75rem;
            height: 2rem;
            padding-left: 0.5rem;
            border: 1px solid rgba(18, 22, 31, 0.2);
            border-radius: 0.3rem;
            background-color: rgba(18, 22, 31, 0.035);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.78rem;
            font-weight: 700;
            color: #12161f;
        }
        .dark .es-gift-stub {
            border-color: rgba(233, 236, 245, 0.22);
            background-color: rgba(233, 236, 245, 0.05);
            color: #e9ecf5;
        }
        .es-gift-band .es-gift-stub {
            border-color: rgba(233, 236, 245, 0.22);
            background-color: rgba(233, 236, 245, 0.05);
            color: #e9ecf5;
        }
        .es-gift-stub::before {
            content: "";
            position: absolute;
            top: 0.3rem;
            bottom: 0.3rem;
            left: 0.42rem;
            width: 2px;
            background-image: radial-gradient(circle, #1b45c4 0.9px, transparent 1.1px);
            background-size: 2px 5px;
        }
        .dark .es-gift-stub::before,
        .es-gift-band .es-gift-stub::before {
            background-image: radial-gradient(circle, #9cbcff 0.9px, transparent 1.1px);
        }

        /* --- The envelope ----------------------------------------- */
        .es-gift-tuck { position: relative; }
        .es-gift-env {
            position: relative;
            z-index: 2;
            margin-top: -1.6rem;
            padding: 3.5rem 1.5rem 1.5rem;
            background-color: #e8eaf3;
            border: 1px solid rgba(18, 22, 31, 0.12);
            border-radius: 0 0 0.85rem 0.85rem;
            box-shadow: 0 24px 50px -34px rgba(18, 22, 31, 0.6);
        }
        .dark .es-gift-env {
            background-color: #1a1f2b;
            border-color: rgba(233, 236, 245, 0.12);
            box-shadow: 0 24px 50px -30px rgba(0, 0, 0, 0.9);
        }
        /* The flap, folded down over the card. A shape, not an outline. */
        .es-gift-env::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3rem;
            background-image: linear-gradient(180deg, #dfe2ee, #ccd2e4);
            clip-path: polygon(0 0, 100% 0, 50% 100%);
        }
        .dark .es-gift-env::before {
            background-image: linear-gradient(180deg, #262d40, #1e2432);
        }
        .es-gift-addr {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.74rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #4b5261;
        }
        .dark .es-gift-addr { color: #a3abbd; }
        .es-gift-note {
            font-style: italic;
            color: #12161f;
            border-top: 1px dashed rgba(18, 22, 31, 0.22);
        }
        .dark .es-gift-note {
            color: #e9ecf5;
            border-top-color: rgba(233, 236, 245, 0.22);
        }

        /* --- The card: a FIXED printed object --------------------- */
        .es-gift-card {
            position: relative;
            overflow: hidden;
            z-index: 1;
            padding: 1.5rem 1.5rem 2.6rem;
            border-radius: 0.85rem;
            background-color: #132a5e;
            background-image:
                repeating-linear-gradient(58deg, rgba(255, 255, 255, 0.05) 0 1px, transparent 1px 6px),
                repeating-linear-gradient(-58deg, rgba(255, 255, 255, 0.04) 0 1px, transparent 1px 7px),
                linear-gradient(152deg, #16306b 0%, #0d1c40 100%);
            box-shadow: 0 20px 44px -26px rgba(9, 18, 44, 0.85);
            color: #ffffff;
        }
        .es-gift-card-rule { height: 1px; background-color: rgba(255, 255, 255, 0.18); }
        .es-gift-card-muted { color: #b9c9ea; }
        .es-gift-amount {
            font-size: clamp(2.5rem, 7vw, 3.5rem);
            font-weight: 900;
            line-height: 1;
            letter-spacing: -0.03em;
            font-variant-numeric: tabular-nums;
            background-image: linear-gradient(118deg, #dbe9ff, #a8cbff);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .es-gift-code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            color: #ffffff;
        }
        /* A stamp box: a bordered square of print, which is typography. */
        .es-gift-stamp {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 3.4rem;
            height: 3.9rem;
            border: 1px dashed rgba(255, 255, 255, 0.4);
            border-radius: 0.3rem;
            background-color: rgba(255, 255, 255, 0.06);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.6rem;
            line-height: 1.25;
            letter-spacing: 0.08em;
            color: #ffffff;
        }
        /* Foil sweep. Transform only: the band diff samples opacity. */
        .es-gift-shine {
            position: absolute;
            top: -60%;
            bottom: -60%;
            left: -40%;
            width: 22%;
            background-image: linear-gradient(90deg, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 0.16) 50%, rgba(255, 255, 255, 0) 100%);
            transform: rotate(14deg) translateX(0);
            animation: es-gift-sweep 7s ease-in-out infinite;
        }
        @keyframes es-gift-sweep {
            0%, 12% { transform: rotate(14deg) translateX(0); }
            60%, 100% { transform: rotate(14deg) translateX(780%); }
        }
        .es-gift-float { animation: es-gift-bob 6.5s ease-in-out infinite; }
        @keyframes es-gift-bob {
            0%, 100% { transform: translateY(0) rotate(-0.5deg); }
            50% { transform: translateY(-10px) rotate(0.5deg); }
        }

        /* --- The handoff ------------------------------------------ */
        .es-gift-hand { display: grid; gap: 1.5rem; }
        @media (min-width: 1024px) {
            .es-gift-hand {
                grid-template-columns: 1fr auto 1fr;
                align-items: stretch;
                gap: 2rem;
            }
        }

        /* The seam between the two halves of the handoff. A perforation
           texture, horizontal when stacked and vertical side by side. */
        .es-gift-seam {
            background-image: radial-gradient(circle, rgba(18, 22, 31, 0.32) 1.4px, transparent 1.7px);
            background-size: 9px 9px;
            background-repeat: repeat-x;
            background-position: center;
            height: 9px;
            width: 100%;
            align-self: center;
        }
        .dark .es-gift-seam {
            background-image: radial-gradient(circle, rgba(233, 236, 245, 0.34) 1.4px, transparent 1.7px);
        }
        @media (min-width: 1024px) {
            .es-gift-seam {
                height: auto;
                width: 9px;
                align-self: stretch;
                background-repeat: repeat-y;
            }
        }

        /* --- The register ----------------------------------------- */
        .es-gift-table {
            width: 100%;
            border-collapse: collapse;
            font-variant-numeric: tabular-nums;
        }
        .es-gift-table th,
        .es-gift-table td {
            padding: 0.7rem 0.5rem;
            text-align: left;
            vertical-align: baseline;
        }
        .es-gift-table thead th {
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #4b5261;
            border-bottom: 1px solid rgba(18, 22, 31, 0.16);
        }
        .dark .es-gift-table thead th { color: #a3abbd; border-bottom-color: rgba(233, 236, 245, 0.18); }
        .es-gift-table tbody tr + tr th,
        .es-gift-table tbody tr + tr td { border-top: 1px dashed rgba(18, 22, 31, 0.16); }
        .dark .es-gift-table tbody tr + tr th,
        .dark .es-gift-table tbody tr + tr td { border-top-color: rgba(233, 236, 245, 0.16); }
        .es-gift-table tfoot th,
        .es-gift-table tfoot td {
            border-top: 2px solid rgba(18, 22, 31, 0.2);
            font-weight: 700;
        }
        .dark .es-gift-table tfoot th,
        .dark .es-gift-table tfoot td { border-top-color: rgba(233, 236, 245, 0.22); }
        .es-gift-figure {
            text-align: right;
            white-space: nowrap;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        }
        .es-gift-out { color: #12161f; }
        .dark .es-gift-out { color: #e9ecf5; }
        .es-gift-in { color: #1b45c4; }
        .dark .es-gift-in { color: #9cbcff; }

        /* --- Chips and plan tags ---------------------------------- */
        .es-gift-chip {
            display: inline-flex;
            align-items: center;
            border: 1px solid rgba(18, 22, 31, 0.2);
            border-radius: 999px;
            padding: 0.1rem 0.55rem;
            font-size: 0.66rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #12161f;
            white-space: nowrap;
        }
        .dark .es-gift-chip { border-color: rgba(233, 236, 245, 0.24); color: #e9ecf5; }
        /* Plan tiers ONLY. Never reuse these for a card state. */
        .es-gift-plan {
            display: inline-flex;
            align-items: center;
            border: 1px solid transparent;
            border-radius: 999px;
            padding: 0.1rem 0.5rem;
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        .es-gift-plan-free { border-color: rgba(18, 22, 31, 0.22); color: #4b5261; }
        .dark .es-gift-plan-free { border-color: rgba(233, 236, 245, 0.26); color: #a3abbd; }
        .es-gift-plan-pro {
            border-color: rgba(27, 69, 196, 0.5);
            background-color: #e6ebf9;
            color: #1b45c4;
        }
        .dark .es-gift-plan-pro {
            border-color: rgba(156, 188, 255, 0.45);
            background-color: #182342;
            color: #9cbcff;
        }

        /* --- Buttons ---------------------------------------------- */
        .es-gift-btn {
            background-color: #1b45c4;
            color: #ffffff;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .es-gift-btn:hover {
            background-color: #1739a8;
            transform: translateY(-1px);
            box-shadow: 0 16px 30px -18px rgba(23, 57, 168, 0.95);
        }
        .es-gift-ghost {
            border: 1px solid rgba(18, 22, 31, 0.22);
            color: #12161f;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }
        .es-gift-ghost:hover { border-color: rgba(27, 69, 196, 0.5); background-color: #e6ebf9; }
        .dark .es-gift-ghost { border-color: rgba(233, 236, 245, 0.24); color: #e9ecf5; }
        .dark .es-gift-ghost:hover { border-color: rgba(156, 188, 255, 0.45); background-color: #182342; }

        /* --- The fixed band: a screen, dark in both modes ---------
           A resolvable background-color sits under the gradient, so
           text over it is scored against something real. */
        .es-gift-band {
            background-color: #0e1220;
            background-image:
                radial-gradient(ellipse 75% 55% at 50% 0%, rgba(27, 69, 196, 0.34), rgba(27, 69, 196, 0) 70%),
                linear-gradient(180deg, #131829, #0e1220);
        }
        .es-gift-band-panel {
            background-color: #182342;
            border: 1px solid rgba(156, 188, 255, 0.2);
            border-radius: 0.75rem;
        }
        .es-gift-band-sub {
            background-color: #131a31;
            border-radius: 0.5rem;
        }
        .es-gift-band-rule { height: 1px; background-color: rgba(233, 236, 245, 0.16); }

        /* The finale is the same envelope, sealed and franked: the band
           carries the flap on its top edge (the hero's clip-path idiom,
           inverted into the dark stock) and a stamp cancelled by
           postmark bars. Both use fixed values, because the band is a
           pinned object. `noise::before` is already taken by the shared
           texture, so the flap has to be `::after`. */
        .es-gift-seal { padding-top: 5.5rem; }
        @media (min-width: 1024px) {
            .es-gift-seal { padding-top: 7.5rem; }
        }
        .es-gift-seal::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3.25rem;
            background-image: linear-gradient(180deg, #1c2848, #141b30);
            clip-path: polygon(0 0, 100% 0, 50% 100%);
            pointer-events: none;
            z-index: 1;
        }
        /* Franked stamp: the hero's stamp box, cancelled by postmark
           bars. Bars are abstract strokes, not a drawing. */
        .es-gift-frank { position: relative; display: inline-flex; }
        .es-gift-frank::after {
            content: "";
            position: absolute;
            top: 50%;
            left: -1.5rem;
            right: -1.5rem;
            height: 1.5rem;
            transform: translateY(-50%) rotate(-8deg);
            background-image: repeating-linear-gradient(180deg, rgba(255, 255, 255, 0.17) 0 1.5px, transparent 1.5px 9px);
            pointer-events: none;
        }

        /* Nothing inside the band or the card may change between colour
           modes. These three shared classes carry their own .dark rules
           in marketing.css and are invisible to a grep of this file. */
        .es-gift-band .grid-overlay {
            background-image:
                linear-gradient(rgba(233, 236, 245, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(233, 236, 245, 0.05) 1px, transparent 1px);
        }
        .es-gift-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-gift-band .es-claim:focus-within {
            border-color: rgba(156, 188, 255, 0.75);
            box-shadow: 0 0 0 4px rgba(156, 188, 255, 0.22);
        }

        /* Shared chrome that is hard-coded brand blue. */
        .es-dot:hover .es-dot-pip { background-color: rgba(27, 69, 196, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(156, 188, 255, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #1b45c4; }
        .dark .es-dot.is-active .es-dot-pip { background: #9cbcff; }

        /* Focus rings. Never set a radius here: an outline already
           follows the element's own corners. */
        #es-gift-page a:focus-visible,
        #es-gift-page summary:focus-visible,
        #es-gift-page button:focus-visible,
        #es-gift-page input:focus-visible {
            outline: 2px solid #1b45c4;
            outline-offset: 2px;
        }
        .dark #es-gift-page a:focus-visible,
        .dark #es-gift-page summary:focus-visible,
        .dark #es-gift-page button:focus-visible,
        .dark #es-gift-page input:focus-visible {
            outline-color: #9cbcff;
        }
        .es-gift-band a:focus-visible,
        .es-gift-band summary:focus-visible,
        .es-gift-band button:focus-visible,
        .es-gift-band input:focus-visible {
            outline-color: #9cbcff !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-gift-shine, .es-gift-float { animation: none !important; }
            .es-gift-shine { transform: rotate(14deg) translateX(240%); }
            .es-gift-btn:hover { transform: none; }
        }
    </style>

    @php
        // ONE card runs through the whole page, so no two figures can
        // disagree. Its life:
        //   issued $100 -> spends $40 on Friday Jazz -> spends its last
        //   $60 on the Sunday Matinee (an $81 order, so the buyer tops
        //   up $21) -> that order is cancelled, and the $60 goes back.
        // The code is a real GiftCard::generateCode() shape: 12
        // characters from an alphabet with no I, O, 0 or 1, printed in
        // three groups of four by GiftCard::formattedCode().
        $card = [
            'schedule' => 'Harbour Lights Sessions',
            'amount' => '$100',
            'code' => 'KM4T-9PQX-H2RB',
            'buyer' => 'Dana',
            'recipient' => 'Alex',
            'recipient_email' => 'alex@example.com',
            'message' => 'For the Friday sessions. Pick whichever ones you like.',
            'valid_until' => 'Mar 14, 2027',
        ];

        // The register: entry, what happened, what moved, balance after.
        // 'in' rows are money going ON to the card.
        $register = [
            ['Issued', 'Payment confirmed, card emailed to Alex', '+$100', '$100', true],
            ['Redeemed', '2 tickets, Friday Jazz Session', '-$40', '$60', false],
            ['Redeemed', '3 tickets, Sunday Matinee', '-$60', '$0', false],
            ['Returned', 'Sunday Matinee order cancelled', '+$60', '$60', true],
        ];

        // The order the register's third row came from. The gift card is
        // applied last, against what is left after the promo code.
        $order = [
            ['3 tickets, Sunday Matinee', '$90', false],
            ['Promo code SPRING10', '-$9', false],
            ['Gift card KM4T-9PQX-H2RB', '-$60', true],
        ];

        // Every state a card can be in, as the Gift cards tab shows it.
        $states = [
            ['Pending payment', 'Bought, not paid for yet. A cash card sits here until you mark it paid.'],
            ['Active', 'Paid for, in date, with balance left. This is the only state that can be spent.'],
            ['Used up', 'The balance reached zero, so there is nothing left to spend. The card stays open, because a cancelled order puts its money back on.'],
            ['Expired', 'Past its validity date. Set no validity period and a card never lands here.'],
            ['Cancelled', 'Stopped by you, or abandoned at the payment step. It cannot be spent again.'],
            ['Refunded', 'Stopped by you after the money came in. Past redemptions are kept.'],
            ['Payment review', 'What arrived did not match the face value, so it waits for you to check the charge.'],
        ];

        // The buyer picks from a fixed list, so the mock is a fixed list.
        // The second value marks the one shown as chosen.
        $amountChips = [
            ['$25', false],
            ['$50', false],
            ['$100', true],
            ['$150', false],
            ['$250', false],
        ];

        $settings = [
            ['Amounts', 'The denominations buyers can pick, up to twelve of them. There is no free-entry box: an amount that is not on your list is refused.'],
            ['Currency', 'One currency per schedule. A card can only be spent at events priced in it, and the checkout says so plainly if they do not match.'],
            ['Valid for', 'A number of days, or empty for no expiry at all. The clock starts when the payment clears, so a card waiting on cash does not quietly burn its validity.'],
            ['How they pay', 'Stripe, Invoice Ninja, a payment link, or cash. Cash cards stay pending until you mark them paid, and only then is the recipient emailed.'],
        ];

        $actions = [
            ['Mark paid', 'Activate a cash card once the money is in your hand. That is the moment the recipient is emailed the code.'],
            ['Resend email', 'Send the card to the recipient again when it is lost in a mailbox somewhere.'],
            ['Cancel', 'Stop a card being redeemed, whether it was ever paid for or not. Once cancelled it cannot be spent again.'],
            ['Refund', 'Stop a paid card. Past redemptions stay as they are, and you move the money in your own payment provider.'],
        ];

        $faqs = [
            [
                'q' => 'What can a gift card be spent on?',
                'a' => 'A gift card can be redeemed toward tickets for any event on the schedule that sold it, including events you add later. It works when the event is priced in the same currency as the card.',
            ],
            [
                'q' => 'How does the recipient receive the gift card?',
                'a' => 'The recipient is emailed the gift card with its code and the buyer\'s personal message, and the buyer gets a receipt carrying the same code as a backup. At checkout the recipient enters the code and the balance is deducted from their order right away.',
            ],
            [
                'q' => 'What if the order costs more or less than the balance?',
                'a' => 'If the order costs less, the remainder stays on the card for next time. If it costs more, the card covers what it can and the customer pays the difference with any normal payment method. If they later cancel that order, the amount the card paid goes back on to it.',
            ],
            [
                'q' => 'Which plan includes gift cards?',
                'a' => 'Selling gift cards is a Pro feature, at $'.$proMonthly.' a month. Redeeming a card that has already been bought always works, even if you later turn selling off, because a sold card is an outstanding liability rather than a feature.',
            ],
            [
                'q' => 'Do gift cards expire?',
                'a' => 'Only if you choose. Set a validity period and a card expires that many days after its payment is confirmed, or leave it empty and it never expires. Buyers see the expiry rule on the purchase page before they pay.',
            ],
        ];

        $dotSections = [
            ['top', 'The envelope'],
            ['handoff', 'The handoff'],
            ['register', 'The register'],
            ['checkout', 'At checkout'],
            ['setup', 'What you set'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];
    @endphp

    <div id="es-gift-page" class="es-gift-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the card in the envelope                            -->
    <!-- ============================================================ -->
    {{-- The nav overlays the top of the page, so the hero carries its own
         top padding rather than letting the envelope sit under it. --}}
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden pb-16 pt-28">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 28% 30%, rgba(27, 69, 196, 0.24), rgba(27, 69, 196, 0) 62%); opacity: 0.5;"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 72% 60%, rgba(156, 188, 255, 0.18), rgba(156, 188, 255, 0) 62%); opacity: 0.45;"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:72px_72px] [mask-image:radial-gradient(ellipse_72%_62%_at_50%_38%,black_22%,transparent_74%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                <div>
                    <p class="es-gift-tag es-fade-up es-d-1 mb-5">Gift cards</p>

                    <h1 class="es-balance mb-7 text-[2.6rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">Sold to one person.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="es-gift-grad">Spent by another</span>.</span></span>
                    </h1>

                    <p class="es-gift-muted es-fade-up es-d-2 mb-9 max-w-xl text-lg sm:text-xl">
                        A gift card is the only thing you sell that has two customers: the one who pays
                        and the one who turns up. You set the amounts. They write the message. The
                        balance follows the code until it runs out.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ app_url('/sign_up') }}" class="es-gift-btn inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            Get started free
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                        <a href="#register" class="es-gift-ghost inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            See how the balance works
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                    </div>
                </div>

                <!-- The object: a printed card tucked into an addressed
                     envelope. The card is pinned to render identically in
                     both colour modes. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-gift-tuck es-gift-float mx-auto max-w-md">
                        <div class="es-gift-card">
                            <div class="es-gift-shine" aria-hidden="true"></div>
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="es-gift-num es-gift-micro es-gift-card-muted font-bold uppercase tracking-[0.22em]">Gift card</p>
                                    <p class="mt-1 text-sm font-semibold text-white">{{ $card['schedule'] }}</p>
                                </div>
                                <div class="es-gift-stamp shrink-0" aria-hidden="true">
                                    <span>ANY</span>
                                    <span>EVENT</span>
                                </div>
                            </div>

                            <p class="es-gift-amount mt-6">{{ $card['amount'] }}</p>
                            <p class="es-gift-card-muted mt-2 text-xs">Balance remaining</p>

                            <div class="es-gift-card-rule my-5" aria-hidden="true"></div>

                            <p class="es-gift-card-muted es-gift-micro font-bold uppercase tracking-[0.2em]">Code</p>
                            <p class="es-gift-code mt-1" dir="ltr">{{ $card['code'] }}</p>
                            <p class="es-gift-card-muted es-gift-num mt-4 text-xs">Valid until {{ $card['valid_until'] }}</p>
                        </div>

                        <div class="es-gift-env">
                            <p class="es-gift-addr">To {{ $card['recipient'] }} &middot; {{ $card['recipient_email'] }}</p>
                            <p class="es-gift-addr mt-1">From {{ $card['buyer'] }}</p>
                            <p class="es-gift-note mt-4 pt-4 text-sm">&ldquo;{{ $card['message'] }}&rdquo;</p>
                        </div>
                    </div>

                    <p class="es-gift-muted mt-6 text-center text-xs">
                        Twelve characters, printed in three groups of four. No I, O, 0 or 1 in the alphabet, so nobody mistypes it.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The handoff (01)                                          -->
    <!-- ============================================================ -->
    <section id="handoff" class="es-gift-rule-t scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-gift-stub mb-6" data-reveal aria-hidden="true"><span>01</span></div>
                <p class="es-gift-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The handoff</p>
                <h2 class="es-balance es-gift-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Two people, <span class="es-gift-grad">one code</span>.
                </h2>
                <p class="es-gift-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    The buyer never uses what they paid for, so the form asks for both of them by name.
                    Everything after that follows the code.
                </p>
            </div>

            <div class="es-gift-hand" data-reveal-group="110">
                <div class="es-gift-panel es-gift-hover p-6 sm:p-7" data-reveal="left">
                    <p class="es-gift-tag mb-3">The buyer pays</p>
                    <h3 class="es-gift-ink mb-4 text-xl font-bold">{{ $card['buyer'] }}, who is not coming</h3>
                    <ul class="space-y-3">
                        @foreach ([
                            'Picks one of your amounts on the gift card page.',
                            'Leaves their own name and email, so the receipt has somewhere to go.',
                            'Names the recipient, and can write a personal message of up to 500 characters.',
                            'Pays with whichever method you chose.',
                        ] as $step)
                            <li class="flex items-start gap-2.5">
                                <svg aria-hidden="true" class="es-gift-accent mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-gift-muted text-sm">{{ $step }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="es-gift-seam" aria-hidden="true"></div>

                <div class="es-gift-panel es-gift-hover p-6 sm:p-7" data-reveal="right">
                    <p class="es-gift-tag mb-3">The recipient spends</p>
                    <h3 class="es-gift-ink mb-4 text-xl font-bold">{{ $card['recipient'] }}, who is</h3>
                    <ul class="space-y-3">
                        @foreach ([
                            'Gets an email holding the card, the code and the message.',
                            'Opens a page showing the balance, the original value and any expiry date.',
                            'Enters the code at checkout for any event on the schedule.',
                            'Comes back with the same code until the balance is gone.',
                        ] as $step)
                            <li class="flex items-start gap-2.5">
                                <svg aria-hidden="true" class="es-gift-accent mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-gift-muted text-sm">{{ $step }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-3" data-reveal-group="90">
                @foreach ([
                    ['Three emails leave', 'The recipient gets the card. The buyer gets a receipt, with the code on it as a backup. You get a sale notification, if you have new-sale emails switched on.'],
                    ['Or the same person twice', 'Send to myself makes the buyer the recipient, which is how a regular tops up their own credit rather than buying a present.'],
                    ['Nothing to print or post', 'The code is the card. There is no plastic to order, no stock to hold, and nothing to reprint when somebody loses it.'],
                ] as [$t, $d])
                    <div class="es-gift-panel es-gift-hover p-6" data-reveal>
                        <h3 class="es-gift-ink mb-2 text-base font-bold">{{ $t }}</h3>
                        <p class="es-gift-muted text-sm">{{ $d }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The register (02)                                         -->
    <!-- ============================================================ -->
    <section id="register" class="es-gift-rule-t scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-gift-stub mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                <p class="es-gift-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The register</p>
                <h2 class="es-balance es-gift-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    A balance, <span class="es-gift-grad">not a coupon</span>.
                </h2>
                <p class="es-gift-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Every code carries its own running total. Spend less than it holds and the rest
                    waits for next time. Spend more and it pays what it can. Cancel the order and the
                    money goes back on.
                </p>
            </div>

            <div class="es-gift-panel mx-auto max-w-3xl overflow-hidden p-5 sm:p-7" data-reveal="panel">
                <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                    <h3 class="es-gift-ink text-lg font-bold">One card, four entries</h3>
                    <span class="es-gift-num es-gift-muted text-xs" dir="ltr">{{ $card['code'] }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="es-gift-table text-sm">
                        <caption class="sr-only">The life of one $100 gift card: what was issued, what each order took off it, and the balance left after every entry.</caption>
                        <thead>
                            <tr>
                                <th scope="col">Entry</th>
                                <th scope="col">What happened</th>
                                <th scope="col" class="es-gift-figure">Amount</th>
                                <th scope="col" class="es-gift-figure">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($register as [$entry, $what, $amount, $balance, $isIn])
                                <tr>
                                    <th scope="row" class="es-gift-ink whitespace-nowrap font-bold">{{ $entry }}</th>
                                    <td class="es-gift-muted">{{ $what }}</td>
                                    <td class="es-gift-figure {{ $isIn ? 'es-gift-in' : 'es-gift-out' }} font-semibold">{{ $amount }}</td>
                                    <td class="es-gift-figure es-gift-ink font-semibold">{{ $balance }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th scope="row" class="es-gift-ink whitespace-nowrap">Balance</th>
                                <td class="es-gift-muted text-xs font-normal">Active, and spendable at the next event</td>
                                <td class="es-gift-figure es-gift-muted text-xs font-normal">of $100</td>
                                <td class="es-gift-figure es-gift-accent">$60</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <p class="es-gift-muted es-gift-rule-t mt-5 pt-4 text-xs">
                    That third entry is an $81 order the card could not cover on its own. It paid its
                    last $60 and the customer paid the remaining $21. When the order was cancelled,
                    the $60 went back on to the card rather than evaporating.
                </p>
            </div>

            <div class="mx-auto mt-6 grid max-w-3xl gap-4 sm:grid-cols-2" data-reveal-group="90">
                <div class="es-gift-panel es-gift-hover p-6" data-reveal>
                    <h3 class="es-gift-ink mb-2 text-base font-bold">Open a card, see its whole story</h3>
                    <p class="es-gift-muted text-sm">
                        The Gift cards tab on your Sales page lists every card you have sold. Open one
                        and you get the buyer, the recipient, the message they wrote, and every order
                        the card was spent on.
                    </p>
                </div>
                <div class="es-gift-panel es-gift-hover p-6" data-reveal>
                    <h3 class="es-gift-ink mb-2 text-base font-bold">What you still owe, in one figure</h3>
                    <p class="es-gift-muted text-sm">
                        The tab header totals the outstanding balance across every active card, per
                        currency. That is money already collected and not yet delivered, which is
                        worth knowing before you read the month's takings.
                    </p>
                </div>
            </div>

            <!-- Every state a card can be in -->
            <div class="mx-auto mt-10 max-w-3xl" data-reveal>
                <h3 class="es-gift-ink mb-4 text-center text-lg font-bold">Seven states, and no others</h3>
                <dl class="es-gift-panel es-gift-split p-2">
                    @foreach ($states as [$state, $meaning])
                        <div class="flex flex-col gap-1 p-3 sm:flex-row sm:items-baseline sm:gap-4">
                            <dt class="w-40 shrink-0"><span class="es-gift-chip">{{ $state }}</span></dt>
                            <dd class="es-gift-muted text-sm">{{ $meaning }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            <!-- Owner actions -->
            <div class="mx-auto mt-6 grid max-w-3xl gap-4 sm:grid-cols-2" data-reveal-group="80">
                @foreach ($actions as [$name, $detail])
                    <div class="es-gift-sub p-4" data-reveal>
                        <p class="es-gift-ink text-sm font-bold">{{ $name }}</p>
                        <p class="es-gift-muted mt-1 text-sm">{{ $detail }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. At checkout (03, fixed dark band)                         -->
    <!-- ============================================================ -->
    <section id="checkout" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-gift-band noise relative overflow-hidden rounded-[2rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="es-gift-stub mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                    <p class="es-gift-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">At checkout</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        The code goes in <span class="es-gift-grad">last</span>.
                    </h2>
                    <p class="mt-5 text-lg text-gray-300" data-reveal style="--reveal-delay: 0.15s;">
                        A gift card is a payment instrument, not a discount, so it is applied after
                        everything else has been worked out: the volume discount, then the promo
                        code, then the card against whatever is left.
                    </p>
                </div>

                <div class="grid items-start gap-8 lg:grid-cols-2 lg:gap-12">
                    <div class="es-gift-band-panel p-6 sm:p-7" data-reveal="panel">
                        <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                            <h3 class="text-lg font-bold text-white">Order summary</h3>
                            <span class="es-gift-lit es-gift-num text-xs">Sunday Matinee</span>
                        </div>

                        <dl class="space-y-2.5">
                            @foreach ($order as [$label, $figure, $isCard])
                                @php
                                    $rowLabelClass = $isCard ? 'es-gift-lit' : 'text-gray-300';
                                    $rowFigureClass = $isCard ? 'es-gift-lit' : 'text-white';
                                @endphp
                                <div class="es-gift-band-sub flex items-baseline justify-between gap-4 p-3.5">
                                    <dt class="{{ $rowLabelClass }} min-w-0 text-sm" dir="ltr">{{ $label }}</dt>
                                    <dd class="es-gift-num {{ $rowFigureClass }} shrink-0 text-sm font-semibold">{{ $figure }}</dd>
                                </div>
                            @endforeach
                        </dl>

                        <div class="es-gift-band-rule my-5" aria-hidden="true"></div>

                        <div class="flex items-baseline justify-between gap-4">
                            <p class="text-base font-bold text-white">Left to pay</p>
                            <p class="es-gift-num text-2xl font-black text-white">$21</p>
                        </div>
                        <p class="es-gift-num mt-3 text-xs text-gray-400">Card balance after this order: $0</p>
                    </div>

                    <div class="space-y-4" data-reveal-group="100">
                        @foreach ([
                            ['Costs less than the card', 'The difference stays on the code, and the same code works again at the next event.'],
                            ['Costs more than the card', 'The card covers what it can and the rest is a normal payment, so nobody has to buy a second gift card to make up the gap. If what is left would fall under the card processor\'s minimum charge, that sliver stays on the card instead of becoming a payment it would refuse.'],
                            ['Covers the whole order', 'Checkout finishes with nothing left to pay, and the ticket is issued the same way it would be on any other sale.'],
                            ['A code that will not work stops the order', 'An expired, empty or cancelled card is not quietly ignored and the buyer charged full price. Checkout says what is wrong and waits.'],
                            ['Same schedule, same currency', 'A card is valid at events on the schedule that sold it, priced in the card\'s currency. The checkout names both currencies when they do not match.'],
                        ] as [$t, $d])
                            <div class="es-gift-band-panel p-5" data-reveal>
                                <h3 class="mb-1.5 text-base font-bold text-white">{{ $t }}</h3>
                                <p class="text-sm text-gray-400">{{ $d }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. What you set (04)                                         -->
    <!-- ============================================================ -->
    <section id="setup" class="es-gift-rule-t scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-gift-stub mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                <p class="es-gift-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">What you set</p>
                <h2 class="es-balance es-gift-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Four decisions, <span class="es-gift-grad">then it sells itself</span>.
                </h2>
                <p class="es-gift-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Everything lives in the Gift Cards section of your schedule's Edit page.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4" data-reveal-group="100">
                @foreach ($settings as $si => [$name, $detail])
                    <div class="es-gift-panel es-gift-hover flex flex-col p-6" data-reveal>
                        <p class="es-gift-num es-gift-accent mb-3 text-xs font-bold">0{{ $si + 1 }}</p>
                        <h3 class="es-gift-ink mb-2 text-lg font-bold">{{ $name }}</h3>
                        <p class="es-gift-muted text-sm">{{ $detail }}</p>
                    </div>
                @endforeach
            </div>

            <!-- The denominations, as a buyer sees them -->
            <div class="mx-auto mt-8 max-w-3xl" data-reveal>
                <div class="es-gift-panel p-6 sm:p-7">
                    <div class="mb-4 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-gift-ink text-base font-bold">What the buyer chooses from</h3>
                        <span class="es-gift-muted text-xs">Your list, up to twelve amounts</span>
                    </div>
                    <div class="flex flex-wrap gap-2" aria-hidden="true">
                        @foreach ($amountChips as [$amount, $isChosen])
                            @php $chipClass = $isChosen ? 'es-gift-tint es-gift-accent' : 'es-gift-sub es-gift-ink'; @endphp
                            <span class="es-gift-num {{ $chipClass }} rounded-lg px-3.5 py-2 text-sm font-bold">{{ $amount }}</span>
                        @endforeach
                    </div>
                    <p class="es-gift-muted mt-4 text-sm">
                        Whatever a buyer sends back, it is checked against this list before a card is
                        created. There is no box to type an amount into, so nobody can invent a
                        denomination you never offered.
                    </p>
                </div>
            </div>

            <!-- Where people find it -->
            <div class="mt-6 grid gap-4 md:grid-cols-3" data-reveal-group="90">
                @foreach ([
                    ['A button on your schedule page', 'Once gift cards are on, a Gift Cards button appears alongside the other actions at the top of your public schedule page.'],
                    ['A line by the ticket selector', 'On event pages a small link sits next to the tickets, for the person who came to buy and leaves buying a present instead.'],
                    ['A link you can post anywhere', 'The settings page gives you the purchase link to copy. Put it in a newsletter, a bio, or a pinned post in December.'],
                ] as [$t, $d])
                    <div class="es-gift-panel es-gift-hover p-6" data-reveal>
                        <h3 class="es-gift-ink mb-2 text-base font-bold">{{ $t }}</h3>
                        <p class="es-gift-muted text-sm">{{ $d }}</p>
                    </div>
                @endforeach
            </div>

            <!-- Plan honesty -->
            <div class="es-gift-tint mx-auto mt-10 max-w-3xl p-6" data-reveal>
                <div class="mb-3 flex flex-wrap items-center gap-2">
                    <span class="es-gift-plan es-gift-plan-pro">Pro</span>
                    <span class="es-gift-ink text-sm font-bold">Selling gift cards is on the Pro plan, at ${{ $proMonthly }} a month.</span>
                </div>
                <p class="es-gift-muted text-sm">
                    The money is collected on your own payment account and Event Schedule takes no cut
                    of it. Redemption is treated differently from selling: a card that has already been
                    bought stays spendable even if you switch selling off, because a sold card is an
                    outstanding liability rather than a feature. On the hosted service the card is
                    delivered from your schedule's own email settings, so those have to be filled in
                    before the purchase page will open for anybody.
                </p>
                <p class="mt-3">
                    <span class="es-gift-plan es-gift-plan-free">Free</span>
                    <span class="es-gift-muted ml-2 text-sm">The schedule itself, its public page and its link cost nothing.</span>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Related features                                          -->
    <!-- ============================================================ -->
    <section class="es-gift-rule-t py-16">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-gift-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3" data-reveal-group="70">
                <a href="{{ marketing_url('/features/ticketing') }}" data-reveal class="es-gift-panel es-gift-hover group flex items-center justify-between gap-3 p-5">
                    <div>
                        <div class="es-gift-muted text-xs">The thing it is spent on</div>
                        <div class="es-gift-ink text-base font-semibold">Ticketing</div>
                    </div>
                    <svg aria-hidden="true" class="es-gift-accent h-5 w-5 shrink-0 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
                <a href="{{ route('marketing.docs.subscriptions') }}" data-reveal class="es-gift-panel es-gift-hover group flex items-center justify-between gap-3 p-5">
                    <div>
                        <div class="es-gift-muted text-xs">The other prepaid thing</div>
                        <div class="es-gift-ink text-base font-semibold">Subscriptions &amp; Passes</div>
                    </div>
                    <svg aria-hidden="true" class="es-gift-accent h-5 w-5 shrink-0 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
                <a href="{{ route('marketing.docs.gift_cards') }}" data-reveal class="es-gift-panel es-gift-hover group flex items-center justify-between gap-3 p-5">
                    <div>
                        <div class="es-gift-muted text-xs">Step by step</div>
                        <div class="es-gift-ink text-base font-semibold">Gift Cards guide</div>
                    </div>
                    <svg aria-hidden="true" class="es-gift-accent h-5 w-5 shrink-0 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-gift-accent inline-flex items-center font-medium hover:underline">
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
    <!-- 7. FAQ (05)                                                  -->
    <!-- ============================================================ -->
    <section id="faq" class="es-gift-rule-t scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-gift-stub mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                <p class="es-gift-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Questions</p>
                <h2 class="es-balance es-gift-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Asked before <span class="es-gift-grad">the first one sells</span>.
                </h2>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ($faqs as $faq)
                    <details name="faq" data-reveal class="es-gift-panel group/faq overflow-hidden">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-6">
                            <h3 class="es-gift-ink text-lg font-semibold">{{ $faq['q'] }}</h3>
                            <svg aria-hidden="true" class="es-gift-muted h-5 w-5 shrink-0 transition-transform duration-300 group-open/faq:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="es-gift-muted faq-answer px-6 pb-6">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <x-seo.faq-schema :items="$faqs" />

    <!-- ============================================================ -->
    <!-- 8. Finale: seal it                                           -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-gift-band es-gift-seal noise relative overflow-hidden rounded-[2rem] border border-white/10 px-6 pb-16 text-center shadow-2xl sm:px-12 lg:pb-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-25"></div>
                </div>

                <div class="relative z-10">
                    <div class="es-gift-frank mb-7" aria-hidden="true">
                        <span class="es-gift-stamp">
                            <span>0%</span>
                            <span>TO US</span>
                        </span>
                    </div>
                    <p class="es-gift-tag mb-6">Free to start</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                        Let somebody buy the night <span class="es-gift-grad">for somebody else</span>.
                    </h2>
                    <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                        Set your amounts, share the link, and let the balance follow the code. None of
                        what you collect comes to us.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-lg border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-gift-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg px-8 py-4 text-lg font-semibold">
                            <span class="relative z-10 flex items-center gap-2">
                                Get Started Free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="mt-6 text-sm text-gray-400">No credit card required</p>
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
                        <span class="es-gift-tip pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
