<x-marketing-layout>
    <x-slot name="title">Invoice Ninja Ticketing | Post Every Sale to the Books You Keep</x-slot>
    <x-slot name="description">Sell tickets in Event Schedule and the entry lands in Invoice Ninja: a client, a line item per ticket type, the QR code printed on the invoice, and the payment reconciled to the cent. Hosted or selfhosted.</x-slot>
    <x-slot name="breadcrumbTitle">Invoice Ninja</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Invoice Ninja Integration",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Event Ticketing and Invoicing",
        "operatingSystem": "Web",
        "description": "Sell tickets in Event Schedule and the entry lands in Invoice Ninja: a client matched by email and currency, a line item per ticket type, discounts as negative lines, the QR ticket printed on the invoice, and the payment reconciled to the cent.",
        "featureList": [
            "An invoice created in your Invoice Ninja company for every ticket purchase",
            "Clients matched by email address and currency, or created when new",
            "A line item per ticket type, with promo codes, volume discounts and gift cards as negative lines",
            "QR code tickets printed in the invoice, so the invoice is the ticket",
            "Payment tracking by webhook, with the amount reconciled to the cent before a sale is marked paid",
            "Two checkout modes: an invoice per purchase, or an Invoice Ninja payment link with grouped invoices",
            "Invoices for corporate buyers who need paperwork for expenses",
            "104 currency codes mapped to Invoice Ninja currencies",
            "Works with a selfhosted Invoice Ninja install or with invoicing.co",
            "Zero platform fees on ticket sales"
        ],
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD"
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
           Invoiceninja "The Ledger" styles.

           CONCEPT: the reader already keeps books. They do not want a
           second set. So the page is a LEDGER - two facing columns, the
           sale on the left and the posting on the right - and the
           product argument is the same sentence as the metaphor: every
           ticket sale is written into the Invoice Ninja company you
           already use, in the same words you would have used, and the
           two sides are reconciled to the cent before anything is
           called paid.

           THE DEVICES:
             1. A green-bar accounting sheet (.es-ledg-sheet). Real
                continuous-form ledger paper: cream stock with pale
                green bars. It is a PHYSICAL OBJECT, so it renders
                IDENTICALLY with .dark on and off - no dark: utilities
                may be used inside it, and nothing inside it may carry
                one. Verified with --bands=.es-ledg-sheet (0 diffs).
             2. A real <table> of postings, one row per thing the
                integration writes, each row traceable to code
                (InvoiceNinja::findClient / createClient / createInvoice,
                TicketController::invoiceninjaInvoiceCheckout).
             3. Ledger typography instead of ornament: a serif
                letterspaced caption face (.es-ledg-tag), tabular
                monospace figures (.es-ledg-num), and the accountant's
                3px DOUBLE RULE (border-bottom: 3px double) as the
                section mark and the totals rule. No illustrations.
             4. A dark reconciliation band (.es-ledg-band) that LEADS with
                THE AGREEMENT: the two figures being compared and the
                difference between them, footed on the sheet's own double
                rule (.es-ledg-agree-rule). The three checks follow as
                annotations on it.

           ANTI-COLLISION, and this is binding. /stripe "The Payout" is
           the sibling payments page and it already owns two things this
           page must never borrow:
             - the ROTATED RULED RUBBER STAMP as a recurring mark, down
               to the word PAID (.es-payout-stamp / .es-payout-mark).
               This page had one and it was removed; the hero entry now
               carries a clerk's cross reference (.es-ledg-xref) into
               folio 01 instead, which is ledger-native and is also a
               real link.
             - a NUMBERED GATE STACK in a dark band as the lead device
               for the same webhook code path. Hence the agreement figure
               above: this page argues the arithmetic, not the procedure.
           Do not reintroduce either.

           COLOUR: the page's existing accent hue family was emerald ->
           teal -> cyan. Teal and cyan are both spoken for elsewhere in
           the campaign, so the tail is dropped and only the EMERALD
           head is kept, pushed deep and desaturated into ledger-ink
           green - the green of a credit column, not a mint accent.
           Nothing on the page is teal or cyan.

           MEASURED (see scratchpad/ledg-pal.mjs):
             light ground #f6f8f4: ink #131a15 16.57, muted #4b544d 7.35,
               accent #0a6a44 6.22, grad stops #075e3c 7.35 / #0f7a4e 5.02
             light card #fdfefc: muted 7.76, accent 6.57
             dark ground #0c110e: ink #eaf1ea 16.58, muted #a2b0a7 8.44,
               accent #62d69f 10.56, grad stops #7ee5b0 12.45 / #62d69f 10.56
             dark card #161d18: muted 7.61, accent 9.52
             sheet #fbfdf9 / bar #dfeadb: ink #1a2318 15.81 / 13.05,
               muted #4d564d 7.45 / 6.15, accent #0a5c3c 7.86 / 6.49
             band #101613: white 18.32, gray-400 7.22, lit #7ee0b0 11.50
             band panel #18211c: #d7e2da 12.39, gray-400 6.50, lit 10.35
             buttons: white on #0a6a44 6.65, on hover #085636 8.77
           NEVER text-gray-500 on these grounds - use .es-ledg-muted.
           ============================================================== */

        /* --- Ground and ink --- */
        .es-ledg-page { background-color: #f6f8f4; color: #131a15; }
        .dark .es-ledg-page { background-color: #0c110e; color: #eaf1ea; }
        .es-ledg-ink { color: #131a15; }
        .dark .es-ledg-ink { color: #eaf1ea; }
        .es-ledg-muted { color: #4b544d; }
        .dark .es-ledg-muted { color: #a2b0a7; }
        .es-ledg-accent { color: #0a6a44; }
        .dark .es-ledg-accent { color: #62d69f; }
        /* Always-lit accent, for use on the dark band in BOTH colour modes. */
        .es-ledg-lit { color: #7ee0b0; }

        .es-ledg-grad {
            background-image: linear-gradient(96deg, #075e3c, #0f7a4e);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dark .es-ledg-grad,
        .es-ledg-band .es-ledg-grad {
            background-image: linear-gradient(96deg, #7ee5b0, #62d69f);
        }

        /* --- Ledger typography ---------------------------------------
           A letterspaced serif caption and tabular figures do the work
           that an illustration would otherwise be asked to do. */
        .es-ledg-tag {
            font-family: ui-serif, Georgia, 'Times New Roman', serif;
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            color: #0a6a44;
        }
        .dark .es-ledg-tag { color: #62d69f; }
        .es-ledg-band .es-ledg-tag { color: #7ee0b0; }

        .es-ledg-num {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, 'Liberation Mono', monospace;
            font-variant-numeric: tabular-nums;
        }

        /* The accountant's double rule, used as the section mark and
           again as the totals rule inside the sheet. */
        .es-ledg-folio {
            display: inline-block;
            padding-bottom: 0.4rem;
            border-bottom: 3px double #0a6a44;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #4b544d;
        }
        .dark .es-ledg-folio { border-bottom-color: #62d69f; color: #a2b0a7; }
        .es-ledg-band .es-ledg-folio { border-bottom-color: #7ee0b0; color: #9ca3af; }

        /* --- Page surfaces --- */
        .es-ledg-card {
            background-color: #fdfefc;
            border: 1px solid rgba(19, 26, 21, 0.12);
            border-radius: 0.45rem;
        }
        .dark .es-ledg-card {
            background-color: #161d18;
            border-color: rgba(234, 241, 234, 0.13);
        }
        .es-ledg-sub {
            background-color: #eceee9;
            border-radius: 0.3rem;
        }
        .dark .es-ledg-sub { background-color: #1d2620; }
        .es-ledg-hover { transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        .es-ledg-hover:hover {
            border-color: rgba(10, 106, 68, 0.5);
            box-shadow: 0 12px 30px -20px rgba(19, 26, 21, 0.55);
        }
        .dark .es-ledg-hover:hover {
            border-color: rgba(98, 214, 159, 0.42);
            box-shadow: 0 12px 30px -20px rgba(0, 0, 0, 0.85);
        }
        .es-ledg-hair { height: 1px; margin: 3rem 0; background-color: rgba(19, 26, 21, 0.12); }
        .dark .es-ledg-hair { background-color: rgba(234, 241, 234, 0.14); }
        /* Section separators. These live here rather than in a
           border-[rgba(...)] utility: an arbitrary Tailwind value that is
           not already in the built bundle paints nothing, and these
           separators silently disappeared. */
        .es-ledg-rule-t { border-top: 1px solid rgba(19, 26, 21, 0.1); }
        .dark .es-ledg-rule-t { border-top-color: rgba(234, 241, 234, 0.1); }

        /* --- The green-bar sheet -------------------------------------
           FIXED PHYSICAL OBJECT. Identical in both colour modes: it has
           no .dark rules and nothing inside it may carry a dark:
           utility. Cream stock, pale green bars, a hairline border and
           the shadow of a sheet lying on the page. */
        .es-ledg-sheet {
            background-color: #fbfdf9;
            border: 1px solid rgba(26, 35, 24, 0.18);
            border-radius: 0.35rem;
            box-shadow: 0 20px 44px -30px rgba(0, 0, 0, 0.6);
            color: #1a2318;
        }
        .es-ledg-sheet-ink { color: #1a2318; }
        .es-ledg-sheet-muted { color: #4d564d; }
        .es-ledg-sheet-accent { color: #0a5c3c; }
        .es-ledg-sheet-hair { height: 1px; background-color: rgba(26, 35, 24, 0.16); }
        .es-ledg-sheet-head {
            font-family: ui-serif, Georgia, 'Times New Roman', serif;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: #0a5c3c;
        }
        /* A pale green bar, the paper's own stripe. */
        .es-ledg-bar { background-color: #dfeadb; }
        /* The totals rule: the same double rule, in sheet ink. */
        .es-ledg-total {
            border-top: 3px double rgba(26, 35, 24, 0.55);
            padding-top: 0.55rem;
        }
        /* A clerk's CROSS REFERENCE, written in the margin of the entry:
           the contra folio the sale was posted to. Deliberately not a
           rotated ruled stamp - /stripe "The Payout" already owns that
           as its recurring mark, down to the word PAID, and this page
           must not borrow it. This one is also a real link, so the hero
           entry and folio 01 are one object rather than two pictures. */
        .es-ledg-xref {
            display: inline-flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.2rem;
        }
        .es-ledg-xref-label {
            font-family: ui-serif, Georgia, 'Times New Roman', serif;
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: #4d564d;
        }
        .es-ledg-xref-ref {
            padding-bottom: 0.25rem;
            border-bottom: 3px double rgba(10, 92, 60, 0.7);
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #0a5c3c;
            transition: color 0.2s ease, border-bottom-color 0.2s ease;
        }
        .es-ledg-xref:hover .es-ledg-xref-ref {
            border-bottom-color: #08492f;
            color: #08492f;
        }

        /* --- The posting table --------------------------------------
           A record, so it is a real table. It scrolls inside its own
           wrapper rather than pushing the page sideways. */
        .es-ledg-scroll { overflow-x: auto; }
        .es-ledg-table {
            width: 100%;
            min-width: 34rem;
            border-collapse: collapse;
            text-align: left;
        }
        .es-ledg-table caption { text-align: left; }
        .es-ledg-table thead th {
            padding: 0.5rem 0.75rem 0.6rem;
            border-bottom: 3px double rgba(26, 35, 24, 0.55);
            font-family: ui-serif, Georgia, 'Times New Roman', serif;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #0a5c3c;
            white-space: nowrap;
        }
        .es-ledg-table tbody th,
        .es-ledg-table tbody td {
            padding: 0.7rem 0.75rem;
            vertical-align: top;
            font-size: 0.875rem;
            font-weight: 400;
            border-bottom: 1px solid rgba(26, 35, 24, 0.1);
        }
        .es-ledg-table tbody th { color: #1a2318; font-weight: 700; width: 34%; }
        .es-ledg-table tbody td { color: #4d564d; }
        .es-ledg-table tbody tr:nth-child(even) { background-color: #dfeadb; }
        .es-ledg-table tfoot th,
        .es-ledg-table tfoot td {
            padding: 0.7rem 0.75rem;
            border-top: 3px double rgba(26, 35, 24, 0.55);
            font-size: 0.8rem;
            color: #1a2318;
            text-align: left;
        }
        .es-ledg-table tfoot th { font-weight: 700; }
        /* Narrow screens: the record stays a real table for semantics and
           for search, but each row stacks so nothing is clipped and there
           is no sideways scroll to discover. The column heads stay in the
           accessibility tree. */
        @media (max-width: 40rem) {
            .es-ledg-table { min-width: 0; }
            .es-ledg-table thead {
                position: absolute;
                width: 1px;
                height: 1px;
                overflow: hidden;
                clip-path: inset(50%);
                white-space: nowrap;
            }
            .es-ledg-table tbody tr,
            .es-ledg-table tfoot tr { display: block; }
            .es-ledg-table tbody tr { border-bottom: 1px solid rgba(26, 35, 24, 0.1); }
            .es-ledg-table tbody th,
            .es-ledg-table tbody td {
                display: block;
                width: auto;
                border-bottom: 0;
            }
            .es-ledg-table tbody th { padding-bottom: 0.1rem; }
            .es-ledg-table tbody td { padding-top: 0.1rem; }
            .es-ledg-table tfoot tr { border-top: 3px double rgba(26, 35, 24, 0.55); }
            .es-ledg-table tfoot th,
            .es-ledg-table tfoot td { display: block; border-top: 0; }
            .es-ledg-table tfoot th { padding-bottom: 0.1rem; }
            .es-ledg-table tfoot td { padding-top: 0.1rem; }
        }

        [dir="rtl"] .es-ledg-table { text-align: right; }
        [dir="rtl"] .es-ledg-table caption,
        [dir="rtl"] .es-ledg-table tfoot th,
        [dir="rtl"] .es-ledg-table tfoot td { text-align: right; }

        /* --- Plan pills. Tiers ONLY, never a state badge. --- */
        .es-ledg-plan {
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
        .es-ledg-plan-free { border-color: rgba(19, 26, 21, 0.24); color: #4b544d; }
        .dark .es-ledg-plan-free { border-color: rgba(234, 241, 234, 0.26); color: #a2b0a7; }
        .es-ledg-plan-pro {
            border-color: rgba(10, 106, 68, 0.5);
            background-color: rgba(10, 106, 68, 0.08);
            color: #0a6a44;
        }
        .dark .es-ledg-plan-pro {
            border-color: rgba(98, 214, 159, 0.42);
            background-color: rgba(98, 214, 159, 0.1);
            color: #62d69f;
        }

        /* --- Buttons --- */
        .es-ledg-btn {
            background-color: #0a6a44;
            color: #ffffff;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .es-ledg-btn:hover {
            background-color: #085636;
            transform: translateY(-1px);
            box-shadow: 0 14px 30px -18px rgba(8, 86, 54, 0.95);
        }
        .es-ledg-ghost {
            border: 1px solid rgba(19, 26, 21, 0.22);
            color: #131a15;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }
        .es-ledg-ghost:hover { border-color: rgba(10, 106, 68, 0.55); background-color: rgba(10, 106, 68, 0.06); }
        .dark .es-ledg-ghost { border-color: rgba(234, 241, 234, 0.24); color: #eaf1ea; }
        .dark .es-ledg-ghost:hover { border-color: rgba(98, 214, 159, 0.45); background-color: rgba(98, 214, 159, 0.08); }

        /* --- The reconciliation band --------------------------------
           A resolvable background-color sits under the gradient so text
           over it is scored against something real. */
        .es-ledg-band {
            border: 1px solid rgba(255, 255, 255, 0.09);
            background-color: #101613;
            background-image:
                radial-gradient(ellipse 72% 52% at 50% 0%, rgba(10, 106, 68, 0.5), rgba(10, 106, 68, 0) 70%),
                linear-gradient(180deg, #18211c, #101613);
        }
        .es-ledg-panel {
            background-color: #18211c;
            border: 1px solid rgba(255, 255, 255, 0.09);
            border-radius: 0.45rem;
        }
        .es-ledg-band-ink { color: #d7e2da; }

        /* THE AGREEMENT. A reconciliation is two figures and the
           difference between them, so the band leads with one instead of
           with a numbered stack of steps - /stripe "The Payout" already
           uses a numbered gate in a dark band for the same underlying
           code path, and this page's argument is the arithmetic, not the
           procedure. The rule is the sheet's own totals rule, carried
           onto the dark ground so the two objects read as one book. */
        .es-ledg-agree-rule { border-top: 3px double rgba(215, 226, 218, 0.5); }

        /* Nothing inside the band may change between colour modes. These
           three shared classes carry their own .dark rules in
           marketing.css and are invisible to a grep of this file. */
        .es-ledg-band .grid-overlay {
            background-image:
                linear-gradient(rgba(234, 241, 234, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(234, 241, 234, 0.05) 1px, transparent 1px);
        }
        .es-ledg-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-ledg-band .es-claim:focus-within {
            border-color: rgba(126, 224, 176, 0.75);
            box-shadow: 0 0 0 4px rgba(126, 224, 176, 0.22);
        }

        /* The dot-nav tooltip. Its colours live HERE, not in a
           dark:bg-[#hex] utility: arbitrary Tailwind values that are not
           already in the built bundle do nothing, and the tooltip then
           kept a white ground under light ink in dark mode. */
        .es-ledg-tip {
            background-color: #ffffff;
            border: 1px solid rgba(19, 26, 21, 0.14);
            color: #374151;
        }
        .dark .es-ledg-tip {
            background-color: #161d18;
            border-color: rgba(234, 241, 234, 0.14);
            color: #d1d5db;
        }

        /* Shared chrome that is hard-coded brand blue. */
        .es-dot:hover .es-dot-pip { background-color: rgba(10, 106, 68, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(98, 214, 159, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #0a6a44; }
        .dark .es-dot.is-active .es-dot-pip { background: #62d69f; }

        /* Focus rings. Never set border-radius here: an outline already
           follows the element's own radius. */
        #es-ledg-page a:focus-visible,
        #es-ledg-page summary:focus-visible,
        #es-ledg-page button:focus-visible,
        #es-ledg-page input:focus-visible {
            outline: 2px solid #0a6a44;
            outline-offset: 2px;
        }
        .dark #es-ledg-page a:focus-visible,
        .dark #es-ledg-page summary:focus-visible,
        .dark #es-ledg-page button:focus-visible,
        .dark #es-ledg-page input:focus-visible {
            outline-color: #62d69f;
        }
        .es-ledg-band a:focus-visible,
        .es-ledg-band summary:focus-visible,
        .es-ledg-band button:focus-visible,
        .es-ledg-band input:focus-visible {
            outline-color: #7ee0b0 !important;
        }
        .es-ledg-sheet a:focus-visible { outline-color: #0a5c3c !important; }

        @media (prefers-reduced-motion: reduce) {
            .es-ledg-btn:hover { transform: none; }
        }
    </style>

    @php
        // One purchase, and every figure on the page comes from here so the
        // sale side and the posted side cannot disagree. Asserted below:
        // qty * unit - discount == total.
        $entry = [
            'invoice'  => '1042',
            'event'    => 'Jazz Night',
            'ticket'   => 'VIP admission',
            'qty'      => 2,
            'unit'     => 75.00,
            'promo'    => 'SPRING10',
            'discount' => 10.00,
            'client'   => 'alice@startup.io',
            'currency' => 'USD',
        ];
        $entry['gross'] = $entry['qty'] * $entry['unit'];
        $entry['total'] = $entry['gross'] - $entry['discount'];

        // What the integration writes, row by row. Every right-hand cell is
        // a real code path, named in the comment beside it.
        $postings = [
            [
                'left'    => 'The buyer',
                'leftSub' => $entry['client'],
                // InvoiceNinja::findClient() then createClient()
                'right'   => 'A client, looked up by email address and currency. Found, and it is reused. Not found, and it is created, with the name split into a first and last name on the contact.',
            ],
            [
                'left'    => 'Each ticket type',
                'leftSub' => $entry['qty'].' x '.$entry['ticket'],
                // TicketController::invoiceninjaInvoiceCheckout() line items
                'right'   => 'A line item: the ticket name as the product, its description as the note, the quantity bought and the price each.',
            ],
            [
                'left'    => 'A promo code',
                'leftSub' => $entry['promo'],
                'right'   => 'A negative line item with the code itself as the note, so the discount reads on the invoice instead of vanishing into a total.',
            ],
            [
                'left'    => 'A volume discount or a gift card',
                'leftSub' => 'if either applies',
                'right'   => 'One more negative line each, the gift card noted with its code. The invoice adds up to what was actually charged.',
            ],
            [
                'left'    => 'The ticket itself',
                'leftSub' => 'QR code',
                // createInvoice() writes the QR into public_notes
                'right'   => 'Printed into the invoice notes, so the invoice the buyer receives is the ticket they present at the door.',
            ],
            [
                'left'    => 'The payment',
                'leftSub' => 'recorded in Invoice Ninja',
                // InvoiceNinjaController::webhook()
                'right'   => 'Reported back by webhook. That entry is what marks the sale paid in Event Schedule and sends the confirmation.',
            ],
        ];

        // The two checkout modes, per docs/tickets#invoiceninja-modes and
        // User::invoiceninja_mode ('invoice' | 'payment_link').
        $modes = [
            [
                'name'  => 'Invoice',
                'sub'   => 'The default',
                'blurb' => 'Buyers pick tickets and enter promo codes on your Event Schedule page. Each purchase becomes its own invoice in Invoice Ninja.',
                'rows'  => [
                    ['The cart', 'Event Schedule'],
                    ['Promo codes', 'Several per event, each able to target specific ticket types'],
                    ['Invoices', 'One per purchase'],
                    ['Buyer lands on', 'The invoice in your client portal'],
                ],
            ],
            [
                'name'  => 'Payment link',
                'sub'   => 'Cart in Invoice Ninja',
                'blurb' => 'Buyers pick tickets and enter a promo code on the Invoice Ninja purchase page. A product is created for each ticket type and add-on, wired to one purchase page per event.',
                'rows'  => [
                    ['The cart', 'Invoice Ninja'],
                    ['Promo codes', 'One active code per event, applied to everything'],
                    ['Invoices', 'Grouped in Invoice Ninja for bulk handling'],
                    ['Buyer lands on', 'Your Invoice Ninja purchase page'],
                ],
            ],
        ];

        // The three things that happen before a sale is called paid.
        // InvoiceNinjaController::webhook(): lockForUpdate, 0.01 tolerance,
        // 'amount_mismatch' status, then EmailService::sendSaleConfirmationEmails.
        $checks = [
            ['01', 'When it does not agree', 'More than a cent apart and the sale is parked with an amount mismatch status for you to look at, rather than quietly confirmed. The invoice id is kept against it either way, so the entry is still findable.'],
            ['02', 'The row is locked', 'The sale is locked while it changes state, and a webhook that has already been handled does nothing the second time. A retry cannot pay for the same seat twice.'],
            ['03', 'Then the ticket goes out', 'Only once the sale actually flips to paid does the confirmation email with the QR ticket send. A gift card bought this way activates on the same entry.'],
        ];

        // Every line here is a real diagnostic. The reason keys come from
        // InvoiceNinja::curlReasonKey() / responseReasonKey(), and the text
        // from messages.invoiceninja_error_* - the same words the settings
        // panel shows.
        $diagnostics = [
            ['Token rejected', 'Invoice Ninja answered 401 or 403. Mint a new token under Settings, Account Management.'],
            ['Not an Invoice Ninja install', 'A 404 from that address. It wants the base address of the install, without /api/v1.'],
            ['Something answered instead', 'A proxy, firewall or bot protection replied with a page rather than JSON. Allow API requests from this server.'],
            ['The address redirects', 'Reported, never followed, because a followed redirect can turn a create into a read that returns 200 and creates nothing.'],
            ['Unreachable', 'The connection did not complete. Check the address, DNS, and any firewall between the two servers.'],
            ['TLS rejected', 'The certificate is not one this server trusts.'],
            ['Rate limited', 'Invoice Ninja is throttling this server. Try again shortly.'],
        ];

        $notes = [
            [
                '104 currency codes',
                'Event Schedule maps 104 currency codes onto Invoice Ninja currencies, and the client lookup is scoped by currency, so the same email can hold a separate client per currency rather than being force-fitted into one.',
            ],
            [
                'Where the invoice lands',
                'Usually straight to the invoice in your client portal. Where the buyer turns out to match an existing client that is not verifiably theirs, and that portal has no password on it, the invoice is emailed instead of handing a stranger a link into somebody else\'s record.',
            ],
            [
                'Gift cards, the same way',
                'A gift card sold through Invoice Ninja is amount-checked and activated by the same payment entry, then emailed to whoever it was bought for.',
            ],
            [
                'No platform fee',
                'Event Schedule takes nothing out of a ticket sale. Whatever your Invoice Ninja gateway charges is the whole cost of getting paid.',
            ],
        ];

        $faqs = [
            [
                'q' => 'Can I use this with a selfhosted Invoice Ninja?',
                'a' => 'Yes, and it is the case the integration is built for. Enter the base address of your install and an API token, and Event Schedule talks to your server. A trailing /api/v1 is stripped for you and an install mounted on a sub-path keeps that sub-path. Leave the address blank and it uses invoicing.co instead. Event Schedule can be selfhosted too, so both halves can sit on hardware you own.',
            ],
            [
                'q' => 'Are QR code tickets generated automatically?',
                'a' => 'Yes. The QR code is printed into the invoice notes when the invoice is created, so the invoice doubles as the ticket, and when the payment is recorded in Invoice Ninja the confirmation email with the ticket goes out on its own.',
            ],
            [
                'q' => 'Does it create client records for me?',
                'a' => 'Yes. Event Schedule first looks for an existing client with that email address in the sale currency and reuses it, which is what keeps a regular buyer from turning into five clients. If there is no match it creates one, splitting the name given at checkout into a first and last name on the contact.',
            ],
            [
                'q' => 'Can I use Stripe and Invoice Ninja at the same time?',
                'a' => 'Yes. Both connect once under payment methods on your profile, and then each event chooses how it takes money: cash, Stripe, Invoice Ninja or a plain payment URL. A corporate booking that needs an invoice and a public show that wants a card payment can run side by side.',
            ],
            [
                'q' => 'What happens if a payment link cannot be created?',
                'a' => 'Checkout falls back to invoice mode and the buyer gets an invoice, rather than seeing an error. The failure is logged for you instead of being handed to the customer.',
            ],
            [
                'q' => 'My selfhosted install connected from curl but failed here. Why?',
                'a' => 'That was a real bug, reported as issue 110, and it had three causes. The API URL was being trimmed by a character list rather than a suffix, so a host ending in a, p, i, v or 1 lost its last letter. Requests carried no user agent, which Cloudflare bot protection and most managed firewall rules answer with an HTML page that looks exactly like a bad token. And redirects were invisible. All three are fixed, and a failed connection now names which of those it was instead of saying only that it failed.',
            ],
        ];

        $dotSections = [
            ['top', 'The entry'],
            ['post', 'What gets posted'],
            ['modes', 'Where the cart lives'],
            ['audit', 'Before it is paid'],
            ['connect', 'Connecting'],
            ['notes', 'Small print'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];
    @endphp

    <div id="es-ledg-page" class="es-ledg-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the entry, and the invoice that is also the ticket  -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden pb-16 pt-28">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(10, 106, 68, 0.26), rgba(10, 106, 68, 0) 62%); opacity: 0.55;"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(98, 214, 159, 0.16), rgba(98, 214, 159, 0) 62%); opacity: 0.5;"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:72px_72px] [mask-image:radial-gradient(ellipse_72%_62%_at_50%_38%,black_22%,transparent_74%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                <div>
                    <p class="es-ledg-tag es-fade-up es-d-1 mb-5 flex items-center gap-3">
                        @include('marketing.partials.integration-logo', ['name' => 'invoiceninja', 'class' => 'h-6 w-6 shrink-0'])
                        <span>Invoice Ninja integration</span>
                    </p>

                    <h1 class="es-balance mb-7 text-[2.6rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">You already keep</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="es-ledg-grad">the books</span>.</span></span>
                    </h1>

                    <p class="es-ledg-muted es-fade-up es-d-2 mb-9 max-w-xl text-lg sm:text-xl">
                        So a ticket sale should not need a second set. Sell it in Event Schedule and
                        the entry is written into your Invoice Ninja company: a client, a line per
                        ticket type, the QR code on the invoice, and a payment reconciled to the cent.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ app_url('/sign_up') }}" class="es-ledg-btn inline-flex items-center justify-center gap-2 rounded-md px-7 py-4 text-base font-semibold">
                            Get started free
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                        <a href="#post" class="es-ledg-ghost inline-flex items-center justify-center gap-2 rounded-md px-7 py-4 text-base font-semibold">
                            See what gets posted
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                    </div>
                </div>

                <!-- The invoice that is also the ticket. A sheet of ledger
                     paper: identical with .dark on and off. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-ledg-sheet p-6 sm:p-8">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="es-ledg-sheet-head">Invoice</p>
                                <p class="es-ledg-num es-ledg-sheet-ink text-2xl font-bold">#{{ $entry['invoice'] }}</p>
                            </div>
                            <a href="#post" class="es-ledg-xref">
                                <span class="es-ledg-xref-label">Posted to</span>
                                <span class="es-ledg-num es-ledg-xref-ref">Folio 01</span>
                            </a>
                        </div>

                        <div class="es-ledg-sheet-hair my-5" aria-hidden="true"></div>

                        <dl class="mb-5 space-y-1 text-sm">
                            <div class="flex justify-between gap-4">
                                <dt class="es-ledg-sheet-muted">Client</dt>
                                <dd class="es-ledg-num es-ledg-sheet-ink">{{ $entry['client'] }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="es-ledg-sheet-muted">Currency</dt>
                                <dd class="es-ledg-num es-ledg-sheet-ink">{{ $entry['currency'] }}</dd>
                            </div>
                        </dl>

                        <div class="space-y-1">
                            <div class="es-ledg-bar flex items-baseline gap-3 px-3 py-2">
                                <span class="es-ledg-sheet-ink min-w-0 flex-1 text-sm font-semibold">{{ $entry['ticket'] }}</span>
                                <span class="es-ledg-num es-ledg-sheet-muted shrink-0 text-xs">{{ $entry['qty'] }} x {{ number_format($entry['unit'], 2) }}</span>
                                <span class="es-ledg-num es-ledg-sheet-ink shrink-0 text-sm">{{ number_format($entry['gross'], 2) }}</span>
                            </div>
                            <div class="flex items-baseline gap-3 px-3 py-2">
                                <span class="es-ledg-sheet-ink min-w-0 flex-1 text-sm font-semibold">Discount</span>
                                <span class="es-ledg-num es-ledg-sheet-muted shrink-0 text-xs">{{ $entry['promo'] }}</span>
                                <span class="es-ledg-num es-ledg-sheet-accent shrink-0 text-sm">-{{ number_format($entry['discount'], 2) }}</span>
                            </div>
                        </div>

                        <div class="es-ledg-total mt-3 flex items-baseline justify-between gap-4">
                            <span class="es-ledg-sheet-head">Total</span>
                            <span class="es-ledg-num es-ledg-sheet-ink text-lg font-bold">{{ number_format($entry['total'], 2) }}</span>
                        </div>

                        <div class="es-ledg-sheet-hair my-5" aria-hidden="true"></div>

                        <div class="flex items-center gap-4">
                            <!-- QR code: a functional mark on the invoice, not an illustration. -->
                            <svg role="img" aria-label="Example QR code ticket" viewBox="0 0 25 25" class="h-20 w-20 shrink-0">
                                <rect x="0" y="0" width="25" height="25" fill="#fbfdf9"/>
                                <rect x="0" y="0" width="7" height="7" fill="#1a2318"/>
                                <rect x="1" y="1" width="5" height="5" fill="#fbfdf9"/>
                                <rect x="2" y="2" width="3" height="3" fill="#1a2318"/>
                                <rect x="18" y="0" width="7" height="7" fill="#1a2318"/>
                                <rect x="19" y="1" width="5" height="5" fill="#fbfdf9"/>
                                <rect x="20" y="2" width="3" height="3" fill="#1a2318"/>
                                <rect x="0" y="18" width="7" height="7" fill="#1a2318"/>
                                <rect x="1" y="19" width="5" height="5" fill="#fbfdf9"/>
                                <rect x="2" y="20" width="3" height="3" fill="#1a2318"/>
                                <rect x="8" y="0" width="1" height="1" fill="#1a2318"/>
                                <rect x="10" y="0" width="1" height="1" fill="#1a2318"/>
                                <rect x="12" y="0" width="1" height="1" fill="#1a2318"/>
                                <rect x="8" y="2" width="1" height="1" fill="#1a2318"/>
                                <rect x="10" y="2" width="1" height="1" fill="#1a2318"/>
                                <rect x="14" y="2" width="1" height="1" fill="#1a2318"/>
                                <rect x="9" y="4" width="1" height="1" fill="#1a2318"/>
                                <rect x="11" y="4" width="1" height="1" fill="#1a2318"/>
                                <rect x="13" y="4" width="1" height="1" fill="#1a2318"/>
                                <rect x="8" y="6" width="1" height="1" fill="#1a2318"/>
                                <rect x="12" y="6" width="1" height="1" fill="#1a2318"/>
                                <rect x="0" y="8" width="1" height="1" fill="#1a2318"/>
                                <rect x="2" y="8" width="1" height="1" fill="#1a2318"/>
                                <rect x="4" y="8" width="1" height="1" fill="#1a2318"/>
                                <rect x="6" y="8" width="1" height="1" fill="#1a2318"/>
                                <rect x="8" y="8" width="1" height="1" fill="#1a2318"/>
                                <rect x="10" y="8" width="1" height="1" fill="#1a2318"/>
                                <rect x="14" y="8" width="1" height="1" fill="#1a2318"/>
                                <rect x="18" y="8" width="1" height="1" fill="#1a2318"/>
                                <rect x="20" y="8" width="1" height="1" fill="#1a2318"/>
                                <rect x="22" y="8" width="1" height="1" fill="#1a2318"/>
                                <rect x="24" y="8" width="1" height="1" fill="#1a2318"/>
                                <rect x="9" y="9" width="1" height="1" fill="#1a2318"/>
                                <rect x="11" y="9" width="1" height="1" fill="#1a2318"/>
                                <rect x="13" y="9" width="1" height="1" fill="#1a2318"/>
                                <rect x="15" y="9" width="1" height="1" fill="#1a2318"/>
                                <rect x="19" y="9" width="1" height="1" fill="#1a2318"/>
                                <rect x="23" y="9" width="1" height="1" fill="#1a2318"/>
                                <rect x="8" y="10" width="1" height="1" fill="#1a2318"/>
                                <rect x="12" y="10" width="1" height="1" fill="#1a2318"/>
                                <rect x="16" y="10" width="1" height="1" fill="#1a2318"/>
                                <rect x="20" y="10" width="1" height="1" fill="#1a2318"/>
                                <rect x="24" y="10" width="1" height="1" fill="#1a2318"/>
                                <rect x="9" y="11" width="1" height="1" fill="#1a2318"/>
                                <rect x="11" y="11" width="1" height="1" fill="#1a2318"/>
                                <rect x="15" y="11" width="1" height="1" fill="#1a2318"/>
                                <rect x="17" y="11" width="1" height="1" fill="#1a2318"/>
                                <rect x="21" y="11" width="1" height="1" fill="#1a2318"/>
                                <rect x="8" y="12" width="1" height="1" fill="#1a2318"/>
                                <rect x="10" y="12" width="1" height="1" fill="#1a2318"/>
                                <rect x="14" y="12" width="1" height="1" fill="#1a2318"/>
                                <rect x="18" y="12" width="1" height="1" fill="#1a2318"/>
                                <rect x="22" y="12" width="1" height="1" fill="#1a2318"/>
                                <rect x="9" y="13" width="1" height="1" fill="#1a2318"/>
                                <rect x="13" y="13" width="1" height="1" fill="#1a2318"/>
                                <rect x="15" y="13" width="1" height="1" fill="#1a2318"/>
                                <rect x="19" y="13" width="1" height="1" fill="#1a2318"/>
                                <rect x="23" y="13" width="1" height="1" fill="#1a2318"/>
                                <rect x="8" y="14" width="1" height="1" fill="#1a2318"/>
                                <rect x="12" y="14" width="1" height="1" fill="#1a2318"/>
                                <rect x="16" y="14" width="1" height="1" fill="#1a2318"/>
                                <rect x="20" y="14" width="1" height="1" fill="#1a2318"/>
                                <rect x="24" y="14" width="1" height="1" fill="#1a2318"/>
                                <rect x="11" y="15" width="1" height="1" fill="#1a2318"/>
                                <rect x="13" y="15" width="1" height="1" fill="#1a2318"/>
                                <rect x="17" y="15" width="1" height="1" fill="#1a2318"/>
                                <rect x="21" y="15" width="1" height="1" fill="#1a2318"/>
                                <rect x="0" y="16" width="1" height="1" fill="#1a2318"/>
                                <rect x="2" y="16" width="1" height="1" fill="#1a2318"/>
                                <rect x="4" y="16" width="1" height="1" fill="#1a2318"/>
                                <rect x="6" y="16" width="1" height="1" fill="#1a2318"/>
                                <rect x="8" y="16" width="1" height="1" fill="#1a2318"/>
                                <rect x="10" y="16" width="1" height="1" fill="#1a2318"/>
                                <rect x="14" y="16" width="1" height="1" fill="#1a2318"/>
                                <rect x="18" y="16" width="1" height="1" fill="#1a2318"/>
                                <rect x="22" y="16" width="1" height="1" fill="#1a2318"/>
                                <rect x="18" y="18" width="1" height="1" fill="#1a2318"/>
                                <rect x="20" y="18" width="1" height="1" fill="#1a2318"/>
                                <rect x="24" y="18" width="1" height="1" fill="#1a2318"/>
                                <rect x="19" y="19" width="1" height="1" fill="#1a2318"/>
                                <rect x="21" y="19" width="1" height="1" fill="#1a2318"/>
                                <rect x="23" y="19" width="1" height="1" fill="#1a2318"/>
                                <rect x="18" y="20" width="1" height="1" fill="#1a2318"/>
                                <rect x="22" y="20" width="1" height="1" fill="#1a2318"/>
                                <rect x="19" y="21" width="1" height="1" fill="#1a2318"/>
                                <rect x="21" y="21" width="1" height="1" fill="#1a2318"/>
                                <rect x="23" y="21" width="1" height="1" fill="#1a2318"/>
                                <rect x="18" y="22" width="1" height="1" fill="#1a2318"/>
                                <rect x="20" y="22" width="1" height="1" fill="#1a2318"/>
                                <rect x="24" y="22" width="1" height="1" fill="#1a2318"/>
                                <rect x="19" y="23" width="1" height="1" fill="#1a2318"/>
                                <rect x="21" y="23" width="1" height="1" fill="#1a2318"/>
                                <rect x="18" y="24" width="1" height="1" fill="#1a2318"/>
                                <rect x="22" y="24" width="1" height="1" fill="#1a2318"/>
                                <rect x="24" y="24" width="1" height="1" fill="#1a2318"/>
                            </svg>
                            <p class="es-ledg-sheet-muted text-sm">
                                <span class="es-ledg-sheet-ink font-semibold">This invoice is the ticket.</span>
                                The code is printed in the notes when the invoice is created, and scanned at the door.
                            </p>
                        </div>
                    </div>

                    <p class="es-ledg-muted mt-5 text-xs">
                        Illustration. {{ $entry['event'] }}, two seats at {{ number_format($entry['unit'], 2) }}, one promo code.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. What gets posted (folio 01) - the record, as a table      -->
    <!-- ============================================================ -->
    <section id="post" class="es-ledg-rule-t scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <p class="es-ledg-folio mb-6" data-reveal>Folio 01</p>
                <p class="es-ledg-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">What gets posted</p>
                <h2 class="es-balance es-ledg-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    One purchase, <span class="es-ledg-grad">written on both sides</span>.
                </h2>
                <p class="es-ledg-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Nothing is summarised and nothing is exported later. At the moment of sale the
                    integration writes into your company, in the words you would have used yourself,
                    which is also why a corporate buyer ends up with paperwork they can expense.
                </p>
            </div>

            <div data-reveal="panel">
                <div class="es-ledg-sheet p-5 sm:p-8">
                    <div class="mb-5 flex flex-wrap items-baseline justify-between gap-3">
                        <p class="es-ledg-sheet-head">Account &middot; Ticket sales</p>
                        <p class="es-ledg-num es-ledg-sheet-muted text-xs">Invoice mode &middot; one purchase</p>
                    </div>

                    <div class="es-ledg-scroll">
                        <table class="es-ledg-table">
                            <caption class="sr-only">What Event Schedule writes into Invoice Ninja for a single ticket purchase</caption>
                            <thead>
                                <tr>
                                    <th scope="col">The sale</th>
                                    <th scope="col">Posted in Invoice Ninja</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($postings as $row)
                                    <tr>
                                        <th scope="row">
                                            {{ $row['left'] }}
                                            <span class="es-ledg-num es-ledg-sheet-muted mt-0.5 block text-xs font-normal">{{ $row['leftSub'] }}</span>
                                        </th>
                                        <td>{{ $row['right'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th scope="row">Balances</th>
                                    <td>
                                        Sale {{ number_format($entry['total'], 2) }} {{ $entry['currency'] }},
                                        invoice #{{ $entry['invoice'] }} {{ number_format($entry['total'], 2) }} {{ $entry['currency'] }}.
                                        The two sides were never allowed to drift.
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mx-auto mt-8 max-w-2xl space-y-2" data-reveal>
                <p>
                    <span class="es-ledg-plan es-ledg-plan-pro">Pro</span>
                    <span class="es-ledg-muted ml-2 text-sm">
                        No ceiling on ticket sales, plus the check-in dashboard, promo codes and gift
                        cards, at ${{ $proMonthly }} a month. Selfhosted installs get every Pro and Enterprise
                        feature, and no plan pays a platform fee on sales.
                    </span>
                </p>
                <p>
                    <span class="es-ledg-plan es-ledg-plan-free">Free</span>
                    <span class="es-ledg-muted ml-2 text-sm">
                        The schedule itself, its public page, calendar sync, RSVP with a capacity per
                        date, the embeddable calendar, and the first 25 paid tickets a month, each one
                        scanned at the door like any other.
                    </span>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Where the cart lives (folio 02) - the duplex split        -->
    <!-- ============================================================ -->
    <section id="modes" class="es-ledg-rule-t scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <p class="es-ledg-folio mb-6" data-reveal>Folio 02</p>
                <p class="es-ledg-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Where the cart lives</p>
                <h2 class="es-balance es-ledg-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Two places to <span class="es-ledg-grad">take the money</span>.
                </h2>
                <p class="es-ledg-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    One setting, chosen once under payment methods. It decides which side of the
                    arrangement the buyer actually stands on while they pick their tickets.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2" data-reveal-group="100">
                @foreach ($modes as $mode)
                    <div class="es-ledg-card es-ledg-hover flex flex-col p-6 sm:p-8" data-reveal>
                        <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                            <h3 class="es-ledg-ink text-xl font-bold">{{ $mode['name'] }}</h3>
                            <span class="es-ledg-tag">{{ $mode['sub'] }}</span>
                        </div>
                        <p class="es-ledg-muted mb-6 text-sm">{{ $mode['blurb'] }}</p>

                        <dl class="mt-auto space-y-1.5">
                            @foreach ($mode['rows'] as [$k, $v])
                                <div class="es-ledg-sub flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 px-3.5 py-2.5">
                                    <dt class="es-ledg-muted text-xs font-semibold uppercase tracking-wider">{{ $k }}</dt>
                                    <dd class="es-ledg-ink min-w-0 text-sm font-semibold">{{ $v }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endforeach
            </div>

            <div class="es-ledg-card mt-4 flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between" data-reveal>
                <p class="es-ledg-muted max-w-2xl text-sm">
                    <span class="es-ledg-ink font-semibold">Either way, the count still holds.</span>
                    In payment link mode the purchase page belongs to Invoice Ninja, so remaining
                    stock is checked again when the purchase is reported back, and quantities are
                    capped to what was actually left. And if a payment link cannot be created at all,
                    checkout quietly falls back to an invoice rather than failing in front of a buyer.
                </p>
                <a href="{{ route('marketing.docs.tickets') }}#invoiceninja-modes" class="es-ledg-accent inline-flex shrink-0 items-center gap-1.5 text-sm font-semibold hover:underline">
                    Compare the modes in detail
                    <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Before it is marked paid (folio 03) - dark band           -->
    <!-- ============================================================ -->
    <section id="audit" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-ledg-band noise relative overflow-hidden rounded-[2rem] px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <p class="es-ledg-folio mb-6" data-reveal>Folio 03</p>
                    <p class="es-ledg-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Before it is paid</p>
                    <h2 class="es-balance mb-5 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Nothing is marked paid <span class="es-ledg-grad">on trust</span>.
                    </h2>
                    <p class="text-lg text-gray-400" data-reveal style="--reveal-delay: 0.15s;">
                        A payment arriving from outside is an assertion, not a fact. It is agreed
                        against what the sale should have cost before a seat is considered sold.
                    </p>
                </div>

                <!-- The agreement: the same entry as the hero, footed on the dark side. -->
                <div class="es-ledg-panel mx-auto mb-10 max-w-2xl p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-baseline justify-between gap-3">
                        <p class="es-ledg-tag">The agreement</p>
                        <p class="es-ledg-num text-xs text-gray-400">Invoice #{{ $entry['invoice'] }}</p>
                    </div>
                    <dl>
                        <div class="flex items-baseline justify-between gap-4 py-1.5">
                            <dt class="text-sm text-gray-400">Reported paid in Invoice Ninja</dt>
                            <dd class="es-ledg-num es-ledg-band-ink shrink-0 text-sm font-semibold">{{ number_format($entry['total'], 2) }} {{ $entry['currency'] }}</dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-4 py-1.5">
                            <dt class="text-sm text-gray-400">What the sale should have cost</dt>
                            <dd class="es-ledg-num es-ledg-band-ink shrink-0 text-sm font-semibold">{{ number_format($entry['total'], 2) }} {{ $entry['currency'] }}</dd>
                        </div>
                        <div class="es-ledg-agree-rule mt-2 flex items-baseline justify-between gap-4 pt-3">
                            <dt class="es-ledg-band-ink text-sm font-semibold">Difference</dt>
                            <dd class="es-ledg-num es-ledg-lit shrink-0 text-lg font-bold">{{ number_format(0, 2) }}</dd>
                        </div>
                    </dl>
                    <p class="mt-4 text-xs text-gray-400">
                        Tolerance is one cent. In payment link mode the figure on the second line is
                        recomputed here from your own ticket prices before the comparison runs, so an
                        edited payload cannot talk the total down.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-3" data-reveal-group="110">
                    @foreach ($checks as [$n, $t, $d])
                        <div class="es-ledg-panel p-7" data-reveal="panel">
                            <p class="es-ledg-lit es-ledg-num mb-3 text-sm font-bold">{{ $n }}</p>
                            <h3 class="mb-2 text-lg font-bold text-white">{{ $t }}</h3>
                            <p class="text-sm text-gray-400">{{ $d }}</p>
                        </div>
                    @endforeach
                </div>

                <p class="es-ledg-band-ink mx-auto mt-8 max-w-3xl text-center text-sm" data-reveal>
                    Every one of those state changes is written to the audit log, tagged as having
                    come from Invoice Ninja, and the sale keeps the invoice's own id against it. A
                    disputed seat can be traced back to the entry that closed it.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Connecting (folio 04) - two fields, and the diagnostics   -->
    <!-- ============================================================ -->
    <section id="connect" class="es-ledg-rule-t scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-start gap-14 lg:grid-cols-2 lg:gap-16">
                <div>
                    <p class="es-ledg-folio mb-6" data-reveal>Folio 04</p>
                    <p class="es-ledg-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Connecting</p>
                    <h2 class="es-balance es-ledg-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Two fields, then it is <span class="es-ledg-grad">your server's business</span>.
                    </h2>
                    <p class="es-ledg-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        An API token from Settings, Account Management, and the base address of your
                        install. Leave the address blank and it uses invoicing.co instead.
                    </p>

                    <ul class="space-y-4" data-reveal-group="90">
                        @foreach ([
                            ['The base address, not the API path', 'A trailing /api/v1 is stripped for you, and an install mounted on a sub-path keeps its sub-path. A port is kept too.'],
                            ['A selfhosted install can be private', 'Point it at a LAN address, a Docker service name or loopback and it will go there. On the shared hosted platform the address is validated and pinned to the IP it resolved to, because there anyone can type one.'],
                            ['One webhook, cleaned up after', 'Connecting registers a single payment webhook on your company. Unlinking removes it again, and reconnecting prunes the one it replaces rather than leaving it behind.'],
                        ] as [$t, $d])
                            <li class="flex items-start gap-3" data-reveal>
                                <svg aria-hidden="true" class="es-ledg-accent mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span><span class="es-ledg-ink font-semibold">{{ $t }}</span> <span class="es-ledg-muted">- {{ $d }}</span></span>
                            </li>
                        @endforeach
                    </ul>

                    <p class="mt-7" data-reveal>
                        <a href="{{ route('marketing.docs.account_settings') }}#invoice-ninja" class="es-ledg-accent inline-flex items-center gap-1.5 text-sm font-semibold hover:underline">
                            Read the setup guide
                            <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    </p>
                </div>

                <!-- The diagnostics slip. Same words the settings panel shows. -->
                <div data-reveal="panel">
                    <div class="es-ledg-sheet p-5 sm:p-7">
                        <div class="mb-1 flex flex-wrap items-baseline justify-between gap-3">
                            <p class="es-ledg-sheet-head">If it will not connect</p>
                            <span class="es-ledg-num es-ledg-sheet-muted text-xs">7 causes</span>
                        </div>
                        <p class="es-ledg-sheet-muted mb-5 text-sm">
                            The settings page names which of these it was, in plain language, next to
                            the raw response. A connection that fails should not leave you guessing
                            between a bad token and a firewall.
                        </p>

                        <ol class="space-y-1">
                            @foreach ($diagnostics as $di => [$dTitle, $dBody])
                                <li class="flex gap-3 px-3 py-2.5 {{ $di % 2 === 1 ? 'es-ledg-bar' : '' }}">
                                    <span class="es-ledg-num es-ledg-sheet-accent shrink-0 pt-0.5 text-xs font-bold">{{ str_pad($di + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                    <span class="min-w-0">
                                        <span class="es-ledg-sheet-ink block text-sm font-semibold">{{ $dTitle }}</span>
                                        <span class="es-ledg-sheet-muted block text-xs">{{ $dBody }}</span>
                                    </span>
                                </li>
                            @endforeach
                        </ol>

                        <div class="es-ledg-total mt-4">
                            <p class="es-ledg-sheet-muted text-xs">
                                <span class="es-ledg-sheet-ink font-semibold">Issue 110.</span>
                                A selfhosted install that worked from a plain curl and failed here.
                                The URL was being trimmed by a character list rather than a suffix,
                                requests carried no user agent for bot protection to recognise, and
                                redirects were silent. All three are fixed.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Small print (folio 05)                                    -->
    <!-- ============================================================ -->
    <section id="notes" class="es-ledg-rule-t scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <p class="es-ledg-folio mb-6" data-reveal>Folio 05</p>
                <p class="es-ledg-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Small print</p>
                <h2 class="es-balance es-ledg-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    The four details <span class="es-ledg-grad">that come up</span>.
                </h2>
            </div>

            <div class="grid gap-4 md:grid-cols-2" data-reveal-group="90">
                @foreach ($notes as [$nTitle, $nBody])
                    <div class="es-ledg-card es-ledg-hover p-6" data-reveal>
                        <h3 class="es-ledg-ink mb-2 text-lg font-bold">{{ $nTitle }}</h3>
                        <p class="es-ledg-muted text-sm">{{ $nBody }}</p>
                    </div>
                @endforeach
            </div>

            <div class="es-ledg-hair" aria-hidden="true"></div>

            <!-- Invoice Ninja itself -->
            <div class="grid gap-4 md:grid-cols-2" data-reveal-group="80">
                <a href="https://invoiceninja.com" target="_blank" rel="noopener noreferrer" data-reveal class="es-ledg-card es-ledg-hover group flex flex-col p-7">
                    <div class="mb-4 flex items-center gap-3">
                        @include('marketing.partials.integration-logo', ['name' => 'invoiceninja', 'class' => 'h-9 w-9 shrink-0'])
                        <span class="es-ledg-tag">Invoice Ninja</span>
                    </div>
                    <h3 class="es-ledg-ink mb-2 text-xl font-bold">Open-source invoicing</h3>
                    <p class="es-ledg-muted mb-5 text-sm">
                        Invoicing, quotes, expenses and payment gateways, used by businesses around the
                        world, and selfhostable in the same way Event Schedule is.
                    </p>
                    <span class="es-ledg-accent mt-auto inline-flex items-center gap-2 text-sm font-semibold transition-all group-hover:gap-3">
                        Visit invoiceninja.com
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    </span>
                </a>

                <a href="{{ marketing_url('/features/integrations') }}" data-reveal class="es-ledg-card es-ledg-hover group flex flex-col p-7">
                    <div class="mb-4 flex items-center gap-3">
                        <svg aria-hidden="true" class="es-ledg-accent h-9 w-9 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H18a3 3 0 010 6h-1.5m-6 6H6a3 3 0 010-6h1.5m0-3h9" /></svg>
                        <span class="es-ledg-tag">Everything else</span>
                    </div>
                    <h3 class="es-ledg-ink mb-2 text-xl font-bold">The other integrations</h3>
                    <p class="es-ledg-muted mb-5 text-sm">
                        Stripe, Google, Outlook and CalDAV calendar sync, webhooks and the REST API.
                        Invoicing is one entry in a longer list.
                    </p>
                    <span class="es-ledg-accent mt-auto inline-flex items-center gap-2 text-sm font-semibold transition-all group-hover:gap-3">
                        View all integrations
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </span>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Related pages                                             -->
    <!-- ============================================================ -->
    <section class="es-ledg-rule-t py-16">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-ledg-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([
                    ['/features/ticketing', 'Ticketing', 'Ticket types, QR check-in, zero platform fees'],
                    ['/features/gift-cards', 'Gift Cards', 'Sold and activated through the same invoice'],
                    ['/features/integrations', 'Integrations', 'Calendars, payments, webhooks and the API'],
                    ['/open-source', 'Open Source', 'Both halves of this can run on your own server'],
                ] as [$relHref, $relName, $relBlurb])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="es-ledg-card es-ledg-hover group flex items-center justify-between gap-4 p-5">
                        <div class="min-w-0">
                            <div class="es-ledg-ink text-lg font-semibold">{{ $relName }}</div>
                            <div class="es-ledg-muted text-sm">{{ $relBlurb }}</div>
                        </div>
                        <svg aria-hidden="true" class="es-ledg-accent h-5 w-5 shrink-0 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <x-marketing.related-pages />

    <!-- ============================================================ -->
    <!-- 8. FAQ (folio 06)                                            -->
    <!-- ============================================================ -->
    <section id="faq" class="es-ledg-rule-t scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <p class="es-ledg-folio mb-6" data-reveal>Folio 06</p>
                <p class="es-ledg-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Questions</p>
                <h2 class="es-balance es-ledg-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Asked at the <span class="es-ledg-grad">month end</span>.
                </h2>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ($faqs as $faq)
                    <details name="faq" data-reveal class="es-ledg-card group/faq overflow-hidden">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-6">
                            <h3 class="es-ledg-ink text-lg font-semibold">{{ $faq['q'] }}</h3>
                            <svg aria-hidden="true" class="es-ledg-muted h-5 w-5 shrink-0 transition-transform duration-300 group-open/faq:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="es-ledg-muted faq-answer px-6 pb-6">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <x-seo.faq-schema :items="$faqs" />

    <!-- ============================================================ -->
    <!-- 9. Finale: close the books                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-ledg-band noise relative overflow-hidden rounded-[2rem] px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-25"></div>
                </div>

                <div class="relative z-10">
                    <p class="es-ledg-tag mb-6">Free to start</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                        Sell the tickets. <span class="es-ledg-grad">Keep one set of books</span>.
                    </h2>
                    <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                        Connect the Invoice Ninja company you already use, and every sale posts itself.
                        No platform fee comes out of the ticket price.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-md border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-ledg-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-md px-8 py-4 text-lg font-semibold">
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
                        <span class="es-ledg-tip pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
