<x-marketing-layout :hp="true">
    <x-slot name="title">Contact Event Schedule | Support Email and Bug Reports</x-slot>
    <x-slot name="description">One support address, no portal to log into. Where to send a bug, a security report or an idea, and who to ask about a refund for a ticket you bought.</x-slot>
    <x-slot name="breadcrumbTitle">Contact</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "ContactPage",
        "name": "Contact Event Schedule",
        "description": "One support address for Event Schedule, and GitHub Issues for bugs and ideas. Refunds and questions about an event go to the organizer who sold the ticket.",
        "url": "{{ url()->current() }}",
        "mainEntity": {
            "@type": "Organization",
            "name": "Event Schedule",
            "email": "{{ config('app.support_email') }}",
            "url": "{{ config('app.url') }}"
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
           Contact "The Postcard" styles. A postcard is the least
           ceremonious way to reach somebody: one side to write on, one
           address, no envelope and no front desk. That is exactly the
           shape of support here, so the page IS a postcard - a divided
           back in the hero and a second card for what to write. (The
           sheet of stamps for the social links and the night-mail band
           for GitHub went in 2026-10: the routing table already sends
           each kind of message to its address, and the footer carries
           the six social links.)

           MATERIAL, NOT HUE, is the differentiator. The accent hue stays
           the page's existing brand blue (#1d4ed8 / #1e40af on light and
           cream, #7ab0ff in the dark bands) because /contact is chrome,
           not an audience page, and the hue wheel is spent. What is new
           is the card stock: #f7f1e3 with #1b1f26 ink, PINNED so it
           renders identically with .dark on and off. A real postcard does
           not change colour when the room does.

           Because the stock and the night band are fixed physical
           objects, three shared classes that carry their own .dark rules
           are overridden inside them: .grid-overlay, .animate-shimmer
           and .es-claim:focus-within. Nothing that flips by mode
           (.glass, .grid-pattern, .es-aurora, .es-spot, .es-glare,
           .es-bento hover shadow) may go inside .es-post-stock or
           .es-post-band. Verified with the verifier's --bands flag.

           RULE ORDER MATTERS: base, then .dark, then .es-post-band, then
           .es-post-stock. All four tiers are the same specificity, so the
           fixed objects only win by coming last.

           NEVER text-gray-500 here: the desk ground is tinted, so the
           page defines its own muted inks (#4b5563 on the light desk,
           6.43; #5b5648 on stock, 6.50; #9aa5b5 in the band, 7.64).

           BLADE RULE for this block: no @supports probes with a "#" hex
           inside the condition - it breaks compilation of every later
           parenthesized directive.
           ============================================================== */

        /* --- The desk: page ground and ink --- */
        .es-post-page { background-color: #e9edf3; color: #151a21; }
        .dark .es-post-page { background-color: #0a0d13; color: #e7ebf2; }

        /* Hairline separators. Page-local because `border-[rgba(...)]` is an
           arbitrary Tailwind value that is NOT in the built marketing CSS, and
           no build may be run here. */
        .es-post-hr { border-color: rgba(21, 26, 33, 0.1); }
        .dark .es-post-hr { border-color: rgba(231, 235, 242, 0.1); }

        .es-post-ink { color: #151a21; }
        .dark .es-post-ink { color: #e7ebf2; }
        .es-post-muted { color: #4b5563; }
        .dark .es-post-muted { color: #98a2b3; }
        .es-post-accent { color: #1e40af; }
        .dark .es-post-accent { color: #7ab0ff; }
        /* Always-lit blue, for use inside the fixed-dark band in both modes. */
        .es-post-lit { color: #7ab0ff; }

        .es-post-tag {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            color: #4b5563;
        }
        .dark .es-post-tag { color: #98a2b3; }

        /* --- Desk cards: these follow the colour mode --- */
        .es-post-card {
            background-color: #ffffff;
            border: 1px solid rgba(21, 26, 33, 0.12);
            border-radius: 1rem;
        }
        .dark .es-post-card {
            background-color: #14181f;
            border-color: rgba(231, 235, 242, 0.12);
        }

        /* --- The night-mail band: fixed dark in both modes --- */
        .es-post-band {
            background-color: #0b1019;
            background-image: radial-gradient(120% 100% at 50% 0%, #16202f 0%, #0e1520 55%, #080b11 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(231, 235, 242, 0.05);
        }
        .es-post-band .es-post-ink { color: #e8ecf3; }
        .es-post-band .es-post-muted { color: #9aa5b5; }
        .es-post-band .es-post-tag { color: #7ab0ff; }
        .es-post-band .es-post-card {
            background-color: #151b26;
            border-color: rgba(231, 235, 242, 0.12);
        }
        /* Shared classes that would otherwise flip with the colour mode. */
        .es-post-band .grid-overlay {
            background-image:
                linear-gradient(rgba(231, 235, 242, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(231, 235, 242, 0.05) 1px, transparent 1px);
        }
        .es-post-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-post-band .es-claim:focus-within {
            border-color: rgba(122, 176, 255, 0.75);
            box-shadow: 0 0 0 4px rgba(122, 176, 255, 0.24);
        }

        /* --- Card stock: the same paper in both colour modes --- */
        .es-post-stock {
            background-color: #f7f1e3;
            border: 1px solid rgba(27, 31, 38, 0.16);
            border-radius: 0.4rem;
            box-shadow: 0 22px 46px -22px rgba(12, 18, 30, 0.45);
            color: #1b1f26;
        }
        .es-post-stock .es-post-ink { color: #1b1f26; }
        .es-post-stock .es-post-muted { color: #5b5648; }
        .es-post-stock .es-post-accent { color: #1e40af; }
        .es-post-stock .es-post-tag { color: #5f5a4c; }
        .es-post-stock .es-post-hr { border-color: rgba(27, 31, 38, 0.16); }
        /* .es-post-stock .es-post-link lives at the end of the link block: the
           .dark rule is declared later and would otherwise win the tie. */

        /* The printed head of a postcard. */
        .es-post-head {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.42em;
            text-transform: uppercase;
            color: #5f5a4c;
            border-bottom: 1px solid rgba(27, 31, 38, 0.18);
        }

        /* The divided back: message on one side, address on the other. */
        .es-post-back { padding-top: 1.25rem; border-top: 1px solid rgba(27, 31, 38, 0.18); }
        @media (min-width: 768px) {
            .es-post-back {
                padding-top: 0;
                border-top: 0;
                border-inline-start: 1px solid rgba(27, 31, 38, 0.18);
                padding-inline-start: 1.75rem;
            }
        }

        /* Ruled message field. Texture, not illustration. */
        .es-post-lines {
            line-height: 1.7rem;
            background-image: repeating-linear-gradient(
                to bottom,
                transparent 0,
                transparent 1.7rem,
                rgba(27, 31, 38, 0.14) 1.7rem,
                rgba(27, 31, 38, 0.14) calc(1.7rem + 1px)
            );
        }
        .es-post-hand {
            font-family: ui-serif, Georgia, "Times New Roman", serif;
            font-style: italic;
            font-size: 0.98rem;
        }

        /* Address block. */
        .es-post-addr {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.74rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .es-post-addr-line {
            border-bottom: 1px solid rgba(27, 31, 38, 0.2);
            padding-bottom: 0.4rem;
            min-height: 1.55rem;
        }

        /* A stamp. The perforation is punched in the stock colour, so a
           stamp only ever sits on .es-post-stock. */
        .es-post-stamp {
            position: relative;
            padding: 0.6rem 0.55rem;
            background-color: #f2ead8;
            background-image:
                radial-gradient(circle, #f7f1e3 2.7px, rgba(247, 241, 227, 0) 2.9px),
                radial-gradient(circle, #f7f1e3 2.7px, rgba(247, 241, 227, 0) 2.9px),
                radial-gradient(circle, #f7f1e3 2.7px, rgba(247, 241, 227, 0) 2.9px),
                radial-gradient(circle, #f7f1e3 2.7px, rgba(247, 241, 227, 0) 2.9px);
            background-size: 8px 8px;
            background-position: 0 -4px, 0 calc(100% + 4px), -4px 0, calc(100% + 4px) 0;
            background-repeat: repeat-x, repeat-x, repeat-y, repeat-y;
            box-shadow: inset 0 0 0 1px rgba(27, 31, 38, 0.12);
            transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1);
        }
        a.es-post-stamp:hover { transform: rotate(-1.5deg) scale(1.03); }
        .es-post-stamp-value {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            color: #1e40af;
        }

        /* Franking bars: abstract strokes across the stamp, marching slowly. */
        .es-post-frank {
            display: block;
            width: 100%;
            height: 0.5rem;
            background-image: repeating-linear-gradient(
                115deg,
                rgba(30, 64, 175, 0.55) 0,
                rgba(30, 64, 175, 0.55) 2px,
                rgba(30, 64, 175, 0) 2px,
                rgba(30, 64, 175, 0) 7px
            );
            background-size: 200% 100%;
            animation: es-post-frank-drift 9s linear infinite;
        }
        @keyframes es-post-frank-drift {
            from { background-position: 0 0; }
            to { background-position: 40px 0; }
        }

        /* The postmark: two rings and small type, struck at an angle. */
        .es-post-mark {
            width: 6.4rem;
            height: 6.4rem;
            border-radius: 9999px;
            border: 1.5px solid rgba(27, 31, 38, 0.42);
            box-shadow: inset 0 0 0 4px rgba(27, 31, 38, 0.3);
            color: #4f4a3c;
            transform: rotate(-11deg);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        }
        .es-post-mark span {
            font-size: 0.5rem;
            font-weight: 700;
            letter-spacing: 0.13em;
            line-height: 1.4;
        }
        .es-post-band .es-post-mark {
            border-color: rgba(122, 176, 255, 0.5);
            box-shadow: inset 0 0 0 4px rgba(122, 176, 255, 0.32);
            color: #7ab0ff;
        }
        /* The finale strike, off to one side of the closing panel. Placement is
           page-local because `top-10` and `ltr:right-10` are NOT in the built
           marketing CSS, and no build may be run here; inset-inline-end also
           mirrors itself under RTL without a second utility. Held back to lg,
           where the panel is wide enough that the mark cannot reach the
           centred heading. */
        .es-post-strike { display: none; }
        @media (min-width: 1024px) {
            .es-post-strike {
                display: flex;
                position: absolute;
                top: 2.5rem;
                inset-inline-end: 2.75rem;
            }
        }

        .es-post-mark-rule {
            display: block;
            width: 2.6rem;
            height: 1px;
            background-color: rgba(27, 31, 38, 0.4);
        }
        .es-post-band .es-post-mark-rule { background-color: rgba(122, 176, 255, 0.5); }

        /* Section numeral, printed on a small square of stock. */
        .es-post-num {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.32rem 0.75rem;
            border-radius: 0.28rem;
            border: 1px solid rgba(21, 26, 33, 0.18);
            background-color: #f7f1e3;
            color: #1b1f26;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-weight: 800;
            letter-spacing: 0.05em;
        }
        .es-post-num::before {
            content: "";
            width: 3px;
            align-self: stretch;
            border-radius: 1px;
            background-color: #1e40af;
        }
        .dark .es-post-num {
            background-color: #14181f;
            border-color: rgba(231, 235, 242, 0.18);
            color: #e7ebf2;
        }
        .dark .es-post-num::before { background-color: #7ab0ff; }
        .es-post-band .es-post-num {
            background-color: #151b26;
            border-color: rgba(231, 235, 242, 0.18);
            color: #e8ecf3;
        }
        .es-post-band .es-post-num::before { background-color: #7ab0ff; }

        /* --- The routing table --- */
        .es-post-table { border-collapse: collapse; width: 100%; }
        .es-post-table th,
        .es-post-table td {
            padding: 0.9rem 0.75rem;
            vertical-align: top;
            text-align: start;
        }
        /* Column widths so a long destination label never breaks mid-word. */
        @media (min-width: 768px) {
            .es-post-table th:first-child { width: 24%; }
            .es-post-table th:nth-child(2),
            .es-post-table td:nth-child(2) { width: 23%; }
        }
        .es-post-table thead th {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #4b5563;
            border-bottom: 1px solid rgba(21, 26, 33, 0.16);
        }
        .dark .es-post-table thead th {
            color: #98a2b3;
            border-bottom-color: rgba(231, 235, 242, 0.16);
        }
        .es-post-table tbody tr + tr th,
        .es-post-table tbody tr + tr td { border-top: 1px solid rgba(21, 26, 33, 0.1); }
        .dark .es-post-table tbody tr + tr th,
        .dark .es-post-table tbody tr + tr td { border-top-color: rgba(231, 235, 242, 0.1); }

        /* --- Links, buttons, hover states --- */
        .es-post-link { color: #1e40af; }
        .es-post-link:hover { color: #151a21; }
        .dark .es-post-link { color: #7ab0ff; }
        .dark .es-post-link:hover { color: #e7ebf2; }
        /* A link printed on card stock stays ink-blue in both modes: same
           specificity as the .dark rule above, so it must come after it. */
        .es-post-stock .es-post-link { color: #1e40af; }
        .es-post-stock .es-post-link:hover { color: #1b1f26; }

        /* Mode-independent on purpose: white on #1d4ed8 measures 6.70, and a
           button that keeps one colour reads as the same object all page. */
        .es-post-btn {
            background-color: #1d4ed8;
            color: #ffffff;
            box-shadow: 0 0 0 1px rgba(122, 176, 255, 0.45), 0 18px 36px -16px rgba(29, 78, 216, 0.6);
        }
        .es-post-btn:hover { background-color: #1e40af; }

        /* Dot-nav tooltip. Page-local because dark:bg-[#151a21] is not in the
           built bundle. */
        .es-post-tip {
            background-color: #ffffff;
            border-color: rgba(21, 26, 33, 0.14);
            color: #374151;
        }
        .dark .es-post-tip {
            background-color: #151a21;
            border-color: rgba(231, 235, 242, 0.12);
            color: #d1d5db;
        }

        .es-post-hover:hover { border-color: rgba(30, 64, 175, 0.45); }
        .dark .es-post-hover:hover { border-color: rgba(122, 176, 255, 0.45); }
        .es-post-hover:hover .es-post-hover-title{ color: #1e40af; }
        .dark .es-post-hover:hover .es-post-hover-title{ color: #7ab0ff; }

        /* --- Focus rings. No border-radius: an outline already follows the
               element's own shape, and setting one changes it on focus. --- */
        #es-post-page a:focus-visible,
        #es-post-page summary:focus-visible,
        #es-post-page input:focus-visible,
        #es-post-page button:focus-visible {
            outline: 2px solid #1e40af;
            outline-offset: 3px;
        }
        .dark #es-post-page a:focus-visible,
        .dark #es-post-page summary:focus-visible,
        .dark #es-post-page input:focus-visible,
        .dark #es-post-page button:focus-visible { outline-color: #7ab0ff; }
        .es-post-band a:focus-visible,
        .es-post-band summary:focus-visible,
        .es-post-band input:focus-visible { outline-color: #7ab0ff !important; }
        .es-post-stock a:focus-visible { outline-color: #1e40af !important; }

        @media (prefers-reduced-motion: reduce) {
            .es-post-frank { animation: none !important; }
            a.es-post-stamp:hover { transform: none; }
        }
    </style>

    @php
        $supportEmail = config('app.support_email');
        $repoUrl = 'https://github.com/eventschedule/eventschedule';

        // Where a message should go, and why there. Every destination is a
        // surface that already exists: the user guide, the FAQ page, the
        // public repo's Issues tab, and the support address.
        $routes = [
            [
                'what' => 'A how-to question',
                'label' => 'The user guide',
                'href' => marketing_url('/docs'),
                'external' => false,
                'why' => 'Searchable, and it covers schedules, events, tickets, newsletters, analytics and the API. Most how-to answers are already written down.',
            ],
            [
                'what' => 'A question about plans or fees',
                'label' => 'The FAQ',
                'href' => marketing_url('/faq'),
                'external' => false,
                'why' => 'What is on the free plan, what Pro adds at '.plan_price($proMonthly).' a month, and why there are zero platform fees on ticket sales.',
            ],
            [
                // No link: the organizer is reached on their own pages, not ours. Ticket money
                // settles into the organizer's own Stripe or PayPal account (Connect on hosted), so a
                // refund is theirs to issue, through SaleRefundService from their Sales page.
                'what' => 'A refund, or a question about an event',
                'label' => 'The organizer',
                'href' => null,
                'external' => false,
                'why' => 'Ticket money goes straight to the organizer\'s own account, not through us, so the refund has to come from them: they can send a Stripe or PayPal sale back in full or in part from their Sales page. They are also the ones who can answer for the event itself.',
            ],
            [
                'what' => 'Something is broken',
                'label' => 'GitHub Issues',
                'href' => 'https://github.com/eventschedule/eventschedule/issues',
                'external' => true,
                'why' => 'Issues are public, so you can see whether somebody has already hit it, add what you are seeing, and follow the fix.',
            ],
            [
                'what' => 'A security vulnerability',
                'label' => $supportEmail,
                'href' => 'mailto:'.$supportEmail.'?subject=Security%20report',
                'external' => false,
                'why' => 'Please do not open a public issue for this one. Write to the address instead, with the steps to reproduce it, and give us a chance to ship a fix before it is described in the open.',
            ],
            [
                'what' => 'An idea, or a feature you want',
                'label' => 'GitHub Issues',
                'href' => 'https://github.com/eventschedule/eventschedule/issues',
                'external' => true,
                'why' => 'Ideas are worth arguing out in the open, next to everyone else who wants a version of the same thing.',
            ],
            [
                // No link: the answer is on the page itself. role/show-guest-unclaimed.blade.php
                // carries "Claim this page" and "This is not me" (RoleController::claimNotMeSubmit).
                'what' => 'A page in your name that you did not make',
                'label' => 'The page itself',
                'href' => null,
                'external' => false,
                'why' => 'A page made for an act or venue that an organizer listed carries two buttons. Signed in with the address on that page, Claim this page makes it yours and This is not me takes it down; from any other account, This is not me is recorded for review.',
            ],
            [
                'what' => 'Anything private, or anything else',
                'label' => $supportEmail,
                'href' => 'mailto:'.$supportEmail,
                'external' => false,
                'why' => 'Your account, billing, or something you would rather not post in public. If none of the others fit, this address takes it.',
            ],
        ];

        // The heading counts distinct destinations, so the support address listed twice counts
        // once. Derived rather than typed: the number drifted once already when a row was added.
        $addressCount = count(array_unique(array_column($routes, 'label')));

        // What actually helps us answer. None of this is a form field: it is
        // the four things that turn a report into a reproduction.
        $onCard = [
            'Your schedule address, which on the hosted site looks like your-name.eventschedule.com.',
            'Hosted or selfhosted. If you selfhost, the version you are running, which is shown in the admin portal.',
            'What you expected, and what happened instead. A screenshot beats a paragraph.',
            'A link to the event or the page it happened on.',
        ];

        $faqs = [
            [
                'q' => 'How do I contact Event Schedule?',
                'a' => 'Email '.$supportEmail.'. For anything technical you can also open an issue on GitHub, which is public. There is no support portal to log into and no ticket number to quote.',
            ],
            [
                'q' => 'Where do I report a bug?',
                'a' => 'GitHub Issues. Reporting it there means other people can see it, add what they are seeing, and follow the fix. Include your schedule address, whether you are on the hosted site or selfhosted, and what you expected to happen instead.',
            ],
            [
                'q' => 'How do I request a feature?',
                'a' => 'Open a GitHub issue. Feature requests are worth putting in the open, because somebody else usually wants a version of the same thing. Email works too if you would rather not post publicly.',
            ],
            [
                'q' => 'Is there a phone number?',
                'a' => 'No. Contact is by email, or on GitHub for anything technical. Written requests are easier to answer accurately, especially when a link or a screenshot is what settles the question.',
            ],
            [
                'q' => 'Do I have to talk to sales before I can sign up?',
                'a' => 'No. Pricing is published on the pricing page, the free plan needs no card, and you can create a schedule and start adding events without speaking to anybody. Free registration and door scanning come with it; Pro at '.plan_price($proMonthly).' a month is what lets you charge for a ticket. Event Schedule charges zero platform fees on ticket sales either way.',
            ],
            [
                'q' => 'Can Event Schedule refund my ticket?',
                'a' => 'No, because the money never passed through us. Ticket sales settle into the organizer\'s own Stripe or PayPal account, so ask the schedule you bought from: they can refund a Stripe or PayPal sale in full or in part from their Sales page, and it goes back through the same provider. A ticket paid any other way, in cash or through a payment link for example, is settled between you and them. Event Schedule does not email you when a refund goes through, so their reply is your confirmation.',
            ],
            [
                'q' => 'Someone made a page with my name on it. Who do I tell?',
                'a' => 'Usually nobody: the page itself has the buttons. When an organizer lists a performer or venue who is not on Event Schedule, the app makes a page to credit the date to, and it stays out of search engines until it is claimed. Sign in with the email address on it and Claim this page makes it yours, or This is not me takes it down at once. From any other account, This is not me is recorded for review. If the page carries no contact details at all, ask the schedule that listed you to send an invitation, or write to '.$supportEmail.'.',
            ],
            [
                'q' => 'I selfhost. Where do I get help?',
                'a' => 'The selfhost section of the user guide covers installation, email, Stripe and PayPal, calendar sync, AI, the admin tools and the rest of the environment settings. For anything the guide does not answer, GitHub Issues is the right address, and it helps to say which version you are running.',
            ],
        ];

        $relatedPages = [
            ['/faq', 'FAQ', 'The short answers, in one page.'],
            ['/pricing', 'Pricing', 'Free forever, Pro at '.plan_price($proMonthly).' a month.'],
            ['/docs', 'User guide', 'Setup, events, tickets and the API.'],
            ['/about', 'About', 'Who builds Event Schedule, and why.'],
        ];

        $dotSections = [
            ['top', 'The card'],
            ['where', 'Where to post it'],
            ['card', 'What to write'],
            ['faq', 'Questions'],
            ['claim', 'Just start'],
        ];
    @endphp

    <div id="es-post-page" class="es-post-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the divided back of a postcard                      -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(80svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 28% 68%, rgba(37, 99, 235, 0.26), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 72% 32%, rgba(14, 165, 233, 0.2), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div>
                    <h1 class="es-balance es-post-ink mb-7 text-[2.5rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <x-marketing.hero-eyebrow class="es-fade-up es-d-1 glass inline-flex items-center gap-3 rounded-full px-5 py-2.5 mb-8">
                            <svg aria-hidden="true" class="es-post-accent h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span class="es-post-muted text-sm font-medium tracking-wide">Contact Event Schedule</span>
                        </x-marketing.hero-eyebrow>
                        <span class="es-mask"><span class="es-mask-line">Write to us.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="es-post-accent">Postcard rules apply.</span></span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-post-muted mb-9 max-w-xl text-lg sm:text-xl">
                        Short, direct, one address. Email us or file an issue. Event Schedule is open source, so everything except the email happens in public.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row sm:flex-wrap">
                        <a href="#where" class="glass group inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-2xl px-7 py-4 text-base font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg sm:text-lg">
                            Where should it go?
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                        <a href="mailto:{{ $supportEmail }}" class="es-post-btn group inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-2xl px-7 py-4 text-base font-semibold transition-all duration-200 hover:-translate-y-0.5 sm:text-lg">
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            {{ $supportEmail }}
                        </a>
                    </div>
                </div>

                <!-- The postcard. Same stock with .dark on or off. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-post-stock noise relative overflow-hidden p-5 sm:p-7">
                        <div class="es-post-head relative z-10 mb-5 flex items-baseline justify-between gap-3 pb-2">
                            <span>Post card</span>
                            <span>Event Schedule</span>
                        </div>

                        <div class="relative z-10 grid gap-6 md:grid-cols-2">
                            <!-- Message side -->
                            <div>
                                <p class="es-post-tag mb-2">Message</p>
                                <div class="es-post-lines es-post-hand es-post-ink">
                                    Hi. Two things: a question about ticket types, and I think the embed is off on mobile. Same person, one card.
                                </div>
                            </div>

                            <!-- Address side -->
                            <div class="es-post-back">
                                <div class="mb-4 flex items-start justify-between gap-3">
                                    <div class="es-post-mark flex flex-col items-center justify-center gap-1 text-center" aria-hidden="true">
                                        <span>EVENT</span>
                                        <span class="es-post-mark-rule"></span>
                                        <span>SCHEDULE</span>
                                        <span class="es-post-mark-rule"></span>
                                        <span>OPEN&nbsp;SOURCE</span>
                                    </div>
                                    <div class="es-post-stamp flex w-20 flex-col items-center gap-1.5 text-center" aria-hidden="true">
                                        <span class="es-post-stamp-value">FREE</span>
                                        <svg aria-hidden="true" class="es-post-accent h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span class="es-post-frank"></span>
                                    </div>
                                </div>

                                <p class="es-post-tag mb-2">Addressed to</p>
                                <div class="es-post-addr es-post-ink space-y-2.5">
                                    <div class="es-post-addr-line font-bold">Event Schedule</div>
                                    <div class="es-post-addr-line">
                                        <a href="mailto:{{ $supportEmail }}" class="es-post-link font-semibold normal-case tracking-normal hover:underline">{{ $supportEmail }}</a>
                                    </div>
                                    <div class="es-post-addr-line"></div>
                                </div>
                            </div>
                        </div>

                        <p class="es-post-muted relative z-10 mt-6 es-post-hr border-t pt-4 text-xs">
                            One address, and it is not a queue number. Nothing to log into, nothing to route, no reference to quote back at us.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. Where to post it: the routing table                       -->
    <!-- ============================================================ -->
    <section id="where" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-post-num mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                <p class="es-post-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Routing</p>
                <h2 class="es-balance es-post-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    {{ ucfirst(\Illuminate\Support\Number::spell($addressCount)) }} addresses. <span class="es-post-accent">Pick the nearest one.</span>
                </h2>
                <p class="es-post-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Two of them answer you faster than we can, because the answer is already written. Two more are not us at all: the organizer who sold you a ticket, and a page that was made in your name.
                </p>
            </div>

            <div class="es-post-card overflow-x-auto p-4 sm:p-7" data-reveal="panel">
                <table class="es-post-table">
                    <caption class="sr-only">Where to send each kind of message, and why that address</caption>
                    <thead>
                        <tr>
                            <th scope="col">What you have</th>
                            <th scope="col">Where it goes</th>
                            <th scope="col" class="hidden md:table-cell">Why there</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($routes as $route)
                            <tr>
                                <th scope="row" class="es-post-ink text-sm font-bold">{{ $route['what'] }}</th>
                                <td class="text-sm">
                                    @if ($route['href'])
                                    <a href="{{ $route['href'] }}"
                                        @if ($route['external']) target="_blank" rel="noopener noreferrer" @endif
                                        class="es-post-link inline-flex items-center gap-1.5 font-semibold hover:underline @if (str_contains($route['label'], '@')) break-all @endif">
                                        {{ $route['label'] }}
                                        @if ($route['external'])
                                            <svg aria-hidden="true" class="h-3.5 w-3.5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                        @endif
                                    </a>
                                    @else
                                    {{-- A destination that is not ours to link to: the organizer, or the page itself. --}}
                                    <span class="es-post-ink font-semibold">{{ $route['label'] }}</span>
                                    @endif
                                    <span class="es-post-muted mt-1.5 block text-xs md:hidden">{{ $route['why'] }}</span>
                                </td>
                                <td class="es-post-muted hidden text-sm md:table-cell">{{ $route['why'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="es-post-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                GitHub is the public half of this. The email address is the private half, and it is the right one for anything to do with your account.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. What to write on the card                                 -->
    <!-- ============================================================ -->
    <section id="card" class="scroll-mt-24 es-post-hr border-y py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-post-num mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                <p class="es-post-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Before you send</p>
                <h2 class="es-balance es-post-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    The back of a card <span class="es-post-accent">is not very big.</span>
                </h2>
                <p class="es-post-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Four lines turn a report into something we can reproduce. Everything else is optional.
                </p>
            </div>

            <div class="grid items-start gap-6 lg:grid-cols-[1.1fr_1fr]">
                <!-- The second postcard: same stock, ruled lines, four lines of it -->
                <div data-reveal="panel">
                    <div class="es-post-stock noise relative overflow-hidden p-5 sm:p-7">
                        <div class="es-post-head relative z-10 mb-5 flex items-baseline justify-between gap-3 pb-2">
                            <span>Worth writing down</span>
                            <span>04 lines</span>
                        </div>
                        <ol class="es-post-lines relative z-10">
                            @foreach ($onCard as $cardIndex => $cardLine)
                                <li class="flex items-start gap-3">
                                    <span class="es-post-accent flex-none font-mono text-xs font-bold" aria-hidden="true">{{ str_pad($cardIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                    <span class="es-post-ink">{{ $cardLine }}</span>
                                </li>
                            @endforeach
                        </ol>
                        <p class="es-post-muted relative z-10 mt-6 es-post-hr border-t pt-4 text-xs">
                            Selfhosted and hosted are genuinely different code paths in places, so which one you are on is usually the first thing worth knowing.
                        </p>
                    </div>
                </div>

                <!-- What you do not need -->
                <div class="es-post-card flex h-full flex-col p-6 sm:p-7" data-reveal="panel">
                    <p class="es-post-tag mb-3">And what you do not need</p>
                    <h3 class="es-post-ink mb-4 text-xl font-bold">No ticket number. No call.</h3>
                    <ul class="es-post-muted space-y-3 text-sm" data-reveal-group="70">
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-post-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>There is no support portal and no case reference, so there is nothing to look up before you write.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-post-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>No demo call stands between you and a working calendar. Pricing is published and sign-up is self-serve.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-post-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>You do not need an account to write to us, or to read every issue in the repo.</span>
                        </li>
                    </ul>
                    <div class="mt-auto pt-6">
                        <a href="{{ marketing_url('/pricing') }}" class="es-post-link inline-flex items-center gap-1.5 text-sm font-semibold hover:underline">
                            See what each plan costs
                            <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. FAQ                                                       -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="scroll-mt-24 es-post-hr border-t py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="es-post-num mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                <h2 class="es-balance es-post-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-post-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    About getting in touch, before you do.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-post-hover es-post-card group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-post-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-post-accent flex-none font-mono text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-post-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-post-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-post-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <x-marketing.related-pages />

    <!-- ============================================================ -->
    <!-- 5. Finale                                                    -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-post-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    {{-- Third strike of the postmark, and the last thing on the page.
                         The hero card is addressed to us; this one is addressed to you. --}}
                    <div class="es-post-mark es-post-strike flex-col items-center justify-center gap-1 text-center">
                        <span>FREE</span>
                        <span class="es-post-mark-rule"></span>
                        <span>PLAN</span>
                        <span class="es-post-mark-rule"></span>
                        <span>NO&nbsp;CARD</span>
                    </div>
                </div>

                <div class="relative z-10">
                    <p class="es-post-tag mb-4">Nothing to ask</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Ready to <span class="es-post-lit">get started?</span>
                    </h2>
                    <p class="es-post-muted mx-auto mb-8 max-w-2xl text-lg sm:text-xl">
                        Create your free schedule today. No credit card required.
                    </p>

                    <p class="es-post-tag mb-3">Addressed to you</p>
                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-4 sm:px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-base font-semibold text-white placeholder-gray-400 focus:outline-none focus:ring-0">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-post-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Start for free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="es-post-muted mt-6 text-sm">
                        Or write first. The address is
                        <a href="mailto:{{ $supportEmail }}" class="es-post-lit font-semibold hover:underline">{{ $supportEmail }}</a>
                    </p>
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
                        <span class="es-post-tip pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
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
