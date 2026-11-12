<x-marketing-layout :hp="true">
    <x-slot name="title">How to Sell Tickets with Stripe | Stripe Payments for Tickets</x-slot>
    <x-slot name="description">Sell event tickets with your own Stripe account: what a Payment Link does alone, what a ticket needs on top, Stripe's fee at real prices, and setup in five steps.</x-slot>
    <x-slot name="breadcrumbTitle">Stripe</x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="How to Sell Tickets with Stripe"
        description="How to sell event tickets with your own Stripe account: Stripe Payment Links compared with a ticketing layer, Stripe's fee at five ticket prices, and setup in five steps, with no platform fee."
        keywords="sell tickets with stripe, stripe ticketing, stripe payment links for events, stripe event tickets, stripe ticket fees, stripe connect tickets" />
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
           Stripe "The Payout" styles.

           CONCEPT: a payout statement. The page is laid out the way the
           money actually moves, because that is the whole argument. The
           charge for a ticket is created ON THE SELLER'S OWN CONNECTED
           ACCOUNT - StripeGateway::stripeCheckout() passes
           ['stripe_account' => $event->user->stripe_account_id] as the
           request option - and there is no application_fee_amount
           anywhere in the repository. So "zero platform fees" is not a
           pricing promise that could be withdrawn later; it is a
           property of the wiring, and the statement has a line with
           nothing on it. The concept and the feature story are the same
           sentence.

           THE DEVICES, in order:
             1. TWO LANES, not one. A vertical money lane (solid rule:
                card, your Stripe account, your bank) beside a vertical
                receipt lane (dashed rule: Stripe's signed webhook, us,
                the ticket email). Event Schedule appears only on the
                dashed one. Deliberately VERTICAL: a horizontal three-stop
                rail collapses at 320px, and a vertical route reads at
                every width. Abstract route strokes only - no outline
                illustration of a bank, a card or a safe (CLAUDE.md).
             2. THE STATEMENT SHEET, a real <table> with a tear-off stub.
                The content is a record, so it is a table: line, amount,
                who takes it. The 0.00 row is the point of the page.
             3. RUBBER STAMPS as the recurring mark. Type and a double
                rule, rotated a degree or two - no glyph illustrations.
                A stamped ordinal marks every section, and the same mark
                enlarges into PAID / HELD in the dark band.
             4. THE GATE, a numbered stack in a fixed dark band: what has
                to be true before a ticket exists. Signature verified
                against the key that matches the payment context, charged
                amount reconciled against the ticket total, one locked
                transition to paid, then the email.
             5. THE STUB THE PAGE TEARS OFF ALONG. The finale is the
                bottom of a statement, so it ends on a remittance stub:
                the same dashed tear rule as the printed sheet, then the
                whole cost of getting paid in three cells (our cut 0.00,
                the Pro plan, Stripe's own rate). The 0.00 the hero opens
                on is the first figure the page closes on. The tear rule
                needs a band-local override because .es-payout-tear
                carries the sheet's paper background.

           PINNED PHYSICAL OBJECT: .es-payout-sheet is a printed
           statement, so it renders IDENTICALLY with .dark on and off.
           Nothing inside it may carry a dark variant, and nothing inside
           it uses a shared class that carries its own .dark rule in
           marketing.css. Verified with --bands=.es-payout-sheet.
           Same contract for .es-payout-band, which is why the
           grid-overlay / animate-shimmer / es-claim overrides are
           re-declared below the base rules.

           COLOUR: the page keeps its existing blue family, but drops the
           shared brand blue -> sky -> cyan gradient it used to paint its
           headings with. That gradient is the site chrome, so using it as
           a page accent made this page look like the nav. Instead: one
           solid deep statement blue, #0f4c81 (207deg, dark and cool),
           which is a passbook blue rather than a UI blue - distinct from
           the brand's #4E81FA (221deg, vivid), from /for-djs' #0e7490
           (192deg, cyan) and from /for-comedy-clubs' #31396b (233deg,
           desaturated navy). Everything else is achromatic: the second
           state signal (HELD) is graphite, not a second hue, because a
           ledger has one ink and one pencil.

           MEASURED (contrast ratio : ground):
             light ground #f4f6f8, card #fcfdfe, sub #e9edf1
               ink #0e1519      17.00 / 18.09 / 15.66
               muted #4a555e     7.04 /  7.49 /  6.49
               accent #0f4c81    8.18 /  8.70 /  7.53
               grad stops #0b4374 9.37, #14618f 6.18
               white on #0b4374 10.15
             dark ground #0a0e12, card #151b21, sub #1d242b
               ink #e8edf1      16.43 / 14.72 / 13.30
               muted #98a4ae     7.61 /  6.82 /  6.16
               accent #8fc4ee   10.42 /  9.33 /  8.44
               grad stops #b6dbf7 13.34, #8fc4ee 10.42
             band #0c1117, band card #161d24
               white 18.95 / 17.00, gray-400 #9ca3af 7.46 / 6.70,
               lit accent #8fc4ee 10.20 / 9.15
             sheet paper #fbfcfd, stub #eef1f4
               ink 17.93 / 16.25, muted 7.43 / 6.73, accent 8.62 / 7.81
           NEVER text-gray-500 on this ground: #6b7280 measures 4.4 on
           #f4f6f8. Use .es-payout-muted.
           ============================================================== */

        /* --- Ground and ink ----------------------------------------- */
        .es-payout-page { background-color: #f4f6f8; color: #0e1519; }
        .dark .es-payout-page { background-color: #0a0e12; color: #e8edf1; }
        .es-payout-ink { color: #0e1519; }
        .dark .es-payout-ink { color: #e8edf1; }
        .es-payout-muted { color: #4a555e; }
        .dark .es-payout-muted { color: #98a4ae; }
        .es-payout-accent { color: #0f4c81; }
        .dark .es-payout-accent { color: #8fc4ee; }

        .es-payout-grad {
            background-image: linear-gradient(100deg, #0b4374, #14618f);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dark .es-payout-grad,
        .es-payout-band .es-payout-grad {
            background-image: linear-gradient(100deg, #b6dbf7, #8fc4ee);
        }

        /* --- Surfaces ----------------------------------------------- */
        .es-payout-card {
            background-color: #fcfdfe;
            border: 1px solid rgba(14, 21, 25, 0.12);
            border-radius: 0.6rem;
        }
        .dark .es-payout-card {
            background-color: #151b21;
            border-color: rgba(232, 237, 241, 0.13);
        }
        .es-payout-sub {
            background-color: #e9edf1;
            border-radius: 0.4rem;
        }
        .dark .es-payout-sub { background-color: #1d242b; }
        .es-payout-hover { transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        .es-payout-hover:hover {
            border-color: rgba(15, 76, 129, 0.45);
            box-shadow: 0 10px 26px -18px rgba(14, 21, 25, 0.55);
        }
        .dark .es-payout-hover:hover {
            border-color: rgba(143, 196, 238, 0.4);
            box-shadow: 0 10px 26px -18px rgba(0, 0, 0, 0.85);
        }
        .es-payout-edge { border-top: 1px solid rgba(14, 21, 25, 0.1); }
        .dark .es-payout-edge { border-top-color: rgba(232, 237, 241, 0.1); }

        /* --- Figures. Money is always tabular. ---------------------- */
        .es-payout-num {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, 'Liberation Mono', monospace;
            font-variant-numeric: tabular-nums;
        }
        /* Long unbroken env var names must not set the grid track's
           min-content width, or the whole page scrolls sideways at 320px.
           `anywhere` (not break-word) is what shrinks min-content. */
        .es-payout-env { overflow-wrap: anywhere; }
        .es-payout-figure {
            font-size: clamp(2.5rem, 8vw, 3.75rem);
            font-weight: 900;
            letter-spacing: -0.03em;
            line-height: 1;
            font-variant-numeric: tabular-nums;
        }

        /* --- Eyebrow ------------------------------------------------ */
        .es-payout-tag {
            font-size: 0.66rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #0f4c81;
        }
        .dark .es-payout-tag { color: #8fc4ee; }
        .es-payout-band .es-payout-tag { color: #8fc4ee; }

        /* --- Stamps -------------------------------------------------
           Type inside a double rule, rotated a degree or two. The small
           variant marks a section; the large one is the PAID / HELD mark
           in the band. Fixed-ink variants (-ink / -lit / -mute) carry no
           dark rule at all, so they are safe inside pinned objects. */
        .es-payout-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2.6rem;
            padding: 0.2rem 0.5rem;
            transform: rotate(-1.5deg);
            border: 1px solid rgba(15, 76, 129, 0.5);
            outline: 1px solid rgba(15, 76, 129, 0.22);
            outline-offset: 2px;
            border-radius: 0.15rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            color: #0f4c81;
        }
        .dark .es-payout-mark {
            border-color: rgba(143, 196, 238, 0.5);
            outline-color: rgba(143, 196, 238, 0.22);
            color: #8fc4ee;
        }
        .es-payout-band .es-payout-mark {
            border-color: rgba(143, 196, 238, 0.5);
            outline-color: rgba(143, 196, 238, 0.22);
            color: #8fc4ee;
        }

        .es-payout-stamp {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.45rem 0.9rem;
            border-width: 2px;
            border-style: solid;
            border-radius: 0.2rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            transform: rotate(-3deg);
        }
        .es-payout-stamp-ink { border-color: rgba(15, 76, 129, 0.55); color: #0f4c81; }
        .es-payout-stamp-lit { border-color: rgba(143, 196, 238, 0.6); color: #8fc4ee; }
        .es-payout-stamp-mute { border-color: rgba(156, 163, 175, 0.5); color: #9ca3af; transform: rotate(2deg); }

        /* --- The two lanes -----------------------------------------
           A vertical route: a rule down the inline-start edge with a stop
           dot per row. Logical properties so it mirrors in RTL. */
        .es-payout-lane {
            position: relative;
            padding-inline-start: 1.45rem;
        }
        .es-payout-lane::before {
            content: "";
            position: absolute;
            inset-inline-start: 0.34rem;
            top: 0.6rem;
            bottom: 0.6rem;
            border-inline-start: 2px solid rgba(15, 76, 129, 0.55);
        }
        .dark .es-payout-lane::before { border-inline-start-color: rgba(143, 196, 238, 0.5); }
        .es-payout-lane-data::before {
            border-inline-start-style: dashed;
            border-inline-start-color: rgba(74, 85, 94, 0.5);
        }
        .dark .es-payout-lane-data::before { border-inline-start-color: rgba(152, 164, 174, 0.45); }

        .es-payout-stop { position: relative; padding-block: 0.4rem; }
        .es-payout-stop::before {
            content: "";
            position: absolute;
            inset-inline-start: -1.45rem;
            top: 0.72rem;
            width: 0.8rem;
            height: 0.8rem;
            border-radius: 999px;
            background-color: #fcfdfe;
            border: 2px solid rgba(15, 76, 129, 0.75);
        }
        .dark .es-payout-stop::before {
            background-color: #151b21;
            border-color: rgba(143, 196, 238, 0.7);
        }
        .es-payout-lane-data .es-payout-stop::before { border-color: rgba(74, 85, 94, 0.6); }
        .dark .es-payout-lane-data .es-payout-stop::before { border-color: rgba(152, 164, 174, 0.55); }
        .es-payout-stop-fill::before { background-color: #0f4c81; border-color: #0f4c81; }
        .dark .es-payout-stop-fill::before { background-color: #8fc4ee; border-color: #8fc4ee; }

        /* A single settlement pulse running down the money lane. */
        .es-payout-pulse {
            position: absolute;
            inset-inline-start: 0.17rem;
            top: 0.6rem;
            width: 0.55rem;
            height: 0.55rem;
            border-radius: 999px;
            background-color: #0f4c81;
            box-shadow: 0 0 10px rgba(15, 76, 129, 0.6);
            opacity: 0;
        }
        .dark .es-payout-pulse {
            background-color: #8fc4ee;
            box-shadow: 0 0 10px rgba(143, 196, 238, 0.7);
        }
        html.es-anim .es-payout-pulse { animation: es-payout-run 4.2s ease-in-out infinite; }
        @keyframes es-payout-run {
            0%   { top: 0.6rem; opacity: 0; }
            12%  { opacity: 1; }
            70%  { top: calc(100% - 1.15rem); opacity: 1; }
            85%  { top: calc(100% - 1.15rem); opacity: 0; }
            100% { top: 0.6rem; opacity: 0; }
        }

        /* --- The statement sheet (PINNED: identical in both modes) --
           A printed document. No descendant may carry a dark variant. */
        .es-payout-sheet {
            position: relative;
            background-color: #fbfcfd;
            border: 1px solid rgba(14, 21, 25, 0.16);
            border-radius: 0.35rem;
            box-shadow: 0 18px 40px -28px rgba(14, 21, 25, 0.55);
            color: #0e1519;
            overflow: hidden;
        }
        /* Ledger margin rule, the way accounting paper is printed. */
        .es-payout-sheet::before {
            content: "";
            position: absolute;
            top: 0;
            bottom: 0;
            inset-inline-start: 0.85rem;
            width: 1px;
            background-color: rgba(15, 76, 129, 0.18);
        }
        .es-payout-sheet-head {
            border-bottom: 1px solid rgba(14, 21, 25, 0.14);
            background-color: #f6f8fa;
        }
        .es-payout-sheet-title {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            color: #0f4c81;
        }
        .es-payout-sheet-meta {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.72rem;
            color: #4a555e;
        }

        /* Below about 400px the three columns squeeze into one word per
           line, so the record scrolls inside its own wrapper instead. */
        .es-payout-table { width: 100%; min-width: 21rem; border-collapse: collapse; }
        .es-payout-table th,
        .es-payout-table td {
            padding: 0.7rem 0.5rem;
            border-bottom: 1px solid rgba(14, 21, 25, 0.09);
            vertical-align: top;
            text-align: start;
        }
        .es-payout-table thead th {
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #4a555e;
            border-bottom-color: rgba(14, 21, 25, 0.2);
        }
        .es-payout-table tbody th { font-size: 0.88rem; font-weight: 700; color: #0e1519; }
        .es-payout-table td { font-size: 0.82rem; color: #4a555e; }
        .es-payout-amount {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.9rem;
            font-weight: 700;
            color: #0e1519;
            text-align: end;
            white-space: nowrap;
        }
        .es-payout-note { display: block; font-size: 0.74rem; font-weight: 400; color: #4a555e; }
        .es-payout-who {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.72rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #4a555e;
            white-space: nowrap;
        }
        /* The row the page exists for. */
        .es-payout-zero { background-color: #eef3f8; }
        .es-payout-zero .es-payout-amount { color: #0f4c81; }
        .es-payout-zero .es-payout-who { color: #0f4c81; font-weight: 700; }

        .es-payout-tear {
            border-top: 2px dashed rgba(14, 21, 25, 0.3);
            background-color: #fbfcfd;
        }
        .es-payout-stub { background-color: #eef1f4; }
        .es-payout-stub-figure {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: clamp(1.9rem, 6vw, 2.6rem);
            font-weight: 900;
            letter-spacing: -0.02em;
            line-height: 1;
            color: #0f4c81;
        }
        .es-payout-stub-label {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.68rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #4a555e;
        }
        .es-payout-sheet-foot { font-size: 0.72rem; color: #4a555e; }

        /* --- The dark band (fixed in both modes) --------------------
           A resolvable background-color under the gradient, so text over
           it is scored against what actually paints. */
        .es-payout-band {
            background-color: #0c1117;
            background-image:
                radial-gradient(ellipse 72% 52% at 50% 0%, rgba(15, 76, 129, 0.42), rgba(15, 76, 129, 0) 70%),
                linear-gradient(180deg, #111820, #0c1117);
        }
        .es-payout-band-card {
            background-color: #161d24;
            border: 1px solid rgba(232, 237, 241, 0.1);
            border-radius: 0.6rem;
        }
        .es-payout-band-rule { height: 1px; background-color: rgba(232, 237, 241, 0.12); }

        /* The finale is the bottom of a statement, so the page ends on the
           remittance stub: the same tear line as the printed sheet, then the
           whole bill in three cells. Fixed ink, because the band is pinned. */
        .es-payout-band .es-payout-tear {
            background-color: transparent;
            border-top-color: rgba(232, 237, 241, 0.32);
        }
        .es-payout-remit-label {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.66rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #9ca3af;
        }
        .es-payout-remit-figure {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 1.35rem;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.01em;
            color: #8fc4ee;
        }
        .es-payout-remit-note { font-size: 0.72rem; color: #9ca3af; }

        /* Nothing inside the band may change between colour modes. Three
           shared classes carry their own .dark rules in marketing.css and
           are invisible to a grep of this file. */
        .es-payout-band .grid-overlay {
            background-image:
                linear-gradient(rgba(232, 237, 241, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(232, 237, 241, 0.05) 1px, transparent 1px);
        }
        .es-payout-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-payout-band .es-claim:focus-within {
            border-color: rgba(143, 196, 238, 0.75);
            box-shadow: 0 0 0 4px rgba(143, 196, 238, 0.22);
        }

        /* --- The gate: a numbered stack ----------------------------- */
        .es-payout-step {
            position: relative;
            padding-inline-start: 3.2rem;
            padding-block: 0.9rem;
        }
        .es-payout-step::before {
            content: attr(data-step);
            position: absolute;
            inset-inline-start: 0;
            top: 0.85rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.2rem;
            height: 1.7rem;
            border: 1px solid rgba(143, 196, 238, 0.45);
            border-radius: 0.15rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            color: #8fc4ee;
        }
        .es-payout-step::after {
            content: "";
            position: absolute;
            inset-inline-start: 1.1rem;
            top: 2.75rem;
            bottom: -0.2rem;
            border-inline-start: 1px dashed rgba(143, 196, 238, 0.28);
        }
        .es-payout-step:last-child::after { display: none; }

        /* --- Plan pills. Tiers ONLY, never a state badge. ----------- */
        .es-payout-plan {
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
        .es-payout-plan-free { border-color: rgba(14, 21, 25, 0.24); color: #4a555e; }
        .dark .es-payout-plan-free { border-color: rgba(232, 237, 241, 0.26); color: #98a4ae; }
        .es-payout-plan-pro {
            border-color: rgba(15, 76, 129, 0.5);
            background-color: rgba(15, 76, 129, 0.08);
            color: #0f4c81;
        }
        .dark .es-payout-plan-pro {
            border-color: rgba(143, 196, 238, 0.42);
            background-color: rgba(143, 196, 238, 0.1);
            color: #8fc4ee;
        }

        /* --- Buttons ------------------------------------------------ */
        .es-payout-btn {
            background-color: #0b4374;
            color: #ffffff;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .es-payout-btn:hover {
            background-color: #093459;
            transform: translateY(-1px);
            box-shadow: 0 14px 28px -16px rgba(11, 67, 116, 0.9);
        }
        .es-payout-ghost {
            border: 1px solid rgba(14, 21, 25, 0.22);
            color: #0e1519;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }
        .es-payout-ghost:hover { border-color: rgba(15, 76, 129, 0.5); background-color: rgba(15, 76, 129, 0.06); }
        .dark .es-payout-ghost { border-color: rgba(232, 237, 241, 0.24); color: #e8edf1; }
        .dark .es-payout-ghost:hover { border-color: rgba(143, 196, 238, 0.45); background-color: rgba(143, 196, 238, 0.08); }

        /* --- Shared chrome that is hard-coded brand blue ------------
           The dot-nav tooltip needs a real CSS rule, not a Tailwind
           arbitrary value: a `dark:bg-[#151b21]` for a hex that is not
           already in the built marketing bundle silently does nothing,
           and the tooltip then keeps bg-white behind dark-mode ink. */
        .es-payout-tip {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            color: #374151;
        }
        .dark .es-payout-tip {
            background-color: #151b21;
            border-color: rgba(232, 237, 241, 0.14);
            color: #d1d5db;
        }

        .es-dot:hover .es-dot-pip { background-color: rgba(15, 76, 129, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(143, 196, 238, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #0f4c81; }
        .dark .es-dot.is-active .es-dot-pip { background: #8fc4ee; }

        /* Focus rings. Never set border-radius here: an outline already
           follows the element's own radius. */
        #es-payout-page a:focus-visible,
        #es-payout-page summary:focus-visible,
        #es-payout-page button:focus-visible,
        #es-payout-page input:focus-visible {
            outline: 2px solid #0f4c81;
            outline-offset: 2px;
        }
        .dark #es-payout-page a:focus-visible,
        .dark #es-payout-page summary:focus-visible,
        .dark #es-payout-page button:focus-visible,
        .dark #es-payout-page input:focus-visible {
            outline-color: #8fc4ee;
        }
        .es-payout-band a:focus-visible,
        .es-payout-band summary:focus-visible,
        .es-payout-band button:focus-visible,
        .es-payout-band input:focus-visible {
            outline-color: #8fc4ee !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-payout-pulse { animation: none !important; opacity: 0.85; }
            .es-payout-btn:hover { transform: none; }
        }

        /* --- Stripe on its own vs a ticketing layer -----------------
           A real table from a tablet up. On a phone each row becomes a
           card and every cell says its column from data-label, because
           display:block drops the table's meaning (hence the explicit
           roles on the markup). */
        .es-payout-compare { width: 100%; border-collapse: collapse; }
        .es-payout-compare th,
        .es-payout-compare td {
            padding: 0.95rem 1rem;
            text-align: start;
            vertical-align: top;
            border-bottom: 1px solid rgba(14, 21, 25, 0.1);
        }
        .dark .es-payout-compare th,
        .dark .es-payout-compare td { border-bottom-color: rgba(232, 237, 241, 0.1); }
        .es-payout-compare thead th {
            font-size: 0.66rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #4a555e;
        }
        .dark .es-payout-compare thead th { color: #98a4ae; }
        .es-payout-compare tbody th { width: 22%; font-size: 0.92rem; font-weight: 700; color: #0e1519; }
        .dark .es-payout-compare tbody th { color: #e8edf1; }
        .es-payout-compare td { font-size: 0.9rem; line-height: 1.55; color: #4a555e; }
        .dark .es-payout-compare td { color: #98a4ae; }
        .es-payout-compare td.es-payout-ours { color: #0e1519; }
        .dark .es-payout-compare td.es-payout-ours { color: #e8edf1; }
        .es-payout-compare tbody tr:last-child th,
        .es-payout-compare tbody tr:last-child td { border-bottom: 0; }
        @media (max-width: 767px) {
            .es-payout-compare,
            .es-payout-compare tbody { display: block; width: 100%; }
            .es-payout-compare thead { display: none; }
            .es-payout-compare tr { display: block; padding: 0.9rem 0; border-bottom: 1px solid rgba(14, 21, 25, 0.1); }
            .dark .es-payout-compare tr { border-bottom-color: rgba(232, 237, 241, 0.1); }
            .es-payout-compare tbody tr:last-child { border-bottom: 0; }
            .es-payout-compare th,
            .es-payout-compare td { display: block; width: auto; padding: 0.3rem 1rem; border-bottom: 0; }
            .es-payout-compare tbody th { width: auto; padding-bottom: 0.5rem; }
            .es-payout-compare td::before {
                content: attr(data-label);
                display: block;
                margin-bottom: 0.15rem;
                font-size: 0.62rem;
                font-weight: 700;
                letter-spacing: 0.16em;
                text-transform: uppercase;
                color: #0f4c81;
            }
            .dark .es-payout-compare td::before { color: #8fc4ee; }
        }

        /* --- The price ladder: Stripe's fee at five ticket prices ---- */
        .es-payout-ladder { width: 100%; border-collapse: collapse; }
        .es-payout-ladder th,
        .es-payout-ladder td {
            padding: 0.6rem 0.75rem;
            text-align: end;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, 'Liberation Mono', monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.85rem;
            border-bottom: 1px solid rgba(14, 21, 25, 0.08);
            color: #4a555e;
        }
        .dark .es-payout-ladder th,
        .dark .es-payout-ladder td { border-bottom-color: rgba(232, 237, 241, 0.08); color: #98a4ae; }
        .es-payout-ladder th:first-child,
        .es-payout-ladder td:first-child { text-align: start; }
        .es-payout-ladder thead th {
            font-family: inherit;
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }
        .es-payout-ladder tbody th { color: #0e1519; font-weight: 700; }
        .dark .es-payout-ladder tbody th { color: #e8edf1; }
        .es-payout-ladder .es-payout-keep { color: #0e1519; font-weight: 700; }
        .dark .es-payout-ladder .es-payout-keep { color: #e8edf1; }
        .es-payout-ladder .es-payout-nil { color: #0f4c81; font-weight: 700; }
        .dark .es-payout-ladder .es-payout-nil { color: #8fc4ee; }
        .es-payout-ladder tbody tr:last-child th,
        .es-payout-ladder tbody tr:last-child td { border-bottom: 0; }

        /* --- Setup steps: a numbered card per step ------------------ */
        .es-payout-setup-num {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, 'Liberation Mono', monospace;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            color: #0f4c81;
        }
        .dark .es-payout-setup-num { color: #8fc4ee; }
    </style>

    @php
        // Stripe's published US card rate, read from the one place the /ticket-fee-calculator
        // reads it from (App\Utils\TicketFees::rates()['stripe']), so this page and the
        // calculator cannot quote different figures. Every figure below is worked in cents
        // from these two values.
        $stripeRate = \App\Utils\TicketFees::rates()['stripe'];
        $ratePercent = (float) $stripeRate['percent'];
        $rateFixedCents = (int) round($stripeRate['fixed'] * 100);
        $feeCentsFor = fn (int $cents) => (int) round($cents * $ratePercent) + $rateFixedCents;
        $money = fn ($cents) => number_format($cents / 100, 2);
        $rateLabel = rtrim(rtrim(number_format($ratePercent * 100, 2), '0'), '.').'% + '.$money($rateFixedCents);

        // One ticket, itemised, for the statement sheet.
        $stmt = ['currency' => 'USD', 'grossCents' => 2500];
        $stmt['feeCents'] = $feeCentsFor($stmt['grossCents']);
        $stmt['netCents'] = $stmt['grossCents'] - $stmt['feeCents'];

        // The ladder: the same arithmetic at five prices, one ticket per order.
        $ladder = array_map(function (int $cents) use ($feeCentsFor) {
            $fee = $feeCentsFor($cents);

            return [
                'price' => $cents,
                'fee' => $fee,
                'keep' => $cents - $fee,
                'share' => number_format($fee / $cents * 100, 1),
            ];
        }, [1000, 2000, 2500, 5000, 10000]);

        $statementLines = [
            [
                'line'   => 'Ticket price',
                'note'   => 'The buyer is charged on your own Stripe account.',
                'amount' => '+' . $money($stmt['grossCents']),
                'who'    => 'Buyer',
                'zero'   => false,
            ],
            [
                'line'   => 'Stripe processing fee',
                'note'   => 'Stripe deducts its own published rate for your country and the method used.',
                'amount' => '-' . $money($stmt['feeCents']),
                'who'    => 'Stripe',
                'zero'   => false,
            ],
            [
                'line'   => 'Event Schedule platform fee',
                'note'   => 'No platform fee is added to the charge, so there is nothing on this line.',
                'amount' => $money(0),
                'who'    => 'Nobody',
                'zero'   => true,
            ],
        ];

        // The remittance stub the page ends on: the whole cost of getting
        // paid, in the order a statement would print it. Nothing here is a
        // figure Event Schedule collects on a sale.
        $remit = [
            ['Our cut of each sale', $money(0), 'There is no fee field in the charge we create.'],
            ['The Pro plan, monthly', plan_price($proMonthly), 'What lets a ticket carry a price. Nothing further is charged on a sale.'],
            ['Card processing', "Stripe's rate", 'Stripe sets it, per country and method.'],
        ];

        // The two lanes. Money on the solid rule, the receipt on the
        // dashed one. Event Schedule appears on the dashed lane only.
        $moneyLane = [
            ['Buyer pays', 'Card, Apple Pay or Google Pay on Stripe Checkout.', false],
            ['Your Stripe account', 'The charge is created on your own Stripe account.', true],
            ['Your bank', 'Stripe pays out to the account you gave Stripe.', false],
        ];
        $receiptLane = [
            ['Stripe confirms it', 'A signed message that the payment went through.', false],
            ['Event Schedule checks it', 'The right account, the right amount, once.', true],
            ['The ticket goes out', 'Confirmation email with the QR code.', false],
        ];

        // Stripe on its own vs Event Schedule on the same Stripe account.
        // Every Stripe cell is from Stripe's own documentation, checked 2026-11-08:
        //   docs.stripe.com/payment-links/customize (payment limit counts completed Checkout
        //     Sessions and deactivates the link; custom fields text / numbers only / dropdown;
        //     customisable deactivation message)
        //   docs.stripe.com/payment-links/post-payment (confirmation message or redirect;
        //     receipts automatic or manual; refunds full or partial from the Dashboard;
        //     fulfillment through a partner such as Zapier or your own webhooks)
        //   docs.stripe.com/apple-pay ("For Payment Links or hosted Checkout, Apple Pay works
        //     with no additional configuration.")
        //   docs.stripe.com/payments/checkout/payment-methods (methods shown from the Dashboard)
        // Our side: docs/FEATURES.md (QR scanning, check-in, refunds, waitlist, ticket-type
        // fields) and marketing/docs/tickets (quantity per date, Sell tickets tile).
        $compare = [
            [
                'Taking the payment',
                'Stripe\'s hosted checkout page, with the cards and wallets you have turned on in the Stripe Dashboard. Apple Pay works there with no extra setup.',
                'The same Stripe Checkout page, created on your own Stripe account, with the same methods.',
            ],
            [
                'Stopping when it sells out',
                'A link can stop after a set number of payments and then deactivate. The limit counts completed checkouts, not tickets, so one buyer taking four uses one of them.',
                'A quantity for each ticket type, counted in tickets. On a recurring event it is kept per date, so Friday selling out leaves Saturday on sale.',
            ],
            [
                'Asking buyers questions',
                'Custom fields on the payment page: free text, numbers only, or a dropdown.',
                'Questions on a ticket type, such as a meal choice on the dinner ticket, on every plan. Your own questions for the whole order are Pro.',
            ],
            [
                'What the buyer gets',
                'A confirmation message or a redirect to your site, and a Stripe receipt by email when receipts are on.',
                'A confirmation email with the ticket and its QR code, on the schedule\'s own name and colours.',
            ],
            [
                'At the door',
                'Not part of a Payment Link. Stripe\'s suggestion for anything after the payment is an automation you build yourself (Stripe calls it a webhook) or a partner such as Zapier.',
                'Scan the QR with any phone from the Sales tab, free on every plan. Each ticket admits once, and a second scan warns you.',
            ],
            [
                'Who is coming',
                'The link\'s payments, listed in the Stripe Dashboard.',
                'A Sales list with every buyer, the tickets they hold and whether they have paid. CSV export is Pro.',
            ],
            [
                'When it is full',
                'The link deactivates and shows a message you can write yourself.',
                'A waitlist that emails the next people when tickets come back. Pro for tickets, free for free registration.',
            ],
            [
                'Refunds',
                'From the Stripe Dashboard, in full or in part.',
                'From the Sales page, in full or in part, on Pro. The money goes back through Stripe and a fully refunded ticket stops scanning.',
            ],
            [
                'A page for the event',
                'Not part of a Payment Link: you link to it from wherever you announce the event.',
                'An event page with the date, the venue and the buy button, on a schedule people can follow.',
            ],
            [
                'What it costs',
                'Stripe\'s processing fee.',
                'Stripe\'s processing fee, and the Pro plan once a ticket carries a price. Nothing per sale.',
            ],
        ];

        $setup = [
            ['01', 'Make a schedule', 'Sign up and create the schedule your events will live on. It is free and asks for no card.'],
            ['02', 'Connect Stripe', 'In Settings, open Payment Methods and press Connect Stripe. Stripe\'s own onboarding asks for the details it needs to pay you. Until Stripe has what it needs, the row reads Setup not finished and Stripe is not offered on your events. If Stripe finishes its review after you come back, press Connect Stripe again to pick it up.'],
            ['03', 'Add your tickets', 'On the event\'s Tickets tab press Sell tickets, add a ticket type for each price (early bird, standard, VIP) with how many exist, and pick the event\'s currency. Charging for a ticket is the Pro plan.'],
            ['04', 'Choose Stripe for the event', 'In the Payment row, pick Stripe. The method is chosen per event, so a paid workshop can take cards while a community night takes cash at the door.'],
            ['05', 'Share it, then scan it', 'Publish the event and share its page. Buyers pay on Stripe Checkout and get the ticket with its QR code by email; at the door you scan it from the Sales tab.'],
        ];

        // Verified in StripeController::webhook() and success().
        $gate = [
            ['01', 'Only Stripe can say a ticket is paid', 'The confirmation comes to us from Stripe, signed, never from the buyer\'s browser. A message without Stripe\'s signature for the right account is refused rather than trusted.'],
            ['02', 'The amount has to match', 'What Stripe charged is compared with what the tickets should have cost after any promo code, volume discount or gift card. More than a cent apart and the sale is held for review as an amount mismatch, shown on your Sales tab, with no ticket sent.'],
            ['03', 'One payment, one set of tickets', 'Stripe can confirm the same payment more than once, and the buyer is landing back on your page at the same moment. The sale is marked paid exactly once, so nobody is sent their tickets twice.'],
            ['04', 'The buyer can close the tab', 'Because the confirmation comes from Stripe and not from the page, a buyer who closes the tab after paying still gets the ticket by email, and the sale still reaches your Sales tab.'],
        ];

        // Event.payment_method, per event. TicketController::checkout() resolves it through
        // config/payments.php: cash, stripe, invoiceninja, payment_url, payfast, paypal, razorpay.
        $routes = [
            [
                'name'  => 'Stripe',
                'held'  => 'Your own Stripe account',
                'desc'  => 'Stripe Checkout handles the card, the wallet and the receipt. The charge is created on your own Stripe account and settles there.',
                'micro' => 'Cards and wallets',
            ],
            [
                'name'  => 'PayPal',
                'held'  => 'Your own PayPal account',
                'desc'  => 'The buyer approves on PayPal, from a balance or a card, and the payment is taken straight afterwards so the ticket is issued on their return.',
                'micro' => 'PayPal balance or card',
            ],
            [
                'name'  => 'Payfast',
                'held'  => 'Your own Payfast account',
                'desc'  => 'Card, Instant EFT, Capitec Pay and the rest of the South African methods. Settles in rand, so it is offered on ZAR events only.',
                'micro' => 'South African rand',
            ],
            [
                'name'  => 'Razorpay',
                'held'  => 'Your own Razorpay account',
                'desc'  => 'UPI, cards, netbanking and wallets on a Razorpay payment link. Settles in Indian rupees, so it is offered on INR events only.',
                'micro' => 'Indian rupees (INR)',
            ],
            [
                'name'  => 'Invoice Ninja',
                'held'  => 'Your Invoice Ninja gateway',
                'desc'  => 'Raise a real invoice, or send a payment link, from your own Invoice Ninja instance. Choose which of the two on the Invoice Ninja tab under Payment Methods.',
                'micro' => 'Invoice or payment link',
            ],
            [
                'name'  => 'Payment link',
                'held'  => 'Whatever the link points at',
                'desc'  => 'Any URL you own, a Stripe Payment Link included. Buyers are sent there to pay, and you reconcile the sale yourself on the Sales tab.',
                'micro' => 'Bring your own',
            ],
            [
                'name'  => 'Cash at the door',
                'held'  => 'Your till',
                'desc'  => 'No card at all. You write the payment instructions the buyer sees, and mark the sale paid when the money arrives.',
                'micro' => 'Marked paid by hand',
                // The seventh card closes the grid: a full row at both column counts.
                'wide'  => true,
            ],
        ];

        // Everything else that gets priced into the same charge or rides
        // the same Stripe account. Each traced in the report.
        $sameRail = [
            ['Promo codes', 'A percentage or a fixed discount, with usage limits and an expiry, priced into the line items before the charge is created.', 'Pro'],
            ['Volume discounts', 'Buy more of one ticket type and the per-unit price drops. The rounding is spread a cent at a time so the charge matches the total exactly.', 'Free'],
            ['Gift cards', 'Sold through the same Stripe account and redeemed against a later ticket. A card is redeemed only on events of the same owner that sold it, and only in the card\'s currency.', 'Pro'],
            ['Add-ons', 'A drink, a programme, a workshop place. Priced as its own line on the same charge rather than a second checkout.', 'Pro'],
            ['Appointments', 'A bookable slot can take payment through the same Stripe account, or a payment link, or cash. Every plan carries one free booking type; putting a price on one is Pro.', 'Pro'],
            ['Installments', 'Split a ticket into monthly payments. The first is taken at checkout on the same Stripe account and the ticket is valid at once; the rest are charged to the saved card on schedule. No other route can do it.', 'Pro'],
        ];

        // Stripe figures here: stripe.com/pricing (2.9% + 30c domestic, +1.5% international
        // cards, +1% currency conversion, processing fees not returned on a refund),
        // docs.stripe.com/payouts (first payout typically 7-14 days; daily, weekly, monthly or
        // manual schedules), docs.stripe.com/payment-links/post-payment (refunds typically take
        // 5-10 business days to reach the statement), stripe.com/global (country list).
        $faqs = [
            [
                'q' => 'Can I sell event tickets with Stripe alone?',
                'a' => 'Yes, with a Stripe Payment Link. You create it in the Stripe Dashboard, share the address, and buyers pay on Stripe\'s checkout page at Stripe\'s normal processing fee. A link can stop after a set number of payments and ask buyers a few questions. What it does not do is produce a ticket: there is no QR code for each buyer, no separate count for each date and nothing to scan at the door, and Stripe\'s own suggestion for anything after the payment is an automation you build yourself (Stripe calls it a webhook) or a partner such as Zapier. For one price on one date with a guest list read out at the door, a Payment Link is enough. Past that, a ticketing tool that charges through your own Stripe account adds the ticket without adding a fee.',
            ],
            [
                'q' => 'How much does Stripe charge per ticket?',
                'a' => 'In the United States Stripe\'s published rate for a domestic card is '.$rateLabel.' per successful charge, plus 1.5% for an international card and 1% when the currency has to be converted. A '.$money($stmt['grossCents']).' ticket therefore costs '.$money($stmt['feeCents']).'. Stripe charges per payment, not per ticket, so an order of four tickets pays the fixed '.$money($rateFixedCents).' once. Other countries have their own rates on Stripe\'s pricing page, and Event Schedule adds nothing to any of them.',
            ],
            [
                'q' => 'Does Event Schedule take a cut of ticket sales?',
                'a' => 'No. On the hosted platform the charge is created on your own Stripe account and no platform fee is added to it, so there is no line where a platform cut could be taken. You pay Stripe its processing fee and keep the rest. Selling tickets that carry a price is on the Pro plan at '.plan_price($proMonthly).' a month, and that subscription is the whole of what Event Schedule charges.',
            ],
            [
                'q' => 'Can buyers pay with Apple Pay or Google Pay?',
                'a' => 'Yes, when your Stripe account offers them. Event Schedule sends buyers to Stripe\'s hosted Checkout without fixing the list of methods, so Stripe shows the ones you have turned on in your Dashboard that suit the buyer\'s device, currency and amount. Stripe\'s documentation says Apple Pay works on hosted Checkout with no extra setup. Buyers never type card details into an Event Schedule page.',
            ],
            [
                'q' => 'Can I take Stripe payments for appointments?',
                'a' => 'Yes. A booking type can take payment through the same connected Stripe account, a payment link or cash. Every plan includes one free booking type; putting a price on a booking type, or offering more than one, is Pro. A paid booking is refunded from the Sales page like a ticket.',
            ],
            [
                'q' => 'When does the money reach my bank?',
                'a' => 'When Stripe pays it out: the money is in your Stripe balance and never passes through an Event Schedule account. Stripe\'s documentation says a first payout typically arrives 7 to 14 days after your first live payment, depending on your industry, country and risk level, and after that follows the schedule you choose in the Stripe Dashboard (daily, weekly, monthly or manual, where your country allows it).',
            ],
            [
                'q' => 'How do refunds work?',
                'a' => 'From the Sales page, in full or in part, on Pro. The money goes back through Stripe first, and only then does the sale change here. A full refund invalidates the tickets, puts the seats back on sale and takes the sale out of your revenue figures. A partial refund leaves the sale paid and its tickets valid, and the Sales page shows how much has gone back so far. Stripe does not return its processing fee on a refund, and says a refund typically takes 5 to 10 business days to reach the buyer\'s statement. An installment plan is refunded payment by payment, and only in full.',
            ],
            [
                'q' => 'Can I refund from the Stripe Dashboard instead?',
                'a' => 'You can, but Event Schedule will not hear about it: no refund event comes back from Stripe, so the sale here would still read as paid and its tickets would still scan. Refunding from the Sales page keeps the two in step, and the refund still shows in your Stripe Dashboard. Outside installment plans, which remember the account that took each payment, it is issued on the Stripe account you are connected to now, so if you have switched accounts since the sale it fails rather than coming out of the wrong one.',
            ],
            [
                'q' => 'Which countries and currencies does it work in?',
                'a' => 'Anywhere you can open a Stripe account, which is Stripe\'s own list of countries. Each event is priced in one of '.count(\App\Utils\MoneyUtils::currencies()).' currencies, including USD, EUR, GBP, CAD, AUD, JPY, INR, BRL, ILS, ZAR and SGD. Stripe is charged in that currency, and zero-decimal currencies such as JPY and KRW are handled in whole units rather than cents.',
            ],
            [
                'q' => 'Does it handle sales tax or VAT?',
                'a' => 'No. Event Schedule does not calculate or collect sales tax or VAT, and does not switch on Stripe Tax for the charge. Price tickets to include what you owe, and on Pro export the sales as CSV when it is time to file.',
            ],
            [
                'q' => 'What happens if Stripe setup is not finished?',
                'a' => 'Until Stripe has what it needs, the Stripe row under Payment Methods reads Setup not finished and Stripe is not offered as a payment method on an event, so nothing can go on sale that cannot be paid for. If Stripe finishes its review after you leave its onboarding, press Connect Stripe again and the row picks it up. Disconnect removes the link to your Stripe account here without touching the account itself.',
            ],
            [
                'q' => 'Can buyers pay in installments?',
                'a' => 'Yes, on the Pro plan, and only through Stripe, because it is the one route that can charge a saved card again later. The buyer pays the first installment at checkout and the ticket is valid straight away; the rest are charged to the same card on schedule. If a payment is missed, the ticket is held at the door until the plan is settled.',
            ],
            [
                'q' => 'Can a selfhosted install take Stripe payments?',
                'a' => 'Yes. Instead of Stripe Connect you put your own Stripe keys and webhook signing secret in the environment file, and the install charges through that account directly. The setup, including which webhook events to send, is written up in the selfhosted Stripe guide.',
            ],
        ];

        $dotSections = [
            ['top', 'The answer'],
            ['compare', 'Stripe on its own'],
            ['statement', 'What it costs'],
            ['setup', 'Setup'],
            ['gate', 'Before a ticket'],
            ['routes', 'Other routes'],
            ['more', 'Same charge'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];
    @endphp

    <div id="es-payout-page" class="es-payout-page">

    {{-- ============================================================
         1. Hero: the direct answer, and the two lanes
         ============================================================
         The nav overlays the top of the page, so the hero carries extra
         top padding rather than letting the card sit under it. --}}
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden pb-16 pt-28">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 28% 30%, rgba(15, 76, 129, 0.26), rgba(15, 76, 129, 0) 62%); opacity: 0.5;"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 72% 60%, rgba(143, 196, 238, 0.18), rgba(143, 196, 238, 0) 62%); opacity: 0.45;"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:72px_72px] [mask-image:radial-gradient(ellipse_72%_62%_at_50%_38%,black_22%,transparent_74%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                <div>
                    <h1 class="es-balance mb-7 text-[2.6rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <x-marketing.hero-eyebrow class="block es-payout-tag es-fade-up es-d-1 mb-5">Stripe payments for tickets</x-marketing.hero-eyebrow>
                        <span class="es-mask"><span class="es-mask-line">Sell tickets with Stripe.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">Keep all of it <span class="es-payout-grad">but Stripe's fee</span>.</span></span>
                    </h1>

                    <p class="es-payout-muted es-fade-up es-d-2 mb-5 max-w-xl text-lg sm:text-xl">
                        Stripe can take the money for a ticket on its own. What it does not do is issue the
                        ticket: a QR code for the door, a count of what is left for each night, a list of who
                        is coming. Event Schedule does that part, on your own Stripe account, and adds nothing
                        to the charge.
                    </p>
                    <p class="es-payout-muted es-fade-up es-d-2 mb-9 max-w-xl text-sm">
                        Free to start, and free registration stays free. A ticket with a price is the Pro
                        plan, {{ plan_price($proMonthly) }} a month, with no fee on any sale.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ app_url('/sign_up?intent=sell') }}" class="es-payout-btn inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            Get started free
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                        <a href="#compare" class="es-payout-ghost inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            Compare with Stripe alone
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                    </div>
                </div>

                {{-- The two lanes. Money on the solid rule, receipt on the
                     dashed one. We are only on the dashed one. --}}
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-payout-card p-6 sm:p-7">
                        <div class="grid gap-8 sm:grid-cols-2 sm:gap-6">
                            <div>
                                <p class="es-payout-tag mb-4">The money</p>
                                <div class="es-payout-lane">
                                    <div class="es-payout-pulse" aria-hidden="true"></div>
                                    @foreach ($moneyLane as [$stopName, $stopNote, $stopFill])
                                        <div class="es-payout-stop {{ $stopFill ? 'es-payout-stop-fill' : '' }}">
                                            <p class="es-payout-ink text-sm font-bold">{{ $stopName }}</p>
                                            <p class="es-payout-muted mt-0.5 text-xs">{{ $stopNote }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <p class="es-payout-tag mb-4">The ticket</p>
                                <div class="es-payout-lane es-payout-lane-data">
                                    @foreach ($receiptLane as [$stopName, $stopNote, $stopFill])
                                        <div class="es-payout-stop {{ $stopFill ? 'es-payout-stop-fill' : '' }}">
                                            <p class="es-payout-ink text-sm font-bold">{{ $stopName }}</p>
                                            <p class="es-payout-muted mt-0.5 text-xs">{{ $stopNote }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="es-payout-sub mt-7 flex flex-wrap items-baseline justify-between gap-3 p-4">
                            <div>
                                <p class="es-payout-tag">Our cut of the ticket price</p>
                                <p class="es-payout-muted mt-1 text-xs">Every currency. Every plan. Every sale.</p>
                            </div>
                            <p class="es-payout-figure es-payout-accent">0.00</p>
                        </div>
                    </div>

                    <p class="es-payout-muted mt-5 text-xs">
                        Event Schedule appears on the dashed lane only. It hears from Stripe that a payment
                        went through and issues the ticket; it is never a stop the money passes through.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
         2. Stripe on its own (01): the honest table
         ============================================================ --}}
    <section id="compare" class="es-payout-edge scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-payout-mark mb-6" data-reveal aria-hidden="true">01</div>
                <p class="es-payout-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Stripe on its own</p>
                <h2 class="es-balance es-payout-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    What Stripe does by itself, and <span class="es-payout-grad">what a ticket needs on top</span>.
                </h2>
                <p class="es-payout-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    The quickest way to sell anything with Stripe is a Payment Link: you make it in the
                    Stripe Dashboard, share the address, and buyers pay on Stripe's own checkout page. Here
                    is how that compares with selling the same event through Event Schedule, which uses
                    Stripe Checkout underneath.
                </p>
            </div>

            <div class="es-payout-card overflow-hidden" data-reveal="panel">
                <table class="es-payout-compare" role="table">
                    <caption class="sr-only">A Stripe Payment Link on its own compared with Event Schedule charging through your own Stripe account</caption>
                    <thead role="rowgroup">
                        <tr role="row">
                            <th scope="col" role="columnheader"><span class="sr-only">What you need</span></th>
                            <th scope="col" role="columnheader">A Stripe Payment Link</th>
                            <th scope="col" role="columnheader">Event Schedule, on your Stripe account</th>
                        </tr>
                    </thead>
                    <tbody role="rowgroup">
                        @foreach ($compare as [$need, $stripeAlone, $ours])
                            <tr role="row">
                                <th scope="row" role="rowheader">{{ $need }}</th>
                                <td role="cell" data-label="A Stripe Payment Link">{{ $stripeAlone }}</td>
                                <td role="cell" class="es-payout-ours" data-label="Event Schedule">{{ $ours }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2" data-reveal-group="90">
                <div class="es-payout-sub p-5" data-reveal>
                    <p class="es-payout-tag mb-2">When a Payment Link is enough</p>
                    <p class="es-payout-ink text-sm">
                        One price, one date, and a guest list you can read out at the door. It costs nothing
                        beyond Stripe's fee, and you can set it up in minutes.
                    </p>
                </div>
                <div class="es-payout-sub p-5" data-reveal>
                    <p class="es-payout-tag mb-2">When it stops being enough</p>
                    <p class="es-payout-ink text-sm">
                        A ticket for each person, separate stock for each price and each night, a scanner at
                        the door. With Stripe alone that is an automation you build and run yourself,
                        which is the part Event Schedule already is.
                    </p>
                </div>
            </div>

            <p class="es-payout-muted mt-6 text-center text-xs" data-reveal>
                Stripe's side of the table is from Stripe's documentation on
                <x-link href="https://docs.stripe.com/payment-links" target="_blank">Payment Links</x-link>,
                checked in November 2026.
            </p>
        </div>
    </section>

    {{-- ============================================================
         3. What it costs (02): the ladder and the statement
         ============================================================ --}}
    <section id="statement" class="es-payout-edge scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-start gap-14 lg:grid-cols-2 lg:gap-16">
                <div class="min-w-0">
                    <div class="es-payout-mark mb-6" data-reveal aria-hidden="true">02</div>
                    <p class="es-payout-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">What it costs</p>
                    <h2 class="es-balance es-payout-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        One ticket, <span class="es-payout-grad">itemised</span>.
                    </h2>
                    <p class="es-payout-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        Two parties touch a ticket sale, and only one of them takes anything. Stripe deducts
                        its processing fee from the charge. Event Schedule deducts nothing, because there is
                        no fee field in the charge we ask Stripe to create. Here is Stripe's fee at five
                        ticket prices, at its published US card rate of {{ $rateLabel }}.
                    </p>

                    <div class="es-payout-card mb-6 overflow-x-auto p-2 sm:p-3" data-reveal>
                        <table class="es-payout-ladder">
                            <caption class="sr-only">Stripe's processing fee and what you keep at five ticket prices, in US dollars, one ticket per order</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Ticket (USD)</th>
                                    <th scope="col">Stripe</th>
                                    <th scope="col">Share</th>
                                    <th scope="col">Our fee</th>
                                    <th scope="col">You keep</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ladder as $rung)
                                    <tr>
                                        <th scope="row">{{ $money($rung['price']) }}</th>
                                        <td>{{ $money($rung['fee']) }}</td>
                                        <td>{{ $rung['share'] }}%</td>
                                        <td class="es-payout-nil">{{ $money(0) }}</td>
                                        <td class="es-payout-keep">{{ $money($rung['keep']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <ul class="mb-8 space-y-3 text-sm" data-reveal-group="90">
                        @foreach ([
                            ['The fixed part weighs most on cheap tickets.', 'Stripe charges per payment, not per ticket, so an order of four tickets pays the fixed '.$money($rateFixedCents).' once, not four times.'],
                            ['Some cards cost more.', 'Stripe adds 1.5% for an international card and 1% when the currency has to be converted. Other countries have their own rates.'],
                            ['A refund does not return Stripe\'s fee.', 'Stripe keeps the processing fee of the original payment, so a refunded ticket costs you that much.'],
                        ] as [$noteTitle, $noteBody])
                            <li class="flex items-start gap-3" data-reveal>
                                <svg aria-hidden="true" class="es-payout-accent mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span><span class="es-payout-ink font-semibold">{{ $noteTitle }}</span> <span class="es-payout-muted">{{ $noteBody }}</span></span>
                            </li>
                        @endforeach
                    </ul>

                    <p class="mb-4" data-reveal>
                        <span class="es-payout-plan es-payout-plan-pro">Pro</span>
                        <span class="es-payout-muted ms-2 text-sm">Selling tickets that carry a price is {{ plan_price($proMonthly) }} a month, and that is the entire bill from us, however many you sell.</span>
                    </p>
                    <p class="es-payout-muted text-sm" data-reveal>
                        Weighing this against a ticketing platform that takes a share? The
                        <x-link :href="marketing_url('/ticket-fee-calculator')">ticket fee calculator</x-link>
                        works out Eventbrite, Luma, Ticket Tailor and others for your own numbers. Rates
                        from <x-link href="https://stripe.com/pricing" target="_blank">stripe.com/pricing</x-link>.
                    </p>
                </div>

                {{-- The statement sheet. A printed document: it renders
                     identically in light and dark mode, so no descendant
                     here may carry a dark variant. --}}
                <div class="min-w-0 lg:sticky lg:top-28" data-reveal="panel">
                    <div class="es-payout-sheet min-w-0">
                        <div class="es-payout-sheet-head flex flex-wrap items-baseline justify-between gap-2 px-5 py-4 sm:px-7">
                            <p class="es-payout-sheet-title">Statement</p>
                            <p class="es-payout-sheet-meta">one ticket &middot; {{ $stmt['currency'] }}</p>
                        </div>

                        <div class="overflow-x-auto px-5 py-2 sm:px-7">
                            <table class="es-payout-table">
                                <caption class="sr-only">Where the money for one {{ $stmt['currency'] }} {{ $money($stmt['grossCents']) }} ticket goes, line by line</caption>
                                <thead>
                                    <tr>
                                        <th scope="col">Line</th>
                                        <th scope="col" class="es-payout-amount">Amount</th>
                                        <th scope="col" class="es-payout-who">Taken by</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($statementLines as $row)
                                        <tr class="{{ $row['zero'] ? 'es-payout-zero' : '' }}">
                                            <th scope="row">
                                                {{ $row['line'] }}
                                                <span class="es-payout-note">{{ $row['note'] }}</span>
                                            </th>
                                            <td class="es-payout-amount">{{ $row['amount'] }}</td>
                                            <td class="es-payout-who">{{ $row['who'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="es-payout-tear mt-2" aria-hidden="true"></div>

                        <div class="es-payout-stub px-5 py-6 sm:px-7">
                            <div class="flex flex-wrap items-end justify-between gap-4">
                                <div>
                                    <p class="es-payout-stub-label">Settles into your Stripe balance</p>
                                    <p class="es-payout-stub-figure mt-2">{{ $money($stmt['netCents']) }}</p>
                                </div>
                                <span class="es-payout-stamp es-payout-stamp-ink">No platform fee</span>
                            </div>
                            <p class="es-payout-sheet-foot mt-5">
                                Worked at Stripe's published card rate in the United States, {{ $rateLabel }}.
                                Stripe sets that rate itself, per country and per payment method, and Event
                                Schedule adds nothing to it.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
         4. Setup (03): five steps, and the selfhosted variant
         ============================================================ --}}
    <section id="setup" class="es-payout-edge scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-payout-mark mb-6" data-reveal aria-hidden="true">03</div>
                <p class="es-payout-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Setup</p>
                <h2 class="es-balance es-payout-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    From a Stripe account to <span class="es-payout-grad">a ticket at the door</span>.
                </h2>
                <p class="es-payout-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Five steps on the hosted platform, and only the second one happens on Stripe's side.
                    A selfhosted install skips Connect and uses your own keys.
                </p>
            </div>

            <ol class="grid gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="90">
                @foreach ($setup as [$stepNum, $stepTitle, $stepBody])
                    <li class="es-payout-card flex flex-col p-6" data-reveal>
                        <p class="es-payout-setup-num mb-3">Step {{ $stepNum }}</p>
                        <h3 class="es-payout-ink mb-2 text-lg font-bold">{{ $stepTitle }}</h3>
                        <p class="es-payout-muted text-sm">{{ $stepBody }}</p>
                    </li>
                @endforeach

                {{-- The sixth cell closes the grid at three columns: the
                     selfhosted route, which has no Connect step at all. --}}
                <li class="es-payout-sub flex flex-col p-6" data-reveal>
                    <p class="es-payout-setup-num mb-3">Selfhosted</p>
                    <h3 class="es-payout-ink mb-2 text-lg font-bold">Your keys, your account</h3>
                    <p class="es-payout-muted mb-4 text-sm">
                        No Connect layer at all. Put your own Stripe keys and webhook signing secret in the
                        environment file and the install charges through that account directly.
                    </p>
                    <ul class="es-payout-num es-payout-env mb-5 space-y-1.5 text-xs">
                        <li class="es-payout-ink">STRIPE_PLATFORM_KEY</li>
                        <li class="es-payout-ink">STRIPE_PLATFORM_SECRET</li>
                        <li class="es-payout-ink">STRIPE_PLATFORM_WEBHOOK_SECRET</li>
                    </ul>
                    <p class="mt-auto text-sm">
                        <x-link :href="route('marketing.docs.selfhost.stripe')">Selfhosted Stripe setup</x-link>
                    </p>
                </li>
            </ol>

            <p class="es-payout-muted mt-8 text-center text-sm" data-reveal>
                <span class="es-payout-plan es-payout-plan-free me-2">Free</span>
                Connecting Stripe costs nothing and is not gated. Every screen named here is in the
                <x-link :href="route('marketing.docs.account_settings').'#stripe'">Payment Methods guide</x-link>
                and the
                <x-link :href="route('marketing.docs.tickets').'#payment'">selling tickets guide</x-link>.
            </p>
        </div>
    </section>

    {{-- ============================================================
         5. Before a ticket exists (04, fixed dark band)
         ============================================================ --}}
    <section id="gate" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-payout-band noise relative overflow-hidden rounded-[2rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="es-payout-mark mb-6" data-reveal aria-hidden="true">04</div>
                    <p class="es-payout-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Before a ticket exists</p>
                    <h2 class="es-balance mb-5 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        No ticket until <span class="es-payout-grad">the money is really there</span>.
                    </h2>
                    <p class="text-lg text-gray-400" data-reveal style="--reveal-delay: 0.15s;">
                        A ticket that scans is worth money, so one is never issued because a browser came back
                        from Stripe looking pleased. It waits for Stripe to confirm the payment itself, and
                        then it checks four things.
                    </p>
                </div>

                <div class="grid gap-8 lg:grid-cols-5 lg:gap-10">
                    <div class="lg:col-span-3">
                        <div data-reveal-group="90">
                            @foreach ($gate as [$stepNum, $stepTitle, $stepBody])
                                <div class="es-payout-step" data-step="{{ $stepNum }}" data-reveal>
                                    <h3 class="mb-1.5 text-lg font-bold text-white">{{ $stepTitle }}</h3>
                                    <p class="text-sm text-gray-400">{{ $stepBody }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="lg:col-span-2" data-reveal="panel">
                        <div class="es-payout-band-card p-6 sm:p-7">
                            <p class="es-payout-tag mb-5">Two outcomes</p>

                            <div class="mb-5">
                                <span class="es-payout-stamp es-payout-stamp-lit">Paid</span>
                                <p class="mt-3 text-sm text-gray-400">
                                    Stripe confirmed it, the amount matched, and it was not already paid. The
                                    ticket, the QR code and the confirmation email follow.
                                </p>
                            </div>

                            <div class="es-payout-band-rule my-5" aria-hidden="true"></div>

                            <div>
                                <span class="es-payout-stamp es-payout-stamp-mute">Held</span>
                                <p class="mt-3 text-sm text-gray-400">
                                    The figures disagreed by more than a cent. The sale is recorded as a
                                    mismatch for review and no ticket is sent. Nothing is
                                    quietly rounded into place.
                                </p>
                            </div>

                            <p class="mt-6 text-xs text-gray-400">
                                Free tickets and RSVPs skip Stripe entirely; there is no charge to check.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
         6. Other routes (05)
         ============================================================ --}}
    <section id="routes" class="es-payout-edge scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-payout-mark mb-6" data-reveal aria-hidden="true">05</div>
                <p class="es-payout-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Other routes</p>
                <h2 class="es-balance es-payout-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Stripe is one of seven, <span class="es-payout-grad">all of them yours</span>.
                </h2>
                <p class="es-payout-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Every event carries its own route, and a new one starts on cash at the door unless your
                    default ticket preset says otherwise. Not one of these passes through an Event Schedule account.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="90">
                @foreach ($routes as $route)
                    <div class="es-payout-card es-payout-hover flex flex-col p-6 {{ ! empty($route['wide']) ? 'md:col-span-2 lg:col-span-3' : '' }}" data-reveal>
                        <p class="es-payout-tag mb-3">{{ $route['micro'] }}</p>
                        <h3 class="es-payout-ink mb-2 text-lg font-bold">{{ $route['name'] }}</h3>
                        <p class="es-payout-muted mb-5 text-sm">{{ $route['desc'] }}</p>
                        <div class="es-payout-sub mt-auto p-3">
                            <p class="es-payout-tag">Money held by</p>
                            <p class="es-payout-ink mt-1 text-sm font-semibold">{{ $route['held'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 text-center" data-reveal>
                <span class="es-payout-muted text-sm">
                    Invoices and books kept for you:
                    <x-link :href="marketing_url('/invoiceninja')">see the Invoice Ninja integration</x-link>.
                </span>
            </div>
        </div>
    </section>

    {{-- ============================================================
         7. Same charge (06)
         ============================================================ --}}
    <section id="more" class="es-payout-edge scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-payout-mark mb-6" data-reveal aria-hidden="true">06</div>
                <p class="es-payout-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Same charge</p>
                <h2 class="es-balance es-payout-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything else rides <span class="es-payout-grad">the same Stripe payment</span>.
                </h2>
                <p class="es-payout-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Discounts, gift cards and add-ons are priced into the order before Stripe is asked to
                    charge it, which is why the amount check before a ticket can be exact.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">
                @foreach ($sameRail as [$railTitle, $railBody, $railPlan])
                    <div class="es-payout-card es-payout-hover flex flex-col p-6" data-reveal>
                        <div class="mb-2 flex items-center gap-2">
                            <h3 class="es-payout-ink text-lg font-bold">{{ $railTitle }}</h3>
                            <span class="es-payout-plan {{ $railPlan === 'Free' ? 'es-payout-plan-free' : 'es-payout-plan-pro' }}">{{ $railPlan }}</span>
                        </div>
                        <p class="es-payout-muted text-sm">{{ $railBody }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================================
         8. Key features
         ============================================================ --}}
    <section class="es-payout-edge scroll-mt-24 py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-payout-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="Ticket types, QR check-in, and zero platform fees on sales" :url="marketing_url('/features/ticketing')" icon-color="blue">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="QR Check-in" description="Scan tickets at the door from any phone, free on every plan" :url="marketing_url('/features/check-in')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Embed Tickets" description="Put the purchase form on the website you already have" :url="marketing_url('/features/embed-tickets')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Installments" description="Split a ticket into monthly payments, charged through Stripe" :url="marketing_url('/features/installments')" icon-color="teal">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Ticket Fee Calculator" description="What each ticketing platform would take from your own event" :url="marketing_url('/ticket-fee-calculator')" icon-color="amber">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-payout-accent inline-flex items-center font-medium hover:underline">
                    See all features
                    <svg aria-hidden="true" class="ml-1 h-4 w-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    {{-- The shared pricing-nudge partial used to sit here. It repeated the
         plan cards word for word on dozens of pages, and this page already
         states its one price in the statement, the hero and the closing stub. --}}

    {{-- ============================================================
         9. Stripe's own site, and the integrations index
         ============================================================ --}}
    <section class="es-payout-edge py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-4 md:grid-cols-2" data-reveal-group="80">
                <a href="https://stripe.com" target="_blank" rel="noopener noreferrer" data-reveal class="es-payout-card es-payout-hover group flex flex-col p-7">
                    <p class="es-payout-tag mb-3">Official site</p>
                    <h3 class="es-payout-ink mb-2 text-xl font-bold">Learn more about Stripe</h3>
                    <p class="es-payout-muted mb-5 text-sm">
                        Rates by country, supported payment methods, payout terms and the developer
                        documentation, straight from Stripe.
                    </p>
                    <span class="es-payout-accent mt-auto inline-flex items-center gap-2 text-sm font-semibold">
                        Visit stripe.com
                        <svg aria-hidden="true" class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </span>
                </a>

                <a href="{{ marketing_url('/features/integrations') }}" data-reveal class="es-payout-card es-payout-hover group flex flex-col p-7">
                    <p class="es-payout-tag mb-3">Payments and beyond</p>
                    <h3 class="es-payout-ink mb-2 text-xl font-bold">Explore more integrations</h3>
                    <p class="es-payout-muted mb-5 text-sm">
                        PayPal, Invoice Ninja, Google and Outlook calendars, CalDAV, outgoing webhooks and
                        the REST API. Everything Event Schedule connects to.
                    </p>
                    <span class="es-payout-accent mt-auto inline-flex items-center gap-2 text-sm font-semibold">
                        View all integrations
                        <svg aria-hidden="true" class="h-4 w-4 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>
            </div>
        </div>
    </section>

    <x-marketing.related-pages />

    {{-- ============================================================
         10. FAQ (07)
         ============================================================ --}}
    <section id="faq" class="es-payout-edge scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-payout-mark mb-6" data-reveal aria-hidden="true">07</div>
                <p class="es-payout-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Questions</p>
                <h2 class="es-balance es-payout-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Selling tickets with Stripe, <span class="es-payout-grad">answered</span>.
                </h2>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ($faqs as $faq)
                    <details name="faq" data-reveal class="es-payout-card group/faq overflow-hidden">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-6">
                            <h3 class="es-payout-ink text-lg font-semibold">{{ $faq['q'] }}</h3>
                            <svg aria-hidden="true" class="es-payout-muted h-5 w-5 shrink-0 transition-transform duration-300 group-open/faq:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="es-payout-muted faq-answer px-6 pb-6">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>

            <p class="es-payout-muted mt-8 text-center text-sm">
                Setting prices, currencies and payment methods is covered in the
                <x-link :href="route('marketing.docs.tickets').'#payment'">selling tickets guide</x-link>.
            </p>
        </div>
    </section>

    <x-seo.faq-schema :items="$faqs" />

    {{-- ============================================================
         11. Finale
         ============================================================ --}}
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-payout-band noise relative overflow-hidden rounded-[2rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-25"></div>
                </div>

                <div class="relative z-10">
                    <p class="es-payout-tag mb-6">Free to start</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                        Take the money <span class="es-payout-grad">into your own account</span>.
                    </h2>
                    <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                        Claim a schedule, connect Stripe, put a ticket on sale. Stripe charges its
                        processing fee and we charge nothing on the sale.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-lg border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?intent=sell') }}" class="es-payout-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg px-8 py-4 text-lg font-semibold">
                            <span class="relative z-10 flex items-center gap-2">
                                Get started free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="mt-6 text-sm text-gray-400">No credit card required</p>

                    {{-- The stub the statement tears off along: the whole cost
                         of getting paid, and the line we take nothing on. --}}
                    <div class="es-payout-tear mx-auto mt-12 max-w-3xl" aria-hidden="true"></div>
                    <dl class="mx-auto mt-7 grid max-w-3xl gap-7 text-start sm:grid-cols-3 sm:gap-8">
                        @foreach ($remit as [$remitLabel, $remitFigure, $remitNote])
                            <div>
                                <dt class="es-payout-remit-label">{{ $remitLabel }}</dt>
                                <dd class="es-payout-remit-figure mt-2">{{ $remitFigure }}</dd>
                                <dd class="es-payout-remit-note mt-2">{{ $remitNote }}</dd>
                            </div>
                        @endforeach
                    </dl>
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
                        <span class="es-payout-tip pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
