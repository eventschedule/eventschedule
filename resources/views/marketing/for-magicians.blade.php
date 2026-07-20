<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Magicians | Gigs, Residencies, Tickets</x-slot>
    <x-slot name="description">One link for every show, residency, and private booking. Sell tickets with zero platform fees and keep corporate gigs off your public schedule. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Magicians</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Magicians",
        "description": "One link for every show, residency, and private booking. Sell tickets with zero platform fees and keep corporate gigs off your public schedule. Free forever.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Magicians"
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
                "name": "Is Event Schedule free for magicians?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule is free forever for sharing your show schedule, building a following, and syncing with Google Calendar. Ticketing and newsletters are available on the Pro plan, with zero platform fees on ticket sales."
                }
            },
            {
                "@type": "Question",
                "name": "Can I keep private and corporate bookings off my public schedule?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Save any booking as a draft and it stays off your public schedule until you publish it. Drafts are free and unlimited, so you can hold close-up gigs and corporate dates privately. On the Enterprise plan you can also make events internal or unlisted with an optional password for private and corporate clients."
                }
            },
            {
                "@type": "Question",
                "name": "Can I sell gift cards or season passes for my shows?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. On the Pro plan you can sell balance-tracked gift cards that buyers send to a recipient by email, redeemable toward tickets for any show on your schedule. You can also sell multi-use passes like a parlor-show season pass, with usage tracked automatically. Zero platform fees apply to both."
                }
            },
            {
                "@type": "Question",
                "name": "Can I sell tickets to my magic shows?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Connect your Stripe account and sell tickets directly from your schedule. Create ticket types for general admission, VIP, and meet-and-greet packages, each with a QR code for check-in at the door. When a show sells out, a waitlist notifies fans if seats open up. Zero platform fees, you only pay Stripe's processing."
                }
            },
            {
                "@type": "Question",
                "name": "Can I run a weekly residency without re-entering the same show?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Set up your show once as a recurring event with a day-of-week pattern, and add date exceptions for the weeks you are away. On the Pro plan you can also save any event as a template, so repeat corporate formats take two clicks instead of a blank form."
                }
            },
            {
                "@type": "Question",
                "name": "How do planners and fans find my shows?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Share one schedule link in your bio, EPK, and booking website, or embed the calendar on any page. Fans who follow your schedule get notified when you add a show, and newsletters reach their inboxes directly. Two-way Google, Outlook, and CalDAV sync keeps every calendar current."
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
        "name": "Event Schedule for Magicians",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Magician Scheduling Software",
        "operatingSystem": "Web",
        "description": "One link for every show, residency, and private booking. Sell tickets with zero platform fees and keep corporate gigs off your public schedule. Free forever.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "One link where planners and fans find every show",
            "Zero-fee ticketing with QR check-in and general admission, VIP, and meet-and-greet tiers",
            "Internal and unlisted events for private and corporate bookings",
            "Recurring weekly residencies with date exceptions",
            "Season passes and balance-tracked gift cards",
            "Ticket waitlists for sold-out shows",
            "Embeddable ticket widget and calendar for any website",
            "Auto-generated show posters and social graphics",
            "AI event parsing that turns a booking email into a draft event",
            "Two-way Google, Outlook, and CalDAV calendar sync",
            "Availability management so bookers see open dates",
            "Direct fan newsletters and new-show alerts"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "magician schedule, magic show calendar, magician booking platform, magic event management, free magician scheduling, private event magician booking, close-up magic schedule, corporate magician calendar, mentalist show scheduling",
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
        "name": "How magicians get their performance schedule online with Event Schedule",
        "description": "Get your performance schedule online in three steps.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Add your shows",
                "text": "Paste a booking email and AI parsing drafts the event for you, or import from Google Calendar. Set a weekly residency once as a recurring event."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Share one link",
                "text": "Add your schedule link to your bio, EPK, and booking website, or embed the calendar on any page. Planners see your availability instantly."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Fill the room",
                "text": "Fans follow your schedule and get notified when you add a show. Newsletters and new-show alerts reach their inboxes directly."
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
           For-magicians "Pick a Card" styles. The page is a close-up
           card routine at a baize table: an ivory parlor in light mode,
           green-tinged near-black rooms in dark mode, and fixed-dark
           green felt table bands that never change with the room
           lights. Playing cards stay card-stock ivory in BOTH modes
           (the deck is a physical object; only the room changes), so
           card internals deliberately carry no dark: variants.
           Features are dealt face down and flip face up on scroll.

           BLADE RULE for this block: never use @supports probes here.
           A "#" hex inside a parenthesized at-rule condition breaks
           Blade compilation of every later parenthesized directive.
           ============================================================== */

        /* --- Accent text: red ink --- */
        .es-pick-red {
            background-image: linear-gradient(135deg, #991b1b, #dc2626 55%, #ef4444);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            -webkit-text-fill-color: transparent;
        }
        .dark .es-pick-red {
            background-image: linear-gradient(135deg, #f87171, #fca5a5 55%, #fecaca);
        }
        /* Always-bright variant for the fixed-dark felt bands (both modes). */
        .es-pick-red-lit {
            background-image: linear-gradient(135deg, #fca5a5, #f87171 45%, #fecaca);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            -webkit-text-fill-color: transparent;
        }

        /* --- Eyebrow tags --- */
        .es-pick-tag {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.3em;
            text-transform: uppercase;
            color: #065f46;
        }
        .dark .es-pick-tag,
        .es-pick-felt .es-pick-tag { color: #6ee7b7; }

        /* --- Links and buttons --- */
        .es-pick-link { color: #b91c1c; }
        .es-pick-link:hover { color: #991b1b; }
        .dark .es-pick-link { color: #f87171; }
        .dark .es-pick-link:hover { color: #fca5a5; }

        .es-pick-btn {
            background-image: linear-gradient(to right, #b91c1c, #dc2626);
            box-shadow: 0 20px 40px -12px rgba(185, 28, 28, 0.45);
        }
        .es-pick-btn:hover {
            background-image: linear-gradient(to right, #991b1b, #b91c1c);
            box-shadow: 0 24px 48px -12px rgba(185, 28, 28, 0.55);
        }

        /* --- FAQ / related-card hover recolor --- */
        .es-pick-hover:hover { border-color: rgba(185, 28, 28, 0.35); }
        .dark .es-pick-hover:hover { border-color: rgba(248, 113, 113, 0.3); }
        .es-pick-hover:hover .es-pick-hover-title,
        .es-pick-hover:hover .es-pick-hover-arrow { color: #b91c1c; }
        .dark .es-pick-hover:hover .es-pick-hover-title,
        .dark .es-pick-hover:hover .es-pick-hover-arrow { color: #f87171; }

        /* --- The felt: fixed-dark baize table band, identical in both modes --- */
        .es-pick-felt {
            background-color: #093425;
            background-image: radial-gradient(120% 100% at 50% 0%, #11543a 0%, #0a3a27 55%, #072a1c 100%);
            box-shadow: inset 0 0 80px rgba(0, 0, 0, 0.45), inset 0 1px 0 rgba(255, 255, 255, 0.06);
        }

        /* --- Card anatomy: faces are fixed ivory in both modes --- */
        .es-pick-card {
            position: relative;
            background: linear-gradient(160deg, #fffdf6, #f3ecd8);
            border: 1px solid #d9cfb4;
            border-radius: 0.8rem;
            color: #1f2937;
            box-shadow: 0 10px 24px -12px rgba(7, 42, 28, 0.35);
        }
        .es-pick-index,
        .es-pick-index-flip {
            position: absolute;
            display: flex;
            flex-direction: column;
            align-items: center;
            line-height: 1;
            font-weight: 900;
            font-size: 0.85rem;
        }
        .es-pick-index { top: 0.6rem; left: 0.65rem; }
        .es-pick-index-flip { bottom: 0.6rem; right: 0.65rem; transform: rotate(180deg); }
        .es-pick-index svg,
        .es-pick-index-flip svg { width: 0.6rem; height: 0.6rem; margin-top: 0.2rem; }
        .es-pick-pip-red { color: #b91c1c; }
        .es-pick-pip-black { color: #1f2937; }

        /* Patterned card back: ivory frame around a baize crosshatch panel. */
        .es-pick-back {
            background: linear-gradient(160deg, #fffdf6, #f3ecd8);
            border: 1px solid #d9cfb4;
            border-radius: 0.8rem;
            padding: 0.5rem;
            box-shadow: 0 10px 24px -12px rgba(7, 42, 28, 0.35);
        }
        .es-pick-back-inner {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            border-radius: 0.5rem;
            border: 1px solid rgba(9, 52, 37, 0.35);
            background-color: #f3ecd8;
            background-image:
                repeating-linear-gradient(45deg, rgba(10, 58, 39, 0.8) 0, rgba(10, 58, 39, 0.8) 2px, transparent 2px, transparent 8px),
                repeating-linear-gradient(-45deg, rgba(185, 28, 28, 0.45) 0, rgba(185, 28, 28, 0.45) 2px, transparent 2px, transparent 8px);
            color: #f6efe2;
        }
        .es-pick-back-inner svg {
            width: 2.5rem;
            height: 2.5rem;
            filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.45));
        }
        /* Ivory medallion on the mystery backs so the "?" reads on the
           crosshatch. */
        .es-pick-back-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 3.25rem;
            height: 3.25rem;
            border-radius: 9999px;
            background: #f6efe2;
            border: 1px solid rgba(9, 52, 37, 0.4);
            color: #b91c1c;
            font-size: 1.6rem;
            font-weight: 900;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
        }

        /* --- Section corner-index chip (the A, 2, 3... rank device) --- */
        .es-pick-corner {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 2.4rem;
            height: 3.3rem;
            line-height: 1;
            font-weight: 900;
            font-size: 1rem;
        }
        .es-pick-corner svg { width: 0.8rem; height: 0.8rem; margin-top: 0.25rem; }

        /* --- Suit-symbol divider --- */
        .es-pick-divider {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.9rem;
        }
        .es-pick-divider::before,
        .es-pick-divider::after {
            content: "";
            height: 1px;
            flex: 1;
            max-width: 9rem;
            background: linear-gradient(to right, transparent, rgba(185, 28, 28, 0.35));
        }
        .es-pick-divider::after {
            background: linear-gradient(to left, transparent, rgba(185, 28, 28, 0.35));
        }
        .dark .es-pick-divider::before { background: linear-gradient(to right, transparent, rgba(248, 113, 113, 0.3)); }
        .dark .es-pick-divider::after { background: linear-gradient(to left, transparent, rgba(248, 113, 113, 0.3)); }
        .es-pick-divider svg { width: 0.9rem; height: 0.9rem; }

        /* --- Hero fan: seven card backs fanning open on the table --- */
        .es-pick-fan {
            position: absolute;
            left: 50%;
            top: 100%;
            width: 0;
            height: 0;
        }
        .es-pick-fan-card {
            position: absolute;
            left: -2.25rem;
            bottom: 0.5rem;
            width: 4.5rem;
            height: 6.5rem;
            /* Origin stays on the ALWAYS-ACTIVE rule so the pivot never
               snaps when the open animation lands. */
            transform-origin: 50% 190%;
            transform: rotate(var(--rot));
        }
        html.es-anim .es-pick-fan-card {
            animation: es-pick-fan-open 1.1s cubic-bezier(0.22, 1, 0.36, 1) both;
            animation-delay: var(--fd, 0s);
        }
        @keyframes es-pick-fan-open {
            from { transform: rotate(0deg) translateY(26px) scale(0.92); opacity: 0; }
            to { transform: rotate(var(--rot)) translateY(0) scale(1); opacity: 1; }
        }

        /* --- Ambient drifting suit pips in the hero --- */
        .es-pick-float { position: absolute; color: rgba(185, 28, 28, 0.16); }
        .es-pick-float-dim { color: rgba(31, 41, 55, 0.12); }
        .dark .es-pick-float { color: rgba(248, 113, 113, 0.13); }
        .dark .es-pick-float-dim { color: rgba(209, 213, 219, 0.09); }
        .es-pick-float svg { width: 100%; height: 100%; }
        html.es-anim .es-pick-float {
            animation: es-pick-drift 9s ease-in-out infinite;
            animation-delay: var(--d, 0s);
        }
        @keyframes es-pick-drift {
            0%, 100% { transform: translateY(0) rotate(-6deg); }
            50% { transform: translateY(-18px) rotate(6deg); }
        }

        /* --- Show-type marquee chips --- */
        .es-pick-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            margin: 0 0.9rem;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            white-space: nowrap;
            color: #6b7280;
        }
        .dark .es-pick-chip { color: #9ca3af; }
        .es-pick-chip svg { width: 0.55rem; height: 0.55rem; opacity: 0.6; }

        /* ==============================================================
           The flip system. perspective, transform-origin, and the
           transition live on ALWAYS-ACTIVE rules; only the face-down
           pre-state is gated, so the default resting state everywhere
           (no JS, crawlers, reduced motion, touch) is FACE UP.
           ============================================================== */
        .es-pick-flip { perspective: 1200px; }
        .es-pick-flip-inner {
            position: relative;
            transform-style: preserve-3d;
            transform-origin: 50% 50%;
            transition: transform 0.85s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .es-pick-face {
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
        }
        .es-pick-face-back {
            position: absolute;
            inset: 0;
            transform: rotateY(180deg);
        }

        /* Scroll-dealt cards: page-local data-reveal token. The shared
           base rule handles opacity + --reveal-delay; the transforms
           below are this page's. Deal cards are standalone [data-reveal]
           elements with inline delays (never inside data-reveal-group,
           which clobbers inline delays) and never carry data-tilt. */
        html.es-anim [data-reveal="deal"] { transform: translateY(20px); }
        html.es-anim [data-reveal="deal"]:not(.is-revealed) .es-pick-flip-inner { transform: rotateY(180deg); }

        /* Pick-a-card choice cards: face down only for hover-capable,
           motion-enabled visitors; hover or keyboard focus turns them. */
        @media (hover: hover) {
            html.es-anim .es-pick-choice .es-pick-flip-inner { transform: rotateY(180deg); }
            html.es-anim .es-pick-choice:hover .es-pick-flip-inner,
            html.es-anim .es-pick-choice:focus-visible .es-pick-flip-inner { transform: rotateY(0deg); }
        }

        /* Finale card: turns face up ~1s after the panel reveal lands. */
        .es-pick-finale-flip { perspective: 1200px; }
        .es-pick-finale-inner {
            position: relative;
            transform-style: preserve-3d;
            transform-origin: 50% 50%;
            transition: transform 1s cubic-bezier(0.22, 1, 0.36, 1) 0.9s;
        }
        html.es-anim [data-reveal] .es-pick-finale-inner { transform: rotateY(180deg); }
        html.es-anim [data-reveal].is-revealed .es-pick-finale-inner { transform: rotateY(0deg); }

        /* The signed card's red-ink signature. */
        .es-pick-sign {
            font-style: italic;
            font-weight: 800;
            color: #b91c1c;
            transform: rotate(-4deg);
            overflow-wrap: anywhere;
        }

        /* --- Emerald recolor of the shared cursor spotlight in the hero --- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(5, 150, 105, 0.1), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(52, 211, 153, 0.12), transparent 60%);
        }

        /* --- Reduced motion: every card rests face up and readable --- */
        @media (prefers-reduced-motion: reduce) {
            .es-pick-fan-card,
            .es-pick-float { animation: none !important; }
            .es-pick-flip-inner,
            .es-pick-finale-inner {
                transition: none !important;
                transform: none !important;
            }
        }
    </style>

    @php
        $suitSpade = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C9.5 5.5 4 9.3 4 13a4 4 0 0 0 6.2 3.3C9.9 18 9 19.6 7.5 21h9c-1.5-1.4-2.4-3-2.7-4.7A4 4 0 0 0 20 13c0-3.7-5.5-7.5-8-11z"/></svg>';
        $suitHeart = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 21C6.5 16.5 2 12.8 2 8.6 2 5.9 4.1 4 6.7 4 8.8 4 10.6 5.2 12 7c1.4-1.8 3.2-3 5.3-3C19.9 4 22 5.9 22 8.6c0 4.2-4.5 7.9-10 12.4z"/></svg>';
        $suitDiamond = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l7 10-7 10-7-10z"/></svg>';
        $suitClub = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a4 4 0 0 0-3.1 6.5A4 4 0 1 0 11 15.4c-.2 2-.9 4.1-2.5 5.6h7c-1.6-1.5-2.3-3.6-2.5-5.6a4 4 0 1 0 2.1-6.9A4 4 0 0 0 12 2z"/></svg>';
        $suits = ['spade' => $suitSpade, 'heart' => $suitHeart, 'diamond' => $suitDiamond, 'club' => $suitClub];
    @endphp

    <!-- ============================================================ -->
    <!-- 1. Hero: the table (A of spades)                             -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(88svh-4rem)] items-center overflow-hidden bg-[#fdfbf4] py-16 dark:bg-[#0b0f0c] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(16, 185, 129, 0.26), rgba(16, 185, 129, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(220, 38, 38, 0.2), rgba(220, 38, 38, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <div class="es-pick-float left-[10%] top-[22%] h-8 w-8" style="--d: 0s;">{!! $suitHeart !!}</div>
            <div class="es-pick-float es-pick-float-dim left-[84%] top-[18%] h-10 w-10" style="--d: 2.2s;">{!! $suitSpade !!}</div>
            <div class="es-pick-float left-[88%] top-[62%] h-7 w-7" style="--d: 4.1s;">{!! $suitDiamond !!}</div>
            <div class="es-pick-float es-pick-float-dim left-[7%] top-[66%] h-9 w-9" style="--d: 5.6s;">{!! $suitClub !!}</div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <span class="inline-flex h-5 w-5 items-center justify-center text-red-700 dark:text-red-400" aria-hidden="true">{!! $suitHeart !!}</span>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">For magicians, mentalists, and illusionists</span>
            </div>

            <h1 class="es-balance mb-8 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Pick a card.</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="es-pick-red">Any card.</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Every show, residency, and private booking on one schedule link. Planners see your availability. Fans never miss the reveal.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#deal" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See the deal
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
                <a href="{{ app_url('/sign_up?type=talent') }}" class="es-pick-btn group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                    Create your schedule
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- The deck fans open on the table -->
            <div class="es-fade-up es-d-4 relative mx-auto mt-10 h-36 w-full max-w-md" aria-hidden="true">
                <div class="es-pick-fan">
                    @foreach (['-36deg' => '0s', '-24deg' => '0.06s', '-12deg' => '0.12s', '0deg' => '0.18s', '12deg' => '0.24s', '24deg' => '0.3s', '36deg' => '0.36s'] as $rot => $fd)
                        <div class="es-pick-fan-card es-pick-back" style="--rot: {{ $rot }}; --fd: {{ $fd }};">
                            <div class="es-pick-back-inner"></div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Show-type marquee -->
            <div class="es-fade-up es-d-4 pointer-events-auto mx-auto mt-6 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach ([['Close-Up', 'heart'], ['Parlor', 'spade'], ['Stage', 'diamond'], ['Corporate', 'club'], ['Weddings', 'heart'], ['Kids Shows', 'spade'], ['Mentalism', 'diamond'], ['Trade Shows', 'club'], ['Street Magic', 'heart'], ['Cruise Ships', 'spade']] as [$chip, $chipSuit])
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-pick-chip">{{ $chip }} <span class="@if (in_array($chipSuit, ['heart', 'diamond'])) text-red-700 dark:text-red-400 @endif" aria-hidden="true">{!! $suits[$chipSuit] !!}</span></span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The stakes: the gigs on the table (felt band, 2 of hearts) -->
    <!-- ============================================================ -->
    <section id="stakes" class="relative scroll-mt-24 bg-[#fdfbf4] px-2 py-14 dark:bg-[#0b0f0c] sm:px-4 lg:py-20">
        <div class="es-pick-felt noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-pick-card es-pick-corner es-pick-pip-red mb-6" data-reveal aria-hidden="true">
                        <span>2</span>
                        {!! $suitHeart !!}
                    </div>
                    <p class="es-pick-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The gigs on the table</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Your next three gigs live in three inboxes. <span class="es-pick-red-lit">Deal them onto one table.</span>
                    </h2>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    <div class="es-pick-card -rotate-2 p-6 pt-9 text-center" data-reveal="panel">
                        <div class="es-pick-index es-pick-pip-black" aria-hidden="true"><span>?</span>{!! $suitSpade !!}</div>
                        <div class="es-pick-index-flip es-pick-pip-black" aria-hidden="true"><span>?</span>{!! $suitSpade !!}</div>
                        <h3 class="mb-2 text-lg font-bold">The corporate inquiry</h3>
                        <p class="text-sm text-gray-600">Sitting in an email thread from three weeks ago. Subject line: Re: Re: Fwd: Holiday party?</p>
                    </div>
                    <div class="es-pick-card p-6 pt-9 text-center" data-reveal="panel">
                        <div class="es-pick-index es-pick-pip-red" aria-hidden="true"><span>?</span>{!! $suitHeart !!}</div>
                        <div class="es-pick-index-flip es-pick-pip-red" aria-hidden="true"><span>?</span>{!! $suitHeart !!}</div>
                        <h3 class="mb-2 text-lg font-bold">The wedding close-up set</h3>
                        <p class="text-sm text-gray-600">An Instagram DM you starred so you would not lose it. You lost it.</p>
                    </div>
                    <div class="es-pick-card rotate-2 p-6 pt-9 text-center" data-reveal="panel">
                        <div class="es-pick-index es-pick-pip-red" aria-hidden="true"><span>?</span>{!! $suitDiamond !!}</div>
                        <div class="es-pick-index-flip es-pick-pip-red" aria-hidden="true"><span>?</span>{!! $suitDiamond !!}</div>
                        <h3 class="mb-2 text-lg font-bold">The Tuesday residency</h3>
                        <p class="text-sm text-gray-600">On a napkin behind the bar. The bar knows the date. Your fans do not.</p>
                    </div>
                </div>

                <p class="mt-10 text-center text-gray-300" data-reveal>
                    One schedule holds every gig, and shows each audience only what they should see.
                    <a href="#deal" class="inline-flex items-center gap-1 font-semibold text-red-300 transition-all hover:gap-2">
                        Watch the deal
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The deal: six features dealt face down (3 of diamonds)    -->
    <!-- ============================================================ -->
    <section id="deal" class="scroll-mt-24 bg-[#f7f1e4] py-20 dark:bg-[#0e130f] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-pick-card es-pick-corner es-pick-pip-red mb-6" data-reveal aria-hidden="true">
                    <span>3</span>
                    {!! $suitDiamond !!}
                </div>
                <p class="es-pick-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The deal</p>
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Six cards, face down. <span class="es-pick-red">Watch them turn.</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.15s;">
                    Everything a working magician needs, dealt one card at a time.
                </p>
            </div>

            @php
                $dealCards = [
                    [
                        'rank' => 'A', 'suit' => 'spade', 'pip' => 'es-pick-pip-black', 'delay' => '0s',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />',
                        'title' => 'Zero-fee ticketing',
                        'copy' => 'Connect Stripe and sell general admission, VIP, and meet-and-greet seats. Every ticket carries a QR code for the door, and you keep every fee.',
                        'url' => '/features/ticketing', 'link' => 'Sell tickets',
                    ],
                    [
                        'rank' => 'K', 'suit' => 'heart', 'pip' => 'es-pick-pip-red', 'delay' => '0.08s',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />',
                        'title' => 'Show posters, produced',
                        'copy' => 'Every event auto-generates a poster sized for socials. Post the date, not a blank story.',
                        'url' => '/features/event-graphics', 'link' => 'See event graphics',
                    ],
                    [
                        'rank' => 'Q', 'suit' => 'diamond', 'pip' => 'es-pick-pip-red', 'delay' => '0.16s',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />',
                        'title' => 'Residencies on repeat',
                        'copy' => 'Set the Tuesday parlor show once. Day-of-week recurrence, with date exceptions for the weeks you tour.',
                        'url' => '/features/recurring-events', 'link' => 'Set up recurring shows',
                    ],
                    [
                        'rank' => 'J', 'suit' => 'club', 'pip' => 'es-pick-pip-black', 'delay' => '0.24s',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />',
                        'title' => 'Private stays private',
                        'copy' => 'Unlimited free drafts keep corporate dates off your public schedule. Enterprise adds internal and unlisted events with an optional password.',
                        'url' => '/pricing', 'link' => 'Compare plans',
                    ],
                    [
                        'rank' => '10', 'suit' => 'heart', 'pip' => 'es-pick-pip-red', 'delay' => '0.32s',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />',
                        'title' => 'Passes and gift cards',
                        'copy' => 'Sell a season pass for the parlor run, and balance-tracked gift cards fans send by email. Zero platform fees on both.',
                        'url' => '/features/gift-cards', 'link' => 'Sell gift cards',
                    ],
                    [
                        'rank' => '9', 'suit' => 'spade', 'pip' => 'es-pick-pip-black', 'delay' => '0.4s',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />',
                        'title' => 'Calendars that agree',
                        'copy' => 'Two-way Google, Outlook, and CalDAV sync. Bookers see open dates, and you never double-book a Saturday.',
                        'url' => '/features/calendar-sync', 'link' => 'Sync your calendar',
                    ],
                ];
            @endphp

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($dealCards as $card)
                    <div data-reveal="deal" style="--reveal-delay: {{ $card['delay'] }};">
                        <div class="es-pick-flip h-full">
                            <div class="es-pick-flip-inner h-full">
                                <div class="es-pick-face es-pick-card flex h-full flex-col p-7 pt-10">
                                    <div class="es-pick-index {{ $card['pip'] }}" aria-hidden="true"><span>{{ $card['rank'] }}</span>{!! $suits[$card['suit']] !!}</div>
                                    <div class="es-pick-index-flip {{ $card['pip'] }}" aria-hidden="true"><span>{{ $card['rank'] }}</span>{!! $suits[$card['suit']] !!}</div>
                                    <div class="mb-4 inline-flex h-11 w-11 items-center justify-center self-center rounded-xl bg-red-700/10 text-red-700">
                                        <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $card['icon'] !!}</svg>
                                    </div>
                                    <h3 class="mb-2 text-center text-lg font-bold">{{ $card['title'] }}</h3>
                                    <p class="mb-4 text-center text-sm text-gray-600">{{ $card['copy'] }}</p>
                                    <a href="{{ marketing_url($card['url']) }}" class="es-pick-link mt-auto inline-flex items-center justify-center gap-1 text-sm font-semibold transition-all hover:gap-2">
                                        {{ $card['link'] }}
                                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                    </a>
                                </div>
                                <div class="es-pick-face es-pick-face-back es-pick-back" aria-hidden="true">
                                    <div class="es-pick-back-inner">{!! $suits[$card['suit']] !!}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Pick a card: the interactive moment (felt, 4 of clubs)    -->
    <!-- ============================================================ -->
    <section id="pick" class="relative scroll-mt-24 bg-[#f7f1e4] px-2 py-14 dark:bg-[#0e130f] sm:px-4 lg:py-20">
        <div class="es-pick-felt noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-pick-card es-pick-corner es-pick-pip-black mb-6" data-reveal aria-hidden="true">
                        <span>4</span>
                        {!! $suitClub !!}
                    </div>
                    <p class="es-pick-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Your routine</p>
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Pick a card. <span class="es-pick-red-lit">Go on.</span>
                    </h2>
                    <p class="text-lg text-gray-300 sm:text-xl" data-reveal style="--reveal-delay: 0.15s;">
                        Hover, or tab across with your keyboard. Whichever gig you run, there is a setup for it.
                    </p>
                </div>

                <div class="mx-auto grid max-w-3xl gap-6 sm:grid-cols-3">
                    <a href="{{ marketing_url('/pricing') }}" class="es-pick-choice group block rounded-[0.8rem] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-300 focus-visible:ring-offset-2 focus-visible:ring-offset-transparent" data-reveal style="--reveal-delay: 0s;">
                        <div class="es-pick-flip h-full">
                            <div class="es-pick-flip-inner h-full">
                                <div class="es-pick-face es-pick-card flex h-full min-h-[19rem] flex-col p-6 pt-10 text-center">
                                    <div class="es-pick-index es-pick-pip-black" aria-hidden="true"><span>J</span>{!! $suitClub !!}</div>
                                    <div class="es-pick-index-flip es-pick-pip-black" aria-hidden="true"><span>J</span>{!! $suitClub !!}</div>
                                    <h3 class="mb-2 text-lg font-bold">The corporate gala</h3>
                                    <p class="text-sm text-gray-600">Internal and unlisted events, password-protected pages, availability the planner can check. The gig nobody hears about until the invoice clears.</p>
                                    <span class="es-pick-link mt-auto inline-flex items-center justify-center gap-1 text-sm font-semibold">See Enterprise
                                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                    </span>
                                </div>
                                <div class="es-pick-face es-pick-face-back es-pick-back" aria-hidden="true">
                                    <div class="es-pick-back-inner"><span class="es-pick-back-badge">?</span></div>
                                </div>
                            </div>
                        </div>
                    </a>

                    <a href="{{ marketing_url('/features/recurring-events') }}" class="es-pick-choice group block rounded-[0.8rem] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-300 focus-visible:ring-offset-2 focus-visible:ring-offset-transparent" data-reveal style="--reveal-delay: 0.12s;">
                        <div class="es-pick-flip h-full">
                            <div class="es-pick-flip-inner h-full">
                                <div class="es-pick-face es-pick-card flex h-full min-h-[19rem] flex-col p-6 pt-10 text-center">
                                    <div class="es-pick-index es-pick-pip-red" aria-hidden="true"><span>Q</span>{!! $suitDiamond !!}</div>
                                    <div class="es-pick-index-flip es-pick-pip-red" aria-hidden="true"><span>Q</span>{!! $suitDiamond !!}</div>
                                    <h3 class="mb-2 text-lg font-bold">The residency</h3>
                                    <p class="text-sm text-gray-600">A recurring Tuesday show, a season pass for the regulars, and a poster that makes itself every week.</p>
                                    <span class="es-pick-link mt-auto inline-flex items-center justify-center gap-1 text-sm font-semibold">Set the pattern
                                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                    </span>
                                </div>
                                <div class="es-pick-face es-pick-face-back es-pick-back" aria-hidden="true">
                                    <div class="es-pick-back-inner"><span class="es-pick-back-badge">?</span></div>
                                </div>
                            </div>
                        </div>
                    </a>

                    <a href="{{ marketing_url('/features/ticketing') }}" class="es-pick-choice group block rounded-[0.8rem] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-300 focus-visible:ring-offset-2 focus-visible:ring-offset-transparent" data-reveal style="--reveal-delay: 0.24s;">
                        <div class="es-pick-flip h-full">
                            <div class="es-pick-flip-inner h-full">
                                <div class="es-pick-face es-pick-card flex h-full min-h-[19rem] flex-col p-6 pt-10 text-center">
                                    <div class="es-pick-index es-pick-pip-red" aria-hidden="true"><span>A</span>{!! $suitHeart !!}</div>
                                    <div class="es-pick-index-flip es-pick-pip-red" aria-hidden="true"><span>A</span>{!! $suitHeart !!}</div>
                                    <h3 class="mb-2 text-lg font-bold">The parlor show</h3>
                                    <p class="text-sm text-gray-600">Twenty seats, sold through your own link. QR check-in at the door, and a waitlist for when the room is full.</p>
                                    <span class="es-pick-link mt-auto inline-flex items-center justify-center gap-1 text-sm font-semibold">Sell the room
                                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                    </span>
                                </div>
                                <div class="es-pick-face es-pick-face-back es-pick-back" aria-hidden="true">
                                    <div class="es-pick-back-inner"><span class="es-pick-back-badge">?</span></div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <p class="mt-10 text-center text-sm text-gray-400" data-reveal>
                    On phones the cards are already face up. A magician never repeats a trick. A schedule should.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. The routine: how it works (5 of diamonds)                 -->
    <!-- ============================================================ -->
    <section id="routine" class="scroll-mt-24 bg-[#fdfbf4] py-20 dark:bg-[#101511] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-pick-card es-pick-corner es-pick-pip-red mb-6" data-reveal aria-hidden="true">
                    <span>5</span>
                    {!! $suitDiamond !!}
                </div>
                <p class="es-pick-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The routine</p>
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Three moves. <span class="es-pick-red">No sleight required.</span>
                </h2>
            </div>

            <div class="grid gap-4 md:grid-cols-3" data-reveal-group="120">
                <div class="es-pick-card p-7 pt-10" data-reveal="panel">
                    <div class="es-pick-index es-pick-pip-red" aria-hidden="true"><span>1</span>{!! $suitDiamond !!}</div>
                    <div class="es-pick-index-flip es-pick-pip-red" aria-hidden="true"><span>1</span>{!! $suitDiamond !!}</div>
                    <h3 class="mb-3 text-center text-xl font-bold">Add your shows</h3>
                    <p class="text-center text-sm text-gray-600">Paste a booking email and AI parsing drafts the event for you, or import from Google Calendar. Set a weekly residency once as a recurring event.</p>
                </div>
                <div class="es-pick-card p-7 pt-10" data-reveal="panel">
                    <div class="es-pick-index es-pick-pip-red" aria-hidden="true"><span>2</span>{!! $suitDiamond !!}</div>
                    <div class="es-pick-index-flip es-pick-pip-red" aria-hidden="true"><span>2</span>{!! $suitDiamond !!}</div>
                    <h3 class="mb-3 text-center text-xl font-bold">Share one link</h3>
                    <p class="text-center text-sm text-gray-600">Add your schedule link to your bio, EPK, and booking website, or embed the calendar on any page. Planners see your availability instantly.</p>
                </div>
                <div class="es-pick-card p-7 pt-10" data-reveal="panel">
                    <div class="es-pick-index es-pick-pip-red" aria-hidden="true"><span>3</span>{!! $suitDiamond !!}</div>
                    <div class="es-pick-index-flip es-pick-pip-red" aria-hidden="true"><span>3</span>{!! $suitDiamond !!}</div>
                    <h3 class="mb-3 text-center text-xl font-bold">Fill the room</h3>
                    <p class="text-center text-sm text-gray-600">Fans follow your schedule and get notified when you add a show. Newsletters and new-show alerts reach their inboxes directly.</p>
                </div>
            </div>
        </div>
    </section>

    <div class="bg-[#fdfbf4] pb-4 dark:bg-[#101511]">
        <div class="es-pick-divider mx-auto max-w-3xl px-4" aria-hidden="true">
            <span class="es-pick-pip-black">{!! $suitSpade !!}</span>
            <span class="es-pick-pip-red">{!! $suitHeart !!}</span>
            <span class="es-pick-pip-red">{!! $suitDiamond !!}</span>
            <span class="es-pick-pip-black">{!! $suitClub !!}</span>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- 6. Sleight of hand: the utility moves (6 of spades)          -->
    <!-- ============================================================ -->
    <section id="sleight" class="scroll-mt-24 bg-[#f7f1e4] py-20 dark:bg-[#0e130f] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-pick-card es-pick-corner es-pick-pip-black mb-6" data-reveal aria-hidden="true">
                    <span>6</span>
                    {!! $suitSpade !!}
                </div>
                <p class="es-pick-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Sleight of hand</p>
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    The moves the audience <span class="es-pick-red">never sees.</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.15s;">
                    Small utilities that make the whole act look effortless.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="90">
                @foreach ([
                    ['Event templates', 'Load the trick once. Save any show as a template and produce the next one in two clicks.', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />'],
                    ['Custom fields', 'Track what only you need: stage size, mic setup, table count, load-in time.', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />'],
                    ['AI event parsing', 'Paste a booking email and a draft event appears, date and venue filled in.', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" />'],
                    ['Ticket waitlist', 'Sold-out parlor show? The waitlist tells fans the moment a seat frees up.', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />'],
                    ['Embed ticket widget', 'Sell tickets from your own website with an embedded checkout.', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />'],
                    ['Availability management', 'Track availability for you and your team, so bookers only ever see real open dates.', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />'],
                ] as [$moveTitle, $moveCopy, $moveIcon])
                    <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                        <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                            <div class="mb-4 inline-flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-700/10 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $moveIcon !!}</svg>
                            </div>
                            <h3 class="mb-2 text-lg font-bold text-gray-900 dark:text-white">{{ $moveTitle }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $moveCopy }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Perfect for (7 of hearts)                                 -->
    <!-- ============================================================ -->
    <section class="bg-[#fdfbf4] py-20 dark:bg-[#0b0f0c] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-pick-card es-pick-corner es-pick-pip-red mb-6" data-reveal aria-hidden="true">
                    <span>7</span>
                    {!! $suitHeart !!}
                </div>
                <p class="es-pick-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Every kind of act</p>
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Perfect for <span class="es-pick-red">every performer.</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.15s;">
                    Close-up or grand illusion, one schedule carries the whole act.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <x-sub-audience-card
                    name="Close-Up Magicians"
                    description="Card tricks, coin magic, sleight of hand for intimate gatherings and table-hopping at events."
                    icon-color="red"
                    blog-slug="for-close-up-magicians"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" /></svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Stage Illusionists"
                    description="Large-scale illusions and theatrical magic shows that fill theaters and wow audiences."
                    icon-color="amber"
                    blog-slug="for-stage-illusionists"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" /></svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Mentalists"
                    description="Mind reading, predictions, and psychological entertainment that leaves audiences amazed."
                    icon-color="emerald"
                    blog-slug="for-mentalists"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Children's Entertainers"
                    description="Birthday parties, school shows, and family events with fun, interactive magic for kids."
                    icon-color="orange"
                    blog-slug="for-childrens-entertainers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Corporate Magicians"
                    description="Trade shows, conferences, and product launches with customized magic presentations."
                    icon-color="red"
                    blog-slug="for-corporate-magicians"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Variety Artists"
                    description="Ventriloquists, escape artists, hypnotists, and specialty acts that defy categorization."
                    icon-color="amber"
                    blog-slug="for-variety-artists"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Key features (8 of clubs)                                 -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-[#f7f1e4] py-20 dark:border-white/5 dark:bg-[#101511]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8 text-center">
                <div class="es-pick-card es-pick-corner es-pick-pip-black mb-6" data-reveal aria-hidden="true">
                    <span>8</span>
                    {!! $suitClub !!}
                </div>
                <h2 class="text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Key <span class="es-pick-red">features</span></h2>
            </div>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="Sell tickets with QR check-in and zero platform fees" :url="marketing_url('/features/ticketing')" icon-color="red">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Event Graphics" description="Show posters generated from your events" :url="marketing_url('/features/event-graphics')" icon-color="red">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Send event updates directly to followers' inboxes" :url="marketing_url('/features/newsletters')" icon-color="red">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Calendar Sync" description="Two-way sync with Google Calendar" :url="marketing_url('/features/calendar-sync')" icon-color="red">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-pick-link inline-flex items-center font-medium hover:underline">
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
    <!-- 9. Related pages (9 of diamonds)                             -->
    <!-- ============================================================ -->
    <section class="bg-[#f7f1e4] py-20 dark:bg-[#0e130f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8 text-center">
                <div class="es-pick-card es-pick-corner es-pick-pip-red mb-6" data-reveal aria-hidden="true">
                    <span>9</span>
                    {!! $suitDiamond !!}
                </div>
                <h2 class="text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Related <span class="es-pick-red">pages</span></h2>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([['/for-comedians', 'Comedians'], ['/for-circus-acrobatics', 'Circus & Acrobatics'], ['/for-theater-performers', 'Theater Performers'], ['/for-spoken-word', 'Spoken Word Artists']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="es-pick-hover group flex items-center justify-between rounded-2xl border border-gray-200 bg-white p-5 transition-all hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-white/5">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                            <div class="es-pick-hover-title text-lg font-semibold text-gray-900 transition-colors dark:text-white">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="es-pick-hover-arrow w-5 h-5 text-gray-400 transition-colors rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-pick-link inline-flex items-center font-medium hover:underline">
                    See all use cases
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. FAQ (10 of spades)                                       -->
    <!-- ============================================================ -->
    <section class="bg-[#fdfbf4] py-20 dark:bg-[#101511] lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-pick-card es-pick-corner es-pick-pip-black mb-6" data-reveal aria-hidden="true">
                    <span>10</span>
                    {!! $suitSpade !!}
                </div>
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Frequently asked <span class="es-pick-red">questions</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything magicians ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['Is Event Schedule free for magicians?', 'Yes. Event Schedule is free forever for sharing your show schedule, building a following, and syncing with Google Calendar. Ticketing and newsletters are available on the Pro plan, with zero platform fees on ticket sales.'],
                    ['Can I keep private and corporate bookings off my public schedule?', 'Yes. Save any booking as a draft and it stays off your public schedule until you publish it. Drafts are free and unlimited, so you can hold close-up gigs and corporate dates privately. On the Enterprise plan you can also make events internal or unlisted with an optional password for private and corporate clients.'],
                    ['Can I sell gift cards or season passes for my shows?', 'Yes. On the Pro plan you can sell balance-tracked gift cards that buyers send to a recipient by email, redeemable toward tickets for any show on your schedule. You can also sell multi-use passes like a parlor-show season pass, with usage tracked automatically. Zero platform fees apply to both.'],
                    ['Can I sell tickets to my magic shows?', 'Yes. Connect your Stripe account and sell tickets directly from your schedule. Create ticket types for general admission, VIP, and meet-and-greet packages, each with a QR code for check-in at the door. When a show sells out, a waitlist notifies fans if seats open up. Zero platform fees, you only pay Stripe\'s processing.'],
                    ['Can I run a weekly residency without re-entering the same show?', 'Yes. Set up your show once as a recurring event with a day-of-week pattern, and add date exceptions for the weeks you are away. On the Pro plan you can also save any event as a template, so repeat corporate formats take two clicks instead of a blank form.'],
                    ['How do planners and fans find my shows?', 'Share one schedule link in your bio, EPK, and booking website, or embed the calendar on any page. Fans who follow your schedule get notified when you add a show, and newsletters reach their inboxes directly. Two-way Google, Outlook, and CalDAV sync keeps every calendar current.'],
                ] as [$q, $a])
                    <details name="faq" data-reveal class="group/faq es-pick-hover overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
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
    <!-- 11. Finale: is this your card? (A of hearts)                 -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-[#fdfbf4] px-2 py-16 dark:bg-[#0b0f0c] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-pick-felt noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-emerald-900/30 sm:px-12 lg:py-20" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-20"></div>
                </div>

                <div class="relative z-10">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/[0.07] px-4 py-1.5">
                        <span class="inline-flex h-3 w-3 items-center justify-center text-red-400" aria-hidden="true">{!! $suitHeart !!}</span>
                        <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-300">The reveal</span>
                    </div>
                    <h2 class="es-balance mx-auto mb-4 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>
                        Is this <span class="es-pick-red-lit">your card?</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl" data-reveal style="--reveal-delay: 0.15s;">
                        Type a name and watch the signature appear. Your schedule link works the same way: one card, always yours.
                    </p>

                    <!-- The signed card, turned face up after the panel settles -->
                    <div class="es-pick-finale-flip mx-auto mb-10 w-64 -rotate-2 sm:w-72" aria-hidden="true">
                        <div class="es-pick-finale-inner">
                            <div class="es-pick-face es-pick-card flex aspect-[5/7] flex-col items-center justify-center p-6">
                                <div class="es-pick-index es-pick-pip-red"><span>A</span>{!! $suitHeart !!}</div>
                                <div class="es-pick-index-flip es-pick-pip-red"><span>A</span>{!! $suitHeart !!}</div>
                                <div class="es-pick-pip-red mb-4 h-9 w-9">{!! $suitHeart !!}</div>
                                <div class="es-pick-sign text-2xl sm:text-3xl"><span id="es-pick-signtext">your-name</span></div>
                                <div class="mt-3 font-mono text-xs text-gray-500">.eventschedule.com</div>
                            </div>
                            <div class="es-pick-face es-pick-face-back es-pick-back">
                                <div class="es-pick-back-inner">{!! $suitHeart !!}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-name" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="es-pick-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Claim your card
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="mt-6 text-sm text-gray-400">No credit card required. Well. One card.</p>
                </div>
            </div>
        </div>
    </section>

    <x-marketing.related-pages />

    {{-- Mirror the claimed name onto the signed card, applying the same
         slug transform as the shared claim-input sanitizer. --}}
    <script {!! nonce_attr() !!}>
        (function () {
            var input = document.getElementById('es-claim-input');
            var sign = document.getElementById('es-pick-signtext');
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
