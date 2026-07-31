<x-marketing-layout>
    <x-slot name="title">Stripe Payments for Tickets | The Money Goes Straight to You</x-slot>
    <x-slot name="description">Sell tickets through your own connected Stripe account. The charge is created on your account, there is no platform fee in it, and Stripe pays you out. Cards, Apple Pay and Google Pay through Stripe Checkout.</x-slot>
    <x-slot name="breadcrumbTitle">Stripe</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Stripe Payments",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Event Ticketing Payments",
        "operatingSystem": "Web",
        "description": "Sell tickets through your own connected Stripe account. The charge is created on your account with no platform fee, and Stripe pays you out on its own schedule.",
        "featureList": [
            "Stripe Checkout, so cards and wallets are handled on Stripe's own page",
            "Charges created directly on your connected Stripe account",
            "Zero platform fee: no application fee is added to the charge",
            "Stripe Connect onboarding on the hosted platform",
            "Your own Stripe keys on a selfhosted install",
            "28 ticket currencies to choose from",
            "Signed webhooks confirm payment before a ticket is issued",
            "The charged amount is checked against the ticket total before the sale is marked paid",
            "Promo codes, volume discounts and gift cards priced into the same charge",
            "Invoice Ninja, a payment link, or cash at the door as alternatives",
            "Sales exportable as CSV for your records"
        ],
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free to start, including up to 25 paid tickets a month. Pro at $5 a month lifts the cap."
        },
        "url": "{{ url()->current() }}",
        "keywords": "stripe ticket payments, stripe connect event tickets, zero platform fee ticketing, stripe checkout tickets, direct payouts",
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
           Stripe "The Payout" styles.

           CONCEPT: a payout statement. The page is laid out the way the
           money actually moves, because that is the whole argument. The
           charge for a ticket is created ON THE SELLER'S OWN CONNECTED
           ACCOUNT - TicketController::stripeCheckout() passes
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
    </style>

    @php
        // One ticket, itemised. Every figure on the statement is derived
        // from these four values in cents, so the rows, the stub and the
        // prose can never quote different numbers. The processing rate is
        // Stripe's published US card rate, the same figure the /compare
        // fee calculator uses (MarketingController::getHubFeeRates()).
        $stmt = [
            'currency'    => 'USD',
            'grossCents'  => 2500,
            'ratePercent' => 0.029,
            'rateFixed'   => 30,
        ];
        $stmt['feeCents'] = (int) round($stmt['grossCents'] * $stmt['ratePercent']) + $stmt['rateFixed'];
        $stmt['netCents'] = $stmt['grossCents'] - $stmt['feeCents'];
        $money = fn ($cents) => number_format($cents / 100, 2);

        $statementLines = [
            [
                'line'   => 'Ticket price',
                'note'   => 'The buyer is charged on your connected account.',
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
                'note'   => 'No application fee is added to the charge, so there is nothing on this line.',
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
            ['The Pro plan, monthly', '$5', 'Optional: it lifts the 25-a-month cap. Nothing further is charged on a sale.'],
            ['Card processing', "Stripe's rate", 'Stripe sets it, per country and method.'],
        ];

        // The two lanes. Money on the solid rule, the receipt on the
        // dashed one. Event Schedule appears on the dashed lane only.
        $moneyLane = [
            ['Buyer pays', 'Card, Apple Pay or Google Pay on Stripe Checkout.', false],
            ['Your Stripe account', 'The charge is created on your connected account.', true],
            ['Your bank', 'Stripe pays out to the account you gave Stripe.', false],
        ];
        $receiptLane = [
            ['Stripe signs a webhook', 'A payment event, not money.', false],
            ['Event Schedule reads it', 'Verifies it, reconciles the amount, marks the sale paid.', true],
            ['The ticket goes out', 'Confirmation email with the QR code.', false],
        ];

        // Verified in StripeController::webhook() and success().
        $gate = [
            ['01', 'The signature is checked against the right key', 'A webhook is verified against the Connect signing secret first and the platform secret second. A Connect sale that arrives signed with the platform key is refused rather than trusted.'],
            ['02', 'The charged amount is reconciled', 'The amount Stripe reports is compared with what the ticket should have cost, including any promo code, volume discount or gift card applied. Anything more than a cent apart is parked as a mismatch.'],
            ['03', 'The sale flips to paid once, under a lock', 'The row is locked and the status change happens exactly once. Stripe can send more than one event for a single payment, and the buyer is being redirected back at the same moment, so the lock is what stops a second ticket being issued.'],
            ['04', 'Then the ticket is emailed', 'Only after that does the confirmation email with the QR code go out, and the sale reach your Sales tab and your revenue figures.'],
        ];

        // Event.payment_method, per event. TicketController::checkout()
        // switches on it: stripe, invoiceninja, payment_url, cash.
        $routes = [
            [
                'name'  => 'Stripe',
                'held'  => 'Your own Stripe account',
                'desc'  => 'Stripe Checkout handles the card, the wallet and the receipt. The charge is created on your connected account and settles there.',
                'micro' => 'Cards and wallets',
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
                'desc'  => 'Any URL you own. Buyers are sent there to pay, and you reconcile the sale yourself on the Sales tab.',
                'micro' => 'Bring your own',
            ],
            [
                'name'  => 'Cash at the door',
                'held'  => 'Your till',
                'desc'  => 'No card at all. You write the payment instructions the buyer sees, and mark the sale paid when the money arrives.',
                'micro' => 'Marked paid by hand',
            ],
        ];

        // Everything else that gets priced into the same charge or rides
        // the same webhook. Each traced in the report.
        $sameRail = [
            ['Promo codes', 'A percentage or a fixed discount, with usage limits and an expiry, priced into the line items before the charge is created.', 'Pro'],
            ['Volume discounts', 'Buy more of one ticket type and the per-unit price drops. The reconciliation spreads the rounding a cent at a time so the charge matches the total exactly.', 'Free'],
            ['Gift cards', 'Sold through the same Stripe account and redeemed against a later ticket. A gift-card payment is checked harder still: the connected account that paid has to be the one selling the card, and the currency has to match.', 'Pro'],
            ['Add-ons', 'A drink, a programme, a workshop place. Priced as its own line on the same charge rather than a second checkout.', 'Pro'],
            ['Appointments', 'A bookable slot can take payment through the same connected account, or a payment link, or cash. One appointment type on the free plan, as many as you like on Pro.', 'Free'],
            ['Outgoing webhooks', 'When a sale is marked paid, Event Schedule can POST it to an endpoint you own, so your own systems hear about it too.', 'Pro'],
        ];

        $faqs = [
            [
                'q' => 'Does Event Schedule take a cut of ticket sales?',
                'a' => 'No. On the hosted platform the charge is created on your own connected Stripe account and no application fee is added to it, so there is no line where a platform cut could be taken. You pay Stripe its processing fee and keep the rest. Selling starts on the free plan, at up to 25 paid tickets a month, and the Pro plan at $5 a month lifts that cap. The subscription is the whole of what Event Schedule charges.',
            ],
            [
                'q' => 'How do I connect Stripe, and what happens if it is not finished?',
                'a' => 'One button hands you to Stripe\'s own onboarding. When you come back, Event Schedule checks a single thing: whether your account can accept charges. Until that is true the account reads as Pending and Stripe is not offered as a payment method on an event. The only details kept here are the account id, the business name Stripe reports and the date the account was confirmed. Unlinking drops the id and the confirmation, and Stripe stops being offered on your events.',
            ],
            [
                'q' => 'How do payouts reach my bank?',
                'a' => 'Stripe pays you, not us, because the money was never in an Event Schedule account. Your payout schedule, your balance and your payout history all live in your Stripe Dashboard, on whatever terms Stripe applies to your account and country.',
            ],
            [
                'q' => 'Which payment methods can buyers use?',
                'a' => 'Whatever your Stripe account has switched on. Event Schedule sends buyers to Stripe Checkout without pinning the method list, so cards, Apple Pay, Google Pay and the local methods Stripe enables for you all appear. Buyers never type card details into an Event Schedule page.',
            ],
            [
                'q' => 'Which currency are tickets priced in?',
                'a' => 'You pick a currency per event from a list of 28, including USD, EUR, GBP, CAD, AUD, JPY, INR, BRL, ILS, ZAR and SGD. Stripe is charged in that currency, and zero-decimal currencies such as JPY and KRW are handled in whole units rather than cents.',
            ],
            [
                'q' => 'What happens if the buyer closes the tab on the way back?',
                'a' => 'Nothing is lost. The redirect back from Stripe only records the payment reference; it is the signed webhook that marks the sale paid and sends the ticket. If Stripe delivers more than one event for the same payment, the row lock means the sale still transitions exactly once.',
            ],
            [
                'q' => 'How do refunds work?',
                'a' => 'You refund in your Stripe Dashboard, because that is where the money is. Marking the sale refunded in Event Schedule updates the record, invalidates the ticket and takes the amount back out of your revenue figures, but it does not move money on its own.',
            ],
            [
                'q' => 'Can a selfhosted install take Stripe payments?',
                'a' => 'Yes. Instead of Stripe Connect you put your own Stripe keys and webhook signing secret in the environment file, and the install charges through that account directly. The setup, including which webhook events to send, is written up in the selfhosted Stripe guide.',
            ],
        ];

        $dotSections = [
            ['top', 'The route'],
            ['statement', 'The statement'],
            ['wiring', 'The wiring'],
            ['gate', 'The gate'],
            ['routes', 'Other routes'],
            ['more', 'Same rail'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];
    @endphp

    <div id="es-payout-page" class="es-payout-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the two lanes                                       -->
    <!-- ============================================================ -->
    {{-- The nav overlays the top of the page, so the hero carries extra
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
                    <p class="es-payout-tag es-fade-up es-d-1 mb-5">Stripe payments</p>

                    <h1 class="es-balance mb-7 text-[2.6rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">The money never</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="es-payout-grad">lands with us</span>.</span></span>
                    </h1>

                    <p class="es-payout-muted es-fade-up es-d-2 mb-9 max-w-xl text-lg sm:text-xl">
                        The charge for a ticket is created on your own connected Stripe account, and no
                        application fee is added to it. Zero platform fees is not a promise we make about
                        our pricing. It is a line on the statement with nothing on it.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ app_url('/sign_up') }}" class="es-payout-btn inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            Get started free
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                        <a href="#statement" class="es-payout-ghost inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            Read the statement
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                    </div>
                </div>

                <!-- The two lanes. Money on the solid rule, receipt on
                     the dashed one. We are only on the dashed one. -->
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
                                <p class="es-payout-tag mb-4">The receipt</p>
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
                        Event Schedule appears on the dashed lane only. It reads a signed payment event and
                        issues a ticket; it is never a stop the money passes through.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The statement (01)                                        -->
    <!-- ============================================================ -->
    <section id="statement" class="es-payout-edge scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-start gap-14 lg:grid-cols-2 lg:gap-16">
                <div>
                    <div class="es-payout-mark mb-6" data-reveal aria-hidden="true">01</div>
                    <p class="es-payout-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The statement</p>
                    <h2 class="es-balance es-payout-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        One ticket, <span class="es-payout-grad">itemised</span>.
                    </h2>
                    <p class="es-payout-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        Two parties touch a ticket sale, and only one of them takes anything. Stripe deducts
                        its processing fee from the charge. Event Schedule deducts nothing, because there is
                        no fee field in the charge we ask Stripe to create.
                    </p>

                    <ul class="mb-8 space-y-4" data-reveal-group="90">
                        @foreach ([
                            ['Charged on your account', 'The Checkout session is created against your connected account id, so the payment is yours from the first cent rather than something we forward on.'],
                            ['No application fee', 'The parameter that would let a platform skim a charge is simply not in our code. It cannot be switched on for your schedule and not another.'],
                            ['Nothing to withdraw', 'A pricing page can change its mind about a percentage. Wiring is harder to change quietly, and this is wiring.'],
                        ] as [$claimTitle, $claimBody])
                            <li class="flex items-start gap-3" data-reveal>
                                <svg aria-hidden="true" class="es-payout-accent mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span><span class="es-payout-ink font-semibold">{{ $claimTitle }}</span> <span class="es-payout-muted">- {{ $claimBody }}</span></span>
                            </li>
                        @endforeach
                    </ul>

                    <p data-reveal>
                        <span class="es-payout-plan es-payout-plan-free">Free</span>
                        <span class="es-payout-muted ms-2 text-sm">Selling starts free, at 25 paid tickets a month. Pro lifts the cap for $5 a month, and that is the entire bill from us.</span>
                    </p>
                </div>

                <!-- The statement sheet. A printed document: it renders
                     identically in light and dark mode, so no descendant
                     here may carry a dark variant. -->
                <div class="min-w-0" data-reveal="panel">
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
                                Worked at Stripe's published card rate in the United States,
                                {{ number_format($stmt['ratePercent'] * 100, 1) }}% plus {{ $money($stmt['rateFixed']) }}.
                                Stripe sets that rate itself, per country and per payment method, and Event
                                Schedule adds nothing to it.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The wiring (02)                                           -->
    <!-- ============================================================ -->
    <section id="wiring" class="es-payout-edge scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-payout-mark mb-6" data-reveal aria-hidden="true">02</div>
                <p class="es-payout-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The wiring</p>
                <h2 class="es-balance es-payout-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Two installs, <span class="es-payout-grad">one owner</span>.
                </h2>
                <p class="es-payout-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Hosted or on your own server, the account the money lands in belongs to you. Only the
                    way you attach it differs.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2" data-reveal-group="100">
                <div class="es-payout-card es-payout-hover p-7" data-reveal>
                    <p class="es-payout-tag mb-3">Hosted at eventschedule.com</p>
                    <h3 class="es-payout-ink mb-3 text-xl font-bold">Stripe Connect, one button</h3>
                    <p class="es-payout-muted mb-5 text-sm">
                        Connect Stripe and you are handed to Stripe's own onboarding. Coming back, Event
                        Schedule checks a single thing: whether the account can accept charges yet.
                    </p>
                    <div class="es-payout-sub p-4">
                        <dl class="space-y-2.5 text-sm">
                            <div class="flex justify-between gap-4">
                                <dt class="es-payout-muted">Until charges are enabled</dt>
                                <dd class="es-payout-ink es-payout-num text-xs font-semibold uppercase">Pending</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="es-payout-muted">What we store</dt>
                                <dd class="es-payout-ink text-end">Account id, business name</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="es-payout-muted">Unlink</dt>
                                <dd class="es-payout-ink text-end">Drops the link, one click</dd>
                            </div>
                        </dl>
                    </div>
                    <p class="es-payout-muted mt-4 text-xs">
                        While an account is pending, Stripe is not offered as a payment method on an event,
                        so nothing can be put on sale that cannot be paid for.
                    </p>
                </div>

                <div class="es-payout-card es-payout-hover p-7" data-reveal>
                    <p class="es-payout-tag mb-3">Selfhosted on your server</p>
                    <h3 class="es-payout-ink mb-3 text-xl font-bold">Your keys, your account</h3>
                    <p class="es-payout-muted mb-5 text-sm">
                        No Connect layer at all. Put your own Stripe keys and webhook signing secret in the
                        environment file and the install charges through that account directly.
                    </p>
                    <div class="es-payout-sub p-4">
                        <ul class="es-payout-num es-payout-env space-y-1.5 text-xs">
                            <li class="es-payout-ink">STRIPE_PLATFORM_KEY</li>
                            <li class="es-payout-ink">STRIPE_PLATFORM_SECRET</li>
                            <li class="es-payout-ink">STRIPE_PLATFORM_WEBHOOK_SECRET</li>
                        </ul>
                    </div>
                    <p class="es-payout-muted mt-4 text-xs">
                        The Payment Methods settings report whether the server is configured, so you can see
                        it is live without reading a log.
                        <a href="{{ route('marketing.docs.selfhost.stripe') }}" class="es-payout-accent font-semibold underline">Selfhosted Stripe setup</a>
                    </p>
                </div>
            </div>

            <div class="mt-8 text-center" data-reveal>
                <span class="es-payout-plan es-payout-plan-free">Free</span>
                <span class="es-payout-muted ms-2 text-sm">
                    Connecting Stripe costs nothing and is not gated. Nor is selling: the free plan takes
                    payment for up to 25 paid tickets a month, and Pro removes the ceiling.
                </span>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. The gate (03, fixed dark band)                            -->
    <!-- ============================================================ -->
    <section id="gate" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-payout-band noise relative overflow-hidden rounded-[2rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="es-payout-mark mb-6" data-reveal aria-hidden="true">03</div>
                    <p class="es-payout-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The gate</p>
                    <h2 class="es-balance mb-5 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        No ticket until <span class="es-payout-grad">the numbers agree</span>.
                    </h2>
                    <p class="text-lg text-gray-400" data-reveal style="--reveal-delay: 0.15s;">
                        A ticket is not issued because a browser came back from Stripe looking pleased. It is
                        issued because a signed payment event arrived and survived four checks.
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
                                    The signature matched, the amount matched, the lock was taken. The ticket,
                                    the QR code and the confirmation email follow.
                                </p>
                            </div>

                            <div class="es-payout-band-rule my-5" aria-hidden="true"></div>

                            <div>
                                <span class="es-payout-stamp es-payout-stamp-mute">Held</span>
                                <p class="mt-3 text-sm text-gray-400">
                                    The figures disagreed by more than a cent. The sale is recorded as a
                                    mismatch, the discrepancy is logged, and no ticket is sent. Nothing is
                                    quietly rounded into place.
                                </p>
                            </div>

                            <p class="mt-6 text-xs text-gray-400">
                                Free tickets and RSVPs skip Stripe entirely; there is no charge to reconcile.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Other routes (04)                                         -->
    <!-- ============================================================ -->
    <section id="routes" class="es-payout-edge scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-payout-mark mb-6" data-reveal aria-hidden="true">04</div>
                <p class="es-payout-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Other routes</p>
                <h2 class="es-balance es-payout-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Four lanes, <span class="es-payout-grad">all of them yours</span>.
                </h2>
                <p class="es-payout-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Every event carries its own route, and a new one starts on cash at the door until you
                    change it. A paid workshop can take cards while a community night takes money at the desk.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4" data-reveal-group="90">
                @foreach ($routes as $route)
                    <div class="es-payout-card es-payout-hover flex flex-col p-6" data-reveal>
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
                    Not one of these routes passes through an Event Schedule account.
                    <a href="{{ marketing_url('/invoiceninja') }}" class="es-payout-accent font-semibold underline">See the Invoice Ninja integration</a>
                </span>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Same rail (05)                                            -->
    <!-- ============================================================ -->
    <section id="more" class="es-payout-edge scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-payout-mark mb-6" data-reveal aria-hidden="true">05</div>
                <p class="es-payout-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Same rail</p>
                <h2 class="es-balance es-payout-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything else rides <span class="es-payout-grad">the same charge</span>.
                </h2>
                <p class="es-payout-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Discounts, gift cards and add-ons are priced into the line items before the charge is
                    created, which is why the reconciliation at the gate can be exact.
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

            <div class="mt-8 text-center" data-reveal>
                <span class="es-payout-muted text-sm">
                    Event Schedule does not calculate or collect sales tax. Price tickets inclusive of what
                    you owe, and export the sales as CSV when it is time to file.
                </span>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Key features                                              -->
    <!-- ============================================================ -->
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
                    <x-feature-link-card name="Embed Tickets" description="Put the purchase form on the website you already have" :url="marketing_url('/features/embed-tickets')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Gift Cards" description="Sell balance through the same account, redeem against later tickets" :url="marketing_url('/features/gift-cards')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Analytics" description="Revenue, sales and views without a third-party tracker" :url="marketing_url('/features/analytics')" icon-color="amber">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg></x-slot:icon>
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

    @include('marketing.partials.pricing-nudge')

    <!-- ============================================================ -->
    <!-- 8. Stripe's own site, and the integrations index              -->
    <!-- ============================================================ -->
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
                        Invoice Ninja, Google and Outlook calendars, CalDAV, outgoing webhooks and the read
                        API. Everything Event Schedule connects to.
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

    <!-- ============================================================ -->
    <!-- 9. FAQ (06)                                                  -->
    <!-- ============================================================ -->
    <section id="faq" class="es-payout-edge scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-payout-mark mb-6" data-reveal aria-hidden="true">06</div>
                <p class="es-payout-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Questions</p>
                <h2 class="es-balance es-payout-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Asked <span class="es-payout-grad">before signing up</span>.
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
                <a href="{{ route('marketing.docs.tickets') }}#payment" class="es-payout-accent font-semibold underline">selling tickets guide</a>.
            </p>
        </div>
    </section>

    <x-seo.faq-schema :items="$faqs" />

    <!-- ============================================================ -->
    <!-- 10. Finale                                                   -->
    <!-- ============================================================ -->
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
                        <a href="{{ app_url('/sign_up') }}" class="es-payout-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg px-8 py-4 text-lg font-semibold">
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
