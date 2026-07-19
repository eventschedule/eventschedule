<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for DJs | Set Times & Residencies</x-slot>
    <x-slot name="description">Put your DJ set times, residencies, and guest spots on one link. Reach fans direct, no promoter middleman. Sell tickets with zero platform fees. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For DJs</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for DJs",
        "description": "Put your DJ set times, residencies, and guest spots on one link. Reach fans direct, no promoter middleman. Sell tickets with zero platform fees. Free forever.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "DJs"
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
                "name": "Can I track both residencies and one-off bookings?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Set up recurring events for your weekly or monthly residencies and they auto-repeat on your schedule. Add guest spots and festival bookings as one-off events. Everything shows up in one clean calendar that fans can follow."
                }
            },
            {
                "@type": "Question",
                "name": "Does it handle late-night sets that cross midnight?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule handles overnight events correctly. A set that starts at 11 PM Saturday and ends at 4 AM Sunday displays properly on the Saturday listing, so fans know when to show up."
                }
            },
            {
                "@type": "Question",
                "name": "What happens when a club adds me to their lineup?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "When a club or promoter adds you to their event on Event Schedule, it automatically appears on your schedule. No double-entry needed. Both calendars stay in sync so your fans always see your latest bookings."
                }
            },
            {
                "@type": "Question",
                "name": "Can I sell advance tickets to my sets?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Connect Stripe and sell tickets directly from your schedule with zero platform fees. Each ticket includes a unique QR code for check-in at the door. You keep 100% of the sale minus Stripe's standard processing fees."
                }
            },
            {
                "@type": "Question",
                "name": "Can I keep private gigs and secret parties off my public schedule?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Draft events are free and stay hidden until you publish them. On the Enterprise plan you can also mark events internal for your team only, or unlisted with an optional password, so a wedding, corporate booking, or secret location party is reachable only by direct link."
                }
            },
            {
                "@type": "Question",
                "name": "Can I run separate club nights or brands on one schedule?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Sub-schedules are free and let you group events by club night or brand, so your weekly techno night and your open format bookings stay organized under one account. Your link still shows everything in one place, and you can embed the calendar on any website."
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
        "name": "Event Schedule for DJs",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "DJ Event Scheduling Software",
        "operatingSystem": "Web",
        "description": "Put your DJ set times, residencies, and guest spots on one link. Built for club DJs, festival DJs, and electronic producers.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Residency tracking with free recurring events",
            "Late-night sets that cross midnight",
            "Club calendar sync with venue auto-linking",
            "One schedule link for every bio",
            "Auto-generated set-time flyers and promo graphics",
            "Zero-fee ticketing with QR door check-in",
            "Sub-schedules for multiple club nights and brands",
            "Direct fan newsletters and nearby-show alerts",
            "Draft, internal, and unlisted events for private gigs",
            "Manager and agency team access"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "DJ schedule, DJ set times, DJ residency schedule, DJ booking platform, DJ event calendar, DJ gig management, club DJ calendar, DJ link in bio, free DJ scheduling",
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
        "name": "How DJs share their set times with Event Schedule",
        "description": "Three steps from your next set to a packed dancefloor.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Add your sets",
                "text": "Import from Google Cal or add manually. Residencies auto-repeat weekly or monthly."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Drop your link",
                "text": "Add to your RA profile, Linktree, SoundCloud bio. Anywhere fans find you."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Pack the dancefloor",
                "text": "Fans follow you, get notified when you're spinning, and show up ready to dance."
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
           For-djs "The Neon Sign" styles. The shared es-* motion system
           (aurora, reveals, bento, marquee, odometer, finale) lives in
           marketing.css; this block is the page's signage system: tube
           lettering that is glass by day and lit by night, sign
           lightboxes, the set-times board, decal chips, the conduit
           rule, plus the carried-over vinyl, waveform, club lights and
           BPM ruler, and their reduced-motion kills.
           ============================================================== */

        /* Sign display face: rounded where the OS has one, heavy elsewhere */
        .es-dj-display {
            font-family: ui-rounded, 'Arial Rounded MT Bold', 'Helvetica Rounded', ui-sans-serif, system-ui, sans-serif;
            font-weight: 800;
            letter-spacing: 0.04em;
        }

        /* Tube lettering. The base rule is the solid fallback for engines
           without text-stroke; the hollow "tubes off" treatment only
           applies where the prefixed property is supported. The support
           test must stay free of hex colors: a # inside a parenthesized
           at-rule condition breaks Blade's directive parser. */
        .es-dj-tube { color: #0e7490; }
        .es-dj-tube-amber { color: #b45309; }
        @supports (-webkit-text-stroke-width: 2px) {
            .es-dj-tube {
                -webkit-text-stroke: 0.045em #0e7490;
                -webkit-text-fill-color: rgba(14, 116, 144, 0.08);
            }
            .es-dj-tube-amber {
                -webkit-text-stroke: 0.045em #b45309;
                -webkit-text-fill-color: rgba(180, 83, 9, 0.08);
            }
        }
        /* Tubes ON (dark mode): near-white core + layered halo. Resets both
           the stroke and the fill-color or the halo would light a ghost. */
        .dark .es-dj-tube {
            -webkit-text-stroke: 0;
            -webkit-text-fill-color: currentColor;
            color: #e0f2fe;
            text-shadow:
                0 0 2px rgba(224, 242, 254, 0.9),
                0 0 8px rgba(34, 211, 238, 0.85),
                0 0 18px rgba(34, 211, 238, 0.5),
                0 0 42px rgba(8, 145, 178, 0.45);
        }
        .dark .es-dj-tube-amber {
            -webkit-text-stroke: 0;
            -webkit-text-fill-color: currentColor;
            color: #fef3c7;
            text-shadow:
                0 0 2px rgba(254, 243, 199, 0.9),
                0 0 8px rgba(251, 191, 36, 0.85),
                0 0 18px rgba(245, 158, 11, 0.5),
                0 0 42px rgba(217, 119, 6, 0.45);
        }
        /* Always-lit tubes for fixed-dark surfaces (bands, board, finale):
           those signs stay switched on even while the site is in light mode. */
        .es-dj-tube-lit {
            color: #e0f2fe;
            text-shadow:
                0 0 2px rgba(224, 242, 254, 0.9),
                0 0 8px rgba(34, 211, 238, 0.85),
                0 0 18px rgba(34, 211, 238, 0.5),
                0 0 42px rgba(8, 145, 178, 0.45);
        }
        .es-dj-tube-lit-amber {
            color: #fef3c7;
            text-shadow:
                0 0 2px rgba(254, 243, 199, 0.9),
                0 0 8px rgba(251, 191, 36, 0.85),
                0 0 18px rgba(245, 158, 11, 0.5),
                0 0 42px rgba(217, 119, 6, 0.45);
        }

        /* Single-letter flicker: dark mode + motion only, opacity-only.
           Two dip clusters per 7s cycle, far below 3 flashes per second. */
        @keyframes es-dj-flicker {
            0%, 41%, 44%, 78%, 100% { opacity: 1; }
            41.5% { opacity: 0.4; }
            42.5% { opacity: 1; }
            43.2% { opacity: 0.75; }
            78.5% { opacity: 0.5; }
            79.5% { opacity: 1; }
        }
        html.es-anim.dark .es-dj-flicker {
            display: inline-block;
            animation: es-dj-flicker 7s linear infinite;
        }

        /* Ice-cyan CTA gradient + halo (the lit push-plate on the door) */
        .es-dj-cta { background-image: linear-gradient(to right, #0e7490, #0891b2); }
        .es-dj-cta:hover { background-image: linear-gradient(to right, #0891b2, #06b6d4); }
        .dark .es-dj-cta { background-image: linear-gradient(to right, #0891b2, #06b6d4); }
        .dark .es-dj-cta:hover { background-image: linear-gradient(to right, #06b6d4, #22d3ee); }
        .es-dj-halo { box-shadow: 0 10px 30px -8px rgba(8, 145, 178, 0.45); }
        .dark .es-dj-halo { box-shadow: 0 0 20px rgba(34, 211, 238, 0.45), 0 0 40px rgba(8, 145, 178, 0.3); }

        /* Sign lightbox cards: enamel panel by day, glowing cabinet by night */
        .es-dj-sign {
            border: 1px solid #e5e7eb;
            background: linear-gradient(180deg, #ffffff, #f8fafc);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9), 0 1px 2px rgba(15, 23, 42, 0.06);
        }
        .dark .es-dj-sign {
            border-color: rgba(34, 211, 238, 0.16);
            background: linear-gradient(180deg, #0d1220, #0a0e18);
            box-shadow: inset 0 0 34px rgba(34, 211, 238, 0.06), 0 0 24px -12px rgba(34, 211, 238, 0.35);
        }
        /* Night variant for boxes inside the fixed-dark bands (single state) */
        .es-dj-sign-night {
            border: 1px solid rgba(34, 211, 238, 0.16);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0.02));
            box-shadow: inset 0 0 30px rgba(34, 211, 238, 0.05);
        }
        /* Amber booth-lamp box */
        .es-dj-lamp { border: 1px solid rgba(245, 158, 11, 0.4); background: rgba(245, 158, 11, 0.07); }
        .dark .es-dj-lamp { border-color: rgba(245, 158, 11, 0.3); background: rgba(245, 158, 11, 0.1); box-shadow: inset 0 0 22px rgba(245, 158, 11, 0.08); }

        /* Tube rule under section headers: glass by day, lit by night */
        .es-dj-tuberule {
            height: 3px;
            width: 6rem;
            margin: 1rem auto 0;
            border-radius: 9999px;
            background: rgba(14, 116, 144, 0.3);
        }
        .dark .es-dj-tuberule {
            background: #22d3ee;
            box-shadow: 0 0 8px rgba(34, 211, 238, 0.8), 0 0 22px rgba(8, 145, 178, 0.5);
        }

        /* Conduit: the tube line along the top of dark inset bands */
        .es-dj-conduit {
            position: absolute;
            top: 0;
            left: 8%;
            right: 8%;
            height: 2px;
            border-radius: 9999px;
            pointer-events: none;
            background: linear-gradient(90deg, transparent, rgba(34, 211, 238, 0.55), rgba(245, 158, 11, 0.45), transparent);
            box-shadow: 0 0 12px rgba(34, 211, 238, 0.5);
        }

        /* Neon window-decal chips (genre marquee + lineup rail) */
        .es-dj-decal {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border-radius: 9999px;
            border: 1.5px solid rgba(8, 145, 178, 0.45);
            padding: 0.375rem 1rem;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #0e7490;
            background: rgba(14, 116, 144, 0.04);
        }
        .dark .es-dj-decal {
            border-color: rgba(34, 211, 238, 0.6);
            color: #a5f3fc;
            background: rgba(34, 211, 238, 0.06);
            box-shadow: 0 0 10px rgba(34, 211, 238, 0.25), inset 0 0 8px rgba(34, 211, 238, 0.12);
        }
        /* Always-lit amber decal for fixed-dark surfaces (the ON AIR chip) */
        .es-dj-decal-on {
            border-color: rgba(251, 191, 36, 0.6);
            color: #fde68a;
            background: rgba(251, 191, 36, 0.08);
            box-shadow: 0 0 10px rgba(251, 191, 36, 0.3), inset 0 0 8px rgba(251, 191, 36, 0.12);
        }

        /* Set-times board: an always-on sign in both modes */
        .es-dj-board {
            position: relative;
            border-radius: 1.5rem;
            border: 1px solid rgba(34, 211, 238, 0.2);
            background:
                radial-gradient(120% 100% at 50% 0%, rgba(13, 22, 38, 0.97) 0%, rgba(7, 9, 15, 0.99) 62%),
                #070910;
            box-shadow: inset 0 0 44px rgba(34, 211, 238, 0.05), 0 24px 60px -24px rgba(2, 6, 23, 0.7);
        }
        .es-dj-board::before {
            content: "";
            position: absolute;
            top: 1.75rem;
            bottom: 1.75rem;
            inset-inline-start: 6.75rem;
            width: 2px;
            border-radius: 9999px;
            background: linear-gradient(180deg, rgba(34, 211, 238, 0.5), rgba(245, 158, 11, 0.4));
            box-shadow: 0 0 8px rgba(34, 211, 238, 0.4);
        }
        .es-dj-slot { border-bottom: 1px solid rgba(148, 163, 184, 0.12); }
        .es-dj-slot:last-child { border-bottom: 0; }
        .es-dj-slot-time {
            font-variant-numeric: tabular-nums;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            color: #fbbf24;
            text-shadow: 0 0 8px rgba(245, 158, 11, 0.5);
        }
        .es-dj-slot-live {
            background: linear-gradient(90deg, rgba(245, 158, 11, 0.1), transparent 70%);
        }

        /* Lineup rail strip above the perfect-for grid */
        .es-dj-lineup {
            border-radius: 1rem;
            border: 1px solid rgba(8, 145, 178, 0.25);
            background: rgba(14, 116, 144, 0.04);
            padding: 1rem 1.25rem;
        }
        .dark .es-dj-lineup {
            border-color: rgba(34, 211, 238, 0.2);
            background: rgba(13, 18, 32, 0.85);
            box-shadow: inset 0 0 26px rgba(34, 211, 238, 0.05);
        }

        /* Finale marquee frame: continuous tube border (no bulbs), and the
           sign text that mirrors the claimed name. Fixed-dark surface. */
        .es-dj-frame {
            border: 2px solid rgba(34, 211, 238, 0.65);
            border-radius: 1.25rem;
            background: rgba(8, 15, 28, 0.75);
            box-shadow: 0 0 14px rgba(34, 211, 238, 0.35), inset 0 0 18px rgba(34, 211, 238, 0.2);
        }
        .es-dj-signtext {
            display: block;
            max-width: 100%;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            text-transform: uppercase;
        }

        /* Odometer ink override: the shared .es-od-strip spans are brand
           blue in marketing.css; here the digits inherit the sign color. */
        .es-dj-od .es-od-strip span {
            background: none;
            -webkit-text-fill-color: currentColor;
        }

        /* Spinning vinyl badge */
        .es-vinyl {
            position: relative;
            height: 1.5rem;
            width: 1.5rem;
            border-radius: 9999px;
            background: radial-gradient(circle at 50% 50%, #1f2937 0 42%, #0b1220 43% 100%);
            animation: es-spin 6s linear infinite;
        }
        .es-vinyl::before {
            content: "";
            position: absolute;
            inset: 32%;
            border-radius: 9999px;
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
        }
        .es-vinyl::after {
            content: "";
            position: absolute;
            inset: 46%;
            border-radius: 9999px;
            background: #0b1220;
        }
        @keyframes es-spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        /* Pulsing waveform */
        .es-wave {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 3px;
            height: 100%;
        }
        .es-wave i {
            width: 4px;
            border-radius: 9999px;
            background: linear-gradient(180deg, #22d3ee, #0891b2);
            transform-origin: center;
            animation: es-wave-bounce 1.1s ease-in-out infinite;
            animation-delay: calc(var(--i, 0) * 0.06s);
        }
        @keyframes es-wave-bounce {
            0%, 100% { transform: scaleY(0.25); }
            45% { transform: scaleY(1); }
            75% { transform: scaleY(0.5); }
        }

        /* Panning club lights (dark surfaces) */
        .es-clublight {
            position: absolute;
            top: -12%;
            width: 46%;
            height: 135%;
            pointer-events: none;
            transform-origin: 50% 0;
            animation: es-club-pan 9s ease-in-out infinite alternate;
        }
        .es-clublight-1 { left: 3%; background: conic-gradient(from 197deg at 50% 0%, transparent 0deg, rgba(34, 211, 238, 0.16) 11deg, transparent 24deg); }
        .es-clublight-2 { right: 3%; background: conic-gradient(from 149deg at 50% 0%, transparent 0deg, rgba(8, 145, 178, 0.15) 11deg, transparent 24deg); animation-delay: -3.5s; animation-duration: 11s; }
        .es-clublight-3 { left: 33%; background: conic-gradient(from 178deg at 50% 0%, transparent 0deg, rgba(245, 158, 11, 0.13) 9deg, transparent 20deg); animation-delay: -6s; animation-duration: 13s; }
        @keyframes es-club-pan { from { transform: rotate(-7deg); } to { transform: rotate(7deg); } }

        /* BPM beat ruler with an amber booth-lamp playhead */
        .es-bpm {
            position: relative;
            height: 8px;
            max-width: 15rem;
            margin-top: 0.85rem;
            border-radius: 9999px;
            background-image: repeating-linear-gradient(to right, rgba(8, 145, 178, 0.55) 0 2px, transparent 2px 11px);
        }
        .dark .es-bpm {
            background-image: repeating-linear-gradient(to right, rgba(34, 211, 238, 0.5) 0 2px, transparent 2px 11px);
        }
        .es-bpm::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 0;
            width: 7px;
            height: 7px;
            margin-top: -3.5px;
            border-radius: 9999px;
            background: #f59e0b;
            box-shadow: 0 0 9px 2px rgba(245, 158, 11, 0.65);
            animation: es-bpm-tick 2.4s steps(8, end) infinite;
        }
        @keyframes es-bpm-tick {
            from { left: 0; }
            to { left: calc(100% - 7px); }
        }

        /* Residency mock rows (ice cyan) */
        .es-res-row { border: 1px solid rgba(34, 211, 238, 0.22); background: rgba(34, 211, 238, 0.1); }
        .es-res-date { color: #0e7490; }
        .dark .es-res-date { color: #7dd3fc; }

        /* Ice-cyan inline link + hover for FAQ items and related cards */
        .es-dj-link { color: #0e7490; }
        .dark .es-dj-link { color: #22d3ee; }
        .es-dj-hover { transition: border-color 0.2s ease, background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease; }
        .es-dj-hover:hover { border-color: #67e8f9; background-color: rgba(6, 182, 212, 0.06); }
        .dark .es-dj-hover:hover { border-color: rgba(34, 211, 238, 0.35); background-color: rgba(34, 211, 238, 0.06); }
        .es-dj-hover:hover .es-dj-hover-title { color: #0891b2; }
        .dark .es-dj-hover:hover .es-dj-hover-title { color: #22d3ee; }
        .es-dj-hover:hover .es-dj-hover-arrow { color: #0891b2; }
        .dark .es-dj-hover:hover .es-dj-hover-arrow { color: #22d3ee; }

        @media (prefers-reduced-motion: reduce) {
            .es-vinyl,
            .es-wave i,
            .es-clublight,
            .es-bpm::after,
            .es-dj-flicker {
                animation: none !important;
            }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: the sign over the door                              -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(88svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(34, 211, 238, 0.5), rgba(34, 211, 238, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(245, 158, 11, 0.35), rgba(245, 158, 11, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <!-- Waveform along the base -->
        <div class="pointer-events-none absolute inset-x-0 bottom-0 h-24 opacity-30 [mask-image:linear-gradient(to_right,transparent,black_15%,black_85%,transparent)]" aria-hidden="true">
            <div class="es-wave">
                @for ($i = 0; $i < 80; $i++)
                    <i style="--i: {{ $i % 12 }}; height: {{ 20 + (($i * 37) % 60) }}%;"></i>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <span class="es-vinyl" aria-hidden="true"></span>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">For DJs & Electronic Producers</span>
            </div>

            <h1 class="es-balance mb-8 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Fill the dancefloor.</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="es-dj-display es-dj-tube">Skip the a<span class="es-dj-flicker">l</span>gorithm.</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Your residencies. Your guest spots. One link that never stops glowing. Fans hear it from you, not from a pay-to-play feed.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#features" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See the setup
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
                <a href="{{ app_url('/sign_up?type=talent') }}" class="group es-dj-halo pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl es-dj-cta px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                    Create your DJ schedule
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Genre marquee: neon window decals -->
            <div class="es-fade-up es-d-4 pointer-events-auto mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($genreCopy = 0; $genreCopy < 2; $genreCopy++)
                                @foreach (['House', 'Techno', 'DnB', 'Trance', 'Dubstep', 'Garage', 'Minimal', 'Electro', 'Breakbeat', 'Disco', 'Acid', 'Hardgroove'] as $genre)
                                    <span @if ($genreCopy === 1) aria-hidden="true" @endif class="es-dj-decal">{{ $genre }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>

    <!-- ============================================================ -->
    <!-- 2. The problem: the dead feed                                -->
    <!-- ============================================================ -->
    <section class="relative bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="es-dj-conduit" aria-hidden="true"></div>
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-clublight es-clublight-1"></div>
                <div class="es-clublight es-clublight-2"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/[0.07] px-4 py-1.5" data-reveal>
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-400" aria-hidden="true"></span>
                        <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-300">The problem</span>
                    </div>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                        You play at <span class="es-dj-display es-dj-tube-lit-amber">2 AM.</span> The algorithm posts at 9.
                    </h2>
                    <p class="mt-4 text-lg text-gray-400" data-reveal style="--reveal-delay: 0.16s;">
                        Your set announcement dies in a story. Your fans find out on Monday.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-3" data-reveal-group="120">
                    <div class="es-dj-sign-night rounded-2xl p-7 text-center" data-reveal="panel">
                        <div class="es-dj-display es-dj-tube-lit mb-3 text-2xl">Unseen</div>
                        <p class="text-sm text-gray-400">Your gig post is three swipes deep before doors even open.</p>
                    </div>
                    <div class="es-dj-sign-night rounded-2xl p-7 text-center" data-reveal="panel">
                        <div class="es-dj-display es-dj-tube-lit mb-3 text-2xl">Scattered</div>
                        <p class="text-sm text-gray-400">One date on the club page, one on RA, one in a chat. No single place that is yours.</p>
                    </div>
                    <div class="es-dj-sign-night rounded-2xl p-7 text-center" data-reveal="panel">
                        <div class="es-dj-display es-dj-tube-lit-amber mb-3 text-2xl">10-20%</div>
                        <p class="text-sm text-gray-400">of the door lost to ticket platform fees elsewhere. Event Schedule takes zero.</p>
                    </div>
                </div>

                <p class="mt-10 text-center text-gray-400" data-reveal>
                    Time to switch your own sign on.
                    <a href="#features" class="inline-flex items-center gap-1 font-semibold text-[#67e8f9] transition-all hover:gap-2">
                        See the setup
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Feature wall: the wall of signs                           -->
    <!-- ============================================================ -->
    <section id="features" class="scroll-mt-24 bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="mb-6 inline-flex items-center gap-2 rounded-full glass px-4 py-1.5" data-reveal>
                    <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-cyan-400 to-teal-400" aria-hidden="true"></span>
                    <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-600 dark:text-gray-300">The wall of signs</span>
                </div>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                    Everything a working DJ needs, <span class="es-dj-display es-dj-tube">lit up</span>
                </h2>
                <div class="es-dj-tuberule" aria-hidden="true" data-reveal style="--reveal-delay: 0.16s;"></div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">

                <!-- 1. Residency tracker (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner es-dj-sign relative flex h-full flex-col overflow-hidden rounded-3xl p-7 lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-cyan-200 bg-cyan-100 px-3 py-1.5 text-sm font-medium text-cyan-700 dark:border-cyan-800/30 dark:bg-cyan-900/40 dark:text-cyan-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Residency Tracker
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">Your residencies, on repeat</h3>
                                <div class="es-bpm mb-5" aria-hidden="true"></div>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Weekly at Fabric? Monthly guest spot at Berghain? Recurring events repeat themselves, and one-off bookings slot in beside them.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Weekly residencies</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Guest spots</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">One-off bookings</span>
                                </div>
                            </div>
                            <div class="w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="animate-float">
                                    <div class="max-w-xs rounded-2xl border border-cyan-300 bg-gradient-to-br from-cyan-50 to-sky-50 p-4 shadow-lg dark:border-cyan-400/30 dark:from-[#0d1220] dark:to-[#0a0e18]">
                                        <div class="mb-3 text-xs font-semibold text-cyan-600 dark:text-cyan-300">DECEMBER</div>
                                        <div class="space-y-2">
                                            <div class="es-ai-field es-res-row flex items-center gap-3 rounded-lg p-2" style="--i: 0;">
                                                <div class="w-12 text-xs font-bold es-res-date">FRI 6</div>
                                                <div class="flex-1">
                                                    <div class="text-sm font-semibold text-gray-900 dark:text-white">Fabric</div>
                                                    <div class="text-[10px] text-gray-500 dark:text-gray-400">Every Friday</div>
                                                </div>
                                                <div class="shrink-0 rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">11 PM</div>
                                            </div>
                                            <div class="es-ai-field es-res-row flex items-center gap-3 rounded-lg p-2" style="--i: 1;">
                                                <div class="w-12 text-xs font-bold es-res-date">SAT 14</div>
                                                <div class="flex-1">
                                                    <div class="text-sm font-semibold text-gray-900 dark:text-white">Berghain</div>
                                                    <div class="text-[10px] text-gray-500 dark:text-gray-400">Guest spot</div>
                                                </div>
                                                <div class="shrink-0 rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">2 AM</div>
                                            </div>
                                            <div class="es-ai-field es-res-row flex items-center gap-3 rounded-lg p-2" style="--i: 2;">
                                                <div class="w-12 text-xs font-bold es-res-date">FRI 20</div>
                                                <div class="flex-1">
                                                    <div class="text-sm font-semibold text-gray-900 dark:text-white">Fabric</div>
                                                    <div class="text-[10px] text-gray-500 dark:text-gray-400">Every Friday</div>
                                                </div>
                                                <div class="shrink-0 rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">11 PM</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2. Late night (1 col) -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner es-dj-sign relative flex h-full flex-col overflow-hidden rounded-3xl p-7">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                            Late Night
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Built for nights that cross midnight</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">A set from 11 PM Saturday to 4 AM Sunday lists on Saturday, where your fans look for it.</p>

                        <div class="es-dj-lamp mt-auto rounded-xl p-4 text-center" aria-hidden="true">
                            <svg aria-hidden="true" class="mx-auto mb-2 h-5 w-5 text-amber-700 dark:text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                            <div class="text-2xl font-black text-gray-900 dark:text-white">11 PM - 4 AM</div>
                            <div class="mt-1 text-xs text-amber-700 dark:text-amber-300">Saturday into Sunday</div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3. Club sync (1 col) -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner es-dj-sign relative flex h-full flex-col overflow-hidden rounded-3xl p-7">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-cyan-200 bg-cyan-100 px-3 py-1.5 text-sm font-medium text-cyan-700 dark:border-cyan-800/30 dark:bg-cyan-900/40 dark:text-cyan-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Club Sync
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Clubs book you, fans know instantly</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">When a promoter adds you to a lineup, it appears on your schedule. No double entry, ever.</p>

                        <div class="relative mt-auto flex items-center justify-center gap-8 py-2" aria-hidden="true">
                            <div class="w-20 rounded-lg border border-cyan-400/40 bg-cyan-500/10 p-2 dark:border-cyan-400/30 dark:bg-cyan-400/10">
                                <div class="mb-1 text-center text-[10px] font-medium text-cyan-700 dark:text-cyan-300">Club</div>
                                <div class="mb-1 h-1.5 rounded bg-gray-300/60 dark:bg-white/20"></div>
                                <div class="h-1.5 w-3/4 rounded bg-cyan-400/40"></div>
                            </div>
                            <div class="absolute left-1/2 top-1/2 h-px w-10 -translate-x-1/2 -translate-y-1/2 border-t border-dashed border-cyan-300 dark:border-cyan-500/40"></div>
                            <div class="es-sync-dot" style="left: calc(50% - 24px);"></div>
                            <div class="w-20 rounded-lg border border-gray-300 bg-gray-100 p-2 dark:border-white/20 dark:bg-white/10">
                                <div class="mb-1 text-center text-[10px] font-medium text-gray-600 dark:text-gray-300">You</div>
                                <div class="mb-1 h-1.5 rounded bg-gray-300/60 dark:bg-white/20"></div>
                                <div class="h-1.5 w-3/4 rounded bg-cyan-400/40"></div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4. One link (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner es-dj-sign relative flex h-full flex-col overflow-hidden rounded-3xl p-7 lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                    </svg>
                                    Share Link
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">One link for RA, Linktree, SoundCloud</h3>
                                <p class="text-lg text-gray-500 dark:text-gray-400">Drop it in every bio. Fans see every upcoming set: residencies, guest spots, festivals.</p>
                            </div>
                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                                <div class="mb-2 text-xs text-gray-500 dark:text-gray-400">Your schedule link</div>
                                <div class="es-dj-lamp flex items-center gap-2 rounded-xl p-3" dir="ltr">
                                    <svg aria-hidden="true" class="h-4 w-4 shrink-0 text-amber-700 dark:text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                    </svg>
                                    <span class="truncate font-mono text-sm text-gray-900 dark:text-white">djnova.eventschedule.com</span>
                                </div>
                                <div class="mt-3 flex gap-2">
                                    <div class="flex-1 rounded-lg bg-gray-100 p-2 text-center text-xs font-medium text-amber-700 dark:bg-white/5 dark:text-amber-300">Resident Advisor</div>
                                    <div class="flex-1 rounded-lg bg-gray-100 p-2 text-center text-xs font-medium text-amber-700 dark:bg-white/5 dark:text-amber-300">SoundCloud</div>
                                    <div class="flex-1 rounded-lg bg-gray-100 p-2 text-center text-xs font-medium text-amber-700 dark:bg-white/5 dark:text-amber-300">Mixcloud</div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5. Set-time flyers (1 col) -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner es-dj-sign relative flex h-full flex-col overflow-hidden rounded-3xl p-7">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Graphics
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Flyers that make themselves</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Auto-generated set-time graphics, sized for stories, RA pages, and the group chat.</p>

                        <div class="mt-auto flex justify-center" aria-hidden="true">
                            <div class="relative h-36 w-28 -rotate-3 rounded-xl border border-amber-400/30 bg-gradient-to-br from-amber-500/25 to-orange-500/25 p-2 transition-transform duration-300 group-hover:rotate-0">
                                <div class="flex h-full w-full flex-col items-center justify-center rounded-lg bg-gradient-to-br from-cyan-700/70 to-sky-700/70">
                                    <div class="mb-1 text-[8px] font-semibold text-white">THIS SATURDAY</div>
                                    <div class="text-sm font-bold text-amber-300">DJ NOVA</div>
                                    <div class="mt-1 text-[10px] text-white">@ FABRIC</div>
                                    <div class="mt-1 text-[8px] text-white/80">11 PM - 4 AM</div>
                                </div>
                                <div class="absolute -bottom-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-amber-500">
                                    <svg aria-hidden="true" class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6. Ticket window (2 cols on lg) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner es-dj-sign relative flex h-full flex-col overflow-hidden rounded-3xl p-7 lg:p-9">
                        <div class="grid items-center gap-8 lg:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Ticketing
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">Your door, your money</h3>
                                <p class="text-lg text-gray-500 dark:text-gray-400">Advance tickets straight from your schedule. QR check-in at the door. The platform fee is a round number: zero.</p>
                            </div>
                            <div class="es-dj-od mx-auto w-full max-w-[240px] rounded-xl border border-emerald-300/50 bg-emerald-50 p-4 dark:border-emerald-400/30 dark:bg-emerald-500/10" aria-hidden="true">
                                <div class="mb-3 text-center">
                                    <div class="text-xs uppercase tracking-[0.18em] text-emerald-600 dark:text-emerald-300">Platform fee</div>
                                    <div class="es-od justify-center text-3xl font-black text-gray-900 dark:text-white" data-odometer="$0">$0</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">You keep 100%</div>
                                </div>
                                <div class="border-t border-emerald-300/40 pt-3 dark:border-emerald-400/20">
                                    <div class="flex items-center justify-center gap-1">
                                        <svg aria-hidden="true" class="h-4 w-4 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span class="text-xs text-emerald-600 dark:text-emerald-300">Direct to your Stripe</span>
                                    </div>
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
    <!-- 4. Set times: one night, whole career                        -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-28">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="mb-6 inline-flex items-center gap-2 rounded-full glass px-4 py-1.5" data-reveal>
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-400" aria-hidden="true"></span>
                    <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-600 dark:text-gray-300">Set times</span>
                </div>
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                    One DJ schedule for <span class="es-dj-display es-dj-tube">every slot</span> on the bill
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.16s;">
                    Start on the early slot. The schedule grows with you.
                </p>
            </div>

            <div class="es-dj-board mx-auto max-w-3xl p-6 sm:p-8" data-reveal="panel">
                <div data-reveal-group="120">
                    <div class="es-dj-slot grid grid-cols-[5.5rem_1fr] gap-x-4 py-5 sm:grid-cols-[6.5rem_1fr] sm:gap-x-6" data-reveal>
                        <div dir="ltr" class="es-dj-slot-time pt-0.5 text-lg font-bold sm:text-xl">22:00</div>
                        <div>
                            <div class="es-dj-display mb-1 text-lg text-white sm:text-xl">Open decks</div>
                            <p class="text-sm text-gray-400">Add your first set in minutes. Type it in, import from Google Calendar, or let AI parse the booking email. Drafts are free until you are ready.</p>
                        </div>
                    </div>
                    <div class="es-dj-slot grid grid-cols-[5.5rem_1fr] gap-x-4 py-5 sm:grid-cols-[6.5rem_1fr] sm:gap-x-6" data-reveal>
                        <div dir="ltr" class="es-dj-slot-time pt-0.5 text-lg font-bold sm:text-xl">00:00</div>
                        <div>
                            <div class="es-dj-display mb-1 text-lg text-white sm:text-xl">Resident</div>
                            <p class="text-sm text-gray-400">Set your night to repeat weekly or monthly. Templates clone it in one tap, and sub-schedules keep each club night or brand separate.</p>
                        </div>
                    </div>
                    <div class="es-dj-slot es-dj-slot-live -mx-3 grid grid-cols-[5.5rem_1fr] gap-x-4 rounded-xl px-3 py-5 sm:grid-cols-[6.5rem_1fr] sm:gap-x-6" data-reveal>
                        <div dir="ltr" class="es-dj-slot-time pt-0.5 text-lg font-bold sm:text-xl">02:00</div>
                        <div>
                            <div class="mb-1 flex flex-wrap items-center gap-3">
                                <span class="es-dj-display text-lg text-white sm:text-xl">Headline</span>
                                <span class="es-dj-decal es-dj-decal-on !py-1 !px-2.5 text-[10px]">On air</span>
                            </div>
                            <p class="text-sm text-gray-400">Fans follow you and get notified when you play near them. One newsletter fills the floor before you are on.</p>
                        </div>
                    </div>
                    <div class="es-dj-slot grid grid-cols-[5.5rem_1fr] gap-x-4 py-5 sm:grid-cols-[6.5rem_1fr] sm:gap-x-6" data-reveal>
                        <div dir="ltr" class="es-dj-slot-time pt-0.5 text-lg font-bold sm:text-xl">04:00</div>
                        <div>
                            <div class="es-dj-display mb-1 text-lg text-white sm:text-xl">Festival closer</div>
                            <p class="text-sm text-gray-400">Your manager or agency gets team access. Venues sync you into their lineups automatically. You just play.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Perfect for: tonight's lineup                             -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-10 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Whether you spin vinyl or <span class="es-dj-display es-dj-tube">push buttons</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Event Schedule works for every type of DJ.
                </p>
            </div>

            <!-- Tonight's lineup decal rail (decorative; the cards below carry the content) -->
            <div class="es-dj-lineup mx-auto mb-12 flex max-w-3xl flex-wrap items-center justify-center gap-2" aria-hidden="true" data-reveal>
                <span class="es-dj-display es-dj-tube-amber px-1 text-lg">Tonight</span>
                <span class="es-dj-decal">Resident</span>
                <span class="es-dj-decal">Touring</span>
                <span class="es-dj-decal">B2B</span>
                <span class="es-dj-decal">Underground</span>
                <span class="es-dj-decal">Open Format</span>
                <span class="es-dj-decal">Producers</span>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">
                <div data-reveal>
                    <x-sub-audience-card
                        name="Resident DJs"
                        description="Track your weekly slots and build loyal locals who know exactly where to find you."
                        icon-color="indigo"
                        blog-slug="for-resident-djs"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </x-slot:icon>
                    </x-sub-audience-card>
                </div>

                <div data-reveal>
                    <x-sub-audience-card
                        name="Touring DJs"
                        description="Share your international dates with fans worldwide. They'll know when you're in their city."
                        icon-color="purple"
                        blog-slug="for-touring-djs"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </x-slot:icon>
                    </x-sub-audience-card>
                </div>

                <div data-reveal>
                    <x-sub-audience-card
                        name="B2B Partners"
                        description="Show joint sets and collaborations. Both schedules stay synced automatically."
                        icon-color="fuchsia"
                        blog-slug="for-b2b-djs"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </x-slot:icon>
                    </x-sub-audience-card>
                </div>

                <div data-reveal>
                    <x-sub-audience-card
                        name="Underground DJs"
                        description="Warehouse parties, afters, secret locations. Share with your inner circle only."
                        icon-color="violet"
                        blog-slug="for-underground-djs"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                        </x-slot:icon>
                    </x-sub-audience-card>
                </div>

                <div data-reveal>
                    <x-sub-audience-card
                        name="Open Format DJs"
                        description="Weddings, corporate gigs, private events. Keep your public and private bookings organized."
                        icon-color="pink"
                        blog-slug="for-open-format-djs"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </x-slot:icon>
                    </x-sub-audience-card>
                </div>

                <div data-reveal>
                    <x-sub-audience-card
                        name="Producers"
                        description="Live sets, album launches, listening parties. Show fans where to hear your music live."
                        icon-color="amber"
                        blog-slug="for-dj-producers"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                            </svg>
                        </x-slot:icon>
                    </x-sub-audience-card>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. How it works: in the booth (dark)                         -->
    <!-- ============================================================ -->
    <section class="relative bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="es-dj-conduit" aria-hidden="true"></div>
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-clublight es-clublight-1"></div>
                <div class="es-clublight es-clublight-2"></div>
                <div class="es-clublight es-clublight-3"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>
            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-9 opacity-40 [mask-image:linear-gradient(to_right,transparent,black_15%,black_85%,transparent)]" aria-hidden="true">
                <div class="es-wave">
                    @for ($i = 0; $i < 60; $i++)
                        <i style="--i: {{ $i % 12 }}; height: {{ 25 + (($i * 43) % 55) }}%;"></i>
                    @endfor
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-4xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/[0.07] px-4 py-1.5" data-reveal>
                        <span class="es-vinyl h-4 w-4" aria-hidden="true"></span>
                        <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-300">In the booth</span>
                    </div>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                        Your DJ schedule, live in <span class="es-dj-display es-dj-tube-lit">three steps</span>
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="120">
                    <div class="es-dj-sign-night rounded-2xl p-7 text-center" data-reveal="panel">
                        <div class="es-dj-display es-dj-tube-lit mx-auto mb-5 text-4xl">1</div>
                        <h3 class="mb-2 text-lg font-semibold text-white">Add your sets</h3>
                        <p class="text-sm text-gray-400">Import from Google Cal or add manually. Residencies auto-repeat weekly or monthly.</p>
                    </div>
                    <div class="es-dj-sign-night rounded-2xl p-7 text-center" data-reveal="panel">
                        <div class="es-dj-display es-dj-tube-lit mx-auto mb-5 text-4xl">2</div>
                        <h3 class="mb-2 text-lg font-semibold text-white">Drop your link</h3>
                        <p class="text-sm text-gray-400">Add to your RA profile, Linktree, SoundCloud bio. Anywhere fans find you.</p>
                    </div>
                    <div class="es-dj-sign-night rounded-2xl p-7 text-center" data-reveal="panel">
                        <div class="es-dj-display es-dj-tube-lit-amber mx-auto mb-5 text-4xl">3</div>
                        <h3 class="mb-2 text-lg font-semibold text-white">Pack the dancefloor</h3>
                        <p class="text-sm text-gray-400">Fans follow you, get notified when you're spinning, and show up ready to dance.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Key features                                              -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-gray-50 py-20 dark:border-white/5 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Key <span class="es-dj-display es-dj-tube">features</span></h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card
                        name="Ticketing"
                        description="Sell tickets with QR check-in and zero platform fees"
                        :url="marketing_url('/features/ticketing')"
                        icon-color="sky"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Event Graphics"
                        description="Auto-generate set-time flyers sized for your socials"
                        :url="marketing_url('/features/event-graphics')"
                        icon-color="sky"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Newsletters"
                        description="Send event updates directly to followers' inboxes"
                        :url="marketing_url('/features/newsletters')"
                        icon-color="sky"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                        icon-color="sky"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="inline-flex items-center font-medium es-dj-link hover:underline">
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
    <!-- 8. Related pages                                             -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Related <span class="es-dj-display es-dj-tube">pages</span></h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([['/for-musicians', 'Musicians'], ['/for-nightclubs', 'Nightclubs'], ['/for-bars', 'Bars'], ['/for-live-concerts', 'Live Concerts']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="group es-dj-hover flex items-center justify-between rounded-2xl border border-gray-200 bg-gray-50 p-5 hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-white/5">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                            <div class="es-dj-hover-title text-lg font-semibold text-gray-900 transition-colors dark:text-white">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="es-dj-hover-arrow w-5 h-5 text-gray-400 transition-colors rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="inline-flex items-center font-medium es-dj-link hover:underline">
                    See all use cases
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. FAQ                                                       -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Frequently asked <span class="es-dj-display es-dj-tube">questions</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything DJs ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['Can I track both residencies and one-off bookings?', 'Yes. Set up recurring events for your weekly or monthly residencies and they auto-repeat on your schedule. Add guest spots and festival bookings as one-off events. Everything shows up in one clean calendar that fans can follow.'],
                    ['Does it handle late-night sets that cross midnight?', 'Yes. Event Schedule handles overnight events correctly. A set that starts at 11 PM Saturday and ends at 4 AM Sunday displays properly on the Saturday listing, so fans know when to show up.'],
                    ['What happens when a club adds me to their lineup?', 'When a club or promoter adds you to their event on Event Schedule, it automatically appears on your schedule. No double-entry needed. Both calendars stay in sync so your fans always see your latest bookings.'],
                    ['Can I sell advance tickets to my sets?', 'Yes. Connect Stripe and sell tickets directly from your schedule with zero platform fees. Each ticket includes a unique QR code for check-in at the door. You keep 100% of the sale minus Stripe\'s standard processing fees.'],
                    ['Can I keep private gigs and secret parties off my public schedule?', 'Yes. Draft events are free and stay hidden until you publish them. On the Enterprise plan you can also mark events internal for your team only, or unlisted with an optional password, so a wedding, corporate booking, or secret location party is reachable only by direct link.'],
                    ['Can I run separate club nights or brands on one schedule?', 'Yes. Sub-schedules are free and let you group events by club night or brand, so your weekly techno night and your open format bookings stay organized under one account. Your link still shows everything in one place, and you can embed the calendar on any website.'],
                ] as [$q, $a])
                    <details name="faq" data-reveal class="group/faq es-dj-hover overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $q }}</h3>
                            <svg aria-hidden="true" class="w-5 h-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="faq-answer px-6 pb-6 text-gray-600 dark:text-gray-400">{{ $a }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. Finale: your name in lights                              -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-gray-50 px-2 py-16 dark:bg-[#0f0f14] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-cyan-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-clublight es-clublight-1"></div>
                    <div class="es-clublight es-clublight-2"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>
                <div class="pointer-events-none absolute inset-x-0 bottom-0 h-10 opacity-40 [mask-image:linear-gradient(to_right,transparent,black_15%,black_85%,transparent)]" aria-hidden="true">
                    <div class="es-wave">
                        @for ($i = 0; $i < 60; $i++)
                            <i style="--i: {{ $i % 12 }}; height: {{ 25 + (($i * 51) % 55) }}%;"></i>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Your name in <span class="es-dj-display es-dj-tube-lit">lights.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Stop posting into the void. Put every set on one link that never sleeps. Free forever.
                    </p>

                    <!-- The marquee sign: mirrors the claimed name below -->
                    <div class="es-dj-frame mx-auto mb-8 max-w-md px-6 py-5" aria-hidden="true">
                        <span id="es-dj-signtext" class="es-dj-display es-dj-tube-lit es-dj-signtext text-3xl sm:text-4xl">dj-nova</span>
                    </div>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="dj-name" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl es-dj-cta px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-cyan-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-cyan-500/40">
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

    <x-marketing.related-pages />

    {{-- Mirror the claimed name into the marquee sign, applying the same
         slug transform as the shared claim-input sanitizer. --}}
    <script {!! nonce_attr() !!}>
        (function () {
            var input = document.getElementById('es-claim-input');
            var sign = document.getElementById('es-dj-signtext');
            if (!input || !sign) { return; }
            var fallback = sign.textContent;
            input.addEventListener('input', function () {
                var slug = input.value.toLowerCase()
                    .replace(/['’]/g, '')
                    .replace(/[^a-z0-9-]+/g, '-')
                    .replace(/-{2,}/g, '-')
                    .replace(/^-+/, '')
                    .slice(0, 30);
                sign.textContent = slug || fallback;
            });
        })();
    </script>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
