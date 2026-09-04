<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Curators | Multi-Venue Events</x-slot>
    <x-slot name="description">Build the ultimate local guide. Use AI import to aggregate events from multiple sources and grow your following. For bloggers, orgs, and event aggregators.</x-slot>
    <x-slot name="breadcrumbTitle">For Curators</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Curators",
        "description": "Build the ultimate local guide. Aggregate events from the venues and performers you cover, review what gets submitted, and grow your following. Zero platform fees.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Event Curators"
        }
    }
    </script>
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Curators",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Event Curation Software",
        "operatingSystem": "Web",
        "description": "Build the ultimate local guide. Aggregate events from the venues and performers you cover, review what gets submitted, and grow your following.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Free forever"
        },
        "featureList": [
            "Event aggregation from other schedules",
            "Submission and approval inbox",
            "AI event import from text and flyers",
            "Sub-schedules",
            "Newsletters to your followers",
            "Schedule graphics"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "event curator schedule, event promoter calendar, multi-venue event management, curator booking platform, free curator scheduling",
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
        /* For-curators "The Listings" styles. The shared es-* motion system lives
           in marketing.css; this holds the newsprint palette, the nameplate and
           rule grammar, the dotted listing leaders, the folio section marks and
           folio dot-nav, the APPROVED stamp, the halftone screen, the scissors
           coupon, and the re-inking of the shared cards, pricing nudge and
           related strip so nothing arrives in brand blue on newsprint.

           Nothing on this page tilts, glares or lifts: it is paper. That also
           means the shared card focus ring (marketing.css, a.feature-card and
           friends) never matches here, so focus states are declared explicitly
           below and are load-bearing, not decoration. */

        :root {
            --esc-paper: #faf6ec;
            --esc-paper-2: #f2ecdd;
            --esc-box: #fffdf7;
            --esc-ink: #1c1917;
            --esc-muted: #57534e;
            --esc-faint: #6d6259;
            --esc-rule: #ddd5c2;
            --esc-rule-strong: #b6ab92;
            --esc-spot: #9a3412;
            --esc-spot-2: #b45309;
            --esc-serif: ui-serif, Georgia, "Times New Roman", Times, serif;
        }
        /* Dark mode inverts the paper rather than floating a cream slab in a dark
           room: for-magicians can fix its card faces to ivory because the cards
           are small objects on a table, but a full-bleed page cannot. */
        .dark {
            --esc-paper: #0d0c0a;
            --esc-paper-2: #131210;
            --esc-box: #191713;
            --esc-ink: #ece7dc;
            --esc-muted: #a8a29b;
            --esc-faint: #8c867e;
            --esc-rule: rgba(236, 231, 220, 0.16);
            --esc-rule-strong: rgba(236, 231, 220, 0.34);
            --esc-spot: #fbbf24;
            --esc-spot-2: #fb923c;
        }

        /* --- Ground and ink --- */
        #es-cur-page { background-color: var(--esc-paper); color: var(--esc-ink); }
        .es-cur-ground { background-color: var(--esc-paper); }
        .es-cur-ground-2 { background-color: var(--esc-paper-2); }
        .es-cur-muted { color: var(--esc-muted); }
        .es-cur-faint { color: var(--esc-faint); }
        .es-cur-icon { color: var(--esc-spot); }

        /* --- Type: serif is display only, body copy stays the system sans --- */
        .es-cur-serif { font-family: var(--esc-serif); }
        .es-cur-name {
            font-family: var(--esc-serif);
            font-weight: 800;
            letter-spacing: -0.025em;
            line-height: 0.96;
            font-size: clamp(2.6rem, 8.6vw, 5.5rem);
        }
        .es-cur-head-2 {
            font-family: var(--esc-serif);
            font-weight: 800;
            letter-spacing: -0.02em;
            line-height: 1.06;
            font-size: clamp(1.75rem, 4.4vw, 2.85rem);
        }
        .es-cur-kicker {
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.22em;
            text-transform: uppercase;
        }
        .es-cur-agate { font-size: 0.8125rem; line-height: 1.45; }
        .es-cur-nums { font-variant-numeric: tabular-nums; }
        /* The shared es-mask only ships a -2 delay; the nameplate sets on three lines. */
        html.es-anim .es-mask-3 .es-mask-line { animation-delay: 0.41s; }

        /* Accent word. Light keeps both stops dark enough to read on cream
           (a saffron gradient measures under 2:1 there); dark gets the bright
           saffron to coral. background-image, never the background shorthand,
           which would reset the clip and paint a solid block. */
        .text-gradient-guide {
            background-image: linear-gradient(135deg, #9a3412, #b45309);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dark .text-gradient-guide {
            background-image: linear-gradient(135deg, #fbbf24, #fb923c);
        }

        /* --- Rules: the page's structural grammar --- */
        .es-cur-rule { height: 1px; background-color: var(--esc-rule-strong); transform-origin: 0% 50%; }
        [dir="rtl"] .es-cur-rule { transform-origin: 100% 50%; }
        .es-cur-rule-hair { height: 1px; background-color: var(--esc-rule); }
        .es-cur-rule-double {
            height: 4px;
            border-top: 3px solid var(--esc-ink);
            border-bottom: 1px solid var(--esc-ink);
            transform-origin: 0% 50%;
        }
        [dir="rtl"] .es-cur-rule-double { transform-origin: 100% 50%; }

        /* Rules draw themselves in, but only on section headers: every rule on
           the page animating at once reads as noise. */
        html.es-anim [data-reveal] .es-cur-rule,
        html.es-anim [data-reveal] .es-cur-rule-double {
            transform: scaleX(0);
            transition: transform 0.9s cubic-bezier(0.22, 1, 0.36, 1);
        }
        html.es-anim [data-reveal].is-revealed .es-cur-rule,
        html.es-anim [data-reveal].is-revealed .es-cur-rule-double { transform: scaleX(1); }

        /* --- Dateline and folio marks --- */
        .es-cur-dateline {
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.28em;
            text-transform: uppercase;
            color: var(--esc-faint);
        }
        .es-cur-folio {
            font-family: var(--esc-serif);
            font-size: 0.8125rem;
            letter-spacing: 0.16em;
            color: var(--esc-faint);
        }

        /* --- Listings: title, dotted leader, right-hand column --- */
        .es-cur-row { display: flex; align-items: baseline; gap: 0.5rem; flex-wrap: wrap; }
        .es-cur-dots {
            flex: 1 1 2rem;
            min-width: 1.75rem;
            border-bottom: 1px dotted var(--esc-rule-strong);
            transform: translateY(-0.28em);
            transition: border-color 0.2s ease, border-bottom-style 0.2s ease;
        }
        .es-cur-listing { padding-block: 0.85rem; border-bottom: 1px solid var(--esc-rule); }
        .es-cur-listing:first-child { border-top: 1px solid var(--esc-rule); }
        /* Print-native hover: the leader inks in and the rule thickens. No lift,
           no scale, no shadow. */
        .es-cur-listing:hover .es-cur-dots { border-bottom-style: solid; border-bottom-color: var(--esc-spot); }

        /* Two real columns at lg, one below. Every child stays whole. */
        .es-cur-cols > * { break-inside: avoid; }
        @media (min-width: 1024px) {
            .es-cur-cols {
                columns: 2;
                column-gap: 3rem;
                column-rule: 1px solid var(--esc-rule);
            }
            .es-cur-cols > .es-cur-listing:first-child { border-top: 1px solid var(--esc-rule); }
        }
        /* Below sm a leader would render as a three-dot stub, so it goes and the
           right-hand column drops to its own line. */
        @media (max-width: 639px) {
            .es-cur-dots { display: none; }
            .es-cur-row > .es-cur-when { flex-basis: 100%; }
        }

        /* --- Tier tag, set like a classified category --- */
        .es-cur-tag {
            display: inline-flex;
            align-items: center;
            border: 1px solid var(--esc-rule-strong);
            border-radius: 2px;
            padding: 0.05rem 0.35rem;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--esc-spot);
            white-space: nowrap;
        }

        /* --- Boxes --- */
        .es-cur-box {
            background-color: var(--esc-box);
            border: 1px solid var(--esc-rule);
            border-radius: 2px;
        }
        .es-cur-box-heavy { border: 2px solid var(--esc-ink); border-radius: 2px; background-color: var(--esc-box); }

        /* --- Halftone screen, kept to small mocks: a full-bleed repeating
               radial gradient is expensive to paint on a phone. --- */
        .es-cur-halftone {
            background-image: radial-gradient(circle at center, var(--esc-ink) 0.9px, transparent 1.1px);
            background-size: 5px 5px;
            opacity: 0.26;
        }
        .dark .es-cur-halftone { opacity: 0.2; }

        /* --- EDITOR'S PICK star --- */
        .es-cur-pick {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--esc-spot);
        }

        /* --- APPROVED rubber stamp, fires once --- */
        .es-cur-stamp {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.1rem 0.4rem;
            border: 2px solid var(--esc-spot);
            border-radius: 3px;
            color: var(--esc-spot);
            font-size: 0.625rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            transform: rotate(-9deg);
        }
        .es-cur-stamp svg { width: 0.7rem; height: 0.7rem; }
        html.es-anim [data-reveal] .es-cur-stamp {
            opacity: 0;
            transform: rotate(7deg) scale(1.7);
            transition: opacity 0.4s ease, transform 0.5s cubic-bezier(0.2, 1.5, 0.4, 1);
            transition-delay: 0.5s;
        }
        html.es-anim [data-reveal].is-revealed .es-cur-stamp { opacity: 1; transform: rotate(-9deg) scale(1); }

        /* --- The press run: warm press black, not the shared navy band --- */
        .es-cur-press.es-band-dark {
            background-color: #080706;
            background-image: radial-gradient(120% 100% at 50% 0%, rgba(30, 25, 18, 0.96) 0%, rgba(8, 7, 6, 0.99) 62%);
        }
        .es-cur-press-ink { color: #f4efe4; }
        .es-cur-press-muted { color: #b0a79a; }
        .es-cur-press-rule { height: 1px; background-color: rgba(244, 239, 228, 0.22); }
        .es-cur-press-num {
            font-family: var(--esc-serif);
            font-size: 2.25rem;
            font-weight: 800;
            line-height: 1;
            color: #fbbf24;
        }

        /* --- The finale coupon --- */
        .es-cur-coupon {
            position: relative;
            border: 2px dashed var(--esc-rule-strong);
            border-radius: 2px;
            background-color: var(--esc-box);
        }
        /* VS15 forces the text presentation of the scissors: without it the
           system paints the colour emoji, which is not ink on paper. */
        .es-cur-coupon::before {
            content: "\2702\FE0E";
            font-variant-emoji: text;
            position: absolute;
            top: -0.8rem;
            inset-inline-start: 1.5rem;
            padding-inline: 0.4rem;
            background-color: var(--esc-paper);
            color: var(--esc-faint);
            font-size: 1.05rem;
            line-height: 1.5rem;
        }

        /* --- Folio dot-nav: the section rail reads as the paper's page numbers --- */
        .es-cur-dotnav {
            background-color: var(--esc-box);
            border: 1px solid var(--esc-rule);
            border-radius: 3px;
        }
        .es-cur-dotnum {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 1.5rem;
            height: 1.5rem;
            border-radius: 2px;
            font-family: var(--esc-serif);
            font-size: 0.75rem;
            color: var(--esc-faint);
            transition: color 0.3s ease, background-color 0.3s ease;
        }
        .es-dot:hover .es-cur-dotnum { color: var(--esc-ink); }
        .es-dot.is-active .es-cur-dotnum { color: var(--esc-paper); background-color: var(--esc-spot); }

        /* --- Focus. The shared a.feature-card / a.bento-card ring never matches
               on this page because nothing here is a bento card, so every
               interactive element gets its ring from here. --- */
        #es-cur-page a:focus-visible,
        #es-cur-page summary:focus-visible,
        #es-cur-page button:focus-visible {
            outline: 2px solid var(--esc-spot);
            outline-offset: 3px;
            border-radius: 2px;
        }
        /* The claim field carries its own ring on the wrapper instead. */
        #es-claim-input:focus-visible { outline: none; }
        #es-cur-page .es-claim:focus-within {
            border-color: var(--esc-spot);
            box-shadow: 0 0 0 4px rgba(154, 52, 18, 0.22);
        }
        .dark #es-cur-page .es-claim:focus-within { box-shadow: 0 0 0 4px rgba(251, 191, 36, 0.22); }

        /* --- Buttons --- */
        .es-cur-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 2px;
            font-weight: 700;
            letter-spacing: 0.04em;
            transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }
        .es-cur-btn-solid { background-color: var(--esc-spot); color: var(--esc-paper); border: 2px solid var(--esc-spot); }
        .es-cur-btn-solid:hover { background-color: var(--esc-ink); border-color: var(--esc-ink); color: var(--esc-paper); }
        .es-cur-btn-ghost { background-color: transparent; color: var(--esc-ink); border: 2px solid var(--esc-ink); }
        .es-cur-btn-ghost:hover { background-color: var(--esc-ink); color: var(--esc-paper); }
        .es-cur-link { color: var(--esc-spot); font-weight: 600; }
        .es-cur-link:hover { text-decoration: underline; }
        a:hover .es-cur-relname { color: var(--esc-spot); }

        /* --- Re-inking the shared components so nothing lands in brand blue.
               Selectors are structural because the components render fixed
               Tailwind utility strings (see sub-audience-card.blade.php and
               feature-link-card.blade.php). --- */

        /* Classified ads. These cards are not links, so they get no hover
           affordance: hover feedback here would be a lie. */
        #es-cur-classifieds .es-cur-ad > div {
            height: 100%;
            background-color: var(--esc-box);
            border-color: var(--esc-rule);
            border-radius: 2px;
            box-shadow: none;
        }
        #es-cur-classifieds .es-cur-ad > div:hover {
            border-color: var(--esc-rule);
            box-shadow: none;
        }
        #es-cur-classifieds .es-cur-ad > div > div:first-child {
            background-color: rgba(154, 52, 18, 0.08);
            border: 1px solid var(--esc-rule);
            border-radius: 2px;
            width: 2.25rem;
            height: 2.25rem;
        }
        .dark #es-cur-classifieds .es-cur-ad > div > div:first-child { background-color: rgba(251, 191, 36, 0.1); }
        #es-cur-classifieds .es-cur-ad h3 { color: var(--esc-ink); font-family: var(--esc-serif); }
        #es-cur-classifieds .es-cur-ad p { color: var(--esc-muted); }

        /* The index rows. The card becomes a ruled listing line. */
        #es-cur-index .es-cur-idx > a > div {
            background-color: transparent;
            border-color: transparent;
            border-bottom: 1px solid var(--esc-rule);
            border-radius: 0;
            padding-inline: 0;
        }
        #es-cur-index .es-cur-idx > a:hover > div {
            background-color: transparent;
            border-bottom-color: var(--esc-spot);
        }
        #es-cur-index .es-cur-idx > a > div > div:first-child {
            background-color: rgba(154, 52, 18, 0.08);
            border: 1px solid var(--esc-rule);
            border-radius: 2px;
        }
        .dark #es-cur-index .es-cur-idx > a > div > div:first-child { background-color: rgba(251, 191, 36, 0.1); }
        #es-cur-index .es-cur-idx .font-semibold { color: var(--esc-ink); font-family: var(--esc-serif); }
        #es-cur-index .es-cur-idx .text-sm { color: var(--esc-muted); }
        #es-cur-index .es-cur-idx svg { color: var(--esc-spot); }

        /* The rates block (shared pricing nudge). */
        #es-cur-rates > section { background-color: var(--esc-paper-2); }
        #es-cur-rates h2 { color: var(--esc-ink); font-family: var(--esc-serif); }
        #es-cur-rates .text-3xl { color: var(--esc-ink); font-family: var(--esc-serif); }
        /* The "/mo" rider inside the price keeps Tailwind's gray-500, which lands
           at 4.1:1 on this cream ground. Re-ink it with the page's own muted ink. */
        #es-cur-rates .text-3xl span { color: var(--esc-muted); }
        #es-cur-rates .text-sm { color: var(--esc-muted); }
        #es-cur-rates .w-px { background-color: var(--esc-rule); }
        #es-cur-rates a { color: var(--esc-spot); }

        /* The shared "Keep exploring" strip. */
        #es-cur-exploring > section {
            background-color: var(--esc-paper);
            border-color: var(--esc-rule);
        }
        #es-cur-exploring h2 { color: var(--esc-ink); font-family: var(--esc-serif); }
        #es-cur-exploring > section > div > div > p { color: var(--esc-spot); }
        #es-cur-exploring a {
            background-color: var(--esc-box);
            border-color: var(--esc-rule);
            border-radius: 2px;
            box-shadow: none;
        }
        #es-cur-exploring a:hover { border-color: var(--esc-spot); box-shadow: none; }
        #es-cur-exploring a h3 { color: var(--esc-ink); font-family: var(--esc-serif); }
        #es-cur-exploring a:hover h3 { color: var(--esc-spot); }
        #es-cur-exploring a p { color: var(--esc-muted); }
        #es-cur-exploring a span { color: var(--esc-spot); }

        /* --- Reduced motion: every rule drawn, every stamp landed, nothing moving --- */
        @media (prefers-reduced-motion: reduce) {
            .es-cur-rule,
            .es-cur-rule-double,
            .es-cur-stamp {
                transform: none !important;
                opacity: 1 !important;
                transition: none !important;
            }
            .es-cur-stamp { transform: rotate(-9deg) !important; }
        }
    </style>

    @php
        $curSections = [
            ['desk', 'The desk', '1'],
            ['toolkit', "The curator's toolkit", '2'],
            ['mailbag', 'From the mailbag', '3'],
            ['classifieds', 'Classifieds', '4'],
            ['press', 'How the page gets made', '5'],
            ['questions', 'Questions', '6'],
            ['claim', 'Claim your guide', '7'],
        ];

        // The listings column. The right-hand column of a real listings page is
        // the showtime; here it is the plan the feature needs, checked against
        // docs/FEATURES.md.
        $curListings = [
            ['Venues publish straight to your guide', 'Free', 'A venue that follows your guide can name it a default curator. Everything they post then lands in your queue.'],
            ['Never list the same thing twice', 'Free', 'When something you paste in is already on Event Schedule, one click lists that event instead of a second copy.'],
            ['A submission inbox you control', 'Free', 'Readers send you events through the form on your page. Nothing reaches the public page until you accept it.'],
            ['Trusted sources skip the queue', 'Free', 'Approve a venue once and everything they send after that publishes straight through.'],
            ['File every event under a section', 'Free', 'Sort what arrives into sub-schedules: music, markets, kids, whatever your guide needs.'],
            ['Merge duplicate venues', 'Free', 'Ten submitters spell the same room ten ways. Merge them back into one in a single place.'],
            ['Email the week to your readers', 'Free', 'Following the guide is permission to email. You write the newsletter and send it: ten recipients a month free, a hundred on Pro.'],
            ['Embed it on the site you have', 'Free', 'Drop the calendar straight into your blog or your organisation page.'],
            ['Schedule graphics', 'Pro', 'Build one shareable image out of what is coming up. Events need their own flyer to appear, and twenty of them fit.'],
            ['Your own domain', 'Enterprise', 'Run the guide on your own address instead of a subdomain.'],
        ];

        $curFaqs = [
            ['q' => 'Is Event Schedule free for event curators?', 'a' => 'Yes. Aggregating events, running the approval inbox, building a following and syncing with Google, Outlook or CalDAV are all free forever. Schedule graphics and removing our branding are on the Pro plan, and your own domain is on Enterprise.'],
            ['q' => 'How do events get onto my guide?', 'a' => 'Three ways. A venue or performer who follows your guide can name it a default curator, and everything they schedule then lands in your queue. Visitors can submit an event through the form on your page, signed in by default. And you can add events yourself, by hand or by pasting in text or a photo of a flyer.'],
            ['q' => 'Can I control which events appear on my schedule?', 'a' => 'Yes. Submitted events wait in your inbox until you accept or decline them, so nothing reaches the public page without your say-so. If you trust a source, add their schedule to your approved list and their events publish straight through.'],
            ['q' => 'What happens when an event is already on Event Schedule?', 'a' => 'You are offered the existing one. Paste in something the site already knows about and the importer flags the match, so a single click lists that event on your guide instead of creating a second copy, and the listing keeps pointing back at the schedule that owns it. Draft and private events are never offered, so nothing unpublished can end up on your page.'],
            ['q' => 'How do people discover my curated schedule?', 'a' => 'Share your schedule link on social media, embed the calendar on your blog or website, and let search engines index the page. Readers who follow the guide are giving you permission to email them, so a newsletter with the highlights of the week is yours to send whenever you like.'],
        ];
    @endphp

    <div id="es-cur-page">

        {{-- Folio rail: the shared dot-nav, rendered as page numbers instead of pips. --}}
        <nav class="es-cur-dotnav es-dotnav fixed top-1/2 z-40 hidden -translate-y-1/2 lg:block ltr:right-5 rtl:left-5" aria-label="Page sections">
            <ul class="flex flex-col items-center gap-1 px-1.5 py-2">
                @foreach ($curSections as [$secId, $secLabel, $secFolio])
                    <li class="relative">
                        <a href="#{{ $secId }}" class="es-dot group block" aria-label="{{ $secLabel }}">
                            <span class="es-cur-dotnum" aria-hidden="true">{{ $secFolio }}</span>
                            <span class="es-cur-box es-cur-agate pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap px-2.5 py-1 opacity-0 transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3" style="color: var(--esc-ink);">{{ $secLabel }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <!-- ============================================================ -->
        <!-- Hero: the nameplate                                          -->
        <!-- ============================================================ -->
        <section class="es-cur-ground noise relative overflow-hidden px-4 pb-14 pt-10 sm:px-6 lg:px-8 lg:pb-20 lg:pt-14">
            <div class="relative z-10 mx-auto max-w-6xl">

                {{-- Dateline. The tail segments only appear once there is room for
                     them, so it never wraps to three lines on a phone. --}}
                <div class="es-fade-up es-d-1">
                    <div class="es-cur-rule-hair"></div>
                    <p class="es-cur-dateline flex flex-wrap items-center justify-center gap-x-3 gap-y-1 py-2.5 text-center">
                        <span>Vol. 1</span>
                        <span aria-hidden="true">&middot;</span>
                        <span>Your city</span>
                        <span class="hidden sm:inline" aria-hidden="true">&middot;</span>
                        <span class="hidden sm:inline">Free forever</span>
                        <span class="hidden md:inline" aria-hidden="true">&middot;</span>
                        <span class="hidden md:inline">Curated by you</span>
                    </p>
                    <div class="es-cur-rule-hair"></div>
                </div>

                <div class="grid items-start gap-10 pt-10 lg:grid-cols-[minmax(0,1.55fr)_minmax(0,1fr)] lg:gap-14 lg:pt-14">

                    <div>
                        {{-- Three masked lines, because at every width the nameplate
                             sets on three. The shared es-mask only ships a -2 delay,
                             so -3 is declared in the style block above. --}}
                        <h1 class="es-cur-name es-balance">
                            <span class="es-mask"><span class="es-mask-line">Build the</span></span>
                            <span class="es-mask es-mask-2"><span class="es-mask-line">ultimate</span></span>
                            <span class="es-mask es-mask-3"><span class="es-mask-line"><span class="text-gradient-guide es-gradient-anim">local guide</span></span></span>
                        </h1>

                        <div class="es-cur-rule-double es-fade-up es-d-2 mt-7"></div>

                        <p class="es-cur-serif es-fade-up es-d-2 mt-7 text-xl leading-relaxed sm:text-2xl" style="color: var(--esc-muted);">
                            You do not book the bands or run the rooms. You just know what is on.
                            This is the page that proves it.
                        </p>

                        <div class="es-fade-up es-d-3 mt-9 flex flex-col gap-3 sm:flex-row">
                            <a href="#toolkit" class="es-cur-btn es-cur-btn-ghost px-6 py-3 text-base">
                                See what it does
                                <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                            </a>
                            <a href="{{ app_url('/sign_up?type=curator') }}" class="es-cur-btn es-cur-btn-solid px-7 py-3 text-base">
                                Start curating
                                <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </a>
                        </div>

                        {{-- Sources, set as a ruled agate line. A scrolling ticker is
                             television; a newspaper sets its wire sources in type. --}}
                        <div class="es-fade-up es-d-4 mt-10">
                            <div class="es-cur-rule-hair"></div>
                            <p class="es-cur-agate flex flex-wrap items-baseline gap-x-2 gap-y-1 py-3">
                                <span class="es-cur-kicker" style="color: var(--esc-spot);">Sources</span>
                                <span class="es-cur-muted">Venues &middot; Performers &middot; Blogs &middot; Flyers &middot; WhatsApp &middot; Telegram &middot; Instagram &middot; Newsletters</span>
                            </p>
                            <div class="es-cur-rule-hair"></div>
                        </div>
                    </div>

                    {{-- The front-page listings box. Desktop only, so a phone reaches
                         the call to action without scrolling past a decorative mock. --}}
                    <div class="es-fade-up es-d-4 hidden lg:block" aria-hidden="true">
                        <div class="es-cur-box-heavy p-5">
                            <p class="es-cur-kicker" style="color: var(--esc-spot);">This week</p>
                            <div class="es-cur-rule mt-2.5"></div>
                            <div class="mt-1">
                                @foreach ([['Jazz Night', 'Blue Note', 'Fri 8:00'], ['Comedy Basement', 'The Roxy', 'Sat 9:00'], ['Riverside Art Walk', 'Old Mill District', 'Sun 6:00'], ['Growers Market', 'Town Square', 'Sun 9:00']] as [$hName, $hVenue, $hTime])
                                    <div class="es-cur-listing">
                                        <div class="es-cur-row">
                                            <span class="es-cur-serif text-base font-bold">{{ $hName }}</span>
                                            <span class="es-cur-dots"></span>
                                            <span class="es-cur-when es-cur-agate es-cur-nums es-cur-muted">{{ $hTime }}</span>
                                        </div>
                                        <p class="es-cur-agate es-cur-faint mt-0.5">{{ $hVenue }}</p>
                                    </div>
                                @endforeach
                            </div>

                            <div class="es-cur-box relative mt-5 overflow-hidden p-4">
                                <div class="es-cur-halftone absolute inset-0" aria-hidden="true"></div>
                                <div class="relative">
                                    <p class="es-cur-pick">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.9 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l7.1-1.01L12 2z" /></svg>
                                        From the editor
                                    </p>
                                    <p class="es-cur-serif mt-1.5 text-lg font-bold leading-snug">Forty-seven sources. One page.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 1. The desk                                                  -->
        <!-- ============================================================ -->
        <section id="desk" class="es-cur-ground-2 scroll-mt-24 px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
            <div class="mx-auto max-w-5xl">

                <div data-reveal>
                    <div class="es-cur-rule"></div>
                    <div class="flex items-baseline justify-between gap-4 py-2">
                        <span class="es-cur-kicker" style="color: var(--esc-spot);">The desk</span>
                        <span class="es-cur-folio es-cur-nums" aria-hidden="true">Page 1</span>
                    </div>
                    <div class="es-cur-rule"></div>
                    <h2 class="es-cur-head-2 es-balance mt-7 max-w-3xl">
                        You do not create the events. You <span class="text-gradient-guide">decide what makes the page</span>.
                    </h2>
                </div>

                <div class="mt-8 grid gap-8 md:grid-cols-2 md:gap-12" data-reveal-group="90">
                    <p class="es-cur-serif text-lg leading-relaxed" data-reveal style="color: var(--esc-muted);">
                        A curator schedule owns nothing of its own. Every event on it belongs to somebody
                        else: the venue that booked it, the performer playing it, the organiser who typed it
                        in at midnight. What you bring is the judgement about which of them is worth
                        somebody's Friday.
                    </p>
                    <p class="es-cur-serif text-lg leading-relaxed" data-reveal style="color: var(--esc-muted);">
                        Event Schedule is built that way on purpose. Events arrive from the schedules that
                        name your guide as their curator, from the submission form on your page, and from
                        whatever you paste in yourself. You accept, you file, you publish. After that the
                        guide keeps filling itself while you get on with covering the scene.
                    </p>
                </div>

                {{-- Circulation line --}}
                <div class="mt-12" data-reveal>
                    <div class="es-cur-rule"></div>
                    <dl class="grid grid-cols-1 gap-6 py-6 sm:grid-cols-3">
                        @foreach ([['Schedules feeding the guide', '24'], ['Events this month', '63'], ['Followers', '2,847']] as [$statLabel, $statValue])
                            <div class="text-center sm:text-start">
                                <dt class="es-cur-kicker es-cur-faint">{{ $statLabel }}</dt>
                                <dd class="es-cur-serif es-cur-nums mt-1 text-4xl font-extrabold" style="color: var(--esc-spot);" data-count-to="{{ $statValue }}">{{ $statValue }}</dd>
                            </div>
                        @endforeach
                    </dl>
                    <div class="es-cur-rule"></div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. The toolkit: lead story plus listings column               -->
        <!-- ============================================================ -->
        <section id="toolkit" class="es-cur-ground scroll-mt-24 px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
            <div class="mx-auto max-w-6xl">

                <div data-reveal>
                    <div class="es-cur-rule"></div>
                    <div class="flex items-baseline justify-between gap-4 py-2">
                        <span class="es-cur-kicker" style="color: var(--esc-spot);">The curator's toolkit</span>
                        <span class="es-cur-folio es-cur-nums" aria-hidden="true">Page 2</span>
                    </div>
                    <div class="es-cur-rule"></div>
                    <h2 class="es-cur-head-2 es-balance mt-7 max-w-3xl">
                        Everything happening, <span class="text-gradient-guide">in one place</span>
                    </h2>
                </div>

                {{-- Lead story: AI import --}}
                <div class="es-cur-box-heavy mt-10 p-6 lg:p-9" data-reveal>
                    <div class="grid items-center gap-9 lg:grid-cols-[minmax(0,1.35fr)_minmax(0,1fr)]">
                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="es-cur-kicker" style="color: var(--esc-spot);">Lead story &middot; AI import</span>
                                <span class="es-cur-tag">Free</span>
                            </div>
                            <h3 class="es-cur-head-2 mt-4">Paste the text. Or drop the flyer.</h3>
                            <p class="es-cur-serif mt-4 text-lg leading-relaxed" style="color: var(--esc-muted);">
                                Copy an announcement straight out of a group chat, or drag in a photo of a
                                poster taped to a lamp post. The name, date, time, venue and price come back
                                filled in and waiting for you to check. It reads several events out of a
                                single image, and it does not care what language the poster is in.
                            </p>
                            <div class="mt-6 flex flex-wrap gap-2">
                                @foreach (['Pasted text', 'Flyer photos', 'Several events at once', 'Any language'] as $aiChip)
                                    <span class="es-cur-box es-cur-agate es-cur-muted px-2.5 py-1">{{ $aiChip }}</span>
                                @endforeach
                            </div>
                        </div>

                        <div aria-hidden="true">
                            <div class="es-cur-box p-4">
                                <p class="es-cur-kicker es-cur-faint">Pasted in</p>
                                <p class="es-cur-agate mt-2 font-mono leading-relaxed" style="color: var(--esc-muted);">
                                    "sat 15th, jazz night @ blue note,<br>doors 8pm, $25 on the door"
                                </p>
                            </div>
                            <div class="flex justify-center py-2">
                                <svg class="h-5 w-5" style="color: var(--esc-spot);" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                            </div>
                            <div class="es-cur-box p-4">
                                <p class="es-cur-kicker" style="color: var(--esc-spot);">Read back</p>
                                <dl class="mt-2 space-y-1.5">
                                    @foreach ([['Name', 'Jazz Night'], ['Date', 'Sat 15, 8:00 PM'], ['Venue', 'Blue Note'], ['Price', '$25']] as $fi => [$fLabel, $fValue])
                                        <div class="es-ai-field es-cur-agate flex justify-between gap-4" style="--i: {{ $fi }};">
                                            <dt class="es-cur-faint">{{ $fLabel }}</dt>
                                            <dd class="es-cur-nums font-semibold">{{ $fValue }}</dd>
                                        </div>
                                    @endforeach
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- The listings column. The showtime column of a real listings page
                     becomes the plan each feature needs. --}}
                <div class="mt-14" data-reveal>
                    <div class="flex items-baseline justify-between gap-4 pb-2">
                        <span class="es-cur-kicker" style="color: var(--esc-spot);">Also in the toolkit</span>
                        <span class="es-cur-kicker es-cur-faint">Plan</span>
                    </div>
                </div>
                <div class="es-cur-cols" data-reveal-group="60">
                    @foreach ($curListings as [$lName, $lTier, $lBlurb])
                        <div class="es-cur-listing" data-reveal>
                            <div class="es-cur-row">
                                <span class="es-cur-serif text-lg font-bold">{{ $lName }}</span>
                                <span class="es-cur-dots" aria-hidden="true"></span>
                                <span class="es-cur-when"><span class="es-cur-tag">{{ $lTier }}</span></span>
                            </div>
                            <p class="es-cur-agate es-cur-muted mt-1.5">{{ $lBlurb }}</p>
                        </div>
                    @endforeach
                </div>

                {{-- The approval spread --}}
                <div class="es-cur-box-heavy mt-14 p-6 lg:p-9" data-reveal>
                    <div class="grid items-center gap-9 lg:grid-cols-2">
                        <div>
                            <span class="es-cur-kicker" style="color: var(--esc-spot);">The inbox</span>
                            <h3 class="es-cur-head-2 mt-3">Nothing goes to print without you.</h3>
                            <p class="es-cur-serif mt-4 text-lg leading-relaxed" style="color: var(--esc-muted);">
                                Every submission waits in the queue until you accept or decline it. Once you
                                trust a venue, put them on your approved list and their events publish
                                straight through, so the queue only ever holds the things you actually need
                                to read.
                            </p>
                        </div>
                        <div aria-hidden="true">
                            <div class="es-cur-box relative p-4">
                                <div class="absolute -top-3 ltr:right-4 rtl:left-4">
                                    <span class="es-cur-stamp" style="background-color: var(--esc-box);">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        Approved
                                    </span>
                                </div>
                                <p class="es-cur-kicker es-cur-faint">Waiting for review</p>
                                <div class="mt-2">
                                    @foreach ([['Jazz Night', 'Blue Note', true], ['Open Mic', 'The Roxy', false], ['Book Launch', 'Corner Bookshop', false]] as [$qName, $qVenue, $qFirst])
                                        <div class="es-cur-listing">
                                            <div class="es-cur-row">
                                                <span class="es-cur-serif font-bold {{ $qFirst ? '' : 'opacity-60' }}">{{ $qName }}</span>
                                                <span class="es-cur-dots"></span>
                                                <span class="es-cur-when es-cur-agate es-cur-faint">{{ $qVenue }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. From the mailbag                                          -->
        <!-- ============================================================ -->
        <section id="mailbag" class="es-cur-ground-2 scroll-mt-24 px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
            <div class="mx-auto max-w-5xl">

                <div data-reveal>
                    <div class="es-cur-rule"></div>
                    <div class="flex items-baseline justify-between gap-4 py-2">
                        <span class="es-cur-kicker" style="color: var(--esc-spot);">From the mailbag</span>
                        <span class="es-cur-folio es-cur-nums" aria-hidden="true">Page 3</span>
                    </div>
                    <div class="es-cur-rule"></div>
                    <h2 class="es-cur-head-2 es-balance mt-7 max-w-3xl">
                        Already running this in a <span class="text-gradient-guide">group chat</span>?
                    </h2>
                    <p class="es-cur-serif mt-4 max-w-3xl text-lg leading-relaxed" style="color: var(--esc-muted);">
                        If you are already posting what is on into a WhatsApp group, a Facebook group or a
                        mailing list, you are a curator. You just do not have an archive.
                    </p>
                </div>

                <div class="es-cur-box mt-9 p-6 lg:p-9" data-reveal>
                    <div class="flex flex-col items-center gap-8 lg:flex-row lg:items-stretch">
                        <div class="flex-1">
                            {{-- Brand marks keep their real colours: this is the documented
                                 exemption from the page accent rule. --}}
                            <div class="flex flex-wrap justify-center gap-3 lg:justify-start">
                                <span class="es-cur-box flex h-12 w-12 items-center justify-center">
                                    <svg aria-hidden="true" class="h-6 w-6 text-[#25D366]" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                </span>
                                <span class="es-cur-box flex h-12 w-12 items-center justify-center">
                                    <svg aria-hidden="true" class="h-6 w-6 text-[#1877F2]" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                </span>
                                <span class="es-cur-box flex h-12 w-12 items-center justify-center">
                                    <svg aria-hidden="true" class="h-6 w-6 text-[#0088cc]" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                                </span>
                                <span class="es-cur-box flex h-12 w-12 items-center justify-center">
                                    <svg aria-hidden="true" class="h-6 w-6 text-[#E4405F]" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.757-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/></svg>
                                </span>
                            </div>
                            <p class="es-cur-serif mt-5 text-center text-lg leading-relaxed lg:text-start" style="color: var(--esc-muted);">
                                A group is good for conversation, but posts sink. Nobody joining next month
                                will ever scroll back far enough to find what you wrote today.
                            </p>
                        </div>

                        {{-- Hairline between the two halves: horizontal when they stack,
                             vertical once they sit side by side. Kept as plain utilities
                             because .es-cur-rule-hair pins height to 1px. --}}
                        <div class="h-px w-full self-stretch lg:h-auto lg:w-px" aria-hidden="true" style="background-color: var(--esc-rule);"></div>

                        <div class="flex-1">
                            <p class="es-cur-kicker text-center lg:text-start" style="color: var(--esc-spot);">And so</p>
                            <p class="es-cur-serif mt-3 text-center text-lg leading-relaxed lg:text-start" style="color: var(--esc-muted);">
                                A guide is a permanent address anybody can find and link to. Keep the group
                                for the chat. Put one link in it, and let the archive do the rest.
                            </p>
                        </div>
                    </div>

                    <div class="es-cur-rule-hair mt-8"></div>
                    <div class="grid gap-6 pt-7 md:grid-cols-3" data-reveal-group="80">
                        @foreach ([['One link to share', 'Post the guide once instead of every event twice.'], ['A standing archive', 'Readers can browse what is coming and what already happened.'], ['Reach past the group', 'Anyone can find the guide, not only the people already in the room.']] as [$mTitle, $mBody])
                            <div data-reveal>
                                <h3 class="es-cur-serif text-lg font-bold">{{ $mTitle }}</h3>
                                <p class="es-cur-agate es-cur-muted mt-1.5">{{ $mBody }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- House ad --}}
                <a href="{{ marketing_url('/features/online-events') }}" class="es-cur-box group mt-9 block p-6 lg:p-8" data-reveal>
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="es-cur-kicker es-cur-faint">Advertisement</p>
                            <h3 class="es-cur-serif mt-2 text-2xl font-bold">Not everything on your guide has an address.</h3>
                            <p class="es-cur-agate es-cur-muted mt-2 max-w-xl">
                                An online event carries a link instead of a venue, and it lists on your
                                guide exactly like everything else.
                            </p>
                        </div>
                        <span class="es-cur-link inline-flex shrink-0 items-center gap-2">
                            Read more
                            <svg aria-hidden="true" class="h-4 w-4 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </span>
                    </div>
                </a>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. Classifieds                                               -->
        <!-- ============================================================ -->
        <section id="classifieds" class="es-cur-ground scroll-mt-24 px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
            <div class="mx-auto max-w-6xl">

                <div data-reveal>
                    <div class="es-cur-rule"></div>
                    <div class="flex items-baseline justify-between gap-4 py-2">
                        <span class="es-cur-kicker" style="color: var(--esc-spot);">Classifieds</span>
                        <span class="es-cur-folio es-cur-nums" aria-hidden="true">Page 4</span>
                    </div>
                    <div class="es-cur-rule"></div>
                    <h2 class="es-cur-head-2 es-balance mt-7 max-w-3xl">
                        Every kind of <span class="text-gradient-guide">curator</span>
                    </h2>
                    <p class="es-cur-serif mt-4 max-w-3xl text-lg leading-relaxed" style="color: var(--esc-muted);">
                        However you gather what is on, the guide gives it a permanent home.
                    </p>
                </div>

                <div id="es-cur-classifieds" class="mt-9 grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                    <div class="es-cur-ad" data-reveal>
                        <x-sub-audience-card
                            name="City Guides"
                            description="Aggregate every gig, market, and pop-up in your city into one guide locals actually check."
                            icon-color="amber"
                        >
                            <x-slot:icon>
                                <svg aria-hidden="true" class="es-cur-icon h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </x-slot:icon>
                        </x-sub-audience-card>
                    </div>

                    <div class="es-cur-ad" data-reveal>
                        <x-sub-audience-card
                            name="Festival Programmers"
                            description="Publish the full lineup, file each stage under its own sub-schedule, and fix the set times as they firm up."
                            icon-color="amber"
                        >
                            <x-slot:icon>
                                <svg aria-hidden="true" class="es-cur-icon h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                </svg>
                            </x-slot:icon>
                        </x-sub-audience-card>
                    </div>

                    <div class="es-cur-ad" data-reveal>
                        <x-sub-audience-card
                            name="Scene Blogs"
                            description="Turn your niche coverage into a living calendar. Import events from pasted text and flyer photos."
                            icon-color="amber"
                        >
                            <x-slot:icon>
                                <svg aria-hidden="true" class="es-cur-icon h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                </svg>
                            </x-slot:icon>
                        </x-sub-audience-card>
                    </div>

                    <div class="es-cur-ad" data-reveal>
                        <x-sub-audience-card
                            name="Campus Calendars"
                            description="Pull together club nights, lectures, and games so students never miss what's on."
                            icon-color="amber"
                        >
                            <x-slot:icon>
                                <svg aria-hidden="true" class="es-cur-icon h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                </svg>
                            </x-slot:icon>
                        </x-sub-audience-card>
                    </div>

                    <div class="es-cur-ad" data-reveal>
                        <x-sub-audience-card
                            name="Tourism & Visitor Boards"
                            description="Show visitors everything happening this week, topped up as the venues that list you post."
                            icon-color="amber"
                        >
                            <x-slot:icon>
                                <svg aria-hidden="true" class="es-cur-icon h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.6 9h16.8M3.6 15h16.8M12 3a15 15 0 010 18 15 15 0 010-18z" />
                                </svg>
                            </x-slot:icon>
                        </x-sub-audience-card>
                    </div>

                    <div class="es-cur-ad" data-reveal>
                        <x-sub-audience-card
                            name="Community Newsletters"
                            description="Curate a weekly roundup and send the highlights straight to your subscribers' inboxes."
                            icon-color="amber"
                        >
                            <x-slot:icon>
                                <svg aria-hidden="true" class="es-cur-icon h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </x-slot:icon>
                        </x-sub-audience-card>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 5. The press run (fixed dark in both modes)                  -->
        <!-- ============================================================ -->
        <section id="press" class="es-cur-ground scroll-mt-24 px-2 py-10 sm:px-4 lg:py-16">
            <div class="es-cur-press es-band-dark noise relative overflow-hidden px-5 py-14 sm:px-8 lg:px-12 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
                <div class="relative z-10 mx-auto max-w-4xl">

                    <div data-reveal>
                        <div class="es-cur-press-rule"></div>
                        <div class="flex items-baseline justify-between gap-4 py-2">
                            <span class="es-cur-kicker" style="color: #fbbf24;">Late edition</span>
                            <span class="es-cur-folio es-cur-nums" style="color: #b0a79a;" aria-hidden="true">Page 5</span>
                        </div>
                        <div class="es-cur-press-rule"></div>
                        <h2 class="es-cur-head-2 es-cur-press-ink es-balance mt-7">
                            How the page gets made
                        </h2>
                    </div>

                    <div class="mt-10 grid gap-9 md:grid-cols-3" data-reveal-group="120">
                        @foreach ([['1', 'Start the paper', 'Sign up, name your guide, and say which city or which scene it covers.'], ['2', 'Fill the listings', 'Let the venues you cover list your guide as their curator, open submissions, and paste in whatever else you find.'], ['3', 'Go to press', 'Share one link instead of posting every event twice. Readers follow the guide, and the week\'s highlights go out by newsletter.']] as [$pNum, $pTitle, $pBody])
                            <div data-reveal>
                                <p class="es-cur-press-num">{{ $pNum }}</p>
                                <div class="es-cur-press-rule mt-3"></div>
                                <h3 class="es-cur-serif es-cur-press-ink mt-4 text-xl font-bold">{{ $pTitle }}</h3>
                                <p class="es-cur-press-muted mt-2 text-sm leading-relaxed">{{ $pBody }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 6. The index                                                 -->
        <!-- ============================================================ -->
        <section class="es-cur-ground-2 px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
            <div class="mx-auto max-w-3xl">

                <div data-reveal>
                    <div class="es-cur-rule"></div>
                    <div class="flex items-baseline justify-between gap-4 py-2">
                        <span class="es-cur-kicker" style="color: var(--esc-spot);">The index</span>
                        <span class="es-cur-folio es-cur-nums" aria-hidden="true">Page 6</span>
                    </div>
                    <div class="es-cur-rule"></div>
                    <h2 class="es-cur-head-2 mt-7">Key <span class="text-gradient-guide">features</span></h2>
                </div>

                <div id="es-cur-index" class="mt-6" data-reveal-group="70">
                    <div class="es-cur-idx" data-reveal>
                        <x-feature-link-card name="Analytics" description="Track page views, devices, and traffic sources" :url="marketing_url('/features/analytics')" icon-color="amber">
                            <x-slot:icon><svg aria-hidden="true" class="es-cur-icon h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg></x-slot:icon>
                        </x-feature-link-card>
                    </div>
                    <div class="es-cur-idx" data-reveal>
                        <x-feature-link-card name="Sub-Schedules" description="Sort what arrives into the sections of your guide" :url="marketing_url('/features/sub-schedules')" icon-color="amber">
                            <x-slot:icon><svg aria-hidden="true" class="es-cur-icon h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg></x-slot:icon>
                        </x-feature-link-card>
                    </div>
                    <div class="es-cur-idx" data-reveal>
                        <x-feature-link-card name="Newsletters" description="Send the week's highlights to your subscribers" :url="marketing_url('/features/newsletters')" icon-color="amber">
                            <x-slot:icon><svg aria-hidden="true" class="es-cur-icon h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                        </x-feature-link-card>
                    </div>
                </div>

                <p class="mt-7 text-center">
                    <a href="{{ marketing_url('/features') }}" class="es-cur-link inline-flex items-center gap-1.5">
                        See all features
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                </p>
            </div>
        </section>

        <div id="es-cur-rates">
            @include('marketing.partials.pricing-nudge')
        </div>

        <!-- ============================================================ -->
        <!-- Also in this issue                                           -->
        <!-- ============================================================ -->
        <section class="es-cur-ground-2 px-4 py-16 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl">
                <div data-reveal>
                    <div class="es-cur-rule"></div>
                    <div class="flex items-baseline justify-between gap-4 py-2">
                        <span class="es-cur-kicker" style="color: var(--esc-spot);">Also in this issue</span>
                    </div>
                    <div class="es-cur-rule"></div>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2" data-reveal-group="70">
                    @foreach ([['/for-talent', 'Talent'], ['/for-venues', 'Venues'], ['/for-community-centers', 'Community Centers'], ['/for-watch-parties', 'Watch Parties']] as [$relHref, $relName])
                        <a href="{{ marketing_url($relHref) }}" data-reveal class="es-cur-box group flex items-center justify-between gap-4 p-4">
                            <span>
                                <span class="es-cur-agate es-cur-faint block">Event Schedule for</span>
                                <span class="es-cur-serif es-cur-relname block text-lg font-bold transition-colors">{{ $relName }}</span>
                            </span>
                            <svg aria-hidden="true" class="h-4 w-4 shrink-0 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" style="color: var(--esc-spot);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    @endforeach
                </div>

                <p class="mt-6 text-center">
                    <a href="{{ marketing_url('/use-cases') }}" class="es-cur-link inline-flex items-center gap-1.5">
                        See all use cases
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                </p>
            </div>
        </section>

        <div id="es-cur-exploring">
            <x-marketing.related-pages />
        </div>

        <!-- ============================================================ -->
        <!-- 7. Ask the editor                                            -->
        <!-- ============================================================ -->
        <x-seo.faq-schema :items="$curFaqs" />
        <section id="questions" class="es-cur-ground scroll-mt-24 px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
            <div class="mx-auto max-w-3xl">

                <div data-reveal>
                    <div class="es-cur-rule"></div>
                    <div class="flex items-baseline justify-between gap-4 py-2">
                        <span class="es-cur-kicker" style="color: var(--esc-spot);">Ask the editor</span>
                        <span class="es-cur-folio es-cur-nums" aria-hidden="true">Page 7</span>
                    </div>
                    <div class="es-cur-rule"></div>
                    <h2 class="es-cur-head-2 es-balance mt-7">
                        Everything curators ask <span class="text-gradient-guide">first</span>
                    </h2>
                </div>

                <div class="mt-8" data-reveal-group="80">
                    @foreach ($curFaqs as $faq)
                        <details name="faq" data-reveal class="group/faq es-cur-listing">
                            <summary class="flex cursor-pointer items-baseline gap-3">
                                <span class="es-cur-serif shrink-0 text-lg font-extrabold" style="color: var(--esc-spot);" aria-hidden="true">Q.</span>
                                <h3 class="es-cur-serif flex-1 text-lg font-bold">{{ $faq['q'] }}</h3>
                                <svg aria-hidden="true" class="h-4 w-4 shrink-0 translate-y-1 transition-transform duration-300 group-open/faq:rotate-180" style="color: var(--esc-faint);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </summary>
                            <div class="faq-answer mt-3 flex items-baseline gap-3">
                                <span class="es-cur-serif shrink-0 text-lg font-extrabold" style="color: var(--esc-faint);" aria-hidden="true">A.</span>
                                <p class="es-cur-muted flex-1 leading-relaxed">{{ $faq['a'] }}</p>
                            </div>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 8. The coupon                                                -->
        <!-- ============================================================ -->
        <section id="claim" class="es-cur-ground scroll-mt-24 px-4 pb-20 pt-6 sm:px-6 lg:px-8 lg:pb-28">
            <div class="mx-auto max-w-3xl">
                <div class="es-cur-coupon px-6 py-10 text-center sm:px-10 lg:py-14" data-confetti data-reveal>
                    <p class="es-cur-kicker" style="color: var(--esc-spot);">Subscribe</p>
                    <h2 class="es-cur-head-2 es-balance mx-auto mt-3 max-w-2xl">
                        Become the guide people <span class="text-gradient-guide">check first</span>
                    </h2>
                    <p class="es-cur-serif mx-auto mt-4 max-w-xl text-lg" style="color: var(--esc-muted);">
                        Name your guide, and it is yours. Free forever, no card.
                    </p>

                    <div class="mx-auto mt-9 flex max-w-xl flex-col items-stretch gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim es-cur-box flex min-w-0 flex-1 items-center px-4 py-3.5 transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-city" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold focus:outline-none focus:ring-0 sm:text-base"
                                style="color: var(--esc-ink);">
                            <span class="es-cur-faint shrink-0 select-none font-mono text-sm sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=curator') }}" class="es-cur-btn es-cur-btn-solid shrink-0 px-7 py-3.5 text-base">
                            Get started free
                            <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    </div>

                    <div class="es-cur-rule-hair mx-auto mt-9 max-w-xs"></div>
                    <p class="es-cur-agate es-cur-faint mt-3">Detach and keep. No credit card required.</p>
                </div>
            </div>
        </section>

    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
