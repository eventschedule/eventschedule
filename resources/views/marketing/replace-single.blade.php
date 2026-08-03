<x-marketing-layout>
    @php
        // ------------------------------------------------------------------
        // This ONE template renders all twelve /*-replacement pages, so every
        // device below has to survive a form (Google Forms), an email tool
        // (Mailchimp), a link-in-bio (Linktree), a spreadsheet, a kanban board
        // (Trello) and a QR generator. Nothing here may assume a category.
        // ------------------------------------------------------------------
        $shortName = $short_name ?? $name;

        $steps = $switch_steps ?? [
            ['title' => 'Create your schedule', 'description' => 'Sign up free and create your first schedule in under a minute.'],
            ['title' => 'Add your events', 'description' => 'Paste event details for AI import or create events manually.'],
            ['title' => 'Share and sell', 'description' => 'Share your schedule URL and start selling tickets.'],
        ];

        // The visible FAQ and the FAQPage schema are rendered from one array, so
        // the two can no longer drift apart.
        $faqs = array_map(fn ($item) => ['q' => $item['question'], 'a' => $item['answer']], $faq);

        // The swap: the tools this job usually gets spread across, then the one
        // that replaces them. Both halves come from real page data - this tool
        // plus the three we also publish a replacement page for - so the strip
        // is honest on every slug and never invents a product or a price.
        $outTabs = array_merge([$shortName], array_map(fn ($l) => $l['name'], $cross_links));
        $tabCount = count($outTabs);
        $numberWords = [1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five'];
        $tabCountWord = $numberWords[$tabCount] ?? $tabCount;
        $otherTabsWord = $numberWords[$tabCount - 1] ?? ($tabCount - 1);
        // Built here rather than with an inline @if: Blade does not compile a
        // directive that follows a word character (`schedule@if` renders the
        // directive as literal text), and this sentence needs no space there.
        $closeTabsLine = $tabCount > 1
            ? 'Create your free schedule and close the other ' . $otherTabsWord . ' tabs.'
            : 'Create your free schedule.';

        // What the one tab does natively, with the tier that carries it. Each
        // line is checked against docs/FEATURES.md: RSVP with a capacity limit
        // is free, ticketing and the check-in dashboard are Pro.
        $inRows = [
            ['A public page for every event, and a calendar of all of them', 'Free'],
            ['Free RSVP with a capacity limit per date', 'Free'],
            ['Tickets with QR check-in and a check-in dashboard', 'Pro'],
            ['Newsletters to the people who follow your schedule', 'Free'],
        ];

        // The sheet never quotes somebody else's price. A third-party list
        // price cannot be verified from inside this repo and goes stale the
        // week they change it, which is why the competitor-price panel was
        // dropped from this page; a currency figure in a table cell is the
        // same unverifiable claim in smaller type. How their pricing is
        // SHAPED (charged per seat) is durable, so that is what the cell
        // says, and the Event Schedule column keeps the real contrast.
        $sheetRows = array_map(function ($row) {
            if (is_string($row['competitor']) && preg_match('/\$\s*\d/', $row['competitor'])) {
                $row['competitor'] = 'Per seat';
            }

            return $row;
        }, $comparison_rows ?? []);

        $dotSections = [
            ['top', 'The swap'],
            ['gaps', 'The gaps'],
            ['native', 'Done natively'],
            ['sheet', 'The swap sheet'],
            ['switch', 'Switching'],
            ['about', 'Both tools'],
            ['faq', 'Questions'],
            ['also', 'Also replace'],
            ['claim', 'One tab'],
        ];
    @endphp

    <x-slot name="title">Replace {{ $shortName }} for Events | Event Schedule</x-slot>
    <x-slot name="description">{{ $description }}</x-slot>
    <x-slot name="keywords">{{ $keywords }}</x-slot>
    <x-slot name="breadcrumbTitle">{{ $shortName }} Replacement</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule",
        "description": "Open-source event management platform for sharing events, selling tickets, and bringing communities together. Zero platform fees.",
        "url": "{{ config('app.url') }}",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": ["Web", "Android", "iOS"],
        "isSimilarTo": {
            "@type": "SoftwareApplication",
            "name": "{{ str_replace('"', '\\"', $name) }}",
            "applicationCategory": "BusinessApplication"
        },
        "offers": [
            {
                "@type": "Offer",
                "name": "Free",
                "price": "0",
                "priceCurrency": "USD",
                "description": "Unlimited events and schedules, public event pages, Google, Outlook and CalDAV calendar sync, free RSVP with capacity limits, embeddable calendar, built-in analytics, AI event parsing, and 10 newsletter emails a month. One team member.",
                "availability": "https://schema.org/InStock"
            },
            {
                "@type": "Offer",
                "name": "Pro",
                "price": "{{ number_format($proMonthly, 2) }}",
                "priceCurrency": "USD",
                "description": "Everything in Free plus ticketing with QR check-in and a check-in dashboard, ticket waitlist, promo codes, custom fields, sale notifications, sales CSV export, Stripe payments, no Event Schedule branding, custom CSS, event graphics, the embeddable ticket widget, REST API and webhooks, and 100 newsletter emails a month.",
                "availability": "https://schema.org/InStock"
            },
            {
                "@type": "Offer",
                "name": "Enterprise",
                "price": "{{ number_format($entMonthly, 2) }}",
                "priceCurrency": "USD",
                "description": "Everything in Pro plus your own custom domain, up to five team members, internal and unlisted events, AI flyer and style generation, AI agenda scanning, WhatsApp event creation, 1,000 newsletter emails a month, and priority support.",
                "availability": "https://schema.org/InStock"
            }
        ],
        "featureList": [
            "Zero platform fees on ticket sales",
            "AI event parsing from pasted text or images",
            "AI flyer generation",
            "AI style generation",
            "Two-way Google Calendar sync",
            "Outlook and CalDAV sync",
            "iCal download",
            "Newsletter builder with A/B testing",
            "QR code ticketing and check-in",
            "Check-in dashboard",
            "Ticket waitlist",
            "Promo and discount codes",
            "Sale notification emails",
            "Sales CSV export",
            "Open source with selfhosting option",
            "Embeddable calendar and ticket widgets",
            "WhatsApp event creation",
            "Custom CSS styling",
            "Fan videos, photos and comments",
            "Free RSVP with capacity limits"
        ]
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to switch from {{ str_replace('"', '\\"', $name) }} to Event Schedule",
        "step": [
            @foreach ($steps as $index => $step)
            {
                "@type": "HowToStep",
                "position": {{ $index + 1 }},
                "name": "{{ str_replace('"', '\\"', $step['title']) }}",
                "text": "{{ str_replace('"', '\\"', $step['description']) }}"
            }@if (!$loop->last),@endif

            @endforeach
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
           Replace-single "The Swap" styles. One template, twelve tools:
           a form, an email tool, a link-in-bio, a spreadsheet, a kanban
           board, a QR generator. So the concept cannot be a picture of
           any one of them.

           THE CONCEPT IS THE TAB. The thing every one of those tools has
           in common is that it is a tab you keep open next to the other
           tabs, and the swap is four tabs becoming one. The tab shape is
           drawn once and reused as: the hero's before/after strip, the
           section marks, the two column heads of the swap-sheet table,
           and the finale. The argument and the ornament are the same
           object, and it says the honest thing: none of these tools is
           bad, they are just general-purpose, so the event job gets
           assembled by hand across several of them.

           MATERIAL: the "before" half is deliberately drawn as generic
           application chrome - flat gray strip, monospaced labels, no
           accent - and the "after" half is the product's own lit
           surface. That contrast is the whole page, so no other texture
           is needed. Explicitly NOT paper: /for-curators (newsprint) and
           /for-theater-performers (archival sepia) own paper, and this
           page is about software anyway.

           COLOUR: blue, the hue this page already had, kept as a SINGLE
           stop. The brand blue-to-sky-to-cyan three-stop gradient is
           shared page chrome and is deliberately not used as this page's
           accent; distinctiveness comes from the tab structure, the
           monospace/chrome material and the duplex seam instead.

           NEVER text-gray-500 here: 4.83 on pure white but only ~4.4 on
           this page's #f5f6f8 ground. Use .es-swap-muted (7.4 on white,
           7.43 on the ground, 6.74 on the chrome surface).

           EVERY design-critical colour is a real rule in this block, not
           a Tailwind arbitrary value, because this page ships without a
           rebuild and an ungenerated arbitrary class paints nothing.
           ============================================================== */

        /* --- Ground and ink ------------------------------------------- */
        .es-swap-page { background-color: #f5f6f8; color: #14181e; }
        .dark .es-swap-page { background-color: #0b0e13; color: #e6e9ee; }
        .es-swap-ink { color: #14181e; }
        .dark .es-swap-ink { color: #e6e9ee; }
        .es-swap-muted { color: #4b5158; }
        .dark .es-swap-muted { color: #9aa4b2; }
        .es-swap-accent { color: #1d4ed8; }
        .dark .es-swap-accent { color: #a6c8ff; }
        /* Always-lit accent, for the bands that stay dark in both modes. */
        .es-swap-lit { color: #a6c8ff; }

        .es-swap-mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
        }

        /* --- Surfaces -------------------------------------------------- */
        .es-swap-card {
            border: 1px solid rgba(20, 24, 30, 0.12);
            border-radius: 1rem;
            background: #ffffff;
        }
        .dark .es-swap-card {
            border-color: rgba(230, 233, 238, 0.12);
            background: rgba(230, 233, 238, 0.04);
        }

        /* Generic application chrome: the "before" material. */
        .es-swap-chrome {
            border: 1px solid rgba(20, 24, 30, 0.12);
            border-radius: 0.9rem;
            background: #e9ebef;
        }
        .dark .es-swap-chrome {
            border-color: rgba(230, 233, 238, 0.1);
            background: rgba(230, 233, 238, 0.055);
        }

        /* --- The tab ---------------------------------------------------- */
        .es-swap-tabs {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 0.3rem;
            border-bottom: 1px solid rgba(20, 24, 30, 0.16);
        }
        .dark .es-swap-tabs { border-bottom-color: rgba(230, 233, 238, 0.16); }
        .es-swap-tab {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            max-width: 100%;
            padding: 0.42rem 0.7rem;
            border: 1px solid rgba(20, 24, 30, 0.12);
            border-bottom: 0;
            border-radius: 0.55rem 0.55rem 0 0;
            background: #e2e5ea;
            color: #3d434b;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .dark .es-swap-tab {
            border-color: rgba(230, 233, 238, 0.12);
            background: rgba(230, 233, 238, 0.07);
            color: #9aa4b2;
        }
        /* The one tab that is left. */
        .es-swap-tab-in {
            border-color: #1d4ed8;
            background: #1d4ed8;
            color: #ffffff;
        }
        .dark .es-swap-tab-in {
            border-color: #a6c8ff;
            background: #a6c8ff;
            color: #0b1017;
        }

        /* The finale's one open tab. The tab title sits on a window whose
           address bar is the claim field, so the page ends holding the same
           object it opened with instead of a second copy of the glyph.
           Fixed rgba values only: this lives inside the always-dark band. */
        .es-swap-tabs-lead { border-bottom: 0; }
        .es-swap-window {
            border: 1px solid rgba(166, 200, 255, 0.28);
            border-radius: 1.25rem;
            border-start-start-radius: 0;
            background: rgba(230, 233, 238, 0.04);
        }

        /* --- The swap glyph -------------------------------------------- */
        .es-swap-glyph {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.4rem;
            height: 2.4rem;
            flex: none;
            border-radius: 9999px;
            border: 1px solid rgba(29, 78, 216, 0.35);
            background: rgba(29, 78, 216, 0.08);
            color: #1d4ed8;
        }
        .dark .es-swap-glyph {
            border-color: rgba(166, 200, 255, 0.35);
            background: rgba(166, 200, 255, 0.1);
            color: #a6c8ff;
        }
        .es-swap-glyph::after {
            content: "";
            position: absolute;
            inset: -4px;
            border-radius: 9999px;
            border: 1px solid currentColor;
            opacity: 0.3;
            animation: es-swap-ring 3.6s ease-in-out infinite;
        }
        @keyframes es-swap-ring {
            0%, 100% { opacity: 0.22; transform: scale(1); }
            50%      { opacity: 0.7;  transform: scale(1.09); }
        }

        /* --- The duplex seam: workaround on one side, native on the
               other. Logical properties so RTL flips with the page. --- */
        .es-swap-seam {
            position: relative;
            border-top: 1px solid rgba(20, 24, 30, 0.14);
            padding-top: 2rem;
        }
        .dark .es-swap-seam { border-top-color: rgba(230, 233, 238, 0.14); }
        @media (min-width: 1024px) {
            .es-swap-seam {
                border-top: 0;
                padding-top: 0;
                border-inline-start: 1px solid rgba(20, 24, 30, 0.14);
                padding-inline-start: 2.75rem;
            }
            .dark .es-swap-seam { border-inline-start-color: rgba(230, 233, 238, 0.14); }
            /* The notch: a short accent segment sitting on the seam. */
            .es-swap-seam::before {
                content: "";
                position: absolute;
                inset-block-start: 0;
                inset-inline-start: -1px;
                width: 2px;
                height: 3.5rem;
                border-radius: 2px;
                background: #1d4ed8;
            }
            .dark .es-swap-seam::before { background: #a6c8ff; }
        }

        /* --- The ledger of gaps ---------------------------------------- */
        .es-swap-row {
            display: flex;
            align-items: flex-start;
            gap: 0.85rem;
            padding: 0.9rem 0;
            border-top: 1px solid rgba(20, 24, 30, 0.1);
        }
        .dark .es-swap-row { border-top-color: rgba(230, 233, 238, 0.1); }
        .es-swap-row:first-child { border-top: 0; }
        .es-swap-rownum {
            flex: none;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            padding-top: 0.15rem;
            color: #4b5158;
        }
        .dark .es-swap-rownum { color: #9aa4b2; }

        /* --- Section mark: the tab shape again, numbered --------------- */
        .es-swap-mark {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 0.85rem 0.45rem;
            border: 1px solid rgba(20, 24, 30, 0.14);
            border-bottom: 2px solid #1d4ed8;
            border-radius: 0.5rem 0.5rem 0 0;
            background: #ffffff;
            color: #14181e;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }
        .dark .es-swap-mark {
            border-color: rgba(230, 233, 238, 0.14);
            border-bottom-color: #a6c8ff;
            background: rgba(230, 233, 238, 0.05);
            color: #e6e9ee;
        }

        /* --- Eyebrow --------------------------------------------------- */
        .es-swap-tag {
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            color: #4b5158;
        }
        .dark .es-swap-tag { color: #9aa4b2; }

        /* --- Plan pills ------------------------------------------------ */
        .es-swap-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border: 1px solid rgba(29, 78, 216, 0.4);
            border-radius: 0.25rem;
            color: #1d4ed8;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }
        .dark .es-swap-plan { border-color: rgba(166, 200, 255, 0.42); color: #a6c8ff; }
        .es-swap-plan-pro { border-color: rgba(20, 24, 30, 0.32); color: #14181e; }
        .dark .es-swap-plan-pro { border-color: rgba(230, 233, 238, 0.34); color: #e6e9ee; }

        /* --- The swap sheet (a real table) ----------------------------- */
        .es-swap-table { width: 100%; border-collapse: collapse; text-align: start; }
        .es-swap-table th,
        .es-swap-table td { padding: 0.8rem 0.9rem; vertical-align: middle; }
        .es-swap-table tbody tr { border-top: 1px solid rgba(230, 233, 238, 0.1); }
        .es-swap-feat { color: #d5dae2; font-size: 0.9rem; font-weight: 600; }
        .es-swap-out-cell {
            color: #a3adba;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.78rem;
            text-align: center;
        }
        .es-swap-in-cell {
            border-inline-start: 1px solid rgba(166, 200, 255, 0.28);
            color: #a6c8ff;
            font-size: 0.82rem;
            font-weight: 700;
            text-align: center;
        }
        /* On a phone all three columns have to fit, or the Event Schedule
           column - the whole point of the sheet - starts off-screen and the
           reader has to scroll right to find the payoff. */
        @media (max-width: 640px) {
            .es-swap-table th,
            .es-swap-table td { padding: 0.6rem 0.35rem; }
            .es-swap-feat { font-size: 0.78rem; }
            .es-swap-out-cell { font-size: 0.68rem; }
            .es-swap-in-cell { font-size: 0.72rem; }
            .es-swap-table .es-swap-tab { padding: 0.3rem 0.4rem; font-size: 0.6rem; }
        }

        /* "not built in", drawn rather than written, so a row never turns
           into a sentence about somebody else's product. */
        .es-swap-no {
            display: inline-block;
            width: 0.85rem;
            height: 2px;
            border-radius: 2px;
            background: #a3adba;
            vertical-align: middle;
        }

        /* --- Fixed-dark band: the same object in both colour modes ----- */
        .es-swap-band {
            background-color: #0b1017;
            background-image: radial-gradient(120% 100% at 50% 0%, #16203a 0%, #0f1524 55%, #080b12 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(230, 233, 238, 0.05);
        }
        .es-swap-band-ink { color: #f2f4f8; }
        .es-swap-band-muted { color: #a3adba; }
        /* Shared classes that otherwise flip with the colour mode. */
        .es-swap-band .grid-overlay {
            background-image:
                linear-gradient(rgba(230, 233, 238, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(230, 233, 238, 0.05) 1px, transparent 1px);
        }
        .es-swap-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-swap-band .es-claim:focus-within {
            border-color: rgba(166, 200, 255, 0.75);
            box-shadow: 0 0 0 4px rgba(166, 200, 255, 0.22);
        }
        .es-swap-band .es-swap-card {
            border-color: rgba(230, 233, 238, 0.14);
            background: rgba(230, 233, 238, 0.05);
        }
        .es-swap-band .es-swap-mark {
            border-color: rgba(230, 233, 238, 0.16);
            border-bottom-color: #a6c8ff;
            background: rgba(230, 233, 238, 0.06);
            color: #f2f4f8;
        }
        .es-swap-band .es-swap-tag { color: #a6c8ff; }
        .es-swap-band .es-swap-tab {
            border-color: rgba(230, 233, 238, 0.14);
            background: rgba(230, 233, 238, 0.08);
            color: #a3adba;
        }
        .es-swap-band .es-swap-tab-in {
            border-color: #a6c8ff;
            background: #a6c8ff;
            color: #0b1017;
        }
        .es-swap-band .es-swap-plan { border-color: rgba(166, 200, 255, 0.42); color: #a6c8ff; }
        .es-swap-band .es-swap-plan-pro { border-color: rgba(230, 233, 238, 0.34); color: #f2f4f8; }
        .es-swap-band .es-swap-glyph {
            border-color: rgba(166, 200, 255, 0.35);
            background: rgba(166, 200, 255, 0.12);
            color: #a6c8ff;
        }

        /* --- Icon plate ------------------------------------------------- */
        .es-swap-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.75rem;
            border: 1px solid rgba(29, 78, 216, 0.22);
            background: rgba(29, 78, 216, 0.08);
            color: #1d4ed8;
        }
        .dark .es-swap-icon {
            border-color: rgba(166, 200, 255, 0.24);
            background: rgba(166, 200, 255, 0.1);
            color: #a6c8ff;
        }

        /* --- Links, buttons, hovers ------------------------------------ */
        .es-swap-link { color: #1d4ed8; }
        .es-swap-link:hover { color: #14181e; }
        .dark .es-swap-link { color: #a6c8ff; }
        .dark .es-swap-link:hover { color: #e6e9ee; }

        .es-swap-btn {
            background-color: #1d4ed8;
            color: #ffffff;
            box-shadow: 0 18px 36px -16px rgba(29, 78, 216, 0.55);
        }
        .es-swap-btn:hover { background-color: #1740b4; }
        .dark .es-swap-btn { background-color: #a6c8ff; color: #0b1017; }
        .dark .es-swap-btn:hover { background-color: #c2d9ff; }
        .es-swap-band .es-swap-btn { background-color: #a6c8ff; color: #0b1017; }
        .es-swap-band .es-swap-btn:hover { background-color: #c2d9ff; }

        .es-swap-ghost {
            border: 1px solid rgba(20, 24, 30, 0.18);
            background: #ffffff;
            color: #14181e;
        }
        .es-swap-ghost:hover { border-color: rgba(29, 78, 216, 0.5); }
        .dark .es-swap-ghost {
            border-color: rgba(230, 233, 238, 0.18);
            background: rgba(230, 233, 238, 0.05);
            color: #e6e9ee;
        }
        .dark .es-swap-ghost:hover { border-color: rgba(166, 200, 255, 0.5); }

        .es-swap-hover:hover { border-color: rgba(29, 78, 216, 0.45); }
        .dark .es-swap-hover:hover { border-color: rgba(166, 200, 255, 0.45); }
        .es-swap-hover:hover .es-swap-hover-title,
        .es-swap-hover:hover .es-swap-hover-arrow { color: #1d4ed8; }
        .dark .es-swap-hover:hover .es-swap-hover-title,
        .dark .es-swap-hover:hover .es-swap-hover-arrow { color: #a6c8ff; }

        /* --- Dot-nav tooltip. Its own rule rather than a dark: arbitrary
               value, which would not exist in the built CSS. ---------- */
        .es-swap-tip {
            border: 1px solid rgba(20, 24, 30, 0.14);
            background: #ffffff;
            color: #14181e;
        }
        .dark .es-swap-tip {
            border-color: rgba(230, 233, 238, 0.14);
            background: #14181e;
            color: #e6e9ee;
        }

        /* --- Hairline divider ------------------------------------------ */
        .es-swap-hr { border-top: 1px solid rgba(20, 24, 30, 0.1); }
        .dark .es-swap-hr { border-top-color: rgba(230, 233, 238, 0.1); }

        /* --- Shared-system recolours (brand blue by default) ----------- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(29, 78, 216, 0.12), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(166, 200, 255, 0.1), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(29, 78, 216, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(166, 200, 255, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #1d4ed8; }
        .dark .es-dot.is-active .es-dot-pip { background: #a6c8ff; }

        /* --- Focus rings. No border-radius here: it would reshape the
               element on focus, and an outline already follows it. ----- */
        #es-swap-page a:focus-visible,
        #es-swap-page summary:focus-visible,
        #es-swap-page button:focus-visible {
            outline: 2px solid #1d4ed8;
            outline-offset: 3px;
        }
        .dark #es-swap-page a:focus-visible,
        .dark #es-swap-page summary:focus-visible,
        .dark #es-swap-page button:focus-visible { outline-color: #a6c8ff; }
        .es-swap-band a:focus-visible,
        .es-swap-band summary:focus-visible,
        .es-swap-band button:focus-visible { outline-color: #a6c8ff !important; }

        @media (prefers-reduced-motion: reduce) {
            .es-swap-glyph::after { animation: none !important; }
            .animate-pulse-slow { animation: none !important; }
        }
    </style>

    <div id="es-swap-page" class="es-swap-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: four tabs, then one                                 -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(29, 78, 216, 0.22), rgba(29, 78, 216, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(166, 200, 255, 0.14), rgba(166, 200, 255, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div>
                    <div class="es-fade-up es-d-1 mb-6">
                        <a href="{{ route('marketing.replace') }}" class="es-swap-link inline-flex items-center gap-1 text-sm font-medium hover:underline">
                            <span aria-hidden="true" class="rtl:rotate-180">&larr;</span>
                            All tools
                        </a>
                    </div>

                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="es-swap-accent h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 9h13m0 0l-3.5-3.5M17 9l-3.5 3.5" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 15H7m0 0l3.5-3.5M7 15l3.5 3.5" />
                        </svg>
                        <span class="es-swap-muted text-sm font-medium tracking-wide">{{ $shortName }} to Event Schedule</span>
                    </div>

                    <h1 class="es-balance es-swap-ink mb-7 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">Replace <span class="es-swap-accent">{{ $name }}</span></span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">for events.</span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-swap-muted mb-4 max-w-xl text-lg sm:text-xl">{{ $tagline }}</p>

                    @if (!empty($audience_hint))
                        <p class="es-fade-up es-d-2 es-swap-muted mb-8 max-w-xl text-base">{{ $audience_hint }}</p>
                    @endif

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="#sheet" class="es-swap-ghost group inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-2xl px-6 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            See the sheet
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                        <a href="{{ app_url('/sign_up') }}" class="es-swap-btn group inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Create your free schedule
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>

                    <div class="es-fade-up es-d-4 mt-8">
                        @include('marketing.partials.github-star-badge')
                    </div>
                </div>

                <!-- The swap itself: the tabs this job is spread across, then the one that is left. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-swap-card p-6 sm:p-7">
                        <p class="es-swap-tag mb-3">Today, in {{ $tabCountWord }} tabs</p>
                        <div class="es-swap-tabs mb-3">
                            @foreach ($outTabs as $tabName)
                                <span class="es-swap-tab">{{ $tabName }}</span>
                            @endforeach
                        </div>
                        <p class="es-swap-muted mb-6 text-sm">
                            Not everyone runs all {{ $tabCountWord }}. Whichever of them you do run, the event details get copied between them by hand, because none of them was built for events.
                        </p>

                        <div class="es-swap-hr flex items-center gap-3 pt-5">
                            <span class="es-swap-glyph">
                                <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 9h13m0 0l-3.5-3.5M17 9l-3.5 3.5" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 15H7m0 0l3.5-3.5M7 15l3.5 3.5" />
                                </svg>
                            </span>
                            <span class="es-swap-mono es-swap-muted text-xs font-bold uppercase tracking-[0.2em]">The swap</span>
                        </div>

                        <p class="es-swap-tag mb-3 mt-5">After</p>
                        <div class="es-swap-tabs mb-4">
                            <span class="es-swap-tab es-swap-tab-in">your-name.eventschedule.com</span>
                        </div>
                        <ul class="space-y-3">
                            @foreach ($inRows as [$inLabel, $inPlan])
                                <li class="flex items-start gap-3">
                                    <svg aria-hidden="true" class="es-swap-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    <span class="es-swap-muted flex-1 text-sm">{{ $inLabel }}</span>
                                    <span class="es-swap-plan @if ($inPlan === 'Pro') es-swap-plan-pro @endif">{{ $inPlan }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The gaps: the ledger, in the "before" material            -->
    <!-- ============================================================ -->
    <section id="gaps" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-swap-mark mb-6" data-reveal aria-hidden="true"><span>Swap 01</span></div>
                <p class="es-swap-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The gaps</p>
                <h2 class="es-balance es-swap-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Where {{ $name }} <span class="es-swap-accent">limits you</span> for events
                </h2>
                <p class="es-swap-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    {{ $name }} was not built for event management. These are the places the event part has to be done somewhere else.
                </p>
            </div>

            <div class="es-swap-chrome mx-auto max-w-3xl p-6 sm:p-8" data-reveal="panel">
                <div class="es-swap-tabs mb-5">
                    <span class="es-swap-tab">{{ $shortName }}</span>
                </div>
                <ol class="mb-0">
                    @foreach ($pain_points as $painIndex => $pain)
                        <li class="es-swap-row">
                            <span class="es-swap-rownum" aria-hidden="true">{{ str_pad($painIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-swap-ink text-sm sm:text-base">{{ $pain }}</span>
                        </li>
                    @endforeach
                </ol>
            </div>

            <p class="es-swap-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                None of that is a fault in {{ $name }}. It is a general-purpose tool being asked to run an event, which is why the rest of the job ends up in other tabs.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Done natively                                             -->
    <!-- ============================================================ -->
    <section id="native" class="es-swap-hr scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-swap-mark mb-6" data-reveal aria-hidden="true"><span>Swap 02</span></div>
                <p class="es-swap-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Done natively</p>
                <h2 class="es-balance es-swap-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    What Event Schedule gives you over <span class="es-swap-accent">{{ $name }}</span>
                </h2>
                <p class="es-swap-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    The same work, in the tool that already knows what an event is.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="90">
                @foreach ($es_solutions as $solution)
                    <div class="es-bento group relative" data-reveal="panel" data-tilt="4">
                        <div class="es-tilt-inner es-swap-card relative flex h-full flex-col overflow-hidden p-7">
                            <div class="relative z-10 flex h-full flex-col">
                                <span class="es-swap-icon mb-5">
                                    <x-marketing-icon :icon="$solution['icon']" class="h-6 w-6" />
                                </span>
                                <h3 class="es-swap-ink mb-3 text-xl font-bold">{{ $solution['title'] }}</h3>
                                <p class="es-swap-muted text-sm leading-relaxed">{{ $solution['description'] }}</p>
                            </div>
                            <div class="es-glare" aria-hidden="true"></div>
                            <div class="es-ring-glow" aria-hidden="true"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. The swap sheet (fixed-dark band, real table)              -->
    <!-- ============================================================ -->
    <section id="sheet" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-swap-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-swap-mark mb-6" data-reveal aria-hidden="true"><span>Swap 03</span></div>
                    <p class="es-swap-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The swap sheet</p>
                    <h2 class="es-balance es-swap-band-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        {{ $name }} <span class="es-swap-lit">vs Event Schedule</span>
                    </h2>
                    <p class="es-swap-band-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Line by line, on the event work. A dash in the {{ $shortName }} column means that job is not built in, so today it is living in another tab. A tick in the Event Schedule column does not mean free: the three cards below say which plan carries what.
                    </p>
                    <p class="es-swap-mono es-swap-lit mt-4 text-sm font-bold" data-reveal style="--reveal-delay: 0.2s;">{{ $es_price }}</p>
                </div>

                @if (!empty($sheetRows))
                <div class="es-swap-card overflow-x-auto p-2 sm:p-4" data-reveal="panel">
                    <table class="es-swap-table">
                        <caption class="sr-only">{{ $name }} compared with Event Schedule on event management features</caption>
                        <thead>
                            <tr>
                                <th scope="col" class="es-swap-tag" style="text-align: start;">Feature</th>
                                <th scope="col"><span class="es-swap-tab">{{ $shortName }}</span></th>
                                <th scope="col" class="es-swap-in-cell"><span class="es-swap-tab es-swap-tab-in">Event Schedule</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sheetRows as $row)
                                <tr>
                                    <th scope="row" class="es-swap-feat" style="text-align: start;">{{ $row['feature'] }}</th>
                                    <td class="es-swap-out-cell">
                                        @if ($row['competitor'] === true)
                                            <svg aria-hidden="true" class="mx-auto h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                            <span class="sr-only">Yes</span>
                                        @elseif ($row['competitor'] === false)
                                            <span class="es-swap-no" aria-hidden="true"></span>
                                            <span class="sr-only">Not built in</span>
                                        @else
                                            {{ $row['competitor'] }}
                                        @endif
                                    </td>
                                    <td class="es-swap-in-cell">
                                        @if ($row['es'] === true)
                                            <svg aria-hidden="true" class="mx-auto h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                            <span class="sr-only">Yes</span>
                                        @elseif ($row['es'] === false)
                                            <span class="es-swap-no" aria-hidden="true"></span>
                                            <span class="sr-only">Not built in</span>
                                        @else
                                            {{ $row['es'] }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                <!-- Plan honesty. The numbers that decide whether the swap is
                     actually cheaper, stated rather than implied. -->
                <div class="mt-10 grid grid-cols-1 gap-4 md:grid-cols-3" data-reveal-group="90">
                    <div class="es-swap-card p-6" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="es-swap-band-ink text-lg font-bold">Free</h3>
                            <span class="es-swap-plan">$0</span>
                        </div>
                        <p class="es-swap-band-muted text-sm leading-relaxed">
                            Unlimited events and schedules. Public event pages and a shareable calendar. Two-way Google, Outlook and CalDAV sync. Free RSVP with a capacity limit. Embeddable calendar, built-in analytics, AI event parsing, and 10 newsletter emails a month.
                        </p>
                    </div>
                    <div class="es-swap-card p-6" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="es-swap-band-ink text-lg font-bold">Pro</h3>
                            <span class="es-swap-plan es-swap-plan-pro">${{ $proMonthly }} a month</span>
                        </div>
                        <p class="es-swap-band-muted text-sm leading-relaxed">
                            Ticketing with QR check-in and the check-in dashboard. Custom fields on the form, waitlist, promo codes, sales export. Event graphics, the embeddable ticket widget, the REST API and webhooks. 100 newsletter emails a month.
                        </p>
                    </div>
                    <div class="es-swap-card p-6" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="es-swap-band-ink text-lg font-bold">Enterprise</h3>
                            <span class="es-swap-plan es-swap-plan-pro">${{ $entMonthly }} a month</span>
                        </div>
                        <p class="es-swap-band-muted text-sm leading-relaxed">
                            Your own domain. Up to five team members. Internal and unlisted events. AI flyer and style generation, AI agenda scanning, WhatsApp event creation, and 1,000 newsletter emails a month.
                        </p>
                    </div>
                </div>

                <p class="es-swap-band-muted mx-auto mt-8 max-w-3xl text-center text-sm" data-reveal>
                    Two footnotes worth reading before you switch. Your follower list is never capped, but the monthly newsletter allowance counts recipients rather than sends, so one letter to 40 followers spends 40 of it. And Free is a single team member; extra members are Enterprise. What does not change with the plan is the fee on ticket sales, which is zero on all three. Past Stripe's own processing, the money is yours.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Switching                                                 -->
    <!-- ============================================================ -->
    <section id="switch" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-swap-mark mb-6" data-reveal aria-hidden="true"><span>Swap 04</span></div>
                <p class="es-swap-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Switching</p>
                <h2 class="es-balance es-swap-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    How to switch from {{ $name }} in <span class="es-swap-accent">{{ count($steps) }} steps</span>
                </h2>
                <p class="es-swap-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Nothing to migrate. Your next event is the first one you put here.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3" data-reveal-group="110">
                @foreach ($steps as $stepIndex => $step)
                    <div class="es-swap-card flex flex-col p-7" data-reveal="panel">
                        <div class="es-swap-tabs mb-5">
                            <span class="es-swap-tab @if ($stepIndex === count($steps) - 1) es-swap-tab-in @endif">Step {{ str_pad($stepIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <h3 class="es-swap-ink mb-2 text-lg font-bold">{{ $step['title'] }}</h3>
                        <p class="es-swap-muted text-sm leading-relaxed">{{ $step['description'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-12 text-center" data-reveal>
                <a href="{{ app_url('/sign_up') }}" class="es-swap-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                    Create your free schedule
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <p class="es-swap-muted mt-4 text-sm">No credit card required.</p>
            </div>
        </div>
    </section>

    @include('marketing.partials.pricing-nudge')

    <!-- ============================================================ -->
    <!-- 6. Both tools, side by side across the seam                  -->
    <!-- ============================================================ -->
    <section id="about" class="es-swap-hr scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-swap-mark mb-6" data-reveal aria-hidden="true"><span>Swap 05</span></div>
                <p class="es-swap-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Both tools</p>
                <h2 class="es-balance es-swap-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    Keep what {{ $shortName }} is good at. <span class="es-swap-accent">Move the events.</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-10 lg:grid-cols-2">
                <div data-reveal>
                    <div class="es-swap-tabs mb-6">
                        <span class="es-swap-tab">{{ $shortName }}</span>
                    </div>
                    <h3 class="es-swap-ink mb-4 text-2xl font-bold">About {{ $name }}</h3>
                    <p class="es-swap-muted mb-6 leading-relaxed">{{ $about }}</p>
                    <p class="es-swap-tag mb-4">What it is good at</p>
                    <ul class="space-y-3" data-reveal-group="70">
                        @foreach ($tool_strengths as $strength)
                            <li class="flex items-start gap-3" data-reveal>
                                <svg aria-hidden="true" class="es-swap-muted mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-swap-muted">{{ $strength }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="es-swap-seam" data-reveal style="--reveal-delay: 0.1s;">
                    <div class="es-swap-tabs mb-6">
                        <span class="es-swap-tab es-swap-tab-in">Event Schedule</span>
                    </div>
                    <h3 class="es-swap-ink mb-4 text-2xl font-bold">Why switch to Event Schedule?</h3>
                    <p class="es-swap-muted mb-6 leading-relaxed">
                        {{ $why_switch['intro'] ?? 'Event Schedule offers a unique combination of features that no other platform matches: zero platform fees, open source transparency, and powerful AI tools.' }}
                    </p>
                    <p class="es-swap-tag mb-4">What moves across</p>
                    <ul class="space-y-3" data-reveal-group="70">
                        @foreach (($why_switch['points'] ?? ['Zero platform fees on all ticket sales, at any plan level', 'Fully open source with selfhosting option for complete control', 'AI-powered event parsing, flyer generation, and automatic graphics', 'Two-way Google Calendar and CalDAV sync included free']) as $point)
                            <li class="flex items-start gap-3" data-reveal>
                                <svg aria-hidden="true" class="es-swap-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-swap-muted">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. FAQ                                                       -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="es-swap-hr scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="es-swap-mark mb-6" data-reveal aria-hidden="true"><span>Swap 06</span></div>
                <h2 class="es-balance es-swap-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    {{ $name }} to Event Schedule <span class="es-swap-accent">FAQ</span>
                </h2>
                <p class="es-swap-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    What people ask before they move the event work across.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $item)
                    <details name="faq" class="es-swap-hover es-swap-card group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-swap-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-swap-mono es-swap-accent flex-none text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-swap-hover-title flex-1 transition-colors">{{ $item['q'] }}</span>
                            <svg aria-hidden="true" class="es-swap-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-swap-muted mt-4 leading-relaxed ps-9">{{ $item['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Also replace / related features / compare                 -->
    <!-- ============================================================ -->
    <section id="also" class="es-swap-hr scroll-mt-24 py-20 lg:py-24">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 text-center">
                <h2 class="es-swap-ink text-2xl font-black tracking-tight md:text-3xl" data-reveal>Also replace</h2>
                <p class="es-swap-muted mt-3" data-reveal style="--reveal-delay: 0.05s;">The other tabs, one at a time.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3" data-reveal-group="80">
                @foreach ($cross_links as $link)
                    <a href="{{ route($link['route']) }}" class="es-swap-hover es-swap-card group flex flex-col p-6 transition-all duration-200 hover:-translate-y-1 hover:shadow-md" data-reveal>
                        <div class="es-swap-tabs mb-4">
                            <span class="es-swap-tab">{{ $link['name'] }}</span>
                        </div>
                        <div class="es-swap-hover-title es-swap-ink mb-2 text-lg font-semibold transition-colors">Replace {{ $link['name'] }}</div>
                        @if (!empty($link['description']))
                            <p class="es-swap-muted text-sm">{{ $link['description'] }}</p>
                        @endif
                        <span class="es-swap-hover-arrow es-swap-muted mt-auto inline-flex items-center gap-1 pt-4 text-xs font-medium transition-colors">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>

            @if (!empty($related_features))
            <div class="mt-16">
                <div class="mb-8 text-center">
                    <h2 class="es-swap-ink text-2xl font-black tracking-tight md:text-3xl" data-reveal>Explore related features</h2>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3" data-reveal-group="80">
                    @foreach ($related_features as $feature)
                        <a href="{{ route($feature['route']) }}" class="es-swap-hover es-swap-card group flex flex-col p-6 transition-all duration-200 hover:-translate-y-1 hover:shadow-md" data-reveal>
                            <div class="es-swap-hover-title es-swap-ink mb-2 text-lg font-semibold transition-colors">{{ $feature['name'] }}</div>
                            <p class="es-swap-muted mt-auto text-sm">{{ $feature['description'] }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="mt-16">
                <a href="{{ route('marketing.compare') }}" class="es-swap-hover es-swap-card group flex items-center justify-between p-8 transition-all duration-200 hover:-translate-y-1 hover:shadow-md" data-reveal>
                    <div>
                        <h2 class="es-swap-hover-title es-swap-ink mb-2 text-xl font-bold transition-colors md:text-2xl">
                            Looking for direct platform comparisons?
                        </h2>
                        <p class="es-swap-muted">
                            See how Event Schedule compares to Eventbrite, Luma, and Ticket Tailor.
                        </p>
                    </div>
                    <svg aria-hidden="true" class="es-swap-hover-arrow es-swap-muted ms-6 h-6 w-6 flex-none transition-colors rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Finale: one tab left open                                 -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-swap-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    <span class="es-swap-glyph mb-5">
                        <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 9h13m0 0l-3.5-3.5M17 9l-3.5 3.5" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 15H7m0 0l3.5-3.5M7 15l3.5 3.5" />
                        </svg>
                    </span>
                    <p class="es-swap-tag mb-4">One tab left</p>
                    <h2 class="es-balance es-swap-band-ink mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight md:text-5xl">
                        Ready to replace <span class="es-swap-lit">{{ $name }}?</span>
                    </h2>
                    <p class="es-swap-band-muted mx-auto mb-10 max-w-2xl text-lg sm:text-xl">
                        {{ $closeTabsLine }} No credit card, and no platform fees on ticket sales.
                    </p>

                    {{-- The tab, open, with the claim field as its address bar. --}}
                    <div class="mx-auto max-w-2xl text-start">
                        <div class="es-swap-tabs es-swap-tabs-lead">
                            <span class="es-swap-tab es-swap-tab-in">Your schedule</span>
                        </div>
                        {{-- p-2 on phones, not p-3: the window's padding comes out of the
                             address bar's width, and at 390px the placeholder and the
                             .eventschedule.com suffix have only a few pixels to spare. --}}
                        <div class="es-swap-window flex flex-col items-stretch gap-3 p-2 sm:flex-row sm:p-3">
                            <label for="es-claim-input" class="sr-only">Your schedule name</label>
                            <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-4 py-4 backdrop-blur-md transition-all sm:px-5">
                                <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                    class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-400 focus:outline-none focus:ring-0 sm:text-base">
                                <span class="shrink-0 select-none font-mono text-sm text-gray-300 sm:text-base">.eventschedule.com</span>
                            </div>
                            <a href="{{ app_url('/sign_up') }}" class="es-swap-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                                <span class="relative z-10 flex items-center gap-2">
                                    Get started free
                                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </span>
                                <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                            </a>
                        </div>
                    </div>

                    <p class="es-swap-band-muted mt-6 text-sm">The free plan is free forever. Open source, with a selfhosting option.</p>
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
                        <span class="es-swap-tip pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <x-marketing.related-pages />

    <!-- Local confetti (no CDN) + motion engines -->
    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
