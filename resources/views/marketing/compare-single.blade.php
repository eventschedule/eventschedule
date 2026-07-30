<x-marketing-layout>
    @php
        /* ==================================================================
           EVERY fact about the competitor on this page comes from
           MarketingController::getComparisonData(). This ONE template renders
           16 URLs (/eventbrite-alternative through /eventzilla-alternative),
           so a hard-coded rival name, price or fee would be wrong on fifteen
           of them. Nothing below authors a competitor claim: it reads the
           rows, counts them, and prints them as written.
           ================================================================== */

        // --- The scoreline ------------------------------------------------
        // $row[3] is the "Event Schedule has the edge" flag the controller
        // already publishes for each row, so this tally summarises claims the
        // page is making anyway rather than inventing a new one.
        $lineTotal = 0;
        $edgeTotal = 0;
        $tally = [];
        $sectionScore = [];

        foreach ($sections as $sectionName => $rows) {
            $edge = 0;
            foreach ($rows as $row) {
                $lineTotal++;
                $won = (bool) ($row[3] ?? false);
                $tally[] = $won;
                if ($won) {
                    $edge++;
                    $edgeTotal++;
                }
            }
            $sectionScore[$sectionName] = ['edge' => $edge, 'total' => count($rows)];
        }
        $restTotal = $lineTotal - $edgeTotal;

        // --- One reader for both columns ----------------------------------
        // A value becomes a MARK only when it is exactly Yes / No / N/A, or
        // one of those followed by a parenthesis. Anything else is a sentence
        // and is printed as written. Requiring the parenthesis is what stops
        // "No subscription (per-ticket only)" and "No native 2-way sync"
        // rendering as a bare cross, which is how the old template read them.
        $readValue = function ($raw, $isOurs = false) {
            $raw = trim((string) $raw);

            foreach (['Yes' => 'yes', 'No' => 'no', 'N/A' => 'na'] as $word => $kind) {
                if ($raw === $word) {
                    return ['kind' => $kind, 'plan' => null, 'note' => '', 'text' => ''];
                }
                if (str_starts_with($raw, $word.' (') && str_ends_with($raw, ')')) {
                    $note = trim(substr($raw, strlen($word) + 1), ' ()');
                    $plan = ($isOurs && in_array($note, ['Free', 'Pro', 'Enterprise'], true)) ? $note : null;

                    return ['kind' => $kind, 'plan' => $plan, 'note' => $plan ? '' : $note, 'text' => ''];
                }
            }

            return ['kind' => 'text', 'plan' => null, 'note' => '', 'text' => $raw];
        };

        // Screen-reader wording for each mark, so the card is not a set of
        // unlabelled squares.
        $markLabel = ['yes' => 'Included', 'no' => 'Not offered', 'na' => 'Not applicable'];

        // --- The three standing lines -------------------------------------
        // Printed at the top of the card because they are the same three rows
        // on all sixteen competitors, and because they are self-referential
        // truths (0%, open source, selfhostable) rather than rival claims.
        $keyLines = ['Platform fees', 'Open source', 'Selfhosting'];
        $standing = [];
        foreach ($keyLines as $keyLine) {
            foreach ($sections as $rows) {
                foreach ($rows as $row) {
                    if ($row[0] === $keyLine) {
                        $standing[] = $row;
                        continue 3;
                    }
                }
            }
        }

        // The plan the importer is on, read off its own row rather than typed,
        // so this cannot drift if another competitor gains an importer.
        $importPlan = null;
        foreach ($sections as $rows) {
            foreach ($rows as $row) {
                if (str_ends_with($row[0], 'auto-import')) {
                    $importPlan = $readValue($row[1], true)['plan'];
                    break 2;
                }
            }
        }

        // --- Optional blocks ----------------------------------------------
        // Only four competitors carry why_choose, one carries auto_import and
        // switch_steps, so every read is defaulted.
        $switchSteps = $switch_steps ?? [
            ['title' => 'Create your schedule', 'description' => 'Sign up free and create your first schedule in under a minute.'],
            ['title' => 'Add your events', 'description' => 'Paste event details for AI import or create events manually.'],
            ['title' => 'Share and sell', 'description' => 'Share your schedule URL and start selling tickets.'],
        ];
        $whyChooseSummary = $why_choose['summary'] ?? 'Event Schedule offers a combination no other platform matches: zero platform fees, open source transparency, and AI tools built in.';
        $whyChoosePoints = $why_choose['points'] ?? [
            'Zero platform fees on all ticket sales, at any plan level',
            'Fully open source with a selfhosting option for complete control',
            'AI event parsing, flyer generation, and automatic event graphics',
            'Two-way Google Calendar and CalDAV sync included free',
        ];

        // FAQ: one array feeds the visible list AND the schema component, so
        // the two can never drift apart.
        $faqs = array_map(fn ($item) => ['q' => $item['question'], 'a' => $item['answer']], $faq);

        // --- Field numbering ----------------------------------------------
        // The card's fields are numbered like a real scorecard. The import
        // field only exists for competitors with an importer, so the numbers
        // are derived rather than typed. This list holds ONLY the sections
        // that actually print a plate, so the sequence runs 01..0N with no
        // gaps: the hero is the head of the card, not a field, and numbering
        // a section that never shows its number made the printed run skip
        // (03 then 05, with the dark band silently holding 04).
        $fieldKeys = array_values(array_filter([
            ! empty($auto_import) ? 'import' : null,
            'card',
            'limits',
            'edge',
            'both',
            'switch',
            'faq',
            'claim',
        ]));
        $fieldNo = fn ($key) => str_pad((string) (array_search($key, $fieldKeys, true) + 1), 2, '0', STR_PAD_LEFT);

        $dotSections = array_values(array_filter([
            ['top', 'The scorecard'],
            ! empty($auto_import) ? ['import', 'Bring them over'] : null,
            ['card', 'Line by line'],
            ['limits', 'What we do not claim'],
            ['edge', 'Where the marks fall'],
            ['both', 'Both columns'],
            ['switch', 'How to switch'],
            ['faq', 'Questions'],
            ['claim', 'Start free'],
        ]));
    @endphp

    <x-slot name="title">{{ $name }} Alternative | Event Schedule</x-slot>
    <x-slot name="description">{{ $description }}</x-slot>
    <x-slot name="keywords">{{ $keywords }}</x-slot>
    <x-slot name="breadcrumbTitle">{{ $name }} Alternative</x-slot>

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
        "offers": [
            {
                "@type": "Offer",
                "name": "Free",
                "price": "0",
                "priceCurrency": "USD",
                "description": "Unlimited events, Google Calendar and CalDAV sync, newsletters, RSVP with capacity, embeddable calendar, and fan engagement features.",
                "availability": "https://schema.org/InStock"
            },
            {
                "@type": "Offer",
                "name": "Pro",
                "price": "5.00",
                "priceCurrency": "USD",
                "description": "Everything in Free plus ticketing with QR check-ins and live dashboard, ticket waitlist, sale notifications, sales CSV export, Stripe payments, remove branding, custom CSS, event graphics, REST API, and webhooks.",
                "availability": "https://schema.org/InStock"
            },
            {
                "@type": "Offer",
                "name": "Enterprise",
                "price": "15.00",
                "priceCurrency": "USD",
                "description": "Everything in Pro plus AI style generation, AI content generation, AI flyer generation, WhatsApp event creation, custom domains, multiple team members, and priority support.",
                "availability": "https://schema.org/InStock"
            }
        ],
        "featureList": [
            "Zero platform fees on ticket sales",
            "AI-powered event import",
            "AI flyer generation",
            "AI style generation",
            "Two-way Google Calendar sync",
            "CalDAV sync",
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
            "Fan videos and comments",
            "Sub-schedules"
        ]
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to switch from {{ str_replace('"', '\\"', $name) }} to Event Schedule",
        "step": [
            @foreach ($switchSteps as $index => $step)
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
           Compare-single "The Scorecard" styles. A comparison page IS a
           judge's card: a stiff ruled card, one line per contested point,
           a mark in each column, a tally in the margin. The concept and
           the argument are the same object, because the only honest way
           to win a scorecard is line by line.

           WHY THIS DEVICE. The /compare hub owns the typographic "vs"
           mark, so the singles must not reuse it: here "vs" is a small
           inline mark inside the h1 and the identity work is done by the
           two NAME PLATES (ours inked, theirs hollow). That is also the
           only identity available - there are no rival logos in this repo
           and none will be added, so the plates are typographic on
           purpose, not for want of an image.

           DATA. This file renders 16 competitor URLs from
           getComparisonData(). Nothing here may hard-code a rival name,
           price, fee or logo. The tally, the section scores and the three
           standing lines are all COUNTED from the rows, so pretix (open
           source, selfhostable, and level with us on the Platform group)
           reads correctly with no special-casing.

           COLOUR: the page keeps its existing blue family, but drops the
           blue-to-sky-to-cyan sweep it used to paint on every heading -
           that gradient is the shared site chrome, and its bright cyan
           stop is the single most common AA failure in this codebase
           (#06b6d4 on white is 2.43). One ink blue instead, #1d4ed8 for
           fills and #1e40af for type on card stock. Distinctiveness comes
           from the MATERIAL (card stock in light, a graphite plate in
           dark), the RULES (hairlines, not boxes) and the TYPE (tabular
           mono numerals, wide-tracked field labels).

           MUTED INK: never text-gray-500 here. #6b7280 measures 4.23 on
           this page's tinted ground. .es-score-muted is #4b5158 (7.36 on
           the page ground, 8.02 on the card).

           FIXED OBJECT: the card is deliberately NOT pinned across colour
           modes - it is stock in light and graphite in dark. The two
           .es-score-band sections ARE pinned: they carry the three shared
           overrides (grid-overlay / animate-shimmer / es-claim) AND every
           page-local part they contain (card, tag, field plate, rule, tally),
           so they render identically with .dark on and off.
           ============================================================== */

        /* --- Ground and ink --- */
        .es-score-page { background-color: #f4f5f7; color: #12161c; }
        .dark .es-score-page { background-color: #0b0e13; color: #e7eaef; }

        .es-score-ink { color: #12161c; }
        .dark .es-score-ink { color: #e7eaef; }
        .es-score-muted { color: #4b5158; }
        .dark .es-score-muted { color: #9aa4b0; }
        .es-score-accent { color: #1e40af; }
        .dark .es-score-accent { color: #9cc0ff; }

        /* Always-lit ink for the fixed-dark bands, in both colour modes. */
        .es-score-band-ink { color: #f2f5f9; }
        .es-score-band-muted { color: #a3adba; }
        .es-score-lit { color: #9cc0ff; }

        /* --- The card: stiff stock in light, a graphite plate in dark --- */
        .es-score-card {
            background: #ffffff;
            border: 1px solid rgba(18, 22, 28, 0.12);
            border-radius: 0.9rem;
            box-shadow: 0 1px 2px rgba(18, 22, 28, 0.05);
        }
        .dark .es-score-card {
            background: #14181f;
            border-color: rgba(231, 234, 239, 0.12);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.4);
        }

        /* --- Section separators. These were `border-[rgba(18,22,28,0.08)]`
               and `dark:border-[rgba(231,234,239,0.08)]` utilities, and
               NEITHER is in the built marketing CSS: both silently did
               nothing and every divider fell back to Tailwind's default
               gray-200, which is a glaring hairline on a #0b0e13 page. The
               contrast probe cannot catch that - a border is not text - so
               separators live here as real rules. --- */
        .es-score-sep { border-top: 1px solid rgba(18, 22, 28, 0.09); }
        .dark .es-score-sep { border-top-color: rgba(231, 234, 239, 0.09); }
        .es-score-sep-y {
            border-top: 1px solid rgba(18, 22, 28, 0.09);
            border-bottom: 1px solid rgba(18, 22, 28, 0.09);
        }
        .dark .es-score-sep-y {
            border-top-color: rgba(231, 234, 239, 0.09);
            border-bottom-color: rgba(231, 234, 239, 0.09);
        }

        /* --- Hairline rule, used as a real divider element --- */
        .es-score-rule {
            height: 1px;
            background: rgba(18, 22, 28, 0.12);
        }
        .dark .es-score-rule { background: rgba(231, 234, 239, 0.12); }

        /* --- Field labels: wide-tracked micro caps --- */
        .es-score-tag {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            color: #4b5158;
        }
        .dark .es-score-tag { color: #9aa4b0; }

        /* --- Field numeral: the card's own field number --- */
        .es-score-field {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.3rem 0.8rem;
            border-radius: 0.3rem;
            border: 1px solid rgba(18, 22, 28, 0.18);
            background: #ffffff;
            color: #12161c;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-weight: 800;
            font-size: 0.8rem;
            letter-spacing: 0.06em;
        }
        .dark .es-score-field {
            border-color: rgba(231, 234, 239, 0.2);
            background: rgba(231, 234, 239, 0.05);
            color: #e7eaef;
        }
        .es-score-field::before {
            content: "";
            width: 2px;
            align-self: stretch;
            border-radius: 1px;
            background: #1d4ed8;
        }
        .dark .es-score-field::before { background: #9cc0ff; }

        /* --- The h1's inline "vs". Small on purpose: the /compare hub owns
               the big typographic vs mark and must keep it. --- */
        .es-score-vs {
            font-size: 0.42em;
            font-weight: 700;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            vertical-align: 0.34em;
            color: #4b5158;
        }
        .dark .es-score-vs { color: #9aa4b0; }

        /* --- Name plates: the only identity available, since this repo
               carries no rival logos. Ours is inked, theirs is hollow. --- */
        .es-score-plate {
            display: inline-flex;
            align-items: center;
            max-width: 100%;
            padding: 0.3rem 0.65rem;
            border-radius: 0.35rem;
            font-size: 0.82rem;
            font-weight: 800;
            letter-spacing: -0.01em;
            line-height: 1.25;
        }
        .es-score-plate-ours { background: #1d4ed8; color: #ffffff; }
        .es-score-plate-theirs {
            border: 1px solid rgba(18, 22, 28, 0.3);
            color: #12161c;
        }
        .dark .es-score-plate-theirs {
            border-color: rgba(231, 234, 239, 0.3);
            color: #e7eaef;
        }

        /* --- The tally: one tick per compared line, filled where the row
               is flagged as an Event Schedule edge. --- */
        .es-score-tally {
            display: flex;
            align-items: flex-end;
            gap: 2px;
            height: 2.6rem;
        }
        .es-score-tick {
            flex: 1 1 0;
            min-width: 2px;
            height: 45%;
            border-radius: 1px;
            background: rgba(18, 22, 28, 0.16);
            transform-origin: bottom;
            transition: transform 0.75s cubic-bezier(0.22, 1, 0.36, 1);
            transition-delay: calc(var(--i, 0) * 9ms);
        }
        .dark .es-score-tick { background: rgba(231, 234, 239, 0.18); }
        .es-score-tick-on {
            height: 100%;
            background: #1d4ed8;
        }
        .dark .es-score-tick-on { background: #9cc0ff; }
        /* Only the UNDRAWN pre-state is gated, so a no-JS or reduced-motion
           visitor sees the finished tally. */
        html.es-anim [data-reveal]:not(.is-revealed) .es-score-tick { transform: scaleY(0.1); }

        /* --- The scoreline numerals --- */
        .es-score-num {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-weight: 800;
            letter-spacing: -0.04em;
            line-height: 1;
        }

        /* --- The scoreline. Wraps rather than crushes: at 390px the caption
               drops under the numerals instead of squeezing to four words a
               line beside them. --- */
        .es-score-scoreline {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 0.4rem 1rem;
        }
        .es-score-scoreline > p + p { flex: 1 1 13rem; }

        /* --- The standing lines strip in the card head --- */
        .es-score-standing {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1fr);
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0;
            border-top: 1px solid rgba(18, 22, 28, 0.08);
        }
        .dark .es-score-standing { border-top-color: rgba(231, 234, 239, 0.09); }
        /* SELFHOSTING is eleven characters and cannot wrap, so the strip's
           labels track tighter than the page's field labels or they run into
           the mark beside them at 390px. */
        .es-score-standing .es-score-tag {
            font-size: 0.63rem;
            letter-spacing: 0.1em;
        }

        /* --- The card proper: a ruled table. border-spacing rather than
               collapse, so the sticky line column keeps its rules. --- */
        .es-score-scroll {
            /* position:relative is load-bearing, not decoration. The mark
               labels in the cells are .sr-only, i.e. position:absolute, and
               with no positioned ancestor their containing block is the
               initial one - so overflow:hidden on the card does NOT clip them
               and the widest of them pushed the whole document 12px sideways
               on a 390px viewport. Containing them here fixes it. */
            position: relative;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .es-score-scroll::-webkit-scrollbar { height: 6px; }
        .es-score-scroll::-webkit-scrollbar-track { background: transparent; }
        .es-score-scroll::-webkit-scrollbar-thumb {
            background: rgba(18, 22, 28, 0.28);
            border-radius: 3px;
        }
        .dark .es-score-scroll::-webkit-scrollbar-thumb { background: rgba(231, 234, 239, 0.24); }

        .es-score-table {
            width: 100%;
            min-width: 33rem;
            border-collapse: separate;
            border-spacing: 0;
            text-align: left;
        }
        .es-score-table th,
        .es-score-table td {
            padding: 0.62rem 0.9rem;
            vertical-align: top;
        }
        .es-score-table thead th {
            padding-top: 1.1rem;
            padding-bottom: 0.9rem;
            border-bottom: 1px solid rgba(18, 22, 28, 0.16);
            background: #ffffff;
        }
        .dark .es-score-table thead th {
            border-bottom-color: rgba(231, 234, 239, 0.16);
            background: #14181f;
        }
        .es-score-table thead th:first-child { min-width: 12.5rem; }
        .es-score-table thead th + th { min-width: 9.5rem; }

        /* A section of the card. Each is its own <tbody> row group. Its label
           and its score travel together at the start of the band and stay
           pinned while the card scrolls sideways on a phone, so you always
           know which section you are reading. */
        .es-score-secgroup {
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            gap: 0.15rem 0.75rem;
            width: max-content;
            max-width: 100%;
        }
        .es-score-sec th {
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
            background: #eceef2;
            border-top: 1px solid rgba(18, 22, 28, 0.1);
            border-bottom: 1px solid rgba(18, 22, 28, 0.1);
        }
        .dark .es-score-sec th {
            background: #1b212a;
            border-top-color: rgba(231, 234, 239, 0.1);
            border-bottom-color: rgba(231, 234, 239, 0.1);
        }

        .es-score-row th,
        .es-score-row td {
            border-top: 1px solid rgba(18, 22, 28, 0.07);
            background: #ffffff;
        }
        .dark .es-score-row th,
        .dark .es-score-row td {
            border-top-color: rgba(231, 234, 239, 0.07);
            background: #14181f;
        }
        .es-score-row:first-child th,
        .es-score-row:first-child td { border-top: 0; }

        /* Our column carries the faintest wash, so the eye can hold the
           column while scrolling sideways. */
        .es-score-ours { box-shadow: inset 0 0 0 999px rgba(29, 78, 216, 0.045); }
        .dark .es-score-ours { box-shadow: inset 0 0 0 999px rgba(156, 192, 255, 0.06); }

        /* The three lines the whole page rests on. */
        .es-score-key th,
        .es-score-key td { border-top-color: rgba(29, 78, 216, 0.4); }
        .dark .es-score-key th,
        .dark .es-score-key td { border-top-color: rgba(156, 192, 255, 0.35); }

        @media (max-width: 767px) {
            .es-score-table th[scope="row"] {
                position: sticky;
                left: 0;
                z-index: 2;
                box-shadow: 1px 0 0 rgba(18, 22, 28, 0.07);
            }
            .dark .es-score-table th[scope="row"] { box-shadow: 1px 0 0 rgba(231, 234, 239, 0.07); }
            .es-score-table thead th:first-child {
                position: sticky;
                left: 0;
                z-index: 3;
            }
            .es-score-secgroup {
                position: sticky;
                left: 0;
            }
        }

        /* --- The line label, with the margin tally mark --- */
        .es-score-gutter {
            display: inline-flex;
            justify-content: center;
            width: 0.7rem;
            flex: none;
        }
        .es-score-wedge {
            width: 0.36rem;
            height: 0.36rem;
            border-radius: 1px;
            background: #1d4ed8;
            transform: rotate(45deg);
        }
        .dark .es-score-wedge { background: #9cc0ff; }
        /* A neutral list mark. The diamond above is spoken for by the legend
           ("Event Schedule has the edge"), so the competitor's strengths get
           their own mark rather than borrowing ours. */
        .es-score-bullet {
            width: 0.55rem;
            height: 2px;
            border-radius: 1px;
            background: rgba(18, 22, 28, 0.4);
        }
        .dark .es-score-bullet { background: rgba(231, 234, 239, 0.4); }

        /* --- Marks --- */
        .es-score-mark {
            position: relative;
            display: inline-block;
            width: 0.95rem;
            height: 0.95rem;
            flex: none;
            border-radius: 0.2rem;
        }
        .es-score-mark-yes { background: #1d4ed8; }
        .es-score-mark-yes::after {
            content: "";
            position: absolute;
            left: 0.22rem;
            top: 0.26rem;
            width: 0.42rem;
            height: 0.2rem;
            border-left: 2px solid #ffffff;
            border-bottom: 2px solid #ffffff;
            transform: rotate(-45deg);
        }
        .es-score-mark-no { border: 1.5px solid rgba(18, 22, 28, 0.32); }
        .dark .es-score-mark-no { border-color: rgba(231, 234, 239, 0.32); }
        .es-score-mark-na {
            border: 1.5px dashed rgba(18, 22, 28, 0.3);
        }
        .dark .es-score-mark-na { border-color: rgba(231, 234, 239, 0.3); }

        /* --- Plan chips. Free is the good news, so it is the inked one. --- */
        .es-score-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.05rem 0.4rem;
            border-radius: 0.25rem;
            border: 1px solid rgba(18, 22, 28, 0.32);
            color: #12161c;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }
        .dark .es-score-plan {
            border-color: rgba(231, 234, 239, 0.34);
            color: #e7eaef;
        }
        .es-score-plan-free {
            border-color: rgba(29, 78, 216, 0.45);
            color: #1e40af;
        }
        .dark .es-score-plan-free {
            border-color: rgba(156, 192, 255, 0.45);
            color: #9cc0ff;
        }

        /* --- Cell text --- */
        .es-score-note {
            font-size: 0.72rem;
            line-height: 1.35;
            color: #4b5158;
        }
        .dark .es-score-note { color: #9aa4b0; }
        .es-score-val {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.78rem;
            line-height: 1.35;
            color: #12161c;
        }
        .dark .es-score-val { color: #e7eaef; }

        /* --- Advantage icon plate --- */
        .es-score-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.6rem;
            border: 1px solid rgba(29, 78, 216, 0.28);
            background: rgba(29, 78, 216, 0.07);
            color: #1e40af;
        }
        .dark .es-score-icon {
            border-color: rgba(156, 192, 255, 0.3);
            background: rgba(156, 192, 255, 0.08);
            color: #9cc0ff;
        }

        /* --- The two facing columns: their side and ours, one hairline
               between them, equal type weight on both. --- */
        /* The rule and the gutter it needs live here together: `lg:ps-14` and
           `lg:pt-0` are NOT in the built marketing CSS, so relying on them
           would have put the column hard against the hairline. */
        .es-score-facing {
            border-top: 1px solid rgba(18, 22, 28, 0.12);
            padding-top: 2.5rem;
        }
        .dark .es-score-facing { border-top-color: rgba(231, 234, 239, 0.12); }
        @media (min-width: 1024px) {
            .es-score-facing {
                border-top: 0;
                padding-top: 0;
                border-inline-start: 1px solid rgba(18, 22, 28, 0.12);
                padding-inline-start: 3.5rem;
            }
            .dark .es-score-facing { border-inline-start-color: rgba(231, 234, 239, 0.12); }
        }

        /* --- Fixed-dark bands: identical with .dark on and off --- */
        .es-score-band {
            background-color: #0a0d12;
            background-image: radial-gradient(120% 100% at 50% 0%, #141b25 0%, #0d1218 55%, #080b0f 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.55), inset 0 1px 0 rgba(231, 234, 239, 0.05);
        }
        /* Shared classes that flip with the colour mode. Pin them inside the
           bands, AFTER their base rules, or the "same object" changes shade. */
        .es-score-band .es-score-card {
            background: #12171e;
            border-color: rgba(231, 234, 239, 0.12);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.4);
        }
        .es-score-band .es-score-tag { color: #9cc0ff; }
        /* The bands are fields of the SAME card, so everything the card is made
           of has to be pinned inside them too, or the object changes material
           halfway down the page: the numbered field plate, the hairline, and
           the tally (whose light-mode ink is #12161c on a #0a0d12 ground, i.e.
           invisible, and whose filled tick is a dark blue that vanishes the
           same way). None of this is catchable by the contrast probe, because a
           tick is not text. */
        .es-score-band .es-score-field {
            border-color: rgba(231, 234, 239, 0.22);
            background: rgba(231, 234, 239, 0.06);
            color: #f2f5f9;
        }
        .es-score-band .es-score-field::before { background: #9cc0ff; }
        .es-score-band .es-score-rule { background: rgba(231, 234, 239, 0.14); }
        .es-score-band .es-score-tick { background: rgba(231, 234, 239, 0.2); }
        .es-score-band .es-score-tick-on { background: #9cc0ff; }
        .es-score-band .grid-overlay {
            background-image:
                linear-gradient(rgba(231, 234, 239, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(231, 234, 239, 0.05) 1px, transparent 1px);
        }
        .es-score-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-score-band .es-claim:focus-within {
            border-color: rgba(156, 192, 255, 0.75);
            box-shadow: 0 0 0 4px rgba(156, 192, 255, 0.22);
        }

        /* --- Buttons and links --- */
        .es-score-btn {
            background-color: #1d4ed8;
            color: #ffffff;
            box-shadow: 0 18px 36px -14px rgba(29, 78, 216, 0.45);
        }
        .es-score-btn:hover {
            background-color: #1e40af;
            box-shadow: 0 22px 44px -14px rgba(29, 78, 216, 0.55);
        }
        .es-score-link { color: #1e40af; }
        .es-score-link:hover { color: #12161c; }
        .dark .es-score-link { color: #9cc0ff; }
        .dark .es-score-link:hover { color: #e7eaef; }

        .es-score-hover:hover { border-color: rgba(29, 78, 216, 0.45); }
        .dark .es-score-hover:hover { border-color: rgba(156, 192, 255, 0.45); }
        .es-score-hover:hover .es-score-hover-title,
        .es-score-hover:hover .es-score-hover-arrow { color: #1e40af; }
        .dark .es-score-hover:hover .es-score-hover-title,
        .dark .es-score-hover:hover .es-score-hover-arrow { color: #9cc0ff; }

        /* --- Shared-system recolours (brand blue by default) --- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(29, 78, 216, 0.13), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(156, 192, 255, 0.12), transparent 60%);
        }
        /* The dot-nav tooltip. Its colours live here rather than as
           `dark:bg-[#14181f]` utilities, because an arbitrary Tailwind value
           that is not already in the built marketing CSS silently does
           nothing - which left white-on-white labels in dark mode. */
        .es-score-tip {
            background: #ffffff;
            border: 1px solid rgba(18, 22, 28, 0.14);
            color: #12161c;
        }
        .dark .es-score-tip {
            background: #14181f;
            border-color: rgba(231, 234, 239, 0.14);
            color: #e7eaef;
        }

        .es-dot:hover .es-dot-pip { background-color: rgba(29, 78, 216, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(156, 192, 255, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #1d4ed8; }
        .dark .es-dot.is-active .es-dot-pip { background: #9cc0ff; }

        /* --- Focus rings. No border-radius here: it would reshape the
               element itself, and outlines already follow its shape. --- */
        #es-score-page a:focus-visible,
        #es-score-page summary:focus-visible,
        #es-score-page button:focus-visible,
        #es-score-page input:focus-visible {
            outline: 2px solid #1d4ed8;
            outline-offset: 3px;
        }
        .dark #es-score-page a:focus-visible,
        .dark #es-score-page summary:focus-visible,
        .dark #es-score-page button:focus-visible,
        .dark #es-score-page input:focus-visible {
            outline-color: #9cc0ff;
        }
        .es-score-band a:focus-visible,
        .es-score-band input:focus-visible {
            outline-color: #9cc0ff !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-score-tick {
                transform: none !important;
                transition: none !important;
            }
        }
    </style>

    <div id="es-score-page" class="es-score-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the head of the card                                -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(72svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(29, 78, 216, 0.16), rgba(29, 78, 216, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(30, 64, 175, 0.12), rgba(30, 64, 175, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 mb-6">
                        <a href="{{ route('marketing.compare') }}" class="es-score-link text-sm font-medium">
                            &larr; Compare all platforms
                        </a>
                    </div>

                    <p class="es-fade-up es-d-1 es-score-tag mb-5">Scorecard</p>

                    <h1 class="es-balance es-score-ink mb-7 text-[2.6rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">Event Schedule</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="es-score-vs">vs</span> <span class="es-score-accent">{{ $name }}</span></span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-score-muted mb-9 max-w-xl text-lg sm:text-xl">
                        {{ $tagline }}
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="#card" class="glass es-score-ink group inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            Read the card
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                        <a href="{{ app_url('/sign_up') }}" class="es-score-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Get started free
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- The head of the card: two plates, the tally, the score,
                     and the three lines that hold on all sixteen cards. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-score-card p-6 sm:p-7">
                        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                            <span class="es-score-plate es-score-plate-ours">Event Schedule</span>
                            <span class="es-score-vs" aria-hidden="true">vs</span>
                            <span class="es-score-plate es-score-plate-theirs">{{ $name }}</span>
                        </div>

                        <div class="es-score-tally mb-3" aria-hidden="true">
                            @foreach ($tally as $tallyIndex => $won)
                                @if ($won)
                                    <span class="es-score-tick es-score-tick-on" style="--i: {{ $tallyIndex }};"></span>
                                @else
                                    <span class="es-score-tick" style="--i: {{ $tallyIndex }};"></span>
                                @endif
                            @endforeach
                        </div>

                        <div class="es-score-scoreline mb-5">
                            <p class="es-score-num es-score-accent text-5xl">
                                <span data-count-to="{{ $edgeTotal }}">{{ $edgeTotal }}</span><span class="es-score-muted text-2xl">/{{ $lineTotal }}</span>
                            </p>
                            <p class="es-score-muted pb-1 text-sm leading-snug">
                                lines where Event&nbsp;Schedule has the edge.<br>
                                The other {{ $restTotal }} are a match or go to {{ $name }}.
                            </p>
                        </div>

                        <div class="es-score-rule mb-1"></div>

                        @foreach ($standing as $row)
                            @php
                                $oursValue = $readValue($row[1], true);
                                $theirsValue = $readValue($row[2]);
                            @endphp
                            <div class="es-score-standing">
                                <span class="es-score-tag">{{ $row[0] }}</span>
                                @foreach ([$oursValue, $theirsValue] as $value)
                                    <span class="flex min-w-0 items-center gap-1.5">
                                        @if ($value['kind'] === 'yes')
                                            <span class="es-score-mark es-score-mark-yes"></span>
                                            <span class="sr-only">{{ $markLabel['yes'] }}</span>
                                        @elseif ($value['kind'] === 'no')
                                            <span class="es-score-mark es-score-mark-no"></span>
                                            <span class="sr-only">{{ $markLabel['no'] }}</span>
                                        @elseif ($value['kind'] === 'na')
                                            <span class="es-score-mark es-score-mark-na"></span>
                                            <span class="sr-only">{{ $markLabel['na'] }}</span>
                                        @else
                                            <span class="es-score-val">{{ $value['text'] }}</span>
                                        @endif

                                        @if ($value['note'])
                                            <span class="es-score-note">{{ $value['note'] }}</span>
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        @endforeach

                        <p class="es-score-note mt-4">
                            Every line on this card is scored from the same table below. Nothing is averaged, weighted or rounded.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if (!empty($auto_import))
    <!-- ============================================================ -->
    <!-- 2. Bring the events over (only where an importer exists)     -->
    <!-- ============================================================ -->
    <section id="import" class="es-score-sep-y scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-start gap-12 lg:grid-cols-2">
                <div>
                    <div class="es-score-field mb-6" data-reveal aria-hidden="true"><span>{{ $fieldNo('import') }}</span></div>
                    <p class="es-score-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Import from {{ $name }}</p>
                    <h2 class="es-balance es-score-ink mb-5 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                        {{ $auto_import['title'] }}
                    </h2>
                    <p class="es-score-muted mb-8 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        {{ $auto_import['description'] }}
                    </p>
                    <ul class="space-y-3" data-reveal-group="70">
                        @foreach ($auto_import['bullets'] as $bullet)
                            <li class="es-score-muted flex items-center gap-3" data-reveal>
                                <span class="es-score-mark es-score-mark-yes" aria-hidden="true"></span>
                                <span>{{ $bullet }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="space-y-3" data-reveal-group="90">
                    @foreach ($auto_import['steps'] as $index => $step)
                        <div class="es-score-card flex items-start gap-4 p-5" data-reveal="panel">
                            <span class="es-score-num es-score-accent pt-0.5 text-xl" aria-hidden="true">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <div class="min-w-0">
                                <h3 class="es-score-ink mb-1 font-bold">{{ $step['title'] }}</h3>
                                <p class="es-score-muted text-sm">{{ $step['description'] }}</p>
                            </div>
                        </div>
                    @endforeach
                    @if ($importPlan && $importPlan !== 'Free')
                        <p class="es-score-note">Importing is on the {{ $importPlan }} plan. Publishing your schedule and its dates stays free forever.</p>
                    @endif
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- ============================================================ -->
    <!-- 3. The card, line by line                                    -->
    <!-- ============================================================ -->
    <section id="card" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-score-field mb-6" data-reveal aria-hidden="true"><span>{{ $fieldNo('card') }}</span></div>
                <p class="es-score-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The card</p>
                <h2 class="es-balance es-score-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Scored <span class="es-score-accent">line by line.</span>
                </h2>
                <p class="es-score-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    A feature-by-feature comparison of Event Schedule and {{ $name }}, in {{ $lineTotal }} lines across {{ count($sections) }} sections. Our column names the plan behind every gated feature, because a comparison that hides the tier is not a comparison.
                </p>
            </div>

            <div class="es-score-card overflow-hidden" data-reveal="panel">
                <div class="es-score-scroll">
                    <table class="es-score-table">
                        <caption class="sr-only">Feature-by-feature comparison of Event Schedule and {{ $name }}. A diamond in the margin marks a line where Event Schedule has the edge.</caption>
                        <thead>
                            <tr>
                                <th scope="col">
                                    <span class="es-score-tag">Line</span>
                                </th>
                                <th scope="col" class="es-score-ours">
                                    <span class="es-score-plate es-score-plate-ours">Event Schedule</span>
                                </th>
                                <th scope="col">
                                    <span class="es-score-plate es-score-plate-theirs">{{ $name }}</span>
                                </th>
                            </tr>
                        </thead>

                        @foreach ($sections as $sectionName => $rows)
                            <tbody>
                                <tr class="es-score-sec">
                                    <th scope="colgroup" colspan="3">
                                        <span class="es-score-secgroup">
                                            <span class="es-score-tag">{{ $sectionName }}</span>
                                            <span class="es-score-note">
                                                {{ $sectionScore[$sectionName]['edge'] }} of {{ $sectionScore[$sectionName]['total'] }} to Event Schedule
                                            </span>
                                        </span>
                                    </th>
                                </tr>

                                @foreach ($rows as $row)
                                    @php
                                        $won = (bool) ($row[3] ?? false);
                                        $isKeyLine = in_array($row[0], $keyLines, true);
                                    @endphp
                                    @if ($isKeyLine)
                                        <tr class="es-score-row es-score-key">
                                    @else
                                        <tr class="es-score-row">
                                    @endif
                                        <th scope="row">
                                            <span class="flex items-start gap-2">
                                                <span class="es-score-gutter pt-1.5">
                                                    @if ($won)
                                                        <span class="es-score-wedge"></span>
                                                    @endif
                                                </span>
                                                <span class="es-score-ink min-w-0 text-sm font-semibold">
                                                    {{ $row[0] }}
                                                    @if ($won)
                                                        <span class="sr-only">(Event Schedule has the edge on this line)</span>
                                                    @endif
                                                </span>
                                            </span>
                                        </th>

                                        @foreach ([['ours', $row[1]], ['theirs', $row[2]]] as [$side, $raw])
                                            @php $value = $readValue($raw, $side === 'ours'); @endphp
                                            @if ($side === 'ours')
                                                <td class="es-score-ours">
                                            @else
                                                <td>
                                            @endif
                                                <span class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                                    @if ($value['kind'] === 'yes')
                                                        <span class="es-score-mark es-score-mark-yes"></span>
                                                        <span class="sr-only">{{ $markLabel['yes'] }}</span>
                                                    @elseif ($value['kind'] === 'no')
                                                        <span class="es-score-mark es-score-mark-no"></span>
                                                        <span class="sr-only">{{ $markLabel['no'] }}</span>
                                                    @elseif ($value['kind'] === 'na')
                                                        <span class="es-score-mark es-score-mark-na"></span>
                                                        <span class="sr-only">{{ $markLabel['na'] }}</span>
                                                    @else
                                                        <span class="es-score-val">{{ $value['text'] }}</span>
                                                    @endif

                                                    @if ($value['plan'] === 'Free')
                                                        <span class="es-score-plan es-score-plan-free">Free</span>
                                                    @elseif ($value['plan'])
                                                        <span class="es-score-plan">{{ $value['plan'] }}</span>
                                                    @endif

                                                    @if ($value['note'])
                                                        <span class="es-score-note">{{ $value['note'] }}</span>
                                                    @endif
                                                </span>
                                            </td>
                                        @endforeach
                                        </tr>
                                @endforeach
                            </tbody>
                        @endforeach
                    </table>
                </div>
            </div>

            <!-- Legend: what the marks mean, spelled out. -->
            <div class="mt-6 flex flex-wrap items-center gap-x-6 gap-y-3" data-reveal>
                <span class="flex items-center gap-2">
                    <span class="es-score-mark es-score-mark-yes" aria-hidden="true"></span>
                    <span class="es-score-note">Included</span>
                </span>
                <span class="flex items-center gap-2">
                    <span class="es-score-mark es-score-mark-no" aria-hidden="true"></span>
                    <span class="es-score-note">Not offered</span>
                </span>
                <span class="flex items-center gap-2">
                    <span class="es-score-mark es-score-mark-na" aria-hidden="true"></span>
                    <span class="es-score-note">Not applicable</span>
                </span>
                <span class="flex items-center gap-2">
                    <span class="es-score-gutter"><span class="es-score-wedge" aria-hidden="true"></span></span>
                    <span class="es-score-note">Event Schedule has the edge on this line</span>
                </span>
                <span class="flex items-center gap-2">
                    <span class="es-score-plan es-score-plan-free">Free</span>
                    <span class="es-score-note">The plan the feature is on</span>
                </span>
            </div>

            <p class="es-score-muted mx-auto mt-8 max-w-3xl text-center text-sm" data-reveal>
                Selfhost Event Schedule and every one of those plan chips opens: a selfhosted install resolves to Enterprise, so no line on the card is held back by a plan. Either way, ticket sales run through your own Stripe account and Event Schedule takes 0%.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. What the card does not claim (fixed-dark band)            -->
    <!-- ============================================================ -->
    <section id="limits" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-score-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-score-field mb-6" data-reveal aria-hidden="true"><span>{{ $fieldNo('limits') }}</span></div>
                    <p class="es-score-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Straight answers</p>
                    <h2 class="es-balance es-score-band-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Three lines <span class="es-score-lit">we do not claim.</span>
                    </h2>
                    <p class="es-score-band-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        A card you can trust has to name what is missing from it. These three are true about Event Schedule no matter which platform you are holding it against.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-3" data-reveal-group="110">
                    <div class="es-score-card flex flex-col p-6" data-reveal="panel">
                        <p class="es-score-tag mb-3">No marketplace</p>
                        <h3 class="es-score-band-ink mb-2 text-lg font-bold">You bring the audience</h3>
                        <p class="es-score-band-muted text-sm">Your public events can be listed on the Event Schedule browse and search pages, but a listing is not a marketplace's built-in audience. What actually fills a room is your own schedule page, an embeddable calendar, a follower QR code and newsletters.</p>
                    </div>
                    <div class="es-score-card flex flex-col p-6" data-reveal="panel">
                        <p class="es-score-tag mb-3">No seat maps</p>
                        <h3 class="es-score-band-ink mb-2 text-lg font-bold">Ticket types, not seats</h3>
                        <p class="es-score-band-muted text-sm">A ticket type has a name, a price and a quantity. There is no seating chart and buyers are not choosing a specific seat, so a reserved-seating house will want something else.</p>
                    </div>
                    <div class="es-score-card flex flex-col p-6" data-reveal="panel">
                        <p class="es-score-tag mb-3">One team member on Free</p>
                        <h3 class="es-score-band-ink mb-2 text-lg font-bold">Extra members are Enterprise</h3>
                        <p class="es-score-band-muted text-sm">The free plan is one person per schedule. Adding team members is on Enterprise and is capped at five on the hosted platform, so do not plan on inviting a whole staff for nothing.</p>
                    </div>
                </div>

                <p class="es-score-band-muted mt-10 text-center" data-reveal>
                    Everything else on this page is a line you can check for yourself.
                    <a href="#card" class="es-score-lit inline-flex items-center gap-1 font-semibold transition-all hover:gap-2">
                        Back to the card
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Where the marks fall our way                              -->
    <!-- ============================================================ -->
    <section id="edge" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-score-field mb-6" data-reveal aria-hidden="true"><span>{{ $fieldNo('edge') }}</span></div>
                <p class="es-score-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The margin marks</p>
                <h2 class="es-balance es-score-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Where the marks <span class="es-score-accent">fall our way.</span>
                </h2>
                <p class="es-score-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    {{ $edgeTotal }} of the {{ $lineTotal }} lines above carry a diamond. Here are the {{ count($key_advantages) }} advantages over {{ $name }} that most often decide it, in plain language rather than table cells.
                </p>
            </div>

            <div class="mx-auto grid max-w-5xl grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">
                @foreach ($key_advantages as $advantage)
                    <div class="es-score-card es-score-hover flex flex-col p-7 transition-all duration-200 hover:-translate-y-1 hover:shadow-md" data-reveal="panel">
                        <span class="es-score-icon mb-5">
                            <x-marketing-icon :icon="$advantage['icon']" class="h-5 w-5" />
                        </span>
                        {{-- Each of these IS a marked line, so its title carries the same
                             margin diamond the table prints in the gutter, in the same
                             gutter slot. The legend above already spells the mark out. --}}
                        <h3 class="es-score-ink mb-2 flex items-start gap-2 text-lg font-bold">
                            <span class="es-score-gutter pt-2" aria-hidden="true"><span class="es-score-wedge"></span></span>
                            <span class="es-score-hover-title min-w-0 transition-colors">{{ $advantage['title'] }}</span>
                        </h3>
                        <p class="es-score-muted text-sm leading-relaxed">{{ $advantage['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Both columns: their side and ours, one hairline between    -->
    <!-- ============================================================ -->
    <section id="both" class="es-score-sep scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-score-field mb-6" data-reveal aria-hidden="true"><span>{{ $fieldNo('both') }}</span></div>
                <p class="es-score-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Both columns</p>
                <h2 class="es-balance es-score-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    Read both sides <span class="es-score-accent">before you sign.</span>
                </h2>
            </div>

            <div class="grid gap-10 lg:grid-cols-2 lg:gap-14">
                <div data-reveal>
                    <div class="mb-5">
                        <span class="es-score-plate es-score-plate-theirs">{{ $name }}</span>
                    </div>
                    <h3 class="es-score-ink mb-4 text-2xl font-bold">About {{ $name }}</h3>
                    <p class="es-score-muted mb-6">{{ $about }}</p>
                    <p class="es-score-tag mb-4">{{ $name }}'s strengths</p>
                    <ul class="space-y-3">
                        @foreach ($competitor_strengths as $strength)
                            <li class="flex items-start gap-3">
                                <span class="es-score-gutter pt-3" aria-hidden="true"><span class="es-score-bullet"></span></span>
                                <span class="es-score-muted">{{ $strength }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="es-score-facing" data-reveal style="--reveal-delay: 0.1s;">
                    <div class="mb-5">
                        <span class="es-score-plate es-score-plate-ours">Event Schedule</span>
                    </div>
                    <h3 class="es-score-ink mb-4 text-2xl font-bold">Why choose Event Schedule?</h3>
                    <p class="es-score-muted mb-6">{{ $whyChooseSummary }}</p>
                    <p class="es-score-tag mb-4">What you get</p>
                    <ul class="space-y-3">
                        @foreach ($whyChoosePoints as $point)
                            <li class="flex items-start gap-3">
                                <span class="es-score-mark es-score-mark-yes mt-1" aria-hidden="true"></span>
                                <span class="es-score-muted">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. How to switch                                             -->
    <!-- ============================================================ -->
    <section id="switch" class="es-score-sep scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-score-field mb-6" data-reveal aria-hidden="true"><span>{{ $fieldNo('switch') }}</span></div>
                <p class="es-score-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">A fresh card</p>
                <h2 class="es-balance es-score-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    How to switch in <span class="es-score-accent">{{ count($switchSteps) }} steps</span>
                </h2>
                <p class="es-score-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    @if (! empty($auto_import))
                        Get started in minutes. Bring your {{ $name }} events across with the importer above, or paste the details in and let the AI do the typing.
                    @else
                        Get started in minutes. There is nothing to migrate and no export file to wait for.
                    @endif
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3" data-reveal-group="100">
                @foreach ($switchSteps as $index => $step)
                    <div class="es-score-card flex flex-col p-7" data-reveal="panel">
                        <p class="es-score-num es-score-accent mb-3 text-2xl">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</p>
                        <h3 class="es-score-ink mb-2 text-lg font-bold">{{ $step['title'] }}</h3>
                        <p class="es-score-muted text-sm leading-relaxed">{{ $step['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @include('marketing.partials.pricing-nudge')

    <!-- ============================================================ -->
    <!-- 8. FAQ                                                       -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="es-score-sep scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="es-score-field mb-6" data-reveal aria-hidden="true"><span>{{ $fieldNo('faq') }}</span></div>
                <h2 class="es-balance es-score-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-score-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    Common questions about switching from {{ $name }}.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $item)
                    <details name="faq" class="es-score-card es-score-hover group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-score-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-score-num es-score-accent flex-none pt-1 text-sm" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-score-hover-title flex-1 transition-colors">{{ $item['q'] }}</span>
                            <svg aria-hidden="true" class="es-score-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-score-muted mt-4 leading-relaxed ps-9">{{ $item['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Also compare with                                         -->
    <!-- ============================================================ -->
    <section class="es-score-sep py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-score-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Also compare with</h2>

            <div class="mx-auto grid max-w-3xl grid-cols-1 gap-4 sm:grid-cols-3" data-reveal-group="80">
                @foreach ($cross_links as $link)
                    <a href="{{ route($link['route']) }}" class="es-score-card es-score-hover group flex flex-col p-5 transition-all duration-200 hover:-translate-y-1 hover:shadow-md" data-reveal>
                        <span class="es-score-tag mb-2">Event Schedule vs</span>
                        <span class="es-score-hover-title es-score-ink mb-4 text-lg font-bold transition-colors">{{ $link['name'] }}</span>
                        <span class="es-score-hover-arrow es-score-muted mt-auto inline-flex items-center gap-1 text-xs font-medium transition-colors">
                            Read the card
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>

            <div class="mt-8 text-center">
                <a href="{{ route('marketing.compare') }}" class="es-score-link inline-flex items-center font-medium hover:underline">
                    See every comparison
                    <svg aria-hidden="true" class="ml-1 h-4 w-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. Finale: sign the card                                    -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-score-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    <div class="es-score-field mb-6" aria-hidden="true"><span>{{ $fieldNo('claim') }}</span></div>
                    <p class="es-score-tag mb-4">Free forever, 0% platform fees</p>
                    <h2 class="es-balance es-score-band-ink mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight md:text-5xl">
                        That is the card. <span class="es-score-lit">Now sign it.</span>
                    </h2>

                    {{-- The foot of the card: the same rule, the same tally and the
                         same two numerals the head opened with, closing the object. --}}
                    <div class="mx-auto mb-8 max-w-sm">
                        <div class="es-score-rule mb-3"></div>
                        <div class="es-score-tally mb-3" aria-hidden="true">
                            @foreach ($tally as $tallyIndex => $won)
                                @if ($won)
                                    <span class="es-score-tick es-score-tick-on" style="--i: {{ $tallyIndex }};"></span>
                                @else
                                    <span class="es-score-tick" style="--i: {{ $tallyIndex }};"></span>
                                @endif
                            @endforeach
                        </div>
                        <p class="es-score-band-muted text-sm">
                            Final tally: <span class="es-score-num es-score-lit">{{ $edgeTotal }}</span> of <span class="es-score-num">{{ $lineTotal }}</span> lines to Event&nbsp;Schedule.
                        </p>
                    </div>

                    <p class="es-score-band-muted mx-auto mb-10 max-w-2xl text-lg sm:text-xl">
                        Create your free schedule today. No credit card required, and nothing taken from your ticket sales.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-400 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="es-score-band-muted shrink-0 select-none font-mono text-sm sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-score-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Get Started Free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="es-score-band-muted mt-6 text-sm">Open source, and yours to selfhost whenever you want it.</p>
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
                        <span class="es-score-tip pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <!-- Local confetti (no CDN) + motion engines -->
    <script {!! nonce_attr() !!} src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
