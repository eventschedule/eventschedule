<x-marketing-layout>
    <x-slot name="title">{{ __('marketing.home_title') }}</x-slot>
    <x-slot name="description">{{ __('marketing.home_description') }}</x-slot>
    <x-slot name="breadcrumbTitle">Home</x-slot>

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present,
         so no-JS visitors, crawlers, and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <style {!! nonce_attr() !!}>
        /* ==============================================================
           Homepage-specific styles: aurora hero, scroll scenes, bento
           mockups, orbit, odometers, and the finale. Generic reveal
           utilities live in marketing.css.
           ============================================================== */

        .es-balance { text-wrap: balance; }

        ::selection { background: rgba(78, 129, 250, 0.35); }

        /* Film grain */
        .noise::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
            opacity: 0.025;
            pointer-events: none;
            z-index: 1;
        }

        /* --------------------------------------------------------------
           Hero: aurora, god rays, cursor spotlight, masked headline
           -------------------------------------------------------------- */

        .es-aurora {
            position: absolute;
            border-radius: 9999px;
            filter: blur(90px);
            opacity: 0.4;
            will-change: transform;
        }
        .dark .es-aurora { opacity: 0.55; }
        .es-aurora-1 {
            width: 640px; height: 640px; left: -160px; top: -140px;
            background: radial-gradient(circle at 30% 30%, rgba(78, 129, 250, 0.55), rgba(78, 129, 250, 0) 65%);
            animation: es-drift-1 26s ease-in-out infinite alternate;
        }
        .es-aurora-2 {
            width: 560px; height: 560px; right: -140px; top: 5%;
            background: radial-gradient(circle at 70% 40%, rgba(14, 165, 233, 0.5), rgba(14, 165, 233, 0) 65%);
            animation: es-drift-2 32s ease-in-out infinite alternate;
        }
        .es-aurora-3 {
            width: 760px; height: 760px; left: 50%; bottom: -45%; margin-left: -380px;
            background: radial-gradient(circle at 50% 30%, rgba(34, 211, 238, 0.35), rgba(34, 211, 238, 0) 60%);
            animation: es-drift-3 30s ease-in-out infinite alternate;
        }
        @keyframes es-drift-1 {
            from { transform: translate3d(0, 0, 0) rotate(0deg) scale(1); }
            to   { transform: translate3d(90px, 70px, 0) rotate(25deg) scale(1.15); }
        }
        @keyframes es-drift-2 {
            from { transform: translate3d(0, 0, 0) rotate(0deg) scale(1.1); }
            to   { transform: translate3d(-80px, 90px, 0) rotate(-20deg) scale(0.95); }
        }
        @keyframes es-drift-3 {
            from { transform: translate3d(0, 0, 0) scale(1); }
            to   { transform: translate3d(0, -70px, 0) scale(1.18); }
        }

        .es-rays {
            opacity: 0.5;
            background: conic-gradient(from 200deg at 50% -20%,
                transparent 0deg,
                rgba(78, 129, 250, 0.08) 12deg,
                transparent 24deg,
                transparent 42deg,
                rgba(14, 165, 233, 0.07) 54deg,
                transparent 66deg,
                transparent 90deg,
                rgba(34, 211, 238, 0.06) 102deg,
                transparent 116deg);
            mask-image: linear-gradient(to bottom, black 20%, transparent 75%);
            -webkit-mask-image: linear-gradient(to bottom, black 20%, transparent 75%);
        }
        .dark .es-rays { opacity: 0.85; }

        .es-spot {
            opacity: 0;
            transition: opacity 0.5s ease;
            pointer-events: none;
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(78, 129, 250, 0.12), transparent 60%);
        }
        .dark .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(110, 160, 255, 0.14), transparent 60%);
        }

        /* Masked headline lines slide up from behind a clip on load */
        .es-mask {
            display: block;
            overflow: hidden;
            padding-bottom: 0.12em;
            margin-bottom: -0.12em;
        }
        .es-mask-line { display: block; }
        html.es-anim .es-mask .es-mask-line {
            transform: translateY(115%);
            animation: es-rise 0.95s cubic-bezier(0.22, 1, 0.36, 1) 0.15s forwards;
        }
        html.es-anim .es-mask-2 .es-mask-line { animation-delay: 0.28s; }
        @keyframes es-rise { to { transform: translateY(0); } }

        html.es-anim .es-fade-up {
            opacity: 0;
            animation: es-fade-up 0.85s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }
        @keyframes es-fade-up {
            from { opacity: 0; transform: translateY(26px); }
            to   { opacity: 1; transform: none; }
        }
        html.es-anim .es-d-1 { animation-delay: 0.05s; }
        html.es-anim .es-d-2 { animation-delay: 0.45s; }
        html.es-anim .es-d-3 { animation-delay: 0.6s; }
        html.es-anim .es-d-4 { animation-delay: 0.75s; }
        html.es-anim .es-d-5 { animation-delay: 1.3s; }

        .es-gradient-anim {
            background-size: 200% 200%;
            animation: es-gradient-shift 6s ease infinite;
        }
        @keyframes es-gradient-shift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        /* Floating hero chips */
        .es-chip { will-change: transform; }
        html.es-anim .es-chip {
            opacity: 0;
            animation: es-chip-in 1.1s cubic-bezier(0.22, 1, 0.36, 1) forwards;
            animation-delay: var(--in, 0.9s);
        }
        @keyframes es-chip-in {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        .es-chip-float { animation: float 7s ease-in-out infinite; animation-delay: var(--fd, 0s); }
        .es-wobble { animation: es-wobble 9s ease-in-out infinite; transform-style: preserve-3d; }
        @keyframes es-wobble {
            0%, 100% { transform: perspective(600px) rotateY(-10deg) rotateX(4deg); }
            50% { transform: perspective(600px) rotateY(12deg) rotateX(-4deg); }
        }
        .es-chip-card {
            box-shadow: 0 18px 50px -18px rgba(10, 15, 35, 0.35), 0 0 0 1px rgba(255, 255, 255, 0.06) inset;
        }

        /* Scroll cue */
        .es-cue {
            width: 26px; height: 42px;
            border-radius: 9999px;
            border: 2px solid rgba(120, 130, 160, 0.5);
            display: flex; justify-content: center;
        }
        .es-cue-dot {
            width: 4px; height: 9px; border-radius: 9999px;
            margin-top: 7px;
            background: linear-gradient(180deg, #4E81FA, #22D3EE);
            animation: es-cue 2s cubic-bezier(0.45, 0, 0.55, 1) infinite;
        }
        @keyframes es-cue {
            0% { transform: translateY(0); opacity: 1; }
            70% { transform: translateY(14px); opacity: 0; }
            100% { transform: translateY(0); opacity: 0; }
        }

        /* --------------------------------------------------------------
           Marquee
           -------------------------------------------------------------- */

        .es-marquee-mask {
            mask-image: linear-gradient(90deg, transparent, black 12%, black 88%, transparent);
            -webkit-mask-image: linear-gradient(90deg, transparent, black 12%, black 88%, transparent);
        }
        .es-marquee { overflow: hidden; }
        .es-marquee-track {
            display: flex;
            width: max-content;
            gap: 0.875rem;
            padding-right: 0.875rem;
            will-change: transform;
            animation: es-marquee-slide 48s linear infinite;
        }
        [data-marquee="-1"] .es-marquee-track { animation-direction: reverse; }
        @keyframes es-marquee-slide {
            from { transform: translateX(0); }
            to   { transform: translateX(-50%); }
        }

        /* --------------------------------------------------------------
           Showcase: 3D screening room
           -------------------------------------------------------------- */

        .es-persp { perspective: 1200px; }
        .es-frame {
            transform-origin: 50% 10%;
            will-change: transform;
        }
        @media (min-width: 1024px) {
            html.es-anim .es-frame { transform: perspective(1200px) rotateX(16deg) scale(0.93); }
        }
        .es-frame-glow {
            position: absolute;
            left: 12%; right: 12%; bottom: -34px;
            height: 70px;
            border-radius: 9999px;
            background: linear-gradient(90deg, #4E81FA, #0EA5E9, #22D3EE);
            filter: blur(46px);
            opacity: 0;
            pointer-events: none;
        }
        .es-play-ring {
            animation: es-play-ping 2.4s cubic-bezier(0, 0, 0.2, 1) infinite;
        }
        @keyframes es-play-ping {
            0% { transform: scale(1); opacity: 0.6; }
            80%, 100% { transform: scale(1.7); opacity: 0; }
        }

        /* --------------------------------------------------------------
           Bento cards: tilt, glare, cursor border glow, mockup life
           -------------------------------------------------------------- */

        .es-tilt-inner {
            transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.35s ease;
            will-change: transform;
        }
        .es-bento:hover .es-tilt-inner { box-shadow: 0 30px 60px -20px rgba(10, 15, 35, 0.25); }
        .dark .es-bento:hover .es-tilt-inner { box-shadow: 0 30px 60px -20px rgba(0, 0, 0, 0.6); }

        .es-glare {
            position: absolute; inset: 0;
            opacity: 0;
            transition: opacity 0.35s ease;
            pointer-events: none;
            background: radial-gradient(620px circle at var(--gx, 50%) var(--gy, 50%), rgba(78, 129, 250, 0.09), transparent 45%);
        }
        .dark .es-glare {
            background: radial-gradient(620px circle at var(--gx, 50%) var(--gy, 50%), rgba(255, 255, 255, 0.09), transparent 45%);
        }
        .es-bento:hover .es-glare { opacity: 1; }

        .es-ring-glow { display: none; }
        @supports ((mask-composite: exclude) or (-webkit-mask-composite: xor)) {
            .es-ring-glow {
                display: block;
                position: absolute; inset: 0;
                border-radius: 1.5rem;
                padding: 1.5px;
                opacity: 0;
                transition: opacity 0.35s ease;
                pointer-events: none;
                background: radial-gradient(420px circle at var(--gx, 50%) var(--gy, 50%), rgba(78, 129, 250, 0.65), rgba(34, 211, 238, 0.25) 45%, transparent 70%);
                -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
                -webkit-mask-composite: xor;
                mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
                mask-composite: exclude;
            }
            .es-bento:hover .es-ring-glow { opacity: 1; }
        }

        /* Ticket QR laser scan */
        .es-laser {
            position: absolute;
            left: 6%; right: 6%;
            top: 14%;
            height: 2px;
            border-radius: 9999px;
            background: linear-gradient(90deg, transparent, #22D3EE, transparent);
            box-shadow: 0 0 14px rgba(34, 211, 238, 0.85);
            animation: es-laser 2.8s ease-in-out infinite;
        }
        @keyframes es-laser {
            0%, 100% { top: 14%; opacity: 0.9; }
            50% { top: 82%; opacity: 1; }
        }
        .es-checkin {
            opacity: 0;
            transform: translateY(10px) scale(0.9);
            transition: all 0.45s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .es-bento:hover .es-checkin { opacity: 1; transform: none; }

        /* Analytics bars grow when the card reveals */
        .es-bar {
            transform: scaleY(0.06);
            transform-origin: bottom;
            transition: transform 1.1s cubic-bezier(0.22, 1, 0.36, 1);
            transition-delay: var(--bd, 0.3s);
        }
        [data-reveal].is-revealed .es-bar, html:not(.es-anim) .es-bar { transform: scaleY(1); }

        /* AI: parsed fields materialize when the card reveals */
        .es-ai-field {
            opacity: 0;
            transform: translateY(8px);
            transition: all 0.55s cubic-bezier(0.22, 1, 0.36, 1);
            transition-delay: calc(var(--i, 0) * 0.18s + 0.5s);
        }
        [data-reveal].is-revealed .es-ai-field, html:not(.es-anim) .es-ai-field { opacity: 1; transform: none; }

        /* Calendar sync pulse traveling between tiles */
        .es-sync-dot {
            position: absolute;
            top: 50%;
            left: 14%;
            width: 8px; height: 8px;
            margin-top: -4px;
            border-radius: 9999px;
            background: #22D3EE;
            box-shadow: 0 0 12px rgba(34, 211, 238, 0.9);
            animation: es-sync-dot 2.6s ease-in-out infinite;
        }
        @keyframes es-sync-dot {
            0% { left: 14%; opacity: 0; }
            12% { opacity: 1; }
            48% { left: calc(86% - 8px); opacity: 1; }
            60% { left: calc(86% - 8px); opacity: 0; }
            61% { left: 14%; opacity: 0; }
            100% { left: 14%; opacity: 0; }
        }

        /* --------------------------------------------------------------
           Gallery: pinned horizontal rail (desktop, motion allowed)
           -------------------------------------------------------------- */

        .es-rail-clip {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scroll-snap-type: x proximity;
            scrollbar-width: none;
        }
        .es-rail-clip::-webkit-scrollbar { display: none; }
        .es-rail { display: flex; width: max-content; will-change: transform; }
        .es-shot { scroll-snap-align: center; }
        .es-rail-progress-wrap { display: none; }

        @media (min-width: 1024px) {
            html.es-anim .es-gallery { height: 300vh; }
            html.es-anim .es-gallery .es-gallery-pin {
                position: sticky;
                top: 0;
                height: 100vh;
                display: flex;
                flex-direction: column;
                justify-content: center;
                overflow: hidden;
            }
            html.es-anim .es-gallery .es-rail-clip { overflow: visible; }
            html.es-anim .es-gallery .es-rail-progress-wrap { display: block; }
        }

        .es-rail-progress {
            transform: scaleX(0);
            transform-origin: left;
            background: linear-gradient(90deg, #4E81FA, #0EA5E9, #22D3EE);
        }

        /* --------------------------------------------------------------
           Integrations orbit
           -------------------------------------------------------------- */

        .es-orbit-stage { position: relative; height: 36rem; }
        .es-ring-wrap {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
        }
        .es-ring {
            position: relative;
            width: 100%; height: 100%;
            border-radius: 9999px;
            border: 1px dashed rgba(78, 129, 250, 0.3);
        }
        .dark .es-ring { border-color: rgba(110, 160, 255, 0.22); }
        .es-ring-1 { animation: es-spin 52s linear infinite; }
        .es-ring-2 { animation: es-spin-rev 78s linear infinite; }
        .es-ring-1 .es-orbit-logo { animation: es-spin-rev 52s linear infinite; }
        .es-ring-2 .es-orbit-logo { animation: es-spin 78s linear infinite; }
        @keyframes es-spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        @keyframes es-spin-rev { from { transform: rotate(0deg); } to { transform: rotate(-360deg); } }
        .es-orbit-stage:hover .es-ring,
        .es-orbit-stage:hover .es-orbit-logo { animation-play-state: paused; }

        .es-orbit-item {
            position: absolute;
            top: 50%; left: 50%;
            width: 6rem; height: 6rem;
            margin: -3rem 0 0 -3rem;
            transform: rotate(var(--a)) translateX(var(--r));
        }
        .es-orbit-unrot {
            width: 100%; height: 100%;
            transform: rotate(calc(-1 * var(--a)));
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .es-pulse {
            position: absolute;
            inset: 0;
            border-radius: 9999px;
            border: 1px solid rgba(78, 129, 250, 0.45);
            animation: es-pulse 3.2s cubic-bezier(0, 0, 0.2, 1) infinite;
        }
        @keyframes es-pulse {
            0% { transform: scale(1); opacity: 0.7; }
            80%, 100% { transform: scale(2.1); opacity: 0; }
        }

        /* --------------------------------------------------------------
           Odometer stats
           -------------------------------------------------------------- */

        .es-od { display: inline-flex; line-height: 1; }
        .es-od-col {
            display: inline-block;
            height: 1em;
            overflow: hidden;
            vertical-align: top;
        }
        /* The translateY on the strip breaks background-clip:text
           propagation from the parent .text-gradient, so each digit
           carries its own gradient. */
        .es-od-strip span {
            background: linear-gradient(135deg, #4E81FA 0%, #0EA5E9 50%, #22D3EE 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .es-od-strip {
            display: flex;
            flex-direction: column;
            transition: transform 2.1s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .es-od-strip span { display: block; height: 1em; line-height: 1; }

        /* --------------------------------------------------------------
           How it works: pinned story
           -------------------------------------------------------------- */

        .es-step { transition: opacity 0.5s ease, transform 0.5s ease; }
        .es-steps-progress {
            transform: scaleY(0);
            transform-origin: top;
        }
        [data-active-step="all"] .es-steps-progress { transform: scaleY(1); }

        .es-scene {
            position: absolute;
            inset: 0;
            opacity: 0;
            transform: translateY(16px) scale(0.985);
            transition: opacity 0.55s ease, transform 0.55s cubic-bezier(0.22, 1, 0.36, 1);
            pointer-events: none;
        }
        [data-active-step="0"] .es-scene-0,
        [data-active-step="all"] .es-scene-0,
        [data-active-step="1"] .es-scene-1,
        [data-active-step="2"] .es-scene-2 { opacity: 1; transform: none; }

        .es-pop {
            opacity: 0;
            transform: translateY(10px) scale(0.97);
            transition: all 0.5s cubic-bezier(0.22, 1, 0.36, 1);
            transition-delay: calc(var(--i, 0) * 0.15s + 0.2s);
        }
        [data-active-step="0"] .es-scene-0 .es-pop,
        [data-active-step="all"] .es-scene-0 .es-pop,
        [data-active-step="1"] .es-scene-1 .es-pop,
        [data-active-step="2"] .es-scene-2 .es-pop { opacity: 1; transform: none; }

        .es-caret {
            display: inline-block;
            width: 2px; height: 1.1em;
            margin-left: 2px;
            vertical-align: text-bottom;
            background: #22D3EE;
            animation: es-caret 1.1s step-end infinite;
        }
        @keyframes es-caret { 0%, 100% { opacity: 1; } 50% { opacity: 0; } }

        @media (min-width: 1024px) {
            html.es-anim .es-steps { height: 280vh; }
            html.es-anim .es-steps .es-steps-pin {
                position: sticky;
                top: 0;
                height: 100vh;
                display: flex;
                align-items: center;
                overflow: hidden;
            }
            html.es-anim .es-steps:not([data-active-step="all"]) .es-step { opacity: 0.32; }
            html.es-anim .es-steps[data-active-step="0"] .es-step-0,
            html.es-anim .es-steps[data-active-step="1"] .es-step-1,
            html.es-anim .es-steps[data-active-step="2"] .es-step-2 { opacity: 1; }
        }

        /* --------------------------------------------------------------
           Finale
           -------------------------------------------------------------- */

        .es-finale-panel {
            background:
                radial-gradient(110% 130% at 50% 0%, rgba(23, 37, 84, 0.9) 0%, rgba(10, 10, 15, 0.98) 58%),
                #0a0a0f;
        }
        .es-claim:focus-within {
            border-color: rgba(78, 129, 250, 0.8);
            box-shadow: 0 0 0 4px rgba(78, 129, 250, 0.25);
        }

        /* --------------------------------------------------------------
           Reduced motion: freeze every homepage animation. The reveal
           system is already handled globally in marketing.css.
           -------------------------------------------------------------- */

        @media (prefers-reduced-motion: reduce) {
            .es-aurora,
            .es-chip-float,
            .es-wobble,
            .es-cue-dot,
            .es-marquee-track,
            .es-laser,
            .es-sync-dot,
            .es-ring-1,
            .es-ring-2,
            .es-orbit-logo,
            .es-pulse,
            .es-play-ring,
            .es-caret,
            .es-gradient-anim {
                animation: none !important;
            }
            .es-marquee-track {
                width: auto;
                flex-wrap: wrap;
                justify-content: center;
            }
            .es-marquee-mask {
                mask-image: none;
                -webkit-mask-image: none;
            }
            .es-bar { transform: scaleY(1); transition: none; }
            .es-ai-field, .es-pop, .es-scene { opacity: 1; transform: none; transition: none; }
            .es-spot { display: none; }
        }
    </style>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule",
        "description": "The simple and free way to share your event schedule. Perfect for musicians, venues, event organizers, and vendors.",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": ["Web", "Android", "iOS"],
        "screenshot": "{{ config('app.url') }}/images/social/home.png",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD"
        }
    }
    </script>
    <x-seo.howto-schema
        name="How to share your event schedule"
        description="Get your event schedule live and shared with your audience in three simple steps."
        :steps="[
            ['name' => 'Create your schedule', 'text' => 'Sign up free. Add your events manually or import from Google Calendar.'],
            ['name' => 'Share your link', 'text' => 'Get your custom URL. Put it in your bio, website, or anywhere you want.'],
            ['name' => 'Grow your audience', 'text' => 'Fans follow your schedule. Send them newsletters and notify them when you add new events.'],
        ]"
    />
    <x-seo.faq-schema :items="[
        ['q' => 'Is Event Schedule free?', 'a' => 'Yes, Event Schedule is free to use with unlimited events and schedules. Pro and Enterprise plans add features like ticketing, event boosting, custom branding, and AI tools.'],
        ['q' => 'Can I sell tickets with Event Schedule?', 'a' => 'Yes, you can sell tickets with zero platform fees using Stripe or Invoice Ninja. Tickets include QR codes for check-in, and you can create multiple ticket types with promo codes.'],
        ['q' => 'Does Event Schedule sync with Google Calendar?', 'a' => 'Yes, Event Schedule offers two-way Google Calendar sync with real-time webhook updates. You can also sync with any CalDAV-compatible calendar server.'],
        ['q' => 'Can I selfhost Event Schedule?', 'a' => 'Yes, Event Schedule is 100% open source. You can selfhost it on your own server for full control over your data, or use the hosted platform at eventschedule.com.'],
        ['q' => 'Who is Event Schedule for?', 'a' => 'Event Schedule is built for musicians, DJs, comedians, venues, bars, theaters, event curators, and anyone who needs to share an event schedule with their audience.'],
    ]" />
    </x-slot>

    <!-- ============================================================ -->
    <!-- 1. Hero: the aurora stage                                    -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(100svh-4rem)] items-center overflow-hidden bg-white dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1"></div>
            <div class="es-aurora es-aurora-2"></div>
            <div class="es-aurora es-aurora-3"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <div class="es-spot absolute inset-0"></div>
        </div>

        <!-- Floating product chips (decorative, wide screens only) -->
        <div class="pointer-events-none absolute inset-0 hidden xl:block" aria-hidden="true">
            <!-- Mini event card -->
            <div class="es-chip absolute left-[5%] top-[22%]" data-depth="26" style="--in: 0.9s;">
                <div class="es-chip-float" style="--fd: 0s;">
                    <div class="es-chip-card glass flex items-center gap-3 rounded-2xl p-3.5 backdrop-blur-xl">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-[#4E81FA] to-[#22D3EE] text-white">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" /></svg>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">Jazz Night</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Fri · 8:00 PM</div>
                        </div>
                        <div class="ml-2 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400">$25</div>
                    </div>
                </div>
            </div>
            <!-- QR ticket stub -->
            <div class="es-chip absolute right-[5%] top-[17%]" data-depth="36" style="--in: 1.05s;">
                <div class="es-chip-float" style="--fd: 1.4s;">
                    <div class="es-wobble">
                        <div class="es-chip-card glass rounded-2xl p-3.5 text-center backdrop-blur-xl">
                            <div class="mb-2 text-[9px] font-bold uppercase tracking-[0.22em] text-gray-400 dark:text-gray-500">Admit One</div>
                            <svg class="mx-auto h-14 w-14 text-gray-900 dark:text-white" viewBox="0 0 29 29" fill="currentColor" aria-hidden="true">
                                <path d="M0 0h9v9H0V0zm2 2v5h5V2H2zm1 1h3v3H3V3zm17-3h9v9h-9V0zm2 2v5h5V2h-5zm1 1h3v3h-3V3zM0 20h9v9H0v-9zm2 2v5h5v-5H2zm1 1h3v3H3v-3zM12 0h2v2h-2V0zm3 0h2v4h-2V0zm-3 4h2v3h-2V4zm3 3h4v2h-4V7zm-3 3h3v2h-3v-2zm5 0h2v3h-2v-3zm7 1h2v2h-2v-2zm3-1h2v4h-2v-4zM0 12h2v2H0v-2zm3 0h4v2H3v-2zm5 1h2v4H8v-4zm3 3h2v2h-2v-2zm3-2h3v2h-3v-2zm5 1h2v3h-2v-3zm3 1h4v2h-4v-2zm5 1h2v2h-2v-2zm-15 4h4v2h-4v-2zm5 1h2v2h-2v-2zm3-2h2v4h-2v-4zm3 2h4v2h-4v-2zm-7 3h2v4h-2v-4zm-3 1h2v3h-2v-3zm8 0h3v2h-3v-2zm5-1h2v4h-2v-4z"/>
                            </svg>
                            <div class="mt-2 text-[10px] font-semibold text-gray-500 dark:text-gray-400">GA x1 · #0042</div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Calendar date tile -->
            <div class="es-chip absolute left-[8%] top-[60%]" data-depth="42" style="--in: 1.2s;">
                <div class="es-chip-float" style="--fd: 0.7s;">
                    <div class="es-chip-card glass w-24 overflow-hidden rounded-2xl text-center backdrop-blur-xl">
                        <div class="bg-gradient-to-r from-[#4E81FA] to-[#0EA5E9] py-1 text-[10px] font-bold uppercase tracking-widest text-white">Jul</div>
                        <div class="py-2.5">
                            <div class="text-3xl font-black leading-none text-gray-900 dark:text-white">18</div>
                            <div class="mt-1 text-[10px] font-medium text-gray-500 dark:text-gray-400">3 events</div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Ticket sold toast -->
            <div class="es-chip absolute right-[6%] top-[63%]" data-depth="24" style="--in: 1.35s;">
                <div class="es-chip-float" style="--fd: 2.1s;">
                    <div class="es-chip-card glass flex items-center gap-3 rounded-2xl p-3.5 backdrop-blur-xl">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-500/20">
                            <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        </span>
                        <div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">Ticket sold</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Front Row x2 · $50.00</div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- New follower toast -->
            <div class="es-chip absolute left-[16%] top-[79%]" data-depth="18" style="--in: 1.5s;">
                <div class="es-chip-float" style="--fd: 3s;">
                    <div class="es-chip-card glass flex items-center gap-3 rounded-2xl p-3 backdrop-blur-xl">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-sky-400 to-cyan-400 text-xs font-bold text-white">M</span>
                        <div>
                            <div class="text-xs font-semibold text-gray-900 dark:text-white">New follower</div>
                            <div class="text-[11px] text-gray-500 dark:text-gray-400">Maya · just now</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-5xl px-4 py-28 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-2 rounded-full glass px-4 py-2">
                <span class="relative flex h-2 w-2">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-green-500"></span>
                </span>
                <span class="text-sm text-gray-600 dark:text-gray-300">Free forever. No credit card.</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.7rem] font-black leading-[1.04] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Plan, promote, and share</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient es-gradient-anim">event calendars</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-2xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Share events, sell tickets, and grow your audience. Built for venues, performers, and communities.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#showcase" data-magnetic="0.16" class="group inline-flex items-center justify-center gap-2.5 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-shadow hover:shadow-lg dark:text-white">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-gray-900/10 transition-colors group-hover:bg-gray-900/20 dark:bg-white/10 dark:group-hover:bg-white/20">
                        <svg class="h-3.5 w-3.5 ltr:ml-0.5 rtl:mr-0.5 rtl:rotate-180" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                    </span>
                    Watch the 2-minute demo
                </a>
                <a href="{{ app_url('/sign_up') }}" data-magnetic="0.16" class="group relative inline-flex items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-[#4E81FA] via-[#0EA5E9] to-[#22D3EE] px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-blue-500/25 transition-shadow hover:shadow-2xl hover:shadow-blue-500/40">
                    <span class="relative z-10 flex items-center gap-2">
                        Start for free
                        <svg class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </span>
                    <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                </a>
            </div>

            <p class="es-fade-up es-d-4 mt-6 text-sm text-gray-500 dark:text-gray-400">Set up in under 2 minutes</p>
        </div>

        <div class="es-fade-up es-d-5 pointer-events-none absolute bottom-7 left-1/2 hidden -translate-x-1/2 sm:block" aria-hidden="true">
            <div class="es-cue"><span class="es-cue-dot"></span></div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. Marquee: who it's for                                     -->
    <!-- ============================================================ -->
    @php
        $marqueePersonas = [
            ['Musicians', 'bg-blue-500'],
            ['Venues', 'bg-sky-500'],
            ['DJs', 'bg-cyan-500'],
            ['Promoters', 'bg-blue-400'],
            ['Food Trucks', 'bg-cyan-500'],
            ['Theaters', 'bg-amber-500'],
            ['Bands', 'bg-emerald-500'],
            ['Festivals', 'bg-rose-500'],
        ];
    @endphp
    <section class="relative overflow-hidden border-y border-gray-200 bg-white py-10 dark:border-white/10 dark:bg-[#0a0a0f]">
        <h2 class="sr-only">Who uses Event Schedule</h2>
        <div class="es-marquee-mask space-y-4">
            <div class="es-marquee" data-marquee="1">
                <div class="es-marquee-track">
                    @for ($i = 0; $i < 2; $i++)
                        @foreach ($marqueePersonas as [$persona, $dot])
                            <span class="glass flex items-center gap-2.5 rounded-full px-6 py-3 text-lg font-semibold text-gray-800 dark:text-gray-200">
                                <span class="h-2 w-2 rounded-full {{ $dot }}" aria-hidden="true"></span>
                                {{ $persona }}
                            </span>
                        @endforeach
                    @endfor
                </div>
            </div>
            <div class="es-marquee" data-marquee="-1">
                <div class="es-marquee-track">
                    @for ($i = 0; $i < 2; $i++)
                        @foreach (array_reverse($marqueePersonas) as [$persona, $dot])
                            <span class="glass flex items-center gap-2.5 rounded-full px-6 py-3 text-lg font-semibold text-gray-800 dark:text-gray-200">
                                <span class="h-2 w-2 rounded-full {{ $dot }}" aria-hidden="true"></span>
                                {{ $persona }}
                            </span>
                        @endforeach
                    @endfor
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Showcase: the 3D screening room                           -->
    <!-- ============================================================ -->
    <section id="showcase" class="relative scroll-mt-24 overflow-hidden bg-white pb-24 pt-16 dark:bg-[#0a0a0f] lg:pb-36 lg:pt-24">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="absolute left-1/2 top-0 h-[420px] w-[820px] -translate-x-1/2 rounded-full bg-gradient-to-b from-blue-600/10 to-transparent blur-[100px]"></div>
        </div>
        <div class="relative z-10 mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    See it in <span class="text-gradient">action</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    From first event to sold-out show, in one short tour.
                </p>
            </div>

            <div class="es-persp relative" data-scene="showcase">
                <div class="es-frame relative overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl shadow-blue-900/20 dark:border-white/10 dark:bg-[#101016]">
                    <!-- Browser chrome -->
                    <div class="flex items-center gap-3 border-b border-gray-200 bg-gray-50 px-4 py-3 dark:border-white/10 dark:bg-white/5" aria-hidden="true">
                        <span class="flex gap-1.5">
                            <span class="h-3 w-3 rounded-full bg-[#FF5F57]"></span>
                            <span class="h-3 w-3 rounded-full bg-[#FEBC2E]"></span>
                            <span class="h-3 w-3 rounded-full bg-[#28C840]"></span>
                        </span>
                        <span class="mx-auto flex items-center gap-1.5 rounded-lg bg-white px-4 py-1 text-xs font-medium text-gray-500 shadow-sm dark:bg-white/10 dark:text-gray-400">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            eventschedule.com
                        </span>
                        <span class="w-14"></span>
                    </div>
                    <!-- Click-to-play video facade -->
                    <div class="relative aspect-video bg-black">
                        <a href="https://www.youtube-nocookie.com/embed/IL8Fj0p6Lz8"
                           target="_blank"
                           rel="noopener"
                           data-video-facade
                           data-video-src="https://www.youtube-nocookie.com/embed/IL8Fj0p6Lz8"
                           data-video-title="Event Schedule Overview"
                           class="group absolute inset-0 block"
                           aria-label="Play the Event Schedule overview video">
                            <img src="https://img.youtube.com/vi/IL8Fj0p6Lz8/maxresdefault.jpg" alt="Event Schedule overview video" class="h-full w-full object-cover opacity-90 transition-opacity group-hover:opacity-100" loading="lazy" decoding="async" width="1280" height="720">
                            <span class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-black/10" aria-hidden="true"></span>
                            <span class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2" aria-hidden="true">
                                <span class="es-play-ring absolute inset-0 rounded-full bg-white/40"></span>
                                <span class="relative flex h-20 w-20 items-center justify-center rounded-full bg-white/95 shadow-2xl transition-transform group-hover:scale-110">
                                    <svg class="h-8 w-8 text-gray-900 ltr:ml-1 rtl:mr-1 rtl:rotate-180" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                </span>
                            </span>
                            <span class="absolute bottom-4 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-full bg-black/50 px-4 py-1.5 text-sm font-medium text-white backdrop-blur-sm" aria-hidden="true">
                                Watch the 2-minute overview
                            </span>
                        </a>
                    </div>
                </div>
                <div class="es-frame-glow" aria-hidden="true"></div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Flagship features: the bento                              -->
    <!-- ============================================================ -->
    <section id="features" class="relative scroll-mt-24 bg-gray-50 py-24 dark:bg-[#0f0f14] lg:py-32">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="mb-6 inline-flex items-center gap-2 rounded-full glass px-4 py-1.5" data-reveal>
                    <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-[#4E81FA] to-[#22D3EE]" aria-hidden="true"></span>
                    <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-600 dark:text-gray-300">The toolkit</span>
                </div>
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                    Everything you need to <span class="text-gradient">fill seats</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.16s;">
                    One platform for scheduling, ticketing, newsletters, and check-ins.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-6" data-reveal-group="110">

                <!-- Ticketing & QR Check-ins (large) -->
                <a href="{{ marketing_url('/features/ticketing') }}" class="es-bento group relative block md:col-span-2 lg:col-span-3 lg:row-span-2" data-tilt="3.5" data-reveal="panel" aria-label="Learn more about ticketing and QR check-ins">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] sm:p-8">
                        <div class="mb-3 flex items-center gap-3">
                            <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-sky-100 dark:bg-sky-500/20">
                                <svg class="h-6 w-6 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                            </span>
                            <h3 class="text-xl font-bold text-gray-900 transition-colors group-hover:text-sky-600 dark:text-white dark:group-hover:text-sky-400">Ticketing & QR Check-ins</h3>
                        </div>
                        <p class="mb-2 text-gray-600 dark:text-gray-400">Sell tickets online with multiple types, scan QR codes for check-ins, and track attendance with a live dashboard.</p>

                        <!-- Ticket mockup -->
                        <div class="relative my-4 flex min-h-[260px] flex-1 items-center justify-center" aria-hidden="true">
                            <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-sky-500/5 via-transparent to-cyan-500/10"></div>
                            <div class="relative w-56 -rotate-3 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl transition-transform duration-300 group-hover:rotate-0 dark:border-white/10 dark:bg-[#15151c]">
                                <div class="bg-gradient-to-r from-[#4E81FA] to-[#0EA5E9] px-4 py-3">
                                    <div class="text-[9px] font-bold uppercase tracking-[0.2em] text-white/70">Event Schedule</div>
                                    <div class="text-sm font-bold text-white">Jazz Night</div>
                                </div>
                                <div class="relative border-b border-dashed border-gray-300 dark:border-white/15">
                                    <span class="absolute -left-2 -top-2 h-4 w-4 rounded-full bg-gray-50 dark:bg-[#0f0f14]"></span>
                                    <span class="absolute -right-2 -top-2 h-4 w-4 rounded-full bg-gray-50 dark:bg-[#0f0f14]"></span>
                                </div>
                                <div class="relative px-4 py-4">
                                    <svg class="mx-auto h-24 w-24 text-gray-900 dark:text-white" viewBox="0 0 29 29" fill="currentColor">
                                        <path d="M0 0h9v9H0V0zm2 2v5h5V2H2zm1 1h3v3H3V3zm17-3h9v9h-9V0zm2 2v5h5V2h-5zm1 1h3v3h-3V3zM0 20h9v9H0v-9zm2 2v5h5v-5H2zm1 1h3v3H3v-3zM12 0h2v2h-2V0zm3 0h2v4h-2V0zm-3 4h2v3h-2V4zm3 3h4v2h-4V7zm-3 3h3v2h-3v-2zm5 0h2v3h-2v-3zm7 1h2v2h-2v-2zm3-1h2v4h-2v-4zM0 12h2v2H0v-2zm3 0h4v2H3v-2zm5 1h2v4H8v-4zm3 3h2v2h-2v-2zm3-2h3v2h-3v-2zm5 1h2v3h-2v-3zm3 1h4v2h-4v-2zm5 1h2v2h-2v-2zm-15 4h4v2h-4v-2zm5 1h2v2h-2v-2zm3-2h2v4h-2v-4zm3 2h4v2h-4v-2zm-7 3h2v4h-2v-4zm-3 1h2v3h-2v-3zm8 0h3v2h-3v-2zm5-1h2v4h-2v-4z"/>
                                    </svg>
                                    <div class="es-laser"></div>
                                    <div class="mt-3 flex items-center justify-between text-[10px] font-semibold text-gray-500 dark:text-gray-400">
                                        <span>GA x1</span>
                                        <span>#0042</span>
                                    </div>
                                </div>
                            </div>
                            <!-- Hover payoff: checked in -->
                            <div class="es-checkin absolute right-1 top-2 flex items-center gap-2 rounded-xl border border-emerald-200 bg-white px-3 py-2 shadow-lg dark:border-emerald-500/30 dark:bg-[#15151c]">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-500/20">
                                    <svg class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                </span>
                                <span class="text-xs font-semibold text-gray-900 dark:text-white">Checked in</span>
                            </div>
                            <div class="absolute bottom-2 left-1 rounded-xl border border-gray-200 bg-white px-3 py-2 shadow-lg dark:border-white/10 dark:bg-[#15151c]">
                                <span class="text-xs font-semibold text-gray-900 dark:text-white"><span data-count-to="142">142</span> checked in</span>
                            </div>
                        </div>

                        <div class="mt-auto flex flex-wrap items-center justify-between gap-3">
                            <span class="inline-flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                Secure payments by Stripe
                            </span>
                            <span class="inline-flex items-center gap-1 text-sm font-medium text-sky-600 transition-all group-hover:gap-2 dark:text-sky-400">
                                Learn more
                                <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </span>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </a>

                <!-- Newsletters (wide) -->
                <a href="{{ route('marketing.newsletters') }}" class="es-bento group relative block md:col-span-2 lg:col-span-3" data-tilt="4" data-reveal="panel" aria-label="Learn more about newsletters">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] sm:p-8">
                        <div class="flex flex-col gap-6 sm:flex-row">
                            <div class="flex-1">
                                <div class="mb-3 flex items-center gap-3">
                                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-sky-100 dark:bg-sky-500/20">
                                        <svg class="h-6 w-6 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    </span>
                                    <h3 class="text-xl font-bold text-gray-900 transition-colors group-hover:text-sky-600 dark:text-white dark:group-hover:text-sky-400">Newsletters</h3>
                                </div>
                                <p class="text-gray-600 dark:text-gray-400">Send branded emails to followers and ticket buyers with a drag-and-drop editor and A/B testing.</p>
                                <p class="mt-3 text-xs font-medium text-gray-400 dark:text-gray-500">Templates · Audience segments · A/B testing</p>
                                <span class="mt-4 inline-flex items-center gap-1 text-sm font-medium text-sky-600 transition-all group-hover:gap-2 dark:text-sky-400">
                                    Learn more
                                    <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                </span>
                            </div>
                            <!-- Email mockup -->
                            <div class="relative w-full shrink-0 sm:w-52" aria-hidden="true">
                                <div class="overflow-hidden rounded-xl border border-gray-200 shadow-lg dark:border-white/10">
                                    <div class="flex items-center gap-2 bg-gradient-to-r from-sky-500 to-cyan-500 px-3 py-2">
                                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-white/25">
                                            <svg class="h-2.5 w-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                        </span>
                                        <span class="text-[10px] font-semibold text-white">This Week's Events</span>
                                    </div>
                                    <div class="space-y-2 bg-white p-3 dark:bg-[#15151c]">
                                        <div class="flex h-10 items-center justify-center rounded-lg bg-gradient-to-r from-sky-100 to-cyan-100 text-[10px] font-bold text-sky-600 dark:from-sky-900/40 dark:to-cyan-900/40 dark:text-sky-400">Featured Event</div>
                                        <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-white/10"></div>
                                        <div class="h-2 w-3/4 rounded-full bg-gray-200 dark:bg-white/10"></div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <div class="h-9 rounded-lg bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900/40 dark:to-sky-900/40"></div>
                                            <div class="h-9 rounded-lg bg-gradient-to-br from-sky-100 to-cyan-100 dark:from-sky-900/40 dark:to-cyan-900/40"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="absolute -top-3 ltr:-right-2 rtl:-left-2 rounded-full border border-sky-200 bg-white px-3 py-1 text-[10px] font-semibold text-sky-600 shadow-md dark:border-sky-500/30 dark:bg-[#15151c] dark:text-sky-400">
                                    Sent to <span data-count-to="1,248">1,248</span> followers
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </a>

                <!-- AI-Powered -->
                <a href="{{ marketing_url('/features/ai') }}" class="es-bento group relative block lg:col-span-3" data-tilt="4" data-reveal="panel" aria-label="Learn more about AI-powered features">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] sm:p-8">
                        <div class="mb-3 flex items-center gap-3">
                            <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-500/20">
                                <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>
                            </span>
                            <h3 class="text-xl font-bold text-gray-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400">AI-Powered</h3>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400">Parse text and images, generate flyers and descriptions, create your brand style, and translate to 11 languages with AI.</p>

                        <!-- Flyer to event mockup -->
                        <div class="mt-5 grid grid-cols-[1fr_auto_1.2fr] items-center gap-3" aria-hidden="true">
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/5">
                                <div class="mb-2 flex h-10 items-center justify-center rounded-lg bg-gray-200 dark:bg-white/10">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                </div>
                                <div class="mb-1.5 h-1.5 w-full rounded-full bg-gray-200 dark:bg-white/10"></div>
                                <div class="h-1.5 w-2/3 rounded-full bg-gray-200 dark:bg-white/10"></div>
                            </div>
                            <svg class="h-5 w-5 animate-pulse-slow text-blue-500 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            <div class="space-y-1.5">
                                <div class="es-ai-field flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-1.5 text-xs font-medium text-gray-700 dark:border-blue-500/25 dark:bg-blue-500/10 dark:text-gray-300" style="--i: 0;">
                                    <svg class="h-3.5 w-3.5 shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    Sat, Jul 18 · 8:00 PM
                                </div>
                                <div class="es-ai-field flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-1.5 text-xs font-medium text-gray-700 dark:border-blue-500/25 dark:bg-blue-500/10 dark:text-gray-300" style="--i: 1;">
                                    <svg class="h-3.5 w-3.5 shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    The Blue Note
                                </div>
                                <div class="es-ai-field flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-1.5 text-xs font-medium text-gray-700 dark:border-blue-500/25 dark:bg-blue-500/10 dark:text-gray-300" style="--i: 2;">
                                    <svg class="h-3.5 w-3.5 shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                                    $25 · 120 tickets
                                </div>
                            </div>
                        </div>

                        <span class="mt-auto inline-flex items-center gap-1 pt-4 text-sm font-medium text-blue-600 transition-all group-hover:gap-2 dark:text-blue-400">
                            Learn more
                            <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </span>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </a>

                <!-- Boost -->
                <a href="{{ route('marketing.boost') }}" class="es-bento group relative block lg:col-span-2" data-tilt="5" data-reveal="panel" aria-label="Learn more about Boost">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-3 flex items-center gap-3">
                            <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-orange-100 dark:bg-orange-500/20">
                                <svg class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                            </span>
                            <h3 class="text-xl font-bold text-gray-900 transition-colors group-hover:text-orange-600 dark:text-white dark:group-hover:text-orange-400">Boost</h3>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Turn any event into a Facebook or Instagram ad in minutes. Set your budget, pick your audience, and launch with no ad experience needed.</p>

                        <!-- Sponsored post mockup -->
                        <div class="mx-auto mt-5 w-40 overflow-hidden rounded-xl border border-gray-200 shadow-lg dark:border-white/10" aria-hidden="true">
                            <div class="flex items-center gap-1.5 bg-white px-2.5 py-2 dark:bg-[#15151c]">
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-gradient-to-br from-orange-400 to-amber-500">
                                    <svg class="h-2.5 w-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" /></svg>
                                </span>
                                <div>
                                    <div class="text-[9px] font-semibold text-gray-900 dark:text-white">The Blue Note</div>
                                    <div class="text-[8px] text-gray-400">Sponsored</div>
                                </div>
                            </div>
                            <div class="flex h-16 items-center justify-center bg-gradient-to-br from-orange-200 to-amber-200 text-[10px] font-bold text-orange-600 dark:from-orange-800/60 dark:to-amber-800/60 dark:text-orange-300">Jazz Night</div>
                            <div class="flex items-center justify-between bg-white px-2.5 py-1.5 dark:bg-[#15151c]">
                                <span class="flex items-center gap-1 text-[9px] text-gray-500 dark:text-gray-400">
                                    <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                                    <span data-count-to="142">142</span>
                                </span>
                                <span class="rounded bg-gradient-to-r from-orange-500 to-amber-500 px-2 py-0.5 text-[8px] font-semibold text-white">Learn More</span>
                            </div>
                        </div>

                        <span class="mt-auto inline-flex items-center gap-1 pt-4 text-sm font-medium text-orange-600 transition-all group-hover:gap-2 dark:text-orange-400">
                            Learn more
                            <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </span>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </a>

                <!-- Built-in Analytics -->
                <a href="{{ route('marketing.analytics') }}" class="es-bento group relative block lg:col-span-2" data-tilt="5" data-reveal="panel" aria-label="Learn more about built-in analytics">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-3 flex items-center gap-3">
                            <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-500/20">
                                <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                            </span>
                            <h3 class="text-xl font-bold text-gray-900 transition-colors group-hover:text-emerald-600 dark:text-white dark:group-hover:text-emerald-400">Built-in Analytics</h3>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Track page views, device breakdown, top events, and traffic sources. No external services required.</p>

                        <!-- Bars mockup -->
                        <div class="mt-5" aria-hidden="true">
                            <div class="flex h-24 items-end justify-center gap-2.5 px-2">
                                <div class="es-bar w-7 rounded-t-lg bg-gradient-to-t from-emerald-500/70 to-emerald-400" style="height: 38%; --bd: 0.25s;"></div>
                                <div class="es-bar w-7 rounded-t-lg bg-gradient-to-t from-emerald-500/70 to-emerald-400" style="height: 62%; --bd: 0.35s;"></div>
                                <div class="es-bar w-7 rounded-t-lg bg-gradient-to-t from-emerald-500/70 to-emerald-400" style="height: 48%; --bd: 0.45s;"></div>
                                <div class="es-bar w-7 rounded-t-lg bg-gradient-to-t from-emerald-500/70 to-emerald-400" style="height: 82%; --bd: 0.55s;"></div>
                                <div class="es-bar w-7 rounded-t-lg bg-gradient-to-t from-[#4E81FA] to-[#22D3EE]" style="height: 100%; --bd: 0.65s;"></div>
                            </div>
                            <div class="mt-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400">
                                <span class="text-gray-900 dark:text-white" data-count-to="12,480">12,480</span> page views this month
                            </div>
                        </div>

                        <span class="mt-auto inline-flex items-center gap-1 pt-4 text-sm font-medium text-emerald-600 transition-all group-hover:gap-2 dark:text-emerald-400">
                            Learn more
                            <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </span>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </a>

                <!-- Calendar Sync -->
                <a href="{{ marketing_url('/features/calendar-sync') }}" class="es-bento group relative block lg:col-span-2" data-tilt="5" data-reveal="panel" aria-label="Learn more about calendar sync">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-3 flex items-center gap-3">
                            <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-500/20">
                                <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </span>
                            <h3 class="text-xl font-bold text-gray-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400">Calendar Sync</h3>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Two-way sync with Google Calendar. Let attendees add events to Apple, Google, or Outlook calendars.</p>

                        <!-- Sync mockup -->
                        <div class="relative mt-5 h-24 px-2" aria-hidden="true">
                            <div class="absolute left-[10%] top-1/2 -translate-y-1/2">
                                <div class="w-16 overflow-hidden rounded-lg border border-gray-200 shadow-md dark:border-white/10">
                                    <div class="bg-gradient-to-r from-[#4E81FA] to-[#0EA5E9] py-0.5 text-center text-[8px] font-bold text-white">ES</div>
                                    <div class="grid grid-cols-3 gap-0.5 bg-white p-1.5 dark:bg-[#15151c]">
                                        <span class="h-2 rounded-sm bg-blue-100 dark:bg-blue-500/30"></span><span class="h-2 rounded-sm bg-gray-100 dark:bg-white/10"></span><span class="h-2 rounded-sm bg-gray-100 dark:bg-white/10"></span>
                                        <span class="h-2 rounded-sm bg-gray-100 dark:bg-white/10"></span><span class="h-2 rounded-sm bg-cyan-100 dark:bg-cyan-500/30"></span><span class="h-2 rounded-sm bg-gray-100 dark:bg-white/10"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="absolute left-1/2 top-1/2 h-px w-[36%] -translate-x-1/2 -translate-y-1/2 border-t border-dashed border-blue-300 dark:border-blue-500/40"></div>
                            <div class="es-sync-dot"></div>
                            <div class="absolute right-[10%] top-1/2 -translate-y-1/2">
                                <div class="w-16 overflow-hidden rounded-lg border border-gray-200 shadow-md dark:border-white/10">
                                    <div class="bg-[#4285F4] py-0.5 text-center text-[8px] font-bold text-white">31</div>
                                    <div class="grid grid-cols-3 gap-0.5 bg-white p-1.5 dark:bg-[#15151c]">
                                        <span class="h-2 rounded-sm bg-gray-100 dark:bg-white/10"></span><span class="h-2 rounded-sm bg-blue-100 dark:bg-blue-500/30"></span><span class="h-2 rounded-sm bg-gray-100 dark:bg-white/10"></span>
                                        <span class="h-2 rounded-sm bg-cyan-100 dark:bg-cyan-500/30"></span><span class="h-2 rounded-sm bg-gray-100 dark:bg-white/10"></span><span class="h-2 rounded-sm bg-gray-100 dark:bg-white/10"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <span class="mt-auto inline-flex items-center gap-1 pt-4 text-sm font-medium text-blue-600 transition-all group-hover:gap-2 dark:text-blue-400">
                            Learn more
                            <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </span>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </a>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Everything else                                           -->
    <!-- ============================================================ -->
    @php
        $moreFeatures = [
            [
                'href' => marketing_url('/features/online-events'),
                'aria' => 'Learn more about online events',
                'title' => 'Online Events',
                'desc' => 'Host virtual events with any streaming platform. Easy toggle between in-person and online, with the link on every ticket.',
                'chip' => 'bg-sky-100 dark:bg-sky-500/20',
                'text' => 'text-sky-600 dark:text-sky-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />',
            ],
            [
                'href' => marketing_url('/features/polls'),
                'aria' => 'Learn more about event polls',
                'title' => 'Event Polls',
                'desc' => 'Add multiple choice polls. Guests vote and see real-time results.',
                'chip' => 'bg-blue-100 dark:bg-blue-500/20',
                'text' => 'text-blue-600 dark:text-blue-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />',
            ],
            [
                'href' => route('marketing.embed_calendar'),
                'aria' => 'Learn more about embed calendar',
                'title' => 'Embed Calendar',
                'desc' => 'Embed your schedule on any website with a simple iframe.',
                'chip' => 'bg-blue-100 dark:bg-blue-500/20',
                'text' => 'text-blue-600 dark:text-blue-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25A2.25 2.25 0 015.25 3h13.5A2.25 2.25 0 0121 5.25z" />',
            ],
            [
                'href' => marketing_url('/features/fan-videos'),
                'aria' => 'Learn more about fan videos and comments',
                'title' => 'Fan Videos & Comments',
                'desc' => 'Fans add YouTube videos and comments to your events, with your approval before anything goes live.',
                'chip' => 'bg-rose-100 dark:bg-rose-500/20',
                'text' => 'text-rose-600 dark:text-rose-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />',
            ],
            [
                'href' => marketing_url('/features/sub-schedules'),
                'aria' => 'Learn more about sub-schedules',
                'title' => 'Sub-schedules',
                'desc' => 'Organize events into categories. Perfect for venues with multiple rooms or event series.',
                'chip' => 'bg-rose-100 dark:bg-rose-500/20',
                'text' => 'text-rose-600 dark:text-rose-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />',
            ],
            [
                'href' => marketing_url('/features/event-graphics'),
                'aria' => 'Learn more about event graphics',
                'title' => 'Event Graphics',
                'desc' => 'Auto-generate shareable images and formatted text for your upcoming events. Ready for Instagram, WhatsApp, email, and more.',
                'chip' => 'bg-orange-100 dark:bg-orange-500/20',
                'text' => 'text-orange-600 dark:text-orange-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />',
            ],
            [
                'href' => marketing_url('/features/custom-fields'),
                'aria' => 'Learn more about custom fields',
                'title' => 'Custom Fields',
                'desc' => 'Collect additional info from ticket buyers with text, dropdown, date, and yes/no fields.',
                'chip' => 'bg-amber-100 dark:bg-amber-500/20',
                'text' => 'text-amber-600 dark:text-amber-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />',
            ],
            [
                'href' => route('marketing.private_events'),
                'aria' => 'Learn more about private events',
                'title' => 'Private Events',
                'desc' => 'Password-protect events for VIP audiences or invite-only gatherings. Control who sees what.',
                'chip' => 'bg-yellow-100 dark:bg-yellow-500/20',
                'text' => 'text-yellow-600 dark:text-yellow-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />',
            ],
            [
                'href' => marketing_url('/features/recurring-events'),
                'aria' => 'Learn more about recurring events',
                'title' => 'Recurring Events',
                'desc' => 'Set events to repeat weekly on chosen days with flexible end conditions and per-occurrence tickets.',
                'chip' => 'bg-lime-100 dark:bg-lime-500/20',
                'text' => 'text-lime-600 dark:text-lime-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />',
            ],
            [
                'href' => route('marketing.white_label'),
                'aria' => 'Learn more about white-label branding',
                'title' => 'White-label Branding',
                'desc' => 'Remove branding, add custom CSS, and match your brand.',
                'chip' => 'bg-emerald-100 dark:bg-emerald-500/20',
                'text' => 'text-emerald-600 dark:text-emerald-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />',
            ],
            [
                'href' => marketing_url('/features/team-scheduling'),
                'aria' => 'Learn more about team scheduling',
                'title' => 'Team Scheduling',
                'desc' => 'Invite your team so everyone can add events and manage tickets without sharing a login.',
                'chip' => 'bg-cyan-100 dark:bg-cyan-500/20',
                'text' => 'text-cyan-600 dark:text-cyan-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />',
            ],
            [
                'href' => marketing_url('/open-source'),
                'aria' => 'Learn more about open source and API',
                'title' => 'Open Source & API',
                'desc' => 'Selfhost for full control over your data. Integrate with your existing tools through our REST API.',
                'chip' => 'bg-gray-100 dark:bg-gray-500/20',
                'text' => 'text-gray-600 dark:text-gray-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />',
            ],
        ];
    @endphp
    <section id="more-features" class="relative scroll-mt-24 bg-gray-50 pb-24 dark:bg-[#0f0f14] lg:pb-32">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <h2 class="es-balance text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>
                    And everything else you'd expect
                </h2>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4" data-reveal-group="55">
                @foreach ($moreFeatures as $feature)
                    <a href="{{ $feature['href'] }}" class="group flex flex-col rounded-2xl border border-gray-200 bg-white p-5 transition-all duration-200 hover:-translate-y-1 hover:border-blue-300 hover:shadow-lg dark:border-white/10 dark:bg-white/[0.04] dark:hover:border-blue-500/40" data-reveal aria-label="{{ $feature['aria'] }}">
                        <div class="mb-2.5 flex items-center gap-3">
                            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $feature['chip'] }}">
                                <svg class="h-5 w-5 {{ $feature['text'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">{!! $feature['icon'] !!}</svg>
                            </span>
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ $feature['title'] }}</h3>
                        </div>
                        <p class="flex-grow text-sm text-gray-600 dark:text-gray-400">{{ $feature['desc'] }}</p>
                        <span class="mt-auto inline-flex items-center gap-1 pt-3 text-sm font-medium {{ $feature['text'] }} transition-all group-hover:gap-2">
                            Learn more
                            <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Gallery: pinned horizontal showcase                       -->
    <!-- ============================================================ -->
    @php
        $galleryShots = [
            ['file' => 'marketing_6', 'alt' => 'Community hub schedule page with a monthly calendar view', 'caption' => 'Community calendars'],
            ['file' => 'list_villageidiot', 'alt' => 'Music venue schedule page with event flyers and ticket buttons', 'caption' => 'Venues and nightlife'],
            ['file' => 'marketing_1', 'alt' => 'Yoga retreat schedule page with a custom tie-dye background', 'caption' => 'Classes and retreats'],
            ['file' => 'marketing_3', 'alt' => 'Art class event page with an auto-generated flyer', 'caption' => 'Event pages and flyers'],
            ['file' => 'list_battleofthebands', 'alt' => 'Band schedule page listing upcoming shows', 'caption' => 'Bands on tour'],
        ];
    @endphp
    <section class="es-gallery relative bg-white dark:bg-[#0a0a0f]" data-scene="gallery">
        <div class="es-gallery-pin py-24 lg:py-0">
            <div class="mx-auto mb-10 max-w-3xl px-4 text-center sm:px-6">
                <div class="mb-6 inline-flex items-center gap-2 rounded-full glass px-4 py-1.5" data-reveal>
                    <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-[#4E81FA] to-[#22D3EE]" aria-hidden="true"></span>
                    <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-600 dark:text-gray-300">Real pages</span>
                </div>
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                    Beautiful <span class="text-gradient">out of the box</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.16s;">
                    Every schedule gets a page worth sharing. Your colors, your background, your brand.
                </p>
            </div>

            <div class="es-rail-clip w-full" tabindex="0" aria-label="Gallery of example schedule pages">
                <div class="es-rail items-stretch gap-6 px-6 lg:gap-8 lg:px-[7vw]">
                    @foreach ($galleryShots as $shot)
                        <figure class="es-shot w-[78vw] shrink-0 sm:w-[400px]">
                            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl shadow-gray-900/10 dark:border-white/10 dark:bg-[#101016] dark:shadow-black/40">
                                <div class="flex items-center gap-1.5 border-b border-gray-200 bg-gray-50 px-4 py-2.5 dark:border-white/10 dark:bg-white/5" aria-hidden="true">
                                    <span class="h-2.5 w-2.5 rounded-full bg-[#FF5F57]"></span>
                                    <span class="h-2.5 w-2.5 rounded-full bg-[#FEBC2E]"></span>
                                    <span class="h-2.5 w-2.5 rounded-full bg-[#28C840]"></span>
                                </div>
                                <div class="relative h-[50vh] overflow-hidden lg:h-[54vh]">
                                    <picture>
                                        <source srcset="{{ asset('images/screenshots/' . $shot['file'] . '_800w.webp') }}" type="image/webp">
                                        <img src="{{ asset('images/screenshots/' . $shot['file'] . '_800w.jpg') }}" alt="{{ $shot['alt'] }}" class="h-full w-full object-cover object-top" loading="lazy" decoding="async" width="585" height="800">
                                    </picture>
                                </div>
                            </div>
                            <figcaption class="mt-4 text-center">
                                <span class="glass inline-block rounded-full px-4 py-1.5 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $shot['caption'] }}</span>
                            </figcaption>
                        </figure>
                    @endforeach

                    <!-- Rail finale card -->
                    <div class="es-shot flex w-[78vw] shrink-0 sm:w-[400px] flex-col">
                        <div class="relative flex flex-1 flex-col items-center justify-center overflow-hidden rounded-2xl border border-white/10 bg-gradient-to-br from-[#101830] via-[#0a0a0f] to-[#06202a] p-10 text-center shadow-2xl">
                            <div class="grid-overlay absolute inset-0 opacity-40" aria-hidden="true"></div>
                            <div class="absolute -top-20 left-1/2 h-64 w-64 -translate-x-1/2 rounded-full bg-[#4E81FA]/30 blur-[80px]" aria-hidden="true"></div>
                            <div class="relative">
                                <div class="mb-3 text-3xl font-black text-white">Yours is next</div>
                                <p class="mb-8 text-gray-400">Set up your page in minutes and make it unmistakably yours.</p>
                                <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#4E81FA] via-[#0EA5E9] to-[#22D3EE] px-6 py-3.5 font-semibold text-white shadow-xl shadow-blue-500/30 transition-transform hover:scale-105">
                                    Start for free
                                    <svg class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                </a>
                            </div>
                        </div>
                        <div class="mt-4 h-8" aria-hidden="true"></div>
                    </div>
                </div>
            </div>

            <div class="es-rail-progress-wrap mx-auto mt-10 w-48" aria-hidden="true">
                <div class="h-1 overflow-hidden rounded-full bg-gray-200 dark:bg-white/10">
                    <div class="es-rail-progress h-full w-full rounded-full"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Discover: live events from the community                  -->
    <!-- ============================================================ -->
    <section id="discover" class="relative scroll-mt-24 overflow-hidden bg-gray-50 py-24 dark:bg-[#0f0f14] lg:py-32">
        <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
            <div class="absolute left-1/4 top-10 h-[420px] w-[420px] rounded-full bg-gradient-to-r from-blue-600/10 to-sky-500/10 blur-[120px]"></div>
            <div class="absolute bottom-0 right-1/4 h-[360px] w-[360px] rounded-full bg-gradient-to-r from-sky-500/10 to-cyan-500/10 blur-[120px]"></div>
        </div>

        <div class="relative z-10 mx-auto max-w-6xl px-4 text-center sm:px-6 lg:px-8">
            <div class="mb-6 inline-flex items-center gap-2 rounded-full glass px-4 py-1.5" data-reveal>
                <svg aria-hidden="true" class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                </svg>
                <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-600 dark:text-gray-300">Discover</span>
            </div>

            <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                Discover events across the <span class="text-gradient">community</span>
            </h2>
            <p class="mx-auto mb-12 max-w-2xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.16s;">
                Upcoming events from across the community. Live music, fitness classes, comedy nights, community meetups, and more.
            </p>

            @if ($discoverEvents->count() > 0)
                <div class="grid grid-cols-1 gap-6 text-left sm:grid-cols-2 lg:grid-cols-3" data-reveal-group="90">
                    @foreach ($discoverEvents as $event)
                        <div data-reveal>
                            @include('marketing.partials.event-card', ['event' => $event])
                        </div>
                    @endforeach
                </div>

                <div class="mt-12 flex flex-col items-center gap-3" data-reveal>
                    <a href="{{ marketing_url('/browse') }}" data-magnetic="0.14" class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#4E81FA] via-[#0EA5E9] to-[#22D3EE] px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-blue-500/25 transition-shadow hover:shadow-xl hover:shadow-blue-500/40">
                        Browse all events
                        <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                    <a href="{{ marketing_url('/search') }}" class="text-sm text-gray-500 transition-colors hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400">
                        or search for something specific
                    </a>
                </div>
            @else
                {{-- No events yet: still offer a way in --}}
                <div data-reveal>
                    <a href="{{ marketing_url('/browse') }}" class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#4E81FA] via-[#0EA5E9] to-[#22D3EE] px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-blue-500/25 transition-all hover:scale-105">
                        Browse all events
                        <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                </div>
            @endif

            <p class="mt-10 text-gray-500 dark:text-gray-400" data-reveal>
                Run events of your own?
                <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center gap-1 font-semibold text-blue-600 transition-all hover:gap-2 dark:text-blue-400">
                    Get discovered in community search
                    <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Integrations: the orbit                                   -->
    <!-- ============================================================ -->
    <section id="integrations" class="relative scroll-mt-24 overflow-hidden bg-white py-24 dark:bg-[#0a0a0f] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-4 max-w-3xl text-center">
                <p class="mb-3 text-sm font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500" data-reveal>Integrates with</p>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal style="--reveal-delay: 0.08s;">
                    The tools you already use
                </h2>
            </div>

            <!-- Orbit (desktop) -->
            <div class="es-orbit-stage hidden lg:block" data-reveal="zoom" aria-hidden="false">
                <!-- Center -->
                <div class="absolute left-1/2 top-1/2 z-10 -translate-x-1/2 -translate-y-1/2">
                    <span class="es-pulse" aria-hidden="true"></span>
                    <span class="es-pulse" style="animation-delay: 1.6s;" aria-hidden="true"></span>
                    <span class="glass relative flex h-24 w-24 items-center justify-center rounded-3xl shadow-xl shadow-blue-500/20">
                        <img src="{{ asset('images/apple-touch-icon.png') }}" alt="Event Schedule" class="h-14 w-14 rounded-2xl" width="56" height="56" loading="lazy" decoding="async">
                    </span>
                </div>
                <!-- Inner ring -->
                <div class="es-ring-wrap h-[19rem] w-[19rem]">
                    <div class="es-ring es-ring-1">
                        <div class="es-orbit-item" style="--a: 0deg; --r: 9.5rem;">
                            <div class="es-orbit-unrot" style="--a: 0deg;">
                                <div class="es-orbit-logo">
                                    <a href="{{ marketing_url('/google-calendar') }}" class="group flex flex-col items-center gap-1.5" aria-label="Google Calendar integration">
                                        <span class="glass flex h-16 w-16 items-center justify-center rounded-2xl transition-transform group-hover:scale-110">
                                            <svg aria-hidden="true" class="h-9 w-9" viewBox="0 0 24 24" fill="none">
                                                <path d="M18.316 5.684H5.684v12.632h12.632V5.684z" fill="#fff"/>
                                                <path d="M21.053 22H5.684l-2.631-2.632V5.684L5.684 3h12.632L21.053 5.684V22z" fill="#4285F4"/>
                                                <path d="M18.316 22l2.737-2.632V22h-2.737z" fill="#1A73E8"/>
                                                <path d="M5.684 18.316L3.053 22V19.368l2.631-1.053z" fill="#1A73E8"/>
                                                <path d="M21.053 5.684L18.316 3v2.684h2.737z" fill="#1A73E8"/>
                                                <path d="M5.684 3L3.053 5.684h2.631V3z" fill="#EA4335"/>
                                                <path d="M18.316 5.684V3l2.737 2.684h-2.737z" fill="#34A853"/>
                                                <rect x="7" y="9" width="10" height="1" rx="0.5" fill="#fff"/>
                                                <rect x="7" y="12" width="7" height="1" rx="0.5" fill="#fff"/>
                                                <rect x="7" y="15" width="5" height="1" rx="0.5" fill="#fff"/>
                                            </svg>
                                        </span>
                                        <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400">Google Calendar</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="es-orbit-item" style="--a: 120deg; --r: 9.5rem;">
                            <div class="es-orbit-unrot" style="--a: 120deg;">
                                <div class="es-orbit-logo">
                                    <a href="{{ marketing_url('/stripe') }}" class="group flex flex-col items-center gap-1.5" aria-label="Stripe integration">
                                        <span class="glass flex h-16 w-16 items-center justify-center rounded-2xl transition-transform group-hover:scale-110">
                                            <svg aria-hidden="true" class="h-9 w-9" viewBox="0 0 24 24" fill="none">
                                                <rect x="1" y="4" width="22" height="16" rx="3" fill="#635BFF"/>
                                                <path d="M11.2 10.3c0-.66.6-1.12 1.45-1.12.95 0 1.95.45 2.55 1.05l.8-1.85c-.7-.55-1.7-.95-3.05-.95-2.2 0-3.6 1.15-3.6 3.05 0 3 4.1 2.5 4.1 3.8 0 .55-.5.95-1.35.95-1.1 0-2.3-.55-3-1.15l-.85 1.85c.85.7 2.1 1.15 3.55 1.15 2.25 0 3.75-1.1 3.75-3.05 0-3.25-4.35-2.7-4.35-3.73z" fill="#fff"/>
                                            </svg>
                                        </span>
                                        <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400">Stripe</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="es-orbit-item" style="--a: 240deg; --r: 9.5rem;">
                            <div class="es-orbit-unrot" style="--a: 240deg;">
                                <div class="es-orbit-logo">
                                    <a href="{{ marketing_url('/invoiceninja') }}" class="group flex flex-col items-center gap-1.5" aria-label="Invoice Ninja integration">
                                        <span class="glass flex h-16 w-16 items-center justify-center rounded-2xl transition-transform group-hover:scale-110">
                                            <svg aria-hidden="true" class="h-9 w-9" viewBox="0 0 24 24" fill="none">
                                                <rect x="3" y="5" width="18" height="16" rx="2" class="fill-gray-900 dark:fill-white"/>
                                                <path d="M7.5 10h9M7.5 13h6M7.5 16h3" class="stroke-white dark:stroke-gray-900" stroke-width="1.5" stroke-linecap="round"/>
                                                <path d="M12 2L9 5h6l-3-3z" fill="#4CAF50"/>
                                            </svg>
                                        </span>
                                        <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400">Invoice Ninja</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Outer ring -->
                <div class="es-ring-wrap h-[31rem] w-[31rem]">
                    <div class="es-ring es-ring-2">
                        <div class="es-orbit-item" style="--a: 60deg; --r: 15.5rem;">
                            <div class="es-orbit-unrot" style="--a: 60deg;">
                                <div class="es-orbit-logo">
                                    <a href="{{ marketing_url('/caldav') }}" class="group flex flex-col items-center gap-1.5" aria-label="CalDAV integration">
                                        <span class="glass flex h-16 w-16 items-center justify-center rounded-2xl transition-transform group-hover:scale-110">
                                            <svg aria-hidden="true" class="h-9 w-9" viewBox="0 0 24 24" fill="none">
                                                <rect x="3" y="5" width="18" height="17" rx="2" fill="#F57C00"/>
                                                <rect x="3" y="5" width="18" height="5" rx="2" fill="#E65100"/>
                                                <circle cx="7" cy="7.5" r="1" fill="#fff" fill-opacity="0.8"/>
                                                <circle cx="17" cy="7.5" r="1" fill="#fff" fill-opacity="0.8"/>
                                                <path d="M9 15l2 2 4-4" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                                            </svg>
                                        </span>
                                        <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400">CalDAV</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="es-orbit-item" style="--a: 180deg; --r: 15.5rem;">
                            <div class="es-orbit-unrot" style="--a: 180deg;">
                                <div class="es-orbit-logo">
                                    <a href="{{ marketing_url('/features/calendar-sync') }}" class="group flex flex-col items-center gap-1.5" aria-label="Apple Calendar sync">
                                        <span class="glass flex h-16 w-16 items-center justify-center rounded-2xl transition-transform group-hover:scale-110">
                                            <svg aria-hidden="true" class="h-9 w-9" viewBox="0 0 24 24" fill="none">
                                                <rect x="3" y="5" width="18" height="17" rx="3" class="fill-white dark:fill-gray-200"/>
                                                <rect x="3" y="5" width="18" height="6" rx="3" fill="#EF4444"/>
                                                <text x="7.5" y="9.5" fill="#fff" font-size="5" font-weight="bold" font-family="system-ui">31</text>
                                                <circle cx="8" cy="16" r="1.5" fill="#EF4444"/>
                                                <rect x="11" y="14" width="6" height="1" rx="0.5" fill="#9ca3af"/>
                                                <rect x="11" y="16.5" width="4" height="1" rx="0.5" fill="#9ca3af"/>
                                            </svg>
                                        </span>
                                        <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400">Apple Calendar</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="es-orbit-item" style="--a: 300deg; --r: 15.5rem;">
                            <div class="es-orbit-unrot" style="--a: 300deg;">
                                <div class="es-orbit-logo">
                                    <a href="{{ marketing_url('/features/calendar-sync') }}" class="group flex flex-col items-center gap-1.5" aria-label="Outlook calendar sync">
                                        <span class="glass flex h-16 w-16 items-center justify-center rounded-2xl transition-transform group-hover:scale-110">
                                            <svg aria-hidden="true" class="h-9 w-9" viewBox="0 0 24 24" fill="none">
                                                <rect x="3" y="4" width="18" height="17" rx="2" fill="#0078D4"/>
                                                <rect x="12" y="4" width="9" height="8.5" rx="1" fill="#0063B1"/>
                                                <path d="M13 5.5l4 3-4 3" stroke="#fff" stroke-width="1" fill="none" stroke-linejoin="round"/>
                                                <ellipse cx="8" cy="14.5" rx="3.5" ry="4" fill="#0063B1"/>
                                                <ellipse cx="8" cy="14.5" rx="2" ry="2.5" fill="#fff" fill-opacity="0.3"/>
                                            </svg>
                                        </span>
                                        <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400">Outlook</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Flat row (mobile and tablets) -->
            <div class="mt-10 flex flex-wrap items-center justify-center gap-x-10 gap-y-8 lg:hidden" data-reveal>
                <a href="{{ marketing_url('/google-calendar') }}" class="group flex flex-col items-center gap-2">
                    <svg aria-hidden="true" class="h-10 w-10 opacity-60 transition-opacity group-hover:opacity-100" viewBox="0 0 24 24" fill="none">
                        <path d="M18.316 5.684H5.684v12.632h12.632V5.684z" fill="#fff"/>
                        <path d="M21.053 22H5.684l-2.631-2.632V5.684L5.684 3h12.632L21.053 5.684V22z" fill="#4285F4"/>
                        <path d="M18.316 22l2.737-2.632V22h-2.737z" fill="#1A73E8"/>
                        <path d="M5.684 18.316L3.053 22V19.368l2.631-1.053z" fill="#1A73E8"/>
                        <path d="M21.053 5.684L18.316 3v2.684h2.737z" fill="#1A73E8"/>
                        <path d="M5.684 3L3.053 5.684h2.631V3z" fill="#EA4335"/>
                        <path d="M18.316 5.684V3l2.737 2.684h-2.737z" fill="#34A853"/>
                        <rect x="7" y="9" width="10" height="1" rx="0.5" fill="#fff"/>
                        <rect x="7" y="12" width="7" height="1" rx="0.5" fill="#fff"/>
                        <rect x="7" y="15" width="5" height="1" rx="0.5" fill="#fff"/>
                    </svg>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Google Calendar</span>
                </a>
                <a href="{{ marketing_url('/stripe') }}" class="group flex flex-col items-center gap-2">
                    <svg aria-hidden="true" class="h-10 w-10 opacity-60 transition-opacity group-hover:opacity-100" viewBox="0 0 24 24" fill="none">
                        <rect x="1" y="4" width="22" height="16" rx="3" fill="#635BFF"/>
                        <path d="M11.2 10.3c0-.66.6-1.12 1.45-1.12.95 0 1.95.45 2.55 1.05l.8-1.85c-.7-.55-1.7-.95-3.05-.95-2.2 0-3.6 1.15-3.6 3.05 0 3 4.1 2.5 4.1 3.8 0 .55-.5.95-1.35.95-1.1 0-2.3-.55-3-1.15l-.85 1.85c.85.7 2.1 1.15 3.55 1.15 2.25 0 3.75-1.1 3.75-3.05 0-3.25-4.35-2.7-4.35-3.73z" fill="#fff"/>
                    </svg>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Stripe</span>
                </a>
                <a href="{{ marketing_url('/invoiceninja') }}" class="group flex flex-col items-center gap-2">
                    <svg aria-hidden="true" class="h-10 w-10 opacity-60 transition-opacity group-hover:opacity-100" viewBox="0 0 24 24" fill="none">
                        <rect x="3" y="5" width="18" height="16" rx="2" class="fill-gray-900 dark:fill-white"/>
                        <path d="M7.5 10h9M7.5 13h6M7.5 16h3" class="stroke-white dark:stroke-gray-900" stroke-width="1.5" stroke-linecap="round"/>
                        <path d="M12 2L9 5h6l-3-3z" fill="#4CAF50"/>
                    </svg>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Invoice Ninja</span>
                </a>
                <a href="{{ marketing_url('/caldav') }}" class="group flex flex-col items-center gap-2">
                    <svg aria-hidden="true" class="h-10 w-10 opacity-60 transition-opacity group-hover:opacity-100" viewBox="0 0 24 24" fill="none">
                        <rect x="3" y="5" width="18" height="17" rx="2" fill="#F57C00"/>
                        <rect x="3" y="5" width="18" height="5" rx="2" fill="#E65100"/>
                        <circle cx="7" cy="7.5" r="1" fill="#fff" fill-opacity="0.8"/>
                        <circle cx="17" cy="7.5" r="1" fill="#fff" fill-opacity="0.8"/>
                        <path d="M9 15l2 2 4-4" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                    </svg>
                    <span class="text-xs text-gray-500 dark:text-gray-400">CalDAV</span>
                </a>
                <a href="{{ marketing_url('/features/calendar-sync') }}" class="group flex flex-col items-center gap-2">
                    <svg aria-hidden="true" class="h-10 w-10 opacity-60 transition-opacity group-hover:opacity-100" viewBox="0 0 24 24" fill="none">
                        <rect x="3" y="5" width="18" height="17" rx="3" class="fill-white dark:fill-gray-200"/>
                        <rect x="3" y="5" width="18" height="6" rx="3" fill="#EF4444"/>
                        <text x="7.5" y="9.5" fill="#fff" font-size="5" font-weight="bold" font-family="system-ui">31</text>
                        <circle cx="8" cy="16" r="1.5" fill="#EF4444"/>
                        <rect x="11" y="14" width="6" height="1" rx="0.5" fill="#9ca3af"/>
                        <rect x="11" y="16.5" width="4" height="1" rx="0.5" fill="#9ca3af"/>
                    </svg>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Apple Calendar</span>
                </a>
                <a href="{{ marketing_url('/features/calendar-sync') }}" class="group flex flex-col items-center gap-2">
                    <svg aria-hidden="true" class="h-10 w-10 opacity-60 transition-opacity group-hover:opacity-100" viewBox="0 0 24 24" fill="none">
                        <rect x="3" y="4" width="18" height="17" rx="2" fill="#0078D4"/>
                        <rect x="12" y="4" width="9" height="8.5" rx="1" fill="#0063B1"/>
                        <path d="M13 5.5l4 3-4 3" stroke="#fff" stroke-width="1" fill="none" stroke-linejoin="round"/>
                        <ellipse cx="8" cy="14.5" rx="3.5" ry="4" fill="#0063B1"/>
                        <ellipse cx="8" cy="14.5" rx="2" ry="2.5" fill="#fff" fill-opacity="0.3"/>
                    </svg>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Outlook</span>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. The numbers: free and open source                         -->
    <!-- ============================================================ -->
    <section id="open-source" class="relative scroll-mt-24 bg-gray-50 py-24 dark:bg-[#0f0f14] lg:py-32">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Free and open source. <span class="text-gradient">Forever.</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    No hidden fees. No per-ticket charges. Keep 100% of your ticket sales.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3" data-reveal-group="140">
                <div class="rounded-3xl border border-blue-200 bg-gradient-to-br from-blue-50 to-sky-50 p-8 text-center dark:border-blue-500/20 dark:from-blue-900/25 dark:to-sky-900/25" data-reveal="panel">
                    <div class="es-od text-gradient mb-4 justify-center text-6xl font-black lg:text-7xl" data-odometer="0%">0%</div>
                    <div class="mb-1 text-xl font-bold text-gray-900 dark:text-white">No platform fees</div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">We don't take a cut of your ticket sales</p>
                </div>
                <div class="rounded-3xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-teal-50 p-8 text-center dark:border-emerald-500/20 dark:from-emerald-900/25 dark:to-teal-900/25" data-reveal="panel">
                    <div class="es-od text-gradient mb-4 justify-center text-6xl font-black lg:text-7xl" data-odometer="$0">$0</div>
                    <div class="mb-1 text-xl font-bold text-gray-900 dark:text-white">Free forever</div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Unlimited events and schedules on our free plan</p>
                </div>
                <div class="rounded-3xl border border-sky-200 bg-gradient-to-br from-sky-50 to-cyan-50 p-8 text-center dark:border-sky-500/20 dark:from-sky-900/25 dark:to-cyan-900/25" data-reveal="panel">
                    <div class="es-od text-gradient mb-4 justify-center text-6xl font-black lg:text-7xl" data-odometer="100%">100%</div>
                    <div class="mb-1 text-xl font-bold text-gray-900 dark:text-white">Open source</div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Selfhost on your own server. Your data, your rules.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. How it works: the pinned story                           -->
    <!-- ============================================================ -->
    <section id="how-it-works" class="es-steps relative scroll-mt-24 bg-white dark:bg-[#0a0a0f]" data-scene="steps" data-active-step="all">
        <div class="es-steps-pin w-full py-24 lg:py-0">
            <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full glass px-4 py-1.5" data-reveal>
                        <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-[#4E81FA] to-[#22D3EE]" aria-hidden="true"></span>
                        <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-600 dark:text-gray-300">Quick setup</span>
                    </div>
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                        Live in <span class="text-gradient">minutes</span>
                    </h2>
                    <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.16s;">
                        Three steps. That's all it takes.
                    </p>
                </div>

                <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2 lg:gap-16">
                    <!-- Steps -->
                    <div class="relative">
                        <div class="absolute bottom-4 top-4 w-px bg-gray-200 ltr:left-6 rtl:right-6 dark:bg-white/10" aria-hidden="true">
                            <span class="es-steps-progress absolute inset-x-0 top-0 h-full bg-gradient-to-b from-[#4E81FA] via-[#0EA5E9] to-[#22D3EE]"></span>
                        </div>
                        <div class="space-y-12">
                            <div class="es-step es-step-0 relative ltr:pl-20 rtl:pr-20">
                                <span class="absolute top-0 flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-blue-400 text-xl font-bold text-white shadow-lg shadow-blue-500/30 ltr:left-0 rtl:right-0">1</span>
                                <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">Create your schedule</h3>
                                <p class="text-gray-600 dark:text-gray-400">Sign up free. Add your events manually or import from Google Calendar.</p>
                            </div>
                            <div class="es-step es-step-1 relative ltr:pl-20 rtl:pr-20">
                                <span class="absolute top-0 flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 to-cyan-500 text-xl font-bold text-white shadow-lg shadow-sky-500/30 ltr:left-0 rtl:right-0">2</span>
                                <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">Share your link</h3>
                                <p class="text-gray-600 dark:text-gray-400">Get your custom URL. Put it in your bio, website, or anywhere you want.</p>
                            </div>
                            <div class="es-step es-step-2 relative ltr:pl-20 rtl:pr-20">
                                <span class="absolute top-0 flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 text-xl font-bold text-white shadow-lg shadow-emerald-500/30 ltr:left-0 rtl:right-0">3</span>
                                <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">Grow your audience</h3>
                                <p class="text-gray-600 dark:text-gray-400">Fans follow your schedule. Send them newsletters and notify them when you add new events.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Device mock (desktop only) -->
                    <div class="relative hidden lg:block" aria-hidden="true">
                        <div class="absolute -inset-6 rounded-[2rem] bg-gradient-to-br from-blue-500/10 via-transparent to-cyan-500/10 blur-2xl"></div>
                        <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-white/10 dark:bg-[#101016]">
                            <div class="flex items-center gap-3 border-b border-gray-200 bg-gray-50 px-4 py-3 dark:border-white/10 dark:bg-white/5">
                                <span class="flex gap-1.5">
                                    <span class="h-3 w-3 rounded-full bg-[#FF5F57]"></span>
                                    <span class="h-3 w-3 rounded-full bg-[#FEBC2E]"></span>
                                    <span class="h-3 w-3 rounded-full bg-[#28C840]"></span>
                                </span>
                                <span class="mx-auto rounded-lg bg-white px-4 py-1 text-xs font-medium text-gray-500 shadow-sm dark:bg-white/10 dark:text-gray-400">
                                    <span class="es-type-url" data-full="eventschedule.com/blue-note">eventschedule.com/blue-note</span><span class="es-caret"></span>
                                </span>
                                <span class="w-14"></span>
                            </div>
                            <div class="relative h-[380px]">
                                <!-- Scene 1: schedule builds itself -->
                                <div class="es-scene es-scene-0 p-6">
                                    <div class="es-pop mb-5 flex items-center gap-3" style="--i: 0;">
                                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-[#4E81FA] to-[#22D3EE] text-lg font-black text-white">B</span>
                                        <div>
                                            <div class="font-bold text-gray-900 dark:text-white">The Blue Note</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Live jazz, five nights a week</div>
                                        </div>
                                        <span class="ml-auto rounded-full bg-gradient-to-r from-[#4E81FA] to-[#0EA5E9] px-4 py-1.5 text-xs font-semibold text-white">Follow</span>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="es-pop flex items-center gap-3 rounded-xl border border-gray-200 p-3 dark:border-white/10" style="--i: 1;">
                                            <span class="w-12 shrink-0 rounded-lg bg-blue-50 py-1.5 text-center dark:bg-blue-500/15"><span class="block text-[9px] font-bold uppercase text-blue-500">Jul</span><span class="block text-lg font-black leading-none text-gray-900 dark:text-white">18</span></span>
                                            <div class="min-w-0"><div class="truncate text-sm font-semibold text-gray-900 dark:text-white">Jazz Night</div><div class="text-xs text-gray-500 dark:text-gray-400">8:00 PM · $25</div></div>
                                            <span class="ml-auto rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400">Tickets</span>
                                        </div>
                                        <div class="es-pop flex items-center gap-3 rounded-xl border border-gray-200 p-3 dark:border-white/10" style="--i: 2;">
                                            <span class="w-12 shrink-0 rounded-lg bg-sky-50 py-1.5 text-center dark:bg-sky-500/15"><span class="block text-[9px] font-bold uppercase text-sky-500">Jul</span><span class="block text-lg font-black leading-none text-gray-900 dark:text-white">24</span></span>
                                            <div class="min-w-0"><div class="truncate text-sm font-semibold text-gray-900 dark:text-white">Open Mic</div><div class="text-xs text-gray-500 dark:text-gray-400">7:30 PM · Free</div></div>
                                            <span class="ml-auto rounded-full bg-blue-100 px-2.5 py-1 text-[10px] font-semibold text-blue-700 dark:bg-blue-500/20 dark:text-blue-400">RSVP</span>
                                        </div>
                                        <div class="es-pop flex items-center gap-3 rounded-xl border border-gray-200 p-3 dark:border-white/10" style="--i: 3;">
                                            <span class="w-12 shrink-0 rounded-lg bg-cyan-50 py-1.5 text-center dark:bg-cyan-500/15"><span class="block text-[9px] font-bold uppercase text-cyan-500">Aug</span><span class="block text-lg font-black leading-none text-gray-900 dark:text-white">01</span></span>
                                            <div class="min-w-0"><div class="truncate text-sm font-semibold text-gray-900 dark:text-white">Blues & Brews</div><div class="text-xs text-gray-500 dark:text-gray-400">9:00 PM · $18</div></div>
                                            <span class="ml-auto rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400">Tickets</span>
                                        </div>
                                    </div>
                                </div>
                                <!-- Scene 2: share the link -->
                                <div class="es-scene es-scene-1 flex flex-col items-center justify-center gap-5 p-6">
                                    <div class="es-pop flex items-center gap-3 rounded-2xl border border-blue-200 bg-blue-50 px-6 py-4 dark:border-blue-500/30 dark:bg-blue-500/10" style="--i: 0;">
                                        <svg class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                                        <span class="font-mono text-sm font-semibold text-gray-900 dark:text-white">eventschedule.com/blue-note</span>
                                        <span class="rounded-lg bg-white px-2.5 py-1 text-xs font-semibold text-gray-600 shadow-sm dark:bg-white/10 dark:text-gray-300">Copy</span>
                                    </div>
                                    <div class="flex flex-wrap justify-center gap-2">
                                        <span class="es-pop glass rounded-full px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300" style="--i: 1;">Link in bio</span>
                                        <span class="es-pop glass rounded-full px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300" style="--i: 2;">QR poster</span>
                                        <span class="es-pop glass rounded-full px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300" style="--i: 3;">Embed on your site</span>
                                    </div>
                                </div>
                                <!-- Scene 3: audience grows -->
                                <div class="es-scene es-scene-2 flex flex-col items-center justify-center gap-6 p-6">
                                    <div class="flex -space-x-3">
                                        <span class="es-pop flex h-12 w-12 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br from-blue-400 to-blue-500 text-sm font-bold text-white dark:border-[#101016]" style="--i: 0;">M</span>
                                        <span class="es-pop flex h-12 w-12 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br from-sky-400 to-sky-500 text-sm font-bold text-white dark:border-[#101016]" style="--i: 1;">J</span>
                                        <span class="es-pop flex h-12 w-12 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br from-cyan-400 to-cyan-500 text-sm font-bold text-white dark:border-[#101016]" style="--i: 2;">A</span>
                                        <span class="es-pop flex h-12 w-12 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br from-emerald-400 to-emerald-500 text-sm font-bold text-white dark:border-[#101016]" style="--i: 3;">S</span>
                                        <span class="es-pop flex h-12 w-12 items-center justify-center rounded-full border-2 border-white bg-gray-100 text-xs font-bold text-gray-600 dark:border-[#101016] dark:bg-white/10 dark:text-gray-300" style="--i: 4;">+1.2k</span>
                                    </div>
                                    <div class="es-pop flex items-center gap-3 rounded-2xl border border-gray-200 bg-white px-5 py-3 shadow-lg dark:border-white/10 dark:bg-[#15151c]" style="--i: 5;">
                                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-sky-100 dark:bg-sky-500/20">
                                            <svg class="h-5 w-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                        </span>
                                        <div class="text-left rtl:text-right">
                                            <div class="text-sm font-semibold text-gray-900 dark:text-white">Newsletter sent</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">1,248 followers notified</div>
                                        </div>
                                        <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 11. Finale: claim your stage                                 -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-gray-50 py-24 dark:bg-[#0f0f14] lg:py-32">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-20 text-center shadow-2xl shadow-blue-500/20 sm:px-12 lg:py-28" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="opacity: 0.35;"></div>
                    <div class="es-aurora es-aurora-2" style="opacity: 0.3;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-4xl font-black tracking-tight text-white md:text-5xl lg:text-6xl">
                        Ready to share <span class="text-gradient es-gradient-anim">your schedule?</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Create your event page, sell tickets with zero platform fees, and email your audience directly. All in one free platform.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">eventschedule.com/</span>
                            <input id="es-claim-input" type="text" placeholder="your-name" autocomplete="off" spellcheck="false" maxlength="30"
                                class="w-full min-w-0 border-0 bg-transparent p-0 font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                        </div>
                        <a href="{{ app_url('/sign_up') }}" data-magnetic="0.14" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-[#4E81FA] via-[#0EA5E9] to-[#22D3EE] px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-blue-500/30 transition-shadow hover:shadow-2xl hover:shadow-cyan-500/40">
                            <span class="relative z-10 flex items-center gap-2">
                                Claim it free
                                <svg class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="mt-6 text-sm text-gray-400">No credit card required. Free forever.</p>
                </div>
            </div>
        </div>
    </section>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
