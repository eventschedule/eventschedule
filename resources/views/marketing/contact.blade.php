<x-marketing-layout>
    <x-slot name="title">Contact Event Schedule | Get in Touch</x-slot>
    <x-slot name="description">Contact Event Schedule by email for support, report issues on GitHub, or connect with us on social media. We're here to help with setup, features, and more.</x-slot>
    <x-slot name="breadcrumbTitle">Contact</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "ContactPage",
        "name": "Contact Event Schedule",
        "description": "Get in touch with Event Schedule. Reach out via email, social media, or report issues on GitHub.",
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
           back in the hero, a second card for what to write, a sheet of
           stamps for the social links, and a night-mail band for the
           part that happens in public on GitHub.

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

        /* Same reason: 2xl:mx-auto / 2xl:max-w-[100rem] are not in the bundle. */
        @media (min-width: 1536px) {
            .es-post-wide { margin-inline: auto; max-width: 100rem; }
        }

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
        /* The shared GitHub star badge (marketing/partials/github-star-badge)
           carries bg-gray-800/dark:bg-gray-700 on its two halves, so it changes
           shade with the colour mode. Inside a band that is dark in BOTH modes
           that reads as the object changing, so pin both halves structurally.
           Targeting the elements rather than the utility class names keeps this
           page from asserting anything about a file it does not own. */
        .es-post-star a > span:first-child { background-color: #252526; }
        .es-post-star a > span:last-child { background-color: #2d2d30; }

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
        .es-post-stamp-name {
            font-size: 0.68rem;
            font-weight: 600;
            line-height: 1.15;
        }
        /* The sheet. Six stamps, so the block is complete at every breakpoint:
           6/2, 6/3 and 6/6 all divide, which a sheet of five never did (a
           two-column sheet of five leaves the bottom-right corner torn off).
           Page-local because md:grid-cols-6 is not in the built marketing CSS
           and no build may be run here. */
        .es-post-sheet {
            display: grid;
            gap: 0.75rem;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        @media (min-width: 640px) {
            .es-post-sheet { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
        @media (min-width: 768px) {
            .es-post-sheet { grid-template-columns: repeat(6, minmax(0, 1fr)); }
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
        .es-post-hover:hover .es-post-hover-title,
        .es-post-hover:hover .es-post-hover-arrow { color: #1e40af; }
        .dark .es-post-hover:hover .es-post-hover-title,
        .dark .es-post-hover:hover .es-post-hover-arrow { color: #7ab0ff; }

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
        // public repo's Issues and Discussions tabs, and the support address.
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
                'why' => 'What is on the free plan, what Pro adds at $5 a month, and why there are zero platform fees on ticket sales.',
            ],
            [
                'what' => 'Something is broken',
                'label' => 'GitHub Issues',
                'href' => 'https://github.com/eventschedule/eventschedule/issues',
                'external' => true,
                'why' => 'Issues are public, so you can see whether somebody has already hit it, add what you are seeing, and follow the fix.',
            ],
            [
                'what' => 'An idea, or a feature you want',
                'label' => 'GitHub Discussions',
                'href' => 'https://github.com/eventschedule/eventschedule/discussions',
                'external' => true,
                'why' => 'Ideas are worth arguing out in the open, next to everyone else who wants a version of the same thing.',
            ],
            [
                'what' => 'Anything private, or anything else',
                'label' => $supportEmail,
                'href' => 'mailto:'.$supportEmail,
                'external' => false,
                'why' => 'Your account, billing, or something you would rather not post in public. If none of the others fit, this address takes it.',
            ],
        ];

        // What actually helps us answer. None of this is a form field: it is
        // the four things that turn a report into a reproduction.
        $onCard = [
            'Your schedule address, which on the hosted site looks like your-name.eventschedule.com.',
            'Hosted or selfhosted. If you selfhost, the version you are running, which is shown in the admin portal.',
            'What you expected, and what happened instead. A screenshot beats a paragraph.',
            'A link to the event or the page it happened on.',
        ];

        $socials = [
            [
                'name' => 'Facebook',
                'href' => 'https://www.facebook.com/appeventschedule',
                'path' => '<path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />',
            ],
            [
                'name' => 'Instagram',
                'href' => 'https://www.instagram.com/eventschedule/',
                'path' => '<path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" />',
            ],
            [
                'name' => 'YouTube',
                'href' => 'https://youtube.com/@EventSchedule',
                'path' => '<path fill-rule="evenodd" d="M19.812 5.418c.861.23 1.538.907 1.768 1.768C21.998 8.746 22 12 22 12s0 3.255-.418 4.814a2.504 2.504 0 0 1-1.768 1.768c-1.56.419-7.814.419-7.814.419s-6.255 0-7.814-.419a2.505 2.505 0 0 1-1.768-1.768C2 15.255 2 12 2 12s0-3.255.417-4.814a2.507 2.507 0 0 1 1.768-1.768C5.744 5 11.998 5 11.998 5s6.255 0 7.814.418ZM15.194 12 10 15V9l5.194 3Z" clip-rule="evenodd" />',
            ],
            [
                'name' => 'X (Twitter)',
                'href' => 'https://x.com/ScheduleEvent',
                'path' => '<path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />',
            ],
            [
                'name' => 'LinkedIn',
                'href' => 'https://www.linkedin.com/company/eventschedule/',
                'path' => '<path fill-rule="evenodd" d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" clip-rule="evenodd" />',
            ],
            // The sixth stamp is the repository, which makes the sheet complete
            // at 2, 3 and 6 columns and puts the page's own argument on it: the
            // release notes and the whole history are published there.
            [
                'name' => 'GitHub',
                'href' => $repoUrl,
                'path' => '<path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z" />',
            ],
        ];

        $faqs = [
            [
                'q' => 'How do I contact Event Schedule?',
                'a' => 'Email '.$supportEmail.'. For anything technical you can also open an issue or start a discussion on GitHub, both of which are public. There is no support portal to log into and no ticket number to quote.',
            ],
            [
                'q' => 'Where do I report a bug?',
                'a' => 'GitHub Issues. Reporting it there means other people can see it, add what they are seeing, and follow the fix. Include your schedule address, whether you are on the hosted site or selfhosted, and what you expected to happen instead.',
            ],
            [
                'q' => 'How do I request a feature?',
                'a' => 'Start a GitHub Discussion. Feature requests are worth putting in the open, because somebody else usually wants a version of the same thing. Email works too if you would rather not post publicly.',
            ],
            [
                'q' => 'Is there a phone number?',
                'a' => 'No. Contact is by email, or on GitHub for anything technical. Written requests are easier to answer accurately, especially when a link or a screenshot is what settles the question.',
            ],
            [
                'q' => 'Do I have to talk to sales before I can sign up?',
                'a' => 'No. Pricing is published on the pricing page, the free plan needs no card, and you can create a schedule and start adding events without speaking to anybody. Ticketing is on the Pro plan at $5 a month, and Event Schedule charges zero platform fees on ticket sales.',
            ],
            [
                'q' => 'I selfhost. Where do I get help?',
                'a' => 'The selfhost section of the user guide covers installation, email, Stripe, calendar sync, AI and the rest of the environment settings. For anything the guide does not answer, GitHub Issues is the right address, and it helps to say which version you are running.',
            ],
        ];

        $relatedPages = [
            ['/faq', 'FAQ', 'The short answers, in one page.'],
            ['/pricing', 'Pricing', 'Free forever, Pro at $5 a month.'],
            ['/docs', 'User guide', 'Setup, events, tickets and the API.'],
            ['/about', 'About', 'Who builds Event Schedule, and why.'],
        ];

        $dotSections = [
            ['top', 'The card'],
            ['where', 'Where to post it'],
            ['card', 'What to write'],
            ['open', 'Open source'],
            ['answers', 'Answers'],
            ['follow', 'Follow us'],
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
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="es-post-accent h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span class="es-post-muted text-sm font-medium tracking-wide">Contact</span>
                    </div>

                    <h1 class="es-balance es-post-ink mb-7 text-[2.5rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">Write to us.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="es-post-accent">Postcard rules apply.</span></span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-post-muted mb-9 max-w-xl text-lg sm:text-xl">
                        Short, direct, one address. Email us, start a discussion, or file an issue. Event Schedule is open source, so everything except the email happens in public.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="mailto:{{ $supportEmail }}" class="es-post-btn group inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-2xl px-7 py-4 text-base font-semibold transition-all duration-200 hover:-translate-y-0.5 sm:text-lg">
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            {{ $supportEmail }}
                        </a>
                        <a href="#where" class="glass group inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-2xl px-7 py-4 text-base font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg sm:text-lg">
                            Where should it go?
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
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
                                        <svg class="es-post-accent h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
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
                    Five addresses. <span class="es-post-accent">Pick the nearest one.</span>
                </h2>
                <p class="es-post-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Two of them answer you faster than we can, because the answer is already written.
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
                                    <a href="{{ $route['href'] }}"
                                        @if ($route['external']) target="_blank" rel="noopener noreferrer" @endif
                                        class="es-post-link inline-flex items-center gap-1.5 font-semibold hover:underline @if (str_contains($route['label'], '@')) break-all @endif">
                                        {{ $route['label'] }}
                                        @if ($route['external'])
                                            <svg aria-hidden="true" class="h-3.5 w-3.5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                        @endif
                                    </a>
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
                            <span>You do not need an account to write to us, or to read every issue and discussion in the repo.</span>
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
    <!-- 4. Open source: the night mail (fixed-dark band)             -->
    <!-- ============================================================ -->
    <section id="open" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-post-band noise relative overflow-hidden es-post-wide rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-post-num mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                    <p class="es-post-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Open source</p>
                    <h2 class="es-balance es-post-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        The code is <span class="es-post-lit">already public.</span>
                    </h2>
                    <p class="es-post-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Which means so is the mail about it. Every issue, every discussion, every commit that closes one.
                    </p>

                    <div class="mt-8 flex flex-col items-center gap-6" data-reveal>
                        <div class="es-post-mark flex flex-col items-center justify-center gap-1 text-center" aria-hidden="true">
                            <span>GITHUB</span>
                            <span class="es-post-mark-rule"></span>
                            <span>RECEIVED</span>
                            <span class="es-post-mark-rule"></span>
                            <span>IN&nbsp;PUBLIC</span>
                        </div>

                        <div class="es-post-star">
                            @include('marketing.partials.github-star-badge')
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="100">
                    <div class="es-post-card flex h-full flex-col p-6" data-reveal="panel">
                        <p class="es-post-tag mb-3">Discussions</p>
                        <h3 class="es-post-ink mb-2 text-lg font-bold">Ask, or propose</h3>
                        <p class="es-post-muted text-sm">Have a question or an idea? Start a conversation. It is the easiest way to reach both us and the people already using the thing you are asking about.</p>
                        <a href="https://github.com/eventschedule/eventschedule/discussions" target="_blank" rel="noopener noreferrer" class="es-post-lit mt-auto inline-flex items-center gap-1.5 pt-5 text-sm font-semibold hover:underline">
                            GitHub Discussions
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                        </a>
                    </div>
                    <div class="es-post-card flex h-full flex-col p-6" data-reveal="panel">
                        <p class="es-post-tag mb-3">Issues</p>
                        <h3 class="es-post-ink mb-2 text-lg font-bold">Report a bug</h3>
                        <p class="es-post-muted text-sm">Found something broken? Open an issue and we will look into it. Because the tracker is public, you can watch the fix land instead of wondering.</p>
                        <a href="https://github.com/eventschedule/eventschedule/issues" target="_blank" rel="noopener noreferrer" class="es-post-lit mt-auto inline-flex items-center gap-1.5 pt-5 text-sm font-semibold hover:underline">
                            GitHub Issues
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                        </a>
                    </div>
                    <div class="es-post-card flex h-full flex-col p-6" data-reveal="panel">
                        <p class="es-post-tag mb-3">Selfhost</p>
                        <h3 class="es-post-ink mb-2 text-lg font-bold">Run it yourself</h3>
                        <p class="es-post-muted text-sm">Prefer it on your own server? The installation guide walks through it, and selfhost questions are welcome on GitHub alongside everything else.</p>
                        <a href="{{ marketing_url('/docs/selfhost/installation') }}" class="es-post-lit mt-auto inline-flex items-center gap-1.5 pt-5 text-sm font-semibold hover:underline">
                            Installation guide
                            <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    </div>
                </div>

                <p class="es-post-muted mt-10 text-center" data-reveal>
                    Reading the source is a legitimate way to get an answer.
                    <a href="{{ $repoUrl }}" target="_blank" rel="noopener noreferrer" class="es-post-lit inline-flex items-center gap-1 font-semibold transition-all hover:gap-2">
                        Open the repository
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. You can also find answers here                            -->
    <!-- ============================================================ -->
    <section id="answers" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-post-num mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                <p class="es-post-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Helpful resources</p>
                <h2 class="es-balance es-post-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    You can also find answers here
                </h2>
                <p class="es-post-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    The documentation and the FAQ already answer many common questions, and neither of them has to wait for us to read anything.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2" data-reveal-group="90">
                <div class="es-bento group relative" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner es-post-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10 flex h-full flex-col">
                            <svg aria-hidden="true" class="es-post-accent mb-4 h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <h3 class="es-post-ink mb-2 text-xl font-bold">Documentation</h3>
                            <p class="es-post-muted mb-5">The user guide, with a search box. Getting started, creating schedules and events, tickets, newsletters, analytics, the API, and a whole selfhost section.</p>
                            <a href="{{ marketing_url('/docs') }}" class="es-post-link mt-auto inline-flex items-center gap-1.5 font-semibold hover:underline">
                                Read the guide
                                <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </a>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <div class="es-bento group relative" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner es-post-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10 flex h-full flex-col">
                            <svg aria-hidden="true" class="es-post-accent mb-4 h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="es-post-ink mb-2 text-xl font-bold">FAQ</h3>
                            <p class="es-post-muted mb-5">The short answers, in one page: what the free plan includes, what Pro adds, why there are zero platform fees on ticket sales, and how selfhosting differs.</p>
                            <a href="{{ marketing_url('/faq') }}" class="es-post-link mt-auto inline-flex items-center gap-1.5 font-semibold hover:underline">
                                Read the FAQ
                                <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </a>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Follow us: a sheet of stamps                              -->
    <!-- ============================================================ -->
    <section id="follow" class="scroll-mt-24 es-post-hr border-t py-20 lg:py-28">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-10 max-w-2xl text-center">
                <div class="es-post-num mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                <h2 class="es-balance es-post-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Follow us
                </h2>
                <p class="es-post-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    Stay up to date with the latest news, features, and updates.
                </p>
            </div>

            <div data-reveal="panel">
                <div class="es-post-stock noise relative overflow-hidden p-5 sm:p-7">
                    <div class="es-post-head relative z-10 mb-6 flex items-baseline justify-between gap-3 pb-2">
                        <span>Sheet of six</span>
                        <span>Perforated</span>
                    </div>
                    <div class="es-post-sheet relative z-10">
                        @foreach ($socials as $social)
                            <a href="{{ $social['href'] }}" target="_blank" rel="noopener noreferrer"
                                class="es-post-stamp flex flex-col items-center justify-between gap-2 text-center">
                                <span class="es-post-stamp-value">FOLLOW</span>
                                <svg aria-hidden="true" class="es-post-ink h-7 w-7" fill="currentColor" viewBox="0 0 24 24">{!! $social['path'] !!}</svg>
                                <span class="es-post-stamp-name es-post-ink">{{ $social['name'] }}</span>
                                <span class="es-post-frank"></span>
                            </a>
                        @endforeach
                    </div>
                    <p class="es-post-muted relative z-10 mt-6 es-post-hr border-t pt-4 text-xs">
                        Six places to follow along, and not one of them is a support queue. For anything that needs an answer, the addresses above are better than a reply in a feed.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. FAQ                                                       -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="scroll-mt-24 es-post-hr border-t py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="es-post-num mb-6" data-reveal aria-hidden="true"><span>07</span></div>
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

    <!-- ============================================================ -->
    <!-- 8. Related pages                                             -->
    <!-- ============================================================ -->
    <section class="es-post-hr border-t py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-post-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4" data-reveal-group="70">
                @foreach ($relatedPages as [$relHref, $relName, $relBlurb])
                    <a href="{{ marketing_url($relHref) }}" class="es-post-hover es-post-card group flex flex-col p-5 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-post-hover-title es-post-ink mb-2 text-sm font-semibold transition-colors">{{ $relName }}</span>
                        <span class="es-post-muted mb-4 text-xs leading-relaxed">{{ $relBlurb }}</span>
                        <span class="es-post-hover-arrow es-post-muted mt-auto inline-flex items-center gap-1 text-xs font-medium transition-colors">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
            <div class="mt-8 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-post-link inline-flex items-center font-medium hover:underline">
                    See all features
                    <svg aria-hidden="true" class="ml-1 h-4 w-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Finale                                                    -->
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
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-400 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-post-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Get Started Free
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

    <x-marketing.related-pages />

    <!-- Local confetti (no CDN) + motion engines -->
    <script {!! nonce_attr() !!} src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
