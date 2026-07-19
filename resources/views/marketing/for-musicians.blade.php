<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Musicians | Tour Dates & Tickets</x-slot>
    <x-slot name="description">Put every gig on one link. Sell tickets with zero platform fees, email fans directly, and sync with venues and Google Calendar. Free forever for musicians.</x-slot>
    <x-slot name="breadcrumbTitle">For Musicians</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Musicians",
        "description": "Put every gig on one link. Sell tickets with zero platform fees, email fans directly, and sync with venues and Google Calendar. Free forever for musicians.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Musicians"
        }
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "Is Event Schedule free for musicians?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule is free forever for sharing your gig schedule, building a fan following, and syncing with Google Calendar. Ticketing and newsletters are available on the Pro and Enterprise plans, with no platform fees on ticket sales."
                }
            },
            {
                "@type": "Question",
                "name": "How do fans find out about my upcoming shows?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Fans can follow your schedule and receive email notifications when you add new shows. You can also send newsletters directly to followers with your upcoming dates, and share your schedule link on Spotify, Bandcamp, your EPK, or any social profile."
                }
            },
            {
                "@type": "Question",
                "name": "Can I sell tickets to my own shows?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Connect your Stripe account and sell tickets directly from your schedule. Every ticket includes a QR code for check-in at the door. Event Schedule charges zero platform fees - you only pay Stripe's standard processing fees."
                }
            },
            {
                "@type": "Question",
                "name": "What happens when a venue books me for a show?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "When a venue adds you to their event on Event Schedule, it automatically appears on your schedule too. No need to manually add the same gig in two places. Both calendars stay in sync."
                }
            },
            {
                "@type": "Question",
                "name": "Can I list a weekly residency or recurring gigs?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Recurring events are free. Set the day-of-week pattern once, like every Thursday at the same club, and Event Schedule fills in the dates. You can exclude the weeks you skip, and fans always see the next upcoming show."
                }
            },
            {
                "@type": "Question",
                "name": "Can I use Event Schedule as my band website?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Many musicians do. Your schedule lives at your own link, like your-band.eventschedule.com, with your bio, photos, and streaming links. You can also embed the calendar on an existing website, and the Enterprise plan supports a fully custom domain."
                }
            }
        ]
    }
    </script>
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Musicians",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Musician Scheduling Software",
        "operatingSystem": "Web",
        "description": "Share your tour dates, sell tickets, and reach fans directly with newsletters. Built for musicians, bands, and solo artists.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Tour date page with a custom link",
            "Zero-fee ticket sales with QR door check-in",
            "Direct fan newsletters with open and click stats",
            "Recurring events for weekly residencies",
            "Two-way Google Calendar sync for gigs, rehearsals, and sessions",
            "Venue auto-linking when clubs book you",
            "Band, manager, and agent team access",
            "Fan follows with nearby-show notifications",
            "Waitlists for sold-out shows",
            "AI parsing of booking emails into events",
            "Auto-generated show graphics"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "musician schedule, band tour dates, share gig schedule, musician event calendar, band booking platform, free musician scheduling, band website with tour dates, residency schedule",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
    <!-- HowTo Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How musicians share their gig schedule with Event Schedule",
        "description": "Three steps from your first listed gig to a growing fanbase.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Add your gigs",
                "text": "Import from Google Calendar or add tour dates manually. Set up ticket sales if you want."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Share your link",
                "text": "Add it to your Spotify bio, Bandcamp, EPK, or anywhere fans find you."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Grow your fanbase",
                "text": "Fans follow your schedule, get notified about shows near them, and share videos and comments after your gigs."
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
           For-musicians "The Tour Poster" styles. The shared es-* motion
           system (reveals, marquee, bento, odometer, finale) lives in
           marketing.css; this block holds only the poster-print effects:
           ink variables, poster type, halftone fields, sawtooth tears,
           stamps, date stacks, lineup type, the backstage laminate, and
           their reduced-motion kills.
           ============================================================== */

        :root {
            --esp-paper: #f6f1e6;
            --esp-paper-2: #efe7d8;
            --esp-ink: #1c2733;
            --esp-ink-soft: rgba(28, 39, 51, 0.72);
            --esp-line: rgba(28, 39, 51, 0.16);
            --esp-cyan: #0e7490;
            --esp-teal: #0f766e;
            --esp-amber: #b45309;
            --esp-amber-deep: #92400e;
        }
        .dark {
            --esp-paper: #10151f;
            --esp-paper-2: #0f0f14;
            --esp-ink: #ede9dd;
            --esp-ink-soft: rgba(237, 233, 221, 0.72);
            --esp-line: rgba(237, 233, 221, 0.16);
            --esp-cyan: #22d3ee;
            --esp-teal: #2dd4bf;
            --esp-amber: #fbbf24;
            --esp-amber-deep: #fcd34d;
        }

        /* Poster typography. Marketing pages ship no webfont, so the
           condensed poster voice comes from system stacks: Arial Narrow
           where installed (weight 800 is synthesized there), otherwise
           the system face; font-stretch narrows variable system fonts
           and is a silent no-op everywhere else. */
        .es-poster-display {
            font-family: 'Arial Narrow', 'Helvetica Neue', ui-sans-serif, system-ui, sans-serif;
            font-weight: 800;
            font-stretch: 87.5%;
            text-transform: uppercase;
            letter-spacing: 0.005em;
            line-height: 0.92;
        }
        .es-poster-eyebrow {
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3em;
            color: var(--esp-ink-soft);
        }

        /* Two-color print misregistration on the H1 accent line */
        .es-poster-ink-offset {
            color: var(--esp-amber-deep);
            text-shadow: 0.045em 0.045em 0 rgba(14, 116, 144, 0.5);
        }
        .dark .es-poster-ink-offset {
            color: #fbbf24;
            text-shadow: 0.045em 0.045em 0 rgba(34, 211, 238, 0.35);
        }

        /* Third masked H1 line (marketing.css only defines delays for two) */
        html.es-anim .es-mask-3 .es-mask-line { animation-delay: 0.44s; }

        /* Halftone dot fields: static print texture, never animated */
        .es-poster-halftone {
            position: absolute;
            pointer-events: none;
            background-image: radial-gradient(circle, var(--esp-halftone-ink) 1.2px, transparent 1.7px);
            background-size: 12px 12px;
            -webkit-mask-image: radial-gradient(closest-side, black, transparent 78%);
            mask-image: radial-gradient(closest-side, black, transparent 78%);
        }
        .es-poster-halftone-cyan { --esp-halftone-ink: rgba(14, 116, 144, 0.16); }
        .dark .es-poster-halftone-cyan { --esp-halftone-ink: rgba(34, 211, 238, 0.10); }
        .es-poster-halftone-amber { --esp-halftone-ink: rgba(180, 83, 9, 0.15); }
        .dark .es-poster-halftone-amber { --esp-halftone-ink: rgba(251, 191, 36, 0.08); }
        /* Variants for the always-dark bands and finale */
        .es-poster-halftone-night { --esp-halftone-ink: rgba(34, 211, 238, 0.10); }
        .es-poster-halftone-night-amber { --esp-halftone-ink: rgba(251, 191, 36, 0.08); }

        /* Print crop marks in the hero corners */
        .es-poster-corner span {
            position: absolute;
            width: 1.5rem;
            height: 1.5rem;
            border: 0 solid var(--esp-line);
        }
        .es-poster-corner span:nth-child(1) { top: 1.25rem; left: 1.25rem; border-top-width: 2px; border-left-width: 2px; }
        .es-poster-corner span:nth-child(2) { top: 1.25rem; right: 1.25rem; border-top-width: 2px; border-right-width: 2px; }
        .es-poster-corner span:nth-child(3) { bottom: 1.25rem; left: 1.25rem; border-bottom-width: 2px; border-left-width: 2px; }
        .es-poster-corner span:nth-child(4) { bottom: 1.25rem; right: 1.25rem; border-bottom-width: 2px; border-right-width: 2px; }

        /* Star rule divider */
        .es-poster-rule {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .es-poster-rule::before,
        .es-poster-rule::after {
            content: "";
            height: 2px;
            flex: 1;
            background: var(--esp-line);
        }
        .es-poster-rule svg { color: var(--esp-amber); }

        /* Rubber stamps */
        .es-poster-stamp {
            display: inline-flex;
            align-items: center;
            padding: 0.3rem 0.7rem;
            border: 2px solid currentColor;
            border-radius: 0.375rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            font-size: 0.65rem;
            line-height: 1;
            transform: rotate(var(--stamp-rot, -3deg));
        }
        .es-poster-stamp-cyan { color: var(--esp-cyan); }
        .es-poster-stamp-teal { color: var(--esp-teal); }
        .es-poster-stamp-amber { color: var(--esp-amber-deep); }
        .es-poster-stamp-pin {
            position: absolute;
            top: 1.1rem;
            inset-inline-end: 1.1rem;
        }
        /* Bright inks on the always-dark mini flyers */
        .es-poster-block-night .es-poster-stamp-cyan { color: #22d3ee; }
        .es-poster-block-night .es-poster-stamp-teal { color: #2dd4bf; }
        .es-poster-block-night .es-poster-stamp-amber { color: #fbbf24; }

        /* Sawtooth tear strip along the dark bands' top edge. The tooth
           color must match the wrapper section behind the band. */
        .es-poster-tear {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 12px;
            pointer-events: none;
            --esp-tear: #f6f1e6;
            background:
                linear-gradient(135deg, var(--esp-tear) 50%, transparent 50%) 0 0 / 16px 12px repeat-x,
                linear-gradient(-135deg, var(--esp-tear) 50%, transparent 50%) 8px 0 / 16px 12px repeat-x;
        }
        .dark .es-poster-tear { --esp-tear: #0a0a0f; }

        /* Ticket perforation with punched side notches. The notch color
           is the surface BEHIND the ticket (set via --esp-perf-bg). */
        .es-poster-perf {
            position: relative;
            border-top: 2px dashed var(--esp-line);
        }
        .es-poster-perf::before,
        .es-poster-perf::after {
            content: "";
            position: absolute;
            top: -8px;
            width: 14px;
            height: 14px;
            border-radius: 9999px;
            background: var(--esp-perf-bg, var(--esp-paper));
        }
        .es-poster-perf::before { left: -7px; }
        .es-poster-perf::after { right: -7px; }

        /* Mini-poster blocks: 2px ink border + hard offset print shadow.
           No hover transform here; the bento tilt supplies the motion. */
        .es-poster-block {
            height: 100%;
            border: 2px solid var(--esp-ink);
            border-radius: 0.75rem;
            background: var(--esp-paper);
            box-shadow: 4px 4px 0 rgba(28, 39, 51, 0.85);
            transition: box-shadow 0.2s ease;
        }
        .dark .es-poster-block { box-shadow: 4px 4px 0 rgba(34, 211, 238, 0.28); }
        .es-bento:hover .es-poster-block { box-shadow: 6px 6px 0 rgba(28, 39, 51, 0.85); }
        .dark .es-bento:hover .es-poster-block { box-shadow: 6px 6px 0 rgba(34, 211, 238, 0.28); }
        .es-poster-block.es-poster-block-night {
            border-color: rgba(237, 233, 221, 0.35);
            background: rgba(255, 255, 255, 0.04);
            box-shadow: 4px 4px 0 rgba(34, 211, 238, 0.22);
        }

        /* Tour date stacks */
        .es-poster-daterow {
            border-bottom: 2px solid var(--esp-line);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            font-variant-numeric: tabular-nums;
        }
        .es-poster-datestack > .es-poster-daterow:first-child { border-top: 2px solid var(--esp-line); }

        /* Festival lineup type block */
        .es-poster-lineup { text-transform: uppercase; text-wrap: balance; line-height: 1.15; }
        .es-poster-lineup-t1 { color: var(--esp-ink); }
        .es-poster-lineup-t2 { color: var(--esp-cyan); }
        .es-poster-lineup-t3 { color: var(--esp-ink-soft); }

        /* Odometer ink: the shared .es-od-strip spans are brand blue in
           marketing.css, so both odometers get poster inks instead. */
        .es-poster-od .es-od-strip span {
            background: linear-gradient(180deg, var(--esp-amber-deep), var(--esp-amber));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .es-poster-block-night .es-od-strip span {
            background: linear-gradient(180deg, #fcd34d, #fbbf24);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* FAQ poster cards */
        .es-poster-faq[open] { box-shadow: 4px 4px 0 rgba(28, 39, 51, 0.15); }
        .dark .es-poster-faq[open] { box-shadow: 4px 4px 0 rgba(34, 211, 238, 0.15); }

        /* Backstage laminate: lanyard strap, clip ring, card, punched slot */
        .es-poster-lanyard {
            width: 26px;
            height: 96px;
            margin-inline: auto;
            background: repeating-linear-gradient(45deg, #155e75 0 10px, #0e7490 10px 20px);
            border-radius: 0 0 6px 6px;
        }
        .es-poster-lanyard-clip {
            width: 18px;
            height: 18px;
            margin: -4px auto 6px;
            border: 3px solid #ede9dd;
            border-radius: 9999px;
        }
        .es-poster-laminate {
            position: relative;
            overflow: hidden;
            border: 2px solid rgba(237, 233, 221, 0.4);
            border-radius: 1rem;
            background: linear-gradient(160deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.02));
        }
        .es-poster-laminate::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(115deg, rgba(255, 255, 255, 0.16) 0%, transparent 38%);
            pointer-events: none;
        }
        .es-poster-slot {
            width: 3.5rem;
            height: 0.625rem;
            border-radius: 9999px;
            background: #0a0a0f;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.8);
            margin-inline: auto;
        }
        @media (min-width: 1024px) {
            html.es-anim .es-poster-sway {
                animation: es-poster-sway 7s ease-in-out infinite alternate;
                transform-origin: 50% -60px;
            }
        }
        @keyframes es-poster-sway {
            from { transform: rotate(-1.2deg); }
            to   { transform: rotate(1.2deg); }
        }

        /* Hero micro equalizer: the musicians-owned motif at small scale.
           Static printed bars by default; they dance only with motion on. */
        .es-poster-eq {
            display: inline-flex;
            align-items: flex-end;
            gap: 2px;
            height: 14px;
        }
        .es-poster-eq i {
            width: 3px;
            height: 100%;
            border-radius: 2px;
            background: linear-gradient(180deg, var(--esp-cyan), var(--esp-teal));
            transform-origin: bottom;
            transform: scaleY(var(--s, 0.5));
        }
        html.es-anim .es-poster-eq i {
            animation: es-poster-eq 1.15s ease-in-out infinite;
            animation-delay: calc(var(--i, 0) * 0.14s);
        }
        @keyframes es-poster-eq {
            0%, 100% { transform: scaleY(0.3); }
            35% { transform: scaleY(1); }
            70% { transform: scaleY(0.55); }
        }

        /* Teal accent (recolored from the hard-coded blue) so the poster
           inks carry through the "See all" links and related-card hovers. */
        .es-accent-link { color: #0d9488; transition: color 0.2s ease; }
        .es-accent-link:hover { color: #0f766e; }
        .dark .es-accent-link { color: #2dd4bf; }
        .dark .es-accent-link:hover { color: #5eead4; }
        .es-related-card:hover {
            border-color: #5eead4;
            background-color: #f0fdfa;
        }
        .dark .es-related-card:hover {
            border-color: rgba(45, 212, 191, 0.3);
            background-color: rgba(45, 212, 191, 0.06);
        }
        .es-related-card:hover .es-related-title,
        .es-related-card:hover .es-related-arrow { color: #0d9488; }
        .dark .es-related-card:hover .es-related-title,
        .dark .es-related-card:hover .es-related-arrow { color: #2dd4bf; }

        @media (prefers-reduced-motion: reduce) {
            .es-poster-sway,
            .es-poster-eq i {
                animation: none !important;
            }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: the poster masthead                                 -->
    <!-- ============================================================ -->
    <section class="relative flex min-h-[calc(88svh-4rem)] items-center overflow-x-clip bg-[#f6f1e6] py-16 dark:bg-[#0a0a0f] noise">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-poster-halftone es-poster-halftone-cyan -left-16 -top-16 h-[24rem] w-[24rem]"></div>
            <div class="es-poster-halftone es-poster-halftone-amber -bottom-20 -right-16 h-[26rem] w-[26rem]"></div>
            <div class="es-poster-corner absolute inset-0 hidden sm:block"><span></span><span></span><span></span><span></span></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <p class="es-poster-eyebrow es-fade-up es-d-1 text-[11px] sm:text-xs">Event Schedule presents</p>
            <div class="es-poster-rule es-fade-up es-d-1 mx-auto mt-4 max-w-xs" aria-hidden="true">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.4 7.6L22 12l-7.6 2.4L12 22l-2.4-7.6L2 12l7.6-2.4L12 2z" /></svg>
            </div>
            <p class="es-fade-up es-d-1 mt-4 inline-flex items-center justify-center gap-3 text-[11px] font-bold uppercase tracking-[0.3em] text-[#0e7490] dark:text-[#22d3ee] sm:text-xs">
                For musicians, bands &amp; solo artists
                <span class="es-poster-eq" aria-hidden="true"><i style="--i: 0; --s: 0.4;"></i><i style="--i: 1; --s: 0.8;"></i><i style="--i: 2; --s: 0.55;"></i><i style="--i: 3; --s: 1;"></i><i style="--i: 4; --s: 0.68;"></i></span>
            </p>

            <h1 class="es-poster-display es-balance mt-6 text-5xl text-[#1c2733] dark:text-[#ede9dd] sm:text-7xl lg:text-[6.5rem]">
                <span class="es-mask"><span class="es-mask-line">Your gigs.</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line">Your fans.</span></span>
                <span class="es-mask es-mask-3"><span class="es-mask-line"><span class="es-poster-ink-offset">No middleman.</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mt-8 max-w-2xl text-lg text-[color:var(--esp-ink-soft)] sm:text-xl">
                One link for every show you play. Fans hear it from you, not from an algorithm. And when you sell tickets, the platform fee is zero.
            </p>

            <div class="es-fade-up es-d-3 mt-8 flex flex-wrap items-center justify-center gap-3">
                <span class="es-poster-stamp es-poster-stamp-teal" style="--stamp-rot: -3deg;">Free forever</span>
                <span class="es-poster-stamp es-poster-stamp-amber" style="--stamp-rot: 2deg;">No platform fees</span>
                <span class="es-poster-stamp es-poster-stamp-cyan" style="--stamp-rot: -2deg;">All ages</span>
            </div>

            <div class="es-fade-up es-d-3 mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ app_url('/sign_up?type=talent') }}" class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-cyan-600 to-teal-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-cyan-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-cyan-500/40">
                    Create your schedule
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="#features" class="group inline-flex items-center justify-center gap-2 rounded-2xl border-2 border-[color:var(--esp-ink)] px-7 py-4 text-lg font-semibold text-[color:var(--esp-ink)] transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                    See the bill
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
            </div>

            <!-- Printed tour-date teaser (flat poster art, not a mockup card) -->
            <div class="es-fade-up es-d-4 mx-auto mt-12 max-w-md" aria-hidden="true">
                <div class="es-poster-datestack text-left" dir="ltr">
                    <div class="es-poster-daterow grid grid-cols-[auto_1fr_auto] items-center gap-3 py-3 text-xs sm:text-sm">
                        <span class="es-poster-display w-14 text-[color:var(--esp-amber-deep)]">Jun 12</span>
                        <span class="truncate font-bold text-[color:var(--esp-ink)]">The Roxy &middot; Los Angeles</span>
                        <span class="es-poster-stamp es-poster-stamp-teal" style="--stamp-rot: 0deg;">Tickets</span>
                    </div>
                    <div class="es-poster-daterow grid grid-cols-[auto_1fr_auto] items-center gap-3 py-3 text-xs sm:text-sm">
                        <span class="es-poster-display w-14 text-[color:var(--esp-amber-deep)]">Jun 14</span>
                        <span class="truncate font-bold text-[color:var(--esp-ink)]">Mercury Lounge &middot; New York</span>
                        <span class="es-poster-stamp es-poster-stamp-teal" style="--stamp-rot: 0deg;">Tickets</span>
                    </div>
                    <div class="es-poster-daterow grid grid-cols-[auto_1fr_auto] items-center gap-3 py-3 text-xs sm:text-sm">
                        <span class="es-poster-display w-14 text-[color:var(--esp-amber-deep)]">Jun 20</span>
                        <span class="truncate font-bold text-[color:var(--esp-ink)]">The Crocodile &middot; Seattle</span>
                        <span class="es-poster-stamp es-poster-stamp-amber" style="--stamp-rot: -4deg;">Sold out</span>
                    </div>
                </div>
            </div>

            <!-- Genre marquee -->
            <div class="es-fade-up es-d-5 pointer-events-auto mx-auto mt-12 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($genreCopy = 0; $genreCopy < 2; $genreCopy++)
                                @foreach (['Rock', 'Jazz', 'Folk', 'Blues', 'Classical', 'Country', 'Indie', 'Metal', 'Hip-Hop', 'Electronic', 'Acoustic', 'Punk', 'Soul', 'Reggae'] as $genre)
                                    <span @if ($genreCopy === 1) aria-hidden="true" @endif class="inline-flex items-center gap-2 rounded-md border-2 border-[color:var(--esp-line)] px-4 py-1.5 text-xs font-bold uppercase tracking-[0.12em] text-[color:var(--esp-ink-soft)]">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-cyan-600 to-teal-600 dark:from-cyan-400 dark:to-teal-400" aria-hidden="true"></span>
                                        {{ $genre }}
                                    </span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The problem: great show, empty room                       -->
    <!-- ============================================================ -->
    <section class="relative bg-[#f6f1e6] px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="es-poster-tear" aria-hidden="true"></div>
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-poster-halftone es-poster-halftone-night -top-10 left-1/4 h-[20rem] w-[36rem]"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="mb-6 inline-flex" data-reveal>
                        <span class="es-poster-stamp text-[#fbbf24]" style="--stamp-rot: -2deg;">The problem</span>
                    </div>
                    <h2 class="es-poster-display es-balance text-4xl text-white md:text-6xl" data-reveal style="--reveal-delay: 0.08s;">
                        Great show. <span class="text-[#fbbf24]">Empty room.</span>
                    </h2>
                    <p class="mt-4 text-lg text-gray-400" data-reveal style="--reveal-delay: 0.16s;">
                        You played your heart out. Your fans found out on Monday.
                    </p>
                </div>

                <div class="grid gap-6 pt-2 md:grid-cols-3" data-reveal-group="120">
                    <div class="es-poster-block es-poster-block-night flex flex-col items-center p-7 text-center" data-reveal="panel">
                        <div class="es-poster-display mb-2 text-4xl text-[#22d3ee]">Most</div>
                        <p class="text-sm text-gray-400">of your social media followers never see your posts about shows</p>
                        <span class="es-poster-stamp es-poster-stamp-cyan mt-5" style="--stamp-rot: -2deg;">Buried by the feed</span>
                    </div>
                    <div class="es-poster-block es-poster-block-night flex flex-col items-center p-7 text-center" data-reveal="panel">
                        <div class="es-poster-display mb-2 text-4xl text-[#2dd4bf]">Too late</div>
                        <p class="text-sm text-gray-400">fans only hear about your shows after they've happened</p>
                        <span class="es-poster-stamp es-poster-stamp-teal mt-5" style="--stamp-rot: 2deg;">Missed it</span>
                    </div>
                    <div class="es-poster-block es-poster-block-night flex flex-col items-center p-7 text-center" data-reveal="panel">
                        <div class="es-od es-poster-display mb-2 justify-center text-4xl text-[#fbbf24]" data-odometer="10-20%">10-20%</div>
                        <p class="text-sm text-gray-400">of ticket revenue lost to platform fees elsewhere. Event Schedule charges zero.</p>
                        <span class="es-poster-stamp es-poster-stamp-amber mt-5" style="--stamp-rot: -3deg;">Gone</span>
                    </div>
                </div>

                <p class="mt-10 text-center text-gray-400" data-reveal>
                    Your fans deserve a better flyer.
                    <a href="#features" class="inline-flex items-center gap-1 font-semibold text-[#67e8f9] transition-all hover:gap-2">
                        Here is the fix
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Feature poster wall: on the bill                          -->
    <!-- ============================================================ -->
    <section id="features" class="scroll-mt-24 overflow-x-clip bg-[#efe7d8] py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="mb-6 inline-flex" data-reveal>
                    <span class="es-poster-stamp es-poster-stamp-cyan" style="--stamp-rot: -2deg;">On the bill</span>
                </div>
                <h2 class="es-poster-display es-balance mb-5 text-4xl text-[color:var(--esp-ink)] md:text-5xl lg:text-6xl" data-reveal style="--reveal-delay: 0.08s;">
                    Everything a working musician needs
                </h2>
                <div class="es-poster-rule mx-auto max-w-[12rem]" aria-hidden="true" data-reveal style="--reveal-delay: 0.16s;">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.4 7.6L22 12l-7.6 2.4L12 22l-2.4-7.6L2 12l7.6-2.4L12 2z" /></svg>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">

                <!-- 1. Newsletters: direct to the fans (spans 2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner es-poster-block relative flex h-full flex-col overflow-hidden p-7 lg:p-9">
                        <span class="es-poster-stamp es-poster-stamp-cyan es-poster-stamp-pin" style="--stamp-rot: 3deg;">No algorithm</span>
                        <div class="relative flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-4 text-[10px] font-bold uppercase tracking-[0.3em] text-[#0e7490] dark:text-[#22d3ee]">Newsletters</div>
                                <h3 class="es-poster-display mb-4 text-3xl text-[color:var(--esp-ink)] lg:text-4xl">Direct to the fans</h3>
                                <p class="mb-6 text-lg text-[color:var(--esp-ink-soft)]">New tour on sale? Say it straight to the inbox. Followers get your dates the moment you post them, and Pro auto-generates social-ready show art for every gig.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-md border-2 border-[color:var(--esp-line)] px-3 py-1 text-xs font-bold uppercase tracking-[0.12em] text-[color:var(--esp-ink-soft)]">Tour announcements</span>
                                    <span class="inline-flex items-center rounded-md border-2 border-[color:var(--esp-line)] px-3 py-1 text-xs font-bold uppercase tracking-[0.12em] text-[color:var(--esp-ink-soft)]">Album releases</span>
                                    <span class="inline-flex items-center rounded-md border-2 border-[color:var(--esp-line)] px-3 py-1 text-xs font-bold uppercase tracking-[0.12em] text-[color:var(--esp-ink-soft)]">Show reminders</span>
                                </div>
                            </div>
                            <div class="w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="max-w-xs rounded-xl border-2 border-[color:var(--esp-line)] bg-[#fdfcf8] p-4 dark:bg-[#171c26]">
                                    <div class="mb-3 flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-md bg-gradient-to-br from-cyan-600 to-teal-600 text-sm font-bold text-white">MH</div>
                                        <div>
                                            <div class="text-sm font-bold text-[color:var(--esp-ink)]">The Midnight Hour</div>
                                            <div class="text-xs font-semibold uppercase tracking-[0.12em] text-[#0e7490] dark:text-[#22d3ee]">Summer tour on sale now</div>
                                        </div>
                                    </div>
                                    <div class="rounded-lg border-2 border-dashed border-[color:var(--esp-line)] p-3 text-center">
                                        <div class="es-poster-display mb-1 text-xs text-[color:var(--esp-ink-soft)]">This Saturday</div>
                                        <div class="es-poster-display text-sm text-[#92400e] dark:text-[#fbbf24]">Live at The Roxy</div>
                                        <div class="mt-1 text-[10px] uppercase tracking-[0.12em] text-[color:var(--esp-ink-soft)]">Doors 7 PM &middot; $25</div>
                                    </div>
                                    <div class="mt-3 flex gap-4 text-xs">
                                        <div class="text-[color:var(--esp-ink-soft)]"><span class="font-bold text-[#0f766e] dark:text-[#2dd4bf]"><span data-count-to="72">72</span>%</span> opened</div>
                                        <div class="text-[color:var(--esp-ink-soft)]"><span class="font-bold text-[#92400e] dark:text-[#fbbf24]"><span data-count-to="31">31</span>%</span> clicked</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2. Zero-fee tickets -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner es-poster-block relative flex h-full flex-col overflow-hidden p-7">
                        <div class="mb-4 text-[10px] font-bold uppercase tracking-[0.3em] text-[#0e7490] dark:text-[#22d3ee]">Ticketing</div>
                        <h3 class="es-poster-display mb-3 text-2xl text-[color:var(--esp-ink)]">Zero-fee tickets</h3>
                        <p class="mb-6 text-[color:var(--esp-ink-soft)]">Connect Stripe and sell pre-sales or door tickets with QR check-in. Promo codes for the fan club, waitlists for the sellouts.</p>

                        <div class="es-poster-od relative mt-auto rounded-xl border-2 border-[color:var(--esp-line)] bg-[#fdfcf8] p-4 font-mono text-xs dark:bg-[#171c26]" dir="ltr" aria-hidden="true">
                            <div class="mb-1 flex justify-between text-[color:var(--esp-ink)]"><span>GA TICKET x2</span><span>$50.00</span></div>
                            <div class="mb-3 flex justify-between font-bold"><span class="text-[color:var(--esp-ink)]">PLATFORM FEE</span><span class="text-[#0f766e] dark:text-[#2dd4bf]">$0.00</span></div>
                            <div class="es-poster-perf -mx-4 mb-3"></div>
                            <div class="flex items-end justify-between gap-3">
                                <div>
                                    <div class="text-[10px] uppercase tracking-[0.18em] text-[color:var(--esp-ink-soft)]">You keep</div>
                                    <div class="es-od es-poster-display text-3xl text-[#92400e] dark:text-[#fbbf24]" data-odometer="100%">100%</div>
                                    <div class="text-[10px] text-[color:var(--esp-ink-soft)]">of ticket sales</div>
                                </div>
                                <svg class="h-10 w-10 text-[color:var(--esp-ink)]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M3 3h8v8H3V3zm2 2v4h4V5H5zm8-2h8v8h-8V3zm2 2v4h4V5h-4zM3 13h8v8H3v-8zm2 2v4h4v-4H5zm10-2h2v2h-2v-2zm4 0h2v2h-2v-2zm-4 4h2v2h-2v-2zm4 4h2v2h-2v-2zm-4 0h2v2h-2v-2zm4-4h2v2h-2v-2z" />
                                </svg>
                            </div>
                        </div>
                        <span class="es-poster-stamp es-poster-stamp-amber es-poster-stamp-pin" style="--stamp-rot: 3deg;">No fees ever</span>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3. One link for everything -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner es-poster-block relative flex h-full flex-col overflow-hidden p-7">
                        <div class="mb-4 text-[10px] font-bold uppercase tracking-[0.3em] text-[#0e7490] dark:text-[#22d3ee]">Share link</div>
                        <h3 class="es-poster-display mb-3 text-2xl text-[color:var(--esp-ink)]">One link. Every show.</h3>
                        <p class="mb-6 text-[color:var(--esp-ink-soft)]">Put it in your Spotify bio, Bandcamp page, and EPK. You can embed the calendar on your own website too.</p>

                        <div class="mt-auto rounded-xl border-2 border-[color:var(--esp-line)] bg-[#fdfcf8] p-4 dark:bg-[#171c26]" aria-hidden="true">
                            <div class="mb-3 flex items-center gap-2 rounded-lg border-2 border-dashed border-[color:var(--esp-line)] p-2" dir="ltr">
                                <svg aria-hidden="true" class="h-4 w-4 shrink-0 text-[#0e7490] dark:text-[#22d3ee]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                                <span class="truncate font-mono text-xs font-semibold text-[color:var(--esp-ink)]">yourband.eventschedule.com</span>
                            </div>
                            <div class="grid grid-cols-2 gap-1.5 text-center">
                                <div class="rounded-md border-2 border-[color:var(--esp-line)] p-1.5 text-[10px] font-bold uppercase tracking-[0.12em] text-[color:var(--esp-ink-soft)]">Spotify</div>
                                <div class="rounded-md border-2 border-[color:var(--esp-line)] p-1.5 text-[10px] font-bold uppercase tracking-[0.12em] text-[color:var(--esp-ink-soft)]">Bandcamp</div>
                                <div class="rounded-md border-2 border-[color:var(--esp-line)] p-1.5 text-[10px] font-bold uppercase tracking-[0.12em] text-[color:var(--esp-ink-soft)]">EPK</div>
                                <div class="rounded-md border-2 border-[color:var(--esp-line)] p-1.5 text-[10px] font-bold uppercase tracking-[0.12em] text-[color:var(--esp-ink-soft)]">Your site</div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4. Venue sync + team + AI (spans 2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner es-poster-block relative flex h-full flex-col overflow-hidden p-7 lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-4 text-[10px] font-bold uppercase tracking-[0.3em] text-[#0e7490] dark:text-[#22d3ee]">Venue sync &middot; Team</div>
                                <h3 class="es-poster-display mb-4 text-3xl text-[color:var(--esp-ink)]">Booked once. Printed twice.</h3>
                                <p class="mb-5 text-lg text-[color:var(--esp-ink-soft)]">When a venue books you on Event Schedule, the gig lands on your poster automatically. One booking, both schedules.</p>
                                <p class="mb-5 text-[color:var(--esp-ink-soft)]">Give your band, manager, and booking agent access. And when a booking email lands, forward it and AI turns it into a listed gig.</p>
                                <div class="flex flex-wrap gap-3" aria-hidden="true">
                                    <span class="es-ai-field es-poster-stamp es-poster-stamp-amber" style="--i: 0; --stamp-rot: -2deg;">Lead</span>
                                    <span class="es-ai-field es-poster-stamp es-poster-stamp-cyan" style="--i: 1; --stamp-rot: 1.5deg;">Manager</span>
                                    <span class="es-ai-field es-poster-stamp es-poster-stamp-teal" style="--i: 2; --stamp-rot: -1deg;">Agent</span>
                                </div>
                            </div>
                            <div class="relative flex items-center justify-center py-4" aria-hidden="true">
                                <div class="relative flex items-center gap-10">
                                    <div class="w-32 rounded-lg border-2 border-[color:var(--esp-line)] bg-[#fdfcf8] p-4 dark:bg-[#171c26]">
                                        <div class="es-poster-display mb-2 text-center text-[10px] text-[#0e7490] dark:text-[#22d3ee]">The venue's bill</div>
                                        <div class="space-y-1.5">
                                            <div class="h-2 rounded bg-[color:var(--esp-line)]"></div>
                                            <div class="h-2 w-3/4 rounded bg-cyan-600/40 dark:bg-cyan-400/40"></div>
                                            <div class="h-2 w-1/2 rounded bg-[color:var(--esp-line)]"></div>
                                        </div>
                                        <div class="mt-3 rounded-md border-2 border-dashed border-[color:var(--esp-line)] p-2">
                                            <div class="text-center text-[10px] font-bold uppercase tracking-[0.12em] text-[color:var(--esp-ink)]">+ Your band</div>
                                        </div>
                                    </div>
                                    <div class="absolute left-1/2 top-1/2 h-px w-16 -translate-x-1/2 -translate-y-1/2 border-t-2 border-dashed border-[color:var(--esp-line)]"></div>
                                    <div class="es-sync-dot"></div>
                                    <div class="w-32 rounded-lg border-2 border-[color:var(--esp-ink)] bg-[#fdfcf8] p-4 dark:bg-[#171c26]">
                                        <div class="es-poster-display mb-2 text-center text-[10px] text-[color:var(--esp-ink)]">Your poster</div>
                                        <div class="space-y-1.5">
                                            <div class="h-2 rounded bg-[color:var(--esp-line)]"></div>
                                            <div class="h-2 w-3/4 rounded bg-teal-600/40 dark:bg-teal-400/40"></div>
                                            <div class="h-2 w-1/2 rounded bg-[color:var(--esp-line)]"></div>
                                        </div>
                                        <div class="mt-3 rounded-md border-2 border-[color:var(--esp-line)] bg-teal-600/10 p-2 dark:bg-teal-400/10">
                                            <div class="text-center text-[10px] font-bold uppercase tracking-[0.12em] text-[#0f766e] dark:text-[#2dd4bf]">New gig!</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5. Calendar sync + residencies -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner es-poster-block relative flex h-full flex-col overflow-hidden p-7">
                        <div class="mb-4 text-[10px] font-bold uppercase tracking-[0.3em] text-[#0e7490] dark:text-[#22d3ee]">Calendar sync</div>
                        <h3 class="es-poster-display mb-3 text-2xl text-[color:var(--esp-ink)]">The road book</h3>
                        <p class="mb-6 text-[color:var(--esp-ink-soft)]">Two-way sync with Google Calendar, Outlook, or CalDAV. Weekly residency? Set the pattern once and it repeats, minus the weeks you skip.</p>

                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="flex items-center justify-between rounded-md border-2 border-[color:var(--esp-line)] px-3 py-2 text-[10px] font-bold uppercase tracking-[0.18em]">
                                <span class="text-[color:var(--esp-ink)]">Thu</span>
                                <span class="text-[#92400e] dark:text-[#fbbf24]">Gig &middot; Doors 8 PM</span>
                            </div>
                            <div class="flex items-center justify-between rounded-md border-2 border-[color:var(--esp-line)] px-3 py-2 text-[10px] font-bold uppercase tracking-[0.18em]">
                                <span class="text-[color:var(--esp-ink)]">Fri</span>
                                <span class="text-[#0e7490] dark:text-[#22d3ee]">Rehearsal</span>
                            </div>
                            <div class="flex items-center justify-between rounded-md border-2 border-[color:var(--esp-line)] px-3 py-2 text-[10px] font-bold uppercase tracking-[0.18em]">
                                <span class="text-[color:var(--esp-ink)]">Sun</span>
                                <span class="text-[#0f766e] dark:text-[#2dd4bf]">Studio session</span>
                            </div>
                            <div class="flex items-center justify-center gap-2 pt-1">
                                <svg aria-hidden="true" class="h-4 w-4 animate-pulse-slow text-[#0e7490] dark:text-[#22d3ee]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                </svg>
                                <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-[color:var(--esp-ink-soft)]">Google &middot; Outlook &middot; CalDAV</span>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6. Followers + fan content (spans 2 cols on lg) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner es-poster-block relative flex h-full flex-col overflow-hidden p-7 lg:p-9">
                        <div class="relative flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-4 text-[10px] font-bold uppercase tracking-[0.3em] text-[#0e7490] dark:text-[#22d3ee]">Followers</div>
                                <h3 class="es-poster-display mb-4 text-3xl text-[color:var(--esp-ink)]">The street team</h3>
                                <p class="text-lg text-[color:var(--esp-ink-soft)]">Fans follow your schedule and get notified when you play near them. After the show they post clips, photos, and comments, and nothing goes live until you approve it.</p>
                            </div>
                            <div class="w-full shrink-0 lg:w-64" aria-hidden="true">
                                <div class="rounded-xl border-2 border-[color:var(--esp-line)] bg-[#fdfcf8] p-4 text-center dark:bg-[#171c26]">
                                    <div class="flex items-center justify-center">
                                        <div class="flex -space-x-2">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-[#fdfcf8] bg-gradient-to-br from-cyan-600 to-teal-600 text-xs text-white dark:border-[#171c26]">A</div>
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-[#fdfcf8] bg-gradient-to-br from-teal-600 to-emerald-600 text-xs text-white dark:border-[#171c26]">B</div>
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-[#fdfcf8] bg-gradient-to-br from-amber-600 to-orange-600 text-xs text-white dark:border-[#171c26]">C</div>
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-[#fdfcf8] bg-[color:var(--esp-line)] text-xs font-bold text-[color:var(--esp-ink)] dark:border-[#171c26]">+127</div>
                                        </div>
                                    </div>
                                    <div class="es-poster-display mt-3 text-sm text-[#0f766e] dark:text-[#2dd4bf]"><span data-count-to="130">130</span> fans following</div>
                                    <div class="mt-1 text-[10px] uppercase tracking-[0.12em] text-[color:var(--esp-ink-soft)]">Notified when you play nearby</div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. The routing: career journey as a tour-date stack          -->
    <!-- ============================================================ -->
    <section class="overflow-x-clip bg-[#f6f1e6] py-20 dark:bg-[#0a0a0f] lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="mb-6 inline-flex" data-reveal>
                    <span class="es-poster-stamp es-poster-stamp-teal" style="--stamp-rot: -2deg;">The routing</span>
                </div>
                <h2 class="es-poster-display es-balance mb-4 text-4xl text-[color:var(--esp-ink)] md:text-5xl lg:text-6xl" data-reveal style="--reveal-delay: 0.08s;">
                    From open mics to festival stages
                </h2>
                <p class="text-lg text-[color:var(--esp-ink-soft)]" data-reveal style="--reveal-delay: 0.16s;">
                    One schedule that grows with your career.
                </p>
            </div>

            @php
                $tourStops = [
                    ['title' => 'Open mic nights', 'desc' => 'Coffee shops and local bars. Track your spots and let friends know where to catch you.'],
                    ['title' => 'Local gigging', 'desc' => 'Regular slots at venues around your city. Build a local following and start selling your own tickets.'],
                    ['title' => 'Regional tours', 'desc' => 'Weekend runs and opening slots. Fans in nearby cities follow along and know when you\'re coming through.'],
                    ['title' => 'Headlining', 'desc' => 'Your name at the top of the bill. Email your fans directly and sell out your own shows.'],
                    ['title' => 'National tours', 'desc' => 'Multi-city runs across the country. One link tells fans everywhere when you\'re hitting their town.'],
                    ['title' => 'Festivals & special events', 'desc' => 'Festival slots, album release shows, and one-off specials, all on one professional schedule.'],
                ];
            @endphp
            <div class="es-poster-datestack" data-reveal-group="90">
                @foreach ($tourStops as $stopIndex => $stop)
                    <div class="es-poster-daterow py-5" data-reveal>
                        <div class="flex flex-wrap items-baseline gap-x-4 gap-y-1">
                            <span class="es-poster-display w-20 shrink-0 text-sm text-[color:var(--esp-cyan)]">Stop {{ str_pad($stopIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-poster-display text-xl text-[color:var(--esp-ink)] sm:text-2xl">{{ $stop['title'] }}</span>
                            @if ($stopIndex === count($tourStops) - 1)
                                <span class="es-poster-stamp es-poster-stamp-amber ltr:ml-auto rtl:mr-auto" style="--stamp-rot: -3deg;">Headliner</span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm normal-case tracking-normal text-[color:var(--esp-ink-soft)] ltr:sm:pl-24 rtl:sm:pr-24">{{ $stop['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Festival lineup: perfect for                              -->
    <!-- ============================================================ -->
    <section class="bg-[#efe7d8] py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-10 max-w-3xl text-center">
                <h2 class="es-poster-display es-balance mb-4 text-4xl text-[color:var(--esp-ink)] md:text-5xl lg:text-6xl" data-reveal>
                    Perfect for every kind of musician
                </h2>
                <p class="text-lg text-[color:var(--esp-ink-soft)]" data-reveal style="--reveal-delay: 0.1s;">
                    Whether you're a solo artist or a touring band, Event Schedule works for you.
                </p>
            </div>

            <!-- Festival-poster lineup block (decorative; the cards below carry the content) -->
            <div class="es-poster-lineup mx-auto mb-14 max-w-3xl text-center" aria-hidden="true" data-reveal>
                <div class="es-poster-display es-poster-lineup-t1 text-3xl sm:text-5xl">Solo artists</div>
                <div class="es-poster-rule mx-auto my-3 max-w-[10rem]">
                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.4 7.6L22 12l-7.6 2.4L12 22l-2.4-7.6L2 12l7.6-2.4L12 2z" /></svg>
                </div>
                <div class="es-poster-display es-poster-lineup-t2 text-xl sm:text-3xl">Rock &amp; pop bands &middot; Jazz musicians</div>
                <div class="es-poster-rule mx-auto my-3 max-w-[10rem]">
                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.4 7.6L22 12l-7.6 2.4L12 22l-2.4-7.6L2 12l7.6-2.4L12 2z" /></svg>
                </div>
                <div class="es-poster-display es-poster-lineup-t3 text-base sm:text-xl">Cover bands &middot; Tribute acts &middot; Session musicians</div>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">
                <div data-reveal>
                    <x-sub-audience-card
                        name="Solo Artists"
                        description="Share your acoustic nights, open mics, and solo performances with your growing fanbase."
                        icon-color="cyan"
                        blog-slug="for-solo-artists"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </x-slot:icon>
                    </x-sub-audience-card>
                </div>

                <div data-reveal>
                    <x-sub-audience-card
                        name="Rock & Pop Bands"
                        description="Coordinate your tour dates across the whole band and let fans follow along."
                        icon-color="teal"
                        blog-slug="for-rock-pop-bands"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                            </svg>
                        </x-slot:icon>
                    </x-sub-audience-card>
                </div>

                <div data-reveal>
                    <x-sub-audience-card
                        name="Jazz Musicians"
                        description="List your residencies, jam sessions, and special performances at clubs and festivals."
                        icon-color="indigo"
                        blog-slug="for-jazz-musicians"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                        </x-slot:icon>
                    </x-sub-audience-card>
                </div>

                <div data-reveal>
                    <x-sub-audience-card
                        name="Cover Bands"
                        description="Show your weekly bar gigs and private events all in one professional calendar."
                        icon-color="violet"
                        blog-slug="for-cover-bands"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        </x-slot:icon>
                    </x-sub-audience-card>
                </div>

                <div data-reveal>
                    <x-sub-audience-card
                        name="Tribute Acts"
                        description="Build a dedicated fanbase for your tribute shows and special themed events."
                        icon-color="purple"
                        blog-slug="for-tribute-acts"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                        </x-slot:icon>
                    </x-sub-audience-card>
                </div>

                <div data-reveal>
                    <x-sub-audience-card
                        name="Session Musicians"
                        description="Show your availability and let bands know when you're free for gigs and recording sessions."
                        icon-color="amber"
                        blog-slug="for-session-musicians"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-sub-audience-card>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Run of show: how it works                                 -->
    <!-- ============================================================ -->
    <section class="relative bg-[#f6f1e6] px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="es-poster-tear" aria-hidden="true"></div>
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-poster-halftone es-poster-halftone-night-amber -top-10 right-1/4 h-[20rem] w-[36rem]"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-4xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="mb-6 inline-flex" data-reveal>
                        <span class="es-poster-stamp text-[#2dd4bf]" style="--stamp-rot: -2deg;">Run of show</span>
                    </div>
                    <h2 class="es-poster-display es-balance text-4xl text-white md:text-6xl" data-reveal style="--reveal-delay: 0.08s;">
                        Three steps to showtime
                    </h2>
                </div>

                <div class="space-y-8" data-reveal-group="120">
                    <div class="grid gap-3 sm:grid-cols-[9rem_1fr] sm:gap-8" data-reveal>
                        <div class="sm:ltr:text-right sm:rtl:text-left">
                            <div class="es-poster-display text-2xl text-[#fbbf24]">Doors</div>
                            <div class="text-xs font-bold tracking-[0.3em] text-gray-500">01</div>
                        </div>
                        <div>
                            <h3 class="mb-2 text-lg font-semibold text-white">Add your gigs</h3>
                            <p class="text-sm text-gray-400">Import from Google Calendar or add tour dates manually. Set up ticket sales if you want.</p>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-[9rem_1fr] sm:gap-8" data-reveal>
                        <div class="sm:ltr:text-right sm:rtl:text-left">
                            <div class="es-poster-display text-2xl text-[#22d3ee]">Soundcheck</div>
                            <div class="text-xs font-bold tracking-[0.3em] text-gray-500">02</div>
                        </div>
                        <div>
                            <h3 class="mb-2 text-lg font-semibold text-white">Share your link</h3>
                            <p class="text-sm text-gray-400">Add it to your Spotify bio, Bandcamp, EPK, or anywhere fans find you.</p>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-[9rem_1fr] sm:gap-8" data-reveal>
                        <div class="sm:ltr:text-right sm:rtl:text-left">
                            <div class="es-poster-display text-2xl text-[#2dd4bf]">Showtime</div>
                            <div class="text-xs font-bold tracking-[0.3em] text-gray-500">03</div>
                        </div>
                        <div>
                            <h3 class="mb-2 text-lg font-semibold text-white">Grow your fanbase</h3>
                            <p class="text-sm text-gray-400">Fans follow your schedule, get notified about shows near them, and share videos and comments after your gigs (all approved by you before going live). Build your audience on your terms.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Key features: also on the bill                            -->
    <!-- ============================================================ -->
    <section class="overflow-x-clip bg-[#efe7d8] py-20 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8 text-center">
                <div class="mb-5 inline-flex" data-reveal>
                    <span class="es-poster-stamp es-poster-stamp-cyan" style="--stamp-rot: 2deg;">Also on the bill</span>
                </div>
                <h2 class="es-poster-display text-3xl text-[color:var(--esp-ink)] md:text-4xl" data-reveal style="--reveal-delay: 0.08s;">Key features</h2>
            </div>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card
                        name="Ticketing"
                        description="Sell tickets with QR check-in and zero platform fees"
                        :url="marketing_url('/features/ticketing')"
                        icon-color="teal"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Newsletters"
                        description="Send event updates directly to followers' inboxes"
                        :url="marketing_url('/features/newsletters')"
                        icon-color="teal"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Calendar Sync"
                        description="Two-way sync with Google Calendar"
                        :url="marketing_url('/features/calendar-sync')"
                        icon-color="teal"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Boost"
                        description="Promote events with Facebook and Instagram ads"
                        :url="marketing_url('/features/boost')"
                        icon-color="teal"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="inline-flex items-center font-medium es-accent-link hover:underline">
                    See all features
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    @include('marketing.partials.pricing-nudge')

    <!-- ============================================================ -->
    <!-- 8. Related pages: other stages                               -->
    <!-- ============================================================ -->
    <section class="bg-[#f6f1e6] py-20 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8 text-center">
                <div class="mb-5 inline-flex" data-reveal>
                    <span class="es-poster-stamp es-poster-stamp-teal" style="--stamp-rot: -2deg;">Other stages</span>
                </div>
                <h2 class="es-poster-display text-3xl text-[color:var(--esp-ink)] md:text-4xl" data-reveal style="--reveal-delay: 0.08s;">Related pages</h2>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                <a href="{{ marketing_url('/for-comedians') }}" data-reveal class="group flex items-center justify-between rounded-xl border-2 border-[color:var(--esp-line)] bg-white/50 p-5 transition-all hover:-translate-y-0.5 hover:shadow-md dark:bg-white/5 es-related-card">
                    <div>
                        <div class="text-sm text-[color:var(--esp-ink-soft)]">Event Schedule for</div>
                        <div class="text-lg font-semibold text-[color:var(--esp-ink)] transition-colors es-related-title">Comedians</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 transition-colors es-related-arrow rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-djs') }}" data-reveal class="group flex items-center justify-between rounded-xl border-2 border-[color:var(--esp-line)] bg-white/50 p-5 transition-all hover:-translate-y-0.5 hover:shadow-md dark:bg-white/5 es-related-card">
                    <div>
                        <div class="text-sm text-[color:var(--esp-ink-soft)]">Event Schedule for</div>
                        <div class="text-lg font-semibold text-[color:var(--esp-ink)] transition-colors es-related-title">DJs</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 transition-colors es-related-arrow rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-spoken-word') }}" data-reveal class="group flex items-center justify-between rounded-xl border-2 border-[color:var(--esp-line)] bg-white/50 p-5 transition-all hover:-translate-y-0.5 hover:shadow-md dark:bg-white/5 es-related-card">
                    <div>
                        <div class="text-sm text-[color:var(--esp-ink-soft)]">Event Schedule for</div>
                        <div class="text-lg font-semibold text-[color:var(--esp-ink)] transition-colors es-related-title">Spoken Word Artists</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 transition-colors es-related-arrow rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-dance-groups') }}" data-reveal class="group flex items-center justify-between rounded-xl border-2 border-[color:var(--esp-line)] bg-white/50 p-5 transition-all hover:-translate-y-0.5 hover:shadow-md dark:bg-white/5 es-related-card">
                    <div>
                        <div class="text-sm text-[color:var(--esp-ink-soft)]">Event Schedule for</div>
                        <div class="text-lg font-semibold text-[color:var(--esp-ink)] transition-colors es-related-title">Dance Groups</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 transition-colors es-related-arrow rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="inline-flex items-center font-medium es-accent-link hover:underline">
                    See all use cases
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. FAQ: the fine print                                       -->
    <!-- ============================================================ -->
    <section class="overflow-x-clip bg-[#efe7d8] py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="mb-6 inline-flex" data-reveal>
                    <span class="es-poster-stamp es-poster-stamp-amber" style="--stamp-rot: -2deg;">The fine print</span>
                </div>
                <h2 class="es-poster-display es-balance mb-4 text-4xl text-[color:var(--esp-ink)] md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                    Frequently asked questions
                </h2>
                <p class="text-lg text-[color:var(--esp-ink-soft)]" data-reveal style="--reveal-delay: 0.16s;">
                    Everything musicians ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                <details name="faq" data-reveal class="group/faq es-poster-faq overflow-hidden rounded-xl border-2 border-[color:var(--esp-line)] bg-[color:var(--esp-paper)] transition-shadow">
                    <summary class="flex cursor-pointer items-center justify-between p-6">
                        <h3 class="text-lg font-semibold text-[color:var(--esp-ink)]">
                            Is Event Schedule free for musicians?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 shrink-0 text-[color:var(--esp-ink-soft)] transition-transform duration-300 group-open/faq:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <p class="faq-answer px-6 pb-6 text-[color:var(--esp-ink-soft)]">
                        Yes. Event Schedule is free forever for sharing your gig schedule, building a fan following, and syncing with Google Calendar. Ticketing and newsletters are available on the Pro and Enterprise plans, with no platform fees on ticket sales.
                    </p>
                </details>

                <details name="faq" data-reveal class="group/faq es-poster-faq overflow-hidden rounded-xl border-2 border-[color:var(--esp-line)] bg-[color:var(--esp-paper)] transition-shadow">
                    <summary class="flex cursor-pointer items-center justify-between p-6">
                        <h3 class="text-lg font-semibold text-[color:var(--esp-ink)]">
                            How do fans find out about my upcoming shows?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 shrink-0 text-[color:var(--esp-ink-soft)] transition-transform duration-300 group-open/faq:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <p class="faq-answer px-6 pb-6 text-[color:var(--esp-ink-soft)]">
                        Fans can follow your schedule and receive email notifications when you add new shows. You can also send newsletters directly to followers with your upcoming dates, and share your schedule link on Spotify, Bandcamp, your EPK, or any social profile.
                    </p>
                </details>

                <details name="faq" data-reveal class="group/faq es-poster-faq overflow-hidden rounded-xl border-2 border-[color:var(--esp-line)] bg-[color:var(--esp-paper)] transition-shadow">
                    <summary class="flex cursor-pointer items-center justify-between p-6">
                        <h3 class="text-lg font-semibold text-[color:var(--esp-ink)]">
                            Can I sell tickets to my own shows?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 shrink-0 text-[color:var(--esp-ink-soft)] transition-transform duration-300 group-open/faq:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <p class="faq-answer px-6 pb-6 text-[color:var(--esp-ink-soft)]">
                        Yes. Connect your Stripe account and sell tickets directly from your schedule. Every ticket includes a QR code for check-in at the door. Event Schedule charges zero platform fees - you only pay Stripe's standard processing fees.
                    </p>
                </details>

                <details name="faq" data-reveal class="group/faq es-poster-faq overflow-hidden rounded-xl border-2 border-[color:var(--esp-line)] bg-[color:var(--esp-paper)] transition-shadow">
                    <summary class="flex cursor-pointer items-center justify-between p-6">
                        <h3 class="text-lg font-semibold text-[color:var(--esp-ink)]">
                            What happens when a venue books me for a show?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 shrink-0 text-[color:var(--esp-ink-soft)] transition-transform duration-300 group-open/faq:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <p class="faq-answer px-6 pb-6 text-[color:var(--esp-ink-soft)]">
                        When a venue adds you to their event on Event Schedule, it automatically appears on your schedule too. No need to manually add the same gig in two places. Both calendars stay in sync.
                    </p>
                </details>

                <details name="faq" data-reveal class="group/faq es-poster-faq overflow-hidden rounded-xl border-2 border-[color:var(--esp-line)] bg-[color:var(--esp-paper)] transition-shadow">
                    <summary class="flex cursor-pointer items-center justify-between p-6">
                        <h3 class="text-lg font-semibold text-[color:var(--esp-ink)]">
                            Can I list a weekly residency or recurring gigs?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 shrink-0 text-[color:var(--esp-ink-soft)] transition-transform duration-300 group-open/faq:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <p class="faq-answer px-6 pb-6 text-[color:var(--esp-ink-soft)]">
                        Yes. Recurring events are free. Set the day-of-week pattern once, like every Thursday at the same club, and Event Schedule fills in the dates. You can exclude the weeks you skip, and fans always see the next upcoming show.
                    </p>
                </details>

                <details name="faq" data-reveal class="group/faq es-poster-faq overflow-hidden rounded-xl border-2 border-[color:var(--esp-line)] bg-[color:var(--esp-paper)] transition-shadow">
                    <summary class="flex cursor-pointer items-center justify-between p-6">
                        <h3 class="text-lg font-semibold text-[color:var(--esp-ink)]">
                            Can I use Event Schedule as my band website?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 shrink-0 text-[color:var(--esp-ink-soft)] transition-transform duration-300 group-open/faq:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <p class="faq-answer px-6 pb-6 text-[color:var(--esp-ink-soft)]">
                        Many musicians do. Your schedule lives at your own link, like your-band.eventschedule.com, with your bio, photos, and streaming links. You can also embed the calendar on an existing website, and the Enterprise plan supports a fully custom domain.
                    </p>
                </details>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. Encore finale: the backstage laminate                    -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 overflow-x-clip bg-[#f6f1e6] px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-cyan-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-poster-halftone es-poster-halftone-night -left-10 -top-10 h-[22rem] w-[22rem]"></div>
                    <div class="es-poster-halftone es-poster-halftone-night-amber -bottom-16 -right-10 h-[24rem] w-[24rem]"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    <div class="mb-6 inline-flex">
                        <span class="es-poster-stamp text-[#fbbf24]" style="--stamp-rot: -2deg;">Encore</span>
                    </div>
                    <h2 class="es-poster-display es-balance mx-auto mb-4 max-w-3xl text-4xl text-white md:text-6xl">
                        Your name on the poster.
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Stop posting into the void. Put every show on one link and fill the room. Free forever.
                    </p>

                    <div class="es-poster-sway mx-auto max-w-xl">
                        <div class="es-poster-lanyard" aria-hidden="true"></div>
                        <div class="es-poster-lanyard-clip" aria-hidden="true"></div>
                        <div class="es-poster-laminate p-6 text-center sm:p-8">
                            <div class="es-poster-slot mb-5" aria-hidden="true"></div>
                            <div class="es-poster-display text-3xl text-white">All access</div>
                            <div class="mb-6 mt-1 text-[10px] font-bold uppercase tracking-[0.3em] text-gray-400">Backstage &middot; Tour laminate</div>

                            <div class="flex flex-col items-stretch gap-3">
                                <label for="es-claim-input" class="sr-only">Your schedule name</label>
                                <div dir="ltr" class="es-claim flex min-w-0 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                                    <input id="es-claim-input" type="text" placeholder="your-band" autocomplete="off" spellcheck="false" maxlength="30"
                                        class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                                    <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                                </div>
                                <a href="{{ app_url('/sign_up?type=talent') }}" class="group relative inline-flex items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-cyan-600 to-teal-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-cyan-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-teal-500/40">
                                    <span class="relative z-10 flex items-center gap-2">
                                        Get Started Free
                                        <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                        </svg>
                                    </span>
                                    <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                                </a>
                            </div>

                            <p class="mt-5 text-sm text-gray-400">No credit card required</p>

                            <div class="-mx-6 mt-6 border-t-2 border-dashed border-white/15 px-6 pt-4 sm:-mx-8 sm:px-8">
                                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-gray-500">Free forever &middot; Zero platform fees &middot; Keep 100% of ticket sales</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <x-marketing.related-pages />

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
